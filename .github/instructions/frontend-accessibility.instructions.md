---
name: Frontend Accessibility
applyTo: public/assets/js/**/*.js
---

# Instruction — Accessibilité Frontend

## Objectif

Garantir que toutes les interfaces et composants JS respectent les standards d’accessibilité (WCAG 2.1 AA minimum) : navigation clavier, ARIA, contrastes, feedback utilisateur.

## Règles obligatoires

- **Navigation clavier** : tout élément interactif (bouton, carte, menu) doit être accessible au clavier (tabindex, focus visible, activation via Entrée/Espace).
- **Labels et ARIA** : chaque champ de formulaire ou contrôle doit avoir un label explicite et/ou un attribut ARIA pertinent (`aria-label`, `aria-labelledby`, `aria-describedby`).
- **Feedback utilisateur** : tout message d’erreur, succès ou changement d’état doit être annoncé (aria-live ou équivalent).
- **Contraste** : respecter un ratio de contraste minimum de 4.5:1 pour le texte, 3:1 pour les éléments UI non textuels.
- **Ordre logique** : l’ordre de tabulation doit suivre la logique visuelle et fonctionnelle.
- **Pas de piège clavier** : aucun composant ne doit bloquer la navigation clavier (focus trap non justifié interdit).

## Checklist à lier

- [dev/docs/CHECKLIST_ACCESSIBILITE_ASSETS.md](../../dev/docs/CHECKLIST_ACCESSIBILITE_ASSETS.md)

## Bonnes pratiques

- Utiliser les rôles ARIA natifs avant d’ajouter des rôles personnalisés.
- Tester chaque composant avec un lecteur d’écran (NVDA, VoiceOver).
- Vérifier la navigation sans souris sur tous les écrans.

## Liens utiles

- [WCAG 2.1 (fr)](https://www.w3.org/Translations/WCAG21-fr/)
- [Checklist accessibilité MonCoachScolaire](../../dev/docs/CHECKLIST_ACCESSIBILITE_ASSETS.md)
