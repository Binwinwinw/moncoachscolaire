#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Mathématiques 6e — SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "mathematiques_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

quizzes_data = [
  [
    "49",
    "Nombres et opérations",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "49_1",
        "type": "qcm",
        "question": "Quelle est la somme de 2 + 2 ?",
        "options": [
          "3",
          "4",
          "5",
          "6"
        ],
        "correct_option": "4",
        "explanation": "2 + 2 = 4."
      },
      {
        "id": "49_2",
        "type": "vrai-faux",
        "question": "Le nombre 0 est un nombre entier.",
        "correct": True,
        "explanation": "0 est un nombre entier."
      },
      {
        "id": "49_3",
        "type": "qcm",
        "question": "Combien de côtés a un triangle ?",
        "options": [
          "2",
          "3",
          "4",
          "5"
        ],
        "correct_option": "3",
        "explanation": "Un triangle a 3 côtés."
      },
      {
        "id": "49_4",
        "type": "vrai-faux",
        "question": "Le nombre 1 est un nombre premier.",
        "correct": False,
        "explanation": "1 n'est pas un nombre premier."
      },
      {
        "id": "49_5",
        "type": "qcm",
        "question": "Combien de minutes y a-t-il dans une heure ?",
        "options": [
          "50",
          "55",
          "60",
          "65"
        ],
        "correct_option": "60",
        "explanation": "Il y a 60 minutes dans une heure."
      },
      {
        "id": "49_6",
        "type": "qcm",
        "question": "Combien de secondes y a-t-il dans une minute ?",
        "options": [
          "50",
          "55",
          "60",
          "65"
        ],
        "correct_option": "60",
        "explanation": "Il y a 60 secondes dans une minute."
      },
      {
        "id": "49_7",
        "type": "qcm",
        "question": "Quel est le résultat de 10 - 4 ?",
        "options": [
          "5",
          "6",
          "7",
          "8"
        ],
        "correct_option": "6",
        "explanation": "10 - 4 = 6."
      },
      {
        "id": "49_8",
        "type": "vrai-faux",
        "question": "Le nombre 2 est un nombre pair.",
        "correct": True,
        "explanation": "2 est un nombre pair."
      }
    ]
  ],
  [
    "50",
    "grandeurs et mesures, fonctions et organisation de données",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "50_1",
        "type": "qcm",
        "question": "Quelle est l'unité de mesure de la longueur ?",
        "options": [
          "mètre",
          "kilogramme",
          "seconde",
          "litre"
        ],
        "correct_option": "mètre",
        "explanation": "L'unité de mesure de la longueur est le mètre."
      },
      {
        "id": "50_2",
        "type": "vrai-faux",
        "question": "Un kilogramme est une unité de mesure de la masse.",
        "correct": True,
        "explanation": "Un kilogramme est une unité de mesure de la masse."
      },
      {
        "id": "50_3",
        "type": "qcm",
        "question": "Combien de secondes y a-t-il dans une minute ?",
        "options": [
          "50",
          "55",
          "60",
          "65"
        ],
        "correct_option": "60",
        "explanation": "Il y a 60 secondes dans une minute."
      },
      {
        "id": "50_4",
        "type": "qcm",
        "question": "Quelle est l'unité de mesure de la capacité ?",
        "options": [
          "mètre",
          "kilogramme",
          "seconde",
          "litre"
        ],
        "correct_option": "litre",
        "explanation": "L'unité de mesure de la capacité est le litre."
      },
      {
        "id": "50_5",
        "type": "vrai-faux",
        "question": "Un mètre est une unité de mesure de la masse.",
        "correct": False,
        "explanation": "Un mètre est une unité de mesure de la longueur, pas de la masse."
      },
      {
        "id": "50_6",
        "type": "qcm",
        "question": "Combien de minutes y a-t-il dans une heure ?",
        "options": [
          "50",
          "55",
          "60",
          "65"
        ],
        "correct_option": "60",
        "explanation": "Il y a 60 minutes dans une heure."
      },
      {
        "id": "50_7",
        "type": "vrai-faux",
        "question": "Un litre est une unité de mesure de la capacité.",
        "correct": True,
        "explanation": "Un litre est une unité de mesure de la capacité."
      },
      {
        "id": "50_8",
        "type": "qcm",
        "question": "Quelle est l'unité de mesure du temps ?",
        "options": [
          "mètre",
          "kilogramme",
          "seconde",
          "litre"
        ],
        "correct_option": "seconde",
        "explanation": "L'unité de mesure du temps est la seconde."
      }
    ]
  ],
  [
    "51",
    "algèbre et organisation de données",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "51_1",
        "type": "qcm",
        "question": "Quelle est la valeur de x dans l'équation 2x + 3 = 7 ?",
        "options": [
          "1",
          "2",
          "3",
          "4"
        ],
        "correct_option": "2",
        "explanation": "2x + 3 = 7 → 2x = 4 → x = 2."
      },
      {
        "id": "51_2",
        "type": "vrai-faux",
        "question": "Dans l'équation y = 3x + 2, y est la variable dépendante.",
        "correct": True,
        "explanation": "Dans l'équation y = 3x + 2, y est la variable dépendante car sa valeur dépend de celle de x."
      },
      {
        "id": "51_3",
        "type": "qcm",
        "question": "Quelle est la valeur de y dans l'équation y = 2x - 1 lorsque x = 3 ?",
        "options": [
          "4",
          "5",
          "6",
          "7"
        ],
        "correct_option": "5",
        "explanation": "y = 2x - 1 → y = 2(3) - 1 → y = 6 - 1 → y = 5."
      },
      {
        "id": "51_4",
        "type": "vrai-faux",
        "question": "Dans l'équation z = x + y, z est la variable indépendante.",
        "correct": False,
        "explanation": "Dans l'équation z = x + y, z est la variable dépendante car sa valeur dépend de celles de x et y."
      },
      {
        "id": "51_5",
        "type": "qcm",
        "question": "Quelle est la valeur de x dans l'équation 3x - 2 = 4 ?",
        "options": [
          "1",
          "2",
          "3",
          "4"
        ],
        "correct_option": "2",
        "explanation": "3x - 2 = 4 → 3x = 6 → x = 2."
      },
      {
        "id": "51_6",
        "type": "vrai-faux",
        "question": "Dans l'équation w = 5y, w est la variable dépendante.",
        "correct": True,
        "explanation": "Dans l'équation w = 5y, w est la variable dépendante car sa valeur dépend de celle de y."
      },
      {
        "id": "51_7",
        "type": "qcm",
        "question": "Quelle est la valeur de y dans l'équation y = 4x + 1 lorsque x = 2 ?",
        "options": [
          "7",
          "8",
          "9",
          "10"
        ],
        "correct_option": "9",
        "explanation": "y = 4x + 1 → y = 4(2) + 1 → y = 8 + 1 → y = 9."
      },
      {
        "id": "51_8",
        "type": "vrai-faux",
        "question": "Dans l'équation v = 2z + 3, v est la variable indépendante.",
        "correct": False,
        "explanation": "Dans l'équation v = 2z + 3, v est la variable dépendante car sa valeur dépend de celle de z."
      }
    ]
  ],
  [
    "52",
    "géometrie",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "52_1",
        "type": "qcm",
        "question": "Combien de côtés a un carré ?",
        "options": [
          "2",
          "3",
          "4",
          "5"
        ],
        "correct_option": "4",
        "explanation": "Un carré a 4 côtés."
      },
      {
        "id": "52_2",
        "type": "vrai-faux",
        "question": "Un triangle a 4 côtés.",
        "correct": False,
        "explanation": "Un triangle a 3 côtés."
      },
      {
        "id": "52_3",
        "type": "qcm",
        "question": "Combien de faces a un cube ?",
        "options": [
          "4",
          "6",
          "8",
          "10"
        ],
        "correct_option": "6",
        "explanation": "Un cube a 6 faces."
      },
      {
        "id": "52_4",
        "type": "vrai-faux",
        "question": "Un cercle a 1 côté.",
        "correct": False,
        "explanation": "Un cercle n'a pas de côté, c'est une courbe fermée."
      },
      {
        "id": "52_5",
        "type": "qcm",
        "question": "Combien de sommets a un tétraèdre ?",
        "options": [
          "3",
          "4",
          "5",
          "6"
        ],
        "correct_option": "4",
        "explanation": "Un tétraèdre a 4 sommets."
      },
      {
        "id": "52_6",
        "type": "vrai-faux",
        "question": "Un rectangle a 4 côtés.",
        "correct": True,
        "explanation": "Un rectangle a 4 côtés."
      },
      {
        "id": "52_7",
        "type": "qcm",
        "question": "Combien de côtés a un pentagone ?",
        "options": [
          "3",
          "4",
          "5",
          "6"
        ],
        "correct_option": "5",
        "explanation": "Un pentagone a 5 côtés."
      },
      {
        "id": "52_8",
        "type": "vrai-faux",
        "question": "Un hexagone a 6 côtés.",
        "correct": True,
        "explanation": "Un hexagone a 6 côtés."
      }
    ]
  ],
  [
    "53",
    "Espace et géométrie",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "53_1",
        "type": "qcm",
        "question": "Combien de faces a un cube ?",
        "options": [
          "4",
          "6",
          "8",
          "10"
        ],
        "correct_option": "6",
        "explanation": "Un cube a 6 faces."
      },
      {
        "id": "53_2",
        "type": "vrai-faux",
        "question": "Un tétraèdre a 5 faces.",
        "correct": False,
        "explanation": "Un tétraèdre a 4 faces."
      },
      {
        "id": "53_3",
        "type": "qcm",
        "question": "Combien de sommets a un cube ?",
        "options": [
          "6",
          "8",
          "10",
          "12"
        ],
        "correct_option": "8",
        "explanation": "Un cube a 8 sommets."
      },
      {
        "id": "53_4",
        "type": "vrai-faux",
        "question": "Un cylindre a 2 faces.",
        "correct": False,
        "explanation": "Un cylindre a 3 faces (2 cercles + 1 surface latérale)."
      },
      {
        "id": "53_5",
        "type": "qcm",
        "question": "Combien de faces a un prisme à base triangulaire ?",
        "options": [
          "3",
          "4",
          "5",
          "6"
        ],
        "correct_option": "5",
        "explanation": "Un prisme à base triangulaire a 5 faces (2 triangles + 3 rectangles)."
      },
      {
        "id": "53_6",
        "type": "vrai-faux",
        "question": "Un cône a 2 faces.",
        "correct": False,
        "explanation": "Un cône a 2 faces (1 cercle + 1 surface latérale)."
      },
      {
        "id": "53_7",
        "type": "qcm",
        "question": "Combien de sommets a un prisme à base carrée ?",
        "options": [
          "6",
          "8",
          "10",
          "12"
        ],
        "correct_option": "8",
        "explanation": "Un prisme à base carrée a 8 sommets."
      },
      {
        "id": "53_8",
        "type": "vrai-faux",
        "question": "Un sphère a 0 face.",
        "correct": True,
        "explanation": "Une sphère n'a pas de face, c'est une surface courbe fermée."
      }
    ]
  ],
  [
    "54",
    "Organisation et gestion de données",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "54_1",
        "type": "qcm",
        "question": "Quel est le rôle principal d'un tableau de données ?",
        "options": [
          "Stocker des données",
          "Analyser des données",
          "Afficher des données",
          "Toutes les réponses"
        ],
        "correct_option": "Toutes les réponses",
        "explanation": "Un tableau de données permet de stocker, analyser et afficher des données."
      },
      {
        "id": "54_2",
        "type": "vrai-faux",
        "question": "Un graphique en barres est utilisé pour représenter des données catégorielles.",
        "correct": True,
        "explanation": "Un graphique en barres est utilisé pour représenter des données catégorielles."
      },
      {
        "id": "54_3",
        "type": "qcm",
        "question": "Quel type de graphique est le mieux adapté pour montrer la répartition d'un ensemble de données ?",
        "options": [
          "Graphique en barres",
          "Graphique circulaire",
          "Histogramme",
          "Graphique linéaire"
        ],
        "correct_option": "Graphique circulaire",
        "explanation": "Un graphique circulaire est le mieux adapté pour montrer la répartition d'un ensemble de données."
      },
      {
        "id": "54_4",
        "type": "vrai-faux",
        "question": "Un histogramme est utilisé pour représenter des données continues.",
        "correct": True,
        "explanation": "Un histogramme est utilisé pour représenter des données continues."
      },
      {
        "id": "54_5",
        "type": "qcm",
        "question": "Quel est le rôle d'une légende dans un graphique ?",
        "options": [
          "Expliquer les axes",
          "Identifier les séries de données",
          "Afficher les titres",
          "Aucune des réponses"
        ],
        "correct_option": "Identifier les séries de données",
        "explanation": "Une légende dans un graphique sert à identifier les différentes séries de données représentées."
      },
      {
        "id": "54_6",
        "type": "vrai-faux",
        "question": "Un graphique linéaire est utilisé pour représenter des données temporelles.",
        "correct": True,
        "explanation": "Un graphique linéaire est utilisé pour représenter des données temporelles."
      },
      {
        "id": "54_7",
        "type": "qcm",
        "question": "Quel type de graphique est le mieux adapté pour montrer l'évolution d'une variable dans le temps ?",
        "options": [
          "Graphique en barres",
          "Graphique circulaire",
          "Histogramme",
          "Graphique linéaire"
        ],
        "correct_option": "Graphique linéaire",
        "explanation": "Un graphique linéaire est le mieux adapté pour montrer l'évolution d'une variable dans le temps."
      },
      {
        "id": "54_8",
        "type": "vrai-faux",
        "question": "Un graphique en barres peut être utilisé pour représenter des données continues.",
        "correct": False,
        "explanation": "Un graphique en barres est généralement utilisé pour représenter des données catégorielles, pas continues."
      }
    ]
  ],
  [
    "55",
    "Comparaison et ordre des entiers",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "55_1",
        "type": "qcm",
        "question": "Quel est le plus grand nombre entier parmi les suivants ?",
        "options": [
          "-1",
          "0",
          "1",
          "2"
        ],
        "correct_option": "2",
        "explanation": "2 est le plus grand nombre entier parmi les options données."
      },
      {
        "id": "55_2",
        "type": "vrai-faux",
        "question": "Le nombre -3 est plus petit que le nombre 0.",
        "correct": True,
        "explanation": "-3 est plus petit que 0."
      },
      {
        "id": "55_3",
        "type": "qcm",
        "question": "Quel est le plus petit nombre entier parmi les suivants ?",
        "options": [
          "-2",
          "-1",
          "0",
          "1"
        ],
        "correct_option": "-2",
        "explanation": "-2 est le plus petit nombre entier parmi les options données."
      },
      {
        "id": "55_4",
        "type": "vrai-faux",
        "question": "Le nombre 5 est plus grand que le nombre 3.",
        "correct": True,
        "explanation": "5 est plus grand que 3."
      },
      {
        "id": "55_5",
        "type": "qcm",
        "question": "Quel est le nombre entier qui se trouve entre 2 et 4 ?",
        "options": [
          "1",
          "2",
          "3",
          "4"
        ],
        "correct_option": "3",
        "explanation": "3 est le nombre entier qui se trouve entre 2 et 4."
      },
      {
        "id": "55_6",
        "type": "vrai-faux",
        "question": "Le nombre -1 est plus grand que le nombre -2.",
        "correct": True,
        "explanation": "-1 est plus grand que -2."
      },
      {
        "id": "55_7",
        "type": "qcm",
        "question": "Quel est le nombre entier qui se trouve entre -3 et -1 ?",
        "options": [
          "-4",
          "-3",
          "-2",
          "-1"
        ],
        "correct_option": "-2",
        "explanation": "-2 est le nombre entier qui se trouve entre -3 et -1."
      },
      {
        "id": "55_8",
        "type": "vrai-faux",
        "question": "Le nombre 0 est plus petit que le nombre 1.",
        "correct": True,
        "explanation": "0 est plus petit que 1."
      }
    ]
  ],
  [
    "56",
    "Comparaison et ordre des décimaux",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "56_1",
        "type": "qcm",
        "question": "Quel est le plus grand nombre décimal parmi les suivants ?",
        "options": [
          "0.1",
          "0.01",
          "0.001",
          "0.0001"
        ],
        "correct_option": "0.1",
        "explanation": "0.1 est le plus grand nombre décimal parmi les options données."
      },
      {
        "id": "56_2",
        "type": "vrai-faux",
        "question": "Le nombre 0.5 est plus petit que le nombre 0.6.",
        "correct": True,
        "explanation": "0.5 est plus petit que 0.6."
      },
      {
        "id": "56_3",
        "type": "qcm",
        "question": "Quel est le plus petit nombre décimal parmi les suivants ?",
        "options": [
          "0.1",
          "0.01",
          "0.001",
          "0.0001"
        ],
        "correct_option": "0.0001",
        "explanation": "0.0001 est le plus petit nombre décimal parmi les options données."
      },
      {
        "id": "56_4",
        "type": "qcm",
        "question": "Quel nombre décimal est plus grand : 0.2 ou 0.19 ?",
        "options": [
          "0.2",
          "0.19",
          "0.02",
          "0.29"
        ],
        "correct_option": "0.2",
        "explanation": "0.2 est plus grand que 0.19."
      },
      {
        "id": "56_5",
        "type": "vrai-faux",
        "question": "0.75 est plus grand que 0.705.",
        "correct": True,
        "explanation": "0.75 est plus grand que 0.705."
      },
      {
        "id": "56_6",
        "type": "qcm",
        "question": "Lequel de ces nombres décimaux est le plus proche de 1 ?",
        "options": [
          "0.9",
          "0.99",
          "0.5",
          "0.1"
        ],
        "correct_option": "0.99",
        "explanation": "0.99 est le nombre le plus proche de 1 parmi les options proposées."
      },
      {
        "id": "56_7",
        "type": "vrai-faux",
        "question": "0.05 est plus grand que 0.005.",
        "correct": True,
        "explanation": "0.05 est plus grand que 0.005."
      },
      {
        "id": "56_8",
        "type": "qcm",
        "question": "Quel nombre décimal est le plus petit ?",
        "options": [
          "0.25",
          "0.205",
          "0.52",
          "0.502"
        ],
        "correct_option": "0.205",
        "explanation": "0.205 est le plus petit nombre décimal parmi les options proposées."
      }
    ]
  ],
  [
    "57",
    "Comparaison et ordre des fractions",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "57_1",
        "type": "qcm",
        "question": "Quel est le plus grand nombre fractionnaire parmi les suivants ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/5"
        ],
        "correct_option": "1/2",
        "explanation": "1/2 est le plus grand nombre fractionnaire parmi les options données."
      },
      {
        "id": "57_2",
        "type": "vrai-faux",
        "question": "Le nombre 1/4 est plus petit que le nombre 1/3.",
        "correct": True,
        "explanation": "1/4 est plus petit que 1/3."
      },
      {
        "id": "57_3",
        "type": "qcm",
        "question": "Quel est le plus petit nombre fractionnaire parmi les suivants ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/5"
        ],
        "correct_option": "1/5",
        "explanation": "1/5 est le plus petit nombre fractionnaire parmi les options données."
      },
      {
        "id": "57_4",
        "type": "vrai-faux",
        "question": "Le nombre 1/3 est plus grand que le nombre 1/4.",
        "correct": True,
        "explanation": "1/3 est plus grand que 1/4."
      },
      {
        "id": "57_5",
        "type": "qcm",
        "question": "Quel est le nombre fractionnaire qui se trouve entre 1/4 et 1/2 ?",
        "options": [
          "1/3",
          "1/5",
          "1/6",
          "1/7"
        ],
        "correct_option": "1/3",
        "explanation": "1/3 est le nombre fractionnaire qui se trouve entre 1/4 et 1/2."
      },
      {
        "id": "57_6",
        "type": "vrai-faux",
        "question": "Le nombre 1/5 est plus petit que le nombre 1/4.",
        "correct": True,
        "explanation": "1/5 est plus petit que 1/4."
      },
      {
        "id": "57_7",
        "type": "qcm",
        "question": "Quel est le nombre fractionnaire qui se trouve entre 1/3 et 1/2 ?",
        "options": [
          "1/4",
          "1/5",
          "1/6",
          "1/7"
        ],
        "correct_option": "1/4",
        "explanation": "1/4 est le nombre fractionnaire qui se trouve entre 1/3 et 1/2."
      },
      {
        "id": "57_8",
        "type": "vrai-faux",
        "question": "Le nombre 1/6 est plus petit que le nombre 1/5.",
        "correct": True,
        "explanation": "1/6 est plus petit que 1/5."
      }
    ]
  ],
  [
    "58",
    "Comparaison ordre décimaux",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "58_1",
        "type": "qcm",
        "question": "Quel est le plus grand nombre décimal parmi les suivants ?",
        "options": [
          "0.1",
          "0.01",
          "0.001",
          "0.0001"
        ],
        "correct_option": "0.1",
        "explanation": "0.1 est le plus grand nombre décimal parmi les options données."
      },
      {
        "id": "58_2",
        "type": "vrai-faux",
        "question": "Le nombre 0.5 est plus petit que le nombre 0.6.",
        "correct": True,
        "explanation": "0.5 est plus petit que 0.6."
      },
      {
        "id": "58_3",
        "type": "qcm",
        "question": "Quel est le plus petit nombre décimal parmi les suivants ?",
        "options": [
          "0.1",
          "0.01",
          "0.001",
          "0.0001"
        ],
        "correct_option": "0.0001",
        "explanation": "0.0001 est le plus petit nombre décimal parmi les options données."
      },
      {
        "id": "58_4",
        "type": "vrai-faux",
        "question": "Le nombre 0.01 est plus grand que le nombre 0.001.",
        "correct": True,
        "explanation": "0.01 est plus grand que 0.001."
      },
      {
        "id": "58_5",
        "type": "qcm",
        "question": "Quel est le nombre décimal qui se trouve entre 0.01 et 0.1 ?",
        "options": [
          "0.02",
          "0.03",
          "0.04",
          "0.05"
        ],
        "correct_option": "0.05",
        "explanation": "0.05 est le nombre décimal qui se trouve entre 0.01 et 0.1."
      },
      {
        "id": "58_6",
        "type": "vrai-faux",
        "question": "Le nombre 0.001 est plus petit que le nombre 0.0001.",
        "correct": False,
        "explanation": "0.001 est plus grand que 0.0001."
      },
      {
        "id": "58_7",
        "type": "qcm",
        "question": "Quel est le nombre décimal qui se trouve entre 0.001 et 0.01 ?",
        "options": [
          "0.002",
          "0.003",
          "0.004",
          "0.005"
        ],
        "correct_option": "0.005",
        "explanation": "0.005 est le nombre décimal qui se trouve entre 0.001 et 0.01."
      },
      {
        "id": "58_8",
        "type": "vrai-faux",
        "question": "Le nombre 0.0001 est plus petit que le nombre 0.001.",
        "correct": True,
        "explanation": "0.0001 est plus petit que 0.001."
      }
    ]
  ],
  [
    "59",
    "Multiplication entiers (1 chiffre)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "59_1",
        "type": "qcm",
        "question": "Quel est le résultat de 3 x 4 ?",
        "options": [
          "7",
          "11",
          "12",
          "15"
        ],
        "correct_option": "12",
        "explanation": "3 x 4 = 12."
      },
      {
        "id": "59_2",
        "type": "vrai-faux",
        "question": "Le résultat de 5 x 6 est 30.",
        "correct": True,
        "explanation": "5 x 6 = 30."
      },
      {
        "id": "59_3",
        "type": "qcm",
        "question": "Quel est le résultat de 7 x 8 ?",
        "options": [
          "54",
          "56",
          "58",
          "60"
        ],
        "correct_option": "56",
        "explanation": "7 x 8 = 56."
      },
      {
        "id": "59_4",
        "type": "vrai-faux",
        "question": "Le résultat de 9 x 9 est 81.",
        "correct": True,
        "explanation": "9 x 9 = 81."
      },
      {
        "id": "59_5",
        "type": "qcm",
        "question": "Quel est le résultat de 4 x 5 ?",
        "options": [
          "18",
          "19",
          "20",
          "21"
        ],
        "correct_option": "20",
        "explanation": "4 x 5 = 20."
      },
      {
        "id": "59_6",
        "type": "vrai-faux",
        "question": "Le résultat de 6 x 7 est 42.",
        "correct": True,
        "explanation": "6 x 7 = 42."
      },
      {
        "id": "59_7",
        "type": "qcm",
        "question": "Quel est le résultat de 8 x 9 ?",
        "options": [
          "70",
          "72",
          "74",
          "76"
        ],
        "correct_option": "72",
        "explanation": "8 x 9 = 72."
      },
      {
        "id": "59_8",
        "type": "vrai-faux",
        "question": "Le résultat de 2 x 3 est 5.",
        "correct": False,
        "explanation": "2 x 3 = 6, pas 5."
      }
    ]
  ],
  [
    "60",
    "Multiplication entiers (2 chiffres)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "60_1",
        "type": "qcm",
        "question": "Quel est le résultat de 12 x 3 ?",
        "options": [
          "32",
          "34",
          "36",
          "38"
        ],
        "correct_option": "36",
        "explanation": "12 x 3 = 36."
      },
      {
        "id": "60_2",
        "type": "vrai-faux",
        "question": "Le résultat de 15 x 4 est 60.",
        "correct": True,
        "explanation": "15 x 4 = 60."
      },
      {
        "id": "60_3",
        "type": "qcm",
        "question": "Quel est le résultat de 23 x 5 ?",
        "options": [
          "110",
          "115",
          "120",
          "125"
        ],
        "correct_option": "115",
        "explanation": "23 x 5 = 115."
      },
      {
        "id": "60_4",
        "type": "vrai-faux",
        "question": "Le résultat de 34 x 6 est 204.",
        "correct": True,
        "explanation": "34 x 6 = 204."
      },
      {
        "id": "60_5",
        "type": "qcm",
        "question": "Quel est le résultat de 45 x 7 ?",
        "options": [
          "310",
          "315",
          "320",
          "325"
        ],
        "correct_option": "315",
        "explanation": "45 x 7 = 315."
      },
      {
        "id": "60_6",
        "type": "vrai-faux",
        "question": "Le résultat de 56 x 8 est 448.",
        "correct": True,
        "explanation": "56 x 8 = 448."
      },
      {
        "id": "60_7",
        "type": "qcm",
        "question": "Quel est le résultat de 67 x 9 ?",
        "options": [
          "600",
          "603",
          "607",
          "606"
        ],
        "correct_option": "603",
        "explanation": "67 x 9 = 603."
      },
      {
        "id": "60_8",
        "type": "vrai-faux",
        "question": "Le résultat de 78 x 2 est 156.",
        "correct": True,
        "explanation": "78 x 2 = 156."
      }
    ]
  ],
  [
    "61",
    "Comparaison fractions (dénominateur commun)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "61_1",
        "type": "qcm",
        "question": "Quelle fraction est la plus grande : 3/4 ou 2/4 ?",
        "options": [
          "3/4",
          "2/4"
        ],
        "correct_option": "3/4",
        "explanation": "3/4 est plus grand que 2/4."
      },
      {
        "id": "61_2",
        "type": "vrai-faux",
        "question": "1/2 est égal à 2/4.",
        "correct": True,
        "explanation": "1/2 est égal à 2/4."
      },
      {
        "id": "61_3",
        "type": "qcm",
        "question": "Quelle fraction est la plus petite : 5/6 ou 4/6 ?",
        "options": [
          "5/6",
          "4/6"
        ],
        "correct_option": "4/6",
        "explanation": "4/6 est plus petit que 5/6."
      },
      {
        "id": "61_4",
        "type": "vrai-faux",
        "question": "2/3 est égal à 4/6.",
        "correct": True,
        "explanation": "2/3 est égal à 4/6."
      },
      {
        "id": "61_5",
        "type": "qcm",
        "question": "Quelle fraction est la plus grande : 7/8 ou 6/8 ?",
        "options": [
          "7/8",
          "6/8"
        ],
        "correct_option": "7/8",
        "explanation": "7/8 est plus grand que 6/8."
      },
      {
        "id": "61_6",
        "type": "vrai-faux",
        "question": "3/5 est égal à 6/10.",
        "correct": True,
        "explanation": "3/5 est égal à 6/10."
      },
      {
        "id": "61_7",
        "type": "qcm",
        "question": "Quelle fraction est la plus petite : 1/3 ou 2/3 ?",
        "options": [
          "1/3",
          "2/3"
        ],
        "correct_option": "1/3",
        "explanation": "1/3 est plus petit que 2/3."
      },
      {
        "id": "61_8",
        "type": "vrai-faux",
        "question": "4/7 est égal à 8/14.",
        "correct": True,
        "explanation": "4/7 est égal à 8/14."
      }
    ]
  ],
  [
    "62",
    "Multiplier fraction par entier",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "62_1",
        "type": "qcm",
        "question": "Quel est le résultat de 2/3 x 3 ?",
        "options": [
          "2",
          "1",
          "3/2"
        ],
        "correct_option": "2",
        "explanation": "2/3 x 3 = 2."
      },
      {
        "id": "62_2",
        "type": "vrai-faux",
        "question": "Le résultat de 4/5 x 5 est 4.",
        "correct": True,
        "explanation": "4/5 x 5 = 4."
      },
      {
        "id": "62_3",
        "type": "qcm",
        "question": "Quel est le résultat de 1/4 x 8 ?",
        "options": [
          "1",
          "2",
          "3"
        ],
        "correct_option": "2",
        "explanation": "1/4 x 8 = 2."
      },
      {
        "id": "62_4",
        "type": "vrai-faux",
        "question": "Le résultat de 3/7 x 7 est 3.",
        "correct": True,
        "explanation": "3/7 x 7 = 3."
      },
      {
        "id": "62_5",
        "type": "qcm",
        "question": "Quel est le résultat de 5/6 x 6 ?",
        "options": [
          "5",
          "6",
          "7"
        ],
        "correct_option": "5",
        "explanation": "5/6 x 6 = 5."
      },
      {
        "id": "62_6",
        "type": "vrai-faux",
        "question": "Le résultat de 2/9 x 9 est 2.",
        "correct": True,
        "explanation": "2/9 x 9 = 2."
      },
      {
        "id": "62_7",
        "type": "qcm",
        "question": "Quel est le résultat de 1/5 x 10 ?",
        "options": [
          "1",
          "2",
          "3"
        ],
        "correct_option": "2",
        "explanation": "1/5 x 10 = 2."
      },
      {
        "id": "62_8",
        "type": "vrai-faux",
        "question": "Le résultat de 6/11 x 11 est 6.",
        "correct": True,
        "explanation": "6/11 x 11 = 6."
      }
    ]
  ],
  [
    "63",
    "Balances et égalités simples",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "63_1",
        "type": "qcm",
        "question": "Quelle est la valeur de x dans l'équation 2x = 8 ?",
        "options": [
          "2",
          "4",
          "8"
        ],
        "correct_option": "4",
        "explanation": "2x = 8, donc x = 4."
      },
      {
        "id": "0063_2",
        "type": "vrai-faux",
        "question": "Si 3x = 9, alors x = 3.",
        "correct": True,
        "explanation": "3x = 9, donc x = 3."
      },
      {
        "id": "63_3",
        "type": "qcm",
        "question": "Quelle est la valeur de y dans l'équation y/4 = 2 ?",
        "options": [
          "4",
          "6",
          "8"
        ],
        "correct_option": "8",
        "explanation": "y/4 = 2, donc y = 8."
      },
      {
        "id": "63_4",
        "type": "vrai-faux",
        "question": "Si x - 5 = 10, alors x = 15.",
        "correct": True,
        "explanation": "x - 5 = 10, donc x = 15."
      },
      {
        "id": "63_5",
        "type": "qcm",
        "question": "Quelle est la valeur de z dans l'équation z + 3 = 7 ?",
        "options": [
          "2",
          "3",
          "4"
        ],
        "correct_option": "4",
        "explanation": "z + 3 = 7, donc z = 4."
      },
      {
        "id": "63_6",
        "type": "vrai-faux",
        "question": "Si 5x = 25, alors x = 5.",
        "correct": True,
        "explanation": "5x = 25, donc x = 5."
      },
      {
        "id": "63_7",
        "type": "qcm",
        "question": "Quelle est la valeur de w dans l'équation w/2 = 6 ?",
        "options": [
          "10",
          "12",
          "14"
        ],
        "correct_option": "12",
        "explanation": "w/2 = 6, donc w = 12."
      },
      {
        "id": "63_8",
        "type": "vrai-faux",
        "question": "Si x + 4 = 9, alors x = 5.",
        "correct": True,
        "explanation": "x + 4 = 9, donc x = 5."
      }
    ]
  ],
  [
    "64",
    "Résolution de problèmes simples",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "64_1",
        "type": "qcm",
        "question": "Si un train parcourt 60 km en 1 heure, combien de kilomètres parcourra-t-il en 3 heures ?",
        "options": [
          "120 km",
          "150 km",
          "180 km",
          "200 km"
        ],
        "correct_option": "180 km",
        "explanation": "Si un train parcourt 60 km en 1 heure, il parcourra 60 km x 3 heures = 180 km en 3 heures."
      },
      {
        "id": "64_2",
        "type": "vrai-faux",
        "question": "Si une voiture consomme 5 litres d'essence pour parcourir 100 km, elle consommera 10 litres pour parcourir 200 km.",
        "correct": True,
        "explanation": "Si une voiture consomme 5 litres pour 100 km, elle consommera 10 litres pour 200 km (5 litres x 2)."
      },
      {
        "id": "64_3",
        "type": "qcm",
        "question": "Si un rectangle a une longueur de 8 cm et une largeur de 5 cm, quelle est sa surface ?",
        "options": [
          "30 cm²",
          "35 cm²",
          "40 cm²",
          "45 cm²"
        ],
        "correct_option": "40 cm²",
        "explanation": "La surface d'un rectangle est calculée en multipliant la longueur par la largeur : 8 cm x 5 cm = 40 cm²."
      },
      {
        "id": "64_4",
        "type": "vrai-faux",
        "question": "Si un sac contient 3 pommes et que chaque pomme coûte 2 euros, le coût total du sac est de 6 euros.",
        "correct": True,
        "explanation": "Le coût total du sac est de 3 pommes x 2 euros par pomme = 6 euros."
      },
      {
        "id": "64_5",
        "type": "qcm",
        "question": "Si un rectangle a une longueur de 10 cm et une largeur de 4 cm, quelle est sa surface ?",
        "options": [
          "40 cm²",
          "44 cm²",
          "48 cm²",
          "52 cm²"
        ],
        "correct_option": "40 cm²",
        "explanation": "La surface d'un rectangle est calculée en multipliant la longueur par la largeur : 10 cm x 4 cm = 40 cm²."
      },
      {
        "id": "64_6",
        "type": "vrai-faux",
        "question": "Si un train parcourt 80 km en 2 heures, sa vitesse moyenne est de 40 km/h.",
        "correct": True,
        "explanation": "La vitesse moyenne est calculée en divisant la distance parcourue par le temps : 80 km / 2 heures = 40 km/h."
      },
      {
        "id": "64_7",
        "type": "qcm",
        "question": "Si un sac contient 5 oranges et que chaque orange coûte 3 euros, quel est le coût total du sac ?",
        "options": [
          "15 euros",
          "20 euros",
          "25 euros",
          "30 euros"
        ],
        "correct_option": "15 euros",
        "explanation": "Le coût total du sac est de 5 oranges x 3 euros par orange = 15 euros."
      },
      {
        "id": "64_8",
        "type": "vrai-faux",
        "question": "Si une voiture consomme 8 litres d'essence pour parcourir 160 km, elle consommera 16 litres pour parcourir 320 km.",
        "correct": True,
        "explanation": "Si une voiture consomme 8 litres pour 160 km, elle consommera 16 litres pour 320 km (8 litres x 2)."
      }
    ]
  ],
  [
    "65",
    "Lecture de graphiques",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "65_1",
        "type": "qcm",
        "question": "Quel type de graphique est le mieux adapté pour montrer la répartition d'un ensemble de données ?",
        "options": [
          "Graphique en barres",
          "Graphique circulaire",
          "Histogramme",
          "Graphique linéaire"
        ],
        "correct_option": "Graphique circulaire",
        "explanation": "Un graphique circulaire est le mieux adapté pour montrer la répartition d'un ensemble de données."
      },
      {
        "id": "65_2",
        "type": "vrai-faux",
        "question": "Un histogramme est utilisé pour représenter des données continues.",
        "correct": True,
        "explanation": "Un histogramme est utilisé pour représenter des données continues."
      },
      {
        "id": "65_3",
        "type": "qcm",
        "question": "Quel type de graphique est le mieux adapté pour montrer l'évolution d'une variable dans le temps ?",
        "options": [
          "Graphique en barres",
          "Graphique circulaire",
          "Histogramme",
          "Graphique linéaire"
        ],
        "correct_option": "Graphique linéaire",
        "explanation": "Un graphique linéaire est le mieux adapté pour montrer l'évolution d'une variable dans le temps."
      },
      {
        "id": "65_4",
        "type": "vrai-faux",
        "question": "Un graphique en barres peut être utilisé pour représenter des données catégorielles.",
        "correct": True,
        "explanation": "Un graphique en barres est généralement utilisé pour représenter des données catégorielles."
      },
      {
        "id": "65_5",
        "type": "qcm",
        "question": "Quel type de graphique est le mieux adapté pour montrer la répartition d'un ensemble de données ? (variante 2)",
        "options": [
          "Graphique en barres",
          "Graphique circulaire",
          "Histogramme",
          "Graphique linéaire"
        ],
        "correct_option": "Graphique circulaire",
        "explanation": "Un graphique circulaire est le mieux adapté pour montrer la répartition d'un ensemble de données."
      },
      {
        "id": "65_6",
        "type": "vrai-faux",
        "question": "Un graphique en secteurs est utilisé pour représenter des données continues.",
        "correct": False,
        "explanation": "Un graphique en secteurs est utilisé pour représenter des données catégorielles, pas continues."
      },
      {
        "id": "65_7",
        "type": "qcm",
        "question": "Quel type de graphique est le mieux adapté pour montrer l'évolution d'une variable dans le temps ? (variante 2)",
        "options": [
          "Graphique en barres",
          "Graphique circulaire",
          "Histogramme",
          "Graphique linéaire"
        ],
        "correct_option": "Graphique linéaire",
        "explanation": "Un graphique linéaire est le mieux adapté pour montrer l'évolution d'une variable dans le temps."
      },
      {
        "id": "65_8",
        "type": "vrai-faux",
        "question": "Un graphique en barres peut être utilisé pour représenter des données continues.",
        "correct": False,
        "explanation": "Un graphique en barres est généralement utilisé pour représenter des données catégorielles, pas continues."
      }
    ]
  ],
  [
    "66",
    "Droites perpendiculaires/parallèles",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "66_1",
        "type": "qcm",
        "question": "Deux droites sont parallèles si elles sont dans le même plan et ne se coupent jamais.",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "Deux droites sont parallèles si elles sont dans le même plan et ne se coupent jamais."
      },
      {
        "id": "66_2",
        "type": "vrai-faux",
        "question": "Deux droites sont perpendiculaires si elles se coupent à un angle de 90 degrés.",
        "correct": True,
        "explanation": "Deux droites sont perpendiculaires si elles se coupent à un angle de 90 degrés."
      },
      {
        "id": "66_3",
        "type": "qcm",
        "question": "Deux droites sont parallèles si elles sont dans le même plan et se coupent à un angle de 90 degrés.",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Faux",
        "explanation": "Deux droites sont parallèles si elles sont dans le même plan et ne se coupent jamais."
      },
      {
        "id": "66_4",
        "type": "vrai-faux",
        "question": "Deux droites sont perpendiculaires si elles se coupent à un angle de 45 degrés.",
        "correct": False,
        "explanation": "Deux droites sont perpendiculaires si elles se coupent à un angle de 90 degrés, pas 45 degrés."
      },
      {
        "id": "66_5",
        "type": "qcm",
        "question": "Deux droites sont parallèles si elles sont dans le même plan et ne se coupent jamais. (variante 2)",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "Deux droites sont parallèles si elles sont dans le même plan et ne se coupent jamais."
      },
      {
        "id": "66_6",
        "type": "vrai-faux",
        "question": "Deux droites sont perpendiculaires si elles se coupent à un angle de 90 degrés. (variante 2)",
        "correct": True,
        "explanation": "Deux droites sont perpendiculaires si elles se coupent à un angle de 90 degrés."
      },
      {
        "id": "66_7",
        "type": "qcm",
        "question": "Deux droites sont parallèles si elles sont dans le même plan et se coupent à un angle de 90 degrés. (variante 2)",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Faux",
        "explanation": "Deux droites sont parallèles si elles sont dans le même plan et ne se coupent jamais."
      },
      {
        "id": "66_8",
        "type": "vrai-faux",
        "question": "Deux droites sont perpendiculaires si elles se coupent à un angle de 45 degrés. (variante 2)",
        "correct": False,
        "explanation": "Deux droites sont perpendiculaires si elles se coupent à un angle de 90 degrés, pas 45 degrés."
      }
    ]
  ],
  [
    "67",
    "Bissectrice d’angle, Médiatrice segment, Disque et cercle (vocabulaire)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "67_1",
        "type": "qcm",
        "question": "La bissectrice d'un angle est une droite qui divise l'angle en deux angles égaux.",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "La bissectrice d'un angle est une droite qui divise l'angle en deux angles égaux."
      },
      {
        "id": "67_2",
        "type": "vrai-faux",
        "question": "La bissectrice d'un angle est une droite qui divise l'angle en deux angles inégaux.",
        "correct": False,
        "explanation": "La bissectrice d'un angle divise l'angle en deux angles égaux, pas inégaux."
      },
      {
        "id": "67_3",
        "type": "qcm",
        "question": "La médiatrice d'un segment est une droite qui passe par le milieu du segment et est perpendiculaire à ce segment.",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "La médiatrice d'un segment est une droite qui passe par le milieu du segment et est perpendiculaire à ce segment."
      },
      {
        "id": "67_4",
        "type": "vrai-faux",
        "question": "La médiatrice d'un segment est une droite qui passe par une extrémité du segment et est parallèle à ce segment.",
        "correct": False,
        "explanation": "La médiatrice d'un segment passe par le milieu du segment et est perpendiculaire à ce segment, pas par une extrémité et parallèle."
      },
      {
        "id": "67_5",
        "type": "qcm",
        "question": "Un disque est la partie du plan délimitée par un cercle.",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "Un disque est la partie du plan délimitée par un cercle."
      },
      {
        "id": "67_6",
        "type": "vrai-faux",
        "question": "Un disque est la partie du plan délimitée par une droite.",
        "correct": False,
        "explanation": "Un disque est la partie du plan délimitée par un cercle, pas par une droite."
      },
      {
        "id": "67_7",
        "type": "qcm",
        "question": "Un cercle est la ligne formée par tous les points du plan qui sont à une distance donnée d'un point fixe appelé centre.",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "Un cercle est la ligne formée par tous les points du plan qui sont à une distance donnée d'un point fixe appelé centre."
      },
      {
        "id": "67_8",
        "type": "vrai-faux",
        "question": "Un cercle est la ligne formée par tous les points du plan qui sont à une distance donnée d'une droite.",
        "correct": False,
        "explanation": "Un cercle est la ligne formée par tous les points du plan qui sont à une distance donnée d'un point fixe appelé centre, pas d'une droite."
      }
    ]
  ],
  [
    "68",
    "Quadrilatères (carré, rectangle)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "68_1",
        "type": "qcm",
        "question": "Un carré est un quadrilatère qui a quatre côtés de même longueur et quatre angles droits.",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "Un carré est un quadrilatère qui a quatre côtés de même longueur et quatre angles droits."
      },
      {
        "id": "68_2",
        "type": "vrai-faux",
        "question": "Un carré est un quadrilatère qui a deux côtés de même longueur et quatre angles droits.",
        "correct": False,
        "explanation": "Un carré a quatre côtés de même longueur, pas seulement deux."
      },
      {
        "id": "68_3",
        "type": "qcm",
        "question": "Un rectangle est un quadrilatère qui a quatre angles droits et des côtés opposés de même longueur.",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "Un rectangle est un quadrilatère qui a quatre angles droits et des côtés opposés de même longueur."
      },
      {
        "id": "68_4",
        "type": "vrai-faux",
        "question": "Un rectangle est un quadrilatère qui a quatre angles droits et des côtés de même longueur.",
        "correct": False,
        "explanation": "Un rectangle a des côtés opposés de même longueur, pas tous les côtés."
      },
      {
        "id": "68_5",
        "type": "qcm",
        "question": "Un carré est un quadrilatère qui a quatre côtés de même longueur et quatre angles droits. (variante 2)",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "Un carré est un quadrilatère qui a quatre côtés de même longueur et quatre angles droits."
      },
      {
        "id": "68_6",
        "type": "vrai-faux",
        "question": "Un carré est un quadrilatère qui a deux côtés de même longueur et quatre angles droits. (variante 2)",
        "correct": False,
        "explanation": "Un carré a quatre côtés de même longueur, pas seulement deux."
      },
      {
        "id": "68_7",
        "type": "qcm",
        "question": "Un rectangle est un quadrilatère qui a quatre angles droits et des côtés opposés de même longueur. (variante 2)",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "Un rectangle est un quadrilatère qui a quatre angles droits et des côtés opposés de même longueur."
      },
      {
        "id": "68_8",
        "type": "vrai-faux",
        "question": "Un rectangle est un quadrilatère qui a quatre angles droits et des côtés de même longueur. (variante 2)",
        "correct": False,
        "explanation": "Un rectangle a des côtés opposés de même longueur, pas tous les côtés."
      }
    ]
  ],
  [
    "69",
    "Moyenne simple",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "69_1",
        "type": "qcm",
        "question": "Quelle est la moyenne de 4, 6 et 8 ?",
        "options": [
          "5",
          "6",
          "7",
          "8"
        ],
        "correct_option": "6",
        "explanation": "La moyenne de 4, 6 et 8 est (4 + 6 + 8) / 3 = 18 / 3 = 6."
      },
      {
        "id": "0069_2",
        "type": "vrai-faux",
        "question": "La moyenne de 10, 20 et 30 est 20.",
        "correct": True,
        "explanation": "La moyenne de 10, 20 et 30 est (10 + 20 + 30) / 3 = 60 / 3 = 20."
      },
      {
        "id": "69_3",
        "type": "qcm",
        "question": "Quelle est la moyenne de 5, 10 et 15 ?",
        "options": [
          "8",
          "10",
          "12",
          "15"
        ],
        "correct_option": "10",
        "explanation": "La moyenne de 5, 10 et 15 est (5 + 10 + 15) / 3 = 30 / 3 = 10."
      },
      {
        "id": "69_4",
        "type": "vrai-faux",
        "question": "La moyenne de 2, 4 et 6 est 4.",
        "correct": True,
        "explanation": "La moyenne de 2, 4 et 6 est (2 + 4 + 6) / 3 = 12 / 3 = 4."
      },
      {
        "id": "69_5",
        "type": "qcm",
        "question": "Quelle est la moyenne de 1, 2 et 3 ?",
        "options": [
          "1",
          "2",
          "3",
          "4"
        ],
        "correct_option": "2",
        "explanation": "La moyenne de 1, 2 et 3 est (1 + 2 + 3) / 3 = 6 / 3 = 2."
      },
      {
        "id": "69_6",
        "type": "vrai-faux",
        "question": "La moyenne de -1, -2 et -3 est -2.",
        "correct": True,
        "explanation": "La moyenne de -1, -2 et -3 est (-1 + (-2) + (-3)) / 3 = (-6) / 3 = -2."
      },
      {
        "id": "69_7",
        "type": "qcm",
        "question": "Quelle est la moyenne de -4, -2 et 0 ?",
        "options": [
          "-2",
          "-1",
          "0",
          "1"
        ],
        "correct_option": "-2",
        "explanation": "La moyenne de -4, -2 et 0 est (-4 + (-2) + 0) / 3 = (-6) / 3 = -2."
      },
      {
        "id": "69_8",
        "type": "vrai-faux",
        "question": "La moyenne de 0, 0 et 0 est 0.",
        "correct": True,
        "explanation": "La moyenne de 0, 0 et 0 est (0 + 0 + 0) / 3 = 0 / 3 = 0."
      }
    ]
  ],
  [
    "70",
    "Probabilités équiprobables (pièces, dés)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "70_1",
        "type": "qcm",
        "question": "Quelle est la probabilité d'obtenir face en lançant une pièce ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/6"
        ],
        "correct_option": "1/2",
        "explanation": "La probabilité d'obtenir face en lançant une pièce est de 1/2."
      },
      {
        "id": "0070_2",
        "type": "qcm",
        "question": "Quelle est la probabilité d'obtenir un 6 en lançant un dé ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/6"
        ],
        "correct_option": "1/6",
        "explanation": "La probabilité d'obtenir un 6 en lançant un dé est de 1/6."
      },
      {
        "id": "0070_3",
        "type": "qcm",
        "question": "Quelle est la probabilité d'obtenir un nombre pair en lançant un dé ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/6"
        ],
        "correct_option": "1/2",
        "explanation": "La probabilité d'obtenir un nombre pair en lançant un dé est de 1/2 (2, 4, 6)."
      },
      {
        "id": "0070_4",
        "type": "qcm",
        "question": "Quelle est la probabilité d'obtenir un nombre impair en lançant un dé ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/6"
        ],
        "correct_option": "1/2",
        "explanation": "La probabilité d'obtenir un nombre impair en lançant un dé est de 1/2 (1, 3, 5)."
      },
      {
        "id": "0070_5",
        "type": "qcm",
        "question": "Quelle est la probabilité d'obtenir face ou pile en lançant une pièce ?",
        "options": [
          "1",
          "1/2",
          "1/3",
          "1/4"
        ],
        "correct_option": "1",
        "explanation": "La probabilité d'obtenir face ou pile en lançant une pièce est de 1 (100%)."
      },
      {
        "id": "0070_6",
        "type": "qcm",
        "question": "Quelle est la probabilité d'obtenir un nombre supérieur à 4 en lançant un dé ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/6"
        ],
        "correct_option": "1/3",
        "explanation": "La probabilité d'obtenir un nombre supérieur à 4 en lançant un dé est de 1/3 (5, 6)."
      },
      {
        "id": "0070_7",
        "type": "qcm",
        "question": "Quelle est la probabilité d'obtenir un nombre inférieur à 3 en lançant un dé ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/6"
        ],
        "correct_option": "1/3",
        "explanation": "La probabilité d'obtenir un nombre inférieur à 3 en lançant un dé est de 1/3 (1, 2)."
      },
      {
        "id": "0070_8",
        "type": "qcm",
        "question": "Quelle est la probabilité d'obtenir un nombre entre 2 et 5 en lançant un dé ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/6"
        ],
        "correct_option": "1/2",
        "explanation": "La probabilité d'obtenir un nombre entre 2 et 5 en lançant un dé est de 1/2 (2, 3, 4, 5)."
      }
    ]
  ],
  [
    "0071",
    "Notion de variable (sans calcul de l’expression littérale)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "71_1",
        "type": "qcm",
        "question": "Dans l'expression 2x + 3, que représente x ?",
        "options": [
          "Une constante",
          "Une variable",
          "Un coefficient",
          "Un nombre"
        ],
        "correct_option": "Une variable",
        "explanation": "Dans l'expression 2x + 3, x représente une variable."
      },
      {
        "id": "71_2",
        "type": "vrai-faux",
        "question": "Dans l'expression 5y - 4, y est une variable.",
        "correct": True,
        "explanation": "Dans l'expression 5y - 4, y est une variable."
      },
      {
        "id": "71_3",
        "type": "qcm",
        "question": "Dans l'expression 3z + 2, que représente z ?",
        "options": [
          "Une constante",
          "Une variable",
          "Un coefficient",
          "Un nombre"
        ],
        "correct_option": "Une variable",
        "explanation": "Dans l'expression 3z + 2, z représente une variable."
      },
      {
        "id": "71_4",
        "type": "vrai-faux",
        "question": "Dans l'expression 7a - 5, a est une variable.",
        "correct": True,
        "explanation": "Dans l'expression 7a - 5, a est une variable."
      },
      {
        "id": "71_5",
        "type": "qcm",
        "question": "Dans l'expression 4b + 6, que représente b ?",
        "options": [
          "Une constante",
          "Une variable",
          "Un coefficient",
          "Un nombre"
        ],
        "correct_option": "Une variable",
        "explanation": "Dans l'expression 4b + 6, b représente une variable."
      },
      {
        "id": "71_6",
        "type": "vrai-faux",
        "question": "Dans l'expression 9c - 3, c est une variable.",
        "correct": True,
        "explanation": "Dans l'expression 9c - 3, c est une variable."
      },
      {
        "id": "71_7",
        "type": "qcm",
        "question": "Dans l'expression 6d + 1, que représente d ?",
        "options": [
          "Une constante",
          "Une variable",
          "Un coefficient",
          "Un nombre"
        ],
        "correct_option": "Une variable",
        "explanation": "Dans l'expression 6d + 1, d représente une variable."
      },
      {
        "id": "71_8",
        "type": "vrai-faux",
        "question": "Dans l'expression 8e - 2, e est une variable.",
        "correct": True,
        "explanation": "Dans l'expression 8e - 2, e est une variable."
      }
    ]
  ],
  [
    "72",
    "Déconstruction intuitions hasardeuses et certitudes (exemples de situations de hasard, d’incertitude, de certitude)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "0072_1",
        "type": "qcm",
        "question": "Si vous lancez une pièce, quelle est la probabilité d'obtenir face ?",
        "options": [
          "1/2",
          "1/3",
          "1/4",
          "1/6"
        ],
        "correct_option": "1/2",
        "explanation": "La probabilité d'obtenir face en lançant une pièce est de 1/2."
      },
      {
        "id": "0072_2",
        "type": "vrai-faux",
        "question": "Si vous lancez un dé, la probabilité d'obtenir un 6 est de 1/6.",
        "correct": True,
        "explanation": "La probabilité d'obtenir un 6 en lançant un dé est de 1/6."
      },
      {
        "id": "0072_3",
        "type": "qcm",
        "question": "Si vous tirez une carte d'un jeu de 52 cartes, quelle est la probabilité d'obtenir un as ?",
        "options": [
          "1/52",
          "1/13",
          "1/4",
          "1/2"
        ],
        "correct_option": "1/13",
        "explanation": "Il y a 4 as dans un jeu de 52 cartes, donc la probabilité d'obtenir un as est de 4/52 = 1/13."
      },
      {
        "id": "0072_4",
        "type": "vrai-faux",
        "question": "Si vous lancez une pièce 10 fois, la probabilité d'obtenir face au moins une fois est de 1.",
        "correct": True,
        "explanation": "La probabilité d'obtenir face au moins une fois en lançant une pièce 10 fois est de 1 (100%)."
      },
      {
        "id": "0072_5",
        "type": "qcm",
        "question": "Si vous lancez un dé 6 fois, quelle est la probabilité d'obtenir au moins un 6 ?",
        "options": [
          "1/6",
          "1/3",
          "1/2",
          "1"
        ],
        "correct_option": "1/2",
        "explanation": "La probabilité d'obtenir au moins un 6 en lançant un dé 6 fois est de 1 - (5/6)^6 ≈ 0.6651, soit environ 1/2."
      },
      {
        "id": "72_6",
        "type": "vrai-faux",
        "question": "Si vous tirez une carte d'un jeu de 52 cartes, la probabilité d'obtenir un roi est de 1/13.",
        "correct": True,
        "explanation": "Il y a 4 rois dans un jeu de 52 cartes, donc la probabilité d'obtenir un roi est de 4/52 = 1/13."
      },
      {
        "id": "72_7",
        "type": "qcm",
        "question": "Si vous lancez une pièce 5 fois, quelle est la probabilité d'obtenir face exactement 3 fois ?",
        "options": [
          "1/16",
          "5/16",
          "10/16",
          "15/16"
        ],
        "correct_option": "10/16",
        "explanation": "La probabilité d'obtenir face exactement 3 fois en lançant une pièce 5 fois est de C(5, 3) * (1/2)^3 * (1/2)^2 = 10/16."
      },
      {
        "id": "72_8",
        "type": "vrai-faux",
        "question": "Si vous lancez un dé 4 fois, la probabilité d'obtenir un nombre pair au moins une fois est de 1.",
        "correct": True,
        "explanation": "La probabilité d'obtenir un nombre pair au moins une fois en lançant un dé 4 fois est de 1 - (1/2)^4 = 1 - 1/16 = 15/16, soit environ 1."
      }
    ]
  ],
  [
    "73",
    "Proportionnalité tableau valeurs, graphiques, situations de la vie courante",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "73_1",
        "type": "qcm",
        "question": "Si 3 kg de pommes coûtent 6 euros, combien coûteront 5 kg de pommes ?",
        "options": [
          "8 euros",
          "10 euros",
          "12 euros",
          "15 euros"
        ],
        "correct_option": "10 euros",
        "explanation": "Si 3 kg de pommes coûtent 6 euros, le prix par kg est de 6 euros / 3 kg = 2 euros/kg. Donc, 5 kg de pommes coûteront 5 kg x 2 euros/kg = 10 euros."
      },
      {
        "id": "73_2",
        "type": "vrai-faux",
        "question": "Si 4 litres de peinture couvrent 20 m², alors 10 litres de peinture couvriront 50 m².",
        "correct": True,
        "explanation": "Si 4 litres de peinture couvrent 20 m², le taux de couverture est de 20 m² / 4 litres = 5 m²/litre. Donc, 10 litres de peinture couvriront 10 litres x 5 m²/litre = 50 m²."
      },
      {
        "id": "73_3",
        "type": "qcm",
        "question": "Si 2 heures de travail permettent de construire 4 chaises, combien de chaises pourront être construites en 5 heures ?",
        "options": [
          "8 chaises",
          "10 chaises",
          "12 chaises",
          "15 chaises"
        ],
        "correct_option": "10 chaises",
        "explanation": "Si 2 heures de travail permettent de construire 4 chaises, le taux de production est de 4 chaises / 2 heures = 2 chaises/heure. Donc, en 5 heures, on pourra construire 5 heures x 2 chaises/heure = 10 chaises."
      },
      {
        "id": "73_4",
        "type": "vrai-faux",
        "question": "Si 5 kg de pommes coûtent 10 euros, alors 8 kg de pommes coûteront 16 euros.",
        "correct": True,
        "explanation": "Si 5 kg de pommes coûtent 10 euros, le prix par kg est de 10 euros / 5 kg = 2 euros/kg. Donc, 8 kg de pommes coûteront 8 kg x 2 euros/kg = 16 euros."
      },
      {
        "id": "73_5",
        "type": "qcm",
        "question": "Si 6 litres de peinture couvrent 30 m², combien de litres de peinture seront nécessaires pour couvrir 50 m² ?",
        "options": [
          "8 litres",
          "10 litres",
          "12 litres",
          "15 litres"
        ],
        "correct_option": "10 litres",
        "explanation": "Si 6 litres de peinture couvrent 30 m², le taux de couverture est de 30 m² / 6 litres = 5 m²/litre. Donc, pour couvrir 50 m², il faudra 50 m² / 5 m²/litre = 10 litres de peinture."
      },
      {
        "id": "73_6",
        "type": "vrai-faux",
        "question": "Si 3 heures de travail permettent de construire 6 chaises, alors en 9 heures, on pourra construire 18 chaises.",
        "correct": True,
        "explanation": "Si 3 heures de travail permettent de construire 6 chaises, le taux de production est de 6 chaises / 3 heures = 2 chaises/heure. Donc, en 9 heures, on pourra construire 9 heures x 2 chaises/heure = 18 chaises."
      },
      {
        "id": "73_7",
        "type": "qcm",
        "question": "Si 4 kg de pommes coûtent 8 euros, combien coûteront 7 kg de pommes ?",
        "options": [
          "12 euros",
          "14 euros",
          "16 euros",
          "18 euros"
        ],
        "correct_option": "14 euros",
        "explanation": "Si 4 kg de pommes coûtent 8 euros, le prix par kg est de 8 euros / 4 kg = 2 euros/kg. Donc, 7 kg de pommes coûteront 7 kg x 2 euros/kg = 14 euros."
      },
      {
        "id": "73_8",
        "type": "vrai-faux",
        "question": "Si en travaillant pendant une heure on peut construire une chaise, alors en travaillant pendant trois heures on pourra construire trois chaises.",
        "correct": True,
        "explanation": "Si en travaillant pendant une heure on peut construire une chaise, alors en travaillant pendant trois heures on pourra construire trois chaises."
      }
    ]
  ],
  [
    "74",
    "Échelles cartographiques",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "74_1",
        "type": "qcm",
        "question": "Si une carte a une échelle de 1:100 000, cela signifie que 1 cm sur la carte représente combien de cm dans la réalité ?",
        "options": [
          "1 cm",
          "10 cm",
          "100 cm",
          "100 000 cm"
        ],
        "correct_option": "100 000 cm",
        "explanation": "Si une carte a une échelle de 1:100 000, cela signifie que 1 cm sur la carte représente 100 000 cm dans la réalité."
      },
      {
        "id": "74_2",
        "type": "vrai-faux",
        "question": "Si une carte a une échelle de 1:50 000, cela signifie que 1 cm sur la carte représente 50 000 cm dans la réalité.",
        "correct": True,
        "explanation": "Si une carte a une échelle de 1:50 000, cela signifie que 1 cm sur la carte représente 50 000 cm dans la réalité."
      },
      {
        "id": "74_3",
        "type": "qcm",
        "question": "Si une carte a une échelle de 1:25 000, cela signifie que 1 cm sur la carte représente combien de cm dans la réalité ?",
        "options": [
          "25 cm",
          "250 cm",
          "2 500 cm",
          "25 000 cm"
        ],
        "correct_option": "25 000 cm",
        "explanation": "Si une carte a une échelle de 1:25 000, cela signifie que 1 cm sur la carte représente 25 000 cm dans la réalité."
      },
      {
        "id": "74_4",
        "type": "vrai-faux",
        "question": "Si une carte a une échelle de 1:10 000, cela signifie que 1 cm sur la carte représente 10 000 cm dans la réalité.",
        "correct": True,
        "explanation": "Si une carte a une échelle de 1:10 000, cela signifie que 1 cm sur la carte représente 10 000 cm dans la réalité."
      },
      {
        "id": "74_5",
        "type": "qcm",
        "question": "Si une carte a une échelle de 1:5 000, cela signifie que 1 cm sur la carte représente combien de cm dans la réalité ?",
        "options": [
          "5 cm",
          "50 cm",
          "500 cm",
          "5 000 cm"
        ],
        "correct_option": "5 000 cm",
        "explanation": "Si une carte a une échelle de 1:5 000, cela signifie que 1 cm sur la carte représente 5 000 cm dans la réalité."
      },
      {
        "id": "74_6",
        "type": "vrai-faux",
        "question": "Si une carte a une échelle de 1:1 000, cela signifie que 1 cm sur la carte représente 1 000 cm dans la réalité.",
        "correct": True,
        "explanation": "Si une carte a une échelle de 1:1 000, cela signifie que 1 cm sur la carte représente 1 000 cm dans la réalité."
      },
      {
        "id": "74_7",
        "type": "qcm",
        "question": "Si une carte a une échelle de 1:500, cela signifie que 1 cm sur la carte représente combien de cm dans la réalité ?",
        "options": [
          "5 cm",
          "50 cm",
          "500 cm",
          "5 000 cm"
        ],
        "correct_option": "500 cm",
        "explanation": "Si une carte a une échelle de 1:500, cela signifie que 1 cm sur la carte représente 500 cm dans la réalité."
      },
      {
        "id": "74_8",
        "type": "vrai-faux",
        "question": "Si une carte a une échelle de 1:200, cela signifie que 1 cm sur la carte représente 200 cm dans la réalité.",
        "correct": True,
        "explanation": "Si une carte a une échelle de 1:200, cela signifie que 1 cm sur la carte représente 200 cm dans la réalité."
      }
    ]
  ],
  [
    "75",
    "Suites logiques (numériques, géométriques, de formes, de couleurs)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "75_1",
        "type": "qcm",
        "question": "Quelle est la prochaine forme dans la suite : cercle, carré, triangle, cercle, carré, triangle, ... ?",
        "options": [
          "Cercle",
          "Carré",
          "Triangle",
          "Rectangle"
        ],
        "correct_option": "Cercle",
        "explanation": "La suite de formes est cercle, carré, triangle, cercle, carré, triangle, ... La prochaine forme est donc un cercle."
      },
      {
        "id": "75_2",
        "type": "vrai-faux",
        "question": "La suite de nombres 2, 4, 6, 8, ... est une suite logique.",
        "correct": True,
        "explanation": "La suite de nombres 2, 4, 6, 8, ... est une suite logique car chaque nombre est obtenu en ajoutant 2 au nombre précédent."
      },
      {
        "id": "75_3",
        "type": "qcm",
        "question": "Quelle est la prochaine couleur dans la suite : rouge, vert, bleu, rouge, vert, bleu, ... ?",
        "options": [
          "Rouge",
          "Vert",
          "Bleu",
          "Jaune"
        ],
        "correct_option": "Rouge",
        "explanation": "La suite de couleurs est rouge, vert, bleu, rouge, vert, bleu, ... La prochaine couleur est donc rouge."
      },
      {
        "id": "75_4",
        "type": "vrai-faux",
        "question": "La suite de nombres 1, 2, 4, 8, ... est une suite logique.",
        "correct": True,
        "explanation": "La suite de nombres 1, 2, 4, 8, ... est une suite logique car chaque nombre est obtenu en multipliant le nombre précédent par 2."
      },
      {
        "id": "75_5",
        "type": "qcm",
        "question": "Quelle est la prochaine forme dans la suite : carré, triangle, cercle, carré, triangle, cercle, ... ?",
        "options": [
          "Carré",
          "Triangle",
          "Cercle",
          "Rectangle"
        ],
        "correct_option": "Carré",
        "explanation": "La suite de formes est carré, triangle, cercle, carré, triangle, cercle, ... La prochaine forme est donc un carré."
      },
      {
        "id": "75_6",
        "type": "vrai-faux",
        "question": "La suite de nombres 3, 6, 9, 12, ... est une suite logique.",
        "correct": True,
        "explanation": "La suite de nombres 3, 6, 9, 12, ... est une suite logique car chaque nombre est obtenu en ajoutant 3 au nombre précédent."
      },
      {
        "id": "75_7",
        "type": "qcm",
        "question": "Quelle est la prochaine couleur dans la suite : jaune, bleu, rouge, jaune, bleu, rouge, ... ?",
        "options": [
          "Jaune",
          "Bleu",
          "Rouge",
          "Vert"
        ],
        "correct_option": "Jaune",
        "explanation": "La suite de couleurs est jaune, bleu, rouge, jaune, bleu, rouge, ... La prochaine couleur est donc jaune."
      },
      {
        "id": "75_8",
        "type": "vrai-faux",
        "question": "La suite de nombres 5, 10, 15, 20, ... est une suite logique.",
        "correct": True,
        "explanation": "La suite de nombres 5, 10, 15, 20, ... est une suite logique car chaque nombre est obtenu en ajoutant 5 au nombre précédent."
      }
    ]
  ],
  [
    "76",
    "Algorithmes simples (tri)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "76_1",
        "type": "qcm",
        "question": "Quel est le résultat du tri de la liste [3, 1, 4, 1, 5] en ordre croissant ?",
        "options": [
          "[1, 1, 3, 4, 5]",
          "[1, 3, 4, 5]",
          "[3, 1, 4, 1, 5]",
          "[5, 4, 3, 1, 1]"
        ],
        "correct_option": "[1, 1, 3, 4, 5]",
        "explanation": "Le tri de la liste [3, 1, 4, 1, 5] en ordre croissant donne [1, 1, 3, 4, 5]."
      },
      {
        "id": "76_2",
        "type": "vrai-faux",
        "question": "Le tri de la liste [5, 4, 3, 2, 1] en ordre croissant donne [1, 2, 3, 4, 5].",
        "correct": True,
        "explanation": "Le tri de la liste [5, 4, 3, 2, 1] en ordre croissant donne [1, 2, 3, 4, 5]."
      },
      {
        "id": "76_3",
        "type": "qcm",
        "question": "Quel est le résultat du tri de la liste [2, 7, 1, 8, 2] en ordre croissant ?",
        "options": [
          "[1, 2, 2, 7, 8]",
          "[1, 7, 2, 8, 2]",
          "[2, 7, 1, 8, 2]",
          "[8, 7, 2, 2, 1]"
        ],
        "correct_option": "[1, 2, 2, 7, 8]",
        "explanation": "Le tri de la liste [2, 7, 1, 8, 2] en ordre croissant donne [1, 2, 2, 7, 8]."
      },
      {
        "id": "76_4",
        "type": "vrai-faux",
        "question": "Le tri de la liste [1, 2, 3, 4, 5] en ordre croissant donne [1, 2, 3, 4, 5].",
        "correct": True,
        "explanation": "Le tri de la liste [1, 2, 3, 4, 5] en ordre croissant donne [1, 2, 3, 4, 5]."
      },
      {
        "id": "76_5",
        "type": "qcm",
        "question": "Quel est le résultat du tri de la liste [9, 5, 2, 6, 5] en ordre croissant ?",
        "options": [
          "[2, 5, 5, 6, 9]",
          "[2, 6, 5, 5, 9]",
          "[9, 5, 2, 6, 5]",
          "[9, 6, 5, 5, 2]"
        ],
        "correct_option": "[2, 5, 5, 6, 9]",
        "explanation": "Le tri de la liste [9, 5, 2, 6, 5] en ordre croissant donne [2, 5, 5, 6, 9]."
      },
      {
        "id": "76_6",
        "type": "vrai-faux",
        "question": "Le tri de la liste [4, 3, 2, 1] en ordre croissant donne [1, 2, 3, 4].",
        "correct": True,
        "explanation": "Le tri de la liste [4, 3, 2, 1] en ordre croissant donne [1, 2, 3, 4]."
      },
      {
        "id": "76_7",
        "type": "qcm",
        "question": "Quel est le résultat du tri de la liste [8, 7, 6] en ordre croissant ?",
        "options": [
          "[6, 7, 8]",
          "[7, 8, 6]",
          "[8, 7, 6]",
          "[8, 6, 7]"
        ],
        "correct_option": "[6, 7, 8]",
        "explanation": "Le tri de la liste [8, 7, 6] en ordre croissant donne [6, 7, 8]."
      },
      {
        "id": "76_8",
        "type": "vrai-faux",
        "question": "Le tri de la liste [10] en ordre croissant donne [10].",
        "correct": True,
        "explanation": "Le tri de la liste [10] en ordre croissant donne [10]."
      }
    ]
  ],
  [
    "77",
    "Initiation programmation (ex. : Scratch blocs)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "77_1",
        "type": "qcm",
        "question": "Quel bloc Scratch permet de répéter une action 10 fois ?",
        "options": [
          "Répéter indéfiniment",
          "Répéter 10 fois",
          "Répéter jusqu'à ce que",
          "Répéter pendant"
        ],
        "correct_option": "Répéter 10 fois",
        "explanation": "Le bloc Scratch 'Répéter 10 fois' permet de répéter une action 10 fois."
      },
      {
        "id": "77_2",
        "type": "vrai-faux",
        "question": "Le bloc Scratch 'Répéter indéfiniment' permet de répéter une action sans fin.",
        "correct": True,
        "explanation": "Le bloc Scratch 'Répéter indéfiniment' permet de répéter une action sans fin."
      },
      {
        "id": "77_3",
        "type": "qcm",
        "question": "Quel bloc Scratch permet de répéter une action jusqu'à ce qu'une condition soit vraie ?",
        "options": [
          "Répéter indéfiniment",
          "Répéter 10 fois",
          "Répéter jusqu'à ce que",
          "Répéter pendant"
        ],
        "correct_option": "Répéter jusqu'à ce que",
        "explanation": "Le bloc Scratch 'Répéter jusqu'à ce que' permet de répéter une action jusqu'à ce qu'une condition soit vraie."
      },
      {
        "id": "77_4",
        "type": "vrai-faux",
        "question": "Le bloc Scratch 'Répéter pendant' permet de répéter une action pendant un certain temps.",
        "correct": True,
        "explanation": "Le bloc Scratch 'Répéter pendant' permet de répéter une action pendant un certain temps."
      },
      {
        "id": "77_5",
        "type": "qcm",
        "question": "Quel bloc Scratch permet de répéter une action indéfiniment ?",
        "options": [
          "Répéter indéfiniment",
          "Répéter 10 fois",
          "Répéter jusqu'à ce que",
          "Répéter pendant"
        ],
        "correct_option": "Répéter indéfiniment",
        "explanation": "Le bloc Scratch 'Répéter indéfiniment' permet de répéter une action indéfiniment."
      },
      {
        "id": "77_6",
        "type": "vrai-faux",
        "question": "Le bloc Scratch 'Répéter 10 fois' permet de répéter une action 10 fois.",
        "correct": True,
        "explanation": "Le bloc Scratch 'Répéter 10 fois' permet de répéter une action 10 fois."
      },
      {
        "id": "77_7",
        "type": "qcm",
        "question": "Quel bloc Scratch permet de répéter une action pendant un certain temps ?",
        "options": [
          "Répéter indéfiniment",
          "Répéter 10 fois",
          "Répéter jusqu'à ce que",
          "Répéter pendant"
        ],
        "correct_option": "Répéter pendant",
        "explanation": "Le bloc Scratch 'Répéter pendant' permet de répéter une action pendant un certain temps."
      },
      {
        "id": "77_8",
        "type": "vrai-faux",
        "question": "Le bloc Scratch 'Répéter jusqu'à ce que' permet de répéter une action jusqu'à ce qu'une condition soit vraie.",
        "correct": True,
        "explanation": "Le bloc Scratch 'Répéter jusqu'à ce que' permet de répéter une action jusqu'à ce qu'une condition soit vraie."
      }
    ]
  ],
  [
    "78",
    "Capacité (litres/cL/mL)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "78_1",
        "type": "qcm",
        "question": "Combien de centilitres y a-t-il dans 1 litre ?",
        "options": [
          "10 cL",
          "100 cL",
          "1 000 cL",
          "10 000 cL"
        ],
        "correct_option": "100 cL",
        "explanation": "Il y a 100 centilitres dans 1 litre."
      },
      {
        "id": "78_2",
        "type": "vrai-faux",
        "question": "Il y a 1 000 millilitres dans 1 litre.",
        "correct": True,
        "explanation": "Il y a 1 000 millilitres dans 1 litre."
      },
      {
        "id": "78_3",
        "type": "qcm",
        "question": "Combien de millilitres y a-t-il dans 1 centilitre ?",
        "options": [
          "10 mL",
          "100 mL",
          "1 000 mL",
          "10 000 mL"
        ],
        "correct_option": "10 mL",
        "explanation": "Il y a 10 millilitres dans 1 centilitre."
      },
      {
        "id": "78_4",
        "type": "vrai-faux",
        "question": "Il y a 100 centilitres dans 1 litre.",
        "correct": True,
        "explanation": "Il y a 100 centilitres dans 1 litre."
      },
      {
        "id": "78_5",
        "type": "qcm",
        "question": "Combien de millilitres y a-t-il dans 1 litre ?",
        "options": [
          "100 mL",
          "500 mL",
          "1 000 mL",
          "10 000 mL"
        ],
        "correct_option": "1 000 mL",
        "explanation": "Il y a 1 000 millilitres dans 1 litre."
      },
      {
        "id": "78_6",
        "type": "vrai-faux",
        "question": "Il y a 10 centilitres dans 1 litre.",
        "correct": False,
        "explanation": "Il y a 100 centilitres dans 1 litre, pas 10."
      },
      {
        "id": "78_7",
        "type": "qcm",
        "question": "Combien de centilitres y a-t-il dans 1 millilitre ?",
        "options": [
          "0.1 cL",
          "1 cL",
          "10 cL",
          "100 cL"
        ],
        "correct_option": "0.1 cL",
        "explanation": "Il y a 0.1 centilitre dans 1 millilitre."
      },
      {
        "id": "78_8",
        "type": "vrai-faux",
        "question": "Il y a 1 000 centilitres dans 1 litre.",
        "correct": False,
        "explanation": "Il y a 100 centilitres dans 1 litre, pas 1 000."
      }
    ]
  ],
  [
    "79",
    "Masse (kg/g/mg)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "79_1",
        "type": "qcm",
        "question": "Combien de grammes y a-t-il dans 1 kilogramme ?",
        "options": [
          "10 g",
          "100 g",
          "1 000 g",
          "10 000 g"
        ],
        "correct_option": "1 000 g",
        "explanation": "Il y a 1 000 grammes dans 1 kilogramme."
      },
      {
        "id": "79_2",
        "type": "vrai-faux",
        "question": "Il y a 1 000 milligrammes dans 1 gramme.",
        "correct": True,
        "explanation": "Il y a 1 000 milligrammes dans 1 gramme."
      },
      {
        "id": "79_3",
        "type": "qcm",
        "question": "Combien de milligrammes y a-t-il dans 1 kilogramme ?",
        "options": [
          "10 mg",
          "100 mg",
          "1 000 mg",
          "1 000 000 mg"
        ],
        "correct_option": "1 000 000 mg",
        "explanation": "Il y a 1 000 000 milligrammes dans 1 kilogramme."
      },
      {
        "id": "79_4",
        "type": "vrai-faux",
        "question": "Il y a 100 grammes dans 1 kilogramme.",
        "correct": False,
        "explanation": "Il y a 1 000 grammes dans 1 kilogramme, pas 100."
      },
      {
        "id": "79_5",
        "type": "qcm",
        "question": "Combien de grammes y a-t-il dans 500 milligrammes ?",
        "options": [
          "0.5 g",
          "5 g",
          "50 g",
          "500 g"
        ],
        "correct_option": "0.5 g",
        "explanation": "Il y a 0.5 gramme dans 500 milligrammes."
      },
      {
        "id": "79_6",
        "type": "vrai-faux",
        "question": "Il y a 10 milligrammes dans 1 gramme.",
        "correct": False,
        "explanation": "Il y a 1 000 milligrammes dans 1 gramme, pas 10."
      },
      {
        "id": "79_7",
        "type": "qcm",
        "question": "Combien de kilogrammes y a-t-il dans 2 000 grammes ?",
        "options": [
          "0.2 kg",
          "2 kg",
          "20 kg",
          "200 kg"
        ],
        "correct_option": "2 kg",
        "explanation": "Il y a 2 kilogrammes dans 2 000 grammes."
      },
      {
        "id": "79_8",
        "type": "vrai-faux",
        "question": "Il y a 1 000 grammes dans 1 kilogramme.",
        "correct": True,
        "explanation": "Il y a 1 000 grammes dans 1 kilogramme."
      }
    ]
  ],
  [
    "80",
    "Longueur (km/m/cm/mm)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "80_1",
        "type": "qcm",
        "question": "Combien de mètres y a-t-il dans 1 kilomètre ?",
        "options": [
          "10 m",
          "100 m",
          "1 000 m",
          "10 000 m"
        ],
        "correct_option": "1 000 m",
        "explanation": "Il y a 1 000 mètres dans 1 kilomètre."
      },
      {
        "id": "80_2",
        "type": "vrai-faux",
        "question": "Il y a 100 centimètres dans 1 mètre.",
        "correct": True,
        "explanation": "Il y a 100 centimètres dans 1 mètre."
      },
      {
        "id": "80_3",
        "type": "qcm",
        "question": "Combien de millimètres y a-t-il dans 1 centimètre ?",
        "options": [
          "10 mm",
          "100 mm",
          "1 000 mm",
          "10 000 mm"
        ],
        "correct_option": "10 mm",
        "explanation": "Il y a 10 millimètres dans 1 centimètre."
      },
      {
        "id": "80_4",
        "type": "vrai-faux",
        "question": "Il y a 1 000 millimètres dans 1 mètre.",
        "correct": True,
        "explanation": "Il y a 1 000 millimètres dans 1 mètre."
      },
      {
        "id": "80_5",
        "type": "qcm",
        "question": "Combien de centimètres y a-t-il dans 1 kilomètre ?",
        "options": [
          "100 cm",
          "1 000 cm",
          "10 000 cm",
          "100 000 cm"
        ],
        "correct_option": "100 000 cm",
        "explanation": "Il y a 100 000 centimètres dans 1 kilomètre."
      },
      {
        "id": "80_6",
        "type": "vrai-faux",
        "question": "Il y a 10 millimètres dans 1 centimètre.",
        "correct": True,
        "explanation": "Il y a 10 millimètres dans 1 centimètre."
      },
      {
        "id": "80_7",
        "type": "qcm",
        "question": "Combien de mètres y a-t-il dans 500 millimètres ?",
        "options": [
          "0.5 m",
          "5 m",
          "50 m",
          "500 m"
        ],
        "correct_option": "0.5 m",
        "explanation": "Il y a 0.5 mètre dans 500 millimètres."
      },
      {
        "id": "80_8",
        "type": "vrai-faux",
        "question": "Il y a 1 000 mètres dans 1 kilomètre.",
        "correct": True,
        "explanation": "Il y a 1 000 mètres dans 1 kilomètre."
      }
    ]
  ],
  [
    "81",
    "Problèmes de proportionnalité",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "81_1",
        "type": "qcm",
        "question": "Si 3 kg de pommes coûtent 6 euros, combien coûteront 5 kg de pommes ?",
        "options": [
          "8 euros",
          "10 euros",
          "12 euros",
          "15 euros"
        ],
        "correct_option": "10 euros",
        "explanation": "Si 3 kg de pommes coûtent 6 euros, le prix par kg est de 6 euros / 3 kg = 2 euros/kg. Donc, 5 kg de pommes coûteront 5 kg x 2 euros/kg = 10 euros."
      },
      {
        "id": "81_2",
        "type": "vrai-faux",
        "question": "Si 4 litres de peinture couvrent 20 m², alors 10 litres de peinture couvriront 50 m².",
        "correct": True,
        "explanation": "Si 4 litres de peinture couvrent 20 m², le taux de couverture est de 20 m² / 4 litres = 5 m²/litre. Donc, 10 litres de peinture couvriront 10 litres x 5 m²/litre = 50 m²."
      },
      {
        "id": "81_3",
        "type": "qcm",
        "question": "Si 5 heures de travail permettent de construire 10 chaises, combien de chaises pourront être construites en 15 heures ?",
        "options": [
          "20 chaises",
          "25 chaises",
          "30 chaises",
          "35 chaises"
        ],
        "correct_option": "30 chaises",
        "explanation": "Si 5 heures de travail permettent de construire 10 chaises, le taux de production est de 10 chaises / 5 heures = 2 chaises/heure. Donc, en 15 heures, on pourra construire 15 heures x 2 chaises/heure = 30 chaises."
      },
      {
        "id": "81_4",
        "type": "vrai-faux",
        "question": "Si 2 kg de pommes coûtent 4 euros, alors 5 kg de pommes coûteront 10 euros.",
        "correct": True,
        "explanation": "Si 2 kg de pommes coûtent 4 euros, le prix par kg est de 4 euros / 2 kg = 2 euros/kg. Donc, 5 kg de pommes coûteront 5 kg x 2 euros/kg = 10 euros."
      },
      {
        "id": "81_5",
        "type": "qcm",
        "question": "Si 6 litres de peinture couvrent 30 m², alors 12 litres de peinture couvriront combien de m² ?",
        "options": [
          "50 m²",
          "60 m²",
          "70 m²",
          "80 m²"
        ],
        "correct_option": "60 m²",
        "explanation": "Si 6 litres de peinture couvrent 30 m², le taux de couverture est de 30 m² / 6 litres = 5 m²/litre. Donc, 12 litres de peinture couvriront 12 litres x 5 m²/litre = 60 m²."
      },
      {
        "id": "81_6",
        "type": "vrai-faux",
        "question": "Si 3 heures de travail permettent de construire 6 chaises, alors en 9 heures on pourra construire 18 chaises.",
        "correct": True,
        "explanation": "Si 3 heures de travail permettent de construire 6 chaises, le taux de production est de 6 chaises / 3 heures = 2 chaises/heure. Donc, en 9 heures, on pourra construire 9 heures x 2 chaises/heure = 18 chaises."
      },
      {
        "id": "81_7",
        "type": "qcm",
        "question": "Si 8 kg de pommes coûtent 16 euros, combien coûteront 3 kg de pommes ?",
        "options": [
          "4 euros",
          "6 euros",
          "8 euros",
          "10 euros"
        ],
        "correct_option": "6 euros",
        "explanation": "Si 8 kg de pommes coûtent 16 euros, le prix par kg est de 16 euros / 8 kg = 2 euros/kg. Donc, 3 kg de pommes coûteront 3 kg x 2 euros/kg = 6 euros."
      },
      {
        "id": "81_8",
        "type": "vrai-faux",
        "question": "Si 10 litres de peinture couvrent 50 m², alors 5 litres de peinture couvriront 25 m².",
        "correct": True,
        "explanation": "Si 10 litres de peinture couvrent 50 m², le taux de couverture est de 50 m² / 10 litres = 5 m²/litre. Donc, 5 litres de peinture couvriront 5 litres x 5 m²/litre = 25 m²."
      }
    ]
  ],
  [
    "82",
    "Somme angles triangle",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "82_1",
        "type": "qcm",
        "question": "Quel est la somme des angles d'un triangle ?",
        "options": [
          "90°",
          "180°",
          "270°",
          "360°"
        ],
        "correct_option": "180°",
        "explanation": "La somme des angles d'un triangle est toujours égale à 180°."
      },
      {
        "id": "82_2",
        "type": "vrai-faux",
        "question": "La somme des angles d'un triangle est toujours égale à 180°.",
        "correct": True,
        "explanation": "La somme des angles d'un triangle est toujours égale à 180°."
      },
      {
        "id": "82_3",
        "type": "qcm",
        "question": "Si un triangle a un angle de 90° et un angle de 45°, quel est la mesure du troisième angle ?",
        "options": [
          "45°",
          "60°",
          "90°",
          "135°"
        ],
        "correct_option": "45°",
        "explanation": "La somme des angles d'un triangle est de 180°. Si un triangle a un angle de 90° et un angle de 45°, alors le troisième angle mesure 180° - 90° - 45° = 45°."
      },
      {
        "id": "82_4",
        "type": "vrai-faux",
        "question": "Si un triangle a deux angles de 60°, alors le troisième angle mesure 60°.",
        "correct": True,
        "explanation": "La somme des angles d'un triangle est de 180°. Si un triangle a deux angles de 60°, alors le troisième angle mesure 180° - 60° - 60° = 60°."
      },
      {
        "id": "82_5",
        "type": "qcm",
        "question": "Si un triangle a un angle de 120° et un angle de 30°, quel est la mesure du troisième angle ?",
        "options": [
          "30°",
          "40°",
          "50°",
          "60°"
        ],
        "correct_option": "30°",
        "explanation": "La somme des angles d'un triangle est de 180°. Si un triangle a un angle de 120° et un angle de 30°, alors le troisième angle mesure 180° - 120° - 30° = 30°."
      },
      {
        "id": "0082_6",
        "type": "vrai-faux",
        "question": "Si un triangle a trois angles égaux, alors chaque angle mesure 60°.",
        "correct": True,
        "explanation": "La somme des angles d'un triangle est de 180°. Si un triangle a trois angles égaux, alors chaque angle mesure 180° / 3 = 60°."
      },
      {
        "id": "82_7",
        "type": "qcm",
        "question": "Si un triangle a un angle de 70° et un angle de 50°, quel est la mesure du troisième angle ?",
        "options": [
          "60°",
          "70°",
          "80°",
          "90°"
        ],
        "correct_option": "60°",
        "explanation": "La somme des angles d'un triangle est de 180°. Si un triangle a un angle de 70° et un angle de 50°, alors le troisième angle mesure 180° - 70° - 50° = 60°."
      },
      {
        "id": "82_8",
        "type": "vrai-faux",
        "question": "Si un triangle a un angle de 90° et un angle de 60°, alors le troisième angle mesure 30°.",
        "correct": True,
        "explanation": "La somme des angles d'un triangle est de 180°. Si un triangle a un angle de 90° et un angle de 60°, alors le troisième angle mesure 180° - 90° - 60° = 30°."
      }
    ]
  ],
  [
    "83",
    "Angles complémentaires et supplémentaires",
    "Mathématiques",
    "6eme",
    [
      {
        "id": "83_1",
        "type": "qcm",
        "question": "Deux angles sont complémentaires si leur somme vaut :",
        "options": [
          "90°",
          "180°",
          "270°",
          "360°"
        ],
        "correct_option": "90°",
        "explanation": "Deux angles complémentaires ont une somme égale à 90°."
      },
      {
        "id": "83_2",
        "type": "vrai-faux",
        "question": "Deux angles supplémentaires ont une somme égale à 180°.",
        "correct": True,
        "explanation": "Par définition, deux angles supplémentaires totalisent 180°."
      },
      {
        "id": "83_3",
        "type": "qcm",
        "question": "Si un angle mesure 25°, son complémentaire mesure :",
        "options": [
          "55°",
          "65°",
          "145°",
          "155°"
        ],
        "correct_option": "65°",
        "explanation": "L'angle complémentaire vaut 90° - 25° = 65°."
      },
      {
        "id": "83_4",
        "type": "vrai-faux",
        "question": "L'angle supplémentaire d'un angle de 130° mesure 50°.",
        "correct": True,
        "explanation": "L'angle supplémentaire vaut 180° - 130° = 50°."
      },
      {
        "id": "83_5",
        "type": "qcm",
        "question": "Quel est le supplémentaire d'un angle de 72° ?",
        "options": [
          "18°",
          "72°",
          "108°",
          "128°"
        ],
        "correct_option": "108°",
        "explanation": "Le supplémentaire vaut 180° - 72° = 108°."
      },
      {
        "id": "83_6",
        "type": "vrai-faux",
        "question": "Deux angles de 40° et 50° sont complémentaires.",
        "correct": True,
        "explanation": "40° + 50° = 90°, donc ils sont complémentaires."
      },
      {
        "id": "83_7",
        "type": "qcm",
        "question": "Deux angles de 95° et 85° sont :",
        "options": [
          "Complémentaires",
          "Supplémentaires",
          "Égaux",
          "Opposés"
        ],
        "correct_option": "Supplémentaires",
        "explanation": "95° + 85° = 180°, ce sont donc des angles supplémentaires."
      },
      {
        "id": "83_8",
        "type": "vrai-faux",
        "question": "Si deux angles ont une somme de 90°, alors ils sont supplémentaires.",
        "correct": False,
        "explanation": "Une somme de 90° correspond à des angles complémentaires, pas supplémentaires."
      }
    ]
  ],
  [
    "84",
    "Angles triangle",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "84_1",
        "type": "qcm",
        "question": "Quel est le type de triangle qui a un angle droit ?",
        "options": [
          "Triangle équilatéral",
          "Triangle isocèle",
          "Triangle scalène",
          "Triangle rectangle"
        ],
        "correct_option": "Triangle rectangle",
        "explanation": "Un triangle qui a un angle droit est appelé un triangle rectangle."
      },
      {
        "id": "84_2",
        "type": "vrai-faux",
        "question": "Un triangle qui a un angle droit est appelé un triangle rectangle.",
        "correct": True,
        "explanation": "Un triangle qui a un angle droit est appelé un triangle rectangle."
      },
      {
        "id": "84_3",
        "type": "qcm",
        "question": "Quel est le type de triangle qui a tous les angles égaux ?",
        "options": [
          "Triangle équilatéral",
          "Triangle isocèle",
          "Triangle scalène",
          "Triangle rectangle"
        ],
        "correct_option": "Triangle équilatéral",
        "explanation": "Un triangle qui a tous les angles égaux est appelé un triangle équilatéral."
      },
      {
        "id": "84_4",
        "type": "vrai-faux",
        "question": "Un triangle qui a tous les angles égaux est appelé un triangle équilatéral.",
        "correct": True,
        "explanation": "Un triangle qui a tous les angles égaux est appelé un triangle équilatéral."
      },
      {
        "id": "84_5",
        "type": "qcm",
        "question": "Quel est le type de triangle qui a deux angles égaux ?",
        "options": [
          "Triangle équilatéral",
          "Triangle isocèle",
          "Triangle scalène",
          "Triangle rectangle"
        ],
        "correct_option": "Triangle isocèle",
        "explanation": "Un triangle qui a deux angles égaux est appelé un triangle isocèle."
      },
      {
        "id": "84_6",
        "type": "vrai-faux",
        "question": "Un triangle qui a deux angles égaux est appelé un triangle isocèle.",
        "correct": True,
        "explanation": "Un triangle qui a deux angles égaux est appelé un triangle isocèle."
      },
      {
        "id": "84_7",
        "type": "qcm",
        "question": "Quel est le type de triangle qui n'a aucun angle égal ?",
        "options": [
          "Triangle équilatéral",
          "Triangle isocèle",
          "Triangle scalène",
          "Triangle rectangle"
        ],
        "correct_option": "Triangle scalène",
        "explanation": "Un triangle qui n'a aucun angle égal est appelé un triangle scalène."
      },
      {
        "id": "84_8",
        "type": "vrai-faux",
        "question": "Un triangle qui n'a aucun angle égal est appelé un triangle scalène.",
        "correct": True,
        "explanation": "Un triangle qui n'a aucun angle égal est appelé un triangle scalène."
      }
    ]
  ],
  [
    "85",
    "Méso-espace (cour d’école)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "85_1",
        "type": "qcm",
        "question": "Quel est le périmètre d'une cour d'école rectangulaire de 20 mètres de long et 10 mètres de large ?",
        "options": [
          "30 mètres",
          "40 mètres",
          "50 mètres",
          "60 mètres"
        ],
        "correct_option": "60 mètres",
        "explanation": "Le périmètre d'un rectangle est calculé en additionnant la longueur et la largeur, puis en multipliant par 2. Donc, le périmètre de la cour d'école est (20 m + 10 m) x 2 = 60 mètres."
      },
      {
        "id": "85_2",
        "type": "vrai-faux",
        "question": "Le périmètre d'une cour d'école rectangulaire de 15 mètres de long et 5 mètres de large est de 40 mètres.",
        "correct": False,
        "explanation": "Le périmètre d'un rectangle est calculé en additionnant la longueur et la largeur, puis en multipliant par 2. Donc, le périmètre de la cour d'école est (15 m + 5 m) x 2 = 40 mètres, pas 30 mètres."
      },
      {
        "id": "85_3",
        "type": "qcm",
        "question": "Quel est le périmètre d'une cour d'école rectangulaire de 25 mètres de long et 15 mètres de large ?",
        "options": [
          "70 mètres",
          "80 mètres",
          "90 mètres",
          "100 mètres"
        ],
        "correct_option": "80 mètres",
        "explanation": "Le périmètre d'un rectangle est calculé en additionnant la longueur et la largeur, puis en multipliant par 2. Donc, le périmètre de la cour d'école est (25 m + 15 m) x 2 = 80 mètres."
      },
      {
        "id": "85_4",
        "type": "vrai-faux",
        "question": "Le périmètre d'une cour d'école rectangulaire de 10 mètres de long et 5 mètres de large est de 30 mètres.",
        "correct": True,
        "explanation": "Le périmètre d'un rectangle est calculé en additionnant la longueur et la largeur, puis en multipliant par 2. Donc, le périmètre de la cour d'école est (10 m + 5 m) x 2 = 30 mètres."
      },
      {
        "id": "85_5",
        "type": "qcm",
        "question": "Quel est le périmètre d'une cour d'école rectangulaire de 30 mètres de long et 20 mètres de large ?",
        "options": [
          "90 mètres",
          "100 mètres",
          "110 mètres",
          "120 mètres"
        ],
        "correct_option": "100 mètres",
        "explanation": "Le périmètre d'un rectangle est calculé en additionnant la longueur et la largeur, puis en multipliant par 2. Donc, le périmètre de la cour d'école est (30 m + 20 m) x 2 = 100 mètres."
      },
      {
        "id": "85_6",
        "type": "vrai-faux",
        "question": "Le périmètre d'une cour d'école rectangulaire de 20 mètres de long et 10 mètres de large est de 50 mètres.",
        "correct": False,
        "explanation": "Le périmètre d'un rectangle est calculé en additionnant la longueur et la largeur, puis en multipliant par 2. Donc, le périmètre de la cour d'école est (20 m + 10 m) x 2 = 60 mètres, pas 50 mètres."
      },
      {
        "id": "85_7",
        "type": "qcm",
        "question": "Quel est le périmètre d'une cour d'école rectangulaire de 40 mètres de long et 25 mètres de large ?",
        "options": [
          "120 mètres",
          "130 mètres",
          "140 mètres",
          "150 mètres"
        ],
        "correct_option": "130 mètres",
        "explanation": "Le périmètre d'un rectangle est calculé en additionnant la longueur et la largeur, puis en multipliant par 2. Donc, le périmètre de la cour d'école est (40 m + 25 m) x 2 = 130 mètres."
      },
      {
        "id": "85_8",
        "type": "vrai-faux",
        "question": "Le périmètre d'une cour d'école rectangulaire de 15 mètres de long et 10 mètres de large est de 50 mètres.",
        "correct": False,
        "explanation": "Le périmètre d'un rectangle est calculé en additionnant la longueur et la largeur, puis en multipliant par 2. Donc, le périmètre de la cour d'école est (15 m + 10 m) x 2 = 50 mètres."
      }
    ]
  ],
  [
    "86",
    "Vues de face/côté/dessus",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "86_1",
        "type": "qcm",
        "question": "Quelle est la vue de face d'un cube ?",
        "options": [
          "Un carré",
          "Un rectangle",
          "Un cercle",
          "Un triangle"
        ],
        "correct_option": "Un carré",
        "explanation": "La vue de face d'un cube est un carré."
      },
      {
        "id": "86_2",
        "type": "vrai-faux",
        "question": "La vue de côté d'un cube est un rectangle.",
        "correct": False,
        "explanation": "La vue de côté d'un cube est un carré, pas un rectangle."
      },
      {
        "id": " 86_3",
        "type": "qcm",
        "question": "Quelle est la vue de dessus d'un cube ?",
        "options": [
          "Un carré",
          "Un rectangle",
          "Un cercle",
          "Un triangle"
        ],
        "correct_option": "Un carré",
        "explanation": "La vue de dessus d'un cube est un carré."
      },
      {
        "id": "86_4",
        "type": "vrai-faux",
        "question": "La vue de face d'un cube est un cercle.",
        "correct": False,
        "explanation": "La vue de face d'un cube est un carré, pas un cercle."
      },
      {
        "id": "86_5",
        "type": "qcm",
        "question": "Quelle est la vue de côté d'un cube ?",
        "options": [
          "Un carré",
          "Un rectangle",
          "Un cercle",
          "Un triangle"
        ],
        "correct_option": "Un carré",
        "explanation": "La vue de côté d'un cube est un carré."
      },
      {
        "id": "86_6",
        "type": "vrai-faux",
        "question": "La vue de dessus d'un cube est un triangle.",
        "correct": False,
        "explanation": "La vue de dessus d'un cube est un carré, pas un triangle."
      },
      {
        "id": "86_7",
        "type": "qcm",
        "question": "Quelle est la vue de face d'un cube ? (variante 2)",
        "options": [
          "Un carré",
          "Un rectangle",
          "Un cercle",
          "Un triangle"
        ],
        "correct_option": "Un carré",
        "explanation": "La vue de face d'un cube est un carré."
      },
      {
        "id": "86_8",
        "type": "vrai-faux",
        "question": "La vue de côté d'un cube est un cercle.",
        "correct": False,
        "explanation": "La vue de côté d'un cube est un carré, pas un cercle."
      }
    ]
  ],
  [
    "87",
    "Patrons figures 3D simples",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "87_1",
        "type": "qcm",
        "question": "Quel est le patron d'un cube ?",
        "options": [
          "Un carré",
          "Un rectangle",
          "Un cercle",
          "Un triangle"
        ],
        "correct_option": "Un carré",
        "explanation": "Le patron d'un cube est un carré."
      },
      {
        "id": "87_2",
        "type": "vrai-faux",
        "question": "Le patron d'un cube est un rectangle.",
        "correct": False,
        "explanation": "Le patron d'un cube est un carré, pas un rectangle."
      },
      {
        "id": "87_3",
        "type": "qcm",
        "question": "Quel est le patron d'un cylindre ?",
        "options": [
          "Un cercle et un rectangle",
          "Un carré et un triangle",
          "Un rectangle et un triangle",
          "Un cercle et un triangle"
        ],
        "correct_option": "Un cercle et un rectangle",
        "explanation": "Le patron d'un cylindre est composé d'un cercle (la base) et d'un rectangle (la surface latérale)."
      },
      {
        "id": "87_4",
        "type": "vrai-faux",
        "question": "Le patron d'un cube est un cercle.",
        "correct": False,
        "explanation": "Le patron d'un cube est un carré, pas un cercle."
      },
      {
        "id": "87_5",
        "type": "qcm",
        "question": "Quel est le patron d'une pyramide à base carrée ?",
        "options": [
          "Un carré et quatre triangles",
          "Un rectangle et quatre triangles",
          "Un cercle et quatre triangles",
          "Un triangle et quatre rectangles"
        ],
        "correct_option": "Un carré et quatre triangles",
        "explanation": "Le patron d'une pyramide à base carrée est composé d'un carré (la base) et de quatre triangles (les faces latérales)."
      },
      {
        "id": "87_6",
        "type": "vrai-faux",
        "question": "Le patron d'un cylindre est composé de deux cercles et d'un rectangle.",
        "correct": False,
        "explanation": "Le patron d'un cylindre est composé d'un cercle (la base) et d'un rectangle (la surface latérale), pas de deux cercles."
      },
      {
        "id": "87_7",
        "type": "qcm",
        "question": "Quel est le patron d'une sphère ?",
        "options": [
          "Un cercle",
          "Un carré",
          "Un rectangle",
          "Une figure complexe"
        ],
        "correct_option": "Une figure complexe",
        "explanation": "Le patron d'une sphère n'est pas une figure simple comme un cercle ou un carré, c'est une figure complexe qui ne peut pas être représentée par une forme géométrique simple."
      },
      {
        "id": "87_8",
        "type": "vrai-faux",
        "question": "Le patron d'une pyramide à base carrée est composé d'un carré et de quatre triangles.",
        "correct": True,
        "explanation": "Le patron d'une pyramide à base carrée est composé d'un carré (la base) et de quatre triangles (les faces latérales)."
      }
    ]
  ],
  [
    "88",
    "calcul mentale",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "88_1",
        "type": "qcm",
        "question": "Quel est le résultat de 25 + 17 ?",
        "options": [
          "40",
          "42",
          "44",
          "45"
        ],
        "correct_option": "42",
        "explanation": "25 + 17 = 42."
      },
      {
        "id": "88_2",
        "type": "vrai-faux",
        "question": "Le résultat de 51 - 23 est égal à 27.",
        "correct": False,
        "explanation": "51 - 23 = 28, donc l'affirmation est fausse."
      },
      {
        "id": "88_3",
        "type": "qcm",
        "question": "Quel est le résultat de 6 x 7 ?",
        "options": [
          "40",
          "42",
          "44",
          "45"
        ],
        "correct_option": "42",
        "explanation": "6 x 7 = 42."
      },
      {
        "id": "88_4",
        "type": "vrai-faux",
        "question": "Le résultat de 100 ÷ 4 est égal à 25.",
        "correct": True,
        "explanation": "100 ÷ 4 = 25, donc l'affirmation est vraie."
      },
      {
        "id": "88_5",
        "type": "qcm",
        "question": "Quel est le résultat de 15 + 28 ?",
        "options": [
          "40",
          "42",
          "43",
          "45"
        ],
        "correct_option": "43",
        "explanation": "15 + 28 = 43."
      },
      {
        "id": "88_6",
        "type": "vrai-faux",
        "question": "Le résultat de 72 - 19 est égal à 53.",
        "correct": True,
        "explanation": "72 - 19 = 53, donc l'affirmation est vraie."
      },
      {
        "id": "88_7",
        "type": "qcm",
        "question": "Quel est le résultat de 9 x 8 ?",
        "options": [
          "70",
          "72",
          "74",
          "75"
        ],
        "correct_option": "72",
        "explanation": "9 x 8 = 72."
      },
      {
        "id": "88_8",
        "type": "vrai-faux",
        "question": "Le résultat de 144 ÷ 12 est égal à 12.",
        "correct": True,
        "explanation": "144 ÷ 12 = 12, donc l'affirmation est vraie."
      }
    ]
  ],
  [
    "89",
    "Espace et géométrie (symétrie, axes de symétrie, figures symétriques)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "89_1",
        "type": "qcm",
        "question": "Quelle figure est symétrique par rapport à un axe vertical ?",
        "options": [
          "Un cercle",
          "Un carré",
          "Un triangle isocèle",
          "Un rectangle"
        ],
        "correct_option": "Un triangle isocèle",
        "explanation": "Un triangle isocèle est symétrique par rapport à un axe vertical qui passe par son sommet et divise sa base en deux parties égales."
      },
      {
        "id": "89_2",
        "type": "vrai-faux",
        "question": "Un cercle est symétrique par rapport à n'importe quel axe passant par son centre.",
        "correct": True,
        "explanation": "Un cercle est symétrique par rapport à n'importe quel axe passant par son centre, car il a une symétrie circulaire."
      },
      {
        "id": "89_3",
        "type": "qcm",
        "question": "Quelle figure est symétrique par rapport à un axe horizontal ?",
        "options": [
          "Un triangle équilatéral",
          "Un carré",
          "Un rectangle",
          "Un triangle scalène"
        ],
        "correct_option": "Un rectangle",
        "explanation": "Un rectangle est symétrique par rapport à un axe horizontal qui passe par son centre et divise sa hauteur en deux parties égales."
      },
      {
        "id": "89_4",
        "type": "vrai-faux",
        "question": "Un carré est symétrique par rapport à deux axes de symétrie perpendiculaires.",
        "correct": True,
        "explanation": "Un carré est symétrique par rapport à deux axes de symétrie perpendiculaires qui passent par son centre, l'un horizontal et l'autre vertical."
      },
      {
        "id": "89_5",
        "type": "qcm",
        "question": "Quelle figure n'est pas symétrique ?",
        "options": [
          "Un triangle scalène",
          "Un cercle",
          "Un carré",
          "Un rectangle"
        ],
        "correct_option": "Un triangle scalène",
        "explanation": "Un triangle scalène n'est pas symétrique car il n'a aucun côté ni angle égal, contrairement aux autres figures qui ont des axes de symétrie."
      },
      {
        "id": "89_6",
        "type": "vrai-faux",
        "question": "Un rectangle est symétrique par rapport à un axe diagonal.",
        "correct": False,
        "explanation": "Un rectangle n'est pas symétrique par rapport à un axe diagonal, car les diagonales d'un rectangle ne sont pas des axes de symétrie."
      },
      {
        "id": "89_7",
        "type": "qcm",
        "question": "Quelle figure est symétrique par rapport à une diagonale ?",
        "options": [
          "Un carré",
          "Un rectangle",
          "Un triangle équilatéral",
          "Un cercle"
        ],
        "correct_option": "Un carré",
        "explanation": "Un carré est symétrique par rapport à ses diagonales, car elles divisent le carré en deux parties égales."
      },
      {
        "id": "89_8",
        "type": "vrai-faux",
        "question": "Un triangle équilatéral est symétrique par rapport à trois axes de symétrie.",
        "correct": True,
        "explanation": "Un triangle équilatéral est symétrique par rapport à trois axes de symétrie qui passent par chaque sommet et le milieu du côté opposé."
      }
    ]
  ],
  [
    "90",
    "Espace et géométrie (symétrie centrale, figures à symétrie centrale)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "90_1",
        "type": "qcm",
        "question": "Quelle figure a une symétrie centrale ?",
        "options": [
          "Un cercle",
          "Un carré",
          "Un triangle isocèle",
          "Un rectangle"
        ],
        "correct_option": "Un cercle",
        "explanation": "Un cercle a une symétrie centrale, car il est identique à lui-même lorsqu'il est tourné de 180 degrés autour de son centre."
      },
      {
        "id": "90_2",
        "type": "vrai-faux",
        "question": "Un carré a une symétrie centrale.",
        "correct": False,
        "explanation": "Un carré n'a pas de symétrie centrale, car il n'est pas identique à lui-même lorsqu'il est tourné de 180 degrés autour de son centre."
      },
      {
        "id": "90_3",
        "type": "qcm",
        "question": "Quelle figure n'a pas de symétrie centrale ?",
        "options": [
          "Un triangle équilatéral",
          "Un cercle",
          "Un rectangle",
          "Un triangle scalène"
        ],
        "correct_option": "Un triangle scalène",
        "explanation": "Un triangle scalène n'a pas de symétrie centrale, car il n'est pas identique à lui-même lorsqu'il est tourné de 180 degrés autour de son centre."
      },
      {
        "id": "90_4",
        "type": "vrai-faux",
        "question": "Un rectangle a une symétrie centrale.",
        "correct": False,
        "explanation": "Un rectangle n'a pas de symétrie centrale, car il n'est pas identique à lui-même lorsqu'il est tourné de 180 degrés autour de son centre."
      },
      {
        "id": "90_5",
        "type": "qcm",
        "question": "Quelle figure a une symétrie centrale ? (variante 2)",
        "options": [
          "Un triangle isocèle",
          "Un cercle",
          "Un carré",
          "Un rectangle"
        ],
        "correct_option": "Un cercle",
        "explanation": "Un cercle a une symétrie centrale, car il est identique à lui-même lorsqu'il est tourné de 180 degrés autour de son centre."
      },
      {
        "id": "90_6",
        "type": "vrai-faux",
        "question": "Un triangle isocèle a une symétrie centrale.",
        "correct": False,
        "explanation": "Un triangle isocèle n'a pas de symétrie centrale, car il n'est pas identique à lui-même lorsqu'il est tourné de 180 degrés autour de son centre."
      },
      {
        "id": "90_7",
        "type": "qcm",
        "question": "Quelle figure a une symétrie centrale ? (variante 3)",
        "options": [
          "Un triangle équilatéral",
          "Un cercle",
          "Un rectangle",
          "Un triangle scalène"
        ],
        "correct_option": "Un cercle",
        "explanation": "Un cercle a une symétrie centrale, car il est identique à lui-même lorsqu'il est tourné de 180 degrés autour de son centre."
      },
      {
        "id": "90_8",
        "type": "vrai-faux",
        "question": "Un triangle équilatéral a une symétrie centrale.",
        "correct": False,
        "explanation": "Un triangle équilatéral n'a pas de symétrie centrale, car il n'est pas identique à lui-même lorsqu'il est tourné de 180 degrés autour de son centre."
      }
    ]
  ],
  [
    "91",
    "Proportionnalité (propriétés de la proportionnalité, tableaux de proportionnalité, situations de proportionnalité)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "91_1",
        "type": "qcm",
        "question": "Si 4 kg de pommes coûtent 8 euros, combien coûteront 10 kg de pommes ?",
        "options": [
          "16 euros",
          "20 euros",
          "24 euros",
          "30 euros"
        ],
        "correct_option": "20 euros",
        "explanation": "Si 4 kg de pommes coûtent 8 euros, le prix par kg est de 8 euros / 4 kg = 2 euros/kg. Donc, 10 kg de pommes coûteront 10 kg x 2 euros/kg = 20 euros."
      },
      {
        "id": "91_2",
        "type": "vrai-faux",
        "question": "Si 5 litres de peinture couvrent 25 m², alors 10 litres de peinture couvriront 50 m².",
        "correct": True,
        "explanation": "Si 5 litres de peinture couvrent 25 m², le taux de couverture est de 25 m² / 5 litres = 5 m²/litre. Donc, 10 litres de peinture couvriront 10 litres x 5 m²/litre = 50 m²."
      },
      {
        "id": "91_3",
        "type": "qcm",
        "question": "Si un train parcourt 150 km en 3 heures, quelle distance parcourra-t-il en 5 heures ?",
        "options": [
          "200 km",
          "250 km",
          "300 km",
          "350 km"
        ],
        "correct_option": "250 km",
        "explanation": "Si un train parcourt 150 km en 3 heures, sa vitesse est de 150 km / 3 h = 50 km/h. Donc, en 5 heures, il parcourra une distance de 50 km/h x 5 h = 250 km."
      },
      {
        "id": "91_4",
        "type": "vrai-faux",
        "question": "Si un vélo parcourt 60 km en 2 heures, alors en 4 heures il parcourra 120 km.",
        "correct": True,
        "explanation": "Si un vélo parcourt 60 km en 2 heures, sa vitesse est de 60 km / 2 h = 30 km/h. Donc, en 4 heures, il parcourra une distance de 30 km/h x 4 h = 120 km."
      },
      {
        "id": "91_5",
        "type": "qcm",
        "question": "Si 3 kg de farine coûtent 6 euros, combien coûteront 7 kg de farine ?",
        "options": [
          "12 euros",
          "14 euros",
          "16 euros",
          "18 euros"
        ],
        "correct_option": "14 euros",
        "explanation": "Si 3 kg de farine coûtent 6 euros, le prix par kg est de 6 euros / 3 kg = 2 euros/kg. Donc, 7 kg de farine coûteront 7 kg x 2 euros/kg = 14 euros."
      },
      {
        "id": "91_6",
        "type": "vrai-faux",
        "question": "Si un bus parcourt 120 km en 4 heures, alors en 6 heures il parcourra 180 km.",
        "correct": True,
        "explanation": "Si un bus parcourt 120 km en 4 heures, sa vitesse est de 120 km / 4 h = 30 km/h. Donc, en 6 heures, il parcourra une distance de 30 km/h x 6 h = 180 km."
      },
      {
        "id": "91_7",
        "type": "qcm",
        "question": "Si 8 litres de jus coûtent 16 euros, combien coûteront 15 litres de jus ?",
        "options": [
          "30 euros",
          "32 euros",
          "34 euros",
          "36 euros"
        ],
        "correct_option": "30 euros",
        "explanation": "Si 8 litres de jus coûtent 16 euros, le prix par litre est de 16 euros / 8 litres = 2 euros/litre. Donc, 15 litres de jus coûteront 15 litres x 2 euros/litre = 30 euros."
      },
      {
        "id": "91_8",
        "type": "vrai-faux",
        "question": "Si un avion parcourt 600 km en 2 heures, alors en 4 heures il parcourra 1200 km.",
        "correct": True,
        "explanation": "Si un avion parcourt 600 km en 2 heures, sa vitesse est de 600 km / 2 h = 300 km/h. Donc, en 4 heures, il parcourra une distance de 300 km/h x 4 h = 1200 km."
      }
    ]
  ],
  [
    "92",
    "Organisation de données et probabilités",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "92_1",
        "type": "qcm",
        "question": "Quel est le mode de la série de données suivante : 3, 5, 7, 5, 3, 5 ?",
        "options": [
          "3",
          "5",
          "7",
          "Aucun mode"
        ],
        "correct_option": "5",
        "explanation": "Le mode est la valeur qui apparaît le plus fréquemment dans une série de données. Dans cette série, le nombre 5 apparaît trois fois, ce qui en fait le mode."
      },
      {
        "id": "92_2",
        "type": "vrai-faux",
        "question": "La médiane de la série de données suivante : 1, 3, 5, 7, 9 est 5.",
        "correct": True,
        "explanation": "La médiane est la valeur qui sépare une série de données en deux parties égales. Dans cette série, le nombre 5 est la médiane car il se trouve au milieu lorsque les données sont classées par ordre croissant."
      },
      {
        "id": "92_3",
        "type": "qcm",
        "question": "Quel est l'écart-type de la série de données suivante : 2, 4, 4, 4, 5, 5, 7, 9 ?",
        "options": [
          "1",
          "2",
          "3",
          "4"
        ],
        "correct_option": "2",
        "explanation": "L'écart-type mesure la dispersion des données par rapport à la moyenne. Pour cette série de données, l'écart-type est d'environ 2."
      },
      {
        "id": "92_4",
        "type": "vrai-faux",
        "question": "La probabilité d'obtenir un nombre pair en lançant un dé à six faces est de 1/2.",
        "correct": True,
        "explanation": "Un dé à six faces a trois nombres pairs (2, 4, 6) et trois nombres impairs (1, 3, 5). Donc, la probabilité d'obtenir un nombre pair est de 3/6 = 1/2."
      },
      {
        "id": "92_5",
        "type": "qcm",
        "question": "Quel est le mode de la série de données suivante : 10, 20, 20, 30, 40 ?",
        "options": [
          "10",
          "20",
          "30",
          "40"
        ],
        "correct_option": "20",
        "explanation": "Le mode est la valeur qui apparaît le plus fréquemment dans une série de données. Dans cette série, le nombre 20 apparaît deux fois, ce qui en fait le mode."
      },
      {
        "id": "92_6",
        "type": "vrai-faux",
        "question": "La médiane de la série de données suivante : 5, 10, 15, 20, 25 est 15.",
        "correct": True,
        "explanation": "La médiane est la valeur qui sépare une série de données en deux parties égales. Dans cette série, le nombre 15 est la médiane car il se trouve au milieu lorsque les données sont classées par ordre croissant."
      },
      {
        "id": "92_7",
        "type": "qcm",
        "question": "Quel est l'écart-type de la série de données suivante : 1, 2, 3, 4, 5 ?",
        "options": [
          "0.5",
          "1",
          "1.5",
          "2"
        ],
        "correct_option": "1",
        "explanation": "L'écart-type mesure la dispersion des données par rapport à la moyenne. Pour cette série de données, l'écart-type est d'environ 1."
      },
      {
        "id": "92_8",
        "type": "vrai-faux",
        "question": "La probabilité d'obtenir un nombre supérieur à 4 en lançant un dé à six faces est de 1/3.",
        "correct": True,
        "explanation": "Un dé à six faces a deux nombres supérieurs à 4 (5 et 6) et quatre nombres inférieurs ou égaux à 4 (1, 2, 3, 4). Donc, la probabilité d'obtenir un nombre supérieur à 4 est de 2/6 = 1/3."
      }
    ]
  ],
  [
    "93",
    "Nombres et calculs (calcul littéral, équations, inéquations)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "93_1",
        "type": "qcm",
        "question": "Quelle est la valeur de x dans l'équation 2x + 3 = 7 ?",
        "options": [
          "1",
          "2",
          "3",
          "4"
        ],
        "correct_option": "2",
        "explanation": "Pour résoudre l'équation 2x + 3 = 7, on soustrait 3 des deux côtés pour obtenir 2x = 4, puis on divise par 2 pour trouver x = 2."
      },
      {
        "id": "93_2",
        "type": "vrai-faux",
        "question": "L'inéquation x - 5 > 3 a pour solution x > 8.",
        "correct": True,
        "explanation": "Pour résoudre l'inéquation x - 5 > 3, on ajoute 5 des deux côtés pour obtenir x > 8."
      },
      {
        "id": "93_3",
        "type": "qcm",
        "question": "Quelle est la valeur de y dans l'équation 3y - 4 = 11 ?",
        "options": [
          "4",
          "5",
          "6",
          "7"
        ],
        "correct_option": "5",
        "explanation": "Pour résoudre l'équation 3y - 4 = 11, on ajoute 4 des deux côtés pour obtenir 3y = 15, puis on divise par 3 pour trouver y = 5."
      },
      {
        "id": "93_4",
        "type": "vrai-faux",
        "question": "L'inéquation 2z + 1 ≤ 9 a pour solution z ≤ 4.",
        "correct": True,
        "explanation": "Pour résoudre l'inéquation 2z + 1 ≤ 9, on soustrait 1 des deux côtés pour obtenir 2z ≤ 8, puis on divise par 2 pour trouver z ≤ 4."
      },
      {
        "id": "93_5",
        "type": "qcm",
        "question": "Quelle est la valeur de a dans l'équation a/4 + 2 = 6 ?",
        "options": [
          "12",
          "14",
          "16",
          "18"
        ],
        "correct_option": "16",
        "explanation": "Pour résoudre l'équation a/4 + 2 = 6, on soustrait 2 des deux côtés pour obtenir a/4 = 4, puis on multiplie par 4 pour trouver a = 16."
      },
      {
        "id": "93_6",
        "type": "vrai-faux",
        "question": "L'inéquation 5m - 3 < 2 a pour solution m < 1.",
        "correct": True,
        "explanation": "Pour résoudre l'inéquation 5m - 3 < 2, on ajoute 3 des deux côtés pour obtenir 5m < 5, puis on divise par 5 pour trouver m < 1."
      },
      {
        "id": "93_7",
        "type": "qcm",
        "question": "Quelle est la valeur de b dans l'équation 4b + 5 = 21 ?",
        "options": [
          "3",
          "4",
          "5",
          "6"
        ],
        "correct_option": "4",
        "explanation": "Pour résoudre l'équation 4b + 5 = 21, on soustrait 5 des deux côtés pour obtenir 4b = 16, puis on divise par 4 pour trouver b = 4."
      },
      {
        "id": "93_8",
        "type": "vrai-faux",
        "question": "L'inéquation c/2 - 1 ≥ 3 a pour solution c ≥ 8.",
        "correct": True,
        "explanation": "Pour résoudre l'inéquation c/2 - 1 ≥ 3, on ajoute 1 des deux côtés pour obtenir c/2 ≥ 4, puis on multiplie par 2 pour trouver c ≥ 8."
      }
    ]
  ],
  [
    "94",
    "Nombres et calculs (nombres entiers, décimaux, fractions, pourcentages, puissances, racines carrées, opérations sur les nombres)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "94_1",
        "type": "qcm",
        "question": "Quel est le résultat de 3/4 + 1/2 ?",
        "options": [
          "1/4",
          "1/2",
          "5/4",
          "7/4"
        ],
        "correct_option": "5/4",
        "explanation": "Pour additionner les fractions 3/4 et 1/2, on trouve un dénominateur commun qui est 4. On convertit 1/2 en 2/4, puis on additionne les numérateurs : 3/4 + 2/4 = (3 + 2)/4 = 5/4."
      },
      {
        "id": "94_2",
        "type": "vrai-faux",
        "question": "Le pourcentage de 25% de 80 est égal à 20.",
        "correct": True,
        "explanation": "Pour calculer 25% de 80, on multiplie 80 par 0.25 : 80 x 0.25 = 20."
      },
      {
        "id": "94_3",
        "type": "qcm",
        "question": "Quel est le résultat de 5^3 ?",
        "options": [
          "15",
          "25",
          "125",
          "625"
        ],
        "correct_option": "125",
        "explanation": "5^3 signifie 5 multiplié par lui-même trois fois : 5 x 5 x 5 = 125."
      },
      {
        "id": "94_4",
        "type": "vrai-faux",
        "question": "La racine carrée de 49 est égale à 7.",
        "correct": True,
        "explanation": "La racine carrée de 49 est le nombre qui, multiplié par lui-même, donne 49. Ce nombre est 7, car 7 x 7 = 49."
      },
      {
        "id": "94_5",
        "type": "qcm",
        "question": "Quel est le résultat de (2 + 3) x 4 ?",
        "options": [
          "20",
          "24",
          "28",
          "30"
        ],
        "correct_option": "20",
        "explanation": "Selon les règles de priorité des opérations, on effectue d'abord les parenthèses : (2 + 3) = 5, puis on multiplie par 4 : 5 x 4 = 20."
      },
      {
        "id": "94_6",
        "type": "vrai-faux",
        "question": "Le résultat de 0.5 x 0.2 est égal à 0.1.",
        "correct": True,
        "explanation": "Pour multiplier les décimaux, on multiplie les nombres comme s'ils étaient entiers et on place la virgule au bon endroit : 0.5 x 0.2 = (5 x 2) / (10 x 10) = 10 / 100 = 0.1."
      },
      {
        "id": "94_7",
        "type": "qcm",
        "question": "Quel est le résultat de 10% de 200 ?",
        "options": [
          "10",
          "20",
          "30",
          "40"
        ],
        "correct_option": "20",
        "explanation": "Pour calculer 10% de 200, on multiplie 200 par 0.10 : 200 x 0.10 = 20."
      },
      {
        "id": "94_8",
        "type": "vrai-faux",
        "question": "Le résultat de 7^2 est égal à 49.",
        "correct": True,
        "explanation": "7^2 signifie 7 multiplié par lui-même : 7 x 7 = 49."
      }
    ]
  ],
  [
    "95",
    "Espace et géométrie (patrons de solides)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "95_1",
        "type": "qcm",
        "question": "Quel est le patron d'un cube ?",
        "options": [
          "Un carré",
          "Un rectangle",
          "Un cercle",
          "Une figure complexe"
        ],
        "correct_option": "Un carré",
        "explanation": "Le patron d'un cube est composé de six carrés identiques qui représentent les faces du cube."
      },
      {
        "id": "95_2",
        "type": "vrai-faux",
        "question": "Le patron d'un cube est composé de rectangles.",
        "correct": False,
        "explanation": "Le patron d'un cube est composé de six carrés identiques, pas de rectangles."
      },
      {
        "id": "95_3",
        "type": "qcm",
        "question": "Quel est le patron d'un prisme à base triangulaire ?",
        "options": [
          "Deux triangles et trois rectangles",
          "Un triangle et trois rectangles",
          "Deux triangles et deux rectangles",
          "Un triangle et deux rectangles"
        ],
        "correct_option": "Deux triangles et trois rectangles",
        "explanation": "Le patron d'un prisme à base triangulaire est composé de deux triangles (les bases) et de trois rectangles (les faces latérales)."
      },
      {
        "id": "95_4",
        "type": "vrai-faux",
        "question": "Le patron d'un prisme à base triangulaire est composé de deux triangles et trois rectangles.",
        "correct": True,
        "explanation": "Le patron d'un prisme à base triangulaire est composé de deux triangles (les bases) et de trois rectangles (les faces latérales)."
      },
      {
        "id": "95_5",
        "type": "qcm",
        "question": "Quel est le patron d'un cylindre ?",
        "options": [
          "Deux cercles et un rectangle",
          "Un cercle et un rectangle",
          "Deux cercles et deux rectangles",
          "Un cercle et deux rectangles"
        ],
        "correct_option": "Deux cercles et un rectangle",
        "explanation": "Le patron d'un cylindre est composé de deux cercles (les bases) et d'un rectangle (la face latérale qui s'enroule autour des bases)."
      },
      {
        "id": "95_6",
        "type": "vrai-faux",
        "question": "Le patron d'un cylindre est composé de deux cercles et un rectangle.",
        "correct": True,
        "explanation": "Le patron d'un cylindre est composé de deux cercles (les bases) et d'un rectangle (la face latérale qui s'enroule autour des bases)."
      },
      {
        "id": "95_7",
        "type": "qcm",
        "question": "Quel est le patron d'une pyramide à base carrée ?",
        "options": [
          "Un carré et quatre triangles",
          "Deux carrés et quatre triangles",
          "Un carré et trois triangles",
          "Deux carrés et trois triangles"
        ],
        "correct_option": "Un carré et quatre triangles",
        "explanation": "Le patron d'une pyramide à base carrée est composé d'un carré (la base) et de quatre triangles (les faces latérales)."
      },
      {
        "id": "95_8",
        "type": "vrai-faux",
        "question": "Le patron d'une pyramide à base carrée est composé d'un carré et quatre triangles.",
        "correct": True,
        "explanation": "Le patron d'une pyramide à base carrée est composé d'un carré (la base) et de quatre triangles (les faces latérales)."
      }
    ]
  ],
  [
    "96",
    "Espace et géométrie (patrons de solides)",
    "Mathématiques",
    "6ème",
    [
      {
        "id": "96_1",
        "type": "qcm",
        "question": "Quel est le patron d'un cube ?",
        "options": [
          "Un carré",
          "Un rectangle",
          "Un cercle",
          "Une figure complexe"
        ],
        "correct_option": "Un carré",
        "explanation": "Le patron d'un cube est composé de six carrés identiques qui représentent les faces du cube."
      },
      {
        "id": "96_2",
        "type": "vrai-faux",
        "question": "Le patron d'un cube est composé de rectangles.",
        "correct": False,
        "explanation": "Le patron d'un cube est composé de six carrés identiques, pas de rectangles."
      },
      {
        "id": "96_3",
        "type": "qcm",
        "question": "Quel est le patron d'un prisme à base triangulaire ?",
        "options": [
          "Deux triangles et trois rectangles",
          "Un triangle et trois rectangles",
          "Deux triangles et deux rectangles",
          "Un triangle et deux rectangles"
        ],
        "correct_option": "Deux triangles et trois rectangles",
        "explanation": "Le patron d'un prisme à base triangulaire est composé de deux triangles (les bases) et de trois rectangles (les faces latérales)."
      },
      {
        "id": "96_4",
        "type": "vrai-faux",
        "question": "Le patron d'un prisme à base triangulaire est composé de deux triangles et trois rectangles.",
        "correct": True,
        "explanation": "Le patron d'un prisme à base triangulaire est composé de deux triangles (les bases) et de trois rectangles (les faces latérales)."
      },
      {
        "id": "96_5",
        "type": "qcm",
        "question": "Quel est le patron d'un cylindre ?",
        "options": [
          "Deux cercles et un rectangle",
          "Un cercle et un rectangle",
          "Deux cercles et deux rectangles",
          "Un cercle et deux rectangles"
        ],
        "correct_option": "Deux cercles et un rectangle",
        "explanation": "Le patron d'un cylindre est composé de deux cercles (les bases) et d'un rectangle (la face latérale qui s'enroule autour des bases)."
      },
      {
        "id": "96_6",
        "type": "vrai-faux",
        "question": "Le patron d'un cylindre est composé de deux cercles et un rectangle.",
        "correct": True,
        "explanation": "Le patron d'un cylindre est composé de deux cercles (les bases) et d'un rectangle (la face latérale qui s'enroule autour des bases)."
      },
      {
        "id": "96_7",
        "type": "qcm",
        "question": "Quel est le patron d'une pyramide à base carrée ?",
        "options": [
          "Un carré et quatre triangles",
          "Deux carrés et quatre triangles",
          "Un carré et trois triangles",
          "Deux carrés et trois triangles"
        ],
        "correct_option": "Un carré et quatre triangles",
        "explanation": "Le patron d'une pyramide à base carrée est composé d'un carré (la base) et de quatre triangles (les faces latérales)."
      },
      {
        "id": "96_8",
        "type": "vrai-faux",
        "question": "Le patron d'une pyramide à base carrée est composé d'un carré et quatre triangles.",
        "correct": True,
        "explanation": "Le patron d'une pyramide à base carrée est composé d'un carré (la base) et de quatre triangles (les faces latérales)."
      }
    ]
  ]
]

def normalize_question_type(question_type):
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    return "vrai-faux"


def normalize_level_label(level):
    normalized = str(level).strip().lower()
    if normalized in {"6e", "6ème", "6eme", "sixieme"}:
        return "6eme"
    return str(level).strip()


def build_true_false_statement(question_text, fallback_answer=""):
    question_text = str(question_text).strip()
    fallback_answer = str(fallback_answer).strip().rstrip(".")
    if fallback_answer:
        return f"{question_text} La bonne réponse attendue est : {fallback_answer}."
    return question_text or "Choisis si l'affirmation est vraie ou fausse."


def make_quiz(qid, title, subject, level, questions):
    level = normalize_level_label(level)
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []
    for question in questions:
        qtype = normalize_question_type(question.get("type", ""))
        if qtype == "qcm":
            runtime_questions.append({"type": "qcm", "question": str(question.get("question", "")), "choices": list(question.get("options", []))})
        elif qtype == "vrai-faux":
            runtime_questions.append({"type": "vrai-faux", "question": str(question.get("question", ""))})
        else:
            runtime_questions.append({"type": "vrai-faux", "question": build_true_false_statement(question.get("question", ""), question.get("correct_answer", ""))})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "type": "quiz", "level": level, "subject": subject, "description": f"Diagnostic {subject} {level} : {title}", "status": "published", "created_at": created_at, "updated_at": created_at},
        "quiz": {"title": title, "type": "quiz", "level": level, "subject": subject, "question_count": len(runtime_questions), "passing_score": 70, "time_limit_minutes": 15, "questions": runtime_questions},
        "exercisenotion": [],
        "exerciseresponses": [],
    }

def make_answers(qid, title, subject, level, questions):
    level = normalize_level_label(level)
    answers = []
    for index, q in enumerate(questions):
        qtype = normalize_question_type(q.get("type", ""))
        if qtype == "qcm":
            answers.append({"index": index, "question_id": index + 1, "type": "qcm", "answer": q["correct_option"], "correction": q["explanation"]})
        else:
            answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q.get("correct", True) else "faux", "correction": q["explanation"]})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "level": level, "subject": subject},
        "quiz": {"title": title, "question_count": len(answers), "level": level, "subject": subject, "answers": answers},
    }


def write_json(path, payload):
    with open(path, "w", encoding="utf-8", newline="\n") as f:
        json.dump(payload, f, ensure_ascii=False, indent=2)
        f.write("\n")


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

if __name__ == "__main__":
    write_quiz_files()
