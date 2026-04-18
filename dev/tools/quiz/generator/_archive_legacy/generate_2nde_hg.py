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
BASENAME = "hg_2nde_quizzes"

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
        1673,
        'Histoire-Geographie 2nde - Reperes chronologiques',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1673_1',
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
                'id': '1673_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Reperes chronologiques', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1673_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Reperes chronologiques'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1673_4',
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
                'id': '1673_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1673_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Reperes chronologiques'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1673_7',
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
                'id': '1673_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Reperes chronologiques'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1674,
        'Histoire-Geographie 2nde - Acteurs et evenements',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1674_1',
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
                'id': '1674_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Acteurs et evenements', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1674_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Acteurs et evenements'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1674_4',
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
                'id': '1674_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1674_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Acteurs et evenements'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1674_7',
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
                'id': '1674_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Acteurs et evenements'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1675,
        'Histoire-Geographie 2nde - Espaces productifs',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1675_1',
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
                'id': '1675_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Espaces productifs', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1675_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Espaces productifs'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1675_4',
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
                'id': '1675_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1675_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Espaces productifs'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1675_7',
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
                'id': '1675_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Espaces productifs'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1676,
        'Histoire-Geographie 2nde - Dynamiques territoriales',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1676_1',
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
                'id': '1676_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Dynamiques territoriales', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1676_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Dynamiques territoriales'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1676_4',
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
                'id': '1676_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1676_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Dynamiques territoriales'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1676_7',
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
                'id': '1676_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Dynamiques territoriales'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1677,
        'Histoire-Geographie 2nde - Puissances et influences',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1677_1',
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
                'id': '1677_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Puissances et influences', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1677_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Puissances et influences'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1677_4',
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
                'id': '1677_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1677_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Puissances et influences'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1677_7',
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
                'id': '1677_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Puissances et influences'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1678,
        'Histoire-Geographie 2nde - Developpement durable',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1678_1',
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
                'id': '1678_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Developpement durable', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1678_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Developpement durable'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1678_4',
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
                'id': '1678_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1678_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Developpement durable'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1678_7',
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
                'id': '1678_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Developpement durable'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1679,
        'Histoire-Geographie 2nde - Etude de documents',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1679_1',
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
                'id': '1679_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Etude de documents', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1679_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Etude de documents'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1679_4',
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
                'id': '1679_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1679_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Etude de documents'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1679_7',
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
                'id': '1679_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Etude de documents'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1680,
        'Histoire-Geographie 2nde - Methodes de composition',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1680_1',
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
                'id': '1680_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Methodes de composition', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1680_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Methodes de composition'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1680_4',
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
                'id': '1680_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1680_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Methodes de composition'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1680_7',
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
                'id': '1680_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Methodes de composition'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1681,
        'Histoire-Geographie 2nde - Cartographie',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1681_1',
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
                'id': '1681_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Cartographie', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1681_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Cartographie'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1681_4',
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
                'id': '1681_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1681_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Cartographie'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1681_7',
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
                'id': '1681_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Cartographie'.",
                'correct': True,
                'explanation': 'Le cycle entrainement-feedback-reprise est une methode robuste de progression.'
            }
        ]
    ),
    (
        1682,
        'Histoire-Geographie 2nde - Analyse critique des sources',
        'Histoire-Geographie',
        '2nde',
        [
            {
                'id': '1682_1',
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
                'id': '1682_2',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Sur 'Analyse critique des sources', verifier ses reponses avant validation ameliore la fiabilite.",
                'correct': True,
                'explanation': "Une relecture finale aide a corriger les erreurs d'inattention."
            },
            {
                'id': '1682_3',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Cite une methode concrete pour progresser sur le theme 'Analyse critique des sources'.",
                'correct_answer': "S'entrainer regulierement, analyser ses erreurs et reformuler les notions essentielles.",
                'explanation': "La progression vient de la repetition guidee et de l'analyse des erreurs."
            },
            {
                'id': '1682_4',
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
                'id': '1682_5',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] L'explication d'une reponse est moins importante que la reponse elle-meme.",
                'correct': False,
                'explanation': 'La justification montre la comprehension et permet un feedback utile.'
            },
            {
                'id': '1682_6',
                'type': 'texte',
                'question': "[Histoire-Geographie 2nde] Propose un exemple d'auto-correction pertinente sur 'Analyse critique des sources'.",
                'correct_answer': "Comparer sa reponse au corrig?, identifier l'erreur precise et ecrire la bonne strategie.",
                'explanation': "L'auto-correction explicite transforme une erreur en apprentissage."
            },
            {
                'id': '1682_7',
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
                'id': '1682_8',
                'type': 'vrai-faux',
                'question': "[Histoire-Geographie 2nde] Alterner entrainement, feedback et reprise des erreurs aide a progresser sur 'Analyse critique des sources'.",
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
        print(f"  âœ“ {qid}.json - {title}")

    print(f"\nâœ… {count} quiz gÃ©nÃ©rÃ©s (+ {count} rÃ©ponses)")


if __name__ == "__main__":
    print("Generating quizzes from template IA...")
    write_quiz_files()
