# Prompt détaillé pour génération de quiz diagnostics MonCoachScolaire

Objectif : Générer automatiquement des quiz diagnostics pour chaque niveau scolaire (6eme, 5eme, 4eme, 3eme, 2nde, 1ere, Terminale) et chaque matière présente dans l’application MonCoachScolaire (Mathématiques, Français, Anglais, SVT, Physique-Chimie, Histoire-Géographie, etc.).

## Contraintes
- Pour chaque niveau/matière, produire un quiz complet et adapté au niveau, avec au moins 5 questions variées (QCM, vrai/faux, calcul, rédaction, etc.).
- Chaque quiz doit comporter : titre, description, liste de questions, réponses, corrections, et notions associées si pertinent.
- Respecter la structure officielle des tables SQL : `contents`, `quiz`, `exercisenotion`, `exerciseresponses` (voir exemples JSON ci-dessous).
- La sortie doit être un fichier JSON par niveau/matière, nommé selon le modèle : `diagnostic_6eme_mathématiques.json`, `diagnostic_2nde_francais.json`, etc.
- Les fichiers doivent être organisés par dossier : `college/` pour 6eme à 3eme, `lycee/` pour 2nde à Terminale.
- Les clés et types doivent correspondre strictement aux exemples fournis pour garantir l’import direct dans la base ou via l’API.
- Encodage UTF-8, formatage lisible, un quiz par fichier.

## Exemples de structure JSON à respecter

### Table `contents`
```json
{
  "id": 1,
  "title": "Quiz : Diagnostic 6eme Mathématiques",
  "type": "quiz",
  "level": "6eme",
  "subject": "Mathématiques",
  "description": "Quiz de diagnostic pour évaluer les acquis en mathématiques niveau 6ème.",
  "content_url": null,
  "status": "published",
  "created_at": "2026-02-28 10:00:00",
  "updated_at": "2026-02-28 10:00:00"
}
```

### Table `quiz`
```json
{
  "id": 1,
  "content_id": 1,
  "title": "Quiz Diagnostic 6eme Mathématiques",
  "level": "6eme",
  "subject": "Mathématiques",
  "questions": [
    {
      "id": 1,
      "type": "qcm",
      "question": "Combien font 3 + 5 ?",
      "choices": ["6", "7", "8", "9"],
      "answer": "8",
      "correction": "3 + 5 = 8"
    },
    {
      "id": 2,
      "type": "vrai-faux",
      "question": "Une fraction peut être simplifiée.",
      "answer": true,
      "correction": "Oui, on peut simplifier une fraction si le numérateur et le dénominateur ont un diviseur commun."
    }
    // ... autres questions
  ]
}
```

### Table `exercisenotion`
```json
{
  "id": 1,
  "quiz_id": 1,
  "notion": "Fractions",
  "description": "Comprendre et manipuler les fractions.",
  "example": "1/2 + 1/4 = 3/4"
}
```

### Table `exerciseresponses`
```json
{
  "id": 1,
  "question_id": 1,
  "user_id": 123,
  "response": "8",
  "is_correct": true,
  "submitted_at": "2026-02-28 10:05:00"
}
```

## Consignes complémentaires
- Pour chaque quiz, veille à la diversité des questions et à la clarté des consignes.
- Les fichiers doivent être directement exploitables pour l’import ou l’intégration dans MonCoachScolaire.

📖 Pour la structure officielle complète, voir le schéma SQL dans [db/moncoachscolaire_20260228.sql](../db/moncoachscolaire_20260228.sql).

Ce prompt garantit une génération structurée, exhaustive et compatible avec l’application.
