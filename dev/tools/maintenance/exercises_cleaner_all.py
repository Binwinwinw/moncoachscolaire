import json
import re
from pathlib import Path

SOURCE = Path(__file__).parent.parent.parent / 'db/json/all_exercises_clean.json'
OUTPUT = Path(__file__).parent.parent.parent / 'db/json/all_exercises_clean_enriched.json'

# 1. Vérification de cohérence
def check_coherence(ex):
    fields = ['Subject','Level','Title','Content','Instruction','Answer','AnswerType','Choices']
    return all(f in ex and ex[f] not in [None, ''] for f in fields)

# 2. Enrichissement de l'instruction
def enrich_instruction(ex):
    if not ex.get('Instruction') or ex['Instruction'] == ex.get('Content'):
        return 'Complète chaque phrase en choisissant la bonne réponse.'
    return ex['Instruction']

# 3. Génération d'identifiant unique
_domain_map = {}
def get_domain_keyword(domain, title):
    if domain and domain.strip():
        return domain.split()[0].upper()
    return (title.split()[0] if title else 'GEN').upper()

def build_identifier(ex):
    subject = ex.get('Subject','GEN').upper()
    level = ex.get('Level','GEN').upper()
    domain = get_domain_keyword(ex.get('Domain',''), ex.get('Title',''))
    if domain not in _domain_map:
        _domain_map[domain] = 1
    num = f"{_domain_map[domain]:03d}"
    _domain_map[domain] += 1
    return f"{subject}-{level}-{domain}-{num}"

# 4. Calcul du niveau de difficulté
def get_difficulty(ex):
    content = re.sub('<.*?>', '', ex.get('Content','')).lower()
    answer = re.sub('<.*?>', '', ex.get('Answer','')).lower()
    choices = ex.get('Choices')
    if any(w in content for w in ['explique','justifie','démontre']):
        return 'difficile'
    if choices and isinstance(choices, list) and len(choices) > 4:
        return 'moyen'
    if len(content) < 200 and len(answer) < 100:
        return 'facile'
    return 'moyen'

# 5. Déduction du type de réponse
def get_answertype(ex):
    answer = re.sub('<.*?>', '', ex.get('Answer','')).lower()
    choices = ex.get('Choices')
    if choices and isinstance(choices, list) and len(choices) > 0:
        return 'choix_multiple'
    if 'calcule' in answer or 'calcul' in answer:
        return 'calcule'
    if 'vrai' in answer or 'faux' in answer:
        return 'case_a_cocher'
    return 'texte'

# 6. Enrichir Domain et Competence automatiquement
def enrich_domain(title):
    if not title:
        return 'Sciences'
    t = title.lower()
    if 'chimie' in t or 'reaction' in t:
        return 'Chimie'
    if 'energie' in t or 'thermo' in t:
        return 'Physique'
    if 'electricite' in t:
        return 'Physique'
    if 'onde' in t or 'vibration' in t:
        return 'Physique'
    if 'biologie' in t or 'génetique' in t or 'adn' in t:
        return 'Biologie'
    if 'math' in t:
        return 'Mathématiques'
    if 'francais' in t or 'grammaire' in t or 'conjugaison' in t:
        return 'Français'
    if 'histoire' in t or 'geo' in t:
        return 'Histoire-Géographie'
    return 'Sciences'

def enrich_competence(title):
    if not title:
        return 'Compétence scientifique générale.'
    t = title.lower()
    if 'chimie' in t or 'reaction' in t:
        return 'Comprendre les réactions chimiques et équilibrer une équation.'
    if 'energie' in t or 'thermo' in t:
        return 'Comprendre les principes de conservation de l’énergie.'
    if 'electricite' in t:
        return 'Maîtriser les lois de l’électricité.'
    if 'onde' in t or 'vibration' in t:
        return 'Comprendre la propagation des ondes.'
    if 'biologie' in t or 'génetique' in t or 'adn' in t:
        return 'Comprendre la structure et la fonction de l’ADN.'
    if 'math' in t:
        return 'Résoudre des problèmes mathématiques.'
    if 'francais' in t or 'grammaire' in t or 'conjugaison' in t:
        return 'Maîtriser les bases du français écrit.'
    if 'histoire' in t or 'geo' in t:
        return 'Comprendre les faits historiques et géographiques.'
    return 'Compétence scientifique générale.'

# 7. Générer Choices si possible (à partir des parenthèses dans Content)
def extract_choices(content):
    matches = re.findall(r'\(([^)]+)\)', content)
    choices = set()
    for m in matches:
        for c in m.split('/'):
            c = c.strip()
            if c:
                choices.add(c)
    return list(choices) if choices else None

def main():
    with open(SOURCE, encoding='utf-8') as f:
        data = json.load(f)
    enriched = []
    for ex in data:
        ex = dict(ex)  # copy
        # 1. Cohérence
        ex['Coherence'] = check_coherence(ex)
        # 2. Instruction
        ex['Instruction'] = enrich_instruction(ex)
        # 3. Identifier
        if not ex.get('Identifier'):
            ex['Identifier'] = build_identifier(ex)
        # 4. Difficulty
        ex['Difficulty'] = get_difficulty(ex)
        # 5. AnswerType
        ex['AnswerType'] = get_answertype(ex)
        # 6. Domain/Competence
        if not ex.get('Domain'):
            ex['Domain'] = enrich_domain(ex.get('Title',''))
        if not ex.get('Competence'):
            ex['Competence'] = enrich_competence(ex.get('Title',''))
        # 7. Choices
        if not ex.get('Choices'):
            ch = extract_choices(ex.get('Content',''))
            if ch:
                ex['Choices'] = ch
        enriched.append(ex)
    with open(OUTPUT, 'w', encoding='utf-8') as f:
        json.dump(enriched, f, ensure_ascii=False, indent=2)
    print(f"Nettoyage et enrichissement terminés. Export : {OUTPUT}")

if __name__ == '__main__':
    main()
