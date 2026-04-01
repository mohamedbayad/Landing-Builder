/**
 * Plugin: AI Assistant
 *
 * Bridges the AI backend with the GrapesJS editor. Adds:
 *   - "✨ AI" button to the component toolbar
 *   - Modal dialog for AI actions (improve copy, generate section)
 *   - Async API calls to /editor/ai/* endpoints
 */
export default function aiAssistantPlugin(editor, opts = {}) {

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ─── Helpers ──────────────────────────────────────────────────

    async function apiCall(endpoint, body) {
        const res = await fetch(`/editor/ai/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({ message: 'Unknown error' }));
            throw new Error(err.message || `API error ${res.status}`);
        }

        return res.json();
    }

    function getTextContent(cmp) {
        const el = cmp.getEl();
        return el?.innerText?.trim() || '';
    }

    function getElementTag(cmp) {
        return (cmp.get('tagName') || 'div').toLowerCase();
    }

    function getSurroundingContext(cmp) {
        const parent = cmp.parent();
        if (!parent) return '';
        const el = parent.getEl();
        return el?.innerText?.trim()?.substring(0, 500) || '';
    }

    function showToast(msg, type = 'info') {
        // Use the editor's built-in notification or a simple DOM toast
        const colors = {
            info: '#6366f1',
            success: '#10b981',
            error: '#ef4444',
        };

        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; top: 16px; right: 16px; z-index: 99999;
            background: ${colors[type] || colors.info}; color: white;
            padding: 12px 20px; border-radius: 10px; font-size: 13px;
            font-family: -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            animation: gjsToastIn 0.3s ease-out;
            max-width: 360px;
        `;
        toast.textContent = msg;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ─── AI Modal ─────────────────────────────────────────────────

    function createAIModal() {
        const modal = editor.Modal;

        return {
            showImproveCopy(cmp) {
                const text = getTextContent(cmp);
                const tag = getElementTag(cmp);

                if (!text) {
                    showToast('Select a text element first.', 'error');
                    return;
                }

                const content = document.createElement('div');
                content.innerHTML = `
                    <div style="font-family: -apple-system, system-ui, sans-serif; color: #e0e0e0;">
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #888; display: block; margin-bottom: 6px;">Original Text</label>
                            <div style="background: #1a1a2e; border: 1px solid #333; border-radius: 8px; padding: 12px; font-size: 14px; color: #ccc; max-height: 100px; overflow-y: auto;">${text.substring(0, 300)}${text.length > 300 ? '...' : ''}</div>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #888; display: block; margin-bottom: 6px;">Direction (optional)</label>
                            <input id="ai-direction" type="text" placeholder="e.g. make it more urgent, add numbers, shorter..."
                                style="width: 100%; background: #1a1a2e; border: 1px solid #333; border-radius: 8px; padding: 10px 12px; color: #e0e0e0; font-size: 14px; outline: none; box-sizing: border-box;"
                            />
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #888; display: block; margin-bottom: 6px;">Tone</label>
                            <select id="ai-tone" style="width: 100%; background: #1a1a2e; border: 1px solid #333; border-radius: 8px; padding: 10px 12px; color: #e0e0e0; font-size: 14px; outline: none; box-sizing: border-box;">
                                <option value="professional and persuasive">Professional & Persuasive</option>
                                <option value="urgent and scarcity-driven">Urgent & Scarcity</option>
                                <option value="friendly and conversational">Friendly & Casual</option>
                                <option value="luxury and premium">Premium & Luxury</option>
                                <option value="bold and provocative">Bold & Provocative</option>
                            </select>
                        </div>
                        <div id="ai-result-container" style="display: none; margin-bottom: 16px;">
                            <label style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #888; display: block; margin-bottom: 6px;">✨ Improved</label>
                            <div id="ai-result" style="background: #0d1a0d; border: 1px solid #10b981; border-radius: 8px; padding: 12px; font-size: 14px; color: #a7f3d0;"></div>
                        </div>
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button id="ai-generate-btn" style="padding: 10px 20px; background: #6366f1; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; transition: background 0.2s;">
                                ✨ Improve with AI
                            </button>
                            <button id="ai-apply-btn" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; display: none; transition: background 0.2s;">
                                ✓ Apply
                            </button>
                        </div>
                        <div id="ai-loading" style="display: none; text-align: center; padding: 20px; color: #888;">
                            <div style="width: 24px; height: 24px; border: 3px solid #333; border-top: 3px solid #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 8px;"></div>
                            Generating...
                        </div>
                    </div>
                    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                `;

                modal.open({
                    title: '✨ AI Copy Improver',
                    content,
                    attributes: { class: 'gjs-ai-modal' },
                });

                let improvedText = '';

                content.querySelector('#ai-generate-btn').addEventListener('click', async () => {
                    const direction = content.querySelector('#ai-direction').value;
                    const tone = content.querySelector('#ai-tone').value;
                    const loading = content.querySelector('#ai-loading');
                    const resultContainer = content.querySelector('#ai-result-container');
                    const resultEl = content.querySelector('#ai-result');
                    const applyBtn = content.querySelector('#ai-apply-btn');
                    const genBtn = content.querySelector('#ai-generate-btn');

                    genBtn.style.display = 'none';
                    loading.style.display = 'block';
                    resultContainer.style.display = 'none';

                    try {
                        const data = await apiCall('improve-copy', {
                            text,
                            element_tag: tag,
                            context: getSurroundingContext(cmp),
                            instruction: direction,
                            tone,
                        });

                        improvedText = data.improved;
                        resultEl.textContent = improvedText;
                        resultContainer.style.display = 'block';
                        applyBtn.style.display = 'inline-block';
                        genBtn.textContent = '🔄 Try Again';
                        genBtn.style.display = 'inline-block';
                    } catch (err) {
                        showToast('AI Error: ' + err.message, 'error');
                        genBtn.style.display = 'inline-block';
                    } finally {
                        loading.style.display = 'none';
                    }
                });

                content.querySelector('#ai-apply-btn').addEventListener('click', () => {
                    if (improvedText) {
                        // Apply the improved text to the component
                        const el = cmp.getEl();
                        if (el) {
                            // For elements with children, set inner text carefully
                            const childComponents = cmp.components();
                            if (childComponents.length === 0) {
                                cmp.components(improvedText);
                            } else {
                                // Replace all text nodes
                                el.childNodes.forEach(node => {
                                    if (node.nodeType === 3) { // TEXT_NODE
                                        node.textContent = improvedText;
                                    }
                                });
                                // Sync GrapesJS model from DOM
                                cmp.components(el.innerHTML);
                            }
                        }
                        showToast('Applied ✓', 'success');
                        modal.close();
                    }
                });
            },

            showGenerateSection() {
                const content = document.createElement('div');
                content.innerHTML = `
                    <div style="font-family: -apple-system, system-ui, sans-serif; color: #e0e0e0;">
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #888; display: block; margin-bottom: 6px;">Section Type</label>
                            <select id="ai-section-type" style="width: 100%; background: #1a1a2e; border: 1px solid #333; border-radius: 8px; padding: 10px 12px; color: #e0e0e0; font-size: 14px; outline: none; box-sizing: border-box;">
                                <option value="hero">Hero Section</option>
                                <option value="features">Features / Benefits</option>
                                <option value="testimonials">Testimonials</option>
                                <option value="faq">FAQ / Accordion</option>
                                <option value="pricing">Pricing Cards</option>
                                <option value="cta">Call to Action</option>
                                <option value="guarantee">Guarantee / Trust</option>
                                <option value="comparison">Comparison Table</option>
                                <option value="social-proof">Social Proof / Stats</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #888; display: block; margin-bottom: 6px;">Product / Service Name</label>
                            <input id="ai-product" type="text" placeholder="e.g. Premium Skin Cream"
                                style="width: 100%; background: #1a1a2e; border: 1px solid #333; border-radius: 8px; padding: 10px 12px; color: #e0e0e0; font-size: 14px; outline: none; box-sizing: border-box;"
                            />
                        </div>
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #888; display: block; margin-bottom: 6px;">Description / Extra Instructions</label>
                            <textarea id="ai-desc" rows="3" placeholder="Describe what you want in this section..."
                                style="width: 100%; background: #1a1a2e; border: 1px solid #333; border-radius: 8px; padding: 10px 12px; color: #e0e0e0; font-size: 14px; outline: none; resize: vertical; box-sizing: border-box;"
                            ></textarea>
                        </div>
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button id="ai-gen-section-btn" style="padding: 10px 20px; background: #6366f1; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">
                                ✨ Generate Section
                            </button>
                        </div>
                        <div id="ai-section-loading" style="display: none; text-align: center; padding: 20px; color: #888;">
                            <div style="width: 24px; height: 24px; border: 3px solid #333; border-top: 3px solid #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 8px;"></div>
                            Generating section...
                        </div>
                    </div>
                    <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                `;

                modal.open({
                    title: '✨ AI Section Generator',
                    content,
                    attributes: { class: 'gjs-ai-modal' },
                });

                content.querySelector('#ai-gen-section-btn').addEventListener('click', async () => {
                    const type = content.querySelector('#ai-section-type').value;
                    const product = content.querySelector('#ai-product').value;
                    const description = content.querySelector('#ai-desc').value;
                    const loading = content.querySelector('#ai-section-loading');
                    const btn = content.querySelector('#ai-gen-section-btn');

                    btn.style.display = 'none';
                    loading.style.display = 'block';

                    try {
                        const data = await apiCall('suggest-section', {
                            type,
                            product,
                            description,
                        });

                        if (data.html) {
                            // Insert the generated section into the canvas
                            const wrapper = editor.getWrapper();
                            const selected = editor.getSelected();
                            const target = selected?.parent() || wrapper;

                            const added = target.append(data.html);
                            if (added?.length > 0) {
                                editor.select(added[added.length - 1]);
                            }

                            showToast('Section added ✓', 'success');
                            modal.close();
                        } else {
                            showToast('AI returned empty content', 'error');
                            btn.style.display = 'inline-block';
                        }
                    } catch (err) {
                        showToast('AI Error: ' + err.message, 'error');
                        btn.style.display = 'inline-block';
                    } finally {
                        loading.style.display = 'none';
                    }
                });
            },
        };
    }

    // ─── Commands Registration ────────────────────────────────────

    const aiModal = createAIModal();

    editor.Commands.add('ai:improve-copy', {
        run(ed) {
            const cmp = ed.getSelected();
            if (!cmp) {
                showToast('Select a text element first.', 'error');
                return;
            }
            aiModal.showImproveCopy(cmp);
        },
    });

    editor.Commands.add('ai:generate-section', {
        run() {
            aiModal.showGenerateSection();
        },
    });

    // ─── Component Toolbar Button ─────────────────────────────────

    // Add AI button to default toolbar for text-like components
    editor.on('component:selected', (cmp) => {
        const tag = getElementTag(cmp);
        const textTags = ['h1','h2','h3','h4','h5','h6','p','span','a','button','li','label','td','th','blockquote'];

        if (textTags.includes(tag)) {
            const toolbar = cmp.get('toolbar') || [];

            // Avoid duplicates
            if (!toolbar.some(t => t.id === 'ai-improve')) {
                toolbar.push({
                    id: 'ai-improve',
                    label: '✨',
                    command: 'ai:improve-copy',
                    attributes: { title: 'Improve with AI' },
                });
                cmp.set('toolbar', toolbar);
            }
        }
    });

    // ─── AI Section Block (in Block Manager) ─────────────────────

    editor.BlockManager.add('ai-generate-section', {
        label: '✨ AI Section',
        category: 'AI',
        attributes: { class: 'fa fa-magic' },
        content: { type: 'text', content: 'Generating...' },
        // Override: instead of dropping content, open the AI modal
        select: true,
        activate: true,
    });

    // When the AI block is dropped, immediately open the generator
    editor.on('block:drag:stop', (cmp, block) => {
        if (block?.getId?.() === 'ai-generate-section') {
            // Remove the placeholder
            if (cmp) cmp.remove();
            editor.runCommand('ai:generate-section');
        }
    });

    // Inject toast animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes gjsToastIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .gjs-ai-modal .gjs-mdl-dialog { background: #1e1e2e !important; border: 1px solid #333 !important; border-radius: 12px !important; }
        .gjs-ai-modal .gjs-mdl-header { border-bottom: 1px solid #333 !important; color: #e0e0e0 !important; }
        .gjs-ai-modal .gjs-mdl-title { color: #e0e0e0 !important; }
    `;
    document.head.appendChild(style);

    console.log('[GrapesJS] AI Assistant plugin loaded. (✨ button on text, AI Section block)');
}
