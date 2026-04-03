#!/usr/bin/env python3
"""Comparer public/quiz vs src/data/quiz"""

import json
from pathlib import Path
import hashlib

repo_root = Path(__file__).resolve().parents[3]
public_dir = repo_root / "public" / "quiz"
src_dir = repo_root / "src" / "data" / "quiz"

public_files = sorted([p.name for p in public_dir.glob("*.json")])
src_files = sorted([p.name for p in src_dir.glob("*.json")])

print(f"[compare] public/quiz: {len(public_files)} fichiers")
print(f"[compare] src/data/quiz: {len(src_files)} fichiers")

if public_files != src_files:
    print("\n[WARN] Listes différentes!")
    missing_in_src = set(public_files) - set(src_files)
    missing_in_public = set(src_files) - set(public_files)
    if missing_in_src:
        print(f"  Manquants dans src/data/quiz: {sorted(missing_in_src)}")
    if missing_in_public:
        print(f"  Manquants dans public/quiz: {sorted(missing_in_public)}")
else:
    print("\n[OK] Même liste de fichiers")

different_count = 0
identical_count = 0

for filename in public_files:
    if filename not in src_files:
        continue

    public_path = public_dir / filename
    src_path = src_dir / filename

    public_hash = hashlib.sha256(public_path.read_bytes()).hexdigest()
    src_hash = hashlib.sha256(src_path.read_bytes()).hexdigest()

    if public_hash != src_hash:
        different_count += 1
        print(f"  [DIFF] {filename}")
    else:
        identical_count += 1

print(f"\n[compare] Identiques: {identical_count}")
print(f"[compare] Différents: {different_count}")

if different_count == 0 and len(public_files) == len(src_files):
    print("\n[OK] Les deux dossiers sont IDENTIQUES - sûr de supprimer public/quiz/")
else:
    print("\n[WARN] Différences détectées - synchroniser d'abord!")
