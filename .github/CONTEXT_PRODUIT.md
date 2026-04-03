# CONTEXT_PRODUIT - MonCoachScolaire

**Ajout du 02/04/2026 :** La suite produit se pilote depuis [../dev/SUIVI_BUGS_AMELIORATIONS.md](../dev/SUIVI_BUGS_AMELIORATIONS.md). Priorites actives retenues : robustesse Quiz AI, validation anti-repetition, smoke test E2E connecte, nouveaux exercices/cours, mode sombre a cadrer.

**Ajout du 02/04/2026 :** Pivot produit Quiz AI confirme.
La generation de quiz par scripts manuels n'est plus une finalite produit. La voie principale est desormais la generation dynamique cote eleve via Quiz AI, avec priorite sur la creation de nouveaux exercices et nouveaux cours.

**Ajout du 01/04/2026 :** Refactorisation gouvernee du fichier pour reprise rapide.
Le contenu historique complet est preserve dans [CONTEXT_PRODUIT_REFERENCE.md](CONTEXT_PRODUIT_REFERENCE.md).

## Role de ce document

Ce fichier centralise le contexte produit de haut niveau:

- objectif pedagogique
- workflows metier prioritaires
- principes UX/qualite
- orientation des contributeurs vers les bonnes sources

## Regles de gouvernance documentaire

- Dater chaque ajout: **Ajout du JJ/MM/AAAA : ...**
- Eviter les doublons: conserver le detail dans une source unique
- Utiliser des liens croises vers les documents specialises

## Sources de verite a consulter en premier

1. [CONTEXT_INDEX.md](../CONTEXT_INDEX.md)
2. [DOCUMENTATION.md](../DOCUMENTATION.md)
3. [README.md](../README.md)
4. [dev/JOURNAL_REPRISE.md](../dev/JOURNAL_REPRISE.md)
5. [dev/SUIVI_BUGS_AMELIORATIONS.md](../dev/SUIVI_BUGS_AMELIORATIONS.md)

## Organisation des contenus (ou documenter quoi)

- Contexte produit et philosophie: [CONTEXT_PRODUIT.md](CONTEXT_PRODUIT.md)
- Roadmap et lots techniques: [copilot-plan.md](copilot-plan.md)
- Regles IA et exigences d execution: [REGLES_IA.md](REGLES_IA.md)
- Contexte global et points d entree: [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md), [../CONTEXT_INDEX.md](../CONTEXT_INDEX.md)
- Outils, scripts et workflows techniques: [../dev/tools/README.md](../dev/tools/README.md)

## Points produit invariants

- Public cible: eleves college -> lycee -> bac, avec accompagnement parent et administration.
- Valeurs: bienveillance, progression, autonomie, feedback constructif.
- Priorites qualite: accessibilite, robustesse des parcours, coherence visuelle par niveau, tracabilite documentaire.
- Axe quiz 2026: generation dynamique via Quiz AI prioritaire; scripts batch limites a un role de support technique.

## Historique detaille et lots anciens

- Tous les details de migration, checklists et historiques datés sont conserves dans [CONTEXT_PRODUIT_REFERENCE.md](CONTEXT_PRODUIT_REFERENCE.md).

## Rollback

- Restaurer la version precedente via [CONTEXT_PRODUIT_REFERENCE.md](CONTEXT_PRODUIT_REFERENCE.md).
- Reappliquer ensuite un refactor incremental si necessaire.
