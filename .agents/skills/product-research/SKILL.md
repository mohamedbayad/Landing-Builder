---
name: product-research
description: Researches a product from its image to extract type, niche, audience, competitive positioning, and builds a complete marketing intelligence profile (pain mining, awareness levels, angles, offer engineering, ad hooks, creative direction).
---

# Product Research from Image

Use this skill first when a product image is provided.
The goal is to identify the real-world product identity and marketing position before copy, design, or section planning.

## Core Rule

Do not invent product identity.
Every identity claim must align with visible product details and marketplace evidence.

## Workflow

### Step 1: Visual Fingerprint
Extract from image:
1. Product type and niche.
2. Shape, materials, visible components.
3. Branding marks, text, model numbers.
4. Color palette from the product itself.
5. Packaging style if visible (minimal, utility, gift-style, premium box, no box shown).

### Step 2: Marketplace Identity Sweep (Required if image exists)
Run a marketplace sweep using search-based visual matching logic across:
1. Amazon
2. AliExpress
3. Alibaba
4. Shopify stores

Use query clusters:
- `[visible text/model] [product type]`
- `[shape + function + color] [product category]`
- `[brand guess] official [product type]`
- `[product type] site:amazon.com`
- `[product type] site:aliexpress.com`
- `[product type] site:alibaba.com`
- `[product type] inurl:products`

For each source, capture:
1. Listing title.
2. Brand name (or unbranded/white-label).
3. Price band.
4. Product variations (color/size/kit options).
5. Packaging cues from listing media.
6. Common use-case language.
7. Review pattern snippets (complaints, praised outcomes, objections).

### Step 3: Match Confidence Gate
Score identity confidence:
- `high`: clear match on shape + components + text/brand.
- `medium`: shape and function match, weak brand certainty.
- `low`: only category-level match.

If confidence is `low`, explicitly mark unknown fields as unknown.
Do not fabricate brand, model, review stats, or exact specs.

### Step 4: Product Identity Profile (Required Output)
Return:

```json
{
  "product_identity_profile": {
    "real_market_name": "string",
    "brand": "string",
    "identity_confidence": "high|medium|low",
    "market_sources_used": ["amazon", "aliexpress", "alibaba", "shopify"],
    "dominant_colors": ["string"],
    "packaging_style": "string",
    "product_variations": ["string"],
    "common_use_cases": ["string"],
    "pricing_range_usd": "string",
    "review_pattern_summary": {
      "top_positive_patterns": ["string"],
      "top_negative_patterns": ["string"],
      "recurring_objections": ["string"]
    },
    "brand_tone": "cheap|mid|premium",
    "perceived_value_level": "budget|mid|high-ticket",
    "positioning": "budget|premium"
  }
}
```

### Step 5: Marketing Intelligence Layer
Based on the identity profile, generate:
1. Deep Pain Analysis.
2. Awareness Level.
3. Market Sophistication (1-5).
4. Angle Set (pain, result, social proof, offer, optional contrarian).
5. Offer Structure.
6. Ad Hooks (5).
7. Creative Direction tied to dominant colors and perceived value level.

## Output Structure

Return in this order:
1. `Product Identity Profile` (JSON block above).
2. `Competitive Snapshot` (top alternatives and differentiation risk).
3. `Marketing Intelligence Layer` (steps 5.1-5.7).
4. `Design/Positioning Recommendations`:
   - premium cues to emphasize,
   - trust assets needed,
   - pricing/offer tension recommendations.

## Non-Negotiable Rules

- Do not skip marketplace sweep when image is provided.
- Do not invent brand names or market stats.
- Do not classify as premium without visual + pricing evidence.
- Always tie color decisions to observed product/listing visuals.
- Always surface uncertainty explicitly when identity confidence is not high.
