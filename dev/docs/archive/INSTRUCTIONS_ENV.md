# Instructions pour configurer le fichier .env en production

## Problème identifié
Le fichier `.env` n'est pas chargé correctement sur le serveur, ce qui empêche la connexion à la base de données.

## Solution

### 1. Vérifier/corriger le fichier .env local

Le fichier `.env` doit contenir (à la racine du projet) :

```
DB_HOST=localhost
DB_USERNAME=u936396612_moncoachadmin
DB_PASSWORD=KmWm/?FREs/8
DB_DATABASE=u936396612_mcoachscolaire
APP_ENV=production
```

**⚠️ IMPORTANT :** Le `DB_DATABASE` doit être `u936396612_mcoachscolaire` (avec le préfixe Hostinger), PAS `moncoachscolaire`.

### 2. Uploader le fichier .env sur le serveur

1. Connectez-vous à votre serveur via FTP/SFTP
2. Naviguez vers la racine du projet (`public_html/moncoachscolaire/`)
3. **Uploader le fichier `.env`** à la racine (à côté de `index.php` et `config.php`)

### 3. Vérifier les permissions

Le fichier `.env` doit être lisible par PHP :
- Permissions recommandées : `644` ou `600` (lecture seule pour le propriétaire)

### 4. Tester la configuration

Après avoir uploadé le `.env`, testez avec :

1. **Test du chargement du .env :**
   - URL : `https://moncoachscolaire.fr/test_env.php`
   - Ce script vérifie si le `.env` est chargé correctement

2. **Test de l'inscription :**
   - URL : `https://moncoachscolaire.fr/test_register.php`
   - Ce script teste l'insertion dans la base de données

### 5. Sécurité

**⚠️ IMPORTANT :** Après les tests, supprimez les fichiers de test :
- `test_env.php`
- `test_register.php`

Ces fichiers ne doivent PAS rester sur le serveur en production.

## Modifications apportées

J'ai modifié `config.php` pour ajouter un **fallback** qui charge le `.env` manuellement même si Composer/phpdotenv n'est pas installé. Cela garantit que le fichier `.env` sera toujours chargé.

## Vérification

Si tout fonctionne correctement :
- `test_env.php` devrait afficher "✅ Toutes les variables requises sont chargées !"
- `test_register.php` devrait afficher "✅ Test d'inscription réussi !"
- L'inscription sur le site devrait fonctionner
