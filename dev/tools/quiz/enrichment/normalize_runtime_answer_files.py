#!/usr/bin/env python3
"""Normalise les fichiers runtime quiz_answers vers la structure finale."""

from __future__ import annotations

import argparse
import json
import re
from pathlib import Path
from typing import Any, Dict, List, Tuple

REPO_ROOT = Path(__file__).resolve().parents[4]
DEFAULT_QUIZ_DIR = REPO_ROOT / "src/data/quiz"
DEFAULT_ANSWERS_DIR = REPO_ROOT / "src/data/quiz_answers"


def load_json(path: Path) -> Dict[str, Any]:
    with path.open("r", encoding="utf-8") as handle:
        data = json.load(handle)
    if not isinstance(data, dict):
        raise ValueError(f"JSON invalide: {path}")
    return data


def dump_json(path: Path, payload: Dict[str, Any]) -> None:
    with path.open("w", encoding="utf-8") as handle:
        json.dump(payload, handle, ensure_ascii=False, indent=2)
        handle.write("\n")


def normalize_type(raw_type: Any) -> str:
    value = str(raw_type or "open").strip().lower().replace("_", "-")
    if value in {"tf", "truefalse", "true-false", "vrai/faux", "vrai-faux"}:
        return "vrai-faux"
    if value in {"qcm", "mcq"}:
        return "qcm"
    return "open"


def resolve_choice_answer(correct_value: Any, choices: List[str]) -> Any:
    if isinstance(correct_value, int):
        if 0 <= correct_value < len(choices):
            return choices[correct_value]
        return ""

    if isinstance(correct_value, str):
        value = correct_value.strip()
        if value.isdigit():
            index = int(value)
            if 0 <= index < len(choices):
                return choices[index]
        letter_map = {"A": 0, "B": 1, "C": 2, "D": 3}
        if value.upper() in letter_map and letter_map[value.upper()] < len(choices):
            return choices[letter_map[value.upper()]]
        if value in choices:
            return value
    return correct_value or ""


def clean_text(value: Any) -> str:
    text = str(value or "")
    text = re.sub(r"<[^>]+>", " ", text)
    text = re.sub(r"\s+", " ", text)
    return text.strip()


def sentence_to_statement(value: Any) -> str:
    text = clean_text(value)
    text = re.sub(r"^(VRAI|FAUX)\.\s*", "", text, flags=re.IGNORECASE)
    text = re.sub(r"^Réponse attendue\s*:\s*", "", text, flags=re.IGNORECASE)
    if not text:
        return ""

    first = re.split(r"(?<=[.!?])\s+", text)[0].strip()
    first = first.rstrip(" .?!;:")
    if not first:
        return ""

    return first[0].upper() + first[1:] + "."


def build_true_false_statement(question_text: Any, answer_text: Any, correction_text: Any) -> str:
    for candidate in (answer_text, correction_text):
        statement = sentence_to_statement(candidate)
        if len(statement) >= 20:
            return statement

    source = clean_text(question_text)
    lowered = source.lower()

    if "importance de l'étude" in lowered or "comprendre le monde contemporain" in lowered:
        return "L'étude de ce thème aide à comprendre le monde contemporain."
    if "territoire" in lowered and "espace géographique" in lowered:
        return "Un territoire est un espace géographique approprié et organisé par une société."
    if "comment tu traiterais" in lowered or "traiterais un exercice" in lowered:
        return "Pour traiter ce type d'exercice, il faut identifier les données, choisir la bonne méthode et vérifier le résultat."
    if "idh" in lowered:
        return "L'IDH combine la santé, l'éducation et le niveau de vie."
    if "pib réel" in lowered or "pib nominal" in lowered:
        return "Le PIB réel corrige l'effet de l'inflation, contrairement au PIB nominal."
    if "suite géométrique" in lowered:
        return "Dans une suite géométrique, chaque terme s'obtient en multipliant le précédent par une même raison."

    generic_subject = source.rstrip(" .?!;:")
    if generic_subject:
        return f"Cette affirmation reformule l'idée essentielle à retenir : {generic_subject}."

    return "Cette affirmation reformule le point essentiel attendu dans ce quiz."


def build_true_false_correction(statement: str, correction_text: Any) -> str:
    correction = clean_text(correction_text)
    if correction:
        if correction.lower().startswith(("vrai.", "faux.")):
            return correction
        return f"VRAI. {correction}"
    return f"VRAI. {statement}"


def normalize_question_row(question: Dict[str, Any]) -> Dict[str, Any]:
    qtype = normalize_type(question.get("type"))
    normalized = {
        "type": qtype,
        "question": str(question.get("question") or ""),
    }
    if qtype == "qcm":
        normalized["choices"] = list(question.get("choices") or question.get("options") or [])
    return normalized


def normalize_quiz_payload(quiz_data: Dict[str, Any]) -> Dict[str, Any]:
    if isinstance(quiz_data.get("contents"), dict) and isinstance(quiz_data.get("quiz"), dict):
        questions = quiz_data.get("quiz", {}).get("questions", [])
        normalized_questions = [normalize_question_row(q) for q in questions if isinstance(q, dict)]
        quiz_data["quiz"]["questions"] = normalized_questions
        quiz_data["quiz"]["question_count"] = len(normalized_questions)
        quiz_data["quiz"]["type"] = "quiz"
        quiz_data.setdefault("contents", {})["type"] = "quiz"
        quiz_data.setdefault("contents", {})["status"] = quiz_data.get("contents", {}).get("status", "published")
        quiz_data.setdefault("exercisenotion", quiz_data.get("exercisenotion", []))
        quiz_data.setdefault("exerciseresponses", [])
        return quiz_data

    questions = quiz_data.get("questions", [])
    normalized_questions = [normalize_question_row(q) for q in questions if isinstance(q, dict)]

    return {
        "contents": {
            "title": str(quiz_data.get("title") or "Diagnostic"),
            "type": "quiz",
            "level": str(quiz_data.get("level") or ""),
            "subject": str(quiz_data.get("subject") or ""),
            "description": str(quiz_data.get("description") or ""),
            "status": "published",
        },
        "quiz": {
            "title": str(quiz_data.get("title") or "Diagnostic"),
            "type": "quiz",
            "level": str(quiz_data.get("level") or ""),
            "subject": str(quiz_data.get("subject") or ""),
            "question_count": len(normalized_questions),
            "passing_score": int(quiz_data.get("passing_score") or 70),
            "time_limit_minutes": int(quiz_data.get("time_limit_minutes") or 15),
            "questions": normalized_questions,
        },
        "exercisenotion": quiz_data.get("exercisenotion", []),
        "exerciseresponses": [],
    }


def resolve_answer(question: Dict[str, Any], answer_row: Dict[str, Any]) -> Tuple[str, Any]:
    qtype = normalize_type(answer_row.get("type") or question.get("type"))

    if qtype == "qcm":
        choices = list(question.get("choices") or question.get("options") or [])
        explicit_answer = answer_row.get("answer")
        if explicit_answer not in (None, ""):
            return qtype, resolve_choice_answer(explicit_answer, choices)
        correct_value = answer_row.get("correct", question.get("correct", question.get("correct_option", question.get("correct_answer", ""))))
        return qtype, resolve_choice_answer(correct_value, choices)

    explicit_answer = answer_row.get("answer")
    if explicit_answer not in (None, ""):
        return qtype, explicit_answer

    if qtype == "vrai-faux":
        raw_value = answer_row.get("correct", question.get("correct", question.get("correct_answer", False)))
        if isinstance(raw_value, str):
            return qtype, "vrai" if raw_value.strip().lower() in {"true", "vrai", "1", "yes"} else "faux"
        return qtype, "vrai" if bool(raw_value) else "faux"

    raw_value = answer_row.get("correct_answer", answer_row.get("correct", question.get("correct_answer", question.get("answer", ""))))
    return qtype, raw_value or ""


def normalize_one(
    quiz_path: Path,
    answers_path: Path,
    dry_run: bool = False,
    convert_open_to_tf: bool = False,
    start_id: int = 0,
    end_id: int = 0,
) -> bool:
    raw_quiz_data = load_json(quiz_path)
    quiz_data = normalize_quiz_payload(raw_quiz_data)
    answers_data = load_json(answers_path)

    if isinstance(answers_data.get("quiz"), dict):
        answers = answers_data.get("quiz", {}).get("answers", [])
    else:
        answers = answers_data.get("answers", [])

    questions = quiz_data.get("quiz", {}).get("questions", [])
    if not isinstance(questions, list) or not isinstance(answers, list):
        return False

    normalized_answers = []
    changed = raw_quiz_data != quiz_data
    quiz_id = int(quiz_path.stem) if quiz_path.stem.isdigit() else 0
    in_requested_range = (start_id <= quiz_id <= end_id) if start_id and end_id else True

    for idx, question in enumerate(questions):
        current_answer = answers[idx] if idx < len(answers) and isinstance(answers[idx], dict) else {}

        question_text_clean = re.sub(r"\.{2,}", ".", clean_text(question.get("question", "")))
        if question_text_clean and question.get("question") != question_text_clean:
            question["question"] = question_text_clean
            changed = True

        if convert_open_to_tf and in_requested_range and normalize_type(question.get("type")) == "open":
            existing_answer_text = current_answer.get("answer", "")
            existing_correction_text = current_answer.get("correction") or current_answer.get("explanation") or ""
            statement = build_true_false_statement(
                question.get("question", ""),
                existing_answer_text,
                existing_correction_text,
            )
            question["type"] = "vrai-faux"
            question["question"] = statement
            current_answer = dict(current_answer)
            current_answer["type"] = "vrai-faux"
            current_answer["answer"] = "vrai"
            current_answer["correction"] = build_true_false_correction(statement, existing_correction_text)
            changed = True

        if convert_open_to_tf and in_requested_range and normalize_type(question.get("type")) == "vrai-faux":
            correction_preview = clean_text(current_answer.get("correction") or current_answer.get("explanation") or "")
            if correction_preview.lower() in {"vrai. vrai", "réponse attendue : vrai"}:
                current_answer = dict(current_answer)
                current_answer["correction"] = f"VRAI. {question.get('question', '')}"
                changed = True

        qtype, answer_value = resolve_answer(question, current_answer)
        correction = current_answer.get("correction") or current_answer.get("explanation") or ""
        if correction == "" and isinstance(answer_value, str) and answer_value.strip():
            correction = f"Réponse attendue : {answer_value}"

        normalized = {
            "index": idx,
            "question_id": idx + 1,
            "type": qtype,
            "answer": answer_value,
            "correction": correction,
        }
        normalized_answers.append(normalized)

        if current_answer != normalized:
            changed = True

    contents = answers_data.get("contents", {}) if isinstance(answers_data.get("contents"), dict) else {}
    quiz_meta = answers_data.get("quiz", {}) if isinstance(answers_data.get("quiz"), dict) else {}

    normalized_payload = {
        "contents": {
            "title": str(contents.get("title") or quiz_meta.get("title") or quiz_data.get("contents", {}).get("title", "")),
            "level": str(contents.get("level") or quiz_meta.get("level") or quiz_data.get("contents", {}).get("level", "")),
            "subject": str(contents.get("subject") or quiz_meta.get("subject") or quiz_data.get("contents", {}).get("subject", "")),
        },
        "quiz": {
            "title": str(quiz_meta.get("title") or quiz_data.get("quiz", {}).get("title", "")),
            "question_count": len(normalized_answers),
            "level": str(quiz_meta.get("level") or quiz_data.get("quiz", {}).get("level", "")),
            "subject": str(quiz_meta.get("subject") or quiz_data.get("quiz", {}).get("subject", "")),
            "answers": normalized_answers,
        },
    }

    if changed and not dry_run:
        dump_json(quiz_path, quiz_data)
        dump_json(answers_path, normalized_payload)

    return changed


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Normaliser les quiz_answers legacy vers le format runtime final.")
    parser.add_argument("--quiz-dir", default=str(DEFAULT_QUIZ_DIR), help="Dossier des quiz runtime")
    parser.add_argument("--answers-dir", default=str(DEFAULT_ANSWERS_DIR), help="Dossier des réponses runtime")
    parser.add_argument("--dry-run", action="store_true", help="Analyser sans écrire")
    parser.add_argument("--convert-open-to-tf", action="store_true", help="Convertir les questions open en vrai-faux")
    parser.add_argument("--start-id", type=int, default=0, help="ID de début pour la conversion ciblée")
    parser.add_argument("--end-id", type=int, default=0, help="ID de fin pour la conversion ciblée")
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    quiz_dir = Path(args.quiz_dir)
    answers_dir = Path(args.answers_dir)

    updated = 0
    scanned = 0

    for answers_path in sorted(answers_dir.glob("*.json")):
        quiz_path = quiz_dir / answers_path.name
        if not quiz_path.exists():
            continue
        scanned += 1
        if normalize_one(
            quiz_path,
            answers_path,
            dry_run=args.dry_run,
            convert_open_to_tf=args.convert_open_to_tf,
            start_id=args.start_id,
            end_id=args.end_id,
        ):
            updated += 1

    mode = "DRY-RUN" if args.dry_run else "WRITE"
    print(f"[{mode}] scanned={scanned} updated={updated}")


if __name__ == "__main__":
    main()
