<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Plans</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">
            @if(session('status'))
                <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
            @endif

            @if(auth()->user()->hasPermission('plans.create'))
                <section class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Create Plan</h3>
                    <form method="POST" action="{{ route('plans.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        <input type="text" name="name" placeholder="Plan name" required class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        <input type="text" name="slug" placeholder="slug (optional)" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        <input type="number" step="0.01" min="0" name="monthly_price" placeholder="Monthly price" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        <input type="number" step="0.01" min="0" name="yearly_price" placeholder="Yearly price" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        <select name="status" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="draft">Draft</option>
                        </select>
                        <input type="number" min="0" name="trial_days" placeholder="Trial days" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        <input type="number" min="0" name="sort_order" placeholder="Sort order" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        <textarea name="description" rows="3" placeholder="Description" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white md:col-span-2"></textarea>
                        <textarea name="features_json" rows="4" placeholder='Features JSON e.g. {"landing_pages.limit":50,"analytics.enabled":true}' class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white md:col-span-2"></textarea>
                        <div class="md:col-span-2 flex justify-end"><button type="submit" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Create Plan</button></div>
                    </form>
                </section>
            @endif

            <div class="space-y-4">
                @foreach($plans as $plan)
                    <section class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                        <form method="POST" action="{{ route('plans.update', $plan) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $plan->name }}" required class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                            <input type="text" name="slug" value="{{ $plan->slug }}" required class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                            <input type="number" step="0.01" min="0" name="monthly_price" value="{{ $plan->monthly_price }}" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                            <input type="number" step="0.01" min="0" name="yearly_price" value="{{ $plan->yearly_price }}" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                            <select name="status" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="active" @selected($plan->status === 'active')>Active</option>
                                <option value="inactive" @selected($plan->status === 'inactive')>Inactive</option>
                                <option value="draft" @selected($plan->status === 'draft')>Draft</option>
                            </select>
                            <input type="number" min="0" name="trial_days" value="{{ $plan->trial_days }}" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                            <input type="number" min="0" name="sort_order" value="{{ $plan->sort_order }}" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                            <textarea name="description" rows="2" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white md:col-span-2">{{ $plan->description }}</textarea>
                            <textarea name="features_json" rows="4" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white md:col-span-2">{{ json_encode($plan->features->mapWithKeys(fn($f) => [$f->feature_key => $f->feature_value])->all(), JSON_PRETTY_PRINT) }}</textarea>
                            <div class="md:col-span-2 flex justify-end"><button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold">Update Plan</button></div>
                        </form>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
