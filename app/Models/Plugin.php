<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'author',
        'description',
        'category',
        'icon',
        'manifest',
        'requires',
        'hooks',
        'settings_schema',
        'permissions',
        'assets',
        'runtime_entry',
        'is_core',
        'is_active',
        'installed_at',
    ];

    protected $casts = [
        'manifest' => 'array',
        'requires' => 'array',
        'hooks' => 'array',
        'settings_schema' => 'array',
        'permissions' => 'array',
        'assets' => 'array',
        'is_core' => 'boolean',
        'is_active' => 'boolean',
        'installed_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function workspacePlugins()
    {
        return $this->hasMany(WorkspacePlugin::class);
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'workspace_plugins')
            ->withPivot([
                'id',
                'status',
                'approved_permissions',
                'settings',
                'auto_update',
                'activated_at',
                'deactivated_at',
                'last_error',
            ])
            ->withTimestamps();
    }
}

