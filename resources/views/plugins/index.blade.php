<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
            Plugin Marketplace (Workspace)
        </h2>
    </x-slot>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div
        class="py-8"
        x-data="pluginMarketplacePage({
            plugins: @js($plugins),
            workspaceId: @js($workspace->id),
            csrfToken: @js(csrf_token()),
            settingsBaseUrl: @js(url('/settings/plugins')),
            pluginsApiBaseUrl: @js(url('/plugins')),
            installRoute: @js(route('settings.plugins.install')),
            openInstallOnLoad: @js(old('manifest_json') !== null),
        })"
        x-init="init()"
        @keydown.escape.window="closeDrawer(); closeInstallModal(); closeMenu();"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-5">

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm dark:bg-emerald-900/20 dark:border-emerald-700/40 dark:text-emerald-300">
                    @switch(session('status'))
                        @case('plugin-installed')
                            Plugin installed successfully.
                            @break
                        @case('plugin-activated')
                            Plugin activated successfully.
                            @break
                        @case('plugin-deactivated')
                            Plugin deactivated successfully.
                            @break
                        @case('plugin-settings-updated')
                            Plugin settings updated successfully.
                            @break
                        @default
                            Action completed successfully.
                    @endswitch
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm dark:bg-red-900/20 dark:border-red-700/40 dark:text-red-300">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="rounded-2xl border border-gray-200 dark:border-white/[0.06] bg-white dark:bg-[#161B22] shadow-sm overflow-hidden">
                <div class="px-4 sm:px-5 py-4 border-b border-gray-200 dark:border-white/[0.06] bg-gradient-to-r from-gray-50 to-white dark:from-[#0D1117] dark:to-[#161B22]">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white tracking-tight">Plugin Control Center</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Monitor plugin health, filter faster, and manage actions in one compact area.</p>
                </div>

                <div class="p-4 sm:p-5 space-y-4">
                    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
                        <article class="rounded-xl border border-gray-200 dark:border-white/[0.06] bg-white dark:bg-[#0D1117] p-3.5 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Installed</p>
                            <p class="mt-1.5 text-2xl font-semibold text-gray-900 dark:text-white" x-text="stats.totalInstalled"></p>
                        </article>
                        <article class="rounded-xl border border-emerald-200/70 dark:border-emerald-900/40 bg-emerald-50/60 dark:bg-emerald-900/10 p-3.5 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-emerald-700/80 dark:text-emerald-300/80">Active Plugins</p>
                            <p class="mt-1.5 text-2xl font-semibold text-emerald-700 dark:text-emerald-300" x-text="stats.active"></p>
                        </article>
                        <article class="rounded-xl border border-amber-200/70 dark:border-amber-900/40 bg-amber-50/60 dark:bg-amber-900/10 p-3.5 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Inactive Plugins</p>
                            <p class="mt-1.5 text-2xl font-semibold text-amber-700 dark:text-amber-300" x-text="stats.inactive"></p>
                        </article>
                        <article class="rounded-xl border border-blue-200/70 dark:border-blue-900/40 bg-blue-50/60 dark:bg-blue-900/10 p-3.5 shadow-sm">
                            <p class="text-[11px] uppercase tracking-wide text-blue-700/80 dark:text-blue-300/80">Updates Available</p>
                            <p class="mt-1.5 text-2xl font-semibold text-blue-700 dark:text-blue-300" x-text="stats.updates"></p>
                        </article>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-white/[0.06] bg-gray-50/60 dark:bg-[#0D1117]/70 p-3.5 space-y-3">
                        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.2fr)_repeat(4,minmax(0,1fr))] gap-2.5">
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400 dark:text-gray-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0Z"></path>
                                    </svg>
                                </span>
                                <input
                                    x-model.trim="query"
                                    type="search"
                                    placeholder="Search plugins..."
                                    class="w-full rounded-lg border-gray-300 dark:border-white/[0.08] dark:bg-[#161B22] dark:text-white text-sm pl-9"
                                >
                            </div>

                            <select x-model="statusFilter" class="rounded-lg border-gray-300 dark:border-white/[0.08] dark:bg-[#161B22] dark:text-white text-sm">
                                <option value="all">Status: All</option>
                                <option value="active">Status: Active</option>
                                <option value="inactive">Status: Inactive</option>
                            </select>

                            <select x-model="typeFilter" class="rounded-lg border-gray-300 dark:border-white/[0.08] dark:bg-[#161B22] dark:text-white text-sm">
                                <option value="all">Type: All</option>
                                <option value="core">Type: Core</option>
                                <option value="third-party">Type: Third-party</option>
                            </select>

                            <select x-model="categoryFilter" class="rounded-lg border-gray-300 dark:border-white/[0.08] dark:bg-[#161B22] dark:text-white text-sm">
                                <option value="all">Category: All</option>
                                <template x-for="category in categories" :key="category">
                                    <option :value="category" x-text="toTitleCase(category)"></option>
                                </template>
                            </select>

                            <select x-model="sortBy" class="rounded-lg border-gray-300 dark:border-white/[0.08] dark:bg-[#161B22] dark:text-white text-sm">
                                <option value="name">Sort: Name</option>
                                <option value="recent">Sort: Recently Updated</option>
                                <option value="status">Sort: Status</option>
                            </select>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                            <div class="inline-flex w-fit items-center rounded-md border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#161B22] px-2.5 py-1.5 text-xs text-gray-600 dark:text-gray-300">
                                Workspace #<span class="ml-1 font-semibold text-gray-900 dark:text-white" x-text="workspaceId"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="resetFilters()"
                                    class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white text-sm font-medium hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:border-white/[0.12] dark:hover:bg-white/10"
                                >
                                    Reset Filters
                                </button>
                                <button
                                    type="button"
                                    @click="openInstallModal()"
                                    class="px-3 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:opacity-90 transition shadow-sm"
                                >
                                    Install Plugin
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-1.5 rounded-xl bg-gray-100 dark:bg-[#0D1117] p-1.5 overflow-x-auto">
                        <button
                            @click="activeTab = 'installed'"
                            :class="tabButtonClass('installed')"
                            class="whitespace-nowrap rounded-lg px-3.5 py-2 text-sm font-medium transition-all"
                        >
                            Installed
                            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-md bg-white/90 dark:bg-white/[0.08]" x-text="tabCount('installed')"></span>
                        </button>
                        <button
                            @click="activeTab = 'marketplace'"
                            :class="tabButtonClass('marketplace')"
                            class="whitespace-nowrap rounded-lg px-3.5 py-2 text-sm font-medium transition-all"
                        >
                            Marketplace
                            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-md bg-white/90 dark:bg-white/[0.08]" x-text="tabCount('marketplace')"></span>
                        </button>
                        <button
                            @click="activeTab = 'updates'"
                            :class="tabButtonClass('updates')"
                            class="whitespace-nowrap rounded-lg px-3.5 py-2 text-sm font-medium transition-all"
                        >
                            Updates
                            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-md bg-white/90 dark:bg-white/[0.08]" x-text="tabCount('updates')"></span>
                        </button>
                        <button
                            @click="activeTab = 'core'"
                            :class="tabButtonClass('core')"
                            class="whitespace-nowrap rounded-lg px-3.5 py-2 text-sm font-medium transition-all"
                        >
                            Core Plugins
                            <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-md bg-white/90 dark:bg-white/[0.08]" x-text="tabCount('core')"></span>
                        </button>
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <div x-show="activeTab === 'installed'" x-cloak class="space-y-3">
                    <div class="rounded-xl border border-gray-200 dark:border-white/[0.06] bg-white dark:bg-[#161B22] shadow-sm overflow-hidden">
                        <div class="hidden md:grid grid-cols-[minmax(0,2.2fr)_minmax(0,0.9fr)_minmax(0,1fr)] gap-4 px-4 py-3 border-b border-gray-200 dark:border-white/[0.06] text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <span>Plugin</span>
                            <span>Status</span>
                            <span class="text-right">Actions</span>
                        </div>

                        <template x-if="filteredInstalledPlugins.length === 0">
                            <div class="px-4 py-10 text-center text-sm text-gray-600 dark:text-gray-400">
                                No installed plugins match your current filters.
                            </div>
                        </template>

                        <template x-for="plugin in filteredInstalledPlugins" :key="'installed-' + plugin.slug">
                            <article class="px-4 py-3 border-b last:border-b-0 border-gray-200 dark:border-white/[0.06]">
                                <div class="grid grid-cols-1 md:grid-cols-[minmax(0,2.2fr)_minmax(0,0.9fr)_minmax(0,1fr)] gap-4 md:items-center">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-white/[0.08] flex items-center justify-center overflow-hidden flex-shrink-0">
                                            <template x-if="isIconUrl(plugin.icon)">
                                                <img :src="plugin.icon" :alt="plugin.name" class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!isIconUrl(plugin.icon)">
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="pluginInitial(plugin)"></span>
                                            </template>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white" x-text="plugin.name"></h4>
                                                <template x-if="plugin.is_core">
                                                    <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Core</span>
                                                </template>
                                            </div>
                                            <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400 truncate" x-text="plugin.description"></p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <span x-text="toTitleCase(plugin.category)"></span>
                                                <span class="mx-1">&middot;</span>
                                                v<span x-text="plugin.version"></span>
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                            :class="statusBadgeClass(plugin)"
                                            x-text="statusLabel(plugin)"
                                        ></span>
                                    </div>

                                    <div class="flex items-center justify-start md:justify-end gap-2">
                                        <template x-if="plugin.normalized_status === 'active'">
                                            <form :action="deactivateRoute(plugin.slug)" method="POST" class="inline-flex">
                                                <input type="hidden" name="_token" :value="csrfToken">
                                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-amber-300 text-amber-700 bg-amber-50 text-xs font-medium hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/40">
                                                    Deactivate
                                                </button>
                                            </form>
                                        </template>

                                        <template x-if="plugin.normalized_status !== 'active'">
                                            <form :action="activateRoute(plugin.slug)" method="POST" class="inline-flex">
                                                <input type="hidden" name="_token" :value="csrfToken">
                                                <template x-for="permission in plugin.permissions" :key="plugin.slug + '-installed-perm-' + permission">
                                                    <input type="hidden" name="approved_permissions[]" :value="permission">
                                                </template>
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-brand-orange text-white text-xs font-semibold hover:opacity-90 transition">
                                                    Activate
                                                </button>
                                            </form>
                                        </template>

                                        <button
                                            type="button"
                                            @click="openConfigure(plugin.slug)"
                                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 bg-white text-xs font-medium hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:border-white/[0.12] dark:hover:bg-white/10"
                                        >
                                            Configure
                                        </button>

                                        <div class="relative" @click.outside="closeMenu()">
                                            <button
                                                type="button"
                                                @click.stop="toggleMenu(plugin.slug)"
                                                class="h-8 w-8 rounded-lg border border-gray-300 text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:border-white/[0.12] dark:hover:bg-white/10"
                                                aria-label="Plugin actions"
                                            >
                                                <svg class="h-4 w-4 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-2 4a2 2 0 1 1 4 0 2 2 0 0 1-4 0Z"></path>
                                                </svg>
                                            </button>

                                            <div
                                                x-show="openMenuSlug === plugin.slug"
                                                x-cloak
                                                class="absolute right-0 mt-2 w-44 rounded-lg border border-gray-200 dark:border-white/[0.1] bg-white dark:bg-[#0D1117] shadow-lg py-1 z-20"
                                            >
                                                <button
                                                    type="button"
                                                    @click="openDetails(plugin.slug)"
                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/10"
                                                >
                                                    View details
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="openConfigure(plugin.slug, true)"
                                                    class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/10"
                                                >
                                                    Reset settings
                                                </button>
                                                <template x-if="!plugin.is_core">
                                                    <button
                                                        type="button"
                                                        @click="uninstallPlugin(plugin.slug)"
                                                        class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-900/30"
                                                    >
                                                        Uninstall
                                                    </button>
                                                </template>
                                                <template x-if="plugin.is_core">
                                                    <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">Core plugin protected</div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>

                <div x-show="activeTab === 'marketplace'" x-cloak class="space-y-3">
                    <template x-if="filteredMarketplacePlugins.length === 0">
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-white/[0.12] p-8 text-center bg-white dark:bg-[#161B22]">
                            <p class="text-sm text-gray-600 dark:text-gray-400">No marketplace plugins match your current filters.</p>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <template x-for="plugin in filteredMarketplacePlugins" :key="'market-' + plugin.slug">
                            <article class="rounded-xl border border-gray-200 dark:border-white/[0.06] bg-white dark:bg-[#161B22] shadow-sm p-4 flex flex-col gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-white/[0.08] flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <template x-if="isIconUrl(plugin.icon)">
                                            <img :src="plugin.icon" :alt="plugin.name" class="h-full w-full object-cover">
                                        </template>
                                        <template x-if="!isIconUrl(plugin.icon)">
                                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="pluginInitial(plugin)"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white" x-text="plugin.name"></h4>
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium"
                                                :class="statusBadgeClass(plugin)"
                                                x-text="statusLabel(plugin)"
                                            ></span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-5 h-10 overflow-hidden" x-text="plugin.description"></p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="px-2 py-1 rounded-md bg-gray-100 dark:bg-white/[0.06]" x-text="toTitleCase(plugin.category)"></span>
                                    <span class="px-2 py-1 rounded-md bg-gray-100 dark:bg-white/[0.06]">v<span x-text="plugin.version"></span></span>
                                    <template x-if="plugin.is_core">
                                        <span class="px-2 py-1 rounded-md bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Core</span>
                                    </template>
                                    <template x-if="plugin.has_update">
                                        <span class="px-2 py-1 rounded-md bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Update available</span>
                                    </template>
                                </div>

                                <div class="mt-auto pt-1 flex items-center gap-2">
                                    <template x-if="plugin.normalized_status === 'active'">
                                        <button type="button" disabled class="px-3 py-2 rounded-lg border border-emerald-300 text-emerald-700 bg-emerald-50 text-xs font-semibold cursor-default dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/40">
                                            Active
                                        </button>
                                    </template>

                                    <template x-if="plugin.normalized_status !== 'active'">
                                        <form :action="activateRoute(plugin.slug)" method="POST" class="inline-flex">
                                            <input type="hidden" name="_token" :value="csrfToken">
                                            <template x-for="permission in plugin.permissions" :key="plugin.slug + '-market-perm-' + permission">
                                                <input type="hidden" name="approved_permissions[]" :value="permission">
                                            </template>
                                            <button type="submit" class="px-3 py-2 rounded-lg bg-brand-orange text-white text-xs font-semibold hover:opacity-90 transition" x-text="plugin.workspace_state ? 'Activate' : 'Install & Activate'"></button>
                                        </form>
                                    </template>

                                    <button
                                        type="button"
                                        @click="openDetails(plugin.slug)"
                                        class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white text-xs font-medium hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:border-white/[0.12] dark:hover:bg-white/10"
                                    >
                                        View Details
                                    </button>
                                </div>
                            </article>
                        </template>
                    </div>
                </div>

                <div x-show="activeTab === 'updates'" x-cloak class="space-y-3">
                    <template x-if="filteredUpdatesPlugins.length === 0">
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-white/[0.12] p-8 text-center bg-white dark:bg-[#161B22]">
                            <p class="text-sm text-gray-600 dark:text-gray-400">No plugin updates available right now.</p>
                        </div>
                    </template>

                    <template x-for="plugin in filteredUpdatesPlugins" :key="'update-' + plugin.slug">
                        <article class="rounded-xl border border-blue-200 dark:border-blue-900/40 bg-white dark:bg-[#161B22] shadow-sm p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white" x-text="plugin.name"></h4>
                                    <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Update</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate" x-text="plugin.description"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Current: v<span x-text="plugin.version"></span>
                                    <span class="mx-1">&middot;</span>
                                    Latest: v<span x-text="plugin.latest_version"></span>
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="openDetails(plugin.slug)"
                                    class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white text-xs font-medium hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:border-white/[0.12] dark:hover:bg-white/10"
                                >
                                    View Details
                                </button>
                            </div>
                        </article>
                    </template>
                </div>

                <div x-show="activeTab === 'core'" x-cloak class="space-y-3">
                    <template x-if="filteredCorePlugins.length === 0">
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-white/[0.12] p-8 text-center bg-white dark:bg-[#161B22]">
                            <p class="text-sm text-gray-600 dark:text-gray-400">No core plugins match your current filters.</p>
                        </div>
                    </template>

                    <template x-for="plugin in filteredCorePlugins" :key="'core-' + plugin.slug">
                        <article class="rounded-xl border border-gray-200 dark:border-white/[0.06] bg-white dark:bg-[#161B22] shadow-sm p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white" x-text="plugin.name"></h4>
                                    <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Core</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate" x-text="plugin.description"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span x-text="toTitleCase(plugin.category)"></span>
                                    <span class="mx-1">&middot;</span>
                                    v<span x-text="plugin.version"></span>
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                    :class="statusBadgeClass(plugin)"
                                    x-text="statusLabel(plugin)"
                                ></span>
                                <button
                                    type="button"
                                    @click="openDetails(plugin.slug)"
                                    class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white text-xs font-medium hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:border-white/[0.12] dark:hover:bg-white/10"
                                >
                                    View Details
                                </button>
                            </div>
                        </article>
                    </template>
                </div>
            </section>
        </div>

        <div
            x-show="installModalOpen"
            x-cloak
            class="fixed inset-0 z-40"
            role="dialog"
            aria-modal="true"
        >
            <div class="absolute inset-0 bg-black/45" @click="closeInstallModal()"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-2xl rounded-xl border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#161B22] shadow-xl overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.08] flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Install Plugin Manifest</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Paste a valid <code class="text-xs bg-gray-100 dark:bg-white/10 px-1 rounded">plugin.json</code> manifest.</p>
                        </div>
                        <button type="button" @click="closeInstallModal()" class="h-8 w-8 rounded-lg border border-gray-300 text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:border-white/[0.12] dark:hover:bg-white/10" aria-label="Close install modal">
                            <svg class="h-4 w-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18"></path>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" :action="installRoute" class="p-5 space-y-4">
                        @csrf

                        <div>
                            <label for="manifest_json" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Manifest JSON</label>
                            <textarea
                                id="manifest_json"
                                name="manifest_json"
                                rows="11"
                                class="w-full rounded-lg border-gray-300 dark:border-white/[0.08] dark:bg-[#0D1117] dark:text-white text-sm font-mono"
                                placeholder='{"name":"Google Analytics","slug":"google-analytics","version":"1.2.0","category":"integration","hooks":["page.render"],"permissions":["analytics","tracking"]}'
                            >{{ old('manifest_json') }}</textarea>
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="is_core" value="1" class="rounded border-gray-300 dark:border-white/[0.2]" {{ old('is_core') ? 'checked' : '' }}>
                            Mark as core plugin
                        </label>

                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" @click="closeInstallModal()" class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white text-sm font-medium hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:border-white/[0.12] dark:hover:bg-white/10">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:opacity-90 transition">
                                Install Plugin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div
            x-show="drawerOpen"
            x-cloak
            class="fixed inset-0 z-50"
            role="dialog"
            aria-modal="true"
        >
            <div class="absolute inset-0 bg-black/45" @click="closeDrawer()"></div>

            <aside class="absolute right-0 top-0 h-full w-full max-w-xl bg-white dark:bg-[#161B22] border-l border-gray-200 dark:border-white/[0.08] shadow-xl flex flex-col">
                <header class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.08] flex items-start justify-between gap-4">
                    <div class="min-w-0" x-show="drawerPlugin">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="drawerPlugin ? drawerPlugin.name : ''"></h3>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                :class="drawerPlugin ? statusBadgeClass(drawerPlugin) : ''"
                                x-text="drawerPlugin ? statusLabel(drawerPlugin) : ''"
                            ></span>
                            <template x-if="drawerPlugin && drawerPlugin.is_core">
                                <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">Core</span>
                            </template>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="drawerPlugin ? drawerPlugin.description : ''"></p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span x-show="drawerPlugin">Slug: <code x-text="drawerPlugin ? drawerPlugin.slug : ''"></code></span>
                            <span class="mx-1">&middot;</span>
                            <span x-show="drawerPlugin">Version: <code x-text="drawerPlugin ? drawerPlugin.version : ''"></code></span>
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeDrawer()"
                        class="h-8 w-8 rounded-lg border border-gray-300 text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:border-white/[0.12] dark:hover:bg-white/10"
                        aria-label="Close settings drawer"
                    >
                        <svg class="h-4 w-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18"></path>
                        </svg>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    <section class="rounded-lg border border-gray-200 dark:border-white/[0.08] p-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Workspace status</p>
                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-200" x-text="drawerPlugin && drawerPlugin.workspace_state ? 'Installed in this workspace' : 'Not installed in this workspace yet'"></p>

                        <template x-if="drawerPlugin && drawerPlugin.permissions.length">
                            <div class="mt-3">
                                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Permissions</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="permission in drawerPlugin.permissions" :key="'drawer-permission-' + permission">
                                        <span class="text-xs px-2 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-white/[0.06] dark:text-gray-300" x-text="permission"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </section>

                    <template x-if="drawerPlugin && !drawerPlugin.workspace_state">
                        <section class="rounded-lg border border-amber-200 bg-amber-50 text-amber-700 px-3 py-2 text-sm dark:bg-amber-900/20 dark:border-amber-700/40 dark:text-amber-300">
                            Activate this plugin first, then configure workspace-specific settings.
                        </section>
                    </template>

                    <template x-if="drawerPlugin && drawerPlugin.workspace_state">
                        <form :action="settingsRoute(drawerPlugin.slug)" method="POST" class="space-y-4">
                            <input type="hidden" name="_token" :value="csrfToken">
                            <input type="hidden" name="_method" value="PUT">

                            <template x-if="drawerSchema.length === 0">
                                <div class="rounded-lg border border-gray-200 dark:border-white/[0.08] px-3 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    This plugin does not define configurable settings.
                                </div>
                            </template>

                            <template x-for="field in drawerSchema" :key="'drawer-field-' + field.key">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1" x-text="field.label"></label>

                                    <template x-if="field.type === 'textarea'">
                                        <textarea
                                            :name="'settings[' + field.key + ']'"
                                            rows="3"
                                            x-model="drawerSettings[field.key]"
                                            class="w-full rounded-md border-gray-300 dark:border-white/[0.08] dark:bg-[#0D1117] dark:text-white text-sm"
                                        ></textarea>
                                    </template>

                                    <template x-if="field.type === 'select'">
                                        <select
                                            :name="'settings[' + field.key + ']'"
                                            x-model="drawerSettings[field.key]"
                                            class="w-full rounded-md border-gray-300 dark:border-white/[0.08] dark:bg-[#0D1117] dark:text-white text-sm"
                                        >
                                            <template x-for="option in normalizeFieldOptions(field.options)" :key="'drawer-option-' + field.key + '-' + option.value">
                                                <option :value="option.value" x-text="option.label"></option>
                                            </template>
                                        </select>
                                    </template>

                                    <template x-if="field.type === 'toggle'">
                                        <div>
                                            <input type="hidden" :name="'settings[' + field.key + ']'" :value="isTruthy(drawerSettings[field.key]) ? '1' : '0'">
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                <input
                                                    type="checkbox"
                                                    class="rounded border-gray-300 dark:border-white/[0.2]"
                                                    :checked="isTruthy(drawerSettings[field.key])"
                                                    @change="drawerSettings[field.key] = $event.target.checked ? '1' : '0'"
                                                >
                                                Enabled
                                            </label>
                                        </div>
                                    </template>

                                    <template x-if="isTextLikeField(field.type)">
                                        <input
                                            :type="field.type"
                                            :name="'settings[' + field.key + ']'"
                                            x-model="drawerSettings[field.key]"
                                            :required="field.required"
                                            class="w-full rounded-md border-gray-300 dark:border-white/[0.08] dark:bg-[#0D1117] dark:text-white text-sm"
                                        >
                                    </template>
                                </div>
                            </template>

                            <div class="pt-2 flex items-center justify-between gap-2">
                                <button
                                    type="button"
                                    @click="resetDrawerSettings()"
                                    class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white text-sm font-medium hover:bg-gray-50 dark:bg-white/5 dark:text-gray-200 dark:border-white/[0.12] dark:hover:bg-white/10"
                                >
                                    Reset
                                </button>
                                <button
                                    type="submit"
                                    class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:opacity-90 transition"
                                >
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </aside>
        </div>
    </div>

    <script>
        function pluginMarketplacePage(config) {
            return {
                workspaceId: config.workspaceId,
                csrfToken: config.csrfToken,
                settingsBaseUrl: config.settingsBaseUrl,
                pluginsApiBaseUrl: config.pluginsApiBaseUrl,
                installRoute: config.installRoute,
                plugins: [],
                activeTab: 'installed',
                query: '',
                statusFilter: 'all',
                typeFilter: 'all',
                categoryFilter: 'all',
                sortBy: 'name',
                openMenuSlug: null,
                installModalOpen: false,
                drawerOpen: false,
                drawerPlugin: null,
                drawerSchema: [],
                drawerSettings: {},
                drawerDefaults: {},

                init() {
                    this.plugins = (Array.isArray(config.plugins) ? config.plugins : []).map((plugin) => this.normalizePlugin(plugin));
                    if (config.openInstallOnLoad) {
                        this.installModalOpen = true;
                        this.activeTab = 'marketplace';
                    }
                },

                get stats() {
                    const totalInstalled = this.plugins.length;
                    const active = this.plugins.filter((plugin) => plugin.normalized_status === 'active').length;
                    const inactive = totalInstalled - active;
                    const updates = this.plugins.filter((plugin) => plugin.has_update).length;

                    return { totalInstalled, active, inactive, updates };
                },

                get categories() {
                    return [...new Set(this.plugins.map((plugin) => plugin.category).filter(Boolean))].sort();
                },

                get filteredInstalledPlugins() {
                    return this.applyFilters(this.plugins.filter((plugin) => plugin.workspace_state !== null));
                },

                get filteredMarketplacePlugins() {
                    return this.applyFilters(this.plugins);
                },

                get filteredUpdatesPlugins() {
                    return this.applyFilters(this.plugins.filter((plugin) => plugin.has_update));
                },

                get filteredCorePlugins() {
                    return this.applyFilters(this.plugins.filter((plugin) => plugin.is_core));
                },

                tabCount(tab) {
                    if (tab === 'installed') {
                        return this.plugins.filter((plugin) => plugin.workspace_state !== null).length;
                    }
                    if (tab === 'updates') {
                        return this.plugins.filter((plugin) => plugin.has_update).length;
                    }
                    if (tab === 'core') {
                        return this.plugins.filter((plugin) => plugin.is_core).length;
                    }

                    return this.plugins.length;
                },

                normalizePlugin(plugin) {
                    const workspaceState = plugin && typeof plugin.workspace_state === 'object'
                        ? {
                            ...plugin.workspace_state,
                            settings: (plugin.workspace_state && typeof plugin.workspace_state.settings === 'object') ? plugin.workspace_state.settings : {},
                        }
                        : null;

                    const rawSchema = Array.isArray(plugin.settings_schema)
                        ? plugin.settings_schema
                        : (plugin.settings_schema && typeof plugin.settings_schema === 'object' ? Object.entries(plugin.settings_schema).map(([key, field]) => ({ key, ...(field || {}) })) : []);

                    const schema = rawSchema
                        .map((field) => {
                            const type = this.normalizeFieldType(field.type || 'text');
                            const key = field.key || field.name || '';
                            return {
                                key,
                                type,
                                label: field.label || key,
                                required: Boolean(field.required),
                                options: field.options || [],
                                default: Object.prototype.hasOwnProperty.call(field, 'default') ? field.default : '',
                            };
                        })
                        .filter((field) => field.key !== '');

                    const latestVersion = typeof plugin.latest_version === 'string' && plugin.latest_version !== ''
                        ? plugin.latest_version
                        : (plugin.version || '');

                    const hasUpdate = Boolean(plugin.has_update) || this.compareVersions(latestVersion, plugin.version || '') > 0;

                    return {
                        ...plugin,
                        name: plugin.name || 'Unnamed Plugin',
                        slug: plugin.slug || '',
                        description: plugin.description || 'No description provided.',
                        category: plugin.category || 'general',
                        version: plugin.version || '0.0.0',
                        latest_version: latestVersion,
                        has_update: hasUpdate,
                        is_core: Boolean(plugin.is_core),
                        permissions: Array.isArray(plugin.permissions) ? plugin.permissions : [],
                        workspace_state: workspaceState,
                        settings_schema: schema,
                        normalized_status: this.normalizeStatus(workspaceState ? workspaceState.status : 'inactive'),
                    };
                },

                applyFilters(items) {
                    const q = this.query.trim().toLowerCase();

                    let output = [...items].filter((plugin) => {
                        if (q !== '') {
                            const searchable = [
                                plugin.name,
                                plugin.slug,
                                plugin.description,
                                plugin.category,
                                plugin.version,
                                ...(plugin.permissions || []),
                            ]
                                .filter(Boolean)
                                .join(' ')
                                .toLowerCase();

                            if (!searchable.includes(q)) {
                                return false;
                            }
                        }

                        if (this.statusFilter !== 'all') {
                            const status = plugin.normalized_status === 'active' ? 'active' : 'inactive';
                            if (status !== this.statusFilter) {
                                return false;
                            }
                        }

                        if (this.typeFilter === 'core' && !plugin.is_core) {
                            return false;
                        }

                        if (this.typeFilter === 'third-party' && plugin.is_core) {
                            return false;
                        }

                        if (this.categoryFilter !== 'all' && plugin.category !== this.categoryFilter) {
                            return false;
                        }

                        return true;
                    });

                    output.sort((a, b) => {
                        if (this.sortBy === 'recent') {
                            return this.dateScore(b.updated_at || b.installed_at) - this.dateScore(a.updated_at || a.installed_at);
                        }

                        if (this.sortBy === 'status') {
                            const statusOrder = { active: 0, error: 1, inactive: 2 };
                            const left = statusOrder[a.normalized_status] ?? 3;
                            const right = statusOrder[b.normalized_status] ?? 3;
                            if (left !== right) {
                                return left - right;
                            }
                        }

                        return a.name.localeCompare(b.name);
                    });

                    return output;
                },

                normalizeStatus(status) {
                    if (status === 'active') {
                        return 'active';
                    }
                    if (status === 'error') {
                        return 'error';
                    }

                    return 'inactive';
                },

                normalizeFieldType(type) {
                    const valid = ['text', 'password', 'number', 'color', 'textarea', 'select', 'toggle'];
                    return valid.includes(type) ? type : 'text';
                },

                normalizeFieldOptions(options) {
                    if (!Array.isArray(options)) {
                        return [];
                    }

                    return options.map((option) => {
                        if (option && typeof option === 'object') {
                            const value = Object.prototype.hasOwnProperty.call(option, 'value') ? option.value : '';
                            return {
                                value: String(value),
                                label: option.label || String(value),
                            };
                        }

                        return {
                            value: String(option),
                            label: String(option),
                        };
                    });
                },

                isTextLikeField(type) {
                    return ['text', 'password', 'number', 'color'].includes(type);
                },

                isTruthy(value) {
                    return value === true || value === 1 || value === '1' || value === 'true';
                },

                dateScore(value) {
                    if (!value) {
                        return 0;
                    }

                    const timestamp = Date.parse(value);
                    return Number.isNaN(timestamp) ? 0 : timestamp;
                },

                compareVersions(leftVersion, rightVersion) {
                    const left = String(leftVersion || '').split('.').map((segment) => Number.parseInt(segment, 10) || 0);
                    const right = String(rightVersion || '').split('.').map((segment) => Number.parseInt(segment, 10) || 0);
                    const maxLength = Math.max(left.length, right.length);

                    for (let i = 0; i < maxLength; i += 1) {
                        const l = left[i] ?? 0;
                        const r = right[i] ?? 0;
                        if (l > r) {
                            return 1;
                        }
                        if (l < r) {
                            return -1;
                        }
                    }

                    return 0;
                },

                toTitleCase(value) {
                    return String(value || '')
                        .replace(/[-_]+/g, ' ')
                        .replace(/\b\w/g, (char) => char.toUpperCase());
                },

                pluginInitial(plugin) {
                    return String(plugin.name || 'P').charAt(0).toUpperCase();
                },

                isIconUrl(icon) {
                    return typeof icon === 'string' && /^https?:\/\//i.test(icon);
                },

                statusLabel(plugin) {
                    if (!plugin) {
                        return 'Inactive';
                    }

                    if (plugin.normalized_status === 'active') {
                        return 'Active';
                    }

                    if (plugin.normalized_status === 'error') {
                        return 'Error';
                    }

                    return 'Inactive';
                },

                statusBadgeClass(plugin) {
                    if (!plugin) {
                        return 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300';
                    }

                    if (plugin.normalized_status === 'active') {
                        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
                    }

                    if (plugin.normalized_status === 'error') {
                        return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
                    }

                    return 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300';
                },

                tabButtonClass(tab) {
                    if (this.activeTab === tab) {
                        return 'bg-white dark:bg-[#161B22] shadow text-gray-900 dark:text-white';
                    }

                    return 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
                },

                activateRoute(slug) {
                    return `${this.settingsBaseUrl}/${encodeURIComponent(slug)}/activate`;
                },

                deactivateRoute(slug) {
                    return `${this.settingsBaseUrl}/${encodeURIComponent(slug)}/deactivate`;
                },

                settingsRoute(slug) {
                    return `${this.settingsBaseUrl}/${encodeURIComponent(slug)}/settings`;
                },

                destroyRoute(slug) {
                    return `${this.pluginsApiBaseUrl}/${encodeURIComponent(slug)}`;
                },

                openConfigure(slug, reset = false) {
                    this.closeMenu();
                    const plugin = this.plugins.find((candidate) => candidate.slug === slug);
                    if (!plugin) {
                        return;
                    }

                    this.drawerPlugin = plugin;
                    this.drawerSchema = plugin.settings_schema || [];

                    const currentSettings = plugin.workspace_state && plugin.workspace_state.settings && typeof plugin.workspace_state.settings === 'object'
                        ? plugin.workspace_state.settings
                        : {};

                    const defaults = {};
                    this.drawerSchema.forEach((field) => {
                        defaults[field.key] = Object.prototype.hasOwnProperty.call(field, 'default')
                            ? field.default
                            : '';
                    });

                    this.drawerDefaults = defaults;
                    this.drawerSettings = {};

                    this.drawerSchema.forEach((field) => {
                        const hasCurrent = Object.prototype.hasOwnProperty.call(currentSettings, field.key);
                        this.drawerSettings[field.key] = hasCurrent
                            ? currentSettings[field.key]
                            : (Object.prototype.hasOwnProperty.call(defaults, field.key) ? defaults[field.key] : '');
                    });

                    if (reset) {
                        this.resetDrawerSettings();
                    }

                    this.drawerOpen = true;
                },

                openDetails(slug) {
                    this.openConfigure(slug, false);
                },

                closeDrawer() {
                    this.drawerOpen = false;
                    this.drawerPlugin = null;
                    this.drawerSchema = [];
                    this.drawerSettings = {};
                    this.drawerDefaults = {};
                },

                resetDrawerSettings() {
                    this.drawerSchema.forEach((field) => {
                        this.drawerSettings[field.key] = Object.prototype.hasOwnProperty.call(this.drawerDefaults, field.key)
                            ? this.drawerDefaults[field.key]
                            : '';
                    });
                },

                openInstallModal() {
                    this.installModalOpen = true;
                },

                closeInstallModal() {
                    this.installModalOpen = false;
                },

                toggleMenu(slug) {
                    this.openMenuSlug = this.openMenuSlug === slug ? null : slug;
                },

                closeMenu() {
                    this.openMenuSlug = null;
                },

                resetFilters() {
                    this.query = '';
                    this.statusFilter = 'all';
                    this.typeFilter = 'all';
                    this.categoryFilter = 'all';
                    this.sortBy = 'name';
                },

                async uninstallPlugin(slug) {
                    this.closeMenu();
                    const plugin = this.plugins.find((candidate) => candidate.slug === slug);
                    if (!plugin) {
                        return;
                    }

                    if (plugin.is_core) {
                        window.alert('Core plugins cannot be uninstalled.');
                        return;
                    }

                    if (!window.confirm(`Uninstall ${plugin.name}? This action cannot be undone.`)) {
                        return;
                    }

                    try {
                        const response = await window.fetch(this.destroyRoute(slug), {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            let message = 'Unable to uninstall plugin.';
                            try {
                                const payload = await response.json();
                                if (payload && payload.errors && payload.errors.plugin && payload.errors.plugin[0]) {
                                    message = payload.errors.plugin[0];
                                }
                            } catch (_) {
                                // no-op
                            }
                            window.alert(message);
                            return;
                        }

                        window.location.reload();
                    } catch (_) {
                        window.alert('Unable to uninstall plugin right now. Please try again.');
                    }
                },
            };
        }
    </script>
</x-app-layout>
