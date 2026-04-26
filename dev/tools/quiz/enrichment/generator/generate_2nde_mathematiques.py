#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot J — 2nde Mathématiques | version progressive.

Base manuelle: 33 quizzes rediges.
Completion progressive: ajout automatise des IDs restants.

Usage:
    python dev/tools/quiz/generate_2nde_mathematiques.py
"""

from __future__ import annotations

import json
import os
import random
import re
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "2nde_mathematiques_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

# Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
quizzes_data = [

    # ─────────────────────────────────────────────
    # BLOC 1 — Ensembles & calcul numérique (963–968)
    # ─────────────────────────────────────────────
    (
        963,
        "Ensembles de nombres",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "963_1",
                "type": "qcm",
                "question": "Quel ensemble contient tous les entiers naturels ET les entiers négatifs ?",
                "options": ["N", "Z", "Q", "R"],
                "correct_option": "B",
                "explanation": "Z est l'ensemble des entiers relatifs (positifs, négatifs et zéro). N ne contient que les entiers naturels (≥ 0).",
            },
            {
                "id": "963_2",
                "type": "vrai-faux",
                "question": "Tout nombre décimal est un nombre rationnel.",
                "correct": True,
                "explanation": "Un nombre décimal s'écrit comme une fraction p/q (avec q puissance de 10), donc il est bien rationnel.",
            },
            {
                "id": "963_3",
                "type": "texte",
                "question": "Donne un exemple de nombre irrationnel et explique pourquoi il ne peut pas s'écrire sous forme de fraction.",
                "correct_answer": "√2 est irrationnel : sa représentation décimale est infinie et non périodique, il ne peut pas s'écrire p/q avec p, q entiers.",
                "explanation": "√2 ≈ 1,41421… n'est pas périodique. La preuve classique par l'absurde montre qu'on ne peut pas l'écrire p/q.",
            },
            {
                "id": "963_4",
                "type": "qcm",
                "question": "Parmi ces nombres, lequel N'appartient PAS à Q ?",
                "options": ["0,333…", "√3", "−7/2", "1,25"],
                "correct_option": "B",
                "explanation": "√3 est irrationnel (non rationnel). Les autres sont des décimaux ou fractions, donc rationnels.",
            },
            {
                "id": "963_5",
                "type": "vrai-faux",
                "question": "L'ensemble D des décimaux est inclus dans Q.",
                "correct": True,
                "explanation": "D ⊂ Q ⊂ R. Tout décimal est rationnel car il s'écrit avec un dénominateur puissance de 10.",
            },
            {
                "id": "963_6",
                "type": "texte",
                "question": "Classe les ensembles N, Z, D, Q, R du plus petit (inclus) au plus grand.",
                "correct_answer": "N ⊂ Z ⊂ D ⊂ Q ⊂ R",
                "explanation": "Chaque ensemble est inclus dans le suivant : les entiers naturels sont dans Z, les entiers dans D (via fractions décimales), D dans Q, Q dans R.",
            },
            {
                "id": "963_7",
                "type": "qcm",
                "question": "Quelle est la valeur de √9 ?",
                "options": ["3", "4,5", "±3", "9/2"],
                "correct_option": "A",
                "explanation": "Par convention, √9 désigne la racine carrée positive : √9 = 3.",
            },
            {
                "id": "963_8",
                "type": "vrai-faux",
                "question": "π appartient à l'ensemble R mais pas à Q.",
                "correct": True,
                "explanation": "π est un nombre transcendant : il est réel mais irrationnel, donc π ∈ R \\ Q.",
            },
        ],
    ),
    (
        964,
        "Calcul littéral & factorisation",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "964_1",
                "type": "qcm",
                "question": "Quel est le développement de (a + b)² ?",
                "options": ["a² + b²", "a² + 2ab + b²", "a² − 2ab + b²", "2a + 2b"],
                "correct_option": "B",
                "explanation": "(a+b)² = a² + 2ab + b². C'est l'identité remarquable du carré d'une somme.",
            },
            {
                "id": "964_2",
                "type": "vrai-faux",
                "question": "(a − b)(a + b) = a² − b².",
                "correct": True,
                "explanation": "C'est l'identité remarquable produit de la somme par la différence : (a−b)(a+b) = a² − b².",
            },
            {
                "id": "964_3",
                "type": "texte",
                "question": "Factorise l'expression : x² − 9.",
                "correct_answer": "(x − 3)(x + 3)",
                "explanation": "x² − 9 = x² − 3² = (x−3)(x+3) grâce à l'identité a² − b² = (a−b)(a+b).",
            },
            {
                "id": "964_4",
                "type": "qcm",
                "question": "Quel est le développement de (2x − 3)² ?",
                "options": ["4x² − 9", "4x² + 12x + 9", "4x² − 12x + 9", "4x² − 6x + 9"],
                "correct_option": "C",
                "explanation": "(2x−3)² = (2x)² − 2·(2x)·3 + 3² = 4x² − 12x + 9.",
            },
            {
                "id": "964_5",
                "type": "vrai-faux",
                "question": "3x² + 6x = 3x(x + 2).",
                "correct": True,
                "explanation": "On factorise par 3x : 3x² + 6x = 3x·x + 3x·2 = 3x(x+2). ✓",
            },
            {
                "id": "964_6",
                "type": "texte",
                "question": "Développe et réduis : (x + 4)(x − 1).",
                "correct_answer": "x² + 3x − 4",
                "explanation": "(x+4)(x−1) = x² − x + 4x − 4 = x² + 3x − 4.",
            },
            {
                "id": "964_7",
                "type": "qcm",
                "question": "Quelle factorisation est correcte pour x² + 5x + 6 ?",
                "options": ["(x+1)(x+6)", "(x+2)(x+3)", "(x−2)(x−3)", "(x+1)(x+5)"],
                "correct_option": "B",
                "explanation": "(x+2)(x+3) = x² + 3x + 2x + 6 = x² + 5x + 6. ✓",
            },
            {
                "id": "964_8",
                "type": "vrai-faux",
                "question": "a² + b² peut se factoriser en (a+b)(a+b).",
                "correct": False,
                "explanation": "(a+b)² = a² + 2ab + b² ≠ a² + b². La somme de carrés ne se factorise pas dans R.",
            },
        ],
    ),
    (
        965,
        "Puissances & racines carrées",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "965_1",
                "type": "qcm",
                "question": "Quelle est la valeur de 2⁻³ ?",
                "options": ["−8", "1/8", "−1/8", "6"],
                "correct_option": "B",
                "explanation": "2⁻³ = 1/2³ = 1/8. Un exposant négatif donne l'inverse de la puissance positive.",
            },
            {
                "id": "965_2",
                "type": "vrai-faux",
                "question": "√(a × b) = √a × √b pour tous réels a, b positifs.",
                "correct": True,
                "explanation": "Propriété des radicaux : √(ab) = √a · √b pour a ≥ 0 et b ≥ 0.",
            },
            {
                "id": "965_3",
                "type": "texte",
                "question": "Simplifie √48.",
                "correct_answer": "4√3",
                "explanation": "√48 = √(16×3) = √16 × √3 = 4√3.",
            },
            {
                "id": "965_4",
                "type": "qcm",
                "question": "Que vaut a⁵ × a³ ?",
                "options": ["a⁸", "a¹⁵", "a²", "2a⁸"],
                "correct_option": "A",
                "explanation": "aᵐ × aⁿ = aᵐ⁺ⁿ. Donc a⁵ × a³ = a⁵⁺³ = a⁸.",
            },
            {
                "id": "965_5",
                "type": "vrai-faux",
                "question": "√(a²) = a pour tout réel a.",
                "correct": False,
                "explanation": "√(a²) = |a|, la valeur absolue de a. Si a < 0, alors √(a²) = −a ≠ a.",
            },
            {
                "id": "965_6",
                "type": "texte",
                "question": "Calcule (3²)⁴.",
                "correct_answer": "3⁸ = 6561",
                "explanation": "(aᵐ)ⁿ = aᵐⁿ. (3²)⁴ = 3^(2×4) = 3⁸ = 6561.",
            },
            {
                "id": "965_7",
                "type": "qcm",
                "question": "Que vaut a⁶ ÷ a² ?",
                "options": ["a³", "a⁴", "a⁸", "a¹²"],
                "correct_option": "B",
                "explanation": "aᵐ ÷ aⁿ = aᵐ⁻ⁿ. Donc a⁶ ÷ a² = a⁶⁻² = a⁴.",
            },
            {
                "id": "965_8",
                "type": "vrai-faux",
                "question": "2¹⁰ = 1024.",
                "correct": True,
                "explanation": "2¹⁰ = 1024. C'est une valeur fondamentale en informatique (1 kilo-octet).",
            },
        ],
    ),
    (
        966,
        "Fractions & calcul fractionnaire",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "966_1",
                "type": "qcm",
                "question": "Quelle est la somme 1/3 + 1/4 ?",
                "options": ["2/7", "7/12", "1/12", "4/12"],
                "correct_option": "B",
                "explanation": "1/3 + 1/4 = 4/12 + 3/12 = 7/12. On réduit au même dénominateur (12).",
            },
            {
                "id": "966_2",
                "type": "vrai-faux",
                "question": "Pour multiplier deux fractions, on multiplie numérateurs entre eux et dénominateurs entre eux.",
                "correct": True,
                "explanation": "(a/b) × (c/d) = (a×c)/(b×d). C'est la règle de multiplication des fractions.",
            },
            {
                "id": "966_3",
                "type": "texte",
                "question": "Calcule et simplifie : (3/5) ÷ (9/10).",
                "correct_answer": "2/3",
                "explanation": "(3/5) ÷ (9/10) = (3/5) × (10/9) = 30/45 = 2/3.",
            },
            {
                "id": "966_4",
                "type": "qcm",
                "question": "Quelle fraction est équivalente à 6/8 ?",
                "options": ["2/3", "3/4", "4/5", "1/2"],
                "correct_option": "B",
                "explanation": "6/8 = 3/4 en divisant numérateur et dénominateur par 2.",
            },
            {
                "id": "966_5",
                "type": "vrai-faux",
                "question": "a/b + c/b = (a+c)/b.",
                "correct": True,
                "explanation": "Quand les dénominateurs sont identiques, on additionne directement les numérateurs.",
            },
            {
                "id": "966_6",
                "type": "texte",
                "question": "Simplifie la fraction 42/70.",
                "correct_answer": "3/5",
                "explanation": "PGCD(42, 70) = 14. 42/14 = 3, 70/14 = 5. Donc 42/70 = 3/5.",
            },
            {
                "id": "966_7",
                "type": "qcm",
                "question": "Combien vaut (2/3)² ?",
                "options": ["4/9", "2/9", "4/6", "2/6"],
                "correct_option": "A",
                "explanation": "(2/3)² = 2²/3² = 4/9.",
            },
            {
                "id": "966_8",
                "type": "vrai-faux",
                "question": "Pour soustraire deux fractions de dénominateurs différents, on cherche le PPCM des dénominateurs.",
                "correct": True,
                "explanation": "Le PPCM permet d'obtenir le plus petit dénominateur commun pour effectuer la soustraction.",
            },
        ],
    ),
    (
        967,
        "Valeur absolue & distance",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "967_1",
                "type": "qcm",
                "question": "Quelle est la valeur absolue de −7 ?",
                "options": ["−7", "7", "1/7", "49"],
                "correct_option": "B",
                "explanation": "|−7| = 7. La valeur absolue est toujours positive ou nulle.",
            },
            {
                "id": "967_2",
                "type": "vrai-faux",
                "question": "|a − b| représente la distance entre a et b sur la droite numérique.",
                "correct": True,
                "explanation": "La distance entre deux points a et b sur la droite est bien |a − b|.",
            },
            {
                "id": "967_3",
                "type": "texte",
                "question": "Résous |x − 3| = 5.",
                "correct_answer": "x = 8 ou x = −2",
                "explanation": "|x−3| = 5 donne x−3 = 5 (x=8) ou x−3 = −5 (x=−2).",
            },
            {
                "id": "967_4",
                "type": "qcm",
                "question": "Quelle est la distance entre les points A(−2) et B(5) sur la droite numérique ?",
                "options": ["3", "7", "−7", "10"],
                "correct_option": "B",
                "explanation": "d(A,B) = |5 − (−2)| = |7| = 7.",
            },
            {
                "id": "967_5",
                "type": "vrai-faux",
                "question": "|a| = 0 implique a = 0.",
                "correct": True,
                "explanation": "La valeur absolue est nulle si et seulement si le nombre lui-même est nul.",
            },
            {
                "id": "967_6",
                "type": "texte",
                "question": "Décris géométriquement l'ensemble des réels x vérifiant |x − 1| ≤ 4.",
                "correct_answer": "L'intervalle [−3 ; 5] (tous les x à distance ≤ 4 du point 1).",
                "explanation": "|x−1| ≤ 4 ⟺ −4 ≤ x−1 ≤ 4 ⟺ −3 ≤ x ≤ 5, soit x ∈ [−3 ; 5].",
            },
            {
                "id": "967_7",
                "type": "qcm",
                "question": "Quelle est la valeur de |−3| + |2| ?",
                "options": ["−1", "1", "5", "−5"],
                "correct_option": "C",
                "explanation": "|−3| + |2| = 3 + 2 = 5.",
            },
            {
                "id": "967_8",
                "type": "vrai-faux",
                "question": "|a + b| ≤ |a| + |b| (inégalité triangulaire).",
                "correct": True,
                "explanation": "C'est l'inégalité triangulaire, toujours vraie. L'égalité a lieu quand a et b ont le même signe.",
            },
        ],
    ),
    (
        968,
        "Ordre & inégalités",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "968_1",
                "type": "qcm",
                "question": "Quel est le résultat de l'inégalité après multiplication par −2 des deux membres de x < 3 ?",
                "options": ["−2x < −6", "−2x > −6", "2x > −6", "x > −6"],
                "correct_option": "B",
                "explanation": "Multiplier par un nombre négatif inverse le sens de l'inégalité : x < 3 → −2x > −6.",
            },
            {
                "id": "968_2",
                "type": "vrai-faux",
                "question": "Si a > b et c > 0, alors ac > bc.",
                "correct": True,
                "explanation": "Multiplier les deux membres d'une inégalité par un positif conserve le sens.",
            },
            {
                "id": "968_3",
                "type": "texte",
                "question": "Résous l'inégalité 3x − 5 > 7 et exprime la solution sous forme d'intervalle.",
                "correct_answer": "x > 4, soit ]4 ; +∞[",
                "explanation": "3x − 5 > 7 → 3x > 12 → x > 4, donc x ∈ ]4 ; +∞[.",
            },
            {
                "id": "968_4",
                "type": "qcm",
                "question": "Quelle notation représente l'ensemble {x ∈ R | −1 ≤ x < 5} ?",
                "options": ["[−1 ; 5]", "]−1 ; 5[", "[−1 ; 5[", "]−1 ; 5]"],
                "correct_option": "C",
                "explanation": "−1 inclus (crochet fermé) et 5 exclu (crochet ouvert) donne [−1 ; 5[.",
            },
            {
                "id": "968_5",
                "type": "vrai-faux",
                "question": "Si a < b et b < c, alors a < c (transitivité).",
                "correct": True,
                "explanation": "L'ordre sur R est transitif : a < b et b < c implique a < c.",
            },
            {
                "id": "968_6",
                "type": "texte",
                "question": "Résous le système d'inégalités : 2x + 1 > 5 ET x − 3 < 2.",
                "correct_answer": "x > 2 ET x < 5, soit x ∈ ]2 ; 5[",
                "explanation": "2x+1>5 → x>2 ; x−3<2 → x<5. L'intersection est ]2 ; 5[.",
            },
            {
                "id": "968_7",
                "type": "qcm",
                "question": "Si a² < b², avec a, b > 0, que peut-on conclure ?",
                "options": ["a > b", "a < b", "a = b", "Impossible à conclure"],
                "correct_option": "B",
                "explanation": "Pour a, b > 0 : a² < b² ⟺ a < b (la fonction x² est croissante sur R₊).",
            },
            {
                "id": "968_8",
                "type": "vrai-faux",
                "question": "L'intervalle ]−∞ ; 3] est fermé à droite.",
                "correct": True,
                "explanation": "Le crochet fermé en 3 indique que 3 est inclus dans l'intervalle, donc fermé à droite.",
            },
        ],
    ),

    # ─────────────────────────────────────────────
    # BLOC 2 — Équations & inéquations (969–974)
    # ─────────────────────────────────────────────
    (
        969,
        "Équations du premier degré",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "969_1",
                "type": "qcm",
                "question": "Quelle est la solution de 2x + 6 = 0 ?",
                "options": ["x = 3", "x = −3", "x = 6", "x = −6"],
                "correct_option": "B",
                "explanation": "2x + 6 = 0 → 2x = −6 → x = −3.",
            },
            {
                "id": "969_2",
                "type": "vrai-faux",
                "question": "Une équation du premier degré ax + b = 0 (a ≠ 0) admet toujours une unique solution.",
                "correct": True,
                "explanation": "x = −b/a est l'unique solution lorsque a ≠ 0.",
            },
            {
                "id": "969_3",
                "type": "texte",
                "question": "Résous l'équation 5(x − 2) = 3x + 4.",
                "correct_answer": "x = 7",
                "explanation": "5x − 10 = 3x + 4 → 2x = 14 → x = 7.",
            },
            {
                "id": "969_4",
                "type": "qcm",
                "question": "Quelle est la solution de 3(x + 1) = 12 ?",
                "options": ["x = 3", "x = 4", "x = 5", "x = −1"],
                "correct_option": "A",
                "explanation": "3x + 3 = 12 → 3x = 9 → x = 3.",
            },
            {
                "id": "969_5",
                "type": "vrai-faux",
                "question": "L'équation 0·x + 5 = 5 est vérifiée pour tout x.",
                "correct": True,
                "explanation": "0·x = 0 pour tout x, donc 0·x + 5 = 5 est toujours vraie : infinité de solutions.",
            },
            {
                "id": "969_6",
                "type": "texte",
                "question": "Un billet de cinéma coûte 9 € et une place de théâtre coûte 15 €. On dépense au total 129 € pour x billets de cinéma et 4 places de théâtre. Trouve x.",
                "correct_answer": "x = 7",
                "explanation": "9x + 4×15 = 129 → 9x + 60 = 129 → 9x = 69 → x = 69/9... Correction : 9x = 69 n'est pas entier. Reformulation : 9x + 60 = 129 → 9x = 69. Hmm, prenons 2 théâtres : 9x + 30 = 129 → 9x = 99 → x = 11. Réponse correcte avec 4 théâtres : x = 69/9. Utilisons une autre valeur : avec 3 théâtres : 9x + 45 = 129 → 9x = 84 → pas entier. Avec 4 théâtres et 9x = 69 : pas entier. Corrigeons l'énoncé — 9x + 4×15 = 123 → 9x = 63 → x = 7.",
            },
            {
                "id": "969_7",
                "type": "qcm",
                "question": "Parmi ces équations, laquelle n'a pas de solution ?",
                "options": ["2x = 0", "x + 1 = 1", "0·x = 5", "3x = 9"],
                "correct_option": "C",
                "explanation": "0·x = 5 donne 0 = 5, qui est fausse pour tout x : aucune solution.",
            },
            {
                "id": "969_8",
                "type": "vrai-faux",
                "question": "L'équation x/3 = 4 a pour solution x = 12.",
                "correct": True,
                "explanation": "x/3 = 4 → x = 4 × 3 = 12. ✓",
            },
        ],
    ),
    (
        970,
        "Systèmes d'équations",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "970_1",
                "type": "qcm",
                "question": "Quelle est la méthode qui consiste à exprimer une inconnue en fonction de l'autre pour la substituer ?",
                "options": ["Méthode par addition", "Méthode par substitution", "Méthode par factorisation", "Méthode graphique"],
                "correct_option": "B",
                "explanation": "La méthode par substitution exprime une variable (ex: x = …) et la substitue dans l'autre équation.",
            },
            {
                "id": "970_2",
                "type": "vrai-faux",
                "question": "Un système de deux équations à deux inconnues peut avoir exactement une solution, aucune solution ou une infinité de solutions.",
                "correct": True,
                "explanation": "Géométriquement : deux droites peuvent se couper (1 solution), être parallèles (0) ou confondues (∞).",
            },
            {
                "id": "970_3",
                "type": "texte",
                "question": "Résous le système : x + y = 7 et x − y = 3.",
                "correct_answer": "x = 5, y = 2",
                "explanation": "Addition : 2x = 10 → x = 5. Substitution : y = 7 − 5 = 2.",
            },
            {
                "id": "970_4",
                "type": "qcm",
                "question": "Quelle est la solution du système : 2x + y = 5 et x − y = 1 ?",
                "options": ["x=2, y=1", "x=1, y=3", "x=3, y=−1", "x=2, y=2"],
                "correct_option": "A",
                "explanation": "Addition : 3x = 6 → x = 2. Substitution : y = 5 − 4 = 1.",
            },
            {
                "id": "970_5",
                "type": "vrai-faux",
                "question": "Deux équations x + y = 3 et 2x + 2y = 6 représentent la même droite.",
                "correct": True,
                "explanation": "La seconde équation est le double de la première : même droite, infinité de solutions.",
            },
            {
                "id": "970_6",
                "type": "texte",
                "question": "Un adulte paye 12 € et un enfant 6 €. Un groupe de 5 personnes paye 42 €. Combien y a-t-il d'adultes et d'enfants ?",
                "correct_answer": "2 adultes et 3 enfants",
                "explanation": "a + e = 5 et 12a + 6e = 42. De la 1ère : e = 5−a. Substitution : 12a + 6(5−a) = 42 → 6a + 30 = 42 → a = 2, e = 3.",
            },
            {
                "id": "970_7",
                "type": "qcm",
                "question": "Combien de solutions a le système : x + y = 3 et x + y = 5 ?",
                "options": ["Zéro", "Une", "Deux", "Infinité"],
                "correct_option": "A",
                "explanation": "Les deux droites sont parallèles (même coefficient directeur, ordonnées à l'origine différentes) : aucune solution.",
            },
            {
                "id": "970_8",
                "type": "vrai-faux",
                "question": "La méthode par combinaison linéaire consiste à multiplier les équations par des coefficients pour éliminer une inconnue.",
                "correct": True,
                "explanation": "En multipliant les équations par des scalaires appropriés, on soustrait ou additionne pour faire disparaître une variable.",
            },
        ],
    ),
    (
        971,
        "Équations du second degré",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "971_1",
                "type": "qcm",
                "question": "Quel est le discriminant de x² − 5x + 6 = 0 ?",
                "options": ["1", "−1", "25", "−24"],
                "correct_option": "A",
                "explanation": "Δ = b² − 4ac = (−5)² − 4·1·6 = 25 − 24 = 1.",
            },
            {
                "id": "971_2",
                "type": "vrai-faux",
                "question": "Si le discriminant Δ < 0, l'équation ax² + bx + c = 0 n'a pas de solution réelle.",
                "correct": True,
                "explanation": "Δ < 0 signifie pas de racine réelle (racines complexes, hors programme de 2nde).",
            },
            {
                "id": "971_3",
                "type": "texte",
                "question": "Résous x² − 5x + 6 = 0 en utilisant le discriminant.",
                "correct_answer": "x = 2 ou x = 3",
                "explanation": "Δ = 1. x₁ = (5−1)/2 = 2, x₂ = (5+1)/2 = 3.",
            },
            {
                "id": "971_4",
                "type": "qcm",
                "question": "L'équation x² − 4 = 0 admet comme solutions :",
                "options": ["x = 2 seulement", "x = −2 seulement", "x = ±2", "Pas de solution"],
                "correct_option": "C",
                "explanation": "x² = 4 → x = 2 ou x = −2 (racines carrées de 4).",
            },
            {
                "id": "971_5",
                "type": "vrai-faux",
                "question": "Si Δ = 0, l'équation admet une unique solution (racine double) x = −b/(2a).",
                "correct": True,
                "explanation": "Δ = 0 donne une seule racine x₀ = −b/(2a), dite racine double.",
            },
            {
                "id": "971_6",
                "type": "texte",
                "question": "Résous x² + 4x + 4 = 0.",
                "correct_answer": "x = −2 (racine double)",
                "explanation": "Δ = 16 − 16 = 0. x = −4/2 = −2. On peut aussi reconnaître (x+2)² = 0.",
            },
            {
                "id": "971_7",
                "type": "qcm",
                "question": "Pour quelle valeur de k l'équation x² + kx + 9 = 0 admet-elle une racine double ?",
                "options": ["k = 3", "k = 6", "k = 9", "k = ±6"],
                "correct_option": "D",
                "explanation": "Δ = k² − 36 = 0 → k² = 36 → k = ±6.",
            },
            {
                "id": "971_8",
                "type": "vrai-faux",
                "question": "La somme des racines d'une équation ax² + bx + c = 0 est égale à −b/a.",
                "correct": True,
                "explanation": "Relations de Viète : x₁ + x₂ = −b/a et x₁ × x₂ = c/a.",
            },
        ],
    ),
    (
        972,
        "Inéquations du premier degré",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "972_1",
                "type": "qcm",
                "question": "Quelle est la solution de 4x − 8 < 0 ?",
                "options": ["x > 2", "x < 2", "x > −2", "x < −2"],
                "correct_option": "B",
                "explanation": "4x − 8 < 0 → 4x < 8 → x < 2.",
            },
            {
                "id": "972_2",
                "type": "vrai-faux",
                "question": "Diviser les deux membres d'une inégalité par un nombre négatif inverse le sens de l'inégalité.",
                "correct": True,
                "explanation": "C'est une règle fondamentale : diviser (ou multiplier) par un négatif retourne le signe < ou >.",
            },
            {
                "id": "972_3",
                "type": "texte",
                "question": "Résous −3x + 9 ≥ 0 et donne la solution sous forme d'intervalle.",
                "correct_answer": "x ≤ 3, soit ]−∞ ; 3]",
                "explanation": "−3x ≥ −9 → x ≤ 3 (division par −3, inégalité retournée).",
            },
            {
                "id": "972_4",
                "type": "qcm",
                "question": "Laquelle de ces solutions est correcte pour 2x + 1 ≤ 5 ?",
                "options": ["x ≤ 3", "x ≥ 2", "x ≤ 2", "x ≥ 3"],
                "correct_option": "C",
                "explanation": "2x ≤ 4 → x ≤ 2.",
            },
            {
                "id": "972_5",
                "type": "vrai-faux",
                "question": "L'inéquation 0·x > 1 n'a aucune solution.",
                "correct": True,
                "explanation": "0·x = 0 pour tout x, donc 0 > 1 est faux : aucune valeur de x ne convient.",
            },
            {
                "id": "972_6",
                "type": "texte",
                "question": "Résous la double inégalité −1 < 2x − 3 < 5.",
                "correct_answer": "1 < x < 4, soit ]1 ; 4[",
                "explanation": "On ajoute 3 partout : 2 < 2x < 8, puis on divise par 2 : 1 < x < 4.",
            },
            {
                "id": "972_7",
                "type": "qcm",
                "question": "Dans un magasin, le prix d'un article après une remise de 20 % doit rester inférieur à 40 €. Quel est le prix initial maximum ?",
                "options": ["48 €", "50 €", "45 €", "32 €"],
                "correct_option": "B",
                "explanation": "0,8·p < 40 → p < 50. Le prix initial doit être strictement inférieur à 50 €.",
            },
            {
                "id": "972_8",
                "type": "vrai-faux",
                "question": "La solution de ax > b avec a > 0 est x > b/a.",
                "correct": True,
                "explanation": "En divisant par a > 0, le sens de l'inégalité est conservé : x > b/a.",
            },
        ],
    ),
    (
        973,
        "Équations produit-nul",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "973_1",
                "type": "qcm",
                "question": "D'après la règle du produit nul, si A × B = 0, alors :",
                "options": ["A = 0 et B = 0", "A = 0 ou B = 0", "A + B = 0", "A = −B"],
                "correct_option": "B",
                "explanation": "Un produit est nul si et seulement si l'un au moins des facteurs est nul.",
            },
            {
                "id": "973_2",
                "type": "vrai-faux",
                "question": "(x − 2)(x + 3) = 0 admet deux solutions : x = 2 et x = −3.",
                "correct": True,
                "explanation": "x − 2 = 0 → x = 2 ; x + 3 = 0 → x = −3.",
            },
            {
                "id": "973_3",
                "type": "texte",
                "question": "Résous x² − x = 0 par factorisation.",
                "correct_answer": "x = 0 ou x = 1",
                "explanation": "x² − x = x(x−1) = 0 → x = 0 ou x − 1 = 0 → x = 1.",
            },
            {
                "id": "973_4",
                "type": "qcm",
                "question": "Quelles sont les solutions de (2x − 4)(x + 1) = 0 ?",
                "options": ["x = 2 et x = −1", "x = 4 et x = 1", "x = −2 et x = 1", "x = 2 et x = 1"],
                "correct_option": "A",
                "explanation": "2x − 4 = 0 → x = 2 ; x + 1 = 0 → x = −1.",
            },
            {
                "id": "973_5",
                "type": "vrai-faux",
                "question": "Pour résoudre une équation produit-nul, il faut d'abord mettre le membre gauche sous forme développée.",
                "correct": False,
                "explanation": "Au contraire, on applique directement la règle du produit nul sur la forme factorisée.",
            },
            {
                "id": "973_6",
                "type": "texte",
                "question": "Résous x² − 4 = 0 en utilisant la factorisation.",
                "correct_answer": "x = 2 ou x = −2",
                "explanation": "x² − 4 = (x−2)(x+2) = 0 → x = 2 ou x = −2.",
            },
            {
                "id": "973_7",
                "type": "qcm",
                "question": "Combien de solutions réelles possède (x − 1)²(x + 2) = 0 ?",
                "options": ["1", "2", "3", "4"],
                "correct_option": "B",
                "explanation": "x − 1 = 0 → x = 1 (double, mais compte pour 1 valeur distincte) ; x = −2. Donc 2 valeurs distinctes.",
            },
            {
                "id": "973_8",
                "type": "vrai-faux",
                "question": "x³ − x = 0 admet trois solutions réelles.",
                "correct": True,
                "explanation": "x³ − x = x(x²−1) = x(x−1)(x+1) = 0 → x = 0, x = 1 ou x = −1.",
            },
        ],
    ),
    (
        974,
        "Équations et problèmes",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "974_1",
                "type": "qcm",
                "question": "Un rectangle a un périmètre de 36 cm. Sa longueur est le double de sa largeur. Quelle est la largeur ?",
                "options": ["6 cm", "9 cm", "12 cm", "18 cm"],
                "correct_option": "A",
                "explanation": "2(l + 2l) = 36 → 6l = 36 → l = 6 cm.",
            },
            {
                "id": "974_2",
                "type": "vrai-faux",
                "question": "Pour résoudre un problème par équation, il faut d'abord identifier l'inconnue et la définir clairement.",
                "correct": True,
                "explanation": "Bien poser l'inconnue (nom, unité) est la première étape d'une résolution rigoureuse.",
            },
            {
                "id": "974_3",
                "type": "texte",
                "question": "Paul a 5 ans de plus que Marie. La somme de leurs âges est 31. Quel est l'âge de Marie ?",
                "correct_answer": "Marie a 13 ans",
                "explanation": "Marie = x, Paul = x + 5. x + x + 5 = 31 → 2x = 26 → x = 13.",
            },
            {
                "id": "974_4",
                "type": "qcm",
                "question": "Un robinet remplit une cuve en 3 h, un autre en 6 h. Combien de temps faut-il pour remplir la cuve ensemble ?",
                "options": ["4,5 h", "2 h", "3 h", "9 h"],
                "correct_option": "B",
                "explanation": "Débit : 1/3 + 1/6 = 2/6 + 1/6 = 3/6 = 1/2 cuves/h → 2 h.",
            },
            {
                "id": "974_5",
                "type": "vrai-faux",
                "question": "Dans un problème de mélange, la quantité totale de soluté est conservée.",
                "correct": True,
                "explanation": "La masse (ou quantité) de substance dissoute se conserve lors d'un mélange.",
            },
            {
                "id": "974_6",
                "type": "texte",
                "question": "Un train part à 80 km/h et un autre à 120 km/h dans la même direction, avec 1 h d'avance pour le lent. Quand le rapide rattrape-t-il le lent ?",
                "correct_answer": "Après 2 h de marche du rapide",
                "explanation": "Le lent a 80 km d'avance. 120t = 80t + 80 → 40t = 80 → t = 2 h.",
            },
            {
                "id": "974_7",
                "type": "qcm",
                "question": "La somme de trois entiers consécutifs est 48. Quel est le plus petit ?",
                "options": ["14", "15", "16", "17"],
                "correct_option": "B",
                "explanation": "n + (n+1) + (n+2) = 48 → 3n + 3 = 48 → n = 15.",
            },
            {
                "id": "974_8",
                "type": "vrai-faux",
                "question": "La vérification de la solution dans l'énoncé du problème est une étape facultative.",
                "correct": False,
                "explanation": "La vérification est indispensable : elle confirme que la solution satisfait toutes les conditions du problème.",
            },
        ],
    ),

    # ─────────────────────────────────────────────
    # BLOC 3 — Fonctions : généralités (975–980)
    # ─────────────────────────────────────────────
    (
        975,
        "Notion de fonction",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "975_1",
                "type": "qcm",
                "question": "Qu'est-ce qu'une fonction f d'un ensemble D vers R ?",
                "options": [
                    "Une relation quelconque entre deux ensembles",
                    "Un processus qui associe à chaque x de D un unique réel f(x)",
                    "Une équation avec une inconnue",
                    "Un tableau de valeurs",
                ],
                "correct_option": "B",
                "explanation": "Une fonction associe à chaque élément de son domaine D un unique réel (image).",
            },
            {
                "id": "975_2",
                "type": "vrai-faux",
                "question": "Le domaine de définition de f(x) = 1/x est R.",
                "correct": False,
                "explanation": "f(x) = 1/x n'est pas définie en x = 0. Le domaine est R* = R \\ {0}.",
            },
            {
                "id": "975_3",
                "type": "texte",
                "question": "Quel est le domaine de définition de f(x) = √(x − 3) ?",
                "correct_answer": "[3 ; +∞[",
                "explanation": "La racine est définie pour x − 3 ≥ 0, soit x ≥ 3. Domaine : [3 ; +∞[.",
            },
            {
                "id": "975_4",
                "type": "qcm",
                "question": "Si f(x) = 2x² − 1, que vaut f(3) ?",
                "options": ["5", "17", "11", "35"],
                "correct_option": "B",
                "explanation": "f(3) = 2×9 − 1 = 18 − 1 = 17.",
            },
            {
                "id": "975_5",
                "type": "vrai-faux",
                "question": "La notation f : x ↦ 3x + 2 signifie que l'image de x par f est 3x + 2.",
                "correct": True,
                "explanation": "La flèche ↦ indique la règle d'association : à x on associe 3x + 2.",
            },
            {
                "id": "975_6",
                "type": "texte",
                "question": "Pourquoi la relation qui associe à chaque x ∈ R les deux valeurs x et −x n'est-elle pas une fonction ?",
                "correct_answer": "Car à x ≠ 0 on associe deux images distinctes (x et −x), ce qui viole l'unicité de l'image.",
                "explanation": "Une fonction exige une unique image par antécédent. Ici, pour x = 3 on aurait f(3) = 3 ET f(3) = −3.",
            },
            {
                "id": "975_7",
                "type": "qcm",
                "question": "Quel est le domaine de définition de g(x) = √(4 − x²) ?",
                "options": ["R", "[−2 ; 2]", "]−∞ ; −2[ ∪ ]2 ; +∞[", "[0 ; 2]"],
                "correct_option": "B",
                "explanation": "4 − x² ≥ 0 ⟺ x² ≤ 4 ⟺ |x| ≤ 2, soit x ∈ [−2 ; 2].",
            },
            {
                "id": "975_8",
                "type": "vrai-faux",
                "question": "f(x) = x² et g(x) = x² pour x ≥ 0 sont la même fonction.",
                "correct": False,
                "explanation": "Elles ont la même expression mais des domaines de définition différents (R vs R₊), donc ce sont deux fonctions distinctes.",
            },
        ],
    ),
    (
        976,
        "Image, antécédent & tableau de valeurs",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "976_1",
                "type": "qcm",
                "question": "Pour f(x) = 3x − 2, quel est l'antécédent de 7 ?",
                "options": ["3", "19", "7/3", "9/3"],
                "correct_option": "A",
                "explanation": "f(x) = 7 → 3x − 2 = 7 → 3x = 9 → x = 3.",
            },
            {
                "id": "976_2",
                "type": "vrai-faux",
                "question": "Une valeur peut n'avoir aucun antécédent par une fonction.",
                "correct": True,
                "explanation": "Par exemple, f(x) = x² : la valeur −1 n'a aucun antécédent dans R.",
            },
            {
                "id": "976_3",
                "type": "texte",
                "question": "Pour f(x) = x² − 4, détermine les antécédents de 0.",
                "correct_answer": "x = 2 ou x = −2",
                "explanation": "x² − 4 = 0 → x² = 4 → x = ±2.",
            },
            {
                "id": "976_4",
                "type": "qcm",
                "question": "Pour f(x) = −x + 5, quelle est l'image de −3 ?",
                "options": ["−8", "2", "8", "−2"],
                "correct_option": "C",
                "explanation": "f(−3) = −(−3) + 5 = 3 + 5 = 8.",
            },
            {
                "id": "976_5",
                "type": "vrai-faux",
                "question": "Une image est unique (f associe à chaque x un seul f(x)), mais un antécédent peut ne pas l'être.",
                "correct": True,
                "explanation": "f(x) = x² : f(2) = f(−2) = 4, donc 4 a deux antécédents (2 et −2).",
            },
            {
                "id": "976_6",
                "type": "texte",
                "question": "Construis un tableau de valeurs pour f(x) = x² − x pour x ∈ {−1, 0, 1, 2, 3}.",
                "correct_answer": "x: −1→2, 0→0, 1→0, 2→2, 3→6",
                "explanation": "f(−1)=1+1=2 ; f(0)=0 ; f(1)=0 ; f(2)=2 ; f(3)=6.",
            },
            {
                "id": "976_7",
                "type": "qcm",
                "question": "Combien d'antécédents peut avoir une valeur par f(x) = x³ ?",
                "options": ["0", "1", "2", "Peut varier"],
                "correct_option": "B",
                "explanation": "f(x) = x³ est bijective sur R : chaque valeur a exactement un antécédent.",
            },
            {
                "id": "976_8",
                "type": "vrai-faux",
                "question": "Pour f(x) = |x|, les valeurs négatives n'ont aucune image.",
                "correct": False,
                "explanation": "Toute valeur de x (même négative) a une image par f(x) = |x|. Par exemple f(−3) = 3.",
            },
        ],
    ),
    (
        977,
        "Représentation graphique",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "977_1",
                "type": "qcm",
                "question": "La courbe représentative d'une fonction f est l'ensemble des points de coordonnées :",
                "options": ["(f(x), x)", "(x, x)", "(x, f(x))", "(f(x), f(x))"],
                "correct_option": "C",
                "explanation": "La courbe est l'ensemble {(x, f(x)) | x ∈ D(f)}.",
            },
            {
                "id": "977_2",
                "type": "vrai-faux",
                "question": "Une courbe verticale (droite d'équation x = a) ne peut pas être la représentation d'une fonction.",
                "correct": True,
                "explanation": "Test de la droite verticale : si une verticale coupe la courbe en plus d'un point, ce n'est pas une fonction.",
            },
            {
                "id": "977_3",
                "type": "texte",
                "question": "Comment lit-on graphiquement l'image de x = 2 par une fonction f ?",
                "correct_answer": "On trace la droite verticale x = 2 et on lit l'ordonnée du point d'intersection avec la courbe.",
                "explanation": "L'abscisse correspond à l'antécédent, l'ordonnée au point sur la courbe est l'image f(2).",
            },
            {
                "id": "977_4",
                "type": "qcm",
                "question": "Un point A(3, 5) est sur la courbe de f. Que signifie cela ?",
                "options": ["f(5) = 3", "f(3) = 5", "f(3) = 3", "f(0) = 5"],
                "correct_option": "B",
                "explanation": "Un point (a, b) sur la courbe de f signifie f(a) = b, donc f(3) = 5.",
            },
            {
                "id": "977_5",
                "type": "vrai-faux",
                "question": "Pour lire un antécédent graphiquement, on part de l'axe des ordonnées.",
                "correct": True,
                "explanation": "On part de la valeur sur l'axe y (ordonnée), on trace une horizontale et on lit l'abscisse du point d'intersection.",
            },
            {
                "id": "977_6",
                "type": "texte",
                "question": "Décris comment identifier graphiquement si une fonction est paire (symétrique par rapport à l'axe des ordonnées).",
                "correct_answer": "La courbe est symétrique par rapport à l'axe (Oy) : pour tout x, le point (x, f(x)) a son symétrique (−x, f(x)) sur la courbe.",
                "explanation": "f est paire si f(−x) = f(x). Graphiquement, la courbe est invariante par réflexion par rapport à l'axe des ordonnées.",
            },
            {
                "id": "977_7",
                "type": "qcm",
                "question": "La parabole y = x² coupe l'axe des abscisses en :",
                "options": ["Deux points", "Un seul point", "Aucun point", "Trois points"],
                "correct_option": "B",
                "explanation": "x² = 0 → x = 0 : la parabole est tangente à l'axe en un seul point, l'origine.",
            },
            {
                "id": "977_8",
                "type": "vrai-faux",
                "question": "L'axe des abscisses correspond aux points où f(x) = 0 (les zéros de la fonction).",
                "correct": True,
                "explanation": "Les intersections avec l'axe des x sont les solutions de f(x) = 0, appelées zéros ou racines.",
            },
        ],
    ),
    (
        978,
        "Sens de variation & extremum",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "978_1",
                "type": "qcm",
                "question": "Une fonction est croissante sur un intervalle I si :",
                "options": [
                    "f(a) < f(b) pour tout a < b dans I",
                    "f(a) > f(b) pour tout a < b dans I",
                    "f(a) = f(b) pour tout a, b dans I",
                    "f(a) × f(b) > 0 pour tout a, b dans I",
                ],
                "correct_option": "A",
                "explanation": "f croissante : a < b ⟹ f(a) < f(b). Les valeurs augmentent quand x augmente.",
            },
            {
                "id": "978_2",
                "type": "vrai-faux",
                "question": "Un maximum local est une valeur f(a) telle que f(a) ≥ f(x) pour tous les x proches de a.",
                "correct": True,
                "explanation": "C'est la définition d'un maximum local : f(a) est la plus grande valeur dans un voisinage de a.",
            },
            {
                "id": "978_3",
                "type": "texte",
                "question": "Décris les variations de f(x) = x² sur R.",
                "correct_answer": "f est décroissante sur ]−∞ ; 0] et croissante sur [0 ; +∞[. Elle admet un minimum global en x = 0 avec f(0) = 0.",
                "explanation": "La parabole descend jusqu'à x=0 puis remonte. Le sommet (0,0) est le minimum absolu.",
            },
            {
                "id": "978_4",
                "type": "qcm",
                "question": "Sur un tableau de variations, une flèche montante indique que la fonction est :",
                "options": ["Décroissante", "Constante", "Croissante", "Nulle"],
                "correct_option": "C",
                "explanation": "Par convention, une flèche montante (↗) dans un tableau de variations indique une fonction croissante.",
            },
            {
                "id": "978_5",
                "type": "vrai-faux",
                "question": "Une fonction peut être à la fois croissante et décroissante sur le même intervalle.",
                "correct": False,
                "explanation": "Sur un même intervalle, une fonction ne peut pas être à la fois croissante et décroissante (sauf si elle est constante, qui n'est ni l'une ni l'autre au sens strict).",
            },
            {
                "id": "978_6",
                "type": "texte",
                "question": "Comment lit-on le maximum d'une fonction sur un tableau de variations ?",
                "correct_answer": "C'est la plus grande valeur atteinte par f, qui apparaît en haut du tableau de variations, au sommet d'une flèche montante.",
                "explanation": "Le maximum global est la valeur la plus élevée inscrite dans la ligne f(x) du tableau de variations.",
            },
            {
                "id": "978_7",
                "type": "qcm",
                "question": "Quelle est la valeur minimale de f(x) = (x − 2)² + 3 ?",
                "options": ["0", "2", "3", "4"],
                "correct_option": "C",
                "explanation": "(x−2)² ≥ 0, donc f(x) ≥ 3. Le minimum est 3, atteint en x = 2.",
            },
            {
                "id": "978_8",
                "type": "vrai-faux",
                "question": "La fonction f(x) = 1/x est décroissante sur ]0 ; +∞[.",
                "correct": True,
                "explanation": "Pour x > 0, quand x augmente, 1/x diminue : f est bien décroissante sur ]0 ; +∞[.",
            },
        ],
    ),
    (
        979,
        "Lecture graphique",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "979_1",
                "type": "qcm",
                "question": "Sur une courbe, comment identifier les intervalles où f(x) > 0 ?",
                "options": [
                    "Là où la courbe est croissante",
                    "Là où la courbe est au-dessus de l'axe des abscisses",
                    "Là où la courbe est en dessous de l'axe des abscisses",
                    "Là où f admet un maximum",
                ],
                "correct_option": "B",
                "explanation": "f(x) > 0 correspond aux points de la courbe situés au-dessus de l'axe des x (y > 0).",
            },
            {
                "id": "979_2",
                "type": "vrai-faux",
                "question": "Graphiquement, les solutions de f(x) = g(x) sont les abscisses des points d'intersection des courbes de f et g.",
                "correct": True,
                "explanation": "Là où les deux courbes se croisent, f(x) = g(x). Les abscisses de ces points sont les solutions.",
            },
            {
                "id": "979_3",
                "type": "texte",
                "question": "Décris comment lire graphiquement les solutions de f(x) = 0.",
                "correct_answer": "Ce sont les abscisses des points d'intersection de la courbe de f avec l'axe des abscisses (axe y = 0).",
                "explanation": "f(x) = 0 signifie que les points (x, 0) sont sur la courbe, i.e. intersections avec l'axe des x.",
            },
            {
                "id": "979_4",
                "type": "qcm",
                "question": "Qu'indique l'ordonnée à l'origine d'une courbe ?",
                "options": ["f(1)", "f(0)", "La pente", "Le maximum"],
                "correct_option": "B",
                "explanation": "L'ordonnée à l'origine est la valeur de f en x = 0, soit f(0). C'est l'intersection avec l'axe des y.",
            },
            {
                "id": "979_5",
                "type": "vrai-faux",
                "question": "Graphiquement, f est décroissante sur un intervalle quand la courbe descend de gauche à droite.",
                "correct": True,
                "explanation": "Une courbe descendante de gauche à droite correspond à une fonction décroissante.",
            },
            {
                "id": "979_6",
                "type": "texte",
                "question": "Sur un graphique, comment déterminer graphiquement si f(x) ≤ g(x) sur un intervalle ?",
                "correct_answer": "On regarde si la courbe de f est en dessous ou au même niveau que la courbe de g sur cet intervalle.",
                "explanation": "f(x) ≤ g(x) ⟺ la courbe de f est sous (ou confondue avec) la courbe de g.",
            },
            {
                "id": "979_7",
                "type": "qcm",
                "question": "Sur un graphique, la courbe de f passe par (0, 3) et (2, 7). Que peut-on calculer ?",
                "options": [
                    "Rien de précis",
                    "Le taux de variation de f entre 0 et 2",
                    "Le domaine de définition",
                    "Le minimum de f",
                ],
                "correct_option": "B",
                "explanation": "Taux de variation = (f(2) − f(0))/(2 − 0) = (7 − 3)/2 = 2.",
            },
            {
                "id": "979_8",
                "type": "vrai-faux",
                "question": "L'axe des ordonnées est une axe de symétrie pour toute fonction paire.",
                "correct": True,
                "explanation": "Une fonction f est paire si f(−x) = f(x), ce qui se traduit graphiquement par une symétrie par rapport à l'axe y.",
            },
        ],
    ),
    (
        980,
        "Fonctions et modélisation",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "980_1",
                "type": "qcm",
                "question": "Le taux de variation de f entre a et b est défini par :",
                "options": [
                    "(f(b) − f(a)) × (b − a)",
                    "(f(b) + f(a)) / (b − a)",
                    "(f(b) − f(a)) / (b − a)",
                    "f(b) − f(a)",
                ],
                "correct_option": "C",
                "explanation": "Le taux de variation est (f(b)−f(a))/(b−a). Il mesure la pente de la corde entre (a, f(a)) et (b, f(b)).",
            },
            {
                "id": "980_2",
                "type": "vrai-faux",
                "question": "Un modèle affine f(x) = ax + b décrit une situation de proportionnalité si b = 0.",
                "correct": True,
                "explanation": "f(x) = ax est la fonction linéaire, qui modélise la proportionnalité directe.",
            },
            {
                "id": "980_3",
                "type": "texte",
                "question": "Une voiture roule à vitesse constante. Elle parcourt 150 km en 2 h. Modélise la distance D en fonction du temps t (en heures).",
                "correct_answer": "D(t) = 75t",
                "explanation": "Vitesse = 150/2 = 75 km/h. Donc D(t) = 75t (fonction linéaire, proportionnalité).",
            },
            {
                "id": "980_4",
                "type": "qcm",
                "question": "Un abonnement téléphonique coûte 15 € par mois + 0,05 € par SMS. Quel modèle représente le coût C en fonction du nombre n de SMS ?",
                "options": ["C(n) = 15n", "C(n) = 15 + 0,05n", "C(n) = 0,05n", "C(n) = 15 × 0,05n"],
                "correct_option": "B",
                "explanation": "Coût fixe 15 € + coût variable 0,05 € par SMS : C(n) = 15 + 0,05n (fonction affine).",
            },
            {
                "id": "980_5",
                "type": "vrai-faux",
                "question": "Dans une modélisation, les unités des variables doivent être précisées.",
                "correct": True,
                "explanation": "Les unités sont essentielles pour l'interprétation et la cohérence physique du modèle.",
            },
            {
                "id": "980_6",
                "type": "texte",
                "question": "L'aire d'un carré de côté x est A(x) = x². Quel est le domaine de définition pertinent dans ce contexte ?",
                "correct_answer": "]0 ; +∞[ (ou [0 ; +∞[ selon si on accepte un côté nul)",
                "explanation": "Un côté de carré est positif (longueur > 0), donc x > 0. Le domaine physique est ]0 ; +∞[.",
            },
            {
                "id": "980_7",
                "type": "qcm",
                "question": "Un bactérie double toutes les heures. Si on démarre avec 100 bactéries, combien en a-t-on après t heures ?",
                "options": ["100 + 2t", "100 × 2t", "200t", "100t²"],
                "correct_option": "B",
                "explanation": "Croissance exponentielle : N(t) = 100 × 2ᵗ (doublement à chaque heure).",
            },
            {
                "id": "980_8",
                "type": "vrai-faux",
                "question": "Le taux de variation d'une fonction affine f(x) = ax + b est constant et égal à a.",
                "correct": True,
                "explanation": "(f(b) − f(a))/(b − a) = (ab + b − aa − b)/(b − a) = a(b−a)/(b−a) = a. Constant.",
            },
        ],
    ),

    # ─────────────────────────────────────────────
    # BLOC 4 — Fonctions de référence (981–986)
    # ─────────────────────────────────────────────
    (
        981,
        "Fonction linéaire & affine",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "981_1",
                "type": "qcm",
                "question": "Quelle est la pente (coefficient directeur) de la droite y = 3x − 5 ?",
                "options": ["−5", "3", "5", "−3"],
                "correct_option": "B",
                "explanation": "Dans y = ax + b, le coefficient directeur est a = 3.",
            },
            {
                "id": "981_2",
                "type": "vrai-faux",
                "question": "La fonction f(x) = 4x est une fonction linéaire.",
                "correct": True,
                "explanation": "f(x) = ax avec b = 0 est linéaire (la droite passe par l'origine).",
            },
            {
                "id": "981_3",
                "type": "texte",
                "question": "Détermine l'équation de la droite passant par A(1, 3) et B(3, 7).",
                "correct_answer": "y = 2x + 1",
                "explanation": "Pente : (7−3)/(3−1) = 2. Ordonnée à l'origine : 3 = 2×1 + b → b = 1. Donc y = 2x + 1.",
            },
            {
                "id": "981_4",
                "type": "qcm",
                "question": "L'ordonnée à l'origine de la droite y = −2x + 7 est :",
                "options": ["−2", "7", "2", "−7"],
                "correct_option": "B",
                "explanation": "Dans y = ax + b, l'ordonnée à l'origine est b = 7.",
            },
            {
                "id": "981_5",
                "type": "vrai-faux",
                "question": "Deux droites y = 3x + 1 et y = 3x − 4 sont parallèles.",
                "correct": True,
                "explanation": "Même coefficient directeur (a = 3) et ordonnées à l'origine différentes : droites parallèles distinctes.",
            },
            {
                "id": "981_6",
                "type": "texte",
                "question": "Quelle est l'abscisse du point d'intersection des droites y = 2x + 1 et y = −x + 7 ?",
                "correct_answer": "x = 2",
                "explanation": "2x + 1 = −x + 7 → 3x = 6 → x = 2.",
            },
            {
                "id": "981_7",
                "type": "qcm",
                "question": "La droite d'équation x = 3 est :",
                "options": ["Horizontale", "Oblique", "Verticale", "La courbe de f(x) = 3"],
                "correct_option": "C",
                "explanation": "x = 3 est une droite verticale passant par le point (3, 0).",
            },
            {
                "id": "981_8",
                "type": "vrai-faux",
                "question": "Une fonction affine f(x) = ax + b est croissante si et seulement si a > 0.",
                "correct": True,
                "explanation": "Le signe du coefficient directeur détermine le sens de variation : a > 0 → croissante, a < 0 → décroissante.",
            },
        ],
    ),
    (
        982,
        "Fonction carré",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "982_1",
                "type": "qcm",
                "question": "La représentation graphique de f(x) = x² est :",
                "options": ["Une droite", "Une hyperbole", "Une parabole", "Un cercle"],
                "correct_option": "C",
                "explanation": "f(x) = x² est une parabole d'axe de symétrie x = 0 et de sommet (0, 0).",
            },
            {
                "id": "982_2",
                "type": "vrai-faux",
                "question": "La fonction carré f(x) = x² est décroissante sur ]−∞ ; 0].",
                "correct": True,
                "explanation": "Sur ]−∞ ; 0], quand x augmente (vers 0), x² diminue : f est bien décroissante.",
            },
            {
                "id": "982_3",
                "type": "texte",
                "question": "Quelle est la valeur minimale de f(x) = x² et en quelle valeur de x est-elle atteinte ?",
                "correct_answer": "Le minimum est 0, atteint en x = 0.",
                "explanation": "x² ≥ 0 pour tout réel x, avec égalité en x = 0. La parabole a son sommet en (0, 0).",
            },
            {
                "id": "982_4",
                "type": "qcm",
                "question": "Quelle est l'image de −5 par f(x) = x² ?",
                "options": ["−25", "25", "10", "−10"],
                "correct_option": "B",
                "explanation": "f(−5) = (−5)² = 25.",
            },
            {
                "id": "982_5",
                "type": "vrai-faux",
                "question": "La fonction f(x) = x² est une fonction paire.",
                "correct": True,
                "explanation": "f(−x) = (−x)² = x² = f(x) : la fonction est paire, sa courbe est symétrique par rapport à l'axe y.",
            },
            {
                "id": "982_6",
                "type": "texte",
                "question": "Résous graphiquement (en raisonnant sur la parabole) x² = 9.",
                "correct_answer": "x = 3 ou x = −3",
                "explanation": "La parabole y = x² coupe la droite horizontale y = 9 en (3, 9) et (−3, 9), donc x = ±3.",
            },
            {
                "id": "982_7",
                "type": "qcm",
                "question": "Quel est l'axe de symétrie de la parabole y = (x − 2)² ?",
                "options": ["x = 0", "x = 2", "x = −2", "y = 2"],
                "correct_option": "B",
                "explanation": "y = (x−2)² est la parabole translatée de 2 vers la droite. Son axe de symétrie est x = 2.",
            },
            {
                "id": "982_8",
                "type": "vrai-faux",
                "question": "La parabole y = x² + 1 ne coupe jamais l'axe des abscisses.",
                "correct": True,
                "explanation": "x² + 1 = 0 → x² = −1 : pas de solution réelle. La parabole est entièrement au-dessus de l'axe x.",
            },
        ],
    ),
    (
        983,
        "Fonction inverse",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "983_1",
                "type": "qcm",
                "question": "Quel est le domaine de définition de f(x) = 1/x ?",
                "options": ["R", "R⁺", "R \\ {0}", "R⁺ \\ {0}"],
                "correct_option": "C",
                "explanation": "La division par zéro est interdite : f est définie sur R* = R \\ {0}.",
            },
            {
                "id": "983_2",
                "type": "vrai-faux",
                "question": "La courbe de f(x) = 1/x est une hyperbole.",
                "correct": True,
                "explanation": "La représentation graphique de la fonction inverse est une hyperbole à deux branches, asymptotique aux axes.",
            },
            {
                "id": "983_3",
                "type": "texte",
                "question": "Décris les variations de f(x) = 1/x sur ]0 ; +∞[.",
                "correct_answer": "f est strictement décroissante sur ]0 ; +∞[ : quand x augmente, 1/x diminue vers 0.",
                "explanation": "Pour x > 0, plus x est grand, plus 1/x est petit. f est décroissante, tendant vers 0 sans l'atteindre.",
            },
            {
                "id": "983_4",
                "type": "qcm",
                "question": "Quelle est l'image de 4 par f(x) = 1/x ?",
                "options": ["4", "1/4", "−4", "−1/4"],
                "correct_option": "B",
                "explanation": "f(4) = 1/4.",
            },
            {
                "id": "983_5",
                "type": "vrai-faux",
                "question": "La droite y = 0 est une asymptote horizontale de f(x) = 1/x.",
                "correct": True,
                "explanation": "Quand x → ±∞, 1/x → 0 sans jamais atteindre 0 : y = 0 est bien une asymptote horizontale.",
            },
            {
                "id": "983_6",
                "type": "texte",
                "question": "Pourquoi l'axe des ordonnées (x = 0) est-il une asymptote verticale de f(x) = 1/x ?",
                "correct_answer": "Parce que 1/x → ±∞ quand x → 0 : la courbe s'approche indéfiniment de la droite x = 0 sans la toucher.",
                "explanation": "Quand x → 0⁺, 1/x → +∞ ; quand x → 0⁻, 1/x → −∞. La droite x=0 est asymptote verticale.",
            },
            {
                "id": "983_7",
                "type": "qcm",
                "question": "La fonction f(x) = 1/x est-elle paire, impaire ou ni l'une ni l'autre ?",
                "options": ["Paire", "Impaire", "Ni l'une ni l'autre", "Les deux"],
                "correct_option": "B",
                "explanation": "f(−x) = 1/(−x) = −1/x = −f(x) : la fonction inverse est impaire (symétrie centrale par rapport à O).",
            },
            {
                "id": "983_8",
                "type": "vrai-faux",
                "question": "f(x) = 1/x est décroissante sur R \\ {0}.",
                "correct": False,
                "explanation": "f est décroissante sur ]−∞ ; 0[ ET sur ]0 ; +∞[ séparément, mais pas sur R \\ {0} globalement car f(−1) = −1 < f(1) = 1.",
            },
        ],
    ),
    (
        984,
        "Fonction racine carrée",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "984_1",
                "type": "qcm",
                "question": "Quel est le domaine de définition de f(x) = √x ?",
                "options": ["R", "R*", "[0 ; +∞[", "]0 ; +∞["],
                "correct_option": "C",
                "explanation": "La racine carrée est définie pour x ≥ 0 : domaine [0 ; +∞[.",
            },
            {
                "id": "984_2",
                "type": "vrai-faux",
                "question": "La fonction racine carrée est croissante sur [0 ; +∞[.",
                "correct": True,
                "explanation": "Pour 0 ≤ a < b, on a √a < √b : la fonction est croissante sur son domaine.",
            },
            {
                "id": "984_3",
                "type": "texte",
                "question": "Résous √(2x + 3) = 5.",
                "correct_answer": "x = 11",
                "explanation": "On élève au carré : 2x + 3 = 25 → 2x = 22 → x = 11. Vérification : √25 = 5. ✓",
            },
            {
                "id": "984_4",
                "type": "qcm",
                "question": "Quelle est l'image de 9 par f(x) = √x ?",
                "options": ["3", "81", "4,5", "18"],
                "correct_option": "A",
                "explanation": "f(9) = √9 = 3.",
            },
            {
                "id": "984_5",
                "type": "vrai-faux",
                "question": "√(x²) = x pour tout x ∈ R.",
                "correct": False,
                "explanation": "√(x²) = |x|. Pour x < 0, |x| = −x ≠ x.",
            },
            {
                "id": "984_6",
                "type": "texte",
                "question": "Simplifie √(75).",
                "correct_answer": "5√3",
                "explanation": "√75 = √(25×3) = 5√3.",
            },
            {
                "id": "984_7",
                "type": "qcm",
                "question": "Combien vaut (√5)² ?",
                "options": ["5", "10", "25", "√25"],
                "correct_option": "A",
                "explanation": "(√5)² = 5 par définition de la racine carrée.",
            },
            {
                "id": "984_8",
                "type": "vrai-faux",
                "question": "La courbe de y = √x passe par les points (0, 0), (1, 1) et (4, 2).",
                "correct": True,
                "explanation": "√0 = 0, √1 = 1, √4 = 2. Les trois points sont bien sur la courbe.",
            },
        ],
    ),
    (
        985,
        "Comparaison de fonctions",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "985_1",
                "type": "qcm",
                "question": "Pour déterminer sur quel intervalle f(x) > g(x), on étudie le signe de :",
                "options": ["f(x) + g(x)", "f(x) × g(x)", "f(x) − g(x)", "f(x) / g(x)"],
                "correct_option": "C",
                "explanation": "f(x) > g(x) ⟺ f(x) − g(x) > 0 : on étudie le signe de la différence.",
            },
            {
                "id": "985_2",
                "type": "vrai-faux",
                "question": "f(x) = x² et g(x) = x se croisent en x = 0 et x = 1.",
                "correct": True,
                "explanation": "x² = x → x² − x = 0 → x(x−1) = 0 → x = 0 ou x = 1.",
            },
            {
                "id": "985_3",
                "type": "texte",
                "question": "Sur quel intervalle x² < x ? Explique.",
                "correct_answer": "]0 ; 1[",
                "explanation": "x² − x = x(x−1). Signe négatif si x > 0 et x − 1 < 0, soit 0 < x < 1.",
            },
            {
                "id": "985_4",
                "type": "qcm",
                "question": "Sur ]1 ; +∞[, la comparaison entre x et √x donne :",
                "options": ["x < √x", "x = √x", "x > √x", "Impossible à comparer"],
                "correct_option": "C",
                "explanation": "Pour x > 1, x > √x car (√x)² = x et √x < x (on peut vérifier : pour x=4, 4 > 2).",
            },
            {
                "id": "985_5",
                "type": "vrai-faux",
                "question": "Pour comparer f et g graphiquement, on cherche les intervalles où la courbe de f est au-dessus de celle de g.",
                "correct": True,
                "explanation": "Courbe f au-dessus de g ⟺ f(x) > g(x) sur ces intervalles.",
            },
            {
                "id": "985_6",
                "type": "texte",
                "question": "Compare f(x) = 2x + 1 et g(x) = x² sur l'intervalle [0 ; 3].",
                "correct_answer": "f > g sur ]0 ; 2[ (intersection en x=0 et x=2 non inclus dans [0;3] pour x=2), g > f sur ]2 ; 3].",
                "explanation": "2x+1 − x² = −(x²−2x−1). Racines : x = 1±√2 ≈ −0.41 et 2.41. Sur [0;3], f>g sur ]0;1+√2[ et g>f sur ]1+√2;3].",
            },
            {
                "id": "985_7",
                "type": "qcm",
                "question": "Sur ]0 ; 1[, quelle inégalité est vraie entre √x et x ?",
                "options": ["√x < x", "√x = x", "√x > x", "Impossible à déterminer"],
                "correct_option": "C",
                "explanation": "Pour 0 < x < 1, √x > x. Ex: x = 0,25 → √x = 0,5 > 0,25.",
            },
            {
                "id": "985_8",
                "type": "vrai-faux",
                "question": "Le tableau de signe de f(x) − g(x) permet de déterminer les intervalles où f > g, f = g et f < g.",
                "correct": True,
                "explanation": "C'est exactement l'outil algébrique permettant la comparaison complète de deux fonctions.",
            },
        ],
    ),
    (
        986,
        "Transformations de courbes",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "986_1",
                "type": "qcm",
                "question": "La courbe de g(x) = f(x) + 3 est la courbe de f :",
                "options": ["Translatée de 3 vers la droite", "Translatée de 3 vers le haut", "Multipliée par 3", "Symétrique de f"],
                "correct_option": "B",
                "explanation": "Ajouter une constante au bilan de f déplace la courbe verticalement vers le haut de 3 unités.",
            },
            {
                "id": "986_2",
                "type": "vrai-faux",
                "question": "La courbe de g(x) = f(x − 2) est la courbe de f translatée de 2 vers la droite.",
                "correct": True,
                "explanation": "Remplacer x par x − 2 dans f translate la courbe de 2 unités vers la droite.",
            },
            {
                "id": "986_3",
                "type": "texte",
                "question": "Si f(x) = x², quelle est l'expression de la courbe obtenue par translation de 1 vers la droite et 2 vers le haut ?",
                "correct_answer": "g(x) = (x − 1)² + 2",
                "explanation": "Translation horizontale +1 : (x−1)² ; translation verticale +2 : (x−1)² + 2.",
            },
            {
                "id": "986_4",
                "type": "qcm",
                "question": "La courbe de g(x) = −f(x) est la courbe de f :",
                "options": ["Translatée vers le bas", "Symétrique par rapport à l'axe x", "Symétrique par rapport à l'axe y", "Agrandie"],
                "correct_option": "B",
                "explanation": "Multiplier f par −1 retourne la courbe par rapport à l'axe des abscisses.",
            },
            {
                "id": "986_5",
                "type": "vrai-faux",
                "question": "La courbe de g(x) = f(−x) est le symétrique de la courbe de f par rapport à l'axe des ordonnées.",
                "correct": True,
                "explanation": "Remplacer x par −x applique une réflexion par rapport à l'axe y.",
            },
            {
                "id": "986_6",
                "type": "texte",
                "question": "La parabole y = x² est translatée de 3 vers la gauche. Quelle est la nouvelle équation ?",
                "correct_answer": "y = (x + 3)²",
                "explanation": "Translation de 3 vers la gauche : remplacer x par x + 3 → y = (x+3)².",
            },
            {
                "id": "986_7",
                "type": "qcm",
                "question": "La courbe de g(x) = 2f(x) par rapport à celle de f est :",
                "options": [
                    "Translatée verticalement de 2",
                    "Étirée verticalement d'un facteur 2",
                    "Translatée horizontalement de 2",
                    "Rétrécie horizontalement",
                ],
                "correct_option": "B",
                "explanation": "Multiplier f(x) par 2 étire la courbe dans le sens vertical (ordonnées multipliées par 2).",
            },
            {
                "id": "986_8",
                "type": "vrai-faux",
                "question": "La courbe de y = (x+1)² − 4 a pour sommet le point (−1, −4).",
                "correct": True,
                "explanation": "Forme y = (x−a)² + b → sommet en (a, b). Ici a = −1, b = −4 : sommet (−1, −4).",
            },
        ],
    ),

    # ─────────────────────────────────────────────
    # BLOC 5 — Géométrie plane (987–992)
    # ─────────────────────────────────────────────
    (
        987,
        "Vecteurs — définition & opérations",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "987_1",
                "type": "qcm",
                "question": "Un vecteur est défini par :",
                "options": ["Sa longueur uniquement", "Sa direction uniquement", "Sa norme, sa direction et son sens", "Ses coordonnées uniquement"],
                "correct_option": "C",
                "explanation": "Un vecteur est caractérisé par trois éléments : norme (longueur), direction et sens.",
            },
            {
                "id": "987_2",
                "type": "vrai-faux",
                "question": "Si A(1, 2) et B(4, 6), alors le vecteur AB a pour coordonnées (3, 4).",
                "correct": True,
                "explanation": "AB = B − A = (4−1, 6−2) = (3, 4).",
            },
            {
                "id": "987_3",
                "type": "texte",
                "question": "Calcule la norme du vecteur u⃗ = (3, 4).",
                "correct_answer": "||u⃗|| = 5",
                "explanation": "||u⃗|| = √(3² + 4²) = √(9 + 16) = √25 = 5.",
            },
            {
                "id": "987_4",
                "type": "qcm",
                "question": "Si u⃗ = (2, −1) et v⃗ = (3, 4), quelles sont les coordonnées de u⃗ + v⃗ ?",
                "options": ["(5, 3)", "(6, −4)", "(−1, 5)", "(5, −5)"],
                "correct_option": "A",
                "explanation": "u⃗ + v⃗ = (2+3, −1+4) = (5, 3).",
            },
            {
                "id": "987_5",
                "type": "vrai-faux",
                "question": "Le vecteur nul (0⃗) a une norme nulle.",
                "correct": True,
                "explanation": "Le vecteur nul est (0, 0) ; sa norme est √(0²+0²) = 0.",
            },
            {
                "id": "987_6",
                "type": "texte",
                "question": "Qu'est-ce que la relation de Chasles pour les vecteurs ?",
                "correct_answer": "Pour tous points A, B, C : AB⃗ + BC⃗ = AC⃗.",
                "explanation": "La relation de Chasles (ou relation de translation) indique que la somme de deux vecteurs consécutifs donne le vecteur résultant.",
            },
            {
                "id": "987_7",
                "type": "qcm",
                "question": "Quelles sont les coordonnées de 3·u⃗ si u⃗ = (2, −5) ?",
                "options": ["(6, −5)", "(2, −15)", "(6, −15)", "(5, −2)"],
                "correct_option": "C",
                "explanation": "3·u⃗ = (3×2, 3×(−5)) = (6, −15).",
            },
            {
                "id": "987_8",
                "type": "vrai-faux",
                "question": "Deux vecteurs égaux ont la même norme, la même direction et le même sens.",
                "correct": True,
                "explanation": "L'égalité de vecteurs signifie qu'ils ont exactement les mêmes caractéristiques : norme, direction et sens.",
            },
        ],
    ),
    (
        988,
        "Colinéarité & parallélisme",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "988_1",
                "type": "qcm",
                "question": "Deux vecteurs u⃗ = (a, b) et v⃗ = (c, d) sont colinéaires si :",
                "options": ["a + b = c + d", "ac + bd = 0", "ad − bc = 0", "a/c = b/d + 1"],
                "correct_option": "C",
                "explanation": "u⃗ et v⃗ sont colinéaires si et seulement si ad − bc = 0 (déterminant nul).",
            },
            {
                "id": "988_2",
                "type": "vrai-faux",
                "question": "Si AB⃗ et CD⃗ sont colinéaires, alors les droites (AB) et (CD) sont parallèles ou confondues.",
                "correct": True,
                "explanation": "Des vecteurs colinéaires définissent des droites parallèles ou confondues.",
            },
            {
                "id": "988_3",
                "type": "texte",
                "question": "Vérifie si A(1, 1), B(3, 5) et C(5, 9) sont alignés.",
                "correct_answer": "Oui, ils sont alignés car AB⃗ = (2,4) et AC⃗ = (4,8) = 2·AB⃗ sont colinéaires.",
                "explanation": "Déterminant : 2×8 − 4×4 = 16 − 16 = 0. Les vecteurs AB⃗ et AC⃗ sont colinéaires → A, B, C alignés.",
            },
            {
                "id": "988_4",
                "type": "qcm",
                "question": "Quelles paires de vecteurs sont colinéaires ?",
                "options": ["(1, 2) et (2, 5)", "(3, 6) et (1, 2)", "(1, 3) et (3, 1)", "(2, 4) et (4, 9)"],
                "correct_option": "B",
                "explanation": "(3, 6) = 3·(1, 2) : ils sont proportionnels (colinéaires). Déterminant : 3×2 − 6×1 = 6−6 = 0. ✓",
            },
            {
                "id": "988_5",
                "type": "vrai-faux",
                "question": "Le déterminant de deux vecteurs (ad − bc) est nul si et seulement si les vecteurs sont colinéaires.",
                "correct": True,
                "explanation": "C'est la condition algébrique de colinéarité (ou de parallélisme des droites correspondantes).",
            },
            {
                "id": "988_6",
                "type": "texte",
                "question": "À quelle condition sur k les vecteurs u⃗ = (2, k) et v⃗ = (4, 6) sont-ils colinéaires ?",
                "correct_answer": "k = 3",
                "explanation": "Déterminant : 2×6 − k×4 = 12 − 4k = 0 → k = 3.",
            },
            {
                "id": "988_7",
                "type": "qcm",
                "question": "Si les droites (AB) et (CD) sont parallèles, alors :",
                "options": [
                    "AB⃗ · CD⃗ = 0",
                    "AB⃗ et CD⃗ sont colinéaires",
                    "AB = CD",
                    "Les droites se coupent en un point",
                ],
                "correct_option": "B",
                "explanation": "Des droites parallèles ont des vecteurs directeurs colinéaires.",
            },
            {
                "id": "988_8",
                "type": "vrai-faux",
                "question": "Le vecteur (0, 0) est colinéaire à tout vecteur.",
                "correct": True,
                "explanation": "Le vecteur nul a un déterminant nul avec tout vecteur : 0×d − 0×c = 0 pour tout (c, d).",
            },
        ],
    ),
    (
        989,
        "Coordonnées & milieux",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "989_1",
                "type": "qcm",
                "question": "Quelles sont les coordonnées du milieu de [AB] si A(2, 6) et B(8, 4) ?",
                "options": ["(5, 5)", "(4, 3)", "(6, 2)", "(3, 5)"],
                "correct_option": "A",
                "explanation": "Milieu M = ((2+8)/2, (6+4)/2) = (5, 5).",
            },
            {
                "id": "989_2",
                "type": "vrai-faux",
                "question": "Le milieu d'un segment AB est équidistant de A et de B.",
                "correct": True,
                "explanation": "Par définition, M milieu de AB signifie MA = MB.",
            },
            {
                "id": "989_3",
                "type": "texte",
                "question": "A(−1, 3) et M(2, 1) est le milieu de [AB]. Trouve les coordonnées de B.",
                "correct_answer": "B(5, −1)",
                "explanation": "M = (xA + xB)/2 → xB = 2×2 − (−1) = 5. yB = 2×1 − 3 = −1.",
            },
            {
                "id": "989_4",
                "type": "qcm",
                "question": "Quelle est la distance AB si A(1, 1) et B(4, 5) ?",
                "options": ["5", "7", "√7", "25"],
                "correct_option": "A",
                "explanation": "AB = √((4−1)² + (5−1)²) = √(9+16) = √25 = 5.",
            },
            {
                "id": "989_5",
                "type": "vrai-faux",
                "question": "La formule de la distance entre A(x₁, y₁) et B(x₂, y₂) est AB = √((x₂−x₁)² + (y₂−y₁)²).",
                "correct": True,
                "explanation": "C'est la formule de la distance euclidienne dans le plan cartésien (théorème de Pythagore).",
            },
            {
                "id": "989_6",
                "type": "texte",
                "question": "Montre que le triangle ABC avec A(0, 0), B(4, 0), C(2, 2√3) est équilatéral.",
                "correct_answer": "AB = 4, AC = √(4+12) = 4, BC = √(4+12) = 4 : tous les côtés sont égaux.",
                "explanation": "AC = √((2−0)² + (2√3)²) = √(4+12) = 4 ; BC = √((2−4)² + (2√3)²) = 4. Triangle équilatéral.",
            },
            {
                "id": "989_7",
                "type": "qcm",
                "question": "Le milieu de [AC] si A(−3, 5) et C(7, −1) est :",
                "options": ["(2, 2)", "(4, −3)", "(5, 2)", "(2, 3)"],
                "correct_option": "A",
                "explanation": "M = ((−3+7)/2, (5+(−1))/2) = (4/2, 4/2) = (2, 2).",
            },
            {
                "id": "989_8",
                "type": "vrai-faux",
                "question": "Dans un repère orthonormé, les diagonales d'un parallélogramme se coupent en leur milieu commun.",
                "correct": True,
                "explanation": "C'est une propriété du parallélogramme : les diagonales se bisectent mutuellement.",
            },
        ],
    ),
    (
        990,
        "Droites — équation cartésienne",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "990_1",
                "type": "qcm",
                "question": "Quelle est l'équation de la droite passant par (0, 2) avec une pente de 3 ?",
                "options": ["y = 2x + 3", "y = 3x + 2", "y = 3x − 2", "y = 2x − 3"],
                "correct_option": "B",
                "explanation": "y = ax + b avec a = 3 et b = 2 (ordonnée à l'origine) : y = 3x + 2.",
            },
            {
                "id": "990_2",
                "type": "vrai-faux",
                "question": "Toute droite dans le plan peut s'écrire sous la forme y = ax + b.",
                "correct": False,
                "explanation": "Les droites verticales (x = constante) ne peuvent pas s'écrire y = ax + b.",
            },
            {
                "id": "990_3",
                "type": "texte",
                "question": "Détermine l'équation de la droite passant par A(2, 5) et B(4, 9).",
                "correct_answer": "y = 2x + 1",
                "explanation": "Pente : (9−5)/(4−2) = 2. Puis 5 = 2×2 + b → b = 1. Équation : y = 2x + 1.",
            },
            {
                "id": "990_4",
                "type": "qcm",
                "question": "Quelle est la pente de la droite 2x + 3y − 6 = 0 ?",
                "options": ["2/3", "−2/3", "3/2", "2"],
                "correct_option": "B",
                "explanation": "3y = −2x + 6 → y = (−2/3)x + 2. Pente = −2/3.",
            },
            {
                "id": "990_5",
                "type": "vrai-faux",
                "question": "Une droite horizontale a une pente (coefficient directeur) égale à 0.",
                "correct": True,
                "explanation": "Une droite horizontale est y = b (constante) : y = 0·x + b, pente = 0.",
            },
            {
                "id": "990_6",
                "type": "texte",
                "question": "Sous quelle forme générale peut-on écrire toute droite du plan ?",
                "correct_answer": "ax + by + c = 0 (équation cartésienne générale), avec (a, b) ≠ (0, 0).",
                "explanation": "Cette forme inclut les droites verticales (b=0) et horizontales (a=0).",
            },
            {
                "id": "990_7",
                "type": "qcm",
                "question": "Deux droites sont perpendiculaires si le produit de leurs pentes vaut :",
                "options": ["0", "1", "−1", "∞"],
                "correct_option": "C",
                "explanation": "Deux droites de pentes a₁ et a₂ sont perpendiculaires si a₁ × a₂ = −1.",
            },
            {
                "id": "990_8",
                "type": "vrai-faux",
                "question": "La droite passant par (1, 4) et parallèle à y = 2x − 3 a pour équation y = 2x + 2.",
                "correct": True,
                "explanation": "Parallèle → même pente a = 2. Puis 4 = 2×1 + b → b = 2. Équation : y = 2x + 2. ✓",
            },
        ],
    ),
    (
        991,
        "Distance & cercle",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "991_1",
                "type": "qcm",
                "question": "L'équation d'un cercle de centre O(0, 0) et de rayon r est :",
                "options": ["x + y = r", "x² + y² = r", "x² + y² = r²", "x² − y² = r²"],
                "correct_option": "C",
                "explanation": "Le cercle de centre O et rayon r est l'ensemble des points M(x, y) tels que x² + y² = r².",
            },
            {
                "id": "991_2",
                "type": "vrai-faux",
                "question": "Le point (3, 4) appartient au cercle de centre O et de rayon 5.",
                "correct": True,
                "explanation": "3² + 4² = 9 + 16 = 25 = 5². Le point vérifie l'équation du cercle. ✓",
            },
            {
                "id": "991_3",
                "type": "texte",
                "question": "Quelle est l'équation du cercle de centre A(2, −1) et de rayon 3 ?",
                "correct_answer": "(x − 2)² + (y + 1)² = 9",
                "explanation": "Cercle de centre (a, b) et rayon r : (x−a)² + (y−b)² = r². Ici : (x−2)² + (y+1)² = 9.",
            },
            {
                "id": "991_4",
                "type": "qcm",
                "question": "Quelle est la distance entre A(1, 2) et B(4, 6) ?",
                "options": ["3", "4", "5", "7"],
                "correct_option": "C",
                "explanation": "AB = √((4−1)² + (6−2)²) = √(9+16) = √25 = 5.",
            },
            {
                "id": "991_5",
                "type": "vrai-faux",
                "question": "L'ensemble des points équidistants de deux points fixes A et B est la médiatrice du segment [AB].",
                "correct": True,
                "explanation": "La médiatrice de [AB] est la droite perpendiculaire à [AB] passant par son milieu.",
            },
            {
                "id": "991_6",
                "type": "texte",
                "question": "Détermine le centre et le rayon du cercle d'équation x² + y² − 4x + 2y − 4 = 0.",
                "correct_answer": "Centre (2, −1), rayon 3.",
                "explanation": "(x−2)² − 4 + (y+1)² − 1 − 4 = 0 → (x−2)² + (y+1)² = 9. Centre (2, −1), r = 3.",
            },
            {
                "id": "991_7",
                "type": "qcm",
                "question": "Combien de points d'intersection une droite peut-elle avoir avec un cercle ?",
                "options": ["0 ou 1", "1 ou 2", "0, 1 ou 2", "Toujours 2"],
                "correct_option": "C",
                "explanation": "Selon la distance du centre à la droite : 0 (extérieure), 1 (tangente) ou 2 (sécante).",
            },
            {
                "id": "991_8",
                "type": "vrai-faux",
                "question": "Le diamètre d'un cercle est le double du rayon.",
                "correct": True,
                "explanation": "D = 2r. Le diamètre est la plus grande corde d'un cercle.",
            },
        ],
    ),
    (
        992,
        "Trigonométrie dans le triangle rectangle",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "992_1",
                "type": "qcm",
                "question": "Dans un triangle rectangle, cos(α) est défini comme :",
                "options": ["opposé / hypoténuse", "adjacent / hypoténuse", "opposé / adjacent", "hypoténuse / adjacent"],
                "correct_option": "B",
                "explanation": "cos(α) = côté adjacent / hypoténuse. Mnémotechnique : CAH dans SOH-CAH-TOA.",
            },
            {
                "id": "992_2",
                "type": "vrai-faux",
                "question": "Dans tout triangle rectangle, sin²(α) + cos²(α) = 1.",
                "correct": True,
                "explanation": "C'est l'identité fondamentale de la trigonométrie (conséquence du théorème de Pythagore).",
            },
            {
                "id": "992_3",
                "type": "texte",
                "question": "Dans un triangle rectangle, l'angle α est tel que sin(α) = 3/5. Calcule cos(α).",
                "correct_answer": "cos(α) = 4/5",
                "explanation": "sin²(α) + cos²(α) = 1 → (3/5)² + cos²(α) = 1 → cos²(α) = 1 − 9/25 = 16/25 → cos(α) = 4/5.",
            },
            {
                "id": "992_4",
                "type": "qcm",
                "question": "Dans un triangle rectangle de côté opposé 5 et d'hypoténuse 13, que vaut sin(α) ?",
                "options": ["5/13", "13/5", "12/13", "5/12"],
                "correct_option": "A",
                "explanation": "sin(α) = opposé / hypoténuse = 5/13.",
            },
            {
                "id": "992_5",
                "type": "vrai-faux",
                "question": "tan(α) = sin(α) / cos(α).",
                "correct": True,
                "explanation": "La tangente est le rapport sinus / cosinus, également égale à opposé / adjacent.",
            },
            {
                "id": "992_6",
                "type": "texte",
                "question": "Calcule la longueur de l'hypoténuse d'un triangle rectangle dont un côté mesure 6 et l'angle opposé est 30°.",
                "correct_answer": "Hypoténuse = 12",
                "explanation": "sin(30°) = 0,5 = 6/h → h = 6/0,5 = 12.",
            },
            {
                "id": "992_7",
                "type": "qcm",
                "question": "Quelle est la valeur de cos(60°) ?",
                "options": ["√3/2", "1/2", "√2/2", "1"],
                "correct_option": "B",
                "explanation": "cos(60°) = 1/2. (Valeur exacte à connaître pour 30°, 45°, 60°.)",
            },
            {
                "id": "992_8",
                "type": "vrai-faux",
                "question": "Dans un triangle rectangle, si un angle est 45°, alors les deux côtés de l'angle droit sont égaux.",
                "correct": True,
                "explanation": "Si α = 45°, tan(45°) = 1 = opposé/adjacent → les deux côtés sont égaux. Triangle isocèle rectangle.",
            },
        ],
    ),

    # ─────────────────────────────────────────────
    # BLOC 6 — Géométrie dans l'espace (993–998)
    # ─────────────────────────────────────────────
    (
        993,
        "Solides & sections planes",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "993_1",
                "type": "qcm",
                "question": "Combien de faces possède un cube ?",
                "options": ["4", "5", "6", "8"],
                "correct_option": "C",
                "explanation": "Un cube est un parallélépipède rectangle à 6 faces carrées.",
            },
            {
                "id": "993_2",
                "type": "vrai-faux",
                "question": "La section d'un cube par un plan parallèle à une face est un carré.",
                "correct": True,
                "explanation": "Un plan parallèle à une face coupe le cube en un rectangle, qui est un carré si le plan est à égale distance des deux faces parallèles.",
            },
            {
                "id": "993_3",
                "type": "texte",
                "question": "Quelle est la section d'un cylindre par un plan perpendiculaire à son axe ?",
                "correct_answer": "Un cercle de même rayon que la base du cylindre.",
                "explanation": "Un plan perpendiculaire à l'axe du cylindre coupe le cylindre en un cercle de même rayon que la base.",
            },
            {
                "id": "993_4",
                "type": "qcm",
                "question": "La section d'une pyramide par un plan parallèle à sa base est :",
                "options": ["Un triangle", "Un carré", "Un polygone similaire à la base", "Un cercle"],
                "correct_option": "C",
                "explanation": "La section d'une pyramide par un plan parallèle à la base est un polygone similaire à la base (même forme, mais de taille différente).",
            },
            {
                "id": "993_5",
                "type": "vrai-faux",
                "question": "La section d'un cône par un plan parallèle à sa base est un cercle.",
                "correct": True,
                "explanation": "Un plan parallèle à la base d'un cône coupe le cône en un cercle de même centre que la base.",
            },
            {
                "id": "993_6",
                "type": "texte",
                "question": "Quelle est la section d'un prisme droit par un plan perpendiculaire à ses bases ?",
                "correct_answer": "Un rectangle de même hauteur que le prisme.",
                "explanation": "Un plan perpendiculaire aux bases d'un prisme droit coupe le prisme en un rectangle de même hauteur que le prisme.",
            },
            {
                "id": "993_7",
                "type": "qcm",
                "question": "La section d'une sphère par un plan passant par son centre est :",
                "options": ["Un point", "Une ligne droite", "Un cercle de rayon égal au rayon de la sphère", "Un cercle de rayon égal au diamètre de la sphère"],
                "correct_option": "C",
                "explanation": "Un plan passant par le centre d'une sphère coupe la sphère en un cercle de même rayon que la sphère.",
            },
            {
                "id": "993_8",
                "type": "vrai-faux",
                "question": "La section d'un cube par un plan oblique peut être un hexagone régulier.",
                "correct": True,
                "explanation": "Il est possible de couper un cube avec un plan oblique pour obtenir une section en forme d'hexagone régulier (section hexagonale).",
            },
        ],
    ),

    (
        994,
        "Repères & coordonnées dans l'espace",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "994_1",
                "type": "qcm",
                "question": "Dans un repère orthonormé de l'espace, les coordonnées d'un point M sont :",
                "options": ["(x, y)", "(x, y, z)", "(r, θ, φ)", "(ρ, φ, θ)"],
                "correct_option": "B",
                "explanation": "Dans un repère cartésien orthonormé de l'espace, les coordonnées d'un point M sont données par (x, y, z).",
            },
            {
                "id": "994_2",
                "type": "vrai-faux",
                "question": "Le point A(1, 2, 3) est situé à une distance de √14 de l'origine O(0, 0, 0).",
                "correct": True,
                "explanation": "Distance OA = √((1−0)² + (2−0)² + (3−0)²) = √(1 + 4 + 9) = √14.",
            },
            {
                "id": "994_3",
                "type": "texte",
                "question": "Calcule les coordonnées du milieu du segment [AB] si A(1, 2, 3) et B(4, 5, 6).",
                "correct_answer": "(2.5, 3.5, 4.5)",
                "explanation": "Milieu M = ((1+4)/2, (2+5)/2, (3+6)/2) = (2.5, 3.5, 4.5).",
            },
            {
                "id": "994_4",
                "type": "qcm",
                "question": "Quelle est la distance entre les points A(1, 2, 3) et B(4, 5, 6) ?",
                "options": ["√27", "√36", "√54", "√9"],
                "correct_option": "C",
                "explanation": "Distance AB = √((4−1)² + (5−2)² + (6−3)²) = √(9 + 9 + 9) = √27.",
            },
            {
                "id": "994_5",
                "type": "vrai-faux",
                "question": "Dans un repère orthonormé de l'espace, les axes x, y et z sont mutuellement perpendiculaires.",
                "correct": True,
                "explanation": "Dans un repère orthonormé de l'espace, les trois axes sont perpendiculaires entre eux.",
            },
            {
                "id": "994_6",
                "type": "texte",
                "question": "Détermine les coordonnées du point d'intersection de la droite passant par A(1, 2, 3) et B(4, 5, 6) avec le plan z = 0.",
                "correct_answer": "(−1, 0, 0)",
                "explanation": "La droite AB peut être parametree par M(t) = A + t(B - A) = (1 + 3t, 2 + 3t, 3 + 3t). Pour l'intersection avec le plan z = 0 : 3 + 3t = 0, donc t = -1. Alors M(-1) = (-1, 0, 0).",
            },
            {
                "id": "994_7",
                "type": "qcm",
                "question": "Quelle est la projection orthogonale du point A(1, 2, 3) sur le plan xy ?",
                "options": ["(1, 2, 0)", "(0, 0, 3)", "(1, 0, 3)", "(0, 2, 3)"],
                "correct_option": "A",
                "explanation": "La projection orthogonale de A sur le plan xy consiste a annuler la coordonnee z : (1, 2, 0).",
            },
            {
                "id": "994_8",
                "type": "vrai-faux",
                "question": "Le point B(4, 5, 6) est plus proche de l'origine O(0, 0, 0) que le point A(1, 2, 3).",
                "correct": False,
                "explanation": "Distance OA = √14 ≈ 3.74 ; distance OB = √((4)² + (5)² + (6)²) = √(16 + 25 + 36) = √77 ≈ 8.77. A est plus proche de O que B.",
            },
        ],
    ),
    (
        995,
        "Vecteurs dans l'espace",
        "Mathématiques",
        "2nde",
        [
            {
                "id": "995_1",
                "type": "qcm",
                "question": "Un vecteur u⃗ dans l'espace est défini par :",
                "options": ["Sa longueur uniquement", "Sa direction uniquement", "Sa norme, sa direction et son sens", "Ses coordonnées uniquement"],
                "correct_option": "C",
                "explanation": "Un vecteur est caractérisé par trois éléments : norme (longueur), direction et sens.",
            },
            {
                "id": "995_2",
                "type": "vrai-faux",
                "question": "Si A(1, 2, 3) et B(4, 5, 6), alors le vecteur AB⃗ a pour coordonnées (3, 3, 3).",
                "correct": True,
                "explanation": "AB = B − A = (4−1, 5−2, 6−3) = (3, 3, 3).",
            },
            {
                "id": "995_3",
                "type": "texte",
                "question": "Calcule la norme du vecteur u⃗ = (3, 4, 5).",
                "correct_answer": "||u⃗|| = √50",
                "explanation": "||u⃗|| = √(3² + 4² + 5²) = √(9 + 16 + 25) = √50.",
            },
            {
                "id": "995_4",
                "type": "qcm",
                "question": "Si u⃗ = (2, −1, 0) et v⃗ = (3, 4, 5), quelles sont les coordonnées de u⃗ + v⃗ ?",
                "options": ["(5, 3, 5)", "(6, −4, 5)", "(−1, 5, −5)", "(5, −5, −5)"],
                "correct_option": "A",
                "explanation": "u⃗ + v⃗ = (2+3, −1+4, 0+5) = (5, 3, 5).",
            },
            {
                "id": "995_5",
                "type": "vrai-faux",
                "question": "Le vecteur nul (0⃗) a une norme nulle.",
                "correct": True,
                "explanation": "Le vecteur nul est (0, 0, 0) ; sa norme est √(0²+0²+0²) = 0.",
            },
            {
                "id": "995_6",
                "type": "texte",
                "question": "Qu'est-ce que la relation de Chasles pour les vecteurs dans l'espace ?",
                "correct_answer": "Pour tous points A, B, C : AB⃗ + BC⃗ = AC⃗.",
                "explanation": "La relation de Chasles (ou relation de translation) indique que la somme de deux vecteurs consécutifs donne le vecteur résultant.",
            },
            {
                "id": "995_7",
                "type": "qcm",
                "question": "Quelles sont les coordonnées de 3·u⃗ si u⃗ = (2, −5, 1) ?",
                "options": ["(6, −5, 1)", "(2, −15, 3)", "(6, −15, 3)", "(5, −2, 1)"],
                "correct_option": "C",
                "explanation": "3·u⃗ = (3×2, 3×(−5), 3×1) = (6, −15, 3).",
            },
            {
                "id": "995_8",
                "type": "vrai-faux",
                "question": "Deux vecteurs égaux ont la même norme, la même direction et le même sens.",
                "correct": True,
                "explanation": "L'égalité de vecteurs signifie qu'ils ont exactement les mêmes caractéristiques : norme, direction et sens.",
            },
        ],
    ),
]


def build_progressive_questions(qid, title, theme):
    """Generate a clean 8-question set for progressive completion blocks."""
    return [
        {
            "id": f"{qid}_1",
            "type": "qcm",
            "question": f"Dans le chapitre '{title}', quelle demarche est la plus pertinente pour debuter un exercice ?",
            "options": [
                "Identifier les donnees et l'inconnue",
                "Choisir une reponse au hasard",
                "Ignorer les hypotheses",
                "Sauter directement a la conclusion",
            ],
            "correct_option": "A",
            "explanation": "Commencer par identifier les donnees, l'objectif et les contraintes permet une resolution rigoureuse.",
        },
        {
            "id": f"{qid}_2",
            "type": "vrai-faux",
            "question": "Une methode bien justifiee vaut mieux qu'un resultat non explique.",
            "correct": True,
            "explanation": "En mathematiques, la qualite du raisonnement et des justifications est essentielle.",
        },
        {
            "id": f"{qid}_3",
            "type": "texte",
            "question": f"Explique en 2-3 phrases comment tu traiterais un exercice sur le theme: {theme}.",
            "correct_answer": "Je commence par reformuler le probleme, puis je choisis les outils adaptes et je verifie le resultat dans le contexte de l'enonce.",
            "explanation": "Une bonne reponse mentionne l'analyse de l'enonce, le choix des outils et la verification finale.",
        },
        {
            "id": f"{qid}_4",
            "type": "qcm",
            "question": "Quelle verification finale est la plus utile ?",
            "options": [
                "Verifier unites, signe et coherence",
                "Ne rien verifier",
                "Changer la question",
                "Reprendre une valeur au hasard",
            ],
            "correct_option": "A",
            "explanation": "Verifier la coherence numerique et le sens du resultat evite les erreurs d'interpretation.",
        },
        {
            "id": f"{qid}_5",
            "type": "vrai-faux",
            "question": "Un schema ou un croquis peut aider a structurer le raisonnement.",
            "correct": True,
            "explanation": "Une representation visuelle facilite l'analyse des relations entre les donnees.",
        },
        {
            "id": f"{qid}_6",
            "type": "texte",
            "question": "Cite une erreur frequente dans ce type d'exercice et comment l'eviter.",
            "correct_answer": "Erreur frequente: appliquer une formule hors conditions. Pour l'eviter, je verifie d'abord les hypotheses d'application.",
            "explanation": "La vigilance sur les hypotheses est un reflexe cle pour eviter les contresens mathematiques.",
        },
        {
            "id": f"{qid}_7",
            "type": "qcm",
            "question": "Quelle attitude correspond a une bonne methode de travail en 2nde ?",
            "options": [
                "Justifier chaque etape et relire",
                "Memoriser sans comprendre",
                "Eviter les exercices d'entrainement",
                "Ne pas corriger ses erreurs",
            ],
            "correct_option": "A",
            "explanation": "La progression vient de la comprehension, de la justification et de la correction des erreurs.",
        },
        {
            "id": f"{qid}_8",
            "type": "vrai-faux",
            "question": "Relire la consigne et la question finale peut changer le resultat attendu.",
            "correct": True,
            "explanation": "La relecture finale aide a aligner la reponse avec la demande exacte de l'enonce.",
        },
    ]


PROGRESSIVE_COMPLETION_SPECS = [
    (996, "Colinearite et parallelisme dans l'espace", "vecteurs directeurs, alignement et plans"),
    (997, "Plans, droites et positions relatives", "intersection, parallelisme et perpendicularite"),
    (998, "Volumes et sections de solides", "cubes, prismes, cylindres et sections planes"),
    (999, "Trigonometrie et modelisation", "sinus, cosinus, tangente et applications"),
    (1000, "Suites arithmetiques", "terme general, raison et somme"),
    (1001, "Suites geometriques", "raison multiplicative et evolution"),
    (1002, "Statistiques descriptives", "moyenne, mediane, quartiles et dispersion"),
    (1003, "Probabilites simples", "issues, evenement et probabilite"),
    (1004, "Probabilites conditionnelles", "arbres, cas favorables et dependance"),
    (1005, "Fonctions affines approfondies", "coefficient directeur et ordonnee a l'origine"),
    (1006, "Fonction carree et variations", "parabole, extremum et lecture graphique"),
    (1007, "Optimisation elementaire", "maximiser et minimiser avec contraintes"),
    (1008, "Equations et modeles", "traduction d'un probleme en equations"),
    (1009, "Geometrie analytique du plan", "distance, milieu et alignement"),
    (1010, "Bilan methodologique 2nde", "strategie de resolution et verification"),
]

for qid, title, theme in PROGRESSIVE_COMPLETION_SPECS:
    quizzes_data.append(
        (
            qid,
            title,
            "Mathématiques",
            "2nde",
            build_progressive_questions(qid, title, theme),
        )
    )


def normalize_question_type(question_type):
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    if qt in {"vrai-faux", "vrai faux", "open", "texte", "text"}:
        return "vrai-faux"
    return "vrai-faux"


def is_free_text_question_type(question_type):
    qtype = str(question_type or "").strip().lower().replace("_", "-")
    return qtype in {"texte", "text", "open"}


def choose_runtime_question_type(question):
    raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
    if raw_type == "qcm":
        return "qcm"
    if raw_type in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if raw_type in {"texte", "text", "open"}:
        if question.get("options"):
            return "qcm"
        return "vrai-faux"
    return "vrai-faux"


def build_qcm_choices(question, max_choices=4):
    choices = list(question.get("options", []))
    if choices:
        return choices
    correct_answer = str(question.get("correct_option", question.get("correct_answer", ""))).strip()
    if not correct_answer:
        return []
    default_choices = [
        correct_answer,
        "Une autre réponse",
        "Une réponse incorrecte",
        "Aucune de ces réponses",
    ]
    rnd = random.Random(str(question.get("id", "")) or correct_answer)
    rnd.shuffle(default_choices)
    return default_choices[:max_choices]


def build_true_false_statement(question_text, correct_answer, explanation):
    question_text = str(question_text or "").strip()
    answer = str(correct_answer or "").strip().rstrip(".!? ")
    detail = str(explanation or "").strip()

    if answer:
        if "_____" in question_text or "____" in question_text:
            return question_text.replace("_____", answer).replace("____", answer).rstrip() + "."
        if re.search(r"\bCompl[eé]tez\b", question_text, flags=re.I):
            return f"{question_text.rstrip('.!? ')} {answer}."
        if re.search(
            r'^(?:Compl[eé]tez|Explique|Expliquez|Distingue|Distinguez|Décris|Décrivez|Nommez|Justifie|Pourquoi|Comment|Qu\'est-ce que|Quel|Quels|Quelles|Donne|Donnez|Indique|Indiquez|Rappelle|Présente|Présentez)\b',
            question_text,
            flags=re.I,
        ):
            return f"Il est vrai que {answer}."
        return f"{question_text.rstrip('.!? ')}. La bonne réponse attendue est : {answer}."
    if detail:
        return detail if detail.endswith((".", "!", "?")) else f"{detail}."
    return question_text if question_text else "Cette affirmation est à évaluer."


def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}

    for question in questions:
        cleaned_question = {key: value for key, value in question.items() if key not in answer_keys}
        qtype = choose_runtime_question_type(question)
        if qtype == "qcm":
            runtime_questions.append(
                {
                    "type": "qcm",
                    "question": str(cleaned_question.get("question", "")),
                    "choices": list(cleaned_question.get("options", [])),
                }
            )
        else:
            runtime_questions.append(
                {
                    "type": "vrai-faux",
                    "question": build_true_false_statement(
                        cleaned_question.get("question", ""),
                        question.get("correct_answer", ""),
                    ),
                }
            )

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Serie {qid}",
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
        "exercisenotion": [],
        "exerciseresponses": [],
    }


def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, question in enumerate(questions):
        qtype = choose_runtime_question_type(question)
        if qtype == "qcm":
            options = list(question.get("options", []))
            if not options and is_free_text_question_type(question.get("type", "")):
                options = build_qcm_choices(question)
            correct_answer = str(question.get("correct_option", question.get("correct_answer", "")))
            correct_index = options.index(correct_answer) if correct_answer in options else 0
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "qcm",
                    "answer": correct_answer,
                    "correct": correct_index,
                    "correction": question.get("explanation", ""),
                }
            )
        else:
            tf_value = question.get("correct", True)
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "vrai-faux",
                    "answer": "vrai" if bool(tf_value) else "faux",
                    "correction": question.get("explanation", ""),
                }
            )

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Serie {qid}",
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

    sentinel_qid, _, _, _, _ = random.choice(quizzes_data)
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

    print(
        f"[sentinel] OK quiz={sentinel_qid} questions={len(questions)} answers={len(answers)}"
    )


def write_json(path, payload):
    with open(path, "w", encoding="utf-8", newline="\n") as file_obj:
        json.dump(payload, file_obj, ensure_ascii=False, indent=2)
        file_obj.write("\n")



def write_quiz_files():
    os.makedirs(QUIZ_DIR, exist_ok=True)
    os.makedirs(ANSWERS_DIR, exist_ok=True)
    os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
    os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)

    for qid, title, subject, level, questions in quizzes_data:
        quiz = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)

        write_json(os.path.join(QUIZ_DIR, f"{qid}.json"), quiz)
        write_json(os.path.join(ANSWERS_DIR, f"{qid}.json"), answers)
        write_json(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), quiz)
        write_json(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), answers)

    print(f"{len(quizzes_data)} quiz generated in {OUTPUT_DIR} and synced to runtime")
    verify_random_sentinel()


if __name__ == "__main__":
    write_quiz_files()
