# Documentation de la correction - page Exercices et Diagnostic

## Contexte

Suite à la révision de l'UI, 2 pages sont déjà conformes à la charte :

- `landingpage` (ok)
- `eleve/dashboard` (ok)

L'objectif suivant a été de fixer complètement :

- `exercices` - suppression du double bloc de matières
- `diagnostic` - rendre le container semi-translucide pour laisser voir le fond graphique

## Fichiers touchés

- `src/pages/system/exercices.php`
  - suppression de la seconde liste de matières en doublon
  - conservation du nouveau système de filtres actions matières/niveaux

- `src/pages/diagnostic.php`
  - ajustement UI (bouton retour, page d'accueil, structure visible)

- `public/assets/css/pages/diagnostic.css`
  - style de la frame principale : `background: rgba(...,0.22)`, `backdrop-filter: blur(8px)`
  - environnement pro + police Inter
  - suppression du bloc blanc fixe bloquant le fond

- `src/config/site_boot.php`
  - correction de `asset_url()` pour éviter `public/public/...` en local

## Résultat attendu

- toutes les pages ciblées disposent d'un style sobre, professionnel et cohérent avec la charte.
- plus de lien CSS 404/MIME erroné pour footer.css.
- page `exercices` sans doublon matière.

## Tracker de validation

- [x] `landingpage` verifié
- [x] `eleve/dashboard` verifié
- [x] `diagnostic` adapté + mis en place
- [x] `exercices` doublon enlevé
- [x] checks PHP syntaxiques passés

## Prochaine étape

- exécuter un test end-to-end sur la page `exercices` (navigateur avec devtools)
- décliner la même charte sur `progression`, `cours`, `topbar` éventuellement
