<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Styles: Tailwind CDN (CSS only, no 407KB runtime compiler) + Alpine.js for cart/interactions -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> -->

     @vite(['resources/css/app.css'])
    <script src="/js/tailwind.js"></script>

    @if($landing->enable_cart)
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    @endif
    {{-- NOTE: @vite removed from public pages -- admin CSS/JS is not needed here.
         Page-specific CSS is rendered inline via $page->css below.
         For production, pre-compile Tailwind per-landing with CLI. --}}
    
    <title>{{ $landing->settings->meta_title ?? $page->name }}</title>
    <meta name="landing-id" content="{{ $landing->id }}">
    
    @if($landing->settings && $landing->settings->meta_description)
        <meta name="description" content="{{ $landing->settings->meta_description }}">
    @endif

    <!-- Custom Head Scripts -->
    @if($landing->settings && $landing->settings->custom_head_scripts)
        {!! $landing->settings->custom_head_scripts !!}
    @endif

    <!-- GA4 -->
    @if($landing->settings && $landing->settings->ga_measurement_id)
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $landing->settings->ga_measurement_id }}"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $landing->settings->ga_measurement_id }}');
        </script>
    @endif

    @php
        $preparedPageCss = (string) ($page->css ?? '');
        $preparedPageCss = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $preparedPageCss) ?? $preparedPageCss;
        $preparedPageCss = preg_replace('/<\/?(?:html|head|body|meta|title|link)\b[^>]*>/i', '', $preparedPageCss) ?? $preparedPageCss;
        $preparedPageCss = preg_replace('/<style\b[^>]*>/i', '', $preparedPageCss) ?? $preparedPageCss;
        $preparedPageCss = preg_replace('/<\/style>/i', '', $preparedPageCss) ?? $preparedPageCss;
        $preparedPageCss = trim((string) $preparedPageCss);
    @endphp
    
    <style>
        {!! $preparedPageCss !!}
        :root {
            --cart-bg: {{ $landing->cart_bg_color ?? '#ffffff' }};
            --cart-text: {{ $landing->cart_text_color ?? '#000000' }};
            --cart-btn: {{ $landing->cart_btn_color ?? '#3b82f6' }};
            --cart-btn-text: {{ $landing->cart_btn_text_color ?? '#ffffff' }};
            --lp-topbar-height: 48px;
            --lp-topbar-bg: rgba(8, 13, 24, 0.82);
            --lp-topbar-border: rgba(148, 163, 184, 0.22);
            --lp-topbar-muted: rgba(203, 213, 225, 0.72);
            --lp-topbar-text: #f8fafc;
            --lp-topbar-accent: #6366f1;
        }

        body.lp-topbar-visible {
            padding-top: calc(var(--lp-topbar-height) + 6px);
        }

        .lp-adminbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10000000;
            height: var(--lp-topbar-height);
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 14px;
            color: var(--lp-topbar-text);
            background: var(--lp-topbar-bg);
            border-bottom: 1px solid var(--lp-topbar-border);
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 26px rgba(2, 6, 23, 0.28);
            font-family: "Inter", "Segoe UI", sans-serif;
        }

        .lp-adminbar__left,
        .lp-adminbar__center,
        .lp-adminbar__right {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .lp-adminbar__left {
            flex: 0 1 36%;
        }

        .lp-adminbar__center {
            flex: 1 1 auto;
            justify-content: center;
            min-width: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .lp-adminbar__center::-webkit-scrollbar {
            display: none;
        }

        .lp-adminbar__right {
            flex: 0 1 36%;
            justify-content: flex-end;
            gap: 6px;
        }

        .lp-brand-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.015em;
            white-space: nowrap;
        }

        .lp-brand-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: linear-gradient(180deg, #f43f5e, #7c3aed 70%);
            box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.22);
        }

        .lp-context {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            color: var(--lp-topbar-muted);
            font-size: 11px;
            white-space: nowrap;
        }

        .lp-context strong {
            color: var(--lp-topbar-text);
            font-weight: 700;
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lp-context-name {
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .lp-badge {
            display: inline-flex;
            align-items: center;
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .lp-badge--draft {
            color: #fbbf24;
            background: rgba(146, 64, 14, 0.26);
            border-color: rgba(251, 191, 36, 0.4);
        }

        .lp-badge--published {
            color: #22c55e;
            background: rgba(22, 101, 52, 0.28);
            border-color: rgba(34, 197, 94, 0.38);
        }

        .lp-badge--preview {
            color: #60a5fa;
            background: rgba(30, 58, 138, 0.35);
            border-color: rgba(96, 165, 250, 0.45);
        }

        .lp-nav {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 2px;
            border-radius: 11px;
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }

        .lp-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 30px;
            padding: 0 10px;
            border-radius: 8px;
            color: #cbd5e1;
            font-size: 11px;
            font-weight: 600;
            line-height: 30px;
            white-space: nowrap;
            transition: color .15s ease, background-color .15s ease;
        }

        .lp-tab:hover {
            color: #f8fafc;
            background: rgba(51, 65, 85, 0.72);
        }

        .lp-tab.is-active {
            color: #eef2ff;
            background: linear-gradient(180deg, rgba(79, 70, 229, 0.28), rgba(67, 56, 202, 0.58));
            box-shadow: inset 0 0 0 1px rgba(129, 140, 248, 0.38);
        }

        .lp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            height: 32px;
            border-radius: 9px;
            font-size: 11px;
            font-weight: 700;
            padding: 0 11px;
            white-space: nowrap;
            transition: all .17s ease;
        }

        .lp-btn--ghost {
            color: #dbe3f1;
            background: transparent;
            border: 1px solid transparent;
        }

        .lp-btn--ghost:hover {
            color: #ffffff;
            background: rgba(51, 65, 85, 0.54);
            border-color: rgba(148, 163, 184, 0.35);
        }

        .lp-btn--secondary {
            color: #f1f5f9;
            background: rgba(30, 41, 59, 0.72);
            border: 1px solid rgba(148, 163, 184, 0.34);
        }

        .lp-btn--secondary:hover {
            background: rgba(51, 65, 85, 0.85);
            border-color: rgba(148, 163, 184, 0.52);
        }

        .lp-btn--primary {
            color: #ffffff;
            border: 1px solid rgba(129, 140, 248, 0.68);
            background: linear-gradient(180deg, #6366f1, #4f46e5);
            box-shadow: 0 8px 18px rgba(79, 70, 229, 0.34);
        }

        .lp-btn--primary:hover {
            background: linear-gradient(180deg, #7c83ff, #5a52eb);
            box-shadow: 0 10px 24px rgba(79, 70, 229, 0.45);
        }

        .lp-btn--danger {
            color: #fecdd3;
            background: rgba(136, 19, 55, 0.38);
            border: 1px solid rgba(244, 114, 182, 0.55);
        }

        .lp-btn--danger:hover {
            color: #fff1f2;
            background: rgba(159, 18, 57, 0.55);
            border-color: rgba(251, 113, 133, 0.72);
        }

        .lp-user-wrap {
            position: relative;
        }

        .lp-user-pill {
            display: flex;
            align-items: center;
            gap: 7px;
            height: 32px;
            padding: 0 9px 0 4px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            color: #e2e8f0;
            background: rgba(15, 23, 42, 0.7);
            font-size: 11px;
            font-weight: 600;
        }

        .lp-user-pill:hover {
            border-color: rgba(148, 163, 184, 0.56);
            background: rgba(30, 41, 59, 0.8);
        }

        .lp-avatar {
            width: 24px;
            height: 24px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #3b82f6, #9333ea);
            color: white;
            font-size: 10px;
            font-weight: 700;
        }

        .lp-user-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 220px;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.36);
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 20px 34px rgba(2, 6, 23, 0.45);
            backdrop-filter: blur(12px);
        }

        .lp-user-menu[hidden] {
            display: none;
        }

        .lp-user-menu-item {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            height: 34px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #e2e8f0;
            border: 0;
            background: transparent;
            padding: 0 10px;
            white-space: nowrap;
        }

        .lp-user-menu-item:hover {
            background: rgba(51, 65, 85, 0.8);
            color: #ffffff;
        }

        .lp-user-menu-item.is-disabled {
            opacity: 0.58;
            pointer-events: none;
        }

        .lp-mobile-hide {
            display: inline-flex;
        }

        .lp-ai-chat {
            position: fixed;
            right: 20px;
            bottom: 18px;
            z-index: 2147483000;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            pointer-events: none;
            font-family: "Segoe UI", "Inter", "Helvetica Neue", Arial, sans-serif;
        }

        .lp-ai-chat__intro {
            width: min(330px, calc(100vw - 34px));
            background: #ffffff;
            color: #17223b;
            border: 1px solid #d6deeb;
            border-radius: 12px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.16);
            padding: 12px 12px 11px;
            pointer-events: auto;
            cursor: pointer;
            opacity: 0;
            transform: translateY(6px);
            visibility: hidden;
            transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
            position: absolute;
            right: 2px;
            bottom: 74px;
            z-index: 2;
        }

        .lp-ai-chat__intro.is-visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .lp-ai-chat__intro::after {
            content: "";
            position: absolute;
            right: 23px;
            bottom: -8px;
            width: 13px;
            height: 13px;
            background: #ffffff;
            border-right: 1px solid #d6deeb;
            border-bottom: 1px solid #d6deeb;
            transform: rotate(45deg);
        }

        .lp-ai-chat__intro-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 5px;
        }

        .lp-ai-chat__intro-title {
            margin: 0;
            font-size: 14px;
            line-height: 1.2;
            font-weight: 700;
            color: #1c2f57;
        }

        .lp-ai-chat__intro-close {
            width: 20px;
            height: 20px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #5b6e92;
            font-size: 17px;
            line-height: 1;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color .15s ease, color .15s ease;
        }

        .lp-ai-chat__intro-close:hover {
            color: #22365f;
            background: #edf1f8;
        }

        .lp-ai-chat__intro-text {
            margin: 0;
            font-size: 12px;
            line-height: 1.48;
            color: #405577;
            font-weight: 500;
        }

        .lp-ai-chat__toggle {
            width: 54px;
            height: 54px;
            border: 0;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            background: linear-gradient(180deg, #3e63f3 0%, #2e4ecf 100%);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.3);
            transition: transform .16s ease, box-shadow .2s ease, filter .2s ease;
            pointer-events: auto;
            cursor: pointer;
        }

        .lp-ai-chat__toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.36);
            filter: brightness(1.03);
        }

        .lp-ai-chat__panel {
            width: min(360px, calc(100vw - 26px));
            max-height: min(74vh, 600px);
            display: flex;
            flex-direction: column;
            border-radius: 14px;
            overflow: hidden;
            color: #1e2d49;
            background: #ffffff;
            border: 1px solid #d4deed;
            box-shadow: 0 20px 44px rgba(15, 23, 42, 0.24);
            opacity: 0;
            transform: translateY(8px);
            transform-origin: bottom right;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
            position: absolute;
            right: 0;
            bottom: 74px;
            z-index: 3;
        }

        .lp-ai-chat__panel.is-open,
        .lp-ai-chat.is-open .lp-ai-chat__panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }

        .lp-ai-chat__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            background: #f8fbff;
            border-bottom: 1px solid #dbe3ef;
        }

        .lp-ai-chat__title {
            margin: 0;
            font-size: 17px;
            line-height: 1.25;
            font-weight: 700;
            color: #22345d;
        }

        .lp-ai-chat__subtitle {
            margin: 3px 0 0;
            font-size: 12px;
            color: #607395;
            font-weight: 500;
        }

        .lp-ai-chat__close {
            border: 0;
            background: transparent;
            color: #5f7296;
            width: 24px;
            height: 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background-color .15s ease, color .15s ease;
        }

        .lp-ai-chat__close:hover {
            background: #e9eff8;
            color: #1d2f56;
        }

        .lp-ai-chat__messages {
            padding: 12px;
            min-height: 112px;
            max-height: 350px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #f3f7fc;
        }

        .lp-ai-chat__message {
            max-width: 92%;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13.5px;
            line-height: 1.46;
            white-space: pre-wrap;
            word-break: break-word;
            opacity: 0;
            transform: translateY(6px);
            transition: opacity .16s ease, transform .16s ease;
        }

        .lp-ai-chat__message.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .lp-ai-chat__message--assistant {
            align-self: flex-start;
            background: #ffffff;
            color: #213252;
            border: 1px solid #d7e0ee;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
        }

        .lp-ai-chat__message--user {
            align-self: flex-end;
            color: #ffffff;
            background: linear-gradient(180deg, #4a69f3 0%, #3655de 100%);
            box-shadow: 0 6px 14px rgba(54, 85, 222, 0.22);
        }

        .lp-ai-chat__composer {
            padding: 10px;
            border-top: 1px solid #dbe3ef;
            background: #f8fbff;
        }

        .lp-ai-chat__status {
            margin: 0 0 8px;
            font-size: 11px;
            line-height: 1.42;
            color: #647998;
            font-weight: 500;
        }

        .lp-ai-chat__status.is-error {
            color: #b91c1c;
        }

        .lp-ai-chat__form {
            display: flex;
            align-items: flex-end;
            gap: 7px;
        }

        .lp-ai-chat__input {
            flex: 1;
            resize: none;
            min-height: 38px;
            max-height: 110px;
            border-radius: 10px;
            border: 1px solid #c3d0e4;
            background: #ffffff;
            color: #1a2e4f;
            padding: 8px 10px;
            font-size: 14px;
            line-height: 1.35;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .lp-ai-chat__input:focus {
            border-color: #5b74dc;
            box-shadow: 0 0 0 3px rgba(91, 116, 220, 0.16);
        }

        .lp-ai-chat__send {
            border: 0;
            border-radius: 10px;
            height: 38px;
            min-width: 76px;
            padding: 0 14px;
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(180deg, #4f6ef2 0%, #3654d8 100%);
            cursor: pointer;
            transition: transform .15s ease, opacity .15s ease, box-shadow .15s ease;
        }

        .lp-ai-chat__send:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(54, 84, 216, 0.28);
        }

        .lp-ai-chat__send:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .lp-ai-chat__action-wrap {
            display: flex;
            justify-content: flex-start;
        }

        .lp-ai-chat__action-btn {
            border: 0;
            border-radius: 10px;
            background: #f97316;
            color: #ffffff;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 16px rgba(249, 115, 22, 0.28);
        }

        .lp-ai-chat__action-btn:hover {
            filter: brightness(1.04);
        }

        .lp-ai-chat__typing {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lp-ai-chat__typing-label {
            font-size: 12px;
            font-weight: 500;
            color: #4c5f7f;
        }

        .lp-ai-chat__typing-dots {
            display: inline-flex;
            gap: 4px;
            align-items: center;
        }

        .lp-ai-chat__typing-dots span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #7388ab;
            animation: lp-ai-chat-dot 1.1s infinite ease-in-out;
        }

        .lp-ai-chat__typing-dots span:nth-child(2) {
            animation-delay: .15s;
        }

        .lp-ai-chat__typing-dots span:nth-child(3) {
            animation-delay: .3s;
        }

        .lp-ai-chat__target-flash {
            animation: lp-ai-chat-flash 1.4s ease;
        }

        @keyframes lp-ai-chat-dot {
            0%, 80%, 100% { opacity: .35; transform: translateY(0); }
            40% { opacity: 1; transform: translateY(-2px); }
        }

        @keyframes lp-ai-chat-flash {
            0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, .55); }
            100% { box-shadow: 0 0 0 16px rgba(79, 70, 229, 0); }
        }

        @media (max-width: 1160px) {
            .lp-context {
                display: none;
            }
        }

        @media (max-width: 980px) {
            .lp-adminbar__center {
                justify-content: flex-start;
            }

            .lp-hide-md {
                display: none;
            }
        }

        @media (max-width: 760px) {
            body.lp-topbar-visible {
                padding-top: calc(var(--lp-topbar-height) + 12px);
            }

            .lp-adminbar {
                gap: 8px;
                padding-inline: 8px;
            }

            .lp-ai-chat {
                right: 10px;
                bottom: 10px;
            }

            .lp-ai-chat__intro {
                width: min(330px, calc(100vw - 20px));
                right: 0;
                bottom: 72px;
            }

            .lp-ai-chat__panel {
                width: min(360px, calc(100vw - 14px));
                max-height: min(72vh, 520px);
                bottom: 72px;
            }
        }
    </style>
</head>
<body class="antialiased {{ auth()->check() ? 'lp-topbar-visible' : '' }}">
    <!-- Toast Container -->
    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-2"></div>

    @auth
        @php
            $authUser = auth()->user();
            $ownerId = optional($landing->workspace)->user_id;
            $canManageLanding = $authUser && $ownerId && ((int) $authUser->id === (int) $ownerId);
            $workspaceName = optional($landing->workspace)->name ?? 'Workspace';
            $allPages = $landing->relationLoaded('pages') ? $landing->pages : $landing->pages()->get();
            $landingEditPage = $allPages->firstWhere('type', 'index') ?? $allPages->first();
            $checkoutEditPage = $allPages->firstWhere('type', 'checkout');
            $thankyouEditPage = $allPages->firstWhere('type', 'thankyou');
            $pageState = strtolower((string) ($page->status ?: $landing->status ?: 'draft'));
            $statusLabel = $pageState === 'published' ? 'Published' : 'Draft';
            $statusClass = $pageState === 'published' ? 'lp-badge--published' : 'lp-badge--draft';
            if (request()->route()?->getName() === 'landings.preview') {
                $statusLabel = $pageState === 'published' ? 'Preview' : 'Draft Preview';
                $statusClass = 'lp-badge--preview';
            }

            $publicUrl = \App\Support\LandingPublicUrl::pageUrl($landing, $page);
            $previewUrl = route('landings.preview', [$landing, $page]);
            $avatarInitials = collect(explode(' ', trim((string) ($authUser->name ?? 'U'))))
                ->filter()
                ->map(fn($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                ->take(2)
                ->implode('');
            if ($avatarInitials === '') {
                $avatarInitials = 'U';
            }
            $pageTypeLabel = match((string) $page->type) {
                'index' => 'Landing',
                'checkout' => 'Checkout',
                'thankyou' => 'Thank You',
                default => ucfirst((string) $page->type),
            };
        @endphp

        <div id="lp-adminbar" class="lp-adminbar" data-role="{{ $canManageLanding ? 'admin' : 'viewer' }}">
            <div class="lp-adminbar__left">
                <div class="lp-brand-chip">
                    <span class="lp-brand-dot" aria-hidden="true"></span>
                    <span>Landing Builder</span>
                </div>
                <div class="lp-context">
                    <strong>{{ $workspaceName }}</strong>
                    <span>/</span>
                    <span class="lp-context-name">{{ $landing->name }}</span>
                    <span>/</span>
                    <span class="lp-context-name">{{ $page->name ?? $pageTypeLabel }}</span>
                    <span class="lp-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </div>

            <div class="lp-adminbar__center">
                <nav class="lp-nav" aria-label="Funnel navigation">
                    @if($canManageLanding && $landingEditPage)
                        <a class="lp-tab {{ $page->type === 'index' ? 'is-active' : '' }}" href="{{ route('landings.pages.edit', [$landing, $landingEditPage]) }}">Landing</a>
                    @else
                        <span class="lp-tab {{ $page->type === 'index' ? 'is-active' : '' }}">Landing</span>
                    @endif

                    @if($canManageLanding && $checkoutEditPage)
                        <a class="lp-tab {{ $page->type === 'checkout' ? 'is-active' : '' }}" href="{{ route('landings.pages.edit', [$landing, $checkoutEditPage]) }}">Checkout</a>
                    @else
                        <span class="lp-tab {{ $page->type === 'checkout' ? 'is-active' : '' }}">Checkout</span>
                    @endif

                    @if($canManageLanding && $thankyouEditPage)
                        <a class="lp-tab {{ $page->type === 'thankyou' ? 'is-active' : '' }}" href="{{ route('landings.pages.edit', [$landing, $thankyouEditPage]) }}">Thank You</a>
                    @else
                        <span class="lp-tab {{ $page->type === 'thankyou' ? 'is-active' : '' }}">Thank You</span>
                    @endif

                    @if($canManageLanding)
                        <a class="lp-tab lp-hide-md" href="{{ route('leads.index', ['landing' => $landing->id]) }}">Leads</a>
                        <a class="lp-tab lp-hide-md" href="{{ route('analytics.index', ['landing' => $landing->id]) }}">Analytics</a>
                    @endif
                </nav>
            </div>

            <div class="lp-adminbar__right">
                <a class="lp-btn lp-btn--ghost" href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer">Preview</a>

                @if($canManageLanding)
                    <a class="lp-btn lp-btn--secondary" href="{{ route('landings.pages.edit', [$landing, $page]) }}">Edit</a>

                    @if($landing->status === 'published')
                        <form method="POST" action="{{ route('landings.unpublish', $landing) }}">
                            @csrf
                            <button type="submit" class="lp-btn lp-btn--danger">Unpublish</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('landings.publish', $landing) }}">
                            @csrf
                            <button type="submit" class="lp-btn lp-btn--primary">Publish</button>
                        </form>
                    @endif

                    <a class="lp-btn lp-btn--ghost lp-hide-md" href="{{ route('landings.edit', $landing) }}">Settings</a>
                @endif

                <div class="lp-user-wrap">
                    <button type="button" class="lp-user-pill" data-lp-toggle="lp-user-menu" aria-label="Open user menu">
                        <span class="lp-avatar">{{ $avatarInitials }}</span>
                        <span class="lp-mobile-hide">{{ $authUser->name }}</span>
                    </button>
                    <div id="lp-user-menu" class="lp-user-menu" hidden>
                        <div class="lp-user-menu-item is-disabled">{{ $canManageLanding ? 'Admin' : 'Viewer' }}</div>
                        <button type="button" class="lp-user-menu-item" data-copy="{{ $publicUrl }}" data-copy-label="Copy Page URL">Copy Page URL</button>
                        <button type="button" class="lp-user-menu-item" data-copy="{{ $previewUrl }}" data-copy-label="Copy Preview URL">Copy Preview URL</button>
                        @if($canManageLanding)
                            <form method="POST" action="{{ route('landings.pages.duplicate', [$landing, $page]) }}">
                                @csrf
                                <button type="submit" class="lp-user-menu-item">Duplicate Page</button>
                            </form>
                            <a href="{{ route('landings.pages.edit', [$landing, $page]) }}#history" class="lp-user-menu-item">Revisions / History</a>
                            <a href="{{ route('ai-generator.index') }}" class="lp-user-menu-item">AI Actions</a>
                            <a href="{{ route('dashboard') }}" class="lp-user-menu-item">Open Dashboard</a>
                            <a href="{{ route('settings.index') }}" class="lp-user-menu-item">Account Settings</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="lp-user-menu-item">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    @if($landing->enable_cart)
    <div x-data="shoppingCart()" x-cloak class="relative z-50">
        <!-- Floating Cart Button Wrapper -->
        @php
            $x = $landing->cart_x_offset ?? 20;
            $y = $landing->cart_y_offset ?? 20;
            $pos = $landing->cart_position ?? 'bottom-right';
            
            $style = '';
            if($pos === 'bottom-right') $style = "bottom: {$y}px; right: {$x}px;";
            elseif($pos === 'bottom-left') $style = "bottom: {$y}px; left: {$x}px;";
            elseif($pos === 'top-right') $style = "top: {$y}px; right: {$x}px;";
            elseif($pos === 'top-left') $style = "top: {$y}px; left: {$x}px;";
            elseif($pos === 'bottom-bar') $style = ""; // Bottom bar uses classes
        @endphp

        <div class="fixed z-[9999999] transition-all duration-300 
            {{ $pos === 'bottom-bar' ? 'bottom-0 left-0 w-full flex justify-center pb-6' : '' }}"
            style="{{ $style }}">
            <button @click="showCart = !showCart" 
                    class="p-4 rounded-full shadow-2xl transition-all transform hover:scale-110 active:scale-95 flex items-center justify-center group relative"
                    style="background-color: var(--cart-btn); color: var(--cart-btn-text);">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 group-hover:animate-bounce-short" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span x-show="count > 0" 
                          x-transition:enter="transition ease-out duration-200"
                          x-transition:enter-start="transform scale-0"
                          x-transition:enter-end="transform scale-100"
                          class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center border-2 border-white dark:border-gray-900 shadow-sm" 
                          x-text="count">
                    </span>
                </div>
            </button>
        </div>

        <!-- Backdrop -->
        <div x-show="showCart" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showCart = false"
             class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40">
        </div>

        <!-- Cart Sidebar -->
        <div x-show="showCart" 
             x-transition:enter="transform transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed top-0 right-0 h-full w-full max-w-sm z-[9999999] shadow-2xl flex flex-col glass-panel"
             style="background-color: var(--cart-bg); color: var(--cart-text);">
            
            <!-- Header -->
            <div class="p-5 flex justify-between items-center border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opactiy-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h2 class="text-lg font-bold tracking-tight">Your Cart</h2>
                </div>
                <button @click="showCart = false" class="p-2 rounded-full hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scrollable Items Area -->
            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                <template x-if="items.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center opacity-60 space-y-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="text-lg font-medium">Your cart is empty</p>
                        <p class="text-sm max-w-[200px]">Looks like you haven't added anything to your cart yet.</p>
                        <button @click="showCart = false" class="mt-4 px-6 py-2 rounded-full border border-current text-sm font-semibold hover:opacity-75 transition-opacity">
                            Continue Shopping
                        </button>
                    </div>
                </template>

                <template x-for="(item, index) in items" :key="index">
                    <div class="group flex items-start gap-4 p-4 rounded-xl bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition-colors relative overflow-hidden">
                        
                        <!-- Product Icon/Image Placeholder -->
                        <div class="h-16 w-16 flex-shrink-0 bg-white dark:bg-gray-800 rounded-lg shadow-sm flex items-center justify-center text-xl font-bold uppercase text-gray-400">
                             <span x-text="item.title.substring(0,2)"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-sm leading-tight pr-6" x-text="item.title"></h3>
                                <p class="font-mono text-sm font-semibold" x-text="item.price"></p>
                            </div>
                            
                            <div class="flex items-center justify-between mt-4">
                                <!-- Professional Quantity Stepper -->
                                <div class="flex items-center bg-white dark:bg-gray-800 rounded-lg shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-0.5">
                                    <button @click="if(item.qty > 1) item.qty--;" 
                                            class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    
                                    <input type="number" 
                                           x-model.number="item.qty" 
                                           min="1"
                                           class="w-12 text-center bg-transparent border-0 p-0 text-sm font-bold text-gray-900 dark:text-white focus:ring-0 appearance-none [-moz-appearance:_textfield] [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none">
                                    
                                    <button @click="item.qty++;" 
                                            class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>

                                <button @click="removeFromCart(index)" class="group flex items-center gap-1.5 text-xs font-medium text-red-500 hover:text-red-600 px-2 py-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span class="opacity-0 group-hover:opacity-100 transition-opacity -ml-2 group-hover:ml-0">Remove</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer: Totals & Checkout -->
            <div class="p-6 border-t border-black/5 dark:border-white/10 bg-black/5 dark:bg-white/5 backdrop-blur-md" x-show="items.length > 0">
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center text-sm opacity-70">
                        <span>Subtotal</span>
                        <span class="font-mono font-medium" x-text="total"></span>
                    </div>
                    <div class="flex justify-between items-center text-sm opacity-70">
                        <span>Shipping</span>
                        <span class="text-green-500 font-medium">Free</span>
                    </div>
                    <div class="flex justify-between items-center text-xl font-bold pt-2 border-t border-black/10 dark:border-white/10">
                        <span>Total</span>
                        <span class="font-mono" x-text="total"></span>
                    </div>
                </div>
                
                <button @click="proceedToCheckout()" 
                        class="w-full py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2 group"
                        style="background-color: var(--cart-btn); color: var(--cart-btn-text);">
                    <span>Proceed to Checkout</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
                <div class="mt-4 flex justify-center gap-2 opacity-50">
                     <svg class="h-5 w-8" viewBox="0 0 38 24" fill="currentColor"><path d="M35 0H3C1.3 0 0 1.3 0 3V21C0 22.7 1.3 24 3 24H35C36.7 24 38 22.7 38 21V3C38 1.3 36.7 0 35 0Z" fill="#999"/></svg>
                     <p class="text-[10px] text-center pt-0.5">Secure Checkout</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function shoppingCart() {
            return {
                showCart: false,
                items: [],
                get count() { return this.items.length; },
                get total() {
                    let sum = 0;
                    this.items.forEach(i => {
                        const price = parseFloat(i.price.replace(/[^0-9.-]+/g,""));
                        if(!isNaN(price)) sum += price * i.qty;
                    });
                    return sum.toFixed(2);
                },
                init() {
                    // 1. Initialize from LocalStorage
                    const savedCart = localStorage.getItem('landing_cart');
                    if (savedCart) {
                        this.items = JSON.parse(savedCart);
                    }

                    // 2. Watch for changes and save (using JSON.stringify to catch deep changes if proxy allows, 
                    // otherwise acts on structural changes. Alpine 3 $watch is shallow by default, 
                    // but we will trust proper array mutation triggers or we could use specific save logic).
                    // To ensure Qty updates trigger this, we might need a workaround or just rely on Alpine's reactivity.
                    this.$watch('items', (val) => {
                        localStorage.setItem('landing_cart', JSON.stringify(val));
                    });

                    // Listen for add-to-cart clicks from GrapesJS components
                    document.addEventListener('click', (e) => {
                        if (e.target && (e.target.matches('.btn-add-cart') || e.target.closest('.btn-add-cart'))) {
                            const btn = e.target.closest('.btn-add-cart') || e.target;
                            const product = {
                                hasId: btn.dataset.productId, // Optional ID
                                title: btn.dataset.productLabel || btn.dataset.title || 'Product',
                                price: btn.dataset.price || '0.00',
                                qty: 1
                            };
                            this.addToCart(product);
                        }
                    });
                },
                addToCart(product) {
                    const existing = this.items.find(i => i.title === product.title);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.items.push(product);
                    }
                    this.showCart = true;
                    // Optional: Toast notification
                },
                removeFromCart(index) {
                    this.items.splice(index, 1);
                },
                proceedToCheckout() {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const landingId = "{{ $landing->id }}";
                    
                    fetch('/cart/sync', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            items: this.items.map(i => ({
                                label: i.title,
                                qty: i.qty
                            })),
                            landing_id: landingId
                        })
                    })
                    .then(response => {
                        if (response.ok) {
                            // Redirect to checkout page logic
                            window.location.href = "{{ route('landings.checkout', $landing->id) }}"; 
                        } else {
                            alert('Failed to sync cart.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            }
        }
    </script>
    @endif

    @if($page->type === 'checkout')
        @include('landings.public.checkout', ['layout' => $checkoutLayout ?? 'layout_1'])
    @elseif($page->type === 'thankyou')
        @include('landings.public.thankyou', ['layout' => $thankyouLayout ?? 'thankyou_1'])
    @else
        {!! $page->html !!}

        @if(isset($lead))
        <script>
            window.leadData = {
                transaction_id: "{{ $lead->transaction_id ?? $lead->id }}",
                status: "{{ ucfirst($lead->status) }}",
                first_name: "{{ $lead->first_name }}",
                last_name: "{{ $lead->last_name }}",
                email: "{{ $lead->email }}",
                phone: "{{ $lead->phone }}",
                address: "{{ $lead->address }}",
                city: "{{ $lead->city }}",
                zip: "{{ $lead->zip }}",
                country: "{{ $lead->country }}",
                created_at: "{{ $lead->created_at->format('F j, Y, g:i a') }}",
                payment_method: "{{ ucfirst($lead->payment_method) }}",
                currency: "{{ $lead->currency }}",
                amount: "{{ number_format($lead->amount, 2) }}",
                product_name: "{{ $lead->product->name ?? 'Product' }}",
                invoice_url: "{{ \Illuminate\Support\Facades\URL::signedRoute('invoices.download', $lead) }}"
            };

            document.addEventListener('DOMContentLoaded', function() {
                // Helper to safely set text content
                const set = (id, value) => {
                    const el = document.getElementById(id);
                    if(el) el.textContent = value;
                };

                if(window.leadData) {
                    set('crm-order-id', window.leadData.transaction_id);
                    set('crm-status', window.leadData.status);
                    set('crm-fullname', window.leadData.first_name + ' ' + window.leadData.last_name);
                    set('crm-email', window.leadData.email);
                    set('crm-phone', window.leadData.phone);
                    set('crm-address', window.leadData.address + ', ' + window.leadData.city + ' ' + window.leadData.zip + ', ' + window.leadData.country);
                    set('crm-date', window.leadData.created_at);
                    set('crm-payment', window.leadData.payment_method);
                    set('crm-product', window.leadData.product_name);
                    set('crm-amount', window.leadData.currency + ' ' + window.leadData.amount);
                    set('crm-total', window.leadData.currency + ' ' + window.leadData.amount);
                    
                    const invoiceBtn = document.getElementById('crm-invoice-btn');
                    if(invoiceBtn) {
                        invoiceBtn.href = window.leadData.invoice_url;
                        invoiceBtn.style.display = 'inline-flex'; // Ensure it's visible if hidden
                        invoiceBtn.onclick = null; // Remove disable handler
                    }
                }
            });
        </script>
        @endif
    @endif

    @php
        $rawPageJs = (string) ($page->js ?? '');
        $preparedPageJs = preg_replace('/^\s*@import\s+url\([^)]+\)\s*;?\s*$/mi', '', $rawPageJs) ?? $rawPageJs;
        $preparedPageJs = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $preparedPageJs) ?? $preparedPageJs;
        $preparedPageJs = trim((string) $preparedPageJs);

        $templateStorageBase = '';
        $assetSource = $preparedPageCss . "\n" . ((string) ($page->html ?? '')) . "\n" . $preparedPageJs;
        if (preg_match('#/storage/builder-templates/[^"\'\s)]+#i', $assetSource, $assetMatch) === 1) {
            $detectedPath = (string) ($assetMatch[0] ?? '');
            $templateStorageBase = preg_replace('#/assets/.*$#i', '', $detectedPath) ?? $detectedPath;
            $templateStorageBase = rtrim($templateStorageBase, '/');
        }

        if ($templateStorageBase !== '' && str_contains($preparedPageJs, 'assets/')) {
            $preparedPageJs = preg_replace_callback(
                '/([\'"])(\/?(?:\.{1,2}\/)?assets\/[^\'"]*)(\1)/i',
                function ($matches) use ($templateStorageBase) {
                    $quote = (string) ($matches[1] ?? "'");
                    $raw = trim((string) ($matches[2] ?? ''));
                    if ($raw === '' || str_starts_with($raw, '/storage/')) {
                        return (string) ($matches[0] ?? '');
                    }

                    $normalized = $raw;
                    $normalized = ltrim($normalized, '/');
                    $normalized = preg_replace('#^(?:\./)+#', '', $normalized) ?? $normalized;
                    while (str_starts_with($normalized, '../')) {
                        $normalized = substr($normalized, 3);
                    }

                    if (!str_starts_with($normalized, 'assets/')) {
                        return (string) ($matches[0] ?? '');
                    }

                    return $quote . $templateStorageBase . '/' . $normalized . $quote;
                },
                $preparedPageJs
            ) ?? $preparedPageJs;
        }

        $needsThreeImportMap = false;

        if ($preparedPageJs !== '' && str_contains($preparedPageJs, '<script')) {
            $preparedPageJs = preg_replace_callback('/<script\b([^>]*)src=["\']([^"\']+)["\']([^>]*)><\/script>/i', function ($matches) use (&$needsThreeImportMap) {
                $before = trim((string) ($matches[1] ?? ''));
                $src = trim((string) ($matches[2] ?? ''));
                $after = trim((string) ($matches[3] ?? ''));

                $attrs = trim($before . ' src="' . $src . '" ' . $after);
                $attrs = preg_replace('/\s+/', ' ', $attrs) ?? $attrs;

                $path = (string) parse_url($src, PHP_URL_PATH);
                if ($path !== '' && str_starts_with($path, '/storage/')) {
                    $relative = ltrim(substr($path, strlen('/storage/')), '/');
                    $absolute = storage_path('app/public/' . $relative);
                    if (is_file($absolute)) {
                        $content = (string) @file_get_contents($absolute);
                        $isModule = preg_match('/^\s*(?:import\s+.+from\s+|import\s+[\'"]|export\s+)/m', $content) === 1;
                        $usesThreeBareImport = preg_match('/from\s+[\'"]three(?:\/addons\/)?/i', $content) === 1;

                        if ($isModule && !preg_match('/\btype\s*=\s*["\']module["\']/i', $attrs)) {
                            $attrs .= ' type="module"';
                        }

                        if ($isModule && $usesThreeBareImport) {
                            $needsThreeImportMap = true;
                        }
                    }
                }

                return '<script ' . trim($attrs) . '></script>';
            }, $preparedPageJs) ?? $preparedPageJs;

            // Repair malformed inline Three.js import maps that were stored without type="importmap".
            // Example bad payload:
            //   <script>{ "imports": { "three": "...", "three/addons/": "..." } }</script>
            $preparedPageJs = preg_replace_callback('/<script\b([^>]*)>([\s\S]*?)<\/script>/i', function ($matches) use (&$needsThreeImportMap) {
                $attrs = trim((string) ($matches[1] ?? ''));
                $content = trim((string) ($matches[2] ?? ''));

                // Keep external scripts untouched.
                if (preg_match('/\bsrc\s*=/i', $attrs)) {
                    return (string) ($matches[0] ?? '');
                }

                // If already importmap, keep as-is.
                if (preg_match('/\btype\s*=\s*["\']importmap["\']/i', $attrs)) {
                    $needsThreeImportMap = false;
                    return (string) ($matches[0] ?? '');
                }

                // If inline content looks like an import map, convert it.
                if ($content !== '' && preg_match('/^\s*\{[\s\S]*"imports"\s*:/i', $content)) {
                    $needsThreeImportMap = false;
                    return '<script type="importmap">' . "\n" . $content . "\n" . '</script>';
                }

                return (string) ($matches[0] ?? '');
            }, $preparedPageJs) ?? $preparedPageJs;

            if (preg_match_all('/<script\b[^>]*>.*?<\/script>/is', $preparedPageJs, $scriptBlocks) && !empty($scriptBlocks[0])) {
                $preparedPageJs = trim(implode("\n\n", array_map(fn ($block) => trim((string) $block), $scriptBlocks[0])));
            }
        }
    @endphp

    @if($needsThreeImportMap)
        <script type="importmap">
        {
          "imports": {
            "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
            "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
          }
        }
        </script>
    @endif

    @if($preparedPageJs !== '')
        @if(str_contains($preparedPageJs, '<script'))
            {!! $preparedPageJs !!}
        @else
            <script>
                {!! $preparedPageJs !!}
            </script>
        @endif
    @endif

    @php
        $assistantProfileName = 'Samya';
        $countryCode = strtoupper((string) ($visitorCountryCode ?? 'XX'));
        $countryName = trim((string) ($visitorCountryName ?? 'Unknown'));
        $offerLabelForChat = trim((string) ($landing->name ?? 'this offer'));
        $offerLabelForChat = $offerLabelForChat !== '' ? $offerLabelForChat : 'this offer';
        if (preg_match('/\b(template|html|css|js|seed|seeder|code)\b/i', $offerLabelForChat) === 1) {
            $offerLabelForChat = 'cette offre';
        }
        $pageHtmlForWelcome = (string) ($page->html ?? '');
        $lpPromiseSnippet = '';
        if (preg_match('/<h1\b[^>]*>(.*?)<\/h1>/is', $pageHtmlForWelcome, $headingMatch) || preg_match('/<h2\b[^>]*>(.*?)<\/h2>/is', $pageHtmlForWelcome, $headingMatch)) {
            $lpPromiseSnippet = html_entity_decode(strip_tags((string) ($headingMatch[1] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $lpPromiseSnippet = trim((string) preg_replace('/\s+/u', ' ', $lpPromiseSnippet));
            $lpPromiseSnippet = \Illuminate\Support\Str::limit($lpPromiseSnippet, 90, '...');
        }

        $countryMessages = [
            'MA' => 'Marhba! Ana ' . $assistantProfileName . '. Ila mazal katdour f nafs lmochkil, nkhllik tchof achno khassek daba bach t7ssn natiija b sor3a.',
            'FR' => 'Bienvenue. Moi c est ' . $assistantProfileName . '. Si votre blocage continue, je vais vous montrer la meilleure prochaine action maintenant.',
            'US' => 'Welcome. I am ' . $assistantProfileName . '. If you are still stuck with the same pain point, I will show you the fastest next move now.',
            'GB' => 'Welcome. I am ' . $assistantProfileName . '. If the same problem keeps repeating, I will guide you to the strongest next step right now.',
            'ES' => 'Bienvenido. Soy ' . $assistantProfileName . '. Si sigues con el mismo problema, te guio al siguiente paso mas fuerte ahora.',
            'DE' => 'Willkommen. Ich bin ' . $assistantProfileName . '. Wenn das gleiche Problem bleibt, fuehre ich Sie jetzt zum klarsten naechsten Schritt.',
            'IT' => 'Benvenuto. Sono ' . $assistantProfileName . '. Se il problema continua, ti guido subito al prossimo passo piu efficace.',
            'AE' => 'Welcome. I am ' . $assistantProfileName . '. If the same pain point is still blocking you, I will guide you to the fastest next step now.',
            'SA' => 'Welcome. I am ' . $assistantProfileName . '. If you are still facing the same pain, I will direct you to the strongest next action now.',
            'EG' => 'Welcome. I am ' . $assistantProfileName . '. If this pain point is still holding you back, I will guide you to the right next move now.',
        ];
        $defaultCountryMessage = 'Welcome. I am ' . $assistantProfileName . '. If you are still stuck, I will guide you to the strongest next step now.';
        $countryWelcomeText = $countryMessages[$countryCode] ?? $defaultCountryMessage;
        if ($lpPromiseSnippet !== '') {
            $countryWelcomeText .= ' Main promise on this page: "' . $lpPromiseSnippet . '".';
        }
        $countryWelcomeTitle = $countryName !== '' && $countryName !== 'Unknown'
            ? 'Special Welcome - ' . $countryName
            : 'Special Welcome';
    @endphp


    <!-- Facebook Pixel -->
    @if($landing->settings && $landing->settings->fb_pixel_id)
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $landing->settings->fb_pixel_id }}');
        fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $landing->settings->fb_pixel_id }}&ev=PageView&noscript=1"
        /></noscript>
    @endif

    <!-- Custom Body Scripts -->
    @if($landing->settings && $landing->settings->custom_body_scripts)
        {!! $landing->settings->custom_body_scripts !!}
    @endif
    <!-- Internal Tracking -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/api/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    landing_id: {{ $landing->id }},
                    page_id: {{ $page->id ?? 'null' }},
                    type: 'page_view'
                })
            }).catch(console.error);
        });
    </script>

    <!-- Session Recording (rrweb injected dynamically by controller) -->
    <script src="/js/countdown.js" defer></script>
    <script src="/js/external-slider-touch-runtime.js" defer></script>
    <script src="/js/lp-slider-runtime.js" defer></script>
    <script src="/js/analytics.js?v={{ filemtime(public_path('js/analytics.js')) }}" defer></script>
    <script src="/js/exit-intent.js" defer></script>
    <!-- Settings & WhatsApp Application -->
    @php
        $wsSettings = $landing->workspace->settings ?? null;
        $showSummary = $wsSettings->thankyou_show_summary ?? true;
        
        $pageData = [];
        $whatsappData = null;

        if ($page->type === 'thankyou' && isset($lead)) {
             $pageData = [
                'email' => $lead->email ?? $lead->data['email'] ?? $lead->data['billing_email'] ?? '',
                'phone' => $lead->phone ?? $lead->data['phone'] ?? $lead->data['billing_phone'] ?? '',
                'customerName' => $lead->customer_name ?? $lead->name ?? '',
                'orderId' => 'ORD-' . $lead->id,
                'productName' => (function() use ($lead) {
                    if (!empty($lead->order_items)) {
                        $items = is_string($lead->order_items) ? json_decode($lead->order_items, true) : $lead->order_items;
                        if (is_array($items)) {
                            return collect($items)->map(function($i) {
                                $name = $i['name'] ?? $i['title'] ?? 'Product';
                                $qty = $i['qty'] ?? 1;
                                return $qty > 1 ? "$name (x$qty)" : $name;
                            })->join(', ');
                        }
                    }
                    return $lead->product->name ?? 'Product';
                })(), 
                'amount' => ($lead->currency ?? 'USD') . ' ' . number_format($lead->amount ?? 0, 2),
                'paymentMethod' => ucfirst($lead->payment_provider ?? 'N/A'),
                'status' => ucfirst($lead->status ?? 'pending'),
                'date' => $lead->created_at->format('M d, Y'),
                'leadId' => $lead->id, 
                'invoiceUrl' => \Illuminate\Support\Facades\URL::signedRoute('invoices.download', $lead),
            ];
            
            // Fix: If email is missing in top level but present in data
            if (empty($pageData['customerName'])) {
                 $pageData['customerName'] = $lead->data['billing_first_name'] ?? 'Guest';
            }
         
            if ($wsSettings && $wsSettings->whatsapp_enabled && $wsSettings->whatsapp_redirect_enabled) {
                 try {
                     $waService = new \App\Services\WhatsAppService();
                     $template = $wsSettings->whatsapp_template_thankyou ?? 'Hello {{ customer-name }}, thank you for your order!';
                     $message = $waService->renderThankYouMessage($template, $lead, $landing);
                     $url = $waService->generateUrl($wsSettings->whatsapp_phone, $message);
                     
                     $whatsappData = [
                        'url' => $url,
                        'delay' => ($wsSettings->whatsapp_redirect_seconds ?? 5) * 1000,
                        'openNewTab' => $wsSettings->whatsapp_open_new_tab ?? true
                     ];
                 } catch (\Exception $e) {
                     \Illuminate\Support\Facades\Log::error('WhatsApp Redirect Error: ' . $e->getMessage());
                 }
            }
        }
    @endphp

    {{-- Hide Summary Table if Disabled --}}
    @if(!$showSummary)
    <style>
        .max-w-3xl > .bg-white.shadow { display: none !important; }
    </style>
    @endif

    {{-- Dynamic Data Injection & WhatsApp Redirect --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pageType = "{{ $page->type }}";
            // Use defaults to avoid JS syntax errors if PHP vars are empty
            const pageData = @json($pageData);
            const whatsappData = @json($whatsappData);

            if (pageType === 'thankyou' && Object.keys(pageData).length > 0) {
                // 1. Inject Data into Placeholder Elements
                const update = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.innerText = val;
                };

                update('crm-fullname', pageData.customerName);
                update('crm-email', pageData.email);
                update('crm-phone', pageData.phone);
                update('crm-order-id', pageData.orderId);
                update('crm-product', pageData.productName);
                update('crm-amount', pageData.amount);
                update('crm-total', pageData.amount); // Update total as well
                update('crm-payment', pageData.paymentMethod);
                update('crm-status', pageData.status);
                update('crm-date', pageData.date);
                
                // Invoice Button
                const btn = document.getElementById('crm-invoice-btn');
                if (btn) {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        window.location.href = pageData.invoiceUrl;
                    });
                }
                
                // 2. WhatsApp Auto-Redirect
                if (whatsappData) {
                    const notification = document.createElement('div');
                    notification.style.cssText = "position:fixed; bottom:20px; right:20px; background:#25D366; color:white; padding:15px 25px; border-radius:50px; font-family:sans-serif; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:9999; display:flex; align-items:center; gap:10px; animation: slideIn 0.5s ease-out;";
                    notification.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.592 2.654-.696c1.062.579 2.147.882 3.805.882 3.193 0 5.765-2.586 5.765-5.766.001-3.181-2.575-5.765-5.764-5.765zM12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.58 20 4 16.42 4 12C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12C20 16.42 16.42 20 12 20Z"/></svg> <span>Redirecting to WhatsApp in ${whatsappData.delay/1000}s...</span>`;
                    document.body.appendChild(notification);
                    
                    // Add animation keyframe
                    const style = document.createElement('style');
                    style.innerHTML = `@keyframes slideIn { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }`;
                    document.head.appendChild(style);

                    setTimeout(() => {
                        if (whatsappData.openNewTab) {
                            window.open(whatsappData.url, '_blank');
                            notification.innerHTML = '<span>Opened WhatsApp!</span> <a href="#" style="color:white;text-decoration:underline;margin-left:5px" onclick="window.location.reload()">Refresh?</a>';
                        } else {
                            window.location.href = whatsappData.url;
                        }
                    }, whatsappData.delay);
                }
            }
        });
    </script>
    <script>
            // 3. Robust Form Handling (CSRF, Landing ID, Auto-naming)
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const landingId = "{{ $landing->id }}";

            if (csrfToken) {
                document.querySelectorAll('form').forEach(form => {
                    // 1. Inject CSRF Token
                    if (!form.querySelector('input[name="_token"]')) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_token';
                        input.value = csrfToken;
                        form.appendChild(input);
                    } else {
                        // Update existing if present (user manual script case)
                        form.querySelector('input[name="_token"]').value = csrfToken;
                    }

                    // 2. Inject Landing ID
                    if (landingId && !form.querySelector('input[name="landing_id"]')) {
                        const lInput = document.createElement('input');
                        lInput.type = 'hidden';
                        lInput.name = 'landing_id';
                        lInput.value = landingId;
                        form.appendChild(lInput);
                    }

                    // 3. Auto-name inputs (Crucial for dynamic forms to send data)
                    form.querySelectorAll('input, textarea, select').forEach((el, index) => {
                        // Skip our hidden fields
                        if (el.name === '_token' || el.name === 'landing_id') return;

                        if (!el.name) {
                            // Generate a name based on type or label if possible, or just generic
                            const type = el.getAttribute('type') || el.tagName.toLowerCase();
                            el.name = `field_${type}_${index}`;
                        }
                    });
                });
            }
    </script>

    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const topbar = document.getElementById('lp-adminbar');
            if (!topbar) return;

            const closeMenus = () => {
                topbar.querySelectorAll('.lp-user-menu').forEach(menu => {
                    menu.hidden = true;
                });
            };

            topbar.querySelectorAll('[data-lp-toggle]').forEach(button => {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const targetId = button.getAttribute('data-lp-toggle');
                    const target = document.getElementById(targetId);
                    if (!target) return;

                    const willOpen = target.hidden;
                    closeMenus();
                    target.hidden = !willOpen;
                });
            });

            topbar.querySelectorAll('[data-copy]').forEach(button => {
                button.addEventListener('click', async function () {
                    const value = button.getAttribute('data-copy') || '';
                    if (!value) return;

                    const originalLabel = button.getAttribute('data-copy-label') || button.textContent.trim();
                    const notify = (label) => {
                        button.textContent = label;
                        setTimeout(() => {
                            button.textContent = originalLabel;
                        }, 1300);
                    };

                    try {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(value);
                        } else {
                            const input = document.createElement('input');
                            input.value = value;
                            document.body.appendChild(input);
                            input.select();
                            document.execCommand('copy');
                            document.body.removeChild(input);
                        }
                        notify('Copied');
                    } catch (error) {
                        notify('Copy failed');
                    }
                });
            });

            document.addEventListener('click', function (event) {
                if (!topbar.contains(event.target)) {
                    closeMenus();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMenus();
                }
            });
        });
    </script>
    @endauth

    @php
        $showAiChatbot = !auth()->check() || !auth()->user()->hasAnyRole(['super-admin', 'admin']);
    @endphp
    @if($showAiChatbot)
    @php
        $aiWelcome = $countryWelcomeText;
        $isPlaceholderPromise = $lpPromiseSnippet !== '' && (
            str_contains(mb_strtolower($lpPromiseSnippet), 'insert your html here')
            || str_contains(mb_strtolower($lpPromiseSnippet), 'templateseeder.php')
        );
        $offerAnchor = (!$isPlaceholderPromise && $lpPromiseSnippet !== '')
            ? $lpPromiseSnippet
            : (trim((string) optional($landing->settings)->meta_title) ?: $offerLabelForChat);
        $offerAnchor = \Illuminate\Support\Str::limit($offerAnchor, 95, '...');
        $aiCommercialIntro = $assistantProfileName . ' ici. Je suis votre closer pour cette offre. Mon role: vous qualifier vite, traiter vos doutes, puis vous pousser vers la meilleure action.';
    @endphp
    <div id="lp-ai-chat" class="lp-ai-chat" data-endpoint="{{ route('public.ai.chat') }}" data-landing-id="{{ $landing->id }}" data-page-id="{{ $page->id ?? '' }}" data-assistant-name="{{ $assistantProfileName }}">
        <div id="lp-ai-chat-intro" class="lp-ai-chat__intro is-visible" role="status" aria-live="polite">
            <div class="lp-ai-chat__intro-head">
                <p class="lp-ai-chat__intro-title">{{ $assistantProfileName }} - Assistant de l'offre</p>
                <button id="lp-ai-chat-intro-close" class="lp-ai-chat__intro-close" type="button" aria-label="Fermer l intro">&times;</button>
            </div>
            <p class="lp-ai-chat__intro-text">{{ $aiCommercialIntro }}</p>
        </div>
        <div id="lp-ai-chat-panel" class="lp-ai-chat__panel" aria-hidden="true">
            <div class="lp-ai-chat__head">
                <div>
                    <p class="lp-ai-chat__title">{{ $assistantProfileName }}</p>
                    <p class="lp-ai-chat__subtitle">Je vous aide a sortir du blocage et passer a l action avec la bonne prochaine etape.</p>
                </div>
                <button id="lp-ai-chat-close" class="lp-ai-chat__close" type="button" aria-label="Fermer assistant">&times;</button>
            </div>
            <div id="lp-ai-chat-messages" class="lp-ai-chat__messages" aria-live="polite">
                <div class="lp-ai-chat__message lp-ai-chat__message--assistant">{{ $aiWelcome }}</div>
            </div>
            <div class="lp-ai-chat__composer">
                <p id="lp-ai-chat-status" class="lp-ai-chat__status">Donnez-moi votre objectif en 1 phrase, et je vous donne la meilleure prochaine action.</p>
                <form id="lp-ai-chat-form" class="lp-ai-chat__form">
                    <textarea id="lp-ai-chat-input" class="lp-ai-chat__input" rows="1" maxlength="1200" placeholder="Posez une question sur l'offre, le prix ou les resultats attendus..." required></textarea>
                    <button id="lp-ai-chat-send" class="lp-ai-chat__send" type="submit">Envoyer</button>
                </form>
            </div>
        </div>
        <button id="lp-ai-chat-toggle" class="lp-ai-chat__toggle" type="button" aria-controls="lp-ai-chat-panel" aria-expanded="false" aria-label="Ouvrir assistant">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor" aria-hidden="true">
                <path d="M4 5.75A2.75 2.75 0 0 1 6.75 3h10.5A2.75 2.75 0 0 1 20 5.75v7.5A2.75 2.75 0 0 1 17.25 16H11l-3.84 3.2A.75.75 0 0 1 6 18.62V16h-.25A2.75 2.75 0 0 1 3 13.25v-7.5zm4.75 2.5a1 1 0 1 0 0 2h6.5a1 1 0 1 0 0-2h-6.5zm0 3.75a1 1 0 1 0 0 2h4.25a1 1 0 1 0 0-2H8.75z"/>
            </svg>
        </button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.getElementById('lp-ai-chat');
            if (!root) return;
            if (root.parentElement !== document.body) {
                document.body.appendChild(root);
            }

            const endpoint = root.dataset.endpoint || '';
            const landingId = Number(root.dataset.landingId || 0);
            const pageIdRaw = root.dataset.pageId || '';
            const pageId = pageIdRaw !== '' ? Number(pageIdRaw) : null;
            const assistantName = (root.dataset.assistantName || 'Samya').trim() || 'Samya';
            const panel = document.getElementById('lp-ai-chat-panel');
            const toggle = document.getElementById('lp-ai-chat-toggle');
            const closeBtn = document.getElementById('lp-ai-chat-close');
            const intro = document.getElementById('lp-ai-chat-intro');
            const introCloseBtn = document.getElementById('lp-ai-chat-intro-close');
            const messagesEl = document.getElementById('lp-ai-chat-messages');
            const statusEl = document.getElementById('lp-ai-chat-status');
            const form = document.getElementById('lp-ai-chat-form');
            const input = document.getElementById('lp-ai-chat-input');
            const sendBtn = document.getElementById('lp-ai-chat-send');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            if (!endpoint || !landingId || !panel || !toggle || !messagesEl || !statusEl || !form || !input || !sendBtn) {
                return;
            }

            let isBusy = false;
            let history = [];
            let typingBubble = null;
            let latestCta = null;
            let proactiveTimer = null;
            let proactiveShown = false;
            let silenceTimer = null;
            let silenceStep = 0;
            let userHasMessaged = false;

            const PROACTIVE_DELAY_MS = 8000;
            const FOLLOW_UP_1_DELAY_MS = 60000;
            const FOLLOW_UP_2_DELAY_MS = 90000;

            const proactiveOpeners = [
                'You are still dealing with the same pain point, right? What is the biggest thing blocking results for you right now?',
                'If this problem keeps draining your time, why let it continue one more week? What is your main goal now?',
                'Most people stay stuck because they wait too long. What exactly do you want to fix first right now?'
            ];

            const fomoLines = [
                'Honestly, people who wait usually come back after the best window is gone.',
                'The visitors who move now are usually the ones who get results first.',
                'If this pain is real for you, delaying it rarely makes it easier.',
                'Most people who decide quickly break the cycle faster.',
                'If this already feels relevant, your best move is to lock the next step now.'
            ];

            const setOpen = (open) => {
                panel.classList.toggle('is-open', open);
                panel.setAttribute('aria-hidden', open ? 'false' : 'true');
                root.classList.toggle('is-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open && intro) {
                    intro.classList.remove('is-visible');
                }
                if (open) {
                    input.focus();
                }
            };

            const setStatus = (text, isError = false) => {
                statusEl.textContent = text;
                statusEl.classList.toggle('is-error', isError);
            };

            const pickRandom = (items) => {
                if (!Array.isArray(items) || items.length === 0) return '';
                return items[Math.floor(Math.random() * items.length)] || '';
            };

            const clearProactiveTimer = () => {
                if (!proactiveTimer) return;
                clearTimeout(proactiveTimer);
                proactiveTimer = null;
            };

            const clearSilenceTimer = () => {
                if (!silenceTimer) return;
                clearTimeout(silenceTimer);
                silenceTimer = null;
            };

            const getCtaLink = () => {
                const cta = latestCta && typeof latestCta === 'object' ? latestCta : null;
                const target = cta && typeof cta.target === 'string' ? cta.target.trim() : '';
                if (target) return target;
                return window.location.href;
            };

            const buildCtaPushLine = () => {
                const cta = latestCta && typeof latestCta === 'object' ? latestCta : null;
                const actionText = cta && typeof cta.action_text === 'string' && cta.action_text.trim() !== ''
                    ? cta.action_text.trim()
                    : 'Take the next step now';
                return actionText + ': ' + getCtaLink();
            };

            const runSilenceNudge = () => {
                if (!userHasMessaged || isBusy) {
                    return;
                }

                if (silenceStep === 0) {
                    const nudge1 = 'Quick check: this offer helps you solve the current pain faster. What is stopping you from starting today?';
                    addMessage('assistant', nudge1);
                    history.push({ role: 'assistant', content: nudge1 });
                    history = history.slice(-10);
                    setStatus('If you are serious about results, answer this and I will guide your next move.');
                    silenceStep = 1;
                    silenceTimer = setTimeout(runSilenceNudge, FOLLOW_UP_2_DELAY_MS);
                    return;
                }

                if (silenceStep === 1) {
                    const nudge2 = 'Final push: ' + pickRandom(fomoLines) + ' Ready to move now?';
                    const ctaDrop = buildCtaPushLine();
                    addMessage('assistant', nudge2);
                    addMessage('assistant', ctaDrop);
                    history.push({ role: 'assistant', content: nudge2 });
                    history.push({ role: 'assistant', content: ctaDrop });
                    history = history.slice(-10);
                    addCtaAction(latestCta);
                    setStatus('Final step shared. Take action from the CTA now.');
                    silenceStep = 2;
                    clearSilenceTimer();
                }
            };

            const scheduleSilenceNudges = () => {
                clearSilenceTimer();
                if (!userHasMessaged || isBusy || silenceStep >= 2) {
                    return;
                }
                silenceStep = 0;
                silenceTimer = setTimeout(runSilenceNudge, FOLLOW_UP_1_DELAY_MS);
            };

            const scheduleProactiveOpener = () => {
                clearProactiveTimer();
                if (proactiveShown || userHasMessaged || isBusy) {
                    return;
                }
                proactiveTimer = setTimeout(() => {
                    if (proactiveShown || userHasMessaged || isBusy) {
                        return;
                    }
                    proactiveShown = true;
                    const opener = pickRandom(proactiveOpeners);
                    if (!opener) {
                        return;
                    }
                    addMessage('assistant', opener);
                    history.push({ role: 'assistant', content: opener });
                    history = history.slice(-10);
                    setStatus('Reply with your biggest pain point and I will guide your best next step.');
                }, PROACTIVE_DELAY_MS);
            };

            const normalizeText = (value) => {
                return (value || '')
                    .toString()
                    .toLowerCase()
                    .replace(/[^a-z0-9\s]/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            };

            const isGreetingMessage = (value) => {
                const text = normalizeText(value);
                if (!text) return false;

                const tokens = text.split(' ').filter(Boolean);
                if (tokens.length > 5) {
                    return false;
                }

                const greetingKeywords = new Set([
                    'hi', 'hello', 'hey', 'yo', 'salut', 'bonjour', 'bonsoir', 'coucou',
                    'salam', 'slm', 'marhba', 'mar7ba', 'ahlan', 'hola'
                ]);

                return tokens.some((token) => greetingKeywords.has(token));
            };

            const detectSimpleLang = (value) => {
                const text = normalizeText(value);
                if (/\b(bonjour|bonsoir|salut|coucou)\b/.test(text)) return 'fr';
                if (/\b(salam|slm|marhba|mar7ba|ahlan)\b/.test(text)) return 'darija';
                return 'en';
            };

            const buildGreetingReply = (value) => {
                const lang = detectSimpleLang(value);
                if (lang === 'fr') {
                    return 'Salut, moi c est ' + assistantName + '. Si vous avez encore ce blocage, on le traite maintenant. Quel est votre objectif principal?';
                }
                if (lang === 'darija') {
                    return 'Salam, ana ' + assistantName + '. Ila mazal nafs lmochkil kay7besk, nkhdmo 3lih daba. Chno lhadaf dyalk daba?';
                }
                return 'Hi, I am ' + assistantName + '. If this pain point is still blocking you, let us fix it now. What is your main goal right now?';
            };

            const addMessage = (role, text) => {
                const bubble = document.createElement('div');
                bubble.className = `lp-ai-chat__message lp-ai-chat__message--${role}`;
                bubble.textContent = text;
                bubble.classList.add('is-entering');
                messagesEl.appendChild(bubble);
                requestAnimationFrame(() => {
                    bubble.classList.add('is-visible');
                    bubble.classList.remove('is-entering');
                });
                messagesEl.scrollTop = messagesEl.scrollHeight;
            };

            const scrollAndFlashTarget = (el) => {
                if (!el) return;
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('lp-ai-chat__target-flash');
                setTimeout(() => el.classList.remove('lp-ai-chat__target-flash'), 1500);
            };

            const findTargetByCtaLabel = (label) => {
                if (!label || typeof label !== 'string') {
                    return null;
                }

                const targetLabel = label.trim().toLowerCase();
                if (!targetLabel) {
                    return null;
                }

                const candidates = Array.from(document.querySelectorAll('a, button, input[type="submit"], input[type="button"], [role="button"]'));
                return candidates.find((el) => {
                    const text = (el.textContent || el.value || '').trim().toLowerCase();
                    if (!text) return false;
                    return text.includes(targetLabel) || targetLabel.includes(text);
                }) || null;
            };

            const findFirstFormField = () => {
                return document.querySelector('form input:not([type="hidden"]):not([type="submit"]):not([type="button"]), form textarea, form select');
            };

            const addCtaAction = (cta) => {
                if (!cta || typeof cta !== 'object') {
                    return;
                }
                latestCta = cta;

                const ctaType = typeof cta.type === 'string' && cta.type.trim() !== ''
                    ? cta.type.trim()
                    : 'form';

                const actionLabel = typeof cta.action_text === 'string' && cta.action_text.trim() !== ''
                    ? cta.action_text.trim()
                    : 'Passer a l action';

                const targetValue = typeof cta.target === 'string' ? cta.target.trim() : '';

                const wrap = document.createElement('div');
                wrap.className = 'lp-ai-chat__action-wrap';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'lp-ai-chat__action-btn';
                btn.textContent = actionLabel;

                btn.addEventListener('click', () => {
                    if (ctaType === 'whatsapp' && targetValue) {
                        const phone = targetValue.replace(/[^0-9]/g, '');
                        if (phone) {
                            const waMessage = encodeURIComponent('Bonjour, je souhaite plus de details sur cette offre.');
                            window.open(`https://wa.me/${phone}?text=${waMessage}`, '_blank', 'noopener');
                            return;
                        }
                    }

                    if (ctaType === 'instagram' && targetValue) {
                        window.open(targetValue, '_blank', 'noopener');
                        return;
                    }

                    if (ctaType === 'custom_link' && targetValue) {
                        window.open(targetValue, '_blank', 'noopener');
                        return;
                    }

                    if (ctaType === 'custom_phone' && targetValue) {
                        const phone = targetValue.replace(/\s+/g, '');
                        if (phone) {
                            window.location.href = `tel:${phone}`;
                            return;
                        }
                    }

                    const ctaTarget = findTargetByCtaLabel(cta.label || '');
                    if (ctaTarget) {
                        scrollAndFlashTarget(ctaTarget);
                        return;
                    }

                    const fieldTarget = findFirstFormField();
                    if (fieldTarget) {
                        scrollAndFlashTarget(fieldTarget);
                        fieldTarget.focus({ preventScroll: true });
                    }
                });

                wrap.appendChild(btn);
                messagesEl.appendChild(wrap);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            };

            const showTyping = () => {
                if (typingBubble) return;
                typingBubble = document.createElement('div');
                typingBubble.className = 'lp-ai-chat__message lp-ai-chat__message--assistant lp-ai-chat__typing is-visible';
                typingBubble.innerHTML = '<span class="lp-ai-chat__typing-label">L assistant prepare sa reponse</span><span class="lp-ai-chat__typing-dots" aria-hidden="true"><span></span><span></span><span></span></span>';
                messagesEl.appendChild(typingBubble);
                messagesEl.scrollTop = messagesEl.scrollHeight;
            };

            const hideTyping = () => {
                if (!typingBubble) return;
                typingBubble.remove();
                typingBubble = null;
            };

            const autoResizeInput = () => {
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 110) + 'px';
            };

            toggle.addEventListener('click', function () {
                setOpen(!panel.classList.contains('is-open'));
            });

            if (intro) {
                intro.addEventListener('click', function (event) {
                    if (event.target && event.target.id === 'lp-ai-chat-intro-close') {
                        return;
                    }
                    setOpen(true);
                });
            }

            if (introCloseBtn) {
                introCloseBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (intro) {
                        intro.classList.remove('is-visible');
                    }
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    setOpen(false);
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && panel.classList.contains('is-open')) {
                    setOpen(false);
                }
            });

            input.addEventListener('input', autoResizeInput);
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    form.requestSubmit();
                }
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const message = input.value.trim();
                if (!message || isBusy) {
                    return;
                }

                addMessage('user', message);
                history.push({ role: 'user', content: message });
                history = history.slice(-10);
                userHasMessaged = true;
                proactiveShown = true;
                clearProactiveTimer();
                clearSilenceTimer();
                input.value = '';
                autoResizeInput();

                if (isGreetingMessage(message)) {
                    const greetingReply = buildGreetingReply(message);
                    addMessage('assistant', greetingReply);
                    history.push({ role: 'assistant', content: greetingReply });
                    history = history.slice(-10);
                    setStatus('Parfait. Donnez-moi votre blocage principal et je vous donne la meilleure prochaine action.');
                    scheduleSilenceNudges();
                    input.focus();
                    return;
                }

                setStatus('Analyse en cours de cette offre pour preparer une recommandation claire...');
                showTyping();

                isBusy = true;
                input.disabled = true;
                sendBtn.disabled = true;

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            landing_id: landingId,
                            page_id: pageId,
                            message: message,
                            history: history,
                            current_url: window.location.href
                        })
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok || data.status !== 'success' || typeof data.reply !== 'string') {
                        throw new Error(data.message || 'Assistant indisponible pour le moment.');
                    }

                    const reply = data.reply.trim() || 'Je peux vous aider sur cette offre et les details de commande.';
                    hideTyping();
                    if (data && data.cta && typeof data.cta === 'object') {
                        latestCta = data.cta;
                    }
                    addMessage('assistant', reply);
                    addCtaAction(data.cta);
                    history.push({ role: 'assistant', content: reply });
                    history = history.slice(-10);
                    setStatus('Recu. Repondez en 1 phrase et je vous pousse vers la meilleure prochaine etape.');
                    scheduleSilenceNudges();
                } catch (error) {
                    hideTyping();
                    const fallback = 'Je suis temporairement indisponible. Merci de reessayer dans un instant.';
                    const friendly = (error && typeof error.message === 'string' && error.message.trim() !== '')
                        ? error.message.trim()
                        : fallback;
                    addMessage('assistant', friendly);
                    setStatus(friendly, true);
                } finally {
                    isBusy = false;
                    input.disabled = false;
                    sendBtn.disabled = false;
                    input.focus();
                }
            });

            autoResizeInput();

            // Proactive opener after inactivity.
            scheduleProactiveOpener();
            const activityEvents = ['click', 'keydown', 'scroll', 'mousemove', 'touchstart'];
            activityEvents.forEach((eventName) => {
                document.addEventListener(eventName, function () {
                    if (!proactiveShown && !userHasMessaged) {
                        scheduleProactiveOpener();
                    } else if (userHasMessaged && silenceStep < 2) {
                        scheduleSilenceNudges();
                    }
                }, { passive: true });
            });

            // Auto-hide outside intro after short time if user ignores it.
            if (intro) {
                setTimeout(() => {
                    if (!panel.classList.contains('is-open')) {
                        intro.classList.remove('is-visible');
                    }
                }, 18000);
            }
        });
    </script>
    @endif

    {{-- Floating WhatsApp Button --}}
    @if($wsSettings && $wsSettings->whatsapp_enabled && !empty($wsSettings->whatsapp_phone))
        @php
            $waMessage = $wsSettings->whatsapp_template_landing ?? 'I want to know more about this offer.';
            // Clean phone number
            $waPhone = preg_replace('/[^0-9]/', '', $wsSettings->whatsapp_phone);
            $waUrl = 'https://wa.me/' . $waPhone . '?text=' . urlencode($waMessage);
        @endphp
        <!-- WhatsApp Button -->
        <div class="fixed bottom-6 left-6 z-[9999] group animate-fade-in-up">
            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" 
               class="flex items-center justify-center w-14 h-14 bg-[#25D366] text-white rounded-full shadow-[0_4px_14px_0_rgba(37,211,102,0.39)] hover:shadow-[0_6px_20px_rgba(37,211,102,0.23)] hover:bg-[#20bd5a] transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 focus:outline-none ring-4 ring-transparent focus:ring-[#25D366]/50">
                <svg viewBox="0 0 24 24" class="w-8 h-8 fill-current" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </a>
            <!-- Tooltip -->
            <div class="absolute bottom-full left-0 mb-3 w-max max-w-[250px] p-3 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 text-sm font-medium rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 text-center border border-gray-100 dark:border-gray-700">
                {{ $waMessage }}
                <!-- Arrow -->
                <div class="absolute -bottom-1.5 left-6 w-3 h-3 bg-white dark:bg-gray-800 transform rotate-45 border-r border-b border-gray-100 dark:border-gray-700"></div>
            </div>
        </div>
    @endif
</body>
</html>
