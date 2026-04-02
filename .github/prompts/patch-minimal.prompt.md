---
name: patch-minimal
description: Générer un patch minimal sans casser l’existant
---

@workspace Propose un patch minimal.

Contraintes:
- Ne touche pas au front sauf demande explicite.
- Compatibilité totale.
- Donne un diff unified + tests (commandes PowerShell si possible).
