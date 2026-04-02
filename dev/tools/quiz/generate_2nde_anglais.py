#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Template IA â€“ generate_<niveau>_<matiere>.py
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", ".."))

# Ã€ adapter dans chaque clone
BASENAME = "anglais_2nde_quizzes"

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


def normalize_question_type(question_type):
    qt = str(question_type).strip().lower()
    if qt in {"vrai-faux", "vrai faux"}:
        return "vrai-faux"
    if qt == "qcm":
        return "qcm"
    if qt in {"open", "texte", "text"}:
        return "open"
    return qt


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
                "type": "qcm",
                "question": str(question.get("question", "")),
                "choices": list(question.get("options", [])),
            }
        elif qtype == "vrai-faux":
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
        elif qtype == "vrai-faux":
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


quizzes_data = [
    (
        1653,
        "Diagnostic 2nde Anglais - Grammar Foundations",
        "Anglais",
        "2nde",
        [
            {"id": "1653_1", "type": "qcm", "question": "Choose the correct sentence.", "options": ["She go to school every day.", "She goes to school every day.", "She going to school every day.", "She gone to school every day."], "correct_option": "She goes to school every day.", "explanation": "Au présent simple, la 3e personne du singulier prend -s au verbe."},
            {"id": "1653_2", "type": "vrai-faux", "question": "The present simple is often used for habits and routines.", "correct": True, "explanation": "Le present simple exprime des habitudes, des faits généraux et des routines."},
            {"id": "1653_3", "type": "texte", "question": "Write the missing auxiliary in this question: ___ you like reading novels?", "correct_answer": "do", "explanation": "Avec le sujet 'you' au présent simple, l'auxiliaire interrogatif est 'do'."},
            {"id": "1653_4", "type": "qcm", "question": "Select the correct negative form.", "options": ["He don't understand.", "He doesn't understands.", "He doesn't understand.", "He not understand."], "correct_option": "He doesn't understand.", "explanation": "Après 'doesn't', le verbe reste à la base verbale sans -s."},
            {"id": "1653_5", "type": "vrai-faux", "question": "In English, adjectives are usually placed before the noun.", "correct": True, "explanation": "En anglais, l'adjectif est généralement antéposé: 'a beautiful city'."},
            {"id": "1653_6", "type": "texte", "question": "Give the comparative form of " + '"good"' + ".", "correct_answer": "better", "explanation": "'Good' est irrégulier: good, better, the best."},
            {"id": "1653_7", "type": "qcm", "question": "Which sentence is correct?", "options": ["There is many students in class.", "There are many students in class.", "There are much students in class.", "There is a many students in class."], "correct_option": "There are many students in class.", "explanation": "On emploie 'there are' avec un nom pluriel comptable."},
            {"id": "1653_8", "type": "vrai-faux", "question": "'Much' is generally used with uncountable nouns.", "correct": True, "explanation": "'Much' s'utilise surtout avec les indénombrables: much time, much information."}
        ],
    ),
    (
        1654,
        "Diagnostic 2nde Anglais - Tenses and Timeline",
        "Anglais",
        "2nde",
        [
            {"id": "1654_1", "type": "qcm", "question": "Choose the correct form: I ___ my homework when she called.", "options": ["did", "was doing", "have done", "am doing"], "correct_option": "was doing", "explanation": "Le past continuous exprime une action en cours interrompue par un événement."},
            {"id": "1654_2", "type": "vrai-faux", "question": "The past simple is used for completed actions in the past.", "correct": True, "explanation": "Le past simple situe des actions terminées à un moment passé."},
            {"id": "1654_3", "type": "texte", "question": "Write the past participle of " + '"write"' + ".", "correct_answer": "written", "explanation": "Le verbe irrégulier write suit la série write/wrote/written."},
            {"id": "1654_4", "type": "qcm", "question": "Which sentence uses the present perfect correctly?", "options": ["I have saw this film.", "I have seen this film.", "I seen this film.", "I has seen this film."], "correct_option": "I have seen this film.", "explanation": "Le present perfect: have/has + participe passé."},
            {"id": "1654_5", "type": "vrai-faux", "question": "'Since' is used with a starting point in time.", "correct": True, "explanation": "'Since' introduit le point de départ: since 2020, since Monday."},
            {"id": "1654_6", "type": "texte", "question": "Complete: She has lived here ___ 2019.", "correct_answer": "since", "explanation": "Avec un repère initial précis, on emploie 'since'."},
            {"id": "1654_7", "type": "qcm", "question": "Choose the best option: By next year, they ___ the project.", "options": ["finish", "will finish", "will have finished", "have finished"], "correct_option": "will have finished", "explanation": "Le futur antérieur exprime une action achevée avant un repère futur."},
            {"id": "1654_8", "type": "vrai-faux", "question": "'For' is used with a duration.", "correct": True, "explanation": "'For' exprime une durée: for two hours, for many years."}
        ],
    ),
    (
        1655,
        "Diagnostic 2nde Anglais - Modal Verbs",
        "Anglais",
        "2nde",
        [
            {"id": "1655_1", "type": "qcm", "question": "Which modal expresses strong obligation?", "options": ["may", "might", "must", "could"], "correct_option": "must", "explanation": "'Must' exprime une obligation forte ou une nécessité."},
            {"id": "1655_2", "type": "vrai-faux", "question": "After a modal verb, the main verb is in base form.", "correct": True, "explanation": "Après un modal, on utilise la base verbale sans 'to': must go, can read."},
            {"id": "1655_3", "type": "texte", "question": "Give one modal used to ask for permission politely.", "correct_answer": "may", "explanation": "'May' est courant pour demander une permission de façon formelle."},
            {"id": "1655_4", "type": "qcm", "question": "Choose the sentence that expresses advice.", "options": ["You must leave now.", "You should see a doctor.", "You can swim.", "You might be right."], "correct_option": "You should see a doctor.", "explanation": "'Should' sert à donner un conseil ou une recommandation."},
            {"id": "1655_5", "type": "vrai-faux", "question": "'Can' may express both ability and permission.", "correct": True, "explanation": "'Can' peut signifier capacité (I can swim) ou permission (You can leave)."},
            {"id": "1655_6", "type": "texte", "question": "Complete: You ___ not park here. It is forbidden.", "correct_answer": "must", "explanation": "'Must not' exprime l'interdiction."},
            {"id": "1655_7", "type": "qcm", "question": "Which modal best expresses probability in this sentence? " + '"It is dark; it ___ rain soon."' , "options": ["should", "must", "can", "might"], "correct_option": "might", "explanation": "'Might' marque une probabilité possible mais non certaine."},
            {"id": "1655_8", "type": "vrai-faux", "question": "'Have to' can also express obligation.", "correct": True, "explanation": "'Have to' exprime une obligation souvent externe à la personne."}
        ],
    ),
    (
        1656,
        "Diagnostic 2nde Anglais - Reading Skills",
        "Anglais",
        "2nde",
        [
            {"id": "1656_1", "type": "qcm", "question": "In reading, what does 'skimming' mean?", "options": ["Reading every word slowly", "Reading quickly to get the main idea", "Translating each sentence", "Copying the text"], "correct_option": "Reading quickly to get the main idea", "explanation": "Le skimming est une lecture globale rapide pour saisir le sens général."},
            {"id": "1656_2", "type": "vrai-faux", "question": "'Scanning' means looking for specific information in a text.", "correct": True, "explanation": "Le scanning cible une information précise: date, nom, chiffre, mot-clé."},
            {"id": "1656_3", "type": "texte", "question": "What is the English term for " + '"idée principale d\'un paragraphe"' + "?", "correct_answer": "main idea", "explanation": "'Main idea' désigne l'idée principale développée dans un passage."},
            {"id": "1656_4", "type": "qcm", "question": "Which clue helps you infer the meaning of an unknown word?", "options": ["Its color", "Context around the word", "Its length only", "Its position on the page"], "correct_option": "Context around the word", "explanation": "Le contexte lexical et syntaxique permet d'inférer le sens d'un mot inconnu."},
            {"id": "1656_5", "type": "vrai-faux", "question": "A reliable summary should include only key information.", "correct": True, "explanation": "Un bon résumé conserve les idées essentielles et élimine les détails secondaires."},
            {"id": "1656_6", "type": "texte", "question": "Give one discourse marker used to introduce a contrast.", "correct_answer": "however", "explanation": "'However' introduit un contraste ou une opposition d'idées."},
            {"id": "1656_7", "type": "qcm", "question": "What should you do first when answering comprehension questions?", "options": ["Guess randomly", "Read questions before rereading key parts", "Translate the whole text", "Ignore the title"], "correct_option": "Read questions before rereading key parts", "explanation": "Lire d'abord les questions permet de cibler les informations pertinentes."},
            {"id": "1656_8", "type": "vrai-faux", "question": "The title and subtitle can help predict the text content.", "correct": True, "explanation": "Le titre et les sous-titres aident à anticiper thème et intention du texte."}
        ],
    ),
    (
        1657,
        "Diagnostic 2nde Anglais - Vocabulary and Word Formation",
        "Anglais",
        "2nde",
        [
            {"id": "1657_1", "type": "qcm", "question": "Which prefix usually gives a negative meaning?", "options": ["re-", "un-", "pre-", "over-"], "correct_option": "un-", "explanation": "Le préfixe 'un-' inverse souvent le sens: happy/unhappy."},
            {"id": "1657_2", "type": "vrai-faux", "question": "'Careful' and 'careless' have opposite meanings.", "correct": True, "explanation": "Le suffixe '-less' indique l'absence et crée souvent l'antonyme."},
            {"id": "1657_3", "type": "texte", "question": "Give the noun form of " + '"decide"' + ".", "correct_answer": "decision", "explanation": "Le verbe 'decide' devient le nom 'decision'."},
            {"id": "1657_4", "type": "qcm", "question": "Choose the correct collocation.", "options": ["do a mistake", "make a mistake", "take a mistake", "have a mistake"], "correct_option": "make a mistake", "explanation": "La collocation correcte en anglais est 'make a mistake'."},
            {"id": "1657_5", "type": "vrai-faux", "question": "A false friend is a word that looks similar in two languages but has a different meaning.", "correct": True, "explanation": "Les faux amis peuvent induire en erreur malgré une forme proche."},
            {"id": "1657_6", "type": "texte", "question": "Give the adjective form of " + '"danger"' + ".", "correct_answer": "dangerous", "explanation": "Le suffixe '-ous' forme souvent un adjectif: danger/dangerous."},
            {"id": "1657_7", "type": "qcm", "question": "Which option is the best synonym of " + '"begin"' + "?", "options": ["start", "stop", "finish", "avoid"], "correct_option": "start", "explanation": "'Begin' et 'start' sont des synonymes courants."},
            {"id": "1657_8", "type": "vrai-faux", "question": "Learning words in chunks (groups) helps memorization.", "correct": True, "explanation": "Apprendre par groupes lexicaux favorise la mémorisation et la réutilisation."}
        ],
    ),
    (
        1658,
        "Diagnostic 2nde Anglais - Pronunciation and Listening Basics",
        "Anglais",
        "2nde",
        [
            {"id": "1658_1", "type": "qcm", "question": "Which word has a silent letter?", "options": ["know", "near", "name", "need"], "correct_option": "know", "explanation": "Dans 'know', la lettre 'k' est muette."},
            {"id": "1658_2", "type": "vrai-faux", "question": "English word stress can change the intelligibility of speech.", "correct": True, "explanation": "L'accentuation correcte facilite la compréhension orale en anglais."},
            {"id": "1658_3", "type": "texte", "question": "Write one common weak form of " + '"and"' + " in connected speech.", "correct_answer": "ən", "explanation": "En parole enchaînée, 'and' est souvent réduit à /ən/ ou /n/."},
            {"id": "1658_4", "type": "qcm", "question": "What is the best strategy when you miss one word in listening?", "options": ["Stop listening", "Focus on overall meaning and continue", "Guess all remaining words", "Translate everything immediately"], "correct_option": "Focus on overall meaning and continue", "explanation": "En compréhension orale, il faut garder le fil global plutôt que bloquer sur un mot."},
            {"id": "1658_5", "type": "vrai-faux", "question": "In English, intonation may indicate if a speaker is asking a yes/no question.", "correct": True, "explanation": "L'intonation montante est fréquente dans certaines questions fermées."},
            {"id": "1658_6", "type": "texte", "question": "Give one pair of words that differ by vowel sound only (minimal pair).", "correct_answer": "ship/sheep", "explanation": "Les minimal pairs entraînent la discrimination fine des sons voyelles."},
            {"id": "1658_7", "type": "qcm", "question": "Which ending is pronounced /t/ in regular past forms?", "options": ["played", "called", "watched", "cleaned"], "correct_option": "watched", "explanation": "Après un son sourd, -ed se prononce souvent /t/: watched."},
            {"id": "1658_8", "type": "vrai-faux", "question": "Listening twice with different goals is an effective strategy.", "correct": True, "explanation": "Première écoute globale, seconde écoute détaillée: méthode efficace."}
        ],
    ),
    (
        1659,
        "Diagnostic 2nde Anglais - Writing a Paragraph",
        "Anglais",
        "2nde",
        [
            {"id": "1659_1", "type": "qcm", "question": "What is the role of a topic sentence?", "options": ["To conclude the paragraph", "To introduce the main idea", "To add an unrelated example", "To quote a dictionary"], "correct_option": "To introduce the main idea", "explanation": "La topic sentence annonce clairement l'idée directrice du paragraphe."},
            {"id": "1659_2", "type": "vrai-faux", "question": "A coherent paragraph should use linking words.", "correct": True, "explanation": "Les connecteurs logiques assurent la cohérence et l'enchaînement des idées."},
            {"id": "1659_3", "type": "texte", "question": "Give one linker to add an idea.", "correct_answer": "moreover", "explanation": "'Moreover' sert à ajouter un argument de façon formelle."},
            {"id": "1659_4", "type": "qcm", "question": "Which sentence is the best concluding sentence?", "options": ["I like music.", "To sum up, social media can be useful if used responsibly.", "Yesterday was sunny.", "Because it is important."], "correct_option": "To sum up, social media can be useful if used responsibly.", "explanation": "Une conclusion reformule l'idée principale de manière synthétique."},
            {"id": "1659_5", "type": "vrai-faux", "question": "Examples should support the main idea of the paragraph.", "correct": True, "explanation": "Les exemples doivent illustrer et renforcer l'argument principal."},
            {"id": "1659_6", "type": "texte", "question": "Write one linker used to give an example.", "correct_answer": "for example", "explanation": "'For example' introduit une illustration concrète."},
            {"id": "1659_7", "type": "qcm", "question": "Which option best improves style in formal writing?", "options": ["Use contractions everywhere", "Avoid very repetitive basic words", "Write without punctuation", "Use only very short fragments"], "correct_option": "Avoid very repetitive basic words", "explanation": "Varier le lexique et structurer les phrases améliore la qualité rédactionnelle."},
            {"id": "1659_8", "type": "vrai-faux", "question": "Planning ideas before writing usually improves paragraph quality.", "correct": True, "explanation": "Planifier (idées + ordre logique) rend le paragraphe plus clair et efficace."}
        ],
    ),
    (
        1660,
        "Diagnostic 2nde Anglais - Argumentation",
        "Anglais",
        "2nde",
        [
            {"id": "1660_1", "type": "qcm", "question": "In an argumentative paragraph, what is a 'claim'?", "options": ["A random detail", "A central position to defend", "A citation only", "A grammar rule"], "correct_option": "A central position to defend", "explanation": "Le claim est la thèse ou position principale que l'on défend."},
            {"id": "1660_2", "type": "vrai-faux", "question": "A strong argument is usually supported by evidence.", "correct": True, "explanation": "Des preuves (faits, exemples, données) renforcent la validité de l'argument."},
            {"id": "1660_3", "type": "texte", "question": "Give one connector used to introduce a counter-argument.", "correct_answer": "however", "explanation": "Un contre-argument est souvent introduit par 'however' ou 'on the other hand'."},
            {"id": "1660_4", "type": "qcm", "question": "Which structure is the most logical?", "options": ["Claim -> Evidence -> Explanation", "Conclusion -> Claim -> Title", "Example -> Greeting -> Claim", "Opinion -> No support"], "correct_option": "Claim -> Evidence -> Explanation", "explanation": "Cette progression rend l'argumentation lisible et convaincante."},
            {"id": "1660_5", "type": "vrai-faux", "question": "Refuting an opposite idea can strengthen your position.", "correct": True, "explanation": "Réfuter une objection montre que l'argumentation est construite et nuancée."},
            {"id": "1660_6", "type": "texte", "question": "Write one expression to conclude an argument: " + '"___, schools should limit phone use."' , "correct_answer": "therefore", "explanation": "'Therefore' introduit une conclusion logique à partir des arguments."},
            {"id": "1660_7", "type": "qcm", "question": "Which is the most formal sentence starter?", "options": ["I guess", "Basically", "It can be argued that", "You know"], "correct_option": "It can be argued that", "explanation": "Cette formule est adaptée au registre argumentatif formel."},
            {"id": "1660_8", "type": "vrai-faux", "question": "Using only personal opinion without support is enough in academic argumentation.", "correct": False, "explanation": "En argumentation scolaire, l'opinion seule doit être appuyée par des justifications."}
        ],
    ),
    (
        1661,
        "Diagnostic 2nde Anglais - Culture and Civilization Basics",
        "Anglais",
        "2nde",
        [
            {"id": "1661_1", "type": "qcm", "question": "Which country is NOT part of the United Kingdom?", "options": ["Scotland", "Wales", "Republic of Ireland", "Northern Ireland"], "correct_option": "Republic of Ireland", "explanation": "Le Royaume-Uni comprend Angleterre, Écosse, Pays de Galles et Irlande du Nord."},
            {"id": "1661_2", "type": "vrai-faux", "question": "English is spoken as an official language in many countries outside Europe.", "correct": True, "explanation": "L'anglais est langue officielle ou co-officielle dans de nombreux pays."},
            {"id": "1661_3", "type": "texte", "question": "Name one major English-speaking country in North America.", "correct_answer": "Canada", "explanation": "Le Canada a l'anglais parmi ses langues officielles, selon les provinces."},
            {"id": "1661_4", "type": "qcm", "question": "What does the term 'Commonwealth' generally refer to?", "options": ["A military alliance", "A network of states historically linked to the UK", "A European tax system", "An English exam board"], "correct_option": "A network of states historically linked to the UK", "explanation": "Le Commonwealth réunit des États historiquement liés à l'ancien Empire britannique."},
            {"id": "1661_5", "type": "vrai-faux", "question": "Cultural references can help interpret a text more accurately.", "correct": True, "explanation": "Connaître le contexte culturel améliore la compréhension implicite."},
            {"id": "1661_6", "type": "texte", "question": "Give one famous English-speaking city.", "correct_answer": "London", "explanation": "Des repères géographiques et culturels aident à contextualiser les documents."},
            {"id": "1661_7", "type": "qcm", "question": "Which topic belongs to cultural studies in English class?", "options": ["The periodic table", "Voting systems in anglophone countries", "Only verb conjugation", "Solving equations"], "correct_option": "Voting systems in anglophone countries", "explanation": "La civilisation anglophone traite des institutions, sociétés et cultures."},
            {"id": "1661_8", "type": "vrai-faux", "question": "Understanding historical context can improve text analysis.", "correct": True, "explanation": "Le contexte historique éclaire les enjeux et références du document."}
        ],
    ),
    (
        1662,
        "Diagnostic 2nde Anglais - Start of Year Synthesis",
        "Anglais",
        "2nde",
        [
            {"id": "1662_1", "type": "qcm", "question": "Choose the sentence with correct word order.", "options": ["Always I am tired on Monday.", "I am always tired on Monday.", "I always am tired on Monday.", "I am tired always on Monday."], "correct_option": "I am always tired on Monday.", "explanation": "L'adverbe de fréquence se place avant l'adjectif ou après l'auxiliaire."},
            {"id": "1662_2", "type": "vrai-faux", "question": "In English, every full sentence needs at least a subject and a verb.", "correct": True, "explanation": "La phrase complète anglaise exige au minimum sujet + verbe conjugué."},
            {"id": "1662_3", "type": "texte", "question": "Write the correct pronoun to replace " + '"my friends and I"' + " as an object.", "correct_answer": "us", "explanation": "En fonction complément, on utilise 'us', pas 'we'."},
            {"id": "1662_4", "type": "qcm", "question": "Which sentence is grammatically correct?", "options": ["If I will have time, I call you.", "If I have time, I will call you.", "If I had time, I will call you.", "If I have time, I call you will."], "correct_option": "If I have time, I will call you.", "explanation": "First conditional: if + present simple, will + base verb."},
            {"id": "1662_5", "type": "vrai-faux", "question": "A clear paragraph usually has an introduction, development and conclusion.", "correct": True, "explanation": "Une structure claire facilite la lecture et la progression des idées."},
            {"id": "1662_6", "type": "texte", "question": "Give one polite expression to disagree in English.", "correct_answer": "I see your point, but", "explanation": "On peut nuancer un désaccord avec une formule polie puis un contre-argument."},
            {"id": "1662_7", "type": "qcm", "question": "Which strategy best helps revise vocabulary long term?", "options": ["Reading once only", "Spaced repetition with regular review", "Memorizing randomly the night before", "Ignoring context"], "correct_option": "Spaced repetition with regular review", "explanation": "La répétition espacée améliore fortement la mémorisation durable."},
            {"id": "1662_8", "type": "vrai-faux", "question": "Self-correction after writing can improve both grammar and precision.", "correct": True, "explanation": "La relecture ciblée réduit les erreurs et améliore la qualité d'expression."}
        ],
    ),
]


def write_quiz_files():
    count = 0
    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(
            qid, title, subject, level, questions,
            source="Eduscol programmes officiels + BOEN",
            programme_ref="BO special n°1 du 22 janvier 2019 (LGT) + Eduscol Langues vivantes Seconde",
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
        print(f"  âœ“ {qid}.json - {title}")

    print(f"\nâœ… {count} quiz gÃ©nÃ©rÃ©s (+ {count} rÃ©ponses)")


if __name__ == "__main__":
    print("Generating quizzes from template IA...")
    write_quiz_files()
