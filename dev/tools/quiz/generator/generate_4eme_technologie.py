#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Générateur quiz Technologie 4e."""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "technologie_4eme_quizzes"
SUBJECT = "Technologie"
LEVEL = "4eme"
START_ID = 1983

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

THEMES = [
    "Algorithmique simple",
    "Capteurs et actionneurs",
    "Programmation visuelle",
    "Objets connectés",
    "Réseaux et communication",
    "Modélisation d'un objet",
    "Choix des matériaux",
    "Conception d'un prototype",
    "Impact environnemental",
    "Organisation d'un projet technique",
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
        {"id": f"{quiz_id}_1", "type": "qcm", "question": f"{prefix} Pour le thème '{theme}', quelle démarche est la plus utile ?", "options": ["Identifier la fonction, les contraintes et les solutions", "Répondre au hasard", "Ignorer le besoin", "Supprimer les schémas"], "correct_option": "Identifier la fonction, les contraintes et les solutions", "explanation": "En technologie, la compréhension d'un système part du besoin et des solutions retenues."},
        {"id": f"{quiz_id}_2", "type": "vrai-faux", "question": f"{prefix} Un schéma ou un organigramme peut aider à comprendre '{theme}'.", "correct": True, "explanation": "Les représentations visuelles rendent les fonctions et étapes plus lisibles."},
        {"id": f"{quiz_id}_3", "type": "qcm", "question": f"{prefix} Quelle habitude aide à progresser sur '{theme}' ?", "options": ["Comparer plusieurs solutions techniques", "Choisir une réponse sans preuve", "Éviter la correction", "Oublier la fonction d'usage"], "correct_option": "Comparer plusieurs solutions techniques", "explanation": "Comparer les solutions permet de comprendre leurs avantages et leurs limites."},
        {"id": f"{quiz_id}_4", "type": "vrai-faux", "question": f"{prefix} Une réponse sans justification suffit toujours pour montrer la maîtrise de '{theme}'.", "correct": False, "explanation": "L'explication du fonctionnement ou du choix technique est essentielle."},
        {"id": f"{quiz_id}_5", "type": "qcm", "question": f"{prefix} Quel signe montre une bonne maîtrise de '{theme}' ?", "options": ["Expliquer le rôle d'un élément ou d'une étape", "Réciter un mot isolé", "Ignorer les contraintes", "Répondre très vite"], "correct_option": "Expliquer le rôle d'un élément ou d'une étape", "explanation": "La maîtrise se voit dans la capacité à relier un élément à sa fonction."},
        {"id": f"{quiz_id}_6", "type": "vrai-faux", "question": f"{prefix} Corriger ses erreurs peut aider à mieux comprendre '{theme}'.", "correct": True, "explanation": "Le retour sur erreur consolide la logique technique et la méthode."},
        {"id": f"{quiz_id}_7", "type": "qcm", "question": f"{prefix} Si tu bloques sur une question liée à '{theme}', quel réflexe est le plus pertinent ?", "options": ["Revenir au schéma ou au document technique", "Abandonner", "Répondre sans lire", "Ignorer les composants"], "correct_option": "Revenir au schéma ou au document technique", "explanation": "Le document technique contient souvent les éléments qui permettent de débloquer le raisonnement."},
        {"id": f"{quiz_id}_8", "type": "vrai-faux", "question": f"{prefix} Réviser, comparer et reformuler aide à progresser sur '{theme}'.", "correct": True, "explanation": "Cette méthode améliore la compréhension des systèmes et des projets techniques."},
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

