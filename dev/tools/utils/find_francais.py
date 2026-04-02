#!/usr/bin/env python3
with open(r'd:\Hostinger\public_html\moncoachscolaire\exercices\college\4eme exercices&correction.md', 'r', encoding='utf-8') as f:
    lines = f.readlines()
    for i, line in enumerate(lines, 1):
        if '## FRANÇAIS' in line:
            print(f"Ligne {i}: {repr(line)}")
            for j in range(max(0, i-2), min(len(lines), i+20)):
                print(f"{j+1:4d}: {lines[j].rstrip()}")
            break
