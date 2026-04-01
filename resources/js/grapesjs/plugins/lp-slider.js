export default function lpSliderPlugin(editor, opts = {}) {
    const CATEGORY = opts.blockCategory || 'Landing Page Elements';
    const TRACK_CLASS = 'lp-slider__track swiper-wrapper';
    const SLIDE_CLASS = 'lp-slider__slide swiper-slide';
    const EDITOR_STYLE_ID = 'lp-slider-editor-style';
    const RUNTIME_SCRIPT_ID = 'lp-slider-runtime-script';
    const SLIDES_MODAL_STYLE_ID = 'lp-slider-slides-modal-style';

    const bool = (v, fb = false) => {
        if (typeof v === 'boolean') return v;
        if (v == null || v === '') return fb;
        return ['1', 'true', 'yes', 'on'].includes(String(v).toLowerCase());
    };
    const jparse = (raw, fb = []) => {
        try {
            const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
            return Array.isArray(parsed) ? parsed : fb;
        } catch {
            return fb;
        }
    };

    const placeholder = (label) => {
        const safe = String(label || 'Slide').replace(/[<>&"']/g, '');
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#111827" offset="0%"/><stop stop-color="#1f2937" offset="100%"/></linearGradient></defs><rect fill="url(#g)" width="1200" height="900"/><text x="50%" y="50%" fill="#f9fafb" font-family="Inter,Arial,sans-serif" font-size="64" font-weight="700" dominant-baseline="middle" text-anchor="middle">${safe}</text></svg>`;
        return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
    };

    const PRESETS = {
        gallery: {
            attrs: { 'data-slides-desktop': '3', 'data-slides-tablet': '2', 'data-slides-mobile': '1', 'data-space-between': '16', 'data-autoplay': 'false', 'data-loop': 'true', 'data-arrows': 'true', 'data-dots': 'true', 'data-image-fit': 'cover', 'data-ratio': '4:5', 'data-shadow': 'medium', 'data-enable-captions': 'false', 'data-enable-links': 'false', 'data-lightbox': 'true', 'data-smooth-scroll': 'false', 'data-speed': '6500' },
            slides: [{ src: placeholder('Gallery 1'), alt: 'Gallery 1', caption: '', href: '' }, { src: placeholder('Gallery 2'), alt: 'Gallery 2', caption: '', href: '' }, { src: placeholder('Gallery 3'), alt: 'Gallery 3', caption: '', href: '' }, { src: placeholder('Gallery 4'), alt: 'Gallery 4', caption: '', href: '' }],
        },
        logos: {
            attrs: { 'data-slides-desktop': '5', 'data-slides-tablet': '3', 'data-slides-mobile': '2', 'data-space-between': '24', 'data-autoplay': 'true', 'data-autoplay-delay': '0', 'data-loop': 'true', 'data-arrows': 'false', 'data-dots': 'false', 'data-image-fit': 'contain', 'data-ratio': '1:1', 'data-shadow': 'none', 'data-enable-captions': 'false', 'data-enable-links': 'false', 'data-lightbox': 'false', 'data-smooth-scroll': 'true', 'data-speed': '7000' },
            slides: [{ src: placeholder('Logo 1'), alt: 'Logo 1', caption: '', href: '' }, { src: placeholder('Logo 2'), alt: 'Logo 2', caption: '', href: '' }, { src: placeholder('Logo 3'), alt: 'Logo 3', caption: '', href: '' }, { src: placeholder('Logo 4'), alt: 'Logo 4', caption: '', href: '' }, { src: placeholder('Logo 5'), alt: 'Logo 5', caption: '', href: '' }],
        },
        testimonials: {
            attrs: { 'data-slides-desktop': '2', 'data-slides-tablet': '1', 'data-slides-mobile': '1', 'data-space-between': '20', 'data-autoplay': 'true', 'data-autoplay-delay': '4500', 'data-loop': 'true', 'data-arrows': 'true', 'data-dots': 'true', 'data-image-fit': 'cover', 'data-ratio': '16:9', 'data-shadow': 'medium', 'data-enable-captions': 'true', 'data-enable-links': 'false', 'data-lightbox': 'false', 'data-smooth-scroll': 'false', 'data-speed': '800' },
            slides: [{ src: placeholder('Story 1'), alt: 'Testimonial 1', caption: '"Fast results." - Sarah M.', href: '' }, { src: placeholder('Story 2'), alt: 'Testimonial 2', caption: '"Super clean workflow." - David K.', href: '' }, { src: placeholder('Story 3'), alt: 'Testimonial 3', caption: '"High quality outcome." - Lina R.', href: '' }],
        },
        'product-showcase': {
            attrs: { 'data-slides-desktop': '3', 'data-slides-tablet': '2', 'data-slides-mobile': '1', 'data-space-between': '18', 'data-autoplay': 'false', 'data-loop': 'true', 'data-arrows': 'true', 'data-dots': 'true', 'data-image-fit': 'cover', 'data-ratio': '4:5', 'data-shadow': 'large', 'data-enable-captions': 'true', 'data-enable-links': 'true', 'data-lightbox': 'true', 'data-smooth-scroll': 'false', 'data-speed': '800' },
            slides: [{ src: placeholder('Product 1'), alt: 'Product 1', caption: 'Front view', href: '#' }, { src: placeholder('Product 2'), alt: 'Product 2', caption: 'Detail shot', href: '#' }, { src: placeholder('Product 3'), alt: 'Product 3', caption: 'In use', href: '#' }],
        },
        'social-proof': {
            attrs: { 'data-slides-desktop': '4', 'data-slides-tablet': '2', 'data-slides-mobile': '1', 'data-space-between': '14', 'data-autoplay': 'true', 'data-autoplay-delay': '0', 'data-loop': 'true', 'data-arrows': 'true', 'data-dots': 'true', 'data-image-fit': 'cover', 'data-ratio': '4:5', 'data-shadow': 'small', 'data-enable-captions': 'true', 'data-enable-links': 'true', 'data-lightbox': 'true', 'data-smooth-scroll': 'true', 'data-speed': '7000' },
            slides: [{ src: placeholder('UGC 1'), alt: 'UGC 1', caption: 'Real customer result #1', href: '#' }, { src: placeholder('UGC 2'), alt: 'UGC 2', caption: 'Real customer result #2', href: '#' }, { src: placeholder('UGC 3'), alt: 'UGC 3', caption: 'Real customer result #3', href: '#' }, { src: placeholder('UGC 4'), alt: 'UGC 4', caption: 'Real customer result #4', href: '#' }],
        },
    };

    const STYLE_DEFAULTS = { 'data-image-fit': 'cover', 'data-ratio': '4:5', 'data-custom-height': '360', 'data-border-radius': '16', 'data-shadow': 'medium', 'data-overlay': 'false', 'data-caption-align': 'left', 'data-lazy': 'true' };

    const normalizeSlides = (slides) => (Array.isArray(slides) ? slides : []).map((slide, idx) => {
        const s = slide || {};
        return {
            src: String(s.src || '').trim() || placeholder(`Slide ${idx + 1}`),
            alt: String(s.alt || '').trim() || `Slide ${idx + 1}`,
            caption: String(s.caption || '').trim(),
            href: String(s.href || '').trim(),
        };
    });

    const findSlider = (cmp) => {
        let current = cmp;
        while (current) {
            if (current.get?.('type') === 'lp-slider') return current;
            current = current.parent ? current.parent() : null;
        }
        return null;
    };

    const getSliderTarget = (ed, optsArg = {}) => findSlider(optsArg.component || ed.getSelected());

    const ensureRootAttrs = (cmp) => {
        const attrs = { ...(cmp.getAttributes() || {}) };
        attrs.class = Array.from(new Set(`${attrs.class || ''} lp-slider swiper`.trim().split(/\s+/).filter(Boolean))).join(' ');
        attrs['data-gjs-type'] = 'lp-slider';
        attrs['data-component'] = 'lp-slider';
        attrs['data-slider-version'] = '1.0.0';
        attrs['data-nav-position'] = attrs['data-nav-position'] || 'outside';
        attrs['data-thumbnails'] = attrs['data-thumbnails'] || 'placeholder';
        attrs['data-prev-label'] = attrs['data-prev-label'] || 'Previous slide';
        attrs['data-next-label'] = attrs['data-next-label'] || 'Next slide';
        attrs['data-smooth-scroll'] = attrs['data-smooth-scroll'] || 'true';
        attrs['data-speed'] = attrs['data-speed'] || '6500';
        attrs['data-space-between'] = attrs['data-space-between'] || attrs['data-gap'] || '24';
        attrs['data-gap'] = attrs['data-gap'] || attrs['data-space-between'];
        attrs.role = attrs.role || 'region';
        attrs['aria-roledescription'] = attrs['aria-roledescription'] || 'carousel';
        attrs['aria-label'] = attrs['aria-label'] || 'Image slider';
        cmp.addAttributes(attrs);
    };

    const readSlidesFromMarkup = (cmp) => {
        const html = cmp.toHTML ? cmp.toHTML() : '';
        if (!html) return [];
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const track = doc.querySelector('[data-slider-track], .lp-slider__track');
        if (!track) return [];
        const directSlides = Array.from(track.querySelectorAll(':scope > [data-slider-slide], :scope > .lp-slider__slide'));
        const slides = directSlides.length ? directSlides : Array.from(track.querySelectorAll('[data-slider-slide], .lp-slider__slide'));
        return normalizeSlides(slides.map((slideEl, idx) => ({
            src: slideEl.querySelector('[data-slider-image], .lp-slider__image, img')?.getAttribute('src') || '',
            alt: slideEl.querySelector('[data-slider-image], .lp-slider__image, img')?.getAttribute('alt') || `Slide ${idx + 1}`,
            caption: slideEl.querySelector('.lp-slider__caption')?.textContent?.trim() || '',
            href: slideEl.querySelector('a[href]')?.getAttribute('href') || '',
        })));
    };

    const slideToNode = (slide, idx, attrs) => {
        const img = {
            type: 'image',
            tagName: 'img',
            attributes: {
                class: 'lp-slider__image',
                'data-slider-image': 'true',
                src: slide.src,
                alt: slide.alt || `Slide ${idx + 1}`,
                loading: bool(attrs['data-lazy'], true) ? 'lazy' : 'eager',
            },
        };
        const mediaChildren = [img];
        if (bool(attrs['data-overlay'])) {
            mediaChildren.push({ type: 'default', tagName: 'div', attributes: { class: 'lp-slider__overlay', 'aria-hidden': 'true' } });
        }
        const media = { type: 'default', tagName: 'div', attributes: { class: 'lp-slider__media' }, components: mediaChildren };
        const children = [];
        if (bool(attrs['data-enable-links']) && slide.href) {
            children.push({ type: 'link', tagName: 'a', attributes: { class: 'lp-slider__link', href: slide.href, target: '_self', rel: 'noopener' }, components: [media] });
        } else {
            children.push(media);
        }
        if (bool(attrs['data-enable-captions']) && slide.caption) {
            children.push({ type: 'text', tagName: 'div', attributes: { class: 'lp-slider__caption' }, content: slide.caption });
        }
        return { type: 'default', tagName: 'div', attributes: { class: SLIDE_CLASS, 'data-slider-slide': 'true', 'data-slide-index': String(idx) }, components: children };
    };

    const refreshRuntime = (cmp) => {
        const el = cmp.getEl?.();
        if (!el) return;
        const runtime = el.ownerDocument?.defaultView?.LPSliderRuntime;
        if (runtime?.refresh) {
            runtime.refresh([el], { force: true });
            return;
        }
        try {
            el.dispatchEvent(new CustomEvent('lp-slider:refresh', { bubbles: true }));
        } catch {
            // no-op
        }
    };

    const syncSlider = (cmp) => {
        const attrs = cmp.getAttributes() || {};
        const slides = normalizeSlides(cmp.get('lpSlides'));
        const safeSlides = slides.length > 0 ? slides : normalizeSlides(PRESETS.gallery.slides);
        cmp.__lpSliderSyncing = true;
        ensureRootAttrs(cmp);
        const gap = String((attrs['data-space-between'] ?? attrs['data-gap'] ?? '24'));
        cmp.addAttributes({ 'data-lp-slides': JSON.stringify(safeSlides), 'data-slide-count': String(safeSlides.length) });
        cmp.addAttributes({ 'data-space-between': gap, 'data-gap': gap });
        cmp.components([
            { type: 'default', tagName: 'div', attributes: { class: TRACK_CLASS, 'data-slider-track': 'true' }, components: safeSlides.map((s, idx) => slideToNode(s, idx, attrs)) },
            { type: 'default', tagName: 'button', attributes: { class: 'lp-slider__arrow lp-slider__arrow--prev', type: 'button', 'aria-label': attrs['data-prev-label'] || 'Previous slide' }, content: '&#8249;' },
            { type: 'default', tagName: 'button', attributes: { class: 'lp-slider__arrow lp-slider__arrow--next', type: 'button', 'aria-label': attrs['data-next-label'] || 'Next slide' }, content: '&#8250;' },
            { type: 'default', tagName: 'div', attributes: { class: 'lp-slider__dots' } },
        ]);
        cmp.__lpSliderSyncing = false;
    };

    const applyPreset = (cmp, presetKey, replaceSlides = false) => {
        const key = PRESETS[presetKey] ? presetKey : 'gallery';
        const preset = PRESETS[key];
        cmp.__lpSliderSyncing = true;
        cmp.addAttributes({ ...(cmp.getAttributes() || {}), 'data-preset': key, ...preset.attrs });
        cmp.__lpSliderSyncing = false;
        if (replaceSlides || normalizeSlides(cmp.get('lpSlides')).length === 0) {
            cmp.set('lpSlides', normalizeSlides(preset.slides));
        } else {
            syncSlider(cmp);
            refreshRuntime(cmp);
        }
    };

    const openAssets = (onSelect) => {
        editor.AssetManager.open({
            types: ['image'],
            select(asset) {
                const src = (typeof asset === 'string' && asset) || asset?.get?.('src') || asset?.src || '';
                if (src) onSelect(String(src));
                editor.AssetManager.close();
            },
        });
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const shortenPath = (value, max = 64) => {
        const clean = String(value || '').trim();
        if (!clean) return 'No media selected';
        if (clean.length <= max) return clean;
        return `...${clean.slice(clean.length - max + 3)}`;
    };

    const ensureSlidesModalStyles = () => {
        const doc = document;
        if (doc.getElementById(SLIDES_MODAL_STYLE_ID)) return;
        const style = doc.createElement('style');
        style.id = SLIDES_MODAL_STYLE_ID;
        style.textContent = `
            .gjs-mdl-content:has(.lp-slider-modal) .gjs-mdl-header { display: none; }
            .gjs-mdl-content:has(.lp-slider-modal) { padding: 0 !important; background: #090d18 !important; border: 1px solid rgba(124, 141, 179, 0.3); border-radius: 16px; overflow: hidden; box-shadow: 0 26px 70px rgba(2, 6, 23, 0.65); }
            .gjs-mdl-dialog:has(.lp-slider-modal) { width: min(1080px, 94vw) !important; max-width: min(1080px, 94vw) !important; }
            .gjs-mdl-container:has(.lp-slider-modal) { background: rgba(2, 6, 23, 0.72) !important; backdrop-filter: blur(8px); }

            .lp-slider-modal {
                font-family: Inter, "Segoe UI", Arial, sans-serif;
                color: #e6ebff;
                width: min(1080px, 94vw);
                height: min(82vh, 860px);
                display: flex;
                flex-direction: column;
                background: radial-gradient(circle at 10% 0%, rgba(99, 102, 241, 0.16), transparent 52%), #090d18;
            }
            .lp-slider-modal__header {
                position: sticky;
                top: 0;
                z-index: 8;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                padding: 20px 24px 16px;
                background: linear-gradient(180deg, rgba(10, 14, 28, 0.97), rgba(10, 14, 28, 0.9));
                border-bottom: 1px solid rgba(94, 111, 151, 0.34);
            }
            .lp-slider-modal__title {
                margin: 0;
                font-size: 19px;
                line-height: 1.2;
                font-weight: 700;
                color: #f8faff;
                letter-spacing: 0.01em;
            }
            .lp-slider-modal__subtitle {
                margin: 8px 0 0;
                color: #95a3c7;
                font-size: 12px;
                line-height: 1.5;
            }
            .lp-slider-modal__toolbar {
                position: sticky;
                top: 74px;
                z-index: 7;
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 14px 24px;
                background: rgba(9, 13, 24, 0.92);
                border-bottom: 1px solid rgba(79, 95, 130, 0.26);
            }
            .lp-slider-modal__search {
                flex: 1;
                min-width: 0;
            }
            .lp-slider-modal__body {
                flex: 1;
                min-height: 0;
                overflow: auto;
                padding: 18px 24px 22px;
                display: flex;
                flex-direction: column;
                gap: 14px;
            }
            .lp-slider-modal__footer {
                position: sticky;
                bottom: 0;
                z-index: 8;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 14px 24px 18px;
                border-top: 1px solid rgba(79, 95, 130, 0.3);
                background: linear-gradient(180deg, rgba(9, 13, 24, 0.9), rgba(9, 13, 24, 0.98));
            }

            .lp-slider-btn {
                height: 34px;
                border-radius: 10px;
                border: 1px solid transparent;
                font-size: 12px;
                font-weight: 600;
                padding: 0 13px;
                cursor: pointer;
                transition: all 0.18s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                white-space: nowrap;
            }
            .lp-slider-btn:disabled { opacity: 0.44; cursor: not-allowed; }
            .lp-slider-btn--primary { color: #f5f7ff; background: linear-gradient(180deg, #5865f2, #4a56da); border-color: rgba(141, 155, 255, 0.64); box-shadow: 0 10px 22px rgba(74, 86, 218, 0.34); }
            .lp-slider-btn--primary:hover { background: linear-gradient(180deg, #6773ff, #5460eb); }
            .lp-slider-btn--secondary { color: #e4eafb; background: rgba(35, 45, 67, 0.8); border-color: rgba(102, 118, 155, 0.56); }
            .lp-slider-btn--secondary:hover { background: rgba(45, 57, 84, 0.95); }
            .lp-slider-btn--ghost { color: #c6d2ef; background: rgba(23, 31, 48, 0.64); border-color: rgba(88, 104, 139, 0.5); }
            .lp-slider-btn--ghost:hover { color: #eef3ff; background: rgba(39, 50, 72, 0.92); }
            .lp-slider-btn--danger { color: #ffd6dd; background: rgba(79, 19, 33, 0.7); border-color: rgba(211, 86, 112, 0.66); }
            .lp-slider-btn--danger:hover { background: rgba(106, 22, 42, 0.86); }
            .lp-slider-btn--icon { width: 34px; padding: 0; font-size: 18px; line-height: 1; }

            .lp-slider-input {
                width: 100%;
                height: 36px;
                background: rgba(14, 20, 35, 0.88);
                border: 1px solid rgba(84, 103, 142, 0.55);
                color: #e9efff;
                border-radius: 10px;
                padding: 0 12px;
                font-size: 12px;
                transition: border-color 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
                outline: none;
            }
            .lp-slider-input::placeholder { color: #7584a8; }
            .lp-slider-input:hover { border-color: rgba(109, 126, 164, 0.72); }
            .lp-slider-input:focus {
                border-color: rgba(131, 153, 255, 0.9);
                box-shadow: 0 0 0 3px rgba(88, 101, 242, 0.22);
                background: rgba(15, 22, 39, 0.96);
            }
            .lp-slider-input[disabled] { opacity: 0.7; cursor: not-allowed; }

            .lp-slide-card {
                border: 1px solid rgba(75, 92, 126, 0.5);
                border-radius: 14px;
                background: linear-gradient(180deg, rgba(14, 20, 36, 0.88), rgba(10, 15, 28, 0.96));
                box-shadow: 0 14px 24px rgba(2, 6, 23, 0.35);
                padding: 14px;
                display: flex;
                flex-direction: column;
                gap: 12px;
                transition: border-color 0.2s ease, transform 0.2s ease;
            }
            .lp-slide-card:hover { border-color: rgba(113, 131, 172, 0.74); transform: translateY(-1px); }
            .lp-slide-card__head {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 8px;
                padding-bottom: 10px;
                border-bottom: 1px solid rgba(64, 80, 113, 0.4);
            }
            .lp-slide-card__badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 62px;
                height: 24px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                color: #e5eafe;
                background: rgba(73, 88, 130, 0.58);
                border: 1px solid rgba(122, 138, 179, 0.58);
            }
            .lp-slide-card__meta {
                font-size: 11px;
                color: #93a2c8;
                margin-left: 10px;
                max-width: 280px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .lp-slide-card__actions {
                display: flex;
                gap: 6px;
                flex-wrap: wrap;
                justify-content: flex-end;
            }
            .lp-slide-card__body {
                display: grid;
                grid-template-columns: 180px minmax(0, 1fr);
                gap: 14px;
            }
            .lp-slide-media {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .lp-slide-media__thumb {
                width: 100%;
                aspect-ratio: 16 / 11;
                border-radius: 12px;
                overflow: hidden;
                border: 1px solid rgba(82, 102, 145, 0.56);
                background: #1a2238;
            }
            .lp-slide-media__thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
            .lp-slide-media__path {
                font-size: 11px;
                color: #8d9bbf;
                line-height: 1.35;
                min-height: 30px;
                word-break: break-all;
            }
            .lp-slide-form {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .lp-slide-form__inline {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 8px;
            }
            .lp-slide-form__grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }
            .lp-field {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
            .lp-field__label {
                color: #96a5cb;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 0.045em;
                text-transform: uppercase;
            }
            .lp-slider-empty {
                border: 1px dashed rgba(104, 122, 160, 0.55);
                border-radius: 12px;
                padding: 24px;
                color: #9aabd5;
                text-align: center;
                font-size: 13px;
                background: rgba(16, 24, 43, 0.7);
            }

            @media (max-width: 940px) {
                .lp-slider-modal { width: min(96vw, 96vw); height: min(88vh, 860px); }
                .lp-slider-modal__header,
                .lp-slider-modal__toolbar,
                .lp-slider-modal__body,
                .lp-slider-modal__footer { padding-left: 16px; padding-right: 16px; }
                .lp-slider-modal__toolbar { top: 82px; flex-wrap: wrap; }
                .lp-slider-modal__search { min-width: 100%; }
                .lp-slide-card__body { grid-template-columns: 1fr; }
                .lp-slide-media__thumb { max-width: 280px; }
                .lp-slide-form__grid { grid-template-columns: 1fr; }
                .lp-slide-card__head { align-items: flex-start; flex-direction: column; }
                .lp-slide-card__actions { width: 100%; justify-content: flex-start; }
            }
        `;
        doc.head.appendChild(style);
    };

    const renderSlidesModal = (cmp) => {
        ensureSlidesModalStyles();
        const slides = normalizeSlides(cmp.get('lpSlides'));
        if (slides.length === 0) slides.push(...normalizeSlides(PRESETS.gallery.slides));
        const root = document.createElement('div');
        root.className = 'lp-slider-modal';
        root.innerHTML = `
            <div class="lp-slider-modal__header">
                <div>
                    <h2 class="lp-slider-modal__title">LP Slider / Gallery</h2>
                    <p class="lp-slider-modal__subtitle">Manage your slider slides, media, captions, and links.</p>
                </div>
                <button data-action="close" type="button" class="lp-slider-btn lp-slider-btn--ghost lp-slider-btn--icon" aria-label="Close">×</button>
            </div>
            <div class="lp-slider-modal__toolbar">
                <input class="lp-slider-input lp-slider-modal__search" type="text" placeholder="Search slides (coming soon)" disabled />
                <button data-action="add" type="button" class="lp-slider-btn lp-slider-btn--primary">+ Add Slide</button>
            </div>
            <div class="lp-slider-modal__body" data-slot="rows"></div>
            <div class="lp-slider-modal__footer">
                <button data-action="cancel" type="button" class="lp-slider-btn lp-slider-btn--secondary">Cancel</button>
                <button data-action="save" type="button" class="lp-slider-btn lp-slider-btn--primary">Save Slides</button>
            </div>
        `;
        const rowSlot = root.querySelector('[data-slot="rows"]');
        const closeModal = () => editor.Modal.close();

        const renderRows = () => {
            rowSlot.innerHTML = '';
            if (!slides.length) {
                rowSlot.innerHTML = `<div class="lp-slider-empty">No slides yet. Add your first slide to start building this gallery.</div>`;
                return;
            }
            slides.forEach((slide, index) => {
                const row = document.createElement('div');
                row.dataset.index = String(index);
                row.className = 'lp-slide-card';
                const mediaPath = shortenPath(slide.src);
                const safeSrc = escapeHtml(slide.src);
                const safeAlt = escapeHtml(slide.alt);
                const safeCaption = escapeHtml(slide.caption);
                const safeHref = escapeHtml(slide.href);
                row.innerHTML = `
                    <div class="lp-slide-card__head">
                        <div>
                            <span class="lp-slide-card__badge">Slide ${index + 1}</span>
                            <span class="lp-slide-card__meta" title="${escapeHtml(mediaPath)}">${escapeHtml(mediaPath)}</span>
                        </div>
                        <div class="lp-slide-card__actions">
                            <button data-action="up" type="button" class="lp-slider-btn lp-slider-btn--ghost" ${index === 0 ? 'disabled' : ''}>Up</button>
                            <button data-action="down" type="button" class="lp-slider-btn lp-slider-btn--ghost" ${index >= slides.length - 1 ? 'disabled' : ''}>Down</button>
                            <button data-action="dup" type="button" class="lp-slider-btn lp-slider-btn--ghost">Duplicate</button>
                            <button data-action="remove" type="button" class="lp-slider-btn lp-slider-btn--danger">Remove</button>
                        </div>
                    </div>
                    <div class="lp-slide-card__body">
                        <div class="lp-slide-media">
                            <div class="lp-slide-media__thumb"><img data-field="preview" src="${safeSrc}" alt="" /></div>
                            <div class="lp-slide-media__path" data-field="path" title="${safeSrc}">${escapeHtml(mediaPath)}</div>
                        </div>
                        <div class="lp-slide-form">
                            <div class="lp-field">
                                <label class="lp-field__label">Media URL</label>
                                <div class="lp-slide-form__inline">
                                    <input data-field="src" class="lp-slider-input" type="text" value="${safeSrc}" placeholder="https://..." />
                                    <button data-action="media" type="button" class="lp-slider-btn lp-slider-btn--secondary">Media</button>
                                </div>
                            </div>
                            <div class="lp-slide-form__grid">
                                <div class="lp-field">
                                    <label class="lp-field__label">Title / Alt</label>
                                    <input data-field="alt" class="lp-slider-input" type="text" value="${safeAlt}" placeholder="Slide title" />
                                </div>
                                <div class="lp-field">
                                    <label class="lp-field__label">Link</label>
                                    <input data-field="href" class="lp-slider-input" type="text" value="${safeHref}" placeholder="https:// or /path" />
                                </div>
                            </div>
                            <div class="lp-field">
                                <label class="lp-field__label">Caption</label>
                                <input data-field="caption" class="lp-slider-input" type="text" value="${safeCaption}" placeholder="Short supporting copy" />
                            </div>
                        </div>
                    </div>
                `;
                row.querySelectorAll('[data-field]').forEach((input) => {
                    input.addEventListener('input', () => {
                        const idx = Number(row.dataset.index);
                        slides[idx].src = row.querySelector('[data-field="src"]').value.trim() || placeholder(`Slide ${idx + 1}`);
                        slides[idx].alt = row.querySelector('[data-field="alt"]').value.trim() || `Slide ${idx + 1}`;
                        slides[idx].caption = row.querySelector('[data-field="caption"]').value.trim();
                        slides[idx].href = row.querySelector('[data-field="href"]').value.trim();
                        row.querySelector('[data-field="preview"]').src = slides[idx].src;
                        const nextPath = shortenPath(slides[idx].src);
                        const pathNode = row.querySelector('[data-field="path"]');
                        pathNode.textContent = nextPath;
                        pathNode.title = slides[idx].src || nextPath;
                    });
                });
                row.querySelector('[data-action="media"]')?.addEventListener('click', () => openAssets((src) => {
                    row.querySelector('[data-field="src"]').value = src;
                    row.querySelector('[data-field="src"]').dispatchEvent(new Event('input'));
                }));
                row.querySelector('[data-action="up"]')?.addEventListener('click', () => {
                    const idx = Number(row.dataset.index);
                    if (idx <= 0) return;
                    [slides[idx - 1], slides[idx]] = [slides[idx], slides[idx - 1]];
                    renderRows();
                });
                row.querySelector('[data-action="down"]')?.addEventListener('click', () => {
                    const idx = Number(row.dataset.index);
                    if (idx >= slides.length - 1) return;
                    [slides[idx + 1], slides[idx]] = [slides[idx], slides[idx + 1]];
                    renderRows();
                });
                row.querySelector('[data-action="dup"]')?.addEventListener('click', () => {
                    const idx = Number(row.dataset.index);
                    slides.splice(idx + 1, 0, { ...slides[idx], alt: `${slides[idx].alt || `Slide ${idx + 1}`} (copy)` });
                    renderRows();
                });
                row.querySelector('[data-action="remove"]')?.addEventListener('click', () => {
                    const idx = Number(row.dataset.index);
                    slides.splice(idx, 1);
                    if (!slides.length) slides.push({ src: placeholder('Slide 1'), alt: 'Slide 1', caption: '', href: '' });
                    renderRows();
                });
                rowSlot.appendChild(row);
            });
        };

        root.querySelector('[data-action="add"]')?.addEventListener('click', () => {
            slides.push({ src: placeholder(`Slide ${slides.length + 1}`), alt: `Slide ${slides.length + 1}`, caption: '', href: '' });
            renderRows();
        });
        root.querySelector('[data-action="close"]')?.addEventListener('click', closeModal);
        root.querySelector('[data-action="cancel"]')?.addEventListener('click', closeModal);
        root.querySelector('[data-action="save"]')?.addEventListener('click', () => {
            const next = normalizeSlides(slides);
            cmp.set('lpSlides', next);
            cmp.addAttributes({ 'data-lp-slides': JSON.stringify(next) });
            syncSlider(cmp);
            refreshRuntime(cmp);
            closeModal();
        });
        renderRows();
        editor.Modal.setTitle('');
        editor.Modal.setContent(root);
        editor.Modal.open();
    };

    const renderPresetModal = (cmp) => {
        const root = document.createElement('div');
        root.style.cssText = 'font-family:Inter,Arial,sans-serif;color:#e5e7eb;min-width:min(760px,90vw);';
        root.innerHTML = `<p style="margin:0 0 12px;font-size:13px;color:#9ca3af;">Apply a slider preset for section intent.</p><div data-slot="grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;"></div><div style="display:flex;justify-content:flex-end;margin-top:14px;"><button data-action="close" type="button" style="border:1px solid #4b5563;background:transparent;color:#e5e7eb;padding:8px 14px;border-radius:8px;cursor:pointer;">Close</button></div>`;
        const grid = root.querySelector('[data-slot="grid"]');
        Object.keys(PRESETS).forEach((key) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.style.cssText = 'text-align:left;border:1px solid #374151;border-radius:10px;background:#111827;color:#f9fafb;padding:10px;cursor:pointer;';
            btn.innerHTML = `<strong style="display:block;font-size:13px;margin-bottom:4px;">${key}</strong><span style="font-size:11px;color:#9ca3af;">Desktop slides: ${PRESETS[key].attrs['data-slides-desktop'] || '1'}</span>`;
            btn.addEventListener('click', () => {
                applyPreset(cmp, key, false);
                refreshRuntime(cmp);
                editor.Modal.close();
            });
            grid.appendChild(btn);
        });
        root.querySelector('[data-action="close"]')?.addEventListener('click', () => editor.Modal.close());
        editor.Modal.setTitle('Apply Slider Preset');
        editor.Modal.setContent(root);
        editor.Modal.open();
    };

    const resetStyle = (cmp) => {
        cmp.setStyle({});
        cmp.addAttributes({ ...(cmp.getAttributes() || {}), ...STYLE_DEFAULTS });
        syncSlider(cmp);
        refreshRuntime(cmp);
    };

    const ensureCanvasRuntime = () => {
        const doc = editor.Canvas.getDocument();
        if (!doc) return;
        doc.documentElement.setAttribute('data-lp-slider-editor', 'true');
        if (!doc.getElementById(EDITOR_STYLE_ID)) {
            const style = doc.createElement('style');
            style.id = EDITOR_STYLE_ID;
            style.textContent = `
                .lp-slider.gjs-selected { outline:2px dashed #6366f1!important; }
                .gjs-cv-canvas .lp-slider,
                html[data-lp-slider-editor] .lp-slider,
                html[data-gjs-editor-canvas] .lp-slider {
                    overflow-x: auto !important;
                    overflow-y: visible !important;
                    -webkit-overflow-scrolling: touch;
                }
                .gjs-cv-canvas .lp-slider [data-slider-track],
                .gjs-cv-canvas .lp-slider .lp-slider__track,
                html[data-lp-slider-editor] .lp-slider [data-slider-track],
                html[data-lp-slider-editor] .lp-slider .lp-slider__track,
                html[data-gjs-editor-canvas] .lp-slider [data-slider-track],
                html[data-gjs-editor-canvas] .lp-slider .lp-slider__track {
                    min-height: 40px;
                    display: flex !important;
                    gap: 16px !important;
                    transform: none !important;
                    animation: none !important;
                    transition: none !important;
                    overflow: visible !important;
                    width: max-content !important;
                }
                .gjs-cv-canvas .lp-slider [data-slider-slide],
                .gjs-cv-canvas .lp-slider .lp-slider__slide,
                html[data-lp-slider-editor] .lp-slider [data-slider-slide],
                html[data-lp-slider-editor] .lp-slider .lp-slider__slide,
                html[data-gjs-editor-canvas] .lp-slider [data-slider-slide],
                html[data-gjs-editor-canvas] .lp-slider .lp-slider__slide {
                    flex: 0 0 auto !important;
                    width: 260px !important;
                    opacity: 1 !important;
                    visibility: visible !important;
                    transform: none !important;
                    animation: none !important;
                    pointer-events: auto !important;
                }
                .gjs-cv-canvas .lp-slider .lp-slider__caption,
                html[data-lp-slider-editor] .lp-slider .lp-slider__caption,
                html[data-gjs-editor-canvas] .lp-slider .lp-slider__caption { pointer-events: none; }
            `;
            doc.head.appendChild(style);
        }
        if (!doc.getElementById(RUNTIME_SCRIPT_ID)) {
            const script = doc.createElement('script');
            script.id = RUNTIME_SCRIPT_ID;
            script.src = '/js/lp-slider-runtime.js';
            script.defer = true;
            script.onload = () => doc.defaultView?.LPSliderRuntime?.refresh?.();
            doc.head.appendChild(script);
        }
    };

    const TRAITS = [
        { type: 'button', name: 'lp-edit-slides', text: 'Edit Slides', full: true, command: () => editor.runCommand('lp-slider:edit-slides'), category: 'Content' },
        { type: 'select', name: 'data-preset', label: 'Preset', options: [{ id: 'gallery', name: 'Gallery' }, { id: 'logos', name: 'Logos' }, { id: 'testimonials', name: 'Testimonials' }, { id: 'product-showcase', name: 'Product Showcase' }, { id: 'social-proof', name: 'Social Proof / UGC' }], category: 'Content' },
        { type: 'checkbox', name: 'data-enable-captions', label: 'Captions', valueTrue: 'true', valueFalse: 'false', category: 'Content' },
        { type: 'checkbox', name: 'data-enable-links', label: 'Links', valueTrue: 'true', valueFalse: 'false', category: 'Content' },
        { type: 'checkbox', name: 'data-lightbox', label: 'Lightbox', valueTrue: 'true', valueFalse: 'false', category: 'Content' },
        { type: 'number', name: 'data-slides-desktop', label: 'Slides Desktop', min: 1, max: 8, category: 'Slider Settings' },
        { type: 'number', name: 'data-slides-tablet', label: 'Slides Tablet', min: 1, max: 8, category: 'Slider Settings' },
        { type: 'number', name: 'data-slides-mobile', label: 'Slides Mobile', min: 1, max: 4, category: 'Slider Settings' },
        { type: 'number', name: 'data-space-between', label: 'Spacing', min: 0, max: 80, category: 'Slider Settings' },
        { type: 'checkbox', name: 'data-autoplay', label: 'Autoplay', valueTrue: 'true', valueFalse: 'false', category: 'Slider Settings' },
        { type: 'number', name: 'data-autoplay-delay', label: 'Autoplay Delay (ms)', min: 400, max: 20000, category: 'Slider Settings' },
        { type: 'checkbox', name: 'data-smooth-scroll', label: 'Continuous Smooth Scroll', valueTrue: 'true', valueFalse: 'false', category: 'Slider Settings' },
        { type: 'checkbox', name: 'data-loop', label: 'Loop', valueTrue: 'true', valueFalse: 'false', category: 'Slider Settings' },
        { type: 'number', name: 'data-speed', label: 'Speed (ms)', min: 200, max: 20000, category: 'Slider Settings' },
        { type: 'checkbox', name: 'data-pause-on-hover', label: 'Pause on Hover', valueTrue: 'true', valueFalse: 'false', category: 'Slider Settings' },
        { type: 'checkbox', name: 'data-draggable', label: 'Draggable / Swipe', valueTrue: 'true', valueFalse: 'false', category: 'Slider Settings' },
        { type: 'checkbox', name: 'data-center-mode', label: 'Center Mode', valueTrue: 'true', valueFalse: 'false', category: 'Slider Settings' },
        { type: 'number', name: 'data-initial-slide', label: 'Initial Slide', min: 0, max: 50, category: 'Slider Settings' },
        { type: 'checkbox', name: 'data-arrows', label: 'Arrows', valueTrue: 'true', valueFalse: 'false', category: 'Navigation' },
        { type: 'checkbox', name: 'data-dots', label: 'Dots', valueTrue: 'true', valueFalse: 'false', category: 'Navigation' },
        { type: 'select', name: 'data-thumbnails', label: 'Thumbnails', options: [{ id: 'placeholder', name: 'Placeholder (Future)' }], category: 'Navigation' },
        { type: 'select', name: 'data-nav-position', label: 'Navigation Position', options: [{ id: 'inside', name: 'Inside' }, { id: 'outside', name: 'Outside' }, { id: 'bottom', name: 'Bottom' }], category: 'Navigation' },
        { type: 'select', name: 'data-image-fit', label: 'Image Fit', options: [{ id: 'cover', name: 'Cover' }, { id: 'contain', name: 'Contain' }], category: 'Style' },
        { type: 'select', name: 'data-ratio', label: 'Image Ratio', options: [{ id: 'auto', name: 'Auto' }, { id: '1:1', name: '1:1' }, { id: '4:5', name: '4:5' }, { id: '16:9', name: '16:9' }, { id: 'custom', name: 'Custom Height' }], category: 'Style' },
        { type: 'number', name: 'data-custom-height', label: 'Custom Height (px)', min: 80, max: 1200, category: 'Style' },
        { type: 'text', name: 'data-border-radius', label: 'Border Radius (px)', category: 'Style' },
        { type: 'select', name: 'data-shadow', label: 'Shadow', options: [{ id: 'none', name: 'None' }, { id: 'small', name: 'Small' }, { id: 'medium', name: 'Medium' }, { id: 'large', name: 'Large' }], category: 'Style' },
        { type: 'checkbox', name: 'data-overlay', label: 'Overlay', valueTrue: 'true', valueFalse: 'false', category: 'Style' },
        { type: 'select', name: 'data-caption-align', label: 'Caption Align', options: [{ id: 'left', name: 'Left' }, { id: 'center', name: 'Center' }, { id: 'right', name: 'Right' }], category: 'Style' },
        { type: 'text', name: 'class', label: 'Custom Class', category: 'Advanced' },
        { type: 'text', name: 'id', label: 'Custom ID', category: 'Advanced' },
        { type: 'text', name: 'aria-label', label: 'ARIA Label', category: 'Advanced' },
        { type: 'text', name: 'data-prev-label', label: 'Prev Button Label', category: 'Advanced' },
        { type: 'text', name: 'data-next-label', label: 'Next Button Label', category: 'Advanced' },
        { type: 'text', name: 'data-extra-attrs', label: 'Data Attributes', placeholder: 'data-foo=bar;data-env=prod', category: 'Advanced' },
        { type: 'checkbox', name: 'data-lazy', label: 'Lazy Load', valueTrue: 'true', valueFalse: 'false', category: 'Advanced' },
    ];

    editor.Commands.add('lp-slider:edit-slides', {
        run(ed, sender, optsArg = {}) {
            const cmp = getSliderTarget(ed, optsArg);
            if (!cmp) return;
            renderSlidesModal(cmp);
        },
    });
    editor.Commands.add('lp-slider:apply-preset', {
        run(ed, sender, optsArg = {}) {
            const cmp = getSliderTarget(ed, optsArg);
            if (!cmp) return;
            if (optsArg.preset) {
                applyPreset(cmp, optsArg.preset, false);
                refreshRuntime(cmp);
                return;
            }
            renderPresetModal(cmp);
        },
    });
    editor.Commands.add('lp-slider:reset-style', {
        run(ed, sender, optsArg = {}) {
            const cmp = getSliderTarget(ed, optsArg);
            if (!cmp) return;
            resetStyle(cmp);
        },
    });

    editor.DomComponents.addType('lp-slider', {
        isComponent(el) {
            if (!el || el.nodeType !== 1) return false;
            if (el.classList?.contains('lp-slider')) return { type: 'lp-slider' };
            if (el.getAttribute?.('data-component') === 'lp-slider') return { type: 'lp-slider' };
            if (el.getAttribute?.('data-gjs-type') === 'lp-slider') return { type: 'lp-slider' };
            return false;
        },
        model: {
            defaults: {
                tagName: 'div',
                name: 'LP Slider / Gallery',
                draggable: true,
                droppable: false,
                copyable: true,
                highlightable: true,
                traits: TRAITS,
                toolbar: [
                    { attributes: { class: 'fa fa-images', title: 'Edit Slides' }, command: 'lp-slider:edit-slides' },
                    { attributes: { class: 'fa fa-sliders', title: 'Apply Preset' }, command: 'lp-slider:apply-preset' },
                    { attributes: { class: 'fa fa-clone', title: 'Duplicate Component' }, command: 'tlb-clone' },
                    { attributes: { class: 'fa fa-undo', title: 'Reset Style' }, command: 'lp-slider:reset-style' },
                ],
                attributes: {
                    class: 'lp-slider swiper',
                    'data-gjs-type': 'lp-slider',
                    'data-component': 'lp-slider',
                    'data-slider-version': '1.0.0',
                    'data-preset': 'gallery',
                    ...PRESETS.gallery.attrs,
                    ...STYLE_DEFAULTS,
                    'data-pause-on-hover': 'true',
                    'data-smooth-scroll': 'true',
                    'data-center-mode': 'false',
                    'data-initial-slide': '0',
                    'data-speed': '6500',
                    'data-nav-position': 'outside',
                    'data-thumbnails': 'placeholder',
                    'data-prev-label': 'Previous slide',
                    'data-next-label': 'Next slide',
                    'data-extra-attrs': '',
                    'data-lp-slides': JSON.stringify(PRESETS.gallery.slides),
                    role: 'region',
                    'aria-roledescription': 'carousel',
                    'aria-label': 'Image slider',
                },
                components: [],
            },
            init() {
                ensureRootAttrs(this);
                const attrs = this.getAttributes() || {};
                const fromModel = normalizeSlides(this.get('lpSlides'));
                const fromAttr = normalizeSlides(jparse(attrs['data-lp-slides']));
                const fromMarkup = normalizeSlides(readSlidesFromMarkup(this));
                // Priority: explicit model state -> existing markup -> serialized attr fallback.
                // This prevents default placeholder attrs from overriding real imported slides.
                const parsed = fromModel.length ? fromModel : (fromMarkup.length ? fromMarkup : fromAttr);
                this.set('lpSlides', parsed.length ? parsed : normalizeSlides(PRESETS.gallery.slides), { silent: true });
                syncSlider(this);
                this.on('change:lpSlides', () => {
                    if (this.__lpSliderSyncing) return;
                    syncSlider(this);
                    refreshRuntime(this);
                });
                this.on('change:attributes:data-preset', () => {
                    if (this.__lpSliderSyncing) return;
                    applyPreset(this, this.getAttributes()?.['data-preset'] || 'gallery', false);
                    refreshRuntime(this);
                });
                this.on('change:attributes', () => {
                    if (this.__lpSliderSyncing) return;
                    syncSlider(this);
                    refreshRuntime(this);
                });
            },
        },
        view: {
            onRender() {
                refreshRuntime(this.model);
            },
        },
    });

    editor.BlockManager.add('lp-slider-gallery', {
        label: 'LP Slider / Gallery',
        category: CATEGORY,
        attributes: { class: 'gjs-fonts gjs-f-image' },
        media: '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="4.5" width="17" height="15" rx="2"></rect><path d="M8 14.5l2.8-3 2.8 3.2 2.2-2.5 2.2 2.3"></path><circle cx="8.2" cy="8.5" r="1.2"></circle></svg>',
        content: { type: 'lp-slider' },
    });

    editor.on('load', () => {
        ensureCanvasRuntime();
        setTimeout(() => editor.Canvas.getWindow()?.LPSliderRuntime?.refresh?.(), 120);
    });
    editor.on('component:add', (cmp) => {
        if (cmp.get('type') !== 'lp-slider') return;
        setTimeout(() => refreshRuntime(cmp), 30);
    });
}
