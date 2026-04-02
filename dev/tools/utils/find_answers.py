#!/usr/bin/env python3
"""
Chercher où sont les réponses dans le PDF
"""

import pdfplumber
import re

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

with pdfplumber.open(pdf_path) as pdf:
    for i in range(len(pdf.pages)):
        page = pdf.pages[i]
        text = page.extract_text()
        
        # Chercher "réponse", "correction", "solution" etc
        if text and re.search(r'(réponse|correction|solution|réponses|corrections)', text, re.IGNORECASE):
            print(f"\nPAGE {i+1} - Contient une section de réponses/corrections")
            lines = text.split('\n')
            for j, line in enumerate(lines):
                if re.search(r'(réponse|correction|solution)', line, re.IGNORECASE):
                    # Afficher contexte
                    start = max(0, j-2)
                    end = min(len(lines), j+10)
                    for k in range(start, end):
                        print(lines[k])
                    break
