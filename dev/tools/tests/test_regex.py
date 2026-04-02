#!/usr/bin/env python3
import re

filepath = r'd:\Hostinger\public_html\moncoachscolaire\exercices\college\4eme exercices&correction.md'
with open(filepath, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Chercher titres d'exercices
for i, line in enumerate(lines[:250], 1):
    if line.startswith('#### Exercice'):
        print(f"Ligne {i}: {repr(line.rstrip())}")
        match = re.match(r'^####\s+Exercice\s+([0-9]+\.[0-9]+)\s+-\s+(.+)$', line)
        if match:
            print(f"   ✅ Matched: {match.group(1)} -> {match.group(2)}")
        else:
            print(f"   ❌ No match")
