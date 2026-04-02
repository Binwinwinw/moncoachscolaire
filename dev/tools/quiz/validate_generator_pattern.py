#!/usr/bin/env python3
"""Validate generate_*.py scripts against MonCoachScolaire pattern checks.

Checks:
1) datetime import uses UTC + datetime
2) SCRIPT_DIR and path constants are script-relative
3) make_quiz strips correction fields from public payload
4) make_answers includes qcm + vrai-faux + texte dispatch
5) no usage of vrai_faux (underscore)
6) if IDs range is declared, quizzes_data length matches expected
7) write_quiz_files() and __name__ guard are present
"""

from __future__ import annotations

import argparse
import ast
import re
from dataclasses import dataclass
from pathlib import Path
from typing import List


RANGE_REGEX = re.compile(r"IDs?\s*(\d+)\s*[\-–]\s*(\d+)", re.IGNORECASE)


@dataclass
class CheckResult:
    name: str
    ok: bool
    detail: str


@dataclass
class FileReport:
    path: Path
    checks: List[CheckResult]

    @property
    def ok(self) -> bool:
        return all(c.ok for c in self.checks)


def read_text(path: Path) -> str:
    # utf-8-sig keeps compatibility with legacy files saved with BOM.
    return path.read_text(encoding="utf-8-sig", errors="replace")


def count_quizzes_data_items(tree: ast.AST) -> int | None:
    for node in ast.walk(tree):
        if isinstance(node, ast.Assign):
            for target in node.targets:
                if isinstance(target, ast.Name) and target.id == "quizzes_data":
                    if isinstance(node.value, (ast.List, ast.Tuple)):
                        return len(node.value.elts)
    return None


def has_function(tree: ast.AST, name: str) -> bool:
    return any(isinstance(node, ast.FunctionDef) and node.name == name for node in ast.walk(tree))


def function_source(tree: ast.AST, text: str, name: str) -> str:
    lines = text.splitlines()
    for node in ast.walk(tree):
        if isinstance(node, ast.FunctionDef) and node.name == name:
            start = max(0, node.lineno - 1)
            end = node.end_lineno if node.end_lineno is not None else node.lineno
            return "\n".join(lines[start:end])
    return ""


def has_main_guard(text: str) -> bool:
    return "if __name__ == \"__main__\":" in text or "if __name__ == '__main__':" in text


def check_file(path: Path) -> FileReport:
    text = read_text(path)
    try:
        tree = ast.parse(text)
    except SyntaxError as exc:
        return FileReport(
            path=path,
            checks=[CheckResult("python-syntax", False, f"syntax error: {exc}")],
        )

    checks: List[CheckResult] = []

    # 1) datetime import modern
    import_ok = "from datetime import UTC, datetime" in text
    checks.append(CheckResult("datetime-import", import_ok, "must contain: from datetime import UTC, datetime"))

    # 2) script-relative paths
    path_ok = all(
        snippet in text
        for snippet in (
            "SCRIPT_DIR",
            "os.path.dirname(os.path.abspath(__file__))",
            "os.path.join",
            '"quiz"',
            '"quiz_answers"',
        )
    )
    checks.append(CheckResult("script-relative-paths", path_ok, "must define SCRIPT_DIR + join(..., 'quiz') + join(..., 'quiz_answers')"))

    # 3) make_quiz strips correction fields
    make_quiz_exists = has_function(tree, "make_quiz")
    make_quiz_src = function_source(tree, text, "make_quiz")
    strips_ok = (
        re.search(r"if\s+\w+\s+not\s+in\s+", make_quiz_src) is not None
        and "correct_option" in make_quiz_src
        and "correct_answer" in make_quiz_src
        and "explanation" in make_quiz_src
    )
    checks.append(CheckResult("make-quiz-strip-corrections", make_quiz_exists and strips_ok, "make_quiz should drop correction keys"))

    # 4) make_answers dispatch
    make_answers_exists = has_function(tree, "make_answers")
    make_answers_src = function_source(tree, text, "make_answers")
    dispatch_ok = (
        "== \"qcm\"" in make_answers_src
        and "== \"vrai-faux\"" in make_answers_src
        and "correct_answer" in make_answers_src
    )
    checks.append(CheckResult("make-answers-dispatch", make_answers_exists and dispatch_ok, "make_answers should handle qcm / vrai-faux / texte"))

    # 5) no vrai_faux underscore
    vf_ok = "vrai_faux" not in text
    checks.append(CheckResult("vrai-faux-format", vf_ok, "must use 'vrai-faux', never 'vrai_faux'"))

    # 6) IDs range length consistency
    quizzes_count = count_quizzes_data_items(tree)
    match = RANGE_REGEX.search(text)
    if match and quizzes_count is not None:
        start, end = int(match.group(1)), int(match.group(2))
        expected = end - start + 1
        range_ok = quizzes_count == expected
        detail = f"expected quizzes_data len={expected}, got={quizzes_count}"
        checks.append(CheckResult("ids-range-count", range_ok, detail))
    else:
        checks.append(CheckResult("ids-range-count", True, "skipped (no parseable IDs range or dynamic quizzes_data)"))

    # 7) write pipeline and main guard
    write_ok = has_function(tree, "write_quiz_files") and has_main_guard(text)
    checks.append(CheckResult("write-pipeline", write_ok, "requires write_quiz_files() and __main__ guard"))

    return FileReport(path=path, checks=checks)


def collect_targets(folder: Path, glob_expr: str) -> List[Path]:
    excluded = {
        "generate_quiz_bank.py",
        "generate_quiz_packs.py",
            "generate_0template.py",
        "generate_quiz_bank copy.py",
    }
    return sorted(
        [
            path
            for path in folder.glob(glob_expr)
            if path.is_file() and path.name.startswith("generate_") and path.name not in excluded
        ]
    )


def main() -> int:
    parser = argparse.ArgumentParser(description="Validate generator scripts pattern compliance")
    parser.add_argument("--folder", default="dev/tools/quiz", help="Folder that contains generator scripts")
    parser.add_argument("--glob", default="generate_*.py", help="Glob used inside folder")
    parser.add_argument("--file", default="", help="Validate one file only")
    args = parser.parse_args()

    repo_root = Path(__file__).resolve().parents[3]

    if args.file:
        targets = [repo_root / args.file]
    else:
        targets = collect_targets(repo_root / args.folder, args.glob)

    if not targets:
        print("[pattern] files: 0")
        print("[pattern] no target matched")
        return 0

    reports = [check_file(path) for path in targets]

    failures = 0
    for report in reports:
        status = "OK" if report.ok else "FAIL"
        print(f"[pattern] {status} {report.path.relative_to(repo_root)}")
        for check in report.checks:
            marker = "ok" if check.ok else "xx"
            print(f"  - [{marker}] {check.name}: {check.detail}")
            if not check.ok:
                failures += 1

    print(f"[pattern] files: {len(reports)}")
    print(f"[pattern] failed_checks: {failures}")

    return 1 if failures else 0


if __name__ == "__main__":
    raise SystemExit(main())
