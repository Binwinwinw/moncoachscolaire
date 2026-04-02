#!/usr/bin/env python3
import re

with open(r'd:\Hostinger\public_html\moncoachscolaire\exercices\college\4eme exercices&correction.md', 'r', encoding='utf-8') as f:
    md = f.read()

# Chercher les sections
for heading in ['MATHÉMATIQUES', 'FRANÇAIS', 'ANGLAIS', 'Corrections']:
    pattern = r'##\s+' + re.escape(heading) + r'\s*(.+?)(?=\n##\s+|\Z)'
    match = re.search(pattern, md, re.IGNORECASE | re.DOTALL)
    if match:
        section = match.group(1)[:300]
        print(f"✅ Section '{heading}' trouvée ({len(match.group(1))} chars)")
        print(f"   Aperçu: {section}\n")
    else:
        print(f"❌ Section '{heading}' introuvable\n")

# Chercher les exercices dans mathématiques
print("\n--- Recherche d'exercices dans MATHÉMATIQUES ---")
pattern = r'##\s+MATHÉMATIQUES\s*(.+?)(?=\n##\s+|\Z)'
match = re.search(pattern, md, re.IGNORECASE | re.DOTALL)
if match:
    math_section = match.group(1)
    ex_pattern = r'####\s+Exercice\s+([0-9]+\.[0-9]+)\s+-\s+([^\n]+)'
    exercises = re.findall(ex_pattern, math_section, re.IGNORECASE)
    print(f"Exercices trouvés: {len(exercises)}")
    for num, title in exercises[:3]:
        print(f"  {num}: {title}")
