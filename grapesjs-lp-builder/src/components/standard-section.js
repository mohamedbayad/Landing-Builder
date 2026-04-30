/**
 * GrapesJS component type for standard landing page sections.
 */

import { upsertSectionBadge } from '../utils/badge-injector';

/**
 * Register standard-section component type.
 * @param {import('grapesjs').Editor} editor
 */
export default function registerStandardSection(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('standard-section', {
        isComponent(el) {
            if (!el || typeof el.getAttribute !== 'function') {
                return false;
            }

            if (el.getAttribute('data-gjs-type') === 'standard-section') {
                return { type: 'standard-section' };
            }

            return false;
        },
        model: {
            defaults: {
                name: 'Section',
                draggable: true,
                droppable: true,
                style: { 'min-height': '80px', position: 'relative' },
            },
            init() {
                const attrs = this.getAttributes() || {};
                const label = String(attrs['data-label'] || '').trim();
                if (label) {
                    this.set('name', label);
                }
            },
        },
        view: {
            init() {
                this.listenTo(this.model, 'change:attributes', () => {
                    upsertSectionBadge(this.model, this.el);
                });
            },
            onRender() {
                upsertSectionBadge(this.model, this.el);
            },
        },
    });
}
