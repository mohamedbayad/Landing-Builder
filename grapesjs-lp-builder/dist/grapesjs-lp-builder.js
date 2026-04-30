(() => {
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __esm = (fn, res) => function __init() {
    return fn && (res = (0, fn[__getOwnPropNames(fn)[0]])(fn = 0)), res;
  };
  var __commonJS = (cb, mod) => function __require() {
    return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
  };

  // src/utils/badge-injector.js
  function injectLpBuilderCanvasStyles(doc) {
    if (!(doc == null ? void 0 : doc.head)) {
      return;
    }
    let style = doc.getElementById(LP_BUILDER_STYLE_ID);
    if (!style) {
      style = doc.createElement("style");
      style.id = LP_BUILDER_STYLE_ID;
      doc.head.appendChild(style);
    }
    style.textContent = canvasStyles;
  }
  function isDynamicSectionCollapsed(el) {
    return !!(el == null ? void 0 : el.__lpDynamicCollapsed);
  }
  function upsertSectionBadge(model, el, editor) {
    if (!el) {
      return;
    }
    const type = resolveSectionType(model, el);
    if (!SECTION_TYPES.has(type)) {
      return;
    }
    el.classList.add("lp-section");
    if (!el.style.minHeight) {
      el.style.minHeight = "80px";
    }
    if (!el.style.position || el.style.position === "static") {
      el.style.position = "relative";
    }
    let badge = el.querySelector(":scope > [data-lp-badge]");
    if (!badge) {
      badge = el.ownerDocument.createElement("div");
      badge.setAttribute("data-lp-badge", "true");
      badge.setAttribute("data-gjs-selectable", "false");
      badge.setAttribute("contenteditable", "false");
      el.insertBefore(badge, el.firstChild);
    }
    const content = resolveBadgeContent(model, el);
    const isDynamic = DYNAMIC_SECTION_TYPES.has(type);
    badge.className = `lp-section-badge lp-badge--${type}`;
    badge.innerHTML = [
      `<span class="lp-badge-icon">${content.icon}</span>`,
      `<span class="lp-badge-label">${escapeHtml(content.label)}</span>`,
      content.meta ? `<span class="lp-badge-meta">${escapeHtml(content.meta)}</span>` : "",
      isDynamic && el.__lpTemporaryExpanded ? '<button type="button" data-lp-collapse>Collapse</button>' : "",
      isDynamic ? `<button type="button" data-lp-toggle aria-label="Toggle section">${el.__lpDynamicExpanded ? EXPANDED_ICON : COLLAPSED_ICON}</button>` : ""
    ].join("");
    const toggle = badge.querySelector("[data-lp-toggle]");
    if (toggle && editor) {
      toggle.addEventListener("click", (event) => {
        event.stopPropagation();
        if (el.__lpDynamicExpanded) {
          collapseDynamicSection(model, el, editor);
        } else {
          expandDynamicSection(model, el, editor);
        }
      });
    }
    const collapse = badge.querySelector("[data-lp-collapse]");
    if (collapse && editor) {
      collapse.addEventListener("click", (event) => {
        event.stopPropagation();
        collapseDynamicSection(model, el, editor);
      });
    }
  }
  function collapseDynamicSection(model, el, editor) {
    var _a;
    if (!el) {
      return;
    }
    const type = resolveSectionType(model, el);
    if (!DYNAMIC_SECTION_TYPES.has(type)) {
      upsertSectionBadge(model, el, editor);
      return;
    }
    clearTimeout(el.__lpReCollapseTimer);
    el.__lpDynamicCollapsed = true;
    el.__lpDynamicExpanded = false;
    el.__lpTemporaryExpanded = false;
    el.__lpExpandedStyleSnapshot = el.__lpExpandedStyleSnapshot || el.getAttribute("style") || "";
    el.classList.add(getDynamicClass(type));
    el.style.cssText += getCollapsedStyle(type);
    el.style.outline = "";
    hideRealChildren(el);
    upsertSectionBadge(model, el, editor);
    upsertOverlay(model, el, editor);
    (_a = editor == null ? void 0 : editor.trigger) == null ? void 0 : _a.call(editor, "lp:section:collapsed", { cid: model.cid });
  }
  function expandDynamicSection(model, el, editor, options = {}) {
    var _a;
    if (!el) {
      return;
    }
    const type = resolveSectionType(model, el);
    if (!DYNAMIC_SECTION_TYPES.has(type)) {
      return;
    }
    clearTimeout(el.__lpReCollapseTimer);
    el.__lpDynamicCollapsed = false;
    el.__lpDynamicExpanded = true;
    el.__lpTemporaryExpanded = !!options.temporary;
    el.classList.add(getDynamicClass(type));
    el.style.minHeight = "80px";
    el.style.maxHeight = "";
    el.style.height = "";
    el.style.overflow = "";
    el.style.position = "relative";
    el.style.background = "";
    el.style.border = "";
    el.style.borderRadius = "";
    el.style.outline = options.temporary ? "2px solid #7F77DD" : "";
    showRealChildren(el);
    const overlay = el.querySelector(":scope > [data-lp-overlay]");
    if (overlay) {
      overlay.style.display = "none";
    }
    upsertSectionBadge(model, el, editor);
    (_a = editor == null ? void 0 : editor.trigger) == null ? void 0 : _a.call(editor, "lp:section:expanded", { cid: model.cid, temporary: !!options.temporary });
  }
  function scheduleDynamicReCollapse(model, el, editor, delayMs = 2e3) {
    if (!el) {
      return;
    }
    clearTimeout(el.__lpReCollapseTimer);
    el.__lpReCollapseTimer = setTimeout(() => {
      collapseDynamicSection(model, el, editor);
    }, delayMs);
  }
  function updateCanvasSectionSelection(editor, component) {
    var _a, _b, _c, _d, _e, _f;
    const doc = (_b = (_a = editor.Canvas).getDocument) == null ? void 0 : _b.call(_a);
    if (!doc) {
      return;
    }
    doc.querySelectorAll(".lp-section-selected").forEach((node) => {
      node.classList.remove("lp-section-selected");
    });
    const attrs = ((_c = component == null ? void 0 : component.getAttributes) == null ? void 0 : _c.call(component)) || {};
    const type = String(attrs["data-gjs-type"] || ((_d = component == null ? void 0 : component.get) == null ? void 0 : _d.call(component, "type")) || "").trim();
    if (!SECTION_TYPES.has(type)) {
      return;
    }
    (_f = (_e = component.getEl) == null ? void 0 : _e.call(component)) == null ? void 0 : _f.classList.add("lp-section-selected");
  }
  var LP_BUILDER_STYLE_ID, BADGE_ICONS, SECTION_TYPES, DYNAMIC_SECTION_TYPES, COLLAPSED_ICON, EXPANDED_ICON, escapeHtml, canvasStyles, resolveSectionType, resolveSectionLabel, resolveBadgeContent, getDynamicClass, getCollapsedStyle, rememberChildStyles, shouldSkipDynamicChild, hideRealChildren, showRealChildren, upsertOverlay;
  var init_badge_injector = __esm({
    "src/utils/badge-injector.js"() {
      LP_BUILDER_STYLE_ID = "lp-builder-styles";
      BADGE_ICONS = Object.freeze({
        gsap: '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2 11.5C5.4 11.5 5.1 4 8 4s2.6 7.5 6 7.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
        three: '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 1.8 13.4 5v6L8 14.2 2.6 11V5L8 1.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>',
        standard: '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 4h10M3 8h10M3 12h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'
      });
      SECTION_TYPES = /* @__PURE__ */ new Set(["standard-section", "gsap-animated", "threejs-scene"]);
      DYNAMIC_SECTION_TYPES = /* @__PURE__ */ new Set(["gsap-animated", "threejs-scene"]);
      COLLAPSED_ICON = "\u2922";
      EXPANDED_ICON = "\u2921";
      escapeHtml = (value) => String(value != null ? value : "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
      canvasStyles = `
[data-lp-badge] {
  position: absolute;
  top: 8px;
  left: 8px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 8px 3px 5px;
  border-radius: 4px;
  font-family: sans-serif;
  font-size: 11px;
  font-weight: 500;
  pointer-events: none;
  z-index: 9999;
  line-height: 1;
}
.lp-badge--gsap-animated { background: #EEEDFE; color: #534AB7; }
.lp-badge--threejs-scene { background: #E1F5EE; color: #0F6E56; }
.lp-badge--standard-section { background: #F1EFE8; color: #5F5E5A; }
[data-lp-badge] .lp-badge-meta {
  margin-left: 4px;
  opacity: 0.7;
  font-weight: 400;
}
[data-lp-toggle],
[data-lp-collapse] {
  pointer-events: auto;
}
[data-lp-toggle] {
  margin-left: 6px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 11px;
  padding: 0 2px;
  color: inherit;
  opacity: 0.7;
}
[data-lp-collapse] {
  margin-left: 6px;
  border: 0;
  border-radius: 3px;
  background: rgba(255,255,255,0.7);
  color: inherit;
  cursor: pointer;
  font-size: 10px;
  padding: 1px 5px;
}
[data-lp-overlay] {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  z-index: 100;
  transition: background 0.15s;
}
[data-lp-overlay]:hover {
  background: rgba(255,255,255,0.25);
}
.lp-overlay-icon {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.lp-gsap [data-lp-overlay] .lp-overlay-icon { background: #EEEDFE; color: #534AB7; }
.lp-3d [data-lp-overlay] .lp-overlay-icon { background: #E1F5EE; color: #0F6E56; }
.lp-overlay-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}
.lp-overlay-title {
  font-size: 12px;
  font-weight: 600;
  font-family: sans-serif;
}
.lp-gsap [data-lp-overlay] .lp-overlay-title { color: #534AB7; }
.lp-3d [data-lp-overlay] .lp-overlay-title { color: #0F6E56; }
.lp-overlay-hint {
  font-size: 10px;
  font-family: sans-serif;
  color: #888780;
}
.lp-overlay-arrow {
  font-size: 14px;
  color: #888780;
  font-family: sans-serif;
}
.lp-section {
  min-height: 80px;
  position: relative;
}
.lp-section-selected {
  outline: 2px solid #7F77DD !important;
  outline-offset: 2px;
}
`;
      resolveSectionType = (model, el) => {
        var _a, _b, _c;
        const attrs = ((_a = model == null ? void 0 : model.getAttributes) == null ? void 0 : _a.call(model)) || {};
        return String(attrs["data-gjs-type"] || ((_b = el == null ? void 0 : el.getAttribute) == null ? void 0 : _b.call(el, "data-gjs-type")) || ((_c = model == null ? void 0 : model.get) == null ? void 0 : _c.call(model, "type")) || "").trim();
      };
      resolveSectionLabel = (model, el) => {
        var _a, _b;
        const attrs = ((_a = model == null ? void 0 : model.getAttributes) == null ? void 0 : _a.call(model)) || {};
        return String(attrs["data-label"] || attrs.id || ((_b = el == null ? void 0 : el.getAttribute) == null ? void 0 : _b.call(el, "data-label")) || (el == null ? void 0 : el.id) || "Section").trim();
      };
      resolveBadgeContent = (model, el) => {
        var _a, _b, _c, _d, _e, _f;
        const attrs = ((_a = model == null ? void 0 : model.getAttributes) == null ? void 0 : _a.call(model)) || {};
        const type = resolveSectionType(model, el);
        if (type === "gsap-animated") {
          const animation = attrs["data-gsap-animation"] || ((_b = el == null ? void 0 : el.getAttribute) == null ? void 0 : _b.call(el, "data-gsap-animation")) || "fadeInUp";
          const trigger = attrs["data-gsap-trigger"] || ((_c = el == null ? void 0 : el.getAttribute) == null ? void 0 : _c.call(el, "data-gsap-trigger")) || "scroll";
          const duration = attrs["data-gsap-duration"] || ((_d = el == null ? void 0 : el.getAttribute) == null ? void 0 : _d.call(el, "data-gsap-duration")) || "1";
          return {
            icon: BADGE_ICONS.gsap,
            label: "GSAP Animation",
            meta: `${animation} - ${trigger} - ${duration}s`
          };
        }
        if (type === "threejs-scene") {
          const sceneType = attrs["data-scene-type"] || ((_e = el == null ? void 0 : el.getAttribute) == null ? void 0 : _e.call(el, "data-scene-type")) || "particles";
          const height = attrs["data-scene-height"] || ((_f = el == null ? void 0 : el.getAttribute) == null ? void 0 : _f.call(el, "data-scene-height")) || "400";
          return {
            icon: BADGE_ICONS.three,
            label: "3D Scene",
            meta: `${sceneType} - height ${height}px`
          };
        }
        return {
          icon: BADGE_ICONS.standard,
          label: resolveSectionLabel(model, el),
          meta: ""
        };
      };
      getDynamicClass = (type) => type === "gsap-animated" ? "lp-gsap" : "lp-3d";
      getCollapsedStyle = (type) => {
        const common = [
          "min-height:64px !important",
          "max-height:64px !important",
          "overflow:hidden !important",
          "position:relative !important",
          "border-radius:6px !important"
        ];
        if (type === "gsap-animated") {
          common.push(
            "background:repeating-linear-gradient(-45deg,#EEEDFE,#EEEDFE 4px,#ffffff 4px,#ffffff 14px) !important",
            "border:1.5px solid #AFA9EC !important"
          );
        } else {
          common.push(
            "background:repeating-linear-gradient(-45deg,#E1F5EE,#E1F5EE 4px,#ffffff 4px,#ffffff 14px) !important",
            "border:1.5px solid #5DCAA5 !important"
          );
        }
        return common.join(";") + ";";
      };
      rememberChildStyles = (child) => {
        if (!child.__lpDynamicOriginalStyle) {
          child.__lpDynamicOriginalStyle = {
            visibility: child.style.visibility || "",
            position: child.style.position || "",
            pointerEvents: child.style.pointerEvents || ""
          };
        }
      };
      shouldSkipDynamicChild = (child) => {
        var _a, _b;
        return ((_a = child.hasAttribute) == null ? void 0 : _a.call(child, "data-lp-badge")) || ((_b = child.hasAttribute) == null ? void 0 : _b.call(child, "data-lp-overlay"));
      };
      hideRealChildren = (el) => {
        Array.from(el.children || []).forEach((child) => {
          if (shouldSkipDynamicChild(child)) {
            return;
          }
          rememberChildStyles(child);
          child.style.visibility = "hidden";
          child.style.position = "absolute";
          child.style.pointerEvents = "none";
        });
      };
      showRealChildren = (el) => {
        Array.from(el.children || []).forEach((child) => {
          if (shouldSkipDynamicChild(child)) {
            return;
          }
          const original = child.__lpDynamicOriginalStyle || {};
          child.style.visibility = original.visibility || "";
          child.style.position = original.position || "";
          child.style.pointerEvents = original.pointerEvents || "";
        });
      };
      upsertOverlay = (model, el, editor) => {
        const type = resolveSectionType(model, el);
        if (!DYNAMIC_SECTION_TYPES.has(type)) {
          return null;
        }
        let overlay = el.querySelector(":scope > [data-lp-overlay]");
        if (!overlay) {
          overlay = el.ownerDocument.createElement("div");
          overlay.setAttribute("data-lp-overlay", "true");
          overlay.setAttribute("data-gjs-selectable", "false");
          overlay.setAttribute("contenteditable", "false");
        }
        el.appendChild(overlay);
        const isGsap = type === "gsap-animated";
        overlay.innerHTML = `
        <div class="lp-overlay-icon">${isGsap ? BADGE_ICONS.gsap : BADGE_ICONS.three}</div>
        <div class="lp-overlay-text">
            <span class="lp-overlay-title">${escapeHtml(resolveSectionLabel(model, el))}</span>
            <span class="lp-overlay-hint">Click to edit in sidebar</span>
        </div>
        <div class="lp-overlay-arrow">-&gt;</div>
    `;
        if (!overlay.__lpOverlayClickBound) {
          overlay.__lpOverlayClickBound = true;
          overlay.addEventListener("click", (event) => {
            var _a, _b, _c, _d, _e, _f;
            event.stopPropagation();
            (_a = editor == null ? void 0 : editor.select) == null ? void 0 : _a.call(editor, model);
            (_e = (_d = (_c = (_b = editor == null ? void 0 : editor.Panels) == null ? void 0 : _b.getButton) == null ? void 0 : _c.call(_b, "views", "open-tm")) == null ? void 0 : _d.set) == null ? void 0 : _e.call(_d, "active", true);
            (_f = editor == null ? void 0 : editor.trigger) == null ? void 0 : _f.call(editor, "lp:section:focus", { cid: model.cid });
          });
        }
        overlay.style.display = el.__lpDynamicExpanded ? "none" : "flex";
        return overlay;
      };
    }
  });

  // src/components/standard-section.js
  function registerStandardSection(editor) {
    const domComponents = editor.DomComponents;
    domComponents.addType("standard-section", {
      isComponent(el) {
        if (!el || typeof el.getAttribute !== "function") {
          return false;
        }
        if (el.getAttribute("data-gjs-type") === "standard-section") {
          return { type: "standard-section" };
        }
        return false;
      },
      model: {
        defaults: {
          name: "Section",
          draggable: true,
          droppable: true,
          style: { "min-height": "80px", position: "relative" }
        },
        init() {
          const attrs = this.getAttributes() || {};
          const label = String(attrs["data-label"] || "").trim();
          if (label) {
            this.set("name", label);
          }
        }
      },
      view: {
        init() {
          this.listenTo(this.model, "change:attributes", () => {
            upsertSectionBadge(this.model, this.el);
          });
        },
        onRender() {
          upsertSectionBadge(this.model, this.el);
        }
      }
    });
  }
  var init_standard_section = __esm({
    "src/components/standard-section.js"() {
      init_badge_injector();
    }
  });

  // src/components/gsap-animated.js
  function getFromVars(animation) {
    const preset = String(animation || "").trim();
    switch (preset) {
      case "fadeInDown":
        return { opacity: 0, y: -40 };
      case "fadeInLeft":
        return { opacity: 0, x: -40 };
      case "fadeInRight":
        return { opacity: 0, x: 40 };
      case "slideInLeft":
        return { x: -120, opacity: 0 };
      case "slideInRight":
        return { x: 120, opacity: 0 };
      case "zoomIn":
        return { scale: 0.7, opacity: 0 };
      case "zoomOut":
        return { scale: 1.25, opacity: 0 };
      case "bounceIn":
        return { scale: 0.35, opacity: 0 };
      case "flipInX":
        return { rotateX: 90, opacity: 0, transformPerspective: 600 };
      case "flipInY":
        return { rotateY: 90, opacity: 0, transformPerspective: 600 };
      case "rotateIn":
        return { rotate: -15, opacity: 0, transformOrigin: "center center" };
      case "fadeInUp":
      default:
        return { opacity: 0, y: 40 };
    }
  }
  function registerGsapAnimated(editor) {
    const domComponents = editor.DomComponents;
    domComponents.addType("gsap-animated", {
      isComponent(el) {
        if (!el || typeof el.getAttribute !== "function") {
          return false;
        }
        if (el.getAttribute("data-gjs-type") === "gsap-animated") {
          return { type: "gsap-animated" };
        }
        return false;
      },
      model: {
        defaults: {
          name: "GSAP Section",
          draggable: true,
          droppable: true,
          style: { "min-height": "80px", position: "relative" },
          traits: [
            {
              type: "select",
              name: "data-gsap-animation",
              label: "Animation",
              options: optionsFromList(GSAP_ANIMATIONS)
            },
            {
              type: "number",
              name: "data-gsap-duration",
              label: "Duration (s)",
              min: 0.1,
              max: 5,
              step: 0.1
            },
            {
              type: "number",
              name: "data-gsap-delay",
              label: "Delay (s)",
              min: 0,
              max: 3,
              step: 0.1
            },
            {
              type: "select",
              name: "data-gsap-ease",
              label: "Easing",
              options: optionsFromList(GSAP_EASES)
            },
            {
              type: "select",
              name: "data-gsap-trigger",
              label: "Trigger",
              options: optionsFromList(GSAP_TRIGGERS)
            },
            {
              type: "checkbox",
              name: "data-gsap-children",
              label: "Animate children"
            },
            {
              type: "number",
              name: "data-gsap-stagger",
              label: "Stagger (s)",
              min: 0,
              max: 1,
              step: 0.05
            }
          ],
          attributes: {
            "data-gsap-animation": "fadeInUp",
            "data-gsap-duration": "1",
            "data-gsap-delay": "0",
            "data-gsap-ease": "power2.out",
            "data-gsap-trigger": "scroll",
            "data-gsap-children": "false",
            "data-gsap-stagger": "0.1"
          }
        }
      },
      view: {
        init() {
          this.debouncedPreview = debounce(() => this.renderPreview(), 120);
          this.onLpSectionExpanded = ({ cid } = {}) => {
            if (cid === this.model.cid) {
              this.renderPreview();
            }
          };
          editor.on("lp:section:expanded", this.onLpSectionExpanded);
          this.listenTo(this.model, "change:attributes", this.debouncedPreview);
          this.listenTo(this.model, "change:attributes", () => {
            if (isDynamicSectionCollapsed(this.el)) {
              collapseDynamicSection(this.model, this.el, editor);
            } else {
              upsertSectionBadge(this.model, this.el, editor);
            }
          });
        },
        onRender() {
          collapseDynamicSection(this.model, this.el, editor);
          this.renderPreview();
        },
        removed() {
          var _a, _b;
          if (this.onLpSectionExpanded) {
            editor.off("lp:section:expanded", this.onLpSectionExpanded);
          }
          cleanupPreview(this.el, (_b = (_a = this.el) == null ? void 0 : _a.ownerDocument) == null ? void 0 : _b.defaultView);
        },
        renderPreview() {
          var _a;
          const el = this.el;
          if (!el) {
            return;
          }
          upsertSectionBadge(this.model, el, editor);
          if (isDynamicSectionCollapsed(el)) {
            cleanupPreview(el, (_a = el.ownerDocument) == null ? void 0 : _a.defaultView);
            return;
          }
          const didRun = runGsapPreview(this.model, el);
          if (didRun) {
            return;
          }
          const doc = el.ownerDocument;
          if (!doc || el.__lpGsapWaitReadyBound) {
            return;
          }
          el.__lpGsapWaitReadyBound = true;
          const onceReady = () => {
            el.__lpGsapWaitReadyBound = false;
            runGsapPreview(this.model, el);
            doc.removeEventListener("lp:ready", onceReady);
          };
          doc.addEventListener("lp:ready", onceReady, { once: true });
        }
      }
    });
  }
  var GSAP_ANIMATIONS, GSAP_EASES, GSAP_TRIGGERS, clamp, toBool, debounce, optionsFromList, cleanupPreview, runGsapPreview;
  var init_gsap_animated = __esm({
    "src/components/gsap-animated.js"() {
      init_badge_injector();
      GSAP_ANIMATIONS = [
        "fadeInUp",
        "fadeInDown",
        "fadeInLeft",
        "fadeInRight",
        "slideInLeft",
        "slideInRight",
        "zoomIn",
        "zoomOut",
        "bounceIn",
        "flipInX",
        "flipInY",
        "rotateIn"
      ];
      GSAP_EASES = [
        "none",
        "power1.out",
        "power2.out",
        "power3.out",
        "power4.out",
        "back.out(1.7)",
        "elastic.out(1,0.3)",
        "bounce.out",
        "circ.out",
        "expo.out"
      ];
      GSAP_TRIGGERS = ["scroll", "load", "hover", "click"];
      clamp = (value, min, max, fallback) => {
        const parsed = Number.parseFloat(value);
        if (!Number.isFinite(parsed)) {
          return fallback;
        }
        return Math.min(max, Math.max(min, parsed));
      };
      toBool = (value) => String(value).trim().toLowerCase() === "true";
      debounce = (fn, waitMs) => {
        let timer = 0;
        return function debounced(...args) {
          clearTimeout(timer);
          timer = setTimeout(() => fn.apply(this, args), waitMs);
        };
      };
      optionsFromList = (items) => items.map((item) => ({ id: item, name: item }));
      cleanupPreview = (el, win) => {
        if (!el) {
          return;
        }
        if (el.__lpGsapPreviewCleanup) {
          el.__lpGsapPreviewCleanup();
        }
        if (win == null ? void 0 : win.gsap) {
          const targets = [el, ...Array.from(el.children || []).filter((child) => !child.hasAttribute("data-lp-badge") && !child.hasAttribute("data-lp-overlay"))];
          win.gsap.killTweensOf(targets);
          win.gsap.set(targets, { clearProps: "transform,opacity,filter" });
        }
      };
      runGsapPreview = (model, el) => {
        var _a;
        if (!el || !model) {
          return false;
        }
        const win = (_a = el.ownerDocument) == null ? void 0 : _a.defaultView;
        const gsap = win == null ? void 0 : win.gsap;
        if (!gsap) {
          return false;
        }
        cleanupPreview(el, win);
        const attrs = model.getAttributes() || {};
        const animation = attrs["data-gsap-animation"] || "fadeInUp";
        const duration = clamp(attrs["data-gsap-duration"], 0.1, 5, 1);
        const delay = clamp(attrs["data-gsap-delay"], 0, 3, 0);
        const ease = attrs["data-gsap-ease"] || "power2.out";
        const trigger = attrs["data-gsap-trigger"] || "scroll";
        const animateChildren = toBool(attrs["data-gsap-children"]);
        const stagger = clamp(attrs["data-gsap-stagger"], 0, 1, 0.1);
        const editableChildren = Array.from(el.children || []).filter((child) => !child.hasAttribute("data-lp-badge") && !child.hasAttribute("data-lp-overlay"));
        const targets = animateChildren && editableChildren.length > 0 ? editableChildren : el;
        const vars = {
          ...getFromVars(animation),
          duration,
          delay,
          ease
        };
        if (Array.isArray(targets) && targets.length > 1 && animateChildren) {
          vars.stagger = stagger;
        }
        const play = () => {
          if (win == null ? void 0 : win.gsap) {
            win.gsap.killTweensOf(targets);
            win.gsap.from(targets, vars);
          }
        };
        const listeners = [];
        const addListener = (eventName) => {
          const handler = () => play();
          el.addEventListener(eventName, handler);
          listeners.push([eventName, handler]);
        };
        if (trigger === "scroll" && win.ScrollTrigger) {
          gsap.from(targets, {
            ...vars,
            scrollTrigger: {
              trigger: el,
              start: "top 80%",
              toggleActions: "play none none reset"
            }
          });
        } else if (trigger === "hover") {
          addListener("mouseenter");
          play();
        } else if (trigger === "click") {
          addListener("click");
          play();
        } else {
          play();
        }
        el.__lpGsapPreviewCleanup = () => {
          listeners.forEach(([eventName, handler]) => el.removeEventListener(eventName, handler));
        };
        return true;
      };
    }
  });

  // src/utils/scene-builder.js
  function normalizeSceneConfig(rawConfig = {}) {
    var _a, _b, _c;
    const sceneType = String(rawConfig.sceneType || rawConfig["data-scene-type"] || DEFAULT_SCENE_CONFIG.sceneType).trim();
    const sceneColor = normalizeHex(rawConfig.sceneColor || rawConfig["data-scene-color"], DEFAULT_SCENE_CONFIG.sceneColor);
    const sceneBg = normalizeHex(rawConfig.sceneBg || rawConfig["data-scene-bg"], DEFAULT_SCENE_CONFIG.sceneBg);
    const sceneHeight = parseInteger(rawConfig.sceneHeight || rawConfig["data-scene-height"], DEFAULT_SCENE_CONFIG.sceneHeight, 100, 1e3);
    const sceneSpeed = parseNumber(rawConfig.sceneSpeed || rawConfig["data-scene-speed"], DEFAULT_SCENE_CONFIG.sceneSpeed, 0.1, 3);
    const particleCount = parseInteger(rawConfig.particleCount || rawConfig["data-particle-count"], DEFAULT_SCENE_CONFIG.particleCount, 10, 500);
    const overlay = toBool2((_a = rawConfig.overlay) != null ? _a : rawConfig["data-threejs-overlay"], DEFAULT_SCENE_CONFIG.overlay);
    const wireframe = toBool2((_b = rawConfig.wireframe) != null ? _b : rawConfig["data-wireframe"], DEFAULT_SCENE_CONFIG.wireframe);
    const autoRotate = toBool2((_c = rawConfig.autoRotate) != null ? _c : rawConfig["data-auto-rotate"], DEFAULT_SCENE_CONFIG.autoRotate);
    return {
      sceneType,
      sceneColor,
      sceneBg,
      sceneHeight,
      sceneSpeed,
      particleCount,
      overlay,
      wireframe,
      autoRotate
    };
  }
  function buildScene(container, rawConfig = {}, runtime = {}) {
    var _a;
    if (!container || typeof container !== "object") {
      return null;
    }
    const win = runtime.win || ((_a = container.ownerDocument) == null ? void 0 : _a.defaultView) || window;
    const THREE = runtime.THREE || win.THREE;
    const config = normalizeSceneConfig(rawConfig);
    container.style.minHeight = config.sceneHeight + "px";
    container.style.height = config.sceneHeight + "px";
    if (config.overlay && win.getComputedStyle(container).position === "static") {
      container.style.position = "relative";
    }
    if (!THREE) {
      createFallback(container, config.sceneType, "Three.js not available (" + config.sceneType + ")");
      return null;
    }
    clearContainer(container);
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1e3);
    camera.position.z = 5;
    const renderer = new THREE.WebGLRenderer({
      antialias: true,
      alpha: config.sceneBg === "transparent"
    });
    const pixelRatio = Math.min(win.devicePixelRatio || 1, 2);
    renderer.setPixelRatio(pixelRatio);
    if (config.sceneBg === "transparent") {
      renderer.setClearColor(0, 0);
    } else {
      renderer.setClearColor(config.sceneBg, 1);
    }
    renderer.domElement.style.width = "100%";
    renderer.domElement.style.height = "100%";
    renderer.domElement.style.display = "block";
    if (config.overlay) {
      renderer.domElement.style.position = "absolute";
      renderer.domElement.style.top = "0";
      renderer.domElement.style.left = "0";
      renderer.domElement.style.zIndex = "0";
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
        case "rotating-cube": {
          mesh = new THREE.Mesh(new THREE.BoxGeometry(2, 2, 2), baseMaterial);
          scene.add(mesh);
          break;
        }
        case "sphere": {
          mesh = new THREE.Mesh(new THREE.SphereGeometry(1.4, 48, 48), baseMaterial);
          scene.add(mesh);
          break;
        }
        case "wave": {
          waveGeometry = new THREE.PlaneGeometry(8, 5, 48, 32);
          waveBasePositions = Float32Array.from(waveGeometry.attributes.position.array);
          const mat = new THREE.MeshNormalMaterial({ wireframe: config.wireframe, side: THREE.DoubleSide });
          mesh = new THREE.Mesh(waveGeometry, mat);
          mesh.rotation.x = -Math.PI / 3;
          scene.add(mesh);
          break;
        }
        case "globe": {
          const mat = new THREE.MeshNormalMaterial({ wireframe: true });
          mesh = new THREE.Mesh(new THREE.SphereGeometry(1.5, 48, 48), mat);
          scene.add(mesh);
          break;
        }
        case "rings": {
          const torusA = new THREE.Mesh(new THREE.TorusGeometry(1.2, 0.08, 20, 100), baseMaterial.clone());
          const torusB = new THREE.Mesh(new THREE.TorusGeometry(1.8, 0.07, 20, 100), baseMaterial.clone());
          const torusC = new THREE.Mesh(new THREE.TorusGeometry(2.4, 0.06, 20, 100), baseMaterial.clone());
          torusB.rotation.x = Math.PI / 3;
          torusC.rotation.y = Math.PI / 4;
          auxMeshes = [torusA, torusB, torusC];
          auxMeshes.forEach((item) => scene.add(item));
          break;
        }
        case "particles":
        default: {
          const pointsGeometry = new THREE.BufferGeometry();
          const positions = new Float32Array(config.particleCount * 3);
          for (let i = 0; i < config.particleCount; i += 1) {
            const base = i * 3;
            positions[base] = (Math.random() - 0.5) * 8;
            positions[base + 1] = (Math.random() - 0.5) * 8;
            positions[base + 2] = (Math.random() - 0.5) * 8;
          }
          pointsGeometry.setAttribute("position", new THREE.BufferAttribute(positions, 3));
          const pointsMaterial = new THREE.PointsMaterial({
            color: config.sceneColor,
            size: 0.06,
            transparent: true,
            opacity: 0.92
          });
          particlePoints = new THREE.Points(pointsGeometry, pointsMaterial);
          scene.add(particlePoints);
          break;
        }
      }
    } catch (_error) {
      createFallback(container, config.sceneType, "Failed to render scene: " + config.sceneType);
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
      var _a2, _b;
      const parentSection = (_a2 = container.closest) == null ? void 0 : _a2.call(container, "[data-gjs-type]");
      if (container.__lpDynamicCollapsed || (parentSection == null ? void 0 : parentSection.__lpDynamicCollapsed)) {
        rafId = win.requestAnimationFrame(animate);
        return;
      }
      const time = ((_b = win.performance) == null ? void 0 : _b.now) ? win.performance.now() * 1e-3 : Date.now() * 1e-3;
      const speed = config.sceneSpeed;
      if (particlePoints) {
        particlePoints.rotation.y += 25e-4 * speed;
        particlePoints.rotation.x += 15e-4 * speed;
      }
      if (mesh && config.autoRotate) {
        mesh.rotation.y += 0.01 * speed;
        mesh.rotation.x += 6e-3 * speed;
      }
      if (config.sceneType === "wave" && waveGeometry && waveBasePositions) {
        const positions = waveGeometry.attributes.position.array;
        for (let i = 0; i < positions.length; i += 3) {
          const baseX = waveBasePositions[i];
          const baseY = waveBasePositions[i + 1];
          positions[i + 2] = Math.sin(baseX + time * 2 * speed) * 0.24 + Math.cos(baseY + time * 1.6 * speed) * 0.12;
        }
        waveGeometry.attributes.position.needsUpdate = true;
      }
      if (auxMeshes.length > 0 && config.autoRotate) {
        auxMeshes.forEach((item, index) => {
          item.rotation.x += (4e-3 + index * 2e-3) * speed;
          item.rotation.y += (6e-3 + index * 2e-3) * speed;
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
    win.addEventListener("resize", onWindowResize);
    const dispose = () => {
      var _a2;
      if (rafId) {
        win.cancelAnimationFrame(rafId);
      }
      try {
        resizeObserver.disconnect();
      } catch (_error) {
      }
      win.removeEventListener("resize", onWindowResize);
      scene.traverse((node) => {
        if ((node == null ? void 0 : node.geometry) && typeof node.geometry.dispose === "function") {
          node.geometry.dispose();
        }
        if (node == null ? void 0 : node.material) {
          disposeMaterial(node.material);
        }
      });
      if (renderer && typeof renderer.dispose === "function") {
        renderer.dispose();
      }
      if (((_a2 = renderer == null ? void 0 : renderer.domElement) == null ? void 0 : _a2.parentNode) === container) {
        container.removeChild(renderer.domElement);
      }
    };
    return {
      config,
      scene,
      camera,
      renderer,
      dispose
    };
  }
  function disposeScene(instance) {
    var _a;
    if (!instance) {
      return;
    }
    if (typeof instance.dispose === "function") {
      instance.dispose();
      return;
    }
    try {
      if ((_a = instance.renderer) == null ? void 0 : _a.dispose) {
        instance.renderer.dispose();
      }
    } catch (_error) {
    }
  }
  function serializeSceneBuilderHelpers() {
    const source = [
      "function __lpClamp(value, min, max) { return Math.min(max, Math.max(min, value)); }",
      "function __lpToBool(value, fallback) {",
      '  if (typeof value === "boolean") return value;',
      '  if (typeof value === "string") {',
      "    var normalized = value.trim().toLowerCase();",
      '    if (normalized === "true") return true;',
      '    if (normalized === "false") return false;',
      "  }",
      "  return !!fallback;",
      "}",
      "function __lpNormalizeSceneConfig(raw) {",
      "  var cfg = raw || {};",
      '  var parsedHeight = parseInt(cfg.sceneHeight || cfg["data-scene-height"] || "400", 10);',
      '  var parsedSpeed = parseFloat(cfg.sceneSpeed || cfg["data-scene-speed"] || "1");',
      '  var parsedCount = parseInt(cfg.particleCount || cfg["data-particle-count"] || "120", 10);',
      "  return {",
      '    sceneType: String(cfg.sceneType || cfg["data-scene-type"] || "particles"),',
      '    sceneColor: String(cfg.sceneColor || cfg["data-scene-color"] || "#5b8cff"),',
      '    sceneBg: String(cfg.sceneBg || cfg["data-scene-bg"] || "transparent"),',
      "    sceneHeight: __lpClamp(isFinite(parsedHeight) ? parsedHeight : 400, 100, 1000),",
      "    sceneSpeed: __lpClamp(isFinite(parsedSpeed) ? parsedSpeed : 1, 0.1, 3),",
      "    particleCount: __lpClamp(isFinite(parsedCount) ? parsedCount : 120, 10, 500),",
      '    overlay: __lpToBool(cfg.overlay != null ? cfg.overlay : cfg["data-threejs-overlay"], true),',
      '    wireframe: __lpToBool(cfg.wireframe != null ? cfg.wireframe : cfg["data-wireframe"], false),',
      '    autoRotate: __lpToBool(cfg.autoRotate != null ? cfg.autoRotate : cfg["data-auto-rotate"], true)',
      "  };",
      "}",
      "function buildScene(container, rawConfig) {",
      "  if (!container) return null;",
      "  var win = container.ownerDocument && container.ownerDocument.defaultView ? container.ownerDocument.defaultView : window;",
      "  var THREE = win.THREE;",
      "  var config = __lpNormalizeSceneConfig(rawConfig || {});",
      '  container.style.minHeight = config.sceneHeight + "px";',
      '  container.style.height = config.sceneHeight + "px";',
      '  if (config.overlay && win.getComputedStyle(container).position === "static") container.style.position = "relative";',
      "  if (!THREE) return null;",
      "  while (container.firstChild) container.removeChild(container.firstChild);",
      "  var scene = new THREE.Scene();",
      "  var camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1000);",
      "  camera.position.z = 5;",
      '  var renderer = new THREE.WebGLRenderer({ antialias: true, alpha: config.sceneBg === "transparent" });',
      "  renderer.setPixelRatio(Math.min(win.devicePixelRatio || 1, 2));",
      '  if (config.sceneBg === "transparent") renderer.setClearColor(0x000000, 0); else renderer.setClearColor(config.sceneBg, 1);',
      '  renderer.domElement.style.width = "100%";',
      '  renderer.domElement.style.height = "100%";',
      '  renderer.domElement.style.display = "block";',
      '  if (config.overlay) { renderer.domElement.style.position = "absolute"; renderer.domElement.style.top = "0"; renderer.domElement.style.left = "0"; renderer.domElement.style.zIndex = "0"; }',
      "  container.appendChild(renderer.domElement);",
      "  var mesh = null;",
      "  var auxMeshes = [];",
      "  var particlePoints = null;",
      "  var waveGeometry = null;",
      "  var waveBasePositions = null;",
      "  var baseMaterial = new THREE.MeshNormalMaterial({ wireframe: config.wireframe });",
      '  if (config.sceneType === "rotating-cube") {',
      "    mesh = new THREE.Mesh(new THREE.BoxGeometry(2, 2, 2), baseMaterial); scene.add(mesh);",
      '  } else if (config.sceneType === "sphere") {',
      "    mesh = new THREE.Mesh(new THREE.SphereGeometry(1.4, 48, 48), baseMaterial); scene.add(mesh);",
      '  } else if (config.sceneType === "wave") {',
      "    waveGeometry = new THREE.PlaneGeometry(8, 5, 48, 32);",
      "    waveBasePositions = Float32Array.from(waveGeometry.attributes.position.array);",
      "    mesh = new THREE.Mesh(waveGeometry, new THREE.MeshNormalMaterial({ wireframe: config.wireframe, side: THREE.DoubleSide }));",
      "    mesh.rotation.x = -Math.PI / 3; scene.add(mesh);",
      '  } else if (config.sceneType === "globe") {',
      "    mesh = new THREE.Mesh(new THREE.SphereGeometry(1.5, 48, 48), new THREE.MeshNormalMaterial({ wireframe: true })); scene.add(mesh);",
      '  } else if (config.sceneType === "rings") {',
      "    var torusA = new THREE.Mesh(new THREE.TorusGeometry(1.2, 0.08, 20, 100), baseMaterial.clone());",
      "    var torusB = new THREE.Mesh(new THREE.TorusGeometry(1.8, 0.07, 20, 100), baseMaterial.clone());",
      "    var torusC = new THREE.Mesh(new THREE.TorusGeometry(2.4, 0.06, 20, 100), baseMaterial.clone());",
      "    torusB.rotation.x = Math.PI / 3; torusC.rotation.y = Math.PI / 4;",
      "    auxMeshes = [torusA, torusB, torusC];",
      "    auxMeshes.forEach(function(item){ scene.add(item); });",
      "  } else {",
      "    var pointsGeometry = new THREE.BufferGeometry();",
      "    var positions = new Float32Array(config.particleCount * 3);",
      "    for (var i = 0; i < config.particleCount; i += 1) {",
      "      var base = i * 3;",
      "      positions[base] = (Math.random() - 0.5) * 8;",
      "      positions[base + 1] = (Math.random() - 0.5) * 8;",
      "      positions[base + 2] = (Math.random() - 0.5) * 8;",
      "    }",
      '    pointsGeometry.setAttribute("position", new THREE.BufferAttribute(positions, 3));',
      "    particlePoints = new THREE.Points(pointsGeometry, new THREE.PointsMaterial({ color: config.sceneColor, size: 0.06, transparent: true, opacity: 0.92 }));",
      "    scene.add(particlePoints);",
      "  }",
      "  function resolveSize() {",
      "    var width = Math.max(container.clientWidth || 1, 1);",
      "    var height = Math.max(container.clientHeight || config.sceneHeight || 1, 1);",
      "    camera.aspect = width / height; camera.updateProjectionMatrix(); renderer.setSize(width, height, false);",
      "  }",
      "  resolveSize();",
      "  var rafId = 0;",
      "  function animate() {",
      "    var time = win.performance && win.performance.now ? win.performance.now() * 0.001 : Date.now() * 0.001;",
      "    var speed = config.sceneSpeed;",
      "    if (particlePoints) { particlePoints.rotation.y += 0.0025 * speed; particlePoints.rotation.x += 0.0015 * speed; }",
      "    if (mesh && config.autoRotate) { mesh.rotation.y += 0.01 * speed; mesh.rotation.x += 0.006 * speed; }",
      '    if (config.sceneType === "wave" && waveGeometry && waveBasePositions) {',
      "      var arr = waveGeometry.attributes.position.array;",
      "      for (var j = 0; j < arr.length; j += 3) {",
      "        var bx = waveBasePositions[j];",
      "        var by = waveBasePositions[j + 1];",
      "        arr[j + 2] = Math.sin((bx + time * 2 * speed)) * 0.24 + Math.cos((by + time * 1.6 * speed)) * 0.12;",
      "      }",
      "      waveGeometry.attributes.position.needsUpdate = true;",
      "    }",
      "    if (auxMeshes.length && config.autoRotate) {",
      "      auxMeshes.forEach(function(item, index){ item.rotation.x += (0.004 + index * 0.002) * speed; item.rotation.y += (0.006 + index * 0.002) * speed; });",
      "    }",
      "    renderer.render(scene, camera);",
      "    rafId = win.requestAnimationFrame(animate);",
      "  }",
      "  rafId = win.requestAnimationFrame(animate);",
      "  var resizeObserver = new win.ResizeObserver(resolveSize); resizeObserver.observe(container);",
      '  win.addEventListener("resize", resolveSize);',
      "  return {",
      "    scene: scene, camera: camera, renderer: renderer,",
      "    dispose: function() {",
      "      if (rafId) win.cancelAnimationFrame(rafId);",
      "      try { resizeObserver.disconnect(); } catch (error) {}",
      '      win.removeEventListener("resize", resolveSize);',
      "      scene.traverse(function(node){",
      "        if (node && node.geometry && node.geometry.dispose) node.geometry.dispose();",
      "        if (node && node.material) {",
      "          if (Array.isArray(node.material)) node.material.forEach(function(m){ if (m && m.dispose) m.dispose(); });",
      "          else if (node.material.dispose) node.material.dispose();",
      "        }",
      "      });",
      "      if (renderer && renderer.dispose) renderer.dispose();",
      "      if (renderer && renderer.domElement && renderer.domElement.parentNode === container) container.removeChild(renderer.domElement);",
      "    }",
      "  };",
      "}"
    ];
    return source.join("\n");
  }
  var DEFAULT_SCENE_CONFIG, clamp2, parseNumber, parseInteger, toBool2, normalizeHex, disposeMaterial, clearContainer, createFallback;
  var init_scene_builder = __esm({
    "src/utils/scene-builder.js"() {
      DEFAULT_SCENE_CONFIG = Object.freeze({
        sceneType: "particles",
        sceneColor: "#5b8cff",
        sceneBg: "transparent",
        sceneHeight: 400,
        sceneSpeed: 1,
        particleCount: 120,
        overlay: true,
        wireframe: false,
        autoRotate: true
      });
      clamp2 = (value, min, max) => Math.min(max, Math.max(min, value));
      parseNumber = (value, fallback, min, max) => {
        const parsed = Number.parseFloat(value);
        if (!Number.isFinite(parsed)) {
          return fallback;
        }
        const boundedMin = Number.isFinite(min) ? min : parsed;
        const boundedMax = Number.isFinite(max) ? max : parsed;
        return clamp2(parsed, boundedMin, boundedMax);
      };
      parseInteger = (value, fallback, min, max) => {
        const parsed = Number.parseInt(value, 10);
        if (!Number.isFinite(parsed)) {
          return fallback;
        }
        const boundedMin = Number.isFinite(min) ? min : parsed;
        const boundedMax = Number.isFinite(max) ? max : parsed;
        return Math.round(clamp2(parsed, boundedMin, boundedMax));
      };
      toBool2 = (value, fallback = false) => {
        if (typeof value === "boolean") {
          return value;
        }
        if (typeof value === "string") {
          const normalized = value.trim().toLowerCase();
          if (normalized === "true") return true;
          if (normalized === "false") return false;
        }
        return fallback;
      };
      normalizeHex = (value, fallback) => {
        const raw = String(value || "").trim();
        if (raw === "transparent") {
          return "transparent";
        }
        if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw)) {
          return raw;
        }
        return fallback;
      };
      disposeMaterial = (material) => {
        if (!material) {
          return;
        }
        if (Array.isArray(material)) {
          material.forEach((item) => disposeMaterial(item));
          return;
        }
        if (typeof material.dispose === "function") {
          material.dispose();
        }
      };
      clearContainer = (container) => {
        while (container.firstChild) {
          container.removeChild(container.firstChild);
        }
      };
      createFallback = (container, sceneType, message) => {
        clearContainer(container);
        const fallback = container.ownerDocument.createElement("div");
        fallback.setAttribute("data-three-fallback", "true");
        fallback.style.cssText = [
          "display:flex",
          "align-items:center",
          "justify-content:center",
          "height:100%",
          "min-height:180px",
          "border:1px dashed rgba(148,163,184,.7)",
          "background:rgba(15,23,42,.06)",
          "color:#334155",
          "font-size:12px",
          "font-family:Arial,sans-serif",
          "padding:12px",
          "text-align:center"
        ].join(";");
        fallback.textContent = message || "Scene unavailable: " + sceneType;
        container.appendChild(fallback);
      };
    }
  });

  // src/components/threejs-scene.js
  function registerThreejsScene(editor) {
    const domComponents = editor.DomComponents;
    domComponents.addType("threejs-scene", {
      isComponent(el) {
        if (!el || typeof el.getAttribute !== "function") {
          return false;
        }
        if (el.getAttribute("data-gjs-type") === "threejs-scene") {
          return { type: "threejs-scene" };
        }
        return false;
      },
      model: {
        defaults: {
          name: "Three.js Scene",
          draggable: true,
          droppable: true,
          style: { "min-height": "80px", position: "relative" },
          traits: [
            {
              type: "select",
              name: "data-scene-type",
              label: "Scene type",
              options: sceneTypeOptions
            },
            {
              type: "color",
              name: "data-scene-color",
              label: "Primary color"
            },
            {
              type: "select",
              name: "data-scene-bg",
              label: "Background",
              options: bgOptions
            },
            {
              type: "number",
              name: "data-scene-height",
              label: "Height (px)",
              min: 100,
              max: 1e3,
              step: 50
            },
            {
              type: "number",
              name: "data-scene-speed",
              label: "Speed",
              min: 0.1,
              max: 3,
              step: 0.1
            },
            {
              type: "number",
              name: "data-particle-count",
              label: "Particle count",
              min: 10,
              max: 500,
              step: 10
            },
            {
              type: "checkbox",
              name: "data-wireframe",
              label: "Wireframe mode"
            },
            {
              type: "checkbox",
              name: "data-auto-rotate",
              label: "Auto rotate"
            },
            {
              type: "checkbox",
              name: "data-threejs-overlay",
              label: "Background mode"
            }
          ],
          attributes: {
            "data-scene-type": "particles",
            "data-scene-color": "#5b8cff",
            "data-scene-bg": "transparent",
            "data-scene-height": "400",
            "data-scene-speed": "1",
            "data-particle-count": "120",
            "data-wireframe": "false",
            "data-auto-rotate": "true",
            "data-threejs-overlay": "true"
          }
        }
      },
      view: {
        init() {
          this.sceneInstance = null;
          this.rebuildTimer = 0;
          this.waitReadyBound = false;
          this.onLpSectionExpanded = ({ cid } = {}) => {
            if (cid === this.model.cid) {
              this.rebuildScene();
            }
          };
          this.onLpSectionCollapsed = ({ cid } = {}) => {
            var _a, _b;
            if (cid === this.model.cid && ((_b = (_a = this.sceneInstance) == null ? void 0 : _a.renderer) == null ? void 0 : _b.domElement)) {
              this.sceneInstance.renderer.domElement.style.visibility = "hidden";
            }
          };
          editor.on("lp:section:expanded", this.onLpSectionExpanded);
          editor.on("lp:section:collapsed", this.onLpSectionCollapsed);
          this.listenTo(this.model, "change:attributes", () => {
            if (isDynamicSectionCollapsed(this.el)) {
              collapseDynamicSection(this.model, this.el, editor);
            } else {
              upsertSectionBadge(this.model, this.el, editor);
            }
            clearTimeout(this.rebuildTimer);
            this.rebuildTimer = setTimeout(() => {
              this.rebuildScene();
            }, 300);
          });
        },
        onRender() {
          collapseDynamicSection(this.model, this.el, editor);
          this.rebuildScene();
        },
        removed() {
          if (this.onLpSectionExpanded) {
            editor.off("lp:section:expanded", this.onLpSectionExpanded);
          }
          if (this.onLpSectionCollapsed) {
            editor.off("lp:section:collapsed", this.onLpSectionCollapsed);
          }
          clearTimeout(this.rebuildTimer);
          disposeScene(this.sceneInstance);
          this.sceneInstance = null;
        },
        rebuildScene() {
          var _a, _b, _c, _d;
          if (!this.el) {
            return;
          }
          upsertSectionBadge(this.model, this.el, editor);
          if (isDynamicSectionCollapsed(this.el)) {
            if ((_b = (_a = this.sceneInstance) == null ? void 0 : _a.renderer) == null ? void 0 : _b.domElement) {
              this.sceneInstance.renderer.domElement.style.visibility = "hidden";
            }
            return;
          }
          disposeScene(this.sceneInstance);
          this.sceneInstance = null;
          const doc = this.el.ownerDocument;
          const win = doc == null ? void 0 : doc.defaultView;
          if (!(win == null ? void 0 : win.THREE)) {
            if (!doc || this.waitReadyBound) {
              return;
            }
            this.waitReadyBound = true;
            const onceReady = () => {
              this.waitReadyBound = false;
              this.rebuildScene();
              doc.removeEventListener("lp:ready", onceReady);
            };
            doc.addEventListener("lp:ready", onceReady, { once: true });
            return;
          }
          const attrs = this.model.getAttributes() || {};
          const config = normalizeSceneConfig({
            sceneType: attrs["data-scene-type"],
            sceneColor: attrs["data-scene-color"],
            sceneBg: attrs["data-scene-bg"],
            sceneHeight: attrs["data-scene-height"],
            sceneSpeed: attrs["data-scene-speed"],
            particleCount: attrs["data-particle-count"],
            wireframe: toBoolString(attrs["data-wireframe"], "false"),
            autoRotate: toBoolString(attrs["data-auto-rotate"], "true"),
            overlay: toBoolString(attrs["data-threejs-overlay"], "true")
          });
          let preview = this.el.querySelector(":scope > [data-lp-three-preview]");
          if (!preview) {
            preview = this.el.ownerDocument.createElement("div");
            preview.setAttribute("data-lp-three-preview", "true");
            preview.setAttribute("data-gjs-selectable", "false");
            preview.setAttribute("contenteditable", "false");
            preview.style.cssText = "position:relative;width:100%;min-height:220px;z-index:1;";
            this.el.appendChild(preview);
          }
          preview.style.visibility = "";
          preview.style.position = "relative";
          preview.style.pointerEvents = "auto";
          this.sceneInstance = buildScene(preview, config, { win });
          if ((_d = (_c = this.sceneInstance) == null ? void 0 : _c.renderer) == null ? void 0 : _d.domElement) {
            this.sceneInstance.renderer.domElement.style.visibility = "";
          }
        }
      }
    });
  }
  var SCENE_TYPES, sceneTypeOptions, bgOptions, toBoolString;
  var init_threejs_scene = __esm({
    "src/components/threejs-scene.js"() {
      init_scene_builder();
      init_badge_injector();
      SCENE_TYPES = ["particles", "rotating-cube", "sphere", "wave", "globe", "rings"];
      sceneTypeOptions = SCENE_TYPES.map((item) => ({ id: item, name: item }));
      bgOptions = [
        { id: "transparent", name: "transparent" },
        { id: "#0b1020", name: "#0b1020" },
        { id: "#111827", name: "#111827" },
        { id: "#ffffff", name: "#ffffff" }
      ];
      toBoolString = (value, fallback) => {
        if (value == null) {
          return fallback;
        }
        if (typeof value === "boolean") {
          return value ? "true" : "false";
        }
        const normalized = String(value).trim().toLowerCase();
        if (normalized === "true" || normalized === "false") {
          return normalized;
        }
        return fallback;
      };
    }
  });

  // src/panels/animation-controls.js
  function registerAnimationControls(editor) {
    const commands = editor.Commands;
    commands.add("lp-preview-animations", {
      run(ed) {
        var _a, _b, _c, _d;
        const frameWin = (_b = (_a = ed.Canvas).getWindow) == null ? void 0 : _b.call(_a);
        const frameDoc = (_d = (_c = ed.Canvas).getDocument) == null ? void 0 : _d.call(_c);
        if (!(frameWin == null ? void 0 : frameWin.gsap) || !frameDoc) {
          return;
        }
        const gsap = frameWin.gsap;
        const nodes = Array.from(frameDoc.querySelectorAll('[data-gjs-type="gsap-animated"]'));
        nodes.forEach((el) => {
          var _a2, _b2, _c2, _d2, _e, _f;
          const attrs = el.attributes;
          const animation = ((_a2 = attrs.getNamedItem("data-gsap-animation")) == null ? void 0 : _a2.value) || "fadeInUp";
          const duration = parseNumber2((_b2 = attrs.getNamedItem("data-gsap-duration")) == null ? void 0 : _b2.value, 1, 0.1, 5);
          const delay = parseNumber2((_c2 = attrs.getNamedItem("data-gsap-delay")) == null ? void 0 : _c2.value, 0, 0, 3);
          const ease = ((_d2 = attrs.getNamedItem("data-gsap-ease")) == null ? void 0 : _d2.value) || "power2.out";
          const stagger = parseNumber2((_e = attrs.getNamedItem("data-gsap-stagger")) == null ? void 0 : _e.value, 0.1, 0, 1);
          const animateChildren = String(((_f = attrs.getNamedItem("data-gsap-children")) == null ? void 0 : _f.value) || "false").toLowerCase() === "true";
          const targets = animateChildren && el.children.length > 0 ? Array.from(el.children) : el;
          gsap.killTweensOf([el, ...Array.from(el.children || [])]);
          gsap.set([el, ...Array.from(el.children || [])], { clearProps: "transform,opacity,filter" });
          gsap.from(targets, {
            ...fromVarsForAnimation(animation),
            duration,
            delay,
            ease,
            stagger: Array.isArray(targets) && targets.length > 1 ? stagger : 0
          });
        });
      }
    });
    commands.add("lp-reset-animations", {
      run(ed) {
        var _a, _b, _c, _d, _e;
        const frameWin = (_b = (_a = ed.Canvas).getWindow) == null ? void 0 : _b.call(_a);
        const frameDoc = (_d = (_c = ed.Canvas).getDocument) == null ? void 0 : _d.call(_c);
        if (!(frameWin == null ? void 0 : frameWin.gsap) || !frameDoc) {
          return;
        }
        const gsap = frameWin.gsap;
        const nodes = Array.from(frameDoc.querySelectorAll('[data-gjs-type="gsap-animated"]'));
        const allTargets = [];
        nodes.forEach((el) => {
          allTargets.push(el);
          allTargets.push(...Array.from(el.children || []));
        });
        if ((_e = frameWin.ScrollTrigger) == null ? void 0 : _e.getAll) {
          frameWin.ScrollTrigger.getAll().forEach((trigger) => trigger.kill());
        }
        gsap.globalTimeline.getChildren(true, true, true).forEach((timeline) => timeline.kill());
        gsap.killTweensOf(allTargets);
        gsap.set(allTargets, { clearProps: "transform,opacity,filter" });
      }
    });
    const panels = editor.Panels;
    const panelId = "options";
    if (!panels.getButton(panelId, "lp-preview-animations")) {
      panels.addButton(panelId, {
        id: "lp-preview-animations",
        className: "fa fa-play",
        command: "lp-preview-animations",
        attributes: { title: "Preview Animations" }
      });
    }
    if (!panels.getButton(panelId, "lp-reset-animations")) {
      panels.addButton(panelId, {
        id: "lp-reset-animations",
        className: "fa fa-undo",
        command: "lp-reset-animations",
        attributes: { title: "Reset Animations" }
      });
    }
  }
  var fromVarsForAnimation, parseNumber2;
  var init_animation_controls = __esm({
    "src/panels/animation-controls.js"() {
      fromVarsForAnimation = (animation) => {
        switch (String(animation || "")) {
          case "fadeInDown":
            return { opacity: 0, y: -40 };
          case "fadeInLeft":
            return { opacity: 0, x: -40 };
          case "fadeInRight":
            return { opacity: 0, x: 40 };
          case "slideInLeft":
            return { opacity: 0, x: -120 };
          case "slideInRight":
            return { opacity: 0, x: 120 };
          case "zoomIn":
            return { opacity: 0, scale: 0.7 };
          case "zoomOut":
            return { opacity: 0, scale: 1.25 };
          case "bounceIn":
            return { opacity: 0, scale: 0.35 };
          case "flipInX":
            return { opacity: 0, rotateX: 90, transformPerspective: 600 };
          case "flipInY":
            return { opacity: 0, rotateY: 90, transformPerspective: 600 };
          case "rotateIn":
            return { opacity: 0, rotate: -15, transformOrigin: "center center" };
          case "fadeInUp":
          default:
            return { opacity: 0, y: 40 };
        }
      };
      parseNumber2 = (value, fallback, min, max) => {
        const parsed = Number.parseFloat(value);
        if (!Number.isFinite(parsed)) {
          return fallback;
        }
        return Math.min(max, Math.max(min, parsed));
      };
    }
  });

  // src/utils/element-detector.js
  function detectElements(component) {
    const results = [];
    const walk = (current, depth) => {
      var _a;
      if (!current || depth > 4) {
        return;
      }
      const children = (_a = current.components) == null ? void 0 : _a.call(current);
      if (!children || typeof children.forEach !== "function") {
        return;
      }
      children.forEach((child) => {
        var _a2;
        const attrs = ((_a2 = child.getAttributes) == null ? void 0 : _a2.call(child)) || {};
        if (SECTION_TYPES2.has(String(attrs["data-gjs-type"] || "").trim())) {
          return;
        }
        const detected = detectElement(child);
        results.push({
          gjs_component: child,
          ...detected
        });
        walk(child, depth + 1);
      });
    };
    walk(component, 1);
    return results;
  }
  var SECTION_TYPES2, ELEMENT_ICONS, truncate, getTagName, getClassName, getText, isIconElement, detectElement, element_detector_default;
  var init_element_detector = __esm({
    "src/utils/element-detector.js"() {
      SECTION_TYPES2 = /* @__PURE__ */ new Set(["standard-section", "gsap-animated", "threejs-scene"]);
      ELEMENT_ICONS = Object.freeze({
        heading: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 5h10M3 10h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        paragraph: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 4h10M3 8h10M3 12h8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>',
        button: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="2.5" y="4.5" width="11" height="7" rx="2" stroke="currentColor" stroke-width="1.4"/><circle cx="8" cy="8" r="1" fill="currentColor"/></svg>',
        image: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="2.5" y="3" width="11" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="m4.5 11 2.6-3 2 2 1.2-1.4 1.7 2.4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        icon: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m8 2.3 1.5 3.2 3.5.4-2.6 2.4.7 3.5L8 10.1l-3.1 1.7.7-3.5L3 5.9l3.5-.4L8 2.3Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>',
        three: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 2 13 5v6l-5 3-5-3V5l5-3Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>',
        block: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="3" y="3" width="10" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>',
        container: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="2.5" y="4.5" width="7" height="7" rx="1.2" stroke="currentColor" stroke-width="1.2"/><rect x="6.5" y="2.5" width="7" height="7" rx="1.2" stroke="currentColor" stroke-width="1.2"/></svg>'
      });
      truncate = (value, maxLength) => {
        const text = String(value || "").replace(/\s+/g, " ").trim();
        return text.length > maxLength ? `${text.slice(0, maxLength).trim()}...` : text;
      };
      getTagName = (component, el) => {
        var _a, _b;
        return String(
          (el == null ? void 0 : el.tagName) || ((_a = component == null ? void 0 : component.get) == null ? void 0 : _a.call(component, "tagName")) || ((_b = component == null ? void 0 : component.get) == null ? void 0 : _b.call(component, "type")) || "div"
        ).toUpperCase();
      };
      getClassName = (component, el) => {
        var _a, _b, _c;
        return String(
          ((_a = el == null ? void 0 : el.getAttribute) == null ? void 0 : _a.call(el, "class")) || ((_c = (_b = component == null ? void 0 : component.getAttributes) == null ? void 0 : _b.call(component)) == null ? void 0 : _c.class) || ""
        );
      };
      getText = (el) => String((el == null ? void 0 : el.innerText) || (el == null ? void 0 : el.textContent) || "").trim();
      isIconElement = (tag, className) => {
        if (tag === "SVG") {
          return true;
        }
        return tag === "I" && /(icon|fa-|material-icons|material-symbols|lucide)/i.test(className);
      };
      detectElement = (component) => {
        var _a, _b, _c, _d, _e, _f;
        const el = (_a = component.getEl) == null ? void 0 : _a.call(component);
        const tag = getTagName(component, el);
        const className = getClassName(component, el);
        const attrs = ((_b = component.getAttributes) == null ? void 0 : _b.call(component)) || {};
        const text = getText(el);
        if (/^H[1-6]$/.test(tag)) {
          return {
            tag,
            typeLabel: `Heading ${tag}`,
            icon: ELEMENT_ICONS.heading,
            contentPreview: truncate(text, 45)
          };
        }
        if (tag === "P") {
          return {
            tag,
            typeLabel: "Paragraph",
            icon: ELEMENT_ICONS.paragraph,
            contentPreview: truncate(text, 45)
          };
        }
        if (tag === "BUTTON" || /\bbtn\b|btn-|button/i.test(className)) {
          return {
            tag,
            typeLabel: "Button",
            icon: ELEMENT_ICONS.button,
            contentPreview: truncate(text, 45)
          };
        }
        if (tag === "IMG") {
          return {
            tag,
            typeLabel: "Image",
            icon: ELEMENT_ICONS.image,
            contentPreview: truncate(attrs.alt || ((_c = el == null ? void 0 : el.getAttribute) == null ? void 0 : _c.call(el, "alt")) || "image", 45)
          };
        }
        if (isIconElement(tag, className)) {
          return {
            tag,
            typeLabel: "Icon",
            icon: ELEMENT_ICONS.icon,
            contentPreview: truncate(className, 20)
          };
        }
        if (tag === "CANVAS" && ((_e = (_d = el == null ? void 0 : el.parentElement) == null ? void 0 : _d.getAttribute) == null ? void 0 : _e.call(_d, "data-gjs-type")) === "threejs-scene") {
          return {
            tag,
            typeLabel: "Three.js Canvas",
            icon: ELEMENT_ICONS.three,
            contentPreview: truncate(el.parentElement.getAttribute("data-scene-type") || "scene", 45)
          };
        }
        if ((tag === "DIV" || tag === "SECTION") && ((el == null ? void 0 : el.childElementCount) || 0) === 0 && text) {
          return {
            tag,
            typeLabel: "Block",
            icon: ELEMENT_ICONS.block,
            contentPreview: truncate(text, 45)
          };
        }
        return {
          tag,
          typeLabel: "Container",
          icon: ELEMENT_ICONS.container,
          contentPreview: `${tag.toLowerCase()} - ${(el == null ? void 0 : el.childElementCount) || ((_f = component.components) == null ? void 0 : _f.call(component).length) || 0} children`
        };
      };
      element_detector_default = detectElements;
    }
  });

  // src/panels/sections-navigator.js
  function registerSectionsNavigator(editor) {
    ensureHostStyles();
    if (!editor.Panels.getPanel("lp-navigator")) {
      editor.Panels.addPanel({
        id: "lp-navigator",
        label: "Sections",
        visible: true
      });
    }
    const state = {
      open: /* @__PURE__ */ new Set(),
      selectedCid: "",
      host: null
    };
    const highlightNavigatorRow = (cid) => {
      state.selectedCid = cid || "";
      if (!state.host) {
        return;
      }
      state.host.querySelectorAll(".is-selected").forEach((node) => node.classList.remove("is-selected"));
      if (!cid) {
        return;
      }
      state.host.querySelectorAll("[data-cid]").forEach((node) => {
        if (node.getAttribute("data-cid") === cid) {
          node.classList.add("is-selected");
        }
      });
    };
    const scrollToComponent = (component) => {
      var _a;
      const el = (_a = component == null ? void 0 : component.getEl) == null ? void 0 : _a.call(component);
      if (el == null ? void 0 : el.scrollIntoView) {
        el.scrollIntoView({ block: "center", behavior: "smooth" });
      }
    };
    const scrollNavigatorToRow = (cid) => {
      var _a;
      if (!state.host || !cid) {
        return;
      }
      const row = Array.from(state.host.querySelectorAll("[data-lp-section]")).find((node) => node.getAttribute("data-lp-section") === cid);
      (_a = row == null ? void 0 : row.scrollIntoView) == null ? void 0 : _a.call(row, { block: "center", behavior: "smooth" });
    };
    const focusNavigatorSection = (cid) => {
      if (!cid) {
        return;
      }
      state.open.clear();
      state.open.add(cid);
      render();
      highlightNavigatorRow(cid);
      scrollNavigatorToRow(cid);
    };
    const renderTraits = (component) => {
      if (!component) {
        return "";
      }
      const attrs = getAttrs(component);
      const type = getSectionType(component);
      if (type === "gsap-animated") {
        return `
                <div class="lp-nav-traits" data-lp-traits="${escapeHtml2(component.cid)}">
                    <div class="lp-nav-traits-row">
                        <select data-lp-attr="data-gsap-animation">${optionHtml(GSAP_ANIMATIONS2, attrs["data-gsap-animation"] || "fadeInUp")}</select>
                        <select data-lp-attr="data-gsap-duration">${optionHtml(GSAP_DURATIONS, attrs["data-gsap-duration"] || "1")}</select>
                        <select data-lp-attr="data-gsap-trigger">${optionHtml(GSAP_TRIGGERS2, attrs["data-gsap-trigger"] || "scroll")}</select>
                    </div>
                </div>
            `;
      }
      if (type === "threejs-scene") {
        return `
                <div class="lp-nav-traits" data-lp-traits="${escapeHtml2(component.cid)}">
                    <div class="lp-nav-traits-row">
                        <select data-lp-attr="data-scene-type">${optionHtml(THREE_SCENE_TYPES, attrs["data-scene-type"] || "particles")}</select>
                        <input data-lp-attr="data-scene-color" type="color" value="${escapeHtml2(attrs["data-scene-color"] || "#5b8cff")}">
                        <input data-lp-attr="data-scene-height" type="number" min="100" max="1000" step="50" value="${escapeHtml2(attrs["data-scene-height"] || "400")}">
                    </div>
                    <div class="lp-nav-traits-row">
                        <input data-lp-attr="data-scene-speed" type="range" min="0.1" max="3" step="0.1" value="${escapeHtml2(attrs["data-scene-speed"] || "1")}">
                        <input data-lp-attr="data-particle-count" type="number" min="10" max="500" step="10" value="${escapeHtml2(attrs["data-particle-count"] || "120")}">
                        <span></span>
                    </div>
                </div>
            `;
      }
      if (type === "standard-section") {
        return `
                <div class="lp-nav-traits" data-lp-traits="${escapeHtml2(component.cid)}">
                    <input data-lp-attr="data-label" type="text" value="${escapeHtml2(attrs["data-label"] || attrs.id || "Section")}">
                </div>
            `;
      }
      return "";
    };
    const componentByCid = (cid) => {
      var _a;
      if (!cid) {
        return null;
      }
      let found = null;
      const walk = (component) => {
        var _a2;
        if (!component || found) {
          return;
        }
        if (component.cid === cid) {
          found = component;
          return;
        }
        const children = (_a2 = component.components) == null ? void 0 : _a2.call(component);
        if (children && typeof children.forEach === "function") {
          children.forEach(walk);
        }
      };
      walk((_a = editor.getWrapper) == null ? void 0 : _a.call(editor));
      return found;
    };
    const render = () => {
      var _a;
      state.host = resolveHost(editor);
      if (!state.host) {
        return;
      }
      const sections = getTopLevelSections(editor);
      const selected = (_a = editor.getSelected) == null ? void 0 : _a.call(editor);
      const selectedCid = (selected == null ? void 0 : selected.cid) || "";
      const sectionHtml = sections.map((section) => {
        const type = getSectionType(section);
        const meta = sectionMeta(type);
        const isOpen = state.open.has(section.cid);
        const isSelected = selectedCid === section.cid;
        const elements = isOpen ? element_detector_default(section) : [];
        return `
                <div class="lp-section-item">
                    <button type="button" class="lp-section-row ${isOpen ? "is-open" : ""} ${isSelected ? "is-selected" : ""}" data-lp-section="${escapeHtml2(section.cid)}" data-cid="${escapeHtml2(section.cid)}">
                        <span class="lp-nav-icon lp-nav-icon--${type === "gsap-animated" ? "gsap" : type === "threejs-scene" ? "three" : "std"}">${meta.icon}</span>
                        <span class="lp-section-name">${escapeHtml2(getSectionName(section))}</span>
                        <span class="lp-nav-kind ${meta.className}">${meta.short}</span>
                        <span class="lp-section-chevron">&#9654;</span>
                    </button>
                    ${isOpen ? `
                        <div class="lp-elements">
                            ${elements.map((item) => `
                                <div class="lp-el-row ${selectedCid === item.gjs_component.cid ? "is-selected" : ""}" data-lp-element="${escapeHtml2(item.gjs_component.cid)}" data-lp-parent="${escapeHtml2(section.cid)}" data-cid="${escapeHtml2(item.gjs_component.cid)}">
                                    <div class="lp-el-icon">${item.icon}</div>
                                    <div class="lp-el-info">
                                        <div class="lp-el-type">${escapeHtml2(item.typeLabel)}</div>
                                        <div class="lp-el-content">${escapeHtml2(item.contentPreview || item.tag)}</div>
                                    </div>
                                </div>
                            `).join("") || '<div class="lp-nav-empty">No elements</div>'}
                        </div>
                    ` : ""}
                </div>
            `;
      }).join("");
      state.host.innerHTML = `
            <div class="lp-nav-head">
                <span>Sections</span>
                <span>${sections.length}</span>
            </div>
            <div class="lp-nav-list">
                ${sectionHtml || '<div class="lp-nav-empty">No LP sections</div>'}
            </div>
            ${renderTraits(selected)}
        `;
      highlightNavigatorRow(selectedCid);
    };
    const refreshNavigator = debounce2(render, 100);
    const selectComponent = (component) => {
      if (!component) {
        return;
      }
      editor.select(component);
      updateCanvasSectionSelection(editor, component);
      scrollToComponent(component);
      highlightNavigatorRow(component.cid);
    };
    document.addEventListener("click", (event) => {
      var _a, _b, _c;
      if (!((_a = state.host) == null ? void 0 : _a.contains(event.target))) {
        return;
      }
      const sectionRow = event.target.closest("[data-lp-section]");
      if (sectionRow) {
        const component = componentByCid(sectionRow.getAttribute("data-lp-section"));
        if (!component) {
          return;
        }
        if (state.open.has(component.cid)) {
          state.open.delete(component.cid);
        } else {
          state.open.add(component.cid);
        }
        selectComponent(component);
        render();
        return;
      }
      const elementRow = event.target.closest("[data-lp-element]");
      if (elementRow) {
        const component = componentByCid(elementRow.getAttribute("data-lp-element"));
        const parentSection = componentByCid(elementRow.getAttribute("data-lp-parent"));
        selectComponent(component);
        if (parentSection) {
          expandDynamicSection(parentSection, (_b = parentSection.getEl) == null ? void 0 : _b.call(parentSection), editor, { temporary: true });
          scheduleDynamicReCollapse(parentSection, (_c = parentSection.getEl) == null ? void 0 : _c.call(parentSection), editor, 2e3);
        }
        highlightNavigatorRow(component == null ? void 0 : component.cid);
      }
    });
    const updateTraitFromEvent = (event) => {
      var _a;
      if (!((_a = state.host) == null ? void 0 : _a.contains(event.target))) {
        return;
      }
      const field = event.target.closest("[data-lp-attr]");
      const traitBox = event.target.closest("[data-lp-traits]");
      if (!field || !traitBox) {
        return;
      }
      const component = componentByCid(traitBox.getAttribute("data-lp-traits"));
      if (!component) {
        return;
      }
      component.addAttributes({
        [field.getAttribute("data-lp-attr")]: field.value
      });
    };
    document.addEventListener("input", updateTraitFromEvent);
    document.addEventListener("change", updateTraitFromEvent);
    editor.on("component:add", refreshNavigator);
    editor.on("component:remove", refreshNavigator);
    editor.on("component:update", refreshNavigator);
    editor.on("component:selected", (component) => {
      updateCanvasSectionSelection(editor, component);
      highlightNavigatorRow(component == null ? void 0 : component.cid);
      refreshNavigator();
    });
    editor.on("lp:section:focus", ({ cid } = {}) => {
      focusNavigatorSection(cid);
    });
    editor.on("canvas:frame:load", () => {
      var _a, _b, _c;
      const doc = (_b = (_a = editor.Canvas).getDocument) == null ? void 0 : _b.call(_a);
      (_c = doc == null ? void 0 : doc.addEventListener) == null ? void 0 : _c.call(doc, "click", () => {
        getTopLevelSections(editor).forEach((section) => {
          var _a2;
          const el = (_a2 = section.getEl) == null ? void 0 : _a2.call(section);
          if (el == null ? void 0 : el.__lpTemporaryExpanded) {
            collapseDynamicSection(section, el, editor);
          }
        });
      });
    });
    editor.on("load", render);
    editor.on("canvas:frame:load", refreshNavigator);
    render();
  }
  var SECTION_TYPES3, GSAP_ANIMATIONS2, GSAP_TRIGGERS2, GSAP_DURATIONS, THREE_SCENE_TYPES, debounce2, escapeHtml2, optionHtml, getAttrs, getSectionType, getSectionName, sectionMeta, getTopLevelSections, hostStyles, ensureHostStyles, resolveHost;
  var init_sections_navigator = __esm({
    "src/panels/sections-navigator.js"() {
      init_element_detector();
      init_badge_injector();
      SECTION_TYPES3 = /* @__PURE__ */ new Set(["standard-section", "gsap-animated", "threejs-scene"]);
      GSAP_ANIMATIONS2 = [
        "fadeInUp",
        "fadeInDown",
        "fadeInLeft",
        "fadeInRight",
        "slideInLeft",
        "slideInRight",
        "zoomIn",
        "zoomOut",
        "bounceIn",
        "flipInX",
        "flipInY",
        "rotateIn"
      ];
      GSAP_TRIGGERS2 = ["scroll", "load", "hover", "click"];
      GSAP_DURATIONS = ["0.2", "0.4", "0.6", "0.8", "1", "1.2", "1.5", "2", "3", "5"];
      THREE_SCENE_TYPES = ["particles", "rotating-cube", "sphere", "wave", "globe", "rings"];
      debounce2 = (fn, waitMs) => {
        let timer = 0;
        return (...args) => {
          clearTimeout(timer);
          timer = setTimeout(() => fn(...args), waitMs);
        };
      };
      escapeHtml2 = (value) => String(value != null ? value : "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
      optionHtml = (items, selected) => items.map((item) => `<option value="${escapeHtml2(item)}" ${String(item) === String(selected) ? "selected" : ""}>${escapeHtml2(item)}</option>`).join("");
      getAttrs = (component) => {
        var _a;
        return ((_a = component == null ? void 0 : component.getAttributes) == null ? void 0 : _a.call(component)) || {};
      };
      getSectionType = (component) => {
        var _a;
        return String(
          getAttrs(component)["data-gjs-type"] || ((_a = component == null ? void 0 : component.get) == null ? void 0 : _a.call(component, "type")) || ""
        ).trim();
      };
      getSectionName = (component) => {
        const attrs = getAttrs(component);
        return String(attrs["data-label"] || attrs.id || "Section").trim();
      };
      sectionMeta = (type) => {
        if (type === "gsap-animated") {
          return {
            icon: BADGE_ICONS.gsap,
            short: "GSAP",
            className: "lp-nav-kind--gsap"
          };
        }
        if (type === "threejs-scene") {
          return {
            icon: BADGE_ICONS.three,
            short: "3D",
            className: "lp-nav-kind--three"
          };
        }
        return {
          icon: BADGE_ICONS.standard,
          short: "STD",
          className: "lp-nav-kind--std"
        };
      };
      getTopLevelSections = (editor) => {
        var _a, _b;
        const wrapper = (_a = editor.getWrapper) == null ? void 0 : _a.call(editor);
        const children = (_b = wrapper == null ? void 0 : wrapper.components) == null ? void 0 : _b.call(wrapper);
        if (!children || typeof children.forEach !== "function") {
          return [];
        }
        const sections = [];
        children.forEach((component) => {
          const type = getSectionType(component);
          if (SECTION_TYPES3.has(type)) {
            sections.push(component);
          }
        });
        return sections;
      };
      hostStyles = `
.lp-navigator-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 260px;
  background: #11151b;
  border-top: 1px solid rgba(255,255,255,.08);
  color: #d8dee9;
  font-family: sans-serif;
}
.lp-nav-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  font-size: 12px;
  font-weight: 700;
  color: #f7f8fb;
}
.lp-nav-list {
  flex: 1;
  overflow: auto;
  padding: 8px;
}
.lp-section-row {
  display: grid;
  grid-template-columns: 22px 1fr auto 14px;
  gap: 8px;
  align-items: center;
  width: 100%;
  min-height: 36px;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: inherit;
  padding: 7px 8px;
  text-align: left;
  cursor: pointer;
}
.lp-section-row:hover,
.lp-section-row.is-selected {
  background: rgba(238,237,254,.12);
}
.lp-nav-icon {
  width: 22px;
  height: 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 5px;
}
.lp-nav-icon--gsap { background: #EEEDFE; color: #534AB7; }
.lp-nav-icon--three { background: #E1F5EE; color: #0F6E56; }
.lp-nav-icon--std { background: #F1EFE8; color: #5F5E5A; }
.lp-section-name {
  min-width: 0;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: 12px;
  font-weight: 600;
}
.lp-nav-kind {
  border-radius: 999px;
  padding: 2px 6px;
  font-size: 10px;
  font-weight: 700;
}
.lp-nav-kind--gsap { background: #EEEDFE; color: #534AB7; }
.lp-nav-kind--three { background: #E1F5EE; color: #0F6E56; }
.lp-nav-kind--std { background: #F1EFE8; color: #5F5E5A; }
.lp-section-chevron {
  color: #98a2b3;
  font-size: 10px;
  transform: rotate(0deg);
  transition: transform .12s ease;
}
.lp-section-row.is-open .lp-section-chevron {
  transform: rotate(90deg);
}
.lp-elements {
  display: grid;
  gap: 2px;
  margin: 2px 0 8px 30px;
}
.lp-el-row {
  display: grid;
  grid-template-columns: 28px 1fr;
  gap: 8px;
  align-items: center;
  min-height: 44px;
  border-radius: 6px;
  padding: 6px 7px;
  cursor: pointer;
}
.lp-el-row:hover,
.lp-el-row.is-selected {
  background: #EEEDFE;
  color: #534AB7;
}
.lp-el-icon {
  width: 28px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 6px;
  background: var(--color-background-secondary, #171d25);
  color: currentColor;
}
.lp-el-info {
  min-width: 0;
}
.lp-el-type {
  font-size: 10px;
  font-weight: 700;
  color: #98a2b3;
}
.lp-el-content {
  margin-top: 2px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: 12px;
  color: #d8dee9;
}
.lp-el-row.is-selected .lp-el-type,
.lp-el-row:hover .lp-el-type {
  color: rgba(83,74,183,.72);
}
.lp-el-row.is-selected .lp-el-content {
  color: #534AB7;
}
.lp-nav-empty {
  padding: 18px 10px;
  font-size: 12px;
  color: #98a2b3;
  text-align: center;
}
.lp-nav-traits {
  border-top: 1px solid rgba(255,255,255,.08);
  padding: 10px;
  display: grid;
  gap: 8px;
}
.lp-nav-traits-row {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 6px;
}
.lp-nav-traits input,
.lp-nav-traits select {
  width: 100%;
  min-width: 0;
  height: 28px;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 5px;
  background: #0b0f15;
  color: #f7f8fb;
  font-size: 11px;
  padding: 0 7px;
}
.lp-nav-traits input[type="color"] {
  padding: 2px;
}
.lp-nav-traits input[type="range"] {
  padding: 0;
}
`;
      ensureHostStyles = () => {
        if (document.getElementById("lp-navigator-host-styles")) {
          return;
        }
        const style = document.createElement("style");
        style.id = "lp-navigator-host-styles";
        style.textContent = hostStyles;
        document.head.appendChild(style);
      };
      resolveHost = (editor) => {
        var _a, _b, _c, _d;
        const existing = document.getElementById("lp-navigator-panel");
        if (existing) {
          return existing;
        }
        const viewsContainer = document.querySelector(".gjs-pn-views-container") || ((_c = (_b = (_a = editor.getContainer) == null ? void 0 : _a.call(editor)) == null ? void 0 : _b.querySelector) == null ? void 0 : _c.call(_b, ".gjs-pn-views-container")) || ((_d = editor.getContainer) == null ? void 0 : _d.call(editor));
        if (!viewsContainer) {
          return null;
        }
        const panel = document.createElement("div");
        panel.id = "lp-navigator-panel";
        panel.className = "lp-navigator-panel";
        viewsContainer.insertBefore(panel, viewsContainer.firstChild);
        return panel;
      };
    }
  });

  // src/utils/manifest-loader.js
  function loadManifest(editor, manifest) {
    if (!editor || !manifest || !Array.isArray(manifest.sections)) {
      return { applied: 0, skipped: 0 };
    }
    let applied = 0;
    let skipped = 0;
    manifest.sections.forEach((section) => {
      var _a;
      const id = String((section == null ? void 0 : section.id) || "").trim();
      const config = section == null ? void 0 : section.config;
      if (!id || !config || typeof config !== "object") {
        skipped += 1;
        return;
      }
      const component = findComponentById(editor, id);
      if (!component) {
        skipped += 1;
        return;
      }
      const attrs = ((_a = component.getAttributes) == null ? void 0 : _a.call(component)) || {};
      const type = String(attrs["data-gjs-type"] || component.get("type") || "").trim();
      const keys = KNOWN_CONFIG_KEYS[type];
      if (!Array.isArray(keys)) {
        skipped += 1;
        return;
      }
      const nextAttrs = { ...attrs };
      keys.forEach((key) => {
        if (Object.prototype.hasOwnProperty.call(config, key)) {
          nextAttrs[key] = normalizeConfigValue(config[key]);
        }
      });
      component.setAttributes(nextAttrs);
      applied += 1;
    });
    return { applied, skipped };
  }
  var KNOWN_CONFIG_KEYS, collectComponents, findComponentById, normalizeConfigValue;
  var init_manifest_loader = __esm({
    "src/utils/manifest-loader.js"() {
      KNOWN_CONFIG_KEYS = {
        "standard-section": [],
        "gsap-animated": [
          "data-gsap-animation",
          "data-gsap-duration",
          "data-gsap-delay",
          "data-gsap-ease",
          "data-gsap-trigger",
          "data-gsap-children",
          "data-gsap-stagger"
        ],
        "threejs-scene": [
          "data-scene-type",
          "data-scene-color",
          "data-scene-bg",
          "data-scene-height",
          "data-scene-speed",
          "data-particle-count",
          "data-threejs-overlay",
          "data-wireframe",
          "data-auto-rotate"
        ]
      };
      collectComponents = (component, bucket) => {
        var _a;
        if (!component) {
          return;
        }
        bucket.push(component);
        const children = (_a = component.components) == null ? void 0 : _a.call(component);
        if (!children || typeof children.forEach !== "function") {
          return;
        }
        children.forEach((child) => collectComponents(child, bucket));
      };
      findComponentById = (editor, id) => {
        var _a, _b;
        const wrapper = (_a = editor.getWrapper) == null ? void 0 : _a.call(editor);
        if (!wrapper) {
          return null;
        }
        const all = [];
        collectComponents(wrapper, all);
        for (const component of all) {
          const attrs = ((_b = component.getAttributes) == null ? void 0 : _b.call(component)) || {};
          if (String(attrs.id || "").trim() === id) {
            return component;
          }
        }
        return null;
      };
      normalizeConfigValue = (value) => {
        if (typeof value === "boolean") {
          return value ? "true" : "false";
        }
        if (value == null) {
          return "";
        }
        return String(value);
      };
    }
  });

  // src/utils/export-handler.js
  function generateAnimationsJS(sections) {
    const gsapDefs = sections.filter((section) => section.type === "gsap-animated").map((section) => ({
      id: section.id,
      type: section.type,
      index: section.index,
      animation: section.attrs["data-gsap-animation"] || "fadeInUp",
      duration: toNumber(section.attrs["data-gsap-duration"], 1),
      delay: toNumber(section.attrs["data-gsap-delay"], 0),
      ease: section.attrs["data-gsap-ease"] || "power2.out",
      trigger: section.attrs["data-gsap-trigger"] || "scroll",
      children: toBool3(section.attrs["data-gsap-children"]),
      stagger: toNumber(section.attrs["data-gsap-stagger"], 0.1)
    }));
    const threeDefs = sections.filter((section) => section.type === "threejs-scene").map((section) => ({
      id: section.id,
      type: section.type,
      index: section.index,
      sceneType: section.attrs["data-scene-type"] || "particles",
      sceneColor: section.attrs["data-scene-color"] || "#5b8cff",
      sceneBg: section.attrs["data-scene-bg"] || "transparent",
      sceneHeight: Number.parseInt(section.attrs["data-scene-height"], 10) || 400,
      sceneSpeed: toNumber(section.attrs["data-scene-speed"], 1),
      particleCount: Number.parseInt(section.attrs["data-particle-count"], 10) || 120,
      overlay: toBool3(section.attrs["data-threejs-overlay"]),
      wireframe: toBool3(section.attrs["data-wireframe"]),
      autoRotate: toBool3(section.attrs["data-auto-rotate"])
    }));
    const hasGsap = gsapDefs.length > 0;
    const hasThree = threeDefs.length > 0;
    const parts = [];
    parts.push("/**");
    parts.push(" * Auto-generated animations runtime from GrapesJS LP Builder.");
    parts.push(" * Generated on: " + (/* @__PURE__ */ new Date()).toISOString());
    parts.push(" */");
    parts.push("");
    parts.push(buildGetFromVarsSource());
    parts.push("");
    parts.push(serializeSceneBuilderHelpers());
    parts.push("");
    parts.push('document.addEventListener("DOMContentLoaded", function () {');
    parts.push("    var gsapSections = " + JSON.stringify(gsapDefs, null, 4) + ";");
    parts.push("    var threeSections = " + JSON.stringify(threeDefs, null, 4) + ";");
    parts.push("");
    parts.push("    function resolveSection(def) {");
    parts.push("        if (!def) return null;");
    parts.push("        if (def.id) {");
    parts.push("            return document.getElementById(def.id);");
    parts.push("        }");
    parts.push('        var list = document.querySelectorAll("[data-gjs-type=\\"" + def.type + "\\"]");');
    parts.push("        return list[def.index] || null;");
    parts.push("    }");
    parts.push("");
    if (hasGsap) {
      parts.push("    if (window.gsap) {");
      parts.push('        if (window.ScrollTrigger && typeof window.gsap.registerPlugin === "function") {');
      parts.push("            window.gsap.registerPlugin(window.ScrollTrigger);");
      parts.push("        }");
      parts.push("");
      parts.push("        gsapSections.forEach(function (def) {");
      parts.push("            var element = resolveSection(def);");
      parts.push("            if (!element) return;");
      parts.push("");
      parts.push("            var targets = def.children ? Array.prototype.slice.call(element.children || []) : element;");
      parts.push("            var vars = Object.assign({}, getFromVars(def.animation), {");
      parts.push("                duration: def.duration,");
      parts.push("                delay: def.delay,");
      parts.push("                ease: def.ease");
      parts.push("            });");
      parts.push("");
      parts.push("            if (def.children && Array.isArray(targets) && targets.length > 1) {");
      parts.push("                vars.stagger = def.stagger;");
      parts.push("            }");
      parts.push("");
      parts.push('            if (def.trigger === "scroll" && window.ScrollTrigger) {');
      parts.push('                vars.scrollTrigger = { trigger: element, start: "top 80%", toggleActions: "play none none reset" };');
      parts.push("                window.gsap.from(targets, vars);");
      parts.push('            } else if (def.trigger === "hover") {');
      parts.push('                element.addEventListener("mouseenter", function () { window.gsap.from(targets, vars); });');
      parts.push('            } else if (def.trigger === "click") {');
      parts.push('                element.addEventListener("click", function () { window.gsap.from(targets, vars); });');
      parts.push("            } else {");
      parts.push("                window.gsap.from(targets, vars);");
      parts.push("            }");
      parts.push("        });");
      parts.push("    }");
      parts.push("");
    }
    if (hasThree) {
      parts.push("    if (window.THREE) {");
      parts.push("        threeSections.forEach(function (def) {");
      parts.push("            var element = resolveSection(def);");
      parts.push("            if (!element) return;");
      parts.push("            buildScene(element, def);");
      parts.push("        });");
      parts.push("    }");
      parts.push("");
    }
    parts.push("});");
    return parts.join("\n");
  }
  function exportTemplate(editor) {
    var _a, _b;
    const html = String(((_a = editor == null ? void 0 : editor.getHtml) == null ? void 0 : _a.call(editor)) || "");
    const css = String(((_b = editor == null ? void 0 : editor.getCss) == null ? void 0 : _b.call(editor)) || "");
    const doc = parseHtml(html);
    const sections = collectSections(doc);
    const animationsJS = generateAnimationsJS(sections);
    return {
      html,
      css,
      animationsJS
    };
  }
  var requiredAttrsByType, toBool3, toNumber, parseHtml, collectSections, buildGetFromVarsSource;
  var init_export_handler = __esm({
    "src/utils/export-handler.js"() {
      init_scene_builder();
      requiredAttrsByType = {
        "gsap-animated": [
          "data-gsap-animation",
          "data-gsap-duration",
          "data-gsap-delay",
          "data-gsap-ease",
          "data-gsap-trigger",
          "data-gsap-children",
          "data-gsap-stagger"
        ],
        "threejs-scene": [
          "data-scene-type",
          "data-scene-color",
          "data-scene-bg",
          "data-scene-height",
          "data-scene-speed",
          "data-particle-count",
          "data-threejs-overlay",
          "data-wireframe",
          "data-auto-rotate"
        ]
      };
      toBool3 = (value) => String(value || "").trim().toLowerCase() === "true";
      toNumber = (value, fallback) => {
        const parsed = Number.parseFloat(value);
        return Number.isFinite(parsed) ? parsed : fallback;
      };
      parseHtml = (html) => {
        const parser = new DOMParser();
        return parser.parseFromString("<!doctype html><html><body>" + html + "</body></html>", "text/html");
      };
      collectSections = (doc) => {
        const nodes = Array.from(doc.querySelectorAll("[data-gjs-type]"));
        const counters = /* @__PURE__ */ new Map();
        return nodes.map((node) => {
          const type = String(node.getAttribute("data-gjs-type") || "").trim();
          const index = counters.get(type) || 0;
          counters.set(type, index + 1);
          const requiredAttrs = requiredAttrsByType[type] || [];
          const attrs = {};
          requiredAttrs.forEach((key) => {
            attrs[key] = String(node.getAttribute(key) || "");
          });
          return {
            id: String(node.getAttribute("id") || "").trim(),
            type,
            index,
            attrs
          };
        });
      };
      buildGetFromVarsSource = () => [
        "function getFromVars(animation) {",
        '    switch (String(animation || "")) {',
        '    case "fadeInDown": return { opacity: 0, y: -40 };',
        '    case "fadeInLeft": return { opacity: 0, x: -40 };',
        '    case "fadeInRight": return { opacity: 0, x: 40 };',
        '    case "slideInLeft": return { opacity: 0, x: -120 };',
        '    case "slideInRight": return { opacity: 0, x: 120 };',
        '    case "zoomIn": return { opacity: 0, scale: 0.7 };',
        '    case "zoomOut": return { opacity: 0, scale: 1.25 };',
        '    case "bounceIn": return { opacity: 0, scale: 0.35 };',
        '    case "flipInX": return { opacity: 0, rotateX: 90, transformPerspective: 600 };',
        '    case "flipInY": return { opacity: 0, rotateY: 90, transformPerspective: 600 };',
        '    case "rotateIn": return { opacity: 0, rotate: -15, transformOrigin: "center center" };',
        '    case "fadeInUp":',
        "    default: return { opacity: 0, y: 40 };",
        "    }",
        "}"
      ].join("\n");
    }
  });

  // src/index.js
  function lpBuilderPlugin(editor, pluginOptions = {}) {
    const options = { ...DEFAULT_OPTIONS, ...pluginOptions || {} };
    registerStandardSection(editor);
    if (options.gsap) {
      registerGsapAnimated(editor);
    }
    if (options.threejs) {
      registerThreejsScene(editor);
    }
    registerAnimationControls(editor);
    registerSectionsNavigator(editor);
    editor.on("load", () => {
      ensureRuntime(editor, options);
    });
    editor.on("canvas:frame:load", () => {
      ensureRuntime(editor, options);
    });
  }
  var PLUGIN_ID, DEFAULT_OPTIONS, debugLog, buildRuntimeUrls, injectScript, dispatchReady, ensureRuntime, src_default;
  var init_src = __esm({
    "src/index.js"() {
      init_standard_section();
      init_gsap_animated();
      init_threejs_scene();
      init_animation_controls();
      init_sections_navigator();
      init_badge_injector();
      init_manifest_loader();
      init_export_handler();
      PLUGIN_ID = "grapesjs-lp-builder";
      DEFAULT_OPTIONS = Object.freeze({
        gsap: true,
        threejs: true,
        gsapVersion: "3.12.2",
        threeVersion: "r128",
        onReady: null,
        debug: false
      });
      debugLog = (options, ...args) => {
        if (options.debug) {
          console.info("[grapesjs-lp-builder]", ...args);
        }
      };
      buildRuntimeUrls = (options) => {
        const gsapVersion = options.gsapVersion || DEFAULT_OPTIONS.gsapVersion;
        const threeVersion = options.threeVersion || DEFAULT_OPTIONS.threeVersion;
        return [
          `https://cdnjs.cloudflare.com/ajax/libs/gsap/${gsapVersion}/gsap.min.js`,
          `https://cdnjs.cloudflare.com/ajax/libs/gsap/${gsapVersion}/ScrollTrigger.min.js`,
          `https://cdnjs.cloudflare.com/ajax/libs/three.js/${threeVersion}/three.min.js`
        ];
      };
      injectScript = (doc, src) => new Promise((resolve, reject) => {
        const existing = doc.querySelector(`script[data-lp-builder-src="${src}"]`) || Array.from(doc.querySelectorAll("script[src]")).find((node) => node.getAttribute("src") === src);
        if (existing) {
          if (existing.getAttribute("data-lp-builder-loaded") === "true") {
            resolve(existing);
            return;
          }
          const onLoad = () => {
            existing.setAttribute("data-lp-builder-loaded", "true");
            resolve(existing);
          };
          const onError = () => reject(new Error("Failed to load: " + src));
          existing.addEventListener("load", onLoad, { once: true });
          existing.addEventListener("error", onError, { once: true });
          return;
        }
        const script = doc.createElement("script");
        script.async = false;
        script.src = src;
        script.setAttribute("data-lp-builder-src", src);
        script.setAttribute("data-lp-builder-loaded", "false");
        script.onload = () => {
          script.setAttribute("data-lp-builder-loaded", "true");
          resolve(script);
        };
        script.onerror = () => {
          reject(new Error("Failed to load: " + src));
        };
        doc.body.appendChild(script);
      });
      dispatchReady = (editor, options, win, doc) => {
        if (!doc || doc.__lpBuilderReadyDispatched) {
          return;
        }
        doc.__lpBuilderReadyDispatched = true;
        doc.dispatchEvent(new win.CustomEvent("lp:ready", { detail: { plugin: PLUGIN_ID } }));
        if (typeof options.onReady === "function") {
          options.onReady(editor, { win, doc });
        }
      };
      ensureRuntime = async (editor, options) => {
        var _a, _b, _c, _d;
        const doc = (_b = (_a = editor.Canvas).getDocument) == null ? void 0 : _b.call(_a);
        const win = (_d = (_c = editor.Canvas).getWindow) == null ? void 0 : _d.call(_c);
        if (!doc || !win || !doc.body) {
          return;
        }
        if (doc.__lpBuilderScriptsLoading) {
          return;
        }
        doc.__lpBuilderScriptsLoading = true;
        const urls = buildRuntimeUrls(options);
        try {
          for (const src of urls) {
            await injectScript(doc, src);
          }
          injectLpBuilderCanvasStyles(doc);
          dispatchReady(editor, options, win, doc);
          debugLog(options, "Runtime loaded and lp:ready dispatched.");
        } catch (error) {
          debugLog(options, error.message || "Runtime load failed.");
        } finally {
          doc.__lpBuilderScriptsLoading = false;
        }
      };
      lpBuilderPlugin.pluginName = PLUGIN_ID;
      lpBuilderPlugin.id = PLUGIN_ID;
      lpBuilderPlugin.loadManifest = loadManifest;
      lpBuilderPlugin.exportTemplate = exportTemplate;
      src_default = lpBuilderPlugin;
    }
  });

  // tmp-dist-entry.js
  var require_tmp_dist_entry = __commonJS({
    "tmp-dist-entry.js"(exports, module) {
      init_src();
      src_default.loadManifest = loadManifest;
      src_default.exportTemplate = exportTemplate;
      if (typeof window !== "undefined") window.gjsLPBuilder = src_default;
      if (typeof module !== "undefined" && module.exports) module.exports = src_default;
    }
  });
  require_tmp_dist_entry();
})();
