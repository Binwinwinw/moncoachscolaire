# 🚀 Créer un Compte Admin - Guide Rapide

## Méthode 1 : Via SQL (phpMyAdmin) - Le Plus Rapide ⚡

1. **Ouvrir phpMyAdmin** : http://localhost/phpmyadmin (ou votre URL phpMyAdmin)

2. **Sélectionner la base** : `moncoachscolaire`

3. **Exécuter cette requête** :

```sql
-- Option A : Promouvoir un utilisateur existant en admin
UPDATE Users SET Role = 'admin' WHERE Username = 'votre_username';

-- Option B : Créer un nouvel admin (remplacer les valeurs)
INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel)
VALUES (
    'admin', 
    'admin@example.com', 
    '$2y$10$VotreHashIci',  -- Générer avec: php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);"
    'admin', 
    '6ème'
);
```

**Pour générer un hash de mot de passe** :
```bash
php -r "echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT);"
```

---

## Méthode 2 : Via le Script PHP 🛠️

```powershell
cd tools
php create_admin_user.php
```

Suivre les instructions à l'écran.

---

## Méthode 3 : Via le Dashboard Admin (si vous avez déjà un admin) 👨‍💼

1. Se connecter avec un compte admin existant
2. Aller dans "Gestion des Utilisateurs"
3. Cliquer sur "➕ Nouvel Utilisateur"
4. Remplir le formulaire avec `Role = admin`
5. Enregistrer

---

## ✅ Vérifier que ça fonctionne

1. Se connecter avec le compte admin
2. Aller sur : `https://moncoachscolaire.fr/index.php?page=dashboard_admin`
3. Vous devriez voir le Dashboard Administrateur ! 🎉

---

## 🔍 Vérifier les admins existants

```sql
SELECT Id, Username, Email, Role, CreatedAt 
FROM Users 
WHERE Role = 'admin';
```

---

*Guide rapide pour créer votre premier compte administrateur*
