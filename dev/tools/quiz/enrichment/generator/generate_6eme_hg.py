#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Histoire-Géographie 6e — SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "hg_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

quizzes_data = [
    # À compléter : (id, titre, "Histoire-Géographie", "6e", [questions...])
]

def normalize_question_type(question_type):
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    return "vrai-faux"


def build_true_false_statement(question_text, fallback_answer=""):
    question_text = str(question_text).strip()
    fallback_answer = str(fallback_answer).strip().rstrip(".")
    if fallback_answer:
        return f"{question_text} La bonne réponse attendue est : {fallback_answer}."
    return question_text or "Choisis si l'affirmation est vraie ou fausse."


def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []
    for question in questions:
        qtype = normalize_question_type(question.get("type", ""))
        if qtype == "qcm":
            runtime_questions.append({"type": "qcm", "question": str(question.get("question", "")), "choices": list(question.get("options", []))})
        else:
            runtime_questions.append({"type": "vrai-faux", "question": build_true_false_statement(question.get("question", ""), question.get("correct_answer", ""))})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "type": "quiz", "level": level, "subject": subject, "description": f"Diagnostic {subject} {level} : {title}", "status": "published", "created_at": created_at, "updated_at": created_at},
        "quiz": {"title": title, "type": "quiz", "level": level, "subject": subject, "question_count": len(runtime_questions), "passing_score": 70, "time_limit_minutes": 15, "questions": runtime_questions},
        "exercisenotion": [],
        "exerciseresponses": [],
    }

def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        qtype = normalize_question_type(q.get("type", ""))
        if qtype == "qcm":
            answers.append({"index": index, "question_id": index + 1, "type": "qcm", "answer": q["correct_option"], "correction": q["explanation"]})
        else:
            answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q.get("correct", True) else "faux", "correction": q["explanation"]})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "level": level, "subject": subject},
        "quiz": {"title": title, "question_count": len(answers), "level": level, "subject": subject, "answers": answers},
    }

def write_json(path, payload):
    with open(path, "w", encoding="utf-8", newline="\n") as f:
        json.dump(payload, f, ensure_ascii=False, indent=2)
        f.write("\n")


def write_quiz_files():
    os.makedirs(QUIZ_DIR, exist_ok=True)
    os.makedirs(ANSWERS_DIR, exist_ok=True)
    os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
    os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)
    for qid, title, subject, level, questions in quizzes_data:
        quiz = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)
        write_json(os.path.join(QUIZ_DIR, f"{qid}.json"), quiz)
        write_json(os.path.join(ANSWERS_DIR, f"{qid}.json"), answers)
        write_json(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), quiz)
        write_json(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), answers)
    print(f"{len(quizzes_data)} quiz generated in {OUTPUT_DIR}")

if __name__ == "__main__":
    write_quiz_files()
