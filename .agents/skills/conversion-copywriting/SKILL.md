---
name: conversion-copywriting
description: Generates persuasive, benefit-driven landing page copy using direct-response marketing principles.
---

# Conversion Copywriting

Write direct-response copy that sells outcomes, not features.
Voice should feel premium but persuasive: confident, clear, emotionally sharp.

## Required Inputs

1. `landing-page-conversion-engine` blueprint.
2. `product-research` identity profile.
3. `conversion-intensity-controller` mode (default `balanced`).

## Copy System

### 1) Emotional Hook
Open each major section with one of:
- urgent pain trigger,
- desired identity shift,
- cost-of-delay statement.

### 2) Scenario-Based Writing
Use real-life scenes:
- where the pain appears,
- what happens in that moment,
- how product changes the outcome.

Avoid abstract claims.

### 3) Benefit Stacking
For key features, write:
1. Functional benefit.
2. Emotional benefit.
3. Status or confidence benefit (if relevant).

### 4) Objection Handling In-Line
Handle objections before CTA friction peaks:
- "Will this work for me?"
- "Is this worth the price?"
- "Can I trust this?"
- "Is this hard to use?"

### 5) CTA Force
Use action-first CTA language:
- first-person commitment or command-benefit style,
- clear result-oriented wording,
- urgency aligned to real offer logic.

## Style Enforcement

- Short punchy sentences.
- Paragraphs max 2-3 lines.
- Strong verbs, low fluff.
- Readability target: grade 6-8.
- No robotic ecommerce language.

## Section Writing Requirements

For each section return:
1. `headline`
2. `supporting_copy`
3. `bullet_points` (if applicable)
4. `cta_copy` (if section includes CTA)
5. `objection_handled` (if applicable)
6. `tone_level` (`soft|balanced|aggressive`)

## Non-Negotiable Rules

- Do not write generic product descriptions.
- Do not write lifeless, neutral sentences.
- Do not leave all objection handling to FAQ.
- Do not use fake hype words without concrete meaning.
- Always include emotional context + concrete scenario + action outcome.
