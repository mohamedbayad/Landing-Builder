---
name: landing-page-structure
description: Advanced adaptive landing page structure system — a CRO decision engine that selects, orders, and configures page sections based on product psychology, traffic intent, awareness level, and conversion logic.
---

# Landing Page Structure Intelligence System

An **advanced decision engine** that produces the correct landing page structure based on product psychology, traffic intent, and conversion logic. This is NOT a template — it is a system that **thinks like a senior CRO strategist**.

> **Prerequisite:** Use `product-research` to gather the Product Profile and Marketing Intelligence BEFORE invoking this skill. This skill consumes the classification output to drive structural decisions.

---

## When to use this skill

- When planning the section order and flow for a new landing page
- When deciding which sections to include, exclude, or make conditional based on product psychology
- When adapting page structure to match traffic temperature or awareness level
- When engineering the offer section for maximum conversion
- When aligning landing page structure with a specific ad hook or angle

## How to use it

Follow the 6-step decision pipeline below. **Every step is mandatory.** Do NOT skip to structure selection without completing classification first.

---

## STEP 1 — Multi-Dimensional Product Classification

Before choosing ANY structure, classify the product across **all 5 dimensions**. These dimensions interact to produce the final structural decision.

### Dimension 1: Primary Buying Driver

What psychological force triggers the purchase? Determines the dominant narrative arc.

| Driver | Description | Structural Impact |
|---|---|---|
| **Pain Relief** | Eliminates a frustration or risk | Problem → Solution arc mandatory |
| **Convenience** | Saves time, effort, simplifies life | Use Cases + Comparison mandatory |
| **Beauty / Aesthetic** | Enhances appearance or self-image | Visual-first hero, Before/After if applicable |
| **Status / Luxury** | Signals wealth, taste, exclusivity | Lifestyle Showcase, aspirational framing |
| **Identity / Self-Expression** | "This is who I am" | Lifestyle + Community proof, NO problem framing |
| **Trust / Safety** | Protection, security, peace of mind | Authority proof, guarantees early, testimonial-heavy |
| **Aspiration** | Future self, goals, transformation | Transformation narrative, Before/After |

### Dimension 2: Product Nature

| Nature | Description | Impact |
|---|---|---|
| **Physical** | Tangible, shipped product | Hero must showcase product, shipping/COD details in offer |
| **Digital** | Software, course, download, template | Demo/preview mandatory, instant access as benefit |
| **Service** | Consultation, coaching, done-for-you | Authority + process section, lead gen structure |
| **Hybrid** | Physical + digital bundle / subscription | Value stack must separate tangible + intangible |

### Dimension 3: Proof Requirement

What evidence does the buyer need to believe? Determines which proof sections to include and where.

| Proof Type | When to Use | Section Triggered |
|---|---|---|
| **Demo / How-It-Works** | Product mechanism is unclear → must show it in action | Use Cases / How It Works |
| **Before/After** | Visual transformation is the selling point | Before & After (Slot 6) |
| **Testimonials** | Social validation drives purchase in this category | Social Proof (elevated position) |
| **Authority** | High-trust category (health, finance, security) | Authority Bar + Expert section |
| **Craftsmanship** | Handmade, artisan, materials matter | Craftsmanship (Slot 6) |
| **Comparison** | Replacing an inferior method or competitor | Comparison (Slot 5.5) |

### Dimension 4: Offer Type

How is the transaction completed? Determines offer section layout and CTA wording.

| Offer Type | CTA Wording | Offer Section |
|---|---|---|
| **Direct Purchase** | "Buy Now", "Order Now" | Price + checkout |
| **COD (Cash on Delivery)** | "Order Now – Pay on Delivery" | Form with COD badge, no payment fields |
| **WhatsApp / Messenger** | "Order via WhatsApp" | WhatsApp link + quick info |
| **Lead Generation** | "Get Free [Resource]", "Book a Call" | Minimal form, no price |
| **Consultation** | "Schedule Your Free Consultation" | Calendar/form, authority-heavy |
| **Subscription** | "Start Your Plan" | Pricing tiers, comparison table |

### Dimension 5: Price Sensitivity

| Tier | Range | Structural Implication |
|---|---|---|
| **Low-Ticket Impulse** | <$30 | Shorter page, fewer objections, fast CTA, impulse triggers |
| **Mid-Ticket Considered** | $30–$150 | Full structure, balanced proof + offer |
| **High-Ticket** | $150+ | Long-form, maximum proof, multiple CTAs, strong guarantee, FAQ heavy |

### Classification Output Format

```markdown
## Product Classification

- **Buying Driver:** [Pain Relief / Convenience / Beauty / Status / Identity / Trust / Aspiration]
- **Product Nature:** [Physical / Digital / Service / Hybrid]
- **Proof Requirement:** [Demo / Before-After / Testimonial / Authority / Craftsmanship / Comparison] (can be multiple)
- **Offer Type:** [Direct Purchase / COD / WhatsApp / Lead Gen / Consultation / Subscription]
- **Price Tier:** [Low-Ticket Impulse / Mid-Ticket / High-Ticket]
```

---

## STEP 2 — Traffic & Awareness Layer

Determine the audience's **traffic temperature** and **awareness level**. These directly modify section order, depth, and aggressiveness.

### Traffic Temperature

| Temperature | Source | Structural Effect |
|---|---|---|
| **Cold** | Facebook/Instagram/TikTok ads, discovery | Longest page. Maximum education. Problem section expanded. Proof stacked heavily. Soft initial CTA. |
| **Warm** | Retargeted visitors, email list, organic search | Mid-length page. Less education needed. Lead with solution + social proof. Stronger CTA sooner. |
| **Hot / Retargeting** | Cart abandoners, repeat visitors, remarketing | Short page. Skip problem/education. Lead with offer + urgency + testimonials. Aggressive CTA immediately. |

### Awareness Level Integration

This skill consumes the awareness level from `product-research` (Step 6) and adapts accordingly:

| Awareness Level | Hero Approach | Problem Section | Proof Position | CTA Timing |
|---|---|---|---|---|
| **Unaware** | Curiosity hook, NOT product name | Expanded (educate the problem first) | Late (after education) | Delayed, after problem established |
| **Problem Aware** | Name the pain in headline | Present but concise (they know it) | Mid-page (validate their frustration) | After solution reveal |
| **Solution Aware** | Lead with differentiator | Brief or skip | Early + frequent (why THIS solution) | Multiple, starting mid-page |
| **Product Aware** | Lead with offer / social proof | Skip entirely | Immediate, hero area | Immediate + repeated |
| **Most Aware** | Lead with the deal | Skip entirely | Quick trust signals only | Above the fold, aggressive |

### Combined Modifier Rules

```
IF cold_traffic AND unaware:
  → Maximum length page
  → Problem section = 3 pain points + emotional hook
  → First CTA appears AFTER solution reveal
  → Minimum 2 proof sections before offer

IF warm_traffic AND solution_aware:
  → Medium length page
  → Lead hero with "why us" differentiator
  → First CTA in hero
  → Comparison section mandatory (vs. competitors)

IF hot_traffic AND product_aware:
  → Short page (hero + proof + offer + FAQ)
  → Hero = offer headline + product image + CTA
  → Skip problem, solution, features sections
  → Testimonials immediately after hero
```

---

## STEP 3 — Structure Selection (Decision Logic)

Based on Steps 1 + 2, select the appropriate **structure archetype**. Each archetype has **Mandatory**, **Optional**, and **Conditional** sections.

### Archetype A — Problem→Solution (Pain Relief / Convenience / Safety)

Best for: Tech gadgets, automotive tools, cleaning products, security devices, health products.

**Mandatory Sections:**
```
1. HERO — Clear promise + product visual + CTA
2. PROOF BAR — Stats / logos / press
3. PROBLEM — Elegant pain agitation (3 points max)
4. SOLUTION — Product reveal as the answer
5. FEATURES — Benefits grid (4–6 max)
7. SOCIAL PROOF — Story-driven testimonials
8. OFFER — Value stack + risk reversal + CTA
9. FAQ — Kill final objections (4–6 questions)
10. FOOTER CTA — Last chance conversion
```

**Conditional Sections:**
| Section | Insert Position | Include WHEN |
|---|---|---|
| COMPARISON (Old vs New) | After Features (5.5) | Product replaces a manual/inferior method |
| USE CASES | After Features (6) | Product serves 3+ distinct scenarios |
| HOW IT WORKS | After Solution (4.5) | Product mechanism is non-obvious |
| AUTHORITY | After Proof Bar (2.5) | High-trust category (health, finance, security) |

**Optional Sections:**
- GUARANTEE (standalone card) — Include for mid/high-ticket
- MID-PAGE CTA — Include for long pages (7+ sections)

---

### Archetype B — Aspiration→Identity (Fashion / Lifestyle / Status)

Best for: Clothing, jewelry, accessories, luxury goods, lifestyle brands.

**Mandatory Sections:**
```
1. HERO — Full-bleed product aesthetic, aspirational feel
2. PROOF BAR — Heritage, quality badges, "Handcrafted in [Place]"
3. LIFESTYLE SHOWCASE — Product in aspirational environments (3 images)
4. FEATURES — Quality attributes as benefits (4 max)
7. SOCIAL PROOF — Style testimonials + UGC aesthetic
8. OFFER — Bundle/pricing + elegance
9. FAQ — Sizing, shipping, care, returns
10. FOOTER CTA — Final conversion
```

**Conditional Sections:**
| Section | Insert Position | Include WHEN |
|---|---|---|
| CRAFTSMANSHIP | After Proof Bar (2.5) | Handmade, artisan, or material story exists |
| MATERIALS | After Craftsmanship (3) | Physical materials are a selling point |
| HERITAGE / ORIGIN | After Lifestyle (4.5) | Authentic origin story adds value |
| COMFORT / FIT | After Features (5) | Wearable product, fit is an objection |

**Do NOT include:** Problem section. Comparison section. Use Case section. These kill the aspirational mood.

---

### Archetype C — Transformation (Beauty / Skincare / Fitness / Cleaning)

Best for: Products with visible before/after results.

**Mandatory Sections:**
```
1. HERO — The core promise of transformation
2. PROOF BAR — Stats, certifications, "dermatologist-tested"
3. PROBLEM — The concern being addressed (empathetic, not aggressive)
4. BEFORE & AFTER — Visual proof of transformation
5. BENEFITS — What it does for them (outcome-focused)
6. INGREDIENTS / COMPONENTS — What's inside, safety, science
7. SOCIAL PROOF — Transformation testimonials with timelines
8. OFFER — Kits, bundles, or subscription
9. FAQ — Suitability, routine, ingredients, timeline
10. FOOTER CTA — Final conversion
```

**Conditional Sections:**
| Section | Insert Position | Include WHEN |
|---|---|---|
| HOW TO USE | After Benefits (5.5) | Product requires a routine or specific application |
| COMPARISON | After Before/After (4.5) | Clear competitor contrast exists |
| EXPERT / AUTHORITY | After Proof Bar (2.5) | Doctor, dermatologist, or expert endorsement available |

---

### Archetype D — Value Exchange (Lead Generation / Consultation)

Best for: Lead magnets, free trials, consultation booking, service-based offers.

**Mandatory Sections:**
```
1. HERO HOOK — Benefit headline + form (3 fields max) + micro-trust
2. BENEFIT STACK — 3–5 scannable bullets + visual mockup
3. AUTHORITY & PROOF — Testimonials + logos + expert bio
4. CONVERSION REINFORCEMENT — Repeat CTA + FAQ (3–4 objections)
```

**Conditional Sections:**
| Section | Insert Position | Include WHEN |
|---|---|---|
| PROCESS / HOW IT WORKS | After Benefit Stack (2.5) | Multi-step service, client needs to understand the process |
| CASE STUDIES | After Authority (3.5) | Past results can be quantified |
| PRICING TABLE | After Benefits (2.5) | Multiple tiers available |

**Rules:** No navigation menu. No external links. Headline MUST match the ad that brought them. Form fields: absolute minimum (name + email/phone).

---

### Archetype E — Retargeting Short-Form

Best for: Cart abandoners, remarketing audiences, repeat visitors.

**Mandatory Sections:**
```
1. HERO — Offer headline + product image + CTA (above the fold)
2. SOCIAL PROOF — 2–3 featured testimonials
3. OFFER — Full value stack + urgency + guarantee + CTA
4. FAQ — 3 questions max (shipping, returns, guarantee)
```

**Rules:** Maximum 4–5 sections total. No education. No problem agitation. Lead with deal + proof.

---

## STEP 4 — Section Objective Framework

Every section has a defined purpose, conversion role, and anti-patterns. The AI MUST follow these when generating content.

### HERO
- **Purpose:** Stop the scroll. Communicate the core promise in <3 seconds.
- **Conversion Role:** First impression — determines if visitor stays or bounces.
- **Must:** Visually anchor the product. Clear, single-sentence promise. CTA visible.
- **Must NOT:** Overload with text. Use generic stock imagery. Have multiple competing messages.

### PROOF BAR
- **Purpose:** Instant credibility before the visitor commits to reading.
- **Conversion Role:** Reduces initial skepticism, earns the scroll.
- **Must:** Use real numbers, logos, certifications, or press mentions.
- **Must NOT:** Use fake numbers. Include more than 4–5 items. Be the hero (it's a support element).

### PROBLEM
- **Purpose:** Make the visitor feel understood — "they get me."
- **Conversion Role:** Emotional activation — moves from passive browsing to active engagement.
- **Must:** Use empathetic, editorial tone. Max 3 pain points. Tie to emotional, not just functional, frustration.
- **Must NOT:** Use aggressive "ARE YOU TIRED OF" headlines. List 10+ problems. Blame the reader.

### SOLUTION
- **Purpose:** Introduce the product as the natural answer to the established problem.
- **Conversion Role:** Bridge from pain to relief — the "aha" moment.
- **Must:** Product is the visual star. Focus on simplicity and outcomes.
- **Must NOT:** List features here (that's the next section). Be technical. Lead with specs.

### FEATURES (WITH BENEFITS)
- **Purpose:** Translate product specifications into life improvements.
- **Conversion Role:** Logical justification — gives the rational brain reasons to support the emotional decision.
- **Must:** Every feature → benefit pair. 4–6 max. Icon grid format.
- **Must NOT:** List features without benefits. Exceed 6 items. Use jargon without translation.

### COMPARISON (Conditional)
- **Purpose:** Show the old/inferior way vs. this product — make the upgrade obvious.
- **Conversion Role:** Competitive differentiation — eliminate alternatives.
- **Must:** Be specific and fair. Show real contrast.
- **Must NOT:** Be included for aesthetic/identity products. Use fabricated competitors. Be mean-spirited.
- **Include WHEN:** Product replaces a manual/expensive/inferior method. There IS a clear "old way."

### USE CASES (Conditional)
- **Purpose:** Help the buyer visualize themselves using the product in real scenarios.
- **Conversion Role:** Self-identification — "Oh, that's exactly my situation."
- **Must:** 3 cards max. Each with scenario + image + one-line description.
- **Must NOT:** Be generic. Duplicate feature descriptions. Exceed 3 scenarios.
- **Include WHEN:** Product serves 3+ distinct, relatable situations.

### BEFORE & AFTER (Conditional)
- **Purpose:** Visual proof of transformation.
- **Conversion Role:** Belief — the most powerful proof for transformation products.
- **Must:** Side-by-side or slider. If slider is used, follow the `lp-slider` component contract in **STEP 7**. "Before" desaturated, "After" bright and positive. Real results.
- **Must NOT:** Use obviously fake or AI-generated comparisons. Show unrealistic results.
- **Include WHEN:** Product produces a VISIBLE transformation (skin, cleaning, home improvement).

### CRAFTSMANSHIP (Conditional)
- **Purpose:** Showcase the creation process, materials, and human skill behind the product.
- **Conversion Role:** Perceived value — handmade = worth more.
- **Must:** Show real materials, real process. Icon grid or visual story.
- **Must NOT:** Use generic "high quality" claims without substance.
- **Include WHEN:** Product is handmade, artisan, or has genuine material/origin value.

### LIFESTYLE SHOWCASE (Conditional)
- **Purpose:** Place the product in an aspirational context — "I want that life."
- **Conversion Role:** Identity alignment — the product becomes a symbol, not just an object.
- **Must:** 3 images in aspirational settings. First image spans 2 rows (masonry layout).
- **Must NOT:** Show the product alone on white. Use cluttered or low-quality images.
- **Include WHEN:** Product is bought for style, identity, or self-expression.

### HOW IT WORKS (Conditional)
- **Purpose:** Explain the mechanism or process so the buyer trusts it'll work for them.
- **Conversion Role:** Clarity — removes "I don't understand how this works" objection.
- **Must:** 3 steps max. Visual or numbered. Simple, no jargon.
- **Must NOT:** Be overly technical. Exceed 3 steps.
- **Include WHEN:** Product mechanism is non-obvious or requires a routine.

### SOCIAL PROOF
- **Purpose:** Show that real people have bought, used, and loved this product.
- **Conversion Role:** Herd behavior — "if others love it, it must be good."
- **Must:** Story-driven testimonials with scenario + benefit + emotion. Real names. Verified badges.
- **Must NOT:** Use generic "Great product!" quotes. Use fake names. Have fewer than 3 testimonials.

### GUARANTEE
- **Purpose:** Remove the fear of making a wrong decision.
- **Conversion Role:** Risk reversal — turns "maybe later" into "I'll try it."
- **Must:** Clear, specific guarantee (30-day, money-back, satisfaction). Trust badges (secure checkout, free shipping).
- **Must NOT:** Use vague "we stand behind our product" language without specifics.

### OFFER
- **Purpose:** Present the complete value proposition and convert.
- **Conversion Role:** The close — everything has led here.
- **Must:** Include value stack, price anchoring, any bonuses, guarantee reminder, clear CTA.
- **Must NOT:** Introduce new information. Have multiple competing CTAs. Hide the price.

### FAQ
- **Purpose:** Answer remaining objections that block the sale.
- **Conversion Role:** Objection elimination — the final sweep.
- **Must:** 4–6 real objections (not made-up questions). Accordion format.
- **Must NOT:** Repeat marketing copy. Answer more than 6 questions. Include non-objection questions.

### FOOTER CTA
- **Purpose:** Catch visitors who scrolled through everything but didn't click.
- **Conversion Role:** Last chance — the final conversion opportunity.
- **Must:** Repeat the core promise + CTA + micro-trust.
- **Must NOT:** Introduce new messaging. Be different from the primary CTA.

---

## STEP 5 — Social Proof Strategy Selection

Based on the product classification, select the **appropriate proof types**. Use 2–3 types per page, not just one.

### Proof Type Matrix

| Proof Type | Best For | Format |
|---|---|---|
| **Story Testimonials** | All categories | Quote card: scenario → benefit → emotion. Name + badge. |
| **UGC-Style** | Fashion, beauty, lifestyle | Photo + short quote, informal tone, "real person" feel |
| **Numbers / Authority** | Tech, health, service | "200K+ customers", "4.9★ on 12K reviews", "Featured in Forbes" |
| **Before/After** | Beauty, cleaning, fitness | Side-by-side visual transformation |
| **Expert Endorsement** | Health, finance, safety | Doctor/expert photo + credential + quote |
| **Press / Logo Bar** | High-trust, B2B, service | Row of recognizable logos or "As seen in" |

### Selection Rules

```
IF buying_driver = Pain Relief OR Convenience:
  → Story Testimonials (scenario-driven) + Numbers

IF buying_driver = Beauty OR Aspiration:
  → Before/After + UGC-Style + Story Testimonials

IF buying_driver = Status OR Identity:
  → UGC-Style + Press/Logos + Story Testimonials

IF buying_driver = Trust / Safety:
  → Expert Endorsement + Numbers + Story Testimonials

IF price_tier = High-Ticket:
  → ADD Expert Endorsement or Press/Logos regardless of driver

IF offer_type = Lead Gen OR Consultation:
  → Case Studies + Expert Endorsement + Numbers
```

---

## STEP 6 — Offer Engine Logic

The Offer section is NOT just "put the price and a button." It is an engineered conversion system.

### Offer Architecture

Every offer section MUST contain these elements in order:

```
1. VALUE STACK — Show everything the buyer gets (product + bonuses)
2. PRICE ANCHORING — Original price (crossed out) → Current price
3. BONUSES — 1–3 extras that remove secondary objections
4. URGENCY / SCARCITY — Time or quantity limitation
5. RISK REVERSAL — Guarantee that eliminates buyer fear
6. PAYMENT CLARITY — How they pay (card, COD, WhatsApp, installments)
7. PRIMARY CTA — One clear action button
8. MICRO-TRUST — Badges below CTA (secure checkout, free shipping, satisfaction guarantee)
```

### Value Stack Rules

- List each component with its individual perceived value
- Show total value, then show the actual price → creates perception of massive deal
- Format: checkmark list with bolded item names

### Price Anchoring Rules

- ALWAYS show the original/retail price crossed out
- Current price must feel like a deal in context
- If discount exists, show percentage saved
- For COD: hide payment complexity → "Just confirm your order, pay when it arrives"

### Bonus Rules

- Bonus 1: Removes a secondary objection (e.g., carrying case removes "how do I store it?")
- Bonus 2: Increases perceived value (e.g., guide, video tutorial, exclusive content)
- Bonus 3 (optional): Social/community access or extended warranty
- Every bonus must feel like it has standalone value

### Urgency / Scarcity Rules

Use ONLY if legitimate. Choose ONE:

| Trigger | When to Use | Format |
|---|---|---|
| **Limited Stock** | Physical product with real scarcity | "Only X left in stock" |
| **Time-Limited Discount** | Active promotion | Countdown timer or "Offer ends [date]" |
| **Launch Pricing** | New product | "Introductory price — increases to $X" |
| **Seasonal** | Seasonal relevance exists | "Summer/Holiday Special" |
| **Fast-Action Bonus** | Extra incentive | "Order in the next [X] hours and get [bonus]" |

**Must NOT:** Fabricate false urgency. Use "only 2 left!!!" if untrue. Stack multiple urgency types.

### Risk Reversal Rules

- ALWAYS include a guarantee. The stronger the guarantee, the higher the conversion.
- Types: 30-day money-back / Lifetime warranty / Try-before-you-buy / Satisfaction guarantee
- Display as a badge or bordered card — make it visually prominent
- Include micro-copy: "No questions asked" / "Full refund, no hassle"

### Payment Method Clarity

| Offer Type | What to Show |
|---|---|
| Direct Purchase | Accepted payment icons (Visa, Mastercard, PayPal) |
| COD | "Pay cash when your package arrives" + delivery badge |
| WhatsApp | "Tap to order on WhatsApp" + green WhatsApp icon |
| Lead Gen | "100% Free — No Card Required" |

---

## STEP 7 — Slider Component Compatibility Contract (`lp-slider`)

When a section needs slider/carousel behavior, output it as a **builder component**, not generic carousel HTML.

### Section Triggers (must use `lp-slider`)

Use `lp-slider` markup for these intents:

- Image gallery
- Logo strip / brand logos
- Testimonial carousel
- Product image showcase
- UGC/social proof visual slider
- Before/after slider variant

### Mandatory Markers and Structure

Every slider section must include:

- Component marker: `data-component="lp-slider"`
- GrapesJS component hint: `data-gjs-type="lp-slider"` (or equivalent component type hint used by the builder)
- Stable wrapper class: `.lp-slider`
- Stable children: `.lp-slider__track` -> repeated `.lp-slider__slide` -> `.lp-slider__image`
- Optional caption node: `.lp-slider__caption`

Use this structural pattern (adapt values by context):

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
         data-autoplay="true"
         data-loop="true">
      <div class="lp-slider__track">
        <div class="lp-slider__slide">
          <img class="lp-slider__image" src="/storage/..." alt="Customer result 1">
          <div class="lp-slider__caption">Real customer result #1</div>
        </div>
      </div>
    </div>
  </div>
</section>
```

### Intent -> Preset Mapping

Map section intent to `data-preset`:

| Section Intent | Required Preset |
|---|---|
| Gallery | `gallery` |
| Logos | `logos` |
| Testimonials | `testimonials` |
| Product images | `product-showcase` |
| UGC / Social proof | `social-proof` |

### Responsive and Behavior Attributes

Slider sections must expose builder-editable behavior through attributes:

- `data-slides-desktop`, `data-slides-tablet`, `data-slides-mobile`
- `data-space-between`
- `data-arrows`, `data-dots`
- `data-autoplay`, `data-autoplay-delay`, `data-loop`, `data-speed`
- `data-pause-on-hover`, `data-draggable`, `data-center-mode`, `data-initial-slide`
- `data-image-fit`, `data-ratio`, `data-custom-height`, `data-border-radius`, `data-shadow`, `data-lazy`

### Editability and Serialization Rules

- Slider HTML must remain block-structured and component-detectable after save/load/export/import
- Do not flatten slider content into a single text blob
- Keep each slide as a repeated `.lp-slider__slide` node with image/caption children
- Keep marker attributes stable between AI generation and builder edits

### Asset Rules for Slider Sections

- Use managed builder assets (`/storage/...`, uploaded media, imported ZIP assets, AI-generated assets stored in platform media)
- Avoid temporary/unsafe remote image URLs in final output
- Keep `alt` text on all `.lp-slider__image` nodes

### Forbidden Pattern

- Do NOT generate monolithic "carousel blob" HTML with random classes and no component markers

---

## Mobile-First Conversion Constraints (Global)

These rules apply to ALL structures, ALL archetypes, ALL variants. They are non-negotiable.

### Above-the-Fold Rule
- CTA MUST be visible without scrolling on mobile
- Hero = Headline + Product Image + CTA in first viewport
- No hero carousels, no auto-playing videos above the fold

### Scannable Blocks
- No paragraph longer than 3 lines on mobile
- Use bullet points, icon grids, numbered lists
- Each section = ONE core idea, scannable in <5 seconds

### Sticky CTA (Mobile)
- Floating CTA bar at bottom of screen on mobile
- Appears after first scroll past Hero CTA
- Same wording as primary CTA, same style
- Semi-transparent background, always accessible

### One Primary Action
- ONE CTA wording across the entire page (repeats, never competes)
- No secondary actions that distract from the primary conversion
- No "Learn More" links — everything is on this page

### Visual Speed
- Visitor must understand what the product IS within 3 seconds of landing
- Product image must be large, clear, and above the fold
- Headline must be readable in 2 seconds (≤8 words ideal)

### Touch Targets
- All clickable elements ≥ 48px tall
- CTA buttons: full-width below 768px
- Adequate spacing between tap targets (no accidental clicks)

---

## Ad → Page Message Matching

If the visitor arrives from a paid ad, the landing page MUST mirror the ad's messaging. Mismatches cause immediate bounces.

### Matching Rules

| Ad Element | Page Element | Rule |
|---|---|---|
| **Ad Hook** | Hero Headline | Same promise, same angle, same language |
| **Ad Image** | Hero Image | Same product, same or similar visual |
| **Ad Angle** | Page Narrative | If ad leads with pain → page leads with problem. If ad leads with offer → page leads with deal. |
| **Ad CTA** | Page CTA | Same action framing (e.g., if ad says "Get yours" → page says "Get Yours Today") |

### Angle Consistency

```
IF ad_angle = Pain:
  → Hero headline agitates the same pain
  → Problem section is expanded
  → Solution section clearly answers the pain

IF ad_angle = Result:
  → Hero headline shows the transformation/outcome
  → Before/After or Use Cases section is prominent
  → Testimonials emphasize results

IF ad_angle = Social Proof:
  → Hero headline includes proof ("Loved by 200K+")
  → Social proof section appears immediately after hero
  → Testimonials are the dominant proof type

IF ad_angle = Offer:
  → Hero headline IS the deal ("40% Off This Week Only")
  → Offer section is near the top (hero or immediately after)
  → Urgency is visually prominent
```

### Intent Alignment

The page MUST complete the promise that the ad started. If the ad promises a specific benefit, the page must deliver proof of that benefit within the first 2 sections.

---

## Structure Variants by Awareness Level

For each archetype, awareness level creates structural variants. The AI MUST select the correct variant.

### Archetype A Variants (Problem→Solution)

**Variant A1 — Cold / Problem Aware:**
```
1. HERO — Hook with pain headline
2. PROOF BAR
3. PROBLEM — Expanded (3 pain points + emotional trigger)
4. SOLUTION — Full product reveal
5. FEATURES — 6-card benefit grid
5.5. COMPARISON — Old Way vs This Product
6. USE CASES — 3 real-life scenarios
7. SOCIAL PROOF — 3+ testimonials
   MID-PAGE CTA
8. GUARANTEE — Risk reversal card
9. OFFER — Full value stack
10. FAQ — 5-6 questions
11. FOOTER CTA
```

**Variant A2 — Warm / Solution Aware:**
```
1. HERO — Differentiator headline + CTA
2. PROOF BAR — Numbers-heavy
3. SOLUTION — Quick reveal (they know the problem)
4. FEATURES — 4-card benefit grid
5. COMPARISON — Us vs Competitors
6. SOCIAL PROOF — Results-focused testimonials
7. OFFER — Value stack + urgency
8. FAQ — 4 questions
9. FOOTER CTA
```

**Variant A3 — Hot / Product Aware (Retargeting):**
```
1. HERO — Offer headline + product + CTA
2. SOCIAL PROOF — 2 featured testimonials
3. OFFER — Deal + urgency + guarantee
4. FAQ — 3 questions (shipping, returns, guarantee)
```

---

### Archetype B Variants (Aspiration→Identity)

**Variant B1 — Cold / Unaware:**
```
1. HERO — Aspirational lifestyle image + curiosity headline
2. PROOF BAR — Heritage badges
3. LIFESTYLE SHOWCASE — 3 aspirational images
4. CRAFTSMANSHIP — Materials + origin story
5. FEATURES — Quality attributes
6. SOCIAL PROOF — UGC-style + story testimonials
   MID-PAGE CTA
7. OFFER — Bundle or set pricing
8. FAQ — 5 questions (sizing, materials, shipping, care, returns)
9. FOOTER CTA
```

**Variant B2 — Warm / Solution Aware:**
```
1. HERO — Product beauty shot + style headline + CTA
2. PROOF BAR
3. FEATURES — Quality attributes
4. LIFESTYLE SHOWCASE — 2 images
5. SOCIAL PROOF — UGC + reviews
6. OFFER — Pricing + urgency
7. FAQ — 4 questions
8. FOOTER CTA
```

**Variant B3 — Hot / Product Aware (Retargeting):**
```
1. HERO — Product + offer headline + CTA
2. SOCIAL PROOF — 2 UGC testimonials
3. OFFER — Deal + free shipping + guarantee
4. FAQ — 3 questions
```

---

### Archetype C Variants (Transformation)

**Variant C1 — Cold / Problem Aware:**
```
1. HERO — Transformation promise headline
2. PROOF BAR — Certifications, dermatologist-tested badges
3. PROBLEM — The concern (empathetic, max 3 points)
4. BEFORE & AFTER — Visual proof (3 comparisons)
5. BENEFITS — Outcome-focused list
6. INGREDIENTS / COMPONENTS — Safety + science
6.5. HOW TO USE — Routine integration
7. SOCIAL PROOF — Transformation testimonials with timelines
   MID-PAGE CTA
8. EXPERT / AUTHORITY — Doctor or expert endorsement
9. OFFER — Kits / subscription + guarantee
10. FAQ — 5-6 questions
11. FOOTER CTA
```

**Variant C2 — Warm / Solution Aware:**
```
1. HERO — Result headline + product + CTA
2. PROOF BAR
3. BEFORE & AFTER — 2 comparisons
4. BENEFITS — Quick scan
5. SOCIAL PROOF — Results testimonials
6. OFFER — Value stack + urgency
7. FAQ — 4 questions
8. FOOTER CTA
```

**Variant C3 — Hot / Retargeting:**
```
1. HERO — Offer + product + CTA
2. BEFORE & AFTER — 1 strong comparison
3. SOCIAL PROOF — 2 testimonials
4. OFFER — Deal + urgency + guarantee
```

---

## Examples

### Example 1 — Tech Product (Portable Tire Inflator)

**Classification:**
```
Buying Driver: Pain Relief + Convenience
Product Nature: Physical
Proof Requirement: Demo + Comparison + Testimonial
Offer Type: Direct Purchase / COD
Price Tier: Mid-Ticket
```

**Traffic:** Cold (Facebook ad)
**Awareness:** Problem Aware
**Ad Angle:** Pain ("Still waiting 45 min for roadside assistance?")

**Selected:** Archetype A, Variant A1

**Structure Output:**
```
1. HERO — "Never Get Stranded With a Flat Tire Again." + product image + CTA
2. PROOF BAR — 200K+ customers · 4.9★ · 120 PSI · USB-C charging
3. PROBLEM — Flat tire at night, gas station lines, overpriced roadside assistance
4. SOLUTION — "Inflate any tire in under 4 minutes — from your trunk"
5. FEATURES — 120 PSI, LED light, auto-shutoff, USB-C, compact, universal
5.5. COMPARISON — Gas station pump vs. Airmoto pocket inflator
6. USE CASES — Car, bike, sports equipment
7. SOCIAL PROOF — 3 story testimonials (roadside, road trip, family car)
   MID-PAGE CTA
8. GUARANTEE — 30-day money-back + free shipping
9. OFFER — Pump ($79 value) + Carry Case ($19 value) + Tire Repair Kit ($15 value) = $113 total value → Today: $49 + Free shipping + COD badge
10. FAQ — Tire types, charging time, noise level, warranty, PSI accuracy
11. FOOTER CTA
```

**Social Proof Strategy:** Story Testimonials + Numbers

---

### Example 2 — Fashion Product (Moroccan Babouche)

**Classification:**
```
Buying Driver: Identity + Status
Product Nature: Physical
Proof Requirement: Craftsmanship + Testimonial
Offer Type: COD
Price Tier: Mid-Ticket
```

**Traffic:** Cold (Instagram ad)
**Awareness:** Unaware
**Ad Angle:** Story-Based ("My coworker asked if these were designer…")

**Selected:** Archetype B, Variant B1

**Structure Output:**
```
1. HERO — Full-bleed babouche on terracotta tile + "Handcrafted Luxury, Rooted in Tradition"
2. PROOF BAR — "Handcrafted in Fez" · "Premium Goat Leather" · "Free Shipping" · "Pay on Delivery"
3. LIFESTYLE SHOWCASE — Terrace scene, flat-lay with outfit, entryway
4. CRAFTSMANSHIP — Material sourcing, hand-stitching process, 48-hour drying
5. FEATURES — Soft leather, flat sole, hand-stitched, natural dye
6. SOCIAL PROOF — UGC-style photos + story testimonials
   MID-PAGE CTA
7. OFFER — Single pair $34 / Bundle of 2 $59 + velvet pouch + COD badge
8. FAQ — Sizing (how to measure), leather care, shipping time, returns, COD process
9. FOOTER CTA
```

**Social Proof Strategy:** UGC-Style + Story Testimonials

---

### Example 3 — Beauty Product (Anti-Aging Serum)

**Classification:**
```
Buying Driver: Beauty + Aspiration
Product Nature: Physical
Proof Requirement: Before-After + Expert + Testimonial
Offer Type: Direct Purchase
Price Tier: Mid-Ticket
```

**Traffic:** Warm (retargeted from blog post)
**Awareness:** Solution Aware
**Ad Angle:** Result ("See visible results in 14 days")

**Selected:** Archetype C, Variant C2

**Structure Output:**
```
1. HERO — "Visible results in 14 days — or your money back" + serum hero + CTA
2. PROOF BAR — "Dermatologist-tested" · "95% saw improvement" · "Cruelty-Free" · "10K+ reviews"
3. BEFORE & AFTER — 2 transformation comparisons with 14-day and 30-day timelines
4. BENEFITS — Reduces fine lines, hydrates, evens tone, protects
5. SOCIAL PROOF — Transformation testimonials with timeline ("After 3 weeks, my skin felt…")
6. OFFER — Single bottle $39 / 3-pack $89 (save 24%) + Free serum guide + 60-day guarantee
7. FAQ — Skin types, ingredients, routine, results timeline
8. FOOTER CTA
```

**Social Proof Strategy:** Before/After + Story Testimonials + Numbers

---

### Example 4 — Lead Generation (Free Consultation)

**Classification:**
```
Buying Driver: Pain Relief + Trust
Product Nature: Service
Proof Requirement: Authority + Testimonial + Numbers
Offer Type: Consultation
Price Tier: N/A (free consultation, high-ticket backend)
```

**Traffic:** Cold (Facebook lead ad)
**Awareness:** Problem Aware

**Selected:** Archetype D

**Structure Output:**
```
1. HERO HOOK — "Stop Losing Clients to Competitors With Better Funnels" + form (name, email, phone)
2. BENEFIT STACK — 5 bullets: "Custom strategy", "Done-for-you funnels", "30-day results", "Dedicated manager", "Weekly reporting"
3. PROCESS — 3 steps: Book call → We analyze → We build
4. AUTHORITY — "400+ funnels built" · Client logos · Founder bio + photo
5. CASE STUDIES — 2 results: "$0→$47K/mo in 60 days", "3x ROAS in 30 days"
6. SOCIAL PROOF — 3 client testimonials (scenario + result + emotion)
7. CONVERSION REINFORCEMENT — Repeat CTA form + FAQ (3 objections)
```

---

## Constraints

### Classification
- **Do NOT** skip the 5-dimension classification — ALWAYS complete it before choosing structure
- **Do NOT** default to Archetype A for everything — match the buying driver
- **Do NOT** force a Problem→Solution arc on identity/fashion/aesthetic products

### Structure
- **Do NOT** include comparison sections for aesthetic/identity products (fashion, jewelry, decor)
- **Do NOT** include more than 11 sections for any page — if you need more, your sections are too granular
- **Do NOT** use the same structure for all awareness levels — variants exist for a reason
- **Do NOT** put the offer section before social proof (except Retargeting variants)

### Sections
- **Do NOT** use navigation menus — CTA is the only exit
- **Do NOT** add external links — everything leads to conversion
- **Do NOT** have competing CTAs — ONE primary action, repeated, never varied
- **Do NOT** mix section purposes — each section does ONE thing

### Mobile
- **Do NOT** put CTA below the fold on mobile
- **Do NOT** use long paragraphs — 3 lines max on mobile
- **Do NOT** skip the sticky mobile CTA

### Matching
- **Do NOT** create a page that mismatches the ad hook, angle, or imagery
- **Do NOT** use a different promise in the hero than what the ad communicated

### Integration
- **ALWAYS** use `product-research` output as input for classification
- **ALWAYS** reference `landing-page-design` for CSS/theming after structure is set
- **ALWAYS** reference `landing-page-sections` for HTML/CSS patterns after structure is set
- **ALWAYS** reference `conversion-copywriting` for the copy within each section
