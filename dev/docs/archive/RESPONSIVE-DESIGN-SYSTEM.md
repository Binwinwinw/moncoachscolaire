# 📱 Système Responsive Design - MonCoachScolaire

## 📋 Vue d'ensemble

Ce document décrit le système de breakpoints responsive implémenté dans l'application MonCoachScolaire pour garantir une compatibilité maximale sur tous les appareils du marché.

## 🎯 Approche Mobile First

Le système utilise une approche **Mobile First** avec amélioration progressive :
- Styles de base pour mobile
- Améliorations progressives pour les écrans plus larges
- Breakpoints basés sur les standards du marché

## 📐 Breakpoints Standards

### 1. Mobile - `max-width: 768px`
**Cible** : Smartphones et tablettes en mode portrait

**Caractéristiques** :
- Largeur pleine (100%)
- Padding réduit (1rem)
- Typographie adaptée (h1: 1.75rem)
- Boutons pleine largeur
- Grid en 1 colonne
- Sidebar en overlay
- Zone de touch minimale : 44x44px

**Utilisation** :
```css
@media only screen and (max-width: 768px) {
    /* Styles mobile */
}
```

### 2. Tablette Portrait - `767px à 991px`
**Cible** : Tablettes en mode portrait

**Caractéristiques** :
- Padding intermédiaire (2rem)
- Grid en 2 colonnes
- Typographie intermédiaire (h1: 2.25rem)
- Boutons avec largeur minimale

**Utilisation** :
```css
@media only screen and (min-width: 767px) and (max-width: 991px) {
    /* Styles tablette portrait */
}
```

### 3. Tablette Paysage / Petit Desktop - `992px à 1023px`
**Cible** : Tablettes en mode paysage et petits écrans desktop

**Caractéristiques** :
- Max-width: 960px
- Grid en 2-3 colonnes
- Typographie desktop (h1: 2.5rem)

**Utilisation** :
```css
@media only screen and (min-width: 992px) and (max-width: 1023px) {
    /* Styles tablette paysage */
}
```

### 4. Desktop - `min-width: 1024px`
**Cible** : Ordinateurs de bureau et écrans larges

**Caractéristiques** :
- Max-width: 1200px
- Grid en 3 colonnes
- Typographie complète (h1: 3rem)
- Sidebar fixe

**Utilisation** :
```css
@media only screen and (min-width: 1024px) {
    /* Styles desktop */
}
```

### 5. Desktop Large - `min-width: 1200px`
**Cible** : Grands écrans desktop

**Caractéristiques** :
- Max-width: 1400px
- Espacements augmentés

### 6. Desktop Très Large - `min-width: 1600px`
**Cible** : Écrans très larges

**Caractéristiques** :
- Max-width: 1500px
- Grid optimisée (minmax(380px, 1fr))

### 7. Ultra Wide - `min-width: 2000px`
**Cible** : Écrans ultra-larges et 4K

**Caractéristiques** :
- Max-width: 80vw (pour éviter l'étirement excessif)

## 🛠️ Classes Utilitaires

### Masquage conditionnel

```html
<!-- Masquer sur mobile -->
<div class="hide-mobile">Contenu desktop uniquement</div>

<!-- Masquer sur desktop -->
<div class="hide-desktop">Contenu mobile uniquement</div>

<!-- Afficher uniquement sur mobile -->
<div class="show-mobile">Visible mobile</div>

<!-- Afficher uniquement sur desktop -->
<div class="show-desktop">Visible desktop</div>
```

### Flex responsive

```html
<!-- Colonne sur mobile, ligne sur desktop -->
<div class="flex-responsive">
    <div>Élément 1</div>
    <div>Élément 2</div>
</div>
```

## 📏 Règles de Typographie Responsive

| Breakpoint | h1 | h2 | h3 | Body |
|------------|----|----|----|------|
| Mobile (≤768px) | 1.75rem | 1.5rem | 1.25rem | 16px |
| Tablette (767-991px) | 2.25rem | 1.875rem | 1.5rem | 16px |
| Tablette Paysage (992-1023px) | 2.5rem | 2rem | 1.75rem | 16px |
| Desktop (≥1024px) | 3rem | 2.5rem | 1.875rem | 16px |

## 🎨 Règles de Grid Responsive

### `.niveau-cards`, `.cards-grid`

| Breakpoint | Colonnes | Gap |
|------------|----------|-----|
| Mobile (≤768px) | 1 | 1.5rem |
| Tablette (767-991px) | 2 | 2rem |
| Tablette Paysage (992-1023px) | 2 | 2.5rem |
| Desktop (≥1024px) | 3 | 3rem |
| Desktop Large (≥1200px) | 3 | 3.5rem |
| Desktop Très Large (≥1600px) | 3 | 4rem |

## 📱 Optimisations Mobile

### Zone de Touch
- **Minimum recommandé** : 44x44px
- Appliqué automatiquement aux boutons et liens sur mobile

### Prévention du Zoom iOS
- Tous les inputs, textareas et selects ont `font-size: 16px` minimum
- Évite le zoom automatique sur iOS Safari

### Scroll Horizontal
- `overflow-x: hidden` sur le body pour éviter le scroll horizontal indésirable

## 🔄 Orientation Spécifique

### Mode Paysage Mobile
```css
@media only screen and (max-width: 768px) and (orientation: landscape) {
    /* Optimisations pour mode paysage */
}
```

## 📝 Bonnes Pratiques

### 1. Utiliser les breakpoints standardisés
```css
/* ✅ Bon */
@media only screen and (max-width: 768px) { }

/* ❌ Éviter */
@media only screen and (max-width: 750px) { }
```

### 2. Mobile First
```css
/* ✅ Bon - Mobile First */
.element {
    width: 100%;
    padding: 1rem;
}

@media only screen and (min-width: 1024px) {
    .element {
        width: 50%;
        padding: 2rem;
    }
}

/* ❌ Éviter - Desktop First */
.element {
    width: 50%;
    padding: 2rem;
}

@media only screen and (max-width: 1023px) {
    .element {
        width: 100%;
        padding: 1rem;
    }
}
```

### 3. Unités relatives
```css
/* ✅ Bon */
.element {
    width: 100%;
    padding: 1rem;
    font-size: 1rem;
}

/* ❌ Éviter les valeurs fixes */
.element {
    width: 320px;
    padding: 16px;
    font-size: 14px;
}
```

### 4. Images responsives
```css
/* ✅ Bon */
img {
    max-width: 100%;
    height: auto;
}
```

## 🧪 Tests Responsive

### Outils recommandés
- Chrome DevTools (Device Toolbar)
- Firefox Responsive Design Mode
- BrowserStack (tests multi-appareils)

### Appareils de test recommandés
- **Mobile** : iPhone SE (375px), iPhone 12/13 (390px), Samsung Galaxy (360px)
- **Tablette** : iPad (768px), iPad Pro (1024px)
- **Desktop** : 1280px, 1920px, 2560px

## 📚 Références

- [MDN - Using media queries](https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries)
- [CSS-Tricks - A Complete Guide to CSS Media Queries](https://css-tricks.com/a-complete-guide-to-css-media-queries/)
- [Google - Responsive Web Design Basics](https://developers.google.com/web/fundamentals/design-and-ux/responsive)

## ✅ Checklist d'Implémentation

- [x] Breakpoints standardisés définis
- [x] Typographie responsive
- [x] Grid responsive
- [x] Classes utilitaires
- [x] Optimisations mobile (touch, zoom)
- [x] Orientation spécifique
- [x] Documentation complète

---

*Dernière mise à jour : 2025-01-XX*
*Version : 1.0*
