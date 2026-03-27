<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'provider',
        'api_key',
        'base_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'api_key' => 'encrypted',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function models()
    {
        return $this->hasMany(AiModel::class);
    }
}
