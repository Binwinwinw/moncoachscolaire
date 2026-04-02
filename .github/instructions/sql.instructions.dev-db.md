```instructions
---
name: SQL rules (dev/db)
description: Scripts SQL MySQL/phpMyAdmin (dev/db)
applyTo: "dev/db/**/*.sql"
---

- Les scripts doivent être rejouables (IF EXISTS / IF NOT EXISTS).
- Pour triggers/procédures : inclure DELIMITER + test queries.
- Ne jamais renuméroter une PK si elle est référencée ; proposer sort_order ou une table de mapping.

```
