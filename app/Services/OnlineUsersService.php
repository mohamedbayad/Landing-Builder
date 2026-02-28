<?php

namespace App\Services;

use App\Models\AnalyticsSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class OnlineUsersService
{
    /**
     * Get landing IDs for the current user's workspace.
     */
    protected function getLandingIds(): array
    {
        $user = Auth::user();
        if (!$user) return [];

        $workspace = $user->workspaces()->first();
        if (!$workspace) return [];

        return $workspace->landings()->pluck('id')->toArray();
    }

    /**
     * Get the total number of active visitors (seen in last 5 minutes).
     */
    public function getTotalOnline(): int
    {
        $landingIds = $this->getLandingIds();
        if (empty($landingIds)) return 0;

        return AnalyticsSession::active()
            ->whereIn('landing_id', $landingIds)
            ->count();
    }

    /**
     * Get active visitors grouped by location (country > city), sorted by count DESC.
     */
    public function getByLocation(): Collection
    {
        $landingIds = $this->getLandingIds();
        if (empty($landingIds)) return collect();

        return AnalyticsSession::active()
            ->whereIn('landing_id', $landingIds)
            ->selectRaw('COALESCE(country, "Unknown") as country, COALESCE(city, "Unknown") as city, COUNT(*) as count')
            ->groupBy('country', 'city')
            ->orderByDesc('count')
            ->get();
    }

    /**
     * Get combined stats for the online users feature.
     */
    public function getStats(): array
    {
        $total = $this->getTotalOnline();
        $locations = $this->getByLocation();

        return [
            'total'        => $total,
            'locations'    => $locations,
            'last_updated' => now()->toIso8601String(),
        ];
    }
}
