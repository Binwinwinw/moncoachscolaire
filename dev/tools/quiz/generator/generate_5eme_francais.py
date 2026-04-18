#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Français 5e
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "francais_5eme_quizzes"
SUBJECT = "Français"
LEVEL = "5eme"
START_ID = 1883

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

THEMES = [
    "Classes grammaticales",
    "Fonctions dans la phrase",
    "Accords dans le groupe nominal",
    "Conjugaison du présent",
    "Temps du récit",
    "Lecture et implicite",
    "Vocabulaire et champs lexicaux",
    "Figures de style simples",
    "Dialogue et ponctuation",
    "Rédaction organisée",
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
            "question": f"{prefix} Pour réussir une activité sur '{theme}', quel réflexe est le plus utile ?",
            "options": [
                "Repérer les indices du texte et les mots-clés",
                "Choisir au hasard",
                "Éviter de relire la phrase",
                "Répondre sans justification",
            ],
            "correct_option": "Repérer les indices du texte et les mots-clés",
            "explanation": "En français, les indices présents dans la phrase ou le texte orientent l'analyse correcte.",
        },
        {
            "id": f"{quiz_id}_2",
            "type": "vrai-faux",
            "question": f"{prefix} Relire à voix basse peut aider à mieux comprendre une question sur '{theme}'.",
            "correct": True,
            "explanation": "La relecture attentive aide à mieux entendre la structure de la phrase et à clarifier le sens.",
        },
        {
            "id": f"{quiz_id}_3",
            "type": "qcm",
            "question": f"{prefix} Quelle méthode permet de progresser sur '{theme}' ?",
            "options": [
                "Comparer plusieurs exemples corrigés",
                "Apprendre sans s'entraîner",
                "Sauter les exercices d'application",
                "Écrire très vite sans vérifier",
            ],
            "correct_option": "Comparer plusieurs exemples corrigés",
            "explanation": "Comparer des exemples permet d'identifier les régularités et d'affiner la compréhension.",
        },
        {
            "id": f"{quiz_id}_4",
            "type": "vrai-faux",
            "question": f"{prefix} En français, une réponse juste sans explication suffit toujours pour montrer la maîtrise de '{theme}'.",
            "correct": False,
            "explanation": "La justification montre que la règle est comprise et peut être réutilisée dans un autre contexte.",
        },
        {
            "id": f"{quiz_id}_5",
            "type": "qcm",
            "question": f"{prefix} Quel signe montre une bonne maîtrise du thème '{theme}' ?",
            "options": [
                "Être capable d'expliquer la règle avec ses mots",
                "Répéter une réponse par cœur",
                "Ignorer les erreurs précédentes",
                "Confondre les notions proches",
            ],
            "correct_option": "Être capable d'expliquer la règle avec ses mots",
            "explanation": "Quand un élève peut reformuler la règle, il montre une compréhension plus solide.",
        },
        {
            "id": f"{quiz_id}_6",
            "type": "vrai-faux",
            "question": f"{prefix} Faire un brouillon ou surligner les indices du texte peut aider sur '{theme}'.",
            "correct": True,
            "explanation": "Le brouillon et le repérage visuel aident à structurer la réponse et à éviter les oublis.",
        },
        {
            "id": f"{quiz_id}_7",
            "type": "qcm",
            "question": f"{prefix} Si une notion de '{theme}' te semble floue, quelle réaction est la plus pertinente ?",
            "options": [
                "Reprendre un exemple puis reformuler la règle",
                "Passer à autre chose sans vérifier",
                "Inventer une règle au hasard",
                "Éviter les phrases longues seulement",
            ],
            "correct_option": "Reprendre un exemple puis reformuler la règle",
            "explanation": "La compréhension vient souvent d'un retour à un exemple concret accompagné d'une reformulation.",
        },
        {
            "id": f"{quiz_id}_8",
            "type": "vrai-faux",
            "question": f"{prefix} Corriger ses erreurs après un exercice sur '{theme}' aide à progresser plus vite.",
            "correct": True,
            "explanation": "L'analyse des erreurs renforce la mémorisation et améliore la précision future.",
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

