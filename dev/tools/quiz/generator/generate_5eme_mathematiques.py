#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Mathématiques 5e
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "mathematiques_5eme_quizzes"
SUBJECT = "Mathématiques"
LEVEL = "5eme"
START_ID = 1873

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

THEMES = [
    "Nombres relatifs",
    "Fractions et comparaisons",
    "Proportionnalité",
    "Triangles et quadrilatères",
    "Angles et constructions",
    "Symétrie centrale",
    "Aires et périmètres",
    "Organisation de données",
    "Calcul numérique",
    "Solides et volumes",
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
            "question": f"{prefix} Quelle démarche aide le plus à réussir un exercice sur '{theme}' ?",
            "options": [
                "Repérer les mots-clés et la notion visée",
                "Répondre au hasard",
                "Sauter la consigne",
                "Copier sans vérifier",
            ],
            "correct_option": "Repérer les mots-clés et la notion visée",
            "explanation": "Analyser la consigne permet de choisir la bonne méthode et d'éviter les erreurs de compréhension.",
        },
        {
            "id": f"{quiz_id}_2",
            "type": "vrai-faux",
            "question": f"{prefix} En '{theme}', relire le calcul ou la figure avant de valider améliore la fiabilité de la réponse.",
            "correct": True,
            "explanation": "Une relecture finale aide à repérer les oublis, les signes erronés et les imprécisions.",
        },
        {
            "id": f"{quiz_id}_3",
            "type": "qcm",
            "question": f"{prefix} Pour progresser sur '{theme}', quelle habitude est la plus utile ?",
            "options": [
                "Refaire un exemple corrigé puis s'entraîner seul",
                "Attendre la correction sans chercher",
                "Apprendre une formule sans la comprendre",
                "Éviter les questions difficiles",
            ],
            "correct_option": "Refaire un exemple corrigé puis s'entraîner seul",
            "explanation": "Le passage guidé vers l'autonomie consolide la compréhension et la méthode.",
        },
        {
            "id": f"{quiz_id}_4",
            "type": "vrai-faux",
            "question": f"{prefix} En mathématiques, expliquer sa démarche sur '{theme}' est inutile si le résultat semble juste.",
            "correct": False,
            "explanation": "La démarche est essentielle : elle permet de vérifier le raisonnement et de comprendre l'origine d'une erreur.",
        },
        {
            "id": f"{quiz_id}_5",
            "type": "qcm",
            "question": f"{prefix} Quel indicateur montre une bonne maîtrise du thème '{theme}' ?",
            "options": [
                "Savoir justifier chaque étape du raisonnement",
                "Donner une réponse très rapide",
                "Réciter sans exemple",
                "Changer de méthode à chaque ligne",
            ],
            "correct_option": "Savoir justifier chaque étape du raisonnement",
            "explanation": "Justifier les étapes montre que la notion et la méthode sont réellement comprises.",
        },
        {
            "id": f"{quiz_id}_6",
            "type": "vrai-faux",
            "question": f"{prefix} Faire une fiche avec les règles clés de '{theme}' puis s'exercer aide à mémoriser durablement.",
            "correct": True,
            "explanation": "La mémorisation est plus solide lorsqu'elle est associée à des applications concrètes.",
        },
        {
            "id": f"{quiz_id}_7",
            "type": "qcm",
            "question": f"{prefix} Si tu bloques sur une question liée à '{theme}', quel réflexe est le plus pertinent ?",
            "options": [
                "Identifier l'étape bloquante puis reprendre calmement",
                "Abandonner immédiatement",
                "Réécrire l'énoncé sans réfléchir",
                "Choisir la réponse la plus longue",
            ],
            "correct_option": "Identifier l'étape bloquante puis reprendre calmement",
            "explanation": "Repérer précisément le blocage permet de relancer la résolution de manière efficace.",
        },
        {
            "id": f"{quiz_id}_8",
            "type": "vrai-faux",
            "question": f"{prefix} Alterner entraînement, correction et reprise des erreurs est une bonne stratégie sur '{theme}'.",
            "correct": True,
            "explanation": "Le trio entraînement-correction-reprise est un levier robuste de progression.",
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

