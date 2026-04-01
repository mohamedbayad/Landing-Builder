---
name: landing-builder-compat
description: Ensures landing page HTML/CSS is compatible with LandingBuilder system (Laravel + GrapesJS).
---

# Landing Builder Compatibility Rules

Ensures generated landing pages are 100% compatible with the LandingBuilder system (Laravel backend + GrapesJS editor). Follow these rules to avoid import failures and broken functionality.

---

## When to use this skill

- When generating landing page HTML intended for import into LandingBuilder
- When structuring HTML/CSS/JS for the GrapesJS editor
- When adding forms, CTAs, cart buttons, or countdowns to a landing page
- When building a Thank You / confirmation page

## How to use it

### Template Import Structure

LandingBuilder imports templates via JSON:

```json
{
  "html": "<body content HTML only>",
  "css": "body-level CSS",
  "js": "body-level JavaScript",
  "custom_head": "<link>, <script>, and <style> tags for the <head>"
}
```

**Critical rules:**
- `html` = body content ONLY (no `<html>`, `<head>`, `<body>` tags)
- `css` = injected into a `<style>` tag in `<head>`
- `js` = executed in a `<script>` tag after body content
- `custom_head` = external scripts/styles for `<head>` (fonts, libraries)

### Already Provided by the System

Do NOT include — these are auto-loaded:

| Feature | Detail |
|---|---|
| **Tailwind CSS** | Via CDN CSS link (no runtime compiler) |
| **Alpine.js** | Conditionally loaded (cart feature) |
| **CSRF Token** | `<meta name="csrf-token">` |
| **Analytics** | `/js/analytics.js` — auto-tracks clicks, scroll, heartbeat |
| **Countdown** | `/js/countdown.js` |
| **Form Handler** | Auto-injects `_token` and `landing_id` into all `<form>` |

### Forms

```html
<form action="/api/forms/submit" method="POST">
  <input type="text" name="full_name" placeholder="Full name">
  <input type="email" name="email" placeholder="Email">
  <input type="tel" name="phone" placeholder="Phone">
  <button type="submit">Submit</button>
</form>
```

**Rules:** `action="/api/forms/submit"` is mandatory. Use `method="POST"`. Name fields explicitly: `full_name`, `email`, `phone`, `city`, `address`, `message`.

### CTA Tracking

Analytics auto-detects clicks on `.cta`, `.track-cta`, `data-track`, all `<a>` and `<button>`.

```html
<a href="#contact" class="cta" data-track="cta_hero" data-position="hero">
  Order Now
</a>
```

### Cart Button

```html
<button class="btn-add-cart"
        data-product-label="Product Name"
        data-price="89.00"
        data-product-id="1">
  Add to Cart
</button>
```

### Countdown

```html
<div class="countdown" data-target="2026-12-31T23:59:59"></div>
```

### Images

- External URLs are downloaded and stored locally on import
- Use optimized formats (WebP/JPEG), always include `alt` attributes
- Use `loading="lazy"` for below-the-fold images
- Keep under 500 KB

### HTML Best Practices

```html
<section id="hero" data-section="hero">
  <div class="container mx-auto px-4">
    <h1>Main Headline</h1>
    <a href="#contact" class="cta" data-track="cta_hero">Order</a>
  </div>
</section>
```

Use semantic sections with `id` and `data-section` attributes. Clean, semantic HTML (h1, h2, section, nav, footer).

### Thank You Page Element IDs

| Element ID | Data Injected |
|---|---|
| `crm-order-id` | Order number |
| `crm-fullname` | Customer name |
| `crm-email` | Email |
| `crm-phone` | Phone |
| `crm-total` | Total amount |
| `crm-invoice-btn` | Invoice download link |

---

## Examples

### Example 1 — Lead Capture Form

**Input:** Need a simple email capture  
**Output:**
```html
<form action="/api/forms/submit" method="POST">
  <input type="text" name="full_name" placeholder="Your name" required>
  <input type="email" name="email" placeholder="Email address" required>
  <button type="submit" class="cta" data-track="cta_form_submit">Get Started</button>
</form>
```

### Example 2 — Product CTA with Tracking

**Input:** Hero CTA button  
**Output:**
```html
<a href="#offer" class="cta" data-track="cta_hero_buy" data-position="hero">
  Order Now — 50% Off
</a>
```

---

## Constraints

- **Do NOT** include Tailwind, Alpine.js, or jQuery — already loaded
- **Do NOT** put `<script>` tags in the HTML field — stripped on import
- **Do NOT** use inline `onclick` handlers — stripped on import
- **Do NOT** use `document.write()` — incompatible with renderer
- **Do NOT** create forms without `action="/api/forms/submit"` — leads won't be captured
- **Do NOT** use multiple `<h1>` tags on the same page — bad for SEO
- **Do NOT** link images with external URLs — download and store locally
- **Do NOT** output slider/carousel sections as monolithic HTML blobs without `lp-slider` markers/classes
- **ALWAYS** put all JavaScript in the `js` field, not inline
- **ALWAYS** put external scripts/fonts in `custom_head`

---

## Editor-Aware HTML Rules

The LandingBuilder editor includes advanced editing controls: **Tag Changer**, **Attributes Manager**, **Class Manager**, **Flexbox/Grid controls**, and **Semantic/SEO panel**. Generated HTML MUST be compatible with these controls.

### Semantic Tag Flexibility

Generated HTML must support future tag conversion by the user:
- `div` ↔ `section` / `article` / `aside` / `header` / `footer` / `nav`
- `p` ↔ `h1` / `h2` / `h3` / `h4` / `h5` / `h6` / `blockquote`
- `a` ↔ `button`
- `ul` ↔ `ol`

**Rules:**
1. Keep editable text content in **leaf elements** — not deeply nested inside multiple wrappers
2. Use clean, semantic tags — avoid `<div>` when `<section>`, `<article>`, or `<header>` is more appropriate
3. Avoid unnecessary wrapper divs — each structural layer must serve a purpose (container, flex parent, padding box)
4. **Max 3 nesting levels** between a section root and editable text content
5. Use proper heading hierarchy: one `<h1>` per page, then `<h2>`, `<h3>`, etc.

**Good:**
```html
<section id="hero" data-section="hero" class="py-20 bg-gray-900">
  <div class="container mx-auto px-4 text-center">
    <h1 class="text-5xl font-bold text-white">Main Headline</h1>
    <p class="mt-4 text-xl text-gray-300">Supporting description text</p>
    <a href="#offer" class="cta mt-8 inline-block px-8 py-4 bg-indigo-600 text-white rounded-lg" data-track="cta_hero">Order Now</a>
  </div>
</section>
```

**Bad (over-nested, non-semantic, hard to edit):**
```html
<div>
  <div>
    <div>
      <div>
        <div><span><b>Headline</b></span></div>
      </div>
    </div>
  </div>
</div>
```

### Attribute Compatibility

Generated HTML must include meaningful attributes that the editor can display and modify:

| Attribute | Usage | Required |
|-----------|-------|----------|
| `id` | Unique per section. Use descriptive names: `hero`, `features`, `pricing` | ✅ on sections |
| `data-section` | Analytics section tracking. Matches section `id` | ✅ on sections |
| `data-track` | CTA click tracking. Format: `cta_{section}_{action}` | ✅ on CTAs |
| `class` | Tailwind utility classes. Keep organized and readable | ✅ always |
| `href` | On links — use anchors `#offer` or full URLs | ✅ on links |
| `src` / `alt` | On images. `alt` must be descriptive for SEO/accessibility | ✅ on images |
| `role` | ARIA roles for accessibility (`banner`, `navigation`, `main`, etc.) | Recommended |
| `aria-label` | Accessible labels for interactive elements | Recommended |
| `loading` | Image lazy loading: `lazy` for below-fold, `eager` for hero | Recommended |
| `data-gsap-section` | Marks a GSAP/ScrollTrigger section so editor-safe mode can sanitize/export correctly | ✅ required on GSAP sections |
| `data-gsap-item` | Optional marker for animated items/slides inside a GSAP section (editor reveal + save cleanup) | Recommended |
| `data-*` | Custom data for JS features (cart, countdown, etc.) | As needed |

### GSAP Animated Sections (Editor-Safe Contract)

If a section uses GSAP/ScrollTrigger (pin, scrub, slide transitions), always mark it:

```html
<section id="solution" data-section="solution" data-gsap-section="solution" class="min-h-screen ...">
  <div data-gsap-item>...</div>
  <div data-gsap-item>...</div>
</section>
```

Rules:
1. Add `data-gsap-section="{name}"` on the GSAP section root.
2. Keep a stable height baseline on the section root (`min-h-screen` or explicit `min-h-[...]`).
3. For animated cards/slides, add `data-gsap-item` (or use `.slide-solution`).
4. Do not rely on runtime pin inline styles as source-of-truth; they are temporary.

### CSS Editability Rules

Generated HTML must be easy to restyle via the editor's Style Manager (Layout, Flexbox, Position, Dimension, Typography, Background, Borders, Effects).

**Do:**
- Use Tailwind utility classes for base styling (the editor preserves them)
- Keep flex/grid containers simple: `flex`, `items-center`, `justify-between`, `gap-4`
- Use standard CSS properties that the Style Manager controls
- Keep responsive breakpoints via Tailwind (e.g., `md:flex`, `lg:grid-cols-3`)

**Don't:**
- Don't use deeply nested CSS selectors that fight the editor
- Don't use `!important` in inline styles — blocks the Style Manager
- Don't use CSS-in-JS, CSS variables, or custom properties in inline styles
- Don't hardcode widths/heights in inline styles — use Tailwind utilities or leave for the editor
- Don't combine multiple unrelated styles in a single wrapper — separate layout from decoration

### Section Structure Template

Every section should follow this pattern:

```html
<section id="{section-name}" data-section="{section-name}" class="{spacing} {bg-color}">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Section content: headings, text, CTAs, images -->
  </div>
</section>
```

- `id` + `data-section` = required for analytics and editor identification
- Outer `<section>` = background, vertical spacing
- Inner `<div>` = container, horizontal padding, max-width
- Content elements = clean, editable, properly tagged

### LP Slider / Gallery Contract (`lp-slider`)

When a section is a gallery, logo strip, testimonial carousel, product showcase slider, or UGC visual rail, output it with the `lp-slider` component contract.

**Required markers:**
- `data-component="lp-slider"`
- `data-gjs-type="lp-slider"` (or builder-recognized component hint)
- `.lp-slider` root class

**Required structure:**
- `.lp-slider__track` container
- Repeated `.lp-slider__slide`
- `.lp-slider__image` in each slide
- Optional `.lp-slider__caption`

```html
<section id="social-proof" data-section="social-proof" class="py-16 bg-white">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="lp-slider"
         data-component="lp-slider"
         data-gjs-type="lp-slider"
         data-preset="social-proof"
         data-slides-desktop="4"
         data-slides-tablet="2"
         data-slides-mobile="1"
         data-space-between="14"
         data-arrows="true"
         data-dots="true"
         data-autoplay="true"
         data-loop="true">
      <div class="lp-slider__track">
        <div class="lp-slider__slide">
          <img class="lp-slider__image" src="/storage/..." alt="Customer result 1" loading="lazy">
          <div class="lp-slider__caption">Real customer result #1</div>
        </div>
      </div>
    </div>
  </div>
</section>
```

**Preset mapping rules:**
- logos section -> `data-preset="logos"`
- testimonial carousel -> `data-preset="testimonials"`
- product gallery slider -> `data-preset="product-showcase"`
- UGC/social proof slider -> `data-preset="social-proof"`
- generic image gallery -> `data-preset="gallery"`

**Responsive behavior attributes to include:**
- `data-slides-desktop`, `data-slides-tablet`, `data-slides-mobile`
- `data-space-between`
- `data-arrows`, `data-dots`
- `data-autoplay`, `data-autoplay-delay`, `data-loop`, `data-speed`
- `data-pause-on-hover`, `data-draggable`, `data-center-mode`, `data-initial-slide`

**Asset + serialization rules:**
- Use managed assets only (uploaded media, imported ZIP assets, stored AI-generated assets)
- Avoid temporary remote placeholders in final builder output
- Keep slider node hierarchy stable for save/load/export/import
- Keep marker attributes intact so plugin detection survives template reuse
