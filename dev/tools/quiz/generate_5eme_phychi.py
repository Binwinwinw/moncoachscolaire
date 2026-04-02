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
BASENAME = "phychi_5eme_quizzes"

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
        1773,
        'Physique-Chimie 5eme - Mesures et unites',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1773_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Mesures et unites', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1773_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Mesures et unites', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1773_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Mesures et unites'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1773_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Mesures et unites' ?",
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
                'id': '1773_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1773_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Mesures et unites'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1773_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Mesures et unites' ?",
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
                'id': '1773_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Mesures et unites'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1774,
        'Physique-Chimie 5eme - Mouvements et forces',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1774_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Mouvements et forces', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1774_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Mouvements et forces', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1774_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Mouvements et forces'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1774_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Mouvements et forces' ?",
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
                'id': '1774_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1774_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Mouvements et forces'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1774_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Mouvements et forces' ?",
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
                'id': '1774_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Mouvements et forces'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1775,
        'Physique-Chimie 5eme - Energie et conversion',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1775_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Energie et conversion', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1775_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Energie et conversion', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1775_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Energie et conversion'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1775_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Energie et conversion' ?",
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
                'id': '1775_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1775_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Energie et conversion'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1775_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Energie et conversion' ?",
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
                'id': '1775_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Energie et conversion'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1776,
        'Physique-Chimie 5eme - Modeles particulaires',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1776_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Modeles particulaires', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1776_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Modeles particulaires', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1776_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Modeles particulaires'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1776_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Modeles particulaires' ?",
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
                'id': '1776_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1776_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Modeles particulaires'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1776_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Modeles particulaires' ?",
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
                'id': '1776_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Modeles particulaires'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1777,
        'Physique-Chimie 5eme - Reactions chimiques',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1777_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Reactions chimiques', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1777_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Reactions chimiques', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1777_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Reactions chimiques'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1777_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Reactions chimiques' ?",
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
                'id': '1777_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1777_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Reactions chimiques'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1777_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Reactions chimiques' ?",
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
                'id': '1777_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Reactions chimiques'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1778,
        'Physique-Chimie 5eme - Electricite',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1778_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Electricite', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1778_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Electricite', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1778_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Electricite'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1778_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Electricite' ?",
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
                'id': '1778_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1778_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Electricite'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1778_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Electricite' ?",
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
                'id': '1778_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Electricite'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1779,
        'Physique-Chimie 5eme - Ondes et signaux',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1779_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Ondes et signaux', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1779_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Ondes et signaux', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1779_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Ondes et signaux'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1779_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Ondes et signaux' ?",
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
                'id': '1779_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1779_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Ondes et signaux'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1779_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Ondes et signaux' ?",
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
                'id': '1779_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Ondes et signaux'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1780,
        'Physique-Chimie 5eme - Securite au laboratoire',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1780_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Securite au laboratoire', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1780_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Securite au laboratoire', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1780_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Securite au laboratoire'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1780_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Securite au laboratoire' ?",
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
                'id': '1780_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1780_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Securite au laboratoire'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1780_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Securite au laboratoire' ?",
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
                'id': '1780_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Securite au laboratoire'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1781,
        'Physique-Chimie 5eme - Demarche experimentale',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1781_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Demarche experimentale', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1781_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Demarche experimentale', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1781_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Demarche experimentale'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1781_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Demarche experimentale' ?",
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
                'id': '1781_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1781_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Demarche experimentale'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1781_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Demarche experimentale' ?",
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
                'id': '1781_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Demarche experimentale'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1782,
        'Physique-Chimie 5eme - Resolution de problemes',
        'Physique-Chimie',
        '5eme',
        [
            {
                'id': '1782_1',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Sur le theme 'Resolution de problemes', quelle demarche est la plus efficace pour reussir un exercice diagnostic ?",
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
                'id': '1782_2',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Sur 'Resolution de problemes', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1782_3',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Cite une methode concrete pour progresser sur le theme 'Resolution de problemes'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1782_4',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quelle action favorise la memorisation durable du theme 'Resolution de problemes' ?",
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
                'id': '1782_5',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1782_6',
                'type': 'texte',
                'question': "[Physique-Chimie 5eme] Propose un exemple d'auto-correction pertinente sur 'Resolution de problemes'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1782_7',
                'type': 'qcm',
                'question': "[Physique-Chimie 5eme] Quel indicateur montre une bonne maitrise du theme 'Resolution de problemes' ?",
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
                'id': '1782_8',
                'type': 'vrai-faux',
                'question': "[Physique-Chimie 5eme] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Resolution de problemes'.",
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
            programme_ref='BO ndeg31 du 30 juillet 2020 (Cycle 4) + Eduscol Physique-Chimie',
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
