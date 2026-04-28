<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with(['roles:id,name,slug', 'subscriptions.plan:id,name'])
            ->latest()
            ->paginate(25);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::query()->orderBy('name')->get();
        $plans = Plan::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('group')->orderBy('name')->get()->groupBy(fn ($permission) => $permission->group ?: 'general');

        return view('admin.users.create', compact('roles', 'plans', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:40',
            'company_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended,pending',
            'role_id' => 'required|exists:roles,id',
            'subscription_plan_id' => 'nullable|exists:plans,id',
            'subscription_status' => 'nullable|in:active,trial,expired,canceled,paused',
            'billing_cycle' => 'nullable|in:monthly,yearly,lifetime',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'status' => $validated['status'],
            'email_verified_at' => now(),
        ]);

        $user->roles()->sync([(int) $validated['role_id']]);
        $user->permissions()->sync(array_map('intval', $validated['permission_ids'] ?? []));

        Workspace::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->name . "'s Workspace"]
        );

        if (!empty($validated['subscription_plan_id'])) {
            $startsAt = now();
            $billingCycle = $validated['billing_cycle'] ?? 'monthly';
            $endsAt = match ($billingCycle) {
                'yearly' => $startsAt->copy()->addYear(),
                'lifetime' => null,
                default => $startsAt->copy()->addMonth(),
            };

            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => (int) $validated['subscription_plan_id'],
                'status' => $validated['subscription_status'] ?? 'active',
                'billing_cycle' => $billingCycle,
                'payment_status' => 'paid',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'renews_at' => $endsAt,
                'usage_snapshot' => null,
            ]);
        }

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::query()->orderBy('name')->get();
        $plans = Plan::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('group')->orderBy('name')->get()->groupBy(fn ($permission) => $permission->group ?: 'general');
        $activeSubscription = $user->activeSubscription();

        return view('admin.users.edit', compact('user', 'roles', 'plans', 'permissions', 'activeSubscription'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:40',
            'company_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,suspended,pending',
            'role_id' => 'required|exists:roles,id',
            'subscription_plan_id' => 'nullable|exists:plans,id',
            'subscription_status' => 'nullable|in:active,trial,expired,canceled,paused',
            'billing_cycle' => 'nullable|in:monthly,yearly,lifetime',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);
        $user->roles()->sync([(int) $validated['role_id']]);
        $user->permissions()->sync(array_map('intval', $validated['permission_ids'] ?? []));

        if (!empty($validated['subscription_plan_id'])) {
            $subscription = $user->activeSubscription() ?: $user->subscriptions()->latest()->first();
            $startsAt = $subscription?->starts_at ?: now();
            $billingCycle = $validated['billing_cycle'] ?? ($subscription?->billing_cycle ?? 'monthly');
            $endsAt = match ($billingCycle) {
                'yearly' => $startsAt->copy()->addYear(),
                'lifetime' => null,
                default => $startsAt->copy()->addMonth(),
            };

            if ($subscription) {
                $subscription->update([
                    'plan_id' => (int) $validated['subscription_plan_id'],
                    'status' => $validated['subscription_status'] ?? $subscription->status,
                    'billing_cycle' => $billingCycle,
                    'ends_at' => $endsAt,
                    'renews_at' => $endsAt,
                ]);
            } else {
                Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => (int) $validated['subscription_plan_id'],
                    'status' => $validated['subscription_status'] ?? 'active',
                    'billing_cycle' => $billingCycle,
                    'payment_status' => 'paid',
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'renews_at' => $endsAt,
                ]);
            }
        }

        return redirect()->route('users.index')->with('status', 'User updated successfully.');
    }
}
