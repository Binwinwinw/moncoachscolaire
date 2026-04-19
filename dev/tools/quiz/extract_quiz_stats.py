import os
import json
from collections import defaultdict

QUIZ_DIR = "src/data/quiz"
OBJECTIF = 48

compteur = defaultdict(int)

for fname in os.listdir(QUIZ_DIR):
    if not fname.endswith(".json"):
        continue
    path = os.path.join(QUIZ_DIR, fname)
    try:
        with open(path, "r", encoding="utf-8") as f:
            data = json.load(f)
        level = data.get("contents", {}).get("level", "INCONNU")
        subject = data.get("contents", {}).get("subject", "INCONNU")
        compteur[(level, subject)] += 1
    except Exception as e:
        print(f"Erreur lecture {fname}: {e}")

print(f"{'Niveau':<8} | {'Matière':<20} | Quiz présents | Quiz manquants")
print("-" * 60)
for (level, subject), count in sorted(compteur.items()):
    if count < OBJECTIF:
        print(f"{level:<8} | {subject:<20} | {count:<12} | {OBJECTIF - count}")
