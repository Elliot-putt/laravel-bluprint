<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'title_template',
        'body_template',
        'is_default',
        'default_labels',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'default_labels' => 'array',
    ];

    /**
     * Get the user that owns the template.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
