#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Extracteur complet PDF → JSON pour import en PHP
Structure: Exercices avec corrections
"""

import pdfplumber
import re
import json
from pathlib import Path

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'
output_path = r'd:\Hostinger\public_html\moncoachscolaire\tools\pdf_data.json'

print("Extraction du PDF complet...")

# Extraire tout le texte avec numéros de page
pages_text = {}
with pdfplumber.open(pdf_path) as pdf:
    for i, page in enumerate(pdf.pages, 1):
        text = page.extract_text()
        if text:
            pages_text[i] = text

# Joindre pour faciliter le parsing
full_text = '\n--- PAGE_BREAK ---\n'.join(
    f'[PAGE {i}]\n{text}' for i, text in pages_text.items()
)

# Diviser par niveau (structure: 6ème pages 1-25, 5ème pages 26-50, 4ème pages 51-75, 3ème pages 76-100)
levels = {
    '6ème': [1, 7],      # Contenu pages 1-6, corrections page 7
    '5ème': [8, 13],     # Contenu pages 8-12, corrections page 13
    '4ème': [14, 18],    # Contenu pages 14-17, corrections page 18
    '3ème': [19, 22]     # Contenu pages 19-21, corrections page 22
}

exercises = {}

# Pattern pour exercices
ex_pattern = r'Exercice\s+(\d+\.\d+)\s+-\s+([^\n]+)\n([\s\S]*?)(?=Exercice\s+\d+\.\d+|FRANÇAIS|ANGLAIS|Corrections|\[PAGE)'

# Pattern pour réponses
ans_pattern = r'Exercice\s+(\d+\.\d+)\s*:\s*([^\n]+?)(?=\nExercice\s+\d+\.\d+|\nFran[ç]ais|\nAnglais|\n[A-Z]|$)'

for level, (start_page, end_page) in levels.items():
    exercises[level] = {'Mathématiques': [], 'Français': [], 'Anglais': []}
    
    # Extraire contenu exercices pour ce niveau
    content_section = []
    for p in range(start_page, end_page):
        if p in pages_text:
            content_section.append(pages_text[p])
    content_text = '\n'.join(content_section)
    
    # Extraire section corrections
    ans_section = pages_text.get(end_page, '')
    
    # Pour chaque matière
    for subject in ['Mathématiques', 'Français', 'Anglais']:
        # Chercher la section de la matière
        subject_match = re.search(
            rf'({subject}|MATHÉMATIQUES|FRANÇAIS|ANGLAIS)\s*\({level}\)',
            content_text,
            re.IGNORECASE
        )
        
        if subject_match:
            # Extraire les exercices de cette matière
            start_pos = subject_match.end()
            
            # Trouver fin de section (prochaine matière ou fin du texte)
            next_subject_match = re.search(
                r'\n(Mathématiques|Français|Anglais|MATHÉMATIQUES|FRANÇAIS|ANGLAIS)\s*\(',
                content_text[start_pos:],
                re.IGNORECASE
            )
            
            if next_subject_match:
                section = content_text[start_pos:start_pos + next_subject_match.start()]
            else:
                section = content_text[start_pos:]
            
            # Extraire exercices
            for match in re.finditer(ex_pattern, section, re.IGNORECASE):
                ex_num, title, content = match.groups()
                
                # Chercher réponse
                ans = "Non fournie"
                ans_match = re.search(
                    rf'Exercice\s+{re.escape(ex_num)}\s*:\s*([^\n]+)',
                    ans_section,
                    re.IGNORECASE
                )
                if ans_match:
                    ans = ans_match.group(1).strip()
                
                exercises[level][subject].append({
                    'numero': ex_num,
                    'titre': title.strip(),
                    'contenu': content.strip(),
                    'reponse': ans
                })

# Export JSON
output = {
    'metadata': {
        'source': 'PDF Cahiers Complets Collège',
        'niveaux': list(exercises.keys())
    },
    'data': exercises
}

# Comptage
total = sum(
    len(exercises[level][subject])
    for level in exercises
    for subject in exercises[level]
)

print(f"\nRésultats:")
for level in ['6ème', '5ème', '4ème', '3ème']:
    if level in exercises:
        m = len(exercises[level]['Mathématiques'])
        f = len(exercises[level]['Français'])
        a = len(exercises[level]['Anglais'])
        print(f"  {level}: Math={m}, Français={f}, Anglais={a}")

print(f"\nTotal d'exercices: {total}")

with open(output_path, 'w', encoding='utf-8') as f:
    json.dump(output, f, ensure_ascii=False, indent=2)

print(f"\nSauvegarde: {output_path}")
