#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Extracteur PDF v3 - approche simplifiée et robuste
Parse TOUT d'abord pour construire map exercice->réponse
"""

import pdfplumber
import re
import json

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

print("PDF Extraction v3...")

# Étape 1: Extraire TOUS les exercices du PDF entier
all_text = ''
with pdfplumber.open(pdf_path) as pdf:
    for i, page in enumerate(pdf.pages, 1):
        text = page.extract_text()
        if text:
            all_text += f'\n[PAGE {i}]\n' + text

# Étape 2: Trouver tous les numéros d'exercice et créer une map exercice -> réponse
# Pattern: "Exercice X.Y : réponse"
answers_map = {}
for match in re.finditer(r'Exercice\s+(\d+\.\d+)\s*:\s*([^\n]+)', all_text):
    num, ans = match.groups()
    # Garder seulement la première occurrence de chaque exercice
    if num not in answers_map:
        answers_map[num] = ans.strip()

print(f"Réponses trouvées: {len(answers_map)}")

# Étape 3: Extraire les exercices avec leur contenu par niveau/matière
# Structure identifiée:
# CAHIER 6ÈME / CAHIER 5ÈME / etc.
# MATHÉMATIQUES (6ème) / FRANÇAIS (6ème) / ANGLAIS (6ème)
# Exercice X.Y - Titre
# Contenu...

results = {
    '6ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '5ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '4ème': {'Mathématiques': [], 'Français': [], 'Anglais': []},
    '3ème': {'Mathématiques': [], 'Français': [], 'Anglais': []}
}

# Pour chaque niveau
for level in ['6ème', '5ème', '4ème', '3ème']:
    # Chercher section CAHIER NIVEAU
    cahier_pattern = rf'CAHIER\s+{re.escape(level.upper())}'
    cahier_match = re.search(cahier_pattern, all_text, re.IGNORECASE)
    
    if not cahier_match:
        print(f"⚠️ Section {level} non trouvée")
        continue
    
    cahier_start = cahier_match.start()
    
    # Fin du cahier = début du prochain cahier ou fin
    next_cahier = None
    for next_level in ['6ème', '5ème', '4ème', '3ème']:
        if next_level != level:
            next_match = re.search(
                rf'CAHIER\s+{re.escape(next_level.upper())}',
                all_text[cahier_start + 10:],
                re.IGNORECASE
            )
            if next_match:
                cahier_end = cahier_start + 10 + next_match.start()
                break
    else:
        cahier_end = len(all_text)
    
    cahier_text = all_text[cahier_start:cahier_end]
    
    # Pour chaque matière
    for subject, subject_name in [('MATHÉMATIQUES', 'Mathématiques'), ('FRANÇAIS', 'Français'), ('ANGLAIS', 'Anglais')]:
        subject_pattern = rf'{subject}\s*\({level}\)'
        subject_match = re.search(subject_pattern, cahier_text, re.IGNORECASE)
        
        if not subject_match:
            continue
        
        subject_start = subject_match.end()
        
        # Fin = prochaine matière ou fin cahier
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
            
            # Contenu jusqu'au prochain exercice
            content_start = match.end()
            next_ex = re.search(r'\nExercice\s+\d+\.\d+', subject_section[content_start:])
            if next_ex:
                content = subject_section[content_start:content_start + next_ex.start()]
            else:
                content = subject_section[content_start:]
            
            content = content.strip()
            # Limiter à 500 chars
            if len(content) > 500:
                content = content[:500] + "..."
            
            # Trouver réponse
            answer = answers_map.get(ex_num, 'Non fournie')
            
            results[level][subject_name].append({
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
