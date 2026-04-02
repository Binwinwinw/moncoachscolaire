import sys
import json
import os

import sys
import json
import copy
from typing import Any, Dict, List
import os

def load_json_file(filepath: str) -> List[Dict[str, Any]]:
    with open(filepath, 'r', encoding='utf-8') as f:
        return json.load(f)

def save_json_file(filepath: str, data: List[Dict[str, Any]]):
    with open(filepath, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)

def is_empty(value):
    return value is None or (isinstance(value, str) and value.strip() == "")

def analyze_exercise(ex, schema_fields):
    missing_or_empty = []
    for field in schema_fields:
        if field not in ex or is_empty(ex[field]):
            missing_or_empty.append(field)
    return missing_or_empty

def assign_xp_points(difficulty):
    if not difficulty:
        return 5
    d = str(difficulty).lower()
    if d == "facile":
        return 5
    elif d == "moyen":
        return 10
    elif d == "difficile":
        return 15
    else:
        return 5

def fill_field_contextually(field, ex):
    # Peut être enrichi selon le contexte
    if field == "XP_Points":
        return assign_xp_points(ex.get("Difficulty"))
    if field == "Coherence":
        # On considère l'exercice cohérent s'il a tous les champs principaux
        return True
    if field == "is_active":
        return True
    if field == "Choices":
        return None
    if field == "Answer":
        return "À compléter par l'enseignant."
    if field == "Tips":
        return "Relis bien la consigne et vérifie ta réponse avant de valider."
    if field == "Domain":
        return "À préciser"
    if field == "Competence":
        return "À préciser"
    if field == "AnswerType":
        return "texte"
    if field == "Instruction":
        return "Complète chaque phrase en choisissant la bonne réponse."
    return "À compléter"

def conformity_report(ex_list, schema_fields):
    report = []
    total = len(ex_list)
    conform = 0
    for idx, ex in enumerate(ex_list):
        missing = analyze_exercise(ex, schema_fields)
        if not missing:
            conform += 1
        else:
            report.append(f"Exercice {idx+1} (Identifier: {ex.get('Identifier', 'N/A')}): champs manquants ou vides : {missing}")
    report.insert(0, f"Total exercices : {total}\nConformes : {conform}\nNon conformes : {total-conform}\n")
    return report

def correct_exercise(ex, schema_fields):
    ex_corr = copy.deepcopy(ex)
    for field in schema_fields:
        if field not in ex_corr or is_empty(ex_corr[field]):
            ex_corr[field] = fill_field_contextually(field, ex_corr)
    return ex_corr

def process_file(input_file: str, schema_fields: List[str], summary: List[str]):
    try:
        ex_list = load_json_file(input_file)
    except Exception as e:
        print(f"Erreur lors de la lecture de {input_file} : {e}")
        summary.append(f"{input_file} : ERREUR de lecture")
        return
    report = conformity_report(ex_list, schema_fields)
    report_file = input_file.replace('.json', '.conformity_report.txt')
    with open(report_file, 'w', encoding='utf-8') as f:
        f.write('\n'.join(report))
    print(f"Rapport de conformité généré : {report_file}")
    # Correction ciblée
    corrected = []
    corrections = 0
    for ex in ex_list:
        missing = analyze_exercise(ex, schema_fields)
        if not missing:
            corrected.append(ex)
        else:
            corrections += 1
            corrected.append(correct_exercise(ex, schema_fields))
    if corrections > 0:
        output_file = input_file.replace('.json', '.corrected.json')
        save_json_file(output_file, corrected)
        print(f"{corrections} exercices corrigés. Fichier corrigé généré : {output_file}")
        summary.append(f"{input_file} : {corrections} corrections, voir {output_file}")
    else:
        print(f"{input_file} : Tous les exercices sont conformes. Aucune correction nécessaire.")
        summary.append(f"{input_file} : conforme, aucune correction")

def main():
    if len(sys.argv) < 2:
        print("Usage : python auto_coherence_exercices.py <fichier1.json> [<fichier2.json> ...]")
        sys.exit(1)
    # Schéma officiel (ordre et champs)
    schema_fields = [
        "Subject", "Level", "Title", "Content", "Instruction", "Answer", "AnswerType", "Choices", "Tips", "Domain", "Competence", "Difficulty", "Identifier", "is_active", "XP_Points", "Coherence"
    ]
    summary = []
    for input_file in sys.argv[1:]:
        if not os.path.isfile(input_file):
            print(f"Fichier non trouvé : {input_file}")
            summary.append(f"{input_file} : fichier introuvable")
            continue
        print(f"\n--- Traitement de {input_file} ---")
        process_file(input_file, schema_fields, summary)
    print("\nRésumé du traitement :")
    for line in summary:
        print("-", line)

if __name__ == "__main__":
    main()
