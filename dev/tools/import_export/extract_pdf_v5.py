#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Extract PDF v5 - Simple et Direct
1. Extraire tous les Exercice X.Y - Titre par cahier/matière
2. Trouver tous les Exercice X.Y : réponse et les associer par ordre d'apparition
"""

import pdfplumber
import re
import json

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

print("PDF Extraction v5 - Approche simple")

# Extraire toutes les pages
all_text = ''
all_pages = {}
with pdfplumber.open(pdf_path) as pdf:
    for i, page in enumerate(pdf.pages, 1):
        text = page.extract_text()
        if text:
            all_pages[i] = text
            all_text += '\n[PAGE_' + str(i) + ']\n' + text

# Structure simple:
# CAHIER 6ÈME ... [PAGE_8 marks end of 6ème]
# CAHIER 5ÈME ... [PAGE_14 marks end of 5ème]
# CAHIER 4ÈME ... [PAGE_19 marks end of 4ème]
# CAHIER 3ÈME [jusqu'à fin]

# Identifier les positions des cahiers et des sections de réponses
cahier_starts = {}
correction_starts = {}

for level in ['6ème', '5ème', '4ème', '3ème']:
    match = re.search(rf'CAHIER\s+{re.escape(level.upper())}', all_text, re.IGNORECASE)
    if match:
        cahier_starts[level] = match.start()
    
    match = re.search(rf'Corrections\s*\({re.escape(level)}\)', all_text, re.IGNORECASE)
    if match:
        correction_starts[level] = match.start()

print(f"Cahiers trouvés: {list(cahier_starts.keys())}")
print(f"Corrections trouvées: {list(correction_starts.keys())}")

results = {
    '6ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '5ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '4ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '3ème': {'Mathématiques': [], 'Français': [], 'Anglais': []}
}

# Pour chaque cahier
for level_idx, level in enumerate(['6ème', '5ème', '4ème', '3ème']):
    if level not in cahier_starts:
        continue
    
    # Début du cahier
    cahier_start = cahier_starts[level]
    
    # Fin = début du prochain cahier ou fin du document
    if level_idx < 3:
        next_level = ['6ème', '5ème', '4ème', '3ème'][level_idx + 1]
        if next_level in cahier_starts:
            cahier_end = cahier_starts[next_level]
        else:
            cahier_end = len(all_text)
    else:
        cahier_end = len(all_text)
    
    cahier_text = all_text[cahier_start:cahier_end]
    
    # Extraire section "Corrections" pour ce niveau
    corr_section = ''
    corr_match = re.search(rf'Corrections\s*\({re.escape(level)}\)([\s\S]*)', cahier_text, re.IGNORECASE)
    if corr_match:
        # Corrections jusqu'à fin cahier ou CAHIER suivant
        corr_text = corr_match.group(1)
        next_cahier = re.search(r'CAHIER', corr_text, re.IGNORECASE)
        if next_cahier:
            corr_section = corr_text[:next_cahier.start()]
        else:
            corr_section = corr_text
    
    # Extraire réponses groupées par matière de la section corrections
    answers_by_subject = {
        'Mathématiques': {},
        'Français': {},
        'Anglais': {}
    }
    
    for subject, subject_markers in [
        ('Mathématiques', ['MATHÉMATIQUES', 'Mathématiques']),
        ('Français', ['FRANÇAIS', 'Franç ais', 'Français']),
        ('Anglais', ['ANGLAIS', 'Anglais'])
    ]:
        # Chercher "MATHÉMATIQUES - Réponses" ou équivalent
        for marker in subject_markers:
            pattern = rf'{re.escape(marker)}\s*-\s*R[ée]ponses([\s\S]*?)(?={subject_markers[0]}\s*-|CAHIER|$)'
            match = re.search(pattern, corr_section, re.IGNORECASE)
            if match:
                section = match.group(1)
                for ans_match in re.finditer(r'Exercice\s+(\d+\.\d+)\s*:\s*([^\n]+)', section):
                    num, ans = ans_match.groups()
                    answers_by_subject[subject][num] = ans.strip()
                break
    
    print(f"\n{level}:")
    for subject in ['Mathématiques', 'Français', 'Anglais']:
        print(f"  {subject} réponses: {len(answers_by_subject[subject])}")
    
    # Maintenant extraire les exercices du cahier
    for subject, subject_marker in [('Mathématiques', 'MATHÉMATIQUES'), ('Français', 'FRANÇAIS'), ('Anglais', 'ANGLAIS')]:
        # Trouver section matière
        sub_pattern = rf'{subject_marker}\s*\({re.escape(level)}\)'
        sub_match = re.search(sub_pattern, cahier_text, re.IGNORECASE)
        
        if not sub_match:
            continue
        
        sub_start = sub_match.end()
        
        # Fin = prochaine matière
        next_subject_match = re.search(
            r'(MATHÉMATIQUES|FRANÇAIS|ANGLAIS|Corrections)\s*\(',
            cahier_text[sub_start:],
            re.IGNORECASE
        )
        
        if next_subject_match:
            sub_end = sub_start + next_subject_match.start()
        else:
            sub_end = len(cahier_text)
        
        sub_section = cahier_text[sub_start:sub_end]
        
        # Extraire exercices
        for ex_match in re.finditer(r'Exercice\s+(\d+\.\d+)\s+-\s+([^\n]+)', sub_section):
            num, title = ex_match.groups()
            title = title.strip()
            
            # Contenu jusqu'au prochain exercice
            cont_start = ex_match.end()
            next_ex = re.search(r'\nExercice\s+\d+\.\d+', sub_section[cont_start:])
            if next_ex:
                content = sub_section[cont_start:cont_start + next_ex.start()]
            else:
                content = sub_section[cont_start:]
            
            content = content.strip()
            if len(content) > 400:
                content = content[:400] + "..."
            
            # Réponse associée
            answer = answers_by_subject[subject].get(num, 'Non fournie')
            
            results[level][subject].append({
                'numero': num,
                'titre': title,
                'contenu': content,
                'reponse': answer
            })

# Afficher résumé
print("\nRésumé final:")
for level in ['6ème', '5ème', '4ème', '3ème']:
    m = len(results[level]['Mathématiques'])
    f = len(results[level]['Français'])
    a = len(results[level]['Anglais'])
    print(f"  {level}: Math={m}, Français={f}, Anglais={a}")

# Export
output = {
    'metadata': {
        'source': 'PDF Cahiers Complets Collège 6e-5e-4e-3e',
        'levels': list(results.keys())
    },
    'data': results
}

with open(r'd:\Hostinger\public_html\moncoachscolaire\tools\pdf_data.json', 'w', encoding='utf-8') as f:
    json.dump(output, f, ensure_ascii=False, indent=2)

print("\nJSON sauvegardé")
