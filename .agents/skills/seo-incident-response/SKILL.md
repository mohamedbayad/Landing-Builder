---
name: seo-incident-response
description: Investigates sudden organic traffic drops with structured forensic triage, root-cause analysis, and recovery plan.
---

# SEO Incident Response

Investigate **sudden drops in organic traffic or rankings** using forensic analysis. This is NOT a routine audit — it's designed for incident scenarios: traffic crashes, suspected penalties, core update impacts, or major technical failures.

---

## When to use this skill

- When organic traffic or rankings drop suddenly and significantly
- When there are signs of a possible Google penalty or core update impact
- When a site migration, redesign, or major CMS update has gone wrong
- When diagnosing whether a traffic drop is technical, algorithmic, or demand-related

## How to use it

### Step 1 — Incident Triage (Ask These First)

1. **When** did you notice the drop? Sudden (1-3 days) or gradual (weeks)?
2. **What** metrics are affected? (clicks, impressions, CTR, conversions)
3. **Where** is the impact? (site-wide, specific sections, specific pages)
4. **Data access?** GSC, GA4, server logs, deployment logs?
5. **Recent changes?** (30-60 days before): redesign, URL changes, CMS updates, hosting changes, robots.txt edits, content pruning?

### Step 2 — Classify the Incident

| Type | Indicators |
|---|---|
| **Algorithm / Core Update** | Drop coincides with known update dates, no technical changes, quality-related patterns |
| **Technical Failure** | Indexing impaired, 5xx/4xx spikes, robots.txt blocking, broken redirects |
| **Manual Action** | GSC manual action message, severe branded + non-branded drop |
| **Content / Quality** | Specific sections hit, thin/outdated content, competitor improvements |
| **Demand / Seasonality** | Industry-wide search decline, seasonal niche, external events |

### Step 3 — Data-Driven Investigation

1. **Timeline:** Plot clicks/impressions over 6-12 months. Step-like → technical. Gradual → quality.
2. **Segments:** Desktop vs mobile, branded vs non-branded, by country/region.
3. **Page-level:** Top pages with largest drops, new 404s, disappeared pages.
4. **Technical checks:** robots.txt changes, noindex spikes, redirect chains, CWV degradation, Googlebot blocking.
5. **Content quality:** E-E-A-T evaluation, thin content detection, competitor comparison.

### Step 4 — Build Hypotheses

For each plausible cause:
- **Hypothesis:** e.g., "Recent deployment added noindex to category pages"
- **Evidence:** GSC data, analytics, code diffs
- **Impact:** Which sections/pages affected, estimated % drop
- **Validation step:** What check would confirm or refute this
- **Fix:** Concrete remediation action

### Step 5 — Prioritized Recovery Plan

1. **Critical Immediate (0-3 days):** Unblock crawling/indexing, reverse harmful deployments
2. **Stabilization (3-14 days):** Clean up redirects, canonicals, restore templates
3. **Recovery (2-8 weeks):** Content quality improvements, E-E-A-T enhancements
4. **Monitoring:** Metrics to watch, checkpoints, incident closure criteria

---

## Examples

### Example 1 — Technical Regression

**Incident:** 60% traffic drop overnight  
**Finding:** New deployment added `<meta name="robots" content="noindex">` to all category pages  
**Evidence:** GSC shows 200+ pages moved to "Excluded — noindex"  
**Fix:** Remove noindex from category template, request re-indexing  
**Timeline:** Critical — fix within 24 hours

### Example 2 — Core Update Impact

**Incident:** 30% gradual decline over 3 weeks, aligned with Google core update  
**Finding:** Non-branded queries hit hardest, content depth lower than competitors  
**Evidence:** Competitor pages are 3x longer with case studies, expert citations  
**Fix:** Create comprehensive content refresh plan for top 20 affected pages  
**Timeline:** Recovery phase — 4-8 weeks

---

## Constraints

- **Do NOT** use this for routine SEO audits — use `seo-audit` instead
- **Do NOT** jump to conclusions without evidence — use hypothesis-driven approach
- **Do NOT** recommend changes without understanding the root cause first
- **Do NOT** rely on a single data source — cross-reference GSC, analytics, logs
- **ALWAYS** reconstruct the timeline before diagnosing
- **ALWAYS** classify the incident type before deep analysis
- **ALWAYS** include a monitoring plan with the recovery recommendation
