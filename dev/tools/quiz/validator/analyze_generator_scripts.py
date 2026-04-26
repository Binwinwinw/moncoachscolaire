#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Analyse les scripts generate_<niveau>_<matiere>.py pour détecter les défauts de quiz.

Ce script extrait la variable quizzes_data des fichiers Python et vérifie :
- structure quiz / question attendue
- 8 questions par quiz
- type valide qcm/vrai-faux
- présence de champs requis
- cohérence des options et de la réponse correcte
- doublons d'ids, de questions, d'options
- formats de correction plausibles

Usage:
    python analyze_generator_scripts.py --files dev/tools/quiz/enrichment/generator/generate_6eme_anglais0001-0048.py ...
    python analyze_generator_scripts.py --folder dev/tools/quiz/enrichment/generator --glob 'generate_6eme_*.py'
"""

from __future__ import annotations

import argparse
import ast
import json
import re
from collections import Counter, defaultdict
from dataclasses import dataclass
from pathlib import Path
from typing import Any, Iterable, List, Optional, Sequence

RANGE_REGEX = re.compile(r"IDs?\s*(\d+)\s*[\-–]\s*(\d+)", re.IGNORECASE)
VALID_TYPES = {"qcm", "vrai-faux"}


@dataclass
class Issue:
    level: str  # error, warn, info
    scope: str  # file, quiz, question
    location: str
    message: str


@dataclass
class QuizReport:
    quiz_id: str
    issues: List[Issue]

    @property
    def errors(self) -> List[Issue]:
        return [i for i in self.issues if i.level == "error"]

    @property
    def warnings(self) -> List[Issue]:
        return [i for i in self.issues if i.level == "warn"]


@dataclass
class FileReport:
    path: Path
    parse_error: Optional[str]
    syntax_error: Optional[str]
    quizzes: List[QuizReport]
    issues: List[Issue]

    @property
    def ok(self) -> bool:
        return self.parse_error is None and self.syntax_error is None and not any(i.level == "error" for i in self.issues)


def read_text(path: Path) -> str:
    return path.read_text(encoding="utf-8-sig", errors="replace")


def find_quizzes_data_node(tree: ast.AST) -> Optional[ast.AST]:
    for node in ast.walk(tree):
        if isinstance(node, ast.Assign):
            for target in node.targets:
                if isinstance(target, ast.Name) and target.id == "quizzes_data":
                    return node.value
    return None


def literal_eval_node(node: ast.AST) -> Any:
    return ast.literal_eval(node)


def extract_quizzes_data(path: Path) -> tuple[Optional[list], Optional[str], Optional[str]]:
    text = read_text(path)
    try:
        tree = ast.parse(text, filename=str(path))
    except SyntaxError as exc:
        return None, None, f"Syntax error: {exc}"

    node = find_quizzes_data_node(tree)
    if node is None:
        return None, "No quizzes_data assignment found", None

    try:
        quizzes_data = literal_eval_node(node)
    except Exception as exc:
        return None, f"Could not literal_eval quizzes_data: {exc}", None

    if not isinstance(quizzes_data, (list, tuple)):
        return None, f"quizzes_data is not a list/tuple, got {type(quizzes_data).__name__}", None

    return list(quizzes_data), None, None


def check_quiz_block(file_path: Path, quiz_block: Any) -> QuizReport:
    issues: List[Issue] = []
    if not isinstance(quiz_block, (list, tuple)) or len(quiz_block) != 5:
        issues.append(Issue("error", "quiz", "quiz_block", f"Expected quiz tuple/list of 5 elements, got {type(quiz_block).__name__} length {len(quiz_block) if isinstance(quiz_block, (list, tuple)) else 'N/A'}"))
        return QuizReport(str(quiz_block[0] if isinstance(quiz_block, (list, tuple)) and quiz_block else "?"), issues)

    quiz_id, title, subject, level, questions = quiz_block
    quiz_id = str(quiz_id)
    if not quiz_id.isdigit() or len(quiz_id) != 4:
        issues.append(Issue("warn", "quiz", quiz_id, f"Quiz id should be a 4-digit string, got {repr(quiz_id)}"))
    if not isinstance(title, str) or not title.strip():
        issues.append(Issue("error", "quiz", quiz_id, "Empty or invalid title"))
    if not isinstance(subject, str) or not subject.strip():
        issues.append(Issue("warn", "quiz", quiz_id, "Empty or invalid subject"))
    if not isinstance(level, str) or not level.strip():
        issues.append(Issue("warn", "quiz", quiz_id, "Empty or invalid level"))
    if not isinstance(questions, (list, tuple)):
        issues.append(Issue("error", "quiz", quiz_id, "Questions is not a list/tuple"))
        return QuizReport(quiz_id, issues)

    if len(questions) != 8:
        issues.append(Issue("error", "quiz", quiz_id, f"Expected 8 questions, got {len(questions)}"))

    seen_question_ids: set[str] = set()
    question_texts: Counter[str] = Counter()

    for idx, question in enumerate(questions, start=1):
        location = f"question {idx}"
        if not isinstance(question, dict):
            issues.append(Issue("error", "question", f"{quiz_id}:{location}", f"Question entry is not a dict, got {type(question).__name__}"))
            continue

        qid = question.get("id")
        qtype = str(question.get("type", "")).strip()
        qtext = question.get("question")
        if not isinstance(qid, str) or not qid.strip():
            issues.append(Issue("error", "question", f"{quiz_id}:{location}", "Missing or invalid question id"))
        elif qid in seen_question_ids:
            issues.append(Issue("error", "question", f"{quiz_id}:{qid}", "Duplicate question id"))
        else:
            seen_question_ids.add(qid)

        if isinstance(qtext, str):
            if not qtext.strip():
                issues.append(Issue("error", "question", f"{quiz_id}:{qid}", "Empty question text"))
            elif len(qtext.strip()) < 15:
                issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", f"Question text is very short ({len(qtext.strip())} chars)"))
            question_texts[qtext.strip()] += 1
        else:
            issues.append(Issue("error", "question", f"{quiz_id}:{qid}", "Missing or invalid question text"))

        if qtype not in VALID_TYPES:
            issues.append(Issue("error", "question", f"{quiz_id}:{qid}", f"Invalid type {repr(qtype)}; expected one of {sorted(VALID_TYPES)}"))
            continue

        if qtype == "qcm":
            options = question.get("options")
            correct_option = question.get("correct_option")
            if not isinstance(options, (list, tuple)) or len(options) < 2:
                issues.append(Issue("error", "question", f"{quiz_id}:{qid}", "qcm requires an options list of at least 2 items"))
            else:
                if len(set(options)) != len(options):
                    issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", "Duplicate choices in qcm options"))
                for opt in options:
                    if not isinstance(opt, str) or not opt.strip():
                        issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", f"qcm option invalid: {repr(opt)}"))
            if correct_option is None:
                issues.append(Issue("error", "question", f"{quiz_id}:{qid}", "qcm missing correct_option field"))
            elif options and correct_option not in options:
                issues.append(Issue("error", "question", f"{quiz_id}:{qid}", f"correct_option {repr(correct_option)} not in options"))
            if "correct" in question:
                issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", "qcm should not include a boolean field 'correct'"))
            if "correct_answer" in question:
                issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", "qcm should use correct_option instead of correct_answer"))

        if qtype == "vrai-faux":
            if "correct" not in question:
                issues.append(Issue("error", "question", f"{quiz_id}:{qid}", "vrai-faux missing correct boolean"))
            elif not isinstance(question.get("correct"), bool):
                issues.append(Issue("error", "question", f"{quiz_id}:{qid}", "vrai-faux correct value must be a boolean"))
            if "correct_option" in question:
                issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", "vrai-faux should not include correct_option"))
            if "options" in question:
                issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", "vrai-faux should not include options"))

        if qtype not in VALID_TYPES:
            continue

        if qid and isinstance(qid, str):
            if not qid.startswith(f"{quiz_id}_"):
                issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", f"Question id prefix does not match quiz id {quiz_id}"))

        explanation = question.get("explanation")
        if explanation is None or not isinstance(explanation, str) or not explanation.strip():
            issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", "Missing or empty explanation"))
        elif len(explanation.strip()) < 20:
            issues.append(Issue("warn", "question", f"{quiz_id}:{qid}", "Explanation is very short"))

    for text, count in question_texts.items():
        if count > 1:
            issues.append(Issue("warn", "quiz", quiz_id, f"Repeated question text {count} times"))

    return QuizReport(quiz_id, issues)


def check_file(path: Path) -> FileReport:
    quizzes_data, parse_error, syntax_error = extract_quizzes_data(path)
    issues: List[Issue] = []
    quizzes: List[QuizReport] = []

    if syntax_error:
        return FileReport(path, None, syntax_error, quizzes, [Issue("error", "file", "syntax", syntax_error)])
    if parse_error:
        return FileReport(path, parse_error, None, quizzes, [Issue("error", "file", "parse", parse_error)])

    ids_found: set[str] = set()
    for quiz_block in quizzes_data:
        report = check_quiz_block(path, quiz_block)
        quizzes.append(report)
        ids_found.add(report.quiz_id)

    if not quizzes_data:
        issues.append(Issue("error", "file", "quizzes_data", "No quiz blocks found"))

    text = read_text(path)
    if match := RANGE_REGEX.search(text):
        start, end = int(match.group(1)), int(match.group(2))
        expected = end - start + 1
        actual = len(quizzes_data)
        if actual != expected:
            issues.append(Issue("error", "file", "ids-range", f"IDs range says {start}-{end} => {expected} quizzes, but found {actual}"))

    if len(ids_found) != len(quizzes_data):
        issues.append(Issue("error", "file", "duplicate-quiz-ids", "Duplicate quiz ids found"))

    quiz_ids = [quiz.quiz_id for quiz in quizzes]
    if len(quiz_ids) != len(set(quiz_ids)):
        issues.append(Issue("error", "file", "duplicate-quiz-ids", "Repeated quiz ids in quizzes_data"))

    return FileReport(path, None, None, quizzes, issues)


def collect_targets(folder: Path, glob_expr: str) -> list[Path]:
    return sorted([path for path in folder.glob(glob_expr) if path.is_file()])


def main() -> int:
    parser = argparse.ArgumentParser(description="Analyse les scripts de génération de quiz.")
    parser.add_argument("--folder", default="dev/tools/quiz/enrichment/generator", help="Dossier contenant les scripts générateurs")
    parser.add_argument("--glob", default="generate_*.py", help="Motif de fichiers à analyser")
    parser.add_argument("--file", default="", help="Analyse un fichier unique")
    parser.add_argument("--report", default="", help="Chemin de rapport JSON à écrire")
    args = parser.parse_args()

    repo_root = Path(__file__).resolve().parents[4]
    if args.file:
        targets = [Path(args.file) if Path(args.file).is_absolute() else repo_root / args.file]
    else:
        targets = collect_targets(repo_root / args.folder, args.glob)

    if not targets:
        print("No files matched")
        return 1

    reports = [check_file(path) for path in targets]
    total_issues = 0
    total_errors = 0
    total_warnings = 0

    for report in reports:
        file_errors = [i for i in report.issues if i.level == "error"]
        file_warnings = [i for i in report.issues if i.level == "warn"]
        total_errors += len(file_errors)
        total_warnings += len(file_warnings)
        total_issues += len(report.issues)
        print(f"FILE: {report.path.relative_to(repo_root)}")
        if report.syntax_error or report.parse_error:
            print(f"  ! {report.syntax_error or report.parse_error}")
            continue
        print(f"  quizzes: {len(report.quizzes)}")
        if report.issues:
            for issue in report.issues:
                print(f"  [FILE-{issue.level.upper()}] {issue.location}: {issue.message}")
        for quiz in report.quizzes:
            if quiz.issues:
                print(f"  QUIZ {quiz.quiz_id}: {len(quiz.errors)} errors, {len(quiz.warnings)} warnings")
                for issue in quiz.issues:
                    print(f"    [{issue.level}] {issue.location}: {issue.message}")
        print("")

    print(f"SUMMARY: files={len(reports)}, issues={total_issues}, errors={total_errors}, warnings={total_warnings}")
    if args.report:
        result = {
            "files": [
                {
                    "path": str(report.path.relative_to(repo_root)),
                    "syntax_error": report.syntax_error,
                    "parse_error": report.parse_error,
                    "quizzes": [
                        {
                            "quiz_id": quiz.quiz_id,
                            "errors": [i.message for i in quiz.errors],
                            "warnings": [i.message for i in quiz.warnings],
                        }
                        for quiz in report.quizzes
                    ],
                    "issues": [
                        {"level": i.level, "location": i.location, "message": i.message}
                        for i in report.issues
                    ],
                }
                for report in reports
            ],
            "summary": {
                "files": len(reports),
                "issues": total_issues,
                "errors": total_errors,
                "warnings": total_warnings,
            },
        }
        Path(args.report).write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8")
        print(f"Report written to {args.report}")

    return 1 if total_errors else 0


if __name__ == "__main__":
    raise SystemExit(main())
