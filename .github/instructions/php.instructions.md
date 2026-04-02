---
name: PHP rules (src)
description: Conventions PHP du projet (src)
applyTo: "src/**/*.php"
---

- Utilise des fonctions petites et testables.
- Pas d'echo/var_dump debug dans les endpoints JSON ; si debug, le mettre sous dev/tools/tests.
- Toute sortie API doit rester JSON valide même en cas d'erreur (pas de warnings HTML).

## Architecture MonCoachScolaire (février 2026)

### Structure MVC-like
- `src/pages/` : Templates et logique de rendu (eleve/, admin/, system/)
- `src/api/` : Endpoints JSON (exercices, progression, utilisateurs)
- `src/config/` : Configuration et helpers (site_boot.php, config.php)
- `src/database/` : Connexion PDO et requêtes
- `src/utils/` : Fonctions utilitaires réutilisables

### Helpers essentiels (site_boot.php)
- `site_url($path)` : URLs absolues avec gestion local/production
- `asset_url($path)` : URLs vers les assets statiques
- `render_level_navigation()` : Navigation par niveaux scolaires
- `get_theme_by_level($level)` : Thèmes visuels par classe

### Patterns de sécurité
- Sessions HttpOnly + SameSite pour la sécurité
- CSRF tokens sur tous les formulaires
- Validation input avec filter_var() et htmlspecialchars()
- Gestion d'erreurs JSON propre (pas de HTML dans les API)

### Gestion des données
- Base nettoyée : 1088 exercices (34 doublons supprimés le 8/02/2026)
- Utilisateurs : admin/student/parent avec niveaux scolaires
- Progression : XP, achievements, badges, streak
- API first : Toutes les données passent par des endpoints JSON

### Bonnes pratiques établies
- Utiliser PDO avec prepared statements
- Gestion graceful des erreurs DB (connexion peut être null)
- Logs d'admin dans adminlogs pour audit
- Tests dans dev/tools/tests/ avec PHPUnit
