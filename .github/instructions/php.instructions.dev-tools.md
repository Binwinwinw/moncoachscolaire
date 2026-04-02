```instructions
---
name: PHP rules (dev/tools)
description: Conventions PHP du projet (dev/tools)
applyTo: "dev/tools/**/*.php"
---

- Utilise des fonctions petites et testables.
- Pas d'echo/var_dump debug dans les endpoints JSON ; si debug, le mettre sous dev/tools/tests.
- Toute sortie API doit rester JSON valide même en cas d'erreur (pas de warnings HTML).

```
