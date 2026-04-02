import json
import sys
import os
import re

def suggest_title(ex):
    title = ex.get("Title", "").lower()
    content = ex.get("Content", "").lower()
    subject = ex.get("Subject", "").lower()
    # Passive voice
    if "passive" in title or "passive" in content:
        return "Transforme les phrases à la voix passive"
    # If clauses
    if "if clause" in title or "if" in content:
        return "Complète les phrases avec la bonne forme du verbe (if clauses)"
    # Opinion essay
    if "opinion" in title or "essay" in title:
        return "Rédige ou traduis des phrases d'opinion en anglais"
    # Email
    if "email" in title or "email" in content:
        return "Rédige un email simple à un correspondant"
    # Grammaire inversion
    if "inversion" in title or "emphase" in title:
        return "Réécris les phrases en mettant l'accent sur l'élément souligné"
    # Subjonctif
    if "subjonctif" in title or "subjunctive" in content:
        return "Complète les phrases avec la forme correcte du verbe (subjonctif)"
    # Essay argumentatif
    if "essay argumentatif" in title or "argumentatif" in content:
        return "Rédige un essai argumentatif sur le sujet proposé"
    # Opinion
    if "exprimer une opinion" in title or "opinion" in content:
        return "Traduis ou rédige des phrases d'opinion"
    # Default
    return ex.get("Title", "")

def suggest_instruction(ex):
    title = ex.get("Title", "").lower()
    content = ex.get("Content", "").lower()
    # Passive voice
    if "passive" in title or "passive" in content:
        return "Transforme chaque phrase à la voix passive."
    # If clauses
    if "if clause" in title or "if" in content:
        return "Complète chaque phrase avec la bonne forme du verbe selon le type de condition."
    # Opinion essay
    if "opinion" in title or "essay" in title:
        return "Rédige ou traduis la phrase d'opinion demandée."
    # Email
    if "email" in title or "email" in content:
        return "Rédige un email court à un correspondant sur ta vie au collège."
    # Grammaire inversion
    if "inversion" in title or "emphase" in title:
        return "Réécris la phrase en mettant l'accent sur l'élément souligné."
    # Subjonctif
    if "subjonctif" in title or "subjunctive" in content:
        return "Complète la phrase avec la forme correcte du verbe pour le subjonctif."
    # Essay argumentatif
    if "essay argumentatif" in title or "argumentatif" in content:
        return "Rédige un essai argumentatif structuré (introduction, développement, conclusion)."
    # Opinion
    if "exprimer une opinion" in title or "opinion" in content:
        return "Traduis la phrase en anglais en exprimant ton opinion."
    # Default
    return ex.get("Instruction", "")

def main():
    if len(sys.argv) < 2:
        print("Usage: python harmonize_titles_instructions.py <fichier_exercices.json>")
        sys.exit(1)
    path = sys.argv[1]
    with open(path, encoding="utf-8") as f:
        data = json.load(f)
    for ex in data:
        ex["Title"] = suggest_title(ex)
        ex["Instruction"] = suggest_instruction(ex)
    # Générer le nom de sortie
    basename = os.path.basename(path)
    match = re.match(r"exercice-(\w+)-(\w+)_enriched.json", basename, re.I)
    if match:
        level, subject = match.groups()
        outname = f"exercice-{level.lower()}-{subject.lower()}_ok.json"
    else:
        outname = basename.replace("_enriched", "_ok")
    outpath = os.path.join(os.path.dirname(path), outname)
    with open(outpath, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    print(f"Fichier harmonisé écrit : {outpath}")

if __name__ == "__main__":
    main()
