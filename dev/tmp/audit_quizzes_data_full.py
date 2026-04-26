#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Audit complet du corpus `quizzes_data` dans generate_2nde_francais.py."""

import importlib.util
import json
from collections import Counter
from pathlib import Path

SCRIPT_PATH = Path(__file__).resolve().parent.parent / "tools" / "quiz" / "enrichment" / "generator" / "generate_2nde_francais.py"

spec = importlib.util.spec_from_file_location("generate_2nde_francais", SCRIPT_PATH)
quiz_module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(quiz_module)
quizzes_data = getattr(quiz_module, "quizzes_data", [])

allowed_types = {"qcm", "vrai-faux"}
problems = []
question_ids = []

for quiz in quizzes_data:
    if not isinstance(quiz, tuple) or len(quiz) != 5:
        problems.append({"quiz": quiz, "issue": "invalid quiz tuple shape"})
        continue

    quiz_id, title, subject, level, questions = quiz
    if not isinstance(questions, list):
        problems.append({"quiz_id": quiz_id, "issue": "questions not a list"})
        continue

    if len(questions) != 8:
        problems.append({"quiz_id": quiz_id, "issue": f"unexpected question count {len(questions)}"})

    for question in questions:
        if not isinstance(question, dict):
            problems.append({"quiz_id": quiz_id, "question": question, "issue": "question not dict"})
            continue

        question_id = question.get("id")
        question_ids.append(question_id)
        qtype = str(question.get("type", "")).strip().lower().replace("_", "-")

        if qtype not in allowed_types:
            problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": f"invalid type {qtype}"})

        if not question.get("question") or not isinstance(question.get("question"), str):
            problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "invalid or missing question text"})

        if qtype == "qcm":
            options = question.get("options")
            if not isinstance(options, list):
                problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "qcm missing options list"})
            else:
                if len(options) < 2:
                    problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": f"qcm only {len(options)} options"})
                if len(set(options)) != len(options):
                    problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "qcm options contain duplicates"})

            correct_option = question.get("correct_option")
            if correct_option is None:
                problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "missing correct_option"})
            elif isinstance(options, list) and correct_option not in options:
                problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "correct_option not in options"})

            if "correct" in question:
                problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "qcm should not define boolean correct"})

        if qtype == "vrai-faux":
            if "correct_option" in question:
                problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "vrai-faux should not define correct_option"})
            if "options" in question:
                problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "vrai-faux should not define options"})
            if "correct" not in question:
                problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "missing correct boolean"})
            elif not isinstance(question["correct"], bool):
                problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "correct is not boolean"})

        explanation = question.get("explanation")
        if not explanation or not isinstance(explanation, str):
            problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "missing or invalid explanation"})
        elif len(explanation.strip()) < 15:
            problems.append({"quiz_id": quiz_id, "question_id": question_id, "issue": "explanation too short"})

report = {
    "quiz_count": len(quizzes_data),
    "question_count": sum(len(q[4]) for q in quizzes_data if isinstance(q, tuple) and len(q) == 5),
    "quiz_question_counts": {q[0]: len(q[4]) for q in quizzes_data if isinstance(q, tuple) and len(q) == 5},
    "question_type_counts": Counter(
        str(question.get("type", "")).strip().lower().replace("_", "-")
        for quiz in quizzes_data
        for question in (quiz[4] if isinstance(quiz, tuple) and len(quiz) == 5 else [])
    ),
    "duplicate_question_ids": [qid for qid, count in Counter(question_ids).items() if count > 1],
    "problems": problems,
}

print(json.dumps(report, ensure_ascii=False, indent=2))
