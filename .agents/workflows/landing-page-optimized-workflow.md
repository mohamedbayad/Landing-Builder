---
description: Simplified, AI-friendly, conversion-driven workflow for landing page generation.
---

# 🚀 Optimized Landing Page Generation Workflow

A lean, conversion-first orchestration layer. This workflow coordinates specialized skills across 6 distinct phases to produce high-converting landing pages.

**Input required:** Product image & short description.

---

## 🧭 System Architecture & Data Flow

```text
 PHASE 1: RESEARCH    (product-image-safety, product-research)
        ↓
 PHASE 2: STRATEGY    (landing-page-conversion-engine) ← THE BRAIN
        ↓
 PHASE 3: STRUCTURE   (landing-page-structure, conversion-copywriting)
        ↓
 PHASE 4: VISUALS     (ad-creative-strategy, ugc-image-generation)
        ↓
 PHASE 5: BUILD       (landing-builder-compat, frontend-tailwind-system)
        ↓
 PHASE 6: VALIDATE    (frontend-security)
```

> **🔥 THE GOLDEN RULE**
> `landing-page-conversion-engine` (Phase 2) is the absolute **SINGLE SOURCE OF TRUTH**.
> All downstream skills (Copy, Structure, Visuals) MUST blindly obey its strategic blueprint. DO NOT guess, DO NOT overwrite, DO NOT duplicate its logic.

---

## 🛠 PHASE 1 — INPUT & RESEARCH
**Goal:** Extract facts and market data without making strategic decisions.
**Skills Used:** `product-image-safety`, `product-research`

// turbo-all
1. **Safety & Identity:** Use `product-image-safety` to extract the **Product Identity Lock** from the user's image. (If the image is unusable, halt and ask the user).
2. **Data Mining:** Use `product-research` to gather raw market facts (features, competitors, pricing, demographics, and initial pain points).

**Outputs passed forward:** 
✅ Product Identity Lock
✅ Product Profile (raw market facts)

---

## 🧠 PHASE 2 — THE CONVERSION ENGINE (CORE BRAIN)
**Goal:** Make ALL strategic decisions regarding marketing psychology and persuasion.
**Skills Used:** `landing-page-conversion-engine`

// turbo-all
3. **Generate Strategy Blueprint:** Execute `landing-page-conversion-engine` using the Product Profile from Phase 1. 

**Outputs passed forward:**
✅ **Conversion Blueprint** (Strict definitions for: Core Angle, Pain/Desire, Objection Map, Proof Strategy, Offer Mechanics, CTA Logic, Image Directions).

---

## 🏗 PHASE 3 — STRUCTURE & COPY
**Goal:** Translate the blueprint into persuasive structure and text.
**Skills Used:** `landing-page-structure`, `conversion-copywriting`

// turbo-all
4. **Determine Layout:** Apply `landing-page-structure` to select the page skeleton components (Hero, Features, Proof, Offer, FAQ).
5. **Write Copy:** Execute `conversion-copywriting`. 
   * **Rule:** You MUST map the copy directly to the *Objection Map* and *Offer Mechanics* dictated by the Phase 2 Blueprint. Do not invent new angles.

**Outputs passed forward:**
✅ Section Architecture
✅ Finalized Landing Page Text (HTML-ready)

---

## 🎨 PHASE 4 — IMAGE STRATEGY & GENERATION
**Goal:** Generate targeted visuals that support the psychological narrative.
**Skills Used:** `ad-creative-strategy`, `ugc-image-generation`

// turbo-all
6. **Visual Planning:** Execute `ad-creative-strategy` using ONLY the *Image Directions* from the Phase 2 Blueprint. Formulate strict prompts.
7. **Image Execution:** Call `ugc-image-generation` using the prompts and the Phase 1 *Product Identity Lock*. 
   * **Failsafe:** If photorealistic generation fails the identity check, gracefully fallback to compositing or using the original unedited image.

**Outputs passed forward:**
✅ Final Image Assets (Paths)

---

## 💻 PHASE 5 — BUILD & STYLE (HTML/CSS)
**Goal:** Compile the finalized text and images into modular, builder-ready code.
**Skills Used:** `frontend-tailwind-system`, `landing-builder-compat`

// turbo-all
8. **Theme & Styling:** Apply `frontend-tailwind-system` to generate the color palette (derived from product), typography, and UI component styling.
9. **Builder Assembly:** Package the outputs into a strict JSON payload via `landing-builder-compat` (Contains `html`, `css`, `js`, and `custom_head`). 

**Outputs passed forward:**
✅ Renderable JSON Code Package

---

## 🛡 PHASE 6 — VALIDATION (LIGHT PASS)
**Goal:** Quick systematic sanity check before handoff.
**Skills Used:** `frontend-security`

// turbo-all
10. **Security & Cleanup:** Run `frontend-security` to ensure no raw `onclick` handlers, unsafe external links, or broken tags exist in the final payload.
11. **SEO Basics:** Ensure ONLY ONE `<h1>` tag exists and standard meta title/description are present.

**Outputs passed forward:**
✅ **FINAL DELIVERABLE READY FOR THE USER.**
