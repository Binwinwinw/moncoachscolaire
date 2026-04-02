# 🎯 Guide d'Accès aux Cours et Exercices via le Routeur

## 📚 Accès aux Cours

### Nouvelle URL (Recommandée)
```
http://localhost/moncoachscolaire/index.php?page=view_course&id=16
```

### Ancienne URL (Compatible - Redirige automatiquement)
```
http://localhost/moncoachscolaire/view_course.php?id=16
```

Les anciennes URLs fonctionnent toujours mais redirigent vers le routeur.

---

## ✏️ Accès aux Exercices

### Nouvelle URL (Recommandée)
```
http://localhost/moncoachscolaire/index.php?page=view_exercise&id=42
```

### Ancienne URL (Compatible - Redirige automatiquement)
```
http://localhost/moncoachscolaire/view_exercise.php?id=42
```

---

## 🔗 Génération de URLs Sécurisées

Utilisez toujours la fonction `site_url()` en PHP:

### Pour un Cours
```php
<?php
// Dans vos fichiers PHP
$courseId = 16;
$url = site_url('view_course&id=' . $courseId);
// Résultat: index.php?page=view_course&id=16
echo '<a href="' . $url . '">Voir le cours</a>';
?>
```

### Pour un Exercice
```php
<?php
// Dans vos fichiers PHP
$exerciseId = 42;
$url = site_url('view_exercise&id=' . $exerciseId);
// Résultat: index.php?page=view_exercise&id=42
echo '<a href="' . $url . '">Voir l\'exercice</a>';
?>
```

### En JavaScript
```javascript
// Nouvelle URL formatée
const courseUrl = `index.php?page=view_course&id=${course.Id}`;
const exerciseUrl = `index.php?page=view_exercise&id=${exercise.Id}`;

// Redirection
window.location.href = courseUrl;
```

---

## 🏗️ Architecture des Contrôleurs

### Structure d'un Contrôleur de Page

Le routeur `index.php` inclut automatiquement les fichiers trouvés dans ce ordre:

```
1. /{page}                    (dossier)
2. /{page}/index.php
3. /{page}.php
4. /{page}.html
5. /pages/{page}/index.php
6. /pages/{page}.php          ← ✅ Nos contrôleurs vont ici
7. /pages/{page}.html
```

### Contenu du Contrôleur

```php
<?php
// ❌ NE PAS inclure de <!DOCTYPE html>
// ❌ NE PAS inclure de <head>
// ❌ NE PAS inclure topbar/footer (le routeur le fait)

// ✅ Charger les dépendances
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/course_markdown_loader.php';

// ✅ Récupérer les paramètres
$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ✅ Rediriger si paramètres invalides
if ($courseId <= 0) {
    header('Location: ' . site_url('cours'));
    exit;
}

// ✅ Charger les données
$course = getCourseById($courseId);

// ✅ Afficher le contenu
?>
<main class="main-content">
    <!-- Votre HTML ici -->
</main>
```

---

## 🔐 Sécurité et Avantages

### Vérifications Centralisées (via le Routeur)

Le routeur effectue automatiquement:
- ✅ Vérification du mode maintenance
- ✅ Normalisation des URLs (accents)
- ✅ Redirection des doublons
- ✅ Gestion cohérente des erreurs 404
- ✅ Inclusion de topbar/footer

### Avantages de cette Architecture

1. **Single Entry Point** - Un seul fichier d'entrée (index.php)
2. **Cohérence** - Tous les chemins incluent header/footer de la même façon
3. **Maintenabilité** - Changements globaux sans dupliquer le code
4. **Sécurité** - Les vérifications s'appliquent à toutes les pages
5. **SEO** - URLs cohérentes et normalisées

---

## 🧪 Vérification du Fonctionnement

### Script de Test
```bash
php tests/test_router_integration_courses.php
```

Résultat attendu:
```
🎉 TOUS LES TESTS SONT PASSÉS!
L'intégration du routeur est complète et fonctionnelle.
```

---

## 📝 Fichiers Impliqués

| Fichier | Rôle |
|---------|------|
| `index.php` | Routeur central |
| `pages/view_course.php` | Contrôleur d'affichage de cours |
| `pages/view_exercise.php` | Contrôleur d'affichage d'exercice |
| `view_course.php` | Redirection → routeur |
| `view_exercise.php` | Redirection → routeur |
| `cours.php` | Utilise nouvelles URLs |
| `assets/js/dynamic-exercises.js` | Utilise nouvelles URLs |

---

## 🐛 Dépannage

### Le cours/exercice ne s'affiche pas?

1. Vérifier que l'ID est valide dans la BD:
   ```sql
   SELECT * FROM courses WHERE Id = 16;
   SELECT * FROM exercises WHERE Id = 42;
   ```

2. Vérifier l'URL:
   ```
   ✅ index.php?page=view_course&id=16
   ❌ index.php?page=view_course.php&id=16
   ❌ view_course.php?id=16
   ```

3. Vérifier les logs du serveur:
   ```bash
   tail -f /var/log/php-fpm.log
   tail -f /var/log/apache2/error.log
   ```

### La page affiche 404?

1. Vérifier que `pages/view_course.php` existe:
   ```bash
   ls -la pages/view_course.php
   ```

2. Vérifier les permissions du fichier:
   ```bash
   chmod 644 pages/view_course.php
   ```

---

## 🚀 Prochaines Améliorations

- [ ] Ajouter des tests d'intégration E2E
- [ ] Implémenter un système de cache pour les cours
- [ ] Ajouter des breadcrumbs de navigation
- [ ] Implémenter un système de favoris pour les utilisateurs
