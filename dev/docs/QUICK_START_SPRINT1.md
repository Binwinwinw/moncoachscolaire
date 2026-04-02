# 🚀 Quick Start - Continuer le Développement

## 📍 Situation Actuelle

✅ **Fait:**
- 910 exercices normalisés (sans accents)
- 8 tables gamification créées
- 84 notions seededées
- 10 badges prédéfinis
- API progression créée
- Roadmap stratégique documentée

⏳ **À Faire Maintenant:**
- Logging résultats exercices → XP
- Quiz diagnostics
- Dashboard progression avec graphiques
- Système d'émotions

---

## 🎯 Commencer le Sprint 1 (Logging XP)

### Étape 1: Logger les tentatives (fait)
- Table réutilisée : `exerciseresponses`
- Colonnes ajoutées : `TimeSpentSeconds`, `XpEarned`, `Source`, `Device`, `NotionId` (FK Notion.id), `Metadata`
- Index : `idx_user_exercise_time (UserId, ExerciseId, SubmittedAt)`
- Script de migration : `php tools/migrate_exerciseresponses.php`

### Étape 2: Implémenter calcul XP
```php
function calculateXP($score, $difficulty, $time_spent) {
    $base = 10;
    $difficulty_multiplier = [1=>0.5, 2=>1, 3=>1.5][$difficulty] ?? 1;
    $accuracy = $score / 100;
    
    // Bonus vitesse (plus rapide = bonus)
    $avg_time = 60; // secondes moyennes
    $speed_bonus = min(1.5, 1 + ($avg_time / max($time_spent, 5)));
    
    return (int)($base * $difficulty_multiplier * $accuracy * $speed_bonus);
}
```

### Étape 3: API pour logger résultats
- Endpoint créé : `POST /api/log_exercise_result.php`
- Payload : `exercise_id`, `score`, `correct`, `time_spent_seconds`, `source`, `device`, `notion_id`, `metadata`
- Actions : insert dans `exerciseresponses`, upsert `mastery`, upsert `userprogress` (XP total)

### Étape 4: Tester avec données fictives
```bash
php tools/test_xp_system.php
# Devrait créer 10 utilisateurs × 5 exercices = 50 attempts
# Afficher XP totaux et badges gagnés
```

---

## 📚 Quand Vous Êtes Prêt pour Sprint 2

### Créer quizzes diagnostics
```php
// Créer: src/pages/diagnostic.php
// Pour chaque notion: 3 questions (facile, moyen, difficile)

$quiz = [
    'niveau' => '1ere',
    'sujet' => 'Mathematiques',
    'notion' => 'Equations',
    'questions' => [
        ['level' => 'facile', 'text' => 'x + 5 = 10. Trouver x...'],
        ['level' => 'moyen', 'text' => '2x + 3 = 11. Trouver x...'],
        ['level' => 'difficile', 'text' => '(x+2)² = 25. Trouver x...']
    ]
];

// Résultat → recommandations apprentissage
if (score < 60) {
    echo "Tu as du mal. Consulte les explications détaillées...";
} elseif (score < 80) {
    echo "Bon, continue les exercices de cette notion...";
} else {
    echo "Excellent! Passe à la notion suivante...";
}
```

---

## 📊 Quand Prêt pour Sprint 3

### Dashboard Progression
```php
// Créer: src/pages/dashboard_progress.php

// Graphique XP timeline
$data = $pdo->query("
    SELECT DATE(created_at) as date, SUM(xp_earned) as daily_xp
    FROM Mastery WHERE user_id = ?
    GROUP BY DATE(created_at)
");

// Avec Chart.js:
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<canvas id="xpChart"></canvas>
<script>
    const ctx = document.getElementById('xpChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: { labels: [...], datasets: [{ data: [...] }] }
    });
</script>

// Heatmap notions (CSS Grid)
<div class="heatmap" style="display: grid; grid-template-columns: repeat(9, 1fr)">
    <?php foreach ($notions as $notion): ?>
        <div class="notion-cell" style="background-color: <?php echo getMasteryColor($notion) ?>">
            <?php echo $notion['name'] ?>
        </div>
    <?php endforeach; ?>
</div>

function getMasteryColor($notion) {
    // beginner: gris, apprentice: orange, proficient: bleu, expert: vert
    $colors = ['beginner'=>'#ccc', 'apprentice'=>'#ff9800', 'proficient'=>'#2196f3', 'expert'=>'#4caf50'];
    return $colors[$notion['mastery_level']] ?? '#ccc';
}
```

---

## 💾 Fichiers Clés à Consulter

| Fichier | Rôle |
|---------|------|
| `src/database/connection.php` | Connexion BD |
| `src/includes/exercice_loader.php` | Chargement exercices |
| `src/api/get_user_progress.php` | API progression |
| `ROADMAP_2026.md` | Plan détaillé |
| `SESSION_REPORT_2026-01-06.md` | Résumé session |

---

## 🛠️ Commandes Utiles

```bash
# Voir les notions
mysql -u root moncoachscolaire -e "SELECT * FROM Notion LIMIT 10;"

# Voir les exercises mappés
mysql -u root moncoachscolaire -e "
  SELECT COUNT(*) FROM ExerciseNotion;
"

# Ajouter notion manquellement
mysql -u root moncoachscolaire -e "
  INSERT INTO Notion (subject, level, name, difficulty)
  VALUES ('Mathematiques', '1ere', 'Systemes lineaires', 2);
"

# Mapper exercice (après insertion notion)
php tools/map_exercises_to_notions.php
```

---

## 📞 Questions Fréquentes

**Q: Comment tester le XP?**  
R: Créer API `log_exercise_result.php` qui insère dans `Mastery` et calcule XP

**Q: Les badges s'attribuent automatiquement?**  
R: Oui, après chaque `log_exercise_result` vérifier critères

**Q: Où ajouter les graphiques?**  
R: `src/pages/dashboard_progress.php` avec Chart.js CDN

**Q: Comment stocker les réponses quiz?**  
R: Table `QuizResult` a colonne JSON `answers`

---

## ✅ Checklist Sprint 1

- [x] Étendre `exerciseresponses` (temps, XP, source, device, notion, metadata)
- [ ] Fonction `calculateXP()` (ajuster formule si besoin de bonus vitesse/diff.)
- [x] API `log_exercise_result.php` (POST)
- [ ] Fonction `checkBadges()` (auto-award)
- [ ] Test avec 50+ attempts fictives
- [ ] Vérifier XP + badges correctes
- [ ] Merge → production

**Durée estimée: 2-3 jours**

---

**Fait par:** GitHub Copilot  
**Date:** 6 janvier 2026  
**Prêt à continuer?** OUI! 🚀
