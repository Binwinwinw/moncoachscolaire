# Checklist de validation — Exercices multi-parties

🎯 **Objectif**
Valider que les 55 exercices multi-parties s'affichent correctement et offrent une expérience utilisateur optimale.

---

## 📋 CHECKLIST COMPLÈTE

### ✅ Phase 1 : Validation technique backend (API/Base de données)

**Test 1 : Vérifier que les 55 exercices multi-parties existent**
```sql
SELECT COUNT(*) as total_multi_parties
FROM exercises 
WHERE structure_type = 'multi-parties';
-- Résultat attendu : 55
```

**Test 2 : Vérifier que sub_questions est un JSON valide**
```sql
SELECT 
  Id, 
  Identifier,
  JSON_VALID(sub_questions) as is_valid_json,
  JSON_LENGTH(sub_questions) as nb_questions
FROM exercises 
WHERE structure_type = 'multi-parties'
HAVING is_valid_json = 0;
-- Résultat attendu : 0 lignes (aucun JSON invalide)
```

**Test 3 : Vérifier la structure des sub_questions**
```sql
SELECT 
  Id,
  Identifier,
  JSON_EXTRACT(sub_questions, '$[0].id') as first_question_id,
  JSON_EXTRACT(sub_questions, '$[0].question') as first_question_text,
  JSON_EXTRACT(sub_questions, '$[0].type') as first_question_type
FROM exercises 
WHERE structure_type = 'multi-parties'
LIMIT 3;
-- Résultat attendu : données cohérentes (id, question, type présents)
```

**Test 4 : Vérifier les différents types de questions**
```sql
SELECT 
  JSON_EXTRACT(sub_questions, '$[*].type') as question_types,
  COUNT(*) as count
FROM exercises 
WHERE structure_type = 'multi-parties'
GROUP BY question_types;
-- Résultat attendu : qcm, texte, vrai_faux, association
```

- [ ] Tous les tests SQL passent
- [ ] 55 exercices multi-parties confirmés
- [ ] Tous les JSON sub_questions sont valides
- [ ] Structure cohérente (id, question, type, choices, answer)

**API - Endpoint de récupération**

**Test 5 : Récupérer un exercice multi-parties**
```bash
curl -X GET "http://localhost/api/exercises/268" -H "Content-Type: application/json"
```
Vérifications :
- [ ] Statut HTTP 200 OK
- [ ] structure_type = "multi-parties"
- [ ] Content contient le texte de support
- [ ] Instruction contient la consigne globale
- [ ] sub_questions est un tableau JSON
- [ ] Chaque question a : id, question, type, choices (si QCM), answer

---

### ✅ Phase 2 : Validation frontend (Interface utilisateur)

#### 2.1 Affichage de la liste des exercices
- [ ] Les exercices multi-parties apparaissent dans la liste
- [ ] Badge/icône indiquant "Exercice multi-parties" visible
- [ ] Titre de l'exercice correct
- [ ] Matière et niveau affichés correctement
- [ ] Nombre de questions affiché (ex: "14 questions")
- [ ] Difficulté affichée (facile/moyen/difficile)
- [ ] Points XP affichés

#### 2.2 Affichage de l'exercice complet
- [ ] Titre de l'exercice affiché
- [ ] Matière et niveau affichés
- [ ] Difficulté visible
- [ ] Points XP affichés
- [ ] Nombre total de questions affiché (ex: "Question 1/14")
- [ ] Content (support_text) affiché en haut
- [ ] Mise en forme correcte (paragraphes, retours à la ligne)
- [ ] Pas de balises HTML apparentes
- [ ] Texte lisible et bien espacé
- [ ] Instruction affichée clairement
- [ ] Distinguée visuellement du texte de support
- [ ] Police/couleur différente ou encadré

#### 2.3 Navigation entre les questions
- [ ] Bouton "Question suivante" présent
- [ ] Bouton "Question précédente" présent (si pas la 1ère)
- [ ] Navigation fonctionne sans perte de données
- [ ] Réponses précédentes conservées lors du retour
- [ ] Indicateur de progression affiché (ex: "3/14")
- [ ] Barre de progression visuelle (optionnel)
- [ ] Liste des numéros de questions cliquables (1, 2, 3...)
- [ ] Questions répondues marquées visuellement
- [ ] Question actuelle mise en évidence

#### 2.4 Validation et soumission
- [ ] Bouton visible uniquement à la dernière question
- [ ] Confirmation demandée avant soumission
- [ ] Vérification que toutes les questions sont répondues
- [ ] Message d'alerte si questions manquantes
- [ ] Réponses enregistrées en BDD
- [ ] Score calculé correctement
- [ ] Affichage du résultat global (ex: "12/14 bonnes réponses")
- [ ] Points XP attribués

#### 2.5 Affichage des corrections
- [ ] Texte de support réaffiché
- [ ] Toutes les questions affichées
- [ ] Réponse de l'élève affichée
- [ ] Correction affichée pour chaque question
- [ ] Indicateur visuel (✅ bonne réponse / ❌ mauvaise)
- [ ] Explication/Tips affichés si disponibles
- [ ] Score global visible

---

### ✅ Phase 3 : Tests de compatibilité et responsive

#### 3.1 Navigateurs
- [ ] Chrome (dernière version)
- [ ] Firefox (dernière version)
- [ ] Safari (dernière version)
- [ ] Edge (dernière version)
- [ ] Chrome Mobile (Android)
- [ ] Safari Mobile (iOS)

#### 3.2 Responsive design
- [ ] Desktop (≥ 1024px) : Mise en page adaptée grand écran, texte lisible, boutons bien espacés
- [ ] Tablette (768px - 1023px) : Layout adapté, navigation fluide, texte lisible
- [ ] Mobile (< 768px) : Layout en colonne unique, texte lisible sans zoom, boutons suffisamment grands, navigation tactile fluide, pas de débordement horizontal

---

### ✅ Phase 4 : Tests de performance
- [ ] Page exercice charge en < 2 secondes
- [ ] Navigation entre questions instantanée
- [ ] Pas de lag lors de la saisie
- [ ] Images optimisées (si présentes)
- [ ] Pas de requêtes API redondantes
- [ ] Cache navigateur utilisé

---

### ✅ Phase 5 : Tests de cas limites (Edge cases)
- [ ] Exercice avec 2 questions (minimum)
- [ ] Exercice avec 114 questions (maximum - Id 268)
- [ ] Questions sans choix (type texte)
- [ ] Questions avec beaucoup de texte
- [ ] Support text très long (> 5000 caractères)
- [ ] Navigation rapide entre questions
- [ ] Retour arrière multiple
- [ ] Rafraîchissement de page (données conservées ?)
- [ ] Fermeture/réouverture du navigateur
- [ ] Tentative de soumission sans répondre

---

### ✅ Phase 6 : Accessibilité (WCAG)
#### 6.1 Navigation clavier
- [ ] Navigation possible avec Tab
- [ ] Sélection possible avec Espace/Entrée
- [ ] Focus visible
- [ ] Ordre de tabulation logique
#### 6.2 Lecteurs d'écran
- [ ] Questions lisibles par lecteur d'écran
- [ ] Choix QCM lisibles
- [ ] Navigation annoncée
#### 6.3 Contraste et lisibilité
- [ ] Contraste texte/fond ≥ 4.5:1
- [ ] Police lisible (≥ 16px)
- [ ] Espacement suffisant

---

### ✅ Phase 7 : Tests utilisateurs réels
- [ ] Élève comprend comment naviguer
- [ ] Élève identifie le type de question
- [ ] Élève peut répondre sans confusion
- [ ] Élève comprend la correction
- [ ] Feedback utilisateur collecté
- [ ] Points de friction identifiés
- [ ] Améliorations UX listées
- [ ] Bugs rapportés corrigés

---

## 📊 Récapitulatif de validation
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   📋 CHECKLIST DE VALIDATION - EXERCICES MULTI-PARTIES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Phase 1 : Backend (API/BDD)           [ ] / 4 tests
Phase 2 : Frontend (Interface)        [ ] / 40+ points
Phase 3 : Compatibilité               [ ] / 6 navigateurs
Phase 4 : Performance                 [ ] / 4 tests
Phase 5 : Cas limites                 [ ] / 10 scénarios
Phase 6 : Accessibilité               [ ] / 9 critères
Phase 7 : Tests utilisateurs          [ ] / 5 feedbacks

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TAUX DE VALIDATION GLOBAL :           [ ] %
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

### 🎯 Priorités de tests
🔥 Priorité 1 (Critique) - À tester en premier
- [ ] Phase 1 : Validation backend
- [ ] Phase 2.2 : Affichage exercice complet
- [ ] Phase 2.3 : Navigation entre questions
- [ ] Phase 2.4 : Validation et soumission
⚠️ Priorité 2 (Important) - À tester ensuite
- [ ] Phase 2.5 : Affichage corrections
- [ ] Phase 3.2 : Responsive mobile
- [ ] Phase 5 : Cas limites
💡 Priorité 3 (Nice to have) - À tester si temps
- [ ] Phase 6 : Accessibilité
- [ ] Phase 7 : Tests utilisateurs
- [ ] Phase 4 : Performance

---

*Document généré automatiquement — à compléter lors des tests réels.*
