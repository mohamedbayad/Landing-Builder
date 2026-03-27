---
name: product-image-safety
description: Preserves exact product identity across all generated visuals using extraction, validation, and failsafe rules.
---

# Product Image Safety

Ensure every AI-generated visual preserves the **exact product identity** from the uploaded reference image. Works for any product category — gadgets, shoes, bags, beauty, electronics, home, accessories, or any consumer product.

> **Fundamental principle:** The uploaded product image is the ONLY source of truth. This is NOT a similar product. This is NOT a redesigned product. This is NOT an inspired version. It must remain the SAME exact product. Identity preservation is always more important than aesthetic reinterpretation.

---

## When to use this skill

- Before generating ANY image that contains or depicts the product
- When a new product image is uploaded (triggers a fresh extraction)
- When deciding whether to use the original image or generate a new visual
- When verifying a generated image before inserting it into a landing page

## How to use it

### Step 1 — Fresh Product Extraction (Identity Lock)

For every new product image, study it and extract a **complete visual inventory**. Never reuse from a previous task. Never reconstruct from memory — always reference the actual image.

**Critical Identity Lock Checklist** — document ALL of these that are visible:

| Category | What to Document | Why It Matters |
|---|---|---|
| **1. Overall Silhouette** | Exact outline shape, proportions, height-to-width ratio | Any change = a different product |
| **2. Geometry** | Corners (sharp/rounded), edges, body construction, thickness | Subtle geometric changes destroy identity |
| **3. Surface & Material** | Finish (matte/glossy/rubberized), texture, color blocking, material type | Surface is the first thing people recognize |
| **4. Screen / Display** | Screen position, size, shape, interface layout, what's shown on screen | Screen design is a primary identifier |
| **5. Buttons & Controls** | Button placement, size, color, labeling, how many, which side | Moving a button = different product |
| **6. Chambers / Transparent Parts** | Any transparent tubes, chambers, windows — exact position, shape, routing | Structural components define identity |
| **7. Nozzle / Hose / Tube** | Position, routing path, connector type, length, color | Hose misrouting = instant rejection |
| **8. Attachments & Accessories** | Connected parts (mounts, cables, nozzles, caps), included items in kit | Must match exactly — not "similar" |
| **9. Branding & Text** | Logo, text content, placement area, font style, color, size | Brand placement is non-negotiable |
| **10. Color Zones** | Exact color map: which parts are which color, transitions, accents | Color zone changes = different product |

> Skip categories that genuinely don't apply. A serum bottle has no "screen." A shoe has no "nozzle." But if a category is visible in the image, it MUST be documented.

**Output:** Compile into a detailed paragraph — the **Product Identity Lock**. This lock is used in every prompt and is valid for the current task only.

**Critical:** Do NOT describe what you *think* the product looks like. Describe what you *see* in the image. Do NOT fill in details from memory or general product knowledge.

### Step 2 — Image Safety Decision

For each landing page section, apply this decision matrix:

| Section | Strategy | Generate Product? |
|---|---|---|
| **Hero** | **Use original image directly** — never generate | ❌ Original only |
| **Problem** | Generate scenario images — NO product visible | ❌ No product |
| **Solution** | **Prefer original image** — generate only if exact identity preservation succeeds | ⚠️ Original preferred |
| **Features** | Icons or cropped details from original image | ❌ Original only |
| **Use Cases** | Generate scenes — preserve exact identity | ⚠️ Generate with lock |
| **Comparison** | Use original product visuals | ❌ Original only |
| **Offer** | **Reuse hero product image** — never generate | ❌ Original only |
| **FAQ** | No product image required | ❌ No product |

**Default bias:** Use the original product image. Only generate product-in-scene images when the scene context adds genuine conversion value AND you can guarantee exact identity preservation.

### Step 3 — The 3-Part Prompt Formula

Every image generation prompt that includes the product must contain:

```
[Part 1: Product Identity Lock — full extracted description from Step 1]
+
[Part 2: Scene Description — environment, person, context, lighting]
+
[Part 3: Strict Preservation Instruction (copy-paste below)]
```

**Part 3 — Strict Preservation Instruction:**
> "The product must match the uploaded reference image EXACTLY — same overall silhouette, same proportions, same screen layout and interface, same button placement, same side chambers or transparent structures, same nozzle/tube position and routing, same branding placement and text, same material finish, same corners, edges, and geometry. Do NOT simplify, restyle, thicken, shorten, widen, or modernize the product. Do NOT replace components with similar ones. Do NOT reinterpret the product from memory. The generated image must depict the EXACT SAME physical product from the reference image. Photorealistic, naturally integrated into the scene."

**Mandatory:** Pass the original product image via `ImagePaths`.

### Step 4 — Generation Strategy

**Prefer compositing over full regeneration.** When product accuracy is critical (which is always), it is better to:

1. Generate the scene/environment/person WITHOUT the product
2. Integrate the original product image into the scene with CSS/compositing
3. Use `filter: drop-shadow()`, `mix-blend-mode`, and `transform` for natural integration

This is SAFER than asking the AI to regenerate the product in a new context.

**Full regeneration** (product in scene) is acceptable ONLY when:
- The scene requires natural hand interaction with the product
- The product needs to be shown at an angle not available in the original
- The use case demands the product actively being used (e.g., inflating a tire)

Even then, apply the full Identity Lock + Preservation Instruction + `ImagePaths`.

### Step 5 — Verification & Rejection

Before using any generated image, verify against the Identity Lock. Check EVERY documented attribute:

| Check | What to Compare | Reject If... |
|---|---|---|
| **Silhouette** | Overall outline matches exactly | Shape is rounder, taller, shorter, wider |
| **Proportions** | Height-to-width ratio identical | Product looks stretched, compressed, or resized |
| **Screen / Display** | Position, size, layout match | Screen moved, resized, or shows different content |
| **Buttons / Controls** | Same count, same position, same side | Any button moved, added, or removed |
| **Chambers / Transparent** | Same structure, same position | Chamber shape, size, or position differs |
| **Nozzle / Hose / Tube** | Same routing, same connector | Hose routes differently or connector shape changes |
| **Branding** | Same text, same placement area | Logo moved, text changed, or font differs |
| **Material / Surface** | Same finish, same texture feel | Matte became glossy, rubberized became smooth |
| **Color Zones** | Same color map | Any color zone changed or shifted |
| **Accessories** | All included items present | Missing or added components |

**Rejection is immediate and non-negotiable if ANY check fails.**

**Failsafe — when rejected:**
1. **First choice:** Use the original product image directly with CSS styling:
   ```html
   <img src="original-product.png" alt="[Product Name]"
        style="filter: drop-shadow(0 20px 40px rgba(0,0,0,0.5)); max-width: 600px;">
   ```
2. **Second choice:** Generate the scene WITHOUT the product, then composite the original image in
3. **Never:** Ship an image where the product identity has changed

---

## Examples

### Example 1 — Gadget (Tire Inflator)

**Input:** Photo of a portable tire inflator  
**Identity Lock:**
```
"The exact product from the reference image: a compact cylindrical portable
tire inflator with a matte black rubberized body, approximately 8 inches tall.
A small LCD digital display on the top face shows PSI readings. A silver
pressure gauge dial sits on the upper front. A coiled black air hose with a
brass valve connector is attached to the right side. The body has subtle vertical
grip ridges. A small red power button on the left side. A transparent air
chamber/tube visible on one side. The brand 'AIRMOTO' is printed in white
block letters on the front center. The overall silhouette is cylindrical with
slightly tapered top."
```

**Rejection scenario:** Generated image shows a similar-looking inflator but the transparent chamber is on the wrong side, the screen is larger, and the body is more rectangular → **REJECT immediately. Use original image.**

### Example 2 — Shoe (Leather Babouche)

**Input:** Photo of a Moroccan slipper  
**Identity Lock:**
```
"The exact product from the reference image: a traditional Moroccan babouche
slipper in rich emerald green soft leather. Pointed toe with a slight upward
curl. Flat sole with tan/natural leather on the bottom visible from the side.
No heel counter — the back folds down flat. Smooth, slightly glossy leather
upper with visible hand-stitching along the sole edge in tan thread. No logo
or branding visible. Interior is unlined, showing the natural leather back."
```

### Example 3 — Beauty Product (Serum Bottle)

**Input:** Photo of a dropper serum bottle  
**Identity Lock:**
```
"The exact product from the reference image: a clear glass dropper bottle with
a rose-gold metallic cap and rubber squeeze bulb. The bottle is cylindrical,
approximately 4 inches tall. The liquid inside is a pale golden amber color.
A white minimalist label on the front reads 'GLOW SERUM' in thin sans-serif
capitals, with smaller text below. The dropper pipette is visible through the
glass."
```

---

## Constraints

### Identity Rules (Non-Negotiable)
- **NEVER** redesign, simplify, enhance, modernize, restyle, or reimagine the product
- **NEVER** replace components with similar-looking ones
- **NEVER** reconstruct the product from memory or general knowledge — always reference the actual image
- **NEVER** generate a product-containing image without `ImagePaths` referencing the original
- **NEVER** output a generated image if ANY attribute in the Identity Lock has changed

### Extraction Rules
- **Do NOT** reuse a Product Identity Lock from a previous task — extract fresh every time
- **Do NOT** use generic category terms ("a dash cam", "a shoe", "a gadget") — always use the full Identity Lock
- **Do NOT** skip any visible attribute in the extraction — if it's visible, document it

### Generation Rules
- **PREFER** the original product image over any generated version — default to original for hero, features, and offer sections
- **PREFER** compositing/integration over full product regeneration when accuracy is critical
- **Do NOT** generate full product-in-scene images unless exact identity preservation is verified
- If the model cannot preserve exact identity → **do NOT generate** — use the original image and build the scene around it

### Priority Order
1. Use original product image directly (safest)
2. Composite original product into generated scene (safe)
3. Generate product in scene with full Identity Lock + Preservation Instruction + `ImagePaths` (risky — verify strictly)
4. A clean scene with an **exact** product is always better than a cinematic scene with an **altered** product

### Quality
- **Do NOT** prioritize cinematic aesthetics over product accuracy
- The customer must instantly recognize it as the SAME exact product from the original image, not a similar AI-generated variant
- See `ad-creative-strategy` for advanced photorealistic integration rules (hand interaction, shadow physics, depth of field, imperfection layer)
