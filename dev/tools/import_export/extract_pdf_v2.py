#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Extracteur PDF amélioré - parse section par section correctement
"""

import pdfplumber
import re
import json

pdf_path = r'd:\Hostinger\public_html\moncoachscolaire\exercices\Cahiers Complets Collège 6e-5e-4e-3e.pdf'

print("Extraction PDF avec parsing par matière...")

# Extraire tout le texte
pages_text = {}
with pdfplumber.open(pdf_path) as pdf:
    for i, page in enumerate(pdf.pages, 1):
        text = page.extract_text()
        if text:
            pages_text[i] = text

# Créer full text mais en gardant les délimiteurs
full = ''
for i in range(1, 23):
    if i in pages_text:
        full += f'\n[PAGE {i}]\n' + pages_text[i]

# Fonction pour extraire exercices d'une section
def extract_exercises(text, level, subject):
    """Extraire exercices d'une section de matière"""
    exercises = []
    
    # Pattern: "Exercice X.Y - Titre"
    pattern = r'Exercice\s+(\d+\.\d+)\s+-\s+([^\n]+)'
    
    for match in re.finditer(pattern, text):
        num, title = match.groups()
        
        # Contenu = depuis la fin du titre jusqu'au prochain exercice ou fin de section
        start_pos = match.end()
        
        # Chercher prochain exercice
        next_match = re.search(r'\nExercice\s+\d+\.\d+', text[start_pos:])
        if next_match:
            content = text[start_pos:start_pos + next_match.start()]
        else:
            content = text[start_pos:]
        
        # Nettoyer le contenu
        content = content.strip()
        # Garder juste les 200 premiers caractères pour le contenu
        if len(content) > 200:
            content = content[:200] + "..."
        
        exercises.append({
            'numero': num,
            'titre': title.strip(),
            'contenu': content,
            'reponse': '',  # À remplir depuis les corrections
            'level': level,
            'subject': subject
        })
    
    return exercises

# Extraire les corrections d'une section
def extract_answers(text):
    """Extraire les réponses avec format: Exercice X.Y : réponse"""
    answers = {}
    
    # Pattern: "Exercice X.Y : réponse jusqu'à fin ligne"
    pattern = r'Exercice\s+(\d+\.\d+)\s*:\s*([^\n]+)'
    
    for match in re.finditer(pattern, text):
        num, ans = match.groups()
        answers[num] = ans.strip()
    
    return answers

# Structure: par niveau, trouver sections exercices et corrections
levels_config = {
    '6ème': {
        'exercises': '1-7',
        'corrections': '7',
        'subjects_order': ['MATHÉMATIQUES', 'FRANÇAIS', 'ANGLAIS']
    },
    '5ème': {
        'exercises': '8-12',
        'corrections': '13',
        'subjects_order': ['MATHÉMATIQUES', 'FRANÇAIS', 'ANGLAIS']
    },
    '4ème': {
        'exercises': '14-17',
        'corrections': '18',
        'subjects_order': ['MATHÉMATIQUES', 'FRANÇAIS', 'ANGLAIS']
    },
    '3ème': {
        'exercises': '19-21',
        'corrections': '22',
        'subjects_order': ['MATHÉMATIQUES', 'FRANÇAIS', 'ANGLAIS']
    }
}

exercises_by_level = {}

for level, config in levels_config.items():
    print(f"\nTraitement {level}...")
    
    exercises_by_level[level] = {
        'Mathématiques': [],
        'Français': [],
        'Anglais': []
    }
    
    # Récupérer texte exercices
    ex_pages = config['exercises'].split('-')
    start_page = int(ex_pages[0])
    end_page = int(ex_pages[1])
    
    exercises_text = ''
    for p in range(start_page, end_page + 1):
        if p in pages_text:
            exercises_text += '\n' + pages_text[p]
    
    # Récupérer texte corrections
    correction_page = int(config['corrections'])
    corrections_text = pages_text.get(correction_page, '')
    
    # Extraire corrections par sujet
    answers_by_subject = {}
    for subject in config['subjects_order']:
        # Chercher section "Français - Réponses" ou "FRANÇAIS - RéPONSES"
        subject_ans = re.search(
            rf'({subject}|FRANÇAIS|FRANÇAIS)\s*-\s*R[ée]ponses([\s\S]*?)(?={subject}|FRANÇAIS|$)',
            corrections_text,
            re.IGNORECASE
        )
        if subject_ans:
            ans_section = subject_ans.group(2)
            answers_by_subject[subject] = extract_answers(ans_section)
        else:
            answers_by_subject[subject] = {}
    
    # Extraire exercices par sujet
    for subject in config['subjects_order']:
        # Chercher section matière
        subject_pattern = rf'{subject}\s*\({level}\)'
        subject_match = re.search(subject_pattern, exercises_text, re.IGNORECASE)
        
        if subject_match:
            start = subject_match.end()
            
            # Fin = prochaine matière
            next_subject = None
            for s in config['subjects_order']:
                if s != subject:
                    next_match = re.search(
                        rf'{s}\s*\({level}\)',
                        exercises_text[start:],
                        re.IGNORECASE
                    )
                    if next_match:
                        next_subject = next_match
                        break
            
            if next_subject:
                section = exercises_text[start:start + next_match.start()]
            else:
                section = exercises_text[start:]
            
            # Extraire exercices
            exs = extract_exercises(section, level, subject)
            
            # Ajouter réponses
            subject_display = 'Français' if subject == 'FRANÇAIS' else ('Mathématiques' if subject == 'MATHÉMATIQUES' else 'Anglais')
            answers = answers_by_subject.get(subject, {})
            
            for ex in exs:
                ex['reponse'] = answers.get(ex['numero'], 'Non fournie')
                exercises_by_level[level][subject_display].append(ex)
            
            print(f"  {subject_display}: {len(exs)} exercices")

# Export
output = {
    'metadata': {
        'source': 'PDF Cahiers Complets',
        'niveaux': list(exercises_by_level.keys())
    },
    'data': {}
}

total = 0
for level, subjects in exercises_by_level.items():
    output['data'][level] = {}
    for subject, exs in subjects.items():
        output['data'][level][subject] = exs
        total += len(exs)

output_path = r'd:\Hostinger\public_html\moncoachscolaire\tools\pdf_data.json'
with open(output_path, 'w', encoding='utf-8') as f:
    json.dump(output, f, ensure_ascii=False, indent=2)

print(f"\nTotal exercices: {total}")
print(f"Sauvegarde: {output_path}")
