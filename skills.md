# Landing Page Compatibility Rules — LandingBuilder

> Guide pour créer des landing pages 100% compatibles avec le système LandingBuilder (Laravel + GrapesJS).

---

## 1. Structure du Template

Le template est importé via **JSON** avec cette structure :

```json
{
  "html": "<body content HTML only>",
  "css": "body-level CSS",
  "js": "body-level JavaScript",
  "custom_head": "<link>, <script>, et <style> tags pour le <head>"
}
```

### Règles critiques :
- **`html`** = UNIQUEMENT le contenu du `<body>` (pas de `<html>`, `<head>`, `<body>` tags)
- **`css`** = CSS injecté dans un `<style>` tag dans le `<head>`
- **`js`** = JS exécuté dans un `<script>` tag après le body content
- **`custom_head`** = scripts/styles externes pour le `<head>` (fonts, librairies)

> [!CAUTION]
> Les `<script>` tags dans le `html` sont **automatiquement supprimés** lors de l'import. Mets tous les scripts dans `js` ou `custom_head`.

---

## 2. Ce que le système injecte automatiquement

Le wrapper `landing_page.blade.php` ajoute déjà :

| Fonctionnalité | Détail |
|---|---|
| **Tailwind CSS** | Via `/js/tailwind.js` (play CDN local) — toutes les classes Tailwind sont disponibles |
| **Alpine.js** | Chargé via Vite (`app.js`) — `x-data`, `x-show`, `@click` etc. fonctionnent |
| **CSRF Token** | `<meta name="csrf-token">` injecté dans le `<head>` |
| **Analytics** | `/js/analytics.js` — tracking automatique des clics, scroll, heartbeat |
| **Countdown** | `/js/countdown.js` — timers de compte à rebours |
| **Form Handler** | Auto-inject `_token` (CSRF) et `landing_id` dans tous les `<form>` |
| **Session Recording** | rrweb injecté dynamiquement par le controller |
| **Shopping Cart** | Alpine.js cart sidebar (si activé dans les settings) |
| **WhatsApp Button** | Bouton flottant (si configuré dans workspace settings) |

> [!IMPORTANT]
> **NE PAS** inclure Tailwind, Alpine.js, ou jQuery dans le template — ils sont déjà chargés.

---

## 3. Formulaires

### Structure requise :
```html
<form action="/api/forms/submit" method="POST">
  <!-- _token et landing_id sont injectés automatiquement -->
  <input type="text" name="full_name" placeholder="Nom complet">
  <input type="email" name="email" placeholder="Email">
  <input type="tel" name="phone" placeholder="Téléphone">
  <input type="text" name="city" placeholder="Ville">
  <button type="submit">Envoyer</button>
</form>
```

### Règles :
- **`action="/api/forms/submit"`** — endpoint obligatoire pour la capture de leads
- **`method="POST"`** obligatoire
- Les inputs **sans attribut `name`** reçoivent un nom auto-généré (`field_text_0`, etc.) — mais il vaut mieux nommer explicitement
- Noms de champs recommandés : `full_name`, `email`, `phone`, `city`, `address`, `message`

---

## 4. Boutons CTA (Call-to-Action)

### Tracking automatique :
Le JS analytics détecte les clics sur :
- Éléments avec la classe **`.cta`** ou **`.track-cta`**
- Éléments avec l'attribut **`data-track`**
- Tous les `<a>` et `<button>`

### Attributs de tracking recommandés :
```html
<a href="#contact" 
   class="cta" 
   data-track="cta_commander_enligne"
   data-type="button"
   data-position="hero">
  Commander en ligne
</a>
```

| Attribut | Rôle | Fallback auto |
|---|---|---|
| `data-track` | Label unique du CTA | Généré depuis le texte du bouton |
| `data-type` | Type d'élément | Tag name (`a`, `button`) |
| `data-position` | Position dans la page | Section parent détectée |

> [!TIP]
> Si `data-track` n'est pas défini, le système génère automatiquement un label depuis le texte du bouton (ex: "Commander en ligne" → `cta_commander_en_ligne`).

---

## 5. Bouton Ajouter au Panier

```html
<button class="btn-add-cart"
        data-product-label="Pizza Margherita"
        data-price="89.00"
        data-product-id="1">
  Ajouter au panier
</button>
```

| Attribut | Obligatoire | Description |
|---|---|---|
| `class="btn-add-cart"` | ✅ | Déclenche l'ajout au panier |
| `data-product-label` | ✅ | Nom du produit |
| `data-price` | ✅ | Prix (format: `"89.00"`) |
| `data-product-id` | ❌ | ID optionnel |

---

## 6. Countdown (Compte à rebours)

```html
<div class="countdown" data-target="2026-03-15T23:59:59"></div>
```

Le script `/js/countdown.js` gère automatiquement les éléments avec la classe `.countdown`.

---

## 7. Images

### Pendant l'import :
- Les images avec des URLs **`http://` ou `https://`** sont **téléchargées** et stockées localement
- Les `src` sont réécrites vers `/storage/landings/{uuid}/imported_xxx.png`

### Dans l'éditeur GrapesJS :
- Utiliser le **Media Library** pour uploader des images
- Les images uploadées sont stockées dans `/storage/landings/{uuid}/`

### Bonnes pratiques :
- Utiliser des images **optimisées** (WebP ou JPEG compressé)
- Toujours ajouter un attribut `alt` pour le SEO
- Éviter les images de plus de **2 MB**

---

## 8. Éditeur GrapesJS — Bonnes pratiques

### Structure HTML recommandée :
```html
<!-- Utiliser des sections sémantiques -->
<section id="hero" data-section="hero">
  <div class="container mx-auto px-4">
    <h1>Titre principal</h1>
    <p>Description</p>
    <a href="#contact" class="cta" data-track="cta_hero">Commander</a>
  </div>
</section>

<section id="services" data-section="services">
  <!-- contenu -->
</section>

<section id="contact" data-section="contact">
  <form action="/api/forms/submit" method="POST">
    <!-- champs -->
  </form>
</section>
```

### Règles GrapesJS :
- **Pas de `<script>` dans le HTML** — utiliser le champ `js` séparé
- **Pas d'attributs `onclick`** — ils sont supprimés à l'import
- Les `data-gjs-type` sont gérés par le plugin de composants personnalisés
- Garder le HTML **propre et sémantique** (h1, h2, section, nav, footer)

---

## 9. Page Thank You (Confirmation)

Le système injecte les données de la commande dans les éléments avec ces IDs :

| ID de l'élément | Donnée injectée |
|---|---|
| `crm-order-id` | Numéro de commande (ORD-xxx) |
| `crm-fullname` | Nom complet du client |
| `crm-email` | Email |
| `crm-phone` | Téléphone |
| `crm-address` | Adresse complète |
| `crm-date` | Date de commande |
| `crm-product` | Nom du produit |
| `crm-amount` | Montant (MAD/USD + valeur) |
| `crm-total` | Total |
| `crm-payment` | Méthode de paiement |
| `crm-status` | Statut |
| `crm-invoice-btn` | Bouton télécharger facture (href auto) |

### Exemple :
```html
<div class="max-w-3xl mx-auto p-8">
  <h1>Merci pour votre commande!</h1>
  <p>Commande #<span id="crm-order-id">---</span></p>
  <p>Nom: <span id="crm-fullname">---</span></p>
  <p>Total: <span id="crm-total">---</span></p>
  <a id="crm-invoice-btn" href="#">Télécharger la facture</a>
</div>
```

---

## 10. SEO & Performance

### Checklist SEO :
- ✅ Un seul `<h1>` par page
- ✅ Hiérarchie correcte : h1 → h2 → h3
- ✅ Attributs `alt` sur toutes les images
- ✅ Texte descriptif dans les liens (pas de "cliquez ici")
- ✅ Meta title et description configurés dans les settings du landing

### Performance :
- ✅ Images optimisées (< 500 KB idéalement)
- ✅ Pas de librairies JS lourdes inutiles
- ✅ Utiliser les classes Tailwind plutôt que du CSS inline quand possible
- ✅ Lazy loading pour les images below the fold : `loading="lazy"`

---

## 11. Responsive Design

- Tailwind est disponible → utiliser les breakpoints : `sm:`, `md:`, `lg:`, `xl:`
- Tester sur mobile, tablette, et desktop
- Le cart sidebar est déjà responsive
- Le bouton WhatsApp est `fixed` en bas à gauche

---

## 12. Ce qu'il NE FAUT PAS faire

| ❌ Interdit | Raison |
|---|---|
| Inclure Tailwind/Alpine/jQuery via CDN | Déjà chargés par le système |
| Mettre des `<script>` dans le HTML body | Supprimés à l'import |
| Utiliser des `onclick=""` inline | Supprimés à l'import |
| Lier des images avec des URLs externes | Elles sont téléchargées, mais les URLs cassent si le serveur distant est down |
| Utiliser `document.write()` | Incompatible avec le rendering |
| Forms sans `action="/api/forms/submit"` | Les leads ne seront pas capturés |
| Multiples `<h1>` sur la même page | Mauvais pour le SEO |
