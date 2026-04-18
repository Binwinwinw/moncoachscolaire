#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script d'analyse des placeholders dans les quiz
Scanne les 1820 fichiers et détecte les contenus génériques/incomplets
"""

import json
import os
import re
from pathlib import Path
from collections import defaultdict

# Configuration
REPO_ROOT = Path(__file__).resolve().parents[4]
QUIZ_DIR = REPO_ROOT / "src/data/quiz"
REPORT_FILE = REPO_ROOT / "dev/reports/placeholder_analysis.json"
REPORT_FILE.parent.mkdir(parents=True, exist_ok=True)

# Patterns de détection
PLACEHOLDER_PATTERNS = {
    "title": [
        r"quel concept cl[eé]",
        r"notions? fondamentales",
        r"exemple concret",
        r"quiz diagnostic",
        r"s[ée]rie \d+",
    ],
    "concept": [
        r"concept [a-d]$",
        r"notion \d+",
        r"placeholder",
    ],
    "choice": [
        r"^concept [a-d]$",
        r"^option [a-d]$",
        r"^réponse [a-d]$",
        r"^a\b",
        r"^b\b",
        r"^c\b",
        r"^d\b",
    ],
    "empty": [
        r"^$",
        r"^\s+$",
    ],
}


def is_placeholder(text, pattern_type="title"):
    """Détecte si un texte est un placeholder"""
    if not isinstance(text, str) or not text.strip():
        return True

    text_lower = text.lower().strip()

    # Trop court (probablement placeholder)
    if len(text_lower) < 5:
        return True

    # Trop générique (probablement placeholder)
    if pattern_type == "choice" and len(text_lower) <= 2:
        return True

    # Patterns génériques
    patterns = PLACEHOLDER_PATTERNS.get(pattern_type, [])
    for pattern in patterns:
        if re.search(pattern, text_lower):
            return True

    return False


def analyze_quiz(quiz_path):
    """Analyse un fichier quiz et retourne les problèmes trouvés"""
    try:
        with open(quiz_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        return {"error": str(e)}

    issues = []
    quiz_id = quiz_path.stem

    # Vérifier contents
    contents = data.get("contents", {})
    if not contents.get("title") or is_placeholder(contents.get("title")):
        issues.append("title_placeholder")

    # Vérifier questions
    questions = data.get("quiz", {}).get("questions", [])
    if not questions:
        issues.append("no_questions")
    else:
        for q_idx, question in enumerate(questions):
            q_text = question.get("question", "")

            if not q_text or is_placeholder(q_text, "title"):
                issues.append(f"question_{q_idx}_placeholder")

            # Vérifier choix (QCM)
            if question.get("type") == "qcm":
                choices = question.get("choices", [])
                if not choices or len(choices) < 4:
                    issues.append(f"question_{q_idx}_bad_choices")
                else:
                    for c_idx, choice in enumerate(choices):
                        if is_placeholder(choice, "choice"):
                            issues.append(f"question_{q_idx}_choice_{c_idx}_placeholder")

    # Vérifier notions
    notions = data.get("exercisenotion", [])
    if not notions:
        issues.append("no_notions")
    else:
        for n_idx, notion in enumerate(notions):
            notion_text = notion.get("notion", "")
            if not notion_text or is_placeholder(notion_text, "concept"):
                issues.append(f"notion_{n_idx}_placeholder")

    return {
        "quiz_id": quiz_id,
        "has_issues": len(issues) > 0,
        "issue_count": len(issues),
        "issues": issues,
    }


def main():
    print("🔍 Analyse des placeholders dans les quiz...")
    print(f"📁 Répertoire: {QUIZ_DIR}")

    if not QUIZ_DIR.exists():
        print(f"❌ Répertoire {QUIZ_DIR} introuvable")
        return

    quiz_files = sorted(QUIZ_DIR.glob("*.json"))
    print(f"📊 Fichiers trouvés: {len(quiz_files)}\n")

    # Analyse
    results = []
    problematic_quizzes = []
    issue_summary = defaultdict(int)

    for idx, quiz_file in enumerate(quiz_files, 1):
        result = analyze_quiz(quiz_file)
        results.append(result)

        if result.get("has_issues"):
            problematic_quizzes.append(result)
            for issue in result.get("issues", []):
                issue_summary[issue] += 1

        # Afficher progression
        if idx % 200 == 0:
            print(f"  [{idx}/{len(quiz_files)}] Analyzed {idx} quizzes...")

    # Rapport
    print(f"\n✅ Analyse complétée\n")
    print(f"📈 RÉSULTATS:")
    print(f"  Total quiz: {len(quiz_files)}")
    print(f"  Quiz problématiques: {len(problematic_quizzes)}")
    print(f"  Pourcentage: {len(problematic_quizzes)/len(quiz_files)*100:.1f}%")

    print(f"\n🚨 TOP ISSUES:")
    for issue, count in sorted(issue_summary.items(), key=lambda x: -x[1])[:10]:
        print(f"  {issue}: {count} fois")

    # Sauvegarder rapport
    report = {
        "timestamp": str((REPO_ROOT / "dev/reports").absolute()),
        "total_quizzes": len(quiz_files),
        "problematic_count": len(problematic_quizzes),
        "percentage": f"{len(problematic_quizzes)/len(quiz_files)*100:.1f}%",
        "issue_summary": dict(issue_summary),
        "problematic_quizzes": problematic_quizzes[:50],  # Top 50
    }

    with open(REPORT_FILE, 'w', encoding='utf-8') as f:
        json.dump(report, f, ensure_ascii=False, indent=2)

    print(f"\n📄 Rapport sauvegardé: {REPORT_FILE}")
    print(f"\n💡 PROCHAINES ÉTAPES:")
    print(f"  1. Voir le rapport complet: cat {REPORT_FILE}")
    print(f"  2. Enrichir avec Groq: python enrich_with_groq_v2.py --from-placeholders {REPORT_FILE}")
    print(f"  3. Valider: php dev/tools/identify_valid_quizzes.php")


if __name__ == "__main__":
    main()
