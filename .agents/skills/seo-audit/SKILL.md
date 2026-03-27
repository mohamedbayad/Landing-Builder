---
name: seo-audit
description: Diagnoses SEO issues affecting crawlability, indexation, rankings, and organic performance with a scored health index.
---

# SEO Audit

Identify, explain, and prioritize SEO issues affecting organic visibility. Output is evidence-based, scoped, and actionable. This is a diagnostic skill — it does not implement fixes unless explicitly requested.

---

## When to use this skill

- When performing a full or partial SEO health check on a website
- When diagnosing why a site is not ranking or has lost organic traffic
- When assessing technical SEO, on-page optimization, or content quality
- When detecting keyword cannibalization between multiple pages

## How to use it

### Step 1 — Scope the Audit (Ask if Missing)

1. **Business context:** Site type, primary SEO goal, target markets
2. **SEO focus:** Full audit or specific pages? Technical, on-page, or all?
3. **Data access:** Google Search Console? Analytics? Known issues?

### Step 2 — Follow the Audit Framework (Priority Order)

1. **Crawlability & Indexation** (Weight: 30) — robots.txt, sitemaps, architecture, orphaned pages
2. **Technical Foundations** (Weight: 25) — Core Web Vitals (LCP < 2.5s, INP < 200ms, CLS < 0.1), mobile-friendliness, HTTPS
3. **On-Page Optimization** (Weight: 20) — titles, meta descriptions, headings, images, internal links
4. **Content Quality & E-E-A-T** (Weight: 15) — depth, originality, accuracy, author attribution
5. **Authority & Trust** (Weight: 10) — citations, brand mentions, policies, social proof

### Step 3 — Score with SEO Health Index

Start each category at 100, deduct based on issue severity:
- Critical (blocks crawling/indexing): −15 to −30
- High impact: −10
- Medium impact: −5
- Low/cosmetic: −1 to −3

Apply confidence modifier: Medium confidence → 50% deduction. Low → 25%.

**Final score:** `Σ (Category Score × Weight)` → classify into health band (Excellent 90-100, Good 75-89, Fair 60-74, Poor 40-59, Critical <40).

### Step 4 — Classify Findings

For every issue, document: Issue, Category, Evidence, Severity, Confidence, Why It Matters, Score Impact, Recommendation.

### Step 5 — Cannibalization Check (When Relevant)

If multiple pages target overlapping keywords:
- Identify competing pages and their rankings
- Determine resolution: consolidate, differentiate, canonicalize, or redirect

### Step 6 — Prioritized Action Plan

Group actions: Critical Blockers → High-Impact Improvements → Quick Wins → Long-Term Opportunities.

---

## Examples

### Example 1 — Crawlability Issue

**Finding:** `noindex` on key category pages  
**Category:** Crawlability & Indexation  
**Severity:** Critical (−25, High confidence)  
**Score Impact:** Category score drops from 100 to 75  
**Recommendation:** Remove `noindex` from category page template

### Example 2 — Cannibalization Detection

**Finding:** Two blog posts targeting "best portable tire inflator"  
**Resolution:** Consolidate into single authoritative page, 301 redirect the weaker URL

---

## Constraints

- **Do NOT** implement fixes unless explicitly asked — this is a diagnostic skill
- **Do NOT** rely on a single tool for conclusions — cross-reference multiple signals
- **Do NOT** report tool scores without interpretation
- **Do NOT** celebrate score improvements without validating outcomes
- A high score with unresolved Critical issues is invalid — flag the inconsistency
- **ALWAYS** state assumptions if critical context is missing
- **ALWAYS** explain what limits the score from being higher
