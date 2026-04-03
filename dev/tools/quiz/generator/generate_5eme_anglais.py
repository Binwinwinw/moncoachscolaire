#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Anglais 5e — SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "anglais_5eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")

quizzes_data = [
    # À compléter : (id, titre, "Anglais", "5e", [questions...])
]

def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []
    for question in questions:
        qtype = str(question.get("type", "texte"))
        if qtype == "qcm":
            runtime_questions.append({"type": "qcm", "question": str(question.get("question", "")), "choices": list(question.get("options", []))})
        elif qtype == "vrai-faux":
            runtime_questions.append({"type": "vrai-faux", "question": str(question.get("question", ""))})
        else:
            runtime_questions.append({"type": "open", "question": str(question.get("question", ""))})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "type": "quiz", "level": level, "subject": subject, "description": f"Diagnostic {subject} {level} : {title}", "status": "published", "created_at": created_at, "updated_at": created_at},
        "quiz": {"title": title, "type": "quiz", "level": level, "subject": subject, "question_count": len(runtime_questions), "passing_score": 70, "time_limit_minutes": 15, "questions": runtime_questions},
        "exercisenotion": [],
        "exerciseresponses": [],
    }

def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        if q["type"] == "qcm":
            answers.append({"index": index, "question_id": index + 1, "type": "qcm", "answer": q["correct_option"], "correction": q["explanation"]})
        elif q["type"] == "vrai-faux":
            answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q["correct"] else "faux", "correction": q["explanation"]})
        else:
            answers.append({"index": index, "question_id": index + 1, "type": "open", "answer": q["correct_answer"], "correction": q["explanation"]})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "level": level, "subject": subject},
        "quiz": {"title": title, "question_count": len(answers), "level": level, "subject": subject, "answers": answers},
    }

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

if __name__ == "__main__":
    write_quiz_files()
