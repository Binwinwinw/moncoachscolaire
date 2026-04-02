#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Examiner les pages de corrections
"""

import pdfplumber

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

with pdfplumber.open(pdf_path) as pdf:
    # Pages de corrections
    for page_num in [7, 13, 18, 22]:
        if page_num < len(pdf.pages):
            page = pdf.pages[page_num - 1]
            text = page.extract_text()
            
            print(f"\n{'='*60}")
            print(f"PAGE {page_num}")
            print('='*60)
            if text:
                lines = text.split('\n')[:50]
                for i, line in enumerate(lines):
                    print(f"{i+1}: {line}")
