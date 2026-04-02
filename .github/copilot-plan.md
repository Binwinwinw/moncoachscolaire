## [07/03/2026] Traçabilité documentaire (reprise)

- Références de reprise opérationnelle consolidées : `dev/JOURNAL_REPRISE.md` (journal) et `dev/SUIVI_BUGS_AMELIORATIONS.md` (suivi bugs/améliorations).

## [27/02/2026] Clôture lot robustesse login/diagnostic

- Audit et correction du flux de connexion (login.php) : centralisation redirection, robustesse session, suppression debug HTML.
- Documentation technique enrichie (JOURNAL_REPRISE.md, REGLES_IA.md, CONTEXT_PRODUIT.md).
- Règle de datation documentaire appliquée à chaque enrichissement.

## [Février 2026] Migration UI/UX guides collège, lycée, bac

**Fait** :
- Suppression des cards accordéon sur guides collège, lycée, bac
- Ajout grille de boutons matières + modales accessibles
- Bloc plan d’action harmonisé (palette adaptée)
- Correction palette couleur (vert collège, violet lycée, doré bac)
- Centrage du titre sur la page BAC
- Accessibilité renforcée (focus, aria-modal, navigation clavier)
- Hooks front conservés

**Validé [04/03/2026]** :
- Harmonisation UI/UX (boutons, palette, hooks, accessibilité) sur tous les guides-remediation collège, lycée, bac. Tests manuels réalisés, conformité validée, aucun retour ni régression. Plus aucune action requise à ce niveau.
- Cohérence couleurs
- Accessibilité
- Boutons inscription/connexion

**Rollback** :
- Restaurer versions précédentes des fichiers guides-remediation.php concernés

**Références** :
- DOCUMENTATION.md (détails techniques)
- .github/CONTEXT_PRODUIT.md (contexte produit)
- dev/JOURNAL_REPRISE.md (journal de migration)
### [Février 2026] Refonte UI Collège-Accueil

**Fait** :
- Cards visiteurs collège (4 niveaux) : Tailwind, palette verte, boutons harmonisés, hooks conservés.
- Titre principal modernisé (gradient vert, wording landingpage).
- Compression hauteur cards, accessibilité maintenue.

**À faire** :
- Appliquer ce design aux cards “niveau” élèves connectés/admin.
- Harmoniser boutons sur pages guide-remediation/exercices/cours collège.
- Vérifier cohérence couleurs/gradients sur toutes les pages collège.
- Ajouter test visuel + checklist accessibilité.
- Prévoir harmonisation UI sur lycee-accueil et bac-accueil (palette, boutons, wording).
# Copilot Plan — MonCoachScolaire (Roadmap)

**Ajout du 25/02/2026 :** Migration des tests Playwright pour inclure les pages lycée et bac, impacts sur la roadmap technique et documentation. Voir [docs/SESSION_REPORT_2026.md](../docs/SESSION_REPORT_2026.md).
Dernière mise à jour : 2026-02-12

Ce fichier décrit la roadmap “produit + technique” sous forme de jalons et de lots de travail.
Règle : chaque action doit rester patchable en petits lots (2–3 fichiers max), avec diff + plan de test. [file:42]

---

## 0) Invariants (non négociables)

- Sécurité : PDO + prepared statements, validation serveur, échappement en sortie, pas de leak d’erreurs SQL/PHP en prod. [file:42]
- UX élève : toujours bienveillant, jamais de message négatif. [file:42]
- Compatibilité : ne pas casser l’existant, préférer ajouter puis déprécier. [file:42]
- Front : ne pas casser les hooks JS (classes/ids/data-attributes). [file:42]

---

## 1) État actuel (checkpoint)

- Migration Tailwind : Landing Page terminée (classes Tailwind ajoutées, hooks conservés, build CSS effectué). [file:42]
- Stratégie : cohabitation Tailwind + legacy CSS (migration incrémentale page par page). [file:42]

---

## 2) Inventaire obligatoire (avant d’attaquer “toutes les pages”)

Objectif : avoir une carte fiable “pages → endpoints → assets JS/CSS → hooks” pour éviter les régressions. [file:42]

À produire :
- [x] Inventaire des pages (chemins + URL/param `page=` si applicable) : `dev/reports/pages_inventory.md` (nouveau). [file:42]
- [x] Inventaire des endpoints API (par domaine) : `dev/reports/api_inventory.md` (nouveau). [file:42]
- [x] Inventaire des hooks front (classes/data-attributes utilisés en JS) : `dev/reports/hooks_inventory.md` (nouveau). [file:42]

Sources à utiliser (structure repo) :
- Pages : `src/pages/` [file:42]
- API : `src/api/` (admin, cours, demo, exercices, users, parents, public, legacy) [file:42]
- Assets : `public/assets/` (CSS/JS/images) [file:42]

Critère de fin :
- Chaque page “critique” a ses hooks listés et ses endpoints identifiés. [file:42]

---

## 3) Court terme (Semaine 1–2)

### 3.1 Documentation & contexte Copilot (qualité de livraison)
- [x] Finaliser la factorisation docs :
  - [x] `.github/PROJECT_CONTEXT.md` devient un index court. [file:42]
  - [x] `.github/CONTEXT_PRODUIT.md` = doc produit/humaine. [file:42]
  - [x] `.github/REGLES_IA.md` = règles IA/patterns (ex-`copilot_CONTEXT.md` nettoyé). [file:42]
- [x] Nettoyer `copilot_CONTEXT.md` (doublons + “TITLE Template…” + sections hors sujet), puis renommer en `REGLES_IA.md`. [file:42]
- [x] Prompts : garder `.github/prompts/*` comme source, et les référencer dans `copilot-instructions.md` (pas de duplication). [file:42]

Critère de fin :
- Copilot produit des diffs cohérents et n’invente pas des chemins/fonctions. [file:42]

### 3.2 Stabilisation routing & points d’entrée
- [x] Confirmer le(s) point(s) d’entrée réel(s) (`public/index.php`, `index.php`) et le flux pages/API. [file:42]
- [x] Lister les routes “élève” critiques (dashboard, exercice, cours) et les endpoints API associés. [file:42]
- [x] Smoke tests manuels : landing → login → dashboard élève → ouvrir un exercice. [file:42]

Critère de fin :
- Parcours élève minimal OK en local sans erreur fatale. [file:42]

### 3.3 Qualité API (contrats + erreurs)
- [x] Standardiser les réponses JSON (success/data/error), headers, codes HTTP, gestion JSON invalide. [file:42]
- [x] Vérifier la cohérence des endpoints exercices (list/get/submit) : filtres `is_active`, droits, validation. [file:42]

Critère de fin :
- Les endpoints principaux renvoient des erreurs propres, pas de HTML, pas d’echo parasite. [file:42]

---

## 4) Migration Tailwind — pages restantes (ordre recommandé)

Principe : 1 lot = 1 page (ou 1 petit groupe), avec validation visuelle + hooks conservés + diff + rollback.

Objectif : migrer page par page, ajouter classes Tailwind, conserver hooks legacy (classes/IDs/data-attributes pour JS).

Ordre basé sur inventaire repo (auth → élève → contenu → parent → admin) :
1. **Lot A — Auth & accès (public)** : Login/Register (`src/pages/login.php` / `src/pages/register.php`, URL: `index.php?page=login` / `index.php?page=register`) - Hooks: `.login-form`, `#login-form`.
2. **Lot B — Dashboard Élève (home)** : Dashboard élève (`src/pages/eleve/dashboard.php`, URL: `index.php?page=eleve/dashboard`) - Hooks: `.dashboard-card`, `#dashboard-content`.
3. **Lot C — Page Exercice (élève)** : Exercices collège/lycée/BAC (`src/pages/eleve/college/6eme/exercices-6eme.php` etc., URL: `index.php?page=college/6eme/exercices-6eme`) - Hooks critiques: `.exercise-card`, `data-dynamic-exercises`.
4. **Lot D — Page Cours (si affichage cours hybride)** : Cours/Quiz (`src/pages/system/view_course.php` / `src/pages/system/quiz.php`, URL: `index.php?page=view_course&id={id}` / `index.php?page=quiz`) - Hooks: `.course-content`, `#course-viewer`.
5. **Lot E — Progression** : Progression (`src/pages/system/progression.php`, URL: `index.php?page=progression`) - Hooks: `.progress-chart`, `#progression-content`.
6. **Lot F — Dashboard Parent (MVP UI)** : Dashboard parent/Suivi enfant (`src/pages/dashboard_parent.php` / `src/pages/parents/suivi_enfant.php`, URL: `index.php?page=dashboard_parent` / `index.php?page=suivi_enfant&id={id}`) - Hooks: `.parent-dashboard`, `#parent-dashboard`.
7. **Lot G — Dashboard Admin (base)** : Dashboard admin/Gestion exercices (`src/pages/dashboard_admin.php` / `src/pages/admin/exercices_admin.php`, URL: `index.php?page=dashboard_admin` / NON TROUVÉ) - Hooks: `.admin-stats`, `#admin-dashboard`.
8. **Lot H — Maintenance** : Maintenance (`src/pages/maintenance.php`, URL: `index.php?page=maintenance`) - Hooks: `.maintenance-message`.

Après chaque lot : build CSS (`npm run build:css`), test visuel, smoke test Playwright (si en place).

---

## 5) Composants réutilisables (design system léger)

Objectif : éviter la dérive “un style par page”. [file:42]
- [ ] Définir 5 composants : bouton, carte, badge, alerte/notice, section layout.
- [ ] Écrire des snippets dans `REGLES_IA.md` (classes Tailwind + hooks + variantes). [file:42]
- [ ] Standardiser les “states” (hover/focus/disabled/loading). [file:42]

Critère de fin :
- Les lots Tailwind réutilisent les mêmes patterns, pas de classes copiées/collées au hasard. [file:42]

---

## 6) Backlog technique (à piocher, petits lots)

- [ ] Normaliser les helpers d’URL (`site_url()`, `asset_url()`) et éliminer les liens hardcodés si présents. [file:42]
- [ ] Réduire duplication endpoints : fusionner les API redondantes, archiver dans `src/api/legacy/` si besoin. [file:42]
- [ ] Réduire les anti-patterns front : inline JS, `innerHTML` avec données utilisateur, IDs pour le style. [file:42]
- [ ] Scripts dev : ajouter 1 script CLI de diagnostic “API health” (voir prompt `debug-script`). [file:42]
- [ ] Ajout tests : 1–2 tests API (auth/session) + 1 smoke test UI (si Playwright présent). [file:42]

---

## 7) Format “ticket” recommandé (pour Copilot / PR)

Pour chaque lot :
- Objectif
- Fichiers impactés (prévus)
- Plan de patch (2–3 étapes)
- Plan de test (commandes + parcours UI)
- Risques + rollback [file:42]
