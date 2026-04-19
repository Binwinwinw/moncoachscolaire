#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Template IA â€“ generate_<niveau>_<matiere>.py
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

# Ã€ adapter dans chaque clone
BASENAME = "svt_2nde_quizzes"

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
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    if qt in {"vrai-faux", "vrai faux", "open", "texte", "text"}:
        return "vrai-faux"
    return "vrai-faux"


def build_true_false_statement(question_text, fallback_answer=""):
    question_text = str(question_text).strip()
    fallback_answer = str(fallback_answer).strip().rstrip(".")
    if fallback_answer:
        return f"{question_text} La bonne réponse attendue est : {fallback_answer}."
    return question_text or "Choisis si l'affirmation est vraie ou fausse."


def make_quiz(qid, title, subject, level, questions,
              source="Eduscol + BOEN",
              programme_ref=""):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    clean_questions = [
        {k: v for k, v in q.items() if k not in answer_keys}
        for q in questions
    ]
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    quiz_questions = []
    for question in clean_questions:
        qtype = normalize_question_type(question.get("type", ""))
        if qtype == "qcm":
            sanitized = {
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            }
        else:
            sanitized = {
                "type": "vrai-faux",
                "question": build_true_false_statement(
                    question.get("question", ""),
                    question.get("correct_answer", ""),
                ),
            }
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
    answers = []
    for index, q in enumerate(questions):
        qtype = normalize_question_type(q.get("type", ""))
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
        else:
            tf_source = q.get("correct", q.get("correct_answer", "vrai"))
            tf_answer = "vrai" if str(tf_source).strip().lower() in {"true", "vrai", "1"} else "faux"
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": tf_answer,
                "correction": str(q.get("explanation", "")),
            })

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


quizzes_data = [
    (
        1703,
        'SVT 2nde - Cellule et information genetique',
        'SVT',
        '2nde',
        [
            {
                'id': '1703_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Cellule et information genetique', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1703_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Cellule et information genetique', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1703_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Cellule et information genetique'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1703_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Cellule et information genetique' ?",
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
                'id': '1703_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1703_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Cellule et information genetique'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1703_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Cellule et information genetique' ?",
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
                'id': '1703_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Cellule et information genetique'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1704,
        'SVT 2nde - Fonctionnement du vivant',
        'SVT',
        '2nde',
        [
            {
                'id': '1704_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Fonctionnement du vivant', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1704_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Fonctionnement du vivant', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1704_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Fonctionnement du vivant'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1704_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Fonctionnement du vivant' ?",
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
                'id': '1704_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1704_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Fonctionnement du vivant'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1704_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Fonctionnement du vivant' ?",
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
                'id': '1704_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Fonctionnement du vivant'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1705,
        'SVT 2nde - Corps humain et sante',
        'SVT',
        '2nde',
        [
            {
                'id': '1705_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Corps humain et sante', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1705_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Corps humain et sante', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1705_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Corps humain et sante'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1705_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Corps humain et sante' ?",
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
                'id': '1705_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1705_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Corps humain et sante'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1705_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Corps humain et sante' ?",
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
                'id': '1705_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Corps humain et sante'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1706,
        'SVT 2nde - Ecosystemes',
        'SVT',
        '2nde',
        [
            {
                'id': '1706_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Ecosystemes', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1706_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Ecosystemes', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1706_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Ecosystemes'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1706_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Ecosystemes' ?",
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
                'id': '1706_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1706_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Ecosystemes'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1706_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Ecosystemes' ?",
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
                'id': '1706_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Ecosystemes'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1707,
        'SVT 2nde - Biodiversite',
        'SVT',
        '2nde',
        [
            {
                'id': '1707_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Biodiversite', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1707_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Biodiversite', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1707_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Biodiversite'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1707_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Biodiversite' ?",
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
                'id': '1707_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1707_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Biodiversite'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1707_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Biodiversite' ?",
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
                'id': '1707_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Biodiversite'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1708,
        'SVT 2nde - Evolution',
        'SVT',
        '2nde',
        [
            {
                'id': '1708_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Evolution', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1708_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Evolution', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1708_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Evolution'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1708_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Evolution' ?",
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
                'id': '1708_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1708_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Evolution'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1708_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Evolution' ?",
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
                'id': '1708_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Evolution'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1709,
        'SVT 2nde - Terre interne et risques',
        'SVT',
        '2nde',
        [
            {
                'id': '1709_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Terre interne et risques', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1709_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Terre interne et risques', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1709_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Terre interne et risques'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1709_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Terre interne et risques' ?",
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
                'id': '1709_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1709_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Terre interne et risques'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1709_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Terre interne et risques' ?",
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
                'id': '1709_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Terre interne et risques'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1710,
        'SVT 2nde - Climat et environnement',
        'SVT',
        '2nde',
        [
            {
                'id': '1710_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Climat et environnement', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1710_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Climat et environnement', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1710_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Climat et environnement'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1710_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Climat et environnement' ?",
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
                'id': '1710_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1710_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Climat et environnement'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1710_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Climat et environnement' ?",
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
                'id': '1710_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Climat et environnement'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1711,
        "SVT 2nde - Methodes d'investigation",
        'SVT',
        '2nde',
        [
            {
                'id': '1711_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Methodes d'investigation', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1711_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Methodes d'investigation', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1711_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Methodes d'investigation'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1711_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Methodes d'investigation' ?",
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
                'id': '1711_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1711_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Methodes d'investigation'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1711_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Methodes d'investigation' ?",
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
                'id': '1711_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Methodes d'investigation'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1712,
        'SVT 2nde - Interpretation de donnees biologiques',
        'SVT',
        '2nde',
        [
            {
                'id': '1712_1',
                'type': 'qcm',
                'question': "[SVT 2nde] Sur le theme 'Interpretation de donnees biologiques', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1712_2',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Sur 'Interpretation de donnees biologiques', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1712_3',
                'type': 'texte',
                'question': "[SVT 2nde] Cite une methode concrete pour progresser sur le theme 'Interpretation de donnees biologiques'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1712_4',
                'type': 'qcm',
                'question': "[SVT 2nde] Quelle action favorise la memorisation durable du theme 'Interpretation de donnees biologiques' ?",
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
                'id': '1712_5',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1712_6',
                'type': 'texte',
                'question': "[SVT 2nde] Propose un exemple d'auto-correction pertinente sur 'Interpretation de donnees biologiques'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1712_7',
                'type': 'qcm',
                'question': "[SVT 2nde] Quel indicateur montre une bonne maitrise du theme 'Interpretation de donnees biologiques' ?",
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
                'id': '1712_8',
                'type': 'vrai-faux',
                'question': "[SVT 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Interpretation de donnees biologiques'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    )
]

SVT2NDE_COMPLEMENT_SPECS = [
    (6401, "SVT 2nde - Biodiversité et écosystèmes", "la biodiversité"),
    (6402, "SVT 2nde - Cellule et membrane", "la cellule"),
    (6403, "SVT 2nde - Information génétique", "l'information génétique"),
    (6404, "SVT 2nde - ADN et chromosomes", "l'ADN"),
    (6405, "SVT 2nde - La respiration cellulaire", "la respiration cellulaire"),
    (6406, "SVT 2nde - La photosynthèse", "la photosynthèse"),
    (6407, "SVT 2nde - Les échanges gazeux", "les échanges gazeux"),
    (6408, "SVT 2nde - Nutrition des plantes", "la nutrition des plantes"),
    (6409, "SVT 2nde - Reproduction et développement", "la reproduction"),
    (6410, "SVT 2nde - Variation génétique", "la variation génétique"),
    (6411, "SVT 2nde - Évolution et sélection", "l'évolution"),
    (6412, "SVT 2nde - Immunité", "l'immunité"),
    (6413, "SVT 2nde - Micro-organismes", "les micro-organismes"),
    (6414, "SVT 2nde - Le fonctionnement du muscle", "le muscle"),
    (6415, "SVT 2nde - Le système nerveux", "le système nerveux"),
    (6416, "SVT 2nde - Santé et prévention", "la prévention en santé"),
    (6417, "SVT 2nde - Les ressources de la Terre", "les ressources terrestres"),
    (6418, "SVT 2nde - Risques naturels", "les risques naturels"),
    (6419, "SVT 2nde - Climats et environnement", "le climat"),
    (6420, "SVT 2nde - Érosion et paysages", "l'érosion"),
    (6421, "SVT 2nde - Eau et cycle hydrologique", "le cycle de l'eau"),
    (6422, "SVT 2nde - Le vivant et son milieu", "les relations entre les êtres vivants"),
    (6423, "SVT 2nde - Chaînes alimentaires", "les chaînes alimentaires"),
    (6424, "SVT 2nde - Les sols vivants", "les sols"),
    (6425, "SVT 2nde - Pollution et impacts", "la pollution"),
    (6426, "SVT 2nde - Les fossiles", "les fossiles"),
    (6427, "SVT 2nde - Histoire de la Terre", "l'histoire de la Terre"),
    (6428, "SVT 2nde - Méthodes d'observation scientifique", "la démarche scientifique"),
    (6429, "SVT 2nde - Graphiques et données en SVT", "l'exploitation de données"),
    (6430, "SVT 2nde - Interpréter une expérience", "l'interprétation expérimentale"),
    (6431, "SVT 2nde - Le microscope", "le microscope"),
    (6432, "SVT 2nde - Organes et fonctions", "les organes"),
    (6433, "SVT 2nde - Les équilibres des écosystèmes", "les équilibres écologiques"),
    (6434, "SVT 2nde - Adaptations du vivant", "les adaptations"),
    (6435, "SVT 2nde - Énergie et matière dans le vivant", "les transferts de matière et d'énergie"),
    (6436, "SVT 2nde - Réchauffement climatique", "le réchauffement climatique"),
    (6437, "SVT 2nde - Révision générale", "la révision générale"),
]


def build_2nde_svt_complement(qid, title, focus):
    return (
        qid,
        title,
        "SVT",
        "2nde",
        [
            {"id": f"{qid}_1", "type": "qcm", "question": f"En SVT, pourquoi étudie-t-on {focus} ?", "options": ["Pour comprendre le vivant et les phénomènes naturels", "Pour éviter toute observation", "Pour réciter sans preuve", "Pour apprendre uniquement des dates"], "correct_option": "Pour comprendre le vivant et les phénomènes naturels", "explanation": "Les SVT permettent d'expliquer scientifiquement le vivant, la Terre et l'environnement."},
            {"id": f"{qid}_2", "type": "vrai-faux", "question": f"{focus.capitalize()} peut être étudié à l'aide de documents, d'observations ou d'expériences.", "correct": True, "explanation": "Les SVT s'appuient sur des données observables et des démarches expérimentales."},
            {"id": f"{qid}_3", "type": "qcm", "question": f"Quelle méthode aide à progresser sur {focus} ?", "options": ["Observer, comparer et justifier", "Répondre au hasard", "Ignorer les documents", "Éviter les schémas"], "correct_option": "Observer, comparer et justifier", "explanation": "Observer les indices et justifier sa réponse est essentiel en SVT."},
            {"id": f"{qid}_4", "type": "vrai-faux", "question": "Une bonne réponse en SVT s'appuie souvent sur une observation précise ou une donnée.", "correct": True, "explanation": "Les données et observations rendent l'explication scientifique plus solide."},
            {"id": f"{qid}_5", "type": "qcm", "question": f"Quel est l'objectif d'un exercice sur {focus} ?", "options": ["Comprendre un mécanisme du vivant ou de la Terre", "Réciter sans réfléchir", "Éviter toute preuve", "Ne jamais corriger"], "correct_option": "Comprendre un mécanisme du vivant ou de la Terre", "explanation": "Les exercices de SVT visent à expliquer des phénomènes biologiques ou géologiques."},
            {"id": f"{qid}_6", "type": "vrai-faux", "question": f"Reprendre ses erreurs permet d'améliorer sa maîtrise de {focus}.", "correct": True, "explanation": "La correction des erreurs aide à mieux comprendre les notions et à progresser durablement."},
            {"id": f"{qid}_7", "type": "qcm", "question": f"Quel support aide souvent à comprendre {focus} ?", "options": ["Un schéma, un graphique ou une expérience", "Une simple récitation", "Une rime poétique", "Une addition posée"], "correct_option": "Un schéma, un graphique ou une expérience", "explanation": "Les schémas, les expériences et les graphiques sont des outils de compréhension très utiles en SVT."},
            {"id": f"{qid}_8", "type": "vrai-faux", "question": "En SVT, la précision du vocabulaire scientifique est importante.", "correct": True, "explanation": "Employer les bons termes scientifiques améliore la clarté et la justesse des réponses."},
        ],
    )


quizzes_data.extend(build_2nde_svt_complement(*spec) for spec in SVT2NDE_COMPLEMENT_SPECS)


def write_quiz_files():
    count = 0
    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(
            qid, title, subject, level, questions,
            source="Eduscol programmes officiels + BOEN",
            programme_ref='BO special ndeg1 du 22 janvier 2019 (LGT) + Eduscol SVT Seconde',
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
        print(f"  âœ“ {qid}.json - {title}")

    print(f"\nâœ… {count} quiz gÃ©nÃ©rÃ©s (+ {count} rÃ©ponses)")


if __name__ == "__main__":
    print("Generating quizzes from template IA...")
    write_quiz_files()
