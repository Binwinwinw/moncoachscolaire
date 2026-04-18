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
BASENAME = "espagnol_2nde_quizzes"

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
    return "vrai-faux"


def build_true_false_statement(question_text, correct_answer, explanation):
    answer = str(correct_answer or "").strip()
    detail = str(explanation or "").strip()
    if answer:
        return f"La bonne réponse attendue est : {answer}."
    if detail:
        return detail if detail.endswith((".", "!", "?")) else f"{detail}."
    prompt = str(question_text or "").strip()
    return prompt if prompt else "Cette affirmation est à évaluer."


def make_quiz(qid, title, subject, level, questions,
              source="Eduscol + BOEN",
              programme_ref=""):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    quiz_questions = []
    for question in questions:
        raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
        if qtype == "qcm":
            sanitized = {
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            }
        else:
            question_text = str(question.get("question", ""))
            if raw_type not in {"vrai-faux", "vrai faux"}:
                question_text = build_true_false_statement(
                    question.get("question", ""),
                    question.get("correct_answer", ""),
                    question.get("explanation", ""),
                )
            sanitized = {
                "type": "vrai-faux",
                "question": question_text,
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
        raw_type = str(q.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
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
            if raw_type in {"vrai-faux", "vrai faux"}:
                tf_source = q.get("correct", q.get("correct_answer", "faux"))
                tf_answer = "vrai" if str(tf_source).strip().lower() in {"true", "vrai", "1"} else "faux"
            else:
                tf_answer = "vrai"
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
        1663,
        'Espagnol 2nde - Comprendre un message oral',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1663_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Comprendre un message oral', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1663_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Comprendre un message oral', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1663_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Comprendre un message oral'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1663_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Comprendre un message oral' ?",
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
                'id': '1663_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1663_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Comprendre un message oral'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1663_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Comprendre un message oral' ?",
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
                'id': '1663_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Comprendre un message oral'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1664,
        'Espagnol 2nde - Lexique du quotidien',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1664_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Lexique du quotidien', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1664_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Lexique du quotidien', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1664_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Lexique du quotidien'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1664_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Lexique du quotidien' ?",
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
                'id': '1664_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1664_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Lexique du quotidien'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1664_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Lexique du quotidien' ?",
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
                'id': '1664_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Lexique du quotidien'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1665,
        'Espagnol 2nde - Expression ecrite breve',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1665_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Expression ecrite breve', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1665_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Expression ecrite breve', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1665_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Expression ecrite breve'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1665_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Expression ecrite breve' ?",
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
                'id': '1665_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1665_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Expression ecrite breve'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1665_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Expression ecrite breve' ?",
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
                'id': '1665_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Expression ecrite breve'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1666,
        'Espagnol 2nde - Temps et conjugaison',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1666_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Temps et conjugaison', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1666_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Temps et conjugaison', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1666_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Temps et conjugaison'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1666_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Temps et conjugaison' ?",
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
                'id': '1666_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1666_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Temps et conjugaison'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1666_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Temps et conjugaison' ?",
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
                'id': '1666_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Temps et conjugaison'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1667,
        'Espagnol 2nde - Rep?res culturels',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1667_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Rep?res culturels', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1667_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Rep?res culturels', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1667_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Rep?res culturels'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1667_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Rep?res culturels' ?",
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
                'id': '1667_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1667_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Rep?res culturels'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1667_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Rep?res culturels' ?",
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
                'id': '1667_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Rep?res culturels'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1668,
        'Espagnol 2nde - Prendre la parole',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1668_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Prendre la parole', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1668_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Prendre la parole', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1668_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Prendre la parole'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1668_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Prendre la parole' ?",
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
                'id': '1668_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1668_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Prendre la parole'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1668_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Prendre la parole' ?",
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
                'id': '1668_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Prendre la parole'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1669,
        'Espagnol 2nde - Comprendre un article',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1669_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Comprendre un article', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1669_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Comprendre un article', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1669_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Comprendre un article'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1669_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Comprendre un article' ?",
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
                'id': '1669_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1669_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Comprendre un article'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1669_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Comprendre un article' ?",
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
                'id': '1669_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Comprendre un article'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1670,
        'Espagnol 2nde - Interactions en contexte',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1670_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Interactions en contexte', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1670_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Interactions en contexte', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1670_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Interactions en contexte'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1670_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Interactions en contexte' ?",
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
                'id': '1670_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1670_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Interactions en contexte'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1670_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Interactions en contexte' ?",
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
                'id': '1670_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Interactions en contexte'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1671,
        'Espagnol 2nde - Strategies de comprehension',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1671_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Strategies de comprehension', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1671_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Strategies de comprehension', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1671_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Strategies de comprehension'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1671_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Strategies de comprehension' ?",
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
                'id': '1671_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1671_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Strategies de comprehension'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1671_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Strategies de comprehension' ?",
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
                'id': '1671_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Strategies de comprehension'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1672,
        'Espagnol 2nde - Reviser les structures cl?s',
        'Espagnol',
        '2nde',
        [
            {
                'id': '1672_1',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Sur le theme 'Reviser les structures cl?s', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1672_2',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Sur 'Reviser les structures cl?s', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1672_3',
                'type': 'texte',
                'question': "[Espagnol 2nde] Cite une methode concrete pour progresser sur le theme 'Reviser les structures cl?s'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1672_4',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quelle action favorise la memorisation durable du theme 'Reviser les structures cl?s' ?",
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
                'id': '1672_5',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1672_6',
                'type': 'texte',
                'question': "[Espagnol 2nde] Propose un exemple d'auto-correction pertinente sur 'Reviser les structures cl?s'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1672_7',
                'type': 'qcm',
                'question': "[Espagnol 2nde] Quel indicateur montre une bonne maitrise du theme 'Reviser les structures cl?s' ?",
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
                'id': '1672_8',
                'type': 'vrai-faux',
                'question': "[Espagnol 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Reviser les structures cl?s'.",
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
            programme_ref='BO special ndeg1 du 22 janvier 2019 (LGT) + Eduscol Langues vivantes Seconde',
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


if __name__ == "__main__":
    print("Generating quizzes from template IA...")
    write_quiz_files()
