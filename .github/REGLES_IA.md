# REGLES_IA — MonCoachScolaire

**Ajout du 01/04/2026 :** Refactorisation du fichier pour usage opérationnel rapide.
Le détail historique et les exemples étendus sont conservés dans [REGLES_IA_REFERENCE.md](REGLES_IA_REFERENCE.md).

## 1) Règles prioritaires (non négociables)

1. Dire la vérité technique. Ne jamais inventer fichiers, fonctions, classes, tests ou résultats.
2. Sécurité d'abord. Refuser les pratiques dangereuses (SQLi/XSS, secrets exposés, debug sensible).
3. Prouver les affirmations. Expliquer le pourquoi d'un bug/correctif, pas seulement le patch.
4. Être transparent sur l'incertitude. Formuler les hypothèses explicitement et proposer une vérification.
5. Signaler les confusions de cible fichier/page et rediriger vers le bon emplacement.

## 2) Règles de documentation

- Toute mise à jour documentaire doit être datée: **Ajout du JJ/MM/AAAA : ...**
- Respect anti-doublon: ne pas dupliquer, toujours référencer la source de vérité.
- Les ajouts opérationnels majeurs vont dans le journal daté.

## 3) Sources de vérité (à consulter avant action)

- [CONTEXT_INDEX.md](../CONTEXT_INDEX.md)
- [DOCUMENTATION.md](../DOCUMENTATION.md)
- [README.md](../README.md)
- [dev/JOURNAL_REPRISE.md](../dev/JOURNAL_REPRISE.md)
- [dev/SUIVI_BUGS_AMELIORATIONS.md](../dev/SUIVI_BUGS_AMELIORATIONS.md)

## 4) Conventions techniques (fichiers dédiés)

- PHP: [instructions/php.instructions.md](instructions/php.instructions.md)
- JS: [instructions/js.instructions.md](instructions/js.instructions.md)
- SQL: [instructions/sql.instructions.md](instructions/sql.instructions.md)
- Accessibilité front: [instructions/frontend-accessibility.instructions.md](instructions/frontend-accessibility.instructions.md)
- Guide central détaillé: [COPILOT_REFERENCE.md](COPILOT_REFERENCE.md)

## 5) Exigence d'exécution IA

- Audit du contexte avant modification.
- Patch minimal, testable, avec impacts explicités.
- Proposer un rollback simple pour chaque changement significatif.
- En cas de blocage: lister recherches tentées + fichiers consultés + prochaine action.

## 6) Archive complète

- Historique et contenu détaillé conservés dans [REGLES_IA_REFERENCE.md](REGLES_IA_REFERENCE.md).
