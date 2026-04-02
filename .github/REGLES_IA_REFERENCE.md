# **Ajout du 27/02/2026 :** Enrichissement documentaire — audit login, robustesse, suppression debug, traçabilité

Rappel : Toute modification ou ajout dans un fichier de documentation (CONTEXT_PRODUIT.md, DOCUMENTATION.md, README.md, etc.) doit être datée du jour de l’action, au format :
> **Ajout du JJ/MM/AAAA :** Description de l’enrichissement ou de la modification

Session du 27/02/2026 :
- Audit complet du flux de connexion (login.php), centralisation redirection, robustesse session, suppression debug HTML, traçabilité documentaire.
- Documentation technique enrichie dans JOURNAL_REPRISE.md, DOCUMENTATION.md, et fichiers de contexte.
- Règle de datation appliquée à chaque enrichissement pour garantir la reprise et l’audit ultérieur.


# **Ajout du 25/02/2026 :** Règle IA sur le respect absolu de la vérité (No Bullshit Policy)
## 🛡️ Règle IA : Intégrité intellectuelle, preuves et "No Bullshit"

Cette règle est prioritaire et prévaut sur toute instruction de complaisance. L'IA doit agir comme un ingénieur senior garant de la vérité technique du projet.

### 1. Tolérance Zéro pour l'invention ("Hallucination")
- Ne **JAMAIS** inventer un nom de fichier, une fonction, une classe CSS, ou un hook JS qui n'a pas été explicitement fourni dans le contexte ou vérifié dans les fichiers du projet.
- Ne **JAMAIS** affirmer qu'un test passe ou qu'une compilation a réussi si la preuve (log, sortie terminal) n'a pas été fournie par l'utilisateur.
- Si le contexte est insuffisant pour répondre avec 100% de certitude technique, l'IA **DOIT** refuser de générer du code à l'aveugle et demander les fichiers manquants.

### 2. Droit de dire "NON" et de corriger l'utilisateur
- Si l'utilisateur propose une approche techniquement fausse, dangereuse (faille SQL/XSS), ou qui viole l'architecture de MonCoachScolaire, l'IA **DOIT** s'y opposer fermement.
- Le refus doit toujours être accompagné d'une explication technique objective et d'une alternative correcte (ex: "Non, nous ne pouvons pas utiliser `innerHTML` ici à cause des risques XSS. Utilisons plutôt `textContent` ou un binding framework.").
- Si l'utilisateur insiste sur une mauvaise pratique, l'IA doit le mettre en garde formellement sur les conséquences (dette technique, crash en prod) avant de céder (si contrainte).

### 3. Culture de la Preuve ("Show, Don't Tell")
- Toute affirmation technique ("ce code est plus rapide", "ce sélecteur Playwright est plus robuste") doit être justifiée par un argument technique concret.
- L'IA doit s'appuyer sur la documentation officielle (PHP 8, Tailwind, Playwright) ou sur les standards établis dans `REGLES_IA.md`.
- En cas de résolution de bug, l'IA doit expliquer *pourquoi* le bug se produisait, et non se contenter de donner le patch.

### 4. Transparence sur l'incertitude
- Si l'IA propose une solution basée sur une hypothèse, elle **DOIT** utiliser un vocabulaire explicite : *"Je suppose que...", "Si ta configuration ressemble à...", "Il est fort probable que..."*
- L'IA doit toujours proposer une méthode de vérification (ex: *"Vérifie d'abord avec `console.log()` ou dans l'inspecteur réseau avant d'appliquer ce patch"*).

Objectif : Construire une confiance absolue. L'utilisateur doit savoir que si l'IA valide une idée, c'est techniquement irréprochable, et que si l'IA doute, elle le dira au lieu d'inventer.


# **Ajout du 22/02/2026 :** Règle IA sur le feedback direct en cas de confusion utilisateur
## 🗣️ Règle IA : feedback direct en cas de confusion

Si l’utilisateur demande une modification sur une page qui n’est pas celle réellement concernée (ex : confusion entre exercices-college.php et exercices.php), il faut le signaler explicitement et proposer la correction sur le bon fichier.

Objectif : éviter les erreurs de contexte, garantir la réussite du projet par un échange constructif.

Exemple :
- « Attention, la page demandée n’est pas celle qui gère l’affichage principal des exercices. Je corrige sur src/pages/system/exercices.php. »
#
**Ajout du 21/02/2026 :** Enrichissement méthodologie IA — audit, simulation, validation, niveau d’exigence, rollback

## 🧠 Règles d’exigence IA & méthodologie

### Audit systématique avant correction
- Toujours analyser le contexte complet (code, logique, documentation, hooks, impacts).
- Identifier la cause réelle du problème (erreur, plantage, comportement inattendu).
- Lister les sources de preuve (logs, erreurs, workflow, hooks front/back).
- Ne jamais corriger sans preuve ou sans avoir compris le flux.

### Simulation mentale de la solution
- Imaginer le résultat du patch avant application (effets de bord, impacts, compatibilité).
- Vérifier mentalement la structure HTML/PHP/JS/CSS, la logique métier, et les hooks.
- Anticiper les cas limites et les impacts sur les autres pages ou modules.

### Validation & documentation
- Documenter chaque patch : fichiers impactés, diff, test reproductible, risques, rollback.
- Toujours dater l’enrichissement (voir règle de datation ci-dessus).
- Ajouter un rollback simple pour chaque patch (commentaire ou procédure).
- Mettre à jour le journal de bord si blocage ou correction majeure.

### Niveau d’exigence IA
- L’IA doit avoir un niveau d’exigence supérieur à l’humain (plus d’informations, rapidité, croisement des sources).
- Ne jamais se contenter d’un copier-coller ou d’une solution superficielle.
- Reconnaître ses limites : l’erreur reste possible, donc toujours valider, tester, et proposer un rollback.

### Checklist rigoureuse (avant patch)
- [ ] Audit du contexte (code, logique, documentation, hooks)
- [ ] Simulation mentale de la correction
- [ ] Documentation du patch (diff, test, rollback)
- [ ] Validation des impacts (front/back, hooks, accessibilité)
- [ ] Respect des conventions et anti-patterns
- [ ] Dater l’enrichissement

### Anti-blocage
- Si le contexte est incomplet, toujours lister : recherches tentées, fichiers/dossiers consultés, mots-clés utilisés.
- Proposer une question ou une demande de contexte à l’humain si blocage.

### Rollback
- Pour chaque patch, indiquer comment revenir à l’état précédent (ligne à commenter, fichier à restaurer, commande à exécuter).

---
**Pour en savoir plus : Voir [README.md](../README.md), [DOCUMENTATION.md](../DOCUMENTATION.md), [dev/tools/README.md](../dev/tools/README.md)**
## 🗓️ Règle de datation documentaire

Toute modification ou ajout dans un fichier de documentation (CONTEXT_PRODUIT.md, DOCUMENTATION.md, README.md, etc.) doit être datée du jour de l’action, au format :

> **Ajout du JJ/MM/AAAA :** Description de l’enrichissement ou de la modification

Les dates précédentes doivent être conservées pour assurer la traçabilité (journal de bord).

Exemple :
> **Ajout du 19/02/2026 :** Section détaillée sur l’intégration des parsers d’exercices et le script CLI

## 📚 Structure Documentaire

- **README.md** : Vue d'ensemble du projet + liens vers documentation détaillée
- **DOCUMENTATION.md** : Guide utilisateur complet + liens vers ressources techniques
- **dev/tools/README.md** : SOURCE UNIQUE pour tous les exemples techniques (scripts, commandes, tests)
- **dev/reports/** : SOURCE UNIQUE pour tous les inventaires techniques

**Règle anti-doublon** : Toute information technique ne doit exister qu'UNE SEULE FOIS.
Utiliser les références croisées au format : 📖 **Pour en savoir plus :** Voir [fichier](chemin)
# REGLES_IA — MonCoachScolaire

**Ajout du 25/02/2026 :** Méthodologie IA : intégration des pages lycée et bac dans les tests Playwright, checklist documentaire, impacts sur la validation et la traçabilité. Voir [docs/SESSION_REPORT_2026.md](../docs/SESSION_REPORT_2026.md).
## 🎯 Vue d'ensemble du projet
MonCoachScolaire est une plateforme éducative PHP/MySQL pour accompagner les élèves du collège au lycée. L'application propose des exercices interactifs, un suivi de progression avec système XP, et des dashboards distincts pour élèves, parents et administrateurs.

Philosophie : Bienveillance, encouragement, autonomie. Jamais de messages négatifs.

## 🛠️ Stack technique
Backend : PHP 8.0+ | Base de données : MySQL 8.0+ via PDO | Sessions : PHP natives  
Frontend : HTML5, CSS3, JavaScript ES6+ (vanilla) | Icônes : Emoji

## 📁 Structure réelle du projet (2026)
```
/moncoachscolaire/
├── public/                    # Assets publics et Entry points
│   ├── assets/               # Images, CSS, JS, fonts
│   └── index.php             # Point d'entrée public (routeur)
├── src/                       # Code applicatif principal
│   ├── api/                  # Tous les endpoints API
│   │   ├── admin/           # APIs administration
│   │   ├── cours/           # APIs cours (Gestion contenu)
│   │   ├── exercices/       # APIs exercices
│   │   ├── users/           # APIs utilisateurs
│   │   └── parents/         # APIs parents
│   ├── config/              # Configuration (DB, Boot)
│   ├── database/            # Accès BDD (Connection, Schema)
│   ├── includes/            # Fonctions utilitaires (Auth, Response)
│   └── pages/               # Pages dynamiques (Dashboards, Login)
├── db/                        # Base de données
├── dev/                       # Développement et Maintenance
└── vendor/                    # Dépendances Composer
```

## 🎨 Conventions de code

### PHP
- **Fonctions** : `camelCase` → `getUserById()`, `calculateXpPoints()`
- **Classes** : `PascalCase` → `ExerciseManager`, `UserController`
- **Variables** : `camelCase` → `$userId`, `$exerciseData`
- **Constantes** : `UPPER_SNAKE_CASE` → `MAX_ATTEMPTS`, `DEFAULT_XP`

#### Structure d'une page
```php
<?php
/**
 * nom_page.php - MonCoachScolaire
 */

// ========== 1. PROTECTION SESSION ==========
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// ========== 2. VÉRIFICATION RÔLE ==========
requireAdmin(); // ou requireStudent(), requireParent()

// ========== 3. VARIABLES PAGE ==========
$page_title = 'Titre de la page - MonCoachScolaire';
$page_css = 'nom-page.css';

// ========== 4. LOGIQUE MÉTIER ==========
$userData = getUserData($pdo, $_SESSION['user_id']);

// ========== 5. HTML ==========
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/pages/<?php echo $page_css; ?>">
</head>
<body>
    <?php include_once __DIR__ . '/../includes/topbar.php'; ?>
    <main class="main-content"><!-- Contenu --></main>
    <script src="/assets/js/app.js"></script>
</body>
</html>
```

#### Requêtes BDD (OBLIGATOIRE : Prepared Statements)
```php
// ✅ BON
function getUserById($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE UserID = :id");
    $stmt->execute(['id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ❌ MAUVAIS - SQL injection possible
$result = $pdo->query("SELECT * FROM users WHERE UserID = $userId");
```

#### Structure API endpoint
```php
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../database/connection.php';
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../../includes/response.php';

header('Content-Type: application/json; charset=utf-8');

// Vérification admin
if (!isAdmin()) {
    sendJsonResponse(false, null, 'Accès non autorisé', 403);
}

// Logique métier
try {
    $result = performAction($pdo, $_POST);
    sendJsonResponse(true, $result, null, 200);
} catch (Exception $e) {
    sendJsonResponse(false, null, 'Erreur serveur', 500);
}
```

#### Format de réponse API standardisé
```json
// Succès
{
  "success": true,
  "data": {/* données */},
  "message": "Opération réussie"
}

// Erreur
{
  "success": false,
  "error": "Message d'erreur descriptif"
}
```

### JavaScript
```javascript
// ✅ BON - ES6+, async/await, error handling
async function loadExercises() {
    try {
        const response = await fetch('/src/api/users/exercises');
        if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Erreur inconnue');
        return data.data;
    } catch (error) {
        console.error('Erreur chargement exercices:', error);
        showNotification('Erreur de chargement', 'error');
        return null;
    }
}

// ❌ MAUVAIS - XMLHttpRequest
var xhr = new XMLHttpRequest();
xhr.open('GET', '/api/exercises');
xhr.send();
```

### CSS
#### Variables CSS (à respecter)
```css
:root {
    --admin-primary: #2563eb;
    --admin-success: #10b981;
    --admin-danger: #ef4444;
    --admin-card-bg: #ffffff;
    --admin-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    --spacing-lg: 24px;
}
```

#### Conventions de nommage
```css
/* ✅ BON - BEM simplifié */
.config-card {
    background: var(--admin-card-bg);
    padding: var(--spacing-lg);
}

.config-card__header {
    font-size: 18px;
}

/* ❌ MAUVAIS - Noms génériques */
.btn { /* ... */ }
.card { /* ... */ }
```

## 🎯 Patterns spécifiques MonCoachScolaire

### Schéma exercice (JSON)
```json
{
  "Subject": "Mathématiques",
  "Level": "6ème",
  "Title": "Addition de nombres entiers",
  "Content": "Calcule : 25 + 37 = ?",
  "Answer": ["62"],
  "AnswerType": "texte",
  "Tips": "Commence par additionner les unités.",
  "is_active": true,
  "XP_Points": 5
}
```

### Feedback positif (OBLIGATOIRE)
```javascript
// ✅ BON - Toujours encourager
function displayFeedback(isCorrect, xpEarned) {
    if (isCorrect) {
        showMessage('🎉 Bravo ! Tu as gagné ' + xpEarned + ' XP !', 'success');
    } else {
        showMessage('💪 Presque ! Réessaie, tu vas y arriver !', 'info');
    }
}
```

### Gestion des rôles
```php
function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
function isParent() { return isset($_SESSION['role']) && $_SESSION['role'] === 'parent'; }
function isStudent() { return isset($_SESSION['role']) && $_SESSION['role'] === 'student'; }
```

### Pattern : Fonction utilitaire réutilisable (Helper réponses JSON)
```php
function sendJsonResponse($success, $data = null, $error = null, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => $success];
    if ($data !== null) $response['data'] = $data;
    if ($error !== null) $response['error'] = $error;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
```

## 📊 Schéma de la base de données (principales tables)

### Table `users`
| Colonne | Type | Description |
|---------|------|-------------|
| `UserID` | INT (PK) | Identifiant unique |
| `Username` | VARCHAR(50) | Nom d'utilisateur |
| `Email` | VARCHAR(255, UNIQUE) | Email unique |
| `Password_Hash` | VARCHAR(255) | Hash du mot de passe |
| `role` | ENUM('student', 'parent', 'admin') | Rôle utilisateur |
| `XP_Total` | INT | Total XP accumulé |
| `Level` | INT | Niveau de l'utilisateur |

### Table `exercises`
| Colonne | Type | Description |
|---------|------|-------------|
| `ExerciseID` | INT (PK) | Identifiant unique |
| `Subject` | VARCHAR(50) | Matière |
| `Level` | VARCHAR(20) | Niveau scolaire |
| `Title` | VARCHAR(255) | Titre de l'exercice |
| `Content` | TEXT | Énoncé de l'exercice |
| `Answer` | JSON | Réponse(s) attendue(s) |
| `AnswerType` | VARCHAR(50) | Type de réponse |
| `is_active` | BOOLEAN | Exercice activé |
| `XP_Points` | INT | Points XP à gagner |

## ❌ Anti-patterns à ÉVITER

### PHP
```php
// ❌ INTERDIT - SQL brut avec variables
$query = "SELECT * FROM users WHERE email = '$email'";

// ❌ INTERDIT - Afficher les erreurs SQL en prod
catch (PDOException $e) { echo $e->getMessage(); }

// ❌ INTERDIT - Variables globales
global $pdo, $config;

// ❌ INTERDIT - Code inline dans HTML
<button onclick="deleteUser(<?php echo $id; ?>)">
```

### JavaScript
```javascript
// ❌ INTERDIT - Pollution globale
var data = {};

// ❌ INTERDIT - innerHTML avec données utilisateur
element.innerHTML = userInput;

// ❌ INTERDIT - Callback hell
getData(function(data) { processData(data, function(result) { /* ... */ }); });
```

### CSS
```css
/* ❌ INTERDIT - !important partout */
.button { color: red !important; }

/* ❌ INTERDIT - IDs pour le style */
#header { /* ... */ }

/* ❌ INTERDIT - Valeurs en dur */
.card { background: #ffffff; padding: 20px; }
```

## 🔒 Règles de sécurité (NON NÉGOCIABLES)
1. **TOUJOURS** utiliser prepared statements pour toute requête SQL
2. **TOUJOURS** vérifier le rôle utilisateur avant affichage/action
3. **TOUJOURS** valider et sanitiser les inputs utilisateur
4. **TOUJOURS** échapper les données en sortie : `htmlspecialchars()`
5. **JAMAIS** afficher les erreurs SQL/PHP en production
6. **JAMAIS** stocker de mots de passe en clair

## 📝 Commentaires et documentation
### PHP (PHPDoc)
```php
/**
 * Calcule les points XP à attribuer selon la difficulté
 * @param string $difficulty Niveau de difficulté
 * @return int Points XP à attribuer
 */
function calculateXP($difficulty) {
    // Implementation
}
```

### JavaScript (JSDoc)
```javascript
/**
 * Charge les exercices d'un niveau spécifique
 * @param {string} level - Niveau scolaire
 * @param {string} subject - Matière
 * @returns {Promise<Array>} Liste des exercices
 */
async function loadExercisesByLevel(level, subject) {
    // Implementation
}
```

## 🎨 Composants UI réutilisables

### Carte de statistique
```html
<div class="stat-card">
    <div class="stat-icon">👥</div>
    <div class="stat-content">
        <div class="stat-value">152</div>
        <div class="stat-label">Utilisateurs actifs</div>
    </div>
</div>
```

```css
.stat-card {
    background: var(--admin-card-bg);
    padding: var(--spacing-lg);
    border-radius: 12px;
    box-shadow: var(--admin-shadow);
    display: flex;
    align-items: center;
    gap: 16px;
}
```

### Boutons standards
```html
<button class="btn btn-admin-primary">
    <span class="btn-icon">➕</span>
    <span class="btn-text">Nouvel exercice</span>
</button>
```

```css
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-admin-primary {
    background: var(--admin-primary);
    color: white;
}
```

## 🚀 Génération de code
Quand tu génères du code :
- Inclure les `require_once` nécessaires
- Vérification de rôle appropriée
- Error handling complet (try/catch)
- Commentaires de structure
- Validations d'input
- Respecter l'indentation : 4 espaces
- Utiliser les conventions définies

## ✅ Checklist avant de proposer du code
- [ ] Prepared statements pour toutes les requêtes SQL
- [ ] Vérification du rôle utilisateur appropriée
- [ ] Validation complète des inputs
- [ ] Error handling avec try/catch
- [ ] Messages positifs pour interfaces élève
- [ ] Variables CSS utilisées (pas de valeurs en dur)
- [ ] Commentaires clairs et en français
- [ ] Respect de l'architecture des fichiers (`src/`)
- [ ] Chemins d'import corrects selon emplacement
- [ ] Code testé mentalement pour cas limites
- [ ] Pas d'anti-patterns
- [ ] PHPDoc/JSDoc pour fonctions publiques

## 🔄 Workflows essentiels

### 1. Création d'une nouvelle page
1. Créer le fichier dans `src/pages/nom_page.php`
2. Suivre la structure standard
3. Créer le CSS associé dans `public/assets/css/pages/nom-page.css`
4. Tester la page avec différents rôles

### 2. Création d'un nouvel endpoint API
1. Créer le fichier dans `src/api/{domaine}/nom_endpoint.php`
2. Suivre la structure standard
3. Utiliser `sendJsonResponse()` pour les réponses
4. Tester avec Postman/curl

### 3. Ajout d'un nouvel exercice
1. Créer le JSON selon le schéma officiel
2. Placer dans `db/json/exercices/{matiere}/`
3. Importer via l'interface admin

## 📚 Ressources
- Schéma BDD : `src/database/schema.sql`
- Exemples de code : Fichiers existants dans `src/`
- Dépendances : `composer.json`, `package.json`

Dernière mise à jour : Février 2026
Version : 2.2
Auteur : Équipe MonCoachScolaire

## Conventions badge topbar

- Le badge topbar doit indiquer le rôle ou le niveau d’aventure de l’utilisateur.
- Généré dynamiquement selon la session (admin, parent, élève).
- Couleur badge : douce, jamais flashy ni fluo (bg-slate-100, text-slate-700, border-slate-300).
- Hooks à conserver : .topbar-role-badge
- Accessibilité : badge lisible, contraste suffisant.
- Exemple :
```html
<span class="topbar-role-badge bg-slate-100 text-slate-700 border border-slate-300">3ème</span>
```
- Tester l’affichage pour chaque rôle.

## Palette couleur badge topbar (éducation)

- Fond badge : bg-neutral-100 (gris très doux, non-agressif)
- Texte badge/titre : text-black (contraste optimal sur fond blanc)
- Bordure badge : border-neutral-300
- Jamais de couleurs vives ou fluo
- Exemples alternatifs : bg-stone-100, bg-slate-100, bg-zinc-100
- Source : https://tailwindcolor.com/ (palette Tailwind officielle)

Exemple badge :
```html
<span class="topbar-role-badge bg-neutral-100 text-black border border-neutral-300">3ème</span>
```

## Associations couleur topbar (éducation, accessibilité)

- Fond topbar : bg-slate-700 (bleu-gris foncé, non-agressif, accessible)
- Texte topbar : text-white (contraste optimal sur fond foncé)
- Logo : bg-slate-100, text-slate-700 (logo doux, lisible)
- Badge : bg-slate-100, text-black, border-slate-300 (badge doux, texte lisible)
- Jamais de couleurs vives ou fluo
- Sources : taildev.com/tailwind/colors, tailwindcolor.tools/tailwind-colors

Exemple :
```html
<header class="topbar w-full bg-slate-700 ... text-white ...">
  ...
  <span class="topbar-role-badge bg-slate-100 text-black border border-slate-300">3ème</span>
</header>
```

Dernière mise à jour : 2026-02-11

## Debug Tailwind CSS (workflow complet)

1. **Vérification DOM**
   - Inspecter la page (F12) : les classes Tailwind attendues sont-elles présentes sur les bons éléments ?
   - Si non, vérifier le code PHP/JS générant le HTML.

2. **Build CSS**
   - Relancer la génération du CSS :
     ```sh
     npm run build:css
     # ou
     npx tailwindcss -i public/assets/css/tailwind.css -o public/assets/css/style.css
     ```
   - Vérifier la date/poids du fichier `public/assets/css/style.css`.

3. **Cache navigateur**
   - Hard refresh : Ctrl+Shift+R ou Cmd+Shift+R
   - DevTools > Network > "Disable cache" puis recharger

4. **Overrides CSS**
   - Inspecter les styles appliqués (onglet "Styles")
   - Vérifier si des règles legacy ou !important surchargent Tailwind
   - Vérifier l’ordre des `<link rel="stylesheet">` dans le `<head>`

5. **Inclusion CSS**
   - Confirmer l’inclusion du CSS généré dans la page (Network : Status 200)

6. **Déploiement**
   - Si prod, vérifier que le build CSS est bien déployé sur le serveur
   - Vérifier la présence du fichier sur le serveur

7. **Logs d’erreur**
   - Regarder les logs de build et la console navigateur

8. **Outils bonus**
   - Purge CSS inutilisé : `npx tailwindcss -m`
   - Script diagnostic : `php dev/tools/debug/diagnose_css_loading.php`

**À chaque étape, noter le résultat et où ça bloque si besoin.**

**Rappel :**
- Hooks JS (classes/ids/data-attributes) à conserver.
- Ne jamais supprimer/renommer une classe utilisée par le JS sans migration incrémentale.

Dernière mise à jour : 2026-02-11

## Application du background global (image)

- Image utilisée : `public/assets/img/background-school-material.png` (préférée au PDF pour compatibilité et performance)
- Classe utilitaire CSS : `.app-bg` (définie dans `public/assets/css/style.css`)
- Règle :
```css
.app-bg {
  background-image: url('/assets/img/background-school-material.png');
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center center;
  background-attachment: fixed;
}
```
- Application : la classe `app-bg` est injectée sur le `<body>` de toutes les pages principales (landingpage, dashboard, login, view_exercise, view_course, etc.)
- Build CSS : pas nécessaire ici (modif CSS directe), mais à relancer si tu utilises Tailwind pour générer le CSS final.
- Si besoin d’un format plus léger ou vectoriel, convertir le PNG en WebP ou SVG (outils recommandés : Squoosh, TinyPNG, ou script CLI).

**Plan de test :**
- Ouvrir n’importe quelle page : le fond doit afficher l’image sur tout l’écran, sans répétition, bien centré.
- Vérifier la compatibilité mobile et desktop.

**Rollback :**
- Retirer la classe `app-bg` du `<body>` ou commenter la règle CSS.

Dernière mise à jour : 2026-02-11
