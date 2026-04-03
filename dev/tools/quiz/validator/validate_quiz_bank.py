#!/usr/bin/env python3
"""Validate generated diagnostic quiz files for V1 bank generation."""

from __future__ import annotations

import argparse
import json
from collections import defaultdict
from pathlib import Path
from typing import Dict, List, Tuple


def load_json(path: Path) -> dict:
    with path.open("r", encoding="utf-8") as f:
        return json.load(f)


def numeric_json_paths(folder: Path) -> List[Path]:
    return sorted([p for p in folder.glob("*.json") if p.stem.isdigit()], key=lambda p: int(p.stem))


def normalize(value: str) -> str:
    return str(value or "").strip()


def normalize_with_alias(value: str, aliases: Dict[str, str]) -> str:
    raw = normalize(value)
    return aliases.get(raw, raw)


def validate(
    public_dir: Path,
    answers_dir: Path,
    target: int,
    level_aliases: Dict[str, str],
    subject_aliases: Dict[str, str],
) -> Tuple[List[str], Dict[Tuple[str, str], int]]:
    errors: List[str] = []
    counts: Dict[Tuple[str, str], int] = defaultdict(int)

    for quiz_path in numeric_json_paths(public_dir):
        qid = int(quiz_path.stem)
        answers_path = answers_dir / f"{qid}.json"

        try:
            quiz_data = load_json(quiz_path)
        except Exception as exc:
            errors.append(f"quiz {qid}: invalid JSON ({exc})")
            continue

        if not answers_path.exists():
            errors.append(f"quiz {qid}: missing answers file")
            continue

        try:
            answers_data = load_json(answers_path)
        except Exception as exc:
            errors.append(f"quiz {qid}: invalid answers JSON ({exc})")
            continue

        quiz = quiz_data.get("quiz", {})
        contents = quiz_data.get("contents", {})

        level = normalize_with_alias(quiz.get("level", contents.get("level", "")), level_aliases)
        subject = normalize_with_alias(quiz.get("subject", contents.get("subject", "")), subject_aliases)
        if not level or not subject:
            errors.append(f"quiz {qid}: missing level or subject")
            continue

        questions = quiz.get("questions", [])
        if not isinstance(questions, list) or not questions:
            errors.append(f"quiz {qid}: missing questions[]")
            continue

        for i, question in enumerate(questions):
            if not isinstance(question, dict):
                errors.append(f"quiz {qid}: question[{i}] is not an object")
                continue
            if "answer" in question or "correction" in question:
                errors.append(f"quiz {qid}: public question[{i}] leaks answer/correction")

        answers = answers_data.get("quiz", {}).get("answers", [])
        if not isinstance(answers, list) or len(answers) != len(questions):
            errors.append(
                f"quiz {qid}: answers count mismatch (questions={len(questions)} answers={len(answers) if isinstance(answers, list) else 'N/A'})"
            )

        counts[(level, subject)] += 1

    for (level, subject), count in sorted(counts.items()):
        if count < target:
            errors.append(f"coverage {level}/{subject}: {count}/{target} (missing {target - count})")

    return errors, counts


def main() -> int:
    parser = argparse.ArgumentParser(description="Validate V1 quiz bank outputs")
    parser.add_argument("--public-dir", default="src/data/quiz")
    parser.add_argument("--answers-dir", default="src/data/quiz_answers")
    parser.add_argument("--target", type=int, default=50)
    parser.add_argument("--config", default="dev/tools/quiz/config_quiz_bank.v1.json")
    args = parser.parse_args()

    repo_root = Path(__file__).resolve().parents[3]
    public_dir = repo_root / args.public_dir
    answers_dir = repo_root / args.answers_dir

    config = load_json(repo_root / args.config)
    normalization = config.get("normalization", {})
    level_aliases = normalization.get("level_aliases", {})
    subject_aliases = normalization.get("subject_aliases", {})

    errors, counts = validate(
        public_dir=public_dir,
        answers_dir=answers_dir,
        target=args.target,
        level_aliases=level_aliases,
        subject_aliases=subject_aliases,
    )

    print(f"[validate] pairs: {len(counts)}")
    print(f"[validate] errors: {len(errors)}")

    for err in errors[:200]:
        print(f"- {err}")

    return 1 if errors else 0


if __name__ == "__main__":
    raise SystemExit(main())
