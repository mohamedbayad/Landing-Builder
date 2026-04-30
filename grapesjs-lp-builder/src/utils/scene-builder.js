/**
 * Shared Three.js scene runtime for GrapesJS LP Builder.
 * Exposes helpers to build/dispose scenes in the editor iframe and to serialize
 * equivalent helper code into generated animations.js.
 */

const DEFAULT_SCENE_CONFIG = Object.freeze({
    sceneType: 'particles',
    sceneColor: '#5b8cff',
    sceneBg: 'transparent',
    sceneHeight: 400,
    sceneSpeed: 1,
    particleCount: 120,
    overlay: true,
    wireframe: false,
    autoRotate: true,
});

const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

const parseNumber = (value, fallback, min, max) => {
    const parsed = Number.parseFloat(value);
    if (!Number.isFinite(parsed)) {
        return fallback;
    }
    const boundedMin = Number.isFinite(min) ? min : parsed;
    const boundedMax = Number.isFinite(max) ? max : parsed;
    return clamp(parsed, boundedMin, boundedMax);
};

const parseInteger = (value, fallback, min, max) => {
    const parsed = Number.parseInt(value, 10);
    if (!Number.isFinite(parsed)) {
        return fallback;
    }
    const boundedMin = Number.isFinite(min) ? min : parsed;
    const boundedMax = Number.isFinite(max) ? max : parsed;
    return Math.round(clamp(parsed, boundedMin, boundedMax));
};

const toBool = (value, fallback = false) => {
    if (typeof value === 'boolean') {
        return value;
    }
    if (typeof value === 'string') {
        const normalized = value.trim().toLowerCase();
        if (normalized === 'true') return true;
        if (normalized === 'false') return false;
    }
    return fallback;
};

const normalizeHex = (value, fallback) => {
    const raw = String(value || '').trim();
    if (raw === 'transparent') {
        return 'transparent';
    }
    if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw)) {
        return raw;
    }
    return fallback;
};

/**
 * Normalize scene config from element attributes/user input.
 * @param {object} rawConfig
 * @returns {object}
 */
export function normalizeSceneConfig(rawConfig = {}) {
    const sceneType = String(rawConfig.sceneType || rawConfig['data-scene-type'] || DEFAULT_SCENE_CONFIG.sceneType).trim();
    const sceneColor = normalizeHex(rawConfig.sceneColor || rawConfig['data-scene-color'], DEFAULT_SCENE_CONFIG.sceneColor);
    const sceneBg = normalizeHex(rawConfig.sceneBg || rawConfig['data-scene-bg'], DEFAULT_SCENE_CONFIG.sceneBg);
    const sceneHeight = parseInteger(rawConfig.sceneHeight || rawConfig['data-scene-height'], DEFAULT_SCENE_CONFIG.sceneHeight, 100, 1000);
    const sceneSpeed = parseNumber(rawConfig.sceneSpeed || rawConfig['data-scene-speed'], DEFAULT_SCENE_CONFIG.sceneSpeed, 0.1, 3);
    const particleCount = parseInteger(rawConfig.particleCount || rawConfig['data-particle-count'], DEFAULT_SCENE_CONFIG.particleCount, 10, 500);
    const overlay = toBool(rawConfig.overlay ?? rawConfig['data-threejs-overlay'], DEFAULT_SCENE_CONFIG.overlay);
    const wireframe = toBool(rawConfig.wireframe ?? rawConfig['data-wireframe'], DEFAULT_SCENE_CONFIG.wireframe);
    const autoRotate = toBool(rawConfig.autoRotate ?? rawConfig['data-auto-rotate'], DEFAULT_SCENE_CONFIG.autoRotate);

    return {
        sceneType,
        sceneColor,
        sceneBg,
        sceneHeight,
        sceneSpeed,
        particleCount,
        overlay,
        wireframe,
        autoRotate,
    };
}

const disposeMaterial = (material) => {
    if (!material) {
        return;
    }
    if (Array.isArray(material)) {
        material.forEach((item) => disposeMaterial(item));
        return;
    }
    if (typeof material.dispose === 'function') {
        material.dispose();
    }
};

const clearContainer = (container) => {
    while (container.firstChild) {
        container.removeChild(container.firstChild);
    }
};

const createFallback = (container, sceneType, message) => {
    clearContainer(container);
    const fallback = container.ownerDocument.createElement('div');
    fallback.setAttribute('data-three-fallback', 'true');
    fallback.style.cssText = [
        'display:flex',
        'align-items:center',
        'justify-content:center',
        'height:100%',
        'min-height:180px',
        'border:1px dashed rgba(148,163,184,.7)',
        'background:rgba(15,23,42,.06)',
        'color:#334155',
        'font-size:12px',
        'font-family:Arial,sans-serif',
        'padding:12px',
        'text-align:center',
    ].join(';');
    fallback.textContent = message || 'Scene unavailable: ' + sceneType;
    container.appendChild(fallback);
};

/**
 * Build a Three.js scene in a target element.
 * @param {HTMLElement} container
 * @param {object} rawConfig
 * @param {object} runtime
 * @returns {object|null}
 */
export function buildScene(container, rawConfig = {}, runtime = {}) {
    if (!container || typeof container !== 'object') {
        return null;
    }

    const win = runtime.win || container.ownerDocument?.defaultView || window;
    const THREE = runtime.THREE || win.THREE;
    const config = normalizeSceneConfig(rawConfig);

    container.style.minHeight = config.sceneHeight + 'px';
    container.style.height = config.sceneHeight + 'px';
    if (config.overlay && win.getComputedStyle(container).position === 'static') {
        container.style.position = 'relative';
    }

    if (!THREE) {
        createFallback(container, config.sceneType, 'Three.js not available (' + config.sceneType + ')');
        return null;
    }

    clearContainer(container);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1000);
    camera.position.z = 5;

    const renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: config.sceneBg === 'transparent',
    });

    const pixelRatio = Math.min(win.devicePixelRatio || 1, 2);
    renderer.setPixelRatio(pixelRatio);

    if (config.sceneBg === 'transparent') {
        renderer.setClearColor(0x000000, 0);
    } else {
        renderer.setClearColor(config.sceneBg, 1);
    }

    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';
    renderer.domElement.style.display = 'block';
    if (config.overlay) {
        renderer.domElement.style.position = 'absolute';
        renderer.domElement.style.top = '0';
        renderer.domElement.style.left = '0';
        renderer.domElement.style.zIndex = '0';
    }

    container.appendChild(renderer.domElement);

    let mesh = null;
    let auxMeshes = [];
    let particlePoints = null;
    let waveGeometry = null;
    let waveBasePositions = null;

    const baseMaterial = new THREE.MeshNormalMaterial({ wireframe: config.wireframe });

    try {
        switch (config.sceneType) {
        case 'rotating-cube': {
            mesh = new THREE.Mesh(new THREE.BoxGeometry(2, 2, 2), baseMaterial);
            scene.add(mesh);
            break;
        }
        case 'sphere': {
            mesh = new THREE.Mesh(new THREE.SphereGeometry(1.4, 48, 48), baseMaterial);
            scene.add(mesh);
            break;
        }
        case 'wave': {
            waveGeometry = new THREE.PlaneGeometry(8, 5, 48, 32);
            waveBasePositions = Float32Array.from(waveGeometry.attributes.position.array);
            const mat = new THREE.MeshNormalMaterial({ wireframe: config.wireframe, side: THREE.DoubleSide });
            mesh = new THREE.Mesh(waveGeometry, mat);
            mesh.rotation.x = -Math.PI / 3;
            scene.add(mesh);
            break;
        }
        case 'globe': {
            const mat = new THREE.MeshNormalMaterial({ wireframe: true });
            mesh = new THREE.Mesh(new THREE.SphereGeometry(1.5, 48, 48), mat);
            scene.add(mesh);
            break;
        }
        case 'rings': {
            const torusA = new THREE.Mesh(new THREE.TorusGeometry(1.2, 0.08, 20, 100), baseMaterial.clone());
            const torusB = new THREE.Mesh(new THREE.TorusGeometry(1.8, 0.07, 20, 100), baseMaterial.clone());
            const torusC = new THREE.Mesh(new THREE.TorusGeometry(2.4, 0.06, 20, 100), baseMaterial.clone());
            torusB.rotation.x = Math.PI / 3;
            torusC.rotation.y = Math.PI / 4;
            auxMeshes = [torusA, torusB, torusC];
            auxMeshes.forEach((item) => scene.add(item));
            break;
        }
        case 'particles':
        default: {
            const pointsGeometry = new THREE.BufferGeometry();
            const positions = new Float32Array(config.particleCount * 3);
            for (let i = 0; i < config.particleCount; i += 1) {
                const base = i * 3;
                positions[base] = (Math.random() - 0.5) * 8;
                positions[base + 1] = (Math.random() - 0.5) * 8;
                positions[base + 2] = (Math.random() - 0.5) * 8;
            }
            pointsGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            const pointsMaterial = new THREE.PointsMaterial({
                color: config.sceneColor,
                size: 0.06,
                transparent: true,
                opacity: 0.92,
            });
            particlePoints = new THREE.Points(pointsGeometry, pointsMaterial);
            scene.add(particlePoints);
            break;
        }
        }
    } catch (_error) {
        createFallback(container, config.sceneType, 'Failed to render scene: ' + config.sceneType);
        return null;
    }

    const resolveSize = () => {
        const width = Math.max(container.clientWidth || 1, 1);
        const height = Math.max(container.clientHeight || config.sceneHeight || 1, 1);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height, false);
    };

    resolveSize();

    let rafId = 0;
    const animate = () => {
        const parentSection = container.closest?.('[data-gjs-type]');
        if (container.__lpDynamicCollapsed || parentSection?.__lpDynamicCollapsed) {
            rafId = win.requestAnimationFrame(animate);
            return;
        }

        const time = win.performance?.now ? win.performance.now() * 0.001 : Date.now() * 0.001;
        const speed = config.sceneSpeed;

        if (particlePoints) {
            particlePoints.rotation.y += 0.0025 * speed;
            particlePoints.rotation.x += 0.0015 * speed;
        }

        if (mesh && config.autoRotate) {
            mesh.rotation.y += 0.01 * speed;
            mesh.rotation.x += 0.006 * speed;
        }

        if (config.sceneType === 'wave' && waveGeometry && waveBasePositions) {
            const positions = waveGeometry.attributes.position.array;
            for (let i = 0; i < positions.length; i += 3) {
                const baseX = waveBasePositions[i];
                const baseY = waveBasePositions[i + 1];
                positions[i + 2] = Math.sin((baseX + time * 2 * speed)) * 0.24 + Math.cos((baseY + time * 1.6 * speed)) * 0.12;
            }
            waveGeometry.attributes.position.needsUpdate = true;
        }

        if (auxMeshes.length > 0 && config.autoRotate) {
            auxMeshes.forEach((item, index) => {
                item.rotation.x += (0.004 + index * 0.002) * speed;
                item.rotation.y += (0.006 + index * 0.002) * speed;
            });
        }

        renderer.render(scene, camera);
        rafId = win.requestAnimationFrame(animate);
    };

    rafId = win.requestAnimationFrame(animate);

    const resizeObserver = new win.ResizeObserver(() => {
        resolveSize();
    });
    resizeObserver.observe(container);

    const onWindowResize = () => {
        resolveSize();
    };
    win.addEventListener('resize', onWindowResize);

    const dispose = () => {
        if (rafId) {
            win.cancelAnimationFrame(rafId);
        }
        try {
            resizeObserver.disconnect();
        } catch (_error) {
            // ignore disconnect errors
        }
        win.removeEventListener('resize', onWindowResize);

        scene.traverse((node) => {
            if (node?.geometry && typeof node.geometry.dispose === 'function') {
                node.geometry.dispose();
            }
            if (node?.material) {
                disposeMaterial(node.material);
            }
        });

        if (renderer && typeof renderer.dispose === 'function') {
            renderer.dispose();
        }

        if (renderer?.domElement?.parentNode === container) {
            container.removeChild(renderer.domElement);
        }
    };

    return {
        config,
        scene,
        camera,
        renderer,
        dispose,
    };
}

/**
 * Dispose scene instance created by buildScene.
 * @param {object|null} instance
 */
export function disposeScene(instance) {
    if (!instance) {
        return;
    }
    if (typeof instance.dispose === 'function') {
        instance.dispose();
        return;
    }

    try {
        if (instance.renderer?.dispose) {
            instance.renderer.dispose();
        }
    } catch (_error) {
        // ignore
    }
}

/**
 * Serialize helper functions used by generated animations.js.
 * @returns {string}
 */
export function serializeSceneBuilderHelpers() {
    const source = [
        'function __lpClamp(value, min, max) { return Math.min(max, Math.max(min, value)); }',
        'function __lpToBool(value, fallback) {',
        '  if (typeof value === "boolean") return value;',
        '  if (typeof value === "string") {',
        '    var normalized = value.trim().toLowerCase();',
        '    if (normalized === "true") return true;',
        '    if (normalized === "false") return false;',
        '  }',
        '  return !!fallback;',
        '}',
        'function __lpNormalizeSceneConfig(raw) {',
        '  var cfg = raw || {};',
        '  var parsedHeight = parseInt(cfg.sceneHeight || cfg["data-scene-height"] || "400", 10);',
        '  var parsedSpeed = parseFloat(cfg.sceneSpeed || cfg["data-scene-speed"] || "1");',
        '  var parsedCount = parseInt(cfg.particleCount || cfg["data-particle-count"] || "120", 10);',
        '  return {',
        '    sceneType: String(cfg.sceneType || cfg["data-scene-type"] || "particles"),',
        '    sceneColor: String(cfg.sceneColor || cfg["data-scene-color"] || "#5b8cff"),',
        '    sceneBg: String(cfg.sceneBg || cfg["data-scene-bg"] || "transparent"),',
        '    sceneHeight: __lpClamp(isFinite(parsedHeight) ? parsedHeight : 400, 100, 1000),',
        '    sceneSpeed: __lpClamp(isFinite(parsedSpeed) ? parsedSpeed : 1, 0.1, 3),',
        '    particleCount: __lpClamp(isFinite(parsedCount) ? parsedCount : 120, 10, 500),',
        '    overlay: __lpToBool(cfg.overlay != null ? cfg.overlay : cfg["data-threejs-overlay"], true),',
        '    wireframe: __lpToBool(cfg.wireframe != null ? cfg.wireframe : cfg["data-wireframe"], false),',
        '    autoRotate: __lpToBool(cfg.autoRotate != null ? cfg.autoRotate : cfg["data-auto-rotate"], true)',
        '  };',
        '}',
        'function buildScene(container, rawConfig) {',
        '  if (!container) return null;',
        '  var win = container.ownerDocument && container.ownerDocument.defaultView ? container.ownerDocument.defaultView : window;',
        '  var THREE = win.THREE;',
        '  var config = __lpNormalizeSceneConfig(rawConfig || {});',
        '  container.style.minHeight = config.sceneHeight + "px";',
        '  container.style.height = config.sceneHeight + "px";',
        '  if (config.overlay && win.getComputedStyle(container).position === "static") container.style.position = "relative";',
        '  if (!THREE) return null;',
        '  while (container.firstChild) container.removeChild(container.firstChild);',
        '  var scene = new THREE.Scene();',
        '  var camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1000);',
        '  camera.position.z = 5;',
        '  var renderer = new THREE.WebGLRenderer({ antialias: true, alpha: config.sceneBg === "transparent" });',
        '  renderer.setPixelRatio(Math.min(win.devicePixelRatio || 1, 2));',
        '  if (config.sceneBg === "transparent") renderer.setClearColor(0x000000, 0); else renderer.setClearColor(config.sceneBg, 1);',
        '  renderer.domElement.style.width = "100%";',
        '  renderer.domElement.style.height = "100%";',
        '  renderer.domElement.style.display = "block";',
        '  if (config.overlay) { renderer.domElement.style.position = "absolute"; renderer.domElement.style.top = "0"; renderer.domElement.style.left = "0"; renderer.domElement.style.zIndex = "0"; }',
        '  container.appendChild(renderer.domElement);',
        '  var mesh = null;',
        '  var auxMeshes = [];',
        '  var particlePoints = null;',
        '  var waveGeometry = null;',
        '  var waveBasePositions = null;',
        '  var baseMaterial = new THREE.MeshNormalMaterial({ wireframe: config.wireframe });',
        '  if (config.sceneType === "rotating-cube") {',
        '    mesh = new THREE.Mesh(new THREE.BoxGeometry(2, 2, 2), baseMaterial); scene.add(mesh);',
        '  } else if (config.sceneType === "sphere") {',
        '    mesh = new THREE.Mesh(new THREE.SphereGeometry(1.4, 48, 48), baseMaterial); scene.add(mesh);',
        '  } else if (config.sceneType === "wave") {',
        '    waveGeometry = new THREE.PlaneGeometry(8, 5, 48, 32);',
        '    waveBasePositions = Float32Array.from(waveGeometry.attributes.position.array);',
        '    mesh = new THREE.Mesh(waveGeometry, new THREE.MeshNormalMaterial({ wireframe: config.wireframe, side: THREE.DoubleSide }));',
        '    mesh.rotation.x = -Math.PI / 3; scene.add(mesh);',
        '  } else if (config.sceneType === "globe") {',
        '    mesh = new THREE.Mesh(new THREE.SphereGeometry(1.5, 48, 48), new THREE.MeshNormalMaterial({ wireframe: true })); scene.add(mesh);',
        '  } else if (config.sceneType === "rings") {',
        '    var torusA = new THREE.Mesh(new THREE.TorusGeometry(1.2, 0.08, 20, 100), baseMaterial.clone());',
        '    var torusB = new THREE.Mesh(new THREE.TorusGeometry(1.8, 0.07, 20, 100), baseMaterial.clone());',
        '    var torusC = new THREE.Mesh(new THREE.TorusGeometry(2.4, 0.06, 20, 100), baseMaterial.clone());',
        '    torusB.rotation.x = Math.PI / 3; torusC.rotation.y = Math.PI / 4;',
        '    auxMeshes = [torusA, torusB, torusC];',
        '    auxMeshes.forEach(function(item){ scene.add(item); });',
        '  } else {',
        '    var pointsGeometry = new THREE.BufferGeometry();',
        '    var positions = new Float32Array(config.particleCount * 3);',
        '    for (var i = 0; i < config.particleCount; i += 1) {',
        '      var base = i * 3;',
        '      positions[base] = (Math.random() - 0.5) * 8;',
        '      positions[base + 1] = (Math.random() - 0.5) * 8;',
        '      positions[base + 2] = (Math.random() - 0.5) * 8;',
        '    }',
        '    pointsGeometry.setAttribute("position", new THREE.BufferAttribute(positions, 3));',
        '    particlePoints = new THREE.Points(pointsGeometry, new THREE.PointsMaterial({ color: config.sceneColor, size: 0.06, transparent: true, opacity: 0.92 }));',
        '    scene.add(particlePoints);',
        '  }',
        '  function resolveSize() {',
        '    var width = Math.max(container.clientWidth || 1, 1);',
        '    var height = Math.max(container.clientHeight || config.sceneHeight || 1, 1);',
        '    camera.aspect = width / height; camera.updateProjectionMatrix(); renderer.setSize(width, height, false);',
        '  }',
        '  resolveSize();',
        '  var rafId = 0;',
        '  function animate() {',
        '    var time = win.performance && win.performance.now ? win.performance.now() * 0.001 : Date.now() * 0.001;',
        '    var speed = config.sceneSpeed;',
        '    if (particlePoints) { particlePoints.rotation.y += 0.0025 * speed; particlePoints.rotation.x += 0.0015 * speed; }',
        '    if (mesh && config.autoRotate) { mesh.rotation.y += 0.01 * speed; mesh.rotation.x += 0.006 * speed; }',
        '    if (config.sceneType === "wave" && waveGeometry && waveBasePositions) {',
        '      var arr = waveGeometry.attributes.position.array;',
        '      for (var j = 0; j < arr.length; j += 3) {',
        '        var bx = waveBasePositions[j];',
        '        var by = waveBasePositions[j + 1];',
        '        arr[j + 2] = Math.sin((bx + time * 2 * speed)) * 0.24 + Math.cos((by + time * 1.6 * speed)) * 0.12;',
        '      }',
        '      waveGeometry.attributes.position.needsUpdate = true;',
        '    }',
        '    if (auxMeshes.length && config.autoRotate) {',
        '      auxMeshes.forEach(function(item, index){ item.rotation.x += (0.004 + index * 0.002) * speed; item.rotation.y += (0.006 + index * 0.002) * speed; });',
        '    }',
        '    renderer.render(scene, camera);',
        '    rafId = win.requestAnimationFrame(animate);',
        '  }',
        '  rafId = win.requestAnimationFrame(animate);',
        '  var resizeObserver = new win.ResizeObserver(resolveSize); resizeObserver.observe(container);',
        '  win.addEventListener("resize", resolveSize);',
        '  return {',
        '    scene: scene, camera: camera, renderer: renderer,',
        '    dispose: function() {',
        '      if (rafId) win.cancelAnimationFrame(rafId);',
        '      try { resizeObserver.disconnect(); } catch (error) {}',
        '      win.removeEventListener("resize", resolveSize);',
        '      scene.traverse(function(node){',
        '        if (node && node.geometry && node.geometry.dispose) node.geometry.dispose();',
        '        if (node && node.material) {',
        '          if (Array.isArray(node.material)) node.material.forEach(function(m){ if (m && m.dispose) m.dispose(); });',
        '          else if (node.material.dispose) node.material.dispose();',
        '        }',
        '      });',
        '      if (renderer && renderer.dispose) renderer.dispose();',
        '      if (renderer && renderer.domElement && renderer.domElement.parentNode === container) container.removeChild(renderer.domElement);',
        '    }',
        '  };',
        '}',
    ];

    return source.join('\n');
}
