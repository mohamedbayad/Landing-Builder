<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasFactory;

    protected $table = 'media_assets';

    protected $fillable = [
        'user_id',
        'landing_id',
        'template_id',
        'filename',
        'relative_path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'hash',
        'source', // 'manual', 'zip', 'grapesjs'
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function landing()
    {
        return $this->belongsTo(Landing::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    // Accessors
    public function getUrlAttribute()
    {
        if ($this->disk === 'public') {
            return '/storage/' . $this->relative_path;
        }
        return Storage::disk($this->disk)->url($this->relative_path);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSource($query, $source)
    {
        return $query->where('source', $source);
    }
}
