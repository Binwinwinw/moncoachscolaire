---
name: mysql-safe
description: Migration MySQL sûre et rejouable (phpMyAdmin)
---

Génère un script SQL MySQL sûr et rejouable:
- Pas de renumérotation PK si référencée
- Ajoute colonnes/index/triggers si besoin
- Inclure DELIMITER pour triggers
- Donner requêtes de validation après exécution
