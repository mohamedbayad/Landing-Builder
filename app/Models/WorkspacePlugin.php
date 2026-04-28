<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspacePlugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'plugin_id',
        'status',
        'approved_permissions',
        'settings',
        'auto_update',
        'activated_at',
        'deactivated_at',
        'last_error',
    ];

    protected $casts = [
        'approved_permissions' => 'array',
        // `settings` is stored in a JSON DB column, so it must remain plain array casting.
        // Using `encrypted:array` here writes encrypted text and breaks MySQL JSON constraints.
        'settings' => 'array',
        'auto_update' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
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
