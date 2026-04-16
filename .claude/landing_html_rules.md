# Landing Template HTML & Structure Rules

## Role & Purpose
Ensure all generated landing pages adhere to the strict HTML validation, security, and parsing rules enforced by the Web App's \`TemplateZipProcessorService\`.

## Required ZIP Folder Structure
Templates must be provided as a static package.
- \`/index.html\` (Required root page)
- \`/checkout.html\` (Optional)
- \`/thankyou.html\` (Optional)
- \`/assets/\` (Directory for all CSS, JS, Images, Fonts)

## Global Constraints & Path Rules
- **Relative paths only:** \`src="./assets/img.jpg"\`. No absolute local paths.
- **No framework source output:** No JSX, no React, no Next.js build artifacts. Deliver flat semantic HTML.
- **Allowed Extensions:** html, css, js, jpg, jpeg, png, webp, svg, gif, woff, woff2, ttf, eot. (Max file size: 50MB, Max total zip size: 200MB).

## Frontend Code Clean-up & Forbidden Patterns
The backend actively strips insecure and untrusted code. To ensure pages don't break dynamically, DO NOT include the following in the template:

1. **Inline Event Handlers:** Do NOT use \`onclick\`, \`onload\`, \`onmouseover\`, etc. They are stripped automatically via regex.
2. **Untrusted CDNs:** The parser strips external \`<script>\` tags unless they belong to allowed domains (e.g., \`cdn.tailwindcss.com\`, \`unpkg.com/alpinejs\`, \`cdnjs.cloudflare.com\`).
3. **Inline Scripts in \`<body>\`:** Headless scripts in the body are removed. All custom logic should be in \`assets/script.js\` referenced correctly, or rely on Alpine.js.
4. **Symlinks & Directory Traversal:** Zip files with symlinks or \`../\` directory traversals are strictly rejected.

## Clean Semantic HTML Expectations
- Wrap major content areas in \`<section>\` tags.
- Use readable, structural class names alongside utility classes. 
- Ensure critical content exists directly in the HTML (not injected dynamically later), as the GrapesJS parser (\`landing-parser-plugin.js\`) uses DOM children to build blocks.

## Final Validation Checklist
- [ ] Root folder contains \`index.html\`.
- [ ] Assets are confined to the \`assets/\` folder.
- [ ] No inline \`on*\` handlers are used in the HTML.
- [ ] Custom scripts are externalized, not inline \`<script>\` blocks without \`src\`.
- [ ] No third-party sliders or countdown runtime JS routines are present.
