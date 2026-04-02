---
name: JS rules
description: Conventions JS front/admin
applyTo: "public/assets/js/**/*.js"
---

- Utilise ES6+ (const/let, async/await).
- Gère les erreurs fetch (status + JSON invalide).
- Pas de pollution globale : encapsuler et utiliser des IIFE si besoin.

## Contexte MonCoachScolaire (février 2026)

### Fichiers JS clés
- `interactive-exercises.js` : Gestion des types d'exercices (QCM, maths, conjugaison, coloriage)
- `dynamic-exercises.js` : Chargement dynamique des exercices depuis l'API
- `main.js` : Initialisation générale et gestion des événements

### Patterns utilisés
- **Gamification** : Système XP/cristals avec animations de feedback
- **Exercices interactifs** : Validation en temps réel, sauvegarde automatique
- **API calls** : Fetch vers `/api/` avec gestion d'erreurs robuste
- **État local** : localStorage pour la progression hors-ligne

### Événements et hooks
- `exercise-completed` : Déclenché après validation d'exercice
- `progress-saved` : Sauvegarde de progression utilisateur
- Classes CSS : `exercise-card`, `btn-toggle-active`, `feedback-success/error`

### Bonnes pratiques établies
- Toujours vérifier `response.ok` avant de parser le JSON
- Utiliser `async/await` pour les appels API
- Encapsuler dans des fonctions IIFE pour éviter la pollution globale
- Gestion d'erreurs avec messages utilisateur en français
