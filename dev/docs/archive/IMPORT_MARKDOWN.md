# Import d'exercices depuis Markdown

Ce script importe des exercices structurés au format Markdown depuis le dossier `exercices/college/<niveau>/<matiere>/` et les insère dans la table `Exercises`.

## Détection
- **Titre**: premier `# Titre` (H1) du fichier
- **Corrigé**: section `## Solution`, `## Correction` ou `## Réponse` jusqu'à la prochaine section
- **Contenu**: reste du fichier sans le H1

## Lancer l'import
```powershell
php d:\Hostinger\public_html\moncoachscolaire\tools\import_exercises_from_markdown.php
```
Par défaut, cela importe `6ème` pour les matières présentes: Mathématiques, Français, Histoire-Géographie, SVT, Anglais.

## Vérifier
```powershell
php d:\Hostinger\public_html\moncoachscolaire\tools\check_exercises_counts_all.php
php d:\Hostinger\public_html\moncoachscolaire\tools\test_api_get_exercises_cli.php    # Maths
php d:\Hostinger\public_html\moncoachscolaire\tools\test_api_get_exercises_fr_cli.php  # Français
```

## Étendre
Pour importer d'autres niveaux (5ème, 4ème, 3ème), dupliquez la logique dans `tools/import_exercises_from_markdown.php` en ajoutant les dossiers et labels correspondants.
