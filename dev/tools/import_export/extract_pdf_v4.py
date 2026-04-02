#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Extract PDF v4 - parse sections de corrections à partir des pages de correction identifiées
"""

import pdfplumber
import re
import json

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

# Pages de corrections identifiées
correction_pages = {
    '6ème': [6, 7],    # Fin de 6 et début de 7
    '5ème': [13],      # Page 13
    '4ème': [18],      # Page 18
    '3ème': [22]       # Page 22
}

print("PDF Extraction v4 - Corrections par matière")

# Extraire toutes les pages
all_pages = {}
with pdfplumber.open(pdf_path) as pdf:
    for i, page in enumerate(pdf.pages, 1):
        text = page.extract_text()
        if text:
            all_pages[i] = text

# Extraire les réponses par niveau/matière
# Structure: "Matière - Réponses\nExercice X.Y : réponse\n..."

corrections = {}

for level, pages_list in correction_pages.items():
    corrections[level] = {
        'Mathématiques': {},
        'Français': {},
        'Anglais': {}
    }
    
    # Joindre les pages de correction pour ce niveau
    correction_text = '\n'.join(all_pages.get(p, '') for p in pages_list)
    
    # Chercher chaque section de matière
    for subject, subject_fr in [
        ('Mathématiques', '(MATHÉMATIQUES|Mathématiques)'),
        ('Français', '(FRANÇAIS|Franç ais|Français)'),
        ('Anglais', '(ANGLAIS|Anglais)')
    ]:
        # Pattern: "MATIÈRE - Réponses" ou "Matière - Réponses"
        section_pattern = rf'{subject_fr}\s*-\s*R[ée]ponses([\s\S]*?)(?={subject_fr}\s*-|Corrections|CAHIER|\Z)'
        section_match = re.search(section_pattern, correction_text, re.IGNORECASE)
        
        if section_match:
            section = section_match.group(1)
            
            # Extraire les exercices et réponses
            for ex_match in re.finditer(r'Exercice\s+(\d+\.\d+)\s*:\s*([^\n]+)', section):
                num, ans = ex_match.groups()
                corrections[level][subject][num] = ans.strip()
            
            print(f"  {level} {subject}: {len(corrections[level][subject])} réponses")

# Maintenant extraire les exercices et associer les réponses
# Utiliser la même approche que v3 mais avec lookup par niveau/matière

results = {
    '6ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '5ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '4ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '3ème': {'Mathématiques': [], 'Français': [], 'Anglais': []}
}

# Reconsture ALL TEXT
all_text = '\n'.join(all_pages.values())

for level in ['6ème', '5ème', '4ème', '3ème']:
    # Chercher section CAHIER NIVEAU
    cahier_pattern = rf'CAHIER\s+{re.escape(level.upper())}'
    cahier_match = re.search(cahier_pattern, all_text, re.IGNORECASE)
    
    if not cahier_match:
        print(f"⚠️ Section {level} non trouvée")
        continue
    
    cahier_start = cahier_match.start()
    
    # Fin du cahier = début du prochain cahier
    next_cahier_match = re.search(
        rf'CAHIER\s+(?!{re.escape(level.upper())})',
        all_text[cahier_start + 10:],
        re.IGNORECASE
    )
    
    if next_cahier_match:
        cahier_end = cahier_start + 10 + next_cahier_match.start()
    else:
        cahier_end = len(all_text)
    
    cahier_text = all_text[cahier_start:cahier_end]
    
    # Pour chaque matière
    for subject, subject_pattern in [('Mathématiques', 'MATHÉMATIQUES'), ('Français', 'FRANÇAIS'), ('Anglais', 'ANGLAIS')]:
        subject_match = re.search(rf'{subject_pattern}\s*\({level}\)', cahier_text, re.IGNORECASE)
        
        if not subject_match:
            continue
        
        subject_start = subject_match.end()
        
        # Fin = prochaine matière
        next_subject_match = re.search(
            r'(MATHÉMATIQUES|FRANÇAIS|ANGLAIS)\s*\(',
            cahier_text[subject_start:],
            re.IGNORECASE
        )
        
        if next_subject_match:
            subject_end = subject_start + next_subject_match.start()
        else:
            subject_end = len(cahier_text)
        
        subject_section = cahier_text[subject_start:subject_end]
        
        # Extraire exercices
        for match in re.finditer(r'Exercice\s+(\d+\.\d+)\s+-\s+([^\n]+)', subject_section):
            ex_num, title = match.groups()
            title = title.strip()
            
            # Contenu
            content_start = match.end()
            next_ex = re.search(r'\nExercice\s+\d+\.\d+', subject_section[content_start:])
            if next_ex:
                content = subject_section[content_start:content_start + next_ex.start()]
            else:
                content = subject_section[content_start:]
            
            content = content.strip()
            if len(content) > 500:
                content = content[:500] + "..."
            
            # Chercher réponse pour ce niveau/matière/exercice
            answer = corrections[level][subject].get(ex_num, 'Non fournie')
            
            results[level][subject].append({
                'numero': ex_num,
                'titre': title,
                'contenu': content,
                'reponse': answer
            })

# Afficher stats
print("\nRésultats:")
for level in ['6ème', '5ème', '4ème', '3ème']:
    m = len(results[level]['Mathématiques'])
    f = len(results[level]['Français'])
    a = len(results[level]['Anglais'])
    print(f"  {level}: Math={m}, Français={f}, Anglais={a}")

# Export JSON
output = {
    'metadata': {
        'source': 'PDF Cahiers Complets Collège 6e-5e-4e-3e',
        'levels': list(results.keys())
    },
    'data': results
}

output_path = r'd:\Hostinger\public_html\moncoachscolaire\tools\pdf_data.json'
with open(output_path, 'w', encoding='utf-8') as f:
    json.dump(output, f, ensure_ascii=False, indent=2)

print(f"\nFichier JSON: {output_path}")
