#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Enrichissement ciblé Groq pour une plage d'IDs de quiz
Usage: python enrich_placeholders_range.py 899 914
"""

import json
import os
import re
import sys
import time
from pathlib import Path
from groq import Groq


def load_env_file(path='.env'):
    env_path = Path(path)
    if not env_path.exists():
        return
    with env_path.open('r', encoding='utf-8') as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith('#'):
                continue
            if line.startswith('export '):
                line = line[len('export '):].strip()
            if '=' not in line:
                continue
            key, value = line.split('=', 1)
            key = key.strip()
            value = value.strip().strip('"').strip("'")
            if key:
                os.environ[key] = value


REPO_ROOT = Path(__file__).resolve().parents[4]
load_env_file(REPO_ROOT / '.env')

GROQ_API_KEY = os.getenv("GROQ_API_KEY")
if not GROQ_API_KEY:
    print("ERROR: GROQ_API_KEY manquante dans .env")
    sys.exit(1)

QUIZ_DIR = REPO_ROOT / "src/data/quiz"
LOG_FILE = REPO_ROOT / "dev/reports/enrichment_groq_range_899_914.json"
LOG_FILE.parent.mkdir(parents=True, exist_ok=True)

CONTEXT_MAP = {
    "6eme": {
        "Anglais": "present simple, be/have, questions courantes, vocabulaire utile, verbes d'etat, structures de base",
        "Mathématiques": "nombres entiers, fractions, calcul mental, géométrie basique",
        "Français": "grammaire, conjugaison au présent, vocabulaire scolaire, compréhension de texte",
    },
}


def load_quiz(quiz_id):
    quiz_path = QUIZ_DIR / f"{quiz_id}.json"
    if not quiz_path.exists():
        return None
    with open(quiz_path, 'r', encoding='utf-8') as f:
        return json.load(f)


def get_context(level, subject):
    return CONTEXT_MAP.get(level, {}).get(subject, "notions de base du niveau")


def build_prompt(quiz, level, subject):
    question_count = len(quiz.get("quiz", {}).get("questions", []))
    context = get_context(level, subject)

    return f"""Tu es un expert pédagogique chargé d'enrichir un quiz 6ème Anglais.

Contexte:
- Niveau: {level}
- Matière: {subject}
- Notions à couvrir: {context}
- Nombre de questions: {question_count}

Ta mission:
1. Remplace le titre générique du quiz par un titre spécifique et utile.
2. Remplace toutes les questions placeholders par des questions réelles et adaptées au niveau 6ème Anglais.
3. Remplace toutes les réponses de type "Concept A/B/C/D" par des propositions précises en anglais ou en français adaptées à la question.
4. Conserve le même type de question (qcm, vrai-faux, texte).
5. Si le quiz contient un champ "placeholder", remplace-le par un vrai exemple ou un vrai texte utile.
6. Fournis également 3 notions pédagogiques réelles au format JSON.

Format de sortie strict:
{{
  "titre": "Anglais - ...",
  "notions": [
    {{"notion": "...", "description": "..."}},
    {{"notion": "...", "description": "..."}},
    {{"notion": "...", "description": "..."}}
  ],
  "enriched_questions": [
    {{"question": "...", "choices": [...], "question_type": "qcm"}},
    {{"question": "...", "question_type": "vrai-faux"}},
    {{"question": "...", "question_type": "texte"}},
    ...
  ]
}}

Règles importantes:
- N'écris pas de "Concept A/B/C".
- N'écris pas de "Notion 1".
- N'écris pas de questions trop vagues ou hors-sujet.
- Chaque proposition QCM doit être plausible.
- Chaque notion doit être spécifique et utile.
"""


def extract_json(text):
    if not isinstance(text, str):
        return None

    # Supprime les fences Markdown si le modèle renvoie ```json ... ```
    code_block = re.search(r"```(?:json)?\s*(.*?)\s*```", text, re.DOTALL | re.IGNORECASE)
    if code_block:
        text = code_block.group(1)

    # Recherche du premier bloc JSON structuré
    match = re.search(r"\{.*\}", text, re.DOTALL)
    if not match:
        return None
    try:
        return json.loads(match.group())
    except json.JSONDecodeError:
        return None


PLACEHOLDER_PATTERNS = [
    r"concept [a-d]",
    r"^concept [a-d]$",
    r"notion \d+",
    r"placeholder",
    r"exemple\.\.\.",
    r"quel concept",
]


def contains_placeholder(text):
    if not isinstance(text, str):
        return False
    content = text.strip().lower()
    for pattern in PLACEHOLDER_PATTERNS:
        if re.search(pattern, content):
            return True
    return False


def validate_enriched_question(question):
    if not isinstance(question, dict):
        return False
    q_text = question.get("question", "")
    if contains_placeholder(q_text) or len(q_text.strip()) < 10:
        return False
    q_type = question.get("question_type")
    if q_type not in {"qcm", "vrai-faux", "texte"}:
        return False
    if q_type == "qcm":
        choices = question.get("choices")
        if not isinstance(choices, list) or len(choices) < 2:
            return False
        for choice in choices:
            if contains_placeholder(choice) or len(choice.strip()) < 2:
                return False
    return True


def validate_enriched_payload(enriched):
    if not isinstance(enriched, dict):
        return False
    titre = enriched.get("titre")
    if not isinstance(titre, str) or contains_placeholder(titre) or len(titre.strip()) < 10:
        return False
    notions = enriched.get("notions")
    if not isinstance(notions, list) or len(notions) < 1:
        return False
    for notion in notions:
        if not isinstance(notion, dict):
            return False
        if contains_placeholder(notion.get("notion", "")) or len(notion.get("notion", "").strip()) < 10:
            return False
        if contains_placeholder(notion.get("description", "")) or len(notion.get("description", "").strip()) < 20:
            return False
    enriched_questions = enriched.get("enriched_questions")
    if not isinstance(enriched_questions, list) or len(enriched_questions) < 1:
        return False
    for question in enriched_questions:
        if not validate_enriched_question(question):
            return False
    return True


def enrich_quiz(client, quiz_id, quiz):
    level = quiz.get("contents", {}).get("level", "6eme")
    subject = quiz.get("contents", {}).get("subject", "Anglais")
    prompt = build_prompt(quiz, level, subject)

    response = client.chat.completions.create(
        model="llama-3.3-70b-versatile",
        messages=[{"role": "user", "content": prompt}],
        temperature=0.7,
        max_tokens=2500,
    )
    text = response.choices[0].message.content.strip()
    enriched = extract_json(text)
    if not enriched:
        return {"quiz_id": quiz_id, "status": "failed", "error": "No JSON extracted", "raw": text}

    if not validate_enriched_payload(enriched):
        return {"quiz_id": quiz_id, "status": "failed", "error": "Invalid enriched payload or placeholders detected", "raw": text}

    enriched['quiz_id'] = quiz_id
    enriched['raw_response'] = text[:300]
    enriched['status'] = 'success'
    return enriched


def apply_and_save(quiz_id, quiz, enriched):
    if 'titre' in enriched and isinstance(enriched['titre'], str):
        quiz['contents']['title'] = enriched['titre']
        quiz['quiz']['title'] = enriched['titre']

    if 'notions' in enriched and isinstance(enriched['notions'], list):
        quiz['exercisenotion'] = enriched['notions']

    if 'enriched_questions' in enriched and isinstance(enriched['enriched_questions'], list):
        original_questions = quiz['quiz'].get('questions', [])
        enriched_questions = enriched['enriched_questions']
        for idx, eq in enumerate(enriched_questions):
            if idx < len(original_questions) and isinstance(eq, dict):
                if 'question' in eq and isinstance(eq['question'], str):
                    original_questions[idx]['question'] = eq['question']
                if 'question_type' in eq and isinstance(eq['question_type'], str):
                    original_questions[idx]['type'] = eq['question_type']
                if eq.get('question_type') == 'qcm' and 'choices' in eq and isinstance(eq['choices'], list):
                    original_questions[idx]['choices'] = eq['choices']
                elif eq.get('question_type') != 'qcm':
                    original_questions[idx].pop('choices', None)

                if eq.get('question_type') != 'texte':
                    original_questions[idx].pop('placeholder', None)
                elif 'placeholder' in eq and isinstance(eq['placeholder'], str):
                    original_questions[idx]['placeholder'] = eq['placeholder']
        quiz['quiz']['questions'] = original_questions

    path = QUIZ_DIR / f"{quiz_id}.json"
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(quiz, f, ensure_ascii=False, indent=2)
    return True


def main():
    if len(sys.argv) != 3:
        print("Usage: python enrich_placeholders_range.py <start_id> <end_id>")
        sys.exit(1)

    start_id = int(sys.argv[1])
    end_id = int(sys.argv[2])
    client = Groq(api_key=GROQ_API_KEY)

    results = []
    for quiz_id in range(start_id, end_id + 1):
        print(f"Enrichissement quiz {quiz_id}...", end=' ', flush=True)
        quiz = load_quiz(quiz_id)
        if not quiz:
            print("NOT FOUND")
            results.append({"quiz_id": quiz_id, "status": "missing"})
            continue

        try:
            enriched = enrich_quiz(client, quiz_id, quiz)
        except Exception as e:
            print(f"FAILED ({e})")
            results.append({"quiz_id": quiz_id, "status": "failed", "error": str(e)})
            continue

        if enriched.get('status') == 'success':
            saved = apply_and_save(quiz_id, quiz, enriched)
            if saved:
                print("SAVED")
                results.append({"quiz_id": quiz_id, "status": "success"})
            else:
                print("SAVE FAILED")
                results.append({"quiz_id": quiz_id, "status": "failed", "error": "save_failed"})
        else:
            print("FAILED")
            failed_result = {"quiz_id": quiz_id, "status": "failed", "error": enriched.get('error')}
            if 'raw' in enriched:
                failed_result['raw'] = enriched.get('raw')
            results.append(failed_result)

        time.sleep(1)

    with open(LOG_FILE, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

    success = sum(1 for r in results if r['status'] == 'success')
    failed = sum(1 for r in results if r['status'] != 'success')
    print(f"\nRésultats: {success} success, {failed} failed")
    print(f"Log saved to {LOG_FILE}")


if __name__ == '__main__':
    main()
