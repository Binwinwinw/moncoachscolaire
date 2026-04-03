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
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "<niveau>_<matiere>_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")

# Keep this tuple updated and aligned with the declared ID range.
# Pattern recommended for 8 questions:
# qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
quizzes_data = [
    (
        id,
        "Titre du quiz",
        "Matiere",
        "niveau",
        [
            {
                "id": f"{id}question_1",
                "type": "qcm",
                "question": "Question QCM ?",
                "options": ["A", "B", "C", "D"],
                "correct_option": "A",
                "explanation": "Explication concise datée sourcée.",
            },
            {
                "id": f"{id}question_2",
                "type": "vrai-faux",
                "question": "Affirmation vrai/faux ?",
                "correct": True,
                "explanation": "Explication concise datée sourcée.",
            },
            {
                "id": f"{id}question_3",
                "type": "vrai-faux + vrai-faux",
                "question": "Affirmation combinée vrai/faux ?",
                "correct_answer": "Reponse attendue + Reponse attendue",
                "explanation": "Explication concise datée sourcée.",
            },
            {
                "id": f"{id}question_4",
                "type": "qcm",
                "question": "Question QCM 2 ?",
                "options": ["A", "B", "C", "D"],
                "correct_option": "B",
                "explanation": "Explication concise datée sourcée.",
            },
            {
                "id": f"{id}question_5",
                "type": "vrai-faux",
                "question": "Affirmation vrai/faux 2 ?",
                "correct": False,
                "explanation": "Explication concise datée sourcée.",
            },
            {
                "id": f"{id}question_6",
                "type": "vrai-faux + vrai-faux",
                "question": "Affirmation combinée vrai/faux 2 ?",
                "correct_answer": "Reponse attendue + Reponse attendue",
                "explanation": "Explication concise datée sourcée.",
            },
            {
                "id": f"{id}question_7",
                "type": "qcm",
                "question": "Question QCM 3 ?",
                "options": ["A", "B", "C", "D"],
                "correct_option": "C",
                "explanation": "Explication concise datée sourcée.",
            },
            {
                "id": f"{id}question_8",
                "type": "vrai-faux",
                "question": "Affirmation vrai/faux 3 ?",
                "correct": True,
                "explanation": "Explication concise datée sourcée.",
            },
            {
                "id": f"{id}question_9",
                "type": "vrai-faux + vrai-faux",
                "question": "Affirmation combinée vrai/faux 3 ?",
                "correct_answer": "Reponse attendue + Reponse attendue",
                "explanation": "Explication concise datée sourcée.",
            }
        ]
    ),
]


def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []

    for question in questions:
        qtype = str(question.get("type", "texte"))
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
                    "type": "open",
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
        if q["type"] == "qcm":
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "qcm",
                    "answer": q["correct_option"],
                    "correction": q["explanation"],
                }
            )
        elif q["type"] == "vrai-faux":
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "vrai-faux",
                    "answer": "vrai" if q["correct"] else "faux",
                    "correction": q["explanation"],
                }
            )
        else:  # texte
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "open",
                    "answer": q["correct_answer"],
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

    allowed_types = {"qcm", "vrai-faux", "open"}
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

    for qid, title, subject, level, questions in quizzes_data:
        quiz = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)

        with open(os.path.join(QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as f:
            json.dump(quiz, f, ensure_ascii=False, indent=2)
            f.write("\n")

        with open(os.path.join(ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as f:
            json.dump(answers, f, ensure_ascii=False, indent=2)
            f.write("\n")

    print(f"{len(quizzes_data)} quiz generated in {OUTPUT_DIR}")
    verify_random_sentinel()


if __name__ == "__main__":
    write_quiz_files()
