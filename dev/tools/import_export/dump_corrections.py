#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Dump des pages de corrections avec numéro de ligne pour mapping manuel.
Niveaux/pages identifiés :
- 6e : pages 6 et 7
- 5e : page 13
- 4e : page 18
- 3e : page 22
"""

import pdfplumber
from pathlib import Path

pdf_path = Path(r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf')
levels_pages = {
    '6ème': [6, 7],
    '5ème': [13],
    '4ème': [18],
    '3ème': [22],
}

print(f"Source PDF: {pdf_path}")

with pdfplumber.open(str(pdf_path)) as pdf:
    for level, pages in levels_pages.items():
        print("\n" + "="*70)
        print(f"Corrections {level} (pages {', '.join(map(str, pages))})")
        print("="*70)
        for p in pages:
            idx = p - 1
            if idx >= len(pdf.pages):
                print(f"[PAGE {p}] introuvable")
                continue
            text = pdf.pages[idx].extract_text() or ''
            lines = text.split('\n')
            print(f"\n[PAGE {p}] ({len(lines)} lignes)")
            for i, line in enumerate(lines, 1):
                print(f"{i:02d}: {line}")
