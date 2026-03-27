---
name: frontend-tailwind-system
description: Generates Tailwind CSS component patterns for landing pages with dark/light mode, responsive grids, and CTA styles.
---

# Frontend Tailwind CSS System

Defines how to use **Tailwind CSS** to build modern, responsive, high-converting landing pages. Use this skill specifically when building with Tailwind — for vanilla CSS, use `landing-page-design` instead.

---

## When to use this skill

- When building a landing page using Tailwind CSS (CDN or build system)
- When you need ready-to-copy Tailwind component patterns (hero, feature grid, testimonials, FAQ)
- When configuring Tailwind's `tailwind.config` for the landing page design system

## How to use it

### Setup — CDN (Quick)

```html
<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: ['selector', '[data-theme="dark"]'],
      theme: {
        extend: {
          fontFamily: {
            heading: ['"DM Sans"', 'sans-serif'],
            body: ['"Inter"', 'sans-serif'],
            accent: ['"Playfair Display"', 'serif'],
          },
          colors: {
            bg:      { DEFAULT: '#0C0C0E', elevated: '#161618', subtle: '#1C1C1F' },
            surface: { DEFAULT: '#FFFFFF', muted: '#F8F8FA' },
            accent:  { DEFAULT: '#2997FF', dark: '#0071E3' },
            text:    { primary: '#F5F5F7', secondary: '#A1A1A6', tertiary: '#6E6E73' },
          },
          borderRadius: { card: '16px', btn: '12px', sm: '8px' }
        }
      }
    }
  </script>
</head>
```

### Key Tailwind Patterns

**Split Hero:**
```html
<header class="w-full px-5 py-20 md:py-32">
  <div class="mx-auto grid max-w-[1140px] items-center gap-12 md:grid-cols-2">
    <div class="text-center md:text-left">
      <span class="mb-4 inline-block text-xs font-semibold uppercase tracking-[0.15em] text-accent">New Release</span>
      <h1 class="font-heading text-[clamp(2rem,5vw,4rem)] font-bold leading-[1.12] tracking-tight text-text-primary">
        Headline with <span class="font-accent italic text-accent">accent</span> word
      </h1>
      <a href="#" class="mt-8 inline-flex rounded-btn bg-accent px-8 py-4 text-sm font-semibold uppercase tracking-wider text-white transition hover:-translate-y-0.5">
        Order Now
      </a>
    </div>
    <div class="order-first md:order-last">
      <img src="product.webp" alt="Product" class="w-full rounded-card" />
    </div>
  </div>
</header>
```

**Feature Grid (3-Column):**
```html
<div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
  <div class="rounded-card border border-white/[0.06] bg-bg-elevated p-8 text-center transition hover:-translate-y-1">
    <div class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-accent/10 text-accent">
      <!-- SVG icon -->
    </div>
    <h3 class="mb-2 text-base font-semibold text-text-primary">Feature Title</h3>
    <p class="text-sm leading-relaxed text-text-secondary">One-line benefit.</p>
  </div>
</div>
```

**Primary CTA:**
```html
<a href="#" class="inline-flex items-center gap-2 rounded-btn bg-accent px-8 py-4 text-sm font-semibold uppercase tracking-wider text-white transition hover:-translate-y-0.5 hover:opacity-90">
  Order Now
</a>
```

### Responsive Rules

| Breakpoint | Prefix | Usage |
|---|---|---|
| Mobile | Default | Single column, full-width CTAs |
| Tablet | `sm:` / `md:` | 2-column grids, inline CTAs |
| Desktop | `lg:` / `xl:` | 3-column grids, max-width containers |

**Key patterns:** `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`, `text-center md:text-left`, `w-full md:w-auto`, `order-first md:order-last`.

---

## Examples

### Example 1 — Testimonial Card

```html
<div class="rounded-card border border-white/[0.06] bg-bg-elevated p-8 text-left">
  <div class="mb-4 text-sm tracking-wider text-amber-400">★★★★★</div>
  <p class="mb-5 text-sm italic leading-relaxed text-text-secondary">"Quote text."</p>
  <div class="flex items-center gap-3">
    <img src="avatar.webp" class="h-10 w-10 rounded-full object-cover" />
    <div>
      <div class="text-sm font-semibold text-text-primary">Full Name</div>
      <div class="text-xs text-text-tertiary">✓ Verified Buyer</div>
    </div>
  </div>
</div>
```

### Example 2 — Guarantee Card

```html
<div class="mx-auto max-w-xl rounded-[20px] border border-white/[0.06] bg-bg-elevated p-10 text-center">
  <h3 class="font-heading text-xl font-bold text-text-primary">Our Promise</h3>
  <p class="mt-3 text-sm leading-relaxed text-text-secondary">30-day money-back guarantee.</p>
</div>
```

---

## Constraints

- **Do NOT** hardcode colors — use theme tokens in `tailwind.config`
- **Do NOT** use arbitrary values for colors (`text-[#ff0000]`) — extend config
- **Do NOT** use emoji icons — SVG only (Lucide/Feather)
- **Do NOT** write custom CSS when Tailwind utilities exist
- **ALWAYS** add `dark:` counterparts when using Tailwind with `data-theme`
- **ALWAYS** apply `rounded-card` + `w-full` + `object-cover` to images
