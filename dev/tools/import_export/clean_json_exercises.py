import json
import sys
from collections import OrderedDict

# Usage: python clean_json_exercises.py <input.json> <output.json>
def clean_exercises(input_path, output_path):
    with open(input_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    seen = set()
    cleaned = []
    for ex in data:
        identifier = ex.get('Identifier')
        if identifier and identifier not in seen:
            cleaned.append(ex)
            seen.add(identifier)
        # Si on veut loguer les doublons, décommenter :
        # else:
        #     print(f"Doublon ignoré : {identifier}")

    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(cleaned, f, ensure_ascii=False, indent=2)
    print(f"Nettoyage terminé. {len(cleaned)} exercices uniques écrits dans {output_path}")

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python clean_json_exercises.py <input.json> <output.json>")
        sys.exit(1)
    clean_exercises(sys.argv[1], sys.argv[2])
