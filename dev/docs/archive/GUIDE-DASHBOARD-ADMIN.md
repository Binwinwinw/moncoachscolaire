# Guide de Démarrage Rapide - Dashboard Administrateur

## 🚀 Installation Rapide

### Étape 1 : Créer un compte administrateur

**Option A : Via SQL (phpMyAdmin)**

1. Ouvrir phpMyAdmin : http://localhost/phpmyadmin
2. Sélectionner la base `moncoachscolaire`
3. Exécuter cette requête :

```sql
-- Promouvoir un utilisateur existant en admin
UPDATE Users SET Role = 'admin' WHERE Username = 'votre_username';

-- OU créer un nouvel admin
INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel)
VALUES ('admin', 'admin@example.com', '$2y$10$VotreHashIci', 'admin', '6ème');
```

**Option B : Via le script PHP**

```powershell
cd tools
php create_admin_user.php
```

Suivre les instructions à l'écran.

### Étape 2 : Créer la table AdminLogs

La table est créée automatiquement lors de la première utilisation, mais vous pouvez aussi l'exécuter manuellement :

```sql
-- Via phpMyAdmin ou MySQL CLI
SOURCE db/admin_setup.sql;
```

### Étape 3 : Accéder au Dashboard

1. Se connecter avec votre compte admin
2. Aller sur : `https://moncoachscolaire.fr/index.php?page=dashboard_admin`
   OU directement : `https://moncoachscolaire.fr/dashboard_admin.php`

---

## 📋 Fonctionnalités Principales

### Vue d'ensemble
- Statistiques en temps réel
- Activité récente
- État du système

### Gestion Utilisateurs
- Liste paginée
- Recherche et filtres
- CRUD complet

### Logs
- Actions admin
- Erreurs système
- Logs applicatifs

### Debug
- Info système
- Variables ENV
- État DB
- Session

### Configuration
- Mode debug
- Mode maintenance
- Notifications

---

## 🔧 Configuration

### Permissions Requises

Le compte admin doit avoir le rôle `admin` dans la table `Users`.

### URLs

- **Dashboard** : `/dashboard_admin.php` ou `/index.php?page=dashboard_admin`
- **API Users** : `/api/admin/users.php`
- **API Logs** : `/api/admin/logs.php`
- **API Stats** : `/api/admin/stats.php`
- **API Debug** : `/api/admin/debug.php`

---

## 🆘 Dépannage

### Problème : "Accès refusé"
**Solution** : Vérifier que votre compte a `Role = 'admin'` dans la table Users

### Problème : "Table AdminLogs n'existe pas"
**Solution** : La table est créée automatiquement. Sinon, exécuter `db/admin_setup.sql`

### Problème : Les APIs ne fonctionnent pas
**Solution** : Vérifier que le dossier `api/admin/` existe et est accessible

---

*Guide créé pour faciliter la prise en main du dashboard administrateur*
