# Documentation API MonCoachScolaire

Cette documentation liste les principaux points d’entrée de l’API PHP (src/api/). Pour chaque endpoint, sont indiqués : la méthode, l’URL, les paramètres attendus et la réponse.

## Endpoints principaux

### Authentification & Utilisateurs

- **POST** `/src/api/admin_create_user.php` : Création d’un utilisateur admin
- **GET** `/src/api/get_user_progress.php` : Récupère la progression d’un utilisateur
- **POST** `/src/api/log_exercise_result.php` : Enregistre un résultat d’exercice
- **GET** `/src/api/user_stats.php` : Statistiques utilisateur

### Exercices & Cours

- **GET** `/src/api/get_exercises.php` : Liste des exercices filtrés
- **GET** `/src/api/courses.php` : Liste des cours
- **GET** `/src/api/courses_detail.php` : Détail d’un cours
- **GET** `/src/api/resources.php` : Liste des ressources pédagogiques

### Progression & Gamification

- **GET** `/src/api/get_progress_chart.php` : Données pour graphique de progression
- **POST** `/src/api/save_progress.php` : Sauvegarde la progression
- **GET** `/src/api/user_xp.php` : Points d’expérience utilisateur

### Diagnostic & Demo

- **POST** `/src/api/submit_diagnostic.php` : Soumission d’un diagnostic
- **GET** `/src/api/get_demo_content.php` : Contenu de démonstration

### IA & Génération de quiz

- **POST** `/src/api/ia/generate_quiz.php` : Génère un quiz via IA (Ollama/container ou provider cloud)
  - **Payload JSON** :
    ```json
    {
      "level": "6eme",
      "subject": "Mathématiques",
      "type": "quiz",
      "provider": "ollama" // optionnel, sinon auto-détection
    }
    ```
  - **Sécurité** : validation forte des paramètres, auth requise pour la sauvegarde, fallback automatique si erreur IA
  - **Réponse** :
    ```json
    {
      "success": true,
      "quiz_html": "<div ...>",
      "questions": [ ... ],
      "subject": "Mathématiques",
      "level": "6eme"
    }
    ```
  - **Notes** :
    - Le provider peut être explicitement fixé à 'ollama' pour utiliser le container local (nécessite OLLAMA_API_URL dans .env)
    - Fallback automatique sur les autres providers si Ollama non dispo
    - Voir la doc centrale pour le format JSON attendu et les consignes IA

## Convention de réponse

Toutes les réponses sont au format JSON, avec généralement :

- `success` (bool)
- `data` (array|object)
- `message` (string)

## Exemple de requête

```http
GET /src/api/get_exercises.php?level=6eme&subject=francais
```

## Pour aller plus loin

- Voir chaque fichier dans `src/api/` pour les paramètres détaillés.
- Ajouter des exemples de payloads et de réponses selon les besoins.
