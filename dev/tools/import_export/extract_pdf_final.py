#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Extract PDF Final - Corriger l'association réponse/exercice par numéro
"""

import pdfplumber
import re
import json

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

print("PDF Extraction FINAL (parsing corrections fiable)")

# Pages de corrections par niveau
correction_pages = {
    '6ème': [6, 7],
    '5ème': [13],
    '4ème': [18],
    '3ème': [22]
}

# Pages de cours (approx) pour limiter la recherche des exercices
cahier_ranges = {
    '6ème': (1, 7),
    '5ème': (8, 13),
    '4ème': (14, 18),
    '3ème': (19, 22)
}

results = {
    '6ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '5ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '4ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '3ème': {'Mathématiques': [], 'Français': [], 'Anglais': []}
}

# Utilitaires
def read_pages(start, end):
    buf = ''
    with pdfplumber.open(pdf_path) as pdf:
        for p in range(start, end + 1):
            if p-1 < len(pdf.pages):
                t = pdf.pages[p-1].extract_text()
                if t:
                    buf += '\n' + t
    return buf

def parse_corrections(level):
    answers = {
        'Mathématiques': {},
        'Français': {},
        'Anglais': {}
    }
    text = ''
    for p in correction_pages.get(level, []):
        with pdfplumber.open(pdf_path) as pdf:
            page = pdf.pages[p-1]
            t = page.extract_text()
            if t:
                text += '\n' + t

    current = None
    for line in text.split('\n'):
        low = line.lower()
        if 'math' in low:
            current = 'Mathématiques'; continue
        if 'fran' in low:
            current = 'Français'; continue
        if 'angl' in low:
            current = 'Anglais'; continue
        m = re.match(r'Exercice\s+(\d+\.\d+)\s*:\s*(.*)', line)
        if m and current:
            num, ans = m.groups()
            answers[current][num] = ans.strip()
    return answers

def parse_exercises(level, subject, text):
    exercises = []
    # support -, –, — as separator
    for m in re.finditer(r'Exercice\s+(\d+\.\d+)\s+[-–—]\s+([^\n]+)', text):
        num, title = m.groups()
        start = m.end()
        next_m = re.search(r'\nExercice\s+\d+\.\d+', text[start:])
        if next_m:
            content = text[start:start+next_m.start()]
        else:
            content = text[start:]
        content = content.strip()
        if len(content) > 500:
            content = content[:500] + '...'
        exercises.append({'numero': num, 'titre': title.strip(), 'contenu': content, 'reponse': 'Non fournie'})
    return exercises

# Lecture globale des pages pour recherche de matières
with pdfplumber.open(pdf_path) as pdf:
    all_pages_text = []
    for i, page in enumerate(pdf.pages, 1):
        t = page.extract_text()
        all_pages_text.append(t or '')

for level in ['6ème', '5ème', '4ème', '3ème']:
    print(f"\nTraitement {level}...")
    start, end = cahier_ranges[level]
    cahier_text = read_pages(start, end)
    answers = parse_corrections(level)

    for subject, marker in [('Mathématiques', 'MATHÉMATIQUES'), ('Français', 'FRANÇAIS'), ('Anglais', 'ANGLAIS')]:
        # Isoler la section matière dans le cahier
        sub_match = re.search(rf'{marker}\s*\({re.escape(level)}\)', cahier_text, re.IGNORECASE)
        if not sub_match:
            continue
        sub_start = sub_match.end()
        next_sub = re.search(r'(MATHÉMATIQUES|FRANÇAIS|ANGLAIS|Corrections)', cahier_text[sub_start:], re.IGNORECASE)
        if next_sub:
            sub_end = sub_start + next_sub.start()
        else:
            sub_end = len(cahier_text)
        sub_text = cahier_text[sub_start:sub_end]

        exs = parse_exercises(level, subject, sub_text)
        # Mapper les réponses par numéro
        for ex in exs:
            ex['reponse'] = answers.get(subject, {}).get(ex['numero'], 'Non fournie')
        results[level][subject].extend(exs)
        print(f"  {subject}: {len(exs)} ex, réponses capturées: {len(answers.get(subject, {}))}")

# Export
output = {
    'metadata': {'source': 'PDF Cahiers Complets', 'levels': list(results.keys())},
    'data': results
}

with open(r'd:\Hostinger\public_html\moncoachscolaire\tools\pdf_data.json', 'w', encoding='utf-8') as f:
    json.dump(output, f, ensure_ascii=False, indent=2)

print("\nJSON sauvegardé")
