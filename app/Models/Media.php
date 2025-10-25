<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'size',
        'disk',
        'collection_name',
        'custom_properties',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_properties' => 'array',
    ];

    /**
     * Get the user that owns the media.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL to the media file.
     *
     * @return string
     */
    public function getUrl()
    {
        return Storage::disk($this->disk)->url($this->file_name);
    }

    /**
     * Determine if the media is an image.
     *
     * @return bool
     */
    public function isImage()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Determine if the media is a video.
     *
     * @return bool
     */
    public function isVideo()
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Get the markdown representation of the media.
     *
     * @return string
     */
    public function getMarkdown()
    {
        $url = $this->getUrl();

        if ($this->isImage()) {
            return "![{$this->name}]({$url})";
        }

        if ($this->isVideo()) {
            return "<video src=\"{$url}\" controls></video>";
        }

        return "[{$this->name}]({$url})";
    }
}
