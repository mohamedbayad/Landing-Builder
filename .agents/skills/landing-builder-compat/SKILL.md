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
| **Tailwind CSS** | Via `/js/tailwind.js` |
| **Alpine.js** | Via Vite (`app.js`) |
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
- **ALWAYS** put all JavaScript in the `js` field, not inline
- **ALWAYS** put external scripts/fonts in `custom_head`
