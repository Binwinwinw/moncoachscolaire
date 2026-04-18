#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Détecteur PRÉCIS de placeholders dans les quiz
Cible UNIQUEMENT les patterns vraiment génériques:
- "Quel concept clé as-tu étudié ?"
- "Concept A", "Concept B", "Concept C", "Concept D"
- "Notion 1 - Matière", "Notion 2 - Matière"
- "Placeholder...", etc.
"""

import json
from pathlib import Path
from collections import defaultdict

REPO_ROOT = Path(__file__).resolve().parents[4]
QUIZ_DIR = REPO_ROOT / "src/data/quiz"
REPORT_FILE = REPO_ROOT / "dev/reports/placeholder_detection_v2.json"
REPORT_FILE.parent.mkdir(parents=True, exist_ok=True)

# Patterns de VRAIS placeholders (très spécifiques)
PLACEHOLDER_PATTERNS = {
    # Questions génériques
    "question": [
        r"^Quel concept cl[eé] as-tu [eé]tudi[eé]",
        r"^Quel concept clé de ",
        r"^Le niveau \d+ aborde des notions fondamentales",
        r"^Cite un exemple concret li[eé]",
        r"^Donne un exemple de",
    ],
    # Choix "Concept X" génériques
    "choice_generic": [
        r"^Concept [A-D]$",
        r"^Concept [A-D] $",
        r"^Option [A-D]$",
        r"^R[eé]ponse [A-D]$",
    ],
    # Notions "Notion N - Matière"
    "notion": [
        r"^Notion \d+ -",
        r"^Notion \d+ de ",
    ],
    # Placeholders littéraux
    "literal": [
        r"placeholder",
        r"exemple\.\.\.",
        r"À compléter",
        r"TODO:",
    ],
}


def has_placeholder(text, target_type):
    """Vérifie si le texte est un VRAI placeholder"""
    if not isinstance(text, str):
        return False

    text_stripped = text.strip()
    if not text_stripped:
        return False

    patterns = PLACEHOLDER_PATTERNS.get(target_type, [])

    import re
    for pattern in patterns:
        if re.search(pattern, text_stripped, re.IGNORECASE):
            return True

    return False


def analyze_quiz_v2(quiz_path):
    """Analyse stricte : cherche UNIQUEMENT les vrais placeholders"""
    try:
        with open(quiz_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        return {"error": str(e)}

    issues = []
    quiz_id = quiz_path.stem

    # Vérifier titre (contents)
    title = data.get("contents", {}).get("title", "")
    if has_placeholder(title, "question"):
        issues.append("title_is_placeholder")

    # Vérifier questions
    questions = data.get("quiz", {}).get("questions", [])
    for q_idx, question in enumerate(questions):
        q_text = question.get("question", "")

        # Chercher placeholders dans la question
        if has_placeholder(q_text, "question"):
            issues.append(f"q{q_idx}_is_placeholder")

        # Chercher choix génériques (QCM)
        if question.get("type") == "qcm":
            choices = question.get("choices", [])
            for c_idx, choice in enumerate(choices):
                if has_placeholder(choice, "choice_generic"):
                    issues.append(f"q{q_idx}_choice{c_idx}_generic")

    # Vérifier notions
    notions = data.get("exercisenotion", [])
    for n_idx, notion in enumerate(notions):
        notion_text = notion.get("notion", "")
        if has_placeholder(notion_text, "notion"):
            issues.append(f"n{n_idx}_is_placeholder")

    return {
        "quiz_id": quiz_id,
        "has_issues": len(issues) > 0,
        "issue_count": len(issues),
        "issues": issues,
        "title": title[:50],  # Aperçu du titre
    }


def main():
    print("🔍 Détection PRÉCISE des placeholders...")
    print(f"📁 Répertoire: {QUIZ_DIR}\n")

    if not QUIZ_DIR.exists():
        print(f"❌ Répertoire {QUIZ_DIR} introuvable")
        return

    quiz_files = sorted(QUIZ_DIR.glob("*.json"))
    print(f"📊 Quiz à analyser: {len(quiz_files)}\n")

    # Analyse
    results = []
    problematic_quizzes = []
    issue_summary = defaultdict(int)

    for idx, quiz_file in enumerate(quiz_files, 1):
        result = analyze_quiz_v2(quiz_file)
        results.append(result)

        if result.get("has_issues"):
            problematic_quizzes.append(result)
            for issue in result.get("issues", []):
                issue_summary[issue] += 1

        # Afficher progression
        if idx % 300 == 0:
            print(f"  [{idx}/{len(quiz_files)}] Analyzed...")

    # Résultats
    print(f"\n✅ Analyse complétée\n")
    print(f"📈 RÉSULTATS:")
    print(f"  Total quiz: {len(quiz_files)}")
    print(f"  Quiz avec placeholders VRAIS: {len(problematic_quizzes)}")
    print(f"  Pourcentage: {len(problematic_quizzes)/len(quiz_files)*100:.1f}%")
    print(f"  Quiz valides estimés: {len(quiz_files) - len(problematic_quizzes)}")

    print(f"\n🚨 TOP PLACEHOLDERS TROUVÉS:")
    for issue, count in sorted(issue_summary.items(), key=lambda x: -x[1])[:15]:
        print(f"  {issue}: {count}")

    # Sauvegarder rapport
    report = {
        "total_quizzes": len(quiz_files),
        "with_placeholders": len(problematic_quizzes),
        "valid_estimated": len(quiz_files) - len(problematic_quizzes),
        "percentage": f"{len(problematic_quizzes)/len(quiz_files)*100:.1f}%",
        "issue_summary": dict(issue_summary),
        "problematic_quizzes": problematic_quizzes[:100],  # Top 100
    }

    with open(REPORT_FILE, 'w', encoding='utf-8') as f:
        json.dump(report, f, ensure_ascii=False, indent=2)

    print(f"\n📄 Rapport sauvegardé: {REPORT_FILE}")

    # Afficher exemples
    if problematic_quizzes:
        print(f"\n📝 EXEMPLES DE QUIZ AFFECTÉS:")
        for quiz in problematic_quizzes[:5]:
            print(f"  Quiz {quiz['quiz_id']}: {quiz['title']}")
            print(f"    Issues: {', '.join(quiz['issues'][:3])}")


if __name__ == "__main__":
    main()
