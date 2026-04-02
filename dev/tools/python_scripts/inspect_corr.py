#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Inspecter les sections de corrections page 6-7 en détail
"""

import pdfplumber
import re

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

with pdfplumber.open(pdf_path) as pdf:
    page6 = pdf.pages[5].extract_text()
    page7 = pdf.pages[6].extract_text()

    # Chercher "Corrections"
    print("Page 6 - Corrections found at:", page6.find("Corrections"))
    print("\nPage 6 - from 'Corrections' onwards:")
    corr_idx = page6.find("Corrections")
    if corr_idx >= 0:
        lines = page6[corr_idx:].split('\n')[:35]
        for i, line in enumerate(lines):
            print(f"{i:2}: {line}")

    print("\n\n" + "="*60)
    print("Page 7 - Full content:")
    lines = page7.split('\n')[:40]
    for i, line in enumerate(lines):
        print(f"{i:2}: {line}")
