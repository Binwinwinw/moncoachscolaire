---
name: quiz-generator
description: Crée ou corrige un script Python generate_<matiere>_<niveau>.py pour MonCoachScolaire. Utiliser pour générer de nouveaux quiz diagnostiques, corriger un schéma JSON non conforme, ou normaliser un script legacy. Produit quiz/<id>.json (sans réponses) et quiz_answers/<id>.json (avec corrections). Checklist 7 points obligatoires intégrée.
argument-hint: [matiere] [niveau] [IDs min-max]
---

# Skill — Quiz Generator Python

## Objectif

Créer ou corriger un script `generate_<matiere>_<niveau>.py` conforme au pattern standard MonCoachScolaire.

## Localisation

```
dev/tools/quiz/
├── generate_svt_3eme.py     ← Référence IDs 651–698
├── generate_pc_3eme.py      ← Référence IDs 699–746
├── generate_svt_1ere.py     ← IDs 603–650
├── generate_hg_3eme.py      ← IDs 555–602
├── generate_francais_1ere.py ← IDs 466–500
├── generate_hg_1ere.py      ← IDs 175–223
├── generate_pc_1ere.py      ← IDs 273–321
└── generate_philo_1ere.py   ← IDs 224–272
```

**Sortie de chaque script :**

```
dev/tools/quiz/<matiere>_<niveau>_quizzes/
├── quiz/<id>.json          ← Questions SANS réponses (côté élève)
└── quiz_answers/<id>.json  ← Réponses + explications (côté API)
```

## Checklist 7 points obligatoires

Avant toute génération ou correction, vérifier chaque point :

### 1. Import datetime moderne

```python
from datetime import UTC, datetime
```

**Interdit :** `datetime.utcnow()`, `import datetime` seul.

### 2. Constantes de chemin SCRIPT_DIR-relatifs

```python
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
<MATIERE>_OUTPUT_DIR  = os.path.join(SCRIPT_DIR, "<matiere>_<niveau>_quizzes")
<MATIERE>_QUIZ_DIR    = os.path.join(<MATIERE>_OUTPUT_DIR, "quiz")
<MATIERE>_ANSWERS_DIR = os.path.join(<MATIERE>_OUTPUT_DIR, "quiz_answers")
```

**Interdit :** chemins en dur `"quizzes"` ou `"answers"`.

### 3. make_quiz — striper les champs de correction

```python
def make_quiz(qid, title, subject, level, questions):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    return {
        "id": qid,
        "title": title,
        "subject": subject,
        "level": level,
        "created_at": datetime.now(UTC).isoformat().replace("+00:00", "Z"),
        "questions": [
            {k: v for k, v in q.items() if k not in answer_keys}
            for q in questions
        ]
    }
```

### 4. make_answers — dispatch complet par type

```python
def make_answers(qid, title, subject, level, questions):
    answers = []
    for q in questions:
        if q["type"] == "qcm":
            answers.append({
                "question_id": q["id"],
                "correct_option": q["correct_option"],
                "explanation": q["explanation"]
            })
        elif q["type"] == "vrai-faux":
            answers.append({
                "question_id": q["id"],
                "correct": q["correct"],        # bool
                "explanation": q["explanation"]
            })
        else:  # texte
            answers.append({
                "question_id": q["id"],
                "correct_answer": q["correct_answer"],
                "explanation": q["explanation"]
            })
    return {"quiz_id": qid, "title": title, "subject": subject,
            "level": level, "answers": answers}
```

### 5. Type `"vrai-faux"` avec tiret

**Jamais** `"vrai_faux"` (underscore). Vérifier avec :

```powershell
Select-String -Path "generate_*.py" -Pattern "vrai_faux"
```

Zéro résultat attendu.

### 6. Complétion des IDs jusqu'au max déclaré

Si le commentaire annonce IDs 699–746, **48 tuples** dans `quizzes_data`.  
Vérifier : `print(len(quizzes_data))` → doit afficher 48.

### 7. Pipeline write_quiz_files() + if **name**

```python
def write_quiz_files():
    os.makedirs(XXX_QUIZ_DIR, exist_ok=True)
    os.makedirs(XXX_ANSWERS_DIR, exist_ok=True)
    for qid, title, subject, level, questions in quizzes_data:
        quiz    = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)
        with open(os.path.join(XXX_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8") as f:
            json.dump(quiz, f, ensure_ascii=False, indent=2)
        with open(os.path.join(XXX_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8") as f:
            json.dump(answers, f, ensure_ascii=False, indent=2)
    print(f"{len(quizzes_data)} quiz générés dans {XXX_OUTPUT_DIR}")

if __name__ == "__main__":
    write_quiz_files()
```

## Schéma JSON cible

**quiz/<id>.json :**

```json
{
  "id": 1,
  "title": "Quiz titre",
  "subject": "Matière",
  "level": "niveau",
  "created_at": "2026-03-12T10:00:00Z",
  "questions": [
    {
      "id": 1,
      "type": "qcm",
      "question": "Texte ?",
      "options": ["A", "B", "C", "D"]
    },
    { "id": 2, "type": "vrai-faux", "question": "Affirmation ?" },
    { "id": 3, "type": "texte", "question": "Développer..." }
  ]
}
```

**quiz_answers/<id>.json :**

```json
{
  "quiz_id": 1,
  "title": "Quiz titre",
  "subject": "Matière",
  "level": "niveau",
  "answers": [
    { "question_id": 1, "correct_option": "B", "explanation": "Car..." },
    { "question_id": 2, "correct": true, "explanation": "Car..." },
    {
      "question_id": 3,
      "correct_answer": "Réponse développée",
      "explanation": "Car..."
    }
  ]
}
```

## Exécution

```powershell
# Activer le venv
& .venv\Scripts\Activate.ps1

# Générer les fichiers
python dev/tools/quiz/generate_<matiere>_<niveau>.py

# Vérifier la sortie
Get-ChildItem dev/tools/quiz/<matiere>_<niveau>_quizzes/quiz/ | Measure-Object
```

## Pattern de questions (8 questions/quiz recommandé)

```
qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
```
