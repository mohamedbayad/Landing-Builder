<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class AccessControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionNames = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.manage',
            'subscriptions.view',
            'subscriptions.create',
            'subscriptions.edit',
            'subscriptions.cancel',
            'plans.view',
            'plans.create',
            'plans.edit',
            'plans.delete',
            'templates.view',
            'templates.create',
            'templates.upload',
            'templates.edit',
            'templates.delete',
            'templates.publish',
            'landing_pages.view',
            'landing_pages.create',
            'landing_pages.edit',
            'landing_pages.delete',
            'landing_pages.publish',
            'builder.access',
            'builder.export',
            'builder.import',
            'settings.view',
            'settings.edit',
            'analytics.view',
            'plugins.manage',
            'integrations.manage',
            'custom_code.manage',
            'tech.manage',
            'custom_domains.manage',
        ];

        foreach ($permissionNames as $permissionName) {
            Permission::updateOrCreate(
                ['name' => $permissionName],
                [
                    'group' => explode('.', $permissionName)[0] ?? 'general',
                    'description' => null,
                ]
            );
        }

        $roles = [
            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Full platform access',
                'permissions' => $permissionNames,
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Operational administration access',
                'permissions' => [
                    'users.view', 'users.create', 'users.edit',
                    'roles.view',
                    'subscriptions.view', 'subscriptions.create', 'subscriptions.edit',
                    'plans.view',
                    'templates.view', 'templates.create', 'templates.upload', 'templates.edit', 'templates.delete', 'templates.publish',
                    'landing_pages.view', 'landing_pages.create', 'landing_pages.edit', 'landing_pages.delete', 'landing_pages.publish',
                    'builder.access', 'builder.export', 'builder.import',
                    'settings.view', 'settings.edit', 'analytics.view',
                    'plugins.manage', 'integrations.manage', 'custom_code.manage',
                    'tech.manage', 'custom_domains.manage',
                ],
            ],
            'subscriber' => [
                'name' => 'Subscriber',
                'description' => 'Subscriber account owner',
                'permissions' => [
                    'templates.view',
                    'landing_pages.view', 'landing_pages.create', 'landing_pages.edit',
                    'builder.access', 'builder.export', 'builder.import',
                    'analytics.view', 'custom_domains.manage',
                ],
            ],
            'developer' => [
                'name' => 'Developer',
                'description' => 'Technical contributor',
                'permissions' => [
                    'templates.view',
                    'landing_pages.view', 'landing_pages.create', 'landing_pages.edit',
                    'builder.access', 'builder.export', 'builder.import',
                ],
            ],
            'marketer' => [
                'name' => 'Marketer',
                'description' => 'Marketing-focused contributor',
                'permissions' => [
                    'templates.view',
                    'landing_pages.view', 'landing_pages.create', 'landing_pages.edit', 'landing_pages.publish',
                    'builder.access', 'builder.export',
                    'analytics.view',
                ],
            ],
            'manager' => [
                'name' => 'Manager',
                'description' => 'Team/project oversight',
                'permissions' => [
                    'users.view',
                    'subscriptions.view',
                    'plans.view',
                    'templates.view',
                    'landing_pages.view', 'landing_pages.create', 'landing_pages.edit',
                    'builder.access',
                    'analytics.view',
                ],
            ],
        ];

        $permissionIdsByName = Permission::query()->pluck('id', 'name');

        foreach ($roles as $slug => $roleData) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_system' => true,
                ]
            );

            $permissionIds = collect($roleData['permissions'])
                ->map(fn ($name) => $permissionIdsByName[$name] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);
        }

        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For solo subscribers getting started.',
                'monthly_price' => 19,
                'yearly_price' => 190,
                'status' => 'active',
                'trial_days' => 7,
                'sort_order' => 1,
                'features' => [
                    'landing_pages.limit' => 5,
                    'templates.limit' => 20,
                    'custom_domains.limit' => 1,
                    'team_members.limit' => 1,
                    'exports.limit' => 10,
                    'analytics.enabled' => true,
                    'integrations.enabled' => false,
                    'custom_code.enabled' => false,
                ],
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For teams running active campaigns.',
                'monthly_price' => 59,
                'yearly_price' => 590,
                'status' => 'active',
                'trial_days' => 14,
                'sort_order' => 2,
                'features' => [
                    'landing_pages.limit' => 50,
                    'templates.limit' => 200,
                    'custom_domains.limit' => 10,
                    'team_members.limit' => 10,
                    'exports.limit' => 200,
                    'analytics.enabled' => true,
                    'integrations.enabled' => true,
                    'custom_code.enabled' => true,
                    'ab_testing.enabled' => true,
                ],
            ],
            [
                'name' => 'Scale',
                'slug' => 'scale',
                'description' => 'For larger organizations with advanced needs.',
                'monthly_price' => 129,
                'yearly_price' => 1290,
                'status' => 'active',
                'trial_days' => 14,
                'sort_order' => 3,
                'features' => [
                    'landing_pages.limit' => 500,
                    'templates.limit' => 1000,
                    'custom_domains.limit' => 100,
                    'team_members.limit' => 100,
                    'exports.limit' => 1000,
                    'analytics.enabled' => true,
                    'integrations.enabled' => true,
                    'custom_code.enabled' => true,
                    'ab_testing.enabled' => true,
                    'automation.enabled' => true,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $plan = Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                [
                    'name' => $planData['name'],
                    'description' => $planData['description'],
                    'monthly_price' => $planData['monthly_price'],
                    'yearly_price' => $planData['yearly_price'],
                    'status' => $planData['status'],
                    'trial_days' => $planData['trial_days'],
                    'sort_order' => $planData['sort_order'],
                ]
            );

            foreach ($planData['features'] as $featureKey => $featureValue) {
                $featureType = is_bool($featureValue) ? 'boolean' : (is_numeric($featureValue) ? 'limit' : 'text');
                $serializedValue = is_bool($featureValue) ? ($featureValue ? '1' : '0') : (string) $featureValue;

                $plan->features()->updateOrCreate(
                    ['feature_key' => $featureKey],
                    ['feature_type' => $featureType, 'feature_value' => $serializedValue]
                );
            }
        }

        $roleMapByEmail = [
            'maroc.demo@landingbuilder.local' => 'super-admin',
            'admin.demo@landingbuilder.local' => 'admin',
            'marketer.demo@landingbuilder.local' => 'marketer',
            'support.demo@landingbuilder.local' => 'manager',
        ];

        foreach ($roleMapByEmail as $email => $roleSlug) {
            $user = User::where('email', $email)->first();
            $role = Role::where('slug', $roleSlug)->first();
            if (!$user || !$role) {
                continue;
            }

            $user->roles()->syncWithoutDetaching([$role->id]);
            Workspace::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => $user->name . "'s Workspace"]
            );
        }

        $subscriberRole = Role::where('slug', 'subscriber')->first();
        $starterPlan = Plan::where('slug', 'starter')->first();

        if ($subscriberRole && $starterPlan) {
            $subscriberUsers = User::query()
                ->whereHas('roles', function ($query) {
                    $query->where('slug', 'subscriber');
                })
                ->get();

            foreach ($subscriberUsers as $subscriber) {
                Subscription::firstOrCreate(
                    [
                        'user_id' => $subscriber->id,
                        'plan_id' => $starterPlan->id,
                    ],
                    [
                        'status' => 'active',
                        'billing_cycle' => 'monthly',
                        'payment_status' => 'paid',
                        'starts_at' => now()->subDays(2),
                        'ends_at' => now()->addMonth(),
                        'renews_at' => now()->addMonth(),
                        'usage_snapshot' => null,
                    ]
                );
            }
        }
    }
}
