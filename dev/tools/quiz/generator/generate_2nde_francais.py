#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Génération des quiz Français 2nde – Template IA
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

BASENAME = "francais_2nde_quizzes"

GEN_OUTPUT_DIR = os.path.join(SCRIPT_DIR, BASENAME)
GEN_QUIZ_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz")
GEN_ANSWERS_DIR = os.path.join(GEN_OUTPUT_DIR, "quiz_answers")

RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

os.makedirs(GEN_QUIZ_DIR, exist_ok=True)
os.makedirs(GEN_ANSWERS_DIR, exist_ok=True)
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
        "Ã©": "é", "Ã¨": "è", "Ãª": "ê", "Ã«": "ë", "Ã ": "à", "Ã¢": "â",
        "Ã´": "ô", "Ã»": "û", "Ã¹": "ù", "Ã®": "î", "Ã¯": "ï", "Ã§": "ç",
        "Ã‰": "É", "Ã€": "À", "Ã‡": "Ç", "Å“": "œ", "Â": "", "â€™": "’",
        "â€œ": "“", "â€\x9d": "”", "â€“": "–", "â—": "—", "â€¦": "…",
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


def normalize_question_type(question_type):
    qt = str(question_type or "").strip().lower().replace("_", "-")
    if qt in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if qt == "qcm":
        return "qcm"
    return "vrai-faux"


def build_true_false_statement(question_text, correct_answer, explanation):
    answer = str(correct_answer or "").strip()
    detail = str(explanation or "").strip()
    if answer:
        return f"La bonne réponse attendue est : {answer}."
    if detail:
        return detail if detail.endswith((".", "!", "?")) else f"{detail}."
    prompt = str(question_text or "").strip()
    return prompt if prompt else "Cette affirmation est à évaluer."


def make_quiz(qid, title, subject, level, questions,
              source="Eduscol + BOEN",
              programme_ref=""):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    quiz_questions = []
    for question in questions:
        raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = normalize_question_type(raw_type)
        if qtype == "qcm":
            sanitized = {
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            }
        else:
            question_text = str(question.get("question", ""))
            if raw_type not in {"vrai-faux", "vrai faux"}:
                question_text = build_true_false_statement(
                    question.get("question", ""),
                    question.get("correct_answer", ""),
                    question.get("explanation", ""),
                )
            sanitized = {
                "type": "vrai-faux",
                "question": question_text,
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


def write_quiz_files():
    count = 0
    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(
            qid, title, subject, level, questions,
            source="Eduscol programmes officiels + BOEN",
            programme_ref="BO spécial n°1 du 22 janvier 2019 (LGT) + Eduscol Français seconde",
        )
        answers_payload = make_answers(qid, title, subject, level, questions)

        quiz_payload = normalize_text_payload(quiz_payload)
        answers_payload = normalize_text_payload(answers_payload)

        for dir_path, payload, suffix in [
            (GEN_QUIZ_DIR, quiz_payload, "quiz"),
            (GEN_ANSWERS_DIR, answers_payload, "answers"),
            (RUNTIME_QUIZ_DIR, quiz_payload, "quiz"),
            (RUNTIME_ANSWERS_DIR, answers_payload, "answers"),
        ]:
            path = os.path.join(dir_path, f"{qid}.json")
            with open(path, "w", encoding="utf-8", newline="\n") as f:
                json.dump(payload, f, ensure_ascii=False, indent=2)
                f.write("\n")

        count += 1
        print(f"  [OK] {qid}.json - {title}")

    print(f"\n[OK] {count} quiz generes (+ {count} reponses)")

quizzes_data = [
(
1643,
"Diagnostic 2nde Français - Lecture narrative et focalisation",
"Français",
"2nde",
[
{
"id": "1643_1",
"type": "qcm",
"question": "Dans un récit à la première personne, quel indice permet le plus souvent d'identifier un narrateur personnage ?",
"options": [
"L'emploi du pronom « je » pour raconter les actions",
"La présence exclusive de dialogues",
"L'absence de repères temporels",
"L'utilisation du présent de vérité générale"
],
"correct_option": "L'emploi du pronom « je » pour raconter les actions",
"explanation": "Le narrateur personnage participe à l'histoire et la raconte de son point de vue. L'emploi de « je » est un marqueur fréquent de cette implication."
},
{
"id": "1643_2",
"type": "vrai-faux",
"question": "En focalisation interne, le lecteur connaît toutes les pensées de tous les personnages.",
"correct": False,
"explanation": "La focalisation interne limite l'information à ce qu'un personnage perçoit, pense ou sait. Le lecteur n'a donc pas accès à toutes les consciences."
},
{
"id": "1643_3",
"type": "texte",
"question": "Donne le nom de la focalisation quand le narrateur en sait plus que les personnages.",
"correct_answer": "focalisation zéro",
"explanation": "La focalisation zéro correspond à un narrateur omniscient. Il connaît le passé, l'avenir et les pensées de plusieurs personnages."
},
{
"id": "1643_4",
"type": "qcm",
"question": "Quel effet produit surtout la focalisation interne dans un roman ?",
"options": [
"Une distance froide et objective",
"Une immersion dans la subjectivité d'un personnage",
"Une description uniquement historique",
"Une suppression de toute émotion"
],
"correct_option": "Une immersion dans la subjectivité d'un personnage",
"explanation": "La focalisation interne place le lecteur au plus près de l'expérience d'un personnage. Elle renforce l'empathie et l'identification."
},
{
"id": "1643_5",
"type": "vrai-faux",
"question": "Un récit peut alterner plusieurs focalisations selon les passages.",
"correct": True,
"explanation": "Beaucoup de récits changent de point de vue pour varier l'information donnée au lecteur. On parle alors de variations de focalisation."
},
{
"id": "1643_6",
"type": "texte",
"question": "Comment appelle-t-on un narrateur absent de l'histoire qu'il raconte ?",
"correct_answer": "narrateur hétérodiégétique",
"explanation": "Le narrateur hétérodiégétique n'est pas personnage de l'intrigue. Il raconte des événements auxquels il ne participe pas."
},
{
"id": "1643_7",
"type": "qcm",
"question": "Quel énoncé correspond à une focalisation externe ?",
"options": [
"Il pensa qu'il avait enfin compris son erreur.",
"Je me sentais humilié devant toute la classe.",
"Paul serra les poings et fixa la porte sans parler.",
"Elle savait déjà ce qui arriverait dix ans plus tard."
],
"correct_option": "Paul serra les poings et fixa la porte sans parler.",
"explanation": "La focalisation externe décrit ce qui est observable de l'extérieur, sans entrer dans les pensées. On voit les gestes, pas l'intériorité."
},
{
"id": "1643_8",
"type": "vrai-faux",
"question": "Le choix de la focalisation influence l'interprétation d'un personnage par le lecteur.",
"correct": True,
"explanation": "Selon les informations transmises, le lecteur juge différemment les personnages. La focalisation oriente la compréhension du récit."
}
]
),
(
1644,
"Diagnostic 2nde Français - Théâtre et double énonciation",
"Français",
"2nde",
[
{
"id": "1644_1",
"type": "qcm",
"question": "Au théâtre, que signifie la double énonciation ?",
"options": [
"Le texte est écrit par deux auteurs",
"Un personnage parle à un autre personnage et, en même temps, au public",
"La pièce possède deux actes seulement",
"Les répliques sont dites deux fois"
],
"correct_option": "Un personnage parle à un autre personnage et, en même temps, au public",
"explanation": "La parole théâtrale fonctionne sur deux plans : dialogue interne à la scène et réception par les spectateurs. C'est un principe central du théâtre."
},
{
"id": "1644_2",
"type": "vrai-faux",
"question": "Une didascalie est destinée prioritairement à la mise en scène et au jeu des acteurs.",
"correct": True,
"explanation": "Les didascalies donnent des indications de décor, de gestes, de ton ou de déplacements. Elles guident la représentation."
},
{
"id": "1644_3",
"type": "texte",
"question": "Comment nomme-t-on une longue réplique prononcée par un seul personnage ?",
"correct_answer": "tirade",
"explanation": "La tirade est une réplique développée, souvent argumentative ou expressive. Elle permet de déployer une pensée ou une émotion."
},
{
"id": "1644_4",
"type": "qcm",
"question": "Quel terme désigne un personnage qui parle seul sur scène pour exprimer ses pensées ?",
"options": [
"Aparté",
"Monologue",
"Stichomythie",
"Prologue"
],
"correct_option": "Monologue",
"explanation": "Le monologue est une parole prolongée d'un personnage seul en scène. Il révèle sa réflexion, son conflit intérieur ou ses intentions."
},
{
"id": "1644_5",
"type": "vrai-faux",
"question": "L'aparté est entendu par tous les personnages présents sur scène.",
"correct": False,
"explanation": "Par convention, l'aparté est censé être entendu par le public mais pas par les autres personnages. Il crée souvent un effet comique ou critique."
},
{
"id": "1644_6",
"type": "texte",
"question": "Comment appelle-t-on l'enchaînement très rapide de répliques brèves au théâtre ?",
"correct_answer": "stichomythie",
"explanation": "La stichomythie dynamise l'échange et met en scène un affrontement verbal. Elle rend le conflit particulièrement vif."
},
{
"id": "1644_7",
"type": "qcm",
"question": "Dans une comédie classique, quelle fonction est la plus fréquente des quiproquos ?",
"options": [
"Créer de la tension tragique irréversible",
"Produire un effet comique fondé sur le malentendu",
"Présenter un commentaire historique",
"Remplacer les didascalies"
],
"correct_option": "Produire un effet comique fondé sur le malentendu",
"explanation": "Le quiproquo repose sur une confusion de sens ou d'identité. Ce décalage entre ce que savent les personnages et le public déclenche le rire."
},
{
"id": "1644_8",
"type": "vrai-faux",
"question": "Le texte théâtral est conçu pour être joué, pas seulement lu.",
"correct": True,
"explanation": "Le théâtre est un art de la scène : voix, corps, espace et rythme participent au sens. La représentation complète la lecture."
}
]
),
(
1645,
"Diagnostic 2nde Français - Poésie et figures de style",
"Français",
"2nde",
[
{
"id": "1645_1",
"type": "qcm",
"question": "Quelle figure de style associe deux réalités grâce à un outil comparatif comme « comme » ?",
"options": [
"La métaphore",
"La comparaison",
"L'hyperbole",
"L'antithèse"
],
"correct_option": "La comparaison",
"explanation": "La comparaison rapproche explicitement deux éléments à l'aide d'un comparatif. Elle met en valeur une ressemblance précise."
},
{
"id": "1645_2",
"type": "vrai-faux",
"question": "Un sonnet est traditionnellement composé de quatorze vers.",
"correct": True,
"explanation": "Le sonnet classique comporte 14 vers, souvent répartis en deux quatrains et deux tercets. Cette forme impose une forte contrainte d'écriture."
},
{
"id": "1645_3",
"type": "texte",
"question": "Comment appelle-t-on la répétition d'un même son consonantique dans un vers ?",
"correct_answer": "allitération",
"explanation": "L'allitération est la répétition d'une consonne pour créer un effet sonore. Elle peut suggérer douceur, violence ou insistance."
},
{
"id": "1645_4",
"type": "qcm",
"question": "Quel registre est surtout mobilisé quand un poème insiste sur la souffrance, la plainte et la perte ?",
"options": [
"Le registre lyrique",
"Le registre comique",
"Le registre didactique",
"Le registre épique"
],
"correct_option": "Le registre lyrique",
"explanation": "Le registre lyrique exprime les émotions personnelles, notamment l'amour, la mélancolie ou la nostalgie. Il privilégie la subjectivité."
},
{
"id": "1645_5",
"type": "vrai-faux",
"question": "Une métaphore contient toujours un outil comparatif explicite.",
"correct": False,
"explanation": "Contrairement à la comparaison, la métaphore rapproche deux réalités sans outil comparatif. Le lien est implicite."
},
{
"id": "1645_6",
"type": "texte",
"question": "Comment nomme-t-on la répétition d'un mot ou groupe de mots en début de vers successifs ?",
"correct_answer": "anaphore",
"explanation": "L'anaphore crée un rythme insistant et structure le poème. Elle met en relief une idée essentielle."
},
{
"id": "1645_7",
"type": "qcm",
"question": "Dans un poème, quelle fonction peut avoir l'enjambement ?",
"options": [
"Couper systématiquement le sens à la fin de chaque vers",
"Prolonger une phrase au-delà de la fin du vers",
"Supprimer toute ponctuation",
"Remplacer les rimes par des refrains"
],
"correct_option": "Prolonger une phrase au-delà de la fin du vers",
"explanation": "L'enjambement dépasse la frontière du vers et crée un effet de continuité. Il peut accélérer ou nuancer le rythme de lecture."
},
{
"id": "1645_8",
"type": "vrai-faux",
"question": "Le vers libre se caractérise par l'absence d'un mètre régulier imposé.",
"correct": True,
"explanation": "Le vers libre s'affranchit des règles fixes de syllabes et parfois de rimes. Il conserve pourtant une organisation poétique du langage."
}
]
),
(
1646,
"Diagnostic 2nde Français - Argumentation et raisonnement",
"Français",
"2nde",
[
{
"id": "1646_1",
"type": "qcm",
"question": "Dans un texte argumentatif, la thèse correspond à :",
"options": [
"Un exemple isolé",
"L'idée principale que l'auteur défend",
"La conclusion obligatoire du récit",
"Une citation sans commentaire"
],
"correct_option": "L'idée principale que l'auteur défend",
"explanation": "La thèse est la position globale de l'auteur sur un sujet. Les arguments servent à la justifier."
},
{
"id": "1646_2",
"type": "vrai-faux",
"question": "Un argument est plus solide lorsqu'il est accompagné d'un exemple précis.",
"correct": True,
"explanation": "L'exemple illustre l'argument et le rend concret pour le lecteur. Il renforce la crédibilité du raisonnement."
},
{
"id": "1646_3",
"type": "texte",
"question": "Quel mot de liaison exprime une opposition dans un raisonnement ?",
"correct_answer": "cependant",
"explanation": "Un connecteur d'opposition permet de nuancer ou de contredire une idée précédente. « Cependant » est un marqueur classique."
},
{
"id": "1646_4",
"type": "qcm",
"question": "Quel enchaînement est le plus logique dans un paragraphe argumentatif ?",
"options": [
"Exemple → Thèse → Connecteur",
"Argument → Exemple → Bilan partiel",
"Conclusion → Question → Définition",
"Citation brute → Digression → Résumé"
],
"correct_option": "Argument → Exemple → Bilan partiel",
"explanation": "Cette structure facilite la progression : on affirme, on prouve, puis on tire une petite conclusion. Elle aide à la clarté argumentative."
},
{
"id": "1646_5",
"type": "vrai-faux",
"question": "« Donc » est un connecteur qui marque la conséquence.",
"correct": True,
"explanation": "« Donc » introduit un résultat ou une conclusion tirée d'éléments précédents. Il sert à enchaîner logiquement les idées."
},
{
"id": "1646_6",
"type": "texte",
"question": "Comment appelle-t-on le raisonnement qui part d'un cas particulier pour aboutir à une idée générale ?",
"correct_answer": "induction",
"explanation": "L'induction observe des faits particuliers pour formuler une généralisation. C'est l'inverse d'un raisonnement déductif."
},
{
"id": "1646_7",
"type": "qcm",
"question": "Quel procédé cherche à convaincre en touchant les émotions du lecteur ?",
"options": [
"L'argument d'autorité strictement scientifique",
"L'appel aux sentiments",
"La neutralité descriptive totale",
"La suppression des exemples"
],
"correct_option": "L'appel aux sentiments",
"explanation": "L'argumentation peut jouer sur la sensibilité pour persuader. Ce levier affectif complète parfois la démonstration rationnelle."
},
{
"id": "1646_8",
"type": "vrai-faux",
"question": "Réfuter un argument consiste à montrer ses limites ou son invalidité.",
"correct": True,
"explanation": "La réfutation analyse l'argument adverse pour en révéler les faiblesses. Elle est essentielle dans un débat argumenté."
}
]
),
(
1647,
"Diagnostic 2nde Français - Orthographe grammaticale et accords",
"Français",
"2nde",
[
{
"id": "1647_1",
"type": "qcm",
"question": "Dans « Les élèves attentifs écoutent », quel mot commande l'accord du verbe ?",
"options": [
"attentifs",
"écoutent",
"élèves",
"les"
],
"correct_option": "élèves",
"explanation": "Le verbe s'accorde avec le sujet du verbe. Ici, le sujet est « élèves », au pluriel."
},
{
"id": "1647_2",
"type": "vrai-faux",
"question": "Dans « Elles se sont parlé », le participe passé « parlé » reste invariable.",
"correct": True,
"explanation": "Avec le verbe « parler » construit avec « à », le pronom réfléchi est COI. Le participe passé employé avec « être » dans ce cas reste invariable."
},
{
"id": "1647_3",
"type": "texte",
"question": "Écris l'adjectif correctement : « des décisions (important) ».",
"correct_answer": "importantes",
"explanation": "L'adjectif qualificatif s'accorde en genre et en nombre avec le nom qu'il qualifie. « Décisions » est féminin pluriel."
},
{
"id": "1647_4",
"type": "qcm",
"question": "Choisis la phrase correcte.",
"options": [
"Les fleurs que j'ai cueilli sont fanées.",
"Les fleurs que j'ai cueillies sont fanées.",
"Les fleurs que j'ai cueillis sont fanées.",
"Les fleurs que j'ai cueillie sont fanées."
],
"correct_option": "Les fleurs que j'ai cueillies sont fanées.",
"explanation": "Avec « avoir », le participe passé s'accorde avec le COD si celui-ci est placé avant. « Que » reprend « fleurs », féminin pluriel."
},
{
"id": "1647_5",
"type": "vrai-faux",
"question": "Le verbe s'accorde toujours avec le nom le plus proche.",
"correct": False,
"explanation": "Le verbe s'accorde avec son sujet grammatical, pas avec le nom le plus proche. La proximité peut tromper, surtout dans les phrases longues."
},
{
"id": "1647_6",
"type": "texte",
"question": "Complète correctement : « Ils se sont ___ de ce problème » (rendre compte).",
"correct_answer": "rendu compte",
"explanation": "Dans « se rendre compte de », le pronom « se » est COI. Le participe passé « rendu » reste donc invariable."
},
{
"id": "1647_7",
"type": "qcm",
"question": "Quelle phrase contient un accord correct du participe passé avec « être » ?",
"options": [
"Elles sont parti tôt.",
"Elles sont parties tôt.",
"Elles sont partiez tôt.",
"Elles sont partis tôt."
],
"correct_option": "Elles sont parties tôt.",
"explanation": "Avec l'auxiliaire « être », le participe passé s'accorde en genre et en nombre avec le sujet. « Elles » impose le féminin pluriel."
},
{
"id": "1647_8",
"type": "vrai-faux",
"question": "Dans « ni Paul ni ses amis ne vient », l'accord est correct.",
"correct": False,
"explanation": "Le verbe doit s'accorder au pluriel ici : « ni Paul ni ses amis ne viennent ». Le second terme est pluriel et entraîne l'accord."
}
]
),
(
1648,
"Diagnostic 2nde Français - Temps du récit",
"Français",
"2nde",
[
{
"id": "1648_1",
"type": "qcm",
"question": "Dans un récit au passé, quel temps sert le plus souvent à exprimer l'action principale et ponctuelle ?",
"options": [
"L'imparfait",
"Le passé simple",
"Le présent",
"Le futur antérieur"
],
"correct_option": "Le passé simple",
"explanation": "Le passé simple marque en général les actions de premier plan. Il fait avancer l'intrigue."
},
{
"id": "1648_2",
"type": "vrai-faux",
"question": "L'imparfait sert souvent à décrire un cadre, une habitude ou une action en cours dans le passé.",
"correct": True,
"explanation": "L'imparfait installe l'arrière-plan du récit. Il exprime la durée, la répétition ou la description."
},
{
"id": "1648_3",
"type": "texte",
"question": "Donne le nom du temps utilisé pour exprimer une action antérieure à une autre action passée : « il avait terminé ». ",
"correct_answer": "plus-que-parfait",
"explanation": "Le plus-que-parfait situe une action avant un repère passé. Il crée un effet de retour en arrière temporel."
},
{
"id": "1648_4",
"type": "qcm",
"question": "Quelle phrase illustre le mieux la répartition classique imparfait / passé simple ?",
"options": [
"Il marchait dans la rue quand soudain la porte s'ouvrit.",
"Il marcha dans la rue quand soudain la porte s'ouvrait.",
"Il marchait dans la rue quand soudain la porte s'ouvrait.",
"Il marcha dans la rue quand soudain la porte s'ouvre."
],
"correct_option": "Il marchait dans la rue quand soudain la porte s'ouvrit.",
"explanation": "L'imparfait pose l'action de fond (« marchait »), tandis que le passé simple marque l'événement soudain (« s'ouvrit »)."
},
{
"id": "1648_5",
"type": "vrai-faux",
"question": "Le passé simple est majoritairement utilisé à l'oral courant.",
"correct": False,
"explanation": "Le passé simple appartient surtout à la langue écrite littéraire. À l'oral, on emploie plus fréquemment le passé composé."
},
{
"id": "1648_6",
"type": "texte",
"question": "Quel temps emploie-t-on souvent pour raconter des actions achevées dans la conversation courante ?",
"correct_answer": "passé composé",
"explanation": "Le passé composé est le temps usuel du récit oral pour des faits terminés. Il concurrence le passé simple dans la langue courante."
},
{
"id": "1648_7",
"type": "qcm",
"question": "Dans « Il lut la lettre, puis il resta silencieux », la valeur principale du passé simple est :",
"options": [
"Description durable",
"Action brève de premier plan",
"Hypothèse",
"Ordre"
],
"correct_option": "Action brève de premier plan",
"explanation": "Le passé simple met en relief des actions successives qui structurent l'histoire. Il sert la progression narrative."
},
{
"id": "1648_8",
"type": "vrai-faux",
"question": "Dans un récit, on peut utiliser le présent de narration pour vivifier une scène passée.",
"correct": True,
"explanation": "Le présent de narration crée un effet de proximité et de dynamisme. Il actualise une action pourtant située dans le passé."
}
]
),
(
1649,
"Diagnostic 2nde Français - Phrase complexe et subordination",
"Français",
"2nde",
[
{
"id": "1649_1",
"type": "qcm",
"question": "Une phrase complexe est une phrase qui :",
"options": [
"contient au moins deux verbes conjugués",
"contient uniquement des adjectifs",
"se termine toujours par un point d'exclamation",
"ne comporte jamais de conjonction"
],
"correct_option": "contient au moins deux verbes conjugués",
"explanation": "La phrase complexe associe plusieurs propositions. La présence d'au moins deux verbes conjugués est un indice fréquent."
},
{
"id": "1649_2",
"type": "vrai-faux",
"question": "Dans « Je pense que tu as raison », « que tu as raison » est une subordonnée conjonctive complétive.",
"correct": True,
"explanation": "La subordonnée introduite par « que » complète le verbe principal « pense ». Elle remplit la fonction de complément."
},
{
"id": "1649_3",
"type": "texte",
"question": "Comment appelle-t-on une subordonnée introduite par « qui », « que », « dont » ou « où » ?",
"correct_answer": "subordonnée relative",
"explanation": "La subordonnée relative complète un nom antécédent. Elle apporte une précision sur ce nom."
},
{
"id": "1649_4",
"type": "qcm",
"question": "Quel lien logique exprime « parce que » ?",
"options": [
"La conséquence",
"La cause",
"L'opposition",
"La concession"
],
"correct_option": "La cause",
"explanation": "« Parce que » introduit la raison d'un fait. C'est un connecteur de causalité."
},
{
"id": "1649_5",
"type": "vrai-faux",
"question": "« Bien que » introduit généralement une idée de concession.",
"correct": True,
"explanation": "La concession oppose un fait à une attente logique. « Bien que » signale cette relation argumentative."
},
{
"id": "1649_6",
"type": "texte",
"question": "Donne un connecteur de conséquence fréquemment utilisé dans un raisonnement.",
"correct_answer": "donc",
"explanation": "Un connecteur de conséquence marque le résultat d'une cause ou d'un raisonnement. « Donc » est l'un des plus courants."
},
{
"id": "1649_7",
"type": "qcm",
"question": "Dans « L'élève qui révise progresse », quelle est la nature de « qui révise » ?",
"options": [
"Proposition indépendante",
"Subordonnée relative",
"Subordonnée circonstancielle de temps",
"Incise"
],
"correct_option": "Subordonnée relative",
"explanation": "« Qui révise » complète l'antécédent « l'élève » et est introduite par le pronom relatif « qui ». C'est une relative."
},
{
"id": "1649_8",
"type": "vrai-faux",
"question": "Une proposition subordonnée ne peut jamais être supprimée sans modifier le sens de la phrase.",
"correct": False,
"explanation": "Certaines subordonnées sont essentielles, d'autres apportent une précision facultative. Leur suppression peut être possible selon le cas."
}
]
),
(
1650,
"Diagnostic 2nde Français - Registres littéraires",
"Français",
"2nde",
[
{
"id": "1650_1",
"type": "qcm",
"question": "Quel registre cherche principalement à susciter la peur et la pitié ?",
"options": [
"Le registre tragique",
"Le registre comique",
"Le registre épistolaire",
"Le registre didactique"
],
"correct_option": "Le registre tragique",
"explanation": "Le tragique met en scène une fatalité ou une impasse qui dépasse le personnage. Il provoque des émotions intenses chez le lecteur."
},
{
"id": "1650_2",
"type": "vrai-faux",
"question": "Le registre comique peut reposer sur le langage, les gestes, les situations ou les caractères.",
"correct": True,
"explanation": "Le comique est pluriel : mots, comportements, répétitions, quiproquos ou contrastes peuvent faire rire. Ces procédés se combinent souvent."
},
{
"id": "1650_3",
"type": "texte",
"question": "Quel registre vise à dénoncer en ridiculisant des défauts ou des comportements ?",
"correct_answer": "satirique",
"explanation": "Le registre satirique critique une personne, un groupe ou une idée en utilisant l'ironie, l'exagération ou la caricature."
},
{
"id": "1650_4",
"type": "qcm",
"question": "Quel indice est le plus typique du registre polémique ?",
"options": [
"Un vocabulaire neutre et objectif",
"Une argumentation vive avec attaques et réfutations",
"Une narration exclusivement descriptive",
"Une absence totale de jugement"
],
"correct_option": "Une argumentation vive avec attaques et réfutations",
"explanation": "Le registre polémique assume l'affrontement d'idées. Il emploie un ton combatif pour convaincre ou disqualifier une position adverse."
},
{
"id": "1650_5",
"type": "vrai-faux",
"question": "Un même texte peut mêler plusieurs registres.",
"correct": True,
"explanation": "Les registres ne sont pas exclusifs : un texte peut passer du lyrique au tragique ou mêler comique et critique. Cela enrichit l'interprétation."
},
{
"id": "1650_6",
"type": "texte",
"question": "Quel registre met en avant l'expression personnelle des sentiments ?",
"correct_answer": "lyrique",
"explanation": "Le registre lyrique exprime une subjectivité forte, souvent à la première personne. Il valorise les émotions et la musicalité."
},
{
"id": "1650_7",
"type": "qcm",
"question": "Dans une scène où un personnage ignore un danger que le public connaît, l'effet dominant peut être :",
"options": [
"Le registre épique",
"Le registre tragique",
"Le registre didactique",
"Le registre administratif"
],
"correct_option": "Le registre tragique",
"explanation": "Ce décalage d'information crée une tension dramatique et annonce un destin défavorable. C'est un ressort classique du tragique."
},
{
"id": "1650_8",
"type": "vrai-faux",
"question": "Le registre pathétique cherche à émouvoir en montrant la souffrance ou la vulnérabilité.",
"correct": True,
"explanation": "Le pathétique suscite la compassion du lecteur. Il met en avant la douleur, la faiblesse ou l'injustice subie."
}
]
),
(
1651,
"Diagnostic 2nde Français - Genres et mouvements littéraires",
"Français",
"2nde",
[
{
"id": "1651_1",
"type": "qcm",
"question": "À quel mouvement associe-t-on volontiers Victor Hugo dans l'histoire littéraire ?",
"options": [
"Le classicisme",
"Le romantisme",
"Le naturalisme",
"Le surréalisme"
],
"correct_option": "Le romantisme",
"explanation": "Victor Hugo est une figure majeure du romantisme au XIXe siècle. Ce mouvement valorise la sensibilité, l'histoire et la liberté créatrice."
},
{
"id": "1651_2",
"type": "vrai-faux",
"question": "Le naturalisme cherche à observer la société et les comportements avec une méthode proche de l'enquête.",
"correct": True,
"explanation": "Le naturalisme, notamment avec Zola, revendique une démarche d'observation du réel. Il étudie les milieux sociaux et les déterminismes."
},
{
"id": "1651_3",
"type": "texte",
"question": "Comment nomme-t-on le genre qui met en scène des personnages sur scène avec didascalies et répliques ?",
"correct_answer": "théâtre",
"explanation": "Le théâtre est un genre destiné à la représentation. Son écriture combine dialogues, indications scéniques et structure en actes ou scènes."
},
{
"id": "1651_4",
"type": "qcm",
"question": "Quel trait caractérise le classicisme au XVIIe siècle ?",
"options": [
"Le refus de toute règle",
"La recherche de l'ordre, de la mesure et de la clarté",
"L'écriture automatique",
"La priorité à la science-fiction"
],
"correct_option": "La recherche de l'ordre, de la mesure et de la clarté",
"explanation": "Le classicisme valorise la raison, la maîtrise et l'équilibre. Il s'appuie sur des règles formelles et un idéal de clarté."
},
{
"id": "1651_5",
"type": "vrai-faux",
"question": "Le surréalisme explore l'inconscient et les associations inattendues.",
"correct": True,
"explanation": "Le surréalisme veut dépasser la logique ordinaire. Il privilégie l'imaginaire, le rêve et les rapprochements surprenants."
},
{
"id": "1651_6",
"type": "texte",
"question": "Quel mouvement du XIXe siècle met fortement l'accent sur l'émotion, la nature et le moi ?",
"correct_answer": "romantisme",
"explanation": "Le romantisme place au centre l'expression de la subjectivité. Il privilégie le lyrisme, l'élan personnel et la sensibilité."
},
{
"id": "1651_7",
"type": "qcm",
"question": "Quel genre repose sur une narration d'événements vécus par des personnages dans un univers fictif ?",
"options": [
"Le roman",
"La dissertation",
"Le mode d'emploi",
"Le compte rendu administratif"
],
"correct_option": "Le roman",
"explanation": "Le roman raconte une histoire construite autour de personnages et d'une intrigue. Il peut adopter des formes et des époques variées."
},
{
"id": "1651_8",
"type": "vrai-faux",
"question": "Un mouvement littéraire correspond uniquement à une période chronologique sans idées communes.",
"correct": False,
"explanation": "Un mouvement regroupe des auteurs et des œuvres partageant des choix esthétiques et des enjeux communs. La période historique ne suffit pas à le définir."
}
]
),
(
1652,
"Diagnostic 2nde Français - Synthèse des compétences de début d'année",
"Français",
"2nde",
[
{
"id": "1652_1",
"type": "qcm",
"question": "Dans une introduction de commentaire, quelle étape est attendue en priorité ?",
"options": [
"Donner uniquement son opinion personnelle",
"Présenter le texte et formuler une problématique",
"Recopier la conclusion",
"Énumérer des figures sans lien"
],
"correct_option": "Présenter le texte et formuler une problématique",
"explanation": "L'introduction situe le texte (auteur, œuvre, contexte utile) puis annonce l'angle d'analyse. La problématique guide tout le développement."
},
{
"id": "1652_2",
"type": "vrai-faux",
"question": "Dans un paragraphe d'analyse littéraire, il est conseillé de citer le texte et d'expliquer l'effet produit.",
"correct": True,
"explanation": "Une analyse efficace articule preuve et interprétation. La citation doit être brève, pertinente et commentée."
},
{
"id": "1652_3",
"type": "texte",
"question": "Quel terme désigne l'idée directrice qui organise une lecture analytique ?",
"correct_answer": "problématique",
"explanation": "La problématique pose la question centrale à laquelle l'analyse répond. Elle donne une cohérence à l'ensemble du devoir."
},
{
"id": "1652_4",
"type": "qcm",
"question": "Quel choix de connecteur convient le mieux pour introduire un second argument allant dans le même sens ?",
"options": [
"Cependant",
"De plus",
"Au contraire",
"Néanmoins"
],
"correct_option": "De plus",
"explanation": "« De plus » ajoute un élément convergent. C'est un connecteur d'addition utile pour renforcer une démonstration."
},
{
"id": "1652_5",
"type": "vrai-faux",
"question": "La conclusion d'un devoir doit simplement répéter mot à mot l'introduction.",
"correct": False,
"explanation": "La conclusion répond à la problématique en synthétisant les acquis de l'analyse. Elle reformule, sans copier, et peut ouvrir sur une perspective."
},
{
"id": "1652_6",
"type": "texte",
"question": "Donne le nom du procédé qui consiste à reprendre les mots exacts d'un texte entre guillemets.",
"correct_answer": "citation",
"explanation": "La citation sert d'appui précis à l'analyse. Elle doit être intégrée à la phrase et expliquée."
},
{
"id": "1652_7",
"type": "qcm",
"question": "Dans « Bien qu'il soit tard, elle continue de lire », quelle relation logique est exprimée ?",
"options": [
"La cause",
"La concession",
"Le but",
"La comparaison"
],
"correct_option": "La concession",
"explanation": "La concession oppose un fait à ce qu'on attendrait logiquement. « Bien que » est un marqueur typique de cette relation."
},
{
"id": "1652_8",
"type": "vrai-faux",
"question": "Relire un devoir permet de corriger des accords et d'améliorer la précision des formulations.",
"correct": True,
"explanation": "La relecture est une étape stratégique : elle réduit les fautes et clarifie les idées. Elle améliore directement la qualité de l'expression."
}
]
)
]

if __name__ == "__main__":
    print("Generating Français 2nde quizzes...")
    write_quiz_files()

