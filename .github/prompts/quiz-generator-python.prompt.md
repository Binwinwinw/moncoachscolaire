---
name: quiz-generator-python
description: Créer ou corriger un script Python de génération de quiz scolaire (pattern MonCoachScolaire)
---

@workspace Génère ou corrige un script Python `generate_<matiere>_<niveau>.py` selon le **pattern standard MonCoachScolaire**.

---

## Contexte

Les scripts de génération de quiz se trouvent dans `dev/tools/quiz/`.
Chaque script produit deux séries de fichiers JSON dans un dossier `<matiere>_<niveau>_quizzes/` :
- `quiz/<id>.json` — questions sans réponses (affichage côté élève)
- `quiz_answers/<id>.json` — réponses + explications (validation côté API)

Voir `dev/tools/quiz/generate_pc_3eme.py` (IDs 699–746) et `generate_svt_3eme.py` (IDs 651–698) comme référence.

---

## Checklist de correction (7 points obligatoires)

Avant de générer ou corriger un script, vérifier chaque point :

1. **Import datetime moderne**
   ```python
   from datetime import UTC, datetime
   ```
   Interdit : `datetime.utcnow()`, `import datetime` seul.

2. **Constantes de chemin SCRIPT_DIR-relatifs**
   ```python
   SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
   XXX_OUTPUT_DIR  = os.path.join(SCRIPT_DIR, "<matiere>_<niveau>_quizzes")
   XXX_QUIZ_DIR    = os.path.join(XXX_OUTPUT_DIR, "quiz")
   XXX_ANSWERS_DIR = os.path.join(XXX_OUTPUT_DIR, "quiz_answers")
   ```
   Interdit : chemins codés en dur comme `"quizzes"` ou `"answers"`.

3. **datetime ISO 8601 UTC dans make_quiz**
   ```python
   "created_at": datetime.now(UTC).isoformat().replace("+00:00", "Z"),
   ```

4. **Dispatch complet dans make_answers**
   Les trois branches sont obligatoires :
   ```python
   if q["type"] == "qcm":        → correct_option
   elif q["type"] == "vrai-faux": → correct (bool)
   else:                          → correct_answer (str)
   ```

5. **Type `"vrai-faux"` avec tiret** (jamais `"vrai_faux"`)
   Vérifier globalement avec `grep -n "vrai_faux"` — aucune occurrence acceptée.

6. **Complétion des IDs jusqu'au max déclaré**
   Si le bloc de commentaire annonce IDs 699–746, 48 tuples doivent être présents.
   Compter avec : `len(quizzes_data)`.

7. **Pipeline write_quiz_files() + if __name__**
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

---

## Template complet d'un nouveau script

```python
#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Génération des quiz <Matière> <Niveau> – IDs <min>–<max>
<N> quizzes x 8 questions = <N*8> questions
Thèmes : <liste thèmes>
Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
"""

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR       = os.path.dirname(os.path.abspath(__file__))
XXX_OUTPUT_DIR   = os.path.join(SCRIPT_DIR, "<matiere>_<niveau>_quizzes")
XXX_QUIZ_DIR     = os.path.join(XXX_OUTPUT_DIR, "quiz")
XXX_ANSWERS_DIR  = os.path.join(XXX_OUTPUT_DIR, "quiz_answers")
os.makedirs(XXX_QUIZ_DIR, exist_ok=True)
os.makedirs(XXX_ANSWERS_DIR, exist_ok=True)


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
        else:   # texte
            answers.append({
                "question_id": q["id"],
                "correct_answer": q["correct_answer"],
                "explanation": q["explanation"]
            })
    return {
        "quiz_id": qid,
        "title": title,
        "subject": subject,
        "level": level,
        "answers": answers
    }


quizzes_data = [

    # =========================================================
    # BLOC 1 – <THEME> (<ID_MIN>–<ID_MAX>)
    # =========================================================
    (<ID>, "<Titre du quiz>", "<Matière>", "<Niveau>", [
        {"id": "<ID>_1", "type": "qcm",
         "question": "...",
         "options": ["Option A", "Option B", "Option C", "Option D"],
         "correct_option": "Option A",
         "explanation": "Explication pédagogique complète."},
        {"id": "<ID>_2", "type": "vrai-faux",
         "question": "Affirmation vraie ou fausse ?",
         "correct": True,
         "explanation": "Explication pédagogique complète."},
        {"id": "<ID>_3", "type": "texte",
         "question": "Réponse courte attendue ?",
         "correct_answer": "réponse attendue",
         "explanation": "Explication pédagogique complète."},
        # … 8 questions par quiz
    ]),
]


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
    print(f"{len(quizzes_data)} quiz <Matière> <Niveau> générés dans {XXX_OUTPUT_DIR}")


if __name__ == "__main__":
    write_quiz_files()
```

---

## Structure d'une question par type

### QCM
```python
{"id": "700_1", "type": "qcm",
 "question": "Question à choix multiples ?",
 "options": ["Option A", "Option B", "Option C", "Option D"],
 "correct_option": "Option A",
 "explanation": "Explication complète et pédagogique."}
```

### Vrai-Faux
```python
{"id": "700_2", "type": "vrai-faux",
 "question": "Affirmation à valider ou infirmer.",
 "correct": True,   # ou False
 "explanation": "Explication complète et pédagogique."}
```

### Texte (réponse courte)
```python
{"id": "700_3", "type": "texte",
 "question": "Quel est le terme technique pour … ?",
 "correct_answer": "terme attendu",
 "explanation": "Explication complète et pédagogique."}
```

---

## Commande de validation après génération

```bash
# Exécution
.venv\Scripts\python.exe dev/tools/quiz/generate_<matiere>_<niveau>.py

# Vérification du nombre de fichiers générés (Windows PowerShell)
(Get-ChildItem "dev/tools/quiz/<matiere>_<niveau>_quizzes/quiz/*.json").Count
(Get-ChildItem "dev/tools/quiz/<matiere>_<niveau>_quizzes/quiz_answers/*.json").Count
```

Les deux comptages doivent être égaux à `len(quizzes_data)`.

---

## Répartition des IDs dans le projet

| Script | IDs | Matière | Niveau |
|--------|-----|---------|--------|
| `generate_svt_3eme.py` | 651–698 | SVT | 3ème |
| `generate_pc_3eme.py` | 699–746 | Physique-Chimie | 3ème |
| *(prochain script)* | 747–… | À définir | À définir |

Toujours vérifier l'ID max existant avant de créer un nouveau script.
