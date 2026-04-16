<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasFactory;

    protected $table = 'media_assets';

    protected $appends = [
        'url',
        'kind',
        'extension',
    ];

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

    public function getExtensionAttribute(): string
    {
        return strtolower((string) pathinfo((string) $this->filename, PATHINFO_EXTENSION));
    }

    public function getKindAttribute(): string
    {
        $mime = strtolower((string) $this->mime_type);
        $ext = $this->extension;

        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }

        if (str_starts_with($mime, 'model/') || in_array($ext, ['glb', 'gltf', 'obj', 'fbx', 'stl', 'usdz'], true)) {
            return 'model';
        }

        if (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'], true)) {
            return 'document';
        }

        if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'], true)) {
            return 'archive';
        }

        if (in_array($ext, ['js', 'mjs', 'css', 'json', 'html', 'xml'], true)) {
            return 'code';
        }

        if (in_array($ext, ['woff', 'woff2', 'ttf', 'otf', 'eot'], true)) {
            return 'font';
        }

        return 'file';
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
