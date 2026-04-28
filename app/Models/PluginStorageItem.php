<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluginStorageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'plugin_id',
        'key',
        'value',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function plugin()
    {
        return $this->belongsTo(Plugin::class);
    }
}

