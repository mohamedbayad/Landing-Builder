<?php

namespace App\Services\Email;

use App\Models\EmailMessage;
use Illuminate\Support\Facades\DB;

class EmailAnalyticsService
{
    public function overview(int $userId, int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();

        $query = EmailMessage::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate);

        $totalSent = (clone $query)->whereNotNull('sent_at')->count();
        $delivered = (clone $query)->whereNotNull('delivered_at')->count();
        $opened = (clone $query)->whereNotNull('opened_at')->count();
        $clicked = (clone $query)->whereNotNull('first_clicked_at')->count();
        $bounced = (clone $query)->where('status', 'bounced')->count();
        $unsubscribed = (clone $query)->where('status', 'unsubscribed')->count();

        $base = max($delivered, 1);
        $openRate = $delivered > 0 ? round(($opened / $base) * 100, 2) : 0.0;
        $clickRate = $delivered > 0 ? round(($clicked / $base) * 100, 2) : 0.0;
        $bounceRate = $totalSent > 0 ? round(($bounced / $totalSent) * 100, 2) : 0.0;
        $unsubscribeRate = $totalSent > 0 ? round(($unsubscribed / $totalSent) * 100, 2) : 0.0;

        $trend = EmailMessage::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total, SUM(CASE WHEN opened_at IS NOT NULL THEN 1 ELSE 0 END) as opened, SUM(CASE WHEN first_clicked_at IS NOT NULL THEN 1 ELSE 0 END) as clicked')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return [
            'total_sent' => $totalSent,
            'delivered' => $delivered,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
            'bounce_rate' => $bounceRate,
            'unsubscribe_rate' => $unsubscribeRate,
            'trend' => $trend,
            'days' => $days,
        ];
    }
}

