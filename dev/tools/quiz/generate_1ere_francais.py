#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Génération des quiz Français 1ère – IDs 466–500
35 quiz × 8 questions = 280 questions
Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
"""

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", ".."))
FR_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "francais_1ere_quizzes")
FR_QUIZ_DIR = os.path.join(FR_OUTPUT_DIR, "quiz")
FR_ANSWERS_DIR = os.path.join(FR_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "francais_1ere_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

os.makedirs(FR_QUIZ_DIR, exist_ok=True)
os.makedirs(FR_ANSWERS_DIR, exist_ok=True)
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

def make_quiz(qid, title, subject, level, questions):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    questions = [{k: v for k, v in q.items() if k not in answer_keys} for q in questions]
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    quiz_questions = []
    for question in questions:
        normalized_type = normalize_question_type(question.get("type", ""))
        if normalized_type == "qcm":
            sanitized = {
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            }
        elif normalized_type == "vrai-faux":
            sanitized = {
                "type": "vrai-faux",
                "question": str(question.get("question", "")),
            }
        else:
            sanitized = {
                "type": "open",
                "question": str(question.get("question", "")),
            }
        quiz_questions.append(sanitized)

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
            "question_count": len(quiz_questions),
            "passing_score": 70,
            "time_limit_minutes": 15,
            "questions": quiz_questions,
        },
        "exercisenotion": [],
        "exerciseresponses": [],
    }

def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        question_type = normalize_question_type(q.get("type", ""))
        if question_type == "qcm":
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
        elif question_type == "vrai-faux":
            tf_source = q.get("correct", q.get("correct_answer", "faux"))
            tf_answer = "vrai" if str(tf_source).strip().lower() in {"true", "vrai", "1"} else "faux"
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": tf_answer,
                "correction": str(q.get("explanation", "")),
            })
        else:
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "open",
                "answer": str(q.get("correct_answer", "")),
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


def normalize_question_type(question_type):
    if question_type == "vrai-faux":
        return "vrai-faux"
    if question_type == "qcm":
        return "qcm"
    if question_type == "open":
        return "open"
    return question_type

quizzes_data = [

# ─── 466 – Le roman : personnage et narration ─────────────────────────────────
(466, "Le roman : personnage et narration", "Français", "1ère", [
    {
        "id":"466_1",
        "type":"qcm",
        "question":"Qu'est-ce qu'un narrateur homodiégétique ?",
        "options":["Il raconte une histoire dont il est absent","Il raconte une histoire dont il est le héros","Il est omniscient et extérieur","Il s'adresse directement au lecteur"],
        "correct_option":"Il raconte une histoire dont il est le héros","explanation":"Homodiégétique = narrateur présent dans l'histoire comme personnage principal."
        },
    {
        "id":"466_2","type":"vrai-faux","question":"Le narrateur hétérodiégétique est absent de l'histoire qu'il raconte.",
        "correct":True,
        "explanation":"Hétéro = autre ; ce narrateur est extérieur à la diégèse."
        },
    {
        "id":"466_3",
        "type":"texte",
        "question":"Complétez : 'La focalisation zéro correspond à un narrateur _____, qui sait tout des personnages.'",
        "correct_answer":"omniscient",
        "explanation":"En focalisation zéro, le narrateur connaît pensées, sentiments et événements passés/futurs."
    },
    {
        "id":"466_4",
        "type":"qcm",
        "question":"Quelle focalisation limite le savoir narratif à ce que perçoit un seul personnage ?",
        "options":["Focalisation zéro","Focalisation interne","Focalisation externe","Focalisation multiple"],
        "correct_option":"Focalisation interne",
        "explanation":"En focalisation interne, le lecteur ne sait que ce que sait le personnage focal."
    },
    {
        "id":"466_5",
        "type":"vrai-faux",
        "question":"Un personnage rond (au sens de E.M. Forster) est stéréotypé et ne change pas.",
        "correct":False,
        "explanation":"Un personnage rond est complexe et évolue ; le personnage plat est stéréotypé."
    },
    {
        "id":"466_6",
        "type":"texte",
        "question":"Quel procédé narratif consiste à raconter un événement antérieur à l'action principale ?",
        "correct_answer":"analepse",
        "explanation":"L'analepse (ou retour en arrière, flashback) remonte dans le temps."
    },
    {
        "id":"466_7",
        "type":"qcm",
        "question":"Le discours indirect libre se caractérise par :",
        "options":["L'utilisation de guillemets","L'absence de verbe introducteur et le maintien des temps du passé","Une proposition subordonnée introduite par 'que'","La présence d'un tiret de dialogue"],
        "correct_option":"L'absence de verbe introducteur et le maintien des temps du passé",
        "explanation":"Le DIL fusionne la voix du narrateur et la pensée du personnage sans marqueurs typographiques."
    },
    {
        "id":"466_8",
        "type":"vrai-faux",
        "question":"Le temps dominant du récit réaliste est l'imparfait pour les descriptions et le passé simple pour les actions.",
        "correct":True,
        "explanation":"Cette alternance imparfait/passé simple est caractéristique du roman réaliste du XIXe siècle."
    },
    ]
),

# ─── 467 – Le roman réaliste et naturaliste ───────────────────────────────────
(
    467, "Le roman réaliste et naturaliste", "Français", "1ère", [
    {
        "id":"467_1",
        "type":"qcm",
        "question":"Quel auteur est le chef de file du naturalisme ?",
        "options":["Gustave Flaubert","Stendhal","Émile Zola","Honoré de Balzac"],
        "correct_option":"Émile Zola",
        "explanation":"Zola théorise le naturalisme dans Le Roman expérimental (1880) et le met en pratique dans Les Rougon-Macquart."
    },
    {
        "id":"467_2",
        "type":"vrai-faux",
        "question":"La Comédie humaine est l'oeuvre maîtresse de Stendhal.",
        "correct":False,
        "explanation":"La Comédie humaine appartient à Honoré de Balzac."
    },
    {
        "id":"467_3",
        "type":"texte",
        "question":"Complétez : Madame Bovary, de Flaubert, est souvent cité comme roman précurseur du _____.",
        "correct_answer":"réalisme","explanation":"Flaubert, bien que rejetant l'étiquette, est associé au réalisme par son souci du style et de l'exactitude."
        },
    {
        "id":"467_4",
        "type":"qcm",
        "question":"Le naturalisme se distingue du réalisme principalement par :",
        "options":["L'usage de la 1re personne","L'application des méthodes scientifiques à la littérature","Le refus de décrire le milieu social","L'idéalisation des personnages"],
        "correct_option":"L'application des méthodes scientifiques à la littérature",
        "explanation":"Zola s'inspire de Claude Bernard pour appliquer l'expérimentation à la création romanesque."
    },
    {
        "id":"467_5",
        "type":"vrai-faux",
        "question":"Germinal de Zola décrit la condition des mineurs du Nord de la France.",
        "correct":True,
        "explanation":"Germinal (1885) appartient au cycle des Rougon-Macquart et peint la vie des mineurs de charbon."
    },
    {
        "id":"467_6",
        "type":"texte",
        "question":"Quel procédé stylistique consiste à accumuler des détails concrets et précis pour donner l'illusion du réel ?",
        "correct_answer":"effet de réel",
        "explanation":"Roland Barthes théorise l' 'effet de réel' : les détails inutiles créent un sentiment d'authenticité."
    },
    {
        "id":"467_7",
        "type":"qcm",
        "question":"Stendhal est l'auteur de :",
        "options":["Bel-Ami","Le Rouge et le Noir","Nana","L'Assommoir"],
        "correct_option":"Le Rouge et le Noir",
        "explanation":"Le Rouge et le Noir (1830) est le grand roman de Stendhal, avec Julien Sorel pour héros."
    },
    {
        "id":"467_8",
        "type":"vrai-faux",
        "question":"Le roman réaliste idéalise la réalité pour lui donner un aspect plus poétique.",
        "correct":False,
        "explanation":"Le réalisme vise à représenter fidèlement la réalité sociale et humaine, sans idéalisation."
    },
]
),

# ─── 468 – Le roman d'apprentissage ──────────────────────────────────────────
(
    468, "Le roman d'apprentissage (Bildungsroman)", "Français", "1ère", [
    {
        "id":"468_1",
        "type":"qcm",
        "question":"Qu'est-ce qu'un roman d'apprentissage ?",
        "options":["Un roman policier","Un roman centré sur la formation et la maturation d'un personnage jeune","Un roman épistolaire","Un roman historique"],
        "correct_option":"Un roman centré sur la formation et la maturation d'un personnage jeune",
        "explanation":"Le Bildungsroman (all. : roman de formation) suit le héros de l'adolescence à l'âge adulte."
    },
    {
        "id":"468_2",
        "type":"vrai-faux",
        "question":"L'Éducation sentimentale de Flaubert est un roman d'apprentissage.",
        "correct":True,
        "explanation":"Frédéric Moreau y vit une initiation amoureuse et sociale qui constitue son éducation."
    },
    {
        "id":"468_3",
        "type":"texte",
        "question":"Dans le roman d'apprentissage, le héros part souvent d'une _____ (lieu natal) vers la ville ou le monde.",
        "correct_answer":"province",
        "explanation":"Le départ de la province vers Paris est un schéma récurrent (Rastignac, Julien Sorel, Frédéric Moreau…)."
    },
    {
        "id":"468_4",
        "type":"qcm",
        "question":"Quel personnage balzacien est le type même du jeune ambitieux venu de province à Paris ?",
        "options":["Jean Valjean","Rastignac","Vautrin","Père Goriot"],
        "correct_option":"Rastignac",
        "explanation":"Eugène de Rastignac, dans Le Père Goriot, incarne l'ambition provinciale dans la jungle parisienne."
    },
    {
        "id":"468_5",
        "type":"vrai-faux",
        "question":"Dans un roman d'apprentissage, le personnage principal n'évolue pas au fil du récit.",
        "correct":False,
        "explanation":"L'évolution du personnage est précisément le moteur du roman de formation."
    },
    {
        "id":"468_6",
        "type":"texte",
        "question":"Le mentor est une figure récurrente du roman d'apprentissage : il guide le héros dans sa _____.",
        "correct_answer":"initiation",
        "explanation":"Le mentor (précepteur, ami plus âgé, figure paternelle) initie le héros aux codes du monde."
    },
    {
        "id":"468_7",
        "type":"qcm",
        "question":"Quel roman de Voltaire peut être lu comme une parodie du roman d'apprentissage ?",
        "options":["Zadig","Candide","Micromégas","L'Ingénu"],
        "correct_option":"Candide",
        "explanation":"Candide (1759) suit un héros naïf qui 'apprend' que le monde n'est pas le meilleur des mondes possible."},
    {
        "id":"468_8",
        "type":"vrai-faux",
        "question":"Le roman épistolaire est incompatible avec le schéma du roman d'apprentissage.",
        "correct":False,
        "explanation":"Certains romans épistolaires (ex. Les Liaisons dangereuses) peuvent comporter une dimension initiatique."
    },
]),

# ─── 469 – La poésie lyrique ──────────────────────────────────────────────────
(469, "La poésie lyrique", "Français", "1ère", [
    {
        "id":"469_1",
        "type":"qcm",
        "question":"Quelle est la caractéristique principale de la poésie lyrique ?",
        "options":["La narration d'événements historiques","L'expression des sentiments personnels du poète","La satire sociale","La description objective du monde"],
        "correct_option":"L'expression des sentiments personnels du poète",
        "explanation":"Le lyrisme vient de la lyre : il exprime les émotions intimes du sujet poétique."
    },
    {
        "id":"469_2",
        "type":"vrai-faux",
        "question":"Le Romantisme est un mouvement littéraire favorable au lyrisme personnel.",
        "correct":True,
        "explanation":"Les Romantiques (Lamartine, Hugo, Musset, Vigny) font du 'moi' lyrique le centre de la création poétique."
    },
    {
        "id":"469_3",
        "type":"texte",
        "question":"Le spleen désigne chez Baudelaire un sentiment de _____ et d'ennui profond.",
        "correct_answer":"mélancolie",
        "explanation":"Le spleen baudelairien est une mélancolie existentielle, opposée à l'idéal."
    },
    {
        "id":"469_4",
        "type":"qcm",
        "question":"Quelle figure de style exprime le lyrisme en attribuant des sentiments à la nature ?",
        "options":["Métaphore","Prosopopée","Personnification de la nature / pathetic fallacy","Anaphore"],
        "correct_option":"Personnification de la nature / pathetic fallacy",
        "explanation":"Le poète lyrique projette ses émotions sur la nature (paysage état d'âme)."
    },
    {
        "id":"469_5",
        "type":"vrai-faux",
        "question":"L'alexandrin est un vers de 14 syllabes.",
        "correct":False,
        "explanation":"L'alexandrin compte 12 syllabes."
    },
    {
        "id":"469_6",
        "type":"texte",
        "question":"Le recueil de Lamartine intitulé _____ (1820) inaugure le lyrisme romantique en France.",
        "correct_answer":"Méditations poétiques",
        "explanation":"Les Méditations poétiques de Lamartine (1820) marquent le début de la poésie romantique française."
    },
    {
        "id":"469_7",
        "type":"qcm",
        "question":"Dans 'Le Lac' de Lamartine, le poète s'adresse au lac pour :",
        "options":["Décrire un paysage pittoresque","Lutter contre l'oubli du temps et d'un amour disparu","Célébrer une victoire","Critiquer la société"],
        "correct_option":"Lutter contre l'oubli du temps et d'un amour disparu",
        "explanation":"Le lac est invoqué comme témoin de l'amour passé ; le poème médite sur la fuite du temps."
    },
    {
        "id":"469_8",
        "type":"vrai-faux",
        "question":"Le vers libre est caractérisé par l'absence de métrique fixe et de rime obligatoire.",
        "correct":True,
        "explanation":"Le vers libre (Verlaine, Rimbaud, puis XXe siècle) s'affranchit des contraintes prosodiques classiques."
    },
]),

# ─── 470 – La poésie symboliste ───────────────────────────────────────────────
(470, "La poésie symboliste", "Français", "1ère", [
    {
        "id":"470_1",
        "type":"qcm",
        "question":"Quel poème de Baudelaire est considéré comme manifeste du Symbolisme ?",
        "options":["Spleen","Correspondances","L'Albatros","Harmonie du soir"],
        "correct_option":"Correspondances",
        "explanation":"Dans 'Correspondances', Baudelaire pose les bases du Symbolisme en affirmant les liens secrets entre les sens."
    },
    {
        "id":"470_2",
        "type":"vrai-faux",
        "question":"Le Symbolisme est un mouvement du XVIIe siècle.",
        "correct":False,
        "explanation":"Le Symbolisme est un mouvement de la fin du XIXe siècle (1880-1900), avec Mallarmé, Verlaine, Rimbaud."
    },
    {
        "id":"470_3",
        "type":"texte",
        "question":"Chez les symbolistes, le symbole est une image qui suggère une réalité _____ invisible.",
        "correct_answer":"spirituelle",
        "explanation":"Le symbole renvoie à un au-delà du réel visible : idées, états d'âme, vérités métaphysiques."
    },
    {
        "id":"470_4",
        "type":"qcm",
        "question":"Verlaine préconise dans 'Art poétique' de privilegier :",
        "options":["La rime riche","La musique avant toute chose","La clarté du discours","La régularité du mètre"],
        "correct_option":"La musique avant toute chose",
        "explanation":"'De la musique avant toute chose' : Verlaine place la musicalité au-dessus de la signification."
    },
    {
        "id":"470_5",
        "type":"vrai-faux",
        "question":"Rimbaud a écrit 'Une saison en enfer'.",
        "correct":True,
        "explanation":"Une saison en enfer (1873) est l'œuvre autobiographique majeure de Rimbaud."
    },
    {
        "id":"470_6",
        "type":"texte",
        "question":"Mallarmé recherche un langage poétique pur, dépouillé du réel, visant à suggérer plutôt qu'à _____.",
        "correct_answer":"nommer",
        "explanation":"'Nommer un objet, c'est supprimer les trois quarts de la jouissance du poème' (Mallarmé)."
    },
    {
        "id":"470_7",
        "type":"qcm",
        "question":"La synesthésie, procédé cher aux symbolistes, consiste à :",
        "options":["Répéter un son en fin de vers","Associer des sensations de registres différents","Inverser l'ordre habituel des mots","Omettre la ponctuation"],
        "correct_option":"Associer des sensations de registres différents",
        "explanation":"Ex. : 'des parfums frais comme des chairs d'enfants' (Baudelaire) mêle olfactif et tactile."
    },
    {
        "id":"470_8",
        "type":"vrai-faux",
        "question":"Le Parnasse et le Symbolisme partagent le même idéal poétique.",
        "correct":False,
        "explanation":"Le Parnasse valorise la forme parfaite et l'impassibilité ; le Symbolisme cherche la suggestion et la musique intérieure."
    },
]),

# ─── 471 – Le théâtre classique ───────────────────────────────────────────────
(471, "Le théâtre classique (XVIIe siècle)", "Français", "1ère", [
    {
        "id":"471_1",
        "type":"qcm",
        "question":"Quelles sont les trois unités du théâtre classique ?",
        "options":["Temps, lieu, action","Personnage, intrigue, dénouement","Exposition, nœud, dénouement","Actes, scènes, répliques"],
        "correct_option":"Temps, lieu, action",
        "explanation":"La règle des trois unités impose : une journée, un lieu, une action principale."
    },
    {
        "id":"471_2",
        "type":"vrai-faux",
        "question":"La bienséance interdit de montrer des scènes violentes sur la scène classique.",
        "correct":True,
        "explanation":"La bienséance impose que les actes choquants (meurtre, violence) soient narrés plutôt que montrés."
    },
    {
        "id":"471_3",
        "type":"texte",
        "question":"La _____ est la règle imposant que les événements représentés paraissent vraisemblables au spectateur.",
        "correct_answer":"vraisemblance",
        "explanation":"La vraisemblance exige que l'action soit crédible ; elle est complémentaire de la bienséance."
    },
    {
        "id":"471_4",
        "type":"qcm",
        "question":"Molière est l'auteur de :",
        "options":["Andromaque","Le Misanthrope","Phèdre","Le Cid"],
        "correct_option":"Le Misanthrope",
        "explanation":"Le Misanthrope (1666) est une comédie de Molière qui met en scène Alceste, ennemi de la flatterie."
    },
    {
        "id":"471_5",
        "type":"vrai-faux",
        "question":"Phèdre est une tragédie de Corneille.",
        "correct":False,
        "explanation":"Phèdre (1677) est une tragédie de Jean Racine."
    },
    {
        "id":"471_6",
        "type":"texte",
        "question":"Dans la tragédie classique, la _____ est le moment de basculement qui conduit le héros vers sa chute.",
        "correct_answer":"péripétie",
        "explanation":"La péripétie (renversement de situation) précipite la catastrophe finale."
    },
    {
        "id":"471_7",
        "type":"qcm",
        "question":"Quel est le but de la tragédie selon Aristote ?",
        "options":["Faire rire le spectateur","Provoquer la catharsis (purification des passions)","Critiquer la société","Enseigner l'histoire"],
        "correct_option":"Provoquer la catharsis (purification des passions)",
        "explanation":"La catharsis aristotélicienne : la tragédie purge les passions de crainte et de pitié."
    },
    {
        "id":"471_8",
        "type":"vrai-faux",
        "question":"L'alexandrin est le vers dominant de la tragédie classique française.",
        "correct":True,
        "explanation":"La tragédie classique est écrite en alexandrins (12 syllabes) rimés."
    },
]),

# ─── 472 – Le théâtre moderne et contemporain ─────────────────────────────────
(472, "Le théâtre moderne et contemporain", "Français", "1ère", [
    {
        "id":"472_1",
        "type":"qcm",
        "question":"Quel mouvement dramatique du XXe siècle remet en cause les conventions théâtrales traditionnelles ?",
        "options":["Le Classicisme","Le théâtre de l'absurde","Le Romantisme","Le Naturalisme"],
        "correct_option":"Le théâtre de l'absurde",
        "explanation":"Ionesco, Beckett et le théâtre de l'absurde déconstruisent le langage et la logique dramatique."
    },
    {
        "id":"472_2",
        "type":"vrai-faux",
        "question":"En attendant Godot de Beckett possède une intrigue linéaire et un dénouement clair.",
        "correct":False,
        "explanation":"En attendant Godot est construit sur la répétition, l'attente et l'absence de résolution."
    },
    {
        "id":"472_3",
        "type":"texte",
        "question":"Le drame bourgeois du XVIIIe siècle, théorisé par Diderot, introduit des personnages issus de la _____.",
        "correct_answer":"bourgeoisie",
        "explanation":"Diderot veut représenter des personnages de condition moyenne, plus proches du spectateur."
    },
    {
        "id":"472_4",
        "type":"qcm",
        "question":"Bertolt Brecht développe le concept de :",
        "options":["Catharsis","Distanciation (Verfremdungseffekt)","Bienséance","Vraisemblance"],
        "correct_option":"Distanciation (Verfremdungseffekt)",
        "explanation":"La distanciation brechtiènne empêche l'identification du spectateur pour favoriser son esprit critique."
    },
    {
        "id":"472_5",
        "type":"vrai-faux",
        "question":"Le monologue intérieur peut être utilisé au théâtre sous la forme d'aparté ou de soliloque.",
        "correct":True,
        "explanation":"Le soliloque (parole seul sur scène) et l'aparté (parole à voix basse non entendue des autres personnages) sont des formes théâtrales de l'intériorité."
    },
    {
        "id":"472_6",
        "type":"texte",
        "question":"La didascalie est une indication scénique fournie par l' _____ pour guider le metteur en scène et les acteurs.",
        "correct_answer":"auteur",
        "explanation":"Les didascalies (en italique) précisent décors, costumes, gestes, ton."
    },
    {
        "id":"472_7",
        "type":"qcm",
        "question":"Le théâtre épique, cher à Brecht, se caractérise par :",
        "options":["L'immersion totale du spectateur","La rupture de l'illusion théâtrale","L'absence de conflits","L'unité de temps et de lieu"],
        "correct_option":"La rupture de l'illusion théâtrale",
        "explanation":"Le théâtre épique interpelle le public, use de panneaux, de chants, pour briser l'illusion."
    },
    {
        "id":"472_8",
        "type":"vrai-faux",
        "question":"Eugène Ionesco est l'auteur de La Cantatrice chauve.",
        "correct":True,"explanation":"La Cantatrice chauve (1950) est la première pièce de Ionesco, fondatrice du théâtre de l'absurde en France."},
]),

# ─── 473 – L'argumentation : thèse et arguments ───────────────────────────────
(473, "L'argumentation : thèse et arguments", "Français", "1ère", [
    {
        "id":"473_1",
        "type":"qcm",
        "question":"Qu'est-ce qu'une thèse dans un texte argumentatif ?",
        "options":["Un exemple concret","La position défendue par l'auteur","Un contre-argument","Une question rhétorique"],
        "correct_option":"La position défendue par l'auteur","explanation":"La thèse est l'idée principale que l'auteur cherche à faire admettre au lecteur."},
    {
        "id":"473_2",
        "type":"vrai-faux",
        "question":"Un argument est une affirmation qui étaie ou réfute une thèse.",
        "correct":True,"explanation":"L'argument apporte une raison, un fait ou un raisonnement qui soutient ou contredit la thèse."},
    {
        "id":"473_3",
        "type":"texte",
        "question":"L'illustration d'un argument par un fait précis ou une anecdote s'appelle un _____.",
        "correct_answer":"exemple","explanation":"L'exemple concrétise l'argument et lui donne une valeur démonstrative."},
    {
        "id":"473_4",
        "type":"qcm",
        "question":"Quel est le rôle de la concession dans l'argumentation ?",
        "options":["Refuser tout contre-argument","Admettre partiellement l'opinion adverse avant de la réfuter","Renforcer l'émotion du lecteur","Introduire la conclusion"],
        "correct_option":"Admettre partiellement l'opinion adverse avant de la réfuter","explanation":"La concession (certes…, il est vrai que… mais) montre que l'auteur connaît les objections et les dépasse."},
    {
        "id":"473_5",
        "type":"vrai-faux",
        "question":"Un syllogisme est un raisonnement déductif en trois étapes : majeure, mineure, conclusion.",
        "correct":True,"explanation":"Ex. : Tous les hommes sont mortels (majeure) ; Socrate est un homme (mineure) ; donc Socrate est mortel."},
    {
        "id":"473_6",
        "type":"texte",
        "question":"L'appel aux émotions du lecteur pour le convaincre est appelé _____ dans la rhétorique classique.",
        "correct_answer":"pathos","explanation":"Le pathos (ethos, pathos, logos) vise à émouvoir le destinataire pour le persuader."},
    {
        "id":"473_7",
        "type":"qcm",
        "question":"La réfutation directe consiste à :",
        "options":["Ignorer l'argument adverse","Démontrer qu'un argument adverse est faux ou insuffisant","Reformuler sa propre thèse","Utiliser une métaphore"],
        "correct_option":"Démontrer qu'un argument adverse est faux ou insuffisant","explanation":"La réfutation directe attaque frontalement l'argument de l'adversaire."},
    {
        "id":"473_8",
        "type":"vrai-faux",
        "question":"Dans un plan dialectique, la synthèse dépasse le simple résumé des deux parties précédentes.",
        "correct":True,"explanation":"La synthèse propose un dépassement (Aufhebung) qui intègre et transcende thèse et antithèse."},
]),

# ─── 474 – Les formes de l'argumentation : essai, pamphlet, apologue ──────────
(474, "Formes de l'argumentation : essai, pamphlet, apologue", "Français", "1ère", [
    {
        "id":"474_1",
        "type":"qcm",
        "question":"Quel genre consiste en une réflexion personnelle non exhaustive sur un sujet ?",
        "options":["Le pamphlet","L'apologue","L'essai","La fable"],
        "correct_option":"L'essai","explanation":"L'essai (Montaigne, XVI e s.) est une réflexion libre et subjective sur des questions diverses."},
    {
        "id":"474_2",
        "type":"vrai-faux",
        "question":"Un pamphlet est un texte polémique qui attaque violemment une personne ou une idée.",
        "correct":True,"explanation":"Le pamphlet (ex. J'accuse de Zola) est virulent et engagé, souvent à caractère politique."},
    {
        "id":"474_3",
        "type":"texte",
        "question":"L'apologue est un récit court à visée _____, illustrant une morale.",
        "correct_answer":"didactique","explanation":"L'apologue (fable, conte philosophique, parabole) enseigne par le plaisir d'un récit fictif."},
    {
        "id":"474_4",
        "type":"qcm",
        "question":"Quel est l'auteur des Fables, forme d'apologue majeure de la littérature française ?",
        "options":["Montaigne","La Fontaine","Voltaire","Molière"],
        "correct_option":"La Fontaine","explanation":"Jean de La Fontaine (1621-1695) est l'auteur des Fables en vers, adaptées d'Ésope et Phèdre."},
    {
        "id":"474_5",
        "type":"vrai-faux",
        "question":"Candide de Voltaire est un conte philosophique qui peut être classé comme apologue.",
        "correct":True,"explanation":"Candide illustre la critique de l'optimisme leibnizien à travers un récit fictif à portée didactique."},
    {
        "id":"474_6",
        "type":"texte",
        "question":"Dans une fable, la _____ résume la leçon morale, souvent placée au début ou à la fin du texte.",
        "correct_answer":"morale","explanation":"La morale est l'énoncé de la leçon que l'auteur tire du récit allégorique."},
    {
        "id":"474_7",
        "type":"qcm",
        "question":"Montaigne est l'inventeur du genre de l'essai avec son œuvre :",
        "options":["Le Discours de la méthode","Les Essais","Candide","Les Pensées"],
        "correct_option":"Les Essais","explanation":"Les Essais (1580-1588) de Montaigne inaugurent le genre de la réflexion personnelle libre."},
    {
        "id":"474_8",
        "type":"vrai-faux",
        "question":"La parabole est une forme d'apologue à caractère religieux ou moral.",
        "correct":True,"explanation":"Les paraboles évangéliques (ex. le Fils prodigue) sont des récits symboliques à portée morale."},
]),

# ─── 475 – Les Lumières et la littérature engagée ─────────────────────────────
(475, "Les Lumières et la littérature engagée (XVIIIe s.)", "Français", "1ère", [
    {
        "id":"475_1",
        "type":"qcm",
        "question":"Quel est le grand projet éditorial des philosophes des Lumières ?",
        "options":["La Comédie humaine","L'Encyclopédie","Les Rougon-Macquart","La Nouvelle Héloïse"],
        "correct_option":"L'Encyclopédie","explanation":"L'Encyclopédie (1751-1772), dirigée par Diderot et d'Alembert, diffuse les savoirs et les idées des Lumières."},
    {"id":"475_2","type":"vrai-faux","question":"Voltaire défend la tolérance religieuse notamment dans son Traité sur la tolérance.",
     "correct":True,"explanation":"Le Traité sur la tolérance (1763) est écrit par Voltaire à la suite de l'affaire Calas."},
    {"id":"475_3","type":"texte","question":"Rousseau développe la notion de _____ social dans son œuvre Du contrat social.",
     "correct_answer":"contrat","explanation":"Du contrat social (1762) théorise le pacte entre les individus comme fondement de la société."},
    {"id":"475_4","type":"qcm","question":"Quelle est la devise des Lumières selon Kant ?",
     "options":["Liberté, Égalité, Fraternité","Ose savoir (Sapere aude)","Je pense donc je suis","L'homme est un loup pour l'homme"],
     "correct_option":"Ose savoir (Sapere aude)","explanation":"Kant définit les Lumières comme la sortie de l'homme de sa minorité : 'Ose te servir de ton propre entendement.'"},
    {"id":"475_5","type":"vrai-faux","question":"Le conte philosophique est un genre utilisé par les philosophes des Lumières pour transmettre leurs idées.",
     "correct":True,"explanation":"Voltaire, Diderot et Montesquieu utilisent la fiction (conte, roman, lettres) pour propager les idées des Lumières."},
    {"id":"475_6","type":"texte","question":"Dans les Lettres persanes, Montesquieu utilise le point de vue d'étrangers pour _____ la société française.",
     "correct_answer":"critiquer","explanation":"La vision extérieure du Persan Rica rend visible l'absurdité de certaines institutions françaises."},
    {"id":"475_7","type":"qcm","question":"Quel philosophe des Lumières est le plus associé à la défense de la liberté d'expression ?",
     "options":["Rousseau","Voltaire","Montesquieu","Diderot"],
     "correct_option":"Voltaire","explanation":"Voltaire combat toute sa vie l'intolérance et la censure : 'Je ne suis pas d'accord avec ce que vous dites, mais je me battrai pour que vous puissiez le dire.'"},
    {"id":"475_8","type":"vrai-faux","question":"La littérature engagée implique nécessairement un parti politique précis.",
     "correct":False,"explanation":"La littérature engagée défend des valeurs (liberté, justice) sans se limiter à un parti ; Sartre la théorise dans Qu'est-ce que la littérature ?"},
]),

# ─── 476 – La dissertation littéraire : méthode ───────────────────────────────
(476, "La dissertation littéraire : méthode", "Français", "1ère", [
    {"id":"476_1","type":"qcm","question":"Quelle est la première étape de la méthode de dissertation ?",
     "options":["Rédiger la conclusion","Analyser le sujet et dégager la problématique","Rédiger l'introduction","Chercher des exemples"],
     "correct_option":"Analyser le sujet et dégager la problématique","explanation":"Avant tout plan ou rédaction, il faut comprendre précisément ce que le sujet demande."},
    {"id":"476_2","type":"vrai-faux","question":"La problématique d'une dissertation est une question à laquelle le devoir va répondre.",
     "correct":True,"explanation":"La problématique formule l'enjeu intellectuel du sujet sous forme interrogative."},
    {"id":"476_3","type":"texte","question":"Le plan _____ est le plus adapté lorsque le sujet formule un jugement à discuter (ex. 'Le roman est-il un simple divertissement ?').",
     "correct_answer":"dialectique","explanation":"Le plan dialectique (thèse / antithèse / synthèse) convient aux sujets d'opinion à nuancer."},
    {"id":"476_4","type":"qcm","question":"Une transition en dissertation sert à :",
     "options":["Introduire un exemple","Bilan de la partie et annonce de la suivante","Formuler la thèse","Citer un auteur"],
     "correct_option":"Bilan de la partie et annonce de la suivante","explanation":"La transition (ou lien) assure la progression logique du devoir."},
    {"id":"476_5","type":"vrai-faux","question":"L'introduction d'une dissertation se termine par l'annonce du plan.",
     "correct":True,"explanation":"L'introduction comprend : accroche, présentation du sujet, problématique, annonce du plan."},
    {"id":"476_6","type":"texte","question":"En dissertation, un argument doit toujours être illustré par un _____ tiré des œuvres au programme.",
     "correct_answer":"exemple","explanation":"L'exemple littéraire précis donne du poids à l'argument et montre la maîtrise des textes."},
    {"id":"476_7","type":"qcm","question":"Que doit contenir la conclusion d'une dissertation ?",
     "options":["Un nouveau développement","Un bilan des arguments et une ouverture","La liste des auteurs cités","La reformulation de l'introduction"],
     "correct_option":"Un bilan des arguments et une ouverture","explanation":"La conclusion synthétise la réponse à la problématique, puis ouvre sur une perspective plus large."},
    {"id":"476_8","type":"vrai-faux","question":"Il est acceptable de formuler sa propre opinion au 'je' dans une dissertation au lycée.",
     "correct":False,"explanation":"On évite le 'je' en dissertation ; on utilise 'nous' ou des tournures impersonnelles pour maintenir la distance critique."},
]),

# ─── 477 – Le commentaire littéraire : méthode ────────────────────────────────
(477, "Le commentaire littéraire : méthode", "Français", "1ère", [
    {"id":"477_1","type":"qcm","question":"Quel est l'objectif principal du commentaire littéraire ?",
     "options":["Raconter le résumé du texte","Analyser comment le texte produit du sens et de l'effet","Émettre un jugement personnel","Comparer plusieurs auteurs"],
     "correct_option":"Analyser comment le texte produit du sens et de l'effet","explanation":"Le commentaire analyse les procédés stylistiques et leur effet sur le lecteur."},
    {"id":"477_2","type":"vrai-faux","question":"La paraphrase (reformulation du texte) est valorisée dans un commentaire littéraire.",
     "correct":False,"explanation":"La paraphrase est à éviter ; il faut analyser les procédés, pas simplement reformuler."},
    {"id":"477_3","type":"texte","question":"Dans un commentaire, l'axe de lecture est parfois appelé _____ : il oriente l'analyse d'une partie.",
     "correct_answer":"centre d'intérêt","explanation":"Chaque partie développe un centre d'intérêt (ou axe) qui éclaire un aspect du texte."},
    {"id":"477_4","type":"qcm","question":"Un relevé stylistique sans interprétation est :",
     "options":["Une analyse complète","Insuffisant : il faut toujours interpréter l'effet","Valorisé par les correcteurs","Une paraphrase"],
     "correct_option":"Insuffisant : il faut toujours interpréter l'effet","explanation":"Identifier un procédé n'est que la première étape ; il faut expliquer pourquoi l'auteur l'utilise et quel effet il produit."},
    {"id":"477_5","type":"vrai-faux","question":"L'introduction d'un commentaire doit situer le texte dans son contexte et annoncer les axes d'analyse.",
     "correct":True,"explanation":"L'introduction : accroche, présentation de l'auteur/œuvre/extrait, problématique, annonce du plan."},
    {"id":"477_6","type":"texte","question":"Le mouvement du texte désigne la _____ interne du passage, c'est-à-dire comment il progresse.",
     "correct_answer":"progression","explanation":"Identifier le mouvement permet de dégager les étapes de l'extrait avant de construire le plan."},
    {"id":"477_7","type":"qcm","question":"Dans quelle partie de la correction va-t-on insérer des citations courtes du texte ?",
     "options":["Uniquement en introduction","Dans chaque sous-partie pour appuyer les analyses","Uniquement en conclusion","Jamais"],
     "correct_option":"Dans chaque sous-partie pour appuyer les analyses","explanation":"Les citations (entre guillemets, avec référence) sont la preuve textuelle de chaque argument."},
    {"id":"477_8","type":"vrai-faux","question":"La conclusion d'un commentaire littéraire peut proposer une ouverture vers une autre œuvre.",
     "correct":True,"explanation":"L'ouverture met en perspective l'analyse en reliant le texte à une autre œuvre, un thème, une époque."},
]),

# ─── 478 – Grammaire avancée : syntaxe et subordination ──────────────────────
(478, "Grammaire avancée : syntaxe et subordination", "Français", "1ère", [
    {"id":"478_1","type":"qcm","question":"Quelle proposition est une subordonnée relative :",
     "options":["Parce qu'il pleuvait","Que je lis chaque soir","Bien qu'il soit fatigué","Dès que le soleil se lève"],
     "correct_option":"Que je lis chaque soir","explanation":"'Que je lis chaque soir' est une relative (introduite par un pronom relatif) qui complète un nom."},
    {"id":"478_2","type":"vrai-faux","question":"Une proposition subordonnée conjonctive complétive est introduite par 'que'.",
     "correct":True,"explanation":"Ex. : 'Il pense que tu as raison.' – 'que tu as raison' est une complétive, COD du verbe 'penser'."},
    {"id":"478_3","type":"texte","question":"Dans 'Bien qu'il travaille dur, il échoue', la subordonnée exprime une relation de _____.",
     "correct_answer":"concession","explanation":"'Bien que' introduit une subordonnée circonstancielle de concession."},
    {"id":"478_4","type":"qcm","question":"Quel est le mode verbal utilisé dans la subordonnée après 'bien que' ?",
     "options":["Indicatif","Conditionnel","Subjonctif","Infinitif"],
     "correct_option":"Subjonctif","explanation":"Les conjonctions de concession (bien que, quoique) exigent le subjonctif."},
    {"id":"478_5","type":"vrai-faux","question":"La proposition participiale est une subordonnée sans conjonction de subordination.",
     "correct":True,"explanation":"La proposition participiale (ex. 'La nuit tombant, nous rentrâmes') est construite autour d'un participe avec son sujet propre."},
    {"id":"478_6","type":"texte","question":"Dans la phrase complexe, la proposition principale est celle dont aucune autre ne dépend ; elle est syntaxiquement _____.",
     "correct_answer":"indépendante","explanation":"La principale n'est subordonnée à aucune autre ; les subordonnées en dépendent."},
    {"id":"478_7","type":"qcm","question":"Quelle conjonction introduit une subordonnée de but ?",
     "options":["Parce que","Pendant que","Pour que","Bien que"],
     "correct_option":"Pour que","explanation":"'Pour que' + subjonctif introduit une subordonnée circonstancielle de but."},
    {"id":"478_8","type":"vrai-faux","question":"Le discours indirect transforme les déictiques (je, ici, maintenant) du discours direct.",
     "correct":True,"explanation":"En passant au discours indirect, 'je' devient 'il/elle', 'ici' devient 'là', 'maintenant' devient 'alors', etc."},
]),

# ─── 479 – Orthographe : accord du participe passé ───────────────────────────
(479, "Orthographe : accord du participe passé", "Français", "1ère", [
    {"id":"479_1","type":"qcm","question":"Avec l'auxiliaire 'avoir', le participe passé s'accorde :",
     "options":["Toujours avec le sujet","Avec le COD si celui-ci est placé avant le verbe","Toujours avec le COI","Jamais"],
     "correct_option":"Avec le COD si celui-ci est placé avant le verbe","explanation":"Règle de base : PP avec avoir s'accorde avec le COD antéposé (ex. : Les lettres qu'il a écrites)."},
    {"id":"479_2","type":"vrai-faux","question":"'Nous nous sommes parlé' : le participe reste invariable car 'se' est COI.",
     "correct":True,"explanation":"Le verbe 'parler' est transitif indirect (parler à qqn) ; 'se' est COI, donc pas d'accord."},
    {"id":"479_3","type":"texte","question":"Dans 'Elle s'est lavée', le participe s'accorde parce que 'se' est _____ du verbe.",
     "correct_answer":"COD","explanation":"'Se laver' : elle a lavé qui ? → 'se' = elle-même → COD antéposé → accord."},
    {"id":"479_4","type":"qcm","question":"Quel est l'accord dans 'Les fleurs qu'il a cueilli___' ?",
     "options":["Cueilli (invariable)","Cueillie","Cueillis","Cueillies"],
     "correct_option":"Cueillies","explanation":"COD 'les fleurs' (féminin pluriel) est placé avant → accord : cueillies."},
    {"id":"479_5","type":"vrai-faux","question":"Avec l'auxiliaire 'être', le participe passé s'accorde toujours avec le sujet.",
     "correct":True,"explanation":"Ex. : Elles sont arrivées tôt. Sauf exceptions (verbes pronominaux avec COD postposé)."},
    {"id":"479_6","type":"texte","question":"Les participes passés des verbes pronominaux suivent la règle de l'accord avec l'auxiliaire _____ pour le choix de l'auxiliaire.",
     "correct_answer":"être","explanation":"Les verbes pronominaux se conjuguent toujours avec 'être' aux temps composés."},
    {"id":"479_7","type":"qcm","question":"Dans 'La chanson qu'elle a chanté___', le participe est :",
     "options":["Chanté","Chantée","Chantés","Chantées"],
     "correct_option":"Chantée","explanation":"COD 'la chanson' (fém. sing.) placé avant → chanté → e → chantée."},
    {"id":"479_8","type":"vrai-faux","question":"Le participe passé employé sans auxiliaire s'accorde comme un adjectif.",
     "correct":True,"explanation":"Ex. : Des lettres bien rédigées (épithète) → accord avec le nom."},
]),

# ─── 480 – Conjugaison : subjonctif et modes ─────────────────────────────────
(480, "Conjugaison : subjonctif et conditionnel", "Français", "1ère", [
    {"id":"480_1","type":"qcm","question":"Quand emploie-t-on principalement le subjonctif présent ?",
     "options":["Pour les faits certains","Dans les subordonnées après des verbes de doute, volonté, sentiment","Pour des hypothèses réelles","Dans les récits au passé"],
     "correct_option":"Dans les subordonnées après des verbes de doute, volonté, sentiment","explanation":"Verbes de volonté (vouloir), doute (douter), sentiment (regretter) imposent le subjonctif."},
    {"id":"480_2","type":"vrai-faux","question":"Le conditionnel présent peut exprimer un souhait ou une hypothèse irréelle.",
     "correct":True,"explanation":"Ex. : 'Je voudrais partir' (souhait) ; 'S'il venait, je serais content' (hypothèse irréelle dans l'apodose)."},
    {"id":"480_3","type":"texte","question":"Dans une période conditionnelle au passé, la structure est : 'Si + plus-que-parfait, _____ passé.'",
     "correct_answer":"conditionnel","explanation":"Ex. : 'Si tu avais étudié, tu aurais réussi.' – conditionnel passé dans l'apodose."},
    {"id":"480_4","type":"qcm","question":"Quelle est la conjugaison correcte du verbe 'être' au subjonctif présent, 3e personne du singulier ?",
     "options":["Est","Soit","Était","Serait"],
     "correct_option":"Soit","explanation":"Subjonctif présent d'être : que je sois, que tu sois, qu'il soit…"},
    {"id":"480_5","type":"vrai-faux","question":"L'imparfait du subjonctif est encore couramment utilisé à l'oral aujourd'hui.",
     "correct":False,"explanation":"L'imparfait du subjonctif ('qu'il fît', 'qu'il eût') est perçu comme archaïque et réservé au style soutenu écrit."},
    {"id":"480_6","type":"texte","question":"Le mode _____ exprime une action comme réelle, certaine ou probable ; c'est le mode de la réalité.",
     "correct_answer":"indicatif","explanation":"L'indicatif est le mode de l'assertion ; il s'oppose au subjonctif (mode du virtuel)."},
    {"id":"480_7","type":"qcm","question":"Quelle construction exige l'infinitif plutôt que le subjonctif ?",
     "options":["Sujets différents dans les deux propositions","Même sujet dans les deux propositions","Verbe de sentiment","Locution conjonctive de but"],
     "correct_option":"Même sujet dans les deux propositions","explanation":"Si le sujet est le même : 'Il veut partir' (infinitif) ; sujets différents : 'Il veut qu'elle parte' (subjonctif)."},
    {"id":"480_8","type":"vrai-faux","question":"Le conditionnel est parfois appelé 'mode' bien qu'il soit souvent classé parmi les temps de l'indicatif.",
     "correct":True,"explanation":"Selon les grammaires, le conditionnel est tantôt un mode à part, tantôt un temps de l'indicatif (futur du passé)."},
]),

# ─── 481 – Vocabulaire littéraire et figures de style ────────────────────────
(481, "Vocabulaire littéraire et figures de style", "Français", "1ère", [
    {"id":"481_1","type":"qcm","question":"Quelle figure de style compare deux éléments sans outil de comparaison ?",
     "options":["Comparaison","Métaphore","Métonymie","Synecdoque"],
     "correct_option":"Métaphore","explanation":"La métaphore est une comparaison implicite sans 'comme', 'tel que', etc."},
    {"id":"481_2","type":"vrai-faux","question":"L'hyperbole consiste à atténuer une réalité pour ménager la sensibilité.",
     "correct":False,"explanation":"L'hyperbole exagère (ex. : 'mourir de rire') ; c'est l'euphémisme qui atténue."},
    {"id":"481_3","type":"texte","question":"La _____ consiste à répéter le même mot ou la même construction en début de vers ou de phrase.",
     "correct_answer":"anaphore","explanation":"L'anaphore (ex. 'Je t'aime… Je t'aime…') crée un effet d'insistance et de rythme."},
    {"id":"481_4","type":"qcm","question":"Quelle figure désigne une partie pour le tout (ex. : 'une voile' pour 'un bateau') ?",
     "options":["Métaphore","Synecdoque","Oxymore","Litote"],
     "correct_option":"Synecdoque","explanation":"La synecdoque prend la partie pour le tout ou inversement."},
    {"id":"481_5","type":"vrai-faux","question":"L'ironie exprime le contraire de ce qu'elle dit, souvent pour critiquer.",
     "correct":True,"explanation":"Ex. : 'Quelle brillante idée !' (pour dire que l'idée est mauvaise) → ironie."},
    {"id":"481_6","type":"texte","question":"L'association de deux termes contradictoires dans une même expression s'appelle un _____.",
     "correct_answer":"oxymore","explanation":"Ex. : 'obscure clarté' (Corneille) ; 'douce violence' → oxymore."},
    {"id":"481_7","type":"qcm","question":"Quelle figure consiste à exprimer beaucoup en disant peu (ex. : 'ce n'est pas mal' pour 'c'est très bien') ?",
     "options":["Hyperbole","Euphémisme","Litote","Antiphrase"],
     "correct_option":"Litote","explanation":"La litote dit moins pour laisser entendre plus : 'Je ne le hais point' (Chimène dans Le Cid) = 'Je l'aime'."},
    {"id":"481_8","type":"vrai-faux","question":"La prosopopée est une figure qui fait parler un absent, un mort, une abstraction ou un objet.",
     "correct":True,"explanation":"Ex. : 'Si la mer pouvait parler…' → prosopopée."},
]),

# ─── 482 – Les mouvements littéraires : Humanisme, Baroque, Classicisme ───────
(482, "Mouvements littéraires : Humanisme, Baroque, Classicisme", "Français", "1ère", [
    {"id":"482_1","type":"qcm","question":"Quel mouvement du XVIe siècle redécouvre les textes antiques grecs et latins ?",
     "options":["Le Baroque","Le Classicisme","L'Humanisme","Le Romantisme"],
     "correct_option":"L'Humanisme","explanation":"L'Humanisme (XVIe s.) place l'Homme au centre et redécouvre les Anciens (studia humanitatis)."},
    {"id":"482_2","type":"vrai-faux","question":"Le Baroque est caractérisé par l'ordre, la mesure et la sobriété.",
     "correct":False,"explanation":"Le Baroque (fin XVIe-début XVIIe) valorise le mouvement, l'excès, l'ornement et l'instabilité."},
    {"id":"482_3","type":"texte","question":"La devise humaniste 'Je suis homme et rien d'humain ne m'est étranger' est associée à l'idéal de l'_____ universel.",
     "correct_answer":"homme","explanation":"L'humaniste aspire à l'honnête homme universel, curieux de tout savoir humain."},
    {"id":"482_4","type":"qcm","question":"Quel auteur est le représentant majeur du Classicisme dramatique ?",
     "options":["Ronsard","Montaigne","Racine","Victor Hugo"],
     "correct_option":"Racine","explanation":"Racine (Andromaque, Phèdre) est l'un des grands tragédiens classiques du XVIIe siècle."},
    {"id":"482_5","type":"vrai-faux","question":"Le Classicisme français se développe principalement sous Louis XIV.",
     "correct":True,"explanation":"Le Classicisme (1660-1685) est l'esthétique du Grand Siècle, soutenu par la monarchie absolue."},
    {"id":"482_6","type":"texte","question":"Ronsard, poète de la Pléiade, invite la jeune fille à profiter de la vie dans 'Cueillez dès aujourd'hui les _____ de la vie'.",
     "correct_answer":"roses","explanation":"Ronsard, 'Ode à Cassandre' : 'Cueillez dès aujourd'hui les roses de la vie' → carpe diem."},
    {"id":"482_7","type":"qcm","question":"Le Baroque au théâtre se manifeste notamment par :",
     "options":["La règle stricte des trois unités","Les tragi-comédies et le théâtre dans le théâtre","L'unité de ton","La séparation stricte des genres"],
     "correct_option":"Les tragi-comédies et le théâtre dans le théâtre","explanation":"Le Baroque mêle les genres et joue avec les illusions théâtrales (Le Vrai Saint-Genest de Rotrou)."},
    {"id":"482_8","type":"vrai-faux","question":"Descartes et son 'Je pense donc je suis' participe de l'esprit du Classicisme rationnel du XVIIe siècle.",
     "correct":True,"explanation":"La philosophie cartésienne (raison, méthode) est parallèle à l'esthétique classique (ordre, clarté)."},
]),

# ─── 483 – Les mouvements littéraires : Romantisme et Réalisme ───────────────
(483, "Mouvements littéraires : Romantisme et Réalisme", "Français", "1ère", [
    {"id":"483_1","type":"qcm","question":"Quel est le texte manifeste du Romantisme français ?",
     "options":["La Préface de Cromwell de Victor Hugo","Le Roman expérimental de Zola","L'Art poétique de Boileau","Du Bellay, Défense et illustration"],
     "correct_option":"La Préface de Cromwell de Victor Hugo","explanation":"La Préface de Cromwell (1827) de Hugo rompt avec les règles classiques et théorise le mélange des genres."},
    {"id":"483_2","type":"vrai-faux","question":"Le Romantisme valorise la raison et l'ordre au détriment des émotions.",
     "correct":False,"explanation":"Le Romantisme exalte les émotions, l'imagination, le moi et la nature contre le rationalisme des Lumières."},
    {"id":"483_3","type":"texte","question":"Le 'mal du siècle' romantique désigne une mélancolie et un sentiment de _____ ressentis par la génération post-révolutionnaire.",
     "correct_answer":"désenchantement","explanation":"Musset, dans La Confession d'un enfant du siècle, décrit ce malaise existentiel de la génération de 1820."},
    {"id":"483_4","type":"qcm","question":"Quel est le chef-d'œuvre du Romantisme de Victor Hugo ?",
     "options":["Germinal","Les Misérables","Madame Bovary","Le Rouge et le Noir"],
     "correct_option":"Les Misérables","explanation":"Les Misérables (1862) est le grand roman social de Hugo, incarnant les idéaux romantiques et humanistes."},
    {"id":"483_5","type":"vrai-faux","question":"Le Réalisme cherche à représenter le monde tel qu'il est, sans embellissement.",
     "correct":True,"explanation":"Le Réalisme (Balzac, Flaubert, Maupassant) vise la vérité sociale et psychologique."},
    {"id":"483_6","type":"texte","question":"Balzac regroupe l'ensemble de ses romans sous le titre _____ pour créer une fresque sociale de la France.",
     "correct_answer":"La Comédie humaine","explanation":"La Comédie humaine (90+ romans) est la grande entreprise réaliste de Balzac."},
    {"id":"483_7","type":"qcm","question":"La technique du 'retour des personnages' est une invention de :",
     "options":["Zola","Flaubert","Balzac","Maupassant"],
     "correct_option":"Balzac","explanation":"Balzac fait revenir les mêmes personnages d'un roman à l'autre pour créer l'illusion d'un monde cohérent."},
    {"id":"483_8","type":"vrai-faux","question":"Le Réalisme littéraire est un mouvement essentiellement du XXe siècle.",
     "correct":False,"explanation":"Le Réalisme se développe au XIXe siècle (1830-1880), avant de laisser place au Naturalisme."},
]),

# ─── 484 – Les mouvements littéraires : Symbolisme et Surréalisme ─────────────
(484, "Mouvements littéraires : Symbolisme et Surréalisme", "Français", "1ère", [
    {"id":"484_1","type":"qcm","question":"Quel manifeste fonde officiellement le Surréalisme ?",
     "options":["Le Manifeste du Futurisme","Le Manifeste du Surréalisme d'André Breton (1924)","La Préface de Cromwell","L'Art poétique de Verlaine"],
     "correct_option":"Le Manifeste du Surréalisme d'André Breton (1924)","explanation":"Breton publie le premier Manifeste du Surréalisme en 1924, définissant l'écriture automatique et l'exploration de l'inconscient."},
    {"id":"484_2","type":"vrai-faux","question":"L'écriture automatique surréaliste consiste à écrire sans contrôle de la raison.",
     "correct":True,"explanation":"L'écriture automatique vise à libérer le flux de l'inconscient en supprimant la censure rationnelle."},
    {"id":"484_3","type":"texte","question":"Le Symbolisme refuse de nommer les choses directement et préfère les _____.",
     "correct_answer":"suggérer","explanation":"'Peindre non la chose mais l'effet qu'elle produit' (Mallarmé) : le symbole suggère plutôt qu'il n'affirme."},
    {"id":"484_4","type":"qcm","question":"Quel poète est associé à la fois au Symbolisme et aux 'poètes maudits' ?",
     "options":["Victor Hugo","Paul Verlaine","Lamartine","Alfred de Vigny"],
     "correct_option":"Paul Verlaine","explanation":"Verlaine est présenté par lui-même comme un 'poète maudit' ; son œuvre est emblématique du Symbolisme musical."},
    {"id":"484_5","type":"vrai-faux","question":"Le Surréalisme s'intéresse aux rêves et à l'inconscient, influencé par les théories de Freud.",
     "correct":True,"explanation":"Breton est fasciné par la psychanalyse freudienne et cherche à libérer le 'fonctionnement réel de la pensée'."},
    {"id":"484_6","type":"texte","question":"Paul Éluard, Louis Aragon et André Breton sont des figures majeures du mouvement _____.",
     "correct_answer":"Surréalisme","explanation":"Ces trois poètes sont les piliers du groupe surréaliste parisien."},
    {"id":"484_7","type":"qcm","question":"Quelle technique surréaliste consiste à écrire un texte à plusieurs mains sans voir ce que l'autre a écrit ?",
     "options":["Cadavre exquis","Collage","Écriture automatique","Poème en prose"],
     "correct_option":"Cadavre exquis","explanation":"Le cadavre exquis (ex. 'Le cadavre exquis boira le vin nouveau') est un jeu collectif surréaliste."},
    {"id":"484_8","type":"vrai-faux","question":"Stéphane Mallarmé est un poète symboliste dont l'œuvre se caractérise par la clarté et la simplicité.",
     "correct":False,"explanation":"Mallarmé est réputé pour son hermétisme et sa recherche d'un langage poétique pur, difficile d'accès."},
]),

# ─── 485 – L'expression écrite : la synthèse de documents ────────────────────
(485, "L'expression écrite : la synthèse de documents", "Français", "1ère", [
    {"id":"485_1","type":"qcm","question":"Qu'est-ce qu'une synthèse de documents ?",
     "options":["Un résumé de chaque document séparément","Un texte qui articule les idées de plusieurs documents autour d'une problématique commune","Un commentaire littéraire","Un texte d'opinion personnel"],
     "correct_option":"Un texte qui articule les idées de plusieurs documents autour d'une problématique commune","explanation":"La synthèse regroupe et organise les idées des documents sans les juxtaposer ni donner son avis personnel."},
    {"id":"485_2","type":"vrai-faux","question":"Dans une synthèse, on peut donner son opinion personnelle.",
     "correct":False,"explanation":"La synthèse est un exercice objectif : on reformule les idées des auteurs sans les commenter ni y adhérer."},
    {"id":"485_3","type":"texte","question":"Dans une synthèse, on attribue chaque idée à son auteur à l'aide d'un verbe introducteur ou d'une _____.",
     "correct_answer":"référence","explanation":"Ex. : 'Selon (doc. 1)…', 'L'auteur du doc. 2 montre que…' → référence aux sources."},
    {"id":"485_4","type":"qcm","question":"Quelle est la structure habituelle d'une synthèse réussie ?",
     "options":["Résumé doc. 1, résumé doc. 2, résumé doc. 3","Introduction (problématique) + développement thématique + conclusion","Introduction + thèse + antithèse + synthèse","Résumé + commentaire + opinion"],
     "correct_option":"Introduction (problématique) + développement thématique + conclusion","explanation":"La synthèse suit un plan thématique (non documentaire) : les idées sont regroupées par thèmes, pas par document."},
    {"id":"485_5","type":"vrai-faux","question":"La synthèse peut comporter de longues citations extraites des documents.",
     "correct":False,"explanation":"Les citations sont réduites au minimum ; on reformule (reformulation fidèle) plutôt qu'on ne cite."},
    {"id":"485_6","type":"texte","question":"La _____ de documents commence par définir une problématique commune à l'ensemble du corpus.",
     "correct_answer":"synthèse","explanation":"Avant tout, on interroge : Quel est le problème/thème commun à tous ces documents ?"},
    {"id":"485_7","type":"qcm","question":"Le plan documentaire (doc. 1, doc. 2…) est à éviter dans une synthèse car :",
     "options":["Il rend la synthèse trop longue","Il juxtapose les documents sans les articuler","Il est difficile à mettre en œuvre","Il exige trop de citations"],
     "correct_option":"Il juxtapose les documents sans les articuler","explanation":"La synthèse doit croiser les documents, pas les présenter l'un après l'autre."},
    {"id":"485_8","type":"vrai-faux","question":"La synthèse de documents fait partie des épreuves anticipées de Français au baccalauréat.",
     "correct":False,"explanation":"L'épreuve anticipée de Français au bac comprend la lecture (commentaire ou dissertation) et l'écriture personnelle, non la synthèse qui est propre au BTS/classes prépas."},
]),

# ─── 486 – L'écriture d'invention ────────────────────────────────────────────
(486, "L'écriture d'invention", "Français", "1ère", [
    {"id":"486_1","type":"qcm","question":"L'écriture d'invention à l'EAF demande d'écrire :",
     "options":["Un résumé du corpus","Un texte original en respectant des contraintes de genre et de tonalité","Une synthèse de documents","Un commentaire critique"],
     "correct_option":"Un texte original en respectant des contraintes de genre et de tonalité","explanation":"L'écriture d'invention évalue la créativité et la maîtrise des codes génériques (argumentation, récit, dialogue…)."},
    {"id":"486_2","type":"vrai-faux","question":"Dans un texte d'invention argumentatif, il est inutile de respecter la logique de l'argumentation.",
     "correct":False,"explanation":"Même créative, l'écriture d'invention argumentative doit être cohérente et structurée."},
    {"id":"486_3","type":"texte","question":"Quand le sujet demande 'd'écrire la suite du texte', on doit respecter le registre, le _____ et les personnages.",
     "correct_answer":"ton","explanation":"La cohérence de ton, de style et de personnages garantit la vraisemblance de la suite."},
    {"id":"486_4","type":"qcm","question":"Pour un sujet d'invention 'Écrivez un discours engagé', quelle tonalité est attendue ?",
     "options":["Humoristique","Pathétique et/ou polémique","Épique","Fantastique"],
     "correct_option":"Pathétique et/ou polémique","explanation":"Un discours engagé use souvent du registre pathétique (émouvoir) et polémique (convaincre/persuader)."},
    {"id":"486_5","type":"vrai-faux","question":"La maîtrise des procédés stylistiques (figures de style, rythme) est valorisée dans l'écriture d'invention.",
     "correct":True,"explanation":"L'écriture d'invention évalue aussi la richesse stylistique et la maîtrise de la langue."},
    {"id":"486_6","type":"texte","question":"Si le sujet demande de rédiger une lettre fictive, on respecte les codes _____ de la lettre (date, formule d'appel, signature).",
     "correct_answer":"formels","explanation":"Les codes formels du genre (lettre, discours, article) sont évalués dans l'écriture d'invention."},
    {"id":"486_7","type":"qcm","question":"Qu'est-ce que le registre épique dans une écriture d'invention ?",
     "options":["L'expression de la tristesse","La grandeur, l'héroïsme, l'ampleur narrative","L'ironie et le comique","La peur et l'angoisse"],
     "correct_option":"La grandeur, l'héroïsme, l'ampleur narrative","explanation":"Le registre épique évoque les grandes actions, les héros, les batailles, avec un style ample et solennel."},
    {"id":"486_8","type":"vrai-faux","question":"Il est possible de mêler registres comique et pathétique dans un texte d'invention.",
     "correct":True,"explanation":"Hugo mêle justement le sublime et le grotesque ; les mélanges de registres peuvent être artistiquement réussis."},
]),

# ─── 487 – Victor Hugo : œuvres et thèmes ────────────────────────────────────
(487, "Victor Hugo : œuvres et thèmes majeurs", "Français", "1ère", [
    {"id":"487_1","type":"qcm","question":"Dans quel recueil Hugo exprime-t-il son deuil après la mort de sa fille Léopoldine ?",
     "options":["Les Contemplations","Les Châtiments","La Légende des siècles","Les Feuilles d'automne"],
     "correct_option":"Les Contemplations","explanation":"Les Contemplations (1856) comprennent le livre IV ('Pauca meae') dédié à Léopoldine."},
    {"id":"487_2","type":"vrai-faux","question":"Hugo a été exilé en Belgique et aux îles anglo-normandes pendant le Second Empire.",
     "correct":True,"explanation":"Opposé à Louis-Napoléon Bonaparte, Hugo s'exile à Bruxelles, Jersey et Guernesey (1851-1870)."},
    {"id":"487_3","type":"texte","question":"Dans Notre-Dame de Paris, le personnage de _____ illustre la beauté intérieure malgré la laideur physique.",
     "correct_answer":"Quasimodo","explanation":"Quasimodo, bossu et sourd, aime Esmeralda d'un amour pur ; Hugo illustre le contraste beauté/laideur."},
    {"id":"487_4","type":"qcm","question":"Les Misérables traite principalement de :",
     "options":["La guerre de Napoléon","La misère sociale et la rédemption humaine","L'amour courtois médiéval","La Révolution américaine"],
     "correct_option":"La misère sociale et la rédemption humaine","explanation":"Jean Valjean, ex-forçat, cherche sa rédemption dans une société injuste."},
    {"id":"487_5","type":"vrai-faux","question":"Victor Hugo est à la fois poète, dramaturge et romancier.",
     "correct":True,"explanation":"Hugo est l'un des rares auteurs à exceller dans tous les genres : poésie (Les Contemplations), théâtre (Hernani), roman (Notre-Dame de Paris, Les Misérables)."},
    {"id":"487_6","type":"texte","question":"Le discours d'Hugo 'Détruisez la misère !' est un exemple de littérature _____.",
     "correct_answer":"engagée","explanation":"Hugo se bat toute sa vie pour les pauvres, l'abolition de la peine de mort et les droits des enfants."},
    {"id":"487_7","type":"qcm","question":"Quelle pièce de Hugo provoque la fameuse 'bataille d'Hernani' en 1830 ?",
     "options":["Ruy Blas","Lucrèce Borgia","Hernani","Marion de Lorme"],
     "correct_option":"Hernani","explanation":"La première d'Hernani (1830) oppose classiques et romantiques dans une querelle célèbre."},
    {"id":"487_8","type":"vrai-faux","question":"Les Châtiments est un recueil de pamphlets poétiques contre Napoléon III.",
     "correct":True,"explanation":"Les Châtiments (1853) sont des poèmes satiriques violents contre Louis-Napoléon Bonaparte, écrits depuis l'exil."},
]),

# ─── 488 – Baudelaire : Les Fleurs du Mal ─────────────────────────────────────
(488, "Baudelaire et Les Fleurs du Mal", "Français", "1ère", [
    {"id":"488_1","type":"qcm","question":"En quelle année Les Fleurs du Mal sont-elles publiées ?",
     "options":["1820","1857","1880","1900"],
     "correct_option":"1857","explanation":"Les Fleurs du Mal paraissent en 1857 ; Baudelaire est condamné pour immoralité et doit supprimer 6 pièces."},
    {"id":"488_2","type":"vrai-faux","question":"Les Fleurs du Mal sont organisées en sections thématiques.",
     "correct":True,"explanation":"Le recueil est structuré en 6 sections : Spleen et Idéal, Tableaux parisiens, Le Vin, Fleurs du Mal, Révolte, La Mort."},
    {"id":"488_3","type":"texte","question":"Dans Les Fleurs du Mal, la tension centrale oppose le _____ (aspiration à l'élévation) au spleen (dépression).",
     "correct_answer":"idéal","explanation":"Spleen et Idéal est la première et la plus longue section : l'idéal est l'aspiration à la beauté absolue."},
    {"id":"488_4","type":"qcm","question":"Quelle image baudelairienne représente le poète incompris dans la société moderne ?",
     "options":["La rose","L'albatros","Le chat","Le gouffre"],
     "correct_option":"L'albatros","explanation":"Dans 'L'Albatros', le poète est comme l'oiseau majestueux en vol mais ridicule sur le pont du navire."},
    {"id":"488_5","type":"vrai-faux","question":"Baudelaire est considéré comme le précurseur du Symbolisme.",
     "correct":True,"explanation":"'Correspondances' et sa théorie des synesthésies préfigurent l'esthétique symboliste."},
    {"id":"488_6","type":"texte","question":"Le titre paradoxal 'Les Fleurs du Mal' réunit la _____ (fleurs) et le mal moral/esthétique.",
     "correct_answer":"beauté","explanation":"Baudelaire extrait la beauté (fleurs) du mal, de la souffrance et de la laideur moderne."},
    {"id":"488_7","type":"qcm","question":"Dans quel poème Baudelaire associe les parfums, les couleurs et les sons ?",
     "options":["Spleen","Correspondances","L'Invitation au voyage","La Beauté"],
     "correct_option":"Correspondances","explanation":"'Correspondances' développe la théorie des synesthésies entre les différents sens."},
    {"id":"488_8","type":"vrai-faux","question":"Le 'voyage' est un thème récurrent dans Les Fleurs du Mal, symbolisant la fuite et l'aspiration à l'idéal.",
     "correct":True,"explanation":"'L'Invitation au voyage', 'Le Voyage' (final du recueil) montrent l'aspiration à échapper au spleen."},
]),

# ─── 489 – Molière : comédies et satire sociale ───────────────────────────────
(489, "Molière : comédies et satire sociale", "Français", "1ère", [
    {"id":"489_1","type":"qcm","question":"Dans Le Tartuffe, quel vice principal est dénoncé ?",
     "options":["L'avarice","L'hypocrisie religieuse","La jalousie","L'orgueil"],
     "correct_option":"L'hypocrisie religieuse","explanation":"Tartuffe est un faux dévot qui manipule Orgon ; Molière dénonce l'hypocrisie des bigots."},
    {"id":"489_2","type":"vrai-faux","question":"Le Bourgeois gentilhomme de Molière est une comédie-ballet.",
     "correct":True,"explanation":"Le Bourgeois gentilhomme (1670) est une comédie-ballet avec musique de Lully."},
    {"id":"489_3","type":"texte","question":"Dans L'Avare, le personnage d'Harpagon incarne le défaut de l'_____ poussé à l'extrême.",
     "correct_answer":"avarice","explanation":"Harpagon est l'archétype de l'avare, prêt à sacrifier famille et bonheur pour son or."},
    {"id":"489_4","type":"qcm","question":"Quel est le mécanisme comique fondamental décrit par Bergson ?",
     "options":["L'ironie","Le rire naît du mécanique plaqué sur du vivant","Le paradoxe","Le jeu de mots"],
     "correct_option":"Le rire naît du mécanique plaqué sur du vivant","explanation":"Bergson (Le Rire, 1900) : le comique naît d'une rigidité mécanique (l'obsession) qui s'oppose à la souplesse du vivant."},
    {"id":"489_5","type":"vrai-faux","question":"Dom Juan de Molière est une tragédie.",
     "correct":False,"explanation":"Dom Juan (1665) est une comédie (en prose) qui explore l'athéisme et le libertinage."},
    {"id":"489_6","type":"texte","question":"La comédie de caractère met en scène un personnage dominé par un _____ unique (avarice, misanthropie…).",
     "correct_answer":"vice","explanation":"Chaque comédie de caractère de Molière centre l'intrigue sur un défaut psychologique dominant."},
    {"id":"489_7","type":"qcm","question":"Les Précieuses ridicules (1659) satirise :",
     "options":["Les paysans","L'Église","Les femmes qui singent les manières aristocratiques","Les médecins"],
     "correct_option":"Les femmes qui singent les manières aristocratiques","explanation":"Molière moque les bourgeoises qui imitent maladroitement les précieuses de la cour."},
    {"id":"489_8","type":"vrai-faux","question":"Molière a lui-même joué dans plusieurs de ses pièces.",
     "correct":True,"explanation":"Molière était acteur-auteur-directeur de troupe ; il jouait souvent le rôle du valet ou du héros ridicule."},
]),

# ─── 490 – Racine : la tragédie et les passions ───────────────────────────────
(490, "Racine : la tragédie et les passions", "Français", "1ère", [
    {"id":"490_1","type":"qcm","question":"Dans Phèdre, de quoi est coupable la reine Phèdre ?",
     "options":["Du meurtre de Thésée","D'un amour coupable pour son beau-fils Hippolyte","De trahison envers la Grèce","D'ambition politique"],
     "correct_option":"D'un amour coupable pour son beau-fils Hippolyte","explanation":"Phèdre est victime d'une passion incestueuse inspirée par Vénus, qui la mène à la mort."},
    {"id":"490_2","type":"vrai-faux","question":"La fatalité est une notion centrale dans la tragédie racinienne.",
     "correct":True,"explanation":"Les héros raciniens sont écrasés par une fatalité (divine, amoureuse, politique) à laquelle ils ne peuvent échapper."},
    {"id":"490_3","type":"texte","question":"Dans Andromaque, Oreste aime Hermione qui aime Pyrrhus qui aime Andromaque : cette structure est appelée chaîne d'_____ non partagé.",
     "correct_answer":"amour","explanation":"La chaîne d'amours non partagés crée une tension dramatique inexorable."},
    {"id":"490_4","type":"qcm","question":"Le style racinien est caractérisé par :",
     "options":["La pompe baroque et les métaphores foisonnantes","La simplicité apparente et l'intensité psychologique","L'humour et le burlesque","La longueur des tirades épiques"],
     "correct_option":"La simplicité apparente et l'intensité psychologique","explanation":"Racine use d'un vocabulaire simple mais dense ; 'Dans un mois, dans un an' (Bérénice) → sobriété déchirante."},
    {"id":"490_5","type":"vrai-faux","question":"Bérénice de Racine n'a pas de mort sur scène et se termine par une séparation.",
     "correct":True,"explanation":"Bérénice est une tragédie sans mort : Titus renonce à Bérénice pour la raison d'État ; ils se séparent."},
    {"id":"490_6","type":"texte","question":"La confidente, personnage secondaire de la tragédie classique, permet au héros de _____ ses sentiments.",
     "correct_answer":"exprimer","explanation":"La confidente (Œnone pour Phèdre, Pylade pour Oreste) permet l'exposition des sentiments intérieurs."},
    {"id":"490_7","type":"qcm","question":"Quelle est la particularité de la langue de Racine par rapport à celle de Corneille ?",
     "options":["Racine use du latin","Racine privilégie la psychologie amoureuse et le langage des passions","Racine écrit en prose","Racine s'inspire uniquement de la Bible"],
     "correct_option":"Racine privilégie la psychologie amoureuse et le langage des passions","explanation":"Corneille met en scène la volonté héroïque (Le Cid) ; Racine explore les abîmes de la passion dévastatrice."},
    {"id":"490_8","type":"vrai-faux","question":"Hermione dans Andromaque est un personnage passif qui subit les événements.",
     "correct":False,"explanation":"Hermione est au contraire un personnage actif, violent dans sa passion et sa jalousie, qui provoque la mort de Pyrrhus."},
]),

# ─── 491 – Textes de l'EAF : lecture analytique ──────────────────────────────
(491, "Lecture analytique : méthode et pratique", "Français", "1ère", [
    {"id":"491_1","type":"qcm","question":"Quelle est la première étape dans la lecture analytique d'un texte ?",
     "options":["Chercher les figures de style","Identifier le paratexte et situer l'extrait dans l'œuvre","Rédiger l'introduction","Compter les syllabes"],
     "correct_option":"Identifier le paratexte et situer l'extrait dans l'œuvre","explanation":"Avant l'analyse, on situe l'auteur, l'œuvre, le genre, le mouvement et la place de l'extrait."},
    {"id":"491_2","type":"vrai-faux","question":"Un axe de lecture est une piste d'analyse qui oriente l'étude d'une partie du texte.",
     "correct":True,"explanation":"L'axe (centre d'intérêt) est le fil conducteur de chaque partie de l'analyse."},
    {"id":"491_3","type":"texte","question":"Dans l'explication de texte, on procède à un _____ stylistique : repérer les procédés linguistiques et littéraires.",
     "correct_answer":"relevé","explanation":"Le relevé systématique (lexique, syntaxe, figures, rythme) précède l'interprétation."},
    {"id":"491_4","type":"qcm","question":"Comment appelle-t-on le mouvement interne d'un texte ?",
     "options":["La thèse","La progression","Le registre","Le genre"],
     "correct_option":"La progression","explanation":"Identifier la progression (comment le texte avance, quelles étapes) permet de construire un plan pertinent."},
    {"id":"491_5","type":"vrai-faux","question":"Il est conseillé de citer le texte en insérant de courtes citations entre guillemets dans l'analyse.",
     "correct":True,"explanation":"Les citations courtes et précises sont la preuve textuelle de chaque analyse ; elles sont indispensables."},
    {"id":"491_6","type":"texte","question":"Le registre d'un texte désigne l'ensemble des procédés qui créent une _____ particulière chez le lecteur.",
     "correct_answer":"tonalité","explanation":"Le registre (lyrique, comique, tragique…) est défini par les effets produits sur le lecteur."},
    {"id":"491_7","type":"qcm","question":"Dans l'analyse d'un texte théâtral, qu'est-ce qu'une scène d'exposition ?",
     "options":["La scène de la mort du héros","La scène d'ouverture qui présente la situation initiale","La scène de climax","La scène finale"],
     "correct_option":"La scène d'ouverture qui présente la situation initiale","explanation":"La scène d'exposition (acte I) fournit les informations nécessaires sur les personnages et la situation de départ."},
    {"id":"491_8","type":"vrai-faux","question":"La conclusion d'une lecture analytique peut proposer une mise en perspective de l'extrait.",
     "correct":True,"explanation":"La mise en perspective (ouverture) enrichit la conclusion en reliant le texte à d'autres œuvres ou contextes."},
]),

# ─── 492 – L'oral de l'EAF : préparation et méthode ─────────────────────────
(492, "L'oral de l'EAF : préparation et méthode", "Français", "1ère", [
    {"id":"492_1","type":"qcm","question":"Combien de temps dure la préparation de l'exposé oral à l'EAF ?",
     "options":["10 minutes","20 minutes","30 minutes","1 heure"],
     "correct_option":"30 minutes","explanation":"L'élève dispose de 30 minutes de préparation pour préparer son exposé de l'EAF."},
    {"id":"492_2","type":"vrai-faux","question":"À l'oral du bac de Français, l'élève peut consulter ses notes pendant l'exposé.",
     "correct":True,"explanation":"L'élève peut disposer de ses notes et annotations sur le texte pendant l'exposé."},
    {"id":"492_3","type":"texte","question":"L'examinateur peut poser des questions sur les _____ au programme lors de l'entretien suivant l'exposé.",
     "correct_answer":"œuvres","explanation":"L'entretien porte sur les œuvres intégrales étudiées dans l'année et sur les lectures cursives."},
    {"id":"492_4","type":"qcm","question":"Quelle qualité est primordiale lors de l'exposé oral du bac ?",
     "options":["Lire son texte mot à mot","Parler distinctement et structurer clairement son analyse","Mémoriser tout le cours","Éviter les figures de style"],
     "correct_option":"Parler distinctement et structurer clairement son analyse","explanation":"L'oral valorise la clarté de l'expression, la précision des analyses et la maîtrise des références."},
    {"id":"492_5","type":"vrai-faux","question":"L'oral de l'EAF dure en totalité environ 20 minutes (exposé + entretien).",
     "correct":True,"explanation":"L'exposé dure environ 10 minutes, puis l'entretien avec l'examinateur dure environ 10 minutes."},
    {"id":"492_6","type":"texte","question":"Pour l'oral, l'élève présente un texte étudié en classe : son exposé doit suivre un _____ clair avec introduction et conclusion.",
     "correct_answer":"plan","explanation":"Un plan en 2-3 axes, annoncé en introduction et conclu par une synthèse, structure l'exposé oral."},
    {"id":"492_7","type":"qcm","question":"Lors de l'entretien, si l'examinateur pose une question à laquelle on ne sait pas répondre, la meilleure attitude est :",
     "options":["Rester silencieux","Reconnaître honnêtement la limite de sa connaissance et proposer une piste","Changer de sujet","Répéter l'exposé"],
     "correct_option":"Reconnaître honnêtement la limite de sa connaissance et proposer une piste","explanation":"L'examinateur valorise l'honnêteté intellectuelle et la démarche de réflexion, même face à l'incertitude."},
    {"id":"492_8","type":"vrai-faux","question":"La lecture cursive (lecture personnelle) peut être évoquée lors de l'entretien de l'oral EAF.",
     "correct":True,"explanation":"L'examinateur peut interroger sur les lectures cursives lorsqu'elles enrichissent la réponse."},
]),

# ─── 493 – Poésie engagée et résistante ──────────────────────────────────────
(493, "Poésie engagée et résistante", "Français", "1ère", [
    {"id":"493_1","type":"qcm","question":"Quel poète de la Résistance a écrit 'Liberté' (1942) ?",
     "options":["Louis Aragon","Paul Éluard","René Char","Francis Ponge"],
     "correct_option":"Paul Éluard","explanation":"'Liberté' d'Éluard (1942), publié dans Poésie et Vérité, est un poème-manifeste de la Résistance."},
    {"id":"493_2","type":"vrai-faux","question":"Aragon a écrit des poèmes engagés pendant la Résistance sous le pseudonyme de 'François la Colère'.",
     "correct":True,"explanation":"Aragon signe certains poèmes 'François la Colère' pour contourner la censure nazie."},
    {"id":"493_3","type":"texte","question":"Dans 'Liberté' d'Éluard, le poème s'achève par l'écriture du mot _____ sur toutes les surfaces du monde.",
     "correct_answer":"Liberté","explanation":"Chaque strophe liste les surfaces (cahier d'écolier, arbre, pierre…) sur lesquelles le poète écrit 'Liberté'."},
    {"id":"493_4","type":"qcm","question":"René Char, poète résistant, est également connu comme :",
     "options":["Romancier policier","Chef de maquis dans le Vaucluse","Professeur de Sorbonne","Journaliste de guerre"],
     "correct_option":"Chef de maquis dans le Vaucluse","explanation":"Char (pseudonyme 'Capitaine Alexandre') dirige le groupe de résistance Ventoux dans le Vaucluse."},
    {"id":"493_5","type":"vrai-faux","question":"La poésie engagée renonce toujours à la beauté formelle au profit du message.",
     "correct":False,"explanation":"La poésie résistante (Éluard, Aragon, Char) maintient une haute exigence formelle ; la beauté renforce le message."},
    {"id":"493_6","type":"texte","question":"Le poème 'Le Désespoir du soleil' de René Char fait partie du recueil _____ (1946).",
     "correct_answer":"Feuillets d'Hypnos","explanation":"Feuillets d'Hypnos (1946) rassemble les notes de Char pendant sa période de résistance armée."},
    {"id":"493_7","type":"qcm","question":"Quelle technique le poème 'La Rose et le Réséda' d'Aragon célèbre-t-il ?",
     "options":["Le vers libre","Le sonnet traditionnel","L'alexandrin rimé","Le poème en prose"],
     "correct_option":"L'alexandrin rimé","explanation":"'La Rose et le Réséda' utilise des alexandrins rimés, récupérant la tradition formelle pour mieux résister."},
    {"id":"493_8","type":"vrai-faux","question":"Jacques Prévert est associé à une poésie engagée contre la guerre et les injustices sociales.",
     "correct":True,"explanation":"Prévert (Paroles, 1945) dénonce la guerre ('Familiale'), les inégalités et le conformisme avec humour et tendresse."},
]),

# ─── 494 – Le roman au XXe siècle : nouveaux courants ────────────────────────
(494, "Le roman au XXe siècle : nouveaux courants", "Français", "1ère", [
    {"id":"494_1","type":"qcm","question":"Le Nouveau Roman des années 1950-60 se caractérise par :",
     "options":["Une intrigue linéaire et des personnages bien définis","Le refus du personnage traditionnel, de l'intrigue et du narrateur omniscient","Un retour au roman historique","L'engagement politique explicite"],
     "correct_option":"Le refus du personnage traditionnel, de l'intrigue et du narrateur omniscient","explanation":"Robbe-Grillet, Sarraute, Butor, Simon déconstruisent les codes du roman réaliste."},
    {"id":"494_2","type":"vrai-faux","question":"Alain Robbe-Grillet est l'auteur du manifeste Pour un nouveau roman.",
     "correct":True,"explanation":"Pour un nouveau roman (1963) de Robbe-Grillet théorise les nouvelles pratiques romanesques."},
    {"id":"494_3","type":"texte","question":"L'Existentialisme, dont Sartre est la figure phare, affirme que l'existence précède l'_____, c'est-à-dire que l'homme se définit par ses choix.",
     "correct_answer":"essence","explanation":"'L'existence précède l'essence' : l'homme n'a pas de nature prédéfinie ; il se crée par ses actes."},
    {"id":"494_4","type":"qcm","question":"L'Étranger de Camus présente un narrateur :",
     "options":["Omniscient et engagé","Détaché et indifférent (absurde)","Lyrique et romantique","Comique et ironique"],
     "correct_option":"Détaché et indifférent (absurde)","explanation":"Meursault raconte les événements (même le meurtre) avec une neutralité qui incarne l'absurde camusien."},
    {"id":"494_5","type":"vrai-faux","question":"Simone de Beauvoir est une romancière et philosophe existentialiste.",
     "correct":True,"explanation":"De Beauvoir (Le Deuxième Sexe, Les Mandarins) unit fiction et philosophie existentialiste et féministe."},
    {"id":"494_6","type":"texte","question":"Dans le Nouveau Roman, le lecteur est souvent sollicité de manière active : il doit _____ le sens du texte.",
     "correct_answer":"construire","explanation":"Le Nouveau Roman refuse le sens tout fait et engage le lecteur dans une co-construction du sens."},
    {"id":"494_7","type":"qcm","question":"Quel roman de Nathalie Sarraute est souvent cité comme exemple de Nouveau Roman ?",
     "options":["La Jalousie","Tropismes","L'Étranger","Les Gommes"],
     "correct_option":"Tropismes","explanation":"Tropismes (1939, rééd. 1957) de Sarraute explore les micro-mouvements psychologiques presque imperceptibles."},
    {"id":"494_8","type":"vrai-faux","question":"Le roman existentialiste s'intéresse à la liberté et à la responsabilité de l'individu face au monde.",
     "correct":True,"explanation":"Sartre (La Nausée, Les Chemins de la liberté) fait du roman le lieu d'exploration de la condition humaine libre et responsable."},
]),

# ─── 495 – Révisions générales : œuvres au programme ─────────────────────────
(495, "Révisions : œuvres canoniques au programme", "Français", "1ère", [
    {"id":"495_1","type":"qcm","question":"Qui a écrit 'À la recherche du temps perdu' ?",
     "options":["André Gide","Marcel Proust","Émile Zola","Stendhal"],
     "correct_option":"Marcel Proust","explanation":"À la recherche du temps perdu (7 tomes, 1913-1927) est le grand roman de la mémoire involontaire de Proust."},
    {"id":"495_2","type":"vrai-faux","question":"L'Illiade et L'Odyssée sont des épopées grecques attribuées à Homère.",
     "correct":True,"explanation":"Ces deux épopées (VIIIe s. av. J.-C.) sont les textes fondateurs de la littérature occidentale."},
    {"id":"495_3","type":"texte","question":"Le mythe de Sisyphe, revisité par Camus, symbolise l'_____.",
     "correct_answer":"absurde","explanation":"Sisyphe, condamné à rouler un rocher éternellement, incarne l'absurde ; Camus conclut qu'il faut l'imaginer heureux."},
    {"id":"495_4","type":"qcm","question":"Quel texte de Rousseau est considéré comme une autobiographie fondatrice ?",
     "options":["Du contrat social","Les Confessions","L'Émile","La Nouvelle Héloïse"],
     "correct_option":"Les Confessions","explanation":"Les Confessions (posthume, 1782) de Rousseau inaugurent le genre autobiographique moderne."},
    {"id":"495_5","type":"vrai-faux","question":"La Princesse de Clèves (1678) est un roman de Madame de Lafayette.",
     "correct":True,"explanation":"Madame de Lafayette est l'auteure de ce roman psychologique précurseur du roman moderne."},
    {"id":"495_6","type":"texte","question":"Le 'roman épistolaire' comme Les Liaisons dangereuses est un roman composé de _____.",
     "correct_answer":"lettres","explanation":"Le roman épistolaire (epistola = lettre) est composé d'échanges de lettres entre personnages."},
    {"id":"495_7","type":"qcm","question":"Qui est l'auteur du Cid, tragi-comédie héroïque du XVIIe siècle ?",
     "options":["Molière","Racine","Corneille","Rotrou"],
     "correct_option":"Corneille","explanation":"Le Cid (1637) de Corneille provoque la querelle du Cid ; c'est une tragi-comédie devenue emblème du héroïsme cornélien."},
    {"id":"495_8","type":"vrai-faux","question":"Voltaire est à la fois philosophe, poète, dramaturge, essayiste et auteur de contes philosophiques.",
     "correct":True,"explanation":"Voltaire est un génie universel des Lumières, excellent dans tous les genres."},
]),

# ─── 496 – Révisions : méthodologie EAF ──────────────────────────────────────
(496, "Révisions : méthodologie EAF (écrit)", "Français", "1ère", [
    {"id":"496_1","type":"qcm","question":"L'épreuve écrite anticipée de Français (EAF) comporte :",
     "options":["Une rédaction libre","Un commentaire ou une dissertation + une écriture d'invention","Un contrôle de connaissances","Un résumé de texte"],
     "correct_option":"Un commentaire ou une dissertation + une écriture d'invention","explanation":"L'EAF écrit propose le choix entre commentaire et dissertation, plus un sujet d'écriture d'invention."},
    {"id":"496_2","type":"vrai-faux","question":"Le commentaire littéraire nécessite une introduction avec problématique et annonce de plan.",
     "correct":True,"explanation":"L'introduction du commentaire comprend : accroche, présentation du texte, problématique, annonce du plan."},
    {"id":"496_3","type":"texte","question":"En dissertation, la _____ est la question centrale à laquelle tout le devoir doit répondre.",
     "correct_answer":"problématique","explanation":"La problématique articule le sujet en une question ouverte, guide et fil conducteur du devoir."},
    {"id":"496_4","type":"qcm","question":"Quel plan choisir pour le sujet : 'Le roman est-il le meilleur genre pour dénoncer les injustices sociales ?' ?",
     "options":["Plan thématique","Plan dialectique","Plan chronologique","Plan analytique"],
     "correct_option":"Plan dialectique","explanation":"La question appelle une discussion : oui (thèse) / nuances ou non (antithèse) / dépassement (synthèse)."},
    {"id":"496_5","type":"vrai-faux","question":"L'écriture d'invention peut prendre la forme d'un dialogue, d'un discours ou d'un récit selon le sujet.",
     "correct":True,"explanation":"Le sujet d'invention précise souvent le genre (dialogue, lettre, discours, suite de texte…)."},
    {"id":"496_6","type":"texte","question":"Dans un commentaire, chaque sous-partie s'articule : idée directrice + analyse du procédé + _____ + interprétation.",
     "correct_answer":"citation","explanation":"La citation courte du texte est la preuve textuelle de l'analyse ; elle est indispensable."},
    {"id":"496_7","type":"qcm","question":"Combien de temps dure l'épreuve écrite anticipée de Français au bac ?",
     "options":["2 heures","3 heures","4 heures","5 heures"],
     "correct_option":"4 heures","explanation":"L'EAF écrit dure 4 heures, permettant de traiter les deux parties (lecture + écriture)."},
    {"id":"496_8","type":"vrai-faux","question":"Les brouillons et la planification comptent pour la note finale de l'EAF.",
     "correct":False,"explanation":"Seule la copie finale est notée ; les brouillons sont des outils de préparation non évalués."},
]),

# ─── 497 – La langue : registres et niveaux de langue ────────────────────────
(497, "Registres et niveaux de langue", "Français", "1ère", [
    {"id":"497_1","type":"qcm","question":"Quel niveau de langue est attendu dans un devoir de Français au lycée ?",
     "options":["Familier","Courant","Soutenu","Populaire"],
     "correct_option":"Soutenu","explanation":"L'EAF exige un niveau de langue soutenu : vocabulaire précis, syntaxe correcte, absence d'argot."},
    {"id":"497_2","type":"vrai-faux","question":"Le registre comique peut inclure le burlesque, le parodique et l'ironie.",
     "correct":True,"explanation":"Le registre comique recouvre différentes tonalités : humour, ironie, burlesque, farce, satire…"},
    {"id":"497_3","type":"texte","question":"Le registre _____ vise à émouvoir le lecteur par la représentation de la souffrance et du malheur.",
     "correct_answer":"pathétique","explanation":"Le pathétique (pathos) provoque compassion et pitié chez le lecteur."},
    {"id":"497_4","type":"qcm","question":"Qu'est-ce que le registre polémique ?",
     "options":["Une tonalité douce et mélancolique","Un ton agressif et combatif visant à attaquer une idée ou une personne","Un registre épique","Un style poétique"],
     "correct_option":"Un ton agressif et combatif visant à attaquer une idée ou une personne","explanation":"Le registre polémique (ex. pamphlets, éditoriaux engagés) cherche à déstabiliser l'adversaire."},
    {"id":"497_5","type":"vrai-faux","question":"Le niveau de langue familier est approprié dans une correspondance professionnelle.",
     "correct":False,"explanation":"La correspondance professionnelle exige un niveau soutenu ou courant, jamais familier."},
    {"id":"497_6","type":"texte","question":"Le _____ fantastique crée l'hésitation entre explication rationnelle et surnaturelle, selon Todorov.",
     "correct_answer":"registre","explanation":"Todorov définit le fantastique comme 'l'hésitation éprouvée par un être qui ne connaît que les lois naturelles, face à un événement en apparence surnaturel'."},
    {"id":"497_7","type":"qcm","question":"Quel registre domine dans les épopées homériques ?",
     "options":["Lyrique","Épique","Comique","Pathétique"],
     "correct_option":"Épique","explanation":"Le registre épique valorise la grandeur, les héros, les batailles et l'ampleur des enjeux collectifs."},
    {"id":"497_8","type":"vrai-faux","question":"Le registre tragique implique une fatalité qui écrase le personnage malgré ses efforts.",
     "correct":True,"explanation":"Le tragique naît du conflit entre la volonté du héros et une force supérieure (destin, passion, loi divine) qui le condamne."},
]),

# ─── 498 – Révisions finales : culture littéraire générale ───────────────────
(498, "Révisions finales : culture littéraire générale", "Français", "1ère", [
    {"id":"498_1","type":"qcm","question":"Quel est le titre complet du roman de Flaubert publié en 1857 ?",
     "options":["Madame Bovary, mœurs de province","Madame Bovary, roman réaliste","Madame Bovary, vie d'une femme","Madame Bovary, histoire d'une passion"],
     "correct_option":"Madame Bovary, mœurs de province","explanation":"Le sous-titre 'mœurs de province' ancre le roman dans une critique sociale du milieu bourgeois provincial."},
    {"id":"498_2","type":"vrai-faux","question":"La tragédie antique grecque est jouée en l'honneur du dieu Dionysos.",
     "correct":True,"explanation":"Les concours de tragédies athéniens (Eschyle, Sophocle, Euripide) se déroulent lors des fêtes dionysiaques."},
    {"id":"498_3","type":"texte","question":"Le sonnet est un poème de _____ vers, structuré en deux quatrains et deux tercets.",
     "correct_answer":"14","explanation":"Le sonnet (Pétrarque, Ronsard, Baudelaire) comporte 14 vers : ABBA ABBA CCD EDE (ou variantes)."},
    {"id":"498_4","type":"qcm","question":"Quel genre littéraire propose une vision idéale et utopique d'une société parfaite ?",
     "options":["La dystopie","L'utopie","Le roman d'anticipation","Le roman noir"],
     "correct_option":"L'utopie","explanation":"L'utopie (Thomas More, 1516) décrit une société idéale ; son contraire est la dystopie (Orwell, Huxley)."},
    {"id":"498_5","type":"vrai-faux","question":"La nouvelle littéraire se distingue du roman par sa brièveté et son effet de chute.",
     "correct":True,"explanation":"La nouvelle est un récit court, condensé, souvent avec un dénouement surprenant (chute ou fin ouverte)."},
    {"id":"498_6","type":"texte","question":"Le genre du _____ autobiographique implique que l'auteur, le narrateur et le personnage principal soient la même personne.",
     "correct_answer":"pacte","explanation":"Philippe Lejeune définit le 'pacte autobiographique' : identité auteur = narrateur = personnage principal."},
    {"id":"498_7","type":"qcm","question":"Quel est le mouvement littéraire du roman La Nausée de Sartre ?",
     "options":["Surréalisme","Existentialisme","Naturalisme","Romantisme"],
     "correct_option":"Existentialisme","explanation":"La Nausée (1938) de Sartre est le roman manifeste de l'existentialisme."},
    {"id":"498_8","type":"vrai-faux","question":"Le théâtre absurde s'inscrit dans la continuité du théâtre classique.",
     "correct":False,"explanation":"Le théâtre de l'absurde (Ionesco, Beckett) rompt avec toutes les conventions dramatiques classiques."},
]),

# ─── 499 – Grand oral : préparation et enjeux ────────────────────────────────
(499, "Grand oral : préparation et enjeux", "Français", "1ère", [
    {"id":"499_1","type":"qcm","question":"Le Grand Oral du baccalauréat s'appuie sur :",
     "options":["Un exposé de mathématiques","Une question liée aux spécialités du lycéen","Une récitation poétique","Un entretien sur la vie personnelle"],
     "correct_option":"Une question liée aux spécialités du lycéen","explanation":"Le Grand Oral (20 min) porte sur une question choisie par le candidat, en lien avec ses spécialités."},
    {"id":"499_2","type":"vrai-faux","question":"Le Grand Oral valorise la clarté de l'expression et la posture de l'orateur.",
     "correct":True,"explanation":"Les critères d'évaluation incluent la qualité de l'expression, la posture, le regard et la structuration du discours."},
    {"id":"499_3","type":"texte","question":"Pour préparer le Grand Oral, l'élève doit formuer sa question de façon _____, claire et problématisée.",
     "correct_answer":"précise","explanation":"La question doit être bien délimitée et problématisée pour permettre un développement structuré."},
    {"id":"499_4","type":"qcm","question":"Lors du Grand Oral, quelle est la durée de la prise de parole en continu ?",
     "options":["5 minutes","10 minutes","15 minutes","20 minutes"],
     "correct_option":"5 minutes","explanation":"Le candidat parle 5 minutes en continu, puis répond aux questions de l'examinateur pendant 10 minutes, puis suit 5 minutes sur le projet d'orientation."},
    {"id":"499_5","type":"vrai-faux","question":"Le Grand Oral peut également être utilisé pour présenter un projet personnel ou professionnel.",
     "correct":True,"explanation":"La 3e partie du Grand Oral (5 min) porte sur le projet d'orientation du candidat."},
    {"id":"499_6","type":"texte","question":"La capacité à reformuler ses idées, à écouter les questions et à répondre avec nuance s'appelle l'_____ orale.",
     "correct_answer":"aisance","explanation":"L'aisance orale (fluidité, adaptation, écoute) est un critère clé du Grand Oral."},
    {"id":"499_7","type":"qcm","question":"Pour le Grand Oral, une bonne introduction doit :",
     "options":["Résumer toute la réponse","Poser la question, en montrer l'intérêt et annoncer le plan","Citer une définition de dictionnaire","Donner une liste d'exemples"],
     "correct_option":"Poser la question, en montrer l'intérêt et annoncer le plan","explanation":"L'introduction accroche l'examinateur, contextualise la question et annonce la structure de l'exposé."},
    {"id":"499_8","type":"vrai-faux","question":"Il est conseillé d'utiliser des supports visuels (diaporama) lors du Grand Oral.",
     "correct":False,"explanation":"Le Grand Oral est une épreuve exclusivement orale, sans support numérique ou diaporama."},
]),

# ─── 500 – Révision ultime : panorama de la littérature française ──────────────
(500, "Révision ultime : panorama de la littérature française", "Français", "1ère", [
    {"id":"500_1","type":"qcm","question":"Quelle est la première grande époque de la littérature française ?",
     "options":["Le XVIe siècle humaniste","Le Moyen Âge (IXe-XVe siècle)","Le XVIIe siècle classique","Le XIXe siècle romantique"],
     "correct_option":"Le Moyen Âge (IXe-XVe siècle)","explanation":"La littérature française commence au Moyen Âge : Serments de Strasbourg (842), Chanson de Roland, roman courtois…"},
    {"id":"500_2","type":"vrai-faux","question":"La chanson de geste est un genre poétique médiéval qui chante les exploits guerriers des chevaliers.",
     "correct":True,"explanation":"La Chanson de Roland (XIe s.) est la chanson de geste la plus célèbre, chantant la bravoure des chevaliers de Charlemagne."},
    {"id":"500_3","type":"texte","question":"Le Roman de la Rose (XIIIe s.) est une allégorie de la _____ courtoise.",
     "correct_answer":"conquête","explanation":"Le Roman de la Rose allègorise la conquête de l'amour (la rose = la femme aimée)."},
    {"id":"500_4","type":"qcm","question":"Quel siècle est souvent surnommé le 'Grand Siècle' de la littérature française ?",
     "options":["XVIe siècle","XVIIe siècle","XVIIIe siècle","XIXe siècle"],
     "correct_option":"XVIIe siècle","explanation":"Le XVIIe siècle, siècle de Molière, Racine, Corneille, La Fontaine, La Rochefoucauld, est le 'Grand Siècle'."},
    {"id":"500_5","type":"vrai-faux","question":"Le XVIIIe siècle est surnommé le 'Siècle des Lumières' en raison du mouvement philosophique dominant.",
     "correct":True,"explanation":"Les philosophes (Voltaire, Rousseau, Montesquieu, Diderot) éclairent la raison et combattent l'obscurantisme."},
    {"id":"500_6","type":"texte","question":"Le XXe siècle voit naître le _____, mouvement qui affirme l'absurdité de l'existence humaine.",
     "correct_answer":"Existentialisme","explanation":"Sartre et Camus, dans les années 1940-50, définissent l'existentialisme comme philosophie et esthétique littéraire."},
    {"id":"500_7","type":"qcm","question":"Quel mouvement littéraire du XXe siècle associe littérature et engagement politique ?",
     "options":["Le Nouveau Roman","Le Surréalisme","La littérature engagée (Sartre, de Beauvoir)","Le Parnasse"],
     "correct_option":"La littérature engagée (Sartre, de Beauvoir)","explanation":"Sartre théorise l'engagement dans Qu'est-ce que la littérature ? (1947) : l'écrivain doit prendre position."},
    {"id":"500_8","type":"vrai-faux","question":"La littérature francophone inclut des auteurs du monde entier écrivant en français.",
     "correct":True,"explanation":"Aimé Césaire (Martinique), Léopold Sédar Senghor (Sénégal), Marguerite Yourcenar (Belgique)… la littérature francophone est mondiale."},
])]


def write_quiz_files():
    count = 0

    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(qid, title, subject, level, questions)
        answers_payload = make_answers(qid, title, subject, level, questions)
        quiz_payload = normalize_text_payload(quiz_payload)
        answers_payload = normalize_text_payload(answers_payload)

        with open(os.path.join(FR_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as quiz_file:
            json.dump(quiz_payload, quiz_file, ensure_ascii=False, indent=2)
            quiz_file.write("\n")

        with open(os.path.join(FR_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as answers_file:
            json.dump(answers_payload, answers_file, ensure_ascii=False, indent=2)
            answers_file.write("\n")

        with open(os.path.join(OUTPUT_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as quiz_file:
            json.dump(quiz_payload, quiz_file, ensure_ascii=False, indent=2)
            quiz_file.write("\n")

        with open(os.path.join(OUTPUT_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as answers_file:
            json.dump(answers_payload, answers_file, ensure_ascii=False, indent=2)
            answers_file.write("\n")

        with open(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as quiz_file:
            json.dump(quiz_payload, quiz_file, ensure_ascii=False, indent=2)
            quiz_file.write("\n")

        with open(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as answers_file:
            json.dump(answers_payload, answers_file, ensure_ascii=False, indent=2)
            answers_file.write("\n")

        count += 1
        print(f"  ✓ {qid}.json - {title}")

    print(f"\n✅ Français 1ère: {count} quiz générés (+ {count} réponses = {count * 6} fichiers)")


if __name__ == "__main__":
    print("Generating Français 1ère quizzes 466-500...")
    write_quiz_files()
