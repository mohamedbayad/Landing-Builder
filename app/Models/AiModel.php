<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    protected $fillable = [
        'ai_provider_id',
        'name',
        'supports_text_generation',
        'supports_image_generation',
        'supports_vision',
        'supports_embeddings',
        'supports_audio',
        'is_default_text_generation',
        'is_default_image_generation',
        'is_default_vision',
        'is_default_embeddings',
        'is_default_audio',
        'raw_metadata',
    ];

    protected $casts = [
        'supports_text_generation' => 'boolean',
        'supports_image_generation' => 'boolean',
        'supports_vision' => 'boolean',
        'supports_embeddings' => 'boolean',
        'supports_audio' => 'boolean',
        'is_default_text_generation' => 'boolean',
        'is_default_image_generation' => 'boolean',
        'is_default_vision' => 'boolean',
        'is_default_embeddings' => 'boolean',
        'is_default_audio' => 'boolean',
        'raw_metadata' => 'array',
    ];

    public function provider()
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
