import json
import sys

file = sys.argv[1] if len(sys.argv) > 1 else 'db/json/college/3eme/exercices_3eme.json'

try:
    with open(file, encoding='utf-8') as f:
        json.load(f)
    print(f'Le fichier {file} est un JSON valide.')
except json.JSONDecodeError as e:
    print(f'Erreur JSON dans {file} : {e}')
    print(f'Ligne fautive : {e.lineno}, colonne : {e.colno}')
    # Affiche la ligne fautive
    with open(file, encoding='utf-8') as f:
        lines = f.readlines()
        start = max(0, e.lineno-3)
        end = min(len(lines), e.lineno+2)
        for i in range(start, end):
            print(f'{i+1:4d}: {lines[i].rstrip()}')
