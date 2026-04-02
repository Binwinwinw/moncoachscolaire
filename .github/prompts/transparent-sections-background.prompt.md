# Prompt : Rendre transparents les fonds de 4 sections spécifiques

## 🎯 Objectif
Modifier **uniquement** les classes CSS des balises `<section>` suivantes dans `src/pages/landingpage.php` pour rendre leur fond **transparent**, **sans toucher à aucun autre élément** du code.

---

## 📍 Sections concernées

### 1️⃣ Section "Choisis ton niveau d'étude" (visiteurs non connectés)
**Ligne approximative :** ~ligne 420

**Code actuel :**
```html
<section class="intro bg-gradient-to-br from-sky-50 to-sky-100 mx-auto my-12 max-w-screen-xl">
```

**Code à obtenir :**
```html
<section class="intro bg-transparent mx-auto my-12 max-w-screen-xl">
```

**Action :** Remplacer `bg-gradient-to-br from-sky-50 to-sky-100` par `bg-transparent`.

---

### 2️⃣ Section "Essayez MonCoachScolaire gratuitement" (CTA Démo)
**Ligne approximative :** ~ligne 540

**Code actuel :**
```html
<section class="demo-cta-section bg-gradient-to-br from-sky-50 to-sky-100 mx-auto my-12 max-w-screen-xl">
```

**Code à obtenir :**
```html
<section class="demo-cta-section bg-transparent mx-auto my-12 max-w-screen-xl">
```

**Action :** Remplacer `bg-gradient-to-br from-sky-50 to-sky-100` par `bg-transparent`.

---

### 3️⃣ Section "Pourquoi choisir MonCoachScolaire ?"
**Ligne approximative :** ~ligne 555

**Code actuel :**
```html
<section class="why-mcs bg-gradient-to-br from-amber-50 to-yellow-100 mx-auto my-12 max-w-screen-xl">
```

**Code à obtenir :**
```html
<section class="why-mcs bg-transparent mx-auto my-12 max-w-screen-xl">
```

**Action :** Remplacer `bg-gradient-to-br from-amber-50 to-yellow-100` par `bg-transparent`.

---

### 4️⃣ Section "Pour Parents & Encadrants"
**Ligne approximative :** ~ligne 590

**Code actuel :**
```html
<section class="infos bg-gradient-to-br from-green-50 to-emerald-100 mx-auto my-12 max-w-screen-xl">
```

**Code à obtenir :**
```html
<section class="infos bg-transparent mx-auto my-12 max-w-screen-xl">
```

**Action :** Remplacer `bg-gradient-to-br from-green-50 to-emerald-100` par `bg-transparent`.

---

## ⚠️ Contraintes ABSOLUES

### ✅ À FAIRE
- **Uniquement** remplacer les classes `bg-gradient-to-br from-X to-Y` par `bg-transparent` sur les 4 balises `<section>` listées ci-dessus
- **Conserver toutes les autres classes** des sections (`intro`, `demo-cta-section`, `why-mcs`, `infos`, `mx-auto`, `my-12`, `max-w-screen-xl`, etc.)
- **Ne toucher à rien d'autre** : titres, contenus, cartes, images, boutons, structures HTML doivent rester **identiques**

### ❌ À NE PAS FAIRE
- ❌ **Ne PAS fusionner** les sections entre elles
- ❌ **Ne PAS supprimer** de contenu (titres, paragraphes, images, cartes, etc.)
- ❌ **Ne PAS modifier** les classes des éléments enfants (cartes, boutons, images)
- ❌ **Ne PAS toucher** aux autres sections de la page (header, section admin, section parent, section élève, footer)
- ❌ **Ne PAS changer** les noms des classes existantes (sauf remplacement du gradient par `bg-transparent`)

---

## 🧪 Vérification

Après modification, les 4 sections doivent :
- [x] Avoir un fond transparent (pas de dégradé de couleur visible)
- [x] Conserver leur titre (`<h2>`)
- [x] Conserver toutes leurs cartes/contenus intacts
- [x] Conserver leur espacement (`mx-auto my-12 max-w-screen-xl`)
- [x] Rester **séparées et distinctes** (4 balises `<section>` différentes)

---

## 📝 Résumé en une phrase

**Remplace les 4 classes de fond `bg-gradient-to-br from-X to-Y` par `bg-transparent` sur les sections "Choisis ton niveau", "Essayez gratuitement", "Pourquoi choisir", "Pour Parents", sans toucher à rien d'autre.**

---

## 🚀 Exemple de modification attendue

### Avant (ligne ~420)
```html
<section class="intro bg-gradient-to-br from-sky-50 to-sky-100 mx-auto my-12 max-w-screen-xl">
  <h2 class="text-3xl font-bold text-center mb-8">Choisis ton niveau d'étude</h2>
  <!-- Contenu identique conservé -->
</section>
```

### Après
```html
<section class="intro bg-transparent mx-auto my-12 max-w-screen-xl">
  <h2 class="text-3xl font-bold text-center mb-8">Choisis ton niveau d'étude</h2>
  <!-- Contenu identique conservé -->
</section>
```

**Seule différence :** `bg-gradient-to-br from-sky-50 to-sky-100` → `bg-transparent`

---

## 📂 Fichier concerné

- **Fichier :** `src/pages/landingpage.php`
- **Lignes concernées :** ~420, ~540, ~555, ~590 (sections `<section>` uniquement)

---

## ✅ Checklist finale

Avant de valider la modification, vérifie :
- [ ] Les 4 sections ont bien `bg-transparent` au lieu du gradient
- [ ] Les titres (`<h2>`) sont toujours présents
- [ ] Les cartes, images, boutons sont toujours présents et identiques
- [ ] Les 4 sections sont toujours **séparées** (pas de fusion)
- [ ] Aucune autre partie du code n'a été modifiée

---

**Nom du fichier :** `transparent-sections-background.prompt.md`