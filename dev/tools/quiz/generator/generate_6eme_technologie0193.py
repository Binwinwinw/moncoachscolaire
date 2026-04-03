#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Générateur quiz Technologie 6e — SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "technologie_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")

quizzes_data = [
    (
        0193,
        "Découverte de la technologie",
        "Technologie",
        "6e",
        [

            {
                'id': "0193_1",
                'type': "qcm",
                'question': "Qu'est-ce que la technologie ?",
                'options': ["Une matière scientifique", "L'étude des objets techniques", "Un sport", "Une langue"],
                'correct_option': "L'étude des objets techniques",
                'explanation': "La technologie est l'étude des objets techniques et de leur utilisation."
            },
            {
                'id': "0193_2",
                'type': "vrai-faux",
                'question': "La technologie est uniquement utilisée pour fabriquer des ordinateurs. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la technologie est utilisée dans de nombreux domaines, pas seulement pour les ordinateurs."
            },
            {
                'id': "0193_3",
                'type': "qcm",
                'question': "Donne un exemple d'objet technique que tu utilises tous les jours.",
                'options': ["Un smartphone", "Une voiture", "Une lampe", "Autre"],
                'correct_option': "Un smartphone",
                'explanation': "Il existe de nombreux objets techniques que nous utilisons quotidiennement, comme les smartphones, les voitures, les lampes, etc."
            },
            {
                'id': "0193_4",
                'type': "vrai-faux",
                'question': "La technologie peut aider à résoudre des problèmes du quotidien. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la technologie peut être utilisée pour trouver des solutions à de nombreux problèmes du quotidien."
            },
            {
                'id': "0193_5",
                'type': "qcm",
                'question': "Quel est le but principal de la technologie ?",
                'options': ["Améliorer la vie des gens", "Créer des œuvres d'art", "Étudier les animaux", "Faire du sport"],
                'correct_option': "Améliorer la vie des gens",
                'explanation': "Le but principal de la technologie est d'améliorer la vie des gens en créant des outils et des solutions pour répondre à leurs besoins."
            },
            {
                'id': "0193_6",
                'type': "vrai-faux",
                'question': "La technologie est un domaine qui évolue rapidement. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la technologie évolue constamment avec de nouvelles inventions et innovations."
            },
            {
                'id': "0193_7",
                'type': "qcm",
                'question': "Qu'est-ce qu'un objet technique ?",
                'options': ["Un objet naturel", "Un objet fabriqué par l'homme pour répondre à un besoin", "Un animal", "Une plante"],
                'correct_option': "Un objet fabriqué par l'homme pour répondre à un besoin",
                'explanation': "Un objet technique est un objet fabriqué par l'homme pour répondre à un besoin spécifique."
            },
            {
                'id': "0193_8",
                'type': "vrai-faux",
                'question': "La technologie peut être utilisée pour créer des œuvres d'art. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la technologie peut être utilisée pour créer des œuvres d'art, comme la musique, les films, les jeux vidéo, etc."
            },
        ]
    ),
    (
        0194,
        "Les matériaux et leurs propriétés",
        "Technologie",
        "6e",
        [
            {
                'id': "0194_1",
                'type': "qcm",
                'question': "Quels sont les trois états de la matière ?",
                'options': ["Solide, liquide, gaz", "Solide, liquide, plasma", "Liquide, gaz, plasma", "Solide, gaz, plasma"],
                'correct_option': "Solide, liquide, gaz",
                'explanation': "Les trois états de la matière sont le solide, le liquide et le gaz."
            },
            {
                'id': "0194_2",
                'type': "vrai-faux",
                'question': "Le bois est un matériau naturel. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le bois est un matériau naturel provenant des arbres."
            },
            {
                'id': "0194_3",
                'type': "qcm",
                'question': "Quel matériau est le plus conducteur d'électricité ?",
                'options': ["Plastique", "Bois", "Métal", "Verre"],
                'correct_option': "Métal",
                'explanation': "Le métal est un excellent conducteur d'électricité, contrairement au plastique, au bois et au verre."
            },
            {
                'id': "0194_4",
                'type': "vrai-faux",
                'question': "Le verre est un matériau transparent. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le verre est un matériau transparent qui permet de voir à travers lui."
            },
            {
                'id': "0194_5",
                'type': "qcm",
                'question': "Quel matériau est le plus résistant à la chaleur ?",
                'options': ["Plastique", "Bois", "Métal", "Verre"],
                'correct_option': "Métal",
                'explanation': "Le métal est généralement plus résistant à la chaleur que le plastique, le bois et le verre."
            },
            {
                'id': "0194_6",
                'type': "vrai-faux",
                'question': "Le plastique est un matériau recyclable. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le plastique peut être recyclé et réutilisé pour fabriquer de nouveaux objets."
            },
            {
                'id': "0194_7",
                'type': "qcm",
                'question': "Quel matériau est le plus léger ?",
                'options': ["Plastique", "Bois", "Métal", "Verre"],
                'correct_option': "Plastique",
                'explanation': "Le plastique est généralement plus léger que le bois, le métal et le verre."
            },
            {
                'id': "0194_8",
                'type': "vrai-faux",
                'question': "Le bois est un matériau renouvelable. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le bois est un matériau renouvelable car il peut être replanté et cultivé à nouveau."
            },
        ]
    ),
    (
        0195,
        "Les énergies renouvelables",
        "Technologie",
        "6eme",
        [
            {
                'id': "0195_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'une énergie renouvelable ?",
                'options': ["Une énergie qui ne s'épuise pas", "Une énergie qui pollue beaucoup", "Une énergie qui vient du pétrole", "Une énergie qui est très chère"],
                'correct_option': "Une énergie qui ne s'épuise pas",
                'explanation': "Une énergie renouvelable est une énergie qui ne s'épuise pas et qui peut être utilisée de manière durable, comme l'énergie solaire, éolienne, hydraulique, etc."
            },
            {
                'id': "0195_2",
                'type': "vrai-faux",
                'question': "L'énergie solaire est une énergie renouvelable. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'énergie solaire est une énergie renouvelable qui provient du soleil."
            },
            {
                'id': "0195_3",
                'type': "qcm",
                'question': "Quel est l'avantage principal des énergies renouvelables ?",
                'options': ["Elles sont gratuites", "Elles ne polluent pas", "Elles sont faciles à stocker", "Elles sont disponibles partout"],
                'correct_option': "Elles ne polluent pas",
                'explanation': "L'avantage principal des énergies renouvelables est qu'elles ne polluent pas l'environnement, contrairement aux énergies fossiles."
            },
            {
                'id': "0195_4",
                'type': "vrai-faux",
                'question': "L'énergie éolienne est produite par le vent. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'énergie éolienne est produite par le mouvement du vent qui fait tourner les éoliennes."
            },
            {
                'id': "0195_5",
                'type': "qcm",
                'question': "Quel est le principal inconvénient des énergies renouvelables ?",
                'options': ["Elles sont très chères", "Elles ne sont pas fiables", "Elles nécessitent beaucoup d'espace", "Elles sont difficiles à utiliser"],
                'correct_option': "Elles nécessitent beaucoup d'espace",
                'explanation': "Le principal inconvénient des énergies renouvelables est qu'elles nécessitent souvent beaucoup d'espace pour être installées, comme les panneaux solaires ou les éoliennes."
            },
            {
                'id': "0195_6",
                'type': "vrai-faux",
                'question': "L'énergie hydraulique est produite par l'eau. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'énergie hydraulique est produite par le mouvement de l'eau, comme les barrages hydroélectriques."
            },
            {
                'id': "0195_7",
                'type': "qcm",
                'question': "Quel est le principal défi pour l'utilisation des énergies renouvelables ?",
                'options': ["Le coût élevé", "La dépendance au climat", "La pollution", "La rareté des ressources"],
                'correct_option': "La dépendance au climat",
                'explanation': "Le principal défi pour l'utilisation des énergies renouvelables est leur dépendance au climat, car elles peuvent être moins efficaces dans certaines conditions météorologiques."
            },
            {
                'id': "0195_8",
                'type': "vrai-faux",
                'question': "L'énergie géothermique est une énergie renouvelable. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'énergie géothermique est une énergie renouvelable qui provient de la chaleur de la Terre."
            },
        ]
    ),
    (
        0196,
        "Les objets connectés",
        "Technologie",
        "6eme",
        [
            {
                'id': "0196_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un objet connecté ?",
                'options': ["Un objet qui peut se connecter à Internet", "Un objet qui est très cher", "Un objet qui est fabriqué en plastique", "Un objet qui est très grand"],
                'correct_option': "Un objet qui peut se connecter à Internet",
                'explanation': "Un objet connecté est un objet qui peut se connecter à Internet pour envoyer et recevoir des données, comme les smartphones, les montres connectées, les assistants vocaux, etc."
            },
            {
                'id': "0196_2",
                'type': "vrai-faux",
                'question': "Les objets connectés peuvent aider à améliorer notre vie quotidienne. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les objets connectés peuvent aider à améliorer notre vie quotidienne en facilitant certaines tâches, en offrant des informations en temps réel, et en permettant une meilleure gestion de notre environnement."
            },
        ]
    ),
    (
        0197,
        "Les objets connectés et la sécurité",
        "Technologie",
        "6eme",
        [
            {
                'id': "0197_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un objet connecté ?",
                'options': ["Un objet qui peut se connecter à Internet", "Un objet qui est très cher", "Un objet qui est fabriqué en plastique", "Un objet qui est très grand"],
                'correct_option': "Un objet qui peut se connecter à Internet",
                'explanation': "Un objet connecté est un objet qui peut se connecter à Internet pour envoyer et recevoir des données, comme les smartphones, les montres connectées, les assistants vocaux, etc."
            },
            {
                'id': "0197_2",
                'type': "vrai-faux",
                'question': "Les objets connectés peuvent aider à améliorer notre vie quotidienne. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les objets connectés peuvent aider à améliorer notre vie quotidienne en facilitant certaines tâches, en offrant des informations en temps réel, et en permettant une meilleure gestion de notre environnement."
            },
            {
                'id': "0197_3",
                'type': "qcm",
                'question': "Quel est le principal risque lié aux objets connectés ?",
                'options': ["Le coût élevé", "La dépendance à la technologie", "La violation de la vie privée", "La pollution"],
                'correct_option': "La violation de la vie privée",
                'explanation': "Le principal risque lié aux objets connectés est la violation de la vie privée, car ils collectent souvent des données personnelles qui peuvent être utilisées à des fins malveillantes."
            },
            {
                'id': "0197_4",
                'type': "vrai-faux",
                'question': "Il est important de sécuriser les objets connectés pour protéger nos données personnelles. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, il est crucial de sécuriser les objets connectés en utilisant des mots de passe forts, en mettant à jour régulièrement le firmware, et en étant vigilant quant aux permissions accordées aux applications pour protéger nos données personnelles."
            },
            {
                'id': "0197_5",
                'type': "qcm",
                'question': "Quel est un moyen de protéger la sécurité de nos objets connectés ?",
                'options': ["Utiliser des mots de passe forts", "Ne jamais les utiliser", "Partager nos données avec tout le monde", "Ne pas les mettre à jour"],
                'correct_option': "Utiliser des mots de passe forts",
                'explanation': "Pour protéger la sécurité de nos objets connectés, il est important d'utiliser des mots de passe forts, de ne pas partager nos données avec tout le monde, et de mettre à jour régulièrement le firmware pour corriger les vulnérabilités de sécurité."
            },
            {
                'id': "0197_6",
                'type': "vrai-faux",
                'question': "Il est sûr de connecter tous les objets à Internet sans protections. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il n'est pas sûr de connecter tous les objets à Internet sans protections, car cela peut exposer les données personnelles à des risques de piratage et de violation de la vie privée."
            },
            {
                'id': "0197_7",
                'type': "qcm",
                'question': "Quel est un exemple d'objet connecté qui peut présenter des risques de sécurité ?",
                'options': ["Un smartphone", "Une montre connectée", "Un assistant vocal", "Tous les objets connectés"],
                'correct_option': "Tous les objets connectés",
                'explanation': "Tous les objets connectés peuvent présenter des risques de sécurité s'ils ne sont pas correctement sécurisés, car ils collectent souvent des données personnelles qui peuvent être utilisées à des fins malveillantes."
            },
            {
                'id': "0197_8",
                'type': "vrai-faux",
                'question': "Il est important de lire les politiques de confidentialité des objets connectés que nous utilisons. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, il est important de lire les politiques de confidentialité des objets connectés que nous utilisons pour comprendre quelles données sont collectées, comment elles sont utilisées, et quelles mesures de sécurité sont en place pour protéger nos informations personnelles."
            },
        ]
    ),
    (
        0198,
        "Schématiser le fonctionnement d'un objet technique simple.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0198_1",
                'type': "qcm",
                'question': "Comment schématiser le fonctionnement d'un objet technique simple ?",
                'options': ["En dessinant un diagramme de flux", "En écrivant une description détaillée", "En créant une maquette", "En faisant une présentation orale"],
                'correct_option': "En dessinant un diagramme de flux",
                'explanation': "Pour schématiser le fonctionnement d'un objet technique simple, il est souvent utile de dessiner un diagramme de flux qui montre les différentes étapes du processus et les interactions entre les composants."
            },
            {
                'id': "0198_2",
                'type': "vrai-faux",
                'question': "L'usage d'un objet technique apporte-t-il des avantages dans la vie de tous les jours ? Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'usage d'un objet technique apporte des avantages dans la vie de tous les jours en facilitant certaines tâches, en améliorant l'efficacité et en offrant de nouvelles possibilités."
            },
            {
                'id': "0198_3",
                'type': "qcm",
                'question': "Quel est l'avantage de schématiser le fonctionnement d'un objet technique simple ?",
                'options': ["Cela permet de mieux comprendre le fonctionnement de l'objet", "Cela rend l'objet plus joli", "Cela permet de le vendre plus cher", "Cela n'a aucun avantage"],
                'correct_option': "Cela permet de mieux comprendre le fonctionnement de l'objet",
                'explanation': "Schématiser le fonctionnement d'un objet technique simple permet de mieux comprendre comment il fonctionne, quelles sont les différentes étapes du processus, et comment les composants interagissent."
            },
            {
                'id': "0198_4",
                'type': "vrai-faux",
                'question': "Il est important de schématiser le fonctionnement d'un objet technique pour pouvoir le réparer en cas de panne. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, schématiser le fonctionnement d'un objet technique peut être très utile pour pouvoir le réparer en cas de panne, car cela permet de comprendre comment les différentes parties fonctionnent ensemble et où se situe le problème."
            },
            {
                'id': "0198_5",
                'type': "qcm",
                'question': "Quel est un exemple d'objet technique simple que l'on peut schématiser ?",
                'options': ["Une lampe", "Un ordinateur", "Une voiture", "Un avion"],
                'correct_option': "Une lampe",
                'explanation': "Une lampe est un exemple d'objet technique simple que l'on peut schématiser, car elle a un fonctionnement relativement simple et des composants faciles à représenter."
            },
            {
                'id': "0198_6",
                'type': "vrai-faux",
                'question': "Schématiser le fonctionnement d'un objet technique simple peut aider à trouver des idées pour l'améliorer. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, schématiser le fonctionnement d'un objet technique simple peut aider à trouver des idées pour l'améliorer en identifiant les points faibles du design actuel et en proposant des solutions pour les résoudre."
            },
            {
                'id': "0198_7",
                'type': "qcm",
                'question': "Quel est un outil couramment utilisé pour schématiser le fonctionnement d'un objet technique ?",
                'options': ["Un logiciel de dessin", "Un tableur", "Un traitement de texte", "Un logiciel de présentation"],
                'correct_option': "Un logiciel de dessin",
                'explanation': "Un logiciel de dessin, comme Microsoft Visio, Lucidchart, ou même des outils de dessin simples comme Microsoft Paint, est couramment utilisé pour schématiser le fonctionnement d'un objet technique."
            },
            {
                'id': "0198_8",
                'type': "vrai-faux",
                'question': "Il est inutile de schématiser le fonctionnement d'un objet technique si on comprend déjà comment il fonctionne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, même si on comprend déjà comment un objet technique fonctionne, schématiser son fonctionnement peut offrir une perspective différente et aider à identifier des améliorations potentielles ou à mieux communiquer son fonctionnement à d'autres personnes."
            },
        ]
    ),
    (
        0199,
        "Les étapes de la démarche de résolution de problèmes techniques",
        "Technologie",
        "6eme",
        [
            {
                'id': "0199_1",
                'type': "qcm",
                'question': "Quelles sont les étapes de la démarche de résolution de problèmes techniques ?",
                'options': ["Identifier le problème, trouver des solutions, choisir la meilleure solution, mettre en œuvre la solution", "Trouver des solutions, identifier le problème, choisir la meilleure solution, mettre en œuvre la solution", "Choisir la meilleure solution, identifier le problème, trouver des solutions, mettre en œuvre la solution", "Mettre en œuvre la solution, identifier le problème, trouver des solutions, choisir la meilleure solution"],
                'correct_option': "Identifier le problème, trouver des solutions, choisir la meilleure solution, mettre en œuvre la solution",
                'explanation': "Les étapes de la démarche de résolution de problèmes techniques sont : identifier le problème, trouver des solutions, choisir la meilleure solution, et mettre en œuvre la solution."
            },
            {
                'id': "0199_2",
                'type': "vrai-faux",
                'question': "Pourquoi l'étape d'analyse du besoin est-elle la première dans la démarche technique ?\n\nVrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, suivre les étapes de la démarche de résolution de problèmes techniques permet de trouver une solution efficace en s'assurant que le problème est correctement identifié, que plusieurs solutions sont envisagées, et que la meilleure solution est choisie et mise en œuvre."
            },
            {
                'id': "0199_3",
                'type': "qcm",
                'question': "Dans quelle étape utilise-t-on un cahier des charges pour définir un projet ?",
                'options': ["Identifier le problème", "Trouver des solutions", "Choisir la meilleure solution", "Mettre en œuvre la solution"],
                'correct_option': "Identifier le problème",
                'explanation': "Le cahier des charges est utilisé dans l'étape d'identification du problème pour définir les besoins et les contraintes du projet."
            },
            {
                'id': "0199_4",
                'type': "vrai-faux",
                'question': "Pour concevoir un abri pour un animal, il faut bien choisir les matériaux adaptés ?",
                'correct': True,
                'explanation': "Vrai, pour concevoir un abri pour un animal, il est important de choisir des matériaux adaptés qui offrent une bonne protection contre les intempéries, qui sont durables, et qui sont sûrs pour l'animal."
            },
            {
                'id': "0199_5",
                'type': "qcm",
                'question': "Besoins humains et objets techniques associés. Quel est l'objectif principal de l'étape de recherche de solutions dans la démarche technique ?",
                'options': ["Trouver une seule solution possible", "Trouver plusieurs solutions possibles", "Choisir la meilleure solution immédiatement", "Mettre en œuvre la solution sans réfléchir"],
                'correct_option': "Trouver plusieurs solutions possibles",
                'explanation': "L'objectif principal de l'étape de recherche de solutions est de trouver plusieurs solutions possibles pour pouvoir les comparer et choisir la meilleure."
            },
            {
                'id': "0199_6",
                'type': "vrai-faux",
                'question': "Il est acceptable de sauter des étapes dans la démarche de résolution de problèmes techniques si on pense que ce n'est pas nécessaire. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il est important de suivre toutes les étapes de la démarche de résolution de problèmes techniques pour s'assurer que le problème est correctement identifié, que plusieurs solutions sont envisagées, et que la meilleure solution est choisie et mise en œuvre."
            },
            {
                'id': "0199_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil utilisé pour choisir la meilleure solution dans la démarche technique ?",
                'options': ["Un diagramme de Gantt", "Un tableau comparatif", "Un logiciel de dessin", "Un tableur"],
                'correct_option': "Un tableau comparatif",
                'explanation': "Un tableau comparatif est un outil couramment utilisé pour comparer différentes solutions en fonction de critères spécifiques afin d'aider à choisir la meilleure option."
            },
            {
                'id': "0199_8",
                'type': "qcm",
                'question': "Quel est un exemple d'outil utilisé pour planifier un projet dans la démarche technique ?",
                'options': ["Un diagramme de Gantt", "Un tableau comparatif", "Un logiciel de dessin", "Un tableur"],
                'correct_option': "Un diagramme de Gantt",
                'explanation': "Un diagramme de Gantt est un outil couramment utilisé pour planifier et suivre l'avancement d'un projet en représentant les différentes tâches et leur chronologie."
            },
        ]
    ),
    (
        0200,
        "Matière vs matériau : différences et exemples.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0200_1",
                'type': "qcm",
                'question': "Quelle est la différence entre une matière et un matériau ?",
                'options': ["La matière est un matériau naturel, le matériau est fabriqué par l'homme", "La matière est un matériau fabriqué par l'homme, le matériau est naturel", "La matière est un concept abstrait, le matériau est concret", "Il n'y a pas de différence"],
                'correct_option': "La matière est un matériau naturel, le matériau est fabriqué par l'homme",
                'explanation': "La matière est un matériau naturel qui existe dans la nature, tandis que le matériau est un matériau fabriqué par l'homme à partir de matières premières pour répondre à des besoins spécifiques."
            },
            {
                'id': "0200_2",
                'type': "vrai-faux",
                'question': "Le bois est un exemple de matière. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le bois est un exemple de matière naturelle qui peut être utilisée pour fabriquer des matériaux comme le contreplaqué ou les panneaux de particules."
            },
            {
                'id': "0200_3",
                'type': "qcm",
                'question': "Quel est un exemple de matériau fabriqué par l'homme ?",
                'options': ["Le bois", "Le plastique", "Le métal", "Le verre"],
                'correct_option': "Le plastique",
                'explanation': "Le plastique est un exemple de matériau fabriqué par l'homme à partir de matières premières comme le pétrole."
            },
            {
                'id': "0200_4",
                'type': "vrai-faux",
                'question': "Le métal est un exemple de matière. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, le métal est un matériau fabriqué par l'homme à partir de matières premières comme le minerai de fer."
            },
            {
                'id': "0200_5",
                'type': "qcm",
                'question': "Quel est un exemple de matière naturelle ?",
                'options': ["Le plastique", "Le bois", "Le métal", "Le verre"],
                'correct_option': "Le bois",
                'explanation': "Le bois est un exemple de matière naturelle qui peut être utilisée pour fabriquer des matériaux comme le contreplaqué ou les panneaux de particules."
            },
            {
                'id': "0200_6",
                'type': "vrai-faux",
                'question': "Le verre est un exemple de matériau fabriqué par l'homme. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le verre est un matériau fabriqué par l'homme à partir de matières premières comme le sable et la soude."
            },
            {
                'id': "0200_7",
                'type': "qcm",
                'question': "Quel est l'intrus dans la liste suivante ?",
                'options': ["Le plastique", "Le bois", "Le métal", "Le verre"],
                'correct_option': "Le bois",
                'explanation': "Le bois est une matière naturelle, tandis que le plastique, le métal et le verre sont des matériaux fabriqués par l'homme à partir de matières premières."
            },
            {
                'id': "0200_8",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre une matière et un matériau. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il y a une différence entre une matière et un matériau. La matière est un matériau naturel qui existe dans la nature, tandis que le matériau est un matériau fabriqué par l'homme à partir de matières premières pour répondre à des besoins spécifiques."
            },
        ]
    ),
    (
        0201,
        "Tri et recyclage des matériaux.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0201_1",
                'type': "qcm",
                'question': "Quels matériaux peuvent être recyclés ?",
                'options': ["Le plastique", "Le papier", "Le métal", "Tous les matériaux mentionnés"],
                'correct_option': "Tous les matériaux mentionnés",
                'explanation': "De nombreux matériaux peuvent être recyclés, y compris le plastique, le papier et le métal."
            },
            {
                'id': "0201_2",
                'type': "vrai-faux",
                'question': "Le tri des déchets est important pour le recyclage. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le tri des déchets est essentiel pour permettre le recyclage des matériaux et réduire l'impact environnemental."
            },
            {
                'id': "0201_3",
                'type': "qcm",
                'question': "Quel est l'objectif principal du recyclage ?",
                'options': ["Réduire la pollution", "Économiser les ressources naturelles", "Créer de nouveaux emplois", "Tous les objectifs mentionnés"],
                'correct_option': "Tous les objectifs mentionnés",
                'explanation': "Le recyclage a plusieurs objectifs, notamment réduire la pollution, économiser les ressources naturelles, et créer de nouveaux emplois dans l'industrie du recyclage."
            },
            {
                'id': "0201_4",
                'type': "vrai-faux",
                'question': "Le verre peut être recyclé indéfiniment sans perdre ses propriétés. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le verre peut être recyclé indéfiniment sans perdre ses propriétés, ce qui en fait un matériau très durable pour le recyclage."
            },
            {
                'id': "0201_5",
                'type': "qcm",
                'question': "Quel est un exemple de matériau qui ne peut pas être recyclé ?",
                'options': ["Le plastique", "Le papier", "Le métal", "Le polystyrène"],
                'correct_option': "Le polystyrène",
                'explanation': "Le polystyrène est un matériau qui est difficile à recycler et qui n'est pas accepté dans de nombreux programmes de recyclage en raison de sa faible valeur et de son impact environnemental."
            },
            {
                'id': "0201_6",
                'type': "vrai-faux",
                'question': "Le tri des déchets permet de réduire la quantité de déchets envoyés à la décharge. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le tri des déchets permet de réduire la quantité de déchets envoyés à la décharge en permettant le recyclage et la réutilisation des matériaux."
            },
            {
                'id': "0201_7",
                'type': "qcm",
                'question': "Quel est un avantage du recyclage des matériaux ?",
                'options': ["Réduire la pollution", "Économiser les ressources naturelles", "Créer de nouveaux emplois", "Tous les avantages mentionnés"],
                'correct_option': "Tous les avantages mentionnés",
                'explanation': "Le recyclage des matériaux offre plusieurs avantages, notamment réduire la pollution, économiser les ressources naturelles, et créer de nouveaux emplois dans l'industrie du recyclage."
            },
            {
                'id': "0201_8",
                'type': "vrai-faux",
                'question': "Il est inutile de trier les déchets si on ne recycle pas. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, même si on ne recycle pas, trier les déchets peut aider à réduire la quantité de déchets envoyés à la décharge et à faciliter le traitement des déchets, ce qui peut avoir un impact positif sur l'environnement."
            },
        ]
    ),
    (
        0202,
        "Sources d'énergie (musculaire, chimique, électrique).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0202_1",
                'type': "qcm",
                'question': "Quelles sont les sources d'énergie ?",
                'options': ["Musculaire", "Chimique", "Électrique", "Toutes les sources mentionnées"],
                'correct_option': "Toutes les sources mentionnées",
                'explanation': "Il existe plusieurs sources d'énergie, notamment l'énergie musculaire, l'énergie chimique, et l'énergie électrique."
            },
            {
                'id': "0202_2",
                'type': "vrai-faux",
                'question': "L'énergie musculaire est produite par le cerveau du corps humain. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, l'énergie musculaire est produite par les muscles du corps humain lorsqu'ils se contractent pour effectuer un travail."
            },
            {
                'id': "0202_3",
                'type': "qcm",
                'question': "Quel est un exemple d'énergie chimique ?",
                'options': ["L'électricité", "Le carburant", "La lumière", "Le son"],
                'correct_option': "Le carburant",
                'explanation': "Le carburant est un exemple d'énergie chimique, car il contient de l'énergie stockée dans les liaisons chimiques qui peut être libérée lors de la combustion."
            },
            {
                'id': "0202_4",
                'type': "vrai-faux",
                'question': "L'énergie électrique est produite par le mouvement des électrons. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'énergie électrique est produite par le mouvement des électrons à travers un conducteur, ce qui permet de faire fonctionner des appareils électriques."
            },
            {
                'id': "0202_5",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise l'énergie électrique ?",
                'options': ["Une lampe", "Un moteur à essence", "Un vélo", "Une bougie"],
                'correct_option': "Une lampe",
                'explanation': "Une lampe est un exemple d'appareil qui utilise l'énergie électrique pour produire de la lumière."
            },
            {
                'id': "0202_6",
                'type': "vrai-faux",
                'question': "L'énergie musculaire peut être convertie en énergie électrique. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, l'énergie musculaire ne peut pas être directement convertie en énergie électrique. Cependant, elle peut être utilisée pour produire de l'électricité à travers des dispositifs mécaniques comme des générateurs."
            },
            {
                'id': "0202_7",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise l'énergie chimique ?",
                'options': ["Une batterie", "Un moteur à essence", "Un panneau solaire", "Un éolienne"],
                'correct_option': "Un moteur à essence",
                'explanation': "Un moteur à essence est un exemple d'appareil qui utilise l'énergie chimique du carburant pour produire de l'énergie mécanique."
            },
            {
                'id': "0202_8",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes sources d'énergie. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe des différences entre les différentes sources d'énergie en termes de leur origine, de leur mode de production, et de leur utilisation."
            },
        ]
    ),
    (
        0203,
        "Types de mouvements (rectiligne, circulaire).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0203_1",
                'type': "qcm",
                'question': "Quels sont les types de mouvements ?",
                'options': ["Rectiligne", "Circulaire", "Tous les types mentionnés", "Aucun des types mentionnés"],
                'correct_option': "Tous les types mentionnés",
                'explanation': "Il existe plusieurs types de mouvements, notamment le mouvement rectiligne, qui se fait en ligne droite, et le mouvement circulaire, qui se fait autour d'un point fixe."
            },
            {
                'id': "0203_2",
                'type': "vrai-faux",
                'question': "Le mouvement rectiligne est un mouvement horizontal. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le mouvement rectiligne se fait en ligne droite, et peut être horizontal ou vertical selon le contexte."
            },
            {
                'id': "0203_3",
                'type': "qcm",
                'question': "Quel est un exemple de mouvement circulaire ?",
                'options': ["Le mouvement d'une voiture sur une route droite", "Le mouvement d'une roue de vélo", "Le mouvement d'un ascenseur", "Le mouvement d'un train sur des rails"],
                'correct_option': "Le mouvement d'une roue de vélo",
                'explanation': "Le mouvement d'une roue de vélo est un exemple de mouvement circulaire, car elle tourne autour d'un point fixe (l'axe de la roue)."
            },
            {
                'id': "0203_4",
                'type': "vrai-faux",
                'question': "Le mouvement circulaire est un mouvement qui se fait en ligne droite. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, le mouvement circulaire se fait autour d'un point fixe et n'est pas en ligne droite."
            },
            {
                'id': "0203_5",
                'type': "qcm",
                'question': "Quel est un exemple de mouvement rectiligne ?",
                'options': ["Le mouvement d'une roue de vélo", "Le mouvement d'une voiture sur une route droite", "Le mouvement d'un ascenseur qui monte et descend", "Le mouvement d'un train sur des rails"],
                'correct_option': "Le mouvement d'une voiture sur une route droite",
                'explanation': "Le mouvement d'une voiture sur une route droite est un exemple de mouvement rectiligne, car elle se déplace en ligne droite."
            },
            {
                'id': "0203_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les mouvements rectilignes et circulaires. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les mouvements rectilignes et circulaires en termes de leur trajectoire et de leur nature."
            },
            {
                'id': "0203_7",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise un mouvement circulaire ?",
                'options': ["Une voiture", "Une roue de vélo", "Un ascenseur", "Un train"],
                'correct_option': "Une roue de vélo",
                'explanation': "Une roue de vélo utilise un mouvement circulaire, car elle tourne autour d'un point fixe (l'axe de la roue)."
            },
            {
                'id': "0203_8",
                'type': "vrai-faux",
                'question': "Le mouvement rectiligne est plus rapide que le mouvement circulaire. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la vitesse d'un mouvement rectiligne ou circulaire dépend de nombreux facteurs, tels que la force appliquée, la résistance, et les conditions environnementales, et il n'est pas correct de dire que l'un est intrinsèquement plus rapide que l'autre."
            },
        ]
    ),
    (
        0204,
        "Transmission de mouvement (engrenages, courroies).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0204_1",
                'type': "qcm",
                'question': "Quels sont les moyens de transmission de mouvement ?",
                'options': ["Engrenages", "Courroies", "Tous les moyens mentionnés", "Aucun des moyens mentionnés"],
                'correct_option': "Tous les moyens mentionnés",
                'explanation': "Il existe plusieurs moyens de transmission de mouvement, notamment les engrenages, qui sont des roues dentées qui s'engrènent les unes dans les autres, et les courroies, qui sont des bandes flexibles qui transmettent le mouvement d'une poulie à unen autre."
            },
            {
                'id': "0204_2",
                'type': "vrai-faux",
                'question': "Les engrenages permettent de changer la direction du mouvement. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les engrenages peuvent être utilisés pour changer la direction du mouvement, ainsi que pour augmenter ou réduire la vitesse et le couple."
            },
            {
                'id': "0204_3",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise des engrenages pour transmettre le mouvement ?",
                'options': ["Une voiture", "Une montre", "Un ascenseur", "Un train"],
                'correct_option': "Une montre",
                'explanation': "Une montre utilise des engrenages pour transmettre le mouvement des aiguilles et assurer un fonctionnement précis."
            },
            {
                'id': "0204_4",
                'type': "vrai-faux",
                'question': "Les courroies sont utilisées pour transmettre le mouvement entre des pièces qui ne sont pas en contact direct. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les courroies sont utilisées pour transmettre le mouvement entre des pièces qui ne sont pas en contact direct, comme dans les systèmes de transmission de puissance dans les machines industrielles ou les véhicules."
            },
            {
                'id': "0204_5",
                'type': "qcm",
                'question': "Quel est un avantage des courroies par rapport aux engrenages ?",
                'options': ["Elles sont plus silencieuses", "Elles sont plus durables", "Elles nécessitent moins d'entretien", "Toutes les réponses mentionnées"],
                'correct_option': "Toutes les réponses mentionnées",
                'explanation': "Les courroies ont plusieurs avantages par rapport aux engrenages, notamment qu'elles sont généralement plus silencieuses, peuvent être plus durables dans certaines applications, et nécessitent souvent moins d'entretien."
            },
            {
                'id': "0204_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les engrenages et les courroies. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe des différences entre les engrenages et les courroies en termes de leur fonctionnement, de leur application, et de leurs avantages et inconvénients respectifs."
            },
            {
                'id': "0204_7",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise des courroies pour transmettre le mouvement ?",
                'options': ["Une voiture", "Une montre", "Un ascenseur", "Un train"],
                'correct_option': "Une voiture",
                'explanation': "Une voiture utilise des courroies pour transmettre le mouvement du moteur à d'autres composants, comme l'alternateur ou la pompe à eau."
            },
            {
                'id': "0204_8",
                'type': "vrai-faux",
                'question': "Les engrenages sont plus efficaces que les courroies pour transmettre le mouvement. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, l'efficacité de la transmission de mouvement dépend de nombreux facteurs, et il n'est pas correct de dire que les engrenages sont intrinsèquement plus efficaces que les courroies dans toutes les situations."
            },
        ]
    ),
    (
        0205,
        "Stockage d'énergie (batterie, accumulateur).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0205_1",
                'type': "qcm",
                'question': "Quels sont les moyens de stockage d'énergie ?",
                'options': ["Batterie", "Accumulateur", "Tous les moyens mentionnés", "Aucun des moyens mentionnés"],
                'correct_option': "Tous les moyens mentionnés",
                'explanation': "Il existe plusieurs moyens de stockage d'énergie, notamment les batteries, qui sont des dispositifs électrochimiques qui stockent l'énergie sous forme chimique, et les accumulateurs, qui sont des dispositifs qui stockent l'énergie sous forme électrique."
            },
            {
                'id': "0205_2",
                'type': "vrai-faux",
                'question': "Les batteries sont utilisées pour stocker l'énergie dans les appareils électroniques. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les batteries sont couramment utilisées pour stocker l'énergie dans les appareils électroniques tels que les téléphones portables, les ordinateurs portables, et les véhicules électriques."
            },
            {
                'id': "0205_3",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise un accumulateur pour stocker l'énergie ?",
                'options': ["Une voiture électrique", "Un téléphone portable", "Un ordinateur portable", "Une lampe de poche"],
                'correct_option': "Une voiture électrique",
                'explanation': "Une voiture électrique utilise un accumulateur pour stocker l'énergie électrique qui alimente le moteur de la voiture."
            },
            {
                'id': "0205_4",
                'type': "vrai-faux",
                'question': "Les accumulateurs sont plus durables que les batteries. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la durabilité des accumulateurs par rapport aux batteries dépend de nombreux facteurs, et il n'est pas correct de dire que les accumulateurs sont intrinsèquement plus durables que les batteries dans toutes les situations."
            },
            {
                'id': "0205_5",
                'type': "qcm",
                'question': "Quel est un avantage des batteries par rapport aux accumulateurs ?",
                'options': ["Elles sont plus légères", "Elles ont une plus grande capacité de stockage", "Elles sont plus faciles à recharger", "Toutes les réponses mentionnées"],
                'correct_option': "Toutes les réponses mentionnées",
                'explanation': "Les batteries ont plusieurs avantages par rapport aux accumulateurs, notamment qu'elles sont généralement plus légères, peuvent avoir une plus grande capacité de stockage, et sont souvent plus faciles à recharger."
            },
            {
                'id': "0205_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les batteries et les accumulateurs. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe des différences entre les batteries et les accumulateurs en termes de leur fonctionnement, de leur application, et de leurs avantages et inconvénients respectifs."
            },
            {
                'id': "0205_7",
                'type': "qcm",
                'question': "Trouve l'intrus dans la liste suivante :",
                'options': ["Une voiture électrique", "Un téléphone portable", "Un ordinateur portable", "Une lampe de chevet"],
                'correct_option': "Une lampe de chevet",
                'explanation': "Une lampe de chevet utilise une ampoule pour produire de la lumière et ne dépend pas d'une batterie pour fonctionner."
            },
            {
                'id': "0205_8",
                'type': "vrai-faux",
                'question': "Les accumulateurs et les batteries servent à stocker l'énergie. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les accumulateurs et les batteries ont pour fonction principale de stocker l'énergie pour une utilisation ultérieure."
            },
        ]
    ),
    (
        0206,
        "Mesure de vitesse et accélération simple."
        "Technologie",
        "6eme",
        [
            {
                'id': "0206_1",
                'type': "qcm",
                'question': "Comment mesure-t-on la vitesse d'un objet ?",
                'options': ["En utilisant un chronomètre et une règle pour mesurer le temps et la distance parcourue", "En utilisant un thermomètre pour mesurer la température de l'objet", "En utilisant un baromètre pour mesurer la pression de l'air autour de l'objet", "En utilisant un hygromètre pour mesurer l'humidité de l'air autour de l'objet"],
                'correct_option': "En utilisant un chronomètre et une règle pour mesurer le temps et la distance parcourue",
                'explanation': "La vitesse d'un objet peut être mesurée en utilisant un chronomètre pour mesurer le temps qu'il met à parcourir une certaine distance, et une règle pour mesurer cette distance. La formule de la vitesse est : vitesse = distance / temps."
            },
            {
                'id': "0206_2",
                'type': "vrai-faux",
                'question': "L'accélération est la variation de la vitesse d'un objet par unité de temps. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'accélération est définie comme la variation de la vitesse d'un objet par unité de temps. Elle peut être positive (accélération) ou négative (décélération)."
            },
            {
                'id': "0206_3",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui peut accélérer ?",
                'options': ["Une voiture qui démarre", "Un livre posé sur une table", "Une lampe de bureau", "Un arbre"],
                'correct_option': "Une voiture qui démarre",
                'explanation': "Une voiture qui démarre peut accélérer en augmentant sa vitesse à partir d'un état de repos."
            },
            {
                'id': "0206_4",
                'type': "vrai-faux",
                'question': "La vitesse et l'accélération sont des concepts liés mais différents. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la vitesse est une mesure de la rapidité d'un objet, tandis que l'accélération mesure comment cette vitesse change au fil du temps."
            },
            {
                'id': "0206_5",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui peut décélérer ?",
                'options': ["Une voiture qui freine", "Un livre posé sur une table", "Une lampe de bureau", "Un arbre"],
                'correct_option': "Une voiture qui freine",
                'explanation': "Une voiture qui freine peut décélérer en réduisant sa vitesse à partir d'une vitesse initiale."
            },
            {
                'id': "0206_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre la vitesse et l'accélération. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre la vitesse et l'accélération en termes de leur définition et de leur rôle dans la description du mouvement d'un objet."
            },
            {
                'id': "0206_7",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui peut maintenir une vitesse constante ?",
                'options': ["Une voiture qui roule à une vitesse constante sur une autoroute", "Un livre posé sur une table", "Une lampe de bureau", "Un arbre"],
                'correct_option': "Une voiture qui roule à une vitesse constante sur une autoroute",
                'explanation': "Une voiture qui roule à une vitesse constante sur une autoroute peut maintenir cette vitesse sans accélérer ni décélérer."
            },
            {
                'id': "0206_8",
                'type': "vrai-faux",
                'question': "La vitesse peut être mesurée en mètres par seconde (m/s) ou en kilomètres par heure (km/h). Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la vitesse peut être mesurée en différentes unités, notamment en mètres par seconde (m/s) ou en kilomètres par heure (km/h), selon le contexte et les besoins de mesure."
            },
        ]
    ),
    (
        0207,
        "Périphériques d'un ordinateur (entrée/sortie).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0207_1",
                'type': "qcm",
                'question': "Quels sont les périphériques d'entrée d'un ordinateur ?",
                'options': ["Clavier", "Souris", "Écran", "Tous les périphériques mentionnés"],
                'correct_option': "Tous les périphériques mentionnés",
                'explanation': "Les périphériques d'entrée d'un ordinateur comprennent le clavier, la souris, et l'écran tactile, qui permettent à l'utilisateur de fournir des données et des commandes à l'ordinateur."
            },
            {
                'id': "0207_2",
                'type': "vrai-faux",
                'question': "Un écran est un périphérique de sortie. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un écran est un périphérique de sortie qui affiche les informations traitées par l'ordinateur à l'utilisateur."
            },
            {
                'id': "0207_3",
                'type': "qcm",
                'question': "Quel est un exemple qui n'est pas un périphérique d'entrée ?",
                'options': ["Clavier", "Souris", "Imprimante", "Haut-parleur"],
                'correct_option': "Imprimante",
                'explanation': "Une imprimante est un périphérique de sortie qui permet d'obtenir une copie papier des informations traitées par l'ordinateur."
            },
            {
                'id': "0207_4",
                'type': "vrai-faux",
                'question': "Un microphone est un périphérique d'entrée. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un microphone est un périphérique d'entrée qui permet de capturer des sons et de les transmettre à l'ordinateur pour traitement."
            },
            {
                'id': "0207_5",
                'type': "qcm",
                'question': "Quel est un exemple de périphérique de sortie ?",
                'options': ["Clavier", "Souris", "Écran", "Tous les périphériques mentionnés"],
                'correct_option': "Écran",
                'explanation': "Un écran est un périphérique de sortie qui affiche les informations traitées par l'ordinateur à l'utilisateur."
            },
            {
                'id': "0207_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les périphériques d'entrée et de sortie. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les périphériques d'entrée et de sortie en termes de leur fonction dans le système informatique. Les périphériques d'entrée permettent à l'utilisateur de fournir des données et des commandes à l'ordinateur, tandis que les périphériques de sortie permettent à l'ordinateur de communiquer des informations à l'utilisateur."
            },
            {
                'id': "0207_7",
                'type': "qcm",
                'question': "Quel est un exemple de périphérique qui peut être à la fois d'entrée et de sortie ?",
                'options': ["Clavier", "Souris", "Écran tactile", "Haut-parleur"],
                'correct_option': "Écran tactile",
                'explanation': "Un écran tactile peut être à la fois un périphérique d'entrée, car il permet à l'utilisateur d'interagir avec l'ordinateur en touchant l'écran, et un périphérique de sortie, car il affiche les informations traitées par l'ordinateur."
            },
            {
                'id': "0207_8",
                'type': "vrai-faux",
                'question': "Tous les périphériques d'un ordinateur sont soit d'entrée, soit de sortie. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, certains périphériques peuvent être à la fois d'entrée et de sortie, comme les écrans tactiles ou les haut-parleurs avec microphone intégré, qui permettent à la fois de fournir des données à l'ordinateur et de recevoir des informations de l'ordinateur."
            },
        ]
    ),
    (
        0208,
        "Logiciels de base (système d'exploitation, navigateur).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0208_1",
                'type': "qcm",
                'question': "Quels sont des exemples de logiciels de base ?",
                'options': ["Système d'exploitation", "Navigateur web", "Traitement de texte", "Tous les logiciels mentionnés"],
                'correct_option': "Tous les logiciels mentionnés",
                'explanation': "Les logiciels de base comprennent le système d'exploitation, qui gère les ressources matérielles et logicielles de l'ordinateur, le navigateur web, qui permet d'accéder à Internet, et le traitement de texte, qui permet de créer et d'éditer des documents."
            },
            {
                'id': "0208_2",
                'type': "vrai-faux",
                'question': "Un système d'exploitation est un logiciel qui gère les ressources matérielles et logicielles de l'ordinateur. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un système d'exploitation est un logiciel essentiel qui gère les ressources matérielles et logicielles de l'ordinateur, permettant aux autres logiciels de fonctionner correctement."
            },
            {
                'id': "0208_3",
                'type': "qcm",
                'question': "Quel est un exemple de système d'exploitation ?",
                'options': ["Windows", "Google Chrome", "Microsoft Word", "Adobe Photoshop"],
                'correct_option': "Windows",
                'explanation': "Windows est un exemple de système d'exploitation développé par Microsoft."
            },
            {
                'id': "0208_4",
                'type': "vrai-faux",
                'question': "Un navigateur web est un logiciel qui permet d'accéder à Internet. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un navigateur web est un logiciel qui permet aux utilisateurs d'accéder à Internet et de visualiser des pages web."
            },
            {
                'id': "0208_5",
                'type': "qcm",
                'question': "Quel est un exemple de navigateur web ?",
                'options': ["Windows", "Google Chrome", "Microsoft Word", "Adobe Photoshop"],
                'correct_option': "Google Chrome",
                'explanation': "Google Chrome est un exemple de navigateur web développé par Google."
            },
            {
                'id': "0208_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents types de logiciels. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents types de logiciels en termes de leur fonction, de leur utilisation, et de leur importance dans le système informatique."
            },
            {
                'id': "0208_7",
                'type': "qcm",
                'question': "Quel est un exemple de logiciel qui n'est pas un logiciel de base ?",
                'options': ["Windows", "Google Chrome", "Microsoft Word", "Adobe Photoshop"],
                'correct_option': "Adobe Photoshop",
                'explanation': "Adobe Photoshop est un logiciel de retouche photo et de création graphique, et n'est pas considéré comme un logiciel de base comme le système d'exploitation ou le navigateur web."
            },
            {
                'id': "0208_8",
                'type': "vrai-faux",
                'question': "Tous les logiciels sont des logiciels de base. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les logiciels ne sont pas des logiciels de base. Il existe une grande variété de logiciels avec des fonctions spécifiques, et seuls certains d'entre eux sont considérés comme des logiciels de base."
            },
        ]
    ),
    (
        0209,
        "Notions de base en programmation (algorithme, langage de programmation).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0209_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en programmation ?",
                'options': ["Algorithme", "Langage de programmation", "Base de données", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en programmation comprennent l'algorithme, qui est une série d'instructions pour résoudre un problème, le langage de programmation, qui est un langage utilisé pour écrire des programmes informatiques, et la base de données, qui est un système organisé pour stocker et gérer des données."
            },
            {
                'id': "0209_2",
                'type': "vrai-faux",
                'question': "Un algorithme est une série d'instructions pour résoudre un problème. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un algorithme est une séquence d'instructions ou d'étapes qui sont suivies pour résoudre un problème ou accomplir une tâche spécifique."
            },
            {
                'id': "0209_3",
                'type': "qcm",
                'question': "Quel est un exemple de langage de programmation ?",
                'options': ["Python", "GIT", "CRUD", "Tous les langages mentionnés"],
                'correct_option': "Python",
                'explanation': "Python est un exemple de langage de programmation populaire utilisé pour le développement de logiciels, l'analyse de données, et l'intelligence artificielle."
            },
            {
                'id': "0209_4",
                'type': "vrai-faux",
                'question': "Le langage de HTML est utilisé pour créer des pages web. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, HTML (HyperText Markup Language) est un langage de balisage utilisé pour structurer et présenter le contenu sur le web."
            },
            {
                'id': "0209_5",
                'type': "qcm",
                'question': "Quel est un exemple de base de données ?",
                'options': ["MySQL", "Python", "HTML", "CSS"],
                'correct_option': "MySQL",
                'explanation': "MySQL est un exemple de système de gestion de base de données relationnelle utilisé pour stocker et gérer des données dans des applications web et d'autres types de logiciels."
            },
            {
                'id': "0209_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents concepts en programmation. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents concepts en programmation en termes de leur fonction, de leur utilisation, et de leur importance dans le processus de développement logiciel."
            },
            {
                'id': "0209_7",
                'type': "qcm",
                'question': "Quel est un exemple de tâche qui peut être accomplie avec un algorithme ?",
                'options': ["Trier une liste de nombres", "Créer une page web", "Gérer une base de données", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Un algorithme peut être utilisé pour accomplir une variété de tâches, telles que trier une liste de nombres, créer une page web, ou gérer une base de données, en fonction des instructions spécifiques qu'il contient."
            },
            {
                'id': "0209_8",
                'type': "vrai-faux",
                'question': "Tous les langages de programmation sont utilisés pour les mêmes types de tâches. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, différents langages de programmation sont souvent utilisés pour des types de tâches spécifiques en fonction de leurs caractéristiques et de leur domaine d'application. Par exemple, Python est souvent utilisé pour l'analyse de données et l'intelligence artificielle, tandis que JavaScript est couramment utilisé pour le développement web."
            },
        ]
    ),
    (
        0210,
        "Notions de base en électronique (circuit, composant).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0210_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en électronique ?",
                'options': ["Circuit", "Composant", "Résistance", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en électronique comprennent le circuit, qui est un chemin fermé à travers lequel le courant électrique peut circuler, le composant, qui est une partie individuelle d'un circuit électronique, et la résistance, qui est une mesure de la difficulté pour le courant de circuler à travers un composant ou un circuit."
            },
            {
                'id': "0210_2",
                'type': "vrai-faux",
                'question': "Un circuit est un chemin fermé à travers lequel le courant électrique peut circuler. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un circuit est défini comme un chemin fermé qui permet au courant électrique de circuler, et il peut être composé de divers composants électroniques tels que des résistances, des condensateurs, et des transistors."
            },
            {
                'id': "0210_3",
                'type': "qcm",
                'question': "Quel est un exemple de composant électronique ?",
                'options': ["Résistance", "Clavier", "Souris", "Écran"],
                'correct_option': "Résistance",
                'explanation': "Une résistance est un exemple de composant électronique qui limite le flux de courant dans un circuit."
            },
            {
                'id': "0210_4",
                'type': "vrai-faux",
                'question': "Un transistor est un composant électronique qui peut amplifier ou commuter des signaux électriques. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un transistor est un composant électronique essentiel qui peut être utilisé pour amplifier ou commuter des signaux électriques dans les circuits électroniques."
            },
            {
                'id': "0210_5",
                'type': "qcm",
                'question': "Quel est un exemple de circuit électronique ?",
                'options': ["Un circuit imprimé", "Un clavier d'ordinateur", "Une souris d'ordinateur", "Un écran d'ordinateur"],
                'correct_option': "Un circuit imprimé",
                'explanation': "Un circuit imprimé (PCB) est un exemple de circuit électronique qui supporte et connecte les composants électroniques à l'aide de pistes conductrices."
            },
            {
                'id': "0210_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents concepts en électronique. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents concepts en électronique en termes de leur fonction, de leur utilisation, et de leur importance dans la conception et le fonctionnement des circuits électroniques."
            },
            {
                'id': "0210_7",
                'type': "qcm",
                'question': "Quel est un exemple de composant qui n'est pas électronique ?",
                'options': ["Résistance", "Clavier", "Souris", "Écran"],
                'correct_option': "Clavier",
                'explanation': "Un clavier est un périphérique d'entrée utilisé pour interagir avec un ordinateur, et n'est pas considéré comme un composant électronique dans le contexte des circuits électroniques."
            },
            {
                'id': "0210_8",
                'type': "vrai-faux",
                'question': "Tous les composants électroniques sont utilisés dans les circuits électroniques. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, tous les composants électroniques sont conçus pour être utilisés dans des circuits électroniques afin de remplir des fonctions spécifiques telles que la résistance, l'amplification, ou la commutation de signaux électriques."
            },
        ]
    ),
    (
        0211,
        "Arborescence des fichiers informatiques.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0211_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en arborescence des fichiers informatiques ?",
                'options': ["Dossier", "Fichier", "Chemin d'accès", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en arborescence des fichiers informatiques comprennent le dossier, qui est un conteneur pour organiser les fichiers, le fichier lui-même, qui est une unité de stockage de données, et le chemin d'accès, qui est l'adresse permettant de localiser un fichier ou un dossier dans le système de fichiers."
            },
            {
                'id': "0211_2",
                'type': "vrai-faux",
                'question': "Un fichier est un conteneur pour organiser les dossiers. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, un fichier est une unité de stockage de données, tandis qu'un dossier est un conteneur utilisé pour organiser les fichiers. Les fichiers sont stockés à l'intérieur des dossiers, et non l'inverse."
            },
            {
                'id': "0211_3",
                'type': "qcm",
                'question': "Quel est un exemple de chemin d'accès à un fichier ?",
                'options': ["C:\\Users\\NomUtilisateur\\Documents\\Fichier.txt", "/home/nomutilisateur/documents/fichier.txt", "Tous les exemples mentionnés", "Aucun des exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Les chemins d'accès à un fichier peuvent varier en fonction du système d'exploitation utilisé. Par exemple, sous Windows, un chemin d'accès peut ressembler à 'C:\\Users\\NomUtilisateur\\Documents\\Fichier.txt', tandis que sous Linux ou macOS, un chemin d'accès peut ressembler à '/home/nomutilisateur/documents/fichier.txt'."
            },
            {
                'id': "0211_4",
                'type': "vrai-faux",
                'question': "L'arborescence des fichiers informatiques est une structure hiérarchique qui organise les fichiers et les dossiers. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'arborescence des fichiers informatiques est une structure hiérarchique qui organise les fichiers et les dossiers de manière à faciliter la gestion et la navigation dans le système de fichiers. Les dossiers peuvent contenir des fichiers et d'autres dossiers, créant ainsi une structure en forme d'arbre."
            },
            {
                'id': "0211_5",
                'type': "qcm",
                'question': "Quel est un exemple de système d'exploitation qui utilise une arborescence de fichiers ?",
                'options': ["Windows", "Linux", "macOS", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Les systèmes d'exploitation modernes comme Windows, Linux et macOS utilisent tous une arborescence de fichiers pour organiser les fichiers et les dossiers."
            },
            {
                'id': "0211_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les fichiers et les dossiers dans l'arborescence des fichiers informatiques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les fichiers et les dossiers dans l'arborescence des fichiers informatiques. Les fichiers sont des unités de stockage de données, tandis que les dossiers sont des conteneurs utilisés pour organiser les fichiers."
            },
            {
                'id': "0211_7",
                'type': "qcm",
                'question': "Quel est un exemple de fichier qui pourrait être stocké dans un dossier ?",
                'options': ["Document Word", "Image JPEG", "Fichier PDF", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés sont des types de fichiers courants qui peuvent être stockés dans un dossier pour une organisation efficace."
            },
            {
                'id': "0211_8",
                'type': "vrai-faux",
                'question': "Tous les systèmes d'exploitation utilisent la même structure d'arborescence de fichiers. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, bien que la plupart des systèmes d'exploitation modernes utilisent une structure d'arborescence de fichiers, la manière dont cette structure est organisée peut varier d'un système à l'autre."
            },
        ]
    ),
    (
        0212,
        "Notions de base en réseaux informatiques (adresse IP, protocole).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0212_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en réseaux informatiques ?",
                'options': ["Adresse IP", "Protocole", "Routeur", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en réseaux informatiques comprennent l'adresse IP, qui est une adresse unique attribuée à chaque appareil connecté à un réseau, le protocole, qui est un ensemble de règles pour la communication entre les appareils sur un réseau, et le routeur, qui est un dispositif qui dirige le trafic de données entre les réseaux."
            },
            {
                'id': "0212_2",
                'type': "vrai-faux",
                'question': "Une adresse IP est une adresse unique attribuée à chaque appareil connecté à un réseau. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, une adresse IP (Internet Protocol) est une adresse numérique unique attribuée à chaque appareil connecté à un réseau informatique, permettant l'identification et la communication entre les appareils sur le réseau."
            },
            {
                'id': "0212_3",
                'type': "qcm",
                'question': "Quel est un exemple de protocole de communication utilisé dans les réseaux informatiques ?",
                'options': ["HTTP", "FTP", "TCP/IP", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "HTTP (HyperText Transfer Protocol), FTP (File Transfer Protocol), et TCP/IP (Transmission Control Protocol/Internet Protocol) sont tous des exemples de protocoles de communication utilisés dans les réseaux informatiques pour permettre la transmission de données entre les appareils."
            },
            {
                'id': "0212_4",
                'type': "vrai-faux",
                'question': "Un routeur est un dispositif qui dirige le trafic de données entre les réseaux. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un routeur est un dispositif essentiel dans les réseaux informatiques qui dirige le trafic de données entre les différents réseaux, permettant aux appareils de communiquer entre eux même s'ils sont sur des réseaux différents."
            },
            {
                'id': "0212_5",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui peut être connecté à un réseau informatique ?",
                'options': ["Ordinateur", "Smartphone", "Imprimante", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés sont des types d'appareils courants qui peuvent être connectés à un réseau informatique pour partager des ressources et communiquer avec d'autres appareils."
            },
            {
                'id': "0212_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents concepts en réseaux informatiques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents concepts en réseaux informatiques en termes de leur fonction, de leur utilisation, et de leur importance dans la conception et le fonctionnement des réseaux informatiques."
            },
            {
                'id': "0212_7",
                'type': "qcm",
                'question': "Quel est un exemple de protocole qui n'est pas utilisé dans les réseaux informatiques ?",
                'options': ["HTTP", "FTP", "TCP/IP", "SMTP"],
                'correct_option': "SMTP",
                'explanation': "SMTP (Simple Mail Transfer Protocol) est un protocole utilisé pour la transmission d'e-mails, et n'est pas principalement utilisé pour la communication générale dans les réseaux informatiques comme HTTP, FTP, ou TCP/IP."
            },
            {
                'id': "0212_8",
                'type': "vrai-faux",
                'question': "Tous les appareils connectés à un réseau informatique ont une adresse IP. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, tous les appareils connectés à un réseau informatique se voient attribuer une adresse IP unique pour permettre l'identification et la communication sur le réseau."
            },
        ]
    ),
    (
        0213,
        "Signaux et information (analogique/numérique).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0213_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en signaux et information ?",
                'options': ["Signal analogique", "Signal numérique", "Information binaire", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en signaux et information comprennent le signal analogique, qui est un signal continu qui peut prendre une infinité de valeurs, le signal numérique, qui est un signal discret qui ne peut prendre que des valeurs spécifiques, et l'information binaire, qui est une forme d'information codée en utilisant deux états (0 et 1) pour représenter les données."
            },
            {
                'id': "0213_2",
                'type': "vrai-faux",
                'question': "Un signal analogique est un signal continu qui peut prendre une infinité de valeurs. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un signal analogique est caractérisé par sa continuité et sa capacité à prendre une infinité de valeurs dans une plage donnée, contrairement à un signal numérique qui est discret et ne peut prendre que des valeurs spécifiques."
            },
            {
                'id': "0213_3",
                'type': "qcm",
                'question': "Quel est un exemple de signal numérique ?",
                'options': ["Signal de télévision analogique", "Signal de téléphone analogique", "Signal de données numériques", "Tous les exemples mentionnés"],
                'correct_option': "Signal de données numériques",
                'explanation': "Un signal de données numériques est un exemple de signal numérique, qui est utilisé pour transmettre des informations sous forme de données codées en binaire (0 et 1) dans les systèmes informatiques et de communication."
            },
            {
                'id': "0213_4",
                'type': "vrai-faux",
                'question': "L'information binaire est une forme d'information codée en utilisant deux états (0 et 1) pour représenter les données. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'information binaire est une méthode de codage qui utilise deux états (0 et 1) pour représenter les données, et est largement utilisée dans les systèmes informatiques et de communication pour stocker et transmettre des informations de manière efficace et fiable."
            },
            {
                'id': "0213_5",
                'type': "qcm",
                'question': "Quel est un exemple de signal qui n'est pas numérique ?",
                'options': ["Signal de télévision analogique", "Signal de téléphone analogique", "Signal de données numériques", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Les signaux de télévision analogique et de téléphone analogique sont des exemples de signaux analogiques, tandis que le signal de données numériques est un exemple de signal numérique."
            },
            {
                'id': "0213_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les signaux analogiques et numériques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les signaux analogiques et numériques sont différents. Les signaux analogiques sont continus et peuvent prendre une infinité de valeurs, tandis que les signaux numériques sont discrets et ne peuvent prendre que des valeurs spécifiques."
            },
            {
                'id': "0213_7",
                'type': "qcm",
                'question': "Quel est un exemple d'information qui peut être représentée en binaire ?",
                'options': ["Texte", "Image", "Son", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (texte, image, son) peuvent être représentés en binaire, car les données numériques sont codées en utilisant des séquences de 0 et 1 pour représenter différentes formes d'information dans les systèmes informatiques."
            },
            {
                'id': "0213_8",
                'type': "vrai-faux",
                'question': "Tous les systèmes de communication utilisent des signaux numériques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, bien que de nombreux systèmes de communication modernes utilisent des signaux numériques pour leur efficacité et leur fiabilité, il existe encore des systèmes qui utilisent des signaux analogiques, notamment dans les domaines de la radio, de la télévision, et de certaines formes de téléphonie."
            },
        ]
    ),
    (
        0214,
        "Transmission d'information (câbles, sans fil).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0214_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en transmission d'information ?",
                'options': ["Câbles", "Sans fil", "Réseaux de communication", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en transmission d'information comprennent les câbles, qui sont des supports physiques pour la transmission de données, les technologies sans fil, qui permettent la transmission de données sans l'utilisation de câbles, et les réseaux de communication, qui sont des systèmes interconnectés pour la transmission de données entre les appareils."
            },
            {
                'id': "0214_2",
                'type': "vrai-faux",
                'question': "La transmission d'information peut se faire à la fois par câbles et sans fil. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la transmission d'information peut se faire à la fois par câbles et sans fil, en fonction des besoins et des contraintes du système de communication."
            },
            {
                'id': "0214_3",
                'type': "qcm",
                'question': "Quels sont des exemples de virus informatique ?",
                'options': ["Cheval de Troie", "Ransomware", "Spyware", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Le cheval de Troie, le ransomware, et le spyware sont tous des exemples de types courants de virus informatiques qui peuvent causer des dommages aux ordinateurs et aux réseaux."
            },
            {
                'id': "0214_4",
                'type': "vrai-faux",
                'question': "Un pare-feu est un dispositif de sécurité qui surveille et contrôle le trafic réseau entrant et sortant pour protéger contre les menaces. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un pare-feu est un dispositif de sécurité qui surveille et contrôle le trafic réseau entrant et sortant pour protéger contre les menaces."
            },
            {
                'id': "0214_5",
                'type': "qcm",
                'question': "Quel est un exemple de technologie de transmission sans fil ?",
                'options': ["Wi-Fi", "Ethernet", "Fibre optique", "Tous les exemples mentionnés"],
                'correct_option': "Wi-Fi",
                'explanation': "Le Wi-Fi est un exemple de technologie de transmission sans fil qui permet aux appareils de se connecter à Internet et à d'autres réseaux sans l'utilisation de câbles."
            },
            {
                'id': "0214_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes technologies de transmission d'information. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes technologies de transmission d'information en termes de leur méthode de transmission, de leur portée, de leur vitesse, et de leur utilisation dans différents contextes."
            },
            {
                'id': "0214_7",
                'type': "qcm",
                'question': "Quel est un exemple de technologie de transmission par câble ?",
                'options': ["Ethernet", "Wi-Fi", "Bluetooth", "Tous les exemples mentionnés"],
                'correct_option': "Ethernet",
                'explanation': "Ethernet est un exemple de technologie de transmission par câble qui est largement utilisée pour connecter des appareils à un réseau local (LAN) à l'aide de câbles physiques."
            },
            {
                'id': "0214_8",
                'type': "vrai-faux",
                'question': "Tous les systèmes de communication utilisent la même technologie de transmission d'information. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, différents systèmes de communication peuvent utiliser différentes technologies de transmission d'information en fonction des besoins spécifiques du système, tels que la portée, la vitesse, la sécurité, et les contraintes environnementales."
            },
        ]
    ),
    (
        0215,
        "Environnement numérique de travail.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0215_1",
                'type': "qcm",
                'question': "Quels sont des éléments d'un environnement numérique de travail ?",
                'options': ["Ordinateur", "Logiciels de productivité", "Connexion Internet", "Tous les éléments mentionnés"],
                'correct_option': "Tous les éléments mentionnés",
                'explanation': "Un environnement numérique de travail comprend généralement un ordinateur, des logiciels de productivité tels que des suites bureautiques, et une connexion Internet pour accéder à des ressources en ligne et collaborer avec d'autres."
            },
            {
                'id': "0215_2",
                'type': "vrai-faux",
                'question': "Un environnement numérique de travail est un espace physique où les gens travaillent. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, un environnement numérique de travail est un espace virtuel qui comprend les outils et les ressources numériques nécessaires pour accomplir des tâches professionnelles, et n'est pas limité à un espace physique."
            },
            {
                'id': "0215_3",
                'type': "qcm",
                'question': "Quel est un exemple de logiciel de productivité utilisé dans un environnement numérique de travail ?",
                'options': ["Microsoft Word", "Adobe Photoshop", "Google Chrome", "Tous les exemples mentionnés"],
                'correct_option': "Microsoft Word",
                'explanation': "Microsoft Word est un exemple de logiciel de productivité largement utilisé dans les environnements numériques de travail pour la création et l'édition de documents texte."
            },
            {
                'id': "0215_4",
                'type': "vrai-faux",
                'question': "Une connexion Internet n'est pas nécessaire pour travailler dans un environnement numérique de travail. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, une connexion Internet est souvent essentielle pour travailler efficacement dans un environnement numérique de travail, car elle permet d'accéder à des ressources en ligne, de collaborer avec d'autres, et d'utiliser des outils basés sur le cloud."
            },
            {
                'id': "0215_5",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de collaboration en ligne utilisé dans un environnement numérique de travail ?",
                'options': ["Google Drive", "Microsoft Excel", "Adobe Illustrator", "Tous les exemples mentionnés"],
                'correct_option': "Google Drive",
                'explanation': "Google Drive est un exemple d'outil de collaboration en ligne qui permet aux utilisateurs de stocker, partager, et collaborer sur des fichiers et des documents en temps réel."
            },
            {
                'id': "0215_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents éléments d'un environnement numérique de travail. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents éléments d'un environnement numérique de travail en termes de leur fonction, de leur utilisation, et de leur importance dans le processus de travail numérique."
            },
            {
                'id': "0215_7",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui peut être utilisé dans un environnement numérique de travail ?",
                'options': ["Ordinateur de bureau", "Tablette", "Smartphone", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (ordinateur de bureau, tablette, smartphone) sont des types d'appareils couramment utilisés dans les environnements numériques de travail pour accomplir diverses tâches professionnelles."
            },
            {
                'id': "0215_8",
                'type': "vrai-faux",
                'question': "Tous les environnements numériques de travail sont les mêmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les environnements numériques de travail peuvent varier considérablement en fonction des outils, des ressources, et des technologies utilisés, ainsi que des besoins spécifiques de l'utilisateur ou de l'organisation."
            },
        ]
    ),
    (
        0216,
        "Réseaux informatiques au collège.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0216_1",
                'type': "qcm",
                'question': "Quels sont des éléments d'un réseau informatique au collège ?",
                'options': ["Routeur", "Switch", "Câbles Ethernet", "Tous les éléments mentionnés"],
                'correct_option': "Tous les éléments mentionnés",
                'explanation': "Un réseau informatique au collège peut inclure des éléments tels que des routeurs pour diriger le trafic de données, des switches pour connecter les appareils, et des câbles Ethernet pour la transmission de données."
            },
            {
                'id': "0216_2",
                'type': "vrai-faux",
                'question': "Un réseau informatique au collège est uniquement utilisé pour accéder à Internet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, un réseau informatique au collège peut être utilisé pour une variété de fonctions, y compris l'accès à Internet, le partage de ressources telles que les imprimantes, et la communication entre les appareils sur le réseau."
            },
            {
                'id': "0216_3",
                'type': "qcm",
                'question': "Quel est un exemple de protocole de communication utilisé dans un réseau informatique au collège ?",
                'options': ["HTTP", "FTP", "TCP/IP", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "HTTP, FTP, et TCP/IP sont tous des exemples de protocoles de communication qui peuvent être utilisés dans un réseau informatique au collège pour permettre la transmission de données entre les appareils et l'accès à Internet."
            },
            {
                'id': "0216_4",
                'type': "vrai-faux",
                'question': "Un pare-feu est un dispositif de sécurité qui peut être utilisé dans un réseau informatique au collège pour protéger contre les menaces. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un pare-feu est un dispositif de sécurité qui peut être utilisé dans un réseau informatique au collège pour surveiller et contrôler le trafic réseau afin de protéger contre les menaces potentielles."
            },
            {
                'id': "0216_5",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui peut être connecté à un réseau informatique au collège ?",
                'options': ["Ordinateur de bureau", "Tablette", "Imprimante réseau", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (ordinateur de bureau, tablette, imprimante réseau) sont des types d'appareils couramment connectés à un réseau informatique au collège pour accomplir diverses tâches éducatives et administratives."
            },
            {
                'id': "0216_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents éléments d'un réseau informatique au collège. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents éléments d'un réseau informatique au collège en termes de leur fonction, de leur utilisation, et de leur importance dans le fonctionnement global du réseau."
            },
            {
                'id': "0216_7",
                'type': "qcm",
                'question': "Quel est un exemple de technologie de transmission utilisée dans un réseau informatique au collège ?",
                'options': ["Ethernet", "Wi-Fi", "Bluetooth", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Ethernet, Wi-Fi, et Bluetooth sont tous des technologies de transmission qui peuvent être utilisées dans un réseau informatique au collège pour permettre la communication entre les appareils et l'accès à Internet."
            },
            {
                'id': "0216_8",
                'type': "vrai-faux",
                'question': "Tous les réseaux informatiques au collège sont les mêmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les réseaux informatiques au collège peuvent varier considérablement en fonction des technologies utilisées, des besoins spécifiques du collège, et des ressources disponibles."
            },
        ]
    ),
    (
        0217,
        "Sécurité et éthique numérique.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0210_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en sécurité et éthique numérique ?",
                'options': ["Confidentialité", "Sécurité des données", "Comportement en ligne responsable", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en sécurité et éthique numérique comprennent la confidentialité, qui protège les informations personnelles, la sécurité des données, qui assure la protection contre les cyberattaques, et le comportement en ligne responsable, qui guide les interactions sur Internet."
            },
            {
                'id': "0210_2",
                'type': "vrai-faux",
                'question': "La confidentialité est un concept de base en sécurité et éthique numérique. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la confidentialité est un concept de base en sécurité et éthique numérique qui protège les informations personnelles des utilisateurs."
            },
            {
                'id': "0210_3",
                'type': "qcm",
                'question': "Quel est un exemple de comportement en ligne responsable ?",
                'options': ["Partager des informations personnelles", "Respecter la vie privée des autres", "Ignorer les règles de sécurité", "Tous les comportements mentionnés"],
                'correct_option': "Respecter la vie privée des autres",
                'explanation': "Respecter la vie privée des autres est un exemple de comportement en ligne responsable, qui contribue à créer un environnement numérique sûr et respectueux."
            },
            {
                'id': "0210_4",
                'type': "vrai-faux",
                'question': "La sécurité des données n'est pas importante en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la sécurité des données est extrêmement importante en ligne pour protéger les informations personnelles et sensibles contre les cyberattaques et les violations de données."
            },
            {
                'id': "0210_5",
                'type': "qcm",
                'question': "Quel est un exemple de menace en ligne ?",
                'options': ["Phishing", "Ransomware", "Spyware", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Le phishing, le ransomware, et le spyware sont tous des exemples de menaces en ligne qui peuvent causer des dommages aux utilisateurs et à leurs appareils."
            },
            {
                'id': "0210_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes menaces en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes menaces en ligne en termes de leur nature, de leur méthode d'attaque, et de leur impact potentiel sur les utilisateurs."
            },
            {
                'id': "0210_7",
                'type': "qcm",
                'question': "Quel est un exemple de mesure de sécurité que les utilisateurs peuvent prendre pour se protéger en ligne ?",
                'options': ["Utiliser des mots de passe forts", "Mettre à jour régulièrement les logiciels", "Éviter de cliquer sur des liens suspects", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Utiliser des mots de passe forts, mettre à jour régulièrement les logiciels, et éviter de cliquer sur des liens suspects sont tous des mesures de sécurité importantes que les utilisateurs peuvent prendre pour se protéger en ligne."
            },
            {
                'id': "0210_8",
                'type': "vrai-faux",
                'question': "Tous les utilisateurs en ligne sont conscients des risques de sécurité et agissent de manière responsable. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les utilisateurs en ligne ne sont pas conscients des risques de sécurité et ne agissent pas toujours de manière responsable, ce qui souligne l'importance de l'éducation à la sécurité et à l'éthique numérique pour promouvoir un comportement en ligne sûr et responsable."
            },
        ]
    ),
    (
        0211,
        "Vérification et tests d'un prototype.",
        "Technologie",
        "6eme",
        [
            {
               'id': "0211_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en vérification et tests d'un prototype ?",
                'options': ["Test unitaire", "Test d'intégration", "Test système", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Les concepts de base en vérification et tests d'un prototype incluent le test unitaire, le test d'intégration, et le test système."
            },
            {
                'id': "0211_2",
                'type': "vrai-faux",
                'question': "Le test unitaire est un type de test qui vérifie le fonctionnement d'une unité individuelle d'un prototype. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le test unitaire est un type de test qui vérifie le fonctionnement d'une unité individuelle d'un prototype pour s'assurer qu'elle fonctionne correctement de manière isolée."
            },
            {
                'id': "0211_3",
                'type': "qcm",
                'question': "Quel est un exemple de test d'intégration ?",
                'options': ["Tester une fonction individuelle", "Tester l'interaction entre plusieurs unités", "Tester l'ensemble du système", "Tous les exemples mentionnés"],
                'correct_option': "Tester l'interaction entre plusieurs unités",
                'explanation': "Le test d'intégration est un type de test qui vérifie l'interaction entre plusieurs unités d'un prototype pour s'assurer qu'elles fonctionnent correctement ensemble."
            },
            {
                'id': "0211_4",
                'type': "vrai-faux",
                'question': "Le test système est un type de test qui vérifie le fonctionnement de l'ensemble du prototype. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le test système est un type de test qui vérifie le fonctionnement de l'ensemble du prototype pour s'assurer qu'il répond aux exigences spécifiées et fonctionne correctement dans son environnement prévu."
            },
            {
                'id': "0211_5",
                'type': "qcm",
                'question': "Quel est un exemple de test qui n'est pas utilisé dans la vérification d'un prototype ?",
                'options': ["Test unitaire", "Test d'intégration", "Test de performance", "Test de cuisine"],
                'correct_option': "Test de cuisine",
                'explanation': "Le test de cuisine n'est pas un type de test utilisé dans la vérification d'un prototype, tandis que le test unitaire, le test d'intégration, et le test de performance sont tous des types de tests couramment utilisés pour vérifier différents aspects d'un prototype."
            },
            {
                'id': "0211_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents types de tests utilisés dans la vérification d'un prototype. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents types de tests utilisés dans la vérification d'un prototype en termes de leur objectif, de leur portée, et de leur méthodologie."
            },
            {
                'id': "0211_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut être utilisé pour effectuer des tests sur un prototype ?",
                'options': ["JUnit", "Selenium", "Postman", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "JUnit, Selenium, et Postman sont tous des outils qui peuvent être utilisés pour effectuer des tests sur un prototype, chacun ayant des fonctionnalités spécifiques adaptées à différents types de tests."
            },
            {
                'id': "0211_8",
                'type': "vrai-faux",
                'question': "Tous les tests effectués sur un prototype sont les mêmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les tests effectués sur un prototype ne sont pas les mêmes, car ils peuvent varier en fonction de leur objectif, de leur portée, et de leur méthodologie."
            },
        ]
    ),
    (
        0212,
        "Déplacement et freinage d'un objet (exemples : vélo, voiture, ascenseur).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0212_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en déplacement et freinage d'un objet ?",
                'options': ["Force de friction", "Inertie", "Accélération", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en déplacement et freinage d'un objet comprennent la force de friction, qui s'oppose au mouvement, l'inertie, qui est la tendance d'un objet à rester en mouvement ou au repos, et l'accélération, qui est le changement de vitesse d'un objet."
            },
            {
                'id': "0212_2",
                'type': "vrai-faux",
                'question': "La force de friction est une force qui s'oppose au mouvement d'un objet. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la force de friction est une force qui s'oppose au mouvement d'un objet, ralentissant ou empêchant son déplacement."
            },
            {
                'id': "0212_3",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui utilise la force de friction pour se déplacer ?",
                'options': ["Vélo", "Voiture", "Ascenseur", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (vélo, voiture, ascenseur) utilisent la force de friction pour se déplacer, que ce soit entre les pneus et la route, ou entre les câbles et les poulies dans le cas de l'ascenseur."
            },
            {
                'id': "0212_4",
                'type': "vrai-faux",
                'question': "L'inertie est la tendance d'un objet à rester en mouvement ou au repos. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'inertie est un concept physique qui décrit la tendance d'un objet à rester en mouvement ou au repos à moins qu'une force extérieure ne soit appliquée pour changer son état de mouvement."
            },
            {
                'id': "0212_5",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui utilise l'inertie pour se déplacer ?",
                'options': ["Vélo", "Voiture", "Ascenseur", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (vélo, voiture, ascenseur) utilisent l'inertie pour se déplacer, car ils continuent à se déplacer à moins qu'une force extérieure ne soit appliquée pour les arrêter ou les ralentir."
            },
            {
                'id': "0212_6",
                'type': "vrai-faux",
                'question': "L'accélération est le changement de vitesse d'un objet. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'accélération est un concept physique qui décrit le changement de vitesse d'un objet au fil du temps, que ce soit une augmentation ou une diminution de sa vitesse."
            },
            {
                'id': "0212_7",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui utilise l'accélération pour se déplacer ?",
                'options': ["Vélo", "Voiture", "Ascenseur", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (vélo, voiture, ascenseur) utilisent l'accélération pour se déplacer, car ils peuvent augmenter ou diminuer leur vitesse en fonction des forces appliquées."
            },
            {
                'id': "0212_8",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents concepts de déplacement et freinage d'un objet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents concepts de déplacement et freinage d'un objet en termes de leur nature, de leur fonction, et de leur impact sur le mouvement d'un objet."
            },
        ]
    ),
    (
        0213,
        "Conception.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0213_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en conception ?",
                'options': ["Signal analogique", "Signal numérique", "Information binaire", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en conception comprennent le signal analogique, qui est un signal continu qui peut prendre une infinité de valeurs, le signal numérique, qui est un signal discret qui ne peut prendre que des valeurs spécifiques, et l'information binaire, qui est une forme d'information codée en utilisant deux états (0 et 1) pour représenter les données."
            },
            {
                'id': "0213_2",
                'type': "vrai-faux",
                'question': "Un signal analogique est caractérisé par sa continuité et sa capacité à prendre une infinité de valeurs dans une plage donnée. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un signal analogique est caractérisé par sa continuité et sa capacité à prendre une infinité de valeurs dans une plage donnée, contrairement à un signal numérique qui est discret et ne peut prendre que des valeurs spécifiques."
            },
            {
                'id': "0213_3",
                'type': "qcm",
                'question': "Quel est un exemple de signal numérique ?",
                'options': ["Signal de télévision analogique", "Signal de téléphone analogique", "Signal de données numériques", "Tous les exemples mentionnés"],
                'correct_option': "Signal de données numériques",
                'explanation': "Un signal de données numériques est un exemple de signal numérique, qui est utilisé pour transmettre des informations sous forme de données codées en binaire (0 et 1) dans les systèmes informatiques et de communication."
            },
            {
                'id': "0213_4",
                'type': "vrai-faux",
                'question': "L'information binaire est une forme d'information codée en utilisant deux états (0 et 1) pour représenter les données. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'information binaire est une forme d'information codée en utilisant deux états (0 et 1) pour représenter les données."
            },
            {
                'id': "0213_5",
                'type': "qcm",
                'question': "Quel est un exemple d'information qui peut être représentée en binaire ?",
                'options': ["Texte", "Image", "Son", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (texte, image, son) peuvent être représentés en binaire, car les données numériques sont codées en utilisant des séquences de 0 et 1 pour représenter différentes formes d'information dans les systèmes informatiques."
            },
            {
                'id': "0213_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les signaux analogiques et numériques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les signaux analogiques et numériques en termes de leur nature, de leur fonction, et de leur utilisation dans différents contextes de communication et de traitement de l'information."
            },
            {
                'id': "0213_7",
                'type': "qcm",
                'question': "Quel est un exemple de technologie qui utilise des signaux numériques ?",
                'options': ["Télévision analogique", "Téléphone analogique", "Ordinateur", "Tous les exemples mentionnés"],
                'correct_option': "Ordinateur",
                'explanation': "Un ordinateur est un exemple de technologie qui utilise des signaux numériques pour traiter et transmettre des données, tandis que la télévision analogique et le téléphone analogique utilisent des signaux analogiques."
            },
            {
                'id': "0213_8",
                'type': "vrai-faux",
                'question': "Tous les systèmes de communication utilisent des signaux numériques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les systèmes de communication n'utilisent pas des signaux numériques, car certains systèmes peuvent encore utiliser des signaux analogiques en fonction de leurs besoins spécifiques et de leur contexte d'utilisation."
            },
        ]
    ),
    (
        0214,
        "Définir un projet.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0214_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en définition d'un projet ?",
                'options': ["Objectifs du projet", "Ressources nécessaires", "Planification du projet", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en définition d'un projet comprennent les objectifs du projet, qui définissent ce que le projet vise à accomplir, les ressources nécessaires, qui incluent les personnes, les matériaux, et les finances requises pour réaliser le projet, et la planification du projet, qui implique l'organisation des tâches et des échéances pour atteindre les objectifs du projet."
            },
            {
                'id': "0214_2",
                'type': "vrai-faux",
                'question': "Les objectifs du projet sont des éléments clés dans la définition d'un projet. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les objectifs du projet sont des éléments clés dans la définition d'un projet, car ils fournissent une direction claire et mesurable pour ce que le projet vise à accomplir."
            },
            {
                'id': "0214_3",
                'type': "qcm",
                'question': "Quel est un exemple de ressource nécessaire pour un projet ?",
                'options': ["Personnel", "Matériaux", "Finances", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (personnel, matériaux, finances) sont des types de ressources nécessaires pour un projet, car ils sont essentiels pour réaliser les tâches et atteindre les objectifs du projet."
            },
            {
                'id': "0214_4",
                'type': "vrai-faux",
                'question': "La planification du projet n'est pas importante dans la définition d'un projet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la planification du projet est très importante dans la définition d'un projet, car elle permet d'organiser les tâches, de gérer les ressources, et de respecter les échéances pour atteindre les objectifs du projet de manière efficace."
            },
            {
                'id': "0214_5",
                'type': "qcm",
                'question': "Quel est un exemple de type de projet ?",
                'options': ["Projet de construction", "Projet de développement logiciel", "Projet de recherche scientifique", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (projet de construction, projet de développement logiciel, projet de recherche scientifique) sont des types de projets qui peuvent être définis en fonction de leurs objectifs, de leurs ressources, et de leur planification spécifiques."
            },
            {
                'id': "0214_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents types de projets. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents types de projets en termes de leur nature, de leurs objectifs, de leurs ressources, et de leur planification."
            },
            {
                'id': "0214_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut être utilisé pour la planification d'un projet ?",
                'options': ["GanttProject", "Trello", "Microsoft Project", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "GanttProject, Trello, et Microsoft Project sont tous des outils qui peuvent être utilisés pour la planification d'un projet, chacun offrant des fonctionnalités spécifiques pour aider à organiser les tâches, gérer les ressources, et suivre l'avancement du projet."
            },
            {
                'id': "0214_8",
                'type': "vrai-faux",
                'question': "Tous les projets sont les mêmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les projets ne sont pas les mêmes, car ils peuvent varier considérablement en fonction de leur nature, de leurs objectifs, de leurs ressources, et de leur planification."
            },
        ]
    ),
    (
        0215,
        "Évolution des besoins et innovations techniques.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0215_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en évolution des besoins et innovations techniques ?",
                'options': ["Évolution des besoins", "Innovation technique", "Impact de l'innovation sur la société", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en évolution des besoins et innovations techniques comprennent l'évolution des besoins, qui fait référence aux changements dans les exigences et les attentes des utilisateurs au fil du temps, l'innovation technique, qui implique le développement de nouvelles technologies ou l'amélioration de technologies existantes pour répondre à ces besoins changeants, et l'impact de l'innovation sur la société, qui examine comment les innovations techniques peuvent influencer la vie quotidienne, les comportements, et les structures sociales."
            },
            {
                'id': "0215_2",
                'type': "vrai-faux",
                'question': "L'évolution des besoins fait référence aux changements dans les exigences et les attentes des utilisateurs au fil du temps. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'évolution des besoins fait référence aux changements dans les exigences et les attentes des utilisateurs au fil du temps, ce qui peut conduire à la nécessité d'innovations techniques pour répondre à ces nouveaux besoins."
            },
            {
                'id': "0215_3",
                'type': "qcm",
                'question': "Quel est un exemple d'innovation technique ?",
                'options': ["Internet", "Smartphone", "Impression 3D", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Internet, le smartphone, et l'impression 3D sont tous des exemples d'innovations techniques qui ont eu un impact significatif sur la société en répondant à l'évolution des besoins des utilisateurs et en transformant la manière dont les gens communiquent, travaillent, et créent."
            },
            {
                'id': "0215_4",
                'type': "vrai-faux",
                'question': "L'innovation technique n'a pas d'impact sur la société. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, l'innovation technique a souvent un impact significatif sur la société en modifiant les comportements, les interactions, et les structures sociales, ainsi qu'en créant de nouvelles opportunités et défis pour les individus et les communautés."
            },
            {
                'id': "0215_5",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de collaboration en ligne qui a été développé en réponse à l'évolution des besoins des utilisateurs ?",
                'options': ["Google Drive", "Microsoft Teams", "Slack", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Google Drive, Microsoft Teams, et Slack sont tous des exemples d'outils de collaboration en ligne qui ont été développés en réponse à l'évolution des besoins des utilisateurs pour faciliter la communication, la collaboration, et le partage de ressources dans les environnements de travail et d'apprentissage à distance."
            },
            {
                'id': "0215_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes innovations techniques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes innovations techniques en termes de leur nature, de leur fonction, et de leur impact sur la société."
            },
            {
                'id': "0215_7",
                'type': "qcm",
                'question': "Quel est un exemple d'innovation technique qui a eu un impact significatif sur la société ?",
                'options': ["Internet", "Smartphone", "Impression 3D", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Internet, le smartphone, et l'impression 3D sont tous des exemples d'innovations techniques qui ont eu un impact significatif sur la société en répondant à l'évolution des besoins des utilisateurs et en transformant la manière dont les gens communiquent, travaillent, et créent."
            },
            {
                'id': "0215_8",
                'type': "vrai-faux",
                'question': "Tous les utilisateurs sont conscients de l'évolution des besoins et des innovations techniques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les utilisateurs ne sont pas nécessairement conscients de l'évolution des besoins et des innovations techniques, ce qui souligne l'importance de l'éducation et de la sensibilisation pour aider les individus à comprendre ces concepts et à s'adapter aux changements technologiques."
            },
        ]
    ),
    (
        0216,
        "Réalisation et démarches.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0216_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en réalisation et démarches ?",
                'options': ["Réalisation d'un projet", "Démarche de résolution de problèmes", "Collaboration en équipe", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en réalisation et démarches comprennent la réalisation d'un projet, qui implique la mise en œuvre des étapes nécessaires pour concrétiser une idée ou un plan, la démarche de résolution de problèmes, qui est un processus structuré pour identifier, analyser, et résoudre des problèmes, et la collaboration en équipe, qui est essentielle pour travailler efficacement avec d'autres personnes pour atteindre des objectifs communs."
            },
            {
                'id': "0216_2",
                'type': "vrai-faux",
                'question': "La réalisation d'un projet implique la mise en œuvre des étapes nécessaires pour concrétiser une idée ou un plan. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la réalisation d'un projet implique la mise en œuvre des étapes nécessaires pour concrétiser une idée ou un plan, ce qui peut inclure la planification, l'exécution, et l'évaluation du projet."
            },
            {
                'id': "0216_3",
                'type': "qcm",
                'question': "Quel est un exemple de technologie de transmission utilisée dans un réseau informatique au collège ?",
                'options': ["Ethernet", "Wi-Fi", "Bluetooth", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Ethernet, Wi-Fi, et Bluetooth sont tous des technologies de transmission qui peuvent être utilisées dans un réseau informatique au collège pour permettre la communication entre les appareils et l'accès à Internet."
            },
            {
                'id': "0216_4",
                'type': "vrai-faux",
                'question': "Un pare-feu est un dispositif de sécurité qui peut être utilisé dans un réseau informatique au collège pour protéger contre les menaces. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un pare-feu est un dispositif de sécurité qui peut être utilisé dans un réseau informatique au collège pour protéger contre les menaces en filtrant le trafic réseau et en bloquant les connexions non autorisées."
            },
            {
                'id': "0216_5",
                'type': "qcm",
                'question': "Quel est un exemple de protocole de communication utilisé dans un réseau informatique au collège ?",
                'options': ["TCP/IP", "HTTP", "FTP", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "TCP/IP, HTTP, et FTP sont tous des protocoles de communication qui peuvent être utilisés dans un réseau informatique au collège pour permettre la transmission de données et l'accès à des ressources en ligne."
            },
            {
                'id': "0216_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes technologies de transmission utilisées dans un réseau informatique au collège. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes technologies de transmission utilisées dans un réseau informatique au collège en termes de leur portée, de leur vitesse, et de leur utilisation spécifique."
            },
            {
                'id': "0216_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut être utilisé pour surveiller et gérer un réseau informatique au collège ?",
                'options': ["Wireshark", "Nagios", "SolarWinds", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Wireshark, Nagios, et SolarWinds sont tous des outils qui peuvent être utilisés pour surveiller et gérer un réseau informatique au collège, offrant des fonctionnalités pour analyser le trafic réseau, détecter les problèmes, et assurer la performance et la sécurité du réseau."
            },
            {
                'id': "0216_8",
                'type': "vrai-faux",
                'question': "Tous les réseaux informatiques au collège sont les mêmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les réseaux informatiques au collège ne sont pas les mêmes, car ils peuvent varier considérablement en fonction des technologies utilisées, des besoins spécifiques du collège, et des ressources disponibles."
            },
        ]
    ),
    (
        0217,
        "Sécurité et éthique numérique.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0217_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en sécurité et éthique numérique ?",
                'options': ["Sécurité des données", "Menaces en ligne", "Mesures de sécurité", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en sécurité et éthique numérique comprennent la sécurité des données, qui fait référence à la protection des informations personnelles et sensibles contre les cyberattaques et les violations de données, les menaces en ligne, qui incluent des activités malveillantes telles que le phishing, le ransomware, et le spyware, et les mesures de sécurité, qui sont des actions que les utilisateurs peuvent prendre pour se protéger en ligne, telles que l'utilisation de mots de passe forts, la mise à jour régulière des logiciels, et l'évitement de liens suspects."
            },
            {
                'id': "0217_2",
                'type': "vrai-faux",
                'question': "La sécurité des données n'est pas importante en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la sécurité des données est extrêmement importante en ligne pour protéger les informations personnelles et sensibles contre les cyberattaques et les violations de données."
            },
            {
                'id': "0217_3",
                'type': "qcm",
                'question': "Quel est un exemple de menace en ligne ?",
                'options': ["Phishing", "Ransomware", "Spyware", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Le phishing, le ransomware, et le spyware sont tous des exemples de menaces en ligne qui peuvent causer des dommages aux utilisateurs et à leurs appareils."
            },
            {
                'id': "0217_4",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes menaces en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes menaces en ligne en termes de leur nature, de leur impact, et des mesures de sécurité nécessaires pour les prévenir et les atténuer."
            },
            {
                'id': "0217_5",
                'type': "qcm",
                'question': "Quel est un exemple de mesure de sécurité que les utilisateurs peuvent prendre pour se protéger en ligne ?",
                'options': ["Utiliser des mots de passe forts", "Mettre à jour régulièrement les logiciels", "Éviter les liens suspects", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Utiliser des mots de passe forts, mettre à jour régulièrement les logiciels, et éviter les liens suspects sont tous des mesures de sécurité que les utilisateurs peuvent prendre pour se protéger en ligne contre les menaces et les cyberattaques."
             },
            {
                'id': "0217_6",
                'type': "vrai-faux",
                'question': "Tous les utilisateurs sont conscients des menaces en ligne et des mesures de sécurité. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les utilisateurs ne sont pas nécessairement conscients des menaces en ligne et des mesures de sécurité, ce qui peut les rendre vulnérables aux cyberattaques."
            },
            {
                'id': "0217_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de sécurité en ligne que les utilisateurs peuvent utiliser pour se protéger contre les menaces en ligne ?",
                'options': ["Antivirus", "Pare-feu", "VPN", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Un antivirus, un pare-feu, et un VPN sont tous des outils de sécurité en ligne que les utilisateurs peuvent utiliser pour se protéger contre les menaces en ligne en détectant et en bloquant les logiciels malveillants, en filtrant le trafic réseau, et en chiffrant les données pour assurer la confidentialité et la sécurité en ligne."
            },
            {
                'id': "0217_8",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes mesures de sécurité en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes mesures de sécurité en ligne en termes de leur nature, de leur fonction, et de leur efficacité pour prévenir et atténuer les menaces en ligne."
            },
        ]
    ),
    (
        0218,
        "Vérification d'un prototype.",
        "Technologie",
        "6eme",
        [
            {   'id': "0211_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en vérification d'un prototype ?",
                'options': ["Test de validation", "Test de vérification", "Test de performance", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en vérification d'un prototype comprennent le test de validation, qui vise à s'assurer que le prototype répond aux besoins et aux attentes des utilisateurs, le test de vérification, qui vise à s'assurer que le prototype fonctionne correctement et conformément aux spécifications, et le test de performance, qui évalue la rapidité, l'efficacité, et la fiabilité du prototype dans des conditions d'utilisation réelles."
            },
            {
                'id': "0211_2",
                'type': "vrai-faux",
                'question': "Le test de validation vise à s'assurer que le prototype répond aux besoins et aux attentes des utilisateurs. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le test de validation vise à s'assurer que le prototype répond aux besoins et aux attentes des utilisateurs en évaluant son adéquation avec les exigences fonctionnelles et les préférences des utilisateurs."
            },
            {
                'id': "0211_3",
                'type': "qcm",
                'question': "Quel est un exemple de test de vérification ?",
                'options': ["Test unitaire", "Test d'intégration", "Test de système", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Le test unitaire, le test d'intégration, et le test de système sont tous des exemples de tests de vérification qui peuvent être utilisés pour s'assurer que le prototype fonctionne correctement et conformément aux spécifications à différents niveaux du développement."
            },
            {
                'id': "0211_4",
                'type': "vrai-faux",
                'question': "Le test de performance évalue la rapidité, l'efficacité, et la fiabilité du prototype dans des conditions d'utilisation réelles. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le test de performance évalue la rapidité, l'efficacité, et la fiabilité du prototype dans des conditions d'utilisation réelles pour s'assurer qu'il peut répondre aux exigences de performance attendues par les utilisateurs."
            },
            {
                'id': "0211_5",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut être utilisé pour effectuer des tests sur un prototype ?",
                'options': ["JUnit", "Selenium", "Postman", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "JUnit, Selenium, et Postman sont tous des outils qui peuvent être utilisés pour effectuer des tests sur un prototype, offrant des fonctionnalités spécifiques pour les tests unitaires, les tests d'intégration, et les tests de performance."
            },
            {
                'id': "0211_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différents types de tests pour la vérification d'un prototype. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différents types de tests pour la vérification d'un prototype en termes de leur objectif, de leur portée, et de leur méthodologie."
            },
            {
                'id': "0211_7",
                'type': "qcm",
                'question': "Quel est un exemple de critère d'acceptation pour la validation d'un prototype ?",
                'options': ["Le prototype doit répondre aux besoins des utilisateurs", "Le prototype doit fonctionner correctement", "Le prototype doit être performant", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Tous les exemples mentionnés (le prototype doit répondre aux besoins des utilisateurs, le prototype doit fonctionner correctement, le prototype doit être performant) sont des critères d'acceptation importants pour la validation d'un prototype, car ils garantissent que le prototype est adapté à son usage prévu et satisfait les exigences des utilisateurs."
            },
            {
                'id': "0211_8",
                'type': "vrai-faux",
                'question': "Tous les prototypes sont vérifiés de la même manière. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les prototypes ne sont pas vérifiés de la même manière, car la vérification peut varier en fonction du type de prototype, du domaine d'application, et des exigences spécifiques du projet."
            },
        ]
    ),
    (
        0212,
        "Histoire des télécommunications.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0212_1",
                'type': "qcm",
                'question': "Quels sont des concepts que l'on ne retrouve pas dans l'histoire des télécommunications ?",
                'options': ["Télégraphe", "Téléphone", "Radio", "vélo"],
                'correct_option': "vélo",
                'explanation': "Le télégraphe, le téléphone, et la radio sont tous des concepts de base en histoire des télécommunications, car ils représentent des étapes clés dans l'évolution des technologies de communication. Le vélo, en revanche, n'est pas un concept lié à l'histoire des télécommunications."
            },
            {
                'id': "0212_2",
                'type': "vrai-faux",
                'question': "Le télégraphe a été inventé avant le téléphone. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le télégraphe a été inventé avant le téléphone. Le télégraphe a été développé au début du 19ème siècle, tandis que le téléphone a été inventé plus tard au 19ème siècle."
            },
            {
                'id': "0212_3",
                'type': "qcm",
                'question': "Quel exemple de technologie de communication a été développée après la radio ?",
                'options': ["Télévision", "Internet", "Téléphone mobile", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Les technologies de communication développées après la radio incluent la télévision, Internet, et le téléphone mobile, qui représentent des avancées significatives dans le domaine des communications."
            },
            {
                'id': "0212_4",
                'type': "vrai-faux",
                'question': "La radio a été inventée avant le télégraphe. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, le télégraphe a été inventé avant la radio. Le télégraphe a été développé au début du 19ème siècle, tandis que la radio a été inventée plus tard au 19ème siècle."
            },
            {
                'id': "0212_5",
                'type': "qcm",
                'question': "Quel exemple de technologie de communication n'a eu aucun impact significatif sur la société ?",
                'options': ["Télégraphe", "Téléphone", "Radio", "Aucune des options mentionnées"],
                'correct_option': "Aucune des options mentionnées",
                'explanation': "Toutes les technologies de communication mentionnées ont eu un impact significatif sur la société."
            },
            {
                'id': "0212_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes technologies de communication. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes technologies de communication en termes de leur nature, de leur fonction, et de leur impact sur la société."
            },
            {
                'id': "0212_7",
                'type': "qcm",
                'question': "Quel est un exemple d'innovation technique dans le domaine des télécommunications ?",
                'options': ["Télégraphe sans fil", "Téléphone portable", "Internet à haut débit", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Le télégraphe sans fil, le téléphone portable, et Internet à haut débit sont tous des exemples d'innovations techniques dans le domaine des télécommunications qui ont amélioré la connectivité et l'accessibilité des communications à travers le monde."
            },
            {
                'id': "0212_8",
                'type': "vrai-faux",
                'question': "Tous les utilisateurs sont conscients de l'histoire des télécommunications. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les utilisateurs ne sont pas nécessairement conscients de l'histoire des télécommunications, ce qui souligne l'importance de l'éducation pour aider les individus à comprendre l'évolution des technologies de communication et leur impact sur la société."
            },
        ]
    ),
    (
            0213,
            "Cahier des charges et contraintes.",
            "Technologie",
            "6eme",
            [
                {
                    'id': "0213_1",
                    'type': "qcm",
                    'question': "Qu'est-ce qu'un cahier des charges et les contraintes ?",
                    'options': ["Le cahier des charges est un document qui définit précisément les besoins, objectifs et exigences d'un projet.", "Les contraintes techniques sont des limitations ou des exigences liées à la technologie utilisée dans le projet.", "Les contraintes économiques sont des limitations ou des exigences liées au budget et aux ressources financières disponibles pour le projet.", "Aucun des concepts mentionnés"],
                    'correct_option': "Le cahier des charges est un document qui définit précisément les besoins, objectifs et exigences d'un projet.",
                    'explanation': "Le cahier des charges est un document essentiel dans la gestion de projet qui définit précisément les besoins, objectifs et exigences d'un projet pour guider le développement et assurer que les objectifs du projet sont atteints. Les contraintes techniques sont des limitations ou des exigences liées à la technologie utilisée dans le projet, tandis que les contraintes économiques sont des limitations ou des exigences liées au budget et aux ressources financières disponibles pour le projet."
            },
            {
                'id': "0213_2",
                'type': "vrai-faux",
                'question': "Le cahier des charges est un document qui décrit les exigences, les spécifications, et les critères de réussite d'un projet ou d'un produit. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le cahier des charges est un document essentiel dans la gestion de projet qui décrit les exigences, les spécifications, et les critères de réussite d'un projet ou d'un produit pour guider le développement et assurer que les objectifs du projet sont atteints."
            },
            {
                'id': "0213_3",
                'type': "qcm",
                'question': "Quel est un exemple de contrainte technique ?",
                'options': ["Limitation de la bande passante", "Compatibilité avec les systèmes existants", "Disponibilité des ressources matérielles", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "La limitation de la bande passante, la compatibilité avec les systèmes existants, et la disponibilité des ressources matérielles sont tous des exemples de contraintes techniques qui peuvent affecter la conception et le développement d'un projet ou d'un produit."
            },
            {
                'id': "0213_4",
                'type': "vrai-faux",
                'question': "Les contraintes économiques n'ont pas d'impact sur la gestion de projet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les contraintes économiques ont un impact significatif sur la gestion de projet, car elles peuvent limiter les ressources disponibles, affecter les délais, et influencer les décisions prises tout au long du projet."
            },
            {
                'id': "0213_5",
                'type': "qcm",
                'question': "Quel est un exemple de contrainte économique ?",
                'options': ["Budget limité", "Ressources humaines insuffisantes", "Délai serré", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Un budget limité, des ressources humaines insuffisantes, et un délai serré sont tous des exemples de contraintes économiques qui peuvent affecter la gestion de projet en limitant les ressources disponibles et en influençant les décisions prises pour atteindre les objectifs du projet."
            },
            {
                'id': "0213_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes contraintes dans un projet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes contraintes dans un projet en termes de leur nature, de leur impact, et des stratégies nécessaires pour les gérer efficacement."
            },
            {
                'id': "0213_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut être utilisé pour gérer les contraintes dans un projet ?",
                'options': ["Diagramme de Gantt", "Analyse SWOT", "Matrice de priorisation", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Le diagramme de Gantt, l'analyse SWOT, et la matrice de priorisation sont tous des outils qui peuvent être utilisés pour gérer les contraintes dans un projet en aidant à planifier, analyser, et prioriser les tâches et les ressources pour atteindre les objectifs du projet malgré les limitations et les exigences."
            },
            {
                'id': "0213_8",
                'type': "vrai-faux",
                'question': "Tous les projets ont les mêmes contraintes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les projets n'ont pas les mêmes contraintes, car ils peuvent varier considérablement en fonction de leur nature, de leurs objectifs, et des ressources disponibles."
            },
        ]
    ),
    (
        0214,
        "Planification d'un projet.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0214_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en planification d'un projet ?",
                'options': ["Planification de projet", "Gestion du temps", "Gestion des ressources", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en planification d'un projet comprennent la planification de projet, qui est le processus de définition des objectifs, des tâches, et des échéances pour atteindre les résultats souhaités, la gestion du temps, qui implique l'organisation et la priorisation des tâches pour respecter les délais, et la gestion des ressources, qui consiste à allouer efficacement les ressources humaines, matérielles, et financières pour soutenir la réalisation du projet."
            },
            {
                'id': "0214_2",
                'type': "vrai-faux",
                'question': "La planification de projet est le processus de définition des objectifs, des tâches, et des échéances pour atteindre les résultats souhaités. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la planification de projet est un processus essentiel dans la gestion de projet qui implique la définition des objectifs, des tâches, et des échéances pour guider le développement et assurer que les résultats souhaités sont atteints."
            },
            {
                'id': "0214_3",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de planification de projet ?",
                'options': ["GanttProject", "Trello", "Microsoft Project", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "GanttProject, Trello, et Microsoft Project sont tous des outils de planification de projet qui offrent différentes fonctionnalités pour aider à organiser les tâches, gérer les ressources, et suivre l'avancement du projet."
            },
            {
                'id': "0214_4",
                'type': "vrai-faux",
                'question': "La gestion du temps n'est pas importante dans la planification d'un projet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la gestion du temps est cruciale dans la planification d'un projet, car elle permet de s'assurer que les tâches sont réalisées dans les délais impartis et que les ressources sont utilisées efficacement."
            },
            {
                'id': "0214_5",
                'type': "qcm",
                'question': "Quel est un exemple de technique de gestion du temps ?",
                'options': ["Méthode Pomodoro", "Matrice d'Eisenhower", "Technique de la boîte à outils", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "La méthode Pomodoro, la matrice d'Eisenhower, et la technique de la boîte à outils sont toutes des techniques de gestion du temps qui peuvent aider les individus à organiser leur travail, prioriser les tâches, et améliorer leur productivité."
            },
            {
                'id': "0214_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de différence entre les différentes techniques de gestion du temps. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une différence entre les différentes techniques de gestion du temps en termes de leur approche, de leur efficacité, et de leur adaptabilité aux différents styles de travail et aux besoins individuels."
            },
            {
                'id': "0214_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de gestion des ressources ?",
                'options': ["Resource Guru", "Smartsheet", "Asana", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "Resource Guru, Smartsheet, et Asana sont tous des outils de gestion des ressources qui offrent des fonctionnalités pour planifier, allouer, et suivre l'utilisation des ressources dans le cadre d'un projet."
            },
            {
                'id': "0214_8",
                'type': "vrai-faux",
                'question': "Tous les projets nécessitent la même planification. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les projets ne nécessitent pas la même planification, car ils peuvent varier considérablement en fonction de leur nature, de leurs objectifs, et des ressources disponibles."
            },
        ]
    ),
    (
        0215,
        "Évolution des besoins et innovations techniques.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0215_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en évolution des besoins et innovations techniques ?",
                'options': ["Évolution des besoins", "Innovations techniques", "Impact sur la société", "Tous les concepts mentionnés"],
                'correct_option': "Tous les concepts mentionnés",
                'explanation': "Les concepts de base en évolution des besoins et innovations techniques comprennent l'évolution des besoins, qui fait référence aux changements dans les attentes, les préférences, et les exigences des utilisateurs au fil du temps, les innovations techniques, qui sont des avancées ou des améliorations dans les technologies existantes ou la création de nouvelles technologies pour répondre à ces besoins changeants, et l'impact sur la société, qui examine comment ces évolutions et innovations affectent la manière dont les gens vivent, travaillent, et interagissent."
            },
            {
                'id': "0215_2",
                'type': "vrai-faux",
                'question': "L'évolution des besoins fait référence aux changements dans les attentes, les préférences, et les exigences des utilisateurs au fil du temps. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'évolution des besoins fait référence aux changements dans les attentes, les préférences, et les exigences des utilisateurs au fil du temps, ce qui peut être influencé par divers facteurs tels que les avancées technologiques, les tendances sociales, et les changements culturels."
            },
            {
                'id': "0215_3",
                'type': "qcm",
                'question': "Quel est un exemple d'innovation technique ?",
                'options': ["Intelligence artificielle", "Réalité virtuelle", "Impression 3D", "Tous les exemples mentionnés"],
                'correct_option': "Tous les exemples mentionnés",
                'explanation': "L'intelligence artificielle, la réalité virtuelle, et l'impression 3D sont tous des exemples d'innovations techniques qui ont apporté de nouvelles possibilités et améliorations dans divers domaines tels que la santé, l'éducation, le divertissement, et la fabrication."
            },
            {
                'id': "0215_4",
                'type': "vrai-faux",
                'question': "Les innovations techniques n'ont aucun impact sur la société. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les innovations techniques ont un impact significatif sur la société, influençant la manière dont les gens vivent, travaillent, et interagissent."
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
            answers.append({"index": index, "question_id": index + 1, "type": "open", "answer": q["correct_answer"], "correction": q["explanation"]})
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
