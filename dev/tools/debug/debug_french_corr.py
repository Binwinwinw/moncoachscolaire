#!/usr/bin/env python3
import re

with open(r'd:\Hostinger\public_html\moncoachscolaire\exercices\college\4eme exercices&correction.md', 'r', encoding='utf-8') as f:
    md = f.read()

# Extraire Corrections
pattern = r'##\s+Corrections\s*([\s\S]*?)(?=\n##\s+|\Z)'
match = re.search(pattern, md, re.IGNORECASE)
if match:
    corrections = match.group(1)
    
    # Chercher Français - Réponses
    pattern2 = r'###\s+FRAN[ÇC]AIS\s+-\s+R[ÉE]PONSES([\s\S]*?)(?=\n###\s+|\Z)'
    match2 = re.search(pattern2, corrections, re.IGNORECASE)
    if match2:
        fr_corr = match2.group(1)
        print(f"Section FRANÇAIS - Réponses trouvée ({len(fr_corr)} chars)\n")
        print("Aperçu:\n", fr_corr[:1000])
        
        # Chercher les exercices
        pattern3 = r'\*?\*?Exercice\s+([0-9]+\.[0-9]+)\s*:\*?\*?\s*\n?([\s\S]*?)(?=\n\*?\*?Exercice|\Z)'
        matches = re.findall(pattern3, fr_corr, re.IGNORECASE)
        print(f"\n\nExercices trouvés: {len(matches)}")
        for num, ans in matches[:2]:
            print(f"  {num}: {ans[:100]}")
    else:
        print("❌ Section FRANÇAIS - Réponses NON trouvée")
