#!/usr/bin/env python3
with open(r'd:\Hostinger\public_html\moncoachscolaire\exercices\college\4eme exercices&correction.md', 'r', encoding='utf-8') as f:
    lines = f.readlines()
    
in_corrections = False
for i, line in enumerate(lines):
    if '## Corrections' in line:
        in_corrections = True
    elif in_corrections:
        if line.startswith('## '):
            break
        if line.startswith('###'):
            print(f"Ligne {i+1}: {line.rstrip()}")
