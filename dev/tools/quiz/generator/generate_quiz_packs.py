#!/usr/bin/env python3
"""Generate enriched quiz packs from existing quiz and quiz_answers files.

Usage:
    python dev/tools/quiz/generate_quiz_packs.py --start-id 11 --end-id 50
"""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path
from typing import Any


def load_json(path: Path) -> Any:
    with path.open("r", encoding="utf-8") as f:
        return json.load(f)


def save_json(path: Path, data: Any) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8", newline="\n") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
        f.write("\n")


def normalize_quiz(quiz: dict[str, Any], add_question_ids: bool) -> int:
    questions = list(quiz.get("quiz", {}).get("questions", []))
    if add_question_ids:
        for idx, question in enumerate(questions, start=1):
            question["id"] = idx

    quiz.setdefault("quiz", {})["questions"] = questions
    quiz["quiz"]["question_count"] = len(questions)
    return len(questions)


def normalize_answers(answers: dict[str, Any], questions: list[dict[str, Any]], qcount: int, level: str, subject: str) -> None:
    answers_obj = answers.setdefault("quiz", {})
    answer_list = list(answers_obj.get("answers", []))

    for idx, answer in enumerate(answer_list):
        answer["index"] = idx
        answer["question_id"] = idx + 1
        if idx < len(questions):
            answer["type"] = str(questions[idx].get("type", answer.get("type", "open")))

    answers_obj["answers"] = answer_list
    answers_obj["question_count"] = qcount
    answers_obj["level"] = level
    answers_obj["subject"] = subject


PLACEHOLDER_PATTERNS = [
    re.compile(r"question de diagnostic pour", re.IGNORECASE),
    re.compile(r"\bconcept\s*[a-d]\b", re.IGNORECASE),
    re.compile(r"\bexemple\s*\.\.\.\b", re.IGNORECASE),
]


def contains_placeholder_content(quiz: dict[str, Any], answers: dict[str, Any]) -> bool:
    questions = list(quiz.get("quiz", {}).get("questions", []))
    answer_list = list(answers.get("quiz", {}).get("answers", []))

    for question in questions:
        qtext = str(question.get("question", "")).strip()
        if any(pattern.search(qtext) for pattern in PLACEHOLDER_PATTERNS):
            return True

        choices = question.get("choices", [])
        if isinstance(choices, list) and len(choices) == 4:
            normalized = [str(c).strip().upper() for c in choices]
            if normalized == ["A", "B", "C", "D"]:
                return True

    for answer in answer_list:
        correction = str(answer.get("correction", "")).strip()
        if any(pattern.search(correction) for pattern in PLACEHOLDER_PATTERNS):
            return True

    return False


def main() -> int:
    parser = argparse.ArgumentParser(description="Generate UTF-8 safe quiz packs")
    parser.add_argument("--start-id", type=int, required=True)
    parser.add_argument("--end-id", type=int, required=True)
    parser.add_argument("--input-quiz-dir", default="src/data/quiz")
    parser.add_argument("--input-answers-dir", default="src/data/quiz_answers")
    parser.add_argument("--output-dir", default="src/data/quiz_packs")
    parser.add_argument(
        "--min-questions",
        type=int,
        default=8,
        help="Minimum number of questions required to generate a pack (default: 8).",
    )
    parser.add_argument(
        "--add-question-ids",
        action="store_true",
        help="Add sequential id to quiz.questions entries (disabled by default to preserve source schema).",
    )
    args = parser.parse_args()

    if args.start_id > args.end_id:
        raise ValueError("start-id must be <= end-id")

    root = Path(__file__).resolve().parents[3]
    in_quiz_dir = root / args.input_quiz_dir
    in_answers_dir = root / args.input_answers_dir
    out_dir = root / args.output_dir

    generated = 0
    skipped: list[int] = []
    skipped_low_questions: list[tuple[int, int]] = []
    skipped_placeholders: list[int] = []

    for quiz_id in range(args.start_id, args.end_id + 1):
        quiz_path = in_quiz_dir / f"{quiz_id}.json"
        ans_path = in_answers_dir / f"{quiz_id}.json"

        if not quiz_path.exists() or not ans_path.exists():
            skipped.append(quiz_id)
            continue

        quiz = load_json(quiz_path)
        answers = load_json(ans_path)

        qcount = normalize_quiz(quiz, add_question_ids=args.add_question_ids)

        if qcount < args.min_questions:
            skipped_low_questions.append((quiz_id, qcount))
            continue

        questions = quiz.get("quiz", {}).get("questions", [])
        level = str(quiz.get("quiz", {}).get("level", quiz.get("contents", {}).get("level", "")))
        subject = str(quiz.get("quiz", {}).get("subject", quiz.get("contents", {}).get("subject", "")))
        normalize_answers(answers, questions, qcount, level, subject)

        if contains_placeholder_content(quiz, answers):
            skipped_placeholders.append(quiz_id)
            continue

        quiz_out = out_dir / "quiz" / f"{quiz_id}.json"
        answers_out = out_dir / "quiz_answers" / f"{quiz_id}.json"

        save_json(quiz_out, quiz)
        save_json(answers_out, answers)

        generated += 1
        print(f"OK {quiz_id}: {quiz_out.relative_to(root)} / {answers_out.relative_to(root)}")

    print(f"Generated pairs: {generated}")
    if skipped:
        print(f"Skipped IDs (missing source files): {', '.join(map(str, skipped))}")
    if skipped_low_questions:
        details = ", ".join(f"{quiz_id}({count})" for quiz_id, count in skipped_low_questions)
        print(f"Skipped IDs (question_count < {args.min_questions}): {details}")
    if skipped_placeholders:
        print(f"Skipped IDs (placeholder content detected): {', '.join(map(str, skipped_placeholders))}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
