#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
GÃ©nÃ©ration des quiz SVT 3Ã¨me â€“ IDs 651â€“698
48 quizzes Ã— 8 questions = 384 questions
ThÃ¨mes : GÃ©nÃ©tique, Ã‰volution, Immunologie, Reproduction,
         Nutrition, GÃ©ologie, Ã‰cologie, RÃ©visions
Pattern : qcm, vrai-faux, texte, qcm, vrai-faux, texte, qcm, vrai-faux
"""

import json
import os
import random
import re
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
SVT3_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "svt_3eme_quizzes")
SVT3_QUIZ_DIR = os.path.join(SVT3_OUTPUT_DIR, "quiz")
SVT3_ANSWERS_DIR = os.path.join(SVT3_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "svt_3eme_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

os.makedirs(SVT3_QUIZ_DIR, exist_ok=True)
os.makedirs(SVT3_ANSWERS_DIR, exist_ok=True)
os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)


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
        return {key: normalize_text_payload(value) for key, value in payload.items()}
    if isinstance(payload, list):
        return [normalize_text_payload(item) for item in payload]
    if isinstance(payload, tuple):
        return tuple(normalize_text_payload(item) for item in payload)
    if isinstance(payload, str):
        return fix_mojibake_text(payload)
    return payload


def dump_json_file(path, payload):
    with open(path, "w", encoding="utf-8", newline="\n") as file_handle:
        json.dump(normalize_text_payload(payload), file_handle, ensure_ascii=False, indent=2)
        file_handle.write("\n")


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
    # BLOC 1 â€“ GÃ‰NÃ‰TIQUE ET HÃ‰RÃ‰DITÃ‰ (651â€“656)
    # =========================================================
    (651, "GÃ©nÃ©tique : cellule, ADN et chromosomes", "SVT", "3Ã¨me", [
        {"id":"651_1","type":"qcm","question":"Dans une cellule humaine, combien de chromosomes trouve-t-on ?",
         "options":["23","46","48","92"],"correct_option":"46","explanation":"Les cellules humaines sont diploÃ¯des et possÃ¨dent 46 chromosomes (23 paires)."},
        {"id":"651_2","type":"vrai-faux","question":"L'ADN est une molÃ©cule localisÃ©e uniquement dans le cytoplasme de la cellule.",
         "correct":False,"explanation":"L'ADN se trouve principalement dans le noyau (et aussi dans les mitochondries)."},
        {"id":"651_3","type":"texte","question":"Quel est le nom de l'unitÃ© fonctionnelle de l'ADN qui porte l'information gÃ©nÃ©tique ?",
         "correct_answer":"gÃ¨ne","explanation":"Un gÃ¨ne est une portion d'ADN codant une information hÃ©rÃ©ditaire."},
        {"id":"651_4","type":"qcm","question":"Qu'est-ce qu'un allÃ¨le ?",
         "options":["Un chromosome entier","Une forme alternative d'un gÃ¨ne","Une cellule reproductrice","Un type d'ARN"],
         "correct_option":"Une forme alternative d'un gÃ¨ne","explanation":"Les allÃ¨les sont les diffÃ©rentes versions d'un mÃªme gÃ¨ne, occupant le mÃªme locus."},
        {"id":"651_5","type":"vrai-faux","question":"Les gamÃ¨tes (ovule, spermatozoÃ¯de) contiennent 46 chromosomes chez l'Ãªtre humain.",
         "correct":False,"explanation":"Les gamÃ¨tes sont haploÃ¯des : ils ne contiennent que 23 chromosomes."},
        {"id":"651_6","type":"texte","question":"Comment appelle-t-on le phÃ©nomÃ¨ne par lequel une cellule mÃ¨re donne deux cellules filles gÃ©nÃ©tiquement identiques ?",
         "correct_answer":"mitose","explanation":"La mitose assure la reproduction conforme des cellules somatiques."},
        {"id":"651_7","type":"qcm","question":"Un individu possÃ©dant deux allÃ¨les identiques pour un gÃ¨ne est dit :",
         "options":["hÃ©tÃ©rozygote","homozygote","diploÃ¯de","haploÃ¯de"],"correct_option":"homozygote",
         "explanation":"L'homozygotie signifie que les deux allÃ¨les du gÃ¨ne sont identiques."},
        {"id":"651_8","type":"vrai-faux","question":"La mÃ©iose produit des cellules haploÃ¯des Ã  partir d'une cellule diploÃ¯de.",
         "correct":True,"explanation":"La mÃ©iose divise par deux le nombre de chromosomes pour former les gamÃ¨tes."},
    ]),

    (652, "GÃ©nÃ©tique : lois de l'hÃ©rÃ©ditÃ© et croisements", "SVT", "3Ã¨me", [
        {"id":"652_1","type":"qcm","question":"Lorsqu'on croise deux individus homozygotes de gÃ©notypes AA et aa, quelle est la descendance F1 ?",
         "options":["Tous aa","Tous AA","Tous Aa","50% AA, 50% aa"],"correct_option":"Tous Aa",
         "explanation":"Tous les individus F1 reÃ§oivent un allÃ¨le A de chaque parent : ils sont tous hÃ©tÃ©rozygotes Aa."},
        {"id":"652_2","type":"vrai-faux","question":"Si A est dominant sur a, un individu Aa prÃ©sente le phÃ©notype associÃ© Ã  l'allÃ¨le a.",
         "correct":False,"explanation":"Avec A dominant, un individu Aa exprime le phÃ©notype A."},
        {"id":"652_3","type":"texte","question":"Dans un croisement Aa Ã— Aa, quelle proportion d'individus prÃ©sente le gÃ©notype aa ?",
         "correct_answer":"1/4","explanation":"La grille de Punnett donne AA (1/4), Aa (2/4), aa (1/4)."},
        {"id":"652_4","type":"qcm","question":"Quel outil permet de visualiser les chromosomes d'un individu classÃ©s par paires ?",
         "options":["Ã‰lectrophorÃ¨se","Caryotype","IRM","SÃ©quenÃ§age"],"correct_option":"Caryotype",
         "explanation":"Le caryotype est la reprÃ©sentation ordonnÃ©e des chromosomes d'une cellule."},
        {"id":"652_5","type":"vrai-faux","question":"Le sexe gÃ©nÃ©tique humain est dÃ©terminÃ© par les chromosomes sexuels XX (femme) et XY (homme).",
         "correct":True,"explanation":"Les chromosomes sexuels X et Y dÃ©terminent le sexe gÃ©nÃ©tique chez les mammifÃ¨res."},
        {"id":"652_6","type":"texte","question":"Comment appelle-t-on la transmission d'un gÃ¨ne situÃ© sur le chromosome X ?",
         "correct_answer":"hÃ©rÃ©ditÃ© liÃ©e Ã  l'X","explanation":"Les gÃ¨nes portÃ©s par le chromosome X sont transmis selon un mode liÃ© au sexe."},
        {"id":"652_7","type":"qcm","question":"Que montre un arbre gÃ©nÃ©alogique ?",
         "options":["La structure de l'ADN","Les liens de parentÃ© et la transmission des caractÃ¨res","La formule chromosomique","La composition en bases azotÃ©es"],
         "correct_option":"Les liens de parentÃ© et la transmission des caractÃ¨res",
         "explanation":"L'arbre gÃ©nÃ©alogique reprÃ©sente la transmission des caractÃ¨res hÃ©rÃ©ditaires dans une famille."},
        {"id":"652_8","type":"vrai-faux","question":"Une mutation est toujours pathologique.",
         "correct":False,"explanation":"Les mutations peuvent Ãªtre neutres, bÃ©nÃ©fiques ou nocives selon leur contexte."},
    ]),

    (653, "GÃ©nÃ©tique : mutations et maladies gÃ©nÃ©tiques", "SVT", "3Ã¨me", [
        {"id":"653_1","type":"qcm","question":"Quelle est la cause chromosomique de la trisomie 21 ?",
         "options":["Absence du chromosome 21","Trois exemplaires du chromosome 21","Mutation du gÃ¨ne 21","DÃ©lÃ©tion du chromosome 21"],
         "correct_option":"Trois exemplaires du chromosome 21","explanation":"La trisomie 21 est due Ã  la prÃ©sence d'un chromosome 21 supplÃ©mentaire (3 au lieu de 2)."},
        {"id":"653_2","type":"vrai-faux","question":"Les rayonnements UV peuvent provoquer des mutations de l'ADN.",
         "correct":True,"explanation":"Les UV sont un agent mutagÃ¨ne qui peut induire des lÃ©sions de l'ADN, notamment dans les cellules de la peau."},
        {"id":"653_3","type":"texte","question":"Quel mÃ©canisme cellulaire rÃ©pare la plupart des mutations de l'ADN avant la division ?",
         "correct_answer":"rÃ©paration de l'ADN","explanation":"Les enzymes de rÃ©paration corrigent la plupart des erreurs survenant lors de la rÃ©plication ou sous l'effet de mutagÃ¨nes."},
        {"id":"653_4","type":"qcm","question":"La drÃ©panocytose est une maladie gÃ©nÃ©tique affectant :",
         "options":["Les os","Les globules rouges","Les neurones","Le foie"],
         "correct_option":"Les globules rouges","explanation":"La drÃ©panocytose est une mutation du gÃ¨ne de la globine bÃªta qui dÃ©forme les globules rouges."},
        {"id":"653_5","type":"vrai-faux","question":"Toutes les mutations des cellules somatiques sont transmissibles aux enfants.",
         "correct":False,"explanation":"Seules les mutations des cellules germinales (gamÃ¨tes) peuvent Ãªtre transmises Ã  la descendance."},
        {"id":"653_6","type":"texte","question":"Comment appelle-t-on un individu porteur d'un seul allÃ¨le mutÃ© rÃ©cessif sans manifester la maladie ?",
         "correct_answer":"conducteur","explanation":"Un conducteur (ou porteur sain) possÃ¨de un allÃ¨le mutÃ© rÃ©cessif et un allÃ¨le normal ; il n'est pas malade mais peut transmettre la mutation."},
        {"id":"653_7","type":"qcm","question":"Quel type de rayonnement est le plus souvent Ã  l'origine des cancers de la peau ?",
         "options":["Infrarouge","Ultraviolet","Micro-ondes","Rayons gamma"],
         "correct_option":"Ultraviolet","explanation":"Les UV solaires sont le principal facteur de risque des mÃ©lanomes et carcinomes cutanÃ©s."},
        {"id":"653_8","type":"vrai-faux","question":"La mucoviscidose est une maladie autosomique rÃ©cessive.",
         "correct":True,"explanation":"Le gÃ¨ne CFTR responsable de la mucoviscidose est situÃ© sur un autosome et la maladie nÃ©cessite deux allÃ¨les mutÃ©s."},
    ]),

    (654, "GÃ©nÃ©tique : diversitÃ© gÃ©nÃ©tique et brassage", "SVT", "3Ã¨me", [
        {"id":"654_1","type":"qcm","question":"Quel mÃ©canisme de la mÃ©iose augmente la diversitÃ© gÃ©nÃ©tique en Ã©changeant des segments entre chromosomes homologues ?",
         "options":["Mitose","Crossing-over","Apoptose","Traduction"],
         "correct_option":"Crossing-over","explanation":"Le crossing-over (enjambement) produit de nouveaux arrangements allÃ©liques lors de la mÃ©iose."},
        {"id":"654_2","type":"vrai-faux","question":"Deux frÃ¨res et sÅ“urs issus des mÃªmes parents ont exactement le mÃªme gÃ©nome.",
         "correct":False,"explanation":"La sÃ©grÃ©gation alÃ©atoire et les crossing-overs lors de la mÃ©iose rendent chaque individu gÃ©nÃ©tiquement unique (sauf jumeaux vrais)."},
        {"id":"654_3","type":"texte","question":"Comment appelle-t-on l'ensemble des allÃ¨les d'une espÃ¨ce au sein d'une population ?",
         "correct_answer":"pool gÃ©nÃ©tique","explanation":"Le pool gÃ©nÃ©tique (ou patrimoine gÃ©nÃ©tique) dÃ©signe l'ensemble des allÃ¨les prÃ©sents dans une population."},
        {"id":"654_4","type":"qcm","question":"Pourquoi deux individus humains ne sont-ils jamais gÃ©nÃ©tiquement identiques (hors vrais jumeaux) ?",
         "options":["Car ils ont des rÃ©gimes alimentaires diffÃ©rents","Car la mÃ©iose et la fÃ©condation sont des processus alÃ©atoires","Car les cellules mutent constamment","Car les chromosomes disparaissent aprÃ¨s chaque division"],
         "correct_option":"Car la mÃ©iose et la fÃ©condation sont des processus alÃ©atoires",
         "explanation":"La sÃ©grÃ©gation alÃ©atoire des chromosomes et les crossing-overs, combinÃ©s Ã  la fÃ©condation, assurent une diversitÃ© gÃ©nÃ©tique quasi infinie."},
        {"id":"654_5","type":"vrai-faux","question":"La reproduction sexuÃ©e est le seul mÃ©canisme permettant la diversitÃ© gÃ©nÃ©tique.",
         "correct":False,"explanation":"Les mutations sont aussi une source de diversitÃ© gÃ©nÃ©tique, indÃ©pendamment de la reproduction sexuÃ©e."},
        {"id":"654_6","type":"texte","question":"Quel est le nom du stade de la mÃ©iose oÃ¹ les chromosomes homologues s'Ã©changent des segments ?",
         "correct_answer":"prophase I","explanation":"C'est lors de la prophase I que se produisent les crossing-overs entre chromosomes homologues."},
        {"id":"654_7","type":"qcm","question":"Quelle est la diffÃ©rence entre gÃ©notype et phÃ©notype ?",
         "options":["Aucune diffÃ©rence","Le gÃ©notype est l'ensemble des allÃ¨les, le phÃ©notype est l'expression observable","Le phÃ©notype concerne l'ADN, le gÃ©notype les protÃ©ines","Le gÃ©notype est le comportement de l'individu"],
         "correct_option":"Le gÃ©notype est l'ensemble des allÃ¨les, le phÃ©notype est l'expression observable",
         "explanation":"Le gÃ©notype est la constitution allÃ©lique ; le phÃ©notype est le caractÃ¨re observable rÃ©sultant du gÃ©notype et de l'environnement."},
        {"id":"654_8","type":"vrai-faux","question":"L'environnement peut influencer l'expression d'un gÃ©notype.",
         "correct":True,"explanation":"Le phÃ©notype rÃ©sulte de l'interaction entre le gÃ©notype et l'environnement."},
    ]),

    (655, "GÃ©nÃ©tique : ADN, rÃ©plication et expression gÃ©nÃ©tique", "SVT", "3Ã¨me", [
        {"id":"655_1","type":"qcm","question":"Quelle est la structure de la molÃ©cule d'ADN ?",
         "options":["Simple brin circulaire","Double hÃ©lice","Triple brin","ChaÃ®ne d'acides aminÃ©s"],
         "correct_option":"Double hÃ©lice","explanation":"L'ADN est formÃ© de deux brins complÃ©mentaires enroulÃ©s en double hÃ©lice, modÃ¨le proposÃ© par Watson et Crick."},
        {"id":"655_2","type":"vrai-faux","question":"La rÃ©plication de l'ADN produit deux molÃ©cules filles dont chacune est identique Ã  la molÃ©cule mÃ¨re.",
         "correct":True,"explanation":"La rÃ©plication est semi-conservative : chaque molÃ©cule fille contient un brin parental et un brin nÃ©osynthÃ©tisÃ©."},
        {"id":"655_3","type":"texte","question":"Quel est le rÃ´le de l'ARN messager dans l'expression des gÃ¨nes ?",
         "correct_answer":"transporter l'information gÃ©nÃ©tique du noyau aux ribosomes pour la synthÃ¨se des protÃ©ines",
         "explanation":"L'ARN messager est transcrit Ã  partir de l'ADN dans le noyau, puis traduit en protÃ©ines par les ribosomes."},
        {"id":"655_4","type":"qcm","question":"Quelle base azotÃ©e est prÃ©sente dans l'ARN mais pas dans l'ADN ?",
         "options":["AdÃ©nine","Guanine","Uracile","Cytosine"],"correct_option":"Uracile",
         "explanation":"L'uracile remplace la thymine dans l'ARN ; l'ADN contient thymine Ã  la place."},
        {"id":"655_5","type":"vrai-faux","question":"Chaque gÃ¨ne code pour une et une seule protÃ©ine dans tous les cas.",
         "correct":False,"explanation":"Un seul gÃ¨ne peut coder pour plusieurs protÃ©ines grÃ¢ce Ã  l'Ã©pissage alternatif de l'ARN."},
        {"id":"655_6","type":"texte","question":"Comment appelle-t-on le processus de fabrication d'une protÃ©ine Ã  partir de l'ARN messager ?",
         "correct_answer":"traduction","explanation":"La traduction se dÃ©roule au niveau des ribosomes et convertit la sÃ©quence de nuclÃ©otides en sÃ©quence d'acides aminÃ©s."},
        {"id":"655_7","type":"qcm","question":"OÃ¹ se dÃ©roule la transcription de l'ADN en ARN messager chez les eucaryotes ?",
         "options":["Dans le cytoplasme","Dans les mitochondries","Dans le noyau","Dans les lysosomes"],
         "correct_option":"Dans le noyau","explanation":"La transcription a lieu dans le noyau oÃ¹ l'ADN est localisÃ©."},
        {"id":"655_8","type":"vrai-faux","question":"Tous les gÃ¨nes d'une cellule sont exprimÃ©s simultanÃ©ment.",
         "correct":False,"explanation":"L'expression gÃ©nique est rÃ©gulÃ©e : certains gÃ¨nes sont actifs ou inactifs selon le type cellulaire et les conditions."},
    ]),

    (656, "GÃ©nÃ©tique : biotechnologies et applications", "SVT", "3Ã¨me", [
        {"id":"656_1","type":"qcm","question":"Qu'est-ce qu'un organisme gÃ©nÃ©tiquement modifiÃ© (OGM) ?",
         "options":["Un organisme sÃ©lectionnÃ© naturellement","Un organisme dont le gÃ©nome a Ã©tÃ© modifiÃ© par insertion ou suppression de gÃ¨nes","Un organisme hybride nÃ© de deux espÃ¨ces diffÃ©rentes","Un organisme cultivÃ© sans produits chimiques"],
         "correct_option":"Un organisme dont le gÃ©nome a Ã©tÃ© modifiÃ© par insertion ou suppression de gÃ¨nes",
         "explanation":"Un OGM est obtenu par transgenÃ¨se ou par Ã©dition du gÃ©nome (ex. CRISPR-Cas9)."},
        {"id":"656_2","type":"vrai-faux","question":"Le diagnostic prÃ©natal permet de dÃ©tecter certaines anomalies chromosomiques avant la naissance.",
         "correct":True,"explanation":"L'amniocentÃ¨se ou la biopsie du trophoblaste permettent d'analyser le caryotype du fÅ“tus."},
        {"id":"656_3","type":"texte","question":"Comment appelle-t-on la technique qui amplifie des fragments d'ADN en laboratoire ?",
         "correct_answer":"PCR","explanation":"La PCR (Polymerase Chain Reaction) permet de multiplier un fragment d'ADN cible des millions de fois."},
        {"id":"656_4","type":"qcm","question":"L'insuline humaine produite par des bactÃ©ries OGM est utilisÃ©e pour traiter :",
         "options":["L'hypertension","Le diabÃ¨te","L'asthme","La drÃ©panocytose"],"correct_option":"Le diabÃ¨te",
         "explanation":"Des bactÃ©ries transformÃ©es par le gÃ¨ne de l'insuline humaine produisent cette hormone utilisÃ©e en thÃ©rapie du diabÃ¨te."},
        {"id":"656_5","type":"vrai-faux","question":"L'empreinte gÃ©nÃ©tique est utilisÃ©e dans les enquÃªtes judiciaires pour identifier un individu.",
         "correct":True,"explanation":"Les profils gÃ©nÃ©tiques (empreintes ADN) sont utilisÃ©s en mÃ©decine lÃ©gale pour les identifications."},
        {"id":"656_6","type":"texte","question":"Quel outil d'Ã©dition du gÃ©nome, basÃ© sur une protÃ©ine bactÃ©rienne, permet de couper l'ADN Ã  un endroit prÃ©cis ?",
         "correct_answer":"CRISPR-Cas9","explanation":"CRISPR-Cas9 utilise un guide ARN pour diriger la protÃ©ase Cas9 vers une sÃ©quence cible de l'ADN."},
        {"id":"656_7","type":"qcm","question":"Pourquoi l'identification d'une maladie gÃ©nÃ©tique par diagnostic prÃ©natal est-elle controversÃ©e Ã©thiquement ?",
         "options":["Car elle est toujours fausse","Car elle peut conduire Ã  des dÃ©cisions difficiles concernant la grossesse","Car elle est trop coÃ»teuse","Car elle modifie les gÃ¨nes du fÅ“tus"],
         "correct_option":"Car elle peut conduire Ã  des dÃ©cisions difficiles concernant la grossesse",
         "explanation":"Le diagnostic prÃ©natal soulÃ¨ve des questions Ã©thiques autour de l'interruption mÃ©dicale de grossesse."},
        {"id":"656_8","type":"vrai-faux","question":"Le clonage reproductif est autorisÃ© chez l'Ãªtre humain en France.",
         "correct":False,"explanation":"Le clonage reproductif humain est interdit en France par la loi de bioÃ©thique."},
    ]),

    # =========================================================
    # BLOC 2 â€“ Ã‰VOLUTION DES ÃŠTRES VIVANTS (657â€“662)
    # =========================================================
    (657, "Ã‰volution : thÃ©orie et preuves", "SVT", "3Ã¨me", [
        {"id":"657_1","type":"qcm","question":"Qui a proposÃ© la thÃ©orie de la sÃ©lection naturelle ?",
         "options":["Lamarck","Mendel","Darwin","Pasteur"],"correct_option":"Darwin",
         "explanation":"Charles Darwin a formulÃ© la thÃ©orie de la sÃ©lection naturelle dans 'De l'origine des espÃ¨ces' (1859)."},
        {"id":"657_2","type":"vrai-faux","question":"La sÃ©lection naturelle favorise les individus les mieux adaptÃ©s Ã  leur environnement.",
         "correct":True,"explanation":"Les individus dont les variations hÃ©rÃ©ditaires augmentent la survie et la reproduction transmettent davantage leurs gÃ¨nes."},
        {"id":"657_3","type":"texte","question":"Quel type de structure anatomique partageant la mÃªme origine mais remplissant des fonctions diffÃ©rentes illustre l'Ã©volution par homologie ?",
         "correct_answer":"organes homologues","explanation":"Les organes homologues (ex. aile de chauve-souris, bras humain, nageoire de baleine) montrent une origine commune malgrÃ© des fonctions diffÃ©rentes."},
        {"id":"657_4","type":"qcm","question":"Quelle discipline compare les sÃ©quences d'ADN pour Ã©tablir des liens de parentÃ© entre espÃ¨ces ?",
         "options":["Anatomie comparÃ©e","PhylogÃ©nÃ©tique molÃ©culaire","PalÃ©ontologie","Ã‰cologie"],
         "correct_option":"PhylogÃ©nÃ©tique molÃ©culaire","explanation":"La phylogÃ©nÃ©tique molÃ©culaire utilise les sÃ©quences gÃ©nÃ©tiques pour reconstruire l'histoire Ã©volutive."},
        {"id":"657_5","type":"vrai-faux","question":"Les fossiles sont des preuves de l'Ã©volution car ils montrent des formes de vie disparues.",
         "correct":True,"explanation":"Les fossiles tÃ©moignent de la biodiversitÃ© passÃ©e et des transformations au cours du temps gÃ©ologique."},
        {"id":"657_6","type":"texte","question":"Comment appelle-t-on la formation d'une nouvelle espÃ¨ce Ã  partir d'une espÃ¨ce ancestrale ?",
         "correct_answer":"spÃ©ciation","explanation":"La spÃ©ciation est le processus par lequel une population Ã©volue en une espÃ¨ce distincte."},
        {"id":"657_7","type":"qcm","question":"Selon Darwin, qu'est-ce qui provoque la variation hÃ©rÃ©ditaire dans une population ?",
         "options":["La volontÃ© des individus","Les mutations et la reproduction sexuÃ©e","L'alimentation","L'usage ou le non-usage des organes"],
         "correct_option":"Les mutations et la reproduction sexuÃ©e",
         "explanation":"La variabilitÃ© gÃ©nÃ©tique hÃ©rÃ©ditaire vient des mutations et du brassage lors de la reproduction sexuÃ©e."},
        {"id":"657_8","type":"vrai-faux","question":"L'Ã©volution est dirigÃ©e vers un objectif prÃ©cis de perfection.",
         "correct":False,"explanation":"L'Ã©volution est un processus sans direction ni finalitÃ© ; elle rÃ©sulte de la sÃ©lection naturelle et du hasard."},
    ]),

    (658, "Ã‰volution : classification phylogÃ©nÃ©tique", "SVT", "3Ã¨me", [
        {"id":"658_1","type":"qcm","question":"Sur quel critÃ¨re repose principalement la classification phylogÃ©nÃ©tique ?",
         "options":["La ressemblance morphologique","Les liens de parentÃ© Ã©volutifs","La taille des organismes","Le rÃ©gime alimentaire"],
         "correct_option":"Les liens de parentÃ© Ã©volutifs",
         "explanation":"La classification phylogÃ©nÃ©tique regroupe les Ãªtres vivants selon leur histoire Ã©volutive commune."},
        {"id":"658_2","type":"vrai-faux","question":"Les baleines sont plus proches des poissons que des mammifÃ¨res terrestres selon la classification phylogÃ©nÃ©tique.",
         "correct":False,"explanation":"Les baleines sont des mammifÃ¨res ; elles partagent un ancÃªtre commun plus rÃ©cent avec les artiodactyles (hippopotames) qu'avec les poissons."},
        {"id":"658_3","type":"texte","question":"Comment appelle-t-on un groupe rÃ©unissant un ancÃªtre commun et tous ses descendants ?",
         "correct_answer":"clade","explanation":"Un clade (ou groupe monophylÃ©tique) inclut un ancÃªtre commun et la totalitÃ© de sa descendance."},
        {"id":"658_4","type":"qcm","question":"Quel caractÃ¨re dÃ©rivÃ© (synapomorphie) est partagÃ© par tous les vertÃ©brÃ©s ?",
         "options":["Les ailes","La colonne vertÃ©brale","Les poumons","Les pattes"],
         "correct_option":"La colonne vertÃ©brale","explanation":"La prÃ©sence d'une colonne vertÃ©brale est un caractÃ¨re dÃ©rivÃ© commun Ã  tous les vertÃ©brÃ©s."},
        {"id":"658_5","type":"vrai-faux","question":"Les champignons font partie du rÃ¨gne animal.",
         "correct":False,"explanation":"Les champignons constituent leur propre rÃ¨gne (Fungi), distinct des animaux, des plantes et des protistes."},
        {"id":"658_6","type":"texte","question":"Quel outil graphique reprÃ©sente les relations de parentÃ© entre espÃ¨ces sous forme d'arbre ?",
         "correct_answer":"arbre phylogÃ©nÃ©tique","explanation":"L'arbre phylogÃ©nÃ©tique (cladogramme) reprÃ©sente les relations Ã©volutives entre espÃ¨ces."},
        {"id":"658_7","type":"qcm","question":"Parmi les vertÃ©brÃ©s suivants, lequel est le plus Ã©troitement apparentÃ© Ã  l'Ãªtre humain ?",
         "options":["La grenouille","Le requin","Le chimpanzÃ©","Le pigeon"],"correct_option":"Le chimpanzÃ©",
         "explanation":"L'Ãªtre humain et le chimpanzÃ© partagent environ 98,7% de leur ADN et un ancÃªtre commun rÃ©cent (~6 millions d'annÃ©es)."},
        {"id":"658_8","type":"vrai-faux","question":"L'homologie entre organes peut aussi Ãªtre dÃ©tectÃ©e au niveau molÃ©culaire.",
         "correct":True,"explanation":"Les sÃ©quences d'ADN et de protÃ©ines homologues confirment les liens de parentÃ© identifiÃ©s par l'anatomie comparÃ©e."},
    ]),

    (659, "Ã‰volution : adaptation et sÃ©lection naturelle", "SVT", "3Ã¨me", [
        {"id":"659_1","type":"qcm","question":"Qu'est-ce qu'une adaptation Ã©volutive ?",
         "options":["Un apprentissage individuel","Un caractÃ¨re hÃ©rÃ©ditaire augmentant la valeur sÃ©lective","Une modification du comportement au cours de la vie","Un organe inutile"],
         "correct_option":"Un caractÃ¨re hÃ©rÃ©ditaire augmentant la valeur sÃ©lective",
         "explanation":"Une adaptation est un caractÃ¨re hÃ©rÃ©ditaire qui amÃ©liore la survie ou la reproduction d'un individu dans son environnement."},
        {"id":"659_2","type":"vrai-faux","question":"La rÃ©sistance des bactÃ©ries aux antibiotiques est un exemple de sÃ©lection naturelle.",
         "correct":True,"explanation":"Les bactÃ©ries portant des mutations de rÃ©sistance survivent et se reproduisent prÃ©fÃ©rentiellement en prÃ©sence d'antibiotiques."},
        {"id":"659_3","type":"texte","question":"Quel est le terme qui dÃ©signe la capacitÃ© d'un individu Ã  survivre et Ã  se reproduire dans son milieu ?",
         "correct_answer":"valeur sÃ©lective","explanation":"La valeur sÃ©lective (fitness) mesure la contribution relative d'un individu Ã  la gÃ©nÃ©ration suivante."},
        {"id":"659_4","type":"qcm","question":"Quel mÃ©canisme Ã©volutif non sÃ©lectif peut modifier la frÃ©quence des allÃ¨les dans une petite population par hasard ?",
         "options":["SÃ©lection naturelle","DÃ©rive gÃ©nÃ©tique","Migration","Mutation directe"],
         "correct_option":"DÃ©rive gÃ©nÃ©tique","explanation":"La dÃ©rive gÃ©nÃ©tique est un processus alÃ©atoire particuliÃ¨rement important dans les petites populations."},
        {"id":"659_5","type":"vrai-faux","question":"Tous les individus d'une population sont identiques avant la sÃ©lection naturelle.",
         "correct":False,"explanation":"La variabilitÃ© gÃ©nÃ©tique est le substrat nÃ©cessaire Ã  la sÃ©lection naturelle."},
        {"id":"659_6","type":"texte","question":"Comment appelle-t-on la capacitÃ© d'une espÃ¨ce Ã  modifier son phÃ©notype en rÃ©ponse Ã  l'environnement sans changement gÃ©nÃ©tique ?",
         "correct_answer":"plasticitÃ© phÃ©notypique","explanation":"La plasticitÃ© phÃ©notypique permet Ã  un gÃ©notype de produire des phÃ©notypes diffÃ©rents selon les conditions environnementales."},
        {"id":"659_7","type":"qcm","question":"Quel exemple illustre la coÃ©volution entre deux espÃ¨ces ?",
         "options":["La relation fleur-pollinisateur","La prÃ©dation d'un lapin par un renard","La compÃ©tition entre deux arbres","La migration des oiseaux"],
         "correct_option":"La relation fleur-pollinisateur","explanation":"Les fleurs et leurs pollinisateurs ont co-Ã©voluÃ©, chaque espÃ¨ce influenÃ§ant l'Ã©volution de l'autre."},
        {"id":"659_8","type":"vrai-faux","question":"La dÃ©rive gÃ©nÃ©tique peut conduire Ã  la fixation d'un allÃ¨le dÃ©lÃ©tÃ¨re dans une petite population.",
         "correct":True,"explanation":"Par hasard, un allÃ¨le dÃ©favorable peut atteindre une frÃ©quence de 100% dans une petite population."},
    ]),

    (660, "Ã‰volution : de la cellule Ã  l'Ãªtre humain", "SVT", "3Ã¨me", [
        {"id":"660_1","type":"qcm","question":"Depuis quand estime-t-on l'apparition de la vie sur Terre ?",
         "options":["Il y a 6 000 ans","Il y a 65 millions d'annÃ©es","Il y a 3,5 milliards d'annÃ©es","Il y a 500 millions d'annÃ©es"],
         "correct_option":"Il y a 3,5 milliards d'annÃ©es",
         "explanation":"Les traces les plus anciennes de vie (stromatolites) sont datÃ©es d'environ 3,5 milliards d'annÃ©es."},
        {"id":"660_2","type":"vrai-faux","question":"Les premiers organismes vivants Ã©taient pluricellulaires.",
         "correct":False,"explanation":"Les premiers Ãªtres vivants Ã©taient des organismes unicellulaires prokaryotes."},
        {"id":"660_3","type":"texte","question":"Comment appelle-t-on les grandes extinctions massives ayant Ã©liminÃ© plus de 75% des espÃ¨ces ?",
         "correct_answer":"extinctions de masse","explanation":"Cinq grandes extinctions de masse ont Ã©tÃ© identifiÃ©es, dont celle du CrÃ©tacÃ©-PalÃ©ogÃ¨ne il y a 66 Ma."},
        {"id":"660_4","type":"qcm","question":"Quand a eu lieu la sÃ©paration entre la lignÃ©e des chimpanzÃ©s et la lignÃ©e humaine ?",
         "options":["Il y a 300 000 ans","Il y a 6 millions d'annÃ©es","Il y a 65 millions d'annÃ©es","Il y a 200 000 ans"],
         "correct_option":"Il y a 6 millions d'annÃ©es",
         "explanation":"Les donnÃ©es gÃ©nÃ©tiques et palÃ©ontologiques situent la divergence hominine-chimpanzÃ© il y a 6 Ã  8 millions d'annÃ©es."},
        {"id":"660_5","type":"vrai-faux","question":"L'Homo sapiens est la seule espÃ¨ce du genre Homo Ã  avoir jamais existÃ©.",
         "correct":False,"explanation":"Le genre Homo comprend plusieurs espÃ¨ces disparues : H. habilis, H. erectus, H. neanderthalensis, etc."},
        {"id":"660_6","type":"texte","question":"Quel caractÃ¨re anatomique distingue les hominidÃ©s bipÃ¨des des autres grands singes ?",
         "correct_answer":"bipÃ©die","explanation":"La bipÃ©die permanente est un caractÃ¨re dÃ©rivÃ© des hominidÃ©s, distinguant la lignÃ©e humaine de celle des autres primates."},
        {"id":"660_7","type":"qcm","question":"Quel fossile cÃ©lÃ¨bre, dÃ©couvert en Ã‰thiopie en 1974, est un ancÃªtre de l'Ãªtre humain ?",
         "options":["Lucy (Australopithecus afarensis)","Homo habilis","Homo neanderthalensis","Ardipithecus"],
         "correct_option":"Lucy (Australopithecus afarensis)","explanation":"Lucy est un squelette fossile d'Australopithecus afarensis vieux d'environ 3,2 millions d'annÃ©es."},
        {"id":"660_8","type":"vrai-faux","question":"Homo sapiens et Homo neanderthalensis ont coexistÃ© et se sont hybridÃ©s.",
         "correct":True,"explanation":"Les Ã©tudes gÃ©nÃ©tiques montrent qu'Homo sapiens et Homo neanderthalensis se sont croisÃ©s, lÃ©gant environ 1 Ã  4% d'ADN nÃ©andertalien aux non-Africains actuels."},
    ]),

    (661, "Ã‰volution : biodiversitÃ© et conservation", "SVT", "3Ã¨me", [
        {"id":"661_1","type":"qcm","question":"Combien d'espÃ¨ces connues coexistent sur Terre environ ?",
         "options":["10 000","500 000","8 Ã  10 millions","1 milliard"],"correct_option":"8 Ã  10 millions",
         "explanation":"On estime entre 8 et 10 millions d'espÃ¨ces sur Terre, dont seulement 1,5 Ã  2 millions ont Ã©tÃ© dÃ©crites."},
        {"id":"661_2","type":"vrai-faux","question":"L'Ã©rosion de la biodiversitÃ© actuelle est principalement due aux activitÃ©s humaines.",
         "correct":True,"explanation":"La destruction des habitats, la surexploitation, la pollution et le changement climatique sont les principales menaces anthropiques."},
        {"id":"661_3","type":"texte","question":"Comment appelle-t-on une espÃ¨ce dont la disparition aurait un impact majeur sur l'Ã©cosystÃ¨me ?",
         "correct_answer":"espÃ¨ce clÃ© de voÃ»te","explanation":"Une espÃ¨ce clÃ© de voÃ»te joue un rÃ´le disproportionnÃ© dans son Ã©cosystÃ¨me par rapport Ã  son abondance."},
        {"id":"661_4","type":"qcm","question":"Quel document international, adoptÃ© Ã  Rio en 1992, vise Ã  protÃ©ger la biodiversitÃ© ?",
         "options":["Protocole de Kyoto","Convention sur la diversitÃ© biologique","Accord de Paris","TraitÃ© de Lisbonne"],
         "correct_option":"Convention sur la diversitÃ© biologique","explanation":"La CDB adoptÃ©e au Sommet de Rio en 1992 est le principal accord international pour la conservation de la biodiversitÃ©."},
        {"id":"661_5","type":"vrai-faux","question":"Les espÃ¨ces invasives peuvent menacer la biodiversitÃ© locale.",
         "correct":True,"explanation":"Les espÃ¨ces exotiques envahissantes entrent en compÃ©tition avec les espÃ¨ces indigÃ¨nes et peuvent provoquer leur extinction."},
        {"id":"661_6","type":"texte","question":"Quel outil classe les espÃ¨ces selon leur risque d'extinction (Ã‰teint, MenacÃ©, VulnÃ©rable...) ?",
         "correct_answer":"liste rouge de l'UICN","explanation":"La Liste Rouge de l'UICN (Union internationale pour la conservation de la nature) Ã©value le statut de conservation des espÃ¨ces."},
        {"id":"661_7","type":"qcm","question":"Que dÃ©signe le terme 'point chaud de biodiversitÃ©' (hotspot) ?",
         "options":["Une rÃ©gion trÃ¨s chaude","Une zone Ã  forte diversitÃ© d'espÃ¨ces endÃ©miques sous forte pression","Un lieu oÃ¹ la biodiversitÃ© est nulle","Un pays sans espÃ¨ces menacÃ©es"],
         "correct_option":"Une zone Ã  forte diversitÃ© d'espÃ¨ces endÃ©miques sous forte pression",
         "explanation":"Les hotspots abritent de nombreuses espÃ¨ces endÃ©miques et ont perdu plus de 70% de leur vÃ©gÃ©tation d'origine."},
        {"id":"661_8","type":"vrai-faux","question":"La crÃ©ation de rÃ©serves naturelles est inutile pour protÃ©ger la biodiversitÃ©.",
         "correct":False,"explanation":"Les aires protÃ©gÃ©es sont essentielles pour conserver les habitats et freiner l'extinction des espÃ¨ces."},
    ]),

    (662, "Ã‰volution : palÃ©ontologie et datation", "SVT", "3Ã¨me", [
        {"id":"662_1","type":"qcm","question":"Qu'est-ce qu'un fossile ?",
         "options":["Un organisme vivant trÃ¨s ancien","Les restes ou traces conservÃ©s d'un organisme passÃ©","Un minÃ©ral cristallisÃ©","Un rocher sÃ©dimentaire"],
         "correct_option":"Les restes ou traces conservÃ©s d'un organisme passÃ©",
         "explanation":"Un fossile est tout vestige (os, empreinte, trace) d'un organisme ayant vÃ©cu dans le passÃ© gÃ©ologique."},
        {"id":"662_2","type":"vrai-faux","question":"La datation radiomÃ©trique utilise la dÃ©sintÃ©gration d'Ã©lÃ©ments radioactifs pour estimer l'Ã¢ge des roches.",
         "correct":True,"explanation":"Des isotopes comme le carbone 14 (courtes pÃ©riodes) ou le potassium-argon (longues pÃ©riodes) permettent de dater les fossiles."},
        {"id":"662_3","type":"texte","question":"Quel isotope radioactif est utilisÃ© pour dater des restes organiques de moins de 50 000 ans ?",
         "correct_answer":"carbone 14","explanation":"Le carbone 14 (Â¹â´C) a une demi-vie d'environ 5 730 ans, idÃ©ale pour dater les matiÃ¨res organiques rÃ©centes."},
        {"id":"662_4","type":"qcm","question":"Quel type de roche conserve le mieux les fossiles ?",
         "options":["Roches magmatiques","Roches mÃ©tamorphiques","Roches sÃ©dimentaires","Roches volcaniques"],
         "correct_option":"Roches sÃ©dimentaires","explanation":"Les roches sÃ©dimentaires, formÃ©es par accumulation de sÃ©diments, emprisonnent et conservent les restes organiques."},
        {"id":"662_5","type":"vrai-faux","question":"Plus un fossile est profond dans les couches sÃ©dimentaires, plus il est rÃ©cent.",
         "correct":False,"explanation":"Le principe de superposition : les couches supÃ©rieures sont plus rÃ©centes, les couches infÃ©rieures plus anciennes."},
        {"id":"662_6","type":"texte","question":"Comment appelle-t-on les fossiles caractÃ©ristiques d'une Ã©poque gÃ©ologique prÃ©cise permettant de dater les couches qui les contiennent ?",
         "correct_answer":"fossiles stratigraphiques","explanation":"Les fossiles stratigraphiques (ou fossiles directeurs) sont abondants, Ã  large rÃ©partition et Ã  courte durÃ©e d'existence."},
        {"id":"662_7","type":"qcm","question":"Quelle est la demi-vie approximative du carbone 14 ?",
         "options":["730 ans","5 730 ans","45 000 ans","1 300 millions d'annÃ©es"],"correct_option":"5 730 ans",
         "explanation":"La demi-vie du Â¹â´C est de 5 730 ans Â± 40 ans."},
        {"id":"662_8","type":"vrai-faux","question":"L'existence de fossiles de transition valide la thÃ©orie de l'Ã©volution.",
         "correct":True,"explanation":"Les fossiles de transition (ex. Archaeopteryx entre reptiles et oiseaux) montrent des formes intermÃ©diaires entre groupes."},
    ]),

    # =========================================================
    # BLOC 3 â€“ IMMUNOLOGIE ET SANTÃ‰ (663â€“668)
    # =========================================================
    (663, "Immunologie : dÃ©fenses de l'organisme", "SVT", "3Ã¨me", [
        {"id":"663_1","type":"qcm","question":"Quelle est la premiÃ¨re ligne de dÃ©fense de l'organisme contre les agents pathogÃ¨nes ?",
         "options":["Les anticorps","Les barriÃ¨res naturelles (peau, muqueuses)","Les lymphocytes T","Les phagocytes"],
         "correct_option":"Les barriÃ¨res naturelles (peau, muqueuses)",
         "explanation":"La peau et les muqueuses constituent la premiÃ¨re barriÃ¨re physique contre l'entrÃ©e des agents pathogÃ¨nes."},
        {"id":"663_2","type":"vrai-faux","question":"L'inflammation est une rÃ©action immunitaire non spÃ©cifique.",
         "correct":True,"explanation":"L'inflammation (rougeur, chaleur, douleur, gonflement) est une rÃ©ponse innÃ©e non spÃ©cifique Ã  toute agression."},
        {"id":"663_3","type":"texte","question":"Comment appelle-t-on les cellules qui 'avalent' et dÃ©truisent les microbes par phagocytose ?",
         "correct_answer":"phagocytes","explanation":"Les phagocytes (macrophages, neutrophiles) ingÃ¨rent et digÃ¨rent les agents pathogÃ¨nes par phagocytose."},
        {"id":"663_4","type":"qcm","question":"Quelle cellule du systÃ¨me immunitaire produit les anticorps ?",
         "options":["Lymphocyte T","Globule rouge","Lymphocyte B / plasmocyte","Macrophage"],
         "correct_option":"Lymphocyte B / plasmocyte","explanation":"Les lymphocytes B se diffÃ©rencient en plasmocytes qui sÃ©crÃ¨tent des anticorps spÃ©cifiques."},
        {"id":"663_5","type":"vrai-faux","question":"Les anticorps peuvent reconnaÃ®tre et neutraliser n'importe quel antigÃ¨ne sans spÃ©cificitÃ©.",
         "correct":False,"explanation":"Chaque anticorps est spÃ©cifique d'un antigÃ¨ne dÃ©terminÃ© ; la complÃ©mentaritÃ© de forme est indispensable."},
        {"id":"663_6","type":"texte","question":"Quel terme dÃ©signe les substances Ã©trangÃ¨res qui dÃ©clenchent une rÃ©ponse immunitaire spÃ©cifique ?",
         "correct_answer":"antigÃ¨nes","explanation":"Un antigÃ¨ne est toute molÃ©cule (protÃ©ine, polysaccharide) reconnue comme Ã©trangÃ¨re par le systÃ¨me immunitaire."},
        {"id":"663_7","type":"qcm","question":"Quelle est la diffÃ©rence entre immunitÃ© innÃ©e et immunitÃ© acquise ?",
         "options":["Aucune diffÃ©rence","L'immunitÃ© innÃ©e est non spÃ©cifique et rapide ; l'immunitÃ© acquise est spÃ©cifique et mÃ©morisÃ©e","L'immunitÃ© innÃ©e est produite par vaccination","L'immunitÃ© acquise est hÃ©rÃ©ditaire"],
         "correct_option":"L'immunitÃ© innÃ©e est non spÃ©cifique et rapide ; l'immunitÃ© acquise est spÃ©cifique et mÃ©morisÃ©e",
         "explanation":"L'immunitÃ© innÃ©e rÃ©pond immÃ©diatement sans mÃ©moire ; l'immunitÃ© adaptative est spÃ©cifique et gÃ©nÃ¨re une mÃ©moire immunologique."},
        {"id":"663_8","type":"vrai-faux","question":"Les lymphocytes T cytotoxiques dÃ©truisent directement les cellules infectÃ©es.",
         "correct":True,"explanation":"Les LT cytotoxiques (CD8+) reconnaissent et dÃ©truisent les cellules infectÃ©es par des virus ou cancÃ©reuses."},
    ]),

    (664, "Immunologie : vaccination et sÃ©rum", "SVT", "3Ã¨me", [
        {"id":"664_1","type":"qcm","question":"Quel est le principe de la vaccination ?",
         "options":["Introduire directement des anticorps dans l'organisme","Stimuler l'immunitÃ© acquise en prÃ©sentant un antigÃ¨ne inoffensif","DÃ©truire les microbes avec des antibiotiques","Augmenter la fiÃ¨vre pour tuer les microbes"],
         "correct_option":"Stimuler l'immunitÃ© acquise en prÃ©sentant un antigÃ¨ne inoffensif",
         "explanation":"Un vaccin contient des antigÃ¨nes (attÃ©nuÃ©s, inactivÃ©s ou fragments) qui stimulent l'immunitÃ© et crÃ©ent une mÃ©moire immunologique sans provoquer la maladie."},
        {"id":"664_2","type":"vrai-faux","question":"Un vaccin Ã  ARN messager introduit directement des anticorps dans l'organisme.",
         "correct":False,"explanation":"Le vaccin ARNm fournit les instructions pour que les cellules fabriquent elles-mÃªmes l'antigÃ¨ne, stimulant ainsi l'immunitÃ©."},
        {"id":"664_3","type":"texte","question":"Comment appelle-t-on l'immunitÃ© de groupe atteinte lorsqu'une grande proportion de la population est vaccinÃ©e ?",
         "correct_answer":"immunitÃ© collective","explanation":"L'immunitÃ© collective (ou immunitÃ© grÃ©gaire) protÃ¨ge indirectement les individus non immunisÃ©s en rÃ©duisant la circulation du pathogÃ¨ne."},
        {"id":"664_4","type":"qcm","question":"Quelle est la diffÃ©rence entre un vaccin et un sÃ©rum ?",
         "options":["Aucune diffÃ©rence","Le vaccin prÃ©vient la maladie ; le sÃ©rum guÃ©rit aprÃ¨s l'infection","Le sÃ©rum est pris oralement ; le vaccin est injectÃ©","Le vaccin contient des bactÃ©ries vivantes"],
         "correct_option":"Le vaccin prÃ©vient la maladie ; le sÃ©rum guÃ©rit aprÃ¨s l'infection",
         "explanation":"La vaccination crÃ©e une mÃ©moire immunitaire (immunitÃ© active) ; le sÃ©rum apporte des anticorps prÃ©formÃ©s (immunitÃ© passive) pour un traitement d'urgence."},
        {"id":"664_5","type":"vrai-faux","question":"L'immunitÃ© passive transmise par les anticorps maternels est durable toute la vie.",
         "correct":False,"explanation":"L'immunitÃ© passive est temporaire car les anticorps reÃ§us passivement sont progressivement Ã©liminÃ©s."},
        {"id":"664_6","type":"texte","question":"Quel scientifique a dÃ©veloppÃ© le premier vaccin (contre la variole) ?",
         "correct_answer":"Edward Jenner","explanation":"Edward Jenner a dÃ©veloppÃ© le premier vaccin en 1796 en utilisant la vaccine (variole de vache) pour immuniser contre la variole."},
        {"id":"664_7","type":"qcm","question":"Pourquoi doit-on se faire vacciner contre la grippe chaque annÃ©e ?",
         "options":["Car les vaccins perdent leur efficacitÃ© aprÃ¨s un an","Car le virus de la grippe mute rapidement","Car les anticorps disparaissent aprÃ¨s six mois","Car la grippe est causÃ©e par une bactÃ©rie"],
         "correct_option":"Car le virus de la grippe mute rapidement","explanation":"Le virus influenza prÃ©sente une forte variabilitÃ© antigÃ©nique (mutations et rÃ©assortiments gÃ©nÃ©tiques) nÃ©cessitant une adaptation annuelle du vaccin."},
        {"id":"664_8","type":"vrai-faux","question":"Les antibiotiques sont efficaces contre les infections virales.",
         "correct":False,"explanation":"Les antibiotiques ciblent les bactÃ©ries ; ils sont inefficaces contre les virus. Les antiviraux sont utilisÃ©s pour les infections virales."},
    ]),

    (665, "Immunologie : SIDA et VIH", "SVT", "3Ã¨me", [
        {"id":"665_1","type":"qcm","question":"Quel type de microorganisme est le VIH ?",
         "options":["BactÃ©rie","Parasite","Virus","Champignon"],"correct_option":"Virus",
         "explanation":"Le VIH (Virus de l'ImmunodÃ©ficience Humaine) est un rÃ©trovirus qui attaque le systÃ¨me immunitaire."},
        {"id":"665_2","type":"vrai-faux","question":"Le VIH dÃ©truit principalement les lymphocytes T CD4+, affaiblissant l'immunitÃ©.",
         "correct":True,"explanation":"Le VIH infecte et dÃ©truit les LT4 (CD4+), rÃ©duisant la capacitÃ© de l'organisme Ã  rÃ©pondre aux infections."},
        {"id":"665_3","type":"texte","question":"Quel est le nom complet de la maladie causÃ©e par le VIH lorsque le systÃ¨me immunitaire est gravement affaibli ?",
         "correct_answer":"SIDA","explanation":"Le SIDA (Syndrome d'ImmunodÃ©ficience Acquise) est le stade avancÃ© de l'infection par le VIH, caractÃ©risÃ© par une immunodÃ©pression sÃ©vÃ¨re."},
        {"id":"665_4","type":"qcm","question":"Par quelles voies le VIH se transmet-il ?",
         "options":["Par l'air et les Ã©ternuements","Par les relations sexuelles non protÃ©gÃ©es, le sang contaminÃ© et la transmission mÃ¨re-enfant","Par le simple contact physique","Par les insectes piqueurs"],
         "correct_option":"Par les relations sexuelles non protÃ©gÃ©es, le sang contaminÃ© et la transmission mÃ¨re-enfant",
         "explanation":"Le VIH se transmet par voie sexuelle, sanguine et verticale (mÃ¨re Ã  enfant lors de la grossesse, accouchement ou allaitement)."},
        {"id":"665_5","type":"vrai-faux","question":"Il existe un vaccin efficace contre le VIH approuvÃ© Ã  l'Ã©chelle mondiale.",
         "correct":False,"explanation":"MalgrÃ© des dÃ©cennies de recherche, aucun vaccin prÃ©ventif efficace contre le VIH n'est encore disponible."},
        {"id":"665_6","type":"texte","question":"Comment appelle-t-on le traitement mÃ©dicamenteux qui maintient la charge virale du VIH trÃ¨s basse ?",
         "correct_answer":"traitement antirÃ©troviral","explanation":"Les antirÃ©troviraux (ARV) bloquent la rÃ©plication du VIH et permettent aux personnes sÃ©ropositives de vivre longtemps en bonne santÃ©."},
        {"id":"665_7","type":"qcm","question":"Quel test permet de dÃ©tecter la prÃ©sence d'anticorps contre le VIH dans le sang ?",
         "options":["NumÃ©ration formule sanguine","Test ELISA (sÃ©rologie VIH)","Scanner","Biopsie"],
         "correct_option":"Test ELISA (sÃ©rologie VIH)","explanation":"Le test ELISA dÃ©tecte les anticorps anti-VIH dans le sang ; un rÃ©sultat positif est confirmÃ© par un Western blot."},
        {"id":"665_8","type":"vrai-faux","question":"Une personne sÃ©ropositive sous traitement ARV efficace peut avoir une charge virale indÃ©tectable.",
         "correct":True,"explanation":"Un traitement ARV bien suivi peut ramener la charge virale Ã  un niveau indÃ©tectable, prÃ©servant le systÃ¨me immunitaire et rÃ©duisant drastiquement la transmissibilitÃ©."},
    ]),

    (666, "SantÃ© : microbiotes et hygiÃ¨ne", "SVT", "3Ã¨me", [
        {"id":"666_1","type":"qcm","question":"Que dÃ©signe le terme 'microbiote intestinal' ?",
         "options":["Les bactÃ©ries pathogÃ¨nes du tube digestif","L'ensemble des micro-organismes vivant dans l'intestin","Les globules blancs de la paroi intestinale","Les enzymes digestives"],
         "correct_option":"L'ensemble des micro-organismes vivant dans l'intestin",
         "explanation":"Le microbiote intestinal (ou flore intestinale) est composÃ© de milliards de bactÃ©ries, virus et champignons bÃ©nÃ©fiques pour l'hÃ´te."},
        {"id":"666_2","type":"vrai-faux","question":"Le lavage des mains rÃ©duit la transmission des maladies infectieuses.",
         "correct":True,"explanation":"Le lavage des mains avec du savon est l'un des gestes les plus efficaces pour prÃ©venir la transmission de nombreux pathogÃ¨nes."},
        {"id":"666_3","type":"texte","question":"Comment appelle-t-on les micro-organismes qui vivent en symbiose avec l'Ãªtre humain sans lui causer de maladie ?",
         "correct_answer":"flore commensale","explanation":"La flore commensale (ou microbiote) vit en harmonie avec l'hÃ´te et joue des rÃ´les essentiels (digestion, immunitÃ©, protection)."},
        {"id":"666_4","type":"qcm","question":"Quel est le rÃ´le du microbiote intestinal dans l'immunitÃ© ?",
         "options":["Il affaiblit le systÃ¨me immunitaire","Il stimule et rÃ©gule le dÃ©veloppement du systÃ¨me immunitaire","Il produit des anticorps directement","Il dÃ©truit les lymphocytes"],
         "correct_option":"Il stimule et rÃ©gule le dÃ©veloppement du systÃ¨me immunitaire",
         "explanation":"Le microbiote intestinal interagit avec les cellules immunitaires de la paroi intestinale et contribue Ã  la maturation du systÃ¨me immunitaire."},
        {"id":"666_5","type":"vrai-faux","question":"L'utilisation excessive d'antibiotiques est sans effet sur le microbiote.",
         "correct":False,"explanation":"Les antibiotiques Ã  large spectre Ã©liminent aussi des bactÃ©ries bÃ©nÃ©fiques du microbiote, pouvant favoriser des infections opportunistes."},
        {"id":"666_6","type":"texte","question":"Comment appelle-t-on le phÃ©nomÃ¨ne par lequel le microbiote normal empÃªche les agents pathogÃ¨nes de coloniser l'hÃ´te ?",
         "correct_answer":"effet barriÃ¨re","explanation":"L'effet barriÃ¨re du microbiote empÃªche les agents pathogÃ¨nes de coloniser les muqueuses en occupant les niches Ã©cologiques disponibles."},
        {"id":"666_7","type":"qcm","question":"Quel organe contient la plus grande densitÃ© de micro-organismes du microbiote humain ?",
         "options":["Estomac","Intestin grÃªle","CÃ´lon","Bouche"],"correct_option":"CÃ´lon",
         "explanation":"Le cÃ´lon (gros intestin) abrite la plus grande concentration de micro-organismes du corps humain."},
        {"id":"666_8","type":"vrai-faux","question":"Le microbiote d'une personne est identique tout au long de sa vie.",
         "correct":False,"explanation":"Le microbiote Ã©volue avec l'Ã¢ge, le rÃ©gime alimentaire, les mÃ©dicaments, le mode de vie et l'environnement."},
    ]),

    (667, "SantÃ© : cancers et facteurs de risque", "SVT", "3Ã¨me", [
        {"id":"667_1","type":"qcm","question":"Qu'est-ce qu'un cancer ?",
         "options":["Une infection virale","Une prolifÃ©ration incontrÃ´lÃ©e de cellules anormales","Une carence nutritionnelle","Une maladie auto-immune"],
         "correct_option":"Une prolifÃ©ration incontrÃ´lÃ©e de cellules anormales",
         "explanation":"Un cancer rÃ©sulte de mutations qui altÃ¨rent les mÃ©canismes de contrÃ´le de la division cellulaire."},
        {"id":"667_2","type":"vrai-faux","question":"Le tabac est le principal facteur de risque du cancer du poumon.",
         "correct":True,"explanation":"Environ 85% des cancers du poumon sont liÃ©s Ã  la consommation de tabac."},
        {"id":"667_3","type":"texte","question":"Comment appelle-t-on la propagation de cellules cancÃ©reuses Ã  d'autres organes via le sang ou la lymphe ?",
         "correct_answer":"mÃ©tastases","explanation":"Les mÃ©tastases sont des foyers cancÃ©reux secondaires formÃ©s par migration de cellules tumorales."},
        {"id":"667_4","type":"qcm","question":"Quel virus peut provoquer un cancer du col de l'utÃ©rus ?",
         "options":["VIH","VHB (hÃ©patite B)","HPV (papillomavirus humain)","VHS (herpÃ¨s)"],
         "correct_option":"HPV (papillomavirus humain)","explanation":"Certains sÃ©rotypes du HPV (notamment HPV 16 et 18) sont oncogÃ¨nes et responsables de la majoritÃ© des cancers du col de l'utÃ©rus."},
        {"id":"667_5","type":"vrai-faux","question":"L'alcool est un facteur de risque cancÃ©rigÃ¨ne reconnu.",
         "correct":True,"explanation":"L'alcool est classÃ© cancÃ©rigÃ¨ne de groupe 1 par le CIRC ; il augmente le risque de cancers de la bouche, du foie, du sein, etc."},
        {"id":"667_6","type":"texte","question":"Quel terme dÃ©signe une tumeur dont les cellules ne se propagent pas et restent localisÃ©es ?",
         "correct_answer":"tumeur bÃ©nigne","explanation":"Une tumeur bÃ©nigne ne mÃ©tastase pas ; contrairement Ã  une tumeur maligne (cancer), elle n'envahit pas les tissus voisins."},
        {"id":"667_7","type":"qcm","question":"Quel traitement du cancer utilise des rayonnements ionisants pour dÃ©truire les cellules tumorales ?",
         "options":["ChimiothÃ©rapie","RadiothÃ©rapie","ImmunothÃ©rapie","HormonothÃ©rapie"],
         "correct_option":"RadiothÃ©rapie","explanation":"La radiothÃ©rapie utilise des rayonnements ionisants ciblÃ©s pour dÃ©truire l'ADN des cellules cancÃ©reuses."},
        {"id":"667_8","type":"vrai-faux","question":"Certains cancers peuvent Ãªtre dÃ©tectÃ©s tÃ´t par des dÃ©pistages, ce qui amÃ©liore le pronostic.",
         "correct":True,"explanation":"Le dÃ©pistage prÃ©coce (mammographie, coloscopie, frottis) permet de dÃ©tecter des cancers Ã  des stades guÃ©rissables."},
    ]),

    (668, "SantÃ© : nutrition et maladies nutritionnelles", "SVT", "3Ã¨me", [
        {"id":"668_1","type":"qcm","question":"Quelle maladie est liÃ©e Ã  un excÃ¨s de cholestÃ©rol LDL dans les artÃ¨res ?",
         "options":["DiabÃ¨te de type 1","AthÃ©rosclÃ©rose","OstÃ©oporose","AnÃ©mie"],
         "correct_option":"AthÃ©rosclÃ©rose","explanation":"L'athÃ©rosclÃ©rose est caractÃ©risÃ©e par le dÃ©pÃ´t de plaques de cholestÃ©rol dans les artÃ¨res, augmentant le risque cardiovasculaire."},
        {"id":"668_2","type":"vrai-faux","question":"Le diabÃ¨te de type 2 est liÃ© Ã  une rÃ©sistance Ã  l'insuline souvent associÃ©e Ã  l'obÃ©sitÃ©.",
         "correct":True,"explanation":"Le diabÃ¨te de type 2 rÃ©sulte d'une rÃ©sistance des cellules Ã  l'insuline, frÃ©quemment associÃ©e au surpoids et Ã  la sÃ©dentaritÃ©."},
        {"id":"668_3","type":"texte","question":"Quel organe sÃ©crÃ¨te l'insuline pour rÃ©guler la glycÃ©mie ?",
         "correct_answer":"pancrÃ©as","explanation":"Le pancrÃ©as endocrine sÃ©crÃ¨te l'insuline (cellules bÃªta des Ã®lots de Langerhans) pour abaisser la glycÃ©mie."},
        {"id":"668_4","type":"qcm","question":"Quelle vitamine est synthÃ©tisÃ©e par la peau sous l'effet des rayons UV ?",
         "options":["Vitamine A","Vitamine B12","Vitamine C","Vitamine D"],
         "correct_option":"Vitamine D","explanation":"La vitamine D est synthÃ©tisÃ©e dans la peau Ã  partir de prÃ©curseurs sous l'action des UVB du soleil."},
        {"id":"668_5","type":"vrai-faux","question":"Un rÃ©gime trop riche en sucres rapides peut conduire Ã  un diabÃ¨te de type 2.",
         "correct":True,"explanation":"Une alimentation hyper-calorique favorise l'obÃ©sitÃ© et la rÃ©sistance Ã  l'insuline, facteurs de risque du diabÃ¨te de type 2."},
        {"id":"668_6","type":"texte","question":"Comment appelle-t-on l'indice qui mesure le rapport entre le poids et la taille au carrÃ© d'une personne ?",
         "correct_answer":"IMC","explanation":"L'Indice de Masse Corporelle (IMC = poids/tailleÂ²) permet d'Ã©valuer si une personne est en sous-poids, normale, en surpoids ou obÃ¨se."},
        {"id":"668_7","type":"qcm","question":"Quelle est la consÃ©quence d'une carence en fer sur l'organisme ?",
         "options":["DiabÃ¨te","AnÃ©mie ferriprive","Cancer du foie","Maladie de Parkinson"],
         "correct_option":"AnÃ©mie ferriprive","explanation":"Le fer est indispensable Ã  la synthÃ¨se de l'hÃ©moglobine ; sa carence provoque une anÃ©mie (diminution des globules rouges fonctionnels)."},
        {"id":"668_8","type":"vrai-faux","question":"L'activitÃ© physique rÃ©guliÃ¨re contribue Ã  prÃ©venir les maladies cardiovasculaires.",
         "correct":True,"explanation":"L'exercice physique amÃ©liore le profil lipidique, rÃ©duit la tension artÃ©rielle et maintient un poids sain, rÃ©duisant le risque cardiovasculaire."},
    ]),

    # =========================================================
    # BLOC 4 â€“ REPRODUCTION ET DÃ‰VELOPPEMENT (669â€“673)
    # =========================================================
    (669, "Reproduction humaine : les organes gÃ©nitaux", "SVT", "3Ã¨me", [
        {"id":"669_1","type":"qcm","question":"Quel organe fÃ©minin produit les ovocytes ?",
         "options":["UtÃ©rus","Trompe de Fallope","Ovaire","Vagin"],"correct_option":"Ovaire",
         "explanation":"Les ovaires produisent les ovocytes (gamÃ¨tes femelles) et les hormones sexuelles fÃ©minines."},
        {"id":"669_2","type":"vrai-faux","question":"La femme libÃ¨re un ovocyte chaque mois lors de l'ovulation.",
         "correct":True,"explanation":"Environ au 14Ã¨me jour d'un cycle de 28 jours, un ovocyte est libÃ©rÃ© par l'ovaire lors de l'ovulation."},
        {"id":"669_3","type":"texte","question":"Quel nom porte la muqueuse utÃ©rine qui s'Ã©paissit chaque mois pour accueillir un Ã©ventuel embryon ?",
         "correct_answer":"endomÃ¨tre","explanation":"L'endomÃ¨tre est la muqueuse utÃ©rine qui se rÃ©gÃ©nÃ¨re Ã  chaque cycle et est Ã©liminÃ©e lors des rÃ¨gles en l'absence de fÃ©condation."},
        {"id":"669_4","type":"qcm","question":"OÃ¹ se produit gÃ©nÃ©ralement la fÃ©condation chez la femme ?",
         "options":["Dans l'ovaire","Dans l'utÃ©rus","Dans la trompe de Fallope","Dans le vagin"],
         "correct_option":"Dans la trompe de Fallope","explanation":"La fÃ©condation (fusion du spermatozoÃ¯de et de l'ovocyte) a lieu dans le tiers supÃ©rieur de la trompe de Fallope."},
        {"id":"669_5","type":"vrai-faux","question":"Le testicule produit Ã  la fois les spermatozoÃ¯des et la testostÃ©rone.",
         "correct":True,"explanation":"Les tubes sÃ©minifÃ¨res des testicules produisent les spermatozoÃ¯des et les cellules de Leydig sÃ©crÃ¨tent la testostÃ©rone."},
        {"id":"669_6","type":"texte","question":"Comment appelle-t-on l'implantation de l'embryon dans la paroi utÃ©rine ?",
         "correct_answer":"nidation","explanation":"La nidation (ou implantation) est l'insertion de l'embryon dans l'endomÃ¨tre vers le 7Ã¨me jour aprÃ¨s la fÃ©condation."},
        {"id":"669_7","type":"qcm","question":"Qu'est-ce que le placenta ?",
         "options":["L'enveloppe externe du fÅ“tus","L'organe d'Ã©change entre la mÃ¨re et le fÅ“tus","Le cordon ombilical","La poche des eaux"],
         "correct_option":"L'organe d'Ã©change entre la mÃ¨re et le fÅ“tus",
         "explanation":"Le placenta assure les Ã©changes de nutriments, d'O2 et de CO2 entre la circulation maternelle et fÅ“tale."},
        {"id":"669_8","type":"vrai-faux","question":"Le cordon ombilical relie le fÅ“tus au placenta.",
         "correct":True,"explanation":"Le cordon ombilical contient la veine ombilicale (O2, nutriments) et deux artÃ¨res ombilicales (dÃ©chets)."},
    ]),

    (670, "Reproduction humaine : cycle menstruel et pubertÃ©", "SVT", "3Ã¨me", [
        {"id":"670_1","type":"qcm","question":"Quelle hormone dÃ©clenche l'ovulation chez la femme ?",
         "options":["Å’strogÃ¨nes","ProgestÃ©rone","LH (hormone lutÃ©inisante)","FSH (hormone folliculostimulante)"],
         "correct_option":"LH (hormone lutÃ©inisante)","explanation":"Le pic de LH produit par l'hypophyse dÃ©clenche l'ovulation environ 36 heures aprÃ¨s son apparition."},
        {"id":"670_2","type":"vrai-faux","question":"La pubertÃ© chez les filles dÃ©bute gÃ©nÃ©ralement entre 8 et 13 ans.",
         "correct":True,"explanation":"La pubertÃ© fÃ©minine dÃ©bute en moyenne entre 8 et 13 ans, avec l'apparition des caractÃ¨res sexuels secondaires."},
        {"id":"670_3","type":"texte","question":"Quel nom porte le premier cycle menstruel d'une jeune fille ?",
         "correct_answer":"mÃ©narche","explanation":"La mÃ©narche dÃ©signe les premiÃ¨res rÃ¨gles, marquant le dÃ©but de la fertilitÃ© fÃ©minine."},
        {"id":"670_4","type":"qcm","question":"Quelle hormone masculine est responsable du dÃ©veloppement des caractÃ¨res sexuels masculins Ã  la pubertÃ© ?",
         "options":["Å’strogÃ¨ne","ProgestÃ©rone","TestostÃ©rone","Cortisol"],
         "correct_option":"TestostÃ©rone","explanation":"La testostÃ©rone, sÃ©crÃ©tÃ©e par les testicules sous contrÃ´le de la LH, dÃ©clenche la pubertÃ© masculine."},
        {"id":"670_5","type":"vrai-faux","question":"Les rÃ¨gles correspondent au renouvellement de l'endomÃ¨tre en l'absence de fÃ©condation.",
         "correct":True,"explanation":"En l'absence de nidation, la chute de progestÃ©rone provoque la destruction de l'endomÃ¨tre et les saignements menstruels."},
        {"id":"670_6","type":"texte","question":"Quelle glande du cerveau rÃ©gule les hormones de reproduction en sÃ©crÃ©tant la FSH et la LH ?",
         "correct_answer":"hypophyse","explanation":"L'hypophyse (antÃ©rieure) sÃ©crÃ¨te FSH et LH, qui contrÃ´lent le cycle ovarien et la production de gamÃ¨tes."},
        {"id":"670_7","type":"qcm","question":"Combien de temps dure en moyenne la grossesse chez l'Ãªtre humain ?",
         "options":["6 mois","9 mois (38 semaines de dÃ©veloppement)","12 mois","4 mois"],"correct_option":"9 mois (38 semaines de dÃ©veloppement)",
         "explanation":"La grossesse dure environ 38 semaines de dÃ©veloppement embryonnaire/fÅ“tal, soit 40 semaines d'amÃ©norrhÃ©e."},
        {"id":"670_8","type":"vrai-faux","question":"La mÃ©nopause marque l'arrÃªt dÃ©finitif de l'ovulation et des menstruations.",
         "correct":True,"explanation":"La mÃ©nopause survient vers 50 ans en moyenne et est dÃ©finie par l'arrÃªt des cycles menstruels depuis 12 mois consÃ©cutifs."},
    ]),

    (671, "Reproduction humaine : contraception et IST", "SVT", "3Ã¨me", [
        {"id":"671_1","type":"qcm","question":"Quel est le seul contraceptif qui protÃ¨ge Ã©galement contre les infections sexuellement transmissibles (IST) ?",
         "options":["La pilule","Le stÃ©rilet","Le prÃ©servatif","L'implant sous-cutanÃ©"],
         "correct_option":"Le prÃ©servatif","explanation":"Le prÃ©servatif (masculin ou fÃ©minin) est la seule mÃ©thode contraceptive qui protÃ¨ge aussi contre les IST."},
        {"id":"671_2","type":"vrai-faux","question":"La pilule contraceptive empÃªche l'ovulation en agissant sur les hormones.",
         "correct":True,"explanation":"La pilule combinÃ©e (Å“strogÃ¨nes + progestÃ©rone) inhibe la sÃ©crÃ©tion de FSH et LH, bloquant l'ovulation."},
        {"id":"671_3","type":"texte","question":"Quel est le mÃ©dicament qui peut Ãªtre pris aprÃ¨s un rapport non protÃ©gÃ© pour empÃªcher une grossesse non dÃ©sirÃ©e ?",
         "correct_answer":"pilule du lendemain","explanation":"La contraception d'urgence (pilule du lendemain) retarde ou inhibe l'ovulation si elle est prise dans les 72 heures."},
        {"id":"671_4","type":"qcm","question":"Quelle IST est causÃ©e par la bactÃ©rie Chlamydia trachomatis ?",
         "options":["HerpÃ¨s","Chlamydiose","Syphilis","HÃ©patite B"],
         "correct_option":"Chlamydiose","explanation":"La chlamydiose est l'IST bactÃ©rienne la plus rÃ©pandue dans les pays dÃ©veloppÃ©s, souvent asymptomatique."},
        {"id":"671_5","type":"vrai-faux","question":"L'hÃ©patite B est une IST qui peut Ãªtre prÃ©venue par la vaccination.",
         "correct":True,"explanation":"Un vaccin efficace contre l'hÃ©patite B est disponible et recommandÃ© dans le calendrier vaccinal."},
        {"id":"671_6","type":"texte","question":"Comment appelle-t-on l'interruption volontaire de grossesse mÃ©dicamenteuse ou chirurgicale ?",
         "correct_answer":"IVG","explanation":"L'IVG (Interruption Volontaire de Grossesse) est lÃ©gale en France jusqu'Ã  14 semaines d'amÃ©norrhÃ©e."},
        {"id":"671_7","type":"qcm","question":"Qu'est-ce que la PrEP ?",
         "options":["Un vaccin contre le VIH","Un traitement prÃ©ventif contre le VIH pris avant l'exposition","Un traitement aprÃ¨s contamination par le VIH","Un test de dÃ©pistage du VIH"],
         "correct_option":"Un traitement prÃ©ventif contre le VIH pris avant l'exposition",
         "explanation":"La PrEP (Prophylaxie PrÃ©-Exposition) est un traitement antirÃ©troviral pris par des personnes sÃ©ronÃ©gatives Ã  risque pour prÃ©venir l'infection par le VIH."},
        {"id":"671_8","type":"vrai-faux","question":"La syphilis est une IST bactÃ©rienne traitable par antibiotiques.",
         "correct":True,"explanation":"La syphilis est causÃ©e par Treponema pallidum et se traite efficacement par pÃ©nicilline."},
    ]),

    (672, "DÃ©veloppement embryonnaire et fÅ“tal", "SVT", "3Ã¨me", [
        {"id":"672_1","type":"qcm","question":"Comment appelle-t-on la cellule rÃ©sultant de la fusion du spermatozoÃ¯de et de l'ovocyte ?",
         "options":["Blastocyte","Zygote","Embryon","Gastrula"],"correct_option":"Zygote",
         "explanation":"Le zygote est la cellule initiale formÃ©e par la fÃ©condation ; il contient 46 chromosomes (23 paternels + 23 maternels)."},
        {"id":"672_2","type":"vrai-faux","question":"Le cÅ“ur fÅ“tal commence Ã  battre vers la 3Ã¨me semaine de grossesse.",
         "correct":True,"explanation":"Le cÅ“ur de l'embryon commence Ã  se contracter vers les 3-4Ã¨me semaines de dÃ©veloppement."},
        {"id":"672_3","type":"texte","question":"Comment appelle-t-on le stade de dÃ©veloppement de 8 semaines aprÃ¨s la fÃ©condation jusqu'Ã  la naissance ?",
         "correct_answer":"fÅ“tus","explanation":"L'embryon devient un fÅ“tus Ã  partir de la 8Ã¨me semaine de dÃ©veloppement, lorsque tous les organes sont Ã©bauches."},
        {"id":"672_4","type":"qcm","question":"Quelle substance traverse facilement le placenta et peut nuire au dÃ©veloppement fÅ“tal ?",
         "options":["Les globules rouges maternels","La nicotine et l'alcool","Les anticorps maternels uniquement","Les bactÃ©ries"],
         "correct_option":"La nicotine et l'alcool","explanation":"De nombreuses substances (alcool, nicotine, drogues, certains mÃ©dicaments) traversent le placenta et peuvent provoquer des malformations."},
        {"id":"672_5","type":"vrai-faux","question":"L'Ã©chographie permet de visualiser le fÅ“tus sans danger pour la mÃ¨re ni pour l'enfant.",
         "correct":True,"explanation":"L'Ã©chographie utilise des ultrasons inoffensifs pour visualiser le fÅ“tus, le placenta et le liquide amniotique."},
        {"id":"672_6","type":"texte","question":"Comment appelle-t-on le liquide dans lequel le fÅ“tus est immergÃ© durant la grossesse ?",
         "correct_answer":"liquide amniotique","explanation":"Le liquide amniotique remplit la cavitÃ© amniotique et protÃ¨ge le fÅ“tus des chocs tout en permettant ses mouvements."},
        {"id":"672_7","type":"qcm","question":"Qu'est-ce que le syndrome d'alcoolisation fÅ“tale (SAF) ?",
         "options":["Une maladie gÃ©nÃ©tique","Un ensemble de malformations dues Ã  la consommation d'alcool pendant la grossesse","Une infection du fÅ“tus par le VIH","Une carence en vitamines"],
         "correct_option":"Un ensemble de malformations dues Ã  la consommation d'alcool pendant la grossesse",
         "explanation":"Le SAF est la premiÃ¨re cause non gÃ©nÃ©tique de handicap mental en France, causÃ© par l'alcool qui traverse le placenta."},
        {"id":"672_8","type":"vrai-faux","question":"La procrÃ©ation mÃ©dicalement assistÃ©e (PMA) comprend la fÃ©condation in vitro (FIV).",
         "correct":True,"explanation":"La FIV (fÃ©condation in vitro) est l'une des techniques de PMA oÃ¹ la fÃ©condation est rÃ©alisÃ©e en dehors du corps de la femme."},
    ]),

    (673, "PubertÃ©, croissance et hormones", "SVT", "3Ã¨me", [
        {"id":"673_1","type":"qcm","question":"Quelle glande cÃ©rÃ©brale est souvent appelÃ©e 'chef d'orchestre' du systÃ¨me hormonal ?",
         "options":["ThyroÃ¯de","Hypophyse","SurrÃ©nale","Ã‰piphyse"],"correct_option":"Hypophyse",
         "explanation":"L'hypophyse contrÃ´le de nombreuses glandes endocrines via ses hormones (TSH, FSH, LH, GH, etc.)."},
        {"id":"673_2","type":"vrai-faux","question":"L'hormone de croissance (GH) est sÃ©crÃ©tÃ©e par le pancrÃ©as.",
         "correct":False,"explanation":"L'hormone de croissance (GH ou somatotropine) est sÃ©crÃ©tÃ©e par l'antÃ©hypophyse, non par le pancrÃ©as."},
        {"id":"673_3","type":"texte","question":"Quel organe produit les hormones thyroÃ¯diennes qui rÃ©gulent le mÃ©tabolisme ?",
         "correct_answer":"thyroÃ¯de","explanation":"La glande thyroÃ¯de, situÃ©e dans le cou, produit T3 et T4 qui rÃ©gulent le mÃ©tabolisme de base."},
        {"id":"673_4","type":"qcm","question":"Comment les hormones circulent-elles dans l'organisme pour atteindre leurs cellules cibles ?",
         "options":["Par les nerfs","Via le sang","Par diffusion dans les tissus","Par les vaisseaux lymphatiques uniquement"],
         "correct_option":"Via le sang","explanation":"Les hormones sont sÃ©crÃ©tÃ©es dans le sang par les glandes endocrines et transportÃ©es vers leurs organes cibles."},
        {"id":"673_5","type":"vrai-faux","question":"Les caractÃ¨res sexuels secondaires (pilositÃ©, mue de la voix, dÃ©veloppement des seins) apparaissent Ã  la pubertÃ© sous l'effet des hormones sexuelles.",
         "correct":True,"explanation":"La testostÃ©rone (chez les garÃ§ons) et les Å“strogÃ¨nes (chez les filles) dÃ©clenchent les modifications corporelles de la pubertÃ©."},
        {"id":"673_6","type":"texte","question":"Comment appelle-t-on les glandes qui libÃ¨rent leurs sÃ©crÃ©tions directement dans le sang sans canal excrÃ©teur ?",
         "correct_answer":"glandes endocrines","explanation":"Les glandes endocrines (thyroÃ¯de, hypophyse, glandes surrÃ©nales, gonades) sÃ©crÃ¨tent leurs hormones directement dans la circulation sanguine."},
        {"id":"673_7","type":"qcm","question":"Quel est le rÃ´le de l'adrÃ©naline sÃ©crÃ©tÃ©e par les glandes surrÃ©nales ?",
         "options":["RÃ©guler la glycÃ©mie","PrÃ©parer l'organisme Ã  une rÃ©action de 'combat ou fuite'","Favoriser la croissance osseuse","Stimuler la production de gamÃ¨tes"],
         "correct_option":"PrÃ©parer l'organisme Ã  une rÃ©action de 'combat ou fuite'","explanation":"L'adrÃ©naline augmente la frÃ©quence cardiaque, dilate les bronches et mobilise les rÃ©serves Ã©nergÃ©tiques face au stress."},
        {"id":"673_8","type":"vrai-faux","question":"Le diabÃ¨te insipide est causÃ© par une carence en insuline.",
         "correct":False,"explanation":"Le diabÃ¨te insipide est dÃ» Ã  une carence en hormone antidiurÃ©tique (ADH/vasopressine), non en insuline."},
    ]),

    # =========================================================
    # BLOC 5 â€“ GÃ‰OLOGIE ET TECTONIQUE DES PLAQUES (674â€“680)
    # =========================================================
    (674, "GÃ©ologie : structure interne du globe terrestre", "SVT", "3Ã¨me", [
        {"id":"674_1","type":"qcm","question":"Quelles sont les trois grandes enveloppes internes de la Terre de la surface vers le centre ?",
         "options":["CroÃ»te, manteau, noyau","LithosphÃ¨re, asthÃ©nosphÃ¨re, mÃ©sosphÃ¨re","Sol, roche mÃ¨re, magma","SÃ©diments, granite, basalte"],
         "correct_option":"CroÃ»te, manteau, noyau","explanation":"La Terre est structurÃ©e en croÃ»te (externe), manteau (intermÃ©diaire) et noyau (central, lui-mÃªme divisÃ© en noyau externe liquide et interne solide)."},
        {"id":"674_2","type":"vrai-faux","question":"Le noyau interne de la Terre est liquide.",
         "correct":False,"explanation":"Le noyau interne (graine) est solide composÃ© de fer et de nickel, tandis que le noyau externe est liquide."},
        {"id":"674_3","type":"texte","question":"Comment appelle-t-on la discontinuitÃ© entre la croÃ»te et le manteau ?",
         "correct_answer":"discontinuitÃ© de Mohorovicic","explanation":"La discontinuitÃ© de Mohorovicic (Moho) marque la limite entre la croÃ»te terrestre et le manteau supÃ©rieur."},
        {"id":"674_4","type":"qcm","question":"De quoi est principalement composÃ© le noyau terrestre ?",
         "options":["Silicium et oxygÃ¨ne","Fer et nickel","Calcium et magnÃ©sium","Uranium et plutonium"],
         "correct_option":"Fer et nickel","explanation":"Le noyau est composÃ© essentiellement de fer (80%) et de nickel, responsable du champ magnÃ©tique terrestre."},
        {"id":"674_5","type":"vrai-faux","question":"La lithosphÃ¨re comprend la croÃ»te terrestre et la partie supÃ©rieure du manteau.",
         "correct":True,"explanation":"La lithosphÃ¨re est la couche rigide externe incluant la croÃ»te et le manteau lithosphÃ©rique jusqu'Ã  environ 100 km de profondeur."},
        {"id":"674_6","type":"texte","question":"Quelle mÃ©thode indirecte utilise la propagation des ondes sismiques pour Ã©tudier la structure interne du globe ?",
         "correct_answer":"sismologie","explanation":"La sismologie analyse la vitesse et la direction des ondes sismiques pour dÃ©duire la composition et l'Ã©tat des couches internes."},
        {"id":"674_7","type":"qcm","question":"Quel est l'Ã©tat physique du manteau supÃ©rieur (asthÃ©nosphÃ¨re) ?",
         "options":["Solide rigide","Solide plastique et partiellement fondu","EntiÃ¨rement liquide","Gazeux"],
         "correct_option":"Solide plastique et partiellement fondu",
         "explanation":"L'asthÃ©nosphÃ¨re est semi-plastique, permettant la convection et le dÃ©placement des plaques lithosphÃ©riques."},
        {"id":"674_8","type":"vrai-faux","question":"La croÃ»te ocÃ©anique est plus Ã©paisse que la croÃ»te continentale.",
         "correct":False,"explanation":"La croÃ»te continentale mesure 30 Ã  70 km d'Ã©paisseur, contre 5 Ã  10 km pour la croÃ»te ocÃ©anique."},
    ]),

    (675, "GÃ©ologie : tectonique des plaques", "SVT", "3Ã¨me", [
        {"id":"675_1","type":"qcm","question":"Qui a proposÃ© la thÃ©orie de la dÃ©rive des continents ?",
         "options":["Charles Darwin","Alfred Wegener","Albert Einstein","Isaac Newton"],
         "correct_option":"Alfred Wegener","explanation":"Alfred Wegener a proposÃ© la thÃ©orie de la dÃ©rive des continents en 1912, prÃ©misse de la tectonique des plaques."},
        {"id":"675_2","type":"vrai-faux","question":"Les plaques tectoniques se dÃ©placent de quelques centimÃ¨tres par an.",
         "correct":True,"explanation":"Les plaques tectoniques se dÃ©placent Ã  des vitesses de 1 Ã  15 cm/an selon les zones."},
        {"id":"675_3","type":"texte","question":"Comment appelle-t-on la zone oÃ¹ deux plaques tectoniques s'Ã©cartent l'une de l'autre ?",
         "correct_answer":"dorsale ocÃ©anique","explanation":"Les dorsales ocÃ©aniques sont des zones de divergence oÃ¹ le magma remonte et crÃ©e de la nouvelle croÃ»te ocÃ©anique."},
        {"id":"675_4","type":"qcm","question":"Que se passe-t-il dans une zone de subduction ?",
         "options":["Deux plaques s'Ã©cartent","Une plaque plonge sous une autre","Deux plaques coulissent latÃ©ralement","Une plaque se soulÃ¨ve pour former des montagnes"],
         "correct_option":"Une plaque plonge sous une autre","explanation":"Dans une zone de subduction, la plaque ocÃ©anique (plus dense) plonge sous une plaque continentale ou ocÃ©anique."},
        {"id":"675_5","type":"vrai-faux","question":"La chaÃ®ne himalayenne rÃ©sulte d'une collision entre deux plaques continentales.",
         "correct":True,"explanation":"La collision de la plaque indo-australienne avec la plaque eurasiatique soulÃ¨ve les Himalayas."},
        {"id":"675_6","type":"texte","question":"Comment appelle-t-on les forces motrices qui dÃ©placent les plaques tectoniques, liÃ©es Ã  la chaleur interne de la Terre ?",
         "correct_answer":"courants de convection","explanation":"Les courants de convection dans l'asthÃ©nosphÃ¨re et le manteau infÃ©rieur entraÃ®nent les plaques tectoniques."},
        {"id":"675_7","type":"qcm","question":"Quel est le lien entre les zones de subduction et le volcanisme ?",
         "options":["Aucun lien","La plaque qui plonge libÃ¨re de l'eau qui abaisse le point de fusion du manteau, formant du magma","La subduction refroidit le manteau","La subduction crÃ©e uniquement des sÃ©ismes"],
         "correct_option":"La plaque qui plonge libÃ¨re de l'eau qui abaisse le point de fusion du manteau, formant du magma",
         "explanation":"La dÃ©shydratation de la plaque subduite hydrate le manteau sus-jacent, abaissant son point de fusion et gÃ©nÃ©rant du magma."},
        {"id":"675_8","type":"vrai-faux","question":"La faille de San Andreas en Californie est une faille transformante.",
         "correct":True,"explanation":"La faille de San Andreas est une faille dÃ©crochante oÃ¹ la plaque Pacifique coulisse par rapport Ã  la plaque nord-amÃ©ricaine."},
    ]),

    (676, "GÃ©ologie : sÃ©ismes", "SVT", "3Ã¨me", [
        {"id":"676_1","type":"qcm","question":"Qu'est-ce que l'Ã©picentre d'un sÃ©isme ?",
         "options":["Le point de rupture en profondeur","Le point en surface situÃ© Ã  la verticale du foyer","La zone de collision entre plaques","Le centre gÃ©ographique du sÃ©isme"],
         "correct_option":"Le point en surface situÃ© Ã  la verticale du foyer",
         "explanation":"L'Ã©picentre est le point de la surface terrestre directement au-dessus du foyer (hypocentre) du sÃ©isme."},
        {"id":"676_2","type":"vrai-faux","question":"L'Ã©chelle de Richter mesure l'intensitÃ© ressentie d'un sÃ©isme.",
         "correct":False,"explanation":"L'Ã©chelle de Richter (magnitude) mesure l'Ã©nergie libÃ©rÃ©e, tandis que l'Ã©chelle MSK ou EMS mesure l'intensitÃ© (dommages ressentis)."},
        {"id":"676_3","type":"texte","question":"Quel instrument mesure les ondes sismiques ?",
         "correct_answer":"sismographe","explanation":"Le sismographe enregistre les vibrations du sol lors d'un sÃ©isme ; le tracÃ© obtenu est un sismogramme."},
        {"id":"676_4","type":"qcm","question":"Un tsunami est provoquÃ© par :",
         "options":["Un cyclone tropical","Un sÃ©isme sous-marin ou une Ã©ruption volcanique sous-marine","Une marÃ©e exceptionnelle","Un sÃ©isme continental"],
         "correct_option":"Un sÃ©isme sous-marin ou une Ã©ruption volcanique sous-marine",
         "explanation":"Un tsunami rÃ©sulte d'un dÃ©placement vertical brutal du fond marin lors d'un sÃ©isme sous-marin ou d'une Ã©ruption volcanique."},
        {"id":"676_5","type":"vrai-faux","question":"Les sÃ©ismes les plus destructeurs se produisent gÃ©nÃ©ralement aux frontiÃ¨res de plaques.",
         "correct":True,"explanation":"La majoritÃ© des sÃ©ismes majeurs se concentrent aux limites de plaques (zones de subduction, dorsales, failles transformantes)."},
        {"id":"676_6","type":"texte","question":"Comment appelle-t-on le phÃ©nomÃ¨ne de liquÃ©faction des sols meubles saturÃ©s d'eau lors d'un sÃ©isme ?",
         "correct_answer":"liquÃ©faction","explanation":"La liquÃ©faction des sols est un phÃ©nomÃ¨ne oÃ¹ les secousses sismiques transforment un sol meuble en liquide, provoquant l'effondrement des bÃ¢timents."},
        {"id":"676_7","type":"qcm","question":"Quelle est la diffÃ©rence entre un sÃ©isme d'une magnitude 6 et 7 selon l'Ã©chelle de Richter ?",
         "options":["Le sÃ©isme 7 libÃ¨re 10 fois plus d'Ã©nergie","Le sÃ©isme 7 libÃ¨re environ 32 fois plus d'Ã©nergie","La diffÃ©rence est d'un seul degrÃ© sans signification physique","Le sÃ©isme 7 dure 2 fois plus longtemps"],
         "correct_option":"Le sÃ©isme 7 libÃ¨re environ 32 fois plus d'Ã©nergie","explanation":"L'Ã©chelle de Richter est logarithmique : chaque unitÃ© correspond Ã  environ 32 fois plus d'Ã©nergie libÃ©rÃ©e."},
        {"id":"676_8","type":"vrai-faux","question":"La construction parasismique peut rÃ©duire significativement les dommages lors d'un sÃ©isme.",
         "correct":True,"explanation":"Les normes parasismiques (fondations profondes, structures flexibles, contreventements) rÃ©duisent les dommages et protÃ¨gent les populations."},
    ]),

    (677, "GÃ©ologie : volcans et risque volcanique", "SVT", "3Ã¨me", [
        {"id":"677_1","type":"qcm","question":"Quel est le nom du rÃ©servoir de roche fondue sous un volcan ?",
         "options":["Lave","Magma (dans la chambre magmatique)","Basalte","Pyroclaste"],
         "correct_option":"Magma (dans la chambre magmatique)","explanation":"Le magma est le roche fondue en profondeur ; il devient lave lorsqu'il atteint la surface."},
        {"id":"677_2","type":"vrai-faux","question":"Les volcans des points chauds comme HawaÃ¯ se trouvent au centre des plaques tectoniques.",
         "correct":True,"explanation":"Les volcans de point chaud (HawaÃ¯, La RÃ©union) se forment au-dessus d'un panache mantellique fixe, indÃ©pendamment des frontiÃ¨res de plaques."},
        {"id":"677_3","type":"texte","question":"Comment appelle-t-on les projections solides Ã©jectÃ©es par un volcan lors d'une Ã©ruption explosive ?",
         "correct_answer":"pyroclastes","explanation":"Les pyroclastes (bombes, lapilli, cendres) sont des fragments solides projetÃ©s lors d'Ã©ruptions explosives."},
        {"id":"677_4","type":"qcm","question":"Quel gaz volcanique est le plus abondant dans les Ã©missions volcaniques ?",
         "options":["CO2","SO2","H2O (vapeur d'eau)","H2S"],"correct_option":"H2O (vapeur d'eau)",
         "explanation":"La vapeur d'eau reprÃ©sente 60 Ã  90% des gaz volcaniques Ã©mis lors d'une Ã©ruption."},
        {"id":"677_5","type":"vrai-faux","question":"Une Ã©ruption effusive produit des coulÃ©es de lave peu dangereuses pour les populations Ã  cause de leur lenteur.",
         "correct":True,"explanation":"Les Ã©ruptions effusives (comme Ã  HawaÃ¯) produisent des laves fluides qui s'Ã©coulent lentement, permettant l'Ã©vacuation."},
        {"id":"677_6","type":"texte","question":"Comment appelle-t-on le nuage de cendres, gaz et dÃ©bris chauds qui dÃ©vale les flancs d'un volcan Ã  trÃ¨s grande vitesse ?",
         "correct_answer":"nuÃ©e ardente","explanation":"La nuÃ©e ardente (ou coulÃ©e pyroclastique) est l'un des phÃ©nomÃ¨nes volcaniques les plus dangereux, atteignant 700Â°C et plus de 500 km/h."},
        {"id":"677_7","type":"qcm","question":"Quelle Ã©ruption explosive catastrophique a dÃ©truit la ville de Saint-Pierre en Martinique en 1902 ?",
         "options":["Ã‰ruption du VÃ©suve","Ã‰ruption de la Montagne PelÃ©e","Ã‰ruption du Pinatubo","Ã‰ruption du Krakatoa"],
         "correct_option":"Ã‰ruption de la Montagne PelÃ©e","explanation":"La Montagne PelÃ©e (Martinique) a produit une nuÃ©e ardente dÃ©vastatrice en mai 1902, tuant environ 30 000 personnes."},
        {"id":"677_8","type":"vrai-faux","question":"La surveillance volcanique (OVSG, IPGP) permet d'anticiper certaines Ã©ruptions et d'alerter les populations.",
         "correct":True,"explanation":"Les observatoires volcanologiques surveillent les sÃ©ismes volcano-tectoniques, la dÃ©formation du sol et les Ã©missions de gaz pour prÃ©voir les Ã©ruptions."},
    ]),

    (678, "GÃ©ologie : roches et minÃ©raux", "SVT", "3Ã¨me", [
        {"id":"678_1","type":"qcm","question":"Comment se forme le granite ?",
         "options":["Par refroidissement lent du magma en profondeur","Par accumulation de sÃ©diments","Par mÃ©tamorphisme de haute tempÃ©rature","Par dÃ©pÃ´t d'organismes marins"],
         "correct_option":"Par refroidissement lent du magma en profondeur","explanation":"Le granite est une roche magmatique plutonique formÃ©e par refroidissement lent du magma dans la croÃ»te terrestre."},
        {"id":"678_2","type":"vrai-faux","question":"Le calcaire est une roche sÃ©dimentaire souvent formÃ©e de restes d'organismes marins.",
         "correct":True,"explanation":"Le calcaire rÃ©sulte de l'accumulation de coquilles et squelettes d'organismes marins contenant du carbonate de calcium (CaCO3)."},
        {"id":"678_3","type":"texte","question":"Comment appelle-t-on les roches formÃ©es par transformation d'autres roches sous l'effet de la chaleur et de la pression ?",
         "correct_answer":"roches mÃ©tamorphiques","explanation":"Le mÃ©tamorphisme transforme des roches prÃ©existantes (sÃ©dimentaires ou magmatiques) en roches mÃ©tamorphiques (ex. marbre, gneiss, schiste)."},
        {"id":"678_4","type":"qcm","question":"Quel est le principal minÃ©ral du granite ?",
         "options":["Calcite","Quartz","PyroxÃ¨ne","Olivine"],"correct_option":"Quartz",
         "explanation":"Le granite est composÃ© de quartz, de feldspaths (orthose et plagioclase) et de micas (biotite, muscovite)."},
        {"id":"678_5","type":"vrai-faux","question":"Le basalte est une roche volcanique formÃ©e par refroidissement rapide de la lave en surface.",
         "correct":True,"explanation":"Le basalte (roche volcanique effusive) se forme par refroidissement rapide de lave Ã  la surface, prÃ©sentant une texture microlithique."},
        {"id":"678_6","type":"texte","question":"Quel phÃ©nomÃ¨ne gÃ©ologique transforme progressivement les roches exposÃ©es en surface (eau, gel, vent) ?",
         "correct_answer":"Ã©rosion","explanation":"L'Ã©rosion dÃ©sagrÃ¨ge et transporte les roches via l'action de l'eau, du vent, de la glace et des variations thermiques."},
        {"id":"678_7","type":"qcm","question":"Quel est le cycle gÃ©ologique qui relie les trois types de roches (magmatiques, sÃ©dimentaires, mÃ©tamorphiques) ?",
         "options":["Cycle de l'eau","Cycle des roches (cycle lithologique)","Cycle du carbone","Cycle de l'azote"],
         "correct_option":"Cycle des roches (cycle lithologique)","explanation":"Le cycle lithologique (pÃ©trogÃ©nÃ©tique) dÃ©crit les transformations continues entre les trois grands types de roches."},
        {"id":"678_8","type":"vrai-faux","question":"Le diamant est une forme cristalline du carbone.",
         "correct":True,"explanation":"Le diamant est du carbone pur cristallisÃ© sous forme cubique sous trÃ¨s haute pression et haute tempÃ©rature."},
    ]),

    (679, "GÃ©ologie : ressources naturelles et Ã©nergie", "SVT", "3Ã¨me", [
        {"id":"679_1","type":"qcm","question":"Quelle est l'origine des combustibles fossiles (pÃ©trole, gaz naturel, charbon) ?",
         "options":["Des roches volcaniques","La dÃ©composition d'organismes anciens sous chaleur et pression","Des minÃ©raux radioactifs","Des dÃ©pÃ´ts de sels marins"],
         "correct_option":"La dÃ©composition d'organismes anciens sous chaleur et pression",
         "explanation":"Les combustibles fossiles sont issus de la transformation de matiÃ¨re organique (algues, vÃ©gÃ©taux, animaux) sur des millions d'annÃ©es."},
        {"id":"679_2","type":"vrai-faux","question":"Les ressources gÃ©othermiques utilisent la chaleur interne de la Terre pour produire de l'Ã©nergie.",
         "correct":True,"explanation":"La gÃ©othermie exploite la chaleur des roches profondes ou des eaux souterraines chaudes pour chauffage ou production d'Ã©lectricitÃ©."},
        {"id":"679_3","type":"texte","question":"Comment appelle-t-on les minerais contenant de l'uranium exploitÃ©s pour l'Ã©nergie nuclÃ©aire ?",
         "correct_answer":"pechblende","explanation":"La pechblende (uraninite) est le principal minerai d'uranium ; l'uranium extrait alimente les centrales nuclÃ©aires."},
        {"id":"679_4","type":"qcm","question":"Pourquoi les combustibles fossiles sont-ils considÃ©rÃ©s comme des ressources non renouvelables ?",
         "options":["Car ils brÃ»lent trop vite","Car leur formation prend des millions d'annÃ©es, bien plus que leur consommation","Car ils sont difficiles Ã  extraire","Car ils ne produisent pas d'Ã©nergie"],
         "correct_option":"Car leur formation prend des millions d'annÃ©es, bien plus que leur consommation",
         "explanation":"Les combustibles fossiles se forment sur des millions d'annÃ©es ; leur consommation actuelle est des millions de fois plus rapide que leur renouvellement."},
        {"id":"679_5","type":"vrai-faux","question":"L'eau douce est une ressource non renouvelable Ã  l'Ã©chelle humaine.",
         "correct":False,"explanation":"L'eau douce fait partie du cycle hydrologique et se renouvelle ; cependant, sa surexploitation peut localement en Ã©puiser les rÃ©serves."},
        {"id":"679_6","type":"texte","question":"Comment appelle-t-on les sables imbibÃ©s de pÃ©trole trÃ¨s visqueux, exploitÃ©s notamment au Canada ?",
         "correct_answer":"sables bitumineux","explanation":"Les sables bitumineux (tar sands) contiennent une forme lourde de pÃ©trole nÃ©cessitant des procÃ©dÃ©s d'extraction trÃ¨s Ã©nergivores."},
        {"id":"679_7","type":"qcm","question":"Quel gaz Ã  effet de serre est principalement libÃ©rÃ© par la combustion des hydrocarbures ?",
         "options":["MÃ©thane (CH4)","Dioxyde de carbone (CO2)","Vapeur d'eau","Dioxyde de soufre (SO2)"],
         "correct_option":"Dioxyde de carbone (CO2)","explanation":"La combustion d'hydrocarbures produit principalement du CO2, principal gaz Ã  effet de serre d'origine anthropique."},
        {"id":"679_8","type":"vrai-faux","question":"L'Ã©nergie solaire et Ã©olienne sont des Ã©nergies renouvelables.",
         "correct":True,"explanation":"Le soleil et le vent sont des sources inÃ©puisables Ã  l'Ã©chelle humaine, contrairement aux combustibles fossiles."},
    ]),

    (680, "GÃ©ologie : histoire de la Terre et temps gÃ©ologiques", "SVT", "3Ã¨me", [
        {"id":"680_1","type":"qcm","question":"Quel est l'Ã¢ge estimÃ© de la Terre ?",
         "options":["65 millions d'annÃ©es","500 millions d'annÃ©es","4,5 milliards d'annÃ©es","13,8 milliards d'annÃ©es"],
         "correct_option":"4,5 milliards d'annÃ©es","explanation":"La Terre a Ã©tÃ© formÃ©e il y a environ 4,54 milliards d'annÃ©es par accrÃ©tion de matiÃ¨res dans la nÃ©buleuse solaire."},
        {"id":"680_2","type":"vrai-faux","question":"L'Ã¨re MÃ©sozoÃ¯que est aussi appelÃ©e 'l'Ã¨re des dinosaures'.",
         "correct":True,"explanation":"Le MÃ©sozoÃ¯que (252 â€“ 66 Ma) comprend le Trias, le Jurassique et le CrÃ©tacÃ©, pÃ©riode de domination des dinosaures."},
        {"id":"680_3","type":"texte","question":"Comment appelle-t-on le super-continent unique existant il y a environ 250 millions d'annÃ©es ?",
         "correct_answer":"PangÃ©e","explanation":"La PangÃ©e Ã©tait l'unique super-continent de la fin du PalÃ©ozoÃ¯que, entourÃ© par l'ocÃ©an Panthallassa."},
        {"id":"680_4","type":"qcm","question":"Quand a eu lieu l'extinction des dinosaures non-aviens ?",
         "options":["Il y a 250 millions d'annÃ©es","Il y a 66 millions d'annÃ©es","Il y a 2 millions d'annÃ©es","Il y a 500 000 ans"],
         "correct_option":"Il y a 66 millions d'annÃ©es","explanation":"L'extinction CrÃ©tacÃ©-PalÃ©ogÃ¨ne a Ã©liminÃ© les dinosaures non-aviens il y a 66 Ma, probablement causÃ©e par l'impact d'un astÃ©roÃ¯de et/ou un volcanisme intense."},
        {"id":"680_5","type":"vrai-faux","question":"L'Homo sapiens est apparu Ã  l'Ã¨re CÃ©nozoÃ¯que.",
         "correct":True,"explanation":"L'Homo sapiens est apparu il y a environ 300 000 ans dans le Quaternaire (Ã¨re CÃ©nozoÃ¯que)."},
        {"id":"680_6","type":"texte","question":"Comment appelle-t-on la mÃ©thode qui consiste Ã  utiliser la composition isotopique des roches pour les dater ?",
         "correct_answer":"datation radiomÃ©trique","explanation":"La datation radiomÃ©trique utilise la dÃ©sintÃ©gration d'isotopes radioactifs (U-Pb, K-Ar, Rb-Sr) pour mesurer l'Ã¢ge absolu des roches."},
        {"id":"680_7","type":"qcm","question":"Quel principe stratigraphique stipule que les couches les plus rÃ©centes se trouvent au-dessus des plus anciennes ?",
         "options":["Principe de continuitÃ©","Principe de superposition","Principe d'horizontalitÃ©","Principe d'identitÃ© palÃ©ontologique"],
         "correct_option":"Principe de superposition","explanation":"Le principe de superposition (Nicolas StÃ©non, 1669) stipule que dans une sÃ©rie non perturbÃ©e, les couches infÃ©rieures sont plus anciennes."},
        {"id":"680_8","type":"vrai-faux","question":"La dÃ©rive des continents a modifiÃ© les conditions climatiques de la Terre au cours des temps gÃ©ologiques.",
         "correct":True,"explanation":"Le dÃ©placement des continents a modifiÃ© les courants ocÃ©aniques, la distribution des terres et des mers, et influencÃ© les palÃ©oclimats."},
    ]),

    # =========================================================
    # BLOC 6 â€“ Ã‰COLOGIE ET ENVIRONNEMENT (681â€“689)
    # =========================================================
    (681, "Ã‰cologie : Ã©cosystÃ¨mes et chaÃ®nes alimentaires", "SVT", "3Ã¨me", [
        {"id":"681_1","type":"qcm","question":"Qu'est-ce qu'un Ã©cosystÃ¨me ?",
         "options":["Un ensemble d'espÃ¨ces animales","L'ensemble des Ãªtres vivants et de leur milieu physique en interaction","Une forÃªt tropicale uniquement","Une chaÃ®ne alimentaire"],
         "correct_option":"L'ensemble des Ãªtres vivants et de leur milieu physique en interaction",
         "explanation":"Un Ã©cosystÃ¨me comprend la biocÃ©nose (communautÃ© d'Ãªtres vivants) et le biotope (milieu physique) qui interagissent."},
        {"id":"681_2","type":"vrai-faux","question":"Dans une chaÃ®ne alimentaire, les producteurs primaires sont des vÃ©gÃ©taux chlorophylliens.",
         "correct":True,"explanation":"Les producteurs primaires (vÃ©gÃ©taux, algues, cyanobactÃ©ries) synthÃ©tisent la matiÃ¨re organique par photosynthÃ¨se."},
        {"id":"681_3","type":"texte","question":"Comment appelle-t-on l'ensemble des chaÃ®nes alimentaires interconnectÃ©es dans un Ã©cosystÃ¨me ?",
         "correct_answer":"rÃ©seau trophique","explanation":"Le rÃ©seau trophique (ou rÃ©seau alimentaire) reprÃ©sente toutes les relations alimentaires entre les espÃ¨ces d'un Ã©cosystÃ¨me."},
        {"id":"681_4","type":"qcm","question":"Un organisme qui se nourrit de producteurs primaires est appelÃ© :",
         "options":["DÃ©composeur","Consommateur primaire (herbivore)","Producteur secondaire","Parasite"],
         "correct_option":"Consommateur primaire (herbivore)","explanation":"Le consommateur primaire se nourrit directement de vÃ©gÃ©taux ; il est au 2Ã¨me niveau trophique."},
        {"id":"681_5","type":"vrai-faux","question":"Les dÃ©composeurs jouent un rÃ´le clÃ© en recyclant la matiÃ¨re organique en Ã©lÃ©ments minÃ©raux.",
         "correct":True,"explanation":"Les dÃ©composeurs (bactÃ©ries, champignons) minÃ©ralisent la matiÃ¨re organique morte, restituant les Ã©lÃ©ments nutritifs au sol."},
        {"id":"681_6","type":"texte","question":"Comment appelle-t-on les relations oÃ¹ deux espÃ¨ces coexistent avec profit pour l'une et sans effet pour l'autre ?",
         "correct_answer":"commensalisme","explanation":"Le commensalisme est une relation interspÃ©cifique bÃ©nÃ©fique pour une espÃ¨ce (le commensal) et neutre pour l'autre (l'hÃ´te)."},
        {"id":"681_7","type":"qcm","question":"Qu'est-ce que la pyramide des nombres dans un Ã©cosystÃ¨me ?",
         "options":["Une reprÃ©sentation des espÃ¨ces par ordre alphabÃ©tique","Un graphique montrant la diminution du nombre d'individus Ã  chaque niveau trophique","Une carte des espÃ¨ces menacÃ©es","Un arbre phylogÃ©nÃ©tique"],
         "correct_option":"Un graphique montrant la diminution du nombre d'individus Ã  chaque niveau trophique",
         "explanation":"La pyramide des nombres illustre la rÃ©duction progressive du nombre d'individus des producteurs aux prÃ©dateurs."},
        {"id":"681_8","type":"vrai-faux","question":"Un prÃ©dateur peut aussi Ãªtre proie d'un autre prÃ©dateur.",
         "correct":True,"explanation":"Dans les rÃ©seaux trophiques complexes, un prÃ©dateur peut occuper plusieurs niveaux et Ãªtre lui-mÃªme consommÃ© (ex. renard mangÃ© par aigle)."},
    ]),

    (682, "Ã‰cologie : photosynthÃ¨se et respiration", "SVT", "3Ã¨me", [
        {"id":"682_1","type":"qcm","question":"Quelle est l'Ã©quation gÃ©nÃ©rale de la photosynthÃ¨se ?",
         "options":["CO2 + H2O + lumiÃ¨re â†’ glucose + O2","O2 + glucose â†’ CO2 + H2O + Ã©nergie","CO2 + O2 â†’ glucose + H2O","H2O + O2 â†’ CO2 + glucose"],
         "correct_option":"CO2 + H2O + lumiÃ¨re â†’ glucose + O2",
         "explanation":"La photosynthÃ¨se utilise CO2 et H2O en prÃ©sence de lumiÃ¨re pour synthÃ©tiser du glucose et libÃ©rer de l'O2."},
        {"id":"682_2","type":"vrai-faux","question":"La chlorophylle est le pigment qui absorbe la lumiÃ¨re pour la photosynthÃ¨se.",
         "correct":True,"explanation":"La chlorophylle, localisÃ©e dans les chloroplastes, absorbe principalement les longueurs d'onde rouge et bleue de la lumiÃ¨re."},
        {"id":"682_3","type":"texte","question":"Quel organite est le siÃ¨ge de la photosynthÃ¨se dans les cellules vÃ©gÃ©tales ?",
         "correct_answer":"chloroplaste","explanation":"Les chloroplastes contiennent la chlorophylle et les enzymes nÃ©cessaires aux deux phases de la photosynthÃ¨se."},
        {"id":"682_4","type":"qcm","question":"La respiration cellulaire produit de l'Ã©nergie Ã  partir de :",
         "options":["CO2 et H2O","Glucose et O2","LumiÃ¨re et chlorophylle","N2 et H2O"],
         "correct_option":"Glucose et O2","explanation":"La respiration aÃ©robie dÃ©grade le glucose en prÃ©sence d'O2 pour produire de l'Ã©nergie (ATP), du CO2 et de l'H2O."},
        {"id":"682_5","type":"vrai-faux","question":"La respiration et la photosynthÃ¨se sont des processus opposÃ©s qui se complementent dans les cycles biogÃ©ochimiques.",
         "correct":True,"explanation":"La photosynthÃ¨se absorbe CO2 et libÃ¨re O2 ; la respiration absorbe O2 et libÃ¨re CO2, formant un cycle complÃ©mentaire."},
        {"id":"682_6","type":"texte","question":"Quel phÃ©nomÃ¨ne permet aux vÃ©gÃ©taux d'Ã©changer les gaz avec l'atmosphÃ¨re via des pores foliaires ?",
         "correct_answer":"transpiration stomate","explanation":"Les stomates (pores foliaires) permettent les Ã©changes gazeux (CO2, O2, H2O) entre la feuille et l'atmosphÃ¨re."},
        {"id":"682_7","type":"qcm","question":"Quel est le rÃ´le des forÃªts tropicales dans le cycle du carbone ?",
         "options":["Elles libÃ¨rent plus de CO2 qu'elles n'en absorbent","Elles constituent un puits de carbone en absorbant le CO2 par photosynthÃ¨se","Elles ne participent pas au cycle du carbone","Elles produisent du mÃ©thane uniquement"],
         "correct_option":"Elles constituent un puits de carbone en absorbant le CO2 par photosynthÃ¨se",
         "explanation":"Les forÃªts tropicales sont des puits de carbone majeurs, absorbant environ 2,5 milliards de tonnes de CO2 par an."},
        {"id":"682_8","type":"vrai-faux","question":"La dÃ©forestation libÃ¨re du CO2 stockÃ© dans la biomasse vÃ©gÃ©tale.",
         "correct":True,"explanation":"Lors de la dÃ©forestation (coupe ou brÃ»lage), le carbone stockÃ© dans les arbres est rejetÃ© sous forme de CO2 dans l'atmosphÃ¨re."},
    ]),

    (683, "Ã‰cologie : changement climatique", "SVT", "3Ã¨me", [
        {"id":"683_1","type":"qcm","question":"Quel est le principal gaz Ã  effet de serre d'origine anthropique ?",
         "options":["O2","CH4","CO2","N2O"],"correct_option":"CO2",
         "explanation":"Le CO2 est le principal GES Ã©mis par les activitÃ©s humaines (combustion fossile, dÃ©forestation), reprÃ©sentant ~76% des Ã©missions mondiales."},
        {"id":"683_2","type":"vrai-faux","question":"L'effet de serre naturel est indispensable Ã  la vie sur Terre.",
         "correct":True,"explanation":"Sans effet de serre naturel, la tempÃ©rature moyenne terrestre serait d'environ -18Â°C au lieu de +15Â°C."},
        {"id":"683_3","type":"texte","question":"Quel organisme international rÃ©unissant des milliers de scientifiques Ã©value les risques du changement climatique ?",
         "correct_answer":"GIEC","explanation":"Le GIEC (Groupe d'experts Intergouvernemental sur l'Ã‰volution du Climat) synthÃ©tise les connaissances scientifiques sur le changement climatique."},
        {"id":"683_4","type":"qcm","question":"Quelle est la principale consÃ©quence de la fonte des glaces polaires ?",
         "options":["Augmentation de la salinitÃ© des ocÃ©ans","Ã‰lÃ©vation du niveau des mers","Diminution de la chaleur ocÃ©anique","Augmentation de la photosynthÃ¨se"],
         "correct_option":"Ã‰lÃ©vation du niveau des mers","explanation":"La fonte des glaces terrestres (glaciers, calotte arctique et antarctique) contribue Ã  l'Ã©lÃ©vation du niveau des ocÃ©ans."},
        {"id":"683_5","type":"vrai-faux","question":"L'acidification des ocÃ©ans est liÃ©e Ã  l'absorption du CO2 atmosphÃ©rique par l'eau de mer.",
         "correct":True,"explanation":"L'absorption de CO2 par les ocÃ©ans forme de l'acide carbonique, abaissant le pH de l'eau de mer et menaÃ§ant les organismes Ã  coquilles calcaires."},
        {"id":"683_6","type":"texte","question":"Comment appelle-t-on l'accord international adoptÃ© en 2015 visant Ã  limiter le rÃ©chauffement climatique Ã  moins de 2Â°C ?",
         "correct_answer":"Accord de Paris","explanation":"L'Accord de Paris (COP21) engage les Ã‰tats signataires Ã  rÃ©duire leurs Ã©missions de GES pour limiter le rÃ©chauffement Ã  1,5-2Â°C."},
        {"id":"683_7","type":"qcm","question":"Quel phÃ©nomÃ¨ne amplifie le rÃ©chauffement climatique en rÃ©duisant la surface rÃ©flÃ©chissante des glaces ?",
         "options":["AlbÃ©do positif","RÃ©troaction glace-albÃ©do","Effet de serre naturel","Cycle de Milankovitch"],
         "correct_option":"RÃ©troaction glace-albÃ©do","explanation":"La fonte des glaces rÃ©duit l'albÃ©do (rÃ©flexion) et augmente l'absorption solaire, amplifiant le rÃ©chauffement (boucle de rÃ©troaction positive)."},
        {"id":"683_8","type":"vrai-faux","question":"Les Ã©nergies renouvelables permettent de rÃ©duire les Ã©missions de CO2 liÃ©es Ã  la production d'Ã©lectricitÃ©.",
         "correct":True,"explanation":"Les Ã©nergies solaire, Ã©olienne, hydraulique et gÃ©othermique Ã©mettent trÃ¨s peu de GES par rapport aux centrales Ã  combustibles fossiles."},
    ]),

    (684, "Ã‰cologie : dÃ©veloppement durable et actions humaines", "SVT", "3Ã¨me", [
        {"id":"684_1","type":"qcm","question":"Quelle est la dÃ©finition du dÃ©veloppement durable selon le rapport Brundtland (1987) ?",
         "options":["Un dÃ©veloppement qui ne pollue pas","Un dÃ©veloppement qui rÃ©pond aux besoins du prÃ©sent sans compromettre ceux des gÃ©nÃ©rations futures","Un dÃ©veloppement Ã©conomique maximal","Un dÃ©veloppement uniquement Ã©cologique"],
         "correct_option":"Un dÃ©veloppement qui rÃ©pond aux besoins du prÃ©sent sans compromettre ceux des gÃ©nÃ©rations futures",
         "explanation":"La dÃ©finition du dÃ©veloppement durable intÃ¨gre trois piliers : Ã©conomique, social et environnemental."},
        {"id":"684_2","type":"vrai-faux","question":"Le recyclage des dÃ©chets contribue Ã  rÃ©duire l'exploitation des ressources naturelles.",
         "correct":True,"explanation":"Le recyclage permet de rÃ©introduire des matÃ©riaux dans le cycle de production, rÃ©duisant l'extraction de nouvelles ressources."},
        {"id":"684_3","type":"texte","question":"Quel sigle dÃ©signe les 17 objectifs mondiaux adoptÃ©s par l'ONU en 2015 pour un avenir durable ?",
         "correct_answer":"ODD","explanation":"Les ODD (Objectifs de DÃ©veloppement Durable) de l'ONU couvrent la pauvretÃ©, la santÃ©, l'Ã©ducation, l'environnement, etc."},
        {"id":"684_4","type":"qcm","question":"Comment appelle-t-on l'ensemble des polluants chimiques persistants qui s'accumulent dans les chaÃ®nes alimentaires ?",
         "options":["Polluants volatils","Polluants organiques persistants (POP)","Pesticides biodÃ©gradables","Endocrine disruptors uniquement"],
         "correct_option":"Polluants organiques persistants (POP)","explanation":"Les POP (ex. DDT, PCB, dioxines) s'accumulent dans les graisses et se concentrent Ã  chaque niveau de la chaÃ®ne alimentaire."},
        {"id":"684_5","type":"vrai-faux","question":"L'agriculture intensive peut provoquer l'eutrophisation des cours d'eau.",
         "correct":True,"explanation":"Les nitrates et phosphates des engrais s'Ã©coulent dans les cours d'eau et favorisent la prolifÃ©ration d'algues (eutrophisation)."},
        {"id":"684_6","type":"texte","question":"Comment appelle-t-on la dÃ©marche qui vise Ã  rÃ©duire les dÃ©chets en les transformant en ressources pour d'autres activitÃ©s ?",
         "correct_answer":"Ã©conomie circulaire","explanation":"L'Ã©conomie circulaire s'oppose Ã  l'Ã©conomie linÃ©aire (extraire-produire-jeter) en minimisant les dÃ©chets et optimisant les ressources."},
        {"id":"684_7","type":"qcm","question":"Quel phÃ©nomÃ¨ne est causÃ© par les Ã©missions de gaz CFC dans la stratosphÃ¨re ?",
         "options":["RÃ©chauffement climatique","Destruction de la couche d'ozone","Pluies acides","Eutrophisation"],
         "correct_option":"Destruction de la couche d'ozone","explanation":"Les CFC (chlorofluorocarbones) libÃ¨rent du chlore qui dÃ©truit l'ozone stratosphÃ©rique protecteur contre les UV."},
        {"id":"684_8","type":"vrai-faux","question":"Le Protocole de MontrÃ©al (1987) a rÃ©ussi Ã  rÃ©duire les substances dÃ©truisant la couche d'ozone.",
         "correct":True,"explanation":"Le Protocole de MontrÃ©al a permis de bannir progressivement les CFC ; la couche d'ozone montre des signes de rÃ©cupÃ©ration."},
    ]),

    (685, "Ã‰cologie : eau et cycles biogÃ©ochimiques", "SVT", "3Ã¨me", [
        {"id":"685_1","type":"qcm","question":"Quel est le moteur principal du cycle de l'eau ?",
         "options":["Les volcans","L'Ã©nergie solaire et la gravitÃ©","Les vents polaires","La dÃ©forestation"],
         "correct_option":"L'Ã©nergie solaire et la gravitÃ©","explanation":"L'Ã©nergie solaire provoque l'Ã©vaporation ; la gravitÃ© assure le retour des prÃ©cipitations vers les ocÃ©ans et les cours d'eau."},
        {"id":"685_2","type":"vrai-faux","question":"La transpiration des plantes contribue au cycle de l'eau.",
         "correct":True,"explanation":"L'Ã©vapotranspiration (Ã©vaporation du sol + transpiration des plantes) reprÃ©sente une part significative du flux d'eau vers l'atmosphÃ¨re."},
        {"id":"685_3","type":"texte","question":"Comment appelle-t-on la transformation de l'ammoniac en nitrates par des bactÃ©ries du sol ?",
         "correct_answer":"nitrification","explanation":"La nitrification est rÃ©alisÃ©e par des bactÃ©ries nitrifiantes (Nitrosomonas, Nitrobacter) qui oxydent l'ammoniac en nitrites puis en nitrates."},
        {"id":"685_4","type":"qcm","question":"Quel processus bactÃ©rien transforme les nitrates du sol en azote gazeux (N2) atmosphÃ©rique ?",
         "options":["Fixation de l'azote","Nitrification","DÃ©nitrification","Ammonification"],
         "correct_option":"DÃ©nitrification","explanation":"La dÃ©nitrification (bactÃ©ries anaÃ©robies) convertit les nitrates en N2, refermant le cycle de l'azote."},
        {"id":"685_5","type":"vrai-faux","question":"Les ocÃ©ans constituent le principal rÃ©servoir d'eau douce sur Terre.",
         "correct":False,"explanation":"Les ocÃ©ans contiennent de l'eau salÃ©e. Les rÃ©servoirs d'eau douce sont principalement les glaces polaires et les eaux souterraines."},
        {"id":"685_6","type":"texte","question":"Comment appelle-t-on le stockage souterrain d'eau dans des couches poreuses de roches ?",
         "correct_answer":"nappe phrÃ©atique","explanation":"La nappe phrÃ©atique est une accumulation d'eau dans un aquifÃ¨re (couche souterraine permÃ©able), alimentÃ©e par l'infiltration des eaux de pluie."},
        {"id":"685_7","type":"qcm","question":"Quelle activitÃ© humaine contribue le plus Ã  la pollution des nappes phrÃ©atiques ?",
            "options":["L'agriculture intensive","La pÃªche","Le tourisme","L'industrie du textile"],
            "correct_option":"L'agriculture intensive","explanation":"L'utilisation excessive d'engrais et de pesticides en agriculture peut contaminer les nappes phrÃ©atiques par lixiviation."},
        {"id":"685_8","type":"vrai-faux","question":"Le cycle du phosphore n'inclut pas de phase gazeuse.",
         "correct":True,"explanation":"Le phosphore circule principalement dans les roches, les sols et les organismes vivants, sans phase gazeuse significative."},
    ]),

    (686, "Ã‰cologie : biodiversitÃ© et services Ã©cosystÃ©miques", "SVT", "3Ã¨me", [
        {"id":"686_1","type":"qcm","question":"Que dÃ©signe un service Ã©cosystÃ©mique ?",
         "options":["Une aide financiÃ¨re pour les parcs naturels","Un bÃ©nÃ©fice rendu par les Ã©cosystÃ¨mes aux sociÃ©tÃ©s humaines","Un programme scolaire sur l'Ã©cologie","Une pollution naturelle"],
         "correct_option":"Un bÃ©nÃ©fice rendu par les Ã©cosystÃ¨mes aux sociÃ©tÃ©s humaines","explanation":"Les Ã©cosystÃ¨mes fournissent des services d'approvisionnement, de rÃ©gulation, de soutien et culturels indispensables Ã  l'humanitÃ©."},
        {"id":"686_2","type":"vrai-faux","question":"La pollinisation par les insectes est un exemple de service Ã©cosystÃ©mique.",
         "correct":True,"explanation":"Les insectes pollinisateurs permettent la reproduction de nombreuses plantes cultivÃ©es et sauvages, soutenant la production alimentaire."},
        {"id":"686_3","type":"texte","question":"Comment appelle-t-on la disparition locale d'une espÃ¨ce due Ã  la fragmentation ou Ã  la destruction de son habitat ?",
         "correct_answer":"extinction locale","explanation":"La perte d'habitat peut conduire Ã  l'extinction locale d'une espÃ¨ce avant une Ã©ventuelle disparition globale."},
        {"id":"686_4","type":"qcm","question":"Quel milieu naturel stocke beaucoup de carbone et protÃ¨ge les cÃ´tes contre les tempÃªtes ?",
         "options":["La toundra alpine","La mangrove","Le dÃ©sert chaud","La savane sÃ¨che"],
         "correct_option":"La mangrove","explanation":"Les mangroves stockent du carbone dans leurs sÃ©diments, servent de nurseries pour de nombreuses espÃ¨ces et protÃ¨gent les littoraux."},
        {"id":"686_5","type":"vrai-faux","question":"La disparition d'une espÃ¨ce clÃ© de voÃ»te peut dÃ©sÃ©quilibrer tout un Ã©cosystÃ¨me.",
         "correct":True,"explanation":"Certaines espÃ¨ces ont un rÃ´le Ã©cologique majeur disproportionnÃ© Ã  leur abondance ; leur disparition peut entraÃ®ner des cascades trophiques."},
        {"id":"686_6","type":"texte","question":"Le fait de protÃ©ger un milieu naturel pour prÃ©server les espÃ¨ces et leurs interactions correspond Ã  la _____ de la biodiversitÃ©.",
         "correct_answer":"conservation","explanation":"La conservation repose sur la protection des espÃ¨ces, des habitats et des Ã©quilibres Ã©cologiques."},
        {"id":"686_7","type":"qcm","question":"Quel exemple illustre un service de rÃ©gulation rendu par les Ã©cosystÃ¨mes ?",
         "options":["La fabrication de plastique","La rÃ©gulation des crues par les zones humides","La production d'acier","La construction de barrages"],
         "correct_option":"La rÃ©gulation des crues par les zones humides","explanation":"Les zones humides absorbent l'eau lors des fortes pluies, limitent les inondations et filtrent une partie des polluants."},
        {"id":"686_8","type":"vrai-faux","question":"La biodiversitÃ© renforce gÃ©nÃ©ralement la rÃ©silience des Ã©cosystÃ¨mes face aux perturbations.",
         "correct":True,"explanation":"Un Ã©cosystÃ¨me riche en espÃ¨ces et en interactions dispose souvent de plus de capacitÃ©s d'adaptation aprÃ¨s une perturbation."},
    ]),

    (687, "Ã‰cologie : pollution, dÃ©chets et santÃ© environnementale", "SVT", "3Ã¨me", [
        {"id":"687_1","type":"qcm","question":"Quelle pollution provoque l'accumulation de nitrates et de phosphates dans un lac, favorisant la prolifÃ©ration d'algues ?",
         "options":["L'acidification","L'eutrophisation","La dÃ©sertification","La sÃ©dimentation glaciaire"],
         "correct_option":"L'eutrophisation","explanation":"L'excÃ¨s de nutriments stimule la prolifÃ©ration d'algues puis l'appauvrissement en dioxygÃ¨ne de l'eau."},
        {"id":"687_2","type":"vrai-faux","question":"Les microplastiques peuvent Ãªtre ingÃ©rÃ©s par des organismes aquatiques et entrer dans les chaÃ®nes alimentaires.",
         "correct":True,"explanation":"Les microplastiques sont consommÃ©s par le plancton, les poissons et d'autres organismes, avec des effets potentiels sur la santÃ© des Ã©cosystÃ¨mes."},
        {"id":"687_3","type":"texte","question":"Comment appelle-t-on le tri, la collecte et la transformation de dÃ©chets en nouvelles matiÃ¨res premiÃ¨res ?",
         "correct_answer":"recyclage","explanation":"Le recyclage permet de rÃ©utiliser certains matÃ©riaux et de limiter l'extraction de nouvelles ressources."},
        {"id":"687_4","type":"qcm","question":"Quel polluant atmosphÃ©rique est majoritairement Ã©mis par les moteurs thermiques et les chauffages au fioul ou au bois mal rÃ©glÃ©s ?",
         "options":["Le diazote","Les particules fines","Le nÃ©on","L'hÃ©lium"],
         "correct_option":"Les particules fines","explanation":"Les particules fines pÃ©nÃ¨trent profondÃ©ment dans les voies respiratoires et augmentent les risques cardiovasculaires et respiratoires."},
        {"id":"687_5","type":"vrai-faux","question":"RÃ©duire, rÃ©utiliser puis recycler est un ordre cohÃ©rent pour limiter les dÃ©chets.",
         "correct":True,"explanation":"La hiÃ©rarchie des dÃ©chets privilÃ©gie d'abord la rÃ©duction Ã  la source, puis la rÃ©utilisation et enfin le recyclage."},
        {"id":"687_6","type":"texte","question":"Le fait qu'un polluant se concentre progressivement d'un maillon Ã  l'autre d'une chaÃ®ne alimentaire s'appelle la _____.",
         "correct_answer":"bioaccumulation","explanation":"Lorsqu'un polluant persistant s'accumule dans les tissus et augmente de concentration Ã  chaque niveau trophique, on parle de bioaccumulation ou biomagnification."},
        {"id":"687_7","type":"qcm","question":"Quel geste rÃ©duit directement la pollution plastique des ocÃ©ans ?",
         "options":["Jeter les dÃ©chets dans la nature","PrivilÃ©gier les objets rÃ©utilisables et trier correctement","BrÃ»ler les emballages chez soi","Enterrer les bouteilles"],
         "correct_option":"PrivilÃ©gier les objets rÃ©utilisables et trier correctement","explanation":"Limiter les plastiques Ã  usage unique et amÃ©liorer le tri diminue les rejets dans l'environnement."},
        {"id":"687_8","type":"vrai-faux","question":"La pollution lumineuse peut perturber le comportement de nombreuses espÃ¨ces nocturnes.",
         "correct":True,"explanation":"La lumiÃ¨re artificielle modifie les dÃ©placements, la reproduction et la prÃ©dation chez de nombreux insectes, oiseaux et mammifÃ¨res nocturnes."},
    ]),

    (688, "Ã‰cologie : Ã©nergies, transition et empreinte carbone", "SVT", "3Ã¨me", [
        {"id":"688_1","type":"qcm","question":"Qu'appelle-t-on empreinte carbone ?",
         "options":["Le poids d'un vÃ©hicule","La quantitÃ© totale de gaz Ã  effet de serre Ã©mise par une activitÃ©, une personne ou un produit","La surface de forÃªt nÃ©cessaire Ã  une ville","Le nombre de centrales Ã©lectriques d'un pays"],
         "correct_option":"La quantitÃ© totale de gaz Ã  effet de serre Ã©mise par une activitÃ©, une personne ou un produit","explanation":"L'empreinte carbone additionne les Ã©missions directes et indirectes de gaz Ã  effet de serre, souvent exprimÃ©es en Ã©quivalent CO2."},
        {"id":"688_2","type":"vrai-faux","question":"L'isolation thermique des bÃ¢timents permet de rÃ©duire la consommation d'Ã©nergie et les Ã©missions de CO2 associÃ©es au chauffage.",
         "correct":True,"explanation":"Un bÃ¢timent bien isolÃ© perd moins de chaleur, ce qui rÃ©duit les besoins en chauffage et les Ã©missions liÃ©es Ã  l'Ã©nergie utilisÃ©e."},
        {"id":"688_3","type":"texte","question":"Comment appelle-t-on l'Ã©nergie produite par le dÃ©placement de l'air dans des turbines ?",
         "correct_answer":"Ã©nergie Ã©olienne","explanation":"Les Ã©oliennes transforment l'Ã©nergie cinÃ©tique du vent en Ã©lectricitÃ©."},
        {"id":"688_4","type":"qcm","question":"Quel secteur Ã©met une part importante des gaz Ã  effet de serre liÃ©s aux dÃ©placements quotidiens ?",
         "options":["Le transport","La lecture","Le sport scolaire","La photographie"],
         "correct_option":"Le transport","explanation":"Les transports routiers, aÃ©riens et maritimes contribuent fortement aux Ã©missions mondiales de gaz Ã  effet de serre."},
        {"id":"688_5","type":"vrai-faux","question":"Remplacer un trajet en voiture individuelle par un trajet Ã  vÃ©lo ou en transport en commun rÃ©duit gÃ©nÃ©ralement l'empreinte carbone.",
         "correct":True,"explanation":"Les mobilitÃ©s actives et collectives sont souvent beaucoup moins Ã©mettrices par passager que la voiture individuelle."},
        {"id":"688_6","type":"texte","question":"La rÃ©duction des Ã©missions afin d'atteindre un Ã©quilibre entre Ã©missions et absorptions de CO2 est appelÃ©e neutralitÃ© _____.",
         "correct_answer":"carbone","explanation":"La neutralitÃ© carbone vise Ã  limiter fortement les Ã©missions rÃ©siduelles et Ã  compenser le reste par des puits de carbone."},
        {"id":"688_7","type":"qcm","question":"Quel avantage majeur prÃ©sentent les Ã©nergies renouvelables par rapport aux combustibles fossiles ?",
         "options":["Elles sont toujours disponibles sans variation","Elles Ã©mettent gÃ©nÃ©ralement moins de gaz Ã  effet de serre sur l'ensemble de leur cycle de vie","Elles produisent toutes du carburant liquide","Elles n'occupent aucun espace"],
         "correct_option":"Elles Ã©mettent gÃ©nÃ©ralement moins de gaz Ã  effet de serre sur l'ensemble de leur cycle de vie","explanation":"MÃªme si elles nÃ©cessitent des matÃ©riaux et des infrastructures, leurs Ã©missions globales restent bien infÃ©rieures Ã  celles du charbon, du pÃ©trole ou du gaz."},
        {"id":"688_8","type":"vrai-faux","question":"La sobriÃ©tÃ© Ã©nergÃ©tique consiste Ã  rÃ©duire les besoins d'Ã©nergie par les usages et l'organisation des activitÃ©s.",
         "correct":True,"explanation":"La sobriÃ©tÃ© complÃ¨te l'efficacitÃ© Ã©nergÃ©tique en agissant sur les comportements, les Ã©quipements et les choix collectifs."},
    ]),

    (689, "Ã‰cologie : risques naturels et prÃ©vention", "SVT", "3Ã¨me", [
        {"id":"689_1","type":"qcm","question":"Qu'est-ce qu'un risque naturel ?",
         "options":["Un danger exclusivement crÃ©Ã© par l'industrie","La rencontre entre un alÃ©a naturel et des enjeux humains vulnÃ©rables","Un Ã©vÃ©nement impossible Ã  prÃ©voir","Un phÃ©nomÃ¨ne sans consÃ©quence pour l'Homme"],
         "correct_option":"La rencontre entre un alÃ©a naturel et des enjeux humains vulnÃ©rables","explanation":"Le risque dÃ©pend de l'alÃ©a, de l'exposition des populations et de leur vulnÃ©rabilitÃ©."},
        {"id":"689_2","type":"vrai-faux","question":"Construire dans le lit majeur d'un fleuve augmente la vulnÃ©rabilitÃ© face aux inondations.",
         "correct":True,"explanation":"L'urbanisation des zones inondables expose davantage de personnes et de biens aux crues."},
        {"id":"689_3","type":"texte","question":"Comment appelle-t-on une grande masse de neige qui dÃ©vale une pente de montagne ?",
         "correct_answer":"avalanche","explanation":"Les avalanches sont des mouvements rapides de neige pouvant menacer les zones de montagne et les infrastructures."},
        {"id":"689_4","type":"qcm","question":"Quel outil permet aux autoritÃ©s de limiter les constructions dans les zones exposÃ©es Ã  certains alÃ©as naturels ?",
         "options":["Le bulletin mÃ©tÃ©o tÃ©lÃ©visÃ©","Le plan de prÃ©vention des risques","Le calendrier scolaire","Le permis de pÃªche"],
         "correct_option":"Le plan de prÃ©vention des risques","explanation":"Les plans de prÃ©vention des risques encadrent l'amÃ©nagement du territoire selon les alÃ©as identifiÃ©s."},
        {"id":"689_5","type":"vrai-faux","question":"Les systÃ¨mes d'alerte prÃ©coce et l'information des populations peuvent rÃ©duire le nombre de victimes lors d'une catastrophe naturelle.",
         "correct":True,"explanation":"Les alertes, les Ã©vacuations et la prÃ©paration des habitants permettent de limiter l'impact humain des catastrophes."},
        {"id":"689_6","type":"texte","question":"L'action de replanter des arbres sur des pentes fragiles pour limiter l'Ã©rosion et les glissements de terrain correspond au _____.",
         "correct_answer":"reboisement","explanation":"Le reboisement stabilise les sols, ralentit le ruissellement et limite certains mouvements de terrain."},
        {"id":"689_7","type":"qcm","question":"Dans les rÃ©gions littorales, quel phÃ©nomÃ¨ne peut accentuer le risque d'inondation marine ?",
         "options":["La montÃ©e du niveau de la mer","La baisse de l'ensoleillement","La prÃ©sence d'algues vertes","La rotation de la Terre"],
         "correct_option":"La montÃ©e du niveau de la mer","explanation":"L'Ã©lÃ©vation du niveau marin augmente la frÃ©quence et l'intensitÃ© potentielles des submersions cÃ´tiÃ¨res."},
        {"id":"689_8","type":"vrai-faux","question":"Le changement climatique peut modifier la frÃ©quence ou l'intensitÃ© de certains alÃ©as naturels.",
         "correct":True,"explanation":"Les vagues de chaleur, sÃ©cheresses, pluies intenses et incendies sont dÃ©jÃ  influencÃ©s dans plusieurs rÃ©gions par le rÃ©chauffement climatique."},
    ]),

    # =========================================================
    # BLOC 7 â€“ RÃ‰VISIONS (690â€“698)
    # =========================================================
    (690, "RÃ©visions : gÃ©nÃ©tique et hÃ©rÃ©ditÃ©", "SVT", "3Ã¨me", [
        {"id":"690_1","type":"qcm","question":"Quel ensemble rÃ©unit la totalitÃ© des chromosomes d'un individu classÃ©s par paires ?",
         "options":["Un clone","Un caryotype","Un phÃ©notype","Un allÃ¨le"],
         "correct_option":"Un caryotype","explanation":"Le caryotype permet d'observer le nombre et la structure des chromosomes, utile pour repÃ©rer certaines anomalies chromosomiques."},
        {"id":"690_2","type":"vrai-faux","question":"Une mutation sur une cellule germinale peut Ãªtre transmise Ã  la descendance.",
         "correct":True,"explanation":"Les cellules germinales sont Ã  l'origine des gamÃ¨tes ; une mutation qui les touche peut donc Ãªtre hÃ©ritÃ©e."},
        {"id":"690_3","type":"texte","question":"Le croisement de deux individus hÃ©tÃ©rozygotes pour un caractÃ¨re monogÃ©nique peut Ãªtre reprÃ©sentÃ© avec une grille de _____.",
         "correct_answer":"Punnett","explanation":"La grille de Punnett permet de prÃ©voir les proportions gÃ©notypiques et phÃ©notypiques attendues chez la descendance."},
        {"id":"690_4","type":"qcm","question":"Chez l'Ãªtre humain, combien de chromosomes contient un gamÃ¨te normal ?",
         "options":["23","46","69","92"],
         "correct_option":"23","explanation":"Les gamÃ¨tes sont haploÃ¯des et contiennent un seul exemplaire de chaque chromosome."},
        {"id":"690_5","type":"vrai-faux","question":"Le phÃ©notype dÃ©pend uniquement du gÃ©notype et jamais de l'environnement.",
         "correct":False,"explanation":"L'environnement peut modifier l'expression de certains caractÃ¨res, donc le phÃ©notype final."},
        {"id":"690_6","type":"texte","question":"Quand un allÃ¨le masque l'expression d'un autre allÃ¨le chez un hÃ©tÃ©rozygote, on dit qu'il est _____.",
         "correct_answer":"dominant","explanation":"L'allÃ¨le dominant s'exprime chez l'hÃ©tÃ©rozygote alors que l'allÃ¨le rÃ©cessif ne s'exprime qu'en double exemplaire."},
        {"id":"690_7","type":"qcm","question":"Quel processus cellulaire conserve le nombre de chromosomes d'une cellule somatique Ã  l'autre ?",
         "options":["La mÃ©iose","La mitose","La fÃ©condation","La mutation"],
         "correct_option":"La mitose","explanation":"La mitose produit deux cellules filles gÃ©nÃ©tiquement trÃ¨s proches de la cellule mÃ¨re, avec le mÃªme nombre de chromosomes."},
        {"id":"690_8","type":"vrai-faux","question":"Deux vrais jumeaux proviennent du mÃªme zygote initial.",
         "correct":True,"explanation":"Les vrais jumeaux rÃ©sultent de la sÃ©paration d'un mÃªme embryon prÃ©coce ; ils partagent presque le mÃªme gÃ©nome."},
    ]),

    (691, "RÃ©visions : Ã©volution et classification", "SVT", "3Ã¨me", [
        {"id":"691_1","type":"qcm","question":"La sÃ©lection naturelle agit principalement sur :",
         "options":["Les gÃ¨nes sans effet sur le phÃ©notype","Les caractÃ¨res hÃ©rÃ©ditaires influenÃ§ant survie et reproduction","Les habitudes acquises pendant la vie","Les besoins conscients des individus"],
         "correct_option":"Les caractÃ¨res hÃ©rÃ©ditaires influenÃ§ant survie et reproduction","explanation":"Les individus porteurs de variations avantageuses laissent en moyenne plus de descendants."},
        {"id":"691_2","type":"vrai-faux","question":"Un clade rassemble un ancÃªtre commun et seulement une partie de ses descendants.",
         "correct":False,"explanation":"Un clade comprend un ancÃªtre commun et tous ses descendants."},
        {"id":"691_3","type":"texte","question":"Les ressemblances hÃ©ritÃ©es d'un ancÃªtre commun entre deux organes sont appelÃ©es organes _____.",
         "correct_answer":"homologues","explanation":"Les organes homologues ont une mÃªme origine Ã©volutive mÃªme si leurs fonctions actuelles peuvent Ãªtre diffÃ©rentes."},
        {"id":"691_4","type":"qcm","question":"Quel fossile de transition est souvent citÃ© entre certains dinosaures et les oiseaux ?",
         "options":["Trilobite","Archaeopteryx","Lucy","Mammouth laineux"],
         "correct_option":"Archaeopteryx","explanation":"Archaeopteryx combine des caractÃ¨res reptiliens et aviens, ce qui en fait un fossile de transition cÃ©lÃ¨bre."},
        {"id":"691_5","type":"vrai-faux","question":"L'Ã©volution peut conduire Ã  la disparition d'espÃ¨ces aussi bien qu'Ã  l'apparition de nouvelles espÃ¨ces.",
         "correct":True,"explanation":"L'histoire du vivant comprend des extinctions, des radiations Ã©volutives et des spÃ©ciations continues."},
        {"id":"691_6","type":"texte","question":"La science qui reconstitue les liens de parentÃ© entre espÃ¨ces Ã  partir de caractÃ¨res partagÃ©s s'appelle la _____.",
         "correct_answer":"phylogÃ©nie","explanation":"La phylogÃ©nie cherche Ã  reconstruire l'histoire Ã©volutive et les relations de parentÃ© entre les Ãªtres vivants."},
        {"id":"691_7","type":"qcm","question":"Pourquoi les bactÃ©ries rÃ©sistantes deviennent-elles plus frÃ©quentes aprÃ¨s un traitement antibiotique mal conduit ?",
         "options":["Parce qu'elles apprennent Ã  rÃ©sister","Parce que l'antibiotique sÃ©lectionne les bactÃ©ries dÃ©jÃ  rÃ©sistantes","Parce que le mÃ©dicament les transforme toutes","Parce qu'elles cessent de se reproduire"],
         "correct_option":"Parce que l'antibiotique sÃ©lectionne les bactÃ©ries dÃ©jÃ  rÃ©sistantes","explanation":"L'antibiotique Ã©limine les bactÃ©ries sensibles et laisse davantage de place aux souches rÃ©sistantes pour se multiplier."},
        {"id":"691_8","type":"vrai-faux","question":"La classification phylogÃ©nÃ©tique repose sur des liens de parentÃ© et non sur la seule ressemblance extÃ©rieure.",
         "correct":True,"explanation":"Des espÃ¨ces peuvent se ressembler par convergence Ã©volutive sans Ãªtre proches parentes."},
    ]),

    (692, "RÃ©visions : immunologie et vaccination", "SVT", "3Ã¨me", [
        {"id":"692_1","type":"qcm","question":"Quel Ã©lÃ©ment du systÃ¨me immunitaire reconnaÃ®t spÃ©cifiquement un antigÃ¨ne donnÃ© ?",
         "options":["Tous les globules rouges","Les anticorps et certains lymphocytes","Les plaquettes","L'hÃ©moglobine"],
         "correct_option":"Les anticorps et certains lymphocytes","explanation":"L'immunitÃ© adaptative repose sur une reconnaissance spÃ©cifique des antigÃ¨nes par les lymphocytes B et T."},
        {"id":"692_2","type":"vrai-faux","question":"L'inflammation est une rÃ©ponse rapide de l'immunitÃ© innÃ©e.",
         "correct":True,"explanation":"L'inflammation fait partie des premiÃ¨res dÃ©fenses de l'organisme face Ã  une agression ou une infection."},
        {"id":"692_3","type":"texte","question":"Une personne vaccinÃ©e dÃ©veloppe des cellules de mÃ©moire capables de rÃ©agir plus vite lors d'une future _____.",
         "correct_answer":"infection","explanation":"La mÃ©moire immunitaire accÃ©lÃ¨re et renforce la rÃ©ponse lors d'un nouveau contact avec le mÃªme agent pathogÃ¨ne."},
        {"id":"692_4","type":"qcm","question":"Le VIH fragilise surtout l'organisme en dÃ©truisant :",
         "options":["Les globules rouges","Les lymphocytes T CD4","Les neurones moteurs","Les cellules de peau"],
         "correct_option":"Les lymphocytes T CD4","explanation":"La diminution des lymphocytes T CD4 affaiblit l'ensemble de la rÃ©ponse immunitaire."},
        {"id":"692_5","type":"vrai-faux","question":"Un sÃ©rum apporte une immunitÃ© passive immÃ©diate mais gÃ©nÃ©ralement temporaire.",
         "correct":True,"explanation":"Le sÃ©rum fournit directement des anticorps dÃ©jÃ  fabriquÃ©s, sans crÃ©er de mÃ©moire immunitaire durable."},
        {"id":"692_6","type":"texte","question":"L'action consistant Ã  engloutir et dÃ©truire un microbe par certaines cellules immunitaires s'appelle la _____.",
         "correct_answer":"phagocytose","explanation":"Les phagocytes internalisent les agents pathogÃ¨nes puis les digÃ¨rent grÃ¢ce Ã  des enzymes."},
        {"id":"692_7","type":"qcm","question":"Pourquoi les antibiotiques ne doivent-ils pas Ãªtre utilisÃ©s contre la grippe ?",
         "options":["Parce qu'ils dÃ©truisent les anticorps","Parce qu'ils agissent sur les bactÃ©ries, pas sur les virus","Parce qu'ils empÃªchent la vaccination","Parce qu'ils augmentent toujours la fiÃ¨vre"],
         "correct_option":"Parce qu'ils agissent sur les bactÃ©ries, pas sur les virus","explanation":"La grippe est une maladie virale, donc les antibiotiques sont inefficaces sauf en cas de surinfection bactÃ©rienne."},
        {"id":"692_8","type":"vrai-faux","question":"Le respect du calendrier vaccinal contribue aussi Ã  protÃ©ger les personnes fragiles non vaccinables.",
         "correct":True,"explanation":"Une forte couverture vaccinale rÃ©duit la circulation des agents infectieux dans la population."},
    ]),

    (693, "RÃ©visions : reproduction humaine", "SVT", "3Ã¨me", [
        {"id":"693_1","type":"qcm","question":"Quel Ã©vÃ©nement marque le dÃ©but de la grossesse au sens biologique ?",
         "options":["La pubertÃ©","La fÃ©condation","La menstruation","La mÃ©nopause"],
         "correct_option":"La fÃ©condation","explanation":"La grossesse dÃ©bute par la rencontre d'un spermatozoÃ¯de et d'un ovocyte, formant un zygote."},
        {"id":"693_2","type":"vrai-faux","question":"Le prÃ©servatif est Ã  la fois un moyen de contraception et une protection contre de nombreuses IST.",
         "correct":True,"explanation":"C'est la seule mÃ©thode contraceptive qui protÃ¨ge aussi contre la transmission de nombreuses infections sexuellement transmissibles."},
        {"id":"693_3","type":"texte","question":"La libÃ©ration d'un ovocyte par l'ovaire au cours du cycle fÃ©minin s'appelle l'_____.",
         "correct_answer":"ovulation","explanation":"L'ovulation survient en gÃ©nÃ©ral au milieu du cycle et rend possible la fÃ©condation pendant une courte pÃ©riode."},
        {"id":"693_4","type":"qcm","question":"Quel organe assure les Ã©changes nutritifs et gazeux entre la mÃ¨re et le fÅ“tus ?",
         "options":["Le foie","Le placenta","Le rein","Le pancrÃ©as"],
         "correct_option":"Le placenta","explanation":"Le placenta relie les circulations maternelle et fÅ“tale sans les mÃ©langer directement."},
        {"id":"693_5","type":"vrai-faux","question":"L'alcool consommÃ© pendant la grossesse peut traverser le placenta.",
         "correct":True,"explanation":"L'alcool atteint le fÅ“tus et peut altÃ©rer durablement son dÃ©veloppement neurologique et physique."},
        {"id":"693_6","type":"texte","question":"L'interruption volontaire de grossesse est couramment dÃ©signÃ©e par le sigle _____.",
         "correct_answer":"IVG","explanation":"L'IVG peut Ãªtre mÃ©dicamenteuse ou chirurgicale selon le terme de la grossesse et le cadre lÃ©gal."},
        {"id":"693_7","type":"qcm","question":"Ã€ la pubertÃ©, l'apparition des caractÃ¨res sexuels secondaires est dÃ©clenchÃ©e par :",
         "options":["Les hormones sexuelles","Les anticorps","Les vitamines","Les enzymes digestives"],
         "correct_option":"Les hormones sexuelles","explanation":"Les hormones produites par les gonades sous contrÃ´le cÃ©rÃ©bral modifient progressivement le corps Ã  la pubertÃ©."},
        {"id":"693_8","type":"vrai-faux","question":"La PMA peut inclure une fÃ©condation in vitro rÃ©alisÃ©e en laboratoire.",
         "correct":True,"explanation":"La procrÃ©ation mÃ©dicalement assistÃ©e regroupe plusieurs techniques destinÃ©es Ã  aider un couple ou une personne Ã  concevoir."},
    ]),

    (694, "RÃ©visions : gÃ©ologie interne et tectonique", "SVT", "3Ã¨me", [
        {"id":"694_1","type":"qcm","question":"Quelle couche terrestre est directement au-dessus du noyau ?",
         "options":["La croÃ»te ocÃ©anique","Le manteau","L'atmosphÃ¨re","L'hydrosphÃ¨re"],
         "correct_option":"Le manteau","explanation":"De la surface vers le centre, on trouve la croÃ»te, le manteau puis le noyau."},
        {"id":"694_2","type":"vrai-faux","question":"Les plaques tectoniques reposent sur une lithosphÃ¨re mobile totalement liquide.",
         "correct":False,"explanation":"Les plaques correspondent Ã  la lithosphÃ¨re rigide ; elles se dÃ©placent sur l'asthÃ©nosphÃ¨re plus ductile."},
        {"id":"694_3","type":"texte","question":"La thÃ©orie selon laquelle les continents se dÃ©placent lentement Ã  la surface de la Terre est Ã  l'origine de la thÃ©orie de la dÃ©rive des _____.",
         "correct_answer":"continents","explanation":"La dÃ©rive des continents proposÃ©e par Wegener a prÃ©parÃ© la thÃ©orie moderne de la tectonique des plaques."},
        {"id":"694_4","type":"qcm","question":"Quel phÃ©nomÃ¨ne se produit surtout dans les zones de subduction ?",
         "options":["La formation de nouvelles glaces polaires","Un volcanisme souvent explosif et de forts sÃ©ismes","La disparition de l'atmosphÃ¨re","La formation de coraux"],
         "correct_option":"Un volcanisme souvent explosif et de forts sÃ©ismes","explanation":"La plongÃ©e d'une plaque sous une autre gÃ©nÃ¨re fusion partielle, remontÃ©e de magma et forte activitÃ© sismique."},
        {"id":"694_5","type":"vrai-faux","question":"La croÃ»te ocÃ©anique est en moyenne plus dense que la croÃ»te continentale.",
         "correct":True,"explanation":"Sa composition basaltique la rend plus dense, ce qui favorise sa subduction sous certaines conditions."},
        {"id":"694_6","type":"texte","question":"Les vibrations enregistrÃ©es lors d'un sÃ©isme sont Ã©tudiÃ©es grÃ¢ce Ã  la _____.",
         "correct_answer":"sismologie","explanation":"La sismologie renseigne sur la structure interne du globe et sur la propagation des ondes sismiques."},
        {"id":"694_7","type":"qcm","question":"Quel mouvement de plaques est Ã  l'origine des dorsales ocÃ©aniques ?",
         "options":["Convergence","Divergence","Collision continentale","Obduction"],
         "correct_option":"Divergence","explanation":"Aux dorsales, les plaques s'Ã©cartent et du magma remonte pour former une nouvelle croÃ»te ocÃ©anique."},
        {"id":"694_8","type":"vrai-faux","question":"Les chaÃ®nes de montagnes peuvent se former lors de collisions entre plaques continentales.",
         "correct":True,"explanation":"Les collisions provoquent l'Ã©paississement de la croÃ»te et le soulÃ¨vement de grandes chaÃ®nes de montagnes comme l'Himalaya."},
    ]),

    (695, "RÃ©visions : sÃ©ismes, volcans et risques", "SVT", "3Ã¨me", [
        {"id":"695_1","type":"qcm","question":"Quel nom donne-t-on au point de rupture en profondeur d'un sÃ©isme ?",
         "options":["L'Ã©picentre","Le foyer","Le cratÃ¨re","La faille inversÃ©e"],
         "correct_option":"Le foyer","explanation":"Le foyer, ou hypocentre, est le point de dÃ©part de la rupture ; l'Ã©picentre est sa projection Ã  la surface."},
        {"id":"695_2","type":"vrai-faux","question":"Une nuÃ©e ardente est gÃ©nÃ©ralement plus dangereuse qu'une coulÃ©e de lave effusive.",
         "correct":True,"explanation":"TrÃ¨s chaude et trÃ¨s rapide, une nuÃ©e ardente tue par brÃ»lure, asphyxie et choc mÃ©canique."},
        {"id":"695_3","type":"texte","question":"Le plan d'Ã©vacuation et les consignes transmises Ã  la population relÃ¨vent de la _____ des risques.",
         "correct_answer":"prÃ©vention","explanation":"La prÃ©vention rÃ©duit la vulnÃ©rabilitÃ© grÃ¢ce Ã  l'information, aux exercices, aux normes et Ã  l'amÃ©nagement du territoire."},
        {"id":"695_4","type":"qcm","question":"Quel phÃ©nomÃ¨ne peut suivre un sÃ©isme sous-marin majeur ?",
         "options":["Une mousson","Un tsunami","Une marÃ©e noire","Une Ã©clipse"],
         "correct_option":"Un tsunami","explanation":"Un dÃ©placement brutal du plancher ocÃ©anique peut gÃ©nÃ©rer une onde de trÃ¨s grande longueur d'onde Ã  travers l'ocÃ©an."},
        {"id":"695_5","type":"vrai-faux","question":"Tous les volcans ont des Ã©ruptions identiques en intensitÃ© et en type.",
         "correct":False,"explanation":"Le comportement Ã©ruptif dÃ©pend notamment de la composition du magma, de sa viscositÃ© et de sa teneur en gaz."},
        {"id":"695_6","type":"texte","question":"Les bÃ¢timents conÃ§us pour mieux rÃ©sister aux secousses sont dits _____.",
         "correct_answer":"parasismiques","explanation":"Les constructions parasismiques limitent l'effondrement et protÃ¨gent les habitants dans les zones exposÃ©es."},
        {"id":"695_7","type":"qcm","question":"Quel indice permet surtout de dÃ©crire les dÃ©gÃ¢ts observÃ©s localement aprÃ¨s un sÃ©isme ?",
         "options":["L'intensitÃ© macrosismique","L'albÃ©do","Le pH","La salinitÃ©"],
         "correct_option":"L'intensitÃ© macrosismique","explanation":"L'intensitÃ© dÃ©crit les effets sur les personnes, les bÃ¢timents et le paysage en un lieu donnÃ©."},
        {"id":"695_8","type":"vrai-faux","question":"La surveillance scientifique d'un volcan peut combiner mesures sismiques, dÃ©formation du sol et analyse des gaz.",
         "correct":True,"explanation":"Le croisement de plusieurs indicateurs amÃ©liore l'anticipation des phases d'agitation volcanique."},
    ]),

    (696, "RÃ©visions : Ã©cologie et climat", "SVT", "3Ã¨me", [
        {"id":"696_1","type":"qcm","question":"Quel rÃ´le jouent les producteurs primaires dans un Ã©cosystÃ¨me ?",
         "options":["Ils dÃ©composent les cadavres","Ils fabriquent la matiÃ¨re organique Ã  partir de matiÃ¨re minÃ©rale","Ils mangent les herbivores","Ils dÃ©truisent les champignons"],
         "correct_option":"Ils fabriquent la matiÃ¨re organique Ã  partir de matiÃ¨re minÃ©rale","explanation":"Par photosynthÃ¨se, ils constituent la base de la plupart des rÃ©seaux trophiques terrestres et aquatiques."},
        {"id":"696_2","type":"vrai-faux","question":"Le CO2 atmosphÃ©rique participe Ã  l'effet de serre.",
         "correct":True,"explanation":"Le dioxyde de carbone absorbe une partie du rayonnement infrarouge Ã©mis par la Terre et contribue au rÃ©chauffement de l'atmosphÃ¨re."},
        {"id":"696_3","type":"texte","question":"La capacitÃ© d'un milieu Ã  absorber davantage de CO2 qu'il n'en Ã©met correspond Ã  un _____ de carbone.",
         "correct_answer":"puits","explanation":"Les forÃªts, les ocÃ©ans et certains sols peuvent agir comme puits de carbone en stockant du carbone sur le long terme."},
        {"id":"696_4","type":"qcm","question":"Quel phÃ©nomÃ¨ne provoque souvent un manque d'oxygÃ¨ne dans l'eau aprÃ¨s une prolifÃ©ration massive d'algues ?",
         "options":["La dessiccation","L'eutrophisation","La fossilisation","La subduction"],
         "correct_option":"L'eutrophisation","explanation":"La dÃ©composition des algues consomme beaucoup de dioxygÃ¨ne et peut asphyxier les organismes aquatiques."},
        {"id":"696_5","type":"vrai-faux","question":"Le changement climatique n'a aucun effet attendu sur la rÃ©partition gÃ©ographique des espÃ¨ces.",
         "correct":False,"explanation":"De nombreuses espÃ¨ces modifient dÃ©jÃ  leur aire de rÃ©partition ou leur calendrier biologique en rÃ©ponse au rÃ©chauffement."},
        {"id":"696_6","type":"texte","question":"Le dioxyde de carbone, le mÃ©thane et le protoxyde d'azote sont des gaz Ã  effet de _____.",
         "correct_answer":"serre","explanation":"Ces gaz piÃ¨gent une partie du rayonnement infrarouge et modifient l'Ã©quilibre Ã©nergÃ©tique de la planÃ¨te."},
        {"id":"696_7","type":"qcm","question":"Quel comportement contribue le plus directement Ã  prÃ©server la biodiversitÃ© locale ?",
         "options":["Introduire des espÃ¨ces exotiques partout","PrÃ©server les habitats et limiter l'artificialisation des sols","Supprimer tous les prÃ©dateurs","Augmenter l'Ã©clairage nocturne"],
         "correct_option":"PrÃ©server les habitats et limiter l'artificialisation des sols","explanation":"La destruction et la fragmentation des habitats figurent parmi les premiÃ¨res causes d'Ã©rosion de la biodiversitÃ©."},
        {"id":"696_8","type":"vrai-faux","question":"Les zones humides jouent un rÃ´le utile Ã  la fois pour la biodiversitÃ© et pour la gestion de l'eau.",
         "correct":True,"explanation":"Elles abritent de nombreuses espÃ¨ces, filtrent une partie des polluants et attÃ©nuent certaines crues."},
    ]),

    (697, "RÃ©visions : santÃ©, nutrition et prÃ©vention", "SVT", "3Ã¨me", [
        {"id":"697_1","type":"qcm","question":"Quel comportement rÃ©duit le risque de maladies cardiovasculaires ?",
         "options":["Le tabagisme rÃ©gulier","L'activitÃ© physique rÃ©guliÃ¨re et une alimentation Ã©quilibrÃ©e","Le manque de sommeil chronique","La sÃ©dentaritÃ©"],
         "correct_option":"L'activitÃ© physique rÃ©guliÃ¨re et une alimentation Ã©quilibrÃ©e","explanation":"Ces habitudes limitent notamment l'obÃ©sitÃ©, l'hypertension et certains troubles mÃ©taboliques."},
        {"id":"697_2","type":"vrai-faux","question":"Le microbiote intestinal peut participer Ã  la digestion et au bon fonctionnement du systÃ¨me immunitaire.",
         "correct":True,"explanation":"Le microbiote aide Ã  dÃ©grader certains aliments, produit des molÃ©cules utiles et interagit avec l'immunitÃ©."},
        {"id":"697_3","type":"texte","question":"Le dÃ©pistage prÃ©coce permet souvent une prise en charge plus efficace d'un _____.",
         "correct_answer":"cancer","explanation":"DÃ©tecter une maladie tÃ´t amÃ©liore souvent les chances de traitement et rÃ©duit la gravitÃ© des complications."},
        {"id":"697_4","type":"qcm","question":"Quel organe rÃ©gule principalement la glycÃ©mie grÃ¢ce Ã  l'insuline ?",
         "options":["Le pancrÃ©as","Le poumon","La rate","Le cerveau"],
         "correct_option":"Le pancrÃ©as","explanation":"Le pancrÃ©as endocrine sÃ©crÃ¨te des hormones comme l'insuline et le glucagon qui rÃ©gulent le taux de glucose sanguin."},
        {"id":"697_5","type":"vrai-faux","question":"Le tabac augmente le risque de plusieurs cancers et pas seulement celui du poumon.",
         "correct":True,"explanation":"Le tabagisme augmente aussi le risque de cancers de la bouche, de la vessie, du larynx, du pancrÃ©as et d'autres organes."},
        {"id":"697_6","type":"texte","question":"Un manque de globules rouges ou d'hÃ©moglobine fonctionnelle peut entraÃ®ner une _____.",
         "correct_answer":"anÃ©mie","explanation":"L'anÃ©mie diminue la capacitÃ© du sang Ã  transporter l'oxygÃ¨ne et provoque fatigue, pÃ¢leur et essoufflement."},
        {"id":"697_7","type":"qcm","question":"Quel indicateur est utilisÃ© pour estimer la corpulence Ã  partir du poids et de la taille ?",
         "options":["La tension artÃ©rielle","L'IMC","La glycÃ©mie","Le groupe sanguin"],
         "correct_option":"L'IMC","explanation":"L'indice de masse corporelle est un repÃ¨re simple, Ã  interprÃ©ter avec d'autres Ã©lÃ©ments selon l'Ã¢ge et la situation mÃ©dicale."},
        {"id":"697_8","type":"vrai-faux","question":"La prÃ©vention en santÃ© repose aussi sur l'information, les vaccinations et le dÃ©pistage.",
         "correct":True,"explanation":"PrÃ©venir, informer et diagnostiquer tÃ´t font partie des leviers majeurs de santÃ© publique."},
    ]),

    (698, "Quiz blanc final SVT 3Ã¨me", "SVT", "3Ã¨me", [
        {"id":"698_1","type":"qcm","question":"Quel processus transforme l'Ã©nergie lumineuse en Ã©nergie chimique stockÃ©e dans la matiÃ¨re organique ?",
         "options":["La fermentation","La photosynthÃ¨se","La respiration","La digestion"],
         "correct_option":"La photosynthÃ¨se","explanation":"La photosynthÃ¨se rÃ©alisÃ©e par les producteurs primaires est Ã  la base de la plupart des chaÃ®nes alimentaires."},
        {"id":"698_2","type":"vrai-faux","question":"Une espÃ¨ce peut disparaÃ®tre si son habitat est fortement dÃ©gradÃ© ou fragmentÃ©.",
         "correct":True,"explanation":"La perte d'habitat fait partie des principales causes d'Ã©rosion de la biodiversitÃ© Ã  l'Ã©chelle mondiale."},
        {"id":"698_3","type":"texte","question":"Le nom de la molÃ©cule qui porte l'information gÃ©nÃ©tique dans les cellules est l'_____.",
         "correct_answer":"ADN","explanation":"L'ADN stocke l'information hÃ©rÃ©ditaire sous forme de sÃ©quences de nuclÃ©otides organisÃ©es en gÃ¨nes."},
        {"id":"698_4","type":"qcm","question":"Dans quel organe se situe principalement le dÃ©veloppement de l'embryon puis du fÅ“tus chez l'Ãªtre humain ?",
         "options":["L'ovaire","L'utÃ©rus","Le foie","Le rein"],
         "correct_option":"L'utÃ©rus","explanation":"AprÃ¨s la nidation, l'embryon puis le fÅ“tus se dÃ©veloppent dans l'utÃ©rus pendant toute la grossesse."},
        {"id":"698_5","type":"vrai-faux","question":"Les plaques tectoniques sont immobiles Ã  l'Ã©chelle des temps gÃ©ologiques.",
         "correct":False,"explanation":"Elles se dÃ©placent lentement mais continuellement, modifiant ocÃ©ans, continents et reliefs au cours du temps."},
        {"id":"698_6","type":"texte","question":"La protection d'une population par la vaccination d'une grande partie du groupe est appelÃ©e immunitÃ© _____.",
         "correct_answer":"collective","explanation":"L'immunitÃ© collective limite la circulation d'un agent infectieux et protÃ¨ge indirectement les personnes vulnÃ©rables."},
        {"id":"698_7","type":"qcm","question":"Quel ensemble de relations alimentaires relie producteurs, consommateurs et dÃ©composeurs dans un milieu ?",
         "options":["Le rÃ©seau trophique","Le caryotype","Le cycle menstruel","La tectonique"],
         "correct_option":"Le rÃ©seau trophique","explanation":"Le rÃ©seau trophique dÃ©crit les transferts de matiÃ¨re et d'Ã©nergie entre les Ãªtres vivants d'un Ã©cosystÃ¨me."},
        {"id":"698_8","type":"vrai-faux","question":"Les connaissances scientifiques en SVT reposent sur l'observation, l'expÃ©rimentation et la confrontation des hypothÃ¨ses aux faits.",
         "correct":True,"explanation":"La dÃ©marche scientifique s'appuie sur des hypothÃ¨ses testables, des mesures et une validation collective des rÃ©sultats."},
    ])
]


def write_quiz_files():
    for qid, title, subject, level, questions in quizzes_data:
        quiz = make_quiz(qid, title, subject, level, questions)
        answers = make_answers(qid, title, subject, level, questions)
        dump_json_file(os.path.join(SVT3_QUIZ_DIR, f"{qid}.json"), quiz)
        dump_json_file(os.path.join(SVT3_ANSWERS_DIR, f"{qid}.json"), answers)
        dump_json_file(os.path.join(OUTPUT_QUIZ_DIR, f"{qid}.json"), quiz)
        dump_json_file(os.path.join(OUTPUT_ANSWERS_DIR, f"{qid}.json"), answers)
        dump_json_file(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), quiz)
        dump_json_file(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), answers)

    print(f"{len(quizzes_data)} quiz SVT 3ème générés ({len(quizzes_data) * 6} fichiers)")


if __name__ == "__main__":
    write_quiz_files()

