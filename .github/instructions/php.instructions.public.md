```instructions
---
name: PHP rules (public)
description: Conventions PHP du projet (public)
applyTo: "public/**/*.php"
---

- Utilise des fonctions petites et testables.
- Pas d'echo/var_dump debug dans les endpoints JSON ; si debug, le mettre sous dev/tools/tests.
- Toute sortie API doit rester JSON valide même en cas d'erreur (pas de warnings HTML).

```
