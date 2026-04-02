#!/usr/bin/env python3
"""
Extracteur de données du PDF "Cahiers Complets Collège 6e-5e-4e-3e.pdf"
Analyse la structure et extrait exercices + corrections
"""

import pdfplumber
import re
import json
from collections import defaultdict

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

print("📖 Ouverture du PDF...\n")

try:
    with pdfplumber.open(pdf_path) as pdf:
        print(f"✅ Total de pages: {len(pdf.pages)}\n")
        
        # Analyser la structure globale
        print("--- Analyse des en-têtes et structure ---\n")
        
        data_by_level = defaultdict(lambda: defaultdict(list))
        
        current_level = None
        current_subject = None
        current_exercise_num = None
        current_question = ""
        current_answer = ""
        in_answer_section = False
        
        all_text = ""
        
        # Extraire tout le texte
        for page_num, page in enumerate(pdf.pages[:10], 1):  # Analyser les 10 premières pages
            text = page.extract_text()
            if text:
                all_text += f"\n--- PAGE {page_num} ---\n{text}"
        
        # Afficher aperçu
        print(all_text[:3000])
        print("\n" + "="*80 + "\n")
        
        # Chercher les patterns niveau/matière
        print("--- Patterns détectés ---\n")
        
        # Chercher niveaux
        levels = ['6ème', '6e', '5ème', '5e', '4ème', '4e', '3ème', '3e']
        for level in levels:
            if level in all_text:
                print(f"✅ Niveau détecté: {level}")
        
        # Chercher matières
        subjects = ['Mathématiques', 'Français', 'Anglais', 'Histoire-Géographie', 'SVT', 'Sciences']
        for subject in subjects:
            count = all_text.count(subject)
            if count > 0:
                print(f"✅ Matière détectée: {subject} ({count} occurrences)")
        
        # Chercher pattern exercices/réponses
        ex_pattern = re.compile(r'Exercice\s+(\d+\.?\d*)', re.IGNORECASE)
        matches = ex_pattern.findall(all_text)
        if matches:
            print(f"✅ Exercices détectés: {len(set(matches))} uniques")
        
        print("\n--- Détail des premières pages ---\n")
        for i in range(min(3, len(pdf.pages))):
            text = pdf.pages[i].extract_text()
            if text:
                lines = text.split('\n')[:15]
                print(f"Page {i+1}:")
                for line in lines:
                    if line.strip():
                        print(f"  {line[:100]}")
                print()
        
except Exception as e:
    print(f"❌ Erreur: {e}")
    import traceback
    traceback.print_exc()
