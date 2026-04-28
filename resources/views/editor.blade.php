<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editor - {{ $landing->name }}</title>
    @vite(['resources/js/editor.js'])
    <style>
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
        }

        .editor-topbar {
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .editor-title-wrap {
            min-width: 0;
        }

        .editor-title {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .editor-subtitle {
            margin: 2px 0 0;
            font-size: 12px;
            color: #64748b;
        }

        .editor-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .editor-last-saved {
            font-size: 12px;
            color: #64748b;
            margin-right: 8px;
            white-space: nowrap;
        }

        .editor-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            border-radius: 6px;
            height: 34px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .editor-btn:hover {
            background: #f8fafc;
        }

        .editor-btn-primary {
            border-color: #2563eb;
            background: #2563eb;
            color: #ffffff;
        }

        .editor-btn-primary:hover {
            background: #1d4ed8;
        }

        #gjs-editor {
            height: calc(100vh - 56px);
            opacity: 1;
            transition: opacity 220ms ease;
        }

        body.editor-booting #gjs-editor {
            opacity: 0;
            pointer-events: none;
        }

        .editor-boot-overlay {
            position: fixed;
            top: 56px;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1200;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 220ms ease;
        }

        body:not(.editor-booting) .editor-boot-overlay {
            opacity: 0;
            pointer-events: none;
        }

        .editor-boot-card {
            width: min(560px, calc(100vw - 40px));
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 20px;
        }

        .editor-boot-title {
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .editor-boot-subtitle {
            margin: 0;
            font-size: 13px;
            color: #64748b;
        }

        .editor-boot-status {
            margin: 14px 0 12px;
            font-size: 12px;
            color: #334155;
            font-weight: 600;
        }

        .editor-boot-skeleton {
            display: grid;
            gap: 8px;
        }

        .editor-boot-line {
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(90deg, #e2e8f0 0%, #f8fafc 50%, #e2e8f0 100%);
            background-size: 220% 100%;
            animation: editorBootShimmer 1.4s linear infinite;
        }

        .editor-boot-line:nth-child(1) { width: 100%; }
        .editor-boot-line:nth-child(2) { width: 92%; }
        .editor-boot-line:nth-child(3) { width: 84%; }
        .editor-boot-line:nth-child(4) { width: 76%; }

        @keyframes editorBootShimmer {
            0% { background-position: 220% 0; }
            100% { background-position: -220% 0; }
        }
    </style>
</head>
<body class="editor-booting">
    <header class="editor-topbar">
        <div class="editor-title-wrap">
            <p class="editor-title">{{ $landing->name }} / {{ $page->name }}</p>
            <p class="editor-subtitle">Funnel Builder Plugin Editor</p>
        </div>
        <div class="editor-actions">
            <span id="editor-last-saved" class="editor-last-saved">Booting...</span>
            <button id="btn-restore-snapshot" type="button" class="editor-btn">Restore Autosave</button>
            <button id="btn-save-block" type="button" class="editor-btn">Save Block</button>
            <button id="btn-insert-template" type="button" class="editor-btn">Insert Template</button>
            <button id="btn-seo" type="button" class="editor-btn">SEO</button>
            <button type="button" class="editor-btn" onclick="window.editorInstance?.runCommand('core:undo')">Undo</button>
            <button type="button" class="editor-btn" onclick="window.editorInstance?.runCommand('core:redo')">Redo</button>
            <a href="{{ route('landings.index') }}" class="editor-btn">Back</a>
            <button id="btn-preview" type="button" class="editor-btn">Preview</button>
            <button id="btn-save" type="button" class="editor-btn editor-btn-primary">Save</button>
        </div>
    </header>

    <main id="gjs-editor"></main>
    <div id="editor-boot-overlay" class="editor-boot-overlay" role="status" aria-live="polite">
        <div class="editor-boot-card">
            <p class="editor-boot-title">Preparing your funnel editor</p>
            <p class="editor-boot-subtitle">Loading installed plugins and page styles...</p>
            <p id="editor-boot-status" class="editor-boot-status">Starting editor engine...</p>
            <div class="editor-boot-skeleton">
                <div class="editor-boot-line"></div>
                <div class="editor-boot-line"></div>
                <div class="editor-boot-line"></div>
                <div class="editor-boot-line"></div>
            </div>
        </div>
    </div>

    <div id="gjs-html" style="display:none">{!! $page->html !!}</div>
    <div id="gjs-css" style="display:none">{!! $page->css !!}</div>
    <div id="gjs-js" style="display:none">{!! e($page->js ?? '') !!}</div>

    <script>
        window.editorData = {
            landingId: {{ $landing->id }},
            pageId: {{ $page->id }},
            saveUrl: "{{ route('landings.pages.update', [$landing, $page]) }}",
            previewUrl: "{{ route('landings.preview', [$landing, $page]) }}",
            builderCssUrl: @json(\Illuminate\Support\Facades\Vite::asset('resources/css/app.css')),
            csrfToken: "{{ csrf_token() }}",
            grapesJsJson: {!! $page->grapesjs_json ?? 'null' !!},
            forceHtmlMode: {!! !empty($forceHtmlMode) ? 'true' : 'false' !!},
            editorMode: "editor",
            workspacePlugins: @json($activeEditorPlugins ?? []),
            initialSeoMeta: {
                title: @json(optional($landing->settings)->meta_title),
                description: @json(optional($landing->settings)->meta_description),
                ogTitle: "",
                ogDescription: "",
                canonical: ""
            }
        };
    </script>
</body>
</html>
