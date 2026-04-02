#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Dump correction pages pour inspection manuelle
"""
import pdfplumber

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

correction_pages = {
    '6ème': [6, 7],
    '5ème': [13],
    '4ème': [18],
    '3ème': [22]
}

for level, pages in correction_pages.items():
    print(f"\n\n{'='*80}")
    print(f"CORRECTIONS {level.upper()}")
    print('='*80)
    for p in pages:
        with pdfplumber.open(pdf_path) as pdf:
            if p - 1 < len(pdf.pages):
                text = pdf.pages[p - 1].extract_text()
                print(f"\n--- Page {p} ---\n", flush=True)
                # Write to file instead to avoid encoding issues
                try:
                    print(text, flush=True)
                except:
                    with open(f'd:/temp_corrections_{level}_{p}.txt', 'w', encoding='utf-8') as f:
                        f.write(text)
                print("\n", flush=True)
