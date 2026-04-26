````instructions
---
name: Python rules (quiz generators)
description: Conventions Python à appliquer systématiquement sur tous les scripts generate_*.py du projet MonCoachScolaire
applyTo: "dev/tools/quiz/generate_*.py"
---

## Règles obligatoires — Pattern quiz generator

### 1. En-tête du fichier

Toujours en première position, avant toute logique :

```python
#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Génération des quiz <Matière> <Niveau> – IDs <min>–<max>
<N> quizzes x 8 questions = <N*8> questions
Thèmes : <liste des blocs thématiques>
Pattern : qcm, vrai-faux, qcm, vrai-faux, qcm, vrai-faux, qcm, vrai-faux
"""

import json
import os
from datetime import UTC, datetime
````

**Interdit :** `from datetime import datetime` seul, `datetime.utcnow()`.

---

### 2. Constantes de chemin

Toujours SCRIPT_DIR-relatif, jamais de chemins codés en dur comme `"quizzes"` :

```python
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
<MATIERE>_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "<matiere>_<niveau>_quizzes")
<MATIERE>_QUIZ_DIR   = os.path.join(<MATIERE>_OUTPUT_DIR, "quiz")
<MATIERE>_ANSWERS_DIR = os.path.join(<MATIERE>_OUTPUT_DIR, "quiz_answers")
os.makedirs(<MATIERE>_QUIZ_DIR, exist_ok=True)
os.makedirs(<MATIERE>_ANSWERS_DIR, exist_ok=True)
```

Convention de nommage : `<matiere>_<niveau>_quizzes/quiz/` et `quiz_answers/`.

---

### 3. Fonction make_quiz

Striper les champs de correction, trier les clés canoniques :

```python
def make_quiz(qid, title, subject, level, questions):
    return {
        "id": qid,
        "title": title,
        "subject": subject,
        "level": level,
        "created_at": datetime.now(UTC).isoformat().replace("+00:00", "Z"),
        "questions": [
            {k: v for k, v in q.items()
             if k not in ("correct_option", "correct", "correct_answer", "explanation")}
            for q in questions
        ]
    }
```

---

### 4. Fonction make_answers

Dispatch par type avec une branche pour chaque type valide :

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
                "correct": q["correct"],
                "explanation": q["explanation"]
            })
        else:
            raise ValueError(f"Type de question invalide : {q['type']}. Utiliser uniquement 'qcm' ou 'vrai-faux'.")
    return {
        "quiz_id": qid,
        "title": title,
        "subject": subject,
        "level": level,
        "answers": answers
    }
```

---

### 5. Types de question — valeurs autorisées

| Type          | Champ réponse data                       | Champ réponse answers |
| ------------- | ---------------------------------------- | --------------------- |
| `"qcm"`       | `options` (list), `correct_option` (str) | `correct_option`      |
| `"vrai-faux"` | `correct` (bool)                         | `correct`             |

**Règle critique :** Les nouveaux scripts doivent utiliser uniquement `"qcm"` et `"vrai-faux"`.

**Règle critique :** Le tiret est OBLIGATOIRE dans `"vrai-faux"` (pas `"vrai_faux"`).
Avant tout commit, vérifier qu'aucun `"vrai_faux"` n'est présent :

```
grep -n "vrai_faux" <fichier>.py
```

---

### 6. Structure des données quizzes_data

Liste de tuples `(id, title, subject, level, [questions])` :

```python
quizzes_data = [

    # =========================================================
    # BLOC 1 – <THEME> (<ID_MIN>–<ID_MAX>)
    # =========================================================
    (699, "Titre du quiz", "Matière", "Niveau", [
        {"id": "699_1", "type": "qcm",
         "question": "...",
         "options": ["A", "B", "C", "D"],
         "correct_option": "A",
         "explanation": "..."},
        {"id": "699_2", "type": "vrai-faux",
         "question": "...",
         "correct": True,
         "explanation": "..."},
        {"id": "699_3", "type": "qcm",
         "question": "...",
         "options": ["Option A", "Option B", "Option C", "Option D"],
         "correct_option": "Option A",
         "explanation": "..."},
        # … 8 questions par quiz (pattern suggéré : qcm, vrai-faux, qcm, vrai-faux, qcm, vrai-faux, qcm, vrai-faux)
    ]),
]
```

**Règle ID question :** `"<quiz_id>_<numero>"` (ex : `"699_1"`, `"699_8"`).

---

### 7. Pipeline d'export write_quiz_files

Toujours présent en fin de fichier, après `quizzes_data` :

```python
def write_quiz_files():
    os.makedirs(<MATIERE>_QUIZ_DIR, exist_ok=True)
    os.makedirs(<MATIERE>_ANSWERS_DIR, exist_ok=True)
    for qid, title, subject, level, questions in quizzes_data:
        quiz = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)
        quiz_path = os.path.join(<MATIERE>_QUIZ_DIR, f"{qid}.json")
        answers_path = os.path.join(<MATIERE>_ANSWERS_DIR, f"{qid}.json")
        with open(quiz_path, "w", encoding="utf-8") as f:
            json.dump(quiz, f, ensure_ascii=False, indent=2)
        with open(answers_path, "w", encoding="utf-8") as f:
            json.dump(answers, f, ensure_ascii=False, indent=2)
    print(f"{len(quizzes_data)} quiz <Matière> <Niveau> générés dans {<MATIERE>_OUTPUT_DIR}")


if __name__ == "__main__":
    write_quiz_files()
```

---

### 8. Checklist de validation avant commit

- [ ] `from datetime import UTC, datetime` présent
- [ ] `SCRIPT_DIR` + constantes de dossiers dédiées
- [ ] `datetime.now(UTC)...replace("+00:00", "Z")` (pas `utcnow()`)
- [ ] Tous les types dans `make_answers` : `qcm`, `vrai-faux`
- [ ] Aucun `"vrai_faux"` (underscore) dans le fichier
- [ ] IDs quiz consécutifs et complets (pas de trou)
- [ ] `write_quiz_files()` + `if __name__ == "__main__"` en fin de fichier
- [ ] Exécution validée : `python generate_<matiere>_<niveau>.py`
- [ ] Comptage JSON : `len(quizzes_data)` fichiers dans `quiz/` et `quiz_answers/`

```

```
