#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Générateur quiz Histoire-Géographie 4e."""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "hg_4eme_quizzes"
SUBJECT = "Histoire-Géographie"
LEVEL = "4eme"
START_ID = 1963

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

THEMES = [
    "Europe des Lumières",
    "Révolution française",
    "Révolutions industrielles",
    "Société au XIXe siècle",
    "Colonisation et empires",
    "Urbanisation",
    "Mers et océans",
    "Mobilités humaines",
    "Puissances et échanges",
    "Inégalités de développement",
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
        {"id": f"{quiz_id}_1", "type": "qcm", "question": f"{prefix} Pour traiter un document sur '{theme}', quelle démarche est la plus efficace ?", "options": ["Observer la source, la date et le contexte", "Lire seulement le titre", "Répondre sans preuve", "Ignorer les repères"], "correct_option": "Observer la source, la date et le contexte", "explanation": "La source et le contexte donnent des indices essentiels pour comprendre un document historique ou géographique."},
        {"id": f"{quiz_id}_2", "type": "vrai-faux", "question": f"{prefix} Repérer les lieux ou les repères chronologiques aide à comprendre '{theme}'.", "correct": True, "explanation": "Les repères spatiaux et temporels sont des clés de compréhension importantes."},
        {"id": f"{quiz_id}_3", "type": "qcm", "question": f"{prefix} Quelle habitude aide à progresser sur '{theme}' ?", "options": ["Comparer plusieurs cartes, textes ou images", "Retenir un seul mot sans contexte", "Éviter les documents", "Répondre vite"], "correct_option": "Comparer plusieurs cartes, textes ou images", "explanation": "Le croisement des supports permet d'affiner l'analyse et d'éviter les contresens."},
        {"id": f"{quiz_id}_4", "type": "vrai-faux", "question": f"{prefix} En histoire-géographie, une réponse sans justification suffit toujours pour traiter '{theme}'.", "correct": False, "explanation": "L'argumentation et l'appui sur des repères sont indispensables."},
        {"id": f"{quiz_id}_5", "type": "qcm", "question": f"{prefix} Quel signe montre une bonne maîtrise de '{theme}' ?", "options": ["Utiliser un vocabulaire précis et replacer le contexte", "Réciter sans comprendre", "Oublier les repères", "Répondre vaguement"], "correct_option": "Utiliser un vocabulaire précis et replacer le contexte", "explanation": "Le vocabulaire spécifique et le contexte sont au cœur du raisonnement disciplinaire."},
        {"id": f"{quiz_id}_6", "type": "vrai-faux", "question": f"{prefix} Une frise, une carte mentale ou un croquis peut aider à retenir '{theme}'.", "correct": True, "explanation": "Ces outils visuels aident à organiser les connaissances."},
        {"id": f"{quiz_id}_7", "type": "qcm", "question": f"{prefix} Si tu bloques sur une question liée à '{theme}', quel réflexe est le plus pertinent ?", "options": ["Revenir au document et relever les indices utiles", "Supprimer les repères", "Répondre au hasard", "Éviter la consigne"], "correct_option": "Revenir au document et relever les indices utiles", "explanation": "Les documents contiennent souvent les éléments de réponse lorsqu'on les lit attentivement."},
        {"id": f"{quiz_id}_8", "type": "vrai-faux", "question": f"{prefix} Corriger ses erreurs après un exercice aide à progresser sur '{theme}'.", "correct": True, "explanation": "Le retour sur erreur consolide les repères et les méthodes d'analyse."},
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

