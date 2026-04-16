<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white leading-tight tracking-tight">
            {{ __('Order Management') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">

            <!-- Analytics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Revenue -->
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10">Revenue</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">${{ number_format($analytics['total_revenue'], 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Revenue (Confirmed)</p>
                </div>

                <!-- Pending Orders -->
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-500/10">Pending</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $analytics['pending_orders'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Orders Awaiting Action</p>
                </div>

                <!-- Conversion Rate -->
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-orange-50 dark:bg-orange-500/10 text-brand-orange">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-brand-orange bg-orange-50 dark:bg-orange-500/10">Rate</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $analytics['conversion_rate'] }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Conversion Rate</p>
                </div>

                <!-- Today's Orders -->
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10">Today</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $analytics['today_orders'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Orders Today</p>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm">
                <div class="p-6">

                    <!-- Toolbar -->
                    <div class="flex flex-col lg:flex-row gap-4 mb-6">
                        <!-- Search -->
                        <div class="flex-1">
                            <form method="GET" action="{{ route('leads.index') }}" class="flex gap-2">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                                <div class="relative flex-1">
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email, order ID..."
                                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <button type="submit" class="px-4 py-2.5 bg-brand-orange text-white text-sm font-semibold rounded-lg hover:bg-brand-orange-600 transition-colors shadow-sm">
                                    Search
                                </button>
                            </form>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-wrap gap-2">
                            <select id="statusFilter" onchange="applyFilter('status', this.value)"
                                class="text-sm border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2.5 px-3 focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                                @foreach($statuses as $key => $status)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                                @endforeach
                            </select>

                            <input type="date" id="dateFrom" value="{{ request('date_from') }}" onchange="applyFilter('date_from', this.value)"
                                class="text-sm border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2.5 px-3 focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">

                            <input type="date" id="dateTo" value="{{ request('date_to') }}" onchange="applyFilter('date_to', this.value)"
                                class="text-sm border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2.5 px-3 focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">

                            <a href="{{ route('leads.export', request()->query()) }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export CSV
                            </a>
                        </div>
                    </div>

                    <!-- Bulk Actions Bar -->
                    <div id="bulkActionsBar" class="hidden mb-4 p-3 bg-orange-50 dark:bg-orange-500/10 rounded-lg border border-orange-200 dark:border-orange-500/20">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="text-sm font-medium text-orange-700 dark:text-orange-300">
                                <span id="selectedCount">0</span> orders selected
                            </span>
                            <div class="flex gap-2">
                                <select id="bulkStatusSelect" class="text-sm border border-orange-300 dark:border-orange-500/30 rounded-lg bg-white dark:bg-[#161B22] text-gray-900 dark:text-white py-1.5 px-3">
                                    <option value="">Change Status...</option>
                                    @foreach($statuses as $key => $status)
                                        <option value="{{ $key }}">{{ $status['label'] }}</option>
                                    @endforeach
                                </select>
                                <button onclick="bulkUpdateStatus()" class="px-3 py-1.5 bg-brand-orange text-white text-sm font-semibold rounded-lg hover:bg-brand-orange-600 transition-colors">
                                    Apply
                                </button>
                                <button onclick="bulkDelete()" class="px-3 py-1.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">
                                    Delete Selected
                                </button>
                                <button onclick="clearSelection()" class="px-3 py-1.5 bg-gray-200 dark:bg-white/[0.06] text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-white/[0.10] transition-colors">
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($leads->isEmpty())
                        <div class="text-center py-20 bg-gray-50 dark:bg-white/[0.02] rounded-lg border-2 border-dashed border-gray-200 dark:border-white/[0.06]">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No orders found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                                    Try adjusting your filters or <a href="{{ route('leads.index') }}" class="text-brand-orange hover:underline">clear all filters</a>.
                                @else
                                    Share your landing pages to start generating sales.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-white/[0.06]">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                    <tr>
                                        <th scope="col" class="px-4 py-4 text-left">
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300 dark:border-white/[0.06] text-brand-orange focus:ring-brand-orange">
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            <a href="{{ route('leads.index', array_merge(request()->query(), ['sort' => 'created_at', 'dir' => request('sort') == 'created_at' && request('dir') == 'desc' ? 'asc' : 'desc'])) }}" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                                Date
                                                @if(request('sort') == 'created_at')
                                                    <svg class="w-4 h-4 {{ request('dir') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                @endif
                                            </a>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            <a href="{{ route('leads.index', array_merge(request()->query(), ['sort' => 'amount', 'dir' => request('sort') == 'amount' && request('dir') == 'desc' ? 'asc' : 'desc'])) }}" class="flex items-center gap-1 hover:text-gray-700 dark:hover:text-gray-200">
                                                Amount
                                                @if(request('sort') == 'amount')
                                                    <svg class="w-4 h-4 {{ request('dir') == 'asc' ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                @endif
                                            </a>
                                        </th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-4 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                                    @foreach($leads as $lead)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150" data-lead-id="{{ $lead->id }}">
                                            <td class="px-4 py-4">
                                                <input type="checkbox" class="lead-checkbox rounded border-gray-300 dark:border-white/[0.06] text-brand-orange focus:ring-brand-orange" value="{{ $lead->id }}" onchange="updateBulkActions()">
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $lead->created_at->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->created_at->format('H:i') }}</div>
                                                <div class="text-xs text-gray-400 dark:text-gray-500">#{{ $lead->id }}</div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $lead->first_name }} {{ $lead->last_name }}</div>
                                                @if($lead->email)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->email }}</div>
                                                @endif
                                                @if($lead->phone)
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->phone }}</span>
                                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}?text={{ urlencode('Hello ' . $lead->first_name . ', we are confirming your order #' . $lead->id . '. Your total is ' . $lead->currency . ' ' . number_format($lead->amount, 2) . '. Please confirm your shipping address.') }}"
                                                           target="_blank"
                                                           class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-500 hover:bg-green-600 text-white transition-colors"
                                                           title="Chat on WhatsApp">
                                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                @if(!empty($lead->order_items) && count($lead->order_items) > 0)
                                                    <div class="flex flex-col gap-1">
                                                        @foreach($lead->order_items as $item)
                                                            <div class="text-xs">
                                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $item['qty'] ?? 1 }}x</span>
                                                                <span class="text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($item['name'] ?? $item['title'] ?? 'Product', 20) }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $lead->currency }} {{ number_format($lead->amount, 2) }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $lead->payment_provider }}</div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <form action="{{ route('leads.update', $lead) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    @php
                                                        $statusColors = [
                                                            'new' => 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                                                            'pending' => 'border-yellow-200 bg-yellow-50 text-yellow-700 dark:border-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                            'contacted' => 'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
                                                            'qualified' => 'border-cyan-200 bg-cyan-50 text-cyan-700 dark:border-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400',
                                                            'paid' => 'border-green-200 bg-green-50 text-green-700 dark:border-green-600 dark:bg-green-900/30 dark:text-green-400',
                                                            'shipped' => 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-400',
                                                            'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                                                            'failed' => 'border-red-200 bg-red-50 text-red-700 dark:border-red-600 dark:bg-red-900/30 dark:text-red-400',
                                                            'refunded' => 'border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
                                                        ];
                                                    @endphp
                                                    <select name="status" onchange="this.form.submit()" class="text-xs rounded-lg shadow-sm focus:ring-2 focus:ring-brand-orange/20 py-1.5 pl-2 pr-8 border font-medium {{ $statusColors[$lead->status] ?? 'border-gray-300 bg-white text-gray-700' }}">
                                                        @foreach($statuses as $key => $status)
                                                            <option value="{{ $key }}" {{ $lead->status === $key ? 'selected' : '' }} class="bg-white dark:bg-[#161B22] text-gray-900 dark:text-gray-200">
                                                                {{ $status['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <button onclick="viewDetails({{ $lead->id }})" class="p-2 text-gray-400 hover:text-brand-orange hover:bg-orange-50 dark:hover:bg-orange-500/10 rounded-lg transition-colors" title="View Details">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </button>

                                                    <button onclick="editOrder({{ $lead->id }})" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Edit Order">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>

                                                    @if($lead->invoice_id || in_array($lead->status, ['paid', 'completed', 'shipped']))
                                                        <a href="{{ \Illuminate\Support\Facades\URL::signedRoute('invoices.download', $lead) }}"
                                                           class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                                           title="Download Invoice">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </a>
                                                    @endif

                                                    <form action="{{ route('leads.destroy', $lead) }}" method="POST" class="inline" onsubmit="event.preventDefault(); window.confirmAction('Are you sure you want to delete this order?', this);">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                                <select onchange="applyFilter('per_page', this.value)" class="text-sm border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-1.5 px-3">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <span class="text-sm text-gray-500 dark:text-gray-400">per page</span>
                            </div>
                            {{ $leads->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div id="detailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/75 transition-opacity" onclick="closeModal('detailsModal')"></div>
            <div class="relative bg-white dark:bg-[#161B22] rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-100 dark:border-white/[0.06]">
                <div class="sticky top-0 bg-white dark:bg-[#161B22] px-6 py-4 border-b border-gray-100 dark:border-white/[0.06] flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Order Details</h3>
                    <button onclick="closeModal('detailsModal')" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-white/[0.06]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div id="detailsContent" class="p-6">
                    <div class="flex justify-center py-8">
                        <svg class="animate-spin h-8 w-8 text-brand-orange" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/75 transition-opacity" onclick="closeModal('editModal')"></div>
            <div class="relative bg-white dark:bg-[#161B22] rounded-2xl shadow-xl max-w-lg w-full border border-gray-100 dark:border-white/[0.06]">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06] flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Edit Order</h3>
                    <button onclick="closeModal('editModal')" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-white/[0.06]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form id="editForm" method="POST" class="p-6">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name</label>
                            <input type="text" name="first_name" id="edit_first_name" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name</label>
                            <input type="text" name="last_name" id="edit_last_name" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" id="edit_email" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                            <input type="text" name="address" id="edit_address" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                            <input type="text" name="city" id="edit_city" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ZIP</label>
                            <input type="text" name="zip" id="edit_zip" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                            <input type="text" name="country" id="edit_country" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                            <input type="number" step="0.01" name="amount" id="edit_amount" class="w-full border border-gray-300 dark:border-white/[0.06] rounded-lg bg-white dark:bg-[#0D1117] text-gray-900 dark:text-white py-2 px-3 text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-white/[0.06] rounded-lg hover:bg-gray-200 dark:hover:bg-white/[0.10] transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-brand-orange rounded-lg hover:bg-brand-orange-600 transition-colors shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function applyFilter(key, value) {
            const url = new URL(window.location.href);
            if (value && value !== 'all') {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.lead-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.lead-checkbox:checked');
            const bar = document.getElementById('bulkActionsBar');
            const count = document.getElementById('selectedCount');

            if (checkboxes.length > 0) {
                bar.classList.remove('hidden');
                count.textContent = checkboxes.length;
            } else {
                bar.classList.add('hidden');
            }
        }

        function getSelectedIds() {
            return Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => parseInt(cb.value));
        }

        function clearSelection() {
            document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('selectAll').checked = false;
            updateBulkActions();
        }

        async function bulkUpdateStatus() {
            const ids = getSelectedIds();
            const status = document.getElementById('bulkStatusSelect').value;

            if (!status) {
                alert('Please select a status');
                return;
            }

            if (!confirm(`Update ${ids.length} orders to "${status}"?`)) return;

            try {
                const response = await fetch('{{ route("leads.bulk-update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ids, status })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (e) {
                alert('Error updating orders');
            }
        }

        async function bulkDelete() {
            const ids = getSelectedIds();
            if (!confirm(`Delete ${ids.length} orders? This cannot be undone.`)) return;

            try {
                const response = await fetch('{{ route("leads.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ids })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                }
            } catch (e) {
                alert('Error deleting orders');
            }
        }

        function viewDetails(id) {
            document.getElementById('detailsModal').classList.remove('hidden');
            fetch(`/leads/${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('detailsContent').innerHTML = html;
                });
        }

        function editOrder(id) {
            document.getElementById('editModal').classList.remove('hidden');
            fetch(`/leads/${id}/edit`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('editForm').action = `/leads/${id}`;
                    ['first_name','last_name','email','phone','address','city','zip','country','amount'].forEach(f => {
                        const el = document.getElementById('edit_' + f);
                        if (el) el.value = data[f] || '';
                    });
                });
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
    </script>

</x-app-layout>
