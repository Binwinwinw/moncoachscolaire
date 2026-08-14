# AUDIT SECURITE - 07-01-2026

## Addendum correctifs (07-01-2026, lot complementaire)

Correctifs appliques suite a revue:

- reset password: token desormais stocke hache en base (`sha256`) dans [src/pages/forgot_password.php](src/pages/forgot_password.php)
- reset password: validation prioritaire sur hash, avec compatibilite transitoire pour anciens tokens en clair dans [src/pages/reset_password.php](src/pages/reset_password.php)
- reset password: suppression du log contenant le lien/token complet dans [src/pages/forgot_password.php](src/pages/forgot_password.php)
- login: message d'erreur unifie pour reduire l'enumeration utilisateur dans [src/pages/login.php](src/pages/login.php)
- login: suppression des logs de prefixes de token CSRF et session id dans [src/pages/login.php](src/pages/login.php)

Preuves rapides:

- `php -l src/pages/forgot_password.php` -> OK
- `php -l src/pages/reset_password.php` -> OK
- `php -l src/pages/login.php` -> OK

Reste a traiter (prochain lot recommande):

- rate limiting persistant (DB/cache) pour survivre au changement de session
- retrait de la creation runtime de table `AdminLogs` dans `logAdminAction`
- hygiene supplementaire des logs d'auth en mode production

Date: 2026-07-01
Perimetre: authentication, session, reset password, DB bootstrap, admin access, middleware API.
Branche: feature/normalize-exercises-files

## 1) login.php

Fichier: src/pages/login.php

Extraits critiques actuels (auth, CSRF, session, DB indisponible)

- Retrouver utilisateur:
  - SELECT par Username OR Email OR Telephone, puis fallback parent similaire.
  - Requete principale: lignes ~204-222.

- Comparaison mot de passe:

if (is_string($passwordHash) && $passwordHash !== '' && strpos($passwordHash, '$') === 0) {
    $passwordValid = password_verify($password, $passwordHash);
} elseif (is_string($passwordHash) && $passwordHash !== '') {
    error_log("LOGIN: hash de mot de passe non supporte pour l'utilisateur " . ($username ?? 'inconnu'));
}

- CSRF:
  - Generation: ligne 121.
  - Verification: lignes 138-146.

- Rate limit:
  - Appel checkLoginAttempts: ligne 179.
  - recordFailedAttempt sur echec: lignes 334, 338.

- Session:
  - Regeneration apres login: ligne 300.

- DB indisponible:

$auth_reason = 'db_unavailable';
$\_SESSION['\_\_login_last_attempt']['reason'] = $auth_reason;
$error = "La base de donnees est temporairement indisponible. Veuillez reessayer plus tard.";

- Message echec user inexistant:

$error = "❌ Cet identifiant n'existe pas. Veuillez verifier ou creer un compte.";

- Message echec mot de passe:

$error = "❌ Mot de passe incorrect. Veuillez verifier votre mot de passe.";

Constat securite

- Plus de fallback plain text ni demo automatique dans le flux POST.
- Enumeration possible: messages differents user inexistant vs mot de passe invalide.
- Des logs de debug existent encore (session id, prefixes token CSRF).

## 2) register.php

Fichier: src/pages/register.php

Extraits critiques actuels (inscription + schema check)

- Verification schema sans mutation:

function checkUsersTableColumns(PDO $pdo): bool
{
    $requiredColumns = ['Nom', 'Prenom', 'Telephone', 'ParentId'];
    $stmt = $pdo->query('SHOW COLUMNS FROM `users`');
    ...
    if (!in_array($column, $existingColumns, true)) {
        error_log("register.php: missing users column {$column}; run DB migration before parent registration.");
return false;
}
}

- Message utilisateur si schema absent:

$error = "La structure de la base de donnees n'est pas a jour pour l'inscription parent. Merci de contacter l'administrateur.";

- Hash mot de passe eleve et parent:
  - password_hash(...) lignes ~174 et ~259.

Preuve absence mutation schema dans ce fichier

- Aucun ALTER TABLE / CREATE TABLE / CREATE INDEX detecte dans src/pages/register.php.
- Recherche effectuee: grep de motifs SQL schema et resultat negatif sur ces motifs dans ce fichier.

## 3) config.php / connection.php

Fichiers:

- src/config/config.php
- src/database/connection.php

Variables d'environnement lues

- DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD, DB_DATABASE, APP_ENV, DB_READ_ONLY.

Variables obligatoires

- DB_USERNAME et DB_DATABASE (preuve: config.php ligne 129, puis validation lignes ~299-303).
- connection.php refuse aussi si DB_DATABASE ou DB_USERNAME vides (lignes ~120-123).

Comportement si variable absente

- pdo = null
- dbUnavailable = true
- error_log explicite
- pas de fallback root/blank implicite.

Extrait config.php

if ($envUser === '' || $envDb === '') {
    $pdo = null;
    $dbUnavailable = true;
    $dbErrorMessage = 'Configuration BDD invalide : DB_USERNAME et DB_DATABASE doivent etre definis...';
    error_log($dbErrorMessage);
}

Extrait connection.php

if ($envDbName === '' || $envDbUser === '') {
$pdo = null;
$dbUnavailable = true;
error_log('MonCoachScolaire: DB configuration missing; define DB_USERNAME and DB_DATABASE in .env or the environment.');
}

Note

- DB_HOST garde une valeur par defaut (127.0.0.1 en local, localhost sinon).

## 4) login_security.php

Fichier: src/includes/login_security.php

Mecanisme rate limiting

- Stockage: $\_SESSION['login_attempts']
- Cle: md5(identifier + REMOTE_ADDR)
- Fenetre: 900 secondes
- Seuil: 5 echecs
- Blocage: message avec minutes restantes

Extraits

$key = md5($identifier . $_SERVER['REMOTE_ADDR']);
$attempts = $_SESSION['login_attempts'][$key] ?? ['count' => 0, 'time' => 0];
if ($attempts['count'] >= 5) { ... }

CSRF

- generateCSRFToken: random_bytes(32)
- verifyCSRFToken: hash_equals

## 5) reset_password.php (+ forgot_password.php pour generation)

Fichiers:

- src/pages/forgot_password.php
- src/pages/reset_password.php

Cycle token actuel

- Generation: forgot_password.php ligne ~43 via bin2hex(random_bytes(32))
- Expiration: now + 3600s (1h)
- Stockage: INSERT password_resets avec token en clair
- Invalidation avant emission: UPDATE password_resets SET used = 1 WHERE email = ?
- Invalidation apres usage: UPDATE password_resets SET used = 1 WHERE id = ?
- Verification en reset: SELECT sur token exact + used=0 + expires_at > NOW()

Messages anti-enumeration

- forgot_password: message uniforme
  - "Si un compte existe pour cet email, un lien de reinitialisation a ete envoye."

CSRF reset form

- Token present et verifie avant changement de mot de passe.

Politique mot de passe

- Minimum 8 caracteres au reset.

Point faible

- Token stocke en clair en base (non hache).

## 6) admin_auth.php / middleware.php / bootstrap.php

Fichiers:

- src/includes/admin_auth.php
- src/api/\_core/bootstrap.php
- src/api/\_core/middleware.php

Identification admin

- Session user_id + logged_in requis.
- Cache session user_role == admin accepte immediatement.
- Sinon verification DB: SELECT Role FROM users WHERE Id = ?.

Protection routes sensibles

- requireAdmin() redirige web ou throw Exception pour API.
- middleware API: api_require applique auth, roles, csrf, rate limit.

Extraits

if (!empty($rules['auth'])) { require_auth(); }
if (!empty($rules['roles'])) { require_role((array) $rules['roles']); }
if (!empty($rules['csrf'])) { require_same_origin(); ... require_csrf($token); }
if (!empty($rules['rate'])) { rate_limit($key, $limit, $window); }

Point faible

- admin_auth.php contient une mutation schema runtime dans logAdminAction:
  - CREATE TABLE IF NOT EXISTS AdminLogs (...)
- Ce n'est pas public, mais ce n'est pas ideal (migration hors requete recommandee).

## 7) Session management (preuves)

Reglages observes

- session.cookie_httponly = 1 (config.php ligne 276)
- session.cookie_secure = 1 si HTTPS (config.php ligne 272)
- session.cookie_samesite = Lax si HTTPS (config.php ligne 281)
- session.use_only_cookies = 1 (config.php ligne 285)
- session.gc_maxlifetime = 86400 (config.php ligne 292)
- session.use_trans_sid = 0 (config.php ligne 295)
- session_regenerate_id(true) apres login (login.php ligne 300) et apres inscription (register.php)
- logout: session cookie purge + session_destroy (src/pages/logout.php ligne 30)

API bootstrap

- session_start avec cookie_httponly true, cookie_samesite Strict, cookie_secure dynamique, use_strict_mode true.

## 8) Diff reel des correctifs

Commande utilisee

- git diff -- src/pages/login.php src/pages/register.php src/pages/reset_password.php src/includes/login_security.php src/database/connection.php src/config/config.php src/includes/admin_auth.php src/api/\_core/bootstrap.php src/api/\_core/middleware.php

Fichiers avec diff actif actuellement

- src/pages/login.php
- src/pages/register.php
- src/database/connection.php

Resume diff login.php

- Supprime fallback demo et fallback plain text
- Garde uniquement password_verify sur hash prefixe $
- Message DB indisponible durci (plus de mode demo)

Resume diff register.php

- Supprime ensureUsersTableColumns avec ALTER TABLE/ADD INDEX
- Ajoute checkUsersTableColumns (SHOW COLUMNS read-only)
- Bloque inscription parent proprement si schema incomplet

Resume diff connection.php

- Supprime fallback implicite root/empty
- Rend DB_USERNAME/DB_DATABASE obligatoires
- fail securely: pdo null + dbUnavailable + log

## 9) Reponses OUI/NON + preuve

1. Fallback demo/legacy/plain/local/debug login encore present dans login.php ?

- Partiellement OUI.
- NON pour bypass authentification demo/legacy/plain text (supprimes du flux POST, diff login.php).
- OUI pour traces demo/debug restantes:
  - nettoyage session is_demo (login.php lignes 80-89)
  - marquage is_demo si compte demo existe en base (ligne 292)
  - bloc debug conditionnel via ?debug=1 (lignes 384-395)

2. Connexion DB possible en root/empty sans config explicite ?

- NON pour fallback implicite.
- OUI seulement si fourni explicitement via variables env.
- Preuve: connection.php exige DB_USERNAME/DB_DATABASE sinon pdo null (lignes ~120-123).

3. register.php contient encore ALTER TABLE / CREATE TABLE / CREATE INDEX ?

- NON.
- Preuve: grep register.php ne renvoie que SHOW COLUMNS et checkUsersTableColumns.

4. Session ID regenere apres login reussi ?

- OUI.
- Preuve: login.php ligne 300 session_regenerate_id(true).

5. Cookies session HttpOnly Secure SameSite definis ?

- OUI partiel.
- HttpOnly: oui.
- Secure: oui si HTTPS.
- SameSite: Lax en web HTTPS, Strict en bootstrap API.

6. Rate limit survit a recreation session/nouvelle fenetre ?

- NON.
- Stockage en $\_SESSION uniquement.

7. Messages login identiques entre compte inexistant et mot de passe invalide ?

- NON.
- Deux messages differents, enumeration possible.

8. Tokens reset usage unique et expiration ?

- OUI.
- used=1 + expires_at > NOW() + invalidation avant/apres.

9. Logs contiennent mot de passe/token/hash ou erreur SQL brute ?

- Mot de passe en clair: NON observe.
- Token reset complet en log: OUI (forgot_password.php: error_log du lien complet).
- Prefixe token CSRF logge dans login: OUI (login.php lignes 140-141).
- Erreurs SQL brutes: parfois exception message en logs (connexion PDO et auth exceptions).

## 10) Commandes et preuves d'execution

Sortie php -l

- No syntax errors detected in:
  - src/pages/login.php
  - src/pages/register.php
  - src/pages/reset_password.php
  - src/includes/login_security.php
  - src/database/connection.php
  - src/config/config.php
  - src/includes/admin_auth.php
  - src/api/\_core/bootstrap.php
  - src/api/\_core/middleware.php

Notes commande grep

- rg indisponible sur cet environnement PowerShell.
- Preuves collectees via grep_search avec numéros de ligne.

## 11) Fichiers complets vs extraits

- Fichiers courts fournis quasi complets dans cet audit: login_security.php, connection.php, admin_auth.php, bootstrap.php, middleware.php, reset_password.php, forgot_password.php, logout.php.
- Fichiers longs fournis par extraits fonctionnels critiques: login.php, register.php, config.php.

Pour dump complet literal des 9 fichiers dans une annexe unique, ajouter une section Annexe brute dans un second fichier pour eviter un rapport de taille excessive.

## Addendum lot 2 — journalisation structurée (01/07/2026)

Correctifs appliqués:

- ajout d'une classe de journalisation structurée dans [src/includes/security_logger.php](src/includes/security_logger.php)
- ajout de la migration [db/migration_add_auth_logs_20260701.sql](db/migration_add_auth_logs_20260701.sql)
- instrumentation des flux login, forgot_password, reset_password et admin_action
- masquage des identifiants, sanitisation des métadonnées et purge de logs ancienne

Validation:

- `php -l src/includes/security_logger.php` -> OK
- `php -l src/pages/login.php` -> OK
- `php -l src/pages/forgot_password.php` -> OK
- `php -l src/pages/reset_password.php` -> OK
- `php -l src/includes/admin_auth.php` -> OK
