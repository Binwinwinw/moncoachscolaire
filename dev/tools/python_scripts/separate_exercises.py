#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script de séparation automatique des exercices en 3 fichiers:
- exercice-XXX-nom.md (questions uniquement)
- exercice-XXX-nom-corrige.md (corrections détaillées)
- exercice-XXX-nom-cours.md (ressources pédagogiques)
"""

import os
import re
import sys
from pathlib import Path
from typing import Dict, List, Tuple, Optional

# Fichiers à exclure du traitement automatique
EXCLUDED_FILES = [
    'README.md',
    'INDEX.md',
    'RESUME-PHASE2.md',
    'CORRECTIONS_APPLIQUEES.md',
    'COHERENCE_EXERCICES.md',
    'esxercices-5eme.md',
    'exercices_perplexity_comet.md'
]

# Patterns pour détecter les sections
CORRECTION_MARKERS = [
    r'##\s*Correction\s*$',
    r'##\s*✅\s*Answer Key',
    r'##\s*🔑\s*Answer Key',
    r'<details>\s*<summary>.*answer.*</summary>',
    r'###\s*\[Rappel\]',
    r'###\s*a\)',  # Format exercice maths
]

RESOURCES_MARKERS = [
    r'##\s*📚\s*Learn More',
    r'##\s*Rappel\s*:',
    r'##\s*Astuce',
    r'##\s*Pour aller plus loin',
    r'##\s*Gamification',
    r'##\s*Métadonnées',
    r'##\s*🎬\s*Additional Resources',
    r'##\s*💡\s*Did you know',
]

def log(message: str, level: str = "INFO"):
    """Log un message avec niveau"""
    prefix = {
        "INFO": "ℹ️",
        "SUCCESS": "✅",
        "WARNING": "⚠️",
        "ERROR": "❌"
    }
    print(f"{prefix.get(level, 'ℹ️')} {message}")

def should_process(filepath: Path) -> bool:
    """Vérifie si un fichier doit être traité"""
    filename = filepath.name
    
    # Exclure fichiers spéciaux
    if filename in EXCLUDED_FILES:
        return False
    
    # Exclure fichiers avec &correction dans le nom
    if '&correction' in filename.lower():
        return False
    
    # Traiter uniquement exercice-*.md (pas les -corrige.md ou -cours.md déjà créés)
    if not filename.startswith('exercice-'):
        return False
    
    if filename.endswith('-corrige.md') or filename.endswith('-cours.md'):
        return False
    
    return True

def find_section_start(lines: List[str], markers: List[str]) -> Optional[int]:
    """Trouve le début d'une section basée sur les marqueurs"""
    for i, line in enumerate(lines):
        for marker in markers:
            if re.search(marker, line, re.IGNORECASE):
                return i
    return None

def extract_sections(content: str) -> Tuple[str, str, str]:
    """
    Extrait les 3 sections d'un exercice:
    - questions (header + énoncé + questions)
    - corrections (réponses + explications)
    - ressources (rappels, astuces, gamification, métadonnées)
    """
    lines = content.split('\n')
    
    # Trouver le début de la section correction
    correction_start = find_section_start(lines, CORRECTION_MARKERS)
    
    # Trouver le début de la section ressources
    resources_start = find_section_start(lines, RESOURCES_MARKERS)
    
    # Si pas de section détectée, tout est dans questions
    if correction_start is None and resources_start is None:
        return content.strip(), "", ""
    
    # Déterminer les limites
    if correction_start is not None and resources_start is not None:
        if correction_start < resources_start:
            questions = '\n'.join(lines[:correction_start]).strip()
            corrections = '\n'.join(lines[correction_start:resources_start]).strip()
            resources = '\n'.join(lines[resources_start:]).strip()
        else:
            questions = '\n'.join(lines[:resources_start]).strip()
            resources = '\n'.join(lines[resources_start:correction_start]).strip()
            corrections = '\n'.join(lines[correction_start:]).strip()
    elif correction_start is not None:
        questions = '\n'.join(lines[:correction_start]).strip()
        corrections = '\n'.join(lines[correction_start:]).strip()
        resources = ""
    elif resources_start is not None:
        questions = '\n'.join(lines[:resources_start]).strip()
        corrections = ""
        resources = '\n'.join(lines[resources_start:]).strip()
    else:
        questions = content.strip()
        corrections = ""
        resources = ""
    
    return questions, corrections, resources

def get_file_title(content: str) -> str:
    """Extrait le titre du fichier (première ligne # ...)"""
    lines = content.split('\n')
    for line in lines:
        if line.strip().startswith('#') and not line.strip().startswith('##'):
            return line.strip('# ').strip()
    return "Exercice"

def create_resources_section(filepath: Path) -> str:
    """Crée le bloc de renvoi vers corrigé et cours"""
    basename = filepath.stem  # Sans extension
    
    return f"""
## 📌 Resources

Pour consulter la **correction détaillée** de cet exercice :  
→ Voir le fichier **[{basename}-corrige.md]({basename}-corrige.md)**

Pour accéder aux **ressources pédagogiques** (rappels, astuces, pour aller plus loin) :  
→ Voir le fichier **[{basename}-cours.md]({basename}-cours.md)**
"""

def extract_frontmatter(content: str) -> Tuple[str, str]:
    """Extrait le frontmatter (header avec Level, Subject, etc.) et le reste"""
    lines = content.split('\n')
    
    # Chercher le premier titre #
    title_idx = None
    for i, line in enumerate(lines):
        if line.strip().startswith('#') and not line.strip().startswith('##'):
            title_idx = i
            break
    
    if title_idx is None:
        return "", content
    
    # Tout jusqu'au premier ## ou ### est considéré comme frontmatter
    frontmatter_end = title_idx + 1
    for i in range(title_idx + 1, len(lines)):
        if lines[i].strip().startswith('##'):
            frontmatter_end = i
            break
    
    frontmatter = '\n'.join(lines[:frontmatter_end]).strip()
    rest = '\n'.join(lines[frontmatter_end:]).strip()
    
    return frontmatter, rest

def process_exercise(filepath: Path, dry_run: bool = False) -> Dict[str, str]:
    """Traite un fichier exercice et retourne le résultat"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Extraire les sections
        questions, corrections, resources = extract_sections(content)
        
        # Extraire le frontmatter
        frontmatter, questions_body = extract_frontmatter(questions)
        
        # Titre du fichier
        title = get_file_title(content)
        
        # Construire le fichier questions
        questions_file = frontmatter + '\n\n' + questions_body
        if corrections or resources:
            questions_file += '\n\n' + create_resources_section(filepath)
        
        # Construire le fichier corrigé
        corrige_file = ""
        if corrections:
            corrige_title = title + " - CORRIGÉ"
            corrige_file = frontmatter.replace(title, corrige_title) + '\n\n'
            corrige_file += "## 🔑 Correction Détaillée\n\n" + corrections
        
        # Construire le fichier cours
        cours_file = ""
        if resources:
            cours_title = title + " - Ressources Pédagogiques"
            cours_file = frontmatter.replace(title, cours_title) + '\n\n'
            cours_file += resources
        
        result = {
            'status': 'success',
            'questions': questions_file,
            'corrige': corrige_file,
            'cours': cours_file,
            'has_corrections': bool(corrections),
            'has_resources': bool(resources)
        }
        
        # Écrire les fichiers si pas en dry-run
        if not dry_run:
            # Fichier principal (questions)
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(questions_file)
            
            # Fichier corrigé
            if corrige_file:
                corrige_path = filepath.parent / f"{filepath.stem}-corrige.md"
                with open(corrige_path, 'w', encoding='utf-8') as f:
                    f.write(corrige_file)
            
            # Fichier cours
            if cours_file:
                cours_path = filepath.parent / f"{filepath.stem}-cours.md"
                with open(cours_path, 'w', encoding='utf-8') as f:
                    f.write(cours_file)
        
        return result
        
    except Exception as e:
        return {
            'status': 'error',
            'error': str(e)
        }

def main():
    """Fonction principale"""
    # Racine du projet
    root = Path(__file__).parent.parent
    exercises_dir = root / 'docs' / 'exercices'
    
    if not exercises_dir.exists():
        log(f"Répertoire {exercises_dir} introuvable", "ERROR")
        sys.exit(1)
    
    # Trouver tous les fichiers exercice-*.md
    all_files = list(exercises_dir.rglob('exercice-*.md'))
    
    # Filtrer les fichiers à traiter
    files_to_process = [f for f in all_files if should_process(f)]
    
    log(f"Fichiers trouvés : {len(all_files)}")
    log(f"Fichiers à traiter : {len(files_to_process)}")
    log(f"Fichiers exclus : {len(all_files) - len(files_to_process)}")
    
    # Statistiques
    stats = {
        'total': 0,
        'success': 0,
        'with_corrections': 0,
        'with_resources': 0,
        'errors': 0,
        'atypical': []
    }
    
    # Traiter chaque fichier
    for filepath in files_to_process:
        stats['total'] += 1
        rel_path = filepath.relative_to(exercises_dir)
        
        log(f"Traitement de {rel_path}...", "INFO")
        
        result = process_exercise(filepath, dry_run=False)
        
        if result['status'] == 'success':
            stats['success'] += 1
            if result['has_corrections']:
                stats['with_corrections'] += 1
            if result['has_resources']:
                stats['with_resources'] += 1
            
            # Marquer comme atypique si pas de correction ni ressources
            if not result['has_corrections'] and not result['has_resources']:
                stats['atypical'].append(str(rel_path))
                log(f"  ⚠️  Fichier atypique (pas de correction/ressources détectées)", "WARNING")
            else:
                log(f"  ✅ Traité avec succès", "SUCCESS")
        else:
            stats['errors'] += 1
            log(f"  ❌ Erreur: {result.get('error', 'Inconnue')}", "ERROR")
    
    # Rapport final
    print("\n" + "="*60)
    log("RAPPORT FINAL", "INFO")
    print("="*60)
    log(f"Fichiers traités : {stats['total']}")
    log(f"Succès : {stats['success']}")
    log(f"Avec corrections : {stats['with_corrections']}")
    log(f"Avec ressources : {stats['with_resources']}")
    log(f"Erreurs : {stats['errors']}")
    
    if stats['atypical']:
        log(f"\nFichiers atypiques ({len(stats['atypical'])}) :", "WARNING")
        for f in stats['atypical']:
            print(f"  - {f}")
    
    print("="*60)

if __name__ == "__main__":
    main()
