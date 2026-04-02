import json
import sys
import os

# Mots-clés pour l'identification
KEYWORDS_CORRIGE = ["corrigé", "correction", "réponse", "solutions", "corrige"]
KEYWORDS_COURS = ["cours", "leçon", "rappel", "astuce", "méthode", "résumé", "notions", "fiche"]
KEYWORDS_EXERCICE = ["exercice", "question", "complète", "problème", "trouve", "calcule", "transforme", "associe", "relie", "entoure", "souligne"]

# Détection par mots-clés dans Title et Content

def detect_type(obj):
    title = obj.get("Title", "").lower()
    content = obj.get("Content", "").lower()
    # Corrigé prioritaire
    if any(k in title for k in KEYWORDS_CORRIGE) or any(k in content for k in KEYWORDS_CORRIGE):
        return "corrige"
    # Cours ensuite
    if any(k in title for k in KEYWORDS_COURS) or any(k in content for k in KEYWORDS_COURS):
        return "cours"
    # Exercice si consigne ou question
    if any(k in title for k in KEYWORDS_EXERCICE) or any(k in content for k in KEYWORDS_EXERCICE):
        return "exercice"
    # Heuristique : si Content long (> 300) et pas de réponse, probablement un cours
    if len(content) > 300 and not obj.get("Answer"):
        return "cours"
    # Heuristique : si Answer long (> 200) et Content court, probablement un corrigé
    if len(obj.get("Answer", "")) > 200 and len(content) < 100:
        return "corrige"
    # Par défaut, exercice
    return "exercice"

def main():
    if len(sys.argv) < 2:
        print("Usage : python split_6eme_exercices.py <fichier_6eme.json>")
        sys.exit(1)
    input_file = sys.argv[1]
    with open(input_file, encoding="utf-8") as f:
        data = json.load(f)
    exercices, corriges, cours = [], [], []
    log_ambigus = []
    for idx, obj in enumerate(data):
        t = detect_type(obj)
        if t == "exercice":
            exercices.append(obj)
        elif t == "corrige":
            corriges.append(obj)
        elif t == "cours":
            cours.append(obj)
        else:
            log_ambigus.append(f"Objet {idx+1} non classé : {obj.get('Title', '?')}")
    base = os.path.splitext(input_file)[0]
    with open(base + ".exercices.json", "w", encoding="utf-8") as f:
        json.dump(exercices, f, ensure_ascii=False, indent=2)
    with open(base + ".corriges.json", "w", encoding="utf-8") as f:
        json.dump(corriges, f, ensure_ascii=False, indent=2)
    with open(base + ".cours.json", "w", encoding="utf-8") as f:
        json.dump(cours, f, ensure_ascii=False, indent=2)
    print(f"Exercices : {len(exercices)} | Corrigés : {len(corriges)} | Cours : {len(cours)}")
    if log_ambigus:
        with open(base + ".split_ambigus.log", "w", encoding="utf-8") as f:
            f.write("\n".join(log_ambigus))
        print(f"Attention : {len(log_ambigus)} objets non classés. Voir {base}.split_ambigus.log")

if __name__ == "__main__":
    main()
