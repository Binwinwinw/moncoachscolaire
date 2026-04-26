#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz EMC 6e — SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "emc_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

quizzes_data = [
  [
    "97",
    "EMC 6e - Les symboles de la République",
    "EMC",
    "6eme",
    [
      {
        "id": "97_1",
        "type": "qcm",
        "question": "Quels sont les trois symboles de la République française ?",
        "options": [
          "Le drapeau tricolore, la Marseillaise, Marianne",
          "Le drapeau tricolore, la Marseillaise, le coq gaulois",
          "Marianne, le coq gaulois, la devise Liberté, Égalité, Fraternité",
          "Le drapeau tricolore, la devise Liberté, Égalité, Fraternité, Marianne"
        ],
        "correct_option": "Le drapeau tricolore, la Marseillaise, Marianne",
        "explanation": "Les trois symboles de la République française sont le drapeau tricolore (bleu, blanc, rouge), la Marseillaise (l'hymne national) et Marianne (la figure allégorique de la République)."
      },
      {
        "id": "97_2",
        "type": "vrai-faux",
        "question": "La devise de la République française est Liberté, Égalité, Fraternité.",
        "correct": True,
        "explanation": "C'est vrai. La devise de la République française est Liberté, Égalité, Fraternité."
      },
      {
        "id": "97_3",
        "type": "qcm",
        "question": "Quel est le rôle de Marianne en tant que symbole de la République ?",
        "options": [
          "Marianne représente les valeurs de la République, notamment la liberté et la raison, et incarne l'esprit de la nation française.",
          "Marianne est un symbole militaire de la République.",
          "Marianne est une figure religieuse importante en France.",
          "Marianne est un personnage fictif de la littérature française."
        ],
        "correct_option": "Marianne représente les valeurs de la République, notamment la liberté et la raison, et incarne l'esprit de la nation française.",
        "explanation": "Marianne est une figure allégorique qui symbolise les valeurs de la République française, telles que la liberté, l'égalité et la fraternité. Elle incarne également l'esprit de la nation française et est souvent représentée portant un bonnet phrygien."
      },
      {
        "id": "97_4",
        "type": "vrai-faux",
        "question": "Le drapeau tricolore français est composé de trois bandes verticales de couleurs bleu, blanc et rouge.",
        "correct": True,
        "explanation": "C'est vrai. Le drapeau tricolore français est composé de trois bandes verticales de couleurs bleu (côté mât), blanc (au centre) et rouge (côté flottant)."
      },
      {
        "id": "97_5",
        "type": "qcm",
        "question": "Quel est le symbole de la liberté en France ?",
        "options": [
          "Le drapeau tricolore",
          "La Marseillaise",
          "Marianne",
          "Le coq gaulois"
        ],
        "correct_option": "Marianne",
        "explanation": "Marianne est le symbole de la liberté en France, représentant les valeurs de la République."
      },
      {
        "id": "97_6",
        "type": "vrai-faux",
        "question": "La Marseillaise est l'hymne national de la France.",
        "correct": True,
        "explanation": "C'est vrai. La Marseillaise est l'hymne national de la France."
      },
      {
        "id": "97_7",
        "type": "qcm",
        "question": "Quel est le symbole de l'égalité en France ?",
        "options": [
          "Le drapeau tricolore",
          "La Marseillaise",
          "Marianne",
          "Le coq gaulois"
        ],
        "correct_option": "Marianne",
        "explanation": "Marianne est également le symbole de l'égalité en France, représentant les valeurs de la République."
      },
      {
        "id": "97_8",
        "type": "vrai-faux",
        "question": "Le coq gaulois est un symbole officiel de la République française.",
        "correct": False,
        "explanation": "C'est faux. Le coq gaulois est un symbole culturel et historique de la France, mais il n'est pas un symbole officiel de la République française."
      }
    ]
  ],
  [
    "98",
    "EMC 6e - Les institutions de la République",
    "EMC",
    "6eme",
    [
      {
        "id": "98_1",
        "type": "qcm",
        "question": "Quelles sont les trois principales institutions de la République française ?",
        "options": [
          "Le Président de la République, le Parlement, le Gouvernement",
          "Le Président de la République, le Conseil constitutionnel, le Conseil d'État",
          "Le Parlement, le Gouvernement, le Conseil constitutionnel",
          "Le Président de la République, le Parlement, le Conseil constitutionnel"
        ],
        "correct_option": "Le Président de la République, le Parlement, le Gouvernement",
        "explanation": "Les trois principales institutions de la République française sont le Président de la République, le Parlement (composé de l'Assemblée nationale et du Sénat) et le Gouvernement (composé du Premier ministre et des ministres)."
      },
      {
        "id": "98_2",
        "type": "vrai-faux",
        "question": "Le Président de la République est élu au suffrage universel direct pour un mandat de cinq ans.",
        "correct": True,
        "explanation": "C'est vrai. Le Président de la République française est élu au suffrage universel direct pour un mandat de cinq ans."
      },
      {
        "id": "98_3",
        "type": "qcm",
        "question": "Quel est le rôle du Parlement en France ?",
        "options": [
          "Le Parlement élabore les lois, contrôle le Gouvernement et représente les citoyens.",
          "Le Parlement est responsable de la politique étrangère de la France.",
          "Le Parlement gère les finances publiques de la France.",
          "Le Parlement est chargé de la défense nationale."
        ],
        "correct_option": "Le Parlement élabore les lois, contrôle le Gouvernement et représente les citoyens.",
        "explanation": "Le Parlement français a pour rôle d'élaborer les lois, de contrôler le Gouvernement et de représenter les citoyens. Il est composé de deux chambres : l'Assemblée nationale et le Sénat."
      },
      {
        "id": "98_4",
        "type": "vrai-faux",
        "question": "Le Gouvernement est dirigé par le Premier ministre, qui est nommé par le Président de la République.",
        "correct": True,
        "explanation": "C'est vrai. Le Gouvernement français est dirigé par le Premier ministre, qui est nommé par le Président de la République."
      },
      {
        "id": "98_5",
        "type": "qcm",
        "question": "Quel est le rôle du Président de la République en France ?",
        "options": [
          "Le Président de la République représente la France à l'étranger, nomme le Premier ministre et peut dissoudre l'Assemblée nationale.",
          "Le Président de la République élabore les lois et contrôle le Gouvernement.",
          "Le Président de la République gère les finances publiques de la France.",
          "Le Président de la République est chargé de la défense nationale."
        ],
        "correct_option": "Le Président de la République représente la France à l'étranger, nomme le Premier ministre et peut dissoudre l'Assemblée nationale.",
        "explanation": "Le Président de la République française représente la France à l'étranger, nomme le Premier ministre et peut dissoudre l'Assemblée nationale."
      },
      {
        "id": "98_6",
        "type": "vrai-faux",
        "question": "Le Conseil constitutionnel est chargé de veiller à la conformité des lois à la Constitution.",
        "correct": True,
        "explanation": "C'est vrai. Le Conseil constitutionnel français est chargé de veiller à la conformité des lois à la Constitution."
      },
      {
        "id": "98_7",
        "type": "qcm",
        "question": "Quel est le rôle du Conseil d'État en France ?",
        "options": [
          "Le Conseil d'État conseille le Gouvernement sur les questions juridiques et est la juridiction administrative suprême.",
          "Le Conseil d'État élabore les lois et contrôle le Gouvernement.",
          "Le Conseil d'État gère les finances publiques de la France.",
          "Le Conseil d'État est chargé de la défense nationale."
        ],
        "correct_option": "Le Conseil d'État conseille le Gouvernement sur les questions juridiques et est la juridiction administrative suprême.",
        "explanation": "Le Conseil d'État français conseille le Gouvernement sur les questions juridiques et est la juridiction administrative suprême."
      },
      {
        "id": "98_8",
        "type": "vrai-faux",
        "question": "Le Sénat est la chambre basse du Parlement français.",
        "correct": False,
        "explanation": "C'est faux. Le Sénat est la chambre haute du Parlement français, tandis que l'Assemblée nationale est la chambre basse."
      }
    ]
  ],
  [
    "99",
    "EMC 6e - Les droits et devoirs des citoyens",
    "EMC",
    "6eme",
    [
      {
        "id": "99_1",
        "type": "qcm",
        "question": "Quels sont les trois droits fondamentaux des citoyens français ?",
        "options": [
          "Le droit de vote, la liberté d'expression, le droit à l'éducation",
          "Le droit de vote, la liberté de religion, le droit à la santé",
          "La liberté d'expression, le droit à l'éducation, le droit à la santé",
          "Le droit de vote, la liberté d'expression, le droit à la santé"
        ],
        "correct_option": "Le droit de vote, la liberté d'expression, le droit à l'éducation",
        "explanation": "Les trois droits fondamentaux des citoyens français sont le droit de vote, la liberté d'expression et le droit à l'éducation."
      },
      {
        "id": "99_2",
        "type": "vrai-faux",
        "question": "Le droit de vote est un droit fondamental pour tous les citoyens français majeurs.",
        "correct": True,
        "explanation": "C'est vrai. Le droit de vote est un droit fondamental pour tous les citoyens français majeurs, leur permettant de participer à la vie démocratique du pays."
      },
      {
        "id": "99_3",
        "type": "qcm",
        "question": "Quels sont les devoirs des citoyens français ?",
        "options": [
          "Respecter les lois, payer des impôts, défendre la patrie",
          "Respecter les lois, payer des impôts, avoir une profession",
          "Payer des impôts, défendre la patrie, avoir une profession",
          "Respecter les lois, défendre la patrie, avoir une profession"
        ],
        "correct_option": "Respecter les lois, payer des impôts, défendre la patrie",
        "explanation": "Les devoirs des citoyens français incluent le respect des lois, le paiement des impôts et la défense de la patrie en cas de besoin."
      },
      {
        "id": "99_4",
        "type": "vrai-faux",
        "question": "La liberté d'expression permet aux citoyens français de s'exprimer librement sans aucune restriction.",
        "correct": False,
        "explanation": "C'est faux. La liberté d'expression est un droit fondamental qui permet aux citoyens français de s'exprimer librement, mais elle n'est pas absolue et peut être limitée dans certains cas (par exemple, pour protéger la sécurité nationale ou prévenir les discours haineux)."
      },
      {
        "id": "99_5",
        "type": "qcm",
        "question": "Quel est le devoir de solidarité en France ?",
        "options": [
          "Aider les personnes en difficulté, respecter les différences, participer à la vie de la communauté",
          "Aider les personnes en difficulté, respecter les différences, avoir une profession",
          "Respecter les différences, participer à la vie de la communauté, avoir une profession",
          "Aider les personnes en difficulté, participer à la vie de la communauté, avoir une profession"
        ],
        "correct_option": "Aider les personnes en difficulté, respecter les différences, participer à la vie de la communauté",
        "explanation": "Le devoir de solidarité en France implique d'aider les personnes en difficulté, de respecter les différences et de participer à la vie de la communauté pour construire une société plus juste et inclusive."
      },
      {
        "id": "99_6",
        "type": "vrai-faux",
        "question": "Le droit à l'éducation est un droit fondamental pour tous les citoyens français.",
        "correct": True,
        "explanation": "C'est vrai. Le droit à l'éducation est un droit fondamental pour tous les citoyens français, garantissant l'accès à une éducation de qualité pour tous les enfants."
      },
      {
        "id": "99_7",
        "type": "qcm",
        "question": "Quel est le devoir de respect en France ?",
        "options": [
          "Respecter les lois, respecter les différences, participer à la vie de la communauté",
          "Respecter les lois, respecter les différences, avoir une profession",
          "Respecter les différences, participer à la vie de la communauté, avoir une profession",
          "Respecter les lois, participer à la vie de la communauté, avoir une profession"
        ],
        "correct_option": "Respecter les lois, respecter les différences, participer à la vie de la communauté",
        "explanation": "Le devoir de respect en France implique de respecter les lois, de respecter les différences et de participer à la vie de la communauté pour vivre ensemble dans une société harmonieuse."
      },
      {
        "id": "99_8",
        "type": "vrai-faux",
        "question": "Le devoir de défendre la patrie est obligatoire pour tous les citoyens français.",
        "correct": False,
        "explanation": "C'est faux. Le devoir de défendre la patrie est un devoir civique important, mais il n'est pas obligatoire pour tous les citoyens français. Cependant, en cas de besoin, les citoyens peuvent être appelés à défendre la patrie, notamment à travers le service militaire ou d'autres formes de service civique."
      }
    ]
  ],
  [
    "100",
    "EMC 6e - La laïcité en France",
    "EMC",
    "6eme",
    [
      {
        "id": "100_1",
        "type": "qcm",
        "question": "Qu'est-ce que la laïcité en France ?",
        "options": [
          "La séparation des Églises et de l'État, garantissant la liberté de conscience et d'expression religieuse",
          "La promotion d'une religion d'État en France",
          "L'interdiction de toute expression religieuse en public",
          "La coexistence pacifique de toutes les religions en France"
        ],
        "correct_option": "La séparation des Églises et de l'État, garantissant la liberté de conscience et d'expression religieuse",
        "explanation": "La laïcité en France est un principe qui garantit la séparation des Églises et de l'État, assurant ainsi la liberté de conscience et d'expression religieuse pour tous les citoyens."
      },
      {
        "id": "100_2",
        "type": "vrai-faux",
        "question": "La laïcité permet à chacun de pratiquer sa religion librement, tant que cela ne perturbe pas l'ordre public.",
        "correct": True,
        "explanation": "C'est vrai. La laïcité permet à chacun de pratiquer sa religion librement, tant que cela ne perturbe pas l'ordre public ou ne porte pas atteinte aux droits d'autrui."
      },
      {
        "id": "100_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'État dans une société laïque ?",
        "options": [
          "L'État doit rester neutre vis-à-vis des religions et garantir la liberté de conscience pour tous les citoyens.",
          "L'État doit promouvoir une religion d'État pour unifier la nation.",
          "L'État doit interdire toute expression religieuse en public pour maintenir l'ordre.",
          "L'État doit favoriser le dialogue interreligieux pour promouvoir la coexistence pacifique."
        ],
        "correct_option": "L'État doit rester neutre vis-à-vis des religions et garantir la liberté de conscience pour tous les citoyens.",
        "explanation": "Dans une société laïque, l'État doit rester neutre vis-à-vis des religions et garantir la liberté de conscience pour tous les citoyens, sans favoriser ni discriminer aucune religion."
      },
      {
        "id": "100_4",
        "type": "vrai-faux",
        "question": "La laïcité implique que l'État ne favorise aucune religion.",
        "correct": True,
        "explanation": "C'est vrai. La laïcité implique que l'État reste neutre vis-à-vis des religions et ne favorise aucune d'entre elles."
      },
      {
        "id": "100_5",
        "type": "qcm",
        "question": "Quel est le symbole de la laïcité en France ?",
        "options": [
          "Le drapeau tricolore",
          "La Marseillaise",
          "Marianne",
          "Le coq gaulois"
        ],
        "correct_option": "Marianne",
        "explanation": "Marianne est souvent considérée comme un symbole de la laïcité en France, représentant les valeurs de liberté, d'égalité et de fraternité qui sont au cœur de la République française."
      },
      {
        "id": "100_6",
        "type": "vrai-faux",
        "question": "La laïcité garantit la liberté d'expression religieuse pour tous les citoyens français.",
        "correct": True,
        "explanation": "C'est vrai. La laïcité garantit la liberté d'expression religieuse pour tous les citoyens français, tant que cela ne perturbe pas l'ordre public ou ne porte pas atteinte aux droits d'autrui."
      },
      {
        "id": "100_7",
        "type": "qcm",
        "question": "Quel est le rôle des écoles publiques dans une société laïque ?",
        "options": [
          "Les écoles publiques doivent enseigner les valeurs de la République et respecter la neutralité religieuse.",
          "Les écoles publiques doivent promouvoir une religion d'État pour unifier les élèves.",
          "Les écoles publiques doivent interdire toute expression religieuse pour maintenir l'ordre.",
          "Les écoles publiques doivent favoriser le dialogue interreligieux pour promouvoir la coexistence pacifique."
        ],
        "correct_option": "Les écoles publiques doivent enseigner les valeurs de la République et respecter la neutralité religieuse.",
        "explanation": "Dans une société laïque, les écoles publiques ont pour rôle d'enseigner les valeurs de la République et de respecter la neutralité religieuse, sans favoriser ni discriminer aucune religion."
      },
      {
        "id": "100_8",
        "type": "vrai-faux",
        "question": "La laïcité permet à chacun de pratiquer sa religion librement, même si cela perturbe l'ordre public.",
        "correct": False,
        "explanation": "C'est faux. La laïcité permet à chacun de pratiquer sa religion librement, mais cela ne doit pas perturber l'ordre public ou porter atteinte aux droits d'autrui. La liberté de religion est un droit fondamental, mais elle doit être exercée dans le respect des lois et de la coexistence pacifique."
      }
    ]
  ],
  [
    "101",
    "EMC 6e - La citoyenneté européenne",
    "EMC",
    "6eme",
    [
      {
        "id": "101_1",
        "type": "qcm",
        "question": "Qu'est-ce que la citoyenneté européenne ?",
        "options": [
          "La citoyenneté européenne est un statut qui confère des droits et des devoirs aux citoyens des États membres de l'Union européenne.",
          "La citoyenneté européenne est un statut qui confère des droits et des devoirs aux citoyens de tous les pays d'Europe.",
          "La citoyenneté européenne est un statut qui confère des droits et des devoirs aux citoyens de la France uniquement.",
          "La citoyenneté européenne est un statut qui confère des droits et des devoirs aux citoyens de l'Union européenne et de la France."
        ],
        "correct_option": "La citoyenneté européenne est un statut qui confère des droits et des devoirs aux citoyens des États membres de l'Union européenne.",
        "explanation": "La citoyenneté européenne est un statut qui confère des droits et des devoirs aux citoyens des États membres de l'Union européenne, leur permettant de participer à la vie démocratique de l'Union et de bénéficier de certains droits au sein de l'Union européenne."
      },
      {
        "id": "101_2",
        "type": "vrai-faux",
        "question": "La citoyenneté européenne permet aux citoyens de l'Union européenne de circuler librement et de résider dans n'importe quel pays de l'Union européenne.",
        "correct": True,
        "explanation": "C'est vrai. La citoyenneté européenne permet aux citoyens de l'Union européenne de circuler librement et de résider dans n'importe quel pays de l'Union européenne, facilitant ainsi la mobilité et les échanges entre les États membres."
      },
      {
        "id": "101_3",
        "type": "qcm",
        "question": "Quels sont les droits politiques associés à la citoyenneté européenne ?",
        "options": [
          "Le droit de vote et d'éligibilité aux élections municipales, régionales, nationales et européennes dans l'État membre de résidence.",
          "Le droit de vote et d'éligibilité uniquement aux élections européennes.",
          "Le droit de vote et d'éligibilité uniquement aux élections nationales du pays d'origine.",
          "Le droit de vote et d'éligibilité uniquement aux élections municipales du pays de résidence."
        ],
        "correct_option": "Le droit de vote et d'éligibilité aux élections municipales, régionales, nationales et européennes dans l'État membre de résidence.",
        "explanation": "Les citoyens européens ont le droit de vote et d'éligibilité aux élections municipales, régionales, nationales et européennes dans l'État membre où ils résident, leur permettant ainsi de participer activement à la vie démocratique de leur pays d'accueil."
      },
      {
        "id": "101_4",
        "type": "vrai-faux",
        "question": "La citoyenneté européenne confère également des droits sociaux, économiques et culturels aux citoyens de l'Union européenne.",
        "correct": True,
        "explanation": "C'est vrai. La citoyenneté européenne confère également des droits sociaux, économiques et culturels aux citoyens de l'Union européenne, tels que le droit à la protection sociale, le droit à l'emploi et le droit à la culture au sein de l'Union européenne."
      },
      {
        "id": "101_5",
        "type": "qcm",
        "question": "Quel est le rôle du Parlement européen pour les citoyens européens ?",
        "options": [
          "Le Parlement européen représente les citoyens européens au sein des institutions de l'Union européenne et participe à l'élaboration des lois européennes.",
          "Le Parlement européen gère les finances publiques de l'Union européenne.",
          "Le Parlement européen est chargé de la défense nationale de l'Union européenne.",
          "Le Parlement européen élabore les politiques étrangères de l'Union européenne."
        ],
        "correct_option": "Le Parlement européen représente les citoyens européens au sein des institutions de l'Union européenne et participe à l'élaboration des lois européennes.",
        "explanation": "Le Parlement européen représente les citoyens européens au sein des institutions de l'Union européenne et participe activement à l'élaboration des lois européennes, contribuant ainsi à la démocratie et à la gouvernance de l'Union européenne."
      },
      {
        "id": "101_6",
        "type": "vrai-faux",
        "question": "Les citoyens européens peuvent se déplacer librement dans tous les États membres de l'Union européenne.",
        "correct": True,
        "explanation": "C'est vrai. Les citoyens européens ont le droit de se déplacer librement dans tous les États membres de l'Union européenne, ce qui inclut le droit de résider, de travailler et d'étudier dans n'importe quel pays de l'UE."
      },
      {
        "id": "101_7",
        "type": "qcm",
        "question": "Quel est le rôle de la Cour de justice de l'Union européenne pour les citoyens européens ?",
        "options": [
          "La Cour de justice de l'Union européenne veille à l'application uniforme du droit européen et protège les droits des citoyens européens.",
          "La Cour de justice de l'Union européenne gère les finances publiques de l'Union européenne.",
          "La Cour de justice de l'Union européenne est chargée de la défense nationale de l'Union européenne.",
          "La Cour de justice de l'Union européenne élabore les politiques étrangères de l'Union européenne."
        ],
        "correct_option": "La Cour de justice de l'Union européenne veille à l'application uniforme du droit européen et protège les droits des citoyens européens.",
        "explanation": "La Cour de justice de l'Union européenne veille à l'application uniforme du droit européen dans tous les États membres et protège les droits des citoyens européens en interprétant et en appliquant le droit européen."
      },
      {
        "id": "101_8",
        "type": "vrai-faux",
        "question": "La citoyenneté européenne permet aux citoyens d'un pays non membre de l'Union européenne d'obtenir des droits au sein de l'UE.",
        "correct": False,
        "explanation": "C'est faux. La citoyenneté européenne est réservée aux citoyens des États membres de l'Union européenne. Les citoyens d'un pays non membre ne bénéficient pas des droits associés à la citoyenneté européenne, bien qu'ils puissent avoir certains droits en fonction des accords bilatéraux ou des politiques nationales."
      }
    ]
  ],
  [
    "102",
    "EMC 6e - La solidarité internationale",
    "EMC",
    "6eme",
    [
      {
        "id": "102_1",
        "type": "qcm",
        "question": "Qu'est-ce que la solidarité internationale ?",
        "options": [
          "La solidarité internationale est un principe qui encourage les individus et les nations à s'entraider pour résoudre les problèmes mondiaux.",
          "La solidarité internationale est un concept qui promeut l'indépendance totale des nations.",
          "La solidarité internationale est une politique qui favorise uniquement les pays riches.",
          "La solidarité internationale est une idéologie qui prône la non-intervention dans les affaires des autres pays."
        ],
        "correct_option": "La solidarité internationale est un principe qui encourage les individus et les nations à s'entraider pour résoudre les problèmes mondiaux.",
        "explanation": "La solidarité internationale est un principe qui encourage les individus et les nations à s'entraider pour résoudre les problèmes mondiaux tels que la pauvreté, les conflits, les catastrophes naturelles et les inégalités."
      },
      {
        "id": "102_2",
        "type": "vrai-faux",
        "question": "La solidarité internationale implique que les pays riches doivent aider les pays pauvres à se développer.",
        "correct": True,
        "explanation": "C'est vrai. La solidarité internationale implique que les pays riches ont la responsabilité d'aider les pays pauvres à se développer en fournissant une assistance financière, technique et humanitaire."
      },
      {
        "id": "102_3",
        "type": "qcm",
        "question": "Quels sont les domaines dans lesquels la solidarité internationale peut s'exprimer ?",
        "options": [
          "L'aide humanitaire, le développement économique, la protection de l'environnement, la promotion des droits humains",
          "L'aide humanitaire, le développement économique, la promotion de la culture, la défense nationale",
          "Le développement économique, la protection de l'environnement, la promotion des droits humains, la gestion des ressources naturelles",
          "L'aide humanitaire, la promotion de la culture, la défense nationale, la gestion des ressources naturelles"
        ],
        "correct_option": "L'aide humanitaire, le développement économique, la protection de l'environnement, la promotion des droits humains",
        "explanation": "La solidarité internationale peut s'exprimer dans divers domaines tels que l'aide humanitaire pour les victimes de catastrophes ou de conflits, le développement économique pour réduire les inégalités, la protection de l'environnement pour lutter contre le changement climatique et la promotion des droits humains pour garantir la dignité et l'égalité pour tous."
      },
      {
        "id": "102_4",
        "type": "vrai-faux",
        "question": "La solidarité internationale est un concept qui ne concerne que les gouvernements et les organisations internationales.",
        "correct": False,
        "explanation": "C'est faux. La solidarité internationale concerne non seulement les gouvernements et les organisations internationales, mais aussi les individus et les communautés qui peuvent contribuer à des actions solidaires à travers le bénévolat, les dons ou la sensibilisation aux enjeux mondiaux."
      },
      {
        "id": "102_5",
        "type": "qcm",
        "question": "Quel est le rôle des organisations non gouvernementales (ONG) dans la solidarité internationale ?",
        "options": [
          "Les ONG jouent un rôle crucial en fournissant une assistance directe, en sensibilisant le public et en plaidant pour des politiques plus justes au niveau international.",
          "Les ONG sont principalement responsables de la gestion des finances publiques dans les pays en développement.",
          "Les ONG sont chargées de la défense nationale dans les pays en développement.",
          "Les ONG sont responsables de l'élaboration des politiques étrangères des pays riches."
        ],
        "correct_option": "Les ONG jouent un rôle crucial en fournissant une assistance directe, en sensibilisant le public et en plaidant pour des politiques plus justes au niveau international.",
        "explanation": "Les organisations non gouvernementales (ONG) jouent un rôle crucial dans la solidarité internationale en fournissant une assistance directe aux populations dans le besoin, en sensibilisant le public aux enjeux mondiaux et en plaidant pour des politiques plus justes au niveau international."
      },
      {
        "id": "102_6",
        "type": "vrai-faux",
        "question": "La solidarité internationale est un concept qui promeut la non-intervention dans les affaires des autres pays.",
        "correct": False,
        "explanation": "C'est faux. La solidarité internationale encourage l'entraide et la coopération entre les nations pour résoudre les problèmes mondiaux, ce qui peut impliquer une intervention dans les affaires des autres pays lorsque cela est nécessaire pour protéger les droits humains ou fournir une assistance humanitaire."
      },
      {
        "id": "102_7",
        "type": "qcm",
        "question": "Quel est le rôle des citoyens dans la solidarité internationale ?",
        "options": [
          "Les citoyens peuvent contribuer à la solidarité internationale en faisant du bénévolat, en faisant des dons ou en sensibilisant aux enjeux mondiaux.",
          "Les citoyens n'ont aucun rôle à jouer dans la solidarité internationale, qui est uniquement une responsabilité des gouvernements.",
          "Les citoyens sont responsables de la gestion des finances publiques dans les pays en développement.",
          "Les citoyens sont chargés de la défense nationale dans les pays en développement."
        ],
        "correct_option": "Les citoyens peuvent contribuer à la solidarité internationale en faisant du bénévolat, en faisant des dons ou en sensibilisant aux enjeux mondiaux.",
        "explanation": "Les citoyens ont un rôle important à jouer dans la solidarité internationale en contribuant à des actions solidaires à travers le bénévolat, les dons ou la sensibilisation aux enjeux mondiaux, ce qui peut aider à mobiliser des ressources et à promouvoir une culture de solidarité à l'échelle mondiale."
      },
      {
        "id": "102_8",
        "type": "vrai-faux",
        "question": "La solidarité internationale est un concept qui promeut l'indépendance totale des nations.",
        "correct": False,
        "explanation": "C'est faux. La solidarité internationale encourage la coopération et l'entraide entre les nations pour résoudre les problèmes mondiaux, ce qui peut impliquer une interdépendance entre les pays plutôt qu'une indépendance totale."
      }
    ]
  ],
  [
    "103",
    "EMC 6e - la citoyenneté française ",
    "EMC",
    "6eme",
    [
      {
        "id": "103_1",
        "type": "qcm",
        "question": "Qu'est-ce que la citoyenneté française ?",
        "options": [
          "La citoyenneté française est un statut juridique qui confère des droits et des devoirs aux personnes reconnues comme membres de la nation française.",
          "La citoyenneté française est un statut juridique qui confère des droits et des devoirs aux personnes nées en France uniquement.",
          "La citoyenneté française est un statut juridique qui confère des droits et des devoirs aux personnes ayant des ancêtres français uniquement.",
          "La citoyenneté française est un statut juridique qui confère des droits et des devoirs aux personnes résidant en France depuis au moins 10 ans."
        ],
        "correct_option": "La citoyenneté française est un statut juridique qui confère des droits et des devoirs aux personnes reconnues comme membres de la nation française.",
        "explanation": "La citoyenneté française est un statut juridique qui confère des droits et des devoirs aux personnes reconnues comme membres de la nation française."
      },
      {
        "id": "103_2",
        "type": "vrai-faux",
        "question": "La citoyenneté française peut être acquise par la naissance, la naturalisation ou le mariage avec un citoyen français.",
        "correct": True,
        "explanation": "C'est vrai. La citoyenneté française peut être acquise de différentes manières, notamment par la naissance en France (droit du sol), par la naturalisation (pour les étrangers qui remplissent certaines conditions) ou par le mariage avec un citoyen français."
      },
      {
        "id": "103_3",
        "type": "qcm",
        "question": "Quels sont les droits politiques associés à la citoyenneté française ?",
        "options": [
          "Le droit de vote et d'éligibilité aux élections municipales, régionales, nationales et européennes.",
          "Le droit de vote et d'éligibilité uniquement aux élections municipales.",
          "Le droit de vote et d'éligibilité uniquement aux élections nationales.",
          "Le droit de vote et d'éligibilité uniquement aux élections européennes."
        ],
        "correct_option": "Le droit de vote et d'éligibilité aux élections municipales, régionales, nationales et européennes.",
        "explanation": "Les citoyens français ont le droit de vote et d'éligibilité aux élections municipales, régionales, nationales et européennes, leur permettant ainsi de participer activement à la vie démocratique de la France."
      },
      {
        "id": "103_4",
        "type": "vrai-faux",
        "question": "La citoyenneté française confère également des droits sociaux, économiques et culturels aux citoyens français.",
        "correct": True,
        "explanation": "C'est vrai. La citoyenneté française confère également des droits sociaux, économiques et culturels aux citoyens français, tels que le droit à la protection sociale, le droit à l'emploi et le droit à la culture en France."
      },
      {
        "id": "103_5",
        "type": "qcm",
        "question": "Quel est le rôle de la Constitution française pour les citoyens français ?",
        "options": [
          "La Constitution française établit les droits et les devoirs des citoyens français et organise les institutions de la République.",
          "La Constitution française gère les finances publiques de la France.",
          "La Constitution française est chargée de la défense nationale de la France.",
          "La Constitution française élabore les politiques étrangères de la France."
        ],
        "correct_option": "La Constitution française établit les droits et les devoirs des citoyens français et organise les institutions de la République.",
        "explanation": "La Constitution française établit les droits et les devoirs des citoyens français et organise les institutions de la République, garantissant ainsi le fonctionnement démocratique de la France."
      },
      {
        "id": "103_6",
        "type": "vrai-faux",
        "question": "La citoyenneté française permet aux citoyens de se déplacer librement dans tous les pays du monde.",
        "correct": False,
        "explanation": "C'est faux. La citoyenneté française permet aux citoyens de se déplacer librement dans les pays de l'Union européenne grâce à la citoyenneté européenne, mais elle ne garantit pas une liberté de déplacement totale dans tous les pays du monde, qui peut être soumise à des restrictions en fonction des accords internationaux et des politiques des autres pays."
      },
      {
        "id": "103_7",
        "type": "qcm",
        "question": "Quel est le rôle du Président de la République française pour les citoyens français ?",
        "options": [
          "Le Président de la République française est le chef de l'État et représente les citoyens français au niveau national et international.",
          "Le Président de la République française gère les finances publiques de la France.",
          "Le Président de la République française est chargé de la défense nationale de la France.",
          "Le Président de la République française élabore les politiques étrangères de la France."
        ],
        "correct_option": "Le Président de la République française est le chef de l'État et représente les citoyens français au niveau national et international.",
        "explanation": "Le Président de la République française est le chef de l'État et représente les citoyens français au niveau national et international, jouant un rôle important dans la gouvernance et la diplomatie de la France."
      },
      {
        "id": "103_8",
        "type": "vrai-faux",
        "question": "La citoyenneté française permet aux citoyens d'un pays non membre de l'Union européenne d'obtenir des droits en France.",
        "correct": False,
        "explanation": "C'est faux. La citoyenneté française est réservée aux personnes reconnues comme membres de la nation française, et les citoyens d'un pays non membre de l'Union européenne ne bénéficient pas des droits associés à la citoyenneté française, bien qu'ils puissent avoir certains droits en fonction des accords bilatéraux ou des politiques nationales."
      }
    ]
  ],
  [
    "104",
    "EMC 6e - Les institutions françaises",
    "EMC",
    "6eme",
    [
      {
        "id": "104_1",
        "type": "qcm",
        "question": "Quelles sont les principales institutions de la République française ?",
        "options": [
          "Le Président de la République, le Parlement, le Gouvernement, le Conseil constitutionnel",
          "Le Président de la République, le Parlement, le Gouvernement, la Cour de justice de l'Union européenne",
          "Le Président de la République, le Parlement, le Gouvernement, la Cour des comptes",
          "Le Président de la République, le Parlement, le Gouvernement, la Cour de cassation"
        ],
        "correct_option": "Le Président de la République, le Parlement, le Gouvernement, le Conseil constitutionnel",
        "explanation": "Les principales institutions de la République française sont le Président de la République, le Parlement (composé de l'Assemblée nationale et du Sénat), le Gouvernement et le Conseil constitutionnel."
      },
      {
        "id": "104_2",
        "type": "vrai-faux",
        "question": "Le Président de la République française est élu au suffrage universel direct pour un mandat de cinq ans.",
        "correct": True,
        "explanation": "C'est vrai. Le Président de la République française est élu au suffrage universel direct pour un mandat de cinq ans, renouvelable une fois."
      },
      {
        "id": "104_3",
        "type": "qcm",
        "question": "Quel est le rôle du Parlement français ?",
        "options": [
          "Le Parlement français élabore les lois, contrôle le Gouvernement et représente les citoyens français.",
          "Le Parlement français gère les finances publiques de la France.",
          "Le Parlement français est chargé de la défense nationale de la France.",
          "Le Parlement français élabore les politiques étrangères de la France."
        ],
        "correct_option": "Le Parlement français élabore les lois, contrôle le Gouvernement et représente les citoyens français.",
        "explanation": "Le Parlement français a pour rôle d'élaborer les lois, de contrôler le Gouvernement et de représenter les citoyens français à travers les députés et les sénateurs."
      },
      {
        "id": "104_4",
        "type": "vrai-faux",
        "question": "Le Gouvernement français est dirigé par le Premier ministre, qui est nommé par le Président de la République.",
        "correct": True,
        "explanation": "C'est vrai. Le Gouvernement français est dirigé par le Premier ministre, qui est nommé par le Président de la République et est responsable devant le Parlement."
      },
      {
        "id": "104_5",
        "type": "qcm",
        "question": "Quel est le rôle du Conseil constitutionnel français ?",
        "options": [
          "Le Conseil constitutionnel veille à la conformité des lois à la Constitution et protège les droits fondamentaux des citoyens français.",
          "Le Conseil constitutionnel gère les finances publiques de la France.",
          "Le Conseil constitutionnel est chargé de la défense nationale de la France.",
          "Le Conseil constitutionnel élabore les politiques étrangères de la France."
        ],
        "correct_option": "Le Conseil constitutionnel veille à la conformité des lois à la Constitution et protège les droits fondamentaux des citoyens français.",
        "explanation": "Le Conseil constitutionnel français veille à la conformité des lois à la Constitution et protège les droits fondamentaux des citoyens français en examinant les lois avant leur promulgation et en statuant sur les questions de constitutionnalité."
      },
      {
        "id": "104_6",
        "type": "vrai-faux",
        "question": "Le Parlement français est composé de deux chambres : l'Assemblée nationale et le Sénat.",
        "correct": True,
        "explanation": "C'est vrai. Le Parlement français est composé de deux chambres : l'Assemblée nationale et le Sénat."
      },
      {
        "id": "104_7",
        "type": "qcm",
        "question": "Qu'est-ce que la liberté d'expression ?",
        "options": [
          "La liberté d'expression permet à chacun de s'exprimer librement, sans censure ni restriction.",
          "La liberté d'expression permet au gouvernement de contrôler les médias.",
          "La liberté d'expression interdit toute critique envers les autorités.",
          "La liberté d'expression est limitée aux discours publics uniquement."
        ],
        "correct_option": "La liberté d'expression permet à chacun de s'exprimer librement, sans censure ni restriction.",
        "explanation": "La liberté d'expression est un droit fondamental qui permet à chacun de s'exprimer librement, sans censure ni restriction, tout en respectant les lois et les droits des autres."
      },
      {
        "id": "104_8",
        "type": "vrai-faux",
        "question": "Le Conseil constitutionnel français est chargé de l'élaboration des politiques étrangères de la France.",
        "correct": False,
        "explanation": "C'est faux. Le Conseil constitutionnel français n'est pas chargé de l'élaboration des politiques étrangères de la France. Son rôle principal est de veiller à la conformité des lois à la Constitution et de protéger les droits fondamentaux des citoyens français."
      }
    ]
  ],
  [
    "105",
    "EMC 6e - Les droits et devoirs du citoyen",
    "EMC",
    "6eme",
    [
      {
        "id": "105_1",
        "type": "qcm",
        "question": "Quels sont les droits fondamentaux des citoyens français ?",
        "options": [
          "La liberté d'expression, le droit de vote, le droit à l'éducation",
          "Le droit de posséder des armes, le droit de ne pas payer d'impôts",
          "Le droit de ne pas respecter les lois, le droit de ne pas travailler",
          "Le droit de contrôler les médias, le droit de faire la guerre"
        ],
        "correct_option": "La liberté d'expression, le droit de vote, le droit à l'éducation",
        "explanation": "Les droits fondamentaux des citoyens français incluent la liberté d'expression, le droit de vote, le droit à l'éducation, et d'autres droits garantis par la Constitution."
      },
      {
        "id": "105_2",
        "type": "vrai-faux",
        "question": "Les citoyens français ont le devoir de respecter les lois.",
        "correct": True,
        "explanation": "C'est vrai. Les citoyens français ont le devoir de respecter les lois pour garantir le bon fonctionnement de la société."
      },
      {
        "id": "105_3",
        "type": "qcm",
        "question": "Quels sont les devoirs des citoyens français ?",
        "options": [
          "Respecter les lois, payer des impôts, participer à la vie démocratique",
          "Ne pas respecter les lois, ne pas payer d'impôts, ne pas participer à la vie démocratique",
          "Respecter les lois uniquement lorsqu'ils le souhaitent",
          "Payer des impôts uniquement lorsqu'ils en ont envie"
        ],
        "correct_option": "Respecter les lois, payer des impôts, participer à la vie démocratique",
        "explanation": "Les devoirs des citoyens français incluent le respect des lois, le paiement des impôts et la participation à la vie démocratique pour contribuer au bien-être de la société."
      },
      {
        "id": "²105_4",
        "type": "vrai-faux",
        "question": "Les citoyens français ont le droit de ne pas respecter les lois s'ils ne sont pas d'accord avec elles.",
        "correct": False,
        "explanation": "C'est faux. Les citoyens français ont le devoir de respecter les lois, même s'ils ne sont pas d'accord avec elles. Cependant, ils peuvent exprimer leur désaccord de manière pacifique et démocratique, par exemple en votant ou en participant à des manifestations légales."
      },
      {
        "id": "105_5",
        "type": "qcm",
        "question": "Quel est le rôle de la participation citoyenne dans une démocratie ?",
        "options": [
          "La participation citoyenne permet aux citoyens de s'impliquer dans les décisions qui affectent leur vie et de contribuer au fonctionnement de la démocratie.",
          "La participation citoyenne est inutile dans une démocratie, car les décisions sont prises par les politiciens.",
          "La participation citoyenne est réservée aux élites politiques et économiques.",
          "La participation citoyenne est limitée aux élections uniquement."
        ],
        "correct_option": "La participation citoyenne permet aux citoyens de s'impliquer dans les décisions qui affectent leur vie et de contribuer au fonctionnement de la démocratie.",
        "explanation": "La participation citoyenne est essentielle dans une démocratie, car elle permet aux citoyens de s'impliquer dans les décisions qui affectent leur vie et de contribuer au fonctionnement de la démocratie à travers le vote, le bénévolat, la participation à des associations ou des mouvements sociaux."
      },
      {
        "id": "105_6",
        "type": "vrai-faux",
        "question": "Les citoyens français ont le droit de ne pas payer d'impôts s'ils ne sont pas d'accord avec les politiques fiscales du gouvernement.",
        "correct": False,
        "explanation": "C'est faux. Les citoyens français ont le devoir de payer des impôts pour financer les services publics et les infrastructures, même s'ils ne sont pas d'accord avec les politiques fiscales du gouvernement. Cependant, ils peuvent exprimer leur désaccord de manière pacifique et démocratique, par exemple en votant ou en participant à des manifestations légales."
      },
      {
        "id": "105_7",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation civique dans la formation des citoyens français ?",
        "options": [
          "L'éducation civique permet aux citoyens français de comprendre leurs droits et devoirs, ainsi que le fonctionnement des institutions de la République.",
          "L'éducation civique est inutile dans la formation des citoyens français, car ils peuvent apprendre par eux-mêmes.",
          "L'éducation civique est réservée aux élites politiques et économiques.",
          "L'éducation civique est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation civique permet aux citoyens français de comprendre leurs droits et devoirs, ainsi que le fonctionnement des institutions de la République.",
        "explanation": "L'éducation civique joue un rôle crucial dans la formation des citoyens français en leur permettant de comprendre leurs droits et devoirs, ainsi que le fonctionnement des institutions de la République, ce qui les prépare à participer activement à la vie démocratique."
      },
      {
        "id": "105_8",
        "type": "vrai-faux",
        "question": "Les citoyens français ont le droit de ne pas participer à la vie démocratique s'ils ne le souhaitent pas.",
        "correct": True,
        "explanation": "C'est vrai. Les citoyens français ont le droit de choisir s'ils souhaitent ou non participer à la vie démocratique, que ce soit en votant, en s'engageant dans des associations ou en exprimant leurs opinions. Cependant, la participation citoyenne est encouragée pour renforcer la démocratie et contribuer au bien-être de la société."
      }
    ]
  ],
  [
    "106",
    "EMC 6e - Respect des différences (apparence, origine)",
    "EMC",
    "6eme",
    [
      {
        "id": "106_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les différences d'apparence et d'origine ?",
        "options": [
          "Le respect des différences favorise la tolérance, l'inclusion et la diversité dans la société.",
          "Le respect des différences est inutile, car tout le monde devrait être pareil.",
          "Le respect des différences est réservé aux élites politiques et économiques.",
          "Le respect des différences est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des différences favorise la tolérance, l'inclusion et la diversité dans la société.",
        "explanation": "Le respect des différences d'apparence et d'origine est important car il favorise la tolérance, l'inclusion et la diversité dans la société, permettant à chacun de se sentir accepté et valorisé pour ce qu'il est."
      },
      {
        "id": "106_2",
        "type": "vrai-faux",
        "question": "Le respect des différences d'apparence et d'origine est un droit fondamental protégé par la loi.",
        "correct": True,
        "explanation": "C'est vrai. Le respect des différences d'apparence et d'origine est un droit fondamental protégé par la loi, notamment à travers les lois contre la discrimination et les discours de haine."
      },
      {
        "id": "106_3",
        "type": "qcm",
        "question": "Quels sont les avantages de respecter les différences d'apparence et d'origine ?",
        "options": [
          "Le respect des différences favorise la créativité, l'innovation et la richesse culturelle dans la société.",
          "Le respect des différences est inutile, car tout le monde devrait être pareil.",
          "Le respect des différences est réservé aux élites politiques et économiques.",
          "Le respect des différences est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des différences favorise la créativité, l'innovation et la richesse culturelle dans la société.",
        "explanation": "Le respect des différences d'apparence et d'origine favorise la créativité, l'innovation et la richesse culturelle dans la société, permettant à chacun de contribuer de manière unique et précieuse."
      },
      {
        "id": "106_4",
        "type": "vrai-faux",
        "question": "Le respect des différences d'apparence et d'origine est un concept qui promeut la non-intervention dans les affaires des autres personnes.",
        "correct": False,
        "explanation": "C'est faux. Le respect des différences d'apparence et d'origine encourage l'acceptation et la valorisation de la diversité, ce qui peut impliquer une intervention pour protéger les droits des personnes ou pour promouvoir l'inclusion et la tolérance dans la société."
      },
      {
        "id": "106_5",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des différences d'apparence et d'origine ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus à la diversité et en promouvant le respect des différences.",
          "L'éducation est inutile dans le respect des différences, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus à la diversité et en promouvant le respect des différences.",
        "explanation": "L'éducation joue un rôle crucial dans le respect des différences d'apparence et d'origine en sensibilisant les individus à la diversité, en promouvant le respect des différences et en encourageant l'inclusion et la tolérance dès le plus jeune âge."
      },
      {
        "id": "106_6",
        "type": "vrai-faux",
        "question": "Le respect des différences d'apparence et d'origine est un concept qui promeut l'indépendance totale des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des différences d'apparence et d'origine encourage l'acceptation de la diversité tout en reconnaissant que les individus peuvent être interconnectés et interdépendants au sein de la société."
      },
      {
        "id": "106_7",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des différences d'apparence et d'origine ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives de la diversité et en sensibilisant le public au respect des différences.",
          "Les médias sont inutiles dans le respect des différences, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives de la diversité et en sensibilisant le public au respect des différences.",
        "explanation": "Les médias ont un rôle important dans le respect des différences d'apparence et d'origine en promouvant des représentations positives de la diversité, en sensibilisant le public au respect des différences et en contribuant à la construction d'une société plus inclusive et tolérante."
      },
      {
        "id": "106_8",
        "type": "vrai-faux",
        "question": "Le respect des différences d'apparence et d'origine est un concept qui promeut la non-intervention dans les affaires des autres personnes. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des différences d'apparence et d'origine encourage l'acceptation et la valorisation de la diversité, ce qui peut impliquer une intervention pour protéger les droits des personnes ou pour promouvoir l'inclusion et la tolérance dans la société."
      }
    ]
  ],
  [
    "107",
    "EMC 6e - Respect environnement (déchets, pollution)",
    "EMC",
    "6eme",
    [
      {
        "id": "107_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter l'environnement en réduisant les déchets et la pollution ?",
        "options": [
          "Le respect de l'environnement contribue à la santé de la planète, à la préservation des ressources naturelles et à la qualité de vie des générations futures.",
          "Le respect de l'environnement est inutile, car la nature peut se régénérer toute seule.",
          "Le respect de l'environnement est réservé aux élites politiques et économiques.",
          "Le respect de l'environnement est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect de l'environnement contribue à la santé de la planète, à la préservation des ressources naturelles et à la qualité de vie des générations futures.",
        "explanation": "Le respect de l'environnement en réduisant les déchets et la pollution est crucial pour préserver la santé de la planète, protéger les ressources naturelles et assurer une qualité de vie durable pour les générations futures."
      },
      {
        "id": "107_2",
        "type": "vrai-faux",
        "question": "Le respect de l'environnement est un droit fondamental protégé par la loi.",
        "correct": True,
        "explanation": "C'est vrai. Le respect de l'environnement est un droit fondamental protégé par la loi, notamment à travers les lois sur la protection de l'environnement et les réglementations sur les émissions polluantes."
      },
      {
        "id": "107_3",
        "type": "qcm",
        "question": "Quels sont les avantages du respect de l'environnement en réduisant les déchets et la pollution ?",
        "options": [
          "Le respect de l'environnement favorise la santé publique, protège les écosystèmes et contribue à un avenir plus durable.",
          "Le respect de l'environnement est inutile, car la nature peut se régénérer toute seule.",
          "Le respect de l'environnement est réservé aux élites politiques et économiques.",
          "Le respect de l'environnement est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect de l'environnement favorise la santé publique, protège les écosystèmes et contribue à un avenir plus durable.",
        "explanation": "Le respect de l'environnement en réduisant les déchets et la pollution favorise la santé publique en limitant les risques liés à la pollution, protège les écosystèmes en préservant la biodiversité et contribue à un avenir plus durable en utilisant les ressources de manière responsable."
      },
      {
        "id": "107_4",
        "type": "vrai-faux",
        "question": "Le respect de l'environnement est un concept qui promeut la non-intervention dans les affaires de la nature.",
        "correct": False,
        "explanation": "C'est faux. Le respect de l'environnement encourage l'intervention pour protéger la nature, réduire les déchets et la pollution, et promouvoir des pratiques durables pour préserver la planète."
      },
      {
        "id": "107_5",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect de l'environnement en réduisant les déchets et la pollution ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus à l'importance du respect de l'environnement et en promouvant des comportements éco-responsables.",
          "L'éducation est inutile dans le respect de l'environnement, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus à l'importance du respect de l'environnement et en promouvant des comportements éco-responsables.",
        "explanation": "L'éducation joue un rôle crucial dans le respect de l'environnement en sensibilisant les individus à l'importance de réduire les déchets et la pollution, en promouvant des comportements éco-responsables et en encourageant une culture de durabilité dès le plus jeune âge."
      },
      {
        "id": "107_6",
        "type": "vrai-faux",
        "question": "Le respect de l'environnement est un concept qui promeut la non-intervention dans les affaires de la nature. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect de l'environnement encourage l'intervention pour protéger la nature, réduire les déchets et la pollution, et promouvoir des pratiques durables pour préserver la planète."
      },
      {
        "id": "107_7",
        "type": "qcm",
        "question": "Quel est le rôle des gouvernements dans le respect de l'environnement en réduisant les déchets et la pollution ?",
        "options": [
          "Les gouvernements ont un rôle important en adoptant des lois et des politiques pour protéger l'environnement et en encourageant les comportements éco-responsables.",
          "Les gouvernements sont inutiles dans le respect de l'environnement, car les individus peuvent agir par eux-mêmes.",
          "Les gouvernements sont réservés aux élites politiques et économiques.",
          "Les gouvernements sont limités aux écoles uniquement."
        ],
        "correct_option": "Les gouvernements ont un rôle important en adoptant des lois et des politiques pour protéger l'environnement et en encourageant les comportements éco-responsables.",
        "explanation": "Les gouvernements ont un rôle important dans le respect de l'environnement en adoptant des lois et des politiques pour protéger l'environnement, en réglementant les émissions polluantes, en encourageant les comportements éco-responsables et en investissant dans des technologies durables pour préserver la planète."
      },
      {
        "id": "107_8",
        "type": "vrai-faux",
        "question": "Le respect de l'environnement est un concept qui promeut la non-intervention dans les affaires de la nature. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect de l'environnement encourage l'intervention pour protéger la nature, réduire les déchets et la pollution, et promouvoir des pratiques durables pour préserver la planète."
      }
    ]
  ],
  [
    "108",
    "EMC 6e - Respect des différences (handicap)",
    "EMC",
    "6eme",
    [
      {
        "id": "108_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les différences liées au handicap ?",
        "options": [
          "Le respect des différences liées au handicap favorise l'inclusion, l'égalité des chances et la dignité des personnes en situation de handicap.",
          "Le respect des différences liées au handicap est inutile, car les personnes en situation de handicap devraient être traitées comme tout le monde.",
          "Le respect des différences liées au handicap est réservé aux élites politiques et économiques.",
          "Le respect des différences liées au handicap est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des différences liées au handicap favorise l'inclusion, l'égalité des chances et la dignité des personnes en situation de handicap.",
        "explanation": "Le respect des différences liées au handicap est important car il favorise l'inclusion, l'égalité des chances et la dignité des personnes en situation de handicap, permettant à chacun de se sentir accepté et valorisé pour ce qu'il est."
      },
      {
        "id": "108_2",
        "type": "vrai-faux",
        "question": "Le respect des différences liées au handicap est un droit fondamental protégé par la loi.",
        "correct": True,
        "explanation": "C'est vrai. Le respect des différences liées au handicap est un droit fondamental protégé par la loi, notamment à travers les lois sur l'accessibilité, la non-discrimination et les droits des personnes en situation de handicap."
      },
      {
        "id": "108_3",
        "type": "qcm",
        "question": "Quels sont les avantages de respecter les différences liées au handicap ?",
        "options": [
          "Le respect des différences liées au handicap favorise la diversité, l'inclusion et la richesse humaine dans la société.",
          "Le respect des différences liées au handicap est inutile, car les personnes en situation de handicap devraient être traitées comme tout le monde.",
          "Le respect des différences liées au handicap est réservé aux élites politiques et économiques.",
          "Le respect des différences liées au handicap est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des différences liées au handicap favorise la diversité, l'inclusion et la richesse humaine dans la société.",
        "explanation": "Le respect des différences liées au handicap favorise la diversité, l'inclusion et la richesse humaine dans la société, permettant à chacun de contribuer de manière unique et précieuse."
      },
      {
        "id": "108_4",
        "type": "vrai-faux",
        "question": "Le respect des différences liées au handicap est un concept qui promeut la non-intervention dans les affaires des personnes en situation de handicap.",
        "correct": False,
        "explanation": "C'est faux. Le respect des différences liées au handicap encourage l'acceptation et la valorisation de la diversité, ce qui peut impliquer une intervention pour protéger les droits des personnes en situation de handicap ou pour promouvoir l'inclusion et la tolérance dans la société."
      },
      {
        "id": "108_5",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des différences liées au handicap ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus à la diversité et en promouvant le respect des différences liées au handicap.",
          "L'éducation est inutile dans le respect des différences liées au handicap, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus à la diversité et en promouvant le respect des différences liées au handicap.",
        "explanation": "L'éducation joue un rôle crucial dans le respect des différences liées au handicap en sensibilisant les individus à la diversité, en promouvant le respect des différences et en encourageant l'inclusion et la tolérance dès le plus jeune âge."
      },
      {
        "id": "108_6",
        "type": "vrai-faux",
        "question": "Le respect des différences liées au handicap est un concept qui promeut la non-intervention dans les affaires des personnes en situation de handicap. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des différences liées au handicap encourage l'acceptation et la valorisation de la diversité, ce qui peut impliquer une intervention pour protéger les droits des personnes en situation de handicap ou pour promouvoir l'inclusion et la tolérance dans la société."
      },
      {
        "id": "108_7",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des différences liées au handicap ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives de la diversité et en sensibilisant le public au respect des différences liées au handicap.",
          "Les médias sont inutiles dans le respect des différences liées au handicap, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives de la diversité et en sensibilisant le public au respect des différences liées au handicap.",
        "explanation": "Les médias ont un rôle important dans le respect des différences liées au handicap en promouvant des représentations positives de la diversité, en sensibilisant le public au respect des différences et en contribuant à la construction d'une société plus inclusive et tolérante."
      },
      {
        "id": "108_8",
        "type": "vrai-faux",
        "question": "Le respect des différences liées au handicap est un concept qui promeut la non-intervention dans les affaires des personnes en situation de handicap. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des différences liées au handicap encourage l'acceptation et la valorisation de la diversité, ce qui peut impliquer une intervention pour protéger les droits des personnes en situation de handicap ou pour promouvoir l'inclusion et la tolérance dans la société."
      }
    ]
  ],
  [
    "109",
    "EMC 6e - Diversité culturelle et religieuse",
    "EMC",
    "6eme",
    [
      {
        "id": "109_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter la diversité culturelle et religieuse ?",
        "options": [
          "Le respect de la diversité culturelle et religieuse favorise la tolérance, l'inclusion et la richesse culturelle dans la société.",
          "Le respect de la diversité culturelle et religieuse est inutile, car tout le monde devrait être pareil.",
          "Le respect de la diversité culturelle et religieuse est réservé aux élites politiques et économiques.",
          "Le respect de la diversité culturelle et religieuse est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect de la diversité culturelle et religieuse favorise la tolérance, l'inclusion et la richesse culturelle dans la société.",
        "explanation": "Le respect de la diversité culturelle et religieuse est important car il favorise la tolérance, l'inclusion et la richesse culturelle dans la société, permettant à chacun de se sentir accepté et valorisé pour ce qu'il est."
      },
      {
        "id": "109_2",
        "type": "vrai-faux",
        "question": "Le respect de la diversité culturelle et religieuse est un droit fondamental protégé par la loi.",
        "correct": True,
        "explanation": "C'est vrai. Le respect de la diversité culturelle et religieuse est un droit fondamental protégé par la loi, notamment à travers les lois sur la liberté de religion, la non-discrimination et les droits culturels."
      },
      {
        "id": "109_3",
        "type": "qcm",
        "question": "Quels sont les avantages de respecter la diversité culturelle et religieuse ?",
        "options": [
          "Le respect de la diversité culturelle et religieuse favorise la créativité, l'innovation et la richesse culturelle dans la société.",
          "Le respect de la diversité culturelle et religieuse est inutile, car tout le monde devrait être pareil.",
          "Le respect de la diversité culturelle et religieuse est réservé aux élites politiques et économiques.",
          "Le respect de la diversité culturelle et religieuse est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect de la diversité culturelle et religieuse favorise la créativité, l'innovation et la richesse culturelle dans la société.",
        "explanation": "Le respect de la diversité culturelle et religieuse favorise la créativité, l'innovation et la richesse culturelle dans la société, permettant à chacun de contribuer de manière unique et précieuse."
      },
      {
        "id": "109_4",
        "type": "vrai-faux",
        "question": "Le respect de la diversité culturelle et religieuse est un concept qui promeut la non-intervention dans les affaires des autres personnes.",
        "correct": False,
        "explanation": "C'est faux. Le respect de la diversité culturelle et religieuse encourage l'acceptation et la valorisation de la diversité, ce qui peut impliquer une intervention pour protéger les droits des personnes ou pour promouvoir l'inclusion et la tolérance dans la société."
      },
      {
        "id": "109_5",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect de la diversité culturelle et religieuse ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus à la diversité et en promouvant le respect de la diversité culturelle et religieuse.",
          "L'éducation est inutile dans le respect de la diversité culturelle et religieuse, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus à la diversité et en promouvant le respect de la diversité culturelle et religieuse.",
        "explanation": "L'éducation joue un rôle crucial dans le respect de la diversité culturelle et religieuse en sensibilisant les individus à la diversité, en promouvant le respect de la diversité et en encourageant l'inclusion et la tolérance dès le plus jeune âge."
      },
      {
        "id": "109_6",
        "type": "vrai-faux",
        "question": "Le respect de la diversité culturelle et religieuse est un concept qui promeut la non-intervention dans les affaires des autres personnes. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect de la diversité culturelle et religieuse encourage l'acceptation et la valorisation de la diversité, ce qui peut impliquer une intervention pour protéger les droits des personnes ou pour promouvoir l'inclusion et la tolérance dans la société."
      },
      {
        "id": "109_7",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect de la diversité culturelle et religieuse ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives de la diversité et en sensibilisant le public au respect de la diversité culturelle et religieuse.",
          "Les médias sont inutiles dans le respect de la diversité culturelle et religieuse, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives de la diversité et en sensibilisant le public au respect de la diversité culturelle et religieuse.",
        "explanation": "Les médias ont un rôle important dans le respect de la diversité culturelle et religieuse en promouvant des représentations positives de la diversité, en sensibilisant le public au respect de la diversité et en contribuant à la construction d'une société plus inclusive et tolérante."
      },
      {
        "id": "109_8",
        "type": "vrai-faux",
        "question": "Le respect de la diversité culturelle et religieuse est un concept qui promeut la non-intervention dans les affaires des autres personnes. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect de la diversité culturelle et religieuse encourage l'acceptation et la valorisation de la diversité, ce qui peut impliquer une intervention pour protéger les droits des personnes ou pour promouvoir l'inclusion et la tolérance dans la société."
      }
    ]
  ],
  [
    "110",
    "EMC 6e - Prévention harcèlement scolaire",
    "EMC",
    "6eme",
    [
      {
        "id": "110_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de prévenir le harcèlement scolaire ?",
        "options": [
          "La prévention du harcèlement scolaire contribue à créer un environnement scolaire sûr, respectueux et inclusif pour tous les élèves.",
          "La prévention du harcèlement scolaire est inutile, car le harcèlement fait partie de la vie.",
          "La prévention du harcèlement scolaire est réservée aux élites politiques et économiques.",
          "La prévention du harcèlement scolaire est limitée aux écoles uniquement."
        ],
        "correct_option": "La prévention du harcèlement scolaire contribue à créer un environnement scolaire sûr, respectueux et inclusif pour tous les élèves.",
        "explanation": "La prévention du harcèlement scolaire est essentielle pour créer un environnement scolaire sûr, respectueux et inclusif pour tous les élèves, en protégeant leur bien-être, en favorisant leur réussite académique et en promouvant des relations positives entre les élèves."
      },
      {
        "id": "110_2",
        "type": "vrai-faux",
        "question": "Le harcèlement scolaire est un comportement inacceptable qui peut avoir des conséquences graves sur la santé mentale et physique des victimes.",
        "correct": True,
        "explanation": "C'est vrai. Le harcèlement scolaire est un comportement inacceptable qui peut avoir des conséquences graves sur la santé mentale et physique des victimes, notamment l'anxiété, la dépression, le stress post-traumatique et, dans les cas extrêmes, le suicide. Il est important de prévenir le harcèlement scolaire pour protéger les élèves et promouvoir un environnement scolaire sain et respectueux."
      },
      {
        "id": "110_3",
        "type": "qcm",
        "question": "Que pouvez-vous faire si vous êtes témoin de harcèlement scolaire ?",
        "options": [
          "Il est important de signaler le harcèlement scolaire à un adulte de confiance, de soutenir la victime et de promouvoir un environnement scolaire respectueux.",
          "Il est préférable d'ignorer le harcèlement scolaire et de ne pas intervenir.",
          "Il est conseillé de rejoindre le harcèlement scolaire pour se protéger.",
          "Il est recommandé de filmer le harcèlement scolaire pour le partager sur les réseaux sociaux."
        ],
        "correct_option": "Il est important de signaler le harcèlement scolaire à un adulte de confiance, de soutenir la victime et de promouvoir un environnement scolaire respectueux.",
        "explanation": "Si vous êtes témoin de harcèlement scolaire, il est crucial de signaler la situation à un adulte de confiance, de soutenir la victime et de contribuer à créer un environnement scolaire respectueux et inclusif."
      },
      {
        "id": "110_4",
        "type": "vrai-faux",
        "question": "Le harcèlement scolaire est un problème qui ne concerne que les élèves et les écoles.",
        "correct": False,
        "explanation": "C'est faux. Le harcèlement scolaire est un problème qui concerne non seulement les élèves et les écoles, mais aussi les familles, les communautés et la société dans son ensemble. La prévention du harcèlement scolaire nécessite une approche collective impliquant tous les acteurs concernés pour créer un environnement sûr et respectueux pour tous les élèves."
      },
      {
        "id": "110_5",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans la prévention du harcèlement scolaire ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les élèves au harcèlement scolaire, en promouvant des comportements respectueux et en encourageant la solidarité entre les élèves.",
          "L'éducation est inutile dans la prévention du harcèlement scolaire, car les élèves peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les élèves au harcèlement scolaire, en promouvant des comportements respectueux et en encourageant la solidarité entre les élèves.",
        "explanation": "L'éducation joue un rôle crucial dans la prévention du harcèlement scolaire en sensibilisant les élèves à ce phénomène, en promouvant des comportements respectueux et en encourageant la solidarité entre les élèves pour créer un environnement scolaire sûr et inclusif."
      },
      {
        "id": "110_6",
        "type": "vrai-faux",
        "question": "Le harcèlement scolaire est un problème qui ne peut être résolu que par des sanctions disciplinaires contre les auteurs.",
        "correct": False,
        "explanation": "C'est faux. Bien que des sanctions disciplinaires puissent être nécessaires pour traiter le harcèlement scolaire, il est également important d'adopter une approche préventive qui inclut l'éducation, le soutien aux victimes, la promotion de comportements respectueux et la création d'un environnement scolaire inclusif pour résoudre efficacement le problème du harcèlement scolaire."
      },
      {
        "id": "110_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans la prévention du harcèlement scolaire ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants au harcèlement scolaire, en encourageant des comportements respectueux et en collaborant avec les écoles pour prévenir le harcèlement.",
          "Les parents sont inutiles dans la prévention du harcèlement scolaire, car les élèves peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants au harcèlement scolaire, en encourageant des comportements respectueux et en collaborant avec les écoles pour prévenir le harcèlement.",
        "explanation": "Les parents jouent un rôle crucial dans la prévention du harcèlement scolaire en sensibilisant leurs enfants à ce phénomène, en encourageant des comportements respectueux et en collaborant avec les écoles pour créer un environnement scolaire sûr et inclusif."
      },
      {
        "id": "110_8",
        "type": "vrai-faux",
        "question": "Le harcèlement scolaire est un problème qui ne peut être résolu que par des sanctions disciplinaires contre les auteurs. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Bien que des sanctions disciplinaires puissent être nécessaires pour traiter le harcèlement scolaire, il est également important d'adopter une approche préventive qui inclut l'éducation, le soutien aux victimes, la promotion de comportements respectueux et la création d'un environnement scolaire inclusif pour résoudre efficacement le problème du harcèlement scolaire."
      }
    ]
  ],
  [
    "111",
    "EMC 6e - Droits de l’enfant (Convention ONU)",
    "EMC",
    "6eme",
    [
      {
        "id": "111_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les droits de l'enfant tels que définis par la Convention des Nations Unies ?",
        "options": [
          "Le respect des droits de l'enfant garantit la protection, le bien-être et le développement des enfants, en leur permettant de vivre dans un environnement sûr et épanouissant.",
          "Le respect des droits de l'enfant est inutile, car les enfants n'ont pas besoin de droits spécifiques.",
          "Le respect des droits de l'enfant est réservé aux élites politiques et économiques.",
          "Le respect des droits de l'enfant est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des droits de l'enfant garantit la protection, le bien-être et le développement des enfants, en leur permettant de vivre dans un environnement sûr et épanouissant.",
        "explanation": "Le respect des droits de l'enfant tels que définis par la Convention des Nations Unies est crucial pour garantir la protection, le bien-être et le développement des enfants, en leur permettant de vivre dans un environnement sûr, épanouissant et respectueux de leurs besoins et de leur dignité."
      },
      {
        "id": "111_2",
        "type": "vrai-faux",
        "question": "Les droits de l'enfant sont des droits fondamentaux protégés par la loi.",
        "correct": True,
        "explanation": "C'est vrai. Les droits de l'enfant sont des droits fondamentaux protégés par la loi, notamment à travers la Convention des Nations Unies relative aux droits de l'enfant, qui établit les droits civils, politiques, économiques, sociaux et culturels des enfants."
      },
      {
        "id": "111_3",
        "type": "qcm",
        "question": "Quels sont les avantages de respecter les droits de l'enfant ?",
        "options": [
          "Le respect des droits de l'enfant favorise le développement, la protection et le bien-être des enfants, en leur permettant de vivre dans un environnement sûr et épanouissant.",
          "Le respect des droits de l'enfant est inutile, car les enfants n'ont pas besoin de droits spécifiques.",
          "Le respect des droits de l'enfant est réservé aux élites politiques et économiques.",
          "Le respect des droits de l'enfant est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des droits de l'enfant favorise le développement, la protection et le bien-être des enfants, en leur permettant de vivre dans un environnement sûr et épanouissant.",
        "explanation": "Le respect des droits de l'enfant favorise le développement, la protection et le bien-être des enfants, en leur permettant de vivre dans un environnement sûr, épanouissant et respectueux de leurs besoins et de leur dignité."
      },
      {
        "id": "111_4",
        "type": "vrai-faux",
        "question": "Le respect des droits de l'enfant est un concept qui promeut la non-intervention dans les affaires des enfants.",
        "correct": False,
        "explanation": "C'est faux. Le respect des droits de l'enfant encourage l'acceptation et la valorisation des enfants en tant qu'individus à part entière, ce qui peut impliquer une intervention pour protéger leurs droits ou pour promouvoir leur bien-être dans la société."
      },
      {
        "id": "111_5",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des droits de l'enfant ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux droits de l'enfant et en promouvant le respect de ces droits.",
          "L'éducation est inutile dans le respect des droits de l'enfant, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux droits de l'enfant et en promouvant le respect de ces droits.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux droits de l'enfant et en promouvant le respect de ces droits."
      },
      {
        "id": "111_6",
        "type": "vrai-faux",
        "question": "Le respect des droits de l'enfant est un concept qui promeut la non-intervention dans les affaires des enfants. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des droits de l'enfant encourage l'acceptation et la valorisation des enfants en tant qu'individus à part entière, ce qui peut impliquer une intervention pour protéger leurs droits ou pour promouvoir leur bien-être dans la société."
      },
      {
        "id": "111_7",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des droits de l'enfant ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives des enfants et en sensibilisant le public au respect des droits de l'enfant.",
          "Les médias sont inutiles dans le respect des droits de l'enfant, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives des enfants et en sensibilisant le public au respect des droits de l'enfant.",
        "explanation": "Les médias ont un rôle important dans le respect des droits de l'enfant en promouvant des représentations positives des enfants, en sensibilisant le public au respect de leurs droits et en contribuant à la construction d'une société plus inclusive et respectueuse des enfants."
      },
      {
        "id": "111_8",
        "type": "vrai-faux",
        "question": "Le respect des droits de l'enfant est un concept qui promeut la non-intervention dans les affaires des enfants. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des droits de l'enfant encourage l'acceptation et la valorisation des enfants en tant qu'individus à part entière, ce qui peut impliquer une intervention pour protéger leurs droits ou pour promouvoir leur bien-être dans la société."
      }
    ]
  ],
  [
    "112",
    "EMC 6e - Devoirs et sanctions",
    "EMC",
    "6eme",
    [
      {
        "id": "112_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les devoirs et les sanctions dans la société ?",
        "options": [
          "Le respect des devoirs et des sanctions contribue à maintenir l'ordre, la justice et la sécurité dans la société.",
          "Le respect des devoirs et des sanctions est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des devoirs et des sanctions est réservé aux élites politiques et économiques.",
          "Le respect des devoirs et des sanctions est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des devoirs et des sanctions contribue à maintenir l'ordre, la justice et la sécurité dans la société.",
        "explanation": "Le respect des devoirs et des sanctions est essentiel pour maintenir l'ordre, la justice et la sécurité dans la société, car il garantit que les règles sont respectées et que les comportements inappropriés sont corrigés."
      },
      {
        "id": "112_2",
        "type": "vrai-faux",
        "question": "Le respect des devoirs et des sanctions est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des devoirs et des sanctions encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "112_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des devoirs et des sanctions ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux devoirs et aux sanctions et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des devoirs et des sanctions, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux devoirs et aux sanctions et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux devoirs et aux sanctions, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "112_4",
        "type": "vrai-faux",
        "question": "Le respect des devoirs et des sanctions signifie toujours accepter sans réfléchir toute règle.",
        "correct": False,
        "explanation": "C'est faux. Respecter les devoirs et sanctions implique aussi une réflexion sur la justice et l'éthique, pas une acceptation aveugle de toutes les règles."
      },
      {
        "id": "112_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des devoirs et des sanctions ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des devoirs et des sanctions et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des devoirs et des sanctions, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des devoirs et des sanctions et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des devoirs et des sanctions en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "112_6",
        "type": "vrai-faux",
        "question": "Le respect des devoirs et des sanctions est un concept qui se limite à l'école uniquement.",
        "correct": False,
        "explanation": "C'est faux. Le respect des devoirs et des sanctions s'applique dans la société entière, pas seulement à l'école."
      },
      {
        "id": "112_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des devoirs et des sanctions ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux devoirs et aux sanctions et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des devoirs et des sanctions, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux devoirs et aux sanctions et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des devoirs et des sanctions en sensibilisant leurs enfants à ces règles, en promouvant leur respect et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "112_8",
        "type": "vrai-faux",
        "question": "Le respect des devoirs et des sanctions peut aider à construire une société plus juste.",
        "correct": True,
        "explanation": "C'est vrai. Le respect des devoirs et des sanctions contribue à une société plus juste en assurant que les règles sont appliquées équitablement et que les comportements respectueux sont encouragés."
      }
    ]
  ],
  [
    "113",
    "EMC 6e - Lois et justice (tribunal, police)",
    "EMC",
    "6eme",
    [
      {
        "id": "113_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les lois et la justice dans la société ?",
        "options": [
          "Le respect des lois et de la justice garantit la protection des droits, la sécurité et l'équité dans la société.",
          "Le respect des lois et de la justice est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des lois et de la justice est réservé aux élites politiques et économiques.",
          "Le respect des lois et de la justice est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des lois et de la justice garantit la protection des droits, la sécurité et l'équité dans la société.",
        "explanation": "Le respect des lois et de la justice est essentiel pour garantir la protection des droits, la sécurité et l'équité dans la société, en assurant que les règles sont respectées et que les comportements inappropriés sont corrigés de manière juste et équitable."
      },
      {
        "id": "113_2",
        "type": "vrai-faux",
        "question": "Le respect des lois et de la justice est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des lois et de la justice encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "113_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des lois et de la justice ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux lois et à la justice et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des lois et de la justice, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux lois et à la justice et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux lois et à la justice, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "113_4",
        "type": "vrai-faux",
        "question": "Le respect des lois et de la justice est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des lois et de la justice encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "113_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des lois et de la justice ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des lois et de la justice et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des lois et de la justice, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des lois et de la justice et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des lois et de la justice en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "113_6",
        "type": "vrai-faux",
        "question": "Le respect des lois et de la justice est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des lois et de la justice encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "113_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des lois et de la justice ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux lois et à la justice et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des lois et de la justice, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux lois et à la justice et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des lois et de la justice en sensibilisant leurs enfants à ces règles, en promouvant leur respect et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "113_8",
        "type": "vrai-faux",
        "question": "Le respect des lois et de la justice est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des lois et de la justice encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      }
    ]
  ],
  [
    "114",
    "EMC 6e - Propriété et vol",
    "EMC",
    "6eme",
    [
      {
        "id": "114_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter la propriété d'autrui et de prévenir le vol ?",
        "options": [
          "Le respect de la propriété d'autrui et la prévention du vol contribuent à maintenir l'ordre, la justice et la sécurité dans la société.",
          "Le respect de la propriété d'autrui et la prévention du vol sont inutiles, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect de la propriété d'autrui et la prévention du vol sont réservés aux élites politiques et économiques.",
          "Le respect de la propriété d'autrui et la prévention du vol sont limités aux écoles uniquement."
        ],
        "correct_option": "Le respect de la propriété d'autrui et la prévention du vol contribuent à maintenir l'ordre, la justice et la sécurité dans la société.",
        "explanation": "Le respect de la propriété d'autrui et la prévention du vol sont essentiels pour maintenir l'ordre, la justice et la sécurité dans la société, en assurant que les biens des individus sont protégés et que les comportements inappropriés sont corrigés de manière juste et équitable."
      },
      {
        "id": "114_2",
        "type": "vrai-faux",
        "question": "Le respect de la propriété d'autrui et la prévention du vol sont des concepts qui promeuvent la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect de la propriété d'autrui et la prévention du vol encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "114_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect de la propriété d'autrui et la prévention du vol ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus au respect de la propriété d'autrui et à la prévention du vol et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect de la propriété d'autrui et la prévention du vol, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus au respect de la propriété d'autrui et à la prévention du vol et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus au respect de la propriété d'autrui et à la prévention du vol, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "114_4",
        "type": "vrai-faux",
        "question": "Le respect de la propriété d'autrui et la prévention du vol sont des concepts qui promeuvent la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect de la propriété d'autrui et la prévention du vol encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "114_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect de la propriété d'autrui et la prévention du vol ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect de la propriété d'autrui et de la prévention du vol et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect de la propriété d'autrui et la prévention du vol, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect de la propriété d'autrui et de la prévention du vol et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect de la propriété d'autrui et la prévention du vol en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "114_6",
        "type": "vrai-faux",
        "question": "Le respect de la propriété d'autrui et la prévention du vol sont des concepts qui promeuvent la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect de la propriété d'autrui et la prévention du vol encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "114_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect de la propriété d'autrui et la prévention du vol ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants au respect de la propriété d'autrui et à la prévention du vol et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect de la propriété d'autrui et la prévention du vol, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants au respect de la propriété d'autrui et à la prévention du vol et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect de la propriété d'autrui et la prévention du vol en sensibilisant leurs enfants à ces règles, en promouvant leur respect et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "114_8",
        "type": "vrai-faux",
        "question": "Le respect de la propriété d'autrui et la prévention du vol sont des concepts qui promeuvent la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect de la propriété d'autrui et la prévention du vol encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      }
    ]
  ],
  [
    "115",
    "EMC 6e - Sécurité routière",
    "EMC",
    "6eme",
    [
      {
        "id": "115_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de sécurité routière ?",
        "options": [
          "Le respect des règles de sécurité routière garantit la protection de tous les usagers de la route, en réduisant les risques d'accidents et en assurant la sécurité de tous.",
          "Le respect des règles de sécurité routière est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de sécurité routière est réservé aux élites politiques et économiques.",
          "Le respect des règles de sécurité routière est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de sécurité routière garantit la protection de tous les usagers de la route, en réduisant les risques d'accidents et en assurant la sécurité de tous.",
        "explanation": "Le respect des règles de sécurité routière est essentiel pour protéger tous les usagers de la route, réduire les risques d'accidents et assurer la sécurité de tous."
      },
      {
        "id": "115_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de sécurité routière est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de sécurité routière encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "115_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de sécurité routière ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de sécurité routière et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de sécurité routière, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de sécurité routière et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de sécurité routière, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "115_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de sécurité routière est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de sécurité routière encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "115_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de sécurité routière ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de sécurité routière et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles de sécurité routière, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de sécurité routière et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles de sécurité routière en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "115_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de sécurité routière est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de sécurité routière encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "115_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de sécurité routière ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de sécurité routière et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de sécurité routière, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de sécurité routière et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de sécurité routière en sensibilisant leurs enfants à ces règles, en promouvant leur respect et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "115_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de sécurité routière est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de sécurité routière encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      }
    ]
  ],
  [
    "116",
    "EMC 6e - Respect biens communs",
    "EMC",
    "6eme",
    [
      {
        "id": "116_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les biens communs ?",
        "options": [
          "Le respect des biens communs garantit la préservation de l'environnement, la sécurité et le bien-être de tous les membres de la société.",
          "Le respect des biens communs est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des biens communs est réservé aux élites politiques et économiques.",
          "Le respect des biens communs est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des biens communs garantit la préservation de l'environnement, la sécurité et le bien-être de tous les membres de la société.",
        "explanation": "Le respect des biens communs est essentiel pour garantir la préservation de l'environnement, la sécurité et le bien-être de tous les membres de la société, en assurant que les ressources partagées sont utilisées de manière responsable et durable."
      },
      {
        "id": "116_2",
        "type": "vrai-faux",
        "question": "Le respect des biens communs est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des biens communs encourage l'acceptation et la valorisation de la responsabilité collective, ce qui peut impliquer une intervention pour protéger les ressources partagées ou pour promouvoir leur utilisation responsable dans la société."
      },
      {
        "id": "116_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des biens communs ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus au respect des biens communs et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des biens communs, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus au respect des biens communs et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus au respect des biens communs, en promouvant le respect de ces règles et en encourageant la responsabilité collective pour garantir la préservation de l'environnement, la sécurité et le bien-être de tous les membres de la société."
      },
      {
        "id": "116_4",
        "type": "vrai-faux",
        "question": "Le respect des biens communs est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des biens communs encourage l'acceptation et la valorisation de la responsabilité collective, ce qui peut impliquer une intervention pour protéger les ressources partagées ou pour promouvoir leur utilisation responsable dans la société."
      },
      {
        "id": "116_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des biens communs ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des biens communs et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des biens communs, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des biens communs et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des biens communs en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "116_6",
        "type": "vrai-faux",
        "question": "Le respect des biens communs est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des biens communs encourage l'acceptation et la valorisation de la responsabilité collective, ce qui peut impliquer une intervention pour protéger les ressources partagées ou pour promouvoir leur utilisation responsable dans la société."
      },
      {
        "id": "116_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des biens communs ?",
        "options": [
          "Les parents ont un rôle important en enseignant à leurs enfants le respect des biens communs et en les encourageant à adopter des comportements responsables.",
          "Les parents n'ont aucun rôle dans le respect des biens communs, car cela relève uniquement de l'école.",
          "Les parents doivent uniquement se concentrer sur l'éducation académique de leurs enfants.",
          "Les parents doivent laisser leurs enfants apprendre par eux-mêmes sans intervention."
        ],
        "correct_option": "Les parents ont un rôle important en enseignant à leurs enfants le respect des biens communs et en les encourageant à adopter des comportements responsables.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des biens communs en enseignant à leurs enfants l'importance de ces règles et en les encourageant à adopter des comportements responsables pour garantir la préservation de l'environnement, la sécurité et le bien-être de tous les membres de la société."
      },
      {
        "id": "116_8",
        "type": "vrai-faux",
        "question": "Le respect des biens communs est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des biens communs encourage l'acceptation et la valorisation de la responsabilité collective, ce qui peut impliquer une intervention pour protéger les ressources partagées ou pour promouvoir leur utilisation responsable dans la société."
      }
    ]
  ],
  [
    "117",
    "EMC 6e - Égalité devant la loi",
    "EMC",
    "6eme",
    [
      {
        "id": "117_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les devoirs et les sanctions dans la société ?",
        "options": [
          "Le respect des devoirs et des sanctions garantit la protection des droits, la sécurité et l'équité dans la société.",
          "Le respect des devoirs et des sanctions est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des devoirs et des sanctions est réservé aux élites politiques et économiques.",
          "Le respect des devoirs et des sanctions est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des devoirs et des sanctions garantit la protection des droits, la sécurité et l'équité dans la société.",
        "explanation": "Le respect des devoirs et des sanctions est essentiel pour garantir la protection des droits, la sécurité et l'équité dans la société, en assurant que les règles sont respectées et que les comportements inappropriés sont corrigés de manière juste et équitable."
      },
      {
        "id": "117_2",
        "type": "vrai-faux",
        "question": "Le respect des devoirs et des sanctions est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des devoirs et des sanctions encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "117_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des devoirs et des sanctions ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux devoirs et aux sanctions et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des devoirs et des sanctions, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux devoirs et aux sanctions et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux devoirs et aux sanctions, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "117_4",
        "type": "vrai-faux",
        "question": "Le respect des devoirs et des sanctions est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des devoirs et des sanctions encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "117_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des devoirs et des sanctions ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des devoirs et des sanctions et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des devoirs et des sanctions, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des devoirs et des sanctions et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des devoirs et des sanctions en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "117_6",
        "type": "vrai-faux",
        "question": "Le respect des devoirs et des sanctions est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des devoirs et des sanctions encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      },
      {
        "id": "117_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des devoirs et des sanctions ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux devoirs et aux sanctions et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des devoirs et des sanctions, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux devoirs et aux sanctions et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des devoirs et des sanctions en sensibilisant leurs enfants à ces règles, en promouvant leur respect et en encourageant la responsabilité individuelle pour maintenir l'ordre, la justice et la sécurité dans la société."
      },
      {
        "id": "117_8",
        "type": "vrai-faux",
        "question": "Le respect des devoirs et des sanctions est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des devoirs et des sanctions encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect des règles dans la société."
      }
    ]
  ],
  [
    "118",
    "EMC 6e - Institutions locales (mairie)",
    "EMC",
    "6eme",
    [
      {
        "id": "118_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les institutions locales telles que la mairie ?",
        "options": [
          "Le respect des institutions locales garantit le bon fonctionnement de la démocratie, la participation citoyenne et la gestion efficace des services publics.",
          "Le respect des institutions locales est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des institutions locales est réservé aux élites politiques et économiques.",
          "Le respect des institutions locales est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des institutions locales garantit le bon fonctionnement de la démocratie, la participation citoyenne et la gestion efficace des services publics.",
        "explanation": "Le respect des institutions locales telles que la mairie est essentiel pour garantir le bon fonctionnement de la démocratie, encourager la participation citoyenne et assurer une gestion efficace des services publics."
      },
      {
        "id": "118_2",
        "type": "vrai-faux",
        "question": "Le respect des institutions locales telles que la mairie est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des institutions locales encourage l'acceptation et la valorisation de la responsabilité collective, ce qui peut impliquer une intervention pour soutenir le bon fonctionnement de ces institutions et promouvoir la participation citoyenne dans la société."
      },
      {
        "id": "118_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des institutions locales telles que la mairie ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus au respect des institutions locales et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des institutions locales, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus au respect des institutions locales et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus au respect des institutions locales, en promouvant le respect de ces règles et en encourageant la responsabilité collective pour garantir le bon fonctionnement de la démocratie, encourager la participation citoyenne et assurer une gestion efficace des services publics."
      },
      {
        "id": "118_4",
        "type": "vrai-faux",
        "question": "Le respect des institutions locales telles que la mairie est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des institutions locales encourage l'acceptation et la valorisation de la responsabilité collective, ce qui peut impliquer une intervention pour soutenir le bon fonctionnement de ces institutions et promouvoir la participation citoyenne dans la société."
      },
      {
        "id": "118_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des institutions locales telles que la mairie ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des institutions locales et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des institutions locales, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des institutions locales et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des institutions locales en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "118_6",
        "type": "vrai-faux",
        "question": "Le respect des institutions locales telles que la mairie est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des institutions locales encourage l'acceptation et la valorisation de la responsabilité collective, ce qui peut impliquer une intervention pour soutenir le bon fonctionnement de ces institutions et promouvoir la participation citoyenne dans la société."
      },
      {
        "id": "118_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des institutions locales telles que la mairie ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants au respect des institutions locales et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des institutions locales, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants au respect des institutions locales et en promouvant le respect de ces règles.",
        "explanation": "Les parents ont un rôle important dans le respect des institutions locales en sensibilisant leurs enfants au respect de ces règles et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "118_8",
        "type": "vrai-faux",
        "question": "Le respect des institutions locales telles que la mairie est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des institutions locales encourage l'acceptation et la valorisation de la responsabilité collective, ce qui peut impliquer une intervention pour soutenir le bon fonctionnement de ces institutions et promouvoir la participation citoyenne dans la société."
      }
    ]
  ],
  [
    "119",
    "EMC 6e - Vrai/faux infos (fake news)",
    "EMC",
    "6eme",
    [
      {
        "id": "119_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de vérification des informations pour éviter les fake news ?",
        "options": [
          "Le respect des règles de vérification des informations garantit la diffusion d'informations fiables, la protection contre la désinformation et la promotion d'une société informée et responsable.",
          "Le respect des règles de vérification des informations est inutile, car les individus peuvent croire ce qu'ils veulent.",
          "Le respect des règles de vérification des informations est réservé aux élites politiques et économiques.",
          "Le respect des règles de vérification des informations est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de vérification des informations garantit la diffusion d'informations fiables, la protection contre la désinformation et la promotion d'une société informée et responsable.",
        "explanation": "Le respect des règles de vérification des informations est essentiel pour garantir la diffusion d'informations fiables, protéger contre la désinformation et promouvoir une société informée et responsable."
      },
      {
        "id": "119_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de vérification des informations pour éviter les fake news est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vérification des informations encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "119_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de vérification des informations pour éviter les fake news ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de vérification des informations et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de vérification des informations, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de vérification des informations et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de vérification des informations, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la diffusion d'informations fiables, protéger contre la désinformation et promouvoir une société informée et responsable."
      },
      {
        "id": "119_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de vérification des informations pour éviter les fake news est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vérification des informations encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "119_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de vérification des informations pour éviter les fake news ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de vérification des informations et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles de vérification des informations, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de vérification des informations et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles de vérification des informations en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "119_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de vérification des informations pour éviter les fake news est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vérification des informations encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "119_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de vérification des informations pour éviter les fake news ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de vérification des informations et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de vérification des informations, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de vérification des informations et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de vérification des informations en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la diffusion d'informations fiables, protéger contre la désinformation et promouvoir une société informée et responsable."
      },
      {
        "id": "119_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de vérification des informations pour éviter les fake news est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vérification des informations encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "120",
    "EMC 6e - Débat argumenté",
    "EMC",
    "6eme",
    [
      {
        "id": "120_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles du débat argumenté ?",
        "options": [
          "Le respect des règles du débat argumenté garantit la promotion d'une discussion constructive, le respect des opinions divergentes et la construction d'une société démocratique et pluraliste.",
          "Le respect des règles du débat argumenté est inutile, car les individus peuvent exprimer leurs opinions comme ils le souhaitent.",
          "Le respect des règles du débat argumenté est réservé aux élites politiques et économiques.",
          "Le respect des règles du débat argumenté est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles du débat argumenté garantit la promotion d'une discussion constructive, le respect des opinions divergentes et la construction d'une société démocratique et pluraliste.",
        "explanation": "Le respect des règles du débat argumenté est essentiel pour garantir une discussion constructive, le respect des opinions divergentes et la construction d'une société démocratique et pluraliste."
      },
      {
        "id": "120_2",
        "type": "vrai-faux",
        "question": "Le respect des règles du débat argumenté est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles du débat argumenté encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "120_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles du débat argumenté ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles du débat argumenté et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles du débat argumenté, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles du débat argumenté et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles du débat argumenté, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir une discussion constructive, le respect des opinions divergentes et la construction d'une société démocratique et pluraliste."
      },
      {
        "id": "120_4",
        "type": "vrai-faux",
        "question": "Le respect des règles du débat argumenté est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles du débat argumenté encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "120_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles du débat argumenté ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles du débat argumenté et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles du débat argumenté, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles du débat argumenté et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles du débat argumenté en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "120_6",
        "type": "vrai-faux",
        "question": "Le respect des règles du débat argumenté est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles du débat argumenté encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "120_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles du débat argumenté ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles du débat argumenté et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles du débat argumenté, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles du débat argumenté et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles du débat argumenté en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir une discussion constructive, le respect des opinions divergentes et la construction d'une société démocratique et pluraliste."
      },
      {
        "id": "120_8",
        "type": "vrai-faux",
        "question": "Le respect des règles du débat argumenté est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles du débat argumenté encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "121",
    "EMC 6e - Médias et publicité",
    "EMC",
    "6eme",
    [
      {
        "id": "121_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de consommation responsable face aux médias et à la publicité ?",
        "options": [
          "Le respect des règles de consommation responsable garantit la protection des consommateurs, la promotion de pratiques commerciales éthiques et la construction d'une société plus durable et équitable.",
          "Le respect des règles de consommation responsable est inutile, car les individus peuvent consommer comme ils le souhaitent.",
          "Le respect des règles de consommation responsable est réservé aux élites politiques et économiques.",
          "Le respect des règles de consommation responsable est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de consommation responsable garantit la protection des consommateurs, la promotion de pratiques commerciales éthiques et la construction d'une société plus durable et équitable.",
        "explanation": "Le respect des règles de consommation responsable est essentiel pour garantir la protection des consommateurs, la promotion de pratiques commerciales éthiques et la construction d'une société plus durable et équitable."
      },
      {
        "id": "121_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de consommation responsable face aux médias et à la publicité est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de consommation responsable encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "121_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de consommation responsable face aux médias et à la publicité ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de consommation responsable et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de consommation responsable, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de consommation responsable et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de consommation responsable, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la protection des consommateurs, la promotion de pratiques commerciales éthiques et la construction d'une société plus durable et équitable."
      },
      {
        "id": "121_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de consommation responsable signifie ignorer les informations des médias.",
        "correct": False,
        "explanation": "C'est faux. La consommation responsable implique de vérifier les informations et de ne pas se laisser influencer aveuglément par la publicité."
      },
      {
        "id": "121_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de consommation responsable face aux médias et à la publicité ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de consommation responsable et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles de consommation responsable, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de consommation responsable et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles de consommation responsable en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "121_6",
        "type": "vrai-faux",
        "question": "La consommation responsable face aux médias consiste à acheter le premier produit vu en publicité.",
        "correct": False,
        "explanation": "C'est faux. La consommation responsable consiste à réfléchir avant d'acheter, pas à céder immédiatement aux publicités."
      },
      {
        "id": "121_7",
        "type": "qcm",
        "question": "Quel est l'impact des médias sur le respect des règles de consommation responsable ?",
        "options": [
          "Les médias n'ont aucun impact sur le respect des règles de consommation responsable.",
          "Les médias peuvent influencer positivement ou négativement le respect des règles de consommation responsable.",
          "Les médias sont uniquement responsables de la promotion des comportements irresponsables.",
          "Les médias sont uniquement responsables de la promotion des comportements responsables."
        ],
        "correct_option": "Les médias peuvent influencer positivement ou négativement le respect des règles de consommation responsable.",
        "explanation": "Les médias ont un impact significatif sur le respect des règles de consommation responsable, car ils peuvent influencer les comportements et les attitudes du public, que ce soit de manière positive ou négative."
      },
      {
        "id": "121_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de consommation responsable face aux médias aide à protéger les consommateurs contre des choix impulsifs.",
        "correct": True,
        "explanation": "C'est vrai. La consommation responsable aide à protéger les consommateurs en leur permettant de faire des choix réfléchi et non impulsifs."
      }
    ]
  ],
  [
    "122",
    "EMC 6e - Choix éthiques (mensonge, aide)",
    "EMC",
    "6eme",
    [
      {
        "id": "122_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles éthiques dans les choix de mensonge ou d'aide ?",
        "options": [
          "Le respect des règles éthiques garantit la promotion de comportements moraux, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles éthiques est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles éthiques est réservé aux élites politiques et économiques.",
          "Le respect des règles éthiques est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles éthiques garantit la promotion de comportements moraux, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles éthiques est essentiel pour garantir la promotion de comportements moraux, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "122_2",
        "type": "vrai-faux",
        "question": "Le respect des règles éthiques dans les choix de mensonge ou d'aide est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles éthiques encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "122_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles éthiques dans les choix de mensonge ou d'aide ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles éthiques et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles éthiques, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles éthiques et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles éthiques, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de comportements moraux, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "122_4",
        "type": "vrai-faux",
        "question": "Le respect des règles éthiques dans les choix de mensonge ou d'aide est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles éthiques encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "122_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles éthiques dans les choix de mensonge ou d'aide ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles éthiques et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles éthiques, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles éthiques et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles éthiques en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "122_6",
        "type": "vrai-faux",
        "question": "Le respect des règles éthiques dans les choix de mensonge ou d'aide est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles éthiques encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "122_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles éthiques dans les choix de mensonge ou d'aide ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles éthiques et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles éthiques, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles éthiques et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles éthiques en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de comportements moraux, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "122_8",
        "type": "vrai-faux",
        "question": "Le respect des règles éthiques dans les choix de mensonge ou d'aide est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles éthiques encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "123",
    "EMC 6e - Liberté conscience",
    "EMC",
    "6eme",
    [
      {
        "id": "123_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de liberté de conscience ?",
        "options": [
          "Le respect des règles de liberté de conscience garantit la promotion de la diversité des opinions, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive.",
          "Le respect des règles de liberté de conscience est inutile, car les individus peuvent croire ce qu'ils veulent.",
          "Le respect des règles de liberté de conscience est réservé aux élites politiques et économiques.",
          "Le respect des règles de liberté de conscience est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de liberté de conscience garantit la promotion de la diversité des opinions, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive.",
        "explanation": "Le respect des règles de liberté de conscience est essentiel pour garantir la promotion de la diversité des opinions, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive."
      },
      {
        "id": "123_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de liberté de conscience est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de liberté de conscience encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "123_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de liberté de conscience ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de liberté de conscience et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de liberté de conscience, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de liberté de conscience et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de liberté de conscience, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la diversité des opinions, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive."
      },
      {
        "id": "123_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de liberté de conscience est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de liberté de conscience encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "123_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de liberté de conscience ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de liberté de conscience et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles de liberté de conscience, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de liberté de conscience et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles de liberté de conscience en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "123_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de liberté de conscience est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de liberté de conscience encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "123_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de liberté de conscience ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de liberté de conscience et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de liberté de conscience, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de liberté de conscience et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de liberté de conscience en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la diversité des opinions, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive."
      },
      {
        "id": "123_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de liberté de conscience est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de liberté de conscience encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "124",
    "EMC 6e - Laïcité à l’école",
    "EMC",
    "6eme",
    [
      {
        "id": "124_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de laïcité à l'école ?",
        "options": [
          "Le respect des règles de laïcité à l'école garantit la promotion de la liberté de conscience, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive.",
          "Le respect des règles de laïcité à l'école est inutile, car les individus peuvent croire ce qu'ils veulent.",
          "Le respect des règles de laïcité à l'école est réservé aux élites politiques et économiques.",
          "Le respect des règles de laïcité à l'école est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de laïcité à l'école garantit la promotion de la liberté de conscience, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive.",
        "explanation": "Le respect des règles de laïcité à l'école est essentiel pour garantir la promotion de la liberté de conscience, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive."
      },
      {
        "id": " 124_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de laïcité à l'école est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de laïcité à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "124_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de laïcité à l'école ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de laïcité et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de laïcité, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de laïcité et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de laïcité, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la liberté de conscience, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive."
      },
      {
        "id": "124_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de laïcité à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de laïcité à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "124_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de laïcité à l'école ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de laïcité et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles de laïcité, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de laïcité et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles de laïcité à l'école en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "124_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de laïcité à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de laïcité à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "124_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de laïcité à l'école ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de laïcité et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de laïcité, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de laïcité et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de laïcité à l'école en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la liberté de conscience, le respect des croyances individuelles et la construction d'une société plus tolérante et inclusive."
      },
      {
        "id": "124_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de laïcité à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de laïcité à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "125",
    "EMC 6e - Histoire droits humains",
    "EMC",
    "6eme",
    [
      {
        "id": "125_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles des droits humains ?",
        "options": [
          "Le respect des règles des droits humains garantit la promotion de la dignité humaine, le respect des libertés fondamentales et la construction d'une société plus juste et égalitaire.",
          "Le respect des règles des droits humains est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles des droits humains est réservé aux élites politiques et économiques.",
          "Le respect des règles des droits humains est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles des droits humains garantit la promotion de la dignité humaine, le respect des libertés fondamentales et la construction d'une société plus juste et égalitaire.",
        "explanation": "Le respect des règles des droits humains est essentiel pour garantir la promotion de la dignité humaine, le respect des libertés fondamentales et la construction d'une société plus juste et égalitaire."
      },
      {
        "id": "125_2",
        "type": "vrai-faux",
        "question": "Le respect des règles des droits humains est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles des droits humains encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "125_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles des droits humains ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles des droits humains et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles des droits humains, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles des droits humains et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles des droits humains, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la dignité humaine, le respect des libertés fondamentales et la construction d'une société plus juste et égalitaire."
      },
      {
        "id": "125_4",
        "type": "vrai-faux",
        "question": "Le respect des règles des droits humains est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles des droits humains encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "125_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles des droits humains ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles des droits humains et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles des droits humains, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles des droits humains et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles des droits humains en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "125_6",
        "type": "vrai-faux",
        "question": "Le respect des règles des droits humains est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles des droits humains encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "125_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles des droits humains ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles des droits humains et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles des droits humains, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles des droits humains et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles des droits humains en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la dignité humaine, le respect des libertés fondamentales et la construction d'une société plus juste et égalitaire."
      },
      {
        "id": "125_8",
        "type": "vrai-faux",
        "question": "Le respect des règles des droits humains est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles des droits humains encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "126",
    "EMC 6e - Jugement moral cas concrets",
    "EMC",
    "6eme",
    [
      {
        "id": "126_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de jugement moral dans les cas concrets ?",
        "options": [
          "Le respect des règles de jugement moral dans les cas concrets garantit la promotion de comportements éthiques, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de jugement moral dans les cas concrets est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de jugement moral dans les cas concrets est réservé aux élites politiques et économiques.",
          "Le respect des règles de jugement moral dans les cas concrets est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de jugement moral dans les cas concrets garantit la promotion de comportements éthiques, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de jugement moral dans les cas concrets est essentiel pour garantir la promotion de comportements éthiques, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "126_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de jugement moral dans les cas concrets est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de jugement moral dans les cas concrets encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "126_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de jugement moral dans les cas concrets ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de jugement moral et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de jugement moral, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de jugement moral et en promouvant le respect de ces règles.",
        "explanation": "L'éducation est essentielle pour sensibiliser les individus aux règles de jugement moral et pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "126_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de jugement moral dans les cas concrets est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de jugement moral dans les cas concrets encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "126_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de jugement moral dans les cas concrets ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de jugement moral et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles de jugement moral, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de jugement moral et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles de jugement moral en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "126_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de jugement moral dans les cas concrets est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de jugement moral dans les cas concrets encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "126_7",
        "type": "qcm",
        "question": "Quel est le rôle des enseignants dans le respect des règles de jugement moral dans les cas concrets ?",
        "options": [
          "Les enseignants ont un rôle important en promouvant des représentations positives du respect des règles de jugement moral et en sensibilisant les élèves à l'importance de ces règles.",
          "Les enseignants sont inutiles dans le respect des règles de jugement moral, car les élèves peuvent apprendre par eux-mêmes.",
          "Les enseignants sont réservés aux élèves les plus avancés.",
          "Les enseignants sont limités aux matières scientifiques uniquement."
        ],
        "correct_option": "Les enseignants ont un rôle important en promouvant des représentations positives du respect des règles de jugement moral et en sensibilisant les élèves à l'importance de ces règles.",
        "explanation": "Les enseignants ont un rôle important dans le respect des règles de jugement moral en promouvant des représentations positives du respect de ces règles, en sensibilisant les élèves à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "126_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de jugement moral dans les cas concrets est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de jugement moral dans les cas concrets encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "127",
    "EMC 6e - Critique publicité",
    "EMC",
    "6eme",
    [
      {
        "id": "127_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de critique de la publicité ?",
        "options": [
          "Le respect des règles de critique de la publicité garantit la promotion d'une consommation responsable, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de critique de la publicité est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de critique de la publicité est réservé aux élites politiques et économiques.",
          "Le respect des règles de critique de la publicité est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de critique de la publicité garantit la promotion d'une consommation responsable, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de critique de la publicité est essentiel pour promouvoir une consommation responsable, respecter les autres et contribuer à la construction d'une société plus juste et solidaire."
      },
      {
        "id": "127_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de critique de la publicité est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de critique de la publicité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "127_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de critique de la publicité ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de critique de la publicité et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de critique de la publicité, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de critique de la publicité et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de critique de la publicité, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une consommation responsable, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "127_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de critique de la publicité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de critique de la publicité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "127_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de critique de la publicité ?",
        "options": [
          "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de critique de la publicité et en sensibilisant le public à l'importance de ces règles.",
          "Les médias sont inutiles dans le respect des règles de critique de la publicité, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias ont un rôle important en promouvant des représentations positives du respect des règles de critique de la publicité et en sensibilisant le public à l'importance de ces règles.",
        "explanation": "Les médias ont un rôle important dans le respect des règles de critique de la publicité en promouvant des représentations positives du respect de ces règles, en sensibilisant le public à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "127_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de critique de la publicité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de critique de la publicité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "127_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de critique de la publicité ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de critique de la publicité et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de critique de la publicité, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de critique de la publicité et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de critique de la publicité en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une consommation responsable, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "127_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de critique de la publicité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de critique de la publicité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "128",
    "EMC 6e - Sources fiables",
    "EMC",
    "6eme",
    [
      {
        "id": "128_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de recherche de sources fiables ?",
        "options": [
          "Le respect des règles de recherche de sources fiables garantit la promotion d'une information de qualité, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de recherche de sources fiables est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de recherche de sources fiables est réservé aux élites politiques et économiques.",
          "Le respect des règles de recherche de sources fiables est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de recherche de sources fiables garantit la promotion d'une information de qualité, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de recherche de sources fiables est essentiel pour garantir la promotion d'une information de qualité, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "128_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de recherche de sources fiables est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de recherche de sources fiables encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "128_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de recherche de sources fiables ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de recherche de sources fiables et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de recherche de sources fiables, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de recherche de sources fiables et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de recherche de sources fiables, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une information de qualité, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "128_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de recherche de sources fiables est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de recherche de sources fiables encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "128_5",
        "type": "qcm",
        "question": "Quel est l'impact du respect des règles de recherche de sources fiables sur la société ?",
        "options": [
          "Il favorise la diffusion d'une information de qualité et le respect des autres.",
          "Il n'a aucun impact sur la société.",
          "Il limite la liberté d'expression.",
          "Il est uniquement bénéfique pour les chercheurs."
        ],
        "correct_option": "Il favorise la diffusion d'une information de qualité et le respect des autres.",
        "explanation": "Le respect des règles de recherche de sources fiables favorise la diffusion d'une information de qualité, le respect des autres et contribue à la construction d'une société plus juste et solidaire."
      },
      {
        "id": "128_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de recherche de sources fiables est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de recherche de sources fiables encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "128_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de recherche de sources fiables ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de recherche de sources fiables et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de recherche de sources fiables, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de recherche de sources fiables et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de recherche de sources fiables en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une information de qualité, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "128_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de recherche de sources fiables est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de recherche de sources fiables encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "129",
    "EMC 6e - Projets collectifs école",
    "EMC",
    "6eme",
    [
      {
        "id": "129_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles des projets collectifs à l'école ?",
        "options": [
          "Le respect des règles des projets collectifs à l'école garantit la promotion de la collaboration, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles des projets collectifs à l'école est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles des projets collectifs à l'école est réservé aux élites politiques et économiques.",
          "Le respect des règles des projets collectifs à l'école est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles des projets collectifs à l'école garantit la promotion de la collaboration, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles des projets collectifs à l'école est essentiel pour garantir la promotion de la collaboration, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "129_2",
        "type": "vrai-faux",
        "question": "Le respect des règles des projets collectifs à l'école est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles des projets collectifs à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "129_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'élève lors des projets collectifs à l'école ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles des projets collectifs et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles des projets collectifs, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles des projets collectifs et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles des projets collectifs et en promouvant le respect de ces règles."
      },
      {
        "id": "129_4",
        "type": "vrai-faux",
        "question": "Le respect des règles des projets collectifs à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles des projets collectifs à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "129_5",
        "type": "qcm",
        "question": "Quel est le rôle des enseignants lors des projets collectifs à l'école ?",
        "options": [
          "Les enseignants ont un rôle important en promouvant des représentations positives du respect des règles des projets collectifs et en sensibilisant les élèves à l'importance de ces règles.",
          "Les enseignants sont inutiles dans le respect des règles des projets collectifs, car les élèves peuvent apprendre par eux-mêmes.",
          "Les enseignants sont réservés aux élèves les plus avancés.",
          "Les enseignants sont limités aux matières scientifiques uniquement."
        ],
        "correct_option": "Les enseignants ont un rôle important en promouvant des représentations positives du respect des règles des projets collectifs et en sensibilisant les élèves à l'importance de ces règles.",
        "explanation": "Les enseignants ont un rôle important dans le respect des règles des projets collectifs en promouvant des représentations positives du respect de ces règles, en sensibilisant les élèves à leur importance et en contribuant à la construction d'une société plus responsable et respectueuse des règles."
      },
      {
        "id": "129_6",
        "type": "vrai-faux",
        "question": "Le respect des règles des projets collectifs à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles des projets collectifs à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "129_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents lors des projets collectifs à l'école ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles des projets collectifs et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles des projets collectifs, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles des projets collectifs et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles des projets collectifs à l'école en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la collaboration, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "129_8",
        "type": "vrai-faux",
        "question": "Le respect des règles des projets collectifs à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles des projets collectifs à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "130",
    "EMC 6e - Solidarité (Restos du Cœur, association d'aide)",
    "EMC",
    "6eme",
    [
      {
        "id": "130_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de solidarité ?",
        "options": [
          "Le respect des règles de solidarité garantit la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de solidarité est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de solidarité est réservé aux élites politiques et économiques.",
          "Le respect des règles de solidarité est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de solidarité garantit la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de solidarité est essentiel pour garantir la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "130_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de solidarité est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de solidarité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "130_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de solidarité ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de solidarité et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de solidarité, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de solidarité et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de solidarité, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "130_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de solidarité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de solidarité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "130_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de solidarité ?",
        "options": [
          "Les médias jouent un rôle crucial en sensibilisant le public aux règles de solidarité et en promouvant le respect de ces règles.",
          "Les médias sont inutiles dans le respect des règles de solidarité, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de solidarité et en promouvant le respect de ces règles.",
        "explanation": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de solidarité, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "130_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de solidarité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de solidarité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "130_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de solidarité ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de solidarité et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de solidarité, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de solidarité et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de solidarité en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "130_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de solidarité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de solidarité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "131",
    "EMC 6e - Écologie citoyenne",
    "EMC",
    "6eme",
    [
      {
        "id": "131_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de jugement moral dans les cas concrets ?",
        "options": [
          "Le respect des règles de jugement moral dans les cas concrets garantit la promotion d'une société plus juste et solidaire.",
          "Le respect des règles de jugement moral dans les cas concrets est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de jugement moral dans les cas concrets est réservé aux élites politiques et économiques.",
          "Le respect des règles de jugement moral dans les cas concrets est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de jugement moral dans les cas concrets garantit la promotion d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de jugement moral dans les cas concrets est essentiel pour garantir la promotion d'une société plus juste et solidaire."
      },
      {
        "id": "131_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de jugement moral dans les cas concrets est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de jugement moral dans les cas concrets encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "131_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de jugement moral dans les cas concrets ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de jugement moral dans les cas concrets et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de jugement moral dans les cas concrets, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de jugement moral dans les cas concrets et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de jugement moral dans les cas concrets, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une société plus juste et solidaire."
      },
      {
        "id": "131_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de jugement moral dans les cas concrets est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de jugement moral dans les cas concrets encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "131_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de jugement moral dans les cas concrets ?",
        "options": [
          "Les médias jouent un rôle crucial en sensibilisant le public aux règles de jugement moral dans les cas concrets et en promouvant le respect de ces règles.",
          "Les médias sont inutiles dans le respect des règles de jugement moral dans les cas concrets, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de jugement moral dans les cas concrets et en promouvant le respect de ces règles.",
        "explanation": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de jugement moral dans les cas concrets, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une société plus juste et solidaire."
      },
      {
        "id": "131_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de jugement moral dans les cas concrets est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de jugement moral dans les cas concrets encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "131_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de jugement moral dans les cas concrets ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de jugement moral dans les cas concrets et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de jugement moral dans les cas concrets, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de jugement moral dans les cas concrets et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de jugement moral dans les cas concrets en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une société plus juste et solidaire."
      },
      {
        "id": "131_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de jugement moral dans les cas concrets est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de jugement moral dans les cas concrets encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "132",
    "EMC 6e - Vie démocratique école",
    "EMC",
    "6eme",
    [
      {
        "id": "132_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de vie démocratique à l'école ?",
        "options": [
          "Le respect des règles de vie démocratique à l'école garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de vie démocratique à l'école est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de vie démocratique à l'école est réservé aux élites politiques et économiques.",
          "Le respect des règles de vie démocratique à l'école est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de vie démocratique à l'école garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de vie démocratique à l'école est essentiel pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "132_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "132_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de vie démocratique à l'école ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de vie démocratique à l'école et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de vie démocratique à l'école, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de vie démocratique à l'école et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de vie démocratique à l'école, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "132_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "132_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de vie démocratique à l'école ?",
        "options": [
          "Les médias jouent un rôle crucial en sensibilisant le public aux règles de vie démocratique à l'école et en promouvant le respect de ces règles.",
          "Les médias sont inutiles dans le respect des règles de vie démocratique à l'école, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de vie démocratique à l'école et en promouvant le respect de ces règles.",
        "explanation": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de vie démocratique à l'école, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "132_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "132_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de vie démocratique à l'école ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de vie démocratique à l'école et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de vie démocratique à l'école, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de vie démocratique à l'école et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de vie démocratique à l'école en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "132_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "133",
    "EMC 6e - La vie scolaire",
    "EMC",
    "6eme",
    [
      {
        "id": "133_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de la vie scolaire ?",
        "options": [
          "Le respect des règles de la vie scolaire garantit la promotion d'un environnement d'apprentissage sain, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de la vie scolaire est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de la vie scolaire est réservé aux élites politiques et économiques.",
          "Le respect des règles de la vie scolaire est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de la vie scolaire garantit la promotion d'un environnement d'apprentissage sain, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de la vie scolaire est essentiel pour garantir la promotion d'un environnement d'apprentissage sain, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "133_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de la vie scolaire est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de la vie scolaire encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "133_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de la vie scolaire ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de la vie scolaire et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de la vie scolaire, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de la vie scolaire et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de la vie scolaire, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'un environnement d'apprentissage sain, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "133_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de la vie scolaire permet de garantir un environnement d'apprentissage sain et le respect des autres.",
        "correct": True,
        "explanation": "C'est vrai. Le respect des règles de la vie scolaire est essentiel pour garantir la promotion d'un environnement d'apprentissage sain, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "133_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de la vie scolaire ?",
        "options": [
          "Les enseignants jouent un rôle crucial en sensibilisant les élèves aux règles de la vie scolaire et en promouvant le respect de ces règles.",
          "Les médias sont inutiles dans le respect des règles de la vie scolaire, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les enseignants jouent un rôle crucial en sensibilisant les élèves aux règles de la vie scolaire et en promouvant le respect de ces règles.",
        "explanation": "Les enseignants jouent un rôle crucial en sensibilisant les élèves aux règles de la vie scolaire, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'un environnement d'apprentissage sain, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "133_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de la vie scolaire est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de la vie scolaire encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "133_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de la vie scolaire ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de la vie scolaire et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de la vie scolaire, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de la vie scolaire et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de la vie scolaire en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'un environnement d'apprentissage sain, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "133_8",
        "type": "vrai-faux",
        "question": "La vie scolaire ne sert qu'à imposer des règles strictes sans encourager la responsabilité individuelle.",
        "correct": False,
        "explanation": "C'est faux. La vie scolaire vise à promouvoir un environnement d'apprentissage sain, le respect des autres et la construction d'une société plus juste et solidaire en encourageant la responsabilité individuelle et le respect des règles de la vie scolaire."
      }
    ]
  ],
  [
    "134",
    "EMC 6e - Paix et non-violence",
    "EMC",
    "6eme",
    [
      {
        "id": "134_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de paix et de non-violence ?",
        "options": [
          "Le respect des règles de paix et de non-violence garantit la promotion d'une société plus pacifique et respectueuse des droits de l'homme.",
          "Le respect des règles de paix et de non-violence est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de paix et de non-violence est réservé aux élites politiques et économiques.",
          "Le respect des règles de paix et de non-violence est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de paix et de non-violence garantit la promotion d'une société plus pacifique et respectueuse des droits de l'homme.",
        "explanation": "Le respect des règles de paix et de non-violence est essentiel pour garantir la promotion d'une société plus pacifique et respectueuse des droits de l'homme."
      },
      {
        "id": "134_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de paix et de non-violence est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de paix et de non-violence encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "134_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de paix et de non-violence ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de paix et de non-violence et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de paix et de non-violence, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de paix et de non-violence et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de paix et de non-violence, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une société plus pacifique et respectueuse des droits de l'homme."
      },
      {
        "id": "134_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de paix et de non-violence est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de paix et de non-violence encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "134_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de paix et de non-violence ?",
        "options": [
          "Les médias jouent un rôle crucial en sensibilisant le public aux règles de paix et de non-violence et en promouvant le respect de ces règles.",
          "Les médias sont inutiles dans le respect des règles de paix et de non-violence, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux informations locales uniquement."
        ],
        "correct_option": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de paix et de non-violence et en promouvant le respect de ces règles.",
        "explanation": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de paix et de non-violence, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une société plus pacifique et respectueuse des droits de l'homme."
      },
      {
        "id": "134_6",
        "type": "vrai-faux",
        "question": "La paix et la non-violence sont des concepts qui promeuvent la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. La paix et la non-violence encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces concepts dans la société."
      },
      {
        "id": "134_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de paix et de non-violence ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de paix et de non-violence et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de paix et de non-violence, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de paix et de non-violence et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de paix et de non-violence en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion d'une société plus pacifique et respectueuse des droits de l'homme."
      },
      {
        "id": "134_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de paix et de non-violence est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de paix et de non-violence encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "135",
    "EMC 6e - Solidarité",
    "EMC",
    "6eme",
    [
      {
        "id": "135_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de solidarité ?",
        "options": [
          "Le respect des règles de solidarité garantit la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de solidarité est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de solidarité est réservé aux élites politiques et économiques.",
          "Le respect des règles de solidarité est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de solidarité garantit la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de solidarité est essentiel pour promouvoir l'entraide, le respect des autres et construire une société plus juste et solidaire."
      },
      {
        "id": "135_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de solidarité est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de solidarité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "135_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'éducation dans le respect des règles de solidarité ?",
        "options": [
          "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de solidarité et en promouvant le respect de ces règles.",
          "L'éducation est inutile dans le respect des règles de solidarité, car les individus peuvent apprendre par eux-mêmes.",
          "L'éducation est réservée aux élites politiques et économiques.",
          "L'éducation est limitée aux écoles uniquement."
        ],
        "correct_option": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de solidarité et en promouvant le respect de ces règles.",
        "explanation": "L'éducation joue un rôle crucial en sensibilisant les individus aux règles de solidarité, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "135_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de solidarité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de solidarité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "135_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans le respect des règles de solidarité ?",
        "options": [
          "Les médias jouent un rôle crucial en sensibilisant le public aux règles de solidarité et en promouvant le respect de ces règles.",
          "Les médias sont inutiles dans le respect des règles de solidarité, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux écoles uniquement."
        ],
        "correct_option": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de solidarité et en promouvant le respect de ces règles.",
        "explanation": "Les médias jouent un rôle crucial en sensibilisant le public aux règles de solidarité, en promouvant le respect de ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "135_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de solidarité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de solidarité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "135_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans le respect des règles de solidarité ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de solidarité et en promouvant le respect de ces règles.",
          "Les parents sont inutiles dans le respect des règles de solidarité, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux règles de solidarité et en promouvant le respect de ces règles.",
        "explanation": "Les parents jouent un rôle crucial dans le respect des règles de solidarité en sensibilisant leurs enfants à ces règles et en encourageant la responsabilité individuelle pour garantir la promotion de l'entraide, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "135_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de solidarité est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de solidarité encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "136",
    "EMC 6e - Institutions locales (mairie)",
    "EMC",
    "6eme",
    [
      {
        "id": "136_1",
        "type": "qcm",
        "question": "Qu'est-ce qu'une institution locale ?",
        "options": [
          "Une institution locale est une organisation ou un organisme qui gère les affaires locales et prend des décisions au niveau local.",
          "Une institution locale est une organisation internationale qui gère les affaires mondiales.",
          "Une institution locale est une organisation privée qui gère les affaires commerciales.",
          "Une institution locale est une organisation religieuse qui gère les affaires spirituelles."
        ],
        "correct_option": "Une institution locale est une organisation ou un organisme qui gère les affaires locales et prend des décisions au niveau local.",
        "explanation": "Les institutions locales, comme les mairies, sont responsables de la gestion des affaires locales et de la prise de décisions au niveau local."
      },
      {
        "id": "136_2",
        "type": "vrai-faux",
        "question": "Les institutions locales n'ont aucun impact sur la vie quotidienne des citoyens.",
        "correct": False,
        "explanation": "C'est faux. Les institutions locales, comme les mairies, ont un impact significatif sur la vie quotidienne des citoyens en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale."
      },
      {
        "id": "136_3",
        "type": "qcm",
        "question": "Quel est le rôle d'une mairie dans une institution locale ?",
        "options": [
          "La mairie est responsable de la gestion des affaires locales, de la prise de décisions au niveau local et de la fourniture de services publics locaux.",
          "La mairie est responsable de la gestion des affaires internationales et de la prise de décisions au niveau mondial.",
          "La mairie est responsable de la gestion des affaires commerciales et de la prise de décisions au niveau économique.",
          "La mairie est responsable de la gestion des affaires spirituelles et de la prise de décisions au niveau religieux."
        ],
        "correct_option": "La mairie est responsable de la gestion des affaires locales, de la prise de décisions au niveau local et de la fourniture de services publics locaux.",
        "explanation": "La mairie joue un rôle central dans la gestion des affaires locales, la prise de décisions au niveau local et la fourniture de services publics locaux."
      },
      {
        "id": "136_4",
        "type": "vrai-faux",
        "question": "Les institutions locales, comme les mairies, sont essentielles pour garantir la participation citoyenne et la démocratie locale.",
        "correct": True,
        "explanation": "C'est vrai. Les institutions locales, comme les mairies, sont essentielles pour garantir la participation citoyenne et la démocratie locale en permettant aux citoyens de s'impliquer dans les décisions qui affectent leur communauté locale."
      },
      {
        "id": "136_5",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans les institutions locales ?",
        "options": [
          "Les médias jouent un rôle crucial en informant le public sur les activités des institutions locales et en encourageant la participation citoyenne.",
          "Les médias sont inutiles dans les institutions locales, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux informations nationales uniquement."
        ],
        "correct_option": "Les médias jouent un rôle crucial en informant le public sur les activités des institutions locales et en encourageant la participation citoyenne.",
        "explanation": "Les médias jouent un rôle crucial en informant le public sur les activités des institutions locales, en encourageant la participation citoyenne et en garantissant la transparence dans la gestion des affaires locales."
      },
      {
        "id": "136_6",
        "type": "vrai-faux",
        "question": "Les institutions locales n'ont aucun impact sur la vie quotidienne des citoyens. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Les institutions locales, comme les mairies, ont un impact significatif sur la vie quotidienne des citoyens en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale."
      },
      {
        "id": "136_7",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans les institutions locales ?",
        "options": [
          "Les parents ont un rôle important en sensibilisant leurs enfants aux institutions locales et en encourageant leur participation citoyenne.",
          "Les parents sont inutiles dans les institutions locales, car les individus peuvent apprendre par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux informations nationales uniquement."
        ],
        "correct_option": "Les parents ont un rôle important en sensibilisant leurs enfants aux institutions locales et en encourageant leur participation citoyenne.",
        "explanation": "Les parents ont un rôle important en sensibilisant leurs enfants aux institutions locales, en encourageant leur participation citoyenne et en contribuant à la formation de citoyens responsables."
      },
      {
        "id": "136_8",
        "type": "vrai-faux",
        "question": "Les institutions locales, comme les mairies, sont essentielles pour garantir la participation citoyenne et la démocratie locale. (variante 2)",
        "correct": True,
        "explanation": "C'est vrai. Les institutions locales, comme les mairies, sont essentielles pour garantir la participation citoyenne et la démocratie locale en permettant aux citoyens de s'impliquer dans les décisions qui affectent leur communauté locale."
      }
    ]
  ],
  [
    "137",
    "EMC 6e - Vote classe : Élection délégués (affiches, discours)",
    "EMC",
    "6eme",
    [
      {
        "id": "137_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de vie démocratique à l'école ?",
        "options": [
          "Le respect des règles de vie démocratique à l'école garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de vie démocratique à l'école est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de vie démocratique à l'école est réservé aux élites politiques et économiques.",
          "Le respect des règles de vie démocratique à l'école est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de vie démocratique à l'école garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de vie démocratique à l'école est essentiel pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "137_2",
        "type": "vrai-faux",
        "question": "Un délégué à l'école permet de représenter les élèves et de faire remonter leurs préoccupations.",
        "correct": True,
        "explanation": "C'est vrai. Un délégué à l'école joue un rôle important en représentant les élèves et en faisant remonter leurs préoccupations auprès de l'administration scolaire."
      },
      {
        "id": "137_3",
        "type": "qcm",
        "question": "Quel est le rôle du délégué dans la classe?",
        "options": [
          "Le délégué représente les élèves et fait remonter leurs préoccupations auprès de l'administration scolaire.",
          "Le délégué est responsable de l'enseignement des matières scolaires.",
          "Le délégué organise les activités sportives et culturelles.",
          "Le délégué est chargé de la discipline dans la classe."
        ],
        "correct_option": "Le délégué représente les élèves et fait remonter leurs préoccupations auprès de l'administration scolaire.",
        "explanation": "Le délégué joue un rôle crucial en représentant les élèves et en faisant remonter leurs préoccupations auprès de l'administration scolaire, contribuant ainsi à la vie démocratique de l'école."
      },
      {
        "id": "137_4",
        "type": "vrai-faux",
        "question": "Le délégué est le meilleure élève de la classe.",
        "correct": False,
        "explanation": "C'est faux. Le rôle du délégué n'est pas d'être le meilleur élève, mais de représenter les élèves et de faire remonter leurs préoccupations auprès de l'administration scolaire."
      },
      {
        "id": "137_5",
        "type": "vrai-faux",
        "question": "Un délégué de classe peut aider ses camarades en représentant leurs préoccupations auprès de l'administration scolaire.",
        "correct": True,
        "explanation": "C'est vrai. Un délégué de classe peut jouer un rôle important en représentant les préoccupations de ses camarades auprès de l'administration scolaire, contribuant ainsi à améliorer la vie scolaire et à promouvoir la participation des élèves."
      },
      {
        "id": "137_6",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans la vie démocratique à l'école ?",
        "options": [
          "Les médias jouent un rôle crucial en informant les élèves sur les activités démocratiques à l'école et en encourageant la participation citoyenne.",
          "Les médias sont inutiles dans la vie démocratique à l'école, car les individus peuvent apprendre par eux-mêmes.",
          "Les médias sont réservés aux élites politiques et économiques.",
          "Les médias sont limités aux informations nationales uniquement."
        ],
        "correct_option": "Les médias jouent un rôle crucial en informant les élèves sur les activités démocratiques à l'école et en encourageant la participation citoyenne.",
        "explanation": "Les médias jouent un rôle crucial en informant les élèves sur les activités démocratiques à l'école, en encourageant la participation citoyenne et en garantissant la transparence dans la gestion de la vie scolaire."
      },
      {
        "id": "137_7",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de vie démocratique à l'école ? (variante 2)",
        "options": [
          "Le respect des règles de vie démocratique à l'école garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de vie démocratique à l'école est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de vie démocratique à l'école est réservé aux élites politiques et économiques.",
          "Le respect des règles de vie démocratique à l'école est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de vie démocratique à l'école garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de vie démocratique à l'école est essentiel pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "137_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "138",
    "EMC 6e - L'enseignant et l'élève : une bonne communication",
    "EMC",
    "6eme",
    [
      {
        "id": "138_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de communication entre l'enseignant et l'élève ?",
        "options": [
          "Le respect des règles de communication entre l'enseignant et l'élève garantit la promotion d'une relation de confiance, le respect mutuel et un environnement d'apprentissage positif.",
          "Le respect des règles de communication entre l'enseignant et l'élève est inutile, car les individus peuvent communiquer comme ils le souhaitent.",
          "Le respect des règles de communication entre l'enseignant et l'élève est réservé aux élites politiques et économiques.",
          "Le respect des règles de communication entre l'enseignant et l'élève est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de communication entre l'enseignant et l'élève garantit la promotion d'une relation de confiance, le respect mutuel et un environnement d'apprentissage positif.",
        "explanation": "Le respect des règles de communication entre l'enseignant et l'élève est essentiel pour garantir la promotion d'une relation de confiance, le respect mutuel et un environnement d'apprentissage positif."
      },
      {
        "id": "138_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de communication entre l'enseignant et l'élève est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de communication entre l'enseignant et l'élève encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "138_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'enseignant dans la communication avec les élèves ?",
        "options": [
          "L'enseignant joue un rôle crucial en établissant des règles de communication claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif.",
          "L'enseignant est inutile dans la communication avec les élèves, car les individus peuvent communiquer par eux-mêmes.",
          "L'enseignant est réservé aux élites politiques et économiques.",
          "L'enseignant est limité aux écoles uniquement."
        ],
        "correct_option": "L'enseignant joue un rôle crucial en établissant des règles de communication claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif.",
        "explanation": "L'enseignant joue un rôle crucial en établissant des règles de communication claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif pour garantir une relation de confiance entre l'enseignant et les élèves."
      },
      {
        "id": "138_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de communication entre l'enseignant et l'élève est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de communication entre l'enseignant et l'élève encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "138_5",
        "type": "qcm",
        "question": "Quel est le rôle des élèves dans la communication avec les enseignants ?",
        "options": [
          "Les élèves jouent un rôle crucial en respectant les règles de communication, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif.",
          "Les élèves sont inutiles dans la communication avec les enseignants, car les individus peuvent communiquer par eux-mêmes.",
          "Les élèves sont réservés aux élites politiques et économiques.",
          "Les élèves sont limités aux écoles uniquement."
        ],
        "correct_option": "Les élèves jouent un rôle crucial en respectant les règles de communication, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif.",
        "explanation": "Les élèves jouent un rôle crucial en respectant les règles de communication, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif pour garantir une relation de confiance entre l'enseignant et les élèves."
      },
      {
        "id": "138_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de communication entre l'enseignant et l'élève est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de communication entre l'enseignant et l'élève encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "138_7",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de communication entre l'enseignant et l'élève ? (variante 2)",
        "options": [
          "Le respect des règles de communication entre l'enseignant et l'élève garantit la promotion d'une relation de confiance, le respect mutuel et un environnement d'apprentissage positif.",
          "Le respect des règles de communication entre l'enseignant et l'élève est inutile, car les individus peuvent communiquer comme ils le souhaitent.",
          "Le respect des règles de communication entre l'enseignant et l'élève est réservé aux élites politiques et économiques.",
          "Le respect des règles de communication entre l'enseignant et l'élève est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de communication entre l'enseignant et l'élève garantit la promotion d'une relation de confiance, le respect mutuel et un environnement d'apprentissage positif.",
        "explanation": "Le respect des règles de communication entre l'enseignant et l'élève est essentiel pour garantir la promotion d'une relation de confiance, le respect mutuel et un environnement d'apprentissage positif."
      },
      {
        "id": "138_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de communication entre l'enseignant et l'élève est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de communication entre l'enseignant et l'élève encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "139",
    "EMC 6e - Les sanctions à l'école",
    "EMC",
    "6eme",
    [
      {
        "id": "139_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de sanction à l'école ?",
        "options": [
          "Le respect des règles de sanction à l'école garantit la promotion de la discipline, le respect des autres et un environnement d'apprentissage positif.",
          "Le respect des règles de sanction à l'école est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de sanction à l'école est réservé aux élites politiques et économiques.",
          "Le respect des règles de sanction à l'école est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de sanction à l'école garantit la promotion de la discipline, le respect des autres et un environnement d'apprentissage positif.",
        "explanation": "Le respect des règles de sanction à l'école est essentiel pour garantir la promotion de la discipline, le respect des autres et un environnement d'apprentissage positif."
      },
      {
        "id": "139_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de sanction à l'école est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de sanction à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "139_3",
        "type": "qcm",
        "question": "Quel est le rôle de l'enseignant dans l'application des sanctions à l'école ?",
        "options": [
          "L'enseignant joue un rôle crucial en appliquant les sanctions de manière juste et équitable, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif.",
          "L'enseignant est inutile dans l'application des sanctions à l'école, car les individus peuvent se discipliner par eux-mêmes.",
          "L'enseignant est réservé aux élites politiques et économiques.",
          "L'enseignant est limité aux écoles uniquement."
        ],
        "correct_option": "L'enseignant joue un rôle crucial en appliquant les sanctions de manière juste et équitable, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif.",
        "explanation": "L'enseignant joue un rôle crucial en appliquant les sanctions de manière juste et équitable, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif."
      },
      {
        "id": "139_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de sanction à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de sanction à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "139_5",
        "type": "qcm",
        "question": "Quel est le rôle des élèves dans le respect des règles de sanction à l'école ?",
        "options": [
          "Les élèves jouent un rôle crucial en respectant les règles de sanction, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif.",
          "Les élèves sont inutiles dans le respect des règles de sanction à l'école, car les individus peuvent se discipliner par eux-mêmes.",
          "Les élèves sont réservés aux élites politiques et économiques.",
          "Les élèves sont limités aux écoles uniquement."
        ],
        "correct_option": "Les élèves jouent un rôle crucial en respectant les règles de sanction, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif.",
        "explanation": "Les élèves jouent un rôle crucial en respectant les règles de sanction, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif pour garantir une discipline juste et équitable à l'école."
      },
      {
        "id": "139_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de sanction à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de sanction à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "139_7",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de sanction à l'école ? (variante 2)",
        "options": [
          "Le respect des règles de sanction à l'école garantit la promotion de la discipline, le respect des autres et un environnement d'apprentissage positif.",
          "Le respect des règles de sanction à l'école est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de sanction à l'école est réservé aux élites politiques et économiques.",
          "Le respect des règles de sanction à l'école est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de sanction à l'école garantit la promotion de la discipline, le respect des autres et un environnement d'apprentissage positif.",
        "explanation": "Le respect des règles de sanction à l'école est essentiel pour garantir la promotion de la discipline, le respect des autres et un environnement d'apprentissage positif."
      },
      {
        "id": "139_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de sanction à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de sanction à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "140",
    "EMC 6e - Le droit de vote : citoyenneté et démocratie",
    "EMC",
    "6eme",
    [
      {
        "id": "140_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de citoyenneté et de démocratie ?",
        "options": [
          "Le respect des règles de citoyenneté et de démocratie garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de citoyenneté et de démocratie est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de citoyenneté et de démocratie est réservé aux élites politiques et économiques.",
          "Le respect des règles de citoyenneté et de démocratie est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de citoyenneté et de démocratie garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de citoyenneté et de démocratie est essentiel pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "140_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de citoyenneté et de démocratie est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de citoyenneté et de démocratie encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "140_3",
        "type": "qcm",
        "question": "Quel est le rôle du droit de vote dans la citoyenneté et la démocratie ?",
        "options": [
          "Le droit de vote est un élément essentiel de la citoyenneté et de la démocratie, car il permet aux citoyens de participer aux décisions qui affectent leur communauté et leur pays.",
          "Le droit de vote est inutile dans la citoyenneté et la démocratie, car les individus peuvent agir comme ils le souhaitent.",
          "Le droit de vote est réservé aux élites politiques et économiques.",
          "Le droit de vote est limité aux élections nationales uniquement."
        ],
        "correct_option": "Le droit de vote est un élément essentiel de la citoyenneté et de la démocratie, car il permet aux citoyens de participer aux décisions qui affectent leur communauté et leur pays.",
        "explanation": "Le droit de vote est un élément essentiel de la citoyenneté et de la démocratie, car il permet aux citoyens de participer aux décisions qui affectent leur communauté et leur pays, contribuant ainsi à la construction d'une société plus juste et solidaire."
      },
      {
        "id": "140_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de citoyenneté et de démocratie est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de citoyenneté et de démocratie encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "140_5",
        "type": "qcm",
        "question": "Quel est le rôle des citoyens dans la citoyenneté et la démocratie ?",
        "options": [
          "Les citoyens jouent un rôle crucial en respectant les règles de citoyenneté et de démocratie, en participant aux décisions qui affectent leur communauté et leur pays, et en contribuant à la construction d'une société plus juste et solidaire.",
          "Les citoyens sont inutiles dans la citoyenneté et la démocratie, car les individus peuvent agir comme ils le souhaitent.",
          "Les citoyens sont réservés aux élites politiques et économiques.",
          "Les citoyens sont limités aux élections nationales uniquement."
        ],
        "correct_option": "Les citoyens jouent un rôle crucial en respectant les règles de citoyenneté et de démocratie, en participant aux décisions qui affectent leur communauté et leur pays, et en contribuant à la construction d'une société plus juste et solidaire.",
        "explanation": "Les citoyens jouent un rôle crucial en respectant les règles de citoyenneté et de démocratie, en participant aux décisions qui affectent leur communauté et leur pays, et en contribuant à la construction d'une société plus juste et solidaire."
      },
      {
        "id": "140_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de citoyenneté et de démocratie est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de citoyenneté et de démocratie encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "140_7",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de citoyenneté et de démocratie ? (variante 2)",
        "options": [
          "Le respect des règles de citoyenneté et de démocratie garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de citoyenneté et de démocratie est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de citoyenneté et de démocratie est réservé aux élites politiques et économiques.",
          "Le respect des règles de citoyenneté et de démocratie est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de citoyenneté et de démocratie garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de citoyenneté et de démocratie est essentiel pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "140_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de citoyenneté et de démocratie est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de citoyenneté et de démocratie encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "141",
    "EMC 6e - Les institutions locales : mairie, rôle des parents, rôle des médias",
    "EMC",
    "6eme",
    [
      {
        "id": "141_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les institutions locales ?",
        "options": [
          "Le respect des institutions locales garantit la promotion de la participation citoyenne, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des institutions locales est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des institutions locales est réservé aux élites politiques et économiques.",
          "Le respect des institutions locales est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des institutions locales garantit la promotion de la participation citoyenne, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des institutions locales est essentiel pour garantir la promotion de la participation citoyenne, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "141_2",
        "type": "vrai-faux",
        "question": "Les institutions locales n'ont aucun impact sur la vie quotidienne des citoyens.",
        "correct": False,
        "explanation": "C'est faux. Les institutions locales, comme les mairies, ont un impact significatif sur la vie quotidienne des citoyens en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale."
      },
      {
        "id": "141_3",
        "type": "qcm",
        "question": "Quel est le rôle de la mairie dans les institutions locales ?",
        "options": [
          "La mairie joue un rôle crucial en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale.",
          "La mairie est inutile dans les institutions locales, car les individus peuvent gérer par eux-mêmes.",
          "La mairie est réservée aux élites politiques et économiques.",
          "La mairie est limitée aux informations nationales uniquement."
        ],
        "correct_option": "La mairie joue un rôle crucial en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale.",
        "explanation": "La mairie joue un rôle crucial en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale."
      },
      {
        "id": "141_4",
        "type": "vrai-faux",
        "question": "Les institutions locales n'ont aucun impact sur la vie quotidienne des citoyens. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Les institutions locales, comme les mairies, ont un impact significatif sur la vie quotidienne des citoyens en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale."
      },
      {
        "id": "141_5",
        "type": "qcm",
        "question": "Quel est le rôle des parents dans les institutions locales ?",
        "options": [
          "Les parents jouent un rôle crucial en participant à la vie scolaire de leurs enfants, en soutenant les activités éducatives et en contribuant à la construction d'une communauté éducative solide.",
          "Les parents sont inutiles dans les institutions locales, car les individus peuvent gérer par eux-mêmes.",
          "Les parents sont réservés aux élites politiques et économiques.",
          "Les parents sont limités aux écoles uniquement."
        ],
        "correct_option": "Les parents jouent un rôle crucial en participant à la vie scolaire de leurs enfants, en soutenant les activités éducatives et en contribuant à la construction d'une communauté éducative solide.",
        "explanation": "Les parents jouent un rôle crucial en participant à la vie scolaire de leurs enfants, en soutenant les activités éducatives et en contribuant à la construction d'une communauté éducative solide."
      },
      {
        "id": "141_6",
        "type": "vrai-faux",
        "question": "Les institutions locales n'ont aucun impact sur la vie quotidienne des citoyens. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Les institutions locales, comme les mairies, ont un impact significatif sur la vie quotidienne des citoyens en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale."
      },
      {
        "id": "141_7",
        "type": "qcm",
        "question": "Quel est le rôle des médias dans les institutions locales ?",
        "options": [
          "Les médias jouent un rôle crucial en informant les citoyens sur les activités locales, en encourageant la participation citoyenne et en garantissant la transparence des décisions locales.",
          "Les médias n'ont aucun rôle dans les institutions locales.",
          "Les médias sont uniquement responsables de la publicité des événements locaux.",
          "Les médias sont limités à la couverture des événements sportifs locaux."
        ],
        "correct_option": "Les médias jouent un rôle crucial en informant les citoyens sur les activités locales, en encourageant la participation citoyenne et en garantissant la transparence des décisions locales.",
        "explanation": "Les médias jouent un rôle crucial en informant les citoyens sur les activités locales, en encourageant la participation citoyenne et en garantissant la transparence des décisions locales."
      },
      {
        "id": "141_8",
        "type": "vrai-faux",
        "question": "Les institutions locales n'ont aucun impact sur la vie quotidienne des citoyens. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Les institutions locales, comme les mairies, ont un impact significatif sur la vie quotidienne des citoyens en gérant les services publics locaux, en organisant des événements communautaires et en prenant des décisions qui affectent directement la communauté locale."
      }
    ]
  ],
  [
    "142",
    "EMC 6e - La vie démocratique à l'école",
    "EMC",
    "6eme",
    [
      {
        "id": "142_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de vie démocratique à l'école ?",
        "options": [
          "Le respect des règles de vie démocratique à l'école garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de vie démocratique à l'école est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de vie démocratique à l'école est réservé aux élites politiques et économiques.",
          "Le respect des règles de vie démocratique à l'école est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de vie démocratique à l'école garantit la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de vie démocratique à l'école est essentiel pour garantir la promotion de la participation, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "142_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "142_3",
        "type": "qcm",
        "question": "Quel est le rôle des élèves dans la vie démocratique à l'école ?",
        "options": [
          "Les élèves jouent un rôle crucial en respectant les règles de vie démocratique, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif.",
          "Les élèves sont inutiles dans la vie démocratique à l'école, car les individus peuvent agir par eux-mêmes.",
          "Les élèves sont réservés aux élites politiques et économiques.",
          "Les élèves sont limités aux écoles uniquement."
        ],
        "correct_option": "Les élèves jouent un rôle crucial en respectant les règles de vie démocratique, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif.",
        "explanation": "Les élèves jouent un rôle crucial en respectant les règles de vie démocratique, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif pour garantir une vie démocratique à l'école."
      },
      {
        "id": "142_4",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "142_5",
        "type": "qcm",
        "question": "Quel est le rôle des enseignants dans la vie démocratique à l'école ?",
        "options": [
          "Les enseignants jouent un rôle crucial en établissant des règles de vie démocratique claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif.",
          "Les enseignants sont inutiles dans la vie démocratique à l'école, car les individus peuvent agir par eux-mêmes.",
          "Les enseignants sont réservés aux élites politiques et économiques.",
          "Les enseignants sont limités aux écoles uniquement."
        ],
        "correct_option": "Les enseignants jouent un rôle crucial en établissant des règles de vie démocratique claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif.",
        "explanation": "Les enseignants jouent un rôle crucial en établissant des règles de vie démocratique claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif pour garantir une vie démocratique à l'école."
      },
      {
        "id": "142_6",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "142_7",
        "type": "qcm",
        "question": "Quel est le rôle des élèves dans la vie démocratique à l'école ? (variante 2)",
        "options": [
          "Les élèves participent activement à la prise de décisions et au respect des règles de vie démocratique.",
          "Les élèves n'ont aucun rôle dans la vie démocratique à l'école.",
          "Les élèves sont uniquement responsables de leurs propres actions.",
          "Les élèves sont limités à suivre les instructions des enseignants."
        ],
        "correct_option": "Les élèves participent activement à la prise de décisions et au respect des règles de vie démocratique.",
        "explanation": "Les élèves participent activement à la prise de décisions et au respect des règles de vie démocratique, ce qui contribue à créer un environnement scolaire respectueux et inclusif."
      },
      {
        "id": "142_8",
        "type": "vrai-faux",
        "question": "Le respect des règles de vie démocratique à l'école est un concept qui promeut la non-intervention dans les affaires des individus. (variante 4)",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de vie démocratique à l'école encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      }
    ]
  ],
  [
    "143",
    "EMC 6e - tolérance et respect des différences",
    "EMC",
    "6eme",
    [
      {
        "id": "143_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de respecter les règles de tolérance et de respect des différences ?",
        "options": [
          "Le respect des règles de tolérance et de respect des différences garantit la promotion de l'inclusion, le respect des autres et la construction d'une société plus juste et solidaire.",
          "Le respect des règles de tolérance et de respect des différences est inutile, car les individus peuvent agir comme ils le souhaitent.",
          "Le respect des règles de tolérance et de respect des différences est réservé aux élites politiques et économiques.",
          "Le respect des règles de tolérance et de respect des différences est limité aux écoles uniquement."
        ],
        "correct_option": "Le respect des règles de tolérance et de respect des différences garantit la promotion de l'inclusion, le respect des autres et la construction d'une société plus juste et solidaire.",
        "explanation": "Le respect des règles de tolérance et de respect des différences est essentiel pour garantir la promotion de l'inclusion, le respect des autres et la construction d'une société plus juste et solidaire."
      },
      {
        "id": "143_2",
        "type": "vrai-faux",
        "question": "Le respect des règles de tolérance et de respect des différences est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. Le respect des règles de tolérance et de respect des différences encourage l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "143_3",
        "type": "qcm",
        "question": "Quel est le rôle des élèves dans le respect des règles de tolérance et de respect des différences ?",
        "options": [
          "Les élèves jouent un rôle crucial en respectant les règles de tolérance et de respect des différences, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif.",
          "Les élèves sont inutiles dans le respect des règles de tolérance et de respect des différences, car les individus peuvent agir par eux-mêmes.",
          "Les élèves sont réservés aux élites politiques et économiques.",
          "Les élèves sont limités aux écoles uniquement."
        ],
        "correct_option": "Les élèves jouent un rôle crucial en respectant les règles de tolérance et de respect des différences, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif.",
        "explanation": "Les élèves jouent un rôle crucial en respectant les règles de tolérance et de respect des différences, en exprimant leurs préoccupations de manière respectueuse et en contribuant à un environnement d'apprentissage positif."
      },
      {
        "id": "143_4",
        "type": "vrai-faux",
        "question": "La tolérance et le respect des différences est un concept qui promeut la non-intervention dans les affaires des individus.",
        "correct": False,
        "explanation": "C'est faux. La tolérance et le respect des différences encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces règles dans la société."
      },
      {
        "id": "143_5",
        "type": "qcm",
        "question": "Quel est le rôle des enseignants dans le respect des règles de tolérance et de respect des différences ?",
        "options": [
          "Les enseignants jouent un rôle crucial en établissant des règles de tolérance et de respect des différences claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif.",
          "Les enseignants sont inutiles dans le respect des règles de tolérance et de respect des différences, car les individus peuvent agir par eux-mêmes.",
          "Les enseignants sont réservés aux élites politiques et économiques.",
          "Les enseignants sont limités aux écoles uniquement."
        ],
        "correct_option": "Les enseignants jouent un rôle crucial en établissant des règles de tolérance et de respect des différences claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif.",
        "explanation": "Les enseignants jouent un rôle crucial en établissant des règles de tolérance et de respect des différences claires, en encourageant le respect mutuel et en créant un environnement d'apprentissage positif pour garantir le respect des règles de tolérance et de respect des différences à l'école."
      },
      {
        "id": "143_6",
        "type": "vrai-faux",
        "question": "La tolérance et le respect des différences sont des valeurs fondamentales.",
        "correct": True,
        "explanation": "C'est vrai. La tolérance et le respect des différences sont des valeurs fondamentales qui encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces valeurs dans la société."
      },
      {
        "id": "0143_7",
        "type": "qcm",
        "question": "La tolérance et le respect des différences sont des valeurs fondamentales. (variante 2)",
        "options": [
          "Vrai",
          "Faux"
        ],
        "correct_option": "Vrai",
        "explanation": "C'est vrai. La tolérance et le respect des différences sont des valeurs fondamentales qui encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces valeurs dans la société."
      },
      {
        "id": "143_8",
        "type": "vrai-faux",
        "question": "La tolérance est plus importante que le respect d'autrui ?",
        "correct": False,
        "explanation": "C'est faux. La tolérance et le respect d'autrui sont des valeurs complémentaires qui encouragent l'acceptation et la valorisation de la responsabilité individuelle, ce qui peut impliquer une intervention pour corriger les comportements inappropriés ou pour promouvoir le respect de ces valeurs dans la société."
      }
    ]
  ],
  [
    "144",
    "EMC 6e - Le travail scolaire à la maison",
    "EMC",
    "6eme",
    [
      {
        "id": " 144_1",
        "type": "qcm",
        "question": "Pourquoi est-il important de faire du travail scolaire à la maison ?",
        "options": [
          "Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel.",
          "Le travail scolaire à la maison est inutile, car les élèves peuvent apprendre tout ce dont ils ont besoin à l'école.",
          "Le travail scolaire à la maison est réservé aux élites politiques et économiques.",
          "Le travail scolaire à la maison est limité aux écoles uniquement."
        ],
        "correct_option": "Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel.",
        "explanation": "Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel."
      },
      {
        "id": "144_2",
        "type": "vrai-faux",
        "question": "Il est inutile de travailler à la maison.",
        "correct": False,
        "explanation": "C'est faux. Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel."
      },
      {
        "id": "144_3",
        "type": "qcm",
        "question": "Quels sont les avantages à travailler à la maison ?",
        "options": [
          "Renforcer les compétences",
          "Approfondir les connaissances",
          "Développer l'autonomie",
          "Toutes les réponses précédentes"
        ],
        "correct_option": "Toutes les réponses précédentes",
        "explanation": "Travailler à la maison permet de renforcer les compétences, d'approfondir les connaissances et de développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel."
      },
      {
        "id": "144_4",
        "type": "vrai-faux",
        "question": "Il est inutile de travailler à la maison. (variante 2)",
        "correct": False,
        "explanation": "C'est faux. Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel."
      },
      {
        "id": "144_5",
        "type": "qcm",
        "question": "Pourquoi est-il important de faire du travail scolaire à la maison ? (variante 2)",
        "options": [
          "Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel.",
          "Le travail scolaire à la maison est inutile, car les élèves peuvent apprendre tout ce dont ils ont besoin à l'école.",
          "Le travail scolaire à la maison est réservé aux élites politiques et économiques.",
          "Le travail scolaire à la maison est limité aux écoles uniquement."
        ],
        "correct_option": "Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel.",
        "explanation": "Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel."
      },
      {
        "id": "144_6",
        "type": "vrai-faux",
        "question": "Il est inutile de travailler à la maison. (variante 3)",
        "correct": False,
        "explanation": "C'est faux. Le travail scolaire à la maison est essentiel pour renforcer les compétences, approfondir les connaissances et développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel."
      },
      {
        "id": "144_7",
        "type": "qcm",
        "question": "Quels sont les avantages à travailler à la maison ? (variante 2)",
        "options": [
          "Renforcer les compétences",
          "Approfondir les connaissances",
          "Développer l'autonomie",
          "Toutes les réponses précédentes"
        ],
        "correct_option": "Toutes les réponses précédentes",
        "explanation": "Travailler à la maison permet de renforcer les compétences, d'approfondir les connaissances et de développer l'autonomie des élèves, ce qui contribue à leur réussite scolaire et à leur développement personnel."
      },
      {
        "id": "144_8",
        "type": "vrai-faux",
        "question": "Il faut toujours apprendre les leçons par cœur.",
        "correct": False,
        "explanation": "C'est faux. Il est important de comprendre les concepts et les compétences plutôt que de simplement apprendre les leçons par cœur, car cela favorise une meilleure rétention des connaissances et une application plus efficace dans différentes situations."
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
        if q["type"] == "qcm":
            answers.append({"index": index, "question_id": index + 1, "type": "qcm", "answer": q["correct_option"], "correction": q["explanation"]})
        elif q["type"] == "vrai-faux":
            answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q["correct"] else "faux", "correction": q["explanation"]})
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
