<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
            {{ $template->exists ? 'Edit Template' : 'Create Template' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 md:px-8">
            @include('email-automation._subnav')

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 px-4 py-3 text-sm border border-red-100 dark:border-red-900/50">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $action }}" method="POST" class="space-y-6">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                @php
                    $mergeTags = [
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'product_name',
                        'order_total',
                        'landing_page_name',
                        'unsubscribe_url',
                    ];
                @endphp

                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Internal Name</label>
                            <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                                   class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <input type="text" name="status" value="{{ old('status', $template->status ?: 'active') }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Preview Text</label>
                        <input type="text" name="preview_text" value="{{ old('preview_text', $template->preview_text) }}"
                               class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                    </div>

                    <div class="relative" data-merge-tag-wrapper>
                        <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Body (HTML)</label>
                        <div class="mb-3 grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_170px_auto_auto] gap-2">
                            <input
                                type="text"
                                id="ai-email-brief"
                                placeholder="AI brief (e.g. Welcome email for new lead with product benefits)"
                                class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white"
                            >
                            <select
                                id="ai-email-tone"
                                class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white"
                            >
                                <option value="professional and persuasive">Professional</option>
                                <option value="friendly and warm">Friendly</option>
                                <option value="urgent and action-driven">Urgent</option>
                                <option value="premium and elegant">Premium</option>
                            </select>
                            <button
                                type="button"
                                id="ai-generate-email-body-btn"
                                class="inline-flex items-center justify-center px-4 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg hover:bg-brand-orange-600 shadow-sm transition-colors"
                            >
                                Generate Body with AI
                            </button>
                            <button
                                type="button"
                                id="preview-email-template-btn"
                                class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 dark:bg-slate-700 dark:hover:bg-slate-600 shadow-sm transition-colors"
                            >
                                Preview (Inline)
                            </button>
                        </div>
                        <p id="ai-email-generate-status" class="mb-2 text-xs text-gray-500 dark:text-gray-400"></p>

                        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-white/[0.08] bg-[#0B1220] shadow-sm" data-code-editor-wrapper>
                            <div class="flex items-center justify-between px-3 py-2 bg-[#111827] border-b border-white/10">
                                <span class="text-[11px] uppercase tracking-wider font-semibold text-slate-300">HTML Editor</span>
                                <span class="text-[11px] text-slate-400">Tab / Shift+Tab for indent</span>
                            </div>

                            <div class="grid grid-cols-[52px_minmax(0,1fr)]">
                                <pre data-code-editor-gutter class="m-0 whitespace-pre overflow-hidden select-none text-right px-3 py-3 text-xs font-mono leading-6 text-slate-500 bg-[#020617]/90 border-r border-white/10">1</pre>
                                <textarea
                                    name="body_html"
                                    rows="16"
                                    required
                                    data-code-editor
                                    data-merge-tag-autocomplete
                                    data-merge-tags='@json($mergeTags)'
                                    spellcheck="false"
                                    class="block w-full min-h-[420px] resize-y border-0 bg-transparent text-slate-100 placeholder:text-slate-500 font-mono text-sm leading-6 px-4 py-3 focus:outline-none focus:ring-0"
                                >{{ old('body_html', $template->body_html) }}</textarea>
                            </div>
                        </div>

                        <div id="email-inline-preview" class="hidden rounded-xl overflow-hidden border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0B1220] shadow-sm">
                            <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-[#111827] border-b border-gray-200 dark:border-white/10">
                                <span class="text-[11px] uppercase tracking-wider font-semibold text-gray-700 dark:text-slate-300">Email Preview</span>
                                <button
                                    type="button"
                                    id="email-preview-back-btn"
                                    class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-md text-xs font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 dark:text-slate-200 dark:bg-slate-700 dark:border-slate-600 dark:hover:bg-slate-600"
                                >
                                    Back To Editor
                                </button>
                            </div>
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-white/[0.08] bg-gray-50 dark:bg-[#0F172A]">
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">Subject</p>
                                <p id="email-preview-subject" class="text-sm font-semibold text-gray-900 dark:text-white"></p>
                                <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">Preview Text</p>
                                <p id="email-preview-text" class="text-sm text-gray-700 dark:text-gray-300"></p>
                            </div>
                            <div class="bg-slate-200 dark:bg-[#020617] p-3 sm:p-5">
                                <div class="mx-auto w-full max-w-[760px] rounded-xl border border-slate-300/80 dark:border-slate-600/60 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.18)] overflow-hidden">
                                    <div class="px-3 py-2 text-[11px] font-medium text-slate-500 bg-slate-50 border-b border-slate-200">
                                        Live Email Canvas · 680px
                                    </div>
                                    <iframe
                                        id="email-preview-iframe"
                                        title="Email Preview"
                                        class="w-full h-[68vh] min-h-[460px] border-0 bg-white block"
                                        sandbox="allow-same-origin"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Type <span class="font-mono">&#123;&#123;</span> to insert merge tags quickly.</p>
                    </div>

                    <div class="rounded-lg bg-gray-50 dark:bg-white/[0.02] border border-gray-200 dark:border-white/[0.06] p-4">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">Supported Merge Tags</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            @foreach($mergeTags as $tag)
                                <code class="inline-block font-mono text-[11px]">&#123;&#123;{{ $tag }}&#125;&#125;</code>@if(! $loop->last), @endif
                            @endforeach
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('email-automation.templates.index') }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-white/[0.06] text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg hover:bg-brand-orange-600 shadow-sm transition-colors">
                        Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('ai-generate-email-body-btn');
            const previewButton = document.getElementById('preview-email-template-btn');
            const briefInput = document.getElementById('ai-email-brief');
            const toneSelect = document.getElementById('ai-email-tone');
            const statusEl = document.getElementById('ai-email-generate-status');
            const bodyTextarea = document.querySelector('textarea[name="body_html"]');
            const codeEditorWrapper = document.querySelector('[data-code-editor-wrapper]');
            const subjectInput = document.querySelector('input[name="subject"]');
            const previewInput = document.querySelector('input[name="preview_text"]');
            const inlinePreview = document.getElementById('email-inline-preview');
            const previewBackBtn = document.getElementById('email-preview-back-btn');
            const previewSubject = document.getElementById('email-preview-subject');
            const previewText = document.getElementById('email-preview-text');
            const previewIframe = document.getElementById('email-preview-iframe');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const endpoint = "{{ route('email-automation.templates.generate-body') }}";

            if (!button || !bodyTextarea || !subjectInput || !csrfToken) {
                return;
            }

            const setStatus = (text, type = 'info') => {
                if (!statusEl) return;
                statusEl.textContent = text || '';
                statusEl.className = 'mb-2 text-xs ';
                if (type === 'error') {
                    statusEl.className += 'text-red-600 dark:text-red-300';
                    return;
                }
                if (type === 'success') {
                    statusEl.className += 'text-emerald-600 dark:text-emerald-300';
                    return;
                }
                statusEl.className += 'text-gray-500 dark:text-gray-400';
            };

            const setLoading = (loading) => {
                button.disabled = loading;
                button.classList.toggle('opacity-70', loading);
                button.textContent = loading ? 'Generating...' : 'Generate Body with AI';
            };

            const previewReplacements = {
                first_name: 'John',
                last_name: 'Doe',
                email: 'john.doe@example.com',
                phone: '+1 555 123 4567',
                product_name: 'Premium Plan',
                order_total: '$99.00',
                landing_page_name: 'Landing Builder',
                unsubscribe_url: 'https://example.com/unsubscribe',
            };

            const applyMergeTagPreview = (html) => {
                return String(html || '').replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, (full, tag) => {
                    const key = String(tag || '').trim();
                    return Object.prototype.hasOwnProperty.call(previewReplacements, key)
                        ? previewReplacements[key]
                        : full;
                });
            };

            const buildPreviewDocument = (rawHtml) => {
                const html = applyMergeTagPreview(rawHtml || '').trim();
                const hasDocumentTags = /<(?:!doctype|html|head|body)\b/i.test(html);

                if (hasDocumentTags) {
                    if (/<!doctype/i.test(html)) {
                        return html;
                    }
                    return `<!doctype html>\n${html}`;
                }

                const content = html !== ''
                    ? html
                    : '<div style="padding:32px 24px;font-family:Arial,sans-serif;color:#334155;text-align:center;">No HTML body to preview yet.</div>';

                return `
                    <!doctype html>
                    <html>
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <title>Email Preview</title>
                            <style>
                                html, body { margin: 0; padding: 0; background: #eef2f7; }
                                body { min-height: 100vh; }
                                * { box-sizing: border-box; }
                                .preview-wrap { padding: 24px 12px; }
                                .preview-shell { max-width: 680px; width: 100%; margin: 0 auto; background: #ffffff; border: 1px solid #dbe2ea; border-radius: 12px; overflow: hidden; box-shadow: 0 14px 32px rgba(15,23,42,0.08); }
                            </style>
                        </head>
                        <body>
                            <div class="preview-wrap">
                                <div class="preview-shell">${content}</div>
                            </div>
                        </body>
                    </html>
                `;
            };

            const openPreview = () => {
                if (!inlinePreview || !previewIframe || !codeEditorWrapper) return;

                const subject = (subjectInput.value || '').trim();
                const preheader = (previewInput?.value || '').trim();
                const htmlBody = (bodyTextarea.value || '').trim();

                if (previewSubject) {
                    previewSubject.textContent = subject || '(No subject yet)';
                }
                if (previewText) {
                    previewText.textContent = preheader || '(No preview text yet)';
                }

                previewIframe.srcdoc = buildPreviewDocument(htmlBody);
                codeEditorWrapper.classList.add('hidden');
                inlinePreview.classList.remove('hidden');
                previewButton?.classList.add('hidden');
            };

            const closePreview = () => {
                if (!inlinePreview || !codeEditorWrapper) return;
                inlinePreview.classList.add('hidden');
                codeEditorWrapper.classList.remove('hidden');
                previewButton?.classList.remove('hidden');
            };

            button.addEventListener('click', async function () {
                const subject = (subjectInput.value || '').trim();
                const previewText = (previewInput?.value || '').trim();
                const brief = (briefInput?.value || '').trim();
                const tone = (toneSelect?.value || 'professional and persuasive').trim();

                if (!subject && !previewText && !brief) {
                    setStatus('Add at least a subject or a short AI brief first.', 'error');
                    return;
                }

                setLoading(true);
                setStatus('Generating professional email body...');

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            subject,
                            preview_text: previewText,
                            brief,
                            tone,
                        }),
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        throw new Error(payload.message || 'AI generation failed.');
                    }

                    const generatedHtml = (payload.body_html || '').toString().trim();
                    if (!generatedHtml) {
                        throw new Error('AI returned empty HTML body.');
                    }

                    bodyTextarea.value = generatedHtml;
                    bodyTextarea.dispatchEvent(new Event('input', { bubbles: true }));

                    if (!subject && payload.subject_suggestion && typeof payload.subject_suggestion === 'string') {
                        subjectInput.value = payload.subject_suggestion.trim();
                    }

                    setStatus('Email body generated successfully.', 'success');
                } catch (error) {
                    setStatus(error.message || 'AI generation failed.', 'error');
                } finally {
                    setLoading(false);
                }
            });

            previewButton?.addEventListener('click', openPreview);
            previewBackBtn?.addEventListener('click', closePreview);
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && inlinePreview && !inlinePreview.classList.contains('hidden')) {
                    closePreview();
                }
            });
        });
    </script>
</x-app-layout>
