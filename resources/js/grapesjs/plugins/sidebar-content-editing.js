/**
 * Plugin: Sidebar Content Editing — DEPRECATED (no-op)
 *
 * This plugin has been intentionally disabled.
 *
 * The sidebar trait-injection approach caused too many issues with
 * dynamically revealed (toggled) nested elements. We now rely entirely
 * on GrapesJS's native inline Rich Text Editor (RTE):
 *
 *   • Double-click any text element on the canvas → native RTE opens.
 *   • Ctrl/Cmd + Click → triggers the component's embedded JS behavior
 *     (handled by the Canvas Interaction Control plugin).
 *
 * The file is kept as an importable no-op so that editor.js does not
 * need to be changed.
 */
export default function sidebarContentEditingPlugin(editor, opts = {}) {
    // ── No-op ──────────────────────────────────────────────────────────────────
    // GrapesJS's default `editable: true` on text components is preserved.
    // Double-clicking any text element on the canvas will open the built-in RTE.
    // No traits are injected, no events are intercepted.
    console.log('[GrapesJS] Sidebar Content Editing plugin: native RTE mode active.');
}
