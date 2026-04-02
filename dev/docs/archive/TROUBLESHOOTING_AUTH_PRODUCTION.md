# 🔧 Dépannage Authentification en Production

## Problèmes courants et solutions

### 1. ❌ Les sessions ne fonctionnent pas

**Symptômes :**
- L'utilisateur se connecte mais est immédiatement déconnecté
- Les tokens CSRF ne sont pas acceptés
- Les données de session ne persistent pas

**Causes possibles :**
- Les cookies de session ne sont pas envoyés (problème HTTPS/Secure)
- Le chemin de sauvegarde des sessions n'est pas accessible en écriture
- Le domaine du cookie est incorrect

**Solutions :**

#### A. Vérifier la configuration HTTPS
Si votre site est en HTTPS (`https://moncoachscolaire.fr`), les cookies de session doivent être marqués comme "secure".

Le fichier `config.php` configure automatiquement cela, mais vérifiez que :
- Votre serveur détecte correctement HTTPS
- Les cookies sont bien envoyés avec le flag `Secure`

#### B. Vérifier les permissions du dossier de sessions
```bash
# Sur le serveur, vérifier les permissions
ls -la /tmp  # ou le chemin défini dans session.save_path
```

Le serveur web doit avoir les droits d'écriture dans ce dossier.

#### C. Tester avec le script de diagnostic
Uploader `test_auth_production.php` sur le serveur et accéder à :
```
https://moncoachscolaire.fr/test_auth_production.php
```

Ce script affichera :
- La configuration des sessions
- L'état de la connexion à la base de données
- Les variables d'environnement
- L'état des cookies

**⚠️ IMPORTANT : Supprimez ce fichier après diagnostic pour des raisons de sécurité !**

### 2. ❌ La base de données n'est pas accessible

**Symptômes :**
- Message "Base de données indisponible"
- Erreurs PDO lors de la connexion

**Solutions :**

#### A. Vérifier le fichier `.env`
Assurez-vous que le fichier `.env` contient les bonnes valeurs :
```env
DB_HOST=localhost
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
DB_DATABASE=moncoachscolaire
APP_ENV=production
```

#### B. Vérifier les variables d'environnement dans Hostinger
Dans le panneau Hostinger, vérifiez que les variables d'environnement sont bien définies :
- `DB_HOST`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_DATABASE`

#### C. Tester la connexion manuellement
Créez un fichier `test_db.php` :
```php
<?php
require_once __DIR__ . '/config.php';
if (isset($pdo) && $pdo) {
    echo "✅ Connexion OK";
    $stmt = $pdo->query("SELECT COUNT(*) FROM Users");
    echo " - Utilisateurs: " . $stmt->fetchColumn();
} else {
    echo "❌ Connexion échouée";
}
```

**⚠️ Supprimez ce fichier après test !**

### 3. ❌ Les redirections après connexion ne fonctionnent pas

**Symptômes :**
- Après connexion, l'utilisateur reste sur la page de login
- Erreur "Headers already sent"

**Solutions :**

#### A. Vérifier qu'aucun output n'est envoyé avant les headers
Assurez-vous qu'il n'y a pas d'espaces ou de caractères avant `<?php` dans :
- `config.php`
- `login.php`
- `index.php`
- Tous les fichiers inclus

#### B. Vérifier la fonction `site_url()`
La fonction `site_url()` doit retourner des URLs correctes :
```php
// Devrait retourner : /index.php?page=dashboard
echo site_url('dashboard');
```

### 4. ❌ Les tokens CSRF sont rejetés

**Symptômes :**
- Message "Erreur de sécurité" à chaque tentative de connexion
- Les tokens CSRF ne correspondent pas

**Causes :**
- Les sessions ne fonctionnent pas (voir point 1)
- Le token CSRF n'est pas généré correctement
- Le token CSRF expire trop rapidement

**Solutions :**

#### A. Vérifier que les sessions fonctionnent
Utilisez le script `test_auth_production.php` pour vérifier l'état des sessions.

#### B. Vérifier la génération du token
Dans `includes/login_security.php`, la fonction `generateCSRFToken()` doit :
- Créer un token unique à chaque chargement de page
- Le stocker dans `$_SESSION['csrf_token']`

### 5. ✅ Checklist de vérification

Avant de déployer en production, vérifiez :

- [ ] Le fichier `.env` est présent avec les bonnes valeurs
- [ ] Le fichier `.htaccess` protège le fichier `.env`
- [ ] Les permissions du fichier `.env` sont correctes (644 ou 600)
- [ ] La base de données est accessible depuis le serveur
- [ ] Les sessions fonctionnent (test avec `test_auth_production.php`)
- [ ] HTTPS est correctement configuré
- [ ] Les cookies de session sont envoyés avec le flag `Secure` en HTTPS
- [ ] Aucun output n'est envoyé avant les headers PHP
- [ ] La fonction `site_url()` retourne des URLs correctes

### 6. 🔍 Logs de diagnostic

Les logs PHP contiennent des informations utiles. Vérifiez les logs d'erreur PHP sur Hostinger :
- Panneau Hostinger → Logs → Error Logs

Recherchez les messages commençant par `LOGIN POST:` pour voir ce qui se passe lors des tentatives de connexion.

### 7. 📞 Support

Si le problème persiste après avoir suivi ce guide :
1. Exécutez `test_auth_production.php` et notez les résultats
2. Vérifiez les logs d'erreur PHP
3. Contactez le support Hostinger avec ces informations

---

**Dernière mise à jour :** 2024-12-02


