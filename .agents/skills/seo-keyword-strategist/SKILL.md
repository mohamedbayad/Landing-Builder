---
name: seo-keyword-strategist
description: Analyzes keyword usage, calculates density, and suggests semantic variations to prevent over-optimization.
---

# SEO Keyword Strategist

Analyzes content for keyword optimization opportunities. Calculates density, identifies entities, generates LSI keywords, and prevents over-optimization.

---

## When to use this skill

- When optimizing keyword usage in existing or new content
- When needing LSI keyword suggestions for a specific topic
- When checking if content is over-optimized or keyword-stuffed
- When building a keyword strategy for a topic cluster

## How to use it

### Step 1 — Extract Current Usage

From the provided content, extract:
- Primary keyword and current density
- Secondary keywords and their frequency
- Related entities and concepts present

### Step 2 — Analyze & Recommend

1. Calculate keyword density percentages
2. Flag over-optimization (density > 1.5%)
3. Generate 20-30 LSI keywords based on the topic
4. Map entity relationships and co-occurrence patterns
5. Suggest optimal keyword distribution across sections

### Step 3 — Deliver Keyword Package

```
Primary: [keyword] (0.8% density, 12 uses)
Secondary: [3-5 keyword targets]
LSI Keywords: [20-30 semantic variations]
Entities: [related concepts to include]
```

**Density Guidelines:**
- Primary keyword: 0.5-1.5%
- Avoid exact-match repetition in consecutive sentences
- Use semantic variations throughout
- Include question-based keywords for PAA opportunities

---

## Examples

### Example 1 — Keyword Analysis

**Input:** 2000-word article about "portable tire inflator"  
**Output:**
```
Primary: "portable tire inflator" — 1.2% density (24 uses) ✅
Secondary: "air compressor for car", "tire inflation tool" — suggested
LSI: "PSI gauge", "flat tire", "roadside emergency", "12V power",
     "Presta valve", "Schrader valve", "auto shutoff", "LED flashlight"
Entities: [tire pressure, roadside assistance, car maintenance]
Warning: None — density is optimal
```

### Example 2 — Over-Optimization Detection

**Input:** Blog post repeating "best dash cam" 45 times in 1500 words  
**Output:**
```
Primary: "best dash cam" — 3.0% density ⚠️ OVER-OPTIMIZED
Action: Reduce to 15-22 occurrences, replace with variations:
  "top dashboard camera", "recommended car camera", "front-facing cam"
```

---

## Constraints

- **Do NOT** recommend keyword density above 1.5%
- **Do NOT** generate keyword lists without analyzing the actual content context
- **Do NOT** suggest exact-match keyword stuffing — always recommend natural integration
- **ALWAYS** include semantic variations, not just exact matches
- **ALWAYS** flag over-optimization before suggesting additions
