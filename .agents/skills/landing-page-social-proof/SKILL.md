---
name: landing-page-social-proof
description: Builds conversion-focused social proof systems for landing pages including realistic rating summaries, testimonial patterns, trust blocks, and placement logic. Use when pages need stronger trust, credibility, and purchase confidence.
---

# Landing Page Social Proof

Design social proof as a conversion engine, not decoration.
Proof must reduce skepticism and increase buying confidence at key friction points.

## Inputs

- Product Identity Profile (`product-research`).
- Conversion blueprint (`landing-page-conversion-engine`).
- Intensity mode (`conversion-intensity-controller`, optional).

## Proof Stack Builder

Create this full stack:
1. Rating summary block.
2. Volume credibility block.
3. Testimonial cards.
4. UGC-style short quotes.
5. Trust badges / policy proof.

## Realism Rules

- Rating range should be realistic: 4.7-4.9.
- Review count should be plausible for positioning:
  - new niche product: 300-2,000
  - scaling DTC: 2,000-20,000
  - mature bestseller: 20,000+
- Do not use impossible precision or fabricated platform claims.

## Testimonial Requirements

Each testimonial must include:
1. Scenario context (where/when problem happened).
2. Product use moment.
3. Emotional shift/result.
4. Customer first name + last initial.

Optional: avatar style direction (UGC selfie, lifestyle portrait, neutral profile).

## Required Sections

### 1) Rating Summary
Include:
- Star rating,
- review volume,
- one-line credibility statement.

### 2) Trusted by Thousands Block
Include:
- count statement,
- category credibility cue (drivers, homeowners, athletes, parents, etc.),
- optional repeat-purchase or satisfaction metric.

### 3) Testimonial Card Grid
3-6 cards with mixed scenarios:
- emergency/high stress,
- daily convenience,
- long-term confidence.

### 4) UGC Quote Strip
Short quote snippets for speed-scanning.
Use punchy, spoken language.

## Placement Logic

- Place rating summary near hero CTA.
- Place first testimonial cluster before offer reveal.
- Place trust badges near checkout CTA and guarantee.
- Place a final short proof reminder before footer CTA.

## Output Format

```json
{
  "rating_summary": {
    "rating": "4.8",
    "review_count": "string",
    "credibility_line": "string"
  },
  "trusted_by_block": {
    "headline": "string",
    "supporting_metrics": ["string"]
  },
  "testimonials": [
    {
      "name": "string",
      "scenario": "string",
      "quote": "string",
      "emotional_outcome": "string",
      "avatar_direction": "string"
    }
  ],
  "ugc_quote_strip": ["string"],
  "placement_directives": ["string"]
}
```

## Non-Negotiable Rules

- Do not write generic praise-only testimonials.
- Do not place all proof in one section.
- Do not use unrealistic social proof numbers.
- Always tie proof statements to the product's use context and audience.
