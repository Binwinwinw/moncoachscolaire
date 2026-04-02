#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Afficher la page 7 au complet
"""

import pdfplumber

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

with pdfplumber.open(pdf_path) as pdf:
    page = pdf.pages[6]  # Page 7 (0-indexed)
    text = page.extract_text()
    print(text)
