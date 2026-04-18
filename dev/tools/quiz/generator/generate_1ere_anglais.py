#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Template IA Ã¢â‚¬â€œ generate_<niveau>_<matiere>.py
"""

from __future__ import annotations

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))

# Ãƒâ‚¬ adapter dans chaque clone
BASENAME = "anglais_1ere_quizzes"

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
    markers = ("ÃƒÆ’", "Ãƒâ€š", "ÃƒÂ¢Ã¢â€šÂ¬", "ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢", "ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œ", "ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â", "ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“", "Ãƒâ€¦")
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
        "ÃƒÆ’Ã‚Â©": "ÃƒÂ©", "ÃƒÆ’Ã‚Â¨": "ÃƒÂ¨", "ÃƒÆ’Ã‚Âª": "ÃƒÂª", "ÃƒÆ’Ã‚Â«": "ÃƒÂ«", "ÃƒÆ’Ã‚Â ": "ÃƒÂ ", "ÃƒÆ’Ã‚Â¢": "ÃƒÂ¢",
        "ÃƒÆ’Ã‚Â´": "ÃƒÂ´", "ÃƒÆ’Ã‚Â»": "ÃƒÂ»", "ÃƒÆ’Ã‚Â¹": "ÃƒÂ¹", "ÃƒÆ’Ã‚Â®": "ÃƒÂ®", "ÃƒÆ’Ã‚Â¯": "ÃƒÂ¯", "ÃƒÆ’Ã‚Â§": "ÃƒÂ§",
        "ÃƒÆ’Ã¢â‚¬Â°": "Ãƒâ€°", "ÃƒÆ’Ã¢â€šÂ¬": "Ãƒâ‚¬", "ÃƒÆ’Ã¢â‚¬Â¡": "Ãƒâ€¡", "Ãƒâ€¦Ã¢â‚¬Å“": "Ã…â€œ", "Ãƒâ€š": "", "ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢": "Ã¢â‚¬â„¢",
        "ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œ": "Ã¢â‚¬Å“", "ÃƒÂ¢Ã¢â€šÂ¬\x9d": "Ã¢â‚¬Â", "ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“": "Ã¢â‚¬â€œ", "ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Â": "Ã¢â‚¬â€", "ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦": "Ã¢â‚¬Â¦",
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
    if qt == "qcm":
        return "qcm"
    return "vrai-faux"


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
                "type": "vrai-faux",
                "question": str(question.get("question", "")),
            }
        quiz_questions.append(sanitized)

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - SÃƒÂ©rie {qid}",
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
            tf_source = q.get("correct", q.get("correct_answer", "faux"))
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
            "title": f"Quiz Diagnostic {subject} {level} - SÃƒÂ©rie {qid}",
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
        (1059, "Diagnostic Anglais 1Ã¨re - SÃ©rie 1", "Anglais", "1Ã¨re", [
            {
                'id': '1059_1',
                'type': 'qcm',
                'question': 'What is the past tense of the verb "go"?',
                'options': ['goed', 'went', 'gone', 'goes'],
                'correct_option': 'went',
                'explanation': 'The past tense of "go" is "went".',
            },
            {
                'id': '1059_2',
                'type': 'vrai-faux',
                'question': 'The word "their" is a possessive pronoun.',
                'correct': True,
                'explanation': '"Their" is a possessive pronoun used to indicate ownership.',
            },
            {
                'id': '1059_3',
                'type': 'vrai-faux',
                'question': 'Translate the sentence "I am going to the market" into French.',
                'correct_answer': 'Je vais au marchÃ©',
                'explanation': 'The correct translation of "I am going to the market" in French is "Je vais au marchÃ©".',
            },
            {
                'id': '1059_4',
                'type': 'qcm',
                'question': 'Which of the following is a synonym for "happy"?',
                'options': ['sad', 'angry', 'joyful', 'tired'],
                'correct_option': 'joyful',
                'explanation': '"Joyful" is a synonym for "happy".',
            },
            {
                'id': '1059_5',
                'type': 'vrai-faux',
                'question': 'The word "affect" is a noun.',
                'correct': False,
                'explanation': '"Affect" is primarily a verb, although it can also be used as a noun in psychology.',
            },
            {
                'id': '1059_6',
                'type': 'vrai-faux',
                'question': 'What is the plural form of "child"?',
                'correct_answer': 'children',
                'explanation': 'The plural form of "child" is "children".',
            },
            {
                'id': '1059_7',
                'type': 'qcm',
                'question': 'Choose the correct sentence:',
                'options': ['She don\'t like pizza.', 'She doesn\'t like pizza.', 'She isn\'t like pizza.', 'She no like pizza.'],
                'correct_option': 'She doesn\'t like pizza.',
                'explanation': '"She doesn\'t like pizza." is the correct sentence because it uses the correct auxiliary verb "doesn\'t" for third person singular.',
            },
            {
                'id': '1059_8',
                'type': 'vrai-faux',
                'question': 'The word "quickly" is an adverb.',
                'correct': True,
                'explanation': '"Quickly" is an adverb because it describes how an action is performed.',
            },
            ]
        ),
        (1060, "Diagnostic Anglais 1Ã¨re - SÃ©rie 2", "Anglais", "1Ã¨re", [
            {
                'id': '1060_1',
                'type': 'qcm',
                'question': 'What is the comparative form of the adjective "good"?',
                'options': ['gooder', 'more good', 'better', 'best'],
                'correct_option': 'better',
                'explanation': 'The comparative form of "good" is "better".',
            },
            {
                'id': '1060_2',
                'type': 'vrai-faux',
                'question': 'The word "their" is a contraction of "they are".',
                'correct': False,
                'explanation': '"Their" is a possessive pronoun, not a contraction. The contraction of "they are" is "they\'re".',
            },
            {
                'id': '1060_3',
                'type': 'vrai-faux',
                'question': 'Translate the sentence "She has a cat" into French.',
                'correct_answer': 'Elle a un chat',
                'explanation': 'The correct translation of "She has a cat" in French is "Elle a un chat".',
            },
            {
                'id': '1060_4',
                'type': 'qcm',
                'question': 'Which of the following words is an antonym of "hot"?',
                'options': ['warm', 'cold', 'cool', 'heat'],
                'correct_option': 'cold',
                'explanation': '"Cold" is an antonym of "hot".',
            },
            {
                'id': '1060_5',
                'type': 'vrai-faux',
                'question': '"It\'s" is a possessive pronoun.',
                'correct': False,
                'explanation': '"It\'s" is a contraction of "it is" or "it has", not a possessive pronoun. The possessive pronoun for "it" is "its".',
            },
            {
                'id': '1060_6',
                'type': 'vrai-faux',
                'question': 'What is the past participle of the verb "eat"?',
                'correct_answer': 'eaten',
                'explanation': 'The past participle of "eat" is "eaten". It is used in perfect tenses and passive voice.',
            },
            {
                'id': '1060_7',
                'type': 'qcm',
                'question': 'Choose the correct sentence:',
                'options': ['They doesn\'t like music.', 'They don\'t like music.', 'They isn\'t like music.', 'They no like music.'],
                'correct_option': 'They don\'t like music.',
                'explanation': 'The correct sentence is "They don\'t like music." because "they" is plural and requires "do not" for negation.',
            },
            {
                'id': '1060_8',
                'type': 'vrai-faux',
                'question': 'The word "slowly" is an adjective.',
                'correct': False,
                'explanation': '"Slowly" is an adverb because it describes how an action is performed, not a noun or pronoun.',
            },
            ]
        ),
        (1061, "Diagnostic Anglais 1Ã¨re - SÃ©rie 3", "Anglais", "1Ã¨re", [
            {
                'id': '1061_1',
                'type': 'qcm',
                'question': 'What is the superlative form of the adjective "bad"?',
                'options': ['badder', 'more bad', 'worst', 'worser'],
                'correct_option': 'worst',
                'explanation': 'The superlative form of "bad" is "worst".',
            },
            {
                'id': '1061_2',
                'type': 'vrai-faux',
                'question': 'The word "your" is a possessive pronoun.',
                'correct': True,
                'explanation': '"Your" is a possessive pronoun used to indicate ownership.',
            },
            {
                'id': '1061_3',
                'type': 'vrai-faux',
                'question': 'Translate the sentence "They are playing soccer" into French.',
                'correct_answer': 'Ils jouent au football',
                'explanation': 'The correct translation of "They are playing soccer" in French is "Ils jouent au football".',

            },
            {
                'id': '1061_4',
                'type': 'qcm',
                'question': 'Which of the following words is a synonym for "big"?',
                'options': ['small', 'large', 'tiny', 'little'],
                'correct_option': 'large',
                'explanation': '"Large" is a synonym for "big".',
            },
            {
                'id': '1061_5',
                'type': 'vrai-faux',
                'question': '"It\'s" can be used as a possessive pronoun.',
                'correct': False,
                'explanation': '"It\'s" is a contraction of "it is" or "it has", not a possessive pronoun. The possessive pronoun for "it" is "its".',
            },
            {
                'id': '1061_6',
                'type': 'vrai-faux',
                'question': 'What is the past tense of the verb "see"?',
                'correct_answer': 'saw',
                'explanation': 'The past tense of "see" is "saw".',
            },

            {
                'id': '1061_7',
                'type': 'qcm',
                'question': 'Choose the correct sentence:',
                'options': ['He don\'t like sports.', 'He doesn\'t like sports.', 'He isn\'t like sports.', 'He no like sports.'],
                'correct_option': 'He doesn\'t like sports.',
                'explanation': 'The correct sentence is "He doesn\'t like sports." because "he" is third person singular and requires "does not" for negation.',
            },
            {
                'id':   '1061_8',
                'type': 'vrai-faux',
                'question': 'The word "happily" is an adverb.',
                'correct': True,
                'explanation': '"Happily" is an adverb because it describes how an action is performed.',
            },
            {
                'id': '1061_4',
                'type': 'qcm',
                'question': 'Which of the following words is a synonym for "big"?',
                'options': ['small', 'large', 'tiny', 'little'],
                'correct_option': 'large',
                'explanation': '"Large" is a synonym for "big".',
            },
            {
                'id': '1061_5',
                'type': 'vrai-faux',
                'question': '"It\'s" can be used as a possessive pronoun.',
                'correct': False,
                'explanation': '"It\'s" is a contraction of "it is" or "it has", not a possessive pronoun. The possessive pronoun for "it" is "its".',
            },
            {
                'id': '1061_6',
                'type': 'vrai-faux',
                'question': 'What is the past tense of the verb "see"?',
                'correct_answer': 'saw',
                'explanation': 'The past tense of "see" is "saw".',
            },
            {
                'id': '1061_7',
                'type': 'qcm',
                'question': 'Choose the correct sentence:',
                'options': ['He don\'t like sports.', 'He doesn\'t like sports.', 'He isn\'t like sports.', 'He no like sports.'],
                'correct_option': 'He doesn\'t like sports.',
                'explanation': 'The correct sentence is "He doesn\'t like sports." because "he" is third person singular and requires "does not" for negation.',
            },
            {
                'id':   '1061_8',
                'type': 'vrai-faux',
                'question': 'The word "happily" is an adverb.',
                'correct': True,
                'explanation': '"Happily" is an adverb because it describes how an action is performed.',
            },
        ]
        ),
        (1062, "Diagnostic Anglais 1Ã¨re - SÃ©rie 4", "Anglais", "1Ã¨re", [
            {
                'id': '1062_1',
                'type': 'qcm',
                'question': 'What is the past tense of the verb "run"?',
                'options': ['runned', 'ran', 'run', 'running'],
                'correct_option': 'ran',
                'explanation': 'The past tense of "run" is "ran".',
            },
            {
                'id': '1062_2',
                'type': 'vrai-faux',
                'question': 'The word "their" is a contraction of "they are".',
                'correct': False,
                'explanation': '"Their" is a possessive pronoun, not a contraction. The contraction of "they are" is "they\'re".',
            },
            {
                'id': '1062_3',
                'type': 'vrai-faux',
                'question': 'Translate the sentence "We have a dog" into French.',
                'correct_answer': 'Nous avons un chien',
                'explanation': 'The correct translation of "We have a dog" in French is "Nous avons un chien".',
            },
            {
                'id': '1062_4',
                'type': 'qcm',
                'question': 'Which of the following words is an antonym of "fast"?',
                'options': ['quick', 'slow', 'speedy', 'rapid'],
                'correct_option': 'slow',
                'explanation': '"Slow" is an antonym of "fast".',
            },
            {
                'id': '1062_5',
                'type': 'vrai-faux',
                'question': '"It\'s" is a possessive pronoun.',
                'correct': False,
                'explanation': '"It\'s" is a contraction of "it is" or "it has", not a possessive pronoun. The possessive pronoun for "it" is "its".',
            },
            {
                'id': '1062_6',
                'type': 'vrai-faux',
                'question': 'What is the past participle of the verb "write"?',
                'correct_answer': 'written',
                'explanation': 'The past participle of "write" is "written". It is used in perfect tenses and passive voice.',
            },
            {
                'id': '1062_7',
                'type': 'qcm',
                'question': 'Choose the correct sentence:',
                'options': ['She don\'t like movies.', 'She doesn\'t like movies.', 'She isn\'t like movies.', 'She no like movies.'],
                'correct_option': 'She doesn\'t like movies.',
                'explanation': '"She doesn\'t like movies." is the correct sentence because it uses the correct auxiliary verb "doesn\'t" for third person singular.',
            },
            {
                'id': '1062_8',
                'type': 'vrai-faux',
                'question': 'The word "loudly" is an adverb.',
                'correct': True,
                'explanation': '"Loudly" is an adverb because it describes how an action is performed.',
            },
        ]
        ),
        (1063, "Diagnostic Anglais 1Ã¨re - SÃ©rie 5", "Anglais", "1Ã¨re", [
            {
                'id': '1063_1',
                'type': 'qcm',
                'question': 'What is the comparative form of the adjective "bad"?',
                'options': ['badder', 'worse', 'more bad', 'worser'],
                'correct_option': 'worse',
                'explanation': 'The comparative form of "bad" is "worse".',
            },
            {
                'id': '1063_2',
                'type': 'vrai-faux',
                'question': 'The word "your" is a possessive pronoun.',
                'correct': True,
                'explanation': '"Your" is a possessive pronoun used to indicate ownership.',
            },
            {
                'id': '1063_3',
                'type': 'vrai-faux',
                'question': 'Translate the sentence "He is reading a book" into French.',
                'correct_answer': 'Il lit un livre',
                'explanation': 'The correct translation of "He is reading a book" in French is "Il lit un livre".',
            },
            {
                'id': '1063_4',
                'type': 'qcm',
                'question': 'Which of the following words is a synonym for "small"?',
                'options': ['tiny', 'big', 'large', 'huge'],
                'correct_option': 'tiny',
                'explanation': '"Tiny" is a synonym for "small".',
            },
            {
                'id': '1063_5',
                'type': 'vrai-faux',
                'question': '"It\'s" can be used as a possessive pronoun.',
                'correct': False,
                'explanation': '"It\'s" is a contraction of "it is" or "it has", not a possessive pronoun. The possessive pronoun for "it" is "its".',
            },
            {
                'id': '1063_6',
                'type': 'vrai-faux',
                'question': 'What is the past tense of the verb "drink"?',
                'correct_answer': 'drank',
                'explanation': 'The past tense of "drink" is "drank".',
            },
            {
                'id':   '1063_7',
                'type': 	'qcm',
                'question': 	'Choose the correct sentence:',
                'options': 	['They doesn\'t like music.',
                            'They don\'t like music.',
                            'They isn\'t like music.',
                            'They no like music.'],
                'correct_option': 	'They don\'t like music.',
                'explanation': 	'The correct sentence is "They don\'t like music." because "they" is plural and requires "do not" for negation.',
            },
            {
                'id': '1063_8',
                'type': 'vrai-faux',
                'question': 'The word "quietly" is an adverb.',
                'correct': True,
                'explanation': '"Quietly" is an adverb because it describes how an action is performed.',
            },
        ]
        ),
        (1064, "Diagnostic Anglais 1Ã¨re - SÃ©rie 6", "Anglais", "1Ã¨re", [
            {
                'id': '1064_1',
                'type': 'qcm',
                'question': 'What is the superlative form of the adjective "good"?',
                'options': ['goodest', 'best', 'more good', 'gooder'],
                'correct_option': 'best',
                'explanation': 'The superlative form of "good" is "best".',
            },
            {
                'id': '1064_2',
                'type': 'vrai-faux',
                'question': 'The word "their" is a possessive pronoun.',
                'correct': True,
                'explanation': '"Their" is a possessive pronoun used to indicate ownership.',
            },
            {
                'id': '1064_3',
                'type': 'vrai-faux',
                'question': 'Translate the sentence "She is cooking dinner" into French.',
                'correct_answer': 'Elle cuisine le dÃ®ner',
                'explanation': 'The correct translation of "She is cooking dinner" in French is "Elle cuisine le dÃ®ner".',
            },
            {
                'id': '1064_4',
                'type': 'qcm',
                'question': 'Which of the following words is an antonym of "old"?',
                'options': ['ancient', 'new', 'aged', 'elderly'],
                'correct_option': 'new',
                'explanation': '"New" is an antonym of "old".',
            },
            {
                'id': '1064_5',
                'type': 'vrai-faux',
                'question': '"It\'s" is a contraction of "it is".',
                'correct': True,
                'explanation': '"It\'s" can be a contraction of "it is" or "it has", but it cannot be a possessive pronoun. The possessive pronoun for "it" is "its".',
            },
            {
                'id':   '1064_6',
                'type': 	'vrai-faux',
                'question': 	'What is the past participle of the verb "break"?',
                'correct_answer': 	'broken',
                'explanation': 	'The past participle of "break" is "broken". It is used in perfect tenses and passive voice.',
            },
            {
                'id':   '1064_7',
                'type': 	'qcm',
                'question': 	'Choose the correct sentence:',
                'options': 	['He don\'t like sports.',
                            'He doesn\'t like sports.',
                            'He isn\'t like sports.',
                            'He no like sports.'],
                'correct_option': 	'He doesn\'t like sports.',
                'explanation': 	'The correct sentence is "He doesn\'t like sports." because "he" is third person singular and requires "does not" for negation.',
            },
            {
                'id': '1064_8',
                'type': 'vrai-faux',
                'question': 'The word "happily" is an adverb.',
                'correct': True,
                'explanation': '"Happily" is an adverb because it describes how an action is performed.',
            }
    ]
    ),
        (1065, "Diagnostic Anglais 1Ã¨re - SÃ©rie 7", "Anglais", "1Ã¨re", [
                {
                    'id': '1065_1',
                    'type': 'qcm',
                    'question': 'What is the past tense of the verb "write"?',
                    'options': ['wrote', 'written', 'writing', 'writes'],
                    'correct_option': 'wrote',
                    'explanation': 'The past tense of "write" is "wrote".',
                },
                {
                    'id': '1065_2',
                    'type': 'vrai-faux',
                    'question': 'The word "your" is a possessive pronoun.',
                    'correct': True,
                    'explanation': '"Your" is a possessive pronoun used to indicate ownership.',
                },
                {
                    'id': '1065_3',
                    'type': 'vrai-faux',
                    'question': 'Translate the sentence "They are watching TV" into French.',
                    'correct_answer': 'Ils regardent la tÃ©lÃ©vision',
                    'explanation': 'The correct translation of "They are watching TV" in French is "Ils regardent la tÃ©lÃ©vision".',
                },
                {
                    'id': '1065_4',
                    'type': 'qcm',
                    'question': 'Which of the following words is an antonym of "light"?',
                    'options': ['bright', 'dark', 'shiny', 'glossy'],
                    'correct_option': 'dark',
                    'explanation': '"Dark" is an antonym of "light".',
                },
                {
                    'id':   '1065_5',
                    'type': 	'vrai-faux',
                    'question': 	'"It\'s" can be used as a possessive pronoun.',
                    'correct': 	False,
                    'explanation': 	'"It\'s" is a contraction of "it is" or "it has", not a possessive pronoun. The possessive pronoun for "it" is "its".',
                },
                {
                    'id':   '1065_6',
                    'type': 	'vrai-faux',
                    'question': 	'What is the past participle of the verb "go"?',
                    'correct_answer': 	'gone',
                    'explanation': 	'The past participle of "go" is "gone". It is used in perfect tenses and passive voice.',
                },
                {
                    'id':   '1065_7',
                    'type': 	'qcm',
                    'question': 	'Choose the correct sentence:',
                    'options': 	['They doesn\'t like music.',
                                'They don\'t like music.',
                                'They isn\'t like music.',
                                'They no like music.'],
                    'correct_option': 	'They don\'t like music.',
                    'explanation': 	'The correct sentence is "They don\'t like music." because "they" is plural and requires "do not" for negation.',
                },
                {
                    'id': '1065_8',
                    'type': 'vrai-faux',
                    'question': 'The word "slowly" is an adverb.',
                    'correct': True,
                    'explanation': '"Slowly" is an adverb because it describes how an action is performed.',
                },
        ]
        ),
        (1066, "Diagnostic Anglais 1Ã¨re - SÃ©rie 8", "Anglais", "1Ã¨re", [
                {
                    'id': '1066_1',
                    'type': 'qcm',
                    'question': 'What is the superlative form of the adjective "bad"?',
                    'options': ['badder', 'worse', 'worst', 'worser'],
                    'correct_option': 'worst',
                    'explanation': 'The superlative form of "bad" is "worst".',
                },
                {
                    'id': '1066_2',
                    'type': 'vrai-faux',
                    'question': 'The word "their" is a possessive pronoun.',
                    'correct': True,
                    'explanation': '"Their" is a possessive pronoun used to indicate ownership.',
                },
                {
                    'id': '1066_3',
                    'type': 'vrai-faux',
                    'question': 'Translate the sentence "We are eating breakfast" into French.',
                    'correct_answer': 'Nous mangeons le petit dÃ©jeuner',
                    'explanation': 'The correct translation of "We are eating breakfast" in French is "Nous mangeons le petit dÃ©jeuner".',
                },
                {
                    'id': '1066_4',
                    'type': 'qcm',
                    'question': 'Which of the following words is a synonym for "happy"?',
                    'options': ['sad', 'angry', 'joyful', 'tired'],
                    'correct_option': 'joyful',
                    'explanation': '"Joyful" is a synonym for "happy".',
                },
                {
                    'id':   '1066_5',
                    'type': 	'vrai-faux',
                    'question': 	'"It\'s" can be used as a possessive pronoun.',
                    'correct': 	False,
                    'explanation': 	'"It\'s" is a contraction of "it is" or "it has", not a possessive pronoun. The possessive pronoun for "it" is "its".',
                },
                {
                    'id':   '1066_6',
                    'type': 	'vrai-faux',
                    'question': 	'What is the past tense of the verb "see"?',
                    'correct_answer': 	'saw',
                    'explanation': 	'The past tense of "see" is "saw".',
                },
                {
                    'id':   '1066_7',
                    'type': 	'qcm',
                    'question': 	'Choose the correct sentence:',
                    'options': 	['He don\'t like sports.',
                                'He doesn\'t like sports.',
                                'He isn\'t like sports.',
                                'He no like sports.'],
                    'correct_option': 	'He doesn\'t like sports.',
                    'explanation': 	'The correct sentence is "He doesn\'t like sports." because "he" is third person singular and requires "does not" for negation.',
                },
                {
                    'id': '1066_8',
                    'type': 'vrai-faux',
                    'question': 'The word "happily" is an adverb.',
                    'correct': True,
                    'explanation': '"Happily" is an adverb because it describes how an action is performed.',
                },
        ]
        )
]

def write_quiz_files():
    count = 0
    for qid, title, subject, level, questions in quizzes_data:
        quiz_payload = make_quiz(
            qid, title, subject, level, questions,
            source="Eduscol programmes officiels + BOEN",
            programme_ref='BO special ndeg1 du 22 janvier 2019 (LGT) + Eduscol Langues vivantes Premiere',
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
        print(f"  Ã¢Å“â€œ {qid}.json - {title}")

    print(f"\nÃ¢Å“â€¦ {count} quiz gÃƒÂ©nÃƒÂ©rÃƒÂ©s (+ {count} rÃƒÂ©ponses)")


if __name__ == "__main__":
    print("Generating quizzes from template IA...")
    write_quiz_files()

