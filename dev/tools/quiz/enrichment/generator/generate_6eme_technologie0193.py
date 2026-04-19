#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
GÃ©nÃ©rateur quiz Technologie 6e â€” SQUELETTE
"""

from __future__ import annotations
import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "technologie_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

quizzes_data = [
    (
        "0193",
        "DÃ©couverte de la technologie",
        "Technologie",
        "6e",
        [

            {
                'id': "0193_1",
                'type': "qcm",
                'question': "Qu'est-ce que la technologie ?",
                'options': ["Une matiÃ¨re scientifique", "L'Ã©tude des objets techniques", "Un sport", "Une langue"],
                'correct_option': "L'Ã©tude des objets techniques",
                'explanation': "La technologie est l'Ã©tude des objets techniques et de leur utilisation."
            },
            {
                'id': "0193_2",
                'type': "vrai-faux",
                'question': "La technologie est uniquement utilisÃ©e pour fabriquer des ordinateurs. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la technologie est utilisÃ©e dans de nombreux domaines, pas seulement pour les ordinateurs."
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
                'question': "La technologie peut aider Ã  rÃ©soudre des problÃ¨mes du quotidien. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la technologie peut Ãªtre utilisÃ©e pour trouver des solutions Ã  de nombreux problÃ¨mes du quotidien."
            },
            {
                'id': "0193_5",
                'type': "qcm",
                'question': "Quel est le but principal de la technologie ?",
                'options': ["AmÃ©liorer la vie des gens", "CrÃ©er des Å“uvres d'art", "Ã‰tudier les animaux", "Faire du sport"],
                'correct_option': "AmÃ©liorer la vie des gens",
                'explanation': "Le but principal de la technologie est d'amÃ©liorer la vie des gens en crÃ©ant des outils et des solutions pour rÃ©pondre Ã  leurs besoins."
            },
            {
                'id': "0193_6",
                'type': "vrai-faux",
                'question': "La technologie est un domaine qui Ã©volue rapidement. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la technologie Ã©volue constamment avec de nouvelles inventions et innovations."
            },
            {
                'id': "0193_7",
                'type': "qcm",
                'question': "Qu'est-ce qu'un objet technique ?",
                'options': ["Un objet naturel", "Un objet fabriquÃ© par l'homme pour rÃ©pondre Ã  un besoin", "Un animal", "Une plante"],
                'correct_option': "Un objet fabriquÃ© par l'homme pour rÃ©pondre Ã  un besoin",
                'explanation': "Un objet technique est un objet fabriquÃ© par l'homme pour rÃ©pondre Ã  un besoin spÃ©cifique."
            },
            {
                'id': "0193_8",
                'type': "vrai-faux",
                'question': "La technologie peut Ãªtre utilisÃ©e pour crÃ©er des Å“uvres d'art. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la technologie peut Ãªtre utilisÃ©e pour crÃ©er des Å“uvres d'art, comme la musique, les films, les jeux vidÃ©o, etc."
            },
        ]
    ),
    (
        "0194",
        "Les matÃ©riaux et leurs propriÃ©tÃ©s",
        "Technologie",
        "6e",
        [
            {
                'id': "0194_1",
                'type': "qcm",
                'question': "Quels sont les trois Ã©tats de la matiÃ¨re ?",
                'options': ["Solide, liquide, gaz", "Solide, liquide, plasma", "Liquide, gaz, plasma", "Solide, gaz, plasma"],
                'correct_option': "Solide, liquide, gaz",
                'explanation': "Les trois Ã©tats de la matiÃ¨re sont le solide, le liquide et le gaz."
            },
            {
                'id': "0194_2",
                'type': "vrai-faux",
                'question': "Le bois est un matÃ©riau naturel. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le bois est un matÃ©riau naturel provenant des arbres."
            },
            {
                'id': "0194_3",
                'type': "qcm",
                'question': "Quel matÃ©riau est le plus conducteur d'Ã©lectricitÃ© ?",
                'options': ["Plastique", "Bois", "MÃ©tal", "Verre"],
                'correct_option': "MÃ©tal",
                'explanation': "Le mÃ©tal est un excellent conducteur d'Ã©lectricitÃ©, contrairement au plastique, au bois et au verre."
            },
            {
                'id': "0194_4",
                'type': "vrai-faux",
                'question': "Le verre est un matÃ©riau transparent. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le verre est un matÃ©riau transparent qui permet de voir Ã  travers lui."
            },
            {
                'id': "0194_5",
                'type': "qcm",
                'question': "Quel matÃ©riau est le plus rÃ©sistant Ã  la chaleur ?",
                'options': ["Plastique", "Bois", "MÃ©tal", "Verre"],
                'correct_option': "MÃ©tal",
                'explanation': "Le mÃ©tal est gÃ©nÃ©ralement plus rÃ©sistant Ã  la chaleur que le plastique, le bois et le verre."
            },
            {
                'id': "0194_6",
                'type': "vrai-faux",
                'question': "Le plastique est un matÃ©riau recyclable. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le plastique peut Ãªtre recyclÃ© et rÃ©utilisÃ© pour fabriquer de nouveaux objets."
            },
            {
                'id': "0194_7",
                'type': "qcm",
                'question': "Quel matÃ©riau est le plus lÃ©ger ?",
                'options': ["Plastique", "Bois", "MÃ©tal", "Verre"],
                'correct_option': "Plastique",
                'explanation': "Le plastique est gÃ©nÃ©ralement plus lÃ©ger que le bois, le mÃ©tal et le verre."
            },
            {
                'id': "0194_8",
                'type': "vrai-faux",
                'question': "Le bois est un matÃ©riau renouvelable. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le bois est un matÃ©riau renouvelable car il peut Ãªtre replantÃ© et cultivÃ© Ã  nouveau."
            },
        ]
    ),
    (
        "0195",
        "Les Ã©nergies renouvelables",
        "Technologie",
        "6eme",
        [
            {
                'id': "0195_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'une Ã©nergie renouvelable ?",
                'options': ["Une Ã©nergie qui ne s'Ã©puise pas", "Une Ã©nergie qui pollue beaucoup", "Une Ã©nergie qui vient du pÃ©trole", "Une Ã©nergie qui est trÃ¨s chÃ¨re"],
                'correct_option': "Une Ã©nergie qui ne s'Ã©puise pas",
                'explanation': "Une Ã©nergie renouvelable est une Ã©nergie qui ne s'Ã©puise pas et qui peut Ãªtre utilisÃ©e de maniÃ¨re durable, comme l'Ã©nergie solaire, Ã©olienne, hydraulique, etc."
            },
            {
                'id': "0195_2",
                'type': "vrai-faux",
                'question': "L'Ã©nergie solaire est une Ã©nergie renouvelable. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'Ã©nergie solaire est une Ã©nergie renouvelable qui provient du soleil."
            },
            {
                'id': "0195_3",
                'type': "qcm",
                'question': "Quel est l'avantage principal des Ã©nergies renouvelables ?",
                'options': ["Elles sont gratuites", "Elles ne polluent pas", "Elles sont faciles Ã  stocker", "Elles sont disponibles partout"],
                'correct_option': "Elles ne polluent pas",
                'explanation': "L'avantage principal des Ã©nergies renouvelables est qu'elles ne polluent pas l'environnement, contrairement aux Ã©nergies fossiles."
            },
            {
                'id': "0195_4",
                'type': "vrai-faux",
                'question': "L'Ã©nergie Ã©olienne est produite par le vent. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'Ã©nergie Ã©olienne est produite par le mouvement du vent qui fait tourner les Ã©oliennes."
            },
            {
                'id': "0195_5",
                'type': "qcm",
                'question': "Quel est le principal inconvÃ©nient des Ã©nergies renouvelables ?",
                'options': ["Elles sont trÃ¨s chÃ¨res", "Elles ne sont pas fiables", "Elles nÃ©cessitent beaucoup d'espace", "Elles sont difficiles Ã  utiliser"],
                'correct_option': "Elles nÃ©cessitent beaucoup d'espace",
                'explanation': "Le principal inconvÃ©nient des Ã©nergies renouvelables est qu'elles nÃ©cessitent souvent beaucoup d'espace pour Ãªtre installÃ©es, comme les panneaux solaires ou les Ã©oliennes."
            },
            {
                'id': "0195_6",
                'type': "vrai-faux",
                'question': "L'Ã©nergie hydraulique est produite par l'eau. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'Ã©nergie hydraulique est produite par le mouvement de l'eau, comme les barrages hydroÃ©lectriques."
            },
            {
                'id': "0195_7",
                'type': "qcm",
                'question': "Quel est le principal dÃ©fi pour l'utilisation des Ã©nergies renouvelables ?",
                'options': ["Le coÃ»t Ã©levÃ©", "La dÃ©pendance au climat", "La pollution", "La raretÃ© des ressources"],
                'correct_option': "La dÃ©pendance au climat",
                'explanation': "Le principal dÃ©fi pour l'utilisation des Ã©nergies renouvelables est leur dÃ©pendance au climat, car elles peuvent Ãªtre moins efficaces dans certaines conditions mÃ©tÃ©orologiques."
            },
            {
                'id': "0195_8",
                'type': "vrai-faux",
                'question': "L'Ã©nergie gÃ©othermique est une Ã©nergie renouvelable. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'Ã©nergie gÃ©othermique est une Ã©nergie renouvelable qui provient de la chaleur de la Terre."
            },
        ]
    ),
    (
        "0196",
        "Les objets connectÃ©s",
        "Technologie",
        "6eme",
        [
            {
                'id': "0196_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un objet connectÃ© ?",
                'options': ["Un objet qui peut se connecter Ã  Internet", "Un objet qui est trÃ¨s cher", "Un objet qui est fabriquÃ© en plastique", "Un objet qui est trÃ¨s grand"],
                'correct_option': "Un objet qui peut se connecter Ã  Internet",
                'explanation': "Un objet connectÃ© est un objet qui peut se connecter Ã  Internet pour envoyer et recevoir des donnÃ©es, comme les smartphones, les montres connectÃ©es, les assistants vocaux, etc."
            },
            {
                'id': "0196_2",
                'type': "vrai-faux",
                'question': "Les objets connectÃ©s peuvent aider Ã  amÃ©liorer notre vie quotidienne. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les objets connectÃ©s peuvent aider Ã  amÃ©liorer notre vie quotidienne en facilitant certaines tÃ¢ches, en offrant des informations en temps rÃ©el, et en permettant une meilleure gestion de notre environnement."
            },
        ]
    ),
    (
        "0197",
        "Les objets connectÃ©s et la sÃ©curitÃ©",
        "Technologie",
        "6eme",
        [
            {
                'id': "0197_1",
                'type': "qcm",
                'question': "Qu'est-ce qu'un objet connectÃ© ?",
                'options': ["Un objet qui peut se connecter Ã  Internet", "Un objet qui est trÃ¨s cher", "Un objet qui est fabriquÃ© en plastique", "Un objet qui est trÃ¨s grand"],
                'correct_option': "Un objet qui peut se connecter Ã  Internet",
                'explanation': "Un objet connectÃ© est un objet qui peut se connecter Ã  Internet pour envoyer et recevoir des donnÃ©es, comme les smartphones, les montres connectÃ©es, les assistants vocaux, etc."
            },
            {
                'id': "0197_2",
                'type': "vrai-faux",
                'question': "Les objets connectÃ©s peuvent aider Ã  amÃ©liorer notre vie quotidienne. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les objets connectÃ©s peuvent aider Ã  amÃ©liorer notre vie quotidienne en facilitant certaines tÃ¢ches, en offrant des informations en temps rÃ©el, et en permettant une meilleure gestion de notre environnement."
            },
            {
                'id': "0197_3",
                'type': "qcm",
                'question': "Quel est le principal risque liÃ© aux objets connectÃ©s ?",
                'options': ["Le coÃ»t Ã©levÃ©", "La dÃ©pendance Ã  la technologie", "La violation de la vie privÃ©e", "La pollution"],
                'correct_option': "La violation de la vie privÃ©e",
                'explanation': "Le principal risque liÃ© aux objets connectÃ©s est la violation de la vie privÃ©e, car ils collectent souvent des donnÃ©es personnelles qui peuvent Ãªtre utilisÃ©es Ã  des fins malveillantes."
            },
            {
                'id': "0197_4",
                'type': "vrai-faux",
                'question': "Il est important de sÃ©curiser les objets connectÃ©s pour protÃ©ger nos donnÃ©es personnelles. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, il est crucial de sÃ©curiser les objets connectÃ©s en utilisant des mots de passe forts, en mettant Ã  jour rÃ©guliÃ¨rement le firmware, et en Ã©tant vigilant quant aux permissions accordÃ©es aux applications pour protÃ©ger nos donnÃ©es personnelles."
            },
            {
                'id': "0197_5",
                'type': "qcm",
                'question': "Quel est un moyen de protÃ©ger la sÃ©curitÃ© de nos objets connectÃ©s ?",
                'options': ["Utiliser des mots de passe forts", "Ne jamais les utiliser", "Partager nos donnÃ©es avec tout le monde", "Ne pas les mettre Ã  jour"],
                'correct_option': "Utiliser des mots de passe forts",
                'explanation': "Pour protÃ©ger la sÃ©curitÃ© de nos objets connectÃ©s, il est important d'utiliser des mots de passe forts, de ne pas partager nos donnÃ©es avec tout le monde, et de mettre Ã  jour rÃ©guliÃ¨rement le firmware pour corriger les vulnÃ©rabilitÃ©s de sÃ©curitÃ©."
            },
            {
                'id': "0197_6",
                'type': "vrai-faux",
                'question': "Il est sÃ»r de connecter tous les objets Ã  Internet sans protections. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il n'est pas sÃ»r de connecter tous les objets Ã  Internet sans protections, car cela peut exposer les donnÃ©es personnelles Ã  des risques de piratage et de violation de la vie privÃ©e."
            },
            {
                'id': "0197_7",
                'type': "qcm",
                'question': "Quel est un exemple d'objet connectÃ© qui peut prÃ©senter des risques de sÃ©curitÃ© ?",
                'options': ["Un smartphone", "Une montre connectÃ©e", "Un assistant vocal", "Tous les objets connectÃ©s"],
                'correct_option': "Tous les objets connectÃ©s",
                'explanation': "Tous les objets connectÃ©s peuvent prÃ©senter des risques de sÃ©curitÃ© s'ils ne sont pas correctement sÃ©curisÃ©s, car ils collectent souvent des donnÃ©es personnelles qui peuvent Ãªtre utilisÃ©es Ã  des fins malveillantes."
            },
            {
                'id': "0197_8",
                'type': "vrai-faux",
                'question': "Il est important de lire les politiques de confidentialitÃ© des objets connectÃ©s que nous utilisons. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, il est important de lire les politiques de confidentialitÃ© des objets connectÃ©s que nous utilisons pour comprendre quelles donnÃ©es sont collectÃ©es, comment elles sont utilisÃ©es, et quelles mesures de sÃ©curitÃ© sont en place pour protÃ©ger nos informations personnelles."
            },
        ]
    ),
    (
        "0198",
        "SchÃ©matiser le fonctionnement d'un objet technique simple.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0198_1",
                'type': "qcm",
                'question': "Comment schÃ©matiser le fonctionnement d'un objet technique simple ?",
                'options': ["En dessinant un diagramme de flux", "En Ã©crivant une description dÃ©taillÃ©e", "En crÃ©ant une maquette", "En faisant une prÃ©sentation orale"],
                'correct_option': "En dessinant un diagramme de flux",
                'explanation': "Pour schÃ©matiser le fonctionnement d'un objet technique simple, il est souvent utile de dessiner un diagramme de flux qui montre les diffÃ©rentes Ã©tapes du processus et les interactions entre les composants."
            },
            {
                'id': "0198_2",
                'type': "vrai-faux",
                'question': "L'usage d'un objet technique apporte-t-il des avantages dans la vie de tous les jours ? Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'usage d'un objet technique apporte des avantages dans la vie de tous les jours en facilitant certaines tÃ¢ches, en amÃ©liorant l'efficacitÃ© et en offrant de nouvelles possibilitÃ©s."
            },
            {
                'id': "0198_3",
                'type': "qcm",
                'question': "Quel est l'avantage de schÃ©matiser le fonctionnement d'un objet technique simple ?",
                'options': ["Cela permet de mieux comprendre le fonctionnement de l'objet", "Cela rend l'objet plus joli", "Cela permet de le vendre plus cher", "Cela n'a aucun avantage"],
                'correct_option': "Cela permet de mieux comprendre le fonctionnement de l'objet",
                'explanation': "SchÃ©matiser le fonctionnement d'un objet technique simple permet de mieux comprendre comment il fonctionne, quelles sont les diffÃ©rentes Ã©tapes du processus, et comment les composants interagissent."
            },
            {
                'id': "0198_4",
                'type': "vrai-faux",
                'question': "Il est important de schÃ©matiser le fonctionnement d'un objet technique pour pouvoir le rÃ©parer en cas de panne. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, schÃ©matiser le fonctionnement d'un objet technique peut Ãªtre trÃ¨s utile pour pouvoir le rÃ©parer en cas de panne, car cela permet de comprendre comment les diffÃ©rentes parties fonctionnent ensemble et oÃ¹ se situe le problÃ¨me."
            },
            {
                'id': "0198_5",
                'type': "qcm",
                'question': "Quel est un exemple d'objet technique simple que l'on peut schÃ©matiser ?",
                'options': ["Une lampe", "Un ordinateur", "Une voiture", "Un avion"],
                'correct_option': "Une lampe",
                'explanation': "Une lampe est un exemple d'objet technique simple que l'on peut schÃ©matiser, car elle a un fonctionnement relativement simple et des composants faciles Ã  reprÃ©senter."
            },
            {
                'id': "0198_6",
                'type': "vrai-faux",
                'question': "SchÃ©matiser le fonctionnement d'un objet technique simple peut aider Ã  trouver des idÃ©es pour l'amÃ©liorer. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, schÃ©matiser le fonctionnement d'un objet technique simple peut aider Ã  trouver des idÃ©es pour l'amÃ©liorer en identifiant les points faibles du design actuel et en proposant des solutions pour les rÃ©soudre."
            },
            {
                'id': "0198_7",
                'type': "qcm",
                'question': "Quel est un outil couramment utilisÃ© pour schÃ©matiser le fonctionnement d'un objet technique ?",
                'options': ["Un logiciel de dessin", "Un tableur", "Un traitement de texte", "Un logiciel de prÃ©sentation"],
                'correct_option': "Un logiciel de dessin",
                'explanation': "Un logiciel de dessin, comme Microsoft Visio, Lucidchart, ou mÃªme des outils de dessin simples comme Microsoft Paint, est couramment utilisÃ© pour schÃ©matiser le fonctionnement d'un objet technique."
            },
            {
                'id': "0198_8",
                'type': "vrai-faux",
                'question': "Il est inutile de schÃ©matiser le fonctionnement d'un objet technique si on comprend dÃ©jÃ  comment il fonctionne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, mÃªme si on comprend dÃ©jÃ  comment un objet technique fonctionne, schÃ©matiser son fonctionnement peut offrir une perspective diffÃ©rente et aider Ã  identifier des amÃ©liorations potentielles ou Ã  mieux communiquer son fonctionnement Ã  d'autres personnes."
            },
        ]
    ),
    (
        "0199",
        "Les Ã©tapes de la dÃ©marche de rÃ©solution de problÃ¨mes techniques",
        "Technologie",
        "6eme",
        [
            {
                'id': "0199_1",
                'type': "qcm",
                'question': "Quelles sont les Ã©tapes de la dÃ©marche de rÃ©solution de problÃ¨mes techniques ?",
                'options': ["Identifier le problÃ¨me, trouver des solutions, choisir la meilleure solution, mettre en Å“uvre la solution", "Trouver des solutions, identifier le problÃ¨me, choisir la meilleure solution, mettre en Å“uvre la solution", "Choisir la meilleure solution, identifier le problÃ¨me, trouver des solutions, mettre en Å“uvre la solution", "Mettre en Å“uvre la solution, identifier le problÃ¨me, trouver des solutions, choisir la meilleure solution"],
                'correct_option': "Identifier le problÃ¨me, trouver des solutions, choisir la meilleure solution, mettre en Å“uvre la solution",
                'explanation': "Les Ã©tapes de la dÃ©marche de rÃ©solution de problÃ¨mes techniques sont : identifier le problÃ¨me, trouver des solutions, choisir la meilleure solution, et mettre en Å“uvre la solution."
            },
            {
                'id': "0199_2",
                'type': "vrai-faux",
                'question': "Pourquoi l'Ã©tape d'analyse du besoin est-elle la premiÃ¨re dans la dÃ©marche technique ?\n\nVrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, suivre les Ã©tapes de la dÃ©marche de rÃ©solution de problÃ¨mes techniques permet de trouver une solution efficace en s'assurant que le problÃ¨me est correctement identifiÃ©, que plusieurs solutions sont envisagÃ©es, et que la meilleure solution est choisie et mise en Å“uvre."
            },
            {
                'id': "0199_3",
                'type': "qcm",
                'question': "Dans quelle Ã©tape utilise-t-on un cahier des charges pour dÃ©finir un projet ?",
                'options': ["Identifier le problÃ¨me", "Trouver des solutions", "Choisir la meilleure solution", "Mettre en Å“uvre la solution"],
                'correct_option': "Identifier le problÃ¨me",
                'explanation': "Le cahier des charges est utilisÃ© dans l'Ã©tape d'identification du problÃ¨me pour dÃ©finir les besoins et les contraintes du projet."
            },
            {
                'id': "0199_4",
                'type': "vrai-faux",
                'question': "Pour concevoir un abri pour un animal, il faut bien choisir les matÃ©riaux adaptÃ©s ?",
                'correct': True,
                'explanation': "Vrai, pour concevoir un abri pour un animal, il est important de choisir des matÃ©riaux adaptÃ©s qui offrent une bonne protection contre les intempÃ©ries, qui sont durables, et qui sont sÃ»rs pour l'animal."
            },
            {
                'id': "0199_5",
                'type': "qcm",
                'question': "Besoins humains et objets techniques associÃ©s. Quel est l'objectif principal de l'Ã©tape de recherche de solutions dans la dÃ©marche technique ?",
                'options': ["Trouver une seule solution possible", "Trouver plusieurs solutions possibles", "Choisir la meilleure solution immÃ©diatement", "Mettre en Å“uvre la solution sans rÃ©flÃ©chir"],
                'correct_option': "Trouver plusieurs solutions possibles",
                'explanation': "L'objectif principal de l'Ã©tape de recherche de solutions est de trouver plusieurs solutions possibles pour pouvoir les comparer et choisir la meilleure."
            },
            {
                'id': "0199_6",
                'type': "vrai-faux",
                'question': "Il est acceptable de sauter des Ã©tapes dans la dÃ©marche de rÃ©solution de problÃ¨mes techniques si on pense que ce n'est pas nÃ©cessaire. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il est important de suivre toutes les Ã©tapes de la dÃ©marche de rÃ©solution de problÃ¨mes techniques pour s'assurer que le problÃ¨me est correctement identifiÃ©, que plusieurs solutions sont envisagÃ©es, et que la meilleure solution est choisie et mise en Å“uvre."
            },
            {
                'id': "0199_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil utilisÃ© pour choisir la meilleure solution dans la dÃ©marche technique ?",
                'options': ["Un diagramme de Gantt", "Un tableau comparatif", "Un logiciel de dessin", "Un tableur"],
                'correct_option': "Un tableau comparatif",
                'explanation': "Un tableau comparatif est un outil couramment utilisÃ© pour comparer diffÃ©rentes solutions en fonction de critÃ¨res spÃ©cifiques afin d'aider Ã  choisir la meilleure option."
            },
            {
                'id': "0199_8",
                'type': "qcm",
                'question': "Quel est un exemple d'outil utilisÃ© pour planifier un projet dans la dÃ©marche technique ?",
                'options': ["Un diagramme de Gantt", "Un tableau comparatif", "Un logiciel de dessin", "Un tableur"],
                'correct_option': "Un diagramme de Gantt",
                'explanation': "Un diagramme de Gantt est un outil couramment utilisÃ© pour planifier et suivre l'avancement d'un projet en reprÃ©sentant les diffÃ©rentes tÃ¢ches et leur chronologie."
            },
        ]
    ),
    (
        "0200",
        "MatiÃ¨re vs matÃ©riau : diffÃ©rences et exemples.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0200_1",
                'type': "qcm",
                'question': "Quelle est la diffÃ©rence entre une matiÃ¨re et un matÃ©riau ?",
                'options': ["La matiÃ¨re est un matÃ©riau naturel, le matÃ©riau est fabriquÃ© par l'homme", "La matiÃ¨re est un matÃ©riau fabriquÃ© par l'homme, le matÃ©riau est naturel", "La matiÃ¨re est un concept abstrait, le matÃ©riau est concret", "Il n'y a pas de diffÃ©rence"],
                'correct_option': "La matiÃ¨re est un matÃ©riau naturel, le matÃ©riau est fabriquÃ© par l'homme",
                'explanation': "La matiÃ¨re est un matÃ©riau naturel qui existe dans la nature, tandis que le matÃ©riau est un matÃ©riau fabriquÃ© par l'homme Ã  partir de matiÃ¨res premiÃ¨res pour rÃ©pondre Ã  des besoins spÃ©cifiques."
            },
            {
                'id': "0200_2",
                'type': "vrai-faux",
                'question': "Le bois est un exemple de matiÃ¨re. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le bois est un exemple de matiÃ¨re naturelle qui peut Ãªtre utilisÃ©e pour fabriquer des matÃ©riaux comme le contreplaquÃ© ou les panneaux de particules."
            },
            {
                'id': "0200_3",
                'type': "qcm",
                'question': "Quel est un exemple de matÃ©riau fabriquÃ© par l'homme ?",
                'options': ["Le bois", "Le plastique", "Le mÃ©tal", "Le verre"],
                'correct_option': "Le plastique",
                'explanation': "Le plastique est un exemple de matÃ©riau fabriquÃ© par l'homme Ã  partir de matiÃ¨res premiÃ¨res comme le pÃ©trole."
            },
            {
                'id': "0200_4",
                'type': "vrai-faux",
                'question': "Le mÃ©tal est un exemple de matiÃ¨re. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, le mÃ©tal est un matÃ©riau fabriquÃ© par l'homme Ã  partir de matiÃ¨res premiÃ¨res comme le minerai de fer."
            },
            {
                'id': "0200_5",
                'type': "qcm",
                'question': "Quel est un exemple de matiÃ¨re naturelle ?",
                'options': ["Le plastique", "Le bois", "Le mÃ©tal", "Le verre"],
                'correct_option': "Le bois",
                'explanation': "Le bois est un exemple de matiÃ¨re naturelle qui peut Ãªtre utilisÃ©e pour fabriquer des matÃ©riaux comme le contreplaquÃ© ou les panneaux de particules."
            },
            {
                'id': "0200_6",
                'type': "vrai-faux",
                'question': "Le verre est un exemple de matÃ©riau fabriquÃ© par l'homme. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le verre est un matÃ©riau fabriquÃ© par l'homme Ã  partir de matiÃ¨res premiÃ¨res comme le sable et la soude."
            },
            {
                'id': "0200_7",
                'type': "qcm",
                'question': "Quel est l'intrus dans la liste suivante ?",
                'options': ["Le plastique", "Le bois", "Le mÃ©tal", "Le verre"],
                'correct_option': "Le bois",
                'explanation': "Le bois est une matiÃ¨re naturelle, tandis que le plastique, le mÃ©tal et le verre sont des matÃ©riaux fabriquÃ©s par l'homme Ã  partir de matiÃ¨res premiÃ¨res."
            },
            {
                'id': "0200_8",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre une matiÃ¨re et un matÃ©riau. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il y a une diffÃ©rence entre une matiÃ¨re et un matÃ©riau. La matiÃ¨re est un matÃ©riau naturel qui existe dans la nature, tandis que le matÃ©riau est un matÃ©riau fabriquÃ© par l'homme Ã  partir de matiÃ¨res premiÃ¨res pour rÃ©pondre Ã  des besoins spÃ©cifiques."
            },
        ]
    ),
    (
        "0201",
        "Tri et recyclage des matÃ©riaux.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0201_1",
                'type': "qcm",
                'question': "Quels matÃ©riaux peuvent Ãªtre recyclÃ©s ?",
                'options': ["Le plastique", "Le papier", "Le mÃ©tal", "Tous les matÃ©riaux mentionnÃ©s"],
                'correct_option': "Tous les matÃ©riaux mentionnÃ©s",
                'explanation': "De nombreux matÃ©riaux peuvent Ãªtre recyclÃ©s, y compris le plastique, le papier et le mÃ©tal."
            },
            {
                'id': "0201_2",
                'type': "vrai-faux",
                'question': "Le tri des dÃ©chets est important pour le recyclage. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le tri des dÃ©chets est essentiel pour permettre le recyclage des matÃ©riaux et rÃ©duire l'impact environnemental."
            },
            {
                'id': "0201_3",
                'type': "qcm",
                'question': "Quel est l'objectif principal du recyclage ?",
                'options': ["RÃ©duire la pollution", "Ã‰conomiser les ressources naturelles", "CrÃ©er de nouveaux emplois", "Tous les objectifs mentionnÃ©s"],
                'correct_option': "Tous les objectifs mentionnÃ©s",
                'explanation': "Le recyclage a plusieurs objectifs, notamment rÃ©duire la pollution, Ã©conomiser les ressources naturelles, et crÃ©er de nouveaux emplois dans l'industrie du recyclage."
            },
            {
                'id': "0201_4",
                'type': "vrai-faux",
                'question': "Le verre peut Ãªtre recyclÃ© indÃ©finiment sans perdre ses propriÃ©tÃ©s. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le verre peut Ãªtre recyclÃ© indÃ©finiment sans perdre ses propriÃ©tÃ©s, ce qui en fait un matÃ©riau trÃ¨s durable pour le recyclage."
            },
            {
                'id': "0201_5",
                'type': "qcm",
                'question': "Quel est un exemple de matÃ©riau qui ne peut pas Ãªtre recyclÃ© ?",
                'options': ["Le plastique", "Le papier", "Le mÃ©tal", "Le polystyrÃ¨ne"],
                'correct_option': "Le polystyrÃ¨ne",
                'explanation': "Le polystyrÃ¨ne est un matÃ©riau qui est difficile Ã  recycler et qui n'est pas acceptÃ© dans de nombreux programmes de recyclage en raison de sa faible valeur et de son impact environnemental."
            },
            {
                'id': "0201_6",
                'type': "vrai-faux",
                'question': "Le tri des dÃ©chets permet de rÃ©duire la quantitÃ© de dÃ©chets envoyÃ©s Ã  la dÃ©charge. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le tri des dÃ©chets permet de rÃ©duire la quantitÃ© de dÃ©chets envoyÃ©s Ã  la dÃ©charge en permettant le recyclage et la rÃ©utilisation des matÃ©riaux."
            },
            {
                'id': "0201_7",
                'type': "qcm",
                'question': "Quel est un avantage du recyclage des matÃ©riaux ?",
                'options': ["RÃ©duire la pollution", "Ã‰conomiser les ressources naturelles", "CrÃ©er de nouveaux emplois", "Tous les avantages mentionnÃ©s"],
                'correct_option': "Tous les avantages mentionnÃ©s",
                'explanation': "Le recyclage des matÃ©riaux offre plusieurs avantages, notamment rÃ©duire la pollution, Ã©conomiser les ressources naturelles, et crÃ©er de nouveaux emplois dans l'industrie du recyclage."
            },
            {
                'id': "0201_8",
                'type': "vrai-faux",
                'question': "Il est inutile de trier les dÃ©chets si on ne recycle pas. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, mÃªme si on ne recycle pas, trier les dÃ©chets peut aider Ã  rÃ©duire la quantitÃ© de dÃ©chets envoyÃ©s Ã  la dÃ©charge et Ã  faciliter le traitement des dÃ©chets, ce qui peut avoir un impact positif sur l'environnement."
            },
        ]
    ),
    (
        "0202",
        "Sources d'Ã©nergie (musculaire, chimique, Ã©lectrique).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0202_1",
                'type': "qcm",
                'question': "Quelles sont les sources d'Ã©nergie ?",
                'options': ["Musculaire", "Chimique", "Ã‰lectrique", "Toutes les sources mentionnÃ©es"],
                'correct_option': "Toutes les sources mentionnÃ©es",
                'explanation': "Il existe plusieurs sources d'Ã©nergie, notamment l'Ã©nergie musculaire, l'Ã©nergie chimique, et l'Ã©nergie Ã©lectrique."
            },
            {
                'id': "0202_2",
                'type': "vrai-faux",
                'question': "L'Ã©nergie musculaire est produite par le cerveau du corps humain. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, l'Ã©nergie musculaire est produite par les muscles du corps humain lorsqu'ils se contractent pour effectuer un travail."
            },
            {
                'id': "0202_3",
                'type': "qcm",
                'question': "Quel est un exemple d'Ã©nergie chimique ?",
                'options': ["L'Ã©lectricitÃ©", "Le carburant", "La lumiÃ¨re", "Le son"],
                'correct_option': "Le carburant",
                'explanation': "Le carburant est un exemple d'Ã©nergie chimique, car il contient de l'Ã©nergie stockÃ©e dans les liaisons chimiques qui peut Ãªtre libÃ©rÃ©e lors de la combustion."
            },
            {
                'id': "0202_4",
                'type': "vrai-faux",
                'question': "L'Ã©nergie Ã©lectrique est produite par le mouvement des Ã©lectrons. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'Ã©nergie Ã©lectrique est produite par le mouvement des Ã©lectrons Ã  travers un conducteur, ce qui permet de faire fonctionner des appareils Ã©lectriques."
            },
            {
                'id': "0202_5",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise l'Ã©nergie Ã©lectrique ?",
                'options': ["Une lampe", "Un moteur Ã  essence", "Un vÃ©lo", "Une bougie"],
                'correct_option': "Une lampe",
                'explanation': "Une lampe est un exemple d'appareil qui utilise l'Ã©nergie Ã©lectrique pour produire de la lumiÃ¨re."
            },
            {
                'id': "0202_6",
                'type': "vrai-faux",
                'question': "L'Ã©nergie musculaire peut Ãªtre convertie en Ã©nergie Ã©lectrique. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, l'Ã©nergie musculaire ne peut pas Ãªtre directement convertie en Ã©nergie Ã©lectrique. Cependant, elle peut Ãªtre utilisÃ©e pour produire de l'Ã©lectricitÃ© Ã  travers des dispositifs mÃ©caniques comme des gÃ©nÃ©rateurs."
            },
            {
                'id': "0202_7",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise l'Ã©nergie chimique ?",
                'options': ["Une batterie", "Un moteur Ã  essence", "Un panneau solaire", "Un Ã©olienne"],
                'correct_option': "Un moteur Ã  essence",
                'explanation': "Un moteur Ã  essence est un exemple d'appareil qui utilise l'Ã©nergie chimique du carburant pour produire de l'Ã©nergie mÃ©canique."
            },
            {
                'id': "0202_8",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes sources d'Ã©nergie. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe des diffÃ©rences entre les diffÃ©rentes sources d'Ã©nergie en termes de leur origine, de leur mode de production, et de leur utilisation."
            },
        ]
    ),
    (
        "0203",
        "Types de mouvements (rectiligne, circulaire).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0203_1",
                'type': "qcm",
                'question': "Quels sont les types de mouvements ?",
                'options': ["Rectiligne", "Circulaire", "Tous les types mentionnÃ©s", "Aucun des types mentionnÃ©s"],
                'correct_option': "Tous les types mentionnÃ©s",
                'explanation': "Il existe plusieurs types de mouvements, notamment le mouvement rectiligne, qui se fait en ligne droite, et le mouvement circulaire, qui se fait autour d'un point fixe."
            },
            {
                'id': "0203_2",
                'type': "vrai-faux",
                'question': "Le mouvement rectiligne est un mouvement horizontal. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le mouvement rectiligne se fait en ligne droite, et peut Ãªtre horizontal ou vertical selon le contexte."
            },
            {
                'id': "0203_3",
                'type': "qcm",
                'question': "Quel est un exemple de mouvement circulaire ?",
                'options': ["Le mouvement d'une voiture sur une route droite", "Le mouvement d'une roue de vÃ©lo", "Le mouvement d'un ascenseur", "Le mouvement d'un train sur des rails"],
                'correct_option': "Le mouvement d'une roue de vÃ©lo",
                'explanation': "Le mouvement d'une roue de vÃ©lo est un exemple de mouvement circulaire, car elle tourne autour d'un point fixe (l'axe de la roue)."
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
                'options': ["Le mouvement d'une roue de vÃ©lo", "Le mouvement d'une voiture sur une route droite", "Le mouvement d'un ascenseur qui monte et descend", "Le mouvement d'un train sur des rails"],
                'correct_option': "Le mouvement d'une voiture sur une route droite",
                'explanation': "Le mouvement d'une voiture sur une route droite est un exemple de mouvement rectiligne, car elle se dÃ©place en ligne droite."
            },
            {
                'id': "0203_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les mouvements rectilignes et circulaires. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les mouvements rectilignes et circulaires en termes de leur trajectoire et de leur nature."
            },
            {
                'id': "0203_7",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise un mouvement circulaire ?",
                'options': ["Une voiture", "Une roue de vÃ©lo", "Un ascenseur", "Un train"],
                'correct_option': "Une roue de vÃ©lo",
                'explanation': "Une roue de vÃ©lo utilise un mouvement circulaire, car elle tourne autour d'un point fixe (l'axe de la roue)."
            },
            {
                'id': "0203_8",
                'type': "vrai-faux",
                'question': "Le mouvement rectiligne est plus rapide que le mouvement circulaire. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la vitesse d'un mouvement rectiligne ou circulaire dÃ©pend de nombreux facteurs, tels que la force appliquÃ©e, la rÃ©sistance, et les conditions environnementales, et il n'est pas correct de dire que l'un est intrinsÃ¨quement plus rapide que l'autre."
            },
        ]
    ),
    (
        "0204",
        "Transmission de mouvement (engrenages, courroies).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0204_1",
                'type': "qcm",
                'question': "Quels sont les moyens de transmission de mouvement ?",
                'options': ["Engrenages", "Courroies", "Tous les moyens mentionnÃ©s", "Aucun des moyens mentionnÃ©s"],
                'correct_option': "Tous les moyens mentionnÃ©s",
                'explanation': "Il existe plusieurs moyens de transmission de mouvement, notamment les engrenages, qui sont des roues dentÃ©es qui s'engrÃ¨nent les unes dans les autres, et les courroies, qui sont des bandes flexibles qui transmettent le mouvement d'une poulie Ã  unen autre."
            },
            {
                'id': "0204_2",
                'type': "vrai-faux",
                'question': "Les engrenages permettent de changer la direction du mouvement. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les engrenages peuvent Ãªtre utilisÃ©s pour changer la direction du mouvement, ainsi que pour augmenter ou rÃ©duire la vitesse et le couple."
            },
            {
                'id': "0204_3",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise des engrenages pour transmettre le mouvement ?",
                'options': ["Une voiture", "Une montre", "Un ascenseur", "Un train"],
                'correct_option': "Une montre",
                'explanation': "Une montre utilise des engrenages pour transmettre le mouvement des aiguilles et assurer un fonctionnement prÃ©cis."
            },
            {
                'id': "0204_4",
                'type': "vrai-faux",
                'question': "Les courroies sont utilisÃ©es pour transmettre le mouvement entre des piÃ¨ces qui ne sont pas en contact direct. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les courroies sont utilisÃ©es pour transmettre le mouvement entre des piÃ¨ces qui ne sont pas en contact direct, comme dans les systÃ¨mes de transmission de puissance dans les machines industrielles ou les vÃ©hicules."
            },
            {
                'id': "0204_5",
                'type': "qcm",
                'question': "Quel est un avantage des courroies par rapport aux engrenages ?",
                'options': ["Elles sont plus silencieuses", "Elles sont plus durables", "Elles nÃ©cessitent moins d'entretien", "Toutes les rÃ©ponses mentionnÃ©es"],
                'correct_option': "Toutes les rÃ©ponses mentionnÃ©es",
                'explanation': "Les courroies ont plusieurs avantages par rapport aux engrenages, notamment qu'elles sont gÃ©nÃ©ralement plus silencieuses, peuvent Ãªtre plus durables dans certaines applications, et nÃ©cessitent souvent moins d'entretien."
            },
            {
                'id': "0204_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les engrenages et les courroies. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe des diffÃ©rences entre les engrenages et les courroies en termes de leur fonctionnement, de leur application, et de leurs avantages et inconvÃ©nients respectifs."
            },
            {
                'id': "0204_7",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise des courroies pour transmettre le mouvement ?",
                'options': ["Une voiture", "Une montre", "Un ascenseur", "Un train"],
                'correct_option': "Une voiture",
                'explanation': "Une voiture utilise des courroies pour transmettre le mouvement du moteur Ã  d'autres composants, comme l'alternateur ou la pompe Ã  eau."
            },
            {
                'id': "0204_8",
                'type': "vrai-faux",
                'question': "Les engrenages sont plus efficaces que les courroies pour transmettre le mouvement. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, l'efficacitÃ© de la transmission de mouvement dÃ©pend de nombreux facteurs, et il n'est pas correct de dire que les engrenages sont intrinsÃ¨quement plus efficaces que les courroies dans toutes les situations."
            },
        ]
    ),
    (
        "0205",
        "Stockage d'Ã©nergie (batterie, accumulateur).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0205_1",
                'type': "qcm",
                'question': "Quels sont les moyens de stockage d'Ã©nergie ?",
                'options': ["Batterie", "Accumulateur", "Tous les moyens mentionnÃ©s", "Aucun des moyens mentionnÃ©s"],
                'correct_option': "Tous les moyens mentionnÃ©s",
                'explanation': "Il existe plusieurs moyens de stockage d'Ã©nergie, notamment les batteries, qui sont des dispositifs Ã©lectrochimiques qui stockent l'Ã©nergie sous forme chimique, et les accumulateurs, qui sont des dispositifs qui stockent l'Ã©nergie sous forme Ã©lectrique."
            },
            {
                'id': "0205_2",
                'type': "vrai-faux",
                'question': "Les batteries sont utilisÃ©es pour stocker l'Ã©nergie dans les appareils Ã©lectroniques. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les batteries sont couramment utilisÃ©es pour stocker l'Ã©nergie dans les appareils Ã©lectroniques tels que les tÃ©lÃ©phones portables, les ordinateurs portables, et les vÃ©hicules Ã©lectriques."
            },
            {
                'id': "0205_3",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui utilise un accumulateur pour stocker l'Ã©nergie ?",
                'options': ["Une voiture Ã©lectrique", "Un tÃ©lÃ©phone portable", "Un ordinateur portable", "Une lampe de poche"],
                'correct_option': "Une voiture Ã©lectrique",
                'explanation': "Une voiture Ã©lectrique utilise un accumulateur pour stocker l'Ã©nergie Ã©lectrique qui alimente le moteur de la voiture."
            },
            {
                'id': "0205_4",
                'type': "vrai-faux",
                'question': "Les accumulateurs sont plus durables que les batteries. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la durabilitÃ© des accumulateurs par rapport aux batteries dÃ©pend de nombreux facteurs, et il n'est pas correct de dire que les accumulateurs sont intrinsÃ¨quement plus durables que les batteries dans toutes les situations."
            },
            {
                'id': "0205_5",
                'type': "qcm",
                'question': "Quel est un avantage des batteries par rapport aux accumulateurs ?",
                'options': ["Elles sont plus lÃ©gÃ¨res", "Elles ont une plus grande capacitÃ© de stockage", "Elles sont plus faciles Ã  recharger", "Toutes les rÃ©ponses mentionnÃ©es"],
                'correct_option': "Toutes les rÃ©ponses mentionnÃ©es",
                'explanation': "Les batteries ont plusieurs avantages par rapport aux accumulateurs, notamment qu'elles sont gÃ©nÃ©ralement plus lÃ©gÃ¨res, peuvent avoir une plus grande capacitÃ© de stockage, et sont souvent plus faciles Ã  recharger."
            },
            {
                'id': "0205_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les batteries et les accumulateurs. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe des diffÃ©rences entre les batteries et les accumulateurs en termes de leur fonctionnement, de leur application, et de leurs avantages et inconvÃ©nients respectifs."
            },
            {
                'id': "0205_7",
                'type': "qcm",
                'question': "Trouve l'intrus dans la liste suivante :",
                'options': ["Une voiture Ã©lectrique", "Un tÃ©lÃ©phone portable", "Un ordinateur portable", "Une lampe de chevet"],
                'correct_option': "Une lampe de chevet",
                'explanation': "Une lampe de chevet utilise une ampoule pour produire de la lumiÃ¨re et ne dÃ©pend pas d'une batterie pour fonctionner."
            },
            {
                'id': "0205_8",
                'type': "vrai-faux",
                'question': "Les accumulateurs et les batteries servent Ã  stocker l'Ã©nergie. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les accumulateurs et les batteries ont pour fonction principale de stocker l'Ã©nergie pour une utilisation ultÃ©rieure."
            },
        ]
    ),
    (
        "0206",
        "Mesure de vitesse et accÃ©lÃ©ration simple.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0206_1",
                'type': "qcm",
                'question': "Comment mesure-t-on la vitesse d'un objet ?",
                'options': ["En utilisant un chronomÃ¨tre et une rÃ¨gle pour mesurer le temps et la distance parcourue", "En utilisant un thermomÃ¨tre pour mesurer la tempÃ©rature de l'objet", "En utilisant un baromÃ¨tre pour mesurer la pression de l'air autour de l'objet", "En utilisant un hygromÃ¨tre pour mesurer l'humiditÃ© de l'air autour de l'objet"],
                'correct_option': "En utilisant un chronomÃ¨tre et une rÃ¨gle pour mesurer le temps et la distance parcourue",
                'explanation': "La vitesse d'un objet peut Ãªtre mesurÃ©e en utilisant un chronomÃ¨tre pour mesurer le temps qu'il met Ã  parcourir une certaine distance, et une rÃ¨gle pour mesurer cette distance. La formule de la vitesse est : vitesse = distance / temps."
            },
            {
                'id': "0206_2",
                'type': "vrai-faux",
                'question': "L'accÃ©lÃ©ration est la variation de la vitesse d'un objet par unitÃ© de temps. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'accÃ©lÃ©ration est dÃ©finie comme la variation de la vitesse d'un objet par unitÃ© de temps. Elle peut Ãªtre positive (accÃ©lÃ©ration) ou nÃ©gative (dÃ©cÃ©lÃ©ration)."
            },
            {
                'id': "0206_3",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui peut accÃ©lÃ©rer ?",
                'options': ["Une voiture qui dÃ©marre", "Un livre posÃ© sur une table", "Une lampe de bureau", "Un arbre"],
                'correct_option': "Une voiture qui dÃ©marre",
                'explanation': "Une voiture qui dÃ©marre peut accÃ©lÃ©rer en augmentant sa vitesse Ã  partir d'un Ã©tat de repos."
            },
            {
                'id': "0206_4",
                'type': "vrai-faux",
                'question': "La vitesse et l'accÃ©lÃ©ration sont des concepts liÃ©s mais diffÃ©rents. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la vitesse est une mesure de la rapiditÃ© d'un objet, tandis que l'accÃ©lÃ©ration mesure comment cette vitesse change au fil du temps."
            },
            {
                'id': "0206_5",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui peut dÃ©cÃ©lÃ©rer ?",
                'options': ["Une voiture qui freine", "Un livre posÃ© sur une table", "Une lampe de bureau", "Un arbre"],
                'correct_option': "Une voiture qui freine",
                'explanation': "Une voiture qui freine peut dÃ©cÃ©lÃ©rer en rÃ©duisant sa vitesse Ã  partir d'une vitesse initiale."
            },
            {
                'id': "0206_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre la vitesse et l'accÃ©lÃ©ration. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre la vitesse et l'accÃ©lÃ©ration en termes de leur dÃ©finition et de leur rÃ´le dans la description du mouvement d'un objet."
            },
            {
                'id': "0206_7",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui peut maintenir une vitesse constante ?",
                'options': ["Une voiture qui roule Ã  une vitesse constante sur une autoroute", "Un livre posÃ© sur une table", "Une lampe de bureau", "Un arbre"],
                'correct_option': "Une voiture qui roule Ã  une vitesse constante sur une autoroute",
                'explanation': "Une voiture qui roule Ã  une vitesse constante sur une autoroute peut maintenir cette vitesse sans accÃ©lÃ©rer ni dÃ©cÃ©lÃ©rer."
            },
            {
                'id': "0206_8",
                'type': "vrai-faux",
                'question': "La vitesse peut Ãªtre mesurÃ©e en mÃ¨tres par seconde (m/s) ou en kilomÃ¨tres par heure (km/h). Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la vitesse peut Ãªtre mesurÃ©e en diffÃ©rentes unitÃ©s, notamment en mÃ¨tres par seconde (m/s) ou en kilomÃ¨tres par heure (km/h), selon le contexte et les besoins de mesure."
            },
        ]
    ),
    (
        "0207",
        "PÃ©riphÃ©riques d'un ordinateur (entrÃ©e/sortie).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0207_1",
                'type': "qcm",
                'question': "Quels sont les pÃ©riphÃ©riques d'entrÃ©e d'un ordinateur ?",
                'options': ["Clavier", "Souris", "Ã‰cran", "Tous les pÃ©riphÃ©riques mentionnÃ©s"],
                'correct_option': "Tous les pÃ©riphÃ©riques mentionnÃ©s",
                'explanation': "Les pÃ©riphÃ©riques d'entrÃ©e d'un ordinateur comprennent le clavier, la souris, et l'Ã©cran tactile, qui permettent Ã  l'utilisateur de fournir des donnÃ©es et des commandes Ã  l'ordinateur."
            },
            {
                'id': "0207_2",
                'type': "vrai-faux",
                'question': "Un Ã©cran est un pÃ©riphÃ©rique de sortie. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un Ã©cran est un pÃ©riphÃ©rique de sortie qui affiche les informations traitÃ©es par l'ordinateur Ã  l'utilisateur."
            },
            {
                'id': "0207_3",
                'type': "qcm",
                'question': "Quel est un exemple qui n'est pas un pÃ©riphÃ©rique d'entrÃ©e ?",
                'options': ["Clavier", "Souris", "Imprimante", "Haut-parleur"],
                'correct_option': "Imprimante",
                'explanation': "Une imprimante est un pÃ©riphÃ©rique de sortie qui permet d'obtenir une copie papier des informations traitÃ©es par l'ordinateur."
            },
            {
                'id': "0207_4",
                'type': "vrai-faux",
                'question': "Un microphone est un pÃ©riphÃ©rique d'entrÃ©e. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un microphone est un pÃ©riphÃ©rique d'entrÃ©e qui permet de capturer des sons et de les transmettre Ã  l'ordinateur pour traitement."
            },
            {
                'id': "0207_5",
                'type': "qcm",
                'question': "Quel est un exemple de pÃ©riphÃ©rique de sortie ?",
                'options': ["Clavier", "Souris", "Ã‰cran", "Tous les pÃ©riphÃ©riques mentionnÃ©s"],
                'correct_option': "Ã‰cran",
                'explanation': "Un Ã©cran est un pÃ©riphÃ©rique de sortie qui affiche les informations traitÃ©es par l'ordinateur Ã  l'utilisateur."
            },
            {
                'id': "0207_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les pÃ©riphÃ©riques d'entrÃ©e et de sortie. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les pÃ©riphÃ©riques d'entrÃ©e et de sortie en termes de leur fonction dans le systÃ¨me informatique. Les pÃ©riphÃ©riques d'entrÃ©e permettent Ã  l'utilisateur de fournir des donnÃ©es et des commandes Ã  l'ordinateur, tandis que les pÃ©riphÃ©riques de sortie permettent Ã  l'ordinateur de communiquer des informations Ã  l'utilisateur."
            },
            {
                'id': "0207_7",
                'type': "qcm",
                'question': "Quel est un exemple de pÃ©riphÃ©rique qui peut Ãªtre Ã  la fois d'entrÃ©e et de sortie ?",
                'options': ["Clavier", "Souris", "Ã‰cran tactile", "Haut-parleur"],
                'correct_option': "Ã‰cran tactile",
                'explanation': "Un Ã©cran tactile peut Ãªtre Ã  la fois un pÃ©riphÃ©rique d'entrÃ©e, car il permet Ã  l'utilisateur d'interagir avec l'ordinateur en touchant l'Ã©cran, et un pÃ©riphÃ©rique de sortie, car il affiche les informations traitÃ©es par l'ordinateur."
            },
            {
                'id': "0207_8",
                'type': "vrai-faux",
                'question': "Tous les pÃ©riphÃ©riques d'un ordinateur sont soit d'entrÃ©e, soit de sortie. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, certains pÃ©riphÃ©riques peuvent Ãªtre Ã  la fois d'entrÃ©e et de sortie, comme les Ã©crans tactiles ou les haut-parleurs avec microphone intÃ©grÃ©, qui permettent Ã  la fois de fournir des donnÃ©es Ã  l'ordinateur et de recevoir des informations de l'ordinateur."
            },
        ]
    ),
    (
        "0208",
        "Logiciels de base (systÃ¨me d'exploitation, navigateur).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0208_1",
                'type': "qcm",
                'question': "Quels sont des exemples de logiciels de base ?",
                'options': ["SystÃ¨me d'exploitation", "Navigateur web", "Traitement de texte", "Tous les logiciels mentionnÃ©s"],
                'correct_option': "Tous les logiciels mentionnÃ©s",
                'explanation': "Les logiciels de base comprennent le systÃ¨me d'exploitation, qui gÃ¨re les ressources matÃ©rielles et logicielles de l'ordinateur, le navigateur web, qui permet d'accÃ©der Ã  Internet, et le traitement de texte, qui permet de crÃ©er et d'Ã©diter des documents."
            },
            {
                'id': "0208_2",
                'type': "vrai-faux",
                'question': "Un systÃ¨me d'exploitation est un logiciel qui gÃ¨re les ressources matÃ©rielles et logicielles de l'ordinateur. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un systÃ¨me d'exploitation est un logiciel essentiel qui gÃ¨re les ressources matÃ©rielles et logicielles de l'ordinateur, permettant aux autres logiciels de fonctionner correctement."
            },
            {
                'id': "0208_3",
                'type': "qcm",
                'question': "Quel est un exemple de systÃ¨me d'exploitation ?",
                'options': ["Windows", "Google Chrome", "Microsoft Word", "Adobe Photoshop"],
                'correct_option': "Windows",
                'explanation': "Windows est un exemple de systÃ¨me d'exploitation dÃ©veloppÃ© par Microsoft."
            },
            {
                'id': "0208_4",
                'type': "vrai-faux",
                'question': "Un navigateur web est un logiciel qui permet d'accÃ©der Ã  Internet. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un navigateur web est un logiciel qui permet aux utilisateurs d'accÃ©der Ã  Internet et de visualiser des pages web."
            },
            {
                'id': "0208_5",
                'type': "qcm",
                'question': "Quel est un exemple de navigateur web ?",
                'options': ["Windows", "Google Chrome", "Microsoft Word", "Adobe Photoshop"],
                'correct_option': "Google Chrome",
                'explanation': "Google Chrome est un exemple de navigateur web dÃ©veloppÃ© par Google."
            },
            {
                'id': "0208_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents types de logiciels. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents types de logiciels en termes de leur fonction, de leur utilisation, et de leur importance dans le systÃ¨me informatique."
            },
            {
                'id': "0208_7",
                'type': "qcm",
                'question': "Quel est un exemple de logiciel qui n'est pas un logiciel de base ?",
                'options': ["Windows", "Google Chrome", "Microsoft Word", "Adobe Photoshop"],
                'correct_option': "Adobe Photoshop",
                'explanation': "Adobe Photoshop est un logiciel de retouche photo et de crÃ©ation graphique, et n'est pas considÃ©rÃ© comme un logiciel de base comme le systÃ¨me d'exploitation ou le navigateur web."
            },
            {
                'id': "0208_8",
                'type': "vrai-faux",
                'question': "Tous les logiciels sont des logiciels de base. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les logiciels ne sont pas des logiciels de base. Il existe une grande variÃ©tÃ© de logiciels avec des fonctions spÃ©cifiques, et seuls certains d'entre eux sont considÃ©rÃ©s comme des logiciels de base."
            },
        ]
    ),
    (
        "0209",
        "Notions de base en programmation (algorithme, langage de programmation).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0209_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en programmation ?",
                'options': ["Algorithme", "Langage de programmation", "Base de donnÃ©es", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en programmation comprennent l'algorithme, qui est une sÃ©rie d'instructions pour rÃ©soudre un problÃ¨me, le langage de programmation, qui est un langage utilisÃ© pour Ã©crire des programmes informatiques, et la base de donnÃ©es, qui est un systÃ¨me organisÃ© pour stocker et gÃ©rer des donnÃ©es."
            },
            {
                'id': "0209_2",
                'type': "vrai-faux",
                'question': "Un algorithme est une sÃ©rie d'instructions pour rÃ©soudre un problÃ¨me. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un algorithme est une sÃ©quence d'instructions ou d'Ã©tapes qui sont suivies pour rÃ©soudre un problÃ¨me ou accomplir une tÃ¢che spÃ©cifique."
            },
            {
                'id': "0209_3",
                'type': "qcm",
                'question': "Quel est un exemple de langage de programmation ?",
                'options': ["Python", "GIT", "CRUD", "Tous les langages mentionnÃ©s"],
                'correct_option': "Python",
                'explanation': "Python est un exemple de langage de programmation populaire utilisÃ© pour le dÃ©veloppement de logiciels, l'analyse de donnÃ©es, et l'intelligence artificielle."
            },
            {
                'id': "0209_4",
                'type': "vrai-faux",
                'question': "Le langage de HTML est utilisÃ© pour crÃ©er des pages web. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, HTML (HyperText Markup Language) est un langage de balisage utilisÃ© pour structurer et prÃ©senter le contenu sur le web."
            },
            {
                'id': "0209_5",
                'type': "qcm",
                'question': "Quel est un exemple de base de donnÃ©es ?",
                'options': ["MySQL", "Python", "HTML", "CSS"],
                'correct_option': "MySQL",
                'explanation': "MySQL est un exemple de systÃ¨me de gestion de base de donnÃ©es relationnelle utilisÃ© pour stocker et gÃ©rer des donnÃ©es dans des applications web et d'autres types de logiciels."
            },
            {
                'id': "0209_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents concepts en programmation. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents concepts en programmation en termes de leur fonction, de leur utilisation, et de leur importance dans le processus de dÃ©veloppement logiciel."
            },
            {
                'id': "0209_7",
                'type': "qcm",
                'question': "Quel est un exemple de tÃ¢che qui peut Ãªtre accomplie avec un algorithme ?",
                'options': ["Trier une liste de nombres", "CrÃ©er une page web", "GÃ©rer une base de donnÃ©es", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Un algorithme peut Ãªtre utilisÃ© pour accomplir une variÃ©tÃ© de tÃ¢ches, telles que trier une liste de nombres, crÃ©er une page web, ou gÃ©rer une base de donnÃ©es, en fonction des instructions spÃ©cifiques qu'il contient."
            },
            {
                'id': "0209_8",
                'type': "vrai-faux",
                'question': "Tous les langages de programmation sont utilisÃ©s pour les mÃªmes types de tÃ¢ches. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, diffÃ©rents langages de programmation sont souvent utilisÃ©s pour des types de tÃ¢ches spÃ©cifiques en fonction de leurs caractÃ©ristiques et de leur domaine d'application. Par exemple, Python est souvent utilisÃ© pour l'analyse de donnÃ©es et l'intelligence artificielle, tandis que JavaScript est couramment utilisÃ© pour le dÃ©veloppement web."
            },
        ]
    ),
    (
        "0210",
        "Notions de base en Ã©lectronique (circuit, composant).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0210_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en Ã©lectronique ?",
                'options': ["Circuit", "Composant", "RÃ©sistance", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en Ã©lectronique comprennent le circuit, qui est un chemin fermÃ© Ã  travers lequel le courant Ã©lectrique peut circuler, le composant, qui est une partie individuelle d'un circuit Ã©lectronique, et la rÃ©sistance, qui est une mesure de la difficultÃ© pour le courant de circuler Ã  travers un composant ou un circuit."
            },
            {
                'id': "0210_2",
                'type': "vrai-faux",
                'question': "Un circuit est un chemin fermÃ© Ã  travers lequel le courant Ã©lectrique peut circuler. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un circuit est dÃ©fini comme un chemin fermÃ© qui permet au courant Ã©lectrique de circuler, et il peut Ãªtre composÃ© de divers composants Ã©lectroniques tels que des rÃ©sistances, des condensateurs, et des transistors."
            },
            {
                'id': "0210_3",
                'type': "qcm",
                'question': "Quel est un exemple de composant Ã©lectronique ?",
                'options': ["RÃ©sistance", "Clavier", "Souris", "Ã‰cran"],
                'correct_option': "RÃ©sistance",
                'explanation': "Une rÃ©sistance est un exemple de composant Ã©lectronique qui limite le flux de courant dans un circuit."
            },
            {
                'id': "0210_4",
                'type': "vrai-faux",
                'question': "Un transistor est un composant Ã©lectronique qui peut amplifier ou commuter des signaux Ã©lectriques. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un transistor est un composant Ã©lectronique essentiel qui peut Ãªtre utilisÃ© pour amplifier ou commuter des signaux Ã©lectriques dans les circuits Ã©lectroniques."
            },
            {
                'id': "0210_5",
                'type': "qcm",
                'question': "Quel est un exemple de circuit Ã©lectronique ?",
                'options': ["Un circuit imprimÃ©", "Un clavier d'ordinateur", "Une souris d'ordinateur", "Un Ã©cran d'ordinateur"],
                'correct_option': "Un circuit imprimÃ©",
                'explanation': "Un circuit imprimÃ© (PCB) est un exemple de circuit Ã©lectronique qui supporte et connecte les composants Ã©lectroniques Ã  l'aide de pistes conductrices."
            },
            {
                'id': "0210_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents concepts en Ã©lectronique. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents concepts en Ã©lectronique en termes de leur fonction, de leur utilisation, et de leur importance dans la conception et le fonctionnement des circuits Ã©lectroniques."
            },
            {
                'id': "0210_7",
                'type': "qcm",
                'question': "Quel est un exemple de composant qui n'est pas Ã©lectronique ?",
                'options': ["RÃ©sistance", "Clavier", "Souris", "Ã‰cran"],
                'correct_option': "Clavier",
                'explanation': "Un clavier est un pÃ©riphÃ©rique d'entrÃ©e utilisÃ© pour interagir avec un ordinateur, et n'est pas considÃ©rÃ© comme un composant Ã©lectronique dans le contexte des circuits Ã©lectroniques."
            },
            {
                'id': "0210_8",
                'type': "vrai-faux",
                'question': "Tous les composants Ã©lectroniques sont utilisÃ©s dans les circuits Ã©lectroniques. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, tous les composants Ã©lectroniques sont conÃ§us pour Ãªtre utilisÃ©s dans des circuits Ã©lectroniques afin de remplir des fonctions spÃ©cifiques telles que la rÃ©sistance, l'amplification, ou la commutation de signaux Ã©lectriques."
            },
        ]
    ),
    (
        "0211",
        "Arborescence des fichiers informatiques.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0211_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en arborescence des fichiers informatiques ?",
                'options': ["Dossier", "Fichier", "Chemin d'accÃ¨s", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en arborescence des fichiers informatiques comprennent le dossier, qui est un conteneur pour organiser les fichiers, le fichier lui-mÃªme, qui est une unitÃ© de stockage de donnÃ©es, et le chemin d'accÃ¨s, qui est l'adresse permettant de localiser un fichier ou un dossier dans le systÃ¨me de fichiers."
            },
            {
                'id': "0211_2",
                'type': "vrai-faux",
                'question': "Un fichier est un conteneur pour organiser les dossiers. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, un fichier est une unitÃ© de stockage de donnÃ©es, tandis qu'un dossier est un conteneur utilisÃ© pour organiser les fichiers. Les fichiers sont stockÃ©s Ã  l'intÃ©rieur des dossiers, et non l'inverse."
            },
            {
                'id': "0211_3",
                'type': "qcm",
                'question': "Quel est un exemple de chemin d'accÃ¨s Ã  un fichier ?",
                'options': ["C:\\Users\\NomUtilisateur\\Documents\\Fichier.txt", "/home/nomutilisateur/documents/fichier.txt", "Tous les exemples mentionnÃ©s", "Aucun des exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Les chemins d'accÃ¨s Ã  un fichier peuvent varier en fonction du systÃ¨me d'exploitation utilisÃ©. Par exemple, sous Windows, un chemin d'accÃ¨s peut ressembler Ã  'C:\\Users\\NomUtilisateur\\Documents\\Fichier.txt', tandis que sous Linux ou macOS, un chemin d'accÃ¨s peut ressembler Ã  '/home/nomutilisateur/documents/fichier.txt'."
            },
            {
                'id': "0211_4",
                'type': "vrai-faux",
                'question': "L'arborescence des fichiers informatiques est une structure hiÃ©rarchique qui organise les fichiers et les dossiers. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'arborescence des fichiers informatiques est une structure hiÃ©rarchique qui organise les fichiers et les dossiers de maniÃ¨re Ã  faciliter la gestion et la navigation dans le systÃ¨me de fichiers. Les dossiers peuvent contenir des fichiers et d'autres dossiers, crÃ©ant ainsi une structure en forme d'arbre."
            },
            {
                'id': "0211_5",
                'type': "qcm",
                'question': "Quel est un exemple de systÃ¨me d'exploitation qui utilise une arborescence de fichiers ?",
                'options': ["Windows", "Linux", "macOS", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Les systÃ¨mes d'exploitation modernes comme Windows, Linux et macOS utilisent tous une arborescence de fichiers pour organiser les fichiers et les dossiers."
            },
            {
                'id': "0211_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les fichiers et les dossiers dans l'arborescence des fichiers informatiques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les fichiers et les dossiers dans l'arborescence des fichiers informatiques. Les fichiers sont des unitÃ©s de stockage de donnÃ©es, tandis que les dossiers sont des conteneurs utilisÃ©s pour organiser les fichiers."
            },
            {
                'id': "0211_7",
                'type': "qcm",
                'question': "Quel est un exemple de fichier qui pourrait Ãªtre stockÃ© dans un dossier ?",
                'options': ["Document Word", "Image JPEG", "Fichier PDF", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s sont des types de fichiers courants qui peuvent Ãªtre stockÃ©s dans un dossier pour une organisation efficace."
            },
            {
                'id': "0211_8",
                'type': "vrai-faux",
                'question': "Tous les systÃ¨mes d'exploitation utilisent la mÃªme structure d'arborescence de fichiers. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, bien que la plupart des systÃ¨mes d'exploitation modernes utilisent une structure d'arborescence de fichiers, la maniÃ¨re dont cette structure est organisÃ©e peut varier d'un systÃ¨me Ã  l'autre."
            },
        ]
    ),
    (
        "0212",
        "Notions de base en rÃ©seaux informatiques (adresse IP, protocole).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0212_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en rÃ©seaux informatiques ?",
                'options': ["Adresse IP", "Protocole", "Routeur", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en rÃ©seaux informatiques comprennent l'adresse IP, qui est une adresse unique attribuÃ©e Ã  chaque appareil connectÃ© Ã  un rÃ©seau, le protocole, qui est un ensemble de rÃ¨gles pour la communication entre les appareils sur un rÃ©seau, et le routeur, qui est un dispositif qui dirige le trafic de donnÃ©es entre les rÃ©seaux."
            },
            {
                'id': "0212_2",
                'type': "vrai-faux",
                'question': "Une adresse IP est une adresse unique attribuÃ©e Ã  chaque appareil connectÃ© Ã  un rÃ©seau. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, une adresse IP (Internet Protocol) est une adresse numÃ©rique unique attribuÃ©e Ã  chaque appareil connectÃ© Ã  un rÃ©seau informatique, permettant l'identification et la communication entre les appareils sur le rÃ©seau."
            },
            {
                'id': "0212_3",
                'type': "qcm",
                'question': "Quel est un exemple de protocole de communication utilisÃ© dans les rÃ©seaux informatiques ?",
                'options': ["HTTP", "FTP", "TCP/IP", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "HTTP (HyperText Transfer Protocol), FTP (File Transfer Protocol), et TCP/IP (Transmission Control Protocol/Internet Protocol) sont tous des exemples de protocoles de communication utilisÃ©s dans les rÃ©seaux informatiques pour permettre la transmission de donnÃ©es entre les appareils."
            },
            {
                'id': "0212_4",
                'type': "vrai-faux",
                'question': "Un routeur est un dispositif qui dirige le trafic de donnÃ©es entre les rÃ©seaux. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un routeur est un dispositif essentiel dans les rÃ©seaux informatiques qui dirige le trafic de donnÃ©es entre les diffÃ©rents rÃ©seaux, permettant aux appareils de communiquer entre eux mÃªme s'ils sont sur des rÃ©seaux diffÃ©rents."
            },
            {
                'id': "0212_5",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui peut Ãªtre connectÃ© Ã  un rÃ©seau informatique ?",
                'options': ["Ordinateur", "Smartphone", "Imprimante", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s sont des types d'appareils courants qui peuvent Ãªtre connectÃ©s Ã  un rÃ©seau informatique pour partager des ressources et communiquer avec d'autres appareils."
            },
            {
                'id': "0212_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents concepts en rÃ©seaux informatiques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents concepts en rÃ©seaux informatiques en termes de leur fonction, de leur utilisation, et de leur importance dans la conception et le fonctionnement des rÃ©seaux informatiques."
            },
            {
                'id': "0212_7",
                'type': "qcm",
                'question': "Quel est un exemple de protocole qui n'est pas utilisÃ© dans les rÃ©seaux informatiques ?",
                'options': ["HTTP", "FTP", "TCP/IP", "SMTP"],
                'correct_option': "SMTP",
                'explanation': "SMTP (Simple Mail Transfer Protocol) est un protocole utilisÃ© pour la transmission d'e-mails, et n'est pas principalement utilisÃ© pour la communication gÃ©nÃ©rale dans les rÃ©seaux informatiques comme HTTP, FTP, ou TCP/IP."
            },
            {
                'id': "0212_8",
                'type': "vrai-faux",
                'question': "Tous les appareils connectÃ©s Ã  un rÃ©seau informatique ont une adresse IP. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, tous les appareils connectÃ©s Ã  un rÃ©seau informatique se voient attribuer une adresse IP unique pour permettre l'identification et la communication sur le rÃ©seau."
            },
        ]
    ),
    (
        "0213",
        "Signaux et information (analogique/numÃ©rique).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0213_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en signaux et information ?",
                'options': ["Signal analogique", "Signal numÃ©rique", "Information binaire", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en signaux et information comprennent le signal analogique, qui est un signal continu qui peut prendre une infinitÃ© de valeurs, le signal numÃ©rique, qui est un signal discret qui ne peut prendre que des valeurs spÃ©cifiques, et l'information binaire, qui est une forme d'information codÃ©e en utilisant deux Ã©tats (0 et 1) pour reprÃ©senter les donnÃ©es."
            },
            {
                'id': "0213_2",
                'type': "vrai-faux",
                'question': "Un signal analogique est un signal continu qui peut prendre une infinitÃ© de valeurs. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un signal analogique est caractÃ©risÃ© par sa continuitÃ© et sa capacitÃ© Ã  prendre une infinitÃ© de valeurs dans une plage donnÃ©e, contrairement Ã  un signal numÃ©rique qui est discret et ne peut prendre que des valeurs spÃ©cifiques."
            },
            {
                'id': "0213_3",
                'type': "qcm",
                'question': "Quel est un exemple de signal numÃ©rique ?",
                'options': ["Signal de tÃ©lÃ©vision analogique", "Signal de tÃ©lÃ©phone analogique", "Signal de donnÃ©es numÃ©riques", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Signal de donnÃ©es numÃ©riques",
                'explanation': "Un signal de donnÃ©es numÃ©riques est un exemple de signal numÃ©rique, qui est utilisÃ© pour transmettre des informations sous forme de donnÃ©es codÃ©es en binaire (0 et 1) dans les systÃ¨mes informatiques et de communication."
            },
            {
                'id': "0213_4",
                'type': "vrai-faux",
                'question': "L'information binaire est une forme d'information codÃ©e en utilisant deux Ã©tats (0 et 1) pour reprÃ©senter les donnÃ©es. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'information binaire est une mÃ©thode de codage qui utilise deux Ã©tats (0 et 1) pour reprÃ©senter les donnÃ©es, et est largement utilisÃ©e dans les systÃ¨mes informatiques et de communication pour stocker et transmettre des informations de maniÃ¨re efficace et fiable."
            },
            {
                'id': "0213_5",
                'type': "qcm",
                'question': "Quel est un exemple de signal qui n'est pas numÃ©rique ?",
                'options': ["Signal de tÃ©lÃ©vision analogique", "Signal de tÃ©lÃ©phone analogique", "Signal de donnÃ©es numÃ©riques", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Les signaux de tÃ©lÃ©vision analogique et de tÃ©lÃ©phone analogique sont des exemples de signaux analogiques, tandis que le signal de donnÃ©es numÃ©riques est un exemple de signal numÃ©rique."
            },
            {
                'id': "0213_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les signaux analogiques et numÃ©riques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les signaux analogiques et numÃ©riques sont diffÃ©rents. Les signaux analogiques sont continus et peuvent prendre une infinitÃ© de valeurs, tandis que les signaux numÃ©riques sont discrets et ne peuvent prendre que des valeurs spÃ©cifiques."
            },
            {
                'id': "0213_7",
                'type': "qcm",
                'question': "Quel est un exemple d'information qui peut Ãªtre reprÃ©sentÃ©e en binaire ?",
                'options': ["Texte", "Image", "Son", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (texte, image, son) peuvent Ãªtre reprÃ©sentÃ©s en binaire, car les donnÃ©es numÃ©riques sont codÃ©es en utilisant des sÃ©quences de 0 et 1 pour reprÃ©senter diffÃ©rentes formes d'information dans les systÃ¨mes informatiques."
            },
            {
                'id': "0213_8",
                'type': "vrai-faux",
                'question': "Tous les systÃ¨mes de communication utilisent des signaux numÃ©riques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, bien que de nombreux systÃ¨mes de communication modernes utilisent des signaux numÃ©riques pour leur efficacitÃ© et leur fiabilitÃ©, il existe encore des systÃ¨mes qui utilisent des signaux analogiques, notamment dans les domaines de la radio, de la tÃ©lÃ©vision, et de certaines formes de tÃ©lÃ©phonie."
            },
        ]
    ),
    (
        "0214",
        "Transmission d'information (cÃ¢bles, sans fil).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0214_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en transmission d'information ?",
                'options': ["CÃ¢bles", "Sans fil", "RÃ©seaux de communication", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en transmission d'information comprennent les cÃ¢bles, qui sont des supports physiques pour la transmission de donnÃ©es, les technologies sans fil, qui permettent la transmission de donnÃ©es sans l'utilisation de cÃ¢bles, et les rÃ©seaux de communication, qui sont des systÃ¨mes interconnectÃ©s pour la transmission de donnÃ©es entre les appareils."
            },
            {
                'id': "0214_2",
                'type': "vrai-faux",
                'question': "La transmission d'information peut se faire Ã  la fois par cÃ¢bles et sans fil. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la transmission d'information peut se faire Ã  la fois par cÃ¢bles et sans fil, en fonction des besoins et des contraintes du systÃ¨me de communication."
            },
            {
                'id': "0214_3",
                'type': "qcm",
                'question': "Quels sont des exemples de virus informatique ?",
                'options': ["Cheval de Troie", "Ransomware", "Spyware", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Le cheval de Troie, le ransomware, et le spyware sont tous des exemples de types courants de virus informatiques qui peuvent causer des dommages aux ordinateurs et aux rÃ©seaux."
            },
            {
                'id': "0214_4",
                'type': "vrai-faux",
                'question': "Un pare-feu est un dispositif de sÃ©curitÃ© qui surveille et contrÃ´le le trafic rÃ©seau entrant et sortant pour protÃ©ger contre les menaces. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un pare-feu est un dispositif de sÃ©curitÃ© qui surveille et contrÃ´le le trafic rÃ©seau entrant et sortant pour protÃ©ger contre les menaces."
            },
            {
                'id': "0214_5",
                'type': "qcm",
                'question': "Quel est un exemple de technologie de transmission sans fil ?",
                'options': ["Wi-Fi", "Ethernet", "Fibre optique", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Wi-Fi",
                'explanation': "Le Wi-Fi est un exemple de technologie de transmission sans fil qui permet aux appareils de se connecter Ã  Internet et Ã  d'autres rÃ©seaux sans l'utilisation de cÃ¢bles."
            },
            {
                'id': "0214_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes technologies de transmission d'information. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes technologies de transmission d'information en termes de leur mÃ©thode de transmission, de leur portÃ©e, de leur vitesse, et de leur utilisation dans diffÃ©rents contextes."
            },
            {
                'id': "0214_7",
                'type': "qcm",
                'question': "Quel est un exemple de technologie de transmission par cÃ¢ble ?",
                'options': ["Ethernet", "Wi-Fi", "Bluetooth", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Ethernet",
                'explanation': "Ethernet est un exemple de technologie de transmission par cÃ¢ble qui est largement utilisÃ©e pour connecter des appareils Ã  un rÃ©seau local (LAN) Ã  l'aide de cÃ¢bles physiques."
            },
            {
                'id': "0214_8",
                'type': "vrai-faux",
                'question': "Tous les systÃ¨mes de communication utilisent la mÃªme technologie de transmission d'information. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, diffÃ©rents systÃ¨mes de communication peuvent utiliser diffÃ©rentes technologies de transmission d'information en fonction des besoins spÃ©cifiques du systÃ¨me, tels que la portÃ©e, la vitesse, la sÃ©curitÃ©, et les contraintes environnementales."
            },
        ]
    ),
    (
        "0215",
        "Environnement numÃ©rique de travail.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0215_1",
                'type': "qcm",
                'question': "Quels sont des Ã©lÃ©ments d'un environnement numÃ©rique de travail ?",
                'options': ["Ordinateur", "Logiciels de productivitÃ©", "Connexion Internet", "Tous les Ã©lÃ©ments mentionnÃ©s"],
                'correct_option': "Tous les Ã©lÃ©ments mentionnÃ©s",
                'explanation': "Un environnement numÃ©rique de travail comprend gÃ©nÃ©ralement un ordinateur, des logiciels de productivitÃ© tels que des suites bureautiques, et une connexion Internet pour accÃ©der Ã  des ressources en ligne et collaborer avec d'autres."
            },
            {
                'id': "0215_2",
                'type': "vrai-faux",
                'question': "Un environnement numÃ©rique de travail est un espace physique oÃ¹ les gens travaillent. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, un environnement numÃ©rique de travail est un espace virtuel qui comprend les outils et les ressources numÃ©riques nÃ©cessaires pour accomplir des tÃ¢ches professionnelles, et n'est pas limitÃ© Ã  un espace physique."
            },
            {
                'id': "0215_3",
                'type': "qcm",
                'question': "Quel est un exemple de logiciel de productivitÃ© utilisÃ© dans un environnement numÃ©rique de travail ?",
                'options': ["Microsoft Word", "Adobe Photoshop", "Google Chrome", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Microsoft Word",
                'explanation': "Microsoft Word est un exemple de logiciel de productivitÃ© largement utilisÃ© dans les environnements numÃ©riques de travail pour la crÃ©ation et l'Ã©dition de documents texte."
            },
            {
                'id': "0215_4",
                'type': "vrai-faux",
                'question': "Une connexion Internet n'est pas nÃ©cessaire pour travailler dans un environnement numÃ©rique de travail. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, une connexion Internet est souvent essentielle pour travailler efficacement dans un environnement numÃ©rique de travail, car elle permet d'accÃ©der Ã  des ressources en ligne, de collaborer avec d'autres, et d'utiliser des outils basÃ©s sur le cloud."
            },
            {
                'id': "0215_5",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de collaboration en ligne utilisÃ© dans un environnement numÃ©rique de travail ?",
                'options': ["Google Drive", "Microsoft Excel", "Adobe Illustrator", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Google Drive",
                'explanation': "Google Drive est un exemple d'outil de collaboration en ligne qui permet aux utilisateurs de stocker, partager, et collaborer sur des fichiers et des documents en temps rÃ©el."
            },
            {
                'id': "0215_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents Ã©lÃ©ments d'un environnement numÃ©rique de travail. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents Ã©lÃ©ments d'un environnement numÃ©rique de travail en termes de leur fonction, de leur utilisation, et de leur importance dans le processus de travail numÃ©rique."
            },
            {
                'id': "0215_7",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui peut Ãªtre utilisÃ© dans un environnement numÃ©rique de travail ?",
                'options': ["Ordinateur de bureau", "Tablette", "Smartphone", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (ordinateur de bureau, tablette, smartphone) sont des types d'appareils couramment utilisÃ©s dans les environnements numÃ©riques de travail pour accomplir diverses tÃ¢ches professionnelles."
            },
            {
                'id': "0215_8",
                'type': "vrai-faux",
                'question': "Tous les environnements numÃ©riques de travail sont les mÃªmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les environnements numÃ©riques de travail peuvent varier considÃ©rablement en fonction des outils, des ressources, et des technologies utilisÃ©s, ainsi que des besoins spÃ©cifiques de l'utilisateur ou de l'organisation."
            },
        ]
    ),
    (
        "0216",
        "RÃ©seaux informatiques au collÃ¨ge.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0216_1",
                'type': "qcm",
                'question': "Quels sont des Ã©lÃ©ments d'un rÃ©seau informatique au collÃ¨ge ?",
                'options': ["Routeur", "Switch", "CÃ¢bles Ethernet", "Tous les Ã©lÃ©ments mentionnÃ©s"],
                'correct_option': "Tous les Ã©lÃ©ments mentionnÃ©s",
                'explanation': "Un rÃ©seau informatique au collÃ¨ge peut inclure des Ã©lÃ©ments tels que des routeurs pour diriger le trafic de donnÃ©es, des switches pour connecter les appareils, et des cÃ¢bles Ethernet pour la transmission de donnÃ©es."
            },
            {
                'id': "0216_2",
                'type': "vrai-faux",
                'question': "Un rÃ©seau informatique au collÃ¨ge est uniquement utilisÃ© pour accÃ©der Ã  Internet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, un rÃ©seau informatique au collÃ¨ge peut Ãªtre utilisÃ© pour une variÃ©tÃ© de fonctions, y compris l'accÃ¨s Ã  Internet, le partage de ressources telles que les imprimantes, et la communication entre les appareils sur le rÃ©seau."
            },
            {
                'id': "0216_3",
                'type': "qcm",
                'question': "Quel est un exemple de protocole de communication utilisÃ© dans un rÃ©seau informatique au collÃ¨ge ?",
                'options': ["HTTP", "FTP", "TCP/IP", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "HTTP, FTP, et TCP/IP sont tous des exemples de protocoles de communication qui peuvent Ãªtre utilisÃ©s dans un rÃ©seau informatique au collÃ¨ge pour permettre la transmission de donnÃ©es entre les appareils et l'accÃ¨s Ã  Internet."
            },
            {
                'id': "0216_4",
                'type': "vrai-faux",
                'question': "Un pare-feu est un dispositif de sÃ©curitÃ© qui peut Ãªtre utilisÃ© dans un rÃ©seau informatique au collÃ¨ge pour protÃ©ger contre les menaces. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un pare-feu est un dispositif de sÃ©curitÃ© qui peut Ãªtre utilisÃ© dans un rÃ©seau informatique au collÃ¨ge pour surveiller et contrÃ´ler le trafic rÃ©seau afin de protÃ©ger contre les menaces potentielles."
            },
            {
                'id': "0216_5",
                'type': "qcm",
                'question': "Quel est un exemple d'appareil qui peut Ãªtre connectÃ© Ã  un rÃ©seau informatique au collÃ¨ge ?",
                'options': ["Ordinateur de bureau", "Tablette", "Imprimante rÃ©seau", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (ordinateur de bureau, tablette, imprimante rÃ©seau) sont des types d'appareils couramment connectÃ©s Ã  un rÃ©seau informatique au collÃ¨ge pour accomplir diverses tÃ¢ches Ã©ducatives et administratives."
            },
            {
                'id': "0216_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents Ã©lÃ©ments d'un rÃ©seau informatique au collÃ¨ge. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents Ã©lÃ©ments d'un rÃ©seau informatique au collÃ¨ge en termes de leur fonction, de leur utilisation, et de leur importance dans le fonctionnement global du rÃ©seau."
            },
            {
                'id': "0216_7",
                'type': "qcm",
                'question': "Quel est un exemple de technologie de transmission utilisÃ©e dans un rÃ©seau informatique au collÃ¨ge ?",
                'options': ["Ethernet", "Wi-Fi", "Bluetooth", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Ethernet, Wi-Fi, et Bluetooth sont tous des technologies de transmission qui peuvent Ãªtre utilisÃ©es dans un rÃ©seau informatique au collÃ¨ge pour permettre la communication entre les appareils et l'accÃ¨s Ã  Internet."
            },
            {
                'id': "0216_8",
                'type': "vrai-faux",
                'question': "Tous les rÃ©seaux informatiques au collÃ¨ge sont les mÃªmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les rÃ©seaux informatiques au collÃ¨ge peuvent varier considÃ©rablement en fonction des technologies utilisÃ©es, des besoins spÃ©cifiques du collÃ¨ge, et des ressources disponibles."
            },
        ]
    ),
    (
        "0217",
        "SÃ©curitÃ© et Ã©thique numÃ©rique.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0210_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en sÃ©curitÃ© et Ã©thique numÃ©rique ?",
                'options': ["ConfidentialitÃ©", "SÃ©curitÃ© des donnÃ©es", "Comportement en ligne responsable", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en sÃ©curitÃ© et Ã©thique numÃ©rique comprennent la confidentialitÃ©, qui protÃ¨ge les informations personnelles, la sÃ©curitÃ© des donnÃ©es, qui assure la protection contre les cyberattaques, et le comportement en ligne responsable, qui guide les interactions sur Internet."
            },
            {
                'id': "0210_2",
                'type': "vrai-faux",
                'question': "La confidentialitÃ© est un concept de base en sÃ©curitÃ© et Ã©thique numÃ©rique. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la confidentialitÃ© est un concept de base en sÃ©curitÃ© et Ã©thique numÃ©rique qui protÃ¨ge les informations personnelles des utilisateurs."
            },
            {
                'id': "0210_3",
                'type': "qcm",
                'question': "Quel est un exemple de comportement en ligne responsable ?",
                'options': ["Partager des informations personnelles", "Respecter la vie privÃ©e des autres", "Ignorer les rÃ¨gles de sÃ©curitÃ©", "Tous les comportements mentionnÃ©s"],
                'correct_option': "Respecter la vie privÃ©e des autres",
                'explanation': "Respecter la vie privÃ©e des autres est un exemple de comportement en ligne responsable, qui contribue Ã  crÃ©er un environnement numÃ©rique sÃ»r et respectueux."
            },
            {
                'id': "0210_4",
                'type': "vrai-faux",
                'question': "La sÃ©curitÃ© des donnÃ©es n'est pas importante en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la sÃ©curitÃ© des donnÃ©es est extrÃªmement importante en ligne pour protÃ©ger les informations personnelles et sensibles contre les cyberattaques et les violations de donnÃ©es."
            },
            {
                'id': "0210_5",
                'type': "qcm",
                'question': "Quel est un exemple de menace en ligne ?",
                'options': ["Phishing", "Ransomware", "Spyware", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Le phishing, le ransomware, et le spyware sont tous des exemples de menaces en ligne qui peuvent causer des dommages aux utilisateurs et Ã  leurs appareils."
            },
            {
                'id': "0210_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes menaces en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes menaces en ligne en termes de leur nature, de leur mÃ©thode d'attaque, et de leur impact potentiel sur les utilisateurs."
            },
            {
                'id': "0210_7",
                'type': "qcm",
                'question': "Quel est un exemple de mesure de sÃ©curitÃ© que les utilisateurs peuvent prendre pour se protÃ©ger en ligne ?",
                'options': ["Utiliser des mots de passe forts", "Mettre Ã  jour rÃ©guliÃ¨rement les logiciels", "Ã‰viter de cliquer sur des liens suspects", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Utiliser des mots de passe forts, mettre Ã  jour rÃ©guliÃ¨rement les logiciels, et Ã©viter de cliquer sur des liens suspects sont tous des mesures de sÃ©curitÃ© importantes que les utilisateurs peuvent prendre pour se protÃ©ger en ligne."
            },
            {
                'id': "0210_8",
                'type': "vrai-faux",
                'question': "Tous les utilisateurs en ligne sont conscients des risques de sÃ©curitÃ© et agissent de maniÃ¨re responsable. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les utilisateurs en ligne ne sont pas conscients des risques de sÃ©curitÃ© et ne agissent pas toujours de maniÃ¨re responsable, ce qui souligne l'importance de l'Ã©ducation Ã  la sÃ©curitÃ© et Ã  l'Ã©thique numÃ©rique pour promouvoir un comportement en ligne sÃ»r et responsable."
            },
        ]
    ),
    (
        "0211",
        "VÃ©rification et tests d'un prototype.",
        "Technologie",
        "6eme",
        [
            {
               'id': "0211_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en vÃ©rification et tests d'un prototype ?",
                'options': ["Test unitaire", "Test d'intÃ©gration", "Test systÃ¨me", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Les concepts de base en vÃ©rification et tests d'un prototype incluent le test unitaire, le test d'intÃ©gration, et le test systÃ¨me."
            },
            {
                'id': "0211_2",
                'type': "vrai-faux",
                'question': "Le test unitaire est un type de test qui vÃ©rifie le fonctionnement d'une unitÃ© individuelle d'un prototype. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le test unitaire est un type de test qui vÃ©rifie le fonctionnement d'une unitÃ© individuelle d'un prototype pour s'assurer qu'elle fonctionne correctement de maniÃ¨re isolÃ©e."
            },
            {
                'id': "0211_3",
                'type': "qcm",
                'question': "Quel est un exemple de test d'intÃ©gration ?",
                'options': ["Tester une fonction individuelle", "Tester l'interaction entre plusieurs unitÃ©s", "Tester l'ensemble du systÃ¨me", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tester l'interaction entre plusieurs unitÃ©s",
                'explanation': "Le test d'intÃ©gration est un type de test qui vÃ©rifie l'interaction entre plusieurs unitÃ©s d'un prototype pour s'assurer qu'elles fonctionnent correctement ensemble."
            },
            {
                'id': "0211_4",
                'type': "vrai-faux",
                'question': "Le test systÃ¨me est un type de test qui vÃ©rifie le fonctionnement de l'ensemble du prototype. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le test systÃ¨me est un type de test qui vÃ©rifie le fonctionnement de l'ensemble du prototype pour s'assurer qu'il rÃ©pond aux exigences spÃ©cifiÃ©es et fonctionne correctement dans son environnement prÃ©vu."
            },
            {
                'id': "0211_5",
                'type': "qcm",
                'question': "Quel est un exemple de test qui n'est pas utilisÃ© dans la vÃ©rification d'un prototype ?",
                'options': ["Test unitaire", "Test d'intÃ©gration", "Test de performance", "Test de cuisine"],
                'correct_option': "Test de cuisine",
                'explanation': "Le test de cuisine n'est pas un type de test utilisÃ© dans la vÃ©rification d'un prototype, tandis que le test unitaire, le test d'intÃ©gration, et le test de performance sont tous des types de tests couramment utilisÃ©s pour vÃ©rifier diffÃ©rents aspects d'un prototype."
            },
            {
                'id': "0211_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents types de tests utilisÃ©s dans la vÃ©rification d'un prototype. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents types de tests utilisÃ©s dans la vÃ©rification d'un prototype en termes de leur objectif, de leur portÃ©e, et de leur mÃ©thodologie."
            },
            {
                'id': "0211_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut Ãªtre utilisÃ© pour effectuer des tests sur un prototype ?",
                'options': ["JUnit", "Selenium", "Postman", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "JUnit, Selenium, et Postman sont tous des outils qui peuvent Ãªtre utilisÃ©s pour effectuer des tests sur un prototype, chacun ayant des fonctionnalitÃ©s spÃ©cifiques adaptÃ©es Ã  diffÃ©rents types de tests."
            },
            {
                'id': "0211_8",
                'type': "vrai-faux",
                'question': "Tous les tests effectuÃ©s sur un prototype sont les mÃªmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les tests effectuÃ©s sur un prototype ne sont pas les mÃªmes, car ils peuvent varier en fonction de leur objectif, de leur portÃ©e, et de leur mÃ©thodologie."
            },
        ]
    ),
    (
        "0212",
        "DÃ©placement et freinage d'un objet (exemples : vÃ©lo, voiture, ascenseur).",
        "Technologie",
        "6eme",
        [
            {
                'id': "0212_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en dÃ©placement et freinage d'un objet ?",
                'options': ["Force de friction", "Inertie", "AccÃ©lÃ©ration", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en dÃ©placement et freinage d'un objet comprennent la force de friction, qui s'oppose au mouvement, l'inertie, qui est la tendance d'un objet Ã  rester en mouvement ou au repos, et l'accÃ©lÃ©ration, qui est le changement de vitesse d'un objet."
            },
            {
                'id': "0212_2",
                'type': "vrai-faux",
                'question': "La force de friction est une force qui s'oppose au mouvement d'un objet. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la force de friction est une force qui s'oppose au mouvement d'un objet, ralentissant ou empÃªchant son dÃ©placement."
            },
            {
                'id': "0212_3",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui utilise la force de friction pour se dÃ©placer ?",
                'options': ["VÃ©lo", "Voiture", "Ascenseur", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (vÃ©lo, voiture, ascenseur) utilisent la force de friction pour se dÃ©placer, que ce soit entre les pneus et la route, ou entre les cÃ¢bles et les poulies dans le cas de l'ascenseur."
            },
            {
                'id': "0212_4",
                'type': "vrai-faux",
                'question': "L'inertie est la tendance d'un objet Ã  rester en mouvement ou au repos. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'inertie est un concept physique qui dÃ©crit la tendance d'un objet Ã  rester en mouvement ou au repos Ã  moins qu'une force extÃ©rieure ne soit appliquÃ©e pour changer son Ã©tat de mouvement."
            },
            {
                'id': "0212_5",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui utilise l'inertie pour se dÃ©placer ?",
                'options': ["VÃ©lo", "Voiture", "Ascenseur", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (vÃ©lo, voiture, ascenseur) utilisent l'inertie pour se dÃ©placer, car ils continuent Ã  se dÃ©placer Ã  moins qu'une force extÃ©rieure ne soit appliquÃ©e pour les arrÃªter ou les ralentir."
            },
            {
                'id': "0212_6",
                'type': "vrai-faux",
                'question': "L'accÃ©lÃ©ration est le changement de vitesse d'un objet. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'accÃ©lÃ©ration est un concept physique qui dÃ©crit le changement de vitesse d'un objet au fil du temps, que ce soit une augmentation ou une diminution de sa vitesse."
            },
            {
                'id': "0212_7",
                'type': "qcm",
                'question': "Quel est un exemple d'objet qui utilise l'accÃ©lÃ©ration pour se dÃ©placer ?",
                'options': ["VÃ©lo", "Voiture", "Ascenseur", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (vÃ©lo, voiture, ascenseur) utilisent l'accÃ©lÃ©ration pour se dÃ©placer, car ils peuvent augmenter ou diminuer leur vitesse en fonction des forces appliquÃ©es."
            },
            {
                'id': "0212_8",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents concepts de dÃ©placement et freinage d'un objet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents concepts de dÃ©placement et freinage d'un objet en termes de leur nature, de leur fonction, et de leur impact sur le mouvement d'un objet."
            },
        ]
    ),
    (
        "0213",
        "Conception.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0213_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en conception ?",
                'options': ["Signal analogique", "Signal numÃ©rique", "Information binaire", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en conception comprennent le signal analogique, qui est un signal continu qui peut prendre une infinitÃ© de valeurs, le signal numÃ©rique, qui est un signal discret qui ne peut prendre que des valeurs spÃ©cifiques, et l'information binaire, qui est une forme d'information codÃ©e en utilisant deux Ã©tats (0 et 1) pour reprÃ©senter les donnÃ©es."
            },
            {
                'id': "0213_2",
                'type': "vrai-faux",
                'question': "Un signal analogique est caractÃ©risÃ© par sa continuitÃ© et sa capacitÃ© Ã  prendre une infinitÃ© de valeurs dans une plage donnÃ©e. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un signal analogique est caractÃ©risÃ© par sa continuitÃ© et sa capacitÃ© Ã  prendre une infinitÃ© de valeurs dans une plage donnÃ©e, contrairement Ã  un signal numÃ©rique qui est discret et ne peut prendre que des valeurs spÃ©cifiques."
            },
            {
                'id': "0213_3",
                'type': "qcm",
                'question': "Quel est un exemple de signal numÃ©rique ?",
                'options': ["Signal de tÃ©lÃ©vision analogique", "Signal de tÃ©lÃ©phone analogique", "Signal de donnÃ©es numÃ©riques", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Signal de donnÃ©es numÃ©riques",
                'explanation': "Un signal de donnÃ©es numÃ©riques est un exemple de signal numÃ©rique, qui est utilisÃ© pour transmettre des informations sous forme de donnÃ©es codÃ©es en binaire (0 et 1) dans les systÃ¨mes informatiques et de communication."
            },
            {
                'id': "0213_4",
                'type': "vrai-faux",
                'question': "L'information binaire est une forme d'information codÃ©e en utilisant deux Ã©tats (0 et 1) pour reprÃ©senter les donnÃ©es. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'information binaire est une forme d'information codÃ©e en utilisant deux Ã©tats (0 et 1) pour reprÃ©senter les donnÃ©es."
            },
            {
                'id': "0213_5",
                'type': "qcm",
                'question': "Quel est un exemple d'information qui peut Ãªtre reprÃ©sentÃ©e en binaire ?",
                'options': ["Texte", "Image", "Son", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (texte, image, son) peuvent Ãªtre reprÃ©sentÃ©s en binaire, car les donnÃ©es numÃ©riques sont codÃ©es en utilisant des sÃ©quences de 0 et 1 pour reprÃ©senter diffÃ©rentes formes d'information dans les systÃ¨mes informatiques."
            },
            {
                'id': "0213_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les signaux analogiques et numÃ©riques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les signaux analogiques et numÃ©riques en termes de leur nature, de leur fonction, et de leur utilisation dans diffÃ©rents contextes de communication et de traitement de l'information."
            },
            {
                'id': "0213_7",
                'type': "qcm",
                'question': "Quel est un exemple de technologie qui utilise des signaux numÃ©riques ?",
                'options': ["TÃ©lÃ©vision analogique", "TÃ©lÃ©phone analogique", "Ordinateur", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Ordinateur",
                'explanation': "Un ordinateur est un exemple de technologie qui utilise des signaux numÃ©riques pour traiter et transmettre des donnÃ©es, tandis que la tÃ©lÃ©vision analogique et le tÃ©lÃ©phone analogique utilisent des signaux analogiques."
            },
            {
                'id': "0213_8",
                'type': "vrai-faux",
                'question': "Tous les systÃ¨mes de communication utilisent des signaux numÃ©riques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les systÃ¨mes de communication n'utilisent pas des signaux numÃ©riques, car certains systÃ¨mes peuvent encore utiliser des signaux analogiques en fonction de leurs besoins spÃ©cifiques et de leur contexte d'utilisation."
            },
        ]
    ),
    (
        "0214",
        "DÃ©finir un projet.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0214_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en dÃ©finition d'un projet ?",
                'options': ["Objectifs du projet", "Ressources nÃ©cessaires", "Planification du projet", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en dÃ©finition d'un projet comprennent les objectifs du projet, qui dÃ©finissent ce que le projet vise Ã  accomplir, les ressources nÃ©cessaires, qui incluent les personnes, les matÃ©riaux, et les finances requises pour rÃ©aliser le projet, et la planification du projet, qui implique l'organisation des tÃ¢ches et des Ã©chÃ©ances pour atteindre les objectifs du projet."
            },
            {
                'id': "0214_2",
                'type': "vrai-faux",
                'question': "Les objectifs du projet sont des Ã©lÃ©ments clÃ©s dans la dÃ©finition d'un projet. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, les objectifs du projet sont des Ã©lÃ©ments clÃ©s dans la dÃ©finition d'un projet, car ils fournissent une direction claire et mesurable pour ce que le projet vise Ã  accomplir."
            },
            {
                'id': "0214_3",
                'type': "qcm",
                'question': "Quel est un exemple de ressource nÃ©cessaire pour un projet ?",
                'options': ["Personnel", "MatÃ©riaux", "Finances", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (personnel, matÃ©riaux, finances) sont des types de ressources nÃ©cessaires pour un projet, car ils sont essentiels pour rÃ©aliser les tÃ¢ches et atteindre les objectifs du projet."
            },
            {
                'id': "0214_4",
                'type': "vrai-faux",
                'question': "La planification du projet n'est pas importante dans la dÃ©finition d'un projet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la planification du projet est trÃ¨s importante dans la dÃ©finition d'un projet, car elle permet d'organiser les tÃ¢ches, de gÃ©rer les ressources, et de respecter les Ã©chÃ©ances pour atteindre les objectifs du projet de maniÃ¨re efficace."
            },
            {
                'id': "0214_5",
                'type': "qcm",
                'question': "Quel est un exemple de type de projet ?",
                'options': ["Projet de construction", "Projet de dÃ©veloppement logiciel", "Projet de recherche scientifique", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (projet de construction, projet de dÃ©veloppement logiciel, projet de recherche scientifique) sont des types de projets qui peuvent Ãªtre dÃ©finis en fonction de leurs objectifs, de leurs ressources, et de leur planification spÃ©cifiques."
            },
            {
                'id': "0214_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents types de projets. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents types de projets en termes de leur nature, de leurs objectifs, de leurs ressources, et de leur planification."
            },
            {
                'id': "0214_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut Ãªtre utilisÃ© pour la planification d'un projet ?",
                'options': ["GanttProject", "Trello", "Microsoft Project", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "GanttProject, Trello, et Microsoft Project sont tous des outils qui peuvent Ãªtre utilisÃ©s pour la planification d'un projet, chacun offrant des fonctionnalitÃ©s spÃ©cifiques pour aider Ã  organiser les tÃ¢ches, gÃ©rer les ressources, et suivre l'avancement du projet."
            },
            {
                'id': "0214_8",
                'type': "vrai-faux",
                'question': "Tous les projets sont les mÃªmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les projets ne sont pas les mÃªmes, car ils peuvent varier considÃ©rablement en fonction de leur nature, de leurs objectifs, de leurs ressources, et de leur planification."
            },
        ]
    ),
    (
        "0215",
        "Ã‰volution des besoins et innovations techniques.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0215_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en Ã©volution des besoins et innovations techniques ?",
                'options': ["Ã‰volution des besoins", "Innovation technique", "Impact de l'innovation sur la sociÃ©tÃ©", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en Ã©volution des besoins et innovations techniques comprennent l'Ã©volution des besoins, qui fait rÃ©fÃ©rence aux changements dans les exigences et les attentes des utilisateurs au fil du temps, l'innovation technique, qui implique le dÃ©veloppement de nouvelles technologies ou l'amÃ©lioration de technologies existantes pour rÃ©pondre Ã  ces besoins changeants, et l'impact de l'innovation sur la sociÃ©tÃ©, qui examine comment les innovations techniques peuvent influencer la vie quotidienne, les comportements, et les structures sociales."
            },
            {
                'id': "0215_2",
                'type': "vrai-faux",
                'question': "L'Ã©volution des besoins fait rÃ©fÃ©rence aux changements dans les exigences et les attentes des utilisateurs au fil du temps. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'Ã©volution des besoins fait rÃ©fÃ©rence aux changements dans les exigences et les attentes des utilisateurs au fil du temps, ce qui peut conduire Ã  la nÃ©cessitÃ© d'innovations techniques pour rÃ©pondre Ã  ces nouveaux besoins."
            },
            {
                'id': "0215_3",
                'type': "qcm",
                'question': "Quel est un exemple d'innovation technique ?",
                'options': ["Internet", "Smartphone", "Impression 3D", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Internet, le smartphone, et l'impression 3D sont tous des exemples d'innovations techniques qui ont eu un impact significatif sur la sociÃ©tÃ© en rÃ©pondant Ã  l'Ã©volution des besoins des utilisateurs et en transformant la maniÃ¨re dont les gens communiquent, travaillent, et crÃ©ent."
            },
            {
                'id': "0215_4",
                'type': "vrai-faux",
                'question': "L'innovation technique n'a pas d'impact sur la sociÃ©tÃ©. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, l'innovation technique a souvent un impact significatif sur la sociÃ©tÃ© en modifiant les comportements, les interactions, et les structures sociales, ainsi qu'en crÃ©ant de nouvelles opportunitÃ©s et dÃ©fis pour les individus et les communautÃ©s."
            },
            {
                'id': "0215_5",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de collaboration en ligne qui a Ã©tÃ© dÃ©veloppÃ© en rÃ©ponse Ã  l'Ã©volution des besoins des utilisateurs ?",
                'options': ["Google Drive", "Microsoft Teams", "Slack", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Google Drive, Microsoft Teams, et Slack sont tous des exemples d'outils de collaboration en ligne qui ont Ã©tÃ© dÃ©veloppÃ©s en rÃ©ponse Ã  l'Ã©volution des besoins des utilisateurs pour faciliter la communication, la collaboration, et le partage de ressources dans les environnements de travail et d'apprentissage Ã  distance."
            },
            {
                'id': "0215_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes innovations techniques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes innovations techniques en termes de leur nature, de leur fonction, et de leur impact sur la sociÃ©tÃ©."
            },
            {
                'id': "0215_7",
                'type': "qcm",
                'question': "Quel est un exemple d'innovation technique qui a eu un impact significatif sur la sociÃ©tÃ© ?",
                'options': ["Internet", "Smartphone", "Impression 3D", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Internet, le smartphone, et l'impression 3D sont tous des exemples d'innovations techniques qui ont eu un impact significatif sur la sociÃ©tÃ© en rÃ©pondant Ã  l'Ã©volution des besoins des utilisateurs et en transformant la maniÃ¨re dont les gens communiquent, travaillent, et crÃ©ent."
            },
            {
                'id': "0215_8",
                'type': "vrai-faux",
                'question': "Tous les utilisateurs sont conscients de l'Ã©volution des besoins et des innovations techniques. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les utilisateurs ne sont pas nÃ©cessairement conscients de l'Ã©volution des besoins et des innovations techniques, ce qui souligne l'importance de l'Ã©ducation et de la sensibilisation pour aider les individus Ã  comprendre ces concepts et Ã  s'adapter aux changements technologiques."
            },
        ]
    ),
    (
        "0216",
        "RÃ©alisation et dÃ©marches.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0216_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en rÃ©alisation et dÃ©marches ?",
                'options': ["RÃ©alisation d'un projet", "DÃ©marche de rÃ©solution de problÃ¨mes", "Collaboration en Ã©quipe", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en rÃ©alisation et dÃ©marches comprennent la rÃ©alisation d'un projet, qui implique la mise en Å“uvre des Ã©tapes nÃ©cessaires pour concrÃ©tiser une idÃ©e ou un plan, la dÃ©marche de rÃ©solution de problÃ¨mes, qui est un processus structurÃ© pour identifier, analyser, et rÃ©soudre des problÃ¨mes, et la collaboration en Ã©quipe, qui est essentielle pour travailler efficacement avec d'autres personnes pour atteindre des objectifs communs."
            },
            {
                'id': "0216_2",
                'type': "vrai-faux",
                'question': "La rÃ©alisation d'un projet implique la mise en Å“uvre des Ã©tapes nÃ©cessaires pour concrÃ©tiser une idÃ©e ou un plan. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la rÃ©alisation d'un projet implique la mise en Å“uvre des Ã©tapes nÃ©cessaires pour concrÃ©tiser une idÃ©e ou un plan, ce qui peut inclure la planification, l'exÃ©cution, et l'Ã©valuation du projet."
            },
            {
                'id': "0216_3",
                'type': "qcm",
                'question': "Quel est un exemple de technologie de transmission utilisÃ©e dans un rÃ©seau informatique au collÃ¨ge ?",
                'options': ["Ethernet", "Wi-Fi", "Bluetooth", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Ethernet, Wi-Fi, et Bluetooth sont tous des technologies de transmission qui peuvent Ãªtre utilisÃ©es dans un rÃ©seau informatique au collÃ¨ge pour permettre la communication entre les appareils et l'accÃ¨s Ã  Internet."
            },
            {
                'id': "0216_4",
                'type': "vrai-faux",
                'question': "Un pare-feu est un dispositif de sÃ©curitÃ© qui peut Ãªtre utilisÃ© dans un rÃ©seau informatique au collÃ¨ge pour protÃ©ger contre les menaces. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, un pare-feu est un dispositif de sÃ©curitÃ© qui peut Ãªtre utilisÃ© dans un rÃ©seau informatique au collÃ¨ge pour protÃ©ger contre les menaces en filtrant le trafic rÃ©seau et en bloquant les connexions non autorisÃ©es."
            },
            {
                'id': "0216_5",
                'type': "qcm",
                'question': "Quel est un exemple de protocole de communication utilisÃ© dans un rÃ©seau informatique au collÃ¨ge ?",
                'options': ["TCP/IP", "HTTP", "FTP", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "TCP/IP, HTTP, et FTP sont tous des protocoles de communication qui peuvent Ãªtre utilisÃ©s dans un rÃ©seau informatique au collÃ¨ge pour permettre la transmission de donnÃ©es et l'accÃ¨s Ã  des ressources en ligne."
            },
            {
                'id': "0216_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes technologies de transmission utilisÃ©es dans un rÃ©seau informatique au collÃ¨ge. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes technologies de transmission utilisÃ©es dans un rÃ©seau informatique au collÃ¨ge en termes de leur portÃ©e, de leur vitesse, et de leur utilisation spÃ©cifique."
            },
            {
                'id': "0216_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut Ãªtre utilisÃ© pour surveiller et gÃ©rer un rÃ©seau informatique au collÃ¨ge ?",
                'options': ["Wireshark", "Nagios", "SolarWinds", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Wireshark, Nagios, et SolarWinds sont tous des outils qui peuvent Ãªtre utilisÃ©s pour surveiller et gÃ©rer un rÃ©seau informatique au collÃ¨ge, offrant des fonctionnalitÃ©s pour analyser le trafic rÃ©seau, dÃ©tecter les problÃ¨mes, et assurer la performance et la sÃ©curitÃ© du rÃ©seau."
            },
            {
                'id': "0216_8",
                'type': "vrai-faux",
                'question': "Tous les rÃ©seaux informatiques au collÃ¨ge sont les mÃªmes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les rÃ©seaux informatiques au collÃ¨ge ne sont pas les mÃªmes, car ils peuvent varier considÃ©rablement en fonction des technologies utilisÃ©es, des besoins spÃ©cifiques du collÃ¨ge, et des ressources disponibles."
            },
        ]
    ),
    (
        "0217",
        "SÃ©curitÃ© et Ã©thique numÃ©rique.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0217_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en sÃ©curitÃ© et Ã©thique numÃ©rique ?",
                'options': ["SÃ©curitÃ© des donnÃ©es", "Menaces en ligne", "Mesures de sÃ©curitÃ©", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en sÃ©curitÃ© et Ã©thique numÃ©rique comprennent la sÃ©curitÃ© des donnÃ©es, qui fait rÃ©fÃ©rence Ã  la protection des informations personnelles et sensibles contre les cyberattaques et les violations de donnÃ©es, les menaces en ligne, qui incluent des activitÃ©s malveillantes telles que le phishing, le ransomware, et le spyware, et les mesures de sÃ©curitÃ©, qui sont des actions que les utilisateurs peuvent prendre pour se protÃ©ger en ligne, telles que l'utilisation de mots de passe forts, la mise Ã  jour rÃ©guliÃ¨re des logiciels, et l'Ã©vitement de liens suspects."
            },
            {
                'id': "0217_2",
                'type': "vrai-faux",
                'question': "La sÃ©curitÃ© des donnÃ©es n'est pas importante en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la sÃ©curitÃ© des donnÃ©es est extrÃªmement importante en ligne pour protÃ©ger les informations personnelles et sensibles contre les cyberattaques et les violations de donnÃ©es."
            },
            {
                'id': "0217_3",
                'type': "qcm",
                'question': "Quel est un exemple de menace en ligne ?",
                'options': ["Phishing", "Ransomware", "Spyware", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Le phishing, le ransomware, et le spyware sont tous des exemples de menaces en ligne qui peuvent causer des dommages aux utilisateurs et Ã  leurs appareils."
            },
            {
                'id': "0217_4",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes menaces en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes menaces en ligne en termes de leur nature, de leur impact, et des mesures de sÃ©curitÃ© nÃ©cessaires pour les prÃ©venir et les attÃ©nuer."
            },
            {
                'id': "0217_5",
                'type': "qcm",
                'question': "Quel est un exemple de mesure de sÃ©curitÃ© que les utilisateurs peuvent prendre pour se protÃ©ger en ligne ?",
                'options': ["Utiliser des mots de passe forts", "Mettre Ã  jour rÃ©guliÃ¨rement les logiciels", "Ã‰viter les liens suspects", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Utiliser des mots de passe forts, mettre Ã  jour rÃ©guliÃ¨rement les logiciels, et Ã©viter les liens suspects sont tous des mesures de sÃ©curitÃ© que les utilisateurs peuvent prendre pour se protÃ©ger en ligne contre les menaces et les cyberattaques."
             },
            {
                'id': "0217_6",
                'type': "vrai-faux",
                'question': "Tous les utilisateurs sont conscients des menaces en ligne et des mesures de sÃ©curitÃ©. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les utilisateurs ne sont pas nÃ©cessairement conscients des menaces en ligne et des mesures de sÃ©curitÃ©, ce qui peut les rendre vulnÃ©rables aux cyberattaques."
            },
            {
                'id': "0217_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de sÃ©curitÃ© en ligne que les utilisateurs peuvent utiliser pour se protÃ©ger contre les menaces en ligne ?",
                'options': ["Antivirus", "Pare-feu", "VPN", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Un antivirus, un pare-feu, et un VPN sont tous des outils de sÃ©curitÃ© en ligne que les utilisateurs peuvent utiliser pour se protÃ©ger contre les menaces en ligne en dÃ©tectant et en bloquant les logiciels malveillants, en filtrant le trafic rÃ©seau, et en chiffrant les donnÃ©es pour assurer la confidentialitÃ© et la sÃ©curitÃ© en ligne."
            },
            {
                'id': "0217_8",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes mesures de sÃ©curitÃ© en ligne. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes mesures de sÃ©curitÃ© en ligne en termes de leur nature, de leur fonction, et de leur efficacitÃ© pour prÃ©venir et attÃ©nuer les menaces en ligne."
            },
        ]
    ),
    (
        "0218",
        "VÃ©rification d'un prototype.",
        "Technologie",
        "6eme",
        [
            {   'id': "0211_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en vÃ©rification d'un prototype ?",
                'options': ["Test de validation", "Test de vÃ©rification", "Test de performance", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en vÃ©rification d'un prototype comprennent le test de validation, qui vise Ã  s'assurer que le prototype rÃ©pond aux besoins et aux attentes des utilisateurs, le test de vÃ©rification, qui vise Ã  s'assurer que le prototype fonctionne correctement et conformÃ©ment aux spÃ©cifications, et le test de performance, qui Ã©value la rapiditÃ©, l'efficacitÃ©, et la fiabilitÃ© du prototype dans des conditions d'utilisation rÃ©elles."
            },
            {
                'id': "0211_2",
                'type': "vrai-faux",
                'question': "Le test de validation vise Ã  s'assurer que le prototype rÃ©pond aux besoins et aux attentes des utilisateurs. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le test de validation vise Ã  s'assurer que le prototype rÃ©pond aux besoins et aux attentes des utilisateurs en Ã©valuant son adÃ©quation avec les exigences fonctionnelles et les prÃ©fÃ©rences des utilisateurs."
            },
            {
                'id': "0211_3",
                'type': "qcm",
                'question': "Quel est un exemple de test de vÃ©rification ?",
                'options': ["Test unitaire", "Test d'intÃ©gration", "Test de systÃ¨me", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Le test unitaire, le test d'intÃ©gration, et le test de systÃ¨me sont tous des exemples de tests de vÃ©rification qui peuvent Ãªtre utilisÃ©s pour s'assurer que le prototype fonctionne correctement et conformÃ©ment aux spÃ©cifications Ã  diffÃ©rents niveaux du dÃ©veloppement."
            },
            {
                'id': "0211_4",
                'type': "vrai-faux",
                'question': "Le test de performance Ã©value la rapiditÃ©, l'efficacitÃ©, et la fiabilitÃ© du prototype dans des conditions d'utilisation rÃ©elles. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le test de performance Ã©value la rapiditÃ©, l'efficacitÃ©, et la fiabilitÃ© du prototype dans des conditions d'utilisation rÃ©elles pour s'assurer qu'il peut rÃ©pondre aux exigences de performance attendues par les utilisateurs."
            },
            {
                'id': "0211_5",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut Ãªtre utilisÃ© pour effectuer des tests sur un prototype ?",
                'options': ["JUnit", "Selenium", "Postman", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "JUnit, Selenium, et Postman sont tous des outils qui peuvent Ãªtre utilisÃ©s pour effectuer des tests sur un prototype, offrant des fonctionnalitÃ©s spÃ©cifiques pour les tests unitaires, les tests d'intÃ©gration, et les tests de performance."
            },
            {
                'id': "0211_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rents types de tests pour la vÃ©rification d'un prototype. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rents types de tests pour la vÃ©rification d'un prototype en termes de leur objectif, de leur portÃ©e, et de leur mÃ©thodologie."
            },
            {
                'id': "0211_7",
                'type': "qcm",
                'question': "Quel est un exemple de critÃ¨re d'acceptation pour la validation d'un prototype ?",
                'options': ["Le prototype doit rÃ©pondre aux besoins des utilisateurs", "Le prototype doit fonctionner correctement", "Le prototype doit Ãªtre performant", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Tous les exemples mentionnÃ©s (le prototype doit rÃ©pondre aux besoins des utilisateurs, le prototype doit fonctionner correctement, le prototype doit Ãªtre performant) sont des critÃ¨res d'acceptation importants pour la validation d'un prototype, car ils garantissent que le prototype est adaptÃ© Ã  son usage prÃ©vu et satisfait les exigences des utilisateurs."
            },
            {
                'id': "0211_8",
                'type': "vrai-faux",
                'question': "Tous les prototypes sont vÃ©rifiÃ©s de la mÃªme maniÃ¨re. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les prototypes ne sont pas vÃ©rifiÃ©s de la mÃªme maniÃ¨re, car la vÃ©rification peut varier en fonction du type de prototype, du domaine d'application, et des exigences spÃ©cifiques du projet."
            },
        ]
    ),
    (
        "0212",
        "Histoire des tÃ©lÃ©communications.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0212_1",
                'type': "qcm",
                'question': "Quels sont des concepts que l'on ne retrouve pas dans l'histoire des tÃ©lÃ©communications ?",
                'options': ["TÃ©lÃ©graphe", "TÃ©lÃ©phone", "Radio", "vÃ©lo"],
                'correct_option': "vÃ©lo",
                'explanation': "Le tÃ©lÃ©graphe, le tÃ©lÃ©phone, et la radio sont tous des concepts de base en histoire des tÃ©lÃ©communications, car ils reprÃ©sentent des Ã©tapes clÃ©s dans l'Ã©volution des technologies de communication. Le vÃ©lo, en revanche, n'est pas un concept liÃ© Ã  l'histoire des tÃ©lÃ©communications."
            },
            {
                'id': "0212_2",
                'type': "vrai-faux",
                'question': "Le tÃ©lÃ©graphe a Ã©tÃ© inventÃ© avant le tÃ©lÃ©phone. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le tÃ©lÃ©graphe a Ã©tÃ© inventÃ© avant le tÃ©lÃ©phone. Le tÃ©lÃ©graphe a Ã©tÃ© dÃ©veloppÃ© au dÃ©but du 19Ã¨me siÃ¨cle, tandis que le tÃ©lÃ©phone a Ã©tÃ© inventÃ© plus tard au 19Ã¨me siÃ¨cle."
            },
            {
                'id': "0212_3",
                'type': "qcm",
                'question': "Quel exemple de technologie de communication a Ã©tÃ© dÃ©veloppÃ©e aprÃ¨s la radio ?",
                'options': ["TÃ©lÃ©vision", "Internet", "TÃ©lÃ©phone mobile", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Les technologies de communication dÃ©veloppÃ©es aprÃ¨s la radio incluent la tÃ©lÃ©vision, Internet, et le tÃ©lÃ©phone mobile, qui reprÃ©sentent des avancÃ©es significatives dans le domaine des communications."
            },
            {
                'id': "0212_4",
                'type': "vrai-faux",
                'question': "La radio a Ã©tÃ© inventÃ©e avant le tÃ©lÃ©graphe. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, le tÃ©lÃ©graphe a Ã©tÃ© inventÃ© avant la radio. Le tÃ©lÃ©graphe a Ã©tÃ© dÃ©veloppÃ© au dÃ©but du 19Ã¨me siÃ¨cle, tandis que la radio a Ã©tÃ© inventÃ©e plus tard au 19Ã¨me siÃ¨cle."
            },
            {
                'id': "0212_5",
                'type': "qcm",
                'question': "Quel exemple de technologie de communication n'a eu aucun impact significatif sur la sociÃ©tÃ© ?",
                'options': ["TÃ©lÃ©graphe", "TÃ©lÃ©phone", "Radio", "Aucune des options mentionnÃ©es"],
                'correct_option': "Aucune des options mentionnÃ©es",
                'explanation': "Toutes les technologies de communication mentionnÃ©es ont eu un impact significatif sur la sociÃ©tÃ©."
            },
            {
                'id': "0212_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes technologies de communication. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes technologies de communication en termes de leur nature, de leur fonction, et de leur impact sur la sociÃ©tÃ©."
            },
            {
                'id': "0212_7",
                'type': "qcm",
                'question': "Quel est un exemple d'innovation technique dans le domaine des tÃ©lÃ©communications ?",
                'options': ["TÃ©lÃ©graphe sans fil", "TÃ©lÃ©phone portable", "Internet Ã  haut dÃ©bit", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Le tÃ©lÃ©graphe sans fil, le tÃ©lÃ©phone portable, et Internet Ã  haut dÃ©bit sont tous des exemples d'innovations techniques dans le domaine des tÃ©lÃ©communications qui ont amÃ©liorÃ© la connectivitÃ© et l'accessibilitÃ© des communications Ã  travers le monde."
            },
            {
                'id': "0212_8",
                'type': "vrai-faux",
                'question': "Tous les utilisateurs sont conscients de l'histoire des tÃ©lÃ©communications. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les utilisateurs ne sont pas nÃ©cessairement conscients de l'histoire des tÃ©lÃ©communications, ce qui souligne l'importance de l'Ã©ducation pour aider les individus Ã  comprendre l'Ã©volution des technologies de communication et leur impact sur la sociÃ©tÃ©."
            },
        ]
    ),
    (
            "0213",
            "Cahier des charges et contraintes.",
            "Technologie",
            "6eme",
            [
                {
                    'id': "0213_1",
                    'type': "qcm",
                    'question': "Qu'est-ce qu'un cahier des charges et les contraintes ?",
                    'options': ["Le cahier des charges est un document qui dÃ©finit prÃ©cisÃ©ment les besoins, objectifs et exigences d'un projet.", "Les contraintes techniques sont des limitations ou des exigences liÃ©es Ã  la technologie utilisÃ©e dans le projet.", "Les contraintes Ã©conomiques sont des limitations ou des exigences liÃ©es au budget et aux ressources financiÃ¨res disponibles pour le projet.", "Aucun des concepts mentionnÃ©s"],
                    'correct_option': "Le cahier des charges est un document qui dÃ©finit prÃ©cisÃ©ment les besoins, objectifs et exigences d'un projet.",
                    'explanation': "Le cahier des charges est un document essentiel dans la gestion de projet qui dÃ©finit prÃ©cisÃ©ment les besoins, objectifs et exigences d'un projet pour guider le dÃ©veloppement et assurer que les objectifs du projet sont atteints. Les contraintes techniques sont des limitations ou des exigences liÃ©es Ã  la technologie utilisÃ©e dans le projet, tandis que les contraintes Ã©conomiques sont des limitations ou des exigences liÃ©es au budget et aux ressources financiÃ¨res disponibles pour le projet."
            },
            {
                'id': "0213_2",
                'type': "vrai-faux",
                'question': "Le cahier des charges est un document qui dÃ©crit les exigences, les spÃ©cifications, et les critÃ¨res de rÃ©ussite d'un projet ou d'un produit. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, le cahier des charges est un document essentiel dans la gestion de projet qui dÃ©crit les exigences, les spÃ©cifications, et les critÃ¨res de rÃ©ussite d'un projet ou d'un produit pour guider le dÃ©veloppement et assurer que les objectifs du projet sont atteints."
            },
            {
                'id': "0213_3",
                'type': "qcm",
                'question': "Quel est un exemple de contrainte technique ?",
                'options': ["Limitation de la bande passante", "CompatibilitÃ© avec les systÃ¨mes existants", "DisponibilitÃ© des ressources matÃ©rielles", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "La limitation de la bande passante, la compatibilitÃ© avec les systÃ¨mes existants, et la disponibilitÃ© des ressources matÃ©rielles sont tous des exemples de contraintes techniques qui peuvent affecter la conception et le dÃ©veloppement d'un projet ou d'un produit."
            },
            {
                'id': "0213_4",
                'type': "vrai-faux",
                'question': "Les contraintes Ã©conomiques n'ont pas d'impact sur la gestion de projet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les contraintes Ã©conomiques ont un impact significatif sur la gestion de projet, car elles peuvent limiter les ressources disponibles, affecter les dÃ©lais, et influencer les dÃ©cisions prises tout au long du projet."
            },
            {
                'id': "0213_5",
                'type': "qcm",
                'question': "Quel est un exemple de contrainte Ã©conomique ?",
                'options': ["Budget limitÃ©", "Ressources humaines insuffisantes", "DÃ©lai serrÃ©", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Un budget limitÃ©, des ressources humaines insuffisantes, et un dÃ©lai serrÃ© sont tous des exemples de contraintes Ã©conomiques qui peuvent affecter la gestion de projet en limitant les ressources disponibles et en influenÃ§ant les dÃ©cisions prises pour atteindre les objectifs du projet."
            },
            {
                'id': "0213_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes contraintes dans un projet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes contraintes dans un projet en termes de leur nature, de leur impact, et des stratÃ©gies nÃ©cessaires pour les gÃ©rer efficacement."
            },
            {
                'id': "0213_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil qui peut Ãªtre utilisÃ© pour gÃ©rer les contraintes dans un projet ?",
                'options': ["Diagramme de Gantt", "Analyse SWOT", "Matrice de priorisation", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Le diagramme de Gantt, l'analyse SWOT, et la matrice de priorisation sont tous des outils qui peuvent Ãªtre utilisÃ©s pour gÃ©rer les contraintes dans un projet en aidant Ã  planifier, analyser, et prioriser les tÃ¢ches et les ressources pour atteindre les objectifs du projet malgrÃ© les limitations et les exigences."
            },
            {
                'id': "0213_8",
                'type': "vrai-faux",
                'question': "Tous les projets ont les mÃªmes contraintes. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les projets n'ont pas les mÃªmes contraintes, car ils peuvent varier considÃ©rablement en fonction de leur nature, de leurs objectifs, et des ressources disponibles."
            },
        ]
    ),
    (
        "0214",
        "Planification d'un projet.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0214_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en planification d'un projet ?",
                'options': ["Planification de projet", "Gestion du temps", "Gestion des ressources", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en planification d'un projet comprennent la planification de projet, qui est le processus de dÃ©finition des objectifs, des tÃ¢ches, et des Ã©chÃ©ances pour atteindre les rÃ©sultats souhaitÃ©s, la gestion du temps, qui implique l'organisation et la priorisation des tÃ¢ches pour respecter les dÃ©lais, et la gestion des ressources, qui consiste Ã  allouer efficacement les ressources humaines, matÃ©rielles, et financiÃ¨res pour soutenir la rÃ©alisation du projet."
            },
            {
                'id': "0214_2",
                'type': "vrai-faux",
                'question': "La planification de projet est le processus de dÃ©finition des objectifs, des tÃ¢ches, et des Ã©chÃ©ances pour atteindre les rÃ©sultats souhaitÃ©s. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, la planification de projet est un processus essentiel dans la gestion de projet qui implique la dÃ©finition des objectifs, des tÃ¢ches, et des Ã©chÃ©ances pour guider le dÃ©veloppement et assurer que les rÃ©sultats souhaitÃ©s sont atteints."
            },
            {
                'id': "0214_3",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de planification de projet ?",
                'options': ["GanttProject", "Trello", "Microsoft Project", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "GanttProject, Trello, et Microsoft Project sont tous des outils de planification de projet qui offrent diffÃ©rentes fonctionnalitÃ©s pour aider Ã  organiser les tÃ¢ches, gÃ©rer les ressources, et suivre l'avancement du projet."
            },
            {
                'id': "0214_4",
                'type': "vrai-faux",
                'question': "La gestion du temps n'est pas importante dans la planification d'un projet. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, la gestion du temps est cruciale dans la planification d'un projet, car elle permet de s'assurer que les tÃ¢ches sont rÃ©alisÃ©es dans les dÃ©lais impartis et que les ressources sont utilisÃ©es efficacement."
            },
            {
                'id': "0214_5",
                'type': "qcm",
                'question': "Quel est un exemple de technique de gestion du temps ?",
                'options': ["MÃ©thode Pomodoro", "Matrice d'Eisenhower", "Technique de la boÃ®te Ã  outils", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "La mÃ©thode Pomodoro, la matrice d'Eisenhower, et la technique de la boÃ®te Ã  outils sont toutes des techniques de gestion du temps qui peuvent aider les individus Ã  organiser leur travail, prioriser les tÃ¢ches, et amÃ©liorer leur productivitÃ©."
            },
            {
                'id': "0214_6",
                'type': "vrai-faux",
                'question': "Il n'y a pas de diffÃ©rence entre les diffÃ©rentes techniques de gestion du temps. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, il existe une diffÃ©rence entre les diffÃ©rentes techniques de gestion du temps en termes de leur approche, de leur efficacitÃ©, et de leur adaptabilitÃ© aux diffÃ©rents styles de travail et aux besoins individuels."
            },
            {
                'id': "0214_7",
                'type': "qcm",
                'question': "Quel est un exemple d'outil de gestion des ressources ?",
                'options': ["Resource Guru", "Smartsheet", "Asana", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "Resource Guru, Smartsheet, et Asana sont tous des outils de gestion des ressources qui offrent des fonctionnalitÃ©s pour planifier, allouer, et suivre l'utilisation des ressources dans le cadre d'un projet."
            },
            {
                'id': "0214_8",
                'type': "vrai-faux",
                'question': "Tous les projets nÃ©cessitent la mÃªme planification. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, tous les projets ne nÃ©cessitent pas la mÃªme planification, car ils peuvent varier considÃ©rablement en fonction de leur nature, de leurs objectifs, et des ressources disponibles."
            },
        ]
    ),
    (
        "0215",
        "Ã‰volution des besoins et innovations techniques.",
        "Technologie",
        "6eme",
        [
            {
                'id': "0215_1",
                'type': "qcm",
                'question': "Quels sont des concepts de base en Ã©volution des besoins et innovations techniques ?",
                'options': ["Ã‰volution des besoins", "Innovations techniques", "Impact sur la sociÃ©tÃ©", "Tous les concepts mentionnÃ©s"],
                'correct_option': "Tous les concepts mentionnÃ©s",
                'explanation': "Les concepts de base en Ã©volution des besoins et innovations techniques comprennent l'Ã©volution des besoins, qui fait rÃ©fÃ©rence aux changements dans les attentes, les prÃ©fÃ©rences, et les exigences des utilisateurs au fil du temps, les innovations techniques, qui sont des avancÃ©es ou des amÃ©liorations dans les technologies existantes ou la crÃ©ation de nouvelles technologies pour rÃ©pondre Ã  ces besoins changeants, et l'impact sur la sociÃ©tÃ©, qui examine comment ces Ã©volutions et innovations affectent la maniÃ¨re dont les gens vivent, travaillent, et interagissent."
            },
            {
                'id': "0215_2",
                'type': "vrai-faux",
                'question': "L'Ã©volution des besoins fait rÃ©fÃ©rence aux changements dans les attentes, les prÃ©fÃ©rences, et les exigences des utilisateurs au fil du temps. Vrai ou Faux ?",
                'correct': True,
                'explanation': "Vrai, l'Ã©volution des besoins fait rÃ©fÃ©rence aux changements dans les attentes, les prÃ©fÃ©rences, et les exigences des utilisateurs au fil du temps, ce qui peut Ãªtre influencÃ© par divers facteurs tels que les avancÃ©es technologiques, les tendances sociales, et les changements culturels."
            },
            {
                'id': "0215_3",
                'type': "qcm",
                'question': "Quel est un exemple d'innovation technique ?",
                'options': ["Intelligence artificielle", "RÃ©alitÃ© virtuelle", "Impression 3D", "Tous les exemples mentionnÃ©s"],
                'correct_option': "Tous les exemples mentionnÃ©s",
                'explanation': "L'intelligence artificielle, la rÃ©alitÃ© virtuelle, et l'impression 3D sont tous des exemples d'innovations techniques qui ont apportÃ© de nouvelles possibilitÃ©s et amÃ©liorations dans divers domaines tels que la santÃ©, l'Ã©ducation, le divertissement, et la fabrication."
            },
            {
                'id': "0215_4",
                'type': "vrai-faux",
                'question': "Les innovations techniques n'ont aucun impact sur la sociÃ©tÃ©. Vrai ou Faux ?",
                'correct': False,
                'explanation': "Faux, les innovations techniques ont un impact significatif sur la sociÃ©tÃ©, influenÃ§ant la maniÃ¨re dont les gens vivent, travaillent, et interagissent."
            },
        ]
    )
]
def normalize_question_type(question_type):
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    return "vrai-faux"


def build_true_false_statement(question_text, fallback_answer=""):
    question_text = str(question_text).strip()
    fallback_answer = str(fallback_answer).strip().rstrip(".")
    if fallback_answer:
        return f"{question_text} La bonne rÃ©ponse attendue est : {fallback_answer}."
    return question_text or "Choisis si l'affirmation est vraie ou fausse."


def make_quiz(qid, title, subject, level, questions):
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
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - SÃ©rie {qid}", "type": "quiz", "level": level, "subject": subject, "description": f"Diagnostic {subject} {level} : {title}", "status": "published", "created_at": created_at, "updated_at": created_at},
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
            answers.append({"index": index, "question_id": index + 1, "type": "vrai-faux", "answer": "vrai" if q.get("correct", True) else "faux", "correction": q["explanation"]})
    return {
        "contents": {"title": f"Quiz Diagnostic {subject} {level} - SÃ©rie {qid}", "level": level, "subject": subject},
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

