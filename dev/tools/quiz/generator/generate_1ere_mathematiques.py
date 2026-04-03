#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot L — 1ère Mathématiques | version progressive.

Usage:
    python dev/tools/quiz/generate_1ere_mathematiques.py
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "1ere_mathematiques_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")

# Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
quizzes_data = [

    # ─────────────────────────────────────────────────────────
    # BLOC 1 — Second degré (1059–1064)
    # ─────────────────────────────────────────────────────────
    (
        1059,
        'Mathématiques 1ère - Résoudre une équation du second degré',
        'Mathématiques',
        '1ere',
        [
            {
                'id': '1059_1',
                'type': 'qcm',
                'question': 'What is the discriminant of the quadratic equation x^2 - 4x + 3 = 0?',
                'options': [
                    '0',
                    '1',
                    '4',
                    '5'
                ],
                'correct_option': '0',
                'explanation': 'The discriminant is calculated as b^2 - 4ac. Here, a=1, b=-4, and c=3, so the discriminant is (-4)^2 - 4*1*3 = 16 - 12 = 4.',
            },
            {
                'id': '1059_2',
                'type': 'vrai-faux',
                'question': 'The equation x^2 + 2x + 5 = 0 has real solutions.',
                'correct': False,
                'explanation': 'The discriminant of the equation is b^2 - 4ac = (2)^2 - 4*1*5 = 4 - 20 = -16, which is negative, so there are no real solutions.',
            },
            {
                'id': '1059_3',
                'type': 'texte',
                'question': 'Solve the equation x^2 - 6x + 9 = 0 and provide the solution.',
                'correct_answer': 'x = 3',
                'explanation': 'The equation can be factored as (x - 3)^2 = 0, which gives the solution x = 3.',
            },
            {
                'id': '1059_4',
                'type': 'qcm',
                'question': 'What are the roots of the equation x^2 - 5x + 6 = 0?',
                'options': [
                    '1 and 6',
                    '2 and 3',
                    '3 and 4',
                    '4 and 5'
                ],
                'correct_option': '2 and 3',
                'explanation': 'The equation can be factored as (x - 2)(x - 3) = 0, which gives the roots x = 2 and x = 3.',
            },
            {
                'id': '1059_5',
                'type': 'vrai-faux',
                'question': 'The equation x^2 + x + 1 = 0 has complex solutions.',
                'correct': True,
                'explanation': 'The discriminant of the equation is b^2 - 4ac = (1)^2 - 4*1*1 = 1 - 4 = -3, which is negative, so there are complex solutions.',
            },
            {
                'id': '1059_6',
                'type': 'texte',
                'question': 'Find the vertex of the parabola defined by the equation y = x^2 - 4x + 7.',
                'correct_answer': '(2, 3)',
                'explanation': 'The vertex of a parabola defined by y = ax^2 + bx + c can be found using the formula (-b/(2a), f(-b/(2a))). Here, a=1, b=-4, and c=7, so the vertex is at (4/(2*1), f(4/(2*1))) = (2, (2)^2 - 4*2 + 7) = (2, 3).',
            },
            {
                'id': '1059_7',
                'type': 'qcm',
                'question': 'What is the axis of symmetry of the parabola defined by the equation y = 2x^2 - 8x + 5?',
                'options': [
                    'x = 2',
                    'x = 4',
                    'x = -2',
                    'x = -4'
                ],
                'correct_option': 'x = 2',
                'explanation': 'The axis of symmetry of a parabola defined by y = ax^2 + bx + c is given by the line x = -b/(2a). Here, a=2 and b=-8, so the axis of symmetry is x = -(-8)/(2*2) = 8/4 = 2.',
            },
            {
                'id': '1059_8',
                'type': 'vrai-faux',
                'question': 'The equation x^2 - 2x + 1 = 0 has a double root.',
                'correct': True,
                'explanation': 'The equation can be factored as (x - 1)^2 = 0, which means it has a double root at x = 1.',
            },
        ]
    ),
    (1060, "Title", "Subject", "Level", [

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
        ],
    }


def make_answers(qid, title, subject, level, questions):
    answers = []
    for q in questions:
        if q["type"] == "qcm":
            answers.append({
                "question_id": q["id"],
                "correct_option": q["correct_option"],
                "explanation": q["explanation"],
            })
        elif q["type"] == "vrai-faux":
            correct = q["correct"]
            if isinstance(correct, str):
                correct = correct.strip().lower() == "vrai"
            answers.append({
                "question_id": q["id"],
                "correct": correct,
                "explanation": q["explanation"],
            })
        else:  # texte / open
            answers.append({
                "question_id": q["id"],
                "correct_answer": q["correct_answer"],
                "explanation": q["explanation"],
            })
    return {
        "quiz_id": qid,
        "title": title,
        "subject": subject,
        "level": level,
        "answers": answers,
    }


def verify_random_sentinel():
    """Lit un quiz+réponses aléatoire et vérifie la cohérence structurelle."""
    entry = random.choice(quizzes_data)
    qid = entry[0]
    quiz_path    = os.path.join(QUIZ_DIR,    f"{qid}.json")
    answers_path = os.path.join(ANSWERS_DIR, f"{qid}.json")
    with open(quiz_path,    encoding="utf-8") as f:
        quiz_obj = json.load(f)
    with open(answers_path, encoding="utf-8") as f:
        ans_obj = json.load(f)
    allowed = {"qcm", "vrai-faux", "open", "texte"}
    questions = quiz_obj["questions"]
    answers   = ans_obj["answers"]
    assert len(questions) == len(answers), (
        f"[sentinel] FAIL quiz={qid}: {len(questions)} questions vs {len(answers)} réponses"
    )
    for q in questions:
        assert q["type"] in allowed, f"[sentinel] FAIL quiz={qid}: type inconnu '{q['type']}'"
    print(f"[sentinel] OK quiz={qid} questions={len(questions)} answers={len(answers)}")


def write_quiz_files():
    os.makedirs(QUIZ_DIR,    exist_ok=True)
    os.makedirs(ANSWERS_DIR, exist_ok=True)
    for qid, title, subject, level, questions in quizzes_data:
        quiz    = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)
        with open(os.path.join(QUIZ_DIR,    f"{qid}.json"), "w", encoding="utf-8") as f:
            json.dump(quiz, f, ensure_ascii=False, indent=2)
        with open(os.path.join(ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8") as f:
            json.dump(answers, f, ensure_ascii=False, indent=2)
    print(f"{len(quizzes_data)} quiz générés dans {OUTPUT_DIR}")
    verify_random_sentinel()


if __name__ == "__main__":
    write_quiz_files()
