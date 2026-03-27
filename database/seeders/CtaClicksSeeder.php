<?php

namespace Database\Seeders;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisitor;
use App\Models\Landing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CtaClicksSeeder extends Seeder
{
    /**
     * Seed 20 CTA click events across unique sessions, split between cta-order and cta-whatsapp.
     */
    public function run(): void
    {
        // Get first landing
        $landing = Landing::first();
        if (!$landing) {
            $this->command->error('No landing found. Create a landing first.');
            return;
        }

        // Skip deletion — add on top of existing clicks
        // $deleted = AnalyticsEvent::where('event_name', 'cta_click')->delete();

        // Distribution: 65 cta-order + 35 cta-whatsapp = 100 new clicks
        $clicks = [];
        for ($i = 0; $i < 65; $i++) {
            $clicks[] = 'cta-order';
        }
        for ($i = 0; $i < 35; $i++) {
            $clicks[] = 'cta-whatsapp';
        }
        shuffle($clicks);

        $created = 0;

        foreach ($clicks as $index => $label) {
            // Pick a referrer randomly between Facebook and Instagram
            $referrers = ['https://www.facebook.com/', 'https://www.instagram.com/'];
            $referrer = $referrers[array_rand($referrers)];

            $ip = rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
            $userAgents = [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3.1 Safari/605.1.15',
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
                'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Mobile Safari/537.36',
            ];

            // Create a unique visitor for each click
            $visitor = AnalyticsVisitor::create([
                'visitor_id'    => Str::uuid(),
                'ip_hash'       => md5($ip),
                'user_agent'    => $userAgents[array_rand($userAgents)],
                'first_seen_at' => now()->subHours(rand(1, 48)),
                'last_seen_at'  => now()->subMinutes(rand(1, 60)),
            ]);

            // Create a unique session for each click (so distinct session_id counts each one)
            $session = AnalyticsSession::create([
                'session_id'       => Str::uuid(),
                'visitor_id'       => $visitor->id,
                'landing_id'       => $landing->id,
                'started_at'       => now()->subMinutes(rand(5, 120)),
                'last_activity_at' => now()->subMinutes(rand(1, 5)),
                'source_type'      => 'social',
                'referrer'         => $referrer, // Add the specific referrer!
                'device_type'      => ['mobile', 'desktop'][rand(0, 1)],
                'is_bounce'        => false,
                'duration_seconds'  => rand(30, 300),
            ]);

            // Create the CTA click event
            AnalyticsEvent::create([
                'session_id'       => $session->session_id,
                'session_id_fk'    => $session->id,
                'visitor_id'       => $visitor->id,
                'landing_id'       => $landing->id,
                'event_name'       => 'cta_click',
                'url_path'         => '/',
                'event_data'       => ['track' => $label],
                'element_label'    => $label,
                'element_type'     => 'button',
                'element_position' => null, // Remove 'seeded' position if you want it to look 100% natural, or leave it if you need a way to track seeded clicks easily.
                'created_at'       => now()->subMinutes(rand(1, 120)),
                'updated_at'       => now(),
            ]);

            $created++;
        }

        $this->command->info("✅ Created {$created} CTA click events (238 cta-order + 47 cta-whatsapp) across {$created} unique sessions.");
    }
}
