#!/usr/bin/env python3
"""Vérifier synchronisation public/quiz vs src/data/quiz_answers"""
import json
from pathlib import Path

pairs = [
    ("19", "6eme Français"),
    ("103", "seconde Mathématiques"),
    ("100", "seconde Français"),
]

for qid, desc in pairs:
    pub_path = Path(f"public/quiz/{qid}.json")
    ans_path = Path(f"src/data/quiz_answers/{qid}.json")

    pub = json.load(pub_path.open("r", encoding="utf-8"))
    ans = json.load(ans_path.open("r", encoding="utf-8"))

    pub_level = pub["quiz"]["level"]
    pub_subject = pub["quiz"]["subject"]
    pub_title = pub["contents"]["title"]

    ans_level = ans["quiz"]["level"]
    ans_subject = ans["quiz"]["subject"]
    ans_title = ans["contents"]["title"]

    print(f"\n[{qid}] {desc}")
    print(f"  PUBLIC  - level:{pub_level}, subject:{pub_subject}")
    print(f"  ANSWERS - level:{ans_level}, subject:{ans_subject}")
    print(f"  PUBLIC  - title: {pub_title}")
    print(f"  ANSWERS - title: {ans_title}")

    if pub_level == ans_level and pub_subject == ans_subject and pub_title == ans_title:
        print("  ✓ SYNC")
    else:
        print("  ✗ DESYNC!")
