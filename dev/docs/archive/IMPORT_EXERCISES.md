# Import des exercices depuis JSON

Ce guide explique comment remplacer les exercices existants par de nouvelles sources (avec corrigés) via des fichiers JSON.

## Schéma JSON

Chaque fichier JSON décrit un lot d'exercices pour un couple (niveau, matière):

```
{
  "level": "6ème",
  "subject": "Mathématiques",
  "replace": true,
  "source_pdf": "exercices/Exercices 6ème Collège.pdf",
  "corrections_pdf": "exercices/Corrigés Complets 6ème.pdf",
  "exercises": [
    {"title": "…", "content": "…", "answer": "…", "tags": ["…"], "difficulty": 1}
  ]
}
```

- `replace`: si `true`, supprime les anciens exercices pour ce couple (niveau/matière) avant insertion.
- `source_pdf` et `corrections_pdf`: champs libres pour la traçabilité (non utilisés par l’API actuellement).

## Emplacement des fichiers

Placez vos fichiers dans le dossier [exercices](../exercices), par ex.:
- [exercices/college-6eme-mathematiques.json](../exercices/college-6eme-mathematiques.json)
- [exercices/college-6eme-francais.json](../exercices/college-6eme-francais.json)

## Importer en base de données

L’outil d’import lit tous les `*.json` présents dans `exercices/` (ou les chemins passés en arguments) et insère dans la table `Exercises`.

Commande (PowerShell/Windows):

```powershell
php d:\Hostinger\public_html\moncoachscolaire\tools\import_exercises_from_json.php
# ou
php d:\Hostinger\public_html\moncoachscolaire\tools\import_exercises_from_json.php d:\Hostinger\public_html\moncoachscolaire\exercices\college-6eme-mathematiques.json d:\Hostinger\public_html\moncoachscolaire\exercices\college-6eme-francais.json
```

Pré-requis:
- Base de données accessible via `db/connection.php` (variables `.env` ou `config.php`).
- Ne pas être en mode lecture seule (`DB_READ_ONLY=false`).

## Vérification

- API: [api/get_exercises.php](../api/get_exercises.php) → `?action=exercises&level=6ème&subject=Mathématiques` doit retourner les nouveaux exercices.
- Pages: vérifiez l’affichage sur [exercices.php](../exercices.php) et les pages spécialisées.

## Remarques

- Le contenu JSON peut être produit manuellement depuis vos PDF (résumés, consignes, corrigés). Pour un import massif, on peut ajouter un parseur dédié au besoin.
- Le champ `Answer` reste optionnel mais est privilégié par le tri « smart » dans l’API.
