#!/usr/bin/env python3
"""
Extracteur complet du PDF collège avec toutes corrections
Export en JSON pour import en PHP
"""

import pdfplumber
import re
import json
from collections import defaultdict

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

print("📖 Extraction du PDF complet...\n")

# Extraire tout le texte
full_text = ""
with pdfplumber.open(pdf_path) as pdf:
    for page in pdf.pages:
        text = page.extract_text()
        if text:
            full_text += "\n" + text

# Structure: extraire par niveau puis matière
levels_subjects = {
    '6ème': ['Mathématiques', 'Français', 'Anglais'],
    '5ème': ['Mathématiques', 'Français', 'Anglais'],
    '4ème': ['Mathématiques', 'Français', 'Anglais'],
    '3ème': ['Mathématiques', 'Français', 'Anglais']
}

data = defaultdict(lambda: defaultdict(list))

# Pattern pour extraire exercices avec numérotation
# "Exercice X.Y - Titre ... (contenu jusqu'à Exercice suivant ou fin de matière)"

for level in levels_subjects:
    # Chercher section du niveau (ex: "CAHIER 6ÈME" ou "MATHÉMATIQUES (6ème)")
    level_pattern = r'CAHIER\s+' + re.escape(level.upper()) + r'|' + level.upper()
    
    if re.search(level_pattern, full_text, re.IGNORECASE):
        print(f"✅ Niveau trouvé: {level}")
        
        for subject in levels_subjects[level]:
            # Chercher section matière
            subject_pattern = r'(MATHÉMATIQUES|FRANÇAIS|ANGLAIS)\s*\(' + re.escape(level) + r'\)|' + re.escape(subject) + r'\s*\(' + re.escape(level) + r'\)'
            
            if re.search(subject_pattern, full_text, re.IGNORECASE):
                print(f"  ✅ Matière trouvée: {subject}")
                
                # Chercher les exercices dans cette section
                # Pattern: "Exercice X.Y - Titre" suivi du contenu
                ex_pattern = r'Exercice\s+(\d+\.\d+)\s+-\s+([^\n]+)\n([\s\S]*?)(?=Exercice\s+\d+\.\d+|(?:MATHÉMATIQUES|FRANÇAIS|ANGLAIS)(?:\s+\(|$))'
                
                matches = re.findall(ex_pattern, full_text, re.IGNORECASE)
                
                if matches:
                    print(f"    📝 {len(matches)} exercices trouvés")
                    for num, title, content in matches[:3]:
                        print(f"       - {num}: {title.strip()[:50]}")

# Export en JSON
output_file = r'd:\Hostinger\public_html\moncoachscolaire\tools\pdf_extracted.json'
print(f"\n✅ Extraction complète. Sauvegarde en {output_file}")

# Analyser la couverture
print("\n📊 Couverture estimée:")
ex_count = len(re.findall(r'Exercice\s+\d+\.\d+', full_text, re.IGNORECASE))
print(f"  Total d'exercices détectés: {ex_count}")

level_count = sum(1 for level in levels_subjects if re.search(r'CAHIER\s+' + level.upper(), full_text, re.IGNORECASE))
print(f"  Niveaux couverts: {level_count}/4")

print("\n✅ Analyse terminée.")
