#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Debug: afficher ce qui est trouvé comme réponses
"""

import pdfplumber
import re

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

# Extraire tout le texte
all_text = ''
with pdfplumber.open(pdf_path) as pdf:
    for i, page in enumerate(pdf.pages, 1):
        text = page.extract_text()
        if text:
            all_text += f'\n[PAGE {i}]\n' + text

# Chercher "Exercice" suivi de deux chiffres
matches = re.findall(r'Exercice\s+\d+\.\d+\s*[:\-]', all_text)
print(f"Total 'Exercice X.Y :' trouvés: {len(matches)}")

# Afficher les 20 premiers
for i, match in enumerate(matches[:20]):
    print(f"  {i+1}: {match}")

# Chercher spécifiquement le pattern que j'utilise pour les réponses
ans_matches = list(re.finditer(r'Exercice\s+(\d+\.\d+)\s*:\s*([^\n]+)', all_text))
print(f"\nPattern 'Exercice X.Y : réponse' trouvé {len(ans_matches)} fois")

# Afficher les premiers
for match in ans_matches[:15]:
    num, ans = match.groups()
    print(f"  {num}: {ans[:60]}")
