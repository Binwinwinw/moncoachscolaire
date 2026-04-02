import json
import os
from pathlib import Path

def harmonize_file(file_path):
    """
    Harmonizes a JSON file from the old structure to the new structure
    Old structure: Subject, Level, Title, Content, Identifier, etc.
    New structure: identifier, classe, niveau, matiere, titre, content, etc.
    """
    with open(file_path, 'r', encoding='utf-8') as f:
        try:
            data = json.load(f)
        except json.JSONDecodeError:
            print(f"Could not decode JSON in file: {file_path}")
            return
    
    # Check if the file is already in the new format
    if isinstance(data, list) and len(data) > 0 and 'matiere' in data[0]:
        print(f"File {file_path} is already in the new format, skipping...")
        return
    
    # Transform the data to the new format
    transformed_data = []
    
    for item in data:
        if not isinstance(item, dict):
            continue
            
        # Create a new item with the harmonized structure
        new_item = {
            "identifier": item.get("Identifier", ""),
            "course_id": None,
            "classe": item.get("Level", ""),
            "niveau": item.get("Level", ""),
            "matiere": item.get("Subject", ""),
            "type": "exercice",  # Default, could be inferred from filename
            "titre": item.get("Title", ""),
            "domaine": item.get("Domain", ""),
            "competence": item.get("Competence", ""),
            "difficulty": item.get("Difficulty", ""),
            "content": item.get("Content", ""),
            "instruction": item.get("Instruction", ""),
            "answer": item.get("Answer", ""),
            "answerType": item.get("AnswerType", ""),
            "choices": item.get("Choices", []),
            "tips": item.get("Tips", ""),
            "source": None
        }
        
        # Determine type based on filename
        filename = os.path.basename(file_path).lower()
        if 'corrige' in filename:
            new_item['type'] = 'corrige'
        elif 'cours' in filename:
            new_item['type'] = 'cours'
        else:
            new_item['type'] = 'exercice'
            
        transformed_data.append(new_item)
    
    # Write the transformed data back to the file
    with open(file_path, 'w', encoding='utf-8') as f:
        json.dump(transformed_data, f, ensure_ascii=False, indent=2)
    
    print(f"Harmonized file: {file_path}")

def main():
    # First process all files in the college directory
    directory = Path("d:/Hostinger/public_html/moncoachscolaire/dev/tools/json/college")

    # Find all JSON files in the directory and subdirectories
    json_files = directory.rglob("*.json")

    for json_file in json_files:
        # Skip the clean files as they are already harmonized
        if '_clean' in str(json_file):
            print(f"Skipping clean file: {json_file}")
            continue

        print(f"Processing: {json_file}")
        harmonize_file(str(json_file))

    # Then process the large backup file
    large_file_path = "d:/Hostinger/public_html/moncoachscolaire/db/json/exercices_backup_normalized.json"
    print(f"Processing large backup file: {large_file_path}")
    harmonize_file(large_file_path)

if __name__ == "__main__":
    main()