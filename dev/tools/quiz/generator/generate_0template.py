#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Template standard for generate_<niveau>_<matiere>.py scripts.

Usage:
1) Copy this file to generate_<niveau>_<matiere>.py
2) Replace placeholders and fill quizzes_data
3) Run: python dev/tools/quiz/generate_<niveau>_<matiere>.py
"""

from __future__ import annotations

import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "<niveau>_<matiere>_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

# Keep this tuple updated and aligned with the declared ID range.
# Pattern aligned with the current runtime structure:
# qcm, vrai-faux, qcm, qcm, vrai-faux, qcm, qcm, vrai-faux
quizzes_data = [
    (
        "0001",
        "Titre du quiz",
        "Matiere",
        "niveau",
        [
            {
                "id": "0001_1",
                "type": "qcm",
                "question": "Quelle réponse correspond au document ?",
                "options": [
                    "Réponse attendue",
                    "Distracteur 1",
                    "Distracteur 2",
                    "Distracteur 3",
                ],
                "correct_option": "Réponse attendue",
                "explanation": "Explication concise, claire et pédagogique.",
            },
            {
                "id": "0001_2",
                "type": "vrai-faux",
                "question": "Cette affirmation est correcte.",
                "correct": True,
                "explanation": "Justification brève et compréhensible.",
            },
            {
                "id": "0001_3",
                "type": "qcm",
                "question": "Quelle formulation décrit le mieux la situation ?",
                "options": [
                    "Bonne formulation",
                    "Formulation imprécise",
                    "Contresens 1",
                    "Contresens 2",
                ],
                "correct_option": "Bonne formulation",
                "explanation": "Le quiz runtime stocke la réponse littérale, pas une lettre A/B/C/D.",
            },
            {
                "id": "0001_4",
                "type": "qcm",
                "question": "Quelle méthode est la plus pertinente ?",
                "options": [
                    "Méthode correcte",
                    "Méthode incomplète",
                    "Méthode erronée",
                    "Réponse hors sujet",
                ],
                "correct_option": "Méthode correcte",
                "explanation": "Le format de sortie doit correspondre au couple quiz/réponses du dépôt.",
            },
            {
                "id": "0001_5",
                "type": "vrai-faux",
                "question": "Une vérification finale est utile.",
                "correct": True,
                "explanation": "La relecture évite beaucoup d'erreurs simples.",
            },
            {
                "id": "0001_6",
                "type": "qcm",
                "question": "Quelle erreur faut-il éviter ?",
                "options": [
                    "Erreur classique",
                    "Bonne pratique",
                    "Réflexe utile",
                    "Méthode experte",
                ],
                "correct_option": "Erreur classique",
                "explanation": "Le distracteur correct doit rester exprimé comme le texte exact de la bonne réponse.",
            },
            {
                "id": "0001_7",
                "type": "qcm",
                "question": "Quelle conclusion est correcte ?",
                "options": [
                    "Conclusion juste",
                    "Conclusion incomplète",
                    "Conclusion fausse",
                    "Conclusion hors contexte",
                ],
                "correct_option": "Conclusion juste",
                "explanation": "Cette structure recopie le comportement observé dans les quiz déjà en production.",
            },
            {
                "id": "0001_8",
                "type": "vrai-faux",
                "question": "Le modèle peut rester simple et robuste.",
                "correct": True,
                "explanation": "Le plus important est la compatibilité avec le runtime existant.",
            },
        ],
    ),
]


def normalize_question_type(question_type):
    qtype = str(question_type).strip().lower()
    if qtype in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if qtype == "qcm":
        return "qcm"
    return "vrai-faux"


def resolve_qcm_answer(question):
    options = list(question.get("options", []))
    raw_answer = str(question.get("correct_option", question.get("correct_answer", ""))).strip()
    letter_map = {"A": 0, "B": 1, "C": 2, "D": 3}

    if raw_answer.upper() in letter_map:
        option_index = letter_map[raw_answer.upper()]
        if option_index < len(options):
            return options[option_index]

    return raw_answer


def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []

    for question in questions:
        qtype = normalize_question_type(question.get("type", "vrai-faux"))
        if qtype == "qcm":
            runtime_questions.append(
                {
                    "type": "qcm",
                    "question": str(question.get("question", "")),
                    "choices": list(question.get("options", [])),
                }
            )
        elif qtype == "vrai-faux":
            runtime_questions.append(
                {
                    "type": "vrai-faux",
                    "question": str(question.get("question", "")),
                }
            )
        else:
            runtime_questions.append(
                {
                    "type": "vrai-faux",
                    "question": str(question.get("question", "")),
                }
            )

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Série {qid}",
            "type": "quiz",
            "level": level,
            "subject": subject,
            "description": f"Diagnostic {subject} {level} : {title}",
            "status": "published",
            "created_at": created_at,
            "updated_at": created_at,
        },
        "quiz": {
            "title": title,
            "type": "quiz",
            "level": level,
            "subject": subject,
            "question_count": len(runtime_questions),
            "passing_score": 70,
            "time_limit_minutes": 15,
            "questions": runtime_questions,
        },
        "exercisenotion": [],
        "exerciseresponses": [],
    }


def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        qtype = normalize_question_type(q.get("type", "vrai-faux"))
        if qtype == "qcm":
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "qcm",
                    "answer": resolve_qcm_answer(q),
                    "correction": q["explanation"],
                }
            )
        elif qtype == "vrai-faux":
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "vrai-faux",
                    "answer": "vrai" if q["correct"] else "faux",
                    "correction": q["explanation"],
                }
            )
        else:
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "vrai-faux",
                    "answer": "vrai" if str(q.get("correct", q.get("correct_answer", "faux"))).strip().lower() in {"true", "vrai", "1"} else "faux",
                    "correction": q["explanation"],
                }
            )

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Série {qid}",
            "level": level,
            "subject": subject,
        },
        "quiz": {
            "title": title,
            "question_count": len(answers),
            "level": level,
            "subject": subject,
            "answers": answers,
        },
    }


def verify_random_sentinel():
    if not quizzes_data:
        print("[sentinel] skipped: no quiz data")
        return

    sentinel_qid, _, _, _, _ = random.choice(quizzes_data)
    quiz_path = os.path.join(QUIZ_DIR, f"{sentinel_qid}.json")
    answers_path = os.path.join(ANSWERS_DIR, f"{sentinel_qid}.json")

    with open(quiz_path, "r", encoding="utf-8") as file_obj:
        quiz_payload = json.load(file_obj)

    with open(answers_path, "r", encoding="utf-8") as file_obj:
        answers_payload = json.load(file_obj)

    questions = list(quiz_payload.get("quiz", {}).get("questions", []))
    answers = list(answers_payload.get("quiz", {}).get("answers", []))

    if len(questions) != len(answers):
        raise ValueError(
            f"[sentinel] mismatch for quiz {sentinel_qid}: questions={len(questions)} answers={len(answers)}"
        )

    allowed_types = {"qcm", "vrai-faux"}
    for index, question in enumerate(questions):
        question_type = str(question.get("type", ""))
        if question_type not in allowed_types:
            raise ValueError(
                f"[sentinel] invalid question type for quiz {sentinel_qid} at index {index}: {question_type}"
            )

    print(
        f"[sentinel] OK quiz={sentinel_qid} questions={len(questions)} answers={len(answers)}"
    )


def write_quiz_files():
    os.makedirs(QUIZ_DIR, exist_ok=True)
    os.makedirs(ANSWERS_DIR, exist_ok=True)
    os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
    os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)

    for qid, title, subject, level, questions in quizzes_data:
        quiz = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)

        for dir_path, payload in [
            (QUIZ_DIR, quiz),
            (ANSWERS_DIR, answers),
            (RUNTIME_QUIZ_DIR, quiz),
            (RUNTIME_ANSWERS_DIR, answers),
        ]:
            with open(os.path.join(dir_path, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as f:
                json.dump(payload, f, ensure_ascii=False, indent=2)
                f.write("\n")

    print(f"{len(quizzes_data)} quiz generated in {OUTPUT_DIR} and synced to runtime")
    verify_random_sentinel()


if __name__ == "__main__":
    write_quiz_files()
