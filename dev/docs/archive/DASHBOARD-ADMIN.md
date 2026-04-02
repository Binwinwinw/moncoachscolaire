# Dashboard Administrateur - Documentation

## 🎯 Vue d'ensemble

Le Dashboard Administrateur est une interface complète de gestion et monitoring pour les administrateurs de MonCoachScolaire. Il permet de gérer les utilisateurs, consulter les logs, déboguer le système et surveiller l'application en temps réel.

---

## 🔐 Authentification

### Accès Admin

Seuls les utilisateurs avec le rôle `admin` dans la table `Users` peuvent accéder au dashboard.

**Vérification** :
- Vérifie le champ `Role` dans la base de données
- Redirige vers le dashboard utilisateur si non-admin
- Redirige vers la page de login si non connecté

### Créer un compte Admin

**Via SQL** :
```sql
UPDATE Users SET Role = 'admin' WHERE Username = 'votre_username';
```

**Via le dashboard** (si vous êtes déjà admin) :
1. Aller dans "Gestion des Utilisateurs"
2. Cliquer sur "Modifier" pour un utilisateur
3. Changer le rôle en "Administrateur"

---

## 📁 Structure des Fichiers

```
moncoachscolaire/
├── dashboard_admin.php              # Page principale du dashboard
├── includes/
│   └── admin_auth.php              # Authentification et autorisation admin
├── api/
│   └── admin/
│       ├── users.php               # API gestion utilisateurs
│       ├── logs.php                # API gestion logs
│       ├── stats.php               # API statistiques temps réel
│       └── debug.php               # API outils de debug
├── assets/
│   ├── css/
│   │   └── pages/
│   │       └── dashboard-admin.css # Styles du dashboard admin
│   └── js/
│       └── admin-dashboard.js      # JavaScript pour interactions
└── docs/
    └── DASHBOARD-ADMIN.md          # Cette documentation
```

---

## 🎨 Sections du Dashboard

### 1. Vue d'ensemble

**Fonctionnalités** :
- Statistiques en temps réel (utilisateurs, XP, exercices, logs)
- Activité récente (dernières actions admin)
- État du système (DB, PHP, mémoire)

**Rafraîchissement** : Automatique toutes les 30 secondes

### 2. Gestion des Utilisateurs

**Fonctionnalités** :
- Liste paginée de tous les utilisateurs
- Recherche par nom d'utilisateur ou email
- Filtre par rôle (élève/admin)
- Création d'utilisateurs
- Modification d'utilisateurs (username, email, mot de passe, rôle, niveau)
- Suppression d'utilisateurs (avec confirmation)

**Actions disponibles** :
- ➕ Créer un nouvel utilisateur
- ✏️ Modifier un utilisateur existant
- 🗑️ Supprimer un utilisateur (sauf soi-même)

### 3. Logs Système

**Types de logs** :
- **Admin** : Actions administratives (création/modification/suppression utilisateurs)
- **Error** : Erreurs PHP et système
- **System** : Logs système généraux

**Fonctionnalités** :
- Filtrage par type
- Affichage chronologique (plus récent en premier)
- Informations détaillées (IP, user agent, timestamp)

### 4. Outils de Debug

**Outils disponibles** :
- **Info Système** : Version PHP, mémoire, configuration serveur
- **Variables ENV** : Variables d'environnement (masquées pour sécurité)
- **État DB** : Informations sur la connexion et les tables
- **Session** : État de la session PHP actuelle
- **Nettoyer Cache** : Réinitialise le cache OPcache

### 5. Configuration Système

**Options** :
- Mode Debug : Active/désactive l'affichage des erreurs PHP
- Mode Maintenance : Désactive l'accès public (admin uniquement)
- Notifications : Active/désactive les notifications temps réel

---

## 🔌 APIs Disponibles

### GET `/api/admin/users.php`

**Paramètres** :
- `page` : Numéro de page (défaut: 1)
- `limit` : Nombre d'éléments par page (défaut: 20, max: 100)
- `search` : Recherche par username/email
- `role` : Filtre par rôle (student/admin)

**Réponse** :
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "pages": 8
  }
}
```

### POST `/api/admin/users.php`

**Body** :
```json
{
  "username": "nouvel_utilisateur",
  "email": "user@example.com",
  "password": "motdepasse",
  "role": "student",
  "userLevel": "6ème"
}
```

### PUT `/api/admin/users.php`

**Body** :
```json
{
  "userId": 123,
  "username": "nouveau_nom",
  "email": "nouveau@example.com",
  "password": "nouveau_motdepasse",  // Optionnel
  "role": "admin",
  "userLevel": "Terminale"
}
```

### DELETE `/api/admin/users.php?id=123`

Supprime un utilisateur (CASCADE supprime aussi UserProgress, etc.)

### GET `/api/admin/stats.php`

Retourne les statistiques en temps réel

### GET `/api/admin/logs.php`

**Paramètres** :
- `type` : all|admin|error|system
- `page` : Numéro de page
- `limit` : Nombre de logs

### GET `/api/admin/debug.php?action=info`

**Actions** : `info`, `env`, `db`, `session`, `cache`

---

## 📊 Table AdminLogs

Le système crée automatiquement la table `AdminLogs` pour enregistrer toutes les actions administratives :

```sql
CREATE TABLE IF NOT EXISTS AdminLogs (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    AdminId INT NOT NULL,
    Action VARCHAR(100) NOT NULL,
    Details TEXT,
    TargetUserId INT NULL,
    IpAddress VARCHAR(45),
    UserAgent TEXT,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (AdminId) REFERENCES Users(Id) ON DELETE CASCADE
);
```

---

## 🎨 Design

Le dashboard utilise un design moderne avec :
- **Couleurs** : Bleu primaire (#2563eb), gris pour les éléments secondaires
- **Typographie** : Système de polices natives (-apple-system, Segoe UI, etc.)
- **Responsive** : S'adapte aux écrans mobiles et tablettes
- **Animations** : Transitions fluides et animations subtiles
- **Cartes** : Design en cartes avec ombres légères

---

## 🔄 Temps Réel

Le dashboard se rafraîchit automatiquement :
- **Vue d'ensemble** : Toutes les 30 secondes
- **Logs** : Toutes les 30 secondes (si section active)
- **Bouton manuel** : Disponible pour forcer le rafraîchissement

---

## 🛡️ Sécurité

### Mesures de Sécurité

1. **Vérification Admin** : Toutes les APIs vérifient `isAdmin()`
2. **Logs d'Actions** : Toutes les actions sont enregistrées dans `AdminLogs`
3. **Protection CSRF** : À implémenter si nécessaire
4. **Validation** : Validation côté serveur pour toutes les entrées
5. **Échappement HTML** : Tous les outputs sont échappés

### Bonnes Pratiques

- Ne jamais exposer les mots de passe dans les APIs
- Masquer les informations sensibles dans les logs
- Limiter les permissions aux admins uniquement
- Vérifier les permissions avant chaque action

---

## 🚀 Utilisation

### Accéder au Dashboard

1. Se connecter avec un compte admin
2. Aller sur : `https://moncoachscolaire.fr/dashboard_admin.php`
   OU via le router : `index.php?page=dashboard_admin`

### Créer un Utilisateur

1. Section "Gestion des Utilisateurs"
2. Cliquer sur "➕ Nouvel Utilisateur"
3. Remplir le formulaire
4. Cliquer sur "Enregistrer"

### Consulter les Logs

1. Section "Logs Système"
2. Choisir le type de log (Tous/Admin/Erreurs/Système)
3. Les logs s'affichent automatiquement

### Utiliser les Outils de Debug

1. Section "Debug"
2. Cliquer sur le bouton correspondant à l'information souhaitée
3. Les informations s'affichent dans la zone de texte

---

## 📝 Notes Techniques

### Base URL API

Le JavaScript détecte automatiquement le `baseUrl` depuis le chemin du script. Si vous êtes dans un sous-dossier (ex: `/moncoachscolaire`), l'API sera automatiquement ajustée.

### Gestion des Erreurs

Toutes les erreurs sont capturées et affichées via `showMessage()`. Les erreurs réseau sont également loggées dans la console.

### Performance

- Pagination pour les grandes listes
- Rafraîchissement sélectif (seulement la section active)
- Lazy loading pour les sections non actives

---

## ✅ Checklist de Déploiement

- [x] Système d'authentification admin créé
- [x] Dashboard PHP créé
- [x] APIs admin créées (users, logs, stats, debug)
- [x] CSS moderne créé
- [x] JavaScript pour interactions créé
- [x] Table AdminLogs créée automatiquement
- [ ] Créer un compte admin de test
- [ ] Tester toutes les fonctionnalités
- [ ] Vérifier la sécurité
- [ ] Documenter les procédures spécifiques

---

*Dashboard créé pour une gestion complète et professionnelle de l'application*
