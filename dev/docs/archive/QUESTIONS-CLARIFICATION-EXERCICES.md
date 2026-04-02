# Questions de Clarification - Intégrité des Exercices

> **Date** : 2025-01-XX  
> **Objectif** : Clarifier les exigences pour garantir l'intégrité pédagogique

---

## ❓ Questions

### 1. Problème d'Import ou d'Affichage ?

**Question** : Le problème vient-il de :
- **A)** L'import des exercices (un exercice de 5ème est mal classé comme "6ème" en base de données) ?
- **B)** L'affichage des exercices (un élève de 6ème voit des exercices d'autres niveaux) ?

**État actuel** :
- ✅ Le filtrage par niveau fonctionne correctement (testé)
- ✅ Les exercices en base ont les bons niveaux (vérifié)
- ⚠️ Besoin de confirmation sur le problème exact

---

### 2. Normalisation des Niveaux

**Question** : Comment doivent être stockés les niveaux en base de données ?

**Options** :
- **A)** Toujours avec accents : "6ème", "5ème", "4ème", "3ème", "Première"
- **B)** Toujours sans accents : "6eme", "5eme", "4eme", "3eme", "Premiere"
- **C)** Mixte (accepter les deux formats)

**État actuel** :
- Les exercices sont stockés avec accents : "6ème", "3ème", "Seconde", "Première"
- La fonction `getExercisesByLevel()` gère maintenant les variantes
- ⚠️ Besoin de confirmation sur le format standard

---

### 3. Vérification des Exercices Mal Classés

**Question** : Y a-t-il des exercices actuellement mal classés en base de données ?

**Exemples de problèmes possibles** :
- Un exercice de 5ème classé comme "6ème"
- Un exercice de 4ème classé comme "3ème"
- Un exercice du collège classé comme "Seconde"

**Action proposée** :
- Créer un script pour détecter les exercices potentiellement mal classés
- Analyser le contenu (titre, énoncé) pour détecter des incohérences

---

### 4. Sources de Données

**Question** : Les sources de données (`exercices/` et `docs/exercices-sources-par-niveau.md`) contiennent-elles des erreurs de niveau ?

**Action proposée** :
- Vérifier que tous les fichiers dans `exercices/college/6eme/` sont bien des exercices de 6ème
- Vérifier que tous les fichiers dans `exercices/college/5eme/` sont bien des exercices de 5ème
- Vérifier que le fichier `docs/exercices-sources-par-niveau.md` classe correctement les exercices

---

## ✅ Ce qui est Déjà Fait

1. ✅ **Filtrage strict** : `getExercisesByLevel()` ne retourne que les exercices du niveau demandé
2. ✅ **Normalisation** : La fonction gère les variantes (6ème/6eme)
3. ✅ **Vérification** : Scripts de test pour vérifier l'intégrité
4. ✅ **Import** : Le script d'import normalise les niveaux

---

## 🔧 Actions Proposées

### Action 1 : Vérifier les Exercices en Base
Créer un script pour détecter les exercices potentiellement mal classés en analysant :
- Le titre de l'exercice
- Le contenu (énoncé)
- Le chemin du fichier source

### Action 2 : Vérifier les Sources
Vérifier que tous les fichiers dans `exercices/` sont bien classés dans le bon dossier :
- `exercices/college/6eme/` → Exercices de 6ème uniquement
- `exercices/college/5eme/` → Exercices de 5ème uniquement
- etc.

### Action 3 : Améliorer la Détection lors de l'Import
Améliorer le script d'import pour :
- Détecter les incohérences entre le dossier et le niveau déclaré
- Afficher des avertissements si un exercice semble mal classé
- Demander confirmation avant d'importer un exercice suspect

---

**En attente de vos réponses pour procéder aux corrections nécessaires.**

