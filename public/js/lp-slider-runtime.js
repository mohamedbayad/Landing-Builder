(function () {
    const SELECTOR = '.lp-slider[data-component="lp-slider"], .lp-slider[data-gjs-type="lp-slider"], [data-component="lp-slider"], [data-gjs-type="lp-slider"]';
    const SWIPER_JS = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
    const SWIPER_CSS = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
    const SPLIDE_JS = 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/js/splide.min.js';
    const SPLIDE_CSS = 'https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/css/splide.min.css';
    const instances = new WeakMap();
    const configSignatures = new WeakMap();
    let enginePromise = null;

    const bool = (v, fb) => {
        if (v == null || v === '') return fb;
        return ['1', 'true', 'yes', 'on'].includes(String(v).toLowerCase());
    };
    const num = (v, fb, min, max) => {
        const n = Number(v);
        if (!Number.isFinite(n)) return fb;
        if (typeof min === 'number' && n < min) return min;
        if (typeof max === 'number' && n > max) return max;
        return n;
    };

    function isEditorContext() {
        const isEditor = window.frameElement !== null;
        return isEditor;
    }

    function ensureBaseStyle() {
        if (document.getElementById('lp-slider-runtime-style')) return;
        const style = document.createElement('style');
        style.id = 'lp-slider-runtime-style';
        style.textContent = `
            .lp-slider { position: relative; width: 100%; }
            .lp-slider__track { width: 100%; }
            .lp-slider__slide { box-sizing: border-box; }
            .lp-slider__media { position: relative; overflow: hidden; background: #111827; }
            .lp-slider__image { width: 100%; height: 100%; display: block; object-fit: cover; }
            .lp-slider__overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,.35), rgba(0,0,0,.08)); pointer-events: none; }
            .lp-slider__caption { margin-top: 10px; font-size: .925rem; color: inherit; line-height: 1.45; }
            .lp-slider__arrow { position: absolute; top: 50%; transform: translateY(-50%); z-index: 20; width: 38px; height: 38px; border-radius: 9999px; border: 1px solid rgba(148,163,184,.45); background: rgba(15,23,42,.65); color: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
            .lp-slider__arrow--prev { left: -18px; }
            .lp-slider__arrow--next { right: -18px; }
            .lp-slider[data-nav-position="inside"] .lp-slider__arrow--prev { left: 12px; }
            .lp-slider[data-nav-position="inside"] .lp-slider__arrow--next { right: 12px; }
            .lp-slider[data-nav-position="bottom"] .lp-slider__arrow { top: auto; bottom: -56px; transform: none; }
            .lp-slider[data-nav-position="bottom"] .lp-slider__arrow--prev { left: 8px; }
            .lp-slider[data-nav-position="bottom"] .lp-slider__arrow--next { left: 52px; right: auto; }
            .lp-slider__dots { margin-top: 14px; display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; }
            .lp-slider__dot { width: 9px; height: 9px; border-radius: 9999px; border: 0; background: rgba(148,163,184,.45); cursor: pointer; }
            .lp-slider__dot.is-active { background: rgba(79,70,229,1); }
            .lp-slider__track.lp-slider--native-track { display: grid; grid-auto-flow: column; grid-auto-columns: calc((100% - (var(--lp-gap,16px) * (var(--lp-spv,1) - 1))) / var(--lp-spv,1)); gap: var(--lp-gap,16px); overflow-x: auto; scroll-snap-type: x mandatory; scroll-behavior: smooth; padding-bottom: 2px; }
            .lp-slider__track.lp-slider--native-track > .lp-slider__slide { scroll-snap-align: start; }
            .lp-slider--continuous .lp-slider__track.swiper-wrapper { transition-timing-function: linear !important; }
            .lp-slider--continuous .swiper-wrapper { will-change: transform; }
            .lp-slider--marquee { overflow: hidden !important; }
            .lp-slider--marquee .lp-slider__track {
                display: flex !important;
                width: max-content !important;
                gap: var(--lp-marquee-gap, 24px) !important;
                animation: lpSliderMarquee var(--lp-marquee-duration, 22s) linear infinite !important;
                will-change: transform;
                transform: translate3d(0, 0, 0);
            }
            .lp-slider--marquee .lp-slider__slide {
                flex: 0 0 var(--lp-marquee-slide-width, 280px) !important;
                width: var(--lp-marquee-slide-width, 280px) !important;
            }
            .lp-slider--marquee[data-pause-on-hover="true"]:hover .lp-slider__track { animation-play-state: paused !important; }
            @keyframes lpSliderMarquee {
                from { transform: translate3d(0, 0, 0); }
                to { transform: translate3d(calc(-1 * var(--lp-marquee-shift, 50%)), 0, 0); }
            }
            .lp-slider--editor-static { overflow-x: auto !important; overflow-y: visible !important; -webkit-overflow-scrolling: touch; }
            .lp-slider--editor-static .lp-slider__track {
                display: flex !important;
                gap: var(--lp-editor-gap, 16px) !important;
                overflow: visible !important;
                transform: none !important;
                transition: none !important;
                animation: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: max-content !important;
            }
            .lp-slider--editor-static .lp-slider__slide {
                display: block !important;
                width: var(--lp-editor-slide-width, 260px) !important;
                min-width: 0 !important;
                max-width: none !important;
                flex: 0 0 auto !important;
                transform: none !important;
                opacity: 1 !important;
                visibility: visible !important;
                animation: none !important;
                pointer-events: auto !important;
            }
            .lp-slider--editor-static .lp-slider__media,
            .lp-slider--editor-static .lp-slider__image,
            .lp-slider--editor-static .lp-slider__caption {
                transform: none !important;
                opacity: 1 !important;
                visibility: visible !important;
                animation: none !important;
                pointer-events: auto !important;
            }
            .lp-slider--editor-static .lp-slider__arrow,
            .lp-slider--editor-static .lp-slider__dots {
                display: none !important;
            }
        `;
        document.head.appendChild(style);
    }

    function ensureStylesheet(id, href) {
        if (document.getElementById(id)) return;
        const link = document.createElement('link');
        link.id = id;
        link.rel = 'stylesheet';
        link.href = href;
        document.head.appendChild(link);
    }

    function loadScript(id, src) {
        return new Promise((resolve, reject) => {
            const existing = document.getElementById(id);
            if (existing && existing.dataset.loaded === 'true') return resolve();
            if (existing) {
                existing.addEventListener('load', () => resolve(), { once: true });
                existing.addEventListener('error', () => reject(new Error('script-load-failed')), { once: true });
                return;
            }
            const script = document.createElement('script');
            script.id = id;
            script.src = src;
            script.async = true;
            script.onload = () => {
                script.dataset.loaded = 'true';
                resolve();
            };
            script.onerror = () => reject(new Error('script-load-failed'));
            document.head.appendChild(script);
        });
    }

    function ensureEngine() {
        if (window.Swiper) return Promise.resolve('swiper');
        if (enginePromise) return enginePromise;
        ensureStylesheet('lp-slider-swiper-css', SWIPER_CSS);
        enginePromise = loadScript('lp-slider-swiper-js', SWIPER_JS)
            .then(() => (window.Swiper ? 'swiper' : Promise.reject(new Error('swiper-missing'))))
            .catch(() => {
                ensureStylesheet('lp-slider-splide-css', SPLIDE_CSS);
                return loadScript('lp-slider-splide-js', SPLIDE_JS)
                    .then(() => (window.Splide ? 'splide' : 'native'))
                    .catch(() => 'native');
            });
        return enginePromise;
    }

    function destroySlider(el) {
        const current = instances.get(el);
        if (!current) return;
        try {
            if (current.type === 'swiper' && current.instance?.destroy) current.instance.destroy(true, true);
            if (current.type === 'splide' && current.instance?.destroy) current.instance.destroy(true);
        } catch (_e) {
            // no-op
        }
        if (typeof current.cleanup === 'function') {
            try {
                current.cleanup();
            } catch (_e) {
                // no-op
            }
        }
        instances.delete(el);
        configSignatures.delete(el);
    }

    function parseSliderAttributes(el) {
        const speed = num(el.getAttribute('data-speed'), 4000, 200, 20000);
        const loop = bool(el.getAttribute('data-loop'), true);
        const slidesDesktop = num(el.getAttribute('data-slides-desktop'), 3, 1, 8);
        const slidesTablet = num(el.getAttribute('data-slides-tablet'), 2, 1, 8);
        const slidesMobile = num(el.getAttribute('data-slides-mobile'), 1, 1, 4);
        const gapRaw = el.getAttribute('data-gap') ?? el.getAttribute('data-space-between');
        const gap = num(gapRaw, 24, 0, 120);
        const autoplay = bool(el.getAttribute('data-autoplay'), false);
        const initialSlide = num(el.getAttribute('data-initial-slide'), 0, 0, 100);
        const centeredSlides = bool(el.getAttribute('data-center-mode'), false);
        const arrows = bool(el.getAttribute('data-arrows'), true);
        const dots = bool(el.getAttribute('data-dots'), true);
        const smoothScroll = bool(el.getAttribute('data-smooth-scroll'), false);
        const pauseOnHover = bool(el.getAttribute('data-pause-on-hover'), true);
        return {
            speed,
            loop,
            slidesDesktop,
            slidesTablet,
            slidesMobile,
            gap,
            autoplay,
            initialSlide,
            centeredSlides,
            arrows,
            dots,
            smoothScroll,
            pauseOnHover,
        };
    }

    function getSlidesPerViewByViewport(opts) {
        if (window.matchMedia('(min-width:1024px)').matches) return opts.slidesDesktop;
        if (window.matchMedia('(min-width:768px)').matches) return opts.slidesTablet;
        return opts.slidesMobile;
    }

    function getConfigSignature(el, mode, engine = '') {
        const opts = parseSliderAttributes(el);
        const payload = {
            mode,
            engine,
            ...opts,
            slideCount: Number(el.getAttribute('data-slide-count') || el.querySelectorAll('[data-slider-slide], .lp-slider__slide').length || 0),
        };
        return JSON.stringify(payload);
    }

    function getSlideWidthForViewport(el, opts) {
        const perView = Math.max(1, getSlidesPerViewByViewport(opts));
        const containerWidth = Math.max(1, Math.floor(el.getBoundingClientRect().width || el.clientWidth || 1));
        const width = (containerWidth - (opts.gap * Math.max(0, perView - 1))) / perView;
        return Math.max(120, Math.floor(width));
    }

    function measureSlidesWidth(slides, gap) {
        if (!slides.length) return 0;
        const widths = slides.reduce((acc, slide) => acc + (slide.getBoundingClientRect().width || slide.offsetWidth || 0), 0);
        return widths + (gap * Math.max(0, slides.length - 1));
    }

    function initMarquee(el, track) {
        clearTransientState(el, track);
        el.setAttribute('data-lp-mode', 'preview');
        el.classList.add('lp-slider--marquee');

        const prev = el.querySelector('.lp-slider__arrow--prev');
        const next = el.querySelector('.lp-slider__arrow--next');
        const dots = el.querySelector('.lp-slider__dots');
        if (prev) prev.style.display = 'none';
        if (next) next.style.display = 'none';
        if (dots) dots.style.display = 'none';

        const rebuild = () => {
            const opts = parseSliderAttributes(el);
            const gap = opts.gap;

            track.querySelectorAll('[data-lp-clone="true"]').forEach((node) => node.remove());
            const originalSlides = Array.from(track.children).filter((child) => child.nodeType === 1 && child.matches?.('[data-slider-slide], .lp-slider__slide'));
            if (!originalSlides.length) return;

            const slideWidth = getSlideWidthForViewport(el, opts);
            el.style.setProperty('--lp-marquee-gap', `${gap}px`);
            el.style.setProperty('--lp-marquee-slide-width', `${slideWidth}px`);
            el.style.setProperty('--lp-marquee-duration', `${Math.max(4, (opts.speed || 7000) / 1000)}s`);

            originalSlides.forEach((slide) => {
                slide.style.flex = `0 0 ${slideWidth}px`;
                slide.style.width = `${slideWidth}px`;
            });

            // Ensure one half is at least as wide as the visible viewport to avoid blank regions.
            const viewportWidth = Math.max(1, Math.floor(el.getBoundingClientRect().width || el.clientWidth || 1));
            let firstHalf = Array.from(originalSlides);
            let guard = 0;
            while (measureSlidesWidth(firstHalf, gap) < viewportWidth && guard < 64) {
                const source = originalSlides[guard % originalSlides.length];
                const seed = source.cloneNode(true);
                seed.setAttribute('data-lp-clone', 'true');
                seed.setAttribute('data-lp-clone-kind', 'seed');
                seed.classList.add('lp-slider__slide');
                seed.style.flex = `0 0 ${slideWidth}px`;
                seed.style.width = `${slideWidth}px`;
                track.appendChild(seed);
                firstHalf.push(seed);
                guard += 1;
            }

            const loopClones = firstHalf.map((slide) => {
                const loop = slide.cloneNode(true);
                loop.setAttribute('data-lp-clone', 'true');
                loop.setAttribute('data-lp-clone-kind', 'loop');
                loop.classList.add('lp-slider__slide');
                loop.style.flex = `0 0 ${slideWidth}px`;
                loop.style.width = `${slideWidth}px`;
                track.appendChild(loop);
                return loop;
            });

            const firstHalfStart = firstHalf[0];
            const secondHalfStart = loopClones[0];
            const shift = Math.max(1, (secondHalfStart?.offsetLeft || 0) - (firstHalfStart?.offsetLeft || 0));
            el.style.setProperty('--lp-marquee-shift', `${shift}px`);
        };

        rebuild();

        let resizeTimer = null;
        const onResize = () => {
            if (resizeTimer) window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(rebuild, 120);
        };
        window.addEventListener('resize', onResize);

        // Recalculate once images settle to keep shift distance exact.
        const onImageLoad = (event) => {
            const target = event.target;
            if (!(target instanceof HTMLImageElement)) return;
            if (!track.contains(target)) return;
            onResize();
        };
        track.addEventListener('load', onImageLoad, true);

        return () => {
            if (resizeTimer) window.clearTimeout(resizeTimer);
            window.removeEventListener('resize', onResize);
            track.removeEventListener('load', onImageLoad, true);
        };
    }

    function ensureStructure(el) {
        const track = el.querySelector('[data-slider-track], .lp-slider__track');
        if (!track) return null;
        if (track.getAttribute('data-slider-track') !== 'true') {
            track.setAttribute('data-slider-track', 'true');
        }

        // Unwrap Splide list if present so the canonical structure remains stable.
        const splideList = track.querySelector(':scope > .splide__list');
        if (splideList) {
            Array.from(splideList.children).forEach((child) => track.appendChild(child));
            splideList.remove();
        }

        track.classList.add('lp-slider__track');
        track.classList.remove('lp-slider--native-track');
        track.classList.add('swiper-wrapper');
        let slides = Array.from(track.children).filter((child) => child.nodeType === 1 && (child.matches?.('[data-slider-slide], .lp-slider__slide') || child.classList.contains('swiper-slide')));
        if (!slides.length) {
            slides = Array.from(track.querySelectorAll('[data-slider-slide], .lp-slider__slide')).filter((slide) => slide !== track);
            slides.forEach((slide) => {
                if (slide.parentElement !== track) {
                    track.appendChild(slide);
                }
            });
        }
        slides.forEach((slide) => {
            slide.classList.add('lp-slider__slide', 'swiper-slide');
            if (slide.getAttribute('data-slider-slide') !== 'true') {
                slide.setAttribute('data-slider-slide', 'true');
            }
            const image = slide.querySelector('[data-slider-image], .lp-slider__image, img');
            if (image) image.classList.add('lp-slider__image');
            if (image && image.getAttribute('data-slider-image') !== 'true') {
                image.setAttribute('data-slider-image', 'true');
            }
        });
        if (!el.querySelector('.lp-slider__arrow--prev')) {
            const prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'lp-slider__arrow lp-slider__arrow--prev';
            prev.innerHTML = '&#8249;';
            el.appendChild(prev);
        }
        if (!el.querySelector('.lp-slider__arrow--next')) {
            const next = document.createElement('button');
            next.type = 'button';
            next.className = 'lp-slider__arrow lp-slider__arrow--next';
            next.innerHTML = '&#8250;';
            el.appendChild(next);
        }
        if (!el.querySelector('.lp-slider__dots')) {
            const dots = document.createElement('div');
            dots.className = 'lp-slider__dots';
            el.appendChild(dots);
        }
        return track;
    }

    function clearTransientState(el, track) {
        el.classList.remove('lp-slider--editor-static', 'splide', 'swiper-initialized', 'swiper-horizontal', 'swiper-vertical', 'swiper-backface-hidden', 'lp-slider--continuous');
        el.classList.remove('lp-slider--marquee');
        el.classList.remove('animate-scroll-left-fast', 'animate-scroll-right-fast');
        el.removeAttribute('data-lp-mode');
        el.style.removeProperty('--lp-editor-columns');
        el.style.removeProperty('--lp-editor-gap');
        el.style.removeProperty('--lp-marquee-duration');
        el.style.removeProperty('--lp-marquee-gap');
        el.style.removeProperty('--lp-marquee-slide-width');
        el.style.removeProperty('--lp-marquee-shift');

        if (track) {
            track.classList.remove('splide__track', 'lp-slider--native-track');
            track.classList.remove('animate-scroll-left-fast', 'animate-scroll-right-fast');
            track.querySelectorAll('[data-lp-clone="true"]').forEach((node) => node.remove());
            track.querySelectorAll('.animate-scroll-left-fast, .animate-scroll-right-fast').forEach((node) => {
                node.classList.remove('animate-scroll-left-fast', 'animate-scroll-right-fast');
            });
            track.style.removeProperty('display');
            track.style.removeProperty('grid-template-columns');
            track.style.removeProperty('gap');
            track.style.removeProperty('overflow');
            track.style.removeProperty('transform');
            track.style.removeProperty('transition');
            track.style.removeProperty('animation');
            track.style.removeProperty('width');
            track.style.removeProperty('margin');
            track.style.removeProperty('padding');
            track.style.removeProperty('animation');
            track.querySelectorAll('[data-slider-slide], .lp-slider__slide').forEach((slide) => {
                slide.style.removeProperty('flex');
                slide.style.removeProperty('width');
                slide.style.removeProperty('min-width');
                slide.style.removeProperty('max-width');
                slide.style.removeProperty('margin-right');
                slide.style.removeProperty('transform');
                slide.style.removeProperty('transition');
                slide.style.removeProperty('animation');
            });
        }
    }

    function applyPresentation(el) {
        const fit = el.getAttribute('data-image-fit') || 'cover';
        const ratio = el.getAttribute('data-ratio') || '4:5';
        const customHeight = num(el.getAttribute('data-custom-height'), 360, 60, 1600);
        const radius = num(el.getAttribute('data-border-radius'), 16, 0, 200);
        const shadow = el.getAttribute('data-shadow') || 'medium';
        const align = el.getAttribute('data-caption-align') || 'left';
        const lazy = bool(el.getAttribute('data-lazy'), true);
        const overlay = bool(el.getAttribute('data-overlay'), false);

        const shadowMap = {
            none: 'none',
            small: '0 6px 16px rgba(15, 23, 42, 0.14)',
            medium: '0 12px 28px rgba(15, 23, 42, 0.20)',
            large: '0 22px 38px rgba(15, 23, 42, 0.28)',
        };
        const ratioMap = { '1:1': '1 / 1', '4:5': '4 / 5', '16:9': '16 / 9' };

        el.querySelectorAll('.lp-slider__media').forEach((media) => {
            media.style.borderRadius = `${radius}px`;
            media.style.boxShadow = shadowMap[shadow] || shadowMap.medium;
            if (ratio === 'custom') {
                media.style.aspectRatio = 'auto';
                media.style.height = `${customHeight}px`;
            } else if (ratio === 'auto') {
                media.style.aspectRatio = 'auto';
                media.style.height = '';
            } else {
                media.style.aspectRatio = ratioMap[ratio] || ratioMap['4:5'];
                media.style.height = '';
            }
            if (overlay && !media.querySelector('.lp-slider__overlay')) {
                const layer = document.createElement('div');
                layer.className = 'lp-slider__overlay';
                layer.setAttribute('aria-hidden', 'true');
                media.appendChild(layer);
            }
            if (!overlay) media.querySelectorAll('.lp-slider__overlay').forEach((n) => n.remove());
        });
        el.querySelectorAll('.lp-slider__image').forEach((img) => {
            img.style.objectFit = fit;
            img.setAttribute('loading', lazy ? 'lazy' : 'eager');
        });
        el.querySelectorAll('.lp-slider__caption').forEach((cap) => {
            cap.style.textAlign = align;
        });
    }

    function renderDots(el, total, active, onClick) {
        const dots = el.querySelector('.lp-slider__dots');
        if (!dots) return;
        dots.innerHTML = '';
        for (let i = 0; i < total; i += 1) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = `lp-slider__dot${i === active ? ' is-active' : ''}`;
            dot.addEventListener('click', () => onClick(i));
            dots.appendChild(dot);
        }
    }

    function setupLightbox(el) {
        if (!bool(el.getAttribute('data-lightbox'), false)) return () => {};
        if (isEditorContext()) return () => {};
        let overlay = document.getElementById('lp-slider-lightbox');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'lp-slider-lightbox';
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(2,6,23,.88);display:none;align-items:center;justify-content:center;z-index:99999;padding:24px;';
            overlay.innerHTML = '<button type="button" aria-label="Close lightbox" style="position:absolute;top:16px;right:16px;border:1px solid rgba(255,255,255,.4);background:transparent;color:#fff;border-radius:9999px;width:36px;height:36px;cursor:pointer;">×</button><img alt="" style="max-width:min(1100px,92vw);max-height:88vh;border-radius:12px;box-shadow:0 20px 50px rgba(0,0,0,.45);" />';
            document.body.appendChild(overlay);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay || e.target.tagName === 'BUTTON') overlay.style.display = 'none';
            });
        }
        const clickHandler = (e) => {
            const image = e.target.closest('.lp-slider__image');
            if (!image || !el.contains(image)) return;
            const link = image.closest('a[href]');
            if (link && link.getAttribute('href') && link.getAttribute('href') !== '#') return;
            e.preventDefault();
            overlay.querySelector('img').src = image.getAttribute('src') || '';
            overlay.querySelector('img').alt = image.getAttribute('alt') || '';
            overlay.style.display = 'flex';
        };
        el.addEventListener('click', clickHandler);
        return () => el.removeEventListener('click', clickHandler);
    }

    function initEditorStatic(el, track) {
        const opts = parseSliderAttributes(el);
        const gap = opts.gap;

        clearTransientState(el, track);
        el.classList.add('lp-slider--editor-static');
        el.setAttribute('data-lp-mode', 'editor');

        // Remove duplicates/clones from loop engines to keep a clean editable DOM.
        track.querySelectorAll('.swiper-slide-duplicate').forEach((dup) => dup.remove());
        track.querySelectorAll('[data-slider-slide], .lp-slider__slide, .swiper-slide').forEach((slide) => {
            slide.style.removeProperty('transform');
            slide.style.removeProperty('transition');
            slide.style.removeProperty('animation');
            slide.style.removeProperty('width');
            slide.style.removeProperty('margin-right');
            slide.style.removeProperty('opacity');
            slide.style.removeProperty('visibility');
        });
        track.querySelectorAll('[data-slider-image], .lp-slider__image, img').forEach((img) => {
            img.style.removeProperty('transform');
            img.style.removeProperty('transition');
            img.style.removeProperty('animation');
            img.style.removeProperty('opacity');
            img.style.removeProperty('visibility');
        });

        const prev = el.querySelector('.lp-slider__arrow--prev');
        const next = el.querySelector('.lp-slider__arrow--next');
        const dots = el.querySelector('.lp-slider__dots');
        if (prev) prev.style.display = 'none';
        if (next) next.style.display = 'none';
        if (dots) dots.style.display = 'none';

        el.style.setProperty('--lp-editor-gap', `${gap || 16}px`);
        const applyEditorSizing = () => {
            const perView = Math.max(1, getSlidesPerViewByViewport(opts));
            const containerWidth = Math.max(320, Math.floor(el.getBoundingClientRect().width || el.clientWidth || 960));
            const slideWidth = Math.max(120, Math.floor((containerWidth - (Math.max(0, perView - 1) * gap)) / perView));
            el.style.setProperty('--lp-editor-slide-width', `${slideWidth}px`);
        };
        applyEditorSizing();
        window.addEventListener('resize', applyEditorSizing);

        return () => {
            window.removeEventListener('resize', applyEditorSizing);
        };
    }

    function initNative(el, track) {
        clearTransientState(el, track);
        el.setAttribute('data-lp-mode', 'preview');
        const arrows = bool(el.getAttribute('data-arrows'), true);
        const dots = bool(el.getAttribute('data-dots'), true);
        const desktop = num(el.getAttribute('data-slides-desktop'), 3, 1, 8);
        const tablet = num(el.getAttribute('data-slides-tablet'), 2, 1, 8);
        const mobile = num(el.getAttribute('data-slides-mobile'), 1, 1, 4);
        const gap = num(el.getAttribute('data-space-between'), 16, 0, 120);
        const byWidth = () => (window.matchMedia('(min-width:1024px)').matches ? desktop : (window.matchMedia('(min-width:768px)').matches ? tablet : mobile));
        track.classList.add('lp-slider--native-track');
        track.style.setProperty('--lp-gap', `${gap}px`);
        track.style.setProperty('--lp-spv', String(byWidth()));
        const slideCount = track.querySelectorAll('.lp-slider__slide').length;
        let index = num(el.getAttribute('data-initial-slide'), 0, 0, Math.max(slideCount - 1, 0));
        const scrollTo = () => {
            const first = track.querySelector('.lp-slider__slide');
            if (!first) return;
            const width = first.getBoundingClientRect().width + gap;
            track.scrollTo({ left: width * index, behavior: 'smooth' });
            if (dots) renderDots(el, slideCount, index, (i) => { index = i; scrollTo(); });
        };
        const prev = el.querySelector('.lp-slider__arrow--prev');
        const next = el.querySelector('.lp-slider__arrow--next');
        prev.style.display = arrows ? '' : 'none';
        next.style.display = arrows ? '' : 'none';
        if (!dots && el.querySelector('.lp-slider__dots')) el.querySelector('.lp-slider__dots').style.display = 'none';
        const prevHandler = () => { index = Math.max(index - 1, 0); scrollTo(); };
        const nextHandler = () => { index = Math.min(index + 1, slideCount - 1); scrollTo(); };
        prev.addEventListener('click', prevHandler);
        next.addEventListener('click', nextHandler);
        window.addEventListener('resize', scrollTo);
        scrollTo();
        return () => {
            prev.removeEventListener('click', prevHandler);
            next.removeEventListener('click', nextHandler);
            window.removeEventListener('resize', scrollTo);
        };
    }

    function initSwiper(el) {
        clearTransientState(el, el.querySelector('[data-slider-track], .lp-slider__track'));
        el.setAttribute('data-lp-mode', 'preview');

        const opts = parseSliderAttributes(el);
        const prev = el.querySelector('.lp-slider__arrow--prev');
        const next = el.querySelector('.lp-slider__arrow--next');
        const dots = el.querySelector('.lp-slider__dots');
        if (prev) prev.style.display = opts.arrows ? '' : 'none';
        if (next) next.style.display = opts.arrows ? '' : 'none';
        if (dots) dots.style.display = opts.dots ? '' : 'none';
        if (opts.autoplay) el.classList.add('lp-slider--continuous');
        else el.classList.remove('lp-slider--continuous');

        const instance = new window.Swiper(el, {
            slidesPerView: opts.slidesMobile,
            spaceBetween: opts.gap,
            loop: opts.loop,
            speed: opts.speed,
            centeredSlides: opts.centeredSlides,
            allowTouchMove: true,
            initialSlide: opts.initialSlide,
            watchOverflow: false,
            grabCursor: true,
            navigation: opts.arrows ? { prevEl: prev, nextEl: next } : false,
            pagination: opts.dots ? { el: dots, clickable: true } : false,
            autoplay: opts.autoplay
                ? {
                    delay: 1,
                    disableOnInteraction: false,
                }
                : false,
            loopAdditionalSlides: Math.max(6, el.querySelectorAll('.lp-slider__slide').length * 2),
            breakpoints: {
                768: { slidesPerView: opts.slidesTablet },
                1024: { slidesPerView: opts.slidesDesktop },
            },
        });

        return {
            type: 'swiper',
            instance,
            cleanup: () => {},
        };
    }

    function initSplide(el, track) {
        clearTransientState(el, track);
        el.setAttribute('data-lp-mode', 'preview');
        const arrowsEnabled = bool(el.getAttribute('data-arrows'), true);
        const dotsEnabled = bool(el.getAttribute('data-dots'), true);
        el.classList.add('splide');
        track.classList.add('splide__track');
        let list = track.querySelector(':scope > .splide__list');
        if (!list) {
            list = document.createElement('div');
            list.className = 'splide__list';
            Array.from(track.children).forEach((slide) => {
                if (slide.classList.contains('lp-slider__slide')) {
                    slide.classList.add('splide__slide');
                    list.appendChild(slide);
                }
            });
            track.appendChild(list);
        }
        const splide = new window.Splide(el, {
            type: bool(el.getAttribute('data-loop'), true) ? 'loop' : 'slide',
            perPage: num(el.getAttribute('data-slides-desktop'), 3, 1, 8),
            gap: `${num(el.getAttribute('data-space-between'), 16, 0, 120)}px`,
            speed: num(el.getAttribute('data-speed'), 500, 200, 20000),
            start: num(el.getAttribute('data-initial-slide'), 0, 0, 100),
            drag: bool(el.getAttribute('data-draggable'), true),
            autoplay: bool(el.getAttribute('data-autoplay'), false),
            interval: num(el.getAttribute('data-autoplay-delay'), 3200, 200, 60000),
            pauseOnHover: bool(el.getAttribute('data-pause-on-hover'), true),
            arrows: false,
            pagination: false,
            breakpoints: {
                1024: { perPage: num(el.getAttribute('data-slides-tablet'), 2, 1, 8) },
                767: { perPage: num(el.getAttribute('data-slides-mobile'), 1, 1, 4) },
            },
        });
        splide.mount();
        const prev = el.querySelector('.lp-slider__arrow--prev');
        const next = el.querySelector('.lp-slider__arrow--next');
        prev.style.display = arrowsEnabled ? '' : 'none';
        next.style.display = arrowsEnabled ? '' : 'none';
        const prevHandler = () => splide.go('<');
        const nextHandler = () => splide.go('>');
        prev.addEventListener('click', prevHandler);
        next.addEventListener('click', nextHandler);
        if (dotsEnabled) {
            if (el.querySelector('.lp-slider__dots')) el.querySelector('.lp-slider__dots').style.display = '';
            const total = list.children.length || 0;
            const update = () => renderDots(el, total, splide.index || 0, (i) => splide.go(i));
            splide.on('moved', update);
            update();
        } else if (el.querySelector('.lp-slider__dots')) {
            el.querySelector('.lp-slider__dots').style.display = 'none';
        }
        return { type: 'splide', instance: splide, cleanup: () => { prev.removeEventListener('click', prevHandler); next.removeEventListener('click', nextHandler); } };
    }

    function initOne(el, engine, editorMode = false) {
        const track = ensureStructure(el);
        if (!track) return;
        destroySlider(el);
        applyPresentation(el);
        if (editorMode) {
            const cleanup = initEditorStatic(el, track);
            instances.set(el, { type: 'editor', instance: null, cleanup });
            configSignatures.set(el, getConfigSignature(el, 'editor', 'editor'));
            return;
        }
        const cleanupLightbox = setupLightbox(el);
        let record = null;
        if (engine === 'marquee') record = { type: 'marquee', instance: null, cleanup: initMarquee(el, track) };
        else if (engine === 'swiper' && window.Swiper) record = initSwiper(el);
        else if (engine === 'splide' && window.Splide) record = initSplide(el, track);
        else record = { type: 'native', instance: null, cleanup: initNative(el, track) };
        const oldCleanup = record.cleanup;
        record.cleanup = () => {
            if (typeof oldCleanup === 'function') oldCleanup();
            cleanupLightbox();
        };
        instances.set(el, record);
        configSignatures.set(el, getConfigSignature(el, 'preview', record.type));
    }

    function collectTargets(target) {
        if (!target) return Array.from(document.querySelectorAll(SELECTOR));
        if (Array.isArray(target)) return target.filter((node) => node && node.matches && node.matches(SELECTOR));
        if (target.matches && target.matches(SELECTOR)) return [target];
        return Array.from(target.querySelectorAll ? target.querySelectorAll(SELECTOR) : []);
    }

    function refresh(target, options = {}) {
        const elements = collectTargets(target);
        if (!elements.length) return;
        ensureBaseStyle();
        const isEditor = isEditorContext();
        const force = Boolean(options.force);
        if (isEditor) {
            elements.forEach((el) => {
                const desiredType = 'editor';
                const nextSig = getConfigSignature(el, 'editor', desiredType);
                const current = instances.get(el);
                const prevSig = configSignatures.get(el);
                if (!force && current?.type === desiredType && prevSig === nextSig) return;
                initOne(el, 'native', true);
            });
            return;
        }
        const resolveDesiredType = (el, fallback) => {
            const opts = parseSliderAttributes(el);
            if (opts.smoothScroll && opts.autoplay) return 'marquee';
            return fallback;
        };

        // If all sliders are marquee mode, skip external engine loading entirely.
        const allMarquee = elements.every((el) => resolveDesiredType(el, 'native') === 'marquee');
        if (allMarquee) {
            elements.forEach((el) => {
                const desiredType = 'marquee';
                const nextSig = getConfigSignature(el, 'preview', desiredType);
                const current = instances.get(el);
                const prevSig = configSignatures.get(el);
                if (!force && current?.type === desiredType && prevSig === nextSig) return;
                initOne(el, desiredType, false);
            });
            return;
        }

        ensureEngine().then((engine) => {
            elements.forEach((el) => {
                const desiredType = resolveDesiredType(el, engine);
                const nextSig = getConfigSignature(el, 'preview', desiredType);
                const current = instances.get(el);
                const prevSig = configSignatures.get(el);
                if (!force && current?.type === desiredType && prevSig === nextSig) return;
                initOne(el, desiredType, false);
            });
        }).catch(() => {
            elements.forEach((el) => {
                const desiredType = resolveDesiredType(el, 'native');
                const nextSig = getConfigSignature(el, 'preview', desiredType);
                const current = instances.get(el);
                const prevSig = configSignatures.get(el);
                if (!force && current?.type === desiredType && prevSig === nextSig) return;
                initOne(el, desiredType, false);
            });
        });
    }

    function observe() {
        if (window.__lpSliderObserver) return;
        const ATTRS = ['data-speed', 'data-loop', 'data-slides-desktop', 'data-slides-tablet', 'data-slides-mobile', 'data-space-between', 'data-gap', 'data-autoplay', 'data-center-mode', 'data-initial-slide', 'data-arrows', 'data-dots', 'data-slide-count', 'data-preset', 'data-smooth-scroll'];
        const observer = new MutationObserver((mutations) => {
            const nodes = [];
            mutations.forEach((m) => {
                if (m.type === 'attributes' && m.target instanceof HTMLElement) {
                    // Only react to trait/runtime attributes on the slider root itself.
                    if (m.target.matches?.(SELECTOR)) nodes.push(m.target);
                }
                m.addedNodes.forEach((node) => {
                    if (!(node instanceof HTMLElement)) return;
                    if (node.matches?.(SELECTOR)) nodes.push(node);
                    node.querySelectorAll?.(SELECTOR).forEach((inner) => nodes.push(inner));
                });
                m.removedNodes.forEach((node) => {
                    if (!(node instanceof HTMLElement)) return;
                    if (node.matches?.(SELECTOR)) destroySlider(node);
                    node.querySelectorAll?.(SELECTOR).forEach((inner) => destroySlider(inner));
                });
            });
            if (nodes.length) refresh(Array.from(new Set(nodes)));
        });
        observer.observe(document.documentElement || document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ATTRS,
        });
        window.__lpSliderObserver = observer;
    }

    window.LPSliderRuntime = {
        refresh,
        init: refresh,
        version: '1.0.0',
    };

    document.addEventListener('lp-slider:refresh', (event) => {
        const target = event.target?.closest?.('.lp-slider') || event.target;
        if (target) refresh([target], { force: true });
    });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            refresh();
            observe();
        });
    } else {
        refresh();
        observe();
    }
    window.addEventListener('load', () => refresh());
})();
