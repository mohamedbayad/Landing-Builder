<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CountdownController extends Controller
{
    public function show(Landing $landing)
    {
        // Check if countdown is enabled
        if (!$landing->countdown_enabled) {
            return response()->json([
                'enabled' => false,
                'remaining_seconds' => 0,
                'server_now' => now()->toIso8601String(),
            ]);
        }

        $endAt = $landing->getEffectiveCountdownEndAt();

        if (!$endAt) {
             return response()->json([
                'enabled' => false,
                'remaining_seconds' => 0,
                'server_now' => now()->toIso8601String(),
            ]);
        }

        $now = now();
        $remaining = $now->diffInSeconds($endAt, false); // false = return negative if past

        return response()->json([
            'enabled' => true,
            'remaining_seconds' => (int) max(0, $remaining),
            'end_at' => $endAt->toIso8601String(),
            'server_now' => $now->toIso8601String(),
        ]);
    }
}
