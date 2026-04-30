<?php

namespace App\Support;

use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LandingPublicUrl
{
    public const RESERVED_ENDPOINTS = [
        'w', 'api', 'dashboard', 'login', 'register', 'logout', 'password', 'email',
        'profile', 'sanctum', 'templates', 'landings', 'app', 'preview', 'settings',
        'plugins', 'users', 'plans', 'subscriptions', 'analytics', 'media',
    ];

    public static function workspaceEndpointForLanding(Landing $landing): string
    {
        return self::workspaceEndpoint($landing->workspace);
    }

    public static function workspaceEndpoint(?Workspace $workspace): string
    {
        if (!$workspace) {
            return 'workspace';
        }

        $settings = $workspace->settings;
        if (!$settings) {
            $settings = WorkspaceSetting::query()->where('workspace_id', $workspace->id)->first();
        }

        $endpoint = strtolower(trim((string) ($settings?->workspace_public_endpoint ?? '')));
        if ($endpoint !== '' && !in_array($endpoint, self::RESERVED_ENDPOINTS, true)) {
            return $endpoint;
        }

        return self::defaultWorkspaceEndpoint($workspace->name, $workspace->id);
    }

    public static function defaultWorkspaceEndpoint(?string $workspaceName, ?int $workspaceId): string
    {
        $base = Str::slug((string) $workspaceName);
        if ($base === '') {
            $base = 'workspace';
        }

        $suffix = $workspaceId ? '-' . $workspaceId : '';
        $candidate = strtolower(trim($base . $suffix, '-'));

        if ($candidate === '' || in_array($candidate, self::RESERVED_ENDPOINTS, true)) {
            return 'workspace' . ($workspaceId ? '-' . $workspaceId : '');
        }

        return $candidate;
    }

    private static function resolveLandingModel(mixed $landing): ?Landing
    {
        if ($landing instanceof Landing) {
            return $landing;
        }

        $landingId = null;
        if (is_object($landing) && isset($landing->id)) {
            $landingId = (int) $landing->id;
        } elseif (is_array($landing) && isset($landing['id'])) {
            $landingId = (int) $landing['id'];
        }

        if (!$landingId) {
            return null;
        }

        return Landing::query()
            ->with(['workspace.user.roles:id,slug'])
            ->find($landingId);
    }

    public static function isAdminOwnedLanding(mixed $landing): bool
    {
        $landingModel = self::resolveLandingModel($landing);
        if (!$landingModel || !$landingModel->workspace) {
            return false;
        }

        $user = $landingModel->workspace->user;
        if (!$user) {
            $user = $landingModel->workspace->user()->with('roles:id,slug')->first();
        }

        if (!$user) {
            return false;
        }

        if ($user->relationLoaded('roles')) {
            return $user->roles->contains(fn ($role) => in_array($role->slug, ['admin', 'super-admin'], true));
        }

        return $user->roles()->whereIn('slug', ['admin', 'super-admin'])->exists();
    }

    public static function isPlatformMainLanding(mixed $landing): bool
    {
        $isMain = false;
        if ($landing instanceof Landing) {
            $isMain = (bool) $landing->is_main;
        } elseif (is_object($landing) && isset($landing->is_main)) {
            $isMain = (bool) $landing->is_main;
        } elseif (is_array($landing) && isset($landing['is_main'])) {
            $isMain = (bool) $landing['is_main'];
        }

        return (bool) ($isMain && self::isAdminOwnedLanding($landing));
    }

    public static function indexPath(Landing $landing): string
    {
        if (self::isPlatformMainLanding($landing)) {
            return '/';
        }

        $endpoint = self::workspaceEndpointForLanding($landing);
        return '/w/' . $endpoint . '/' . ltrim($landing->slug, '/');
    }

    public static function pagePath(Landing $landing, ?LandingPage $page = null): string
    {
        $pageType = (string) ($page?->type ?? 'index');
        $pageSlug = trim((string) ($page?->slug ?? ''));

        if ($pageType === 'index' || $pageSlug === '') {
            return self::indexPath($landing);
        }

        if (self::isPlatformMainLanding($landing)) {
            return '/' . ltrim($pageSlug, '/');
        }

        return self::indexPath($landing) . '/' . ltrim($pageSlug, '/');
    }

    public static function indexUrl(Landing $landing): string
    {
        return url(self::indexPath($landing));
    }

    public static function pageUrl(Landing $landing, ?LandingPage $page = null): string
    {
        return url(self::pagePath($landing, $page));
    }

    /**
     * @param array<string,mixed> $extraParams
     */
    public static function signedPageUrl(Landing $landing, LandingPage $page, array $extraParams = []): string
    {
        if (self::isPlatformMainLanding($landing)) {
            return URL::signedRoute('public.page', array_merge([
                'slug' => $page->slug,
            ], $extraParams));
        }

        return URL::signedRoute('public.workspace.landing.page', array_merge([
            'workspaceEndpoint' => self::workspaceEndpointForLanding($landing),
            'landingSlug' => $landing->slug,
            'pageSlug' => $page->slug,
        ], $extraParams));
    }
}
