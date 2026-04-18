#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""V1 generator for diagnostic quiz bank.

Primary output folders (configured in dev/tools/quiz/config_quiz_bank.v1.json):
1) src/data/quiz
2) src/data/quiz_answers

Optional compatibility output (disabled by default):
- src/data/quiz

The script clones existing quizzes by level+subject pair to reach target counts,
while creating lightweight variants (question order rotation + qcm choice rotation)
and keeping answers fully out of public quiz files.
"""

from __future__ import annotations

import argparse
import copy
import json
import random
from dataclasses import dataclass
from datetime import UTC, datetime
from pathlib import Path
from typing import Dict, List, Tuple


SCRIPT_DIR = Path(__file__).resolve().parent
# generator/ -> quiz/ -> tools/ -> dev/ -> repo root
REPO_ROOT = SCRIPT_DIR.parents[3]


@dataclass(frozen=True)
class PairKey:
    level: str
    subject: str


ALLOWED_TYPES = {"qcm", "vrai-faux"}


def now_utc_z() -> str:
    return datetime.now(UTC).isoformat().replace("+00:00", "Z")


def load_json(path: Path) -> dict:
    with path.open("r", encoding="utf-8") as f:
        return json.load(f)


def write_json(path: Path, payload: dict) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8", newline="\n") as f:
        json.dump(payload, f, ensure_ascii=False, indent=2)
        f.write("\n")


def normalize_value(value: str) -> str:
    return str(value or "").strip()


def normalize_level(level: str, aliases: Dict[str, str]) -> str:
    raw = normalize_value(level)
    return aliases.get(raw, raw)


def normalize_subject(subject: str, aliases: Dict[str, str]) -> str:
    raw = normalize_value(subject)
    return aliases.get(raw, raw)


def normalize_text(text: str) -> str:
    """Normalize text by replacing non-normalized level forms."""
    if not isinstance(text, str):
        return text

    # Mapping de toutes les formes non normalisées vers la forme normalisée
    replacements = {
        "6ème": "6eme",
        "5ème": "5eme",
        "4ème": "4eme",
        "3ème": "3eme",
        "2nde": "seconde",
        "1ère": "1ere",
        "première": "1ere",
        "Première": "1ere"
    }

    normalized = text
    for old, new in replacements.items():
        normalized = normalized.replace(old, new)

    return normalized


def normalize_question_type(raw_type: str) -> str:
    qtype = normalize_value(raw_type).lower()
    qtype = qtype.replace("_", "-")
    if qtype in {"vrai faux", "vrai-faux"}:
        return "vrai-faux"
    if qtype == "qcm":
        return "qcm"
    return "vrai-faux"


def sanitize_public_question(question: dict) -> dict:
    """Ensure public quiz questions never contain correction fields."""
    if not isinstance(question, dict):
        return question

    sanitized = copy.deepcopy(question)
    sanitized["type"] = normalize_question_type(sanitized.get("type", ""))

    for key in ("correct_option", "correct", "correct_answer", "explanation", "correction"):
        sanitized.pop(key, None)

    return sanitized


def update_answer_after_choice_rotation(answer: dict, shift: int, choice_count: int) -> dict:
    """Keep QCM correction aligned when choices are rotated."""
    if not isinstance(answer, dict) or choice_count <= 0:
        return answer

    updated = copy.deepcopy(answer)
    if isinstance(updated.get("correct"), int):
        updated["correct"] = (updated["correct"] - shift) % choice_count
    return updated


def harmonize_payload_fields(payload: dict, level: str, subject: str) -> bool:
    changed = False

    contents = payload.setdefault("contents", {})
    quiz = payload.setdefault("quiz", {})

    if contents.get("level") != level:
        contents["level"] = level
        changed = True
    if contents.get("subject") != subject:
        contents["subject"] = subject
        changed = True

    # Normaliser le titre dans contents
    if "title" in contents:
        old_title = contents["title"]
        new_title = normalize_text(old_title)
        if old_title != new_title:
            contents["title"] = new_title
            changed = True

    # Normaliser la description dans contents
    if "description" in contents:
        old_desc = contents["description"]
        new_desc = normalize_text(old_desc)
        if old_desc != new_desc:
            contents["description"] = new_desc
            changed = True

    if isinstance(quiz, dict):
        if quiz.get("level") != level:
            quiz["level"] = level
            changed = True
        if quiz.get("subject") != subject:
            quiz["subject"] = subject
            changed = True

        # Normaliser le titre dans quiz
        if "title" in quiz:
            old_title = quiz["title"]
            new_title = normalize_text(old_title)
            if old_title != new_title:
                quiz["title"] = new_title
                changed = True

        # Normaliser la description dans quiz
        if "description" in quiz:
            old_desc = quiz["description"]
            new_desc = normalize_text(old_desc)
            if old_desc != new_desc:
                quiz["description"] = new_desc
                changed = True

    return changed


def harmonize_existing_metadata(
    public_quiz_dir: Path,
    answers_dir: Path,
    src_quiz_dir: Path,
    aliases: dict,
    apply_changes: bool,
    sync_src_quiz: bool,
) -> Tuple[int, int]:
    reviewed = 0
    changed_files = 0

    for qid in numeric_json_ids(public_quiz_dir):
        public_path = public_quiz_dir / f"{qid}.json"
        answers_path = answers_dir / f"{qid}.json"
        src_path = src_quiz_dir / f"{qid}.json"

        if not answers_path.exists():
            continue

        public_payload = load_json(public_path)
        answers_payload = load_json(answers_path)
        reviewed += 1

        level_raw = (
            public_payload.get("quiz", {}).get("level")
            or public_payload.get("contents", {}).get("level")
            or answers_payload.get("contents", {}).get("level")
            or ""
        )
        subject_raw = (
            public_payload.get("quiz", {}).get("subject")
            or public_payload.get("contents", {}).get("subject")
            or answers_payload.get("contents", {}).get("subject")
            or ""
        )

        level = normalize_level(level_raw, aliases.get("level_aliases", {}))
        subject = normalize_subject(subject_raw, aliases.get("subject_aliases", {}))

        if not level or not subject:
            continue

        public_changed = harmonize_payload_fields(public_payload, level, subject)
        answers_changed = harmonize_payload_fields(answers_payload, level, subject)

        src_changed = False
        src_payload = None
        if sync_src_quiz and src_path.exists():
            src_payload = load_json(src_path)
            src_changed = harmonize_payload_fields(src_payload, level, subject)

        if public_changed or answers_changed or src_changed:
            changed_files += int(public_changed) + int(answers_changed) + int(src_changed)
            if apply_changes:
                if public_changed:
                    write_json(public_path, public_payload)
                if answers_changed:
                    write_json(answers_path, answers_payload)
                if sync_src_quiz and src_changed and src_payload is not None:
                    write_json(src_path, src_payload)

    return reviewed, changed_files


def numeric_json_ids(folder: Path) -> List[int]:
    ids: List[int] = []
    for path in folder.glob("*.json"):
        stem = path.stem
        if stem.isdigit():
            ids.append(int(stem))
    return sorted(ids)


def collect_existing_pairs(public_quiz_dir: Path, aliases: dict) -> Dict[PairKey, List[int]]:
    out: Dict[PairKey, List[int]] = {}

    for qid in numeric_json_ids(public_quiz_dir):
        payload = load_json(public_quiz_dir / f"{qid}.json")
        quiz = payload.get("quiz", {})
        contents = payload.get("contents", {})

        level = normalize_level(quiz.get("level", contents.get("level", "")), aliases["level_aliases"])
        subject = normalize_subject(quiz.get("subject", contents.get("subject", "")), aliases["subject_aliases"])
        if not level or not subject:
            continue

        key = PairKey(level=level, subject=subject)
        out.setdefault(key, []).append(qid)

    for key in list(out.keys()):
        out[key] = sorted(out[key])

    return out


def filter_pairs(
    pairs: Dict[PairKey, List[int]],
    requested_level: str,
    requested_subject: str,
    aliases: dict,
) -> Dict[PairKey, List[int]]:
    level_filter = normalize_level(requested_level, aliases.get("level_aliases", {})) if requested_level else ""
    subject_filter = normalize_subject(requested_subject, aliases.get("subject_aliases", {})) if requested_subject else ""

    out: Dict[PairKey, List[int]] = {}
    for key, ids in pairs.items():
        if level_filter and key.level != level_filter:
            continue
        if subject_filter and key.subject != subject_filter:
            continue
        out[key] = ids
    return out


def validate_generated_payload(quiz_payload: dict, answers_payload: dict) -> List[str]:
    errors: List[str] = []

    questions = quiz_payload.get("quiz", {}).get("questions", [])
    answers = answers_payload.get("quiz", {}).get("answers", [])

    if len(questions) != len(answers):
        errors.append(f"question_count_mismatch:{len(questions)}!={len(answers)}")

    for idx, question in enumerate(questions):
        if not isinstance(question, dict):
            errors.append(f"question_not_dict:{idx}")
            continue

        qtype = normalize_question_type(question.get("type", ""))
        if qtype not in ALLOWED_TYPES:
            errors.append(f"invalid_question_type:{idx}:{qtype}")

        leaked_keys = [k for k in ("correct_option", "correct", "correct_answer", "explanation", "correction") if k in question]
        if leaked_keys:
            errors.append(f"public_leak:{idx}:{','.join(leaked_keys)}")

    for idx, answer in enumerate(answers):
        if not isinstance(answer, dict):
            errors.append(f"answer_not_dict:{idx}")
            continue

        qtype = normalize_question_type(answer.get("type", ""))
        if qtype not in ALLOWED_TYPES:
            errors.append(f"invalid_answer_type:{idx}:{qtype}")

    return errors


def merge_question_answer(quiz_payload: dict, answers_payload: dict) -> List[Tuple[dict, dict]]:
    questions = list(quiz_payload.get("quiz", {}).get("questions", []))
    answers = list(answers_payload.get("quiz", {}).get("answers", []))

    by_qid = {}
    by_index = {}
    for ans in answers:
        if isinstance(ans, dict):
            qid = ans.get("question_id")
            idx = ans.get("index")
            if isinstance(qid, int):
                by_qid[qid] = ans
            if isinstance(idx, int):
                by_index[idx] = ans

    merged: List[Tuple[dict, dict]] = []
    for i, question in enumerate(questions):
        qid = question.get("id") if isinstance(question, dict) else None
        answer = by_qid.get(qid) if isinstance(qid, int) else None
        if answer is None:
            answer = by_index.get(i, {})
        merged.append((question, answer if isinstance(answer, dict) else {}))

    return merged


def build_variant(base_quiz: dict, base_answers: dict, variant_index: int) -> Tuple[dict, dict]:
    quiz_payload = copy.deepcopy(base_quiz)
    answers_payload = copy.deepcopy(base_answers)

    merged = merge_question_answer(quiz_payload, answers_payload)
    if not merged:
        return quiz_payload, answers_payload

    rotate = variant_index % len(merged)
    merged = merged[rotate:] + merged[:rotate]

    new_questions: List[dict] = []
    new_answers: List[dict] = []

    for i, (question, answer) in enumerate(merged):
        question = sanitize_public_question(question)
        answer = copy.deepcopy(answer)
        answer["type"] = normalize_question_type(answer.get("type", question.get("type", "")))

        question["id"] = i + 1

        qtype = normalize_question_type(question.get("type", answer.get("type", "")))
        question["type"] = qtype
        if qtype == "qcm" and isinstance(question.get("choices"), list) and len(question["choices"]) > 1:
            choices = list(question["choices"])
            shift = (variant_index + i) % len(choices)
            choices = choices[shift:] + choices[:shift]
            question["choices"] = choices
            answer = update_answer_after_choice_rotation(answer, shift, len(choices))

        answer["index"] = i
        answer["question_id"] = i + 1

        new_questions.append(question)
        new_answers.append(answer)

    quiz_payload.setdefault("quiz", {})["questions"] = new_questions
    quiz_payload["quiz"]["question_count"] = len(new_questions)

    answers_payload.setdefault("quiz", {})["answers"] = new_answers
    answers_payload["quiz"]["question_count"] = len(new_answers)

    return quiz_payload, answers_payload


def update_metadata(quiz_payload: dict, answers_payload: dict, key: PairKey, series_idx: int) -> None:
    now = now_utc_z()

    contents = quiz_payload.setdefault("contents", {})
    quiz = quiz_payload.setdefault("quiz", {})

    base_public_title = f"Quiz Diagnostic {key.level} {key.subject}"
    base_quiz_title = f"Diagnostic {key.subject} {key.level}"

    contents["title"] = f"{base_public_title} - Serie {series_idx:02d}"
    contents["type"] = contents.get("type", "quiz")
    contents["level"] = key.level
    contents["subject"] = key.subject
    contents["status"] = contents.get("status", "published")
    contents["updated_at"] = now
    contents.setdefault("created_at", now)

    quiz["title"] = f"{base_quiz_title} - Serie {series_idx:02d}"
    quiz["level"] = key.level
    quiz["subject"] = key.subject
    quiz["question_count"] = int(quiz.get("question_count", 8) or 8)
    quiz["passing_score"] = int(quiz.get("passing_score", 70) or 70)

    ans_contents = answers_payload.setdefault("contents", {})
    ans_quiz = answers_payload.setdefault("quiz", {})
    ans_contents["title"] = contents["title"]
    ans_contents["level"] = key.level
    ans_contents["subject"] = key.subject
    ans_quiz["title"] = quiz["title"]
    ans_quiz["question_count"] = quiz["question_count"]


def main() -> int:
    parser = argparse.ArgumentParser(description="Generate V1 diagnostic quiz bank by level+subject pairs")
    parser.add_argument("--config", default="dev/tools/quiz/config_quiz_bank.v1.json", help="Path to JSON config")
    parser.add_argument("--dry-run", action="store_true", help="Only report planned creations")
    parser.add_argument(
        "--apply",
        action="store_true",
        help="Apply file writes. Without this flag, generation remains non-destructive (dry-run only).",
    )
    parser.add_argument(
        "--harmonize-only",
        action="store_true",
        help="Harmonize existing level/subject metadata aliases without creating new quizzes.",
    )
    parser.add_argument(
        "--sync-src-quiz",
        action="store_true",
        help="Also write generated public quiz files to src/data/quiz for backend submit compatibility",
    )
    parser.add_argument("--seed", type=int, default=42, help="Random seed used for template selection")
    parser.add_argument("--level", default="", help="Filter generation on one normalized level (ex: 3eme, seconde)")
    parser.add_argument("--subject", default="", help="Filter generation on one subject (ex: SVT, Physique-Chimie)")

    args = parser.parse_args()

    config_path = REPO_ROOT / args.config
    config = load_json(config_path)

    public_quiz_dir = REPO_ROOT / config["paths"]["public_quiz"]
    answers_dir = REPO_ROOT / config["paths"]["quiz_answers"]
    src_quiz_dir = REPO_ROOT / config["paths"]["src_quiz_optional"]

    target = int(config.get("target_quiz_per_pair", 50))
    aliases = config.get("normalization", {"level_aliases": {}, "subject_aliases": {}})

    if args.harmonize_only:
        reviewed, changed_files = harmonize_existing_metadata(
            public_quiz_dir=public_quiz_dir,
            answers_dir=answers_dir,
            src_quiz_dir=src_quiz_dir,
            aliases=aliases,
            apply_changes=args.apply,
            sync_src_quiz=args.sync_src_quiz,
        )
        mode = "apply" if args.apply else "dry-run"
        print(f"[harmonize] mode: {mode}")
        print(f"[harmonize] reviewed_quiz_files: {reviewed}")
        print(f"[harmonize] files_to_update: {changed_files}")
        print("[harmonize] deletion: 0 (no file is deleted by this mode)")
        return 0

    existing = collect_existing_pairs(public_quiz_dir, aliases)
    existing = filter_pairs(existing, args.level, args.subject, aliases)

    if not existing:
        print("[plan] pairs: 0")
        print("[plan] new_quizzes: 0")
        print("[plan] no pair matched the current --level/--subject filter")
        return 0

    all_ids = set(numeric_json_ids(public_quiz_dir))
    all_ids.update(numeric_json_ids(answers_dir))
    next_id = (max(all_ids) + 1) if all_ids else 1

    random.seed(args.seed)
    planned = []
    planned_by_pair: Dict[PairKey, int] = {}
    validation_issues = 0

    for key in sorted(existing.keys(), key=lambda x: (x.level, x.subject)):
        ids = existing[key]
        missing = max(0, target - len(ids))
        if missing <= 0:
            continue

        template_ids = [qid for qid in ids if (answers_dir / f"{qid}.json").exists()]
        if not template_ids:
            print(f"[skip] {key.level} | {key.subject}: no answers template found")
            continue

        for i in range(missing):
            template_id = random.choice(template_ids)
            new_id = next_id
            next_id += 1

            base_quiz = load_json(public_quiz_dir / f"{template_id}.json")
            base_answers = load_json(answers_dir / f"{template_id}.json")

            variant_quiz, variant_answers = build_variant(base_quiz, base_answers, variant_index=i + 1)
            update_metadata(variant_quiz, variant_answers, key, series_idx=len(ids) + i + 1)

            issues = validate_generated_payload(variant_quiz, variant_answers)
            if issues:
                validation_issues += len(issues)
                print(f"[warn] id={new_id} pair={key.level}/{key.subject} issues={';'.join(issues[:3])}")

            planned.append((new_id, key, variant_quiz, variant_answers, template_id))
            planned_by_pair[key] = planned_by_pair.get(key, 0) + 1

    print(f"[plan] pairs: {len(existing)}")
    print(f"[plan] new_quizzes: {len(planned)}")
    print(f"[plan] validation_issues: {validation_issues}")

    for key in sorted(planned_by_pair.keys(), key=lambda x: (x.level, x.subject)):
        print(f"[plan] pair={key.level}/{key.subject} new={planned_by_pair[key]}")

    if args.dry_run:
        for new_id, key, _, _, template_id in planned[:20]:
            print(f"[dry-run] id={new_id} pair={key.level}/{key.subject} template={template_id}")
        if len(planned) > 20:
            print(f"[dry-run] ... {len(planned) - 20} more")
        return 0

    if not args.apply:
        print("[safe-guard] Generation blocked: use --apply to confirm file creation.")
        print("[safe-guard] No file has been created, modified, or deleted.")
        return 0

    for new_id, _, quiz_payload, answers_payload, _ in planned:
        write_json(public_quiz_dir / f"{new_id}.json", quiz_payload)
        write_json(answers_dir / f"{new_id}.json", answers_payload)
        if args.sync_src_quiz:
            write_json(src_quiz_dir / f"{new_id}.json", quiz_payload)

    print(f"[write] {config['paths']['public_quiz']}: {len(planned)}")
    print(f"[write] src/data/quiz_answers: {len(planned)}")
    if args.sync_src_quiz:
        print(f"[write] src/data/quiz: {len(planned)}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
