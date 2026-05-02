<x-app-layout>
    @php
        $builderConfig = $builderConfig ?? [];
        $workflow = $automation;
        $triggerOptions = $builderConfig['trigger_options'] ?? [
            'date_based' => 'Date based',
            'event_based' => 'Event based',
            'tag_added' => 'Tag added',
            'form_submitted' => 'Form submitted',
            'purchase_made' => 'Purchase made',
            'manual' => 'Manual',
            'api' => 'API / webhook',
        ];
        $statusOptions = [
            'draft' => 'Draft',
            'active' => 'Live',
            'paused' => 'Paused',
        ];
        $saveUrl = $builderConfig['save_url'] ?? '';
        $previewUrl = $builderConfig['preview_url'] ?? '';
        $publishUrl = $builderConfig['publish_url'] ?? '';
        $title = $builderConfig['title'] ?? 'Visual Workflow Builder';
        $subtitle = $builderConfig['subtitle'] ?? 'Design multi-channel customer journeys with drag and drop.';
        $headerActionLabel = $builderConfig['header_action_label'] ?? null;
        $headerActionUrl = $builderConfig['header_action_url'] ?? null;
        $headerActionMethod = strtolower($builderConfig['header_action_method'] ?? 'get');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $title }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
            </div>

            @if($headerActionLabel && $headerActionUrl)
                @if($headerActionMethod === 'post')
                    <form action="{{ $headerActionUrl }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center rounded-xl bg-brand-orange px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600">
                            {{ $headerActionLabel }}
                        </button>
                    </form>
                @else
                    <a href="{{ $headerActionUrl }}"
                       class="inline-flex items-center rounded-xl bg-brand-orange px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600">
                        {{ $headerActionLabel }}
                    </a>
                @endif
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-[1800px] px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="wf-shell overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_24px_80px_-32px_rgba(15,23,42,0.35)] dark:border-white/[0.08] dark:bg-[#0b1020]">
                <div class="wf-topbar border-b border-slate-200/80 bg-gradient-to-r from-slate-50 via-white to-sky-50/60 px-4 py-4 dark:border-white/[0.08] dark:from-[#111827] dark:via-[#0f172a] dark:to-[#0c162b]">
                    <div class="grid gap-4 xl:grid-cols-[minmax(240px,1.4fr)_180px_220px_auto]">
                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Workflow Name</label>
                            <input id="workflow-name"
                                   type="text"
                                   value="{{ $workflow->name }}"
                                   class="wf-input h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-500/10">
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Status</label>
                            <select id="workflow-status"
                                    class="wf-input h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-500/10">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($workflow->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">{{ $builderConfig['trigger_label'] ?? 'Trigger Type' }}</label>
                            <select id="workflow-trigger"
                                    class="wf-input h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 shadow-sm outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-500/10">
                                @foreach($triggerOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($workflow->trigger_type === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col justify-end gap-3 xl:items-end">
                            <div class="flex flex-wrap items-center gap-2">
                                <span id="sync-badge" class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">Draft synced</span>
                                <span id="connect-indicator" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-slate-300">Connect Mode: Off</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button id="undo-button" type="button" class="wf-action-btn">Undo</button>
                                <button id="redo-button" type="button" class="wf-action-btn">Redo</button>
                                <button id="toggle-connect-button" type="button" class="wf-action-btn">Connect</button>
                                <button id="preview-button" type="button" class="wf-action-btn">Preview</button>
                                <button id="save-button" type="button" class="rounded-2xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">Save</button>
                                <button id="publish-button" type="button" class="rounded-2xl bg-brand-orange px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600">Publish</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="wf-stat-card">
                            <p class="wf-stat-label">Workflow Version</p>
                            <p id="version-value" class="wf-stat-value">{{ (int) ($workflow->builder_version ?? 1) }}</p>
                        </div>
                        <div class="wf-stat-card">
                            <p class="wf-stat-label">Blocks On Canvas</p>
                            <p id="node-count" class="wf-stat-value">0</p>
                        </div>
                        <div class="wf-stat-card">
                            <p class="wf-stat-label">Connections</p>
                            <p id="edge-count" class="wf-stat-value">0</p>
                        </div>
                        <div class="wf-stat-card">
                            <p class="wf-stat-label">Active Triggers</p>
                            <p id="trigger-count" class="wf-stat-value">1 / 1</p>
                        </div>
                    </div>
                </div>

                <div class="grid min-h-[78vh] xl:grid-cols-[minmax(720px,1fr)_360px]">
                    <div class="wf-workspace relative overflow-hidden border-r border-slate-200/80 bg-[#f5f8ff] dark:border-white/[0.08] dark:bg-[#081120]">
                        <div class="wf-canvas-toolbar">
                            <div class="flex items-center gap-2">
                                <button id="zoom-out-button" type="button" class="wf-icon-btn">-</button>
                                <button id="zoom-reset-button" type="button" class="wf-icon-btn"><span id="zoom-value">100%</span></button>
                                <button id="zoom-in-button" type="button" class="wf-icon-btn">+</button>
                            </div>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Pan with drag. Drop blocks anywhere.</p>
                        </div>

                        <div id="canvas-viewport" class="wf-canvas-viewport">
                            <svg id="edges-layer" class="wf-edges-layer" aria-hidden="true"></svg>
                            <div id="stage" class="wf-stage"></div>

                            <div id="canvas-hint" class="wf-canvas-hint">
                                <div class="rounded-3xl border border-sky-200/70 bg-white/95 px-5 py-4 shadow-lg backdrop-blur dark:border-sky-400/20 dark:bg-slate-900/90">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Drag blocks from the panel or click to add them.</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Select a block to edit its settings, then connect steps to build the journey.</p>
                                </div>
                            </div>
                        </div>

                        <div class="wf-minimap-shell">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Mini Map</p>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">Drag the canvas to explore</span>
                            </div>
                            <div id="minimap" class="wf-minimap">
                                <div id="minimap-nodes" class="absolute inset-0"></div>
                                <div id="minimap-viewport" class="wf-minimap-viewport"></div>
                            </div>
                        </div>
                    </div>

                    <aside class="wf-panel bg-white dark:bg-[#0c1323]">
                        <div class="border-b border-slate-200/80 px-5 py-4 dark:border-white/[0.08]">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p id="panel-eyebrow" class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Block Library</p>
                                    <h3 id="panel-title" class="text-lg font-semibold text-slate-900 dark:text-white">Add a step</h3>
                                </div>
                                <button id="clear-selection-button" type="button" class="wf-icon-btn hidden">X</button>
                            </div>
                        </div>

                        <div id="library-panel" class="h-full overflow-y-auto px-5 py-5">
                            <div class="mb-4">
                                <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Search blocks</label>
                                <input id="block-search"
                                       type="text"
                                       placeholder="Send message, delay, goal..."
                                       class="wf-input h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-500/10">
                            </div>

                            <div class="space-y-5">
                                <div id="library-groups"></div>
                            </div>
                        </div>

                        <div id="config-panel" class="hidden h-full overflow-y-auto px-5 py-5">
                            <div class="space-y-5">
                                <div>
                                    <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Block Name</label>
                                    <input id="node-label-input" type="text" class="wf-input h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-500/10">
                                </div>

                                <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-4 dark:border-white/[0.08] dark:bg-white/[0.03]">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Block Type</p>
                                            <p id="node-type-pill" class="mt-1 text-sm font-semibold text-slate-900 dark:text-white"></p>
                                        </div>
                                        <button id="delete-node-button" type="button" class="rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                <div id="config-fields" class="space-y-4"></div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>

    <div id="preview-modal" class="hidden fixed inset-0 z-50 bg-slate-950/55 px-4 py-8 backdrop-blur-sm">
        <div class="mx-auto max-w-3xl rounded-[28px] border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/[0.08] dark:bg-[#0c1323]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Preview Path</p>
                    <h3 class="text-xl font-semibold text-slate-900 dark:text-white">Workflow simulation</h3>
                </div>
                <button id="close-preview-button" type="button" class="wf-icon-btn">X</button>
            </div>
            <div id="preview-content" class="mt-6 space-y-3"></div>
        </div>
    </div>

    <style>
        .wf-shell {
            background-image:
                radial-gradient(circle at top left, rgba(56, 189, 248, 0.08), transparent 18rem),
                radial-gradient(circle at bottom right, rgba(249, 115, 22, 0.08), transparent 20rem);
        }

        .wf-action-btn {
            border-radius: 1rem;
            border: 1px solid rgb(226 232 240 / 1);
            background: rgba(255, 255, 255, 0.92);
            padding: 0.625rem 0.95rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(51 65 85 / 1);
            transition: 150ms ease;
        }

        .dark .wf-action-btn {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: rgb(226 232 240 / 1);
        }

        .wf-action-btn:hover {
            transform: translateY(-1px);
            border-color: rgb(125 211 252 / 0.95);
        }

        .wf-stat-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.84);
            padding: 0.95rem 1rem;
            box-shadow: 0 10px 30px -24px rgba(15, 23, 42, 0.7);
        }

        .dark .wf-stat-card {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
        }

        .wf-stat-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgb(100 116 139 / 1);
        }

        .wf-stat-value {
            margin-top: 0.35rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: rgb(15 23 42 / 1);
        }

        .dark .wf-stat-value {
            color: white;
        }

        .wf-canvas-toolbar {
            position: absolute;
            left: 1rem;
            right: 1rem;
            top: 1rem;
            z-index: 14;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-radius: 1.5rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.88);
            padding: 0.75rem 1rem;
            backdrop-filter: blur(12px);
        }

        .dark .wf-canvas-toolbar {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.74);
        }

        .wf-icon-btn {
            display: inline-flex;
            min-width: 2.2rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.95rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.94);
            padding: 0.55rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: rgb(51 65 85 / 1);
            transition: 150ms ease;
        }

        .dark .wf-icon-btn {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: rgb(226 232 240 / 1);
        }

        .wf-icon-btn:hover {
            transform: translateY(-1px);
        }

        .wf-canvas-viewport {
            position: relative;
            height: 100%;
            min-height: 78vh;
            overflow: hidden;
            cursor: grab;
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.18) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.18) 1px, transparent 1px),
                radial-gradient(circle at top left, rgba(125, 211, 252, 0.18), transparent 20rem),
                radial-gradient(circle at bottom right, rgba(251, 191, 36, 0.14), transparent 16rem);
            background-size: 32px 32px, 32px 32px, auto, auto;
            background-position: 0 0, 0 0, 0 0, 100% 100%;
        }

        .dark .wf-canvas-viewport {
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.12) 1px, transparent 1px),
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.16), transparent 20rem),
                radial-gradient(circle at bottom right, rgba(249, 115, 22, 0.12), transparent 16rem);
        }

        .wf-canvas-viewport.is-panning {
            cursor: grabbing;
        }

        .wf-stage {
            position: absolute;
            left: 0;
            top: 0;
            width: 4800px;
            height: 3200px;
            transform-origin: 0 0;
        }

        .wf-edges-layer {
            position: absolute;
            inset: 0;
            z-index: 2;
            overflow: visible;
            pointer-events: none;
        }

        .wf-canvas-hint {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 1;
            transform: translate(-50%, -50%);
            pointer-events: none;
            transition: opacity 150ms ease;
        }

        .wf-node {
            position: absolute;
            z-index: 3;
            width: 270px;
            user-select: none;
            cursor: pointer;
            transition: transform 150ms ease, box-shadow 150ms ease;
        }

        .wf-node-card {
            position: relative;
            border-radius: 1.35rem;
            border: 1px solid rgba(226, 232, 240, 0.92);
            background: rgba(255, 255, 255, 0.97);
            padding: 1rem 1rem 0.95rem;
            box-shadow: 0 22px 36px -28px rgba(15, 23, 42, 0.7);
        }

        .dark .wf-node-card {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.94);
        }

        .wf-node:hover {
            transform: translateY(-2px);
        }

        .wf-node.is-selected .wf-node-card {
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18), 0 28px 46px -28px rgba(14, 165, 233, 0.65);
            border-color: rgba(56, 189, 248, 0.85);
        }

        .wf-node-accent {
            position: absolute;
            inset: 0;
            border-radius: 1.35rem;
            opacity: 0.16;
        }

        .wf-node-header {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .wf-node-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.32rem 0.65rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .wf-node-handle {
            position: absolute;
            top: 50%;
            width: 14px;
            height: 14px;
            border-radius: 999px;
            border: 3px solid white;
            box-shadow: 0 0 0 1px rgba(148, 163, 184, 0.45);
            transform: translateY(-50%);
            background: white;
        }

        .dark .wf-node-handle {
            border-color: rgb(15 23 42 / 1);
            background: rgb(226 232 240 / 1);
        }

        .wf-node-handle.input {
            left: -7px;
        }

        .wf-node-handle.output {
            right: -7px;
        }

        .wf-minimap-shell {
            position: absolute;
            right: 1rem;
            bottom: 1rem;
            z-index: 15;
            width: 220px;
            border-radius: 1.4rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.9);
            padding: 0.85rem;
            backdrop-filter: blur(12px);
        }

        .dark .wf-minimap-shell {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.82);
        }

        .wf-minimap {
            position: relative;
            height: 132px;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.96), rgba(226, 232, 240, 0.88));
        }

        .dark .wf-minimap {
            border-color: rgba(255, 255, 255, 0.08);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.8));
        }

        .wf-minimap-viewport {
            position: absolute;
            border: 2px solid rgba(14, 165, 233, 0.75);
            border-radius: 0.6rem;
            background: rgba(14, 165, 233, 0.12);
        }

        .wf-library-group-title {
            margin-bottom: 0.75rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgb(100 116 139 / 1);
        }

        .wf-library-card {
            position: relative;
            display: block;
            width: 100%;
            cursor: grab;
            overflow: hidden;
            border-radius: 1.35rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem;
            text-align: left;
            transition: 150ms ease;
        }

        .wf-library-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 36px -28px rgba(15, 23, 42, 0.75);
        }

        .dark .wf-library-card {
            border-color: rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
        }

        .wf-library-card:active {
            cursor: grabbing;
        }

        .wf-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.22rem 0.55rem;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .wf-rich-field {
            min-height: 130px;
            resize: vertical;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const stageSize = { width: 4800, height: 3200 };
            const csrfToken = '{{ csrf_token() }}';

            const initialNodes = @json($workflow->visual_nodes ?? []);
            const initialEdges = @json($workflow->visual_edges ?? []);
            const saveUrl = @json($saveUrl);
            const previewUrl = @json($previewUrl);
            const publishUrl = @json($publishUrl);
            const triggerOptions = @json($triggerOptions);

            const viewportEl = document.getElementById('canvas-viewport');
            const stageEl = document.getElementById('stage');
            const edgesLayerEl = document.getElementById('edges-layer');
            const minimapNodesEl = document.getElementById('minimap-nodes');
            const minimapViewportEl = document.getElementById('minimap-viewport');
            const libraryGroupsEl = document.getElementById('library-groups');
            const libraryPanelEl = document.getElementById('library-panel');
            const configPanelEl = document.getElementById('config-panel');
            const panelEyebrowEl = document.getElementById('panel-eyebrow');
            const panelTitleEl = document.getElementById('panel-title');
            const clearSelectionButton = document.getElementById('clear-selection-button');
            const nodeLabelInput = document.getElementById('node-label-input');
            const nodeTypePill = document.getElementById('node-type-pill');
            const configFieldsEl = document.getElementById('config-fields');
            const canvasHintEl = document.getElementById('canvas-hint');
            const syncBadgeEl = document.getElementById('sync-badge');
            const connectIndicatorEl = document.getElementById('connect-indicator');
            const nodeCountEl = document.getElementById('node-count');
            const edgeCountEl = document.getElementById('edge-count');
            const triggerCountEl = document.getElementById('trigger-count');
            const zoomValueEl = document.getElementById('zoom-value');
            const versionValueEl = document.getElementById('version-value');
            const previewModalEl = document.getElementById('preview-modal');
            const previewContentEl = document.getElementById('preview-content');
            const workflowNameEl = document.getElementById('workflow-name');
            const workflowStatusEl = document.getElementById('workflow-status');
            const workflowTriggerEl = document.getElementById('workflow-trigger');
            const blockSearchEl = document.getElementById('block-search');

            const blockCatalog = [
                {
                    type: 'trigger',
                    title: 'Trigger',
                    description: 'Start on a date, event, tag, form submission, purchase, or API call.',
                    category: 'Triggers & Goals',
                    color: '#2563eb',
                    badge: 'Start',
                    defaults: { label: 'Workflow Trigger', config: { scheduleType: 'specific_date', date: '', time: '', timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC' } }
                },
                {
                    type: 'send_message',
                    title: 'Send Message',
                    description: 'Send one personalized Email, WhatsApp, Instagram, or SMS message.',
                    category: 'Messages',
                    color: '#16a34a',
                    badge: 'Message',
                    defaults: { label: 'Send Message', config: { channel: 'email', subject: '', message: '', variables: '@{{first_name}}, @{{product}}', attachments: '', preview: 'email' } }
                },
                {
                    type: 'send_sequence',
                    title: 'Send Sequence',
                    description: 'Chain multiple messages with delays between each step.',
                    category: 'Messages',
                    color: '#10b981',
                    badge: 'Sequence',
                    defaults: { label: 'Send Sequence', config: { sequence: [{ channel: 'email', message: '', delay_value: 1, delay_unit: 'days' }] } }
                },
                {
                    type: 'delay',
                    title: 'Delay',
                    description: 'Wait minutes, hours, days, or until a specific date.',
                    category: 'Logic',
                    color: '#0f766e',
                    badge: 'Delay',
                    defaults: { label: 'Delay', config: { mode: 'days', value: 1, scheduled_at: '' } }
                },
                {
                    type: 'branch',
                    title: 'Branch Path',
                    description: 'Split contacts by tag, email activity, purchase, or custom condition.',
                    category: 'Logic',
                    color: '#7c3aed',
                    badge: 'Branch',
                    defaults: { label: 'Branch Path', config: { field: 'tags', operator: 'contains', value: 'vip', yesLabel: 'Yes', noLabel: 'No' } }
                },
                {
                    type: 'tag',
                    title: 'Add/Remove Tag',
                    description: 'Update customer tags based on workflow behavior.',
                    category: 'Actions',
                    color: '#ea580c',
                    badge: 'Tag',
                    defaults: { label: 'Add / Remove Tag', config: { action: 'add', tag: 'engaged' } }
                },
                {
                    type: 'goal',
                    title: 'Goal',
                    description: 'Mark a conversion milestone and track completion.',
                    category: 'Triggers & Goals',
                    color: '#2563eb',
                    badge: 'Goal',
                    defaults: { label: 'Goal', config: { goal_name: 'Converted', condition: 'purchase_made' } }
                },
                {
                    type: 'end',
                    title: 'End Workflow',
                    description: 'Stop the journey with a clear reason or note.',
                    category: 'Actions',
                    color: '#475569',
                    badge: 'End',
                    defaults: { label: 'End Workflow', config: { reason: 'Sequence completed' } }
                }
            ];

            const categoryOrder = ['Messages', 'Logic', 'Actions', 'Triggers & Goals'];

            const state = {
                nodes: Array.isArray(initialNodes) ? initialNodes.map(normalizeNode) : [],
                edges: Array.isArray(initialEdges) ? initialEdges.map(normalizeEdge) : [],
                selectedNodeId: null,
                viewport: { x: 380, y: 180, zoom: 1 },
                isPanning: false,
                isDraggingNode: false,
                dragNodeId: null,
                dragOffsetX: 0,
                dragOffsetY: 0,
                panStartX: 0,
                panStartY: 0,
                connectMode: false,
                connectSourceId: null,
                history: [],
                future: [],
                dirty: false,
                autosaveTimer: null,
                librarySearch: '',
                dragLibraryItem: null,
                toastTimer: null,
                saveVersion: {{ (int) ($workflow->builder_version ?? 1) }},
            };

            function normalizeNode(node) {
                return {
                    id: String(node.id || createId('node')),
                    type: node.type || 'send_message',
                    label: node.label || catalogItem(node.type)?.defaults?.label || 'Untitled',
                    x: Number(node.x || 0),
                    y: Number(node.y || 0),
                    config: node.config || {},
                };
            }

            function normalizeEdge(edge) {
                return {
                    id: String(edge.id || createId('edge')),
                    source: String(edge.source || ''),
                    target: String(edge.target || ''),
                    label: edge.label || '',
                };
            }

            function catalogItem(type) {
                return blockCatalog.find((item) => item.type === type) || blockCatalog[0];
            }

            function createId(prefix) {
                if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                    return `${prefix}_${window.crypto.randomUUID().replace(/-/g, '').slice(0, 10)}`;
                }

                return `${prefix}_${Math.random().toString(36).slice(2, 10)}`;
            }

            function cloneStateSnapshot() {
                return {
                    nodes: JSON.parse(JSON.stringify(state.nodes)),
                    edges: JSON.parse(JSON.stringify(state.edges)),
                    selectedNodeId: state.selectedNodeId,
                    viewport: { ...state.viewport },
                };
            }

            function pushHistory() {
                state.history.push(cloneStateSnapshot());
                if (state.history.length > 80) {
                    state.history.shift();
                }
                state.future = [];
            }

            function markDirty(statusText = 'Draft not synced') {
                state.dirty = true;
                syncBadgeEl.textContent = statusText;
                syncBadgeEl.className = 'rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300';
                refreshStats();
            }

            function markSynced(text = 'Draft synced') {
                state.dirty = false;
                syncBadgeEl.textContent = text;
                syncBadgeEl.className = 'rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300';
            }

            function showError(text) {
                syncBadgeEl.textContent = text;
                syncBadgeEl.className = 'rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300';
            }

            function restoreSnapshot(snapshot) {
                state.nodes = JSON.parse(JSON.stringify(snapshot.nodes));
                state.edges = JSON.parse(JSON.stringify(snapshot.edges));
                state.selectedNodeId = snapshot.selectedNodeId;
                state.viewport = { ...snapshot.viewport };
                render();
            }

            function undo() {
                if (!state.history.length) return;
                state.future.push(cloneStateSnapshot());
                const snapshot = state.history.pop();
                restoreSnapshot(snapshot);
                markDirty();
            }

            function redo() {
                if (!state.future.length) return;
                state.history.push(cloneStateSnapshot());
                const snapshot = state.future.pop();
                restoreSnapshot(snapshot);
                markDirty();
            }

            function renderLibrary() {
                const search = state.librarySearch.trim().toLowerCase();
                const grouped = {};

                blockCatalog.forEach((item) => {
                    if (search && !`${item.title} ${item.description} ${item.category}`.toLowerCase().includes(search)) {
                        return;
                    }

                    if (!grouped[item.category]) {
                        grouped[item.category] = [];
                    }

                    grouped[item.category].push(item);
                });

                libraryGroupsEl.innerHTML = '';

                categoryOrder.forEach((category) => {
                    const items = grouped[category] || [];
                    if (!items.length) return;

                    const section = document.createElement('section');
                    section.className = 'space-y-3';

                    const heading = document.createElement('h4');
                    heading.className = 'wf-library-group-title';
                    heading.textContent = category;
                    section.appendChild(heading);

                    const stack = document.createElement('div');
                    stack.className = 'space-y-3';

                    items.forEach((item) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'wf-library-card';
                        button.draggable = true;
                        button.dataset.blockType = item.type;
                        button.innerHTML = `
                            <div class="absolute inset-y-0 left-0 w-1.5" style="background:${item.color};"></div>
                            <div class="pl-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <span class="wf-chip" style="background:${hexToRgba(item.color, 0.12)}; color:${item.color};">${item.badge}</span>
                                        <h5 class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">${item.title}</h5>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-400">Drag</span>
                                </div>
                                <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">${item.description}</p>
                            </div>
                        `;

                        button.addEventListener('click', () => {
                            addNodeFromCatalog(item.type, getViewportCenterWorld());
                        });

                        button.addEventListener('dragstart', (event) => {
                            state.dragLibraryItem = item.type;
                            event.dataTransfer.setData('text/plain', item.type);
                            event.dataTransfer.effectAllowed = 'copy';
                        });

                        button.addEventListener('dragend', () => {
                            state.dragLibraryItem = null;
                        });

                        stack.appendChild(button);
                    });

                    section.appendChild(stack);
                    libraryGroupsEl.appendChild(section);
                });
            }

            function render() {
                renderStage();
                renderEdges();
                renderPanel();
                renderMinimap();
                refreshStats();
                stageEl.style.transform = `translate(${state.viewport.x}px, ${state.viewport.y}px) scale(${state.viewport.zoom})`;
                zoomValueEl.textContent = `${Math.round(state.viewport.zoom * 100)}%`;
                connectIndicatorEl.textContent = `Connect Mode: ${state.connectMode ? 'On' : 'Off'}`;
                canvasHintEl.style.opacity = state.nodes.length ? '0' : '1';
                canvasHintEl.style.display = state.nodes.length ? 'none' : 'block';
                viewportEl.classList.toggle('is-panning', state.isPanning);
            }

            function renderCanvasOnly() {
                renderStage();
                renderEdges();
                renderMinimap();
                refreshStats();
                stageEl.style.transform = `translate(${state.viewport.x}px, ${state.viewport.y}px) scale(${state.viewport.zoom})`;
                zoomValueEl.textContent = `${Math.round(state.viewport.zoom * 100)}%`;
                connectIndicatorEl.textContent = `Connect Mode: ${state.connectMode ? 'On' : 'Off'}`;
                canvasHintEl.style.opacity = state.nodes.length ? '0' : '1';
                canvasHintEl.style.display = state.nodes.length ? 'none' : 'block';
                viewportEl.classList.toggle('is-panning', state.isPanning);
            }

            function renderStage() {
                stageEl.innerHTML = '';

                state.nodes.forEach((node) => {
                    const item = catalogItem(node.type);
                    const nodeEl = document.createElement('div');
                    nodeEl.className = `wf-node ${state.selectedNodeId === node.id ? 'is-selected' : ''}`;
                    nodeEl.dataset.nodeId = node.id;
                    nodeEl.style.left = `${node.x}px`;
                    nodeEl.style.top = `${node.y}px`;

                    nodeEl.innerHTML = `
                        <div class="wf-node-card">
                            <div class="wf-node-accent" style="background:${item.color};"></div>
                            <span class="wf-node-handle input"></span>
                            <span class="wf-node-handle output"></span>
                            <div class="wf-node-header">
                                <div class="relative z-[1] min-w-0">
                                    <span class="wf-node-badge" style="background:${hexToRgba(item.color, 0.12)}; color:${item.color};">${item.badge}</span>
                                    <h4 class="mt-3 truncate text-base font-semibold text-slate-900 dark:text-white">${escapeHtml(node.label)}</h4>
                                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">${escapeHtml(nodeSummary(node))}</p>
                                </div>
                                <button type="button" class="relative z-[1] rounded-full border border-slate-200 bg-white/80 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500 dark:border-white/[0.08] dark:bg-white/[0.06] dark:text-slate-300">
                                    ${item.title}
                                </button>
                            </div>
                        </div>
                    `;

                    nodeEl.addEventListener('pointerdown', (event) => onNodePointerDown(event, node.id));
                    nodeEl.addEventListener('click', (event) => onNodeClick(event, node.id));
                    stageEl.appendChild(nodeEl);
                });
            }

            function renderEdges() {
                const viewportRect = viewportEl.getBoundingClientRect();
                edgesLayerEl.setAttribute('width', `${viewportRect.width}`);
                edgesLayerEl.setAttribute('height', `${viewportRect.height}`);
                edgesLayerEl.setAttribute('viewBox', `0 0 ${viewportRect.width} ${viewportRect.height}`);
                edgesLayerEl.innerHTML = '';

                state.edges.forEach((edge) => {
                    const source = findNode(edge.source);
                    const target = findNode(edge.target);
                    if (!source || !target) return;

                    const sourcePoint = worldToViewport({ x: source.x + 270, y: source.y + 62 });
                    const targetPoint = worldToViewport({ x: target.x, y: target.y + 62 });
                    const midX = sourcePoint.x + (targetPoint.x - sourcePoint.x) / 2;
                    const pathData = `M ${sourcePoint.x} ${sourcePoint.y} C ${midX} ${sourcePoint.y}, ${midX} ${targetPoint.y}, ${targetPoint.x} ${targetPoint.y}`;

                    const group = document.createElementNS('http://www.w3.org/2000/svg', 'g');

                    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    path.setAttribute('d', pathData);
                    path.setAttribute('fill', 'none');
                    path.setAttribute('stroke', 'rgba(14,165,233,0.9)');
                    path.setAttribute('stroke-width', '3');
                    path.setAttribute('stroke-linecap', 'round');
                    path.setAttribute('stroke-dasharray', edge.label ? '0' : '0');
                    group.appendChild(path);

                    if (edge.label) {
                        const labelBg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                        labelBg.setAttribute('x', `${midX - 22}`);
                        labelBg.setAttribute('y', `${(sourcePoint.y + targetPoint.y) / 2 - 11}`);
                        labelBg.setAttribute('width', '44');
                        labelBg.setAttribute('height', '22');
                        labelBg.setAttribute('rx', '11');
                        labelBg.setAttribute('fill', 'rgba(255,255,255,0.94)');
                        labelBg.setAttribute('stroke', 'rgba(148,163,184,0.22)');
                        group.appendChild(labelBg);

                        const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                        label.setAttribute('x', `${midX}`);
                        label.setAttribute('y', `${(sourcePoint.y + targetPoint.y) / 2 + 4}`);
                        label.setAttribute('text-anchor', 'middle');
                        label.setAttribute('font-size', '10');
                        label.setAttribute('font-weight', '700');
                        label.setAttribute('fill', '#334155');
                        label.textContent = edge.label;
                        group.appendChild(label);
                    }

                    edgesLayerEl.appendChild(group);
                });
            }

            function renderPanel() {
                const selectedNode = getSelectedNode();
                const showingConfig = !!selectedNode;

                libraryPanelEl.classList.toggle('hidden', showingConfig);
                configPanelEl.classList.toggle('hidden', !showingConfig);
                clearSelectionButton.classList.toggle('hidden', !showingConfig);

                if (!selectedNode) {
                    panelEyebrowEl.textContent = 'Block Library';
                    panelTitleEl.textContent = 'Add a step';
                    return;
                }

                const item = catalogItem(selectedNode.type);
                panelEyebrowEl.textContent = 'Block Configuration';
                panelTitleEl.textContent = item.title;
                nodeLabelInput.value = selectedNode.label || '';
                nodeTypePill.textContent = item.title;
                configFieldsEl.innerHTML = buildConfigMarkup(selectedNode);
                wireConfigFields(selectedNode);
            }

            function renderMinimap() {
                minimapNodesEl.innerHTML = '';
                const scaleX = 220 / stageSize.width;
                const scaleY = 132 / stageSize.height;

                state.nodes.forEach((node) => {
                    const item = catalogItem(node.type);
                    const box = document.createElement('div');
                    box.style.position = 'absolute';
                    box.style.left = `${node.x * scaleX}px`;
                    box.style.top = `${node.y * scaleY}px`;
                    box.style.width = `${Math.max(14, 270 * scaleX)}px`;
                    box.style.height = `${Math.max(10, 92 * scaleY)}px`;
                    box.style.borderRadius = '6px';
                    box.style.background = hexToRgba(item.color, 0.55);
                    minimapNodesEl.appendChild(box);
                });

                const visibleWidth = viewportEl.clientWidth / state.viewport.zoom;
                const visibleHeight = viewportEl.clientHeight / state.viewport.zoom;
                const viewX = Math.max(0, -state.viewport.x / state.viewport.zoom);
                const viewY = Math.max(0, -state.viewport.y / state.viewport.zoom);

                minimapViewportEl.style.left = `${viewX * scaleX}px`;
                minimapViewportEl.style.top = `${viewY * scaleY}px`;
                minimapViewportEl.style.width = `${Math.min(220, visibleWidth * scaleX)}px`;
                minimapViewportEl.style.height = `${Math.min(132, visibleHeight * scaleY)}px`;
            }

            function refreshStats() {
                nodeCountEl.textContent = `${state.nodes.length}`;
                edgeCountEl.textContent = `${state.edges.length}`;
                const triggerCount = state.nodes.filter((node) => node.type === 'trigger').length;
                triggerCountEl.textContent = `${triggerCount} / 1`;
                workflowTriggerEl.value = workflowTriggerEl.value || 'date_based';
                versionValueEl.textContent = `${state.saveVersion}`;
            }

            function hexToRgba(hex, alpha) {
                const value = hex.replace('#', '');
                const bigint = parseInt(value, 16);
                const r = (bigint >> 16) & 255;
                const g = (bigint >> 8) & 255;
                const b = bigint & 255;
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            function nodeSummary(node) {
                const config = node.config || {};

                switch (node.type) {
                    case 'trigger':
                        return `${workflowTriggerEl.value.replace(/_/g, ' ')}${config.date ? ` - ${config.date}` : ''}`;
                    case 'send_message':
                        return `${(config.channel || 'email').toUpperCase()} - ${truncate(config.message || 'Write your message', 42)}`;
                    case 'send_sequence':
                        return `${Array.isArray(config.sequence) ? config.sequence.length : 0} messages in sequence`;
                    case 'delay':
                        return config.mode === 'specific_date'
                            ? `Wait until ${config.scheduled_at || 'specific date'}`
                            : `Wait ${config.value || 1} ${config.mode || 'days'}`;
                    case 'branch':
                        return `${config.field || 'condition'} ${config.operator || 'equals'} ${config.value || ''}`.trim();
                    case 'tag':
                        return `${config.action || 'add'} tag "${config.tag || ''}"`;
                    case 'goal':
                        return `${config.goal_name || 'Goal'} - ${config.condition || 'condition'}`;
                    case 'end':
                        return config.reason || 'Exit workflow';
                    default:
                        return catalogItem(node.type).description;
                }
            }

            function truncate(text, max) {
                const value = String(text || '');
                return value.length > max ? `${value.slice(0, max - 1)}...` : value;
            }

            function escapeHtml(text) {
                return String(text || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function getViewportCenterWorld() {
                return {
                    x: Math.round((viewportEl.clientWidth / 2 - state.viewport.x) / state.viewport.zoom - 135),
                    y: Math.round((viewportEl.clientHeight / 2 - state.viewport.y) / state.viewport.zoom - 46),
                };
            }

            function worldToViewport(point) {
                return {
                    x: point.x * state.viewport.zoom + state.viewport.x,
                    y: point.y * state.viewport.zoom + state.viewport.y,
                };
            }

            function viewportToWorld(clientX, clientY) {
                const rect = viewportEl.getBoundingClientRect();
                return {
                    x: (clientX - rect.left - state.viewport.x) / state.viewport.zoom,
                    y: (clientY - rect.top - state.viewport.y) / state.viewport.zoom,
                };
            }

            function findNode(id) {
                return state.nodes.find((node) => node.id === id) || null;
            }

            function getSelectedNode() {
                return findNode(state.selectedNodeId);
            }

            function addNodeFromCatalog(type, position) {
                const item = catalogItem(type);
                if (type === 'trigger' && state.nodes.some((node) => node.type === 'trigger')) {
                    showError('Only one trigger is allowed.');
                    window.setTimeout(() => markDirty(), 1600);
                    return;
                }

                pushHistory();
                const node = {
                    id: createId(type),
                    type,
                    label: item.defaults.label,
                    x: Math.max(24, Math.min(stageSize.width - 320, position.x)),
                    y: Math.max(24, Math.min(stageSize.height - 120, position.y)),
                    config: JSON.parse(JSON.stringify(item.defaults.config || {})),
                };
                state.nodes.push(node);
                state.selectedNodeId = node.id;
                markDirty();
                render();
            }

            function updateNode(id, updates) {
                const node = findNode(id);
                if (!node) return;
                Object.assign(node, updates);
                markDirty();
                renderCanvasOnly();
            }

            function updateNodeConfig(id, key, value) {
                const node = findNode(id);
                if (!node) return;
                node.config = node.config || {};
                node.config[key] = value;
                if (node.type === 'trigger' && key === 'scheduleType') {
                    workflowTriggerEl.value = value === 'specific_date' ? 'date_based' : workflowTriggerEl.value;
                }
                markDirty();
                renderCanvasOnly();
            }

            function deleteSelectedNode() {
                const selectedNode = getSelectedNode();
                if (!selectedNode) return;
                pushHistory();
                state.nodes = state.nodes.filter((node) => node.id !== selectedNode.id);
                state.edges = state.edges.filter((edge) => edge.source !== selectedNode.id && edge.target !== selectedNode.id);
                state.selectedNodeId = null;
                state.connectSourceId = null;
                markDirty();
                render();
            }

            function onNodePointerDown(event, nodeId) {
                if (event.button !== 0) return;
                if (state.connectMode) return;

                const node = findNode(nodeId);
                if (!node) return;

                pushHistory();
                state.isDraggingNode = true;
                state.dragNodeId = nodeId;
                const point = viewportToWorld(event.clientX, event.clientY);
                state.dragOffsetX = point.x - node.x;
                state.dragOffsetY = point.y - node.y;
                viewportEl.setPointerCapture(event.pointerId);
            }

            function onNodeClick(event, nodeId) {
                event.stopPropagation();
                if (state.connectMode) {
                    handleConnectClick(nodeId);
                    return;
                }

                state.selectedNodeId = nodeId;
                render();
            }

            function handleConnectClick(nodeId) {
                if (!state.connectSourceId) {
                    state.connectSourceId = nodeId;
                    state.selectedNodeId = nodeId;
                    render();
                    return;
                }

                if (state.connectSourceId === nodeId) {
                    state.connectSourceId = null;
                    render();
                    return;
                }

                pushHistory();
                const sourceNode = findNode(state.connectSourceId);
                const nextLabel = sourceNode && sourceNode.type === 'branch'
                    ? branchEdgeLabel(sourceNode.id)
                    : '';

                const existing = state.edges.find((edge) => edge.source === state.connectSourceId && edge.target === nodeId && edge.label === nextLabel);
                if (!existing) {
                    state.edges.push({
                        id: createId('edge'),
                        source: state.connectSourceId,
                        target: nodeId,
                        label: nextLabel,
                    });
                }

                state.connectSourceId = null;
                state.selectedNodeId = nodeId;
                markDirty();
                render();
            }

            function branchEdgeLabel(sourceId) {
                const labels = state.edges
                    .filter((edge) => edge.source === sourceId)
                    .map((edge) => String(edge.label || '').toLowerCase());

                if (!labels.includes('yes')) return 'yes';
                if (!labels.includes('no')) return 'no';
                return '';
            }

            function buildConfigMarkup(node) {
                const config = node.config || {};

                switch (node.type) {
                    case 'trigger':
                        return `
                            ${fieldGroup('Trigger Source', selectField('config-schedule-type', [
                                { value: 'specific_date', label: 'Specific date & time' },
                                { value: 'event', label: 'Event based' },
                                { value: 'manual', label: 'Manual enrollment' },
                                { value: 'webhook', label: 'API / webhook' }
                            ], config.scheduleType || 'specific_date'))}
                            ${dualFieldRow(
                                fieldGroup('Date', inputField('config-date', config.date || '', 'date')),
                                fieldGroup('Time', inputField('config-time', config.time || '', 'time'))
                            )}
                            ${fieldGroup('Timezone', inputField('config-timezone', config.timezone || 'UTC', 'text', 'America/Los_Angeles'))}
                            ${fieldGroup('Notes', textareaField('config-notes', config.notes || '', 'Optional context for the trigger'))}
                        `;
                    case 'send_message':
                        return `
                            ${fieldGroup('Channel', selectField('config-channel', [
                                { value: 'email', label: 'Email' },
                                { value: 'whatsapp', label: 'WhatsApp' },
                                { value: 'instagram', label: 'Instagram' },
                                { value: 'sms', label: 'SMS' }
                            ], config.channel || 'email'))}
                            ${fieldGroup('Subject Line', inputField('config-subject', config.subject || '', 'text', 'Welcome to the offer'))}
                            ${fieldGroup('Message Body', textareaField('config-message', config.message || '', 'Use variables like @{{first_name}} and @{{product}}', true))}
                            ${fieldGroup('Variables', inputField('config-variables', config.variables || '@{{first_name}}, @{{product}}', 'text'))}
                            ${fieldGroup('Attachments', inputField('config-attachments', config.attachments || '', 'text', 'image.jpg, brochure.pdf'))}
                        `;
                    case 'send_sequence':
                        return `
                            ${fieldGroup('Sequence Preview', sequenceEditorMarkup(config.sequence || []))}
                            ${fieldGroup('Tips', infoCard('Use one line per message. Format: channel | delay_value | delay_unit | message'))}
                        `;
                    case 'delay':
                        return `
                            ${fieldGroup('Delay Mode', selectField('config-delay-mode', [
                                { value: 'minutes', label: 'Minutes' },
                                { value: 'hours', label: 'Hours' },
                                { value: 'days', label: 'Days' },
                                { value: 'specific_date', label: 'Specific date' }
                            ], config.mode || 'days'))}
                            ${fieldGroup('Delay Value', inputField('config-delay-value', config.value || 1, 'number', '1', false, '1'))}
                            ${fieldGroup('Specific Date / Time', inputField('config-delay-scheduled-at', config.scheduled_at || '', 'datetime-local'))}
                        `;
                    case 'branch':
                        return `
                            ${fieldGroup('Condition Field', inputField('config-field', config.field || 'tags', 'text', 'tags'))}
                            ${fieldGroup('Operator', selectField('config-operator', [
                                { value: 'equals', label: 'Equals' },
                                { value: 'not_equals', label: 'Does not equal' },
                                { value: 'contains', label: 'Contains' },
                                { value: 'exists', label: 'Exists' }
                            ], config.operator || 'contains'))}
                            ${fieldGroup('Expected Value', inputField('config-value', config.value || 'vip', 'text'))}
                            ${dualFieldRow(
                                fieldGroup('Yes Label', inputField('config-yes-label', config.yesLabel || 'Yes', 'text')),
                                fieldGroup('No Label', inputField('config-no-label', config.noLabel || 'No', 'text'))
                            )}
                        `;
                    case 'tag':
                        return `
                            ${fieldGroup('Action', selectField('config-tag-action', [
                                { value: 'add', label: 'Add tag' },
                                { value: 'remove', label: 'Remove tag' }
                            ], config.action || 'add'))}
                            ${fieldGroup('Tag Name', inputField('config-tag-name', config.tag || 'engaged', 'text'))}
                        `;
                    case 'goal':
                        return `
                            ${fieldGroup('Goal Name', inputField('config-goal-name', config.goal_name || 'Converted', 'text'))}
                            ${fieldGroup('Condition', inputField('config-goal-condition', config.condition || 'purchase_made', 'text', 'purchase_made'))}
                        `;
                    case 'end':
                        return `
                            ${fieldGroup('Reason / Note', textareaField('config-end-reason', config.reason || 'Sequence completed', 'Why this workflow ends here'))}
                        `;
                    default:
                        return infoCard('Select a supported block to configure its behavior.');
                }
            }

            function fieldGroup(label, fieldHtml) {
                return `
                    <div>
                        <label class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">${label}</label>
                        ${fieldHtml}
                    </div>
                `;
            }

            function dualFieldRow(left, right) {
                return `<div class="grid gap-4 md:grid-cols-2">${left}${right}</div>`;
            }

            function inputField(id, value, type = 'text', placeholder = '', required = false, min = '') {
                const minAttr = min !== '' ? `min="${min}"` : '';
                const requiredAttr = required ? 'required' : '';
                return `<input id="${id}" type="${type}" value="${escapeHtml(value)}" placeholder="${escapeHtml(placeholder)}" ${requiredAttr} ${minAttr} class="wf-input h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-500/10">`;
            }

            function textareaField(id, value, placeholder = '', rich = false) {
                return `<textarea id="${id}" placeholder="${escapeHtml(placeholder)}" class="wf-input ${rich ? 'wf-rich-field' : 'min-h-[96px]'} w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-500/10">${escapeHtml(value)}</textarea>`;
            }

            function selectField(id, options, current) {
                const optionsHtml = options.map((option) => `<option value="${option.value}" ${option.value === current ? 'selected' : ''}>${option.label}</option>`).join('');
                return `<select id="${id}" class="wf-input h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-white dark:focus:border-sky-400 dark:focus:ring-sky-500/10">${optionsHtml}</select>`;
            }

            function infoCard(text) {
                return `<div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-200">${text}</div>`;
            }

            function sequenceEditorMarkup(sequence) {
                const lines = sequence.length
                    ? sequence.map((item) => `${item.channel || 'email'} | ${item.delay_value || 1} | ${item.delay_unit || 'days'} | ${item.message || ''}`).join('\n')
                    : 'email | 1 | days | Welcome to the workflow';

                return textareaField('config-sequence-lines', lines, 'email | 1 | days | Message text');
            }

            function wireConfigFields(node) {
                nodeLabelInput.oninput = (event) => {
                    node.label = event.target.value;
                    markDirty();
                    renderCanvasOnly();
                };

                const bind = (id, callback, eventName = 'input') => {
                    const field = document.getElementById(id);
                    if (!field) return;
                    field.addEventListener(eventName, callback);
                };

                switch (node.type) {
                    case 'trigger':
                        bind('config-schedule-type', (event) => updateNodeConfig(node.id, 'scheduleType', event.target.value), 'change');
                        bind('config-date', (event) => updateNodeConfig(node.id, 'date', event.target.value));
                        bind('config-time', (event) => updateNodeConfig(node.id, 'time', event.target.value));
                        bind('config-timezone', (event) => updateNodeConfig(node.id, 'timezone', event.target.value));
                        bind('config-notes', (event) => updateNodeConfig(node.id, 'notes', event.target.value));
                        break;
                    case 'send_message':
                        bind('config-channel', (event) => updateNodeConfig(node.id, 'channel', event.target.value), 'change');
                        bind('config-subject', (event) => updateNodeConfig(node.id, 'subject', event.target.value));
                        bind('config-message', (event) => updateNodeConfig(node.id, 'message', event.target.value));
                        bind('config-variables', (event) => updateNodeConfig(node.id, 'variables', event.target.value));
                        bind('config-attachments', (event) => updateNodeConfig(node.id, 'attachments', event.target.value));
                        break;
                    case 'send_sequence':
                        bind('config-sequence-lines', (event) => {
                            updateNodeConfig(node.id, 'sequence', parseSequenceLines(event.target.value));
                        });
                        break;
                    case 'delay':
                        bind('config-delay-mode', (event) => updateNodeConfig(node.id, 'mode', event.target.value), 'change');
                        bind('config-delay-value', (event) => updateNodeConfig(node.id, 'value', Number(event.target.value || 0)));
                        bind('config-delay-scheduled-at', (event) => updateNodeConfig(node.id, 'scheduled_at', event.target.value));
                        break;
                    case 'branch':
                        bind('config-field', (event) => updateNodeConfig(node.id, 'field', event.target.value));
                        bind('config-operator', (event) => updateNodeConfig(node.id, 'operator', event.target.value), 'change');
                        bind('config-value', (event) => updateNodeConfig(node.id, 'value', event.target.value));
                        bind('config-yes-label', (event) => renameBranchLabels(node.id, event.target.value, 'yes'));
                        bind('config-no-label', (event) => renameBranchLabels(node.id, event.target.value, 'no'));
                        break;
                    case 'tag':
                        bind('config-tag-action', (event) => updateNodeConfig(node.id, 'action', event.target.value), 'change');
                        bind('config-tag-name', (event) => updateNodeConfig(node.id, 'tag', event.target.value));
                        break;
                    case 'goal':
                        bind('config-goal-name', (event) => updateNodeConfig(node.id, 'goal_name', event.target.value));
                        bind('config-goal-condition', (event) => updateNodeConfig(node.id, 'condition', event.target.value));
                        break;
                    case 'end':
                        bind('config-end-reason', (event) => updateNodeConfig(node.id, 'reason', event.target.value));
                        break;
                }
            }

            function renameBranchLabels(nodeId, value, branchKey) {
                updateNodeConfig(nodeId, branchKey === 'yes' ? 'yesLabel' : 'noLabel', value);
                state.edges.forEach((edge) => {
                    if (edge.source !== nodeId) return;
                    if (branchKey === 'yes' && String(edge.label || '').toLowerCase() === 'yes') {
                        edge.label = value || 'yes';
                    }
                    if (branchKey === 'no' && String(edge.label || '').toLowerCase() === 'no') {
                        edge.label = value || 'no';
                    }
                });
                markDirty();
                renderCanvasOnly();
            }

            function parseSequenceLines(text) {
                return String(text || '')
                    .split('\n')
                    .map((line) => line.trim())
                    .filter(Boolean)
                    .map((line) => {
                        const [channel, delayValue, delayUnit, ...messageParts] = line.split('|').map((part) => part.trim());
                        return {
                            channel: channel || 'email',
                            delay_value: Number(delayValue || 1),
                            delay_unit: delayUnit || 'days',
                            message: messageParts.join(' | '),
                        };
                    });
            }

            async function saveWorkflow() {
                const triggerNode = state.nodes.find((node) => node.type === 'trigger');
                const payload = {
                    name: workflowNameEl.value.trim() || 'Untitled Workflow',
                    status: workflowStatusEl.value,
                    trigger_type: workflowTriggerEl.value,
                    trigger_config: triggerNode ? (triggerNode.config || {}) : {},
                    timezone: triggerNode?.config?.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
                    nodes: state.nodes,
                    edges: state.edges,
                };

                try {
                    const response = await fetch(saveUrl, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Save failed.');
                    }

                    state.saveVersion += 1;
                    markSynced(data.message || 'Draft synced');
                    render();
                } catch (error) {
                    showError(error.message || 'Save failed.');
                }
            }

            async function previewWorkflow() {
                try {
                    const response = await fetch(previewUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ context: {} }),
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Preview failed.');
                    }

                    previewContentEl.innerHTML = '';
                    if (!Array.isArray(data.path) || !data.path.length) {
                        previewContentEl.innerHTML = infoCard('No preview path is available yet. Add at least one connected path from the trigger.');
                    } else {
                        data.path.forEach((step, index) => {
                            const item = catalogItem(step.type);
                            const card = document.createElement('div');
                            card.className = 'rounded-3xl border border-slate-200 bg-slate-50/80 p-4 dark:border-white/[0.08] dark:bg-white/[0.03]';
                            card.innerHTML = `
                                <div class="flex items-center gap-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-bold" style="background:${hexToRgba(item.color, 0.12)}; color:${item.color};">${index + 1}</div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">${escapeHtml(step.label || item.title)}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">${escapeHtml(step.type)}</p>
                                    </div>
                                </div>
                            `;
                            previewContentEl.appendChild(card);
                        });
                    }

                    previewModalEl.classList.remove('hidden');
                } catch (error) {
                    showError(error.message || 'Preview failed.');
                }
            }

            async function publishWorkflow() {
                try {
                    if (state.dirty) {
                        await saveWorkflow();
                    }

                    const response = await fetch(publishUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Publish failed.');
                    }

                    workflowStatusEl.value = 'active';
                    markSynced(data.message || 'Workflow published');
                } catch (error) {
                    showError(error.message || 'Publish failed.');
                }
            }

            function scheduleAutosave() {
                state.autosaveTimer = window.setInterval(() => {
                    if (state.dirty) {
                        saveWorkflow();
                    }
                }, 10000);
            }

            viewportEl.addEventListener('pointerdown', (event) => {
                if (event.target.closest('.wf-node')) return;
                state.isPanning = true;
                state.panStartX = event.clientX - state.viewport.x;
                state.panStartY = event.clientY - state.viewport.y;
                state.selectedNodeId = null;
                render();
                viewportEl.setPointerCapture(event.pointerId);
            });

            viewportEl.addEventListener('pointermove', (event) => {
                if (state.isDraggingNode && state.dragNodeId) {
                    const node = findNode(state.dragNodeId);
                    if (!node) return;
                    const point = viewportToWorld(event.clientX, event.clientY);
                    node.x = Math.max(0, Math.min(stageSize.width - 280, Math.round(point.x - state.dragOffsetX)));
                    node.y = Math.max(0, Math.min(stageSize.height - 110, Math.round(point.y - state.dragOffsetY)));
                    markDirty('Draft moving');
                    renderCanvasOnly();
                    return;
                }

                if (!state.isPanning) return;
                state.viewport.x = event.clientX - state.panStartX;
                state.viewport.y = event.clientY - state.panStartY;
                render();
            });

            viewportEl.addEventListener('pointerup', (event) => {
                if (state.isDraggingNode) {
                    state.isDraggingNode = false;
                    state.dragNodeId = null;
                    viewportEl.releasePointerCapture(event.pointerId);
                    markDirty();
                    render();
                    return;
                }

                state.isPanning = false;
                viewportEl.releasePointerCapture(event.pointerId);
                render();
            });

            viewportEl.addEventListener('pointerleave', () => {
                state.isPanning = false;
                state.isDraggingNode = false;
                state.dragNodeId = null;
                render();
            });

            viewportEl.addEventListener('dragover', (event) => {
                event.preventDefault();
            });

            viewportEl.addEventListener('drop', (event) => {
                event.preventDefault();
                const type = event.dataTransfer.getData('text/plain') || state.dragLibraryItem;
                if (!type) return;
                const point = viewportToWorld(event.clientX, event.clientY);
                addNodeFromCatalog(type, { x: point.x - 135, y: point.y - 48 });
                state.dragLibraryItem = null;
            });

            viewportEl.addEventListener('wheel', (event) => {
                event.preventDefault();
                const direction = event.deltaY > 0 ? -0.08 : 0.08;
                const previousZoom = state.viewport.zoom;
                const nextZoom = Math.max(0.45, Math.min(1.8, previousZoom + direction));
                const rect = viewportEl.getBoundingClientRect();
                const offsetX = event.clientX - rect.left;
                const offsetY = event.clientY - rect.top;
                const worldX = (offsetX - state.viewport.x) / previousZoom;
                const worldY = (offsetY - state.viewport.y) / previousZoom;
                state.viewport.zoom = nextZoom;
                state.viewport.x = offsetX - worldX * nextZoom;
                state.viewport.y = offsetY - worldY * nextZoom;
                render();
            }, { passive: false });

            document.getElementById('zoom-in-button').addEventListener('click', () => {
                state.viewport.zoom = Math.min(1.8, state.viewport.zoom + 0.1);
                render();
            });

            document.getElementById('zoom-out-button').addEventListener('click', () => {
                state.viewport.zoom = Math.max(0.45, state.viewport.zoom - 0.1);
                render();
            });

            document.getElementById('zoom-reset-button').addEventListener('click', () => {
                state.viewport = { x: 380, y: 180, zoom: 1 };
                render();
            });

            document.getElementById('toggle-connect-button').addEventListener('click', () => {
                state.connectMode = !state.connectMode;
                state.connectSourceId = null;
                render();
            });

            document.getElementById('undo-button').addEventListener('click', undo);
            document.getElementById('redo-button').addEventListener('click', redo);
            document.getElementById('save-button').addEventListener('click', saveWorkflow);
            document.getElementById('preview-button').addEventListener('click', previewWorkflow);
            document.getElementById('publish-button').addEventListener('click', publishWorkflow);
            document.getElementById('delete-node-button').addEventListener('click', deleteSelectedNode);
            document.getElementById('clear-selection-button').addEventListener('click', () => {
                state.selectedNodeId = null;
                render();
            });
            document.getElementById('close-preview-button').addEventListener('click', () => previewModalEl.classList.add('hidden'));
            previewModalEl.addEventListener('click', (event) => {
                if (event.target === previewModalEl) {
                    previewModalEl.classList.add('hidden');
                }
            });

            workflowNameEl.addEventListener('input', () => markDirty());
            workflowStatusEl.addEventListener('change', () => markDirty());
            workflowTriggerEl.addEventListener('change', () => {
                const triggerNode = state.nodes.find((node) => node.type === 'trigger');
                if (triggerNode) {
                    triggerNode.config = triggerNode.config || {};
                    triggerNode.config.source = workflowTriggerEl.value;
                }
                markDirty();
                render();
            });

            blockSearchEl.addEventListener('input', (event) => {
                state.librarySearch = event.target.value;
                renderLibrary();
            });

            window.addEventListener('keydown', (event) => {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
                    event.preventDefault();
                    saveWorkflow();
                }

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'z' && !event.shiftKey) {
                    event.preventDefault();
                    undo();
                }

                if ((event.ctrlKey || event.metaKey) && ((event.key.toLowerCase() === 'z' && event.shiftKey) || event.key.toLowerCase() === 'y')) {
                    event.preventDefault();
                    redo();
                }
            });

            renderLibrary();
            render();
            scheduleAutosave();
            markSynced();
        });
    </script>
</x-app-layout>
