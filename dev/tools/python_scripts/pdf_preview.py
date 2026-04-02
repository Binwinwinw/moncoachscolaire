#!/usr/bin/env python3
"""
Afficher les premières pages du PDF pour comprendre sa structure
"""

import pdfplumber

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

with pdfplumber.open(pdf_path) as pdf:
    print(f"📄 Total de pages: {len(pdf.pages)}\n")
    
    # Afficher les 5 premières pages
    for i in range(min(5, len(pdf.pages))):
        page = pdf.pages[i]
        text = page.extract_text()
        print(f"\n{'='*60}")
        print(f"PAGE {i+1}")
        print('='*60)
        if text:
            lines = text.split('\n')[:40]  # Afficher les 40 premières lignes
            for line in lines:
                print(line)
