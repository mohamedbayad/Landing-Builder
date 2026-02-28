<?php

namespace App\Http\Controllers;

use App\Services\OnlineUsersService;
use Illuminate\Http\Request;

class OnlineUsersController extends Controller
{
    public function __construct(
        protected OnlineUsersService $onlineUsersService
    ) {}

    /**
     * Show the Who's Online dedicated page.
     */
    public function index()
    {
        $stats = $this->onlineUsersService->getStats();

        return view('online-users.index', [
            'totalOnline' => $stats['total'],
            'locations'   => $stats['locations'],
            'lastUpdated' => $stats['last_updated'],
        ]);
    }

    /**
     * Return JSON stats for AJAX auto-refresh.
     */
    public function api()
    {
        return response()->json($this->onlineUsersService->getStats());
    }
}
