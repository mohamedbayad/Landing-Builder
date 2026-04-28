const DEFAULT_MERGE_TAGS = [
    'first_name',
    'last_name',
    'email',
    'phone',
    'product_name',
    'order_total',
    'landing_page_name',
    'unsubscribe_url',
];

const TOKEN_CHARS_REGEX = /^[a-zA-Z0-9_]*$/;
const TOKEN_CHAR_REGEX = /[a-zA-Z0-9_]/;

class TextareaMergeTagAutocomplete {
    constructor(textarea, mergeTags = DEFAULT_MERGE_TAGS) {
        this.textarea = textarea;
        this.mergeTags = Array.isArray(mergeTags) && mergeTags.length ? mergeTags : DEFAULT_MERGE_TAGS;
        this.activeToken = null;
        this.filteredTags = [];
        this.activeIndex = 0;
        this.isOpen = false;

        this.wrapper = this.resolveWrapper();
        this.dropdown = this.createDropdown();
        this.list = this.dropdown.querySelector('[data-merge-tag-list]');

        this.handleInput = this.handleInput.bind(this);
        this.handleCaretMove = this.handleCaretMove.bind(this);
        this.handleKeydown = this.handleKeydown.bind(this);
        this.handleOutsidePointer = this.handleOutsidePointer.bind(this);
        this.handleSuggestionClick = this.handleSuggestionClick.bind(this);
        this.handleSuggestionMouseDown = this.handleSuggestionMouseDown.bind(this);

        this.bindEvents();
    }

    resolveWrapper() {
        const explicitWrapper = this.textarea.closest('[data-merge-tag-wrapper]');
        if (explicitWrapper) {
            return explicitWrapper;
        }

        const fallback = this.textarea.parentElement;
        if (fallback && getComputedStyle(fallback).position === 'static') {
            fallback.style.position = 'relative';
        }
        return fallback;
    }

    createDropdown() {
        const dropdown = document.createElement('div');
        dropdown.className = [
            'hidden',
            'absolute',
            'left-0',
            'right-0',
            'top-full',
            'mt-2',
            'z-30',
            'rounded-lg',
            'border',
            'border-gray-200',
            'dark:border-white/[0.08]',
            'bg-white',
            'dark:bg-[#0D1117]',
            'shadow-lg',
            'overflow-hidden',
            'max-h-56',
            'overflow-y-auto',
        ].join(' ');

        dropdown.innerHTML = '<ul data-merge-tag-list class="py-1"></ul>';
        this.wrapper.appendChild(dropdown);
        return dropdown;
    }

    bindEvents() {
        this.textarea.setAttribute('autocomplete', 'off');

        this.textarea.addEventListener('input', this.handleInput);
        this.textarea.addEventListener('keyup', this.handleCaretMove);
        this.textarea.addEventListener('click', this.handleCaretMove);
        this.textarea.addEventListener('keydown', this.handleKeydown);

        this.list.addEventListener('mousedown', this.handleSuggestionMouseDown);
        this.list.addEventListener('click', this.handleSuggestionClick);

        document.addEventListener('mousedown', this.handleOutsidePointer);
    }

    handleInput() {
        this.refreshSuggestions();
    }

    handleCaretMove(event) {
        if (['ArrowUp', 'ArrowDown', 'Enter', 'Tab', 'Escape'].includes(event.key)) {
            return;
        }
        this.refreshSuggestions();
    }

    handleKeydown(event) {
        if (!this.isOpen) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.activeIndex = (this.activeIndex + 1) % this.filteredTags.length;
            this.renderSuggestions();
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.activeIndex = (this.activeIndex - 1 + this.filteredTags.length) % this.filteredTags.length;
            this.renderSuggestions();
            return;
        }

        if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault();
            this.insertTag(this.filteredTags[this.activeIndex]);
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            this.close();
        }
    }

    handleOutsidePointer(event) {
        if (!this.wrapper.contains(event.target)) {
            this.close();
        }
    }

    handleSuggestionMouseDown(event) {
        const item = event.target.closest('[data-merge-tag-item]');
        if (!item) {
            return;
        }

        // Keep textarea focus while selecting with mouse.
        event.preventDefault();
    }

    handleSuggestionClick(event) {
        const item = event.target.closest('[data-merge-tag-item]');
        if (!item) {
            return;
        }

        const index = Number.parseInt(item.dataset.index, 10);
        if (Number.isNaN(index)) {
            return;
        }

        this.insertTag(this.filteredTags[index]);
    }

    refreshSuggestions() {
        const token = this.getActiveToken();
        if (!token) {
            this.close();
            return;
        }

        const query = token.query.toLowerCase();
        const filtered = this.mergeTags.filter((tag) => tag.toLowerCase().startsWith(query));
        if (!filtered.length) {
            this.close();
            return;
        }

        this.activeToken = token;
        this.filteredTags = filtered;
        this.activeIndex = 0;
        this.open();
        this.renderSuggestions();
    }

    getActiveToken() {
        if (this.textarea.selectionStart !== this.textarea.selectionEnd) {
            return null;
        }

        const caret = this.textarea.selectionStart;
        const value = this.textarea.value;
        const leftText = value.slice(0, caret);

        const openIndex = leftText.lastIndexOf('{{');
        if (openIndex === -1) {
            return null;
        }

        const closedBeforeCaret = leftText.lastIndexOf('}}');
        if (closedBeforeCaret > openIndex) {
            return null;
        }

        const closeIndex = value.indexOf('}}', openIndex + 2);
        if (closeIndex !== -1 && caret <= closeIndex + 2) {
            return null;
        }

        const query = leftText.slice(openIndex + 2);
        if (!TOKEN_CHARS_REGEX.test(query)) {
            return null;
        }

        let end = caret;
        while (end < value.length && TOKEN_CHAR_REGEX.test(value[end])) {
            end += 1;
        }

        return {
            start: openIndex,
            end,
            query,
        };
    }

    open() {
        this.isOpen = true;
        this.dropdown.classList.remove('hidden');
    }

    close() {
        this.isOpen = false;
        this.activeToken = null;
        this.filteredTags = [];
        this.activeIndex = 0;
        this.dropdown.classList.add('hidden');
        this.list.innerHTML = '';
    }

    renderSuggestions() {
        this.list.innerHTML = this.filteredTags
            .map((tag, index) => {
                const active = index === this.activeIndex;
                const itemClass = active
                    ? 'bg-orange-50 text-brand-orange dark:bg-orange-500/20 dark:text-orange-200'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.06]';

                return `
                    <li>
                        <button
                            type="button"
                            data-merge-tag-item
                            data-index="${index}"
                            class="w-full text-left px-3 py-2 text-sm font-medium transition-colors ${itemClass}"
                        >
                            {{${tag}}}
                        </button>
                    </li>
                `;
            })
            .join('');
    }

    insertTag(tag) {
        const token = this.activeToken ?? this.getActiveToken();
        if (!token || !tag) {
            this.close();
            return;
        }

        const insertion = `{{${tag}}}`;
        const current = this.textarea.value;

        this.textarea.value = `${current.slice(0, token.start)}${insertion}${current.slice(token.end)}`;

        const nextCaret = token.start + insertion.length;
        this.textarea.focus();
        this.textarea.setSelectionRange(nextCaret, nextCaret);
        this.textarea.dispatchEvent(new Event('input', { bubbles: true }));

        this.close();
    }
}

const parseMergeTags = (textarea) => {
    const payload = textarea.dataset.mergeTags;
    if (!payload) {
        return DEFAULT_MERGE_TAGS;
    }

    try {
        const parsed = JSON.parse(payload);
        if (Array.isArray(parsed)) {
            return parsed.filter((value) => typeof value === 'string' && value.trim().length > 0);
        }
    } catch (error) {
        console.warn('Invalid merge tag config on textarea', error);
    }

    return DEFAULT_MERGE_TAGS;
};

export const initTextareaMergeTagAutocomplete = (root = document) => {
    root.querySelectorAll('textarea[data-merge-tag-autocomplete]').forEach((textarea) => {
        if (textarea.dataset.mergeTagAutocompleteReady === '1') {
            return;
        }

        const mergeTags = parseMergeTags(textarea);
        new TextareaMergeTagAutocomplete(textarea, mergeTags);
        textarea.dataset.mergeTagAutocompleteReady = '1';
    });
};
