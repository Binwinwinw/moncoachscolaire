#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Générateur quiz Anglais 3e."""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "anglais_3eme_quizzes"
SUBJECT = "Anglais"
LEVEL = "3eme"
START_ID = 2003

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

THEMES = [
    "Present perfect",
    "Passive voice",
    "Reported speech",
    "Expressing opinion",
    "Future forms",
    "Conditional sentences",
    "Media and technology",
    "Travel and experiences",
    "Environmental issues",
    "Job orientation",
]


def normalize_question_type(question_type: str) -> str:
    qt = str(question_type).strip().lower()
    if qt in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if qt == "qcm":
        return "qcm"
    return "open"


def build_questions(theme: str, quiz_id: int) -> list[dict]:
    prefix = f"[{SUBJECT} {LEVEL}]"
    return [
        {"id": f"{quiz_id}_1", "type": "qcm", "question": f"{prefix} For the topic '{theme}', what is the best first step in a diagnostic quiz?", "options": ["Identify the grammar clue and read the context", "Answer randomly", "Ignore the verb", "Skip the sentence"], "correct_option": "Identify the grammar clue and read the context", "explanation": "The context and grammar clue often indicate the expected tense or structure."},
        {"id": f"{quiz_id}_2", "type": "vrai-faux", "question": f"{prefix} Re-reading a sentence carefully can help you answer correctly on '{theme}'.", "correct": True, "explanation": "A careful reading helps detect tense markers, pronouns or modal verbs."},
        {"id": f"{quiz_id}_3", "type": "qcm", "question": f"{prefix} What habit helps you improve on '{theme}'?", "options": ["Practise short examples and correct them", "Memorise answers without context", "Avoid the correction", "Never review mistakes"], "correct_option": "Practise short examples and correct them", "explanation": "Short repeated practice in context is efficient in 3e English."},
        {"id": f"{quiz_id}_4", "type": "vrai-faux", "question": f"{prefix} Explaining why an answer is correct can help you master '{theme}'.", "correct": True, "explanation": "Justifying the rule reinforces long-term understanding."},
        {"id": f"{quiz_id}_5", "type": "qcm", "question": f"{prefix} Which sign shows a good mastery of '{theme}'?", "options": ["Using the notion in a complete sentence", "Choosing every answer at random", "Skipping the context", "Changing the rule each time"], "correct_option": "Using the notion in a complete sentence", "explanation": "Reusing the structure in context shows real understanding."},
        {"id": f"{quiz_id}_6", "type": "vrai-faux", "question": f"{prefix} Looking for time markers and pronouns can help on '{theme}'.", "correct": True, "explanation": "These markers guide the grammatical choice in many questions."},
        {"id": f"{quiz_id}_7", "type": "qcm", "question": f"{prefix} If a question on '{theme}' seems difficult, what is the best reaction?", "options": ["Go back to the sentence and find useful clues", "Answer immediately without reading", "Ignore the context", "Leave the page"], "correct_option": "Go back to the sentence and find useful clues", "explanation": "The sentence usually contains the clues needed to solve the problem."},
        {"id": f"{quiz_id}_8", "type": "vrai-faux", "question": f"{prefix} Correcting your mistakes after practice is a good strategy for '{theme}'.", "correct": True, "explanation": "Error analysis is one of the fastest ways to improve accuracy."},
    ]


quizzes_data = [(START_ID + offset, f"{SUBJECT} 3eme - {theme}", SUBJECT, LEVEL, build_questions(theme, START_ID + offset)) for offset, theme in enumerate(THEMES)]


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

