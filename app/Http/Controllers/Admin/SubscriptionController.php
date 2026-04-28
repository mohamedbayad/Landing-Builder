<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::query()
            ->with(['user:id,name,email', 'plan:id,name,slug'])
            ->latest()
            ->paginate(25);

        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);
        $plans = Plan::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('admin.subscriptions.index', compact('subscriptions', 'users', 'plans'));
    }

    public function invoices()
    {
        $subscriptions = Subscription::query()
            ->with(['user:id,name,email', 'user.workspaces:id,user_id,currency,name', 'plan:id,name,slug,monthly_price,yearly_price'])
            ->latest()
            ->paginate(25);

        return view('admin.subscriptions.invoices', compact('subscriptions'));
    }

    public function downloadInvoice(Subscription $subscription)
    {
        $subscription->loadMissing(['user:id,name,email', 'user.workspaces:id,user_id,currency,name', 'plan:id,name,monthly_price,yearly_price']);

        $amount = $this->resolveInvoiceAmount($subscription);
        $currency = $this->resolveInvoiceCurrency($subscription);
        $invoiceNumber = $this->buildInvoiceNumber($subscription);

        $pdf = Pdf::loadView('invoices.subscription-pdf', [
            'subscription' => $subscription,
            'amount' => $amount,
            'currency' => $currency,
            'invoiceNumber' => $invoiceNumber,
        ]);

        return $pdf->download('subscription-invoice-' . $subscription->id . '.pdf');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|in:active,trial,expired,canceled,paused',
            'billing_cycle' => 'required|in:monthly,yearly,lifetime',
            'payment_status' => 'nullable|string|max:120',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'renews_at' => 'nullable|date',
        ]);

        Subscription::create([
            'user_id' => (int) $validated['user_id'],
            'plan_id' => (int) $validated['plan_id'],
            'status' => $validated['status'],
            'billing_cycle' => $validated['billing_cycle'],
            'payment_status' => $validated['payment_status'] ?? null,
            'starts_at' => $validated['starts_at'] ?? now(),
            'ends_at' => $validated['ends_at'] ?? null,
            'renews_at' => $validated['renews_at'] ?? null,
        ]);

        return redirect()->route('subscriptions.index')->with('status', 'Subscription created successfully.');
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'status' => 'required|in:active,trial,expired,canceled,paused',
            'billing_cycle' => 'required|in:monthly,yearly,lifetime',
            'payment_status' => 'nullable|string|max:120',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'renews_at' => 'nullable|date',
        ]);

        $subscription->update([
            'plan_id' => (int) $validated['plan_id'],
            'status' => $validated['status'],
            'billing_cycle' => $validated['billing_cycle'],
            'payment_status' => $validated['payment_status'] ?? null,
            'starts_at' => $validated['starts_at'] ?? $subscription->starts_at,
            'ends_at' => $validated['ends_at'] ?? null,
            'renews_at' => $validated['renews_at'] ?? null,
        ]);

        return redirect()->route('subscriptions.index')->with('status', 'Subscription updated successfully.');
    }

    private function resolveInvoiceAmount(Subscription $subscription): float
    {
        $plan = $subscription->plan;
        if (!$plan) {
            return 0.0;
        }

        if ($subscription->billing_cycle === 'yearly') {
            return (float) $plan->yearly_price;
        }

        if ($subscription->billing_cycle === 'lifetime') {
            return (float) ($plan->yearly_price ?: $plan->monthly_price);
        }

        return (float) $plan->monthly_price;
    }

    private function resolveInvoiceCurrency(Subscription $subscription): string
    {
        $workspaceCurrency = optional(optional($subscription->user)->workspaces->first())->currency;

        return strtoupper((string) ($workspaceCurrency ?: 'USD'));
    }

    private function buildInvoiceNumber(Subscription $subscription): string
    {
        $dateChunk = optional($subscription->created_at)->format('Ymd') ?: now()->format('Ymd');

        return 'SUB-' . $subscription->id . '-' . $dateChunk;
    }
}
