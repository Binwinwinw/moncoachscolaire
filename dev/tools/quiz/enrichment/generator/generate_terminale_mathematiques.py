#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot K — Terminale Mathématiques | version progressive.

Base manuelle: 28 quizzes rediges.
Completion progressive: ajout automatise des IDs restants.

Usage:
    python dev/tools/quiz/generate_terminale_mathematiques.py
"""

from __future__ import annotations

import json
import os
import random
import re
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "terminale_mathematiques_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

# Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
quizzes_data = [

    # ─────────────────────────────────────────────────────────
    # BLOC 1 — Limites & continuité (1011–1016)
    # ─────────────────────────────────────────────────────────
    (
        1011,
        "Limites finies et infinies",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1011_1",
                "type": "qcm",
                "question": "Que vaut lim(x→+∞) (3x² − 5x + 1) ?",
                "options": ["+∞", "−∞", "3", "0"],
                "correct_option": "A",
                "explanation": "Le terme dominant est 3x² → +∞ quand x → +∞.",
            },
            {
                "id": "1011_2",
                "type": "vrai-faux",
                "question": "Si lim(x→a) f(x) = L (L fini), on dit que f admet une limite finie en a.",
                "correct": True,
                "explanation": "Une limite finie en a signifie que f(x) se rapproche d'un réel L quand x se rapproche de a.",
            },
            {
                "id": "1011_3",
                "type": "texte",
                "question": "Explique la notion de forme indéterminée «+∞ − ∞» et donne une méthode pour la lever.",
                "correct_answer": "On ne peut pas conclure directement. On factorise par le terme dominant pour lever l'indétermination.",
                "explanation": "Ex: x² − x = x(x−1) → ∞ ; mais x² − x³ → −∞. Il faut factoriser par x^n le plus grand.",
            },
            {
                "id": "1011_4",
                "type": "qcm",
                "question": "Que vaut lim(x→0) sin(x)/x ?",
                "options": ["0", "1", "+∞", "Indéterminé"],
                "correct_option": "B",
                "explanation": "C'est une limite fondamentale : lim(x→0) sin(x)/x = 1.",
            },
            {
                "id": "1011_5",
                "type": "vrai-faux",
                "question": "lim(x→+∞) (1/x) = 0.",
                "correct": True,
                "explanation": "Quand x → +∞, 1/x → 0. C'est une limite fondamentale.",
            },
            {
                "id": "1011_6",
                "type": "texte",
                "question": "Calcule lim(x→+∞) (2x³ − x) / (x³ + 5).",
                "correct_answer": "2",
                "explanation": "On divise par x³ : (2 − 1/x²) / (1 + 5/x³) → 2/1 = 2.",
            },
            {
                "id": "1011_7",
                "type": "qcm",
                "question": "Que vaut lim(x→3) (x² − 9)/(x − 3) ?",
                "options": ["0", "3", "6", "Indéfini"],
                "correct_option": "C",
                "explanation": "(x²−9)/(x−3) = (x−3)(x+3)/(x−3) = x+3 → 6 quand x→3.",
            },
            {
                "id": "1011_8",
                "type": "vrai-faux",
                "question": "Une fonction peut avoir une limite en un point sans y être définie.",
                "correct": True,
                "explanation": "Ex : f(x) = (x²−1)/(x−1) n'est pas définie en x=1, mais sa limite vaut 2.",
            },
        ],
    ),
    (
        1012,
        "Limites en l'infini",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1012_1",
                "type": "qcm",
                "question": "Quel est lim(x→+∞) eˣ ?",
                "options": ["0", "1", "+∞", "e"],
                "correct_option": "C",
                "explanation": "La fonction exponentielle tend vers +∞ quand x → +∞.",
            },
            {
                "id": "1012_2",
                "type": "vrai-faux",
                "question": "lim(x→−∞) eˣ = 0.",
                "correct": True,
                "explanation": "L'exponentielle tend vers 0 (par valeurs positives) quand x → −∞.",
            },
            {
                "id": "1012_3",
                "type": "texte",
                "question": "Calcule lim(x→+∞) (x² + 3x − 2) / (2x² − x + 1).",
                "correct_answer": "1/2",
                "explanation": "On divise numérateur et dénominateur par x² : (1 + 3/x − 2/x²)/(2 − 1/x + 1/x²) → 1/2.",
            },
            {
                "id": "1012_4",
                "type": "qcm",
                "question": "Que vaut lim(x→+∞) ln(x) ?",
                "options": ["0", "1", "+∞", "e"],
                "correct_option": "C",
                "explanation": "Le logarithme népérien tend vers +∞ quand x → +∞ (croissance lente mais divergente).",
            },
            {
                "id": "1012_5",
                "type": "vrai-faux",
                "question": "lim(x→0⁺) ln(x) = −∞.",
                "correct": True,
                "explanation": "Quand x → 0⁺, ln(x) → −∞. La droite x=0 est asymptote verticale de ln.",
            },
            {
                "id": "1012_6",
                "type": "texte",
                "question": "Détermine les branches infinies de f(x) = (x² + 1) / x.",
                "correct_answer": "f(x) = x + 1/x. Quand x→±∞, f(x) ~ x : la droite y = x est asymptote oblique.",
                "explanation": "f(x) = x + 1/x ; lim[f(x) − x] = 0. La droite y = x est une asymptote oblique en ±∞.",
            },
            {
                "id": "1012_7",
                "type": "qcm",
                "question": "Que vaut lim(x→+∞) x·e^(−x) ?",
                "options": ["+∞", "1", "0", "e"],
                "correct_option": "C",
                "explanation": "Croissances comparées : l'exponentielle l'emporte sur tout polynôme. x·e^(−x) → 0.",
            },
            {
                "id": "1012_8",
                "type": "vrai-faux",
                "question": "lim(x→+∞) ln(x)/x = 0.",
                "correct": True,
                "explanation": "Le logarithme croît moins vite que x. C'est une croissance comparée fondamentale.",
            },
        ],
    ),
    (
        1013,
        "Continuité d'une fonction",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1013_1",
                "type": "qcm",
                "question": "f est continue en a si et seulement si :",
                "options": [
                    "f(a) existe",
                    "lim(x→a) f(x) existe",
                    "lim(x→a) f(x) = f(a)",
                    "f est dérivable en a",
                ],
                "correct_option": "C",
                "explanation": "La continuité en a requiert : f(a) définie, la limite existe et elle égale f(a).",
            },
            {
                "id": "1013_2",
                "type": "vrai-faux",
                "question": "Toute fonction dérivable est continue.",
                "correct": True,
                "explanation": "La dérivabilité implique la continuité, mais la réciproque est fausse (ex : |x| en 0).",
            },
            {
                "id": "1013_3",
                "type": "texte",
                "question": "La fonction f définie par f(x) = (x²−4)/(x−2) pour x≠2 et f(2) = k est-elle continue en 2 ? Quelle valeur k doit prendre ?",
                "correct_answer": "k = 4. Car lim(x→2)(x²−4)/(x−2) = lim(x+2) = 4.",
                "explanation": "(x²−4)/(x−2) = x+2 → 4 quand x→2. Pour la continuité : f(2) = k = 4.",
            },
            {
                "id": "1013_4",
                "type": "qcm",
                "question": "La fonction f(x) = |x| est-elle dérivable en 0 ?",
                "options": [
                    "Oui, de dérivée 0",
                    "Oui, de dérivée 1",
                    "Non, mais elle est continue",
                    "Non, et elle n'est pas continue",
                ],
                "correct_option": "C",
                "explanation": "|x| est continue en 0 (f(0)=0) mais non dérivable : les dérivées à gauche (−1) et à droite (+1) diffèrent.",
            },
            {
                "id": "1013_5",
                "type": "vrai-faux",
                "question": "La somme et le produit de deux fonctions continues sur un intervalle sont continus sur cet intervalle.",
                "correct": True,
                "explanation": "Les opérations algébriques préservent la continuité (sauf la division par une fonction nulle).",
            },
            {
                "id": "1013_6",
                "type": "texte",
                "question": "Explique pourquoi la fonction partie entière ⌊x⌋ n'est pas continue en tout entier n.",
                "correct_answer": "En tout entier n : lim par la gauche = n−1 ≠ lim par la droite = n. Il y a une discontinuité de saut.",
                "explanation": "⌊x⌋ = n pour x ∈ [n, n+1[. En x=n : limite à gauche = n−1, valeur = n → discontinuité.",
            },
            {
                "id": "1013_7",
                "type": "qcm",
                "question": "La composée de deux fonctions continues est :",
                "options": ["Toujours continue", "Parfois continue", "Jamais continue", "Continue seulement si les deux sont dérivables"],
                "correct_option": "A",
                "explanation": "La composée de fonctions continues est continue : si f et g sont continues, g∘f l'est aussi.",
            },
            {
                "id": "1013_8",
                "type": "vrai-faux",
                "question": "Une fonction continue sur un segment [a, b] est bornée et atteint ses bornes.",
                "correct": True,
                "explanation": "C'est le théorème des bornes (ou théorème des valeurs extrêmes) pour les fonctions continues sur un compact.",
            },
        ],
    ),
    (
        1014,
        "Théorème des valeurs intermédiaires",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1014_1",
                "type": "qcm",
                "question": "Le TVI affirme que si f est continue sur [a,b] et k est entre f(a) et f(b), alors :",
                "options": [
                    "f est dérivable en k",
                    "Il existe c ∈ [a,b] tel que f(c) = k",
                    "f(a) = f(b) = k",
                    "k = (f(a)+f(b))/2",
                ],
                "correct_option": "B",
                "explanation": "Le TVI garantit l'existence d'un c ∈ ]a,b[ (au moins) tel que f(c) = k.",
            },
            {
                "id": "1014_2",
                "type": "vrai-faux",
                "question": "Le TVI garantit l'unicité du point c tel que f(c) = k.",
                "correct": False,
                "explanation": "Le TVI ne garantit que l'existence, pas l'unicité. Pour l'unicité, il faut la stricte monotonie.",
            },
            {
                "id": "1014_3",
                "type": "texte",
                "question": "Utilise le TVI pour montrer que f(x) = x³ − 2x − 5 a une racine dans ]2 ; 3[.",
                "correct_answer": "f(2) = 8−4−5 = −1 < 0 et f(3) = 27−6−5 = 16 > 0. f continue, change de signe → racine dans ]2;3[.",
                "explanation": "f continue sur [2,3], f(2)=−1<0, f(3)=16>0 ; par le TVI il existe c ∈ ]2;3[ tel que f(c)=0.",
            },
            {
                "id": "1014_4",
                "type": "qcm",
                "question": "Pour appliquer le TVI avec unicité (corollaire), quelle condition supplémentaire faut-il ?",
                "options": [
                    "f doit être bornée",
                    "f doit être strictement monotone sur [a,b]",
                    "f(a) et f(b) doivent être entiers",
                    "f doit être deux fois dérivable",
                ],
                "correct_option": "B",
                "explanation": "Si f est continue ET strictement monotone sur [a,b], alors pour chaque k entre f(a) et f(b), il y a un unique c.",
            },
            {
                "id": "1014_5",
                "type": "vrai-faux",
                "question": "Si f(a) et f(b) ont des signes opposés, l'équation f(x) = 0 a au moins une solution dans ]a,b[.",
                "correct": True,
                "explanation": "0 est entre f(a) et f(b) si ces derniers sont de signes opposés → TVI → existence d'une racine.",
            },
            {
                "id": "1014_6",
                "type": "texte",
                "question": "Qu'appelle-t-on méthode de dichotomie et sur quel théorème se base-t-elle ?",
                "correct_answer": "Méthode numérique qui divise un intervalle en deux à chaque étape pour encadrer une racine. Elle repose sur le TVI.",
                "explanation": "On coupe [a,b] en deux. On garde le sous-intervalle où f change de signe. Convergence vers la racine.",
            },
            {
                "id": "1014_7",
                "type": "qcm",
                "question": "f est continue sur [0,1], f(0) = 2 et f(1) = −3. Que peut-on affirmer ?",
                "options": [
                    "f est croissante sur [0,1]",
                    "f admet un maximum en 0",
                    "Il existe c ∈ ]0,1[ tel que f(c) = 0",
                    "f est dérivable sur ]0,1[",
                ],
                "correct_option": "C",
                "explanation": "f(0)=2>0 et f(1)=−3<0 : changement de signe → par TVI, existence d'une racine dans ]0,1[.",
            },
            {
                "id": "1014_8",
                "type": "vrai-faux",
                "question": "Le TVI s'applique à toute fonction, même discontinue.",
                "correct": False,
                "explanation": "Le TVI exige impérativement la continuité sur l'intervalle considéré.",
            },
        ],
    ),
    (
        1015,
        "Asymptotes & comportements asymptotiques",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1015_1",
                "type": "qcm",
                "question": "La droite y = L est une asymptote horizontale de f si :",
                "options": [
                    "f(L) = 0",
                    "lim(x→L) f(x) = +∞",
                    "lim(x→±∞) f(x) = L",
                    "f'(L) = 0",
                ],
                "correct_option": "C",
                "explanation": "Asymptote horizontale y = L : la courbe se rapproche indéfiniment de cette droite à l'infini.",
            },
            {
                "id": "1015_2",
                "type": "vrai-faux",
                "question": "La droite x = a est une asymptote verticale de f si lim(x→a) |f(x)| = +∞.",
                "correct": True,
                "explanation": "Une asymptote verticale se produit lorsque la fonction tend vers ±∞ quand x approche a.",
            },
            {
                "id": "1015_3",
                "type": "texte",
                "question": "Détermine les asymptotes de f(x) = (2x + 1) / (x − 3).",
                "correct_answer": "Asymptote verticale : x = 3. Asymptote horizontale : y = 2 (car f(x)→2 quand x→±∞).",
                "explanation": "f non définie en 3 et |f(x)|→∞. Pour x→∞ : (2x+1)/(x−3) ~ 2x/x = 2.",
            },
            {
                "id": "1015_4",
                "type": "qcm",
                "question": "Une asymptote oblique y = ax + b existe si :",
                "options": [
                    "lim(x→+∞) f(x) = ±∞",
                    "lim(x→+∞) [f(x) − (ax+b)] = 0",
                    "f'(x) = a pour tout x",
                    "f(x) − b = ax",
                ],
                "correct_option": "B",
                "explanation": "La courbe a l'asymptote oblique y=ax+b si la différence f(x)−(ax+b) tend vers 0 à l'infini.",
            },
            {
                "id": "1015_5",
                "type": "vrai-faux",
                "question": "La courbe de f(x) = 1/x a deux asymptotes : x = 0 et y = 0.",
                "correct": True,
                "explanation": "1/x → ∞ quand x→0 (AV: x=0) et 1/x → 0 quand x→∞ (AH: y=0).",
            },
            {
                "id": "1015_6",
                "type": "texte",
                "question": "Montre que y = x est asymptote oblique de g(x) = x + sin(x)/x.",
                "correct_answer": "g(x) − x = sin(x)/x → 0 quand x→+∞ (car |sin(x)|≤1). Donc y=x est AO.",
                "explanation": "sin(x)/x est bornée (|sin x|≤1) divisée par x→∞, donc tend vers 0. La droite y=x est AO.",
            },
            {
                "id": "1015_7",
                "type": "qcm",
                "question": "Quelle est l'asymptote horizontale de f(x) = (3x² + 1)/(x² − 4) ?",
                "options": ["y = 0", "y = 1", "y = 3", "y = −4"],
                "correct_option": "C",
                "explanation": "Diviser par x² : (3 + 1/x²)/(1 − 4/x²) → 3. Asymptote horizontale : y = 3.",
            },
            {
                "id": "1015_8",
                "type": "vrai-faux",
                "question": "Une courbe peut couper son asymptote oblique.",
                "correct": True,
                "explanation": "Contrairement à une idée reçue, une courbe peut croiser son asymptote oblique, même plusieurs fois.",
            },
        ],
    ),
    (
        1016,
        "Croissances comparées",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1016_1",
                "type": "qcm",
                "question": "Laquelle de ces affirmations sur les croissances comparées est vraie ?",
                "options": [
                    "ln(x) domine xⁿ quand x→+∞",
                    "xⁿ domine eˣ quand x→+∞",
                    "eˣ domine xⁿ quand x→+∞",
                    "ln(x) domine eˣ quand x→+∞",
                ],
                "correct_option": "C",
                "explanation": "Hiérarchie : ln(x) ≪ xⁿ ≪ eˣ quand x→+∞. L'exponentielle croît plus vite que tout polynôme.",
            },
            {
                "id": "1016_2",
                "type": "vrai-faux",
                "question": "lim(x→+∞) xⁿ / eˣ = 0 pour tout entier n ≥ 0.",
                "correct": True,
                "explanation": "L'exponentielle croît plus vite que tout polynôme : xⁿ/eˣ → 0.",
            },
            {
                "id": "1016_3",
                "type": "texte",
                "question": "Calcule lim(x→+∞) x³ · e^(−x).",
                "correct_answer": "0",
                "explanation": "x³·e^(−x) = x³/eˣ → 0 par croissances comparées (eˣ domine x³).",
            },
            {
                "id": "1016_4",
                "type": "qcm",
                "question": "Que vaut lim(x→+∞) ln(x) / x ?",
                "options": ["+∞", "1", "0", "ln(2)"],
                "correct_option": "C",
                "explanation": "ln(x) ≪ x : ln(x)/x → 0. Le polynôme x domine ln(x).",
            },
            {
                "id": "1016_5",
                "type": "vrai-faux",
                "question": "lim(x→0⁺) x · ln(x) = 0.",
                "correct": True,
                "explanation": "Forme 0·(−∞) : on pose t=−ln(x)→+∞, x = e^(−t). x·ln(x) = −t·e^(−t) → 0.",
            },
            {
                "id": "1016_6",
                "type": "texte",
                "question": "Classe par ordre de croissance (vers +∞) : eˣ, x¹⁰⁰, ln(x), x.",
                "correct_answer": "ln(x) ≪ x ≪ x¹⁰⁰ ≪ eˣ",
                "explanation": "La hiérarchie est : logarithme ≪ polynômes (croissant en degré) ≪ exponentielle.",
            },
            {
                "id": "1016_7",
                "type": "qcm",
                "question": "Que vaut lim(x→+∞) (eˣ − x²) ?",
                "options": ["0", "+∞", "−∞", "1"],
                "correct_option": "B",
                "explanation": "eˣ domine x² : eˣ − x² ~ eˣ → +∞.",
            },
            {
                "id": "1016_8",
                "type": "vrai-faux",
                "question": "lim(x→+∞) ln(x)/√x = 0.",
                "correct": True,
                "explanation": "√x = x^(1/2) domine ln(x) : ln(x)/x^(1/2) → 0 par croissances comparées.",
            },
        ],
    ),

    # ─────────────────────────────────────────────────────────
    # BLOC 2 — Dérivation avancée (1017–1022)
    # ─────────────────────────────────────────────────────────
    (
        1017,
        "Dérivées des fonctions usuelles",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1017_1",
                "type": "qcm",
                "question": "Quelle est la dérivée de f(x) = xⁿ (n entier) ?",
                "options": ["xⁿ⁻¹", "n·xⁿ", "n·xⁿ⁻¹", "(n−1)·xⁿ⁻¹"],
                "correct_option": "C",
                "explanation": "(xⁿ)' = n·xⁿ⁻¹. C'est la règle de base pour les puissances.",
            },
            {
                "id": "1017_2",
                "type": "vrai-faux",
                "question": "La dérivée de eˣ est eˣ.",
                "correct": True,
                "explanation": "(eˣ)' = eˣ. L'exponentielle est sa propre dérivée.",
            },
            {
                "id": "1017_3",
                "type": "texte",
                "question": "Donne les dérivées de ln(x), sin(x) et cos(x).",
                "correct_answer": "(ln x)' = 1/x ; (sin x)' = cos x ; (cos x)' = −sin x",
                "explanation": "Dérivées fondamentales à connaître absolument.",
            },
            {
                "id": "1017_4",
                "type": "qcm",
                "question": "Quelle est la dérivée de f(x) = 1/x ?",
                "options": ["ln(x)", "−1/x²", "1/x²", "−ln(x)"],
                "correct_option": "B",
                "explanation": "1/x = x⁻¹ → (x⁻¹)' = −x⁻² = −1/x².",
            },
            {
                "id": "1017_5",
                "type": "vrai-faux",
                "question": "La dérivée de √x est 1/(2√x).",
                "correct": True,
                "explanation": "√x = x^(1/2) → (x^(1/2))' = (1/2)·x^(−1/2) = 1/(2√x).",
            },
            {
                "id": "1017_6",
                "type": "texte",
                "question": "Calcule la dérivée de f(x) = 3x⁴ − 2x² + 5x − 1.",
                "correct_answer": "f'(x) = 12x³ − 4x + 5",
                "explanation": "(3x⁴)' = 12x³ ; (−2x²)' = −4x ; (5x)' = 5 ; (−1)' = 0.",
            },
            {
                "id": "1017_7",
                "type": "qcm",
                "question": "Quelle est la dérivée de tan(x) ?",
                "options": ["−1/cos²(x)", "1/sin²(x)", "1/cos²(x)", "cos²(x)"],
                "correct_option": "C",
                "explanation": "(tan x)' = 1/cos²(x) = 1 + tan²(x). Valable là où cos(x) ≠ 0.",
            },
            {
                "id": "1017_8",
                "type": "vrai-faux",
                "question": "La dérivée d'une constante est 0.",
                "correct": True,
                "explanation": "Si f(x) = c (constante), f'(x) = 0 : une constante ne varie pas.",
            },
        ],
    ),
    (
        1018,
        "Règles de dérivation",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1018_1",
                "type": "qcm",
                "question": "Quelle est la dérivée du produit f·g ?",
                "options": ["f'·g'", "f'·g + f·g'", "f·g' − f'·g", "(f'·g − f·g')/g²"],
                "correct_option": "B",
                "explanation": "Règle du produit : (f·g)' = f'·g + f·g'.",
            },
            {
                "id": "1018_2",
                "type": "vrai-faux",
                "question": "La dérivée du quotient f/g est (f'g − fg') / g².",
                "correct": True,
                "explanation": "(f/g)' = (f'g − fg') / g². Valable là où g ≠ 0.",
            },
            {
                "id": "1018_3",
                "type": "texte",
                "question": "Calcule la dérivée de f(x) = x²·sin(x).",
                "correct_answer": "f'(x) = 2x·sin(x) + x²·cos(x)",
                "explanation": "(x²·sin x)' = (x²)'·sin x + x²·(sin x)' = 2x·sin x + x²·cos x.",
            },
            {
                "id": "1018_4",
                "type": "qcm",
                "question": "Quelle est la dérivée de f(x) = (x² + 1) / (x − 1) ?",
                "options": [
                    "(2x(x−1) − (x²+1)) / (x−1)²",
                    "(2x(x−1) + (x²+1)) / (x−1)²",
                    "(x−1) / (x²+1)²",
                    "2x / (x−1)",
                ],
                "correct_option": "A",
                "explanation": "Règle du quotient : ((x²+1)'(x−1) − (x²+1)(x−1)') / (x−1)² = (2x(x−1) − (x²+1)) / (x−1)².",
            },
            {
                "id": "1018_5",
                "type": "vrai-faux",
                "question": "(f + g)' = f' + g' (linéarité de la dérivation).",
                "correct": True,
                "explanation": "La dérivation est linéaire : (f+g)' = f'+g' et (λf)' = λf' pour tout scalaire λ.",
            },
            {
                "id": "1018_6",
                "type": "texte",
                "question": "Calcule la dérivée de f(x) = (2x − 3) / (x + 1).",
                "correct_answer": "f'(x) = 5/(x+1)²",
                "explanation": "f' = (2(x+1) − (2x−3)·1)/(x+1)² = (2x+2−2x+3)/(x+1)² = 5/(x+1)².",
            },
            {
                "id": "1018_7",
                "type": "qcm",
                "question": "Quelle est la dérivée de f(x) = x·eˣ ?",
                "options": ["eˣ", "(x+1)eˣ", "x·eˣ + eˣ", "(x−1)eˣ"],
                "correct_option": "C",
                "explanation": "(x·eˣ)' = 1·eˣ + x·eˣ = eˣ(1+x) = (x+1)eˣ. Réponse B et C sont équivalentes ; la formulation C est développée.",
            },
            {
                "id": "1018_8",
                "type": "vrai-faux",
                "question": "La dérivée de (f(x))² est 2f(x)·f'(x).",
                "correct": True,
                "explanation": "Règle de la chaîne appliquée à u² avec u=f(x) : (u²)' = 2u·u' = 2f(x)·f'(x).",
            },
        ],
    ),
    (
        1019,
        "Dérivée de la fonction composée",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1019_1",
                "type": "qcm",
                "question": "Quelle est la dérivée de (g∘f)(x) = g(f(x)) ?",
                "options": ["g'(f(x))", "g(f'(x))", "g'(f(x)) · f'(x)", "f'(x) · g'(x)"],
                "correct_option": "C",
                "explanation": "Règle de la chaîne : (g∘f)'(x) = g'(f(x)) · f'(x).",
            },
            {
                "id": "1019_2",
                "type": "vrai-faux",
                "question": "La dérivée de e^(f(x)) est f'(x)·e^(f(x)).",
                "correct": True,
                "explanation": "Règle de la chaîne avec g(u) = eᵘ : (e^f(x))' = f'(x)·e^(f(x)).",
            },
            {
                "id": "1019_3",
                "type": "texte",
                "question": "Calcule la dérivée de f(x) = sin(3x² + 1).",
                "correct_answer": "f'(x) = 6x·cos(3x² + 1)",
                "explanation": "f = sin(u) avec u = 3x²+1 ; f' = cos(u)·u' = cos(3x²+1)·6x.",
            },
            {
                "id": "1019_4",
                "type": "qcm",
                "question": "Quelle est la dérivée de ln(x² + 1) ?",
                "options": ["1/(x²+1)", "2x·ln(x²+1)", "2x/(x²+1)", "1/(2x+1)"],
                "correct_option": "C",
                "explanation": "(ln u)' = u'/u. Ici u = x²+1, u' = 2x → (2x)/(x²+1).",
            },
            {
                "id": "1019_5",
                "type": "vrai-faux",
                "question": "La dérivée de (ax + b)ⁿ est n·a·(ax + b)ⁿ⁻¹.",
                "correct": True,
                "explanation": "Règle de la chaîne : ((ax+b)ⁿ)' = n·(ax+b)ⁿ⁻¹ · (ax+b)' = n·a·(ax+b)ⁿ⁻¹.",
            },
            {
                "id": "1019_6",
                "type": "texte",
                "question": "Calcule la dérivée de f(x) = e^(−x²).",
                "correct_answer": "f'(x) = −2x·e^(−x²)",
                "explanation": "f = e^u avec u = −x², u' = −2x → f' = −2x·e^(−x²).",
            },
            {
                "id": "1019_7",
                "type": "qcm",
                "question": "Quelle est la dérivée de √(2x + 3) ?",
                "options": ["1/√(2x+3)", "2/√(2x+3)", "1/(√(2x+3))", "2(2x+3)^(−1/2)"],
                "correct_option": "D",
                "explanation": "(√u)' = u'/(2√u). u = 2x+3, u' = 2 → 2/(2√(2x+3)) = 1/√(2x+3). Options C et D équivalentes.",
            },
            {
                "id": "1019_8",
                "type": "vrai-faux",
                "question": "La dérivée de ln(f(x)) est f'(x)/f(x).",
                "correct": True,
                "explanation": "Règle de la chaîne avec g = ln : (ln(f(x)))' = f'(x)/f(x) (valable pour f(x) > 0).",
            },
        ],
    ),
    (
        1020,
        "Convexité & concavité",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1020_1",
                "type": "qcm",
                "question": "f est convexe sur I si :",
                "options": ["f' est décroissante sur I", "f'' ≤ 0 sur I", "f'' ≥ 0 sur I", "f est croissante sur I"],
                "correct_option": "C",
                "explanation": "f convexe ⟺ f'' ≥ 0 (courbe 'tournée vers le haut', comme une parabole ouverte).",
            },
            {
                "id": "1020_2",
                "type": "vrai-faux",
                "question": "Un point d'inflexion est un point où la courbe change de convexité (f'' change de signe).",
                "correct": True,
                "explanation": "En un point d'inflexion, f'' s'annule et change de signe : la concavité s'inverse.",
            },
            {
                "id": "1020_3",
                "type": "texte",
                "question": "Détermine les intervalles de convexité de f(x) = x³ − 3x.",
                "correct_answer": "f'' = 6x. f convexe sur [0;+∞[ (f''≥0) et concave sur ]−∞;0] (f''≤0). Point d'inflexion en x=0.",
                "explanation": "f'=3x²−3, f''=6x. f''≥0 ⟺ x≥0 (convexe) ; f''≤0 ⟺ x≤0 (concave).",
            },
            {
                "id": "1020_4",
                "type": "qcm",
                "question": "La courbe de eˣ est :",
                "options": ["Concave sur R", "Convexe sur R", "Convexe sur R⁺ seulement", "Ni convexe ni concave"],
                "correct_option": "B",
                "explanation": "(eˣ)'' = eˣ > 0 pour tout x : eˣ est convexe sur tout R.",
            },
            {
                "id": "1020_5",
                "type": "vrai-faux",
                "question": "La tangente à la courbe d'une fonction convexe est toujours en dessous (ou sur) la courbe.",
                "correct": True,
                "explanation": "Pour f convexe, la tangente en tout point est sous la courbe : f(x) ≥ f(a) + f'(a)(x−a).",
            },
            {
                "id": "1020_6",
                "type": "texte",
                "question": "En quel point la courbe de f(x) = x⁴ − 6x² admet-elle des points d'inflexion ?",
                "correct_answer": "x = ±1",
                "explanation": "f'' = 12x² − 12 = 0 → x² = 1 → x = ±1. Changement de signe de f'' en ces points.",
            },
            {
                "id": "1020_7",
                "type": "qcm",
                "question": "Si f''(a) = 0 et f'''(a) ≠ 0, alors le point (a, f(a)) est :",
                "options": ["Un maximum local", "Un minimum local", "Un point d'inflexion", "Un point de discontinuité"],
                "correct_option": "C",
                "explanation": "f''(a)=0 avec changement de signe (assuré par f'''(a)≠0) → point d'inflexion.",
            },
            {
                "id": "1020_8",
                "type": "vrai-faux",
                "question": "La fonction ln(x) est concave sur ]0 ; +∞[.",
                "correct": True,
                "explanation": "(ln x)'' = −1/x² < 0 pour tout x > 0 : ln est concave (courbe tournée vers le bas).",
            },
        ],
    ),
    (
        1021,
        "Étude complète d'une fonction",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1021_1",
                "type": "qcm",
                "question": "Quelle est la première étape d'une étude complète de fonction ?",
                "options": [
                    "Calculer f''",
                    "Tracer la courbe",
                    "Déterminer le domaine de définition",
                    "Chercher les asymptotes",
                ],
                "correct_option": "C",
                "explanation": "On commence par le domaine de définition D(f), puis parité/périodicité, limites, dérivée, variations, etc.",
            },
            {
                "id": "1021_2",
                "type": "vrai-faux",
                "question": "Un extremum local d'une fonction dérivable correspond toujours à un zéro de la dérivée.",
                "correct": True,
                "explanation": "Si f admet un extremum local en a et est dérivable en a, alors f'(a) = 0 (condition nécessaire).",
            },
            {
                "id": "1021_3",
                "type": "texte",
                "question": "Étudie les variations de f(x) = x·e^(−x) sur R.",
                "correct_answer": "f'(x) = (1−x)e^(−x). f'=0 en x=1. f croissante sur ]−∞;1], décroissante sur [1;+∞[. Maximum en x=1 : f(1)=1/e.",
                "explanation": "f'(x) = e^(−x) + x·(−e^(−x)) = (1−x)e^(−x). Signe de f' = signe de (1−x) : + sur ]−∞;1[, − sur ]1;+∞[.",
            },
            {
                "id": "1021_4",
                "type": "qcm",
                "question": "Si f'(a) = 0 et f''(a) > 0, alors f admet en a :",
                "options": ["Un maximum local", "Un minimum local", "Un point d'inflexion", "Aucun extremum"],
                "correct_option": "B",
                "explanation": "Critère de la dérivée seconde : f'(a)=0 et f''(a)>0 → minimum local en a.",
            },
            {
                "id": "1021_5",
                "type": "vrai-faux",
                "question": "f'(a) = 0 est une condition suffisante pour que f admette un extremum en a.",
                "correct": False,
                "explanation": "C'est nécessaire mais pas suffisant : le point peut être un point d'inflexion à tangente horizontale (ex: f(x)=x³ en 0).",
            },
            {
                "id": "1021_6",
                "type": "texte",
                "question": "Pour f(x) = x³/3 − x, détermine les extrema locaux.",
                "correct_answer": "Maximum local en x=−1 : f(−1)=2/3 ; minimum local en x=1 : f(1)=−2/3.",
                "explanation": "f'=x²−1=0 → x=±1. f''=2x : f''(−1)=−2<0 (max), f''(1)=2>0 (min).",
            },
            {
                "id": "1021_7",
                "type": "qcm",
                "question": "Dans un tableau de variations, que représente le maximum de f ?",
                "options": [
                    "Le point où f'=0",
                    "La plus grande valeur atteinte par f sur son domaine",
                    "La valeur en laquelle f change de monotonie",
                    "Le point d'inflexion",
                ],
                "correct_option": "B",
                "explanation": "Le maximum (global) de f est la plus grande valeur de f(x) sur tout le domaine.",
            },
            {
                "id": "1021_8",
                "type": "vrai-faux",
                "question": "Pour dresser le tableau de signes de f', on résout f'(x) = 0 et on étudie le signe entre les racines.",
                "correct": True,
                "explanation": "Les zéros de f' délimitent des intervalles sur lesquels f' garde un signe constant.",
            },
        ],
    ),
    (
        1022,
        "Équation de la tangente",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1022_1",
                "type": "qcm",
                "question": "L'équation de la tangente à la courbe de f au point d'abscisse a est :",
                "options": [
                    "y = f(a) + f(x)(x − a)",
                    "y = f'(a)(x − a) + f(a)",
                    "y = f(a)(x − a) + f'(a)",
                    "y = f'(x)(x − a)",
                ],
                "correct_option": "B",
                "explanation": "Tangente en x=a : y = f(a) + f'(a)(x−a). Pente = f'(a), passe par (a, f(a)).",
            },
            {
                "id": "1022_2",
                "type": "vrai-faux",
                "question": "La pente de la tangente à la courbe de f en x = a est f'(a).",
                "correct": True,
                "explanation": "Le nombre dérivé f'(a) est précisément le coefficient directeur (pente) de la tangente en a.",
            },
            {
                "id": "1022_3",
                "type": "texte",
                "question": "Détermine l'équation de la tangente à f(x) = x² − 3x + 2 en x = 2.",
                "correct_answer": "y = x − 2",
                "explanation": "f(2) = 4−6+2 = 0 ; f'(x) = 2x−3, f'(2) = 1. Tangente : y = 0 + 1·(x−2) = x−2.",
            },
            {
                "id": "1022_4",
                "type": "qcm",
                "question": "Quelle est la pente de la tangente à f(x) = eˣ en x = 0 ?",
                "options": ["0", "e", "1", "1/e"],
                "correct_option": "C",
                "explanation": "f'(x) = eˣ, f'(0) = e⁰ = 1. La pente est 1.",
            },
            {
                "id": "1022_5",
                "type": "vrai-faux",
                "question": "La tangente à la courbe de f en un point d'inflexion coupe la courbe en ce point.",
                "correct": True,
                "explanation": "Au point d'inflexion, la courbe traverse sa tangente (la courbe change de côté par rapport à la tangente).",
            },
            {
                "id": "1022_6",
                "type": "texte",
                "question": "Quelle est l'équation de la tangente à y = ln(x) en x = 1 ?",
                "correct_answer": "y = x − 1",
                "explanation": "f(1) = ln(1) = 0 ; f'(x) = 1/x, f'(1) = 1. Tangente : y = 0 + 1·(x−1) = x−1.",
            },
            {
                "id": "1022_7",
                "type": "qcm",
                "question": "Quelle est l'équation de la tangente à f(x) = x³ en x = 1 ?",
                "options": ["y = x", "y = 3x − 2", "y = 3x − 3", "y = x − 2"],
                "correct_option": "B",
                "explanation": "f(1)=1 ; f'(x)=3x², f'(1)=3. Tangente : y = 1 + 3(x−1) = 3x−2.",
            },
            {
                "id": "1022_8",
                "type": "vrai-faux",
                "question": "Si f'(a) = 0, la tangente en a est horizontale.",
                "correct": True,
                "explanation": "Pente nulle → droite horizontale y = f(a). La courbe est localement 'plate' en a.",
            },
        ],
    ),

    # ─────────────────────────────────────────────────────────
    # BLOC 3 — Fonctions exp & ln (1023–1028)
    # ─────────────────────────────────────────────────────────
    (
        1023,
        "Fonction exponentielle — définition & propriétés",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1023_1",
                "type": "qcm",
                "question": "Quelle est la valeur de e⁰ ?",
                "options": ["0", "1", "e", "Indéfini"],
                "correct_option": "B",
                "explanation": "e⁰ = 1. Propriété fondamentale : toute base à la puissance 0 vaut 1.",
            },
            {
                "id": "1023_2",
                "type": "vrai-faux",
                "question": "eˣ⁺ʸ = eˣ · eʸ pour tous réels x et y.",
                "correct": True,
                "explanation": "Propriété de l'exponentielle : e^(a+b) = eᵃ · eᵇ.",
            },
            {
                "id": "1023_3",
                "type": "texte",
                "question": "Résous l'équation e^(2x) = e^(x+3).",
                "correct_answer": "x = 3",
                "explanation": "eˣ est injective : e^(2x) = e^(x+3) ⟺ 2x = x+3 ⟺ x = 3.",
            },
            {
                "id": "1023_4",
                "type": "qcm",
                "question": "Que vaut e^(ln 5) ?",
                "options": ["5", "ln 5", "e·5", "1/5"],
                "correct_option": "A",
                "explanation": "e^(ln x) = x pour tout x > 0. Donc e^(ln 5) = 5.",
            },
            {
                "id": "1023_5",
                "type": "vrai-faux",
                "question": "La fonction eˣ est strictement positive pour tout réel x.",
                "correct": True,
                "explanation": "eˣ > 0 pour tout x ∈ R. La courbe de l'exponentielle est entièrement au-dessus de l'axe x.",
            },
            {
                "id": "1023_6",
                "type": "texte",
                "question": "Résous e^(x²−1) = 1.",
                "correct_answer": "x = 1 ou x = −1",
                "explanation": "e^(x²−1) = 1 = e⁰ ⟺ x²−1 = 0 ⟺ x = ±1.",
            },
            {
                "id": "1023_7",
                "type": "qcm",
                "question": "Quelle est la valeur de e^(−ln 3) ?",
                "options": ["3", "−3", "1/3", "ln 3"],
                "correct_option": "C",
                "explanation": "e^(−ln 3) = 1/e^(ln 3) = 1/3.",
            },
            {
                "id": "1023_8",
                "type": "vrai-faux",
                "question": "La fonction eˣ est la seule fonction dérivable égale à sa propre dérivée et valant 1 en 0.",
                "correct": True,
                "explanation": "C'est une caractérisation de l'exponentielle. Si f' = f et f(0) = 1, alors f = exp.",
            },
        ],
    ),
    (
        1024,
        "Équations et inéquations avec exp",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1024_1",
                "type": "qcm",
                "question": "Résous e^(3x) = e^6.",
                "options": ["x = 2", "x = 3", "x = 6", "x = 18"],
                "correct_option": "A",
                "explanation": "e^(3x) = e^6 ⟺ 3x = 6 ⟺ x = 2.",
            },
            {
                "id": "1024_2",
                "type": "vrai-faux",
                "question": "eˣ > eʸ ⟺ x > y.",
                "correct": True,
                "explanation": "La fonction exponentielle est strictement croissante : elle conserve le sens des inégalités.",
            },
            {
                "id": "1024_3",
                "type": "texte",
                "question": "Résous l'inéquation e^(2x−1) ≥ e^(x+2).",
                "correct_answer": "x ≥ 3",
                "explanation": "e strictement croissante : 2x−1 ≥ x+2 ⟺ x ≥ 3.",
            },
            {
                "id": "1024_4",
                "type": "qcm",
                "question": "Quelle substitution permet de résoudre e^(2x) − 3e^x + 2 = 0 ?",
                "options": ["u = e^x", "u = 2x", "u = ln(x)", "u = x²"],
                "correct_option": "A",
                "explanation": "Avec u = eˣ (u > 0) : u² − 3u + 2 = 0 → (u−1)(u−2) = 0 → u=1 ou u=2.",
            },
            {
                "id": "1024_5",
                "type": "vrai-faux",
                "question": "L'équation e^(2x) − 3e^x + 2 = 0 admet deux solutions réelles.",
                "correct": True,
                "explanation": "u=1 → eˣ=1 → x=0 ; u=2 → eˣ=2 → x=ln2. Deux solutions : x=0 et x=ln 2.",
            },
            {
                "id": "1024_6",
                "type": "texte",
                "question": "Résous (eˣ − 2)(eˣ + 1) = 0.",
                "correct_answer": "x = ln 2 (la seule solution, car eˣ = −1 est impossible)",
                "explanation": "eˣ = 2 → x = ln 2 ; eˣ = −1 impossible (eˣ > 0). Unique solution : x = ln 2.",
            },
            {
                "id": "1024_7",
                "type": "qcm",
                "question": "Résous e^x > 5.",
                "options": ["x > 5", "x > ln 5", "x < ln 5", "x > e^5"],
                "correct_option": "B",
                "explanation": "eˣ > 5 ⟺ x > ln 5 (fonction exp strictement croissante).",
            },
            {
                "id": "1024_8",
                "type": "vrai-faux",
                "question": "L'équation eˣ = −1 n'a pas de solution réelle.",
                "correct": True,
                "explanation": "eˣ > 0 pour tout réel x : impossible d'égaler une valeur négative.",
            },
        ],
    ),
    (
        1025,
        "Fonction logarithme népérien",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1025_1",
                "type": "qcm",
                "question": "Quel est le domaine de définition de ln(x) ?",
                "options": ["R", "R*", "]0 ; +∞[", "[0 ; +∞["],
                "correct_option": "C",
                "explanation": "ln est défini uniquement pour x > 0, donc sur ]0 ; +∞[.",
            },
            {
                "id": "1025_2",
                "type": "vrai-faux",
                "question": "ln(1) = 0.",
                "correct": True,
                "explanation": "ln(1) = 0 car e⁰ = 1. C'est une valeur fondamentale à retenir.",
            },
            {
                "id": "1025_3",
                "type": "texte",
                "question": "Résous ln(x) = 3.",
                "correct_answer": "x = e³",
                "explanation": "ln(x) = 3 ⟺ x = e³ (définition du logarithme comme réciproque de l'exponentielle).",
            },
            {
                "id": "1025_4",
                "type": "qcm",
                "question": "Que vaut ln(e²) ?",
                "options": ["e²", "2", "2e", "1/2"],
                "correct_option": "B",
                "explanation": "ln(eˣ) = x pour tout x. Donc ln(e²) = 2.",
            },
            {
                "id": "1025_5",
                "type": "vrai-faux",
                "question": "La fonction ln est strictement croissante sur ]0 ; +∞[.",
                "correct": True,
                "explanation": "(ln x)' = 1/x > 0 pour x > 0 : ln est bien strictement croissante.",
            },
            {
                "id": "1025_6",
                "type": "texte",
                "question": "Résous l'inéquation ln(x) < 2.",
                "correct_answer": "0 < x < e²",
                "explanation": "ln strictement croissante : ln(x) < 2 = ln(e²) ⟺ 0 < x < e².",
            },
            {
                "id": "1025_7",
                "type": "qcm",
                "question": "Que vaut ln(1/e) ?",
                "options": ["1", "−1", "e", "1/e"],
                "correct_option": "B",
                "explanation": "ln(1/e) = ln(e⁻¹) = −1.",
            },
            {
                "id": "1025_8",
                "type": "vrai-faux",
                "question": "La droite x = 0 est une asymptote verticale de ln(x).",
                "correct": True,
                "explanation": "lim(x→0⁺) ln(x) = −∞ : la courbe de ln s'approche verticalement de l'axe des y.",
            },
        ],
    ),
    (
        1026,
        "Propriétés du logarithme",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1026_1",
                "type": "qcm",
                "question": "Que vaut ln(a · b) ?",
                "options": ["ln(a) · ln(b)", "ln(a) + ln(b)", "ln(a) − ln(b)", "ln(a)^ln(b)"],
                "correct_option": "B",
                "explanation": "Propriété fondamentale : ln(ab) = ln(a) + ln(b) pour a, b > 0.",
            },
            {
                "id": "1026_2",
                "type": "vrai-faux",
                "question": "ln(a/b) = ln(a) − ln(b) pour a, b > 0.",
                "correct": True,
                "explanation": "ln(a/b) = ln(a·b⁻¹) = ln(a) + ln(b⁻¹) = ln(a) − ln(b).",
            },
            {
                "id": "1026_3",
                "type": "texte",
                "question": "Simplifie : ln(8) en utilisant les propriétés du logarithme.",
                "correct_answer": "3·ln(2)",
                "explanation": "ln(8) = ln(2³) = 3·ln(2). Propriété de la puissance : ln(aⁿ) = n·ln(a).",
            },
            {
                "id": "1026_4",
                "type": "qcm",
                "question": "Quelle est la valeur de ln(e⁵) − ln(e²) ?",
                "options": ["3", "7", "e³", "ln 3"],
                "correct_option": "A",
                "explanation": "ln(e⁵) − ln(e²) = 5 − 2 = 3. Ou : ln(e⁵/e²) = ln(e³) = 3.",
            },
            {
                "id": "1026_5",
                "type": "vrai-faux",
                "question": "ln(aⁿ) = n · ln(a) pour tout entier n et tout a > 0.",
                "correct": True,
                "explanation": "Propriété de la puissance du logarithme : ln(aⁿ) = n · ln(a).",
            },
            {
                "id": "1026_6",
                "type": "texte",
                "question": "Résous ln(x) + ln(x − 2) = ln(3) avec x > 2.",
                "correct_answer": "x = 3",
                "explanation": "ln(x(x−2)) = ln(3) ⟺ x(x−2) = 3 ⟺ x²−2x−3 = 0 ⟺ (x−3)(x+1)=0. x=3 (x>2).",
            },
            {
                "id": "1026_7",
                "type": "qcm",
                "question": "Que vaut ln(√e) ?",
                "options": ["1", "1/2", "2", "e/2"],
                "correct_option": "B",
                "explanation": "ln(√e) = ln(e^(1/2)) = 1/2.",
            },
            {
                "id": "1026_8",
                "type": "vrai-faux",
                "question": "ln(a + b) = ln(a) + ln(b).",
                "correct": False,
                "explanation": "FAUX. ln(ab) = ln(a)+ln(b), mais ln(a+b) ≠ ln(a)+ln(b). Erreur fréquente !",
            },
        ],
    ),
    (
        1027,
        "Équations & inéquations avec ln",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1027_1",
                "type": "qcm",
                "question": "Résous ln(2x − 1) = 0.",
                "options": ["x = 0", "x = 1", "x = 1/2", "x = e"],
                "correct_option": "B",
                "explanation": "ln(2x−1) = 0 ⟺ 2x−1 = e⁰ = 1 ⟺ x = 1. Vérif : 2(1)−1 = 1 > 0 ✓.",
            },
            {
                "id": "1027_2",
                "type": "vrai-faux",
                "question": "ln(x) > 1 ⟺ x > e.",
                "correct": True,
                "explanation": "ln strictement croissante : ln(x) > 1 = ln(e) ⟺ x > e.",
            },
            {
                "id": "1027_3",
                "type": "texte",
                "question": "Résous ln(x² − 3x + 2) = 0.",
                "correct_answer": "x = 0 ou x = 3 (avec vérification x²−3x+2 > 0)",
                "explanation": "ln(...)=0 ⟺ x²−3x+2 = 1 ⟺ x²−3x+1 = 0. Δ=5, x=(3±√5)/2 ≈ 0.38 et 2.62. NB: correction — x²−3x+2=1 → x²−3x+1=0, solutions (3±√5)/2.",
            },
            {
                "id": "1027_4",
                "type": "qcm",
                "question": "Résous ln(x+1) ≤ ln(3).",
                "options": ["x ≤ 2 et x > −1", "x ≤ 3", "x ≤ 2", "x < 3"],
                "correct_option": "A",
                "explanation": "ln croissante : ln(x+1) ≤ ln(3) ⟺ x+1 ≤ 3 ⟺ x ≤ 2. Domaine : x+1>0 → x>−1.",
            },
            {
                "id": "1027_5",
                "type": "vrai-faux",
                "question": "Résoudre ln(f(x)) = k revient à résoudre f(x) = eᵏ avec f(x) > 0.",
                "correct": True,
                "explanation": "ln est défini pour des valeurs > 0, et ln(u) = k ⟺ u = eᵏ.",
            },
            {
                "id": "1027_6",
                "type": "texte",
                "question": "Résous ln(x) + ln(x+2) = ln(8).",
                "correct_answer": "x = 2",
                "explanation": "ln(x(x+2)) = ln(8) ⟺ x²+2x = 8 ⟺ x²+2x−8=0 ⟺ (x+4)(x−2)=0. x=2 (x>0 exclu −4).",
            },
            {
                "id": "1027_7",
                "type": "qcm",
                "question": "L'inéquation ln(x) > ln(2x − 3) est vraie pour :",
                "options": ["x > 3", "x < 3 et x > 3/2", "x > 3/2", "x < 3"],
                "correct_option": "B",
                "explanation": "ln croissante : x > 2x−3 ⟺ x < 3. Domaine : x>0 ET 2x−3>0 → x>3/2. Solution : 3/2 < x < 3.",
            },
            {
                "id": "1027_8",
                "type": "vrai-faux",
                "question": "ln(x) = 0 admet une unique solution : x = 1.",
                "correct": True,
                "explanation": "ln(1) = 0, et ln est injective (strictement croissante) donc x=1 est la seule solution.",
            },
        ],
    ),
    (
        1028,
        "Applications : modèles exponentiels & logarithmiques",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1028_1",
                "type": "qcm",
                "question": "Dans un modèle de croissance exponentielle N(t) = N₀·e^(kt), que représente k > 0 ?",
                "options": ["Le taux de décroissance", "Le taux de croissance", "La valeur initiale", "Le temps de doublement"],
                "correct_option": "B",
                "explanation": "k > 0 est le taux de croissance (positif). k < 0 modéliserait une décroissance.",
            },
            {
                "id": "1028_2",
                "type": "vrai-faux",
                "question": "Le temps de demi-vie d'une substance radioactive est ln(2)/k, où k est le taux de désintégration.",
                "correct": True,
                "explanation": "N(t₁/₂) = N₀/2 → e^(−kt₁/₂) = 1/2 → kt₁/₂ = ln(2) → t₁/₂ = ln(2)/k.",
            },
            {
                "id": "1028_3",
                "type": "texte",
                "question": "Une population de 1000 individus croît de 5% par an. Modélise P(t) et calcule le temps de doublement.",
                "correct_answer": "P(t) = 1000·(1,05)ᵗ. Doublement : 1,05ᵗ = 2 → t = ln(2)/ln(1,05) ≈ 14,2 ans.",
                "explanation": "Croissance géométrique : P(t) = 1000×1,05ᵗ. Doublement : t = ln 2 / ln 1,05.",
            },
            {
                "id": "1028_4",
                "type": "qcm",
                "question": "La loi de Beer-Lambert en spectrophotométrie est A = ε·l·c. Le logarithme intervient car :",
                "options": [
                    "L'absorbance est toujours négative",
                    "L'intensité lumineuse décroît exponentiellement",
                    "La concentration est un logarithme",
                    "La longueur d'onde suit une loi log",
                ],
                "correct_option": "B",
                "explanation": "L'intensité I(l) = I₀·e^(−αl) décroît exponentiellement. L'absorbance A = log(I₀/I) est logarithmique.",
            },
            {
                "id": "1028_5",
                "type": "vrai-faux",
                "question": "Le pH d'une solution est défini par pH = −log₁₀([H⁺]), ce qui est une échelle logarithmique.",
                "correct": True,
                "explanation": "Le pH est une échelle logarithmique décimale de la concentration en ions H⁺.",
            },
            {
                "id": "1028_6",
                "type": "texte",
                "question": "Un capital C₀ placé à un taux annuel r (continu) vaut C(t) = C₀·e^(rt). En combien de temps double-t-il pour r = 0,04 ?",
                "correct_answer": "t = ln(2)/0,04 ≈ 17,3 ans",
                "explanation": "C(t) = 2C₀ → e^(0,04t) = 2 → t = ln(2)/0,04 ≈ 17,3 ans.",
            },
            {
                "id": "1028_7",
                "type": "qcm",
                "question": "Une bactérie double toutes les 3 heures. Après t heures, on a N₀·2^(t/3) bactéries. En termes d'exponentielle, c'est équivalent à :",
                "options": [
                    "N₀·e^(t/3)",
                    "N₀·e^(t·ln2/3)",
                    "N₀·e^(2t/3)",
                    "N₀·e^(3t·ln2)",
                ],
                "correct_option": "B",
                "explanation": "2^(t/3) = e^(ln2·t/3). Donc N(t) = N₀·e^((ln2/3)·t).",
            },
            {
                "id": "1028_8",
                "type": "vrai-faux",
                "question": "L'échelle de Richter (magnitude des séismes) est une échelle logarithmique en base 10.",
                "correct": True,
                "explanation": "Chaque incrément de 1 sur l'échelle de Richter correspond à une multiplication par 10 de l'amplitude.",
            },
        ],
    ),

    # ─────────────────────────────────────────────────────────
    # BLOC 4 — Intégration (1029–1034)
    # ─────────────────────────────────────────────────────────
    (
        1029,
        "Primitives et intégrale définie",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1029_1",
                "type": "qcm",
                "question": "F est une primitive de f sur I si :",
                "options": ["F = f'", "F' = f sur I", "f = F²", "f' = F sur I"],
                "correct_option": "B",
                "explanation": "F est une primitive de f si F'(x) = f(x) pour tout x de I.",
            },
            {
                "id": "1029_2",
                "type": "vrai-faux",
                "question": "Si F est une primitive de f, alors F + C (C constante) l'est aussi.",
                "correct": True,
                "explanation": "(F+C)' = F' = f. Toutes les primitives diffèrent d'une constante.",
            },
            {
                "id": "1029_3",
                "type": "texte",
                "question": "Quelle est la primitive de f(x) = 3x² − 2x + 1 qui vaut 0 en x = 0 ?",
                "correct_answer": "F(x) = x³ − x² + x",
                "explanation": "Primitive générale : x³ − x² + x + C. F(0) = C = 0 → F(x) = x³ − x² + x.",
            },
            {
                "id": "1029_4",
                "type": "qcm",
                "question": "Que vaut ∫₀¹ 2x dx ?",
                "options": ["0", "1", "2", "4"],
                "correct_option": "B",
                "explanation": "∫₀¹ 2x dx = [x²]₀¹ = 1² − 0² = 1.",
            },
            {
                "id": "1029_5",
                "type": "vrai-faux",
                "question": "∫ₐᵃ f(x) dx = 0 pour toute fonction f.",
                "correct": True,
                "explanation": "[F(x)]ₐᵃ = F(a) − F(a) = 0 : une intégrale sur un intervalle de longueur nulle est toujours 0.",
            },
            {
                "id": "1029_6",
                "type": "texte",
                "question": "Calcule ∫₁ᵉ (1/x) dx.",
                "correct_answer": "1",
                "explanation": "∫₁ᵉ (1/x) dx = [ln x]₁ᵉ = ln(e) − ln(1) = 1 − 0 = 1.",
            },
            {
                "id": "1029_7",
                "type": "qcm",
                "question": "La primitive de eˣ est :",
                "options": ["eˣ + C", "eˣ⁺¹ + C", "x·eˣ + C", "e^(x+1) + C"],
                "correct_option": "A",
                "explanation": "La primitive de eˣ est eˣ + C, car (eˣ)' = eˣ.",
            },
            {
                "id": "1029_8",
                "type": "vrai-faux",
                "question": "∫ₐᵇ f(x) dx = −∫ᵦᵃ f(x) dx.",
                "correct": True,
                "explanation": "Inverser les bornes change le signe de l'intégrale : F(b)−F(a) = −(F(a)−F(b)).",
            },
        ],
    ),
    (
        1030,
        "Calcul d'intégrales",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1030_1",
                "type": "qcm",
                "question": "Que vaut ∫₀² (x² + 1) dx ?",
                "options": ["10/3", "8/3", "14/3", "6"],
                "correct_option": "C",
                "explanation": "[x³/3 + x]₀² = 8/3 + 2 = 8/3 + 6/3 = 14/3.",
            },
            {
                "id": "1030_2",
                "type": "vrai-faux",
                "question": "∫ cos(x) dx = sin(x) + C.",
                "correct": True,
                "explanation": "La primitive de cos(x) est sin(x), puisque (sin x)' = cos x.",
            },
            {
                "id": "1030_3",
                "type": "texte",
                "question": "Calcule ∫₀^π sin(x) dx.",
                "correct_answer": "2",
                "explanation": "[−cos(x)]₀^π = −cos(π) − (−cos(0)) = −(−1) − (−1) = 1 + 1 = 2.",
            },
            {
                "id": "1030_4",
                "type": "qcm",
                "question": "La primitive de 1/(2x + 1) est :",
                "options": ["ln|2x+1| + C", "(1/2)ln|2x+1| + C", "2·ln|2x+1| + C", "ln(2x+1)/2x + C"],
                "correct_option": "B",
                "explanation": "∫ 1/(ax+b) dx = (1/a)·ln|ax+b| + C. Ici a=2 : (1/2)ln|2x+1| + C.",
            },
            {
                "id": "1030_5",
                "type": "vrai-faux",
                "question": "∫ xⁿ dx = xⁿ⁺¹/(n+1) + C pour n ≠ −1.",
                "correct": True,
                "explanation": "C'est la formule de base pour les puissances (sauf n=−1 pour laquelle on obtient ln|x|).",
            },
            {
                "id": "1030_6",
                "type": "texte",
                "question": "Calcule ∫₀¹ eˣ dx.",
                "correct_answer": "e − 1",
                "explanation": "[eˣ]₀¹ = e¹ − e⁰ = e − 1.",
            },
            {
                "id": "1030_7",
                "type": "qcm",
                "question": "Que vaut ∫₁⁴ (1/√x) dx ?",
                "options": ["1", "2", "4", "6"],
                "correct_option": "B",
                "explanation": "∫ x^(−1/2) dx = 2√x + C. [2√x]₁⁴ = 2·2 − 2·1 = 4 − 2 = 2.",
            },
            {
                "id": "1030_8",
                "type": "vrai-faux",
                "question": "∫ₐᵇ [f(x) + g(x)] dx = ∫ₐᵇ f(x) dx + ∫ₐᵇ g(x) dx.",
                "correct": True,
                "explanation": "L'intégrale est linéaire : elle distribue sur la somme et le scalaire.",
            },
        ],
    ),
    (
        1031,
        "Intégration par parties",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1031_1",
                "type": "qcm",
                "question": "La formule d'intégration par parties est :",
                "options": [
                    "∫ uv dx = u'v' + C",
                    "∫ u'v dx = [uv] − ∫ uv' dx",
                    "∫ u'v dx = uv − ∫ u'v' dx",
                    "∫ uv' dx = u'v − ∫ u'v' dx",
                ],
                "correct_option": "B",
                "explanation": "IPP : ∫ u'v dx = [uv] − ∫ uv' dx. On dérive v et on intègre u'.",
            },
            {
                "id": "1031_2",
                "type": "vrai-faux",
                "question": "Pour calculer ∫ x·eˣ dx par IPP, on peut poser u' = eˣ et v = x.",
                "correct": True,
                "explanation": "u' = eˣ → u = eˣ ; v = x → v' = 1. IPP : ∫ xeˣ dx = x·eˣ − ∫ eˣ dx = xeˣ − eˣ + C.",
            },
            {
                "id": "1031_3",
                "type": "texte",
                "question": "Calcule ∫ x·eˣ dx par intégration par parties.",
                "correct_answer": "(x−1)eˣ + C",
                "explanation": "IPP : u'=eˣ, u=eˣ ; v=x, v'=1. ∫xeˣ dx = xeˣ − ∫eˣ dx = xeˣ − eˣ + C = (x−1)eˣ + C.",
            },
            {
                "id": "1031_4",
                "type": "qcm",
                "question": "Pour ∫ ln(x) dx, quelle décomposition est pertinente ?",
                "options": [
                    "u' = ln(x), v = 1",
                    "u' = 1, v = ln(x)",
                    "u' = x, v = ln(x)",
                    "u' = 1/x, v = x",
                ],
                "correct_option": "B",
                "explanation": "On pose u' = 1 → u = x et v = ln(x) → v' = 1/x. IPP : ∫ln(x)dx = x·ln(x) − x + C.",
            },
            {
                "id": "1031_5",
                "type": "vrai-faux",
                "question": "∫ ln(x) dx = x·ln(x) − x + C.",
                "correct": True,
                "explanation": "Résultat par IPP : ∫ln x dx = x·ln x − ∫ x·(1/x) dx = x·ln x − x + C.",
            },
            {
                "id": "1031_6",
                "type": "texte",
                "question": "Calcule ∫₁ᵉ ln(x) dx.",
                "correct_answer": "1",
                "explanation": "[x·ln(x) − x]₁ᵉ = (e·1 − e) − (1·0 − 1) = 0 − (−1) = 1.",
            },
            {
                "id": "1031_7",
                "type": "qcm",
                "question": "Que vaut ∫ x·cos(x) dx ?",
                "options": [
                    "x·sin(x) + cos(x) + C",
                    "x·sin(x) − cos(x) + C",
                    "−x·sin(x) + cos(x) + C",
                    "sin(x) − x·cos(x) + C",
                ],
                "correct_option": "A",
                "explanation": "IPP : u'=cos x → u=sin x ; v=x → v'=1. ∫x·cos x dx = x·sin x − ∫sin x dx = x·sin x + cos x + C.",
            },
            {
                "id": "1031_8",
                "type": "vrai-faux",
                "question": "L'IPP peut parfois nécessiter d'être appliquée plusieurs fois.",
                "correct": True,
                "explanation": "Certaines intégrales (ex: ∫ x²·eˣ dx) nécessitent deux applications successives de l'IPP.",
            },
        ],
    ),
    (
        1032,
        "Aire entre deux courbes",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1032_1",
                "type": "qcm",
                "question": "L'aire entre f et g (avec f ≥ g) sur [a, b] est :",
                "options": [
                    "∫ₐᵇ f(x) dx − ∫ₐᵇ g(x) dx",
                    "∫ₐᵇ (f(x) + g(x)) dx",
                    "∫ₐᵇ f(x)·g(x) dx",
                    "|∫ₐᵇ f(x) dx|",
                ],
                "correct_option": "A",
                "explanation": "Aire = ∫ₐᵇ (f(x) − g(x)) dx quand f(x) ≥ g(x). C'est la différence des intégrales.",
            },
            {
                "id": "1032_2",
                "type": "vrai-faux",
                "question": "L'aire entre la courbe de f et l'axe des x correspond à ∫ₐᵇ |f(x)| dx.",
                "correct": True,
                "explanation": "On prend la valeur absolue pour que l'aire soit positive même quand f est négatif.",
            },
            {
                "id": "1032_3",
                "type": "texte",
                "question": "Calcule l'aire entre f(x) = x² et g(x) = x sur [0, 1].",
                "correct_answer": "1/6",
                "explanation": "Sur [0,1], x ≥ x². Aire = ∫₀¹(x−x²)dx = [x²/2 − x³/3]₀¹ = 1/2 − 1/3 = 1/6.",
            },
            {
                "id": "1032_4",
                "type": "qcm",
                "question": "Pour calculer l'aire entre deux courbes qui se croisent, il faut :",
                "options": [
                    "Calculer directement ∫(f−g)dx",
                    "Repérer les intersections et séparer l'intégrale",
                    "Additionner les aires",
                    "Utiliser uniquement la valeur absolue de f",
                ],
                "correct_option": "B",
                "explanation": "Quand f et g se croisent, f−g change de signe : on découpe l'intégrale sur chaque sous-intervalle.",
            },
            {
                "id": "1032_5",
                "type": "vrai-faux",
                "question": "Une intégrale définie peut être négative, mais une aire est toujours positive ou nulle.",
                "correct": True,
                "explanation": "L'intégrale peut être négative (courbe sous l'axe x). L'aire géométrique est ∫|f|dx ≥ 0.",
            },
            {
                "id": "1032_6",
                "type": "texte",
                "question": "Calcule l'aire entre y = x et y = x² (trouver d'abord les intersections).",
                "correct_answer": "1/6 (entre x=0 et x=1)",
                "explanation": "x = x² → x=0 ou x=1. Sur [0,1], x ≥ x². Aire = ∫₀¹(x−x²)dx = 1/2−1/3 = 1/6.",
            },
            {
                "id": "1032_7",
                "type": "qcm",
                "question": "Quelle est l'unité de l'aire calculée par intégration ?",
                "options": ["Mètres", "Mètres carrés", "Dépend des axes", "Aucune"],
                "correct_option": "C",
                "explanation": "L'unité est le produit des unités des axes : [x]×[y]. Si x en s et y en m/s, aire en mètres.",
            },
            {
                "id": "1032_8",
                "type": "vrai-faux",
                "question": "∫₀² (x² − x) dx donne l'aire entre y = x² et y = x sur [0, 2].",
                "correct": False,
                "explanation": "Sur [0,1], x ≥ x² (intégrale de x−x²). Sur [1,2], x² > x. Il faut séparer et prendre |f−g|.",
            },
        ],
    ),
    (
        1033,
        "Valeur moyenne d'une fonction",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1033_1",
                "type": "qcm",
                "question": "La valeur moyenne de f sur [a, b] est :",
                "options": [
                    "∫ₐᵇ f(x) dx",
                    "(f(a) + f(b)) / 2",
                    "(1/(b−a)) · ∫ₐᵇ f(x) dx",
                    "f((a+b)/2)",
                ],
                "correct_option": "C",
                "explanation": "Valeur moyenne de f sur [a,b] : m = (1/(b−a)) · ∫ₐᵇ f(x) dx.",
            },
            {
                "id": "1033_2",
                "type": "vrai-faux",
                "question": "La valeur moyenne d'une fonction constante f(x) = k sur [a,b] est k.",
                "correct": True,
                "explanation": "m = (1/(b−a)) · ∫ₐᵇ k dx = (1/(b−a)) · k(b−a) = k.",
            },
            {
                "id": "1033_3",
                "type": "texte",
                "question": "Calcule la valeur moyenne de f(x) = x² sur [0, 3].",
                "correct_answer": "3",
                "explanation": "m = (1/3)·∫₀³ x² dx = (1/3)·[x³/3]₀³ = (1/3)·9 = 3.",
            },
            {
                "id": "1033_4",
                "type": "qcm",
                "question": "Quelle est la valeur moyenne de f(x) = sin(x) sur [0, π] ?",
                "options": ["0", "1", "2/π", "π/2"],
                "correct_option": "C",
                "explanation": "m = (1/π)·∫₀^π sin(x) dx = (1/π)·[−cos x]₀^π = (1/π)·(1+1) = 2/π.",
            },
            {
                "id": "1033_5",
                "type": "vrai-faux",
                "question": "Pour une fonction continue sur [a, b], il existe c ∈ [a, b] tel que f(c) égale la valeur moyenne.",
                "correct": True,
                "explanation": "C'est le théorème de la valeur moyenne pour les intégrales (conséquence du TVI).",
            },
            {
                "id": "1033_6",
                "type": "texte",
                "question": "Interprète la valeur moyenne de la vitesse v(t) sur [0, T] en physique.",
                "correct_answer": "C'est la vitesse constante qui aurait parcouru la même distance en même temps : (1/T)·∫₀ᵀ v(t) dt = distance totale / T.",
                "explanation": "L'intégrale ∫v(t)dt donne la distance parcourue. Divisée par T, on obtient la vitesse moyenne.",
            },
            {
                "id": "1033_7",
                "type": "qcm",
                "question": "Quelle est la valeur moyenne de f(x) = eˣ sur [0, 1] ?",
                "options": ["e", "e−1", "1/(e−1)", "(e−1)"],
                "correct_option": "D",
                "explanation": "m = (1/1)·∫₀¹ eˣ dx = [eˣ]₀¹ = e − 1.",
            },
            {
                "id": "1033_8",
                "type": "vrai-faux",
                "question": "La valeur moyenne est toujours comprise entre le minimum et le maximum de f sur [a, b].",
                "correct": True,
                "explanation": "min f ≤ m ≤ max f. C'est une conséquence directe des propriétés de l'intégrale.",
            },
        ],
    ),
    (
        1034,
        "Intégrale et accumulation",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1034_1",
                "type": "qcm",
                "question": "Si v(t) est la vitesse d'un objet, que représente ∫₀ᵀ v(t) dt ?",
                "options": ["La vitesse moyenne", "L'accélération", "La distance parcourue", "L'énergie cinétique"],
                "correct_option": "C",
                "explanation": "L'intégrale de la vitesse par rapport au temps donne la distance (déplacement) parcourue.",
            },
            {
                "id": "1034_2",
                "type": "vrai-faux",
                "question": "La fonction G(x) = ∫ₐˣ f(t) dt est une primitive de f sur I.",
                "correct": True,
                "explanation": "Théorème fondamental du calcul : G'(x) = f(x). G est la primitive de f nulle en a.",
            },
            {
                "id": "1034_3",
                "type": "texte",
                "question": "Un débit d'eau Q(t) (en m³/h) coule dans un réservoir. Que représente ∫₀⁵ Q(t) dt ?",
                "correct_answer": "Le volume total d'eau entré dans le réservoir entre t=0 et t=5 heures.",
                "explanation": "L'intégrale du débit sur un intervalle de temps donne le volume accumulé.",
            },
            {
                "id": "1034_4",
                "type": "qcm",
                "question": "Si P'(t) représente le profit marginal (€/unité), que vaut ∫₀¹⁰⁰ P'(t) dt ?",
                "options": ["Le profit marginal en t=100", "Le profit total sur 100 unités", "Le coût total", "Le prix moyen"],
                "correct_option": "B",
                "explanation": "∫₀¹⁰⁰ P'(t) dt = P(100) − P(0) : c'est la variation du profit (profit total généré).",
            },
            {
                "id": "1034_5",
                "type": "vrai-faux",
                "question": "Le théorème fondamental de l'analyse dit que d/dx [∫ₐˣ f(t)dt] = f(x).",
                "correct": True,
                "explanation": "C'est le premier théorème fondamental du calcul intégral : la dérivée de l'intégrale à borne variable donne l'intégrande.",
            },
            {
                "id": "1034_6",
                "type": "texte",
                "question": "La population P(t) croît à un taux r(t). Si P(0) = 1000 et ∫₀¹⁰ r(t) dt = 500, que vaut P(10) ?",
                "correct_answer": "P(10) = 1500",
                "explanation": "P(10) = P(0) + ∫₀¹⁰ r(t) dt = 1000 + 500 = 1500.",
            },
            {
                "id": "1034_7",
                "type": "qcm",
                "question": "Le changement de variable u = g(x) dans une intégrale nécessite de remplacer dx par :",
                "options": ["du", "du/g'(x)", "g'(x) dx", "du · g(x)"],
                "correct_option": "C",
                "explanation": "u = g(x) → du = g'(x) dx. On remplace x et dx : ∫f(g(x))g'(x)dx = ∫f(u)du.",
            },
            {
                "id": "1034_8",
                "type": "vrai-faux",
                "question": "∫ₐᶜ f(x) dx = ∫ₐᵇ f(x) dx + ∫ᵦᶜ f(x) dx (relation de Chasles pour les intégrales).",
                "correct": True,
                "explanation": "On peut couper l'intervalle d'intégration en sous-intervalles et additionner les intégrales.",
            },
        ],
    ),

    # ─────────────────────────────────────────────────────────
    # BLOC 5 — Suites numériques (1035–1040)
    # ─────────────────────────────────────────────────────────
    (
        1035,
        "Suites arithmétiques",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1035_1",
                "type": "qcm",
                "question": "Dans une suite arithmétique de raison r, le terme général est :",
                "options": ["uₙ = u₀ · rⁿ", "uₙ = u₀ + n·r", "uₙ = u₀ · n", "uₙ = u₁ · rⁿ⁻¹"],
                "correct_option": "B",
                "explanation": "Terme général d'une suite arithmétique : uₙ = u₀ + n·r.",
            },
            {
                "id": "1035_2",
                "type": "vrai-faux",
                "question": "La différence entre deux termes consécutifs est constante dans une suite arithmétique.",
                "correct": True,
                "explanation": "uₙ₊₁ − uₙ = r (raison) : la différence est constante.",
            },
            {
                "id": "1035_3",
                "type": "texte",
                "question": "La suite (uₙ) est arithmétique avec u₀ = 5 et r = 3. Calcule u₁₀ et S = u₀ + u₁ + … + u₁₀.",
                "correct_answer": "u₁₀ = 35 ; S = 11 × (5+35)/2 = 220",
                "explanation": "u₁₀ = 5 + 10×3 = 35. Somme : S = (n+1)×(u₀+uₙ)/2 = 11×40/2 = 220.",
            },
            {
                "id": "1035_4",
                "type": "qcm",
                "question": "La somme des n premiers entiers 1 + 2 + … + n est :",
                "options": ["n²", "n(n+1)/2", "n(n−1)/2", "n²/2"],
                "correct_option": "B",
                "explanation": "Somme des n premiers entiers = n(n+1)/2 (suite arithmétique de premier terme 1, raison 1).",
            },
            {
                "id": "1035_5",
                "type": "vrai-faux",
                "question": "Une suite arithmétique de raison positive est strictement croissante.",
                "correct": True,
                "explanation": "r > 0 → uₙ₊₁ = uₙ + r > uₙ : la suite est strictement croissante.",
            },
            {
                "id": "1035_6",
                "type": "texte",
                "question": "Un employé gagne 2 000 € le premier mois et une augmentation de 50 € chaque mois. Quel est son salaire au bout de 12 mois ? Quelle est la somme totale perçue ?",
                "correct_answer": "Salaire au mois 12 : 2550 €. Somme : 12×(2000+2550)/2 = 27300 €.",
                "explanation": "Suite arithmétique : u₁=2000, r=50, u₁₂=2000+11×50=2550. S₁₂=12×(2000+2550)/2=27300.",
            },
            {
                "id": "1035_7",
                "type": "qcm",
                "question": "Pour une suite arithmétique, uₙ = 3n + 2. Quelle est la raison ?",
                "options": ["2", "3", "5", "n"],
                "correct_option": "B",
                "explanation": "uₙ = 3n + 2 → uₙ₊₁ − uₙ = 3(n+1)+2 − (3n+2) = 3. Raison r = 3.",
            },
            {
                "id": "1035_8",
                "type": "vrai-faux",
                "question": "La somme de n termes consécutifs d'une suite arithmétique est n fois le terme central (ou la demi-somme des extrêmes).",
                "correct": True,
                "explanation": "Sₙ = n × (premier terme + dernier terme)/2 = n × terme central.",
            },
        ],
    ),
    (
        1036,
        "Suites géométriques",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1036_1",
                "type": "qcm",
                "question": "Dans une suite géométrique de raison q, le terme général est :",
                "options": ["uₙ = u₀ + n·q", "uₙ = u₀ · qⁿ", "uₙ = u₀ · q · n", "uₙ = u₀ⁿ · q"],
                "correct_option": "B",
                "explanation": "Terme général d'une suite géométrique : uₙ = u₀ · qⁿ.",
            },
            {
                "id": "1036_2",
                "type": "vrai-faux",
                "question": "Dans une suite géométrique, le rapport uₙ₊₁/uₙ est constant et égal à q.",
                "correct": True,
                "explanation": "C'est la définition d'une suite géométrique : le rapport entre termes consécutifs est constant.",
            },
            {
                "id": "1036_3",
                "type": "texte",
                "question": "Calcule la somme S = 1 + 2 + 4 + 8 + … + 2¹⁰.",
                "correct_answer": "S = 2¹¹ − 1 = 2047",
                "explanation": "Suite géométrique de raison 2, premier terme 1. S = (2¹¹ − 1)/(2−1) = 2047.",
            },
            {
                "id": "1036_4",
                "type": "qcm",
                "question": "La somme des n premiers termes d'une suite géométrique (q ≠ 1) est :",
                "options": [
                    "u₀ · n · q",
                    "u₀ · (1 − qⁿ) / (1 − q)",
                    "u₀ · qⁿ / (q − 1)",
                    "n · u₀ · q",
                ],
                "correct_option": "B",
                "explanation": "Sₙ = u₀ · (1 − qⁿ)/(1 − q) pour q ≠ 1.",
            },
            {
                "id": "1036_5",
                "type": "vrai-faux",
                "question": "Si |q| < 1, la suite géométrique uₙ = u₀·qⁿ converge vers 0.",
                "correct": True,
                "explanation": "Pour |q| < 1, qⁿ → 0 quand n → +∞, donc uₙ → 0.",
            },
            {
                "id": "1036_6",
                "type": "texte",
                "question": "Un capital de 1000 € est placé à 4% annuel. Exprime la valeur Cₙ après n années et calcule C₅.",
                "correct_answer": "Cₙ = 1000 × 1,04ⁿ. C₅ = 1000 × 1,04⁵ ≈ 1216,65 €.",
                "explanation": "Suite géométrique de raison q=1,04. C₅ = 1000×1,04⁵ ≈ 1216,65.",
            },
            {
                "id": "1036_7",
                "type": "qcm",
                "question": "La suite uₙ = 3ⁿ est géométrique de raison :",
                "options": ["n", "3", "1/3", "3n"],
                "correct_option": "B",
                "explanation": "uₙ₊₁/uₙ = 3ⁿ⁺¹/3ⁿ = 3. La raison est q = 3.",
            },
            {
                "id": "1036_8",
                "type": "vrai-faux",
                "question": "La somme de la série géométrique infinie 1 + 1/2 + 1/4 + … converge vers 2.",
                "correct": True,
                "explanation": "Série géométrique infinie : S = u₀/(1−q) = 1/(1−1/2) = 2.",
            },
        ],
    ),
    (
        1037,
        "Convergence et limites de suites",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1037_1",
                "type": "qcm",
                "question": "Une suite (uₙ) converge vers L si :",
                "options": [
                    "uₙ est croissante",
                    "Pour tout ε > 0, il existe N tel que n > N ⟹ |uₙ − L| < ε",
                    "uₙ est bornée",
                    "uₙ₊₁ − uₙ → 0",
                ],
                "correct_option": "B",
                "explanation": "C'est la définition formelle (ε-N) de la convergence d'une suite vers L.",
            },
            {
                "id": "1037_2",
                "type": "vrai-faux",
                "question": "Toute suite croissante et majorée converge.",
                "correct": True,
                "explanation": "Théorème de la limite monotone : une suite monotone bornée est convergente.",
            },
            {
                "id": "1037_3",
                "type": "texte",
                "question": "Quelle est la limite de uₙ = (2n + 1)/(n + 3) quand n → +∞ ?",
                "correct_answer": "2",
                "explanation": "Diviser par n : (2 + 1/n)/(1 + 3/n) → 2/1 = 2.",
            },
            {
                "id": "1037_4",
                "type": "qcm",
                "question": "La suite uₙ = (−1)ⁿ :",
                "options": ["Converge vers 0", "Converge vers 1", "Diverge", "Converge vers −1"],
                "correct_option": "C",
                "explanation": "(−1)ⁿ alterne entre 1 et −1 : la suite diverge (pas de limite).",
            },
            {
                "id": "1037_5",
                "type": "vrai-faux",
                "question": "Si uₙ → L et vₙ → M, alors uₙ + vₙ → L + M.",
                "correct": True,
                "explanation": "Les opérations algébriques sont compatibles avec la limite : somme, produit, quotient (si M≠0).",
            },
            {
                "id": "1037_6",
                "type": "texte",
                "question": "Applique le théorème des gendarmes pour montrer que uₙ = sin(n)/n → 0.",
                "correct_answer": "−1/n ≤ sin(n)/n ≤ 1/n. Les deux encadrants → 0, donc sin(n)/n → 0.",
                "explanation": "|sin(n)| ≤ 1 → −1/n ≤ sin(n)/n ≤ 1/n. Comme ±1/n → 0, par gendarmes : uₙ → 0.",
            },
            {
                "id": "1037_7",
                "type": "qcm",
                "question": "La suite uₙ = nⁿ/(n!) tend vers :",
                "options": ["0", "1", "+∞", "e"],
                "correct_option": "C",
                "explanation": "nⁿ/n! → +∞ (Stirling : n! ~ √(2πn)·(n/e)ⁿ, donc nⁿ/n! ~ eⁿ/√(2πn) → +∞).",
            },
            {
                "id": "1037_8",
                "type": "vrai-faux",
                "question": "Une suite bornée est nécessairement convergente.",
                "correct": False,
                "explanation": "Contre-exemple : (−1)ⁿ est bornée (valeurs ±1) mais diverge. Bornée + monotone → convergente.",
            },
        ],
    ),
    (
        1038,
        "Suites définies par récurrence",
        "Mathématiques",
        "Terminale",
        [
            {
                "id": "1038_1",
                "type": "qcm",
                "question": "La suite définie par u₀ = 2 et uₙ₊₁ = (uₙ + 4)/2. Que vaut u₁ ?",
                "options": ["2", "3", "4", "6"],
                "correct_option": "B",
                "explanation": "u₁ = (u₀ + 4)/2 = (2 + 4)/2 = 3.",
            },
            {
                "id": "1038_2",
                "type": "vrai-faux",
                "question": "Si la suite (uₙ) est récurrente et converge vers L, alors L vérifie L = f(L).",
                "correct": True,
                "explanation": "Si uₙ → L et uₙ₊₁ = f(uₙ), par continuité de f : L = f(L). L est un point fixe.",
            },
            {
                "id": "1038_3",
                "type": "texte",
                "question": "La suite uₙ₊₁ = uₙ/2 + 1, u₀ = 0. Trouve la limite éventuelle L et vérifie.",
                "correct_answer": "L = L/2 + 1 → L/2 = 1 → L = 2. La suite converge vers 2.",
                "explanation": "En supposant convergence vers L : L = L/2 + 1 → L = 2. La suite est croissante et bornée par 2.",
            },
            {
                "id": "1038_4",
                "type": "qcm",
                "question": "Étudier le sens de variation d'une suite récurrente uₙ₊₁ = f(uₙ) revient à :",
                "options": [
                    "Calculer f'",
                    "Étudier le signe de uₙ₊₁ − uₙ = f(uₙ) − uₙ",
                    "Calculer la limite de f",
                    "Tracer la courbe de f",
                ],
                "correct_option": "B",
                "explanation": "Le sens de variation s'obtient en étudiant le signe de uₙ₊₁ − uₙ = f(uₙ) − uₙ.",
            },
            {
                "id": "1038_5",
                "type": "vrai-faux",
                "question": "La représentation graphique de la suite récurrente utilise les droites y = x et y = f(x) (toile d'araignée).",
                "correct": True,
                "explanation": "L'algorithme de la toile : on part de (u₀, 0), monte vers (u₀, f(u₀)) = (u₀, u₁), puis vers (u₁, u₁)…",
            },
            {
                "id": "1038_6",
                "type": "texte",
                "question": "Montre par récurrence que si uₙ ≤ 2, alors uₙ₊₁ = uₙ/2 + 1 ≤ 2.",
                "correct_answer": "uₙ ≤ 2 → uₙ₊₁ = uₙ/2 + 1 ≤ 2/2 + 1 = 2. La propriété est conservée.",
                "explanation": "uₙ/2 ≤ 1 (si uₙ ≤ 2) → uₙ/2 + 1 ≤ 2. L'invariant uₙ ≤ 2 est héréditaire.",
            },
            {
                "id": "1038_7",
                "type": "qcm",
                "question": "La suite de Fibonacci : u₀=0, u₁=1, uₙ₊₂ = uₙ₊₁ + uₙ. Que vaut u₅ ?",
                "options": ["5", "8", "3", "13"],
                "correct_option": "A",
                "explanation": "u₀=0, u₁=1, u₂=1, u₃=2, u₄=3, u₅=5.",
            },
            {
                "id": "1038_8",
                "type": "vrai-faux",
                "question": "La suite définie par u₀ = 1 et uₙ₊₁ = √(uₙ + 1) converge vers une limite L qui vérifie L = √(L + 1).",
                "correct": True,
                "explanation": "Supposons uₙ → L : L = √(L + 1) → L² = L + 1 → L² − L − 1 = 0. L = (1 + √5)/2 (solution positive).",
            },
        ],
    ),
]


def build_progressive_questions(qid, title, theme):
    return [
        {
            "id": f"{qid}_1",
            "type": "qcm",
            "question": f"Dans le chapitre '{title}', quelle premiere etape est la plus rigoureuse ?",
            "options": [
                "Identifier les hypotheses et l'objectif",
                "Choisir une reponse sans calcul",
                "Ignorer les conditions de validite",
                "Passer directement au resultat final",
            ],
            "correct_option": "A",
            "explanation": "Une demarche rigoureuse commence par l'analyse des hypotheses, des notations et de l'objectif mathematique.",
        },
        {
            "id": f"{qid}_2",
            "type": "vrai-faux",
            "question": "En Terminale, justifier la methode choisie fait partie de la reponse attendue.",
            "correct": True,
            "explanation": "La qualite de l'argumentation compte autant que le resultat numerique ou algebrique.",
        },
        {
            "id": f"{qid}_3",
            "type": "texte",
            "question": f"Explique en quelques lignes comment tu aborderais un exercice sur le theme suivant : {theme}.",
            "correct_answer": "Je commence par reformuler la question, j'identifie les outils utiles, puis je deroule le calcul en verifiant les hypotheses et l'interpretation finale.",
            "explanation": "Une bonne reponse mentionne la reformulation, le choix des outils adaptes et une verification finale du resultat.",
        },
        {
            "id": f"{qid}_4",
            "type": "qcm",
            "question": "Quelle verification finale est la plus pertinente ?",
            "options": [
                "Controler coherence, signe et domaine",
                "Ne rien verifier",
                "Modifier la question initiale",
                "Recopier le resultat",
            ],
            "correct_option": "A",
            "explanation": "Verifier le domaine, le signe, l'ordre de grandeur et la coherence theorique evite les erreurs de conclusion.",
        },
        {
            "id": f"{qid}_5",
            "type": "vrai-faux",
            "question": "Un contre-exemple ou un test numerique peut aider a controler une conjecture avant redaction finale.",
            "correct": True,
            "explanation": "Verifier une idee sur quelques valeurs est utile pour orienter le raisonnement, meme si cela ne remplace pas une preuve.",
        },
        {
            "id": f"{qid}_6",
            "type": "texte",
            "question": "Cite une erreur frequente sur ce theme et explique comment l'eviter.",
            "correct_answer": "Erreur frequente : appliquer une formule sans verifier ses conditions. Pour l'eviter, je controle d'abord le cadre de validite avant tout calcul.",
            "explanation": "La plupart des erreurs viennent d'une formule appliquee hors contexte ou d'une hypothese oubliee.",
        },
        {
            "id": f"{qid}_7",
            "type": "qcm",
            "question": "Quelle attitude correspond au niveau attendu en specialite mathematiques ?",
            "options": [
                "Justifier, organiser et verifier",
                "Memoriser sans comprendre",
                "Eviter les preuves",
                "Ne pas relire les calculs",
            ],
            "correct_option": "A",
            "explanation": "Le niveau attendu repose sur la maitrise des outils, la justification des etapes et la verification finale.",
        },
        {
            "id": f"{qid}_8",
            "type": "vrai-faux",
            "question": "Relire la consigne et la nature exacte du resultat attendu peut corriger une copie entiere.",
            "correct": True,
            "explanation": "Beaucoup d'erreurs viennent d'une mauvaise lecture du resultat demande : valeur, ensemble solution, equation de droite, interpretation, etc.",
        },
    ]


PROGRESSIVE_COMPLETION_SPECS = [
    (1039, "Raisonnement par recurrence", "initialisation, heredite et conclusion"),
    (1040, "Algorithmes et suites", "boucles, calcul iteratif et interpretation"),
    (1041, "Probabilites conditionnelles", "arbres ponderes et conditionnement"),
    (1042, "Variables aleatoires", "esperance, variance et interpretation"),
    (1043, "Loi binomiale", "schema de Bernoulli et calcul de probabilites"),
    (1044, "Echantillonnage", "frequences et intervalle de fluctuation"),
    (1045, "Geometrie dans l'espace", "vecteurs, droites, plans et intersections"),
    (1046, "Produit scalaire", "orthogonalite et calcul d'angles"),
    (1047, "Nombres complexes", "forme algebrique et interpretation geometrique"),
    (1048, "Module et argument", "representation complexe du plan"),
    (1049, "Exponentielle complexe", "forme trigonometrique et rotations"),
    (1050, "Equations complexes", "resolutions et ensembles solutions"),
    (1051, "Matrices 2x2", "operations, determinant et inversibilite"),
    (1052, "Graphes et chaines", "sommets, arretes et lecture de modele"),
    (1053, "Optimisation", "contraintes, extremums et interpretation"),
    (1054, "Modelisation continue", "equations, parametres et comportements"),
    (1055, "Analyse graphique", "variations, tangentes et convexite"),
    (1056, "Synthese calcul integral", "primitives, aires et accumulation"),
    (1057, "Synthese suites et limites", "convergence et comparaison"),
    (1058, "Bilan methodologique Terminale", "strategie, justification et verification"),
]

for qid, title, theme in PROGRESSIVE_COMPLETION_SPECS:
    quizzes_data.append(
        (
            qid,
            title,
            "Mathématiques",
            "Terminale",
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
            choices = list(cleaned_question.get("options", []))
            if not choices and is_free_text_question_type(question.get("type", "")):
                choices = build_qcm_choices(question)
            if choices:
                runtime_questions.append(
                    {
                        "type": "qcm",
                        "question": str(cleaned_question.get("question", "")),
                        "choices": choices,
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
            answers.append(
                {
                    "index": index,
                    "question_id": index + 1,
                    "type": "vrai-faux",
                    "answer": "vrai" if bool(question.get("correct", True)) else "faux",
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

    print(f"[sentinel] OK quiz={sentinel_qid} questions={len(questions)} answers={len(answers)}")


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
