# Documentation des Extensions du Dashboard

## Vue d'ensemble

Ce document décrit les nouvelles fonctionnalités ajoutées au dashboard de MonCoachScolaire :
- Graphiques de progression
- Objectifs quotidiens/hebdomadaires
- Streak de connexion
- Widget météo
- Notifications

## Installation

### 1. Base de données

Exécuter le fichier SQL pour créer les nouvelles tables :

```bash
mysql -u username -p moncoachscolaire < db/mysql_schema_extended.sql
```

Ou via phpMyAdmin : importer `db/mysql_schema_extended.sql`

### 2. Fichiers créés

- `db/mysql_schema_extended.sql` : Schéma SQL des nouvelles tables
- `includes/dashboard_extensions.php` : Fonctions PHP pour les nouvelles fonctionnalités
- `api/get_progress_chart.php` : API pour récupérer les données des graphiques
- `assets/css/pages/dashboard.css` : Styles CSS mis à jour

## Tables créées

### UserDailyGoals
Stoque les objectifs quotidiens des utilisateurs (exercices à compléter par jour).

### UserWeeklyGoals
Stoque les objectifs hebdomadaires des utilisateurs.

### UserStreak
Gère le streak de connexion (jours consécutifs).

### UserProgressHistory
Historique quotidien de la progression (XP, exercices, cristaux) pour les graphiques.

### UserNotifications
Système de notifications pour badges, niveaux, objectifs.

### UserLoginHistory
Historique des connexions pour calculer le streak.

## Fonctionnalités

### 1. Graphiques de progression

**API :** `/api/get_progress_chart.php?days=30`

Affiche un graphique linéaire avec :
- Évolution de l'XP
- Nombre d'exercices complétés
- Cristaux collectés

**Utilisation :**
Le graphique se charge automatiquement au chargement du dashboard. L'utilisateur peut changer la période (7, 30, 90 jours).

### 2. Objectifs Quotidiens/Hebdomadaires

**Objectifs par défaut :**
- Quotidien : 3 exercices/jour
- Hebdomadaire : 15 exercices/semaine

Les objectifs sont mis à jour automatiquement lorsque l'utilisateur complète des exercices.

**Fonctions PHP :**
- `getDailyGoals($userId, $date)` : Récupère les objectifs du jour
- `getWeeklyGoals($userId)` : Récupère les objectifs de la semaine
- `updateDailyGoalProgress($userId, $date)` : Met à jour la progression

### 3. Streak de Connexion

Le streak est calculé automatiquement à chaque connexion :
- Si connexion hier → streak continue
- Si pas de connexion hier → streak réinitialisé à 1
- Le plus long streak jamais atteint est conservé

**Fonctions PHP :**
- `updateLoginStreak($userId)` : Met à jour le streak (appelé automatiquement)
- `getUserStreak($userId)` : Récupère le streak actuel

### 4. Widget Météo

Le widget météo reflète la performance globale de l'élève :
- ☀️ Excellent : Streak ≥7 jours + objectifs atteints + ≥10 exercices
- 🌤️ Très bien : Streak ≥3 jours + progression bonne
- ⛅ Bon : Streak ≥1 jour + quelques exercices
- 🌧️ Peut mieux faire : Pas de streak récent

**Fonction PHP :**
- `getWeatherMood($userId)` : Retourne l'humeur météo

### 5. Notifications

Le système crée automatiquement des notifications pour :
- Nouveaux badges débloqués
- Objectifs quotidiens/hebdomadaires atteints
- Nouveaux niveaux atteints (à venir)

**Fonctions PHP :**
- `createNotification($userId, $type, $title, $message, $icon, $link)` : Crée une notification
- `getUnreadNotifications($userId, $limit)` : Récupère les notifications non lues
- `checkAndCreateNotifications($userId)` : Vérifie et crée les notifications automatiques

## Intégration dans le Dashboard

Le dashboard (`dashboard.php`) charge automatiquement :
1. `includes/dashboard_extensions.php`
2. Met à jour le streak à chaque chargement
3. Met à jour l'historique de progression
4. Vérifie et crée les notifications

## Utilisation

Toutes les fonctionnalités sont automatiques. Il n'y a pas de configuration nécessaire après l'installation du schéma SQL.

### Pour les développeurs

Pour ajouter une notification manuellement :
```php
createNotification(
    $userId,
    'badge', // Type : badge, level, goal, streak, info
    '🏆 Nouveau badge !',
    'Tu as débloqué le badge "Expert"',
    '🏆', // Icône
    null // Lien optionnel
);
```

## Notes importantes

- Les objectifs quotidiens sont créés automatiquement au premier chargement
- Le streak est mis à jour à chaque connexion (une seule fois par jour)
- L'historique de progression est mis à jour quotidiennement
- Les notifications sont vérifiées à chaque chargement du dashboard

## Améliorations futures

- [ ] Notifications push en temps réel
- [ ] Personnalisation des objectifs par l'utilisateur
- [ ] Graphiques comparatifs avec d'autres élèves (anonymisés)
- [ ] Export des données de progression
- [ ] Alertes email pour streak en danger

