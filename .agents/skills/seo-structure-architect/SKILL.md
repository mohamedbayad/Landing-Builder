---
name: seo-structure-architect
description: Optimizes content structure, header hierarchy, schema markup, and internal linking for search visibility.
---

# SEO Structure Architect

Analyzes and optimizes content structure including header hierarchy, schema markup, internal linking, and featured snippet formatting for maximum search visibility.

---

## When to use this skill

- When designing the heading hierarchy (H1-H6) for a page
- When implementing schema markup (JSON-LD structured data)
- When optimizing content for featured snippets and SERP features
- When building internal linking strategy and silo structure
- When formatting content for PAA (People Also Ask) eligibility

## How to use it

### Header Hierarchy

- One H1 per page, matching the main topic
- H2s for main sections, including keyword variations
- H3s for subsections with related terms
- Maintain strict logical hierarchy — never skip levels

### Schema Markup (Priority Types)

| Schema | Use For |
|---|---|
| `Product` | E-commerce product pages |
| `FAQPage` | FAQ sections on landing pages |
| `HowTo` | Step-by-step guides |
| `Article` / `BlogPosting` | Blog content |
| `Organization` | Company/brand pages |
| `Review` / `AggregateRating` | Product reviews |
| `BreadcrumbList` | Site navigation |

### Featured Snippet Optimization

**Paragraph snippets (40-60 words):**
- Direct answer in the opening sentence
- Question-based H2/H3 headers
- Clear, concise definitions

**List snippets:**
- Numbered steps (5-8 items)
- Clear header before the list
- Concise item descriptions

**Table snippets:**
- Comparison data
- Structured specifications
- Clean markdown/HTML tables

### Internal Linking Strategy

1. Create topical theme clusters (pillar + supporting pages)
2. Link from supporting pages to pillar page
3. Cross-link only when highly relevant
4. Use descriptive anchor text (not "click here")
5. Ensure no orphaned pages

---

## Examples

### Example 1 — Heading Structure

**Input:** Landing page for tire inflator  
**Output:**
```
H1: Airmoto Portable Tire Inflator — Never Get Stranded Again
├── H2: The Problem With Traditional Air Pumps
├── H2: How Airmoto Solves It
│   ├── H3: 120 PSI Power in Your Pocket
│   └── H3: Universal Valve Compatibility
├── H2: What Our Customers Say
├── H2: Frequently Asked Questions
│   ├── H3: How long does it take to inflate a tire?
│   └── H3: What types of tires can it handle?
└── H2: Get Yours Today — 50% Off
```

### Example 2 — FAQ Schema Markup

**Input:** FAQ section with 3 questions  
**Output:**
```json
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How long does it take to inflate a tire?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Most car tires reach optimal pressure in 3-5 minutes."
      }
    }
  ]
}
```

### Example 3 — Snippet-Optimized Answer

**Input:** "What is a portable tire inflator?"  
**Output:**
```
## What Is a Portable Tire Inflator?

A portable tire inflator is a compact, battery-powered air compressor
designed to inflate car, bike, and sports equipment tires anywhere without
access to a gas station air pump. Most models weigh under 2 pounds and
can fill a standard car tire in 3-5 minutes.
```
*(44 words — within the snippet sweet spot)*

---

## Constraints

- **Do NOT** skip heading levels (e.g., H1 → H3 without H2)
- **Do NOT** use multiple H1 tags on a single page
- **Do NOT** use generic anchor text ("click here", "read more")
- **Do NOT** add schema markup that doesn't match the actual page content
- **ALWAYS** answer questions directly in the first sentence for snippet eligibility
- **ALWAYS** validate JSON-LD schema with Google's Rich Result Test
- **ALWAYS** place snippet-optimized answers immediately after the question heading
