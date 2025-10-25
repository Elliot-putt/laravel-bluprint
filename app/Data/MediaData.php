<?php

namespace App\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class MediaData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $file_name,
        public string $mime_type,
        public int $size,
        public string $disk,
        public string $collection_name,
        public array $custom_properties,
        public Carbon $created_at,
        public Carbon $updated_at,
        public string $url,
        public ?string $thumbnail_url,
        public string $markdown,
        public bool $is_image,
        public bool $is_video,
        public string $display_name,
        public string $action_text,
    ) {}

    /**
     * Check if this media item is an image
     */
    public function isImage(): bool
    {
        return $this->is_image;
    }

    /**
     * Check if this media item is a video
     */
    public function isVideo(): bool
    {
        return $this->is_video;
    }

    /**
     * Get the markdown representation
     */
    public function getMarkdown(): string
    {
        return $this->markdown;
    }

    /**
     * Get the display URL (with fallback)
     */
    public function getDisplayUrl(): string
    {
        return $this->thumbnail_url ?? $this->url;
    }

    /**
     * Get human-readable file size
     */
    public function getHumanFileSize(): string
    {
        $bytes = $this->size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Check if this media has a thumbnail
     */
    public function hasThumbnail(): bool
    {
        return $this->thumbnail_url !== null;
    }
}
