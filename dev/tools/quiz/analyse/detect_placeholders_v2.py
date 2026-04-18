#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Détecteur précis de placeholders pour la structure finale quiz runtime."""

from __future__ import annotations

import argparse
import json
import re
from collections import defaultdict
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[4]
DEFAULT_QUIZ_DIR = REPO_ROOT / "src/data/quiz"
DEFAULT_ANSWERS_DIR = REPO_ROOT / "src/data/quiz_answers"
REPORT_FILE = REPO_ROOT / "dev/reports/placeholder_detection_v2.json"
REPORT_FILE.parent.mkdir(parents=True, exist_ok=True)

PLACEHOLDER_PATTERNS = {
    "question": [
        r"^Quel concept cl[eé] as-tu [eé]tudi[eé]",
        r"^Quel concept clé de ",
        r"^Le niveau .* aborde des notions fondamentales",
        r"^Cite un exemple concret li[eé]",
        r"^Donne un exemple concret$",
    ],
    "choice_generic": [
        r"^Concept [A-D]$",
        r"^Option [A-D]$",
        r"^R[eé]ponse [A-D]$",
    ],
    "notion": [
        r"^Notion \d+ -",
        r"^Notion \d+ de ",
        r"^Concept cl[eé] de ",
    ],
    "literal": [
        r"placeholder",
        r"exemple\.\.\.",
        r"À compléter",
        r"TODO:",
    ],
    "generic_correction": [
        r"La bonne réponse est celle qui correspond au concept étudié",
        r"Vrai ou Faux dépend de la définition précise du concept",
        r"Ta réponse textuelle doit correspondre à ce qui est attendu",
    ],
}


def has_placeholder(text: object, target_type: str) -> bool:
    if not isinstance(text, str):
        return False

    text_stripped = text.strip()
    if not text_stripped:
        return False

    for pattern in PLACEHOLDER_PATTERNS.get(target_type, []):
        if re.search(pattern, text_stripped, re.IGNORECASE):
            return True

    return False


def load_json(path: Path) -> dict:
    with path.open("r", encoding="utf-8") as handle:
        return json.load(handle)


def analyze_quiz_pair(quiz_path: Path, answers_dir: Path) -> dict:
    try:
        quiz_data = load_json(quiz_path)
    except Exception as exc:
        return {"quiz_id": quiz_path.stem, "has_issues": True, "issues": [f"quiz_read_error:{exc}"]}

    quiz_id = quiz_path.stem
    answers_path = answers_dir / f"{quiz_id}.json"
    issues: list[str] = []

    title = str(quiz_data.get("contents", {}).get("title", ""))
    if has_placeholder(title, "question"):
        issues.append("title_is_placeholder")

    questions = quiz_data.get("quiz", {}).get("questions", [])
    for q_idx, question in enumerate(questions):
        q_text = question.get("question", "")
        q_type = str(question.get("type", "")).strip().lower().replace("_", "-")

        if has_placeholder(q_text, "question"):
            issues.append(f"q{q_idx + 1}_question_placeholder")

        if has_placeholder(question.get("placeholder", ""), "literal"):
            issues.append(f"q{q_idx + 1}_literal_placeholder")

        if q_type == "qcm":
            for c_idx, choice in enumerate(question.get("choices", [])):
                if has_placeholder(choice, "choice_generic"):
                    issues.append(f"q{q_idx + 1}_choice{c_idx + 1}_generic")

    for n_idx, notion in enumerate(quiz_data.get("exercisenotion", [])):
        if has_placeholder(notion.get("notion", ""), "notion"):
            issues.append(f"n{n_idx + 1}_notion_placeholder")

    if not answers_path.exists():
        issues.append("answers_missing")
    else:
        try:
            answers_data = load_json(answers_path)
            answers = answers_data.get("quiz", {}).get("answers", [])
            for a_idx, answer_row in enumerate(answers):
                if has_placeholder(answer_row.get("correction", ""), "generic_correction"):
                    issues.append(f"a{a_idx + 1}_generic_correction")
                if has_placeholder(answer_row.get("answer", ""), "choice_generic"):
                    issues.append(f"a{a_idx + 1}_generic_answer")
        except Exception as exc:
            issues.append(f"answers_read_error:{exc}")

    return {
        "quiz_id": quiz_id,
        "has_issues": len(issues) > 0,
        "issue_count": len(issues),
        "issues": issues,
        "title": title[:80],
    }


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Détection stricte des placeholders dans les quiz runtime.")
    parser.add_argument("--quiz-dir", default=str(DEFAULT_QUIZ_DIR), help="Dossier des quiz runtime")
    parser.add_argument("--answers-dir", default=str(DEFAULT_ANSWERS_DIR), help="Dossier des réponses runtime")
    parser.add_argument("--output", default=str(REPORT_FILE), help="Rapport JSON de sortie")
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    quiz_dir = Path(args.quiz_dir)
    answers_dir = Path(args.answers_dir)
    report_file = Path(args.output)

    print("🔍 Détection PRÉCISE des placeholders runtime...")
    print(f"📁 Quiz: {quiz_dir}")
    print(f"📁 Answers: {answers_dir}\n")

    if not quiz_dir.exists():
        print(f"❌ Répertoire introuvable: {quiz_dir}")
        return

    quiz_files = sorted(quiz_dir.glob("*.json"))
    problematic_quizzes = []
    issue_summary: dict[str, int] = defaultdict(int)

    for idx, quiz_file in enumerate(quiz_files, 1):
        result = analyze_quiz_pair(quiz_file, answers_dir)

        if result.get("has_issues"):
            problematic_quizzes.append(result)
            for issue in result.get("issues", []):
                issue_summary[issue] += 1

        if idx % 300 == 0:
            print(f"  [{idx}/{len(quiz_files)}] analysés...")

    total = len(quiz_files)
    affected = len(problematic_quizzes)
    percent = (affected / total * 100) if total else 0

    print("\n✅ Analyse complétée\n")
    print(f"📈 Total quiz: {total}")
    print(f"🚨 Quiz avec placeholders confirmés: {affected}")
    print(f"📉 Pourcentage: {percent:.1f}%")
    print(f"✅ Quiz estimés sains: {total - affected}")

    report = {
        "total_quizzes": total,
        "with_placeholders": affected,
        "valid_estimated": total - affected,
        "percentage": f"{percent:.1f}%",
        "issue_summary": dict(issue_summary),
        "problematic_quizzes": problematic_quizzes[:200],
    }

    report_file.parent.mkdir(parents=True, exist_ok=True)
    with report_file.open("w", encoding="utf-8") as handle:
        json.dump(report, handle, ensure_ascii=False, indent=2)

    print(f"📄 Rapport sauvegardé: {report_file}")


if __name__ == "__main__":
    main()

