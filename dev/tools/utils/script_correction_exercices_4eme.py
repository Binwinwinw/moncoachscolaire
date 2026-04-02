import json
import re
import sys
import os
import random

def clean_html(text):
    if not isinstance(text, str):
        return text
    return re.sub(r'<[^>]+>', '', text).replace('\n', ' ').strip()

def is_already_processed(filename):
    return filename.endswith('_ok.json')

def extract_enumerated_answers(answer):
    # Détecte les réponses du type "1) ... 2) ..." ou "a) ... b) ..."
    if not isinstance(answer, str):
        return None
    items = re.findall(r'(?:\d+\)|[a-z]\))\s*([^\d\)a-z].*?)(?=(?:\d+\)|[a-z]\)|$))', answer)
    if not items:
        # Variante : séparer par "e?" ou points-virgules
        items = re.split(r'\s*e\?\s*|;|\n', answer)
        items = [i.strip() for i in items if i.strip()]
    return items if len(items) > 1 else None

def is_vrai_faux_exercise(exo):
    # Détection simple : titre ou contenu contient "Vrai ou Faux" ou réponses V/F
    if 'vrai ou faux' in exo.get('Title', '').lower() or 'vrai ou faux' in exo.get('Content', '').lower():
        return True
    if isinstance(exo.get('Answer', ''), str) and re.search(r'\b[VF]\b', exo['Answer']):
        return True
    return False

def generate_distractors(correct, subject):
    # Distracteurs simples pour anglais/français
    if subject.lower() == "anglais":
        # Erreurs courantes de traduction
        return ["What is your age?", "Where is your house?", "How are you?"]
    if subject.lower() == "francais":
        return ["Quel est ton prénom ?", "Où travailles-tu ?", "Quel est ton numéro ?"]
    # Par défaut, permutations
    return random.sample([correct, "Réponse incorrecte 1", "Réponse incorrecte 2", "Réponse incorrecte 3"], 3)

def enrich_qcm(exo):
    answers = extract_enumerated_answers(exo.get('Answer', ''))
    if answers:
        if is_vrai_faux_exercise(exo):
            exo['AnswerType'] = 'qcm'
            exo['Choices'] = [['Vrai', 'Faux'] for _ in answers]
        else:
            exo['AnswerType'] = 'qcm'
            exo['Choices'] = []
            subject = exo.get('Subject', '')
            for ans in answers:
                distractors = generate_distractors(ans, subject)
                # Mélange la bonne réponse et les distracteurs
                choices = [ans] + distractors
                random.shuffle(choices)
                exo['Choices'].append(choices)
    return exo

def process_exercices(input_path, output_path):
    if is_already_processed(input_path):
        print(f"Le fichier {input_path} est déjà traité (_ok.json), opération ignorée.")
        return
    with open(input_path, encoding='utf-8') as f:
        data = json.load(f)
    new_data = []
    for exo in data:
        exo['Content'] = clean_html(exo.get('Content', ''))
        exo['Instruction'] = clean_html(exo.get('Instruction', ''))
        exo['Answer'] = clean_html(exo.get('Answer', ''))
        exo['Domain'] = clean_html(exo.get('Domain', ''))
        exo['Coherence'] = True
        # Ajout logique QCM auto
        exo = enrich_qcm(exo)
        if exo.get('Choices') is None and exo.get('Choices', None) is not None:
            if isinstance(exo['Choices'], list):
                exo['Choices'] = [clean_html(c) for c in exo['Choices']]
            else:
                exo['Choices'] = exo.get('Choices', None)
        new_data.append(exo)
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(new_data, f, ensure_ascii=False, indent=2)

def batch_process_all_json_in_folder(folder_path):
    for filename in os.listdir(folder_path):
        if filename.endswith('_enriched.json') and not filename.endswith('_ok.json'):
            input_path = os.path.join(folder_path, filename)
            output_path = input_path.replace('_enriched.json', '_ok.json')
            print(f"Traitement de {input_path} ...")
            process_exercices(input_path, output_path)

if __name__ == "__main__":
    if len(sys.argv) == 2 and sys.argv[1] == '--batch':
        batch_process_all_json_in_folder(os.path.dirname(__file__))
    elif len(sys.argv) == 3:
        process_exercices(sys.argv[1], sys.argv[2])
    else:
        print("Usage: python script_correction_exercices_4eme.py input.json output.json OU python script_correction_exercices_4eme.py --batch")
        sys.exit(1)
