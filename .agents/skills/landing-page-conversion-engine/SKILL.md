---
name: landing-page-conversion-engine
description: Defines the core conversion strategy (angle, pain, desire, objections, proof, offer, CTA, image direction) before any landing page generation.
---

# Landing Page Conversion Engine

Set conversion strategy before any structure, copy, or visuals.
Do not output HTML/CSS. Output only the strategic blueprint.

Run after `product-research` and `conversion-intensity-controller`.

## Decision Workflow

### Phase 0: Inputs Lock
Consume:
1. Product Identity Profile.
2. Intensity profile (soft/balanced/aggressive).
3. Audience awareness level.

### Phase 1: Pain and Stakes
Define:
1. Core desire.
2. Primary pain.
3. Pain intensity (`low|medium|high`).
4. Worst-case scenario (if problem remains unsolved).
5. Emotional trigger (single dominant trigger).

### Phase 2: Multi-Angle Selling Map (Required)
Define and score these three mandatory angles:
1. Emergency angle.
2. Convenience angle.
3. Versatility angle.

For each angle include:
1. Hook concept.
2. Scenario anchor.
3. Proof requirement.
4. Best section placement.

Select one `primary_angle` and keep two as `supporting_angles`.

### Phase 3: Why Buy Now vs Why Not
Define:
1. Why buy now:
   - trigger event,
   - immediate gain,
   - urgency logic.
2. What happens if they do not buy:
   - cost of delay,
   - likely escalation,
   - emotional/financial risk.

### Phase 4: Objection Neutralization
Map 3-5 objections:
1. Price.
2. Efficacy.
3. Trust.
4. Effort/convenience.
5. Fit/compatibility.

For each:
1. Reframe strategy.
2. Proof type needed.
3. Placement where objection is handled.

### Phase 5: Offer Engineering
Define:
1. Offer structure (bundle, tier, discount, hybrid).
2. Value stack (tangible + emotional value).
3. Fast-action bonus.
4. Risk reversal guarantee.
5. Urgency/scarcity logic (must be believable).

### Phase 6: Scenario-Driven Image and Proof Direction
Specify visuals that correspond to angle map:
1. Emergency tension scene.
2. Convenience in-use scene.
3. Versatility multi-context scene.
4. Proof scene (testimonial, before/after, or data-led evidence).

## Non-Negotiable Rules

- Do not use only one selling angle.
- Do not define urgency without real reason.
- Do not skip consequence framing ("if they wait, what gets worse").
- Do not output neutral strategy language.
- Always tie strategy to real product identity and perceived value tier.

## Output Format

```json
{
  "product_analysis": {
    "core_desire": "string",
    "primary_pain": "string",
    "pain_intensity": "low|medium|high",
    "worst_case_scenario": "string",
    "emotional_trigger": "string",
    "awareness_level": "cold|warm|hot"
  },
  "angle_stack": {
    "primary_angle": "string",
    "supporting_angles": ["emergency", "convenience", "versatility"],
    "angle_cards": [
      {
        "angle_name": "string",
        "hook_concept": "string",
        "scenario_anchor": "string",
        "proof_requirement": "string",
        "section_placement": "string"
      }
    ]
  },
  "why_now_logic": {
    "buy_now_trigger": "string",
    "immediate_gain": "string",
    "urgency_reason": "string",
    "if_not_now_consequence": "string",
    "cost_of_delay": "string"
  },
  "objection_map": [
    {
      "objection": "string",
      "reframing_strategy": "string",
      "required_proof": "string",
      "placement_recommendation": "string"
    }
  ],
  "offer_engineering": {
    "offer_structure": "string",
    "value_stack": ["string"],
    "fast_action_bonus": "string",
    "risk_reversal_guarantee": "string",
    "urgency_scarcity_logic": "string"
  },
  "cta_strategy": {
    "primary_button_copy": "string",
    "secondary_button_copy": "string",
    "micro_copy_under_button": "string",
    "cta_pacing": "hero|demo|offer|footer"
  },
  "image_strategy": [
    {
      "section_target": "string",
      "image_purpose": "string",
      "visual_description": "string"
    }
  ],
  "structural_directives": ["string"]
}
```
