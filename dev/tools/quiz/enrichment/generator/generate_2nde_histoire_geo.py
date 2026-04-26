#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Template IA â€“ generate_<niveau>_<matiere>.py
"""

from __future__ import annotations

import argparse
import json
import os
from datetime import UTC, datetime
from pathlib import Path

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

# Ã€ adapter dans chaque clone
BASENAME = "histoire_geo_2nde_quizzes"

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")

RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

os.makedirs(GEN_QUIZ_DIR, exist_ok=True)
os.makedirs(GEN_ANSWERS_DIR, exist_ok=True)
os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)


def fix_mojibake_text(value):
    if not isinstance(value, str):
        return value
    markers = ("Ãƒ", "Ã‚", "Ã¢â‚¬", "Ã¢â‚¬â„¢", "Ã¢â‚¬Å“", "Ã¢â‚¬â€", "Ã¢â‚¬â€œ", "Ã…")
    if not any(marker in value for marker in markers):
        return value
    for legacy_encoding in ("latin-1", "cp1252"):
        try:
            repaired = value.encode(legacy_encoding).decode("utf-8")
            if repaired != value:
                value = repaired
        except UnicodeError:
            continue
    replacements = {
        "ÃƒÂ©": "Ã©", "ÃƒÂ¨": "Ã¨", "ÃƒÂª": "Ãª", "ÃƒÂ«": "Ã«", "ÃƒÂ ": "Ã ", "ÃƒÂ¢": "Ã¢",
        "ÃƒÂ´": "Ã´", "ÃƒÂ»": "Ã»", "ÃƒÂ¹": "Ã¹", "ÃƒÂ®": "Ã®", "ÃƒÂ¯": "Ã¯", "ÃƒÂ§": "Ã§",
        "Ãƒâ€°": "Ã‰", "Ãƒâ‚¬": "Ã€", "Ãƒâ€¡": "Ã‡", "Ã…â€œ": "Å“", "Ã‚": "", "Ã¢â‚¬â„¢": "â€™",
        "Ã¢â‚¬Å“": "â€œ", "Ã¢â‚¬\x9d": "â€", "Ã¢â‚¬â€œ": "â€“", "Ã¢â‚¬â€": "â€”", "Ã¢â‚¬Â¦": "â€¦",
    }
    for source, target in replacements.items():
        value = value.replace(source, target)
    return value


def normalize_text_payload(payload):
    if isinstance(payload, dict):
        return {k: normalize_text_payload(v) for k, v in payload.items()}
    if isinstance(payload, list):
        return [normalize_text_payload(x) for x in payload]
    if isinstance(payload, tuple):
        return tuple(normalize_text_payload(x) for x in payload)
    if isinstance(payload, str):
        return fix_mojibake_text(payload)
    return payload


def normalize_question_type(question_type):
    qt = str(question_type or "").strip().lower().replace("_", "-")
    if qt in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if qt == "qcm":
        return "qcm"
    raise ValueError(
        f"Type de question invalide ou interdit : '{question_type}'. Seuls 'qcm' et 'vrai-faux' sont autorisés."
    )


def validate_question_type(question):
    raw_type = question.get("type", "")
    normalize_question_type(raw_type)
    return True


def normalize_subject_label(subject):
    normalized = fix_mojibake_text(subject).strip().lower()
    if normalized in {"histoire-geographie", "histoire géographie", "histoire-géographie"}:
        return "Histoire-Géographie"
    return fix_mojibake_text(subject).strip()


def make_quiz(qid, title, subject, level, questions,
              source="Eduscol + BOEN",
              programme_ref=""):
    subject = normalize_subject_label(subject)
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    quiz_questions = []
    for question in questions:
        raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
        validate_question_type(question)
        if qtype == "qcm":
            sanitized = {
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            }
        elif qtype == "vrai-faux":
            sanitized = {
                "type": "vrai-faux",
                "question": str(question.get("question", "")),
            }
        else:
            raise ValueError(f"Type de question non supporté après normalisation : {qtype}")
        quiz_questions.append(sanitized)

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - SÃ©rie {qid}",
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
            "question_count": len(quiz_questions),
            "passing_score": 70,
            "time_limit_minutes": 15,
            "questions": quiz_questions,
        },
        "exercisenotion": [],
        "exerciseresponses": [],
    }


def make_answers(qid, title, subject, level, questions):
    subject = normalize_subject_label(subject)
    answers = []
    for index, q in enumerate(questions):
        raw_type = str(q.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
        validate_question_type(q)
        if qtype == "qcm":
            options = list(q.get("options", []))
            correct_answer = str(q.get("correct_option", q.get("correct_answer", "")))
            correct_index = options.index(correct_answer) if correct_answer in options else 0
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "qcm",
                "answer": correct_answer,
                "correct": correct_index,
                "correction": str(q.get("explanation", "")),
            })
        elif qtype == "vrai-faux":
            tf_source = q.get("correct", q.get("correct_answer", "faux"))
            tf_answer = "vrai" if str(tf_source).strip().lower() in {"true", "vrai", "1"} else "faux"
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": tf_answer,
                "correction": str(q.get("explanation", "")),
            })
        else:
            raise ValueError(f"Type de question non supporté dans make_answers : {qtype}")

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - SÃ©rie {qid}",
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


def count_question_types(questions):
    counts = {"qcm": 0, "vrai-faux": 0, "other": 0}
    for question in questions:
        raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
        if raw_type in {"vrai-faux", "vrai faux"}:
            counts["vrai-faux"] += 1
        elif raw_type == "qcm":
            counts["qcm"] += 1
        else:
            counts["other"] += 1
    return counts


quizzes_data = [
    (
        1692,
        'Histoire-Geographie 2nde - Reperes chronologiques',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1692_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Reperes chronologiques', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1692_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Reperes chronologiques', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1692_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Reperes chronologiques'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1692_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Reperes chronologiques' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1692_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1692_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Reperes chronologiques'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1692_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Reperes chronologiques' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1692_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Reperes chronologiques'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1693,
        'Histoire-Geographie 2nde - Acteurs et evenements',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1693_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Acteurs et evenements', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1693_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Acteurs et evenements', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1693_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Acteurs et evenements'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1693_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Acteurs et evenements' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1693_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1693_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Acteurs et evenements'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1693_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Acteurs et evenements' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1693_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Acteurs et evenements'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1694,
        'Histoire-Geographie 2nde - Espaces productifs',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1694_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Espaces productifs', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1694_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Espaces productifs', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1694_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Espaces productifs'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1694_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Espaces productifs' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1694_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1694_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Espaces productifs'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1694_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Espaces productifs' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1694_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Espaces productifs'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1695,
        'Histoire-Geographie 2nde - Dynamiques territoriales',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1695_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Dynamiques territoriales', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1695_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Dynamiques territoriales', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1695_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Dynamiques territoriales'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1695_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Dynamiques territoriales' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1695_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1695_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Dynamiques territoriales'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1695_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Dynamiques territoriales' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1695_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Dynamiques territoriales'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1696,
        'Histoire-Geographie 2nde - Puissances et influences',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1696_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Puissances et influences', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1696_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Puissances et influences', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1696_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Puissances et influences'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1696_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Puissances et influences' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1696_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1696_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Puissances et influences'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1696_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Puissances et influences' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1696_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Puissances et influences'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1697,
        'Histoire-Geographie 2nde - Developpement durable',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1697_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Developpement durable', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1697_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Developpement durable', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1697_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Developpement durable'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1697_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Developpement durable' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1697_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1697_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Developpement durable'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1697_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Developpement durable' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1697_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Developpement durable'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1698,
        'Histoire-Geographie 2nde - Etude de documents',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1698_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Etude de documents', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1698_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Etude de documents', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1698_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Etude de documents'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1698_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Etude de documents' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1698_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1698_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Etude de documents'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1698_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Etude de documents' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1698_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Etude de documents'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1699,
        'Histoire-Geographie 2nde - Methodes de composition',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1699_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Methodes de composition', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1699_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Methodes de composition', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1699_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Methodes de composition'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1699_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Methodes de composition' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1699_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1699_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Methodes de composition'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1699_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Methodes de composition' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1699_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Methodes de composition'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1700,
        'Histoire-Geographie 2nde - Cartographie',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1700_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Cartographie', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1700_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Cartographie', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1700_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Cartographie'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1700_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Cartographie' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1700_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1700_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Cartographie'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1700_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Cartographie' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1700_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Cartographie'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1701,
        'Histoire-Geographie 2nde - Analyse critique des sources',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1701_1',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Sur le theme 'Analyse critique des sources', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
                'options': [
                    "Relire la consigne et identifier l'objectif",
                    'Repondre vite sans verifier',
                    'Ignorer le contexte',
                    'Memoriser sans comprendre'
                ],
                'correct_option': "Relire la consigne et identifier l'objectif",
                'explanation': "Identifier l'objectif de la consigne permet de mobiliser la bonne methode."
            },
            {
                'id': '1701_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Analyse critique des sources', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1701_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Analyse critique des sources'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1701_4',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quelle action favorise la memorisation durable du theme 'Analyse critique des sources' ?",
                'options': [
                    'Faire des rappels espaces',
                    'Tout revoir une seule fois',
                    'Copier sans comprendre',
                    'Eviter les exercices'
                ],
                'correct_option': 'Faire des rappels espaces',
                'explanation': 'Les rappels espaces consolidant la memoire a long terme.'
            },
            {
                'id': '1701_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1701_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Analyse critique des sources'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1701_7',
                'type': 'qcm',
                'question': "[Histoire-Geographie 2nde] Quel indicateur montre une bonne maitrise du theme 'Analyse critique des sources' ?",
                'options': [
                    'Expliquer clairement la demarche',
                    'Donner une reponse au hasard',
                    'Eviter les notions difficiles',
                    'Memoriser sans application'
                ],
                'correct_option': 'Expliquer clairement la demarche',
                'explanation': 'Savoir expliquer la demarche prouve une comprehension solide.'
            },
            {
                'id': '1701_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Analyse critique des sources'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    )
]

HG2NDE_COMPLEMENT_SPECS = [
    (1692, "Histoire-Géographie 2nde - Cartes et échelles", "les cartes et les échelles"),
    (1693, "Histoire-Géographie 2nde - Repères spatiaux", "les repères spatiaux"),
    (1694, "Histoire-Géographie 2nde - Lire un document historique", "la lecture d'un document historique"),
    (1695, "Histoire-Géographie 2nde - Situer un événement", "la chronologie"),
    (1696, "Histoire-Géographie 2nde - Décrire un paysage", "la description d'un paysage"),
    (1697, "Histoire-Géographie 2nde - Comprendre une carte thématique", "la carte thématique"),
    (1698, "Histoire-Géographie 2nde - Identifier un acteur historique", "les acteurs historiques"),
    (1699, "Histoire-Géographie 2nde - Les territoires de proximité", "les territoires"),
    (1700, "Histoire-Géographie 2nde - Les mobilités", "les mobilités"),
    (1701, "Histoire-Géographie 2nde - Urbanisation et métropoles", "l'urbanisation"),
    (1702, "Histoire-Géographie 2nde - Ressources et développement", "les ressources"),
    (1703, "Histoire-Géographie 2nde - Les littoraux", "les littoraux"),
    (1704, "Histoire-Géographie 2nde - Les espaces ruraux", "les espaces ruraux"),
    (1705, "Histoire-Géographie 2nde - Les espaces productifs", "les espaces productifs"),
    (1706, "Histoire-Géographie 2nde - Frontières et échanges", "les frontières"),
    (1707, "Histoire-Géographie 2nde - Le développement durable", "le développement durable"),
    (1708, "Histoire-Géographie 2nde - Les sociétés face aux risques", "les risques"),
    (1709, "Histoire-Géographie 2nde - Raconter et expliquer", "l'explication historique"),
    (1710, "Histoire-Géographie 2nde - Analyser une source", "l'analyse critique des sources"),
    (1711, "Histoire-Géographie 2nde - Comprendre un graphique", "la lecture de graphique"),
    (1712, "Histoire-Géographie 2nde - Comprendre un tableau", "la lecture de tableau"),
    (1713, "Histoire-Géographie 2nde - Développement et inégalités", "les inégalités"),
    (1714, "Histoire-Géographie 2nde - Population et dynamiques", "les dynamiques de population"),
    (1715, "Histoire-Géographie 2nde - Mondialisation des échanges", "la mondialisation"),
    (1716, "Histoire-Géographie 2nde - Habiter une métropole", "les métropoles"),
    (1717, "Histoire-Géographie 2nde - Conflits d'usage", "les conflits d'usage"),
    (1718, "Histoire-Géographie 2nde - Patrimoine et mémoire", "le patrimoine"),
    (1719, "Histoire-Géographie 2nde - Révision générale", "la révision générale"),
]


def build_2nde_hg_complement(qid, title, focus):
    return (
        qid,
        title,
        "Histoire-Géographie",
        "2nde",
        [
            {"id": f"{qid}_1", "type": "qcm", "question": f"En histoire-géographie, pourquoi travaille-t-on {focus} ?", "options": ["Pour mieux comprendre les sociétés, les territoires et les documents", "Pour faire uniquement des calculs", "Pour éviter les documents", "Pour apprendre sans réflexion"], "correct_option": "Pour mieux comprendre les sociétés, les territoires et les documents", "explanation": "L'histoire-géographie apprend à situer, expliquer, comparer et interpréter des faits et des espaces."},
            {"id": f"{qid}_2", "type": "vrai-faux", "question": f"{focus.capitalize()} peut être étudié à partir de cartes, textes, images ou graphiques.", "correct": True, "explanation": "Les documents variés sont au cœur du travail en histoire-géographie."},
            {"id": f"{qid}_3", "type": "qcm", "question": f"Quelle méthode aide à réussir sur {focus} ?", "options": ["Repérer les informations utiles et les relier au cours", "Répondre au hasard", "Ignorer la légende", "Ne jamais justifier"], "correct_option": "Repérer les informations utiles et les relier au cours", "explanation": "Il faut sélectionner les indices pertinents du document et les relier aux notions du programme."},
            {"id": f"{qid}_4", "type": "vrai-faux", "question": "Justifier sa réponse avec un document ou un exemple renforce l'analyse.", "correct": True, "explanation": "L'appui sur des indices précis montre que la réponse repose sur une vraie analyse."},
            {"id": f"{qid}_5", "type": "qcm", "question": f"Quel est l'objectif d'un exercice sur {focus} ?", "options": ["Comprendre et expliquer une situation historique ou géographique", "Mémoriser sans vérifier", "Réciter sans contexte", "Éviter toute interprétation"], "correct_option": "Comprendre et expliquer une situation historique ou géographique", "explanation": "L'objectif est d'interpréter des faits ou des territoires de manière claire et argumentée."},
            {"id": f"{qid}_6", "type": "vrai-faux", "question": f"Observer attentivement le document aide à mieux maîtriser {focus}.", "correct": True, "explanation": "Une observation rigoureuse permet d'éviter les contresens et de construire une réponse pertinente."},
            {"id": f"{qid}_7", "type": "qcm", "question": f"Quelle pratique est la plus utile pour progresser sur {focus} ?", "options": ["S'entraîner, corriger et reformuler", "Ne jamais relire", "Répondre sans document", "Aller le plus vite possible"], "correct_option": "S'entraîner, corriger et reformuler", "explanation": "La progression vient de l'entraînement régulier et de la reprise des erreurs."},
            {"id": f"{qid}_8", "type": "vrai-faux", "question": "En histoire-géographie, une réponse claire, organisée et justifiée est essentielle.", "correct": True, "explanation": "Une bonne réponse doit être compréhensible, précise et appuyée sur des éléments du cours ou du document."},
        ],
    )


quizzes_data.extend(build_2nde_hg_complement(*spec) for spec in HG2NDE_COMPLEMENT_SPECS)


def write_quiz_files():
    count = 0
    total_counts = {"qcm": 0, "vrai-faux": 0, "texte": 0, "other": 0}
    for qid, title, subject, level, questions in quizzes_data:
        counts = count_question_types(questions)
        total_counts["qcm"] += counts["qcm"]
        total_counts["vrai-faux"] += counts["vrai-faux"]
        total_counts["texte"] += counts["texte"]
        total_counts["other"] += counts["other"]
        if counts["texte"]:
            print(f"  [AUDIT] {qid}.json contient {counts['texte']} question(s) type 'texte'")

        quiz_payload = make_quiz(
            qid, title, subject, level, questions,
            source="Eduscol programmes officiels + BOEN",
            programme_ref='BO special ndeg1 du 22 janvier 2019 (LGT) + Eduscol Histoire-Geographie Seconde',
        )
        answers_payload = make_answers(qid, title, subject, level, questions)

        quiz_payload = normalize_text_payload(quiz_payload)
        answers_payload = normalize_text_payload(answers_payload)

        for dir_path, payload, suffix in [
            (GEN_QUIZ_DIR, quiz_payload, "quiz"),
            (GEN_ANSWERS_DIR, answers_payload, "answers"),
            (RUNTIME_QUIZ_DIR, quiz_payload, "quiz"),
            (RUNTIME_ANSWERS_DIR, answers_payload, "answers"),
        ]:
            path = os.path.join(dir_path, f"{qid}.json")
            with open(path, "w", encoding="utf-8", newline="\n") as f:
                json.dump(payload, f, ensure_ascii=False, indent=2)
                f.write("\n")

        count += 1
        print(f"  [OK] {qid}.json - {title}")

    print(f"\n[OK] {count} quiz generes (+ {count} reponses)")
    if total_counts["texte"]:
        print(f"[AUDIT] Total questions type 'texte' dans le jeu source : {total_counts['texte']}")


def load_external_quiz_source(path: str):
    quiz_file = Path(path)
    if not quiz_file.exists():
        raise FileNotFoundError(f"Input file not found: {path}")
    with quiz_file.open("r", encoding="utf-8") as handle:
        data = json.load(handle)

    if isinstance(data, dict) and "quizzes_data" in data:
        data = data["quizzes_data"]

    if not isinstance(data, list):
        raise ValueError("External quiz source must be a JSON list or an object containing 'quizzes_data'.")

    return data


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Generate Histoire-Géo 2nde quiz JSON files")
    parser.add_argument(
        "--input-file",
        help="Optional external JSON file containing quizzes_data.",
        default="",
    )
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    if args.input_file:
        print(f"Loading external quiz source from {args.input_file}")
        external_data = load_external_quiz_source(args.input_file)
        globals()["quizzes_data"] = external_data

    print("Generating quizzes from template IA...")
    write_quiz_files()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
