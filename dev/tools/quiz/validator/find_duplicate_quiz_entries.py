#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Détecte les doublons dans les fichiers quiz et quiz_answers.

Usage:
    python find_duplicate_quiz_entries.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers
    python find_duplicate_quiz_entries.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers --report dev/reports/duplicate_quiz_report.md
"""

import argparse
import json
import re
from collections import Counter, defaultdict
from pathlib import Path
from typing import Dict, List, Optional, Tuple


def normalize_text(text: Optional[str]) -> str:
    if text is None:
        return ""
    text = str(text).strip().lower()
    text = re.sub(r"\s+", " ", text)
    return text


def load_json(path: Path) -> Tuple[Optional[Dict], Optional[str]]:
    try:
        with open(path, "r", encoding="utf-8") as f:
            return json.load(f), None
    except Exception as exc:
        return None, str(exc)


def save_json(path: Path, data: Dict):
    with open(path, "w", encoding="utf-8", newline="\n") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
        f.write("\n")


def dedupe_quiz_questions(quiz_data: Dict) -> Tuple[Dict, int]:
    questions = quiz_data.get("quiz", {}).get("questions", [])
    if not isinstance(questions, list):
        return quiz_data, 0

    seen = set()
    cleaned = []
    removed = 0
    for question in questions:
        question_text = normalize_text(question.get("question"))
        if question_text == "":
            cleaned.append(question)
            continue
        if question_text in seen:
            removed += 1
            continue
        seen.add(question_text)
        cleaned.append(question)

    if removed > 0:
        quiz_data["quiz"]["questions"] = cleaned
        if "question_count" in quiz_data["quiz"]:
            quiz_data["quiz"]["question_count"] = len(cleaned)

    return quiz_data, removed


def dedupe_answer_question_ids(answers_data: Dict) -> Tuple[Dict, int]:
    answers = answers_data.get("quiz", {}).get("answers", [])
    if not isinstance(answers, list):
        return answers_data, 0

    seen = set()
    cleaned = []
    removed = 0
    for answer in answers:
        question_id = str(answer.get("question_id") or answer.get("id") or "").strip()
        if question_id == "":
            cleaned.append(answer)
            continue
        if question_id in seen:
            removed += 1
            continue
        seen.add(question_id)
        cleaned.append(answer)

    if removed > 0:
        answers_data["quiz"]["answers"] = cleaned
        if "question_count" in answers_data.get("quiz", {}):
            answers_data["quiz"]["question_count"] = len(cleaned)

    return answers_data, removed


def find_quiz_question_duplicates(quiz_data: Dict) -> List[Tuple[str, List[str]]]:
    questions = quiz_data.get("quiz", {}).get("questions", [])
    if not isinstance(questions, list):
        return []

    normalized = []
    for question in questions:
        question_text = normalize_text(question.get("question"))
        if question_text:
            question_id = str(question.get("id") or "")
            normalized.append((question_text, question_id))

    counts = Counter(text for text, _ in normalized)
    duplicates: List[Tuple[str, List[str]]] = []
    for text, count in counts.items():
        if count > 1:
            ids = [qid for current_text, qid in normalized if current_text == text]
            duplicates.append((text, ids))
    return duplicates


def find_answer_question_id_duplicates(answers_data: Dict) -> List[Tuple[str, int]]:
    answers = answers_data.get("quiz", {}).get("answers", [])
    if not isinstance(answers, list):
        return []

    question_ids = []
    for answer in answers:
        qid = str(answer.get("question_id") or answer.get("id") or "")
        if qid:
            question_ids.append(qid)

    counts = Counter(question_ids)
    duplicates = [(qid, count) for qid, count in counts.items() if count > 1]
    return sorted(duplicates, key=lambda item: (-item[1], item[0]))


def generate_report(quiz_dir: Path, answers_dir: Path, report_path: Optional[Path]):
    quiz_files = sorted(quiz_dir.glob("*.json"))
    answer_files = sorted(answers_dir.glob("*.json"))

    quiz_duplicates = {}
    quiz_parse_errors = {}
    for quiz_file in quiz_files:
        quiz_data, error = load_json(quiz_file)
        if error:
            quiz_parse_errors[quiz_file.stem] = error
            continue
        duplicates = find_quiz_question_duplicates(quiz_data)
        if duplicates:
            quiz_duplicates[quiz_file.stem] = duplicates

    answer_duplicates = {}
    answer_parse_errors = {}
    for answer_file in answer_files:
        answers_data, error = load_json(answer_file)
        if error:
            answer_parse_errors[answer_file.stem] = error
            continue
        duplicates = find_answer_question_id_duplicates(answers_data)
        if duplicates:
            answer_duplicates[answer_file.stem] = duplicates

    report_lines = []
    report_lines.append(f"# Rapport de doublons quiz — {quiz_dir} / {answers_dir}\n")
    report_lines.append(f"- Quiz scannés : {len(quiz_files)}")
    report_lines.append(f"- Fichiers réponse scannés : {len(answer_files)}")
    report_lines.append(f"- Quiz avec doublons de questions : {len(quiz_duplicates)}")
    report_lines.append(f"- Fichiers answers avec doublons de question_id : {len(answer_duplicates)}")
    report_lines.append(f"- Erreurs de parsing quiz : {len(quiz_parse_errors)}")
    report_lines.append(f"- Erreurs de parsing answers : {len(answer_parse_errors)}\n")

    if quiz_parse_errors:
        report_lines.append("## Erreurs de parsing des quiz")
        for quiz_id, error in sorted(quiz_parse_errors.items()):
            report_lines.append(f"- Quiz {quiz_id} : {error}")
        report_lines.append("")

    if answer_parse_errors:
        report_lines.append("## Erreurs de parsing des réponses")
        for answer_id, error in sorted(answer_parse_errors.items()):
            report_lines.append(f"- Answers {answer_id} : {error}")
        report_lines.append("")

    if quiz_duplicates:
        report_lines.append("## Quiz avec doublons de questions")
        for quiz_id, duplicates in sorted(quiz_duplicates.items(), key=lambda x: int(x[0]) if x[0].isdigit() else x[0]):
            report_lines.append(f"### Quiz {quiz_id} ({len(duplicates)} doublons détectés)")
            for text, ids in duplicates:
                report_lines.append(f"- question_text: {text}")
                report_lines.append(f"  - ids: {', '.join(ids)}")
            report_lines.append("")

    if answer_duplicates:
        report_lines.append("## Answers avec doublons de question_id")
        for answer_id, duplicates in sorted(answer_duplicates.items(), key=lambda x: int(x[0]) if x[0].isdigit() else x[0]):
            report_lines.append(f"### Answers {answer_id} ({len(duplicates)} doublons détectés)")
            for qid, count in duplicates:
                report_lines.append(f"- question_id {qid}: {count} occurrences")
            report_lines.append("")

    report = "\n".join(report_lines)
    if report_path:
        report_path.parent.mkdir(parents=True, exist_ok=True)
        with open(report_path, "w", encoding="utf-8") as f:
            f.write(report)
        print(f"[report] Rapport généré: {report_path}")

    return report


def clean_duplicate_entries(quiz_dir: Path, answers_dir: Path, dry_run: bool) -> str:
    quiz_files = sorted(quiz_dir.glob("*.json"))
    answer_files = sorted(answers_dir.glob("*.json"))

    report_lines = []
    report_lines.append(f"# Nettoyage des doublons quiz — {quiz_dir} / {answers_dir}\n")
    report_lines.append(f"- Quiz scannés : {len(quiz_files)}")
    report_lines.append(f"- Fichiers réponse scannés : {len(answer_files)}\n")

    quiz_cleaned = {}
    answers_cleaned = {}
    quiz_parse_errors = {}
    answer_parse_errors = {}
    total_quiz_removed = 0
    total_answer_removed = 0

    for quiz_file in quiz_files:
        quiz_data, error = load_json(quiz_file)
        if error:
            quiz_parse_errors[quiz_file.stem] = error
            continue
        cleaned_data, removed = dedupe_quiz_questions(quiz_data)
        if removed > 0:
            quiz_cleaned[quiz_file.stem] = removed
            total_quiz_removed += removed
            if not dry_run:
                save_json(quiz_file, cleaned_data)

    for answer_file in answer_files:
        answers_data, error = load_json(answer_file)
        if error:
            answer_parse_errors[answer_file.stem] = error
            continue
        cleaned_data, removed = dedupe_answer_question_ids(answers_data)
        if removed > 0:
            answers_cleaned[answer_file.stem] = removed
            total_answer_removed += removed
            if not dry_run:
                save_json(answer_file, cleaned_data)

    report_lines.append(f"- Quiz nettoyés : {len(quiz_cleaned)}")
    report_lines.append(f"- Fichiers answers nettoyés : {len(answers_cleaned)}")
    report_lines.append(f"- Questions supprimées dans quiz : {total_quiz_removed}")
    report_lines.append(f"- Entrées supprimées dans answers : {total_answer_removed}")
    report_lines.append(f"- Mode dry-run : {'oui' if dry_run else 'non'}\n")

    if quiz_parse_errors:
        report_lines.append("## Erreurs de parsing des quiz")
        for quiz_id, error in sorted(quiz_parse_errors.items()):
            report_lines.append(f"- Quiz {quiz_id} : {error}")
        report_lines.append("")

    if answer_parse_errors:
        report_lines.append("## Erreurs de parsing des réponses")
        for answer_id, error in sorted(answer_parse_errors.items()):
            report_lines.append(f"- Answers {answer_id} : {error}")
        report_lines.append("")

    if quiz_cleaned:
        report_lines.append("## Quiz nettoyés")
        for quiz_id, removed in sorted(quiz_cleaned.items(), key=lambda x: int(x[0]) if x[0].isdigit() else x[0]):
            report_lines.append(f"- Quiz {quiz_id} : {removed} doublons supprimés")
        report_lines.append("")

    if answers_cleaned:
        report_lines.append("## Answers nettoyés")
        for answer_id, removed in sorted(answers_cleaned.items(), key=lambda x: int(x[0]) if x[0].isdigit() else x[0]):
            report_lines.append(f"- Answers {answer_id} : {removed} doublons supprimés")
        report_lines.append("")

    report = "\n".join(report_lines)
    return report


def clean_duplicate_entries(quiz_dir: Path, answers_dir: Path, dry_run: bool) -> str:
    quiz_files = sorted(quiz_dir.glob("*.json"))
    answer_files = sorted(answers_dir.glob("*.json"))

    report_lines = []
    report_lines.append(f"# Nettoyage des doublons quiz — {quiz_dir} / {answers_dir}\n")
    report_lines.append(f"- Quiz scannés : {len(quiz_files)}")
    report_lines.append(f"- Fichiers réponse scannés : {len(answer_files)}\n")

    quiz_cleaned = {}
    answers_cleaned = {}
    quiz_parse_errors = {}
    answer_parse_errors = {}
    total_quiz_removed = 0
    total_answer_removed = 0

    for quiz_file in quiz_files:
        quiz_data, error = load_json(quiz_file)
        if error:
            quiz_parse_errors[quiz_file.stem] = error
            continue
        cleaned_data, removed = dedupe_quiz_questions(quiz_data)
        if removed > 0:
            quiz_cleaned[quiz_file.stem] = removed
            total_quiz_removed += removed
            if not dry_run:
                save_json(quiz_file, cleaned_data)

    for answer_file in answer_files:
        answers_data, error = load_json(answer_file)
        if error:
            answer_parse_errors[answer_file.stem] = error
            continue
        cleaned_data, removed = dedupe_answer_question_ids(answers_data)
        if removed > 0:
            answers_cleaned[answer_file.stem] = removed
            total_answer_removed += removed
            if not dry_run:
                save_json(answer_file, cleaned_data)

    report_lines.append(f"- Quiz nettoyés : {len(quiz_cleaned)}")
    report_lines.append(f"- Fichiers answers nettoyés : {len(answers_cleaned)}")
    report_lines.append(f"- Questions supprimées dans quiz : {total_quiz_removed}")
    report_lines.append(f"- Entrées supprimées dans answers : {total_answer_removed}")
    report_lines.append(f"- Mode dry-run : {'oui' if dry_run else 'non'}\n")

    if quiz_parse_errors:
        report_lines.append("## Erreurs de parsing des quiz")
        for quiz_id, error in sorted(quiz_parse_errors.items()):
            report_lines.append(f"- Quiz {quiz_id} : {error}")
        report_lines.append("")

    if answer_parse_errors:
        report_lines.append("## Erreurs de parsing des réponses")
        for answer_id, error in sorted(answer_parse_errors.items()):
            report_lines.append(f"- Answers {answer_id} : {error}")
        report_lines.append("")

    if quiz_cleaned:
        report_lines.append("## Quiz nettoyés")
        for quiz_id, removed in sorted(quiz_cleaned.items(), key=lambda x: int(x[0]) if x[0].isdigit() else x[0]):
            report_lines.append(f"- Quiz {quiz_id} : {removed} doublons supprimés")
        report_lines.append("")

    if answers_cleaned:
        report_lines.append("## Answers nettoyés")
        for answer_id, removed in sorted(answers_cleaned.items(), key=lambda x: int(x[0]) if x[0].isdigit() else x[0]):
            report_lines.append(f"- Answers {answer_id} : {removed} doublons supprimés")
        report_lines.append("")

    report = "\n".join(report_lines)
    return report


def main():
    parser = argparse.ArgumentParser(description='Détection de doublons dans les quiz et quiz_answers')
    parser.add_argument('--quiz-dir', type=str, default='src/data/quiz', help='Répertoire des quiz')
    parser.add_argument('--answers-dir', type=str, default='src/data/quiz_answers', help='Répertoire des réponses')
    parser.add_argument('--report', type=str, default=None, help='Chemin du rapport Markdown')
    parser.add_argument('--clean', action='store_true', help='Supprimer automatiquement les doublons en gardant la première occurrence')
    parser.add_argument('--dry-run', action='store_true', help='Afficher ce qui serait supprimé sans modifier les fichiers')
    args = parser.parse_args()

    quiz_dir = Path(args.quiz_dir)
    answers_dir = Path(args.answers_dir)

    if not quiz_dir.exists() or not quiz_dir.is_dir():
        print(f"[error] Répertoire quiz introuvable: {quiz_dir}")
        return 1
    if not answers_dir.exists() or not answers_dir.is_dir():
        print(f"[error] Répertoire answers introuvable: {answers_dir}")
        return 1

    print(f"[duplicates] Quiz dir: {quiz_dir}")
    print(f"[duplicates] Answers dir: {answers_dir}\n")

    if args.clean:
        cleaned_report = clean_duplicate_entries(quiz_dir, answers_dir, args.dry_run)
        if args.report:
            report_path = Path(args.report)
            report_path.parent.mkdir(parents=True, exist_ok=True)
            with open(report_path, 'w', encoding='utf-8') as f:
                f.write(cleaned_report)
            print(f"[report] Rapport de nettoyage généré: {report_path}")
        else:
            print(cleaned_report)
        return 0

    report = generate_report(quiz_dir, answers_dir, Path(args.report) if args.report else None)

    if not args.report:
        print(report)

    return 0


if __name__ == '__main__':
    exit(main())
