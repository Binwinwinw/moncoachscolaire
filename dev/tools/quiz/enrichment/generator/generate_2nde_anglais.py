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
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

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
            {"id": "1653_3", "type": "qcm", "question": "What is the missing auxiliary in this question: ___ you like reading novels?", "options": ["do", "does", "is", "are"], "correct_option": "do", "explanation": "Avec le sujet 'you' au présent simple, l'auxiliaire interrogatif est 'do'."},
            {"id": "1653_4", "type": "qcm", "question": "Select the correct negative form.", "options": ["He don't understand.", "He doesn't understands.", "He doesn't understand.", "He not understand."], "correct_option": "He doesn't understand.", "explanation": "Après 'doesn't', le verbe reste à la base verbale sans -s."},
            {"id": "1653_5", "type": "vrai-faux", "question": "In English, adjectives are usually placed before the noun.", "correct": True, "explanation": "En anglais, l'adjectif est généralement antéposé: 'a beautiful city'."},
            {"id": "1653_6", "type": "qcm", "question": "What is the comparative form of 'good'?", "options": ["gooder", "better", "more good", "best"], "correct_option": "better", "explanation": "'Good' est irrégulier: good, better, the best."},
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
            {"id": "1654_3", "type": "qcm", "question": "What is the past participle of 'write'?", "options": ["writed", "wrote", "written", "write"], "correct_option": "written", "explanation": "Le verbe irrégulier write suit la série write/wrote/written."},
            {"id": "1654_4", "type": "qcm", "question": "Which sentence uses the present perfect correctly?", "options": ["I have saw this film.", "I have seen this film.", "I seen this film.", "I has seen this film."], "correct_option": "I have seen this film.", "explanation": "Le present perfect: have/has + participe passé."},
            {"id": "1654_5", "type": "vrai-faux", "question": "'Since' is used with a starting point in time.", "correct": True, "explanation": "'Since' introduit le point de départ: since 2020, since Monday."},
            {"id": "1654_6", "type": "qcm", "question": "Complete: She has lived here ___ 2019.", "options": ["for", "since", "during", "from"], "correct_option": "since", "explanation": "Avec un repère initial précis, on emploie 'since'."},
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
            {"id": "1655_3", "type": "qcm", "question": "Which modal is used to ask for permission politely?", "options": ["must", "may", "can", "should"], "correct_option": "may", "explanation": "'May' est courant pour demander une permission de façon formelle."},
            {"id": "1655_4", "type": "qcm", "question": "Choose the sentence that expresses advice.", "options": ["You must leave now.", "You should see a doctor.", "You can swim.", "You might be right."], "correct_option": "You should see a doctor.", "explanation": "'Should' sert à donner un conseil ou une recommandation."},
            {"id": "1655_5", "type": "vrai-faux", "question": "'Can' may express both ability and permission.", "correct": True, "explanation": "'Can' peut signifier capacité (I can swim) ou permission (You can leave)."},
            {"id": "1655_6", "type": "qcm", "question": "Complete: You ___ not park here. It is forbidden.", "options": ["must", "can", "should", "may"], "correct_option": "must", "explanation": "'Must not' exprime l'interdiction."},
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
            {"id": "1656_3", "type": "qcm", "question": "What is the English term for 'idée principale d'un paragraphe'?", "options": ["main idea", "introduction", "summary", "topic"], "correct_option": "main idea", "explanation": "'Main idea' désigne l'idée principale développée dans un passage."},
            {"id": "1656_4", "type": "qcm", "question": "Which clue helps you infer the meaning of an unknown word?", "options": ["Its color", "Context around the word", "Its length only", "Its position on the page"], "correct_option": "Context around the word", "explanation": "Le contexte lexical et syntaxique permet d'inférer le sens d'un mot inconnu."},
            {"id": "1656_5", "type": "vrai-faux", "question": "A reliable summary should include only key information.", "correct": True, "explanation": "Un bon résumé conserve les idées essentielles et élimine les détails secondaires."},
            {"id": "1656_6", "type": "qcm", "question": "Which of the following is a discourse marker used to introduce a contrast?", "options": ["however", "and", "because", "so"], "correct_option": "however", "explanation": "'However' introduit un contraste ou une opposition d'idées."},
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
            {"id": "1657_3", "type": "qcm", "question": "What is the noun form of 'decide'?", "options": ["deciding", "decision", "decided", "decide"], "correct_option": "decision", "explanation": "Le verbe 'decide' devient le nom 'decision'."},
            {"id": "1657_4", "type": "qcm", "question": "Choose the correct collocation.", "options": ["do a mistake", "make a mistake", "take a mistake", "have a mistake"], "correct_option": "make a mistake", "explanation": "La collocation correcte en anglais est 'make a mistake'."},
            {"id": "1657_5", "type": "vrai-faux", "question": "A false friend is a word that looks similar in two languages but has a different meaning.", "correct": True, "explanation": "Les faux amis peuvent induire en erreur malgré une forme proche."},
            {"id": "1657_6", "type": "qcm", "question": "What is the adjective form of 'danger'?", "options": ["danger", "dangerous", "dangerly", "dangerousness"], "correct_option": "dangerous", "explanation": "Le suffixe '-ous' forme souvent un adjectif: danger/dangerous."},
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
            {"id": "1658_3", "type": "qcm", "question": "Which is a common weak form of 'and' in connected speech?", "options": ["ænd", "ən", "ændə", "ad"], "correct_option": "ən", "explanation": "En parole enchaînée, 'and' est souvent réduit à /ən/ ou /n/."},
            {"id": "1658_4", "type": "qcm", "question": "What is the best strategy when you miss one word in listening?", "options": ["Stop listening", "Focus on overall meaning and continue", "Guess all remaining words", "Translate everything immediately"], "correct_option": "Focus on overall meaning and continue", "explanation": "En compréhension orale, il faut garder le fil global plutôt que bloquer sur un mot."},
            {"id": "1658_5", "type": "vrai-faux", "question": "In English, intonation may indicate if a speaker is asking a yes/no question.", "correct": True, "explanation": "L'intonation montante est fréquente dans certaines questions fermées."},
            {"id": "1658_6", "type": "qcm", "question": "Which pair of words is a minimal pair (differ by vowel sound only)?", "options": ["ship/sheep", "cat/dog", "run/walk", "big/small"], "correct_option": "ship/sheep", "explanation": "Les minimal pairs entraînent la discrimination fine des sons voyelles."},
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
            {"id": "1659_3", "type": "qcm", "question": "Which linker is used to add an idea?", "options": ["however", "moreover", "because", "although"], "correct_option": "moreover", "explanation": "'Moreover' sert à ajouter un argument de façon formelle."},
            {"id": "1659_4", "type": "qcm", "question": "Which sentence is the best concluding sentence?", "options": ["I like music.", "To sum up, social media can be useful if used responsibly.", "Yesterday was sunny.", "Because it is important."], "correct_option": "To sum up, social media can be useful if used responsibly.", "explanation": "Une conclusion reformule l'idée principale de manière synthétique."},
            {"id": "1659_5", "type": "vrai-faux", "question": "Examples should support the main idea of the paragraph.", "correct": True, "explanation": "Les exemples doivent illustrer et renforcer l'argument principal."},
            {"id": "1659_6", "type": "qcm", "question": "Which linker is used to give an example?", "options": ["for example", "however", "because", "although"], "correct_option": "for example", "explanation": "'For example' introduit une illustration concrète."},
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
            {"id": "1660_3", "type": "qcm", "question": "Which connector is used to introduce a counter-argument?", "options": ["however", "and", "because", "so"], "correct_option": "however", "explanation": "Un contre-argument est souvent introduit par 'however' ou 'on the other hand'."},
            {"id": "1660_4", "type": "qcm", "question": "Which structure is the most logical?", "options": ["Claim -> Evidence -> Explanation", "Conclusion -> Claim -> Title", "Example -> Greeting -> Claim", "Opinion -> No support"], "correct_option": "Claim -> Evidence -> Explanation", "explanation": "Cette progression rend l'argumentation lisible et convaincante."},
            {"id": "1660_5", "type": "vrai-faux", "question": "Refuting an opposite idea can strengthen your position.", "correct": True, "explanation": "Réfuter une objection montre que l'argumentation est construite et nuancée."},
            {"id": "1660_6", "type": "qcm", "question": "Which expression best completes: ___, schools should limit phone use?", "options": ["however", "therefore", "because", "although"], "correct_option": "therefore", "explanation": "'Therefore' introduit une conclusion logique à partir des arguments."},
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
            {"id": "1661_3", "type": "qcm", "question": "Which of the following is a major English-speaking country in North America?", "options": ["Mexico", "Canada", "Brazil", "Argentina"], "correct_option": "Canada", "explanation": "Le Canada a l'anglais parmi ses langues officielles, selon les provinces."},
            {"id": "1661_4", "type": "qcm", "question": "What does the term 'Commonwealth' generally refer to?", "options": ["A military alliance", "A network of states historically linked to the UK", "A European tax system", "An English exam board"], "correct_option": "A network of states historically linked to the UK", "explanation": "Le Commonwealth réunit des États historiquement liés à l'ancien Empire britannique."},
            {"id": "1661_5", "type": "vrai-faux", "question": "Cultural references can help interpret a text more accurately.", "correct": True, "explanation": "Connaître le contexte culturel améliore la compréhension implicite."},
            {"id": "1661_6", "type": "qcm", "question": "Which of the following is a famous English-speaking city?", "options": ["London", "Paris", "Rome", "Berlin"], "correct_option": "London", "explanation": "Des repères géographiques et culturels aident à contextualiser les documents."},
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
            {"id": "1662_3", "type": "qcm", "question": "What is the correct pronoun to replace 'my friends and I' as an object?", "options": ["we", "us", "they", "them"], "correct_option": "us", "explanation": "En fonction complément, on utilise 'us', pas 'we'."},
            {"id": "1662_4", "type": "qcm", "question": "Which sentence is grammatically correct?", "options": ["If I will have time, I call you.", "If I have time, I will call you.", "If I had time, I will call you.", "If I have time, I call you will."], "correct_option": "If I have time, I will call you.", "explanation": "First conditional: if + present simple, will + base verb."},
            {"id": "1662_5", "type": "vrai-faux", "question": "A clear paragraph usually has an introduction, development and conclusion.", "correct": True, "explanation": "Une structure claire facilite la lecture et la progression des idées."},
            {"id": "1662_6", "type": "qcm", "question": "Which of the following is a polite expression to disagree in English?", "options": ["I see your point, but", "No way!", "You're wrong!", "Never!"], "correct_option": "I see your point, but", "explanation": "On peut nuancer un désaccord avec une formule polie puis un contre-argument."},
            {"id": "1662_7", "type": "qcm", "question": "Which strategy best helps revise vocabulary long term?", "options": ["Reading once only", "Spaced repetition with regular review", "Memorizing randomly the night before", "Ignoring context"], "correct_option": "Spaced repetition with regular review", "explanation": "La répétition espacée améliore fortement la mémorisation durable."},
            {"id": "1662_8", "type": "vrai-faux", "question": "Self-correction after writing can improve both grammar and precision.", "correct": True, "explanation": "La relecture ciblée réduit les erreurs et améliore la qualité d'expression."}
        ],
    ),
        (
            1663,
            "Diagnostic 2nde Anglais - Listening: Everyday Situations",
            "Anglais",
            "2nde",
            [
                {"id": "1663_1", "type": "qcm", "question": "What would you probably hear at a train station?", "options": ["Boarding pass, please.", "Your prescription is ready.", "Would you like fries with that?", "Please fasten your seatbelt."], "correct_option": "Boarding pass, please.", "explanation": "Les annonces de gare utilisent ce type de phrase."},
                {"id": "1663_2", "type": "vrai-faux", "question": "In a restaurant, you might hear: 'Are you ready to order?'", "correct": True, "explanation": "C'est une phrase typique du service en restauration."},
                {"id": "1663_3", "type": "qcm", "question": "Which is a polite way to ask for repetition?", "options": ["Repeat!", "Say again!", "Could you repeat, please?", "What?"], "correct_option": "Could you repeat, please?", "explanation": "La politesse est essentielle à l'oral."},
                {"id": "1663_4", "type": "qcm", "question": "What do you answer if someone says: 'Bless you!' after you sneeze?", "options": ["Thank you!", "Sorry!", "You're welcome!", "No problem!"], "correct_option": "Thank you!", "explanation": "On répond 'Thank you!' après 'Bless you!'"},
                {"id": "1663_5", "type": "vrai-faux", "question": "'Excuse me' is used to get someone's attention.", "correct": True, "explanation": "'Excuse me' sert à attirer l'attention ou demander pardon."},
                {"id": "1663_6", "type": "qcm", "question": "Which phrase would you use to ask for directions?", "options": ["Where is the nearest bus stop?", "How much does it cost?", "What time is it?", "Can I have the menu?"], "correct_option": "Where is the nearest bus stop?", "explanation": "Demander son chemin est une compétence clé."},
                {"id": "1663_7", "type": "qcm", "question": "What is the best response to: 'How are you?'", "options": ["I'm fine, thank you.", "Blue.", "In the morning.", "Yes, I do."], "correct_option": "I'm fine, thank you.", "explanation": "Réponse attendue dans une interaction courante."},
                {"id": "1663_8", "type": "vrai-faux", "question": "'See you later' is a way to say goodbye.", "correct": True, "explanation": "C'est une formule de prise de congé."}
            ],
        ),
        (
            1664,
            "Diagnostic 2nde Anglais - Reading: Informational Texts",
            "Anglais",
            "2nde",
            [
                {"id": "1664_1", "type": "qcm", "question": "What is the main purpose of a news article?", "options": ["To entertain", "To inform", "To sell", "To confuse"], "correct_option": "To inform", "explanation": "Un article d'actualité vise à informer."},
                {"id": "1664_2", "type": "vrai-faux", "question": "A headline gives you an idea of the article's topic.", "correct": True, "explanation": "Le titre oriente la compréhension du texte."},
                {"id": "1664_3", "type": "qcm", "question": "Which is a feature of informational texts?", "options": ["Rhyme", "Facts", "Fictional characters", "Verse"], "correct_option": "Facts", "explanation": "Les textes informatifs présentent des faits."},
                {"id": "1664_4", "type": "qcm", "question": "What should you do if you don't understand a word?", "options": ["Ignore the whole text", "Guess from context", "Stop reading", "Ask a friend in class"], "correct_option": "Guess from context", "explanation": "Le contexte aide à déduire le sens."},
                {"id": "1664_5", "type": "vrai-faux", "question": "Subheadings help organize information.", "correct": True, "explanation": "Les sous-titres structurent le texte."},
                {"id": "1664_6", "type": "qcm", "question": "Which is NOT usually found in a news report?", "options": ["Date", "Place", "Main character", "Facts"], "correct_option": "Main character", "explanation": "Le personnage principal est propre à la fiction."},
                {"id": "1664_7", "type": "qcm", "question": "What is a synonym for 'summary'?", "options": ["Conclusion", "Recap", "Introduction", "Detail"], "correct_option": "Recap", "explanation": "'Recap' signifie résumé ou synthèse."},
                {"id": "1664_8", "type": "vrai-faux", "question": "A caption explains a picture or illustration.", "correct": True, "explanation": "La légende éclaire l'image ou le schéma."}
            ],
        ),
        (
            1665,
            "Diagnostic 2nde Anglais - Grammar: Present Simple vs Present Continuous",
            "Anglais",
            "2nde",
            [
                {"id": "1665_1", "type": "qcm", "question": "Which sentence is in the present continuous?", "options": ["She plays tennis.", "She is playing tennis.", "She played tennis.", "She has played tennis."], "correct_option": "She is playing tennis.", "explanation": "Le present continuous exprime une action en cours."},
                {"id": "1665_2", "type": "vrai-faux", "question": "The present simple is used for routines.", "correct": True, "explanation": "Le present simple exprime les habitudes et routines."},
                {"id": "1665_3", "type": "qcm", "question": "Choose the correct negative form: 'He ___ going to school.'", "options": ["isn't", "doesn't", "aren't", "don't"], "correct_option": "isn't", "explanation": "On utilise 'isn't' pour la forme négative du present continuous."},
                {"id": "1665_4", "type": "qcm", "question": "Which question is in the present simple?", "options": ["Are you coming?", "Do you like pizza?", "Did you go?", "Will you come?"], "correct_option": "Do you like pizza?", "explanation": "Le present simple sert à exprimer les goûts et habitudes."},
                {"id": "1665_5", "type": "vrai-faux", "question": "'She is reading' describes an action happening now.", "correct": True, "explanation": "Le present continuous décrit une action en cours."},
                {"id": "1665_6", "type": "qcm", "question": "Which word is often used with the present continuous?", "options": ["always", "now", "yesterday", "every day"], "correct_option": "now", "explanation": "'Now' indique une action en cours."},
                {"id": "1665_7", "type": "qcm", "question": "Choose the correct sentence.", "options": ["He don't like apples.", "He doesn't like apples.", "He isn't like apples.", "He not like apples."], "correct_option": "He doesn't like apples.", "explanation": "Après 'doesn't', le verbe reste à la base verbale."},
                {"id": "1665_8", "type": "vrai-faux", "question": "The present continuous can describe a temporary situation.", "correct": True, "explanation": "Il sert à décrire une situation temporaire."}
            ],
        ),
        (
            1666,
            "Diagnostic 2nde Anglais - Communication: Making Appointments",
            "Anglais",
            "2nde",
            [
                {"id": "1666_1", "type": "qcm", "question": "Which phrase is appropriate to suggest a meeting?", "options": ["Let's meet at 5pm.", "Go away!", "I don't care.", "Why bother?"], "correct_option": "Let's meet at 5pm.", "explanation": "Formule polie pour proposer un rendez-vous."},
                {"id": "1666_2", "type": "vrai-faux", "question": "'Are you free tomorrow?' is a way to check someone's availability.", "correct": True, "explanation": "C'est une question courante pour organiser un rendez-vous."},
                {"id": "1666_3", "type": "qcm", "question": "How do you politely refuse an invitation?", "options": ["No!", "Sorry, I can't.", "Never!", "Not interested."], "correct_option": "Sorry, I can't.", "explanation": "Formule polie pour décliner une invitation."},
                {"id": "1666_4", "type": "qcm", "question": "Which is a suitable response to 'See you at 6'?", "options": ["No way!", "See you then!", "Whatever!", "Don't care."], "correct_option": "See you then!", "explanation": "Réponse attendue pour confirmer un rendez-vous."},
                {"id": "1666_5", "type": "vrai-faux", "question": "'Let's reschedule' means to change the time of a meeting.", "correct": True, "explanation": "'Reschedule' signifie déplacer un rendez-vous."},
                {"id": "1666_6", "type": "qcm", "question": "Which phrase is used to confirm an appointment?", "options": ["It's confirmed.", "Maybe.", "I don't know.", "No."], "correct_option": "It's confirmed.", "explanation": "Formule pour valider un rendez-vous."},
                {"id": "1666_7", "type": "qcm", "question": "How do you ask for a different time?", "options": ["Can we meet later?", "Never!", "No way!", "Forget it."], "correct_option": "Can we meet later?", "explanation": "Formule polie pour proposer un autre horaire."},
                {"id": "1666_8", "type": "vrai-faux", "question": "'Looking forward to it' expresses enthusiasm for a future meeting.", "correct": True, "explanation": "C'est une formule positive pour exprimer l'attente d'un rendez-vous."}
            ],
        ),
        (
            1667,
            "Diagnostic 2nde Anglais - Vocabulary: School Life",
            "Anglais",
            "2nde",
            [
                {"id": "1667_1", "type": "qcm", "question": "Which word means 'a break between classes'?", "options": ["lesson", "recess", "exam", "subject"], "correct_option": "recess", "explanation": "'Recess' désigne la pause entre les cours."},
                {"id": "1667_2", "type": "vrai-faux", "question": "A 'timetable' is a schedule of lessons.", "correct": True, "explanation": "'Timetable' signifie emploi du temps scolaire."},
                {"id": "1667_3", "type": "qcm", "question": "What is the English for 'cahier'?", "options": ["notebook", "book", "pen", "desk"], "correct_option": "notebook", "explanation": "'Notebook' = cahier en anglais."},
                {"id": "1667_4", "type": "qcm", "question": "Which is a place where you borrow books?", "options": ["library", "gym", "cafeteria", "classroom"], "correct_option": "library", "explanation": "'Library' = bibliothèque."},
                {"id": "1667_5", "type": "vrai-faux", "question": "'Homework' is work you do at home for school.", "correct": True, "explanation": "'Homework' = devoirs à la maison."},
                {"id": "1667_6", "type": "qcm", "question": "Which word means 'a person who teaches'?", "options": ["student", "teacher", "principal", "janitor"], "correct_option": "teacher", "explanation": "'Teacher' = enseignant."},
                {"id": "1667_7", "type": "qcm", "question": "What is the opposite of 'pass an exam'?", "options": ["fail", "study", "revise", "cheat"], "correct_option": "fail", "explanation": "'Fail' = échouer à un examen."},
                {"id": "1667_8", "type": "vrai-faux", "question": "A 'subject' is a topic studied at school.", "correct": True, "explanation": "'Subject' = matière scolaire."}
            ],
        ),
            (
                1668,
                "Diagnostic 2nde Anglais - Grammar: Past Simple vs Past Continuous",
                "Anglais",
                "2nde",
                [
                    {"id": "1668_1", "type": "qcm", "question": "Which sentence is in the past continuous?", "options": ["I was reading.", "I read.", "I have read.", "I am reading."], "correct_option": "I was reading.", "explanation": "Le past continuous exprime une action en cours dans le passé."},
                    {"id": "1668_2", "type": "vrai-faux", "question": "The past simple is used for finished actions.", "correct": True, "explanation": "Le past simple sert à raconter des actions terminées."},
                    {"id": "1668_3", "type": "qcm", "question": "Choose the correct negative form: 'He ___ go to the party.'", "options": ["didn't", "wasn't", "doesn't", "isn't"], "correct_option": "didn't", "explanation": "On utilise 'didn't' pour la négation au past simple."},
                    {"id": "1668_4", "type": "qcm", "question": "Which question is in the past continuous?", "options": ["Did you see her?", "Were you watching TV?", "Do you like pizza?", "Will you come?"], "correct_option": "Were you watching TV?", "explanation": "Le past continuous sert à décrire une action en cours dans le passé."},
                    {"id": "1668_5", "type": "vrai-faux", "question": "'She was eating' describes an action happening at a specific moment in the past.", "correct": True, "explanation": "Le past continuous décrit une action en cours à un moment passé."},
                    {"id": "1668_6", "type": "qcm", "question": "Which word is often used with the past simple?", "options": ["yesterday", "now", "always", "every day"], "correct_option": "yesterday", "explanation": "'Yesterday' indique une action passée."},
                    {"id": "1668_7", "type": "qcm", "question": "Choose the correct sentence.", "options": ["He didn't went.", "He didn't go.", "He doesn't go.", "He not go."], "correct_option": "He didn't go.", "explanation": "Après 'didn't', le verbe reste à la base verbale."},
                    {"id": "1668_8", "type": "vrai-faux", "question": "The past continuous can describe two actions happening at the same time.", "correct": True, "explanation": "Il sert à décrire deux actions simultanées dans le passé."}
                ],
            ),
            (
                1669,
                "Diagnostic 2nde Anglais - Communication: Phone Calls",
                "Anglais",
                "2nde",
                [
                    {"id": "1669_1", "type": "qcm", "question": "Which phrase is used to answer the phone?", "options": ["Hello?", "Goodbye!", "See you!", "Thank you!"], "correct_option": "Hello?", "explanation": "'Hello?' est la formule standard pour décrocher."},
                    {"id": "1669_2", "type": "vrai-faux", "question": "'Can I speak to Mr Smith?' is a way to ask for someone on the phone.", "correct": True, "explanation": "C'est une formule courante pour demander à parler à quelqu'un."},
                    {"id": "1669_3", "type": "qcm", "question": "How do you ask someone to wait on the phone?", "options": ["Hold on, please.", "Go away!", "Wait outside!", "Come in!"], "correct_option": "Hold on, please.", "explanation": "'Hold on' signifie patienter au téléphone."},
                    {"id": "1669_4", "type": "qcm", "question": "Which is a polite way to end a call?", "options": ["Bye!", "See you never!", "Don't call again!", "No!"], "correct_option": "Bye!", "explanation": "'Bye!' est la formule standard pour raccrocher poliment."},
                    {"id": "1669_5", "type": "vrai-faux", "question": "'Who's calling, please?' is a polite way to ask for the caller's name.", "correct": True, "explanation": "Formule polie pour identifier l'appelant."},
                    {"id": "1669_6", "type": "qcm", "question": "How do you ask someone to repeat on the phone?", "options": ["Could you repeat that, please?", "What?", "Say again!", "Repeat!"], "correct_option": "Could you repeat that, please?", "explanation": "La politesse est essentielle à l'oral, surtout au téléphone."},
                    {"id": "1669_7", "type": "qcm", "question": "Which phrase is used to check if someone is available?", "options": ["Is this a good time?", "Are you busy?", "Can you talk now?", "All of the above"], "correct_option": "All of the above", "explanation": "Toutes ces formules servent à vérifier la disponibilité."},
                    {"id": "1669_8", "type": "vrai-faux", "question": "'I'll call you back' means you will return the call later.", "correct": True, "explanation": "C'est une promesse de rappeler plus tard."}
                ],
            ),
            (
                1670,
                "Diagnostic 2nde Anglais - Grammar: Future Forms",
                "Anglais",
                "2nde",
                [
                    {"id": "1670_1", "type": "qcm", "question": "Which sentence is in the future with 'will'?", "options": ["I will go.", "I go.", "I went.", "I am going."], "correct_option": "I will go.", "explanation": "'Will' exprime le futur simple."},
                    {"id": "1670_2", "type": "vrai-faux", "question": "'Going to' is used for planned actions.", "correct": True, "explanation": "'Be going to' sert à exprimer une intention ou un projet."},
                    {"id": "1670_3", "type": "qcm", "question": "Choose the correct form: 'She ___ going to travel.'", "options": ["is", "are", "am", "be"], "correct_option": "is", "explanation": "'She is going to' est la forme correcte."},
                    {"id": "1670_4", "type": "qcm", "question": "Which is a prediction?", "options": ["I will probably pass.", "I am eating.", "I went home.", "I have a cat."], "correct_option": "I will probably pass.", "explanation": "Le futur simple sert à faire des prédictions."},
                    {"id": "1670_5", "type": "vrai-faux", "question": "'Will' is used for spontaneous decisions.", "correct": True, "explanation": "On utilise 'will' pour une décision prise sur le moment."},
                    {"id": "1670_6", "type": "qcm", "question": "Which word is often used with the future?", "options": ["tomorrow", "yesterday", "now", "last week"], "correct_option": "tomorrow", "explanation": "'Tomorrow' indique une action future."},
                    {"id": "1670_7", "type": "qcm", "question": "Choose the correct sentence.", "options": ["He will goes.", "He will go.", "He goes will.", "He go will."], "correct_option": "He will go.", "explanation": "Après 'will', le verbe reste à la base verbale."},
                    {"id": "1670_8", "type": "vrai-faux", "question": "The future continuous describes an action in progress at a future time.", "correct": True, "explanation": "Il sert à décrire une action en cours dans le futur."}
                ],
            ),
            (
                1671,
                "Diagnostic 2nde Anglais - Vocabulary: Food and Meals",
                "Anglais",
                "2nde",
                [
                    {"id": "1671_1", "type": "qcm", "question": "Which word means 'the first meal of the day'?", "options": ["lunch", "dinner", "breakfast", "snack"], "correct_option": "breakfast", "explanation": "'Breakfast' = petit-déjeuner."},
                    {"id": "1671_2", "type": "vrai-faux", "question": "'Dessert' is usually eaten at the end of a meal.", "correct": True, "explanation": "Le dessert se mange en fin de repas."},
                    {"id": "1671_3", "type": "qcm", "question": "What is the English for 'légumes'?", "options": ["vegetables", "fruits", "meat", "fish"], "correct_option": "vegetables", "explanation": "'Vegetables' = légumes."},
                    {"id": "1671_4", "type": "qcm", "question": "Which is a drink?", "options": ["bread", "water", "rice", "cheese"], "correct_option": "water", "explanation": "'Water' = boisson."},
                    {"id": "1671_5", "type": "vrai-faux", "question": "'Snack' is a small amount of food eaten between meals.", "correct": True, "explanation": "'Snack' = encas."},
                    {"id": "1671_6", "type": "qcm", "question": "Which word means 'to cook in an oven'?", "options": ["boil", "bake", "fry", "grill"], "correct_option": "bake", "explanation": "'Bake' = cuire au four."},
                    {"id": "1671_7", "type": "qcm", "question": "What is the opposite of 'hungry'?", "options": ["full", "thirsty", "tired", "happy"], "correct_option": "full", "explanation": "'Full' = rassasié."},
                    {"id": "1671_8", "type": "vrai-faux", "question": "'Menu' is a list of dishes available in a restaurant.", "correct": True, "explanation": "'Menu' = carte des plats."}
                ],
            ),
            (
                1672,
                "Diagnostic 2nde Anglais - Grammar: Comparatives and Superlatives",
                "Anglais",
                "2nde",
                [
                    {"id": "1672_1", "type": "qcm", "question": "Which is the comparative form of 'big'?", "options": ["bigger", "biggest", "more big", "most big"], "correct_option": "bigger", "explanation": "Comparatif régulier: big/bigger."},
                    {"id": "1672_2", "type": "vrai-faux", "question": "'The best' is the superlative form of 'good'.", "correct": True, "explanation": "Superlatif irrégulier: good/better/the best."},
                    {"id": "1672_3", "type": "qcm", "question": "Choose the correct sentence.", "options": ["She is more tall than her sister.", "She is taller than her sister.", "She is tall than her sister.", "She is the tall than her sister."], "correct_option": "She is taller than her sister.", "explanation": "Comparatif de supériorité: adj + -er + than."},
                    {"id": "1672_4", "type": "qcm", "question": "Which is the superlative form of 'happy'?", "options": ["happiest", "more happy", "most happy", "happier"], "correct_option": "happiest", "explanation": "Superlatif régulier: happy/happiest."},
                    {"id": "1672_5", "type": "vrai-faux", "question": "'More interesting' is the comparative form of 'interesting'.", "correct": True, "explanation": "Comparatif des adjectifs longs: more + adj."},
                    {"id": "1672_6", "type": "qcm", "question": "Which is the correct superlative sentence?", "options": ["She is the most intelligent in the class.", "She is most intelligent in the class.", "She is more intelligent in the class.", "She is intelligentest in the class."], "correct_option": "She is the most intelligent in the class.", "explanation": "Superlatif: the most + adj."},
                    {"id": "1672_7", "type": "qcm", "question": "What is the opposite of 'the worst'?", "options": ["the best", "the better", "the good", "the most good"], "correct_option": "the best", "explanation": "Antonyme: best/worst."},
                    {"id": "1672_8", "type": "vrai-faux", "question": "'Less' is used to compare quantities in the negative.", "correct": True, "explanation": "'Less' sert à comparer des quantités à la baisse."}
                ],
            ),
            (
                1673,
                "Diagnostic 2nde Anglais - Communication: Expressing Opinions",
                "Anglais",
                "2nde",
                [
                    {"id": "1673_1", "type": "qcm", "question": "Which phrase introduces an opinion?", "options": ["In my opinion", "For example", "As a result", "On the contrary"], "correct_option": "In my opinion", "explanation": "'In my opinion' introduit un avis personnel."},
                    {"id": "1673_2", "type": "vrai-faux", "question": "'I think' is a way to express a personal view.", "correct": True, "explanation": "'I think' exprime une opinion personnelle."},
                    {"id": "1673_3", "type": "qcm", "question": "How do you agree politely?", "options": ["I agree.", "No way!", "Never!", "Not at all!"], "correct_option": "I agree.", "explanation": "'I agree' est la formule standard d'accord."},
                    {"id": "1673_4", "type": "qcm", "question": "Which is a polite way to disagree?", "options": ["I see your point, but...", "You're wrong!", "No!", "Impossible!"], "correct_option": "I see your point, but...", "explanation": "Formule polie pour nuancer un désaccord."},
                    {"id": "1673_5", "type": "vrai-faux", "question": "'Personally' is used to introduce a personal opinion.", "correct": True, "explanation": "'Personally' marque l'expression d'un avis personnel."},
                    {"id": "1673_6", "type": "qcm", "question": "Which phrase is used to ask for an opinion?", "options": ["What do you think?", "How old are you?", "Where do you live?", "What time is it?"], "correct_option": "What do you think?", "explanation": "'What do you think?' invite à donner un avis."},
                    {"id": "1673_7", "type": "qcm", "question": "How do you express strong agreement?", "options": ["Absolutely!", "Maybe.", "Not really.", "I don't know."], "correct_option": "Absolutely!", "explanation": "'Absolutely!' marque un accord total."},
                    {"id": "1673_8", "type": "vrai-faux", "question": "'On the other hand' introduces a contrasting idea.", "correct": True, "explanation": "'On the other hand' sert à introduire un contraste."}
                ],
            ),
                (
                    1674,
                    "Diagnostic 2nde Anglais - Grammar: Question Forms",
                    "Anglais",
                    "2nde",
                    [
                        {"id": "1674_1", "type": "qcm", "question": "Which is a yes/no question?", "options": ["Do you like music?", "What is your name?", "Where do you live?", "How old are you?"], "correct_option": "Do you like music?", "explanation": "Les yes/no questions attendent une réponse oui/non."},
                        {"id": "1674_2", "type": "vrai-faux", "question": "'Where' introduces a question about place.", "correct": True, "explanation": "'Where' sert à demander un lieu."},
                        {"id": "1674_3", "type": "qcm", "question": "Choose the correct question: ___ you speak English?", "options": ["Do", "Does", "Is", "Are"], "correct_option": "Do", "explanation": "'Do you' est la forme correcte pour 'you'."},
                        {"id": "1674_4", "type": "qcm", "question": "Which word is used to ask about time?", "options": ["When", "Where", "Why", "Who"], "correct_option": "When", "explanation": "'When' interroge sur le temps."},
                        {"id": "1674_5", "type": "vrai-faux", "question": "'How' can be combined with adjectives to ask about quantity or quality.", "correct": True, "explanation": "Ex: How old, how much, how many, how tall..."},
                        {"id": "1674_6", "type": "qcm", "question": "Which is a question word?", "options": ["What", "Blue", "Quickly", "Always"], "correct_option": "What", "explanation": "'What' est un mot interrogatif."},
                        {"id": "1674_7", "type": "qcm", "question": "Choose the correct question: ___ is your birthday?", "options": ["When", "Where", "Why", "Who"], "correct_option": "When", "explanation": "'When' pour demander une date."},
                        {"id": "1674_8", "type": "vrai-faux", "question": "'Why' is used to ask for a reason.", "correct": True, "explanation": "'Why' interroge sur la cause ou la raison."}
                    ],
                ),
                (
                    1675,
                    "Diagnostic 2nde Anglais - Communication: Apologizing and Thanking",
                    "Anglais",
                    "2nde",
                    [
                        {"id": "1675_1", "type": "qcm", "question": "Which phrase is used to apologize?", "options": ["I'm sorry.", "Thank you.", "See you.", "Good luck!"], "correct_option": "I'm sorry.", "explanation": "'I'm sorry' est la formule standard d'excuse."},
                        {"id": "1675_2", "type": "vrai-faux", "question": "'Thank you' is used to express gratitude.", "correct": True, "explanation": "'Thank you' exprime la gratitude."},
                        {"id": "1675_3", "type": "qcm", "question": "How do you respond to 'Thank you'?", "options": ["You're welcome.", "Sorry!", "No!", "Please!"], "correct_option": "You're welcome.", "explanation": "'You're welcome' est la réponse attendue."},
                        {"id": "1675_4", "type": "qcm", "question": "Which phrase is used to accept an apology?", "options": ["That's all right.", "No way!", "Never!", "Not at all!"], "correct_option": "That's all right.", "explanation": "Formule pour accepter des excuses."},
                        {"id": "1675_5", "type": "vrai-faux", "question": "'Excuse me' can be used to get attention.", "correct": True, "explanation": "'Excuse me' sert à attirer l'attention."},
                        {"id": "1675_6", "type": "qcm", "question": "Which is a polite way to refuse?", "options": ["No, thank you.", "No way!", "Never!", "Not at all!"], "correct_option": "No, thank you.", "explanation": "Formule polie pour refuser."},
                        {"id": "1675_7", "type": "qcm", "question": "How do you thank someone for a present?", "options": ["Thank you very much!", "Sorry!", "No!", "Please!"], "correct_option": "Thank you very much!", "explanation": "Formule de remerciement renforcée."},
                        {"id": "1675_8", "type": "vrai-faux", "question": "'No problem' can be used to respond to thanks.", "correct": True, "explanation": "'No problem' est une réponse informelle à un remerciement."}
                    ],
                ),
                (
                    1676,
                    "Diagnostic 2nde Anglais - Grammar: Passive Voice",
                    "Anglais",
                    "2nde",
                    [
                        {"id": "1676_1", "type": "qcm", "question": "Which sentence is in the passive voice?", "options": ["The cake was eaten.", "She eats the cake.", "She is eating the cake.", "She will eat the cake."], "correct_option": "The cake was eaten.", "explanation": "La voix passive: be + participe passé."},
                        {"id": "1676_2", "type": "vrai-faux", "question": "The passive voice focuses on the action, not the doer.", "correct": True, "explanation": "La voix passive met l'accent sur l'action ou le résultat."},
                        {"id": "1676_3", "type": "qcm", "question": "Choose the correct passive: 'The letter ___ by Tom.'", "options": ["was written", "wrote", "is write", "writes"], "correct_option": "was written", "explanation": "Forme passive correcte: was written."},
                        {"id": "1676_4", "type": "qcm", "question": "Which tense is this passive: 'The house will be built.'?", "options": ["present simple", "past simple", "future simple", "present continuous"], "correct_option": "future simple", "explanation": "'Will be built' = futur simple passif."},
                        {"id": "1676_5", "type": "vrai-faux", "question": "The passive is often used when the doer is unknown.", "correct": True, "explanation": "On utilise la voix passive quand l'agent est inconnu ou sans importance."},
                        {"id": "1676_6", "type": "qcm", "question": "Which is the passive of 'People speak English worldwide.'?", "options": ["English is spoken worldwide.", "English speaks worldwide.", "English was spoken worldwide.", "English is speaking worldwide."], "correct_option": "English is spoken worldwide.", "explanation": "Transformation correcte à la voix passive."},
                        {"id": "1676_7", "type": "qcm", "question": "Choose the correct passive: 'The homework ___ by the students.'", "options": ["is done", "do", "did", "does"], "correct_option": "is done", "explanation": "Forme passive correcte: is done."},
                        {"id": "1676_8", "type": "vrai-faux", "question": "The passive voice is common in news reports.", "correct": True, "explanation": "La voix passive est fréquente dans la presse."}
                    ],
                ),
                (
                    1677,
                    "Diagnostic 2nde Anglais - Vocabulary: Travel and Transport",
                    "Anglais",
                    "2nde",
                    [
                        {"id": "1677_1", "type": "qcm", "question": "Which word means 'to go from one place to another'?", "options": ["travel", "stay", "eat", "sleep"], "correct_option": "travel", "explanation": "'Travel' = se déplacer."},
                        {"id": "1677_2", "type": "vrai-faux", "question": "A 'ticket' is needed to take a train.", "correct": True, "explanation": "'Ticket' = billet de transport."},
                        {"id": "1677_3", "type": "qcm", "question": "What is the English for 'gare'?", "options": ["station", "airport", "bus", "port"], "correct_option": "station", "explanation": "'Station' = gare."},
                        {"id": "1677_4", "type": "qcm", "question": "Which is a means of public transport?", "options": ["bus", "car", "bicycle", "skateboard"], "correct_option": "bus", "explanation": "'Bus' = transport en commun."},
                        {"id": "1677_5", "type": "vrai-faux", "question": "'Luggage' means bags and suitcases.", "correct": True, "explanation": "'Luggage' = bagages."},
                        {"id": "1677_6", "type": "qcm", "question": "Which word means 'to arrive'?", "options": ["depart", "leave", "arrive", "miss"], "correct_option": "arrive", "explanation": "'Arrive' = arriver."},
                        {"id": "1677_7", "type": "qcm", "question": "What is the opposite of 'depart'?", "options": ["arrive", "leave", "travel", "miss"], "correct_option": "arrive", "explanation": "Antonyme: depart/arrive."},
                        {"id": "1677_8", "type": "vrai-faux", "question": "'Platform' is where you wait for a train.", "correct": True, "explanation": "'Platform' = quai de gare."}
                    ],
                ),
                (
                    1678,
                    "Diagnostic 2nde Anglais - Grammar: Reported Speech",
                    "Anglais",
                    "2nde",
                    [
                        {"id": "1678_1", "type": "qcm", "question": "Which is reported speech?", "options": ["He said he was tired.", "He says: 'I am tired.'", "He is tired.", "He will be tired."], "correct_option": "He said he was tired.", "explanation": "Le discours rapporté transforme la phrase d'origine."},
                        {"id": "1678_2", "type": "vrai-faux", "question": "Reported speech often uses the past tense.", "correct": True, "explanation": "On recule d'un temps au discours rapporté."},
                        {"id": "1678_3", "type": "qcm", "question": "Choose the correct transformation: 'I am happy' -> He said that he ___ happy.", "options": ["was", "is", "were", "be"], "correct_option": "was", "explanation": "On recule d'un temps: am -> was."},
                        {"id": "1678_4", "type": "qcm", "question": "Which word introduces reported speech?", "options": ["that", "if", "because", "when"], "correct_option": "that", "explanation": "'That' introduit la subordonnée au discours rapporté."},
                        {"id": "1678_5", "type": "vrai-faux", "question": "Pronouns often change in reported speech.", "correct": True, "explanation": "Les pronoms s'adaptent au contexte du discours rapporté."},
                        {"id": "1678_6", "type": "qcm", "question": "Which is the correct reported question: 'Where do you live?' -> He asked where I ___.", "options": ["lived", "live", "was living", "am living"], "correct_option": "lived", "explanation": "On recule d'un temps: do you live -> I lived."},
                        {"id": "1678_7", "type": "qcm", "question": "Choose the correct sentence.", "options": ["She said she will come.", "She said she would come.", "She said she comes.", "She said she is coming."], "correct_option": "She said she would come.", "explanation": "Will -> would au discours rapporté."},
                        {"id": "1678_8", "type": "vrai-faux", "question": "Reported speech is common in news and storytelling.", "correct": True, "explanation": "Le discours rapporté est fréquent dans la presse et les récits."}
                    ],
                ),
                (
                    1679,
                    "Diagnostic 2nde Anglais - Communication: Giving Directions",
                    "Anglais",
                    "2nde",
                    [
                        {"id": "1679_1", "type": "qcm", "question": "Which phrase is used to start giving directions?", "options": ["Go straight ahead.", "Sit down.", "Be quiet.", "Turn off the light."], "correct_option": "Go straight ahead.", "explanation": "Formule standard pour débuter des indications de direction."},
                        {"id": "1679_2", "type": "vrai-faux", "question": "'Turn left' is an instruction for direction.", "correct": True, "explanation": "'Turn left' = tourner à gauche."},
                        {"id": "1679_3", "type": "qcm", "question": "How do you tell someone to cross the street?", "options": ["Cross the street.", "Eat your lunch.", "Do your homework.", "Close the door."], "correct_option": "Cross the street.", "explanation": "Instruction claire pour traverser."},
                        {"id": "1679_4", "type": "qcm", "question": "Which is a place you might mention in directions?", "options": ["bank", "pencil", "teacher", "book"], "correct_option": "bank", "explanation": "'Bank' = lieu dans la ville."},
                        {"id": "1679_5", "type": "vrai-faux", "question": "'At the corner' means at the intersection of two streets.", "correct": True, "explanation": "'At the corner' = à l'angle de deux rues."},
                        {"id": "1679_6", "type": "qcm", "question": "Which phrase is used to finish giving directions?", "options": ["It's on your right.", "Sit down.", "Be quiet.", "Turn off the light."], "correct_option": "It's on your right.", "explanation": "Formule pour conclure des indications de direction."},
                        {"id": "1679_7", "type": "qcm", "question": "What is the opposite of 'left'?", "options": ["right", "straight", "back", "ahead"], "correct_option": "right", "explanation": "Antonyme: left/right."},
                        {"id": "1679_8", "type": "vrai-faux", "question": "'Near' means close to a place.", "correct": True, "explanation": "'Near' = proche de."}
                    ],
                ),
                    (
                        1680,
                        "Diagnostic 2nde Anglais - Grammar: Conditional Sentences",
                        "Anglais",
                        "2nde",
                        [
                            {"id": "1680_1", "type": "qcm", "question": "Which is a first conditional sentence?", "options": ["If it rains, I will stay home.", "If I was rich, I would travel.", "If I had known, I would have come.", "If I am late, I was sorry."], "correct_option": "If it rains, I will stay home.", "explanation": "First conditional: if + present, will + base verb."},
                            {"id": "1680_2", "type": "vrai-faux", "question": "Second conditional expresses unreal or hypothetical situations.", "correct": True, "explanation": "Second conditional: if + past, would + base verb."},
                            {"id": "1680_3", "type": "qcm", "question": "Choose the correct form: 'If I ___ you, I would study more.'", "options": ["was", "were", "am", "be"], "correct_option": "were", "explanation": "On utilise 'were' pour toutes les personnes dans le second conditional."},
                            {"id": "1680_4", "type": "qcm", "question": "Which is a third conditional sentence?", "options": ["If I had known, I would have come.", "If it rains, I will stay home.", "If I was rich, I would travel.", "If I am late, I was sorry."], "correct_option": "If I had known, I would have come.", "explanation": "Third conditional: if + past perfect, would have + past participle."},
                            {"id": "1680_5", "type": "vrai-faux", "question": "First conditional is used for real future possibilities.", "correct": True, "explanation": "Il exprime une conséquence possible dans le futur."},
                            {"id": "1680_6", "type": "qcm", "question": "Choose the correct sentence.", "options": ["If I see him, I will tell him.", "If I saw him, I will tell him.", "If I see him, I would tell him.", "If I had seen him, I tell him."], "correct_option": "If I see him, I will tell him.", "explanation": "First conditional: if + present, will + base verb."},
                            {"id": "1680_7", "type": "qcm", "question": "Which is the correct negative form?", "options": ["If it doesn't rain, we'll go.", "If it don't rain, we'll go.", "If it not rain, we'll go.", "If it isn't rain, we'll go."], "correct_option": "If it doesn't rain, we'll go.", "explanation": "Négation correcte au présent simple."},
                            {"id": "1680_8", "type": "vrai-faux", "question": "Third conditional is used for past regrets.", "correct": True, "explanation": "Il exprime un regret ou une situation irréelle dans le passé."}
                        ],
                    ),
                    (
                        1681,
                        "Diagnostic 2nde Anglais - Communication: Invitations and Offers",
                        "Anglais",
                        "2nde",
                        [
                            {"id": "1681_1", "type": "qcm", "question": "Which phrase is used to invite someone?", "options": ["Would you like to come?", "Go away!", "I don't care.", "Why bother?"], "correct_option": "Would you like to come?", "explanation": "Formule polie pour inviter."},
                            {"id": "1681_2", "type": "vrai-faux", "question": "'Can I offer you a drink?' is a polite offer.", "correct": True, "explanation": "Formule de politesse pour proposer quelque chose."},
                            {"id": "1681_3", "type": "qcm", "question": "How do you accept an invitation?", "options": ["I'd love to, thank you!", "No way!", "Never!", "Not at all!"], "correct_option": "I'd love to, thank you!", "explanation": "Formule positive pour accepter une invitation."},
                            {"id": "1681_4", "type": "qcm", "question": "Which is a polite way to refuse?", "options": ["I'm sorry, I can't.", "No!", "Never!", "Not at all!"], "correct_option": "I'm sorry, I can't.", "explanation": "Formule polie pour décliner une invitation."},
                            {"id": "1681_5", "type": "vrai-faux", "question": "'Shall I open the window?' is an offer.", "correct": True, "explanation": "'Shall I...' sert à proposer un service."},
                            {"id": "1681_6", "type": "qcm", "question": "Which phrase is used to make a suggestion?", "options": ["Let's go to the cinema.", "Go away!", "I don't care.", "Why bother?"], "correct_option": "Let's go to the cinema.", "explanation": "'Let's...' sert à faire une suggestion."},
                            {"id": "1681_7", "type": "qcm", "question": "How do you respond to an offer?", "options": ["Yes, please.", "No way!", "Never!", "Not at all!"], "correct_option": "Yes, please.", "explanation": "Réponse polie à une offre."},
                            {"id": "1681_8", "type": "vrai-faux", "question": "'Thanks for inviting me' is used to thank for an invitation.", "correct": True, "explanation": "Formule de remerciement après une invitation."}
                        ],
                    ),
                    (
                        1682,
                        "Diagnostic 2nde Anglais - Grammar: Gerunds and Infinitives",
                        "Anglais",
                        "2nde",
                        [
                            {"id": "1682_1", "type": "qcm", "question": "Which verb is followed by a gerund?", "options": ["enjoy", "want", "hope", "plan"], "correct_option": "enjoy", "explanation": "'Enjoy' est suivi d'un gérondif (verbe en -ing)."},
                            {"id": "1682_2", "type": "vrai-faux", "question": "'Want' is followed by an infinitive.", "correct": True, "explanation": "'Want to do' est la structure correcte."},
                            {"id": "1682_3", "type": "qcm", "question": "Choose the correct sentence.", "options": ["I like swimming.", "I like to swimming.", "I like swim.", "I like to swiming."], "correct_option": "I like swimming.", "explanation": "'Like' peut être suivi d'un gérondif ou d'un infinitif, mais ici 'swimming' est correct."},
                            {"id": "1682_4", "type": "qcm", "question": "Which verb is followed by an infinitive?", "options": ["decide", "enjoy", "finish", "avoid"], "correct_option": "decide", "explanation": "'Decide to do' est la structure correcte."},
                            {"id": "1682_5", "type": "vrai-faux", "question": "'Stop' can be followed by a gerund or an infinitive with a change in meaning.", "correct": True, "explanation": "'Stop doing' (arrêter), 'stop to do' (s'arrêter pour faire)."},
                            {"id": "1682_6", "type": "qcm", "question": "Which is correct?", "options": ["She promised to help.", "She promised helping.", "She promised help.", "She promised to helping."], "correct_option": "She promised to help.", "explanation": "'Promise' est suivi d'un infinitif."},
                            {"id": "1682_7", "type": "qcm", "question": "Choose the correct sentence.", "options": ["He finished doing his homework.", "He finished to do his homework.", "He finish doing his homework.", "He finish to do his homework."], "correct_option": "He finished doing his homework.", "explanation": "'Finish' est suivi d'un gérondif."},
                            {"id": "1682_8", "type": "vrai-faux", "question": "'Remember' can be followed by a gerund or an infinitive with a change in meaning.", "correct": True, "explanation": "'Remember doing' (se souvenir d'avoir fait), 'remember to do' (ne pas oublier de faire)."}
                        ],
                    ),
                    (
                        1683,
                        "Diagnostic 2nde Anglais - Communication: Describing People",
                        "Anglais",
                        "2nde",
                        [
                            {"id": "1683_1", "type": "qcm", "question": "Which word describes hair color?", "options": ["blond", "tall", "young", "friendly"], "correct_option": "blond", "explanation": "'Blond' décrit la couleur des cheveux."},
                            {"id": "1683_2", "type": "vrai-faux", "question": "'Tall' describes height.", "correct": True, "explanation": "'Tall' = grand (taille)."},
                            {"id": "1683_3", "type": "qcm", "question": "Which is a personality adjective?", "options": ["friendly", "brown", "short", "blue"], "correct_option": "friendly", "explanation": "'Friendly' décrit la personnalité."},
                            {"id": "1683_4", "type": "qcm", "question": "Choose the correct sentence.", "options": ["She has blue eyes.", "She have blue eyes.", "She is blue eyes.", "She has eyes blue."], "correct_option": "She has blue eyes.", "explanation": "Structure correcte: have/has + adj + nom."},
                            {"id": "1683_5", "type": "vrai-faux", "question": "'Young' is the opposite of 'old'.", "correct": True, "explanation": "Antonyme: young/old."},
                            {"id": "1683_6", "type": "qcm", "question": "Which word describes eye color?", "options": ["green", "tall", "kind", "short"], "correct_option": "green", "explanation": "'Green' décrit la couleur des yeux."},
                            {"id": "1683_7", "type": "qcm", "question": "What is the opposite of 'short'?", "options": ["tall", "young", "old", "friendly"], "correct_option": "tall", "explanation": "Antonyme: short/tall."},
                            {"id": "1683_8", "type": "vrai-faux", "question": "'Kind' is a positive personality trait.", "correct": True, "explanation": "'Kind' = gentil."}
                        ],
                    ),
                    (
                        1684,
                        "Diagnostic 2nde Anglais - Grammar: Relative Clauses",
                        "Anglais",
                        "2nde",
                        [
                            {"id": "1684_1", "type": "qcm", "question": "Which word introduces a relative clause?", "options": ["who", "and", "but", "so"], "correct_option": "who", "explanation": "'Who' introduit une subordonnée relative."},
                            {"id": "1684_2", "type": "vrai-faux", "question": "'Which' is used for things in relative clauses.", "correct": True, "explanation": "'Which' s'emploie pour les objets."},
                            {"id": "1684_3", "type": "qcm", "question": "Choose the correct sentence.", "options": ["The boy who is talking is my brother.", "The boy which is talking is my brother.", "The boy where is talking is my brother.", "The boy when is talking is my brother."], "correct_option": "The boy who is talking is my brother.", "explanation": "'Who' pour les personnes."},
                            {"id": "1684_4", "type": "qcm", "question": "Which is a defining relative clause?", "options": ["The book that I bought is interesting.", "The book, that I bought, is interesting.", "The book is interesting, that I bought.", "That I bought, the book is interesting."], "correct_option": "The book that I bought is interesting.", "explanation": "Relative définie sans virgule."},
                            {"id": "1684_5", "type": "vrai-faux", "question": "'That' can replace 'who' or 'which' in defining clauses.", "correct": True, "explanation": "'That' est polyvalent en relative définie."},
                            {"id": "1684_6", "type": "qcm", "question": "Which is the correct relative pronoun for places?", "options": ["where", "who", "which", "when"], "correct_option": "where", "explanation": "'Where' pour les lieux."},
                            {"id": "1684_7", "type": "qcm", "question": "Choose the correct sentence.", "options": ["This is the house where I live.", "This is the house who I live.", "This is the house which I live.", "This is the house when I live."], "correct_option": "This is the house where I live.", "explanation": "'Where' pour les lieux."},
                            {"id": "1684_8", "type": "vrai-faux", "question": "Relative clauses add information about a noun.", "correct": True, "explanation": "Elles précisent ou ajoutent une information sur le nom."}
                        ],
                    ),
                    (
                        1685,
                        "Diagnostic 2nde Anglais - Communication: Shopping and Services",
                        "Anglais",
                        "2nde",
                        [
                            {"id": "1685_1", "type": "qcm", "question": "Which phrase is used to ask the price?", "options": ["How much is it?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "How much is it?", "explanation": "Formule standard pour demander le prix."},
                            {"id": "1685_2", "type": "vrai-faux", "question": "'Receipt' is a document you get after paying.", "correct": True, "explanation": "'Receipt' = reçu."},
                            {"id": "1685_3", "type": "qcm", "question": "What is the English for 'magasin'?", "options": ["shop", "school", "hospital", "bank"], "correct_option": "shop", "explanation": "'Shop' = magasin."},
                            {"id": "1685_4", "type": "qcm", "question": "Which is a service you can find in a town?", "options": ["post office", "mountain", "river", "forest"], "correct_option": "post office", "explanation": "'Post office' = service postal."},
                            {"id": "1685_5", "type": "vrai-faux", "question": "'Change' means the money you get back after paying.", "correct": True, "explanation": "'Change' = monnaie rendue."},
                            {"id": "1685_6", "type": "qcm", "question": "Which phrase is used to ask for help in a shop?", "options": ["Can you help me, please?", "Go away!", "I don't care.", "Why bother?"], "correct_option": "Can you help me, please?", "explanation": "Formule polie pour demander de l'aide."},
                            {"id": "1685_7", "type": "qcm", "question": "How do you ask to try something on?", "options": ["Can I try it on?", "Can I eat it?", "Can I read it?", "Can I see it?"], "correct_option": "Can I try it on?", "explanation": "Formule pour essayer un vêtement."},
                            {"id": "1685_8", "type": "vrai-faux", "question": "'Sale' means a period when prices are reduced.", "correct": True, "explanation": "'Sale' = période de soldes."}
                        ],
                    ),
                        (
                            1686,
                            "Diagnostic 2nde Anglais - Grammar: Adverbs and Word Order",
                            "Anglais",
                            "2nde",
                            [
                                {"id": "1686_1", "type": "qcm", "question": "Which is an adverb of frequency?", "options": ["always", "cat", "blue", "run"], "correct_option": "always", "explanation": "'Always' est un adverbe de fréquence."},
                                {"id": "1686_2", "type": "vrai-faux", "question": "Adverbs of frequency usually go before the main verb.", "correct": True, "explanation": "Ex: I always eat breakfast."},
                                {"id": "1686_3", "type": "qcm", "question": "Choose the correct word order: 'She / always / is / on time.'", "options": ["She is always on time.", "She always is on time.", "Always she is on time.", "She on time is always."], "correct_option": "She is always on time.", "explanation": "L'adverbe se place après l'auxiliaire."},
                                {"id": "1686_4", "type": "qcm", "question": "Which is an adverb of manner?", "options": ["quickly", "dog", "red", "table"], "correct_option": "quickly", "explanation": "'Quickly' décrit la manière d'agir."},
                                {"id": "1686_5", "type": "vrai-faux", "question": "Adverbs of manner usually go after the verb or object.", "correct": True, "explanation": "Ex: She sings beautifully."},
                                {"id": "1686_6", "type": "qcm", "question": "Choose the correct sentence.", "options": ["He drives carefully.", "He carefully drives.", "Carefully he drives.", "He drives careful."], "correct_option": "He drives carefully.", "explanation": "Adverbe de manière après le verbe."},
                                {"id": "1686_7", "type": "qcm", "question": "Which is the correct negative sentence?", "options": ["She doesn't always eat meat.", "She always doesn't eat meat.", "She doesn't eat always meat.", "She eat doesn't always meat."], "correct_option": "She doesn't always eat meat.", "explanation": "Négation correcte avec adverbe de fréquence."},
                                {"id": "1686_8", "type": "vrai-faux", "question": "Adverbs can modify adjectives, verbs, or other adverbs.", "correct": True, "explanation": "Ex: very quickly, really good."}
                            ],
                        ),
                        (
                            1687,
                            "Diagnostic 2nde Anglais - Communication: Health and Emergencies",
                            "Anglais",
                            "2nde",
                            [
                                {"id": "1687_1", "type": "qcm", "question": "Which phrase is used to call for help?", "options": ["Help!", "Sit down!", "Be quiet!", "Turn off the light!"], "correct_option": "Help!", "explanation": "Formule d'urgence pour demander de l'aide."},
                                {"id": "1687_2", "type": "vrai-faux", "question": "'Call an ambulance!' is used in emergencies.", "correct": True, "explanation": "Phrase d'urgence en cas d'accident."},
                                {"id": "1687_3", "type": "qcm", "question": "What is the English for 'malade'?", "options": ["ill", "happy", "tall", "blue"], "correct_option": "ill", "explanation": "'Ill' = malade."},
                                {"id": "1687_4", "type": "qcm", "question": "Which is a symptom?", "options": ["fever", "table", "cat", "red"], "correct_option": "fever", "explanation": "'Fever' = fièvre, symptôme courant."},
                                {"id": "1687_5", "type": "vrai-faux", "question": "'Pharmacy' is a place to buy medicine.", "correct": True, "explanation": "'Pharmacy' = pharmacie."},
                                {"id": "1687_6", "type": "qcm", "question": "Which phrase is used to describe pain?", "options": ["I have a headache.", "I am happy.", "I am tall.", "I am blue."], "correct_option": "I have a headache.", "explanation": "Formule pour décrire une douleur."},
                                {"id": "1687_7", "type": "qcm", "question": "What is the English for 'urgence'?", "options": ["emergency", "holiday", "party", "lesson"], "correct_option": "emergency", "explanation": "'Emergency' = urgence."},
                                {"id": "1687_8", "type": "vrai-faux", "question": "'Doctor' is a person who treats illnesses.", "correct": True, "explanation": "'Doctor' = médecin."}
                            ],
                        ),
                        (
                            1688,
                            "Diagnostic 2nde Anglais - Grammar: Articles and Quantifiers",
                            "Anglais",
                            "2nde",
                            [
                                {"id": "1688_1", "type": "qcm", "question": "Which is a definite article?", "options": ["the", "a", "an", "some"], "correct_option": "the", "explanation": "'The' est l'article défini."},
                                {"id": "1688_2", "type": "vrai-faux", "question": "'A' and 'an' are indefinite articles.", "correct": True, "explanation": "'A' et 'an' sont des articles indéfinis."},
                                {"id": "1688_3", "type": "qcm", "question": "Choose the correct sentence.", "options": ["I have a dog.", "I have dog.", "I have the dog.", "I have an dog."], "correct_option": "I have a dog.", "explanation": "Article indéfini devant un nom singulier."},
                                {"id": "1688_4", "type": "qcm", "question": "Which quantifier is used with uncountable nouns?", "options": ["much", "many", "few", "several"], "correct_option": "much", "explanation": "'Much' s'emploie avec les indénombrables."},
                                {"id": "1688_5", "type": "vrai-faux", "question": "'Many' is used with plural countable nouns.", "correct": True, "explanation": "'Many' pour les noms pluriels comptables."},
                                {"id": "1688_6", "type": "qcm", "question": "Which is the correct negative sentence?", "options": ["I don't have any money.", "I don't have no money.", "I have not any money.", "I have no any money."], "correct_option": "I don't have any money.", "explanation": "Négation correcte avec 'any'."},
                                {"id": "1688_7", "type": "qcm", "question": "Which is a quantifier for large quantities?", "options": ["a lot of", "a few", "a little", "some"], "correct_option": "a lot of", "explanation": "'A lot of' exprime une grande quantité."},
                                {"id": "1688_8", "type": "vrai-faux", "question": "'Few' is used with plural countable nouns to mean not many.", "correct": True, "explanation": "'Few' = peu de (pluriel comptable)."}
                            ],
                        ),
                        (
                            1689,
                            "Diagnostic 2nde Anglais - Communication: Describing Places",
                            "Anglais",
                            "2nde",
                            [
                                {"id": "1689_1", "type": "qcm", "question": "Which word describes a city?", "options": ["crowded", "cat", "blue", "run"], "correct_option": "crowded", "explanation": "'Crowded' décrit une ville pleine de monde."},
                                {"id": "1689_2", "type": "vrai-faux", "question": "'Quiet' describes a place with little noise.", "correct": True, "explanation": "'Quiet' = calme, silencieux."},
                                {"id": "1689_3", "type": "qcm", "question": "Choose the correct sentence.", "options": ["The park is beautiful.", "The park beautiful is.", "Beautiful is the park.", "Is beautiful the park."], "correct_option": "The park is beautiful.", "explanation": "Structure correcte: sujet + verbe + adj."},
                                {"id": "1689_4", "type": "qcm", "question": "Which is a feature of a town?", "options": ["museum", "cat", "blue", "run"], "correct_option": "museum", "explanation": "'Museum' = équipement urbain."},
                                {"id": "1689_5", "type": "vrai-faux", "question": "'Noisy' is the opposite of 'quiet'.", "correct": True, "explanation": "Antonyme: noisy/quiet."},
                                {"id": "1689_6", "type": "qcm", "question": "Which word describes the countryside?", "options": ["rural", "urban", "crowded", "noisy"], "correct_option": "rural", "explanation": "'Rural' = campagne."},
                                {"id": "1689_7", "type": "qcm", "question": "What is the opposite of 'modern'?", "options": ["ancient", "urban", "crowded", "quiet"], "correct_option": "ancient", "explanation": "Antonyme: modern/ancient."},
                                {"id": "1689_8", "type": "vrai-faux", "question": "'Attractive' means pleasant to look at.", "correct": True, "explanation": "'Attractive' = agréable à regarder."}
                            ],
                        ),
                        (
                            1690,
                            "Diagnostic 2nde Anglais - Grammar: Pronouns and Possessives",
                            "Anglais",
                            "2nde",
                            [
                                {"id": "1690_1", "type": "qcm", "question": "Which is a subject pronoun?", "options": ["he", "his", "him", "hers"], "correct_option": "he", "explanation": "'He' est un pronom sujet."},
                                {"id": "1690_2", "type": "vrai-faux", "question": "'His' is a possessive adjective.", "correct": True, "explanation": "'His' = adjectif possessif."},
                                {"id": "1690_3", "type": "qcm", "question": "Choose the correct sentence.", "options": ["This is my book.", "This is me book.", "This is I book.", "This is mine book."], "correct_option": "This is my book.", "explanation": "'My' = adjectif possessif."},
                                {"id": "1690_4", "type": "qcm", "question": "Which is an object pronoun?", "options": ["him", "he", "his", "hers"], "correct_option": "him", "explanation": "'Him' = pronom objet."},
                                {"id": "1690_5", "type": "vrai-faux", "question": "'Hers' is a possessive pronoun.", "correct": True, "explanation": "'Hers' = pronom possessif."},
                                {"id": "1690_6", "type": "qcm", "question": "Which is the correct possessive form?", "options": ["John's car", "John car's", "Car's John", "Car John"], "correct_option": "John's car", "explanation": "Forme possessive correcte: nom + 's + objet."},
                                {"id": "1690_7", "type": "qcm", "question": "What is the plural of 'child'?", "options": ["children", "childs", "childes", "childen"], "correct_option": "children", "explanation": "Pluriel irrégulier: child/children."},
                                {"id": "1690_8", "type": "vrai-faux", "question": "'Ours' is a possessive pronoun.", "correct": True, "explanation": "'Ours' = pronom possessif."}
                            ],
                        ),
                        (
                            1691,
                            "Diagnostic 2nde Anglais - Communication: Making Complaints",
                            "Anglais",
                            "2nde",
                            [
                                {"id": "1691_1", "type": "qcm", "question": "Which phrase is used to make a complaint?", "options": ["I'm not satisfied.", "Thank you!", "See you!", "Good luck!"], "correct_option": "I'm not satisfied.", "explanation": "Formule standard pour exprimer une plainte."},
                                {"id": "1691_2", "type": "vrai-faux", "question": "'I'd like to speak to the manager' is a formal complaint.", "correct": True, "explanation": "Formule formelle pour demander un responsable."},
                                {"id": "1691_3", "type": "qcm", "question": "How do you ask for compensation?", "options": ["Can I have a refund?", "Can I have a coffee?", "Can I have a seat?", "Can I have a look?"], "correct_option": "Can I have a refund?", "explanation": "Formule pour demander un remboursement."},
                                {"id": "1691_4", "type": "qcm", "question": "Which is a polite way to express dissatisfaction?", "options": ["I'm afraid I'm not happy with this.", "No way!", "Never!", "Not at all!"], "correct_option": "I'm afraid I'm not happy with this.", "explanation": "Formule polie pour exprimer un mécontentement."},
                                {"id": "1691_5", "type": "vrai-faux", "question": "'This is unacceptable' is a strong complaint.", "correct": True, "explanation": "Formule forte pour exprimer une plainte."},
                                {"id": "1691_6", "type": "qcm", "question": "How do you ask for a solution?", "options": ["What can you do about it?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "What can you do about it?", "explanation": "Formule pour demander une solution."},
                                {"id": "1691_7", "type": "qcm", "question": "Which phrase is used to insist?", "options": ["I insist.", "I agree.", "I refuse.", "I accept."], "correct_option": "I insist.", "explanation": "Formule pour insister sur une demande."},
                                {"id": "1691_8", "type": "vrai-faux", "question": "'Thank you for your help' is a polite way to end a complaint.", "correct": True, "explanation": "Formule de clôture polie après une plainte."}
                            ],
                        ),
                            (
                                1692,
                                "Diagnostic 2nde Anglais - Grammar: Prepositions of Time and Place",
                                "Anglais",
                                "2nde",
                                [
                                    {"id": "1692_1", "type": "qcm", "question": "Which is a preposition of time?", "options": ["at", "cat", "blue", "run"], "correct_option": "at", "explanation": "'At' s'emploie pour l'heure: at 5 o'clock."},
                                    {"id": "1692_2", "type": "vrai-faux", "question": "'In' is used for months and years.", "correct": True, "explanation": "Ex: in July, in 2026."},
                                    {"id": "1692_3", "type": "qcm", "question": "Choose the correct sentence: 'I go to school ___ bus.'", "options": ["by", "on", "in", "at"], "correct_option": "by", "explanation": "'By bus' est la préposition correcte pour le moyen de transport."},
                                    {"id": "1692_4", "type": "qcm", "question": "Which is a preposition of place?", "options": ["under", "cat", "blue", "run"], "correct_option": "under", "explanation": "'Under' indique la position sous quelque chose."},
                                    {"id": "1692_5", "type": "vrai-faux", "question": "'On' is used for days and dates.", "correct": True, "explanation": "Ex: on Monday, on 18 April."},
                                    {"id": "1692_6", "type": "qcm", "question": "Choose the correct sentence: 'The book is ___ the table.'", "options": ["on", "in", "at", "by"], "correct_option": "on", "explanation": "'On the table' est la préposition correcte."},
                                    {"id": "1692_7", "type": "qcm", "question": "Which is the correct preposition for cities?", "options": ["in", "on", "at", "by"], "correct_option": "in", "explanation": "'In Paris', 'in London'."},
                                    {"id": "1692_8", "type": "vrai-faux", "question": "'At' is used for specific places.", "correct": True, "explanation": "Ex: at the station, at home."}
                                ],
                            ),
                            (
                                1693,
                                "Diagnostic 2nde Anglais - Communication: Expressing Preferences",
                                "Anglais",
                                "2nde",
                                [
                                    {"id": "1693_1", "type": "qcm", "question": "Which phrase is used to express a preference?", "options": ["I prefer", "I agree", "I refuse", "I accept"], "correct_option": "I prefer", "explanation": "'I prefer' exprime une préférence."},
                                    {"id": "1693_2", "type": "vrai-faux", "question": "'Would rather' is another way to express preference.", "correct": True, "explanation": "'Would rather' = préférer."},
                                    {"id": "1693_3", "type": "qcm", "question": "How do you ask about someone's preference?", "options": ["Which do you prefer?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "Which do you prefer?", "explanation": "Formule pour demander une préférence."},
                                    {"id": "1693_4", "type": "qcm", "question": "Which is a polite way to express preference?", "options": ["I'd rather", "No way!", "Never!", "Not at all!"], "correct_option": "I'd rather", "explanation": "Formule polie pour exprimer une préférence."},
                                    {"id": "1693_5", "type": "vrai-faux", "question": "'Like' can be used to express general preferences.", "correct": True, "explanation": "'Like' exprime un goût général."},
                                    {"id": "1693_6", "type": "qcm", "question": "Choose the correct sentence.", "options": ["I like swimming.", "I like to swimming.", "I like swim.", "I like to swiming."], "correct_option": "I like swimming.", "explanation": "'Like' peut être suivi d'un gérondif."},
                                    {"id": "1693_7", "type": "qcm", "question": "Which is the correct negative form?", "options": ["I don't like coffee.", "I not like coffee.", "I doesn't like coffee.", "I no like coffee."], "correct_option": "I don't like coffee.", "explanation": "Négation correcte avec 'don't'."},
                                    {"id": "1693_8", "type": "vrai-faux", "question": "'Dislike' is the opposite of 'like'.", "correct": True, "explanation": "Antonyme: like/dislike."}
                                ],
                            ),
                            (
                                1694,
                                "Diagnostic 2nde Anglais - Grammar: Countable and Uncountable Nouns",
                                "Anglais",
                                "2nde",
                                [
                                    {"id": "1694_1", "type": "qcm", "question": "Which is a countable noun?", "options": ["apple", "water", "rice", "bread"], "correct_option": "apple", "explanation": "'Apple' est un nom comptable."},
                                    {"id": "1694_2", "type": "vrai-faux", "question": "'Water' is an uncountable noun.", "correct": True, "explanation": "'Water' = indénombrable."},
                                    {"id": "1694_3", "type": "qcm", "question": "Choose the correct sentence.", "options": ["I have some apples.", "I have some apple.", "I have any apples.", "I have any apple."], "correct_option": "I have some apples.", "explanation": "'Some' s'emploie avec les noms comptables au pluriel."},
                                    {"id": "1694_4", "type": "qcm", "question": "Which is the correct question for uncountable nouns?", "options": ["How much water?", "How many water?", "How much apples?", "How many apple?"], "correct_option": "How much water?", "explanation": "'How much' pour les indénombrables."},
                                    {"id": "1694_5", "type": "vrai-faux", "question": "'Many' is used with countable nouns.", "correct": True, "explanation": "'Many' pour les noms comptables."},
                                    {"id": "1694_6", "type": "qcm", "question": "Which is the correct negative sentence?", "options": ["I don't have any bread.", "I don't have no bread.", "I have not any bread.", "I have no any bread."], "correct_option": "I don't have any bread.", "explanation": "Négation correcte avec 'any'."},
                                    {"id": "1694_7", "type": "qcm", "question": "Which is a quantifier for uncountable nouns?", "options": ["a little", "a few", "many", "several"], "correct_option": "a little", "explanation": "'A little' pour les indénombrables."},
                                    {"id": "1694_8", "type": "vrai-faux", "question": "'Few' is used with countable nouns.", "correct": True, "explanation": "'Few' = peu de (comptable)."}
                                ],
                            ),
                            (
                                1695,
                                "Diagnostic 2nde Anglais - Communication: Arranging Travel",
                                "Anglais",
                                "2nde",
                                [
                                    {"id": "1695_1", "type": "qcm", "question": "Which phrase is used to book a ticket?", "options": ["I'd like to book a ticket.", "I want a coffee.", "I need a pen.", "I have a cat."], "correct_option": "I'd like to book a ticket.", "explanation": "Formule standard pour réserver un billet."},
                                    {"id": "1695_2", "type": "vrai-faux", "question": "'Departure' is the time a train leaves.", "correct": True, "explanation": "'Departure' = départ."},
                                    {"id": "1695_3", "type": "qcm", "question": "What is the English for 'billet'?", "options": ["ticket", "school", "hospital", "bank"], "correct_option": "ticket", "explanation": "'Ticket' = billet."},
                                    {"id": "1695_4", "type": "qcm", "question": "Which is a travel document?", "options": ["passport", "cat", "blue", "run"], "correct_option": "passport", "explanation": "'Passport' = document de voyage."},
                                    {"id": "1695_5", "type": "vrai-faux", "question": "'Arrival' is the time a train gets to its destination.", "correct": True, "explanation": "'Arrival' = arrivée."},
                                    {"id": "1695_6", "type": "qcm", "question": "Which phrase is used to ask for information?", "options": ["Can you tell me...?", "Go away!", "I don't care.", "Why bother?"], "correct_option": "Can you tell me...?", "explanation": "Formule polie pour demander une information."},
                                    {"id": "1695_7", "type": "qcm", "question": "How do you ask about the price?", "options": ["How much is it?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "How much is it?", "explanation": "Formule pour demander le prix."},
                                    {"id": "1695_8", "type": "vrai-faux", "question": "'Platform' is where you wait for a train.", "correct": True, "explanation": "'Platform' = quai de gare."}
                                ],
                            ),
                            (
                                1696,
                                "Diagnostic 2nde Anglais - Grammar: Modal Verbs for Advice and Obligation",
                                "Anglais",
                                "2nde",
                                [
                                    {"id": "1696_1", "type": "qcm", "question": "Which modal verb expresses advice?", "options": ["should", "must", "can", "may"], "correct_option": "should", "explanation": "'Should' sert à donner un conseil."},
                                    {"id": "1696_2", "type": "vrai-faux", "question": "'Must' expresses strong obligation.", "correct": True, "explanation": "'Must' = obligation forte."},
                                    {"id": "1696_3", "type": "qcm", "question": "Choose the correct sentence.", "options": ["You should see a doctor.", "You should seeing a doctor.", "You should saw a doctor.", "You should to see a doctor."], "correct_option": "You should see a doctor.", "explanation": "Structure correcte: should + base verbale."},
                                    {"id": "1696_4", "type": "qcm", "question": "Which modal verb is used for permission?", "options": ["may", "must", "should", "can"], "correct_option": "may", "explanation": "'May' pour la permission formelle."},
                                    {"id": "1696_5", "type": "vrai-faux", "question": "'Can' can express both ability and permission.", "correct": True, "explanation": "'Can' = capacité ou permission."},
                                    {"id": "1696_6", "type": "qcm", "question": "Which is the correct negative form?", "options": ["You must not park here.", "You must park not here.", "You not must park here.", "You mustn't park here."], "correct_option": "You must not park here.", "explanation": "Négation correcte avec 'must not'."},
                                    {"id": "1696_7", "type": "qcm", "question": "How do you ask for advice?", "options": ["What should I do?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "What should I do?", "explanation": "Formule pour demander un conseil."},
                                    {"id": "1696_8", "type": "vrai-faux", "question": "'Have to' can also express obligation.", "correct": True, "explanation": "'Have to' = obligation externe."}
                                ],
                            ),
                            (
                                1697,
                                "Diagnostic 2nde Anglais - Communication: Social Interactions",
                                "Anglais",
                                "2nde",
                                [
                                    {"id": "1697_1", "type": "qcm", "question": "Which phrase is used to introduce someone?", "options": ["This is my friend.", "I want a coffee.", "I need a pen.", "I have a cat."], "correct_option": "This is my friend.", "explanation": "Formule standard pour présenter quelqu'un."},
                                    {"id": "1697_2", "type": "vrai-faux", "question": "'Nice to meet you' is used when meeting someone for the first time.", "correct": True, "explanation": "Formule de salutation lors d'une première rencontre."},
                                    {"id": "1697_3", "type": "qcm", "question": "How do you ask about someone's job?", "options": ["What do you do?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "What do you do?", "explanation": "Formule pour demander la profession."},
                                    {"id": "1697_4", "type": "qcm", "question": "Which is a polite way to end a conversation?", "options": ["It was nice talking to you.", "Go away!", "I don't care.", "Why bother?"], "correct_option": "It was nice talking to you.", "explanation": "Formule de clôture polie."},
                                    {"id": "1697_5", "type": "vrai-faux", "question": "'See you soon' is a way to say goodbye.", "correct": True, "explanation": "Formule de prise de congé."},
                                    {"id": "1697_6", "type": "qcm", "question": "Which phrase is used to ask about hobbies?", "options": ["What are your hobbies?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "What are your hobbies?", "explanation": "Formule pour demander les loisirs."},
                                    {"id": "1697_7", "type": "qcm", "question": "How do you ask about family?", "options": ["Do you have any brothers or sisters?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "Do you have any brothers or sisters?", "explanation": "Formule pour demander la composition familiale."},
                                    {"id": "1697_8", "type": "vrai-faux", "question": "'Congratulations' is used to celebrate someone's success.", "correct": True, "explanation": "Formule de félicitations."}
                                ],
                            ),
                                (
                                    1698,
                                    "Diagnostic 2nde Anglais - Grammar: Direct and Indirect Questions",
                                    "Anglais",
                                    "2nde",
                                    [
                                        {"id": "1698_1", "type": "qcm", "question": "Which is a direct question?", "options": ["Where do you live?", "I wonder where you live.", "Could you tell me where you live?", "I don't know where you live."], "correct_option": "Where do you live?", "explanation": "Direct: Where do you live?"},
                                        {"id": "1698_2", "type": "vrai-faux", "question": "Indirect questions are more polite.", "correct": True, "explanation": "Les questions indirectes sont plus polies."},
                                        {"id": "1698_3", "type": "qcm", "question": "Choose the correct indirect question: 'Where is the station?'", "options": ["Could you tell me where the station is?", "Could you tell me where is the station?", "Where is the station could you tell me?", "Tell me where is the station?"], "correct_option": "Could you tell me where the station is?", "explanation": "Structure correcte: ...where the station is."},
                                        {"id": "1698_4", "type": "qcm", "question": "Which phrase introduces an indirect question?", "options": ["Do you know", "Blue", "Quickly", "Always"], "correct_option": "Do you know", "explanation": "'Do you know' introduit une question indirecte."},
                                        {"id": "1698_5", "type": "vrai-faux", "question": "Word order changes in indirect questions.", "correct": True, "explanation": "L'ordre des mots change dans les questions indirectes."},
                                        {"id": "1698_6", "type": "qcm", "question": "Which is a polite way to ask for information?", "options": ["Could you tell me...?", "Go away!", "I don't care.", "Why bother?"], "correct_option": "Could you tell me...?", "explanation": "Formule polie pour demander une information."},
                                        {"id": "1698_7", "type": "qcm", "question": "Choose the correct sentence.", "options": ["I wonder what time it is.", "I wonder what time is it.", "What time is it I wonder?", "I wonder is it what time."], "correct_option": "I wonder what time it is.", "explanation": "Structure correcte: ...what time it is."},
                                        {"id": "1698_8", "type": "vrai-faux", "question": "Indirect questions are common in formal writing.", "correct": True, "explanation": "Les questions indirectes sont fréquentes à l'écrit formel."}
                                    ],
                                ),
                                (
                                    1699,
                                    "Diagnostic 2nde Anglais - Communication: Giving Advice",
                                    "Anglais",
                                    "2nde",
                                    [
                                        {"id": "1699_1", "type": "qcm", "question": "Which phrase is used to give advice?", "options": ["You should", "I want", "I need", "I have"], "correct_option": "You should", "explanation": "'You should' sert à donner un conseil."},
                                        {"id": "1699_2", "type": "vrai-faux", "question": "'If I were you' is a way to give advice.", "correct": True, "explanation": "'If I were you' = conseil hypothétique."},
                                        {"id": "1699_3", "type": "qcm", "question": "How do you ask for advice?", "options": ["What should I do?", "What time is it?", "Where is it?", "Who is it?"], "correct_option": "What should I do?", "explanation": "Formule pour demander un conseil."},
                                        {"id": "1699_4", "type": "qcm", "question": "Which is a polite way to give advice?", "options": ["Maybe you could", "No way!", "Never!", "Not at all!"], "correct_option": "Maybe you could", "explanation": "Formule polie pour suggérer un conseil."},
                                        {"id": "1699_5", "type": "vrai-faux", "question": "'You ought to' is another way to give advice.", "correct": True, "explanation": "'Ought to' = conseil."},
                                        {"id": "1699_6", "type": "qcm", "question": "Choose the correct sentence.", "options": ["You should see a doctor.", "You should seeing a doctor.", "You should saw a doctor.", "You should to see a doctor."], "correct_option": "You should see a doctor.", "explanation": "Structure correcte: should + base verbale."},
                                        {"id": "1699_7", "type": "qcm", "question": "How do you respond to advice?", "options": ["Thank you for your advice.", "No way!", "Never!", "Not at all!"], "correct_option": "Thank you for your advice.", "explanation": "Formule de remerciement après un conseil."},
                                        {"id": "1699_8", "type": "vrai-faux", "question": "Advice is often given using modal verbs.", "correct": True, "explanation": "Les modaux servent à donner des conseils."}
                                    ],
                                ),
                                (
                                    1700,
                                    "Diagnostic 2nde Anglais - Grammar: Subject-Verb Agreement",
                                    "Anglais",
                                    "2nde",
                                    [
                                        {"id": "1700_1", "type": "qcm", "question": "Which sentence is correct?", "options": ["She goes to school.", "She go to school.", "She going to school.", "She gone to school."], "correct_option": "She goes to school.", "explanation": "3e personne du singulier: -s au verbe."},
                                        {"id": "1700_2", "type": "vrai-faux", "question": "'They play football' is correct for the plural.", "correct": True, "explanation": "Pas de -s au verbe pour le pluriel."},
                                        {"id": "1700_3", "type": "qcm", "question": "Choose the correct negative form: 'He ___ like apples.'", "options": ["doesn't", "don't", "isn't", "aren't"], "correct_option": "doesn't", "explanation": "Négation correcte à la 3e personne: doesn't."},
                                        {"id": "1700_4", "type": "qcm", "question": "Which is the correct question form?", "options": ["Does she like music?", "Do she like music?", "Is she like music?", "Are she like music?"], "correct_option": "Does she like music?", "explanation": "Question correcte à la 3e personne: does she...?"},
                                        {"id": "1700_5", "type": "vrai-faux", "question": "'He don't like apples' is incorrect.", "correct": True, "explanation": "Il faut 'doesn't' à la 3e personne."},
                                        {"id": "1700_6", "type": "qcm", "question": "Which is the correct plural form?", "options": ["They are students.", "They is students.", "They am students.", "They be students."], "correct_option": "They are students.", "explanation": "'Are' pour le pluriel."},
                                        {"id": "1700_7", "type": "qcm", "question": "Choose the correct sentence.", "options": ["The children play.", "The children plays.", "The children playing.", "The children played."], "correct_option": "The children play.", "explanation": "Pas de -s au verbe pour le pluriel."},
                                        {"id": "1700_8", "type": "vrai-faux", "question": "Subject-verb agreement is essential for correct grammar.", "correct": True, "explanation": "L'accord sujet-verbe est fondamental."}
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
        print(f"  [OK] {qid}.json - {title}")

    print(f"\n[OK] {count} quiz generes (+ {count} reponses)")


if __name__ == "__main__":
    print("Generating quizzes from template IA...")
    write_quiz_files()
