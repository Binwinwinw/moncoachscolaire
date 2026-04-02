import json
import re

def clean_html(text):
    if not isinstance(text, str):
        return text
    # Remove all HTML tags
    return re.sub(r'<[^>]+>', '', text).replace('\n', ' ').strip()

def enrich_choices(exo):
    # Exemples d'enrichissement automatique (à adapter selon le type)
    if exo['Identifier'].startswith('ANG-BAC-GRAMMAIRE-001'):
        return ["would pass", "passed", "will pass"]
    if exo['Identifier'].startswith('ANG-BAC-GRAMMAIRE-002'):
        return ["have I", "I have"]
    if exo['Identifier'].startswith('ANG-BAC-GRAMMAIRE-003'):
        return ["be", "is", "was"]
    if exo['Identifier'].startswith('ANG-BAC-GRAMMAIRE-004'):
        return ["was ready.", "is ready."]
    if exo['Identifier'].startswith('ANG-BAC-GRAMMAIRE-005'):
        return ["Knowing the answer, he smiled.", "He smiled, knowing the answer."]
    if exo['Identifier'].startswith('ANG-BAC-LEXIQUE-012'):
        return ["issue", "question"]
    # Si déjà des choices valides, les garder
    if exo.get('Choices') and isinstance(exo['Choices'], list):
        return [clean_html(c) for c in exo['Choices']]
    return None

def correct_domain(exo):
    # Correction automatique du champ Domain
    d = exo.get('Domain', '').lower()
    if 'condition' in d:
        return 'Grammaire - Conditionnels'
    if 'syntax' in d or 'inversion' in exo['Title'].lower():
        return 'Grammaire - Syntaxe'
    if 'subjonctif' in d or 'subjunctive' in exo['Title'].lower():
        return 'Grammaire - Subjonctif'
    if 'discours' in d or 'reported' in exo['Title'].lower():
        return 'Grammaire - Discours rapporté'
    if 'participial' in d or 'participle' in exo['Title'].lower():
        return 'Grammaire - Participial clause'
    if 'cohesion' in d or 'linker' in exo['Title'].lower():
        return 'Cohesion du discours'
    if 'lexique' in d or 'vocab' in exo['Title'].lower():
        return 'Lexique'
    if 'expression ecrite' in d or 'thesis' in exo['Title'].lower():
        return 'Expression écrite'
    if 'phonologie' in d or 'prononciation' in exo['Title'].lower():
        return 'Phonologie'
    if 'comprehension orale' in d or 'listening' in exo['Title'].lower():
        return 'Compréhension orale'
    if 'expression orale' in d or 'speaking' in exo['Title'].lower():
        return 'Expression orale'
    return clean_html(exo.get('Domain', ''))

def correct_instruction(exo):
    # Correction automatique de l'instruction (exemples, à adapter)
    t = exo['Title'].lower()
    if 'conditional' in t:
        return "Complète la phrase avec la forme correcte du verbe pour exprimer un conditionnel mixte (cause passée, conséquence présente)."
    if 'inversion' in t:
        return "Complète la phrase en respectant l'inversion du sujet et de l'auxiliaire après un adverbe négatif."
    if 'subjunctive' in t:
        return "Complète la phrase avec la base verbale pour exprimer le subjonctif après un verbe de recommandation."
    if 'reported speech' in t:
        return "Transforme la phrase au discours indirect en appliquant le backshift (décalage des temps) après un verbe de parole au passé."
    if 'participle' in t:
        return "Réécris la phrase en utilisant une proposition participiale (participe présent) pour exprimer la cause."
    if 'linker' in t or 'concession' in t:
        return "Donne un connecteur logique exprimant la concession (ex : although, despite, etc.)."
    if 'nominalisation' in t:
        return "Transforme la phrase en utilisant la nominalisation du verbe (verbe → nom)."
    if 'passive' in t:
        return "Complète la phrase en utilisant la voix passive avec un verbe de rumeur (reporting verb)."
    if 'gerund' in t or 'infinitive' in t:
        return "Complète la phrase avec la forme correcte du verbe après 'look forward to'."
    if 'question tag' in t:
        return "Complète la phrase avec le bon question tag (tag question) en anglais."
    if 'false friend' in t:
        return "Explique le sens réel du mot anglais entre guillemets (faux-ami)."
    if 'collocation' in t:
        return "Complète la collocation anglaise courante avec le mot approprié."
    if 'hedging' in t:
        return "Donne un verbe qui permet de nuancer une affirmation dans un contexte académique."
    if 'thesis' in t:
        return "Complète la phrase pour expliquer le rôle d'une thesis statement dans un essai."
    if 'cleft' in t:
        return "Complète la phrase en respectant la structure de mise en relief (cleft sentence) en anglais."
    if 'coherence' in t:
        return "Cite un élément essentiel pour assurer la cohérence d'un essai (IELTS/TOEFL)."
    if 'listening' in t or 'inference' in t:
        return "Explique ce que signifie 'inférer' dans le contexte de la compréhension orale."
    if 'rebuttal' in t:
        return "Propose une formulation polie pour exprimer un désaccord lors d'une prise de parole."
    if 'prononciation' in t or 'ed' in t:
        return "Indique la terminaison phonétique correcte pour le verbe régulier au passé simple."
    if 'emphasis' in t:
        return "Donne un adverbe d'emphase adapté à l'adjectif proposé."
    return clean_html(exo.get('Instruction', ''))

def process_exercices(input_path, output_path):
    with open(input_path, encoding='utf-8') as f:
        data = json.load(f)
    new_data = []
    for exo in data:
        exo['Content'] = clean_html(exo.get('Content', ''))
        exo['Instruction'] = correct_instruction(exo)
        exo['Answer'] = clean_html(exo.get('Answer', ''))
        exo['Domain'] = correct_domain(exo)
        exo['Choices'] = enrich_choices(exo)
        if exo['Choices'] == None:
            exo['Choices'] = None
        exo['Coherence'] = True
        new_data.append(exo)
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(new_data, f, ensure_ascii=False, indent=2)

# Utilisation
process_exercices(
    'd:/Hostinger/public_html/moncoachscolaire/db/json/bac/exercices_bac_anglais_enriched.json',
    'd:/Hostinger/public_html/moncoachscolaire/db/json/bac/exercices_bac_anglais_ok.json'
)
