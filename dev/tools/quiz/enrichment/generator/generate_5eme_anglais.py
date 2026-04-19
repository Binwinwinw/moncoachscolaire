#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Générateur quiz Anglais 5e."""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "anglais_5eme_quizzes"
SUBJECT = "Anglais"
LEVEL = "5eme"
START_ID = 1913

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

THEMES = [
    "Present simple",
    "Daily routines",
    "There is and there are",
    "School vocabulary",
    "Family and relationships",
    "Directions and places",
    "Food and habits",
    "Questions and short answers",
    "Can and cannot",
    "Description of people",
]


def normalize_question_type(question_type: str) -> str:
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    return "vrai-faux"


def build_questions(theme: str, quiz_id: int) -> list[dict]:
    prefix = f"[{SUBJECT} {LEVEL}]"
    return [
        {
            "id": f"{quiz_id}_1",
            "type": "qcm",
            "question": f"{prefix} For the theme '{theme}', what is the best first step in a quiz?",
            "options": [
                "Read the instruction carefully",
                "Answer without thinking",
                "Skip the sentence",
                "Choose the longest answer",
            ],
            "correct_option": "Read the instruction carefully",
            "explanation": "Reading the instruction helps identify what the question expects in English.",
        },
        {
            "id": f"{quiz_id}_2",
            "type": "vrai-faux",
            "question": f"{prefix} Checking the key words can help you answer correctly on '{theme}'.",
            "correct": True,
            "explanation": "Key words often reveal the grammar point or vocabulary target.",
        },
        {
            "id": f"{quiz_id}_3",
            "type": "qcm",
            "question": f"{prefix} What habit helps you improve on '{theme}'?",
            "options": [
                "Practise with short sentences and examples",
                "Memorise without understanding",
                "Ignore corrections",
                "Avoid speaking or reading",
            ],
            "correct_option": "Practise with short sentences and examples",
            "explanation": "Short, repeated practice helps learners reuse the notion in context.",
        },
        {
            "id": f"{quiz_id}_4",
            "type": "vrai-faux",
            "question": f"{prefix} In English, explaining why an answer is correct can help you progress on '{theme}'.",
            "correct": True,
            "explanation": "Justification improves understanding and helps retain grammar or vocabulary.",
        },
        {
            "id": f"{quiz_id}_5",
            "type": "qcm",
            "question": f"{prefix} Which sign shows a good mastery of '{theme}'?",
            "options": [
                "Using the notion in a simple sentence",
                "Guessing every answer",
                "Copying without reading",
                "Changing answers randomly",
            ],
            "correct_option": "Using the notion in a simple sentence",
            "explanation": "Being able to reuse the notion in context is a strong sign of understanding.",
        },
        {
            "id": f"{quiz_id}_6",
            "type": "vrai-faux",
            "question": f"{prefix} Re-reading the sentence before validating is useful for '{theme}'.",
            "correct": True,
            "explanation": "A final re-read helps catch agreement or vocabulary errors.",
        },
        {
            "id": f"{quiz_id}_7",
            "type": "qcm",
            "question": f"{prefix} If you do not understand a question on '{theme}', what should you do first?",
            "options": [
                "Look for familiar words and grammar clues",
                "Leave it blank immediately",
                "Invent a rule",
                "Choose the first answer",
            ],
            "correct_option": "Look for familiar words and grammar clues",
            "explanation": "Grammar clues and familiar vocabulary help rebuild meaning.",
        },
        {
            "id": f"{quiz_id}_8",
            "type": "vrai-faux",
            "question": f"{prefix} Reviewing mistakes after correction is a good way to improve on '{theme}'.",
            "correct": True,
            "explanation": "Error analysis reinforces the correct form and builds confidence.",
        },
    ]


quizzes_data = [
    (START_ID + offset, f"{SUBJECT} 5eme - {theme}", SUBJECT, LEVEL, build_questions(theme, START_ID + offset))
    for offset, theme in enumerate(THEMES)
]


def make_quiz(qid, title, subject, level, questions, source="MonCoachScolaire", programme_ref="Cycle 4"):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    created_at = datetime.now(UTC).isoformat().replace("+00:00", "Z")
    runtime_questions = []
    for question in questions:
        sanitized_question = {k: v for k, v in question.items() if k not in answer_keys}
        qtype = normalize_question_type(sanitized_question.get("type", ""))
        if qtype == "qcm":
            runtime_questions.append({"id": sanitized_question.get("id"), "type": "qcm", "question": str(sanitized_question.get("question", "")), "choices": list(sanitized_question.get("options", []))})
        elif qtype == "vrai-faux":
            runtime_questions.append({"id": sanitized_question.get("id"), "type": "vrai-faux", "question": str(sanitized_question.get("question", ""))})
    return {"contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "type": "quiz", "level": level, "subject": subject, "description": f"Diagnostic {subject} {level} : {title}", "status": "published", "created_at": created_at, "updated_at": created_at, "source": source, "programme_ref": programme_ref}, "quiz": {"title": title, "type": "quiz", "level": level, "subject": subject, "question_count": len(runtime_questions), "passing_score": 70, "time_limit_minutes": 15, "questions": runtime_questions}, "exercisenotion": [], "exerciseresponses": []}


def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        qtype = normalize_question_type(q.get("type", ""))
        if qtype == "qcm":
            options = list(q.get("options", []))
            correct_answer = str(q.get("correct_option", ""))
            correct_index = options.index(correct_answer) if correct_answer in options else 0
            answers.append({"index": index, "question_id": index + 1, "type": "qcm", "answer": correct_answer, "correct": correct_index, "correction": str(q.get("explanation", ""))})
        elif qtype == "vrai-faux":
            answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q.get("correct") else "faux", "correction": str(q.get("explanation", ""))})
    return {"contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "level": level, "subject": subject}, "quiz": {"title": title, "question_count": len(answers), "level": level, "subject": subject, "answers": answers}}


def write_json(path, payload):
    with open(path, "w", encoding="utf-8", newline="\n") as handle:
        json.dump(payload, handle, ensure_ascii=False, indent=2)
        handle.write("\n")


def write_quiz_files():
    for directory in (GEN_QUIZ_DIR, GEN_ANSWERS_DIR, RUNTIME_QUIZ_DIR, RUNTIME_ANSWERS_DIR):
        os.makedirs(directory, exist_ok=True)
    for qid, title, subject, level, questions in quizzes_data:
        quiz = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)
        write_json(os.path.join(GEN_QUIZ_DIR, f"{qid}.json"), quiz)
        write_json(os.path.join(GEN_ANSWERS_DIR, f"{qid}.json"), answers)
        write_json(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), quiz)
        write_json(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), answers)
    print(f"{len(quizzes_data)} quiz générés dans {GEN_OUTPUT_DIR} et synchronisés vers le runtime.")


if __name__ == "__main__":
    write_quiz_files()

