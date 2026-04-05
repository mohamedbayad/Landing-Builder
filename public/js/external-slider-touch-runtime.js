(function () {
    const BUILDER_SELECTOR = '[data-slider-source="builder"], [data-component="builder-slider"], [data-gjs-type="builder-slider"], [data-component="lp-slider"][data-slider-version][data-lp-slides], [data-gjs-type="lp-slider"][data-slider-version][data-lp-slides], .lp-slider[data-slider-version][data-lp-slides]';
    const EXTERNAL_SELECTOR = '[data-slider-source="external"], .ext-slider, [data-gjs-external-slider="true"]';
    const CANDIDATE_SELECTOR = '.ext-slider, [data-slider-source], [data-gjs-external-slider="true"], .swiper, .swiper-container, .slick-slider, .splide, .keen-slider, .glide, [data-slider], [data-carousel], [class*="slider"], [class*="carousel"]';
    const TRACK_SELECTOR = '.swiper-wrapper, .slick-track, .splide__list, .splide__track, .glide__slides, .keen-slider, [data-slider-track], [data-carousel-track], [data-gjs-external-slider-track="true"]';
    const SLIDE_SELECTOR = '.swiper-slide, .slick-slide, .splide__slide, .glide__slide, .keen-slider__slide, [data-slide], [data-slider-slide], [data-gjs-external-slider-slide="true"]';
    const DIRECT_SLIDE_SELECTOR = ':scope > .swiper-slide, :scope > .slick-slide, :scope > .splide__slide, :scope > .glide__slide, :scope > .keen-slider__slide, :scope > [data-slide], :scope > [data-slider-slide], :scope > [data-gjs-external-slider-slide="true"]';
    const STYLE_ID = 'external-slider-touch-runtime-style';
    const TRACK_ATTR = 'data-ext-slider-track';
    const SLIDE_ATTR = 'data-ext-slider-slide';
    const LOCK_ATTR = 'data-ext-gesture-lock';
    const MOBILE_QUERY = '(max-width: 1024px)';
    const touchState = new Map();
    let wheelReleaseTimer = null;

    const isBuilderRoot = (el) => {
        if (!el || el.nodeType !== 1) return false;
        return !!el.closest?.(BUILDER_SELECTOR);
    };

    const hasSliderSignals = (root) => {
        if (!root || root.nodeType !== 1) return false;
        if (root.matches(EXTERNAL_SELECTOR)) return true;
        if (root.matches('.swiper, .swiper-container, .slick-slider, .splide, .keen-slider, .glide, [data-slider], [data-carousel]')) return true;
        if (root.querySelector('.swiper-wrapper > .swiper-slide, .slick-track > .slick-slide, .splide__list > .splide__slide, .splide__track > .splide__list > .splide__slide')) return true;

        const directSlides = Array.from(root.children || []).filter((child) => {
            if (!child || child.nodeType !== 1) return false;
            return child.matches?.(SLIDE_SELECTOR) || /slide/i.test(child.className || '');
        });
        return directSlides.length > 1;
    };

    const findNearestSliderRoot = (el) => {
        if (!el || el.nodeType !== 1 || isBuilderRoot(el)) return null;

        const explicit = el.closest(EXTERNAL_SELECTOR);
        if (explicit && !isBuilderRoot(explicit) && hasSliderSignals(explicit)) {
            return explicit;
        }

        const knownRoot = el.closest('.swiper, .swiper-container, .slick-slider, .splide, .keen-slider, .glide, [data-slider], [data-carousel]');
        if (knownRoot && !isBuilderRoot(knownRoot) && hasSliderSignals(knownRoot)) {
            return knownRoot;
        }

        const genericRoot = el.closest('[class*="slider"], [class*="carousel"]');
        if (genericRoot && !isBuilderRoot(genericRoot) && hasSliderSignals(genericRoot)) {
            return genericRoot;
        }

        return null;
    };

    const collectRoots = (scope) => {
        const base = scope?.nodeType === 1 || scope?.nodeType === 9 ? scope : document;
        const candidates = Array.from(base.querySelectorAll(CANDIDATE_SELECTOR));
        if (base.nodeType === 1 && base.matches?.(CANDIDATE_SELECTOR)) {
            candidates.unshift(base);
        }
        const roots = new Set();

        candidates.forEach((candidate) => {
            const root = findNearestSliderRoot(candidate);
            if (root) roots.add(root);
        });

        return Array.from(roots).filter((root) => !Array.from(roots).some((other) => other !== root && other.contains(root)));
    };

    const pickTrack = (root) => {
        if (!root || root.nodeType !== 1) return null;
        const known = root.querySelector(TRACK_SELECTOR);
        if (known) return known;

        const directChildren = Array.from(root.children || []).filter((child) => child.nodeType === 1);
        if (directChildren.length > 1) return root;

        const nested = directChildren.find((child) => (child.children?.length || 0) > 1);
        return nested || null;
    };

    const markRoot = (root) => {
        if (!root || root.nodeType !== 1 || isBuilderRoot(root)) return;

        if (!root.getAttribute('data-slider-source')) {
            root.setAttribute('data-slider-source', 'external');
        }

        const track = pickTrack(root);
        if (!track) return;

        root.querySelectorAll(`[${TRACK_ATTR}]`).forEach((node) => node.removeAttribute(TRACK_ATTR));
        root.querySelectorAll(`[${SLIDE_ATTR}]`).forEach((node) => node.removeAttribute(SLIDE_ATTR));
        track.setAttribute(TRACK_ATTR, 'true');

        const explicitSlides = Array.from(track.querySelectorAll(DIRECT_SLIDE_SELECTOR));
        const slides = explicitSlides.length > 0
            ? explicitSlides
            : Array.from(track.children || []).filter((child) => child && child.nodeType === 1);
        slides.forEach((slide) => slide.setAttribute(SLIDE_ATTR, 'true'));
    };

    const ensureStyle = () => {
        if (document.getElementById(STYLE_ID)) return;
        const style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = `
            [data-slider-source="external"],
            .ext-slider,
            [data-gjs-external-slider="true"] {
                overscroll-behavior-x: contain;
                overscroll-behavior-y: auto;
                -webkit-overflow-scrolling: touch;
                touch-action: pan-y pinch-zoom;
            }

            [data-slider-source="external"][${LOCK_ATTR}="x"],
            .ext-slider[${LOCK_ATTR}="x"],
            [data-gjs-external-slider="true"][${LOCK_ATTR}="x"] {
                touch-action: pan-x pinch-zoom;
            }

            [data-slider-source="external"] [${TRACK_ATTR}="true"],
            .ext-slider [${TRACK_ATTR}="true"],
            [data-gjs-external-slider="true"] [${TRACK_ATTR}="true"] {
                min-height: 0;
                touch-action: pan-x pan-y pinch-zoom;
                overscroll-behavior-x: contain;
            }

            [data-slider-source="external"] [${SLIDE_ATTR}="true"],
            .ext-slider [${SLIDE_ATTR}="true"],
            [data-gjs-external-slider="true"] [${SLIDE_ATTR}="true"] {
                min-height: 0;
                max-height: 100%;
                contain: layout paint;
            }
        `;
        document.head.appendChild(style);
    };

    const resolveTouch = (event, target) => {
        const changed = Array.from(event.changedTouches || []);
        return changed.find((touch) => touch.identifier === target.identifier) || null;
    };

    const beginTouch = (event) => {
        if (!window.matchMedia(MOBILE_QUERY).matches) return;
        const touch = event.changedTouches?.[0];
        const targetEl = event.target instanceof Element ? event.target : null;
        if (!touch || !targetEl) return;

        const root = findNearestSliderRoot(targetEl);
        if (!root || isBuilderRoot(root)) return;
        markRoot(root);

        touchState.set(touch.identifier, {
            id: touch.identifier,
            startX: touch.clientX,
            startY: touch.clientY,
            lock: '',
            root,
        });
    };

    const updateTouchLock = (event) => {
        if (!window.matchMedia(MOBILE_QUERY).matches) return;
        let blockSliderHandlers = false;

        touchState.forEach((state, key) => {
            const touch = resolveTouch(event, state);
            if (!touch) return;

            const dx = touch.clientX - state.startX;
            const dy = touch.clientY - state.startY;
            const absX = Math.abs(dx);
            const absY = Math.abs(dy);
            if (!state.lock && absX < 8 && absY < 8) return;

            if (!state.lock) {
                if (absY > absX * 1.15) state.lock = 'y';
                else if (absX > absY * 1.15) state.lock = 'x';
                else state.lock = absY >= absX ? 'y' : 'x';
            }

            state.root.setAttribute(LOCK_ATTR, state.lock);
            if (state.lock === 'y') blockSliderHandlers = true;
            touchState.set(key, state);
        });

        if (blockSliderHandlers) {
            event.stopImmediatePropagation();
        }
    };

    const endTouch = (event) => {
        const changed = Array.from(event.changedTouches || []);
        changed.forEach((touch) => {
            const state = touchState.get(touch.identifier);
            if (state?.root) {
                state.root.removeAttribute(LOCK_ATTR);
            }
            touchState.delete(touch.identifier);
        });
    };

    const onWheel = (event) => {
        if (!window.matchMedia(MOBILE_QUERY).matches) return;
        const targetEl = event.target instanceof Element ? event.target : null;
        if (!targetEl) return;
        const root = findNearestSliderRoot(targetEl);
        if (!root || isBuilderRoot(root)) return;

        const absX = Math.abs(event.deltaX || 0);
        const absY = Math.abs(event.deltaY || 0);
        if (absY <= absX * 1.1) return;

        root.setAttribute(LOCK_ATTR, 'y-wheel');
        event.stopImmediatePropagation();

        if (wheelReleaseTimer) {
            window.clearTimeout(wheelReleaseTimer);
        }
        wheelReleaseTimer = window.setTimeout(() => {
            root.removeAttribute(LOCK_ATTR);
        }, 90);
    };

    const refresh = (scope) => {
        ensureStyle();
        const roots = collectRoots(scope || document);
        roots.forEach(markRoot);
    };

    const observe = () => {
        if (window.__externalSliderTouchObserver) return;
        const observer = new MutationObserver((mutations) => {
            const rootsToRefresh = new Set();
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.target instanceof Element) {
                    const root = findNearestSliderRoot(mutation.target);
                    if (root) rootsToRefresh.add(root);
                }
                mutation.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) return;
                    const root = findNearestSliderRoot(node) || collectRoots(node)[0];
                    if (root) rootsToRefresh.add(root);
                });
            });
            if (rootsToRefresh.size) {
                rootsToRefresh.forEach((root) => markRoot(root));
            }
        });
        observer.observe(document.documentElement || document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'data-slider-source', 'data-slider', 'data-carousel', 'data-gjs-external-slider'],
        });
        window.__externalSliderTouchObserver = observer;
    };

    const bind = () => {
        if (window.__externalSliderTouchBound) return;
        document.addEventListener('touchstart', beginTouch, { capture: true, passive: true });
        document.addEventListener('touchmove', updateTouchLock, { capture: true, passive: true });
        document.addEventListener('touchend', endTouch, { capture: true, passive: true });
        document.addEventListener('touchcancel', endTouch, { capture: true, passive: true });
        document.addEventListener('wheel', onWheel, { capture: true, passive: true });
        window.__externalSliderTouchBound = true;
    };

    window.ExternalSliderTouchRuntime = {
        refresh,
        version: '1.0.0',
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            refresh();
            bind();
            observe();
        }, { once: true });
    } else {
        refresh();
        bind();
        observe();
    }

    window.addEventListener('load', () => refresh());
})();
