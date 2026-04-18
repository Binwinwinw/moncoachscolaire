#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Lot M — Terminale SES | version progressive.

Usage:
    python dev/tools/quiz/generate_terminale_ses.py
"""

from __future__ import annotations

import json
import os
import random
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
OUTPUT_DIR = os.path.join(SCRIPT_DIR, "terminale_ses_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

# Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
quizzes_data = [

    # ─────────────────────────────────────────────────────────
    # BLOC 1 — Croissance économique & développement (1107–1112)
    # ─────────────────────────────────────────────────────────
    (
        1107,
        "Croissance économique — mesure et sources",
        "SES",
        "Terminale",
        [
            {
                "id": "1107_1",
                "type": "qcm",
                "question": "Le PIB (Produit Intérieur Brut) mesure :",
                "options": [
                    "Le revenu des ménages uniquement",
                    "La valeur de tous les biens et services produits sur un territoire en une période",
                    "Les exportations nettes d'un pays",
                    "La richesse totale accumulée d'une nation",
                ],
                "correct_option": "B",
                "explanation": "Le PIB mesure la production totale de richesses (biens et services) réalisée sur un territoire donné sur une période (généralement un an).",
            },
            {
                "id": "1107_2",
                "type": "vrai-faux",
                "question": "La croissance extensive repose sur l'augmentation des quantités de facteurs de production.",
                "correct": True,
                "explanation": "La croissance extensive mobilise plus de travail et de capital. La croissance intensive repose sur la productivité (PTF).",
            },
            {
                "id": "1107_3",
                "type": "texte",
                "question": "Distingue PIB réel et PIB nominal. Pourquoi utilise-t-on le PIB réel pour mesurer la croissance ?",
                "correct_answer": "Le PIB nominal est calculé aux prix courants ; le PIB réel est corrigé de l'inflation (prix constants). On utilise le PIB réel pour mesurer la vraie croissance de la production, sans l'effet des prix.",
                "explanation": "Le déflateur du PIB permet de passer du nominal au réel. Seul le PIB réel mesure la croissance effective du volume de production.",
            },
            {
                "id": "1107_4",
                "type": "qcm",
                "question": "La Productivité Globale des Facteurs (PGF ou PTF) mesure :",
                "options": [
                    "La quantité de travail utilisée",
                    "La part de la croissance non expliquée par l'accumulation de capital et de travail",
                    "Le rendement du capital uniquement",
                    "Le taux de chômage",
                ],
                "correct_option": "B",
                "explanation": "La PTF (résidu de Solow) capture l'efficacité de la combinaison des facteurs, principalement liée au progrès technique.",
            },
            {
                "id": "1107_5",
                "type": "vrai-faux",
                "question": "Le PIB par habitant est un indicateur parfait du bien-être d'une population.",
                "correct": False,
                "explanation": "Le PIB/habitant mesure la richesse moyenne mais ignore les inégalités, l'environnement, le travail domestique. Des indicateurs comme l'IDH complètent cette mesure.",
            },
            {
                "id": "1107_6",
                "type": "texte",
                "question": "Qu'est-ce que l'IDH et quelles sont ses trois dimensions ?",
                "correct_answer": "L'Indice de Développement Humain (PNUD) mesure : l'espérance de vie à la naissance, le niveau d'éducation (accès et durée de scolarisation), et le revenu national brut par habitant.",
                "explanation": "L'IDH, créé par Amartya Sen et Mahbub ul Haq (1990), dépasse la seule dimension économique pour intégrer santé et éducation.",
            },
            {
                "id": "1107_7",
                "type": "qcm",
                "question": "Selon la théorie de la croissance endogène (Romer, Lucas), la croissance est générée par :",
                "options": [
                    "Des facteurs exogènes uniquement (dons de la nature)",
                    "Le seul capital physique",
                    "Le capital humain, la connaissance et les externalités positives",
                    "La diminution des impôts",
                ],
                "correct_option": "C",
                "explanation": "Les modèles de croissance endogène (Romer, Lucas, Barro) montrent que la R&D, le capital humain et les institutions génèrent une croissance auto-entretenue.",
            },
            {
                "id": "1107_8",
                "type": "vrai-faux",
                "question": "La loi des rendements décroissants du capital prédit que la croissance ralentit à mesure que le capital s'accumule (modèle de Solow).",
                "correct": True,
                "explanation": "Dans le modèle de Solow, chaque unité de capital supplémentaire produit moins que la précédente : tendance à la convergence et à l'état stationnaire.",
            },
        ],
    ),
    (
        1108,
        "Facteurs de production et productivité",
        "SES",
        "Terminale",
        [
            {
                "id": "1108_1",
                "type": "qcm",
                "question": "La productivité du travail se mesure par :",
                "options": [
                    "Le nombre de travailleurs dans l'entreprise",
                    "La production par unité de travail (par heure ou par salarié)",
                    "Le coût du travail unitaire",
                    "Le taux de chômage",
                ],
                "correct_option": "B",
                "explanation": "Productivité du travail = production / quantité de travail. Elle mesure l'efficacité de chaque heure travaillée.",
            },
            {
                "id": "1108_2",
                "type": "vrai-faux",
                "question": "Le capital humain désigne les compétences, savoir-faire et connaissances acquises par les individus via l'éducation et la formation.",
                "correct": True,
                "explanation": "Concept développé par Gary Becker et Theodore Schultz : l'investissement en éducation/formation augmente la productivité du travail.",
            },
            {
                "id": "1108_3",
                "type": "texte",
                "question": "Explique le cercle vertueux entre productivité, salaires et croissance.",
                "correct_answer": "Une hausse de productivité → hausse des salaires réels → hausse du pouvoir d'achat → hausse de la demande → hausse de la production → nouvelles hausses de productivité.",
                "explanation": "Ce cercle vertueux (fordisme) a caractérisé les Trente Glorieuses. Il combine gains de productivité et partage de la valeur ajoutée.",
            },
            {
                "id": "1108_4",
                "type": "qcm",
                "question": "La substitution capital-travail désigne :",
                "options": [
                    "Le remplacement du capital par du travail",
                    "Le remplacement de travailleurs par des machines/robots",
                    "L'augmentation simultanée du capital et du travail",
                    "La formation des travailleurs aux nouvelles technologies",
                ],
                "correct_option": "B",
                "explanation": "La substitution capital-travail implique de remplacer des emplois humains par du capital (machines, automatisation).",
            },
            {
                "id": "1108_5",
                "type": "vrai-faux",
                "question": "Les investissements en R&D sont considérés comme du capital immatériel.",
                "correct": True,
                "explanation": "Le capital immatériel inclut R&D, logiciels, marques, formation. Il est de plus en plus central dans les économies de la connaissance.",
            },
            {
                "id": "1108_6",
                "type": "texte",
                "question": "Qu'est-ce que la valeur ajoutée et comment se calcule-t-elle ?",
                "correct_answer": "VA = Production − Consommations intermédiaires. Elle mesure la richesse créée par l'entreprise. La somme des VA = PIB.",
                "explanation": "La VA représente la contribution nette de chaque entreprise à la richesse nationale. PIB = somme des valeurs ajoutées.",
            },
            {
                "id": "1108_7",
                "type": "qcm",
                "question": "Le taux de marge d'une entreprise est :",
                "options": [
                    "Valeur ajoutée / chiffre d'affaires",
                    "EBE (excédent brut d'exploitation) / valeur ajoutée",
                    "Bénéfice net / actif total",
                    "Investissement / production",
                ],
                "correct_option": "B",
                "explanation": "Taux de marge = EBE/VA. Il mesure la part de la VA qui revient aux entreprises (après rémunération du travail).",
            },
            {
                "id": "1108_8",
                "type": "vrai-faux",
                "question": "Une hausse de la productivité entraîne toujours une hausse de l'emploi.",
                "correct": False,
                "explanation": "La relation est ambiguë : une hausse de productivité peut créer des emplois (effet de compétitivité) mais aussi en détruire (substitution capital-travail). Débat entre économistes.",
            },
        ],
    ),
    (
        1109,
        "Innovation et progrès technique",
        "SES",
        "Terminale",
        [
            {
                "id": "1109_1",
                "type": "qcm",
                "question": "Selon Joseph Schumpeter, la « destruction créatrice » désigne :",
                "options": [
                    "La destruction de l'environnement par la croissance",
                    "Le processus par lequel l'innovation détruit les structures productives existantes pour en créer de nouvelles",
                    "La faillite des petites entreprises par les grandes",
                    "La dépréciation du capital physique",
                ],
                "correct_option": "B",
                "explanation": "Schumpeter (1942) : l'innovation détruit les anciens secteurs (destruction) pour créer de nouvelles activités (créatrice). Ex : voiture/cheval, streaming/DVD.",
            },
            {
                "id": "1109_2",
                "type": "vrai-faux",
                "question": "Une innovation de procédé consiste à introduire un nouveau produit sur le marché.",
                "correct": False,
                "explanation": "L'innovation de procédé améliore les méthodes de production. L'innovation de produit introduit un bien ou service nouveau.",
            },
            {
                "id": "1109_3",
                "type": "texte",
                "question": "Distingue invention et innovation au sens schumpétérien.",
                "correct_answer": "L'invention est une découverte scientifique ou technique. L'innovation est la mise en œuvre commerciale d'une invention : c'est l'entrepreneur qui transforme l'invention en source de profit.",
                "explanation": "Schumpeter insiste sur l'entrepreneur innovateur qui prend le risque de commercialiser une nouvelle idée.",
            },
            {
                "id": "1109_4",
                "type": "qcm",
                "question": "Les externalités positives de la R&D justifient l'intervention publique car :",
                "options": [
                    "Les entreprises investissent trop en R&D",
                    "La connaissance est un bien rival",
                    "Le bénéfice social de la R&D dépasse le bénéfice privé, entraînant un sous-investissement",
                    "La R&D est toujours rentable",
                ],
                "correct_option": "C",
                "explanation": "La connaissance issue de la R&D profite à tous (diffusion, spillovers) → l'entreprise ne peut pas tout capturer → sous-investissement → justification des subventions publiques.",
            },
            {
                "id": "1109_5",
                "type": "vrai-faux",
                "question": "Les brevets permettent à l'innovateur de bénéficier d'un monopole temporaire pour rentabiliser son investissement.",
                "correct": True,
                "explanation": "Le brevet protège l'innovation pendant 20 ans, permettant à l'innovateur de récupérer son investissement avant que la concurrence ne diffuse l'innovation.",
            },
            {
                "id": "1109_6",
                "type": "texte",
                "question": "Qu'est-ce qu'une innovation de rupture (disruptive) ? Donne un exemple récent.",
                "correct_answer": "Une innovation de rupture transforme radicalement un marché existant ou en crée un nouveau, rendant les solutions précédentes obsolètes. Ex : smartphone (disrupte téléphone fixe + appareil photo + GPS), plateforme de streaming (disrupte DVD/télévision).",
                "explanation": "Concept de Christensen : l'innovation disruptive commence souvent sur des marchés de niche avant de s'imposer massivement.",
            },
            {
                "id": "1109_7",
                "type": "qcm",
                "question": "Les nouvelles technologies de l'information et de la communication (NTIC) ont surtout affecté :",
                "options": [
                    "Uniquement le secteur agricole",
                    "Les coûts de transaction, la coordination et la diffusion de l'information",
                    "Uniquement les emplois non qualifiés",
                    "Le coût du capital physique seulement",
                ],
                "correct_option": "B",
                "explanation": "Les NTIC réduisent les coûts de transaction (Coase), facilitent la coordination entre agents et accélèrent la diffusion de l'information (économie numérique).",
            },
            {
                "id": "1109_8",
                "type": "vrai-faux",
                "question": "Le paradoxe de Solow (1987) souligne que la révolution informatique n'avait pas encore amélioré la productivité mesurée.",
                "correct": True,
                "explanation": "« On voit des ordinateurs partout, sauf dans les statistiques de productivité. » Ce paradoxe a été en partie résolu dans les années 1990 avec la hausse effective de la productivité américaine.",
            },
        ],
    ),
    (
        1110,
        "Développement durable",
        "SES",
        "Terminale",
        [
            {
                "id": "1110_1",
                "type": "qcm",
                "question": "Le développement durable (rapport Brundtland, 1987) est défini comme :",
                "options": [
                    "Une croissance économique sans limite",
                    "Un développement qui répond aux besoins du présent sans compromettre ceux des générations futures",
                    "La protection de l'environnement au détriment de l'économie",
                    "Un développement uniquement technologique",
                ],
                "correct_option": "B",
                "explanation": "Définition officielle de la Commission Brundtland (1987) : équité intergénérationnelle entre développement économique, équité sociale et protection environnementale.",
            },
            {
                "id": "1110_2",
                "type": "vrai-faux",
                "question": "Les trois piliers du développement durable sont l'économique, le social et l'environnemental.",
                "correct": True,
                "explanation": "Le triangle du développement durable articule viabilité économique, équité sociale et soutenabilité environnementale.",
            },
            {
                "id": "1110_3",
                "type": "texte",
                "question": "Distingue soutenabilité forte et soutenabilité faible.",
                "correct_answer": "Soutenabilité faible : le capital naturel et le capital artificiel sont substituables (on peut compenser la dégradation environnementale par plus de capital humain ou physique). Soutenabilité forte : certains actifs naturels sont irremplaçables (biodiversité, climat) et ne peuvent être substitués.",
                "explanation": "Cette distinction sépare les économistes néo-classiques (substituabilité) des écologistes (non-substituabilité des ressources critiques).",
            },
            {
                "id": "1110_4",
                "type": "qcm",
                "question": "Une externalité négative de production comme la pollution est corrigée par :",
                "options": [
                    "Une subvention aux pollueurs",
                    "Une taxe pigouvienne égale au coût social de la pollution",
                    "La suppression du marché",
                    "Une hausse du salaire minimum",
                ],
                "correct_option": "B",
                "explanation": "La taxe pigouvienne (Pigou, 1920) internalise le coût externe en taxant le pollueur à hauteur du dommage causé, incitant à réduire la pollution.",
            },
            {
                "id": "1110_5",
                "type": "vrai-faux",
                "question": "Les marchés de droits à polluer (permis d'émissions) permettent de réduire les émissions au moindre coût collectif.",
                "correct": True,
                "explanation": "Le marché carbone (EU ETS) fixe un plafond global d'émissions et laisse les entreprises s'échanger des droits : celles pour qui réduire est moins coûteux réduisent davantage.",
            },
            {
                "id": "1110_6",
                "type": "texte",
                "question": "Qu'est-ce que la courbe de Kuznets environnementale et quelles en sont les limites ?",
                "correct_answer": "Elle suppose qu'à partir d'un certain niveau de revenu, la croissance améliore l'environnement (relation en U inversé). Limites : pas vérifiée pour toutes les pollutions (CO₂), peut justifier de ne pas agir.",
                "explanation": "La courbe de Kuznets environnementale est empiriquement contestée : valable pour certains polluants locaux, pas pour les GES.",
            },
            {
                "id": "1110_7",
                "type": "qcm",
                "question": "L'économie circulaire vise à :",
                "options": [
                    "Accroître la production linéaire",
                    "Réduire les déchets en allongeant la durée de vie des produits et en réutilisant les matières",
                    "Supprimer la mondialisation",
                    "Nationaliser les industries polluantes",
                ],
                "correct_option": "B",
                "explanation": "L'économie circulaire s'oppose au modèle « extraire-fabriquer-jeter » en privilégiant réparation, réutilisation, recyclage et éco-conception.",
            },
            {
                "id": "1110_8",
                "type": "vrai-faux",
                "question": "Les Objectifs de Développement Durable (ODD) de l'ONU (2015) comportent 17 objectifs à atteindre d'ici 2030.",
                "correct": True,
                "explanation": "L'Agenda 2030 de l'ONU fixe 17 ODD couvrant pauvreté, santé, éducation, inégalités, climat, etc.",
            },
        ],
    ),
    (
        1111,
        "Fluctuations économiques et crises",
        "SES",
        "Terminale",
        [
            {
                "id": "1111_1",
                "type": "qcm",
                "question": "Un cycle économique comprend généralement les phases suivantes :",
                "options": [
                    "Expansion, récession, dépression, reprise",
                    "Inflation, déflation, stagflation",
                    "Boom, récession, chômage",
                    "Production, distribution, consommation",
                ],
                "correct_option": "A",
                "explanation": "Le cycle de Juglar (7-11 ans) : expansion (croissance), récession (ralentissement), dépression (contraction), reprise (redémarrage).",
            },
            {
                "id": "1111_2",
                "type": "vrai-faux",
                "question": "La récession est définie techniquement comme deux trimestres consécutifs de baisse du PIB.",
                "correct": True,
                "explanation": "Définition standard : une récession correspond à au moins deux trimestres consécutifs de croissance négative du PIB.",
            },
            {
                "id": "1111_3",
                "type": "texte",
                "question": "Explique le mécanisme du multiplicateur keynésien.",
                "correct_answer": "Une hausse des dépenses autonomes (investissement, dépenses publiques) entraîne une hausse du revenu national supérieure à l'impulsion initiale, via la consommation induite. Multiplicateur k = 1/(1−c) où c est la propension marginale à consommer.",
                "explanation": "Keynes : 1 € d'investissement génère plus de 1 € de revenu supplémentaire car il est en partie re-dépensé (consommation induite).",
            },
            {
                "id": "1111_4",
                "type": "qcm",
                "question": "La crise financière de 2008 a été déclenchée principalement par :",
                "options": [
                    "Une hausse des impôts",
                    "La bulle immobilière et l'effondrement des subprimes aux États-Unis",
                    "Une guerre commerciale entre l'UE et les USA",
                    "Une crise pétrolière",
                ],
                "correct_option": "B",
                "explanation": "La crise des subprimes (crédits hypothécaires à risque) a provoqué une crise bancaire mondiale par la titrisation et l'interconnexion des marchés financiers.",
            },
            {
                "id": "1111_5",
                "type": "vrai-faux",
                "question": "La trappe à liquidité (Keynes) survient quand la politique monétaire expansive devient inefficace car les agents thésaurisent la monnaie.",
                "correct": True,
                "explanation": "Quand les taux sont proches de 0, les agents préfèrent garder la monnaie plutôt que d'investir : la politique monétaire ne stimule plus l'économie.",
            },
            {
                "id": "1111_6",
                "type": "texte",
                "question": "Distingue choc d'offre et choc de demande, avec un exemple de chacun.",
                "correct_answer": "Choc d'offre : perturbation affectant les capacités de production (ex : hausse du prix du pétrole en 1973). Choc de demande : variation brutale de la demande globale (ex : effondrement de la consommation lors d'une crise financière).",
                "explanation": "Les chocs d'offre négatifs provoquent simultanément inflation et chômage (stagflation). Les chocs de demande négatifs causent récession et déflation.",
            },
            {
                "id": "1111_7",
                "type": "qcm",
                "question": "Les stabilisateurs automatiques sont des mécanismes qui :",
                "options": [
                    "Nécessitent une décision politique pour agir",
                    "Atténuent automatiquement les fluctuations (ex : allocations chômage, impôt progressif)",
                    "Amplifient les cycles économiques",
                    "Régulent uniquement la politique monétaire",
                ],
                "correct_option": "B",
                "explanation": "Les stabilisateurs automatiques (indemnités chômage, impôts progressifs) jouent sans décision discrétionnaire : ils soutiennent la demande en récession et la freinenet en expansion.",
            },
            {
                "id": "1111_8",
                "type": "vrai-faux",
                "question": "L'accélérateur lie l'investissement aux variations de la demande : si la demande croît plus vite, l'investissement accélère davantage.",
                "correct": True,
                "explanation": "Principe de l'accélérateur : les variations de la demande entraînent des variations amplifiées de l'investissement, amplifiant les cycles économiques.",
            },
        ],
    ),
    (
        1112,
        "Politiques macroéconomiques",
        "SES",
        "Terminale",
        [
            {
                "id": "1112_1",
                "type": "qcm",
                "question": "La politique budgétaire expansionniste consiste à :",
                "options": [
                    "Réduire les dépenses publiques et augmenter les impôts",
                    "Augmenter les dépenses publiques et/ou baisser les impôts",
                    "Augmenter les taux d'intérêt",
                    "Réduire la masse monétaire",
                ],
                "correct_option": "B",
                "explanation": "Politique keynésienne de relance : hausse des dépenses publiques et/ou baisse d'impôts pour stimuler la demande globale.",
            },
            {
                "id": "1112_2",
                "type": "vrai-faux",
                "question": "La politique monétaire est conduite en France par la Banque Centrale Européenne (BCE) depuis 1999.",
                "correct": True,
                "explanation": "Depuis l'entrée dans la zone euro (1999), la France a délégué sa politique monétaire à la BCE, qui fixe les taux directeurs pour la zone euro.",
            },
            {
                "id": "1112_3",
                "type": "texte",
                "question": "Quels sont les objectifs du carré magique de Nicholas Kaldor ?",
                "correct_answer": "Croissance du PIB, plein emploi (faible chômage), stabilité des prix (faible inflation) et équilibre extérieur (balance courante équilibrée). Ces quatre objectifs sont souvent contradictoires.",
                "explanation": "Le carré magique de Kaldor représente graphiquement ces quatre objectifs macroéconomiques simultanés, rarement tous atteints ensemble.",
            },
            {
                "id": "1112_4",
                "type": "qcm",
                "question": "L'effet d'éviction de la politique budgétaire survient quand :",
                "options": [
                    "Les dépenses publiques créent de l'emploi",
                    "Le financement du déficit public fait monter les taux d'intérêt et réduit l'investissement privé",
                    "La hausse de TVA stimule la consommation",
                    "La politique monétaire amplifie la relance budgétaire",
                ],
                "correct_option": "B",
                "explanation": "L'effet d'éviction (critique libérale) : l'emprunt public fait monter les taux d'intérêt, décourageant l'investissement privé et annulant partiellement l'effet de relance.",
            },
            {
                "id": "1112_5",
                "type": "vrai-faux",
                "question": "Le Pacte de Stabilité et de Croissance (PSC) de l'UE limite le déficit public à 3% du PIB.",
                "correct": True,
                "explanation": "Le PSC (1997) fixe la règle des 3% de déficit et 60% de dette publique en % du PIB pour les États membres de la zone euro.",
            },
            {
                "id": "1112_6",
                "type": "texte",
                "question": "Explique le dilemme inflation-chômage selon la courbe de Phillips et ses remises en cause.",
                "correct_answer": "La courbe de Phillips (1958) montre un arbitrage : moins de chômage → plus d'inflation. Remise en cause : Friedman/Phelps (1968) → courbe de Phillips augmentée des anticipations, neutralité à long terme. Stagflation des années 1970 invalide empiriquement l'arbitrage simple.",
                "explanation": "À court terme, la relation inverse peut exister, mais à long terme, le taux de chômage naturel (NAIRU) est indépendant de l'inflation.",
            },
            {
                "id": "1112_7",
                "type": "qcm",
                "question": "L'assouplissement quantitatif (quantitative easing) consiste à :",
                "options": [
                    "Réduire les impôts",
                    "Acheter massivement des actifs financiers pour injecter des liquidités dans l'économie",
                    "Augmenter les dépenses publiques",
                    "Réduire le taux de réserves obligatoires",
                ],
                "correct_option": "B",
                "explanation": "Le QE (utilisé par la BCE et la Fed après 2008) : la banque centrale crée de la monnaie pour acheter des obligations, abaissant les taux longs et stimulant le crédit.",
            },
            {
                "id": "1112_8",
                "type": "vrai-faux",
                "question": "La règle d'or budgétaire interdit de financer les dépenses d'investissement par la dette.",
                "correct": False,
                "explanation": "La règle d'or autorise l'emprunt pour les dépenses d'investissement (qui créent des actifs futurs) mais pas pour les dépenses courantes de fonctionnement.",
            },
        ],
    ),

    # ─────────────────────────────────────────────────────────
    # BLOC 2 — Marchés, concurrence & régulation (1113–1118)
    # ─────────────────────────────────────────────────────────
    (
        1113,
        "Marchés et concurrence — approfondissement",
        "SES",
        "Terminale",
        [
            {
                "id": "1113_1",
                "type": "qcm",
                "question": "En concurrence pure et parfaite (CPP), le prix d'équilibre est tel que :",
                "options": [
                    "L'offre est supérieure à la demande",
                    "L'offre est égale à la demande",
                    "Le producteur fixe le prix librement",
                    "L'État régule le prix",
                ],
                "correct_option": "B",
                "explanation": "En CPP, le prix d'équilibre (prix de marché) équilibre offre et demande. C'est le prix pour lequel il n'y a ni pénurie ni surplus.",
            },
            {
                "id": "1113_2",
                "type": "vrai-faux",
                "question": "En situation de concurrence pure et parfaite, chaque producteur est « preneur de prix » (price taker).",
                "correct": True,
                "explanation": "Avec atomicité et homogénéité des produits, aucun acteur ne peut influencer le prix : chacun l'accepte comme une donnée.",
            },
            {
                "id": "1113_3",
                "type": "texte",
                "question": "Quelles sont les cinq conditions de la concurrence pure et parfaite ?",
                "correct_answer": "Atomicité (nombreux offreurs et demandeurs), homogénéité des produits, libre entrée et sortie du marché, transparence de l'information, mobilité parfaite des facteurs.",
                "explanation": "Ces cinq conditions (modèle théorique) assurent que le marché conduit à un optimum de Pareto.",
            },
            {
                "id": "1113_4",
                "type": "qcm",
                "question": "Le surplus du consommateur représente :",
                "options": [
                    "La différence entre le prix payé et le prix maximal qu'il aurait accepté de payer",
                    "Le bénéfice du producteur",
                    "Les externalités positives",
                    "La valeur totale produite",
                ],
                "correct_option": "A",
                "explanation": "Surplus consommateur = prix de réserve − prix payé. Il mesure le gain retiré de l'échange par le consommateur.",
            },
            {
                "id": "1113_5",
                "type": "vrai-faux",
                "question": "L'optimum de Pareto est atteint quand il est impossible d'améliorer la situation d'un agent sans détériorer celle d'un autre.",
                "correct": True,
                "explanation": "Définition de l'efficacité au sens de Pareto. En CPP, l'équilibre de marché est Pareto-optimal (1er théorème du bien-être).",
            },
            {
                "id": "1113_6",
                "type": "texte",
                "question": "Explique pourquoi un prix plancher (prix minimum légal) crée un surplus d'offre.",
                "correct_answer": "Un prix plancher fixé au-dessus du prix d'équilibre incite les producteurs à offrir plus, mais les consommateurs demandent moins : offre > demande → surplus. Ex : salaire minimum peut créer du chômage selon certains économistes.",
                "explanation": "Prix plancher > Péquilibre → Qofferte > Qdemandée → surplus. Inverse du plafond de prix.",
            },
            {
                "id": "1113_7",
                "type": "qcm",
                "question": "L'élasticité-prix de la demande mesure :",
                "options": [
                    "La sensibilité de la demande à un changement de revenu",
                    "Le pourcentage de variation de la demande pour 1% de variation du prix",
                    "La variation absolue de la demande",
                    "La rentabilité du producteur",
                ],
                "correct_option": "B",
                "explanation": "e = (ΔQ/Q) / (ΔP/P). Si |e| > 1 : demande élastique. Si |e| < 1 : demande inélastique.",
            },
            {
                "id": "1113_8",
                "type": "vrai-faux",
                "question": "La demande pour les biens de première nécessité (ex : médicaments essentiels) est généralement peu élastique au prix.",
                "correct": True,
                "explanation": "Les biens essentiels sans substituts proches ont une demande inélastique : les consommateurs les achètent même si le prix monte.",
            },
        ],
    ),
    (
        1114,
        "Concurrence imparfaite et monopole",
        "SES",
        "Terminale",
        [
            {
                "id": "1114_1",
                "type": "qcm",
                "question": "Un monopole se caractérise par :",
                "options": [
                    "Nombreux offreurs, un seul demandeur",
                    "Un seul offreur face à de nombreux demandeurs",
                    "Deux offreurs face à de nombreux demandeurs",
                    "Nombreux offreurs, nombreux demandeurs",
                ],
                "correct_option": "B",
                "explanation": "Monopole = 1 offreur, nombreux demandeurs. L'entreprise est « price maker » : elle fixe le prix (ou la quantité).",
            },
            {
                "id": "1114_2",
                "type": "vrai-faux",
                "question": "En situation de monopole, le prix est supérieur au coût marginal, ce qui crée une perte sèche de bien-être.",
                "correct": True,
                "explanation": "Le monopole fixe P > Cm pour maximiser son profit → quantité trop faible → perte sèche (triangle de Harberger).",
            },
            {
                "id": "1114_3",
                "type": "texte",
                "question": "Qu'est-ce qu'un oligopole ? Cite un secteur oligopolistique en France.",
                "correct_answer": "Un oligopole est un marché avec peu d'offreurs (ex : 2-5 grandes entreprises) faisant face à de nombreux consommateurs. Ex : téléphonie mobile (Orange, SFR, Bouygues, Free), grande distribution, secteur pétrolier.",
                "explanation": "L'oligopole se caractérise par l'interdépendance stratégique : chaque entreprise tient compte des réactions de ses concurrents.",
            },
            {
                "id": "1114_4",
                "type": "qcm",
                "question": "La concurrence monopolistique (Chamberlin) se caractérise par :",
                "options": [
                    "Un seul producteur dominant",
                    "De nombreux producteurs vendant des produits différenciés et substituables",
                    "Un accord de cartel entre concurrents",
                    "Une réglementation stricte des prix",
                ],
                "correct_option": "B",
                "explanation": "Concurrence monopolistique : nombreux producteurs + produits différenciés (marques, qualité) → chacun a un mini-monopole sur sa variété. Ex : restaurants, vêtements.",
            },
            {
                "id": "1114_5",
                "type": "vrai-faux",
                "question": "Un cartel est un accord entre entreprises concurrentes pour fixer les prix ou se répartir les marchés.",
                "correct": True,
                "explanation": "Les cartels (ex : OPEP, ententes illicites) sont prohibés par le droit de la concurrence car ils lèsent les consommateurs.",
            },
            {
                "id": "1114_6",
                "type": "texte",
                "question": "Qu'est-ce qu'un monopole naturel et pourquoi justifie-t-il une régulation publique ?",
                "correct_answer": "Un monopole naturel existe quand les coûts fixes sont très élevés et les coûts marginaux dégressifs, rendant une seule entreprise plus efficace (ex : réseaux ferrés, eau). Régulation : éviter l'abus de position dominante tout en conservant les économies d'échelle.",
                "explanation": "Exemples : SNCF (réseau), RTE (électricité). La régulation peut imposer l'accès aux tiers ou fixer le prix.",
            },
            {
                "id": "1114_7",
                "type": "qcm",
                "question": "Le dilemme du prisonnier illustre :",
                "options": [
                    "La théorie des jeux et la difficulté de la coopération entre agents rationnels",
                    "Le paradoxe de la rationalité collective",
                    "L'intérêt de la concurrence",
                    "La stratégie du monopole",
                ],
                "correct_option": "A",
                "explanation": "Le dilemme du prisonnier (Nash) montre que deux agents rationnels choisissent une stratégie dominante (trahir) conduisant à un équilibre sous-optimal, faute de coopération.",
            },
            {
                "id": "1114_8",
                "type": "vrai-faux",
                "question": "L'équilibre de Nash est une situation où aucun joueur n'a intérêt à changer unilatéralement de stratégie.",
                "correct": True,
                "explanation": "Définition de l'équilibre de Nash (1950) : chaque joueur joue la meilleure réponse à la stratégie des autres joueurs.",
            },
        ],
    ),
    (
        1115,
        "Défaillances du marché et externalités",
        "SES",
        "Terminale",
        [
            {
                "id": "1115_1",
                "type": "qcm",
                "question": "Une externalité négative existe quand :",
                "options": [
                    "La production d'un bien profite à un tiers sans contrepartie",
                    "La production ou consommation d'un bien impose un coût à un tiers non partie à la transaction",
                    "Le marché produit trop peu d'un bien",
                    "L'État subventionne un secteur privé",
                ],
                "correct_option": "B",
                "explanation": "Externalité négative : le coût social > coût privé. Ex : pollution industrielle → surproduction par rapport à l'optimum social.",
            },
            {
                "id": "1115_2",
                "type": "vrai-faux",
                "question": "Les externalités positives conduisent à une sous-production par rapport à l'optimum social.",
                "correct": True,
                "explanation": "Quand la production d'un bien profite à des tiers (ex : vaccination, éducation), le producteur ne capte pas tous les bénéfices → sous-production → justification de la subvention.",
            },
            {
                "id": "1115_3",
                "type": "texte",
                "question": "Explique le théorème de Coase (1960) et ses limites.",
                "correct_answer": "Si les droits de propriété sont bien définis et les coûts de transaction nuls, les agents peuvent négocier une solution optimale aux externalités sans intervention de l'État. Limites : coûts de transaction élevés, asymétries d'information, problèmes de « hold-up ».",
                "explanation": "Coase : la solution aux externalités peut être privée (négociation). En pratique, les hypothèses restrictives limitent son application.",
            },
            {
                "id": "1115_4",
                "type": "qcm",
                "question": "Un bien public pur est caractérisé par :",
                "options": [
                    "La rivalité et l'excludabilité",
                    "La non-rivalité et la non-excludabilité",
                    "La rivalité et la non-excludabilité",
                    "La non-rivalité et l'excludabilité",
                ],
                "correct_option": "B",
                "explanation": "Bien public pur (Samuelson) : non-rival (la consommation d'un ne réduit pas celle d'un autre) et non-excluable (impossible d'en exclure quiconque). Ex : phare, défense nationale.",
            },
            {
                "id": "1115_5",
                "type": "vrai-faux",
                "question": "Le problème du passager clandestin justifie la fourniture publique des biens collectifs.",
                "correct": True,
                "explanation": "Pour les biens non-excluables, chacun espère profiter sans payer (free rider) → sous-financement privé → l'État doit les fournir et les financer par l'impôt.",
            },
            {
                "id": "1115_6",
                "type": "texte",
                "question": "Distingue biens publics, biens collectifs, biens de club et biens communs, avec un exemple de chaque.",
                "correct_answer": "Bien public pur (non-rival, non-excluable) : défense nationale. Bien de club (non-rival, excluable) : autoroute à péage. Bien commun (rival, non-excluable) : poisson en mer. Bien privé (rival, excluable) : pain.",
                "explanation": "Taxonomie de Samuelson et Ostrom. Les biens communs souffrent de la « tragédie des communs » (Hardin).",
            },
            {
                "id": "1115_7",
                "type": "qcm",
                "question": "La tragédie des communs (Hardin, 1968) montre que :",
                "options": [
                    "Les biens communs sont toujours bien gérés",
                    "La gestion collective d'une ressource commune mène inévitablement à sa surexploitation",
                    "L'État doit nationaliser toutes les ressources",
                    "La concurrence protège les ressources communes",
                ],
                "correct_option": "B",
                "explanation": "Hardin : chaque individu rationnel maximise son usage d'une ressource commune, menant à sa destruction collective. Ex : surpêche, déforestation.",
            },
            {
                "id": "1115_8",
                "type": "vrai-faux",
                "question": "Elinor Ostrom (Prix Nobel 2009) a montré que les communautés peuvent gérer les ressources communes sans privatisation ni étatisation.",
                "correct": True,
                "explanation": "Ostrom a documenté des systèmes de gouvernance locale efficaces pour gérer les biens communs (forêts, pêche, irrigation) sans les deux solutions extrêmes.",
            },
        ],
    ),
    (
        1116,
        "Asymétries d'information",
        "SES",
        "Terminale",
        [
            {
                "id": "1116_1",
                "type": "qcm",
                "question": "L'asymétrie d'information sur un marché désigne :",
                "options": [
                    "Un marché où tous les acteurs ont la même information",
                    "Une situation où un acteur dispose d'informations que l'autre n'a pas",
                    "Un excès d'information disponible sur les marchés",
                    "L'absence totale d'information",
                ],
                "correct_option": "B",
                "explanation": "L'asymétrie d'information (Akerlof, Spence, Stiglitz, Nobel 2001) : vendeur ou acheteur en sait plus que l'autre sur la qualité du produit ou les intentions.",
            },
            {
                "id": "1116_2",
                "type": "vrai-faux",
                "question": "Le marché des « lemons » d'Akerlof montre que l'asymétrie d'information peut détruire un marché.",
                "correct": True,
                "explanation": "Akerlof (1970) : sur le marché des voitures d'occasion, les vendeurs connaissent la qualité, pas les acheteurs → sélection adverse → que des « lemons » (mauvaises voitures) sur le marché → effondrement.",
            },
            {
                "id": "1116_3",
                "type": "texte",
                "question": "Distingue sélection adverse et aléa moral, avec un exemple pour chacun.",
                "correct_answer": "Sélection adverse (avant le contrat) : les mauvais risques sont plus enclins à contracter. Ex : en assurance, les personnes malades souscrivent plus → anti-sélection. Aléa moral (après le contrat) : l'assuré change de comportement. Ex : conduite plus imprudente après assurance.",
                "explanation": "La sélection adverse concerne les caractéristiques cachées, l'aléa moral les actions cachées (action/information cachée).",
            },
            {
                "id": "1116_4",
                "type": "qcm",
                "question": "La signalisation (signaling) comme solution à l'asymétrie d'information consiste à :",
                "options": [
                    "Cacher l'information aux acheteurs",
                    "L'agent mieux informé transmet un signal crédible à l'autre partie (ex : diplôme, garantie)",
                    "Réguler les prix par l'État",
                    "Interdire certains produits de mauvaise qualité",
                ],
                "correct_option": "B",
                "explanation": "Spence (signaling) : le diplôme signale la productivité d'un candidat même si le contenu appris est peu pertinent. La garantie signale la qualité du produit.",
            },
            {
                "id": "1116_5",
                "type": "vrai-faux",
                "question": "La relation principal-agent décrit une situation où un mandant délègue une action à un agent mais ne peut pas observer toutes ses actions.",
                "correct": True,
                "explanation": "Ex : actionnaire (principal) / dirigeant (agent) ; assuré (principal) / médecin (agent). L'information cachée crée des problèmes d'incitation.",
            },
            {
                "id": "1116_6",
                "type": "texte",
                "question": "Comment les labels, certifications et normes permettent-ils de réduire les asymétries d'information sur les marchés ?",
                "correct_answer": "Les labels (bio, CE, NF) et certifications fournissent une garantie vérifiable par un tiers de confiance, permettant aux consommateurs d'évaluer la qualité sans l'observer directement, réduisant l'incertitude et les risques de sélection adverse.",
                "explanation": "Ce sont des mécanismes de screening ou de signaling collectif, souvent normalisés par l'État ou les organismes professionnels.",
            },
            {
                "id": "1116_7",
                "type": "qcm",
                "question": "Le problème de sélection adverse dans l'assurance santé est corrigé par :",
                "options": [
                    "La liberté totale de contracter",
                    "L'assurance obligatoire et la mutualisation des risques",
                    "La hausse des primes pour tous",
                    "La réduction des remboursements",
                ],
                "correct_option": "B",
                "explanation": "L'assurance obligatoire (Sécu) force les bons risques à intégrer le pool → mutualisation équitable → pas de sélection adverse.",
            },
            {
                "id": "1116_8",
                "type": "vrai-faux",
                "question": "La réputation d'une entreprise est un mécanisme informel de réduction des asymétries d'information.",
                "correct": True,
                "explanation": "La réputation (avis clients, marque, historique) signal la qualité sans contrat formel, incitant les entreprises à maintenir leur niveau de service.",
            },
        ],
    ),
    (
        1117,
        "Politique de la concurrence",
        "SES",
        "Terminale",
        [
            {
                "id": "1117_1",
                "type": "qcm",
                "question": "L'Autorité de la concurrence en France est chargée de :",
                "options": [
                    "Fixer les prix des biens de première nécessité",
                    "Surveiller les pratiques anticoncurrentielles (ententes, abus de position dominante) et contrôler les fusions",
                    "Gérer la politique monétaire",
                    "Nationaliser les monopoles",
                ],
                "correct_option": "B",
                "explanation": "L'Autorité de la concurrence (ex-Conseil de la concurrence) sanctionne les cartels, abus de position dominante et autorise ou refuse les concentrations.",
            },
            {
                "id": "1117_2",
                "type": "vrai-faux",
                "question": "Une entente illicite entre concurrents pour fixer les prix est interdite par le droit européen de la concurrence.",
                "correct": True,
                "explanation": "L'article 101 du TFUE interdit les accords entre entreprises qui faussent la concurrence (cartels, partage de marchés, fixation des prix).",
            },
            {
                "id": "1117_3",
                "type": "texte",
                "question": "Qu'est-ce qu'un abus de position dominante ? Cite un exemple récent.",
                "correct_answer": "Un abus de position dominante (art. 102 TFUE) : une entreprise dominant son marché adopte des pratiques déloyales (prix prédateurs, refus de vente, tying). Ex : amendes infligées à Google (2017-2019) par la Commission européenne pour abus dans la recherche et la pub en ligne.",
                "explanation": "La domination n'est pas interdite en soi, mais l'exploitation abusive de cette position l'est.",
            },
            {
                "id": "1117_4",
                "type": "qcm",
                "question": "Le contrôle des concentrations (fusions-acquisitions) par les autorités vise à :",
                "options": [
                    "Favoriser la croissance des grandes entreprises",
                    "Prévenir la création de positions dominantes nuisant à la concurrence",
                    "Subventionner les entreprises fusionnantes",
                    "Protéger les entreprises nationales des concurrents étrangers",
                ],
                "correct_option": "B",
                "explanation": "Avant une fusion importante, les parties doivent obtenir l'accord de la Commission européenne ou de l'Autorité nationale, qui vérifie qu'elle ne crée pas un quasi-monopole.",
            },
            {
                "id": "1117_5",
                "type": "vrai-faux",
                "question": "La politique industrielle peut être en tension avec la politique de concurrence (ex : champions nationaux vs concurrence équitable).",
                "correct": True,
                "explanation": "Créer des champions nationaux (fusions autorisées pour concurrencer les géants américains/chinois) peut réduire la concurrence interne et contredire les règles de l'UE.",
            },
            {
                "id": "1117_6",
                "type": "texte",
                "question": "Pourquoi les plateformes numériques posent-elles de nouveaux défis à la politique de concurrence ?",
                "correct_answer": "Effets de réseau, économies d'échelle numériques, lock-in des utilisateurs, data comme barrière à l'entrée : les GAFAM peuvent dominer sans être sanctionnés par les critères classiques (prix non abusifs car services gratuits). Nouveaux outils : DMA européen (2022).",
                "explanation": "Le Digital Markets Act (DMA) européen crée des obligations ex ante pour les « gatekeepers » afin de limiter les pratiques anticoncurrentielles numériques.",
            },
            {
                "id": "1117_7",
                "type": "qcm",
                "question": "Les prix prédateurs consistent à :",
                "options": [
                    "Vendre à un prix très élevé pour maximiser le profit",
                    "Vendre en dessous du coût pour éliminer les concurrents puis remonter les prix",
                    "S'entendre sur les prix avec les concurrents",
                    "Subventionner ses propres produits",
                ],
                "correct_option": "B",
                "explanation": "Prix prédateur : casser les prix en dessous du coût de revient pour évincer les rivaux, puis exploiter la position dominante acquise. Interdit par le droit de la concurrence.",
            },
            {
                "id": "1117_8",
                "type": "vrai-faux",
                "question": "La politique de concurrence européenne relève exclusivement de la compétence des États membres.",
                "correct": False,
                "explanation": "La politique de concurrence est une compétence exclusive de l'UE (Commission européenne, DG Competition) pour les affaires transfrontalières.",
            },
        ],
    ),
    (
        1118,
        "Régulation et intervention de l'État",
        "SES",
        "Terminale",
        [
            {
                "id": "1118_1",
                "type": "qcm",
                "question": "La régulation économique désigne :",
                "options": [
                    "La nationalisation de toutes les entreprises",
                    "L'ensemble des règles et institutions qui encadrent le fonctionnement des marchés pour corriger leurs défaillances",
                    "La suppression de toute intervention publique",
                    "La fixation des prix par l'État",
                ],
                "correct_option": "B",
                "explanation": "La régulation vise à corriger les défaillances de marché (monopoles naturels, externalités, asymétries d'info) sans nécessairement remplacer le marché.",
            },
            {
                "id": "1118_2",
                "type": "vrai-faux",
                "question": "La dérégulation (ou déréglementation) des marchés peut aussi être source de défaillances si elle supprime des règles qui corrigeaient des imperfections.",
                "correct": True,
                "explanation": "La dérégulation financière (années 1980-2000) a contribué à la crise de 2008 en permettant une prise de risque excessive. La régulation protège aussi contre les excès.",
            },
            {
                "id": "1118_3",
                "type": "texte",
                "question": "Quelles sont les principales formes d'intervention de l'État dans l'économie ?",
                "correct_answer": "Production directe (entreprises publiques), réglementation (normes, droits), fiscalité (impôts, taxes), redistribution (transferts sociaux), politique industrielle (subventions, R&D), régulation des marchés (concurrence, finances).",
                "explanation": "L'État intervient comme producteur, régulateur, redistribiteur et organisateur des marchés.",
            },
            {
                "id": "1118_4",
                "type": "qcm",
                "question": "Le service public répond à des missions de :",
                "options": [
                    "Maximisation du profit",
                    "Satisfaction de l'intérêt général avec continuité, égalité et adaptabilité",
                    "Concurrence pure et parfaite",
                    "Privatisation progressive",
                ],
                "correct_option": "B",
                "explanation": "Les trois lois de Rolland : continuité du service, égalité de traitement, adaptabilité. Le service public vise l'intérêt général avant le profit.",
            },
            {
                "id": "1118_5",
                "type": "vrai-faux",
                "question": "Les autorités de régulation indépendantes (AMF, ARCEP, CSA) ont été créées pour réguler des secteurs spécifiques à l'abri des pressions politiques.",
                "correct": True,
                "explanation": "Ces autorités administratives indépendantes (AAI) régulent les marchés financiers, télécoms, médias sans être soumises au pouvoir politique direct.",
            },
            {
                "id": "1118_6",
                "type": "texte",
                "question": "Qu'est-ce que la capture réglementaire et pourquoi pose-t-elle problème ?",
                "correct_answer": "La capture réglementaire (Stigler) : les entreprises régulées finissent par influencer (ou « capturer ») le régulateur pour que celui-ci serve leurs intérêts plutôt que ceux du public. Problème : la régulation perd son objectif de correction des défaillances.",
                "explanation": "Ex : lobbying, portes tournantes (revolving doors) entre régulateurs et secteur privé. La capture affaiblit l'efficacité de la régulation.",
            },
            {
                "id": "1118_7",
                "type": "qcm",
                "question": "La libéralisation d'un secteur public (ex : énergie, télécoms) en Europe vise principalement à :",
                "options": [
                    "Nationaliser les entreprises concernées",
                    "Ouvrir à la concurrence pour baisser les prix et améliorer la qualité",
                    "Supprimer toute régulation",
                    "Créer des monopoles nationaux forts",
                ],
                "correct_option": "B",
                "explanation": "Les directives européennes ont imposé l'ouverture à la concurrence des secteurs comme l'énergie, les télécoms, les transports pour bénéficier aux consommateurs.",
            },
            {
                "id": "1118_8",
                "type": "vrai-faux",
                "question": "La taxation des externalités négatives (taxe carbone) vise à internaliser le coût social dans le prix de marché.",
                "correct": True,
                "explanation": "L'internalisation des externalités (Pigou) : taxer la pollution au niveau du dommage social force le pollueur à prendre en compte son impact environnemental.",
            },
        ],
    ),

    # ─────────────────────────────────────────────────────────
    # BLOC 3 — Finance & mondialisation (1119–1124)
    # ─────────────────────────────────────────────────────────
    (
        1119,
        "Financement de l'économie",
        "SES",
        "Terminale",
        [
            {
                "id": "1119_1",
                "type": "qcm",
                "question": "Le financement indirect (intermédié) de l'économie passe par :",
                "options": [
                    "Les marchés financiers (actions, obligations)",
                    "Les banques qui collectent l'épargne et octroient des crédits",
                    "Les ménages qui prêtent directement aux entreprises",
                    "Les fonds d'investissement non bancaires",
                ],
                "correct_option": "B",
                "explanation": "Financement indirect : les banques servent d'intermédiaires entre épargnants et emprunteurs. Financement direct : passage par les marchés (actions, obligations).",
            },
            {
                "id": "1119_2",
                "type": "vrai-faux",
                "question": "L'autofinancement correspond à l'utilisation des bénéfices non distribués (épargne interne) pour financer les investissements.",
                "correct": True,
                "explanation": "L'autofinancement (ou capacité d'autofinancement) évite le recours à l'emprunt ou à l'émission d'actions, mais limite la croissance aux capacités internes.",
            },
            {
                "id": "1119_3",
                "type": "texte",
                "question": "Distingue action et obligation comme instruments de financement pour une entreprise.",
                "correct_answer": "Action : titre de propriété donnant droit aux dividendes et au vote en AG (financement en capital, sans remboursement). Obligation : titre de dette avec intérêts et remboursement à échéance (financement par emprunt). L'action est plus risquée mais potentiellement plus rémunératrice.",
                "explanation": "Actions (fonds propres) et obligations (dette) constituent les deux grandes sources de financement externe sur les marchés financiers.",
            },
            {
                "id": "1119_4",
                "type": "qcm",
                "question": "La création monétaire bancaire se fait principalement par :",
                "options": [
                    "L'impression de billets par la banque centrale",
                    "L'octroi de crédits par les banques commerciales (les dépôts font les crédits)",
                    "L'or détenu dans les coffres",
                    "Les dépôts des ménages uniquement",
                ],
                "correct_option": "B",
                "explanation": "« Les crédits font les dépôts » : quand une banque accorde un prêt, elle crée de la monnaie ex nihilo (inscription au crédit du compte de l'emprunteur).",
            },
            {
                "id": "1119_5",
                "type": "vrai-faux",
                "question": "Le taux d'intérêt directeur de la BCE influence le coût du crédit dans la zone euro.",
                "correct": True,
                "explanation": "Les taux directeurs de la BCE (taux de refinancement) fixent le coût auquel les banques se financent auprès de la banque centrale, influençant les taux pratiqués aux entreprises et ménages.",
            },
            {
                "id": "1119_6",
                "type": "texte",
                "question": "Qu'est-ce que l'effet de levier financier et quand devient-il dangereux ?",
                "correct_answer": "L'effet de levier consiste à financer un investissement par dette pour amplifier la rentabilité des fonds propres. Il amplifie les gains si le taux de rendement > taux d'intérêt, mais aussi les pertes. Dangereux en cas de retournement de marché (endettement excessif → faillite).",
                "explanation": "Entreprises et banques utilisent l'effet de levier. Un levier trop élevé rend fragile : crise 2008 liée à un levier excessif des banques.",
            },
            {
                "id": "1119_7",
                "type": "qcm",
                "question": "La titrisation consiste à :",
                "options": [
                    "Transformer des créances bancaires en titres négociables sur les marchés financiers",
                    "Émettre des actions en bourse",
                    "Accorder des crédits aux ménages",
                    "Nationaliser les banques",
                ],
                "correct_option": "A",
                "explanation": "La titrisation (securitization) permet aux banques de regrouper des crédits (hypothécaires, auto…) et de les vendre sous forme de titres (MBS, CDO) à des investisseurs, transférant le risque.",
            },
            {
                "id": "1119_8",
                "type": "vrai-faux",
                "question": "La désintermédiation financière désigne le recours croissant aux marchés financiers au détriment des banques.",
                "correct": True,
                "explanation": "Tendance depuis les années 1980 : grandes entreprises et États se financent directement sur les marchés (obligations, billets de trésorerie) sans passer par les banques.",
            },
        ],
    ),
    (
        1120,
        "Système financier et risques",
        "SES",
        "Terminale",
        [
            {
                "id": "1120_1",
                "type": "qcm",
                "question": "Le risque systémique en finance désigne :",
                "options": [
                    "Le risque individuel d'une entreprise",
                    "Le risque qu'une défaillance locale se propage à l'ensemble du système financier",
                    "Le risque de change dans les échanges internationaux",
                    "Le risque de taux d'intérêt pour les ménages",
                ],
                "correct_option": "B",
                "explanation": "Le risque systémique (« too big to fail ») : la faillite d'une institution financière majeure peut déclencher une crise financière globale par contagion.",
            },
            {
                "id": "1120_2",
                "type": "vrai-faux",
                "question": "Une bulle spéculative se forme quand le prix d'un actif s'éloigne durablement de sa valeur fondamentale.",
                "correct": True,
                "explanation": "Bulle = divergence entre prix de marché et valeur intrinsèque de l'actif, alimentée par des anticipations auto-réalisatrices (chacun achète parce que les autres achètent).",
            },
            {
                "id": "1120_3",
                "type": "texte",
                "question": "Explique le mécanisme de contagion financière lors d'une crise bancaire.",
                "correct_answer": "La panique bancaire (bank run) : les déposants retirent massivement leurs fonds, forçant la banque à vendre ses actifs en urgence (fire sale), provoquant des pertes, pouvant contaminer d'autres banques exposées aux mêmes actifs ou liées par des créances interbancaires.",
                "explanation": "Les interconnexions du bilan des banques (prêts interbancaires, actifs communs) créent un risque de contagion en chaîne.",
            },
            {
                "id": "1120_4",
                "type": "qcm",
                "question": "La régulation prudentielle des banques (accords de Bâle) impose :",
                "options": [
                    "Des taux d'intérêt fixes",
                    "Des ratios de fonds propres minimaux pour absorber les pertes",
                    "La nationalisation des banques en crise",
                    "L'interdiction des opérations de marché",
                ],
                "correct_option": "B",
                "explanation": "Bâle III (2010) renforce les exigences de capital (ratio CET1 ≥ 4,5%), de liquidité (LCR, NSFR) pour rendre les banques plus résilientes aux chocs.",
            },
            {
                "id": "1120_5",
                "type": "vrai-faux",
                "question": "Le prêteur en dernier ressort (PDR) est la banque centrale qui intervient pour éviter la faillite des banques en crise de liquidité.",
                "correct": True,
                "explanation": "Bagehot (1873) : la BC doit prêter librement, à taux élevé, contre de bons collatéraux, pour éviter la panique bancaire. Rôle joué par la BCE en 2010-2012.",
            },
            {
                "id": "1120_6",
                "type": "texte",
                "question": "Qu'est-ce que l'aléa moral dans le secteur bancaire et comment le réguler ?",
                "correct_answer": "Aléa moral bancaire : sachant qu'elles seront sauvées (too big to fail), les banques prennent des risques excessifs. Solutions : taxe sur les banques systémiques, réserves de capital contra-cycliques, séparation activités dépôt/spéculation, résolution ordonnée (bail-in).",
                "explanation": "Le bail-out (sauvetage public) crée un aléa moral. Le bail-in (pertes d'abord supportées par actionnaires et créanciers) atténue ce problème.",
            },
            {
                "id": "1120_7",
                "type": "qcm",
                "question": "La finance de l'ombre (shadow banking) désigne :",
                "options": [
                    "Les banques qui financent l'économie souterraine",
                    "Les institutions financières non bancaires (fonds, assurances) qui réalisent des fonctions bancaires hors régulation",
                    "Les paradis fiscaux",
                    "Les crypto-monnaies",
                ],
                "correct_option": "B",
                "explanation": "Le shadow banking (fonds monétaires, hedge funds, SPV…) réalise de la transformation de maturité hors du cadre réglementaire bancaire → risques systémiques non supervisés.",
            },
            {
                "id": "1120_8",
                "type": "vrai-faux",
                "question": "L'hypothèse d'efficience des marchés financiers (Fama) affirme que les prix reflètent toujours toute l'information disponible.",
                "correct": True,
                "explanation": "Fama (1970) : en marchés efficients, il est impossible de battre systématiquement le marché car les prix intègrent déjà toute l'information disponible.",
            },
        ],
    ),
    (
        1121,
        "Commerce international et avantages comparatifs",
        "SES",
        "Terminale",
        [
            {
                "id": "1121_1",
                "type": "qcm",
                "question": "Selon la théorie des avantages comparatifs de Ricardo, un pays doit se spécialiser dans :",
                "options": [
                    "Les secteurs où il a un avantage absolu",
                    "Les secteurs où son coût d'opportunité est le plus faible",
                    "Tous les secteurs simultanément",
                    "Les secteurs les plus technologiques",
                ],
                "correct_option": "B",
                "explanation": "Ricardo (1817) : même si un pays est moins productif dans tous les secteurs, il gagne à se spécialiser là où son désavantage est le moins fort (coût d'opportunité le plus faible).",
            },
            {
                "id": "1121_2",
                "type": "vrai-faux",
                "question": "La théorie HOS (Heckscher-Ohlin-Samuelson) prédit que les pays se spécialisent en fonction de leur dotation en facteurs de production.",
                "correct": True,
                "explanation": "HOS : un pays abondant en travail exporte des biens intensifs en travail ; un pays abondant en capital exporte des biens intensifs en capital.",
            },
            {
                "id": "1121_3",
                "type": "texte",
                "question": "Qu'est-ce que le commerce intra-branche et en quoi contredit-il les théories classiques ?",
                "correct_answer": "Le commerce intra-branche : échange du même type de produit entre pays similaires (ex : la France exporte et importe des voitures). Il s'explique par les économies d'échelle, la différenciation des produits et les préférences pour la variété (Krugman, Nobel 2008), pas par les dotations factorielles.",
                "explanation": "Krugman et la nouvelle économie géographique : la concurrence monopolistique et les économies d'échelle expliquent le commerce entre pays similaires.",
            },
            {
                "id": "1121_4",
                "type": "qcm",
                "question": "Un droit de douane est :",
                "options": [
                    "Une taxe sur les exportations",
                    "Une taxe sur les importations qui protège les producteurs nationaux",
                    "Une subvention aux importateurs",
                    "Une norme technique sur les produits étrangers",
                ],
                "correct_option": "B",
                "explanation": "Le droit de douane est un obstacle tarifaire : il renchérit le prix des importations, protégeant les producteurs locaux mais renchérissant les prix pour les consommateurs.",
            },
            {
                "id": "1121_5",
                "type": "vrai-faux",
                "question": "L'OMC (Organisation Mondiale du Commerce) promeut la libéralisation des échanges via des cycles de négociations commerciales.",
                "correct": True,
                "explanation": "L'OMC (créée en 1995, succède au GATT) gère les règles du commerce mondial et arbitre les différends commerciaux entre ses 164 membres.",
            },
            {
                "id": "1121_6",
                "type": "texte",
                "question": "Explique le paradoxe de Léontief et comment il remet en cause la théorie HOS.",
                "correct_answer": "Léontief (1953) a montré que les USA (pays très capital-intensif) exportaient des biens intensifs en travail et importaient du capital-intensif. Cela contredit HOS. Explications : capital humain, qualité du travail américain, comportements de consommation différents.",
                "explanation": "Le paradoxe de Léontief a conduit à enrichir la théorie HOS en introduisant le capital humain et les qualifications.",
            },
            {
                "id": "1121_7",
                "type": "qcm",
                "question": "Les barrières non tarifaires aux échanges incluent :",
                "options": [
                    "Les droits de douane uniquement",
                    "Les normes techniques, sanitaires, quotas, réglementations administratives",
                    "Les accords bilatéraux de libre-échange",
                    "La parité des monnaies",
                ],
                "correct_option": "B",
                "explanation": "Les BNT (barrières non tarifaires) regroupent normes, quotas, licences d'importation, réglementations sanitaires (ex : OGM, chlore sur le poulet).",
            },
            {
                "id": "1121_8",
                "type": "vrai-faux",
                "question": "Selon Keynes, le protectionnisme peut être justifié en cas de chômage élevé pour protéger l'emploi intérieur.",
                "correct": True,
                "explanation": "Keynes admettait un protectionnisme temporaire pour soutenir la demande intérieure en période de dépression. Mais le risque de guerre commerciale réciproque (beggar-thy-neighbour) est réel.",
            },
        ],
    ),
    (
        1122,
        "Balance des paiements",
        "SES",
        "Terminale",
        [
            {
                "id": "1122_1",
                "type": "qcm",
                "question": "La balance des paiements est :",
                "options": [
                    "Le budget de l'État",
                    "Le document comptable qui enregistre l'ensemble des transactions économiques entre un pays et le reste du monde",
                    "La dette extérieure d'un pays",
                    "Le solde des échanges commerciaux seulement",
                ],
                "correct_option": "B",
                "explanation": "La balance des paiements (BdP) est un document statistique qui retrace toutes les transactions (courantes, en capital, financières) entre résidents et non-résidents.",
            },
            {
                "id": "1122_2",
                "type": "vrai-faux",
                "question": "La balance commerciale mesure uniquement les échanges de biens matériels.",
                "correct": True,
                "explanation": "La balance commerciale (biens) est distincte de la balance des services. La balance courante inclut biens + services + revenus + transferts courants.",
            },
            {
                "id": "1122_3",
                "type": "texte",
                "question": "Quels sont les trois comptes principaux de la balance des paiements ?",
                "correct_answer": "1) Compte courant (biens, services, revenus primaires et secondaires). 2) Compte de capital (transferts en capital, acquisitions d'actifs non financiers). 3) Compte financier (investissements directs, portefeuille, autres, réserves de change).",
                "explanation": "La BdP est toujours équilibrée comptablement : la somme des trois comptes est nulle.",
            },
            {
                "id": "1122_4",
                "type": "qcm",
                "question": "Un déficit de la balance courante signifie que :",
                "options": [
                    "Le pays exporte plus qu'il n'importe",
                    "Le pays consomme plus qu'il ne produit et finance l'écart par endettement ou cession d'actifs",
                    "Le pays est en croissance forte",
                    "La monnaie du pays est surévaluée",
                ],
                "correct_option": "B",
                "explanation": "Un déficit courant doit être financé par des entrées de capitaux (IDE, emprunts) → le pays s'endette vis-à-vis du reste du monde.",
            },
            {
                "id": "1122_5",
                "type": "vrai-faux",
                "question": "Les investissements directs à l'étranger (IDE) sont comptabilisés dans le compte financier de la BdP.",
                "correct": True,
                "explanation": "Les IDE (prise de participation ≥ 10% dans une entreprise étrangère) apparaissent dans le compte financier de la BdP.",
            },
            {
                "id": "1122_6",
                "type": "texte",
                "question": "Pourquoi la France a-t-elle un déficit commercial persistant depuis les années 2000 ?",
                "correct_answer": "Perte de compétitivité-prix (coûts salariaux) et hors-prix (qualité, positionnement), spécialisation inadaptée, forte dépendance énergétique, désindustrialisation. Partiellement compensé par l'excédent des services (tourisme, ingénierie).",
                "explanation": "Le déficit commercial français s'est aggravé avec la hausse des importations énergétiques, la perte de parts de marché industrielles et la montée en gamme insuffisante.",
            },
            {
                "id": "1122_7",
                "type": "qcm",
                "question": "La dépréciation du taux de change d'une monnaie peut améliorer la balance commerciale via :",
                "options": [
                    "La hausse du coût des exportations",
                    "La baisse des importations et la hausse des exportations (compétitivité-prix)",
                    "La réduction des IDE entrants",
                    "La hausse des taux d'intérêt",
                ],
                "correct_option": "B",
                "explanation": "Une dépréciation rend les exportations moins chères (pour l'étranger) et les importations plus chères (pour les résidents) → amélioration tendancielle de la balance courante (condition de Marshall-Lerner).",
            },
            {
                "id": "1122_8",
                "type": "vrai-faux",
                "question": "La condition de Marshall-Lerner stipule qu'une dépréciation améliore la balance commerciale si la somme des élasticités-prix des exportations et des importations est supérieure à 1.",
                "correct": True,
                "explanation": "La condition de Marshall-Lerner : |ε_x| + |ε_m| > 1 pour qu'une dépréciation améliore le solde commercial à long terme.",
            },
        ],
    ),
    (
        1123,
        "Intégration européenne",
        "SES",
        "Terminale",
        [
            {
                "id": "1123_1",
                "type": "qcm",
                "question": "La zone euro est une zone monétaire optimale (ZMO) si :",
                "options": [
                    "Tous ses membres ont le même PIB",
                    "Il existe une mobilité des facteurs, une flexibilité des prix et des mécanismes de stabilisation budgétaire",
                    "Les taux de change sont fixes",
                    "Les États membres renoncent à leur politique fiscale",
                ],
                "correct_option": "B",
                "explanation": "Mundell (1961) : une ZMO nécessite mobilité du travail, flexibilité des salaires et transfers budgétaires fédéraux pour absorber les chocs asymétriques.",
            },
            {
                "id": "1123_2",
                "type": "vrai-faux",
                "question": "Le marché unique européen repose sur les quatre libertés de circulation (biens, services, capitaux, personnes).",
                "correct": True,
                "explanation": "L'Acte unique européen (1986) et le Traité de Maastricht (1992) ont établi ces quatre libertés fondamentales du marché intérieur.",
            },
            {
                "id": "1123_3",
                "type": "texte",
                "question": "Quels sont les avantages et inconvénients de l'appartenance à la zone euro pour un pays membre ?",
                "correct_answer": "Avantages : élimination du risque de change, réduction des coûts de transaction, stabilité monétaire, crédibilité anti-inflationniste. Inconvénients : perte de la politique monétaire nationale, impossibilité de dévaluer pour regagner la compétitivité, chocs asymétriques sans mécanisme d'ajustement fédéral.",
                "explanation": "Le débat sur les zones monétaires optimales est au cœur des crises de la zone euro (2010-2015).",
            },
            {
                "id": "1123_4",
                "type": "qcm",
                "question": "Le principe de subsidiarité dans l'UE stipule que :",
                "options": [
                    "L'UE doit tout décider",
                    "Les décisions doivent être prises au niveau le plus proche des citoyens, sauf si l'action européenne est plus efficace",
                    "Les États membres ont priorité sur l'UE",
                    "Le Parlement européen peut annuler les lois nationales",
                ],
                "correct_option": "B",
                "explanation": "Le principe de subsidiarité (Traité de Maastricht) : l'UE n'intervient que si l'action au niveau national est insuffisante. Cherche à équilibrer intégration et souveraineté nationale.",
            },
            {
                "id": "1123_5",
                "type": "vrai-faux",
                "question": "La BCE a pour mandat principal la stabilité des prix (inflation proche de 2%) dans la zone euro.",
                "correct": True,
                "explanation": "Contrairement à la Fed américaine (double mandat : inflation + emploi), la BCE a un mandat principalement centré sur la stabilité des prix.",
            },
            {
                "id": "1123_6",
                "type": "texte",
                "question": "En quoi la crise de la dette souveraine en zone euro (2010-2015) a-t-elle révélé les limites de l'architecture de l'UEM ?",
                "correct_answer": "Absence de mécanisme de solidarité budgétaire, pas de prêteur en dernier ressort pour les États, asymétries de compétitivité entre pays, pas de mutualisation de la dette. Solutions apportées : MES, OMT de la BCE, union bancaire.",
                "explanation": "La crise grecque (2010), irlandaise, portugaise, espagnole a montré les faiblesses institutionnelles de l'Union Économique et Monétaire.",
            },
            {
                "id": "1123_7",
                "type": "qcm",
                "question": "L'union douanière européenne implique :",
                "options": [
                    "La libre circulation des travailleurs uniquement",
                    "Un tarif extérieur commun vis-à-vis des pays tiers et la suppression des droits de douane internes",
                    "Une politique budgétaire commune",
                    "L'adoption d'une monnaie unique",
                ],
                "correct_option": "B",
                "explanation": "L'union douanière = libre-échange interne + tarif extérieur commun (TEC). C'est une étape avant le marché commun et l'union économique.",
            },
            {
                "id": "1123_8",
                "type": "vrai-faux",
                "question": "Le Brexit (sortie du Royaume-Uni de l'UE en 2020) a mis fin à la libre circulation des personnes entre le RU et l'UE.",
                "correct": True,
                "explanation": "Après le Brexit (effectif le 1er jan. 2021), les ressortissants européens au RU et vice-versa sont soumis aux règles de droit commun des étrangers (visa, permis de travail).",
            },
        ],
    ),
    (
        1124,
        "Gouvernance mondiale de l'économie",
        "SES",
        "Terminale",
        [
            {
                "id": "1124_1",
                "type": "qcm",
                "question": "Le FMI (Fonds Monétaire International) a pour mission principale de :",
                "options": [
                    "Financer le développement des pays pauvres",
                    "Assurer la stabilité du système monétaire international et aider les pays en difficulté de balance des paiements",
                    "Réguler le commerce mondial",
                    "Financer les guerres",
                ],
                "correct_option": "B",
                "explanation": "Créé à Bretton Woods (1944), le FMI veille à la stabilité monétaire internationale et prête aux pays en crise de balance des paiements (avec conditionnalité).",
            },
            {
                "id": "1124_2",
                "type": "vrai-faux",
                "question": "La Banque mondiale finance principalement des projets de développement dans les pays à revenus faibles et intermédiaires.",
                "correct": True,
                "explanation": "La Banque mondiale (BIRD + IDA) finance infrastructure, éducation, santé dans les pays en développement, contrairement au FMI centré sur la stabilité monétaire.",
            },
            {
                "id": "1124_3",
                "type": "texte",
                "question": "Qu'est-ce que le G20 et quel rôle joue-t-il dans la gouvernance économique mondiale ?",
                "correct_answer": "Le G20 regroupe 19 pays + l'UE représentant 80% du PIB mondial. Il coordonne les politiques économiques mondiales (réforme financière post-2008, lutte contre l'évasion fiscale, COP, relance). Il n'a pas de pouvoir contraignant mais influence les agendas internationaux.",
                "explanation": "Le G20 a remplacé le G8 comme principal forum de coordination économique depuis la crise de 2008.",
            },
            {
                "id": "1124_4",
                "type": "qcm",
                "question": "L'évasion fiscale internationale par les multinationales utilise notamment :",
                "options": [
                    "Les taux d'imposition élevés",
                    "Les prix de transfert manipulés, les paradis fiscaux et l'optimisation fiscale agressive",
                    "La nationalisation de leurs filiales",
                    "Les droits de douane",
                ],
                "correct_option": "B",
                "explanation": "Les multinationales déclarent leurs bénéfices dans les pays à faible fiscalité (Irlande, Luxembourg, Pays-Bas, îles Caïmans) via les prix de transfert et des structures d'optimisation fiscale.",
            },
            {
                "id": "1124_5",
                "type": "vrai-faux",
                "question": "L'accord de l'OCDE sur l'impôt minimum mondial de 15% pour les multinationales (Pilier 2, 2021) vise à limiter la concurrence fiscale déloyale.",
                "correct": True,
                "explanation": "Le taux minimum global de 15% (accord OCDE/G20, 2021) pour les multinationales de plus de 750M€ de CA vise à réduire l'érosion fiscale.",
            },
            {
                "id": "1124_6",
                "type": "texte",
                "question": "Quelles sont les critiques adressées à la mondialisation financière ?",
                "correct_answer": "Instabilité financière accrue (crises de change, bulles), contagion rapide des crises, pression à la baisse sur la fiscalité du capital (concurrence fiscale), volatilité des flux de capitaux vers les pays émergents, creusement des inégalités.",
                "explanation": "Les partisans de la régulation financière internationale (Stiglitz, Rodrik) soulignent ces risques liés à la libéralisation totale des flux de capitaux.",
            },
            {
                "id": "1124_7",
                "type": "qcm",
                "question": "Le consensus de Washington (années 1980-90) recommandait aux pays en développement de :",
                "options": [
                    "Nationaliser les entreprises étrangères",
                    "Privatiser, libéraliser, déréguler et équilibrer les budgets (ajustement structurel)",
                    "Mettre en place des politiques keynésiennes de relance",
                    "Protéger leurs marchés avec des droits de douane élevés",
                ],
                "correct_option": "B",
                "explanation": "Le consensus de Washington (FMI/BM/Trésor US) : libéralisation commerciale et financière, privatisations, rigueur budgétaire. Critiqué pour avoir aggravé les inégalités dans certains pays.",
            },
            {
                "id": "1124_8",
                "type": "vrai-faux",
                "question": "La montée du nationalisme économique et du protectionnisme (ex : politiques America First, guerre commerciale sino-américaine) fragilise le multilatéralisme commercial.",
                "correct": True,
                "explanation": "Les tensions commerciales USA-Chine (2018-), le Brexit, les restrictions sur les chaînes d'approvisionnement mondiales remettent en cause le multilatéralisme des décennies précédentes.",
            },
        ],
    ),

    # ─────────────────────────────────────────────────────────
    # BLOC 4 — Travail, emploi & protection sociale (1125–1130)
    # ─────────────────────────────────────────────────────────
    (
        1125,
        "Marché du travail et chômage",
        "SES",
        "Terminale",
        [
            {
                "id": "1125_1",
                "type": "qcm",
                "question": "Le taux de chômage au sens du BIT est le rapport entre :",
                "options": [
                    "Chômeurs / population totale",
                    "Chômeurs / population active",
                    "Chômeurs / emploi total",
                    "Inactifs / population en âge de travailler",
                ],
                "correct_option": "B",
                "explanation": "Taux de chômage BIT = (personnes sans emploi, cherchant un emploi et disponibles) / population active. La population active = actifs occupés + chômeurs.",
            },
            {
                "id": "1125_2",
                "type": "vrai-faux",
                "question": "Le halo du chômage désigne les personnes qui ne sont pas comptabilisées comme chômeurs mais proches du marché du travail (ex : découragés, temps partiel subi).",
                "correct": True,
                "explanation": "Le halo du chômage (INSEE) inclut les inactifs qui souhaitent travailler sans rechercher activement. Le sous-emploi et le halo révèlent la sous-estimation du chômage.",
            },
            {
                "id": "1125_3",
                "type": "texte",
                "question": "Distingue les approches néo-classique et keynésienne du chômage.",
                "correct_answer": "Néo-classique : le chômage résulte d'un salaire réel trop élevé (rigidité à la baisse, SMIC). Solution : flexibiliser les salaires. Keynésienne : le chômage est involontaire, lié à l'insuffisance de la demande globale. Solution : relance budgétaire et monétaire.",
                "explanation": "Ce débat fondamental structure la politique économique : libéralisation du marché du travail (offre) vs politiques de relance (demande).",
            },
            {
                "id": "1125_4",
                "type": "qcm",
                "question": "Le chômage frictionnel est :",
                "options": [
                    "Le chômage lié à une récession économique",
                    "Le chômage de court terme dû aux délais de transition entre deux emplois",
                    "Le chômage structurel lié à l'inadéquation des qualifications",
                    "Le chômage technologique dû à l'automatisation",
                ],
                "correct_option": "B",
                "explanation": "Chômage frictionnel = chômage volontaire, de court terme, lié aux délais de recherche d'emploi (mobilité, information). Il est inévitable et considéré comme sain.",
            },
            {
                "id": "1125_5",
                "type": "vrai-faux",
                "question": "Le NAIRU (Non Accelerating Inflation Rate of Unemployment) représente le taux de chômage compatible avec une inflation stable.",
                "correct": True,
                "explanation": "Le NAIRU ou taux de chômage naturel (Friedman) : en dessous de ce taux, la demande de travail excédentaire pousse les salaires et l'inflation à la hausse.",
            },
            {
                "id": "1125_6",
                "type": "texte",
                "question": "Qu'est-ce que le chômage structurel et pourquoi est-il difficile à résorber ?",
                "correct_answer": "Le chômage structurel résulte d'une inadéquation entre les qualifications des chômeurs et les besoins des employeurs (sectorielles, géographiques, technologiques). Difficile à résorber car nécessite formation, reconversion, mobilité géographique : processus longs et coûteux.",
                "explanation": "Ex : désindustrialisation crée des chômeurs industriels dans des bassins d'emploi où les offres sont dans les services de haute technologie.",
            },
            {
                "id": "1125_7",
                "type": "qcm",
                "question": "La théorie de l'insider-outsider (Lindbeck-Snower) explique le chômage par :",
                "options": [
                    "L'insuffisance de la demande",
                    "Le fait que les salariés en poste (insiders) défendent des salaires élevés excluant les chômeurs (outsiders)",
                    "L'absence de capital humain",
                    "La faiblesse des cotisations sociales",
                ],
                "correct_option": "B",
                "explanation": "Les insiders ont le pouvoir de négocier des salaires élevés sans se soucier des chômeurs (outsiders) qui ne peuvent pas entrer en concurrence. Source de chômage persistant.",
            },
            {
                "id": "1125_8",
                "type": "vrai-faux",
                "question": "La dualisation du marché du travail désigne la coexistence d'un segment primaire (CDI, bons salaires) et d'un segment secondaire (CDD, intérim, bas salaires).",
                "correct": True,
                "explanation": "Théorie du marché du travail segmenté (Piore) : les deux segments coexistent sans passerelles faciles, créant une inégalité structurelle entre salariés.",
            },
        ],
    ),
    (
        1126,
        "Politiques de l'emploi",
        "SES",
        "Terminale",
        [
            {
                "id": "1126_1",
                "type": "qcm",
                "question": "Les politiques actives de l'emploi visent à :",
                "options": [
                    "Indemniser les chômeurs sans contrepartie",
                    "Améliorer l'employabilité et faciliter le retour à l'emploi (formation, aide à la recherche, subventions à l'embauche)",
                    "Réduire le temps de travail",
                    "Augmenter les cotisations chômage",
                ],
                "correct_option": "B",
                "explanation": "Politiques actives (vs passives d'indemnisation) : formation professionnelle, contrats aidés, Pôle emploi, dispositifs d'insertion. Visent à accroître l'offre de travail qualifiée.",
            },
            {
                "id": "1126_2",
                "type": "vrai-faux",
                "question": "Le SMIC (Salaire Minimum Interprofessionnel de Croissance) peut générer du chômage chez les travailleurs peu qualifiés selon les économistes néo-classiques.",
                "correct": True,
                "explanation": "Si le SMIC est au-dessus du salaire d'équilibre pour les peu qualifiés, il réduit la demande de travail pour ce segment → chômage. Les keynésiens contestent cet effet en soulignant l'impact positif sur la demande.",
            },
            {
                "id": "1126_3",
                "type": "texte",
                "question": "Qu'est-ce que la flexicurité et quel pays est souvent cité en modèle ?",
                "correct_answer": "La flexicurité (Danemark) combine : flexibilité pour les employeurs (embauche/licenciement facile), sécurité pour les salariés (indemnités chômage élevées) et activation (formation obligatoire). Résultat : chômage faible et adaptation de l'économie.",
                "explanation": "Le modèle danois (flexicurité) est souvent opposé au modèle français (rigidité) comme voie médiane entre flexibilité et protection sociale.",
            },
            {
                "id": "1126_4",
                "type": "qcm",
                "question": "Les contrats aidés (ex-emplois d'avenir) sont des :",
                "options": [
                    "Subventions à l'investissement",
                    "Contrats de travail subventionnés par l'État pour favoriser l'insertion des publics éloignés de l'emploi",
                    "Allocations chômage majorées",
                    "Exonérations fiscales pour les entreprises exportatrices",
                ],
                "correct_option": "B",
                "explanation": "Les contrats aidés (secteur non marchand principalement) permettent à des chômeurs difficiles à insérer d'acquérir une expérience professionnelle avec une prise en charge partielle du salaire par l'État.",
            },
            {
                "id": "1126_5",
                "type": "vrai-faux",
                "question": "La réduction du temps de travail (RTT, 35 heures) vise à partager l'emploi disponible entre davantage de travailleurs.",
                "correct": True,
                "explanation": "Les lois Aubry (1998-2000) sur les 35 heures visaient le partage du travail (work sharing). L'effet net sur l'emploi est débattu.",
            },
            {
                "id": "1126_6",
                "type": "texte",
                "question": "Quels sont les effets attendus et non attendus des exonérations de charges sur les bas salaires en France ?",
                "correct_answer": "Effets attendus : baisse du coût du travail peu qualifié → hausse de l'emploi peu qualifié. Effets non attendus : trappe à bas salaires (désincitation à monter en gamme), coût budgétaire élevé, faible effet sur l'emploi qualifié.",
                "explanation": "Les exonérations de charges (allégements Fillon, CICE) ont un effet ambigu : bénéfiques pour l'emploi non qualifié mais créent des effets de seuil.",
            },
            {
                "id": "1126_7",
                "type": "qcm",
                "question": "La formation professionnelle continue vise principalement à :",
                "options": [
                    "Former les jeunes avant leur entrée sur le marché du travail",
                    "Adapter les compétences des salariés aux évolutions technologiques et sectorielles tout au long de leur carrière",
                    "Réduire les cotisations patronales",
                    "Augmenter le temps de travail",
                ],
                "correct_option": "B",
                "explanation": "La FPC (CPF, plan de formation, apprentissage) permet d'adapter le capital humain aux mutations économiques et réduire le chômage structurel.",
            },
            {
                "id": "1126_8",
                "type": "vrai-faux",
                "question": "Le revenu universel (ou revenu de base) est une prestation sociale versée à tous sans condition de ressources ni de contrepartie.",
                "correct": True,
                "explanation": "Le revenu universel (ou UBI) est versé à tous les citoyens. Les débats portent sur son financement, son effet sur l'incitation au travail et son impact sur la pauvreté.",
            },
        ],
    ),
    (
        1127,
        "Protection sociale et État-providence",
        "SES",
        "Terminale",
        [
            {
                "id": "1127_1",
                "type": "qcm",
                "question": "L'État-providence (Welfare State) repose sur les principes de :",
                "options": [
                    "Marché libre et responsabilité individuelle uniquement",
                    "Solidarité collective, assurance sociale et réduction des inégalités par la redistribution",
                    "Rigueur budgétaire et réduction des dépenses publiques",
                    "Privatisation des services de santé",
                ],
                "correct_option": "B",
                "explanation": "Le Welfare State (Beveridge, Keynes) repose sur la protection contre les risques sociaux (maladie, vieillesse, chômage, famille) via la solidarité nationale.",
            },
            {
                "id": "1127_2",
                "type": "vrai-faux",
                "question": "Le modèle bismarckien de protection sociale est fondé sur les cotisations et réserve les prestations aux travailleurs cotisants.",
                "correct": True,
                "explanation": "Modèle bismarckien (Allemagne, France) : assurance professionnelle, cotisations, accès lié à l'emploi. S'oppose au modèle beveridgien (universel, financé par l'impôt, ex : Royaume-Uni).",
            },
            {
                "id": "1127_3",
                "type": "texte",
                "question": "Quels sont les quatre risques couverts par la Sécurité sociale française ?",
                "correct_answer": "1) Maladie (assurance maladie, CNAM) ; 2) Vieillesse/retraite (CNAV) ; 3) Famille/maternité (CNAF) ; 4) Accidents du travail et maladies professionnelles. Le 5e risque de dépendance (CNSA) est en cours de constitution.",
                "explanation": "La Sécurité sociale française (1945, Ambroise Croizat, Pierre Laroque) couvre ces risques sociaux majeurs.",
            },
            {
                "id": "1127_4",
                "type": "qcm",
                "question": "Gøsta Esping-Andersen distingue trois régimes d'État-providence. Lequel est dit 'social-démocrate' ?",
                "options": [
                    "Le modèle libéral (USA, RU) : ciblage, means-testing",
                    "Le modèle conservateur-corporatiste (France, Allemagne) : assurance professionnelle",
                    "Le modèle social-démocrate (Scandinavie) : universel, généreux, financé par l'impôt",
                    "Le modèle méditerranéen (Italie, Grèce) : familialiste",
                ],
                "correct_option": "C",
                "explanation": "Le modèle social-démocrate (Suède, Danemark, Norvège) offre des prestations universelles généreuses financées par des impôts élevés, avec un fort taux de démarchandisation.",
            },
            {
                "id": "1127_5",
                "type": "vrai-faux",
                "question": "Le RSA (Revenu de Solidarité Active) est une prestation qui complète les revenus d'activité pour les travailleurs pauvres et remplace plusieurs minima sociaux.",
                "correct": True,
                "explanation": "Le RSA (2009, Martin Hirsch) fusionne RMI et API et est versé aux personnes sans emploi ou à faibles ressources, en maintenant une incitation financière au retour à l'emploi.",
            },
            {
                "id": "1127_6",
                "type": "texte",
                "question": "Qu'est-ce que le taux de remplacement d'une assurance chômage et quel est son rôle économique ?",
                "correct_answer": "Le taux de remplacement = allocation chômage / dernier salaire. Il sécurise le revenu des chômeurs (maintien de la demande, stabilisateur automatique) mais peut réduire l'incitation à chercher un emploi rapidement si trop élevé.",
                "explanation": "Arbitrage entre protection sociale (taux élevé) et incitation au retour à l'emploi (risque d'aléa moral si trop généreux).",
            },
            {
                "id": "1127_7",
                "type": "qcm",
                "question": "L'universalisation de la protection sociale désigne :",
                "options": [
                    "La suppression de la Sécurité sociale",
                    "L'extension de la couverture à toute la population, indépendamment de la situation professionnelle",
                    "La privatisation des mutuelles",
                    "Le ciblage des prestations sur les plus pauvres",
                ],
                "correct_option": "B",
                "explanation": "L'universalisation (PUMA en France, 2016 : Protection Universelle Maladie) étend la couverture maladie à tous les résidents légaux, sans condition d'emploi.",
            },
            {
                "id": "1127_8",
                "type": "vrai-faux",
                "question": "Le vieillissement de la population exerce une pression croissante sur les systèmes de retraite par répartition.",
                "correct": True,
                "explanation": "Dans un système par répartition, les actifs financent les retraites courantes. Le vieillissement (baisse du ratio actifs/retraités) crée un déséquilibre financier structurel.",
            },
        ],
    ),
    (
        1128,
        "Inégalités économiques et redistribution",
        "SES",
        "Terminale",
        [
            {
                "id": "1128_1",
                "type": "qcm",
                "question": "Le coefficient de Gini mesure :",
                "options": [
                    "Le taux de pauvreté",
                    "L'inégalité de la distribution des revenus (0 = égalité parfaite, 1 = inégalité maximale)",
                    "Le taux de croissance",
                    "L'IDH d'un pays",
                ],
                "correct_option": "B",
                "explanation": "L'indice de Gini (0-1 ou 0-100%) mesure l'inégalité de distribution. Plus il est élevé, plus la distribution est inégale.",
            },
            {
                "id": "1128_2",
                "type": "vrai-faux",
                "question": "En France, la redistribution fiscale et sociale réduit significativement les inégalités de revenus primaires.",
                "correct": True,
                "explanation": "L'impôt progressif sur le revenu et les transferts sociaux (allocations, minima sociaux) réduisent les inégalités : le Gini des revenus disponibles est nettement inférieur à celui des revenus primaires.",
            },
            {
                "id": "1128_3",
                "type": "texte",
                "question": "Distingue inégalités de revenus et inégalités de patrimoine. Laquelle est la plus marquée ?",
                "correct_answer": "Les inégalités de patrimoine sont beaucoup plus fortes que celles de revenu. En France, les 10% les plus riches détiennent ~60% du patrimoine, contre ~25% des revenus. Le patrimoine s'accumule et se transmet, amplifiant les inégalités intergénérationnelles.",
                "explanation": "Piketty (2013) montre que les inégalités de patrimoine sont structurellement plus élevées que celles de revenu, notamment quand r > g.",
            },
            {
                "id": "1128_4",
                "type": "qcm",
                "question": "Selon Thomas Piketty (Le Capital au XXIe siècle, 2013), la dynamique capitaliste mène à l'augmentation des inégalités quand :",
                "options": [
                    "Le taux d'inflation est élevé",
                    "Le taux de rendement du capital (r) est supérieur au taux de croissance (g)",
                    "Le chômage est faible",
                    "L'État augmente ses dépenses",
                ],
                "correct_option": "B",
                "explanation": "r > g : le capital croît plus vite que l'économie → les détenteurs de capital s'enrichissent plus vite → concentration croissante des patrimoines.",
            },
            {
                "id": "1128_5",
                "type": "vrai-faux",
                "question": "La courbe de Kuznets (économique) prédit que les inégalités augmentent puis diminuent avec le développement économique.",
                "correct": True,
                "explanation": "Kuznets (1955) : en U inversé, les inégalités augmentent lors de l'industrialisation puis diminuent avec la croissance et la redistribution. Remise en cause par l'augmentation des inégalités dans les pays riches depuis les années 1980.",
            },
            {
                "id": "1128_6",
                "type": "texte",
                "question": "Explique le principe de différence de John Rawls et son application à la politique sociale.",
                "correct_answer": "Rawls (Théorie de la Justice, 1971) : les inégalités ne sont justes que si elles bénéficient aux plus défavorisés (principe de différence, derrière le 'voile d'ignorance'). Application : redistribution vers les plus pauvres est juste si elle améliore leur sort.",
                "explanation": "Derrière le voile d'ignorance, les individus choisiraient une société minimisant les risques pour les plus défavorisés (maximin).",
            },
            {
                "id": "1128_7",
                "type": "qcm",
                "question": "La progressivité de l'impôt sur le revenu signifie que :",
                "options": [
                    "Tous paient le même taux d'imposition",
                    "Le taux marginal d'imposition augmente avec le revenu",
                    "Les plus pauvres paient plus",
                    "L'impôt est calculé sur le patrimoine",
                ],
                "correct_option": "B",
                "explanation": "L'impôt progressif : plus le revenu est élevé, plus le taux marginal est fort → redistribution verticale des riches vers les pauvres.",
            },
            {
                "id": "1128_8",
                "type": "vrai-faux",
                "question": "La courbe de Laffer suggère qu'un taux d'imposition trop élevé peut réduire les recettes fiscales.",
                "correct": True,
                "explanation": "Laffer : au-delà d'un taux optimal, la hausse des impôts décourage l'activité et réduit l'assiette fiscale → recettes baissent. Argument utilisé pour justifier les baisses d'impôt.",
            },
        ],
    ),
    (
        1129,
        "Pauvreté et exclusion",
        "SES",
        "Terminale",
        [
            {
                "id": "1129_1",
                "type": "qcm",
                "question": "La pauvreté relative est définie en France comme :",
                "options": [
                    "Moins de 1,9 $ par jour (seuil BM)",
                    "Un revenu inférieur à 60% du revenu médian",
                    "L'absence de logement",
                    "Un revenu inférieur au SMIC",
                ],
                "correct_option": "B",
                "explanation": "La pauvreté monétaire relative (INSEE) : revenu disponible < 60% du revenu médian (seuil UE). En France : ~1 102 €/mois pour une personne seule (2022).",
            },
            {
                "id": "1129_2",
                "type": "vrai-faux",
                "question": "La pauvreté absolue se mesure indépendamment du niveau de vie de la société, contrairement à la pauvreté relative.",
                "correct": True,
                "explanation": "Pauvreté absolue : seuil fixe (ex : 2,15 $/jour BM) indépendant du revenu médian. Pauvreté relative : seuil qui varie avec le revenu médian de la société.",
            },
            {
                "id": "1129_3",
                "type": "texte",
                "question": "Qu'est-ce que l'exclusion sociale et en quoi se distingue-t-elle de la pauvreté ?",
                "correct_answer": "L'exclusion sociale (Robert Castel) est un processus de désaffiliation progressive (emploi → chômage → précarité → rupture des liens sociaux). La pauvreté est une condition économique (insuffisance de ressources). L'exclusion ajoute une dimension sociale (rupture du lien social, non-accès aux droits).",
                "explanation": "Castel distingue zone d'intégration, zone de vulnérabilité et zone de désaffiliation. L'exclusion est le bout d'un processus cumulatif.",
            },
            {
                "id": "1129_4",
                "type": "qcm",
                "question": "La trappe à pauvreté désigne la situation où :",
                "options": [
                    "Les pauvres sont trop paresseux pour chercher du travail",
                    "Les aides sociales réduisent l'incitation à reprendre un emploi car le gain financier est faible voire négatif",
                    "Les riches font pression pour maintenir les pauvres dans la pauvreté",
                    "Les pauvres n'ont pas accès à l'éducation",
                ],
                "correct_option": "B",
                "explanation": "Trappe à pauvreté ou trappe à inactivité : si reprendre un emploi peu rémunéré implique de perdre des allocations, le gain net est insuffisant → désincitation.",
            },
            {
                "id": "1129_5",
                "type": "vrai-faux",
                "question": "Les working poor (travailleurs pauvres) sont des personnes qui ont un emploi mais restent sous le seuil de pauvreté.",
                "correct": True,
                "explanation": "La pauvreté laborieuse : emploi à temps partiel contraint, CDD, bas salaires → revenus insuffisants malgré l'activité. Phénomène croissant avec la flexibilisation du marché du travail.",
            },
            {
                "id": "1129_6",
                "type": "texte",
                "question": "Présente l'approche par les capabilités d'Amartya Sen et son apport à la mesure de la pauvreté.",
                "correct_answer": "Sen (1999) : la pauvreté n'est pas seulement un manque de revenus, mais un manque de libertés réelles (capabilités = capacités à réaliser des fonctionnements). L'IDH s'en inspire : santé, éducation, revenu. Permet de mesurer une pauvreté multidimensionnelle.",
                "explanation": "L'approche par les capabilités dépasse la mesure monétaire et évalue ce que les personnes peuvent réellement faire et être.",
            },
            {
                "id": "1129_7",
                "type": "qcm",
                "question": "L'Indice de Pauvreté Multidimensionnelle (IPM) du PNUD mesure :",
                "options": [
                    "Uniquement le revenu des ménages pauvres",
                    "Les privations simultanées en santé, éducation et niveau de vie",
                    "Le taux de chômage des pays pauvres",
                    "La dette des pays en développement",
                ],
                "correct_option": "B",
                "explanation": "L'IPM (Alkire-Foster, PNUD) : une personne est pauvreté multidimensionnelle si elle est privée dans au moins un tiers des indicateurs (nutrition, mortalité infantile, scolarisation, eau, électricité, logement…).",
            },
            {
                "id": "1129_8",
                "type": "vrai-faux",
                "question": "La pauvreté infantile a des conséquences à long terme sur la santé, l'éducation et les opportunités économiques des enfants concernés.",
                "correct": True,
                "explanation": "La pauvreté infantile est un déterminant majeur des inégalités de santé, d'éducation et de revenus à l'âge adulte. Les enfants pauvres ont plus de risques de malnutrition, d'échec scolaire et de chômage à l'avenir.",
            },
        ],
    ),
]


def build_progressive_questions(
    qid: int,
    title: str,
    theme: str,
    angle: str,
    institution: str,
    indicator: str,
    example: str,
    reference: str,
    policy_tool: str,
) -> list:
    """Genere 8 questions enrichies et contextualisees sur le style SES du lot."""
    return [
        {
            "id": f"{qid}_1",
            "type": "qcm",
            "question": f"Dans le cadre de '{title}', quel acteur est le plus directement associe a la regulation de {angle} ?",
            "options": [
                institution,
                "Les menages uniquement",
                "Les entreprises sans cadre public",
                "Les ONG locales sans coordination internationale",
            ],
            "correct_option": "A",
            "explanation": f"Dans ce chapitre de {theme}, {institution} est un acteur central pour organiser et encadrer {angle}.",
        },
        {
            "id": f"{qid}_2",
            "type": "vrai-faux",
            "question": f"L'indicateur '{indicator}' est utile pour suivre les evolutions de {angle}.",
            "correct": True,
            "explanation": f"En SES, l'analyse de {angle} mobilise des indicateurs objectivables comme '{indicator}' pour comparer les situations dans le temps et dans l'espace.",
        },
        {
            "id": f"{qid}_3",
            "type": "texte",
            "question": f"Definis {angle} et explique en quoi cet enjeu est important en Terminale SES.",
            "correct_answer": f"{angle} designe un ensemble de mecanismes economiques et sociaux qui influencent les comportements des acteurs. Cet enjeu est important car il permet de comprendre les arbitrages entre efficacite economique, equite sociale et soutenabilite.",
            "explanation": "Une bonne reponse mobilise une definition precise, les acteurs concerns et les effets attendus ou observes.",
        },
        {
            "id": f"{qid}_4",
            "type": "qcm",
            "question": "Quel exemple illustre le mieux le mecanisme etudie ?",
            "options": [
                example,
                "Une situation sans intervention d'acteurs collectifs",
                "Une evolution purement individuelle sans cadre institutionnel",
                "Un cas sans donnees ni enjeux economiques",
            ],
            "correct_option": "A",
            "explanation": f"L'exemple '{example}' permet d'analyser concretement les logiques d'acteurs et les effets de {policy_tool}.",
        },
        {
            "id": f"{qid}_5",
            "type": "vrai-faux",
            "question": f"Le levier '{policy_tool}' peut modifier les incitations des acteurs dans ce domaine.",
            "correct": True,
            "explanation": f"En SES, les instruments d'action publique comme '{policy_tool}' orientent les comportements, les couts relatifs et les resultats collectifs.",
        },
        {
            "id": f"{qid}_6",
            "type": "texte",
            "question": f"Propose un argument montreant a la fois un effet positif et une limite de '{policy_tool}'.",
            "correct_answer": f"Effet positif : '{policy_tool}' peut corriger une defaillance de marche ou une inegalite. Limite : ses effets dependent du contexte institutionnel, des comportements strategiques et de la coordination entre acteurs.",
            "explanation": "On attend une argumentation nuancee : effets attendus, conditions de reussite et limites eventuelles.",
        },
        {
            "id": f"{qid}_7",
            "type": "qcm",
            "question": f"Quelle reference theorique est la plus pertinente pour discuter ce chapitre ?",
            "options": [
                reference,
                "Une approche sans cadre theorique",
                "Une interpretation uniquement morale sans faits",
                "Une explication strictement biologique",
            ],
            "correct_option": "A",
            "explanation": f"La reference '{reference}' fournit un cadre analytique mobilisable dans une copie de Terminale SES.",
        },
        {
            "id": f"{qid}_8",
            "type": "vrai-faux",
            "question": "Dans une dissertation SES, il faut articuler notions, donnees et exemples pour justifier l'argumentation.",
            "correct": True,
            "explanation": "La methode attendue au bac combine definitions, mecanismes, ordres de grandeur et illustrations precises.",
        },
    ]


PROGRESSIVE_COMPLETION_SPECS = [
    (1130, "Gouvernance economique mondiale", "SES", "Terminale", "Mondialisation", "coordination macroeconomique internationale", "FMI", "balance des paiements", "programmes d'ajustement structurel", "Keynes, Bretton Woods", "conditionnalite des prets"),
    (1131, "Institutions economiques internationales", "SES", "Terminale", "Mondialisation", "regulation des echanges et de la finance", "OMC", "taux d'ouverture", "reglement des differends commerciaux", "Ricardo et les gains a l'echange", "regles multilaterales"),
    (1132, "Commerce international et protectionnisme", "SES", "Terminale", "Echanges internationaux", "specialisation productive", "Union europeenne", "solde commercial", "taxes douanieres sur l'acier", "Ricardo puis Krugman", "droits de douane"),
    (1133, "Union economique et monetaire europeenne", "SES", "Terminale", "Europe economique", "coordination budgetaire et monetaire", "BCE", "inflation harmonisee", "Pacte de stabilite et de croissance", "Mundell et la zone monetaire optimale", "taux directeurs"),
    (1134, "Crises financieres et regulation", "SES", "Terminale", "Finance", "stabilite du systeme bancaire", "Banque centrale", "ratio de solvabilite", "crise des subprimes de 2008", "Minsky et l'instabilite financiere", "exigences prudentielles"),
    (1135, "Monnaie, credit et politique monetaire", "SES", "Terminale", "Monnaie", "transmission de la politique monetaire", "BCE", "inflation", "hausse des taux en zone euro", "Friedman et la monnaie", "operations d'open market"),
    (1136, "Dette publique et soutenabilite", "SES", "Terminale", "Finances publiques", "soutenabilite budgetaire", "Etat", "ratio dette/PIB", "strategie de reduction des deficits", "Domar et la dynamique dette-croissance", "regle budgetaire pluriannuelle"),
    (1137, "Politiques budgetaires", "SES", "Terminale", "Politiques economiques", "arbitrage relance-rigueur", "Gouvernement", "deficit public", "plan de relance post-crise", "Keynes et multiplicateur", "depenses publiques ciblees"),
    (1138, "Croissance verte et transition ecologique", "SES", "Terminale", "Environnement", "decarbonation de la production", "Union europeenne", "emissions de CO2", "plan Fit for 55", "Pigou et internalisation", "subventions a l'investissement vert"),
    (1139, "Externalites et instruments environnementaux", "SES", "Terminale", "Environnement", "correction des externalites negatives", "Etat", "prix du carbone", "marche europeen du carbone", "Pigou et Coase", "taxe carbone"),
    (1140, "Justice sociale et egalite des chances", "SES", "Terminale", "Inegalites", "reduction des inegalites de depart", "Ecole publique", "taux de diplome selon origine sociale", "politiques d'education prioritaire", "Rawls et principe de difference", "redistribution ciblee"),
    (1141, "Mobilite sociale et reproduction", "SES", "Terminale", "Stratification sociale", "mobilite intergenerationnelle", "INSEE", "table de mobilite", "destins sociaux selon PCS", "Bourdieu et capitaux", "politique de lutte contre la segregation scolaire"),
    (1142, "Capital social et trajectoires", "SES", "Terminale", "Sociologie", "effet des reseaux sociaux sur les opportunites", "Marche du travail", "taux d'acces a l'emploi par reseau", "recrutement par cooptation", "Coleman et capital social", "accompagnement vers l'emploi"),
    (1143, "Engagement politique conventionnel", "SES", "Terminale", "Science politique", "participation electorale et partisane", "Partis politiques", "taux de participation", "campagne presidentielle", "Verba et participation politique", "inscription electorale facilitee"),
    (1144, "Nouvelles formes de participation", "SES", "Terminale", "Science politique", "mobilisation hors partis", "Collectifs citoyens", "nombre de petitions et mobilisations", "marches pour le climat", "Norris et citoyennete critique", "consultation citoyenne"),
    (1145, "Abstention et comportement electoral", "SES", "Terminale", "Science politique", "determinants de l'abstention", "Corps electoral", "abstention par tranche d'age", "hausse de l'abstention aux legislatives", "Colin Crouch et post-democratie", "campagne de mobilisation des jeunes"),
    (1146, "Opinion publique et medias", "SES", "Terminale", "Science politique", "formation de l'opinion", "Instituts de sondage", "indice de confiance mediatique", "debats sur les reseaux sociaux", "Lazarsfeld et two-step flow", "regulation de la desinformation"),
    (1147, "Action publique et mise en agenda", "SES", "Terminale", "Politiques publiques", "selection des problemes publics", "Gouvernement", "part du budget dediee", "loi apres mobilisation associative", "Kingdon et policy window", "arbitrage interministel"),
    (1148, "Travail, emploi et mutations numeriques", "SES", "Terminale", "Marche du travail", "polarisation des emplois", "Entreprises", "part des emplois routiniers", "automatisation dans les services", "Autor et le changement technologique biaise", "formation continue"),
    (1149, "Dialogue social et conflits du travail", "SES", "Terminale", "Marche du travail", "negociation collective", "Syndicats", "taux de syndicalisation", "negociations sur les salaires", "Hirschman exit-voice", "accord de branche"),
    (1150, "Protection sociale contemporaine", "SES", "Terminale", "Etat-providence", "soutenabilite de la protection sociale", "Securite sociale", "depenses sociales/PIB", "reforme des retraites", "Esping-Andersen et regimes d'Etat-providence", "cotisations sociales"),
    (1151, "Pauvrete et precarite", "SES", "Terminale", "Cohesion sociale", "lutte contre la pauvrete monetaire", "CAF", "taux de pauvrete", "revalorisation des minima sociaux", "Sen et capabilites", "transferts sociaux"),
    (1152, "Innovation, productivite et competitivite", "SES", "Terminale", "Croissance", "gains de productivite", "Entreprises innovantes", "productivite horaire", "credit impot recherche", "Schumpeter et destruction creatrice", "soutien a la R&D"),
    (1153, "Methodologie dissertation SES", "SES", "Terminale", "Methodologie", "construction d'une argumentation problematisee", "Jury du bac", "coherence du plan", "copie de bac bien structuree", "Bachelard et rupture avec l'opinion", "plan dialectique"),
    (1154, "Revision generale Terminale SES", "SES", "Terminale", "Bilan", "synthese transversale des chapitres", "Eleve candidat au bac", "maitrise des notions", "entrainement type bac", "Durkheim, Keynes, Bourdieu comme reperes", "fiche de revision structuree"),
]

for _qid, _title, _subject, _level, _theme, _angle, _institution, _indicator, _example, _reference, _policy_tool in PROGRESSIVE_COMPLETION_SPECS:
    quizzes_data.append(
        (
            _qid,
            _title,
            _subject,
            _level,
            build_progressive_questions(
                _qid,
                _title,
                _theme,
                _angle,
                _institution,
                _indicator,
                _example,
                _reference,
                _policy_tool,
            ),
        )
    )


def normalize_question_type(question_type):
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    if qt in {"vrai-faux", "vrai faux", "open", "texte", "text"}:
        return "vrai-faux"
    return "vrai-faux"


def build_true_false_statement(question_text, fallback_answer=""):
    question_text = str(question_text).strip()
    fallback_answer = str(fallback_answer).strip().rstrip(".")
    if fallback_answer:
        return f"{question_text} La bonne réponse attendue est : {fallback_answer}."
    return question_text or "Choisis si l'affirmation est vraie ou fausse."


def make_quiz(qid, title, subject, level, questions):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []

    for question in questions:
        cleaned_question = {k: v for k, v in question.items() if k not in answer_keys}
        qtype = normalize_question_type(question.get("type", ""))
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
    }


def make_answers(qid, title, subject, level, questions):
    answers = []
    for question in questions:
        qtype = normalize_question_type(question.get("type", ""))
        if qtype == "qcm":
            answers.append(
                {
                    "question_id": question.get("id"),
                    "correct_option": question.get("correct_option"),
                    "explanation": question.get("explanation", ""),
                }
            )
        else:
            correct_value = question.get("correct", True)
            if isinstance(correct_value, str):
                correct_value = correct_value.strip().lower() == "vrai"
            answers.append(
                {
                    "question_id": question.get("id"),
                    "correct": bool(correct_value),
                    "explanation": question.get("explanation", ""),
                }
            )

    return {
        "quiz_id": qid,
        "title": title,
        "subject": subject,
        "level": level,
        "answers": answers,
    }


def verify_random_sentinel():
    """Verifie qu'un quiz/reponses aleatoire reste coherent apres generation."""
    entry = random.choice(quizzes_data)
    qid = entry[0]

    quiz_path = os.path.join(QUIZ_DIR, f"{qid}.json")
    answers_path = os.path.join(ANSWERS_DIR, f"{qid}.json")

    with open(quiz_path, encoding="utf-8") as file_handle:
        quiz_obj = json.load(file_handle)
    with open(answers_path, encoding="utf-8") as file_handle:
        answers_obj = json.load(file_handle)

    questions = quiz_obj.get("quiz", {}).get("questions", [])
    answers = answers_obj.get("answers", [])
    allowed_types = {"qcm", "vrai-faux"}

    assert len(questions) == len(answers), (
        f"[sentinel] FAIL quiz={qid}: {len(questions)} questions vs {len(answers)} reponses"
    )
    for question in questions:
        question_type = question.get("type")
        if question_type not in allowed_types:
            raise AssertionError(f"[sentinel] FAIL quiz={qid}: type inconnu '{question_type}'")

    print(f"[sentinel] OK quiz={qid} questions={len(questions)} answers={len(answers)}")


def write_quiz_files():
    os.makedirs(QUIZ_DIR, exist_ok=True)
    os.makedirs(ANSWERS_DIR, exist_ok=True)

    for qid, title, subject, level, questions in quizzes_data:
        quiz_obj = make_quiz(qid, title, subject, level, questions)
        answers_obj = make_answers(qid, title, subject, level, questions)

        with open(os.path.join(QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8") as file_handle:
            json.dump(quiz_obj, file_handle, ensure_ascii=False, indent=2)
        with open(os.path.join(ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8") as file_handle:
            json.dump(answers_obj, file_handle, ensure_ascii=False, indent=2)

    print(f"{len(quizzes_data)} quiz generes dans {OUTPUT_DIR}")
    verify_random_sentinel()


if __name__ == "__main__":
    write_quiz_files()

