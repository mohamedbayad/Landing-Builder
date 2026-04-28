<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function workspaces()
    {
        return $this->hasMany(Workspace::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->subscriptions()
            ->with('plan.features')
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->latest('starts_at')
            ->first();
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    public function allPermissions()
    {
        $directPermissionIds = $this->permissions()->pluck('permissions.id');
        $rolePermissionIds = Permission::query()
            ->whereHas('roles.users', function ($query) {
                $query->where('users.id', $this->id);
            })
            ->pluck('permissions.id');

        return Permission::whereIn('id', $directPermissionIds->merge($rolePermissionIds)->unique())->get();
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        return $this->allPermissions()->contains('name', $permissionName);
    }

    public function hasActiveSubscription(): bool
    {
        return (bool) $this->activeSubscription();
    }

    public function featureValue(string $featureKey, mixed $default = null): mixed
    {
        $subscription = $this->activeSubscription();
        if (!$subscription || !$subscription->plan) {
            return $default;
        }

        $feature = $subscription->plan->features->firstWhere('feature_key', $featureKey);
        if (!$feature) {
            return $default;
        }

        return $feature->feature_value ?? $default;
    }

    public function featureEnabled(string $featureKey, bool $default = false): bool
    {
        $value = $this->featureValue($featureKey, $default ? '1' : '0');
        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'enabled'], true);
    }

    public function emailAutomations()
    {
        return $this->hasMany(EmailAutomation::class);
    }

    public function emailTemplates()
    {
        return $this->hasMany(EmailTemplate::class);
    }

    public function emailContacts()
    {
        return $this->hasMany(EmailContact::class);
    }

    public function emailMessages()
    {
        return $this->hasMany(EmailMessage::class);
    }

    public function emailSetting()
    {
        return $this->hasOne(EmailSetting::class);
    }

    /**
     * Scope a query to only include online users (seen in the last 5 minutes).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(5));
    }

    /**
     * Check if the user is considered online.
     *
     * @return bool
     */
    public function isOnline()
    {
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 5;
    }
}
