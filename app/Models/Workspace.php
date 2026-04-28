<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'user_id',
        'stripe_publishable_key',
        'stripe_secret_key',
        'paypal_client_id',
        'paypal_secret',
        'currency'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function landings()
    {
        return $this->hasMany(Landing::class);
    }

    public function formEndpoints()
    {
        return $this->hasMany(FormEndpoint::class);
    }
    public function settings()
    {
        return $this->hasOne(WorkspaceSetting::class);
    }

    public function workspacePlugins()
    {
        return $this->hasMany(WorkspacePlugin::class);
    }

    public function plugins()
    {
        return $this->belongsToMany(Plugin::class, 'workspace_plugins')
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

    public function activePlugins()
    {
        return $this->plugins()->wherePivot('status', 'active');
    }
}
