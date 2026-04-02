#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Afficher page 6
"""

import pdfplumber

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

with pdfplumber.open(pdf_path) as pdf:
    page = pdf.pages[5]  # Page 6
    text = page.extract_text()
    print(text[:2000])
