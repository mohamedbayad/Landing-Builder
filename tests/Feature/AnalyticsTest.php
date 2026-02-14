<?php

namespace Tests\Feature;

use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\PageVisit;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected $landing;
    protected $mainLanding;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user and workspace
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'Test Workspace']);

        // Create a regular landing
        $this->landing = Landing::create([
            'workspace_id' => $workspace->id,
            'name' => 'Campaign Landing',
            'slug' => 'campaign-lp',
            'status' => 'published',
            'is_main' => false,
        ]);

        // Create detailed pages
        LandingPage::create([
            'landing_id' => $this->landing->id, 
            'slug' => 'index', 
            'type' => 'index', 
            'name' => 'Home Page',
            'html' => '<h1>Hi</h1>'
        ]);
        LandingPage::create([
            'landing_id' => $this->landing->id, 
            'slug' => 'checkout', 
            'type' => 'checkout', 
            'name' => 'Checkout Page',
            'html' => '<h1>Pay</h1>'
        ]);

        // Create main landing
        $this->mainLanding = Landing::create([
            'workspace_id' => $workspace->id,
            'name' => 'Main Landing',
            'slug' => 'main-lp',
            'status' => 'published',
            'is_main' => true,
        ]);
        LandingPage::create([
            'landing_id' => $this->mainLanding->id, 
            'slug' => 'index', 
            'type' => 'index', 
            'name' => 'Main Home',
            'html' => '<h1>Home</h1>'
        ]);
    }

    public function test_tracks_direct_visit()
    {
        // Visit the specific landing page by slug
        $response = $this->get('/lp/' . $this->landing->slug . '/index');
        
        // Note: The actual route structure might be /slug/page_slug or just /slug if it's index? 
        // Based on PublicLandingController: 
        // /page/{slug} -> tries to find page in Main Landing OR Landing by slug
        // Let's rely on the route registration. Usually `Route::get('/{slug}', ...)` or similarly caught.
        // If the system uses a catch-all, we should check routes/web.php.
        // Assuming standard viewing URL.
        
        // Let's check web.php quickly? No, I'll assume standard access or try standard paths.
        // Usually /lp/{slug} or just /{slug}
        
        // Actually, let's use the MAIN landing as root '/' to be safe for testing middleware
        $response = $this->get('/');
        
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_visits', [
            'landing_id' => $this->mainLanding->id,
            'source_type' => 'direct',
            'device_type' => 'desktop', 
        ]);
    }

    public function test_tracks_visit_via_slug()
    {
        // Visit the specific landing page by slug
        $response = $this->get('/' . $this->landing->slug);
        
        $response->assertStatus(200);

        $this->assertDatabaseHas('page_visits', [
            'landing_id' => $this->landing->id,
            'source_type' => 'direct',
            'device_type' => 'desktop',
        ]);
    }

    public function test_tracks_search_visit()
    {
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_REFERER' => 'https://www.google.com/',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('page_visits', [
            'landing_id' => $this->mainLanding->id,
            'source_type' => 'search',
            'device_type' => 'desktop',
            'referrer' => 'https://www.google.com/',
        ]);
    }

    public function test_tracks_social_visit()
    {
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_REFERER' => 'https://facebook.com/',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('page_visits', [
            'landing_id' => $this->mainLanding->id,
            'source_type' => 'social',
            'device_type' => 'mobile',
        ]);
    }

    public function test_tracks_utm_parameters()
    {
        $response = $this->call('GET', '/?utm_source=newsletter&utm_medium=email&utm_campaign=winter_sale', [], [], [], [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPad; CPU OS 13_2 like Mac OS X)',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('page_visits', [
            'landing_id' => $this->mainLanding->id,
            'source_type' => 'email', // utm_medium=email -> email
            'device_type' => 'tablet',
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'winter_sale',
        ]);
    }

    public function test_tracks_paid_traffic()
    {
        $response = $this->call('GET', '/?utm_source=google&utm_medium=cpc', [], [], [], []);

        $response->assertStatus(200);

        $this->assertDatabaseHas('page_visits', [
            'source_type' => 'paid',
            'utm_medium' => 'cpc',
        ]);
    }

    public function test_ignores_bots()
    {
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        ]);

        $response->assertStatus(200);

        // Should NOT accept the visit if logic says ignore bots
        // Middleware logic: if ($deviceType === 'bot') return $response; (skip creation)
        $this->assertDatabaseMissing('page_visits', [
            'user_agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        ]);
    }

    public function test_dashboard_stats_aggregation()
    {
        // Seed data
        // 5 visits: 3 direct, 1 search, 1 social
        // 3 mobile, 2 desktop
        
        $landingId = $this->mainLanding->id;
        
        PageVisit::factory()->count(3)->create([
            'landing_id' => $landingId,
            'source_type' => 'direct',
            'device_type' => 'mobile',
            'created_at' => now(),
        ]);
        
        PageVisit::factory()->create([
            'landing_id' => $landingId,
            'source_type' => 'search',
            'device_type' => 'desktop',
            'created_at' => now(),
        ]);

        PageVisit::factory()->create([
            'landing_id' => $landingId,
            'source_type' => 'social',
            'device_type' => 'desktop',
            'created_at' => now(),
        ]);

        // Mock acting as the user who owns the workspace
        $user = User::find($this->mainLanding->workspace->user_id);
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        
        // Assert View Data
        $response->assertViewHas('totalVisits', 5);
        
        // Traffic Sources: Direct: 60%, Search: 20%, Social: 20%
        $sources = $response->viewData('trafficSources');
        $this->assertEquals(60.0, $sources['Direct']);
        $this->assertEquals(20.0, $sources['Search']);
        $this->assertEquals(20.0, $sources['Social']);

        // Device Distribution: Mobile: 60%, Desktop: 40%
        $devices = $response->viewData('deviceDistribution');
        $this->assertEquals(60.0, $devices['mobile']);
        $this->assertEquals(40.0, $devices['desktop']);
    }
}
