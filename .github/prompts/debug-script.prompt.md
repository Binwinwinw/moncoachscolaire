---
name: debug-script
description: Générer un script CLI (PHP/Node) pour inspecter l’API
---

@workspace Écris un script CLI de debug (PHP et/ou Node) pour extraire des champs précis d’une réponse API.

Exigences:
- Args: --baseUrl, --id, --action optionnel
- Afficher HTTP_STATUS + extrait brut si JSON invalide
- Si champ absent: "NON TROUVÉ"
- Fournir commandes d’exécution PowerShell
