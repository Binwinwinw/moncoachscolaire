#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz EMC 5e
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "emc_5eme_quizzes"
SUBJECT = "EMC"
LEVEL = "5eme"
START_ID = 1903

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

THEMES = [
    "Droits et devoirs du collégien",
    "Respect d'autrui",
    "Lutte contre le harcèlement",
    "Égalité filles-garçons",
    "Citoyenneté numérique",
    "Laïcité à l'école",
    "Solidarité et entraide",
    "Justice et règles communes",
    "Liberté d'expression et respect",
    "Engagement collectif",
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
        {
            "id": f"{quiz_id}_1",
            "type": "qcm",
            "question": f"{prefix} Face à une situation liée à '{theme}', quelle attitude est la plus responsable ?",
            "options": [
                "Respecter les règles et dialoguer calmement",
                "Se moquer des autres",
                "Refuser toute discussion",
                "Partager une information sans vérifier",
            ],
            "correct_option": "Respecter les règles et dialoguer calmement",
            "explanation": "Le respect des règles et du dialogue fait partie des attitudes citoyennes attendues en EMC.",
        },
        {
            "id": f"{quiz_id}_2",
            "type": "vrai-faux",
            "question": f"{prefix} En EMC, écouter le point de vue d'autrui aide à mieux traiter un sujet sur '{theme}'.",
            "correct": True,
            "explanation": "L'écoute et l'argumentation favorisent une compréhension nuancée des situations.",
        },
        {
            "id": f"{quiz_id}_3",
            "type": "qcm",
            "question": f"{prefix} Quelle habitude aide à progresser sur '{theme}' ?",
            "options": [
                "Justifier son avis avec des exemples précis",
                "Imposer son opinion sans argument",
                "Éviter les débats",
                "Confondre liberté et absence de règles",
            ],
            "correct_option": "Justifier son avis avec des exemples précis",
            "explanation": "En EMC, les arguments concrets et justifiés permettent de construire un raisonnement solide.",
        },
        {
            "id": f"{quiz_id}_4",
            "type": "vrai-faux",
            "question": f"{prefix} On peut progresser sur '{theme}' sans jamais revenir sur ses erreurs ou ses maladresses.",
            "correct": False,
            "explanation": "Le retour sur ses erreurs aide à mieux comprendre les règles communes et à ajuster son comportement.",
        },
        {
            "id": f"{quiz_id}_5",
            "type": "qcm",
            "question": f"{prefix} Quel signe montre une bonne maîtrise du thème '{theme}' ?",
            "options": [
                "Savoir distinguer un droit, un devoir et une responsabilité",
                "Refuser toute règle commune",
                "Parler plus fort que les autres",
                "Donner un avis sans raison",
            ],
            "correct_option": "Savoir distinguer un droit, un devoir et une responsabilité",
            "explanation": "Comprendre ces distinctions est central dans l'apprentissage de l'EMC.",
        },
        {
            "id": f"{quiz_id}_6",
            "type": "vrai-faux",
            "question": f"{prefix} Rechercher une solution collective peut aider à mieux résoudre une situation liée à '{theme}'.",
            "correct": True,
            "explanation": "L'EMC valorise la coopération, la médiation et la recherche de solutions communes.",
        },
        {
            "id": f"{quiz_id}_7",
            "type": "qcm",
            "question": f"{prefix} Si une question sur '{theme}' te semble délicate, quel réflexe est le plus pertinent ?",
            "options": [
                "Revenir aux principes de respect, de droit et de responsabilité",
                "Choisir une réponse provocante",
                "Ignorer les conséquences",
                "Écarter tout échange",
            ],
            "correct_option": "Revenir aux principes de respect, de droit et de responsabilité",
            "explanation": "Ces repères aident à analyser les situations de manière juste et réfléchie.",
        },
        {
            "id": f"{quiz_id}_8",
            "type": "vrai-faux",
            "question": f"{prefix} Discuter, argumenter et coopérer font partie des compétences utiles sur '{theme}'.",
            "correct": True,
            "explanation": "L'argumentation et la coopération sont au cœur des apprentissages en EMC.",
        },
    ]


quizzes_data = [
    (
        START_ID + offset,
        f"{SUBJECT} 5eme - {theme}",
        SUBJECT,
        LEVEL,
        build_questions(theme, START_ID + offset),
    )
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
            runtime_questions.append(
                {
                    "id": sanitized_question.get("id"),
                    "type": "qcm",
                    "question": str(sanitized_question.get("question", "")),
                    "choices": list(sanitized_question.get("options", [])),
                }
            )
        elif qtype == "vrai-faux":
            runtime_questions.append(
                {
                    "id": sanitized_question.get("id"),
                    "type": "vrai-faux",
                    "question": str(sanitized_question.get("question", "")),
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
            "source": source,
            "programme_ref": programme_ref,
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
    for index, question in enumerate(questions):
        qtype = normalize_question_type(question.get("type", ""))
        if qtype == "qcm":
            options = list(question.get("options", []))
            correct_answer = str(question.get("correct_option", ""))
            correct_index = options.index(correct_answer) if correct_answer in options else 0
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "qcm",
                    "answer": correct_answer,
                    "correct": correct_index,
                    "correction": str(question.get("explanation", "")),
                }
            )
        elif qtype == "vrai-faux":
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "vrai-faux",
                    "answer": "vrai" if question.get("correct") else "faux",
                    "correction": str(question.get("explanation", "")),
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

