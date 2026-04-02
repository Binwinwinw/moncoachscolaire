---
name: socle-qualite-stable
description: Socle qualité stable pour tous les moteurs IA — réponses fiables, patches minimaux, compatibilité garantie
---

# Socle Qualité Stable — MonCoachScolaire

@workspace

## Objectif
Produire des réponses fiables, concises et actionnables. Éviter les modifications larges non demandées. Maintenir la compatibilité avec l'existant.

## Règles de travail

### Langage & Communication
- **Répondre en français** (sauf code/commandes)
- **Concision** : soyez direct, évitez les redondances
- **Rigueur** : pas de suppositions, annoncer clairement les hypothèses

### Approche technique
1. **Identifier la cause racine** avant de patcher
2. **Patch minimal** : modifier le strict nécessaire
3. **Ne pas renommer les hooks front** (id/classes/data-attributes) sans demande explicite
4. **Si HTML/CSS/JS est touché** : indiquer "hooks conservés" ou "hooks modifiés" + raison

### Validation avant fin
- Lint/syntaxe vérifiée (ex: `php -l` pour PHP)
- Aucun "fait" non vérifié
- Mention explicite de ce qui n'a pas pu être vérifié
- Hypothèses claires

## Sortie obligatoire (5 points)

Tout patch doit inclure :

1. **Fichiers impactés** — Liste exacte des fichiers modifiés
2. **Diff/Patch clair** — Avant/après visible
3. **Comment tester** — Commandes reproductibles
4. **Risques** — Impacts potentiels et dépendances
5. **Rollback simple** — Procédure de restauration

## Exemple structure de réponse

```
## Fichiers impactés
- src/pages/login.php
- src/includes/auth.php

## Diff
[---] src/pages/login.php (ligne 42)
  if ($username && $password) {
[+++]
  if (isset($username) && isset($password)) {

## Test
php -l src/pages/login.php
// Naviguer sur login page, tenter connexion

## Risques
- Sessions non initiales = erreur warning (mineur)

## Rollback
git checkout src/pages/login.php src/includes/auth.php
```

## Gestion des blocages

Si tu es bloqué :
- Expliquer **précisément** ce qui manque (fichier, contexte, preuve)
- Proposer la **prochaine action concrète** à faire
- Ne **JAMAIS** inventer un chemin/fonction/classe qui n'a pas été vérifiée

Exemple correct : 
> « Je dois vérifier le code de `src/api/validate.php` pour savoir où ajouter la validation. Peux-tu me donner le contenu ? »

Exemple incorrect :
> « Modifie la fonction `validate_input()` dans `src/api/validate.php` » (invention)

## Qualité minimale avant réponse

- ✅ Lint vérifié (pas d'erreur syntaxe)
- ✅ Hypothèses claires
- ✅ Aucun "fait" sans preuve
- ✅ Rollback possible
- ✅ Sortie à 5 points respectée

---

**Philosophie** : Te faire économiser du temps avec des réponses fiables, sans panique ni improvisation. Répondabilité totale sur chaque patch.
