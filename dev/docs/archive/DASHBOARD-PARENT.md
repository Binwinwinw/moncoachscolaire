# Dashboard Parent - Documentation

## 🎯 Vue d'ensemble

Le Dashboard Parent permet aux parents de suivre la progression de tous leurs enfants inscrits sur la plateforme MonCoachScolaire. Chaque parent peut voir les statistiques de ses enfants et accéder à leurs progressions détaillées.

---

## 🔐 Authentification

### Accès Parent

Seuls les utilisateurs connectés en tant que parent (avec `$_SESSION['parent_id']` défini) peuvent accéder au dashboard parent.

**Vérification** :
- Vérifie la présence de `parent_id` dans la session
- Redirige vers `login_parents` si non connecté

### Connexion Parent

Les parents se connectent via `login_parents.php` qui vérifie leurs identifiants dans la table `parents`.

---

## 📁 Structure des Fichiers

```
moncoachscolaire/
├── dashboard_parent.php              # Page principale du dashboard parent
├── assets/
│   └── css/
│       └── pages/
│           └── dashboard-parent.css  # Styles du dashboard parent
└── docs/
    └── DASHBOARD-PARENT.md           # Cette documentation
```

---

## 🎨 Fonctionnalités

### Affichage des Enfants

Le dashboard affiche tous les enfants associés au parent connecté sous forme de cartes, avec :

- **Informations de base** :
  - Nom et prénom
  - Niveau scolaire
  - Avatar/icône

- **Statistiques** :
  - XP Total
  - Position actuelle dans le parcours

- **Dernière activité** :
  - Date et heure de la dernière mise à jour

### Actions Disponibles

Pour chaque enfant, deux boutons d'action :

1. **📊 Voir la Progression** :
   - Redirige vers `progression.php?user_id={enfant_id}`
   - Affiche la progression détaillée de l'enfant
   - Vérifie que l'enfant appartient bien au parent

2. **📈 Consulter les Résultats** :
   - Redirige vers `suivi_enfant.php?id={enfant_id}`
   - Affiche les résultats détaillés (quiz, exercices)

---

## 🗄️ Structure de la Base de Données

Le système supporte deux structures possibles :

### Structure 1 : Table `enfants` (recommandée)

```sql
CREATE TABLE enfants (
    id INT PRIMARY KEY,
    parent_id INT NOT NULL,
    user_id INT NOT NULL,  -- Lien vers Users.Id
    prenom VARCHAR(100),
    nom VARCHAR(100),
    FOREIGN KEY (parent_id) REFERENCES parents(id),
    FOREIGN KEY (user_id) REFERENCES Users(Id)
);
```

### Structure 2 : Table `Users` avec `ParentId`

```sql
ALTER TABLE Users ADD COLUMN ParentId INT NULL;
-- Les enfants ont ParentId = parent_id du parent
```

Le dashboard détecte automatiquement quelle structure est utilisée.

---

## 🔗 Intégration avec `progression.php`

La page `progression.php` a été modifiée pour accepter un paramètre `user_id` permettant aux parents de consulter la progression de leurs enfants :

```php
// URL : progression.php?user_id=123
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$_SESSION['user_id'];
```

**Sécurité** : Si `user_id` est fourni, le système vérifie que l'enfant appartient bien au parent connecté avant d'afficher les données.

---

## 🎨 Design

Le dashboard utilise un design moderne avec :
- **Couleurs** : Violet primaire (#8b5cf6) pour différencier du dashboard admin
- **Layout** : Grille responsive avec cartes pour chaque enfant
- **Responsive** : S'adapte aux écrans mobiles et tablettes
- **Animations** : Transitions fluides et animations subtiles

---

## 📱 Responsive

Le dashboard s'adapte automatiquement :
- **Desktop** : Grille multi-colonnes (3-4 enfants par ligne)
- **Tablette** : 2 enfants par ligne
- **Mobile** : 1 enfant par ligne, actions empilées verticalement

---

## 🔄 Cas d'Usage

### Parent avec un seul enfant

- Une seule carte affichée
- Accès direct aux statistiques et actions

### Parent avec plusieurs enfants

- Toutes les cartes affichées en grille
- Facile de comparer les progressions
- Navigation rapide entre les enfants

### Parent sans enfant

- Message informatif affiché
- Instructions pour contacter l'administrateur

---

## 🛡️ Sécurité

### Mesures de Sécurité

1. **Vérification de session** : Vérifie `parent_id` avant tout affichage
2. **Vérification parent-enfant** : Lors de l'accès à `progression.php?user_id=X`, vérifie que l'enfant appartient au parent
3. **Échappement HTML** : Tous les outputs sont échappés
4. **Validation des IDs** : Tous les IDs sont castés en `int` pour éviter les injections

---

## 🚀 Utilisation

### Accéder au Dashboard

1. Se connecter en tant que parent via `login_parents.php`
2. Aller sur : `https://moncoachscolaire.fr/index.php?page=dashboard_parent`
   OU directement : `https://moncoachscolaire.fr/dashboard_parent.php`

### Consulter la Progression d'un Enfant

1. Cliquer sur "📊 Voir la Progression" sur la carte de l'enfant
2. La page `progression.php` s'ouvre avec les données de l'enfant
3. Toutes les statistiques détaillées sont affichées

### Consulter les Résultats d'un Enfant

1. Cliquer sur "📈 Consulter les Résultats" sur la carte de l'enfant
2. La page `suivi_enfant.php` s'ouvre avec les résultats détaillés

---

## 📝 Notes Techniques

### Détection Automatique de Structure

Le dashboard essaie d'abord la structure avec table `enfants`, puis fallback sur `Users.ParentId` si nécessaire.

### Gestion des Erreurs

- Si aucun enfant n'est trouvé : Message informatif affiché
- Si erreur de base de données : Log dans `error_log`, message utilisateur générique
- Si structure inconnue : Log de l'erreur, tableau vide affiché

---

## ✅ Checklist de Déploiement

- [x] Dashboard parent créé
- [x] CSS moderne créé
- [x] Intégration avec progression.php
- [x] Vérification de sécurité parent-enfant
- [x] Support de deux structures de BDD
- [ ] Tester avec plusieurs enfants
- [ ] Vérifier les permissions d'accès
- [ ] Documenter la structure de BDD utilisée

---

*Dashboard créé pour permettre aux parents de suivre efficacement la progression de leurs enfants*
