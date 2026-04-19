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
BASENAME = "svt_terminale_quizzes"

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


def normalize_level_label(level):
    normalized = str(level or "").strip().lower()
    if normalized == "terminale":
        return "Terminale"
    return str(level).strip()


def make_quiz(qid, title, subject, level, questions,
              source="Eduscol + BOEN",
              programme_ref=""):
    level = normalize_level_label(level)
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
    level = normalize_level_label(level)
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
        1863,
        'SVT terminale - Cellule et information genetique',
        'SVT',
        'terminale',
        [
            {
                'id': '1863_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Cellule et information genetique', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1863_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Cellule et information genetique', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1863_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Cellule et information genetique'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1863_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Cellule et information genetique' ?",
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
                'id': '1863_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1863_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Cellule et information genetique'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1863_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Cellule et information genetique' ?",
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
                'id': '1863_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Cellule et information genetique'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1864,
        'SVT terminale - Fonctionnement du vivant',
        'SVT',
        'terminale',
        [
            {
                'id': '1864_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Fonctionnement du vivant', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1864_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Fonctionnement du vivant', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1864_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Fonctionnement du vivant'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1864_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Fonctionnement du vivant' ?",
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
                'id': '1864_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1864_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Fonctionnement du vivant'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1864_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Fonctionnement du vivant' ?",
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
                'id': '1864_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Fonctionnement du vivant'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1865,
        'SVT terminale - Corps humain et sante',
        'SVT',
        'terminale',
        [
            {
                'id': '1865_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Corps humain et sante', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1865_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Corps humain et sante', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1865_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Corps humain et sante'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1865_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Corps humain et sante' ?",
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
                'id': '1865_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1865_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Corps humain et sante'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1865_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Corps humain et sante' ?",
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
                'id': '1865_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Corps humain et sante'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1866,
        'SVT terminale - Ecosystemes',
        'SVT',
        'terminale',
        [
            {
                'id': '1866_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Ecosystemes', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1866_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Ecosystemes', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1866_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Ecosystemes'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1866_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Ecosystemes' ?",
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
                'id': '1866_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1866_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Ecosystemes'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1866_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Ecosystemes' ?",
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
                'id': '1866_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Ecosystemes'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1867,
        'SVT terminale - Biodiversite',
        'SVT',
        'terminale',
        [
            {
                'id': '1867_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Biodiversite', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1867_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Biodiversite', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1867_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Biodiversite'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1867_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Biodiversite' ?",
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
                'id': '1867_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1867_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Biodiversite'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1867_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Biodiversite' ?",
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
                'id': '1867_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Biodiversite'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1868,
        'SVT terminale - Evolution',
        'SVT',
        'terminale',
        [
            {
                'id': '1868_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Evolution', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1868_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Evolution', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1868_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Evolution'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1868_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Evolution' ?",
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
                'id': '1868_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1868_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Evolution'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1868_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Evolution' ?",
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
                'id': '1868_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Evolution'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1869,
        'SVT terminale - Terre interne et risques',
        'SVT',
        'terminale',
        [
            {
                'id': '1869_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Terre interne et risques', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1869_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Terre interne et risques', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1869_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Terre interne et risques'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1869_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Terre interne et risques' ?",
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
                'id': '1869_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1869_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Terre interne et risques'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1869_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Terre interne et risques' ?",
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
                'id': '1869_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Terre interne et risques'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1870,
        'SVT terminale - Climat et environnement',
        'SVT',
        'terminale',
        [
            {
                'id': '1870_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Climat et environnement', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1870_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Climat et environnement', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1870_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Climat et environnement'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1870_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Climat et environnement' ?",
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
                'id': '1870_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1870_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Climat et environnement'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1870_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Climat et environnement' ?",
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
                'id': '1870_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Climat et environnement'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1871,
        "SVT terminale - Methodes d'investigation",
        'SVT',
        'terminale',
        [
            {
                'id': '1871_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Methodes d'investigation', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1871_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Methodes d'investigation', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1871_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Methodes d'investigation'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1871_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Methodes d'investigation' ?",
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
                'id': '1871_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1871_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Methodes d'investigation'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1871_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Methodes d'investigation' ?",
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
                'id': '1871_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Methodes d'investigation'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1872,
        'SVT terminale - Interpretation de donnees biologiques',
        'SVT',
        'terminale',
        [
            {
                'id': '1872_1',
                'type': 'qcm',
                'question': "[SVT terminale] Sur le theme 'Interpretation de donnees biologiques', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1872_2',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Sur 'Interpretation de donnees biologiques', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1872_3',
                'type': 'texte',
                'question': "[SVT terminale] Cite une methode concrete pour progresser sur le theme 'Interpretation de donnees biologiques'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1872_4',
                'type': 'qcm',
                'question': "[SVT terminale] Quelle action favorise la memorisation durable du theme 'Interpretation de donnees biologiques' ?",
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
                'id': '1872_5',
                'type': 'vrai-faux',
                'question': "[SVT terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1872_6',
                'type': 'texte',
                'question': "[SVT terminale] Propose un exemple d'auto-correction pertinente sur 'Interpretation de donnees biologiques'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1872_7',
                'type': 'qcm',
                'question': "[SVT terminale] Quel indicateur montre une bonne maitrise du theme 'Interpretation de donnees biologiques' ?",
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
                'id': '1872_8',
                'type': 'vrai-faux',
                'question': "[SVT terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Interpretation de donnees biologiques'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    )
]

SVT_TERMINALE_COMPLEMENT_SPECS = [
    (6501, "SVT Terminale - ADN et expression génétique", "l'expression génétique"),
    (6502, "SVT Terminale - Mutation et diversité", "la diversité génétique"),
    (6503, "SVT Terminale - Génétique et évolution", "les liens entre génétique et évolution"),
    (6504, "SVT Terminale - Phylogénie", "la phylogénie"),
    (6505, "SVT Terminale - Sélection naturelle", "la sélection naturelle"),
    (6506, "SVT Terminale - Immunité innée", "l'immunité innée"),
    (6507, "SVT Terminale - Immunité adaptative", "l'immunité adaptative"),
    (6508, "SVT Terminale - Vaccination", "la vaccination"),
    (6509, "SVT Terminale - Communication hormonale", "la communication hormonale"),
    (6510, "SVT Terminale - Le système nerveux", "le système nerveux"),
    (6511, "SVT Terminale - Les réflexes", "les réflexes"),
    (6512, "SVT Terminale - Stress et adaptation", "le stress"),
    (6513, "SVT Terminale - Les enzymes", "les enzymes"),
    (6514, "SVT Terminale - Métabolisme cellulaire", "le métabolisme cellulaire"),
    (6515, "SVT Terminale - Respiration et fermentation", "la respiration et la fermentation"),
    (6516, "SVT Terminale - Dynamique des écosystèmes", "les écosystèmes"),
    (6517, "SVT Terminale - Biodiversité et résilience", "la résilience des écosystèmes"),
    (6518, "SVT Terminale - Datation géologique", "la datation géologique"),
    (6519, "SVT Terminale - Tectonique des plaques", "la tectonique des plaques"),
    (6520, "SVT Terminale - Volcanisme et subduction", "le volcanisme"),
    (6521, "SVT Terminale - Formation des chaînes de montagnes", "l'orogenèse"),
    (6522, "SVT Terminale - Le climat passé", "les climats du passé"),
    (6523, "SVT Terminale - Les archives géologiques", "les archives géologiques"),
    (6524, "SVT Terminale - Ressources énergétiques", "les ressources énergétiques"),
    (6525, "SVT Terminale - Transition énergétique", "la transition énergétique"),
    (6526, "SVT Terminale - Les cycles biogéochimiques", "les cycles biogéochimiques"),
    (6527, "SVT Terminale - Les sols et leur fertilité", "les sols"),
    (6528, "SVT Terminale - Le microbiote", "le microbiote"),
    (6529, "SVT Terminale - Reproduction humaine", "la reproduction humaine"),
    (6530, "SVT Terminale - Procréation et assistance médicale", "la procréation médicalement assistée"),
    (6531, "SVT Terminale - Santé publique", "la santé publique"),
    (6532, "SVT Terminale - Démarche expérimentale", "la démarche expérimentale"),
    (6533, "SVT Terminale - Lecture de graphique", "la lecture de graphique"),
    (6534, "SVT Terminale - Interpréter un schéma", "l'interprétation de schéma"),
    (6535, "SVT Terminale - Argumentation scientifique", "l'argumentation scientifique"),
    (6536, "SVT Terminale - Analyse de documents", "l'analyse de documents"),
    (6537, "SVT Terminale - Révision générale", "la révision générale"),
]


def build_terminale_svt_complement(qid, title, focus):
    return (
        qid,
        title,
        "SVT",
        "Terminale",
        [
            {"id": f"{qid}_1", "type": "qcm", "question": f"En SVT Terminale, pourquoi étudie-t-on {focus} ?", "options": ["Pour comprendre des mécanismes biologiques et géologiques complexes", "Pour éviter toute justification", "Pour répondre sans données", "Pour apprendre des définitions isolées"], "correct_option": "Pour comprendre des mécanismes biologiques et géologiques complexes", "explanation": "Au niveau Terminale, les SVT demandent une compréhension fine des mécanismes du vivant et de la Terre."},
            {"id": f"{qid}_2", "type": "vrai-faux", "question": f"{focus.capitalize()} peut être étudié à partir d'expériences, de schémas et de données scientifiques.", "correct": True, "explanation": "Les SVT s'appuient sur des observations et des preuves pour construire les raisonnements scientifiques."},
            {"id": f"{qid}_3", "type": "qcm", "question": f"Quelle méthode aide à réussir sur {focus} ?", "options": ["Analyser les documents et justifier", "Répondre sans lire", "Ignorer les unités", "Éviter la démarche scientifique"], "correct_option": "Analyser les documents et justifier", "explanation": "La réussite en SVT Terminale repose sur l'analyse rigoureuse des données et la justification des conclusions."},
            {"id": f"{qid}_4", "type": "vrai-faux", "question": "Une réponse scientifique claire doit s'appuyer sur des indices précis et un vocabulaire adapté.", "correct": True, "explanation": "La précision des termes et l'appui sur les documents renforcent la qualité de la réponse."},
            {"id": f"{qid}_5", "type": "qcm", "question": f"Quel est l'objectif principal d'un exercice sur {focus} ?", "options": ["Expliquer un phénomène avec un raisonnement scientifique", "Réciter sans comprendre", "Éviter les preuves", "Ne pas corriger ses erreurs"], "correct_option": "Expliquer un phénomène avec un raisonnement scientifique", "explanation": "Les exercices de SVT Terminale demandent une explication structurée et argumentée."},
            {"id": f"{qid}_6", "type": "vrai-faux", "question": f"Reprendre ses erreurs peut améliorer durablement la maîtrise de {focus}.", "correct": True, "explanation": "L'analyse des erreurs fait partie intégrante de la progression scientifique."},
            {"id": f"{qid}_7", "type": "qcm", "question": f"Quel support est souvent pertinent pour comprendre {focus} ?", "options": ["Un schéma, un graphique ou un protocole expérimental", "Une seule récitation", "Une simple liste sans lien", "Une phrase sans contexte"], "correct_option": "Un schéma, un graphique ou un protocole expérimental", "explanation": "Les données visuelles et expérimentales sont essentielles pour interpréter les phénomènes en SVT."},
            {"id": f"{qid}_8", "type": "vrai-faux", "question": "En Terminale, les SVT demandent de relier connaissances, documents et raisonnement.", "correct": True, "explanation": "La compétence attendue est de mobiliser le cours pour interpréter les documents et construire une explication cohérente."},
        ],
    )


quizzes_data.extend(build_terminale_svt_complement(*spec) for spec in SVT_TERMINALE_COMPLEMENT_SPECS)


def write_quiz_files():
    count = 0
    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(
            qid, title, subject, level, questions,
            source="Eduscol programmes officiels + BOEN",
            programme_ref='BO special ndeg8 du 25 juillet 2019 + Eduscol SVT Terminale',
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
