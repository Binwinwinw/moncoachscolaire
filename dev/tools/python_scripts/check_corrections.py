#!/usr/bin/env python3
import re

with open(r'd:\Hostinger\public_html\moncoachscolaire\exercices\college\4eme exercices&correction.md', 'r', encoding='utf-8') as f:
    md = f.read()

# Extraire la section Corrections
pattern = r'##\s+Corrections\s*([\s\S]*?)(?=\n##\s+|\Z)'
match = re.search(pattern, md, re.IGNORECASE)
if match:
    corrections = match.group(1)
    print(f"Section Corrections trouvée ({len(corrections)} chars)\n")
    
    # Chercher les sous-sections
    for heading in ['Mathématiques', 'FRANÇAIS', 'Français', 'ANGLAIS', 'Anglais']:
        if re.search(r'###\s+' + re.escape(heading), corrections, re.IGNORECASE):
            print(f"✅ {heading} - Réponses trouvé")
        else:
            print(f"❌ {heading} - Réponses NON trouvé")
    
    print("\n--- Aperçu des premières 1000 chars de Corrections ---")
    print(corrections[:1000])
