#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Technologie 2nde — SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "technologie_2nde_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")

quizzes_data = [
    # À compléter : (id, titre, "Technologie", "2nde", [questions...])
]

def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []
    for question in questions:
        # ...existing code...
        pass
    # ...existing code...
    return {
        "id": qid,
        "title": title,
        "subject": subject,
        "level": level,
        "questions": runtime_questions,
        "created_at": created_at,
    }

def make_answers(qid, title, subject, level, questions):
    # ...existing code...
    return {}

def write_quiz_files():
    os.makedirs(QUIZ_DIR, exist_ok=True)
    os.makedirs(ANSWERS_DIR, exist_ok=True)
    for quiz in quizzes_data:
        qid, title, subject, level, questions = quiz
        quiz_obj = make_quiz(qid, title, subject, level, questions)
        answers_obj = make_answers(qid, title, subject, level, questions)
        with open(os.path.join(QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8") as f:
            json.dump(quiz_obj, f, ensure_ascii=False, indent=2)
        with open(os.path.join(ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8") as f:
            json.dump(answers_obj, f, ensure_ascii=False, indent=2)

if __name__ == "__main__":
    write_quiz_files()
