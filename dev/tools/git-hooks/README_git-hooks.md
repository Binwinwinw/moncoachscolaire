# Sécurité Git : hook pre-commit

Ce projet utilise un hook pre-commit pour empêcher toute fuite accidentelle de fichiers sensibles (config.php, .env, .sql, .json, logs, etc.) lors des commits.

## Fonctionnement
- Le script .git/hooks/pre-commit bloque automatiquement le commit si un fichier interdit est détecté dans l’index.
- Un message d’alerte s’affiche et le commit est annulé.

## Activation
1. Le hook est déjà copié dans .git/hooks/pre-commit.
2. Si besoin, rendez-le exécutable (Linux/Mac) :
   ```sh
   chmod +x .git/hooks/pre-commit
   ```
3. Il fonctionne automatiquement à chaque commit.

## Personnalisation
- Pour ajouter ou retirer des fichiers surveillés, modifiez la variable BLOQUEURS dans le script.
- Pour désactiver le contrôle, supprimez ou renommez le fichier .git/hooks/pre-commit.

## Bonnes pratiques
- Ne versionnez jamais de secrets ou de données personnelles.
- Utilisez le .gitignore pour tout ce qui doit rester privé.
- Ce hook est une protection supplémentaire, mais la vigilance reste de mise.

---

Pour toute question ou adaptation, voir la section sécurité du README ou contactez l’administrateur du dépôt.
