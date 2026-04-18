#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Enrichissement intelligent des placeholders avec Groq
Étape 1: Valider sur 10 quiz avant scaling à 737
"""

import json
import os
import sys
import time
import re
from pathlib import Path
from groq import Groq

# Config
REPO_ROOT = Path(__file__).resolve().parents[4]
GROQ_API_KEY = os.getenv("GROQ_API_KEY")
if not GROQ_API_KEY:
    print("❌ GROQ_API_KEY manquante dans .env")
    sys.exit(1)

QUIZ_DIR = REPO_ROOT / "src/data/quiz"
REPORT_FILE = REPO_ROOT / "dev/reports/placeholder_detection_v2.json"
ENRICHMENT_LOG = REPO_ROOT / "dev/reports/enrichment_groq_phase1.json"
ENRICHMENT_LOG.parent.mkdir(parents=True, exist_ok=True)

# Context: Niveau + Matière = Notions
CONTEXT_MAP = {
    "6eme": {
        "Mathématiques": "nombres, fractions, géométrie basique, calcul mental",
        "Français": "grammaire, conjugaison présent/passé, vocabulaire scolaire",
        "Anglais": "present simple, to be/have, vocabulaire courant, nombres",
        "Physique-Chimie": "états matière, température, changements d'état, corps purs",
        "SVT": "cellule, ADN, photosynthèse, écosystèmes simples, reproduction",
        "Histoire-Géographie": "continents, repères chronologiques, régions françaises",
        "Espagnol": "verbes réguliers, présent, vocabulaire basique",
        "EMC": "droits de l'enfant, respect des règles, égalité",
    },
    "bac": {
        "Mathématiques": "dérivées, intégrales, probabilités, suites, limites",
        "Français": "littérature classique, analyse de texte, argumentation, rhétorique",
        "Anglais": "conditional structures, reported speech, modaux, past tenses",
        "Physique-Chimie": "thermodynamique, électricité, ondes, optique, mécanique quantique",
        "SVT": "évolution, génétique, écologie, homeostasie, régulation",
        "Histoire-Géographie": "géopolitique, puissances mondiales, développement, frontières",
        "Espagnol": "subjonctif, passés complexes, vocabulaire spécialisé",
        "Philosophie": "métaphysique, épistémologie, éthique, esthétique",
    },
}

# Autres niveaux (simplified)
for niveau in ["5eme", "4eme", "3eme", "2nde", "1ere", "terminale"]:
    if niveau not in CONTEXT_MAP:
        CONTEXT_MAP[niveau] = CONTEXT_MAP.get("6eme", {})


def load_problematic_quizzes():
    """Charge la liste des 737 quiz problématiques"""
    if not REPORT_FILE.exists():
        print(f"❌ Rapport manquant: {REPORT_FILE}")
        sys.exit(1)

    with open(REPORT_FILE, 'r', encoding='utf-8') as f:
        report = json.load(f)

    return report.get("problematic_quizzes", [])


def load_quiz(quiz_id):
    """Charge un quiz par ID"""
    quiz_path = QUIZ_DIR / f"{quiz_id}.json"
    if not quiz_path.exists():
        return None

    with open(quiz_path, 'r', encoding='utf-8') as f:
        return json.load(f)


def get_context(level, subject):
    """Retourne contexte pédagogique pour Groq"""
    context = CONTEXT_MAP.get(level, {}).get(subject, "Concepts du niveau")
    return context


def build_enrich_prompt(quiz, level, subject):
    """Construit prompt Groq pour enrichissement"""
    context = get_context(level, subject)
    questions = quiz.get("quiz", {}).get("questions", [])
    q_count = len(questions)

    prompt = f"""Tu es un expert pédagogique. Enrichis ce quiz avec du contenu RÉEL et SPÉCIFIQUE.

CONTEXTE:
- Niveau: {level}
- Matière: {subject}
- Notions: {context}
- {q_count} questions à enrichir

TÂCHE: Retourne UNIQUEMENT un JSON avec:

1. **titre**: Remplace "Quiz Diagnostic..." par un titre SPÉCIFIQUE
   Format: "[Matière] - [Notion clé]"
   Exemple: "Anglais - Present Simple et Verbes de Base"

2. **notions**: 3 notions réelles (NOT "Notion 1", "Notion 2")
   Chaque notion a:
   - "notion": nom spécifique (≥15 caractères)
   - "description": explication courte (≥30 caractères)

3. **enriched_questions**: Pour chaque question, remplace les placeholders:
   - "question": Remplace "Quel concept clé..." par une VRAIE question
   - "choices": Remplace "Concept A/B/C/D" par VRAIES réponses pertinentes
     (Au moins 1 est correcte, les autres sont des distracteurs crédibles)
   - "question_type": Garde le type original (qcm, vrai-faux, texte)

EXEMPLES:
- MAUVAIS: "Concept A", "Concept B" ❌
- BON: "The verb 'to have' in third person", "Present continuous use" ✅

- MAUVAIS: "Quelle est la capitale ?" (trop générique)
- BON: "Quelle est la capital de la France ?" ✅

Règles STRICTES:
✓ Chaque texte ≥ 15 caractères
✓ Pas de "Concept X", "Notion N", "Placeholder"
✓ Contenu RÉEL et SPÉCIFIQUE au niveau {level}
✓ Questions variées et pertinentes

Retourne UNIQUEMENT JSON valide avec clés: titre, notions, enriched_questions
"""

    return prompt


def is_clean_content(text):
    """Vérifie qu'un texte n'a pas de placeholders"""
    if not isinstance(text, str) or len(text) < 5:
        return False

    placeholders = [
        "concept [a-d]",
        "notion \\d+",
        "placeholder",
        "exemple\\.\\.\\.",
        "quel concept",
    ]

    text_lower = text.lower()
    for pattern in placeholders:
        if re.search(pattern, text_lower):
            return False

    return True


def enrich_quiz_groq(client, quiz_id, quiz, level, subject):
    """Enrichit un quiz avec Groq"""
    try:
        prompt = build_enrich_prompt(quiz, level, subject)

        response = client.chat.completions.create(
            model="llama-3.3-70b-versatile",
            messages=[{"role": "user", "content": prompt}],
            temperature=0.7,
            max_tokens=3000,
        )

        response_text = response.choices[0].message.content.strip()

        # Extraire JSON
        json_match = re.search(r'\{.*\}', response_text, re.DOTALL)
        if not json_match:
            return {"status": "failed", "error": "No JSON in response"}

        enriched = json.loads(json_match.group())

        # Valider qualité
        if not is_clean_content(enriched.get("titre", "")):
            return {"status": "failed", "error": "Title still has placeholders"}

        # Retourner succès
        return {
            "status": "success",
            "quiz_id": quiz_id,
            "titre": enriched.get("titre"),
            "notions": enriched.get("notions", []),
            "questions": enriched.get("enriched_questions", []),
        }

    except Exception as e:
        return {"status": "failed", "error": str(e)}


def apply_enrichment(quiz, enrichment):
    """Applique l'enrichissement au quiz original"""
    try:
        # Mettre à jour titre
        quiz["contents"]["title"] = enrichment["titre"]
        quiz["quiz"]["title"] = enrichment["titre"]

        # Mettre à jour notions
        quiz["exercisenotion"] = enrichment.get("notions", [])

        # Mettre à jour questions
        original_questions = quiz.get("quiz", {}).get("questions", [])
        enriched_questions = enrichment.get("questions", [])

        for idx, enr_q in enumerate(enriched_questions):
            if idx < len(original_questions):
                original_questions[idx]["question"] = enr_q.get("question")
                if "choices" in enr_q:
                    original_questions[idx]["choices"] = enr_q["choices"]

        return quiz
    except Exception as e:
        return None


def save_quiz(quiz_id, quiz):
    """Sauvegarde le quiz enrichi"""
    quiz_path = QUIZ_DIR / f"{quiz_id}.json"
    try:
        with open(quiz_path, 'w', encoding='utf-8') as f:
            json.dump(quiz, f, ensure_ascii=False, indent=2)
        return True
    except Exception as e:
        print(f"  ❌ Save error: {e}")
        return False


def main():
    batch_size = int(sys.argv[1]) if len(sys.argv) > 1 else 10

    print("🚀 PHASE 1: Enrichissement Groq (10 quiz test)")
    print(f"📊 Batch size: {batch_size}\n")

    client = Groq(api_key=GROQ_API_KEY)
    problematic = load_problematic_quizzes()

    print(f"📋 Quiz à enrichir: {len(problematic)}")
    print(f"🎯 Test sur: {min(batch_size, len(problematic))}\n")

    # Trier par issue_count décroissant (plus problématiques en premier)
    sorted_quizzes = sorted(
        problematic,
        key=lambda x: -x.get("issue_count", 0)
    )[:batch_size]

    enrichment_log = []
    success_count = 0

    for idx, quiz_info in enumerate(sorted_quizzes, 1):
        quiz_id = quiz_info["quiz_id"]
        print(f"[{idx}/{len(sorted_quizzes)}] Quiz {quiz_id}...", end=" ", flush=True)

        # Charger quiz
        quiz = load_quiz(quiz_id)
        if not quiz:
            print("❌ NOT FOUND")
            continue

        # Déterminer level/subject
        level = quiz.get("contents", {}).get("level", "6eme")
        subject = quiz.get("contents", {}).get("subject", "Mathématiques")

        # Enrichir avec Groq
        enrich_result = enrich_quiz_groq(client, quiz_id, quiz, level, subject)
        enrichment_log.append(enrich_result)

        if enrich_result["status"] == "success":
            # Appliquer enrichissement
            enriched_quiz = apply_enrichment(quiz, enrich_result)
            if enriched_quiz and save_quiz(quiz_id, enriched_quiz):
                print("✅ SAVED")
                success_count += 1
            else:
                print("❌ APPLY FAILED")
        else:
            error = enrich_result.get("error", "Unknown")
            print(f"❌ {error[:30]}")

        # Rate limit
        time.sleep(1)

    # Résultats
    print(f"\n📈 RÉSULTATS:")
    print(f"  Total: {len(sorted_quizzes)}")
    print(f"  ✅ Succès: {success_count}")
    print(f"  ❌ Échoué: {len(sorted_quizzes) - success_count}")
    print(f"  Taux: {success_count/len(sorted_quizzes)*100:.0f}%")

    # Sauvegarder log
    with open(ENRICHMENT_LOG, 'w', encoding='utf-8') as f:
        json.dump(enrichment_log, f, ensure_ascii=False, indent=2)

    print(f"\n📄 Log: {ENRICHMENT_LOG}")

    # Prochaines étapes
    if success_count == len(sorted_quizzes):
        print(f"\n✅ PHASE 1 RÉUSSIE!")
        print(f"   → Vérifier les résultats manuellement")
        print(f"   → Si OK, relancer: python enrich_placeholders_phase1.py 50")
        print(f"   → Puis: python enrich_placeholders_phase1.py 737")
    else:
        print(f"\n⚠️  Certains échoués - vérifier le log avant de scaler")


if __name__ == "__main__":
    main()
