#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Generation des quiz Technologie 3eme - IDs 771-797
27 quizzes x 8 questions = 216 questions
Themes : Evolution des objets, Materiaux, Systemes automatises,
         Programmation, Energies, Reseaux, Eco-conception, Projets
Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
"""

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
TECHNO3_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "techno_3eme_quizzes")
TECHNO3_QUIZ_DIR = os.path.join(TECHNO3_OUTPUT_DIR, "quiz")
TECHNO3_ANSWERS_DIR = os.path.join(TECHNO3_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "techno_3eme_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")
os.makedirs(TECHNO3_QUIZ_DIR, exist_ok=True)
os.makedirs(TECHNO3_ANSWERS_DIR, exist_ok=True)
os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)


def fix_mojibake_text(value):
    if not isinstance(value, str):
        return value

    markers = ("Ã", "Â", "â€", "â€™", "â€œ", "â€”", "â€“", "Å")
    if not any(marker in value for marker in markers):
        return value

    for legacy_encoding in ("latin-1", "cp1252"):
        try:
            repaired = value.encode(legacy_encoding).decode("utf-8")
            if repaired != value:
                value = repaired
        except UnicodeError:
            continue

    replacements = {
        "Ã©": "é", "Ã¨": "è", "Ãª": "ê", "Ã«": "ë", "Ã ": "à", "Ã¢": "â",
        "Ã´": "ô", "Ã»": "û", "Ã¹": "ù", "Ã®": "î", "Ã¯": "ï", "Ã§": "ç",
        "Ã‰": "É", "Ã€": "À", "Ã‡": "Ç", "Å“": "œ", "Â": "", "â€™": "’",
        "â€œ": "“", "â€\x9d": "”", "â€“": "–", "â€”": "—", "â€¦": "…",
    }
    for source, target in replacements.items():
        value = value.replace(source, target)
    return value


def normalize_text_payload(payload):
    if isinstance(payload, dict):
        return {key: normalize_text_payload(item) for key, item in payload.items()}
    if isinstance(payload, list):
        return [normalize_text_payload(item) for item in payload]
    if isinstance(payload, tuple):
        return tuple(normalize_text_payload(item) for item in payload)
    if isinstance(payload, str):
        return fix_mojibake_text(payload)
    return payload


def normalize_question_type(question_type):
    qtype = str(question_type or "").strip().lower().replace("_", "-")
    if qtype == "qcm":
        return "qcm"
    return "vrai-faux"


def build_true_false_statement(question_text, correct_answer, explanation):
    answer = str(correct_answer or "").strip()
    detail = str(explanation or "").strip()
    if answer:
        answer = answer.rstrip(".!? ")
        return f"La bonne réponse attendue est : {answer}."
    if detail:
        return detail if detail.endswith((".", "!", "?")) else f"{detail}."
    prompt = str(question_text or "").strip()
    return prompt if prompt else "Cette affirmation est à évaluer."


def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []

    for question in questions:
        raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
        if qtype == "qcm":
            runtime_questions.append({
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            })
        else:
            question_text = str(question.get("question", ""))
            if raw_type not in {"vrai-faux", "vrai faux"}:
                question_text = build_true_false_statement(
                    question.get("question", ""),
                    question.get("correct_answer", ""),
                    question.get("explanation", ""),
                )
            runtime_questions.append({
                "type": "vrai-faux",
                "question": question_text,
            })

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
        "exercisenotion": [],
        "exerciseresponses": [],
    }


def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        raw_type = str(q.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
        if qtype == "qcm":
            options = list(q.get("options", []))
            correct_answer = str(q.get("correct_option", q.get("correct_answer", "")))
            correct_index = options.index(correct_answer) if correct_answer in options else 0
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "qcm",
                "answer": correct_answer,
                "correct": correct_index,
                "correction": str(q.get("explanation", "")),
            })
        else:
            if raw_type in {"vrai-faux", "vrai faux"}:
                tf_source = q.get("correct", q.get("correct_answer", "faux"))
                tf_answer = "vrai" if str(tf_source).strip().lower() in {"true", "vrai", "1"} else "faux"
            else:
                tf_answer = "vrai"
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": tf_answer,
                "correction": str(q.get("explanation", "")),
            })
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


quizzes_data = [

    # =========================================================
    # BLOC 1 - HISTOIRE ET EVOLUTION DES OBJETS TECHNIQUES (771-775)
    # =========================================================
    (771, "L'evolution des objets techniques dans l'histoire", "Technologie", "3eme", [
        {"id": "771_1", "type": "qcm",
         "question": "Comment appelle-t-on la demarche qui consiste a analyser un objet technique pour comprendre son fonctionnement ?",
         "options": [
             "Synthese technique",
             "Analyse fonctionnelle",
             "Prototypage rapide",
             "Modelisation 3D"
         ],
         "correct_option": "Analyse fonctionnelle",
         "explanation": "L'analyse fonctionnelle decompose un objet en fonctions principales (ce qu'il fait) et fonctions contraintes (conditions a respecter)."},
        {"id": "771_2", "type": "vrai-faux",
         "question": "Un objet technique repond toujours a un besoin humain identifie.",
         "correct": True,
         "explanation": "Tout objet technique est concu pour repondre a un besoin : se deplacer, communiquer, transformer l'energie, produire, etc."},
        {"id": "771_3", "type": "texte",
         "question": "Comment appelle-t-on l'amelioration progressive d'un objet technique au fil du temps pour mieux repondre aux besoins ?",
         "correct_answer": "evolution technique",
         "explanation": "L'evolution technique designe les modifications successives d'un objet pour ameliorer ses performances, sa fiabilite, sa securite ou reduire son impact environnemental."},
        {"id": "771_4", "type": "qcm",
         "question": "Qu'est-ce qu'une innovation de rupture en technologie ?",
         "options": [
             "Une amelioration mineure d'un produit existant",
             "Une invention qui change radicalement les usages et rend obsoletes les solutions precedentes",
             "Un brevet depose par une grande entreprise",
             "Un objet technique casse"
         ],
         "correct_option": "Une invention qui change radicalement les usages et rend obsoletes les solutions precedentes",
         "explanation": "L'innovation de rupture (ex. smartphone, moteur a explosion, internet) transforme profondement les pratiques sociales et economiques."},
        {"id": "771_5", "type": "vrai-faux",
         "question": "La machine a vapeur de James Watt (1769) est consideree comme le moteur de la premiere revolution industrielle.",
         "correct": True,
         "explanation": "La machine a vapeur perfectionnee par Watt a permis la mecanisation de la production, lanÃ§ant la revolution industrielle au Royaume-Uni."},
        {"id": "771_6", "type": "texte",
         "question": "Comment appelle-t-on l'ensemble des techniques, outils et savoirs caracteristiques d'une periode historique donnee ?",
         "correct_answer": "patrimoine technique",
         "explanation": "Le patrimoine technique (ou culture technique) designe l'heritage des inventions, savoir-faire et objets techniques qui ont marque une civilisation."},
        {"id": "771_7", "type": "qcm",
         "question": "Quel principe design permet de creer un objet en s'inspirant des formes et solutions trouvees dans la nature ?",
         "options": [
             "Design industriel",
             "Bionique ou biomimetisme",
             "Design modulaire",
             "Ergonomie"
         ],
         "correct_option": "Bionique ou biomimetisme",
         "explanation": "Le biomimetisme (ou bionique) s'inspire des solutions evolutives de la nature : velcro inspire du bardane, structures en nid d'abeille, ailes d'avion inspirees des oiseaux."},
        {"id": "771_8", "type": "vrai-faux",
         "question": "Le brevet d'invention protege une innovation technique pendant 20 ans en France.",
         "correct": True,
         "explanation": "Un brevet d'invention (INPI en France) confere un monopole d'exploitation de 20 ans en echange de la divulgation publique de l'invention."},
    ]),

    (772, "Les familles d'objets techniques et leurs principes", "Technologie", "3eme", [
        {"id": "772_1", "type": "qcm",
         "question": "A quelle famille d'objets techniques appartient une voiture electrique ?",
         "options": [
             "Objet de communication",
             "Objet de transport et deplacement",
             "Objet de production d'energie",
             "Objet de traitement de l'information"
         ],
         "correct_option": "Objet de transport et deplacement",
         "explanation": "Les vehicules (voitures, avions, bateaux) appartiennent a la famille des objets de transport ; ils transforment de l'energie pour produire un mouvement."},
        {"id": "772_2", "type": "vrai-faux",
         "question": "Un smartphone appartient a la fois a la famille des objets de communication et de traitement de l'information.",
         "correct": True,
         "explanation": "Le smartphone est un objet multifonction : il communique (appels, internet), traite l'information (calcul, stockage), capte l'environnement (capteurs) et peut controler d'autres objets."},
        {"id": "772_3", "type": "texte",
         "question": "Comment appelle-t-on la caracteristique d'un objet technique qui permet de l'utiliser facilement et confortablement par des personnes de toutes capacites ?",
         "correct_answer": "ergonomie",
         "explanation": "L'ergonomie etudie l'adaptation des outils et objets a l'utilisateur humain pour reduire la fatigue, les erreurs et ameliorer le confort."},
        {"id": "772_4", "type": "qcm",
         "question": "Qu'est-ce que la fonction d'usage d'un objet technique ?",
         "options": [
             "Sa valeur marchande",
             "Ce a quoi il sert, son utilite principale pour l'utilisateur",
             "Sa duree de vie",
             "Ses composants internes"
         ],
         "correct_option": "Ce a quoi il sert, son utilite principale pour l'utilisateur",
         "explanation": "La fonction d'usage decrit ce que l'objet fait pour repondre au besoin (ex. une lampe : produire de la lumiere). Elle se distingue de la fonction d'estime (esthetique)."},
        {"id": "772_5", "type": "vrai-faux",
         "question": "La standardisation des composants techniques facilite la reparation et la maintenance des objets.",
         "correct": True,
         "explanation": "La standardisation (normes ISO, formats communs) permet d'interchanger des pieces, facilitant la reparation, la production en serie et la maintenance."},
        {"id": "772_6", "type": "texte",
         "question": "Comment appelle-t-on la representation graphique decomposant un objet en sous-systemes et composants selon leurs fonctions ?",
         "correct_answer": "diagramme FAST",
         "explanation": "Le diagramme FAST (Function Analysis System Technique) decompose les fonctions d'un produit en repondant aux questions 'Pourquoi ?', 'Comment ?' et 'Quand ?'."},
        {"id": "772_7", "type": "qcm",
         "question": "Quelle propriete technique permet a un materiau de retrouver sa forme initiale apres deformation ?",
         "options": [
             "Plasticite",
             "Elasticite",
             "Fragilite",
             "Viscosite"
         ],
         "correct_option": "Elasticite",
         "explanation": "L'elasticite est la propriete d'un materiau a se deformer reversiblement (comme le caoutchouc ou un ressort) et retrouver sa forme initiale."},
        {"id": "772_8", "type": "vrai-faux",
         "question": "L'obsolescence programmee consiste a concevoir un produit avec une duree de vie limitee pour forcer son remplacement.",
         "correct": True,
         "explanation": "L'obsolescence programmee (pratique illegale en France depuis 2015) consiste a reduire intentionnellement la duree de vie d'un produit pour inciter a l'achat de son remplacement."},
    ]),

    (773, "Analyse d'un objet technique : demarche et methodes", "Technologie", "3eme", [
        {"id": "773_1", "type": "qcm",
         "question": "Quel document decrit les caracteristiques qu'un objet technique doit satisfaire avant sa conception ?",
         "options": [
             "Le plan de fabrication",
             "Le cahier des charges fonctionnel",
             "Le bon de commande",
             "La fiche technique de montage"
         ],
         "correct_option": "Le cahier des charges fonctionnel",
         "explanation": "Le CdCF (Cahier des Charges Fonctionnel) liste toutes les fonctions, contraintes et performances que le produit doit respecter pour satisfaire le besoin."},
        {"id": "773_2", "type": "vrai-faux",
         "question": "La demarche de projet en technologie suit les etapes : analyser, concevoir, realiser, valider.",
         "correct": True,
         "explanation": "La demarche technologique suit un cycle : analyser le besoin â†’ concevoir une solution â†’ realiser un prototype â†’ valider par rapport au CdCF."},
        {"id": "773_3", "type": "texte",
         "question": "Comment appelle-t-on un modele simplifie representant les caracteristiques essentielles d'un objet ou d'un systeme ?",
         "correct_answer": "maquette ou modele",
         "explanation": "Une maquette (ou modele) permet de tester et visualiser un objet avant sa fabrication definitive, reduisant les couts et les erreurs."},
        {"id": "773_4", "type": "qcm",
         "question": "Qu'est-ce qu'un prototype en technologie ?",
         "options": [
             "Un dessin technique sur papier",
             "Un premier exemplaire fonctionnel d'un produit permettant de le tester",
             "Un composant electronique",
             "Un logiciel de simulation"
         ],
         "correct_option": "Un premier exemplaire fonctionnel d'un produit permettant de le tester",
         "explanation": "Le prototype est le premier exemplaire fonctionnel realise pour valider les choix techniques avant la production en serie."},
        {"id": "773_5", "type": "vrai-faux",
         "question": "La simulation informatique permet de tester virtuellement le comportement d'un objet avant de le fabriquer.",
         "correct": True,
         "explanation": "La simulation (logiciels CAO, FEM, CFD) permet de prevoir le comportement mecanique, thermique ou fluidique d'un objet sans avoir a le fabriquer physiquement."},
        {"id": "773_6", "type": "texte",
         "question": "Comment appelle-t-on la representation en trois dimensions d'un objet realisee par un logiciel de conception assistee par ordinateur ?",
         "correct_answer": "maquette numerique 3D",
         "explanation": "La maquette numerique 3D (realise avec SolidWorks, FreeCAD, Fusion 360) permet de visualiser, simuler et modifier un objet avant fabrication."},
        {"id": "773_7", "type": "qcm",
         "question": "Quel outil graphique represente les differentes etapes d'un projet avec leur duree et leurs dependances sous forme de barres horizontales ?",
         "options": [
             "Organigramme",
             "Diagramme de Gantt",
             "PERT",
             "Schema blocs"
         ],
         "correct_option": "Diagramme de Gantt",
         "explanation": "Le diagramme de Gantt (Henri Gantt, 1910) represente le planning d'un projet avec les taches, leur duree et leur chevauchement eventuel."},
        {"id": "773_8", "type": "vrai-faux",
         "question": "La norme ISO 9001 concerne le management de la qualite dans les entreprises.",
         "correct": True,
         "explanation": "ISO 9001 est la norme internationale de management de la qualite, certifiant qu'une organisation maitrise ses processus pour satisfaire ses clients."},
    ]),

    (774, "Les grandes inventions et leur impact sur la societe", "Technologie", "3eme", [
        {"id": "774_1", "type": "qcm",
         "question": "Quelle invention de Gutenberg (vers 1450) a revolutionne la diffusion du savoir ?",
         "options": [
             "La machine a calculer",
             "L'imprimerie a caracteres mobiles",
             "Le telescope",
             "La poudre a canon"
         ],
         "correct_option": "L'imprimerie a caracteres mobiles",
         "explanation": "L'imprimerie de Gutenberg (vers 1450) a permis la reproduction rapide et economique de textes, democratisant l'acces au savoir et favorisant la Renaissance et la Reforme."},
        {"id": "774_2", "type": "vrai-faux",
         "question": "Internet a ete initialement developpe comme un reseau militaire americain (ARPANET) dans les annees 1960.",
         "correct": True,
         "explanation": "ARPANET (1969), financÃ© par le DoD americain, est le precurseur d'internet ; il visait a maintenir les communications en cas d'attaque nucleaire."},
        {"id": "774_3", "type": "texte",
         "question": "Quel scientifique est considere comme l'inventeur de la radio, ayant realise la premiere transmission sans fil en 1895 ?",
         "correct_answer": "Guglielmo Marconi",
         "explanation": "Marconi a realise la premiere transmission radio en 1895 et recu le prix Nobel de physique en 1909 pour ses travaux sur la telegraphie sans fil."},
        {"id": "774_4", "type": "qcm",
         "question": "Quel est l'impact principal de la revolution numerique sur la societe depuis les annees 1990 ?",
         "options": [
             "La disparition des emplois industriels uniquement",
             "La transformation de la production, de la communication et de l'acces au savoir a l'echelle mondiale",
             "La reduction des inegalites economiques mondiales",
             "L'amelioration uniquement des transports"
         ],
         "correct_option": "La transformation de la production, de la communication et de l'acces au savoir a l'echelle mondiale",
         "explanation": "La revolution numerique a transforme l'economie (industrie 4.0), les communications (internet, reseaux sociaux), le travail et l'acces au savoir."},
        {"id": "774_5", "type": "vrai-faux",
         "question": "La deuxieme revolution industrielle (fin XIXe siecle) est liee a l'electricite et au moteur a explosion.",
         "correct": True,
         "explanation": "La 2e revolution industrielle (1870-1914) est caracterisee par l'electricite (Edison, Tesla), le moteur a explosion (Benz, Diesel) et la production de masse (taylorisme)."},
        {"id": "774_6", "type": "texte",
         "question": "Comment appelle-t-on la transition vers une economie ou les technologies numeriques transforment tous les secteurs de production et de service ?",
         "correct_answer": "industrie 4.0",
         "explanation": "L'Industrie 4.0 (quatrieme revolution industrielle) integre IA, robots, IoT, impression 3D et big data pour creer des usines connectees et intelligentes."},
        {"id": "774_7", "type": "qcm",
         "question": "Quelle technologie d'impression permet de creer des objets physiques couche par couche a partir d'un modele numerique 3D ?",
         "options": [
             "Imprimerie offset",
             "Impression 3D (fabrication additive)",
             "Gravure laser",
             "Decoupe numerique"
         ],
         "correct_option": "Impression 3D (fabrication additive)",
         "explanation": "L'impression 3D (FDM, SLA, SLS...) cree des objets en deposant ou solidifiant de la matiere couche par couche a partir d'un fichier CAO."},
        {"id": "774_8", "type": "vrai-faux",
         "question": "Les nanotechnologies permettent de manipuler la matiere a l'echelle du nanometre (10^-9 metre).",
         "correct": True,
         "explanation": "Les nanotechnologies manipulent la matiere a l'echelle atomique et moleculaire (1-100 nm) pour creer de nouveaux materiaux et dispositifs aux proprietes inedites."},
    ]),

    (775, "Les objets techniques communicants et l'IoT", "Technologie", "3eme", [
        {"id": "775_1", "type": "qcm",
         "question": "Que signifie IoT (Internet of Things) en francais ?",
         "options": [
             "Informatique ou Technologie",
             "Internet des objets",
             "Interconnexion des terminaux",
             "Integration des outils technologiques"
         ],
         "correct_option": "Internet des objets",
         "explanation": "L'IoT (Internet of Things / Internet des objets) designe l'ensemble des objets physiques connectes a internet qui collectent et echangent des donnees."},
        {"id": "775_2", "type": "vrai-faux",
         "question": "Un thermostat connecte est un exemple d'objet de l'Internet des objets.",
         "correct": True,
         "explanation": "Un thermostat connecte (ex. Nest) est pilotable a distance via smartphone, apprend les habitudes de l'utilisateur et optimise la consommation energetique."},
        {"id": "775_3", "type": "texte",
         "question": "Comment appelle-t-on un mini-ordinateur programmable, de la taille d'une carte de credit, utilise pour creer des objets connectes et des prototypes ?",
         "correct_answer": "Raspberry Pi",
         "explanation": "Le Raspberry Pi est un ordinateur monocarte abordable et programmable, tres utilise en education et pour les projets IoT et de domotique."},
        {"id": "775_4", "type": "qcm",
         "question": "Quel protocole de communication sans fil courte portee est le plus utilise par les objets connectes dans une maison ?",
         "options": [
             "Ethernet",
             "Wi-Fi et Bluetooth",
             "Fibre optique",
             "4G/5G uniquement"
         ],
         "correct_option": "Wi-Fi et Bluetooth",
         "explanation": "Le Wi-Fi (reseau local sans fil) et le Bluetooth (courte portee) sont les protocoles les plus repandus pour connecter les objets domestiques (enceintes, montres, capteurs)."},
        {"id": "775_5", "type": "vrai-faux",
         "question": "Les objets connectes peuvent presenter des risques pour la vie privee s'ils ne sont pas securises.",
         "correct": True,
         "explanation": "Les objets connectes mal securises peuvent etre hackes, exposant les donnees personnelles ou permettant une surveillance non autorisee des utilisateurs."},
        {"id": "775_6", "type": "texte",
         "question": "Comment appelle-t-on l'ensemble des technologies permettant de controler l'eclairage, le chauffage et la securite d'une maison depuis un smartphone ?",
         "correct_answer": "domotique",
         "explanation": "La domotique (du latin 'domus' = maison) integre l'automatisation et la communication pour controler et optimiser les equipements d'un habitat."},
        {"id": "775_7", "type": "qcm",
         "question": "Qu'est-ce qu'un capteur dans un objet technique ?",
         "options": [
             "Un composant qui produit de l'energie",
             "Un composant qui mesure une grandeur physique et la convertit en signal exploitable",
             "Un composant qui stocke des donnees",
             "Un composant qui affiche des informations"
         ],
         "correct_option": "Un composant qui mesure une grandeur physique et la convertit en signal exploitable",
         "explanation": "Un capteur (temperature, luminosite, acceleration, CO2...) mesure une grandeur physique et la convertit en signal electrique numerique traitable par un microcontroleur."},
        {"id": "775_8", "type": "vrai-faux",
         "question": "Un actionneur est un composant qui produit une action physique a partir d'un signal de commande.",
         "correct": True,
         "explanation": "Un actionneur (moteur, servomoteur, LED, buzzer, pompe) convertit un signal electrique de commande en action physique (mouvement, lumiere, son)."},
    ]),

    # =========================================================
    # BLOC 2 - MATERIAUX ET PROCEDES DE FABRICATION (776-780)
    # =========================================================
    (776, "Les materiaux : proprietes et choix", "Technologie", "3eme", [
        {"id": "776_1", "type": "qcm",
         "question": "Quelle propriete d'un materiau decrit sa capacite a resister a une force qui tend a le dechirer ?",
         "options": [
             "Durete",
             "Resistance a la traction",
             "Conductivite thermique",
             "Transparence"
         ],
         "correct_option": "Resistance a la traction",
         "explanation": "La resistance a la traction (Rm) mesure la contrainte maximale qu'un materiau peut supporter avant rupture sous un effort d'etirement."},
        {"id": "776_2", "type": "vrai-faux",
         "question": "Le verre est un materiau amorphe (sans structure cristalline reguliere).",
         "correct": True,
         "explanation": "Le verre est un solide amorphe (structure desordonnee a l'echelle atomique), contrairement aux metaux qui ont une structure cristalline."},
        {"id": "776_3", "type": "texte",
         "question": "Comment appelle-t-on un materiau compose de deux ou plusieurs materiaux differents combines pour obtenir des proprietes superieures ?",
         "correct_answer": "materiau composite",
         "explanation": "Un materiau composite (ex. fibre de carbone + resine, beton arme) associe deux materiaux pour combiner leurs qualites (legeretÃ© + resistance, rigidite + flexibilite)."},
        {"id": "776_4", "type": "qcm",
         "question": "Quel materiau est principalement utilise pour les circuits electroniques (cartes electroniques, processeurs) en raison de ses proprietes semi-conductrices ?",
         "options": [
             "Cuivre",
             "Silicium",
             "Aluminium",
             "Plastique"
         ],
         "correct_option": "Silicium",
         "explanation": "Le silicium est le semi-conducteur de base de l'electronique moderne ; sa conductivite peut etre modulee par dopage, permettant de creer transistors et circuits integres."},
        {"id": "776_5", "type": "vrai-faux",
         "question": "L'aluminium est plus leger que l'acier, ce qui le rend utile dans l'aeronautique.",
         "correct": True,
         "explanation": "L'aluminium (densite 2,7 kg/dm3) est 3 fois plus leger que l'acier (7,8 kg/dm3) tout en offrant une bonne resistance mecanique, ideal pour l'aeronautique et l'automobile."},
        {"id": "776_6", "type": "texte",
         "question": "Comment appelle-t-on le traitement thermique qui consiste a chauffer puis refroidir rapidement un acier pour le rendre plus dur ?",
         "correct_answer": "trempe",
         "explanation": "La trempe est un traitement thermique (chauffage au rouge puis refroidissement brutal dans l'eau ou l'huile) qui augmente la durete et la resistance de l'acier."},
        {"id": "776_7", "type": "qcm",
         "question": "Quel critere est determinant dans le choix d'un materiau pour une application donnee ?",
         "options": [
             "Sa couleur uniquement",
             "L'adequation entre ses proprietes (mecaniques, thermiques, chimiques) et les contraintes d'usage",
             "Son prix toujours le plus bas",
             "Sa disponibilite uniquement"
         ],
         "correct_option": "L'adequation entre ses proprietes (mecaniques, thermiques, chimiques) et les contraintes d'usage",
         "explanation": "Le choix d'un materiau integre plusieurs criteres : proprietes techniques, cout, disponibilite, recyclabilite et impact environnemental."},
        {"id": "776_8", "type": "vrai-faux",
         "question": "Les biopolymeres sont des plastiques fabriques a partir de matieres premieres renouvelables (amidon, cellulose).",
         "correct": True,
         "explanation": "Les biopolymeres (PLA, PHA, amidon thermoplastique) sont fabriques a partir de ressources biologiques et peuvent etre biodegradables, reduisant la dependance au petrole."},
    ]),

    (777, "Les procedes de fabrication et de mise en forme", "Technologie", "3eme", [
        {"id": "777_1", "type": "qcm",
         "question": "Quel procede de fabrication consiste a couper, percer ou fraiser un bloc de matiere pour lui donner une forme ?",
         "options": [
             "Moulage par injection",
             "Usinage par enlevement de matiere",
             "Impression 3D",
             "Thermoformage"
         ],
         "correct_option": "Usinage par enlevement de matiere",
         "explanation": "L'usinage (fraisage, tournage, percage) enleve de la matiere par coupe pour obtenir la forme souhaitee ; il convient aux metaux, bois et plastiques."},
        {"id": "777_2", "type": "vrai-faux",
         "question": "Le moulage par injection est utilise pour fabriquer en grande serie des pieces en plastique.",
         "correct": True,
         "explanation": "L'injection plastique consiste a injecter du plastique fondu sous pression dans un moule ; c'est la technique la plus utilisee pour la fabrication en serie de pieces plastiques."},
        {"id": "777_3", "type": "texte",
         "question": "Comment appelle-t-on le procede de fabrication qui consiste a chauffer une feuille de plastique et a la former sur un moule par aspiration ou pression ?",
         "correct_answer": "thermoformage",
         "explanation": "Le thermoformage est utilise pour fabriquer des barquettes alimentaires, blister d'emballage et pieces de carrosserie en chauffant une feuille plastique et en la moulant."},
        {"id": "777_4", "type": "qcm",
         "question": "Quel procede d'assemblage cree une liaison permanente entre deux pieces metalliques en les faisant fondre localement ?",
         "options": [
             "Vissage",
             "Soudage",
             "Collage",
             "Emboitement"
         ],
         "correct_option": "Soudage",
         "explanation": "Le soudage (MIG, TIG, arc electrique) cree une liaison metallurgique permanente entre pieces metalliques en les fondant localement avec ou sans metal d'apport."},
        {"id": "777_5", "type": "vrai-faux",
         "question": "Une machine a commande numerique (CNC) est programmee pour realiser automatiquement des operations d'usinage.",
         "correct": True,
         "explanation": "Les machines CNC (Computer Numerical Control) executent automatiquement des programmes d'usinage (code G), garantissant precision et reproductibilite."},
        {"id": "777_6", "type": "texte",
         "question": "Comment appelle-t-on le procede qui consiste a deposer une couche de metal sur un objet par voie electrolytique ?",
         "correct_answer": "electrodeposition",
         "explanation": "L'electrodeposition (galvanoplastie, chromage, nickelage) depose une fine couche metallique sur un objet par electrlyse pour le proteger ou l'embellir."},
        {"id": "777_7", "type": "qcm",
         "question": "Qu'est-ce que la fabrication additive ?",
         "options": [
             "L'ajout d'additifs chimiques dans les materiaux",
             "Un procede qui construit un objet en ajoutant de la matiere couche par couche",
             "L'assemblage de pieces fabriquees separement",
             "La fabrication de produits alimentaires"
         ],
         "correct_option": "Un procede qui construit un objet en ajoutant de la matiere couche par couche",
         "explanation": "La fabrication additive (impression 3D) est l'opposee de l'usinage : elle construit l'objet en ajoutant de la matiere, reduisant les dechets et permettant des formes complexes."},
        {"id": "777_8", "type": "vrai-faux",
         "question": "Le decoupage laser permet de couper des materiaux avec une tres grande precision grace a un faisceau laser concentre.",
         "correct": True,
         "explanation": "La decoupe laser (CO2, fibre) utilise un faisceau lumineux intense pour couper ou graver des materiaux (acier, bois, acrylique) avec une precision de l'ordre du dixieme de millimetre."},
    ]),

    (778, "Le dessin technique et la representation des objets", "Technologie", "3eme", [
        {"id": "778_1", "type": "qcm",
         "question": "Combien de vues standard comporte generalement un dessin technique en representation orthogonale (methode europeenne) ?",
         "options": [
             "1 vue",
             "2 vues",
             "3 vues (face, dessus, cote)",
             "6 vues"
         ],
         "correct_option": "3 vues (face, dessus, cote)",
         "explanation": "Le dessin technique standard comporte 3 vues principales : la vue de face, la vue de dessus et la vue de cote gauche (methode europeenne ou du 1er diedre)."},
        {"id": "778_2", "type": "vrai-faux",
         "question": "L'echelle 1:2 sur un dessin technique signifie que l'objet est represente deux fois plus grand que sa taille reelle.",
         "correct": False,
         "explanation": "L'echelle 1:2 signifie que le dessin est 2 fois plus petit que l'objet reel (reduction). L'echelle 2:1 agrandit l'objet 2 fois."},
        {"id": "778_3", "type": "texte",
         "question": "Comment appelle-t-on la vue qui montre l'interieur d'un objet en le coupant par un plan imaginaire ?",
         "correct_answer": "vue en coupe",
         "explanation": "La vue en coupe (ou section) est obtenue en 'coupant' virtuellement l'objet par un plan pour visualiser son interieur et les details caches."},
        {"id": "778_4", "type": "qcm",
         "question": "Quel type de ligne est utilise en dessin technique pour representer les contours visibles d'un objet ?",
         "options": [
             "Ligne en tirets fins",
             "Ligne en tirets point",
             "Ligne continue forte",
             "Ligne continue fine"
         ],
         "correct_option": "Ligne continue forte",
         "explanation": "Les contours visibles sont representes par des lignes continues fortes (epaisses). Les contours caches utilisent des tirets fins et les axes des lignes mixtes."},
        {"id": "778_5", "type": "vrai-faux",
         "question": "La cotation sur un dessin technique indique les dimensions de l'objet en millimetres.",
         "correct": True,
         "explanation": "La cotation (normes ISO 129) indique les dimensions, tolerances et references geometriques necessaires a la fabrication ; en technologie, l'unite courante est le millimetre."},
        {"id": "778_6", "type": "texte",
         "question": "Comment appelle-t-on la representation d'un objet en 3D avec des lignes obliques a 45 degrees, tres utilisee en technologie ?",
         "correct_answer": "perspective cavaliere",
         "explanation": "La perspective cavaliere represente un objet en 3D en traÃ§ant les aretes de profondeur a 45Â° et a echelle reduite (souvent 0,5), facilitant la visualisation."},
        {"id": "778_7", "type": "qcm",
         "question": "A quoi sert un tolerancement geometrique sur un dessin technique ?",
         "options": [
             "A indiquer la couleur de la piece",
             "A preciser les ecarts maximaux admissibles de forme ou de position d'un element",
             "A calculer le poids de la piece",
             "A indiquer le materiau"
         ],
         "correct_option": "A preciser les ecarts maximaux admissibles de forme ou de position d'un element",
         "explanation": "Le tolerancement geometrique (norme ISO 1101) fixe les limites admissibles de forme (rectitude, planeite) et de position (perpendicularite, parallelisme) pour garantir l'interchangeabilite."},
        {"id": "778_8", "type": "vrai-faux",
         "question": "Les logiciels de CAO (Conception Assistee par Ordinateur) permettent de creer directement des modeles 3D et d'en extraire des plans 2D.",
         "correct": True,
         "explanation": "Les logiciels CAO (SolidWorks, FreeCAD, CATIA) permettent de modeliser en 3D puis de generer automatiquement les mises en plan 2D avec cotation."},
    ]),

    (779, "Les liaisons et assemblages mecaniques", "Technologie", "3eme", [
        {"id": "779_1", "type": "qcm",
         "question": "Qu'est-ce qu'une liaison pivot en mecanique ?",
         "options": [
             "Une liaison qui bloque tous les mouvements",
             "Une liaison qui autorise uniquement la rotation autour d'un axe",
             "Une liaison qui autorise uniquement la translation",
             "Une liaison qui autorise tous les mouvements"
         ],
         "correct_option": "Une liaison qui autorise uniquement la rotation autour d'un axe",
         "explanation": "La liaison pivot (ex. charniere, roulement) n'autorise qu'une seule mobilite : la rotation autour d'un axe fixe."},
        {"id": "779_2", "type": "vrai-faux",
         "question": "Un assemblage par vissage est une liaison demontable.",
         "correct": True,
         "explanation": "L'assemblage par vis, boulons ou ecrous est demontable (on peut desserrer et remonter) ; contrairement au soudage ou collage qui sont des liaisons permanentes."},
        {"id": "779_3", "type": "texte",
         "question": "Comment appelle-t-on la liaison mecanique qui bloque completement deux pieces en interdisant tout mouvement relatif ?",
         "correct_answer": "encastrement",
         "explanation": "L'encastrement (liaison complete) supprime les 6 degres de liberte (3 translations + 3 rotations) entre deux pieces ; ex. une poutre scelle dans un mur."},
        {"id": "779_4", "type": "qcm",
         "question": "Quel type d'assemblage est utilise pour assembler des planches de bois sans colle ni vis ?",
         "options": [
             "Soudage",
             "Assemblage par tenon-mortaise",
             "Rivetage",
             "Frettage"
         ],
         "correct_option": "Assemblage par tenon-mortaise",
         "explanation": "L'assemblage tenon-mortaise est une technique de menuiserie ancestrale qui emboite une saillie (tenon) dans une cavite (mortaise) sans elements d'assemblage externes."},
        {"id": "779_5", "type": "vrai-faux",
         "question": "Un roulement a billes reduit le frottement entre deux pieces en rotation.",
         "correct": True,
         "explanation": "Le roulement a billes (ou rouleaux, aiguilles) remplace le frottement de glissement par un frottement de roulement, reduisant considerablement les pertes d'energie et l'usure."},
        {"id": "779_6", "type": "texte",
         "question": "Comment appelle-t-on l'assemblage qui utilise des tiges metalliques deformees a froid pour lier des toles entre elles ?",
         "correct_answer": "rivetage",
         "explanation": "Le rivetage est un assemblage permanent par deformation plastique de rivets metalliques ; tres utilise en aeronautique pour assembler les structures aluminium."},
        {"id": "779_7", "type": "qcm",
         "question": "Quelle est la fonction d'un joint d'etancheite dans un assemblage ?",
         "options": [
             "Transmettre un mouvement de rotation",
             "Empecher le passage de fluides ou de gaz a l'interface de deux pieces",
             "Absorber les vibrations uniquement",
             "Mesurer la pression interne"
         ],
         "correct_option": "Empecher le passage de fluides ou de gaz a l'interface de deux pieces",
         "explanation": "Les joints d'etancheite (O-ring, joints plats, joints toriques) s'interposent entre deux pieces pour empecher les fuites de fluides ou l'entree de contaminants."},
        {"id": "779_8", "type": "vrai-faux",
         "question": "Une cle de voute est un element structural qui permet de transmettre les efforts dans une arche en compression.",
         "correct": True,
         "explanation": "La cle de voute est la pierre centrale d'une arche qui distribue les charges en compression vers les piedroits ; c'est l'un des principes structuraux les plus anciens."},
    ]),

    (780, "Controle qualite et metrologie", "Technologie", "3eme", [
        {"id": "780_1", "type": "qcm",
         "question": "Qu'est-ce que la metrologie en technologie industrielle ?",
         "options": [
             "L'etude de la meteorologie",
             "La science de la mesure et du controle dimensionnel",
             "Le calcul des couts de production",
             "La gestion des stocks de materiaux"
         ],
         "correct_option": "La science de la mesure et du controle dimensionnel",
         "explanation": "La metrologie est la science des mesures ; en industrie, elle garantit que les pieces fabriquees respectent les tolerances du dessin technique."},
        {"id": "780_2", "type": "vrai-faux",
         "question": "Un pied a coulisse permet de mesurer des dimensions avec une precision de 0,1 mm ou moins.",
         "correct": True,
         "explanation": "Un pied a coulisse (vernier) permet des mesures avec une resolution de 0,1 mm (vernier classique) ou 0,01 mm (vernier 50 graduations ou numerique)."},
        {"id": "780_3", "type": "texte",
         "question": "Comment appelle-t-on le document qui liste et enregistre toutes les operations de controle effectuees sur une piece ou un produit ?",
         "correct_answer": "fiche de controle qualite",
         "explanation": "La fiche de controle qualite (ou rapport de controle) enregistre les mesures realisees, les comparant aux tolerances du CdCF pour valider ou rejeter une piece."},
        {"id": "780_4", "type": "qcm",
         "question": "Qu'est-ce que l'incertitude de mesure ?",
         "options": [
             "Une erreur de fabrication",
             "L'intervalle de confiance autour d'une valeur mesuree qui quantifie le doute sur le resultat",
             "La difference entre deux pieces identiques",
             "L'ecart entre le dessin et la piece reelle"
         ],
         "correct_option": "L'intervalle de confiance autour d'une valeur mesuree qui quantifie le doute sur le resultat",
         "explanation": "L'incertitude de mesure exprime le doute incontournable sur tout resultat ; elle est liee a l'instrument, a l'operateur et aux conditions de mesure."},
        {"id": "780_5", "type": "vrai-faux",
         "question": "Une piece hors tolerance doit etre systematiquement mise au rebut.",
         "correct": False,
         "explanation": "Une piece hors tolerance peut etre retravaillee (reprise d'usinage) si possible, ou rebutee si la correction est impossible ou trop couteuse."},
        {"id": "780_6", "type": "texte",
         "question": "Comment appelle-t-on le processus qui verifie qu'un produit final satisfait toutes les exigences du cahier des charges ?",
         "correct_answer": "validation",
         "explanation": "La validation est la derniere etape du cycle de conception ; elle confirme que le produit repond bien aux besoins de l'utilisateur definis dans le CdCF."},
        {"id": "780_7", "type": "qcm",
         "question": "Quel instrument de mesure permet de mesurer des diametres interieurs et exterieurs avec une tres haute precision (micrometre) ?",
         "options": [
             "Reglet",
             "Pied a coulisse",
             "Palmer (micrometres)",
             "Jauge de profondeur"
         ],
         "correct_option": "Palmer (micrometres)",
         "explanation": "Le palmer (ou micrometres) permet des mesures avec une resolution de 0,01 mm voire 0,001 mm, ideal pour le controle de precision des pieces mecaniques."},
        {"id": "780_8", "type": "vrai-faux",
         "question": "La traÃ§abilite consiste a enregistrer l'historique complet d'une piece, de sa fabrication a son utilisation.",
         "correct": True,
         "explanation": "La traÃ§abilite (lot, date, operateur, mesures) permet d'identifier l'origine d'un defaut et de rappeler les produits defectueux ; elle est obligatoire dans des secteurs comme l'aeronautique ou la medecine."},
    ]),

    # =========================================================
    # BLOC 3 - SYSTEMES TECHNIQUES ET AUTOMATISMES (781-786)
    # =========================================================
    (781, "Les systemes techniques : structure et fonctionnement", "Technologie", "3eme", [
        {"id": "781_1", "type": "qcm",
         "question": "Qu'est-ce qu'un systeme technique ?",
         "options": [
             "Un outil simple avec une seule fonction",
             "Un ensemble d'elements en interaction organise pour remplir une fonction globale",
             "Un composant electronique unique",
             "Un logiciel de gestion"
         ],
         "correct_option": "Un ensemble d'elements en interaction organise pour remplir une fonction globale",
         "explanation": "Un systeme technique est un ensemble structure de composants mecaniques, electroniques et logiciels qui cooperent pour remplir une ou plusieurs fonctions."},
        {"id": "781_2", "type": "vrai-faux",
         "question": "Dans un systeme technique, on distingue la partie operative (qui agit) et la partie commande (qui decide).",
         "correct": True,
         "explanation": "La partie operative (actionneurs, mecanismes) execute les actions physiques ; la partie commande (controleur, programme) prend les decisions et envoie les ordres."},
        {"id": "781_3", "type": "texte",
         "question": "Comment appelle-t-on le schema representant le flux d'information et d'energie dans un systeme technique ?",
         "correct_answer": "schema blocs fonctionnel",
         "explanation": "Le schema blocs fonctionnel (ou diagramme de flux) represente les entrees (energie, matiere, information) et sorties de chaque sous-systeme et leurs interactions."},
        {"id": "781_4", "type": "qcm",
         "question": "Qu'est-ce qu'une boucle de retroaction (feedback) dans un systeme automatise ?",
         "options": [
             "Un court-circuit electrique",
             "Un mecanisme qui compare la sortie a la consigne pour corriger les ecarts",
             "Un composant de stockage d'energie",
             "Un programme qui reduit la vitesse"
         ],
         "correct_option": "Un mecanisme qui compare la sortie a la consigne pour corriger les ecarts",
         "explanation": "La retroaction (asservissement) mesure la sortie du systeme et la compare a la consigne ; l'ecart (erreur) est utilise pour corriger la commande et atteindre la valeur desiree."},
        {"id": "781_5", "type": "vrai-faux",
         "question": "Un systeme en boucle ouverte ne mesure pas sa sortie pour se corriger.",
         "correct": True,
         "explanation": "Un systeme en boucle ouverte execute ses instructions sans mesurer le resultat ; il est simple mais sensible aux perturbations (ex. minuterie de four sans thermostat)."},
        {"id": "781_6", "type": "texte",
         "question": "Comment appelle-t-on le composant electronique programmable qui joue le role de cerveau dans un systeme automatise moderne ?",
         "correct_answer": "microcontroleur",
         "explanation": "Un microcontroleur (Arduino, PIC, STM32) integre processeur, memoire et peripheriques d'E/S sur une seule puce ; il lit les capteurs et commande les actionneurs."},
        {"id": "781_7", "type": "qcm",
         "question": "Qu'est-ce qu'un automate programmable industriel (API/PLC) ?",
         "options": [
             "Un robot humanoide",
             "Un ordinateur specialise et robuste programme pour controler des systemes industriels",
             "Un logiciel de gestion d'entreprise",
             "Un capteur de mesure de temperature"
         ],
         "correct_option": "Un ordinateur specialise et robuste programme pour controler des systemes industriels",
         "explanation": "L'API (PLC en anglais) est un calculateur industriel robuste programme en langage ladder ou structuredText pour controler des machines, convoyeurs ou lignes de production."},
        {"id": "781_8", "type": "vrai-faux",
         "question": "Un systeme SCADA permet de superviser et controler a distance des installations industrielles.",
         "correct": True,
         "explanation": "SCADA (Supervisory Control And Data Acquisition) est un systeme de supervision qui collecte des donnees en temps reel et permet le controle a distance de processus industriels."},
    ]),

    (782, "Les automatismes : capteurs et actionneurs", "Technologie", "3eme", [
        {"id": "782_1", "type": "qcm",
         "question": "Quel type de capteur mesure la distance en emettant des ultrasons et en mesurant le temps de retour de l'echo ?",
         "options": [
             "Capteur infrarouge",
             "Capteur ultrasonique",
             "Capteur magnetique",
             "Capteur capacitif"
         ],
         "correct_option": "Capteur ultrasonique",
         "explanation": "Le capteur ultrasonique (ex. HC-SR04) emet une onde sonore et mesure le temps de retour pour calculer la distance par la formule d = v Ã— t/2."},
        {"id": "782_2", "type": "vrai-faux",
         "question": "Un detecteur inductif peut detecter la presence d'objets metalliques sans contact.",
         "correct": True,
         "explanation": "Le detecteur inductif cree un champ magnetique oscillant ; la presence d'un metal modifie ce champ et declenche le signal de detection, sans contact physique."},
        {"id": "782_3", "type": "texte",
         "question": "Comment appelle-t-on un capteur qui convertit la lumiere en signal electrique pour detecter la presence d'un objet ?",
         "correct_answer": "capteur photoelectrique",
         "explanation": "Le capteur photoelectrique (barriere optique, reflex, diffus) utilise un faisceau lumineux (visible ou infrarouge) pour detecter la presence d'objets."},
        {"id": "782_4", "type": "qcm",
         "question": "Quel actionneur convertit l'energie electrique en mouvement de rotation continue ?",
         "options": [
             "Verin pneumatique",
             "Moteur electrique",
             "Servomoteur",
             "Solenoid"
         ],
         "correct_option": "Moteur electrique",
         "explanation": "Le moteur electrique (DC, AC, pas-a-pas) convertit l'energie electrique en energie mecanique de rotation ; il equipe ventilateurs, pompes, robots, vehicules."},
        {"id": "782_5", "type": "vrai-faux",
         "question": "Un servomoteur peut controler precisement la position angulaire d'un axe.",
         "correct": True,
         "explanation": "Un servomoteur associe un moteur et un systeme d'asservissement en position ; il est programme pour atteindre et maintenir un angle precis, utilise en robotique et RC."},
        {"id": "782_6", "type": "texte",
         "question": "Comment appelle-t-on un actionneur lineaire qui utilise l'air comprime pour produire un mouvement de translation ?",
         "correct_answer": "verin pneumatique",
         "explanation": "Le verin pneumatique utilise la pression d'air pour deplacer un piston lineairement ; tres utilise dans l'automatisation industrielle pour des mouvements rapides."},
        {"id": "782_7", "type": "qcm",
         "question": "Qu'est-ce qu'un variateur de vitesse dans un systeme motorise ?",
         "options": [
             "Un frein mecanique",
             "Un composant electronique qui regle la vitesse d'un moteur en faisant varier la frequence ou la tension",
             "Un type de capteur de vitesse",
             "Une boite de vitesses mecanique"
         ],
         "correct_option": "Un composant electronique qui regle la vitesse d'un moteur en faisant varier la frequence ou la tension",
         "explanation": "Le variateur electronique (VFD) permet de controler la vitesse d'un moteur AC en modulant la frequence d'alimentation, economisant l'energie."},
        {"id": "782_8", "type": "vrai-faux",
         "question": "Un capteur de temperature de type thermistance voit sa resistance electrique varier avec la temperature.",
         "correct": True,
         "explanation": "Une thermistance (NTC ou PTC) est un capteur dont la resistance varie avec la temperature ; une NTC voit sa resistance diminuer quand la temperature augmente."},
    ]),

    (783, "Les systemes automatises : logique et programmes", "Technologie", "3eme", [
        {"id": "783_1", "type": "qcm",
         "question": "Qu'est-ce qu'un diagramme d'etat (state machine) dans la programmation d'un systeme automatise ?",
         "options": [
             "Un schema de cablage electrique",
             "Une representation graphique des etats d'un systeme et des transitions entre ces etats",
             "Un tableau des composants du systeme",
             "Un planning de maintenance"
         ],
         "correct_option": "Une representation graphique des etats d'un systeme et des transitions entre ces etats",
         "explanation": "Un diagramme d'etat modelise le comportement d'un systeme en decrivant ses etats possibles (veille, marche, alarme) et les evenements qui declenchent les transitions."},
        {"id": "783_2", "type": "vrai-faux",
         "question": "En logique combinatoire, la sortie depend uniquement de l'etat actuel des entrees.",
         "correct": True,
         "explanation": "En logique combinatoire (portes AND, OR, NOT), la sortie est determinee instantanement par les entrees presentes, sans memoire des etats passes."},
        {"id": "783_3", "type": "texte",
         "question": "Comment appelle-t-on le langage de programmation graphique utilise pour les automates industriels qui ressemble a un schema electrique ?",
         "correct_answer": "langage ladder",
         "explanation": "Le langage Ladder (echelle) est un langage de programmation graphique pour API, avec des contacts (entrees) et bobines (sorties) arranges comme un schema de cablage."},
        {"id": "783_4", "type": "qcm",
         "question": "Quelle est la valeur d'une porte logique AND avec les entrees A=1 et B=0 ?",
         "options": ["0", "1", "Indefini", "2"],
         "correct_option": "0",
         "explanation": "La porte AND (ET logique) donne 1 uniquement si TOUTES ses entrees sont a 1 : 1 AND 0 = 0."},
        {"id": "783_5", "type": "vrai-faux",
         "question": "Un grafcet est un outil graphique permettant de decrire le comportement sequentiel d'un automatisme.",
         "correct": True,
         "explanation": "Le Grafcet (Graphe de Commande Etapes-Transitions) est un outil de specification des automatismes sequentiels, normalise IEC 60848, tres utilise en industrie."},
        {"id": "783_6", "type": "texte",
         "question": "Quel est le resultat de l'operation logique NOT(1) ?",
         "correct_answer": "0",
         "explanation": "La porte NOT (NON logique) inverse la valeur : NOT(1) = 0 et NOT(0) = 1."},
        {"id": "783_7", "type": "qcm",
         "question": "Dans un systeme automatise, qu'est-ce qu'une temporisation ?",
         "options": [
             "Un capteur de temps",
             "Une fonction qui retarde ou limite dans le temps une action du systeme",
             "Un moteur a vitesse variable",
             "Un capteur de pression"
         ],
         "correct_option": "Une fonction qui retarde ou limite dans le temps une action du systeme",
         "explanation": "Une temporisation (timer) introduit un delai avant ou apres une action ; ex. une lumiere qui s'eteint 5 minutes apres la detection de presence."},
        {"id": "783_8", "type": "vrai-faux",
         "question": "Un systeme de securite industriel doit privilegier l'etat sur lors de toute defaillance ou perte d'alimentation.",
         "correct": True,
         "explanation": "Le principe de securite positive (fail-safe) impose que tout systeme critique revienne a un etat sur (machine arretee, barriere fermee) en cas de defaillance electrique."},
    ]),

    (784, "La robotique et les systemes mecatroniques", "Technologie", "3eme", [
        {"id": "784_1", "type": "qcm",
         "question": "Qu'est-ce que la mecatronique ?",
         "options": [
             "La mecanique des fluides",
             "L'integration de la mecanique, de l'electronique et de l'informatique pour concevoir des systemes intelligents",
             "La fabrication de pieces metalliques",
             "L'electronique analogique"
         ],
         "correct_option": "L'integration de la mecanique, de l'electronique et de l'informatique pour concevoir des systemes intelligents",
         "explanation": "La mecatronique (mecanique + electronique + informatique) est a la base des systemes modernes : voitures, robots, drones, smartphones."},
        {"id": "784_2", "type": "vrai-faux",
         "question": "Un robot industriel est generalement programme pour repeter des taches avec une grande precision.",
         "correct": True,
         "explanation": "Les robots industriels (bras articules KUKA, Fanuc, ABB) sont programmes pour effectuer des taches repetitives (soudage, assemblage, peinture) avec une precision sub-millimetrique."},
        {"id": "784_3", "type": "texte",
         "question": "Comment appelle-t-on un robot capable de se deplacer dans un environnement et d'interagir avec des humains ?",
         "correct_answer": "robot mobile",
         "explanation": "Un robot mobile (AGV, robot aspirateur, drone) se deplace de maniere autonome ou semi-autonome dans son environnement pour accomplir des taches."},
        {"id": "784_4", "type": "qcm",
         "question": "Combien de degres de liberte possede generalement un bras robotique industriel pour une grande souplesse de mouvement ?",
         "options": ["2 DDL", "3 DDL", "6 DDL", "12 DDL"],
         "correct_option": "6 DDL",
         "explanation": "Un bras robotique a 6 degres de liberte (DDL) peut atteindre n'importe quelle position et orientation dans son espace de travail, comme le bras humain."},
        {"id": "784_5", "type": "vrai-faux",
         "question": "Les drones civils sont soumis a reglementation en France et doivent etre enregistres au-dela d'une certaine masse.",
         "correct": True,
         "explanation": "En France, les drones de plus de 800g doivent etre enregistres sur AlphaTango (DSAC) et respecter des regles de vol (altitude max, zones interdites, assurance)."},
        {"id": "784_6", "type": "texte",
         "question": "Comment appelle-t-on la technologie qui permet a un robot de percevoir et interpreter son environnement par traitement d'images ?",
         "correct_answer": "vision artificielle",
         "explanation": "La vision artificielle (computer vision) utilise des cameras et des algorithmes d'IA pour detecter, reconnaitre et localiser des objets dans l'environnement du robot."},
        {"id": "784_7", "type": "qcm",
         "question": "Qu'est-ce que la cobotique ?",
         "options": [
             "La programmation de robots a distance",
             "La collaboration directe et securisee entre robots et humains sur un meme espace de travail",
             "La fabrication de robots en serie",
             "L'utilisation de robots sous-marins"
         ],
         "correct_option": "La collaboration directe et securisee entre robots et humains sur un meme espace de travail",
         "explanation": "La cobotique (collaborative robotics) conÃ§oit des robots (cobots) equipes de capteurs de force pour travailler en securite a cote des humains sans cage de protection."},
        {"id": "784_8", "type": "vrai-faux",
         "question": "L'intelligence artificielle permet aux robots de s'adapter a des situations non prevues lors de leur programmation initiale.",
         "correct": True,
         "explanation": "Grace a l'apprentissage automatique (machine learning), les robots modernes peuvent apprendre de nouvelles taches, s'adapter a des variations et ameliorer leurs performances."},
    ]),

    (785, "Les transmissions de mouvement", "Technologie", "3eme", [
        {"id": "785_1", "type": "qcm",
         "question": "Quel systeme de transmission transforme un mouvement de rotation en mouvement de translation ?",
         "options": [
             "Poulies-courroies",
             "Vis-ecrou ou cremaillere-pignon",
             "Engrenages cylindriques",
             "Accouplement elastique"
         ],
         "correct_option": "Vis-ecrou ou cremaillere-pignon",
         "explanation": "La vis-ecrou et le pignon-cremaillere convertissent la rotation en translation lineaire ; ils equippent machines-outils, verins mecaniques et directions de vehicules."},
        {"id": "785_2", "type": "vrai-faux",
         "question": "Un rapport de transmission superieur a 1 signifie que la vitesse de sortie est plus grande que la vitesse d'entree.",
         "correct": True,
         "explanation": "Rapport de transmission r = n_sortie / n_entree ; si r > 1, il y a multiplication de vitesse (et reduction du couple) ; si r < 1, reduction de vitesse (et multiplication du couple)."},
        {"id": "785_3", "type": "texte",
         "question": "Comment appelle-t-on le systeme de transmission utilisant deux roues dentees en contact direct pour transmettre un mouvement de rotation ?",
         "correct_answer": "engrenages",
         "explanation": "Les engrenages (pignons) transmettent un mouvement de rotation avec un rapport de vitesse et de couple defini par le rapport du nombre de dents des roues."},
        {"id": "785_4", "type": "qcm",
         "question": "Quel mecanisme permet de reduire la vitesse et d'augmenter le couple d'un moteur electrique ?",
         "options": [
             "Un accouplement direct",
             "Un reducteur de vitesse (boite de vitesses)",
             "Un embrayage",
             "Un variateur de frequence"
         ],
         "correct_option": "Un reducteur de vitesse (boite de vitesses)",
         "explanation": "Le reducteur (train d'engrenages) diminue la vitesse de rotation et augmente proportionnellement le couple, permettant a un petit moteur de developper une grande force."},
        {"id": "785_5", "type": "vrai-faux",
         "question": "Une courroie trapezoidale glisse moins qu'une courroie plate sous forte charge.",
         "correct": True,
         "explanation": "La courroie trapezoidale (section en V) s'enclave dans la gorge de la poulie, augmentant l'adherence et reduisant le glissement sous forte charge."},
        {"id": "785_6", "type": "texte",
         "question": "Comment appelle-t-on le rapport entre la force utile produite et la force motrice dans un mecanisme de transmission ?",
         "correct_answer": "rendement mecanique",
         "explanation": "Le rendement eta = P_utile / P_motrice (toujours < 1 a cause des frottements) mesure l'efficacite d'un mecanisme de transmission."},
        {"id": "785_7", "type": "qcm",
         "question": "Quel systeme de transmission est utilise pour synchroniser les mouvements entre deux axes distants (ex. distribution d'un moteur) ?",
         "options": [
             "Engrenages droits",
             "Courroie crantee (synchrone) ou chaine",
             "Poulies lisses",
             "Accouplement hydraulique"
         ],
         "correct_option": "Courroie crantee (synchrone) ou chaine",
         "explanation": "La courroie crantee (timing belt) et la chaine de transmission (velo, moto) synchronisent les axes sans glissement, garantissant un rapport de transmission constant."},
        {"id": "785_8", "type": "vrai-faux",
         "question": "Une bielle-manivelle transforme un mouvement de rotation continu en mouvement de translation alternatif.",
         "correct": True,
         "explanation": "Le mecanisme bielle-manivelle (moteur a piston, serrure a came) convertit la rotation de la manivelle en translation de la bielle, et vice versa."},
    ]),

    (786, "L'energie dans les systemes techniques", "Technologie", "3eme", [
        {"id": "786_1", "type": "qcm",
         "question": "Qu'est-ce que le rendement energetique d'un systeme ?",
         "options": [
             "La quantite totale d'energie consommee",
             "Le rapport entre l'energie utile produite et l'energie totale consommee",
             "La puissance maximale du systeme",
             "La duree de fonctionnement"
         ],
         "correct_option": "Le rapport entre l'energie utile produite et l'energie totale consommee",
         "explanation": "Le rendement eta = E_utile / E_consommee (exprime en %) mesure l'efficacite d'un systeme ; l'energie non utile est dissipee en chaleur (pertes)."},
        {"id": "786_2", "type": "vrai-faux",
         "question": "Un moteur a combustion interne a un rendement superieur a celui d'un moteur electrique.",
         "correct": False,
         "explanation": "Un moteur electrique a un rendement de 85 a 98% ; un moteur thermique (essence) n'atteint que 25 a 35% de rendement en raison des pertes thermiques importantes."},
        {"id": "786_3", "type": "texte",
         "question": "Comment appelle-t-on la puissance electrique instantanee consommee par un appareil exprimee en watts ?",
         "correct_answer": "puissance nominale",
         "explanation": "La puissance nominale (en W ou kW) indiquee sur un appareil est la puissance electrique absorbee dans des conditions normales de fonctionnement."},
        {"id": "786_4", "type": "qcm",
         "question": "Quelle technologie stocke de l'energie electrique sous forme d'energie chimique pour l'alimentation d'un systeme portable ?",
         "options": [
             "Supercondensateur",
             "Accumulateur (batterie)",
             "Alternateur",
             "Transformateur"
         ],
         "correct_option": "Accumulateur (batterie)",
         "explanation": "L'accumulateur (batterie) stocke l'energie electrique sous forme d'energie chimique lors de la charge et la restitue lors de la decharge."},
        {"id": "786_5", "type": "vrai-faux",
         "question": "La recuperation de l'energie de freinage dans une voiture electrique ameliore son autonomie.",
         "correct": True,
         "explanation": "Le freinage regeneratif transforme l'energie cinetique du vehicule en energie electrique lors des decelerations, la restituant a la batterie et augmentant l'autonomie de 10 a 25%."},
        {"id": "786_6", "type": "texte",
         "question": "Comment appelle-t-on la chaleur produite par un conducteur electrique parcouru par un courant, qui represente une perte d'energie ?",
         "correct_answer": "effet Joule",
         "explanation": "L'effet Joule (Q = R Ã— IÂ² Ã— t) est la dissipation d'energie electrique en chaleur dans les conducteurs resistifs ; minimiser les pertes Joule ameliore le rendement."},
        {"id": "786_7", "type": "qcm",
         "question": "Quel indicateur compare la consommation energetique annuelle d'un appareil electromenager ?",
         "options": [
             "L'etiquette energie (classes A a G)",
             "La tension d'alimentation",
             "La masse de l'appareil",
             "La couleur du boitier"
         ],
         "correct_option": "L'etiquette energie (classes A a G)",
         "explanation": "L'etiquette energie europeenne (classes A a G) permet aux consommateurs de comparer la consommation energetique des appareils et d'identifier les plus efficients."},
        {"id": "786_8", "type": "vrai-faux",
         "question": "Un systeme en veille (standby) consomme de l'energie electrique meme lorsqu'il n'est pas utilise.",
         "correct": True,
         "explanation": "La consommation en veille (phantom load) peut representer 5 a 15% de la consommation totale d'un logement ; les reglementations europeennes la limitent a 0,5W maximum."},
    ]),

    # =========================================================
    # BLOC 4 - PROGRAMMATION ET OBJETS CONNECTES (787-793)
    # =========================================================
    (787, "Introduction a la programmation : algorithmes", "Technologie", "3eme", [
        {"id": "787_1", "type": "qcm",
         "question": "Qu'est-ce qu'un algorithme ?",
         "options": [
             "Un langage de programmation",
             "Une suite finie d'instructions ordonnees pour resoudre un probleme",
             "Un composant electronique",
             "Un logiciel de dessin"
         ],
         "correct_option": "Une suite finie d'instructions ordonnees pour resoudre un probleme",
         "explanation": "Un algorithme est une sequence ordonnee d'instructions qui, lorsqu'elle est executee, resout un probleme ou accomplit une tache specifique."},
        {"id": "787_2", "type": "vrai-faux",
         "question": "Un diagramme de flux (organigramme) permet de representer graphiquement un algorithme.",
         "correct": True,
         "explanation": "L'organigramme utilise des symboles standardises (debut/fin en ovale, action en rectangle, decision en losange) pour representer visuellement le deroulement d'un algorithme."},
        {"id": "787_3", "type": "texte",
         "question": "Comment appelle-t-on une instruction qui execute un bloc de code plusieurs fois tant qu'une condition est vraie ?",
         "correct_answer": "boucle",
         "explanation": "Une boucle (while, for) repete l'execution d'un bloc d'instructions tant qu'une condition est satisfaite, permettant de traiter des sequences repetitives."},
        {"id": "787_4", "type": "qcm",
         "question": "Quelle structure algorithmique permet d'executer un bloc d'instructions seulement si une condition est vraie ?",
         "options": [
             "Boucle while",
             "Structure conditionnelle si...alors...sinon",
             "Sequence lineaire",
             "Fonction recursive"
         ],
         "correct_option": "Structure conditionnelle si...alors...sinon",
         "explanation": "La structure conditionnelle (if...then...else) teste une condition et execute l'un ou l'autre bloc d'instructions selon le resultat."},
        {"id": "787_5", "type": "vrai-faux",
         "question": "Une variable en programmation est un espace memoire nomme qui peut stocker et modifier une valeur.",
         "correct": True,
         "explanation": "Une variable est un conteneur memoire identifie par un nom et un type (entier, flottant, chaine) dont la valeur peut etre lue et modifiee pendant l'execution."},
        {"id": "787_6", "type": "texte",
         "question": "Comment appelle-t-on un bloc d'instructions reutilisable qui accomplit une tache specifique et peut etre appele depuis d'autres parties du programme ?",
         "correct_answer": "fonction",
         "explanation": "Une fonction (procedure, sous-programme) est un bloc de code nomme qui peut recevoir des parametres et retourner une valeur, favorisant la reutilisabilite du code."},
        {"id": "787_7", "type": "qcm",
         "question": "Quel langage visuel par blocs est utilise en education pour initier les enfants a la programmation ?",
         "options": [
             "Python",
             "Scratch",
             "Java",
             "C++"
         ],
         "correct_option": "Scratch",
         "explanation": "Scratch (MIT Media Lab) est un environnement de programmation visuelle par blocs colorÃ©s emboitables, conÃ§u pour apprendre la programmation de maniere ludique."},
        {"id": "787_8", "type": "vrai-faux",
         "question": "Python est un langage de programmation lisible et polyvalent tres utilise en education et en data science.",
         "correct": True,
         "explanation": "Python est un langage interprete, a syntaxe lisible, tres utilise en education (3eme, lycee), data science, IA et developpement web."},
    ]),

    (788, "Programmation Arduino et electronique", "Technologie", "3eme", [
        {"id": "788_1", "type": "qcm",
         "question": "Qu'est-ce que l'Arduino ?",
         "options": [
             "Un logiciel de dessin technique",
             "Une carte microcontroleur open source programmable pour creer des prototypes electroniques",
             "Un capteur de mouvement",
             "Un protocole de communication"
         ],
         "correct_option": "Une carte microcontroleur open source programmable pour creer des prototypes electroniques",
         "explanation": "Arduino est une plateforme electronique open source (materiel + logiciel) basee sur un microcontroleur ATmega, utilisee en education et prototypage."},
        {"id": "788_2", "type": "vrai-faux",
         "question": "La fonction setup() d'un programme Arduino s'execute une seule fois au demarrage.",
         "correct": True,
         "explanation": "En Arduino, setup() s'execute une fois a la mise sous tension (initialisation des broches, variables) ; la fonction loop() s'execute en boucle indefiniment ensuite."},
        {"id": "788_3", "type": "texte",
         "question": "Quelle instruction Arduino allume une LED connectee sur la broche 13 ?",
         "correct_answer": "digitalWrite(13, HIGH)",
         "explanation": "digitalWrite(pin, valeur) ecrit un niveau logique HIGH (1, 5V) ou LOW (0, 0V) sur une broche numerique ; digitalWrite(13, HIGH) allume la LED."},
        {"id": "788_4", "type": "qcm",
         "question": "Que fait la fonction analogRead() sur un Arduino ?",
         "options": [
             "Lit un signal numerique 0 ou 1",
             "Lit une tension analogique (0-5V) et la convertit en valeur numerique (0-1023)",
             "Ecrit une valeur PWM sur une broche",
             "Lit la temperature du microcontroleur"
         ],
         "correct_option": "Lit une tension analogique (0-5V) et la convertit en valeur numerique (0-1023)",
         "explanation": "analogRead() utilise le convertisseur analogique-numerique (CAN) 10 bits de l'Arduino : 0V â†’ 0, 5V â†’ 1023, permettant de lire capteurs (LDR, potentiometre, temperature)."},
        {"id": "788_5", "type": "vrai-faux",
         "question": "Une resistance de 220 ohms est generalement utilisee en serie avec une LED pour limiter le courant et la proteger.",
         "correct": True,
         "explanation": "Une LED sans resistance de limitation peut etre detruite par surintensitÃ© ; la resistance de 220 Î© (avec 5V) limite le courant a environ 15-20mA, la valeur nominale d'une LED standard."},
        {"id": "788_6", "type": "texte",
         "question": "Quel composant electronique permet de polariser un courant dans un seul sens et est utilise dans les circuits de protection et d'affichage ?",
         "correct_answer": "diode",
         "explanation": "La diode (junction PN) ne laisse passer le courant que dans un sens (anode vers cathode en direct) ; les LED sont des diodes electroluminescentes."},
        {"id": "788_7", "type": "qcm",
         "question": "Que signifie PWM dans le contexte de la commande de moteurs ou de l'eclairage avec Arduino ?",
         "options": [
             "Programme de gestion sans memoire",
             "Modulation de largeur d'impulsion : varier la duree des impulsions pour simuler une tension variable",
             "Protocole de communication sans fil",
             "Mode programmation avance"
         ],
         "correct_option": "Modulation de largeur d'impulsion : varier la duree des impulsions pour simuler une tension variable",
         "explanation": "PWM (Pulse Width Modulation) fait varier le rapport cyclique d'un signal numerique pour simuler une tension analogique : utilisee pour piloter la vitesse des moteurs et la luminosite des LED."},
        {"id": "788_8", "type": "vrai-faux",
         "question": "Un breadboard (plaque d'essais) permet de realiser des circuits electroniques sans soudure.",
         "correct": True,
         "explanation": "Le breadboard est une platine de prototypage a connexions internes ; on y insere les composants et fils pour tester un circuit sans soudure, facilitant les modifications."},
    ]),

    (789, "Programmation Python en technologie", "Technologie", "3eme", [
        {"id": "789_1", "type": "qcm",
         "question": "Quelle est la syntaxe correcte pour afficher 'Bonjour' en Python ?",
         "options": [
             "print('Bonjour')",
             "echo 'Bonjour'",
             "display('Bonjour')",
             "write('Bonjour')"
         ],
         "correct_option": "print('Bonjour')",
         "explanation": "La fonction print() en Python affiche du texte ou des variables dans la console ; c'est l'instruction de base pour afficher des informations."},
        {"id": "789_2", "type": "vrai-faux",
         "question": "En Python, l'indentation (decalage) du code est obligatoire pour definir les blocs d'instructions.",
         "correct": True,
         "explanation": "Python utilise l'indentation (4 espaces recommandes) pour delimiter les blocs (if, for, while, def) ; une indentation incorrecte provoque une erreur IndentationError."},
        {"id": "789_3", "type": "texte",
         "question": "Comment cree-t-on une liste de 5 entiers en Python ?",
         "correct_answer": "maliste = [1, 2, 3, 4, 5]",
         "explanation": "Une liste Python est definie avec des crochets [] contenant des elements separes par des virgules ; elle est mutable (modifiable) et peut contenir des types mixtes."},
        {"id": "789_4", "type": "qcm",
         "question": "Quelle boucle Python est la plus adaptee pour parcourir tous les elements d'une liste ?",
         "options": [
             "while condition",
             "for element in liste",
             "do...while",
             "repeat...until"
         ],
         "correct_option": "for element in liste",
         "explanation": "La boucle for...in en Python parcourt directement chaque element d'une sequence (liste, chaine, range) sans gerer manuellement l'index."},
        {"id": "789_5", "type": "vrai-faux",
         "question": "La bibliotheque Turtle de Python permet de creer des dessins geometriques avec une 'tortue' programmable.",
         "correct": True,
         "explanation": "Turtle est une bibliotheque graphique educative Python qui dessine en deplacant une 'tortue' (curseur) selon des commandes (forward, right, left, circle)."},
        {"id": "789_6", "type": "texte",
         "question": "Comment appelle-t-on une erreur de syntaxe detectee par Python lorsque le code ne respecte pas les regles du langage ?",
         "correct_answer": "SyntaxError",
         "explanation": "Une SyntaxError (ou IndentationError) est signalee par l'interpreteur Python quand le code ne respecte pas la grammaire du langage."},
        {"id": "789_7", "type": "qcm",
         "question": "Quel mot-cle Python permet de definir une nouvelle fonction ?",
         "options": ["function", "def", "sub", "procedure"],
         "correct_option": "def",
         "explanation": "Le mot-cle def (define) introduit la definition d'une fonction : def ma_fonction(parametre): ; la fonction est ensuite appelee par son nom."},
        {"id": "789_8", "type": "vrai-faux",
         "question": "Python peut etre utilise pour analyser des donnees scientifiques grace aux bibliotheques NumPy et Pandas.",
         "correct": True,
         "explanation": "NumPy (calcul numerique), Pandas (analyse de donnees tabulaires) et Matplotlib (visualisation) forment l'ecosysteme Python de data science, utilise en SVT, physique et technologie."},
    ]),

    (790, "Les reseaux informatiques et la communication", "Technologie", "3eme", [
        {"id": "790_1", "type": "qcm",
         "question": "Quelle est la fonction d'un routeur dans un reseau informatique ?",
         "options": [
             "Amplifier le signal Wi-Fi",
             "Acheminer les paquets de donnees entre differents reseaux",
             "Stocker les donnees des utilisateurs",
             "Securiser les mots de passe"
         ],
         "correct_option": "Acheminer les paquets de donnees entre differents reseaux",
         "explanation": "Un routeur analyse les adresses IP des paquets et les achemine vers le reseau de destination optimal, reliant le reseau local a internet."},
        {"id": "790_2", "type": "vrai-faux",
         "question": "Une adresse IP identifie de maniere unique un equipement sur un reseau.",
         "correct": True,
         "explanation": "L'adresse IP (IPv4 en 4 octets, ex. 192.168.1.1 ; IPv6 en 128 bits) identifie un equipement sur un reseau ; elle peut etre fixe ou attribuee dynamiquement (DHCP)."},
        {"id": "790_3", "type": "texte",
         "question": "Comment appelle-t-on le protocole qui permet de naviguer sur des pages web en echangeant des donnees entre navigateur et serveur ?",
         "correct_answer": "HTTP/HTTPS",
         "explanation": "HTTP (HyperText Transfer Protocol) permet l'echange de pages web ; HTTPS est sa version chiffree (TLS/SSL) qui securise les communications."},
        {"id": "790_4", "type": "qcm",
         "question": "Qu'est-ce que le cloud computing ?",
         "options": [
             "Un type de reseau local",
             "L'utilisation de ressources informatiques (serveurs, stockage, logiciels) via internet plutot que localement",
             "Un protocole de communication sans fil",
             "Un systeme d'exploitation"
         ],
         "correct_option": "L'utilisation de ressources informatiques (serveurs, stockage, logiciels) via internet plutot que localement",
         "explanation": "Le cloud computing (informatique en nuage) permet d'acceder a des ressources informatiques distantes via internet ; ex. Google Drive, OneDrive, AWS, Azure."},
        {"id": "790_5", "type": "vrai-faux",
         "question": "Un pare-feu (firewall) filtre le trafic reseau pour bloquer les connexions non autorisees.",
         "correct": True,
         "explanation": "Un pare-feu analyse les paquets entrants et sortants et bloque ceux qui ne respectent pas les regles de securite definies, protÃ©geant le reseau local."},
        {"id": "790_6", "type": "texte",
         "question": "Comment appelle-t-on le protocole qui traduit les adresses IP en adresses physiques (MAC) sur un reseau local ?",
         "correct_answer": "ARP",
         "explanation": "ARP (Address Resolution Protocol) resout les adresses IP en adresses MAC pour l'acheminement des donnees au niveau de la couche liaison du modele OSI."},
        {"id": "790_7", "type": "qcm",
         "question": "Quel type de topologie reseau relie tous les equipements a un commutateur (switch) central ?",
         "options": [
             "Topologie bus",
             "Topologie anneau",
             "Topologie etoile",
             "Topologie maillage"
         ],
         "correct_option": "Topologie etoile",
         "explanation": "Dans la topologie etoile, tous les equipements sont relies a un commutateur central ; si un cable tombe en panne, seul l'equipement concerne est affecte."},
        {"id": "790_8", "type": "vrai-faux",
         "question": "Le chiffrement de bout en bout garantit que seuls l'emetteur et le destinataire peuvent lire le contenu d'un message.",
         "correct": True,
         "explanation": "Le chiffrement E2E (end-to-end) utilise des cles cryptographiques : les donnees sont chiffrees chez l'expediteur et seulement le destinataire peut les dechiffrer."},
    ]),

    (791, "La cybersecurite et protection des donnees", "Technologie", "3eme", [
        {"id": "791_1", "type": "qcm",
         "question": "Qu'est-ce qu'un logiciel malveillant (malware) ?",
         "options": [
             "Un antivirus performant",
             "Un programme concu pour endommager, perturber ou acceder sans autorisation a un systeme",
             "Un navigateur web",
             "Un gestionnaire de mots de passe"
         ],
         "correct_option": "Un programme concu pour endommager, perturber ou acceder sans autorisation a un systeme",
         "explanation": "Les malwares incluent virus, vers, chevaux de Troie, ransomwares, spywares ; ils peuvent voler des donnees, chiffrer des fichiers ou prendre le controle d'un systeme."},
        {"id": "791_2", "type": "vrai-faux",
         "question": "Un mot de passe fort doit combiner majuscules, minuscules, chiffres et caracteres speciaux.",
         "correct": True,
         "explanation": "Un mot de passe robuste doit avoir au moins 12 caracteres et inclure des majuscules, minuscules, chiffres et symboles ; il doit etre unique pour chaque service."},
        {"id": "791_3", "type": "texte",
         "question": "Comment appelle-t-on l'attaque qui tente de voler des identifiants en simulant un site web ou courriel officiel ?",
         "correct_answer": "phishing",
         "explanation": "Le phishing (hameconnage) imite l'identite de banques, services publics ou reseaux sociaux pour inciter les victimes a saisir leurs identifiants sur de faux sites."},
        {"id": "791_4", "type": "qcm",
         "question": "Qu'est-ce que l'authentification a deux facteurs (2FA) ?",
         "options": [
             "Deux mots de passe differents",
             "Une verification en deux etapes : mot de passe + second element (code SMS, application)",
             "Deux adresses email",
             "Un certificat numerique"
         ],
         "correct_option": "Une verification en deux etapes : mot de passe + second element (code SMS, application)",
         "explanation": "Le 2FA (Two-Factor Authentication) combine quelque chose que l'on sait (mot de passe) avec quelque chose que l'on possede (telephone, cle USB) pour renforcer la securite."},
        {"id": "791_5", "type": "vrai-faux",
         "question": "Il est securise de se connecter a des services sensibles (banque) depuis un reseau Wi-Fi public non protege.",
         "correct": False,
         "explanation": "Les reseaux Wi-Fi publics non chiffres permettent des attaques 'man-in-the-middle' ou les donnees transitent en clair ; il faut utiliser un VPN ou eviter ces connexions."},
        {"id": "791_6", "type": "texte",
         "question": "Comment appelle-t-on l'outil logiciel qui chiffre le trafic internet et masque l'adresse IP de l'utilisateur ?",
         "correct_answer": "VPN",
         "explanation": "Un VPN (Virtual Private Network) cree un tunnel chiffre entre le client et un serveur distant, protÃ©geant la confidentialite et permettant d'acceder a des contenus geo-restreints."},
        {"id": "791_7", "type": "qcm",
         "question": "Qu'est-ce qu'une attaque DDoS ?",
         "options": [
             "Une intrusion discrete dans une base de donnees",
             "Une attaque qui sature un serveur avec un volume massif de requetes pour le rendre indisponible",
             "Un virus qui chiffre les fichiers",
             "Un logiciel espion"
         ],
         "correct_option": "Une attaque qui sature un serveur avec un volume massif de requetes pour le rendre indisponible",
         "explanation": "DDoS (Distributed Denial of Service) coordonne des milliers de machines infectees (botnet) pour inonder un serveur de requetes jusqu'a le rendre inaccessible."},
        {"id": "791_8", "type": "vrai-faux",
         "question": "Les mises a jour logicielles doivent etre installees rapidement car elles corrigent souvent des failles de securite.",
         "correct": True,
         "explanation": "Les mises a jour corrigent des vulnerabilites (zero-day, CVE) exploitees par les hackers ; retarder les mises a jour augmente le risque d'exploitation."},
    ]),

    (792, "Les interfaces homme-machine (IHM) et UX design", "Technologie", "3eme", [
        {"id": "792_1", "type": "qcm",
         "question": "Qu'est-ce qu'une interface homme-machine (IHM) ?",
         "options": [
             "Un logiciel de CAO",
             "L'ensemble des moyens par lesquels un utilisateur interagit avec un systeme technique",
             "Un composant electronique",
             "Un protocole reseau"
         ],
         "correct_option": "L'ensemble des moyens par lesquels un utilisateur interagit avec un systeme technique",
         "explanation": "L'IHM (ou HMI) designe tous les elements d'interaction : ecran tactile, clavier, boutons, commandes vocales, voyants, permettant a l'humain de controler un systeme."},
        {"id": "792_2", "type": "vrai-faux",
         "question": "Une interface bien concue doit permettre a l'utilisateur d'accomplir ses taches de maniere intuitive et sans formation prealable.",
         "correct": True,
         "explanation": "Le design UX vise a rendre les interfaces intuitives (affordance) en respectant les conventions d'usage et en anticipant les actions de l'utilisateur."},
        {"id": "792_3", "type": "texte",
         "question": "Comment appelle-t-on la discipline qui etudie et optimise l'experience globale de l'utilisateur avec un produit ou service numerique ?",
         "correct_answer": "UX design",
         "explanation": "L'UX design (User Experience) etudie les besoins, comportements et emotions des utilisateurs pour concevoir des interfaces efficaces, efficientes et satisfaisantes."},
        {"id": "792_4", "type": "qcm",
         "question": "Qu'est-ce que le principe d'accessibilite numerique ?",
         "options": [
             "La rapidite de chargement d'une page web",
             "La conception de contenus utilisables par des personnes en situation de handicap",
             "La securite des donnees personnelles",
             "La compatibilite entre navigateurs"
         ],
         "correct_option": "La conception de contenus utilisables par des personnes en situation de handicap",
         "explanation": "L'accessibilite numerique (WCAG, RGAA en France) assure que les sites et applications sont utilisables par des personnes malvoyantes, malentendantes ou avec des troubles moteurs."},
        {"id": "792_5", "type": "vrai-faux",
         "question": "Un test utilisateur consiste a observer de vrais utilisateurs interagir avec un prototype pour identifier les problemes.",
         "correct": True,
         "explanation": "Les tests utilisateurs (user testing) sont une methode essentielle du design UX ; ils revelent les problemes d'usabilite non anticipes par les concepteurs."},
        {"id": "792_6", "type": "texte",
         "question": "Comment appelle-t-on une representation simplifiee et interactive d'une interface web ou mobile, creee pour tester les flux de navigation ?",
         "correct_answer": "prototype interactif",
         "explanation": "Un prototype interactif (wireframe cliquable, Figma, Adobe XD) simule l'experience utilisateur sans coder, permettant de valider la navigation et l'architecture de l'information."},
        {"id": "792_7", "type": "qcm",
         "question": "Quel principe de design indique que chaque element d'une interface doit suggerer son utilisation par sa forme ou son apparence ?",
         "options": [
             "Principe de coherence",
             "Principe d'affordance",
             "Principe de simplicite",
             "Principe de contraste"
         ],
         "correct_option": "Principe d'affordance",
         "explanation": "L'affordance (J.J. Gibson, Norman) designe la propriete d'un objet a suggerer son utilisation : un bouton bombÃ© evoque l'action d'appuyer, une poignee invite a tirer."},
        {"id": "792_8", "type": "vrai-faux",
         "question": "La conception responsive design permet a un site web de s'adapter automatiquement a la taille de l'ecran (mobile, tablette, PC).",
         "correct": True,
         "explanation": "Le responsive design utilise des grilles fluides et media queries CSS pour adapter la mise en page et la lisibilite selon le type d'ecran de l'utilisateur."},
    ]),

    (793, "Les donnees numeriques et leur traitement", "Technologie", "3eme", [
        {"id": "793_1", "type": "qcm",
         "question": "Qu'est-ce que le format binaire en informatique ?",
         "options": [
             "Un fichier avec deux versions",
             "Un systeme de representation des donnees utilisant uniquement les chiffres 0 et 1",
             "Un protocole de communication",
             "Un type de processeur"
         ],
         "correct_option": "Un systeme de representation des donnees utilisant uniquement les chiffres 0 et 1",
         "explanation": "Le systeme binaire (base 2) est la base de toute l'informatique ; toute information (texte, image, son) est codee en sequences de 0 et 1 (bits)."},
        {"id": "793_2", "type": "vrai-faux",
         "question": "Un octet (byte) est compose de 8 bits.",
         "correct": True,
         "explanation": "Un octet = 8 bits, pouvant representer 256 valeurs (0 a 255). 1 kilooctet = 1 024 octets, 1 megaoctet = 1 024 kilo-octets."},
        {"id": "793_3", "type": "texte",
         "question": "Comment s'appelle le fichier de donnees structure en lignes et colonnes souvent utilise pour stocker et analyser des informations ?",
         "correct_answer": "tableur CSV",
         "explanation": "Le format CSV (Comma-Separated Values) est un fichier texte ou les donnees sont separees par des virgules, lisible par Excel, Python (pandas) ou LibreOffice."},
        {"id": "793_4", "type": "qcm",
         "question": "Qu'est-ce que le big data ?",
         "options": [
             "Un tres gros disque dur",
             "Des ensembles massifs de donnees necessitant des outils specifiques pour leur collecte, stockage et analyse",
             "Un logiciel de comptabilite",
             "Une connexion internet tres rapide"
         ],
         "correct_option": "Des ensembles massifs de donnees necessitant des outils specifiques pour leur collecte, stockage et analyse",
         "explanation": "Le big data caracterise les ensembles de donnees par leurs 3V : Volume (enorme), Variete (formats divers) et Velocite (flux continu) ; ils necessite des technologies comme Hadoop ou Spark."},
        {"id": "793_5", "type": "vrai-faux",
         "question": "Une base de donnees SQL permet de stocker et d'interroger des donnees structurees grace a des requetes.",
         "correct": True,
         "explanation": "SQL (Structured Query Language) est le langage standard pour interroger des bases de donnees relationnelles (MySQL, PostgreSQL, SQLite) : SELECT, INSERT, UPDATE, DELETE."},
        {"id": "793_6", "type": "texte",
         "question": "Comment appelle-t-on la representation graphique qui visualise les donnees sous forme de points relies par des lignes pour montrer une evolution ?",
         "correct_answer": "graphique en courbes",
         "explanation": "Le graphique en courbes (line chart) est utilise pour visualiser l'evolution d'une variable quantitative dans le temps ou en fonction d'une autre variable continue."},
        {"id": "793_7", "type": "qcm",
         "question": "Qu'est-ce que l'intelligence artificielle (IA) dans le contexte du traitement des donnees ?",
         "options": [
             "Un robot humanoide",
             "Un ensemble de techniques permettant a des machines d'apprendre et de prendre des decisions a partir de donnees",
             "Un antivirus avance",
             "Un protocole reseau intelligent"
         ],
         "correct_option": "Un ensemble de techniques permettant a des machines d'apprendre et de prendre des decisions a partir de donnees",
         "explanation": "L'IA (machine learning, deep learning) apprend des patterns dans des donnees massives pour faire des predictions, classer des images ou comprendre le langage naturel."},
        {"id": "793_8", "type": "vrai-faux",
         "question": "Les algorithmes de recommandation des plateformes de streaming analysent vos habitudes pour vous suggerer des contenus.",
         "correct": True,
         "explanation": "Les moteurs de recommandation (Netflix, Spotify, YouTube) utilisent le filtrage collaboratif et le machine learning pour analyser vos historiques et anticiper vos preferences."},
    ]),

    # =========================================================
    # BLOC 5 - ENERGIES ET DEVELOPPEMENT DURABLE (794-799)
    # =========================================================
    (794, "Les sources d'energie et leur transformation", "Technologie", "3eme", [
        {"id": "794_1", "type": "qcm",
         "question": "Quelle est la distinction entre energie primaire et energie finale ?",
         "options": [
             "L'energie primaire est produite par l'industrie ; l'energie finale est naturelle",
             "L'energie primaire est extraite de la nature ; l'energie finale est l'energie utilisable par le consommateur apres transformation",
             "L'energie finale est plus chÃ¨re que l'energie primaire",
             "Il n'y a aucune difference"
         ],
         "correct_option": "L'energie primaire est extraite de la nature ; l'energie finale est l'energie utilisable par le consommateur apres transformation",
         "explanation": "L'energie primaire (petrole brut, charbon, vent, soleil) est transformee en energie finale (electricite, carburant) ; chaque conversion engendre des pertes."},
        {"id": "794_2", "type": "vrai-faux",
         "question": "En France, l'energie nucleaire represente la majorite de la production d'electricite.",
         "correct": True,
         "explanation": "La France produit environ 70% de son electricite par l'energie nucleaire, faisant d'elle le pays le plus nucleaire du monde en proportion."},
        {"id": "794_3", "type": "texte",
         "question": "Comment appelle-t-on le processus physique utilise dans les centrales nucleaires pour produire de l'energie a partir de la division de l'atome ?",
         "correct_answer": "fission nucleaire",
         "explanation": "La fission nucleaire divise des noyaux lourds (uranium-235) en noyaux plus legers, liberant une enorme quantite d'energie thermique utilisee pour produire de la vapeur et de l'electricite."},
        {"id": "794_4", "type": "qcm",
         "question": "Quelle source d'energie renouvelable utilise les differences de marees pour produire de l'electricite ?",
         "options": [
             "Energie eolienne",
             "Energie maremotrice",
             "Energie geothermique",
             "Energie solaire photovoltaique"
         ],
         "correct_option": "Energie maremotrice",
         "explanation": "L'usine maremotrice (ex. usine de la Rance en Bretagne, 240 MW) exploite la difference de niveau entre maree haute et basse pour faire tourner des turbines."},
        {"id": "794_5", "type": "vrai-faux",
         "question": "L'energie hydroelectrique est produite par la force de l'eau en mouvement actionnant des turbines.",
         "correct": True,
         "explanation": "Les centrales hydroelectriques (barrages) utilisent la chute d'eau pour faire tourner des turbines Francis, Pelton ou Kaplan, produisant de l'electricite renouvelable et pilotable."},
        {"id": "794_6", "type": "texte",
         "question": "Comment appelle-t-on l'energie produite par la chaleur interne de la Terre, exploitee pour le chauffage et l'electricite ?",
         "correct_answer": "energie geothermique",
         "explanation": "La geothermie exploite la chaleur des roches profondes (haute enthalpie) ou des nappes chaudes (basse enthalpie) pour produire electricite et chauffage."},
        {"id": "794_7", "type": "qcm",
         "question": "Quel est le principal inconvenient des energies solaire et eolienne compare aux energies fossiles ?",
         "options": [
             "Leur cout de fonctionnement tres eleve",
             "Leur intermittence (production variable selon la meteorologie)",
             "Leur dangerositÃ© pour l'environnement",
             "L'impossibilite de les exporter"
         ],
         "correct_option": "Leur intermittence (production variable selon la meteorologie)",
         "explanation": "L'intermittence des EnR solaires et eoliennes necessite des solutions de stockage (batteries, step, hydrogene) ou de flexibilite du reseau pour equilibrer offre et demande."},
        {"id": "794_8", "type": "vrai-faux",
         "question": "La biomasse (bois, biogaz, dechets) est consideree comme une energie renouvelable.",
         "correct": True,
         "explanation": "La biomasse est renouvelable car les plantes absorbent le CO2 pendant leur croissance ; sa combustion libere du CO2 prealablement capte, la rendant theoriquement neutre en carbone."},
    ]),

    (795, "Le bilan energetique d'un systeme technique", "Technologie", "3eme", [
        {"id": "795_1", "type": "qcm",
         "question": "Qu'est-ce qu'un bilan energetique d'un systeme technique ?",
         "options": [
             "La facture d'electricite du systeme",
             "La comparaison entre l'energie entrant dans le systeme et l'energie utile produite",
             "Le calcul du cout de fabrication",
             "La duree de vie du systeme"
         ],
         "correct_option": "La comparaison entre l'energie entrant dans le systeme et l'energie utile produite",
         "explanation": "Le bilan energetique identifie : energie entree = energie utile + pertes ; il permet de calculer le rendement et d'identifier les postes d'amelioration."},
        {"id": "795_2", "type": "vrai-faux",
         "question": "Dans tout systeme energetique reel, une partie de l'energie est inevitablement dissipee sous forme de chaleur.",
         "correct": True,
         "explanation": "Le deuxieme principe de la thermodynamique stipule que toute conversion d'energie produit inevitablement de l'entropie (chaleur dissipee), limitant le rendement a moins de 100%."},
        {"id": "795_3", "type": "texte",
         "question": "Comment appelle-t-on la representation graphique qui montre visuellement comment l'energie se decompose entre partie utile et pertes dans un systeme ?",
         "correct_answer": "diagramme de Sankey",
         "explanation": "Le diagramme de Sankey represente les flux d'energie par des fleches d'epaisseur proportionnelle, visualisant clairement les conversions et les pertes."},
        {"id": "795_4", "type": "qcm",
         "question": "Quel est le rendement d'un systeme qui transforme 80 J en energie utile pour 200 J d'energie consommee ?",
         "options": ["25%", "40%", "80%", "120%"],
         "correct_option": "40%",
         "explanation": "eta = E_utile / E_consommee = 80 / 200 = 0,40 = 40%. Les 120 J restants sont dissipes en chaleur."},
        {"id": "795_5", "type": "vrai-faux",
         "question": "Reduire les pertes par frottement dans un mecanisme ameliore son rendement energetique.",
         "correct": True,
         "explanation": "Les frottements transforment l'energie mecanique en chaleur inutile ; la lubrification, les roulements et les nouveaux materiaux reducteurs de friction ameliorent le rendement."},
        {"id": "795_6", "type": "texte",
         "question": "Comment appelle-t-on la chaleur perdue lors du refroidissement des moteurs ou des centrales electriques ?",
         "correct_answer": "chaleur fatale",
         "explanation": "La chaleur fatale est la chaleur inÃ©vitablement rejetee lors des conversions energetiques ; la cogeneration la recupere pour le chauffage, amÃ©liorant le rendement global."},
        {"id": "795_7", "type": "qcm",
         "question": "Quel systeme recupere l'energie thermique rejetee par une centrale electrique pour chauffer des batiments ?",
         "options": [
             "Pompe a chaleur",
             "Cogeneration (CHP)",
             "Echangeur solaire",
             "Centrale a cycle combine uniquement"
         ],
         "correct_option": "Cogeneration (CHP)",
         "explanation": "La cogeneration (Combined Heat and Power) produit simultanement de l'electricite et de la chaleur utile a partir d'un meme combustible, atteignant des rendements globaux de 80 a 90%."},
        {"id": "795_8", "type": "vrai-faux",
         "question": "L'isolation thermique d'un batiment reduit ses deperditions de chaleur et donc sa consommation energetique.",
         "correct": True,
         "explanation": "L'isolation (murs, toiture, fenetres double-vitrage) reduit les flux de chaleur entre interieur et exterieur, diminuant les besoins de chauffage en hiver et de climatisation en ete."},
    ]),

    (796, "L'eco-conception et cycle de vie des produits", "Technologie", "3eme", [
        {"id": "796_1", "type": "qcm",
         "question": "Qu'est-ce que l'analyse du cycle de vie (ACV) d'un produit ?",
         "options": [
             "Le calcul de la duree de vie garantie",
             "L'evaluation des impacts environnementaux de la conception a la fin de vie",
             "L'etude de la resistance mecanique",
             "L'historique des ventes du produit"
         ],
         "correct_option": "L'evaluation des impacts environnementaux de la conception a la fin de vie",
         "explanation": "L'ACV (ISO 14040) evalue les impacts environnementaux d'un produit de l'extraction des matieres premieres a sa fin de vie, en passant par la fabrication et l'usage."},
        {"id": "796_2", "type": "vrai-faux",
         "question": "L'eco-conception integre les contraintes environnementales des la phase de conception du produit.",
         "correct": True,
         "explanation": "L'eco-conception prend en compte l'environnement comme parametre de conception (choix materiaux, energie, durabilite, recyclabilite) plutot qu'en fin de processus."},
        {"id": "796_3", "type": "texte",
         "question": "Comment appelle-t-on les 7 etapes qui composent le cycle de vie d'un produit, du berceau a la tombe ?",
         "correct_answer": "cycle de vie",
         "explanation": "Le cycle de vie comprend : extraction des matieres premieres, fabrication, distribution, utilisation, fin de vie (reutilisation, recyclage, elimination)."},
        {"id": "796_4", "type": "qcm",
         "question": "Quel principe de l'economie circulaire consiste a transformer les dechets d'une industrie en matieres premieres d'une autre ?",
         "options": [
             "Recyclage classique",
             "Ecologie industrielle et territoriale",
             "Upcycling",
             "Consigne"
         ],
         "correct_option": "Ecologie industrielle et territoriale",
         "explanation": "L'ecologie industrielle (ex. zone industrielle de Kalundborg au Danemark) organise des echanges de flux de matieres et d'energie entre entreprises voisines."},
        {"id": "796_5", "type": "vrai-faux",
         "question": "Privilegier la reparabilite d'un produit fait partie des principes de l'eco-conception.",
         "correct": True,
         "explanation": "Un produit reparable a une duree de vie plus longue, reduisant la consommation de matieres premieres et les dechets ; l'indice de reparabilite est obligatoire en France depuis 2021."},
        {"id": "796_6", "type": "texte",
         "question": "Comment appelle-t-on la valorisation d'un dechet en le transformant en un produit de valeur superieure ou egale (par opposition au recyclage classique) ?",
         "correct_answer": "upcycling",
         "explanation": "L'upcycling (surcyclage) transforme des dechets en nouveaux produits de valeur superieure ou egale sans les degrader, contrairement au recyclage classique (downcycling)."},
        {"id": "796_7", "type": "qcm",
         "question": "Quel label garantit qu'un produit electronique (smartphone, PC) respecte des criteres environnementaux et sociaux tout au long de sa chaine de production ?",
         "options": [
             "Label AB",
             "Label TCO Certified ou EPEAT",
             "Label CE",
             "Label NF"
         ],
         "correct_option": "Label TCO Certified ou EPEAT",
         "explanation": "TCO Certified et EPEAT (Electronic Product Environmental Assessment Tool) evaluent les criteres environnementaux et sociaux des produits electroniques."},
        {"id": "796_8", "type": "vrai-faux",
         "question": "Le recyclage des dechets electroniques (DEEE) est obligatoire en France.",
         "correct": True,
         "explanation": "La directive europeenne DEEE (2002/96/CE) et la loi francaise imposent la collecte et le traitement specifique des dechets electroniques pour recuperer les metaux rares et eviter les pollutions."},
    ]),

    (797, "Les energies renouvelables et leur integration", "Technologie", "3eme", [
        {"id": "797_1", "type": "qcm",
         "question": "Comment fonctionne un panneau solaire photovoltaique ?",
         "options": [
             "Il echauffe un fluide caloporteur",
             "Il convertit la lumiere en electricite grace a l'effet photovoltaique dans des cellules en silicium",
             "Il stocke la chaleur solaire dans un reservoir",
             "Il concentre la lumiere pour faire tourner une turbine"
         ],
         "correct_option": "Il convertit la lumiere en electricite grace a l'effet photovoltaique dans des cellules en silicium",
         "explanation": "L'effet photovoltaique (decouverte d'Edmond Becquerel, 1839) : les photons libÃ¨rent des electrons dans le semi-conducteur, creant un courant electrique continu."},
        {"id": "797_2", "type": "vrai-faux",
         "question": "Une eolienne a axe horizontal produit plus d'electricite qu'une eolienne a axe vertical.",
         "correct": True,
         "explanation": "Les eoliennes a axe horizontal (HAWT) sont plus efficaces (rendement 35-45%) que les eoliennes a axe vertical (VAWT, 20-30%) ; elles dominent le marche eolien."},
        {"id": "797_3", "type": "texte",
         "question": "Comment appelle-t-on la technologie qui stocke l'electricite d'origine renouvelable sous forme d'hydrogene par electrolyse de l'eau ?",
         "correct_answer": "power-to-gas",
         "explanation": "Le Power-to-Gas (PtG) electrolyse l'eau avec de l'electricite renouvelable excedentaire pour produire de l'hydrogene vert, vecteur energetique stockable et transportable."},
        {"id": "797_4", "type": "qcm",
         "question": "Qu'est-ce qu'un reseau electrique intelligent (smart grid) ?",
         "options": [
             "Un reseau electrique souterrain",
             "Un reseau qui utilise les technologies numeriques pour equilibrer production et consommation en temps reel",
             "Un reseau electrique reserve aux entreprises",
             "Un reseau sans transformateurs"
         ],
         "correct_option": "Un reseau qui utilise les technologies numeriques pour equilibrer production et consommation en temps reel",
         "explanation": "Le smart grid integre capteurs, compteurs intelligents (Linky) et IA pour optimiser les flux d'electricite, integrer les EnR et gerer la demande."},
        {"id": "797_5", "type": "vrai-faux",
         "question": "L'autoproduction d'electricite solaire avec revente du surplus sur le reseau est permise en France.",
         "correct": True,
         "explanation": "L'autoconsommation solaire avec injection du surplus est encadree en France ; les producteurs peuvent vendre l'exces a EDF OA (Obligation d'Achat) a un tarif fixe."},
        {"id": "797_6", "type": "texte",
         "question": "Comment appelle-t-on le systeme qui pompe de l'eau en altitude en periode de surplus d'electricite et la turbine pour produire de l'electricite lors des pics de demande ?",
         "correct_answer": "STEP",
         "explanation": "La STEP (Station de Transfert d'Energie par Pompage) est la principale technologie de stockage massif d'electricite ; elle fonctionne comme une batterie hydraulique geante."},
        {"id": "797_7", "type": "qcm",
         "question": "Quel est l'objectif de la France en matiere d'energies renouvelables selon la PPE (Programmation Pluriannuelle de l'Energie) ?",
         "options": [
             "40% d'EnR dans la consommation finale d'energie d'ici 2030",
             "100% d'EnR des 2025",
             "0% de nucleaire d'ici 2035",
             "Supprimer toutes les centrales au gaz"
         ],
         "correct_option": "40% d'EnR dans la consommation finale d'energie d'ici 2030",
         "explanation": "La France vise 40% d'energies renouvelables dans sa consommation finale d'energie d'ici 2030, conformement aux objectifs europeens (directive RED II)."},
        {"id": "797_8", "type": "vrai-faux",
         "question": "Les metaux rares (lithium, cobalt, terres rares) necessaires aux technologies vertes peuvent creer de nouvelles dependances geopolitiques.",
         "correct": True,
         "explanation": "La transition energetique necessite de grandes quantites de metaux rares pour les batteries, aimants eoliens, panneaux solaires ; leur extraction est concentree dans quelques pays (Chine, Congo), posant des enjeux de securite d'approvisionnement et de durabilite."},
    ]),

]

TECHNO3_COMPLEMENT_SPECS = [
    (6101, "Éco-conception et cycle de vie", "l'éco-conception"),
    (6102, "Innovation et besoins des usagers", "l'innovation"),
    (6103, "Maquette numérique et modélisation", "la modélisation 3D"),
    (6104, "Chaîne d'information", "la chaîne d'information"),
    (6105, "Chaîne d'énergie", "la chaîne d'énergie"),
    (6106, "Capteurs et actionneurs", "les capteurs et actionneurs"),
    (6107, "Objets connectés", "les objets connectés"),
    (6108, "Transmission de mouvement", "la transmission de mouvement"),
    (6109, "Résistance des matériaux", "les matériaux"),
    (6110, "Fabrication assistée par ordinateur", "la fabrication assistée"),
    (6111, "Domotique et habitat intelligent", "la domotique"),
    (6112, "Énergies renouvelables", "les énergies renouvelables"),
    (6113, "Stockage de l'énergie", "le stockage de l'énergie"),
    (6114, "Mobilité durable", "la mobilité durable"),
    (6115, "Réseaux et communication", "les réseaux de communication"),
    (6116, "Sécurité des systèmes", "la sécurité des systèmes"),
    (6117, "Robotique et automatisation", "la robotique"),
    (6118, "Programmation par blocs", "la programmation"),
    (6119, "Prototype et tests", "le prototypage"),
    (6120, "Ergonomie et design", "l'ergonomie"),
    (6121, "Projet technique collaboratif", "le projet technique"),
]


def build_techno3_complement(qid, title, focus):
    return (
        qid,
        title,
        "Technologie",
        "3eme",
        [
            {"id": f"{qid}_1", "type": "qcm", "question": f"En technologie, pourquoi étudie-t-on {focus} ?", "options": ["Pour comprendre le fonctionnement et les usages d'un système technique", "Pour faire uniquement de la récitation", "Pour éviter toute expérimentation", "Pour remplacer les mathématiques"], "correct_option": "Pour comprendre le fonctionnement et les usages d'un système technique", "explanation": "La technologie aide à analyser comment les objets répondent à des besoins et fonctionnent dans la vie réelle."},
            {"id": f"{qid}_2", "type": "vrai-faux", "question": f"{focus.capitalize()} peut être étudié à partir d'exemples concrets d'objets ou de systèmes.", "correct": True, "explanation": "Les exemples réels aident à comprendre les fonctions techniques et les choix de conception."},
            {"id": f"{qid}_3", "type": "qcm", "question": f"Quel outil est souvent utile pour comprendre {focus} ?", "options": ["Un schéma ou une maquette", "Une fable", "Une carte des climats", "Une dictée"], "correct_option": "Un schéma ou une maquette", "explanation": "Les schémas, maquettes et prototypes permettent de visualiser le fonctionnement d'un objet technique."},
            {"id": f"{qid}_4", "type": "vrai-faux", "question": "Analyser un objet technique permet de repérer ses fonctions principales et ses contraintes.", "correct": True, "explanation": "L'analyse fonctionnelle sert à comprendre ce que fait l'objet et dans quelles conditions il doit fonctionner."},
            {"id": f"{qid}_5", "type": "qcm", "question": f"Quel est l'objectif principal d'un exercice sur {focus} ?", "options": ["Comprendre et expliquer un choix technique", "Apprendre sans vérifier", "Répondre au hasard", "Éviter les documents"], "correct_option": "Comprendre et expliquer un choix technique", "explanation": "En technologie, on cherche à justifier les choix de matériaux, d'énergie, de forme ou de programmation."},
            {"id": f"{qid}_6", "type": "vrai-faux", "question": "Une bonne réponse en technologie peut être appuyée par une observation, un schéma ou un exemple d'usage.", "correct": True, "explanation": "Justifier sa réponse avec un exemple concret ou un schéma renforce la compréhension."},
            {"id": f"{qid}_7", "type": "qcm", "question": f"Quelle méthode aide à progresser sur {focus} ?", "options": ["S'entraîner, tester et corriger", "Ne jamais relire", "Ignorer les consignes", "Répondre sans observer"], "correct_option": "S'entraîner, tester et corriger", "explanation": "La progression passe par l'essai, l'observation et la correction des erreurs."},
            {"id": f"{qid}_8", "type": "vrai-faux", "question": "En technologie, un raisonnement clair vaut mieux qu'une réponse donnée sans explication.", "correct": True, "explanation": "Expliquer son raisonnement montre que l'on comprend le système étudié."},
        ],
    )


quizzes_data.extend(build_techno3_complement(*spec) for spec in TECHNO3_COMPLEMENT_SPECS)


def write_quiz_files():
    os.makedirs(TECHNO3_QUIZ_DIR, exist_ok=True)
    os.makedirs(TECHNO3_ANSWERS_DIR, exist_ok=True)
    os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
    os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
    os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
    os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)

    for qid, title, subject, level, questions in quizzes_data:
        quiz_obj = make_quiz(qid, title, subject, level, questions)
        quiz_obj = normalize_text_payload(quiz_obj)
        quiz_path = os.path.join(TECHNO3_QUIZ_DIR, f"{qid}.json")
        with open(quiz_path, "w", encoding="utf-8", newline="\n") as file_handle:
            json.dump(quiz_obj, file_handle, ensure_ascii=False, indent=2)
            file_handle.write("\n")

        answers_obj = make_answers(qid, title, subject, level, questions)
        answers_obj = normalize_text_payload(answers_obj)
        answers_path = os.path.join(TECHNO3_ANSWERS_DIR, f"{qid}.json")
        with open(answers_path, "w", encoding="utf-8", newline="\n") as file_handle:
            json.dump(answers_obj, file_handle, ensure_ascii=False, indent=2)
            file_handle.write("\n")

        with open(os.path.join(OUTPUT_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as file_handle:
            json.dump(quiz_obj, file_handle, ensure_ascii=False, indent=2)
            file_handle.write("\n")

        with open(os.path.join(OUTPUT_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as file_handle:
            json.dump(answers_obj, file_handle, ensure_ascii=False, indent=2)
            file_handle.write("\n")

        with open(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as file_handle:
            json.dump(quiz_obj, file_handle, ensure_ascii=False, indent=2)
            file_handle.write("\n")

        with open(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as file_handle:
            json.dump(answers_obj, file_handle, ensure_ascii=False, indent=2)
            file_handle.write("\n")

        print(f"[OK] Quiz {qid} - {title} ({len(questions)} questions)")

    if quizzes_data:
        first_id = quizzes_data[0][0]
        last_id = quizzes_data[-1][0]
    else:
        first_id = "N/A"
        last_id = "N/A"

    print("\n" + "=" * 60)
    print("  BATCH F - TECHNOLOGIE 3eme TERMINE")
    print(f"  {len(quizzes_data)} quizzes generes (IDs {first_id}-{last_id})")
    print(f"  {len(quizzes_data) * 6} fichiers JSON crees")
    print(f"  ({TECHNO3_QUIZ_DIR} + {TECHNO3_ANSWERS_DIR})")
    print(f"  ({OUTPUT_QUIZ_DIR} + {OUTPUT_ANSWERS_DIR})")
    print(f"  ({RUNTIME_QUIZ_DIR} + {RUNTIME_ANSWERS_DIR})")
    print(f"  {len(quizzes_data) * 8} questions au total")
    print("=" * 60)


if __name__ == "__main__":
    write_quiz_files()


