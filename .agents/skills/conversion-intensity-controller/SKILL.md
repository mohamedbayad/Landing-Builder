---
name: conversion-intensity-controller
description: Controls persuasion pressure for landing page generation by setting emotional intensity, urgency level, and conversion aggressiveness mode (soft, balanced, aggressive). Use before landing-page-conversion-engine and conversion-copywriting to calibrate tone without changing architecture.
---

# Conversion Intensity Controller

Set persuasion intensity for the entire landing page pipeline.
This skill does not write copy or layout. It sets pressure controls.

Run after `product-research` and before `landing-page-conversion-engine`.

## Objective

Choose one mode and convert it into concrete behavioral rules that downstream skills obey:
- `soft` for brand-safe trust and low pressure.
- `balanced` for mainstream performance marketing.
- `aggressive` for ad-first conversion pressure.

## Inputs

- Product category and risk sensitivity.
- Traffic type (cold, warm, hot).
- Offer competitiveness.
- Compliance sensitivity (health, finance, regulated claims).

## Mode Selection Logic

### Soft
Use when trust and brand equity matter more than immediate pressure.
Good for premium brands, high-consideration purchases, sensitive audiences.

### Balanced
Use by default when no explicit mode is provided.
Strong persuasion with controlled urgency and low hype risk.

### Aggressive
Use for paid social, cold traffic, impulse-friendly offers, short decision windows.
Prioritize tension, consequence framing, and decisive CTAs.

## Calibration Matrix

For the selected mode, set:
1. Emotional intensity: `1-10`.
2. Urgency level: `1-10`.
3. Persuasion strength: `1-10`.
4. Objection pressure style: `educational | assertive | confrontational`.
5. CTA force: `gentle | direct | command`.
6. Risk tolerance for bold messaging: `low | medium | high`.

## Guardrails

- Keep claims believable and compliant.
- Never fabricate proof, testimonials, or guarantees.
- Urgency must be tied to real logic.
- Aggressive mode increases tension, not dishonesty.

## Output Format

```json
{
  "mode": "soft|balanced|aggressive",
  "intensity_profile": {
    "emotional_intensity": 1,
    "urgency_level": 1,
    "persuasion_strength": 1,
    "objection_pressure_style": "educational|assertive|confrontational",
    "cta_force": "gentle|direct|command",
    "boldness_risk_tolerance": "low|medium|high"
  },
  "messaging_rules": [
    "string"
  ],
  "urgency_rules": {
    "allowed_urgency_devices": ["string"],
    "disallowed_urgency_devices": ["string"]
  },
  "copy_constraints": {
    "max_hype_level": "string",
    "required_readability": "grade 6-8",
    "sentence_style": "short_punchy"
  },
  "integration_directives": {
    "for_conversion_engine": ["string"],
    "for_copywriting": ["string"],
    "for_sections": ["string"],
    "for_visual_strategy": ["string"]
  }
}
```

## Default Profiles

### Soft
- emotional_intensity: 4
- urgency_level: 3
- persuasion_strength: 4
- objection_pressure_style: educational
- cta_force: gentle
- boldness_risk_tolerance: low

### Balanced
- emotional_intensity: 6
- urgency_level: 6
- persuasion_strength: 7
- objection_pressure_style: assertive
- cta_force: direct
- boldness_risk_tolerance: medium

### Aggressive
- emotional_intensity: 9
- urgency_level: 8
- persuasion_strength: 9
- objection_pressure_style: confrontational
- cta_force: command
- boldness_risk_tolerance: high
