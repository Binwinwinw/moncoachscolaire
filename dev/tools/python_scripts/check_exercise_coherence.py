import json
import sys
import os

def detect_type_exercice(ex):
    # Détection simple du type d'exercice (à enrichir)
    if ex.get('AnswerType') == 'qcm' or (ex.get('Choices') and len(ex['Choices']) > 0):
        return 'qcm'
    if ex.get('AnswerType') == 'association':
        return 'association'
    if ex.get('AnswerType') == 'texte':
        return 'texte'
    # Heuristique : si Content ou Instruction contient 'associe', 'relie', etc.
    content = (ex.get('Content') or '').lower() + ' ' + (ex.get('Instruction') or '').lower()
    if 'associe' in content or 'relie' in content:
        return 'association'
    if 'choisis' in content or 'coche' in content or 'qcm' in content:
        return 'qcm'
    return 'texte'

def check_coherence(ex):
    incoherences = []
    suggestions = []
    # Champs obligatoires selon le schéma officiel
    schema_fields = [
        "Subject", "Level", "Title", "Content", "Instruction", "Answer", "AnswerType", "Choices", "Tips", "Domain", "Competence", "Difficulty", "Identifier", "is_active", "XP_Points", "Coherence"
    ]
    # Vérification de la présence et du type de chaque champ
    for field in schema_fields:
        if field not in ex:
            incoherences.append(f"Champ manquant : {field}")
            suggestions.append(f"Ajouter le champ {field}")
        else:
            # Vérification du type attendu pour certains champs
            if field == "Choices" and ex[field] is not None and not isinstance(ex[field], list):
                incoherences.append("Choices doit être une liste ou null")
                suggestions.append("Corriger Choices (liste attendue)")
            if field == "is_active" and not isinstance(ex[field], bool):
                incoherences.append("is_active doit être booléen (true/false)")
                suggestions.append("Corriger is_active (booléen attendu)")
            if field == "XP_Points" and not isinstance(ex[field], int):
                incoherences.append("XP_Points doit être un entier")
                suggestions.append("Corriger XP_Points (entier attendu)")
    # 1. Type de réponse vs Choices
    if ex.get('AnswerType') == 'texte' and ex.get('Choices'):
        incoherences.append('AnswerType=texte mais Choices non vide')
        suggestions.append('Mettre AnswerType=qcm ou vider Choices')
    if ex.get('AnswerType') in ['qcm', 'association'] and not ex.get('Choices'):
        incoherences.append(f"AnswerType={ex.get('AnswerType')} mais Choices vide")
        suggestions.append('Ajouter des Choices ou corriger AnswerType')
    # 2. Cohérence Domaine/Compétence (exemple)
    if ex.get('Domain') and ex.get('Competence'):
        if ex['Domain'].lower() not in ex['Competence'].lower():
            incoherences.append('Domain et Competence semblent non concordants')
            suggestions.append('Vérifier le champ Competence')
    # 3. XP_Points vs Difficulty
    xp = ex.get('XP_Points')
    diff = (ex.get('Difficulty') or '').lower()
    if diff == 'facile' and xp and xp > 5:
        incoherences.append('XP_Points trop élevé pour difficulté facile')
        suggestions.append('Mettre XP_Points=5')
    if diff == 'difficile' and xp and xp < 10:
        incoherences.append('XP_Points trop faible pour difficulté difficile')
        suggestions.append('Mettre XP_Points=15')
    # 4. Coherence vs type détecté
    type_detecte = detect_type_exercice(ex)
    if ex.get('AnswerType') and ex.get('AnswerType') != type_detecte:
        incoherences.append(f"AnswerType={ex.get('AnswerType')} mais type détecté={type_detecte}")
        suggestions.append(f"Vérifier AnswerType ou le contenu de l'exercice")
    # 5. Identifiant unique
    if not ex.get('Identifier'):
        incoherences.append('Identifiant manquant')
        suggestions.append('Ajouter un champ Identifier unique')
    return incoherences, suggestions

def main():
    if len(sys.argv) < 2:
        print("Usage : python check_exercise_coherence.py <fichier_exercices.json>")
        sys.exit(1)
    path = sys.argv[1]
    with open(path, encoding="utf-8") as f:
        data = json.load(f)
    rapport = []
    for idx, ex in enumerate(data):
        incoh, sugg = check_coherence(ex)
        if incoh:
            rapport.append({
                'index': idx+1,
                'Identifier': ex.get('Identifier', '?'),
                'Title': ex.get('Title', '?'),
                'incoherences': incoh,
                'suggestions': sugg
            })
    base = os.path.splitext(path)[0]
    rapport_path = base + '.coherence_report.txt'
    with open(rapport_path, 'w', encoding='utf-8') as f:
        for r in rapport:
            f.write(f"Exercice {r['index']} (id={r['Identifier']}) : {r['Title']}\n")
            for inc in r['incoherences']:
                f.write(f"  - Incohérence : {inc}\n")
            for sug in r['suggestions']:
                f.write(f"    Suggestion : {sug}\n")
            f.write("\n")
    print(f"Rapport de cohérence généré : {rapport_path} ({len(rapport)} exercices à vérifier)")

if __name__ == "__main__":
    main()
