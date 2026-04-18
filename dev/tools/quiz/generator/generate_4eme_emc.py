#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Générateur quiz EMC 4e."""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "emc_4eme_quizzes"
SUBJECT = "EMC"
LEVEL = "4eme"
START_ID = 1943

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

THEMES = [
    "Libertés et règles",
    "Égalité et discriminations",
    "Usage responsable des réseaux",
    "Prévention des violences",
    "Engagement citoyen",
    "Justice des mineurs",
    "Débattre avec respect",
    "Laïcité et vivre ensemble",
    "Solidarité au collège",
    "Responsabilité individuelle et collective",
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
        {"id": f"{quiz_id}_1", "type": "qcm", "question": f"{prefix} Face à un sujet lié à '{theme}', quelle attitude est la plus responsable ?", "options": ["Écouter, argumenter et respecter les règles", "Se moquer des autres", "Refuser tout dialogue", "Ignorer les conséquences"], "correct_option": "Écouter, argumenter et respecter les règles", "explanation": "En EMC, l'écoute, l'argumentation et le respect sont essentiels."},
        {"id": f"{quiz_id}_2", "type": "vrai-faux", "question": f"{prefix} Comprendre les droits et les devoirs aide à mieux traiter '{theme}'.", "correct": True, "explanation": "Les notions de droit et de devoir structurent la réflexion citoyenne."},
        {"id": f"{quiz_id}_3", "type": "qcm", "question": f"{prefix} Quelle habitude aide à progresser sur '{theme}' ?", "options": ["Justifier son point de vue avec des exemples", "Imposer son avis sans argument", "Éviter tout débat", "Choisir une réponse provocante"], "correct_option": "Justifier son point de vue avec des exemples", "explanation": "Les exemples concrets rendent un raisonnement plus solide et plus nuancé."},
        {"id": f"{quiz_id}_4", "type": "vrai-faux", "question": f"{prefix} On peut traiter correctement '{theme}' sans réfléchir aux conséquences pour les autres.", "correct": False, "explanation": "La prise en compte d'autrui est au cœur des apprentissages d'EMC."},
        {"id": f"{quiz_id}_5", "type": "qcm", "question": f"{prefix} Quel signe montre une bonne maîtrise du thème '{theme}' ?", "options": ["Distinguer une liberté, une règle et une responsabilité", "Refuser toute règle", "Parler plus fort que les autres", "Donner un avis sans raison"], "correct_option": "Distinguer une liberté, une règle et une responsabilité", "explanation": "Cette distinction aide à analyser les situations de manière claire et juste."},
        {"id": f"{quiz_id}_6", "type": "vrai-faux", "question": f"{prefix} Revenir sur une erreur de jugement peut aider à progresser sur '{theme}'.", "correct": True, "explanation": "L'analyse d'une erreur ou d'un malentendu permet d'ajuster son raisonnement."},
        {"id": f"{quiz_id}_7", "type": "qcm", "question": f"{prefix} Si une situation liée à '{theme}' est complexe, quel réflexe est le plus pertinent ?", "options": ["Revenir aux principes de respect et de responsabilité", "Choisir l'avis le plus sévère", "Écarter le débat", "Rester sur une impression"], "correct_option": "Revenir aux principes de respect et de responsabilité", "explanation": "Ces repères permettent de structurer un jugement citoyen équilibré."},
        {"id": f"{quiz_id}_8", "type": "vrai-faux", "question": f"{prefix} Débattre calmement et corriger ses erreurs aide à progresser sur '{theme}'.", "correct": True, "explanation": "L'échange argumenté et le retour sur erreur renforcent l'apprentissage citoyen."},
    ]


quizzes_data = [(START_ID + offset, f"{SUBJECT} 4eme - {theme}", SUBJECT, LEVEL, build_questions(theme, START_ID + offset)) for offset, theme in enumerate(THEMES)]


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

