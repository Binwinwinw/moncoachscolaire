#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Enrichissement intelligent des placeholders Groq
Stratégie: Générer du contenu pédagogique authentique par niveau/matière
"""

import json
import os
import sys
from pathlib import Path
import time
import re

# Groq
try:
    from groq import Groq
except ImportError:
    print("❌ Groq lib manquante: pip install groq")
    sys.exit(1)

# Config
REPO_ROOT = Path(__file__).resolve().parents[4]
GROQ_API_KEY = os.getenv("GROQ_API_KEY")
if not GROQ_API_KEY:
    print("❌ GROQ_API_KEY manquante dans .env")
    sys.exit(1)

QUIZ_DIR = REPO_ROOT / "src/data/quiz"
REPORT_FILE = REPO_ROOT / "dev/reports/placeholder_analysis.json"
ENRICHMENT_LOG = REPO_ROOT / "dev/reports/enrichment_groq_v3.json"
ENRICHMENT_LOG.parent.mkdir(parents=True, exist_ok=True)

# Niveau/Matière mapping pour contexte
CONTEXT_MAP = {
    "6eme": {
        "Mathématiques": "nombres entiers, fractions basiques, géométrie simple",
        "Français": "grammaire de base, conjugaison présent/passé, vocabulaire",
        "Anglais": "verbes être/avoir, present simple, vocabulaire courant",
        "Physique-Chimie": "états de la matière, mélanges, changements d'état",
        "SVT": "cellule, photosynthèse, écosystèmes simples",
        "Histoire-Géographie": "repères chronologiques, continents, aires régionales",
        "Espagnol": "verbes courants, saluations, vocabulaire basique",
        "EMC": "droits de l'enfant, respect des règles, civisme",
    },
    # À continuer pour 5ème, 4ème, 3ème, 2nde, 1ère, Terminale
    "5eme": {
        "Mathématiques": "fractions, opérations, proportionnalité, périmètres",
        "Français": "subordonnées, passé composé, textes plus complexes",
    },
    # ... (simplifié pour démo)
}


def load_report():
    """Charge le rapport de placeholders"""
    if not REPORT_FILE.exists():
        print(f"❌ Rapport manquant: {REPORT_FILE}")
        sys.exit(1)

    with open(REPORT_FILE, 'r', encoding='utf-8') as f:
        return json.load(f)


def load_quiz(quiz_id):
    """Charge un quiz par ID"""
    quiz_path = QUIZ_DIR / f"{quiz_id}.json"
    if not quiz_path.exists():
        return None

    with open(quiz_path, 'r', encoding='utf-8') as f:
        return json.load(f)


def get_context(level, subject):
    """Retourne le contexte pédagogique pour guider Groq"""
    level_context = CONTEXT_MAP.get(level, {})
    subject_context = level_context.get(subject, "Concepts fondamentaux du niveau")
    return subject_context


def build_enrich_prompt(quiz, level, subject):
    """Construit le prompt Groq pour enrichissement"""
    context = get_context(level, subject)

    # Nombre de questions
    questions = quiz.get("quiz", {}).get("questions", [])
    q_count = len(questions)

    prompt = f"""Tu es un créateur de contenu pédagogique expert.

CONTEXTE:
- Niveau: {level}
- Matière: {subject}
- Notions couvertes: {context}
- Nombre de questions: {q_count}

TÂCHE: Enrichir ce quiz avec du contenu AUTHENTIQUE et PÉDAGOGIQUE.

Le quiz actuel a des placeholders génériques. Génère:

1. **Titre court et spécifique** (NOT "Quiz Diagnostic 6eme Anglais - Serie 07")
   Format: "[Matière] - [Notion clé spécifique]"
   Exemple: "Anglais - Present Simple et Verbes d'État"

2. **Notions pédagogiques précises** (3-5 au total):
   - Notion spécifique du {level} en {subject}
   - Description courte (1 phrase, ≥30 caractères)
   - Exemples concrets quand possible

   Format JSON:
   {{
     "notions": [
       {{"notion": "...", "description": "..."}},
       ...
     ]
   }}

3. **Questions variées et pertinentes** ({q_count} questions):
   - Mélange QCM / vrai-faux / texte courts
   - Adaptés aux notions ci-dessus
   - Progressivité: facile → moyen → difficile

   Format JSON pour chaque question:
   {{
     "questions": [
       {{
         "id": 1,
         "type": "qcm",
         "question": "Question spécifique ≥ 15 caractères",
         "choices": ["Réponse A spécifique", "Réponse B spécifique", ...],
         "correct_index": 0
       }},
       ...
     ]
   }}

Prérequis STRICTES:
- Chaque notion: ≥ 30 caractères
- Chaque question: ≥ 15 caractères
- Chaque choix: ≥ 5 caractères ET unique
- Pas de "Concept A/B/C", "Notion N", "Exemple...", etc.
- Pas de répétition de questions
- Contenu RÉEL, pas générique

Retourne UNIQUEMENT un objet JSON valide avec "notions" et "questions".
"""

    return prompt


def enrich_single_quiz(client, quiz_id, quiz, level, subject, attempts=3):
    """Enrichit un seul quiz avec Groq"""
    for attempt in range(1, attempts + 1):
        try:
            prompt = build_enrich_prompt(quiz, level, subject)

            response = client.chat.completions.create(
                model="llama-3.3-70b-versatile",
                messages=[{"role": "user", "content": prompt}],
                temperature=0.7,
                max_tokens=2000,
            )

            response_text = response.choices[0].message.content.strip()

            # Parser JSON
            json_match = re.search(r'\{.*\}', response_text, re.DOTALL)
            if not json_match:
                raise ValueError("Pas de JSON trouvé")

            enriched_data = json.loads(json_match.group())

            return {
                "status": "success",
                "quiz_id": quiz_id,
                "title": enriched_data.get("title", ""),
                "notions": enriched_data.get("notions", []),
                "questions": enriched_data.get("questions", []),
            }

        except Exception as e:
            if attempt < attempts:
                time.sleep(1)  # Backoff
                continue
            return {
                "status": "failed",
                "quiz_id": quiz_id,
                "error": str(e),
            }

    return {"status": "failed", "quiz_id": quiz_id, "error": "Max retries"}


def main():
    if len(sys.argv) < 2:
        print("Usage: python enrich_placeholders_groq.py <batch_size> [start_id]")
        print("Exemple: python enrich_placeholders_groq.py 10 1")
        sys.exit(1)

    batch_size = int(sys.argv[1])
    start_id = int(sys.argv[2]) if len(sys.argv) > 2 else 1

    print("🚀 Enrichissement Groq des placeholders")
    print(f"📊 Batch size: {batch_size}")
    print(f"🎯 Start ID: {start_id}\n")

    client = Groq(api_key=GROQ_API_KEY)

    # Charger rapport
    report = load_report()
    problematic = report.get("problematic_quizzes", [])

    # Prioriser: quizzes avec le plus de placeholders
    sorted_quizzes = sorted(
        problematic,
        key=lambda x: -x.get("issue_count", 0)
    )[:batch_size]

    enrichment_log = []
    success_count = 0

    for idx, quiz_info in enumerate(sorted_quizzes, 1):
        quiz_id = quiz_info["quiz_id"]
        print(f"[{idx}/{len(sorted_quizzes)}] Enrichissement {quiz_id}...", end=" ")

        # Charger quiz
        quiz = load_quiz(quiz_id)
        if not quiz:
            print("❌ NOT FOUND")
            continue

        # Déterminer level/subject
        level = quiz.get("contents", {}).get("level", "6eme")
        subject = quiz.get("contents", {}).get("subject", "Mathématiques")

        # Enrichir
        result = enrich_single_quiz(client, quiz_id, quiz, level, subject)
        enrichment_log.append(result)

        if result["status"] == "success":
            print("✅")
            success_count += 1
        else:
            print("❌")

        # Rate limit
        time.sleep(0.5)

    # Rapport
    print(f"\n📈 RÉSULTATS:")
    print(f"  Total: {len(sorted_quizzes)}")
    print(f"  Succès: {success_count}")
    print(f"  Échoué: {len(sorted_quizzes) - success_count}")

    # Sauvegarder
    with open(ENRICHMENT_LOG, 'w', encoding='utf-8') as f:
        json.dump(enrichment_log, f, ensure_ascii=False, indent=2)

    print(f"\n📄 Log sauvegardé: {ENRICHMENT_LOG}")


if __name__ == "__main__":
    main()
