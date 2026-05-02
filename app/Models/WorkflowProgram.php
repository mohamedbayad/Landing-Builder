<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'trigger_type',
        'trigger_config',
        'timezone',
        'visual_nodes',
        'visual_edges',
        'builder_version',
        'settings',
        'published_at',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'visual_nodes' => 'array',
        'visual_edges' => 'array',
        'settings' => 'array',
        'builder_version' => 'integer',
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

