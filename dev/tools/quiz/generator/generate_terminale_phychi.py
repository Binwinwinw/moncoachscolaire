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
BASENAME = "phychi_terminale_quizzes"

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
        1853,
        'Physique-Chimie terminale - Mesures et unites',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1853_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Mesures et unites', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1853_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Mesures et unites', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1853_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Mesures et unites'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1853_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Mesures et unites' ?",
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
                'id': '1853_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1853_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Mesures et unites'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1853_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Mesures et unites' ?",
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
                'id': '1853_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Mesures et unites'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1854,
        'Physique-Chimie terminale - Mouvements et forces',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1854_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Mouvements et forces', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1854_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Mouvements et forces', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1854_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Mouvements et forces'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1854_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Mouvements et forces' ?",
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
                'id': '1854_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1854_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Mouvements et forces'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1854_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Mouvements et forces' ?",
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
                'id': '1854_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Mouvements et forces'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1855,
        'Physique-Chimie terminale - Energie et conversion',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1855_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Energie et conversion', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1855_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Energie et conversion', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1855_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Energie et conversion'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1855_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Energie et conversion' ?",
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
                'id': '1855_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1855_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Energie et conversion'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1855_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Energie et conversion' ?",
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
                'id': '1855_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Energie et conversion'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1856,
        'Physique-Chimie terminale - Modeles particulaires',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1856_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Modeles particulaires', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1856_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Modeles particulaires', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1856_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Modeles particulaires'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1856_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Modeles particulaires' ?",
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
                'id': '1856_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1856_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Modeles particulaires'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1856_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Modeles particulaires' ?",
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
                'id': '1856_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Modeles particulaires'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1857,
        'Physique-Chimie terminale - Reactions chimiques',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1857_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Reactions chimiques', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1857_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Reactions chimiques', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1857_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Reactions chimiques'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1857_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Reactions chimiques' ?",
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
                'id': '1857_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1857_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Reactions chimiques'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1857_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Reactions chimiques' ?",
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
                'id': '1857_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Reactions chimiques'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1858,
        'Physique-Chimie terminale - Electricite',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1858_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Electricite', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1858_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Electricite', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1858_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Electricite'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1858_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Electricite' ?",
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
                'id': '1858_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1858_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Electricite'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1858_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Electricite' ?",
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
                'id': '1858_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Electricite'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1859,
        'Physique-Chimie terminale - Ondes et signaux',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1859_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Ondes et signaux', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1859_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Ondes et signaux', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1859_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Ondes et signaux'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1859_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Ondes et signaux' ?",
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
                'id': '1859_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1859_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Ondes et signaux'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1859_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Ondes et signaux' ?",
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
                'id': '1859_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Ondes et signaux'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1860,
        'Physique-Chimie terminale - Securite au laboratoire',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1860_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Securite au laboratoire', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1860_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Securite au laboratoire', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1860_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Securite au laboratoire'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1860_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Securite au laboratoire' ?",
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
                'id': '1860_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1860_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Securite au laboratoire'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1860_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Securite au laboratoire' ?",
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
                'id': '1860_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Securite au laboratoire'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1861,
        'Physique-Chimie terminale - Demarche experimentale',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1861_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Demarche experimentale', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1861_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Demarche experimentale', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1861_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Demarche experimentale'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1861_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Demarche experimentale' ?",
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
                'id': '1861_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1861_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Demarche experimentale'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1861_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Demarche experimentale' ?",
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
                'id': '1861_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Demarche experimentale'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1862,
        'Physique-Chimie terminale - Resolution de problemes',
        'Physique-Chimie',
        'terminale',
        [
            {
                'id': '1862_1',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Sur le theme 'Resolution de problemes', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1862_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Sur 'Resolution de problemes', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1862_3',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Cite une methode concrete pour progresser sur le theme 'Resolution de problemes'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1862_4',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quelle action favorise la memorisation durable du theme 'Resolution de problemes' ?",
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
                'id': '1862_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1862_6',
                'type': 'texte',
                'question': "[Physique-Chimie terminale] Propose un exemple d'auto-correction pertinente sur 'Resolution de problemes'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1862_7',
                'type': 'qcm',
                'question': "[Physique-Chimie terminale] Quel indicateur montre une bonne maitrise du theme 'Resolution de problemes' ?",
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
                'id': '1862_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie terminale] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Resolution de problemes'.",
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
            programme_ref='BO special ndeg8 du 25 juillet 2019 + Eduscol Physique-Chimie Terminale',
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
