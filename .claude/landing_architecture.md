# Landing Builder Architecture & Compatibility

## Role & Purpose
Teach agents the core architecture of the Landing Builder web app and its template import pipeline so they know what is handled by the backend/builder vs. what needs to be written in the frontend template.

## Architecture Summary
The web app imports ZIP files containing static HTML templates and parses them via \`TemplateZipProcessorService.php\`.
- HTML files (\`index.html\`, \`checkout.html\`, \`thankyou.html\`) are parsed and sanitized.
- Assets (images, CSS, JS) are extracted, downloaded (if external), stored locally, and their paths are rewritten to a UUID-based storage path.
- The GrapesJS editor uses custom plugins to parse the raw HTML into editable blocks (\`landing-parser-plugin.js\`) based on section IDs/classes.
- Public pages are rendered via \`PublicLandingController.php\`, which injects a session recording snippet and handles active layout rendering.

## Builder-Owned Systems (Already Handled by App)
Do **NOT** write custom frontend JavaScript for these systems. The Landing Builder already provides specialized plugins or controllers for them:

1. **Sliders / Carousels**
   - Handled by: \`lp-slider.js\` (GrapesJS plugin)
   - Do NOT include Swiper.js, Slick, or Splide initialization in templates. 

2. **Countdown Timers**
   - Handled by: \`countdown-plugin.js\` (GrapesJS plugin)
   - Do NOT include ticking interval JS logic. Provide static HTML structure (\`#days\`, \`#hours\`, \`#mins\`, \`#secs\`).

3. **Exit Intent & Popups**
   - Handled by: \`exit-intent.js\`
   - Uses the \`data-exit-intent="true"\` attribute to trigger on mouse exit or timer. Do NOT write custom exit-intent scripts.

4. **Device Visibility**
   - Handled by: \`device-visibility.js\` inside GrapesJS.
   - Uses the \`data-visibility\` attribute (\`desktop-only\`, \`tablet-up\`, \`mobile-only\`, \`hidden-all\`).

5. **Form Submissions & Checkout**
   - Handled via internal backend logic (\`checkout.html\` is dynamic).
   - The web app resolves product selection and payment dynamically.
   
## Validation Checklist
- [ ] Ensure template contains no custom JS for sliders, timers, or popups, but relies on builder architecture.
- [ ] Confirm no frameworks (React, Vue) are used—pure static semantic HTML only is accepted by the parser.
