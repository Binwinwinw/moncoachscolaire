#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Batch H — 1ère Numérique et Sciences Informatiques (NSI)
IDs 867–898 | 32 quizzes | 256 questions
Auteur : MonCoachScolaire
"""

import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
NSI1_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "nsi_1ere_quizzes")
NSI1_QUIZ_DIR = os.path.join(NSI1_OUTPUT_DIR, "quiz")
NSI1_ANSWERS_DIR = os.path.join(NSI1_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "nsi_1ere_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")
os.makedirs(NSI1_QUIZ_DIR, exist_ok=True)
os.makedirs(NSI1_ANSWERS_DIR, exist_ok=True)
os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)


def fix_mojibake_text(value):
    if not isinstance(value, str):
        return value

    markers = ("Ã", "Â", "â€", "â€™", "â€œ", "â€”", "â€“", "Å")
    if not any(marker in value for marker in markers):
        return value

    for legacy_encoding in ("latin-1", "cp1252"):
        try:
            repaired = value.encode(legacy_encoding).decode("utf-8")
            if repaired != value:
                value = repaired
        except UnicodeError:
            continue

    replacements = {
        "Ã©": "é", "Ã¨": "è", "Ãª": "ê", "Ã«": "ë", "Ã ": "à", "Ã¢": "â",
        "Ã´": "ô", "Ã»": "û", "Ã¹": "ù", "Ã®": "î", "Ã¯": "ï", "Ã§": "ç",
        "Ã‰": "É", "Ã€": "À", "Ã‡": "Ç", "Å“": "œ", "Â": "", "â€™": "’",
        "â€œ": "“", "â€\x9d": "”", "â€“": "–", "â€”": "—", "â€¦": "…",
    }
    for source, target in replacements.items():
        value = value.replace(source, target)
    return value


def normalize_text_payload(payload):
    if isinstance(payload, dict):
        return {key: normalize_text_payload(item) for key, item in payload.items()}
    if isinstance(payload, list):
        return [normalize_text_payload(item) for item in payload]
    if isinstance(payload, tuple):
        return tuple(normalize_text_payload(item) for item in payload)
    if isinstance(payload, str):
        return fix_mojibake_text(payload)
    return payload


def make_quiz(qid, title, subject, level, questions):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    questions = [{k: v for k, v in q.items() if k not in answer_keys} for q in questions]
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    runtime_questions = []

    for question in questions:
        qtype = str(question.get("type", "texte"))
        if qtype == "qcm":
            runtime_questions.append(
                {
                    "type": "qcm",
                    "question": str(question.get("question", "")),
                    "choices": list(question.get("options", [])),
                }
            )
        elif qtype == "vrai-faux":
            runtime_questions.append(
                {
                    "type": "vrai-faux",
                    "question": str(question.get("question", "")),
                }
            )
        else:
            runtime_questions.append(
                {
                    "type": "vrai-faux",
                    "question": str(question.get("question", "")),
                }
            )

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Série {qid}",
            "type": "quiz",
            "level": level,
            "subject": subject,
            "description": f"Diagnostic {subject} {level} : {title}",
            "status": "published",
            "created_at": created_at,
            "updated_at": created_at,
        },
        "quiz": {
            "title": title,
            "type": "quiz",
            "level": level,
            "subject": subject,
            "question_count": len(runtime_questions),
            "passing_score": 70,
            "time_limit_minutes": 15,
            "questions": runtime_questions,
        },
        "exercisenotion": [],
        "exerciseresponses": [],
    }


def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        qtype = str(q.get("type", "texte"))
        if qtype == "qcm":
            options = list(q.get("options", []))
            correct_answer = str(q.get("correct_option", q.get("correct_answer", "")))
            correct_index = options.index(correct_answer) if correct_answer in options else 0
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "qcm",
                "answer": correct_answer,
                "correct": correct_index,
                "correction": str(q.get("explanation", "")),
            })
        elif qtype == "vrai-faux":
            tf_source = q.get("correct", q.get("correct_answer", "faux"))
            tf_answer = "vrai" if str(tf_source).strip().lower() in {"true", "vrai", "1"} else "faux"
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": tf_answer,
                "correction": str(q.get("explanation", "")),
            })
        else:
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": str(q.get("correct_answer", "")),
                "correction": str(q.get("explanation", "")),
            })
    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - Série {qid}",
            "level": level,
            "subject": subject,
        },
        "quiz": {
            "title": title,
            "question_count": len(answers),
            "level": level,
            "subject": subject,
            "answers": answers,
        },
    }

# ─────────────────────────────────────────────────────────
# DONNÉES — 48 quizzes NSI 1ère (IDs 867–914)
# ─────────────────────────────────────────────────────────
quizzes_data = [

# ══════════════════════════════════════════════════════════
# BLOC 1 — REPRÉSENTATION DES DONNÉES (867–872)
# ══════════════════════════════════════════════════════════

(867, "Codage binaire des entiers", "NSI", "1ère", [
    {"id":"867_1","type":"qcm","question":"Quelle est la valeur décimale du nombre binaire 1011 ?",
     "options":["9","10","11","13"],
     "correct_answer":"11",
     "explanation":"1011 en binaire = 1×2³ + 0×2² + 1×2¹ + 1×2⁰ = 8 + 0 + 2 + 1 = 11."},
    {"id":"867_2","type":"vrai-faux","question":"Le nombre binaire 1111 vaut 15 en décimal.",
     "correct_answer":"Vrai",
     "explanation":"1111 = 8 + 4 + 2 + 1 = 15. C'est le plus grand entier sur 4 bits non signé."},
    {"id":"867_3","type":"vrai-faux","question":"Convertissez le nombre décimal 42 en binaire.",
     "correct_answer":"101010",
     "explanation":"42 = 32 + 8 + 2 = 2⁵ + 2³ + 2¹ = 101010 en binaire."},
    {"id":"867_4","type":"qcm","question":"Sur 8 bits non signés, quel est le plus grand entier représentable ?",
     "options":["127","255","256","128"],
     "correct_answer":"255",
     "explanation":"Sur n bits non signés, la plage est [0, 2ⁿ-1]. Sur 8 bits : 2⁸ - 1 = 255."},
    {"id":"867_5","type":"vrai-faux","question":"Le complément à deux permet de représenter les entiers négatifs en binaire.",
     "correct_answer":"Vrai",
     "explanation":"Le complément à deux est la méthode standard pour représenter les entiers signés en informatique."},
    {"id":"867_6","type":"vrai-faux","question":"Comment représente-t-on -5 en complément à deux sur 8 bits ?",
     "correct_answer":"11111011",
     "explanation":"+5 = 00000101. Inversion : 11111010. On ajoute 1 : 11111011. Vérification : 11111011 = -128+64+32+16+8+2+1 = -128+123 = -5."},
    {"id":"867_7","type":"qcm","question":"L'addition binaire de 0110 + 0101 donne :",
     "options":["1011","1010","1100","0111"],
     "correct_answer":"1011",
     "explanation":"0110 (6) + 0101 (5) = 1011 (11). On additionne bit par bit en propageant les retenues."},
    {"id":"867_8","type":"vrai-faux","question":"Un octet (byte) est composé de 8 bits.",
     "correct_answer":"Vrai",
     "explanation":"1 octet = 8 bits. Il peut stocker 2⁸ = 256 valeurs distinctes (0 à 255)."},
]),

(868, "Codage des caractères", "NSI", "1ère", [
    {"id":"868_1","type":"qcm","question":"Quelle est la table de codage historique des caractères utilisée en informatique ?",
     "options":["Unicode","ASCII","UTF-8","ISO-8859-1"],
     "correct_answer":"ASCII",
     "explanation":"L'ASCII (American Standard Code for Information Interchange, 1963) est la table historique sur 7 bits, codant 128 caractères."},
    {"id":"868_2","type":"vrai-faux","question":"L'UTF-8 est compatible avec l'ASCII pour les 128 premiers caractères.",
     "correct_answer":"Vrai",
     "explanation":"Les 128 premiers caractères Unicode sont identiques à l'ASCII, et leur encodage UTF-8 utilise le même octet."},
    {"id":"868_3","type":"vrai-faux","question":"Quelle est la différence entre Unicode et UTF-8 ?",
     "correct_answer":"Unicode est un standard qui attribue un point de code unique à chaque caractère de toutes les langues. UTF-8 est un encodage (façon de stocker ces points de code) à longueur variable (1 à 4 octets).",
     "explanation":"Unicode définit 'quoi' ; UTF-8 définit 'comment' stocker en mémoire."},
    {"id":"868_4","type":"qcm","question":"En ASCII, le code décimal de la lettre 'A' majuscule est :",
     "options":["61","65","97","41"],
     "correct_answer":"65",
     "explanation":"'A' = 65 en ASCII. 'a' minuscule = 97. La différence est de 32 (= 2⁵), ce qui facilite la conversion majuscule/minuscule."},
    {"id":"868_5","type":"vrai-faux","question":"L'encodage Latin-1 (ISO-8859-1) peut représenter les caractères accentués français.",
     "correct_answer":"Vrai",
     "explanation":"ISO-8859-1 est une extension de l'ASCII sur 8 bits incluant les caractères accentués de l'Europe occidentale (é, è, à, ù…)."},
    {"id":"868_6","type":"vrai-faux","question":"Pourquoi UTF-8 est-il devenu le standard dominant sur le web ?",
     "correct_answer":"UTF-8 est universel (supporte toutes les langues), rétrocompatible avec ASCII, économe (1 octet pour les caractères latins), et a été adopté comme standard par W3C et la plupart des systèmes. Il représente aujourd'hui plus de 97 % du web.",
     "explanation":"Sa compatibilité ASCII et son efficacité pour les textes occidentaux l'ont rendu omniprésent."},
    {"id":"868_7","type":"qcm","question":"En Python 3, la fonction ord('A') retourne :",
     "options":["'65'","65","0x41","'A'"],
     "correct_answer":"65",
     "explanation":"ord() retourne le point de code Unicode d'un caractère. ord('A') = 65. La fonction inverse est chr(65) = 'A'."},
    {"id":"868_8","type":"vrai-faux","question":"Unicode peut représenter plus d'un million de caractères différents.",
     "correct_answer":"Vrai",
     "explanation":"Unicode définit plus de 1 100 000 points de code (U+0000 à U+10FFFF), couvrant toutes les écritures mondiales, emojis inclus."},
]),

(869, "Codage des images", "NSI", "1ère", [
    {"id":"869_1","type":"qcm","question":"Un pixel est :",
     "options":["Un fichier image","L'unité élémentaire d'une image numérique","Un format de compression","Un type de capteur"],
     "correct_answer":"L'unité élémentaire d'une image numérique",
     "explanation":"Un pixel (picture element) est le plus petit élément d'une image numérique, caractérisé par sa position et sa couleur."},
    {"id":"869_2","type":"vrai-faux","question":"En mode RGB, chaque pixel est codé par trois valeurs (rouge, vert, bleu).",
     "correct_answer":"Vrai",
     "explanation":"Le modèle RGB (Red, Green, Blue) utilise 3 canaux. Avec 8 bits par canal, on a 256 niveaux par couleur, soit 256³ = 16 777 216 couleurs possibles."},
    {"id":"869_3","type":"vrai-faux","question":"Calculez la taille non compressée d'une image RGB de 800×600 pixels (en octets et Mo).",
     "correct_answer":"800 × 600 × 3 = 1 440 000 octets ≈ 1,44 Mo (en utilisant 3 octets par pixel en RGB 24 bits).",
     "explanation":"Taille = largeur × hauteur × nombre de canaux × octets par canal."},
    {"id":"869_4","type":"qcm","question":"La résolution d'une image numérique se mesure en :",
     "options":["Mégaoctets","DPI (dots per inch) ou PPI","Mégapixels uniquement","Hz"],
     "correct_answer":"DPI (dots per inch) ou PPI",
     "explanation":"DPI (ou PPI, pixels per inch) mesure la densité de pixels. Une image de 300 DPI est adaptée à l'impression, 72-96 DPI pour l'écran."},
    {"id":"869_5","type":"vrai-faux","question":"Le format PNG utilise une compression sans perte.",
     "correct_answer":"Vrai",
     "explanation":"PNG (Portable Network Graphics) utilise une compression lossless : aucune donnée n'est perdue. JPEG utilise une compression avec perte (lossy)."},
    {"id":"869_6","type":"vrai-faux","question":"Expliquez la différence entre compression avec perte et sans perte.",
     "correct_answer":"Compression sans perte (PNG, BMP, GIF) : l'image originale est parfaitement reconstituée. Compression avec perte (JPEG) : certaines données sont éliminées définitivement pour réduire la taille ; la qualité se dégrade à chaque re-compression.",
     "explanation":"JPEG est adapté aux photos ; PNG aux images avec texte, logos ou transparence."},
    {"id":"869_7","type":"qcm","question":"Une image en niveaux de gris sur 8 bits peut représenter :",
     "options":["8 nuances de gris","64 nuances","256 nuances","1024 nuances"],
     "correct_answer":"256 nuances",
     "explanation":"Sur 8 bits : 2⁸ = 256 niveaux de gris (0 = noir, 255 = blanc)."},
    {"id":"869_8","type":"vrai-faux","question":"Le format SVG est un format d'image vectorielle, pas matricielle.",
     "correct_answer":"Vrai",
     "explanation":"SVG (Scalable Vector Graphics) décrit les images par des formes géométriques (XML). Contrairement aux images matricielles (JPEG, PNG), il ne pixelise pas lors du zoom."},
]),

(870, "Codage des nombres flottants", "NSI", "1ère", [
    {"id":"870_1","type":"qcm","question":"La norme IEEE 754 définit le codage :",
     "options":["Des entiers signés","Des nombres à virgule flottante","Des caractères","Des booléens"],
     "correct_answer":"Des nombres à virgule flottante",
     "explanation":"IEEE 754 est le standard international pour la représentation des flottants en binaire (simple précision 32 bits, double précision 64 bits)."},
    {"id":"870_2","type":"vrai-faux","question":"En Python, le calcul 0.1 + 0.2 == 0.3 renvoie True.",
     "correct_answer":"Faux",
     "explanation":"En Python (et tout langage utilisant IEEE 754), 0.1 + 0.2 = 0.30000000000000004 à cause des erreurs d'arrondi binaires. Il faut utiliser round() ou math.isclose()."},
    {"id":"870_3","type":"vrai-faux","question":"Expliquez pourquoi 0.1 ne peut pas être représenté exactement en binaire.",
     "correct_answer":"0.1 en décimal est une fraction décimale périodique en base 2 : 0.0001100110011... (infinie). La mémoire étant finie, on tronque, introduisant une erreur d'arrondi.",
     "explanation":"Seules les fractions dont le dénominateur est une puissance de 2 sont représentables exactement en binaire."},
    {"id":"870_4","type":"qcm","question":"Un flottant en simple précision (float32) est stocké sur :",
     "options":["16 bits","32 bits","64 bits","128 bits"],
     "correct_answer":"32 bits",
     "explanation":"IEEE 754 simple précision : 1 bit signe + 8 bits exposant + 23 bits mantisse = 32 bits total."},
    {"id":"870_5","type":"vrai-faux","question":"La double précision (float64) offre plus de précision que la simple précision (float32).",
     "correct_answer":"Vrai",
     "explanation":"Float64 : 1+11+52 bits (mantisse de 52 bits ≈ 15-16 chiffres significatifs). Float32 : 1+8+23 bits (≈ 6-7 chiffres significatifs)."},
    {"id":"870_6","type":"vrai-faux","question":"Qu'est-ce que le dépassement de capacité (overflow) en virgule flottante ?",
     "correct_answer":"Un overflow se produit quand un calcul produit un résultat trop grand pour être représenté (supérieur à ~3.4×10³⁸ en float32). Le résultat est alors +inf ou -inf selon IEEE 754.",
     "explanation":"IEEE 754 définit des valeurs spéciales : +inf, -inf, NaN (Not a Number) pour gérer ces cas."},
    {"id":"870_7","type":"qcm","question":"En Python, quel est le type par défaut des nombres à virgule flottante ?",
     "options":["float32","float64","float128","decimal"],
     "correct_answer":"float64",
     "explanation":"Python utilise le type float, qui correspond à un double précision IEEE 754 64 bits (C double)."},
    {"id":"870_8","type":"vrai-faux","question":"NaN (Not a Number) est une valeur spéciale définie dans IEEE 754.",
     "correct_answer":"Vrai",
     "explanation":"NaN résulte d'opérations indéfinies (0/0, racine d'un négatif). Sa propriété remarquable : NaN != NaN est True."},
]),

(871, "Algèbre de Boole et portes logiques", "NSI", "1ère", [
    {"id":"871_1","type":"qcm","question":"Quelle est la valeur de True AND False en logique booléenne ?",
     "options":["True","False","None","Error"],
     "correct_answer":"False",
     "explanation":"L'opérateur AND (ET) ne retourne True que si les deux opérandes sont True."},
    {"id":"871_2","type":"vrai-faux","question":"L'opérateur OR retourne True si au moins un des opérandes est True.",
     "correct_answer":"Vrai",
     "explanation":"OR (OU logique) : True OR False = True. False OR False = False uniquement."},
    {"id":"871_3","type":"vrai-faux","question":"Construisez la table de vérité du XOR (OU exclusif) pour deux entrées A et B.",
     "correct_answer":"A=0,B=0 → 0 ; A=0,B=1 → 1 ; A=1,B=0 → 1 ; A=1,B=1 → 0. XOR est vrai si et seulement si les entrées sont différentes.",
     "explanation":"XOR (ou exclusif) est fondamental en cryptographie et en calcul de parité."},
    {"id":"871_4","type":"qcm","question":"La loi de De Morgan énonce que NOT(A AND B) est équivalent à :",
     "options":["NOT(A) AND NOT(B)","NOT(A) OR NOT(B)","A OR B","NOT(A) XOR NOT(B)"],
     "correct_answer":"NOT(A) OR NOT(B)",
     "explanation":"Première loi de De Morgan : ¬(A∧B) = ¬A∨¬B. Deuxième loi : ¬(A∨B) = ¬A∧¬B."},
    {"id":"871_5","type":"vrai-faux","question":"Une porte NAND est une combinaison de NOT et AND.",
     "correct_answer":"Vrai",
     "explanation":"NAND = NOT(AND). La porte NAND est universelle : on peut construire toutes les autres portes logiques avec des NAND."},
    {"id":"871_6","type":"vrai-faux","question":"Qu'est-ce qu'un circuit combinatoire ? Donnez un exemple.",
     "correct_answer":"Un circuit combinatoire est un circuit dont les sorties dépendent uniquement des entrées actuelles (pas de mémoire). Exemples : additionneur (calcule A+B), multiplexeur, décodeur.",
     "explanation":"Contraste avec les circuits séquentiels (bascules, registres) qui ont une mémoire interne."},
    {"id":"871_7","type":"qcm","question":"En Python, l'opérateur 'and' avec des entiers effectue :",
     "options":["Un ET bit à bit","Un ET logique (court-circuit)","Une addition","Une comparaison"],
     "correct_answer":"Un ET logique (court-circuit)",
     "explanation":"'and' en Python est logique et applique le court-circuit. Pour le ET bit à bit, on utilise l'opérateur '&'."},
    {"id":"871_8","type":"vrai-faux","question":"L'opération bit à bit 5 & 3 en Python donne 1.",
     "correct_answer":"Vrai",
     "explanation":"5 = 0101, 3 = 0011. 0101 & 0011 = 0001 = 1. Le & applique le ET bit à bit."},
]),

(872, "Hexadécimal et conversions", "NSI", "1ère", [
    {"id":"872_1","type":"qcm","question":"Dans le système hexadécimal, la lettre 'A' représente la valeur :",
     "options":["10","11","12","15"],
     "correct_answer":"10",
     "explanation":"Hexadécimal : 0-9 = 0-9, A=10, B=11, C=12, D=13, E=14, F=15."},
    {"id":"872_2","type":"vrai-faux","question":"0xFF en hexadécimal est égal à 255 en décimal.",
     "correct_answer":"Vrai",
     "explanation":"FF = 15×16 + 15 = 240 + 15 = 255. En Python, 0xFF == 255 est True."},
    {"id":"872_3","type":"vrai-faux","question":"Convertissez le nombre hexadécimal 2A en décimal et en binaire.",
     "correct_answer":"2A (hex) = 2×16 + 10 = 32 + 10 = 42 (décimal). 42 en binaire = 101010.",
     "explanation":"Chaque chiffre hexadécimal correspond exactement à 4 bits : 2 = 0010, A = 1010 → 00101010 = 42."},
    {"id":"872_4","type":"qcm","question":"En Python, hex(255) retourne :",
     "options":["'255'","'0xff'","'FF'","255"],
     "correct_answer":"'0xff'",
     "explanation":"hex() retourne une chaîne de caractères avec le préfixe '0x'. Pour obtenir 'FF', on peut faire hex(255)[2:].upper()."},
    {"id":"872_5","type":"vrai-faux","question":"Les couleurs web en CSS peuvent être exprimées en hexadécimal (ex : #FF5733).",
     "correct_answer":"Vrai",
     "explanation":"#RRGGBB en CSS : chaque paire hexadécimale code l'intensité d'une couleur (rouge, vert, bleu) de 00 à FF."},
    {"id":"872_6","type":"vrai-faux","question":"Pourquoi l'hexadécimal est-il souvent préféré au binaire en informatique ?",
     "correct_answer":"Chaque chiffre hexadécimal représente exactement 4 bits, ce qui rend l'hexadécimal beaucoup plus compact que le binaire (4× moins de chiffres). On passe facilement de l'un à l'autre sans calcul.",
     "explanation":"Ex : 11111111 (binaire) = FF (hex), bien plus lisible pour les adresses mémoire ou les codes couleur."},
    {"id":"872_7","type":"qcm","question":"Quel est le préfixe Python pour écrire un entier en hexadécimal dans le code ?",
     "options":["#","0b","0x","0o"],
     "correct_answer":"0x",
     "explanation":"0x pour hexadécimal, 0b pour binaire, 0o pour octal. Ex : 0xFF = 255, 0b1010 = 10, 0o17 = 15."},
    {"id":"872_8","type":"vrai-faux","question":"1 Ko (kilooctet) = 1000 octets en informatique.",
     "correct_answer":"Faux",
     "explanation":"En informatique, 1 Kio (kibioctet) = 2¹⁰ = 1024 octets. Confusion fréquente avec le SI (1 Ko = 1000). Les OS utilisent généralement 1024 (Kio), les fabricants de disques 1000 (Ko)."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 2 — TRAITEMENT ET STRUCTURES DE DONNÉES (873–878)
# ══════════════════════════════════════════════════════════

(873, "Listes et tableaux en Python", "NSI", "1ère", [
    {"id":"873_1","type":"qcm","question":"Quelle est la sortie de len([1, 2, 3, 4, 5]) en Python ?",
     "options":["4","5","6","[5]"],
     "correct_answer":"5",
     "explanation":"len() retourne le nombre d'éléments de la liste. [1,2,3,4,5] contient 5 éléments."},
    {"id":"873_2","type":"vrai-faux","question":"En Python, une liste peut contenir des éléments de types différents.",
     "correct_answer":"Vrai",
     "explanation":"Python est dynamiquement typé. Une liste peut mélanger int, str, float, d'autres listes, etc. : [1, 'hello', 3.14, True]."},
    {"id":"873_3","type":"vrai-faux","question":"Qu'est-ce que le slicing (découpage) de liste en Python ? Donnez un exemple.",
     "correct_answer":"Le slicing extrait une sous-liste avec la syntaxe liste[début:fin:pas]. Ex : lst = [0,1,2,3,4] ; lst[1:4] = [1,2,3] ; lst[::2] = [0,2,4] ; lst[::-1] = [4,3,2,1,0].",
     "explanation":"Le slicing ne modifie pas la liste originale ; il retourne une nouvelle liste."},
    {"id":"873_4","type":"qcm","question":"Quelle méthode ajoute un élément à la fin d'une liste Python ?",
     "options":[".add()","insert(0)","append()","push()"],
     "correct_answer":"append()",
     "explanation":"list.append(x) ajoute x à la fin. insert(i, x) insère à l'indice i. extend() fusionne avec une autre liste."},
    {"id":"873_5","type":"vrai-faux","question":"La compréhension de liste [x**2 for x in range(5)] produit [0, 1, 4, 9, 16].",
     "correct_answer":"Vrai",
     "explanation":"range(5) = [0,1,2,3,4]. En élevant chaque élément au carré : [0,1,4,9,16]."},
    {"id":"873_6","type":"vrai-faux","question":"Expliquez la différence entre une liste et un tableau (array) numpy.",
     "correct_answer":"La liste Python est dynamique, peut contenir des types mixtes, mais les opérations sont lentes en boucle. Le tableau numpy est homogène (même type), stocké de façon contiguë en mémoire, et supporte les opérations vectorisées très rapides. Numpy est préféré pour le calcul scientifique.",
     "explanation":"Pour des milliers d'éléments numériques, numpy est 10 à 100 fois plus rapide qu'une liste Python."},
    {"id":"873_7","type":"qcm","question":"En Python, lst = [0]*3 crée :",
     "options":["Une erreur","La liste [0, 0, 0]","La liste [0, 3]","La liste [0, 1, 2]"],
     "correct_answer":"La liste [0, 0, 0]",
     "explanation":"La multiplication d'une liste par n la répète n fois. [0]*3 = [0,0,0]."},
    {"id":"873_8","type":"vrai-faux","question":"lst.pop() supprime et retourne le dernier élément de la liste.",
     "correct_answer":"Vrai",
     "explanation":"pop() sans argument retire le dernier élément. pop(i) retire l'élément à l'indice i. remove(x) retire la première occurrence de la valeur x."},
]),

(874, "Dictionnaires en Python", "NSI", "1ère", [
    {"id":"874_1","type":"qcm","question":"Dans un dictionnaire Python, les clés doivent être :",
     "options":["Des entiers","Des chaînes de caractères","Des objets hachables (immuables)","Des listes"],
     "correct_answer":"Des objets hachables (immuables)",
     "explanation":"Les clés doivent être hachables : int, str, tuple (immuable) sont valides. Les listes (mutables) ne peuvent pas être clés."},
    {"id":"874_2","type":"vrai-faux","question":"L'accès à une clé inexistante dans un dictionnaire lève une KeyError.",
     "correct_answer":"Vrai",
     "explanation":"d['clé_inexistante'] lève KeyError. Pour éviter cela, utilisez d.get('clé_inexistante', valeur_par_défaut)."},
    {"id":"874_3","type":"vrai-faux","question":"Comment itérer sur les clés, les valeurs et les paires clé-valeur d'un dictionnaire ?",
     "correct_answer":"for k in d: (clés) ; for v in d.values(): (valeurs) ; for k, v in d.items(): (paires clé-valeur). d.keys() retourne les clés, d.values() les valeurs, d.items() les tuples (clé, valeur).",
     "explanation":"Ces méthodes retournent des vues dynamiques qui reflètent les modifications du dictionnaire."},
    {"id":"874_4","type":"qcm","question":"Quelle est la complexité temporelle moyenne de la recherche dans un dictionnaire Python ?",
     "options":["O(n)","O(log n)","O(1)","O(n²)"],
     "correct_answer":"O(1)",
     "explanation":"Les dictionnaires Python sont implémentés avec des tables de hachage. L'accès, l'insertion et la suppression sont en O(1) en moyenne."},
    {"id":"874_5","type":"vrai-faux","question":"En Python 3.7+, les dictionnaires conservent l'ordre d'insertion des clés.",
     "correct_answer":"Vrai",
     "explanation":"Depuis Python 3.7, le dictionnaire standard maintient l'ordre d'insertion. Avant, l'ordre n'était pas garanti."},
    {"id":"874_6","type":"vrai-faux","question":"Qu'est-ce qu'un dictionnaire par compréhension (dict comprehension) ? Donnez un exemple.",
     "correct_answer":"Un dict comprehension crée un dictionnaire en une expression. Ex : {x: x**2 for x in range(5)} = {0:0, 1:1, 2:4, 3:9, 4:16}.",
     "explanation":"Syntaxe : {clé: valeur for élément in itérable if condition}."},
    {"id":"874_7","type":"qcm","question":"La méthode dict.update() permet de :",
     "options":["Trier le dictionnaire","Fusionner un autre dictionnaire (ou itérable de paires) dans le dictionnaire","Supprimer une clé","Retourner toutes les valeurs"],
     "correct_answer":"Fusionner un autre dictionnaire (ou itérable de paires) dans le dictionnaire",
     "explanation":"d.update(d2) ajoute/modifie les clés de d avec celles de d2. En Python 3.9+, on peut aussi utiliser d | d2."},
    {"id":"874_8","type":"vrai-faux","question":"Un dictionnaire peut avoir deux clés identiques.",
     "correct_answer":"Faux",
     "explanation":"Les clés d'un dictionnaire sont uniques. Si on assigne deux fois la même clé, la deuxième valeur écrase la première."},
]),

(875, "Tuples et ensembles", "NSI", "1ère", [
    {"id":"875_1","type":"qcm","question":"Quelle est la principale différence entre un tuple et une liste en Python ?",
     "options":["Un tuple peut contenir des types différents","Un tuple est immuable (non modifiable après création)","Un tuple est plus lent","Un tuple ne supporte pas le slicing"],
     "correct_answer":"Un tuple est immuable (non modifiable après création)",
     "explanation":"Les tuples sont immuables : on ne peut pas ajouter, supprimer ou modifier leurs éléments. Ils sont plus rapides et peuvent servir de clés de dictionnaire."},
    {"id":"875_2","type":"vrai-faux","question":"En Python, (1,) est un tuple à un élément.",
     "correct_answer":"Vrai",
     "explanation":"La virgule est obligatoire pour créer un tuple à un élément. (1) est simplement l'entier 1 entre parenthèses ; (1,) est un tuple."},
    {"id":"875_3","type":"vrai-faux","question":"Expliquez le dépaquetage (unpacking) de tuples en Python avec un exemple.",
     "correct_answer":"Le dépaquetage affecte simultanément les éléments d'un tuple à plusieurs variables. Ex : a, b, c = (1, 2, 3) assigne a=1, b=2, c=3. Utile aussi pour échanger : a, b = b, a.",
     "explanation":"Le dépaquetage fonctionne aussi avec les listes et tout itérable."},
    {"id":"875_4","type":"qcm","question":"Un ensemble (set) Python est une collection :",
     "options":["Ordonnée et avec doublons","Non ordonnée et sans doublons","Ordonnée et sans doublons","Non ordonnée et avec doublons"],
     "correct_answer":"Non ordonnée et sans doublons",
     "explanation":"Un set est une collection non ordonnée d'éléments uniques, optimisée pour les tests d'appartenance."},
    {"id":"875_5","type":"vrai-faux","question":"L'opérateur | entre deux ensembles réalise leur union.",
     "correct_answer":"Vrai",
     "explanation":"s1 | s2 = union, s1 & s2 = intersection, s1 - s2 = différence, s1 ^ s2 = différence symétrique."},
    {"id":"875_6","type":"vrai-faux","question":"Quand utiliser un set plutôt qu'une liste en Python ?",
     "correct_answer":"On utilise un set quand : 1) on veut des éléments uniques (suppression des doublons) ; 2) on fait des tests d'appartenance fréquents (O(1) pour set vs O(n) pour liste) ; 3) on utilise des opérations ensemblistes (union, intersection…).",
     "explanation":"set(liste) est une façon efficace de dédoublonner une liste."},
    {"id":"875_7","type":"qcm","question":"La complexité du test 'x in set' est en moyenne :",
     "options":["O(n)","O(log n)","O(1)","O(n²)"],
     "correct_answer":"O(1)",
     "explanation":"Les ensembles Python utilisent des tables de hachage. Le test d'appartenance est O(1) en moyenne, contre O(n) pour une liste."},
    {"id":"875_8","type":"vrai-faux","question":"Un frozenset est un ensemble immuable en Python.",
     "correct_answer":"Vrai",
     "explanation":"frozenset est la version immuable du set. Il peut être utilisé comme clé de dictionnaire ou comme élément d'un autre set."},
]),

(876, "Fichiers CSV et traitement de données", "NSI", "1ère", [
    {"id":"876_1","type":"qcm","question":"CSV signifie :",
     "options":["Compressed Structured Values","Comma-Separated Values","Character String Values","Code Standard Variable"],
     "correct_answer":"Comma-Separated Values",
     "explanation":"CSV est un format texte simple où les données sont séparées par des virgules (ou point-virgule). Chaque ligne est un enregistrement."},
    {"id":"876_2","type":"vrai-faux","question":"Le module csv de Python permet de lire et écrire des fichiers CSV.",
     "correct_answer":"Vrai",
     "explanation":"import csv ; csv.reader() pour lire, csv.writer() pour écrire, csv.DictReader() pour lire en dictionnaires."},
    {"id":"876_3","type":"vrai-faux","question":"Écrivez le code Python pour lire un fichier 'data.csv' et afficher chaque ligne en tant que dictionnaire.",
     "correct_answer":"import csv\nwith open('data.csv', 'r', encoding='utf-8') as f:\n    reader = csv.DictReader(f)\n    for row in reader:\n        print(row)",
     "explanation":"DictReader utilise la première ligne comme noms de colonnes et chaque ligne devient un dictionnaire {colonne: valeur}."},
    {"id":"876_4","type":"qcm","question":"Quelle bibliothèque Python est la plus utilisée pour l'analyse de données tabulaires ?",
     "options":["numpy","matplotlib","pandas","scipy"],
     "correct_answer":"pandas",
     "explanation":"pandas fournit DataFrame et Series pour manipuler des données tabulaires de façon efficace et expressive."},
    {"id":"876_5","type":"vrai-faux","question":"En pandas, df.head() affiche les 5 premières lignes du DataFrame par défaut.",
     "correct_answer":"Vrai",
     "explanation":"df.head(n) affiche les n premières lignes (n=5 par défaut). df.tail() affiche les dernières lignes."},
    {"id":"876_6","type":"vrai-faux","question":"Comment filtrer les lignes d'un DataFrame pandas où la colonne 'age' est supérieure à 18 ?",
     "correct_answer":"df[df['age'] > 18] ou df.loc[df['age'] > 18]. Cette expression crée un masque booléen et sélectionne les lignes correspondantes.",
     "explanation":"Le filtrage booléen est central dans pandas : on peut combiner des conditions avec & (et), | (ou), ~ (non)."},
    {"id":"876_7","type":"qcm","question":"La méthode df.describe() en pandas retourne :",
     "options":["La documentation du DataFrame","Des statistiques descriptives (count, mean, std, min, max…)","Le type de chaque colonne","La taille du fichier"],
     "correct_answer":"Des statistiques descriptives (count, mean, std, min, max…)",
     "explanation":"describe() est utile pour une première exploration des données numériques (count, mean, std, 25%, 50%, 75%, max)."},
    {"id":"876_8","type":"vrai-faux","question":"Un fichier JSON est un format alternatif au CSV pour stocker des données structurées.",
     "correct_answer":"Vrai",
     "explanation":"JSON (JavaScript Object Notation) est adapté aux données hiérarchiques. En Python, le module json permet de lire/écrire des fichiers JSON."},
]),

(877, "Algorithmes de recherche dans des données", "NSI", "1ère", [
    {"id":"877_1","type":"qcm","question":"La recherche séquentielle dans une liste non triée a une complexité de :",
     "options":["O(1)","O(log n)","O(n)","O(n²)"],
     "correct_answer":"O(n)",
     "explanation":"La recherche séquentielle parcourt les éléments un à un. Dans le pire cas, elle examine tous les n éléments."},
    {"id":"877_2","type":"vrai-faux","question":"La recherche dichotomique (binaire) nécessite que la liste soit triée.",
     "correct_answer":"Vrai",
     "explanation":"La recherche dichotomique compare l'élément cherché avec le milieu de la liste et réduit de moitié l'espace de recherche à chaque étape."},
    {"id":"877_3","type":"vrai-faux","question":"Expliquez le principe de la recherche dichotomique et donnez sa complexité.",
     "correct_answer":"On compare la valeur cible avec l'élément central. Si égal, trouvé. Si inférieur, on cherche dans la moitié gauche ; si supérieur, dans la moitié droite. On répète. Complexité : O(log n) — beaucoup plus rapide que O(n) pour de grands tableaux.",
     "explanation":"Ex : pour 1000 éléments, la recherche séquentielle fait jusqu'à 1000 comparaisons ; la dichotomique en fait au plus 10 (log₂1000 ≈ 10)."},
    {"id":"877_4","type":"qcm","question":"Combien de comparaisons au maximum la recherche dichotomique effectue-t-elle dans une liste de 1024 éléments ?",
     "options":["10","100","512","1024"],
     "correct_answer":"10",
     "explanation":"log₂(1024) = 10. La recherche dichotomique effectue au plus log₂(n) + 1 comparaisons."},
    {"id":"877_5","type":"vrai-faux","question":"En Python, l'opérateur 'in' sur une liste effectue une recherche séquentielle.",
     "correct_answer":"Vrai",
     "explanation":"'x in list' effectue une recherche O(n). 'x in set' ou 'x in dict' est O(1) grâce au hachage."},
    {"id":"877_6","type":"vrai-faux","question":"Implémentez la recherche dichotomique en Python.",
     "correct_answer":"def recherche_dichotomique(lst, cible):\n    gauche, droite = 0, len(lst) - 1\n    while gauche <= droite:\n        milieu = (gauche + droite) // 2\n        if lst[milieu] == cible:\n            return milieu\n        elif lst[milieu] < cible:\n            gauche = milieu + 1\n        else:\n            droite = milieu - 1\n    return -1",
     "explanation":"Cette implémentation itérative est préférable à la récursive pour éviter les dépassements de pile sur de grandes listes."},
    {"id":"877_7","type":"qcm","question":"Le module bisect de Python fournit :",
     "options":["Un algorithme de tri","Des fonctions de recherche dans des listes triées","Un parseur JSON","Des opérations sur les ensembles"],
     "correct_answer":"Des fonctions de recherche dans des listes triées",
     "explanation":"bisect.bisect_left(lst, x) et bisect.bisect_right(lst, x) retournent l'indice d'insertion de x dans lst triée, en O(log n)."},
    {"id":"877_8","type":"vrai-faux","question":"La recherche en table de hachage est en moyenne plus rapide que la recherche dichotomique.",
     "correct_answer":"Vrai",
     "explanation":"O(1) moyen (hachage) > O(log n) (dichotomique). Cependant, la table de hachage nécessite plus de mémoire et ne maintient pas l'ordre."},
]),

(878, "Tri de données", "NSI", "1ère", [
    {"id":"878_1","type":"qcm","question":"Quelle est la complexité du tri par sélection ?",
     "options":["O(n)","O(n log n)","O(n²)","O(log n)"],
     "correct_answer":"O(n²)",
     "explanation":"Le tri par sélection effectue n(n-1)/2 comparaisons dans tous les cas : complexité O(n²) en temps."},
    {"id":"878_2","type":"vrai-faux","question":"Le tri rapide (quicksort) est en moyenne plus rapide que le tri à bulles.",
     "correct_answer":"Vrai",
     "explanation":"Quicksort : O(n log n) en moyenne. Tri à bulles : O(n²). Pour de grandes listes, quicksort est bien plus efficace."},
    {"id":"878_3","type":"vrai-faux","question":"Expliquez le principe du tri par insertion.",
     "correct_answer":"On parcourt la liste de gauche à droite. Pour chaque élément, on l'insère à sa place correcte dans la partie déjà triée (à sa gauche), en décalant les éléments plus grands vers la droite. Complexité : O(n²) en général, O(n) si la liste est déjà presque triée.",
     "explanation":"Le tri par insertion est efficace sur des listes presque triées et sur de petites listes."},
    {"id":"878_4","type":"qcm","question":"En Python, la méthode sort() trie une liste :",
     "options":["En créant une nouvelle liste triée","En place (modifie la liste originale)","En ordre décroissant uniquement","Par ordre alphabétique uniquement"],
     "correct_answer":"En place (modifie la liste originale)",
     "explanation":"list.sort() trie en place. sorted(list) crée et retourne une nouvelle liste triée sans modifier l'originale."},
    {"id":"878_5","type":"vrai-faux","question":"La fonction sorted() de Python utilise l'algorithme Timsort.",
     "correct_answer":"Vrai",
     "explanation":"Timsort est un algorithme hybride (fusion + insertion) développé par Tim Peters pour Python. Il est O(n log n) en pire cas et très efficace sur des données réelles."},
    {"id":"878_6","type":"vrai-faux","question":"Comment trier une liste de dictionnaires par la valeur d'une clé en Python ?",
     "correct_answer":"sorted(liste, key=lambda x: x['cle']) ou liste.sort(key=lambda x: x['cle']). On peut aussi utiliser operator.itemgetter('cle') comme key.",
     "explanation":"La fonction key= reçoit une fonction qui extrait la valeur à comparer pour chaque élément."},
    {"id":"878_7","type":"qcm","question":"Quel algorithme de tri a une complexité O(n log n) garantie dans le pire cas ?",
     "options":["Tri rapide (quicksort)","Tri fusion (merge sort)","Tri à bulles","Tri par sélection"],
     "correct_answer":"Tri fusion (merge sort)",
     "explanation":"Merge sort est toujours O(n log n). Quicksort est O(n log n) en moyenne mais O(n²) dans le pire cas (pivot mal choisi)."},
    {"id":"878_8","type":"vrai-faux","question":"Un tri est dit 'stable' s'il préserve l'ordre relatif des éléments égaux.",
     "correct_answer":"Vrai",
     "explanation":"Timsort (Python), merge sort sont stables. Quicksort et tri par tas (heapsort) ne le sont généralement pas. La stabilité est importante quand on trie par plusieurs critères successifs."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 3 — INTERACTIONS HOMME-MACHINE / WEB (879–884)
# ══════════════════════════════════════════════════════════

(879, "HTML — Structure d'une page web", "NSI", "1ère", [
    {"id":"879_1","type":"qcm","question":"HTML signifie :",
     "options":["HyperText Markup Language","High Text Manage Language","HyperText Modern Layout","Hybrid Text Modeling Language"],
     "correct_answer":"HyperText Markup Language",
     "explanation":"HTML est le langage de balisage standard pour créer des pages web. La version actuelle est HTML5."},
    {"id":"879_2","type":"vrai-faux","question":"La balise <head> contient le contenu visible de la page web.",
     "correct_answer":"Faux",
     "explanation":"<head> contient les métadonnées (titre, liens CSS, scripts…). Le contenu visible est dans <body>."},
    {"id":"879_3","type":"vrai-faux","question":"Quelle est la structure minimale d'une page HTML5 valide ?",
     "correct_answer":"<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>Titre</title></head><body>Contenu</body></html>",
     "explanation":"Le DOCTYPE déclare la version HTML. <html> est la racine. <head> contient les métadonnées. <body> le contenu visible."},
    {"id":"879_4","type":"qcm","question":"Quelle balise HTML crée un lien hypertexte ?",
     "options":["<link>","<href>","<a>","<url>"],
     "correct_answer":"<a>",
     "explanation":"<a href='url'>texte</a> crée un lien. L'attribut href spécifie la destination. <link> est pour les feuilles de style CSS dans <head>."},
    {"id":"879_5","type":"vrai-faux","question":"La balise <img> en HTML5 nécessite une balise de fermeture </img>.",
     "correct_answer":"Faux",
     "explanation":"<img> est une balise auto-fermante (void element) : <img src='image.jpg' alt='description'>. Pas de </img>."},
    {"id":"879_6","type":"vrai-faux","question":"Expliquez la différence entre balises <div> et <span>.",
     "correct_answer":"<div> est un conteneur de niveau bloc (block-level) : il occupe toute la largeur disponible et crée une nouvelle ligne. <span> est en ligne (inline) : il s'intègre dans le flux du texte sans créer de nouvelle ligne.",
     "explanation":"<div> est utilisé pour structurer de grandes sections. <span> pour cibler du texte à l'intérieur d'un paragraphe."},
    {"id":"879_7","type":"qcm","question":"L'attribut 'alt' de la balise <img> sert à :",
     "options":["Définir la taille de l'image","Fournir un texte alternatif pour l'accessibilité","Animer l'image","Définir le format de l'image"],
     "correct_answer":"Fournir un texte alternatif pour l'accessibilité",
     "explanation":"alt est lu par les lecteurs d'écran (accessibilité) et affiché si l'image ne se charge pas. Il est indispensable pour le SEO et l'accessibilité."},
    {"id":"879_8","type":"vrai-faux","question":"Les balises sémantiques HTML5 comme <header>, <footer>, <article> améliorent l'accessibilité.",
     "correct_answer":"Vrai",
     "explanation":"Les balises sémantiques donnent un sens structurel au contenu, améliorant l'accessibilité (lecteurs d'écran) et le référencement (SEO)."},
]),

(880, "CSS — Mise en forme", "NSI", "1ère", [
    {"id":"880_1","type":"qcm","question":"CSS signifie :",
     "options":["Computer Style Sheet","Cascading Style Sheets","Creative Style Syntax","Content Styling System"],
     "correct_answer":"Cascading Style Sheets",
     "explanation":"CSS est le langage de mise en forme des pages HTML. 'Cascading' fait référence à la cascade de priorités entre les règles."},
    {"id":"880_2","type":"vrai-faux","question":"Un sélecteur CSS #monId cible les éléments ayant l'attribut class='monId'.",
     "correct_answer":"Faux",
     "explanation":"#monId cible l'élément avec id='monId'. Le sélecteur de classe est .maClasse (point). Un id est unique sur la page."},
    {"id":"880_3","type":"vrai-faux","question":"Expliquez le modèle de boîte (box model) en CSS.",
     "correct_answer":"Chaque élément HTML est une boîte composée de : contenu (content), rembourrage intérieur (padding), bordure (border), marge extérieure (margin). La taille totale visible = contenu + padding + border.",
     "explanation":"En CSS3, box-sizing: border-box inclut padding et border dans la largeur déclarée, simplifiant les calculs."},
    {"id":"880_4","type":"qcm","question":"Quelle propriété CSS contrôle la couleur du texte ?",
     "options":["background-color","font-color","color","text-color"],
     "correct_answer":"color",
     "explanation":"color définit la couleur du texte. background-color définit la couleur d'arrière-plan. Il n'existe pas de propriété 'text-color' en CSS."},
    {"id":"880_5","type":"vrai-faux","question":"Flexbox est un mode de mise en page CSS conçu pour aligner et distribuer les éléments dans un conteneur.",
     "correct_answer":"Vrai",
     "explanation":"Flexbox (display: flex) simplifie l'alignement horizontal/vertical et la distribution d'espace entre éléments d'un conteneur."},
    {"id":"880_6","type":"vrai-faux","question":"Qu'est-ce que le responsive design et comment le CSS y contribue-t-il ?",
     "correct_answer":"Le responsive design adapte la mise en page à la taille de l'écran (desktop, tablette, mobile). CSS utilise les media queries (@media) pour appliquer des règles selon la largeur d'écran. Ex : @media (max-width: 768px) { /* styles mobile */ }",
     "explanation":"Les unités relatives (%, vw, vh, em, rem) et les grilles flexibles (flexbox, grid) facilitent le responsive design."},
    {"id":"880_7","type":"qcm","question":"En CSS, la propriété position: absolute place un élément :",
     "options":["Selon le flux normal du document","Par rapport à son ancêtre positionné le plus proche","Par rapport à la fenêtre du navigateur","Au centre de la page"],
     "correct_answer":"Par rapport à son ancêtre positionné le plus proche",
     "explanation":"position: absolute retire l'élément du flux et le positionne par rapport à son ancêtre ayant position: relative, absolute ou fixed."},
    {"id":"880_8","type":"vrai-faux","question":"Les variables CSS (custom properties) permettent de réutiliser des valeurs dans une feuille de style.",
     "correct_answer":"Vrai",
     "explanation":"--ma-couleur: #3498db; dans :root, puis color: var(--ma-couleur); facilite la maintenance et la cohérence visuelle."},
]),

(881, "Formulaires HTML et interactivité", "NSI", "1ère", [
    {"id":"881_1","type":"qcm","question":"La balise HTML pour créer un formulaire est :",
     "options":["<input>","<data>","<form>","<submit>"],
     "correct_answer":"<form>",
     "explanation":"<form action='url' method='POST'> encapsule les champs de saisie. action définit où envoyer les données, method (GET ou POST) comment."},
    {"id":"881_2","type":"vrai-faux","question":"La méthode HTTP POST envoie les données dans le corps de la requête, pas dans l'URL.",
     "correct_answer":"Vrai",
     "explanation":"GET ajoute les paramètres dans l'URL (?nom=valeur). POST les envoie dans le corps, plus adapté aux données sensibles ou longues."},
    {"id":"881_3","type":"vrai-faux","question":"Quels sont les principaux types de champs <input> en HTML5 ?",
     "correct_answer":"text, password, email, number, date, checkbox, radio, file, submit, reset, range, color, search, tel, url. HTML5 a ajouté de nombreux types sémantiques avec validation automatique.",
     "explanation":"Les types HTML5 activent la validation côté client et affichent des claviers adaptés sur mobile."},
    {"id":"881_4","type":"qcm","question":"L'attribut 'required' dans un champ <input> signifie que :",
     "options":["Le champ est en lecture seule","Le champ doit être rempli avant soumission du formulaire","Le champ est caché","Le champ est désactivé"],
     "correct_answer":"Le champ doit être rempli avant soumission du formulaire",
     "explanation":"required est une validation HTML5 côté client. Sans valeur, le formulaire ne peut pas être soumis (navigateur affiche un message d'erreur)."},
    {"id":"881_5","type":"vrai-faux","question":"La validation côté client (HTML5, JavaScript) remplace la validation côté serveur.",
     "correct_answer":"Faux",
     "explanation":"La validation côté client améliore l'expérience utilisateur mais peut être contournée. La validation côté serveur est indispensable pour la sécurité."},
    {"id":"881_6","type":"vrai-faux","question":"Expliquez la différence entre GET et POST dans un formulaire.",
     "correct_answer":"GET : données dans l'URL, limité en taille (~2000 caractères), mis en cache, visible dans l'historique. Utilisé pour des requêtes idempotentes (recherche). POST : données dans le corps, taille illimitée, non mis en cache, plus sécurisé. Utilisé pour les modifications (création, connexion, paiement).",
     "explanation":"Par convention REST, GET est pour lire, POST pour créer, PUT pour modifier, DELETE pour supprimer."},
    {"id":"881_7","type":"qcm","question":"La balise <label> en HTML est utilisée pour :",
     "options":["Afficher une liste déroulante","Associer un texte descriptif à un champ de formulaire","Créer un bouton","Afficher une image"],
     "correct_answer":"Associer un texte descriptif à un champ de formulaire",
     "explanation":"<label for='id_champ'>Nom :</label> associe le texte au champ. Cliquer sur le label active le champ associé. Indispensable pour l'accessibilité."},
    {"id":"881_8","type":"vrai-faux","question":"L'attribut placeholder affiche un texte d'indication dans un champ vide.",
     "correct_answer":"Vrai",
     "explanation":"placeholder affiche un texte grisé qui disparaît quand l'utilisateur commence à saisir. Il ne remplace pas le label pour l'accessibilité."},
]),

(882, "Introduction à JavaScript", "NSI", "1ère", [
    {"id":"882_1","type":"qcm","question":"JavaScript est principalement utilisé pour :",
     "options":["Styliser les pages web","Rendre les pages web interactives côté client","Gérer les bases de données","Configurer les serveurs"],
     "correct_answer":"Rendre les pages web interactives côté client",
     "explanation":"JavaScript s'exécute dans le navigateur et permet de modifier le DOM, réagir aux événements, valider des formulaires, faire des requêtes asynchrones…"},
    {"id":"882_2","type":"vrai-faux","question":"JavaScript et Java sont le même langage de programmation.",
     "correct_answer":"Faux",
     "explanation":"JavaScript et Java sont deux langages très différents. JavaScript a été créé par Brendan Eich en 1995 pour le web. Java est un langage compilé orienté objet créé par Sun Microsystems."},
    {"id":"882_3","type":"vrai-faux","question":"Qu'est-ce que le DOM (Document Object Model) ?",
     "correct_answer":"Le DOM est une représentation en arbre de la page HTML, accessible et modifiable par JavaScript. Chaque balise HTML devient un nœud. JavaScript peut modifier le DOM dynamiquement : ajouter/supprimer des éléments, changer du texte, modifier des styles.",
     "explanation":"document.getElementById(), document.querySelector(), element.innerHTML sont des méthodes DOM courantes."},
    {"id":"882_4","type":"qcm","question":"Comment sélectionner en JavaScript l'élément HTML avec l'id 'monBouton' ?",
     "options":["document.getClass('monBouton')","document.querySelector('#monBouton')","document.select('#monBouton')","document.find('monBouton')"],
     "correct_answer":"document.querySelector('#monBouton')",
     "explanation":"querySelector accepte les sélecteurs CSS. document.getElementById('monBouton') fonctionne aussi pour les ids."},
    {"id":"882_5","type":"vrai-faux","question":"addEventListener permet d'ajouter un gestionnaire d'événement à un élément HTML.",
     "correct_answer":"Vrai",
     "explanation":"element.addEventListener('click', maFonction) écoute le clic. D'autres événements : 'input', 'submit', 'mouseover', 'keydown'…"},
    {"id":"882_6","type":"vrai-faux","question":"Expliquez ce qu'est une fonction fléchée (arrow function) en JavaScript.",
     "correct_answer":"Une fonction fléchée est une syntaxe raccourcie pour les fonctions anonymes. Ex : const double = x => x * 2; ou (a, b) => a + b; Elle n'a pas son propre 'this', ce qui la distingue des fonctions classiques.",
     "explanation":"Les fonctions fléchées sont très utilisées comme callbacks dans les méthodes de tableaux (map, filter, reduce)."},
    {"id":"882_7","type":"qcm","question":"La méthode fetch() en JavaScript permet de :",
     "options":["Sélectionner des éléments DOM","Effectuer des requêtes HTTP asynchrones (AJAX)","Trier un tableau","Afficher une boîte de dialogue"],
     "correct_answer":"Effectuer des requêtes HTTP asynchrones (AJAX)",
     "explanation":"fetch() retourne une Promise. fetch('url').then(r => r.json()).then(data => console.log(data)) récupère des données JSON."},
    {"id":"882_8","type":"vrai-faux","question":"JavaScript peut aussi s'exécuter côté serveur via Node.js.",
     "correct_answer":"Vrai",
     "explanation":"Node.js est un environnement d'exécution JavaScript côté serveur. Il permet d'utiliser JavaScript partout : frontend (navigateur) et backend (serveur)."},
]),

(883, "Protocole HTTP", "NSI", "1ère", [
    {"id":"883_1","type":"qcm","question":"HTTP signifie :",
     "options":["HyperText Transfer Procedure","HyperText Transfer Protocol","High Transfer Text Protocol","HyperText Type Protocol"],
     "correct_answer":"HyperText Transfer Protocol",
     "explanation":"HTTP est le protocole de communication entre clients (navigateurs) et serveurs web, basé sur un modèle requête-réponse."},
    {"id":"883_2","type":"vrai-faux","question":"HTTPS est une version sécurisée de HTTP qui chiffre les communications.",
     "correct_answer":"Vrai",
     "explanation":"HTTPS utilise TLS (Transport Layer Security) pour chiffrer les échanges. Indispensable pour les mots de passe, paiements et données personnelles."},
    {"id":"883_3","type":"vrai-faux","question":"Quels sont les principaux codes de statut HTTP et leur signification ?",
     "correct_answer":"200 OK (succès), 201 Created, 301 Moved Permanently (redirection), 400 Bad Request (requête invalide), 401 Unauthorized (non authentifié), 403 Forbidden (accès refusé), 404 Not Found (ressource introuvable), 500 Internal Server Error.",
     "explanation":"Les codes sont groupés : 1xx informatif, 2xx succès, 3xx redirection, 4xx erreur client, 5xx erreur serveur."},
    {"id":"883_4","type":"qcm","question":"Une requête HTTP GET est utilisée pour :",
     "options":["Envoyer des données au serveur pour les stocker","Demander une ressource au serveur","Supprimer une ressource","Mettre à jour une ressource"],
     "correct_answer":"Demander une ressource au serveur",
     "explanation":"GET est idempotent et lisible (pas de modification de l'état serveur). Les paramètres sont dans l'URL (query string)."},
    {"id":"883_5","type":"vrai-faux","question":"HTTP est un protocole avec état (stateful).",
     "correct_answer":"Faux",
     "explanation":"HTTP est sans état (stateless) : chaque requête est indépendante. Les cookies et sessions sont des mécanismes ajoutés pour simuler un état."},
    {"id":"883_6","type":"vrai-faux","question":"Expliquez le rôle des cookies HTTP.",
     "correct_answer":"Les cookies sont de petits fichiers texte stockés par le navigateur pour maintenir un état entre les requêtes HTTP (qui est sans état). Ils servent à l'authentification (session), aux préférences utilisateur, au suivi analytique.",
     "explanation":"Un cookie est envoyé par le serveur via l'en-tête Set-Cookie et renvoyé automatiquement par le navigateur dans les requêtes suivantes."},
    {"id":"883_7","type":"qcm","question":"Le port par défaut pour HTTPS est :",
     "options":["80","443","8080","22"],
     "correct_answer":"443",
     "explanation":"HTTP utilise le port 80 par défaut. HTTPS utilise le port 443. SSH utilise le port 22. Ces ports sont définis par l'IANA."},
    {"id":"883_8","type":"vrai-faux","question":"Une API REST utilise les méthodes HTTP (GET, POST, PUT, DELETE) pour manipuler des ressources.",
     "correct_answer":"Vrai",
     "explanation":"REST (Representational State Transfer) est un style architectural : GET=lire, POST=créer, PUT=modifier, DELETE=supprimer. Les données sont généralement échangées en JSON."},
]),

(884, "API et services web", "NSI", "1ère", [
    {"id":"884_1","type":"qcm","question":"API signifie :",
     "options":["Advanced Program Interface","Application Programming Interface","Automated Process Integration","Application Protocol Interchange"],
     "correct_answer":"Application Programming Interface",
     "explanation":"Une API est une interface qui permet à des programmes de communiquer entre eux, en exposant des fonctions ou des données."},
    {"id":"884_2","type":"vrai-faux","question":"Une API REST retourne le plus souvent des données au format JSON.",
     "correct_answer":"Vrai",
     "explanation":"JSON (JavaScript Object Notation) est le format le plus répandu pour les API REST, bien qu'XML soit encore utilisé (SOAP)."},
    {"id":"884_3","type":"vrai-faux","question":"Comment interroger une API web en Python avec le module requests ?",
     "correct_answer":"import requests\nreponse = requests.get('https://api.example.com/data')\nif reponse.status_code == 200:\n    donnees = reponse.json()\n    print(donnees)",
     "explanation":"requests.get() envoie une requête GET. .json() décode la réponse JSON. On peut aussi passer des paramètres : params={'cle': 'valeur'}."},
    {"id":"884_4","type":"qcm","question":"La clé API (API key) sert principalement à :",
     "options":["Chiffrer les données","Authentifier et autoriser l'accès à une API","Compresser les requêtes","Identifier le navigateur"],
     "correct_answer":"Authentifier et autoriser l'accès à une API",
     "explanation":"La clé API identifie l'application ou l'utilisateur qui interroge l'API. Elle permet de contrôler l'accès et de limiter le nombre de requêtes (rate limiting)."},
    {"id":"884_5","type":"vrai-faux","question":"Le format JSON est basé sur la syntaxe des objets JavaScript.",
     "correct_answer":"Vrai",
     "explanation":"JSON (JavaScript Object Notation) est un sous-ensemble de la syntaxe JavaScript : {\"clé\": valeur}. Il est indépendant du langage et très lisible."},
    {"id":"884_6","type":"vrai-faux","question":"Qu'est-ce que le web scraping et quelles sont ses limites éthiques et légales ?",
     "correct_answer":"Le web scraping est l'extraction automatisée de données de pages web. Limites : les conditions d'utilisation des sites peuvent l'interdire, le RGPD protège les données personnelles, une surcharge du serveur peut constituer une attaque. Préférer les APIs officielles quand elles existent.",
     "explanation":"En Python, BeautifulSoup et Scrapy sont des bibliothèques de scraping. Respecter le fichier robots.txt est une convention de politesse."},
    {"id":"884_7","type":"qcm","question":"Le module json en Python permet de :",
     "options":["Faire des requêtes HTTP","Sérialiser (encode) et désérialiser (decode) des données JSON","Créer des bases de données","Envoyer des emails"],
     "correct_answer":"Sérialiser (encode) et désérialiser (decode) des données JSON",
     "explanation":"json.dumps(obj) convertit un objet Python en chaîne JSON. json.loads(chaine) convertit une chaîne JSON en objet Python."},
    {"id":"884_8","type":"vrai-faux","question":"GraphQL est une alternative à REST pour les API web.",
     "correct_answer":"Vrai",
     "explanation":"GraphQL (développé par Facebook) permet aux clients de demander exactement les données dont ils ont besoin, évitant le sur-chargement ou sous-chargement de données des API REST."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 4 — ALGORITHMIQUE (885–890)
# ══════════════════════════════════════════════════════════

(885, "Algorithmes de recherche", "NSI", "1ère", [
    {"id":"885_1","type":"qcm","question":"Un algorithme est :",
     "options":["Un programme informatique","Une suite finie d'instructions résolvant un problème","Un langage de programmation","Un type de données"],
     "correct_answer":"Une suite finie d'instructions résolvant un problème",
     "explanation":"Un algorithme est une description abstraite d'une procédure de résolution, indépendante du langage de programmation."},
    {"id":"885_2","type":"vrai-faux","question":"Un algorithme doit toujours se terminer en un nombre fini d'étapes.",
     "correct_answer":"Vrai",
     "explanation":"La terminaison est une propriété fondamentale des algorithmes. Un processus qui ne se termine pas n'est pas un algorithme (c'est un problème de la calculabilité)."},
    {"id":"885_3","type":"vrai-faux","question":"Décrivez en pseudo-code la recherche séquentielle dans un tableau.",
     "correct_answer":"POUR i de 0 à n-1 FAIRE\n  SI tableau[i] == cible ALORS\n    RETOURNER i\n  FIN SI\nFIN POUR\nRETOURNER -1  // non trouvé",
     "explanation":"La recherche séquentielle est simple mais O(n). Utilisée quand la liste n'est pas triée."},
    {"id":"885_4","type":"qcm","question":"Quelle est la complexité en temps de la recherche dichotomique dans le pire cas ?",
     "options":["O(1)","O(log n)","O(n)","O(n log n)"],
     "correct_answer":"O(log n)",
     "explanation":"À chaque étape, l'espace de recherche est divisé par 2. Après k étapes, il reste n/2ᵏ éléments. Terminaison quand 1 = n/2ᵏ, soit k = log₂(n)."},
    {"id":"885_5","type":"vrai-faux","question":"La recherche dichotomique peut être appliquée à un tableau non trié.",
     "correct_answer":"Faux",
     "explanation":"La recherche dichotomique nécessite un tableau trié pour pouvoir décider dans quelle moitié chercher."},
    {"id":"885_6","type":"vrai-faux","question":"Qu'est-ce que l'invariant de boucle et à quoi sert-il ?",
     "correct_answer":"Un invariant de boucle est une propriété vraie avant la boucle, maintenue vraie après chaque itération, et vraie à la sortie. Il sert à prouver la correction d'un algorithme itératif. Ex : pour le tri par insertion, l'invariant est que le préfixe déjà traité est toujours trié.",
     "explanation":"Les invariants sont un outil formel pour raisonner sur la correction des algorithmes."},
    {"id":"885_7","type":"qcm","question":"Quelle structure de données est la plus adaptée pour implémenter une file d'attente (FIFO) ?",
     "options":["Tableau avec accès aléatoire","Liste chaînée ou collections.deque","Arbre binaire","Dictionnaire"],
     "correct_answer":"Liste chaînée ou collections.deque",
     "explanation":"Une file FIFO (First In, First Out) nécessite des opérations efficaces en tête et en queue. collections.deque offre O(1) pour ces deux opérations."},
    {"id":"885_8","type":"vrai-faux","question":"Une pile (stack) fonctionne selon le principe LIFO (Last In, First Out).",
     "correct_answer":"Vrai",
     "explanation":"Pile LIFO : le dernier entré est le premier sorti. Opérations : push (empiler) et pop (dépiler). Utilisée pour les appels récursifs, l'analyse syntaxique."},
]),

(886, "Algorithmes de tri", "NSI", "1ère", [
    {"id":"886_1","type":"qcm","question":"Le tri à bulles compare et échange des éléments :",
     "options":["Aléatoirement","Adjacents en remontant les plus grands vers la fin","Du milieu vers les bords","Par dichotomie"],
     "correct_answer":"Adjacents en remontant les plus grands vers la fin",
     "explanation":"À chaque passage, le tri à bulles compare les éléments adjacents et échange ceux dans le mauvais ordre, 'faisant remonter' le plus grand vers la fin."},
    {"id":"886_2","type":"vrai-faux","question":"Le tri par sélection trouve le minimum du sous-tableau non trié et le place en tête.",
     "correct_answer":"Vrai",
     "explanation":"À chaque itération, le tri par sélection cherche le minimum dans la partie non triée et l'échange avec le premier élément de cette partie."},
    {"id":"886_3","type":"vrai-faux","question":"Implémentez le tri par insertion en Python.",
     "correct_answer":"def tri_insertion(lst):\n    for i in range(1, len(lst)):\n        cle = lst[i]\n        j = i - 1\n        while j >= 0 and lst[j] > cle:\n            lst[j + 1] = lst[j]\n            j -= 1\n        lst[j + 1] = cle\n    return lst",
     "explanation":"On mémorise l'élément courant (clé) et on décale les éléments plus grands d'une position vers la droite pour insérer la clé à sa place."},
    {"id":"886_4","type":"qcm","question":"Le tri fusion (merge sort) utilise le paradigme :",
     "options":["Glouton","Diviser pour régner","Programmation dynamique","Force brute"],
     "correct_answer":"Diviser pour régner",
     "explanation":"Merge sort divise le tableau en deux moitiés, trie chacune récursivement, puis fusionne les deux moitiés triées."},
    {"id":"886_5","type":"vrai-faux","question":"Le tri fusion nécessite un espace mémoire supplémentaire proportionnel à n.",
     "correct_answer":"Vrai",
     "explanation":"Merge sort est O(n) en espace supplémentaire car la fusion nécessite un tableau temporaire. Quicksort est O(log n) en espace (pile récursive)."},
    {"id":"886_6","type":"vrai-faux","question":"Expliquez le principe du tri rapide (quicksort).",
     "correct_answer":"On choisit un pivot. On partitionne le tableau en deux parties : éléments ≤ pivot à gauche, éléments > pivot à droite. On applique récursivement quicksort aux deux parties. Complexité : O(n log n) en moyenne, O(n²) dans le pire cas (pivot extrême).",
     "explanation":"Le choix du pivot est crucial : pivot aléatoire ou médiane de trois valeurs améliore les performances."},
    {"id":"886_7","type":"qcm","question":"Parmi ces tris, lequel est le plus efficace sur une liste presque triée ?",
     "options":["Tri rapide (quicksort)","Tri fusion (merge sort)","Tri par insertion","Tri à bulles"],
     "correct_answer":"Tri par insertion",
     "explanation":"Le tri par insertion est O(n) sur une liste presque triée (peu d'échanges nécessaires). C'est pourquoi Timsort (Python) l'utilise sur de petits sous-tableaux."},
    {"id":"886_8","type":"vrai-faux","question":"Aucun algorithme de tri par comparaison ne peut être plus rapide que O(n log n) dans le pire cas.",
     "correct_answer":"Vrai",
     "explanation":"La borne inférieure théorique des tris par comparaison est O(n log n). Des tris non comparatifs (radix sort, counting sort) peuvent faire O(n) mais dans des conditions restrictives."},
]),

(887, "Complexité algorithmique", "NSI", "1ère", [
    {"id":"887_1","type":"qcm","question":"La notation O(n) signifie que l'algorithme a une complexité :",
     "options":["Constante","Linéaire","Quadratique","Logarithmique"],
     "correct_answer":"Linéaire",
     "explanation":"O(n) : le temps d'exécution croît proportionnellement à la taille des données. Ex : recherche séquentielle, parcours de liste."},
    {"id":"887_2","type":"vrai-faux","question":"O(1) représente une complexité constante, indépendante de la taille des données.",
     "correct_answer":"Vrai",
     "explanation":"O(1) : accès à un élément par index, insertion en tête d'une pile, test d'appartenance dans un ensemble. La durée ne dépend pas de n."},
    {"id":"887_3","type":"vrai-faux","question":"Classez les complexités suivantes de la plus rapide à la plus lente : O(n²), O(1), O(n log n), O(log n), O(n).",
     "correct_answer":"O(1) < O(log n) < O(n) < O(n log n) < O(n²). Pour n=1000 : O(1)=1, O(log n)≈10, O(n)=1000, O(n log n)≈10000, O(n²)=1 000 000.",
     "explanation":"Pour de grands n, la différence devient considérable. Un algorithme O(n²) avec n=10⁶ ferait 10¹² opérations, inutilisable en pratique."},
    {"id":"887_4","type":"qcm","question":"Quelle est la complexité temporelle d'un accès à un élément dans un tableau par son indice ?",
     "options":["O(n)","O(log n)","O(1)","O(n²)"],
     "correct_answer":"O(1)",
     "explanation":"L'accès par indice (tableau[i]) est O(1) car l'adresse mémoire est calculée directement : adresse_base + i × taille_élément."},
    {"id":"887_5","type":"vrai-faux","question":"La complexité en espace mesure la mémoire supplémentaire utilisée par un algorithme.",
     "correct_answer":"Vrai",
     "explanation":"La complexité spatiale compte la mémoire allouée en plus des données d'entrée. Ex : merge sort est O(n) en espace ; tri en place est O(1) ou O(log n) pour la pile récursive."},
    {"id":"887_6","type":"vrai-faux","question":"Qu'est-ce que la notation grand O (Big O) capture-t-elle et qu'ignore-t-elle ?",
     "correct_answer":"Big O capture le comportement asymptotique (croissance) pour de grands n, en ignorant les constantes multiplicatives et les termes de degré inférieur. Ex : O(2n) = O(n). Elle mesure le pire cas en général.",
     "explanation":"Big O est utile pour comparer des algorithmes mais ne donne pas les performances absolues. Un O(n²) peut être plus rapide qu'un O(n log n) pour de petits n."},
    {"id":"887_7","type":"qcm","question":"Deux boucles for imbriquées, chacune itérant n fois, donnent une complexité de :",
     "options":["O(n)","O(2n)","O(n log n)","O(n²)"],
     "correct_answer":"O(n²)",
     "explanation":"n × n = n² opérations au total. Ex : tri à bulles, tri par sélection, tri par insertion (cas général) ont des boucles imbriquées d'où O(n²)."},
    {"id":"887_8","type":"vrai-faux","question":"Un algorithme récursif peut avoir une complexité exponentielle.",
     "correct_answer":"Vrai",
     "explanation":"Ex : calcul naïf de Fibonacci récursif est O(2ⁿ) car il recalcule de nombreuses fois les mêmes valeurs. La mémoïsation (ou l'approche itérative) le réduit à O(n)."},
]),

(888, "Récursivité", "NSI", "1ère", [
    {"id":"888_1","type":"qcm","question":"Une fonction récursive est une fonction qui :",
     "options":["S'appelle une seule fois","S'appelle elle-même directement ou indirectement","Boucle sans fin","N'a pas de paramètres"],
     "correct_answer":"S'appelle elle-même directement ou indirectement",
     "explanation":"La récursivité est une technique où une fonction se définit en termes d'elle-même pour résoudre des sous-problèmes de taille réduite."},
    {"id":"888_2","type":"vrai-faux","question":"Toute fonction récursive doit avoir un cas de base pour éviter une récursion infinie.",
     "correct_answer":"Vrai",
     "explanation":"Le cas de base (condition d'arrêt) est indispensable. Sans lui, la fonction s'appelle indéfiniment jusqu'à un dépassement de pile (RecursionError)."},
    {"id":"888_3","type":"vrai-faux","question":"Écrivez en Python une fonction récursive calculant la factorielle de n.",
     "correct_answer":"def factorielle(n):\n    if n == 0 or n == 1:  # cas de base\n        return 1\n    else:  # cas récursif\n        return n * factorielle(n - 1)",
     "explanation":"factorielle(5) = 5 × factorielle(4) = 5 × 4 × 3 × 2 × 1 = 120."},
    {"id":"888_4","type":"qcm","question":"La profondeur de récursion maximale par défaut en Python est d'environ :",
     "options":["100","1000","10000","illimitée"],
     "correct_answer":"1000",
     "explanation":"Python limite la profondeur de récursion à ~1000 par défaut (sys.getrecursionlimit()). Au-delà, RecursionError est levée."},
    {"id":"888_5","type":"vrai-faux","question":"La récursion terminale (tail recursion) est toujours optimisée par Python.",
     "correct_answer":"Faux",
     "explanation":"Python n'optimise pas la récursion terminale (tail-call optimization). Contrairement à certains langages fonctionnels (Haskell, Scheme), chaque appel récursif consomme de la pile en Python."},
    {"id":"888_6","type":"vrai-faux","question":"Expliquez la suite de Fibonacci récursive et son problème de complexité.",
     "correct_answer":"def fib(n):\n    if n <= 1: return n\n    return fib(n-1) + fib(n-2)\nProblème : complexité O(2ⁿ) car les mêmes valeurs sont recalculées exponentiellement de fois. Solution : mémoïsation (@functools.lru_cache) réduit à O(n).",
     "explanation":"fib(40) fait environ 2,5 milliards d'appels récursifs sans mémoïsation."},
    {"id":"888_7","type":"qcm","question":"Le parcours d'un arbre binaire est naturellement implémenté par :",
     "options":["Une boucle while","La récursivité","Une pile manuelle uniquement","La recherche dichotomique"],
     "correct_answer":"La récursivité",
     "explanation":"La structure récursive des arbres se prête naturellement à des algorithmes récursifs : parcours préfixe, infixe, postfixe."},
    {"id":"888_8","type":"vrai-faux","question":"Tout algorithme récursif peut être réécrit de façon itérative.",
     "correct_answer":"Vrai",
     "explanation":"La récursion peut toujours être simulée avec une pile explicite. Cela peut être nécessaire en Python pour éviter les limites de profondeur de récursion."},
]),

(889, "Algorithmes gloutons", "NSI", "1ère", [
    {"id":"889_1","type":"qcm","question":"Un algorithme glouton est un algorithme qui :",
     "options":["Explore toutes les solutions possibles","Choisit à chaque étape l'option localement optimale","Utilise la récursivité","Trie les données en premier"],
     "correct_answer":"Choisit à chaque étape l'option localement optimale",
     "explanation":"L'approche gloutonne (greedy) prend toujours la décision localement meilleure, sans revenir en arrière, en espérant obtenir un optimal global."},
    {"id":"889_2","type":"vrai-faux","question":"Un algorithme glouton garantit toujours d'obtenir la solution optimale globale.",
     "correct_answer":"Faux",
     "explanation":"L'algorithme glouton n'est optimal que pour certains problèmes (change de monnaie avec certaines pièces, couvrance minimale, Huffman). Pour d'autres (sac à dos 0/1), il peut échouer."},
    {"id":"889_3","type":"vrai-faux","question":"Appliquez l'algorithme glouton au problème du rendu de monnaie : rendre 41 centimes avec des pièces de 25, 10, 5 et 1 centime.",
     "correct_answer":"1 pièce de 25 (reste 16), 1 pièce de 10 (reste 6), 1 pièce de 5 (reste 1), 1 pièce de 1. Total : 4 pièces. L'algorithme glouton prend toujours la plus grande pièce possible.",
     "explanation":"Pour le système de monnaie classique, l'algorithme glouton est optimal. Avec des pièces de 1, 3, 4 centimes pour rendre 6, il échoue (choisit 4+1+1=3 pièces au lieu de 3+3=2)."},
    {"id":"889_4","type":"qcm","question":"L'algorithme de Dijkstra (plus court chemin) est un exemple d'algorithme :",
     "options":["De force brute","Glouton","Diviser pour régner","Par backtracking"],
     "correct_answer":"Glouton",
     "explanation":"Dijkstra choisit à chaque étape le sommet non visité avec la distance minimale connue (choix glouton local). Il est optimal pour les graphes à poids positifs."},
    {"id":"889_5","type":"vrai-faux","question":"Le problème du sac à dos (0/1 knapsack) est résolu de façon optimale par un algorithme glouton.",
     "correct_answer":"Faux",
     "explanation":"Le sac à dos 0/1 (prendre ou laisser chaque objet) nécessite la programmation dynamique ou la recherche exhaustive. La version fractionnaire (on peut prendre une fraction) est résolue de façon optimale par un algorithme glouton."},
    {"id":"889_6","type":"vrai-faux","question":"Expliquez l'algorithme glouton de coloration de graphe.",
     "correct_answer":"Pour chaque sommet (dans un ordre quelconque), on lui attribue la plus petite couleur non utilisée par ses voisins déjà colorés. Glouton car on ne revient pas sur les choix. Peut ne pas utiliser le nombre chromatique minimum mais est efficace en pratique.",
     "explanation":"La coloration de graphe est utilisée pour l'allocation de registres dans les compilateurs."},
    {"id":"889_7","type":"qcm","question":"L'algorithme de Huffman (compression) est glouton car il :",
     "options":["Trie toujours les données d'abord","Fusionne toujours les deux arbres de fréquence minimale","Utilise des tableaux de hachage","Divise le texte en blocs"],
     "correct_answer":"Fusionne toujours les deux arbres de fréquence minimale",
     "explanation":"Huffman construit un arbre de codage en fusionnant à chaque étape les deux sous-arbres de plus faible fréquence (choix glouton)."},
    {"id":"889_8","type":"vrai-faux","question":"L'algorithme de Prim pour l'arbre couvrant minimal est un algorithme glouton.",
     "correct_answer":"Vrai",
     "explanation":"Prim ajoute à chaque étape l'arête de poids minimal reliant l'arbre partiel à un sommet non encore visité (choix glouton). Kruskal est aussi glouton."},
]),

(890, "Arbres et parcours", "NSI", "1ère", [
    {"id":"890_1","type":"qcm","question":"Un arbre binaire est une structure où chaque nœud a au maximum :",
     "options":["Un enfant","Deux enfants","Trois enfants","Un nombre illimité d'enfants"],
     "correct_answer":"Deux enfants",
     "explanation":"Un arbre binaire est un arbre dont chaque nœud a 0, 1 ou 2 enfants (gauche et droit). Un arbre binaire de recherche (ABR) respecte en plus une propriété d'ordre."},
    {"id":"890_2","type":"vrai-faux","question":"La racine d'un arbre est le nœud sans parent.",
     "correct_answer":"Vrai",
     "explanation":"La racine est le sommet de l'arbre, sans parent. Les feuilles sont les nœuds sans enfants. La hauteur est la longueur du plus long chemin racine-feuille."},
    {"id":"890_3","type":"vrai-faux","question":"Décrivez les trois ordres de parcours d'un arbre binaire.",
     "correct_answer":"Préfixe (pré-ordre) : racine, gauche, droite. Infixe (in-ordre) : gauche, racine, droite (donne les éléments triés pour un ABR). Postfixe (post-ordre) : gauche, droite, racine (utilisé pour supprimer un arbre).",
     "explanation":"Un parcours en largeur (BFS) utilise une file et explore niveau par niveau."},
    {"id":"890_4","type":"qcm","question":"Dans un arbre binaire de recherche (ABR), les éléments dans le sous-arbre gauche sont :",
     "options":["Plus grands que la racine","Plus petits que la racine","Égaux à la racine","Aléatoirement placés"],
     "correct_answer":"Plus petits que la racine",
     "explanation":"ABR : pour tout nœud, sous-arbre gauche < nœud ≤ sous-arbre droit. Cette propriété permet la recherche en O(h) où h est la hauteur."},
    {"id":"890_5","type":"vrai-faux","question":"La hauteur d'un arbre binaire équilibré de n nœuds est O(log n).",
     "correct_answer":"Vrai",
     "explanation":"Un arbre parfaitement équilibré à n nœuds a une hauteur ≈ log₂(n). Un arbre dégénéré (filiforme) a une hauteur n."},
    {"id":"890_6","type":"vrai-faux","question":"Implémentez en Python le parcours infixe d'un arbre binaire.",
     "correct_answer":"class Noeud:\n    def __init__(self, valeur):\n        self.valeur = valeur\n        self.gauche = None\n        self.droite = None\n\ndef parcours_infixe(noeud):\n    if noeud is not None:\n        parcours_infixe(noeud.gauche)\n        print(noeud.valeur)\n        parcours_infixe(noeud.droite)",
     "explanation":"Le parcours infixe d'un ABR donne les éléments dans l'ordre croissant."},
    {"id":"890_7","type":"qcm","question":"Un tas (heap) est un arbre binaire où :",
     "options":["Les feuilles sont plus grandes que les racines","La racine est plus grande (max-heap) ou plus petite (min-heap) que tous ses descendants","Les nœuds sont triés par niveau","Tous les nœuds ont exactement deux enfants"],
     "correct_answer":"La racine est plus grande (max-heap) ou plus petite (min-heap) que tous ses descendants",
     "explanation":"Un tas est utilisé pour les files de priorité et le tri par tas (heapsort). Python fournit heapq pour un min-heap."},
    {"id":"890_8","type":"vrai-faux","question":"Les arbres sont utilisés pour représenter les systèmes de fichiers des systèmes d'exploitation.",
     "correct_answer":"Vrai",
     "explanation":"Le système de fichiers est une arborescence : chaque dossier est un nœud pouvant contenir des fichiers (feuilles) et d'autres dossiers (nœuds internes)."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 5 — PROGRAMMATION PYTHON (891–896)
# ══════════════════════════════════════════════════════════

(891, "Variables, types et expressions", "NSI", "1ère", [
    {"id":"891_1","type":"qcm","question":"En Python, quel est le type de la valeur 3.14 ?",
     "options":["int","float","str","complex"],
     "correct_answer":"float",
     "explanation":"3.14 est un flottant (float). En Python, type(3.14) retourne <class 'float'>."},
    {"id":"891_2","type":"vrai-faux","question":"En Python, les variables n'ont pas de type fixe ; elles peuvent changer de type.",
     "correct_answer":"Vrai",
     "explanation":"Python est dynamiquement typé : x = 5 (int), puis x = 'hello' (str) est valide. Le type est lié à la valeur, pas à la variable."},
    {"id":"891_3","type":"vrai-faux","question":"Quelle est la différence entre les opérateurs // et / en Python ?",
     "correct_answer":"/ est la division réelle (retourne toujours un float). // est la division entière (retourne le quotient entier, arrondi vers -inf). Ex : 7/2 = 3.5 ; 7//2 = 3 ; -7//2 = -4.",
     "explanation":"// est utile pour l'algorithmique (indices, modulo) quand on veut un résultat entier."},
    {"id":"891_4","type":"qcm","question":"Quel est le résultat de 17 % 5 en Python ?",
     "options":["3","2","0","3.4"],
     "correct_answer":"2",
     "explanation":"% est l'opérateur modulo (reste de la division entière). 17 = 5×3 + 2, donc 17 % 5 = 2."},
    {"id":"891_5","type":"vrai-faux","question":"En Python, l'opérateur ** est l'opérateur de puissance.",
     "correct_answer":"Vrai",
     "explanation":"2**10 = 1024. 2**0.5 = racine carrée de 2 ≈ 1.414."},
    {"id":"891_6","type":"vrai-faux","question":"Expliquez la différence entre = et == en Python.",
     "correct_answer":"= est l'opérateur d'affectation : x = 5 assigne la valeur 5 à x. == est l'opérateur de comparaison d'égalité : x == 5 retourne True ou False. Confondre les deux est une erreur courante.",
     "explanation":"Autre confusion fréquente : is (identité d'objet) vs == (égalité de valeur)."},
    {"id":"891_7","type":"qcm","question":"Que retourne type('hello') en Python ?",
     "options":["'string'","<class 'str'>","str","'hello'"],
     "correct_answer":"<class 'str'>",
     "explanation":"type() retourne l'objet classe. En Python 3, type('hello') affiche <class 'str'>. isinstance('hello', str) retourne True."},
    {"id":"891_8","type":"vrai-faux","question":"En Python, int('42') convertit la chaîne '42' en entier 42.",
     "correct_answer":"Vrai",
     "explanation":"int(), float(), str(), bool() sont des fonctions de conversion de type (casting). int('42') = 42. int('abc') lève une ValueError."},
]),

(892, "Structures conditionnelles", "NSI", "1ère", [
    {"id":"892_1","type":"qcm","question":"Quelle est la syntaxe correcte d'une condition en Python ?",
     "options":["if (x > 0) then:","if x > 0:","IF x > 0 THEN","if x > 0 do:"],
     "correct_answer":"if x > 0:",
     "explanation":"Python utilise if, elif, else sans parenthèses obligatoires, sans then, et avec un deux-points. L'indentation définit le bloc."},
    {"id":"892_2","type":"vrai-faux","question":"En Python, elif est une contraction de 'else if'.",
     "correct_answer":"Vrai",
     "explanation":"elif permet de chaîner plusieurs conditions. On peut avoir autant de elif qu'on veut entre if et else."},
    {"id":"892_3","type":"vrai-faux","question":"Écrivez une fonction Python qui retourne 'positif', 'négatif' ou 'nul' selon le signe d'un nombre.",
     "correct_answer":"def signe(n):\n    if n > 0:\n        return 'positif'\n    elif n < 0:\n        return 'négatif'\n    else:\n        return 'nul'",
     "explanation":"La structure if/elif/else gère les trois cas mutuellement exclusifs."},
    {"id":"892_4","type":"qcm","question":"Quelle valeur est évaluée comme False en Python ?",
     "options":["1","'False'","0","[1]"],
     "correct_answer":"0",
     "explanation":"En Python, les valeurs falsy sont : 0, 0.0, '' (chaîne vide), [] (liste vide), {} (dict vide), None, False. Toutes les autres sont truthy."},
    {"id":"892_5","type":"vrai-faux","question":"L'expression 'x if condition else y' est une expression conditionnelle (ternaire) valide en Python.",
     "correct_answer":"Vrai",
     "explanation":"L'opérateur ternaire en Python : valeur_si_vrai if condition else valeur_si_faux. Ex : max_val = a if a > b else b."},
    {"id":"892_6","type":"vrai-faux","question":"Expliquez le court-circuit (short-circuit evaluation) des opérateurs and et or en Python.",
     "correct_answer":"Python évalue les opérandes de gauche à droite et s'arrête dès que le résultat est déterminé. Pour and : si le premier est False, le deuxième n'est pas évalué. Pour or : si le premier est True, le deuxième n'est pas évalué. Utile pour éviter les erreurs (ex : if lst and lst[0] == x).",
     "explanation":"Le court-circuit permet aussi des idiomes comme x = x or valeur_par_défaut."},
    {"id":"892_7","type":"qcm","question":"Quel est le résultat de : print(3 > 2 > 1) en Python ?",
     "options":["False","True","Error","None"],
     "correct_answer":"True",
     "explanation":"Python supporte les comparaisons chaînées : 3 > 2 > 1 est équivalent à (3 > 2) and (2 > 1) = True and True = True."},
    {"id":"892_8","type":"vrai-faux","question":"En Python, le mot-clé 'match' (Python 3.10+) permet de faire du filtrage par motif.",
     "correct_answer":"Vrai",
     "explanation":"Le pattern matching (match/case, Python 3.10) est similaire au switch/case d'autres langages mais plus puissant (filtrage sur les structures de données)."},
]),

(893, "Boucles for et while", "NSI", "1ère", [
    {"id":"893_1","type":"qcm","question":"Que produit range(2, 10, 3) en Python ?",
     "options":["[2, 5, 8, 11]","[2, 5, 8]","[3, 6, 9]","[2, 4, 6, 8]"],
     "correct_answer":"[2, 5, 8]",
     "explanation":"range(début, fin_exclue, pas) : 2, 2+3=5, 5+3=8, 8+3=11 (exclu car ≥ 10). → [2, 5, 8]."},
    {"id":"893_2","type":"vrai-faux","question":"Une boucle while continue tant que sa condition est vraie.",
     "correct_answer":"Vrai",
     "explanation":"while condition: exécute le bloc tant que condition est vraie. Si la condition ne devient jamais fausse, c'est une boucle infinie."},
    {"id":"893_3","type":"vrai-faux","question":"Quelle est la différence entre break et continue dans une boucle Python ?",
     "correct_answer":"break interrompt immédiatement la boucle entière. continue passe directement à l'itération suivante (saute le reste du corps de la boucle pour cette itération).",
     "explanation":"break sort de la boucle la plus interne. continue la reprend à l'itération suivante."},
    {"id":"893_4","type":"qcm","question":"Que fait enumerate() dans une boucle for ?",
     "options":["Trie la liste","Retourne un tuple (indice, valeur) pour chaque élément","Filtre les éléments","Inverse la liste"],
     "correct_answer":"Retourne un tuple (indice, valeur) pour chaque élément",
     "explanation":"for i, v in enumerate(lst): permet d'accéder simultanément à l'indice et à la valeur, sans avoir à gérer un compteur manuellement."},
    {"id":"893_5","type":"vrai-faux","question":"En Python, for ... else exécute le bloc else si la boucle se termine normalement (sans break).",
     "correct_answer":"Vrai",
     "explanation":"La clause else des boucles est exécutée si la boucle se termine sans break. C'est utile pour détecter si un élément a été trouvé dans une liste."},
    {"id":"893_6","type":"vrai-faux","question":"Écrivez une boucle Python pour calculer la somme des nombres pairs de 1 à 100.",
     "correct_answer":"somme = 0\nfor i in range(2, 101, 2):\n    somme += i\nprint(somme)  # 2550\n# Ou: somme = sum(range(2, 101, 2))",
     "explanation":"range(2, 101, 2) génère 2, 4, 6, …, 100. sum() est encore plus concis."},
    {"id":"893_7","type":"qcm","question":"Que retourne zip([1,2,3], ['a','b','c']) ?",
     "options":["[[1,'a'],[2,'b'],[3,'c']]","Un itérateur de tuples (1,'a'), (2,'b'), (3,'c')","'1a2b3c'","Une erreur"],
     "correct_answer":"Un itérateur de tuples (1,'a'), (2,'b'), (3,'c')",
     "explanation":"zip() associe les éléments de même position dans plusieurs itérables. Utile pour itérer en parallèle sur plusieurs séquences."},
    {"id":"893_8","type":"vrai-faux","question":"La fonction map(f, lst) applique la fonction f à chaque élément de lst.",
     "correct_answer":"Vrai",
     "explanation":"map() retourne un itérateur paresseux. list(map(lambda x: x*2, [1,2,3])) = [2,4,6]. Équivalent à [x*2 for x in [1,2,3]]."},
]),

(894, "Fonctions et portée des variables", "NSI", "1ère", [
    {"id":"894_1","type":"qcm","question":"Le mot-clé 'def' en Python est utilisé pour :",
     "options":["Définir une variable","Définir une fonction","Importer un module","Créer une classe"],
     "correct_answer":"Définir une fonction",
     "explanation":"def nom_fonction(paramètres): définit une nouvelle fonction. Le corps est indenté."},
    {"id":"894_2","type":"vrai-faux","question":"Une fonction Python peut retourner plusieurs valeurs simultanément.",
     "correct_answer":"Vrai",
     "explanation":"return a, b retourne un tuple (a, b). Le dépaquetage permet : x, y = ma_fonction() pour récupérer chaque valeur séparément."},
    {"id":"894_3","type":"vrai-faux","question":"Expliquez la portée des variables (LEGB rule) en Python.",
     "correct_answer":"LEGB : Local (dans la fonction courante), Enclosing (fonctions imbriquées englobantes), Global (module), Builtin (fonctions intégrées). Python cherche une variable dans cet ordre. Une variable locale masque une variable globale de même nom.",
     "explanation":"Le mot-clé global déclare qu'une variable locale référence la variable globale. nonlocal fait de même pour les fonctions imbriquées."},
    {"id":"894_4","type":"qcm","question":"Un paramètre avec une valeur par défaut en Python est défini comme :",
     "options":["def f(x = 0):","def f(x: 0):","def f(x default 0):","def f(x, default=0):"],
     "correct_answer":"def f(x = 0):",
     "explanation":"Les paramètres avec valeur par défaut sont optionnels lors de l'appel. Ils doivent être placés après les paramètres obligatoires."},
    {"id":"894_5","type":"vrai-faux","question":"*args dans une définition de fonction permet de passer un nombre variable d'arguments positionnels.",
     "correct_answer":"Vrai",
     "explanation":"def f(*args) reçoit tous les arguments positionnels supplémentaires dans un tuple. **kwargs fait de même pour les arguments nommés (dictionnaire)."},
    {"id":"894_6","type":"vrai-faux","question":"Qu'est-ce qu'une fonction lambda en Python ? Donnez un exemple.",
     "correct_answer":"Une lambda est une fonction anonyme à une expression. Syntaxe : lambda paramètres: expression. Ex : carre = lambda x: x**2 ; carre(5) = 25. Utilisée pour des fonctions courtes, notamment comme argument de sorted(), map(), filter().",
     "explanation":"Les lambdas sont équivalentes à des def simples mais ne peuvent contenir qu'une expression."},
    {"id":"894_7","type":"qcm","question":"Une fonction récursive calcule n! (factorielle). Si on appelle factorielle(0), elle doit retourner :",
     "options":["0","1","-1","Une erreur"],
     "correct_answer":"1",
     "explanation":"Par convention mathématique, 0! = 1. C'est le cas de base de la récursion de la factorielle."},
    {"id":"894_8","type":"vrai-faux","question":"Les fonctions en Python sont des objets de première classe.",
     "correct_answer":"Vrai",
     "explanation":"Les fonctions peuvent être assignées à des variables, passées comme arguments, retournées par d'autres fonctions. C'est la base de la programmation fonctionnelle en Python."},
]),

(895, "Modules et bibliothèques Python", "NSI", "1ère", [
    {"id":"895_1","type":"qcm","question":"Comment importer uniquement la fonction sqrt du module math ?",
     "options":["import math.sqrt","import sqrt from math","from math import sqrt","use math.sqrt"],
     "correct_answer":"from math import sqrt",
     "explanation":"from module import fonction importe directement sqrt dans l'espace de noms courant. Puis sqrt(16) suffit (sans math. préfixe)."},
    {"id":"895_2","type":"vrai-faux","question":"Le module random de Python permet de générer des nombres pseudo-aléatoires.",
     "correct_answer":"Vrai",
     "explanation":"random.randint(a, b) retourne un entier entre a et b inclus. random.random() retourne un flottant entre 0 et 1. random.choice(lst) choisit un élément aléatoire."},
    {"id":"895_3","type":"vrai-faux","question":"À quoi sert le module os en Python ? Citez trois fonctions utiles.",
     "correct_answer":"Le module os permet d'interagir avec le système d'exploitation. Fonctions : os.getcwd() (répertoire courant), os.listdir(path) (liste des fichiers), os.path.join() (construction de chemins), os.makedirs() (créer dossiers), os.remove() (supprimer fichier).",
     "explanation":"os.path est très utilisé pour manipuler les chemins de fichiers de façon portable (Windows/Linux/Mac)."},
    {"id":"895_4","type":"qcm","question":"Quelle est la différence entre import math et from math import * ?",
     "options":["Aucune différence","import math accède via math.sin() ; from math import * importe tout directement dans l'espace de noms","import math est plus lent","from math import * importe uniquement les fonctions publiques"],
     "correct_answer":"import math accède via math.sin() ; from math import * importe tout directement dans l'espace de noms",
     "explanation":"from module import * est déconseillé car il peut écraser des noms existants et rend difficile de savoir d'où viennent les fonctions."},
    {"id":"895_5","type":"vrai-faux","question":"pip est le gestionnaire de paquets standard de Python.",
     "correct_answer":"Vrai",
     "explanation":"pip install nompaquet installe des bibliothèques depuis PyPI (Python Package Index). Ex : pip install pandas, pip install requests."},
    {"id":"895_6","type":"vrai-faux","question":"Qu'est-ce qu'un environnement virtuel Python (venv) et pourquoi l'utiliser ?",
     "correct_answer":"Un environnement virtuel isole les dépendances d'un projet Python (packages et versions) dans un dossier dédié, évitant les conflits entre projets. python -m venv mon_env, puis activation et pip install dans cet environnement.",
     "explanation":"Chaque projet peut avoir ses propres versions de bibliothèques sans interférer avec les autres projets ou le Python système."},
    {"id":"895_7","type":"qcm","question":"Le module turtle de Python est utilisé pour :",
     "options":["Traiter des fichiers XML","Dessiner des graphiques en déplaçant une tortue virtuelle","Gérer des bases de données","Faire des calculs matriciels"],
     "correct_answer":"Dessiner des graphiques en déplaçant une tortue virtuelle",
     "explanation":"turtle est un module éducatif pour apprendre la programmation par le dessin. forward(100), right(90)... permettent de créer des figures géométriques."},
    {"id":"895_8","type":"vrai-faux","question":"matplotlib.pyplot est une bibliothèque Python pour créer des graphiques et visualisations.",
     "correct_answer":"Vrai",
     "explanation":"matplotlib est la bibliothèque de base pour la visualisation en Python. plt.plot(), plt.hist(), plt.scatter() permettent de créer divers graphiques."},
]),

(896, "Gestion des erreurs et exceptions", "NSI", "1ère", [
    {"id":"896_1","type":"qcm","question":"Quel est le bloc Python utilisé pour capturer une exception ?",
     "options":["try/catch","try/except","error/handle","try/catch/finally"],
     "correct_answer":"try/except",
     "explanation":"Python utilise try/except (pas try/catch comme Java/JavaScript). try: code risqué ; except TypeErreur: gestion de l'erreur."},
    {"id":"896_2","type":"vrai-faux","question":"Le bloc finally s'exécute toujours, qu'une exception ait été levée ou non.",
     "correct_answer":"Vrai",
     "explanation":"finally est utilisé pour les opérations de nettoyage (fermer un fichier, libérer une ressource) qui doivent se produire dans tous les cas."},
    {"id":"896_3","type":"vrai-faux","question":"Quelles sont les exceptions Python les plus courantes ?",
     "correct_answer":"ValueError (mauvaise valeur, ex: int('abc')), TypeError (mauvais type, ex: 1+'a'), IndexError (indice hors plage), KeyError (clé de dict absente), ZeroDivisionError (division par zéro), FileNotFoundError, NameError (variable non définie), AttributeError.",
     "explanation":"Toutes héritent de la classe Exception. BaseException est la classe racine."},
    {"id":"896_4","type":"qcm","question":"Le mot-clé 'raise' en Python permet de :",
     "options":["Capturer une exception","Lever (déclencher) une exception","Ignorer une exception","Afficher un message d'erreur"],
     "correct_answer":"Lever (déclencher) une exception",
     "explanation":"raise ValueError('message') lève une exception. On peut relever une exception capturée avec raise (sans argument) dans un bloc except."},
    {"id":"896_5","type":"vrai-faux","question":"On peut créer des exceptions personnalisées en héritant de la classe Exception.",
     "correct_answer":"Vrai",
     "explanation":"class MonErreur(Exception): pass permet de créer une exception personnalisée. On peut ajouter des attributs et méthodes spécifiques."},
    {"id":"896_6","type":"vrai-faux","question":"Expliquez l'utilisation de 'with open()' pour lire un fichier en Python.",
     "correct_answer":"with open('fichier.txt', 'r', encoding='utf-8') as f:\n    contenu = f.read()\nLe gestionnaire de contexte 'with' garantit que le fichier est fermé automatiquement, même si une exception survient. C'est équivalent à try/finally avec f.close().",
     "explanation":"Les modes d'ouverture : 'r' (lecture), 'w' (écriture, écrase), 'a' (append), 'x' (création exclusive), 'b' pour binaire."},
    {"id":"896_7","type":"qcm","question":"Que se passe-t-il si aucune clause except ne capture une exception levée ?",
     "options":["L'exception est ignorée","Le programme continue normalement","L'exception se propage vers le code appelant, jusqu'à arrêter le programme","Python relance automatiquement le code"],
     "correct_answer":"L'exception se propage vers le code appelant, jusqu'à arrêter le programme",
     "explanation":"Une exception non gérée remonte la pile d'appels (stack trace) jusqu'à atteindre le niveau supérieur, où elle arrête le programme avec un message d'erreur."},
    {"id":"896_8","type":"vrai-faux","question":"except Exception: capture toutes les exceptions standard mais pas les erreurs système (KeyboardInterrupt, SystemExit).",
     "correct_answer":"Vrai",
     "explanation":"KeyboardInterrupt (Ctrl+C) et SystemExit héritent de BaseException mais pas de Exception. except Exception: ne les capture pas, ce qui est généralement le comportement souhaité."},
]),

# ══════════════════════════════════════════════════════════
# BLOC 6 — ARCHITECTURES MATÉRIELLES ET RÉSEAUX (897–902)
# ══════════════════════════════════════════════════════════

(897, "Architecture de Von Neumann", "NSI", "1ère", [
    {"id":"897_1","type":"qcm","question":"Quel est le principal apport du modèle de Von Neumann ?",
     "options":["La programmation orientée objet","Le programme stocké en mémoire avec les données","L'invention du transistor","Le protocole TCP/IP"],
     "correct_answer":"Le programme stocké en mémoire avec les données",
     "explanation":"Le modèle de Von Neumann (1945) est révolutionnaire car le programme est stocké dans la même mémoire que les données, permettant de le modifier dynamiquement."},
    {"id":"897_2","type":"vrai-faux","question":"Le modèle de Von Neumann comprend une unité centrale de traitement (CPU), une mémoire et des entrées/sorties.",
     "correct_answer":"Vrai",
     "explanation":"Composants du modèle : processeur (UAL + UC), mémoire principale, unités d'entrée/sortie, reliés par un bus."},
    {"id":"897_3","type":"vrai-faux","question":"Expliquez le rôle de l'unité arithmétique et logique (UAL) dans le CPU.",
     "correct_answer":"L'UAL (Arithmetic and Logic Unit) effectue les opérations arithmétiques (addition, soustraction, multiplication) et logiques (AND, OR, NOT, comparaisons). C'est le composant qui effectue les calculs proprement dits.",
     "explanation":"L'UAL travaille sur des opérandes stockés dans des registres et produit des résultats stockés à nouveau dans des registres."},
    {"id":"897_4","type":"qcm","question":"Le compteur de programme (Program Counter / PC) contient :",
     "options":["Le résultat du dernier calcul","L'adresse de la prochaine instruction à exécuter","La taille de la mémoire","Le nombre d'instructions exécutées"],
     "correct_answer":"L'adresse de la prochaine instruction à exécuter",
     "explanation":"Le PC est incrémenté après chaque instruction. Les instructions de saut (branch, jump) modifient le PC pour changer le flux d'exécution."},
    {"id":"897_5","type":"vrai-faux","question":"Le cycle d'exécution d'un processeur est : Fetch (chercher) – Decode (décoder) – Execute (exécuter).",
     "correct_answer":"Vrai",
     "explanation":"Cycle FDE : Fetch (lire l'instruction depuis la mémoire), Decode (l'interpréter), Execute (l'exécuter). Ce cycle se répète plusieurs milliards de fois par seconde sur un CPU moderne."},
    {"id":"897_6","type":"vrai-faux","question":"Qu'est-ce que la fréquence d'horloge d'un processeur et quelle est sa limite ?",
     "correct_answer":"La fréquence d'horloge (GHz) mesure le nombre de cycles par seconde. Plus elle est élevée, plus le processeur est rapide. Limite physique : la dissipation thermique (effet Joule). Depuis ~2005, on a atteint un plafond (~4-5 GHz) et l'industrie s'est tournée vers les processeurs multicœurs.",
     "explanation":"La loi de Moore (doublement du nombre de transistors tous les 2 ans) ralentit depuis les années 2010."},
    {"id":"897_7","type":"qcm","question":"La mémoire cache d'un processeur est :",
     "options":["Identique à la RAM","Une mémoire rapide proche du CPU pour réduire les accès à la RAM","Le disque dur","La mémoire virtuelle"],
     "correct_answer":"Une mémoire rapide proche du CPU pour réduire les accès à la RAM",
     "explanation":"La hiérarchie mémoire : registres (plus rapide) > cache L1 > L2 > L3 > RAM > disque dur. Le cache stocke les données et instructions fréquemment utilisées pour accélérer l'exécution."},
    {"id":"897_8","type":"vrai-faux","question":"Le modèle de Von Neumann est encore utilisé dans les ordinateurs modernes, malgré l'existence d'autres architectures (ex: Harvard).",
     "correct_answer":"Vrai",
     "explanation":"Le modèle de Von Neumann est la base de la plupart des architectures de processeurs modernes, même si certaines utilisent une architecture Harvard (séparation des mémoires instructions/données) pour améliorer les performances."},
]),
(898, "Systèmes de numération et codage binaire", "NSI", "1ère", [
    {"id":"898_1","type":"qcm","question":"Quel est le système de numération utilisé par les ordinateurs ?",
     "options":["Décimal","Binaire","Hexadécimal","Octal"],
     "correct_answer":"Binaire",
     "explanation":"Les ordinateurs utilisent le système binaire (base 2) car il est plus facile de représenter deux états (0 et 1) avec des composants électroniques."},
    {"id":"898_2","type":"vrai-faux","question":"En binaire, le nombre 1011 représente le nombre décimal 11.",
     "correct_answer":"Vrai",
     "explanation":"1011 en binaire = 1×2³ + 0×2² + 1×2¹ + 1×2⁰ = 8 + 0 + 2 + 1 = 11 en décimal."},
    {"id":"898_3","type":"vrai-faux","question":"Comment convertir un nombre décimal en binaire ?",
     "correct_answer":"Diviser le nombre par 2 et noter le reste. Répéter avec le quotient jusqu'à ce que le quotient soit 0. Les restes lus à l'envers donnent le nombre binaire. Ex : 13 → 13/2=6 reste 1, 6/2=3 reste 0, 3/2=1 reste 1, 1/2=0 reste 1 → binaire = 1101.",
     "explanation":"Cette méthode est appelée la division successive. Pour les nombres entiers, on peut aussi utiliser des puissances de 2."},
    {"id":"898_4","type":"qcm","question":"Quel est le nombre binaire de 255 en hexadécimal ?",
     "options":["0xFF","0x00","0x1A","0xF0"],
     "correct_answer":"0xFF",
     "explanation":"255 en décimal = 15×16¹ + 15×16⁰ = 0xFF en hexadécimal. En binaire, 255 = 11111111."},
    {"id":"898_5","type":"vrai-faux","question":"Le code ASCII utilise 7 bits pour représenter les caractères.",
     "correct_answer":"Vrai",
     "explanation":"ASCII (American Standard Code for Information Interchange) utilise 7 bits pour représenter 128 caractères (0-127). Le 8ème bit était initialement utilisé pour la parité ou des extensions."},
    {"id":"898_6","type":"vrai-faux","question":"Qu'est-ce que le code Unicode et pourquoi a-t-il été créé ?",
     "correct_answer":"Unicode est un standard de codage de caractères qui utilise jusqu'à 32 bits pour représenter plus d'un million de caractères, couvrant presque tous les systèmes d'écriture du monde. Il a été créé pour résoudre les limitations d'ASCII et permettre l'internationalisation des logiciels.",
     "explanation":"UTF-8 est une implémentation de Unicode qui utilise 1 à 4 octets par caractère, compatible avec ASCII."},
    {"id":"898_7","type":"qcm","question":"Quel est le résultat de l'opération binaire 1010 AND 1100 ?",
     "options":["1000","1110","0110","1010"],
     "correct_answer":"1000",
     "explanation":"AND (ET) : 1 AND 1 = 1, sinon 0. 1010 AND 1100 = 1000."},
    {"id":"898_8","type":"vrai-faux","question":"Le complément à deux est une méthode pour représenter les nombres négatifs en binaire.",
     "correct_answer":"Vrai",
     "explanation":"Le complément à deux permet de faire des opérations arithmétiques sur les nombres négatifs sans avoir besoin de circuits séparés. Pour obtenir le complément à deux d'un nombre, on inverse les bits et on ajoute 1."},
]),

]


def write_quiz_files():
    os.makedirs(NSI1_QUIZ_DIR, exist_ok=True)
    os.makedirs(NSI1_ANSWERS_DIR, exist_ok=True)
    os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
    os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
    os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
    os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)

    for qid, title, subject, level, questions in quizzes_data:
        quiz_obj = make_quiz(qid, title, subject, level, questions)
        quiz_obj = normalize_text_payload(quiz_obj)
        quiz_path = os.path.join(NSI1_QUIZ_DIR, f"{qid}.json")
        with open(quiz_path, "w", encoding="utf-8", newline="\n") as fh:
            json.dump(quiz_obj, fh, ensure_ascii=False, indent=2)
            fh.write("\n")

        answers_obj = make_answers(qid, title, subject, level, questions)
        answers_obj = normalize_text_payload(answers_obj)
        answers_path = os.path.join(NSI1_ANSWERS_DIR, f"{qid}.json")
        with open(answers_path, "w", encoding="utf-8", newline="\n") as fh:
            json.dump(answers_obj, fh, ensure_ascii=False, indent=2)
            fh.write("\n")

        with open(os.path.join(OUTPUT_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as fh:
            json.dump(quiz_obj, fh, ensure_ascii=False, indent=2)
            fh.write("\n")

        with open(os.path.join(OUTPUT_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as fh:
            json.dump(answers_obj, fh, ensure_ascii=False, indent=2)
            fh.write("\n")

        with open(os.path.join(RUNTIME_QUIZ_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as fh:
            json.dump(quiz_obj, fh, ensure_ascii=False, indent=2)
            fh.write("\n")

        with open(os.path.join(RUNTIME_ANSWERS_DIR, f"{qid}.json"), "w", encoding="utf-8", newline="\n") as fh:
            json.dump(answers_obj, fh, ensure_ascii=False, indent=2)
            fh.write("\n")

        print(f"[OK] Quiz {qid} - {title} ({len(questions)} questions)")

    if quizzes_data:
        first_id = quizzes_data[0][0]
        last_id = quizzes_data[-1][0]
    else:
        first_id = last_id = "N/A"

    print("\n" + "=" * 60)
    print("  BATCH H - NSI 1ere TERMINE")
    print(f"  {len(quizzes_data)} quizzes generes (IDs {first_id}-{last_id})")
    print(f"  {len(quizzes_data) * 6} fichiers JSON crees")
    print(f"  ({NSI1_QUIZ_DIR} + {NSI1_ANSWERS_DIR})")
    print(f"  ({OUTPUT_QUIZ_DIR} + {OUTPUT_ANSWERS_DIR})")
    print(f"  ({RUNTIME_QUIZ_DIR} + {RUNTIME_ANSWERS_DIR})")
    print(f"  {len(quizzes_data) * 8} questions au total")
    print("=" * 60)


if __name__ == "__main__":
    write_quiz_files()

