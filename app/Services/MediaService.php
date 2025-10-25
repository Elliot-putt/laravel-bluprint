<?php

namespace App\Services;

use App\Data\MediaData;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    /**
     * Upload a file and create media record
     */
    public function uploadMedia(User $user, UploadedFile $file, string $collection = 'default'): Media
    {
        // Generate a unique filename
        $uuid = (string) Str::uuid();
        $extension = $file->getClientOriginalExtension();
        $filename = "$uuid.$extension";

        // Add media to the user's collection using Spatie's Media Library
        $media = $user
            ->addMedia($file)
            ->usingName($file->getClientOriginalName())
            ->usingFileName($filename)
            ->toMediaCollection($collection, 's3');

        // Generate thumbnail for videos
        if (Str::startsWith($media->mime_type, 'video/')) {
            $this->generateVideoThumbnail($user, $media, $uuid);
        }

        return $media;
    }

    /**
     * Generate thumbnail for video files
     */
    private function generateVideoThumbnail(User $user, Media $media, string $uuid): ?Media
    {
        $thumbnailPath = "thumbnails/{$uuid}.jpg";

        try {
            FFMpeg::fromDisk('s3')
                ->open($media->getPath())
                ->getFrameFromSeconds(5)
                ->export()
                ->toDisk('s3')
                ->save($thumbnailPath);

            // Add the thumbnail to the media library
            return $user
                ->addMediaFromDisk($thumbnailPath, 's3')
                ->preservingOriginal()
                ->usingName("Thumbnail for {$media->name}")
                ->usingFileName("thumb_{$uuid}.jpg")
                ->withCustomProperties(['video_media_id' => $media->id])
                ->toMediaCollection('video_thumbnails', 's3');
        } catch (\Exception $e) {
            Log::error('Failed to generate thumbnail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a media file (with authorization check)
     */
    public function deleteMedia(Media $media, User $user): bool
    {
        // Check if the authenticated user owns this media
        if ($media->model_id !== $user->id || $media->model_type !== get_class($user)) {
            return false;
        }

        // Delete the media using Spatie's Media Library
        $media->delete();
        return true;
    }

    /**
     * Transform a media item into a MediaData object with URLs and metadata
     */
    public function transformMediaItem(Media $media): MediaData
    {
        $isImage = Str::startsWith($media->mime_type, 'image/');
        $isVideo = Str::startsWith($media->mime_type, 'video/');
        
        // Get the appropriate URL for this media
        $url = $this->getMediaUrl($media);

        // Lookup related thumbnail if this is a video
        $thumbnailUrl = null;
        if ($isVideo) {
            $thumbnail = Media::where('collection_name', 'video_thumbnails')
                ->where('custom_properties->video_media_id', $media->id)
                ->first();

            if ($thumbnail) {
                $thumbnailUrl = $this->getMediaUrl($thumbnail);
            }
        }

        // Generate markdown based on media type
        $markdown = $this->generateMarkdown($media, $url, $thumbnailUrl, $isImage, $isVideo);

        // Generate user-friendly display name and action text
        $displayName = $this->generateDisplayName($media, $isImage, $isVideo);
        $actionText = $this->generateActionText($isImage, $isVideo);

        return new MediaData(
            id: $media->id,
            name: $media->name,
            file_name: $media->file_name,
            mime_type: $media->mime_type,
            size: $media->size,
            disk: $media->disk,
            collection_name: $media->collection_name,
            custom_properties: $media->custom_properties,
            created_at: $media->created_at,
            updated_at: $media->updated_at,
            url: $url,
            thumbnail_url: $thumbnailUrl,
            markdown: $markdown,
            is_image: $isImage,
            is_video: $isVideo,
            display_name: $displayName,
            action_text: $actionText,
        );
    }

    /**
     * Get the appropriate URL for media based on configuration
     */
    public function getMediaUrl(Media $media): string
    {
        // Check if we should use public URLs (recommended for PR links)
        if (config('app.use_public_media_urls', true)) {
            // Use permanent public URL - works because bucket is configured as public
            return $media->getUrl();
        }
        
        // Fallback to temporary URL (expires in 1 week for longer PR lifespan)
        return $media->getTemporaryUrl(Carbon::now()->addWeek());
    }

    /**
     * Generate markdown for different media types
     */
    private function generateMarkdown(Media $media, string $url, ?string $thumbnailUrl, bool $isImage, bool $isVideo): string
    {
        if ($isImage) {
            return "![{$media->name}]({$url})";
        } elseif ($isVideo) {
            if ($thumbnailUrl) {
                return "[![{$media->name}]({$thumbnailUrl})]({$url})";
            } else {
                return "<video src=\"{$url}\" controls></video>";
            }
        } else {
            return "[{$media->name}]({$url})";
        }
    }

    /**
     * Generate user-friendly display name for media
     */
    private function generateDisplayName(Media $media, bool $isImage, bool $isVideo): string
    {
        if (!$media->name) return 'Untitled File';

        // Extract meaningful parts from filename
        $name = strtolower($media->name);

        // Check for common patterns and return friendly names
        if (str_contains($name, 'pr') || str_contains($name, 'pull request')) {
            return $isVideo ? '🎬 Pull request Demo Video. Click the thumbnail to watch.' : '📸 PR Screenshot';
        }
        if (str_contains($name, 'demo')) {
            return $isVideo ? '🎬 Demo Video. Click the thumbnail to watch.' : '📸 Demo Screenshot';
        }
        if (str_contains($name, 'test')) {
            return $isVideo ? '🎬 Test Recording. Click the thumbnail to watch.' : '📸 Test Screenshot';
        }
        if (str_contains($name, 'bug') || str_contains($name, 'issue')) {
            return $isVideo ? '🎬 Bug Report Video. Click the thumbnail to watch.' : '📸 Bug Screenshot';
        }
        if (str_contains($name, 'feature')) {
            return $isVideo ? '🎬 Feature Demo. Click the thumbnail to watch.' : '📸 Feature Screenshot';
        }
        if (str_contains($name, 'screen')) {
            return $isVideo ? '🎬 Screen Recording. Click the thumbnail to watch.' : '📸 Screenshot';
        }

        // Default based on file type
        if ($isVideo) {
            return '🎬 Video Recording. Click the thumbnail to watch.';
        } elseif ($isImage) {
            return '📸 Image';
        } else {
            return '📄 File';
        }
    }

    /**
     * Generate action text for media type
     */
    private function generateActionText(bool $isImage, bool $isVideo): string
    {
        if ($isVideo) {
            return 'Click to Watch';
        } elseif ($isImage) {
            return 'Click to View';
        } else {
            return 'Click to Open';
        }
    }

    /**
     * Get paginated media for a user with transformations applied
     */
    public function getUserMedia(User $user, int $perPage = 12): array
    {
        $media = $user->media()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Transform media items into MediaData objects
        $transformedMedia = collect($media->items())
            ->map(fn($item) => $this->transformMediaItem($item));

        return [
            'data' => $transformedMedia,
            'pagination' => [
                'current_page' => $media->currentPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
                'last_page' => $media->lastPage(),
            ]
        ];
    }
} 