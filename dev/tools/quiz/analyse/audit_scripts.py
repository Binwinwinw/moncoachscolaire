#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import os
import re
from collections import defaultdict

SCRIPT_DIR = "dev/tools/quiz/generator"
scripts = [f for f in os.listdir(SCRIPT_DIR) if f.startswith("generate_") and f.endswith(".py")]

status = defaultdict(lambda: {"rempli": 0, "vide": 0})

for script in sorted(scripts):
    filepath = os.path.join(SCRIPT_DIR, script)

    with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
        content = f.read()

    # Extrait le niveau et matière
    match = re.match(r"generate_(\d?eme|nde|ère|terminale)_(.+?)\.py", script)
    if not match:
        continue

    level = match.group(1)
    subject = match.group(2)

    # Cherche quizzes_data = [ ... ]
    data_match = re.search(r"quizzes_data\s*=\s*\[\s*(\(|$)", content)
    if data_match:
        # Compte les tuples (
        count = content.count("\n        (")
        if count > 0:
            status[level]["rempli"] += 1
            print(f"✓ {script:40s} → {count} quizzes")
        else:
            status[level]["vide"] += 1
            print(f"✗ {script:40s} → VIDE")
    else:
        print(f"? {script:40s} → PAS TROUVÉ")

print("\n=== RÉSUMÉ ===")
for level in sorted(status.keys()):
    stats = status[level]
    total = stats["rempli"] + stats["vide"]
    print(f"{level:10s}: {stats['rempli']}/{total} remplis")
