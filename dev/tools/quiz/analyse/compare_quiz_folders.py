#!/usr/bin/env python3
"""Comparer les dossiers quiz runtime pour vérifier la cohérence des IDs."""

from __future__ import annotations

import argparse
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[4]
DEFAULT_LEFT = REPO_ROOT / "src" / "data" / "quiz"
DEFAULT_RIGHT = REPO_ROOT / "src" / "data" / "quiz_answers"


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Comparer deux dossiers JSON de quiz.")
    parser.add_argument("--left", default=str(DEFAULT_LEFT), help="Premier dossier")
    parser.add_argument("--right", default=str(DEFAULT_RIGHT), help="Second dossier")
    return parser.parse_args()


def main() -> None:
    args = parse_args()
    left_dir = Path(args.left)
    right_dir = Path(args.right)

    left_files = sorted(p.stem for p in left_dir.glob("*.json")) if left_dir.exists() else []
    right_files = sorted(p.stem for p in right_dir.glob("*.json")) if right_dir.exists() else []

    print(f"[compare] left: {left_dir} -> {len(left_files)} fichiers")
    print(f"[compare] right: {right_dir} -> {len(right_files)} fichiers")

    missing_in_right = sorted(set(left_files) - set(right_files), key=lambda x: int(x) if x.isdigit() else x)
    missing_in_left = sorted(set(right_files) - set(left_files), key=lambda x: int(x) if x.isdigit() else x)

    if not missing_in_right and not missing_in_left:
        print("\n[OK] Les deux dossiers ont les mêmes IDs de quiz")
        return

    print("\n[WARN] Désynchronisation détectée")
    if missing_in_right:
        print(f"  Manquants à droite: {missing_in_right[:100]}")
    if missing_in_left:
        print(f"  Manquants à gauche: {missing_in_left[:100]}")


if __name__ == "__main__":
    main()
