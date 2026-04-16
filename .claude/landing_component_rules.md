# GrapesJS Component & Trait Rules

## Role & Purpose
Teach agents how to construct HTML nodes so the backend's GrapesJS plugins correctly recognize components, traits, and layout blocks for editing.

## Section Block Detection (\`landing-parser-plugin.js\`)
When HTML is imported, the builder runs a DOM parser that splits the content into GrapesJS blocks by scanning the outer children (typically \`<section>\` elements).

- **Rule:** Provide clear \`id\` or \`class\` attributes containing category keywords for the parser to bucket them properly.
- **Categories:**
  - *Hero*: class/id must include \`hero\`
  - *Footer*: class/id must include \`footer\`
  - *Header*: class/id must include \`nav\` or \`header\`
  - *Pricing*: class/id must include \`price\` or \`pricing\`
  - *Features*: class/id must include \`feature\`
  - *Testimonials*: class/id must include \`testimonial\`
  - *Contact*: class/id must include \`contact\`

## Custom Components & Required Attributes

### 1. Product Cards (\`product-card\`)
If supplying a product listing card, the system recognizes it to append traits.
- **Detection**: Class must contain \`product-card\`.
- **Supported Custom Data Attributes**: 
  - \`data-product-id\`
  - \`data-product-title\`
  - \`data-product-price\`

### 2. Product Buttons (\`product-button\`)
- **Detection**: Class must contain \`btn-add-cart\`.
- **Supported Custom Data Attributes**:
  - \`data-product-label\`
  - \`data-price\`
  - \`data-title\`

### 3. Exit Intent Popup (\`exit-intent.js\`)
- **Detection**: Add attribute \`data-exit-intent="true"\`.
- **Configuration Attributes**:
  - \`data-trigger\`: \`exit\`, \`scroll-50\`, \`scroll-75\`, \`timer-10\`, \`timer-30\`
  - \`data-frequency\`: \`once\`, \`once-per-day\`, \`always\`
  - \`data-delay-ms\`: (Number in ms)

### 4. Device Visibility (\`device-visibility.js\`)
Applied automatically, but you can explicitly set it using:
- \`data-visibility="desktop-only"\` (hidden by default, shown lg)
- \`data-visibility="tablet-up"\` (hidden by default, shown md)
- \`data-visibility="mobile-only"\` (shown by default, hidden md)
- \`data-visibility="hidden-all"\`

## CSS Utilities
- Standard Tailwind CSS is encouraged.
- Ensure the structural UI does not require arbitrary hardcoded \`<style>\` tags dynamically overriding traits, as it makes GrapesJS style manager harder to use. Keep designs flexible.

## Final Validation Checklist
- [ ] Major sections have descriptive classes (e.g., \`hero-section\`, \`pricing-table\`) for GrapesJS block bucketing.
- [ ] Product elements use \`.product-card\` and \`.btn-add-cart\` with appropriate data traits.
- [ ] Any exit intent modal relies on \`data-exit-intent="true"\` rather than custom JS.
