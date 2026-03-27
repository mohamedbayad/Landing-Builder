---
name: landing-page-design
description: Defines the visual design system for high-converting landing pages including theming, typography, and responsive rules.
---

# Landing Page Design System

Design for premium perceived value first, then clarity.
Target the visual standard of high-ticket DTC brands: clean, confident, minimal, and intentional.

## Design Direction

Use this aesthetic baseline:
- premium spacing and breathing room,
- bold typography hierarchy,
- controlled contrast,
- subtle depth and texture,
- product-first visual storytelling.

Avoid generic template patterns.

## Visual Rules (High-Ticket)

1. Strong spacing:
- Desktop section spacing: 96-140px vertical.
- Mobile section spacing: 64-88px vertical.
- Keep generous negative space around hero and offer blocks.

2. Typography hierarchy:
- Hero headline: clamp(2.4rem, 5vw, 5rem), 700-800 weight.
- Section title: clamp(1.7rem, 3vw, 3rem), 600-700 weight.
- Body: 1rem-1.125rem with short paragraphs.

3. Premium surfaces:
- Use soft shadows and subtle edge highlights.
- Optional glassmorphism only for cards that contain trust/proof/offer info.
- Limit decorative effects to one visual motif per page.

4. Color strategy:
- Base on Product Identity Profile dominant colors.
- One primary accent + one support accent max.
- Use subtle gradients (5-12% contrast change), never loud rainbow effects.

5. Text economy:
- Minimal text per visual block.
- One core message per section.

## Layout Rules

1. Hero must feel expensive:
- Strong headline and controlled subcopy.
- Product shown large or in strong contextual use.
- Trust strip visible near primary CTA.

2. Use asymmetry intentionally:
- Offset image/text blocks.
- Vary section rhythms (not repetitive equal-height blocks).
- Maintain visual balance with whitespace and anchors.

3. Section identity:
- Each major section gets a clear visual treatment (background shift, card system, divider, or media pattern).
- Keep conversion path obvious despite visual variation.

4. Anti-template enforcement:
- Do not use identical card grids for 4+ consecutive sections.
- Do not keep all sections center-aligned.
- Do not repeat the same background treatment for entire page.

## Typography System

Preferred premium stack:
```css
--font-heading: 'Sora', 'DM Sans', sans-serif;
--font-body: 'Manrope', 'Inter', sans-serif;
--font-accent: 'Cormorant Garamond', 'Playfair Display', serif;
```

Rules:
- Headlines: short, high impact.
- Paragraphs: 2-3 lines max on desktop where possible.
- Eyebrows and metadata can use uppercase; body and headlines should not be all caps.

## Component Styling Rules

- Cards: 14-20px radius, subtle shadows, light border definition.
- Buttons: strong contrast, clear hover/press states, no novelty shapes.
- Media frames: rounded corners and realistic shadow depth.
- Icons: simple line icon set only, consistent stroke.

## Responsive Rules

- Preserve hierarchy on mobile (headline remains dominant).
- Collapse complex asymmetry into intentional stack order.
- Keep primary CTA visible without excessive scrolling.
- Touch targets 48px minimum.

## Non-Negotiable Rules

- Do not output flat, low-contrast, low-depth layouts.
- Do not use cluttered badge stacks or visual noise.
- Do not ignore product identity colors and perceived value tier.
- Do not let aesthetics reduce readability or conversion flow.
- Always design for perceived value uplift and trust.
