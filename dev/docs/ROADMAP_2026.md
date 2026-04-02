# 🎯 Roadmap Stratégique - MonCoachScolaire 2026

## 📊 État Actuel (Janvier 2026)

### ✅ Accomplissements
- **910 exercices normalisés** (6eme → Terminale)
- **Sujets nettoyés** (sans accents)
- **Pages d'exercices** opérationnelles par niveau
- **Structure BD stable** (Exercises table)
- **UI responsive** (topbar, cards, buttons)

### ⚠️ Manques Critiques
- ❌ Système de diagnostic initial (évaluation compétences/lacunes)
- ❌ Notion/Concepts par sujet (granularité d'apprentissage)
- ❌ Système XP/Badges (gamification)
- ❌ Tests évaluation post-exercice (maîtrise/progression)
- ❌ Dashboard progression détaillée (par niveau/sujet/notion)

---

## 🎮 Phase 1 : Gamification & Progression (Janvier-Février 2026)

### 1.1 Schema BD Gamification
**Fichier : `db/schema_gamification.sql`**

```sql
-- Table des niveaux de maîtrise
CREATE TABLE Mastery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exercise_id INT NOT NULL,
    user_id INT NOT NULL,
    level ENUM('beginner', 'apprentice', 'proficient', 'expert') DEFAULT 'beginner',
    xp_earned INT DEFAULT 0,
    attempts INT DEFAULT 0,
    best_score DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (exercise_id, user_id),
    FOREIGN KEY (exercise_id) REFERENCES Exercises(id),
    FOREIGN KEY (user_id) REFERENCES Users(id)
);

-- Table des badges/récompenses
CREATE TABLE Badge (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon_path VARCHAR(255),
    criteria JSON, -- {type: 'xp_total', value: 1000} ou {type: 'mastery_level', value: 'expert'}
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table d'attribution des badges
CREATE TABLE UserBadge (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, badge_id),
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (badge_id) REFERENCES Badge(id)
);

-- Table des notions (concepts à maîtriser)
CREATE TABLE Notion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject VARCHAR(100) NOT NULL, -- Mathematiques, Francais, etc.
    level VARCHAR(20) NOT NULL,    -- 6eme, 5eme, etc.
    name VARCHAR(100) NOT NULL,    -- Fractions, Équations, etc.
    description TEXT,
    difficulty INT DEFAULT 1, -- 1=facile, 2=moyen, 3=difficile
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (subject, level, name)
);

-- Liaison exercices ↔ notions
CREATE TABLE ExerciseNotion (
    exercise_id INT NOT NULL,
    notion_id INT NOT NULL,
    PRIMARY KEY (exercise_id, notion_id),
    FOREIGN KEY (exercise_id) REFERENCES Exercises(id),
    FOREIGN KEY (notion_id) REFERENCES Notion(id)
);
```

### 1.2 Seeding données Gamification
**Fichier : `tools/seed_gamification.php`**

Créer badges pré-définis :
- 🌟 "Débuter" : 1er exercice complété
- 🔥 "Série gagnante" : 5 exercices de suite avec 80%+
- 💎 "Expert" : Atteindre expert sur 10 exercices
- 🏆 "Champion" : 1000 XP totaux
- 📚 "Érudit" : Maîtriser tous les exercices d'une notion

### 1.3 API Progression
**Fichier : `src/api/get_user_progress.php`**

```
GET /api/get_user_progress.php?user_id=X&level=1ere&subject=Mathematiques

Response:
{
  "total_xp": 2450,
  "level_progress": {
    "current_level": "apprentice",
    "xp_to_next": 550,
    "exercises_mastered": 8,
    "total_exercises": 25
  },
  "badges_earned": [
    {id: 1, name: "Débuter", earned_at: "2026-01-15"},
    {id: 5, name: "Érudit", earned_at: "2026-01-20"}
  ],
  "notions_progress": [
    {notion: "Fractions", mastery: "proficient", exercises: 5, xp: 300},
    {notion: "Équations", mastery: "beginner", exercises: 2, xp: 100}
  ]
}
```

---

## 🔍 Phase 2 : Diagnostic Initial & Émotionnel (Février 2026)

### 2.1 Quiz de Diagnostic
**Fichier : `src/pages/diagnostic.php`**

Créer un mini-quiz (3-5 questions) par notion pour détecter les lacunes :
- Question **facile** (évaluer compréhension basique)
- Question **moyenne** (appliquer concept)
- Question **difficile** (analyser/synthétiser)

Résultats → Score 0-100 par notion → Recommandations

### 2.2 Analyse Émotionnelle
**Fichier : `src/pages/emotion_check.php`**

Après chaque exercice :
- "Comment tu te sens ?" → 😟 Frustré | 😐 Neutre | 😊 Confiant | 🤩 Super
- Stocker en table `ExerciseEmotions`
- Alerter si pattern négatif répété

### 2.3 Recommandations Intelligentes
**Fichier : `src/api/get_recommendations.php`**

Algorithme :
```
SI user_mastery < 'apprentice' POUR notion_X
  ALORS recommander: exercices faciles + explications détaillées
SINON SI user_emotion < neutral DEPUIS 3 jours
  ALORS recommander: break / review concept / changer matière
```

---

## 📋 Phase 3 : Tests & Évaluations (Mars 2026)

### 3.1 Schéma BD Tests
```sql
CREATE TABLE Quiz (
    id INT PRIMARY KEY AUTO_INCREMENT,
    level VARCHAR(20),
    subject VARCHAR(100),
    notion_id INT,
    type ENUM('diagnostic', 'formative', 'summative'),
    title VARCHAR(255),
    description TEXT,
    passing_score INT DEFAULT 70,
    created_at TIMESTAMP
);

CREATE TABLE QuizResult (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    quiz_id INT,
    score DECIMAL(5,2),
    time_spent_seconds INT,
    passed BOOLEAN,
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (quiz_id) REFERENCES Quiz(id)
);
```

### 3.2 Types de Tests
- **Diagnostic** : Avant d'apprendre une notion (évaluer niveau initial)
- **Formatif** : Pendant l'apprentissage (vérifier compréhension)
- **Sommatif** : Fin de notion ou chapitre (évaluer maîtrise)

---

## 📊 Phase 4 : Dashboard Progression Avancé (Mars 2026)

### 4.1 Visualisations
- **Graphique XP** : Progression timeline (courbe)
- **Heatmap Notions** : Matrice level×notion colorée par mastery
- **Badge Wall** : Galerie des achievements débloqués
- **Radar Chart** : Compétences par niveau (6 axes = 6 domaines)

### 4.2 Statistiques Détaillées
```
Mathématiques (1ere):
├─ Fractions
│  ├─ Exercices: 5/5 complétés
│  ├─ Mastery: Expert (100%)
│  ├─ XP: 350/350
│  └─ Tests: 2/2 réussis
├─ Équations
│  ├─ Exercices: 2/8 complétés
│  ├─ Mastery: Apprentice (40%)
│  └─ ⚠️ Diagnostic recommandé
```

---

## 🔧 Implémentation Technique

### Technologies
- **Backend** : PHP PDO pour requêtes complexes
- **Frontend** : Chart.js pour graphiques, D3.js optionnel
- **Algorithme** : Scoring basé sur temps + précision + tentatives
- **Cache** : Redis pour leaderboards (optionnel)

### Formule XP
```
XP = base_points × (1 + difficulty_multiplier) × accuracy_bonus × speed_bonus

Où:
- base_points = 10 (par défaut)
- difficulty_multiplier = {facile: 0.5, moyen: 1, difficile: 1.5}
- accuracy_bonus = score/100 (0-1)
- speed_bonus = 1 + (average_time_spent / actual_time_spent) capped à 1.5
```

### Paliers Mastery
| Level | Min XP | Conditions |
|-------|--------|-----------|
| Beginner | 0 | 1 exercice complété |
| Apprentice | 100 | 3+ exercices, score moyen ≥60% |
| Proficient | 300 | 5+ exercices, score moyen ≥75% |
| Expert | 500 | 8+ exercices, score moyen ≥90% + test réussi |

---

## 📝 Checklist Implémentation

### Sprint 1 (Semaine 1-2)
- [ ] Créer schema gamification + seed badges
- [ ] API `get_user_progress.php`
- [ ] Table Notion + mapping exercices
- [ ] Seeder notions par niveau/sujet

### Sprint 2 (Semaine 3-4)
- [ ] Page diagnostic.php
- [ ] Quiz de diagnostic (3 questions/notion)
- [ ] Calculation XP logic
- [ ] Table UserBadge + attribution auto

### Sprint 3 (Semaine 5-6)
- [ ] Emotion check après exercices
- [ ] API recommendations
- [ ] Tests formatifs/sommatifs
- [ ] Dashboard progress avancé

### Sprint 4 (Semaine 7-8)
- [ ] Graphiques Chart.js
- [ ] Heatmap notions
- [ ] Radar compétences
- [ ] Optimisation perf BD

---

## 🎯 KPIs Succès
- ✅ 100% des exercices mappés à notions
- ✅ Diagnostic < 3 min par utilisateur
- ✅ Progression visible graphiquement en temps réel
- ✅ 5+ badges débloqués par utilisateur mensuel
- ✅ Engagement utilisateur +40% (sessions/badges)

---

**Date** : 6 janvier 2026  
**Version** : 1.0 Roadmap  
**Propriétaire** : Équipe Tech MonCoachScolaire
