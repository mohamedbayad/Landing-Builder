<?php

namespace App\Http\Controllers;

use App\Models\EmailAutomation;
use App\Models\EmailTemplate;
use App\Services\Email\EmailAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailAnalyticsController extends Controller
{
    public function index(Request $request, EmailAnalyticsService $analyticsService)
    {
        $days = max(1, min(90, (int) $request->input('days', 30)));
        $overview = $analyticsService->overview(Auth::id(), $days);

        $topAutomations = EmailAutomation::query()
            ->where('user_id', Auth::id())
            ->withCount([
                'messages as sent_count' => fn ($query) => $query->whereNotNull('sent_at'),
                'messages as opened_count' => fn ($query) => $query->whereNotNull('opened_at'),
                'messages as clicked_count' => fn ($query) => $query->whereNotNull('first_clicked_at'),
            ])
            ->orderByDesc('sent_count')
            ->limit(10)
            ->get();

        $topTemplates = EmailTemplate::query()
            ->where('user_id', Auth::id())
            ->withCount([
                'messages as sent_count' => fn ($query) => $query->whereNotNull('sent_at'),
                'messages as opened_count' => fn ($query) => $query->whereNotNull('opened_at'),
                'messages as clicked_count' => fn ($query) => $query->whereNotNull('first_clicked_at'),
            ])
            ->orderByDesc('sent_count')
            ->limit(10)
            ->get();

        return view('email-automation.analytics.index', compact('overview', 'topAutomations', 'topTemplates', 'days'));
    }
}

