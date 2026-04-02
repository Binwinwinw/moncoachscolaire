# Guide d'intégration de nouvelles sources d'exercices

## Vue d'ensemble

La plateforme **MonCoachScolaire** peut intégrer des exercices de plusieurs sources. Vous avez actuellement :
- **462 exercices** dans la base de données
- Sources: PDF (Cahiers Complets), Perplexity/Comet, etc.

## Workflow d'intégration

### 1️⃣ Préparation de la source

Les exercices doivent être au format SQL `INSERT INTO exercises`:

```sql
INSERT INTO exercises (Subject, Level, Title, Content, Answer, is_active) VALUES
('Mathématiques', '6ème', 'Titre de l\'exercice', '<p>Contenu HTML</p>', '<p>Réponse HTML</p>', 1),
('Français', '5ème', 'Autre exercice', '<p>...</p>', '<p>...</p>', 1);
```

**Champs obligatoires:**
- `Subject` : Mathématiques, Français, Anglais, Sciences, SVT, Physique-Chimie, Histoire-Géographie, Philosophie
- `Level` : 6ème, 5ème, 4ème, 3ème, Seconde, Première, Terminale, BAC
- `Title` : Titre unique par Level+Subject
- `Content` : Contenu de l'exercice (HTML autorisé)
- `Answer` : Réponse/Correction (HTML autorisé)
- `is_active` : 1 (actif) ou 0 (désactivé)

### 2️⃣ Import depuis un fichier

```bash
php tools/import_exercises.php <chemin_fichier_sql>
```

**Exemple:**
```bash
php tools/import_exercises.php exercices/perplexity_comet.md
```

Le script va automatiquement :
- ✓ Extraire les INSERT statements
- ✓ Valider les données
- ✓ Détecter les doublons
- ✓ Importer les exercices valides
- ✓ Générer un rapport d'import

### 3️⃣ Validation post-import

```bash
php tools/validate_exercises.php
```

Contrôle :
- Champs vides
- Doublons
- Format HTML
- Longueurs excessives
- Catégories valides (Level, Subject)

### 4️⃣ Nettoyage (optionnel)

```bash
php tools/cleanup_exercises.php
```

- Supprime les doublons
- Formate les contenus en HTML
- Génère statistiques finales

## Statistiques actuelles

**Total:** 462 exercices

**Par niveau:**
- 6ème: 69
- 5ème: 99
- 4ème: 90
- 3ème: 40
- Seconde: 45
- Première: 48
- Terminale: 56
- BAC: 15

**Par matière:**
- Mathématiques: 159
- Français: 135
- Anglais: 98
- Physique-Chimie: 14
- SVT: 15
- Sciences: 25
- Histoire-Géographie: 38
- Philosophie: 5

## Format des fichiers source

### Option 1: SQL INSERT direct

```sql
INSERT INTO exercises (Subject, Level, Title, Content, Answer, is_active) VALUES
('Mathématiques', '6ème', 'Exercice 1', '<p>Contenu</p>', '<p>Réponse</p>', 1);
```

### Option 2: Format Markdown (comme Perplexity)

Le script peut aussi parser des fichiers `.md` contenant des INSERT statements formatés.

### Option 3: JSON (à venir)

```json
{
  "exercises": [
    {
      "subject": "Mathématiques",
      "level": "6ème",
      "title": "Exercice 1",
      "content": "<p>Contenu</p>",
      "answer": "<p>Réponse</p>"
    }
  ]
}
```

## Conseils de qualité

1. **Contenu HTML:** Utilisez `<p>`, `<ol>`, `<li>`, `<sup>`, `<strong>`, etc.
2. **Unicité:** Chaque exercice doit avoir un titre unique par niveau+matière
3. **Réponses complètes:** Évitez "Non fournie" ou "À compléter"
4. **Caractères spéciaux:** Échappez les quotes (') → \'
5. **Longueur:** Content < 5000 chars, Answer < 2000 chars

## Commandes rapides

```bash
# Compter les exercices par niveau
php tools/stats_exercises.php

# Valider l'intégrité
php tools/validate_exercises.php

# Nettoyer
php tools/cleanup_exercises.php

# Importer depuis fichier
php tools/import_exercises.php <fichier>
```

## Troubleshooting

### "Doublon détecté"
→ Vérifiez que les exercices ne sont pas déjà importés

### "Niveau/Matière invalide"
→ Vérifiez l'orthographe exacte (case-sensitive)

### "Aucun INSERT trouvé"
→ Le format du fichier ne correspond pas à attendu

## Prochain développement

- [ ] Importeur JSON
- [ ] Interface web d'import
- [ ] Détection automatique des doublons
- [ ] Fusion intelligente des sources
- [ ] Export vers différents formats
