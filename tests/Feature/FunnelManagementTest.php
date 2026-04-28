<?php

namespace Tests\Feature;

use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunnelManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Landing $landing;
    protected LandingPage $pageA;
    protected LandingPage $pageB;
    protected LandingPage $pageC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $this->user->id,
            'name' => 'Test Workspace',
        ]);

        $this->landing = Landing::create([
            'workspace_id' => $workspace->id,
            'name' => 'Builder Landing',
            'slug' => 'builder-landing',
            'status' => 'draft',
        ]);

        $this->pageA = LandingPage::create([
            'landing_id' => $this->landing->id,
            'type' => 'index',
            'name' => 'Landing',
            'slug' => 'index',
            'funnel_step_type' => 'landing',
            'funnel_position' => 1,
            'next_landing_page_id' => null,
        ]);

        $this->pageB = LandingPage::create([
            'landing_id' => $this->landing->id,
            'type' => 'checkout',
            'name' => 'Checkout',
            'slug' => 'checkout',
            'funnel_step_type' => 'checkout',
            'funnel_position' => 2,
            'next_landing_page_id' => null,
        ]);

        $this->pageC = LandingPage::create([
            'landing_id' => $this->landing->id,
            'type' => 'thankyou',
            'name' => 'Thank You',
            'slug' => 'thank-you',
            'funnel_step_type' => 'thank_you',
            'funnel_position' => 3,
            'next_landing_page_id' => null,
        ]);
    }

    public function test_owner_can_update_funnel_steps_flow(): void
    {
        $payload = [
            'pages' => [
                [
                    'id' => $this->pageA->id,
                    'name' => 'Offer Page',
                    'funnel_step_type' => 'sales',
                    'funnel_position' => 2,
                    'next_landing_page_id' => $this->pageC->id,
                ],
                [
                    'id' => $this->pageB->id,
                    'name' => 'Lead Magnet',
                    'funnel_step_type' => 'lead_capture',
                    'funnel_position' => 1,
                    'next_landing_page_id' => $this->pageA->id,
                ],
                [
                    'id' => $this->pageC->id,
                    'name' => 'Payment',
                    'funnel_step_type' => 'checkout',
                    'funnel_position' => 3,
                    'next_landing_page_id' => null,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('funnel.steps.update', $this->landing), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Funnel steps updated successfully.');

        $this->assertDatabaseHas('landing_pages', [
            'id' => $this->pageA->id,
            'name' => 'Offer Page',
            'funnel_step_type' => 'sales',
            'funnel_position' => 2,
            'next_landing_page_id' => $this->pageC->id,
            'type' => 'index',
        ]);

        $this->assertDatabaseHas('landing_pages', [
            'id' => $this->pageB->id,
            'name' => 'Lead Magnet',
            'funnel_step_type' => 'lead_capture',
            'funnel_position' => 1,
            'next_landing_page_id' => $this->pageA->id,
            'type' => 'page',
        ]);

        $this->assertDatabaseHas('landing_pages', [
            'id' => $this->pageC->id,
            'name' => 'Payment',
            'funnel_step_type' => 'checkout',
            'funnel_position' => 3,
            'next_landing_page_id' => null,
            'type' => 'checkout',
        ]);
    }

    public function test_owner_can_add_new_funnel_step_page(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('funnel.steps.store', $this->landing), [
                'name' => 'Upsell Bump',
                'funnel_step_type' => 'upsell',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Funnel step created successfully.');

        $this->assertDatabaseHas('landing_pages', [
            'landing_id' => $this->landing->id,
            'name' => 'Upsell Bump',
            'slug' => 'upsell-bump',
            'funnel_step_type' => 'upsell',
            'type' => 'page',
            'funnel_position' => 4,
        ]);
    }

    public function test_user_cannot_update_other_users_funnel_steps(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->put(route('funnel.steps.update', $this->landing), [
                'pages' => [
                    [
                        'id' => $this->pageA->id,
                        'name' => 'Blocked',
                        'funnel_step_type' => 'landing',
                        'funnel_position' => 1,
                        'next_landing_page_id' => null,
                    ],
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_update_steps_normalizes_positions_to_contiguous_sequence(): void
    {
        $payload = [
            'pages' => [
                [
                    'id' => $this->pageA->id,
                    'name' => 'Landing A',
                    'funnel_step_type' => 'landing',
                    'funnel_position' => 10,
                    'next_landing_page_id' => $this->pageB->id,
                ],
                [
                    'id' => $this->pageB->id,
                    'name' => 'Checkout B',
                    'funnel_step_type' => 'checkout',
                    'funnel_position' => 10,
                    'next_landing_page_id' => $this->pageC->id,
                ],
                [
                    'id' => $this->pageC->id,
                    'name' => 'Thanks C',
                    'funnel_step_type' => 'thank_you',
                    'funnel_position' => 99,
                    'next_landing_page_id' => null,
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->put(route('funnel.steps.update', $this->landing), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('landing_pages', [
            'id' => $this->pageA->id,
            'funnel_position' => 1,
        ]);
        $this->assertDatabaseHas('landing_pages', [
            'id' => $this->pageB->id,
            'funnel_position' => 2,
        ]);
        $this->assertDatabaseHas('landing_pages', [
            'id' => $this->pageC->id,
            'funnel_position' => 3,
        ]);
    }

    public function test_owner_cannot_save_cyclic_funnel_flow(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('funnel.steps.update', $this->landing), [
                'pages' => [
                    [
                        'id' => $this->pageA->id,
                        'name' => 'Landing',
                        'funnel_step_type' => 'landing',
                        'funnel_position' => 1,
                        'next_landing_page_id' => $this->pageB->id,
                    ],
                    [
                        'id' => $this->pageB->id,
                        'name' => 'Checkout',
                        'funnel_step_type' => 'checkout',
                        'funnel_position' => 2,
                        'next_landing_page_id' => $this->pageA->id,
                    ],
                    [
                        'id' => $this->pageC->id,
                        'name' => 'Thank You',
                        'funnel_step_type' => 'thank_you',
                        'funnel_position' => 3,
                        'next_landing_page_id' => null,
                    ],
                ],
            ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('landing_pages', [
            'id' => $this->pageA->id,
            'next_landing_page_id' => null,
        ]);
        $this->assertDatabaseHas('landing_pages', [
            'id' => $this->pageB->id,
            'next_landing_page_id' => null,
        ]);
    }

    public function test_owner_cannot_submit_partial_flow_update(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('funnel.steps.update', $this->landing), [
                'pages' => [
                    [
                        'id' => $this->pageA->id,
                        'name' => 'Landing',
                        'funnel_step_type' => 'landing',
                        'funnel_position' => 1,
                        'next_landing_page_id' => $this->pageB->id,
                    ],
                    [
                        'id' => $this->pageB->id,
                        'name' => 'Checkout',
                        'funnel_step_type' => 'checkout',
                        'funnel_position' => 2,
                        'next_landing_page_id' => null,
                    ],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_new_step_position_is_capped_to_funnel_end(): void
    {
        $this->actingAs($this->user)
            ->post(route('funnel.steps.store', $this->landing), [
                'name' => 'Late Step',
                'funnel_step_type' => 'custom',
                'funnel_position' => 999,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('landing_pages', [
            'landing_id' => $this->landing->id,
            'name' => 'Late Step',
            'funnel_position' => 4,
        ]);
    }
}
