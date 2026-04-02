---
name: SQL rules (db)
description: Scripts SQL MySQL/phpMyAdmin (db)
applyTo: "db/**/*.sql"
---

- Les scripts doivent être rejouables (IF EXISTS / IF NOT EXISTS).
- Pour triggers/procédures : inclure DELIMITER + test queries.
- Ne jamais renuméroter une PK si elle est référencée ; proposer sort_order ou une table de mapping.

## Schéma MonCoachScolaire (février 2026)

### Tables principales
- `users` : Utilisateurs (admin/student/parent) avec niveaux scolaires
- `exercises` : 1088 exercices actifs (nettoyés le 8/02/2026)
- `courses` : Cours structurés avec contenu enrichi
- `userprogress` : Progression XP et niveaux des élèves
- `achievements` & `userachievements` : Système de badges
- `quiz` & `quizresult` : Tests et résultats

### Relations clés
- Exercises liés aux Courses via `exercisecourselinks`
- Progression utilisateur via `userprogress` et `userprogresshistory`
- Réponses d'exercices dans `exerciseresponses`
- Logs d'activité dans `adminlogs` et `userloginhistory`

### État actuel de la base
- **Total exercices** : 1088 (34 doublons supprimés)
- **Répartition** : Seconde(217), Terminale(260), Première(128), 6ème(226), 5ème(151), 4ème(42), 3ème(64)
- **Utilisateurs actifs** : ~12 comptes (demos + admins par niveau)
- **Cours enrichis** : 331 cours avec explanation/key_point/examples

### Patterns établis
- Indexes sur les clés étrangères pour les performances
- JSON valide dans `ProgressJson` avec validation CHECK
- Soft deletes avec flags `is_active`
- Historique des modifications dans les tables *history

### Scripts de maintenance
- `cleanup_exercises.php` : Suppression doublons + wrapping HTML
- Tables avec auto-increment et contraintes d'intégrité
- Migrations versionnées dans `dev/tools/db/`
- Exports/imports via `dev/tools/import_export/`
