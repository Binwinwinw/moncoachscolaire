#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
GÃ©nÃ©ration des quiz FranÃ§ais 1Ã¨re â€“ IDs 466â€“500
35 quiz Ã— 8 questions = 280 questions
Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
"""

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
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

    markers = ("Ãƒ", "Ã‚", "Ã¢â‚¬", "Ã¢â‚¬â„¢", "Ã¢â‚¬Å“", "Ã¢â‚¬â€", "Ã¢â‚¬â€œ", "Ã…")
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
        "ÃƒÂ©": "Ã©", "ÃƒÂ¨": "Ã¨", "ÃƒÂª": "Ãª", "ÃƒÂ«": "Ã«", "ÃƒÂ ": "Ã ", "ÃƒÂ¢": "Ã¢",
        "ÃƒÂ´": "Ã´", "ÃƒÂ»": "Ã»", "ÃƒÂ¹": "Ã¹", "ÃƒÂ®": "Ã®", "ÃƒÂ¯": "Ã¯", "ÃƒÂ§": "Ã§",
        "Ãƒâ€°": "Ã‰", "Ãƒâ‚¬": "Ã€", "Ãƒâ€¡": "Ã‡", "Ã…â€œ": "Å“", "Ã‚": "", "Ã¢â‚¬â„¢": "â€™",
        "Ã¢â‚¬Å“": "â€œ", "Ã¢â‚¬\x9d": "â€", "Ã¢â‚¬â€œ": "â€“", "Ã¢â‚¬â€": "â€”", "Ã¢â‚¬Â¦": "â€¦",
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
                "type": "vrai-faux",
                "question": str(question.get("question", "")),
            }
        quiz_questions.append(sanitized)

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - SÃ©rie {qid}",
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
                "type": "vrai-faux",
                "answer": str(q.get("correct_answer", "")),
                "correction": str(q.get("explanation", "")),
            })
    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - SÃ©rie {qid}",
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
        return "vrai-faux"
    return question_type

quizzes_data = [

# â”€â”€â”€ 466 â€“ Le roman : personnage et narration â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(466, "Le roman : personnage et narration", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"466_1",
        "type":"qcm",
        "question":"Qu'est-ce qu'un narrateur homodiÃ©gÃ©tique ?",
        "options":["Il raconte une histoire dont il est absent","Il raconte une histoire dont il est le hÃ©ros","Il est omniscient et extÃ©rieur","Il s'adresse directement au lecteur"],
        "correct_option":"Il raconte une histoire dont il est le hÃ©ros","explanation":"HomodiÃ©gÃ©tique = narrateur prÃ©sent dans l'histoire comme personnage principal."
        },
    {
        "id":"466_2","type":"vrai-faux","question":"Le narrateur hÃ©tÃ©rodiÃ©gÃ©tique est absent de l'histoire qu'il raconte.",
        "correct":True,
        "explanation":"HÃ©tÃ©ro = autre ; ce narrateur est extÃ©rieur Ã  la diÃ©gÃ¨se."
        },
    {
        "id":"466_3",
        "type":"vrai-faux",
        "question":"ComplÃ©tez : 'La focalisation zÃ©ro correspond Ã  un narrateur _____, qui sait tout des personnages.'",
        "correct_answer":"omniscient",
        "explanation":"En focalisation zÃ©ro, le narrateur connaÃ®t pensÃ©es, sentiments et Ã©vÃ©nements passÃ©s/futurs."
    },
    {
        "id":"466_4",
        "type":"qcm",
        "question":"Quelle focalisation limite le savoir narratif Ã  ce que perÃ§oit un seul personnage ?",
        "options":["Focalisation zÃ©ro","Focalisation interne","Focalisation externe","Focalisation multiple"],
        "correct_option":"Focalisation interne",
        "explanation":"En focalisation interne, le lecteur ne sait que ce que sait le personnage focal."
    },
    {
        "id":"466_5",
        "type":"vrai-faux",
        "question":"Un personnage rond (au sens de E.M. Forster) est stÃ©rÃ©otypÃ© et ne change pas.",
        "correct":False,
        "explanation":"Un personnage rond est complexe et Ã©volue ; le personnage plat est stÃ©rÃ©otypÃ©."
    },
    {
        "id":"466_6",
        "type":"vrai-faux",
        "question":"Quel procÃ©dÃ© narratif consiste Ã  raconter un Ã©vÃ©nement antÃ©rieur Ã  l'action principale ?",
        "correct_answer":"analepse",
        "explanation":"L'analepse (ou retour en arriÃ¨re, flashback) remonte dans le temps."
    },
    {
        "id":"466_7",
        "type":"qcm",
        "question":"Le discours indirect libre se caractÃ©rise par :",
        "options":["L'utilisation de guillemets","L'absence de verbe introducteur et le maintien des temps du passÃ©","Une proposition subordonnÃ©e introduite par 'que'","La prÃ©sence d'un tiret de dialogue"],
        "correct_option":"L'absence de verbe introducteur et le maintien des temps du passÃ©",
        "explanation":"Le DIL fusionne la voix du narrateur et la pensÃ©e du personnage sans marqueurs typographiques."
    },
    {
        "id":"466_8",
        "type":"vrai-faux",
        "question":"Le temps dominant du rÃ©cit rÃ©aliste est l'imparfait pour les descriptions et le passÃ© simple pour les actions.",
        "correct":True,
        "explanation":"Cette alternance imparfait/passÃ© simple est caractÃ©ristique du roman rÃ©aliste du XIXe siÃ¨cle."
    },
    ]
),

# â”€â”€â”€ 467 â€“ Le roman rÃ©aliste et naturaliste â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(
    467, "Le roman rÃ©aliste et naturaliste", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"467_1",
        "type":"qcm",
        "question":"Quel auteur est le chef de file du naturalisme ?",
        "options":["Gustave Flaubert","Stendhal","Ã‰mile Zola","HonorÃ© de Balzac"],
        "correct_option":"Ã‰mile Zola",
        "explanation":"Zola thÃ©orise le naturalisme dans Le Roman expÃ©rimental (1880) et le met en pratique dans Les Rougon-Macquart."
    },
    {
        "id":"467_2",
        "type":"vrai-faux",
        "question":"La ComÃ©die humaine est l'oeuvre maÃ®tresse de Stendhal.",
        "correct":False,
        "explanation":"La ComÃ©die humaine appartient Ã  HonorÃ© de Balzac."
    },
    {
        "id":"467_3",
        "type":"vrai-faux",
        "question":"ComplÃ©tez : Madame Bovary, de Flaubert, est souvent citÃ© comme roman prÃ©curseur du _____.",
        "correct_answer":"rÃ©alisme","explanation":"Flaubert, bien que rejetant l'Ã©tiquette, est associÃ© au rÃ©alisme par son souci du style et de l'exactitude."
        },
    {
        "id":"467_4",
        "type":"qcm",
        "question":"Le naturalisme se distingue du rÃ©alisme principalement par :",
        "options":["L'usage de la 1re personne","L'application des mÃ©thodes scientifiques Ã  la littÃ©rature","Le refus de dÃ©crire le milieu social","L'idÃ©alisation des personnages"],
        "correct_option":"L'application des mÃ©thodes scientifiques Ã  la littÃ©rature",
        "explanation":"Zola s'inspire de Claude Bernard pour appliquer l'expÃ©rimentation Ã  la crÃ©ation romanesque."
    },
    {
        "id":"467_5",
        "type":"vrai-faux",
        "question":"Germinal de Zola dÃ©crit la condition des mineurs du Nord de la France.",
        "correct":True,
        "explanation":"Germinal (1885) appartient au cycle des Rougon-Macquart et peint la vie des mineurs de charbon."
    },
    {
        "id":"467_6",
        "type":"vrai-faux",
        "question":"Quel procÃ©dÃ© stylistique consiste Ã  accumuler des dÃ©tails concrets et prÃ©cis pour donner l'illusion du rÃ©el ?",
        "correct_answer":"effet de rÃ©el",
        "explanation":"Roland Barthes thÃ©orise l' 'effet de rÃ©el' : les dÃ©tails inutiles crÃ©ent un sentiment d'authenticitÃ©."
    },
    {
        "id":"467_7",
        "type":"qcm",
        "question":"Stendhal est l'auteur de :",
        "options":["Bel-Ami","Le Rouge et le Noir","Nana","L'Assommoir"],
        "correct_option":"Le Rouge et le Noir",
        "explanation":"Le Rouge et le Noir (1830) est le grand roman de Stendhal, avec Julien Sorel pour hÃ©ros."
    },
    {
        "id":"467_8",
        "type":"vrai-faux",
        "question":"Le roman rÃ©aliste idÃ©alise la rÃ©alitÃ© pour lui donner un aspect plus poÃ©tique.",
        "correct":False,
        "explanation":"Le rÃ©alisme vise Ã  reprÃ©senter fidÃ¨lement la rÃ©alitÃ© sociale et humaine, sans idÃ©alisation."
    },
]
),

# â”€â”€â”€ 468 â€“ Le roman d'apprentissage â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(
    468, "Le roman d'apprentissage (Bildungsroman)", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"468_1",
        "type":"qcm",
        "question":"Qu'est-ce qu'un roman d'apprentissage ?",
        "options":["Un roman policier","Un roman centrÃ© sur la formation et la maturation d'un personnage jeune","Un roman Ã©pistolaire","Un roman historique"],
        "correct_option":"Un roman centrÃ© sur la formation et la maturation d'un personnage jeune",
        "explanation":"Le Bildungsroman (all. : roman de formation) suit le hÃ©ros de l'adolescence Ã  l'Ã¢ge adulte."
    },
    {
        "id":"468_2",
        "type":"vrai-faux",
        "question":"L'Ã‰ducation sentimentale de Flaubert est un roman d'apprentissage.",
        "correct":True,
        "explanation":"FrÃ©dÃ©ric Moreau y vit une initiation amoureuse et sociale qui constitue son Ã©ducation."
    },
    {
        "id":"468_3",
        "type":"vrai-faux",
        "question":"Dans le roman d'apprentissage, le hÃ©ros part souvent d'une _____ (lieu natal) vers la ville ou le monde.",
        "correct_answer":"province",
        "explanation":"Le dÃ©part de la province vers Paris est un schÃ©ma rÃ©current (Rastignac, Julien Sorel, FrÃ©dÃ©ric Moreauâ€¦)."
    },
    {
        "id":"468_4",
        "type":"qcm",
        "question":"Quel personnage balzacien est le type mÃªme du jeune ambitieux venu de province Ã  Paris ?",
        "options":["Jean Valjean","Rastignac","Vautrin","PÃ¨re Goriot"],
        "correct_option":"Rastignac",
        "explanation":"EugÃ¨ne de Rastignac, dans Le PÃ¨re Goriot, incarne l'ambition provinciale dans la jungle parisienne."
    },
    {
        "id":"468_5",
        "type":"vrai-faux",
        "question":"Dans un roman d'apprentissage, le personnage principal n'Ã©volue pas au fil du rÃ©cit.",
        "correct":False,
        "explanation":"L'Ã©volution du personnage est prÃ©cisÃ©ment le moteur du roman de formation."
    },
    {
        "id":"468_6",
        "type":"vrai-faux",
        "question":"Le mentor est une figure rÃ©currente du roman d'apprentissage : il guide le hÃ©ros dans sa _____.",
        "correct_answer":"initiation",
        "explanation":"Le mentor (prÃ©cepteur, ami plus Ã¢gÃ©, figure paternelle) initie le hÃ©ros aux codes du monde."
    },
    {
        "id":"468_7",
        "type":"qcm",
        "question":"Quel roman de Voltaire peut Ãªtre lu comme une parodie du roman d'apprentissage ?",
        "options":["Zadig","Candide","MicromÃ©gas","L'IngÃ©nu"],
        "correct_option":"Candide",
        "explanation":"Candide (1759) suit un hÃ©ros naÃ¯f qui 'apprend' que le monde n'est pas le meilleur des mondes possible."},
    {
        "id":"468_8",
        "type":"vrai-faux",
        "question":"Le roman Ã©pistolaire est incompatible avec le schÃ©ma du roman d'apprentissage.",
        "correct":False,
        "explanation":"Certains romans Ã©pistolaires (ex. Les Liaisons dangereuses) peuvent comporter une dimension initiatique."
    },
]),

# â”€â”€â”€ 469 â€“ La poÃ©sie lyrique â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(469, "La poÃ©sie lyrique", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"469_1",
        "type":"qcm",
        "question":"Quelle est la caractÃ©ristique principale de la poÃ©sie lyrique ?",
        "options":["La narration d'Ã©vÃ©nements historiques","L'expression des sentiments personnels du poÃ¨te","La satire sociale","La description objective du monde"],
        "correct_option":"L'expression des sentiments personnels du poÃ¨te",
        "explanation":"Le lyrisme vient de la lyre : il exprime les Ã©motions intimes du sujet poÃ©tique."
    },
    {
        "id":"469_2",
        "type":"vrai-faux",
        "question":"Le Romantisme est un mouvement littÃ©raire favorable au lyrisme personnel.",
        "correct":True,
        "explanation":"Les Romantiques (Lamartine, Hugo, Musset, Vigny) font du 'moi' lyrique le centre de la crÃ©ation poÃ©tique."
    },
    {
        "id":"469_3",
        "type":"vrai-faux",
        "question":"Le spleen dÃ©signe chez Baudelaire un sentiment de _____ et d'ennui profond.",
        "correct_answer":"mÃ©lancolie",
        "explanation":"Le spleen baudelairien est une mÃ©lancolie existentielle, opposÃ©e Ã  l'idÃ©al."
    },
    {
        "id":"469_4",
        "type":"qcm",
        "question":"Quelle figure de style exprime le lyrisme en attribuant des sentiments Ã  la nature ?",
        "options":["MÃ©taphore","ProsopopÃ©e","Personnification de la nature / pathetic fallacy","Anaphore"],
        "correct_option":"Personnification de la nature / pathetic fallacy",
        "explanation":"Le poÃ¨te lyrique projette ses Ã©motions sur la nature (paysage Ã©tat d'Ã¢me)."
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
        "type":"vrai-faux",
        "question":"Le recueil de Lamartine intitulÃ© _____ (1820) inaugure le lyrisme romantique en France.",
        "correct_answer":"MÃ©ditations poÃ©tiques",
        "explanation":"Les MÃ©ditations poÃ©tiques de Lamartine (1820) marquent le dÃ©but de la poÃ©sie romantique franÃ§aise."
    },
    {
        "id":"469_7",
        "type":"qcm",
        "question":"Dans 'Le Lac' de Lamartine, le poÃ¨te s'adresse au lac pour :",
        "options":["DÃ©crire un paysage pittoresque","Lutter contre l'oubli du temps et d'un amour disparu","CÃ©lÃ©brer une victoire","Critiquer la sociÃ©tÃ©"],
        "correct_option":"Lutter contre l'oubli du temps et d'un amour disparu",
        "explanation":"Le lac est invoquÃ© comme tÃ©moin de l'amour passÃ© ; le poÃ¨me mÃ©dite sur la fuite du temps."
    },
    {
        "id":"469_8",
        "type":"vrai-faux",
        "question":"Le vers libre est caractÃ©risÃ© par l'absence de mÃ©trique fixe et de rime obligatoire.",
        "correct":True,
        "explanation":"Le vers libre (Verlaine, Rimbaud, puis XXe siÃ¨cle) s'affranchit des contraintes prosodiques classiques."
    },
]),

# â”€â”€â”€ 470 â€“ La poÃ©sie symboliste â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(470, "La poÃ©sie symboliste", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"470_1",
        "type":"qcm",
        "question":"Quel poÃ¨me de Baudelaire est considÃ©rÃ© comme manifeste du Symbolisme ?",
        "options":["Spleen","Correspondances","L'Albatros","Harmonie du soir"],
        "correct_option":"Correspondances",
        "explanation":"Dans 'Correspondances', Baudelaire pose les bases du Symbolisme en affirmant les liens secrets entre les sens."
    },
    {
        "id":"470_2",
        "type":"vrai-faux",
        "question":"Le Symbolisme est un mouvement du XVIIe siÃ¨cle.",
        "correct":False,
        "explanation":"Le Symbolisme est un mouvement de la fin du XIXe siÃ¨cle (1880-1900), avec MallarmÃ©, Verlaine, Rimbaud."
    },
    {
        "id":"470_3",
        "type":"vrai-faux",
        "question":"Chez les symbolistes, le symbole est une image qui suggÃ¨re une rÃ©alitÃ© _____ invisible.",
        "correct_answer":"spirituelle",
        "explanation":"Le symbole renvoie Ã  un au-delÃ  du rÃ©el visible : idÃ©es, Ã©tats d'Ã¢me, vÃ©ritÃ©s mÃ©taphysiques."
    },
    {
        "id":"470_4",
        "type":"qcm",
        "question":"Verlaine prÃ©conise dans 'Art poÃ©tique' de privilegier :",
        "options":["La rime riche","La musique avant toute chose","La clartÃ© du discours","La rÃ©gularitÃ© du mÃ¨tre"],
        "correct_option":"La musique avant toute chose",
        "explanation":"'De la musique avant toute chose' : Verlaine place la musicalitÃ© au-dessus de la signification."
    },
    {
        "id":"470_5",
        "type":"vrai-faux",
        "question":"Rimbaud a Ã©crit 'Une saison en enfer'.",
        "correct":True,
        "explanation":"Une saison en enfer (1873) est l'Å“uvre autobiographique majeure de Rimbaud."
    },
    {
        "id":"470_6",
        "type":"vrai-faux",
        "question":"MallarmÃ© recherche un langage poÃ©tique pur, dÃ©pouillÃ© du rÃ©el, visant Ã  suggÃ©rer plutÃ´t qu'Ã  _____.",
        "correct_answer":"nommer",
        "explanation":"'Nommer un objet, c'est supprimer les trois quarts de la jouissance du poÃ¨me' (MallarmÃ©)."
    },
    {
        "id":"470_7",
        "type":"qcm",
        "question":"La synesthÃ©sie, procÃ©dÃ© cher aux symbolistes, consiste Ã  :",
        "options":["RÃ©pÃ©ter un son en fin de vers","Associer des sensations de registres diffÃ©rents","Inverser l'ordre habituel des mots","Omettre la ponctuation"],
        "correct_option":"Associer des sensations de registres diffÃ©rents",
        "explanation":"Ex. : 'des parfums frais comme des chairs d'enfants' (Baudelaire) mÃªle olfactif et tactile."
    },
    {
        "id":"470_8",
        "type":"vrai-faux",
        "question":"Le Parnasse et le Symbolisme partagent le mÃªme idÃ©al poÃ©tique.",
        "correct":False,
        "explanation":"Le Parnasse valorise la forme parfaite et l'impassibilitÃ© ; le Symbolisme cherche la suggestion et la musique intÃ©rieure."
    },
]),

# â”€â”€â”€ 471 â€“ Le thÃ©Ã¢tre classique â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(471, "Le thÃ©Ã¢tre classique (XVIIe siÃ¨cle)", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"471_1",
        "type":"qcm",
        "question":"Quelles sont les trois unitÃ©s du thÃ©Ã¢tre classique ?",
        "options":["Temps, lieu, action","Personnage, intrigue, dÃ©nouement","Exposition, nÅ“ud, dÃ©nouement","Actes, scÃ¨nes, rÃ©pliques"],
        "correct_option":"Temps, lieu, action",
        "explanation":"La rÃ¨gle des trois unitÃ©s impose : une journÃ©e, un lieu, une action principale."
    },
    {
        "id":"471_2",
        "type":"vrai-faux",
        "question":"La biensÃ©ance interdit de montrer des scÃ¨nes violentes sur la scÃ¨ne classique.",
        "correct":True,
        "explanation":"La biensÃ©ance impose que les actes choquants (meurtre, violence) soient narrÃ©s plutÃ´t que montrÃ©s."
    },
    {
        "id":"471_3",
        "type":"vrai-faux",
        "question":"La _____ est la rÃ¨gle imposant que les Ã©vÃ©nements reprÃ©sentÃ©s paraissent vraisemblables au spectateur.",
        "correct_answer":"vraisemblance",
        "explanation":"La vraisemblance exige que l'action soit crÃ©dible ; elle est complÃ©mentaire de la biensÃ©ance."
    },
    {
        "id":"471_4",
        "type":"qcm",
        "question":"MoliÃ¨re est l'auteur de :",
        "options":["Andromaque","Le Misanthrope","PhÃ¨dre","Le Cid"],
        "correct_option":"Le Misanthrope",
        "explanation":"Le Misanthrope (1666) est une comÃ©die de MoliÃ¨re qui met en scÃ¨ne Alceste, ennemi de la flatterie."
    },
    {
        "id":"471_5",
        "type":"vrai-faux",
        "question":"PhÃ¨dre est une tragÃ©die de Corneille.",
        "correct":False,
        "explanation":"PhÃ¨dre (1677) est une tragÃ©die de Jean Racine."
    },
    {
        "id":"471_6",
        "type":"vrai-faux",
        "question":"Dans la tragÃ©die classique, la _____ est le moment de basculement qui conduit le hÃ©ros vers sa chute.",
        "correct_answer":"pÃ©ripÃ©tie",
        "explanation":"La pÃ©ripÃ©tie (renversement de situation) prÃ©cipite la catastrophe finale."
    },
    {
        "id":"471_7",
        "type":"qcm",
        "question":"Quel est le but de la tragÃ©die selon Aristote ?",
        "options":["Faire rire le spectateur","Provoquer la catharsis (purification des passions)","Critiquer la sociÃ©tÃ©","Enseigner l'histoire"],
        "correct_option":"Provoquer la catharsis (purification des passions)",
        "explanation":"La catharsis aristotÃ©licienne : la tragÃ©die purge les passions de crainte et de pitiÃ©."
    },
    {
        "id":"471_8",
        "type":"vrai-faux",
        "question":"L'alexandrin est le vers dominant de la tragÃ©die classique franÃ§aise.",
        "correct":True,
        "explanation":"La tragÃ©die classique est Ã©crite en alexandrins (12 syllabes) rimÃ©s."
    },
]),

# â”€â”€â”€ 472 â€“ Le thÃ©Ã¢tre moderne et contemporain â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(472, "Le thÃ©Ã¢tre moderne et contemporain", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"472_1",
        "type":"qcm",
        "question":"Quel mouvement dramatique du XXe siÃ¨cle remet en cause les conventions thÃ©Ã¢trales traditionnelles ?",
        "options":["Le Classicisme","Le thÃ©Ã¢tre de l'absurde","Le Romantisme","Le Naturalisme"],
        "correct_option":"Le thÃ©Ã¢tre de l'absurde",
        "explanation":"Ionesco, Beckett et le thÃ©Ã¢tre de l'absurde dÃ©construisent le langage et la logique dramatique."
    },
    {
        "id":"472_2",
        "type":"vrai-faux",
        "question":"En attendant Godot de Beckett possÃ¨de une intrigue linÃ©aire et un dÃ©nouement clair.",
        "correct":False,
        "explanation":"En attendant Godot est construit sur la rÃ©pÃ©tition, l'attente et l'absence de rÃ©solution."
    },
    {
        "id":"472_3",
        "type":"vrai-faux",
        "question":"Le drame bourgeois du XVIIIe siÃ¨cle, thÃ©orisÃ© par Diderot, introduit des personnages issus de la _____.",
        "correct_answer":"bourgeoisie",
        "explanation":"Diderot veut reprÃ©senter des personnages de condition moyenne, plus proches du spectateur."
    },
    {
        "id":"472_4",
        "type":"qcm",
        "question":"Bertolt Brecht dÃ©veloppe le concept de :",
        "options":["Catharsis","Distanciation (Verfremdungseffekt)","BiensÃ©ance","Vraisemblance"],
        "correct_option":"Distanciation (Verfremdungseffekt)",
        "explanation":"La distanciation brechtiÃ¨nne empÃªche l'identification du spectateur pour favoriser son esprit critique."
    },
    {
        "id":"472_5",
        "type":"vrai-faux",
        "question":"Le monologue intÃ©rieur peut Ãªtre utilisÃ© au thÃ©Ã¢tre sous la forme d'apartÃ© ou de soliloque.",
        "correct":True,
        "explanation":"Le soliloque (parole seul sur scÃ¨ne) et l'apartÃ© (parole Ã  voix basse non entendue des autres personnages) sont des formes thÃ©Ã¢trales de l'intÃ©rioritÃ©."
    },
    {
        "id":"472_6",
        "type":"vrai-faux",
        "question":"La didascalie est une indication scÃ©nique fournie par l' _____ pour guider le metteur en scÃ¨ne et les acteurs.",
        "correct_answer":"auteur",
        "explanation":"Les didascalies (en italique) prÃ©cisent dÃ©cors, costumes, gestes, ton."
    },
    {
        "id":"472_7",
        "type":"qcm",
        "question":"Le thÃ©Ã¢tre Ã©pique, cher Ã  Brecht, se caractÃ©rise par :",
        "options":["L'immersion totale du spectateur","La rupture de l'illusion thÃ©Ã¢trale","L'absence de conflits","L'unitÃ© de temps et de lieu"],
        "correct_option":"La rupture de l'illusion thÃ©Ã¢trale",
        "explanation":"Le thÃ©Ã¢tre Ã©pique interpelle le public, use de panneaux, de chants, pour briser l'illusion."
    },
    {
        "id":"472_8",
        "type":"vrai-faux",
        "question":"EugÃ¨ne Ionesco est l'auteur de La Cantatrice chauve.",
        "correct":True,"explanation":"La Cantatrice chauve (1950) est la premiÃ¨re piÃ¨ce de Ionesco, fondatrice du thÃ©Ã¢tre de l'absurde en France."},
]),

# â”€â”€â”€ 473 â€“ L'argumentation : thÃ¨se et arguments â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(473, "L'argumentation : thÃ¨se et arguments", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"473_1",
        "type":"qcm",
        "question":"Qu'est-ce qu'une thÃ¨se dans un texte argumentatif ?",
        "options":["Un exemple concret","La position dÃ©fendue par l'auteur","Un contre-argument","Une question rhÃ©torique"],
        "correct_option":"La position dÃ©fendue par l'auteur","explanation":"La thÃ¨se est l'idÃ©e principale que l'auteur cherche Ã  faire admettre au lecteur."},
    {
        "id":"473_2",
        "type":"vrai-faux",
        "question":"Un argument est une affirmation qui Ã©taie ou rÃ©fute une thÃ¨se.",
        "correct":True,"explanation":"L'argument apporte une raison, un fait ou un raisonnement qui soutient ou contredit la thÃ¨se."},
    {
        "id":"473_3",
        "type":"vrai-faux",
        "question":"L'illustration d'un argument par un fait prÃ©cis ou une anecdote s'appelle un _____.",
        "correct_answer":"exemple","explanation":"L'exemple concrÃ©tise l'argument et lui donne une valeur dÃ©monstrative."},
    {
        "id":"473_4",
        "type":"qcm",
        "question":"Quel est le rÃ´le de la concession dans l'argumentation ?",
        "options":["Refuser tout contre-argument","Admettre partiellement l'opinion adverse avant de la rÃ©futer","Renforcer l'Ã©motion du lecteur","Introduire la conclusion"],
        "correct_option":"Admettre partiellement l'opinion adverse avant de la rÃ©futer","explanation":"La concession (certesâ€¦, il est vrai queâ€¦ mais) montre que l'auteur connaÃ®t les objections et les dÃ©passe."},
    {
        "id":"473_5",
        "type":"vrai-faux",
        "question":"Un syllogisme est un raisonnement dÃ©ductif en trois Ã©tapes : majeure, mineure, conclusion.",
        "correct":True,"explanation":"Ex. : Tous les hommes sont mortels (majeure) ; Socrate est un homme (mineure) ; donc Socrate est mortel."},
    {
        "id":"473_6",
        "type":"vrai-faux",
        "question":"L'appel aux Ã©motions du lecteur pour le convaincre est appelÃ© _____ dans la rhÃ©torique classique.",
        "correct_answer":"pathos","explanation":"Le pathos (ethos, pathos, logos) vise Ã  Ã©mouvoir le destinataire pour le persuader."},
    {
        "id":"473_7",
        "type":"qcm",
        "question":"La rÃ©futation directe consiste Ã  :",
        "options":["Ignorer l'argument adverse","DÃ©montrer qu'un argument adverse est faux ou insuffisant","Reformuler sa propre thÃ¨se","Utiliser une mÃ©taphore"],
        "correct_option":"DÃ©montrer qu'un argument adverse est faux ou insuffisant","explanation":"La rÃ©futation directe attaque frontalement l'argument de l'adversaire."},
    {
        "id":"473_8",
        "type":"vrai-faux",
        "question":"Dans un plan dialectique, la synthÃ¨se dÃ©passe le simple rÃ©sumÃ© des deux parties prÃ©cÃ©dentes.",
        "correct":True,"explanation":"La synthÃ¨se propose un dÃ©passement (Aufhebung) qui intÃ¨gre et transcende thÃ¨se et antithÃ¨se."},
]),

# â”€â”€â”€ 474 â€“ Les formes de l'argumentation : essai, pamphlet, apologue â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(474, "Formes de l'argumentation : essai, pamphlet, apologue", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"474_1",
        "type":"qcm",
        "question":"Quel genre consiste en une rÃ©flexion personnelle non exhaustive sur un sujet ?",
        "options":["Le pamphlet","L'apologue","L'essai","La fable"],
        "correct_option":"L'essai","explanation":"L'essai (Montaigne, XVI e s.) est une rÃ©flexion libre et subjective sur des questions diverses."},
    {
        "id":"474_2",
        "type":"vrai-faux",
        "question":"Un pamphlet est un texte polÃ©mique qui attaque violemment une personne ou une idÃ©e.",
        "correct":True,"explanation":"Le pamphlet (ex. J'accuse de Zola) est virulent et engagÃ©, souvent Ã  caractÃ¨re politique."},
    {
        "id":"474_3",
        "type":"vrai-faux",
        "question":"L'apologue est un rÃ©cit court Ã  visÃ©e _____, illustrant une morale.",
        "correct_answer":"didactique","explanation":"L'apologue (fable, conte philosophique, parabole) enseigne par le plaisir d'un rÃ©cit fictif."},
    {
        "id":"474_4",
        "type":"qcm",
        "question":"Quel est l'auteur des Fables, forme d'apologue majeure de la littÃ©rature franÃ§aise ?",
        "options":["Montaigne","La Fontaine","Voltaire","MoliÃ¨re"],
        "correct_option":"La Fontaine","explanation":"Jean de La Fontaine (1621-1695) est l'auteur des Fables en vers, adaptÃ©es d'Ã‰sope et PhÃ¨dre."},
    {
        "id":"474_5",
        "type":"vrai-faux",
        "question":"Candide de Voltaire est un conte philosophique qui peut Ãªtre classÃ© comme apologue.",
        "correct":True,"explanation":"Candide illustre la critique de l'optimisme leibnizien Ã  travers un rÃ©cit fictif Ã  portÃ©e didactique."},
    {
        "id":"474_6",
        "type":"vrai-faux",
        "question":"Dans une fable, la _____ rÃ©sume la leÃ§on morale, souvent placÃ©e au dÃ©but ou Ã  la fin du texte.",
        "correct_answer":"morale","explanation":"La morale est l'Ã©noncÃ© de la leÃ§on que l'auteur tire du rÃ©cit allÃ©gorique."},
    {
        "id":"474_7",
        "type":"qcm",
        "question":"Montaigne est l'inventeur du genre de l'essai avec son Å“uvre :",
        "options":["Le Discours de la mÃ©thode","Les Essais","Candide","Les PensÃ©es"],
        "correct_option":"Les Essais","explanation":"Les Essais (1580-1588) de Montaigne inaugurent le genre de la rÃ©flexion personnelle libre."},
    {
        "id":"474_8",
        "type":"vrai-faux",
        "question":"La parabole est une forme d'apologue Ã  caractÃ¨re religieux ou moral.",
        "correct":True,"explanation":"Les paraboles Ã©vangÃ©liques (ex. le Fils prodigue) sont des rÃ©cits symboliques Ã  portÃ©e morale."},
]),

# â”€â”€â”€ 475 â€“ Les LumiÃ¨res et la littÃ©rature engagÃ©e â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(475, "Les LumiÃ¨res et la littÃ©rature engagÃ©e (XVIIIe s.)", "FranÃ§ais", "1Ã¨re", [
    {
        "id":"475_1",
        "type":"qcm",
        "question":"Quel est le grand projet Ã©ditorial des philosophes des LumiÃ¨res ?",
        "options":["La ComÃ©die humaine","L'EncyclopÃ©die","Les Rougon-Macquart","La Nouvelle HÃ©loÃ¯se"],
        "correct_option":"L'EncyclopÃ©die","explanation":"L'EncyclopÃ©die (1751-1772), dirigÃ©e par Diderot et d'Alembert, diffuse les savoirs et les idÃ©es des LumiÃ¨res."},
    {"id":"475_2","type":"vrai-faux","question":"Voltaire dÃ©fend la tolÃ©rance religieuse notamment dans son TraitÃ© sur la tolÃ©rance.",
     "correct":True,"explanation":"Le TraitÃ© sur la tolÃ©rance (1763) est Ã©crit par Voltaire Ã  la suite de l'affaire Calas."},
    {"id":"475_3","type":"vrai-faux","question":"Rousseau dÃ©veloppe la notion de _____ social dans son Å“uvre Du contrat social.",
     "correct_answer":"contrat","explanation":"Du contrat social (1762) thÃ©orise le pacte entre les individus comme fondement de la sociÃ©tÃ©."},
    {"id":"475_4","type":"qcm","question":"Quelle est la devise des LumiÃ¨res selon Kant ?",
     "options":["LibertÃ©, Ã‰galitÃ©, FraternitÃ©","Ose savoir (Sapere aude)","Je pense donc je suis","L'homme est un loup pour l'homme"],
     "correct_option":"Ose savoir (Sapere aude)","explanation":"Kant dÃ©finit les LumiÃ¨res comme la sortie de l'homme de sa minoritÃ© : 'Ose te servir de ton propre entendement.'"},
    {"id":"475_5","type":"vrai-faux","question":"Le conte philosophique est un genre utilisÃ© par les philosophes des LumiÃ¨res pour transmettre leurs idÃ©es.",
     "correct":True,"explanation":"Voltaire, Diderot et Montesquieu utilisent la fiction (conte, roman, lettres) pour propager les idÃ©es des LumiÃ¨res."},
    {"id":"475_6","type":"vrai-faux","question":"Dans les Lettres persanes, Montesquieu utilise le point de vue d'Ã©trangers pour _____ la sociÃ©tÃ© franÃ§aise.",
     "correct_answer":"critiquer","explanation":"La vision extÃ©rieure du Persan Rica rend visible l'absurditÃ© de certaines institutions franÃ§aises."},
    {"id":"475_7","type":"qcm","question":"Quel philosophe des LumiÃ¨res est le plus associÃ© Ã  la dÃ©fense de la libertÃ© d'expression ?",
     "options":["Rousseau","Voltaire","Montesquieu","Diderot"],
     "correct_option":"Voltaire","explanation":"Voltaire combat toute sa vie l'intolÃ©rance et la censure : 'Je ne suis pas d'accord avec ce que vous dites, mais je me battrai pour que vous puissiez le dire.'"},
    {"id":"475_8","type":"vrai-faux","question":"La littÃ©rature engagÃ©e implique nÃ©cessairement un parti politique prÃ©cis.",
     "correct":False,"explanation":"La littÃ©rature engagÃ©e dÃ©fend des valeurs (libertÃ©, justice) sans se limiter Ã  un parti ; Sartre la thÃ©orise dans Qu'est-ce que la littÃ©rature ?"},
]),

# â”€â”€â”€ 476 â€“ La dissertation littÃ©raire : mÃ©thode â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(476, "La dissertation littÃ©raire : mÃ©thode", "FranÃ§ais", "1Ã¨re", [
    {"id":"476_1","type":"qcm","question":"Quelle est la premiÃ¨re Ã©tape de la mÃ©thode de dissertation ?",
     "options":["RÃ©diger la conclusion","Analyser le sujet et dÃ©gager la problÃ©matique","RÃ©diger l'introduction","Chercher des exemples"],
     "correct_option":"Analyser le sujet et dÃ©gager la problÃ©matique","explanation":"Avant tout plan ou rÃ©daction, il faut comprendre prÃ©cisÃ©ment ce que le sujet demande."},
    {"id":"476_2","type":"vrai-faux","question":"La problÃ©matique d'une dissertation est une question Ã  laquelle le devoir va rÃ©pondre.",
     "correct":True,"explanation":"La problÃ©matique formule l'enjeu intellectuel du sujet sous forme interrogative."},
    {"id":"476_3","type":"vrai-faux","question":"Le plan _____ est le plus adaptÃ© lorsque le sujet formule un jugement Ã  discuter (ex. 'Le roman est-il un simple divertissement ?').",
     "correct_answer":"dialectique","explanation":"Le plan dialectique (thÃ¨se / antithÃ¨se / synthÃ¨se) convient aux sujets d'opinion Ã  nuancer."},
    {"id":"476_4","type":"qcm","question":"Une transition en dissertation sert Ã  :",
     "options":["Introduire un exemple","Bilan de la partie et annonce de la suivante","Formuler la thÃ¨se","Citer un auteur"],
     "correct_option":"Bilan de la partie et annonce de la suivante","explanation":"La transition (ou lien) assure la progression logique du devoir."},
    {"id":"476_5","type":"vrai-faux","question":"L'introduction d'une dissertation se termine par l'annonce du plan.",
     "correct":True,"explanation":"L'introduction comprend : accroche, prÃ©sentation du sujet, problÃ©matique, annonce du plan."},
    {"id":"476_6","type":"vrai-faux","question":"En dissertation, un argument doit toujours Ãªtre illustrÃ© par un _____ tirÃ© des Å“uvres au programme.",
     "correct_answer":"exemple","explanation":"L'exemple littÃ©raire prÃ©cis donne du poids Ã  l'argument et montre la maÃ®trise des textes."},
    {"id":"476_7","type":"qcm","question":"Que doit contenir la conclusion d'une dissertation ?",
     "options":["Un nouveau dÃ©veloppement","Un bilan des arguments et une ouverture","La liste des auteurs citÃ©s","La reformulation de l'introduction"],
     "correct_option":"Un bilan des arguments et une ouverture","explanation":"La conclusion synthÃ©tise la rÃ©ponse Ã  la problÃ©matique, puis ouvre sur une perspective plus large."},
    {"id":"476_8","type":"vrai-faux","question":"Il est acceptable de formuler sa propre opinion au 'je' dans une dissertation au lycÃ©e.",
     "correct":False,"explanation":"On Ã©vite le 'je' en dissertation ; on utilise 'nous' ou des tournures impersonnelles pour maintenir la distance critique."},
]),

# â”€â”€â”€ 477 â€“ Le commentaire littÃ©raire : mÃ©thode â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(477, "Le commentaire littÃ©raire : mÃ©thode", "FranÃ§ais", "1Ã¨re", [
    {"id":"477_1","type":"qcm","question":"Quel est l'objectif principal du commentaire littÃ©raire ?",
     "options":["Raconter le rÃ©sumÃ© du texte","Analyser comment le texte produit du sens et de l'effet","Ã‰mettre un jugement personnel","Comparer plusieurs auteurs"],
     "correct_option":"Analyser comment le texte produit du sens et de l'effet","explanation":"Le commentaire analyse les procÃ©dÃ©s stylistiques et leur effet sur le lecteur."},
    {"id":"477_2","type":"vrai-faux","question":"La paraphrase (reformulation du texte) est valorisÃ©e dans un commentaire littÃ©raire.",
     "correct":False,"explanation":"La paraphrase est Ã  Ã©viter ; il faut analyser les procÃ©dÃ©s, pas simplement reformuler."},
    {"id":"477_3","type":"vrai-faux","question":"Dans un commentaire, l'axe de lecture est parfois appelÃ© _____ : il oriente l'analyse d'une partie.",
     "correct_answer":"centre d'intÃ©rÃªt","explanation":"Chaque partie dÃ©veloppe un centre d'intÃ©rÃªt (ou axe) qui Ã©claire un aspect du texte."},
    {"id":"477_4","type":"qcm","question":"Un relevÃ© stylistique sans interprÃ©tation est :",
     "options":["Une analyse complÃ¨te","Insuffisant : il faut toujours interprÃ©ter l'effet","ValorisÃ© par les correcteurs","Une paraphrase"],
     "correct_option":"Insuffisant : il faut toujours interprÃ©ter l'effet","explanation":"Identifier un procÃ©dÃ© n'est que la premiÃ¨re Ã©tape ; il faut expliquer pourquoi l'auteur l'utilise et quel effet il produit."},
    {"id":"477_5","type":"vrai-faux","question":"L'introduction d'un commentaire doit situer le texte dans son contexte et annoncer les axes d'analyse.",
     "correct":True,"explanation":"L'introduction : accroche, prÃ©sentation de l'auteur/Å“uvre/extrait, problÃ©matique, annonce du plan."},
    {"id":"477_6","type":"vrai-faux","question":"Le mouvement du texte dÃ©signe la _____ interne du passage, c'est-Ã -dire comment il progresse.",
     "correct_answer":"progression","explanation":"Identifier le mouvement permet de dÃ©gager les Ã©tapes de l'extrait avant de construire le plan."},
    {"id":"477_7","type":"qcm","question":"Dans quelle partie de la correction va-t-on insÃ©rer des citations courtes du texte ?",
     "options":["Uniquement en introduction","Dans chaque sous-partie pour appuyer les analyses","Uniquement en conclusion","Jamais"],
     "correct_option":"Dans chaque sous-partie pour appuyer les analyses","explanation":"Les citations (entre guillemets, avec rÃ©fÃ©rence) sont la preuve textuelle de chaque argument."},
    {"id":"477_8","type":"vrai-faux","question":"La conclusion d'un commentaire littÃ©raire peut proposer une ouverture vers une autre Å“uvre.",
     "correct":True,"explanation":"L'ouverture met en perspective l'analyse en reliant le texte Ã  une autre Å“uvre, un thÃ¨me, une Ã©poque."},
]),

# â”€â”€â”€ 478 â€“ Grammaire avancÃ©e : syntaxe et subordination â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(478, "Grammaire avancÃ©e : syntaxe et subordination", "FranÃ§ais", "1Ã¨re", [
    {"id":"478_1","type":"qcm","question":"Quelle proposition est une subordonnÃ©e relative :",
     "options":["Parce qu'il pleuvait","Que je lis chaque soir","Bien qu'il soit fatiguÃ©","DÃ¨s que le soleil se lÃ¨ve"],
     "correct_option":"Que je lis chaque soir","explanation":"'Que je lis chaque soir' est une relative (introduite par un pronom relatif) qui complÃ¨te un nom."},
    {"id":"478_2","type":"vrai-faux","question":"Une proposition subordonnÃ©e conjonctive complÃ©tive est introduite par 'que'.",
     "correct":True,"explanation":"Ex. : 'Il pense que tu as raison.' â€“ 'que tu as raison' est une complÃ©tive, COD du verbe 'penser'."},
    {"id":"478_3","type":"vrai-faux","question":"Dans 'Bien qu'il travaille dur, il Ã©choue', la subordonnÃ©e exprime une relation de _____.",
     "correct_answer":"concession","explanation":"'Bien que' introduit une subordonnÃ©e circonstancielle de concession."},
    {"id":"478_4","type":"qcm","question":"Quel est le mode verbal utilisÃ© dans la subordonnÃ©e aprÃ¨s 'bien que' ?",
     "options":["Indicatif","Conditionnel","Subjonctif","Infinitif"],
     "correct_option":"Subjonctif","explanation":"Les conjonctions de concession (bien que, quoique) exigent le subjonctif."},
    {"id":"478_5","type":"vrai-faux","question":"La proposition participiale est une subordonnÃ©e sans conjonction de subordination.",
     "correct":True,"explanation":"La proposition participiale (ex. 'La nuit tombant, nous rentrÃ¢mes') est construite autour d'un participe avec son sujet propre."},
    {"id":"478_6","type":"vrai-faux","question":"Dans la phrase complexe, la proposition principale est celle dont aucune autre ne dÃ©pend ; elle est syntaxiquement _____.",
     "correct_answer":"indÃ©pendante","explanation":"La principale n'est subordonnÃ©e Ã  aucune autre ; les subordonnÃ©es en dÃ©pendent."},
    {"id":"478_7","type":"qcm","question":"Quelle conjonction introduit une subordonnÃ©e de but ?",
     "options":["Parce que","Pendant que","Pour que","Bien que"],
     "correct_option":"Pour que","explanation":"'Pour que' + subjonctif introduit une subordonnÃ©e circonstancielle de but."},
    {"id":"478_8","type":"vrai-faux","question":"Le discours indirect transforme les dÃ©ictiques (je, ici, maintenant) du discours direct.",
     "correct":True,"explanation":"En passant au discours indirect, 'je' devient 'il/elle', 'ici' devient 'lÃ ', 'maintenant' devient 'alors', etc."},
]),

# â”€â”€â”€ 479 â€“ Orthographe : accord du participe passÃ© â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(479, "Orthographe : accord du participe passÃ©", "FranÃ§ais", "1Ã¨re", [
    {"id":"479_1","type":"qcm","question":"Avec l'auxiliaire 'avoir', le participe passÃ© s'accorde :",
     "options":["Toujours avec le sujet","Avec le COD si celui-ci est placÃ© avant le verbe","Toujours avec le COI","Jamais"],
     "correct_option":"Avec le COD si celui-ci est placÃ© avant le verbe","explanation":"RÃ¨gle de base : PP avec avoir s'accorde avec le COD antÃ©posÃ© (ex. : Les lettres qu'il a Ã©crites)."},
    {"id":"479_2","type":"vrai-faux","question":"'Nous nous sommes parlÃ©' : le participe reste invariable car 'se' est COI.",
     "correct":True,"explanation":"Le verbe 'parler' est transitif indirect (parler Ã  qqn) ; 'se' est COI, donc pas d'accord."},
    {"id":"479_3","type":"vrai-faux","question":"Dans 'Elle s'est lavÃ©e', le participe s'accorde parce que 'se' est _____ du verbe.",
     "correct_answer":"COD","explanation":"'Se laver' : elle a lavÃ© qui ? â†’ 'se' = elle-mÃªme â†’ COD antÃ©posÃ© â†’ accord."},
    {"id":"479_4","type":"qcm","question":"Quel est l'accord dans 'Les fleurs qu'il a cueilli___' ?",
     "options":["Cueilli (invariable)","Cueillie","Cueillis","Cueillies"],
     "correct_option":"Cueillies","explanation":"COD 'les fleurs' (fÃ©minin pluriel) est placÃ© avant â†’ accord : cueillies."},
    {"id":"479_5","type":"vrai-faux","question":"Avec l'auxiliaire 'Ãªtre', le participe passÃ© s'accorde toujours avec le sujet.",
     "correct":True,"explanation":"Ex. : Elles sont arrivÃ©es tÃ´t. Sauf exceptions (verbes pronominaux avec COD postposÃ©)."},
    {"id":"479_6","type":"vrai-faux","question":"Les participes passÃ©s des verbes pronominaux suivent la rÃ¨gle de l'accord avec l'auxiliaire _____ pour le choix de l'auxiliaire.",
     "correct_answer":"Ãªtre","explanation":"Les verbes pronominaux se conjuguent toujours avec 'Ãªtre' aux temps composÃ©s."},
    {"id":"479_7","type":"qcm","question":"Dans 'La chanson qu'elle a chantÃ©___', le participe est :",
     "options":["ChantÃ©","ChantÃ©e","ChantÃ©s","ChantÃ©es"],
     "correct_option":"ChantÃ©e","explanation":"COD 'la chanson' (fÃ©m. sing.) placÃ© avant â†’ chantÃ© â†’ e â†’ chantÃ©e."},
    {"id":"479_8","type":"vrai-faux","question":"Le participe passÃ© employÃ© sans auxiliaire s'accorde comme un adjectif.",
     "correct":True,"explanation":"Ex. : Des lettres bien rÃ©digÃ©es (Ã©pithÃ¨te) â†’ accord avec le nom."},
]),

# â”€â”€â”€ 480 â€“ Conjugaison : subjonctif et modes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(480, "Conjugaison : subjonctif et conditionnel", "FranÃ§ais", "1Ã¨re", [
    {"id":"480_1","type":"qcm","question":"Quand emploie-t-on principalement le subjonctif prÃ©sent ?",
     "options":["Pour les faits certains","Dans les subordonnÃ©es aprÃ¨s des verbes de doute, volontÃ©, sentiment","Pour des hypothÃ¨ses rÃ©elles","Dans les rÃ©cits au passÃ©"],
     "correct_option":"Dans les subordonnÃ©es aprÃ¨s des verbes de doute, volontÃ©, sentiment","explanation":"Verbes de volontÃ© (vouloir), doute (douter), sentiment (regretter) imposent le subjonctif."},
    {"id":"480_2","type":"vrai-faux","question":"Le conditionnel prÃ©sent peut exprimer un souhait ou une hypothÃ¨se irrÃ©elle.",
     "correct":True,"explanation":"Ex. : 'Je voudrais partir' (souhait) ; 'S'il venait, je serais content' (hypothÃ¨se irrÃ©elle dans l'apodose)."},
    {"id":"480_3","type":"vrai-faux","question":"Dans une pÃ©riode conditionnelle au passÃ©, la structure est : 'Si + plus-que-parfait, _____ passÃ©.'",
     "correct_answer":"conditionnel","explanation":"Ex. : 'Si tu avais Ã©tudiÃ©, tu aurais rÃ©ussi.' â€“ conditionnel passÃ© dans l'apodose."},
    {"id":"480_4","type":"qcm","question":"Quelle est la conjugaison correcte du verbe 'Ãªtre' au subjonctif prÃ©sent, 3e personne du singulier ?",
     "options":["Est","Soit","Ã‰tait","Serait"],
     "correct_option":"Soit","explanation":"Subjonctif prÃ©sent d'Ãªtre : que je sois, que tu sois, qu'il soitâ€¦"},
    {"id":"480_5","type":"vrai-faux","question":"L'imparfait du subjonctif est encore couramment utilisÃ© Ã  l'oral aujourd'hui.",
     "correct":False,"explanation":"L'imparfait du subjonctif ('qu'il fÃ®t', 'qu'il eÃ»t') est perÃ§u comme archaÃ¯que et rÃ©servÃ© au style soutenu Ã©crit."},
    {"id":"480_6","type":"vrai-faux","question":"Le mode _____ exprime une action comme rÃ©elle, certaine ou probable ; c'est le mode de la rÃ©alitÃ©.",
     "correct_answer":"indicatif","explanation":"L'indicatif est le mode de l'assertion ; il s'oppose au subjonctif (mode du virtuel)."},
    {"id":"480_7","type":"qcm","question":"Quelle construction exige l'infinitif plutÃ´t que le subjonctif ?",
     "options":["Sujets diffÃ©rents dans les deux propositions","MÃªme sujet dans les deux propositions","Verbe de sentiment","Locution conjonctive de but"],
     "correct_option":"MÃªme sujet dans les deux propositions","explanation":"Si le sujet est le mÃªme : 'Il veut partir' (infinitif) ; sujets diffÃ©rents : 'Il veut qu'elle parte' (subjonctif)."},
    {"id":"480_8","type":"vrai-faux","question":"Le conditionnel est parfois appelÃ© 'mode' bien qu'il soit souvent classÃ© parmi les temps de l'indicatif.",
     "correct":True,"explanation":"Selon les grammaires, le conditionnel est tantÃ´t un mode Ã  part, tantÃ´t un temps de l'indicatif (futur du passÃ©)."},
]),

# â”€â”€â”€ 481 â€“ Vocabulaire littÃ©raire et figures de style â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(481, "Vocabulaire littÃ©raire et figures de style", "FranÃ§ais", "1Ã¨re", [
    {"id":"481_1","type":"qcm","question":"Quelle figure de style compare deux Ã©lÃ©ments sans outil de comparaison ?",
     "options":["Comparaison","MÃ©taphore","MÃ©tonymie","Synecdoque"],
     "correct_option":"MÃ©taphore","explanation":"La mÃ©taphore est une comparaison implicite sans 'comme', 'tel que', etc."},
    {"id":"481_2","type":"vrai-faux","question":"L'hyperbole consiste Ã  attÃ©nuer une rÃ©alitÃ© pour mÃ©nager la sensibilitÃ©.",
     "correct":False,"explanation":"L'hyperbole exagÃ¨re (ex. : 'mourir de rire') ; c'est l'euphÃ©misme qui attÃ©nue."},
    {"id":"481_3","type":"vrai-faux","question":"La _____ consiste Ã  rÃ©pÃ©ter le mÃªme mot ou la mÃªme construction en dÃ©but de vers ou de phrase.",
     "correct_answer":"anaphore","explanation":"L'anaphore (ex. 'Je t'aimeâ€¦ Je t'aimeâ€¦') crÃ©e un effet d'insistance et de rythme."},
    {"id":"481_4","type":"qcm","question":"Quelle figure dÃ©signe une partie pour le tout (ex. : 'une voile' pour 'un bateau') ?",
     "options":["MÃ©taphore","Synecdoque","Oxymore","Litote"],
     "correct_option":"Synecdoque","explanation":"La synecdoque prend la partie pour le tout ou inversement."},
    {"id":"481_5","type":"vrai-faux","question":"L'ironie exprime le contraire de ce qu'elle dit, souvent pour critiquer.",
     "correct":True,"explanation":"Ex. : 'Quelle brillante idÃ©e !' (pour dire que l'idÃ©e est mauvaise) â†’ ironie."},
    {"id":"481_6","type":"vrai-faux","question":"L'association de deux termes contradictoires dans une mÃªme expression s'appelle un _____.",
     "correct_answer":"oxymore","explanation":"Ex. : 'obscure clartÃ©' (Corneille) ; 'douce violence' â†’ oxymore."},
    {"id":"481_7","type":"qcm","question":"Quelle figure consiste Ã  exprimer beaucoup en disant peu (ex. : 'ce n'est pas mal' pour 'c'est trÃ¨s bien') ?",
     "options":["Hyperbole","EuphÃ©misme","Litote","Antiphrase"],
     "correct_option":"Litote","explanation":"La litote dit moins pour laisser entendre plus : 'Je ne le hais point' (ChimÃ¨ne dans Le Cid) = 'Je l'aime'."},
    {"id":"481_8","type":"vrai-faux","question":"La prosopopÃ©e est une figure qui fait parler un absent, un mort, une abstraction ou un objet.",
     "correct":True,"explanation":"Ex. : 'Si la mer pouvait parlerâ€¦' â†’ prosopopÃ©e."},
]),

# â”€â”€â”€ 482 â€“ Les mouvements littÃ©raires : Humanisme, Baroque, Classicisme â”€â”€â”€â”€â”€â”€â”€
(482, "Mouvements littÃ©raires : Humanisme, Baroque, Classicisme", "FranÃ§ais", "1Ã¨re", [
    {"id":"482_1","type":"qcm","question":"Quel mouvement du XVIe siÃ¨cle redÃ©couvre les textes antiques grecs et latins ?",
     "options":["Le Baroque","Le Classicisme","L'Humanisme","Le Romantisme"],
     "correct_option":"L'Humanisme","explanation":"L'Humanisme (XVIe s.) place l'Homme au centre et redÃ©couvre les Anciens (studia humanitatis)."},
    {"id":"482_2","type":"vrai-faux","question":"Le Baroque est caractÃ©risÃ© par l'ordre, la mesure et la sobriÃ©tÃ©.",
     "correct":False,"explanation":"Le Baroque (fin XVIe-dÃ©but XVIIe) valorise le mouvement, l'excÃ¨s, l'ornement et l'instabilitÃ©."},
    {"id":"482_3","type":"vrai-faux","question":"La devise humaniste 'Je suis homme et rien d'humain ne m'est Ã©tranger' est associÃ©e Ã  l'idÃ©al de l'_____ universel.",
     "correct_answer":"homme","explanation":"L'humaniste aspire Ã  l'honnÃªte homme universel, curieux de tout savoir humain."},
    {"id":"482_4","type":"qcm","question":"Quel auteur est le reprÃ©sentant majeur du Classicisme dramatique ?",
     "options":["Ronsard","Montaigne","Racine","Victor Hugo"],
     "correct_option":"Racine","explanation":"Racine (Andromaque, PhÃ¨dre) est l'un des grands tragÃ©diens classiques du XVIIe siÃ¨cle."},
    {"id":"482_5","type":"vrai-faux","question":"Le Classicisme franÃ§ais se dÃ©veloppe principalement sous Louis XIV.",
     "correct":True,"explanation":"Le Classicisme (1660-1685) est l'esthÃ©tique du Grand SiÃ¨cle, soutenu par la monarchie absolue."},
    {"id":"482_6","type":"vrai-faux","question":"Ronsard, poÃ¨te de la PlÃ©iade, invite la jeune fille Ã  profiter de la vie dans 'Cueillez dÃ¨s aujourd'hui les _____ de la vie'.",
     "correct_answer":"roses","explanation":"Ronsard, 'Ode Ã  Cassandre' : 'Cueillez dÃ¨s aujourd'hui les roses de la vie' â†’ carpe diem."},
    {"id":"482_7","type":"qcm","question":"Le Baroque au thÃ©Ã¢tre se manifeste notamment par :",
     "options":["La rÃ¨gle stricte des trois unitÃ©s","Les tragi-comÃ©dies et le thÃ©Ã¢tre dans le thÃ©Ã¢tre","L'unitÃ© de ton","La sÃ©paration stricte des genres"],
     "correct_option":"Les tragi-comÃ©dies et le thÃ©Ã¢tre dans le thÃ©Ã¢tre","explanation":"Le Baroque mÃªle les genres et joue avec les illusions thÃ©Ã¢trales (Le Vrai Saint-Genest de Rotrou)."},
    {"id":"482_8","type":"vrai-faux","question":"Descartes et son 'Je pense donc je suis' participe de l'esprit du Classicisme rationnel du XVIIe siÃ¨cle.",
     "correct":True,"explanation":"La philosophie cartÃ©sienne (raison, mÃ©thode) est parallÃ¨le Ã  l'esthÃ©tique classique (ordre, clartÃ©)."},
]),

# â”€â”€â”€ 483 â€“ Les mouvements littÃ©raires : Romantisme et RÃ©alisme â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(483, "Mouvements littÃ©raires : Romantisme et RÃ©alisme", "FranÃ§ais", "1Ã¨re", [
    {"id":"483_1","type":"qcm","question":"Quel est le texte manifeste du Romantisme franÃ§ais ?",
     "options":["La PrÃ©face de Cromwell de Victor Hugo","Le Roman expÃ©rimental de Zola","L'Art poÃ©tique de Boileau","Du Bellay, DÃ©fense et illustration"],
     "correct_option":"La PrÃ©face de Cromwell de Victor Hugo","explanation":"La PrÃ©face de Cromwell (1827) de Hugo rompt avec les rÃ¨gles classiques et thÃ©orise le mÃ©lange des genres."},
    {"id":"483_2","type":"vrai-faux","question":"Le Romantisme valorise la raison et l'ordre au dÃ©triment des Ã©motions.",
     "correct":False,"explanation":"Le Romantisme exalte les Ã©motions, l'imagination, le moi et la nature contre le rationalisme des LumiÃ¨res."},
    {"id":"483_3","type":"vrai-faux","question":"Le 'mal du siÃ¨cle' romantique dÃ©signe une mÃ©lancolie et un sentiment de _____ ressentis par la gÃ©nÃ©ration post-rÃ©volutionnaire.",
     "correct_answer":"dÃ©senchantement","explanation":"Musset, dans La Confession d'un enfant du siÃ¨cle, dÃ©crit ce malaise existentiel de la gÃ©nÃ©ration de 1820."},
    {"id":"483_4","type":"qcm","question":"Quel est le chef-d'Å“uvre du Romantisme de Victor Hugo ?",
     "options":["Germinal","Les MisÃ©rables","Madame Bovary","Le Rouge et le Noir"],
     "correct_option":"Les MisÃ©rables","explanation":"Les MisÃ©rables (1862) est le grand roman social de Hugo, incarnant les idÃ©aux romantiques et humanistes."},
    {"id":"483_5","type":"vrai-faux","question":"Le RÃ©alisme cherche Ã  reprÃ©senter le monde tel qu'il est, sans embellissement.",
     "correct":True,"explanation":"Le RÃ©alisme (Balzac, Flaubert, Maupassant) vise la vÃ©ritÃ© sociale et psychologique."},
    {"id":"483_6","type":"vrai-faux","question":"Balzac regroupe l'ensemble de ses romans sous le titre _____ pour crÃ©er une fresque sociale de la France.",
     "correct_answer":"La ComÃ©die humaine","explanation":"La ComÃ©die humaine (90+ romans) est la grande entreprise rÃ©aliste de Balzac."},
    {"id":"483_7","type":"qcm","question":"La technique du 'retour des personnages' est une invention de :",
     "options":["Zola","Flaubert","Balzac","Maupassant"],
     "correct_option":"Balzac","explanation":"Balzac fait revenir les mÃªmes personnages d'un roman Ã  l'autre pour crÃ©er l'illusion d'un monde cohÃ©rent."},
    {"id":"483_8","type":"vrai-faux","question":"Le RÃ©alisme littÃ©raire est un mouvement essentiellement du XXe siÃ¨cle.",
     "correct":False,"explanation":"Le RÃ©alisme se dÃ©veloppe au XIXe siÃ¨cle (1830-1880), avant de laisser place au Naturalisme."},
]),

# â”€â”€â”€ 484 â€“ Les mouvements littÃ©raires : Symbolisme et SurrÃ©alisme â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(484, "Mouvements littÃ©raires : Symbolisme et SurrÃ©alisme", "FranÃ§ais", "1Ã¨re", [
    {"id":"484_1","type":"qcm","question":"Quel manifeste fonde officiellement le SurrÃ©alisme ?",
     "options":["Le Manifeste du Futurisme","Le Manifeste du SurrÃ©alisme d'AndrÃ© Breton (1924)","La PrÃ©face de Cromwell","L'Art poÃ©tique de Verlaine"],
     "correct_option":"Le Manifeste du SurrÃ©alisme d'AndrÃ© Breton (1924)","explanation":"Breton publie le premier Manifeste du SurrÃ©alisme en 1924, dÃ©finissant l'Ã©criture automatique et l'exploration de l'inconscient."},
    {"id":"484_2","type":"vrai-faux","question":"L'Ã©criture automatique surrÃ©aliste consiste Ã  Ã©crire sans contrÃ´le de la raison.",
     "correct":True,"explanation":"L'Ã©criture automatique vise Ã  libÃ©rer le flux de l'inconscient en supprimant la censure rationnelle."},
    {"id":"484_3","type":"vrai-faux","question":"Le Symbolisme refuse de nommer les choses directement et prÃ©fÃ¨re les _____.",
     "correct_answer":"suggÃ©rer","explanation":"'Peindre non la chose mais l'effet qu'elle produit' (MallarmÃ©) : le symbole suggÃ¨re plutÃ´t qu'il n'affirme."},
    {"id":"484_4","type":"qcm","question":"Quel poÃ¨te est associÃ© Ã  la fois au Symbolisme et aux 'poÃ¨tes maudits' ?",
     "options":["Victor Hugo","Paul Verlaine","Lamartine","Alfred de Vigny"],
     "correct_option":"Paul Verlaine","explanation":"Verlaine est prÃ©sentÃ© par lui-mÃªme comme un 'poÃ¨te maudit' ; son Å“uvre est emblÃ©matique du Symbolisme musical."},
    {"id":"484_5","type":"vrai-faux","question":"Le SurrÃ©alisme s'intÃ©resse aux rÃªves et Ã  l'inconscient, influencÃ© par les thÃ©ories de Freud.",
     "correct":True,"explanation":"Breton est fascinÃ© par la psychanalyse freudienne et cherche Ã  libÃ©rer le 'fonctionnement rÃ©el de la pensÃ©e'."},
    {"id":"484_6","type":"vrai-faux","question":"Paul Ã‰luard, Louis Aragon et AndrÃ© Breton sont des figures majeures du mouvement _____.",
     "correct_answer":"SurrÃ©alisme","explanation":"Ces trois poÃ¨tes sont les piliers du groupe surrÃ©aliste parisien."},
    {"id":"484_7","type":"qcm","question":"Quelle technique surrÃ©aliste consiste Ã  Ã©crire un texte Ã  plusieurs mains sans voir ce que l'autre a Ã©crit ?",
     "options":["Cadavre exquis","Collage","Ã‰criture automatique","PoÃ¨me en prose"],
     "correct_option":"Cadavre exquis","explanation":"Le cadavre exquis (ex. 'Le cadavre exquis boira le vin nouveau') est un jeu collectif surrÃ©aliste."},
    {"id":"484_8","type":"vrai-faux","question":"StÃ©phane MallarmÃ© est un poÃ¨te symboliste dont l'Å“uvre se caractÃ©rise par la clartÃ© et la simplicitÃ©.",
     "correct":False,"explanation":"MallarmÃ© est rÃ©putÃ© pour son hermÃ©tisme et sa recherche d'un langage poÃ©tique pur, difficile d'accÃ¨s."},
]),

# â”€â”€â”€ 485 â€“ L'expression Ã©crite : la synthÃ¨se de documents â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(485, "L'expression Ã©crite : la synthÃ¨se de documents", "FranÃ§ais", "1Ã¨re", [
    {"id":"485_1","type":"qcm","question":"Qu'est-ce qu'une synthÃ¨se de documents ?",
     "options":["Un rÃ©sumÃ© de chaque document sÃ©parÃ©ment","Un texte qui articule les idÃ©es de plusieurs documents autour d'une problÃ©matique commune","Un commentaire littÃ©raire","Un texte d'opinion personnel"],
     "correct_option":"Un texte qui articule les idÃ©es de plusieurs documents autour d'une problÃ©matique commune","explanation":"La synthÃ¨se regroupe et organise les idÃ©es des documents sans les juxtaposer ni donner son avis personnel."},
    {"id":"485_2","type":"vrai-faux","question":"Dans une synthÃ¨se, on peut donner son opinion personnelle.",
     "correct":False,"explanation":"La synthÃ¨se est un exercice objectif : on reformule les idÃ©es des auteurs sans les commenter ni y adhÃ©rer."},
    {"id":"485_3","type":"vrai-faux","question":"Dans une synthÃ¨se, on attribue chaque idÃ©e Ã  son auteur Ã  l'aide d'un verbe introducteur ou d'une _____.",
     "correct_answer":"rÃ©fÃ©rence","explanation":"Ex. : 'Selon (doc. 1)â€¦', 'L'auteur du doc. 2 montre queâ€¦' â†’ rÃ©fÃ©rence aux sources."},
    {"id":"485_4","type":"qcm","question":"Quelle est la structure habituelle d'une synthÃ¨se rÃ©ussie ?",
     "options":["RÃ©sumÃ© doc. 1, rÃ©sumÃ© doc. 2, rÃ©sumÃ© doc. 3","Introduction (problÃ©matique) + dÃ©veloppement thÃ©matique + conclusion","Introduction + thÃ¨se + antithÃ¨se + synthÃ¨se","RÃ©sumÃ© + commentaire + opinion"],
     "correct_option":"Introduction (problÃ©matique) + dÃ©veloppement thÃ©matique + conclusion","explanation":"La synthÃ¨se suit un plan thÃ©matique (non documentaire) : les idÃ©es sont regroupÃ©es par thÃ¨mes, pas par document."},
    {"id":"485_5","type":"vrai-faux","question":"La synthÃ¨se peut comporter de longues citations extraites des documents.",
     "correct":False,"explanation":"Les citations sont rÃ©duites au minimum ; on reformule (reformulation fidÃ¨le) plutÃ´t qu'on ne cite."},
    {"id":"485_6","type":"vrai-faux","question":"La _____ de documents commence par dÃ©finir une problÃ©matique commune Ã  l'ensemble du corpus.",
     "correct_answer":"synthÃ¨se","explanation":"Avant tout, on interroge : Quel est le problÃ¨me/thÃ¨me commun Ã  tous ces documents ?"},
    {"id":"485_7","type":"qcm","question":"Le plan documentaire (doc. 1, doc. 2â€¦) est Ã  Ã©viter dans une synthÃ¨se car :",
     "options":["Il rend la synthÃ¨se trop longue","Il juxtapose les documents sans les articuler","Il est difficile Ã  mettre en Å“uvre","Il exige trop de citations"],
     "correct_option":"Il juxtapose les documents sans les articuler","explanation":"La synthÃ¨se doit croiser les documents, pas les prÃ©senter l'un aprÃ¨s l'autre."},
    {"id":"485_8","type":"vrai-faux","question":"La synthÃ¨se de documents fait partie des Ã©preuves anticipÃ©es de FranÃ§ais au baccalaurÃ©at.",
     "correct":False,"explanation":"L'Ã©preuve anticipÃ©e de FranÃ§ais au bac comprend la lecture (commentaire ou dissertation) et l'Ã©criture personnelle, non la synthÃ¨se qui est propre au BTS/classes prÃ©pas."},
]),

# â”€â”€â”€ 486 â€“ L'Ã©criture d'invention â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(486, "L'Ã©criture d'invention", "FranÃ§ais", "1Ã¨re", [
    {"id":"486_1","type":"qcm","question":"L'Ã©criture d'invention Ã  l'EAF demande d'Ã©crire :",
     "options":["Un rÃ©sumÃ© du corpus","Un texte original en respectant des contraintes de genre et de tonalitÃ©","Une synthÃ¨se de documents","Un commentaire critique"],
     "correct_option":"Un texte original en respectant des contraintes de genre et de tonalitÃ©","explanation":"L'Ã©criture d'invention Ã©value la crÃ©ativitÃ© et la maÃ®trise des codes gÃ©nÃ©riques (argumentation, rÃ©cit, dialogueâ€¦)."},
    {"id":"486_2","type":"vrai-faux","question":"Dans un texte d'invention argumentatif, il est inutile de respecter la logique de l'argumentation.",
     "correct":False,"explanation":"MÃªme crÃ©ative, l'Ã©criture d'invention argumentative doit Ãªtre cohÃ©rente et structurÃ©e."},
    {"id":"486_3","type":"vrai-faux","question":"Quand le sujet demande 'd'Ã©crire la suite du texte', on doit respecter le registre, le _____ et les personnages.",
     "correct_answer":"ton","explanation":"La cohÃ©rence de ton, de style et de personnages garantit la vraisemblance de la suite."},
    {"id":"486_4","type":"qcm","question":"Pour un sujet d'invention 'Ã‰crivez un discours engagÃ©', quelle tonalitÃ© est attendue ?",
     "options":["Humoristique","PathÃ©tique et/ou polÃ©mique","Ã‰pique","Fantastique"],
     "correct_option":"PathÃ©tique et/ou polÃ©mique","explanation":"Un discours engagÃ© use souvent du registre pathÃ©tique (Ã©mouvoir) et polÃ©mique (convaincre/persuader)."},
    {"id":"486_5","type":"vrai-faux","question":"La maÃ®trise des procÃ©dÃ©s stylistiques (figures de style, rythme) est valorisÃ©e dans l'Ã©criture d'invention.",
     "correct":True,"explanation":"L'Ã©criture d'invention Ã©value aussi la richesse stylistique et la maÃ®trise de la langue."},
    {"id":"486_6","type":"vrai-faux","question":"Si le sujet demande de rÃ©diger une lettre fictive, on respecte les codes _____ de la lettre (date, formule d'appel, signature).",
     "correct_answer":"formels","explanation":"Les codes formels du genre (lettre, discours, article) sont Ã©valuÃ©s dans l'Ã©criture d'invention."},
    {"id":"486_7","type":"qcm","question":"Qu'est-ce que le registre Ã©pique dans une Ã©criture d'invention ?",
     "options":["L'expression de la tristesse","La grandeur, l'hÃ©roÃ¯sme, l'ampleur narrative","L'ironie et le comique","La peur et l'angoisse"],
     "correct_option":"La grandeur, l'hÃ©roÃ¯sme, l'ampleur narrative","explanation":"Le registre Ã©pique Ã©voque les grandes actions, les hÃ©ros, les batailles, avec un style ample et solennel."},
    {"id":"486_8","type":"vrai-faux","question":"Il est possible de mÃªler registres comique et pathÃ©tique dans un texte d'invention.",
     "correct":True,"explanation":"Hugo mÃªle justement le sublime et le grotesque ; les mÃ©langes de registres peuvent Ãªtre artistiquement rÃ©ussis."},
]),

# â”€â”€â”€ 487 â€“ Victor Hugo : Å“uvres et thÃ¨mes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(487, "Victor Hugo : Å“uvres et thÃ¨mes majeurs", "FranÃ§ais", "1Ã¨re", [
    {"id":"487_1","type":"qcm","question":"Dans quel recueil Hugo exprime-t-il son deuil aprÃ¨s la mort de sa fille LÃ©opoldine ?",
     "options":["Les Contemplations","Les ChÃ¢timents","La LÃ©gende des siÃ¨cles","Les Feuilles d'automne"],
     "correct_option":"Les Contemplations","explanation":"Les Contemplations (1856) comprennent le livre IV ('Pauca meae') dÃ©diÃ© Ã  LÃ©opoldine."},
    {"id":"487_2","type":"vrai-faux","question":"Hugo a Ã©tÃ© exilÃ© en Belgique et aux Ã®les anglo-normandes pendant le Second Empire.",
     "correct":True,"explanation":"OpposÃ© Ã  Louis-NapolÃ©on Bonaparte, Hugo s'exile Ã  Bruxelles, Jersey et Guernesey (1851-1870)."},
    {"id":"487_3","type":"vrai-faux","question":"Dans Notre-Dame de Paris, le personnage de _____ illustre la beautÃ© intÃ©rieure malgrÃ© la laideur physique.",
     "correct_answer":"Quasimodo","explanation":"Quasimodo, bossu et sourd, aime Esmeralda d'un amour pur ; Hugo illustre le contraste beautÃ©/laideur."},
    {"id":"487_4","type":"qcm","question":"Les MisÃ©rables traite principalement de :",
     "options":["La guerre de NapolÃ©on","La misÃ¨re sociale et la rÃ©demption humaine","L'amour courtois mÃ©diÃ©val","La RÃ©volution amÃ©ricaine"],
     "correct_option":"La misÃ¨re sociale et la rÃ©demption humaine","explanation":"Jean Valjean, ex-forÃ§at, cherche sa rÃ©demption dans une sociÃ©tÃ© injuste."},
    {"id":"487_5","type":"vrai-faux","question":"Victor Hugo est Ã  la fois poÃ¨te, dramaturge et romancier.",
     "correct":True,"explanation":"Hugo est l'un des rares auteurs Ã  exceller dans tous les genres : poÃ©sie (Les Contemplations), thÃ©Ã¢tre (Hernani), roman (Notre-Dame de Paris, Les MisÃ©rables)."},
    {"id":"487_6","type":"vrai-faux","question":"Le discours d'Hugo 'DÃ©truisez la misÃ¨re !' est un exemple de littÃ©rature _____.",
     "correct_answer":"engagÃ©e","explanation":"Hugo se bat toute sa vie pour les pauvres, l'abolition de la peine de mort et les droits des enfants."},
    {"id":"487_7","type":"qcm","question":"Quelle piÃ¨ce de Hugo provoque la fameuse 'bataille d'Hernani' en 1830 ?",
     "options":["Ruy Blas","LucrÃ¨ce Borgia","Hernani","Marion de Lorme"],
     "correct_option":"Hernani","explanation":"La premiÃ¨re d'Hernani (1830) oppose classiques et romantiques dans une querelle cÃ©lÃ¨bre."},
    {"id":"487_8","type":"vrai-faux","question":"Les ChÃ¢timents est un recueil de pamphlets poÃ©tiques contre NapolÃ©on III.",
     "correct":True,"explanation":"Les ChÃ¢timents (1853) sont des poÃ¨mes satiriques violents contre Louis-NapolÃ©on Bonaparte, Ã©crits depuis l'exil."},
]),

# â”€â”€â”€ 488 â€“ Baudelaire : Les Fleurs du Mal â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(488, "Baudelaire et Les Fleurs du Mal", "FranÃ§ais", "1Ã¨re", [
    {"id":"488_1","type":"qcm","question":"En quelle annÃ©e Les Fleurs du Mal sont-elles publiÃ©es ?",
     "options":["1820","1857","1880","1900"],
     "correct_option":"1857","explanation":"Les Fleurs du Mal paraissent en 1857 ; Baudelaire est condamnÃ© pour immoralitÃ© et doit supprimer 6 piÃ¨ces."},
    {"id":"488_2","type":"vrai-faux","question":"Les Fleurs du Mal sont organisÃ©es en sections thÃ©matiques.",
     "correct":True,"explanation":"Le recueil est structurÃ© en 6 sections : Spleen et IdÃ©al, Tableaux parisiens, Le Vin, Fleurs du Mal, RÃ©volte, La Mort."},
    {"id":"488_3","type":"vrai-faux","question":"Dans Les Fleurs du Mal, la tension centrale oppose le _____ (aspiration Ã  l'Ã©lÃ©vation) au spleen (dÃ©pression).",
     "correct_answer":"idÃ©al","explanation":"Spleen et IdÃ©al est la premiÃ¨re et la plus longue section : l'idÃ©al est l'aspiration Ã  la beautÃ© absolue."},
    {"id":"488_4","type":"qcm","question":"Quelle image baudelairienne reprÃ©sente le poÃ¨te incompris dans la sociÃ©tÃ© moderne ?",
     "options":["La rose","L'albatros","Le chat","Le gouffre"],
     "correct_option":"L'albatros","explanation":"Dans 'L'Albatros', le poÃ¨te est comme l'oiseau majestueux en vol mais ridicule sur le pont du navire."},
    {"id":"488_5","type":"vrai-faux","question":"Baudelaire est considÃ©rÃ© comme le prÃ©curseur du Symbolisme.",
     "correct":True,"explanation":"'Correspondances' et sa thÃ©orie des synesthÃ©sies prÃ©figurent l'esthÃ©tique symboliste."},
    {"id":"488_6","type":"vrai-faux","question":"Le titre paradoxal 'Les Fleurs du Mal' rÃ©unit la _____ (fleurs) et le mal moral/esthÃ©tique.",
     "correct_answer":"beautÃ©","explanation":"Baudelaire extrait la beautÃ© (fleurs) du mal, de la souffrance et de la laideur moderne."},
    {"id":"488_7","type":"qcm","question":"Dans quel poÃ¨me Baudelaire associe les parfums, les couleurs et les sons ?",
     "options":["Spleen","Correspondances","L'Invitation au voyage","La BeautÃ©"],
     "correct_option":"Correspondances","explanation":"'Correspondances' dÃ©veloppe la thÃ©orie des synesthÃ©sies entre les diffÃ©rents sens."},
    {"id":"488_8","type":"vrai-faux","question":"Le 'voyage' est un thÃ¨me rÃ©current dans Les Fleurs du Mal, symbolisant la fuite et l'aspiration Ã  l'idÃ©al.",
     "correct":True,"explanation":"'L'Invitation au voyage', 'Le Voyage' (final du recueil) montrent l'aspiration Ã  Ã©chapper au spleen."},
]),

# â”€â”€â”€ 489 â€“ MoliÃ¨re : comÃ©dies et satire sociale â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(489, "MoliÃ¨re : comÃ©dies et satire sociale", "FranÃ§ais", "1Ã¨re", [
    {"id":"489_1","type":"qcm","question":"Dans Le Tartuffe, quel vice principal est dÃ©noncÃ© ?",
     "options":["L'avarice","L'hypocrisie religieuse","La jalousie","L'orgueil"],
     "correct_option":"L'hypocrisie religieuse","explanation":"Tartuffe est un faux dÃ©vot qui manipule Orgon ; MoliÃ¨re dÃ©nonce l'hypocrisie des bigots."},
    {"id":"489_2","type":"vrai-faux","question":"Le Bourgeois gentilhomme de MoliÃ¨re est une comÃ©die-ballet.",
     "correct":True,"explanation":"Le Bourgeois gentilhomme (1670) est une comÃ©die-ballet avec musique de Lully."},
    {"id":"489_3","type":"vrai-faux","question":"Dans L'Avare, le personnage d'Harpagon incarne le dÃ©faut de l'_____ poussÃ© Ã  l'extrÃªme.",
     "correct_answer":"avarice","explanation":"Harpagon est l'archÃ©type de l'avare, prÃªt Ã  sacrifier famille et bonheur pour son or."},
    {"id":"489_4","type":"qcm","question":"Quel est le mÃ©canisme comique fondamental dÃ©crit par Bergson ?",
     "options":["L'ironie","Le rire naÃ®t du mÃ©canique plaquÃ© sur du vivant","Le paradoxe","Le jeu de mots"],
     "correct_option":"Le rire naÃ®t du mÃ©canique plaquÃ© sur du vivant","explanation":"Bergson (Le Rire, 1900) : le comique naÃ®t d'une rigiditÃ© mÃ©canique (l'obsession) qui s'oppose Ã  la souplesse du vivant."},
    {"id":"489_5","type":"vrai-faux","question":"Dom Juan de MoliÃ¨re est une tragÃ©die.",
     "correct":False,"explanation":"Dom Juan (1665) est une comÃ©die (en prose) qui explore l'athÃ©isme et le libertinage."},
    {"id":"489_6","type":"vrai-faux","question":"La comÃ©die de caractÃ¨re met en scÃ¨ne un personnage dominÃ© par un _____ unique (avarice, misanthropieâ€¦).",
     "correct_answer":"vice","explanation":"Chaque comÃ©die de caractÃ¨re de MoliÃ¨re centre l'intrigue sur un dÃ©faut psychologique dominant."},
    {"id":"489_7","type":"qcm","question":"Les PrÃ©cieuses ridicules (1659) satirise :",
     "options":["Les paysans","L'Ã‰glise","Les femmes qui singent les maniÃ¨res aristocratiques","Les mÃ©decins"],
     "correct_option":"Les femmes qui singent les maniÃ¨res aristocratiques","explanation":"MoliÃ¨re moque les bourgeoises qui imitent maladroitement les prÃ©cieuses de la cour."},
    {"id":"489_8","type":"vrai-faux","question":"MoliÃ¨re a lui-mÃªme jouÃ© dans plusieurs de ses piÃ¨ces.",
     "correct":True,"explanation":"MoliÃ¨re Ã©tait acteur-auteur-directeur de troupe ; il jouait souvent le rÃ´le du valet ou du hÃ©ros ridicule."},
]),

# â”€â”€â”€ 490 â€“ Racine : la tragÃ©die et les passions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(490, "Racine : la tragÃ©die et les passions", "FranÃ§ais", "1Ã¨re", [
    {"id":"490_1","type":"qcm","question":"Dans PhÃ¨dre, de quoi est coupable la reine PhÃ¨dre ?",
     "options":["Du meurtre de ThÃ©sÃ©e","D'un amour coupable pour son beau-fils Hippolyte","De trahison envers la GrÃ¨ce","D'ambition politique"],
     "correct_option":"D'un amour coupable pour son beau-fils Hippolyte","explanation":"PhÃ¨dre est victime d'une passion incestueuse inspirÃ©e par VÃ©nus, qui la mÃ¨ne Ã  la mort."},
    {"id":"490_2","type":"vrai-faux","question":"La fatalitÃ© est une notion centrale dans la tragÃ©die racinienne.",
     "correct":True,"explanation":"Les hÃ©ros raciniens sont Ã©crasÃ©s par une fatalitÃ© (divine, amoureuse, politique) Ã  laquelle ils ne peuvent Ã©chapper."},
    {"id":"490_3","type":"vrai-faux","question":"Dans Andromaque, Oreste aime Hermione qui aime Pyrrhus qui aime Andromaque : cette structure est appelÃ©e chaÃ®ne d'_____ non partagÃ©.",
     "correct_answer":"amour","explanation":"La chaÃ®ne d'amours non partagÃ©s crÃ©e une tension dramatique inexorable."},
    {"id":"490_4","type":"qcm","question":"Le style racinien est caractÃ©risÃ© par :",
     "options":["La pompe baroque et les mÃ©taphores foisonnantes","La simplicitÃ© apparente et l'intensitÃ© psychologique","L'humour et le burlesque","La longueur des tirades Ã©piques"],
     "correct_option":"La simplicitÃ© apparente et l'intensitÃ© psychologique","explanation":"Racine use d'un vocabulaire simple mais dense ; 'Dans un mois, dans un an' (BÃ©rÃ©nice) â†’ sobriÃ©tÃ© dÃ©chirante."},
    {"id":"490_5","type":"vrai-faux","question":"BÃ©rÃ©nice de Racine n'a pas de mort sur scÃ¨ne et se termine par une sÃ©paration.",
     "correct":True,"explanation":"BÃ©rÃ©nice est une tragÃ©die sans mort : Titus renonce Ã  BÃ©rÃ©nice pour la raison d'Ã‰tat ; ils se sÃ©parent."},
    {"id":"490_6","type":"vrai-faux","question":"La confidente, personnage secondaire de la tragÃ©die classique, permet au hÃ©ros de _____ ses sentiments.",
     "correct_answer":"exprimer","explanation":"La confidente (Å’none pour PhÃ¨dre, Pylade pour Oreste) permet l'exposition des sentiments intÃ©rieurs."},
    {"id":"490_7","type":"qcm","question":"Quelle est la particularitÃ© de la langue de Racine par rapport Ã  celle de Corneille ?",
     "options":["Racine use du latin","Racine privilÃ©gie la psychologie amoureuse et le langage des passions","Racine Ã©crit en prose","Racine s'inspire uniquement de la Bible"],
     "correct_option":"Racine privilÃ©gie la psychologie amoureuse et le langage des passions","explanation":"Corneille met en scÃ¨ne la volontÃ© hÃ©roÃ¯que (Le Cid) ; Racine explore les abÃ®mes de la passion dÃ©vastatrice."},
    {"id":"490_8","type":"vrai-faux","question":"Hermione dans Andromaque est un personnage passif qui subit les Ã©vÃ©nements.",
     "correct":False,"explanation":"Hermione est au contraire un personnage actif, violent dans sa passion et sa jalousie, qui provoque la mort de Pyrrhus."},
]),

# â”€â”€â”€ 491 â€“ Textes de l'EAF : lecture analytique â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(491, "Lecture analytique : mÃ©thode et pratique", "FranÃ§ais", "1Ã¨re", [
    {"id":"491_1","type":"qcm","question":"Quelle est la premiÃ¨re Ã©tape dans la lecture analytique d'un texte ?",
     "options":["Chercher les figures de style","Identifier le paratexte et situer l'extrait dans l'Å“uvre","RÃ©diger l'introduction","Compter les syllabes"],
     "correct_option":"Identifier le paratexte et situer l'extrait dans l'Å“uvre","explanation":"Avant l'analyse, on situe l'auteur, l'Å“uvre, le genre, le mouvement et la place de l'extrait."},
    {"id":"491_2","type":"vrai-faux","question":"Un axe de lecture est une piste d'analyse qui oriente l'Ã©tude d'une partie du texte.",
     "correct":True,"explanation":"L'axe (centre d'intÃ©rÃªt) est le fil conducteur de chaque partie de l'analyse."},
    {"id":"491_3","type":"vrai-faux","question":"Dans l'explication de texte, on procÃ¨de Ã  un _____ stylistique : repÃ©rer les procÃ©dÃ©s linguistiques et littÃ©raires.",
     "correct_answer":"relevÃ©","explanation":"Le relevÃ© systÃ©matique (lexique, syntaxe, figures, rythme) prÃ©cÃ¨de l'interprÃ©tation."},
    {"id":"491_4","type":"qcm","question":"Comment appelle-t-on le mouvement interne d'un texte ?",
     "options":["La thÃ¨se","La progression","Le registre","Le genre"],
     "correct_option":"La progression","explanation":"Identifier la progression (comment le texte avance, quelles Ã©tapes) permet de construire un plan pertinent."},
    {"id":"491_5","type":"vrai-faux","question":"Il est conseillÃ© de citer le texte en insÃ©rant de courtes citations entre guillemets dans l'analyse.",
     "correct":True,"explanation":"Les citations courtes et prÃ©cises sont la preuve textuelle de chaque analyse ; elles sont indispensables."},
    {"id":"491_6","type":"vrai-faux","question":"Le registre d'un texte dÃ©signe l'ensemble des procÃ©dÃ©s qui crÃ©ent une _____ particuliÃ¨re chez le lecteur.",
     "correct_answer":"tonalitÃ©","explanation":"Le registre (lyrique, comique, tragiqueâ€¦) est dÃ©fini par les effets produits sur le lecteur."},
    {"id":"491_7","type":"qcm","question":"Dans l'analyse d'un texte thÃ©Ã¢tral, qu'est-ce qu'une scÃ¨ne d'exposition ?",
     "options":["La scÃ¨ne de la mort du hÃ©ros","La scÃ¨ne d'ouverture qui prÃ©sente la situation initiale","La scÃ¨ne de climax","La scÃ¨ne finale"],
     "correct_option":"La scÃ¨ne d'ouverture qui prÃ©sente la situation initiale","explanation":"La scÃ¨ne d'exposition (acte I) fournit les informations nÃ©cessaires sur les personnages et la situation de dÃ©part."},
    {"id":"491_8","type":"vrai-faux","question":"La conclusion d'une lecture analytique peut proposer une mise en perspective de l'extrait.",
     "correct":True,"explanation":"La mise en perspective (ouverture) enrichit la conclusion en reliant le texte Ã  d'autres Å“uvres ou contextes."},
]),

# â”€â”€â”€ 492 â€“ L'oral de l'EAF : prÃ©paration et mÃ©thode â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(492, "L'oral de l'EAF : prÃ©paration et mÃ©thode", "FranÃ§ais", "1Ã¨re", [
    {"id":"492_1","type":"qcm","question":"Combien de temps dure la prÃ©paration de l'exposÃ© oral Ã  l'EAF ?",
     "options":["10 minutes","20 minutes","30 minutes","1 heure"],
     "correct_option":"30 minutes","explanation":"L'Ã©lÃ¨ve dispose de 30 minutes de prÃ©paration pour prÃ©parer son exposÃ© de l'EAF."},
    {"id":"492_2","type":"vrai-faux","question":"Ã€ l'oral du bac de FranÃ§ais, l'Ã©lÃ¨ve peut consulter ses notes pendant l'exposÃ©.",
     "correct":True,"explanation":"L'Ã©lÃ¨ve peut disposer de ses notes et annotations sur le texte pendant l'exposÃ©."},
    {"id":"492_3","type":"vrai-faux","question":"L'examinateur peut poser des questions sur les _____ au programme lors de l'entretien suivant l'exposÃ©.",
     "correct_answer":"Å“uvres","explanation":"L'entretien porte sur les Å“uvres intÃ©grales Ã©tudiÃ©es dans l'annÃ©e et sur les lectures cursives."},
    {"id":"492_4","type":"qcm","question":"Quelle qualitÃ© est primordiale lors de l'exposÃ© oral du bac ?",
     "options":["Lire son texte mot Ã  mot","Parler distinctement et structurer clairement son analyse","MÃ©moriser tout le cours","Ã‰viter les figures de style"],
     "correct_option":"Parler distinctement et structurer clairement son analyse","explanation":"L'oral valorise la clartÃ© de l'expression, la prÃ©cision des analyses et la maÃ®trise des rÃ©fÃ©rences."},
    {"id":"492_5","type":"vrai-faux","question":"L'oral de l'EAF dure en totalitÃ© environ 20 minutes (exposÃ© + entretien).",
     "correct":True,"explanation":"L'exposÃ© dure environ 10 minutes, puis l'entretien avec l'examinateur dure environ 10 minutes."},
    {"id":"492_6","type":"vrai-faux","question":"Pour l'oral, l'Ã©lÃ¨ve prÃ©sente un texte Ã©tudiÃ© en classe : son exposÃ© doit suivre un _____ clair avec introduction et conclusion.",
     "correct_answer":"plan","explanation":"Un plan en 2-3 axes, annoncÃ© en introduction et conclu par une synthÃ¨se, structure l'exposÃ© oral."},
    {"id":"492_7","type":"qcm","question":"Lors de l'entretien, si l'examinateur pose une question Ã  laquelle on ne sait pas rÃ©pondre, la meilleure attitude est :",
     "options":["Rester silencieux","ReconnaÃ®tre honnÃªtement la limite de sa connaissance et proposer une piste","Changer de sujet","RÃ©pÃ©ter l'exposÃ©"],
     "correct_option":"ReconnaÃ®tre honnÃªtement la limite de sa connaissance et proposer une piste","explanation":"L'examinateur valorise l'honnÃªtetÃ© intellectuelle et la dÃ©marche de rÃ©flexion, mÃªme face Ã  l'incertitude."},
    {"id":"492_8","type":"vrai-faux","question":"La lecture cursive (lecture personnelle) peut Ãªtre Ã©voquÃ©e lors de l'entretien de l'oral EAF.",
     "correct":True,"explanation":"L'examinateur peut interroger sur les lectures cursives lorsqu'elles enrichissent la rÃ©ponse."},
]),

# â”€â”€â”€ 493 â€“ PoÃ©sie engagÃ©e et rÃ©sistante â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(493, "PoÃ©sie engagÃ©e et rÃ©sistante", "FranÃ§ais", "1Ã¨re", [
    {"id":"493_1","type":"qcm","question":"Quel poÃ¨te de la RÃ©sistance a Ã©crit 'LibertÃ©' (1942) ?",
     "options":["Louis Aragon","Paul Ã‰luard","RenÃ© Char","Francis Ponge"],
     "correct_option":"Paul Ã‰luard","explanation":"'LibertÃ©' d'Ã‰luard (1942), publiÃ© dans PoÃ©sie et VÃ©ritÃ©, est un poÃ¨me-manifeste de la RÃ©sistance."},
    {"id":"493_2","type":"vrai-faux","question":"Aragon a Ã©crit des poÃ¨mes engagÃ©s pendant la RÃ©sistance sous le pseudonyme de 'FranÃ§ois la ColÃ¨re'.",
     "correct":True,"explanation":"Aragon signe certains poÃ¨mes 'FranÃ§ois la ColÃ¨re' pour contourner la censure nazie."},
    {"id":"493_3","type":"vrai-faux","question":"Dans 'LibertÃ©' d'Ã‰luard, le poÃ¨me s'achÃ¨ve par l'Ã©criture du mot _____ sur toutes les surfaces du monde.",
     "correct_answer":"LibertÃ©","explanation":"Chaque strophe liste les surfaces (cahier d'Ã©colier, arbre, pierreâ€¦) sur lesquelles le poÃ¨te Ã©crit 'LibertÃ©'."},
    {"id":"493_4","type":"qcm","question":"RenÃ© Char, poÃ¨te rÃ©sistant, est Ã©galement connu comme :",
     "options":["Romancier policier","Chef de maquis dans le Vaucluse","Professeur de Sorbonne","Journaliste de guerre"],
     "correct_option":"Chef de maquis dans le Vaucluse","explanation":"Char (pseudonyme 'Capitaine Alexandre') dirige le groupe de rÃ©sistance Ventoux dans le Vaucluse."},
    {"id":"493_5","type":"vrai-faux","question":"La poÃ©sie engagÃ©e renonce toujours Ã  la beautÃ© formelle au profit du message.",
     "correct":False,"explanation":"La poÃ©sie rÃ©sistante (Ã‰luard, Aragon, Char) maintient une haute exigence formelle ; la beautÃ© renforce le message."},
    {"id":"493_6","type":"vrai-faux","question":"Le poÃ¨me 'Le DÃ©sespoir du soleil' de RenÃ© Char fait partie du recueil _____ (1946).",
     "correct_answer":"Feuillets d'Hypnos","explanation":"Feuillets d'Hypnos (1946) rassemble les notes de Char pendant sa pÃ©riode de rÃ©sistance armÃ©e."},
    {"id":"493_7","type":"qcm","question":"Quelle technique le poÃ¨me 'La Rose et le RÃ©sÃ©da' d'Aragon cÃ©lÃ¨bre-t-il ?",
     "options":["Le vers libre","Le sonnet traditionnel","L'alexandrin rimÃ©","Le poÃ¨me en prose"],
     "correct_option":"L'alexandrin rimÃ©","explanation":"'La Rose et le RÃ©sÃ©da' utilise des alexandrins rimÃ©s, rÃ©cupÃ©rant la tradition formelle pour mieux rÃ©sister."},
    {"id":"493_8","type":"vrai-faux","question":"Jacques PrÃ©vert est associÃ© Ã  une poÃ©sie engagÃ©e contre la guerre et les injustices sociales.",
     "correct":True,"explanation":"PrÃ©vert (Paroles, 1945) dÃ©nonce la guerre ('Familiale'), les inÃ©galitÃ©s et le conformisme avec humour et tendresse."},
]),

# â”€â”€â”€ 494 â€“ Le roman au XXe siÃ¨cle : nouveaux courants â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(494, "Le roman au XXe siÃ¨cle : nouveaux courants", "FranÃ§ais", "1Ã¨re", [
    {"id":"494_1","type":"qcm","question":"Le Nouveau Roman des annÃ©es 1950-60 se caractÃ©rise par :",
     "options":["Une intrigue linÃ©aire et des personnages bien dÃ©finis","Le refus du personnage traditionnel, de l'intrigue et du narrateur omniscient","Un retour au roman historique","L'engagement politique explicite"],
     "correct_option":"Le refus du personnage traditionnel, de l'intrigue et du narrateur omniscient","explanation":"Robbe-Grillet, Sarraute, Butor, Simon dÃ©construisent les codes du roman rÃ©aliste."},
    {"id":"494_2","type":"vrai-faux","question":"Alain Robbe-Grillet est l'auteur du manifeste Pour un nouveau roman.",
     "correct":True,"explanation":"Pour un nouveau roman (1963) de Robbe-Grillet thÃ©orise les nouvelles pratiques romanesques."},
    {"id":"494_3","type":"vrai-faux","question":"L'Existentialisme, dont Sartre est la figure phare, affirme que l'existence prÃ©cÃ¨de l'_____, c'est-Ã -dire que l'homme se dÃ©finit par ses choix.",
     "correct_answer":"essence","explanation":"'L'existence prÃ©cÃ¨de l'essence' : l'homme n'a pas de nature prÃ©dÃ©finie ; il se crÃ©e par ses actes."},
    {"id":"494_4","type":"qcm","question":"L'Ã‰tranger de Camus prÃ©sente un narrateur :",
     "options":["Omniscient et engagÃ©","DÃ©tachÃ© et indiffÃ©rent (absurde)","Lyrique et romantique","Comique et ironique"],
     "correct_option":"DÃ©tachÃ© et indiffÃ©rent (absurde)","explanation":"Meursault raconte les Ã©vÃ©nements (mÃªme le meurtre) avec une neutralitÃ© qui incarne l'absurde camusien."},
    {"id":"494_5","type":"vrai-faux","question":"Simone de Beauvoir est une romanciÃ¨re et philosophe existentialiste.",
     "correct":True,"explanation":"De Beauvoir (Le DeuxiÃ¨me Sexe, Les Mandarins) unit fiction et philosophie existentialiste et fÃ©ministe."},
    {"id":"494_6","type":"vrai-faux","question":"Dans le Nouveau Roman, le lecteur est souvent sollicitÃ© de maniÃ¨re active : il doit _____ le sens du texte.",
     "correct_answer":"construire","explanation":"Le Nouveau Roman refuse le sens tout fait et engage le lecteur dans une co-construction du sens."},
    {"id":"494_7","type":"qcm","question":"Quel roman de Nathalie Sarraute est souvent citÃ© comme exemple de Nouveau Roman ?",
     "options":["La Jalousie","Tropismes","L'Ã‰tranger","Les Gommes"],
     "correct_option":"Tropismes","explanation":"Tropismes (1939, rÃ©Ã©d. 1957) de Sarraute explore les micro-mouvements psychologiques presque imperceptibles."},
    {"id":"494_8","type":"vrai-faux","question":"Le roman existentialiste s'intÃ©resse Ã  la libertÃ© et Ã  la responsabilitÃ© de l'individu face au monde.",
     "correct":True,"explanation":"Sartre (La NausÃ©e, Les Chemins de la libertÃ©) fait du roman le lieu d'exploration de la condition humaine libre et responsable."},
]),

# â”€â”€â”€ 495 â€“ RÃ©visions gÃ©nÃ©rales : Å“uvres au programme â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(495, "RÃ©visions : Å“uvres canoniques au programme", "FranÃ§ais", "1Ã¨re", [
    {"id":"495_1","type":"qcm","question":"Qui a Ã©crit 'Ã€ la recherche du temps perdu' ?",
     "options":["AndrÃ© Gide","Marcel Proust","Ã‰mile Zola","Stendhal"],
     "correct_option":"Marcel Proust","explanation":"Ã€ la recherche du temps perdu (7 tomes, 1913-1927) est le grand roman de la mÃ©moire involontaire de Proust."},
    {"id":"495_2","type":"vrai-faux","question":"L'Illiade et L'OdyssÃ©e sont des Ã©popÃ©es grecques attribuÃ©es Ã  HomÃ¨re.",
     "correct":True,"explanation":"Ces deux Ã©popÃ©es (VIIIe s. av. J.-C.) sont les textes fondateurs de la littÃ©rature occidentale."},
    {"id":"495_3","type":"vrai-faux","question":"Le mythe de Sisyphe, revisitÃ© par Camus, symbolise l'_____.",
     "correct_answer":"absurde","explanation":"Sisyphe, condamnÃ© Ã  rouler un rocher Ã©ternellement, incarne l'absurde ; Camus conclut qu'il faut l'imaginer heureux."},
    {"id":"495_4","type":"qcm","question":"Quel texte de Rousseau est considÃ©rÃ© comme une autobiographie fondatrice ?",
     "options":["Du contrat social","Les Confessions","L'Ã‰mile","La Nouvelle HÃ©loÃ¯se"],
     "correct_option":"Les Confessions","explanation":"Les Confessions (posthume, 1782) de Rousseau inaugurent le genre autobiographique moderne."},
    {"id":"495_5","type":"vrai-faux","question":"La Princesse de ClÃ¨ves (1678) est un roman de Madame de Lafayette.",
     "correct":True,"explanation":"Madame de Lafayette est l'auteure de ce roman psychologique prÃ©curseur du roman moderne."},
    {"id":"495_6","type":"vrai-faux","question":"Le 'roman Ã©pistolaire' comme Les Liaisons dangereuses est un roman composÃ© de _____.",
     "correct_answer":"lettres","explanation":"Le roman Ã©pistolaire (epistola = lettre) est composÃ© d'Ã©changes de lettres entre personnages."},
    {"id":"495_7","type":"qcm","question":"Qui est l'auteur du Cid, tragi-comÃ©die hÃ©roÃ¯que du XVIIe siÃ¨cle ?",
     "options":["MoliÃ¨re","Racine","Corneille","Rotrou"],
     "correct_option":"Corneille","explanation":"Le Cid (1637) de Corneille provoque la querelle du Cid ; c'est une tragi-comÃ©die devenue emblÃ¨me du hÃ©roÃ¯sme cornÃ©lien."},
    {"id":"495_8","type":"vrai-faux","question":"Voltaire est Ã  la fois philosophe, poÃ¨te, dramaturge, essayiste et auteur de contes philosophiques.",
     "correct":True,"explanation":"Voltaire est un gÃ©nie universel des LumiÃ¨res, excellent dans tous les genres."},
]),

# â”€â”€â”€ 496 â€“ RÃ©visions : mÃ©thodologie EAF â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(496, "RÃ©visions : mÃ©thodologie EAF (Ã©crit)", "FranÃ§ais", "1Ã¨re", [
    {"id":"496_1","type":"qcm","question":"L'Ã©preuve Ã©crite anticipÃ©e de FranÃ§ais (EAF) comporte :",
     "options":["Une rÃ©daction libre","Un commentaire ou une dissertation + une Ã©criture d'invention","Un contrÃ´le de connaissances","Un rÃ©sumÃ© de texte"],
     "correct_option":"Un commentaire ou une dissertation + une Ã©criture d'invention","explanation":"L'EAF Ã©crit propose le choix entre commentaire et dissertation, plus un sujet d'Ã©criture d'invention."},
    {"id":"496_2","type":"vrai-faux","question":"Le commentaire littÃ©raire nÃ©cessite une introduction avec problÃ©matique et annonce de plan.",
     "correct":True,"explanation":"L'introduction du commentaire comprend : accroche, prÃ©sentation du texte, problÃ©matique, annonce du plan."},
    {"id":"496_3","type":"vrai-faux","question":"En dissertation, la _____ est la question centrale Ã  laquelle tout le devoir doit rÃ©pondre.",
     "correct_answer":"problÃ©matique","explanation":"La problÃ©matique articule le sujet en une question ouverte, guide et fil conducteur du devoir."},
    {"id":"496_4","type":"qcm","question":"Quel plan choisir pour le sujet : 'Le roman est-il le meilleur genre pour dÃ©noncer les injustices sociales ?' ?",
     "options":["Plan thÃ©matique","Plan dialectique","Plan chronologique","Plan analytique"],
     "correct_option":"Plan dialectique","explanation":"La question appelle une discussion : oui (thÃ¨se) / nuances ou non (antithÃ¨se) / dÃ©passement (synthÃ¨se)."},
    {"id":"496_5","type":"vrai-faux","question":"L'Ã©criture d'invention peut prendre la forme d'un dialogue, d'un discours ou d'un rÃ©cit selon le sujet.",
     "correct":True,"explanation":"Le sujet d'invention prÃ©cise souvent le genre (dialogue, lettre, discours, suite de texteâ€¦)."},
    {"id":"496_6","type":"vrai-faux","question":"Dans un commentaire, chaque sous-partie s'articule : idÃ©e directrice + analyse du procÃ©dÃ© + _____ + interprÃ©tation.",
     "correct_answer":"citation","explanation":"La citation courte du texte est la preuve textuelle de l'analyse ; elle est indispensable."},
    {"id":"496_7","type":"qcm","question":"Combien de temps dure l'Ã©preuve Ã©crite anticipÃ©e de FranÃ§ais au bac ?",
     "options":["2 heures","3 heures","4 heures","5 heures"],
     "correct_option":"4 heures","explanation":"L'EAF Ã©crit dure 4 heures, permettant de traiter les deux parties (lecture + Ã©criture)."},
    {"id":"496_8","type":"vrai-faux","question":"Les brouillons et la planification comptent pour la note finale de l'EAF.",
     "correct":False,"explanation":"Seule la copie finale est notÃ©e ; les brouillons sont des outils de prÃ©paration non Ã©valuÃ©s."},
]),

# â”€â”€â”€ 497 â€“ La langue : registres et niveaux de langue â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(497, "Registres et niveaux de langue", "FranÃ§ais", "1Ã¨re", [
    {"id":"497_1","type":"qcm","question":"Quel niveau de langue est attendu dans un devoir de FranÃ§ais au lycÃ©e ?",
     "options":["Familier","Courant","Soutenu","Populaire"],
     "correct_option":"Soutenu","explanation":"L'EAF exige un niveau de langue soutenu : vocabulaire prÃ©cis, syntaxe correcte, absence d'argot."},
    {"id":"497_2","type":"vrai-faux","question":"Le registre comique peut inclure le burlesque, le parodique et l'ironie.",
     "correct":True,"explanation":"Le registre comique recouvre diffÃ©rentes tonalitÃ©s : humour, ironie, burlesque, farce, satireâ€¦"},
    {"id":"497_3","type":"vrai-faux","question":"Le registre _____ vise Ã  Ã©mouvoir le lecteur par la reprÃ©sentation de la souffrance et du malheur.",
     "correct_answer":"pathÃ©tique","explanation":"Le pathÃ©tique (pathos) provoque compassion et pitiÃ© chez le lecteur."},
    {"id":"497_4","type":"qcm","question":"Qu'est-ce que le registre polÃ©mique ?",
     "options":["Une tonalitÃ© douce et mÃ©lancolique","Un ton agressif et combatif visant Ã  attaquer une idÃ©e ou une personne","Un registre Ã©pique","Un style poÃ©tique"],
     "correct_option":"Un ton agressif et combatif visant Ã  attaquer une idÃ©e ou une personne","explanation":"Le registre polÃ©mique (ex. pamphlets, Ã©ditoriaux engagÃ©s) cherche Ã  dÃ©stabiliser l'adversaire."},
    {"id":"497_5","type":"vrai-faux","question":"Le niveau de langue familier est appropriÃ© dans une correspondance professionnelle.",
     "correct":False,"explanation":"La correspondance professionnelle exige un niveau soutenu ou courant, jamais familier."},
    {"id":"497_6","type":"vrai-faux","question":"Le _____ fantastique crÃ©e l'hÃ©sitation entre explication rationnelle et surnaturelle, selon Todorov.",
     "correct_answer":"registre","explanation":"Todorov dÃ©finit le fantastique comme 'l'hÃ©sitation Ã©prouvÃ©e par un Ãªtre qui ne connaÃ®t que les lois naturelles, face Ã  un Ã©vÃ©nement en apparence surnaturel'."},
    {"id":"497_7","type":"qcm","question":"Quel registre domine dans les Ã©popÃ©es homÃ©riques ?",
     "options":["Lyrique","Ã‰pique","Comique","PathÃ©tique"],
     "correct_option":"Ã‰pique","explanation":"Le registre Ã©pique valorise la grandeur, les hÃ©ros, les batailles et l'ampleur des enjeux collectifs."},
    {"id":"497_8","type":"vrai-faux","question":"Le registre tragique implique une fatalitÃ© qui Ã©crase le personnage malgrÃ© ses efforts.",
     "correct":True,"explanation":"Le tragique naÃ®t du conflit entre la volontÃ© du hÃ©ros et une force supÃ©rieure (destin, passion, loi divine) qui le condamne."},
]),

# â”€â”€â”€ 498 â€“ RÃ©visions finales : culture littÃ©raire gÃ©nÃ©rale â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(498, "RÃ©visions finales : culture littÃ©raire gÃ©nÃ©rale", "FranÃ§ais", "1Ã¨re", [
    {"id":"498_1","type":"qcm","question":"Quel est le titre complet du roman de Flaubert publiÃ© en 1857 ?",
     "options":["Madame Bovary, mÅ“urs de province","Madame Bovary, roman rÃ©aliste","Madame Bovary, vie d'une femme","Madame Bovary, histoire d'une passion"],
     "correct_option":"Madame Bovary, mÅ“urs de province","explanation":"Le sous-titre 'mÅ“urs de province' ancre le roman dans une critique sociale du milieu bourgeois provincial."},
    {"id":"498_2","type":"vrai-faux","question":"La tragÃ©die antique grecque est jouÃ©e en l'honneur du dieu Dionysos.",
     "correct":True,"explanation":"Les concours de tragÃ©dies athÃ©niens (Eschyle, Sophocle, Euripide) se dÃ©roulent lors des fÃªtes dionysiaques."},
    {"id":"498_3","type":"vrai-faux","question":"Le sonnet est un poÃ¨me de _____ vers, structurÃ© en deux quatrains et deux tercets.",
     "correct_answer":"14","explanation":"Le sonnet (PÃ©trarque, Ronsard, Baudelaire) comporte 14 vers : ABBA ABBA CCD EDE (ou variantes)."},
    {"id":"498_4","type":"qcm","question":"Quel genre littÃ©raire propose une vision idÃ©ale et utopique d'une sociÃ©tÃ© parfaite ?",
     "options":["La dystopie","L'utopie","Le roman d'anticipation","Le roman noir"],
     "correct_option":"L'utopie","explanation":"L'utopie (Thomas More, 1516) dÃ©crit une sociÃ©tÃ© idÃ©ale ; son contraire est la dystopie (Orwell, Huxley)."},
    {"id":"498_5","type":"vrai-faux","question":"La nouvelle littÃ©raire se distingue du roman par sa briÃ¨vetÃ© et son effet de chute.",
     "correct":True,"explanation":"La nouvelle est un rÃ©cit court, condensÃ©, souvent avec un dÃ©nouement surprenant (chute ou fin ouverte)."},
    {"id":"498_6","type":"vrai-faux","question":"Le genre du _____ autobiographique implique que l'auteur, le narrateur et le personnage principal soient la mÃªme personne.",
     "correct_answer":"pacte","explanation":"Philippe Lejeune dÃ©finit le 'pacte autobiographique' : identitÃ© auteur = narrateur = personnage principal."},
    {"id":"498_7","type":"qcm","question":"Quel est le mouvement littÃ©raire du roman La NausÃ©e de Sartre ?",
     "options":["SurrÃ©alisme","Existentialisme","Naturalisme","Romantisme"],
     "correct_option":"Existentialisme","explanation":"La NausÃ©e (1938) de Sartre est le roman manifeste de l'existentialisme."},
    {"id":"498_8","type":"vrai-faux","question":"Le thÃ©Ã¢tre absurde s'inscrit dans la continuitÃ© du thÃ©Ã¢tre classique.",
     "correct":False,"explanation":"Le thÃ©Ã¢tre de l'absurde (Ionesco, Beckett) rompt avec toutes les conventions dramatiques classiques."},
]),

# â”€â”€â”€ 499 â€“ Grand oral : prÃ©paration et enjeux â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(499, "Grand oral : prÃ©paration et enjeux", "FranÃ§ais", "1Ã¨re", [
    {"id":"499_1","type":"qcm","question":"Le Grand Oral du baccalaurÃ©at s'appuie sur :",
     "options":["Un exposÃ© de mathÃ©matiques","Une question liÃ©e aux spÃ©cialitÃ©s du lycÃ©en","Une rÃ©citation poÃ©tique","Un entretien sur la vie personnelle"],
     "correct_option":"Une question liÃ©e aux spÃ©cialitÃ©s du lycÃ©en","explanation":"Le Grand Oral (20 min) porte sur une question choisie par le candidat, en lien avec ses spÃ©cialitÃ©s."},
    {"id":"499_2","type":"vrai-faux","question":"Le Grand Oral valorise la clartÃ© de l'expression et la posture de l'orateur.",
     "correct":True,"explanation":"Les critÃ¨res d'Ã©valuation incluent la qualitÃ© de l'expression, la posture, le regard et la structuration du discours."},
    {"id":"499_3","type":"vrai-faux","question":"Pour prÃ©parer le Grand Oral, l'Ã©lÃ¨ve doit formuer sa question de faÃ§on _____, claire et problÃ©matisÃ©e.",
     "correct_answer":"prÃ©cise","explanation":"La question doit Ãªtre bien dÃ©limitÃ©e et problÃ©matisÃ©e pour permettre un dÃ©veloppement structurÃ©."},
    {"id":"499_4","type":"qcm","question":"Lors du Grand Oral, quelle est la durÃ©e de la prise de parole en continu ?",
     "options":["5 minutes","10 minutes","15 minutes","20 minutes"],
     "correct_option":"5 minutes","explanation":"Le candidat parle 5 minutes en continu, puis rÃ©pond aux questions de l'examinateur pendant 10 minutes, puis suit 5 minutes sur le projet d'orientation."},
    {"id":"499_5","type":"vrai-faux","question":"Le Grand Oral peut Ã©galement Ãªtre utilisÃ© pour prÃ©senter un projet personnel ou professionnel.",
     "correct":True,"explanation":"La 3e partie du Grand Oral (5 min) porte sur le projet d'orientation du candidat."},
    {"id":"499_6","type":"vrai-faux","question":"La capacitÃ© Ã  reformuler ses idÃ©es, Ã  Ã©couter les questions et Ã  rÃ©pondre avec nuance s'appelle l'_____ orale.",
     "correct_answer":"aisance","explanation":"L'aisance orale (fluiditÃ©, adaptation, Ã©coute) est un critÃ¨re clÃ© du Grand Oral."},
    {"id":"499_7","type":"qcm","question":"Pour le Grand Oral, une bonne introduction doit :",
     "options":["RÃ©sumer toute la rÃ©ponse","Poser la question, en montrer l'intÃ©rÃªt et annoncer le plan","Citer une dÃ©finition de dictionnaire","Donner une liste d'exemples"],
     "correct_option":"Poser la question, en montrer l'intÃ©rÃªt et annoncer le plan","explanation":"L'introduction accroche l'examinateur, contextualise la question et annonce la structure de l'exposÃ©."},
    {"id":"499_8","type":"vrai-faux","question":"Il est conseillÃ© d'utiliser des supports visuels (diaporama) lors du Grand Oral.",
     "correct":False,"explanation":"Le Grand Oral est une Ã©preuve exclusivement orale, sans support numÃ©rique ou diaporama."},
]),

# â”€â”€â”€ 500 â€“ RÃ©vision ultime : panorama de la littÃ©rature franÃ§aise â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
(500, "RÃ©vision ultime : panorama de la littÃ©rature franÃ§aise", "FranÃ§ais", "1Ã¨re", [
    {"id":"500_1","type":"qcm","question":"Quelle est la premiÃ¨re grande Ã©poque de la littÃ©rature franÃ§aise ?",
     "options":["Le XVIe siÃ¨cle humaniste","Le Moyen Ã‚ge (IXe-XVe siÃ¨cle)","Le XVIIe siÃ¨cle classique","Le XIXe siÃ¨cle romantique"],
     "correct_option":"Le Moyen Ã‚ge (IXe-XVe siÃ¨cle)","explanation":"La littÃ©rature franÃ§aise commence au Moyen Ã‚ge : Serments de Strasbourg (842), Chanson de Roland, roman courtoisâ€¦"},
    {"id":"500_2","type":"vrai-faux","question":"La chanson de geste est un genre poÃ©tique mÃ©diÃ©val qui chante les exploits guerriers des chevaliers.",
     "correct":True,"explanation":"La Chanson de Roland (XIe s.) est la chanson de geste la plus cÃ©lÃ¨bre, chantant la bravoure des chevaliers de Charlemagne."},
    {"id":"500_3","type":"vrai-faux","question":"Le Roman de la Rose (XIIIe s.) est une allÃ©gorie de la _____ courtoise.",
     "correct_answer":"conquÃªte","explanation":"Le Roman de la Rose allÃ¨gorise la conquÃªte de l'amour (la rose = la femme aimÃ©e)."},
    {"id":"500_4","type":"qcm","question":"Quel siÃ¨cle est souvent surnommÃ© le 'Grand SiÃ¨cle' de la littÃ©rature franÃ§aise ?",
     "options":["XVIe siÃ¨cle","XVIIe siÃ¨cle","XVIIIe siÃ¨cle","XIXe siÃ¨cle"],
     "correct_option":"XVIIe siÃ¨cle","explanation":"Le XVIIe siÃ¨cle, siÃ¨cle de MoliÃ¨re, Racine, Corneille, La Fontaine, La Rochefoucauld, est le 'Grand SiÃ¨cle'."},
    {"id":"500_5","type":"vrai-faux","question":"Le XVIIIe siÃ¨cle est surnommÃ© le 'SiÃ¨cle des LumiÃ¨res' en raison du mouvement philosophique dominant.",
     "correct":True,"explanation":"Les philosophes (Voltaire, Rousseau, Montesquieu, Diderot) Ã©clairent la raison et combattent l'obscurantisme."},
    {"id":"500_6","type":"vrai-faux","question":"Le XXe siÃ¨cle voit naÃ®tre le _____, mouvement qui affirme l'absurditÃ© de l'existence humaine.",
     "correct_answer":"Existentialisme","explanation":"Sartre et Camus, dans les annÃ©es 1940-50, dÃ©finissent l'existentialisme comme philosophie et esthÃ©tique littÃ©raire."},
    {"id":"500_7","type":"qcm","question":"Quel mouvement littÃ©raire du XXe siÃ¨cle associe littÃ©rature et engagement politique ?",
     "options":["Le Nouveau Roman","Le SurrÃ©alisme","La littÃ©rature engagÃ©e (Sartre, de Beauvoir)","Le Parnasse"],
     "correct_option":"La littÃ©rature engagÃ©e (Sartre, de Beauvoir)","explanation":"Sartre thÃ©orise l'engagement dans Qu'est-ce que la littÃ©rature ? (1947) : l'Ã©crivain doit prendre position."},
    {"id":"500_8","type":"vrai-faux","question":"La littÃ©rature francophone inclut des auteurs du monde entier Ã©crivant en franÃ§ais.",
     "correct":True,"explanation":"AimÃ© CÃ©saire (Martinique), LÃ©opold SÃ©dar Senghor (SÃ©nÃ©gal), Marguerite Yourcenar (Belgique)â€¦ la littÃ©rature francophone est mondiale."},
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
        print(f"  âœ“ {qid}.json - {title}")

    print(f"\nâœ… FranÃ§ais 1Ã¨re: {count} quiz gÃ©nÃ©rÃ©s (+ {count} rÃ©ponses = {count * 6} fichiers)")


if __name__ == "__main__":
    print("Generating FranÃ§ais 1Ã¨re quizzes 466-500...")
    write_quiz_files()

