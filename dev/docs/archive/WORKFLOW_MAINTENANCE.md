# 🔧 Workflow de Maintenance - MonCoachScolaire

**Date de création** : 23 décembre 2025  
**Version** : 1.0

---

## 📋 Vue d'ensemble

Le système de maintenance permet aux administrateurs d'activer/désactiver un mode maintenance qui bloque l'accès public au site tout en permettant aux admins de continuer à travailler.

---

## 🏗️ Architecture

### Composants

```
Système de Maintenance
├── Dashboard Admin (dashboard_admin.php)
│   └── Section "Système" → Toggle Maintenance
│
├── API (api/admin/maintenance.php)
│   ├── GET : Récupère l'état
│   └── POST : Active/désactive
│
├── Stockage (.maintenance.json)
│   └── Fichier JSON à la racine
│
├── Vérification (index.php)
│   └── Check au début, avant tout chargement
│
├── Page Maintenance (maintenance.php)
│   └── Affichage pour les utilisateurs non-admin
│
└── JavaScript (admin-dashboard.js)
    └── Gestion du toggle
```

---

## 🔄 Workflow Complet

### 1. Activation depuis le Dashboard Admin

```
Admin ouvre dashboard_admin.php
  ↓
Onglet "Système" → Section "Maintenance"
  ↓
Clic sur toggle "Mode maintenance"
  ↓
JavaScript : toggleMaintenance(true)
  ↓
API POST /api/admin/maintenance.php
  ↓
Sauvegarde dans .maintenance.json
  ↓
Réponse JSON : { success: true, maintenance: {...} }
  ↓
Message de confirmation affiché
```

### 2. Vérification lors de l'accès

```
Utilisateur accède à index.php
  ↓
index.php vérifie .maintenance.json (LIGNE 1-52)
  ↓
Si maintenance activée :
  - Démarrer session
  - Charger config.php + admin_auth.php
  - Vérifier isAdmin()
  ↓
Si NON admin :
  - Rediriger vers ?page=maintenance
  - Afficher maintenance.php
  ↓
Si ADMIN :
  - Continuer normalement
  - Accès complet au site
```

### 3. Désactivation

```
Admin ouvre dashboard_admin.php
  ↓
Toggle "Mode maintenance" → OFF
  ↓
JavaScript : toggleMaintenance(false)
  ↓
API POST /api/admin/maintenance.php
  ↓
Sauvegarde : enabled = false
  ↓
Message de confirmation
  ↓
Site accessible à tous
```

---

## 📁 Fichiers Créés/Modifiés

### Nouveaux Fichiers

1. **`api/admin/maintenance.php`**
   - API REST pour gérer l'état
   - GET : Récupère l'état
   - POST : Active/désactive
   - Vérification admin obligatoire

2. **`maintenance.php`**
   - Page affichée aux utilisateurs non-admin
   - Design moderne avec gradient
   - Message personnalisable

3. **`assets/css/pages/maintenance.css`**
   - Styles pour la page de maintenance
   - Responsive
   - Animations fluides

4. **`.maintenance.json`** (généré automatiquement)
   - Stockage de l'état
   - Format JSON
   - Contient : enabled, message, activated_at, activated_by

### Fichiers Modifiés

1. **`index.php`**
   - Vérification maintenance au début (lignes 1-52)
   - Exception pour les admins
   - Redirection automatique

2. **`assets/js/admin-dashboard.js`**
   - Fonction `initConfig()` : Charge l'état
   - Fonction `loadMaintenanceStatus()` : GET API
   - Fonction `toggleMaintenance()` : POST API

3. **`dashboard_admin.php`**
   - Section "Maintenance" déjà présente (lignes 479-490)
   - Toggle HTML : `#maintenance-mode-toggle`

---

## 🔐 Sécurité

### Vérifications

1. **API** : Vérifie `isAdmin()` avant toute action
2. **Stockage** : Fichier `.maintenance.json` à la racine (peut être protégé par .htaccess)
3. **Exception Admin** : Les admins peuvent toujours accéder
4. **Session** : Vérification de session pour déterminer le rôle

### Protection Recommandée

Ajouter dans `.htaccess` :
```apache
<Files ".maintenance.json">
    Require all denied
</Files>
```

---

## 📊 Structure du Fichier .maintenance.json

```json
{
    "enabled": true,
    "message": "Le site est actuellement en maintenance. Nous serons de retour bientôt !",
    "activated_at": "2025-12-23 14:30:00",
    "activated_by": 1,
    "deactivated_at": null,
    "deactivated_by": null
}
```

### Champs

- **`enabled`** : `true` ou `false`
- **`message`** : Message personnalisé affiché aux utilisateurs
- **`activated_at`** : Date/heure d'activation (format Y-m-d H:i:s)
- **`activated_by`** : ID de l'admin qui a activé
- **`deactivated_at`** : Date/heure de désactivation
- **`deactivated_by`** : ID de l'admin qui a désactivé

---

## 🎨 Interface Utilisateur

### Dashboard Admin

**Section "Système" → "Maintenance"**
- Toggle checkbox
- Description : "Désactive l'accès public au site (admin uniquement)"
- État chargé automatiquement au chargement
- Feedback visuel lors du changement

### Page Maintenance

**Design** :
- Fond gradient (violet/bleu)
- Carte blanche centrée avec blur
- Icône animée (pulse)
- Message personnalisable
- Bouton "Retour à l'accueil"
- Responsive mobile

---

## 🔧 Utilisation

### Activer la Maintenance

1. Se connecter en tant qu'admin
2. Aller dans **Dashboard Admin** → Onglet **"Système"**
3. Section **"Maintenance"** → Cocher **"Mode maintenance"**
4. Confirmation : "✅ Mode maintenance activé avec succès"

### Désactiver la Maintenance

**Si vous êtes déjà connecté en tant qu'admin :**
1. Aller dans **Dashboard Admin** → Onglet **"Système"**
2. Section **"Maintenance"** → Décocher **"Mode maintenance"**
3. Confirmation : "✅ Mode maintenance désactivé avec succès"

**Si vous n'êtes pas connecté :**
1. Accéder à `?page=login` (accessible même en maintenance)
2. Se connecter avec vos identifiants admin
3. Une fois connecté, accéder au **Dashboard Admin** → Onglet **"Système"**
4. Section **"Maintenance"** → Décocher **"Mode maintenance"**
5. Confirmation : "✅ Mode maintenance désactivé avec succès"

### Personnaliser le Message

**Actuellement** : Le message est fixe dans l'API.  
**Amélioration future** : Ajouter un champ texte dans le dashboard pour personnaliser le message.

---

## 🧪 Tests

### Scénarios de Test

1. **Activation en tant qu'admin**
   - ✅ Toggle fonctionne
   - ✅ Message de confirmation
   - ✅ Fichier `.maintenance.json` créé

2. **Accès utilisateur normal**
   - ✅ Redirection vers page maintenance
   - ✅ Message affiché
   - ✅ Pas d'accès au site

3. **Accès admin pendant maintenance**
   - ✅ Pas de redirection
   - ✅ Accès complet au site
   - ✅ Peut désactiver la maintenance

4. **Désactivation**
   - ✅ Toggle fonctionne
   - ✅ Fichier mis à jour
   - ✅ Site accessible à tous

---

## ⚠️ Points d'Attention

### 1. Fichier .maintenance.json

- **Création automatique** : Si le dossier n'existe pas, il est créé
- **Permissions** : 0755 pour le dossier, fichier en écriture
- **Sécurité** : Protéger avec .htaccess (recommandé)

### 2. Vérification Admin

- **Session requise** : La session doit être démarrée
- **Fonction isAdmin()** : Doit être disponible
- **Cache** : Le rôle admin est mis en cache dans `$_SESSION['user_role']`

### 3. Performance

- **Vérification rapide** : Lecture fichier JSON (très rapide)
- **Pas de requête BDD** : Pas d'impact sur la base de données
- **Cache navigateur** : La page maintenance peut être mise en cache

---

## 🚀 Améliorations Futures

### Court Terme

- [ ] Message personnalisable depuis le dashboard
- [ ] Date de fin prévue (countdown)
- [ ] Email automatique aux admins lors de l'activation

### Moyen Terme

- [ ] Historique des activations/désactivations
- [ ] Planification automatique (cron)
- [ ] Page de maintenance avec thème personnalisable

### Long Terme

- [ ] Mode maintenance partiel (certaines pages seulement)
- [ ] Whitelist d'IPs autorisées
- [ ] Statistiques d'utilisation

---

## 📝 Notes Techniques

### Ordre de Vérification dans index.php

1. **Vérification maintenance** (lignes 1-52) - **PRIORITAIRE**
2. Nettoyage pageRaw
3. Vérification sécurité (fichiers bloqués)
4. Traitement POST login/register
5. Normalisation URLs
6. Routage normal

### Exception Admin

Les admins peuvent accéder à **toutes les pages** même en maintenance, y compris :
- Dashboard admin
- Dashboard utilisateur
- Pages d'exercices
- API endpoints

### Pages Accessibles en Maintenance

**Toujours accessibles** (pour permettre la connexion/déconnexion) :
- `?page=login` - Page de connexion (pour que les admins puissent se connecter)
- `?page=logout` - Page de déconnexion (pour que les utilisateurs puissent se déconnecter)
- `?page=logout_parents` - Page de déconnexion parents
- `?page=maintenance` - Page de maintenance elle-même

**Pages BLOQUÉES en maintenance** :
- `?page=register` - Page d'inscription (élèves et parents) - **BLOQUÉE** pour empêcher la création de comptes

**Important** : 
- La page `login` est accessible même en maintenance pour permettre aux admins de se connecter et désactiver la maintenance.
- La page `register` est **BLOQUÉE** en maintenance pour empêcher la création de nouveaux comptes (élèves et parents).
- La page `logout` est accessible pour permettre aux utilisateurs non-admin de se déconnecter même en maintenance.

### Logs

Les actions de maintenance sont loggées via `logAdminAction()` si disponible :
- "Maintenance activée"
- "Maintenance désactivée"

---

## ✅ Checklist de Vérification

- [x] API maintenance.php créée
- [x] Page maintenance.php créée
- [x] CSS maintenance.css créé
- [x] Vérification dans index.php
- [x] JavaScript pour toggle
- [x] Exception pour admins
- [x] Message de confirmation
- [x] Gestion erreurs
- [x] Responsive mobile
- [x] Documentation complète

---

**Document généré automatiquement**  
**Dernière mise à jour** : 23 décembre 2025
