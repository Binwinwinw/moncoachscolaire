#!/usr/bin/env python3
"""Convertit des JSON diagnostic source en fichiers runtime quiz + quiz_answers.

Modes:
- fichier unique: --input + --quiz-out + --answers-out
- dossier (batch): --input-dir + --quiz-out-dir + --answers-out-dir + --start-id + --end-id

Objectif: compatibilite directe avec src/api/quiz.php et src/api/diagnostic/submit.php.
"""

from __future__ import annotations

import argparse
import json
from pathlib import Path
from typing import Any, Dict, List, Tuple


def load_json(path: Path) -> Dict[str, Any]:
    with path.open("r", encoding="utf-8") as handle:
        data = json.load(handle)
    if not isinstance(data, dict):
        raise ValueError("Le fichier source doit contenir un objet JSON racine")
    return data


def _get_meta(source: Dict[str, Any]) -> Tuple[Dict[str, Any], Dict[str, Any]]:
    contents = source.get("contents")
    quiz = source.get("quiz")

    if not isinstance(contents, dict):
        raise ValueError("Champ obligatoire manquant: contents")
    if not isinstance(quiz, dict):
        raise ValueError("Champ obligatoire manquant: quiz")

    return contents, quiz


def _get_questions(quiz: Dict[str, Any]) -> List[Dict[str, Any]]:
    questions = quiz.get("questions")
    if not isinstance(questions, list) or not questions:
        raise ValueError("Champ obligatoire manquant ou vide: quiz.questions")

    out: List[Dict[str, Any]] = []
    for index, question in enumerate(questions, start=1):
        if not isinstance(question, dict):
            raise ValueError(f"quiz.questions[{index}] doit etre un objet")
        out.append(question)
    return out


def normalize_question_type(raw_type: Any) -> str:
    qtype = str(raw_type or "texte").strip().lower().replace("_", "-")
    if qtype in {"texte", "text", "open"}:
        return "open"
    if qtype == "vrai-faux":
        return "vrai-faux"
    return "qcm" if qtype == "qcm" else "open"


def resolve_qcm_answer(question: Dict[str, Any]) -> Any:
    options = list(question.get("options") or question.get("choices") or [])
    raw_answer = question.get("answer", question.get("correct_option", question.get("correct_answer", "")))

    if isinstance(raw_answer, str):
        raw_answer = raw_answer.strip()
        letter_map = {"A": 0, "B": 1, "C": 2, "D": 3}
        if raw_answer.upper() in letter_map:
            option_index = letter_map[raw_answer.upper()]
            if option_index < len(options):
                return options[option_index]

    return raw_answer


def resolve_true_false_answer(question: Dict[str, Any]) -> str:
    raw_value = question.get("answer", question.get("correct", question.get("correct_answer", False)))
    if isinstance(raw_value, str):
        return "vrai" if raw_value.strip().lower() in {"true", "vrai", "1"} else "faux"
    return "vrai" if bool(raw_value) else "faux"


def build_runtime_payloads(source: Dict[str, Any]) -> Tuple[Dict[str, Any], Dict[str, Any], List[str]]:
    contents, quiz = _get_meta(source)
    questions = _get_questions(quiz)

    title = str(quiz.get("title") or contents.get("title") or "Diagnostic")
    level = str(quiz.get("level") or contents.get("level") or "")
    subject = str(quiz.get("subject") or contents.get("subject") or "")
    passing_score = int(quiz.get("passing_score") or 70)
    time_limit_minutes = int(quiz.get("time_limit_minutes") or 15)

    quiz_questions: List[Dict[str, Any]] = []
    answers_rows: List[Dict[str, Any]] = []
    warnings: List[str] = []
    forbidden_keys = {"answer", "correction", "correct", "correct_option", "correct_answer", "explanation", "placeholder"}

    for idx, q in enumerate(questions):
        qid = q.get("id", idx + 1)
        qtype = normalize_question_type(q.get("type", "texte"))
        question_text = str(q.get("question", ""))

        if not question_text:
            warnings.append(f"Q{idx + 1}: champ question manquant")

        clean_q = {k: v for k, v in q.items() if k not in forbidden_keys}
        clean_q["type"] = qtype
        clean_q["question"] = question_text

        if qtype == "qcm":
            clean_q["choices"] = list(q.get("choices") or q.get("options") or [])
            if not clean_q["choices"]:
                warnings.append(f"Q{idx + 1}: choices manquants pour un QCM")
            answer_value = resolve_qcm_answer(q)
        elif qtype == "vrai-faux":
            clean_q.pop("choices", None)
            answer_value = resolve_true_false_answer(q)
        else:
            clean_q.pop("choices", None)
            answer_value = q.get("answer", q.get("correct_answer", ""))

        quiz_questions.append(clean_q)

        correction = q.get("correction", q.get("explanation", ""))
        if answer_value in (None, ""):
            warnings.append(f"Q{idx + 1}: réponse attendue manquante")
        if correction == "":
            warnings.append(f"Q{idx + 1}: correction manquante")

        answers_rows.append(
            {
                "index": idx,
                "question_id": idx + 1,
                "type": qtype,
                "answer": answer_value,
                "correction": correction,
            }
        )

    quiz_out = {
        "contents": {
            "title": str(contents.get("title") or title),
            "type": "quiz",
            "level": level,
            "subject": subject,
            "description": str(contents.get("description") or ""),
            "status": str(contents.get("status") or "published"),
        },
        "quiz": {
            "title": title,
            "type": "quiz",
            "level": level,
            "subject": subject,
            "question_count": len(quiz_questions),
            "passing_score": passing_score,
            "time_limit_minutes": time_limit_minutes,
            "questions": quiz_questions,
        },
        "exercisenotion": source.get("exercisenotion", []),
        "exerciseresponses": [],
    }

    answers_out = {
        "contents": {
            "title": str(contents.get("title") or title),
            "level": level,
            "subject": subject,
        },
        "quiz": {
            "title": title,
            "question_count": len(answers_rows)hoices", None)
            answer_value = q.get("answer", q.get("correct_answer", ""))

        quiz_questions.append(clean_q)

        correction = q.get("correction", q.get("explanation", ""))
        if answer_value in (None, ""):
            warnings.append(f"Q{idx + 1}: réponse attendue manquante")
        if correction == "":
            warnings.append(f"Q{idx + 1}: correction manquante")

        answers_rows.append(
            {
                "index": idx,
                "question_id": idx + 1,
                "type": qtype,
                "answer": answer_value,
                "correction": correction,
            }
        )

    quiz_out = {
        "contents": {
            "title": str(contents.get("title") or title),
            "type": "quiz",
            "level": level,
            "subject": subject,
            "description": str(contents.get("description") or ""),
            "status": str(contents.get("status") or "published"),
        },
        "quiz": {
            "title": title,
            "type": "quiz",
            "level": level,
            "subject": subject,
            "question_count": len(quiz_questions),
            "passing_score": passing_score,
            "time_limit_minutes": time_limit_minutes,
            "questions": quiz_questions,
        },
        "exercisenotion": source.get("exercisenotion", []),
        "exerciseresponses": [],
    }

    answers_out = {
        "contents": {
            "title": str(contents.get("title") or title),
            "level": level,
            "subject": subject,
        },
        "quiz": {
            "title": title,
            "question_count": len(answers_rows),
            "level": level,
            "subject": subject,
            "answers": answers_rows,
        },
    }

    return quiz_out, answers_out, warnings


def write_json(path: Path, payload: Dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8") as handle:
        json.dump(payload, handle, ensure_ascii=False, indent=2)
        handle.write("\n")


def convert_one_file(input_path: Path, quiz_out_path: Path, answers_out_path: Path) -> List[str]:
    source = load_json(input_path)
    quiz_payload, answers_payload, warnings = build_runtime_payloads(source)
    write_json(quiz_out_path, quiz_payload)
    write_json(answers_out_path, answers_payload)
    return warnings


def convert_directory_with_range(
    input_dir: Path,
    quiz_out_dir: Path,
    answers_out_dir: Path,
    start_id: int,
    end_id: int,
) -> int:
    if not input_dir.is_dir():
        print(f"ERREUR: dossier introuvable: {input_dir}")
        return 1

    if start_id <= 0 or end_id <= 0 or end_id < start_id:
        print("ERREUR: plage d'IDs invalide (attendu: start_id <= end_id et > 0)")
        return 1

    input_files = sorted([p for p in input_dir.glob("*.json") if p.is_file()])
    expected_count = end_id - start_id + 1

    if len(input_files) != expected_count:
        print(
            "ERREUR: le nombre de fichiers source ne correspond pas a la plage d'IDs. "
            f"Fichiers={len(input_files)}, plage={expected_count} ({start_id}-{end_id})"
        )
        return 1

    all_warnings = 0

    for offset, src_path in enumerate(input_files):
        quiz_id = start_id + offset
        quiz_out_path = quiz_out_dir / f"{quiz_id}.json"
        answers_out_path = answers_out_dir / f"{quiz_id}.json"

        try:
            warnings = convert_one_file(src_path, quiz_out_path, answers_out_path)
        except Exception as exc:
            print(f"ERREUR: conversion echouee pour {src_path.name}: {exc}")
            return 1

        print(f"OK: {src_path.name} -> quiz/{quiz_id}.json + quiz_answers/{quiz_id}.json")

        if warnings:
            all_warnings += len(warnings)
            print("AVERTISSEMENTS:")
            for warning in warnings:
                print(f"- {src_path.name}: {warning}")

    print(
        "TERMINE: conversion batch reussie. "
        f"{len(input_files)} sources -> {len(input_files) * 2} fichiers generes"
    )
    print(f"Sortie quiz: {quiz_out_dir}")
    print(f"Sortie quiz_answers: {answers_out_dir}")
    print(f"Total avertissements: {all_warnings}")

    return 0


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description=(
            "Extrait des JSON diagnostic source en 2 fichiers runtime: "
            "quiz (sans reponses) et quiz_answers (avec reponses/corrections)."
        )
    )

    mode_group = parser.add_mutually_exclusive_group(required=True)
    mode_group.add_argument("--input", help="Chemin du JSON source (mode fichier)")
    mode_group.add_argument("--input-dir", help="Dossier source contenant des JSON (mode batch)")

    parser.add_argument("--quiz-out", help="Chemin de sortie du fichier quiz (mode fichier)")
    parser.add_argument("--answers-out", help="Chemin de sortie du fichier quiz_answers (mode fichier)")

    parser.add_argument("--quiz-out-dir", help="Dossier de sortie quiz (mode batch)")
    parser.add_argument("--answers-out-dir", help="Dossier de sortie quiz_answers (mode batch)")
    parser.add_argument("--start-id", type=int, help="ID de debut (mode batch)")
    parser.add_argument("--end-id", type=int, help="ID de fin (mode batch)")

    return parser.parse_args()


def main() -> int:
    args = parse_args()

    if args.input:
        if not args.quiz_out or not args.answers_out:
            print("ERREUR: mode fichier exige --quiz-out et --answers-out")
            return 1

        input_path = Path(args.input)
        quiz_out_path = Path(args.quiz_out)
        answers_out_path = Path(args.answers_out)

        if not input_path.is_file():
            print(f"ERREUR: fichier introuvable: {input_path}")
            return 1

        try:
            warnings = convert_one_file(input_path, quiz_out_path, answers_out_path)
        except Exception as exc:
            print(f"ERREUR: {exc}")
            return 1

        print(f"OK: quiz genere -> {quiz_out_path}")
        print(f"OK: answers genere -> {answers_out_path}")

        if warnings:
            print("AVERTISSEMENTS:")
            for warning in warnings:
                print(f"- {warning}")

        return 0

    if args.input_dir:
        missing = []
        if not args.quiz_out_dir:
            missing.append("--quiz-out-dir")
        if not args.answers_out_dir:
            missing.append("--answers-out-dir")
        if args.start_id is None:
            missing.append("--start-id")
        if args.end_id is None:
            missing.append("--end-id")

        if missing:
            print("ERREUR: mode batch exige " + ", ".join(missing))
            return 1

        return convert_directory_with_range(
            input_dir=Path(args.input_dir),
            quiz_out_dir=Path(args.quiz_out_dir),
            answers_out_dir=Path(args.answers_out_dir),
            start_id=args.start_id,
            end_id=args.end_id,
        )

    print("ERREUR: aucun mode selectionne")
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
