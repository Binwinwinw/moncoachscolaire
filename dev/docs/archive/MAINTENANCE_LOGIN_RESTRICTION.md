# 🔧 Mode Maintenance - Restriction de Connexion

## 📋 Vue d'ensemble

Lorsque le **mode maintenance est activé**, les règles d'authentification suivantes s'appliquent:

- ✅ **Seuls les admins** peuvent se connecter
- ❌ **Les utilisateurs non-admin** (élèves, parents) reçoivent une erreur de connexion
- 🔐 **Les admins** peuvent se connecter normalement

## 🔄 Flux d'authentification en mode maintenance

### 1. Vérification du mode maintenance
Fichier: `login.php` (lignes ~100-106)

```php
$maintenanceFile = __DIR__ . '/.maintenance.json';
$maintenanceEnabled = false;

if (file_exists($maintenanceFile)) {
    $maintenanceData = json_decode(file_get_contents($maintenanceFile), true);
    if ($maintenanceData && isset($maintenanceData['enabled']) && $maintenanceData['enabled'] === true) {
        $maintenanceEnabled = true;
    }
}
```

### 2. Vérification après authentification
Fichier: `login.php` (lignes ~190-203)

Après que le mot de passe soit validé, le système vérifie:

```php
if ($maintenanceEnabled) {
    $isAdmin = ($userType === 'admin' || 
              ($userFound['Role'] ?? null) === 'admin' ||
              ($userFound['user_role'] ?? null) === 'admin');
    
    if (!$isAdmin) {
        // Rejeter la connexion si ce n'est pas un admin
        recordFailedAttempt($username);
        $error = "🔧 Maintenance en cours. Seuls les administrateurs peuvent se connecter.";
        error_log("LOGIN: Mode maintenance activé - Tentative de connexion d'un non-admin...");
    }
}
```

## 🎯 Comportement par utilisateur

| Type d'utilisateur | Mode Maintenance | Résultat |
|---|---|---|
| **Admin** | Activé ✅ | ✅ Connexion autorisée |
| **Élève** | Activé ✅ | ❌ Rejet avec message: "🔧 Maintenance en cours..." |
| **Parent** | Activé ✅ | ❌ Rejet avec message: "🔧 Maintenance en cours..." |
| **Admin** | Désactivé ❌ | ✅ Connexion autorisée |
| **Élève** | Désactivé ❌ | ✅ Connexion autorisée |
| **Parent** | Désactivé ❌ | ✅ Connexion autorisée |

## 🛠️ Gestion du mode maintenance

### Via API Admin
**Endpoint:** `POST /api/admin/maintenance.php`

**Activation:**
```bash
curl -X POST http://localhost/api/admin/maintenance.php \
  -H "Content-Type: application/json" \
  -d '{
    "action": "enable",
    "message": "Mise à jour en cours. Seuls les admins peuvent accéder."
  }'
```

**Désactivation:**
```bash
curl -X POST http://api/admin/maintenance.php \
  -H "Content-Type: application/json" \
  -d '{"action": "disable"}'
```

### Via Dashboard Admin
1. Connectez-vous en tant qu'admin
2. Allez à l'onglet **Maintenance**
3. Cliquez **Activer/Désactiver** le mode maintenance

## 📁 Fichiers impliqués

| Fichier | Rôle |
|---|---|
| `login.php` | 🔐 Vérification du rôle admin en maintenance |
| `api/admin/maintenance.php` | 🔧 Gestion du statut maintenance |
| `.maintenance.json` | 📄 Stockage du statut (généré automatiquement) |
| `index.php` | 🚀 Redirection des non-admins vers /maintenance |
| `maintenance.php` | 📺 Page d'affichage maintenance |

## 🔍 Fichier `.maintenance.json`

Structure du fichier généré:

```json
{
  "enabled": true,
  "message": "Le site est actuellement en maintenance. Nous serons de retour bientôt !",
  "activated_at": "2025-12-25 14:30:00",
  "activated_by": 1,
  "deactivated_at": null,
  "deactivated_by": null
}
```

## 📊 Logs

Toutes les tentatives de connexion en mode maintenance sont loggées:

```
LOGIN: Mode maintenance activé - Tentative de connexion d'un non-admin (username=student, role=student)
```

## ⚠️ Points importants

1. **Authentification toujours vérifiée**: Le mot de passe est d'abord validé, puis le rôle est vérifié
2. **Rate limiting maintenu**: Les tentatives échouées comptent toujours contre le rate limit
3. **Session non créée**: Si un non-admin est rejeté, aucune session n'est créée
4. **Message clair**: L'utilisateur reçoit un message explicite: "🔧 Maintenance en cours..."
5. **Admins toujours autorisés**: Aucune restriction ne s'applique aux administrateurs

## 🧪 Test

Pour tester le fonctionnement:

1. Créer un fichier `.maintenance.json` avec `"enabled": true`
2. Essayer de se connecter avec un compte élève → ❌ Rejet
3. Essayer de se connecter avec un compte admin → ✅ Connexion réussie
4. Supprimer le fichier ou mettre `"enabled": false`
5. Réessayer avec un compte élève → ✅ Connexion réussie
