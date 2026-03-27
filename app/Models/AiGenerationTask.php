<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiGenerationTask extends Model
{
    protected $fillable = [
        'uuid',
        'workspace_id',
        'status',
        'current_phase',
        'progress',
        'input_data',
        'result_data',
        'product_identity',
        'conversion_blueprint',
        'page_structure',
        'generated_images',
        'builder_payload',
        'error',
        'error_message',
    ];

    protected $casts = [
        'input_data'           => 'array',
        'result_data'          => 'array',
        'product_identity'     => 'array',
        'conversion_blueprint' => 'array',
        'page_structure'       => 'array',
        'generated_images'     => 'array',
        'builder_payload'      => 'array',
        'progress'             => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
