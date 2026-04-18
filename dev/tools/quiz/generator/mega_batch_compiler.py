#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
MEGA BATCH COMPILER - Tous les scripts generate_*.py
Gère: encodage UTF-8, mapping niveau/matière, deduplication, error tracking
"""
import os
import sys
import json
import shutil
import subprocess
from pathlib import Path
from collections import defaultdict
from datetime import datetime

# Configuration
REPO_ROOT = Path(__file__).resolve().parents[4]
SCRIPT_DIR = str(REPO_ROOT / "dev/tools/quiz/generator")
RUNTIME_QUIZ_DIR = str(REPO_ROOT / "src/data/quiz")
RUNTIME_ANSWERS_DIR = str(REPO_ROOT / "src/data/quiz_answers")
LOG_DIR = str(REPO_ROOT / "dev/tmp/compiler_logs")

os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)
os.makedirs(LOG_DIR, exist_ok=True)

# Tracking
stats = {
    "scripts_executed": 0,
    "scripts_failed": 0,
    "quizzes_copied": 0,
    "ids_seen": set(),
    "errors": defaultdict(list),
    "start_time": datetime.now().isoformat(),
}

print("=" * 70)
print("MEGA BATCH COMPILER - Tous les scripts generate_*.py")
print("=" * 70)

# Récupère tous les scripts
scripts = sorted([f for f in os.listdir(SCRIPT_DIR)
                  if f.startswith("generate_") and f.endswith(".py")
                  and not f.startswith("generate_6eme_")])  # 6ème déjà fait

print(f"\nScripts à exécuter: {len(scripts)}")
print(f"Répertoire de source: {SCRIPT_DIR}")
print(f"Répertoire de destination: {RUNTIME_QUIZ_DIR}")
print("\n" + "=" * 70 + "\n")

# Exécute chaque script
for idx, script_name in enumerate(scripts, 1):
    script_path = os.path.join(SCRIPT_DIR, script_name)

    # Parse [niveau]_[matière]
    parts = script_name.replace("generate_", "").replace(".py", "").split("_")
    if len(parts) < 2:
        print(f"[{idx}/{len(scripts)}] ⚠ {script_name:40s} → FORMAT INVALIDE")
        continue

    level = "_".join(parts[:-1])  # Peut être "1ere", "2nde", "3eme", etc.
    subject = parts[-1]

    print(f"[{idx}/{len(scripts)}] {script_name:40s}", end=" → ", flush=True)

    try:
        # Exécute le script
        result = subprocess.run(
            [sys.executable, script_path],
            cwd=REPO_ROOT,
            capture_output=True,
            timeout=30,
            encoding="utf-8",
            errors="replace"
        )

        if result.returncode != 0:
            print(f"❌ FAIL")
            stats["scripts_failed"] += 1
            stats["errors"][script_name].append(result.stderr[:200])
            continue

        # Cherche le output dir généré (déductif: <subject>_<level>_quizzes)
        output_patterns = [
            f"{subject}_{level}_quizzes",
            f"{subject}_{level.replace('eme', 'e')}_quizzes",
            f"{level}_{subject}_quizzes",
        ]

        generated_dir = None
        for pattern in output_patterns:
            candidate = os.path.join(SCRIPT_DIR, pattern)
            if os.path.isdir(candidate):
                generated_dir = candidate
                break

        if not generated_dir:
            print(f"⚠ NO OUTPUT DIR")
            continue

        quiz_folder = os.path.join(generated_dir, "quiz")
        answers_folder = os.path.join(generated_dir, "quiz_answers")

        if not os.path.isdir(quiz_folder):
            print(f"⚠ NO QUIZ FOLDER")
            continue

        # Copie les fichiers
        count = 0
        for filename in os.listdir(quiz_folder):
            if not filename.endswith(".json"):
                continue

            try:
                # Parse ID (0145.json → 145, 1059.json → 1059)
                base_name = filename[:-5]
                numeric_id = str(int(base_name))
                dst_filename = numeric_id + ".json"

                # Check duplication
                if int(numeric_id) in stats["ids_seen"]:
                    print(f"⚠ DUPLICATE ID {numeric_id}", end=" ")
                    continue

                stats["ids_seen"].add(int(numeric_id))

                src_quiz = os.path.join(quiz_folder, filename)
                src_answers = os.path.join(answers_folder, filename)

                dst_quiz = os.path.join(RUNTIME_QUIZ_DIR, dst_filename)
                dst_answers = os.path.join(RUNTIME_ANSWERS_DIR, dst_filename)

                # Copy avec encodage UTF-8
                with open(src_quiz, "r", encoding="utf-8", errors="replace") as f:
                    quiz_data = f.read()
                with open(dst_quiz, "w", encoding="utf-8", newline="\n") as f:
                    f.write(quiz_data)

                if os.path.exists(src_answers):
                    with open(src_answers, "r", encoding="utf-8", errors="replace") as f:
                        answers_data = f.read()
                    with open(dst_answers, "w", encoding="utf-8", newline="\n") as f:
                        f.write(answers_data)

                count += 1
                stats["quizzes_copied"] += 1

            except Exception as e:
                stats["errors"][script_name].append(f"Copy error: {str(e)}")
                continue

        if count > 0:
            print(f"✓ {count} quizzes")
        else:
            print(f"⚠ 0 quizzes copied")

        stats["scripts_executed"] += 1

    except subprocess.TimeoutExpired:
        print(f"❌ TIMEOUT")
        stats["scripts_failed"] += 1
        stats["errors"][script_name].append("Timeout")
    except Exception as e:
        print(f"❌ ERROR: {str(e)[:50]}")
        stats["scripts_failed"] += 1
        stats["errors"][script_name].append(str(e)[:200])

# Print résumé
print("\n" + "=" * 70)
print("RÉSUMÉ")
print("=" * 70)
print(f"Scripts exécutés: {stats['scripts_executed']}/{len(scripts)}")
print(f"Scripts échoués:  {stats['scripts_failed']}")
print(f"Quizzes copiés:   {stats['quizzes_copied']}")
print(f"IDs uniques:      {len(stats['ids_seen'])}")

# Sauvegarde log
log_file = os.path.join(LOG_DIR, f"compiler_run_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json")
with open(log_file, "w", encoding="utf-8") as f:
    json.dump({
        "stats": {
            "scripts_executed": stats["scripts_executed"],
            "scripts_failed": stats["scripts_failed"],
            "quizzes_copied": stats["quizzes_copied"],
            "ids_unique": len(stats["ids_seen"]),
            "start_time": stats["start_time"],
            "end_time": datetime.now().isoformat(),
        },
        "errors": {k: v for k, v in stats["errors"].items() if v},
    }, f, ensure_ascii=False, indent=2)

print(f"\n✅ Log sauvegardé: {log_file}")
print("\nProchaines étapes:")
print("1. Vérifier les quizzes nouveaux en runtime")
print("2. Ré-exécuter les audits couverture")
print("3. Identifier les brèches restantes")
