#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Generation des quiz EMC 3eme - IDs 747-770
24 quizzes x 8 questions = 192 questions
Themes : Droits/Libertes, Citoyennete, Institutions, Laicite,
         Defense nationale, Enjeux sociaux contemporains
Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
"""

import json
import os
import random
import re
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
EMC3_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "emc_3eme_quizzes")
EMC3_QUIZ_DIR = os.path.join(EMC3_OUTPUT_DIR, "quiz")
EMC3_ANSWERS_DIR = os.path.join(EMC3_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "emc_3eme_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")
os.makedirs(EMC3_QUIZ_DIR, exist_ok=True)
os.makedirs(EMC3_ANSWERS_DIR, exist_ok=True)
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
    if qtype in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    return "vrai-faux"


def is_free_text_question_type(question_type):
    qtype = str(question_type or "").strip().lower().replace("_", "-")
    return qtype in {"texte", "text", "open"}


def choose_runtime_question_type(question):
    raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
    if raw_type == "qcm":
        return "qcm"
    if raw_type in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if raw_type in {"texte", "text", "open"}:
        if question.get("options"):
            return "qcm"
        return "vrai-faux"
    return "vrai-faux"


def build_qcm_choices(question, max_choices=4):
    choices = list(question.get("options", []))
    if choices:
        return choices
    correct_answer = str(question.get("correct_option", question.get("correct_answer", ""))).strip()
    if not correct_answer:
        return []
    default_choices = [
        correct_answer,
        "Une autre réponse",
        "Une réponse incorrecte",
        "Aucune de ces réponses",
    ]
    rnd = random.Random(str(question.get("id", "")) or correct_answer)
    rnd.shuffle(default_choices)
    return default_choices[:max_choices]


def build_true_false_statement(question_text, correct_answer, explanation):
    question_text = str(question_text or "").strip()
    answer = str(correct_answer or "").strip().rstrip(".!? ")
    detail = str(explanation or "").strip()

    if answer:
        if "_____" in question_text or "____" in question_text:
            return question_text.replace("_____", answer).replace("____", answer).rstrip() + "."
        if re.search(r"\bCompl[eé]tez\b", question_text, flags=re.I):
            return f"{question_text.rstrip('.!? ')} {answer}."
        if re.search(
            r'^(?:Compl[eé]tez|Explique|Expliquez|Distingue|Distinguez|Décris|Décrivez|Nommez|Justifie|Pourquoi|Comment|Qu\'est-ce que|Quel|Quels|Quelles|Donne|Donnez|Indique|Indiquez|Rappelle|Présente|Présentez)\b',
            question_text,
            flags=re.I,
        ):
            return f"Il est vrai que {answer}."
        return f"{question_text.rstrip('.!? ')}. La bonne réponse attendue est : {answer}."
    if detail:
        return detail if detail.endswith((".", "!", "?")) else f"{detail}."
    return question_text if question_text else "Cette affirmation est à évaluer."


def make_quiz(qid, title, subject, level, questions):
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []

    for question in questions:
        raw_type = str(question.get("type", "") or "").strip().lower().replace("_", "-")
        qtype = choose_runtime_question_type(question)
        if qtype == "qcm":
            choices = list(question.get("options", []))
            if not choices and is_free_text_question_type(question.get("type", "")):
                choices = build_qcm_choices(question)
            if choices:
                runtime_questions.append({
                    "type": "qcm",
                    "question": str(question.get("question", "")),
                    "choices": choices,
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
        qtype = choose_runtime_question_type(q)
        if qtype == "qcm":
            options = list(q.get("options", []))
            if not options and is_free_text_question_type(q.get("type", "")):
                options = build_qcm_choices(q)
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
    # BLOC 1 - DROITS ET LIBERTES FONDAMENTAUX (747-750)
    # =========================================================
    (747, "La dignite humaine et les droits fondamentaux", "EMC", "3eme", [
        {"id": "747_1", "type": "qcm",
         "question": "Quel texte fondateur proclame que 'tous les etres humains naissent libres et egaux en dignite et en droits' ?",
         "options": [
             "La Constitution francaise de 1958",
             "La Declaration universelle des droits de l'homme de 1948",
             "La Convention europeenne des droits de l'homme",
             "Le Preambule de la Constitution de 1946"
         ],
         "correct_option": "La Declaration universelle des droits de l'homme de 1948",
         "explanation": "La DUDH, adoptee le 10 decembre 1948 par l'ONU apres les horreurs de la Seconde Guerre mondiale, pose la dignite humaine comme fondement universel."},
        {"id": "747_2", "type": "vrai-faux",
         "question": "Les droits fondamentaux peuvent etre limites ou supprimes par un gouvernement democratique en toutes circonstances.",
         "correct": False,
         "explanation": "Les droits fondamentaux sont inviolables et inaliÃ©nables. MÃªme en Ã©tat d'urgence, un noyau dur de droits (interdiction de la torture, droit Ã  la vie) ne peut Ãªtre suspendu."},
        {"id": "747_3", "type": "texte",
         "question": "Comment appelle-t-on les droits qui ne peuvent pas etre retires a une personne car ils sont attaches a sa nature d'etre humain ?",
         "correct_answer": "droits inaliÃ©nables",
         "explanation": "Les droits inaliÃ©nables (vie, dignitÃ©, libertÃ©) appartiennent Ã  tout Ãªtre humain par nature et ne peuvent Ãªtre ni cÃ©dÃ©s ni supprimÃ©s."},
        {"id": "747_4", "type": "qcm",
         "question": "Quelle institution francaise est chargee de veiller au respect des droits fondamentaux et peut etre saisie par les citoyens ?",
         "options": [
             "Le Conseil d'Etat",
             "Le Defenseur des droits",
             "Le Conseil constitutionnel",
             "La Cour de cassation"
         ],
         "correct_option": "Le Defenseur des droits",
         "explanation": "Le DÃ©fenseur des droits (inscrit dans la Constitution en 2008) peut Ãªtre saisi gratuitement par tout citoyen s'estimant victime d'une discrimination ou d'un dysfonctionnement d'un service public."},
        {"id": "747_5", "type": "vrai-faux",
         "question": "La Cour europeenne des droits de l'homme (CEDH) siege a Strasbourg et peut condamner les Etats membres du Conseil de l'Europe.",
         "correct": True,
         "explanation": "La CEDH, crÃ©Ã©e par la Convention europÃ©enne des droits de l'homme (1950), peut condamner les Ã‰tats membres et les obliger Ã  rÃ©parer les violations."},
        {"id": "747_6", "type": "texte",
         "question": "Quel principe garantit qu'une personne ne peut pas etre jugee deux fois pour les memes faits ?",
         "correct_answer": "non bis in idem",
         "explanation": "Le principe non bis in idem (nul ne peut Ãªtre jugÃ© deux fois pour les mÃªmes faits) est un droit fondamental du procÃ¨s Ã©quitable."},
        {"id": "747_7", "type": "qcm",
         "question": "Que signifie la presomption d'innocence ?",
         "options": [
             "Tout accuse est coupable jusqu'a preuve du contraire",
             "Toute personne accusee est presumee innocente jusqu'a ce que sa culpabilite soit legalement etablie",
             "L'accuse n'a pas besoin d'avocat",
             "Le juge decide seul de la culpabilite"
         ],
         "correct_option": "Toute personne accusee est presumee innocente jusqu'a ce que sa culpabilite soit legalement etablie",
         "explanation": "La prÃ©somption d'innocence est un droit fondamental garanti par l'article 9 de la DDHC de 1789 et la CEDH ; la charge de la preuve repose sur l'accusation."},
        {"id": "747_8", "type": "vrai-faux",
         "question": "La torture est absolument interdite par le droit international, meme en periode de guerre ou de terrorisme.",
         "correct": True,
         "explanation": "L'interdiction de la torture est une norme de jus cogens (droit impÃ©ratif international) sans exception possible, mÃªme en Ã©tat d'urgence ou de guerre."},
    ]),

    (748, "La Declaration des droits de l'homme et du citoyen", "EMC", "3eme", [
        {"id": "748_1", "type": "qcm",
         "question": "En quelle annee a ete adoptee la Declaration des droits de l'homme et du citoyen (DDHC) ?",
         "options": ["1776", "1789", "1848", "1905"],
         "correct_option": "1789",
         "explanation": "La DDHC a Ã©tÃ© adoptÃ©e le 26 aoÃ»t 1789 par l'AssemblÃ©e nationale constituante, au dÃ©but de la RÃ©volution franÃ§aise."},
        {"id": "748_2", "type": "vrai-faux",
         "question": "La DDHC de 1789 a une valeur constitutionnelle en France aujourd'hui.",
         "correct": True,
         "explanation": "Le Conseil constitutionnel a reconnu en 1971 la valeur constitutionnelle de la DDHC via le PrÃ©ambule de la Constitution de 1958, formant le 'bloc de constitutionnalitÃ©'."},
        {"id": "748_3", "type": "texte",
         "question": "Quels sont les trois droits naturels et imprescriptibles de l'homme cites dans l'article 2 de la DDHC ?",
         "correct_answer": "liberte, propriete, surete et resistance a l'oppression",
         "explanation": "L'article 2 cite : libertÃ©, propriÃ©tÃ©, sÃ»retÃ© et rÃ©sistance Ã  l'oppression comme droits naturels et imprescriptibles."},
        {"id": "748_4", "type": "qcm",
         "question": "Selon la DDHC, le principe de toute souverainete reside essentiellement dans :",
         "options": [
             "Le roi",
             "La Nation",
             "Le Parlement",
             "L'Eglise"
         ],
         "correct_option": "La Nation",
         "explanation": "Article 3 de la DDHC : 'Le principe de toute souverainetÃ© rÃ©side essentiellement dans la Nation. Nul corps, nul individu ne peut exercer d'autoritÃ© qui n'en Ã©mane expressÃ©ment.'"},
        {"id": "748_5", "type": "vrai-faux",
         "question": "La DDHC interdit toute arrestation arbitraire sans jugement.",
         "correct": True,
         "explanation": "L'article 7 de la DDHC dispose que nul ne peut Ãªtre arrÃªtÃ© que dans les cas dÃ©terminÃ©s par la loi ; toute arrestation arbitraire est punie."},
        {"id": "748_6", "type": "texte",
         "question": "Quelle celebre devise republicaine resume les valeurs proclamees par la Revolution francaise de 1789 ?",
         "correct_answer": "Liberte, Egalite, Fraternite",
         "explanation": "La devise 'LibertÃ©, Ã‰galitÃ©, FraternitÃ©' est la devise officielle de la RÃ©publique franÃ§aise, inscrite dans la Constitution."},
        {"id": "748_7", "type": "qcm",
         "question": "Quel article de la DDHC pose le principe de la liberte d'opinion, meme religieuse ?",
         "options": [
             "Article 1 (egalite des droits)",
             "Article 10 (liberte d'opinion)",
             "Article 16 (separation des pouvoirs)",
             "Article 17 (droit de propriete)"
         ],
         "correct_option": "Article 10 (liberte d'opinion)",
         "explanation": "L'article 10 garantit que nul ne doit Ãªtre inquiÃ©tÃ© pour ses opinions, mÃªme religieuses, pourvu que leur manifestation ne trouble pas l'ordre public."},
        {"id": "748_8", "type": "vrai-faux",
         "question": "La DDHC de 1789 accordait le droit de vote aux femmes.",
         "correct": False,
         "explanation": "La DDHC de 1789 ne reconnaissait les droits civiques qu'aux hommes. Les femmes ont obtenu le droit de vote en France seulement en 1944."},
    ]),

    (749, "Les droits de l'enfant et la Convention internationale", "EMC", "3eme", [
        {"id": "749_1", "type": "qcm",
         "question": "En quelle annee a ete adoptee la Convention internationale des droits de l'enfant (CIDE) par l'ONU ?",
         "options": ["1948", "1959", "1989", "2000"],
         "correct_option": "1989",
         "explanation": "La CIDE a Ã©tÃ© adoptÃ©e le 20 novembre 1989 par l'AssemblÃ©e gÃ©nÃ©rale de l'ONU. RatifiÃ©e par 196 Ã‰tats, c'est le traitÃ© international le plus ratifiÃ© au monde."},
        {"id": "749_2", "type": "vrai-faux",
         "question": "La CIDE reconnaÃ®t que tout enfant a droit Ã  une identitÃ©, une nationalitÃ© et un nom dÃ¨s sa naissance.",
         "correct": True,
         "explanation": "L'article 7 de la CIDE garantit Ã  tout enfant le droit d'Ãªtre enregistrÃ© Ã  sa naissance, d'avoir un nom et une nationalitÃ©."},
        {"id": "749_3", "type": "texte",
         "question": "Quel est le principe directeur fondamental de la CIDE qui doit guider toute decision concernant un enfant ?",
         "correct_answer": "interet superieur de l'enfant",
         "explanation": "L'article 3 de la CIDE pose que 'l'intÃ©rÃªt supÃ©rieur de l'enfant' doit Ãªtre une considÃ©ration primordiale dans toutes les dÃ©cisions le concernant."},
        {"id": "749_4", "type": "qcm",
         "question": "Quel age minimal de travail la CIDE tend-elle a proteger ?",
         "options": [
             "Tout travail est interdit avant 18 ans",
             "La CIDE protege contre le travail dangereux et l'exploitation ; l'age varie selon les pays",
             "Les enfants peuvent travailler a partir de 10 ans",
             "Il n'y a pas de protection contre le travail dans la CIDE"
         ],
         "correct_option": "La CIDE protege contre le travail dangereux et l'exploitation ; l'age varie selon les pays",
         "explanation": "L'article 32 protÃ¨ge les enfants contre l'exploitation Ã©conomique et les travaux dangereux, sans fixer un Ã¢ge universel absolu."},
        {"id": "749_5", "type": "vrai-faux",
         "question": "En France, l'instruction est obligatoire de 3 a 16 ans.",
         "correct": True,
         "explanation": "Depuis la loi du 26 juillet 2019, l'instruction est obligatoire en France de 3 Ã  16 ans (auparavant de 6 Ã  16 ans)."},
        {"id": "749_6", "type": "texte",
         "question": "Quel organisme de l'ONU surveille l'application de la CIDE et recoit les rapports des Etats ?",
         "correct_answer": "Comite des droits de l'enfant",
         "explanation": "Le ComitÃ© des droits de l'enfant de l'ONU surveille la mise en Å“uvre de la CIDE et examine les rapports pÃ©riodiques des Ã‰tats parties."},
        {"id": "749_7", "type": "qcm",
         "question": "Que garantit le droit a l'education selon la CIDE ?",
         "options": [
             "L'acces gratuit et obligatoire a l'enseignement superieur",
             "L'acces gratuit a l'enseignement primaire et le developpement de la personnalite",
             "L'enseignement uniquement dans la langue maternelle",
             "L'enseignement prive uniquement"
         ],
         "correct_option": "L'acces gratuit a l'enseignement primaire et le developpement de la personnalite",
         "explanation": "L'article 28 garantit l'enseignement primaire gratuit et obligatoire ; l'article 29 prÃ©cise que l'Ã©ducation doit viser le plein Ã©panouissement de l'enfant."},
        {"id": "749_8", "type": "vrai-faux",
         "question": "La CIDE reconnait aux enfants le droit d'exprimer librement leur opinion sur les questions les concernant.",
         "correct": True,
         "explanation": "L'article 12 de la CIDE garantit Ã  l'enfant capable de discernement le droit d'exprimer librement son opinion et d'Ãªtre entendu dans les procÃ©dures le concernant."},
    ]),

    (750, "Etat de droit et principes juridiques fondamentaux", "EMC", "3eme", [
        {"id": "750_1", "type": "qcm",
         "question": "Qu'est-ce qu'un Etat de droit ?",
         "options": [
             "Un Etat tres puissant militairement",
             "Un Etat dans lequel toutes les personnes et institutions sont soumises a la loi",
             "Un Etat sans constitution ecrite",
             "Un Etat gouverne par des juristes"
         ],
         "correct_option": "Un Etat dans lequel toutes les personnes et institutions sont soumises a la loi",
         "explanation": "L'Ã‰tat de droit (Rechtsstaat) est un systÃ¨me oÃ¹ l'Ã‰tat lui-mÃªme est soumis au droit ; le pouvoir est encadrÃ© par des rÃ¨gles juridiques supÃ©rieures."},
        {"id": "750_2", "type": "vrai-faux",
         "question": "Dans un Etat de droit, le gouvernement peut agir en dehors du cadre juridique en situation d'urgence.",
         "correct": False,
         "explanation": "MÃªme en Ã©tat d'urgence, le gouvernement reste soumis aux rÃ¨gles constitutionnelles ; des dispositions spÃ©ciales peuvent s'appliquer, mais le contrÃ´le juridictionnel demeure."},
        {"id": "750_3", "type": "texte",
         "question": "Quel principe juridique signifie que la loi ne peut pas s'appliquer a des actes anterieurs a son entree en vigueur ?",
         "correct_answer": "non-retroactivite des lois",
         "explanation": "Le principe de non-rÃ©troactivitÃ© garantit la sÃ©curitÃ© juridique : une loi nouvelle ne peut pas rendre illÃ©gal un acte qui Ã©tait lÃ©gal au moment oÃ¹ il a Ã©tÃ© commis."},
        {"id": "750_4", "type": "qcm",
         "question": "Quelle est la hierarchie des normes juridiques en France (du plus haut au plus bas) ?",
         "options": [
             "Constitution > Lois ordinaires > Decrets > Arretes",
             "Lois ordinaires > Constitution > Traites > Decrets",
             "Decrets > Lois > Constitution > Traites",
             "Arretes > Decrets > Constitution > Lois"
         ],
         "correct_option": "Constitution > Lois ordinaires > Decrets > Arretes",
         "explanation": "La hiÃ©rarchie des normes (Kelsen) : Constitution (sommet) > traitÃ©s internationaux > lois organiques > lois ordinaires > dÃ©crets > arrÃªtÃ©s."},
        {"id": "750_5", "type": "vrai-faux",
         "question": "Le Conseil constitutionnel peut censurer une loi contraire a la Constitution.",
         "correct": True,
         "explanation": "Le Conseil constitutionnel contrÃ´le la constitutionnalitÃ© des lois ; depuis 2010, la QPC (Question Prioritaire de ConstitutionnalitÃ©) permet aux citoyens de contester une loi."},
        {"id": "750_6", "type": "texte",
         "question": "Comment appelle-t-on le principe selon lequel toute personne faisant l'objet d'une procedure doit pouvoir se defendre ?",
         "correct_answer": "droits de la defense",
         "explanation": "Les droits de la dÃ©fense (droit au contradictoire, droit Ã  un avocat, droit d'Ãªtre entendu) sont garantis par l'Ã‰tat de droit dans toute procÃ©dure judiciaire."},
        {"id": "750_7", "type": "qcm",
         "question": "Quel principe separe les fonctions de faire la loi, de l'executer et de la faire respecter ?",
         "options": [
             "Principe de subsidiarite",
             "Separation des pouvoirs",
             "Principe de proportionnalite",
             "Federalisme"
         ],
         "correct_option": "Separation des pouvoirs",
         "explanation": "La sÃ©paration des pouvoirs (Montesquieu, 'De l'Esprit des lois', 1748) distingue pouvoir lÃ©gislatif, exÃ©cutif et judiciaire pour Ã©viter l'abus de pouvoir."},
        {"id": "750_8", "type": "vrai-faux",
         "question": "En France, la justice est rendue au nom du peuple francais.",
         "correct": True,
         "explanation": "Les dÃ©cisions de justice sont rendues 'au nom du peuple franÃ§ais', soulignant que l'autoritÃ© judiciaire tire sa lÃ©gitimitÃ© de la souverainetÃ© nationale."},
    ]),

    # =========================================================
    # BLOC 2 - CITOYENNETE FRANCAISE ET EUROPEENNE (751-754)
    # =========================================================
    (751, "La citoyennete francaise : droits et devoirs", "EMC", "3eme", [
        {"id": "751_1", "type": "qcm",
         "question": "A quel age peut-on voter en France pour les elections politiques ?",
         "options": ["16 ans", "18 ans", "21 ans", "25 ans"],
         "correct_option": "18 ans",
         "explanation": "En France, le droit de vote est accordÃ© Ã  18 ans depuis 1974 (abaissement de 21 Ã  18 ans sous ValÃ©ry Giscard d'Estaing)."},
        {"id": "751_2", "type": "vrai-faux",
         "question": "Le service national universel (SNU) est obligatoire pour tous les jeunes Francais de 16 ans.",
         "correct": False,
         "explanation": "Le SNU (crÃ©Ã© en 2019) est encore en phase de gÃ©nÃ©ralisation et non pleinement obligatoire ; son extension progressive est en cours de discussion."},
        {"id": "751_3", "type": "texte",
         "question": "Quel est le principal devoir civique des citoyens francais inscrit dans la Constitution ?",
         "correct_answer": "participer a la defense nationale",
         "explanation": "L'article 34 de la Constitution et l'ordonnance de 1959 posent que la dÃ©fense nationale est un devoir pour tout citoyen franÃ§ais."},
        {"id": "751_4", "type": "qcm",
         "question": "Qu'est-ce que la nationalite francaise par le droit du sol ?",
         "options": [
             "Etre ne de parents francais",
             "Etre ne sur le territoire francais et y avoir grandi",
             "Avoir ete naturalise apres 5 ans de residence",
             "Etre marie a un citoyen francais"
         ],
         "correct_option": "Etre ne sur le territoire francais et y avoir grandi",
         "explanation": "Le droit du sol (jus soli) accorde la nationalitÃ© franÃ§aise Ã  une personne nÃ©e en France d'un parent Ã©tranger, sous conditions de rÃ©sidence habituelle."},
        {"id": "751_5", "type": "vrai-faux",
         "question": "Un citoyen francais peut perdre sa nationalite s'il acquiert une autre nationalite.",
         "correct": False,
         "explanation": "La France autorise la double nationalitÃ© ; acquÃ©rir une autre nationalitÃ© ne fait pas automatiquement perdre la nationalitÃ© franÃ§aise."},
        {"id": "751_6", "type": "texte",
         "question": "Comment appelle-t-on le document officiel attestant de la nationalite francaise d'une personne ?",
         "correct_answer": "certificat de nationalite francaise",
         "explanation": "Le certificat de nationalitÃ© franÃ§aise est dÃ©livrÃ© par le greffe du tribunal judiciaire ; il atteste officiellement de la nationalitÃ©."},
        {"id": "751_7", "type": "qcm",
         "question": "Quel est le principal droit politique reserve aux citoyens francais et non aux simples residents ?",
         "options": [
             "Droit de travailler",
             "Droit de vote aux elections presidentielles et legislatives",
             "Droit a l'education",
             "Droit a la securite sociale"
         ],
         "correct_option": "Droit de vote aux elections presidentielles et legislatives",
         "explanation": "Le droit de vote aux Ã©lections nationales (prÃ©sidentielle, lÃ©gislatives, sÃ©natoriales) est rÃ©servÃ© aux citoyens franÃ§ais. Les rÃ©sidents europÃ©ens peuvent voter aux municipales et europÃ©ennes."},
        {"id": "751_8", "type": "vrai-faux",
         "question": "Payer ses impots est un devoir civique pour tout resident en France.",
         "correct": True,
         "explanation": "L'article 13 de la DDHC pose que 'pour l'entretien de la force publique et pour les dÃ©penses d'administration, une contribution commune est indispensable.' L'impÃ´t est un devoir civique."},
    ]),

    (752, "La citoyennete europeenne", "EMC", "3eme", [
        {"id": "752_1", "type": "qcm",
         "question": "Qu'est-ce que la citoyennete europeenne ?",
         "options": [
             "Un statut accordÃ© uniquement aux fonctionnaires europÃ©ens",
             "Un statut complÃ©mentaire Ã  la nationalitÃ© nationale accordÃ© Ã  tout ressortissant d'un Ã‰tat membre de l'UE",
             "La possibilite de vivre dans n'importe quel pays du monde",
             "Un passeport commun a tous les pays europeens"
         ],
         "correct_option": "Un statut complementaire a la nationalite nationale accorde a tout ressortissant d'un Etat membre de l'UE",
         "explanation": "La citoyennetÃ© europÃ©enne (instituÃ©e par le traitÃ© de Maastricht, 1992) est accordÃ©e Ã  toute personne ayant la nationalitÃ© d'un Ã‰tat membre de l'UE."},
        {"id": "752_2", "type": "vrai-faux",
         "question": "Un citoyen europeen peut voter et se presenter aux elections municipales dans tout Etat membre de l'UE ou il reside.",
         "correct": True,
         "explanation": "Tout citoyen de l'UE rÃ©sidant dans un autre Ã‰tat membre peut voter et Ãªtre candidat aux Ã©lections municipales et europÃ©ennes dans cet Ã‰tat."},
        {"id": "752_3", "type": "texte",
         "question": "Comment appelle-t-on le droit des citoyens europeens de circuler et de s'installer librement dans tout Etat membre de l'UE ?",
         "correct_answer": "libre circulation des personnes",
         "explanation": "La libre circulation des personnes est une des quatre libertÃ©s fondamentales du marchÃ© unique europÃ©en (avec biens, services et capitaux)."},
        {"id": "752_4", "type": "qcm",
         "question": "Combien de pays membres compose l'Union europeenne en 2024 ?",
         "options": ["25", "27", "30", "35"],
         "correct_option": "27",
         "explanation": "Depuis le Brexit (sortie du Royaume-Uni en 2020), l'UE compte 27 Ã‰tats membres."},
        {"id": "752_5", "type": "vrai-faux",
         "question": "L'espace Schengen coincide exactement avec les frontieres de l'Union europeenne.",
         "correct": False,
         "explanation": "L'espace Schengen (sans contrÃ´les aux frontiÃ¨res) inclut des pays non membres de l'UE (NorvÃ¨ge, Islande, Suisse) et exclut certains membres de l'UE (Irlande)."},
        {"id": "752_6", "type": "texte",
         "question": "Quel mecanisme permet a 1 million de citoyens europeens de demander a la Commission europeenne de proposer une nouvelle legislation ?",
         "correct_answer": "initiative citoyenne europeenne",
         "explanation": "L'ICE (Initiative Citoyenne EuropÃ©enne), introduite par le TraitÃ© de Lisbonne (2009), permet Ã  1 million de signataires d'au moins 7 Ã‰tats membres de proposer une lÃ©gislation."},
        {"id": "752_7", "type": "qcm",
         "question": "Quel traite a donne son nom actuel a l'Union europeenne et a institue la citoyennete europeenne ?",
         "options": [
             "Traite de Rome (1957)",
             "Acte unique europeen (1986)",
             "Traite de Maastricht (1992)",
             "Traite de Lisbonne (2007)"
         ],
         "correct_option": "Traite de Maastricht (1992)",
         "explanation": "Le traitÃ© de Maastricht (1er novembre 1993) a crÃ©Ã© l'Union europÃ©enne, instituÃ© la citoyennetÃ© europÃ©enne et posÃ© les bases de l'Union Ã©conomique et monÃ©taire."},
        {"id": "752_8", "type": "vrai-faux",
         "question": "Les citoyens europeens peuvent adresser une petition au Parlement europeen sur tout sujet relevant des competences de l'UE.",
         "correct": True,
         "explanation": "Le droit de pÃ©tition au Parlement europÃ©en est un droit fondamental de la citoyennetÃ© europÃ©enne reconnu par les traitÃ©s."},
    ]),

    (753, "Les elections et la democratie representative", "EMC", "3eme", [
        {"id": "753_1", "type": "qcm",
         "question": "Quel type de scrutin est utilise en France pour l'election presidentielle ?",
         "options": [
             "Scrutin proportionnel a un tour",
             "Scrutin uninominal majoritaire a deux tours",
             "Scrutin de liste a la proportionnelle",
             "Vote par approbation"
         ],
         "correct_option": "Scrutin uninominal majoritaire a deux tours",
         "explanation": "L'Ã©lection prÃ©sidentielle franÃ§aise se dÃ©roule au scrutin uninominal majoritaire Ã  deux tours ; le candidat ayant obtenu la majoritÃ© absolue au 1er tour (ou la majoritÃ© relative au 2nd tour) est Ã©lu."},
        {"id": "753_2", "type": "vrai-faux",
         "question": "Le vote blanc est comptabilise dans les suffrages exprimes en France depuis 2014.",
         "correct": False,
         "explanation": "Depuis la loi du 21 fÃ©vrier 2014, le vote blanc est comptabilisÃ© sÃ©parÃ©ment mais n'est toujours pas assimilÃ© aux suffrages exprimÃ©s ; il ne peut pas faire Ã©lire ni Ã©liminer un candidat."},
        {"id": "753_3", "type": "texte",
         "question": "Comment appelle-t-on le fait de ne pas voter lors d'une election, alors qu'on y est eligible ?",
         "correct_answer": "abstention",
         "explanation": "L'abstention dÃ©signe le fait de ne pas participer Ã  un scrutin. Elle est souvent analysÃ©e comme un indicateur de dÃ©senchantement dÃ©mocratique."},
        {"id": "753_4", "type": "qcm",
         "question": "Quelle institution est chargee de surveiller la regularite des elections en France ?",
         "options": [
             "Le Conseil d'Etat",
             "Le Conseil constitutionnel",
             "La Cour de cassation",
             "Le Senat"
         ],
         "correct_option": "Le Conseil constitutionnel",
         "explanation": "Le Conseil constitutionnel est juge Ã©lectoral suprÃªme ; il veille Ã  la rÃ©gularitÃ© des Ã©lections prÃ©sidentielles, lÃ©gislatives et des rÃ©fÃ©rendums."},
        {"id": "753_5", "type": "vrai-faux",
         "question": "La democratie directe signifie que les citoyens votent directement sur les lois sans representants.",
         "correct": True,
         "explanation": "La dÃ©mocratie directe (ex. rÃ©fÃ©rendum, initiative populaire) implique la participation directe des citoyens aux dÃ©cisions politiques, sans intermÃ©diaires Ã©lus."},
        {"id": "753_6", "type": "texte",
         "question": "Comment appelle-t-on un vote par lequel le peuple se prononce directement sur une question ou un texte de loi ?",
         "correct_answer": "referendum",
         "explanation": "Le rÃ©fÃ©rendum est un vote direct des citoyens sur une question prÃ©cise (ex. rÃ©fÃ©rendum sur la Constitution europÃ©enne en 2005 en France)."},
        {"id": "753_7", "type": "qcm",
         "question": "Quel est le mandat du President de la Republique francaise ?",
         "options": [
             "4 ans renouvelable indefiniment",
             "5 ans renouvelable une seule fois",
             "7 ans non renouvelable",
             "6 ans renouvelable deux fois"
         ],
         "correct_option": "5 ans renouvelable une seule fois",
         "explanation": "Depuis le rÃ©fÃ©rendum de 2000, le mandat prÃ©sidentiel est de 5 ans (quinquennat), renouvelable une seule fois consÃ©cutivement."},
        {"id": "753_8", "type": "vrai-faux",
         "question": "Les femmes peuvent voter en France depuis 1944.",
         "correct": True,
         "explanation": "L'ordonnance du 21 avril 1944 du Gouvernement provisoire de la RÃ©publique franÃ§aise a accordÃ© le droit de vote aux femmes, exercÃ© pour la premiÃ¨re fois aux municipales d'avril 1945."},
    ]),

    (754, "Les droits sociaux et economiques", "EMC", "3eme", [
        {"id": "754_1", "type": "qcm",
         "question": "Quels droits sont qualifies de 'droits-creances' car ils impliquent une obligation d'action de l'Etat ?",
         "options": [
             "Droits civils et politiques",
             "Droits economiques, sociaux et culturels",
             "Droits naturels et imprescriptibles",
             "Droits des peuples a l'autodetermination"
         ],
         "correct_option": "Droits economiques, sociaux et culturels",
         "explanation": "Les droits-crÃ©ances (santÃ©, Ã©ducation, logement, travail) exigent une prestation positive de l'Ã‰tat, contrairement aux libertÃ©s-dÃ©fenses qui nÃ©cessitent seulement une abstention."},
        {"id": "754_2", "type": "vrai-faux",
         "question": "Le droit a un logement opposable (DALO) permet aux personnes sans logis de contraindre l'Etat a leur en fournir un.",
         "correct": True,
         "explanation": "La loi DALO (2007) reconnaÃ®t le droit au logement comme opposable ; toute personne remplissant les critÃ¨res peut saisir la commission de mÃ©diation si elle n'a pas de logement."},
        {"id": "754_3", "type": "texte",
         "question": "Comment appelle-t-on le revenu minimum garanti par l'Etat aux personnes sans ressources suffisantes en France ?",
         "correct_answer": "RSA",
         "explanation": "Le RSA (Revenu de SolidaritÃ© Active), crÃ©Ã© en 2009, assure un revenu minimum aux personnes sans ressources ou dont les revenus sont insuffisants."},
        {"id": "754_4", "type": "qcm",
         "question": "Quel principe garantit a tous l'acces aux soins de sante en France ?",
         "options": [
             "Le principe de bienfaisance",
             "La Couverture Maladie Universelle (CMU) / Protection Universelle Maladie (PUMa)",
             "Le code de deontologie medicale",
             "Le principe de precaution"
         ],
         "correct_option": "La Couverture Maladie Universelle (CMU) / Protection Universelle Maladie (PUMa)",
         "explanation": "La PUMa (2016, remplaÃ§ant la CMU) garantit Ã  toute personne travaillant ou rÃ©sidant stablement en France l'accÃ¨s Ã  la prise en charge de ses frais de santÃ©."},
        {"id": "754_5", "type": "vrai-faux",
         "question": "Le droit de greve est un droit constitutionnel reconnu aux travailleurs en France.",
         "correct": True,
         "explanation": "Le droit de grÃ¨ve est inscrit dans le PrÃ©ambule de la Constitution de 1946 (Ã  valeur constitutionnelle) ; il s'exerce dans le cadre des lois qui le rÃ©glementent."},
        {"id": "754_6", "type": "texte",
         "question": "Quel texte constitutionnel de 1946 enumere les droits sociaux (droit au travail, a la sante, a l'education) qui ont valeur constitutionnelle ?",
         "correct_answer": "Preambule de la Constitution de 1946",
         "explanation": "Le PrÃ©ambule de la Constitution de 1946 (intÃ©grÃ© au bloc de constitutionnalitÃ©) proclame les droits Ã©conomiques et sociaux fondamentaux."},
        {"id": "754_7", "type": "qcm",
         "question": "Quel organisme collecte les cotisations sociales et verse les prestations maladie, retraite, chomage en France ?",
         "options": [
             "Le Tresor public",
             "La Securite sociale",
             "La Banque de France",
             "L'INSEE"
         ],
         "correct_option": "La Securite sociale",
         "explanation": "La SÃ©curitÃ© sociale (crÃ©Ã©e en 1945 par les ordonnances d'Ambroise Croizat et Alexandre Parodi) couvre les risques maladie, vieillesse, famille et accidents du travail."},
        {"id": "754_8", "type": "vrai-faux",
         "question": "Le principe de solidarite nationale signifie que les plus aises contribuent davantage au financement des services collectifs.",
         "correct": True,
         "explanation": "L'impÃ´t progressif et les cotisations sociales expriment la solidaritÃ© nationale : les personnes aux revenus plus Ã©levÃ©s contribuent proportionnellement plus."},
    ]),

    # =========================================================
    # BLOC 3 - INSTITUTIONS ET DEMOCRATIE (755-758)
    # =========================================================
    (755, "Les institutions de la Ve Republique", "EMC", "3eme", [
        {"id": "755_1", "type": "qcm",
         "question": "En quelle annee la Ve Republique a-t-elle ete fondee ?",
         "options": ["1944", "1946", "1958", "1962"],
         "correct_option": "1958",
         "explanation": "La Ve RÃ©publique a Ã©tÃ© instaurÃ©e par la Constitution du 4 octobre 1958, rÃ©digÃ©e sous l'impulsion du gÃ©nÃ©ral de Gaulle pour rÃ©soudre la crise algÃ©rienne."},
        {"id": "755_2", "type": "vrai-faux",
         "question": "En France, le Premier ministre est nomme par le Parlement.",
         "correct": False,
         "explanation": "En France, le Premier ministre est nommÃ© par le PrÃ©sident de la RÃ©publique (article 8 de la Constitution), qui choisit une personnalitÃ© susceptible de disposer d'une majoritÃ© Ã  l'AssemblÃ©e nationale."},
        {"id": "755_3", "type": "texte",
         "question": "Quel article de la Constitution de 1958 donne au President de la Republique le pouvoir de dissolution de l'Assemblee nationale ?",
         "correct_answer": "article 12",
         "explanation": "L'article 12 permet au PrÃ©sident de dissoudre l'AssemblÃ©e nationale aprÃ¨s consultation du Premier ministre et des prÃ©sidents des assemblÃ©es."},
        {"id": "755_4", "type": "qcm",
         "question": "Quel est le role principal de l'Assemblee nationale dans le systeme institutionnel francais ?",
         "options": [
             "Nommer le President de la Republique",
             "Voter les lois et controler le gouvernement",
             "Juger les criminels",
             "Diriger la politique etrangere"
         ],
         "correct_option": "Voter les lois et controler le gouvernement",
         "explanation": "L'AssemblÃ©e nationale (577 dÃ©putÃ©s Ã©lus pour 5 ans) vote les lois, le budget de l'Ã‰tat et peut renverser le gouvernement par une motion de censure."},
        {"id": "755_5", "type": "vrai-faux",
         "question": "Le Senat peut renverser le gouvernement par une motion de censure.",
         "correct": False,
         "explanation": "Seule l'AssemblÃ©e nationale peut renverser le gouvernement par une motion de censure (article 49 de la Constitution) ; le SÃ©nat ne dispose pas de ce pouvoir."},
        {"id": "755_6", "type": "texte",
         "question": "Comment appelle-t-on la situation ou le President de la Republique et le Premier ministre appartiennent a des partis politiques opposes ?",
         "correct_answer": "cohabitation",
         "explanation": "La cohabitation (1986-88, 1993-95, 1997-2002) est une situation de division du pouvoir exÃ©cutif entre un prÃ©sident et un premier ministre de camps politiques opposÃ©s."},
        {"id": "755_7", "type": "qcm",
         "question": "Quel est le role du Conseil constitutionnel en France ?",
         "options": [
             "Gouverner le pays en cas de crise",
             "Verifier la conformite des lois a la Constitution",
             "Nommer les ministres",
             "Juger les crimes les plus graves"
         ],
         "correct_option": "Verifier la conformite des lois a la Constitution",
         "explanation": "Le Conseil constitutionnel (9 membres nommÃ©s pour 9 ans non renouvelables) contrÃ´le la constitutionnalitÃ© des lois avant et aprÃ¨s promulgation (via la QPC)."},
        {"id": "755_8", "type": "vrai-faux",
         "question": "Le Conseil d'Etat est la plus haute juridiction administrative francaise.",
         "correct": True,
         "explanation": "Le Conseil d'Ã‰tat est la plus haute juridiction administrative et conseille le gouvernement sur les projets de loi et d'ordonnances."},
    ]),

    (756, "La justice en France : organisation et principes", "EMC", "3eme", [
        {"id": "756_1", "type": "qcm",
         "question": "Quel est le principe selon lequel nul ne peut etre juge par le meme tribunal pour une faute commise par lui-meme ?",
         "options": [
             "Impartialite du juge",
             "Independance de la justice",
             "Principe du contradictoire",
             "Droit d'appel"
         ],
         "correct_option": "Impartialite du juge",
         "explanation": "L'impartialitÃ© du juge est un principe fondamental garantissant qu'aucun juge ne peut Ãªtre partial ou avoir un intÃ©rÃªt dans l'affaire qu'il juge."},
        {"id": "756_2", "type": "vrai-faux",
         "question": "En France, la justice penale juge les infractions (crimes, delits, contraventions).",
         "correct": True,
         "explanation": "La justice pÃ©nale (criminelle) rÃ©prime les infractions Ã  la loi pÃ©nale ; la justice civile rÃ¨gle les litiges entre particuliers (contrats, famille, etc.)."},
        {"id": "756_3", "type": "texte",
         "question": "Comment appelle-t-on la voie de recours permettant de porter une affaire devant une juridiction superieure ?",
         "correct_answer": "appel",
         "explanation": "L'appel permet de contester une dÃ©cision de premiÃ¨re instance devant une cour d'appel, qui rÃ©examine l'affaire en droit et en fait."},
        {"id": "756_4", "type": "qcm",
         "question": "Quelle juridiction juge les crimes punis de plus de 10 ans de reclusion ?",
         "options": [
             "Tribunal correctionnel",
             "Tribunal de police",
             "Cour d'assises",
             "Conseil de prud'hommes"
         ],
         "correct_option": "Cour d'assises",
         "explanation": "La Cour d'assises juge les crimes (infractions les plus graves) avec un jury populaire de 6 jurÃ©s citoyens tirÃ©s au sort."},
        {"id": "756_5", "type": "vrai-faux",
         "question": "La Cour de cassation rejuge les faits de l'affaire et peut innocenter directement un condamne.",
         "correct": False,
         "explanation": "La Cour de cassation n'est pas un 3Ã¨me degrÃ© de juridiction ; elle contrÃ´le uniquement la correcte application du droit (pas les faits) et peut casser un arrÃªt pour le renvoyer devant une autre cour."},
        {"id": "756_6", "type": "texte",
         "question": "Quel magistrat est charge de representer la societe et de declencher les poursuites penales au nom de l'Etat ?",
         "correct_answer": "procureur de la Republique",
         "explanation": "Le procureur (ministÃ¨re public) reprÃ©sente la sociÃ©tÃ© et dÃ©clenche l'action publique ; il dirige la police judiciaire et requiert les peines devant le tribunal."},
        {"id": "756_7", "type": "qcm",
         "question": "Quel est le role du juge d'instruction ?",
         "options": [
             "Prononcer la peine",
             "Instruire (enqueter sur) les affaires penales complexes de facon independante",
             "Defendre l'accuse",
             "Surveiller l'execution des peines"
         ],
         "correct_option": "Instruire (enqueter sur) les affaires penales complexes de facon independante",
         "explanation": "Le juge d'instruction mÃ¨ne une enquÃªte judiciaire indÃ©pendante sur les affaires complexes ; il peut mettre en examen, inculper ou placer en dÃ©tention provisoire."},
        {"id": "756_8", "type": "vrai-faux",
         "question": "L'aide juridictionnelle permet aux personnes sans ressources d'etre assistees gratuitement par un avocat.",
         "correct": True,
         "explanation": "L'aide juridictionnelle (totale ou partielle) permet aux personnes dont les ressources sont insuffisantes d'accÃ©der Ã  la justice avec un avocat commis d'office."},
    ]),

    (757, "Les collectivites territoriales et la decentralisation", "EMC", "3eme", [
        {"id": "757_1", "type": "qcm",
         "question": "Quelles sont les trois collectivites territoriales principales en France ?",
         "options": [
             "Communes, arrondissements, cantons",
             "Communes, departements, regions",
             "Regions, academies, prefectures",
             "Communes, cantons, circonscriptions"
         ],
         "correct_option": "Communes, departements, regions",
         "explanation": "La France est organisÃ©e en 3 niveaux de collectivitÃ©s : 34 965 communes, 101 dÃ©partements et 18 rÃ©gions (dont 5 d'outre-mer)."},
        {"id": "757_2", "type": "vrai-faux",
         "question": "La decentralisation signifie que l'Etat transfere des competences a des collectivites locales elues.",
         "correct": True,
         "explanation": "La dÃ©centralisation (actes I en 1982, II en 2003-04, III en 2014) transfÃ¨re des compÃ©tences et des ressources de l'Ã‰tat aux collectivitÃ©s territoriales Ã©lues."},
        {"id": "757_3", "type": "texte",
         "question": "Comment appelle-t-on le representant de l'Etat dans chaque departement et region ?",
         "correct_answer": "prefet",
         "explanation": "Le prÃ©fet est le reprÃ©sentant de l'Ã‰tat dans le dÃ©partement/la rÃ©gion ; il veille Ã  l'application des lois et coordonne l'action de l'Ã‰tat au niveau local."},
        {"id": "757_4", "type": "qcm",
         "question": "Quelles sont les principales competences du departement en France ?",
         "options": [
             "Education nationale et lycees",
             "Action sociale (RSA, aide aux personnes agees et handicapees) et colleges",
             "Transports ferroviaires nationaux",
             "Politique etrangere"
         ],
         "correct_option": "Action sociale (RSA, aide aux personnes agees et handicapees) et colleges",
         "explanation": "Le dÃ©partement gÃ¨re l'action sociale (RSA, PMI, aide aux personnes Ã¢gÃ©es/handicapÃ©es), les collÃ¨ges et les routes dÃ©partementales."},
        {"id": "757_5", "type": "vrai-faux",
         "question": "Le maire est a la fois chef de la commune et representant de l'Etat dans celle-ci.",
         "correct": True,
         "explanation": "Le maire cumule deux fonctions : exÃ©cutif de la commune (Ã©lu) et agent de l'Ã‰tat (Ã©tat civil, police administrative, organisation des Ã©lections)."},
        {"id": "757_6", "type": "texte",
         "question": "Comment appelle-t-on le budget vote chaque annee par le conseil municipal pour financer les depenses de la commune ?",
         "correct_answer": "budget primitif communal",
         "explanation": "Le budget primitif communal est votÃ© chaque annÃ©e par le conseil municipal avant le 31 dÃ©cembre pour l'exercice suivant."},
        {"id": "757_7", "type": "qcm",
         "question": "Quelle est la principale ressource fiscale des communes ?",
         "options": [
             "Taxe sur la valeur ajoutee (TVA)",
             "Impot sur le revenu",
             "Taxe fonciere et taxe d'habitation",
             "Cotisations sociales"
         ],
         "correct_option": "Taxe fonciere et taxe d'habitation",
         "explanation": "Les impÃ´ts locaux (taxe fonciÃ¨re sur propriÃ©tÃ©s bÃ¢ties et non bÃ¢ties, anciennement taxe d'habitation) constituent les principales ressources propres des communes."},
        {"id": "757_8", "type": "vrai-faux",
         "question": "Les Dom-Tom (departements et regions d'outre-mer) ont le meme statut juridique que les departements metropolitains.",
         "correct": False,
         "explanation": "Les DROM (Martinique, Guadeloupe, RÃ©union, Guyane, Mayotte) ont un statut juridique adaptÃ©, et les COM (PolynÃ©sie, CalÃ©donie, Saint-Martinâ€¦) ont des statuts encore plus spÃ©cifiques."},
    ]),

    (758, "L'Union europeenne et ses institutions", "EMC", "3eme", [
        {"id": "758_1", "type": "qcm",
         "question": "Quel traite fondateur a cree la Communaute economique europeenne (CEE) en 1957 ?",
         "options": [
             "Traite de Paris",
             "Traite de Rome",
             "Traite de Maastricht",
             "Traite de Lisbonne"
         ],
         "correct_option": "Traite de Rome",
         "explanation": "Le TraitÃ© de Rome (25 mars 1957) a crÃ©Ã© la CEE (devenue UE) et l'Euratom, signÃ©s par 6 pays fondateurs (France, Allemagne, Italie, Belgique, Pays-Bas, Luxembourg)."},
        {"id": "758_2", "type": "vrai-faux",
         "question": "Le Parlement europeen est la seule institution de l'UE directement elue par les citoyens europeens.",
         "correct": True,
         "explanation": "Le Parlement europÃ©en (720 eurodÃ©putÃ©s depuis 2024) est la seule institution de l'UE Ã©lue au suffrage universel direct, tous les 5 ans."},
        {"id": "758_3", "type": "texte",
         "question": "Quel est le nom de la monnaie unique utilisee dans la zone euro ?",
         "correct_answer": "euro",
         "explanation": "L'euro a Ã©tÃ© introduit le 1er janvier 1999 pour les transactions financiÃ¨res et le 1er janvier 2002 pour les billets et piÃ¨ces dans 12 pays (aujourd'hui 20 pays)."},
        {"id": "758_4", "type": "qcm",
         "question": "Quel est le role de la Commission europeenne ?",
         "options": [
             "Voter les lois europeennes",
             "Proposer les lois, veiller a leur application et representer l'UE",
             "Juger les conflits entre Etats membres",
             "Fixer les taux d'interet en zone euro"
         ],
         "correct_option": "Proposer les lois, veiller a leur application et representer l'UE",
         "explanation": "La Commission europÃ©enne (27 commissaires) est le 'moteur' de l'UE : elle propose les textes lÃ©gislatifs, veille Ã  l'application du droit communautaire et reprÃ©sente l'UE Ã  l'international."},
        {"id": "758_5", "type": "vrai-faux",
         "question": "La Cour de justice de l'Union europeenne (CJUE) siege au Luxembourg.",
         "correct": True,
         "explanation": "La CJUE siÃ¨ge Ã  Luxembourg et veille Ã  l'application uniforme du droit de l'UE ; ses arrÃªts sont contraignants pour tous les Ã‰tats membres."},
        {"id": "758_6", "type": "texte",
         "question": "Comment appelle-t-on le principe selon lequel l'UE n'agit que dans les domaines ou son action est plus efficace que celle des Etats membres ?",
         "correct_answer": "subsidiarite",
         "explanation": "Le principe de subsidiaritÃ© (TraitÃ© de Maastricht) signifie que l'UE n'intervient que si l'objectif ne peut pas Ãªtre atteint de maniÃ¨re suffisante par les Ã‰tats membres seuls."},
        {"id": "758_7", "type": "qcm",
         "question": "Quelle institution de l'UE reunit les chefs d'Etat et de gouvernement pour definir les grandes orientations politiques ?",
         "options": [
             "Le Parlement europeen",
             "La Commission europeenne",
             "Le Conseil europeen",
             "Le Conseil de l'Europe"
         ],
         "correct_option": "Le Conseil europeen",
         "explanation": "Le Conseil europÃ©en rÃ©unit les chefs d'Ã‰tat et de gouvernement des 27 Ã‰tats membres pour dÃ©finir les grandes orientations politiques de l'UE."},
        {"id": "758_8", "type": "vrai-faux",
         "question": "Le Conseil de l'Europe est une institution de l'Union europeenne.",
         "correct": False,
         "explanation": "Le Conseil de l'Europe est une organisation internationale distincte de l'UE (fondÃ©e en 1949, 46 membres) qui gÃ¨re la CEDH ; ne pas confondre avec le Conseil europÃ©en (institution de l'UE)."},
    ]),

    # =========================================================
    # BLOC 4 - LAICITE ET ENGAGEMENT CITOYEN (759-762)
    # =========================================================
    (759, "La laicite : principes et enjeux", "EMC", "3eme", [
        {"id": "759_1", "type": "qcm",
         "question": "Quelle loi a consacre la laicite en France en separant les Eglises de l'Etat ?",
         "options": [
             "Loi de 1881 sur la liberte de la presse",
             "Loi du 9 decembre 1905 sur la separation des Eglises et de l'Etat",
             "Loi de 1901 sur les associations",
             "Loi de 1958 sur la Constitution"
         ],
         "correct_option": "Loi du 9 decembre 1905 sur la separation des Eglises et de l'Etat",
         "explanation": "La loi de 1905 (portÃ©e par Aristide Briand et Ferdinand Buisson) consacre la sÃ©paration des Ã‰glises et de l'Ã‰tat ; la France ne reconnaÃ®t ni ne subventionne aucun culte."},
        {"id": "759_2", "type": "vrai-faux",
         "question": "La laicite interdit aux citoyens de pratiquer leur religion dans l'espace prive.",
         "correct": False,
         "explanation": "La laÃ¯citÃ© garantit la libertÃ© religieuse dans la sphÃ¨re privÃ©e ; elle impose uniquement la neutralitÃ© religieuse dans les services publics de l'Ã‰tat."},
        {"id": "759_3", "type": "texte",
         "question": "Quels sont les trois piliers de la laicite a l'ecole publique rappeles dans la Charte de la laicite de 2013 ?",
         "correct_answer": "liberte de conscience, neutralite de l'ecole et egale dignite de tous",
         "explanation": "La Charte de la laÃ¯citÃ© Ã  l'Ã©cole (2013) rappelle : libertÃ© de conscience, neutralitÃ© du service public d'Ã©ducation et Ã©gale dignitÃ© de tous les Ã©lÃ¨ves."},
        {"id": "759_4", "type": "qcm",
         "question": "Que dit la loi du 15 mars 2004 concernant les signes religieux a l'ecole publique ?",
         "options": [
             "Elle interdit toute religion dans les ecoles",
             "Elle interdit le port de signes religieux ostensibles dans les ecoles publiques",
             "Elle autorise tous les signes religieux dans les ecoles publiques",
             "Elle oblige les eleves a declarer leur religion"
         ],
         "correct_option": "Elle interdit le port de signes religieux ostensibles dans les ecoles publiques",
         "explanation": "La loi de 2004 (dite loi 'voile') interdit dans les Ã©coles, collÃ¨ges et lycÃ©es publics le port de signes ou tenues manifestant ostensiblement une appartenance religieuse."},
        {"id": "759_5", "type": "vrai-faux",
         "question": "Les enseignants et fonctionnaires sont soumis a une obligation de neutralite religieuse dans l'exercice de leurs fonctions.",
         "correct": True,
         "explanation": "Le principe de neutralitÃ© des agents du service public leur interdit d'exprimer leurs opinions religieuses ou politiques dans l'exercice de leurs fonctions."},
        {"id": "759_6", "type": "texte",
         "question": "Comment appelle-t-on le refus de discuter, d'examiner ou de remettre en question des croyances religieuses au nom de la religion ?",
         "correct_answer": "obscurantisme",
         "explanation": "L'obscurantisme dÃ©signe le refus d'accepter la raison et la discussion ; il s'oppose Ã  l'esprit critique promu par la laÃ¯citÃ© et l'enseignement rÃ©publicain."},
        {"id": "759_7", "type": "qcm",
         "question": "Quel article de la Constitution de 1958 qualifie la France de 'Republique indivisible, laique, democratique et sociale' ?",
         "options": [
             "Article 1",
             "Article 3",
             "Article 11",
             "Article 49"
         ],
         "correct_option": "Article 1",
         "explanation": "L'article 1 de la Constitution dispose : 'La France est une RÃ©publique indivisible, laÃ¯que, dÃ©mocratique et sociale.'"},
        {"id": "759_8", "type": "vrai-faux",
         "question": "La laicite est une specificite francaise qui n'existe sous cette forme dans aucun autre pays.",
         "correct": False,
         "explanation": "D'autres pays ont des systÃ¨mes de sÃ©paration Ã‰tat/religion (Turquie, Mexique, Ã‰tats-Unis avec la First Amendmentâ€¦) mais la laÃ¯citÃ© Ã  la franÃ§aise est une version particuliÃ¨re avec ses propres caractÃ©ristiques."},
    ]),

    (760, "L'engagement citoyen et associatif", "EMC", "3eme", [
        {"id": "760_1", "type": "qcm",
         "question": "Quelle loi fondamentale garantit en France la liberte d'association ?",
         "options": [
             "Loi de 1881",
             "Loi du 1er juillet 1901",
             "Loi de 1905",
             "Loi de 1972"
         ],
         "correct_option": "Loi du 1er juillet 1901",
         "explanation": "La loi de 1901 garantit la libertÃ© d'association en permettant Ã  toute personne de crÃ©er une association sans autorisation prÃ©alable, simplement par dÃ©claration en prÃ©fecture."},
        {"id": "760_2", "type": "vrai-faux",
         "question": "Le benevole contribue a la societe civile sans recevoir de remuneration.",
         "correct": True,
         "explanation": "Le bÃ©nÃ©volat est un engagement gratuit au service d'autrui ou d'une cause d'intÃ©rÃªt gÃ©nÃ©ral ; la France compte environ 22 millions de bÃ©nÃ©voles."},
        {"id": "760_3", "type": "texte",
         "question": "Comment appelle-t-on l'ensemble des organisations, associations et citoyens qui agissent independamment de l'Etat et du marche ?",
         "correct_answer": "societe civile",
         "explanation": "La sociÃ©tÃ© civile regroupe les organisations non gouvernementales (ONG), associations, syndicats, mÃ©dias et mouvements citoyens qui participent Ã  la vie publique."},
        {"id": "760_4", "type": "qcm",
         "question": "Quel est le role d'un syndicat de salaries ?",
         "options": [
             "Gerer les ressources humaines d'une entreprise",
             "Defendre les interets professionnels et sociaux des travailleurs",
             "Percevoir les cotisations sociales",
             "Former les nouveaux employes"
         ],
         "correct_option": "Defendre les interets professionnels et sociaux des travailleurs",
         "explanation": "Les syndicats (CGT, CFDT, CGT-FO, etc.) reprÃ©sentent et dÃ©fendent les intÃ©rÃªts des salariÃ©s dans les nÃ©gociations collectives et les conflits du travail."},
        {"id": "760_5", "type": "vrai-faux",
         "question": "Un jeune de 16 ans peut rejoindre un parti politique en France.",
         "correct": True,
         "explanation": "Les jeunes dÃ¨s 16 ans peuvent adhÃ©rer Ã  un parti politique en France (les jeunes dÃ¨s 15 ans dans certains partis) ; le droit de vote reste Ã  18 ans."},
        {"id": "760_6", "type": "texte",
         "question": "Comment appelle-t-on les organisations non gouvernementales qui agissent dans le domaine humanitaire sans but lucratif ?",
         "correct_answer": "ONG",
         "explanation": "Les ONG (Organisations Non Gouvernementales) comme MÃ©decins Sans FrontiÃ¨res, Amnesty International ou le CICR agissent dans les domaines humanitaire, environnemental et des droits humains."},
        {"id": "760_7", "type": "qcm",
         "question": "Qu'est-ce que le Service Civique en France ?",
         "options": [
             "Le service militaire obligatoire",
             "Un engagement volontaire de 6 a 12 mois au service de l'interet general, indemnise",
             "Un stage en entreprise obligatoire",
             "Une punition pour les mineurs delinquants"
         ],
         "correct_option": "Un engagement volontaire de 6 a 12 mois au service de l'interet general, indemnise",
         "explanation": "Le Service Civique (loi de 2010) permet aux 16-25 ans (30 ans pour les personnes en situation de handicap) de s'engager dans des missions d'intÃ©rÃªt gÃ©nÃ©ral avec une indemnisation."},
        {"id": "760_8", "type": "vrai-faux",
         "question": "La democratie participative permet aux citoyens de contribuer directement aux decisions politiques en dehors des elections.",
         "correct": True,
         "explanation": "La dÃ©mocratie participative (budgets participatifs, consultations citoyennes, rÃ©fÃ©rendum d'initiative locale) complÃ¨te la dÃ©mocratie reprÃ©sentative."},
    ]),

    (761, "Medias, information et democratie", "EMC", "3eme", [
        {"id": "761_1", "type": "qcm",
         "question": "Quel principe garantit en France la liberte de la presse ?",
         "options": [
             "La loi du 29 juillet 1881 sur la liberte de la presse",
             "La loi de 1905",
             "La Constitution de 1958",
             "La loi de 1972 contre le racisme"
         ],
         "correct_option": "La loi du 29 juillet 1881 sur la liberte de la presse",
         "explanation": "La loi de 1881 garantit la libertÃ© de la presse tout en posant des limites (diffamation, injure, appel Ã  la haine) ; elle reste le texte fondateur du droit de la presse en France."},
        {"id": "761_2", "type": "vrai-faux",
         "question": "La liberte d'expression est un droit absolu sans aucune limite legale.",
         "correct": False,
         "explanation": "La libertÃ© d'expression est garantie mais limitÃ©e par la loi : diffamation, injure, appel Ã  la haine, nÃ©gationnisme (loi Gayssot 1990) et provocation au terrorisme sont rÃ©primÃ©s."},
        {"id": "761_3", "type": "texte",
         "question": "Comment appelle-t-on la propagation deliberee de fausses informations pour tromper le public ?",
         "correct_answer": "desinformation",
         "explanation": "La dÃ©sinformation (ou infox / fake news) dÃ©signe la diffusion intentionnelle de fausses informations pour manipuler l'opinion publique."},
        {"id": "761_4", "type": "qcm",
         "question": "Quel organisme public regulateur surveille les medias audiovisuels (TV, radio) en France ?",
         "options": [
             "Le CSA / ARCOM",
             "Le Conseil constitutionnel",
             "Le ministere de la Culture",
             "L'Assemblee nationale"
         ],
         "correct_option": "Le CSA / ARCOM",
         "explanation": "L'ARCOM (AutoritÃ© de RÃ©gulation de la Communication Audiovisuelle et NumÃ©rique, ex-CSA, crÃ©Ã©e en 2022) rÃ©gule les mÃ©dias audiovisuels et les plateformes numÃ©riques en France."},
        {"id": "761_5", "type": "vrai-faux",
         "question": "Verifier les sources d'une information avant de la partager est une pratique essentielle de l'education aux medias.",
         "correct": True,
         "explanation": "L'Ã©ducation aux mÃ©dias et Ã  l'information (EMI) apprend Ã  vÃ©rifier les sources, croiser les informations et distinguer faits et opinions pour lutter contre la dÃ©sinformation."},
        {"id": "761_6", "type": "texte",
         "question": "Comment appelle-t-on le phenomene par lequel les algorithmes des reseaux sociaux n'exposent l'utilisateur qu'a des informations confirmant ses opinions ?",
         "correct_answer": "chambre d'echo",
         "explanation": "La chambre d'Ã©cho (ou bulle de filtre) dÃ©signe l'enfermement de l'utilisateur dans un univers informationnel homogÃ¨ne, renforÃ§ant ses biais cognitifs."},
        {"id": "761_7", "type": "qcm",
         "question": "Quel role essentiel jouent les journalistes dans une democratie ?",
         "options": [
             "Soutenir le gouvernement en place",
             "Informer le public, controler les pouvoirs et donner la parole aux citoyens",
             "Divertir uniquement",
             "Appliquer les lois"
         ],
         "correct_option": "Informer le public, controler les pouvoirs et donner la parole aux citoyens",
         "explanation": "Les journalistes sont souvent qualifiÃ©s de 'quatriÃ¨me pouvoir' : ils informent, enquÃªtent, contrÃ´lent les institutions et permettent le dÃ©bat dÃ©mocratique."},
        {"id": "761_8", "type": "vrai-faux",
         "question": "Les donnees personnelles des utilisateurs de reseaux sociaux sont protegees par le RGPD en Europe.",
         "correct": True,
         "explanation": "Le RGPD (RÃ¨glement GÃ©nÃ©ral sur la Protection des DonnÃ©es, 2018) protÃ¨ge les donnÃ©es personnelles des citoyens europÃ©ens et impose des obligations aux entreprises numÃ©riques."},
    ]),

    (762, "Egalite, discrimination et lutte contre les prejuges", "EMC", "3eme", [
        {"id": "762_1", "type": "qcm",
         "question": "Quelle est la definition juridique de la discrimination ?",
         "options": [
             "Toute difference de traitement entre des personnes",
             "Un traitement defavorable fonde sur un critere interdit par la loi",
             "Une opinion negative sur un groupe",
             "Une difference de salaire entre hommes et femmes"
         ],
         "correct_option": "Un traitement defavorable fonde sur un critere interdit par la loi",
         "explanation": "La discrimination est un traitement dÃ©favorable fondÃ© sur un critÃ¨re protÃ©gÃ© par la loi (origine, sexe, religion, handicap, orientation sexuelleâ€¦) dans des domaines comme l'emploi, le logement ou l'accÃ¨s aux services."},
        {"id": "762_2", "type": "vrai-faux",
         "question": "En France, la discrimination a l'embauche est un delit puni par la loi.",
         "correct": True,
         "explanation": "La discrimination Ã  l'embauche est punie de 3 ans d'emprisonnement et 45 000 â‚¬ d'amende selon l'article 225-1 du Code pÃ©nal."},
        {"id": "762_3", "type": "texte",
         "question": "Comment appelle-t-on un jugement stereotypique negatif porte sur un individu en raison de son appartenance a un groupe ?",
         "correct_answer": "prejuge",
         "explanation": "Un prÃ©jugÃ© est une opinion prÃ©constituÃ©e, souvent nÃ©gative, sur un groupe de personnes ; il alimente les discriminations et les stÃ©rÃ©otypes."},
        {"id": "762_4", "type": "qcm",
         "question": "Quelle loi a reconnu en France l'egalite de remuneration entre hommes et femmes pour un travail de valeur egale ?",
         "options": [
             "Loi Aubry de 2000",
             "Loi Roudy de 1983",
             "Loi de 1944",
             "Loi de 2017"
         ],
         "correct_option": "Loi Roudy de 1983",
         "explanation": "La loi Roudy (1983) a posÃ© le principe d'Ã©galitÃ© professionnelle entre hommes et femmes, y compris l'Ã©galitÃ© de rÃ©munÃ©ration pour un travail de valeur Ã©gale."},
        {"id": "762_5", "type": "vrai-faux",
         "question": "Le harcelement scolaire (bullying) est reconnu et reprime par la loi en France.",
         "correct": True,
         "explanation": "Depuis la loi du 2 mars 2022, le harcÃ¨lement scolaire est une infraction pÃ©nale spÃ©cifique punie jusqu'Ã  10 ans d'emprisonnement si les faits ont conduit au suicide ou Ã  une tentative."},
        {"id": "762_6", "type": "texte",
         "question": "Comment appelle-t-on la discrimination positive visant a corriger des inegalites historiques en favorisant des groupes sous-representes ?",
         "correct_answer": "discrimination positive",
         "explanation": "La discrimination positive (ou action positive/affirmative action) consiste Ã  favoriser un groupe sous-reprÃ©sentÃ© pour rÃ©Ã©quilibrer des inÃ©galitÃ©s structurelles."},
        {"id": "762_7", "type": "qcm",
         "question": "Quel principe republicain pose que tous les citoyens sont traites de la meme maniere par la loi, independamment de leur origine ou religion ?",
         "options": [
             "Principe de fraternite",
             "Principe d'egalite",
             "Principe de laicite",
             "Principe de solidarite"
         ],
         "correct_option": "Principe d'egalite",
         "explanation": "L'Ã©galitÃ© devant la loi est un principe fondamental de la RÃ©publique franÃ§aise (article 1 de la Constitution) : la loi s'applique Ã  tous de la mÃªme maniÃ¨re."},
        {"id": "762_8", "type": "vrai-faux",
         "question": "Le racisme et l'antisemitisme sont des infractions penales en France.",
         "correct": True,
         "explanation": "La loi Pleven (1972) et la loi Gayssot (1990) rÃ©priment les propos ou actes racistes, antisÃ©mites ou discriminatoires ; ils peuvent Ãªtre punis d'amendes et d'emprisonnement."},
    ]),

    # =========================================================
    # BLOC 5 - DEFENSE NATIONALE ET SECURITE (763-765)
    # =========================================================
    (763, "La defense nationale et les forces armees", "EMC", "3eme", [
        {"id": "763_1", "type": "qcm",
         "question": "Qui est le chef supreme des armees francaises selon la Constitution de 1958 ?",
         "options": [
             "Le Premier ministre",
             "Le Ministre de la Defense",
             "Le President de la Republique",
             "Le Chef d'Etat-major des armees"
         ],
         "correct_option": "Le President de la Republique",
         "explanation": "L'article 15 de la Constitution dispose que 'le PrÃ©sident de la RÃ©publique est le chef des armÃ©es' ; il dispose du pouvoir nuclear et prÃ©side les conseils de dÃ©fense."},
        {"id": "763_2", "type": "vrai-faux",
         "question": "La France est dotee de l'arme nucleaire et en est une des puissances reconnues par le Traite de non-proliferation (TNP).",
         "correct": True,
         "explanation": "La France est l'une des 5 puissances nuclÃ©aires reconnues par le TNP (avec USA, Russie, Chine, Royaume-Uni) ; elle dispose d'une force de frappe nuclÃ©aire autonome."},
        {"id": "763_3", "type": "texte",
         "question": "Comment appelle-t-on le service obligatoire qui permettait aux jeunes Francais d'effectuer leur service militaire avant sa suspension en 1997 ?",
         "correct_answer": "service militaire obligatoire",
         "explanation": "Le service national obligatoire a Ã©tÃ© suspendu en 1997 sous Jacques Chirac ; il a Ã©tÃ© remplacÃ© par la JournÃ©e DÃ©fense et CitoyennetÃ© (JDC)."},
        {"id": "763_4", "type": "qcm",
         "question": "Qu'est-ce que la Journee Defense et Citoyennete (JDC) ?",
         "options": [
             "Un exercice militaire mensuel",
             "Une journee d'information et de sensibilisation a la defense nationale obligatoire pour les jeunes de 17 ans",
             "Un vote obligatoire pour les 18 ans",
             "Une visite d'une base militaire facultative"
         ],
         "correct_option": "Une journee d'information et de sensibilisation a la defense nationale obligatoire pour les jeunes de 17 ans",
         "explanation": "La JDC (anciennement JAPD) est une journÃ©e citoyenne obligatoire pour tous les FranÃ§ais de 17 ans, prÃ©alable nÃ©cessaire pour passer les examens comme le baccalaurÃ©at."},
        {"id": "763_5", "type": "vrai-faux",
         "question": "La France est membre fondateur de l'OTAN (Organisation du traite de l'Atlantique Nord).",
         "correct": True,
         "explanation": "La France est membre fondateur de l'OTAN depuis le traitÃ© de Washington du 4 avril 1949 ; elle s'est retirÃ©e du commandement intÃ©grÃ© en 1966 avant d'y revenir en 2009."},
        {"id": "763_6", "type": "texte",
         "question": "Comment appelle-t-on les operations militaires menees par des soldats francais dans le cadre de missions internationales de paix ?",
         "correct_answer": "operations de maintien de la paix",
         "explanation": "Les opÃ©rations de maintien de la paix (OMP), souvent sous mandat ONU ou OTAN, visent Ã  stabiliser des zones de conflit et protÃ©ger les populations civiles."},
        {"id": "763_7", "type": "qcm",
         "question": "Quel corps militaire est charge de la securite des personnes et des biens dans les zones rurales et les petites villes ?",
         "options": [
             "La Police nationale",
             "La Gendarmerie nationale",
             "La Police municipale",
             "La Douane"
         ],
         "correct_option": "La Gendarmerie nationale",
         "explanation": "La Gendarmerie nationale (corps militaire) assure la sÃ©curitÃ© dans les zones rurales et pÃ©riurbaines, tandis que la Police nationale couvre les grandes villes."},
        {"id": "763_8", "type": "vrai-faux",
         "question": "Les engages volontaires dans l'armee francaise recoivent une formation civique et professionnelle.",
         "correct": True,
         "explanation": "Les engagÃ©s volontaires dans l'armÃ©e franÃ§aise reÃ§oivent une formation militaire, professionnelle et citoyenne qui favorise la rÃ©insertion Ã  la sortie du service."},
    ]),

    (764, "Securite interieure et liberte : un equilibre", "EMC", "3eme", [
        {"id": "764_1", "type": "qcm",
         "question": "Quel article de la DDHC de 1789 fonde l'ordre public comme objectif de la societe politique ?",
         "options": [
             "Article 1",
             "Article 4 (la liberte consiste a tout faire qui ne nuit pas a autrui)",
             "Article 11",
             "Article 16"
         ],
         "correct_option": "Article 4 (la liberte consiste a tout faire qui ne nuit pas a autrui)",
         "explanation": "L'article 4 de la DDHC pose la limite de la libertÃ© : 'La libertÃ© consiste Ã  pouvoir faire tout ce qui ne nuit pas Ã  autrui.' L'ordre public est la limite lÃ©gale des libertÃ©s."},
        {"id": "764_2", "type": "vrai-faux",
         "question": "En France, l'etat d'urgence peut etre declare par le gouvernement pour faire face a des situations de crise graves.",
         "correct": True,
         "explanation": "L'Ã©tat d'urgence (loi de 1955, rÃ©visÃ©e) permet au gouvernement de restreindre temporairement certaines libertÃ©s (perquisitions, assignations Ã  rÃ©sidence) pour raisons de sÃ©curitÃ©."},
        {"id": "764_3", "type": "texte",
         "question": "Comment appelle-t-on la surveillance electronique de toutes les communications sans discrimination, denoncee par Edward Snowden en 2013 ?",
         "correct_answer": "surveillance de masse",
         "explanation": "La surveillance de masse dÃ©signe la collecte systÃ©matique de donnÃ©es sur l'ensemble d'une population, sans mandat judiciaire individuel, soulevant de graves questions de libertÃ©s civiles."},
        {"id": "764_4", "type": "qcm",
         "question": "Quel est le cadre legal des perquisitions domiciliaires en France ?",
         "options": [
             "La police peut perquisitionner sans autorisation a tout moment",
             "Une perquisition necessite en principe l'autorisation d'un magistrat et le consentement de l'occupant",
             "Les perquisitions sont interdites",
             "Les perquisitions sont realisees uniquement la nuit"
         ],
         "correct_option": "Une perquisition necessite en principe l'autorisation d'un magistrat et le consentement de l'occupant",
         "explanation": "Hors Ã©tat d'urgence, une perquisition nÃ©cessite un mandat judiciaire (juge d'instruction ou JLD) ; l'inviolabilitÃ© du domicile est protÃ©gÃ©e par la Constitution."},
        {"id": "764_5", "type": "vrai-faux",
         "question": "La video-surveillance dans les espaces publics est soumise a un encadrement legal en France.",
         "correct": True,
         "explanation": "La vidÃ©osurveillance (ou vidÃ©oprotection) dans les espaces publics est rÃ©glementÃ©e par la loi ; les donnÃ©es personnelles captÃ©es sont protÃ©gÃ©es par le RGPD et la CNIL."},
        {"id": "764_6", "type": "texte",
         "question": "Quel principe interdit a la police d'utiliser des methodes coercitives ou douloureuses pour obtenir des aveux ?",
         "correct_answer": "interdiction de la torture et des traitements inhumains",
         "explanation": "L'article 3 de la CEDH et la Convention de l'ONU contre la torture (1984) interdisent absolument la torture et les traitements inhumains ou dÃ©gradants dans les procÃ©dures policiÃ¨res."},
        {"id": "764_7", "type": "qcm",
         "question": "Quel organisme independant controle en France le respect des droits des citoyens par les forces de l'ordre ?",
         "options": [
             "Le ministere de l'Interieur",
             "La CNDS / IGPN (Inspection generale de la Police nationale)",
             "L'Assemblee nationale",
             "Le Conseil d'Etat uniquement"
         ],
         "correct_option": "La CNDS / IGPN (Inspection generale de la Police nationale)",
         "explanation": "L'IGPN ('police des polices') enquÃªte sur les manquements des fonctionnaires de police ; le DÃ©fenseur des droits peut aussi Ãªtre saisi pour des violences policiÃ¨res."},
        {"id": "764_8", "type": "vrai-faux",
         "question": "La radicalisation violente peut mener des individus a commettre des actes terroristes au nom d'ideologies extremistes.",
         "correct": True,
         "explanation": "La radicalisation est un processus progressif par lequel un individu adopte des idÃ©es extrÃ©mistes pouvant mener Ã  la violence ; la prÃ©vention de la radicalisation est un enjeu majeur de sÃ©curitÃ©."},
    ]),

    (765, "Les menaces contemporaines et la securite collective", "EMC", "3eme", [
        {"id": "765_1", "type": "qcm",
         "question": "Qu'est-ce que la cybersecurite ?",
         "options": [
             "La securite des coffres forts bancaires",
             "La protection des systemes informatiques, reseaux et donnees contre les attaques numeriques",
             "La surveillance des reseaux sociaux par l'Etat",
             "La fabrication de firewalls"
         ],
         "correct_option": "La protection des systemes informatiques, reseaux et donnees contre les attaques numeriques",
         "explanation": "La cybersÃ©curitÃ© protÃ¨ge les systÃ¨mes d'information contre les cyberattaques (piratage, ransomware, espionnage) ; en France, l'ANSSI (Agence Nationale de la SÃ©curitÃ© des SystÃ¨mes d'Information) coordonne cette protection."},
        {"id": "765_2", "type": "vrai-faux",
         "question": "Le terrorisme est defini comme l'usage delibere de la violence contre des populations civiles pour semer la terreur a des fins politiques.",
         "correct": True,
         "explanation": "Le terrorisme cible dÃ©libÃ©rÃ©ment des civils ou des institutions pour crÃ©er un climat de peur et obtenir des effets politiques ; il est universellement condamnÃ© par le droit international."},
        {"id": "765_3", "type": "texte",
         "question": "Comment appelle-t-on le phenomene de risque pour la planete lie aux activites humaines (rechauffement climatique, perte de biodiversite) qui constitue aussi une menace pour la securite ?",
         "correct_answer": "risques environnementaux globaux",
         "explanation": "Les risques environnementaux globaux (changement climatique, perte de biodiversitÃ©, rarÃ©faction des ressources) gÃ©nÃ¨rent de nouvelles menaces pour la sÃ©curitÃ© internationale."},
        {"id": "765_4", "type": "qcm",
         "question": "Qu'est-ce qu'une pandemie selon l'OMS ?",
         "options": [
             "Une epidemie localisee dans un seul pays",
             "La propagation mondiale d'une nouvelle maladie infectieuse",
             "Une maladie chronique non transmissible",
             "Une epidemie saisonniere recurrente"
         ],
         "correct_option": "La propagation mondiale d'une nouvelle maladie infectieuse",
         "explanation": "L'OMS dÃ©clare la pandÃ©mie lorsqu'une maladie infectieuse se propage Ã  l'Ã©chelle mondiale (ex. Covid-19 en 2020, grippe espagnole en 1918)."},
        {"id": "765_5", "type": "vrai-faux",
         "question": "La cooperation internationale est indispensable pour lutter contre les menaces mondiales telles que le terrorisme ou les pandemies.",
         "correct": True,
         "explanation": "Les menaces transnationales (terrorisme, pandÃ©mies, cyberattaques, changement climatique) nÃ©cessitent des rÃ©ponses collectives via des organisations internationales (ONU, Interpol, OMS)."},
        {"id": "765_6", "type": "texte",
         "question": "Comment appelle-t-on une attaque informatique qui prend en otage les donnees d'une entreprise ou d'une institution en les chiffrant jusqu'au paiement d'une rancon ?",
         "correct_answer": "ranÃ§ongiciel",
         "explanation": "Un ranÃ§ongiciel (ransomware) est un logiciel malveillant qui chiffre les donnÃ©es et exige une ranÃ§on (souvent en cryptomonnaie) pour leur restitution."},
        {"id": "765_7", "type": "qcm",
         "question": "Quel organisme onusien est charge du maintien de la paix et de la securite internationale ?",
         "options": [
             "L'Assemblee generale de l'ONU",
             "Le Conseil de securite de l'ONU",
             "La Cour internationale de justice",
             "L'UNESCO"
         ],
         "correct_option": "Le Conseil de securite de l'ONU",
         "explanation": "Le Conseil de sÃ©curitÃ© de l'ONU (15 membres dont 5 permanents : USA, Russie, Chine, France, Royaume-Uni) a la responsabilitÃ© principale du maintien de la paix et de la sÃ©curitÃ© internationales."},
        {"id": "765_8", "type": "vrai-faux",
         "question": "La France peut opposer son veto a toute resolution du Conseil de securite de l'ONU.",
         "correct": True,
         "explanation": "La France est l'un des 5 membres permanents du Conseil de sÃ©curitÃ© et dispose du droit de veto sur toute rÃ©solution, lui confÃ©rant une influence majeure dans la diplomatie mondiale."},
    ]),

    # =========================================================
    # BLOC 6 - ENJEUX DE SOCIETE CONTEMPORAINS (766-770)
    # =========================================================
    (766, "L'egalite femmes-hommes : droits et combats", "EMC", "3eme", [
        {"id": "766_1", "type": "qcm",
         "question": "En quelle annee les femmes ont-elles obtenu le droit de voter en France ?",
         "options": ["1789", "1906", "1944", "1965"],
         "correct_option": "1944",
         "explanation": "L'ordonnance du 21 avril 1944 (Gouvernement provisoire du gÃ©nÃ©ral de Gaulle) a accordÃ© le droit de vote et d'Ã©ligibilitÃ© aux femmes franÃ§aises."},
        {"id": "766_2", "type": "vrai-faux",
         "question": "La loi de paritÃ© de 2000 oblige les partis politiques francais a presenter autant de femmes que d'hommes sur leurs listes electorales.",
         "correct": True,
         "explanation": "La loi du 6 juin 2000 impose l'alternance stricte femmes-hommes sur les listes Ã©lectorales ; les partis ne respectant pas la paritÃ© sont sanctionnÃ©s financiÃ¨rement."},
        {"id": "766_3", "type": "texte",
         "question": "Comment appelle-t-on les stereotypes qui assignent des roles et comportements differents selon le sexe des individus ?",
         "correct_answer": "stereotypes de genre",
         "explanation": "Les stÃ©rÃ©otypes de genre sont des reprÃ©sentations figÃ©es attribuant des qualitÃ©s, rÃ´les ou comportements diffÃ©rents aux hommes et aux femmes, alimentant les discriminations."},
        {"id": "766_4", "type": "qcm",
         "question": "Quel est l'ecart de salaire moyen constate entre hommes et femmes en France ?",
         "options": [
             "Aucun ecart",
             "Environ 16 a 17% en defaveur des femmes",
             "50% en defaveur des femmes",
             "5% en defaveur des hommes"
         ],
         "correct_option": "Environ 16 a 17% en defaveur des femmes",
         "explanation": "Selon l'INSEE, l'Ã©cart de salaire entre femmes et hommes est d'environ 16-17% en dÃ©faveur des femmes (tous secteurs et temps de travail confondus)."},
        {"id": "766_5", "type": "vrai-faux",
         "question": "Le sexisme au travail (remarques, blagues, discriminations basees sur le genre) est interdit et sanctionnable legalement.",
         "correct": True,
         "explanation": "Le sexisme et le harcÃ¨lement sexuel au travail sont rÃ©primÃ©s par le Code pÃ©nal et le Code du travail ; l'employeur a l'obligation de les prÃ©venir."},
        {"id": "766_6", "type": "texte",
         "question": "Comment appelle-t-on le mouvement international ayant denonce le harcelement et les violences sexuelles depuis 2017 ?",
         "correct_answer": "MeToo",
         "explanation": "Le mouvement #MeToo (2017) a libÃ©rÃ© la parole des victimes de harcÃ¨lement et violences sexuelles, entraÃ®nant des Ã©volutions lÃ©gislatives et sociales importantes."},
        {"id": "766_7", "type": "qcm",
         "question": "Quel est l'objectif de developpement durable (ODD 5) de l'ONU relatif a l'egalite des sexes ?",
         "options": [
             "Eliminer toutes les formes de discrimination et de violence a l'egard des femmes et des filles",
             "Garantir l'egalite des droits des hommes seulement",
             "Imposer des quotas dans toutes les entreprises",
             "Interdire le travail des femmes dans certains secteurs"
         ],
         "correct_option": "Eliminer toutes les formes de discrimination et de violence a l'egard des femmes et des filles",
         "explanation": "L'ODD 5 des Nations Unies vise Ã  'parvenir Ã  l'Ã©galitÃ© des sexes et autonomiser toutes les femmes et les filles', incluant la fin des violences et discriminations."},
        {"id": "766_8", "type": "vrai-faux",
         "question": "La Convention d'Istanbul (2011) est un traite europeen contre les violences faites aux femmes.",
         "correct": True,
         "explanation": "La Convention d'Istanbul (Convention du Conseil de l'Europe sur la prÃ©vention et la lutte contre la violence Ã  l'Ã©gard des femmes, 2011) est le traitÃ© international le plus complet en matiÃ¨re de violences faites aux femmes."},
    ]),

    (767, "Le numerique, les libertes et la vie privee", "EMC", "3eme", [
        {"id": "767_1", "type": "qcm",
         "question": "Quelle loi francaise de 1978 a ete la premiere au monde a proteger les donnees personnelles ?",
         "options": [
             "Loi Auroux de 1982",
             "Loi Informatique et Libertes de 1978",
             "Loi Badinter de 1981",
             "Loi de 2004 pour la confiance numerique"
         ],
         "correct_option": "Loi Informatique et Libertes de 1978",
         "explanation": "La loi du 6 janvier 1978 (dite 'Informatique et LibertÃ©s') a crÃ©Ã© la CNIL et posÃ© les bases de la protection des donnÃ©es personnelles en France."},
        {"id": "767_2", "type": "vrai-faux",
         "question": "Le RGPD donne aux individus le droit d'acceder a leurs donnees personnelles detenues par une entreprise et d'en demander la suppression.",
         "correct": True,
         "explanation": "Le RGPD (2018) garantit les droits d'accÃ¨s, de rectification, d'effacement ('droit Ã  l'oubli'), de portabilitÃ© et d'opposition au traitement des donnÃ©es personnelles."},
        {"id": "767_3", "type": "texte",
         "question": "Quelle autorite administrative independante controle le respect des donnees personnelles en France ?",
         "correct_answer": "CNIL",
         "explanation": "La CNIL (Commission Nationale de l'Informatique et des LibertÃ©s) veille au respect de la loi Informatique et LibertÃ©s et du RGPD ; elle peut sanctionner les entreprises en infraction."},
        {"id": "767_4", "type": "qcm",
         "question": "Qu'est-ce que le 'droit a l'oubli' numerique ?",
         "options": [
             "L'interdiction de publier des informations sur internet",
             "Le droit de demander la suppression de donnees personnelles des moteurs de recherche et sites web",
             "L'interdiction pour les entreprises de stocker des donnees",
             "Le droit de changer d'identite numerique"
         ],
         "correct_option": "Le droit de demander la suppression de donnees personnelles des moteurs de recherche et sites web",
         "explanation": "ConsacrÃ© par la CJUE en 2014 (affaire Google Spain) et le RGPD, le droit Ã  l'oubli permet de demander le dÃ©rÃ©fÃ©rencement d'informations personnelles obsolÃ¨tes ou inexactes."},
        {"id": "767_5", "type": "vrai-faux",
         "question": "Le harcelement en ligne (cyberbullying) est une infraction penale en France.",
         "correct": True,
         "explanation": "Le cyberharcÃ¨lement est rÃ©primÃ© par le Code pÃ©nal (articles 222-33-2 et suivants) et peut Ãªtre puni d'amendes et d'emprisonnement."},
        {"id": "767_6", "type": "texte",
         "question": "Comment appelle-t-on la technique frauduleuse consistant a usurper l'identite d'une banque ou d'un service officiel pour voler des donnees personnelles ?",
         "correct_answer": "phishing",
         "explanation": "Le phishing (hameÃ§onnage) est une technique de fraude en ligne qui consiste Ã  imiter des sites ou courriels officiels pour voler des identifiants, mots de passe ou donnÃ©es bancaires."},
        {"id": "767_7", "type": "qcm",
         "question": "Quel principe fondamental du numerique permet a un reseau de fonctionner sans favoriser certains contenus ou services par rapport a d'autres ?",
         "options": [
             "Principe de confidentialite",
             "Neutralite du net",
             "Principe de subsidiarite numerique",
             "Interoperabilite"
         ],
         "correct_option": "Neutralite du net",
         "explanation": "La neutralitÃ© du net garantit que les fournisseurs d'accÃ¨s internet traitent tous les flux de donnÃ©es de la mÃªme maniÃ¨re, sans discrimination selon leur source, destination ou contenu."},
        {"id": "767_8", "type": "vrai-faux",
         "question": "Les mineurs de moins de 15 ans doivent avoir l'accord de leurs parents pour s'inscrire sur un reseau social en Europe.",
         "correct": True,
         "explanation": "Le RGPD fixe Ã  16 ans le seuil de consentement numÃ©rique, mais autorise les Ã‰tats membres Ã  l'abaisser Ã  13 ans minimum ; la France a choisi 15 ans."},
    ]),

    (768, "L'ecologie politique et la citoyennete environnementale", "EMC", "3eme", [
        {"id": "768_1", "type": "qcm",
         "question": "Quel droit constitutionnel reconnait en France le droit a un environnement sain ?",
         "options": [
             "La Declaration des droits de l'homme de 1789",
             "La Charte de l'environnement de 2004, adossee a la Constitution",
             "La loi de 1976 sur la protection de la nature",
             "La Convention d'Aarhus"
         ],
         "correct_option": "La Charte de l'environnement de 2004, adossee a la Constitution",
         "explanation": "La Charte de l'environnement (2004) a Ã©tÃ© adossÃ©e Ã  la Constitution de 1958 et reconnaÃ®t Ã  chacun le droit de vivre dans un environnement Ã©quilibrÃ© et respectueux de la santÃ©."},
        {"id": "768_2", "type": "vrai-faux",
         "question": "Le principe de precaution signifie qu'en cas d'incertitude scientifique, on doit eviter toute action susceptible de causer un dommage grave et irreversible.",
         "correct": True,
         "explanation": "Le principe de prÃ©caution (article 5 de la Charte de l'environnement) impose de prendre des mesures provisoires mÃªme sans certitude scientifique lorsque les risques de dommages graves sont potentiels."},
        {"id": "768_3", "type": "texte",
         "question": "Comment appelle-t-on les jeunes militants climatiques qui manifestent pour exiger des gouvernements une action urgente contre le changement climatique ?",
         "correct_answer": "activistes climatiques",
         "explanation": "Des mouvements comme Fridays for Future (lancÃ© par Greta Thunberg en 2018) ou Youth for Climate mobilisent des millions de jeunes dans le monde pour exiger des politiques climatiques ambitieuses."},
        {"id": "768_4", "type": "qcm",
         "question": "Qu'est-ce que la Convention citoyenne pour le climat (2019-2020) ?",
         "options": [
             "Un accord international entre les gouvernements",
             "Une assemblee de 150 citoyens tires au sort pour proposer des mesures de lutte contre le changement climatique",
             "Un referendum sur l'energie nucleaire",
             "Un organe permanent du gouvernement francais"
         ],
         "correct_option": "Une assemblee de 150 citoyens tires au sort pour proposer des mesures de lutte contre le changement climatique",
         "explanation": "La Convention citoyenne pour le climat (CCC) a rÃ©uni 150 citoyens tirÃ©s au sort en 2019-2020 pour proposer des mesures rÃ©duisant les Ã©missions de GES de 40% d'ici 2030."},
        {"id": "768_5", "type": "vrai-faux",
         "question": "Le tri selectif des dechets et le compostage sont des gestes de citoyennete environnementale.",
         "correct": True,
         "explanation": "Le tri sÃ©lectif, le compostage, la rÃ©duction des dÃ©chets et la sobriÃ©tÃ© Ã©nergÃ©tique sont des actes citoyens qui contribuent collectivement Ã  la protection de l'environnement."},
        {"id": "768_6", "type": "texte",
         "question": "Comment appelle-t-on la responsabilite des entreprises de prendre en compte l'impact social et environnemental de leurs activites ?",
         "correct_answer": "RSE",
         "explanation": "La RSE (ResponsabilitÃ© SociÃ©tale des Entreprises) dÃ©signe l'engagement volontaire des entreprises Ã  intÃ©grer des prÃ©occupations sociales et environnementales dans leurs activitÃ©s."},
        {"id": "768_7", "type": "qcm",
         "question": "Quel concept designe la capacite de la planete a satisfaire les besoins humains sans depasser ses limites ecologiques ?",
         "options": [
             "Empreinte carbone",
             "Capacite de charge de la Terre (limites planetaires)",
             "Biodiversite fonctionnelle",
             "PIB vert"
         ],
         "correct_option": "Capacite de charge de la Terre (limites planetaires)",
         "explanation": "Les 9 limites planÃ©taires (RockstrÃ¶m et al., 2009) dÃ©finissent l'espace sÃ©curisÃ© pour l'humanitÃ© ; plusieurs ont dÃ©jÃ  Ã©tÃ© dÃ©passÃ©es (changement climatique, biodiversitÃ©)."},
        {"id": "768_8", "type": "vrai-faux",
         "question": "La justice environnementale vise a garantir que toutes les communautes, independamment de leur richesse, beneficient d'un environnement sain.",
         "correct": True,
         "explanation": "La justice environnementale lutte contre les inÃ©galitÃ©s Ã©cologiques : les populations pauvres et marginalisÃ©es sont souvent les plus exposÃ©es aux pollutions et risques environnementaux."},
    ]),

    (769, "Solidarite, fraternite et engagement humanitaire", "EMC", "3eme", [
        {"id": "769_1", "type": "qcm",
         "question": "Quelle valeur republicaine complementaire a l'egalite et a la liberte appelle a l'entraide entre les membres de la societe ?",
         "options": [
             "Laicite",
             "Fraternite",
             "Subsidiarite",
             "Legitimite"
         ],
         "correct_option": "Fraternite",
         "explanation": "La fraternitÃ©, troisiÃ¨me terme de la devise rÃ©publicaine, exprime le lien de solidaritÃ© entre citoyens : chacun doit contribuer au bien commun et aider les plus fragiles."},
        {"id": "769_2", "type": "vrai-faux",
         "question": "Le Conseil constitutionnel a reconnu le principe de fraternite comme une valeur constitutionnelle en 2018.",
         "correct": True,
         "explanation": "Dans sa dÃ©cision du 6 juillet 2018 (affaire 'dÃ©lit de solidaritÃ©'), le Conseil constitutionnel a reconnu le principe de fraternitÃ© comme une valeur Ã  valeur constitutionnelle."},
        {"id": "769_3", "type": "texte",
         "question": "Comment appelle-t-on le mouvement international fonde par Henri Dunant en 1863 qui vient en aide aux victimes de guerre et de catastrophes ?",
         "correct_answer": "Croix-Rouge",
         "explanation": "La Croix-Rouge internationale (CICR), fondÃ©e en 1863 par Henri Dunant aprÃ¨s la bataille de SolfÃ©rino, est Ã  l'origine du droit international humanitaire."},
        {"id": "769_4", "type": "qcm",
         "question": "Que signifie le concept de 'responsabilite de proteger' (R2P) adopte par l'ONU en 2005 ?",
         "options": [
             "Les Etats doivent proteger leur propre population",
             "La communaute internationale a le droit d'intervenir quand un Etat faillit a proteger ses citoyens de crimes contre l'humanite",
             "Chaque citoyen doit financer les forces armees",
             "Les entreprises doivent proteger leurs employes"
         ],
         "correct_option": "La communaute internationale a le droit d'intervenir quand un Etat faillit a proteger ses citoyens de crimes contre l'humanite",
         "explanation": "La R2P Ã©tablit que si un Ã‰tat ne peut ou ne veut pas protÃ©ger ses citoyens de gÃ©nocides, crimes de guerre ou crimes contre l'humanitÃ©, la communautÃ© internationale peut intervenir."},
        {"id": "769_5", "type": "vrai-faux",
         "question": "Le droit d'asile protege les personnes persecutees dans leur pays d'origine.",
         "correct": True,
         "explanation": "Le droit d'asile, garanti par la Convention de GenÃ¨ve (1951) et inscrit dans la Constitution franÃ§aise, protÃ¨ge toute personne persÃ©cutÃ©e pour ses opinions politiques, religieuses ou son origine."},
        {"id": "769_6", "type": "texte",
         "question": "Comment appelle-t-on l'obligation morale de porter secours a une personne en danger ?",
         "correct_answer": "devoir d'assistance a personne en danger",
         "explanation": "L'article 223-6 du Code pÃ©nal punit la non-assistance Ã  personne en danger ; le devoir d'assistance est une obligation lÃ©gale et morale."},
        {"id": "769_7", "type": "qcm",
         "question": "Qu'est-ce que le Programme alimentaire mondial (PAM) ?",
         "options": [
             "Une entreprise agroalimentaire multinationale",
             "L'agence de l'ONU chargee de lutter contre la faim dans le monde",
             "Un accord commercial agricole",
             "Un fonds de recherche sur les OGM"
         ],
         "correct_option": "L'agence de l'ONU chargee de lutter contre la faim dans le monde",
         "explanation": "Le PAM (Prix Nobel de la Paix 2020) est la plus grande organisation humanitaire mondiale ; il fournit une aide alimentaire d'urgence et lutte contre la faim et la malnutrition."},
        {"id": "769_8", "type": "vrai-faux",
         "question": "La solidarite internationale passe notamment par l'aide publique au developpement (APD) que les pays riches versent aux pays en developpement.",
         "correct": True,
         "explanation": "L'APD (aide publique au dÃ©veloppement) est un transfert financier officiel des pays riches vers les pays en dÃ©veloppement pour financer des projets de santÃ©, d'Ã©ducation ou d'infrastructure."},
    ]),

    (770, "Revision generale EMC : citoyennete et valeurs republicaines", "EMC", "3eme", [
        {"id": "770_1", "type": "qcm",
         "question": "Quels sont les cinq adjectifs qui qualifient la Republique francaise selon l'article 1 de la Constitution de 1958 ?",
         "options": [
             "Libre, egale, fraternelle, solidaire, europeenne",
             "Indivisible, laique, democratique, sociale, europeenne",
             "Forte, juste, seculiere, federale, pacifique",
             "Centralise, populaire, laique, democratique, socialiste"
         ],
         "correct_option": "Indivisible, laique, democratique, sociale, europeenne",
         "explanation": "L'article 1 : 'La France est une RÃ©publique indivisible, laÃ¯que, dÃ©mocratique et sociale.' Le terme 'europÃ©enne' a Ã©tÃ© ajoutÃ© ultÃ©rieurement dans d'autres articles."},
        {"id": "770_2", "type": "vrai-faux",
         "question": "Un citoyen peut etre prive de sa nationalite francaise comme sanction judiciaire.",
         "correct": False,
         "explanation": "En droit franÃ§ais, la dÃ©chÃ©ance de nationalitÃ© pour les binationaux est possible dans des cas trÃ¨s limitÃ©s (crimes terroristes) mais reste constitutionnellement et juridiquement complexe."},
        {"id": "770_3", "type": "texte",
         "question": "Comment appelle-t-on le texte fondamental qui enumere les droits et libertes fondamentaux et que tout Etat doit respecter selon la pensee liberale ?",
         "correct_answer": "constitution",
         "explanation": "La Constitution est la loi fondamentale d'un Ã‰tat ; elle organise les pouvoirs, garantit les droits fondamentaux et s'impose Ã  toutes les autres normes juridiques."},
        {"id": "770_4", "type": "qcm",
         "question": "Quel est le symbole republicain represente sur le buste de Marianne, symbole de la Republique francaise ?",
         "options": [
             "Le bonnet phrygien",
             "La couronne royale",
             "Le casque militaire",
             "Le voile monastique"
         ],
         "correct_option": "Le bonnet phrygien",
         "explanation": "Marianne porte le bonnet phrygien (symbole de la libertÃ© dans l'AntiquitÃ©), emblÃ¨me de la RÃ©publique franÃ§aise depuis la RÃ©volution."},
        {"id": "770_5", "type": "vrai-faux",
         "question": "Le Conseil economique, social et environnemental (CESE) represente la societe civile aupres des pouvoirs publics.",
         "correct": True,
         "explanation": "Le CESE (troisiÃ¨me assemblÃ©e constitutionnelle de France) reprÃ©sente les forces vives de la nation (syndicats, associations, ONG) et Ã©met des avis consultatifs sur les projets de loi Ã©conomiques et sociaux."},
        {"id": "770_6", "type": "texte",
         "question": "Comment appelle-t-on la procedure permettant a un groupe de citoyens d'imposer un vote au Parlement sur une question de loi ?",
         "correct_answer": "initiative populaire",
         "explanation": "L'initiative populaire ou rÃ©fÃ©rendum d'initiative partagÃ©e (RIP, article 11 de la Constitution) permet Ã  1/10 des Ã©lecteurs appuyÃ©s par 1/5 du Parlement de soumettre une proposition de loi Ã  rÃ©fÃ©rendum."},
        {"id": "770_7", "type": "qcm",
         "question": "Quel est le fondement de la democratie selon Abraham Lincoln ?",
         "options": [
             "Le gouvernement du peuple par les experts",
             "Le gouvernement du peuple, par le peuple, pour le peuple",
             "Le gouvernement des meilleurs citoyens",
             "Le gouvernement de la majorite sans droits pour la minorite"
         ],
         "correct_option": "Le gouvernement du peuple, par le peuple, pour le peuple",
         "explanation": "La formule de Lincoln (discours de Gettysburg, 1863) : 'government of the people, by the people, for the people' rÃ©sume l'essence de la dÃ©mocratie."},
        {"id": "770_8", "type": "vrai-faux",
         "question": "Un Etat peut etre a la fois democratique et ne pas respecter les droits de l'homme.",
         "correct": False,
         "explanation": "Une vÃ©ritable dÃ©mocratie implique nÃ©cessairement le respect des droits fondamentaux, la sÃ©paration des pouvoirs, la libertÃ© de la presse et des Ã©lections libres ; sans ces garanties, on parle de dÃ©mocratie illibÃ©rale ou autoritaire."},
    ]),
]

EMC3_COMPLEMENT_SPECS = [
    (6201, "La citoyenneté numérique", "la citoyenneté numérique"),
    (6202, "Liberté d'expression et responsabilité", "la liberté d'expression"),
    (6203, "Lutte contre le harcèlement", "le harcèlement"),
    (6204, "Égalité filles-garçons", "l'égalité"),
    (6205, "Laïcité à l'école", "la laïcité"),
    (6206, "Respect des différences", "le respect d'autrui"),
    (6207, "Les symboles de la République", "les symboles républicains"),
    (6208, "La justice des mineurs", "la justice des mineurs"),
    (6209, "Le rôle du maire", "les collectivités locales"),
    (6210, "Le vote et la participation", "la participation citoyenne"),
    (6211, "Les médias et l'esprit critique", "l'esprit critique"),
    (6212, "La protection des données", "les données personnelles"),
    (6213, "Droits et devoirs au collège", "les droits et devoirs"),
    (6214, "Solidarité et engagement", "la solidarité"),
    (6215, "Le bénévolat", "l'engagement associatif"),
    (6216, "Prévention des discriminations", "les discriminations"),
    (6217, "Défense et sécurité", "la défense nationale"),
    (6218, "Secours et protection civile", "la protection civile"),
    (6219, "Le développement durable", "la responsabilité environnementale"),
    (6220, "La fraternité républicaine", "la fraternité"),
    (6221, "Le débat démocratique", "le débat démocratique"),
    (6222, "Les institutions européennes", "les institutions européennes"),
    (6223, "Les droits de l'enfant", "les droits de l'enfant"),
    (6224, "Agir pour le bien commun", "le bien commun"),
]


def build_emc3_complement(qid, title, focus):
    return (
        qid,
        title,
        "EMC",
        "3eme",
        [
            {"id": f"{qid}_1", "type": "qcm", "question": f"En EMC, pourquoi étudie-t-on {focus} ?", "options": ["Pour comprendre la vie citoyenne et républicaine", "Pour faire un calcul", "Pour apprendre une formule chimique", "Pour étudier un circuit"], "correct_option": "Pour comprendre la vie citoyenne et républicaine", "explanation": "L'EMC aide à comprendre les règles communes, les droits, les responsabilités et la vie démocratique."},
            {"id": f"{qid}_2", "type": "vrai-faux", "question": f"{focus.capitalize()} peut être relié à des situations concrètes de la vie quotidienne.", "correct": True, "explanation": "L'EMC s'appuie sur des exemples concrets pour relier les valeurs républicaines à la vie de tous les jours."},
            {"id": f"{qid}_3", "type": "qcm", "question": f"Quelle attitude correspond le mieux à l'étude de {focus} ?", "options": ["Écouter, argumenter et respecter les autres", "Insulter pour convaincre", "Refuser tout échange", "Ignorer les règles communes"], "correct_option": "Écouter, argumenter et respecter les autres", "explanation": "Le respect, l'écoute et l'argumentation sont des attitudes centrales en EMC."},
            {"id": f"{qid}_4", "type": "vrai-faux", "question": "Une bonne compréhension de l'EMC aide à mieux exercer sa citoyenneté.", "correct": True, "explanation": "L'EMC prépare les élèves à comprendre leurs droits, leurs devoirs et leur rôle de citoyen."},
            {"id": f"{qid}_5", "type": "qcm", "question": f"Quel est l'objectif principal d'un travail sur {focus} ?", "options": ["Réfléchir et agir de manière responsable", "Répondre sans justification", "Mémoriser sans comprendre", "Éviter le dialogue"], "correct_option": "Réfléchir et agir de manière responsable", "explanation": "L'EMC vise l'esprit critique, la responsabilité et le respect du bien commun."},
            {"id": f"{qid}_6", "type": "vrai-faux", "question": "Discuter d'une situation concrète peut aider à mieux comprendre une valeur républicaine.", "correct": True, "explanation": "Les études de cas permettent d'appliquer les principes républicains à des situations réelles."},
            {"id": f"{qid}_7", "type": "qcm", "question": f"Quelle méthode aide le plus à progresser sur {focus} ?", "options": ["Débattre, justifier et corriger", "Ne jamais s'expliquer", "Parler sans écouter", "Répondre au hasard"], "correct_option": "Débattre, justifier et corriger", "explanation": "L'argumentation et la correction des erreurs favorisent une compréhension plus solide en EMC."},
            {"id": f"{qid}_8", "type": "vrai-faux", "question": "Respecter autrui et le cadre commun est cohérent avec les apprentissages d'EMC.", "correct": True, "explanation": "Le respect d'autrui et du cadre collectif est au cœur de l'enseignement moral et civique."},
        ],
    )


quizzes_data.extend(build_emc3_complement(*spec) for spec in EMC3_COMPLEMENT_SPECS)


def write_quiz_files():
    os.makedirs(EMC3_QUIZ_DIR, exist_ok=True)
    os.makedirs(EMC3_ANSWERS_DIR, exist_ok=True)
    os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
    os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
    os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
    os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)

    total_quizzes = 0
    total_files = 0

    for qid, title, subject, level, questions in quizzes_data:
        quiz_obj = make_quiz(qid, title, subject, level, questions)
        quiz_obj = normalize_text_payload(quiz_obj)
        quiz_path = os.path.join(EMC3_QUIZ_DIR, f"{qid}.json")
        with open(quiz_path, "w", encoding="utf-8", newline="\n") as file_handle:
            json.dump(quiz_obj, file_handle, ensure_ascii=False, indent=2)
            file_handle.write("\n")

        answers_obj = make_answers(qid, title, subject, level, questions)
        answers_obj = normalize_text_payload(answers_obj)
        answers_path = os.path.join(EMC3_ANSWERS_DIR, f"{qid}.json")
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

        total_quizzes += 1
        total_files += 6
        print(f"[OK] Quiz {qid} - {title} ({len(questions)} questions)")

    print("\n" + "=" * 60)
    print("  BATCH E - EMC 3eme TERMINE")
    print(f"  {total_quizzes} quizzes generes (IDs 747-770)")
    print(f"  {total_files} fichiers JSON crees")
    print(f"  ({EMC3_QUIZ_DIR} + {EMC3_ANSWERS_DIR})")
    print(f"  ({OUTPUT_QUIZ_DIR} + {OUTPUT_ANSWERS_DIR})")
    print(f"  ({RUNTIME_QUIZ_DIR} + {RUNTIME_ANSWERS_DIR})")
    print(f"  {total_quizzes * 8} questions au total")
    print("=" * 60)


if __name__ == "__main__":
    write_quiz_files()

