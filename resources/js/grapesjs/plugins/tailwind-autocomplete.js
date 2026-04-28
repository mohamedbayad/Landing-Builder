/**
 * Plugin: tailwind-autocomplete
 *
 * Provides Tailwind CSS class autocomplete for the default GrapesJS
 * Classes manager input (the built-in class option), without adding
 * any custom trait field.
 */

const TAILWIND_CLASSES = {
    display: ['block', 'inline-block', 'inline', 'flex', 'inline-flex', 'grid', 'inline-grid', 'hidden', 'contents'],
    flexDirection: ['flex-row', 'flex-row-reverse', 'flex-col', 'flex-col-reverse'],
    flexWrap: ['flex-wrap', 'flex-wrap-reverse', 'flex-nowrap'],
    justifyContent: ['justify-start', 'justify-end', 'justify-center', 'justify-between', 'justify-around', 'justify-evenly'],
    alignItems: ['items-start', 'items-end', 'items-center', 'items-baseline', 'items-stretch'],
    alignContent: ['content-start', 'content-end', 'content-center', 'content-between', 'content-around', 'content-evenly'],
    gap: ['gap-0', 'gap-1', 'gap-2', 'gap-3', 'gap-4', 'gap-5', 'gap-6', 'gap-8', 'gap-10', 'gap-12', 'gap-16', 'gap-20', 'gap-24'],
    padding: [
        'p-0', 'p-0.5', 'p-1', 'p-1.5', 'p-2', 'p-2.5', 'p-3', 'p-3.5', 'p-4', 'p-5', 'p-6', 'p-7', 'p-8', 'p-9', 'p-10', 'p-11', 'p-12', 'p-14', 'p-16', 'p-20', 'p-24', 'p-28', 'p-32', 'p-36', 'p-40', 'p-44', 'p-48', 'p-52', 'p-56', 'p-60', 'p-64', 'p-72', 'p-80', 'p-96',
        'px-0', 'px-1', 'px-2', 'px-3', 'px-4', 'px-5', 'px-6', 'px-8', 'px-10', 'px-12', 'px-16', 'px-20', 'px-24',
        'py-0', 'py-1', 'py-2', 'py-3', 'py-4', 'py-5', 'py-6', 'py-8', 'py-10', 'py-12', 'py-16', 'py-20', 'py-24',
        'pt-0', 'pt-1', 'pt-2', 'pt-4', 'pt-6', 'pt-8', 'pt-10', 'pt-12', 'pt-16', 'pt-20', 'pt-24',
        'pr-0', 'pr-1', 'pr-2', 'pr-4', 'pr-6', 'pr-8', 'pr-10', 'pr-12', 'pr-16', 'pr-20', 'pr-24',
        'pb-0', 'pb-1', 'pb-2', 'pb-4', 'pb-6', 'pb-8', 'pb-10', 'pb-12', 'pb-16', 'pb-20', 'pb-24',
        'pl-0', 'pl-1', 'pl-2', 'pl-4', 'pl-6', 'pl-8', 'pl-10', 'pl-12', 'pl-16', 'pl-20', 'pl-24',
    ],
    margin: [
        'm-0', 'm-1', 'm-2', 'm-3', 'm-4', 'm-5', 'm-6', 'm-8', 'm-10', 'm-12', 'm-16', 'm-20', 'm-24', 'm-auto',
        'mx-0', 'mx-1', 'mx-2', 'mx-3', 'mx-4', 'mx-5', 'mx-6', 'mx-8', 'mx-10', 'mx-12', 'mx-16', 'mx-auto',
        'my-0', 'my-1', 'my-2', 'my-3', 'my-4', 'my-5', 'my-6', 'my-8', 'my-10', 'my-12', 'my-16',
        'mt-0', 'mt-1', 'mt-2', 'mt-4', 'mt-6', 'mt-8', 'mt-10', 'mt-12', 'mt-16', 'mt-auto',
        'mr-0', 'mr-1', 'mr-2', 'mr-4', 'mr-6', 'mr-8', 'mr-10', 'mr-12', 'mr-16', 'mr-auto',
        'mb-0', 'mb-1', 'mb-2', 'mb-4', 'mb-6', 'mb-8', 'mb-10', 'mb-12', 'mb-16', 'mb-auto',
        'ml-0', 'ml-1', 'ml-2', 'ml-4', 'ml-6', 'ml-8', 'ml-10', 'ml-12', 'ml-16', 'ml-auto',
        '-m-1', '-m-2', '-m-3', '-m-4', '-mx-1', '-mx-2', '-my-1', '-my-2',
    ],
    width: [
        'w-0', 'w-1', 'w-2', 'w-3', 'w-4', 'w-5', 'w-6', 'w-8', 'w-10', 'w-12', 'w-16', 'w-20', 'w-24', 'w-32', 'w-40', 'w-48', 'w-56', 'w-64', 'w-72', 'w-80', 'w-96',
        'w-auto', 'w-full', 'w-screen', 'w-min', 'w-max', 'w-fit',
        'w-1/2', 'w-1/3', 'w-2/3', 'w-1/4', 'w-2/4', 'w-3/4', 'w-1/5', 'w-2/5', 'w-3/5', 'w-4/5', 'w-1/6', 'w-5/6', 'w-1/12', 'w-11/12',
    ],
    height: [
        'h-0', 'h-1', 'h-2', 'h-3', 'h-4', 'h-5', 'h-6', 'h-8', 'h-10', 'h-12', 'h-16', 'h-20', 'h-24', 'h-32', 'h-40', 'h-48', 'h-56', 'h-64', 'h-72', 'h-80', 'h-96',
        'h-auto', 'h-full', 'h-screen', 'h-min', 'h-max', 'h-fit',
        'h-1/2', 'h-1/3', 'h-2/3', 'h-1/4', 'h-3/4', 'h-1/5', 'h-2/5', 'h-3/5', 'h-4/5', 'h-1/6', 'h-5/6',
    ],
    fontSize: ['text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl', 'text-5xl', 'text-6xl', 'text-7xl', 'text-8xl', 'text-9xl'],
    fontWeight: ['font-thin', 'font-extralight', 'font-light', 'font-normal', 'font-medium', 'font-semibold', 'font-bold', 'font-extrabold', 'font-black'],
    fontStyle: ['italic', 'not-italic'],
    textAlign: ['text-left', 'text-center', 'text-right', 'text-justify', 'text-start', 'text-end'],
    textTransform: ['uppercase', 'lowercase', 'capitalize', 'normal-case'],
    textDecoration: ['underline', 'overline', 'line-through', 'no-underline'],
    lineHeight: ['leading-none', 'leading-tight', 'leading-snug', 'leading-normal', 'leading-relaxed', 'leading-loose'],
    letterSpacing: ['tracking-tighter', 'tracking-tight', 'tracking-normal', 'tracking-wide', 'tracking-wider', 'tracking-widest'],
    textColors: [
        'text-white', 'text-black', 'text-transparent', 'text-current',
        'text-slate-50', 'text-slate-100', 'text-slate-200', 'text-slate-300', 'text-slate-400', 'text-slate-500', 'text-slate-600', 'text-slate-700', 'text-slate-800', 'text-slate-900',
        'text-gray-50', 'text-gray-100', 'text-gray-200', 'text-gray-300', 'text-gray-400', 'text-gray-500', 'text-gray-600', 'text-gray-700', 'text-gray-800', 'text-gray-900',
        'text-red-50', 'text-red-100', 'text-red-200', 'text-red-300', 'text-red-400', 'text-red-500', 'text-red-600', 'text-red-700', 'text-red-800', 'text-red-900',
        'text-orange-50', 'text-orange-100', 'text-orange-200', 'text-orange-300', 'text-orange-400', 'text-orange-500', 'text-orange-600', 'text-orange-700', 'text-orange-800', 'text-orange-900',
        'text-amber-50', 'text-amber-100', 'text-amber-200', 'text-amber-300', 'text-amber-400', 'text-amber-500', 'text-amber-600', 'text-amber-700', 'text-amber-800', 'text-amber-900',
        'text-yellow-50', 'text-yellow-100', 'text-yellow-200', 'text-yellow-300', 'text-yellow-400', 'text-yellow-500', 'text-yellow-600', 'text-yellow-700', 'text-yellow-800', 'text-yellow-900',
        'text-lime-50', 'text-lime-100', 'text-lime-200', 'text-lime-300', 'text-lime-400', 'text-lime-500', 'text-lime-600', 'text-lime-700', 'text-lime-800', 'text-lime-900',
        'text-green-50', 'text-green-100', 'text-green-200', 'text-green-300', 'text-green-400', 'text-green-500', 'text-green-600', 'text-green-700', 'text-green-800', 'text-green-900',
        'text-emerald-50', 'text-emerald-100', 'text-emerald-200', 'text-emerald-300', 'text-emerald-400', 'text-emerald-500', 'text-emerald-600', 'text-emerald-700', 'text-emerald-800', 'text-emerald-900',
        'text-teal-50', 'text-teal-100', 'text-teal-200', 'text-teal-300', 'text-teal-400', 'text-teal-500', 'text-teal-600', 'text-teal-700', 'text-teal-800', 'text-teal-900',
        'text-cyan-50', 'text-cyan-100', 'text-cyan-200', 'text-cyan-300', 'text-cyan-400', 'text-cyan-500', 'text-cyan-600', 'text-cyan-700', 'text-cyan-800', 'text-cyan-900',
        'text-sky-50', 'text-sky-100', 'text-sky-200', 'text-sky-300', 'text-sky-400', 'text-sky-500', 'text-sky-600', 'text-sky-700', 'text-sky-800', 'text-sky-900',
        'text-blue-50', 'text-blue-100', 'text-blue-200', 'text-blue-300', 'text-blue-400', 'text-blue-500', 'text-blue-600', 'text-blue-700', 'text-blue-800', 'text-blue-900',
        'text-indigo-50', 'text-indigo-100', 'text-indigo-200', 'text-indigo-300', 'text-indigo-400', 'text-indigo-500', 'text-indigo-600', 'text-indigo-700', 'text-indigo-800', 'text-indigo-900',
        'text-violet-50', 'text-violet-100', 'text-violet-200', 'text-violet-300', 'text-violet-400', 'text-violet-500', 'text-violet-600', 'text-violet-700', 'text-violet-800', 'text-violet-900',
        'text-purple-50', 'text-purple-100', 'text-purple-200', 'text-purple-300', 'text-purple-400', 'text-purple-500', 'text-purple-600', 'text-purple-700', 'text-purple-800', 'text-purple-900',
        'text-fuchsia-50', 'text-fuchsia-100', 'text-fuchsia-200', 'text-fuchsia-300', 'text-fuchsia-400', 'text-fuchsia-500', 'text-fuchsia-600', 'text-fuchsia-700', 'text-fuchsia-800', 'text-fuchsia-900',
        'text-pink-50', 'text-pink-100', 'text-pink-200', 'text-pink-300', 'text-pink-400', 'text-pink-500', 'text-pink-600', 'text-pink-700', 'text-pink-800', 'text-pink-900',
        'text-rose-50', 'text-rose-100', 'text-rose-200', 'text-rose-300', 'text-rose-400', 'text-rose-500', 'text-rose-600', 'text-rose-700', 'text-rose-800', 'text-rose-900',
    ],
    bgColors: [
        'bg-white', 'bg-black', 'bg-transparent', 'bg-current',
        'bg-slate-50', 'bg-slate-100', 'bg-slate-200', 'bg-slate-300', 'bg-slate-400', 'bg-slate-500', 'bg-slate-600', 'bg-slate-700', 'bg-slate-800', 'bg-slate-900',
        'bg-gray-50', 'bg-gray-100', 'bg-gray-200', 'bg-gray-300', 'bg-gray-400', 'bg-gray-500', 'bg-gray-600', 'bg-gray-700', 'bg-gray-800', 'bg-gray-900',
        'bg-red-50', 'bg-red-100', 'bg-red-200', 'bg-red-300', 'bg-red-400', 'bg-red-500', 'bg-red-600', 'bg-red-700', 'bg-red-800', 'bg-red-900',
        'bg-blue-50', 'bg-blue-100', 'bg-blue-200', 'bg-blue-300', 'bg-blue-400', 'bg-blue-500', 'bg-blue-600', 'bg-blue-700', 'bg-blue-800', 'bg-blue-900',
        'bg-green-50', 'bg-green-100', 'bg-green-200', 'bg-green-300', 'bg-green-400', 'bg-green-500', 'bg-green-600', 'bg-green-700', 'bg-green-800', 'bg-green-900',
        'bg-yellow-50', 'bg-yellow-100', 'bg-yellow-200', 'bg-yellow-300', 'bg-yellow-400', 'bg-yellow-500', 'bg-yellow-600', 'bg-yellow-700', 'bg-yellow-800', 'bg-yellow-900',
        'bg-purple-50', 'bg-purple-100', 'bg-purple-200', 'bg-purple-300', 'bg-purple-400', 'bg-purple-500', 'bg-purple-600', 'bg-purple-700', 'bg-purple-800', 'bg-purple-900',
        'bg-pink-50', 'bg-pink-100', 'bg-pink-200', 'bg-pink-300', 'bg-pink-400', 'bg-pink-500', 'bg-pink-600', 'bg-pink-700', 'bg-pink-800', 'bg-pink-900',
    ],
    borderWidth: ['border', 'border-0', 'border-2', 'border-4', 'border-8', 'border-t', 'border-r', 'border-b', 'border-l'],
    borderColor: ['border-gray-200', 'border-gray-300', 'border-gray-400', 'border-red-500', 'border-blue-500', 'border-green-500'],
    borderRadius: [
        'rounded-none', 'rounded-sm', 'rounded', 'rounded-md', 'rounded-lg', 'rounded-xl', 'rounded-2xl', 'rounded-3xl', 'rounded-full',
        'rounded-t-none', 'rounded-t', 'rounded-t-lg', 'rounded-t-full',
        'rounded-r-none', 'rounded-r', 'rounded-r-lg', 'rounded-r-full',
        'rounded-b-none', 'rounded-b', 'rounded-b-lg', 'rounded-b-full',
        'rounded-l-none', 'rounded-l', 'rounded-l-lg', 'rounded-l-full',
    ],
    shadow: ['shadow-none', 'shadow-sm', 'shadow', 'shadow-md', 'shadow-lg', 'shadow-xl', 'shadow-2xl', 'shadow-inner'],
    opacity: ['opacity-0', 'opacity-5', 'opacity-10', 'opacity-20', 'opacity-25', 'opacity-30', 'opacity-40', 'opacity-50', 'opacity-60', 'opacity-70', 'opacity-75', 'opacity-80', 'opacity-90', 'opacity-95', 'opacity-100'],
    blur: ['blur-none', 'blur-sm', 'blur', 'blur-md', 'blur-lg', 'blur-xl', 'blur-2xl', 'blur-3xl'],
    transition: ['transition-none', 'transition-all', 'transition', 'transition-colors', 'transition-opacity', 'transition-shadow', 'transition-transform'],
    duration: ['duration-75', 'duration-100', 'duration-150', 'duration-200', 'duration-300', 'duration-500', 'duration-700', 'duration-1000'],
    ease: ['ease-linear', 'ease-in', 'ease-out', 'ease-in-out'],
    scale: ['scale-0', 'scale-50', 'scale-75', 'scale-90', 'scale-95', 'scale-100', 'scale-105', 'scale-110', 'scale-125', 'scale-150'],
    rotate: ['rotate-0', 'rotate-1', 'rotate-2', 'rotate-3', 'rotate-6', 'rotate-12', 'rotate-45', 'rotate-90', 'rotate-180', '-rotate-180', '-rotate-90', '-rotate-45'],
    position: ['static', 'fixed', 'absolute', 'relative', 'sticky'],
    inset: ['inset-0', 'inset-x-0', 'inset-y-0', 'top-0', 'right-0', 'bottom-0', 'left-0'],
    zIndex: ['z-0', 'z-10', 'z-20', 'z-30', 'z-40', 'z-50', 'z-auto'],
    cursor: ['cursor-auto', 'cursor-default', 'cursor-pointer', 'cursor-wait', 'cursor-text', 'cursor-move', 'cursor-not-allowed', 'cursor-grab'],
    responsive: ['sm:', 'md:', 'lg:', 'xl:', '2xl:'],
    states: ['hover:', 'focus:', 'active:', 'disabled:', 'group-hover:', 'focus-within:'],
};

const ALL_TAILWIND_CLASSES = Array.from(new Set(Object.values(TAILWIND_CLASSES).flat()));

const CLASS_INPUT_SELECTORS = [
    '#gjs-clm-new',
    '.gjs-clm-input',
    '.gjs-clm-field input[type="text"]',
    '.gjs-clm-tags input[data-input]',
    '.gjs-clm-tags input',
    'input[data-input]',
    'input[id$="clm-new"]',
];

const parseIntSetting = (value, fallback, min, max) => {
    if (value == null || value === '') return fallback;
    const parsed = Number.parseInt(String(value), 10);
    if (!Number.isFinite(parsed)) return fallback;
    return Math.min(max, Math.max(min, parsed));
};

const isClassManagerInput = (input) => {
    if (!(input instanceof HTMLInputElement)) return false;
    if (input.disabled || input.readOnly) return false;

    if (input.closest('.gjs-clm-tags') || input.closest('.gjs-clm-field') || input.closest('[id$="clm-tags-field"]')) {
        return true;
    }

    const id = String(input.id || '').toLowerCase();
    if (id === 'gjs-clm-new' || id.endsWith('clm-new') || id.includes('clm-new')) {
        return true;
    }

    if (input.hasAttribute('data-input')) {
        return true;
    }

    const placeholder = String(input.getAttribute('placeholder') || '').toLowerCase();
    const name = String(input.getAttribute('name') || '').toLowerCase();
    return placeholder.includes('class') || name.includes('class');
};

const getClassInputs = (root) => {
    const found = new Set();
    CLASS_INPUT_SELECTORS.forEach((selector) => {
        root.querySelectorAll(selector).forEach((input) => {
            if (isClassManagerInput(input)) {
                found.add(input);
            }
        });
    });
    return Array.from(found);
};

const BASE_TAILWIND_CLASSES = ALL_TAILWIND_CLASSES.filter((item) => !item.endsWith(':'));

const parseTailwindToken = (token) => {
    const raw = String(token || '').trim();
    if (!raw) {
        return {
            raw: '',
            baseQuery: '',
            important: false,
            variantPrefix: '',
        };
    }

    const segments = raw.split(':');
    const baseSegment = segments.pop() || '';
    const important = baseSegment.startsWith('!');
    const baseQuery = important ? baseSegment.slice(1) : baseSegment;
    const variantPrefix = segments.filter(Boolean).join(':');

    return {
        raw,
        baseQuery,
        important,
        variantPrefix: variantPrefix ? `${variantPrefix}:` : '',
    };
};

const queryMatches = (query, maxSuggestions) => {
    const token = parseTailwindToken(query);
    const normalized = token.baseQuery.toLowerCase();
    if (!normalized) return [];

    const startsWith = [];
    const includes = [];

    BASE_TAILWIND_CLASSES.forEach((item) => {
        const lowered = item.toLowerCase();
        if (lowered.startsWith(normalized)) {
            startsWith.push(`${token.variantPrefix}${token.important ? '!' : ''}${item}`);
        } else if (lowered.includes(normalized)) {
            includes.push(`${token.variantPrefix}${token.important ? '!' : ''}${item}`);
        }
    });

    return [...startsWith, ...includes].slice(0, maxSuggestions);
};

const replaceLastToken = (value, token) => {
    const chunks = String(value || '')
        .split(/\s+/)
        .map((part) => part.trim())
        .filter(Boolean);

    if (chunks.length === 0) {
        return token;
    }

    chunks[chunks.length - 1] = token;
    return chunks.join(' ');
};

const dispatchInputEvents = (input) => {
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
};

const findAddButton = (input) => {
    const searchRoots = [
        input.closest('.gjs-clm-tags'),
        input.closest('[id$="clm-tags-field"]'),
        input.closest('.gjs-clm-field')?.parentElement,
        input.closest('.gjs-sm-property'),
        input.closest('.gjs-sm-sector'),
        input.closest('.gjs-sm-sectors'),
        input.closest('.gjs-editor'),
        document,
    ].filter(Boolean);

    for (const root of searchRoots) {
        const selectors = [
            '[data-add]',
            '#gjs-clm-add-tag',
            '.gjs-clm-tags-btn__add',
            '.gjs-clm-add-btn',
            '[id$="clm-add-tag"]',
        ];

        for (const selector of selectors) {
            const button = root.querySelector(selector);
            if (button) {
                return button;
            }
        }
    }

    return null;
};

export default function tailwindAutocompletePlugin(editor, options = {}) {
    if (editor.__tailwindAutocompleteReady) return;
    editor.__tailwindAutocompleteReady = true;

    const minChars = parseIntSetting(options.minChars ?? options.min_chars, 2, 1, 5);
    const maxSuggestions = parseIntSetting(options.maxSuggestions ?? options.max_suggestions, 15, 5, 40);
    const states = new WeakMap();

    const hideDropdown = (state) => {
        if (!state) return;
        state.dropdown.style.display = 'none';
        state.matches = [];
        state.activeIndex = -1;
    };

    const commitSelectedClass = (state, selected) => {
        if (!selected) return;

        state.input.value = replaceLastToken(state.input.value, selected);
        dispatchInputEvents(state.input);

        let applied = false;
        const selectedComponents = editor.getSelectedAll?.() || [editor.getSelected?.()].filter(Boolean);
        selectedComponents.forEach((component) => {
            if (!component || typeof component.addClass !== 'function') {
                return;
            }

            component.addClass(selected);
            applied = true;
        });

        if (!applied) {
            const addButton = findAddButton(state.input);
            if (addButton && !addButton.disabled) {
                addButton.click();
            }
        }

        state.input.value = '';
        dispatchInputEvents(state.input);
    };

    const renderDropdown = (state) => {
        const { dropdown, matches, activeIndex } = state;

        if (!matches.length) {
            hideDropdown(state);
            return;
        }

        dropdown.innerHTML = matches.map((item, index) => {
            const isActive = index === activeIndex;
            return `
                <div data-tailwind-item="${item}" style="
                    padding: 7px 10px;
                    cursor: pointer;
                    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                    font-size: 12px;
                    border-bottom: 1px solid #3a3a3a;
                    background: ${isActive ? '#3b82f6' : 'transparent'};
                    color: ${isActive ? '#fff' : '#e5e7eb'};
                ">${item}</div>
            `;
        }).join('');

        dropdown.style.display = 'block';

        dropdown.querySelectorAll('[data-tailwind-item]').forEach((node, index) => {
            node.addEventListener('mouseenter', () => {
                state.activeIndex = index;
                renderDropdown(state);
            });

            node.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });

            node.addEventListener('click', () => {
                const selected = node.getAttribute('data-tailwind-item') || '';
                if (!selected) return;

                commitSelectedClass(state, selected);
                hideDropdown(state);
                state.input.focus();
            });
        });
    };

    const refreshSuggestions = (state) => {
        const token = String(state.input.value || '').trim().split(/\s+/).pop() || '';
        const parsedToken = parseTailwindToken(token);
        if (parsedToken.baseQuery.length < minChars) {
            hideDropdown(state);
            return;
        }

        state.matches = queryMatches(token, maxSuggestions);
        state.activeIndex = state.matches.length > 0 ? 0 : -1;
        renderDropdown(state);
    };

    const enhanceInput = (input) => {
        if (!input || states.has(input)) {
            return;
        }

        const wrapper = input.closest('.gjs-clm-field') || input.closest('[id$="clm-tags-field"]') || input.parentElement;
        if (!wrapper) {
            return;
        }

        if (!wrapper.style.position) {
            wrapper.style.position = 'relative';
        }

        const dropdown = document.createElement('div');
        dropdown.style.position = 'absolute';
        dropdown.style.top = 'calc(100% + 4px)';
        dropdown.style.left = '0';
        dropdown.style.right = '0';
        dropdown.style.display = 'none';
        dropdown.style.maxHeight = '220px';
        dropdown.style.overflowY = 'auto';
        dropdown.style.border = '1px solid #444';
        dropdown.style.background = '#1f1f1f';
        dropdown.style.borderRadius = '4px';
        dropdown.style.boxShadow = '0 8px 16px rgba(0,0,0,0.35)';
        dropdown.style.zIndex = '1200';
        dropdown.style.marginTop = '2px';
        wrapper.appendChild(dropdown);

        const state = {
            input,
            dropdown,
            matches: [],
            activeIndex: -1,
        };
        states.set(input, state);

        input.addEventListener('input', () => {
            refreshSuggestions(state);
        });

        input.addEventListener('focus', () => {
            refreshSuggestions(state);
        });

        input.addEventListener('keydown', (event) => {
            if (state.dropdown.style.display === 'none') {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                hideDropdown(state);
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                state.activeIndex = Math.min(state.activeIndex + 1, state.matches.length - 1);
                renderDropdown(state);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                state.activeIndex = Math.max(state.activeIndex - 1, 0);
                renderDropdown(state);
                return;
            }

            if (event.key === 'Enter' && state.activeIndex >= 0 && state.activeIndex < state.matches.length) {
                event.preventDefault();
                const selected = state.matches[state.activeIndex];
                commitSelectedClass(state, selected);
                hideDropdown(state);
            }
        });

        input.addEventListener('blur', () => {
            window.setTimeout(() => hideDropdown(state), 120);
        });
    };

    const bindAllClassInputs = () => {
        const root = editor.getContainer?.();
        if (!root) return;
        getClassInputs(root).forEach(enhanceInput);
    };

    let bindQueued = false;
    const queueBindInputs = () => {
        if (bindQueued) return;
        bindQueued = true;
        window.requestAnimationFrame(() => {
            bindQueued = false;
            bindAllClassInputs();
        });
    };

    const observer = new MutationObserver(() => {
        queueBindInputs();
    });

    const start = () => {
        const root = editor.getContainer?.();
        if (!root) return;
        observer.observe(root, {
            childList: true,
            subtree: true,
        });
        bindAllClassInputs();
    };

    editor.on('load', start);
    editor.on('component:selected', queueBindInputs);
    editor.on('style:target', queueBindInputs);
    editor.on('selector:add', queueBindInputs);
    editor.on('destroy', () => observer.disconnect());

    start();
}

export { TAILWIND_CLASSES, ALL_TAILWIND_CLASSES };
