#!/usr/bin/env python3
with open(r'd:\Hostinger\public_html\moncoachscolaire\exercices\college\4eme exercices&correction.md', 'r', encoding='utf-8') as f:
    lines = f.readlines()
    for i, line in enumerate(lines[:200], 1):
        if '##' in line or '####' in line or 'Exercice' in line or 'Corrections' in line:
            print(f"{i:4d}: {line.rstrip()}")
