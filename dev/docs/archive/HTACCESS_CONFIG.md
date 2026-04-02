# Configuration .htaccess Hybride - Local & Production

## 📋 Vue d'ensemble

Le fichier `.htaccess` est maintenant **hybride** et s'adapte automatiquement à l'environnement :

- **LOCAL** : Développement sans restrictions strictes (debugging facile)
- **PRODUCTION** : Sécurité renforcée (fichiers protégés, erreurs cachées)

## 🔍 Détection de l'environnement

Le système détecte automatiquement l'environnement selon le **Host/Domain** :

### LOCAL (développement)
```
localhost
127.0.0.1
*.local (moncoachscolaire.local)
dev.*  (dev.moncoachscolaire.com)
*.test (moncoachscolaire.test)
```

### PRODUCTION (tous les autres domaines)
```
moncoachscolaire.fr
www.moncoachscolaire.fr
api.moncoachscolaire.fr
(tous les domaines non listés ci-dessus)
```

## 🔐 Règles de sécurité

### ✅ TOUS LES ENVIRONNEMENTS

| Règle | Effet |
|-------|-------|
| Options -Indexes | Empêche le listing des dossiers |
| Support MIME vidéo | `.webm`, `.mp4` configurés |
| Compression GZIP | Réduit la taille des fichiers |
| Cache navigateur | Améliore les performances |
| Headers de sécurité | CSP, X-Frame-Options, etc. |
| Protection sessions | HttpOnly, SameSite, cookies sécurisés |

### 🛡️ PRODUCTION UNIQUEMENT

| Règle | Cibles | Effet |
|-------|--------|-------|
| Blocage fichiers sensibles | `.env`, `.md`, `.sql`, `config.php`, etc. | Erreur 403 |
| Dossiers protégés | `.git/`, `tools/`, `db/`, `includes/`, etc. | Erreur 403 |
| Blocage PHP uploads | `uploads/`, `public/`, `files/` | Erreur 403 |
| Masquer info serveur | Headers Server/X-Powered-By | Pas d'exposition |
| Erreurs PHP cachées | Affichage des erreurs désactivé | Logs uniquement |

### 💻 LOCAL UNIQUEMENT (Facilite le debugging)

| Règle | Effet |
|-------|-------|
| Affichage des erreurs PHP | Développement plus facile |
| Accès aux fichiers sensibles | Permet de tester/déboguer |
| Info serveur visible | Pas de masquage des headers |

## ⚙️ Configuration avancée

### Activer HTTPS en production

Décommenter dans `.htaccess` (ligne ~140) :

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{HTTPS} off
  RewriteCond %{HTTP_HOST} ^(www\.)?moncoachscolaire\.fr$ [NC]
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
</IfModule>
```

### Augmenter les limites d'upload

Décommenter et adapter (ligne ~145) :

```apache
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
```

### Activer la protection XSS/SQL avancée

Décommenter (ligne ~113) :

```apache
RewriteRule .* - [F,L]  # Bloquer les requêtes suspectes
```

## 🚀 Pour tester localement

### Option 1 : localhost
```bash
http://localhost/moncoachscolaire/index.php
```
→ Détecté comme **LOCAL**, sécurité relâchée

### Option 2 : Virtual Host local
Ajouter dans `/etc/hosts` (Windows: `C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1 moncoachscolaire.local
```

Puis visiter:
```
http://moncoachscolaire.local/index.php
```
→ Détecté comme **LOCAL**, sécurité relâchée

### Option 3 : dev subdomain
```
http://dev.moncoachscolaire.com/index.php
```
→ Détecté comme **LOCAL** (si configuré sur dev), sécurité relâchée

## 📊 Vérification de l'environnement

### En CLI PHP

```php
<?php
// Vérifier si LOCAL
$host = $_SERVER['HTTP_HOST'] ?? 'cli';
$is_local = preg_match('/^(localhost|127\.0\.0\.1|.*\.local|dev\.|.*\.test)/', $host);
echo $is_local ? "LOCAL" : "PRODUCTION";
?>
```

### Via HTTP headers

```bash
# LOCAL
curl -I http://localhost/index.php
# Peut montrer d'autres infos (erreurs PHP, etc.)

# PRODUCTION
curl -I https://moncoachscolaire.fr/index.php
# Headers de sécurité présents, pas d'infos du serveur
```

## 🔧 Compatibilité

| Version Apache | Support | Notes |
|----------------|---------|-------|
| 2.2 | ✅ Complet | Syntaxe `Order/Deny` utilisée |
| 2.4 | ✅ Complet | Syntaxe `Require` moderne utilisée |
| 2.4.17+ | ✅ Optimal | Tous les modules disponibles |

Le `.htaccess` détecte automatiquement la version Apache et applique la syntaxe correcte.

## 📝 Fichiers supprimés

Les anciens fichiers `.htaccess` ont été consolidés en un seul :
- ❌ `.htaccess.improved` (Apache 2.4 uniquement)
- ❌ `.htaccessbak` (sauvegarde)
- ❌ `.htaccesscopy` (copie)
- ✅ `.htaccess` (hybride, nouveau)

## 🔔 Checklist déploiement PRODUCTION

Avant de mettre en production :

- [ ] Vérifier que `SetEnvIf Host` détecte correctement votre domaine
- [ ] Activer HTTPS (décommenter rewrite HTTPS)
- [ ] Tester l'accès aux fichiers sensibles (doit retourner 403)
- [ ] Vérifier que les APIs PHP fonctionnent (api/* n'est pas bloqué)
- [ ] Configurer les logs d'erreur PHP
- [ ] Tester les performances (compression GZIP, cache)

## 📞 Troubleshooting

### "500 Internal Server Error"

→ Vérifier la syntaxe Apache dans `.htaccess`
```bash
# Sur le serveur
apachectl configtest
```

### "403 Forbidden" sur les APIs

→ Vérifier que `/api/` n'est pas bloqué (il ne devrait pas l'être)
→ Les APIs gèrent leur propre authentification en PHP

### "Erreurs PHP affichées en production"

→ Vérifier que l'environnement est bien détecté comme PRODUCTION
→ Vérifier `php_flag display_errors Off` est appliqué
→ Redémarrer Apache après changement

### Compression GZIP non active

→ Vérifier que `mod_deflate` est activé sur le serveur
→ Tester avec: `curl -I http://localhost/index.php | grep Content-Encoding`

## 📚 Ressources

- [Apache .htaccess Documentation](https://httpd.apache.org/docs/current/howto/htaccess.html)
- [OWASP Security Best Practices](https://owasp.org/www-project-secure-headers/)
- [PHP Security](https://www.php.net/manual/en/security.php)
