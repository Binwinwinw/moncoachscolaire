#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Template IA â€“ generate_<niveau>_<matiere>.py
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime
from http.cookiejar import CookieJar
from urllib.error import HTTPError, URLError
from urllib.request import Request, build_opener, HTTPCookieProcessor

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", "..", ".."))

OUTPUT_DIR = os.path.join(SCRIPT_DIR, "phychi_6eme_quizzes")
QUIZ_DIR = os.path.join(OUTPUT_DIR, "quiz")
ANSWERS_DIR = os.path.join(OUTPUT_DIR, "quiz_answers")

RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

PHP_API_URL = os.getenv("MCSPHP_QUIZ_API_URL", "http://localhost/index.php?page=api/ia/generate_quiz")
PHP_API_PROVIDER = os.getenv("MCSPHP_QUIZ_PROVIDER", "groq")
PHP_CLI_TOKEN = os.getenv("MCSPHP_CLI_API_TOKEN", "")
PHP_QUIZ_COUNT = int(os.getenv("MCSPHP_QUIZ_COUNT", "10"))
USE_PHP_BACKEND = os.getenv("MCSPHP_USE_PHP_BACKEND", "false").lower() in {"1", "true", "yes", "y"}


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
        return {k: normalize_text_payload(v) for k, v in payload.items()}
    if isinstance(payload, list):
        return [normalize_text_payload(x) for x in payload]
    if isinstance(payload, tuple):
        return tuple(normalize_text_payload(x) for x in payload)
    if isinstance(payload, str):
        return fix_mojibake_text(payload)
    return payload


def save_json(data, file_path):
    """Sauvegarde un dictionnaire en JSON UTF-8 proprement."""
    os.makedirs(os.path.dirname(file_path), exist_ok=True)
    with open(file_path, "w", encoding="utf-8", newline="\n") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
        f.write("\n")


def normalize_question_type(question_type):
    qt = str(question_type).strip().lower()
    if qt == "qcm":
        return "qcm"
    if qt in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    raise ValueError(f"Type de question inconnu ou interdit : '{question_type}' (valeur normalisée : '{qt}')")


def build_true_false_statement(question_text, fallback_answer=""):
    question_text = str(question_text).strip()
    fallback_answer = str(fallback_answer).strip().rstrip(".")
    if fallback_answer:
        return f"{question_text} La bonne réponse attendue est : {fallback_answer}."
    return question_text or "Choisis si l'affirmation est vraie ou fausse."


def make_quiz(qid, title, subject, level, questions,
              source="Eduscol + BOEN",
              programme_ref=""):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    clean_questions = [
        {k: v for k, v in q.items() if k not in answer_keys}
        for q in questions
    ]
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    quiz_questions = []
    for question in clean_questions:
        qtype = normalize_question_type(question.get("type", ""))
        if qtype == "qcm":
            sanitized = {
                "id": str(question.get("id", "")),
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            }
        else:
            sanitized = {
                "id": str(question.get("id", "")),
                "type": "vrai-faux",
                "question": build_true_false_statement(
                    question.get("question", ""),
                    question.get("correct_answer", ""),
                ),
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
            "source": source,
            "programme_ref": programme_ref,
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
        qtype = normalize_question_type(q.get("type", ""))
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
            tf_source = q.get("correct", q.get("correct_answer", "vrai"))
            tf_answer = "vrai" if str(tf_source).strip().lower() in {"true", "vrai", "1"} else "faux"
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": tf_answer,
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


def normalize_php_quiz_questions(qid, questions):
    normalized = []
    for index, question in enumerate(questions, start=1):
        text = str(question.get("question", "")).strip()
        raw_choices = question.get("choices") or question.get("options") or []
        if not text or not isinstance(raw_choices, list) or len(raw_choices) < 2:
            continue

        choices = []
        for choice in raw_choices:
            if isinstance(choice, dict):
                label = str(choice.get("label") or choice.get("value") or "").strip()
            else:
                label = str(choice).strip()
            if label:
                choices.append(label)

        if len(choices) < 2:
            continue

        raw_correct = question.get("correct")
        if raw_correct is None:
            raw_correct = question.get("correct_option") or question.get("correct_answer") or ""
        if isinstance(raw_correct, dict):
            raw_correct = raw_correct.get("value") or raw_correct.get("label") or ""
        correct_option = str(raw_correct).strip()
        if correct_option not in choices:
            for choice in raw_choices:
                if isinstance(choice, dict) and str(choice.get("value", "")).strip() == correct_option:
                    correct_option = str(choice.get("label", "")).strip()
                    break

        if correct_option not in choices and str(correct_option).isdigit():
            index = int(correct_option)
            if 1 <= index <= len(choices):
                correct_option = choices[index - 1]

        if correct_option not in choices:
            correct_option = choices[0]

        explanation = str(question.get("explanation", "")).strip()
        normalized.append(qcm_question(qid, index, text, choices, correct_option, explanation))

    return normalized


def call_php_quiz_api(qid, title, topic, provider=PHP_API_PROVIDER):
    payload = {
        "level": "6eme",
        "subject": "Physique-Chimie",
        "type": "qcm",
        "topic": topic,
        "provider": provider,
    }
    headers = {
        "Content-Type": "application/json",
    }
    if PHP_CLI_TOKEN:
        payload["cli_token"] = PHP_CLI_TOKEN
        headers["X-CLI-Token"] = PHP_CLI_TOKEN

    request = Request(
        PHP_API_URL,
        data=json.dumps(payload, ensure_ascii=False).encode("utf-8"),
        headers=headers,
        method="POST",
    )
    opener = build_opener(HTTPCookieProcessor(CookieJar()))

    try:
        response = opener.open(request, timeout=30)
        body = response.read().decode("utf-8")
    except HTTPError as error:
        raise RuntimeError(f"PHP API returned HTTP {error.code}: {error.reason}") from error
    except URLError as error:
        raise RuntimeError(f"Impossible de joindre le backend PHP : {error.reason}") from error

    payload = json.loads(body)
    if not payload.get("success"):
        raise RuntimeError(f"PHP backend error: {payload.get('error') or payload}")

    questions = payload.get("questions")
    if not isinstance(questions, list):
        raise RuntimeError("Le backend PHP n'a pas retourné une liste de questions.")

    normalized = normalize_php_quiz_questions(qid, questions)
    if not normalized:
        raise RuntimeError("Aucune question normalisée n'a pu être extraite du backend PHP.")

    return normalized


def get_quizzes_data():
    if USE_PHP_BACKEND:
        topics = PHYCHI_6EME_TOPICS[:PHP_QUIZ_COUNT]
        print(f"Utilisation du backend PHP pour generer {len(topics)} quiz")
        return [
            (qid, title, "Physique-Chimie", "6eme", call_php_quiz_api(qid, title, topic))
            for qid, title, topic in topics
        ]

    return [build_6eme_phychi_quiz(*spec) for spec in PHYCHI_6EME_TOPICS]


PHYCHI_6EME_TOPICS = [
    (241, 'Physique-Chimie 6eme - Mesures et unites', 'mesures et unites'),
    (242, 'Physique-Chimie 6eme - Mouvements et forces', 'mouvements et forces'),
    (243, 'Physique-Chimie 6eme - Energie et conversion', 'energie et conversion'),
    (244, 'Physique-Chimie 6eme - Modeles particulaires', 'modeles particulaires'),
    (245, 'Physique-Chimie 6eme - Reactions chimiques', 'reactions chimiques'),
    (246, 'Physique-Chimie 6eme - Electricite', 'electricite'),
    (247, 'Physique-Chimie 6eme - Ondes et signaux', 'ondes et signaux'),
    (248, 'Physique-Chimie 6eme - Securite au laboratoire', 'securite au laboratoire'),
    (249, 'Physique-Chimie 6eme - Demarche experimentale', 'demarche experimentale'),
    (250, 'Physique-Chimie 6eme - Resolution de problemes', 'resolution de problemes'),
    (251, 'Physique-Chimie 6eme - Communication scientifique', 'communication scientifique'),
    (252, 'Physique-Chimie 6eme - Competences transversales', 'competences transversales'),
    (253, 'Physique-Chimie 6eme - Evaluation des acquis', 'evaluation des acquis'),
    (254, 'Physique-Chimie 6eme - Auto-evaluation et metacognition', 'auto-evaluation et metacognition'),
    (255, 'Physique-Chimie 6eme - Notions d experience', 'notions d experience'),
    (256, 'Physique-Chimie 6eme - Instruments de mesure', 'instruments de mesure'),
    (257, 'Physique-Chimie 6eme - Unites et grandeurs', 'unites et grandeurs'),
    (258, 'Physique-Chimie 6eme - Temperature et chaleur', 'temperature et chaleur'),
    (259, 'Physique-Chimie 6eme - Pression et gaz', 'pression et gaz'),
    (260, 'Physique-Chimie 6eme - Etats de la matiere', 'etats de la matiere'),
    (261, 'Physique-Chimie 6eme - Atomes et molecules', 'atomes et molecules'),
    (262, 'Physique-Chimie 6eme - Corps purs et melanges', 'corps purs et melanges'),
    (263, 'Physique-Chimie 6eme - Separation des melanges', 'separation des melanges'),
    (264, 'Physique-Chimie 6eme - Reactions et transformations', 'reactions et transformations'),
    (265, 'Physique-Chimie 6eme - Conservation de la matiere', 'conservation de la matiere'),
    (266, 'Physique-Chimie 6eme - Energie et transfert', 'energie et transfert'),
    (267, 'Physique-Chimie 6eme - Travail et puissance', 'travail et puissance'),
    (268, 'Physique-Chimie 6eme - Piles et circuits', 'piles et circuits'),
    (269, 'Physique-Chimie 6eme - Conducteurs et isolants', 'conducteurs et isolants'),
    (270, 'Physique-Chimie 6eme - Magnetisme et aimants', 'magnetisme et aimants'),
    (271, 'Physique-Chimie 6eme - Lumiere et optique', 'lumiere et optique'),
    (272, 'Physique-Chimie 6eme - Sons et signaux', 'sons et signaux'),
    (273, 'Physique-Chimie 6eme - Masse et volume', 'masse et volume'),
    (274, 'Physique-Chimie 6eme - Densite et flottabilite', 'densite et flottabilite'),
    (275, 'Physique-Chimie 6eme - Grandeurs physiques', 'grandeurs physiques'),
    (276, 'Physique-Chimie 6eme - Systemes et interactions', 'systemes et interactions'),
    (277, 'Physique-Chimie 6eme - Methodes d observation', 'methodes d observation'),
    (278, 'Physique-Chimie 6eme - Schema et representation', 'schema et representation'),
    (279, 'Physique-Chimie 6eme - Mesure et incertitude', 'mesure et incertitude'),
    (280, 'Physique-Chimie 6eme - Prudence au laboratoire', 'prudence au laboratoire'),
    (281, 'Physique-Chimie 6eme - Recherche documentaire', 'recherche documentaire'),
    (282, 'Physique-Chimie 6eme - Communication des resultats', 'communication des resultats'),
    (283, 'Physique-Chimie 6eme - Auto-correction scientifique', 'auto-correction scientifique'),
    (284, 'Physique-Chimie 6eme - Evaluation des progres', 'evaluation des progres'),
    (285, 'Physique-Chimie 6eme - Interpretation des graphiques', 'interpretation des graphiques'),
    (286, 'Physique-Chimie 6eme - Bilan energetique', 'bilan energetique'),
    (287, 'Physique-Chimie 6eme - Sources d energie', 'sources d energie'),
    (288, 'Physique-Chimie 6eme - Retour d experience', 'retour d experience'),
]


def qcm_question(qid, number, question, options, correct_option, explanation):
    return {
        "id": f"{qid}_{number}",
        "type": "qcm",
        "question": question,
        "options": options,
        "correct_option": correct_option,
        "explanation": explanation,
    }


def vf_question(qid, number, question, correct, explanation):
    return {
        "id": f"{qid}_{number}",
        "type": "vrai-faux",
        "question": question,
        "correct": correct,
        "explanation": explanation,
    }


def build_process_quiz(qid, title, focus):
    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Quelle action est la plus appropriee pour progresser sur {focus} ?",
            [
                "Verifier ses observations et corriger ses erreurs",
                "Ignorer les resultats inattendus",
                "Changer de protocole sans raison",
                "Repondre a la va-vite"
            ],
            "Verifier ses observations et corriger ses erreurs",
            "La correction et la verification font partie de la demarche scientifique."
        ),
        qcm_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Lorsque l'on etudie {focus}, il est utile de :",
            [
                "Noter les etapes et discuter des resultats",
                "Travailler seul sans observer",
                "Inventer des resultats",
                "Oublier la consigne"
            ],
            "Noter les etapes et discuter des resultats",
            "La documentation et la discussion aident a comprendre le travail scientifique."
        ),
        vf_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Expliquer ses observations aide a mieux maitriser {focus}.",
            True,
            "Expliquer ce qu'on observe permet de mieux comprendre le phenomene."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Quel comportement est le moins utile pour {focus} ?",
            [
                "Ignorer ce qui a ete observe",
                "Verifier ses calculs",
                "Poser des questions sur l'experience",
                "Comparer plusieurs resultats"
            ],
            "Ignorer ce qui a ete observe",
            "Ignorer des observations empeche de progresser en science."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Une communication claire des resultats est essentielle pour {focus}.",
            True,
            "Partager des resultats clairs facilite la comprehension collective."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Quelle phrase correspond le mieux a l'etude de {focus} ?",
            [
                "On s'appuie sur des faits et des observations",
                "On fait des suppositions sans verifier",
                "On prend une seule mesure sans comparer",
                "On invente les resultats"
            ],
            "On s'appuie sur des faits et des observations",
            "La demarche scientifique repose sur l'observation et l'analyse."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Quelle methode est utile pour evaluer {focus} ?",
            [
                "Verifier ses solutions et corriger ses erreurs",
                "Ne pas controler ses mesures",
                "Melanger des notions sans lien",
                "Sauter les etapes"
            ],
            "Verifier ses solutions et corriger ses erreurs",
            "La verification et la correction sont essentielles pour avancer."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Se documenter et utiliser des references aide a travailler sur {focus}.",
            True,
            "La recherche documentaire aide a trouver des informations fiables."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_measurement_quiz(qid, title, focus):
    focus_lower = focus.lower()

    if focus_lower == "mesures et unites":
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] Quel symbole correspond à une longueur ?",
                [
                    "m",
                    "kg",
                    "s",
                    "A"
                ],
                "m",
                "Le mètre est l'unité de longueur."
            ),
            qcm_question(
                qid, 2,
                "[Physique-Chimie 6eme] Quelle unité utilise-t-on pour le volume ?",
                [
                    "Litre",
                    "Newton",
                    "Mètre",
                    "Candela"
                ],
                "Litre",
                "Le litre est une unité de volume."
            ),
            vf_question(
                qid, 3,
                "[Physique-Chimie 6eme] La masse s'exprime en kilogrammes.",
                True,
                "La masse est mesurée en kilogrammes."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] Quelle grandeur se mesure en secondes ?",
                [
                    "Temps",
                    "Longueur",
                    "Masse",
                    "Température"
                ],
                "Temps",
                "Le temps se mesure en secondes."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] Une unité permet de comparer des mesures entre elles.",
                True,
                "L'unité rend les mesures comparables."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Parmi ces termes, lequel est une grandeur ?",
                [
                    "Vitesse",
                    "Kilogramme",
                    "Mètre",
                    "Secondes"
                ],
                "Vitesse",
                "La vitesse est une grandeur physique."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] Quel instrument aide à mesurer une longueur ?",
                [
                    "Règle",
                    "Stylo",
                    "Gomme",
                    "Feuille"
                ],
                "Règle",
                "La règle sert à mesurer des longueurs."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] On inscrit toujours l'unité après la valeur d'une mesure.",
                True,
                "L'unité complète la mesure et précise ce qui est mesuré."
            ),
        ]
    elif focus_lower == "instruments de mesure":
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] Quel instrument mesure la température ?",
                [
                    "Thermomètre",
                    "Baromètre",
                    "Balance",
                    "Chronomètre"
                ],
                "Thermomètre",
                "Le thermomètre mesure la température."
            ),
            qcm_question(
                qid, 2,
                "[Physique-Chimie 6eme] Quel instrument sert à mesurer une masse ?",
                [
                    "Balance",
                    "Boussole",
                    "Loupe",
                    "Éprouvette"
                ],
                "Balance",
                "La balance mesure la masse d'un objet."
            ),
            vf_question(
                qid, 3,
                "[Physique-Chimie 6eme] Un jambon n'est pas un instrument de mesure.",
                True,
                "Un jambon n'est pas un outil scientifique."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] Quel instrument mesure le volume d'un liquide ?",
                [
                    "Éprouvette graduée",
                    "Thermomètre",
                    "Voltmètre",
                    "Baromètre"
                ],
                "Éprouvette graduée",
                "L'éprouvette graduée mesure le volume d'un liquide."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] On choisit l'instrument selon la grandeur qu'on mesure.",
                True,
                "Chaque grandeur a un instrument adapté."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Lequel de ces objets n'est pas un instrument de mesure ?",
                [
                    "Couteau",
                    "Thermomètre",
                    "Balance",
                    "Manomètre"
                ],
                "Couteau",
                "Le couteau n'est pas un instrument de mesure scientifique."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] Quel instrument mesure la pression ?",
                [
                    "Manomètre",
                    "Règle",
                    "Loupe",
                    "Ampèremètre"
                ],
                "Manomètre",
                "Le manomètre mesure la pression d'un gaz ou d'un liquide."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] Un instrument de mesure se choisit selon la grandeur.",
                True,
                "L'instrument adapté est nécessaire pour une bonne mesure."
            ),
        ]
    elif focus_lower == "unites et grandeurs":
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] Quelle unité correspond à une longueur ?",
                [
                    "Mètre",
                    "Newton",
                    "Joule",
                    "Coulomb"
                ],
                "Mètre",
                "Le mètre est l'unité de longueur."
            ),
            qcm_question(
                qid, 2,
                "[Physique-Chimie 6eme] Quelle grandeur est mesurée en secondes ?",
                [
                    "Temps",
                    "Masse",
                    "Température",
                    "Volume"
                ],
                "Temps",
                "Le temps est mesuré en secondes."
            ),
            vf_question(
                qid, 3,
                "[Physique-Chimie 6eme] Une unité ne change pas la grandeur mesurée.",
                True,
                "L'unité sert seulement à exprimer la grandeur."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] Quelle paire est correcte ?",
                [
                    "Kilogramme - masse",
                    "Celsius - longueur",
                    "Litre - énergie",
                    "Ampère - volume"
                ],
                "Kilogramme - masse",
                "Le kilogramme est l'unité de masse."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] La température et la chaleur sont la même chose.",
                False,
                "La chaleur est une énergie, la température est une mesure d'agitation."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Quel symbole représente une unité de masse ?",
                [
                    "kg",
                    "m",
                    "s",
                    "A"
                ],
                "kg",
                "Le symbole kg désigne le kilogramme, unité de masse."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] Que permet une unité ?",
                [
                    "Donner un sens numérique à la grandeur",
                    "Changer la couleur d'un objet",
                    "Mesurer une pensée",
                    "Écrire une histoire"
                ],
                "Donner un sens numérique à la grandeur",
                "Une unité rend une grandeur compréhensible et comparable."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] On peut mesurer une grandeur sans unité.",
                False,
                "Sans unité, une mesure n'est pas complète."
            ),
        ]
    elif focus_lower == "temperature et chaleur":
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] Quel instrument permet de mesurer la température ?",
                [
                    "Thermomètre",
                    "Baromètre",
                    "Balance",
                    "Chronomètre"
                ],
                "Thermomètre",
                "Le thermomètre mesure la température."
            ),
            qcm_question(
                qid, 2,
                "[Physique-Chimie 6eme] Quelle unité est utilisée pour la température ?",
                [
                    "Degré Celsius",
                    "Kilogramme",
                    "Mètre",
                    "Ampère"
                ],
                "Degré Celsius",
                "La température est souvent exprimée en degrés Celsius."
            ),
            vf_question(
                qid, 3,
                "[Physique-Chimie 6eme] La chaleur correspond à l'énergie transférée entre deux corps.",
                True,
                "La chaleur est une forme d'énergie en mouvement."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] Quelle action augmente généralement la température d'un objet ?",
                [
                    "Appliquer de la chaleur",
                    "Ouvrir une fenêtre",
                    "Ajouter de l'eau froide",
                    "Presser l'objet"
                ],
                "Appliquer de la chaleur",
                "Appliquer de la chaleur augmente l'agitation des particules et la température."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] Le thermomètre est un instrument de mesure.",
                True,
                "Il permet de lire une valeur de température."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Entre l'eau à 0°C et l'eau à 50°C, quelle est la plus chaude ?",
                [
                    "L'eau à 50°C",
                    "L'eau à 0°C",
                    "Elles ont la même chaleur",
                    "Aucune n'est chaude"
                ],
                "L'eau à 50°C",
                "Plus la température est élevée, plus un objet est chaud."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] Quel effet a une source de chaleur sur un solide ?",
                [
                    "Il peut fondre ou augmenter de température",
                    "Il devient plus léger sans changer",
                    "Il cesse d'exister",
                    "Il devient invisible"
                ],
                "Il peut fondre ou augmenter de température",
                "La chaleur peut faire fondre un solide ou augmenter sa température."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] La température est toujours la même partout dans un objet.",
                False,
                "Différents points peuvent avoir des températures différentes."
            ),
        ]
    elif focus_lower == "pression et gaz":
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] Quel instrument sert à mesurer la pression d'un gaz ?",
                [
                    "Manomètre",
                    "Thermomètre",
                    "Balance",
                    "Règle"
                ],
                "Manomètre",
                "Le manomètre mesure la pression d'un gaz ou d'un liquide."
            ),
            qcm_question(
                qid, 2,
                "[Physique-Chimie 6eme] Quelle unité est utilisée pour la pression ?",
                [
                    "Pascal",
                    "Mètre",
                    "Candela",
                    "Kelvin"
                ],
                "Pascal",
                "La pression se mesure en pascals."
            ),
            vf_question(
                qid, 3,
                "[Physique-Chimie 6eme] La pression est la force exercée par un gaz sur une surface.",
                True,
                "La pression dépend de la force et de la surface."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] Si on compresse un gaz dans un récipient fermé, que se passe-t-il ?",
                [
                    "Sa pression augmente",
                    "Sa masse diminue",
                    "Il devient solide",
                    "Il change de couleur"
                ],
                "Sa pression augmente",
                "La compression d'un gaz tend à augmenter sa pression."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] Un gaz exerce une force sur les parois du récipient.",
                True,
                "Les particules du gaz frappent les parois et créent une pression."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Quel phénomène est lié à la pression d'un gaz ?",
                [
                    "Écraser une boîte de conserve",
                    "Allumer une lampe",
                    "Écrire une formule",
                    "Colorer une feuille"
                ],
                "Écraser une boîte de conserve",
                "La pression d'un gaz peut écraser un objet si le récipient est comprimé."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] Que se passe-t-il quand on augmente la température d'un gaz dans un récipient fermé ?",
                [
                    "La pression augmente",
                    "La pression diminue",
                    "La couleur du gaz change",
                    "Le gaz devient liquide"
                ],
                "La pression augmente",
                "Augmenter la température d'un gaz fermé augmente souvent sa pression."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] La pression est toujours la même dans tout un récipient.",
                False,
                "La pression peut varier selon la position et le gaz."
            ),
        ]
    elif focus_lower == "masse et volume":
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] Quel instrument sert à mesurer la masse d'un objet ?",
                [
                    "Balance",
                    "Chronomètre",
                    "Thermomètre",
                    "Règle"
                ],
                "Balance",
                "La balance mesure la masse d'un objet."
            ),
            vf_question(
                qid, 2,
                "[Physique-Chimie 6eme] Le volume mesure l'espace occupé par un objet.",
                True,
                "Le volume correspond à l'espace occupé par un corps."
            ),
            qcm_question(
                qid, 3,
                "[Physique-Chimie 6eme] Quelle unité peut servir pour un volume ?",
                [
                    "Litre",
                    "Kilogramme",
                    "Celsius",
                    "Ampère"
                ],
                "Litre",
                "Le litre est une unité de volume."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] La densité compare :",
                [
                    "La masse et le volume",
                    "La longueur et le temps",
                    "L'énergie et la puissance",
                    "La couleur et la forme"
                ],
                "La masse et le volume",
                "La densité compare la masse d'un objet à son volume."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] Une masse plus grande signifie toujours un volume plus grand.",
                False,
                "Un objet peut être lourd sans occuper beaucoup de volume."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Quel instrument mesure une longueur ?",
                [
                    "Règle",
                    "Balance",
                    "Voltmètre",
                    "Thermomètre"
                ],
                "Règle",
                "La règle mesure une longueur."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] Quel objet possède une masse et un volume ?",
                [
                    "Une pierre",
                    "Un son",
                    "Une idée",
                    "Une image"
                ],
                "Une pierre",
                "Une pierre est un objet matériel avec une masse et un volume."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] La densité dépend de la masse et du volume.",
                True,
                "La densité se calcule en divisant la masse par le volume."
            ),
        ]
    elif focus_lower == "densite et flottabilite":
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] Si un objet flotte, sa densité est généralement :",
                [
                    "Plus faible que celle de l'eau",
                    "Plus élevée que celle de l'eau",
                    "Toujours égale à 10",
                    "Toujours égale à 0"
                ],
                "Plus faible que celle de l'eau",
                "Un objet flotte quand sa densité est inférieure à celle de l'eau."
            ),
            vf_question(
                qid, 2,
                "[Physique-Chimie 6eme] La densité est un rapport sans unité.",
                True,
                "La densité compare la masse et le volume d'un objet."
            ),
            qcm_question(
                qid, 3,
                "[Physique-Chimie 6eme] Pour calculer la densité, on divise :",
                [
                    "La masse par le volume",
                    "La longueur par le temps",
                    "La température par la pression",
                    "L'énergie par la puissance"
                ],
                "La masse par le volume",
                "La densité est la masse divisée par le volume."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] Pourquoi un iceberg flotte-t-il ?",
                [
                    "Sa densité est plus faible que celle de l'eau",
                    "Il est plus lourd que l'eau",
                    "Il est chaud",
                    "Il est transparent"
                ],
                "Sa densité est plus faible que celle de l'eau",
                "L'iceberg flotte car sa densité est inférieure à celle de l'eau."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] La flottabilité est la force qui pousse vers le haut.",
                True,
                "La flottabilité aide un objet à rester au-dessus de l'eau."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Quel objet a une densité plus faible que l'eau ?",
                [
                    "Une feuille de bois sec",
                    "Une pierre en fer",
                    "Un morceau de métal",
                    "Un morceau de verre"
                ],
                "Une feuille de bois sec",
                "Le bois sec flotte dans l'eau car sa densité est plus faible."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] Que se passe-t-il lorsqu'un objet plus dense que le liquide est plongé ?",
                [
                    "Il coule",
                    "Il flotte toujours",
                    "Il disparait",
                    "Il change de couleur"
                ],
                "Il coule",
                "Un objet plus dense que le liquide coule."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] La densité dépend uniquement de la masse.",
                False,
                "La densité dépend de la masse et du volume."
            ),
        ]
    elif focus_lower == "grandeurs physiques":
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] La longueur, la masse et le temps sont des :",
                [
                    "Grandeurs physiques",
                    "Couleurs",
                    "Aliments",
                    "Odeurs"
                ],
                "Grandeurs physiques",
                "La longueur, la masse et le temps sont des grandeurs physiques mesurables."
            ),
            vf_question(
                qid, 2,
                "[Physique-Chimie 6eme] Une grandeur physique est toujours exprimée avec une unité.",
                True,
                "L'unité est nécessaire pour donner du sens à la valeur."
            ),
            qcm_question(
                qid, 3,
                "[Physique-Chimie 6eme] Quelle grandeur s'exprime en secondes ?",
                [
                    "Temps",
                    "Masse",
                    "Longueur",
                    "Température"
                ],
                "Temps",
                "Le temps est mesuré en secondes."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] Quelle unité est celle de la masse ?",
                [
                    "Kilogramme",
                    "Mètre",
                    "Seconde",
                    "Kelvin"
                ],
                "Kilogramme",
                "Le kilogramme est l'unité de masse."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] La couleur est une grandeur physique standard.",
                False,
                "La couleur n'est pas une grandeur physique standard."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Quel symbole représente une unité de longueur ?",
                [
                    "m",
                    "kg",
                    "s",
                    "A"
                ],
                "m",
                "Le symbole m correspond au mètre."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] La vitesse associe :",
                [
                    "Longueur et temps",
                    "Couleur et goût",
                    "Masse et volume",
                    "Température et pression"
                ],
                "Longueur et temps",
                "La vitesse compare une longueur parcourue au temps."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] On peut mesurer une grandeur avec différentes unités.",
                True,
                "On choisit l'unité adaptée à la grandeur mesurée."
            ),
        ]
    else:
        questions = [
            qcm_question(
                qid, 1,
                "[Physique-Chimie 6eme] Quelle phrase est vraie pour une mesure scientifique ?",
                [
                    "On utilise une unité et un instrument adapté",
                    "On se base uniquement sur l'apparence",
                    "On répète sans noter les résultats",
                    "On ignore l'incertitude"
                ],
                "On utilise une unité et un instrument adapté",
                "Une mesure scientifique combine une unité et un instrument adapté."
            ),
            vf_question(
                qid, 2,
                "[Physique-Chimie 6eme] Un instrument de mesure doit être précis.",
                True,
                "La précision permet d'obtenir un résultat fiable."
            ),
            qcm_question(
                qid, 3,
                "[Physique-Chimie 6eme] Quel élément est indispensable pour mesurer une grandeur ?",
                [
                    "Une unité",
                    "Une couleur",
                    "Un goût",
                    "Une opinion"
                ],
                "Une unité",
                "L'unité est essentielle pour donner du sens à la mesure."
            ),
            qcm_question(
                qid, 4,
                "[Physique-Chimie 6eme] Quel instrument peut aider à mesurer une longueur ?",
                [
                    "Règle",
                    "Casquette",
                    "Chaussure",
                    "Stylo"
                ],
                "Règle",
                "La règle mesure des longueurs."
            ),
            vf_question(
                qid, 5,
                "[Physique-Chimie 6eme] Noter l'unité est important pour une mesure.",
                True,
                "Sans unité, la mesure n'est pas complète."
            ),
            qcm_question(
                qid, 6,
                "[Physique-Chimie 6eme] Parmi ces valeurs, laquelle est une unité ?",
                [
                    "Kilogramme",
                    "Rapide",
                    "Grand",
                    "Pressé"
                ],
                "Kilogramme",
                "Le kilogramme est une unité de masse."
            ),
            qcm_question(
                qid, 7,
                "[Physique-Chimie 6eme] Pourquoi fait-on plusieurs mesures ?",
                [
                    "Pour vérifier la précision",
                    "Pour perdre du temps",
                    "Pour rendre les résultats flous",
                    "Pour changer les unités"
                ],
                "Pour vérifier la précision",
                "Plusieurs mesures permettent de vérifier la fiabilité."
            ),
            vf_question(
                qid, 8,
                "[Physique-Chimie 6eme] Une mesure donne toujours une réponse exacte.",
                False,
                "Toute mesure comporte une incertitude."
            ),
        ]

    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_motion_energy_quiz(qid, title, focus):
    focus_lower = focus.lower()
    if "mouvement" in focus_lower or "force" in focus_lower:
        theme_description = "mouvement et forces"
        option_good = "Une force peut modifier le mouvement d'un objet"
        explanation = "Une force agit sur un objet pour changer sa vitesse ou sa direction."
    elif "travail" in focus_lower or "puissance" in focus_lower:
        theme_description = "travail et puissance"
        option_good = "La puissance mesure la vitesse a laquelle on effectue un travail"
        explanation = "La puissance compare le travail realise par unite de temps."
    else:
        theme_description = focus
        option_good = f"{focus.capitalize()} implique la transformation d'energie"
        explanation = "Ce theme explique comment l'energie se transforme d'une forme a une autre."

    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Quelle phrase est vraie pour {theme_description} ?",
            [
                option_good,
                "Une force rend toujours un objet plus chaud",
                "Une force n'affecte jamais un objet",
                "Une force change toujours la couleur d'un objet"
            ],
            option_good,
            explanation
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Une force peut changer la direction d'un objet.",
            True,
            "Une force modifie le mouvement."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Dans le cadre de {focus}, que represente l'energie ?",
            [
                "La capacite de faire agir ou de transformer une chose",
                "Un instrument de mesure",
                "Une couleur",
                "Un type de dessin"
            ],
            "La capacite de faire agir ou de transformer une chose",
            "L'energie est la capacite a produire un changement ou a fournir un travail."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Quel exemple montre une transformation d'energie ?",
            [
                "Une lampe qui eclaire en consommant de l'electricite",
                "Un morceau de papier qui ne change pas",
                "Un objet qui reste immobile",
                "La couleur d'une voiture"
            ],
            "Une lampe qui eclaire en consommant de l'electricite",
            "Une lampe transforme l'energie electrique en energie lumineuse et thermique."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Le bilan energetique compare l'energie entre un debut et une fin de situation.",
            True,
            "Le bilan energetique permet de voir les transformations d'energie."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Quel element est le plus proche de {focus} ?",
            [
                "La transformation d'energie",
                "Une chanson",
                "Une opinion",
                "Un outil de cuisine"
            ],
            "La transformation d'energie",
            "La notion de {focus} parle souvent de conversions d'energie."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Pour comprendre {focus}, il est utile de :",
            [
                "Observer les changements d'energie ou de mouvement",
                "Ignorer les effets",
                "Melanger des concepts sans lien",
                "Choisir la reponse la plus longue"
            ],
            "Observer les changements d'energie ou de mouvement",
            "La science observe les changements pour expliquer les phenomenes."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] On peut parler de conservation de l'energie dans un systeme isole.",
            True,
            "L'energie totale se conserve dans un systeme isole."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_electricity_quiz(qid, title, focus):
    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Quel element est necessaire pour qu'un circuit {focus} fonctionne ?",
            [
                "Une source d'electricite",
                "De l'eau chaude",
                "Une couleur vive",
                "Un aimant en plastique"
            ],
            "Une source d'electricite",
            "Un circuit electrique a besoin d'une source pour faire circuler le courant."
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Un conducteur laisse passer le courant electrique.",
            True,
            "Les conducteurs facilitent le passage du courant."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Quel materiau est un isolant ?",
            [
                "Le plastique",
                "Le cuivre",
                "L'aluminium",
                "L'eau salée"
            ],
            "Le plastique",
            "Un isolant empeche le courant de circuler facilement."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Dans une pile, que produit l'electricite ?",
            [
                "Une reaction chimique entre ses composants",
                "Un changement de couleur",
                "Un son fort",
                "Un mouvement automatique"
            ],
            "Une reaction chimique entre ses composants",
            "La pile utilise une reaction chimique pour produire un courant."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Un circuit ouvert ne permet pas au courant de circuler.",
            True,
            "Le courant circule seulement si le circuit est ferme."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Quel element est indispensable pour mesurer l'intensite dans un circuit ?",
            [
                "Un ampere metre",
                "Une mire",
                "Une regle",
                "Une loupe"
            ],
            "Un ampere metre",
            "L'ampere metre mesure l'intensite du courant."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Quand on branche un dispositif en serie, le courant :",
            [
                "est le meme dans tous les elements",
                "change a chaque composant",
                "devient plus chaud que la lumiere",
                "deviens de l'eau"
            ],
            "est le meme dans tous les elements",
            "Dans un circuit en serie, le courant circule de facon identique."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Un fil conducteur est utile pour relier les differents elements d'un circuit.",
            True,
            "Sans fil conducteur, le courant ne peut pas circuler."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_waves_quiz(qid, title, focus):
    focus_lower = focus.lower()
    if "lumiere" in focus_lower or "optique" in focus_lower:
        theme = "lumiere et optique"
        good = "La lumiere se deplace en ligne droite dans un milieu homogene"
        explanation = "La lumiere se propage en ligne droite tant que le milieu est uniforme."
    else:
        theme = "ondes et signaux"
        good = "Les ondes transmettent de l'information sans transporter la matiere"
        explanation = "Une onde deplace de l'energie ou de l'information, pas la matiere."

    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Quelle affirmation est vraie pour {theme} ?",
            [
                good,
                "Les ondes sont des objets solides",
                "Les signaux sont des couleurs seulement",
                "Les ondes sont invisibles a toujours"
            ],
            good,
            explanation
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Un signal peut transporter une information a distance.",
            True,
            "Les signaux servent a transmettre des informations."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Quel exemple correspond le mieux a un signal ?",
            [
                "Le son d'une cloche",
                "La taille d'un objet",
                "La couleur d'un mur",
                "La texture d'un tissu"
            ],
            "Le son d'une cloche",
            "Le son est un signal qui transporte une information auditive."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Que fait un miroir avec la lumiere ?",
            [
                "Il la renvoie",
                "Il la mange",
                "Il la transforme en nourriture",
                "Il la rend invisible"
            ],
            "Il la renvoie",
            "Un miroir reflechi la lumiere."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Les ondes sonores peuvent se propager dans l'air.",
            True,
            "Le son se propage dans l'air et dans d'autres milieux."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Une source de lumiere est :",
            [
                "Une ampoule",
                "Un aimant",
                "Une lame de papier",
                "Un volume"
            ],
            "Une ampoule",
            "Une ampoule emet de la lumiere."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Quel mot decrit un signal ?",
            [
                "Information",
                "Couleur",
                "Temperature",
                "Pesanteur"
            ],
            "Information",
            "Un signal transporte de l'information."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Une lentille peut faire converger la lumiere.",
            True,
            "Une lentille modifie la direction des rayons lumineux."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_matter_quiz(qid, title, focus):
    focus_lower = focus.lower()
    if "etats" in focus_lower:
        sample = "etat"
        example = "solide, liquide et gaz"
    elif "corps purs" in focus_lower or "melanges" in focus_lower:
        sample = "corps pur ou melange"
        example = "eau pure et eau salee"
    elif "separation" in focus_lower:
        sample = "separation de melanges"
        example = "filtration ou decantation"
    elif "reactions" in focus_lower or "transformations" in focus_lower:
        sample = "reaction chimique"
        example = "oxydation ou combustion"
    elif "conservation" in focus_lower:
        sample = "conservation de la matiere"
        example = "la masse totale reste la meme"
    else:
        sample = "matiere"
        example = "solide, liquide, gaz"

    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Quel exemple correspond a un {sample} ?",
            [
                example,
                "une chanson",
                "une couleur",
                "un instrument"
            ],
            example,
            f"{example.capitalize()} illustre ce theme de physique-chimie."
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Un melange peut contenir plusieurs substances.",
            True,
            "Un melange est compose de plusieurs substances differentes."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Quel processus sert a separer un melange ?",
            [
                "Filtration",
                "Chant",
                "Peinture",
                "Guitare"
            ],
            "Filtration",
            "La filtration separe les solides des liquides."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Une reaction chimique produit souvent :",
            [
                "Une nouvelle substance",
                "Une nouvelle couleur de stylos",
                "Un son sans changement de matiere",
                "Un mouvement sans fraction"
            ],
            "Une nouvelle substance",
            "Une reaction chimique transforme des substances en d'autres substances."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Lors d'une reaction chimique, la masse totale reste la meme.",
            True,
            "La matiere se conserve pendant une reaction chimique."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Que represente l'etat solide ?",
            [
                "Une forme et un volume fixes",
                "Une substance qui coule toujours",
                "Une substance sans forme",
                "Une fameuse equation"
            ],
            "Une forme et un volume fixes",
            "Un solide conserve sa forme et son volume."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Quand on separe un melange, on utilise :",
            [
                "Une methode physique",
                "Un avis personnel",
                "Une couleur differente",
                "Un appareil photo"
            ],
            "Une methode physique",
            "La separation de melanges utilise des proprietes physiques."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Un corps pur contient une seule substance.",
            True,
            "Un corps pur est compose d'une seule substance definie."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_lab_quiz(qid, title, focus):
    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Que faut-il faire avant une experience en laboratoire ?",
            [
                "Lire la consigne et preparer le materiel",
                "Commencer sans rien lire",
                "Faire du bruit",
                "Courir dans la salle"
            ],
            "Lire la consigne et preparer le materiel",
            "Preparer et comprendre le travail est essentiel avant de commencer."
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Un schema aide a representer une experience scientifique.",
            True,
            "Un schema clarifie la procedure ou le resultat."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Quel etat de vigilance est necessaire au laboratoire ?",
            [
                "Etre prudent et respecter les consignes",
                "Jeter le materiel partout",
                "Manger dans la paillasse",
                "Ne pas porter de lunettes"
            ],
            "Etre prudent et respecter les consignes",
            "La prudence est obligatoire pour travailler en laboratoire."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Pourquoi noter l'incertitude d'une mesure ?",
            [
                "Pour montrer que la valeur peut varier",
                "Pour cacher des erreurs",
                "Pour rendre le resultat plus petit",
                "Pour changer l'unite"
            ],
            "Pour montrer que la valeur peut varier",
            "L'incertitude indique la precision de la mesure."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Observer plusieurs fois un phenomene aide a mieux le comprendre.",
            True,
            "Les observations repetees renforcent la comprehension."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Que veut dire interpreter un graphique ?",
            [
                "Lire les informations qu'il presente",
                "Le dessiner de maniere artistique",
                "Changer les couleurs sans raison",
                "Ignorer les axes"
            ],
            "Lire les informations qu'il presente",
            "Interpréter un graphique consiste à comprendre ses donnees."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Quel role a la representation par schema ?",
            [
                "Montrer la disposition et les relations entre les elements",
                "Faire un dessin joli",
                "Remplacer les conclusions",
                "Changer la consigne"
            ],
            "Montrer la disposition et les relations entre les elements",
            "Un schema montre comment les elements sont relies."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Il faut toujours respecter la prudence en laboratoire.",
            True,
            "La securite est essentielle pour proteger les eleves et l'environnement."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_meta_quiz(qid, title, focus):
    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Pourquoi est-il important de communiquer les resultats de {focus} ?",
            [
                "Pour partager et verifier les conclusions",
                "Pour les garder secrets",
                "Pour changer leur forme",
                "Pour colorier la page"
            ],
            "Pour partager et verifier les conclusions",
            "La communication scientifique permet de confronter et valider les resultats."
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] S'auto-evaluer aide a progresser dans {focus}.",
            True,
            "L'auto-evaluation permet d'identifier ce qui a bien marche et ce qui doit etre corrige."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Quelle pratique est utile pour une bonne recherche documentaire ?",
            [
                "Utiliser des sources fiables et comparer les informations",
                "Copier tout sans verifier",
                "Utiliser uniquement une seule page web",
                "Ne pas noter les sources"
            ],
            "Utiliser des sources fiables et comparer les informations",
            "Comparer plusieurs sources fiables est important en recherche documentaire."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] En {focus}, quel comportement montre une bonne attitude scientifique ?",
            [
                "Verifier ses observations et corriger ses erreurs",
                "Ignorer ce qui ne convient pas",
                "Rejeter les questions des autres",
                "Tricher sur les resultats"
            ],
            "Verifier ses observations et corriger ses erreurs",
            "Une bonne attitude scientifique implique de corriger et d'apprendre de ses erreurs."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Evaluer ses progres aide a mieux preparer la suite.",
            True,
            "La prise de recul sur ses acquis permet de mieux orienter l'apprentissage."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Que signifie 'retour d'experience' ?",
            [
                "Analyser ce qui a fonctionne et ce qui peut etre améliore",
                "Oublier ce qui s'est passe",
                "Faire la meme chose sans changer",
                "Dessiner un schema inutile"
            ],
            "Analyser ce qui a fonctionne et ce qui peut etre améliore",
            "Le retour d'experience consiste a tirer des lecons de ce qui a ete fait."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Quel element montre que l'on travaille de facon scientifique ?",
            [
                "Utiliser des faits et des observations",
                "Faire uniquement des suppositions",
                "Ignorer les erreurs",
                "Changer la consigne sans raison"
            ],
            "Utiliser des faits et des observations",
            "La science s'appuie sur des faits et des observations."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Une auto-correction scientifique consiste a comprendre puis a corriger une erreur.",
            True,
            "Comprendre l'erreur est la premiere etape pour la corriger."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_systems_quiz(qid, title, focus):
    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Que represente un systeme scientifique ?",
            [
                "Un ensemble d'elements qui interagissent entre eux",
                "Un seul objet isole",
                "Une couleur ou une chanson",
                "Une equation inutile"
            ],
            "Un ensemble d'elements qui interagissent entre eux",
            "Un systeme comprend plusieurs elements en relation."
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Les interactions dans un systeme peuvent influencer son comportement.",
            True,
            "Les elements d'un systeme s'influencent mutuellement."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Quel exemple illustre un systeme ?",
            [
                "Une plante et son environnement",
                "Une feuille blanche seule",
                "Une couleur uni",
                "Un geste sans effet"
            ],
            "Une plante et son environment",
            "Une plante et son environnement forment un systeme biologico-chimique."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Pourquoi etudier les interactions entre elements ?",
            [
                "Pour comprendre comment le systeme evolue",
                "Pour ignorer les changements",
                "Pour choisir la plus belle couleur",
                "Pour faire une experience sans objectif"
            ],
            "Pour comprendre comment le systeme evolue",
            "Les interactions expliquent le fonctionnement du systeme."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Un schema peut aider a representer un systeme.",
            True,
            "Un schema clarifie les relations entre les elements."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Quel mot designe une relation entre elements ?",
            [
                "Interaction",
                "Bateau",
                "Chanson",
                "Ordinateur"
            ],
            "Interaction",
            "Une interaction est une relation entre des elements du systeme."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Que signifie 'systemes et interactions' ?",
            [
                "Etudier comment les parties se comportent ensemble",
                "Faire un dessin sans sens",
                "Mesurer seulement la temperature",
                "Changer la couleur"
            ],
            "Etudier comment les parties se comportent ensemble",
            "Le systeme se comprend en observant ses interactions."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Comprendre un systeme aide a prevenir des problemes.",
            True,
            "Connaitre un systeme permet d'anticiper son comportement."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_atomic_quiz(qid, title, focus):
    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Que represente un atome ?",
            [
                "La plus petite unite de matiere conservant ses proprietes",
                "Un instrument de laboratoire",
                "Une couleur",
                "Une reaction chimique"
            ],
            "La plus petite unite de matiere conservant ses proprietes",
            "Un atome est la plus petite unite d'un element chimique."
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Un molecule est composee de plusieurs atomes.",
            True,
            "Une molecule est un ensemble d'atomes lies entre eux."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Quel exemple correspond a une molecule ?",
            [
                "H2O",
                "Une ampoule",
                "Un thermometre",
                "Une bille"
            ],
            "H2O",
            "H2O est une molecule composee d'atomes d'hydrogene et d'oxygene."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Dans une molecule, les atomes sont relies par :",
            [
                "Des liaisons chimiques",
                "Du vent",
                "De la chaleur",
                "Un courant electrique"
            ],
            "Des liaisons chimiques",
            "Les atomes d'une molecule sont maintenus ensemble par des liaisons chimiques."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Un corps pur contient des molecules identiques.",
            True,
            "Un corps pur est compose d'une seule substance definie avec des molecules semblables."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Quelle phrase est vraie pour les atomes et les molecules ?",
            [
                "Ils sont a la base de toute matiere",
                "Ils ne servent qu'a faire des experiences",
                "Ils sont visibles a l'oeil nu",
                "Ils ne peuvent pas se deplacer"
            ],
            "Ils sont a la base de toute matiere",
            "Les atomes et les molecules constituent la matiere qui nous entoure."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] Pourquoi utilise-t-on des modeles pour representer des atomes ?",
            [
                "Parce qu'ils sont trop petits pour etre vus",
                "Parce qu'ils sont grands comme des maisons",
                "Parce qu'ils sont des couleurs",
                "Parce qu'ils sont des instruments"
            ],
            "Parce qu'ils sont trop petits pour etre vus",
            "Les modeles aident a imaginer ce qui est trop petit pour etre observe directement."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Les molecules peuvent avoir des proprietes differentes selon leur composition.",
            True,
            "La composition des molecules determine leurs proprietes."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)


def build_generic_phychi_quiz(qid, title, focus):
    questions = [
        qcm_question(
            qid, 1,
            f"[Physique-Chimie 6eme] Quelle affirmation correspond le mieux a {focus} ?",
            [
                "C'est un concept qui s'appuie sur des observations scientifiques",
                "C'est un jeu sans lien scientifique",
                "C'est une recette de cuisine",
                "C'est une histoire de fiction"
            ],
            "C'est un concept qui s'appuie sur des observations scientifiques",
            "La physique-chimie etudie des concepts qui reposent sur des faits."
        ),
        vf_question(
            qid, 2,
            f"[Physique-Chimie 6eme] Comprendre {focus} passe par la verification des resultats.",
            True,
            "Verifier des resultats permet d'apprendre et d'eviter les erreurs."
        ),
        qcm_question(
            qid, 3,
            f"[Physique-Chimie 6eme] Quelle action est la moins appropriee pour etudier {focus} ?",
            [
                "Ignorer les observations",
                "Analyser les donnees",
                "Poser des questions",
                "Comparer des experiences"
            ],
            "Ignorer les observations",
            "Ignorer des observations n'est pas une bonne pratique scientifique."
        ),
        qcm_question(
            qid, 4,
            f"[Physique-Chimie 6eme] Pour mieux travailler sur {focus}, il faut :",
            [
                "Regarder des exemples concrets",
                "Repondre sans lire la consigne",
                "Melanger des notions au hasard",
                "Faire n'importe quoi"
            ],
            "Regarder des exemples concrets",
            "Les exemples concrets aident a comprendre un concept scientifique."
        ),
        vf_question(
            qid, 5,
            f"[Physique-Chimie 6eme] Une bonne description de {focus} utilise des termes precis.",
            True,
            "En science, des termes precis sont necessaires pour etre clair."
        ),
        qcm_question(
            qid, 6,
            f"[Physique-Chimie 6eme] Quel outil aide a expliquer {focus} ?",
            [
                "Un schema",
                "Une chanson",
                "Un dessin sans sens",
                "Un mensonge"
            ],
            "Un schema",
            "Un schema permet de visualiser un concept scientifique."
        ),
        qcm_question(
            qid, 7,
            f"[Physique-Chimie 6eme] La meilleure strategie pour etudier {focus} est :",
            [
                "Verifier et commenter les resultats",
                "Ignorer les erreurs",
                "Repondre au hasard",
                "Ne rien noter"
            ],
            "Verifier et commenter les resultats",
            "Comparer et commenter les resultats aide a progresser en science."
        ),
        vf_question(
            qid, 8,
            f"[Physique-Chimie 6eme] Parfois, il est utile de relire un schema pour comprendre {focus}.",
            True,
            "Relire les representations visuelles aide a clarifier les idees."
        ),
    ]
    return (qid, title, "Physique-Chimie", "6eme", questions)

THEME_CATEGORY_MAP = {
    "mesures et unites": "measurement",
    "instruments de mesure": "measurement",
    "unites et grandeurs": "measurement",
    "temperature et chaleur": "measurement",
    "pression et gaz": "measurement",
    "masse et volume": "measurement",
    "densite et flottabilite": "measurement",
    "grandeurs physiques": "measurement",
    "mouvements et forces": "motion_energy",
    "energie et conversion": "motion_energy",
    "energie et transfert": "motion_energy",
    "travail et puissance": "motion_energy",
    "bilan energetique": "motion_energy",
    "sources d energie": "motion_energy",
    "electricite": "electricity",
    "piles et circuits": "electricity",
    "conducteurs et isolants": "electricity",
    "magnetisme et aimants": "electricity",
    "lumiere et optique": "waves",
    "ondes et signaux": "waves",
    "sons et signaux": "waves",
    "etats de la matiere": "matter",
    "atomes et molecules": "atomic",
    "corps purs et melanges": "matter",
    "separation des melanges": "matter",
    "reactions chimiques": "matter",
    "reactions et transformations": "matter",
    "conservation de la matiere": "matter",
    "systemes et interactions": "systems",
    "securite au laboratoire": "lab",
    "demarche experimentale": "lab",
    "methodes d observation": "lab",
    "schema et representation": "lab",
    "mesure et incertitude": "lab",
    "prudence au laboratoire": "lab",
    "interpretation des graphiques": "lab",
    "communication scientifique": "meta",
    "communication des resultats": "meta",
    "recherche documentaire": "meta",
    "resolution de problemes": "process",
    "competences transversales": "process",
    "evaluation des acquis": "process",
    "auto-evaluation et metacognition": "process",
    "auto-correction scientifique": "process",
    "evaluation des progres": "process",
    "notions d experience": "meta",
    "retour d experience": "process",
}

THEME_BUILDERS = {
    "measurement": build_measurement_quiz,
    "motion_energy": build_motion_energy_quiz,
    "electricity": build_electricity_quiz,
    "waves": build_waves_quiz,
    "matter": build_matter_quiz,
    "atomic": build_atomic_quiz,
    "lab": build_lab_quiz,
    "meta": build_meta_quiz,
    "systems": build_systems_quiz,
    "process": build_process_quiz,
    "generic": build_generic_phychi_quiz,
}


def build_6eme_phychi_quiz(qid, title, focus):
    category = THEME_CATEGORY_MAP.get(focus.lower(), "generic")
    builder = THEME_BUILDERS.get(category, build_generic_phychi_quiz)
    return builder(qid, title, focus)


def write_quiz_files():
    quizzes_data = get_quizzes_data()
    count = 0
    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(
            qid, title, subject, level, questions,
            source="Eduscol programmes officiels + BOEN",
            programme_ref="<Référence programme officiel à renseigner>",
        )
        answers_payload = make_answers(qid, title, subject, level, questions)

        quiz_payload = normalize_text_payload(quiz_payload)
        answers_payload = normalize_text_payload(answers_payload)

        for dir_path, payload, suffix in [
            (QUIZ_DIR, quiz_payload, "quiz"),
            (ANSWERS_DIR, answers_payload, "answers"),
            (RUNTIME_QUIZ_DIR, quiz_payload, "quiz"),
            (RUNTIME_ANSWERS_DIR, answers_payload, "answers"),
        ]:
            path = os.path.join(dir_path, f"{qid}.json")
            save_json(payload, path)

        count += 1
        print(f"  OK {qid}.json - {title}")

    print(f"\n{count} quiz generes (+ {count} reponses)")


if __name__ == "__main__":
    print("Generating quizzes from template IA...")
    write_quiz_files()
