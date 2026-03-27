# Section CSS Library — Reference

> Complete CSS for every section in the landing page system. Use these as copy-paste templates.
> For section usage rules and page architecture, see `landing-page-sections/SKILL.md`.
> For theme variables and design tokens, see `landing-page-design/SKILL.md`.

---

## Hero Section

```css
.hero-section {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 120px 5% 80px;
  position: relative;
}

.hero-section h1 {
  font-family: var(--font-heading);
  font-size: clamp(2.2rem, 5vw, 4.5rem);
  font-weight: 800;
  letter-spacing: -0.03em;
  line-height: 1.08;
  color: var(--text-primary);
  max-width: 720px;
}

.accent-word {
  font-family: var(--font-accent);
  font-style: italic;
  color: var(--accent);
}

.hero-subtitle {
  font-size: 1.15rem;
  color: var(--text-secondary);
  max-width: 520px;
  margin: 20px auto 0;
  line-height: 1.7;
}

.hero-product {
  margin-top: 48px;
  max-width: 600px;
}

.hero-product img {
  width: 100%;
  filter: drop-shadow(0 30px 60px rgba(0,0,0,0.4));
}

.hero-trust-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 24px;
  font-size: 0.85rem;
  color: var(--text-tertiary);
}

/* Split Hero variant */
.hero-split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 64px;
  align-items: center;
  max-width: 1140px;
  margin: 0 auto;
}

@media (max-width: 768px) {
  .hero-section { padding: 80px 20px 60px; min-height: auto; }
  .hero-split { grid-template-columns: 1fr; gap: 32px; text-align: center; }
}
```

---

## Proof Bar

```css
.proof-bar {
  padding: 20px 5%;
  display: flex;
  justify-content: center;
  gap: 40px;
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  flex-wrap: wrap;
}

.proof-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  color: var(--text-tertiary);
  letter-spacing: 0.5px;
}

@media (max-width: 768px) {
  .proof-bar { flex-wrap: wrap; gap: 20px; }
}
```

---

## Problem Section

```css
.problem-section {
  padding: 100px 5%;
  text-align: center;
}

.pain-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  max-width: 960px;
  margin: 48px auto 0;
}

.pain-card {
  padding: 32px 24px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  text-align: center;
}

.pain-icon {
  width: 40px;
  height: 40px;
  margin: 0 auto 16px;
  color: var(--text-tertiary);
}

@media (max-width: 768px) {
  .pain-grid { grid-template-columns: 1fr; max-width: 400px; }
}
```

---

## Solution Section

```css
.solution-section {
  padding: 100px 5%;
}

.solution-inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 64px;
  align-items: center;
  max-width: 1080px;
  margin: 0 auto;
}

.solution-image img {
  width: 100%;
  border-radius: 16px;
}

@media (max-width: 768px) {
  .solution-inner { grid-template-columns: 1fr; gap: 32px; text-align: center; }
}
```

---

## Features Section

```css
.features-section {
  padding: 100px 5%;
  text-align: center;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  max-width: 1040px;
  margin: 48px auto 0;
}

.feature-card {
  padding: 32px 24px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  text-align: center;
  transition: transform 0.25s ease;
}

.feature-card:hover { transform: translateY(-4px); }

.feature-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 20px;
  background: color-mix(in srgb, var(--accent) 10%, transparent);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
}

.feature-card h3 {
  font-family: var(--font-heading);
  font-weight: 600;
  font-size: 1.05rem;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.feature-card p {
  font-size: 0.9rem;
  color: var(--text-secondary);
  line-height: 1.6;
}

@media (max-width: 768px) {
  .features-grid { grid-template-columns: 1fr; max-width: 400px; }
}
```

---

## Testimonials Section

```css
.testimonials-section {
  padding: 100px 5%;
  text-align: center;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  max-width: 1080px;
  margin: 48px auto 0;
}

.testimonial-card {
  padding: 32px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  text-align: left;
}

.testimonial-stars {
  color: #F5A623;
  font-size: 0.9rem;
  letter-spacing: 2px;
  margin-bottom: 16px;
}

.testimonial-quote {
  font-size: 0.95rem;
  color: var(--text-secondary);
  line-height: 1.7;
  margin-bottom: 20px;
  font-style: italic;
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 12px;
}

.testimonial-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.testimonial-name {
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--text-primary);
}

.testimonial-verified {
  font-size: 0.75rem;
  color: var(--text-tertiary);
}

@media (max-width: 768px) {
  .testimonials-grid { grid-template-columns: 1fr; max-width: 440px; }
}
```

---

## CTA Button System

```css
.cta-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: var(--font-body);
  font-weight: 600;
  font-size: 0.95rem;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #fff;
  background: var(--accent);
  padding: 16px 40px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
}

.cta-primary:hover {
  opacity: 0.88;
  transform: translateY(-2px);
}

.cta-secondary {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-body);
  font-weight: 500;
  font-size: 0.9rem;
  color: var(--text-secondary);
  background: transparent;
  border: 1px solid var(--border);
  padding: 14px 32px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
}

.cta-secondary:hover {
  border-color: var(--text-secondary);
  color: var(--text-primary);
}

.micro-trust {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 12px;
  font-size: 0.78rem;
  color: var(--text-tertiary);
}
```

---

## Guarantee Section

```css
.guarantee-section {
  padding: 64px 5%;
  text-align: center;
}

.guarantee-card {
  max-width: 640px;
  margin: 0 auto;
  padding: 48px 40px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 20px;
}

.guarantee-badges {
  display: flex;
  justify-content: center;
  gap: 40px;
  margin-top: 32px;
}

.guarantee-badge {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.guarantee-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: color-mix(in srgb, var(--accent) 10%, transparent);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .guarantee-badges { flex-direction: column; gap: 16px; align-items: center; }
}
```

---

## Offer / Pricing Section

```css
.offer-section {
  padding: 100px 5%;
  text-align: center;
}

.price-display {
  margin: 24px 0;
}

.price-original {
  text-decoration: line-through;
  color: var(--text-tertiary);
  font-size: 1.3rem;
  margin-right: 12px;
}

.price-current {
  font-family: var(--font-heading);
  font-size: 3rem;
  font-weight: 700;
  color: var(--text-primary);
}

.price-badge {
  display: inline-block;
  background: #FF453A;
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 4px 12px;
  border-radius: 6px;
  margin-left: 12px;
  vertical-align: super;
}
```

---

## FAQ Section

```css
.faq-section {
  padding: 100px 5%;
  max-width: 700px;
  margin: 0 auto;
}

.faq-item {
  border-bottom: 1px solid var(--border);
}

.faq-question {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 0;
  background: none;
  border: none;
  cursor: pointer;
  font-family: var(--font-heading);
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  text-align: left;
}

.faq-icon {
  font-size: 1.2rem;
  color: var(--text-tertiary);
  transition: transform 0.2s ease;
}

.faq-question[aria-expanded="true"] .faq-icon {
  transform: rotate(45deg);
}

.faq-answer {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
}

.faq-answer p {
  padding: 0 0 20px;
  font-size: 0.95rem;
  color: var(--text-secondary);
  line-height: 1.7;
}
```

---

## Before & After (Transformation)

```css
.transform-section {
  padding: 100px 5%;
  text-align: center;
}

.transform-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  max-width: 960px;
  margin: 48px auto 0;
}

.transform-card {
  border-radius: 16px;
  overflow: hidden;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
}

.transform-card .card-image {
  width: 100%;
  aspect-ratio: 4/3;
  overflow: hidden;
}

.transform-card .card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.transform-card.before .card-image img {
  filter: grayscale(50%) brightness(0.85);
}

.transform-card .card-body {
  padding: 24px;
}

.transform-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin-bottom: 12px;
}

.transform-card.before .transform-label { color: #FF453A; }
.transform-card.after .transform-label { color: #34C759; }

@media (max-width: 768px) {
  .transform-grid { grid-template-columns: 1fr; }
}
```

---

## Lifestyle Showcase

```css
.lifestyle-section {
  padding: 100px 5%;
  text-align: center;
}

.lifestyle-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: auto auto;
  gap: 12px;
  max-width: 960px;
  margin: 48px auto 0;
}

.lifestyle-card {
  border-radius: 16px;
  overflow: hidden;
}

.lifestyle-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.lifestyle-card:hover img {
  transform: scale(1.03);
}

.lifestyle-card--tall {
  grid-row: 1 / 3;
}

@media (max-width: 768px) {
  .lifestyle-grid { grid-template-columns: 1fr; }
  .lifestyle-card--tall { grid-row: auto; }
}
```

---

## Craftsmanship

```css
.craft-section {
  padding: 100px 5%;
  text-align: center;
}

.craft-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  max-width: 960px;
  margin: 48px auto 0;
}

.craft-item {
  padding: 32px 24px;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: 16px;
  text-align: center;
}

.craft-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  background: color-mix(in srgb, var(--accent) 10%, transparent);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
}

.craft-image {
  margin-top: 48px;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
}

.craft-image img {
  width: 100%;
  border-radius: 16px;
}

@media (max-width: 768px) {
  .craft-grid { grid-template-columns: 1fr; max-width: 400px; }
}
```

---

## Use Cases

```css
.usecases-section {
  padding: 100px 5%;
  text-align: center;
}

.usecases-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  max-width: 1040px;
  margin: 48px auto 0;
}

.usecase-card {
  border-radius: 16px;
  overflow: hidden;
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  transition: transform 0.25s ease;
}

.usecase-card:hover {
  transform: translateY(-4px);
}

.usecase-card img {
  width: 100%;
  aspect-ratio: 4/3;
  object-fit: cover;
}

.usecase-body {
  padding: 24px;
}

.usecase-body h3 {
  font-family: var(--font-heading);
  font-weight: 600;
  font-size: 1.05rem;
  color: var(--text-primary);
  margin-bottom: 6px;
}

.usecase-body p {
  font-size: 0.9rem;
  color: var(--text-secondary);
  line-height: 1.6;
}

@media (max-width: 768px) {
  .usecases-grid { grid-template-columns: 1fr; max-width: 440px; }
}
```

---

## Comparison (Old Way vs New Way)

```css
.comparison-section {
  padding: 100px 5%;
}

.comparison-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  max-width: 960px;
  margin: 48px auto 0;
}

.comparison-card {
  border-radius: 16px;
  padding: 36px 32px;
  border: 1px solid var(--border);
}

.comparison-old {
  background: var(--bg-elevated);
  opacity: 0.85;
}

.comparison-new {
  background: var(--bg-elevated);
  border-color: var(--accent);
  box-shadow: 0 0 0 1px var(--accent), 0 8px 32px rgba(0,0,0,0.15);
}

.comparison-label {
  font-family: var(--font-heading);
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}

.comparison-new .comparison-label {
  color: var(--accent);
}

.comparison-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.comparison-list li {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-size: 0.95rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

.comparison-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  margin-top: 2px;
}

.icon-x { color: var(--text-tertiary); }
.icon-check { color: var(--accent); }

@media (max-width: 768px) {
  .comparison-grid { grid-template-columns: 1fr; max-width: 480px; }
}
```

---

## Visual Polish

```css
/* Noise Overlay */
.noise-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  pointer-events: none;
  opacity: 0.03;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* Section Divider */
.divider {
  width: 100%;
  max-width: 1140px;
  margin: 0 auto;
  height: 1px;
  background: var(--border);
}

/* Product Float Animation */
@keyframes gentle-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.float-product {
  animation: gentle-float 5s ease-in-out infinite;
}
```

---

## Responsive Overrides

```css
@media (max-width: 768px) {
  section { padding: 64px 20px; }
  h2 { font-size: 1.6rem; }

  .features-grid,
  .testimonials-grid,
  .transform-grid {
    grid-template-columns: 1fr;
  }

  .solution-inner,
  .hero-split {
    grid-template-columns: 1fr;
    gap: 32px;
    text-align: center;
  }

  .cta-primary {
    width: 100%;
    justify-content: center;
    padding: 18px 24px;
  }

  .proof-bar,
  .stat-bar {
    flex-wrap: wrap;
    gap: 20px;
  }
}
```
