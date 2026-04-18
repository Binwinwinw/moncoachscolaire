#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Français 6e — SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "francais_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")

quizzes_data = [
    # À compléter : (id, titre, "Français", "6e", [questions...])
    # Format question :
    # {"type": "qcm", "question": "...", "options": ["A", "B", "C", "D"], "correct_option": "A", "explanation": "..."}
    # {"type": "vrai-faux", "question": "...", "correct": True, "explanation": "..."}
    # (Pas de questions ouvertes)
    (
        "0145",
        'Français 6e - Les bases de la grammaire',
        'Français',
        '6eme',
        [
            {
                'id': "0145_1",
                'type': "qcm",
                'question': "Quelle est la nature du mot 'chat' dans la phrase 'Le chat dort' ?",
                'options': ["Nom", "Verbe", "Adjectif", "Pronom"],
                'correct_option': "Nom",
                'explanation': "Dans la phrase 'Le chat dort', 'chat' est un nom car il désigne un animal."
            },
            {
                'id': "0145_2",
                'type': "vrai-faux",
                'question': "Le verbe 'dort' est conjugué au présent de l'indicatif.",
                'correct': True,
                'explanation': "C'est vrai. Le verbe 'dort' est conjugué au présent de l'indicatif."
            },
            {
                'id': "0145_3",
                'type': "qcm",
                'question': "Quel est le sujet de la phrase 'Le chat dort' ?",
                'options': ["Le", "chat", "dort", "Il n'y a pas de sujet"],
                'correct_option': "chat",
                'explanation': "Dans la phrase 'Le chat dort', le sujet est 'chat' car c'est lui qui effectue l'action de dormir."
            },
            {
                'id': "0145_4",
                'type': "vrai-faux",
                'question': "Le mot 'chat' est un verbe.",
                'correct': False,
                'explanation': "C'est faux. 'Chat' est un nom, pas un verbe."
            },
            {
                'id': "0145_5",
                'type': "qcm",
                'question': "Quel est le complément d'objet direct (COD) dans la phrase 'Le chat mange la souris' ?",
                'options': ["Le", "chat", "mange", "la souris"],
                'correct_option': "la souris",
                'explanation': "Dans la phrase 'Le chat mange la souris', le complément d'objet direct (COD) est 'la souris' car c'est ce qui est mangé par le chat."
            },
            {
                'id': "0145_6",
                'type': "vrai-faux",
                'question': "Le mot 'mange' est un nom.",
                'correct': False,
                'explanation': "C'est faux. 'Mange' est un verbe, pas un nom."
            },
            {
                'id': "0145_7",
                'type': "qcm",
                'question': "Quel est le complément d'objet indirect (COI) dans la phrase 'Le chat parle à la souris' ?",
                'options': ["Le", "chat", "parle", "à la souris"],
                'correct_option': "à la souris",
                'explanation': "Dans la phrase 'Le chat parle à la souris', le complément d'objet indirect (COI) est 'à la souris' car c'est à elle que le chat parle."
            },
            {
                'id': "0145_8",
                'type': "vrai-faux",
                'question': "Le mot 'parle' est un verbe.",
                'correct': True,
                'explanation': "C'est vrai. 'Parle' est un verbe, pas un nom."
            },
        ]
    ),
    (
        "0146",
        'Français 6e - Les types de textes (narratif, descriptif, argumentatif)',
        'Français',
        '6eme',
        [
            {
                'id': "0146_1",
                'type': "qcm",
                'question': "Quel type de texte raconte une histoire avec des personnages, un début, un milieu et une fin ?",
                'options': ["Texte narratif", "Texte descriptif", "Texte argumentatif", "Texte explicatif"],
                'correct_option': "Texte narratif",
                'explanation': "Un texte narratif raconte une histoire avec des personnages, un début, un milieu et une fin."
            },
            {
                'id': "0146_2",
                'type': "vrai-faux",
                'question': "Un texte descriptif vise à décrire une personne, un lieu ou un objet en utilisant des détails sensoriels.",
                'correct': True,
                'explanation': "C'est vrai. Un texte descriptif vise à décrire une personne, un lieu ou un objet en utilisant des détails sensoriels."
            },
            {
                'id': "0146_3",
                'type': "qcm",
                'question': "Quel type de texte présente des arguments pour convaincre le lecteur d'adopter un point de vue ?",
                'options': ["Texte narratif", "Texte descriptif", "Texte argumentatif", "Texte explicatif"],
                'correct_option': "Texte argumentatif",
                'explanation': "Un texte argumentatif présente des arguments pour convaincre le lecteur d'adopter un point de vue."
            },
            {
                'id': "0146_4",
                'type': "vrai-faux",
                'question': "Un texte explicatif a pour but d'expliquer comment ou pourquoi quelque chose se produit.",
                'correct': True,
                'explanation': "C'est vrai. Un texte explicatif a pour but d'expliquer comment ou pourquoi quelque chose se produit."
            },
            {
                'id': "0146_5",
                'type': "qcm",
                'question': "Quel type de texte utilise des descriptions détaillées pour créer une image vivante dans l'esprit du lecteur ?",
                'options': ["Texte narratif", "Texte descriptif", "Texte argumentatif", "Texte explicatif"],
                'correct_option': "Texte descriptif",
                'explanation': "Un texte descriptif utilise des descriptions détaillées pour créer une image vivante dans l'esprit du lecteur."
            },
            {
                'id': "0146_6",
                'type': "vrai-faux",
                'question': "Un texte narratif ne contient jamais de dialogue entre les personnages.",
                'correct': False,
                'explanation': "C'est faux. Un texte narratif peut contenir du dialogue entre les personnages pour faire avancer l'histoire et développer les relations entre eux."
            },
            {
                'id': "0146_7",
                'type': "qcm",
                'question': "Quel type de texte utilise des descriptions détaillées pour créer une image vivante dans l'esprit du lecteur ?",
                'options': ["Texte narratif", "Texte descriptif", "Texte argumentatif", "Texte explicatif"],
                'correct_option': "Texte descriptif",
                'explanation': "Un texte descriptif utilise des descriptions détaillées pour créer une image vivante dans l'esprit du lecteur."
            },
            {
                'id': "0146_8",
                'type': "vrai-faux",
                'question': "Un texte explicatif peut contenir des opinions personnelles de l'auteur.",
                'correct': False,
                'explanation': "C'est faux. Un texte explicatif a pour but d'expliquer des faits ou des concepts de manière objective, sans inclure les opinions personnelles de l'auteur."
            },
        ]
    ),
    (
        "0147",
        'Français 6e - Les genres littéraires (conte, fable, poésie, théâtre…)',
        'Français',
        '6eme',
        [
            {
                'id': "0147_1",
                'type': "qcm",
                'question': "Quel genre littéraire raconte une histoire fantastique avec des éléments magiques et merveilleux ?",
                'options': ["Conte", "Fable", "Poésie", "Théâtre"],
                'correct_option': "Conte",
                'explanation': "Un conte raconte une histoire fantastique avec des éléments magiques et merveilleux."
            },
            {
                'id': "0147_2",
                'type': "vrai-faux",
                'question': "Une fable est un court récit qui met en scène des animaux pour transmettre une leçon morale.",
                'correct': True,
                'explanation': "C'est vrai. Une fable est un court récit qui met en scène des animaux pour transmettre une leçon morale."
            },
            {
                'id': "0147_3",
                'type': "qcm",
                'question': "Quel genre littéraire utilise des vers et des strophes pour exprimer des émotions et des idées de manière artistique ?",
                'options': ["Conte", "Fable", "Poésie", "Théâtre"],
                'correct_option': "Poésie",
                'explanation': "La poésie utilise des vers et des strophes pour exprimer des émotions et des idées de manière artistique."
            },
            {
                'id': "0147_4",
                'type': "vrai-faux",
                'question': "Le théâtre est un genre littéraire qui se caractérise par la représentation d'une histoire devant un public à travers des dialogues entre les personnages.",
                'correct': True,
                'explanation': "C'est vrai. Le théâtre se caractérise par la représentation d'une histoire devant un public à travers des dialogues entre les personnages."
            },
            {
                'id': "0147_5",
                'type': "qcm",
                'question': "Quel genre littéraire met en scène des personnages et des événements imaginaires pour divertir les lecteurs ?",
                'options': ["Conte", "Fable", "Poésie", "Théâtre"],
                'correct_option': "Conte",
                'explanation': "Un conte met en scène des personnages et des événements imaginaires pour divertir les lecteurs."
            },
            {
                'id': "0147_6",
                'type': "vrai-faux",
                'question': "Une fable peut être écrite en prose ou en vers.",
                'correct': True,
                'explanation': "C'est vrai. Une fable peut être écrite en prose ou en vers, mais elle se caractérise principalement par sa structure narrative et sa leçon morale."
            },
            {
                'id': "0147_7",
                'type': "qcm",
                'question': "Quel genre littéraire utilise des dialogues et des actions pour représenter des situations devant un public ?",
                'options': ["Conte", "Fable", "Poésie", "Théâtre"],
                'correct_option': "Théâtre",
                'explanation': "Le théâtre utilise des dialogues et des actions pour représenter des situations devant un public."
            },
        ]
    ),
    (
        "0148",
        'Français 6e - Les genres littéraires (conte, fable, poésie, théâtre…)',
        'Français',
        '6eme',
        [
            {
                'id': "0148_1",
                'type': "qcm",
                'question': "Quel genre littéraire raconte une histoire fantastique avec des éléments magiques et merveilleux ?",
                'options': ["Conte", "Fable", "Poésie", "Théâtre"],
                'correct_option': "Conte",
                'explanation': "Un conte raconte une histoire fantastique avec des éléments magiques et merveilleux."
            },
            {
                'id': "0148_2",
                'type': "vrai-faux",
                'question': "Une fable est un court récit qui met en scène des animaux pour transmettre une leçon morale.",
                'correct': True,
                'explanation': "C'est vrai. Une fable est un court récit qui met en scène des animaux pour transmettre une leçon morale."
            },
            {
                'id': "0148_3",
                'type': "qcm",
                'question': "Quel genre littéraire utilise des vers et des strophes pour exprimer des émotions et des idées de manière artistique ?",
                'options': ["Conte", "Fable", "Poésie", "Théâtre"],
                'correct_option': "Poésie",
                'explanation': "La poésie utilise des vers et des strophes pour exprimer des émotions et des idées de manière artistique."
            },
            {
                'id': "0148_4",
                'type': "vrai-faux",
                'question': "Le théâtre est un genre littéraire qui se caractérise par la représentation d'une histoire devant un public à travers des dialogues entre les personnages.",
                'correct': True,
                'explanation': "C'est vrai. Le théâtre se caractérise par la représentation d'une histoire devant un public à travers des dialogues entre les personnages."
            },
            {
                'id': "0148_5",
                'type': "qcm",
                'question': "Quel genre littéraire met en scène des personnages et des événements imaginaires pour divertir les lecteurs ?",
                'options': ["Conte", "Fable", "Poésie", "Théâtre"],
                'correct_option': "Conte",
                'explanation': "Un conte met en scène des personnages et des événements imaginaires pour divertir les lecteurs."
            },
            {
                'id': "0148_6",
                'type': "vrai-faux",
                'question': "Une fable peut être écrite en prose ou en vers.",
                'correct': True,
                'explanation': "C'est vrai. Une fable peut être écrite en prose ou en vers, mais elle se caractérise principalement par sa structure narrative et sa leçon morale."
            },
            {
                'id': "0148_7",
                'type': "qcm",
                'question': "Quel genre littéraire utilise des dialogues et des actions pour représenter des situations devant un public ?",
                'options': ["Conte", "Fable", "Poésie", "Théâtre"],
                'correct_option': "Théâtre",
                'explanation': "Le théâtre utilise des dialogues et des actions pour représenter des situations devant un public."
            },
            {
                'id': "0148_8",
                'type': "vrai-faux",
                'question': "Un conte peut être considéré comme un genre littéraire qui mélange des éléments de la réalité et de l'imaginaire pour transmettre des valeurs et des leçons de vie.",
                'correct': True,
                'explanation': "C'est vrai. Un conte peut être considéré comme un genre littéraire qui mélange des éléments de la réalité et de l'imaginaire pour transmettre des valeurs et des leçons de vie."
            },
        ]
    ),
    (
        "0149",
        'Français 6e - Les figures de style (métaphore, comparaison, personnification…)',
        'Français',
        '6eme',
        [
            {
                'id': "0149_1",
                'type': "qcm",
                'question': "Quelle figure de style compare deux éléments sans utiliser de mot de comparaison ?",
                'options': ["Métaphore", "Comparaison", "Personnification", "Hyperbole"],
                'correct_option': "Métaphore",
                'explanation': "La métaphore compare deux éléments sans utiliser de mot de comparaison, en les associant directement."
            },
            {
                'id': "0149_2",
                'type': "vrai-faux",
                'question': "Une comparaison utilise des mots de comparaison tels que 'comme', 'tel', 'semblable à' pour établir une relation entre deux éléments.",
                'correct': True,
                'explanation': "C'est vrai. Une comparaison utilise des mots de comparaison tels que 'comme', 'tel', 'semblable à' pour établir une relation entre deux éléments."
            },
            {
                'id': "0149_3",
                'type': "qcm",
                'question': "Quelle figure de style attribue des caractéristiques humaines à des objets inanimés ou à des animaux ?",
                'options': ["Métaphore", "Comparaison", "Personnification", "Hyperbole"],
                'correct_option': "Personnification",
                'explanation': "La personnification attribue des caractéristiques humaines à des objets inanimés ou à des animaux."
            },
            {
                'id': "0149_4",
                'type': "vrai-faux",
                'question': "L'hyperbole est une figure de style qui exagère la réalité pour créer un effet d'amplification ou d'insistance.",
                'correct': True,
                'explanation': "C'est vrai. L'hyperbole exagère la réalité pour créer un effet d'amplification ou d'insistance."
            },
            {
                'id': "0149_5",
                'type': "qcm",
                'question': "Quelle figure de style compare deux éléments en utilisant des mots de comparaison tels que 'comme', 'tel', 'semblable à' ?",
                'options': ["Métaphore", "Comparaison", "Personnification", "Hyperbole"],
                'correct_option': "Comparaison",
                'explanation': "La comparaison compare deux éléments en utilisant des mots de comparaison tels que 'comme', 'tel', 'semblable à'."
            },
            {
                'id': "0149_6",
                'type': "vrai-faux",
                'question': "Une métaphore est une figure de style qui compare deux éléments sans utiliser de mot de comparaison.",
                'correct': True,
                'explanation': "C'est vrai. Une métaphore compare deux éléments sans utiliser de mot de comparaison, en les associant directement."
            },
            {
                'id': "0149_7",
                'type': "qcm",
                'question': "Quelle figure de style exagère la réalité pour créer un effet d'amplification ou d'insistance ?",
                'options': ["Métaphore", "Comparaison", "Personnification", "Hyperbole"],
                'correct_option': "Hyperbole",
                'explanation': "L'hyperbole exagère la réalité pour créer un effet d'amplification ou d'insistance."
            },
            {
                'id': "0149_8",
                'type': "vrai-faux",
                'question': "La personnification est une figure de style qui attribue des caractéristiques humaines à des objets inanimés ou à des animaux.",
                'correct': True,
                'explanation': "C'est vrai. La personnification attribue des caractéristiques humaines à des objets inanimés ou à des animaux."
            },
        ]
    ),
    (
        "0150",
        'Français 6e - Les homophones (a/à, et/est, son/sont…)',
        'Français',
        '6eme',
        [
            {
                'id': "0150_1",
                'type': "qcm",
                'question': "Quel homophone doit être utilisé dans la phrase suivante : 'Il ___ un livre sur la table.' ?",
                'options': ["a", "à", "et", "est"],
                'correct_option': "est",
                'explanation': "Dans la phrase 'Il ___ un livre sur la table.', le mot correct est 'est' car il s'agit du verbe être conjugué à la troisième personne du singulier."
            },
            {
                'id': "0150_2",
                'type': "vrai-faux",
                'question': "Le mot 'son' est un homophone de 'sont'.",
                'correct': True,
                'explanation': "C'est vrai. 'Son' et 'sont' sont des homophones, c'est-à-dire qu'ils se prononcent de la même manière mais ont des significations différentes."
            },
            {
                'id': "0150_3",
                'type': "qcm",
                'question': "Quel homophone doit être utilisé dans la phrase suivante : 'Il va ___ la plage demain.' ?",
                'options': ["a", "à", "et", "est"],
                'correct_option': "à",
                'explanation': "Dans la phrase 'Il va ___ la plage demain.', le mot correct est 'à' car il s'agit d'une préposition indiquant la destination."
            },
            {
                'id': "0150_4",
                'type': "vrai-faux",
                'question': "Le mot 'et' est un homophone de 'est'.",
                'correct': True,
                'explanation': "C'est vrai. 'Et' et 'est' sont des homophones, c'est-à-dire qu'ils se prononcent de la même manière mais ont des significations différentes."
            },
            {
                'id': "0150_5",
                'type': "qcm",
                'question': "Quel homophone doit être utilisé dans la phrase suivante : 'Il ___ un chien et un chat.' ?",
                'options': ["a", "à", "et", "est"],
                'correct_option': "a",
                'explanation': "Dans la phrase 'Il ___ un chien et un chat.', le mot correct est 'a' car il s'agit du verbe avoir conjugué à la troisième personne du singulier."
            },
            {
                'id': "0150_6",
                'type': "vrai-faux",
                'question': "Le mot 'sont' est un homophone de 'son'.",
                'correct': True,
                'explanation': "C'est vrai. 'Sont' et 'son' sont des homophones, c'est-à-dire qu'ils se prononcent de la même manière mais ont des significations différentes."
            },
            {
                'id': "0150_7",
                'type': "qcm",
                'question': "Quel homophone doit être utilisé dans la phrase suivante : 'Il ___ une voiture rouge.' ?",
                'options': ["a", "à", "et", "est"],
                'correct_option': "a",
                'explanation': "Dans la phrase 'Il ___ une voiture rouge.', le mot correct est 'a' car il s'agit du verbe avoir conjugué à la troisième personne du singulier."
            },
            {
                'id': "0150_8",
                'type': "vrai-faux",
                'question': "Le mot 'est' est un homophone de 'et'.",
                'correct': True,
                'explanation': "C'est vrai. 'Est' et 'et' sont des homophones, c'est-à-dire qu'ils se prononcent de la même manière mais ont des significations différentes."
            },
        ]
    ),
    (
        "0151",
        'Français 6e - Les accords (accord du sujet et du verbe, accord de l’adjectif avec le nom…)',
        'Français',
        '6eme',
        [
            {
                'id': "0151_1",
                'type': "qcm",
                'question': "Quel est l'accord correct dans la phrase suivante : 'Les enfants ___ heureux.' ?",
                'options': ["sont", "est", "sont heureux", "est heureux"],
                'correct_option': "sont heureux",
                'explanation': "Dans la phrase 'Les enfants ___ heureux.', l'accord correct est 'sont heureux' car le sujet 'Les enfants' est au pluriel et l'adjectif 'heureux' doit s'accorder en genre et en nombre avec le sujet."
            },
            {
                'id': "0151_2",
                'type': "vrai-faux",
                'question': "Le verbe doit s'accorder avec le sujet en nombre et en personne.",
                'correct': True,
                'explanation': "C'est vrai. Le verbe doit s'accorder avec le sujet en nombre (singulier ou pluriel) et en personne (première, deuxième ou troisième personne)."
            },
            {
                'id': "0151_3",
                'type': "qcm",
                'question': "Quel est l'accord correct dans la phrase suivante : 'La fille ___ intelligente.' ?",
                'options': ["est", "sont", "est intelligente", "sont intelligente"],
                'correct_option': "est intelligente",
                'explanation': "Dans la phrase 'La fille ___ intelligente.', l'accord correct est 'est intelligente' car le sujet 'La fille' est au singulier et l'adjectif 'intelligente' doit s'accorder en genre et en nombre avec le sujet."
            },
            {
                'id': "0151_4",
                'type': "vrai-faux",
                'question': "L'adjectif doit s'accorder avec le nom qu'il qualifie en genre et en nombre.",
                'correct': True,
                'explanation': "C'est vrai. L'adjectif doit s'accorder avec le nom qu'il qualifie en genre (masculin ou féminin) et en nombre (singulier ou pluriel)."
            },
            {
                'id': "0151_5",
                'type': "qcm",
                'question': "Quel est l'accord correct dans la phrase suivante : 'Les chats ___ noirs.' ?",
                'options': ["sont", "est", "sont noirs", "est noirs"],
                'correct_option': "sont noirs",
                'explanation': "Dans la phrase 'Les chats ___ noirs.', l'accord correct est 'sont noirs' car le sujet 'Les chats' est au pluriel et l'adjectif 'noirs' doit s'accorder en genre et en nombre avec le sujet."
            },
            {
                'id': "0151_6",
                'type': "vrai-faux",
                'question': "Le sujet et le verbe doivent toujours être au même nombre (singulier ou pluriel).",
                'correct': True,
                'explanation': "C'est vrai. Le sujet et le verbe doivent toujours être au même nombre (singulier ou pluriel) pour assurer une concordance grammaticale correcte."
            },
            {
                'id': "0151_7",
                'type': "qcm",
                'question': "Quel est l'accord correct dans la phrase suivante : 'La voiture ___ rapide.' ?",
                'options': ["est", "sont", "est rapide", "sont rapide"],
                'correct_option': "est rapide",
                'explanation': "Dans la phrase 'La voiture ___ rapide.', l'accord correct est 'est rapide' car le sujet 'La voiture' est au singulier et l'adjectif 'rapide' doit s'accorder en genre et en nombre avec le sujet."
            },
            {
                'id': "0151_8",
                'type': "vrai-faux",
                'question': "L'accord du participe passé avec l'auxiliaire avoir se fait uniquement lorsque le complément d'objet direct (COD) est placé avant le verbe.",
                'correct': True,
                'explanation': "C'est vrai. L'accord du participe passé avec l'auxiliaire avoir se fait uniquement lorsque le complément d'objet direct (COD) est placé avant le verbe. Si le COD est placé après le verbe, il n'y a pas d'accord du participe passé."
            },
        ]
    ),
    (
        "0152",
        'Français 6e - Les temps verbaux (présent, passé composé, imparfait, futur simple…)',
        'Français',
        '6eme',
        [
            {
                'id': "0152_1",
                'type': "qcm",
                'question': "Quel temps verbal est utilisé pour exprimer une action qui se déroule au moment où l'on parle ?",
                'options': ["Présent", "Passé composé", "Imparfait", "Futur simple"],
                'correct_option': "Présent",
                'explanation': "Le présent est utilisé pour exprimer une action qui se déroule au moment où l'on parle."
            },
            {
                'id': "0152_2",
                'type': "vrai-faux",
                'question': "Le passé composé est utilisé pour exprimer une action qui s'est déroulée dans le passé et qui a un lien avec le présent.",
                'correct': True,
                'explanation': "C'est vrai. Le passé composé est utilisé pour exprimer une action qui s'est déroulée dans le passé et qui a un lien avec le présent, souvent en indiquant une action achevée ou un résultat présent."
            },
            {
                'id': "0152_3",
                'type': "qcm",
                'question': "Quel temps verbal est utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé ?",
                'options': ["Présent", "Passé composé", "Imparfait", "Futur simple"],
                'correct_option': "Imparfait",
                'explanation': "L'imparfait est utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé, ou pour décrire une situation ou un état dans le passé."
            },
            {
                'id': "0152_4",
                'type': "vrai-faux",
                'question': "Le futur simple est utilisé pour exprimer une action qui se déroulera dans le futur.",
                'correct': True,
                'explanation': "C'est vrai. Le futur simple est utilisé pour exprimer une action qui se déroulera dans le futur, souvent en indiquant une intention, une prédiction ou une promesse."
            },
            {
                'id': "0152_5",
                'type': "qcm",
                'question': "Quel temps verbal est utilisé pour exprimer une action qui s'est déroulée dans le passé et qui a un lien avec le présent ?",
                'options': ["Présent", "Passé composé", "Imparfait", "Futur simple"],
                'correct_option': "Passé composé",
                'explanation': "Le passé composé est utilisé pour exprimer une action qui s'est déroulée dans le passé et qui a un lien avec le présent, souvent en indiquant une action achevée ou un résultat présent."
            },
            {
                'id': "0152_6",
                'type': "vrai-faux",
                'question': "Le présent peut être utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé.",
                'correct': False,
                'explanation': "C'est faux. Le présent est utilisé pour exprimer une action qui se déroule au moment où l'on parle, tandis que l'imparfait est utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé."
            },
            {
                'id': "0152_7",
                'type': "qcm",
                'question': "Quel temps verbal est utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé ?",
                'options': ["Présent", "Passé composé", "Imparfait", "Futur simple"],
                'correct_option': "Imparfait",
                'explanation': "L'imparfait est utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé, ou pour décrire une situation ou un état dans le passé."
            },
            {
                'id': "0152_8",
                'type': "vrai-faux",
                'question': "Le futur simple peut être utilisé pour exprimer une intention, une prédiction ou une promesse.",
                'correct': True,
                'explanation': "C'est vrai. Le futur simple est utilisé pour exprimer une intention, une prédiction ou une promesse concernant une action qui se déroulera dans le futur."
            },
        ]
    ),
    (
        "0153",
        'Français 6e - L’analyse d’un personnage (caractéristiques physiques, traits de personnalité, rôle dans l’histoire…)',
        'Français',
        '6eme',
        [
            {
                'id': "0153_1",
                'type': "qcm",
                'question': "Quel élément permet de décrire les caractéristiques physiques d'un personnage ?",
                'options': ["Traits de personnalité", "Rôle dans l'histoire", "Apparence physique", "Actions du personnage"],
                'correct_option': "Apparence physique",
                'explanation': "L'apparence physique permet de décrire les caractéristiques physiques d'un personnage, telles que sa taille, sa couleur de cheveux, ses vêtements, etc."
            },
            {
                'id': "0153_2",
                'type': "vrai-faux",
                'question': "Les traits de personnalité d'un personnage sont des caractéristiques qui décrivent son comportement, ses attitudes et ses émotions.",
                'correct': True,
                'explanation': "C'est vrai. Les traits de personnalité d'un personnage sont des caractéristiques qui décrivent son comportement, ses attitudes et ses émotions, tels que la gentillesse, la colère, la timidité, etc."
            },
            {
                'id': "0153_3",
                'type': "qcm",
                'question': "Quel élément permet de décrire le rôle d'un personnage dans l'histoire ?",
                'options': ["Caractéristiques physiques", "Traits de personnalité", "Rôle dans l'histoire", "Actions du personnage"],
                'correct_option': "Rôle dans l'histoire",
                'explanation': "Le rôle dans l'histoire permet de décrire la fonction ou la position d'un personnage dans le récit, par exemple s'il est le protagoniste, l'antagoniste, un personnage secondaire, etc."
            },
            {
                'id': "0153_4",
                'type': "vrai-faux",
                'question': "Les actions d'un personnage peuvent révéler des aspects importants de sa personnalité et de son rôle dans l'histoire.",
                'correct': True,
                'explanation': "C'est vrai. Les actions d'un personnage peuvent révéler des aspects importants de sa personnalité et de son rôle dans l'histoire, en montrant comment il réagit aux événements et interagit avec les autres personnages."
            },
            {
                'id': "0153_5",
                'type': "qcm",
                'question': "Quel élément permet de décrire les traits de personnalité d'un personnage ?",
                'options': ["Caractéristiques physiques", "Traits de personnalité", "Rôle dans l'histoire", "Actions du personnage"],
                'correct_option': "Traits de personnalité",
                'explanation': "Les traits de personnalité permettent de décrire le comportement, les attitudes et les émotions d'un personnage, tels que la gentillesse, la colère, la timidité, etc."
            },
            {
                'id': "0153_6",
                'type': "vrai-faux",
                'question': "L'apparence physique d'un personnage peut influencer la perception que les lecteurs ont de lui et de son rôle dans l'histoire.",
                'correct': True,
                'explanation': "C'est vrai. L'apparence physique d'un personnage peut influencer la perception que les lecteurs ont de lui et de son rôle dans l'histoire, en créant des stéréotypes ou en suscitant des émotions spécifiques."
            },
            {
                'id': "0153_7",
                'type': "qcm",
                'question': "Quel élément permet de décrire les actions d'un personnage ?",
                'options': ["Caractéristiques physiques", "Traits de personnalité", "Rôle dans l'histoire", "Actions du personnage"],
                'correct_option': "Actions du personnage",
                'explanation': "Les actions du personnage permettent de décrire ce qu'il fait dans l'histoire, comment il réagit aux événements et interagit avec les autres personnages."
            },
            {
                'id': "0153_8",
                'type': "vrai-faux",
                'question': "Le rôle d'un personnage dans l'histoire peut évoluer au fil du récit en fonction des événements et des interactions avec les autres personnages.",
                'correct': True,
                'explanation': "C'est vrai. Le rôle d'un personnage dans l'histoire peut évoluer au fil du récit en fonction des événements et des interactions avec les autres personnages, ce qui peut ajouter de la complexité et de la profondeur à l'intrigue."
            },
       ]
    ),
    (
        "0154",
        'Français 6e - Le schéma narratif',
        'Français',
        '6eme',
        [
            {
                'id': "0154_1",
                'type': "qcm",
                'question': "Quel est le schéma narratif classique qui décrit la structure d'une histoire ?",
                'options': ["Introduction, développement, conclusion", "Situation initiale, élément déclencheur, péripéties, résolution", "Exposition, nœud, dénouement", "Début, milieu, fin"],
                'correct_option': "Situation initiale, élément déclencheur, péripéties, résolution",
                'explanation': "Le schéma narratif classique qui décrit la structure d'une histoire est : Situation initiale, élément déclencheur, péripéties, résolution."
            },
            {
                'id': "0154_2",
                'type': "vrai-faux",
                'question': "Le schéma narratif permet d'organiser les événements d'une histoire de manière logique et cohérente.",
                'correct': True,
                'explanation': "C'est vrai. Le schéma narratif permet d'organiser les événements d'une histoire de manière logique et cohérente, en suivant une progression qui maintient l'intérêt du lecteur."
            },
            {
                'id': "0154_3",
                'type': "qcm",
                'question': "Quel élément du schéma narratif correspond à l'introduction de l'histoire, où les personnages et le cadre sont présentés ?",
                'options': ["Situation initiale", "Élément déclencheur", "Péripéties", "Résolution"],
                'correct_option': "Situation initiale",
                'explanation': "La situation initiale correspond à l'introduction de l'histoire, où les personnages et le cadre sont présentés."
            },
            {
                'id': "0154_4",
                'type': "vrai-faux",
                'question': "L'élément déclencheur est l'événement qui perturbe la situation initiale et lance l'intrigue de l'histoire.",
                'correct': True,
                'explanation': "C'est vrai. L'élément déclencheur est l'événement qui perturbe la situation initiale et lance l'intrigue de l'histoire, en créant un conflit ou une tension qui doit être résolue."
            },
            {
                'id': "0154_5",
                'type': "qcm",
                'question': "Quel élément du schéma narratif correspond aux événements et aux actions qui se déroulent après l'élément déclencheur et qui mènent à la résolution de l'histoire ?",
                'options': ["Situation initiale", "Élément déclencheur", "Péripéties", "Résolution"],
                'correct_option': "Péripéties",
                'explanation': "Les péripéties correspondent aux événements et aux actions qui se déroulent après l'élément déclencheur et qui mènent à la résolution de l'histoire."
            },
            {
                'id': "0154_6",
                'type': "vrai-faux",
                'question': "La résolution est la partie de l'histoire où les conflits sont résolus et où les questions soulevées dans l'intrigue trouvent une conclusion.",
                'correct': True,
                'explanation': "C'est vrai. La résolution est la partie de l'histoire où les conflits sont résolus et où les questions soulevées dans l'intrigue trouvent une conclusion, souvent en apportant une leçon ou une morale."
            },
            {
                'id': "0154_7",
                'type': "qcm",
                'question': "Quel élément du schéma narratif correspond à l'événement qui perturbe la situation initiale et lance l'intrigue de l'histoire ?",
                'options': ["Situation initiale", "Élément déclencheur", "Péripéties", "Résolution"],
                'correct_option': "Élément déclencheur",
                'explanation': "L'élément déclencheur est l'événement qui perturbe la situation initiale et lance l'intrigue de l'histoire, en créant un conflit ou une tension qui doit être résolue."
            },
            {
                'id': "0154_8",
                'type': "vrai-faux",
                'question': "Le schéma narratif peut être utilisé pour analyser la structure d'une histoire et comprendre comment les événements sont organisés pour créer une intrigue captivante.",
                'correct': True,
                'explanation': "C'est vrai. Le schéma narratif peut être utilisé pour analyser la structure d'une histoire et comprendre comment les événements sont organisés pour créer une intrigue captivante, en identifiant les différentes étapes de l'intrigue et leur fonction dans le récit."
            },
        ]
    ),
    (
        "0155",
        'Français 6e - Les temps du récit (imparfait, passé simple…)',
        'Français',
        '6eme',
        [
            {
                'id': "0155_1",
                'type': "qcm",
                'question': "Quel temps du récit est utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé ?",
                'options': ["Imparfait", "Passé simple", "Présent", "Futur"],
                'correct_option': "Imparfait",
                'explanation': "L'imparfait est utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé, ou pour décrire une situation ou un état dans le passé."
            },
            {
                'id': "0155_2",
                'type': "vrai-faux",
                'question': "Le passé simple est utilisé pour exprimer une action qui s'est déroulée dans le passé et qui a un lien avec le présent.",
                'correct': False,
                'explanation': "C'est faux. Le passé simple est utilisé pour exprimer une action qui s'est déroulée dans le passé et qui n'a pas de lien direct avec le présent, souvent dans un contexte littéraire ou formel."
            },
            {
                'id': "0155_3",
                'type': "qcm",
                'question': "Quel temps du récit est utilisé pour exprimer une action qui s'est déroulée dans le passé et qui n'a pas de lien direct avec le présent ?",
                'options': ["Imparfait", "Passé simple", "Présent", "Futur"],
                'correct_option': "Passé simple",
                'explanation': "Le passé simple est utilisé pour exprimer une action qui s'est déroulée dans le passé et qui n'a pas de lien direct avec le présent, souvent dans un contexte littéraire ou formel."
            },
            {
                'id': "0155_4",
                'type': "vrai-faux",
                'question': "Le présent du récit est utilisé pour exprimer des actions ou des événements qui se déroulent au moment où l'on raconte l'histoire.",
                'correct': True,
                'explanation': "C'est vrai. Le présent du récit est utilisé pour exprimer des actions ou des événements qui se déroulent au moment où l'on raconte l'histoire, créant ainsi une impression d'immédiateté et d'engagement du lecteur dans le récit."
            },
            {
                'id': "0155_5",
                'type': "qcm",
                'question': "Quel temps du récit est utilisé pour exprimer des actions ou des événements qui se déroulent au moment où l'on raconte l'histoire ?",
                'options': ["Imparfait", "Passé simple", "Présent", "Futur"],
                'correct_option': "Présent",
                'explanation': "Le présent du récit est utilisé pour exprimer des actions ou des événements qui se déroulent au moment où l'on raconte l'histoire, créant ainsi une impression d'immédiateté et d'engagement du lecteur dans le récit."
            },
            {
                'id': "0155_6",
                'type': "vrai-faux",
                'question': "Le futur du récit est utilisé pour exprimer des actions ou des événements qui se dérouleront dans le futur par rapport au moment où l'on raconte l'histoire.",
                'correct': True,
                'explanation': "C'est vrai. Le futur du récit est utilisé pour exprimer des actions ou des événements qui se dérouleront dans le futur par rapport au moment où l'on raconte l'histoire, souvent pour créer une anticipation ou une tension narrative."
            },
            {
                'id': "0155_7",
                'type': "qcm",
                'question': "Quel temps du récit est utilisé pour exprimer des actions ou des événements qui se dérouleront dans le futur par rapport au moment où l'on raconte l'histoire ?",
                'options': ["Imparfait", "Passé simple", "Présent", "Futur"],
                'correct_option': "Futur",
                'explanation': "Le futur du récit est utilisé pour exprimer des actions ou des événements qui se dérouleront dans le futur par rapport au moment où l'on raconte l'histoire, souvent pour créer une anticipation ou une tension narrative."
            },
            {
                'id': "0155_8",
                'type': "vrai-faux",
                'question': "L'utilisation de différents temps du récit peut contribuer à créer une atmosphère particulière et à renforcer l'impact émotionnel de l'histoire sur les lecteurs.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de différents temps du récit peut contribuer à créer une atmosphère particulière et à renforcer l'impact émotionnel de l'histoire sur les lecteurs, en jouant sur la temporalité et en créant des effets de suspense, d'immédiateté ou de nostalgie."
            },
        ]
    ),
    (
        "0156",
        'Français 6e - L’accord du participe passé',
        'Français',
        '6eme',
        [
            {
                'id': "0156_1",
                'type': "qcm",
                'question': "Quel est l'accord correct du participe passé dans la phrase suivante : 'Les fleurs que j'ai ___ sont magnifiques.' ?",
                'options': ["cueilli", "cueillies", "cueillis", "cueille"],
                'correct_option': "cueillies",
                'explanation': "Dans la phrase 'Les fleurs que j'ai ___ sont magnifiques.', l'accord correct du participe passé est 'cueillies' car le complément d'objet direct (COD) 'les fleurs' est placé avant le verbe et doit s'accorder en genre et en nombre avec le sujet."
            },
            {
                'id': "0156_2",
                'type': "vrai-faux",
                'question': "L'accord du participe passé avec l'auxiliaire avoir se fait uniquement lorsque le complément d'objet direct (COD) est placé avant le verbe.",
                'correct': True,
                'explanation': "C'est vrai. L'accord du participe passé avec l'auxiliaire avoir se fait uniquement lorsque le complément d'objet direct (COD) est placé avant le verbe. Si le COD est placé après le verbe, il n'y a pas d'accord du participe passé."
            },
            {
                'id': "0156_3",
                'type': "qcm",
                'question': "Quel est l'accord correct du participe passé dans la phrase suivante : 'La lettre que tu as ___ est importante.' ?",
                'options': ["écrit", "écrite", "écrits", "écrite"],
                'correct_option': "écrite",
                'explanation': "Dans la phrase 'La lettre que tu as ___ est importante.', l'accord correct du participe passé est 'écrite' car le complément d'objet direct (COD) 'la lettre' est placé avant le verbe et doit s'accorder en genre et en nombre avec le sujet."
            },
            {
                'id': "0156_4",
                'type': "vrai-faux",
                'question': "L'accord du participe passé avec l'auxiliaire être se fait toujours, quel que soit la position du sujet ou du complément d'objet direct.",
                'correct': True,
                'explanation': "C'est vrai. L'accord du participe passé avec l'auxiliaire être se fait toujours, quel que soit la position du sujet ou du complément d'objet direct, car le participe passé s'accorde en genre et en nombre avec le sujet du verbe."
            },
            {
                'id': "0156_5",
                'type': "qcm",
                'question': "Quel est l'accord correct du participe passé dans la phrase suivante : 'Les enfants sont ___ au parc.' ?",
                'options': ["allé", "allés", "allée", "allées"],
                'correct_option': "allés",
                'explanation': "Dans la phrase 'Les enfants sont ___ au parc.', l'accord correct du participe passé est 'allés' car le sujet 'les enfants' est au pluriel et le participe passé s'accorde en genre et en nombre avec le sujet du verbe être."
            },
            {
                'id': "0156_6",
                'type': "vrai-faux",
                'question': "L'accord du participe passé avec l'auxiliaire être se fait uniquement lorsque le sujet est placé avant le verbe.",
                'correct': False,
                'explanation': "C'est faux. L'accord du participe passé avec l'auxiliaire être se fait toujours, quel que soit la position du sujet ou du complément d'objet direct, car le participe passé s'accorde en genre et en nombre avec le sujet du verbe."
            },
            {
                'id': "0156_7",
                'type': "qcm",
                'question': "Quel est l'accord correct du participe passé dans la phrase suivante : 'La maison que nous avons ___ est ancienne.' ?",
                'options': ["visité", "visitée", "visités", "visitée"],
                'correct_option': "visitée",
                'explanation': "Dans la phrase 'La maison que nous avons ___ est ancienne.', l'accord correct du participe passé est 'visitée' car le complément d'objet direct (COD) 'la maison' est placé avant le verbe et doit s'accorder en genre et en nombre avec le sujet."
            },
            {
                'id': "0156_8",
                'type': "vrai-faux",
                'question': "L'accord du participe passé peut parfois être facultatif, notamment dans les cas où il y a une ambiguïté ou une difficulté d'accord.",
                'correct': True,
                'explanation': "C'est vrai. L'accord du participe passé peut parfois être facultatif, notamment dans les cas où il y a une ambiguïté ou une difficulté d'accord."
            },
        ]
    ),
    (
        "0157",
        'Français 6e - Les valeurs des temps',
        'Français',
        '6eme',
        [
            {
                'id': "0157_1",
                'type': "qcm",
                'question': "Quelle est la valeur principale du présent de l'indicatif ?",
                'options': ["Exprimer une action qui se déroule au moment où l'on parle", "Exprimer une action qui s'est déroulée dans le passé", "Exprimer une action qui se déroulera dans le futur", "Exprimer une action hypothétique"],
                'correct_option': "Exprimer une action qui se déroule au moment où l'on parle",
                'explanation': "La valeur principale du présent de l'indicatif est d'exprimer une action qui se déroule au moment où l'on parle."
            },
            {
                'id': "0157_2",
                'type': "vrai-faux",
                'question': "Le passé composé peut être utilisé pour exprimer une action qui s'est déroulée dans le passé et qui a un lien avec le présent.",
                'correct': True,
                'explanation': "C'est vrai. Le passé composé peut être utilisé pour exprimer une action qui s'est déroulée dans le passé et qui a un lien avec le présent, souvent en indiquant une action achevée ou un résultat présent."
            },
            {
                'id': "0157_3",
                'type': "qcm",
                'question': "Quelle est la valeur principale de l'imparfait ?",
                'options': ["Exprimer une action qui se déroule au moment où l'on parle", "Exprimer une action qui s'est déroulée dans le passé de manière habituelle ou répétée", "Exprimer une action qui se déroulera dans le futur", "Exprimer une action hypothétique"],
                'correct_option': "Exprimer une action qui s'est déroulée dans le passé de manière habituelle ou répétée",
                'explanation': "La valeur principale de l'imparfait est d'exprimer une action qui s'est déroulée dans le passé de manière habituelle ou répétée, ou pour décrire une situation ou un état dans le passé."
            },
            {
                'id': "0157_4",
                'type': "vrai-faux",
                'question': "Le futur simple est utilisé pour exprimer une intention, une prédiction ou une promesse concernant une action qui se déroulera dans le futur.",
                'correct': True,
                'explanation': "C'est vrai. Le futur simple est utilisé pour exprimer une intention, une prédiction ou une promesse concernant une action qui se déroulera dans le futur."
            },
            {
                'id': "0157_5",
                'type': "qcm",
                'question': "Quelle est la valeur principale du passé simple ?",
                'options': ["Exprimer une action qui se déroule au moment où l'on parle", "Exprimer une action qui s'est déroulée dans le passé et qui a un lien avec le présent", "Exprimer une action qui s'est déroulée dans le passé et qui n'a pas de lien direct avec le présent", "Exprimer une action hypothétique"],
                'correct_option': "Exprimer une action qui s'est déroulée dans le passé et qui n'a pas de lien direct avec le présent",
                'explanation': "La valeur principale du passé simple est d'exprimer une action qui s'est déroulée dans le passé et qui n'a pas de lien direct avec le présent, souvent dans un contexte littéraire ou formel."
            },
            {
                'id': "0157_6",
                'type': "vrai-faux",
                'question': "Le présent peut être utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé.",
                'correct': False,
                'explanation': "C'est faux. Le présent est utilisé pour exprimer une action qui se déroule au moment où l'on parle, tandis que l'imparfait est utilisé pour exprimer une action qui se déroulait de manière habituelle ou répétée dans le passé."
            },
            {
                'id': "0157_7",
                'type': "qcm",
                'question': "Quelle est la valeur principale du futur simple ?",
                'options': ["Exprimer une action qui se déroule au moment où l'on parle", "Exprimer une action qui s'est déroulée dans le passé", "Exprimer une action qui se déroulera dans le futur", "Exprimer une action hypothétique"],
                'correct_option': "Exprimer une action qui se déroulera dans le futur",
                'explanation': "La valeur principale du futur simple est d'exprimer une action qui se déroulera dans le futur, souvent en indiquant une intention, une prédiction ou une promesse."
            },
            {
                'id': "0157_8",
                'type': "vrai-faux",
                'question': "L'utilisation de différents temps verbaux peut contribuer à créer des nuances de sens et à enrichir la narration d'une histoire.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de différents temps verbaux peut contribuer à créer des nuances de sens et à enrichir la narration d'une histoire, en jouant sur la temporalité et en créant des effets de suspense, d'immédiateté ou de nostalgie."
            },
        ]
    ),
    (
        "0158",
        'Français 6e - Les types de phrases (déclarative, interrogative…)',
        'Français',
        '6eme',
        [
            {
                'id': "0158_1",
                'type': "qcm",
                'question': "Quel type de phrase est utilisé pour faire une déclaration ou exprimer une idée ?",
                'options': ["Phrase déclarative", "Phrase interrogative", "Phrase impérative", "Phrase exclamative"],
                'correct_option': "Phrase déclarative",
                'explanation': "La phrase déclarative est utilisée pour faire une déclaration ou exprimer une idée de manière affirmative ou négative."
            },
            {
                'id': "0158_2",
                'type': "vrai-faux",
                'question': "La phrase interrogative est utilisée pour poser une question ou demander une information.",
                'correct': True,
                'explanation': "C'est vrai. La phrase interrogative est utilisée pour poser une question ou demander une information, souvent en utilisant des mots interrogatifs tels que 'qui', 'quoi', 'où', 'quand', 'comment', etc."
            },
            {
                'id': "0158_3",
                'type': "qcm",
                'question': "Quel type de phrase est utilisé pour donner un ordre ou une instruction ?",
                'options': ["Phrase déclarative", "Phrase interrogative", "Phrase impérative", "Phrase exclamative"],
                'correct_option': "Phrase impérative",
                'explanation': "La phrase impérative est utilisée pour donner un ordre ou une instruction, souvent en utilisant le mode impératif du verbe."
            },
            {
                'id': "0158_4",
                'type': "vrai-faux",
                'question': "La phrase exclamative est utilisée pour exprimer une émotion forte ou une réaction intense.",
                'correct': True,
                'explanation': "C'est vrai. La phrase exclamative est utilisée pour exprimer une émotion forte ou une réaction intense, souvent en utilisant des points d'exclamation et des interjections."
            },
            {
                'id': "0158_5",
                'type': "qcm",
                'question': "Quel type de phrase est utilisé pour exprimer une émotion forte ou une réaction intense ?",
                'options': ["Phrase déclarative", "Phrase interrogative", "Phrase impérative", "Phrase exclamative"],
                'correct_option': "Phrase exclamative",
                'explanation': "La phrase exclamative est utilisée pour exprimer une émotion forte ou une réaction intense, souvent en utilisant des points d'exclamation et des interjections."
            },
            {
                'id': "0158_6",
                'type': "vrai-faux",
                'question': "La phrase déclarative peut être utilisée pour faire une déclaration ou exprimer une idée de manière affirmative ou négative.",
                'correct': True,
                'explanation': "C'est vrai. La phrase déclarative peut être utilisée pour faire une déclaration ou exprimer une idée de manière affirmative ou négative."
            },
            {
                'id': "0158_7",
                'type': "qcm",
                'question': "Quel type de phrase est utilisé pour poser une question ou demander une information ?",
                'options': ["Phrase déclarative", "Phrase interrogative", "Phrase impérative", "Phrase exclamative"],
                'correct_option': "Phrase interrogative",
                'explanation': "La phrase interrogative est utilisée pour poser une question ou demander une information, souvent en utilisant des mots interrogatifs tels que 'qui', 'quoi', 'où', 'quand', 'comment', etc."
            },
            {
                'id': "0158_8",
                'type': "vrai-faux",
                'question': "L'utilisation de différents types de phrases peut contribuer à créer des effets de style et à renforcer l'impact d'un texte sur les lecteurs.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de différents types de phrases peut contribuer à créer des effets de style et à renforcer l'impact d'un texte sur les lecteurs, en jouant sur la structure et le rythme des phrases pour susciter des émotions ou mettre en valeur certaines idées."
            },
        ]
    ),
    (
        "0159",
        'Français 6e - Les fonctions grammaticales (sujet, complément d’objet direct, complément d’objet indirect…)',
        'Français',
        '6eme',
        [
            {
                'id': "0159_1",
                'type': "qcm",
                'question': "Quelle est la fonction grammaticale du mot 'chien' dans la phrase suivante : 'Le chien mange sa nourriture.' ?",
                'options': ["Sujet", "Complément d'objet direct", "Complément d'objet indirect", "Attribut du sujet"],
                'correct_option': "Sujet",
                'explanation': "Dans la phrase 'Le chien mange sa nourriture.', le mot 'chien' est le sujet de la phrase, car il désigne celui qui effectue l'action de manger."
            },
            {
                'id': "0159_2",
                'type': "vrai-faux",
                'question': "Le complément d'objet direct (COD) est la fonction grammaticale qui désigne l'élément qui subit directement l'action du verbe.",
                'correct': True,
                'explanation': "C'est vrai. Le complément d'objet direct (COD) est la fonction grammaticale qui désigne l'élément qui subit directement l'action du verbe, sans préposition."
            },
            {
                'id': "0159_3",
                'type': "qcm",
                'question': "Quelle est la fonction grammaticale du mot 'nourriture' dans la phrase suivante : 'Le chien mange sa nourriture.' ?",
                'options': ["Sujet", "Complément d'objet direct", "Complément d'objet indirect", "Attribut du sujet"],
                'correct_option': "Complément d'objet direct",
                'explanation': "Dans la phrase 'Le chien mange sa nourriture.', le mot 'nourriture' est le complément d'objet direct (COD) de la phrase, car il désigne l'élément qui subit directement l'action de manger."
            },
            {
                'id': "0159_4",
                'type': "vrai-faux",
                'question': "Le complément d'objet indirect (COI) est la fonction grammaticale qui désigne l'élément qui subit indirectement l'action du verbe, souvent introduit par une préposition.",
                'correct': True,
                'explanation': "C'est vrai. Le complément d'objet indirect (COI) est la fonction grammaticale qui désigne l'élément qui subit indirectement l'action du verbe, souvent introduit par une préposition telle que 'à', 'pour', etc."
            },
            {
                'id': "0159_5",
                'type': "qcm",
                'question': "Quelle est la fonction grammaticale du mot 'chien' dans la phrase suivante : 'Le chien est gentil.' ?",
                'options': ["Sujet", "Complément d'objet direct", "Complément d'objet indirect", "Attribut du sujet"],
                'correct_option': "Sujet",
                'explanation': "Dans la phrase 'Le chien est gentil.', le mot 'chien' est le sujet de la phrase, car il désigne celui dont on parle et qui est qualifié d'être gentil."
            },
            {
                'id': "0159_6",
                'type': "vrai-faux",
                'question': "L'attribut du sujet est la fonction grammaticale qui désigne un élément qui qualifie ou décrit le sujet, souvent relié au sujet par un verbe d'état comme 'être'.",
                'correct': True,
                'explanation': "C'est vrai. L'attribut du sujet est la fonction grammaticale qui désigne un élément qui qualifie ou décrit le sujet, souvent relié au sujet par un verbe d'état comme 'être', 'sembler', 'devenir', etc."
            },
            {
                'id': "0159_7",
                'type': "qcm",
                'question': "Quelle est la fonction grammaticale du mot 'gentil' dans la phrase suivante : 'Le chien est gentil.' ?",
                'options': ["Sujet", "Complément d'objet direct", "Complément d'objet indirect", "Attribut du sujet"],
                'correct_option': "Attribut du sujet",
                'explanation': "Dans la phrase 'Le chien est gentil.', le mot 'gentil' est l'attribut du sujet, car il qualifie ou décrit le sujet 'chien' en indiquant une caractéristique de celui-ci."
            },
            {
                'id': "0159_8",
                'type': "vrai-faux",
                'question': "La compréhension des fonctions grammaticales est essentielle pour analyser la structure d'une phrase et comprendre les relations entre les différents éléments qui la composent.",
                'correct': True,
                'explanation': "C'est vrai. La compréhension des fonctions grammaticales est essentielle pour analyser la structure d'une phrase et comprendre les relations entre les différents éléments qui la composent, ce qui permet de mieux saisir le sens et l'intention de l'auteur."
            },
        ]
    ),
    (
        "0160",
        'Français 6e - L’analyse d’un poème',
        'Français',
        '6eme',
        [
            {
                'id': "0160_1",
                'type': "qcm",
                'question': "Quel est l'élément principal à analyser dans un poème pour comprendre son thème et son message ?",
                'options': ["Le titre du poème", "La structure du poème", "Les figures de style utilisées", "Le rythme du poème"],
                'correct_option': "Les figures de style utilisées",
                'explanation': "L'élément principal à analyser dans un poème pour comprendre son thème et son message est les figures de style utilisées, telles que les métaphores, les comparaisons, les allitérations, etc., qui contribuent à créer des images et des émotions chez le lecteur."
            },
            {
                'id': "0160_2",
                'type': "vrai-faux",
                'question': "La structure d'un poème peut être analysée pour comprendre comment les différentes parties du poème sont organisées et comment elles contribuent à l'ensemble du poème.",
                'correct': True,
                'explanation': "C'est vrai. La structure d'un poème peut être analysée pour comprendre comment les différentes parties du poème sont organisées et comment elles contribuent à l'ensemble du poème, en identifiant les strophes, les vers, les rimes, etc."
            },
            {
                'id': "0160_3",
                'type': "qcm",
                'question': "Quel élément du poème correspond à la répétition d'un son ou d'une lettre pour créer un effet de rythme ou d'harmonie ?",
                'options': ["Métaphore", "Comparaison", "Allitération", "Personnification"],
                'correct_option': "Allitération",
                'explanation': "L'allitération correspond à la répétition d'un son ou d'une lettre pour créer un effet de rythme ou d'harmonie dans un poème."
            },
            {
                'id': "0160_4",
                'type': "vrai-faux",
                'question': "L'analyse d'un poème peut aider à mieux comprendre les émotions et les idées que l'auteur souhaite transmettre à travers son œuvre.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'un poème peut aider à mieux comprendre les émotions et les idées que l'auteur souhaite transmettre à travers son œuvre, en décortiquant les éléments du poème et en interprétant leur signification."
            },
            {
                'id': "0160_5",
                'type': "qcm",
                'question': "Quel élément du poème correspond à une comparaison entre deux éléments en utilisant des mots tels que 'comme', 'tel', 'ainsi que', etc. ?",
                'options': ["Métaphore", "Comparaison", "Allitération", "Personnification"],
                'correct_option': "Comparaison",
                'explanation': "La comparaison correspond à une comparaison entre deux éléments en utilisant des mots tels que 'comme', 'tel', 'ainsi que', etc., pour créer une image ou une association d'idées dans un poème."
            },
            {
                'id': "0160_6",
                'type': "vrai-faux",
                'question': "L'analyse d'un poème peut être subjective et dépendre de l'interprétation personnelle du lecteur, ce qui peut conduire à différentes interprétations du même poème.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'un poème peut être subjective et dépendre de l'interprétation personnelle du lecteur, ce qui peut conduire à différentes interprétations du même poème, en fonction des expériences, des émotions et des perspectives de chaque lecteur."
            },
            {
                'id': "0160_7",
                'type': "qcm",
                'question': "Quel élément du poème correspond à une figure de style qui attribue des caractéristiques humaines à des objets inanimés ou à des concepts abstraits ?",
                'options': ["Métaphore", "Comparaison", "Allitération", "Personnification"],
                'correct_option': "Personnification",
                'explanation': "La personnification correspond à une figure de style qui attribue des caractéristiques humaines à des objets inanimés ou à des concepts abstraits, pour créer une image vivante et expressive dans un poème."
            },
            {
                'id': "0160_8",
                'type': "vrai-faux",
                'question': "L'analyse d'un poème peut également inclure l'étude du contexte historique, social et culturel dans lequel le poème a été écrit, pour mieux comprendre les influences et les références présentes dans le poème.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'un poème peut également inclure l'étude du contexte historique, social et culturel dans lequel le poème a été écrit, pour mieux comprendre les influences et les références présentes dans le poème, ainsi que les intentions de l'auteur."
            },
        ]
    ),
    (
        "0161",
        'Français 6e - Les règles de ponctuation',
        'Français',
        '6eme',
        [
            {
                'id': "0161_1",
                'type': "qcm",
                'question': "Quel signe de ponctuation est utilisé pour marquer la fin d'une phrase déclarative ?",
                'options': [".", "?", "!", ","],
                'correct_option': ".",
                'explanation': "Le point (.) est utilisé pour marquer la fin d'une phrase déclarative, qui fait une déclaration ou exprime une idée de manière affirmative ou négative."
            },
            {
                'id': "0161_2",
                'type': "vrai-faux",
                'question': "Le point d'interrogation est utilisé pour marquer la fin d'une phrase interrogative, qui pose une question ou demande une information.",
                'correct': True,
                'explanation': "C'est vrai. Le point d'interrogation (?) est utilisé pour marquer la fin d'une phrase interrogative, qui pose une question ou demande une information."
            },
            {
                'id': "0161_3",
                'type': "qcm",
                'question': "Quel signe de ponctuation est utilisé pour marquer la fin d'une phrase exclamative ?",
                'options': [".", "?", "!", ","],
                'correct_option': "!",
                'explanation': "Le point d'exclamation (!) est utilisé pour marquer la fin d'une phrase exclamative, qui exprime une émotion forte ou une réaction intense."
            },
            {
                'id': "0161_4",
                'type': "vrai-faux",
                'question': "La virgule est utilisée pour séparer les éléments d'une liste, les propositions subordonnées ou les incises dans une phrase.",
                'correct': True,
                'explanation': "C'est vrai. La virgule (,) est utilisée pour séparer les éléments d'une liste, les propositions subordonnées ou les incises dans une phrase, afin de clarifier le sens et de faciliter la lecture."
            },
            {
                'id': "0161_5",
                'type': "qcm",
                'question': "Quel signe de ponctuation est utilisé pour séparer les éléments d'une liste ?",
                'options': [".", "?", "!", ","],
                'correct_option': ",",
                'explanation': "La virgule (,) est utilisée pour séparer les éléments d'une liste, afin de clarifier le sens et de faciliter la lecture."
            },
            {
                'id': "0161_6",
                'type': "vrai-faux",
                'question': "L'utilisation correcte de la ponctuation peut contribuer à améliorer la clarté et l'efficacité de la communication écrite en aidant à structurer les phrases et à exprimer les idées de manière plus précise.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte de la ponctuation peut contribuer à améliorer la clarté et l'efficacité de la communication écrite en aidant à structurer les phrases et à exprimer les idées de manière plus précise, en indiquant les pauses, les intonations et les relations entre les différentes parties d'une phrase."
            },
            {
                'id': "0161_7",
                'type': "qcm",
                'question': "Quel signe de ponctuation est utilisé pour marquer la fin d'une phrase impérative ?",
                'options': [".", "?", "!", ","],
                'correct_option': "!",
                'explanation': "Le point d'exclamation (!) est utilisé pour marquer la fin d'une phrase impérative, qui donne un ordre ou une instruction."
            },
            {
                'id': "0161_8",
                'type': "vrai-faux",
                'question': "L'utilisation excessive de la ponctuation peut parfois rendre un texte difficile à lire et à comprendre, en créant une surcharge d'informations ou en perturbant le rythme de la lecture.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation excessive de la ponctuation peut parfois rendre un texte difficile à lire et à comprendre, en créant une surcharge d'informations ou en perturbant le rythme de la lecture, ce qui peut distraire le lecteur et nuire à la clarté du message."
            },
        ]
    ),
    (
        "0162",
        'Français 6e - L’écriture d’un dialogue',
        'Français',
        '6eme',
        [
            {
                'id': "0162_1",
                'type': "qcm",
                'question': "Quel élément est essentiel pour structurer un dialogue dans un texte narratif ?",
                'options': ["Les guillemets", "Les tirets", "Les parenthèses", "Les crochets"],
                'correct_option': "Les guillemets",
                'explanation': "Les guillemets sont essentiels pour structurer un dialogue dans un texte narratif, car ils permettent de différencier les paroles des personnages du reste du texte et de clarifier qui parle à chaque moment."
            },
            {
                'id': "0162_2",
                'type': "vrai-faux",
                'question': "L'utilisation de tirets peut également être utilisée pour structurer un dialogue, en indiquant les changements de locuteur et en créant une dynamique de conversation.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de tirets peut également être utilisée pour structurer un dialogue, en indiquant les changements de locuteur et en créant une dynamique de conversation, notamment dans les dialogues plus informels ou dans les pièces de théâtre."
            },
            {
                'id': "0162_3",
                'type': "qcm",
                'question': "Quel élément est utilisé pour indiquer les paroles d'un personnage dans un dialogue ?",
                'options': ["Les guillemets", "Les tirets", "Les parenthèses", "Les crochets"],
                'correct_option': "Les guillemets",
                'explanation': "Les guillemets sont utilisés pour indiquer les paroles d'un personnage dans un dialogue, permettant ainsi de différencier les paroles du reste du texte et de clarifier qui parle à chaque moment."
            },
            {
                'id': "0162_4",
                'type': "vrai-faux",
                'question': "L'écriture d'un dialogue peut contribuer à rendre une histoire plus vivante et immersive en permettant aux lecteurs d'entendre les voix des personnages et de mieux comprendre leurs émotions et leurs motivations.",
                'correct': True,
                'explanation': "C'est vrai. L'écriture d'un dialogue peut contribuer à rendre une histoire plus vivante et immersive en permettant aux lecteurs d'entendre les voix des personnages et de mieux comprendre leurs émotions et leurs motivations, ce qui peut renforcer l'engagement du lecteur avec l'histoire."
            },
            {
                'id': "0162_5",
                'type': "qcm",
                'question': "Quel élément est utilisé pour indiquer les changements de locuteur dans un dialogue ?",
                'options': ["Les guillemets", "Les tirets", "Les parenthèses", "Les crochets"],
                'correct_option': "Les tirets",
                'explanation': "Les tirets sont utilisés pour indiquer les changements de locuteur dans un dialogue, en créant une dynamique de conversation et en facilitant la lecture du dialogue, notamment dans les dialogues plus informels ou dans les pièces de théâtre."
            },
            {
                'id': "0162_6",
                'type': "vrai-faux",
                'question': "L'utilisation de guillemets et de tirets pour structurer un dialogue peut aider à clarifier les échanges entre les personnages et à renforcer l'impact émotionnel du dialogue sur les lecteurs.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de guillemets et de tirets pour structurer un dialogue peut aider à clarifier les échanges entre les personnages et à renforcer l'impact émotionnel du dialogue sur les lecteurs, en créant une distinction claire entre les paroles des personnages et le reste du texte."
            },
            {
                'id': "0162_7",
                'type': "qcm",
                'question': "Quel élément est utilisé pour indiquer les incises ou les commentaires d'un narrateur dans un dialogue ?",
                'options': ["Les guillemets", "Les tirets", "Les parenthèses", "Les crochets"],
                'correct_option': "Les parenthèses",
                'explanation': "Les parenthèses sont utilisées pour indiquer les incises ou les commentaires d'un narrateur dans un dialogue, permettant ainsi d'ajouter des informations supplémentaires ou des réflexions du narrateur sans interrompre le flux du dialogue."
            },
            {
                'id': "0162_8",
                'type': "vrai-faux",
                'question': "L'écriture d'un dialogue peut également inclure l'utilisation de différentes voix ou styles de langage pour différencier les personnages et renforcer leur individualité dans l'histoire.",
                'correct': True,
                'explanation': "C'est vrai. L'écriture d'un dialogue peut également inclure l'utilisation de différentes voix ou styles de langage pour différencier les personnages et renforcer leur individualité dans l'histoire, en utilisant des registres de langue, des expressions idiomatiques ou des particularités linguistiques propres à chaque personnage."
            },
        ]
    ),
    (
        "0163",
        'Français 6e - L’analyse d’un texte argumentatif',
        'Français',
        '6eme',
        [
            {
                'id': "0163_1",
                'type': "qcm",
                'question': "Quel est l'élément principal à analyser dans un texte argumentatif pour comprendre la position de l'auteur sur un sujet donné ?",
                'options': ["Le titre du texte", "La structure du texte", "Les arguments utilisés", "Le style d'écriture"],
                'correct_option': "Les arguments utilisés",
                'explanation': "L'élément principal à analyser dans un texte argumentatif pour comprendre la position de l'auteur sur un sujet donné est les arguments utilisés, qui sont les raisons ou les preuves que l'auteur présente pour soutenir sa thèse ou son point de vue."
            },
            {
                'id': "0163_2",
                'type': "vrai-faux",
                'question': "La structure d'un texte argumentatif peut être analysée pour comprendre comment les différentes parties du texte sont organisées et comment elles contribuent à l'ensemble de l'argumentation.",
                'correct': True,
                'explanation': "C'est vrai. La structure d'un texte argumentatif peut être analysée pour comprendre comment les différentes parties du texte sont organisées et comment elles contribuent à l'ensemble de l'argumentation, en identifiant l'introduction, le développement des arguments et la conclusion."
            },
            {
                'id': "0163_3",
                'type': "qcm",
                'question': "Quel élément du texte argumentatif correspond à la thèse ou au point de vue que l'auteur défend ?",
                'options': ["L'introduction", "Le développement des arguments", "La conclusion", "La thèse"],
                'correct_option': "La thèse",
                'explanation': "La thèse correspond au point de vue que l'auteur défend dans un texte argumentatif, et elle est généralement présentée dans l'introduction du texte."
            },
            {
                'id': "0163_4",
                'type': "vrai-faux",
                'question': "L'analyse d'un texte argumentatif peut aider à mieux comprendre les différentes perspectives sur un sujet donné et à développer sa propre capacité à formuler des arguments solides et convaincants.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'un texte argumentatif peut aider à mieux comprendre les différentes perspectives sur un sujet donné et à développer sa propre capacité à formuler des arguments solides et convaincants, en étudiant les techniques d'argumentation utilisées par l'auteur et en réfléchissant aux forces et aux faiblesses de ces arguments."
            },
            {
                'id': "0163_5",
                'type': "qcm",
                'question': "Quel élément du texte argumentatif correspond à la partie où l'auteur présente les raisons ou les preuves pour soutenir sa thèse ?",
                'options': ["L'introduction", "Le développement des arguments", "La conclusion", "La thèse"],
                'correct_option': "Le développement des arguments",
                'explanation': "Le développement des arguments correspond à la partie du texte argumentatif où l'auteur présente les raisons ou les preuves pour soutenir sa thèse, en développant chaque argument de manière détaillée et en fournissant des exemples ou des données pour renforcer son point de vue."
            },
            {
                'id': "0163_6",
                'type': "vrai-faux",
                'question': "La conclusion d'un texte argumentatif est la partie où l'auteur résume les arguments présentés et réaffirme sa thèse de manière convaincante.",
                'correct': True,
                'explanation': "C'est vrai. La conclusion d'un texte argumentatif est la partie où l'auteur résume les arguments présentés et réaffirme sa thèse de manière convaincante, en laissant une impression forte sur le lecteur et en incitant à la réflexion ou à l'action."
            },
            {
                'id': "0163_7",
                'type': "qcm",
                'question': "Quel élément du texte argumentatif correspond à la partie où l'auteur présente le sujet et sa position sur ce sujet ?",
                'options': ["L'introduction", "Le développement des arguments", "La conclusion", "La thèse"],
                'correct_option': "L'introduction",
                'explanation': "L'introduction correspond à la partie du texte argumentatif où l'auteur présente le sujet et sa position sur ce sujet, en introduisant le thème de manière accrocheuse et en énonçant clairement la thèse qu'il défendra dans le reste du texte."
            },
            {
                'id': "0163_8",
                'type': "vrai-faux",
                'question': "L'analyse d'un texte argumentatif peut également inclure l'étude du contexte historique, social et culturel dans lequel le texte a été écrit, pour mieux comprendre les influences et les références présentes dans le texte.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'un texte argumentatif peut également inclure l'étude du contexte historique, social et culturel dans lequel le texte a été écrit, pour mieux comprendre les influences et les références présentes dans le texte, ainsi que les intentions de l'auteur."
            },
        ]
    ),
    (
        "0164",
        'Français 6e - Les connecteurs logiques',
        'Français',
        '6eme',
        [
            {
                'id': "0164_1",
                'type': "qcm",
                'question': "Quel connecteur logique est utilisé pour exprimer une cause ou une raison ?",
                'options': ["Cependant", "Parce que", "Donc", "En revanche"],
                'correct_option': "Parce que",
                'explanation': "Le connecteur logique 'Parce que' est utilisé pour exprimer une cause ou une raison, en indiquant pourquoi quelque chose se produit ou pourquoi une affirmation est vraie."
            },
            {
                'id': "0164_2",
                'type': "vrai-faux",
                'question': "Le connecteur logique 'Cependant' est utilisé pour exprimer une opposition ou une contradiction entre deux idées.",
                'correct': True,
                'explanation': "C'est vrai. Le connecteur logique 'Cependant' est utilisé pour exprimer une opposition ou une contradiction entre deux idées, en indiquant que ce qui suit contredit ou tempère ce qui a été dit précédemment."
            },
            {
                'id': "0164_3",
                'type': "qcm",
                'question': "Quel connecteur logique est utilisé pour exprimer une conséquence ou un résultat ?",
                'options': ["Cependant", "Parce que", "Donc", "En revanche"],
                'correct_option': "Donc",
                'explanation': "Le connecteur logique 'Donc' est utilisé pour exprimer une conséquence ou un résultat, en indiquant que ce qui suit est la conséquence logique de ce qui a été dit précédemment."
            },
            {
                'id': "0164_4",
                'type': "vrai-faux",
                'question': "Le connecteur logique 'En revanche' est utilisé pour exprimer une opposition ou une alternative entre deux idées.",
                'correct': True,
                'explanation': "C'est vrai. Le connecteur logique 'En revanche' est utilisé pour exprimer une opposition ou une alternative entre deux idées, en indiquant que ce qui suit présente une perspective différente ou contradictoire par rapport à ce qui a été dit précédemment."
            },
            {
                'id': "0164_5",
                'type': "qcm",
                'question': "Quel connecteur logique est utilisé pour exprimer une addition ou une accumulation d'idées ?",
                'options': ["Cependant", "Parce que", "Donc", "De plus"],
                'correct_option': "De plus",
                'explanation': "Le connecteur logique 'De plus' est utilisé pour exprimer une addition ou une accumulation d'idées, en indiquant que ce qui suit ajoute des informations supplémentaires à ce qui a été dit précédemment."
            },
            {
                'id': "0164_6",
                'type': "vrai-faux",
                'question': "L'utilisation de connecteurs logiques peut contribuer à améliorer la cohérence et la clarté d'un texte en établissant des relations logiques entre les différentes idées et en guidant le lecteur à travers le raisonnement de l'auteur.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de connecteurs logiques peut contribuer à améliorer la cohérence et la clarté d'un texte en établissant des relations logiques entre les différentes idées et en guidant le lecteur à travers le raisonnement de l'auteur, ce qui facilite la compréhension du message et renforce l'argumentation."
            },
            {
                'id': "0164_7",
                'type': "qcm",
                'question': "Quel connecteur logique est utilisé pour exprimer une concession ou une restriction ?",
                'options': ["Cependant", "Parce que", "Donc", "Bien que"],
                'correct_option': "Bien que",
                'explanation': "Le connecteur logique 'Bien que' est utilisé pour exprimer une concession ou une restriction, en indiquant que ce qui suit présente une idée qui contredit ou tempère ce qui a été dit précédemment, tout en reconnaissant sa validité."
            },
            {
                'id': "0164_8",
                'type': "vrai-faux",
                'question': "L'utilisation excessive de connecteurs logiques peut parfois rendre un texte lourd et difficile à lire, en créant une surcharge d'informations ou en perturbant le flux de la lecture.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation excessive de connecteurs logiques peut parfois rendre un texte lourd et difficile à lire, en créant une surcharge d'informations ou en perturbant le flux de la lecture, ce qui peut distraire le lecteur et nuire à la clarté du message."
            },
        ]
    ),
    (
        "0165",
        'Français 6e - L’écriture créative (rédaction)',
        'Français',
        '6eme',
        [
            {
                'id': "0165_1",
                'type': "qcm",
                'question': "Quel élément est essentiel pour stimuler la créativité dans l'écriture créative ?",
                'options': ["La grammaire parfaite", "L'orthographe impeccable", "L'imagination et l'originalité", "Le respect strict des règles de style"],
                'correct_option': "L'imagination et l'originalité",
                'explanation': "L'imagination et l'originalité sont essentielles pour stimuler la créativité dans l'écriture créative, car elles permettent de générer des idées nouvelles et uniques, de créer des personnages intéressants et de développer des histoires captivantes."
            },
            {
                'id': "0165_2",
                'type': "vrai-faux",
                'question': "L'écriture créative peut inclure différents genres littéraires tels que la fiction, la poésie, le théâtre, etc., offrant ainsi une grande variété de possibilités pour exprimer sa créativité.",
                'correct': True,
                'explanation': "C'est vrai. L'écriture créative peut inclure différents genres littéraires tels que la fiction, la poésie, le théâtre, etc., offrant ainsi une grande variété de possibilités pour exprimer sa créativité et explorer différentes formes d'expression artistique."
            },
            {
                'id': "0165_3",
                'type': "qcm",
                'question': "Quel élément est utilisé pour créer des personnages intéressants dans l'écriture créative ?",
                'options': ["Des descriptions détaillées", "Des dialogues réalistes", "Des motivations claires", "Toutes les réponses ci-dessus"],
                'correct_option': "Toutes les réponses ci-dessus",
                'explanation': "Des descriptions détaillées, des dialogues réalistes et des motivations claires sont tous des éléments utilisés pour créer des personnages intéressants dans l'écriture créative, en donnant vie aux personnages et en les rendant crédibles et attachants pour les lecteurs."
            },
            {
                'id': "0165_4",
                'type': "vrai-faux",
                'question': "L'écriture créative peut être un moyen efficace de développer ses compétences en écriture, en encourageant l'exploration de différentes techniques d'écriture et en favorisant l'expression personnelle à travers l'écriture.",
                'correct': True,
                'explanation': "C'est vrai. L'écriture créative peut être un moyen efficace de développer ses compétences en écriture, en encourageant l'exploration de différentes techniques d'écriture et en favorisant l'expression personnelle à travers l'écriture, ce qui peut renforcer la confiance en soi et améliorer la maîtrise de la langue."
            },
            {
                'id': "0165_5",
                'type': "qcm",
                'question': "Quel élément est utilisé pour créer une intrigue captivante dans l'écriture créative ?",
                'options': ["Un conflit intéressant", "Des rebondissements inattendus", "Une résolution satisfaisante", "Toutes les réponses ci-dessus"],
                'correct_option': "Toutes les réponses ci-dessus",
                'explanation': "Un conflit intéressant, des rebondissements inattendus et une résolution satisfaisante sont tous des éléments utilisés pour créer une intrigue captivante dans l'écriture créative, en maintenant l'intérêt du lecteur et en créant une expérience de lecture engageante."
            },
            {
                'id': "0165_6",
                'type': "vrai-faux",
                'question': "L'écriture créative peut également être utilisée comme un moyen de s'exprimer et de partager ses idées, ses émotions et ses expériences avec les autres à travers l'écriture.",
                'correct': True,
                'explanation': "C'est vrai. L'écriture créative peut également être utilisée comme un moyen de s'exprimer et de partager ses idées, ses émotions et ses expériences avec les autres à travers l'écriture, en offrant une plateforme pour la communication et la connexion avec les lecteurs."
            },
            {
                'id': "0165_7",
                'type': "qcm",
                'question': "Quel élément est utilisé pour créer une atmosphère immersive dans l'écriture créative ?",
                'options': ["Des descriptions sensorielles", "Des détails visuels", "Des émotions fortes", "Toutes les réponses ci-dessus"],
                'correct_option': "Toutes les réponses ci-dessus",
                'explanation': "Des descriptions sensorielles, des détails visuels et des émotions fortes sont tous des éléments utilisés pour créer une atmosphère immersive dans l'écriture créative, en transportant les lecteurs dans le monde de l'histoire et en leur permettant de vivre l'expérience de manière plus intense."
            },
            {
                'id': "0165_8",
                'type': "vrai-faux",
                'question': "L'écriture créative peut être un processus personnel et subjectif, où chaque écrivain peut trouver sa propre voix et son propre style d'écriture, ce qui rend chaque œuvre unique et authentique.",
                'correct': True,
                'explanation': "C'est vrai. L'écriture créative peut être un processus personnel et subjectif, où chaque écrivain peut trouver sa propre voix et son propre style d'écriture, ce qui rend chaque œuvre unique et authentique, reflétant la personnalité, les expériences et les perspectives de l'auteur."
            },
        ]
    ),
    (
        "0166",
        'Français 6e - Les pronoms personnels',
        'Français',
        '6eme',
        [
            {
                'id': "0166_1",
                'type': "qcm",
                'question': "Quel pronom personnel est utilisé pour remplacer un nom de personne ou d'animal de la première personne du singulier ?",
                'options': ["Il", "Elle", "Je", "Nous"],
                'correct_option': "Je",
                'explanation': "Le pronom personnel 'Je' est utilisé pour remplacer un nom de personne ou d'animal de la première personne du singulier, en indiquant que le locuteur parle de lui-même."
            },
            {
                'id': "0166_2",
                'type': "vrai-faux",
                'question': "Le pronom personnel 'Il' est utilisé pour remplacer un nom de personne ou d'animal de la troisième personne du singulier masculin.",
                'correct': True,
                'explanation': "C'est vrai. Le pronom personnel 'Il' est utilisé pour remplacer un nom de personne ou d'animal de la troisième personne du singulier masculin, en indiquant que le locuteur parle d'une autre personne ou d'un animal masculin."
            },
            {
                'id': "0166_3",
                'type': "qcm",
                'question': "Quel pronom personnel est utilisé pour remplacer un nom de personne ou d'animal de la troisième personne du singulier féminin ?",
                'options': ["Il", "Elle", "Je", "Nous"],
                'correct_option': "Elle",
                'explanation': "Le pronom personnel 'Elle' est utilisé pour remplacer un nom de personne ou d'animal de la troisième personne du singulier féminin, en indiquant que le locuteur parle d'une autre personne ou d'un animal féminin."
            },
            {
                'id': "0166_4",
                'type': "vrai-faux",
                'question': "Le pronom personnel 'Nous' est utilisé pour remplacer un nom de groupe de personnes ou d'animaux, y compris le locuteur et les autres personnes impliquées.",
                'correct': True,
                'explanation': "C'est vrai. Le pronom personnel 'Nous' est utilisé pour remplacer un nom de groupe de personnes ou d'animaux, y compris le locuteur et les autres personnes impliquées, en indiquant que le locuteur parle d'un groupe auquel il appartient."
            },
            {
                'id': "0166_5",
                'type': "qcm",
                'question': "Quel pronom personnel est utilisé pour remplacer un nom de personne ou d'animal de la deuxième personne du singulier ?",
                'options': ["Tu", "Il", "Elle", "Nous"],
                'correct_option': "Tu",
                'explanation': "Le pronom personnel 'Tu' est utilisé pour remplacer un nom de personne ou d'animal de la deuxième personne du singulier, en indiquant que le locuteur parle directement à une autre personne."
            },
            {
                'id': "0166_6",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des pronoms personnels peut contribuer à améliorer la clarté et la fluidité de la communication écrite en évitant les répétitions et en facilitant la compréhension du sujet et des actions dans une phrase.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des pronoms personnels peut contribuer à améliorer la clarté et la fluidité de la communication écrite en évitant les répétitions et en facilitant la compréhension du sujet et des actions dans une phrase, ce qui rend le texte plus agréable à lire et plus facile à comprendre."
            },
            {
                'id': "0166_7",
                'type': "qcm",
                'question': "Quel pronom personnel est utilisé pour remplacer un nom de groupe de personnes ou d'animaux, sans inclure le locuteur ?",
                'options': ["Ils", "Elles", "Nous", "Vous"],
                'correct_option': "Ils",
                'explanation': "Le pronom personnel 'Ils' est utilisé pour remplacer un nom de groupe de personnes ou d'animaux, sans inclure le locuteur, en indiquant que le locuteur parle d'un groupe auquel il n'appartient pas."
            },
            {
                'id': "0166_8",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des pronoms personnels peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre qui fait quoi dans une phrase.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des pronoms personnels peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre qui fait quoi dans une phrase, ce qui peut nuire à la clarté du message et à l'efficacité de la communication."
            },
        ]
    ),
    (
        "0167",
        'Français 6e - Les adjectifs qualificatifs',
        'Français',
        '6eme',
        [
            {
                'id': "0167_1",
                'type': "qcm",
                'question': "Quel est le rôle principal d'un adjectif qualificatif dans une phrase ?",
                'options': ["Décrire un nom", "Remplacer un nom", "Indiquer une action", "Exprimer une émotion"],
                'correct_option': "Décrire un nom",
                'explanation': "Le rôle principal d'un adjectif qualificatif dans une phrase est de décrire un nom, en fournissant des informations supplémentaires sur les caractéristiques, les qualités ou les propriétés du nom qu'il accompagne."
            },
            {
                'id': "0167_2",
                'type': "vrai-faux",
                'question': "Un adjectif qualificatif peut être placé avant ou après le nom qu'il décrit, en fonction de la structure de la phrase et du style d'écriture.",
                'correct': True,
                'explanation': "C'est vrai. Un adjectif qualificatif peut être placé avant ou après le nom qu'il décrit, en fonction de la structure de la phrase et du style d'écriture, ce qui permet une certaine flexibilité dans l'expression et la mise en valeur des caractéristiques du nom."
            },
            {
                'id': "0167_3",
                'type': "qcm",
                'question': "Quel adjectif qualificatif est utilisé pour décrire quelque chose de très grand ?",
                'options': ["Petit", "Moyen", "Grand", "Minuscule"],
                'correct_option': "Grand",
                'explanation': "L'adjectif qualificatif 'Grand' est utilisé pour décrire quelque chose de très grand, en indiquant une taille ou une dimension importante par rapport à d'autres objets ou à une norme."
            },
            {
                'id': "0167_4",
                'type': "vrai-faux",
                'question': "L'utilisation d'adjectifs qualificatifs peut contribuer à rendre un texte plus vivant et expressif en ajoutant des détails et des nuances à la description des personnes, des lieux, des objets ou des idées dans une phrase.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation d'adjectifs qualificatifs peut contribuer à rendre un texte plus vivant et expressif en ajoutant des détails et des nuances à la description des personnes, des lieux, des objets ou des idées dans une phrase, ce qui peut renforcer l'impact émotionnel et visuel du texte sur les lecteurs."
            },
            {
                'id': "0167_5",
                'type': "qcm",
                'question': "Quel adjectif qualificatif est utilisé pour décrire quelque chose de très petit ?",
                'options': ["Petit", "Moyen", "Grand", "Minuscule"],
                'correct_option': "Minuscule",
                'explanation': "L'adjectif qualificatif 'Minuscule' est utilisé pour décrire quelque chose de très petit, en indiquant une taille ou une dimension très réduite par rapport à d'autres objets ou à une norme."
            },
            {
                'id': "0167_6",
                'type': "vrai-faux",
                'question': "L'utilisation excessive d'adjectifs qualificatifs peut parfois rendre un texte lourd et difficile à lire, en créant une surcharge d'informations ou en perturbant le rythme de la lecture.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation excessive d'adjectifs qualificatifs peut parfois rendre un texte lourd et difficile à lire, en créant une surcharge d'informations ou en perturbant le rythme de la lecture, ce qui peut distraire le lecteur et nuire à la clarté du message."
            },
            {
                'id': "0167_7",
                'type': "qcm",
                'question': "Quel adjectif qualificatif est utilisé pour décrire quelque chose de moyen ou de taille intermédiaire ?",
                'options': ["Petit", "Moyen", "Grand", "Minuscule"],
                'correct_option': "Moyen",
                'explanation': "L'adjectif qualificatif 'Moyen' est utilisé pour décrire quelque chose de moyen ou de taille intermédiaire, en indiquant une taille ou une dimension qui se situe entre les extrêmes de petit et grand."
            },
            {
                'id': "0167_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des adjectifs qualificatifs peut contribuer à améliorer la qualité et l'efficacité de la communication écrite en fournissant des descriptions précises et évocatrices qui aident les lecteurs à visualiser et à comprendre les éléments décrits dans une phrase.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des adjectifs qualificatifs peut contribuer à améliorer la qualité et l'efficacité de la communication écrite en fournissant des descriptions précises et évocatrices qui aident les lecteurs à visualiser et à comprendre les éléments décrits dans une phrase, ce qui rend le texte plus engageant et plus facile à comprendre."
            },
        ]
    ),
    (
        "0168",
        'Français 6e - Les accords dans le groupe nominal',
        'Français',
        '6eme',
        [
            {
                'id': "0168_1",
                'type': "qcm",
                'question': "Quel est l'accord correct pour le groupe nominal 'les enfants ___ (heureux)' ?",
                'options': ["heureux", "heureuse", "heureuses", "heureuxes"],
                'correct_option': "heureux",
                'explanation': "Le groupe nominal 'les enfants' est au pluriel et masculin, donc l'adjectif 'heureux' doit s'accorder en genre et en nombre avec le nom, ce qui donne 'heureux'."
            },
            {
                'id': "0168_2",
                'type': "vrai-faux",
                'question': "L'accord dans le groupe nominal doit être fait entre le nom et l'adjectif qui le qualifie, en fonction du genre (masculin ou féminin) et du nombre (singulier ou pluriel) du nom.",
                'correct': True,
                'explanation': "C'est vrai. L'accord dans le groupe nominal doit être fait entre le nom et l'adjectif qui le qualifie, en fonction du genre (masculin ou féminin) et du nombre (singulier ou pluriel) du nom, afin de garantir la cohérence grammaticale et la clarté de la communication écrite."
            },
            {
                'id': "0168_3",
                'type': "qcm",
                'question': "Quel est l'accord correct pour le groupe nominal 'une fille ___ (intelligent)' ?",
                'options': ["intelligent", "intelligente", "intelligents", "intelligentes"],
                'correct_option': "intelligente",
                'explanation': "Le groupe nominal 'une fille' est au singulier et féminin, donc l'adjectif 'intelligent' doit s'accorder en genre et en nombre avec le nom, ce qui donne 'intelligente'."
            },
            {
                'id': "0168_4",
                'type': "vrai-faux",
                'question': "L'accord dans le groupe nominal peut parfois être complexe, notamment lorsque le nom est suivi de plusieurs adjectifs ou lorsque le nom est composé de plusieurs mots, ce qui nécessite une attention particulière pour garantir la cohérence grammaticale.",
                'correct': True,
                'explanation': "C'est vrai. L'accord dans le groupe nominal peut parfois être complexe, notamment lorsque le nom est suivi de plusieurs adjectifs ou lorsque le nom est composé de plusieurs mots, ce qui nécessite une attention particulière pour garantir la cohérence grammaticale et éviter les erreurs d'accord qui peuvent nuire à la clarté du message."
            },
            {
                'id': "0168_5",
                'type': "qcm",
                'question': "Quel est l'accord correct pour le groupe nominal 'les fleurs ___ (beau)' ?",
                'options': ["beau", "belle", "beaux", "belles"],
                'correct_option': "belles",
                'explanation': "Le groupe nominal 'les fleurs' est au pluriel et féminin, donc l'adjectif 'beau' doit s'accorder en genre et en nombre avec le nom, ce qui donne 'belles'."
            },
            {
                'id': "0168_6",
                'type': "vrai-faux",
                'question': "L'accord dans le groupe nominal est une règle grammaticale importante qui contribue à la cohérence et à la clarté de la communication écrite, en assurant que les éléments du groupe nominal sont correctement liés et compréhensibles pour les lecteurs.",
                'correct': True,
                'explanation': "C'est vrai. L'accord dans le groupe nominal est une règle grammaticale importante qui contribue à la cohérence et à la clarté de la communication écrite, en assurant que les éléments du groupe nominal sont correctement liés et compréhensibles pour les lecteurs, ce qui renforce l'efficacité de la communication et améliore la qualité du texte."
            },
            {
                'id': "0168_7",
                'type': "qcm",
                'question': "Quel est l'accord correct pour le groupe nominal 'un garçon ___ (grand)' ?",
                'options': ["grand", "grande", "grands", "grandes"],
                'correct_option': "grand",
                'explanation': "Le groupe nominal 'un garçon' est au singulier et masculin, donc l'adjectif 'grand' doit s'accorder en genre et en nombre avec le nom, ce qui donne 'grand'."
            },
            {
                'id': "0168_8",
                'type': "vrai-faux",
                'question': "L'accord dans le groupe nominal peut également inclure l'accord du verbe avec le sujet du groupe nominal, en fonction du nombre et du genre du sujet, ce qui renforce la cohérence grammaticale de la phrase.",
                'correct': True,
                'explanation': "C'est vrai. L'accord dans le groupe nominal peut également inclure l'accord du verbe avec le sujet du groupe nominal, en fonction du nombre et du genre du sujet, ce qui renforce la cohérence grammaticale de la phrase et assure une communication claire et efficace."
            },
        ]
    ),
    (
        "0169",
        'Français 6e - Les homophones grammaticaux',
        'Français',
        '6eme',
        [
            {
                'id': "0169_1",
                'type': "qcm",
                'question': "Quel homophone grammatical est utilisé pour indiquer la possession ou l'appartenance ?",
                'options': ["a", "à", "as", "ah"],
                'correct_option': "à",
                'explanation': "L'homophone grammatical 'à' est utilisé pour indiquer la possession ou l'appartenance, en montrant que quelque chose appartient à quelqu'un ou à quelque chose d'autre."
            },
            {
                'id': "0169_2",
                'type': "vrai-faux",
                'question': "L'homophone grammatical 'a' est utilisé pour indiquer une action ou un état d'être, en tant que forme conjuguée du verbe 'avoir' à la troisième personne du singulier.",
                'correct': True,
                'explanation': "C'est vrai. L'homophone grammatical 'a' est utilisé pour indiquer une action ou un état d'être, en tant que forme conjuguée du verbe 'avoir' à la troisième personne du singulier, ce qui peut parfois prêter à confusion avec l'homophone 'à' qui indique la possession."
            },
            {
                'id': "0169_3",
                'type': "qcm",
                'question': "Quel homophone grammatical est utilisé pour indiquer une exclamation ou une interjection ?",
                'options': ["a", "à", "as", "ah"],
                'correct_option': "ah",
                'explanation': "L'homophone grammatical 'ah' est utilisé pour indiquer une exclamation ou une interjection, en exprimant une émotion forte ou une réaction spontanée à quelque chose."
            },
            {
                'id': "0169_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des homophones grammaticaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les mots sont utilisés de manière appropriée en fonction de leur sens et de leur fonction grammaticale.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des homophones grammaticaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les mots sont utilisés de manière appropriée en fonction de leur sens et de leur fonction grammaticale, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
            {
                'id': "0169_5",
                'type': "qcm",
                'question': "Quel homophone grammatical est utilisé pour indiquer une action ou un état d'être, en tant que forme conjuguée du verbe 'avoir' à la deuxième personne du singulier ?",
                'options': ["a", "à", "as", "ah"],
                'correct_option': "as",
                'explanation': "L'homophone grammatical 'as' est utilisé pour indiquer une action ou un état d'être, en tant que forme conjuguée du verbe 'avoir' à la deuxième personne du singulier, ce qui peut parfois prêter à confusion avec l'homophone 'a' qui est utilisé à la troisième personne du singulier."
            },
            {
                'id': "0169_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des homophones grammaticaux peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre le sens de la phrase ou de saisir l'intention de l'auteur.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des homophones grammaticaux peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre le sens de la phrase ou de saisir l'intention de l'auteur, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0169_7",
                'type': "qcm",
                'question': "Quel homophone grammatical est utilisé pour indiquer une question ou une interrogation ?",
                'options': ["a", "à", "as", "ah"],
                'correct_option': "a",
                'explanation': "L'homophone grammatical 'a' peut également être utilisé pour indiquer une question ou une interrogation, en tant que forme conjuguée du verbe 'avoir' à la troisième personne du singulier, ce qui peut parfois prêter à confusion avec l'homophone 'à' qui indique la possession."
            },
            {
                'id': "0169_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des homophones grammaticaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des homophones grammaticaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les mots sont utilisés de manière appropriée en fonction de leur sens et de leur fonction grammaticale, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0170",
        'Français 6e - Les synonymes et antonymes',
        'Français',
        '6eme',
        [
            {
                'id': "0170_1",
                'type': "qcm",
                'question': "Quel est le synonyme du mot 'heureux' ?",
                'options': ["Triste", "Content", "Fâché", "Inquiet"],
                'correct_option': "Content",
                'explanation': "Le synonyme du mot 'heureux' est 'content', car les deux mots expriment un état de satisfaction ou de joie."
            },
            {
                'id': "0170_2",
                'type': "vrai-faux",
                'question': "Un antonyme est un mot qui a un sens opposé à celui d'un autre mot, tandis qu'un synonyme est un mot qui a un sens similaire ou identique à celui d'un autre mot.",
                'correct': True,
                'explanation': "C'est vrai. Un antonyme est un mot qui a un sens opposé à celui d'un autre mot, tandis qu'un synonyme est un mot qui a un sens similaire ou identique à celui d'un autre mot, ce qui permet d'enrichir le vocabulaire et de varier les expressions dans la communication écrite."
            },
            {
                'id': "0170_3",
                'type': "qcm",
                'question': "Quel est l'antonyme du mot 'grand' ?",
                'options': ["Petit", "Moyen", "Gros", "Minuscule"],
                'correct_option': "Petit",
                'explanation': "L'antonyme du mot 'grand' est 'petit', car les deux mots expriment des tailles ou des dimensions opposées."
            },
            {
                'id': "0170_4",
                'type': "vrai-faux",
                'question': "L'utilisation de synonymes et d'antonymes peut contribuer à améliorer la richesse et la variété du vocabulaire dans la communication écrite, en permettant aux écrivains de choisir des mots plus précis et plus expressifs pour transmettre leurs idées et leurs émotions.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de synonymes et d'antonymes peut contribuer à améliorer la richesse et la variété du vocabulaire dans la communication écrite, en permettant aux écrivains de choisir des mots plus précis et plus expressifs pour transmettre leurs idées et leurs émotions, ce qui peut renforcer l'impact du message et rendre le texte plus engageant pour les lecteurs."
            },
            {
                'id': "0170_5",
                'type': "qcm",
                'question': "Quel est le synonyme du mot 'triste' ?",
                'options': ["Heureux", "Content", "Fâché", "Inquiet"],
                'correct_option': "Inquiet",
                'explanation': "Le synonyme du mot 'triste' est 'inquiet', car les deux mots expriment un état de mécontentement ou de préoccupation."
            },
            {
                'id': "0170_6",
                'type': "vrai-faux",
                'question': "L'utilisation excessive de synonymes peut parfois rendre un texte confus ou difficile à lire, en créant une surcharge d'informations ou en perturbant le flux de la lecture.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation excessive de synonymes peut parfois rendre un texte confus ou difficile à lire, en créant une surcharge d'informations ou en perturbant le flux de la lecture, ce qui peut distraire le lecteur et nuire à la clarté du message."
            },
            {
                'id': "0170_7",
                'type': "qcm",
                'question': "Quel est l'antonyme du mot 'fâché' ?",
                'options': ["Heureux", "Content", "Triste", "Inquiet"],
                'correct_option': "Heureux",
                'explanation': "L'antonyme du mot 'fâché' est 'heureux', car les deux mots expriment des émotions opposées."
            },
            {
                'id': "0170_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des synonymes et des antonymes peut contribuer à améliorer la qualité et l'efficacité de la communication écrite en permettant aux écrivains de choisir des mots plus précis et plus expressifs pour transmettre leurs idées et leurs émotions, tout en évitant les confusions ou les malentendus qui peuvent résulter d'une utilisation incorrecte des mots.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des synonymes et des antonymes peut contribuer à améliorer la qualité et l'efficacité de la communication écrite en permettant aux écrivains de choisir des mots plus précis et plus expressifs pour transmettre leurs idées et leurs émotions, tout en évitant les confusions ou les malentendus qui peuvent résulter d'une utilisation incorrecte des mots, ce qui renforce l'impact du message et améliore la clarté de la communication écrite."
            },
        ]
    ),
    (
        "0171",
        'Français 6e - L’analyse d’une fable',
        'Français',
        '6eme',
        [
            {
                'id': "0171_1",
                'type': "qcm",
                'question': "Quel est l'élément clé d'une fable qui permet de transmettre une leçon ou une morale ?",
                'options': ["Les personnages", "Le cadre", "L'intrigue", "La morale"],
                'correct_option': "La morale",
                'explanation': "La morale est l'élément clé d'une fable qui permet de transmettre une leçon ou une morale, en offrant une conclusion ou une réflexion sur les actions des personnages et les conséquences de leurs choix."
            },
            {
                'id': "0171_2",
                'type': "vrai-faux",
                'question': "L'analyse d'une fable peut aider à développer des compétences de lecture critique et d'interprétation, en encourageant les lecteurs à réfléchir sur les thèmes, les symboles et les messages véhiculés par la fable.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'une fable peut aider à développer des compétences de lecture critique et d'interprétation, en encourageant les lecteurs à réfléchir sur les thèmes, les symboles et les messages véhiculés par la fable, ce qui peut renforcer la compréhension du texte et améliorer la capacité à analyser d'autres types de textes littéraires."
            },
            {
                'id': "0171_3",
                'type': "qcm",
                'question': "Quel est le rôle des personnages dans une fable ?",
                'options': ["Ils représentent des types de personnes ou des traits de caractère", "Ils sont simplement des acteurs de l'intrigue", "Ils n'ont pas d'importance dans la fable", "Ils sont toujours des animaux"],
                'correct_option': "Ils représentent des types de personnes ou des traits de caractère",
                'explanation': "Les personnages dans une fable représentent souvent des types de personnes ou des traits de caractère, en utilisant des animaux ou des objets pour symboliser des qualités humaines, ce qui permet de transmettre la morale de manière plus accessible et mémorable."
            },
            {
                'id': "0171_4",
                'type': "vrai-faux",
                'question': "L'analyse d'une fable peut également aider à développer des compétences d'écriture créative, en inspirant les écrivains à créer leurs propres histoires avec des leçons ou des morales similaires.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'une fable peut également aider à développer des compétences d'écriture créative, en inspirant les écrivains à créer leurs propres histoires avec des leçons ou des morales similaires, en utilisant des personnages, des intrigues et des thèmes pour transmettre leurs messages de manière engageante et significative."
            },
            {
                'id': "0171_5",
                'type': "qcm",
                'question': "Quel est le rôle du cadre dans une fable ?",
                'options': ["Il n'a pas d'importance", "Il sert de toile de fond pour l'intrigue", "Il est essentiel pour comprendre la morale", "Il est toujours un lieu réel"],
                'correct_option': "Il sert de toile de fond pour l'intrigue",
                'explanation': "Le cadre dans une fable sert de toile de fond pour l'intrigue, en fournissant un contexte pour les actions des personnages et en contribuant à l'atmosphère de l'histoire, mais il n'est pas nécessairement essentiel pour comprendre la morale, qui peut être transmise indépendamment du cadre."
            },
            {
                'id': "0171_6",
                'type': "vrai-faux",
                'question': "L'analyse d'une fable peut également aider à développer des compétences de communication orale, en encourageant les lecteurs à discuter et à partager leurs interprétations de la fable avec d'autres personnes.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'une fable peut également aider à développer des compétences de communication orale, en encourageant les lecteurs à discuter et à partager leurs interprétations de la fable avec d'autres personnes, ce qui peut renforcer la compréhension du texte et favoriser des échanges enrichissants sur les thèmes et les messages véhiculés par la fable."
            },
            {
                'id': "0171_7",
                'type': "qcm",
                'question': "Quel est le rôle de l'intrigue dans une fable ?",
                'options': ["Elle n'a pas d'importance", "Elle sert à divertir les lecteurs", "Elle est essentielle pour transmettre la morale", "Elle est toujours linéaire"],
                'correct_option': "Elle est essentielle pour transmettre la morale",
                'explanation': "L'intrigue dans une fable est essentielle pour transmettre la morale, en présentant les actions des personnages et les conséquences de leurs choix, ce qui permet aux lecteurs de comprendre la leçon ou le message que la fable cherche à communiquer."
            },
            {
                'id': "0171_8",
                'type': "vrai-faux",
                'question': "L'analyse d'une fable peut également aider à développer des compétences de réflexion critique et de prise de décision, en encourageant les lecteurs à réfléchir sur les choix des personnages et à envisager comment ils auraient agi dans des situations similaires, ce qui peut renforcer la capacité à prendre des décisions éclairées dans la vie réelle.",
                'correct': True,
                'explanation': "C'est vrai. L'analyse d'une fable peut également aider à développer des compétences de réflexion critique et de prise de décision, en encourageant les lecteurs à réfléchir sur les choix des personnages et à envisager comment ils auraient agi dans des situations similaires, ce qui peut renforcer la capacité à prendre des décisions éclairées dans la vie réelle, en utilisant les leçons apprises de la fable pour guider leurs actions et leurs choix."
            },
        ]
    ),
    (
        "0172",
        'Français 6e - Les valeurs de la modalisation',
        'Français',
        '6eme',
        [
            {
                'id': "0172_1",
                'type': "qcm",
                'question': "Quel est le rôle de la modalisation dans la communication écrite ?",
                'options': ["Indiquer une certitude ou une incertitude", "Exprimer une émotion", "Décrire une action", "Fournir des informations factuelles"],
                'correct_option': "Indiquer une certitude ou une incertitude",
                'explanation': "La modalisation dans la communication écrite a pour rôle d'indiquer une certitude ou une incertitude, en utilisant des mots ou des expressions qui modifient le sens d'une phrase pour refléter le degré de confiance ou de doute du locuteur par rapport à l'information présentée."
            },
            {
                'id': "0172_2",
                'type': "vrai-faux",
                'question': "La modalisation peut être exprimée à travers l'utilisation de verbes modaux, d'adverbes de modalité, de tournures conditionnelles ou d'autres éléments linguistiques qui indiquent le degré de certitude ou d'incertitude dans une phrase.",
                'correct': True,
                'explanation': "C'est vrai. La modalisation peut être exprimée à travers l'utilisation de verbes modaux (comme 'pouvoir', 'devoir', 'vouloir'), d'adverbes de modalité (comme 'peut-être', 'certainement', 'probablement'), de tournures conditionnelles (comme 'si', 'au cas où') ou d'autres éléments linguistiques qui indiquent le degré de certitude ou d'incertitude dans une phrase, ce qui permet aux écrivains de nuancer leurs messages et d'exprimer leurs opinions ou leurs hypothèses de manière plus précise."
            },
            {
                'id': "0172_3",
                'type': "qcm",
                'question': "Quel adverbe de modalité est utilisé pour indiquer une forte probabilité ?",
                'options': ["Peut-être", "Certainement", "Probablement", "Possiblement"],
                'correct_option': "Certainement",
                'explanation': "L'adverbe de modalité 'certainement' est utilisé pour indiquer une forte probabilité, en exprimant un haut degré de confiance dans la véracité ou la réalisation d'une affirmation ou d'une action."
            },
            {
                'id': "0172_4",
                'type': "vrai-faux",
                'question': "L'utilisation de la modalisation peut contribuer à améliorer la qualité et l'efficacité de la communication écrite en permettant aux écrivains d'exprimer leurs opinions, leurs hypothèses ou leurs incertitudes de manière plus nuancée et plus précise, ce qui peut renforcer l'impact du message et favoriser une meilleure compréhension de la part des lecteurs.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de la modalisation peut contribuer à améliorer la qualité et l'efficacité de la communication écrite en permettant aux écrivains d'exprimer leurs opinions, leurs hypothèses ou leurs incertitudes de manière plus nuancée et plus précise, ce qui peut renforcer l'impact du message et favoriser une meilleure compréhension de la part des lecteurs, en leur offrant une perspective plus complète et plus nuancée sur les sujets abordés dans le texte."
            },
            {
                'id': "0172_5",
                'type': "qcm",
                'question': "Quel verbe modal est utilisé pour exprimer une obligation ?",
                'options': ["Pouvoir", "Devoir", "Vouloir", "Savoir"],
                'correct_option': "Devoir",
                'explanation': "Le verbe modal 'devoir' est utilisé pour exprimer une obligation, en indiquant que quelque chose est nécessaire ou requis dans une situation donnée."
            },
            {
                'id': "0172_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte de la modalisation peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre le degré de certitude ou d'incertitude exprimé par l'auteur, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte de la modalisation peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre le degré de certitude ou d'incertitude exprimé par l'auteur, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite, en créant des ambiguïtés ou en donnant une impression de manque de confiance ou de crédibilité de la part de l'auteur."
            },
            {
                'id': "0172_7",
                'type': "qcm",
                'question': "Quel verbe modal est utilisé pour exprimer une capacité ou une possibilité ?",
                'options': ["Pouvoir", "Devoir", "Vouloir", "Savoir"],
                'correct_option': "Pouvoir",
                'explanation': "Le verbe modal 'pouvoir' est utilisé pour exprimer une capacité ou une possibilité, en indiquant que quelque chose est réalisable ou accessible dans une situation donnée."
            },
            {
                'id': "0172_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte de la modalisation est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les éléments de modalisation de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte de la modalisation est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les éléments de modalisation de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre le degré de certitude ou d'incertitude exprimé par l'auteur et d'interpréter le message de manière plus précise."
            },
        ]
    ),
    (
        "0173",
        'Français 6e - Les discours direct et indirect',
        'Français',
        '6eme',
        [
            {
                'id': "0173_1",
                'type': "qcm",
                'question': "Quel est le discours direct ?",
                'options': ["Il rapporte les paroles d'un personnage telles qu'elles ont été prononcées", "Il rapporte les paroles d'un personnage de manière indirecte", "Il n'a pas de rapport avec les paroles d'un personnage", "Il est utilisé uniquement dans les dialogues"],
                'correct_option': "Il rapporte les paroles d'un personnage telles qu'elles ont été prononcées",
                'explanation': "Le discours direct rapporte les paroles d'un personnage telles qu'elles ont été prononcées, en utilisant des guillemets pour indiquer que les mots sont cités directement, ce qui permet de donner une voix distincte aux personnages et de rendre le dialogue plus vivant et plus engageant pour les lecteurs."
            },
            {
                'id': "0173_2",
                'type': "vrai-faux",
                'question': "Le discours indirect rapporte les paroles d'un personnage de manière indirecte, en utilisant des verbes de parole et des pronoms pour indiquer que les mots sont rapportés par un narrateur ou un autre personnage, ce qui permet de donner une perspective différente sur les paroles et de les intégrer de manière plus fluide dans le récit.",
                'correct': True,
                'explanation': "C'est vrai. Le discours indirect rapporte les paroles d'un personnage de manière indirecte, en utilisant des verbes de parole (comme 'dire', 'affirmer', 'expliquer') et des pronoms pour indiquer que les mots sont rapportés par un narrateur ou un autre personnage, ce qui permet de donner une perspective différente sur les paroles et de les intégrer de manière plus fluide dans le récit, tout en offrant une certaine distance entre les paroles rapportées et le lecteur."
            },
            {
                'id': "0173_3",
                'type': "qcm",
                'question': "Quel est le rôle des guillemets dans le discours direct ?",
                'options': ["Ils n'ont pas d'importance", "Ils indiquent que les mots sont cités directement", "Ils sont utilisés uniquement pour les dialogues", "Ils sont utilisés pour indiquer une citation indirecte"],
                'correct_option': "Ils indiquent que les mots sont cités directement",
                'explanation': "Les guillemets dans le discours direct indiquent que les mots sont cités directement, en encadrant les paroles d'un personnage pour montrer qu'elles sont rapportées telles qu'elles ont été prononcées, ce qui permet de différencier les paroles des personnages du reste du texte et de rendre le dialogue plus vivant et plus engageant pour les lecteurs."
            },
            {
                'id': "0173_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte du discours direct et indirect est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent ces formes de discours de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte du discours direct et indirect est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent ces formes de discours de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre qui parle et comment les paroles sont rapportées dans le récit."
            },
            {
                'id': "0173_5",
                'type': "qcm",
                'question': "Quel est le discours indirect libre ?",
                'options': ["Il rapporte les paroles d'un personnage de manière indirecte sans utiliser de verbes de parole", "Il rapporte les paroles d'un personnage telles qu'elles ont été prononcées", "Il n'a pas de rapport avec les paroles d'un personnage", "Il est utilisé uniquement dans les dialogues"],
                'correct_option': "Il rapporte les paroles d'un personnage de manière indirecte sans utiliser de verbes de parole",
                'explanation': "Le discours indirect libre rapporte les paroles d'un personnage de manière indirecte sans utiliser de verbes de parole, en intégrant les pensées et les sentiments du personnage directement dans le récit, ce qui permet de donner une perspective plus intime sur les pensées et les émotions du personnage tout en maintenant une certaine distance narrative."
            },
            {
                'id': "0173_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte du discours direct et indirect peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre qui parle ou comment les paroles sont rapportées dans le récit, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte du discours direct et indirect peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre qui parle ou comment les paroles sont rapportées dans le récit, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite, en créant des ambiguïtés ou en donnant une impression de manque de cohérence dans le récit."
            },
            {
                'id': "0173_7",
                'type': "qcm",
                'question': "Quel est le rôle des verbes de parole dans le discours indirect ?",
                'options': ["Ils n'ont pas d'importance", "Ils indiquent que les mots sont cités directement", "Ils sont utilisés pour indiquer une citation indirecte", "Ils sont utilisés uniquement pour les dialogues"],
                'correct_option': "Ils sont utilisés pour indiquer une citation indirecte",
                'explanation': "Les verbes de parole dans le discours indirect sont utilisés pour indiquer une citation indirecte, en montrant que les paroles d'un personnage sont rapportées par un narrateur ou un autre personnage, ce qui permet de donner une perspective différente sur les paroles et de les intégrer de manière plus fluide dans le récit."
            },
            {
                'id': "0173_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte du discours direct et indirect est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte du discours direct et indirect est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent ces formes de discours de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0174",
        'Français 6e - Les types de narrateurs',
        'Français',
        '6eme',
        [
            {
                'id': "0174_1",
                'type': "qcm",
                'question': "Quel est le narrateur à la première personne ?",
                'options': ["Il raconte l'histoire du point de vue d'un personnage en utilisant 'je'", "Il raconte l'histoire du point de vue d'un personnage en utilisant 'il' ou 'elle'", "Il raconte l'histoire du point de vue d'un narrateur omniscient", "Il n'est pas un narrateur"],
                'correct_option': "Il raconte l'histoire du point de vue d'un personnage en utilisant 'je'",
                'explanation': "Le narrateur à la première personne raconte l'histoire du point de vue d'un personnage en utilisant le pronom 'je', ce qui permet aux lecteurs de vivre les événements et les émotions du personnage de manière plus intime et personnelle."
            },
            {
                'id': "0174_2",
                'type': "vrai-faux",
                'question': "Le narrateur à la troisième personne peut être limité ou omniscient, en fonction de la quantité d'informations qu'il fournit sur les pensées et les sentiments des personnages, ce qui peut influencer la perspective et la compréhension des lecteurs sur les événements du récit.",
                'correct': True,
                'explanation': "C'est vrai. Le narrateur à la troisième personne peut être limité, en se concentrant sur les pensées et les sentiments d'un seul personnage, ou omniscient, en fournissant des informations sur les pensées et les sentiments de plusieurs personnages, ce qui peut influencer la perspective et la compréhension des lecteurs sur les événements du récit."
            },
            {
                'id': "0174_3",
                'type': "qcm",
                'question': "Quel est le narrateur omniscient ?",
                'options': ["Il raconte l'histoire du point de vue d'un personnage en utilisant 'je'", "Il raconte l'histoire du point de vue d'un personnage en utilisant 'il' ou 'elle'", "Il raconte l'histoire du point de vue d'un narrateur omniscient", "Il n'est pas un narrateur"],
                'correct_option': "Il raconte l'histoire du point de vue d'un narrateur omniscient",
                'explanation': "Le narrateur omniscient raconte l'histoire du point de vue d'un narrateur qui a une connaissance complète des événements, des personnages et de leurs pensées et sentiments, ce qui permet aux lecteurs d'avoir une perspective plus large sur le récit et de comprendre les motivations et les actions des personnages de manière plus approfondie."
            },
            {
                'id': "0174_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des types de narrateurs est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains choisissent le type de narrateur approprié en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des types de narrateurs est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains choisissent le type de narrateur approprié en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre la perspective à partir de laquelle l'histoire est racontée et d'interpréter le récit de manière plus précise."
            },
            {
                'id': "0174_5",
                'type': "qcm",
                'question': "Quel est le narrateur à la troisième personne limité ?",
                'options': ["Il raconte l'histoire du point de vue d'un personnage en utilisant 'je'", "Il raconte l'histoire du point de vue d'un personnage en utilisant 'il' ou 'elle'", "Il raconte l'histoire du point de vue d'un narrateur omniscient", "Il n'est pas un narrateur"],
                'correct_option': "Il raconte l'histoire du point de vue d'un personnage en utilisant 'il' ou 'elle'",
                'explanation': "Le narrateur à la troisième personne limité raconte l'histoire du point de vue d'un personnage en utilisant les pronoms 'il' ou 'elle', ce qui permet aux lecteurs de se concentrer sur les pensées et les sentiments d'un seul personnage tout en maintenant une certaine distance narrative."
            },
            {
                'id': "0174_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des types de narrateurs peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre la perspective à partir de laquelle l'histoire est racontée, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des types de narrateurs peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre la perspective à partir de laquelle l'histoire est racontée, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite, en créant des ambiguïtés ou en donnant une impression de manque de cohérence dans le récit."
            },
            {
                'id': "0174_7",
                'type': "qcm",
                'question': "Quel est le rôle du narrateur dans une histoire ?",
                'options': ["Il n'a pas d'importance", "Il raconte les événements de manière objective", "Il fournit une perspective sur les événements et les personnages", "Il est utilisé uniquement pour les dialogues"],
                'correct_option': "Il fournit une perspective sur les événements et les personnages",
                'explanation': "Le rôle du narrateur dans une histoire est de fournir une perspective sur les événements et les personnages, en racontant l'histoire à partir d'un point de vue spécifique qui peut influencer la façon dont les lecteurs interprètent le récit et comprennent les motivations et les actions des personnages."
            },
            {
                'id': "0174_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des types de narrateurs est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des types de narrateurs est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains choisissent le type de narrateur approprié en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0175",
        'Français 6e - Les champs lexicaux',
        'Français',
        '6eme',
        [
            {
                'id': "0175_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un champ lexical ?",
                'options': ["Un groupe de mots qui partagent une même racine", "Un groupe de mots qui appartiennent à la même catégorie grammaticale", "Un groupe de mots qui sont liés par un thème ou une idée commune", "Un groupe de mots qui ont des significations opposées"],
                'correct_option': "Un groupe de mots qui sont liés par un thème ou une idée commune",
                'explanation': "Un champ lexical est un groupe de mots qui sont liés par un thème ou une idée commune, en partageant des significations ou des associations similaires, ce qui permet de créer une cohérence et une unité dans un texte en utilisant des mots qui évoquent des images ou des concepts liés."
            },
            {
                'id': "0175_2",
                'type': "vrai-faux",
                'question': "L'utilisation de champs lexicaux peut contribuer à améliorer la qualité et l'efficacité de la communication écrite en permettant aux écrivains de créer une cohérence et une unité dans leur texte, en utilisant des mots qui évoquent des images ou des concepts liés, ce qui peut renforcer l'impact du message et favoriser une meilleure compréhension de la part des lecteurs.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation de champs lexicaux peut contribuer à améliorer la qualité et l'efficacité de la communication écrite en permettant aux écrivains de créer une cohérence et une unité dans leur texte, en utilisant des mots qui évoquent des images ou des concepts liés, ce qui peut renforcer l'impact du message et favoriser une meilleure compréhension de la part des lecteurs, en leur offrant une expérience de lecture plus immersive et plus engageante."
            },
            {
                'id': "0175_3",
                'type': "qcm",
                'question': "Quel est le champ lexical associé au thème de la nature ?",
                'options': ["Arbre, fleur, rivière", "Maison, voiture, ville", "Amour, amitié, famille", "Musique, danse, art"],
                'correct_option': "Arbre, fleur, rivière",
                'explanation': "Le champ lexical associé au thème de la nature comprend des mots tels que 'arbre', 'fleur' et 'rivière', qui évoquent des éléments naturels et des paysages, ce qui permet de créer une atmosphère liée à la nature dans un texte."
            },
            {
                'id': "0175_4",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des champs lexicaux peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre le thème ou l'idée commune que les écrivains cherchent à transmettre à travers les mots utilisés dans le texte.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des champs lexicaux peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre le thème ou l'idée commune que les écrivains cherchent à transmettre à travers les mots utilisés dans le texte, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite, en créant des ambiguïtés ou en donnant une impression de manque de cohérence dans le texte."
            },
            {
                'id': "0175_5",
                'type': "qcm",
                'question': "Quel est le champ lexical associé au thème de l'amour ?",
                'options': ["Amour, amitié, famille", "Maison, voiture, ville", "Arbre, fleur, rivière", "Musique, danse, art"],
                'correct_option': "Amour, amitié, famille",
                'explanation': "Le champ lexical associé au thème de l'amour comprend des mots tels que 'amour', 'amitié' et 'famille', qui évoquent des relations affectives et des émotions liées à l'amour, ce qui permet de créer une atmosphère liée à l'amour dans un texte."
            },
            {
                'id': "0175_6",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des champs lexicaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les mots de manière appropriée en fonction du thème ou de l'idée commune qu'ils souhaitent transmettre à travers leur texte.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des champs lexicaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les mots de manière appropriée en fonction du thème ou de l'idée commune qu'ils souhaitent transmettre à travers leur texte, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
            {
                'id': "0175_7",
                'type': "qcm",
                'question': "Quel est le champ lexical associé au thème de la musique ?",
                'options': ["Musique, danse, art", "Amour, amitié, famille", "Maison, voiture, ville", "Arbre, fleur, rivière"],
                'correct_option': "Musique, danse, art",
                'explanation': "Le champ lexical associé au thème de la musique comprend des mots tels que 'musique', 'danse' et 'art', qui évoquent des formes d'expression artistique liées à la musique et à la danse, ce qui permet de créer une atmosphère liée à la musique dans un texte."
            },
            {
                'id': "0175_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des champs lexicaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des champs lexicaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les mots de manière appropriée en fonction du thème ou de l'idée commune qu'ils souhaitent transmettre à travers leur texte, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0176",
        'Français 6e - Les verbes pronominaux',
        'Français',
        '6eme',
        [
            {
                'id': "0176_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un verbe pronominal ?",
                'options': ["Un verbe qui se conjugue avec un pronom réfléchi", "Un verbe qui se conjugue avec un pronom personnel", "Un verbe qui se conjugue avec un pronom possessif", "Un verbe qui se conjugue avec un pronom démonstratif"],
                'correct_option': "Un verbe qui se conjugue avec un pronom réfléchi",
                'explanation': "Un verbe pronominal est un verbe qui se conjugue avec un pronom réfléchi, en indiquant que le sujet de l'action est également l'objet de l'action, ce qui peut exprimer des actions réciproques, des actions passives ou des actions qui affectent le sujet lui-même."
            },
            {
                'id': "0176_2",
                'type': "vrai-faux",
                'question': "Les verbes pronominaux peuvent être classés en trois catégories : les verbes pronominaux réfléchis, les verbes pronominaux réciproques et les verbes pronominaux passifs, en fonction de la relation entre le sujet et l'objet de l'action exprimée par le verbe.",
                'correct': True,
                'explanation': "C'est vrai. Les verbes pronominaux peuvent être classés en trois catégories : les verbes pronominaux réfléchis, qui expriment une action que le sujet fait sur lui-même (comme 'se laver'); les verbes pronominaux réciproques, qui expriment une action que deux ou plusieurs sujets font l'un à l'autre (comme 'se parler'); et les verbes pronominaux passifs, qui expriment une action subie par le sujet (comme 'se vendre')."
            },
            {
                'id': "0176_3",
                'type': "qcm",
                'question': "Quel est le pronom réfléchi utilisé pour conjuguer un verbe pronominal à la première personne du singulier ?",
                'options': ["Me", "Te", "Se", "Nous"],
                'correct_option': "Me",
                'explanation': "Le pronom réfléchi utilisé pour conjuguer un verbe pronominal à la première personne du singulier est 'me', en indiquant que le sujet de l'action est également l'objet de l'action."
            },
            {
                'id': "0176_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des verbes pronominaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les verbes pronominaux de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des verbes pronominaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les verbes pronominaux de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre les actions exprimées par les verbes pronominaux et d'interpréter le message de manière plus précise."
            },
            {
                'id': "0176_5",
                'type': "qcm",
                'question': "Quel est le pronom réfléchi utilisé pour conjuguer un verbe pronominal à la troisième personne du pluriel ?",
                'options': ["Me", "Te", "Se", "Ils/Elles"],
                'correct_option': "Se",
                'explanation': "Le pronom réfléchi utilisé pour conjuguer un verbe pronominal à la troisième personne du pluriel est 'se', en indiquant que le sujet de l'action est également l'objet de l'action."
            },
            {
                'id': "0176_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des verbes pronominaux peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les actions exprimées par les verbes pronominaux et d'interpréter le message de manière plus précise.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des verbes pronominaux peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les actions exprimées par les verbes pronominaux et d'interpréter le message de manière plus précise, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0176_7",
                'type': "qcm",
                'question': "Quel est le pronom réfléchi utilisé pour conjuguer un verbe pronominal à la deuxième personne du singulier ?",
                'options': ["Me", "Te", "Se", "Vous"],
                'correct_option': "Te",
                'explanation': "Le pronom réfléchi utilisé pour conjuguer un verbe pronominal à la deuxième personne du singulier est 'te', en indiquant que le sujet de l'action est également l'objet de l'action."
            },
            {
                'id': "0176_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des verbes pronominaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des verbes pronominaux est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les verbes pronominaux de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0177",
        'Français 6e - Les subordonnées',
        'Français',
        '6eme',
        [
            {
                'id': "0177_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'une subordonnée ?",
                'options': ["Une proposition indépendante", "Une proposition qui dépend d'une autre proposition", "Une proposition qui n'a pas de sujet", "Une proposition qui n'a pas de verbe"],
                'correct_option': "Une proposition qui dépend d'une autre proposition",
                'explanation': "Une subordonnée est une proposition qui dépend d'une autre proposition, appelée proposition principale, et qui ne peut pas exister de manière autonome, en fournissant des informations supplémentaires sur le sujet, le verbe ou l'objet de la proposition principale."
            },
            {
                'id': "0177_2",
                'type': "vrai-faux",
                'question': "Les subordonnées peuvent être classées en plusieurs types, tels que les subordonnées relatives, les subordonnées complétives et les subordonnées circonstancielles, en fonction de leur fonction grammaticale et de leur relation avec la proposition principale.",
                'correct': True,
                'explanation': "C'est vrai. Les subordonnées peuvent être classées en plusieurs types, tels que les subordonnées relatives, qui fournissent des informations supplémentaires sur un nom ou un pronom dans la proposition principale; les subordonnées complétives, qui complètent le sens d'un verbe dans la proposition principale; et les subordonnées circonstancielles, qui indiquent les circonstances de l'action exprimée dans la proposition principale."
            },
            {
                'id': "0177_3",
                'type': "qcm",
                'question': "Quel est le rôle d'une subordonnée relative ?",
                'options': ["Elle fournit des informations supplémentaires sur un nom ou un pronom dans la proposition principale", "Elle complète le sens d'un verbe dans la proposition principale", "Elle indique les circonstances de l'action exprimée dans la proposition principale", "Elle n'a pas de rôle spécifique"],
                'correct_option': "Elle fournit des informations supplémentaires sur un nom ou un pronom dans la proposition principale",
                'explanation': "Le rôle d'une subordonnée relative est de fournir des informations supplémentaires sur un nom ou un pronom dans la proposition principale, en utilisant des pronoms relatifs (comme 'qui', 'que', 'dont') pour relier la subordonnée à l'élément qu'elle décrit."
            },
            {
                'id': "0177_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des subordonnées est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les subordonnées de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des subordonnées est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les subordonnées de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre les relations entre les différentes propositions dans un texte et d'interpréter le message de manière plus précise."
            },
            {
                'id': "0177_5",
                'type': "qcm",
                'question': "Quel est le rôle d'une subordonnée complétive ?",
                'options': ["Elle fournit des informations supplémentaires sur un nom ou un pronom dans la proposition principale", "Elle complète le sens d'un verbe dans la proposition principale", "Elle indique les circonstances de l'action exprimée dans la proposition principale", "Elle n'a pas de rôle spécifique"],
                'correct_option': "Elle complète le sens d'un verbe dans la proposition principale",
                'explanation': "Le rôle d'une subordonnée complétive est de compléter le sens d'un verbe dans la proposition principale, en fournissant des informations nécessaires pour comprendre l'action exprimée par le verbe, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0177_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des subordonnées peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les relations entre les différentes propositions dans un texte, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des subordonnées peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les relations entre les différentes propositions dans un texte, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite, en créant des ambiguïtés ou en donnant une impression de manque de cohérence dans le texte."
            },
            {
                'id': "0177_7",
                'type': "qcm",
                'question': "Quel est le rôle d'une subordonnée circonstancielle ?",
                'options': ["Elle fournit des informations supplémentaires sur un nom ou un pronom dans la proposition principale", "Elle complète le sens d'un verbe dans la proposition principale", "Elle indique les circonstances de l'action exprimée dans la proposition principale", "Elle n'a pas de rôle spécifique"],
                'correct_option': "Elle indique les circonstances de l'action exprimée dans la proposition principale",
                'explanation': "Le rôle d'une subordonnée circonstancielle est d'indiquer les circonstances de l'action exprimée dans la proposition principale, en fournissant des informations sur le temps, le lieu, la cause, la conséquence ou la condition de l'action, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0177_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des subordonnées est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des subordonnées est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les subordonnées de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0178",
        'Français 6e - Les compléments circonstanciels',
        'Français',
        '6eme',
        [
            {
                'id': "0178_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un complément circonstanciel ?",
                'options': ["Un complément qui indique le sujet de l'action", "Un complément qui indique l'objet de l'action", "Un complément qui indique les circonstances de l'action", "Un complément qui n'a pas de rôle spécifique"],
                'correct_option': "Un complément qui indique les circonstances de l'action",
                'explanation': "Un complément circonstanciel est un complément qui indique les circonstances de l'action exprimée par le verbe, en fournissant des informations sur le temps, le lieu, la cause, la conséquence ou la condition de l'action, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0178_2",
                'type': "vrai-faux",
                'question': "Les compléments circonstanciels peuvent être classés en plusieurs types, tels que les compléments circonstanciels de temps, de lieu, de cause, de conséquence et de condition, en fonction des informations qu'ils fournissent sur les circonstances de l'action exprimée par le verbe.",
                'correct': True,
                'explanation': "C'est vrai. Les compléments circonstanciels peuvent être classés en plusieurs types, tels que les compléments circonstanciels de temps, qui indiquent quand l'action se déroule; les compléments circonstanciels de lieu, qui indiquent où l'action se déroule; les compléments circonstanciels de cause, qui indiquent pourquoi l'action se déroule; les compléments circonstanciels de conséquence, qui indiquent ce qui résulte de l'action; et les compléments circonstanciels de condition, qui indiquent sous quelles conditions l'action se déroule."
            },
            {
                'id': "0178_3",
                'type': "qcm",
                'question': "Quel est le rôle d'un complément circonstanciel de temps ?",
                'options': ["Il indique quand l'action se déroule", "Il indique où l'action se déroule", "Il indique pourquoi l'action se déroule", "Il indique ce qui résulte de l'action"],
                'correct_option': "Il indique quand l'action se déroule",
                'explanation': "Le rôle d'un complément circonstanciel de temps est d'indiquer quand l'action exprimée par le verbe se déroule, en fournissant des informations sur le moment ou la durée de l'action, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0178_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des compléments circonstanciels est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les compléments circonstanciels de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des compléments circonstanciels est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les compléments circonstanciels de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
            {
                'id': "0178_5",
                'type': "qcm",
                'question': "Quel est le rôle d'un complément circonstanciel de lieu ?",
                'options': ["Il indique quand l'action se déroule", "Il indique où l'action se déroule", "Il indique pourquoi l'action se déroule", "Il indique ce qui résulte de l'action"],
                'correct_option': "Il indique où l'action se déroule",
                'explanation': "Le rôle d'un complément circonstanciel de lieu est d'indiquer où l'action exprimée par le verbe se déroule, en fournissant des informations sur le lieu ou la direction de l'action, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0178_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des compléments circonstanciels peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les circonstances de l'action exprimée par le verbe, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des compléments circonstanciels peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les circonstances de l'action exprimée par le verbe, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0178_7",
                'type': "qcm",
                'question': "Quel est le rôle d'un complément circonstanciel de cause ?",
                'options': ["Il indique quand l'action se déroule", "Il indique où l'action se déroule", "Il indique pourquoi l'action se déroule", "Il indique ce qui résulte de l'action"],
                'correct_option': "Il indique pourquoi l'action se déroule",
                'explanation': "Le rôle d'un complément circonstanciel de cause est d'indiquer pourquoi l'action exprimée par le verbe se déroule, en fournissant des informations sur les raisons ou les motivations de l'action, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0178_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des compléments circonstanciels est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des compléments circonstanciels est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les compléments circonstanciels de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0179",
        'Français 6e - Les valeurs de la négation',
        'Français',
        '6eme',
        [
            {
                'id': "0179_1",
                'type': "qcm",
                'question': "Qu'est-ce que la négation ?",
                'options': ["L'expression de l'affirmation", "L'expression de la question", "L'expression de la négation", "L'expression de l'exclamation"],
                'correct_option': "L'expression de la négation",
                'explanation': "La négation est l'expression de la négation, en utilisant des mots ou des constructions grammaticales pour indiquer que quelque chose n'est pas vrai, n'existe pas ou ne se produit pas, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0179_2",
                'type': "vrai-faux",
                'question': "La négation peut être exprimée de différentes manières en français, telles que l'utilisation de 'ne...pas', 'ne...plus', 'ne...jamais', 'ne...rien', etc., en fonction du type de négation que l'on souhaite exprimer.",
                'correct': True,
                'explanation': "C'est vrai. La négation peut être exprimée de différentes manières en français, telles que l'utilisation de 'ne...pas' pour exprimer la négation simple, 'ne...plus' pour exprimer la négation de la continuité, 'ne...jamais' pour exprimer la négation de la fréquence, 'ne...rien' pour exprimer la négation de l'existence, etc., en fonction du type de négation que l'on souhaite exprimer."
            },
            {
                'id': "0179_3",
                'type': "qcm",
                'question': "Quel est le rôle de la négation dans la communication écrite ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas"],
                'correct_option': "Elle permet d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas",
                'explanation': "Le rôle de la négation dans la communication écrite est de permettre d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas, en fournissant une perspective contrastée par rapport à l'affirmation, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0179_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte de la négation est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de négation appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte de la négation est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de négation appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre les idées ou les faits qui ne sont pas vrais ou qui ne se produisent pas, et d'interpréter le message de manière plus précise."
            },
            {
                'id': "0179_5",
                'type': "qcm",
                'question': "Quel est le rôle de la négation dans la communication écrite ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas"],
                'correct_option': "Elle permet d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas",
                'explanation': "Le rôle de la négation dans la communication écrite est de permettre d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas, en fournissant une perspective contrastée par rapport à l'affirmation, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0179_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte de la négation peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les idées ou les faits qui ne sont pas vrais ou qui ne se produisent pas, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte de la négation peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les idées ou les faits qui ne sont pas vrais ou qui ne se produisent pas, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0179_7",
                'type': "qcm",
                'question': "Quel est le rôle de la négation dans la communication écrite ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas"],
                'correct_option': "Elle permet d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas",
                'explanation': "Le rôle de la négation dans la communication écrite est de permettre d'exprimer des idées ou des faits qui ne sont pas vrais ou qui ne se produisent pas, en fournissant une perspective contrastée par rapport à l'affirmation, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0179_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte de la négation est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte de la négation est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de négation appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0180",
        'Français 6e - Les temps composés',
        'Français',
        '6eme',
        [
            {
                'id': "0180_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un temps composé ?",
                'options': ["Un temps qui se conjugue avec un auxiliaire", "Un temps qui se conjugue sans auxiliaire", "Un temps qui n'a pas de verbe", "Un temps qui n'a pas de sujet"],
                'correct_option': "Un temps qui se conjugue avec un auxiliaire",
                'explanation': "Un temps composé est un temps qui se conjugue avec un auxiliaire (comme 'avoir' ou 'être') suivi du participe passé du verbe principal, en exprimant une action qui s'est déroulée dans le passé, qui est en cours de réalisation ou qui se réalisera dans le futur, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0180_2",
                'type': "vrai-faux",
                'question': "Les temps composés peuvent être classés en plusieurs types, tels que le passé composé, le plus-que-parfait, le futur antérieur, etc., en fonction du temps de l'auxiliaire utilisé et du contexte dans lequel ils sont employés.",
                'correct': True,
                'explanation': "C'est vrai. Les temps composés peuvent être classés en plusieurs types, tels que le passé composé, qui exprime une action qui s'est déroulée dans le passé; le plus-que-parfait, qui exprime une action qui s'était déroulée avant une autre action passée; le futur antérieur, qui exprime une action qui se réalisera dans le futur avant une autre action future; etc., en fonction du temps de l'auxiliaire utilisé et du contexte dans lequel ils sont employés."
            },
            {
                'id': "0180_3",
                'type': "qcm",
                'question': "Quel est l'auxiliaire utilisé pour conjuguer les verbes pronominaux au passé composé ?",
                'options': ["Avoir", "Être", "Faire", "Aller"],
                'correct_option': "Être",
                'explanation': "L'auxiliaire utilisé pour conjuguer les verbes pronominaux au passé composé est 'être', en indiquant que le sujet de l'action est également l'objet de l'action exprimée par le verbe pronominal."
            },
            {
                'id': "0180_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des temps composés est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les temps composés de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des temps composés est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les temps composés de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre les relations temporelles entre les différentes actions exprimées par les temps composés et d'interpréter le message de manière plus précise."
            },
            {
                'id': "0180_5",
                'type': "qcm",
                'question': "Quel est l'auxiliaire utilisé pour conjuguer les verbes intransitifs au passé composé ?",
                'options': ["Avoir", "Être", "Faire", "Aller"],
                'correct_option': "Avoir",
                'explanation': "L'auxiliaire utilisé pour conjuguer les verbes intransitifs au passé composé est généralement 'avoir', en indiquant que le sujet de l'action n'est pas l'objet de l'action exprimée par le verbe intransitif."
            },
            {
                'id': "0180_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des temps composés peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les relations temporelles entre les différentes actions exprimées par les temps composés, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des temps composés peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les relations temporelles entre les différentes actions exprimées par les temps composés, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0180_7",
                'type': "qcm",
                'question': "Quel est l'auxiliaire utilisé pour conjuguer les verbes transitifs au passé composé ?",
                'options': ["Avoir", "Être", "Faire", "Aller"],
                'correct_option': "Avoir",
                'explanation': "L'auxiliaire utilisé pour conjuguer les verbes transitifs au passé composé est généralement 'avoir', en indiquant que le sujet de l'action n'est pas l'objet de l'action exprimée par le verbe transitif."
            },
            {
                'id': "0180_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des temps composés est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des temps composés est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les temps composés de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0181",
        'Français 6e - Les phrases complexes',
        'Français',
        '6eme',
        [
            {
                'id': "0181_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'une phrase complexe ?",
                'options': ["Une phrase qui contient une seule proposition", "Une phrase qui contient plusieurs propositions indépendantes", "Une phrase qui contient une proposition principale et au moins une proposition subordonnée", "Une phrase qui n'a pas de verbe"],
                'correct_option': "Une phrase qui contient une proposition principale et au moins une proposition subordonnée",
                'explanation': "Une phrase complexe est une phrase qui contient une proposition principale et au moins une proposition subordonnée, ce qui permet d'exprimer des idées plus détaillées et nuancées."
            },
            {
                'id': "0181_2",
                'type': "vrai-faux",
                'question': "Les phrases complexes peuvent être classées en plusieurs types, tels que les phrases subordonnées, les phrases coordonnées et les phrases juxtaposées, en fonction de la relation entre les différentes propositions dans la phrase.",
                'correct': True,
                'explanation': "C'est vrai. Les phrases complexes peuvent être classées en plusieurs types, tels que les phrases subordonnées, qui contiennent une proposition principale et une ou plusieurs propositions subordonnées; les phrases coordonnées, qui contiennent plusieurs propositions indépendantes reliées par des conjonctions de coordination; et les phrases juxtaposées, qui contiennent plusieurs propositions indépendantes sans conjonctions de coordination."
            },
            {
                'id': "0181_3",
                'type': "qcm",
                'question': "Quel est le rôle d'une proposition subordonnée dans une phrase complexe ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle fournit des informations supplémentaires sur un élément de la proposition principale", "Elle exprime une idée indépendante de la proposition principale", "Elle indique une relation de cause à effet avec la proposition principale"],
                'correct_option': "Elle fournit des informations supplémentaires sur un élément de la proposition principale",
                'explanation': "Le rôle d'une proposition subordonnée dans une phrase complexe est de fournir des informations supplémentaires sur un élément de la proposition principale, en ajoutant des détails ou des précisions qui enrichissent le sens de la phrase."
            },
            {
                'id': "0181_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des phrases complexes est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les phrases complexes de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des phrases complexes est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les phrases complexes de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre les relations entre les différentes propositions dans une phrase complexe et d'interpréter le message de manière plus précise."
            },
            {
                'id': "0181_5",
                'type': "qcm",
                'question': "Quel est le rôle d'une proposition coordonnée dans une phrase complexe ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle fournit des informations supplémentaires sur un élément de la proposition principale", "Elle exprime une idée indépendante de la proposition principale", "Elle indique une relation de cause à effet avec la proposition principale"],
                'correct_option': "Elle exprime une idée indépendante de la proposition principale",
                'explanation': "Le rôle d'une proposition coordonnée dans une phrase complexe est d'exprimer une idée indépendante de la proposition principale, en ajoutant une information ou une idée qui est au même niveau que la proposition principale."
            },
            {
                'id': "0181_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des phrases complexes peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les relations entre les différentes propositions dans une phrase complexe, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des phrases complexes peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les relations entre les différentes propositions dans une phrase complexe, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0181_7",
                'type': "qcm",
                'question': "Quel est le rôle d'une proposition juxtaposée dans une phrase complexe ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle fournit des informations supplémentaires sur un élément de la proposition principale", "Elle exprime une idée indépendante de la proposition principale", "Elle indique une relation de cause à effet avec la proposition principale"],
                'correct_option': "Elle exprime une idée indépendante de la proposition principale",
                'explanation': "Le rôle d'une proposition juxtaposée dans une phrase complexe est d'exprimer une idée indépendante de la proposition principale, en ajoutant une information ou une idée qui est au même niveau que la proposition principale, sans utiliser de conjonctions de coordination pour relier les propositions."
            },
            {
                'id': "0181_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des phrases complexes est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des phrases complexes est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les phrases complexes de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0182",
        'Français 6e - Les valeurs de l’impératif',
        'Français',
        '6eme',
        [
            {
                'id': "0182_1",
                'type': "qcm",
                'question': "Qu'est-ce que l'impératif ?",
                'options': ["Un mode qui exprime une action qui se déroule dans le passé", "Un mode qui exprime une action qui se déroule dans le présent", "Un mode qui exprime une action qui se déroulera dans le futur", "Un mode qui exprime un ordre, une demande ou un conseil"],
                'correct_option': "Un mode qui exprime un ordre, une demande ou un conseil",
                'explanation': "L'impératif est un mode qui exprime un ordre, une demande ou un conseil, en utilisant des formes verbales spécifiques pour indiquer que l'action exprimée par le verbe doit être réalisée par le destinataire de l'ordre, de la demande ou du conseil."
            },
            {
                'id': "0182_2",
                'type': "vrai-faux",
                'question': "L'impératif peut être utilisé pour exprimer des ordres, des demandes ou des conseils de manière polie ou informelle, en fonction du contexte et de la relation entre les interlocuteurs.",
                'correct': True,
                'explanation': "C'est vrai. L'impératif peut être utilisé pour exprimer des ordres, des demandes ou des conseils de manière polie ou informelle, en fonction du contexte et de la relation entre les interlocuteurs, en utilisant des formes verbales spécifiques pour indiquer le niveau de politesse ou d'informalité souhaité."
            },
            {
                'id': "0182_3",
                'type': "qcm",
                'question': "Quel est le rôle de l'impératif dans la communication écrite ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet d'exprimer des ordres, des demandes ou des conseils"],
                'correct_option': "Il permet d'exprimer des ordres, des demandes ou des conseils",
                'explanation': "Le rôle de l'impératif dans la communication écrite est de permettre d'exprimer des ordres, des demandes ou des conseils, en fournissant une perspective directive par rapport à l'affirmation ou à la question, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0182_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte de l'impératif est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de l'impératif appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte de l'impératif est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de l'impératif appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre les ordres, les demandes ou les conseils exprimés par l'impératif et d'interpréter le message de manière plus précise."
            },
            {
                'id': "0182_5",
                'type': "qcm",
                'question': "Pourquoi utilise-t-on l'impératif dans la communication écrite ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet d'exprimer des ordres, des demandes ou des conseils"],
                'correct_option': "Il permet d'exprimer des ordres, des demandes ou des conseils",
                'explanation': "Le rôle de l'impératif dans la communication écrite est de permettre d'exprimer des ordres, des demandes ou des conseils, en fournissant une perspective directive par rapport à l'affirmation ou à la question, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0182_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte de l'impératif peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les ordres, les demandes ou les conseils exprimés par l'impératif, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte de l'impératif peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les ordres, les demandes ou les conseils exprimés par l'impératif, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0182_7",
                'type': "qcm",
                'question': "Dans quel type de communication s'utilise l'impératif ?",
                'options': ["Dans les affirmations", "Dans les questions", "Dans les ordres, les demandes ou les conseils", "Dans les exclamations"],
                'correct_option': "Dans les ordres, les demandes ou les conseils",
                'explanation': "L'impératif s'utilise dans les ordres, les demandes ou les conseils, en fournissant une perspective directive par rapport à l'affirmation ou à la question, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0182_8",
                'type': "vrai-faux",
                'question': "L'utilisation de l'impératif ne peut se conjuguer qu'à certaines personnes.",
                'correct': True,
                'explanation': "C'est vrai. L'impératif ne se conjugue qu'à certaines personnes, généralement la deuxième personne du singulier et du pluriel, et parfois la première personne du pluriel, ce qui est essentiel pour la clarté et l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0183",
        'Français 6e - Les connecteurs temporels',
        'Français',
        '6eme',
        [
            {
                'id': "0183_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un connecteur temporel ?",
                'options': ["Un mot ou une expression qui indique une relation de cause à effet", "Un mot ou une expression qui indique une relation de comparaison", "Un mot ou une expression qui indique une relation de temps", "Un mot ou une expression qui indique une relation de lieu"],
                'correct_option': "Un mot ou une expression qui indique une relation de temps",
                'explanation': "Un connecteur temporel est un mot ou une expression qui indique une relation de temps entre les différentes actions ou événements exprimés dans une phrase ou un texte, en fournissant des informations sur la chronologie des événements et en aidant à organiser le message de manière claire et cohérente."
            },
            {
                'id': "0183_2",
                'type': "vrai-faux",
                'question': "Les connecteurs temporels peuvent être classés en plusieurs types, tels que les connecteurs de temps, les connecteurs de durée, les connecteurs de fréquence, etc., en fonction de la relation temporelle qu'ils expriment.",
                'correct': True,
                'explanation': "C'est vrai. Les connecteurs temporels peuvent être classés en plusieurs types, tels que les connecteurs de temps (comme 'avant', 'après', 'pendant', etc.), les connecteurs de durée (comme 'depuis', 'jusqu'à', etc.), les connecteurs de fréquence (comme 'souvent', 'rarement', etc.), etc., en fonction de la relation temporelle qu'ils expriment."
            },
            {
                'id': "0183_3",
                'type': "qcm",
                'question': "Quel est le rôle d'un connecteur temporel dans la communication écrite ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet d'indiquer la chronologie des événements"],
                'correct_option': "Il permet d'indiquer la chronologie des événements",
                'explanation': "Le rôle d'un connecteur temporel dans la communication écrite est de permettre d'indiquer la chronologie des événements, en fournissant des informations sur l'ordre dans lequel les actions ou les événements se déroulent, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0183_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des connecteurs temporels est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les connecteurs temporels de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des connecteurs temporels est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les connecteurs temporels de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite, en permettant aux lecteurs de comprendre la chronologie des événements exprimés dans une phrase ou un texte et d'interpréter le message de manière plus précise."
            },
            {
                'id': "0183_5",
                'type': "qcm",
                'question': "Quel est le rôle d'un connecteur de durée dans la communication écrite ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet d'indiquer la durée d'une action ou d'un événement"],
                'correct_option': "Il permet d'indiquer la durée d'une action ou d'un événement",
                'explanation': "Le rôle d'un connecteur de durée dans la communication écrite est de permettre d'indiquer la durée d'une action ou d'un événement, en fournissant des informations sur la période pendant laquelle une action se déroule ou un événement se produit, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0183_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte des connecteurs temporels peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre la chronologie des événements exprimés dans une phrase ou un texte, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte des connecteurs temporels peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre la chronologie des événements exprimés dans une phrase ou un texte, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0183_7",
                'type': "qcm",
                'question': "Quel est le rôle d'un connecteur de fréquence dans la communication écrite ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet d'indiquer la fréquence d'une action ou d'un événement"],
                'correct_option': "Il permet d'indiquer la fréquence d'une action ou d'un événement",
                'explanation': "Le rôle d'un connecteur de fréquence dans la communication écrite est de permettre d'indiquer la fréquence d'une action ou d'un événement, en fournissant des informations sur la régularité avec laquelle une action se produit ou un événement se répète, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0183_8",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des connecteurs temporels est essentielle pour éviter les confusions et les erreurs dans la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des connecteurs temporels est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les connecteurs temporels de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0184",
        'Français 6e - Les valeurs de la voix passive',
        'Français',
        '6eme',
        [
            {
                'id': "0184_1",
                'type': "qcm",
                'question': "Qu'est-ce que la voix passive ?",
                'options': ["Une construction verbale qui met l'accent sur le sujet de l'action", "Une construction verbale qui met l'accent sur l'objet de l'action", "Une construction verbale qui n'a pas de sujet", "Une construction verbale qui n'a pas d'objet"],
                'correct_option': "Une construction verbale qui met l'accent sur l'objet de l'action",
                'explanation': "La voix passive est une construction verbale qui met l'accent sur l'objet de l'action, en utilisant des formes verbales spécifiques pour indiquer que le sujet de l'action subit l'action exprimée par le verbe, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0184_2",
                'type': "vrai-faux",
                'question': "La voix passive peut être utilisée pour exprimer des actions qui sont subies par le sujet de l'action, en mettant l'accent sur l'objet de l'action plutôt que sur le sujet de l'action.",
                'correct': True,
                'explanation': "C'est vrai. La voix passive peut être utilisée pour exprimer des actions qui sont subies par le sujet de l'action, en mettant l'accent sur l'objet de l'action plutôt que sur le sujet de l'action, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0184_3",
                'type': "qcm",
                'question': "Quel est le rôle de la voix passive dans la communication écrite ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet de mettre l'accent sur l'objet de l'action"],
                'correct_option': "Elle permet de mettre l'accent sur l'objet de l'action",
                'explanation': "Le rôle de la voix passive dans la communication écrite est de permettre de mettre l'accent sur l'objet de l'action, en fournissant une perspective différente par rapport à l'affirmation ou à la question, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0184_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte de la voix passive est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de la voix passive appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte de la voix passive est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de la voix passive appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
            {
                'id': "0184_5",
                'type': "qcm",
                'question': "Pourquoi utilise-t-on la voix passive dans la communication écrite ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet de mettre l'accent sur l'objet de l'action"],
                'correct_option': "Elle permet de mettre l'accent sur l'objet de l'action",
                'explanation': "Le rôle de la voix passive dans la communication écrite est de permettre de mettre l'accent sur l'objet de l'action, en fournissant une perspective différente par rapport à l'affirmation ou à la question, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0184_6",
                'type': "vrai-faux",
                'question': "L'utilisation incorrecte de la voix passive peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les relations entre le sujet et l'objet de l'action exprimée par le verbe à la voix passive, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation incorrecte de la voix passive peut parfois entraîner des confusions ou des malentendus dans la communication écrite, en rendant difficile pour les lecteurs de comprendre les relations entre le sujet et l'objet de l'action exprimée par le verbe à la voix passive, ce qui peut nuire à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0184_7",
                'type': "qcm",
                'question': "Dans quel type de communication s'utilise la voix passive ?",
                'options': ["Dans les affirmations", "Dans les questions", "Dans les ordres, les demandes ou les conseils", "Dans les exclamations"],
                'correct_option': "Dans les affirmations",
                'explanation': "La voix passive s'utilise principalement dans les affirmations, en fournissant une perspective différente par rapport à l'affirmation ou à la question, ce qui peut être essentiel pour la clarté du message et l'efficacité de la communication écrite."
            },
            {
                'id': "0184_8",
                'type': "vrai-faux",
                'question': "L'utilisation de la voix passive ne peut se conjuguer qu'à certaines personnes.",
                'correct': True,
                'explanation': "C'est vrai. La voix passive ne se conjugue qu'à certaines personnes, généralement la troisième personne du singulier et du pluriel, ce qui est essentiel pour la clarté et l'efficacité de la communication écrite."
            },
        ]
    ),
    (
        "0185",
        'Français 6e - Les figures d’insistance',
        'Français',
        '6eme',
        [
            {
                'id': "0185_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'une figure d'insistance ?",
                'options': ["Une figure de style qui atténue l'importance d'une idée", "Une figure de style qui met en valeur une idée en la répétant ou en la soulignant", "Une figure de style qui compare deux idées", "Une figure de style qui exagère une idée"],
                'correct_option': "Une figure de style qui met en valeur une idée en la répétant ou en la soulignant",
                'explanation': "Une figure d'insistance est une figure de style qui met en valeur une idée en la répétant ou en la soulignant."
            },
            {
                'id': "0185_2",
                'type': "vrai-faux",
                'question': "Les figures d'insistance peuvent être classées en plusieurs types, tels que les anaphores, les épiphore, les répétitions, les parallélismes, etc., en fonction de la manière dont elles mettent en valeur une idée.",
                'correct': True,
                'explanation': "C'est vrai. Les figures d'insistance peuvent être classées en plusieurs types, tels que les anaphores (répétition d'un mot ou d'une expression au début de plusieurs phrases ou vers), les épiphore (répétition d'un mot ou d'une expression à la fin de plusieurs phrases ou vers), les répétitions (répétition d'un mot ou d'une expression dans une même phrase ou un même vers), les parallélismes (répétition d'une structure syntaxique similaire dans plusieurs phrases ou vers), etc., en fonction de la manière dont elles mettent en valeur une idée."
            },
            {
                'id': "0185_3",
                'type': "qcm",
                'question': "Quel est la figure d'insistance parmi ces options ?",
                'options': ["Anaphore", "Affirmation", "booléen", "Allitération"],
                'correct_option': "Anaphore",
                'explanation': "L'anaphore est une figure d'insistance qui consiste à répéter un mot ou une expression au début de plusieurs phrases ou vers, afin de mettre en valeur une idée ou un thème dans un texte."
            },
            {
                'id': "0185_4",
                'type': "qcm",
                'question': "Quel est le rôle d'une figure d'insistance dans la communication ?",
                'options': ["Atténuer l'importance d'une idée", "Mettre en valeur une idée en la répétant ou en la soulignant", "Comparer deux idées", "Exagérer une idée"],
                'correct_option': "Mettre en valeur une idée en la répétant ou en la soulignant",
                'explanation': "Le rôle d'une figure d'insistance dans la communication est de mettre en valeur une idée en la répétant ou en la soulignant."
            },
            {
                'id': "0185_5",
                'type': "vrai-faux",
                'question': "Est-il facile d'utiliser les figures d'insistance de manière appropriée dans la communication ?",
                'correct': False,
                'explanation': "Il n'est pas toujours facile d'utiliser les figures d'insistance de manière appropriée dans la communication écrite, car cela nécessite une bonne compréhension du contexte et de l'effet recherché."
            },
            {
                'id': "0185_6",
                'type': "qcm",
                'question': "Quel est la figure d'insistance parmi ces options ?",
                'options': ["Démonstration", "Hyperbole", "booléen", "Répétition"],
                'correct_option': "Hyperbole",
                'explanation': "L'hyperbole est une figure d'insistance qui consiste à exagérer une idée ou une réalité, afin de mettre en valeur une idée ou un thème dans un texte."
            },
            {
                'id': "0185_7",
                'type': "vrai-faux",
                'question': "L'utilisation correcte des figures d'insistance est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les figures d'insistance de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des figures d'insistance est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les figures d'insistance de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0185_8",
                'type': "qcm",
                'question': "Quel est la figure d'insistance parmi ces options ?",
                'options': ["Comparaison", "Allitération", "booléen", "Parallélisme"],
                'correct_option': "Parallélisme",
                'explanation': "Le parallélisme est une figure d'insistance qui consiste à répéter une structure syntaxique similaire dans plusieurs phrases ou vers, afin de mettre en valeur une idée ou un thème dans un texte."
            },
        ]
    ),
    (
        "0186",
        'Français 6e - Les valeurs de la répétition',
        'Français',
        '6eme',
        [
            {
                'id': "0186_1",
                'type': "qcm",
                'question': "Qu'est-ce que la répétition ?",
                'options': ["Une figure de style qui atténue l'importance d'une idée", "Une figure de style qui met en valeur une idée en la répétant ou en la soulignant", "Une figure de style qui compare deux idées", "Une figure de style qui exagère une idée"],
                'correct_option': "Une figure de style qui met en valeur une idée en la répétant ou en la soulignant",
                'explanation': "La répétition est une figure de style qui met en valeur une idée en la répétant ou en la soulignant."
            },
            {
                'id': "0186_2",
                'type': "vrai-faux",
                'question': "La répétition peut être classée en plusieurs types, tels que les anaphores, les épiphore, les parallélismes, etc., en fonction de la manière dont elle met en valeur une idée.",
                'correct': True,
                'explanation': "C'est vrai. La répétition peut être classée en plusieurs types, tels que les anaphores, les épiphore, les parallélismes, etc., en fonction de la manière dont elle met en valeur une idée."
            },
            {
                'id': "0186_3",
                'type': "qcm",
                'question': "Quel est la figure de répétition parmi ces options ?",
                'options': ["Anaphore", "Affirmation", "booléen", "Allitération"],
                'correct_option': "Anaphore",
                'explanation': "L'anaphore est une figure de répétition qui consiste à répéter un mot ou une expression au début de plusieurs phrases ou vers, afin de mettre en valeur une idée ou un thème dans un texte."
            },
            {
                'id': "0186_4",
                'type': "qcm",
                'question': "Quel est le rôle d'une figure de répétition dans la communication ?",
                'options': ["Atténuer l'importance d'une idée", "Mettre en valeur une idée en la répétant ou en la soulignant", "Comparer deux idées", "Exagérer une idée"],
                'correct_option': "Mettre en valeur une idée en la répétant ou en la soulignant",
                'explanation': "Le rôle d'une figure de répétition dans la communication est de mettre en valeur une idée en la répétant ou en la soulignant."
            },
            {
                'id': "0186_5",
                'type': "vrai-faux",
                'question': "Est-il facile d'utiliser les figures de répétition de manière appropriée dans la communication ?",
                'correct': False,
                'explanation': "Il n'est pas toujours facile d'utiliser les figures de répétition de manière appropriée dans la communication écrite, car cela nécessite une bonne compréhension du contexte et de l'effet recherché."
            },
            {
                'id': "0186_6",
                'type': "qcm",
                'question': "Quel est la figure de répétition parmi ces options ?",
                'options': ["Démonstration", "Hyperbole", "booléen", "Négation"],
                'correct_option': "Hyperbole",
                'explanation': "L'hyperbole est une figure de répétition qui consiste à exagérer une idée ou une réalité, afin de mettre en valeur une idée ou un thème dans un texte."
            },
            {
                'id': "0186_7",
                'type': "vrai-faux",
                'question': "Si une figure de répétition est utilisée de manière excessive ou inappropriée, cela peut entraîner des confusions ou des malentendus dans la communication, en rendant difficile à comprendre le message",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte des figures de répétition est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les figures de répétition de manière appropriée en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté du message et à l'efficacité de la communication écrite."
            },
            {
                'id': "0186_8",
                'type': "qcm",
                'question': "Quel est la figure de répétition parmi ces options ?",
                'options': ["Comparaison", "Allitération", "booléen", "Parallèle"],
                'correct_option': "Allitération",
                'explanation': "L'allitération est une figure de répétition qui consiste à répéter des sons consonantiques similaires dans plusieurs mots ou vers, afin de mettre en valeur une idée ou un thème dans un texte."
            },
        ]
    ),
    (
        "0187",
        'Français 6e - Les valeurs de la poésie',
        'Français',
        '6eme',
        [
            {
                'id': "0187_1",
                'type': "qcm",
                'question': "Qu'est-ce que la poésie ?",
                'options': ["Un genre littéraire", "Une figure de style", "Une forme de communication", "Une discipline scientifique"],
                'correct_option': "Un genre littéraire",
                'explanation': "La poésie est un genre littéraire qui utilise le langage de manière artistique pour exprimer des émotions, des idées ou des expériences."
            },
            {
                'id': "0187_2",
                'type': "vrai-faux",
                'question': "La Poésie peut être classée en plusieurs types, tels que la poésie lyrique, la poésie épique, la poésie dramatique, etc., en fonction de la manière dont elle exprime les émotions, les idées ou les expériences.",
                'correct': True,
                'explanation': "C'est vrai. La poésie peut être classée en plusieurs types, tels que la poésie lyrique (qui exprime les émotions personnelles du poète), la poésie épique (qui raconte des histoires héroïques ou mythologiques), la poésie dramatique (qui met en scène des personnages et des dialogues), etc., en fonction de la manière dont elle exprime les émotions, les idées ou les expériences."
            },
            {
                'id': "0187_3",
                'type': "qcm",
                'question': "Quel est le rôle de la poésie dans la communication ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet d'exprimer des émotions, des idées ou des expériences de manière artistique"],
                'correct_option': "Elle permet d'exprimer des émotions, des idées ou des expériences de manière artistique",
                'explanation': "Le rôle de la poésie dans la communication est de permettre d'exprimer des émotions, des idées ou des expériences de manière artistique, en utilisant le langage de manière créative pour transmettre des messages de manière plus profonde et plus significative que la communication ordinaire."
            },
            {
                'id': "0187_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte de la poésie est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de la poésie appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte de la poésie est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de la poésie appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
            {
                'id': "0187_5",
                'type': "qcm",
                'question': "Lequel de ces titres n'est pas une Poésie?",
                'options': ["La Nuit étoilée", "Le Cid", "Les Fleurs du mal", "Le Petit Prince"],
                'correct_option': "Le Petit Prince",
                'explanation': "Le Petit Prince est un roman, pas une poésie."
            },
            {
                'id': "0187_6",
                'type': "vrai-faux",
                'question': "La poésie peut se chanter.",
                'correct': True,
                'explanation': "C'est vrai. Certaines formes de poésie, comme les poèmes chantés ou les performances poétiques musicales, peuvent être accompagnées de musique pour exprimer les émotions et les idées de manière artistique."
            },
            {
                'id': "0187_7",
                'type': "qcm",
                'question': "Quel style de poésie n'a pas de rime ?",
                'options': ["Poésie lyrique", "Poésie épique", "Poésie dramatique", "Poésie en vers libres"],
                'correct_option': "Poésie en vers libres",
                'explanation': "La poésie en vers libres n'a pas de rime, contrairement aux autres styles de poésie qui peuvent utiliser des schémas de rimes spécifiques."
            },
            {
                'id': "0187_8",
                'type': "vrai-faux",
                'question': "La poésie a une composition particulière.",
                'correct': True,
                'explanation': "C'est vrai. La poésie a une composition particulière qui peut inclure des éléments tels que le rythme, la rime, la métrique et d'autres dispositifs stylistiques pour créer un effet esthétique et émotionnel."
            },
        ]
    ),
    (
        "0188",
        'Français 6e - Les valeurs de la littérature',
        'Français',
        '6eme',
        [
            {
                'id': "0188_1",
                'type': "qcm",
                'question': "Qu'est-ce que la littérature ?",
                'options': ["Un genre littéraire", "Une figure de style", "Une forme de communication", "Une discipline scientifique"],
                'correct_option': "Une forme de communication",
                'explanation': "La littérature est une forme de communication qui utilise le langage de manière artistique pour exprimer des idées, des émotions ou des expériences à travers des œuvres écrites."
            },
            {
                'id': "0188_2",
                'type': "vrai-faux",
                'question': "La littérature peut être classée en plusieurs genres, tels que la fiction, la poésie, le théâtre, etc., en fonction de la manière dont elle exprime les idées, les émotions ou les expériences.",
                'correct': True,
                'explanation': "C'est vrai. La littérature peut être classée en plusieurs genres, tels que la fiction (romans, nouvelles, etc.), la poésie, le théâtre, etc., en fonction de la manière dont elle exprime les idées, les émotions ou les expériences."
            },
            {
                'id': "0188_3",
                'type': "qcm",
                'question': "Quel est le rôle de la littérature dans la communication ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet d'exprimer des idées, des émotions ou des expériences de manière artistique"],
                'correct_option': "Elle permet d'exprimer des idées, des émotions ou des expériences de manière artistique",
                'explanation': "La littérature permet d'exprimer des idées, des émotions ou des expériences de manière artistique, en utilisant le langage de manière créative et esthétique."
            },
            {
                'id': "0188_4",
                'type': "vrai-faux",
                'question': "L'utilisation correcte de la littérature est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de la littérature appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite.",
                'correct': True,
                'explanation': "C'est vrai. L'utilisation correcte de la littérature est essentielle pour éviter les confusions et les erreurs dans la communication écrite, en assurant que les écrivains utilisent les formes de la littérature appropriées en fonction du contexte et du message qu'ils souhaitent transmettre, ce qui contribue à la clarté et à l'efficacité de la communication écrite."
            },
            {
                'id': "0188_5",
                'type': "qcm",
                'question': "Lequel de ces titres n'est pas une œuvre littéraire ?",
                'options': ["Le Cid", "Les Fleurs du mal", "Le Petit Prince", "La Tour Eiffel"],
                'correct_option': "La Tour Eiffel",
                'explanation': "La Tour Eiffel n'est pas une œuvre littéraire, c'est un monument."
            },
            {
                'id': "0188_6",
                'type': "vrai-faux",
                'question': "La littérature peut être utilisée pour transmettre des messages sociaux, politiques ou culturels importants, en fournissant une plateforme pour l'expression de différentes perspectives et expériences à travers des œuvres écrites.",
                'correct': True,
                'explanation': "C'est vrai. La littérature peut être utilisée pour transmettre des messages sociaux, politiques ou culturels importants, en fournissant une plateforme pour l'expression de différentes perspectives et expériences à travers des œuvres écrites, ce qui peut contribuer à la sensibilisation et à la compréhension de ces questions dans la société."
            },
            {
                'id': "0188_7",
                'type': "qcm",
                'question': "Quel genre littéraire n'est pas principalement axé sur l'expression artistique ?",
                'options': ["Fiction", "Poésie", "Théâtre", "Essai"],
                'correct_option': "Essai",
                'explanation': "L'essai est un genre littéraire qui se concentre davantage sur l'expression d'idées, d'arguments ou de réflexions personnelles, plutôt que sur l'expression artistique, contrairement à la fiction, à la poésie et au théâtre qui sont principalement axés sur l'expression artistique."
            },
            {
                'id': "0188_8",
                'type': "vrai-faux",
                'question': "La littérature est toujours ennuyeuse et difficile à comprendre.",
                'correct': False,
                'explanation': "C'est faux. La littérature peut être captivante et accessible, et elle offre une variété d'expériences et de styles qui peuvent plaire à différents lecteurs."
            },
        ]
    ),
    (
        "0189",
        'Français 6e - Les valeurs de la fable',
        'Français',
        '6eme',
        [
            {
                'id': "0189_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'une fable ?",
                'options': ["Un genre littéraire", "Une figure de style", "Une forme de communication", "Une discipline scientifique"],
                'correct_option': "Un genre littéraire",
                'explanation': "Une fable est un genre littéraire qui utilise des récits courts pour transmettre des leçons morales ou des valeurs."
            },
            {
                'id': "0189_2",
                'type': "vrai-faux",
                'question': "Les fables peuvent être classées en plusieurs types, tels que les fables animalières, les fables morales, etc., en fonction de la manière dont elles transmettent des leçons morales ou des valeurs.",
                'correct': True,
                'explanation': "C'est vrai. Les fables peuvent être classées en plusieurs types, tels que les fables animalières (qui mettent en scène des animaux pour transmettre des leçons morales), les fables morales (qui utilisent des personnages humains pour transmettre des leçons morales), etc., en fonction de la manière dont elles transmettent des leçons morales ou des valeurs."
            },
            {
                'id': "0189_3",
                'type': "qcm",
                'question': "Quel est le rôle d'une fable dans la communication ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet de transmettre des leçons morales ou des valeurs de manière artistique"],
                'correct_option': "Elle permet de transmettre des leçons morales ou des valeurs de manière artistique",
                'explanation': "Le rôle d'une fable dans la communication est de permettre de transmettre des leçons morales ou des valeurs de manière artistique, en utilisant des récits courts et souvent humoristiques pour enseigner des principes éthiques ou sociaux."
            },
            {
                'id': "0189_4",
                'type': "vrai-faux",
                'question': "Une fable peut être dramatique.",
                'correct': True,
                'explanation': "C'est vrai. Une fable peut être dramatique, en utilisant des situations et des personnages pour susciter des émotions fortes et transmettre des leçons morales de manière impactante."
            },
            {
                'id': "0189_5",
                'type': "qcm",
                'question': "Lequel de ces titres n'est pas une fable ?",
                'options': ["Le Corbeau et le Renard", "La Cigale et la Fourmi", "Le Petit Prince", "Le Lièvre et la Tortue"],
                'correct_option': "Le Petit Prince",
                'explanation': "Le Petit Prince n'est pas une fable, c'est un roman."
            },
            {
                'id': "0189_6",
                'type': "vrai-faux",
                'question': "Les fables peuvent être utilisées pour enseigner des leçons morales ou des valeurs importantes aux lecteurs, en fournissant des exemples concrets et des récits engageants qui illustrent ces leçons de manière artistique.",
                'correct': True,
                'explanation': "C'est vrai. Les fables peuvent être utilisées pour enseigner des leçons morales ou des valeurs importantes aux lecteurs, en fournissant des exemples concrets et des récits engageants qui illustrent ces leçons de manière artistique, ce qui peut contribuer à la sensibilisation et à la compréhension de ces leçons chez les lecteurs."
            },
            {
                'id': "0189_7",
                'type': "qcm",
                'question': "Quel est le rôle d'une fable dans la communication ?",
                'options': ["Elle n'a pas de rôle spécifique", "Elle permet d'exprimer des affirmations", "Elle permet d'exprimer des questions", "Elle permet de transmettre des leçons morales ou des valeurs de manière artistique"],
                'correct_option': "Elle permet de transmettre des leçons morales ou des valeurs de manière artistique",
                'explanation': "Le rôle d'une fable dans la communication est de permettre de transmettre des leçons morales ou des valeurs de manière artistique, en utilisant des récits courts et souvent humoristiques pour enseigner des principes éthiques ou sociaux."
            },
            {
                'id': "0189_8",
                'type': "vrai-faux",
                'question': "La fable est un genre littéraire qui utilise des récits courts pour transmettre des leçons morales ou des valeurs.",
                'correct': True,
                'explanation': "C'est vrai. La fable est un genre littéraire qui utilise des récits courts pour transmettre des leçons morales ou des valeurs."
            },
        ]
    ),
    (
        "0190",
        'Français 6e - Les valeurs du récit historique',
        'Français',
        '6eme',
        [
            {
                'id': "0190_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un récit historique ?",
                'options': ["Un genre littéraire", "Une figure de style", "Une forme de communication", "Une discipline scientifique"],
                'correct_option': "Un genre littéraire",
                'explanation': "Un récit historique est un genre littéraire qui raconte des événements passés, souvent basés sur des faits réels, pour transmettre des connaissances historiques ou des leçons à travers une narration."
            },
            {
                'id': "0190_2",
                'type': "vrai-faux",
                'question': "Les récits historiques peuvent être classés en plusieurs types, tels que les biographies, les chroniques, les romans historiques, etc., en fonction de la manière dont ils racontent des événements passés.",
                'correct': True,
                'explanation': "C'est vrai. Les récits historiques peuvent être classés en plusieurs types, tels que les biographies (qui racontent la vie d'une personne), les chroniques (qui racontent des événements dans l'ordre chronologique), les romans historiques (qui racontent des événements passés avec une certaine liberté artistique), etc., en fonction de la manière dont ils racontent des événements passés."
            },
            {
                'id': "0190_3",
                'type': "qcm",
                'question': "Quel est le rôle d'un récit historique dans la communication ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet de transmettre des connaissances historiques ou des leçons à travers une narration"],
                'correct_option': "Il permet de transmettre des connaissances historiques ou des leçons à travers une narration",
                'explanation': "Le rôle d'un récit historique dans la communication est de permettre de transmettre des connaissances historiques ou des leçons à travers une narration, en utilisant des événements passés pour éclairer le présent et offrir des perspectives sur l'histoire et la société."
            },
            {
                'id': "0190_4",
                'type': "vrai-faux",
                'question': "Un récit historique peut être fictif.",
                'correct': True,
                'explanation': "C'est vrai. Un récit historique peut être fictif, en utilisant des éléments imaginaires ou romancés pour raconter une histoire basée sur des événements passés, ce qui peut rendre l'histoire plus engageante et accessible pour les lecteurs."
            },
            {
                'id': "0190_5",
                'type': "qcm",
                'question': "Lequel de ces titres n'est pas un récit historique ?",
                'options': ["Guerre et Paix", "Les Misérables", "Le Petit Prince", "La Guerre du Feu"],
                'correct_option': "Le Petit Prince",
                'explanation': "Le Petit Prince n'est pas un récit historique. C'est un conte philosophique et poétique écrit par Antoine de Saint-Exupéry, qui raconte l'histoire d'un petit prince voyageant de planète en planète et rencontrant divers personnages, avec des leçons de vie et des réflexions sur la nature humaine."
            },
            {
                'id': "0190_6",
                'type': "vrai-faux",
                'question': "Les récits historiques peuvent être utilisés pour transmettre des connaissances historiques ou des leçons importantes aux lecteurs, en fournissant des perspectives sur les événements passés et en éclairant le présent à travers la narration de ces événements.",
                'correct': True,
                'explanation': "C'est vrai. Les récits historiques peuvent être utilisés pour transmettre des connaissances historiques ou des leçons importantes aux lecteurs, en fournissant des perspectives sur les événements passés et en éclairant le présent à travers la narration de ces événements, ce qui peut contribuer à la sensibilisation et à la compréhension de l'histoire et de la société."
            },
            {
                'id': "0190_7",
                'type': "qcm",
                'question': "Quel est le rôle d'un récit historique dans la communication ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet de transmettre des connaissances historiques ou des leçons à travers une narration"],
                'correct_option': "Il permet de transmettre des connaissances historiques ou des leçons à travers une narration",
                'explanation': "Le rôle d'un récit historique dans la communication est de permettre de transmettre des connaissances historiques ou des leçons à travers une narration, en utilisant des événements passés pour éclairer le présent et offrir des perspectives sur l'histoire et la société."
            },
            {
                'id': "0190_8",
                'type': "vrai-faux",
                'question': "Un récit historique est un genre littéraire qui raconte des événements passés, souvent basés sur des faits réels, pour transmettre des connaissances historiques ou des leçons à travers une narration.",
                'correct': True,
                'explanation': "C'est vrai. Un récit historique est un genre littéraire qui raconte des événements passés, souvent basés sur des faits réels, pour transmettre des connaissances historiques ou des leçons à travers une narration."
            },
            ]
    ),
    (
        "0191",
        'Français 6e - Les valeurs du texte descriptif',
        'Français',
        '6eme',
        [
            {
                'id': "0191_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un texte descriptif ?",
                'options': ["Un genre littéraire", "Une figure de style", "Une forme de communication", "Une discipline scientifique"],
                'correct_option': "Une forme de communication",
                'explanation': "Un texte descriptif a pour but de décrire une personne, un lieu, un objet ou une situation de manière détaillée, permettant au lecteur de se représenter mentalement ce qui est décrit."
            },
            {
                'id': "0191_2",
                'type': "vrai-faux",
                'question': "Les textes descriptifs peuvent être classés en plusieurs types, tels que les descriptions de personnes, les descriptions de lieux, les descriptions d'objets, etc., en fonction de ce qui est décrit dans le texte.",
                'correct': True,
                'explanation': "C'est vrai. Les textes descriptifs peuvent être classés en plusieurs types, tels que les descriptions de personnes (qui décrivent les caractéristiques physiques ou psychologiques d'une personne), les descriptions de lieux (qui décrivent les caractéristiques d'un lieu), les descriptions d'objets (qui décrivent les caractéristiques d'un objet), etc., en fonction de ce qui est décrit dans le texte."
            },
            {
                'id': "0191_3",
                'type': "qcm",
                'question': "Quel est le rôle d'un texte descriptif dans la communication ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet de décrire une personne, un lieu, un objet ou une situation de manière détaillée"],
                'correct_option': "Il permet de décrire une personne, un lieu, un objet ou une situation de manière détaillée",
                'explanation': "Le rôle d'un texte descriptif dans la communication est de permettre de décrire une personne, un lieu, un objet ou une situation de manière détaillée, en utilisant des mots et des phrases pour créer une image mentale chez le lecteur."
            },
            {
                'id': "0191_4",
                'type': "vrai-faux",
                'question': "Un texte descriptif peut être utilisé pour transmettre des informations importantes sur une personne, un lieu, un objet ou une situation, en fournissant des détails qui aident à comprendre et à visualiser ce qui est décrit.",
                'correct': True,
                'explanation': "C'est vrai. Un texte descriptif peut être utilisé pour transmettre des informations importantes sur une personne, un lieu, un objet ou une situation, en fournissant des détails qui aident à comprendre et à visualiser ce qui est décrit."
            },
            {
                'id': "0191_5",
                'type': "qcm",
                'question': "Lequel de ces titres n'est pas un texte descriptif ?",
                'options': ["La Description d'une Personne", "La Description d'un Lieu", "La Description d'un Objet", "Le Petit Prince"],
                'correct_option': "Le Petit Prince",
                'explanation': "Le Petit Prince n'est pas un texte descriptif. C'est un conte philosophique et poétique écrit par Antoine de Saint-Exupéry."
            },
            {
                'id': "0191_6",
                'type': "vrai-faux",
                'question': "Les textes descriptifs peuvent être utilisés pour créer des images mentales vivantes et engageantes pour les lecteurs, en utilisant des détails sensoriels et des descriptions précises pour rendre les personnes, les lieux, les objets ou les situations plus réels et plus tangibles dans l'esprit du lecteur.",
                'correct': True,
                'explanation': "C'est vrai. Les textes descriptifs peuvent être utilisés pour créer des images mentales vivantes et engageantes pour les lecteurs, en utilisant des détails sensoriels et des descriptions précises pour rendre les personnes, les lieux, les objets ou les situations plus réels et plus tangibles dans l'esprit du lecteur."
            },
            {
                'id': "0191_7",
                'type': "qcm",
                'question': "Un texte descriptif utilise quel ton ?",
                'options': ["Un ton humoristique", "Un ton dramatique", "Un ton descriptif", "Un ton narratif"],
                'correct_option': "Un ton descriptif",
                'explanation': "Un texte descriptif utilise un ton descriptif, en se concentrant sur les détails et les caractéristiques des personnes, des lieux, des objets ou des situations."
            },
            {
                'id': "0191_8",
                'type': "vrai-faux",
                'question': "Un texte descriptif a pour but de décrire une personne, un lieu, un objet ou une situation de manière détaillée.",
                'correct': True,
                'explanation': "C'est vrai. Un texte descriptif a pour but de décrire une personne, un lieu, un objet ou une situation de manière détaillée."
            },
        ]
    ),
    (
        "0192",
        'Français 6e - Les valeurs du texte injonctif',
        'Français',
        '6eme',
        [
            {
                'id': "0192_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un texte injonctif ?",
                'options': ["Un genre littéraire", "Une figure de style", "Une forme de communication", "Une discipline scientifique"],
                'correct_option': "Une forme de communication",
                'explanation': "Un texte injonctif a pour but de donner des instructions, des conseils ou des ordres à un lecteur ou à un auditeur, en utilisant des verbes à l'impératif et des phrases directives."
            },
            {
                'id': "0192_2",
                'type': "vrai-faux",
                'question': "Les textes injonctifs peuvent être classés en plusieurs types, tels que les recettes de cuisine, les manuels d'instructions, les règles de jeu, etc., en fonction de la manière dont ils donnent des instructions ou des conseils.",
                'correct': True,
                'explanation': "C'est vrai. Les textes injonctifs peuvent être classés en plusieurs types, tels que les recettes de cuisine (qui donnent des instructions pour préparer des plats), les manuels d'instructions (qui donnent des instructions pour utiliser un produit ou accomplir une tâche), les règles de jeu (qui donnent des instructions pour jouer à un jeu), etc., en fonction de la manière dont ils donnent des instructions ou des conseils."
            },
            {
                'id': "0192_3",
                'type': "qcm",
                'question': "Quel est le rôle d'un texte injonctif dans la communication ?",
                'options': ["Il n'a pas de rôle spécifique", "Il permet d'exprimer des affirmations", "Il permet d'exprimer des questions", "Il permet de donner des instructions, des conseils ou des ordres à un lecteur ou à un auditeur"],
                'correct_option': "Il permet de donner des instructions, des conseils ou des ordres à un lecteur ou à un auditeur",
                'explanation': "Le rôle d'un texte injonctif dans la communication est de permettre de donner des instructions, des conseils ou des ordres à un lecteur ou à un auditeur, en utilisant des verbes à l'impératif et des phrases directives pour guider les actions ou les comportements du destinataire."
            },
            {
                'id': "0192_4",
                'type': "vrai-faux",
                'question': "Un texte injonctif peut être utilisé pour transmettre des informations importantes sur la manière d'accomplir une tâche ou de suivre une procédure, en fournissant des instructions claires et précises qui aident le destinataire à comprendre et à exécuter les actions nécessaires.",
                'correct': True,
                'explanation': "C'est vrai. Un texte injonctif peut être utilisé pour transmettre des informations importantes sur la manière d'accomplir une tâche ou de suivre une procédure, en fournissant des instructions claires et précises qui aident le destinataire à comprendre et à exécuter les actions nécessaires."
            },
            {
                'id': "0192_5",
                'type': "qcm",
                'question': "Lequel de ces titres n'est pas un texte injonctif ?",
                'options': ["Recette de Cuisine", "Manuel d'Instructions", "Règles de Jeu", "Le Petit Prince"],
                'correct_option': "Le Petit Prince",
                'explanation': "Le Petit Prince n'est pas un texte injonctif. C'est un conte philosophique et poétique écrit par Antoine de Saint-Exupéry."
            },
            {
                'id': "0192_6",
                'type': "vrai-faux",
                'question': "Les textes injonctifs peuvent être utilisés pour guider les actions ou les comportements des destinataires, en fournissant des instructions claires et précises qui aident à accomplir des tâches, à suivre des procédures ou à respecter des règles, ce qui peut contribuer à la sécurité, à l'efficacité et au bon fonctionnement dans divers contextes.",
                'correct': True,
                'explanation': "C'est vrai. Les textes injonctifs peuvent être utilisés pour guider les actions ou les comportements des destinataires, en fournissant des instructions claires et précises qui aident à accomplir des tâches, à suivre des procédures ou à respecter des règles, ce qui peut contribuer à la sécurité, à l'efficacité et au bon fonctionnement dans divers contextes."
            },
            {
                'id': "0192_7",
                'type': "qcm",
                'question': "Un texte injonctif utilise quel ton ?",
                'options': ["Un ton humoristique", "Un ton dramatique", "Un ton descriptif", "Un ton impératif"],
                'correct_option': "Un ton impératif",
                'explanation': "Un texte injonctif utilise un ton impératif, en se concentrant sur les instructions et les directives pour guider les actions ou les comportements du destinataire."
            },
            {
                'id': "0192_8",
                'type': "vrai-faux",
                'question': "Un texte injonctif a pour but de donner des instructions, des conseils ou des ordres à un lecteur ou à un auditeur.",
                'correct': True,
                'explanation': "C'est vrai. Un texte injonctif a pour but de donner des instructions, des conseils ou des ordres à un lecteur ou à un auditeur."
            },
        ]
    )
]


def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []
    for question in questions:
        qtype = str(question.get("type", "texte"))
        if qtype == "qcm":
            runtime_questions.append({"type": "qcm", "question": str(question.get("question", "")), "choices": list(question.get("options", []))})
        elif qtype == "vrai-faux":
            runtime_questions.append({"type": "vrai-faux", "question": str(question.get("question", ""))})
        else:
            runtime_questions.append({"type": "open", "question": str(question.get("question", ""))})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "type": "quiz", "level": level, "subject": subject, "description": f"Diagnostic {subject} {level} : {title}", "status": "published", "created_at": created_at, "updated_at": created_at},
        "quiz": {"title": title, "type": "quiz", "level": level, "subject": subject, "question_count": len(runtime_questions), "passing_score": 70, "time_limit_minutes": 15, "questions": runtime_questions},
        "exercisenotion": [],
        "exerciseresponses": [],
    }

def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        if q["type"] == "qcm":
            answers.append({"index": index, "question_id": index + 1, "type": "qcm", "answer": q["correct_option"], "correction": q["explanation"]})
        elif q["type"] == "vrai-faux":
            answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q["correct"] else "faux", "correction": q["explanation"]})
        else:
            answers.append({"index": index, "question_id": index + 1, "type": "open", "answer": q.get("correct_answer", ""), "correction": q["explanation"]})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - Série {qid}", "level": level, "subject": subject},
        "quiz": {"title": title, "question_count": len(answers), "level": level, "subject": subject, "answers": answers},
    }

def write_quiz_files():
    os.makedirs(QUIZ_DIR, exist_ok=True)
    os.makedirs(ANSWERS_DIR, exist_ok=True)
    for qid, title, subject, level, questions in quizzes_data:
        quiz = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)
        with open(os.path.join(QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as f:
            json.dump(quiz, f, ensure_ascii=False, indent=2)
            f.write("\n")
        with open(os.path.join(ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as f:
            json.dump(answers, f, ensure_ascii=False, indent=2)
            f.write("\n")
    print(f"{len(quizzes_data)} quiz generated in {OUTPUT_DIR}")

if __name__ == "__main__":
    write_quiz_files()
