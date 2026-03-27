---
name: frontend-security
description: Validates frontend code for XSS prevention, output sanitization, and client-side security vulnerabilities.
---

# Frontend Security

Expert frontend security practices for client-side XSS prevention, DOM security, Content Security Policy, and secure user interactions.

---

## When to use this skill

- When rendering user-generated content or dynamic data in the DOM
- When configuring Content Security Policy (CSP) headers
- When validating form inputs or handling file uploads on the client side
- When integrating third-party scripts, widgets, or payment forms
- When implementing secure authentication token storage

## How to use it

### XSS Prevention (Priority #1)

**Safe DOM manipulation:**
- Use `textContent` instead of `innerHTML` for dynamic text
- Use `DOMPurify.sanitize()` when rendering HTML from untrusted sources
- Never use `document.write()`
- Remove all inline `onclick` handlers — use `addEventListener`

**Context-aware encoding:**
- HTML entity encoding for HTML content
- JavaScript string escaping for JS contexts
- URL encoding for URL parameters

### Content Security Policy

**Recommended policy:**
```
Content-Security-Policy:
  default-src 'self';
  script-src 'self' 'nonce-{random}';
  style-src 'self' 'unsafe-inline';
  img-src 'self' data: https:;
  connect-src 'self';
  frame-ancestors 'none';
```

**Progressive deployment:** Start in `report-only` mode, monitor violations, tighten gradually.

### Input Validation

- Allowlist-based validation (whitelist acceptable inputs)
- Validate file types and sizes before upload
- Validate URLs before navigation (check protocol, domain allowlist)
- Use safe regex patterns — prevent ReDoS

### Secure Auth Token Storage

- Store JWT in `httpOnly` cookies when possible
- If using `localStorage`, implement token rotation
- Auto-logout on inactivity with `sessionStorage`
- Propagate logout across tabs via `storage` events

### Clickjacking Protection

- Use `X-Frame-Options: DENY` or `SAMEORIGIN`
- Use CSP `frame-ancestors: 'none'`
- Apply frame-busting only in production — relax for dev iframes

---

## Examples

### Example 1 — Safe Dynamic Content Rendering

**Input:** Need to display user comment on page  
**Bad:**
```js
element.innerHTML = userComment; // XSS vulnerability!
```
**Good:**
```js
element.textContent = userComment; // Safe — no HTML parsing
```

### Example 2 — Sanitizing Rich HTML

**Input:** Render markdown content from user  
**Output:**
```js
import DOMPurify from 'dompurify';
element.innerHTML = DOMPurify.sanitize(markdownHtml, {
  ALLOWED_TAGS: ['p', 'br', 'strong', 'em', 'a', 'ul', 'ol', 'li'],
  ALLOWED_ATTR: ['href', 'target']
});
```

### Example 3 — Secure External Link

**Input:** User-provided URL needs to be rendered as link  
**Output:**
```html
<a href="https://example.com" target="_blank" rel="noopener noreferrer">Link</a>
```

---

## Constraints

- **Do NOT** use `innerHTML` with untrusted data — use `textContent` or sanitize first
- **Do NOT** use `document.write()` — incompatible and insecure
- **Do NOT** use inline event handlers (`onclick="..."`) — they break CSP
- **Do NOT** store sensitive tokens in `localStorage` without rotation
- **Do NOT** embed third-party scripts without Subresource Integrity (SRI)
- **ALWAYS** add `rel="noopener noreferrer"` to `target="_blank"` links
- **ALWAYS** validate URLs before using them for navigation or redirects
