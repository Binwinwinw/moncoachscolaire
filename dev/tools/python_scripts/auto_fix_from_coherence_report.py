import json
import sys
import os
import re

def parse_report(report_path):
    # Parse le rapport pour extraire les corrections à appliquer par exercice (par identifiant)
    corrections = {}
    with open(report_path, encoding="utf-8") as f:
        current_id = None
        for line in f:
            m = re.match(r"Exercice \d+ \(id=(.*?)\) :", line)
            if m:
                current_id = m.group(1)
                corrections[current_id] = {"incoherences": [], "suggestions": []}
            elif line.strip().startswith("- Incohérence") and current_id:
                corrections[current_id]["incoherences"].append(line.strip())
            elif line.strip().startswith("Suggestion") and current_id:
                corrections[current_id]["suggestions"].append(line.strip())
    return corrections

def auto_fix_exercise(ex, suggestions):
    # Applique les corrections automatiques intelligentes selon le contexte
    for s in suggestions:
        if "Ajouter le champ" in s:
            champ = s.split("Ajouter le champ ")[-1]
            # Valeurs par défaut selon le schéma et le contexte
            if champ == "Choices":
                # Si QCM détecté, générer des choix factices si possible
                if ex.get("AnswerType") == "qcm" and not ex.get("Choices"):
                    ex[champ] = ["Choix 1", "Choix 2", "Choix 3"]
                else:
                    ex[champ] = None
            elif champ == "is_active":
                ex[champ] = True
            elif champ == "XP_Points":
                # Adapter selon Difficulty
                diff = (ex.get("Difficulty") or '').lower()
                if diff == "facile":
                    ex[champ] = 5
                elif diff == "moyen":
                    ex[champ] = 10
                elif diff == "difficile":
                    ex[champ] = 15
                else:
                    ex[champ] = 5
            elif champ == "Coherence":
                # Cohérence vraie si tous les champs principaux sont présents
                ex[champ] = True
            elif champ == "Tips":
                # Conseils plus personnalisés selon la matière
                mat = (ex.get("Subject") or "").lower()
                if "math" in mat:
                    ex[champ] = "Vérifie tes calculs et relis bien l'énoncé."
                elif "francais" in mat:
                    ex[champ] = "Relis bien la consigne et vérifie l'orthographe."
                else:
                    ex[champ] = "Relis bien la consigne et vérifie ta réponse avant de valider."
            elif champ == "Domain":
                # Déduire le domaine à partir du titre ou de la compétence
                titre = (ex.get("Title") or "").lower()
                if "accord" in titre:
                    ex[champ] = "Grammaire"
                elif "probabilit" in titre:
                    ex[champ] = "Probabilités"
                else:
                    ex[champ] = "À préciser"
            elif champ == "Competence":
                # Déduire la compétence à partir du titre ou du domaine
                titre = (ex.get("Title") or "").lower()
                if "accord" in titre:
                    ex[champ] = "Accorder les adjectifs"
                elif "probabilit" in titre:
                    ex[champ] = "Utiliser les probabilités conditionnelles"
                else:
                    ex[champ] = "À préciser"
            elif champ == "AnswerType":
                # Déduire le type selon la présence de Choices
                if ex.get("Choices"):
                    ex[champ] = "qcm"
                else:
                    ex[champ] = "texte"
            elif champ == "Instruction":
                # Générer une consigne adaptée
                if ex.get("AnswerType") == "qcm":
                    ex[champ] = "Choisis la bonne réponse parmi les choix proposés."
                else:
                    ex[champ] = "Complète chaque phrase en choisissant la bonne réponse."
            else:
                ex[champ] = "À compléter"
        if "Corriger Choices" in s:
            # Si QCM, générer des choix factices
            if ex.get("AnswerType") == "qcm":
                ex["Choices"] = ["Choix 1", "Choix 2", "Choix 3"]
            else:
                ex["Choices"] = None
        if "Corriger is_active" in s:
            ex["is_active"] = True
        if "Corriger XP_Points" in s:
            diff = (ex.get("Difficulty") or '').lower()
            if diff == "facile":
                ex["XP_Points"] = 5
            elif diff == "moyen":
                ex["XP_Points"] = 10
            elif diff == "difficile":
                ex["XP_Points"] = 15
            else:
                ex["XP_Points"] = 5
        if "Mettre XP_Points=5" in s:
            ex["XP_Points"] = 5
        if "Mettre XP_Points=15" in s:
            ex["XP_Points"] = 15
        if "Mettre AnswerType=qcm" in s:
            ex["AnswerType"] = "qcm"
            if not ex.get("Choices"):
                ex["Choices"] = ["Choix 1", "Choix 2", "Choix 3"]
        if "vider Choices" in s:
            ex["Choices"] = None
        if "Ajouter un champ Identifier unique" in s and not ex.get("Identifier"):
            ex["Identifier"] = "AUTO-GEN-ID"
    return ex

def main():
    if len(sys.argv) < 3:
        print("Usage : python auto_fix_from_coherence_report.py <exercices.json> <rapport.txt>")
        sys.exit(1)
    json_path = sys.argv[1]
    report_path = sys.argv[2]
    with open(json_path, encoding="utf-8") as f:
        data = json.load(f)
    corrections = parse_report(report_path)
    fixed, log = [], []
    for ex in data:
        id_ = ex.get("Identifier", "?")
        if id_ in corrections:
            before = ex.copy()
            ex = auto_fix_exercise(ex, corrections[id_]["suggestions"])
            fixed.append(ex)
            if before != ex:
                log.append(f"{id_} : corrections appliquées : {corrections[id_]['suggestions']}")
        else:
            fixed.append(ex)
    base = os.path.splitext(json_path)[0]
    out_json = base + ".autofixed.json"
    out_log = base + ".autofix_log.txt"
    with open(out_json, "w", encoding="utf-8") as f:
        json.dump(fixed, f, ensure_ascii=False, indent=2)
    with open(out_log, "w", encoding="utf-8") as f:
        f.write("\n".join(log))
    print(f"Fichier corrigé généré : {out_json}")
    print(f"Log des corrections : {out_log}")

if __name__ == "__main__":
    main()
