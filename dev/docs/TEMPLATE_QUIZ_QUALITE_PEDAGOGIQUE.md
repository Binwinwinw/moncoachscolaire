# Template Quiz de Qualité Pédagogique — MonCoachScolaire

**Date création** : 07/03/2026  
**Objectif** : Définir les standards de qualité pour les quiz diagnostics

---

## 📐 Principes fondamentaux

### 1. Formulation des questions

✅ **FAIRE** :
- Phrases complètes et grammaticalement correctes
- Vocabulaire adapté au niveau scolaire
- Questions contextualisées (ex: "Dans un triangle rectangle...")
- Consignes claires et sans ambiguïté

❌ **NE PAS FAIRE** :
- Abréviations télégraphiques ("Équation premier degré ax+b=0, solution...?")
- Symboles mathématiques en texte ("a²+b²=c²" → écrire "a au carré plus b au carré")
- Questions trop vagues ("Baudelaire genre = ...?")
- Formulations raccourcies ("Lit ≈ catharsie...?")

### 2. Corrections pédagogiques

✅ **FAIRE** :
- 2-4 phrases d'explication minimum
- Rappel du concept clé
- Exemple ou contexte d'application
- Ton bienveillant et encourageant

❌ **NE PAS FAIRE** :
- Corrections d'un mot ("Isoler variable.")
- Réponse sans explication ("Triangle rectangle.")
- Formulations négatives ou décourageantes

### 3. Choix de réponses (QCM)

✅ **FAIRE** :
- Distracteurs plausibles (erreurs courantes d'élèves)
- Homogénéité des propositions (longueur, structure)
- 4 choix équilibrés

❌ **NE PAS FAIRE** :
- Réponses évidentes par déduction
- Propositions absurdes ou hors sujet

---

## 🎓 Référentiels par niveau (programmes Education Nationale)

### 6ème — Consolidation fondamentaux
**Français** : Orthographe (homophones ces/ses, a/à), conjugaison présent/imparfait/futur, nature/fonction mots
**Mathématiques** : Opérations 4, fractions simples, géométrie plane (périmètres aires), proportionnalité
**Histoire-Géo** : Repères chronologiques Antiquité, continents/océans, Orient ancien

### 5ème — Approfondissement
**Français** : Conjugaison temps composés, propositions subordonnées, figures de style (métaphore, comparaison)
**Mathématiques** : Calcul littéral simple (2x+3), triangle égalité Pythagore, statistiques (moyenne, médiane)
**SVT** : Respiration/circulation, géologie (plaques tectoniques)

### 4ème — Complexification
**Français** : Voix active/passive, discours direct/indirect, registres littéraires
**Mathématiques** : Équations 1er degré (ax+b=0), Pythagore/Thalès, puissances, calcul littéral (développement, factorisation)
**Physique-Chimie** : Électricité (loi d'Ohm), optique (réflexion/réfraction), réactions chimiques

### 3ème — Préparation Brevet
**Français** : Analyse littéraire (narrateur, focalisation), argumentation, figures de style élaborées
**Mathématiques** : Systèmes équations, fonctions affines/linéaires, trigonométrie, probabilités
**Histoire-Géo** : Guerres mondiales, géopolitique contemporaine, développement durable

### Seconde — Entrée Lycée
**Français** : Mouvements littéraires (romantisme, réalisme), genres (poésie épique, tragédie)
**Mathématiques** : Fonctions (domaine, image), second degré (forme canonique, discriminant), vecteurs
**Physique-Chimie** : Mécanique (vitesse, accélération), quantité de matière (mole), tableau périodique

### Première — Spécialisation
**Français** : Littérature argumentative (essai, pamphlet), registres (lyrique, pathétique, satirique)
**Mathématiques Spé** : Dérivées, suites numériques, probabilités conditionnelles
**Histoire-Géo** : Totalitarismes XXe, mondialisation, conflits géopolitiques actuels

### Terminale — Préparation Supérieur
**Français/Philo** : Courants philosophiques (existentialisme, absurde), analyse conceptuelle
**Mathématiques Spé** : Intégrales, logarithmes/exponentielles, lois de probabilités continues
**SES** : Croissance économique, politique monétaire, marchés

### BAC — Révisions finales
**Toutes matières** : Synthèse programme, méthodologie épreuve (dissertation, commentaire, exercices types)

---

## 📝 Structure JSON standard

```json
{
  "contents": {
    "title": "Quiz Diagnostic [NIVEAU] [MATIÈRE] - Serie [N]",
    "type": "quiz",
    "level": "6eme|5eme|4eme|3eme|seconde|1ere|terminale|bac",
    "subject": "Mathématiques|Français|Histoire-Géographie|SVT|Physique-Chimie|Anglais|Espagnol",
    "description": "Description pédagogique claire (thèmes abordés)",
    "status": "published",
    "created_at": "2026-03-07 18:00:00",
    "updated_at": "2026-03-07 18:00:00"
  },
  "quiz": {
    "title": "Diagnostic [MATIÈRE] [NIVEAU] - Serie [N]",
    "level": "...",
    "subject": "...",
    "question_count": 8,
    "passing_score": 70,
    "time_limit_minutes": 20,
    "questions": [...]
  },
  "exercisenotion": [
    {
      "notion": "Nom du concept",
      "description": "Explication courte du concept testé"
    }
  ],
  "exerciseresponses": []
}
```

---

## ✨ Exemples concrets AVANT/APRÈS

### Exemple 1 : Mathématiques 4ème

#### ❌ AVANT (télégraphique)
```json
{
  "id": 1,
  "type": "qcm",
  "question": "Équation premier degré ax+b=0, solution...?",
  "choices": ["pas solution", "x = -b/a", "x = a/b", "infini"]
}
```
**Correction** : `"Isoler variable."`

#### ✅ APRÈS (pédagogique)
```json
{
  "id": 1,
  "type": "qcm",
  "question": "Pour résoudre une équation du premier degré de la forme ax + b = 0 (avec a ≠ 0), quelle est la solution ?",
  "choices": [
    "L'équation n'a pas de solution",
    "x = -b/a",
    "x = a/b",
    "L'équation a une infinité de solutions"
  ]
}
```
**Correction** : `"La bonne réponse est x = -b/a. Pour résoudre l'équation ax + b = 0, on isole x en soustrayant b des deux côtés (ax = -b), puis en divisant par a (x = -b/a). Par exemple, si 3x + 6 = 0, alors x = -6/3 = -2."`

---

### Exemple 2 : Français Terminale

#### ❌ AVANT (incompréhensible)
```json
{
  "id": 1,
  "type": "qcm",
  "question": "Baudelaire genre = ...?",
  "choices": ["romantisme pur", "décadentisme", "modernisme", "réalisme"]
}
```
**Correction** : `"Décadent."`

#### ✅ APRÈS (contextualisé)
```json
{
  "id": 1,
  "type": "qcm",
  "question": "À quel courant littéraire Charles Baudelaire est-il principalement associé, notamment à travers son œuvre 'Les Fleurs du mal' ?",
  "choices": [
    "Le romantisme classique",
    "Le décadentisme et le symbolisme précurseur",
    "Le modernisme du XXe siècle",
    "Le réalisme social"
  ]
}
```
**Correction** : `"Baudelaire est considéré comme un précurseur du symbolisme et proche du décadentisme. Son œuvre 'Les Fleurs du mal' (1857) rompt avec le romantisme traditionnel par ses thèmes provocateurs (le mal, la modernité urbaine, la beauté du morbide) et inaugure une poésie moderne qui influencera Rimbaud et Verlaine."`

---

### Exemple 3 : Histoire-Géo 1ère

#### ❌ AVANT (trop vague)
```json
{
  "id": 1,
  "type": "texte",
  "question": "Ressource Moyen-Orient = ...?"
}
```
**Correction** : `"Pétrole."`

#### ✅ APRÈS (précis)
```json
{
  "id": 1,
  "type": "texte",
  "question": "Quelle ressource naturelle est au cœur des enjeux géopolitiques et économiques du Moyen-Orient depuis le XXe siècle ?",
  "placeholder": "Tapez le nom de la ressource"
}
```
**Correction** : `"Le pétrole est la ressource centrale du Moyen-Orient. Cette région détient environ 48% des réserves mondiales prouvées, ce qui en fait un acteur stratégique majeur de l'économie mondiale. Le contrôle du pétrole a motivé de nombreux conflits (guerres du Golfe, tensions Iran-Arabie Saoudite) et conditionne les alliances géopolitiques internationales."`

---

### Exemple 4 : Mathématiques 6ème

#### ❌ AVANT (trop basique)
```json
{
  "id": 3,
  "type": "qcm",
  "question": "Lequel est correct ?",
  "choices": ["a quatre stylo", "a un stylo", "a deux stylo", "a trois stylos"]
}
```
**Correction** : `"Accord pluriel."`

#### ✅ APRÈS (pédagogique niveau 6ème)
```json
{
  "id": 3,
  "type": "qcm",
  "question": "Quelle phrase respecte correctement l'accord du nom commun avec le déterminant numéral ?",
  "choices": [
    "Elle a quatre stylo dans sa trousse",
    "Elle a un stylo dans sa trousse",
    "Elle a deux stylo dans sa trousse",
    "Elle a trois stylos dans sa trousse"
  ]
}
```
**Correction** : `"Les deux bonnes réponses sont : 'Elle a un stylo' (singulier) et 'Elle a trois stylos' (pluriel). Règle : après un déterminant numéral pluriel (deux, trois, quatre...), le nom prend toujours un 's' au pluriel. Seul 'un' est au singulier. Exemples : un chat, deux chats, trois livres."`

---

## 🔧 Checklist validation quiz

Avant de publier un quiz, vérifier :

- [ ] **Questions** : Phrases complètes, vocabulaire adapté, pas d'abréviations
- [ ] **Corrections** : Minimum 2 phrases, explication du concept, exemple si possible
- [ ] **Niveau** : Cohérence avec référentiel EN du niveau ciblé
- [ ] **Choix QCM** : 4 options équilibrées, distracteurs plausibles
- [ ] **Notions** : `exercisenotion` renseigné avec descriptions
- [ ] **Métadonnées** : Title, description, level, subject corrects
- [ ] **Ton** : Bienveillant et encourageant (jamais négatif)
- [ ] **Orthographe** : Relecture complète (fautes = crédibilité perdue)

---

## 🎯 Prochaines étapes

1. Appliquer ce template aux quiz existants (réécriture progressive)
2. Créer script de validation automatique (détection questions télégraphiques, corrections trop courtes)
3. Former banque de questions validées par niveau/matière
4. Intégrer validation dans workflow génération quiz

---

**Maintenu par** : Copilot AI  
**Dernière mise à jour** : 07/03/2026
