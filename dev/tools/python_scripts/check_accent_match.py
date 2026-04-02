#!/usr/bin/env python3
def remove_accents(text):
    unwanted = {
        'á':'a', 'é':'e', 'í':'i', 'ó':'o', 'ú':'u',
        'Á':'A', 'É':'E', 'Í':'I', 'Ó':'O', 'Ú':'U',
        'à':'a', 'è':'e', 'ù':'u', 'À':'A', 'È':'E', 'Ù':'U',
        'ä':'a', 'ë':'e', 'ï':'i', 'ö':'o', 'ü':'u',
        'Ä':'A', 'Ë':'E', 'Ï':'I', 'Ö':'O', 'Ü':'U'
    }
    return ''.join(unwanted.get(c, c) for c in text)

with open(r'd:\Hostinger\public_html\moncoachscolaire\exercices\college\4eme exercices&correction.md', 'r', encoding='utf-8') as f:
    lines = f.readlines()
    
# Trouver les en-têtes ## 
for i, line in enumerate(lines):
    if line.startswith('## '):
        heading = line[3:].strip()
        heading_clean = remove_accents(heading.upper())
        target = remove_accents('Français'.upper())
        print(f"Ligne {i+1}: '{heading}' → '{heading_clean}' (target: '{target}', match: {heading_clean == target})")
