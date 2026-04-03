#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Batch I — 1ère Histoire-Géographie, Géopolitique et Sciences Politiques (HGGSP)
version progressive | Batch I HGGSP 1ère
Auteur : MonCoachScolaire
"""

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", ".."))
HGGSP1_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "hggsp_1ere_quizzes")
HGGSP1_QUIZ_DIR = os.path.join(HGGSP1_OUTPUT_DIR, "quiz")
HGGSP1_ANSWERS_DIR = os.path.join(HGGSP1_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "hggsp_1ere_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

os.makedirs(HGGSP1_QUIZ_DIR, exist_ok=True)
os.makedirs(HGGSP1_ANSWERS_DIR, exist_ok=True)
os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)


def fix_mojibake_text(value):
    if not isinstance(value, str):
        return value

    markers = ("Ã", "Â", "â€", "â€™", "â€œ", "â—", "â€“", "Å")
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
        "Ã©": "é",
        "Ã¨": "è",
        "Ãª": "ê",
        "Ã«": "ë",
        "Ã ": "à",
        "Ã¢": "â",
        "Ã´": "ô",
        "Ã»": "û",
        "Ã¹": "ù",
        "Ã®": "î",
        "Ã¯": "ï",
        "Ã§": "ç",
        "Ã‰": "É",
        "Ã€": "À",
        "Ã‡": "Ç",
        "Å“": "œ",
        "Â": "",
        "â€™": "’",
        "â€œ": "“",
        "â€\x9d": "”",
        "â€“": "–",
        "â—": "—",
        "â€¦": "…",
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
    qtype = str(question_type or "texte").strip().lower().replace("_", "-")
    if qtype in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if qtype == "qcm":
        return "qcm"
    return "texte"

def make_quiz(qid, title, subject, level, questions):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    questions = [{k: v for k, v in q.items() if k not in answer_keys} for q in questions]
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []

    for question in questions:
        qtype = normalize_question_type(question.get("type", "texte"))
        if qtype == "qcm":
            runtime_questions.append({
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            })
        elif qtype == "vrai-faux":
            runtime_questions.append({
                "type": "vrai-faux",
                "question": str(question.get("question", "")),
            })
        else:
            runtime_questions.append({
                "type": "open",
                "question": str(question.get("question", "")),
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
    for index, question in enumerate(questions):
        qtype = normalize_question_type(question.get("type", "texte"))
        if qtype == "qcm":
            options = list(question.get("options", []))
            correct_answer = str(question.get("correct_option", question.get("correct_answer", "")))
            correct_index = options.index(correct_answer) if correct_answer in options else 0
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "qcm",
                "answer": correct_answer,
                "correct": correct_index,
                "correction": str(question.get("explanation", "")),
            })
        elif qtype == "vrai-faux":
            tf_source = question.get("correct", question.get("correct_answer", "faux"))
            tf_answer = "vrai" if str(tf_source).strip().lower() in {"true", "vrai", "1"} else "faux"
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": tf_answer,
                "correction": str(question.get("explanation", "")),
            })
        else:
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "open",
                "answer": str(question.get("correct_answer", "")),
                "correction": str(question.get("explanation", "")),
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

# ─────────────────────────────────────────────────────────
# DONNÉES — quizzes HGGSP 1ère (version progressive)
# ─────────────────────────────────────────────────────────
quizzes_data = [

# ══════════════════════════════════════════════════════════
# BLOC 1 — L'ESPACE EXTRA-ATMOSPHÉRIQUE (915–920)
# ══════════════════════════════════════════════════════════

(915, "Histoire de la conquête spatiale", "HGGSP", "1ère", [
    {"id":"915_1","type":"qcm","question":"Quelle nation a lancé le premier satellite artificiel, Spoutnik 1, en 1957 ?",
     "options":["Les États-Unis","L'Union soviétique","La France","Le Royaume-Uni"],
     "correct_answer":"L'Union soviétique",
     "explanation":"Spoutnik 1 fut lancé le 4 octobre 1957 par l'URSS, déclenchant la 'course à l'espace' entre les deux superpuissances de la Guerre froide."},
    {"id":"915_2","type":"vrai-faux","question":"Neil Armstrong fut le premier humain à marcher sur la Lune en 1969.",
     "correct_answer":"Vrai",
     "explanation":"Le 21 juillet 1969, lors de la mission Apollo 11, Neil Armstrong et Buzz Aldrin marchèrent sur la Lune. C'est une victoire américaine dans la course à l'espace."},
    {"id":"915_3","type":"texte","question":"Pourquoi la conquête spatiale des années 1957-1969 est-elle indissociable du contexte de Guerre froide ?",
     "correct_answer":"La course à l'espace était une compétition idéologique et technologique entre les États-Unis et l'URSS. Elle permettait d'affirmer la supériorité de son système (capitalisme vs communisme), avait des applications militaires (missiles balistiques) et symbolisait la puissance nationale. Les succès spatiaux étaient des outils de propagande et de prestige.",
     "explanation":"La conquête spatiale illustre parfaitement la rivalité bipolaire de la Guerre froide, où science et technologie devenaient des armes géopolitiques."},
    {"id":"915_4","type":"qcm","question":"Quel est le premier homme à avoir voyagé dans l'espace (1961) ?",
     "options":["Buzz Aldrin","John Glenn","Youri Gagarine","Alan Shepard"],
     "correct_answer":"Youri Gagarine",
     "explanation":"Youri Gagarine (URSS) effectua le premier vol habité dans l'espace le 12 avril 1961 à bord de Vostok 1, devenant un héros mondial."},
    {"id":"915_5","type":"vrai-faux","question":"Le traité de l'espace de 1967 interdit la mise en orbite d'armes nucléaires.",
     "correct_answer":"Vrai",
     "explanation":"Le Traité sur l'espace extra-atmosphérique de 1967 interdit le placement d'armes de destruction massive en orbite et déclare l'espace 'province de toute l'humanité'."},
    {"id":"915_6","type":"texte","question":"Quelles sont les principales agences spatiales mondiales et leurs pays d'appartenance ?",
     "correct_answer":"NASA (États-Unis), Roscosmos (Russie), ESA (Agence Spatiale Européenne, dont la France avec le CNES), CNSA (Chine), ISRO (Inde), JAXA (Japon). L'ESA est une organisation multinationale de 22 pays membres.",
     "explanation":"La multiplication des agences spatiales reflète la diffusion du pouvoir spatial au-delà des deux superpuissances originelles."},
    {"id":"915_7","type":"qcm","question":"La Station spatiale internationale (ISS) est le fruit d'une coopération entre :",
     "options":["Les États-Unis uniquement","Les États-Unis, la Russie, l'Europe, le Japon et le Canada","L'ONU et ses États membres","Les pays du G20"],
     "correct_answer":"Les États-Unis, la Russie, l'Europe, le Japon et le Canada",
     "explanation":"L'ISS, construite depuis 1998, est un exemple rare de coopération internationale, réunissant d'anciens rivaux de la Guerre froide."},
    {"id":"915_8","type":"vrai-faux","question":"La navette spatiale américaine fut mise en service en 1981 et retirée en 2011.",
     "correct_answer":"Vrai",
     "explanation":"La navette spatiale (Space Shuttle) a effectué 135 missions entre 1981 et 2011, dont les catastrophes Challenger (1986) et Columbia (2003)."},
]),

(916, "Les nouveaux acteurs de la conquête spatiale", "HGGSP", "1ère", [
    {"id":"916_1","type":"qcm","question":"Quelle entreprise privée d'Elon Musk est aujourd'hui leader mondial du lancement commercial de fusées ?",
     "options":["Blue Origin","Virgin Galactic","SpaceX","Boeing"],
     "correct_answer":"SpaceX",
     "explanation":"SpaceX (Space Exploration Technologies) est fondée en 2002. Elle développe les fusées Falcon 9 et Starship, et transporte des astronautes vers l'ISS depuis 2020."},
    {"id":"916_2","type":"vrai-faux","question":"La Chine est devenue la troisième nation à envoyer un homme dans l'espace en 2003.",
     "correct_answer":"Vrai",
     "explanation":"Yang Liwei fut le premier taikonaute (astronaute chinois) en octobre 2003, à bord de Shenzhou 5. La Chine est la troisième nation après l'URSS/Russie et les États-Unis."},
    {"id":"916_3","type":"texte","question":"Qu'est-ce que le New Space et en quoi représente-t-il un changement dans le secteur spatial ?",
     "correct_answer":"Le New Space désigne l'émergence d'acteurs privés (SpaceX, Blue Origin, OneWeb, Planet Labs…) qui révolutionnent le secteur spatial. Ils réduisent les coûts (fusées réutilisables), accélèrent l'innovation et commercialisent l'accès à l'espace (tourisme, petits satellites, Internet par satellite). L'espace n'est plus uniquement une affaire d'États.",
     "explanation":"Le New Space soulève des questions de régulation, de gouvernance et de partage des ressources spatiales."},
    {"id":"916_4","type":"qcm","question":"Le programme Artémis de la NASA vise à :",
     "options":["Explorer Mars","Retourner des astronautes sur la Lune, dont la première femme","Construire une nouvelle station spatiale","Envoyer des touristes dans l'espace"],
     "correct_answer":"Retourner des astronautes sur la Lune, dont la première femme",
     "explanation":"Artémis (à partir de 2022) vise à établir une présence humaine durable sur la Lune, avec un regard sur Mars. La première femme et la première personne de couleur sur la Lune sont prévues."},
    {"id":"916_5","type":"vrai-faux","question":"L'Inde a réussi à atterrir sur la Lune en 2023 avec sa mission Chandrayaan-3.",
     "correct_answer":"Vrai",
     "explanation":"En août 2023, l'Inde est devenue le 4e pays à atterrir sur la Lune et le 1er à se poser au pôle sud lunaire avec Chandrayaan-3."},
    {"id":"916_6","type":"texte","question":"Quels sont les enjeux économiques liés aux ressources de la Lune ?",
     "correct_answer":"La Lune contient de l'hélium-3 (potentiel combustible pour la fusion nucléaire), des minéraux rares, de la glace d'eau aux pôles (utilisable pour l'eau et le carburant). Ces ressources suscitent un intérêt croissant des États (USA, Chine, Russie) et du secteur privé, rouvrant des débats sur la propriété des ressources spatiales.",
     "explanation":"Les Accords Artémis (2020, initiés par les USA) et le Traité de l'espace de 1967 s'affrontent sur la question de l'exploitation des ressources lunaires."},
    {"id":"916_7","type":"qcm","question":"Starlink, le réseau de satellites de SpaceX, fournit :",
     "options":["Des services de météorologie","Un accès à Internet par satellite dans le monde entier","Des images d'espionnage","Des données GPS militaires"],
     "correct_answer":"Un accès à Internet par satellite dans le monde entier",
     "explanation":"Starlink est une constellation de milliers de petits satellites en orbite basse (LEO) fournissant l'Internet haut débit, y compris en zones rurales ou de conflit (Ukraine)."},
    {"id":"916_8","type":"vrai-faux","question":"La multiplication des satellites en orbite basse crée un risque de pollution spatiale (débris).",
     "correct_answer":"Vrai",
     "explanation":"Le syndrome de Kessler : une accumulation de débris spatiaux pourrait rendre certaines orbites inutilisables. L'ESA et d'autres agences travaillent à des solutions de nettoyage orbital."},
]),

(917, "L'espace, enjeu géopolitique", "HGGSP", "1ère", [
    {"id":"917_1","type":"qcm","question":"Le Traité de l'espace de 1967 qualifie l'espace extra-atmosphérique de :",
     "options":["Territoire américain","Zone militarisée","Province de toute l'humanité, non soumise à appropriation nationale","Zone économique exclusive des grandes puissances"],
     "correct_answer":"Province de toute l'humanité, non soumise à appropriation nationale",
     "explanation":"L'article II du Traité de 1967 interdit toute revendication de souveraineté nationale sur l'espace, la Lune et les corps célestes."},
    {"id":"917_2","type":"vrai-faux","question":"Les orbites géostationnaires (GEO) sont une ressource limitée et objet de compétition entre États.",
     "correct_answer":"Vrai",
     "explanation":"L'orbite géostationnaire est à 36 000 km d'altitude. Elle permet aux satellites de rester fixes par rapport au sol. Les positions sont attribuées par l'UIT mais convoitées."},
    {"id":"917_3","type":"texte","question":"En quoi le contrôle des satellites est-il un enjeu stratégique militaire ?",
     "correct_answer":"Les satellites militaires assurent le renseignement (espionnage, imagerie), les communications des forces armées, la navigation (GPS, GLONASS, Galileo), la détection de missiles et les drones armés. La destruction de satellites adverses (antisatellites, ASAT) est une capacité militaire développée par USA, Russie, Chine et Inde.",
     "explanation":"L'espace est devenu un domaine militaire à part entière ('domain warfare'). La France a annoncé en 2019 une stratégie de défense spatiale."},
    {"id":"917_4","type":"qcm","question":"Le GPS américain est un système de navigation par satellite militaire, mis à disposition civile depuis :",
     "options":["1957","1980","1983 (ouverture civile progressive)","2000"],
     "correct_answer":"2000",
     "explanation":"Le GPS (Global Positioning System) fut développé par l'armée américaine. En 2000, Bill Clinton mit fin à la dégradation volontaire du signal civil (Selective Availability), rendant le GPS pleinement précis pour tous."},
    {"id":"917_5","type":"vrai-faux","question":"L'Europe a développé son propre système de navigation par satellite, Galileo.",
     "correct_answer":"Vrai",
     "explanation":"Galileo est le système européen de navigation par satellite, géré par l'UE et l'ESA. Il offre une indépendance stratégique vis-à-vis du GPS américain, de GLONASS (Russie) et BeiDou (Chine)."},
    {"id":"917_6","type":"texte","question":"Quels sont les risques liés à la militarisation croissante de l'espace ?",
     "correct_answer":"Risques : course aux armements spatiaux (tests antisatellites), création de débris menaçant toutes les orbites, escalade possible vers des conflits terrestres (dépendance militaire aux satellites), absence d'un cadre juridique adapté aux nouvelles capacités (cyberattaques spatiales, armes antisatellites).",
     "explanation":"Le Traité de 1967 interdit les armes de destruction massive dans l'espace mais pas les armes conventionnelles ni les antisatellites."},
    {"id":"917_7","type":"qcm","question":"La constellation militaire Syracuse, mise en service par la France, est un réseau de :",
     "options":["Drones militaires","Satellites de communication militaires","Missiles antisatellites","Sondes spatiales"],
     "correct_answer":"Satellites de communication militaires",
     "explanation":"Syracuse est le système de télécommunications militaires français par satellite, géré par l'armée. La France a aussi développé des capacités de surveillance spatiale (GRAVES, GEOINT)."},
    {"id":"917_8","type":"vrai-faux","question":"En 2021, la France a créé un Commandement de l'espace (CDE) rattaché à l'armée de l'air.",
     "correct_answer":"Vrai",
     "explanation":"Le Commandement de l'espace (CDE), créé en 2019 au sein de l'armée de l'Air et de l'Espace (renommée en 2020), assure la défense des intérêts français dans l'espace."},
]),

(918, "Les usages civils et économiques de l'espace", "HGGSP", "1ère", [
    {"id":"918_1","type":"qcm","question":"Lequel de ces services du quotidien repose directement sur des satellites ?",
     "options":["La climatisation","La navigation GPS","Les réseaux électriques","Les transports en commun"],
     "correct_answer":"La navigation GPS",
     "explanation":"GPS, météo, télécommunications, télévision, internet par satellite sont des usages quotidiens reposant sur des satellites. Environ 50 applications civiles dépendent du GPS."},
    {"id":"918_2","type":"vrai-faux","question":"L'observation de la Terre par satellite permet de surveiller les changements climatiques.",
     "correct_answer":"Vrai",
     "explanation":"Les satellites d'observation (Copernicus/ESA, Landsat/NASA) mesurent la fonte des glaces, la déforestation, les niveaux des mers, la qualité de l'air. Ils sont indispensables aux études climatiques."},
    {"id":"918_3","type":"texte","question":"Comment les satellites météorologiques contribuent-ils à la prévision des catastrophes naturelles ?",
     "correct_answer":"Les satellites météo (Météosat, NOAA…) fournissent des images en temps réel des systèmes nuageux, cyclones, fronts, permettant des prévisions à 1-10 jours. Ils améliorent les alertes aux cyclones, inondations, sécheresses, sauvant des vies en permettant des évacuations préventives.",
     "explanation":"Sans satellites, la prévision météorologique serait limitée aux mesures au sol, perdant la vision globale indispensable."},
    {"id":"918_4","type":"qcm","question":"Ariane 5 et Ariane 6 sont des lanceurs développés par :",
     "options":["La NASA","ArianeSpace / ESA (initiative européenne)","La Russie","SpaceX"],
     "correct_answer":"ArianeSpace / ESA (initiative européenne)",
     "explanation":"ArianeSpace est une entreprise franco-européenne qui commercialise les lanceurs Ariane. Ariane 6 a effectué son premier vol en 2024, assurant l'autonomie d'accès à l'espace de l'Europe."},
    {"id":"918_5","type":"vrai-faux","question":"Le tourisme spatial est aujourd'hui accessible uniquement aux astronautes professionnels.",
     "correct_answer":"Faux",
     "explanation":"SpaceX (Inspiration4, 2021), Blue Origin (New Shepard) et Virgin Galactic proposent des vols spatiaux aux touristes fortunés. Le tourisme spatial commercial est une réalité, même si très coûteux (~250 000 $ pour un vol suborbital)."},
    {"id":"918_6","type":"texte","question":"Quels sont les enjeux de l'exploitation des ressources minières dans l'espace (astéroïdes, Lune) ?",
     "correct_answer":"Les astéroïdes contiennent des métaux précieux (platine, or, nickel) en quantités considérables. La Lune a de l'hélium-3 et de la glace. Des entreprises (Planetary Resources, Deep Space Industries) et des États (USA, Luxembourg) ont adopté des lois autorisant la propriété des ressources extraites, contournant le principe de bien commun du Traité de 1967.",
     "explanation":"L'exploitation spatiale pourrait répondre à la raréfaction terrestre de métaux critiques mais soulève des questions éthiques et juridiques sur le partage."},
    {"id":"918_7","type":"qcm","question":"Copernicus est un programme d'observation de la Terre géré par :",
     "options":["La NASA","L'ESA et l'Union européenne","L'ONU","La Chine"],
     "correct_answer":"L'ESA et l'Union européenne",
     "explanation":"Copernicus est le programme phare de l'ESA/UE pour l'observation de la Terre, fournissant des données libres d'accès pour l'environnement, l'agriculture, les urgences, la sécurité maritime."},
    {"id":"918_8","type":"vrai-faux","question":"L'économie spatiale mondiale représente aujourd'hui plus de 400 milliards de dollars par an.",
     "correct_answer":"Vrai",
     "explanation":"Le secteur spatial mondial dépasse 400 milliards de dollars annuels (2022) et pourrait atteindre 1 000 milliards en 2040 selon Morgan Stanley, notamment grâce au New Space."},
]),

(919, "La militarisation et l'arsenalisation de l'espace", "HGGSP", "1ère", [
    {"id":"919_1","type":"qcm","question":"Un missile antisatellite (ASAT) a pour but de :",
     "options":["Mettre en orbite de nouveaux satellites","Détruire ou neutraliser des satellites adverses","Améliorer les communications spatiales","Cartographier les astéroïdes"],
     "correct_answer":"Détruire ou neutraliser des satellites adverses",
     "explanation":"Les ASAT sont des armes capables d'intercepter des satellites. Ils ont été testés par les USA (1985, 2008), la Chine (2007), l'Inde (2019) et la Russie (2021)."},
    {"id":"919_2","type":"vrai-faux","question":"Le test ASAT chinois de 2007 a créé des milliers de débris spatiaux menaçant d'autres satellites.",
     "correct_answer":"Vrai",
     "explanation":"En janvier 2007, la Chine a détruit son propre satellite météo Fengyun-1C, créant plus de 3 000 débris trackables et des centaines de milliers de plus petits, polluant l'orbite pendant des décennies."},
    {"id":"919_3","type":"texte","question":"Distinguez militarisation et arsenalisation de l'espace.",
     "correct_answer":"Militarisation : utilisation de l'espace à des fins militaires (satellites de renseignement, navigation, communication) — existe depuis les années 1960 et est légale. Arsenalisation : déploiement d'armes dans l'espace pour mener des attaques (ASAT, armes en orbite) — interdit pour les ADM par le Traité de 1967, mais les armes conventionnelles dans l'espace ne sont pas clairement interdites.",
     "explanation":"Le glissement vers l'arsenalisation constitue une escalade stratégique préoccupante."},
    {"id":"919_4","type":"qcm","question":"Quel pays a créé une 'Space Force' en 2019 en tant que 6e branche de ses forces armées ?",
     "options":["La France","La Russie","La Chine","Les États-Unis"],
     "correct_answer":"Les États-Unis",
     "explanation":"La United States Space Force (USSF) fut créée le 20 décembre 2019 sous Trump, devenant la première nouvelle branche militaire américaine depuis 1947."},
    {"id":"919_5","type":"vrai-faux","question":"L'OTAN a reconnu l'espace comme un domaine opérationnel militaire en 2019.",
     "correct_answer":"Vrai",
     "explanation":"En 2019, les membres de l'OTAN ont officiellement reconnu l'espace comme 5e domaine opérationnel (avec terre, mer, air, cyber), soulignant son importance stratégique."},
    {"id":"919_6","type":"texte","question":"Quelles sont les formes de guerre asymétrique applicables à l'espace ?",
     "correct_answer":"Cyberattaques contre des satellites (brouillage, leurrage GPS, piratage), attaques co-orbitales (satellite tueur s'approchant d'un autre), brouillage des communications, attaques laser depuis le sol (DAZZLERS), déni de service GPS. Ces attaques sont difficiles à attribuer et à défendre.",
     "explanation":"La dépendance croissante aux satellites crée des vulnérabilités stratégiques exploitables par des acteurs étatiques ou non."},
    {"id":"919_7","type":"qcm","question":"Le brouillage GPS (GPS spoofing) consiste à :",
     "options":["Améliorer la précision GPS","Envoyer de faux signaux GPS pour tromper les systèmes de navigation","Bloquer toutes les communications radio","Détruire des satellites GPS"],
     "correct_answer":"Envoyer de faux signaux GPS pour tromper les systèmes de navigation",
     "explanation":"Le spoofing GPS peut faire croire à un navire ou un drone qu'il est à une position erronée. Des cas ont été détectés en mer Noire et dans d'autres zones de tension géopolitique."},
    {"id":"919_8","type":"vrai-faux","question":"L'absence de règles claires sur les conflits spatiaux crée un risque d'escalade non contrôlée.",
     "correct_answer":"Vrai",
     "explanation":"Le droit international spatial est insuffisant pour couvrir les nouvelles formes de conflits. Des initiatives (comme le Code de conduite de l'UE) tentent de combler ce vide sans succès pour l'instant."},
]),

(920, "L'espace, bien commun de l'humanité ?", "HGGSP", "1ère", [
    {"id":"920_1","type":"qcm","question":"Quel principe fondamental du Traité de l'espace de 1967 encadre l'accès à l'espace ?",
     "options":["L'espace appartient aux nations développées","L'espace est un bien commun de toute l'humanité, non appropriable","L'espace est géré par l'ONU","L'espace est partagé entre les grandes puissances"],
     "correct_answer":"L'espace est un bien commun de toute l'humanité, non appropriable",
     "explanation":"Le concept de 'province de toute l'humanité' (common heritage of mankind) interdit toute revendication de souveraineté sur l'espace et les corps célestes."},
    {"id":"920_2","type":"vrai-faux","question":"Les accords Artémis (2020) respectent pleinement le principe de patrimoine commun du Traité de 1967.",
     "correct_answer":"Faux",
     "explanation":"Les accords Artémis, promus par les États-Unis, autorisent l'extraction et la propriété de ressources spatiales, ce que certains (Russie, Chine) considèrent contraire au Traité de 1967."},
    {"id":"920_3","type":"texte","question":"Pourquoi les pays en développement défendent-ils le principe de 'patrimoine commun de l'humanité' pour l'espace ?",
     "correct_answer":"Les pays en développement n'ont pas les moyens techniques et financiers d'accéder à l'espace. Le principe de patrimoine commun garantit que les ressources spatiales bénéficieraient à tous, pas seulement aux grandes puissances spatiales. Sans ce principe, l'espace risque de devenir le monopole de quelques nations riches.",
     "explanation":"Ce débat rappelle celui sur le droit de la mer (CNUDM, 1982) et le partage des ressources des grands fonds marins."},
    {"id":"920_4","type":"qcm","question":"L'UNOOSA est :",
     "options":["Une agence spatiale nationale","Le Bureau des Nations Unies pour les affaires de l'espace extra-atmosphérique","Un traité de désarmement spatial","Une entreprise privée de lanceurs"],
     "correct_answer":"Le Bureau des Nations Unies pour les affaires de l'espace extra-atmosphérique",
     "explanation":"L'UNOOSA (United Nations Office for Outer Space Affairs) coordonne la coopération internationale pacifique dans l'espace et administre les traités spatiaux."},
    {"id":"920_5","type":"vrai-faux","question":"La prolifération des débris spatiaux menace l'accès futur à l'espace pour toutes les nations.",
     "correct_answer":"Vrai",
     "explanation":"Le syndrome de Kessler décrit un scénario catastrophique : les débris créent de nouveaux débris en cascade, rendant certaines orbites inutilisables pour des générations."},
    {"id":"920_6","type":"texte","question":"Quels sont les défis de la gouvernance internationale de l'espace au XXIe siècle ?",
     "correct_answer":"Prolifération des acteurs (privés, nouveaux États), absence de règles sur les ASAT, gestion des débris, régulation de l'exploitation des ressources, prévention des conflits, équité d'accès pour les pays en développement, attribution des orbites. Le Traité de 1967 est insuffisant face à ces nouveaux enjeux.",
     "explanation":"Une réforme de la gouvernance spatiale internationale est jugée nécessaire par de nombreux experts mais se heurte aux rivalités entre puissances."},
    {"id":"920_7","type":"qcm","question":"Le Comité des Nations Unies sur les utilisations pacifiques de l'espace extra-atmosphérique (COPUOS) est chargé de :",
     "options":["Construire des satellites pour les pays pauvres","Promouvoir la coopération internationale et élaborer les normes spatiales","Gérer les vols habités vers la Lune","Surveiller les lancements militaires"],
     "correct_answer":"Promouvoir la coopération internationale et élaborer les normes spatiales",
     "explanation":"Le COPUOS (Committee on the Peaceful Uses of Outer Space) est le principal forum onusien sur l'espace. Il a produit les cinq traités spatiaux majeurs."},
    {"id":"920_8","type":"vrai-faux","question":"Les Pays-Bas, le Luxembourg et les Émirats Arabes Unis ont adopté des lois autorisant l'exploitation privée des ressources spatiales.",
     "correct_answer":"Vrai",
     "explanation":"Après les USA (2015), plusieurs pays ont adopté des législations nationales autorisant leurs entreprises à s'approprier les ressources extraites dans l'espace, contournant le cadre multilatéral."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 2 — FONDS MARINS ET CYBERESPACE (921–926)
# ══════════════════════════════════════════════════════════

(921, "Les fonds marins : exploration et ressources", "HGGSP", "1ère", [
    {"id":"921_1","type":"qcm","question":"Quel pourcentage des fonds marins est aujourd'hui encore inexploré ?",
     "options":["20 %","50 %","80 %","95 %"],
     "correct_answer":"80 %",
     "explanation":"Plus de 80 % des fonds marins demeurent inexplorés ou mal cartographiés, faisant de l'océan profond le dernier grand espace terra incognita."},
    {"id":"921_2","type":"vrai-faux","question":"Les fonds marins contiennent des nodules polymétalliques riches en manganèse, cobalt, nickel et cuivre.",
     "correct_answer":"Vrai",
     "explanation":"Les nodules polymétalliques (manganese nodules) se trouvent à 4-5 km de profondeur et contiennent des métaux essentiels aux technologies vertes (batteries électriques, éoliennes)."},
    {"id":"921_3","type":"texte","question":"Qu'est-ce qu'un champ hydrothermal sous-marin et quelles ressources contient-il ?",
     "correct_answer":"Les champs hydrothermaux (ou fumeurs noirs) sont des sources d'eau chaude et chargée de minéraux aux dorsales océaniques. Ils forment des sulfures massifs riches en or, argent, zinc, cuivre, cobalt. Ils abritent aussi des écosystèmes uniques (tubeworms, bactéries chimiosynthétiques) sans lumière solaire.",
     "explanation":"L'exploitation des champs hydrothermaux est techniquement possible mais menacerait des écosystèmes uniques et encore mal connus."},
    {"id":"921_4","type":"qcm","question":"Les hydrates de méthane présents dans les fonds marins représentent :",
     "options":["Une source d'eau douce","Une réserve d'énergie considérable, mais aussi un risque climatique","Des déchets industriels","Un habitat pour les baleines"],
     "correct_answer":"Une réserve d'énergie considérable, mais aussi un risque climatique",
     "explanation":"Les hydrates de méthane stockent d'immenses quantités de méthane. Leur exploitation énergétique est étudiée (Japon), mais leur libération par réchauffement constitue un risque climatique majeur."},
    {"id":"921_5","type":"vrai-faux","question":"La Mission Nekton a cartographié une partie des profondeurs océaniques de l'Atlantique nord.",
     "correct_answer":"Vrai",
     "explanation":"Des missions comme Nekton, et des programmes comme le Seabed 2030 visent à cartographier intégralement les fonds marins d'ici 2030, utilisant des engins sous-marins autonomes."},
    {"id":"921_6","type":"texte","question":"Pourquoi l'exploitation des fonds marins soulève-t-elle des questions environnementales ?",
     "correct_answer":"L'exploitation minière profonde (deep sea mining) crée des panaches de sédiments, détruit des habitats uniques, génère des bruits et vibrations perturbant la faune, et affecte des écosystèmes encore mal connus. Une exploitation irréversible d'espèces et d'écosystèmes jamais étudiés est une préoccupation majeure des scientifiques.",
     "explanation":"Plusieurs États et ONG (WWF, Greenpeace) demandent un moratoire sur l'exploitation des grands fonds en attendant une meilleure connaissance scientifique."},
    {"id":"921_7","type":"qcm","question":"L'Autorité internationale des fonds marins (AIFM) est chargée de :",
     "options":["Construire des sous-marins militaires","Réglementer l'exploration et l'exploitation des ressources des fonds marins internationaux","Protéger la biodiversité marine","Surveiller les câbles sous-marins"],
     "correct_answer":"Réglementer l'exploration et l'exploitation des ressources des fonds marins internationaux",
     "explanation":"L'AIFM (International Seabed Authority) est créée par la CNUDM de 1982. Elle attribue des licences d'exploration et partage les bénéfices avec les pays en développement."},
    {"id":"921_8","type":"vrai-faux","question":"Les câbles sous-marins de fibres optiques transportent plus de 95 % du trafic internet mondial.",
     "correct_answer":"Vrai",
     "explanation":"Il existe plus de 400 câbles sous-marins totalisant plus de 1,3 million de km. Leur coupure (sabotage, accident) aurait des conséquences catastrophiques sur les communications mondiales."},
]),

(922, "La gouvernance des océans", "HGGSP", "1ère", [
    {"id":"922_1","type":"qcm","question":"La Convention des Nations Unies sur le droit de la mer (CNUDM / UNCLOS) a été signée en :",
     "options":["1945","1967","1982","2000"],
     "correct_answer":"1982",
     "explanation":"La CNUDM (Convention de Montego Bay, 1982) est le cadre juridique fondamental régissant les espaces maritimes : eaux territoriales (12 nm), ZEE (200 nm), plateau continental, haute mer."},
    {"id":"922_2","type":"vrai-faux","question":"Dans sa Zone Économique Exclusive (ZEE), un État exerce une souveraineté complète.",
     "correct_answer":"Faux",
     "explanation":"Dans la ZEE (200 miles nautiques), l'État côtier a des droits souverains sur les ressources (pêche, pétrole, minéraux) mais pas une souveraineté pleine. Les navires étrangers ont la liberté de navigation."},
    {"id":"922_3","type":"texte","question":"Quels sont les principaux défis de la gouvernance des espaces maritimes aujourd'hui ?",
     "correct_answer":"Surpêche et effondrement des stocks, pollution plastique et chimique, changement climatique (montée des eaux), piraterie maritime (golfe de Guinée, Somalie), conflits de ZEE (mer de Chine méridionale), câbles sous-marins vulnérables, exploitation non réglementée des grands fonds.",
     "explanation":"La CNUDM est un cadre robuste mais insuffisant face à des acteurs non signataires (USA) ou qui la contournent (Chine en mer de Chine du Sud)."},
    {"id":"922_4","type":"qcm","question":"La mer de Chine méridionale est un foyer de tensions car :",
     "options":["Elle est la principale route de l'Atlantique","Elle est revendiquée quasi intégralement par la Chine malgré les droits d'autres pays riverains","Elle abrite l'unique base navale américaine en Asie","Elle est polluée par des déversements pétroliers"],
     "correct_answer":"Elle est revendiquée quasi intégralement par la Chine malgré les droits d'autres pays riverains",
     "explanation":"La 'ligne des neuf traits' chinoise englobe ~90 % de la mer de Chine méridionale, chevauchant les ZEE du Vietnam, des Philippines, de la Malaisie, de Brunei. La CPA a invalidé les revendications chinoises en 2016."},
    {"id":"922_5","type":"vrai-faux","question":"La haute mer représente la majorité de la surface des océans et n'est soumise à aucune juridiction nationale.",
     "correct_answer":"Vrai",
     "explanation":"La haute mer (au-delà des ZEE) représente environ 64 % des océans. Elle est régie par la liberté de navigation, mais échappait jusqu'à récemment à toute réglementation sur la biodiversité."},
    {"id":"922_6","type":"texte","question":"Présentez le Traité sur la haute mer (BBNJ) adopté en 2023 et ses enjeux.",
     "correct_answer":"Le Traité BBNJ (Biodiversity Beyond National Jurisdiction, 2023) est le premier accord international pour protéger la biodiversité marine en haute mer. Il crée des aires marines protégées, encadre les évaluations d'impact environnemental et organise le partage des ressources génétiques marines. Il complète la CNUDM.",
     "explanation":"Surnommé 'Traité des mers', il a nécessité 20 ans de négociations. Sa ratification et mise en œuvre restent des défis."},
    {"id":"922_7","type":"qcm","question":"La surpêche mondiale menace :",
     "options":["Uniquement les eaux territoriales","Les stocks halieutiques mondiaux, dont 35 % sont surexploités selon la FAO","Seulement les espèces rares","La navigation commerciale"],
     "correct_answer":"Les stocks halieutiques mondiaux, dont 35 % sont surexploités selon la FAO",
     "explanation":"La FAO estime qu'environ 35 % des stocks de poissons sont surexploités. La surpêche menace la sécurité alimentaire de milliards de personnes dépendant de la pêche."},
    {"id":"922_8","type":"vrai-faux","question":"La France possède la 2e ZEE mondiale grâce à ses territoires d'outre-mer.",
     "correct_answer":"Vrai",
     "explanation":"Avec ses territoires éparpillés dans tous les océans (Polynésie, Antilles, La Réunion, Nouvelle-Calédonie…), la France a la 2e ZEE mondiale après les États-Unis, d'environ 11 millions de km²."},
]),

(923, "Le cyberespace : définition et enjeux", "HGGSP", "1ère", [
    {"id":"923_1","type":"qcm","question":"Le cyberespace est défini comme :",
     "options":["Uniquement Internet et les réseaux sociaux","L'espace virtuel des réseaux numériques interconnectés (Internet, réseaux privés, systèmes de contrôle industriels)","Les satellites de communication","Les centres de données uniquement"],
     "correct_answer":"L'espace virtuel des réseaux numériques interconnectés (Internet, réseaux privés, systèmes de contrôle industriels)",
     "explanation":"Le cyberespace englobe tous les systèmes numériques interconnectés : Internet, réseaux militaires, systèmes SCADA (centrales nucléaires, eau, électricité), cloud computing."},
    {"id":"923_2","type":"vrai-faux","question":"Le cyberespace n'a pas de dimension géographique ou physique.",
     "correct_answer":"Faux",
     "explanation":"Le cyberespace a une dimension physique : câbles sous-marins, data centers, serveurs, terminaux. Sa localisation géographique a des implications juridiques et stratégiques."},
    {"id":"923_3","type":"texte","question":"Quelles sont les quatre couches du cyberespace ?",
     "correct_answer":"1. Couche physique (câbles, serveurs, routeurs, terminaux) ; 2. Couche logique (protocoles, logiciels, systèmes d'exploitation) ; 3. Couche données (informations circulant sur les réseaux) ; 4. Couche cognitive (perceptions, opinions, comportements influencés par le numérique).",
     "explanation":"Cette structuration en couches aide à comprendre les différentes formes de vulnérabilités et d'attaques dans le cyberespace."},
    {"id":"923_4","type":"qcm","question":"ARPANET, précurseur d'Internet, fut développé dans les années 1960 pour :",
     "options":["Le commerce électronique","Des communications militaires et scientifiques résilientes","Les réseaux sociaux","La télévision par câble"],
     "correct_answer":"Des communications militaires et scientifiques résilientes",
     "explanation":"ARPANET fut financé par le DARPA (agence militaire américaine). Sa conception distribuée visait à maintenir les communications même après une attaque nucléaire."},
    {"id":"923_5","type":"vrai-faux","question":"L'ICANN est l'organisme qui gère les noms de domaine et les adresses IP à l'échelle mondiale.",
     "correct_answer":"Vrai",
     "explanation":"L'ICANN (Internet Corporation for Assigned Names and Numbers) est une organisation privée américaine gérant les DNS et IP. Sa gouvernance est contestée par les États qui veulent plus de contrôle international."},
    {"id":"923_6","type":"texte","question":"Qu'est-ce que la fracture numérique et quelles en sont les dimensions ?",
     "correct_answer":"La fracture numérique désigne les inégalités d'accès et d'usage du numérique. Elle a plusieurs dimensions : accès (infrastructure, coût), compétences (utilisation, formation), usage (qualité de la connexion), et est visible entre pays développés/en développement, zones rurales/urbaines, générations, et catégories sociales.",
     "explanation":"Selon l'UIT, encore 2,7 milliards de personnes n'ont pas accès à Internet en 2023, soit environ un tiers de l'humanité."},
    {"id":"923_7","type":"qcm","question":"Le 'dark web' désigne :",
     "options":["Internet la nuit","La partie d'Internet non indexée par les moteurs de recherche, accessible via des logiciels spéciaux comme Tor","Les sites pornographiques","Les réseaux sociaux privés"],
     "correct_answer":"La partie d'Internet non indexée par les moteurs de recherche, accessible via des logiciels spéciaux comme Tor",
     "explanation":"Le dark web (sous-ensemble du deep web) est utilisé pour des activités illicites (trafics) mais aussi pour contourner la censure dans les régimes autoritaires (journalistes, dissidents)."},
    {"id":"923_8","type":"vrai-faux","question":"Les GAFAM (Google, Apple, Facebook/Meta, Amazon, Microsoft) exercent un pouvoir considérable sur le cyberespace.",
     "correct_answer":"Vrai",
     "explanation":"Les GAFAM contrôlent les plateformes, le cloud, les données et les infrastructures d'Internet. Leur pouvoir dépasse parfois celui des États, soulevant des questions de souveraineté numérique."},
]),

(924, "La cybersécurité et les cyberattaques", "HGGSP", "1ère", [
    {"id":"924_1","type":"qcm","question":"Stuxnet (2010) est considéré comme la première cyberarme utilisée contre une infrastructure industrielle. Elle visait :",
     "options":["Les réseaux électriques américains","Le programme nucléaire iranien","Les serveurs de l'ONU","Les réseaux bancaires russes"],
     "correct_answer":"Le programme nucléaire iranien",
     "explanation":"Stuxnet, développé par les USA et Israël, ciblait les centrifugeuses d'enrichissement d'uranium iraniennes, détruisant physiquement des équipements via un virus informatique."},
    {"id":"924_2","type":"vrai-faux","question":"Une attaque par ransomware chiffre les données de la victime et exige une rançon.",
     "correct_answer":"Vrai",
     "explanation":"Le ransomware (logiciel de rançon) est devenu une menace majeure. Les attaques contre des hôpitaux, collectivités (Ville de Caen, 2022) ou entreprises se multiplient."},
    {"id":"924_3","type":"texte","question":"Qu'est-ce qu'une cyberattaque d'État et quels exemples peut-on citer ?",
     "correct_answer":"Une cyberattaque d'État est une attaque informatique commanditée ou menée par un gouvernement. Exemples : Stuxnet (USA/Israël contre Iran, 2010), attaques russes NotPetya (Ukraine, 2017), attaque nord-coréenne Sony (2014), attaques chinoises APT contre la propriété intellectuelle occidentale, ingérence russe dans les élections (2016).",
     "explanation":"Ces attaques sont souvent difficiles à attribuer (deniability plausible) car opérées via des intermédiaires ou des groupes hackers parrainés."},
    {"id":"924_4","type":"qcm","question":"L'ANSSI est l'agence française chargée de :",
     "options":["La surveillance des réseaux sociaux","La cybersécurité et la défense des systèmes d'information","La censure d'Internet","La régulation des télécommunications"],
     "correct_answer":"La cybersécurité et la défense des systèmes d'information",
     "explanation":"L'ANSSI (Agence nationale de la sécurité des systèmes d'information) protège les infrastructures critiques françaises et conseille les administrations et entreprises sur la cybersécurité."},
    {"id":"924_5","type":"vrai-faux","question":"Le phishing (hameçonnage) est l'une des principales formes de cyberattaques touchant les particuliers.",
     "correct_answer":"Vrai",
     "explanation":"Le phishing consiste à usurper l'identité d'un organisme de confiance (banque, impôts) pour obtenir des informations sensibles. C'est le vecteur d'attaque le plus fréquent."},
    {"id":"924_6","type":"texte","question":"Quels sont les défis de l'attribution des cyberattaques ?",
     "correct_answer":"L'attribution est difficile car : les attaquants utilisent des proxies et VPN, effacent leurs traces, imitent les signatures d'autres groupes (false flag), opèrent via des hackers indépendants liés à des États. L'attribution technique (forensics) nécessite des moyens importants et des décisions politiques pour rendre l'attribution publique.",
     "explanation":"La difficulté d'attribution affaiblit la dissuasion et favorise l'impunité des États agresseurs."},
    {"id":"924_7","type":"qcm","question":"Le Manuel de Tallinn est :",
     "options":["Un accord international contraignant sur la cyberguerre","Une étude académique sur l'application du droit international aux cyberopérations, commanditée par l'OTAN","Une loi européenne sur la cybersécurité","Un protocole de cryptographie"],
     "correct_answer":"Une étude académique sur l'application du droit international aux cyberopérations, commanditée par l'OTAN",
     "explanation":"Le Manuel de Tallinn (2013, 2017) est une référence pour les experts mais n'a pas force contraignante. Il analyse comment le droit international (droit des conflits armés) s'applique aux cyberopérations."},
    {"id":"924_8","type":"vrai-faux","question":"La guerre en Ukraine (depuis 2022) a illustré l'importance des cyberopérations dans les conflits modernes.",
     "correct_answer":"Vrai",
     "explanation":"Avant et pendant l'invasion de 2022, la Russie a mené des cyberattaques massives contre l'Ukraine (NotPetya, attaques VIASAT). L'Ukraine a bénéficié du soutien de hackers privés et d'entreprises tech occidentales (Microsoft, ESET)."},
]),

(925, "La gouvernance d'Internet", "HGGSP", "1ère", [
    {"id":"925_1","type":"qcm","question":"Le modèle de gouvernance d'Internet dit 'multipartite' inclut :",
     "options":["Uniquement les États","Les États, les entreprises privées, la société civile et la communauté technique","L'ONU et ses agences spécialisées","Les pays membres du G7 uniquement"],
     "correct_answer":"Les États, les entreprises privées, la société civile et la communauté technique",
     "explanation":"Le modèle multipartite (multistakeholder) est défendu par les USA et l'Occident. Il s'oppose au modèle intergouvernemental défendu par la Russie et la Chine via l'UIT."},
    {"id":"925_2","type":"vrai-faux","question":"La Chine applique un modèle de gouvernance nationale d'Internet très restrictif, la 'Grande Muraille numérique'.",
     "correct_answer":"Vrai",
     "explanation":"La Grande Muraille numérique (Great Firewall) bloque Google, Facebook, YouTube, Wikipedia en Chine. Pékin contrôle le cyberespace national et promeut la 'souveraineté cybernétique' à l'international."},
    {"id":"925_3","type":"texte","question":"Qu'est-ce que la neutralité du Net et pourquoi est-elle débattue ?",
     "correct_answer":"La neutralité du Net est le principe selon lequel tous les flux de données doivent être traités de façon égale par les opérateurs (sans discrimination par source, destination ou type). Elle protège l'innovation et la liberté d'expression. Elle est remise en cause par les opérateurs qui veulent faire payer plus pour des contenus prioritaires, et par les États qui veulent contrôler certains contenus.",
     "explanation":"L'UE garantit la neutralité du Net par règlement depuis 2016. Aux USA, la neutralité a été abrogée en 2017 puis rétablie en 2024."},
    {"id":"925_4","type":"qcm","question":"RGPD signifie :",
     "options":["Règlement Général de Protection des Données","Réseau de Gouvernance des Plateformes Digitales","Règles Globales de Protection des Données","Régulation de la Gouvernance des Plateformes et Données"],
     "correct_answer":"Règlement Général de Protection des Données",
     "explanation":"Le RGPD (2018) est le règlement européen sur la protection des données personnelles. Il s'applique à toute organisation traitant des données de résidents européens, y compris les GAFAM."},
    {"id":"925_5","type":"vrai-faux","question":"Le Forum sur la gouvernance d'Internet (FGI / IGF) est un organe décisionnel de l'ONU.",
     "correct_answer":"Faux",
     "explanation":"Le FGI (Internet Governance Forum, créé en 2006) est une plateforme de dialogue multipartite sans pouvoir de décision. Il rassemble États, entreprises, société civile, mais ses conclusions ne sont pas contraignantes."},
    {"id":"925_6","type":"texte","question":"Expliquez les enjeux de la souveraineté numérique pour les États européens.",
     "correct_answer":"La souveraineté numérique désigne la capacité d'un État à maîtriser ses données, ses infrastructures et ses règles dans le cyberespace. L'Europe cherche à réduire sa dépendance aux GAFAM américains et aux équipements chinois (Huawei 5G), à développer son cloud souverain (GAIA-X), à réguler les plateformes (DSA, DMA) et à protéger les données des citoyens (RGPD).",
     "explanation":"La souveraineté numérique est un défi difficile à atteindre compte tenu de la domination technologique américaine et de l'intégration mondiale des chaînes numériques."},
    {"id":"925_7","type":"qcm","question":"Le DSA (Digital Services Act) européen vise à :",
     "options":["Taxer les GAFAM","Responsabiliser les plateformes numériques pour les contenus illicites et les pratiques monopolistiques","Créer un réseau social européen","Bloquer TikTok en Europe"],
     "correct_answer":"Responsabiliser les plateformes numériques pour les contenus illicites et les pratiques monopolistiques",
     "explanation":"Le DSA (2022) impose aux grandes plateformes des obligations de transparence, de lutte contre les contenus illicites et de respect des droits des utilisateurs, sous peine d'amendes importantes."},
    {"id":"925_8","type":"vrai-faux","question":"La question de la taxation des GAFAM est un enjeu de gouvernance économique internationale.",
     "correct_answer":"Vrai",
     "explanation":"Les GAFAM pratiquent l'optimisation fiscale via des paradis fiscaux (Irlande, Luxembourg). L'accord de l'OCDE sur la taxation minimale à 15 % (Pilier 2, 2021) vise à remédier à cette situation."},
]),

(926, "Numérique et nouvelles frontières", "HGGSP", "1ère", [
    {"id":"926_1","type":"qcm","question":"Le concept de 'splinternet' désigne :",
     "options":["Un accès internet ultra-rapide","La fragmentation d'Internet en réseaux nationaux contrôlés (Chine, Russie, Iran)","Un nouveau protocole internet","La neutralité du Net"],
     "correct_answer":"La fragmentation d'Internet en réseaux nationaux contrôlés (Chine, Russie, Iran)",
     "explanation":"Le splinternet est la tendance à fragmenter l'Internet mondial en blocs géopolitiques avec des régulations, contenus et infrastructures différents, à l'opposé du réseau ouvert et mondial d'origine."},
    {"id":"926_2","type":"vrai-faux","question":"La Russie a développé un système d'Internet souverain (RuNet) pouvant se déconnecter du réseau mondial.",
     "correct_answer":"Vrai",
     "explanation":"La loi russe sur le RuNet (2019) crée un système permettant l'isolation du réseau russe. Des tests ont été effectués, notamment depuis l'invasion de l'Ukraine en 2022."},
    {"id":"926_3","type":"texte","question":"En quoi le contrôle des câbles sous-marins est-il un enjeu géopolitique majeur ?",
     "correct_answer":"95 % du trafic internet mondial passe par des câbles sous-marins. Leurs points d'atterrissage sont des infrastructures critiques. Des États (USA via submarine cable agreements) et des entreprises privées (Facebook, Google) investissent dans ces câbles pour contrôler les flux. Leur sabotage (cas de la mer Baltique en 2023) peut couper des pays entiers.",
     "explanation":"Le câble MAREA (Microsoft/Facebook, 2017) est un exemple de l'investissement des GAFAM dans les infrastructures physiques d'Internet."},
    {"id":"926_4","type":"qcm","question":"L'intelligence artificielle générative (comme ChatGPT) soulève des enjeux géopolitiques car :",
     "options":["Elle consomme beaucoup d'électricité","Elle peut être utilisée pour la désinformation, la surveillance et la guerre cognitive","Elle est trop coûteuse pour les pays en développement","Elle remplace les programmeurs"],
     "correct_answer":"Elle peut être utilisée pour la désinformation, la surveillance et la guerre cognitive",
     "explanation":"L'IA générative facilite les deepfakes, la propagande automatisée, les cyberattaques sophistiquées. Sa maîtrise technologique est un enjeu de puissance nationale."},
    {"id":"926_5","type":"vrai-faux","question":"La 5G est un enjeu géopolitique majeur car elle conditionne la connectivité des infrastructures critiques.",
     "correct_answer":"Vrai",
     "explanation":"La controverse Huawei 5G illustre ce que : si un équipementier contrôle les réseaux 5G, il pourrait théoriquement accéder aux données ou perturber les communications. Les USA ont poussé leurs alliés à exclure Huawei."},
    {"id":"926_6","type":"texte","question":"Qu'est-ce que la guerre informationnelle (information warfare) et comment s'illustre-t-elle aujourd'hui ?",
     "correct_answer":"La guerre informationnelle utilise l'information comme arme pour influencer les perceptions, démoraliser l'adversaire, manipuler l'opinion publique. Exemples : ingérence russe dans les élections (2016), désinformation COVID-19, propagande de Daech sur les réseaux sociaux, trolls, deepfakes. Les réseaux sociaux sont devenus des champs de bataille informationnels.",
     "explanation":"La guerre informationnelle brouille la frontière entre guerre et paix, et rend difficile la défense car elle cible les démocraties ouvertes."},
    {"id":"926_7","type":"qcm","question":"Le principe de 'cyberpaix' prôné par certaines ONG signifie :",
     "options":["L'interdiction totale d'Internet en temps de guerre","L'établissement de normes internationales prohibant les cyberattaques contre les civils et les infrastructures critiques","La neutralité des entreprises tech en cas de conflit","La surveillance totale des réseaux par l'ONU"],
     "correct_answer":"L'établissement de normes internationales prohibant les cyberattaques contre les civils et les infrastructures critiques",
     "explanation":"Des acteurs comme Microsoft, l'ICJ ou des ONG défendent l'idée d'une Convention de Genève du numérique qui protégerait les civils des cyberattaques en période de conflit."},
    {"id":"926_8","type":"vrai-faux","question":"L'espace et le cyberespace sont considérés par l'OTAN comme des domaines opérationnels militaires.",
     "correct_answer":"Vrai",
     "explanation":"L'OTAN a reconnu le cyberespace comme domaine opérationnel en 2016 (Varsovie) et l'espace en 2019 (Londres), en plus des domaines traditionnels terre, mer et air."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 3 — FAIRE LA GUERRE, FAIRE LA PAIX (927–932)
# ══════════════════════════════════════════════════════════

(927, "Les formes de conflits armés au XXe siècle", "HGGSP", "1ère", [
    {"id":"927_1","type":"qcm","question":"La Première Guerre mondiale (1914-1918) est souvent qualifiée de 'guerre totale' car :",
     "options":["Elle n'a mobilisé que les armées professionnelles","Elle a mobilisé l'ensemble des sociétés (économie, civils, propagande, femmes)","Elle a utilisé des armes nucléaires","Elle s'est déroulée sur tous les continents"],
     "correct_answer":"Elle a mobilisé l'ensemble des sociétés (économie, civils, propagande, femmes)",
     "explanation":"La 'guerre totale' est un conflit dans lequel toutes les ressources d'une nation (humaines, économiques, industrielles, psychologiques) sont mobilisées pour la victoire."},
    {"id":"927_2","type":"vrai-faux","question":"La Seconde Guerre mondiale a fait environ 60 à 70 millions de victimes, civiles et militaires.",
     "correct_answer":"Vrai",
     "explanation":"La SGM est le conflit le plus meurtrier de l'histoire humaine (1939-1945), avec environ 60 à 70 millions de morts, dont une majorité de civils (génocide, bombardements)."},
    {"id":"927_3","type":"texte","question":"Qu'est-ce que la dissuasion nucléaire et comment a-t-elle influencé les conflits pendant la Guerre froide ?",
     "correct_answer":"La dissuasion nucléaire est la doctrine selon laquelle la menace de représailles nucléaires dévaste empêche toute attaque directe entre puissances nucléaires. Pendant la Guerre froide, elle a évité une guerre directe USA-URSS ('équilibre de la terreur'), mais favorisé des conflits indirects (guerres par procuration en Corée, Vietnam, Angola). La crise de Cuba (1962) illustre ses limites.",
     "explanation":"La dissuasion nucléaire repose sur la crédibilité de la menace : il faut que l'adversaire croie que la réponse nucléaire sera effectivement déclenchée."},
    {"id":"927_4","type":"qcm","question":"La guerre de Corée (1950-1953) est un exemple de guerre :",
     "options":["Civile","Par procuration (proxy war) de la Guerre froide","Nucléaire","De décolonisation uniquement"],
     "correct_answer":"Par procuration (proxy war) de la Guerre froide",
     "explanation":"Les USA soutenaient la Corée du Sud, l'URSS et la Chine la Corée du Nord. C'est un conflit localisé dans lequel les superpuissances s'affrontent indirectement."},
    {"id":"927_5","type":"vrai-faux","question":"Le génocide rwandais de 1994 a fait environ 800 000 à 1 million de victimes en moins de 100 jours.",
     "correct_answer":"Vrai",
     "explanation":"Entre avril et juillet 1994, les Hutus extremistes ont massacré environ 800 000 à 1 million de Tutsis et Hutus modérés au Rwanda, sous le regard passif de la communauté internationale."},
    {"id":"927_6","type":"texte","question":"Distinguez conflit interétatique et conflit intra-étatique en donnant des exemples.",
     "correct_answer":"Conflit interétatique : guerre entre États souverains (ex : guerre Iran-Irak 1980-1988, guerre des Malouines 1982). Conflit intra-étatique (guerre civile) : conflit à l'intérieur d'un État (ex : guerre civile syrienne 2011-, Rwanda 1994). Depuis la fin de la Guerre froide, les conflits intra-étatiques dominent.",
     "explanation":"Les conflits intra-étatiques sont souvent plus complexes car ils impliquent des acteurs non étatiques, des clivages ethniques ou religieux."},
    {"id":"927_7","type":"qcm","question":"Le concept de 'Responsabilité de protéger' (R2P) adopté par l'ONU en 2005 signifie que :",
     "options":["Les États ont le droit de s'armer","La communauté internationale peut intervenir pour protéger les civils quand un État faillit à cette mission","L'ONU peut imposer un gouvernement à un État","Les guerres préventives sont légitimes"],
     "correct_answer":"La communauté internationale peut intervenir pour protéger les civils quand un État faillit à cette mission",
     "explanation":"La R2P (Responsibility to Protect) est adoptée au Sommet mondial 2005 pour prévenir les génocides. Elle a été invoquée en Libye (2011) mais son application est très sélective."},
    {"id":"927_8","type":"vrai-faux","question":"Les accords de Dayton (1995) ont mis fin à la guerre en Bosnie-Herzégovine.",
     "correct_answer":"Vrai",
     "explanation":"Les accords de Dayton (Dayton Peace Agreement) signés en décembre 1995 ont mis fin à la guerre en Bosnie (1992-1995) après les massacres de Srebrenica (génocide reconnu)."},
]),

(928, "Guerres asymétriques, terrorisme et nouvelles formes de conflits", "HGGSP", "1ère", [
    {"id":"928_1","type":"qcm","question":"Une guerre asymétrique est un conflit opposant :",
     "options":["Deux armées de taille et de technologie égales","Des adversaires aux capacités très inégales, souvent État contre acteur non étatique","Uniquement des pays voisins","Des forces aériennes uniquement"],
     "correct_answer":"Des adversaires aux capacités très inégales, souvent État contre acteur non étatique",
     "explanation":"Dans une guerre asymétrique, l'acteur plus faible compense son infériorité militaire par des tactiques non conventionnelles (guérilla, terrorisme, piège, dissimulation civile)."},
    {"id":"928_2","type":"vrai-faux","question":"Le terrorisme est une nouvelle forme de conflit apparu uniquement après 2001.",
     "correct_answer":"Faux",
     "explanation":"Le terrorisme existe depuis longtemps (Anarchisme XIXe, IRA, ETA, Brigate Rosse). Les attentats du 11 septembre 2001 ont néanmoins marqué un tournant avec la globalisation du terrorisme jihadiste."},
    {"id":"928_3","type":"texte","question":"Quels ont été les impacts géopolitiques des attentats du 11 septembre 2001 ?",
     "correct_answer":"Déclenchement de la 'guerre contre le terrorisme', invasion de l'Afghanistan (2001) et de l'Irak (2003), création du Département de la sécurité intérieure américaine (DHS), loi Patriot Act (surveillance), émergence d'Al-Qaïda puis Daech, déstabilisation du Moyen-Orient, redéfinition des menaces sécuritaires mondiales, tensions avec le monde musulman.",
     "explanation":"Le 11-Septembre a profondément reconfiguré l'ordre mondial post-Guerre froide et lancé deux décennies de guerres asymétriques."},
    {"id":"928_4","type":"qcm","question":"Daech (État islamique) a proclamé un califat en :",
     "options":["2001","2003","2014","2019"],
     "correct_answer":"2014",
     "explanation":"En juin 2014, Daech (ISIS/ISIL) a proclamé un califat sur les territoires qu'il contrôlait en Irak et Syrie. À son apogée, il contrôlait une superficie comparable au Royaume-Uni."},
    {"id":"928_5","type":"vrai-faux","question":"Les drones armés sont devenus un élément central de la stratégie militaire américaine.",
     "correct_answer":"Vrai",
     "explanation":"Les drones Predator et Reaper permettent des frappes ciblées (targeted killings) au Pakistan, Yémen, Somalie sans engager de soldats au sol. Ils soulèvent des questions éthiques et juridiques sur les 'assassinats extrajudiciaires'."},
    {"id":"928_6","type":"texte","question":"Qu'est-ce qu'une guerre hybride ? Donnez l'exemple de la Russie en Ukraine (2014-2015).",
     "correct_answer":"La guerre hybride combine des moyens militaires conventionnels et non conventionnels : désinformation, cyberattaques, soldats sans insignes ('petits hommes verts'), soutien à des groupes séparatistes, guerre économique. En Crimée (2014), la Russie a utilisé des forces spéciales sans insignes, des opérations de désinformation et le soutien aux séparatistes pour annexer le territoire sans déclaration de guerre formelle.",
     "explanation":"La guerre hybride brouille la frontière guerre/paix et rend difficile la réponse des États cibles et des organisations internationales."},
    {"id":"928_7","type":"qcm","question":"La Cour pénale internationale (CPI) a pour mission de :",
     "options":["Régler les conflits entre États","Juger les individus accusés de crimes de guerre, crimes contre l'humanité et génocide","Imposer des sanctions économiques","Commander les forces de maintien de la paix"],
     "correct_answer":"Juger les individus accusés de crimes de guerre, crimes contre l'humanité et génocide",
     "explanation":"La CPI (créée par le Statut de Rome, 1998) juge les individus (pas les États) pour les crimes les plus graves. Elle ne peut agir que si les juridictions nationales échouent (principe de complémentarité)."},
    {"id":"928_8","type":"vrai-faux","question":"Les États-Unis, la Russie et la Chine n'ont pas ratifié le Statut de Rome créant la CPI.",
     "correct_answer":"Vrai",
     "explanation":"Les trois grandes puissances (USA, Russie, Chine) n'ont pas ratifié le Statut de Rome, limitant la portée de la CPI sur les acteurs des conflits les plus importants."},
]),

(929, "L'ONU et le maintien de la paix", "HGGSP", "1ère", [
    {"id":"929_1","type":"qcm","question":"L'ONU a été fondée en :",
     "options":["1919","1945","1948","1950"],
     "correct_answer":"1945",
     "explanation":"L'ONU (Organisation des Nations Unies) fut fondée le 24 octobre 1945, succédant à la Société des Nations (SDN). Elle compte aujourd'hui 193 États membres."},
    {"id":"929_2","type":"vrai-faux","question":"Le Conseil de sécurité de l'ONU compte 5 membres permanents avec droit de veto.",
     "correct_answer":"Vrai",
     "explanation":"Les P5 (membres permanents) sont : USA, Russie, Chine, France, Royaume-Uni. Chacun peut bloquer toute résolution du Conseil de sécurité par son veto."},
    {"id":"929_3","type":"texte","question":"Quelles sont les limites du droit de veto dans le fonctionnement du Conseil de sécurité ?",
     "correct_answer":"Le veto permet à chaque P5 de bloquer toute résolution, même en cas de génocide ou crime contre l'humanité. Exemples : veto russe et chinois sur la Syrie, veto américain sur la Palestine. Le veto paralyse le Conseil dans les conflits impliquant un P5 ou ses alliés, remettant en cause l'efficacité du système de sécurité collective.",
     "explanation":"Des réformes du Conseil de sécurité sont régulièrement proposées (élargissement, limitation du veto) mais bloquées par les P5 eux-mêmes."},
    {"id":"929_4","type":"qcm","question":"Les Casques bleus de l'ONU sont des forces de :",
     "options":["Guerre offensive","Maintien de la paix déployées avec l'accord des parties","Coercition contre les États en infraction","Police internationale permanente"],
     "correct_answer":"Maintien de la paix déployées avec l'accord des parties",
     "explanation":"Les opérations de maintien de la paix (OMP) reposent sur trois principes : consentement des parties, impartialité, non-recours à la force (sauf légitime défense). Il y a environ 87 000 Casques bleus actuellement déployés."},
    {"id":"929_5","type":"vrai-faux","question":"L'échec de l'ONU au Rwanda en 1994 a conduit à une réforme des opérations de maintien de la paix.",
     "correct_answer":"Vrai",
     "explanation":"L'impuissance des Casques bleus au Rwanda (mandat trop restrictif, inaction face au génocide) a entraîné le Rapport Brahimi (2000) et des réformes des OMP, notamment sur la protection des civils."},
    {"id":"929_6","type":"texte","question":"Quelle est la différence entre une opération de maintien de la paix et une opération d'imposition de la paix ?",
     "correct_answer":"Maintien de la paix (peacekeeping) : déployé avec l'accord des belligérants pour surveiller un cessez-le-feu, usage de la force minimal. Imposition de la paix (peace enforcement) : autorisée par le Chapitre VII de la Charte ONU, peut utiliser la force sans accord des parties pour restaurer la paix (Corée 1950, Koweït 1991, Libye 2011).",
     "explanation":"Le passage du maintien à l'imposition de la paix nécessite une résolution du Conseil de sécurité sous Chapitre VII."},
    {"id":"929_7","type":"qcm","question":"Quel organe onusien est compétent pour le respect des droits humains ?",
     "options":["Le Conseil de sécurité","Le Conseil des droits de l'homme (CDH)","L'Assemblée générale uniquement","Le FMI"],
     "correct_answer":"Le Conseil des droits de l'homme (CDH)",
     "explanation":"Le CDH (remplaçant la Commission des droits de l'homme en 2006) surveille le respect des droits humains dans le monde. Il a été critiqué pour l'élection de pays peu respectueux des droits."},
    {"id":"929_8","type":"vrai-faux","question":"L'ONU dispose de sa propre armée permanente pour intervenir dans les conflits.",
     "correct_answer":"Faux",
     "explanation":"L'ONU n'a pas d'armée propre. Les Casques bleus sont des soldats nationaux mis à disposition par les États membres. L'article 43 de la Charte (armée permanente de l'ONU) n'a jamais été appliqué."},
]),

(930, "La diplomatie et la résolution des conflits", "HGGSP", "1ère", [
    {"id":"930_1","type":"qcm","question":"La diplomatie est :",
     "options":["L'art de la guerre","La gestion des relations entre États par des négociations pacifiques","Le droit international humanitaire","La politique étrangère militaire"],
     "correct_answer":"La gestion des relations entre États par des négociations pacifiques",
     "explanation":"La diplomatie est l'ensemble des pratiques et institutions permettant aux États de gérer leurs relations (traités, négociations, représentations diplomatiques)."},
    {"id":"930_2","type":"vrai-faux","question":"Les Conventions de Vienne (1961 et 1963) encadrent le statut des diplomates et des consuls.",
     "correct_answer":"Vrai",
     "explanation":"Les Conventions de Vienne établissent l'immunité diplomatique, l'inviolabilité des ambassades, les privilèges consulaires. Ces règles sont le fondement du droit diplomatique moderne."},
    {"id":"930_3","type":"texte","question":"Quels sont les différents modes de règlement pacifique des différends internationaux ?",
     "correct_answer":"Négociation bilatérale (directe entre États), médiation (par un tiers), conciliation (commission d'enquête), arbitrage (tribunal arbitral), règlement judiciaire (CIJ — Cour internationale de Justice), bons offices, conférence internationale. La Charte ONU (Art.33) invite les États à recourir à ces moyens avant la force.",
     "explanation":"La CIJ (créée en 1945, La Haye) est le principal organe judiciaire de l'ONU pour les différends entre États."},
    {"id":"930_4","type":"qcm","question":"Les accords d'Oslo (1993) ont tenté de résoudre le conflit :",
     "options":["En Bosnie","Israélo-palestinien","En Irlande du Nord","En Afrique du Sud"],
     "correct_answer":"Israélo-palestinien",
     "explanation":"Les accords d'Oslo (1993) ont été signés entre Yasser Arafat (OLP) et Yitzhak Rabin (Israël), avec la médiation des USA. Ils ont créé l'Autorité palestinienne mais n'ont pas abouti à un État palestinien."},
    {"id":"930_5","type":"vrai-faux","question":"Les accords de paix d'Abraham (2020) normalisent les relations d'Israël avec plusieurs États arabes.",
     "correct_answer":"Vrai",
     "explanation":"Les Accords d'Abraham (2020), négociés par les USA sous Trump, normalisent les relations d'Israël avec les EAU, Bahreïn, le Soudan et le Maroc, contournant la question palestinienne."},
    {"id":"930_6","type":"texte","question":"Qu'est-ce que la diplomatie multilatérale et quelles en sont les enceintes principales ?",
     "correct_answer":"La diplomatie multilatérale implique plusieurs États négociant ensemble dans des cadres institutionnels. Enceintes : ONU (Assemblée générale, Conseil de sécurité), OMC, G7, G20, COP (changement climatique), OTAN, UE. Elle permet de coordonner des réponses à des problèmes globaux mais nécessite des compromis.",
     "explanation":"La diplomatie multilatérale a connu un essor depuis 1945 mais fait face à la montée du nationalisme et du bilatéralisme."},
    {"id":"930_7","type":"qcm","question":"Les Accords de paix de Good Friday (1998) ont mis fin au conflit en :",
     "options":["Bosnie","Palestine","Irlande du Nord","Chypre"],
     "correct_answer":"Irlande du Nord",
     "explanation":"L'Accord du Vendredi Saint (Good Friday Agreement, 1998) a mis fin aux 'Troubles' en Irlande du Nord en reconnaissant les deux communautés et en créant des institutions de partage du pouvoir."},
    {"id":"930_8","type":"vrai-faux","question":"La médiation internationale a permis de régler de nombreux conflits post-Guerre froide.",
     "correct_answer":"Vrai",
     "explanation":"Des médiations réussies : accords d'Arusha (Rwanda), accords de Lomé (Sierra Leone), processus de paix en Colombie (2016). La médiation internationale reste un outil essentiel mais insuffisant pour les conflits complexes."},
]),

(931, "Le droit international humanitaire", "HGGSP", "1ère", [
    {"id":"931_1","type":"qcm","question":"Les Conventions de Genève (1949) ont pour but de :",
     "options":["Interdire toutes les guerres","Protéger les personnes ne participant pas ou plus aux hostilités (prisonniers, blessés, civils)","Réguler le commerce des armes","Créer un tribunal international"],
     "correct_answer":"Protéger les personnes ne participant pas ou plus aux hostilités (prisonniers, blessés, civils)",
     "explanation":"Les quatre Conventions de Genève de 1949 constituent le noyau du DIH (Droit International Humanitaire). Elles protègent les blessés, naufragés, prisonniers de guerre et civils."},
    {"id":"931_2","type":"vrai-faux","question":"Le droit international humanitaire (DIH) s'applique uniquement aux guerres entre États.",
     "correct_answer":"Faux",
     "explanation":"L'article 3 commun aux quatre Conventions de Genève et le Protocole II s'appliquent aux conflits armés non internationaux (guerres civiles). Le DIH s'applique à toutes les formes de conflits armés."},
    {"id":"931_3","type":"texte","question":"Quels sont les principes fondamentaux du droit international humanitaire ?",
     "correct_answer":"Distinction (entre combattants et civils), Proportionnalité (les dommages causés aux civils ne doivent pas être excessifs par rapport à l'avantage militaire), Précaution (minimiser les dommages collatéraux), Nécessité militaire (seule la force nécessaire est autorisée), Humanité (interdiction des souffrances superflues).",
     "explanation":"Ces principes s'appliquent même en guerre ; ils sont contraignants pour toutes les parties au conflit, étatiques ou non."},
    {"id":"931_4","type":"qcm","question":"Le Comité international de la Croix-Rouge (CICR) est :",
     "options":["Une agence de l'ONU","Une organisation intergouvernementale","Une organisation humanitaire privée suisse, gardienne du DIH","Un tribunal international"],
     "correct_answer":"Une organisation humanitaire privée suisse, gardienne du DIH",
     "explanation":"Fondé en 1863 par Henry Dunant, le CICR est une organisation neutre, impartiale et indépendante qui protège les victimes de conflits armés et promeut le respect du DIH."},
    {"id":"931_5","type":"vrai-faux","question":"Le bombardement délibéré de civils est un crime de guerre selon le DIH.",
     "correct_answer":"Vrai",
     "explanation":"Les attaques délibérées contre des populations civiles, l'utilisation de civils comme boucliers humains, la torture constituent des crimes de guerre poursuivis par la CPI."},
    {"id":"931_6","type":"texte","question":"Comment le DIH encadre-t-il l'utilisation des armes autonomes (drones, robots armés) ?",
     "correct_answer":"Le DIH exige qu'un être humain soit en mesure d'exercer un contrôle sur les décisions létales (principe de responsabilité). Les armes entièrement autonomes (Lethal Autonomous Weapons Systems, LAWS) soulèvent des questions sur leur capacité à distinguer combattants et civils, à respecter la proportionnalité. Des négociations à l'ONU tentent d'établir un cadre réglementaire sans accord à ce jour.",
     "explanation":"La campagne 'Stop Killer Robots' regroupe des ONG et États demandant l'interdiction des LAWS."},
    {"id":"931_7","type":"qcm","question":"Le Statut de Rome créant la Cour pénale internationale définit les crimes de génocide comme :",
     "options":["Tout acte de violence de masse","Des actes commis avec l'intention de détruire, en tout ou en partie, un groupe national, ethnique, racial ou religieux","Des crimes commis uniquement en temps de guerre","Tout meurtre de masse, sans critère d'intention"],
     "correct_answer":"Des actes commis avec l'intention de détruire, en tout ou en partie, un groupe national, ethnique, racial ou religieux",
     "explanation":"L'intention spécifique (dolus specialis) de destruction d'un groupe est l'élément clé du crime de génocide, ce qui le distingue des autres crimes contre l'humanité."},
    {"id":"931_8","type":"vrai-faux","question":"La Convention d'Ottawa (1997) interdit les mines antipersonnel.",
     "correct_answer":"Vrai",
     "explanation":"Le Traité d'Ottawa (1997) interdit l'utilisation, le stockage, la production et le transfert des mines antipersonnel. Plus de 160 pays l'ont ratifié. Les USA, Russie et Chine ne l'ont pas signé."},
]),

(932, "Conflits contemporains et construction de la paix", "HGGSP", "1ère", [
    {"id":"932_1","type":"qcm","question":"Le conflit en Syrie (depuis 2011) illustre quel type de guerre ?",
     "options":["Un conflit interétatique classique","Une guerre civile devenue conflit international par proxy","Une guerre exclusivement terroriste","Un conflit colonial"],
     "correct_answer":"Une guerre civile devenue conflit international par proxy",
     "explanation":"La guerre civile syrienne a impliqué de nombreux acteurs extérieurs : Russie et Iran soutenant Assad, USA, Turquie, Arabie Saoudite soutenant des factions rebelles, Daech. C'est une guerre par procuration internalisée."},
    {"id":"932_2","type":"vrai-faux","question":"Les conflits armés font aujourd'hui plus de victimes civiles que militaires.",
     "correct_answer":"Vrai",
     "explanation":"Dans les conflits contemporains, les civils représentent la grande majorité des victimes (estimations : 90 % des victimes des guerres modernes). Les guerres asymétriques en milieu urbain sont particulièrement meurtrières pour les populations."},
    {"id":"932_3","type":"texte","question":"Qu'est-ce que la consolidation de la paix (peacebuilding) et quels en sont les instruments ?",
     "correct_answer":"La consolidation de la paix vise à reconstruire une société après un conflit pour prévenir les rechutes. Instruments : désarmement-démobilisation-réintégration (DDR) des combattants, justice transitionnelle (tribunaux mixtes, commissions vérité-réconciliation), reconstruction économique et institutionnelle, réconciliation nationale, élections démocratiques.",
     "explanation":"La Commission de consolidation de la paix (ONU, 2005) soutient les États sortant de conflits."},
    {"id":"932_4","type":"qcm","question":"La justice transitionnelle est :",
     "options":["Un tribunal international permanent","L'ensemble des mécanismes judiciaires et non judiciaires permettant d'aborder les violations passées dans une société post-conflit","Une amnistie générale après un conflit","Un tribunal militaire"],
     "correct_answer":"L'ensemble des mécanismes judiciaires et non judiciaires permettant d'aborder les violations passées dans une société post-conflit",
     "explanation":"La justice transitionnelle comprend : poursuites pénales, commissions vérité-réconciliation (Afrique du Sud), réparations, réformes institutionnelles. Elle vise réconciliation et prévention des rechutes."},
    {"id":"932_5","type":"vrai-faux","question":"La Commission Vérité et Réconciliation (TRC) d'Afrique du Sud est un exemple de justice transitionnelle.",
     "correct_answer":"Vrai",
     "explanation":"La TRC sud-africaine (1996-2002), présidée par Desmond Tutu, a offert l'amnistie aux auteurs de violations des droits humains en échange d'aveux publics complets."},
    {"id":"932_6","type":"texte","question":"Quelles sont les 'nouvelles guerres' selon la politologue Mary Kaldor ?",
     "correct_answer":"Mary Kaldor distingue les 'nouvelles guerres' (post-Guerre froide) des guerres classiques : elles impliquent des acteurs non étatiques (milices, groupes terroristes), sont financées par économies de guerre (pillage, trafics), visent délibérément les civils, exploitent les identités ethniques ou religieuses, et brouillent les frontières guerre/crime organisé. Exemples : Bosnie, Sierra Leone, RDC.",
     "explanation":"Les 'nouvelles guerres' remettent en cause les catégories classiques du droit de la guerre et posent de nouveaux défis humanitaires."},
    {"id":"932_7","type":"qcm","question":"L'opération Barkhane (2014-2022) est une intervention militaire française menée :",
     "options":["En Syrie","Dans le Sahel (Mali, Niger, Burkina Faso…) contre les groupes jihadistes","En Libye","En République centrafricaine uniquement"],
     "correct_answer":"Dans le Sahel (Mali, Niger, Burkina Faso…) contre les groupes jihadistes",
     "explanation":"L'opération Barkhane, successeur de Serval, déploie jusqu'à 5 000 soldats français dans la bande sahélo-saharienne (BSS) pour combattre les groupes armés terroristes (GAT). La France s'en est retirée entre 2022 et 2023 sous pression politique locale."},
    {"id":"932_8","type":"vrai-faux","question":"La guerre en Ukraine (2022) marque un retour des guerres interétatiques majeures en Europe.",
     "correct_answer":"Vrai",
     "explanation":"L'invasion de l'Ukraine par la Russie (24 février 2022) est la plus grande guerre interétatique en Europe depuis 1945. Elle ébranle l'ordre de sécurité européen post-Guerre froide et questionne la capacité de l'ONU à maintenir la paix."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 4 — HISTOIRE ET MÉMOIRES (933–938)
# ══════════════════════════════════════════════════════════

(933, "Mémoire collective et histoire", "HGGSP", "1ère", [
    {"id":"933_1","type":"qcm","question":"Maurice Halbwachs est le sociologue qui a théorisé le concept de :",
     "options":["L'histoire officielle","La mémoire collective","La mémoire individuelle uniquement","L'oubli social"],
     "correct_answer":"La mémoire collective",
     "explanation":"Maurice Halbwachs (1877-1945) a montré dans ses travaux que la mémoire individuelle est toujours construite dans un cadre social : les groupes maintiennent et transmettent des souvenirs partagés."},
    {"id":"933_2","type":"vrai-faux","question":"L'histoire et la mémoire sont deux approches identiques du passé.",
     "correct_answer":"Faux",
     "explanation":"L'histoire est une discipline critique qui analyse le passé avec des méthodes scientifiques (sources, distance critique). La mémoire est une représentation du passé chargée d'émotions et d'identité, sujette à reconstruction."},
    {"id":"933_3","type":"texte","question":"Expliquez la distinction entre histoire et mémoire selon Paul Ricœur.",
     "correct_answer":"Pour Paul Ricœur, la mémoire est une reconstruction du passé orientée par le présent et les identités (collective, sélective, émotionnelle). L'histoire distance le passé par la critique des sources et la méthode scientifique. Les deux entretiennent un rapport dialectique : la mémoire fournit des témoignages à l'histoire, mais l'histoire doit résister à la mémoire idéologique.",
     "explanation":"Ricœur parle d'une 'juste mémoire' : ni oubli, ni obsession, mais reconnaissance critique du passé."},
    {"id":"933_4","type":"qcm","question":"Un 'lieu de mémoire' (Pierre Nora) est :",
     "options":["Un musée uniquement","Tout lieu (monument, date, texte, symbole) où se cristallise la mémoire d'un groupe","Un site historique classé à l'UNESCO","Un cimetière militaire"],
     "correct_answer":"Tout lieu (monument, date, texte, symbole) où se cristallise la mémoire d'un groupe",
     "explanation":"Pierre Nora définit les 'lieux de mémoire' comme des points de référence identitaires. Exemples : Verdun, la Marseillaise, le Panthéon, le 14-Juillet en France."},
    {"id":"933_5","type":"vrai-faux","question":"Les commémorations officielles sont des outils de construction d'une identité nationale.",
     "correct_answer":"Vrai",
     "explanation":"L'État utilise les commémorations pour transmettre une mémoire nationale, renforcer le sentiment d'appartenance et légitimer le présent par un récit du passé."},
    {"id":"933_6","type":"texte","question":"Qu'est-ce que le 'devoir de mémoire' et quelles en sont les limites ?",
     "correct_answer":"Le devoir de mémoire est l'obligation morale de ne pas oublier les crimes du passé (génocides, esclavage) pour honorer les victimes et prévenir leur répétition. Limites : peut devenir instrument politique, concurrence des mémoires (compétition mémorielle), peut nuire au travail historiographique critique, risque de 'victimisation' identitaire.",
     "explanation":"Tzvetan Todorov distingue la 'mémoire exemplaire' (tirer des leçons universelles) de la 'mémoire littérale' (enfermement dans le trauma)."},
    {"id":"933_7","type":"qcm","question":"La loi Gayssot (1990) en France réprime :",
     "options":["La promotion de la violence","La négation des crimes contre l'humanité, notamment la Shoah","Le révisionnisme historique en général","Tout discours sur la Seconde Guerre mondiale"],
     "correct_answer":"La négation des crimes contre l'humanité, notamment la Shoah",
     "explanation":"La loi Gayssot (1990) interdit le négationnisme relatif aux crimes contre l'humanité reconnus par le Tribunal de Nuremberg. Elle illustre l'intervention de l'État dans la régulation de la mémoire."},
    {"id":"933_8","type":"vrai-faux","question":"Les 'lois mémorielles' françaises font débat entre historiens et juristes.",
     "correct_answer":"Vrai",
     "explanation":"Des historiens (L. Rousso, P. Nora) ont critiqué les lois mémorielles (Gayssot, loi Taubira sur l'esclavage) qui, selon eux, pénalisent un débat historique légitime. La liberté d'enseignement et de recherche est un enjeu."},
]),

(934, "La mémoire de la Seconde Guerre mondiale", "HGGSP", "1ère", [
    {"id":"934_1","type":"qcm","question":"Le terme 'Shoah' désigne :",
     "options":["L'ensemble des victimes de la Seconde Guerre mondiale","Le génocide des Juifs d'Europe par les nazis (1941-1945)","Le bombardement de Dresde","La politique des otages en France"],
     "correct_answer":"Le génocide des Juifs d'Europe par les nazis (1941-1945)",
     "explanation":"La Shoah (hébreu : 'catastrophe') désigne le génocide systématique de 6 millions de Juifs par le régime nazi et ses collaborateurs. Le terme s'est imposé notamment après le film de Claude Lanzmann (1985)."},
    {"id":"934_2","type":"vrai-faux","question":"Le procès de Nuremberg (1945-1946) est le premier tribunal pénal international de l'histoire.",
     "correct_answer":"Vrai",
     "explanation":"Le Tribunal militaire international de Nuremberg a jugé les dirigeants nazis pour crimes contre la paix, crimes de guerre et crimes contre l'humanité. Il a posé les bases du droit pénal international."},
    {"id":"934_3","type":"texte","question":"Comment la mémoire de la Résistance et de Vichy a-t-elle évolué en France depuis 1944 ?",
     "correct_answer":"1944-1970s : mythe gaulliste d'une France résistante (glorification de la Résistance, minimisation de la collaboration). 1970s-1990s : travaux d'historiens (R. Paxton, H. Rousso) et films (Le Chagrin et la Pitié, 1971) révèlent la réalité de Vichy et la collaboration. 1995 : Chirac reconnaît la responsabilité de l'État français dans la déportation des Juifs. 2000s : multiplication des commémorations et débats sur la collaboration.",
     "explanation":"La mémoire de Vichy illustre le processus de deuil mémoriel complexe d'une société confrontée à un passé douloureux."},
    {"id":"934_4","type":"qcm","question":"La Journée internationale de commémoration en mémoire des victimes de la Shoah est le :",
     "options":["1er septembre","8 mai","27 janvier","9 novembre"],
     "correct_answer":"27 janvier",
     "explanation":"Le 27 janvier commémore la libération du camp d'Auschwitz-Birkenau en 1945. L'ONU l'a officiellement désigné Journée internationale en 2005, pour les 60 ans de la libération."},
    {"id":"934_5","type":"vrai-faux","question":"Le camp d'Auschwitz-Birkenau est classé au patrimoine mondial de l'UNESCO.",
     "correct_answer":"Vrai",
     "explanation":"Auschwitz-Birkenau est inscrit au patrimoine mondial de l'UNESCO depuis 1979 comme 'bien en péril' (héritage de la barbarie), pour transmettre la mémoire aux générations futures."},
    {"id":"934_6","type":"texte","question":"Qu'est-ce que le négationnisme et pourquoi est-il combattu par les historiens et les États ?",
     "correct_answer":"Le négationnisme nie ou minimise les crimes nazis (génocide, chambre à gaz). Il est dangereux car il instrumentalise des 'arguments' pseudo-scientifiques pour servir des idéologies antisémites. Les États combattent le négationnisme par des lois (Gayssot en France, 1990) et les historiens par la rigueur scientifique. Il représente une menace pour la mémoire collective et la démocratie.",
     "explanation":"Figures négationnistes : Robert Faurisson, David Irving. Leurs thèses ont été réfutées par des historiens dans des procès retentissants."},
    {"id":"934_7","type":"qcm","question":"Le Mémorial de la Shoah à Paris est dédié à :",
     "options":["Les soldats français de la Seconde Guerre mondiale","Les victimes juives de la Shoah déportées de France","Les résistants français","Les prisonniers de guerre"],
     "correct_answer":"Les victimes juives de la Shoah déportées de France",
     "explanation":"Le Mémorial de la Shoah (Paris, fondé en 1956, rénovations successives) conserve le Mur des Noms des 76 000 déportés juifs de France, un centre d'archives et un musée."},
    {"id":"934_8","type":"vrai-faux","question":"La Conférence de Wannsee (janvier 1942) a planifié la 'Solution finale' — l'extermination systématique des Juifs.",
     "correct_answer":"Vrai",
     "explanation":"La Conférence de Wannsee (20 janvier 1942) réunit des hauts fonctionnaires nazis pour coordonner la mise en œuvre de la 'Solution finale' (Endlösung), confirmant le passage à l'extermination industrielle."},
]),

(935, "Mémoires des décolonisations et de l'esclavage", "HGGSP", "1ère", [
    {"id":"935_1","type":"qcm","question":"La loi Taubira (2001) en France reconnaît la traite négrière et l'esclavage comme :",
     "options":["Un crime économique","Un crime contre l'humanité","Une erreur historique","Un fait historique sans qualification juridique"],
     "correct_answer":"Un crime contre l'humanité",
     "explanation":"La loi Taubira (mai 2001) reconnaît la traite transatlantique et l'esclavage comme crimes contre l'humanité, faisant de la France le premier État à procéder à cette reconnaissance."},
    {"id":"935_2","type":"vrai-faux","question":"La mémoire de la guerre d'Algérie (1954-1962) fait encore l'objet de tensions entre la France et l'Algérie.",
     "correct_answer":"Vrai",
     "explanation":"La guerre d'Algérie reste un sujet douloureux : massacres (Sétif 1945, torture), pieds-noirs, harkis. La France a reconnu les faits de torture (Macron, 2018) mais sans s'excuser officiellement. Les mémoires algériennes et françaises divergent."},
    {"id":"935_3","type":"texte","question":"Qu'est-ce que le mouvement de décolonisation et comment s'est-il manifesté en Afrique subsaharienne ?",
     "correct_answer":"La décolonisation désigne le processus par lequel les colonies accèdent à l'indépendance. En Afrique subsaharienne, après 1945, des mouvements nationalistes (Kwame Nkrumah, Léopold Sédar Senghor) réclament l'indépendance. L'année 1960 ('année de l'Afrique') voit 17 pays africains accéder à l'indépendance, souvent dans le cadre d'une décolonisation négociée avec la France (Communauté française).",
     "explanation":"La décolonisation s'est parfois faite dans la violence (Kenya, Cameroun, Madagascar, Algérie) ou de façon pacifique (AOF/AEF)."},
    {"id":"935_4","type":"qcm","question":"Le mouvement de la Négritude, dont Aimé Césaire fut une figure majeure, revendique :",
     "options":["L'assimilation totale à la culture française","L'affirmation de l'identité et de la culture africaine et noire","L'indépendance immédiate des colonies","Le retour en Afrique des descendants d'esclaves"],
     "correct_answer":"L'affirmation de l'identité et de la culture africaine et noire",
     "explanation":"La Négritude (années 1930-1950) est un mouvement littéraire et politique qui affirme la dignité des cultures africaines contre l'assimilation coloniale. Aimé Césaire, Léopold Sédar Senghor, Léon-Gontran Damas en sont les fondateurs."},
    {"id":"935_5","type":"vrai-faux","question":"La commémoration du 10 mai est la Journée nationale des mémoires de la traite, de l'esclavage et de leurs abolitions.",
     "correct_answer":"Vrai",
     "explanation":"Le 10 mai (date de la loi Taubira au Sénat, 2001) est depuis 2006 la Journée nationale des mémoires de la traite, de l'esclavage et de leurs abolitions en France."},
    {"id":"935_6","type":"texte","question":"Pourquoi la question des réparations liées à l'esclavage est-elle débattue aujourd'hui ?",
     "correct_answer":"Des mouvements (CARICOM, certaines communautés afro-américaines) réclament des réparations aux États (USA, France, Royaume-Uni) pour l'enrichissement économique tiré de l'esclavage et les préjudices transgénérationnels. Les débats portent sur la forme (compensations financières, développement, excuses officielles), les bénéficiaires, la prescription et la responsabilité collective. C'est un enjeu mémoriel et politique majeur.",
     "explanation":"En 2021, la ville de New York et plusieurs villes américaines ont adopté des plans de réparations. La question reste très controversée politiquement."},
    {"id":"935_7","type":"qcm","question":"L'abolition de l'esclavage en France (colonies) a été définitivement décrétée en :",
     "options":["1794 (abolie puis rétablie en 1802)","1815","1848","1905"],
     "correct_answer":"1848",
     "explanation":"L'abolition définitive de l'esclavage dans les colonies françaises fut décrétée le 27 avril 1848 par Victor Schoelcher, sous la IIe République. Une première abolition en 1794 avait été annulée par Napoléon en 1802."},
    {"id":"935_8","type":"vrai-faux","question":"Le mouvement Black Lives Matter (2013, relancé 2020) a relancé le débat sur les symboles du colonialisme et de l'esclavage dans l'espace public.",
     "correct_answer":"Vrai",
     "explanation":"BLM a provoqué des débats sur les statues de négriers et colonialistes (déboulonnages à Bristol, Anvers, Louvain). La question de la place des symboles controversés dans l'espace public est un enjeu mémoriel contemporain."},
]),

(936, "Le rôle de l'État dans la construction des mémoires", "HGGSP", "1ère", [
    {"id":"936_1","type":"qcm","question":"Les 'lieux de mémoire' comme Verdun ou le Panthéon sont entretenus par l'État car ils permettent de :",
     "options":["Générer des revenus touristiques","Construire et transmettre une identité nationale","Éviter les conflits mémoriel","Satisfaire l'UNESCO"],
     "correct_answer":"Construire et transmettre une identité nationale",
     "explanation":"L'État investit dans les lieux de mémoire pour fédérer les citoyens autour d'un récit national commun, légitimer les institutions et transmettre des valeurs civiques."},
    {"id":"936_2","type":"vrai-faux","question":"Les manuels scolaires sont des vecteurs importants de la mémoire collective officielle.",
     "correct_answer":"Vrai",
     "explanation":"Les programmes scolaires et manuels d'histoire transmettent une vision du passé national. Ils font l'objet de choix politiques et de débats historiographiques sur ce qui doit être enseigné."},
    {"id":"936_3","type":"texte","question":"Comment les politiques mémorielles peuvent-elles être instrumentalisées politiquement ?",
     "correct_answer":"Les gouvernements peuvent sélectionner, amplifier ou minimiser certains événements pour servir des objectifs politiques : légitimer un régime (culte des ancêtres de la Révolution), souder la nation face à un ennemi commun, effacer des pages sombres (régimes autoritaires), ou conquérir un électorat (mémoires communautaires). Exemples : mythification de la Résistance sous de Gaulle, réécriture de l'histoire soviétique.",
     "explanation":"L'historien Henry Rousso parle de 'politique de la mémoire' pour désigner l'usage stratégique du passé par les acteurs politiques."},
    {"id":"936_4","type":"qcm","question":"Qui commémore-t-on le 8 mai en France ?",
     "options":["La fin de la guerre de 14-18","La libération de Paris","La victoire des Alliés et la fin de la Seconde Guerre mondiale en Europe","L'appel du 18 juin"],
     "correct_answer":"La victoire des Alliés et la fin de la Seconde Guerre mondiale en Europe",
     "explanation":"Le 8 mai 1945 est la date de la capitulation de l'Allemagne nazie. Le 11 novembre commémore l'armistice de 1918. Ces dates sont des 'lieux de mémoire' temporels en France."},
    {"id":"936_5","type":"vrai-faux","question":"La reconnaissance des crimes coloniaux par un État peut susciter des tensions diplomatiques.",
     "correct_answer":"Vrai",
     "explanation":"La reconnaissance par la France de la responsabilité dans le génocide rwandais (Macron, 2021) ou les tensions sur la colonisation algérienne illustrent comment les politiques mémorielles affectent les relations diplomatiques."},
    {"id":"936_6","type":"texte","question":"Qu'est-ce qu'un 'roman national' et quelles sont ses limites ?",
     "correct_answer":"Le 'roman national' est un récit historique simplificateur et héroïque présentant l'histoire d'une nation comme linéaire, glorieuse et unifiée. Il efface les conflits internes, les dominations et les pages sombres. Ses limites : il favorise l'exclusion des mémoires minoritaires, peut masquer les injustices historiques et crée des tensions quand d'autres mémoires émergent.",
     "explanation":"L'historienne Suzanne Citron a critiqué le roman national français pour son eurocentrisme et son oubli des populations colonisées."},
    {"id":"936_7","type":"qcm","question":"La 'concurrence des mémoires' désigne :",
     "options":["Une émission télévisée sur l'histoire","La rivalité entre différents groupes pour faire reconnaître leur mémoire dans l'espace public","Un débat académique entre historiens","Un conflit entre États sur leur passé commun"],
     "correct_answer":"La rivalité entre différents groupes pour faire reconnaître leur mémoire dans l'espace public",
     "explanation":"Plusieurs groupes (descendants d'esclaves, harkis, pieds-noirs, Arméniens, Juifs…) revendiquent la reconnaissance officielle de leurs mémoires. Ces revendications peuvent entrer en concurrence pour la visibilité et les commémorations."},
    {"id":"936_8","type":"vrai-faux","question":"Les archives nationales jouent un rôle crucial dans la transmission de la mémoire historique.",
     "correct_answer":"Vrai",
     "explanation":"Les archives (nationales, militaires, diplomatiques) conservent les traces du passé indispensables aux historiens. L'ouverture ou la fermeture des archives est un acte politique fort (ex : archives de Vichy, archives coloniales)."},
]),

(937, "Usages politiques du passé", "HGGSP", "1ère", [
    {"id":"937_1","type":"qcm","question":"Le révisionnisme historique désigne :",
    "options":["La révision normale des connaissances historiques par la recherche","La remise en cause idéologique de faits historiques établis (souvent pour justifier des idéologies)","La commémoration officielle des guerres","L'enseignement critique de l'histoire"],
    "correct_answer":"La remise en cause idéologique de faits historiques établis (souvent pour justifier des idéologies)",
     "explanation":"Le révisionnisme (sens péjoratif) consiste à nier ou minimiser des faits établis (génocides) pour servir des intérêts idéologiques. À distinguer de la révision scientifique légitime qui fait avancer la connaissance."},
    {"id":"937_2","type":"vrai-faux","question":"Certains régimes autoritaires utilisent l'histoire pour légitimer leur pouvoir.",
     "correct_answer":"Vrai",
     "explanation":"La Russie poutinienne utilise la victoire sur le nazisme (Grande Guerre patriotique) pour légitimer le régime. La Chine utilise le 'siècle d'humiliation' pour justifier sa politique étrangère affirmée."},
    {"id":"937_3","type":"texte","question":"En quoi le conflit entre Serbes et Bosniaques dans les années 1990 a-t-il mobilisé des mémoires historiques ?",
     "correct_answer":"La guerre en Bosnie-Herzégovine (1992-1995) a été alimentée par des mémoires historiques réactivées : le souvenir de la collaboration des Oustachis croates pendant la SGM, les mythes fondateurs serbes (la bataille du Kosovo, 1389), l'instrumentalisation des identités religieuses (orthodoxes vs catholiques vs musulmans). Les politiciens nationalistes ont construit une propagande mémorielle pour mobiliser les populations.",
     "explanation":"Ce conflit illustre la 'manipulation mémorielle' comme instrument de mobilisation nationaliste et de légitimation de la violence."},
    {"id":"937_4","type":"qcm","question":"La politique de 'tabula rasa' mémorielle (effacement du passé) est caractéristique de :",
     "options":["Les démocraties libérales","Certains régimes autoritaires révolutionnaires (nazisme, communisme)","L'UNESCO","Les mouvements féministes"],
     "correct_answer":"Certains régimes autoritaires révolutionnaires (nazisme, communisme)",
     "explanation":"Certains régimes cherchent à repartir de zéro en détruisant les symboles du passé (URSS et icônes religieuses, Daech et Palmyre) pour imposer un nouveau récit historique."},
    {"id":"937_5","type":"vrai-faux","question":"La 'mémoire blessée' peut être source de conflits politiques entre États.",
     "correct_answer":"Vrai",
     "explanation":"La question arménienne (génocide de 1915 reconnu par la France en 2001, nié par la Turquie) ou les tensions France-Algérie sur la colonisation illustrent comment les mémoires historiques alimentent les tensions diplomatiques."},
    {"id":"937_6","type":"texte","question":"Comment les monuments commémoratifs participent-ils à la construction d'une mémoire nationale ?",
     "correct_answer":"Les monuments commémoratifs (Arc de Triomphe, Verdun, statues, mémoriaux) matérialisent une mémoire dans l'espace public et la rendent pérenne. Ils indiquent quels événements et quelles valeurs une société juge dignes de mémoire. Leur construction, entretien et controverses (déboulonnages) reflètent les luttes pour l'hégémonie mémorielle.",
     "explanation":"La destruction de statues controversées (Edward Colston à Bristol en 2020) illustre la remise en cause des mémoires nationales dominantes par des mémoires minoritaires."},
    {"id":"937_7","type":"qcm","question":"La patrimonialisation d'un lieu historique peut avoir pour effet de :",
     "options":["Effacer la mémoire de ce lieu","Figer une interprétation du passé en lui donnant une légitimité officielle","Ouvrir le lieu à la recherche indépendante","Dépolitiser complètement le passé"],
     "correct_answer":"Figer une interprétation du passé en lui donnant une légitimité officielle",
     "explanation":"La patrimonialisation sélectionne et fige une interprétation du passé. Elle peut négliger certaines mémoires (ex : mémoire coloniale dans les musées ethnographiques) tout en valorisant d'autres."},
    {"id":"937_8","type":"vrai-faux","question":"Les commémorations du Centenaire de la Première Guerre mondiale (2014-2018) ont visé à promouvoir la réconciliation franco-allemande.",
     "correct_answer":"Vrai",
     "explanation":"Les commémorations du Centenaire 14-18 ont mis en avant la réconciliation (cérémonie franco-allemande à Verdun, 2016) plutôt que la victoire, reflétant l'évolution des valeurs européennes."},
]),

(938, "Mémoire et identité nationale", "HGGSP", "1ère", [
    {"id":"938_1","type":"qcm","question":"Ernest Renan définit la nation (1882) comme :",
     "options":["Un groupe ethnique homogène","Un vouloir vivre ensemble fondé sur une mémoire partagée et un consentement quotidien","Un territoire délimité par des frontières naturelles","Un État souverain"],
     "correct_answer":"Un vouloir vivre ensemble fondé sur une mémoire partagée et un consentement quotidien",
     "explanation":"Dans 'Qu'est-ce qu'une nation ?' (1882), Renan définit la nation par le plébiscite de chaque jour et la mémoire partagée (glorieuse et douloureuse), s'opposant aux conceptions racistes ou linguistiques de la nation."},
    {"id":"938_2","type":"vrai-faux","question":"Le roman national français a longtemps commencé par 'nos ancêtres les Gaulois'.",
     "correct_answer":"Vrai",
     "explanation":"La formule 'nos ancêtres les Gaulois' symbolise la construction d'un récit national homogène ignorant la diversité des origines. Elle fut enseignée jusqu'au milieu du XXe siècle, même dans les colonies."},
    {"id":"938_3","type":"texte","question":"Comment la commémoration du 11 novembre a-t-elle évolué en France ?",
     "correct_answer":"D'abord célébration de la victoire française en 1918, le 11 novembre est devenu progressivement une journée du souvenir pour tous les morts de guerre (1920 : soldat inconnu). Depuis 2012, il est officiellement la Journée d'hommage à tous les morts pour la France, incluant toutes les guerres. Cette évolution reflète le passage d'une mémoire victoriaire à une mémoire inclusive.",
     "explanation":"L'inscription du soldat inconnu sous l'Arc de Triomphe (1920) est emblématique de la construction mémorielle nationale."},
    {"id":"938_4","type":"qcm","question":"Le Panthéon est un lieu de mémoire national car il accueille :",
     "options":["Les tombes de tous les présidents de la République","Les restes de personnalités qui ont rendu de grands services à la France","Les archives nationales","Les reliques de la Révolution française"],
     "correct_answer":"Les restes de personnalités qui ont rendu de grands services à la France",
     "explanation":"Le Panthéon (Paris, construit sous Louis XV, panthéonisé en 1791) accueille 81 personnalités dont Voltaire, Rousseau, Marie Curie, Jean Moulin, Simone Veil. Chaque panthéonisation est un acte politique."},
    {"id":"938_5","type":"vrai-faux","question":"La mémoire nationale peut être source d'exclusion pour les minorités dont l'histoire est absente du récit officiel.",
     "correct_answer":"Vrai",
     "explanation":"Le récit national dominant peut marginaliser les mémoires des minorités (immigrés, descendants de colonisés, LGBTQ+). Des mouvements revendiquent l'inclusion de leurs histoires dans le récit national."},
    {"id":"938_6","type":"texte","question":"Pourquoi la question de l'histoire coloniale dans les manuels français fait-elle débat ?",
     "correct_answer":"Les manuels d'histoire ont longtemps présenté la colonisation comme une 'mission civilisatrice' positive. La loi de 2005 demandant de reconnaître 'les aspects positifs de la colonisation' fut abrogée après polémique. Aujourd'hui, les manuels intègrent mieux les violences coloniales mais restent critiqués pour insuffisance par les descendants de colonisés.",
     "explanation":"Ce débat reflète la tension entre construction d'une identité nationale positive et honnêteté historique sur les crimes commis au nom de la France."},
    {"id":"938_7","type":"qcm","question":"Les cérémonies du 8 mai et du 11 novembre sont des exemples de :",
     "options":["Fêtes religieuses","Rituels mémoriels d'État remplissant une fonction identitaire et sociale","Commémorations privées","Évènements culturels sans portée politique"],
     "correct_answer":"Rituels mémoriels d'État remplissant une fonction identitaire et sociale",
     "explanation":"Ces cérémonies annuelles avec discours présidentiels, dépôt de gerbes, hymne national et minute de silence sont des rituels qui réactivent la mémoire collective et soudent la communauté nationale."},
    {"id":"938_8","type":"vrai-faux","question":"La multiplication des mémoires particulières peut fragiliser la cohésion nationale.",
     "correct_answer":"Vrai",
     "explanation":"La fragmentation mémorielle (revendications concurrentes de groupes identitaires) peut complexifier la construction d'un récit national partagé. Certains parlent de 'guerre des mémoires'. Le défi est de concilier diversité mémorielle et cohésion sociale."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 5 — PATRIMOINE (939–944)
# ══════════════════════════════════════════════════════════

(939, "Définitions et catégories du patrimoine", "HGGSP", "1ère", [
    {"id":"939_1","type":"qcm","question":"Le patrimoine culturel immatériel désigne :",
     "options":["Les monuments historiques","Les œuvres d'art","Les pratiques, représentations, expressions, savoir-faire transmis de génération en génération","Les sites naturels"],
     "correct_answer":"Les pratiques, représentations, expressions, savoir-faire transmis de génération en génération",
     "explanation":"Le patrimoine culturel immatériel (UNESCO, 2003) inclut les traditions orales, les arts du spectacle, les rituels, les savoir-faire artisanaux. Exemples : la danse flamenco, la cuisine française, les fêtes de la Saint-Jean."},
    {"id":"939_2","type":"vrai-faux","question":"Le patrimoine naturel comprend les sites géologiques, les écosystèmes et les espèces menacées.",
     "correct_answer":"Vrai",
     "explanation":"Le patrimoine naturel englobe les éléments naturels d'importance universelle (géologie, biodiversité, paysages). Exemples : la Grande Barrière de corail, le parc national de Yellowstone."},
    {"id":"939_3","type":"texte","question":"Qu'est-ce que le patrimoine culturel matériel et quels en sont les enjeux de conservation ?",
     "correct_answer":"Le patrimoine culturel matériel comprend les monuments, les sites archéologiques, les œuvres d'art, les objets historiques. Les enjeux de conservation sont multiples : protéger contre les dégradations naturelles (érosion, pollution), les conflits armés (détructions intentionnelles), le trafic illicite d'antiquités, et les pressions du développement urbain. La restauration doit respecter l'authenticité et l'intégrité du patrimoine.",
     "explanation":"Le patrimoine matériel est vulnérable et nécessite des politiques de conservation rigoureuses, souvent coûteuses."},
    {"id":"939_4","type":"qcm","question":"Le patrimoine immatériel peut être menacé par :",
     "options":["La mondialisation et l'uniformisation culturelle","La surfréquentation touristique","Le changement climatique","Toutes les réponses ci-dessus"],
     "correct_answer":"Toutes les réponses ci-dessus",
     "explanation":"Le patrimoine immatériel est menacé par la mondialisation (perte de diversité culturelle), le tourisme de masse (commercialisation des traditions) et le changement climatique (disparition de savoir-faire liés à des environnements spécifiques)."},
    {"id":"939_5","type":"vrai-faux","question":"Le patrimoine culturel est un facteur de développement économique grâce au tourisme culturel.",
     "correct_answer":"Vrai",
     "explanation":"Le tourisme culturel génère des revenus importants pour les sites patrimoniaux et les économies locales. Cependant, il peut aussi causer des dommages (érosion, pollution) et nécessite une gestion durable."},
    {"id":"939_6","type":"texte","question":"Qu'est-ce que la patrimonialisation et quels sont ses effets sur les communautés locales ?",
     "correct_answer":"La patrimonialisation est le processus par lequel un élément culturel ou naturel est reconnu comme patrimoine et protégé. Elle peut valoriser les traditions locales, renforcer l'identité communautaire et générer des revenus. Cependant, elle peut aussi entraîner une gentrification, une perte d'authenticité, et des conflits d'usage entre les habitants et les touristes.",
     "explanation":"La patrimonialisation doit être participative pour éviter les effets négatifs sur les communautés locales."},
    {"id":"939_7","type":"qcm","question":"L'inscription d'un site sur la liste du patrimoine mondial de l'UNESCO implique :",
     "options":["Une protection juridique internationale","Un financement de l'UNESCO pour la conservation","Une reconnaissance de l'importance universelle du site","Une interdiction de toute activité humaine à proximité du site"],
     "correct_answer":"Une reconnaissance de l'importance universelle du site",
     "explanation":"L'inscription sur la liste du patrimoine mondial de l'UNESCO reconnaît l'importance universelle d'un site et encourage sa protection, mais n'implique pas nécessairement une protection juridique internationale ou un financement direct."},
    {"id":"939_8","type":"vrai-faux","question":"Le patrimoine culturel peut être un vecteur de dialogue interculturel et de paix entre les peuples.",
     "correct_answer":"Vrai",
     "explanation":"Le patrimoine culturel favorise la compréhension mutuelle, le respect des différences et la coopération internationale. Des initiatives comme les jumelages de villes ou les échanges culturels utilisent le patrimoine pour renforcer les liens entre les peuples."},
]),

(940, "Menaces et défis pour le patrimoine mondial", "HGGSP", "1ère", [
    {"id":"940_1","type":"qcm","question":"Le trafic illicite d'antiquités est une menace majeure pour le patrimoine mondial car il :",
     "options":["Permet de financer des groupes terroristes","Détruit les contextes archéologiques","Alimente le marché noir de l'art","Toutes les réponses ci-dessus"],
     "correct_answer":"Toutes les réponses ci-dessus",
     "explanation":"Le trafic d'antiquités détruit les sites archéologiques, alimente le marché noir de l'art et finance parfois des groupes terroristes (ex : Daech). La lutte contre ce trafic est un enjeu majeur pour la préservation du patrimoine mondial."},
    {"id":"940_2","type":"vrai-faux","question":"Le changement climatique n'affecte pas les sites du patrimoine mondial situés en zone tempérée.",
        "correct_answer":"Faux",
        "explanation":"Le changement climatique affecte tous les sites du patrimoine mondial, y compris ceux en zone tempérée, par des phénomènes tels que les inondations, les tempêtes, la montée du niveau de la mer et les variations de température qui peuvent endommager les structures et les matériaux."},
        {"id":"940_3","type":"texte","question":"Comment les conflits armés menacent-ils le patrimoine mondial ?",
        "correct_answer":"Les conflits armés peuvent entraîner la destruction intentionnelle (ex : Palmyre par Daech), les dommages collatéraux (bombardements), le pillage (ex : Irak, Syrie) et la négligence (manque de ressources pour la conservation). La protection du patrimoine en temps de guerre est un défi majeur pour la communauté internationale."},
        {"id":"940_4","type":"qcm","question":"La surfréquentation touristique peut causer :",
        "options":["L'érosion des sites","La pollution","La perturbation de la vie locale","Toutes les réponses ci-dessus"],
        "correct_answer":"Toutes les réponses ci-dessus",
        "explanation":"Le tourisme de masse peut causer l'érosion des sites, la pollution et perturber la vie locale. La gestion durable du tourisme est essentielle pour préserver le patrimoine mondial."},
        {"id":"940_5","type":"vrai-faux","question":"La restauration d'un site du patrimoine mondial doit toujours viser à le rendre comme neuf.",
        "correct_answer":"Faux",
        "explanation":"La restauration doit respecter l'authenticité et l'intégrité du site, en conservant les éléments d'origine et en évitant les reconstructions excessives qui peuvent altérer la valeur historique du patrimoine."},
        {"id":"940_6","type":"texte","question":"Quelles sont les stratégies de conservation pour protéger le patrimoine mondial ?",
        "correct_answer":"Les stratégies de conservation incluent la surveillance régulière, la gestion des risques (incendies, inondations), la sensibilisation du public, la réglementation du tourisme, la lutte contre le trafic illicite, et la coopération internationale."},
        {"id":"940_7","type":"qcm","question":"L'UNESCO a créé la Convention du patrimoine mondial en :",
        "options":["1972","1985","1990","2001"],
        "correct_answer":"1972",
        "explanation":"La Convention du patrimoine mondial de l'UNESCO a été adoptée en 1972 pour identifier, protéger et préserver le patrimoine culturel et naturel d'importance mondiale."},
        {"id":"940_8","type":"vrai-faux","question":"La guerre en Syrie a causé des dommages importants au patrimoine mondial du pays.",
        "correct_answer":"Vrai",
        "explanation":"La guerre en Syrie (depuis 2011) a causé des dommages importants au patrimoine mondial, notamment la destruction de sites archéologiques (Palmyre), le pillage de musées et la dégradation de monuments historiques."},
]),

]


def build_enriched_hggsp_entry(qid, title, angle, acteur, instrument, exemple):
    """Construit un quiz enrichi 8 questions sur un angle HGGSP donné."""
    if 942 <= qid <= 962:
        return (
            qid,
            title,
            "HGGSP",
            "1ère",
            build_quality_lot_questions(qid, angle, acteur, exemple, instrument),
        )

    return (
        qid,
        title,
        "HGGSP",
        "1ère",
        [
            {
                "id": f"{qid}_1",
                "type": "qcm",
                "question": f"Quel enjeu résume le mieux le thème '{angle}' ?",
                "options": [
                    "Un enjeu exclusivement technique",
                    "Un enjeu géopolitique mêlant pouvoir, acteurs et régulation",
                    "Un enjeu sans impact international",
                    "Un enjeu uniquement local",
                ],
                "correct_answer": "Un enjeu géopolitique mêlant pouvoir, acteurs et régulation",
                "explanation": "En HGGSP, les thèmes croisent rapports de force, acteurs multiples et instruments de gouvernance.",
            },
            {
                "id": f"{qid}_2",
                "type": "vrai-faux",
                "question": f"L'acteur '{acteur}' joue un rôle structurant dans ce thème.",
                "correct_answer": "Vrai",
                "explanation": f"{acteur} intervient comme acteur clé dans les dynamiques liées à '{angle}'.",
            },
            {
                "id": f"{qid}_3",
                "type": "texte",
                "question": "Expliquez en 3-4 phrases pourquoi ce thème est stratégique au XXIe siècle.",
                "correct_answer": f"Ce thème est stratégique car il influence la puissance des États, la coopération internationale et les tensions entre acteurs. Les décisions prises sur '{angle}' ont des effets économiques, politiques et sociétaux. Elles mobilisent des institutions, des normes et des acteurs publics comme privés.",
                "explanation": "Une bonne réponse articule acteurs, échelles, intérêts et conséquences.",
            },
            {
                "id": f"{qid}_4",
                "type": "qcm",
                "question": f"Quel instrument est le plus directement lié à ce thème ?",
                "options": [
                    instrument,
                    "Aucun cadre juridique",
                    "Une simple pratique informelle",
                    "Un outil uniquement national sans coopération",
                ],
                "correct_answer": instrument,
                "explanation": f"{instrument} sert de repère pour comprendre la régulation et les marges de manœuvre des acteurs.",
            },
            {
                "id": f"{qid}_5",
                "type": "vrai-faux",
                "question": "Ce thème peut créer des coopérations mais aussi des rivalités entre puissances.",
                "correct_answer": "Vrai",
                "explanation": "Les enjeux HGGSP articulent presque toujours interdépendance, compétition et recherche d'influence.",
            },
            {
                "id": f"{qid}_6",
                "type": "texte",
                "question": "Donnez un exemple concret et montrez ce qu'il révèle géopolitiquement.",
                "correct_answer": f"Exemple : {exemple}. Cet exemple révèle que les décisions autour de '{angle}' impliquent des intérêts divergents, des négociations et des effets de puissance. Il montre aussi le rôle des normes et de la gouvernance internationale.",
                "explanation": "L'exemple doit être contextualisé et interprété (pas seulement cité).",
            },
            {
                "id": f"{qid}_7",
                "type": "qcm",
                "question": "Dans une logique HGGSP, quelle approche est la plus pertinente ?",
                "options": [
                    "Étudier un seul acteur isolément",
                    "Croiser acteurs, échelles, temporalités et rapports de force",
                    "Éviter toute perspective historique",
                    "Réduire le thème à une opinion personnelle",
                ],
                "correct_answer": "Croiser acteurs, échelles, temporalités et rapports de force",
                "explanation": "La méthode HGGSP repose sur une analyse pluriscalaire et problématisée.",
            },
            {
                "id": f"{qid}_8",
                "type": "vrai-faux",
                "question": "Une analyse solide doit mobiliser à la fois faits, concepts et exemples précis.",
                "correct_answer": "Vrai",
                "explanation": "C'est l'attendu central en HGGSP pour argumenter de façon rigoureuse.",
            },
        ],
    )


def actor_action(actor):
    actor_lower = actor.strip().lower()
    if actor_lower.startswith("les "):
        return "interviennent", "leurs"
    return "intervient", "ses"


def build_quality_lot_questions(qid, angle, acteur, exemple, outils):
    verb, possessive = actor_action(acteur)

    open_variants = [
        {
            "question": f"Montrez en quoi '{angle}' constitue un enjeu géopolitique actuel.",
            "answer": f"'{angle}' est un enjeu géopolitique car il met en tension des intérêts d'acteurs publics et privés, à plusieurs échelles. Les arbitrages de gouvernance influencent l'image internationale des acteurs, la coopération et les rapports de puissance. L'exemple '{exemple}' illustre des choix politiques concrets et leurs effets.",
            "correction": "Attendu : problématique claire, acteurs identifiés, effets politiques explicites.",
        },
        {
            "question": f"Expliquez pourquoi '{angle}' oblige à articuler plusieurs échelles d'analyse.",
            "answer": f"Le thème '{angle}' implique des décisions prises localement, encadrées par des normes nationales et internationales. L'acteur '{acteur}' agit dans un système d'interdépendances où les effets politiques dépassent souvent le cadre local. Cette articulation d'échelles explique la dimension géopolitique du sujet.",
            "correction": "Attendu : articulation local/national/international + rôle des acteurs.",
        },
        {
            "question": "Expliquez l'intérêt géopolitique du thème en mobilisant deux arguments précis.",
            "answer": f"Ce thème est géopolitique car il influence les alliances, les normes et la capacité d'influence des acteurs. Il produit aussi des effets de coopération ou de rivalité selon les choix publics opérés autour de '{angle}'. L'exemple '{exemple}' en donne une illustration concrète.",
            "correction": "Attendu : deux arguments étayés par un exemple contextualisé.",
        },
    ]
    open_variant = open_variants[qid % len(open_variants)]

    method_variants = [
        {
            "question": "Quelle démarche méthodologique est la plus pertinente pour analyser ce thème ?",
            "options": [
                "Empiler des faits sans problématique",
                "Croiser acteurs, temporalités et échelles",
                "Écarter les jeux d'acteurs institutionnels",
                "Limiter l'analyse à un seul document",
            ],
            "correct": "Croiser acteurs, temporalités et échelles",
            "explanation": "C'est le cœur de la méthode HGGSP: problématiser et mettre en relation les dimensions d'analyse.",
        },
        {
            "question": "Pour traiter ce sujet en HGGSP, quelle stratégie est la plus solide ?",
            "options": [
                "Comparer les acteurs et leurs intérêts à plusieurs échelles",
                "Rester uniquement descriptif",
                "Supprimer la dimension historique",
                "Se limiter à un seul point de vue",
            ],
            "correct": "Comparer les acteurs et leurs intérêts à plusieurs échelles",
            "explanation": "La comparaison des acteurs et des intérêts permet une argumentation rigoureuse et nuancée.",
        },
        {
            "question": "Quelle méthode aide le mieux à construire une réponse problématisée ?",
            "options": [
                "Identifier enjeux, acteurs, instruments et effets",
                "Lister des exemples sans les relier",
                "Éviter toute hiérarchisation des arguments",
                "Confondre opinion et démonstration",
            ],
            "correct": "Identifier enjeux, acteurs, instruments et effets",
            "explanation": "Cette méthode structure la démonstration et rend les arguments vérifiables.",
        },
    ]
    method_variant = method_variants[(qid + 1) % len(method_variants)]

    return [
        {
            "id": f"{qid}_1",
            "type": "qcm",
            "question": f"Quel problème central pose '{angle}' ?",
            "options": [
                "Un enjeu uniquement technique",
                "Un arbitrage entre intérêts politiques, économiques et culturels",
                "Un sujet sans acteurs institutionnels",
                "Un sujet purement local sans effet global",
            ],
            "correct_answer": "Un arbitrage entre intérêts politiques, économiques et culturels",
            "explanation": "L'analyse HGGSP repose sur les rapports de force et les compromis entre acteurs.",
        },
        {
            "id": f"{qid}_2",
            "type": "vrai-faux",
            "question": f"L'acteur '{acteur}' peut peser sur la gouvernance de ce thème.",
            "correct_answer": "Vrai",
            "explanation": f"{acteur} {verb} par {possessive} décisions, financements ou capacités de coordination.",
        },
        {
            "id": f"{qid}_3",
            "type": "texte",
            "question": open_variant["question"],
            "correct_answer": open_variant["answer"],
            "explanation": open_variant["correction"],
        },
        {
            "id": f"{qid}_4",
            "type": "qcm",
            "question": "Quel exemple illustre le mieux ce thème ?",
            "options": [
                exemple,
                "Une décision strictement privée sans cadre public",
                "Un cas sans enjeu international",
                "Une mesure ponctuelle sans conséquence politique",
            ],
            "correct_answer": exemple,
            "explanation": "L'exemple choisi doit permettre de discuter acteurs, normes et stratégies.",
        },
        {
            "id": f"{qid}_5",
            "type": "vrai-faux",
            "question": "Les choix publics sur ce thème peuvent produire des gagnants et des perdants.",
            "correct_answer": "Vrai",
            "explanation": "Toute politique publique redistribue coûts, bénéfices et visibilité entre groupes.",
        },
        {
            "id": f"{qid}_6",
            "type": "texte",
            "question": "Proposez une mesure opérationnelle crédible pour améliorer la gouvernance de ce thème.",
            "correct_answer": f"Piste opérationnelle : {outils}. Cette proposition est crédible car elle combine pilotage public, mise en œuvre concrète et critères d'évaluation.",
            "explanation": "On attend une proposition précise, faisable et justifiée.",
        },
        {
            "id": f"{qid}_7",
            "type": "qcm",
            "question": method_variant["question"],
            "options": method_variant["options"],
            "correct_answer": method_variant["correct"],
            "explanation": method_variant["explanation"],
        },
        {
            "id": f"{qid}_8",
            "type": "vrai-faux",
            "question": "Une conclusion HGGSP doit ouvrir sur une limite ou une perspective.",
            "correct_answer": "Vrai",
            "explanation": "La mise en perspective finale renforce la qualité d'analyse attendue en 1ère.",
        },
    ]


ENRICHMENT_SPECS = [
    (941, "Patrimoine en guerre", "la protection du patrimoine en zone de conflit", "l'UNESCO", "la Convention de La Haye (1954)", "la destruction de Palmyre en Syrie"),
    (942, "Patrimoine et tourisme de masse", "la tension entre valorisation et préservation", "les collectivités locales", "les plans de gestion de site", "la régulation des flux à Venise"),
    (943, "UNESCO et diplomatie patrimoniale", "l'inscription patrimoniale comme levier d'influence", "les États", "la Convention du patrimoine mondial (1972)", "les candidatures concurrentes sur des sites transfrontaliers"),
    (944, "Restitutions d'œuvres et justice historique", "les restitutions d'objets culturels", "les musées nationaux", "les accords bilatéraux de restitution", "les restitutions d'œuvres au Bénin"),
    (945, "Patrimoine numérique", "l'archivage et la souveraineté des données culturelles", "les bibliothèques nationales", "les politiques d'open data patrimoniale", "les bibliothèques numériques nationales"),
    (946, "Médias et opinion publique", "la formation de l'opinion dans les démocraties", "les plateformes numériques", "les règles de modération des contenus", "la viralité d'une campagne d'information"),
    (947, "Influence culturelle des puissances", "le soft power culturel", "les instituts culturels", "les stratégies d'influence culturelle", "les saisons culturelles internationales"),
    (948, "Francophonie et géopolitique", "la langue comme instrument de puissance", "l'OIF", "les sommets de la Francophonie", "les programmes éducatifs francophones"),
    (949, "Soft power américain", "la diffusion de modèles culturels", "Hollywood", "les industries culturelles mondialisées", "l'influence des plateformes de streaming"),
    (950, "Soft power chinois", "la diplomatie culturelle émergente", "les instituts Confucius", "les stratégies de diplomatie publique", "les réseaux audiovisuels internationaux chinois"),
    (951, "Sport et puissance", "les méga-événements sportifs comme vitrines géopolitiques", "les États hôtes", "les cahiers des charges des fédérations", "les Jeux olympiques comme outil d'image"),
    (952, "Diplomatie climatique", "la gouvernance du climat", "les COP", "l'Accord de Paris (2015)", "les négociations sur les trajectoires carbone"),
    (953, "Acteurs non étatiques", "l'influence des ONG et entreprises transnationales", "les ONG internationales", "les coalitions transnationales", "les campagnes globales de plaidoyer"),
    (954, "ONG humanitaires", "l'action humanitaire dans les crises", "le CICR", "le droit international humanitaire", "l'accès humanitaire en zone de conflit"),
    (955, "Organisations régionales", "la coopération régionale face aux crises", "l'Union européenne", "les mécanismes de coordination régionale", "les réponses régionales aux crises migratoires"),
    (956, "Frontières maritimes", "les tensions autour des ZEE", "les États riverains", "la CNUDM (1982)", "les différends en Méditerranée orientale"),
    (957, "Arctique et nouvelles routes", "la reconfiguration géopolitique de l'Arctique", "les États arctiques", "le Conseil de l'Arctique", "l'ouverture de routes maritimes polaires"),
    (958, "Espace public mondial", "la circulation globale de l'information", "les agences de presse", "les standards internationaux de l'information", "la couverture médiatique d'un conflit international"),
    (959, "Désinformation électorale", "les ingérences informationnelles", "les autorités électorales", "les dispositifs de vérification des faits", "les campagnes de manipulation sur réseaux sociaux"),
    (960, "IA et gouvernance", "la régulation de l'intelligence artificielle", "les régulateurs publics", "les cadres de gouvernance de l'IA", "les débats sur l'AI Act européen"),
    (961, "Justice internationale contemporaine", "les limites et apports de la justice pénale internationale", "la CPI", "le Statut de Rome", "les mandats d'arrêt internationaux"),
    (962, "Révision HGGSP 1ère — Synthèse", "la méthode de problématisation en HGGSP", "les enseignants et jurys", "les attendus de l'épreuve", "un plan argumenté mobilisant plusieurs thèmes"),
]

quizzes_data.extend(build_enriched_hggsp_entry(*spec) for spec in ENRICHMENT_SPECS)


def write_quiz_files():
    os.makedirs(HGGSP1_QUIZ_DIR, exist_ok=True)
    os.makedirs(HGGSP1_ANSWERS_DIR, exist_ok=True)
    os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
    os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
    os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
    os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)

    for qid, title, subject, level, questions in quizzes_data:
        quiz_obj = make_quiz(qid, title, subject, level, questions)
        quiz_obj = normalize_text_payload(quiz_obj)
        with open(os.path.join(HGGSP1_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as file_handle:
            json.dump(quiz_obj, file_handle, ensure_ascii=False, indent=2)
            file_handle.write("\n")

        answers_obj = make_answers(qid, title, subject, level, questions)
        answers_obj = normalize_text_payload(answers_obj)
        with open(os.path.join(HGGSP1_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as file_handle:
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
    print("  BATCH I - HGGSP 1ere TERMINE")
    print(f"  {len(quizzes_data)} quizzes generes (IDs {first_id}-{last_id})")
    print(f"  {len(quizzes_data) * 6} fichiers JSON crees")
    print(f"  ({HGGSP1_QUIZ_DIR} + {HGGSP1_ANSWERS_DIR})")
    print(f"  ({OUTPUT_QUIZ_DIR} + {OUTPUT_ANSWERS_DIR})")
    print(f"  ({RUNTIME_QUIZ_DIR} + {RUNTIME_ANSWERS_DIR})")
    print(f"  {len(quizzes_data) * 8} questions au total")
    print("=" * 60)


if __name__ == "__main__":
    write_quiz_files()

