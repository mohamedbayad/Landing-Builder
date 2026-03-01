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

        // Delete any previously seeded CTA click events to avoid duplicates
        $deleted = AnalyticsEvent::where('event_name', 'cta_click')
            ->where('element_position', 'seeded')
            ->delete();
        
        if ($deleted > 0) {
            $this->command->info("🗑️  Cleaned up {$deleted} previously seeded CTA click events.");
        }

        // Distribution: 12 cta-order, 8 cta-whatsapp = 20 total
        $clicks = [];
        for ($i = 0; $i < 12; $i++) {
            $clicks[] = 'cta-order';
        }
        for ($i = 0; $i < 8; $i++) {
            $clicks[] = 'cta-whatsapp';
        }
        shuffle($clicks);

        $created = 0;

        foreach ($clicks as $index => $label) {
            // Create a unique visitor for each click
            $visitor = AnalyticsVisitor::create([
                'visitor_id'    => Str::uuid(),
                'ip_hash'       => md5('seed-' . $index . '-' . time()),
                'user_agent'    => 'Mozilla/5.0 (Seeder ' . ($index + 1) . ')',
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
                'source_type'      => ['direct', 'social', 'search'][rand(0, 2)],
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
                'element_position' => 'seeded',
                'created_at'       => now()->subMinutes(rand(1, 120)),
                'updated_at'       => now(),
            ]);

            $created++;
        }

        $this->command->info("✅ Created {$created} CTA click events (12 cta-order + 8 cta-whatsapp) across {$created} unique sessions.");
    }
}
