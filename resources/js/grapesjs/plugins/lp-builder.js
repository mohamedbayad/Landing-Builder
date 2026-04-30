/**
 * Workspace plugin bridge for grapesjs-lp-builder.
 * This installs the standalone LP Builder plugin inside the existing editor runtime.
 */

import gjsLPBuilder from '../../../../grapesjs-lp-builder/src/index';

const boolSetting = (value, fallback = false) => {
    if (typeof value === 'boolean') {
        return value;
    }

    const normalized = String(value ?? '').trim().toLowerCase();
    if (['1', 'true', 'yes', 'on'].includes(normalized)) {
        return true;
    }
    if (['0', 'false', 'no', 'off'].includes(normalized)) {
        return false;
    }

    return fallback;
};

/**
 * Install LP Builder plugin for current GrapesJS editor instance.
 * @param {import('grapesjs').Editor} editor
 * @param {Record<string, unknown>} settings
 */
export default function installLpBuilderWorkspacePlugin(editor, settings = {}) {
    if (editor.__lpBuilderWorkspacePluginReady) {
        return;
    }

    const enabled = boolSetting(settings.enabled, true);
    if (!enabled) {
        return;
    }

    editor.__lpBuilderWorkspacePluginReady = true;

    gjsLPBuilder(editor, {
        gsap: boolSetting(settings.gsap, true),
        threejs: boolSetting(settings.threejs, true),
        sectionsNavigator: boolSetting(settings.sections_navigator, false),
        gsapVersion: String(settings.gsap_version || '3.12.2'),
        threeVersion: String(settings.three_version || 'r128'),
        debug: boolSetting(settings.debug, false),
    });
}
