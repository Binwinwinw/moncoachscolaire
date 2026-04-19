#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Générateur des quiz diagnostics de Mathématiques pour la 1ère."""

from __future__ import annotations

import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

OUTPUT_DIR = os.path.join(SCRIPT_DIR, "1ere_mathematiques_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

for target_dir in (QUIZ_DIR, ANSWERS_DIR, RUNTIME_QUIZ_DIR, RUNTIME_ANSWERS_DIR):
    os.makedirs(target_dir, exist_ok=True)


quizzes_data = [
    (
        1059,
        "Mathématiques 1ère - Résoudre une équation du second degré",
        "Mathématiques",
        "1ere",
        [
            {
                "id": "1059_1",
                "type": "qcm",
                "question": "Quelle est la valeur du discriminant de x² - 4x + 3 = 0 ?",
                "choices": ["1", "4", "8", "16"],
                "correct_option": "4",
                "explanation": "On calcule Δ = b² - 4ac = (-4)² - 4×1×3 = 16 - 12 = 4.",
            },
            {
                "id": "1059_2",
                "type": "vrai-faux",
                "question": "Si le discriminant d'une équation du second degré est négatif, il n'y a pas de solution réelle.",
                "correct": True,
                "explanation": "Un discriminant négatif indique l'absence de racines réelles.",
            },
            {
                "id": "1059_3",
                "type": "qcm",
                "question": "Quelles sont les solutions de x² - 5x + 6 = 0 ?",
                "choices": ["1 et 6", "2 et 3", "-2 et -3", "0 et 6"],
                "correct_option": "2 et 3",
                "explanation": "On factorise x² - 5x + 6 en (x - 2)(x - 3).",
            },
            {
                "id": "1059_4",
                "type": "vrai-faux",
                "question": "L'équation x² - 6x + 9 = 0 possède une racine double.",
                "correct": True,
                "explanation": "Elle s'écrit (x - 3)² = 0 : la racine 3 est double.",
            },
            {
                "id": "1059_5",
                "type": "qcm",
                "question": "Quel est l'axe de symétrie de la parabole y = x² - 4x + 7 ?",
                "choices": ["x = -2", "x = 0", "x = 2", "x = 4"],
                "correct_option": "x = 2",
                "explanation": "L'axe de symétrie vaut x = -b/(2a) = 4/2 = 2.",
            },
            {
                "id": "1059_6",
                "type": "vrai-faux",
                "question": "Le sommet de la parabole y = x² - 4x + 7 est le point (2 ; 3).",
                "correct": True,
                "explanation": "En x = 2, on obtient y = 4 - 8 + 7 = 3.",
            },
            {
                "id": "1059_7",
                "type": "qcm",
                "question": "Quelle écriture factorisée correspond à x² - 9 ?",
                "choices": ["(x - 9)²", "(x - 3)(x + 3)", "(x - 1)(x - 9)", "(x + 9)²"],
                "correct_option": "(x - 3)(x + 3)",
                "explanation": "C'est une différence de deux carrés : x² - 3² = (x - 3)(x + 3).",
            },
            {
                "id": "1059_8",
                "type": "vrai-faux",
                "question": "Remplacer une solution trouvée dans l'équation de départ permet de vérifier le résultat.",
                "correct": True,
                "explanation": "La vérification par substitution est un bon réflexe pour éviter une erreur de calcul.",
            },
        ],
    ),
    (
        1060,
        "Mathématiques 1ère - Factoriser une expression",
        "Mathématiques",
        "1ere",
        [
            {
                "id": "1060_1",
                "type": "qcm",
                "question": "Quelle est la forme factorisée de 3x² + 6x ?",
                "choices": ["3x(x + 2)", "3(x² + 2)", "x(3x + 6)", "6x(x + 3)"],
                "correct_option": "3x(x + 2)",
                "explanation": "On met 3x en facteur commun : 3x² + 6x = 3x(x + 2).",
            },
            {
                "id": "1060_2",
                "type": "vrai-faux",
                "question": "On a x² + 2x + 1 = (x + 1)².",
                "correct": True,
                "explanation": "C'est l'identité remarquable a² + 2ab + b² = (a + b)².",
            },
            {
                "id": "1060_3",
                "type": "qcm",
                "question": "Comment factoriser x² - 16 ?",
                "choices": ["(x - 4)(x + 4)", "(x - 8)(x + 2)", "(x - 4)²", "x(x - 16)"],
                "correct_option": "(x - 4)(x + 4)",
                "explanation": "x² - 16 = x² - 4², donc on applique la différence de deux carrés.",
            },
            {
                "id": "1060_4",
                "type": "vrai-faux",
                "question": "x² - 6x + 9 se factorise en (x - 3)(x + 3).",
                "correct": False,
                "explanation": "La bonne forme est (x - 3)².",
            },
            {
                "id": "1060_5",
                "type": "qcm",
                "question": "Quelle écriture est correcte pour factoriser x² - x ?",
                "choices": ["x(x - 1)", "(x - 1)²", "x²(1 - x)", "x(x + 1)"],
                "correct_option": "x(x - 1)",
                "explanation": "On met x en facteur commun dans x² - x.",
            },
            {
                "id": "1060_6",
                "type": "vrai-faux",
                "question": "Factoriser une expression peut aider à résoudre plus facilement une équation.",
                "correct": True,
                "explanation": "Une équation factorisée permet souvent d'appliquer la règle du produit nul.",
            },
            {
                "id": "1060_7",
                "type": "qcm",
                "question": "Quelle est la forme factorisée de 4x² - 12x + 9 ?",
                "choices": ["(2x - 3)²", "(4x - 9)(x - 1)", "(2x + 3)²", "x(4x - 12) + 9"],
                "correct_option": "(2x - 3)²",
                "explanation": "C'est l'identité remarquable a² - 2ab + b² avec a = 2x et b = 3.",
            },
            {
                "id": "1060_8",
                "type": "vrai-faux",
                "question": "Pour vérifier une factorisation, on peut développer le résultat trouvé.",
                "correct": True,
                "explanation": "Le développement permet de contrôler qu'on retrouve l'expression de départ.",
            },
        ],
    ),
    (
        1061,
        "Mathématiques 1ère - Étudier une fonction",
        "Mathématiques",
        "1ere",
        [
            {
                "id": "1061_1",
                "type": "qcm",
                "question": "Si f(x) = 2x + 1, quelle est l'image de 2 ?",
                "choices": ["3", "4", "5", "6"],
                "correct_option": "5",
                "explanation": "On calcule f(2) = 2×2 + 1 = 5.",
            },
            {
                "id": "1061_2",
                "type": "vrai-faux",
                "question": "Le point (1 ; 3) appartient à la courbe de la fonction f(x) = 2x + 1.",
                "correct": True,
                "explanation": "Comme f(1) = 3, le point (1 ; 3) appartient bien à la courbe.",
            },
            {
                "id": "1061_3",
                "type": "qcm",
                "question": "Sur l'intervalle [0 ; +∞[, la fonction carré x ↦ x² est :",
                "choices": ["constante", "croissante", "décroissante", "négative"],
                "correct_option": "croissante",
                "explanation": "Quand x augmente sur [0 ; +∞[, son carré augmente aussi.",
            },
            {
                "id": "1061_4",
                "type": "vrai-faux",
                "question": "Une fonction affine de coefficient directeur négatif est décroissante.",
                "correct": True,
                "explanation": "Un coefficient directeur négatif traduit une baisse quand x augmente.",
            },
            {
                "id": "1061_5",
                "type": "qcm",
                "question": "Quel est l'antécédent de 0 par la fonction f(x) = x - 4 ?",
                "choices": ["0", "1", "4", "-4"],
                "correct_option": "4",
                "explanation": "On résout x - 4 = 0, donc x = 4.",
            },
            {
                "id": "1061_6",
                "type": "vrai-faux",
                "question": "Un tableau de valeurs peut aider à tracer ou lire une courbe de fonction.",
                "correct": True,
                "explanation": "Il permet d'associer plusieurs x à leurs images pour comprendre la courbe.",
            },
            {
                "id": "1061_7",
                "type": "qcm",
                "question": "Si f(0) = 2 et f(1) = 5, que peut-on dire entre 0 et 1 ?",
                "choices": ["La fonction augmente", "La fonction vaut toujours 0", "La fonction est forcément négative", "La fonction est constante"],
                "correct_option": "La fonction augmente",
                "explanation": "Entre ces deux valeurs, l'image passe de 2 à 5 : on observe une augmentation.",
            },
            {
                "id": "1061_8",
                "type": "vrai-faux",
                "question": "Sur un graphique de fonction, on lit les antécédents sur l'axe horizontal.",
                "correct": True,
                "explanation": "Les antécédents correspondent aux abscisses, donc à l'axe horizontal.",
            },
        ],
    ),
    (
        1062,
        "Mathématiques 1ère - Dériver une fonction",
        "Mathématiques",
        "1ere",
        [
            {
                "id": "1062_1",
                "type": "qcm",
                "question": "Quelle est la dérivée de la fonction x ↦ x² ?",
                "choices": ["x", "2x", "x²", "2"],
                "correct_option": "2x",
                "explanation": "La dérivée de x² est 2x.",
            },
            {
                "id": "1062_2",
                "type": "vrai-faux",
                "question": "La dérivée d'une constante est nulle.",
                "correct": True,
                "explanation": "Une constante ne varie pas, sa pente est donc égale à 0.",
            },
            {
                "id": "1062_3",
                "type": "qcm",
                "question": "Quelle est la dérivée de f(x) = 3x + 2 ?",
                "choices": ["2", "3", "3x", "x + 2"],
                "correct_option": "3",
                "explanation": "La dérivée d'une fonction affine ax + b est a.",
            },
            {
                "id": "1062_4",
                "type": "vrai-faux",
                "question": "Si f'(x) est positive sur un intervalle, alors f est croissante sur cet intervalle.",
                "correct": True,
                "explanation": "Le signe positif de la dérivée indique une augmentation de la fonction.",
            },
            {
                "id": "1062_5",
                "type": "qcm",
                "question": "Quelle est la dérivée de la fonction x ↦ x³ ?",
                "choices": ["x²", "2x", "3x²", "3x"],
                "correct_option": "3x²",
                "explanation": "La dérivée de x³ est 3x².",
            },
            {
                "id": "1062_6",
                "type": "vrai-faux",
                "question": "Le nombre dérivé en un point donne la pente de la tangente à la courbe.",
                "correct": True,
                "explanation": "C'est précisément l'interprétation géométrique du nombre dérivé.",
            },
            {
                "id": "1062_7",
                "type": "qcm",
                "question": "Quelle est la dérivée de la fonction x ↦ 1/x sur son domaine de définition ?",
                "choices": ["1/x²", "-1/x²", "x", "-x"],
                "correct_option": "-1/x²",
                "explanation": "Sur x ≠ 0, la dérivée de x⁻¹ est -x⁻² = -1/x².",
            },
            {
                "id": "1062_8",
                "type": "vrai-faux",
                "question": "Quand f'(a) = 0, le point d'abscisse a peut correspondre à un extremum local.",
                "correct": True,
                "explanation": "Une dérivée nulle peut signaler un maximum, un minimum ou un point particulier à étudier.",
            },
        ],
    ),
    (
        1063,
        "Mathématiques 1ère - Utiliser le produit scalaire",
        "Mathématiques",
        "1ere",
        [
            {
                "id": "1063_1",
                "type": "qcm",
                "question": "Si deux vecteurs sont perpendiculaires, leur produit scalaire vaut :",
                "choices": ["0", "1", "-1", "leur norme"],
                "correct_option": "0",
                "explanation": "Avec un angle droit, cos(90°) = 0, donc le produit scalaire est nul.",
            },
            {
                "id": "1063_2",
                "type": "vrai-faux",
                "question": "Un produit scalaire positif indique un angle aigu entre deux vecteurs.",
                "correct": True,
                "explanation": "Quand l'angle est aigu, son cosinus est positif.",
            },
            {
                "id": "1063_3",
                "type": "qcm",
                "question": "Quelle formule relie le produit scalaire à l'angle entre deux vecteurs ?",
                "choices": ["u·v = ||u|| ||v|| cos(θ)", "u·v = ||u|| + ||v||", "u·v = sin(θ)", "u·v = θ²"],
                "correct_option": "u·v = ||u|| ||v|| cos(θ)",
                "explanation": "C'est la formule géométrique de référence du produit scalaire.",
            },
            {
                "id": "1063_4",
                "type": "vrai-faux",
                "question": "Si l'angle entre deux vecteurs vaut 90°, alors cos(90°) = 1.",
                "correct": False,
                "explanation": "cos(90°) = 0, pas 1.",
            },
            {
                "id": "1063_5",
                "type": "qcm",
                "question": "Dans un repère, si u = (1 ; 2) et v = (3 ; 4), combien vaut u·v ?",
                "choices": ["7", "10", "11", "14"],
                "correct_option": "11",
                "explanation": "u·v = 1×3 + 2×4 = 3 + 8 = 11.",
            },
            {
                "id": "1063_6",
                "type": "vrai-faux",
                "question": "Le produit scalaire peut être utilisé pour prouver l'orthogonalité de deux vecteurs.",
                "correct": True,
                "explanation": "Quand le produit scalaire est nul, on peut conclure à la perpendicularité.",
            },
            {
                "id": "1063_7",
                "type": "qcm",
                "question": "Quel renseignement cherche-t-on souvent à établir avec un produit scalaire ?",
                "choices": ["Un angle ou une perpendicularité", "Le périmètre du cercle", "Une moyenne statistique", "La dérivée d'une fonction"],
                "correct_option": "Un angle ou une perpendicularité",
                "explanation": "Le produit scalaire sert notamment à relier vecteurs, longueurs et angle.",
            },
            {
                "id": "1063_8",
                "type": "vrai-faux",
                "question": "Un produit scalaire peut être négatif.",
                "correct": True,
                "explanation": "C'est le cas lorsque l'angle entre deux vecteurs est obtus.",
            },
        ],
    ),
    (
        1064,
        "Mathématiques 1ère - Étudier une suite numérique",
        "Mathématiques",
        "1ere",
        [
            {
                "id": "1064_1",
                "type": "qcm",
                "question": "Dans une suite arithmétique, la différence entre deux termes consécutifs est :",
                "choices": ["constante", "toujours nulle", "toujours négative", "variable"],
                "correct_option": "constante",
                "explanation": "Une suite arithmétique se construit en ajoutant toujours la même valeur.",
            },
            {
                "id": "1064_2",
                "type": "vrai-faux",
                "question": "Dans une suite géométrique, on multiplie chaque terme par une même raison pour obtenir le suivant.",
                "correct": True,
                "explanation": "C'est la propriété fondamentale d'une suite géométrique.",
            },
            {
                "id": "1064_3",
                "type": "qcm",
                "question": "Si uₙ = 2n + 1, quelle est la valeur de u₄ ?",
                "choices": ["7", "8", "9", "10"],
                "correct_option": "9",
                "explanation": "u₄ = 2×4 + 1 = 9.",
            },
            {
                "id": "1064_4",
                "type": "vrai-faux",
                "question": "Une suite arithmétique peut être définie par une relation du type uₙ₊₁ = uₙ + r.",
                "correct": True,
                "explanation": "On ajoute la raison r pour passer d'un terme au suivant.",
            },
            {
                "id": "1064_5",
                "type": "qcm",
                "question": "Si une suite géométrique a pour premier terme 3 et pour raison 2, quel est son deuxième terme ?",
                "choices": ["5", "6", "8", "9"],
                "correct_option": "6",
                "explanation": "On multiplie 3 par 2 : on obtient 6.",
            },
            {
                "id": "1064_6",
                "type": "vrai-faux",
                "question": "Une suite croissante peut passer de valeurs négatives à des valeurs positives.",
                "correct": True,
                "explanation": "Le caractère croissant ne dépend pas du signe des termes mais de leur ordre.",
            },
            {
                "id": "1064_7",
                "type": "qcm",
                "question": "Que signifie la relation uₙ₊₁ = uₙ + 5 ?",
                "choices": ["La suite est arithmétique de raison 5", "La suite est géométrique de raison 5", "La suite est constante", "La suite est forcément décroissante"],
                "correct_option": "La suite est arithmétique de raison 5",
                "explanation": "Chaque terme s'obtient en ajoutant 5 au précédent.",
            },
            {
                "id": "1064_8",
                "type": "vrai-faux",
                "question": "Calculer les premiers termes d'une suite aide à conjecturer son comportement.",
                "correct": True,
                "explanation": "C'est une bonne méthode pour observer une tendance avant de la démontrer.",
            },
        ],
    ),
    (
        1065,
        "Mathématiques 1ère - Probabilités conditionnelles",
        "Mathématiques",
        "1ere",
        [
            {
                "id": "1065_1",
                "type": "qcm",
                "question": "La notation P(A ∩ B) désigne :",
                "choices": ["la probabilité de A ou B", "la probabilité de A et B", "la probabilité contraire de A", "la probabilité de A sachant B"],
                "correct_option": "la probabilité de A et B",
                "explanation": "L'intersection A ∩ B correspond à la réalisation simultanée des événements A et B.",
            },
            {
                "id": "1065_2",
                "type": "vrai-faux",
                "question": "Si deux événements sont indépendants, alors P(A ∩ B) = P(A) × P(B).",
                "correct": True,
                "explanation": "C'est la propriété caractéristique de l'indépendance de deux événements.",
            },
            {
                "id": "1065_3",
                "type": "qcm",
                "question": "Si P(A) = 0,4 et P(B sachant A) = 0,5, alors P(A ∩ B) vaut :",
                "choices": ["0,1", "0,2", "0,5", "0,9"],
                "correct_option": "0,2",
                "explanation": "On applique P(A ∩ B) = P(A) × P(B sachant A) = 0,4 × 0,5 = 0,2.",
            },
            {
                "id": "1065_4",
                "type": "vrai-faux",
                "question": "Un arbre pondéré peut aider à calculer une probabilité conditionnelle.",
                "correct": True,
                "explanation": "L'arbre pondéré est l'outil classique pour organiser les cas et lire les probabilités.",
            },
            {
                "id": "1065_5",
                "type": "qcm",
                "question": "Deux événements incompatibles peuvent-ils se réaliser en même temps ?",
                "choices": ["Oui, toujours", "Oui, parfois", "Non, jamais", "Seulement si leur somme vaut 1"],
                "correct_option": "Non, jamais",
                "explanation": "Des événements incompatibles ne peuvent pas se produire simultanément.",
            },
            {
                "id": "1065_6",
                "type": "vrai-faux",
                "question": "La somme des probabilités de toutes les issues d'une expérience vaut 1.",
                "correct": True,
                "explanation": "La probabilité totale d'un univers certain est toujours égale à 1.",
            },
            {
                "id": "1065_7",
                "type": "qcm",
                "question": "Si P(A) = 0,7 et P(A barre) désigne l'événement contraire, alors P(A barre) vaut :",
                "choices": ["0,3", "0,7", "1,7", "0"],
                "correct_option": "0,3",
                "explanation": "La probabilité de l'événement contraire vaut 1 - 0,7 = 0,3.",
            },
            {
                "id": "1065_8",
                "type": "vrai-faux",
                "question": "Une probabilité ne peut jamais être négative.",
                "correct": True,
                "explanation": "Par définition, une probabilité appartient toujours à l'intervalle [0 ; 1].",
            },
        ],
    ),
    (
        1066,
        "Mathématiques 1ère - Trigonométrie et cercle trigonométrique",
        "Mathématiques",
        "1ere",
        [
            {
                "id": "1066_1",
                "type": "qcm",
                "question": "Sur le cercle trigonométrique, l'angle π radians correspond à :",
                "choices": ["90°", "120°", "180°", "360°"],
                "correct_option": "180°",
                "explanation": "π radians correspondent à un demi-tour, soit 180°.",
            },
            {
                "id": "1066_2",
                "type": "vrai-faux",
                "question": "Les angles 0 et 2π radians ont le même point image sur le cercle trigonométrique.",
                "correct": True,
                "explanation": "Ils diffèrent d'un tour complet et représentent donc le même point.",
            },
            {
                "id": "1066_3",
                "type": "qcm",
                "question": "Quelle est la mesure en radians de 90° ?",
                "choices": ["π/6", "π/4", "π/2", "π"],
                "correct_option": "π/2",
                "explanation": "90° est un quart de tour, donc π/2 radians.",
            },
            {
                "id": "1066_4",
                "type": "vrai-faux",
                "question": "Sur le cercle trigonométrique, le cosinus d'un angle correspond à l'abscisse du point associé.",
                "correct": True,
                "explanation": "Dans le repère du cercle trigonométrique, le cosinus donne l'abscisse et le sinus l'ordonnée.",
            },
            {
                "id": "1066_5",
                "type": "qcm",
                "question": "Quel angle est associé au point le plus haut du cercle trigonométrique ?",
                "choices": ["0", "π/2", "π", "3π/2"],
                "correct_option": "π/2",
                "explanation": "Le point le plus haut a pour coordonnées (0 ; 1), soit l'angle π/2.",
            },
            {
                "id": "1066_6",
                "type": "vrai-faux",
                "question": "Le sinus d'un angle peut être négatif.",
                "correct": True,
                "explanation": "Dans la partie basse du cercle trigonométrique, l'ordonnée est négative.",
            },
            {
                "id": "1066_7",
                "type": "qcm",
                "question": "À quel angle en degrés correspond 3π/2 radians ?",
                "choices": ["180°", "270°", "300°", "360°"],
                "correct_option": "270°",
                "explanation": "3π/2 radians correspondent à trois quarts de tour, soit 270°.",
            },
            {
                "id": "1066_8",
                "type": "vrai-faux",
                "question": "Les angles peuvent être exprimés en degrés ou en radians.",
                "correct": True,
                "explanation": "Ce sont deux unités usuelles pour mesurer un angle en trigonométrie.",
            },
        ],
    ),
]


def normalize_question_type(question_type: str) -> str:
    return "qcm" if str(question_type or "").strip().lower() == "qcm" else "vrai-faux"


def make_quiz(qid, title, subject, level, questions):
    timestamp = datetime.now(UTC).isoformat().replace("+00:00", "Z")
    public_questions = []

    for question in questions:
        qtype = normalize_question_type(question.get("type", ""))
        payload = {
            "id": question["id"],
            "type": qtype,
            "question": question["question"],
        }
        if qtype == "qcm":
            payload["choices"] = list(question.get("choices", []))
        public_questions.append(payload)

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Série {qid}",
            "type": "quiz",
            "level": level,
            "subject": subject,
            "description": f"Diagnostic {subject} {level} : {title}",
            "status": "published",
            "created_at": timestamp,
            "updated_at": timestamp,
            "source": "MonCoachScolaire",
            "programme_ref": "Mathématiques 1ère",
        },
        "quiz": {
            "title": title,
            "type": "quiz",
            "level": level,
            "subject": subject,
            "question_count": len(public_questions),
            "passing_score": 70,
            "time_limit_minutes": 15,
            "questions": public_questions,
        },
        "exercisenotion": [],
        "exerciseresponses": [],
    }


def make_answers(qid, title, subject, level, questions):
    answers = []

    for index, question in enumerate(questions):
        qtype = normalize_question_type(question.get("type", ""))
        answer_item = {
            "index": index,
            "question_id": index + 1,
            "type": qtype,
            "correction": str(question.get("explanation", "")),
        }

        if qtype == "qcm":
            choices = list(question.get("choices", []))
            correct_option = str(question.get("correct_option", ""))
            answer_item["answer"] = correct_option
            answer_item["correct"] = choices.index(correct_option) if correct_option in choices else 0
        else:
            correct_value = question.get("correct", False)
            if isinstance(correct_value, str):
                correct_value = correct_value.strip().lower() in {"vrai", "true", "1", "yes"}
            answer_item["answer"] = "vrai" if correct_value else "faux"

        answers.append(answer_item)

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


def write_json_file(path, payload):
    with open(path, "w", encoding="utf-8", newline="\n") as file_handle:
        json.dump(payload, file_handle, ensure_ascii=False, indent=2)


def verify_random_sentinel():
    qid, _, _, _, _ = random.choice(quizzes_data)
    quiz_path = os.path.join(QUIZ_DIR, f"{qid}.json")
    answers_path = os.path.join(ANSWERS_DIR, f"{qid}.json")

    with open(quiz_path, encoding="utf-8") as quiz_file:
        quiz_object = json.load(quiz_file)
    with open(answers_path, encoding="utf-8") as answers_file:
        answers_object = json.load(answers_file)

    questions = quiz_object["quiz"]["questions"]
    answers = answers_object["quiz"]["answers"]
    allowed = {"qcm", "vrai-faux"}

    assert len(questions) == len(answers), (
        f"[sentinel] FAIL quiz={qid}: {len(questions)} questions vs {len(answers)} réponses"
    )
    for question in questions:
        assert question["type"] in allowed, f"[sentinel] FAIL quiz={qid}: type inconnu {question['type']}"

    print(f"[sentinel] OK quiz={qid} questions={len(questions)} answers={len(answers)}")


def write_quiz_files():
    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(qid, title, subject, level, questions)
        answers_payload = make_answers(qid, title, subject, level, questions)

        write_json_file(os.path.join(QUIZ_DIR, f"{qid}.json"), quiz_payload)
        write_json_file(os.path.join(ANSWERS_DIR, f"{qid}.json"), answers_payload)
        write_json_file(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), quiz_payload)
        write_json_file(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), answers_payload)

    print(f"{len(quizzes_data)} quiz générés dans {OUTPUT_DIR}")
    verify_random_sentinel()


if __name__ == "__main__":
    write_quiz_files()

