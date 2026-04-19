#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Générateur de quiz Mathématiques 4e — lot pilote fractions."""

from __future__ import annotations

import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "mathematiques_4eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")


def make_qcm(question_id, question, options, correct_option, explanation):
    return {
        "id": question_id,
        "type": "qcm",
        "question": question,
        "options": options,
        "correct_option": correct_option,
        "explanation": explanation,
    }


def make_true_false(question_id, question, correct, explanation):
    return {
        "id": question_id,
        "type": "vrai-faux",
        "question": question,
        "correct": correct,
        "explanation": explanation,
    }


def make_open(question_id, question, correct_answer, explanation):
    return {
        "id": question_id,
        "type": "texte",
        "question": question,
        "correct_answer": correct_answer,
        "explanation": explanation,
    }


quizzes_data = [
    (
        4101,
        "Addition de fractions",
        "Mathématiques",
        "4eme",
        [
            {
                "notion": "Dénominateur commun",
                "description": "Trouver un dénominateur commun avant d'additionner deux fractions de dénominateurs différents.",
            },
            {
                "notion": "Addition des numérateurs",
                "description": "Une fois les dénominateurs égaux, on additionne seulement les numérateurs.",
            },
            {
                "notion": "Simplification finale",
                "description": "Réduire la fraction obtenue pour donner une réponse plus lisible.",
            },
        ],
        [
            make_qcm(
                "4101question_1",
                "Pour additionner 3/4 et 1/4, quel dénominateur commun utilises-tu ?",
                ["4", "8", "12", "2"],
                "4",
                "Les deux fractions ont déjà le même dénominateur : on garde 4.",
            ),
            make_true_false(
                "4101question_2",
                "1/2 et 2/4 représentent la même quantité.",
                True,
                "Oui, 1/2 = 2/4 : on a simplement multiplié numérateur et dénominateur par 2.",
            ),
            make_qcm(
                "4101question_3",
                "Comment calcule-t-on correctement 2/3 + 1/6 ?",
                [
                    "Je prends 6 comme dénominateur commun, puis 2/3 devient 4/6 et j'obtiens 5/6.",
                    "J'additionne 2+1 et 3+6 pour obtenir 3/9.",
                    "Je multiplie directement les deux fractions.",
                    "Je garde 2/3 sans transformation puis j'ajoute 1/6 à part.",
                ],
                "Je prends 6 comme dénominateur commun, puis 2/3 devient 4/6 et j'obtiens 5/6.",
                "La bonne méthode consiste à chercher un dénominateur commun puis à additionner les numérateurs.",
            ),
            make_qcm(
                "4101question_4",
                "Quel est le résultat de 5/8 + 1/8 ?",
                ["6/8", "5/16", "6/16", "4/8"],
                "6/8",
                "Quand les dénominateurs sont identiques, on additionne seulement les numérateurs : 5 + 1 = 6.",
            ),
            make_true_false(
                "4101question_5",
                "Quand les dénominateurs sont identiques, on ne change pas le dénominateur.",
                True,
                "C'est la règle de base : le dénominateur reste le même.",
            ),
            make_qcm(
                "4101question_6",
                "Quelle erreur fréquente faut-il éviter quand on additionne des fractions ?",
                [
                    "Additionner aussi les dénominateurs",
                    "Chercher un dénominateur commun",
                    "Simplifier le résultat final",
                    "Vérifier l'ordre de grandeur",
                ],
                "Additionner aussi les dénominateurs",
                "L'erreur classique consiste à faire 1/2 + 1/3 = 2/5, ce qui est faux.",
            ),
            make_qcm(
                "4101question_7",
                "Quel est le résultat de 2/5 + 1/10 ?",
                ["3/15", "1/2", "3/10", "2/10"],
                "1/2",
                "2/5 = 4/10, donc 4/10 + 1/10 = 5/10 = 1/2.",
            ),
            make_true_false(
                "4101question_8",
                "Après une addition, on peut parfois simplifier la fraction obtenue.",
                True,
                "Par exemple 6/8 peut être simplifiée en 3/4.",
            ),
        ],
    ),
    (
        4102,
        "Soustraction de fractions",
        "Mathématiques",
        "4eme",
        [
            {
                "notion": "Soustraction avec même dénominateur",
                "description": "Soustraire les numérateurs lorsque les fractions ont déjà le même dénominateur.",
            },
            {
                "notion": "Écriture équivalente",
                "description": "Transformer une fraction pour obtenir un dénominateur commun avant la soustraction.",
            },
            {
                "notion": "Contrôle du résultat",
                "description": "Vérifier que le résultat reste cohérent avec l'ordre de grandeur attendu.",
            },
        ],
        [
            make_qcm(
                "4102question_1",
                "Quel est le résultat de 7/8 - 3/8 ?",
                ["3/8", "4/8", "4/16", "10/8"],
                "4/8",
                "7/8 - 3/8 = 4/8, que l'on peut ensuite simplifier en 1/2.",
            ),
            make_true_false(
                "4102question_2",
                "3/4 - 1/2 = 2/2.",
                False,
                "1/2 vaut 2/4, donc 3/4 - 2/4 = 1/4 et non 2/2.",
            ),
            make_qcm(
                "4102question_3",
                "Comment calcule-t-on correctement 5/6 - 1/3 ?",
                [
                    "Je transforme 1/3 en 2/6, puis je fais 5/6 - 2/6 = 3/6, soit 1/2.",
                    "Je soustrais 5-1 et 6-3 pour obtenir 4/3.",
                    "Je multiplie les dénominateurs et les numérateurs.",
                    "Je garde les fractions telles quelles sans transformation.",
                ],
                "Je transforme 1/3 en 2/6, puis je fais 5/6 - 2/6 = 3/6, soit 1/2.",
                "La démarche correcte passe par un dénominateur commun puis une simplification éventuelle.",
            ),
            make_qcm(
                "4102question_4",
                "Pour calculer 3/5 - 1/10, on peut écrire 3/5 sous la forme...",
                ["6/10", "3/10", "9/10", "4/10"],
                "6/10",
                "3/5 = 6/10 ; cela permet de faire ensuite 6/10 - 1/10.",
            ),
            make_true_false(
                "4102question_5",
                "Pour soustraire deux fractions, on soustrait aussi les dénominateurs.",
                False,
                "C'est faux : on garde le dénominateur commun et on ne soustrait que les numérateurs.",
            ),
            make_qcm(
                "4102question_6",
                "Pourquoi est-il utile d'estimer mentalement le résultat avant de calculer ?",
                [
                    "Pour vérifier que le résultat paraît plausible",
                    "Pour éviter d'écrire les fractions",
                    "Pour ne plus avoir besoin de calculer",
                    "Pour transformer automatiquement les fractions",
                ],
                "Pour vérifier que le résultat paraît plausible",
                "L'estimation évite les erreurs grossières comme obtenir un résultat plus grand après une soustraction.",
            ),
            make_qcm(
                "4102question_7",
                "Quel est le résultat de 9/10 - 1/5 ?",
                ["8/5", "7/5", "7/10", "8/10"],
                "7/10",
                "1/5 = 2/10, donc 9/10 - 2/10 = 7/10.",
            ),
            make_true_false(
                "4102question_8",
                "Une vérification finale peut consister à comparer le résultat à un dessin ou une droite graduée.",
                True,
                "Une représentation visuelle aide à confirmer le sens de la soustraction.",
            ),
        ],
    ),
    (
        4103,
        "Fractions en situation-problème",
        "Mathématiques",
        "4eme",
        [
            {
                "notion": "Traduire un énoncé",
                "description": "Repérer les données utiles et les convertir en fractions comparables.",
            },
            {
                "notion": "Sens du résultat",
                "description": "Décider si le résultat final doit être inférieur, supérieur ou égal à 1.",
            },
            {
                "notion": "Résolution progressive",
                "description": "Procéder étape par étape pour éviter les contresens dans un problème.",
            },
        ],
        [
            make_qcm(
                "4103question_1",
                "Lina boit 1/3 d'une bouteille le matin puis 1/6 l'après-midi. Quelle quantité a-t-elle bue au total ?",
                ["1/2", "2/9", "1/9", "2/3"],
                "1/2",
                "1/3 = 2/6, donc 2/6 + 1/6 = 3/6 = 1/2.",
            ),
            make_true_false(
                "4103question_2",
                "4/8 et 1/2 désignent la même portion.",
                True,
                "On simplifie 4/8 en divisant par 4 : on obtient 1/2.",
            ),
            make_qcm(
                "4103question_3",
                "Un élève a déjà résolu 3/4 d'un exercice puis encore 1/8. Quelle fraction de l'exercice est terminée ?",
                [
                    "7/8",
                    "4/12",
                    "3/32",
                    "1 entier",
                ],
                "7/8",
                "L'essentiel est de passer à un même dénominateur avant d'additionner.",
            ),
            make_qcm(
                "4103question_4",
                "Quel dénominateur commun est pertinent pour 1/6 et 5/9 ?",
                ["9", "15", "18", "54"],
                "18",
                "18 est le plus petit multiple commun de 6 et 9.",
            ),
            make_true_false(
                "4103question_5",
                "On peut simplifier une fraction en divisant le numérateur et le dénominateur par un même nombre non nul.",
                True,
                "Cette opération conserve la valeur de la fraction.",
            ),
            make_qcm(
                "4103question_6",
                "À quoi sert le dénominateur commun dans un problème sur les fractions ?",
                [
                    "À comparer ou additionner des parts dans la même unité",
                    "À supprimer les fractions de l'énoncé",
                    "À éviter toute simplification",
                    "À rendre toutes les réponses égales à 1",
                ],
                "À comparer ou additionner des parts dans la même unité",
                "Sans dénominateur commun, on additionne des quantités qui ne sont pas directement comparables.",
            ),
            make_qcm(
                "4103question_7",
                "Quel est le résultat de 5/12 + 1/4 ?",
                ["6/16", "6/12", "8/12", "2/3"],
                "2/3",
                "1/4 = 3/12, donc 5/12 + 3/12 = 8/12 = 2/3.",
            ),
            make_true_false(
                "4103question_8",
                "Avant de répondre, estimer si le résultat final est inférieur ou supérieur à 1 aide à éviter les erreurs.",
                True,
                "Le contrôle d'ordre de grandeur est un très bon réflexe de 4e.",
            ),
        ],
    ),
]

ADDITIONAL_THEMES = [
    (4104, "Nombres relatifs et repérage"),
    (4105, "Calcul littéral"),
    (4106, "Équations simples"),
    (4107, "Puissances"),
    (4108, "Théorème de Pythagore - initiation"),
    (4109, "Statistiques"),
    (4110, "Probabilités simples"),
]


def build_generic_quiz(theme_id, theme_title):
    notions = [
        {"notion": "Méthode", "description": f"Identifier la bonne méthode sur le thème '{theme_title}'."},
        {"notion": "Vérification", "description": "Contrôler le résultat par une estimation ou une relecture logique."},
        {"notion": "Justification", "description": "Être capable d'expliquer la démarche utilisée."},
    ]
    questions = [
        make_qcm(
            f"{theme_id}question_1",
            f"En mathématiques 4e, quel réflexe aide le plus à réussir un exercice sur '{theme_title}' ?",
            [
                "Repérer la notion et organiser la démarche",
                "Répondre au hasard",
                "Ignorer les unités et les indices",
                "Aller vite sans vérifier",
            ],
            "Repérer la notion et organiser la démarche",
            "Identifier la notion permet de choisir l'outil adapté et d'éviter les erreurs de méthode.",
        ),
        make_true_false(
            f"{theme_id}question_2",
            f"Relire le calcul ou la figure avant de valider peut aider sur '{theme_title}'.",
            True,
            "Une vérification finale permet de repérer un signe oublié ou un résultat incohérent.",
        ),
        make_qcm(
            f"{theme_id}question_3",
            f"Quelle habitude aide à progresser sur '{theme_title}' ?",
            [
                "Refaire un exemple corrigé puis s'entraîner seul",
                "Mémoriser sans comprendre",
                "Éviter les exercices d'application",
                "Changer de méthode sans raison",
            ],
            "Refaire un exemple corrigé puis s'entraîner seul",
            "Le passage guidé vers l'autonomie consolide la compréhension durablement.",
        ),
        make_true_false(
            f"{theme_id}question_4",
            f"La justification de la démarche est inutile si le résultat semble juste sur '{theme_title}'.",
            False,
            "La justification montre que l'élève comprend vraiment ce qu'il fait.",
        ),
        make_qcm(
            f"{theme_id}question_5",
            f"Quel indicateur montre une bonne maîtrise du thème '{theme_title}' ?",
            [
                "Savoir expliquer chaque étape du raisonnement",
                "Répondre très vite sans vérifier",
                "Oublier les erreurs précédentes",
                "Réciter sans exemple",
            ],
            "Savoir expliquer chaque étape du raisonnement",
            "L'explication du raisonnement est un vrai signe de compréhension en 4e.",
        ),
        make_true_false(
            f"{theme_id}question_6",
            f"Faire un schéma, un tableau ou un brouillon peut aider à réussir sur '{theme_title}'.",
            True,
            "Ces outils aident à organiser les données et à clarifier le raisonnement.",
        ),
        make_qcm(
            f"{theme_id}question_7",
            f"Si tu bloques sur une question liée à '{theme_title}', quel réflexe est le plus pertinent ?",
            [
                "Revenir à l'énoncé et isoler l'étape bloquante",
                "Abandonner immédiatement",
                "Inventer une réponse",
                "Supprimer les données gênantes",
            ],
            "Revenir à l'énoncé et isoler l'étape bloquante",
            "Repérer le point précis de blocage permet souvent de relancer la résolution.",
        ),
        make_true_false(
            f"{theme_id}question_8",
            f"Corriger ses erreurs après l'exercice aide à progresser sur '{theme_title}'.",
            True,
            "Le retour sur erreur améliore la précision et renforce la mémoire de la méthode.",
        ),
    ]
    return (theme_id, theme_title, "Mathématiques", "4eme", notions, questions)


quizzes_data.extend(build_generic_quiz(theme_id, theme_title) for theme_id, theme_title in ADDITIONAL_THEMES)


def normalize_question_type(question_type):
    qtype = str(question_type or "").strip().lower().replace("_", "-")
    if qtype in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if qtype == "qcm":
        return "qcm"
    return "vrai-faux"


def build_true_false_statement(question_text, correct_answer, explanation):
    answer = str(correct_answer or "").strip().rstrip(".!? ")
    detail = str(explanation or "").strip()
    if answer:
        return f"La bonne réponse attendue est : {answer}."
    if detail:
        return detail if detail.endswith((".", "!", "?")) else f"{detail}."
    prompt = str(question_text or "").strip()
    return prompt if prompt else "Cette affirmation est à évaluer."


def resolve_qcm_answer(question):
    options = list(question.get("options", []))
    raw_answer = str(question.get("correct_option", question.get("correct_answer", ""))).strip()
    letter_map = {"A": 0, "B": 1, "C": 2, "D": 3}

    if raw_answer.upper() in letter_map:
        option_index = letter_map[raw_answer.upper()]
        if option_index < len(options):
            return options[option_index]

    return raw_answer


def make_quiz(qid, title, subject, level, notions, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []

    for question in questions:
        raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
        if qtype == "qcm":
            runtime_questions.append(
                {
                    "type": "qcm",
                    "question": str(question.get("question", "")),
                    "choices": list(question.get("options", [])),
                }
            )
        else:
            question_text = str(question.get("question", ""))
            if raw_type not in {"vrai-faux", "vrai faux"}:
                question_text = build_true_false_statement(
                    question.get("question", ""),
                    question.get("correct_answer", ""),
                    question.get("explanation", ""),
                )
            runtime_questions.append(
                {
                    "type": "vrai-faux",
                    "question": question_text,
                }
            )

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Série {qid}",
            "type": "quiz",
            "level": level,
            "subject": subject,
            "description": f"Diagnostic {subject} {level} : {title}",
            "status": "published",
            "created_at": created_at,
            "updated_at": created_at,
        },
        "quiz": {
            "title": title,
            "type": "quiz",
            "level": level,
            "subject": subject,
            "question_count": len(runtime_questions),
            "passing_score": 70,
            "time_limit_minutes": 15,
            "questions": runtime_questions,
        },
        "exercisenotion": notions,
        "exerciseresponses": [],
    }


def make_answers(qid, title, subject, level, questions):
    answers = []

    for index, question in enumerate(questions):
        raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
        if qtype == "qcm":
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "qcm",
                    "answer": resolve_qcm_answer(question),
                    "correction": question.get("explanation", ""),
                }
            )
        else:
            if raw_type in {"vrai-faux", "vrai faux"}:
                tf_answer = "vrai" if question.get("correct", False) else "faux"
            else:
                tf_answer = "vrai"
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "vrai-faux",
                    "answer": tf_answer,
                    "correction": question.get("explanation", ""),
                }
            )

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Série {qid}",
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


def verify_random_sentinel():
    if not quizzes_data:
        print("[sentinel] skipped: no quiz data")
        return

    sentinel_qid, _, _, _, _, _ = random.choice(quizzes_data)
    quiz_path = os.path.join(QUIZ_DIR, f"{sentinel_qid}.json")
    answers_path = os.path.join(ANSWERS_DIR, f"{sentinel_qid}.json")

    with open(quiz_path, "r", encoding="utf-8") as file_obj:
        quiz_payload = json.load(file_obj)

    with open(answers_path, "r", encoding="utf-8") as file_obj:
        answers_payload = json.load(file_obj)

    questions = list(quiz_payload.get("quiz", {}).get("questions", []))
    answers = list(answers_payload.get("quiz", {}).get("answers", []))

    if len(questions) != len(answers):
        raise ValueError(
            f"[sentinel] mismatch for quiz {sentinel_qid}: questions={len(questions)} answers={len(answers)}"
        )

    allowed_types = {"qcm", "vrai-faux"}
    for index, question in enumerate(questions):
        question_type = str(question.get("type", ""))
        if question_type not in allowed_types:
            raise ValueError(
                f"[sentinel] invalid question type for quiz {sentinel_qid} at index {index}: {question_type}"
            )

    print(f"[sentinel] OK quiz={sentinel_qid} questions={len(questions)} answers={len(answers)}")


def write_quiz_files():
    for directory in (QUIZ_DIR, ANSWERS_DIR, RUNTIME_QUIZ_DIR, RUNTIME_ANSWERS_DIR):
        os.makedirs(directory, exist_ok=True)

    for qid, title, subject, level, notions, questions in quizzes_data:
        quiz_obj = make_quiz(qid, title, subject, level, notions, questions)
        answers_obj = make_answers(qid, title, subject, level, questions)

        for target_dir in (QUIZ_DIR, RUNTIME_QUIZ_DIR):
            with open(os.path.join(target_dir, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as file_obj:
                json.dump(quiz_obj, file_obj, ensure_ascii=False, indent=2)
                file_obj.write("\n")

        for target_dir in (ANSWERS_DIR, RUNTIME_ANSWERS_DIR):
            with open(os.path.join(target_dir, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as file_obj:
                json.dump(answers_obj, file_obj, ensure_ascii=False, indent=2)
                file_obj.write("\n")

    print(f"{len(quizzes_data)} quiz générés dans {OUTPUT_DIR} et synchronisés vers le runtime")
    verify_random_sentinel()


if __name__ == "__main__":
    write_quiz_files()
