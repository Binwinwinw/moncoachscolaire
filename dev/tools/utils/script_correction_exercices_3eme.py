import json
import re
import sys
import os

# Nettoyage HTML générique
def clean_html(text):
    if not isinstance(text, str):
        return text
    return re.sub(r'<[^>]+>', '', text).replace('\n', ' ').strip()

def is_already_processed(filename):
    return filename.endswith('_ok.json')

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
        if exo.get('Choices') and isinstance(exo['Choices'], list):
            exo['Choices'] = [clean_html(c) for c in exo['Choices']]
        else:
            exo['Choices'] = exo.get('Choices', None)
        exo['Domain'] = clean_html(exo.get('Domain', ''))
        exo['Coherence'] = True
        new_data.append(exo)
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(new_data, f, ensure_ascii=False, indent=2)

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python script_correction_exercices_3eme.py input.json output.json")
        sys.exit(1)
    process_exercices(sys.argv[1], sys.argv[2])
