import json
import sys
import re
from collections import defaultdict

# Table de correspondance matière → mots-clés → type d'exercice (coherence)
MAPPING = {
    "Francais": {
        r"conjugaison|imparfait|pass[ée] simple|verbe": "Conjugaison",
        r"grammaire|accord|nature|fonction": "Grammaire",
        r"orthographe": "Orthographe",
        r"compr[ée]hension|texte|question": "Compréhension écrite",
        r"expression|r[ée]daction|r[ée]dige": "Expression écrite",
    },
    "Anglais": {
        r"vocabulary|vocabulaire": "Vocabulaire",
        r"grammar|grammaire": "Grammaire",
        r"reading|compr[ée]hension|texte": "Compréhension écrite",
        r"writing|expression": "Expression écrite",
        r"oral|listening|audio": "Compréhension orale",
    },
    "Mathematiques": {
        r"calcul|num[ée]rique|fraction|d[ée]cimal": "Calcul numérique",
        r"g[ée]om[ée]trie|figure|triangle|cercle": "Géométrie",
        r"probl[èe]me": "Problème",
        r"fonction": "Fonctions",
    },
    "Sciences": {
        r"physique": "Physique",
        r"chimie": "Chimie",
        r"biologie|svt|cellule|vivant": "SVT",
        r"exp[ée]rience|m[ée]thode": "Méthodologie scientifique",
    },
    "Histoire-Geographie": {
        r"histoire": "Histoire",
        r"g[ée]ographie": "Géographie",
        r"civilisation": "Civilisations",
        r"guerre|conflit": "Guerres et conflits",
    },
}

def guess_type(subject, title, instruction):
    subject = subject.strip().capitalize()
    mapping = MAPPING.get(subject, {})
    text = f"{title} {instruction}".lower()
    for pattern, typ in mapping.items():
        if re.search(pattern, text):
            return typ
    return "Autre"

def main():
    if len(sys.argv) < 2:
        print("Usage: python auto_coherence_type.py <fichier_exercices.json>")
        sys.exit(1)
    path = sys.argv[1]
    with open(path, encoding="utf-8") as f:
        data = json.load(f)
    changed = False
    for ex in data:
        subject = ex.get("Subject", "")
        title = ex.get("Title", "")
        instruction = ex.get("Instruction", "")
        typ = guess_type(subject, title, instruction)
        if ex.get("Coherence") != typ:
            ex["Coherence"] = typ
            changed = True
    if changed:
        with open(path, "w", encoding="utf-8") as f:
            json.dump(data, f, ensure_ascii=False, indent=2)
        print(f"Fichier mis à jour avec les types d'exercice dans [Coherence] : {path}")
    else:
        print("Aucune modification nécessaire.")

if __name__ == "__main__":
    main()
