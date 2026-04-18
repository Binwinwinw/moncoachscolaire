#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import os
import shutil
from pathlib import Path

SCRIPTS_CONFIG = [
    ("anglais_6eme_quizzes", "Anglais"),
    ("francais_6eme_quizzes", "Français"),
    ("mathematiques_6eme_quizzes", "Mathématiques"),
    ("emc_6eme_quizzes", "EMC"),
]

# Resolve repo root from file location so the script works from any cwd
REPO_ROOT = Path(__file__).resolve().parents[4]
SCRIPT_DIR = str(REPO_ROOT / "dev/tools/quiz/generator")
RUNTIME_QUIZ_DIR = str(REPO_ROOT / "src/data/quiz")
RUNTIME_ANSWERS_DIR = str(REPO_ROOT / "src/data/quiz_answers")

print(f"REPO_ROOT: {REPO_ROOT}")
print(f"SCRIPT_DIR: {SCRIPT_DIR}")
print(f"RUNTIME_QUIZ_DIR: {RUNTIME_QUIZ_DIR}\n")

os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)

total_copied = 0

for folder_name, subject in SCRIPTS_CONFIG:
    quiz_folder = os.path.join(SCRIPT_DIR, folder_name, "quiz")
    answers_folder = os.path.join(SCRIPT_DIR, folder_name, "quiz_answers")

    if not os.path.isdir(quiz_folder):
        print(f"⚠ {folder_name}/quiz not found")
        continue

    count = 0
    for filename in os.listdir(quiz_folder):
        if filename.endswith(".json"):
            src_quiz = os.path.join(quiz_folder, filename)
            src_answers = os.path.join(answers_folder, filename)

            # Convert "0145.json" → "145.json"
            base_name = filename[:-5]
            try:
                numeric_id = str(int(base_name))
                dst_filename = numeric_id + ".json"
            except ValueError:
                dst_filename = filename

            dst_quiz = os.path.join(RUNTIME_QUIZ_DIR, dst_filename)
            dst_answers = os.path.join(RUNTIME_ANSWERS_DIR, dst_filename)

            shutil.copy2(src_quiz, dst_quiz)
            if os.path.exists(src_answers):
                shutil.copy2(src_answers, dst_answers)

            count += 1
            total_copied += 1

    print(f"✓ Copied {count}/{len([f for f in os.listdir(quiz_folder) if f.endswith('.json')])} quizzes from {subject}")

print(f"\n✅ Total: {total_copied} quizzes copied")
