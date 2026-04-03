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
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", ".."))

# Ã€ adapter dans chaque clone
BASENAME = "anglais_terminale_quizzes"

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
    if qt in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if qt == "qcm":
        return "qcm"
    if qt in {"open", "texte", "text"}:
        return "open"
    return qt


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
        elif qtype == "vrai-faux":
            sanitized = {
                "type": "vrai-faux",
                "question": str(question.get("question", "")),
            }
        else:
            sanitized = {
                "type": "open",
                "question": str(question.get("question", "")),
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
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "open",
                "answer": str(q.get("correct_answer", "")),
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
        1803,
        'Anglais terminale - Comprendre un message oral',
        'Anglais',
        'terminale',
        [
            {
                'id': '1803_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Comprendre un message oral', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1803_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Comprendre un message oral', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1803_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Comprendre un message oral'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1803_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Comprendre un message oral' ?",
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
                'id': '1803_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1803_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Comprendre un message oral'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1803_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Comprendre un message oral' ?",
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
                'id': '1803_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Comprendre un message oral'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1804,
        'Anglais terminale - Lexique du quotidien',
        'Anglais',
        'terminale',
        [
            {
                'id': '1804_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Lexique du quotidien', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1804_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Lexique du quotidien', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1804_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Lexique du quotidien'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1804_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Lexique du quotidien' ?",
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
                'id': '1804_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1804_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Lexique du quotidien'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1804_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Lexique du quotidien' ?",
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
                'id': '1804_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Lexique du quotidien'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1805,
        'Anglais terminale - Expression ecrite breve',
        'Anglais',
        'terminale',
        [
            {
                'id': '1805_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Expression ecrite breve', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1805_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Expression ecrite breve', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1805_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Expression ecrite breve'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1805_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Expression ecrite breve' ?",
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
                'id': '1805_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1805_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Expression ecrite breve'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1805_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Expression ecrite breve' ?",
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
                'id': '1805_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Expression ecrite breve'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1806,
        'Anglais terminale - Temps et conjugaison',
        'Anglais',
        'terminale',
        [
            {
                'id': '1806_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Temps et conjugaison', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1806_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Temps et conjugaison', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1806_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Temps et conjugaison'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1806_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Temps et conjugaison' ?",
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
                'id': '1806_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1806_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Temps et conjugaison'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1806_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Temps et conjugaison' ?",
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
                'id': '1806_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Temps et conjugaison'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1807,
        'Anglais terminale - Rep?res culturels',
        'Anglais',
        'terminale',
        [
            {
                'id': '1807_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Rep?res culturels', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1807_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Rep?res culturels', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1807_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Rep?res culturels'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1807_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Rep?res culturels' ?",
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
                'id': '1807_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1807_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Rep?res culturels'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1807_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Rep?res culturels' ?",
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
                'id': '1807_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Rep?res culturels'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1808,
        'Anglais terminale - Prendre la parole',
        'Anglais',
        'terminale',
        [
            {
                'id': '1808_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Prendre la parole', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1808_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Prendre la parole', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1808_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Prendre la parole'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1808_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Prendre la parole' ?",
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
                'id': '1808_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1808_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Prendre la parole'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1808_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Prendre la parole' ?",
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
                'id': '1808_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Prendre la parole'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1809,
        'Anglais terminale - Comprendre un article',
        'Anglais',
        'terminale',
        [
            {
                'id': '1809_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Comprendre un article', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1809_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Comprendre un article', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1809_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Comprendre un article'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1809_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Comprendre un article' ?",
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
                'id': '1809_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1809_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Comprendre un article'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1809_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Comprendre un article' ?",
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
                'id': '1809_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Comprendre un article'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1810,
        'Anglais terminale - Interactions en contexte',
        'Anglais',
        'terminale',
        [
            {
                'id': '1810_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Interactions en contexte', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1810_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Interactions en contexte', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1810_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Interactions en contexte'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1810_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Interactions en contexte' ?",
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
                'id': '1810_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1810_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Interactions en contexte'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1810_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Interactions en contexte' ?",
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
                'id': '1810_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Interactions en contexte'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1811,
        'Anglais terminale - Strategies de comprehension',
        'Anglais',
        'terminale',
        [
            {
                'id': '1811_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Strategies de comprehension', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1811_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Strategies de comprehension', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1811_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Strategies de comprehension'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1811_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Strategies de comprehension' ?",
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
                'id': '1811_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1811_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Strategies de comprehension'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1811_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Strategies de comprehension' ?",
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
                'id': '1811_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Strategies de comprehension'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1812,
        'Anglais terminale - Reviser les structures cl?s',
        'Anglais',
        'terminale',
        [
            {
                'id': '1812_1',
                'type': 'qcm',
                'question': "[Anglais terminale] Sur le theme 'Reviser les structures cl?s', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1812_2',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Sur 'Reviser les structures cl?s', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1812_3',
                'type': 'texte',
                'question': "[Anglais terminale] Cite une methode concrete pour progresser sur le theme 'Reviser les structures cl?s'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1812_4',
                'type': 'qcm',
                'question': "[Anglais terminale] Quelle action favorise la memorisation durable du theme 'Reviser les structures cl?s' ?",
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
                'id': '1812_5',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1812_6',
                'type': 'texte',
                'question': "[Anglais terminale] Propose un exemple d'auto-correction pertinente sur 'Reviser les structures cl?s'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1812_7',
                'type': 'qcm',
                'question': "[Anglais terminale] Quel indicateur montre une bonne maitrise du theme 'Reviser les structures cl?s' ?",
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
                'id': '1812_8',
                'type': 'vrai-faux',
                'question': "[Anglais terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Reviser les structures cl?s'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    )
]


def write_quiz_files():
    count = 0
    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(
            qid, title, subject, level, questions,
            source="Eduscol programmes officiels + BOEN",
            programme_ref='BO special ndeg8 du 25 juillet 2019 + Eduscol Langues vivantes Terminale',
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
