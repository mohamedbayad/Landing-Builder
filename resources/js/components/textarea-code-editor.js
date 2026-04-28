const INDENT = '    ';

class TextareaCodeEditor {
    constructor(textarea) {
        this.textarea = textarea;
        this.wrapper = textarea.closest('[data-code-editor-wrapper]') ?? textarea.parentElement;
        this.gutter = this.wrapper?.querySelector('[data-code-editor-gutter]') ?? null;

        if (!this.gutter) {
            return;
        }

        this.handleInput = this.handleInput.bind(this);
        this.handleScroll = this.handleScroll.bind(this);
        this.handleKeydown = this.handleKeydown.bind(this);

        this.textarea.style.tabSize = '4';
        this.textarea.style.MozTabSize = '4';

        this.bindEvents();
        this.refreshLineNumbers();
        this.syncScroll();
    }

    bindEvents() {
        this.textarea.addEventListener('input', this.handleInput);
        this.textarea.addEventListener('scroll', this.handleScroll);
        this.textarea.addEventListener('keydown', this.handleKeydown);
    }

    handleInput() {
        this.refreshLineNumbers();
    }

    handleScroll() {
        this.syncScroll();
    }

    handleKeydown(event) {
        if (event.defaultPrevented || event.key !== 'Tab') {
            return;
        }

        event.preventDefault();

        if (event.shiftKey) {
            this.outdentSelection();
        } else {
            this.indentSelection();
        }

        this.refreshLineNumbers();
        this.syncScroll();
        this.textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    refreshLineNumbers() {
        const lineCount = Math.max(1, this.textarea.value.split('\n').length);
        this.gutter.textContent = Array.from({ length: lineCount }, (_, i) => `${i + 1}`).join('\n');
    }

    syncScroll() {
        this.gutter.scrollTop = this.textarea.scrollTop;
    }

    indentSelection() {
        const start = this.textarea.selectionStart;
        const end = this.textarea.selectionEnd;
        const value = this.textarea.value;

        if (start === end) {
            this.textarea.value = `${value.slice(0, start)}${INDENT}${value.slice(end)}`;
            const cursor = start + INDENT.length;
            this.textarea.setSelectionRange(cursor, cursor);
            return;
        }

        const lineStart = value.lastIndexOf('\n', start - 1) + 1;
        const lineEndIndex = value.indexOf('\n', end);
        const lineEnd = lineEndIndex === -1 ? value.length : lineEndIndex;
        const block = value.slice(lineStart, lineEnd);
        const lines = block.split('\n');
        const indented = lines.map((line) => `${INDENT}${line}`).join('\n');

        this.textarea.value = `${value.slice(0, lineStart)}${indented}${value.slice(lineEnd)}`;

        const nextStart = start + INDENT.length;
        const nextEnd = end + (INDENT.length * lines.length);
        this.textarea.setSelectionRange(nextStart, nextEnd);
    }

    outdentSelection() {
        const start = this.textarea.selectionStart;
        const end = this.textarea.selectionEnd;
        const value = this.textarea.value;

        const stripOneIndent = (line) => {
            if (line.startsWith('\t')) {
                return { line: line.slice(1), removed: 1 };
            }

            const match = line.match(/^ {1,4}/);
            if (match) {
                return { line: line.slice(match[0].length), removed: match[0].length };
            }

            return { line, removed: 0 };
        };

        if (start === end) {
            const lineStart = value.lastIndexOf('\n', start - 1) + 1;
            const beforeCursor = value.slice(lineStart, start);
            const removableSpaces = beforeCursor.match(/ {1,4}$/);

            if (removableSpaces) {
                const removeCount = removableSpaces[0].length;
                this.textarea.value = `${value.slice(0, start - removeCount)}${value.slice(start)}`;
                const cursor = start - removeCount;
                this.textarea.setSelectionRange(cursor, cursor);
                return;
            }

            if (beforeCursor.endsWith('\t')) {
                this.textarea.value = `${value.slice(0, start - 1)}${value.slice(start)}`;
                const cursor = start - 1;
                this.textarea.setSelectionRange(cursor, cursor);
            }

            return;
        }

        const lineStart = value.lastIndexOf('\n', start - 1) + 1;
        const lineEndIndex = value.indexOf('\n', end);
        const lineEnd = lineEndIndex === -1 ? value.length : lineEndIndex;
        const block = value.slice(lineStart, lineEnd);
        const lines = block.split('\n');

        let removedOnFirstLine = 0;
        let totalRemoved = 0;

        const outdented = lines.map((line, index) => {
            const result = stripOneIndent(line);
            if (index === 0) {
                removedOnFirstLine = result.removed;
            }
            totalRemoved += result.removed;
            return result.line;
        }).join('\n');

        this.textarea.value = `${value.slice(0, lineStart)}${outdented}${value.slice(lineEnd)}`;

        const nextStart = Math.max(lineStart, start - removedOnFirstLine);
        const nextEnd = Math.max(nextStart, end - totalRemoved);
        this.textarea.setSelectionRange(nextStart, nextEnd);
    }
}

export const initTextareaCodeEditor = (root = document) => {
    root.querySelectorAll('textarea[data-code-editor]').forEach((textarea) => {
        if (textarea.dataset.codeEditorReady === '1') {
            return;
        }

        new TextareaCodeEditor(textarea);
        textarea.dataset.codeEditorReady = '1';
    });
};

