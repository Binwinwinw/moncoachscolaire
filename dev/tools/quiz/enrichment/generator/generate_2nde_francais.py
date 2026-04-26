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
            quiz_questions.append({
                "id": question.get("id"),
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
            quiz_questions.append({
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
            {"id": "1643_1","type": "qcm","question": "Dans un récit à la première personne, quel indice permet le plus souvent d'identifier un narrateur personnage ?","options": ["L'emploi du pronom « je » pour raconter les actions","La présence exclusive de dialogues","L'absence de repères temporels","L'utilisation du présent de vérité générale"],"correct_option": "L'emploi du pronom « je » pour raconter les actions","explanation": "Le narrateur personnage participe à l'histoire et la raconte de son point de vue. L'emploi de « je » est un marqueur fréquent de cette implication."},
            {"id": "1643_2","type": "vrai-faux","question": "En focalisation interne, le lecteur connaît toutes les pensées de tous les personnages.","correct": False,"explanation": "La focalisation interne limite l'information à ce qu'un personnage perçoit, pense ou sait. Le lecteur n'a donc pas accès à toutes les consciences."},
            {"id": "1643_3","type": "qcm","question": "Comment appelle-t-on la focalisation où le narrateur en sait plus que les personnages ?","options": ["Focalisation interne","Focalisation externe","Focalisation zéro","Focalisation multiple"],"correct_option": "Focalisation zéro","explanation": "La focalisation zéro correspond à un narrateur omniscient. Il connaît le passé, l'avenir et les pensées de plusieurs personnages."},
            {"id": "1643_4","type": "qcm","question": "Quel effet produit surtout la focalisation interne dans un roman ?","options": ["Une distance froide et objective","Une immersion dans la subjectivité d'un personnage","Une description uniquement historique","Une suppression de toute émotion"],"correct_option": "Une immersion dans la subjectivité d'un personnage","explanation": "La focalisation interne place le lecteur au plus près de l'expérience d'un personnage. Elle renforce l'empathie et l'identification."},
            {"id": "1643_5","type": "vrai-faux","question": "Un récit peut alterner plusieurs focalisations selon les passages.","correct": True,"explanation": "Beaucoup de récits changent de point de vue pour varier l'information donnée au lecteur. On parle alors de variations de focalisation."},
            {"id": "1643_6","type": "qcm","question": "Comment appelle-t-on un narrateur absent de l'histoire qu'il raconte ?","options": [    "Narrateur homodiégétique",    "Narrateur hétérodiégétique",    "Narrateur personnage",    "Narrateur interne"],"correct_option": "Narrateur hétérodiégétique","explanation": "Le narrateur hétérodiégétique n'est pas personnage de l'intrigue. Il raconte des événements auxquels il ne participe pas."},
            {"id": "1643_7","type": "qcm","question": "Quel énoncé correspond à une focalisation externe ?","options": ["Il pensa qu'il avait enfin compris son erreur.","Je me sentais humilié devant toute la classe.","Paul serra les poings et fixa la porte sans parler.","Elle savait déjà ce qui arriverait dix ans plus tard."],"correct_option": "Paul serra les poings et fixa la porte sans parler.","explanation": "La focalisation externe décrit ce qui est observable de l'extérieur, sans entrer dans les pensées. On voit les gestes, pas l'intériorité."},
            {"id": "1643_8","type": "vrai-faux","question": "Le choix de la focalisation influence l'interprétation d'un personnage par le lecteur.","correct": True,"explanation": "Selon les informations transmises, le lecteur juge différemment les personnages. La focalisation oriente la compréhension du récit."}
    ]
    ),
    (
        1644,
        "Diagnostic 2nde Français - Théâtre et double énonciation",
        "Français",
        "2nde",
        [
            {"id": "1644_1","type": "qcm","question": "Au théâtre, que signifie la double énonciation ?","options": ["Le texte est écrit par deux auteurs","Un personnage parle à un autre personnage et, en même temps, au public","La pièce possède deux actes seulement","Les répliques sont dites deux fois"],"correct_option": "Un personnage parle à un autre personnage et, en même temps, au public","explanation": "La parole théâtrale fonctionne sur deux plans : dialogue interne à la scène et réception par les spectateurs. C'est un principe central du théâtre."},
            {"id": "1644_2","type": "vrai-faux","question": "Une didascalie est destinée prioritairement à la mise en scène et au jeu des acteurs.","correct": True,"explanation": "Les didascalies donnent des indications de décor, de gestes, de ton ou de déplacements. Elles guident la représentation."},
            {"id": "1644_3","type": "qcm","question": "Comment nomme-t-on une longue réplique prononcée par un seul personnage ?","options": [    "Monologue",    "Tirade",    "Aparté",    "Dialogue"],"correct_option": "Tirade","explanation": "La tirade est une réplique développée, souvent argumentative ou expressive. Elle permet de déployer une pensée ou une émotion."},
            {"id": "1644_4","type": "qcm","question": "Quel terme désigne un personnage qui parle seul sur scène pour exprimer ses pensées ?","options": ["Aparté","Monologue","Stichomythie","Prologue"],"correct_option": "Monologue","explanation": "Le monologue est une parole prolongée d'un personnage seul en scène. Il révèle sa réflexion, son conflit intérieur ou ses intentions."},
            {"id": "1644_5","type": "vrai-faux","question": "L'aparté est entendu par tous les personnages présents sur scène.","correct": False,"explanation": "Par convention, l'aparté est censé être entendu par le public mais pas par les autres personnages. Il crée souvent un effet comique ou critique."},
            {"id": "1644_6","type": "qcm","question": "Comment appelle-t-on l'enchaînement très rapide de répliques brèves au théâtre ?","options": [    "Tirade",    "Stichomythie",    "Monologue",    "Prologue"],"correct_option": "Stichomythie","explanation": "La stichomythie dynamise l'échange et met en scène un affrontement verbal. Elle rend le conflit particulièrement vif."},
            {"id": "1644_7","type": "qcm","question": "Dans une comédie classique, quelle fonction est la plus fréquente des quiproquos ?","options": ["Créer de la tension tragique irréversible","Produire un effet comique fondé sur le malentendu","Présenter un commentaire historique","Remplacer les didascalies"],"correct_option": "Produire un effet comique fondé sur le malentendu","explanation": "Le quiproquo repose sur une confusion de sens ou d'identité. Ce décalage entre ce que savent les personnages et le public déclenche le rire."},
            {"id": "1644_8","type": "vrai-faux","question": "Le texte théâtral est conçu pour être joué, pas seulement lu.","correct": True,"explanation": "Le théâtre est un art de la scène : voix, corps, espace et rythme participent au sens. La représentation complète la lecture."},
    ]
    ),
    (
        1645,
        "Diagnostic 2nde Français - Poésie et figures de style",
        "Français",
        "2nde",
        [
            {"id": "1645_1","type": "qcm","question": "Quelle figure de style associe deux réalités grâce à un outil comparatif comme « comme » ?","options": ["La métaphore","La comparaison","L'hyperbole","L'antithèse"],"correct_option": "La comparaison","explanation": "La comparaison rapproche explicitement deux éléments à l'aide d'un comparatif. Elle met en valeur une ressemblance précise."},
            {"id": "1645_2","type": "vrai-faux","question": "Un sonnet est traditionnellement composé de quatorze vers.","correct": True,"explanation": "Le sonnet classique comporte 14 vers, souvent répartis en deux quatrains et deux tercets. Cette forme impose une forte contrainte d'écriture."},
            {"id": "1645_3","type": "qcm","question": "Comment appelle-t-on la répétition d'un même son consonantique dans un vers ?","options": ["Allitération","Assonance","Anaphore","Hyperbole"],"correct_option": "Allitération","explanation": "L'allitération est la répétition d'une consonne pour créer un effet sonore. Elle peut suggérer douceur, violence ou insistance."},
            {"id": "1645_4","type": "qcm","question": "Quel registre est surtout mobilisé quand un poème insiste sur la souffrance, la plainte et la perte ?","options": ["Le registre lyrique","Le registre comique","Le registre didactique","Le registre épique"],"correct_option": "Le registre lyrique","explanation": "Le registre lyrique exprime les émotions personnelles, notamment l'amour, la mélancolie ou la nostalgie. Il privilégie la subjectivité."},
            {"id": "1645_5","type": "vrai-faux","question": "Une métaphore contient toujours un outil comparatif explicite.","correct": False,"explanation": "Contrairement à la comparaison, la métaphore rapproche deux réalités sans outil comparatif. Le lien est implicite."},
            {"id": "1645_6","type": "qcm","question": "Comment nomme-t-on la répétition d'un mot ou groupe de mots en début de vers successifs ?","options": [    "Allitération",    "Anaphore",    "Comparaison",    "Antithèse"],"correct_option": "Anaphore","explanation": "L'anaphore crée un rythme insistant et structure le poème. Elle met en relief une idée essentielle."},
            {"id": "1645_7","type": "qcm","question": "Dans un poème, quelle fonction peut avoir l'enjambement ?","options": ["Couper systématiquement le sens à la fin de chaque vers","Prolonger une phrase au-delà de la fin du vers","Supprimer toute ponctuation","Remplacer les rimes par des refrains"],"correct_option": "Prolonger une phrase au-delà de la fin du vers","explanation": "L'enjambement dépasse la frontière du vers et crée un effet de continuité. Il peut accélérer ou nuancer le rythme de lecture."},
            {"id": "1645_8","type": "vrai-faux","question": "Le vers libre se caractérise par l'absence d'un mètre régulier imposé.","correct": True,"explanation": "Le vers libre s'affranchit des règles fixes de syllabes et parfois de rimes. Il conserve pourtant une organisation poétique du langage."}
    ]
    ),
    (
        1646,
        "Diagnostic 2nde Français - Argumentation et raisonnement",
        "Français",
        "2nde",
        [
            {"id": "1646_1","type": "qcm","question": "Dans un texte argumentatif, la thèse correspond à :","options": ["Un exemple isolé","L'idée principale que l'auteur défend","La conclusion obligatoire du récit","Une citation sans commentaire"],"correct_option": "L'idée principale que l'auteur défend","explanation": "La thèse est la position globale de l'auteur sur un sujet. Les arguments servent à la justifier."},
            {"id": "1646_2","type": "vrai-faux","question": "Un argument est plus solide lorsqu'il est accompagné d'un exemple précis.","correct": True,"explanation": "L'exemple illustre l'argument et le rend concret pour le lecteur. Il renforce la crédibilité du raisonnement."},
            {"id": "1646_3","type": "qcm","question": "Quel mot de liaison exprime une opposition dans un raisonnement ?","options": ["Donc","Cependant","Ainsi","En effet"],"correct_option": "Cependant","explanation": "Un connecteur d'opposition permet de nuancer ou de contredire une idée précédente. « Cependant » est un marqueur classique."},
            {"id": "1646_4","type": "qcm","question": "Quel enchaînement est le plus logique dans un paragraphe argumentatif ?","options": ["Exemple → Thèse → Connecteur","Argument → Exemple → Bilan partiel","Conclusion → Question → Définition","Citation brute → Digression → Résumé"],"correct_option": "Argument → Exemple → Bilan partiel","explanation": "Cette structure facilite la progression : on affirme, on prouve, puis on tire une petite conclusion. Elle aide à la clarté argumentative."},
            {"id": "1646_5","type": "vrai-faux","question": "« Donc » est un connecteur qui marque la conséquence.","correct": True,"explanation": "« Donc » introduit un résultat ou une conclusion tirée d'éléments précédents. Il sert à enchaîner logiquement les idées."},
            {"id": "1646_6","type": "qcm","question": "Comment appelle-t-on le raisonnement qui part d'un cas particulier pour aboutir à une idée générale ?","options": [    "Déduction",    "Induction",    "Analogie",    "Exemple"],"correct_option": "Induction","explanation": "L'induction observe des faits particuliers pour formuler une généralisation. C'est l'inverse d'un raisonnement déductif."},
            {"id": "1646_7","type": "qcm","question": "Quel procédé cherche à convaincre en touchant les émotions du lecteur ?","options": ["L'argument d'autorité strictement scientifique","L'appel aux sentiments","La neutralité descriptive totale","La suppression des exemples"],"correct_option": "L'appel aux sentiments","explanation": "L'argumentation peut jouer sur la sensibilité pour persuader. Ce levier affectif complète parfois la démonstration rationnelle."},
            {"id": "1646_8","type": "vrai-faux","question": "Réfuter un argument consiste à montrer ses limites ou son invalidité.","correct": True,"explanation": "La réfutation analyse l'argument adverse pour en révéler les faiblesses. Elle est essentielle dans un débat argumenté."}
    ]
    ),
    (
        1647,
        "Diagnostic 2nde Français - Orthographe grammaticale et accords",
        "Français",
        "2nde",
        [
            {"id": "1647_1","type": "qcm","question": "Dans « Les élèves attentifs écoutent », quel mot commande l'accord du verbe ?","options": ["attentifs","écoutent","élèves","les"],"correct_option": "élèves","explanation": "Le verbe s'accorde avec le sujet du verbe. Ici, le sujet est « élèves », au pluriel."},
            {"id": "1647_2","type": "vrai-faux","question": "Dans « Elles se sont parlé », le participe passé « parlé » reste invariable.","correct": True,"explanation": "Avec le verbe « parler » construit avec « à », le pronom réfléchi est COI. Le participe passé employé avec « être » dans ce cas reste invariable."},
            {"id": "1647_3","type": "qcm","question": "Quel est l'accord correct de l'adjectif dans « des décisions (important) » ?","options": [    "important",    "importante",    "importants",    "importantes"],"correct_option": "importantes","explanation": "L'adjectif qualificatif s'accorde en genre et en nombre avec le nom qu'il qualifie. « Décisions » est féminin pluriel."},
            {"id": "1647_4","type": "qcm","question": "Choisis la phrase correcte.","options": ["Les fleurs que j'ai cueilli sont fanées.","Les fleurs que j'ai cueillies sont fanées.","Les fleurs que j'ai cueillis sont fanées.","Les fleurs que j'ai cueillie sont fanées."],"correct_option": "Les fleurs que j'ai cueillies sont fanées.","explanation": "Avec « avoir », le participe passé s'accorde avec le COD si celui-ci est placé avant. « Que » reprend « fleurs », féminin pluriel."},
            {"id": "1647_5","type": "vrai-faux","question": "Le verbe s'accorde toujours avec le nom le plus proche.","correct": False,"explanation": "Le verbe s'accorde avec son sujet grammatical, pas avec le nom le plus proche. La proximité peut tromper, surtout dans les phrases longues."},
            {"id": "1647_6","type": "qcm","question": "Complète correctement : « Ils se sont ___ de ce problème » (rendre compte).","options": [    "rendu compte",    "rendues compte",    "rendus compte",    "rendue compte"],"correct_option": "rendu compte","explanation": "Dans « se rendre compte de », le pronom « se » est COI. Le participe passé « rendu » reste donc invariable."},
            {"id": "1647_7","type": "qcm","question": "Quelle phrase contient un accord correct du participe passé avec « être » ?","options": ["Elles sont parti tôt.","Elles sont parties tôt.","Elles sont partiez tôt.","Elles sont partis tôt."],"correct_option": "Elles sont parties tôt.","explanation": "Avec l'auxiliaire « être », le participe passé s'accorde en genre et en nombre avec le sujet. « Elles » impose le féminin pluriel."},
            {"id": "1647_8","type": "vrai-faux","question": "Dans « ni Paul ni ses amis ne vient », l'accord est correct.","correct": False,"explanation": "Le verbe doit s'accorder au pluriel ici : « ni Paul ni ses amis ne viennent ». Le second terme est pluriel et entraîne l'accord."}
    ]
    ),
    (
        1648,
        "Diagnostic 2nde Français - Temps du récit",
        "Français",
        "2nde",
        [
            {"id": "1648_1","type": "qcm","question": "Dans un récit au passé, quel temps sert le plus souvent à exprimer l'action principale et ponctuelle ?","options": ["L'imparfait","Le passé simple","Le présent","Le futur antérieur"],"correct_option": "Le passé simple","explanation": "Le passé simple marque en général les actions de premier plan. Il fait avancer l'intrigue."},
            {"id": "1648_2","type": "vrai-faux","question": "L'imparfait sert souvent à décrire un cadre, une habitude ou une action en cours dans le passé.","correct": True,"explanation": "L'imparfait installe l'arrière-plan du récit. Il exprime la durée, la répétition ou la description."},
            {"id": "1648_3","type": "qcm","question": "Quel est le temps utilisé pour exprimer une action antérieure à une autre action passée : « il avait terminé » ?","options": [    "Imparfait",    "Passé simple",    "Plus-que-parfait",    "Futur antérieur"],"correct_option": "Plus-que-parfait","explanation": "Le plus-que-parfait situe une action avant un repère passé. Il crée un effet de retour en arrière temporel."},
            {"id": "1648_4","type": "qcm","question": "Quelle phrase illustre le mieux la répartition classique imparfait / passé simple ?","options": ["Il marchait dans la rue quand soudain la porte s'ouvrit.","Il marcha dans la rue quand soudain la porte s'ouvrait.","Il marchait dans la rue quand soudain la porte s'ouvrait.","Il marcha dans la rue quand soudain la porte s'ouvre."],"correct_option": "Il marchait dans la rue quand soudain la porte s'ouvrit.","explanation": "L'imparfait pose l'action de fond (« marchait »), tandis que le passé simple marque l'événement soudain (« s'ouvrit »)."},
            {"id": "1648_5","type": "vrai-faux","question": "Le passé simple est majoritairement utilisé à l'oral courant.","correct": False,"explanation": "Le passé simple appartient surtout à la langue écrite littéraire. À l'oral, on emploie plus fréquemment le passé composé."},
            {"id": "1648_6","type": "qcm","question": "Quel temps emploie-t-on souvent pour raconter des actions achevées dans la conversation courante ?","options": [    "Imparfait",    "Passé simple",    "Passé composé",    "Futur antérieur"],"correct_option": "Passé composé","explanation": "Le passé composé est le temps usuel du récit oral pour des faits terminés. Il concurrence le passé simple dans la langue courante."},
            {"id": "1648_7","type": "qcm","question": "Dans « Il lut la lettre, puis il resta silencieux », la valeur principale du passé simple est :","options": ["Description durable","Action brève de premier plan","Hypothèse","Ordre"],"correct_option": "Action brève de premier plan","explanation": "Le passé simple met en relief des actions successives qui structurent l'histoire. Il sert la progression narrative."},
            {"id": "1648_8","type": "vrai-faux","question": "Dans un récit, on peut utiliser le présent de narration pour vivifier une scène passée.","correct": True,"explanation": "Le présent de narration crée un effet de proximité et de dynamisme. Il actualise une action pourtant située dans le passé."}
    ]
    ),
    (
        1649,
        "Diagnostic 2nde Français - Phrase complexe et subordination",
        "Français",
        "2nde",
        [
            {"id": "1649_1","type": "qcm","question": "Une phrase complexe est une phrase qui :","options": ["contient au moins deux verbes conjugués","contient uniquement des adjectifs","se termine toujours par un point d'exclamation","ne comporte jamais de conjonction"],"correct_option": "contient au moins deux verbes conjugués","explanation": "La phrase complexe associe plusieurs propositions. La présence d'au moins deux verbes conjugués est un indice fréquent."},
            {"id": "1649_2","type": "vrai-faux","question": "Dans « Je pense que tu as raison », « que tu as raison » est une subordonnée conjonctive complétive.","correct": True,"explanation": "La subordonnée introduite par « que » complète le verbe principal « pense ». Elle remplit la fonction de complément."},
            {"id": "1649_3","type": "qcm","question": "Comment appelle-t-on une subordonnée introduite par « qui », « que », « dont » ou « où » ?","options": [    "Subordonnée circonstancielle",    "Subordonnée relative",    "Proposition indépendante",    "Incise"],"correct_option": "Subordonnée relative","explanation": "La subordonnée relative complète un nom antécédent. Elle apporte une précision sur ce nom."},
            {"id": "1649_4","type": "qcm","question": "Quel lien logique exprime « parce que » ?","options": ["La conséquence","La cause","L'opposition","La concession"],"correct_option": "La cause","explanation": "« Parce que » introduit la raison d'un fait. C'est un connecteur de causalité."},
            {"id": "1649_5","type": "vrai-faux","question": "« Bien que » introduit généralement une idée de concession.","correct": True,"explanation": "La concession oppose un fait à une attente logique. « Bien que » signale cette relation argumentative."},
            {"id": "1649_6","type": "qcm","question": "Quel connecteur de conséquence est fréquemment utilisé dans un raisonnement ?","options": [    "Car",    "Donc",    "Mais",    "Ou"],"correct_option": "Donc","explanation": "Un connecteur de conséquence marque le résultat d'une cause ou d'un raisonnement. « Donc » est l'un des plus courants."},
            {"id": "1649_7","type": "qcm","question": "Dans « L'élève qui révise progresse », quelle est la nature de « qui révise » ?","options": ["Proposition indépendante","Subordonnée relative","Subordonnée circonstancielle de temps","Incise"],"correct_option": "Subordonnée relative","explanation": "« Qui révise » complète l'antécédent « l'élève » et est introduite par le pronom relatif « qui ». C'est une relative."},
            {"id": "1649_8","type": "vrai-faux","question": "Une proposition subordonnée ne peut jamais être supprimée sans modifier le sens de la phrase.","correct": False,"explanation": "Certaines subordonnées sont essentielles, d'autres apportent une précision facultative. Leur suppression peut être possible selon le cas."}
    ]
    ),
    (
        1650,
        "Diagnostic 2nde Français - Registres littéraires",
        "Français",
        "2nde",
        [
            {"id": "1650_1","type": "qcm","question": "Quel registre cherche principalement à susciter la peur et la pitié ?","options": ["Le registre tragique","Le registre comique","Le registre épistolaire","Le registre didactique"],"correct_option": "Le registre tragique","explanation": "Le tragique met en scène une fatalité ou une impasse qui dépasse le personnage. Il provoque des émotions intenses chez le lecteur."},
            {"id": "1650_2","type": "vrai-faux","question": "Le registre comique peut reposer sur le langage, les gestes, les situations ou les caractères.","correct": True,"explanation": "Le comique est pluriel : mots, comportements, répétitions, quiproquos ou contrastes peuvent faire rire. Ces procédés se combinent souvent."},
            {"id": "1650_3","type": "qcm","question": "Quel registre vise à dénoncer en ridiculisant des défauts ou des comportements ?","options": [    "Tragique",    "Satirique",    "Lyrique",    "Comique"],"correct_option": "Satirique","explanation": "Le registre satirique critique une personne, un groupe ou une idée en utilisant l'ironie, l'exagération ou la caricature."},
            {"id": "1650_4","type": "qcm","question": "Quel indice est le plus typique du registre polémique ?","options": ["Un vocabulaire neutre et objectif","Une argumentation vive avec attaques et réfutations","Une narration exclusivement descriptive","Une absence totale de jugement"],"correct_option": "Une argumentation vive avec attaques et réfutations","explanation": "Le registre polémique assume l'affrontement d'idées. Il emploie un ton combatif pour convaincre ou disqualifier une position adverse."},
            {"id": "1650_5","type": "vrai-faux","question": "Un même texte peut mêler plusieurs registres.","correct": True,"explanation": "Les registres ne sont pas exclusifs : un texte peut passer du lyrique au tragique ou mêler comique et critique. Cela enrichit l'interprétation."},
            {"id": "1650_6","type": "qcm","question": "Quel registre met en avant l'expression personnelle des sentiments ?","options": [    "Lyrique",    "Satirique",    "Comique",    "Didactique"],"correct_option": "Lyrique","explanation": "Le registre lyrique exprime une subjectivité forte, souvent à la première personne. Il valorise les émotions et la musicalité."},
            {"id": "1650_7","type": "qcm","question": "Dans une scène où un personnage ignore un danger que le public connaît, l'effet dominant peut être :","options": ["Le registre épique","Le registre tragique","Le registre didactique","Le registre administratif"],"correct_option": "Le registre tragique","explanation": "Ce décalage d'information crée une tension dramatique et annonce un destin défavorable. C'est un ressort classique du tragique."},
            {"id": "1650_8","type": "vrai-faux","question": "Le registre pathétique cherche à émouvoir en montrant la souffrance ou la vulnérabilité.","correct": True,"explanation": "Le pathétique suscite la compassion du lecteur. Il met en avant la douleur, la faiblesse ou l'injustice subie."}
    ]
    ),
    (
        1651,
        "Diagnostic 2nde Français - Genres et mouvements littéraires",
        "Français",
        "2nde",
        [
            {"id": "1651_1","type": "qcm","question": "À quel mouvement associe-t-on volontiers Victor Hugo dans l'histoire littéraire ?","options": ["Le classicisme","Le romantisme","Le naturalisme","Le surréalisme"],"correct_option": "Le romantisme","explanation": "Victor Hugo est une figure majeure du romantisme au XIXe siècle. Ce mouvement valorise la sensibilité, l'histoire et la liberté créatrice."},
            {"id": "1651_2","type": "vrai-faux","question": "Le naturalisme cherche à observer la société et les comportements avec une méthode proche de l'enquête.","correct": True,"explanation": "Le naturalisme, notamment avec Zola, revendique une démarche d'observation du réel. Il étudie les milieux sociaux et les déterminismes."},
            {"id": "1651_3","type": "qcm","question": "Comment nomme-t-on le genre qui met en scène des personnages sur scène avec didascalies et répliques ?","options": [    "Roman",    "Théâtre",    "Poésie",    "Essai"],"correct_option": "Théâtre","explanation": "Le théâtre est un genre destiné à la représentation. Son écriture combine dialogues, indications scéniques et structure en actes ou scènes."},
            {"id": "1651_4","type": "qcm","question": "Quel trait caractérise le classicisme au XVIIe siècle ?","options": ["Le refus de toute règle","La recherche de l'ordre, de la mesure et de la clarté","L'écriture automatique","La priorité à la science-fiction"],"correct_option": "La recherche de l'ordre, de la mesure et de la clarté","explanation": "Le classicisme valorise la raison, la maîtrise et l'équilibre. Il s'appuie sur des règles formelles et un idéal de clarté."},
            {"id": "1651_5","type": "vrai-faux","question": "Le surréalisme explore l'inconscient et les associations inattendues.","correct": True,"explanation": "Le surréalisme veut dépasser la logique ordinaire. Il privilégie l'imaginaire, le rêve et les rapprochements surprenants."},
            {"id": "1651_6","type": "qcm","question": "Quel mouvement du XIXe siècle met fortement l'accent sur l'émotion, la nature et le moi ?","options": [    "Romantisme",    "Classicisme",    "Naturalisme",    "Surréalisme"],"correct_option": "Romantisme","explanation": "Le romantisme place au centre l'expression de la subjectivité. Il privilégie le lyrisme, l'élan personnel et la sensibilité."},
            {"id": "1651_7","type": "qcm","question": "Quel genre repose sur une narration d'événements vécus par des personnages dans un univers fictif ?","options": ["Le roman","La dissertation","Le mode d'emploi","Le compte rendu administratif"],"correct_option": "Le roman","explanation": "Le roman raconte une histoire construite autour de personnages et d'une intrigue. Il peut adopter des formes et des époques variées."},
            {"id": "1651_8","type": "vrai-faux","question": "Un mouvement littéraire correspond uniquement à une période chronologique sans idées communes.","correct": False,"explanation": "Un mouvement regroupe des auteurs et des œuvres partageant des choix esthétiques et des enjeux communs. La période historique ne suffit pas à le définir."}
    ]
    ),
    (
        1652,
        "Diagnostic 2nde Français - Synthèse des compétences de début d'année",
        "Français",
        "2nde",
        [
            {"id": "1652_1","type": "qcm","question": "Dans une introduction de commentaire, quelle étape est attendue en priorité ?","options": ["Donner uniquement son opinion personnelle","Présenter le texte et formuler une problématique","Recopier la conclusion","Énumérer des figures sans lien"],"correct_option": "Présenter le texte et formuler une problématique","explanation": "L'introduction situe le texte (auteur, œuvre, contexte utile) puis annonce l'angle d'analyse. La problématique guide tout le développement."},
            {"id": "1652_2","type": "vrai-faux","question": "Dans un paragraphe d'analyse littéraire, il est conseillé de citer le texte et d'expliquer l'effet produit.","correct": True,"explanation": "Une analyse efficace articule preuve et interprétation. La citation doit être brève, pertinente et commentée."},            {"id": "1652_3","type": "qcm","question": "Quel terme désigne l'idée directrice qui organise une lecture analytique ?","options": [    "Problématique",    "Conclusion",    "Introduction",    "Citation"],"correct_option": "Problématique","explanation": "La problématique pose la question centrale à laquelle l'analyse répond. Elle donne une cohérence à l'ensemble du devoir."},
            {"id": "1652_4","type": "qcm","question": "Quel choix de connecteur convient le mieux pour introduire un second argument allant dans le même sens ?","options": ["Cependant","De plus","Au contraire","Néanmoins"],"correct_option": "De plus","explanation": "« De plus » ajoute un élément convergent. C'est un connecteur d'addition utile pour renforcer une démonstration."},
            {"id": "1652_5","type": "vrai-faux","question": "La conclusion d'un devoir doit simplement répéter mot à mot l'introduction.","correct": False,"explanation": "La conclusion répond à la problématique en synthétisant les acquis de l'analyse. Elle reformule, sans copier, et peut ouvrir sur une perspective."},
            {"id": "1652_6","type": "qcm","question": "Quel est le procédé qui consiste à reprendre les mots exacts d'un texte entre guillemets ?","options": [    "Citation",    "Résumé",    "Paraphrase",    "Allitération"],"correct_option": "Citation","explanation": "La citation sert d'appui précis à l'analyse. Elle doit être intégrée à la phrase et expliquée."},
            {"id": "1652_7","type": "qcm","question": "Dans « Bien qu'il soit tard, elle continue de lire », quelle relation logique est exprimée ?","options": ["La cause","La concession","Le but","La comparaison"],"correct_option": "La concession","explanation": "La concession oppose un fait à ce qu'on attendrait logiquement. « Bien que » est un marqueur typique de cette relation."},
            {"id": "1652_8","type": "vrai-faux","question": "Relire un devoir permet de corriger des accords et d'améliorer la précision des formulations.","correct": True,"explanation": "La relecture est une étape stratégique : elle réduit les fautes et clarifie les idées. Elle améliore directement la qualité de l'expression."}
    ]
    ),
    (
        1653,
        "Diagnostic 2nde Français - Lexique et nuances de sens",
        "Français",
        "2nde",
        [
            {"id": "1653_1", "type": "qcm", "question": "Quel mot est synonyme de 'hardi' ?", "options": ["Timide", "Courageux", "Lâche", "Indifférent"], "correct_option": "Courageux", "explanation": "'Hardi' signifie audacieux, courageux."},
            {"id": "1653_2", "type": "vrai-faux", "question": "Un antonyme est un mot de sens opposé.", "correct": True, "explanation": "Exemple : 'grand' et 'petit' sont antonymes."},
            {"id": "1653_3", "type": "qcm", "question": "Quel mot a un sens plus fort que 'content' ?", "options": ["Ravi", "Triste", "Fâché", "Indifférent"], "correct_option": "Ravi", "explanation": "'Ravi' exprime une joie plus intense que 'content'."},
            {"id": "1653_4", "type": "vrai-faux", "question": "Un homonyme est toujours un synonyme.", "correct": False, "explanation": "Les homonymes ont la même forme mais pas le même sens."},
            {"id": "1653_5", "type": "qcm", "question": "Quel mot est le plus proche du sens de 'persévérer' ?", "options": ["Abandonner", "Continuer", "Hésiter", "Oublier"], "correct_option": "Continuer", "explanation": "Persévérer, c'est continuer malgré les difficultés."},
            {"id": "1653_6", "type": "vrai-faux", "question": "Le champ lexical de la peur comprend 'angoisse', 'terreur', 'inquiétude'.", "correct": True, "explanation": "Tous ces mots appartiennent au champ lexical de la peur."},
            {"id": "1653_7", "type": "qcm", "question": "Quel mot est un hyperonyme de 'chêne', 'sapin', 'bouleau' ?", "options": ["Arbre", "Forêt", "Feuille", "Bois"], "correct_option": "Arbre", "explanation": "'Arbre' est l'hyperonyme de ces espèces."},
            {"id": "1653_8", "type": "vrai-faux", "question": "Un paronyme est un mot de sens identique à un autre.", "correct": False, "explanation": "Les paronymes se ressemblent mais n'ont pas le même sens (ex : collision/collusion)."}
        ]
    )
    ,
    (
        1654,
        "Diagnostic 2nde Français - Types de discours",
        "Français",
        "2nde",
        [
            {"id": "1654_1", "type": "qcm", "question": "Quel discours rapporte fidèlement les paroles d'autrui ?", "options": ["Discours direct", "Discours indirect", "Discours narrativisé", "Discours descriptif"], "correct_option": "Discours direct", "explanation": "Le discours direct utilise les guillemets et reproduit les paroles telles quelles."},
            {"id": "1654_2", "type": "vrai-faux", "question": "Le discours indirect libre mêle la voix du narrateur et celle du personnage.", "correct": True, "explanation": "Il permet une grande souplesse dans la narration."},
            {"id": "1654_3", "type": "qcm", "question": "Quel marqueur annonce souvent un discours direct ?", "options": ["Que", ":", "Si", "Mais"], "correct_option": ":", "explanation": "Le deux-points introduit fréquemment le discours direct."},
            {"id": "1654_4", "type": "vrai-faux", "question": "Le discours narrativisé donne beaucoup de détails sur les paroles prononcées.", "correct": False, "explanation": "Il résume les propos sans les rapporter précisément."},
            {"id": "1654_5", "type": "qcm", "question": "Dans la phrase : Il répondit qu'il viendrait, quel est le type de discours ?", "options": ["Direct", "Indirect", "Indirect libre", "Narrativisé"], "correct_option": "Indirect", "explanation": "La subordonnée introduite par 'que' signale le discours indirect."},
            {"id": "1654_6", "type": "vrai-faux", "question": "Le discours direct est toujours introduit par un verbe de parole.", "correct": False, "explanation": "Il peut être introduit par une incise ou un contexte narratif."},
            {"id": "1654_7", "type": "qcm", "question": "Quel effet produit le discours indirect libre ?", "options": ["Il objective le récit", "Il rapproche le lecteur de la conscience du personnage", "Il ralentit l'action", "Il supprime toute subjectivité"], "correct_option": "Il rapproche le lecteur de la conscience du personnage", "explanation": "Le discours indirect libre permet d'entrer dans la subjectivité du personnage."},
            {"id": "1654_8", "type": "vrai-faux", "question": "Le discours direct utilise toujours le présent de narration.", "correct": False, "explanation": "Il peut utiliser différents temps selon le contexte."}
        ]
    )
    ,
    (
        1655,
        "Diagnostic 2nde Français - Figures d'opposition et d'atténuation",
        "Français",
        "2nde",
        [
            {"id": "1655_1", "type": "qcm", "question": "Quelle figure de style oppose deux termes ou idées ?", "options": ["Comparaison", "Antithèse", "Hyperbole", "Allitération"], "correct_option": "Antithèse", "explanation": "L'antithèse rapproche deux éléments opposés pour souligner un contraste."},
            {"id": "1655_2", "type": "vrai-faux", "question": "La litote atténue l'expression d'une idée.", "correct": True, "explanation": "La litote consiste à dire moins pour suggérer plus."},
            {"id": "1655_3", "type": "qcm", "question": "Quel énoncé illustre une antiphrase ?", "options": ["C'est du propre !", "Il fait beau aujourd'hui.", "Je suis ravi de te voir.", "Il est très intelligent."], "correct_option": "C'est du propre !", "explanation": "L'antiphrase consiste à dire le contraire de ce que l'on pense, souvent de façon ironique."},
            {"id": "1655_4", "type": "vrai-faux", "question": "L'oxymore associe deux mots de sens opposés dans une même expression.", "correct": True, "explanation": "Exemple : 'cette obscure clarté'."},
            {"id": "1655_5", "type": "qcm", "question": "Quelle figure de style consiste à atténuer une réalité désagréable ?", "options": ["Euphémisme", "Hyperbole", "Allitération", "Comparaison"], "correct_option": "Euphémisme", "explanation": "L'euphémisme adoucit une idée difficile à exprimer."},
            {"id": "1655_6", "type": "vrai-faux", "question": "L'antithèse et l'oxymore sont des figures d'opposition.", "correct": True, "explanation": "Toutes deux mettent en valeur un contraste."},
            {"id": "1655_7", "type": "qcm", "question": "Quel effet produit la litote ?", "options": ["Elle exagère", "Elle atténue", "Elle répète", "Elle oppose"], "correct_option": "Elle atténue", "explanation": "La litote minimise pour suggérer davantage."},
            {"id": "1655_8", "type": "vrai-faux", "question": "L'euphémisme est utilisé pour choquer le lecteur.", "correct": False, "explanation": "Au contraire, il vise à adoucir une réalité."}
        ]
    )
    ,
    (    1656,
        "Diagnostic 2nde Français - Narration et temps des verbes",
        "Français",
        "2nde",
        [
            {"id": "1656_1", "type": "qcm", "question": "Quel temps utiliser pour exprimer une action achevée dans le passé ?", "options": ["Imparfait", "Passé simple", "Présent", "Futur"], "correct_option": "Passé simple", "explanation": "Le passé simple marque l'action ponctuelle et terminée dans le récit."},
            {"id": "1656_2", "type": "vrai-faux", "question": "L'imparfait sert à décrire le cadre ou une action habituelle.", "correct": True, "explanation": "L'imparfait pose l'arrière-plan du récit."},
            {"id": "1656_3", "type": "qcm", "question": "Quel temps verbal utilise-t-on pour une action antérieure à une autre action passée ?", "options": ["Imparfait", "Plus-que-parfait", "Présent", "Futur antérieur"], "correct_option": "Plus-que-parfait", "explanation": "Le plus-que-parfait exprime l'antériorité dans le passé."},
            {"id": "1656_4", "type": "vrai-faux", "question": "Le présent de narration actualise une scène passée.", "correct": True, "explanation": "Il donne un effet de direct et de vivacité."},
            {"id": "1656_5", "type": "qcm", "question": "Dans la phrase : Il marchait dans la rue quand la porte s'ouvrit, quels sont les temps utilisés ?", "options": ["Imparfait et passé simple", "Présent et futur", "Imparfait et présent", "Passé composé et imparfait"], "correct_option": "Imparfait et passé simple", "explanation": "C'est la répartition classique du récit : fond et événement."},
            {"id": "1656_6", "type": "vrai-faux", "question": "Le passé composé est le temps du récit oral courant.", "correct": True, "explanation": "À l'oral, on privilégie le passé composé pour raconter des faits terminés."},
            {"id": "1656_7", "type": "qcm", "question": "Quel temps verbal exprime une action future antérieure à une autre action future ?", "options": ["Futur antérieur", "Futur simple", "Présent", "Imparfait"], "correct_option": "Futur antérieur", "explanation": "Le futur antérieur exprime l'antériorité dans le futur."},
            {"id": "1656_8", "type": "vrai-faux", "question": "Le passé simple est très utilisé à l'oral.", "correct": False, "explanation": "Il est réservé à l'écrit littéraire."}
        ]
    ),
    (
        1657,
        "Diagnostic 2nde Français - Analyse de texte et interprétation",
        "Français",
        "2nde",
        [
            {"id": "1657_1", "type": "qcm", "question": "Quel est le but d'une analyse littéraire ?", "options": ["Raconter l'histoire", "Décrire les personnages", "Interpréter le sens et les effets du texte", "Donner son avis sans argument"], "correct_option": "Interpréter le sens et les effets du texte", "explanation": "L'analyse vise à comprendre comment le texte produit du sens."},
            {"id": "1657_2", "type": "vrai-faux", "question": "Citer le texte est conseillé dans une analyse.", "correct": True, "explanation": "La citation appuie l'argumentation et doit être expliquée."},
            {"id": "1657_3", "type": "qcm", "question": "Quel terme désigne la question centrale d'une lecture analytique ?", "options": ["Problématique", "Conclusion", "Introduction", "Résumé"], "correct_option": "Problématique", "explanation": "La problématique oriente toute l'analyse."},
            {"id": "1657_4", "type": "vrai-faux", "question": "L'effet produit sur le lecteur n'est jamais analysé.", "correct": False, "explanation": "L'effet sur le lecteur est un axe d'analyse important."},
            {"id": "1657_5", "type": "qcm", "question": "Quel connecteur logique introduit un argument allant dans le même sens ?", "options": ["Cependant", "De plus", "Mais", "Néanmoins"], "correct_option": "De plus", "explanation": "Il ajoute un argument convergent."},
            {"id": "1657_6", "type": "vrai-faux", "question": "La conclusion d'une analyse doit ouvrir sur une perspective.", "correct": True, "explanation": "Elle ne se contente pas de répéter l'introduction."},
            {"id": "1657_7", "type": "qcm", "question": "Quel procédé consiste à reprendre les mots exacts d'un texte ?", "options": ["Citation", "Résumé", "Paraphrase", "Allitération"], "correct_option": "Citation", "explanation": "La citation doit être intégrée et expliquée."},
            {"id": "1657_8", "type": "vrai-faux", "question": "La relecture d'un devoir est inutile.", "correct": False, "explanation": "La relecture permet de corriger et d'améliorer la qualité du devoir."}
        ]
    )
    ,
    (
        1658,
        "Diagnostic 2nde Français - Poésie et musicalité du vers",
        "Français",
        "2nde",
        [
            {"id": "1658_1", "type": "qcm", "question": "Quel procédé consiste à répéter un son consonantique ?", "options": ["Allitération", "Assonance", "Anaphore", "Hyperbole"], "correct_option": "Allitération", "explanation": "L'allitération crée un effet sonore particulier."},
            {"id": "1658_2", "type": "vrai-faux", "question": "Le vers libre respecte un mètre régulier.", "correct": False, "explanation": "Le vers libre s'affranchit des règles de syllabes."},
            {"id": "1658_3", "type": "qcm", "question": "Quel est le nombre de vers d'un sonnet classique ?", "options": ["8", "10", "12", "14"], "correct_option": "14", "explanation": "Le sonnet comporte 14 vers répartis en quatrains et tercets."},
            {"id": "1658_4", "type": "vrai-faux", "question": "L'anaphore est la répétition d'un mot en début de vers.", "correct": True, "explanation": "Elle structure le poème et insiste sur une idée."},
            {"id": "1658_5", "type": "qcm", "question": "Quel registre exprime la plainte et la souffrance ?", "options": ["Comique", "Pathétique", "Didactique", "Épique"], "correct_option": "Pathétique", "explanation": "Le pathétique vise à émouvoir le lecteur."},
            {"id": "1658_6", "type": "vrai-faux", "question": "L'enjambement prolonge la phrase au-delà du vers.", "correct": True, "explanation": "Il crée un effet de continuité et de rythme."},
            {"id": "1658_7", "type": "qcm", "question": "Quel effet produit l'assonance ?", "options": ["Répétition d'un son vocalique", "Répétition d'un mot", "Opposition de deux idées", "Suppression de la ponctuation"], "correct_option": "Répétition d'un son vocalique", "explanation": "L'assonance crée une musicalité particulière."},
            {"id": "1658_8", "type": "vrai-faux", "question": "La métaphore utilise un outil comparatif explicite.", "correct": False, "explanation": "La métaphore rapproche deux réalités sans comparatif."}
        ]
    )
    ,
    (
        1659,
        "Diagnostic 2nde Français - Théâtre et vocabulaire scénique",
        "Français",
        "2nde",
        [
            {"id": "1659_1", "type": "qcm", "question": "Comment nomme-t-on une indication de mise en scène dans une pièce ?", "options": ["Didascalie", "Tirade", "Aparté", "Stichomythie"], "correct_option": "Didascalie", "explanation": "La didascalie guide le jeu des acteurs et la scénographie."},
            {"id": "1659_2", "type": "vrai-faux", "question": "Le monologue est une réplique brève et rapide.", "correct": False, "explanation": "Le monologue est une longue prise de parole d'un personnage seul."},
            {"id": "1659_3", "type": "qcm", "question": "Quel terme désigne un échange rapide de répliques courtes ?", "options": ["Stichomythie", "Tirade", "Monologue", "Prologue"], "correct_option": "Stichomythie", "explanation": "La stichomythie dynamise le dialogue et le conflit."},
            {"id": "1659_4", "type": "vrai-faux", "question": "L'aparté est destiné à être entendu par tous les personnages.", "correct": False, "explanation": "Il est entendu du public mais pas des autres personnages."},
            {"id": "1659_5", "type": "qcm", "question": "Quel genre théâtral vise à faire rire ?", "options": ["Tragédie", "Comédie", "Drame", "Farce"], "correct_option": "Comédie", "explanation": "La comédie repose sur le comique de situation, de mots ou de gestes."},
            {"id": "1659_6", "type": "vrai-faux", "question": "La double énonciation est propre au théâtre.", "correct": True, "explanation": "Le personnage s'adresse à la fois à un autre personnage et au public."},
            {"id": "1659_7", "type": "qcm", "question": "Quel est le rôle du prologue dans une pièce ?", "options": ["Présenter l'intrigue", "Conclure l'action", "Introduire un personnage secondaire", "Décrire le décor"], "correct_option": "Présenter l'intrigue", "explanation": "Le prologue expose la situation initiale et les enjeux."},
            {"id": "1659_8", "type": "vrai-faux", "question": "La tirade est une réplique très courte.", "correct": False, "explanation": "La tirade est une longue réplique développée."}
        ]
    )
    ,
    (
        1660,
        "Diagnostic 2nde Français - Genres littéraires et narration",
        "Français",
        "2nde",
        [
            {"id": "1660_1", "type": "qcm", "question": "Quel genre littéraire raconte une histoire fictive ?", "options": ["Roman", "Essai", "Poésie", "Théâtre"], "correct_option": "Roman", "explanation": "Le roman met en scène des personnages et une intrigue fictive."},
            {"id": "1660_2", "type": "vrai-faux", "question": "Le théâtre est un genre uniquement destiné à la lecture silencieuse.", "correct": False, "explanation": "Le théâtre est conçu pour être joué sur scène."},
            {"id": "1660_3", "type": "qcm", "question": "Quel genre privilégie l'expression des sentiments personnels ?", "options": ["Poésie lyrique", "Roman policier", "Essai", "Théâtre classique"], "correct_option": "Poésie lyrique", "explanation": "La poésie lyrique exprime le moi et les émotions."},
            {"id": "1660_4", "type": "vrai-faux", "question": "Le narrateur omniscient connaît tout des personnages.", "correct": True, "explanation": "Il a accès à toutes les pensées et à tous les événements."},
            {"id": "1660_5", "type": "qcm", "question": "Quel genre littéraire met en scène des personnages sur scène ?", "options": ["Théâtre", "Roman", "Poésie", "Essai"], "correct_option": "Théâtre", "explanation": "Le théâtre est destiné à la représentation scénique."},
            {"id": "1660_6", "type": "vrai-faux", "question": "Le roman policier appartient au genre argumentatif.", "correct": False, "explanation": "Il s'agit d'un genre narratif, centré sur l'enquête et la fiction."},
            {"id": "1660_7", "type": "qcm", "question": "Quel genre littéraire privilégie l'analyse d'idées et la réflexion ?", "options": ["Essai", "Roman", "Poésie", "Théâtre"], "correct_option": "Essai", "explanation": "L'essai développe une réflexion personnelle sur un sujet."},
            {"id": "1660_8", "type": "vrai-faux", "question": "La focalisation interne donne accès à la subjectivité d'un personnage.", "correct": True, "explanation": "Le lecteur partage les perceptions et pensées du personnage focalisateur."}
        ]
    ),
(
        1661,
        "Diagnostic 2nde Français - Les valeurs de l'imparfait",
        "Français",
        "2nde",
        [
            {"id": "1661_1", "type": "qcm", "question": "Quelle valeur de l'imparfait exprime une action répétée dans le passé ?", "options": ["Description", "Habitude", "Premier plan", "Futur proche"], "correct_option": "Habitude", "explanation": "L'imparfait d'habitude exprime la répétition d'une action passée."},
            {"id": "1661_2", "type": "vrai-faux", "question": "L'imparfait peut servir à décrire un décor.", "correct": True, "explanation": "L'imparfait descriptif pose le cadre, les décors, les situations."},
            {"id": "1661_3", "type": "qcm", "question": "Dans la phrase : 'Il chantait tous les matins', l'imparfait a une valeur de :", "options": ["Premier plan", "Habitude", "Futur proche", "Ordre"], "correct_option": "Habitude", "explanation": "La répétition quotidienne indique l'habitude."},
            {"id": "1661_4", "type": "qcm", "question": "L'imparfait de description s'oppose surtout à :", "options": ["L'imparfait d'habitude", "Le passé simple de premier plan", "Le futur simple", "Le conditionnel"], "correct_option": "Le passé simple de premier plan", "explanation": "Le passé simple fait avancer l'action, l'imparfait pose le décor."},
            {"id": "1661_5", "type": "vrai-faux", "question": "L'imparfait peut exprimer une action en cours d'accomplissement.", "correct": True, "explanation": "C'est la valeur d'imparfait de progression ou d'action inachevée."},
            {"id": "1661_6", "type": "qcm", "question": "Dans 'Il pleuvait quand je suis sorti', l'imparfait exprime :", "options": ["Action achevée", "Action en cours", "Futur", "Ordre"], "correct_option": "Action en cours", "explanation": "L'action de pleuvoir est en cours au moment où l'autre action se produit."},
            {"id": "1661_7", "type": "qcm", "question": "L'imparfait peut-il être utilisé pour exprimer une hypothèse ?", "options": ["Oui", "Non"], "correct_option": "Oui", "explanation": "Dans certaines constructions, l'imparfait exprime une hypothèse (ex : Si j'avais de l'argent, je partirais)."},
            {"id": "1661_8", "type": "vrai-faux", "question": "L'imparfait est le temps du récit oral spontané.", "correct": False, "explanation": "À l'oral, on utilise surtout le passé composé pour raconter des faits."}
        ]
    ),
    (
        1662,
        "Diagnostic 2nde Français - Les registres de langue",
        "Français",
        "2nde",
        [
            {"id": "1662_1", "type": "qcm", "question": "Quel registre de langue utilise 'tu vas où ?' ?", "options": ["Soutenu", "Courant", "Familier", "Poétique"], "correct_option": "Familier", "explanation": "La tournure est typique du registre familier."},
            {"id": "1662_2", "type": "vrai-faux", "question": "Le registre soutenu s'utilise dans les dissertations.", "correct": True, "explanation": "Le registre soutenu est attendu dans les écrits formels et scolaires."},
            {"id": "1662_3", "type": "qcm", "question": "Quel mot appartient au registre courant ?", "options": ["Bagnole", "Automobile", "Caisse", "Tire"], "correct_option": "Automobile", "explanation": "'Automobile' est neutre, ni familier ni soutenu."},
            {"id": "1662_4", "type": "qcm", "question": "Dans quel contexte emploie-t-on le registre familier ?", "options": ["Lettre administrative", "Discussion entre amis", "Exposé oral", "Article scientifique"], "correct_option": "Discussion entre amis", "explanation": "Le registre familier s'utilise dans la sphère privée et informelle."},
            {"id": "1662_5", "type": "vrai-faux", "question": "Le registre soutenu utilise des tournures recherchées.", "correct": True, "explanation": "Il privilégie la complexité syntaxique et le vocabulaire précis."},
            {"id": "1662_6", "type": "qcm", "question": "Quel synonyme de 'maison' relève du registre soutenu ?", "options": ["Demeure", "Piaule", "Baraque", "Case"], "correct_option": "Demeure", "explanation": "'Demeure' est un terme soutenu pour 'maison'."},
            {"id": "1662_7", "type": "qcm", "question": "Quel registre privilégier dans un entretien d'embauche ?", "options": ["Familier", "Courant", "Soutenu", "Poétique"], "correct_option": "Courant", "explanation": "Le registre courant est adapté à la plupart des situations professionnelles."},
            {"id": "1662_8", "type": "vrai-faux", "question": "Le registre familier est à éviter dans un écrit scolaire.", "correct": True, "explanation": "Il faut privilégier le registre courant ou soutenu à l'écrit."}
        ]
    ),
    (
        1663,
        "Diagnostic 2nde Français - Les figures d'amplification",
        "Français",
        "2nde",
        [
            {"id": "1663_1", "type": "qcm", "question": "Quelle figure de style exagère la réalité ?", "options": ["Litote", "Hyperbole", "Euphémisme", "Antithèse"], "correct_option": "Hyperbole", "explanation": "L'hyperbole amplifie une idée ou une réalité."},
            {"id": "1663_2", "type": "vrai-faux", "question": "L'accumulation consiste à énumérer plusieurs éléments.", "correct": True, "explanation": "L'accumulation multiplie les termes pour insister."},
            {"id": "1663_3", "type": "qcm", "question": "Quel énoncé illustre une gradation ascendante ?", "options": ["Il cria, hurla, rugit", "Il mangea, il dormit, il partit", "Il vit, il vit, il vit", "Il pleura, il rit, il dormit"], "correct_option": "Il cria, hurla, rugit", "explanation": "La gradation organise les termes par intensité croissante."},
            {"id": "1663_4", "type": "qcm", "question": "L'hyperbole est surtout utilisée pour :", "options": ["Minimiser", "Amplifier", "Opposer", "Atténuer"], "correct_option": "Amplifier", "explanation": "L'hyperbole sert à exagérer, à amplifier le propos."},
            {"id": "1663_5", "type": "vrai-faux", "question": "L'accumulation et la gradation sont des figures d'amplification.", "correct": True, "explanation": "Elles servent à renforcer l'effet du discours."},
            {"id": "1663_6", "type": "qcm", "question": "Quel effet produit l'hyperbole ?", "options": ["Rendre le propos plus vivant", "Atténuer la réalité", "Créer une opposition", "Répéter une idée"], "correct_option": "Rendre le propos plus vivant", "explanation": "L'hyperbole dynamise et marque l'expression."},
            {"id": "1663_7", "type": "qcm", "question": "Quel énoncé n'est pas une hyperbole ?", "options": ["Je meurs de faim", "Il a une montagne de devoirs", "Il est un peu fatigué", "C'est un géant !"], "correct_option": "Il est un peu fatigué", "explanation": "C'est une expression neutre, sans exagération."},
            {"id": "1663_8", "type": "vrai-faux", "question": "La gradation peut être descendante.", "correct": True, "explanation": "La gradation peut aller du plus fort au plus faible."}
        ]
    ),
    (
        1664,
        "Diagnostic 2nde Français - Les types de phrases",
        "Français",
        "2nde",
        [
            {"id": "1664_1", "type": "qcm", "question": "Quelle phrase est exclamative ?", "options": ["Viens ici.", "Viens ici !", "Viens ici ?", "Viens ici..."], "correct_option": "Viens ici !", "explanation": "Le point d'exclamation marque l'exclamative."},
            {"id": "1664_2", "type": "vrai-faux", "question": "La phrase interrogative pose une question.", "correct": True, "explanation": "C'est sa fonction principale."},
            {"id": "1664_3", "type": "qcm", "question": "Quelle phrase est impérative ?", "options": ["Ferme la porte.", "La porte est fermée.", "La porte se ferme.", "La porte fut fermée."], "correct_option": "Ferme la porte.", "explanation": "L'impératif donne un ordre ou un conseil."},
            {"id": "1664_4", "type": "qcm", "question": "La phrase déclarative sert à :", "options": ["Donner un ordre", "Exprimer un fait", "Poser une question", "Exprimer une émotion"], "correct_option": "Exprimer un fait", "explanation": "La phrase déclarative énonce une information."},
            {"id": "1664_5", "type": "vrai-faux", "question": "La phrase exclamative exprime une émotion.", "correct": True, "explanation": "Elle sert à marquer la surprise, la joie, la colère, etc."},
            {"id": "1664_6", "type": "qcm", "question": "Quelle phrase est interrogative ?", "options": ["Où vas-tu ?", "Va-t'en !", "Il part.", "Ferme la porte."], "correct_option": "Où vas-tu ?", "explanation": "Le point d'interrogation marque la question."},
            {"id": "1664_7", "type": "qcm", "question": "Quel type de phrase utilise-t-on pour donner un conseil ?", "options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"], "correct_option": "Impérative", "explanation": "L'impératif sert à conseiller ou ordonner."},
            {"id": "1664_8", "type": "vrai-faux", "question": "La phrase déclarative est la plus fréquente dans un texte narratif.", "correct": True, "explanation": "Elle sert à raconter les faits."}
        ]
    ),
    (
        1665,
        "Diagnostic 2nde Français - Les connecteurs logiques",
        "Français",
        "2nde",
        [
            {"id": "1665_1", "type": "qcm", "question": "Quel connecteur introduit une cause ?", "options": ["Parce que", "Donc", "Mais", "Ou"], "correct_option": "Parce que", "explanation": "'Parce que' explique la raison d'un fait."},
            {"id": "1665_2", "type": "vrai-faux", "question": "'Donc' marque la conséquence.", "correct": True, "explanation": "Il introduit le résultat d'une action ou d'un raisonnement."},
            {"id": "1665_3", "type": "qcm", "question": "Quel connecteur exprime l'opposition ?", "options": ["Mais", "Car", "Et", "Ou"], "correct_option": "Mais", "explanation": "'Mais' introduit une idée contraire à la précédente."},
            {"id": "1665_4", "type": "qcm", "question": "Quel connecteur introduit une alternative ?", "options": ["Ou", "Donc", "Car", "Mais"], "correct_option": "Ou", "explanation": "'Ou' propose un choix entre deux éléments."},
            {"id": "1665_5", "type": "vrai-faux", "question": "'Car' introduit une explication.", "correct": True, "explanation": "'Car' justifie ou explique ce qui précède."},
            {"id": "1665_6", "type": "qcm", "question": "Quel connecteur marque l'opposition ?", "options": ["Mais", "Donc", "Parce que", "Ou"], "correct_option": "Mais", "explanation": "'Mais' s'oppose à l'idée précédente."},
            {"id": "1665_7", "type": "qcm", "question": "Quel connecteur introduit une conséquence ?", "options": ["Donc", "Mais", "Ou", "Car"], "correct_option": "Donc", "explanation": "'Donc' marque la conséquence logique."},
            {"id": "1665_8", "type": "vrai-faux", "question": "'Parce que' peut introduire une justification.", "correct": True, "explanation": "Il explique la raison d'un fait ou d'une action."}
        ]
    ),
    (
        1666,
        "Diagnostic 2nde Français - Les champs lexicaux",
        "Français",
        "2nde",
        [
            {"id": "1666_1", "type": "qcm", "question": "Quel mot n'appartient pas au champ lexical de la peur ?", "options": ["Terreur", "Angoisse", "Joie", "Inquiétude"], "correct_option": "Joie", "explanation": "'Joie' appartient au champ lexical du bonheur, pas de la peur."},
            {"id": "1666_2", "type": "vrai-faux", "question": "Le champ lexical regroupe des mots liés par le sens.", "correct": True, "explanation": "Un champ lexical rassemble des mots qui évoquent une même idée ou notion."},
            {"id": "1666_3", "type": "qcm", "question": "Quel champ lexical domine dans : 'La tempête, le vent, la pluie, l'orage' ?", "options": ["La guerre", "La météo", "La peur", "La joie"], "correct_option": "La météo", "explanation": "Tous ces mots appartiennent au champ lexical de la météo."},
            {"id": "1666_4", "type": "qcm", "question": "Quel mot complète le champ lexical de la tristesse ?", "options": ["Bonheur", "Larmes", "Rire", "Fête"], "correct_option": "Larmes", "explanation": "'Larmes' est associé à la tristesse."},
            {"id": "1666_5", "type": "vrai-faux", "question": "Un texte peut contenir plusieurs champs lexicaux.", "correct": True, "explanation": "Un texte riche joue sur plusieurs champs lexicaux pour nuancer le sens."},
            {"id": "1666_6", "type": "qcm", "question": "Quel mot n'appartient pas au champ lexical de la lumière ?", "options": ["Clarté", "Obscurité", "Rayon", "Lueur"], "correct_option": "Obscurité", "explanation": "'Obscurité' est l'opposé de la lumière."},
            {"id": "1666_7", "type": "qcm", "question": "Quel champ lexical domine dans : 'Bataille, soldat, armée, victoire' ?", "options": ["La guerre", "La météo", "La tristesse", "La joie"], "correct_option": "La guerre", "explanation": "Tous ces mots appartiennent au champ lexical de la guerre."},
            {"id": "1666_8", "type": "vrai-faux", "question": "Le champ lexical permet d'enrichir la description.", "correct": True, "explanation": "Il permet de préciser l'atmosphère et les thèmes du texte."}
    ]
    ),
    (
        1667,
        "Diagnostic 2nde Français - Les synonymes et antonymes",
        "Français",
        "2nde",
        [
            {"id": "1667_1", "type": "qcm", "question": "Quel mot est synonyme de 'rapide' ?", "options": ["Lent", "Vif", "Triste", "Petit"], "correct_option": "Vif", "explanation": "'Vif' est un synonyme de 'rapide'."},
            {"id": "1667_2", "type": "vrai-faux", "question": "Un antonyme est un mot de sens opposé.", "correct": True, "explanation": "Exemple : 'grand' et 'petit' sont antonymes."},
            {"id": "1667_3", "type": "qcm", "question": "Quel mot est l'antonyme de 'clair' ?", "options": ["Lumineux", "Sombre", "Brillant", "Vif"], "correct_option": "Sombre", "explanation": "'Sombre' est l'opposé de 'clair'."},
            {"id": "1667_4", "type": "qcm", "question": "Quel mot est synonyme de 'beau' ?", "options": ["Joli", "Moche", "Petit", "Grand"], "correct_option": "Joli", "explanation": "'Joli' est un synonyme de 'beau'."},
            {"id": "1667_5", "type": "vrai-faux", "question": "Les synonymes enrichissent le vocabulaire.", "correct": True, "explanation": "Ils permettent d'éviter les répétitions et d'affiner le sens."},
            {"id": "1667_6", "type": "qcm", "question": "Quel mot est l'antonyme de 'heureux' ?", "options": ["Triste", "Content", "Joyeux", "Satisfait"], "correct_option": "Triste", "explanation": "'Triste' est l'opposé de 'heureux'."},
            {"id": "1667_7", "type": "qcm", "question": "Quel mot est synonyme de 'difficile' ?", "options": ["Simple", "Compliqué", "Petit", "Rapide"], "correct_option": "Compliqué", "explanation": "'Compliqué' est un synonyme de 'difficile'."},
            {"id": "1667_8", "type": "vrai-faux", "question": "Les antonymes permettent de nuancer le propos.", "correct": True, "explanation": "Ils enrichissent l'expression en introduisant des contrastes."}
        ]
    ),
    (
        1668,
        "Diagnostic 2nde Français - Les homonymes et paronymes",
        "Français",
        "2nde",
        [
            {"id": "1668_1", "type": "qcm", "question": "Quel mot est un homonyme de 'verre' ?", "options": ["Vert", "Rouge", "Bleu", "Noir"], "correct_option": "Vert", "explanation": "'Verre' et 'vert' se prononcent de la même façon mais n'ont pas le même sens."},
            {"id": "1668_2", "type": "vrai-faux", "question": "Les homonymes ont la même orthographe ou la même prononciation.", "correct": True, "explanation": "Exemple : 'mer', 'mère', 'maire'."},
            {"id": "1668_3", "type": "qcm", "question": "Quel mot est un paronyme de 'collision' ?", "options": ["Collusion", "Colis", "Colline", "Colle"], "correct_option": "Collusion", "explanation": "'Collision' et 'collusion' se ressemblent mais n'ont pas le même sens."},
            {"id": "1668_4", "type": "qcm", "question": "Quel mot n'est pas un homonyme de 'sang' ?", "options": ["Cent", "Sans", "Sens", "Sanglot"], "correct_option": "Sanglot", "explanation": "'Sanglot' n'a ni la même prononciation ni la même orthographe que 'sang'."},
            {"id": "1668_5", "type": "vrai-faux", "question": "Les paronymes prêtent souvent à confusion.", "correct": True, "explanation": "Leur ressemblance peut entraîner des erreurs de sens."},
            {"id": "1668_6", "type": "qcm", "question": "Quel mot est un homonyme de 'lait' ?", "options": ["Laid", "Laitue", "Laiton", "Laisse"], "correct_option": "Laid", "explanation": "'Lait' et 'laid' se prononcent pareil mais n'ont pas le même sens."},
            {"id": "1668_7", "type": "qcm", "question": "Quel mot est un paronyme de 'affluence' ?", "options": ["Influence", "Affluent", "Affluence", "Effluve"], "correct_option": "Influence", "explanation": "'Affluence' et 'influence' se ressemblent mais n'ont pas le même sens."},
            {"id": "1668_8", "type": "vrai-faux", "question": "Les homonymes enrichissent la langue.", "correct": True, "explanation": "Ils permettent des jeux de mots et des ambiguïtés."}
        ]
    ),
    (
        1669,
        "Diagnostic 2nde Français - Les niveaux de langue",
        "Français",
        "2nde",
        [
            {"id": "1669_1", "type": "qcm", "question": "Quel niveau de langue utilise 'enfant' pour 'gamin' ?", "options": ["Soutenu", "Courant", "Familier", "Poétique"], "correct_option": "Courant", "explanation": "'Enfant' est le terme courant, 'gamin' est familier."},
            {"id": "1669_2", "type": "vrai-faux", "question": "Le niveau soutenu s'utilise dans les textes littéraires.", "correct": True, "explanation": "Le niveau soutenu est attendu dans la littérature classique ou formelle."},
            {"id": "1669_3", "type": "qcm", "question": "Quel mot appartient au niveau familier ?", "options": ["Maman", "Mère", "Môman", "Papa"], "correct_option": "Môman", "explanation": "'Môman' est une déformation familière de 'maman'."},
            {"id": "1669_4", "type": "qcm", "question": "Dans quel contexte emploie-t-on le niveau courant ?", "options": ["Lettre officielle", "Discussion quotidienne", "Poème", "Discours politique"], "correct_option": "Discussion quotidienne", "explanation": "Le niveau courant est utilisé dans la vie de tous les jours."},
            {"id": "1669_5", "type": "vrai-faux", "question": "Le niveau familier est à éviter dans un écrit scolaire.", "correct": True, "explanation": "Il faut privilégier le niveau courant ou soutenu à l'écrit."},
            {"id": "1669_6", "type": "qcm", "question": "Quel synonyme de 'voiture' relève du niveau familier ?", "options": ["Bagnole", "Automobile", "Véhicule", "Cabriolet"], "correct_option": "Bagnole", "explanation": "'Bagnole' est un terme familier pour 'voiture'."},
            {"id": "1669_7", "type": "qcm", "question": "Quel niveau de langue privilégier dans un entretien d'embauche ?", "options": ["Familier", "Courant", "Soutenu", "Poétique"], "correct_option": "Courant", "explanation": "Le niveau courant est adapté à la plupart des situations professionnelles."},
            {"id": "1669_8", "type": "vrai-faux", "question": "Le niveau soutenu utilise des tournures recherchées.", "correct": True, "explanation": "Il privilégie la complexité syntaxique et le vocabulaire précis."}
            ]
    ),
    (
        1670,
        "Diagnostic 2nde Français - Les valeurs du présent",
        "Français",
        "2nde",
        [
            {"id": "1670_1", "type": "qcm", "question": "Quel emploi du présent exprime une vérité générale ?", "options": ["Présent d'énonciation", "Présent de vérité générale", "Présent de narration", "Présent d'habitude"], "correct_option": "Présent de vérité générale", "explanation": "Le présent de vérité générale exprime une réalité intemporelle."},
            {"id": "1670_2", "type": "vrai-faux", "question": "Le présent de narration actualise une action passée.", "correct": True, "explanation": "Il donne un effet de direct et de vivacité."},
            {"id": "1670_3", "type": "qcm", "question": "Quel emploi du présent exprime une action qui se répète ?", "options": ["Présent d'habitude", "Présent de narration", "Présent d'énonciation", "Présent de vérité générale"], "correct_option": "Présent d'habitude", "explanation": "Le présent d'habitude exprime la répétition."},
            {"id": "1670_4", "type": "qcm", "question": "Quel emploi du présent exprime ce qui se passe au moment où l'on parle ?", "options": ["Présent d'énonciation", "Présent de narration", "Présent d'habitude", "Présent de vérité générale"], "correct_option": "Présent d'énonciation", "explanation": "Le présent d'énonciation exprime l'action en cours."},
            {"id": "1670_5", "type": "vrai-faux", "question": "Le présent d'habitude exprime une action ponctuelle.", "correct": False, "explanation": "Il exprime la répétition, pas l'unicité."},
            {"id": "1670_6", "type": "qcm", "question": "Quel emploi du présent trouve-t-on dans : 'Le soleil se lève à l'est.' ?", "options": ["Présent d'habitude", "Présent de narration", "Présent de vérité générale", "Présent d'énonciation"], "correct_option": "Présent de vérité générale", "explanation": "C'est une vérité universelle."},
            {"id": "1670_7", "type": "qcm", "question": "Quel emploi du présent trouve-t-on dans : 'Il arrive, il s'assoit, il regarde.' ?", "options": ["Présent de narration", "Présent d'habitude", "Présent d'énonciation", "Présent de vérité générale"], "correct_option": "Présent de narration", "explanation": "Le présent de narration dynamise le récit."},
            {"id": "1670_8", "type": "vrai-faux", "question": "Le présent d'énonciation exprime ce qui se passe au moment où l'on parle.", "correct": True, "explanation": "Il exprime l'action en cours d'énonciation."}
            ]
    ),
    (
        1671,
        "Diagnostic 2nde Français - Les subordonnées circonstancielles",
        "Français",
        "2nde",
        [
            {"id": "1671_1", "type": "qcm", "question": "Quelle conjonction introduit une subordonnée de temps ?", "options": ["Parce que", "Quand", "Mais", "Ou"], "correct_option": "Quand", "explanation": "'Quand' introduit une subordonnée circonstancielle de temps."},
            {"id": "1671_2", "type": "vrai-faux", "question": "'Si' peut introduire une subordonnée de condition.", "correct": True, "explanation": "'Si' introduit la condition (ex : Si tu viens, je pars)."},
            {"id": "1671_3", "type": "qcm", "question": "Quelle conjonction introduit une subordonnée de cause ?", "options": ["Parce que", "Quand", "Si", "Mais"], "correct_option": "Parce que", "explanation": "'Parce que' introduit la cause."},
            {"id": "1671_4", "type": "qcm", "question": "Quelle conjonction introduit une subordonnée de but ?", "options": ["Pour que", "Quand", "Si", "Mais"], "correct_option": "Pour que", "explanation": "'Pour que' exprime le but recherché."},
            {"id": "1671_5", "type": "vrai-faux", "question": "'Bien que' introduit une subordonnée de concession.", "correct": True, "explanation": "'Bien que' marque l'opposition à une attente logique."},
            {"id": "1671_6", "type": "qcm", "question": "Quelle conjonction introduit une subordonnée de conséquence ?", "options": ["Si bien que", "Quand", "Si", "Mais"], "correct_option": "Si bien que", "explanation": "'Si bien que' exprime la conséquence."},
            {"id": "1671_7", "type": "qcm", "question": "Quelle conjonction introduit une subordonnée de comparaison ?", "options": ["Comme", "Quand", "Si", "Mais"], "correct_option": "Comme", "explanation": "'Comme' introduit la comparaison."},
            {"id": "1671_8", "type": "vrai-faux", "question": "Une subordonnée circonstancielle peut être supprimée sans changer le sens principal.", "correct": True, "explanation": "Elle apporte une précision mais n'est pas toujours essentielle au sens principal."}
            ]
    ),
    (
        1672,
        "Diagnostic 2nde Français - Les expansions du nom",
        "Français",
        "2nde",
        [
            {"id": "1672_1", "type": "qcm", "question": "Quel groupe de mots est une expansion du nom ?", "options": ["Le chat noir", "Il court vite", "Elle chante", "Nous partons"], "correct_option": "Le chat noir", "explanation": "'Noir' est une expansion du nom 'chat'."},
            {"id": "1672_2", "type": "vrai-faux", "question": "L'adjectif qualificatif est une expansion du nom.", "correct": True, "explanation": "Il précise le nom."},
            {"id": "1672_3", "type": "qcm", "question": "Quel type d'expansion est 'de Paris' dans 'la tour de Paris' ?", "options": ["Adjectif", "Complément du nom", "Proposition subordonnée", "Verbe"], "correct_option": "Complément du nom", "explanation": "'de Paris' précise le nom 'tour'."},
            {"id": "1672_4", "type": "qcm", "question": "Quel type d'expansion est 'qui chante' dans 'l'oiseau qui chante' ?", "options": ["Adjectif", "Complément du nom", "Proposition subordonnée relative", "Verbe"], "correct_option": "Proposition subordonnée relative", "explanation": "'qui chante' est une relative qui précise 'oiseau'."},
            {"id": "1672_5", "type": "vrai-faux", "question": "Les expansions du nom enrichissent la phrase.", "correct": True, "explanation": "Elles apportent des précisions et des nuances."},
            {"id": "1672_6", "type": "qcm", "question": "Quel mot n'est pas une expansion du nom ?", "options": ["Adjectif", "Verbe", "Complément du nom", "Proposition subordonnée relative"], "correct_option": "Verbe", "explanation": "Le verbe n'est pas une expansion du nom."},
            {"id": "1672_7", "type": "qcm", "question": "Quel type d'expansion est 'gentil' dans 'un garçon gentil' ?", "options": ["Adjectif", "Complément du nom", "Proposition subordonnée relative", "Verbe"], "correct_option": "Adjectif", "explanation": "'Gentil' précise le nom 'garçon'."},
            {"id": "1672_8", "type": "vrai-faux", "question": "Les expansions du nom sont toujours placées avant le nom.", "correct": False, "explanation": "Elles peuvent être placées avant ou après le nom."}
        ]
    ),
    (
        1673,
        "Diagnostic 2nde Français - Les propositions subordonnées relatives",
        "Français",
        "2nde",
        [
            {"id": "1673_1", "type": "qcm", "question": "Quel pronom relatif introduit une subordonnée relative ?", "options": ["Qui", "Mais", "Ou", "Donc"], "correct_option": "Qui", "explanation": "'Qui' est un pronom relatif classique."},
            {"id": "1673_2", "type": "vrai-faux", "question": "'Que', 'dont', 'où' sont aussi des pronoms relatifs.", "correct": True, "explanation": "Ils introduisent des relatives."},
            {"id": "1673_3", "type": "qcm", "question": "Dans 'Le livre que tu lis', 'que tu lis' est :", "options": ["Une proposition subordonnée relative", "Un adjectif", "Un verbe", "Un complément du nom"], "correct_option": "Une proposition subordonnée relative", "explanation": "Elle précise le nom 'livre'."},
            {"id": "1673_4", "type": "qcm", "question": "Quel est le rôle de la subordonnée relative ?", "options": ["Préciser un nom", "Remplacer un verbe", "Introduire une cause", "Exprimer une conséquence"], "correct_option": "Préciser un nom", "explanation": "La relative apporte une précision sur le nom."},
            {"id": "1673_5", "type": "vrai-faux", "question": "La subordonnée relative peut être supprimée sans changer le sens principal.", "correct": True, "explanation": "Elle apporte une précision mais n'est pas toujours essentielle."},
            {"id": "1673_6", "type": "qcm", "question": "Quel pronom relatif complète la phrase : 'La maison ___ j'habite' ?", "options": ["Où", "Mais", "Ou", "Donc"], "correct_option": "Où", "explanation": "'Où' indique le lieu."},
            {"id": "1673_7", "type": "qcm", "question": "Quel pronom relatif complète la phrase : 'L'élève ___ le professeur félicite' ?", "options": ["Que", "Qui", "Où", "Dont"], "correct_option": "Que", "explanation": "'Que' est COD du verbe 'félicite'."},
            {"id": "1673_8", "type": "vrai-faux", "question": "La subordonnée relative commence toujours par 'qui'.", "correct": False, "explanation": "Elle peut commencer par 'que', 'dont', 'où', etc."}
        ]
    ),
    (
        1674,
        "Diagnostic 2nde Français - Les connecteurs temporels",
        "Français",
        "2nde",
        [
            {"id": "1674_1", "type": "qcm", "question": "Quel connecteur exprime la simultanéité ?", "options": ["Quand", "Après", "Avant", "Depuis"], "correct_option": "Quand", "explanation": "'Quand' exprime que deux actions se passent en même temps."},
            {"id": "1674_2", "type": "vrai-faux", "question": "'Après' exprime la postériorité.", "correct": True, "explanation": "'Après' indique qu'une action suit une autre."},
            {"id": "1674_3", "type": "qcm", "question": "Quel connecteur exprime l'antériorité ?", "options": ["Avant", "Après", "Quand", "Depuis"], "correct_option": "Avant", "explanation": "'Avant' indique qu'une action précède une autre."},
            {"id": "1674_4", "type": "qcm", "question": "Quel connecteur exprime la durée ?", "options": ["Depuis", "Avant", "Après", "Quand"], "correct_option": "Depuis", "explanation": "'Depuis' exprime la durée d'une action commencée dans le passé."},
            {"id": "1674_5", "type": "vrai-faux", "question": "'Quand' peut exprimer la condition.", "correct": False, "explanation": "'Quand' exprime le temps, pas la condition."},
            {"id": "1674_6", "type": "qcm", "question": "Quel connecteur exprime la succession ?", "options": ["Après", "Avant", "Quand", "Depuis"], "correct_option": "Après", "explanation": "'Après' indique qu'une action suit une autre."},
            {"id": "1674_7", "type": "qcm", "question": "Quel connecteur exprime la cause ?", "options": ["Parce que", "Quand", "Après", "Avant"], "correct_option": "Parce que", "explanation": "'Parce que' exprime la cause d'une action."},
            {"id": "1674_8", "type": "vrai-faux", "question": "'Depuis' exprime la simultanéité.", "correct": False, "explanation": "'Depuis' exprime la durée, pas la simultanéité."}
        ]
    ),
    (
        1675,
        "Diagnostic 2nde Français - Les phrases complexes",
        "Français",
        "2nde",
        [
            {"id": "1675_1", "type": "qcm", "question": "Qu'est-ce qu'une phrase complexe ?", "options": ["Une phrase avec plusieurs propositions", "Une phrase très longue", "Une phrase avec beaucoup d'adjectifs", "Une phrase interrogative"], "correct_option": "Une phrase avec plusieurs propositions", "explanation": "La phrase complexe associe plusieurs propositions."},
            {"id": "1675_2", "type": "vrai-faux", "question": "Une phrase complexe peut contenir des subordonnées.", "correct": True, "explanation": "Les subordonnées sont une des formes de la complexité."},
            {"id": "1675_3", "type": "qcm", "question": "Quel type de proposition n'est pas subordonnée ?", "options": ["Indépendante", "Relative", "Conjonctive", "Circonstancielle"], "correct_option": "Indépendante", "explanation": "La proposition indépendante n'est pas subordonnée à une autre."},
            {"id": "1675_4", "type": "qcm", "question": "Quel mot relie deux propositions indépendantes ?", "options": ["Mais", "Qui", "Que", "Dont"], "correct_option": "Mais", "explanation": "'Mais' est une conjonction de coordination."},
            {"id": "1675_5", "type": "vrai-faux", "question": "Une phrase complexe peut être formée par juxtaposition.", "correct": True, "explanation": "La juxtaposition est une des façons d'associer des propositions."},
            {"id": "1675_6", "type": "qcm", "question": "Quel signe de ponctuation peut séparer deux propositions juxtaposées ?", "options": ["Virgule", "Point-virgule", "Deux-points", "Tous"], "correct_option": "Tous", "explanation": "Tous ces signes peuvent séparer des propositions juxtaposées."},
            {"id": "1675_7", "type": "qcm", "question": "Quel type de proposition commence par 'si' ?", "options": ["Subordonnée conditionnelle", "Relative", "Indépendante", "Circonstancielle"], "correct_option": "Subordonnée conditionnelle", "explanation": "'Si' introduit la condition."},
            {"id": "1675_8", "type": "vrai-faux", "question": "Une phrase complexe ne peut jamais être réduite à une phrase simple.", "correct": False, "explanation": "On peut parfois simplifier une phrase complexe."}
        ]
    ),
    (
        1676,
        "Diagnostic 2nde Français - Les figures de style",
        "Français",
        "2nde",
        [
            {"id": "1676_1", "type": "qcm", "question": "Quelle figure de style consiste à comparer deux éléments sans utiliser de mot de comparaison ?", "options": ["Métaphore", "Comparaison", "Personnification", "Hyperbole"], "correct_option": "Métaphore", "explanation": "La métaphore établit une relation d'identité entre deux éléments sans mot de comparaison."},
            {"id": "1676_2", "type": "vrai-faux", "question": "La comparaison utilise des mots comme 'comme', 'tel', 'ainsi que'.", "correct": True, "explanation": "Ces mots introduisent la comparaison."},
            {"id": "1676_3", "type": "qcm", "question": "Quelle figure de style attribue des caractéristiques humaines à un objet ou un animal ?", "options": ["Personnification", "Métaphore", "Comparaison", "Hyperbole"], "correct_option": "Personnification", "explanation": "La personnification donne des traits humains à des éléments inanimés."},
            {"id": "1676_4", "type": "qcm", "question": "Quelle figure de style consiste à exagérer une idée pour la mettre en valeur ?", "options": ["Hyperbole", "Comparaison", "Personnification", "Métaphore"], "correct_option": "Hyperbole", "explanation": "L'hyperbole amplifie une réalité pour créer un effet fort."},
            {"id": "1676_5", "type": "vrai-faux", "question": "La métaphore est une comparaison implicite.", "correct": True, "explanation": "Elle établit une relation d'identité sans mot de comparaison."},
            {"id": "1676_6", "type": "qcm", "question": "Quelle figure de style utilise 'comme' pour établir une ressemblance ?", "options": ["Comparaison", "Métaphore", "Personnification", "Hyperbole"], "correct_option": "Comparaison", "explanation": "'Comme' est un mot de comparaison classique."},
            {"id": "1676_7", "type": "qcm", "question": "Quelle figure de style attribue des qualités humaines à la nature ?", "options": ["Personnification", "Métaphore", "Comparaison", "Hyperbole"], "correct_option": "Personnification", "explanation": "La personnification est souvent utilisée pour donner vie à la nature."},
            {"id": "1676_8", "type": "vrai-faux", "question": "L'hyperbole est une figure de style qui minimise une idée.", "correct": False, "explanation": "L'hyperbole exagère une idée pour créer un effet fort."}
        ]
    ),
    (   1677,
        "Diagnostic 2nde Français - Les types de phrases",
        "Français",
        "2nde",
        [
            {"id": "1677_1", "type": "qcm", "question": "Quel type de phrase utilise-t-on pour faire une déclaration ?", "options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"], "correct_option": "Déclarative", "explanation": "La phrase déclarative sert à énoncer un fait ou une opinion."},
            {"id": "1677_2", "type": "vrai-faux", "question": "La phrase interrogative sert à poser une question.", "correct": True, "explanation": "Elle peut être directe ou indirecte."},
            {"id": "1677_3", "type": "qcm", "question": "Quel type de phrase utilise-t-on pour exprimer une émotion forte ?", "options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"], "correct_option": "Exclamative", "explanation": "La phrase exclamative exprime une émotion intense."},
            {"id": "1677_4", "type": "qcm", "question": "Quel type de phrase utilise-t-on pour donner un ordre ?", "options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"], "correct_option": "Impérative", "explanation": "La phrase impérative sert à ordonner ou conseiller."},
            {"id": "1677_5", "type": "vrai-faux", "question": "'Pourquoi' est un mot qui introduit une phrase interrogative.", "correct": True, "explanation": "'Pourquoi' est un mot interrogatif classique."},
            {"id": "1677_6", "type": "qcm", "question": "'Quelle belle journée !' est une phrase de quel type ?", "options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"], "correct_option": "Exclamative", "explanation": "'Quelle belle journée !' exprime une émotion forte."},
            {"id": "1677_7", "type": "qcm", "question": "'Ferme la porte.' est une phrase de quel type ?", "options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"], "correct_option": "Impérative", "explanation": "'Ferme la porte.' donne un ordre."},
            {"id": "1677_8", "type": "vrai-faux", "question": "La phrase déclarative peut être affirmative ou négative.", "correct": True, "explanation": "Elle peut énoncer une affirmation ou une négation."}
        ]
    ),
    (   1678,
        "Diagnostic 2nde Français - Les connecteurs logiques",
        "Français",
        "2nde",
        [
            {"id": "1678_1", "type": "qcm", "question": "Quel connecteur exprime l'addition ?", "options": ["Et", "Mais", "Ou", "Donc"], "correct_option": "Et", "explanation": "'Et' ajoute des éléments."},
            {"id": "1678_2", "type": "vrai-faux", "question": "'Mais' exprime l'opposition.", "correct": True, "explanation": "'Mais' introduit une idée contraire."},
            {"id": "1678_3", "type": "qcm", "question": "Quel connecteur exprime l'alternative ?", "options": ["Ou", "Et", "Mais", "Donc"], "correct_option": "Ou", "explanation": "'Ou' propose une alternative."},
            {"id": "1678_4", "type": "qcm", "question": "Quel connecteur exprime la conséquence ?", "options": ["Donc", "Et", "Mais", "Ou"], "correct_option": "Donc", "explanation": "'Donc' introduit une conséquence logique."},
            {"id": "1678_5", "type": "vrai-faux", "question": "'Parce que' est un connecteur logique qui exprime la cause.", "correct": True, "explanation": "'Parce que' explique la raison d'une action."},
            {"id": "1678_6", "type": "qcm", "question": "'Cependant' exprime :", "options": ["L'opposition", "L'addition", "La conséquence", "L'alternative"], "correct_option": "L'opposition", "explanation": "'Cependant' marque une opposition ou une restriction."},
            {"id": "1678_7", "type": "qcm", "question": "'En effet' exprime :", "options": ["La cause", "L'addition", "La conséquence", "L'alternative"], "correct_option": "La cause", "explanation": "'En effet' confirme ou explique une affirmation précédente."},
            {"id": "1678_8", "type": "vrai-faux", "question": "'Ou' exprime l'addition.", "correct": False, "explanation": "'Ou' exprime une alternative, pas une addition."}
            ]
    ),
    (   1679,
        "Diagnostic 2nde Français - Les champs lexicaux",
        "Français",
        "2nde",
        [
            {"id": "1679_1", "type": "qcm", "question": "Quel champ lexical domine dans : 'Océan, vague, marée, plage' ?", "options": ["La mer", "La montagne", "La ville", "La campagne"], "correct_option": "La mer", "explanation": "Tous ces mots appartiennent au champ lexical de la mer."},
            {"id": "1679_2", "type": "vrai-faux", "question": "Le champ lexical regroupe des mots liés à un même thème.", "correct": True, "explanation": "Il permet d'identifier les thèmes et les atmosphères d'un texte."},
            {"id": "1679_3", "type": "qcm", "question": "Quel champ lexical domine dans : 'Forêt, arbre, feuille, racine' ?", "options": ["La forêt", "La mer", "La ville", "La campagne"], "correct_option": "La forêt", "explanation": "Tous ces mots appartiennent au champ lexical de la forêt."},
            {"id": "1679_4", "type": "qcm", "question": "Quel champ lexical domine dans : 'École, professeur, élève, classe' ?", "options": ["L'école", "Le travail", "La famille", "Le sport"], "correct_option": "L'école", "explanation": "Tous ces mots appartiennent au champ lexical de l'école."},
            {"id": "1679_5", "type": "vrai-faux", "question": "'Amour', 'passion', 'cœur' font partie du même champ lexical.", "correct": True, "explanation": "'Amour', 'passion', 'cœur' sont liés au thème de l'amour."},
            {"id": "1679_6", "type": "qcm", "question": "Quel champ lexical domine dans : 'Voiture, route, voyage, destination' ?", "options": ["Le voyage", "Le sport", "La cuisine", "La musique"], "correct_option": "Le voyage", "explanation": "Tous ces mots sont liés au thème du voyage."},
            {"id": "1679_7", "type": "qcm", "question": "'Soleil', 'lune', 'étoile' font partie du même champ lexical.", "options": ["Le ciel", "La mer", "La terre", "La ville"], "correct_option": "Le ciel", "explanation": "'Soleil', 'lune', 'étoile' sont liés au thème du ciel."},
            {"id": "1679_8", "type": "vrai-faux", "question": "Le champ lexical permet d'analyser les thèmes d'un texte.", "correct": True, "explanation": "Il aide à comprendre les thèmes et les ambiances d'un texte."}
        ]
    ),
    (   1680,
        "Diagnostic 2nde Français - Les registres littéraires",
        "Français",
        "2nde",
        [
            {"id": "1680_1", "type": "qcm", "question": "Quel registre littéraire est caractérisé par l'exagération et le comique ?", "options": ["Comique", "Tragique", "Lyrique", "Épique"], "correct_option": "Comique", "explanation": "Le registre comique vise à faire rire ou sourire."},
            {"id": "1680_2", "type": "vrai-faux", "question": "Le registre tragique met en scène des personnages confrontés à des forces supérieures.", "correct": True, "explanation": "Il exprime la fatalité et la souffrance."},
            {"id": "1680_3", "type": "qcm", "question": "Quel registre littéraire est caractérisé par l'expression des sentiments personnels ?", "options": ["Lyrique", "Comique", "Tragique", "Épique"], "correct_option": "Lyrique", "explanation": "Le registre lyrique exprime les émotions intimes."},
            {"id": "1680_4", "type": "qcm", "question": "Quel registre littéraire est caractérisé par la mise en valeur des exploits héroïques ?", "options": ["Épique", "Comique", "Tragique", "Lyrique"], "correct_option": "Épique", "explanation": "Le registre épique célèbre les exploits et les héros."},
            {"id": "1680_5", "type": "vrai-faux", "question": "'Satirique' est un registre littéraire qui critique les travers de la société.", "correct": True, "explanation": "'Satirique' utilise l'humour pour dénoncer les défauts sociaux."},
            {"id": "1680_6", "type": "qcm", "question": "'Dramatique' est un synonyme de quel registre ?", "options": ["Tragique", "Comique", "Lyrique", "Épique"], "correct_option": "Tragique", "explanation": "'Dramatique' est souvent associé au tragique en raison de son intensité émotionnelle."},
            {"id": "1680_7", "type": "qcm", "question": "'Romantique' est un synonyme de quel registre ?", "options": ["Lyrique", "Comique", "Tragique", "Épique"],"correct_option": "Lyrique", "explanation": "'Romantique' est souvent associé au registre lyrique en raison de l'expression des émotions et des sentiments."},
            {"id": "1680_8", "type": "vrai-faux", "question": "Le registre épique met en scène des exploits héroïques et des actions grandioses.", "correct": True, "explanation": "Le registre épique valorise les exploits et l'héroïsme dans un ton exalté."}
        ]
    ),
    (   1681,
        "Diagnostic 2nde Français - Les homonymes et paronymes",
        "Français",
        "2nde",
        [
            {"id": "1681_1", "type": "qcm", "question": "Quel mot est un homonyme de 'verre' ?", "options": ["Vers", "Vert", "Ver", "Vair"], "correct_option": "Vers", "explanation": "'Verre' et 'vers' se prononcent pareil mais n'ont pas le même sens."},
            {"id": "1681_2", "type": "vrai-faux", "question": "'Mère' et 'mer' sont des homonymes.", "correct": True, "explanation": "'Mère' et 'mer' se prononcent pareil mais n'ont pas le même sens."},
            {"id": "1681_3", "type": "qcm", "question": "Quel mot est un homonyme de 'sang' ?", "options": ["Sans", "Cent", "Sang", "S'en"], "correct_option": "Sans", "explanation": "'Sang' et 'sans' se prononcent pareil mais n'ont pas le même sens."},
            {"id": "1681_4", "type": "qcm", "question": "'Cousin' et 'coussin' sont des :", "options": ["Homonymes", "Paronymes", "Synonymes", "Antonymes"],"correct_option": "Paronymes", "explanation": "'Cousin' et 'coussin' se ressemblent mais n'ont pas le même sens."},
            {"id": "1681_5", "type": "vrai-faux", "question": "'Coller' et 'colle' sont des homonymes.", "correct": True, "explanation": "'Coller' (verbe) et 'colle' (nom) se prononcent pareil mais n'ont pas le même sens."},
            {"id": "1681_6", "type": "qcm", "question": "Quel mot est un homonyme de 'lait' ?", "options": ["Laid", "Laitue", "Laiton", "Laisse"], "correct_option": "Laid", "explanation": "'Lait' et 'laid' se prononcent pareil mais n'ont pas le même sens."},
            {"id": "1681_7", "type": "qcm", "question": "'Émigré' et 'immigré' sont des :", "options": ["Homonymes", "Paronymes", "Synonymes", "Antonymes"], "correct_option": "Paronymes", "explanation": "'Émigré' et 'immigré' se ressemblent mais ont des sens différents."},
            {"id": "1681_8", "type": "vrai-faux", "question": "'Soleil' et 'soleille' sont des homonymes.", "correct": False, "explanation": "'Soleil' et 'soleille' ne sont pas des homonymes, ils ne se prononcent pas de la même manière."}
        ]
    ),
    (   1682,
        "Diagnostic 2nde Français - Les niveaux de langue",
        "Français",
        "2nde",
        [
            {"id": "1682_1", "type": "qcm", "question": "Quel niveau de langue est le plus formel ?", "options": ["Familier", "Courant", "Soutenu", "Poétique"], "correct_option": "Soutenu", "explanation": "Le niveau soutenu utilise un vocabulaire recherché et des tournures complexes."},
            {"id": "1682_2", "type": "vrai-faux", "question": "Le niveau familier est adapté à une conversation entre amis.", "correct": True, "explanation": "Le niveau familier est utilisé dans les échanges informels."},
            {"id": "1682_3", "type": "qcm", "question": "Quel niveau de langue privilégier dans un discours officiel ?", "options": ["Familier", "Courant", "Soutenu", "Poétique"], "correct_option": "Soutenu", "explanation": "Le niveau soutenu est adapté aux discours officiels."},
            {"id": "1682_4", "type": "qcm", "question": "'Salut' est un mot du niveau de langue :", "options": ["Familier", "Courant", "Soutenu", "Poétique"],"correct_option": "Familier", "explanation": "'Salut' est une salutation informelle."},
            {"id": "1682_5", "type": "vrai-faux", "question": "'Merci' est un mot du niveau de langue courant.", "correct": True, "explanation": "'Merci' est utilisé dans des situations courantes."},
            {"id": "1682_6", "type": "qcm", "question": "'Bonjour' est un mot du niveau de langue :", "options": ["Familier", "Courant", "Soutenu", "Poétique"],"correct_option": "Courant", "explanation": "'Bonjour' est une salutation standard utilisée dans la plupart des situations."},
            {"id": "1682_7", "type": "qcm", "question": "'Adieu' est un mot du niveau de langue :", "options": ["Familier", "Courant", "Soutenu", "Poétique"],"correct_option": "Soutenu", "explanation": "'Adieu' est une formule de départ très formelle et solennelle."},
            {"id": "1682_8", "type": "vrai-faux", "question": "'Merci beaucoup' est une expression du niveau de langue soutenu.", "correct": False, "explanation": "'Merci beaucoup' est une expression du niveau de langue courant."}
        ]
    ),
    (   1683,
        "Diagnostic 2nde Français - Les temps du passé",
        "Français",
        "2nde",
        [
            {"id": "1683_1", "type": "qcm", "question": "Quel temps du passé exprime une action achevée dans le passé ?", "options": ["Passé composé", "Imparfait", "Plus-que-parfait", "Passé simple"], "correct_option": "Passé composé", "explanation": "Le passé composé exprime une action achevée dans le passé."},
            {"id": "1683_2", "type": "vrai-faux", "question": "L'imparfait exprime une action habituelle ou une description dans le passé.", "correct": True, "explanation": "L'imparfait est utilisé pour les actions répétées ou les descriptions passées."},
            {"id": "1683_3", "type": "qcm", "question": "Quel temps du passé exprime une action antérieure à une autre action passée ?", "options": ["Passé composé", "Imparfait", "Plus-que-parfait", "Passé simple"], "correct_option": "Plus-que-parfait", "explanation": "Le plus-que-parfait exprime une action antérieure à une autre action passée."},
            {"id": "1683_4", "type": "qcm", "question": "Quel temps du passé est souvent utilisé dans la littérature pour raconter des événements passés ?", "options": ["Passé composé", "Imparfait", "Plus-que-parfait", "Passé simple"],"correct_option": "Passé simple", "explanation": "Le passé simple est un temps littéraire utilisé pour raconter des événements passés."},
            {"id": "1683_5", "type": "vrai-faux", "question": "'Hier' est un adverbe de temps qui peut être utilisé avec le passé composé.", "correct": True, "explanation": "'Hier' indique que l'action s'est déroulée la veille."},
            {"id": "1683_6", "type": "vrai-faux", "question": "'Quand j'étais jeune' est une expression qui introduit souvent l'imparfait ou le plus-que-parfait.", "correct": True, "explanation": "'Quand j'étais jeune' introduit souvent une description ou une action passée révolue."},
            {"id": "1683_7", "type": "qcm","question": "Quel temps du passé est utilisé pour exprimer une action ponctuelle dans le passé ?", "options": ["Passé composé", "Imparfait", "Plus-que-parfait", "Passé simple"],"correct_option": "Passé composé", "explanation": "Le passé composé est souvent utilisé pour exprimer des actions ponctuelles dans le passé."},
            {"id": "1683_8", "type": "vrai-faux", "question": "'Il y a deux jours' est une expression qui peut être utilisée avec le plus-que-parfait.", "correct": False, "explanation": "'Il y a deux jours' indique une action récente et est généralement utilisée avec le passé composé."}
        ]
    ),
    (   1684,
        "Diagnostic 2nde Français - Les modes verbaux",
        "Français",
        "2nde",
        [
            {"id": "1684_1", "type": "qcm", "question": "Quel mode verbal exprime une action certaine et réelle ?", "options": ["Indicatif", "Subjonctif", "Conditionnel", "Impératif"], "correct_option": "Indicatif", "explanation": "L'indicatif exprime des faits réels et certains."},
            {"id": "1684_2", "type": "vrai-faux", "question": "Le subjonctif exprime souvent le doute, le souhait ou la nécessité.", "correct": True, "explanation": "Le subjonctif est utilisé pour exprimer des sentiments, des doutes ou des nécessités."},
            {"id": "1684_3", "type": "qcm", "question": "Quel mode verbal exprime une action hypothétique ou conditionnelle ?", "options": ["Indicatif", "Subjonctif", "Conditionnel", "Impératif"],"correct_option": "Conditionnel", "explanation": "Le conditionnel exprime des actions qui dépendent d'une condition."},
            {"id": "1684_4", "type": "qcm", "question": "Quel mode verbal est utilisé pour donner un ordre ou un conseil ?", "options": ["Indicatif", "Subjonctif", "Conditionnel", "Impératif"],"correct_option": "Impératif", "explanation": "L'impératif est utilisé pour ordonner ou conseiller."},
            {"id": "1684_5", "type": "vrai-faux", 	"question": "'Il faut que' est une expression qui introduit souvent le subjonctif.", 	"correct": True, 	"explanation": "'Il faut que' exprime une nécessité et est suivi du subjonctif."},
            {"id": "1684_6", "type": "vrai-faux", "question": "'Si j'étais riche' est une expression qui introduit souvent le conditionnel.", "correct": True, "explanation": "'Si j'étais riche' introduit une condition hypothétique qui peut être suivie du conditionnel."},
            {"id": "1684_7", "type": "qcm","question": "'Va à l'école !' est une phrase à quel mode ?", "options": ["Indicatif", "Subjonctif", "Conditionnel", "Impératif"], "correct_option": "Impératif", "explanation": "L'impératif est utilisé pour donner un ordre ou un conseil."},
            {"id": "1684_8", "type": "vrai-faux", "question": "'Je pense que' est une expression qui introduit souvent l'indicatif.", "correct": True, "explanation": "'Je pense que' exprime une opinion et est généralement suivi de l'indicatif."}
        ]
    ),
    (   1685,
        "Diagnostic 2nde Français - Les expansions du nom",
        "Français",
        "2nde",
        [
            {"id": "1685_1", "type": "qcm", "question": "Quel est le rôle d'une expansion du nom ?", "options": ["Préciser un nom", "Remplacer un verbe", "Introduire une cause", "Exprimer une conséquence"], "correct_option": "Préciser un nom", "explanation": "L'expansion du nom apporte une précision sur le nom."},
            {"id": "1685_2", "type": "vrai-faux", "question": "Un adjectif peut être une expansion du nom.", "correct": True, "explanation": "L'adjectif qualificatif est une forme d'expansion du nom."},
            {"id": "1685_3", "type": "qcm", "question": "Quel groupe de mots peut être une expansion du nom ?", "options": ["Groupe nominal apposé", "Groupe verbal", "Groupe prépositionnel", "Groupe adverbial"],"correct_option": "Groupe nominal apposé", "explanation": "Le groupe nominal apposé est une expansion du nom qui apporte une précision."},
            {"id": "1685_4", "type": "qcm", "question": "'Le livre de Marie' contient une expansion du nom qui est :", "options": ["'de Marie'", "'Le'", "'Livre'", "'Marie'"],"correct_option": "'de Marie'", "explanation": "'de Marie' est un complément du nom qui précise 'livre'."},
            {"id": "1685_5", "type": "vrai-faux","question": "'Un homme courageux' contient une expansion du nom qui est 'courageux'.", 	"correct": True, 	"explanation": "'Courageux' est un adjectif qualificatif qui précise 'homme'."},
            {"id": "1685_6", "type": "qcm","question": "'La maison, grande et lumineuse, est à vendre.' contient une expansion du nom qui est :", 	"options": ["'grande et lumineuse'", "'La maison'", "'est à vendre'", "'à vendre'"],"correct_option": "'grande et lumineuse'", 	"explanation": "'Grande et lumineuse' est un groupe adjectival qui précise 'maison'."},
            {"id": "1685_7", "type": "qcm","question": "'Le chat de mon voisin' contient une expansion du nom qui est :", 	"options": ["'de mon voisin'", "'Le chat'", "'mon voisin'", "'chat'"],"correct_option": "'de mon voisin'", 	"explanation": "'De mon voisin' est un complément du nom qui précise 'chat'."},
            {"id": "1685_8", "type": "vrai-faux", "question": "'Un livre intéressant' contient une expansion du nom qui est 'intéressant'.", "correct": True, "explanation": "'Intéressant' est un adjectif qualificatif qui précise 'livre'."}
        ]
    ),
    (   1686,
        "Diagnostic 2nde Français - Les fonctions grammaticales",
        "Français",
        "2nde",
        [
            {"id": "1686_1", "type": "qcm", "question": "Quelle fonction grammaticale est exercée par le sujet d'une phrase ?", "options": ["Sujet", "Complément d'objet direct", "Complément d'objet indirect", "Attribut du sujet"], "correct_option": "Sujet", "explanation": "Le sujet est la fonction grammaticale qui désigne l'élément qui fait l'action ou dont on parle."},
            {"id": "1686_2", "type": "vrai-faux", "question": "Le complément d'objet direct (COD) répond à la question 'qui ?' ou 'quoi ?' après le verbe.", "correct": True, "explanation": "Le COD complète le verbe en répondant à ces questions."},
            {"id": "1686_3", "type": "qcm", "question": "Quelle fonction grammaticale est exercée par un mot qui complète un nom ?", "options": ["Complément du nom", "Sujet", "Attribut du sujet", "Complément d'objet direct"],"correct_option": "Complément du nom", "explanation": "Le complément du nom apporte une précision sur un nom."},
            {"id": "1686_4", "type": "qcm","question": "'Il est professeur.' contient un attribut du sujet qui est :", 	"options": ["'professeur'", "'Il'", "'est'", "'Il est'"],"correct_option": "'professeur'", 	"explanation": "'Professeur' est un attribut du sujet qui qualifie 'Il'."},
            {"id": "1686_5", "type": "vrai-faux","question": "'Le livre de Marie' contient un complément du nom qui est 'de Marie'.", 	"correct": True, 	"explanation": "'De Marie' est un complément du nom qui précise 'livre'."},
            {"id": "1686_6", "type": "qcm","question": "'Je parle à mon ami.' contient un complément d'objet indirect (COI) qui est :", 	"options": ["'à mon ami'", "'Je'", "'parle'", "'mon ami'"],"correct_option": "'à mon ami'", 	"explanation": "'À mon ami' est un complément d'objet indirect qui précise à qui l'action est destinée."},
            {"id": "1686_7", "type": "qcm","question": "'Le chat dort.' contient un sujet qui est :", 	"options": ["'Le chat'", "'dort'", "'Le'", "'chat'"],"correct_option": "'Le chat'", 	"explanation": "'Le chat' est le sujet de la phrase."},
            {"id": "1686_8", "type": "vrai-faux", "question": "'Il mange une pomme.' contient un complément d'objet direct (COD) qui est 'une pomme'.", "correct": True, "explanation": "'Une pomme' est le COD qui complète le verbe 'mange'."}
        ]
    ),
    (   1687,
        "Diagnostic 2nde Français - Les types de discours",
        "Français",
        "2nde",
        [
            {"id": "1687_1", "type": "qcm", "question": "Quel type de discours est utilisé pour raconter une histoire ?", "options": ["Discours narratif", "Discours descriptif", "Discours argumentatif", "Discours explicatif"], "correct_option": "Discours narratif", "explanation": "Le discours narratif sert à raconter des événements."},
            {"id": "1687_2", "type": "vrai-faux", "question": "Le discours descriptif vise à peindre une image ou une scène.", "correct": True, "explanation": "Le discours descriptif utilise des détails pour créer une image mentale."},
            {"id": "1687_3", "type": "qcm", "question": "Quel type de discours est utilisé pour convaincre ou persuader ?", "options": ["Discours argumentatif", "Discours narratif", "Discours descriptif", "Discours explicatif"],"correct_option": "Discours argumentatif", "explanation": "Le discours argumentatif présente des arguments pour défendre une opinion."},
            {"id": "1687_4", "type": "qcm","question": "'Pourquoi' est un mot qui introduit souvent quel type de discours ?", "options": ["Discours explicatif", "Discours narratif", "Discours descriptif", "Discours argumentatif"],"correct_option": "Discours explicatif", "explanation": "'Pourquoi' introduit souvent une explication ou une justification."},
            {"id": "1687_5", "type": "vrai-faux","question": "'Il était une fois' est une expression qui introduit souvent un discours narratif.", "correct": True, "explanation": "'Il était une fois' est une formule classique d'ouverture d'un récit."},
            {"id": "1687_6", "type": "qcm","question": "'Le ciel est bleu.' est un exemple de quel type de discours ?", "options": ["Discours descriptif", "Discours narratif", "Discours argumentatif", "Discours explicatif"],"correct_option": "Discours descriptif", "explanation": "'Le ciel est bleu.' décrit une caractéristique du ciel."},
            {"id": "1687_7", "type": "qcm","question": "'Il faut réduire les émissions de CO2 pour lutter contre le changement climatique.' est un exemple de quel type de discours ?", "options": ["Discours argumentatif", "Discours narratif", "Discours descriptif", "Discours explicatif"],"correct_option": "Discours argumentatif", "explanation": "Cette phrase présente un argument pour défendre une opinion."},
            {"id": "1687_8", "type": "vrai-faux", "question": "'Comment fonctionne un moteur à combustion interne ?' est une question qui introduit souvent un discours explicatif.", "correct": True, "explanation": "Cette question demande une explication sur le fonctionnement d'un moteur à combustion interne."}
        ]
    ),
    (   1688,
        "Diagnostic 2nde Français - Les figures de style",
        "Français",
        "2nde",
        [
            {"id": "1688_1", "type": "qcm", "question": "Quelle figure de style consiste à comparer deux éléments avec un mot de comparaison ?", "options": ["Comparaison", "Métaphore", "Personnification", "Hyperbole"], "correct_option": "Comparaison", "explanation": "La comparaison établit une ressemblance entre deux éléments à l'aide d'un mot de comparaison."},
            {"id": "1688_2", "type": "vrai-faux", "question": "'Le vent hurlait dans les arbres.' est un exemple de personnification.", "correct": True, "explanation": "La personnification attribue des qualités humaines au vent."},
            {"id": "1688_3", "type": "qcm", "question": "Quelle figure de style consiste à remplacer un terme par un autre qui lui est lié ?", "options": ["Métonymie", "Comparaison", "Personnification", "Hyperbole"],"correct_option": "Métonymie", "explanation": "La métonymie remplace un terme par un autre qui lui est associé."},
            {"id": "1688_4", "type": "qcm","question": "'Le roi a couronné son fils.' est un exemple de quelle figure de style ?", "options": ["Métonymie", "Comparaison", "Personnification", "Hyperbole"],"correct_option": "Métonymie", "explanation": "La métonymie remplace 'le roi' par 'son fils' pour désigner le successeur."},
            {"id": "1688_5", "type": "vrai-faux","question": "'Il a une mémoire d'éléphant.' est une hyperbole.", "correct": True, "explanation": "L'hyperbole exagère la capacité de mémoire en la comparant à celle d'un éléphant."},
            {"id": "1688_6", "type": "qcm","question": "'Le temps, ce voleur silencieux, emporte nos souvenirs.' est un exemple de quelle figure de style ?", "options": ["Personnification", "Comparaison", "Métonymie", "Hyperbole"],"correct_option": "Personnification", "explanation": "La personnification attribue des qualités humaines au temps."},
            {"id": "1688_7", "type": "qcm","question": "'Il est rapide comme l'éclair.' est un exemple de quelle figure de style ?", "options": ["Comparaison", "Métaphore", "Personnification", "Hyperbole"],"correct_option": "Comparaison", "explanation": "La comparaison établit une ressemblance entre la rapidité et l'éclair à l'aide du mot de comparaison 'comme'."},
            {"id": "1688_8", "type": "vrai-faux", "question": "'C'est un géant parmi les hommes.' est une métaphore.", "correct": True, "explanation": "La métaphore compare implicitement une personne à un géant sans utiliser de mot de comparaison."}
        ]
    ),
    (   1689,
        "Diagnostic 2nde Français - Les types de phrases",
        "Français",
        "2nde",
        [
            {"id": "1689_1", "type": "qcm", "question": "Quel type de phrase exprime une affirmation ?", "options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"], "correct_option": "Déclarative", "explanation": "La phrase déclarative énonce une information ou une affirmation."},
            {"id": "1689_2", "type": "vrai-faux", "question": "'Comment ça va ?' est une phrase interrogative.", "correct": True, "explanation": "'Comment ça va ?' est une question qui demande des informations."},
            {"id": "1689_3", "type": "qcm", "question": "'Ferme la porte.' est une phrase de quel type ?", "options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"],"correct_option": "Impérative", "explanation": "'Ferme la porte.' donne un ordre ou un conseil."},
            {"id": "1689_4", "type": "qcm","question": "'Quelle belle journée !' est une phrase de quel type ?", 	"options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"],"correct_option": "Exclamative", 	"explanation": "'Quelle belle journée !' exprime une émotion forte."},
            {"id": "1689_5", "type": "vrai-faux","question": "'Il pleut.' est une phrase déclarative.", 	"correct": True, 	"explanation": "'Il pleut.' énonce une information sur le temps."},
            {"id": "1689_6", "type": "qcm","question": "'Pourquoi es-tu en retard ?' est une phrase de quel type ?", 	"options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"],"correct_option": "Interrogative", 	"explanation": "'Pourquoi es-tu en retard ?' pose une question et demande une réponse."},
            {"id": "1689_7", "type": "qcm","question": "'Ferme la porte.' est une phrase de quel type ?", 	"options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"],"correct_option": "Impérative", 	"explanation": "'Ferme la porte.' donne un ordre ou un conseil."},
            {"id": "1689_8", "type": "qcm","question": "'Quelle belle journée !' est une phrase de quel type ?", 	"options": ["Déclarative", "Interrogative", "Impérative", "Exclamative"],"correct_option": "Exclamative", 	"explanation": "'Quelle belle journée !' exprime une émotion forte."}
        ]
    ),
    (   1690,
        "Diagnostic 2nde Français - Les subordonnées circonstancielles",
        "Français",
        "2nde",
        [
            {"id": "1690_1", "type": "qcm", "question": "Quelle est la fonction d'une subordonnée circonstancielle ?", "options": ["Complément de temps", "Complément de lieu", "Complément de cause", "Toutes les réponses sont correctes"], "correct_option": "Toutes les réponses sont correctes", "explanation": "Les subordonnées circonstancielles peuvent exprimer le temps, le lieu, la cause, la conséquence, etc."},
            {"id": "1690_2", "type": "vrai-faux", "question": "'Quand il pleut, je reste à la maison.' contient une subordonnée circonstancielle de temps.", "correct": True, "explanation": "'Quand il pleut' indique le moment où l'action se déroule."},
            {"id": "1690_3", "type": "qcm", "question": "'Où que tu ailles, je te suivrai.' contient une subordonnée circonstancielle de quel type ?", "options": ["Lieu", "Temps", "Cause", "Conséquence"],"correct_option": "Lieu", "explanation": "'Où que tu ailles' indique le lieu où l'action se déroule."},
            {"id": "1690_4", "type": "qcm","question": "'Parce qu'il était malade, il n'est pas venu.' contient une subordonnée circonstancielle de quel type ?", 	"options": ["Cause", "Temps", "Lieu", "Conséquence"],"correct_option": "Cause", 	"explanation": "'Parce qu'il était malade' explique la raison pour laquelle il n'est pas venu."},
            {"id": "1690_5", "type": "vrai-faux","question": "'Si tu étudies, tu réussiras.' contient une subordonnée circonstancielle de condition.", 	"correct": True, 	"explanation": "'Si tu étudies' exprime une condition pour que l'action suivante se réalise."},
            {"id": "1690_6", "type": "qcm","question": "'Il est parti tôt pour éviter les embouteillages.' contient une subordonnée circonstancielle de quel type ?", 	"options": ["But", "Cause", "Temps", "Lieu"],"correct_option": "But", 	"explanation": "'Pour éviter les embouteillages' indique le but de son départ tôt."},
            {"id": "1690_7", "type": "qcm","question": "'Il a tellement travaillé qu'il a réussi.' contient une subordonnée circonstancielle de quel type ?", 	"options": ["Conséquence", "Cause", "Temps", "Lieu"],"correct_option": "Conséquence", 	"explanation": "'Qu'il a réussi' indique la conséquence de son travail acharné."},
            {"id": "1690_8", "type": "vrai-faux", "question": "'Lorsque je serai grand, je veux être astronaute.' contient une subordonnée circonstancielle de temps.", "correct": True, "explanation": "'Lorsque je serai grand' indique le moment futur où l'action se déroulera."}
        ]
    )
]

if __name__ == "__main__":
    print("Generating Français 2nde quizzes...")
    write_quiz_files()

