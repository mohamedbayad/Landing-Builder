---
name: ugc-image-generation
description: Generates section-aware marketing images from a product photo while preserving exact product identity.
---

# UGC Image Generation

Generate conversion-focused images that feel real, active, and product-faithful.
Prioritize in-use human context over static product display.

Prerequisite: `product-image-safety` Product Identity Lock is required.

## Generation Rules

### Real-Context Priority
- At least 80% of generated images must include product-in-use context.
- At least 70% must include human interaction.
- Max one product-only image per page.

### Mandatory Scenario Coverage
Create a set that covers:
1. Emergency scenario.
2. Everyday convenience scenario.
3. Lifestyle/identity scenario.
4. Close-up action/mechanism shot.
5. Before/after scene (when product has visible transformation).

### Prompt Composition Requirements
For every product-containing prompt include:
1. Full Product Identity Lock.
2. Action verb + hand/body interaction details.
3. Environment details (location, time, mood).
4. Light/shadow matching instruction.
5. Realism instruction (imperfections, candid framing).

## Section Mapping

1. Hero: premium in-use context with clear benefit.
2. Problem/Agitation: pain scene, product optional.
3. Demo/How-It-Works: step visuals or close-up mechanism sequence.
4. Use Cases: emergency + daily + preventive/lifestyle.
5. Social Proof: UGC-like customer context.
6. Offer: reuse strongest trust-building product image.

## Rejection Rules

Reject and regenerate if:
- Product is static/posed toward camera without action.
- Human interaction is unnatural.
- Scene looks like stock catalog.
- Product identity changes.
- Benefit is not visually understandable.

## Non-Negotiable Rules

- Do not generate catalog-only image sets.
- Do not skip emergency or lifestyle scenarios for functional products.
- Do not use sterile studio-only shots for all sections.
- Always protect product identity exactly.
