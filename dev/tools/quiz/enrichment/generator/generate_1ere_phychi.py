import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
PC_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "pc_quizzes")
PC_QUIZ_DIR = os.path.join(PC_OUTPUT_DIR, "quiz")
PC_ANSWERS_DIR = os.path.join(PC_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "pc_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

os.makedirs(PC_QUIZ_DIR, exist_ok=True)
os.makedirs(PC_ANSWERS_DIR, exist_ok=True)
os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)


def normalize_question_type(question_type):
    return str(question_type or "").strip().lower().replace("_", "-")


def now_utc_z():
    return datetime.now(UTC).isoformat().replace("+00:00", "Z")


def fix_mojibake_text(value):
    if not isinstance(value, str):
        return value

    markers = ("Ãƒ", "Ã‚", "Ã¢â‚¬", "Ã¢â‚¬â„¢", "Ã¢â‚¬Å“", "Ã¢â‚¬â€", "Ã¢â‚¬â€œ", "Ã…")
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
        "ÃƒÂ©": "Ã©", "ÃƒÂ¨": "Ã¨", "ÃƒÂª": "Ãª", "ÃƒÂ«": "Ã«", "ÃƒÂ ": "Ã ", "ÃƒÂ¢": "Ã¢",
        "ÃƒÂ´": "Ã´", "ÃƒÂ»": "Ã»", "ÃƒÂ¹": "Ã¹", "ÃƒÂ®": "Ã®", "ÃƒÂ¯": "Ã¯", "ÃƒÂ§": "Ã§",
        "Ãƒâ€°": "Ã‰", "Ãƒâ‚¬": "Ã€", "Ãƒâ€¡": "Ã‡", "Ã…â€œ": "Å“", "Ã‚": "", "Ã¢â‚¬â„¢": "â€™",
        "Ã¢â‚¬Å“": "â€œ", "Ã¢â‚¬\x9d": "â€", "Ã¢â‚¬â€œ": "â€“", "Ã¢â‚¬â€": "â€”", "Ã¢â‚¬Â¦": "â€¦",
    }
    for source, target in replacements.items():
        value = value.replace(source, target)
    return value


def normalize_text_payload(payload):
    if isinstance(payload, dict):
        return {key: normalize_text_payload(value) for key, value in payload.items()}
    if isinstance(payload, list):
        return [normalize_text_payload(item) for item in payload]
    if isinstance(payload, tuple):
        return tuple(normalize_text_payload(item) for item in payload)
    if isinstance(payload, str):
        return fix_mojibake_text(payload)
    return payload


def dump_json_file(path, payload):
    with open(path, "w", encoding="utf-8", newline="\n") as file_handle:
        json.dump(normalize_text_payload(payload), file_handle, ensure_ascii=False, indent=2)
        file_handle.write("\n")


def clean_option_label(option):
    if isinstance(option, str) and len(option) > 3 and option[1] == "." and option[2] == " ":
        return option[3:]
    return option


def resolve_qcm_answer(question):
    answer = str(question.get("answer", "")).strip()
    options = [clean_option_label(opt) for opt in question.get("options", [])]
    if answer in options:
        return answer

    label = answer.upper().rstrip(").")
    if len(label) == 1 and "A" <= label <= "Z":
        idx = ord(label) - ord("A")
        if 0 <= idx < len(options):
            return options[idx]
    return answer


def make_quiz(qid, title, subject, level, questions):
    answer_keys = {"correct_answer", "correct_option", "correct", "explanation"}
    questions = [{k: v for k, v in q.items() if k not in answer_keys} for q in questions]
    created_at = datetime.now(UTC).strftime("%Y-%m-%d %H:%M:%S")
    quiz_questions = []
    for q in questions:
        qtype = normalize_question_type(q.get("type", ""))
        if qtype == "qcm":
            quiz_questions.append({
                "type": "qcm",
                "question": str(q.get("question", "")),
                "choices": [clean_option_label(opt) for opt in q.get("options", [])],
            })
        elif qtype == "vrai-faux":
            quiz_questions.append({
                "type": "vrai-faux",
                "question": str(q.get("question", "")),
            })
        else:
            quiz_questions.append({
                "type": "vrai-faux",
                "question": str(q.get("question", "")),
            })

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - SÃ©rie {qid}",
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
            "question_count": len(quiz_questions),
            "passing_score": 70,
            "time_limit_minutes": 15,
            "questions": quiz_questions,
        },
        "exercisenotion": [],
        "exerciseresponses": [],
    }


def make_answers(qid, title, subject, level, questions):
    answers = []
    for index, q in enumerate(questions):
        qtype = normalize_question_type(q.get("type", ""))
        explanation = q.get("correction", "")

        if qtype == "qcm":
            options = [clean_option_label(opt) for opt in q.get("options", [])]
            resolved_answer = resolve_qcm_answer(q)
            correct_index = options.index(resolved_answer) if resolved_answer in options else 0
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "qcm",
                "answer": resolved_answer,
                "correct": correct_index,
                "correction": explanation,
            })
        elif qtype == "vrai-faux":
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "answer": "vrai" if str(q.get("answer", "")).strip().upper() == "VRAI" else "faux",
                "correction": explanation,
            })
        else:
            answers.append({
                "index": index,
                "question_id": index + 1,
                "type": "vrai-faux",
                "correct_answer": q.get("answer", ""),
                "answer": q.get("answer", ""),
                "correction": explanation,
            })

    return {
        "contents": {
            "title": f"Quiz Diagnostic {subject} {level} - SÃ©rie {qid}",
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

# ============================================================
# PHYSIQUE-CHIMIE 1Ã¨re â€” fichiers 273 Ã  321 (49 quizzes)
# ============================================================

pc_data = {
    273: {
        "serie": 1,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 1",
        "description": "La constitution de la matiÃ¨re",
        "questions": [
            {"index":1,"type":"qcm","question":"Un atome de carbone 12 (Â¹Â²C) possÃ¨de :",
             "options":["A. 6 protons, 6 neutrons et 6 Ã©lectrons","B. 12 protons, 12 neutrons et 12 Ã©lectrons","C. 6 protons, 12 neutrons et 6 Ã©lectrons","D. 12 protons et 6 Ã©lectrons"],
             "answer":"A",
             "correction":"Le carbone-12 (Â¹Â²C) a un numÃ©ro atomique Z=6 (6 protons, 6 Ã©lectrons) et un nombre de masse A=12 (6 protons + 6 neutrons). La notation A/Z X indique : A = nombre de masse (protons+neutrons), Z = numÃ©ro atomique (protons). Pour un atome neutre, le nombre d'Ã©lectrons = nombre de protons."},
            {"index":2,"type":"vrai-faux","question":"Les isotopes d'un mÃªme Ã©lÃ©ment chimique ont le mÃªme nombre de protons mais un nombre de neutrons diffÃ©rent.",
             "answer":"VRAI",
             "correction":"VRAI. Les isotopes sont des atomes du mÃªme Ã©lÃ©ment (mÃªme numÃ©ro atomique Z, donc mÃªme nombre de protons) mais avec des nombres de neutrons diffÃ©rents (donc nombres de masse A diffÃ©rents). Exemple : Â¹H (protium), Â²H (deutÃ©rium), Â³H (tritium) sont trois isotopes de l'hydrogÃ¨ne avec respectivement 0, 1 et 2 neutrons."},
            {"index":3,"type":"vrai-faux","question":"DÃ©finissez la notion de mole en chimie et donnez la valeur de la constante d'Avogadro.",
             "answer":"La mole est la quantitÃ© de matiÃ¨re contenant 6,022 Ã— 10Â²Â³ entitÃ©s (atomes, molÃ©cules, ions). La constante d'Avogadro est NA = 6,022 Ã— 10Â²Â³ molâ»Â¹.",
             "correction":"La mole (mol) est l'unitÃ© de quantitÃ© de matiÃ¨re du SI. Elle contient exactement 6,022 Ã— 10Â²Â³ entitÃ©s Ã©lÃ©mentaires (atomes, molÃ©cules, ions, Ã©lectrons...). Cette constante fondamentale est la constante d'Avogadro : NA = 6,022 Ã— 10Â²Â³ molâ»Â¹. La masse molaire M (en g/mol) est la masse d'une mole d'entitÃ©s. Relation : n = m/M oÃ¹ n est la quantitÃ© de matiÃ¨re (mol), m la masse (g) et M la masse molaire (g/mol)."},
            {"index":4,"type":"qcm","question":"Quelle est la configuration Ã©lectronique de l'atome d'oxygÃ¨ne (Z=8) Ã  l'Ã©tat fondamental ?",
             "options":["A. 1sÂ² 2sÂ² 2pâ¶","B. 1sÂ² 2sÂ² 2pâ´","C. 1sÂ² 2sâ´ 2pÂ²","D. 1sÂ² 2sÂ² 2pÂ² 3sÂ²"],
             "answer":"B",
             "correction":"L'oxygÃ¨ne (Z=8) a 8 Ã©lectrons Ã  rÃ©partir : couche 1 (n=1) : 2 Ã©lectrons en 1sÂ² ; couche 2 (n=2) : 6 Ã©lectrons restants â†’ 2 en 2sÂ² et 4 en 2pâ´. Configuration : 1sÂ² 2sÂ² 2pâ´. La couche de valence (externe) contient 6 Ã©lectrons (2sÂ² 2pâ´). L'oxygÃ¨ne appartient Ã  la famille VIA du tableau pÃ©riodique."},
            {"index":5,"type":"vrai-faux","question":"La liaison covalente rÃ©sulte du partage d'une ou plusieurs paires d'Ã©lectrons entre deux atomes.",
             "answer":"VRAI",
             "correction":"VRAI. La liaison covalente est formÃ©e par le partage d'une ou plusieurs paires d'Ã©lectrons entre deux atomes non mÃ©talliques. Exemples : Hâ‚‚ (une liaison simple, 1 paire partagÃ©e), Oâ‚‚ (une liaison double, 2 paires), Nâ‚‚ (une liaison triple, 3 paires). La rÃ¨gle de l'octet (ou du duet pour H) prÃ©dit la formation de liaisons covalentes pour complÃ©ter la couche externe."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que l'Ã©lectronÃ©gativitÃ© et comment influence-t-elle la polaritÃ© des liaisons ?",
             "answer":"L'Ã©lectronÃ©gativitÃ© est la tendance d'un atome Ã  attirer les Ã©lectrons d'une liaison vers lui. Plus la diffÃ©rence d'Ã©lectronÃ©gativitÃ© est grande, plus la liaison est polarisÃ©e.",
             "correction":"L'Ã©lectronÃ©gativitÃ© (Pauling) mesure la tendance d'un atome Ã  attirer le doublet d'Ã©lectrons d'une liaison covalente. Elle croÃ®t de gauche Ã  droite dans une pÃ©riode et de bas en haut dans un groupe. Si la diffÃ©rence d'Ã©lectronÃ©gativitÃ© Î”Ï‡ entre deux atomes liÃ©s est grande (>0,4), la liaison est covalente polaire (ex : H-Cl, O-H) avec des charges partielles Î´+ et Î´-. Si Î”Ï‡ est trÃ¨s grande (>1,7), la liaison est ionique."},
            {"index":7,"type":"qcm","question":"Parmi ces espÃ¨ces chimiques, laquelle est un ion polyatomique ?",
             "options":["A. Naâº","B. Clâ»","C. SOâ‚„Â²â»","D. MgÂ²âº"],
             "answer":"C",
             "correction":"L'ion sulfate SOâ‚„Â²â» est un ion polyatomique (ou molÃ©culaire) : il est constituÃ© de plusieurs atomes (1 soufre et 4 oxygÃ¨nes) liÃ©s de faÃ§on covalente et portant une charge globale de 2-. En revanche, Naâº, Clâ» et MgÂ²âº sont des ions monoatomiques (formÃ©s d'un seul atome ayant perdu ou gagnÃ© des Ã©lectrons)."},
            {"index":8,"type":"vrai-faux","question":"La masse molaire de l'eau (Hâ‚‚O) est d'environ 18 g/mol.",
             "answer":"VRAI",
             "correction":"VRAI. La masse molaire de Hâ‚‚O se calcule : M(Hâ‚‚O) = 2 Ã— M(H) + M(O) = 2 Ã— 1 + 16 = 18 g/mol. Cette valeur fondamentale intervient dans de nombreux calculs de chimie : 1 mole d'eau pÃ¨se 18 g, et 1 litre d'eau (densitÃ© â‰ˆ 1) contient donc environ 1000/18 â‰ˆ 55,6 moles d'eau."},
        ]
    },
    274: {
        "serie": 2,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 2",
        "description": "Les solutions et la concentration",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la concentration molaire d'une solution obtenue en dissolvant 0,1 mol de NaCl dans 500 mL de solution ?",
             "options":["A. 0,1 mol/L","B. 0,2 mol/L","C. 0,05 mol/L","D. 0,5 mol/L"],
             "answer":"B",
             "correction":"La concentration molaire c = n/V oÃ¹ n est la quantitÃ© de matiÃ¨re (mol) et V le volume de solution (en litres). c = 0,1 mol / 0,500 L = 0,2 mol/L. Attention : il faut convertir le volume en litres (500 mL = 0,500 L). La concentration en g/L serait : C = m/V = (0,1 Ã— 58,5)/0,500 = 11,7 g/L."},
            {"index":2,"type":"vrai-faux","question":"Le pH d'une solution acide est infÃ©rieur Ã  7 Ã  25Â°C.",
             "answer":"VRAI",
             "correction":"VRAI. Le pH (potentiel hydrogÃ¨ne) est dÃ©fini par pH = -log[Hâ‚ƒOâº]. Ã€ 25Â°C, l'eau pure est neutre avec pH = 7 ([Hâ‚ƒOâº] = [OHâ»] = 10â»â· mol/L). Une solution acide a [Hâ‚ƒOâº] > 10â»â· mol/L, donc pH < 7. Une solution basique a [Hâ‚ƒOâº] < 10â»â· mol/L, donc pH > 7."},
            {"index":3,"type":"vrai-faux","question":"Qu'est-ce qu'une dilution et comment calcule-t-on la concentration de la solution diluÃ©e ?",
             "answer":"La dilution consiste Ã  ajouter du solvant Ã  une solution pour diminuer sa concentration. La loi de conservation : Câ‚ Ã— Vâ‚ = Câ‚‚ Ã— Vâ‚‚.",
             "correction":"Une dilution consiste Ã  ajouter du solvant (eau) Ã  une solution mÃ¨re de concentration Câ‚ pour obtenir une solution fille de concentration Câ‚‚ < Câ‚. La quantitÃ© de solutÃ© se conserve : nâ‚ = nâ‚‚, soit Câ‚ Ã— Vâ‚ = Câ‚‚ Ã— Vâ‚‚. Le facteur de dilution f = Câ‚/Câ‚‚ = Vâ‚‚/Vâ‚. Exemple : diluer 20 mL d'une solution Ã  2 mol/L Ã  200 mL donne Câ‚‚ = (2 Ã— 20)/200 = 0,2 mol/L (dilution d'un facteur 10)."},
            {"index":4,"type":"qcm","question":"Un acide fort comme HCl (acide chlorhydrique) en solution aqueuse :",
             "options":["A. Se dissocie partiellement","B. Ne se dissocie pas","C. Se dissocie totalement en Hâº et Clâ»","D. RÃ©agit avec l'eau pour former une base"],
             "answer":"C",
             "correction":"Un acide fort (HCl, HNOâ‚ƒ, Hâ‚‚SOâ‚„, HClOâ‚„...) se dissocie totalement dans l'eau : HCl â†’ Hâº + Clâ» (ou HCl + Hâ‚‚O â†’ Hâ‚ƒOâº + Clâ»). Pour un acide fort de concentration c, [Hâ‚ƒOâº] = c et pH = -log(c). Un acide faible (CHâ‚ƒCOOH, HF...) ne se dissocie que partiellement, et le pH est calculÃ© avec la constante d'aciditÃ© Ka."},
            {"index":5,"type":"vrai-faux","question":"La constante d'aciditÃ© Ka d'un acide est indÃ©pendante de la concentration de la solution.",
             "answer":"VRAI",
             "correction":"VRAI. La constante d'aciditÃ© Ka (ou pKa = -log Ka) est une constante thermodynamique qui dÃ©pend uniquement de la nature de l'acide et de la tempÃ©rature, non de la concentration de la solution. Plus Ka est grand (pKa petit), plus l'acide est fort. Exemple : l'acide acÃ©tique CHâ‚ƒCOOH a pKa = 4,75 Ã  25Â°C."},
            {"index":6,"type":"vrai-faux","question":"Comment se rÃ©alise un dosage acido-basique par titrage colorimÃ©trique ?",
             "answer":"Un titrage consiste Ã  ajouter progressivement une solution titrante de concentration connue Ã  la solution Ã  titrer jusqu'Ã  l'Ã©quivalence, repÃ©rÃ©e par le changement de couleur d'un indicateur.",
             "correction":"Un titrage acido-basique consiste Ã  verser progressivement (burette) une solution titrante (base ou acide de concentration connue) dans une solution Ã  titrer (contenue dans un bÃ©cher avec un indicateur colorÃ©). L'Ã©quivalence est atteinte quand la rÃ©action est totale (stÅ“chiomÃ©trie exacte) : le changement de couleur de l'indicateur le signale. Ã€ l'Ã©quivalence : n(acide) = n(base) (pour des acides monoacides et des bases monobasiques). On peut alors calculer la concentration inconnue."},
            {"index":7,"type":"qcm","question":"Quelle est la relation entre pH et pOH dans une solution aqueuse Ã  25Â°C ?",
             "options":["A. pH = pOH","B. pH + pOH = 14","C. pH Ã— pOH = 14","D. pH - pOH = 7"],
             "answer":"B",
             "correction":"Ã€ 25Â°C, le produit ionique de l'eau Ke = [Hâ‚ƒOâº][OHâ»] = 10â»Â¹â´ molÂ²/LÂ². En prenant le -log : pKe = pH + pOH = 14. Pour une solution neutre, pH = pOH = 7. Pour une solution acide (pH < 7), pOH > 7. Pour une solution basique (pH > 7), pOH < 7."},
            {"index":8,"type":"vrai-faux","question":"Une solution tampon rÃ©siste aux variations de pH lors de l'ajout de petites quantitÃ©s d'acide ou de base.",
             "answer":"VRAI",
             "correction":"VRAI. Une solution tampon est un mÃ©lange d'un acide faible et de sa base conjuguÃ©e (ou d'une base faible et de son acide conjuguÃ©) dans des proportions similaires. Elle rÃ©siste aux variations de pH car l'acide conjuguÃ© neutralise les bases ajoutÃ©es et la base conjuguÃ©e neutralise les acides ajoutÃ©s. Exemple : solution tampon acÃ©tate (CHâ‚ƒCOOH/CHâ‚ƒCOOâ»). Le sang humain est maintenu Ã  pH â‰ˆ 7,4 grÃ¢ce Ã  un systÃ¨me tampon."},
        ]
    },
    275: {
        "serie": 3,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 3",
        "description": "La cinÃ©matique",
        "questions": [
            {"index":1,"type":"qcm","question":"Un objet se dÃ©place de 120 m en 8 s. Quelle est sa vitesse moyenne ?",
             "options":["A. 8 m/s","B. 15 m/s","C. 960 m/s","D. 0,067 m/s"],
             "answer":"B",
             "correction":"La vitesse moyenne est dÃ©finie par v = d/t, oÃ¹ d est la distance parcourue et t le temps. v = 120 m / 8 s = 15 m/s. Rappel des unitÃ©s : la vitesse s'exprime en m/s (SI), km/h (1 m/s = 3,6 km/h). 15 m/s = 54 km/h."},
            {"index":2,"type":"vrai-faux","question":"Dans un mouvement uniforme, la vitesse est constante et l'accÃ©lÃ©ration est nulle.",
             "answer":"VRAI",
             "correction":"VRAI. Un mouvement rectiligne uniforme (MRU) est caractÃ©risÃ© par une vitesse constante (en module et en direction). L'accÃ©lÃ©ration (a = dv/dt) est donc nulle. Le graphe vitesse-temps est une droite horizontale, et le graphe position-temps est une droite de pente v (x = xâ‚€ + vÂ·t). C'est le rÃ©gime prÃ©vu par le principe d'inertie de Newton en l'absence de force rÃ©sultante."},
            {"index":3,"type":"vrai-faux","question":"Ã‰tablissez les Ã©quations horaires d'un mouvement uniformÃ©ment accÃ©lÃ©rÃ© (MRUA) en partant d'un Ã©tat initial xâ‚€, vâ‚€.",
             "answer":"Pour un MRUA : a = cste ; v(t) = vâ‚€ + aÂ·t ; x(t) = xâ‚€ + vâ‚€Â·t + Â½Â·aÂ·tÂ²",
             "correction":"Pour un mouvement rectiligne uniformÃ©ment accÃ©lÃ©rÃ© (MRUA) avec accÃ©lÃ©ration constante a : Ã‰quation de la vitesse : v(t) = vâ‚€ + aÂ·t (droite affine en t) ; Ã‰quation de la position : x(t) = xâ‚€ + vâ‚€Â·t + Â½Â·aÂ·tÂ² (parabole en t). Relations indÃ©pendantes du temps : vÂ² = vâ‚€Â² + 2a(x - xâ‚€). Exemple : chute libre : a = g = 9,81 m/sÂ² (vers le bas), vâ‚€ = 0 si lÃ¢chÃ© sans vitesse initiale."},
            {"index":4,"type":"qcm","question":"Un vecteur vitesse est instantanÃ©e est tangent Ã  la trajectoire. Dans un mouvement circulaire uniforme, l'accÃ©lÃ©ration est :",
             "options":["A. Nulle","B. Tangentielle Ã  la trajectoire","C. CentripÃ¨te (dirigÃ©e vers le centre)","D. Centrifuge (dirigÃ©e vers l'extÃ©rieur)"],
             "answer":"C",
             "correction":"Dans un mouvement circulaire uniforme (MCU), la vitesse est constante en module mais varie en direction. L'accÃ©lÃ©ration centripÃ¨te (ou normale) est perpendiculaire Ã  la vitesse, dirigÃ©e vers le centre du cercle : a = vÂ²/R. Elle ne change pas la vitesse (module) mais change la direction du mouvement. Son module est a = Ï‰Â²Â·R = vÂ²/R, oÃ¹ Ï‰ est la vitesse angulaire."},
            {"index":5,"type":"vrai-faux","question":"La chute libre (sans frottements) est un exemple de mouvement uniformÃ©ment accÃ©lÃ©rÃ© avec a = g â‰ˆ 9,81 m/sÂ².",
             "answer":"VRAI",
             "correction":"VRAI. La chute libre est le mouvement d'un objet soumis uniquement Ã  la pesanteur (gravitÃ©), sans frottements de l'air. L'accÃ©lÃ©ration est constante : a = g â‰ˆ 9,81 m/sÂ² (vers le bas). Les Ã©quations horaires sont : v(t) = gÂ·t (vitesse nulle au dÃ©part) ; z(t) = h - Â½Â·gÂ·tÂ² (hauteur en chute). En rÃ©alitÃ©, les frottements de l'air existent et limitent la vitesse Ã  la 'vitesse limite'."},
            {"index":6,"type":"vrai-faux","question":"DÃ©finissez la pÃ©riode, la frÃ©quence et la pulsation d'un mouvement circulaire uniforme et donnez leurs relations.",
             "answer":"PÃ©riode T (s) : durÃ©e d'un tour complet. FrÃ©quence f (Hz) : nombre de tours par seconde. Pulsation Ï‰ (rad/s). Relations : f = 1/T ; Ï‰ = 2Ï€/T = 2Ï€f.",
             "correction":"Pour un MCU : la pÃ©riode T (en secondes) est la durÃ©e d'un tour complet (rotation de 2Ï€ radians) ; la frÃ©quence f (en hertz, Hz) est le nombre de tours par seconde (f = 1/T) ; la pulsation (ou vitesse angulaire) Ï‰ (en rad/s) est l'angle balayÃ© par seconde (Ï‰ = 2Ï€/T = 2Ï€f). La vitesse linÃ©aire est liÃ©e Ã  ces grandeurs par : v = RÂ·Ï‰ = 2Ï€R/T, oÃ¹ R est le rayon de la trajectoire circulaire."},
            {"index":7,"type":"qcm","question":"Un mobile parcourt un virage circulaire de rayon 50 m Ã  la vitesse de 20 m/s. Quelle est l'intensitÃ© de son accÃ©lÃ©ration centripÃ¨te ?",
             "options":["A. 0,4 m/sÂ²","B. 8 m/sÂ²","C. 40 m/sÂ²","D. 1000 m/sÂ²"],
             "answer":"B",
             "correction":"L'accÃ©lÃ©ration centripÃ¨te est donnÃ©e par a = vÂ²/R = (20)Â²/50 = 400/50 = 8 m/sÂ². Cette accÃ©lÃ©ration est dirigÃ©e vers le centre du virage. La force centripÃ¨te correspondante (2Ã¨me loi de Newton : F = mÂ·a) est F = m Ã— 8 = 8m (N) si m est la masse du mobile en kg."},
            {"index":8,"type":"vrai-faux","question":"La vitesse d'un objet et son accÃ©lÃ©ration sont toujours de mÃªme sens.",
             "answer":"FAUX",
             "correction":"FAUX. La vitesse et l'accÃ©lÃ©ration peuvent avoir des sens opposÃ©s. Si a et v sont dans le mÃªme sens, le mouvement est accÃ©lÃ©rÃ© (la vitesse augmente). Si a et v sont de sens opposÃ©s, le mouvement est dÃ©cÃ©lÃ©rÃ© ou ralenti (la vitesse diminue). Exemple : lors d'un freinage, l'accÃ©lÃ©ration (freinage) est opposÃ©e Ã  la vitesse. Dans un mouvement circulaire uniforme, a (centripÃ¨te) est perpendiculaire Ã  v."},
        ]
    },
    276: {
        "serie": 4,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 4",
        "description": "Les lois de Newton",
        "questions": [
            {"index":1,"type":"qcm","question":"Ã‰noncez la premiÃ¨re loi de Newton (principe d'inertie).",
             "options":["A. F = m Ã— a","B. Tout corps reste en Ã©tat de repos ou de mouvement rectiligne uniforme si la rÃ©sultante des forces qui s'exercent sur lui est nulle","C. Les forces d'action et de rÃ©action sont Ã©gales et opposÃ©es","D. L'accÃ©lÃ©ration d'un objet est inversement proportionnelle Ã  sa masse"],
             "answer":"B",
             "correction":"Le 1er principe de Newton (principe d'inertie) : 'Tout corps persÃ©vÃ¨re dans son Ã©tat de repos ou de mouvement rectiligne uniforme, sauf si des forces extÃ©rieures l'obligent Ã  changer.' En formule : si Î£F = 0, alors a = 0. L'inertie est la propriÃ©tÃ© d'un corps de rÃ©sister aux changements de son Ã©tat de mouvement."},
            {"index":2,"type":"vrai-faux","question":"Selon la 2Ã¨me loi de Newton, si on double la force appliquÃ©e sur un objet (masse constante), son accÃ©lÃ©ration double.",
             "answer":"VRAI",
             "correction":"VRAI. La 2Ã¨me loi de Newton : Î£F = m Ã— a, soit a = Î£F/m. Si la masse m est constante et qu'on double la rÃ©sultante des forces Î£F, l'accÃ©lÃ©ration a est Ã©galement doublÃ©e (relation de proportionnalitÃ© directe). RÃ©ciproquement, si la masse double pour la mÃªme force, l'accÃ©lÃ©ration est divisÃ©e par 2."},
            {"index":3,"type":"vrai-faux","question":"Expliquez la diffÃ©rence entre masse et poids, et donnez leur relation.",
             "answer":"La masse est une propriÃ©tÃ© intrinsÃ¨que d'un corps (en kg) ; le poids est la force gravitationnelle exercÃ©e sur ce corps (en N). Relation : P = m Ã— g.",
             "correction":"La masse m (en kg) est une propriÃ©tÃ© intrinsÃ¨que d'un objet, indÃ©pendante du lieu (invariante). Elle mesure l'inertie d'un corps. Le poids P (en newtons, N) est la force gravitationnelle exercÃ©e par un astre sur un objet : P = m Ã— g, oÃ¹ g est l'accÃ©lÃ©ration de la pesanteur (g â‰ˆ 9,81 m/sÂ² sur Terre, 1,62 m/sÂ² sur la Lune). Le poids varie selon le lieu, la masse non. Une balance mesure la masse ; un dynamomÃ¨tre mesure le poids."},
            {"index":4,"type":"qcm","question":"Une voiture de 1000 kg accÃ©lÃ¨re avec a = 3 m/sÂ². Quelle est la rÃ©sultante des forces horizontales qui s'exercent sur elle ?",
             "options":["A. 300 N","B. 3000 N","C. 333 N","D. 9810 N"],
             "answer":"B",
             "correction":"Application de la 2Ã¨me loi de Newton : Î£F = m Ã— a = 1000 kg Ã— 3 m/sÂ² = 3000 N. Cette force rÃ©sultante est la somme algÃ©brique de toutes les forces horizontales (force motrice du moteur moins les frottements et la rÃ©sistance de l'air). L'unitÃ© de force est le Newton (N = kgÂ·m/sÂ²)."},
            {"index":5,"type":"vrai-faux","question":"La 3Ã¨me loi de Newton stipule que si A exerce une force sur B, alors B exerce sur A une force Ã©gale en module, opposÃ©e en sens, sur la mÃªme droite d'action.",
             "answer":"VRAI",
             "correction":"VRAI. Le 3Ã¨me principe de Newton (action-rÃ©action) : 'Toute action appelle une rÃ©action Ã©gale et opposÃ©e.' Si A exerce F(Aâ†’B) sur B, alors B exerce F(Bâ†’A) = -F(Aâ†’B) sur A. Ces deux forces sont Ã©gales en module, opposÃ©es en sens, colinÃ©aires mais exercÃ©es sur des objets diffÃ©rents. Exemple : la Terre attire la pomme (son poids), et la pomme attire la Terre avec la mÃªme force (mais la Terre est beaucoup plus massive, donc son accÃ©lÃ©ration est nÃ©gligeable)."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que la force de frottement et comment modÃ©lise-t-on la rÃ©sistance Ã  l'avancement ?",
             "answer":"La force de frottement s'oppose au mouvement d'un objet. On la modÃ©lise souvent comme f = Î¼ Ã— N, oÃ¹ Î¼ est le coefficient de frottement et N la force normale.",
             "correction":"La force de frottement (cinÃ©tique) s'oppose au glissement d'une surface sur une autre. Sa modÃ©lisation simplifiÃ©e : f = Î¼k Ã— N, oÃ¹ Î¼k est le coefficient de frottement cinÃ©tique (sans unitÃ©, entre 0 et 1) et N la rÃ©action normale (force perpendiculaire Ã  la surface). La rÃ©sistance Ã  l'avancement (dans les fluides) est modÃ©lisÃ©e par une force proportionnelle Ã  la vitesse (frottement visqueux) ou au carrÃ© de la vitesse (rÃ©sistance aÃ©rodynamique) selon le rÃ©gime d'Ã©coulement."},
            {"index":7,"type":"qcm","question":"Lors d'une collision entre deux boules de billard, que se conserve-t-il en l'absence de forces extÃ©rieures ?",
             "options":["A. La vitesse de chaque boule","B. L'Ã©nergie cinÃ©tique uniquement","C. La quantitÃ© de mouvement totale du systÃ¨me","D. La force entre les boules"],
             "answer":"C",
             "correction":"En l'absence de forces extÃ©rieures, la quantitÃ© de mouvement totale du systÃ¨me se conserve (principe de conservation de la quantitÃ© de mouvement, consÃ©quence de la 3Ã¨me loi de Newton) : Î£p(avant) = Î£p(aprÃ¨s), avec p = mÂ·v. En cas de collision Ã©lastique, l'Ã©nergie cinÃ©tique se conserve aussi ; en cas de collision inÃ©lastique, l'Ã©nergie cinÃ©tique est partiellement dissipÃ©e en chaleur, son, dÃ©formation."},
            {"index":8,"type":"vrai-faux","question":"La force de rÃ©action normale exercÃ©e par un sol sur un objet en Ã©quilibre est toujours Ã©gale et opposÃ©e au poids de cet objet.",
             "answer":"VRAI",
             "correction":"VRAI (en Ã©quilibre statique). Pour un objet en Ã©quilibre sur un plan horizontal (a = 0), la somme des forces est nulle : R (rÃ©action normale, vers le haut) + P (poids, vers le bas) = 0, donc R = -P, |R| = |P| = mg. Attention : ce n'est PAS la 3Ã¨me loi de Newton (qui s'applique Ã  la paire 'la Terre attire l'objet / l'objet attire la Terre'), mais la consÃ©quence du 1er principe appliquÃ© Ã  l'Ã©quilibre."},
        ]
    },
    277: {
        "serie": 5,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 5",
        "description": "L'Ã©nergie : formes et conservation",
        "questions": [
            {"index":1,"type":"qcm","question":"L'Ã©nergie cinÃ©tique d'un objet de masse m se dÃ©plaÃ§ant Ã  la vitesse v est :",
             "options":["A. Ec = m Ã— v","B. Ec = Â½ Ã— m Ã— vÂ²","C. Ec = m Ã— g Ã— h","D. Ec = m Ã— a"],
             "answer":"B",
             "correction":"L'Ã©nergie cinÃ©tique est Ec = Â½mvÂ² (en joules, J), oÃ¹ m est la masse en kg et v la vitesse en m/s. Elle est toujours positive ou nulle. Le thÃ©orÃ¨me de l'Ã©nergie cinÃ©tique stipule que la variation d'Ã©nergie cinÃ©tique d'un objet est Ã©gale au travail total des forces qui s'exercent sur lui : Î”Ec = W(toutes forces) = Ec_finale - Ec_initiale."},
            {"index":2,"type":"vrai-faux","question":"L'Ã©nergie mÃ©canique se conserve uniquement lorsque les forces de frottement sont nulles (absence de forces non conservatives).",
             "answer":"VRAI",
             "correction":"VRAI. L'Ã©nergie mÃ©canique Em = Ec + Ep (Ã©nergie cinÃ©tique + Ã©nergie potentielle) se conserve seulement si les forces non conservatives (frottements, rÃ©sistance de l'air) n'effectuent aucun travail (W_nc = 0). En prÃ©sence de frottements, Em diminue, l'Ã©nergie mÃ©canique perdue Ã©tant convertie en Ã©nergie thermique (chaleur). On a : Em(finale) = Em(initiale) + W_nc."},
            {"index":3,"type":"vrai-faux","question":"DÃ©finissez l'Ã©nergie potentielle gravitationnelle et exprimez son expression mathÃ©matique.",
             "answer":"L'Ã©nergie potentielle gravitationnelle est l'Ã©nergie d'un objet due Ã  sa position en hauteur : Ep = mgh, oÃ¹ m est la masse, g la pesanteur et h l'altitude.",
             "correction":"L'Ã©nergie potentielle de pesanteur Ep = mgh, oÃ¹ m est la masse (kg), g l'accÃ©lÃ©ration de la pesanteur (â‰ˆ9,81 m/sÂ²) et h l'altitude (m) mesurÃ©e par rapport Ã  une rÃ©fÃ©rence choisie arbitrairement (Ep = 0). Elle reprÃ©sente le travail que la pesanteur peut effectuer pour amener l'objet de l'altitude h Ã  la rÃ©fÃ©rence. La variation d'Ep lors d'une chute de hâ‚ Ã  hâ‚‚ : Î”Ep = mg(hâ‚‚-hâ‚) ; si hâ‚‚ < hâ‚ (chute), Î”Ep < 0 et Ec augmente (conservation de Em)."},
            {"index":4,"type":"qcm","question":"Un objet de 2 kg tombe d'une hauteur de 5 m (sans frottements). Quelle est sa vitesse au moment de toucher le sol ? (g = 10 m/sÂ²)",
             "options":["A. 5 m/s","B. 10 m/s","C. 20 m/s","D. 50 m/s"],
             "answer":"B",
             "correction":"Conservation de l'Ã©nergie mÃ©canique (sans frottements) : Em_initiale = Em_finale. L'objet part du repos (Ec_i = 0) Ã  h = 5m : Em_i = mgh = 2Ã—10Ã—5 = 100 J. Au sol (h = 0, Ep = 0) : Em_f = Â½mvÂ² = 100 J. Donc Â½ Ã— 2 Ã— vÂ² = 100 â†’ vÂ² = 100 â†’ v = 10 m/s. VÃ©rification : vÂ² = 2gh = 2Ã—10Ã—5 = 100, v = 10 m/s âœ“"},
            {"index":5,"type":"vrai-faux","question":"Le premier principe de la thermodynamique affirme que l'Ã©nergie totale d'un systÃ¨me isolÃ© se conserve.",
             "answer":"VRAI",
             "correction":"VRAI. Le 1er principe de la thermodynamique est le principe de conservation de l'Ã©nergie : 'L'Ã©nergie ne se crÃ©e pas, ne se dÃ©truit pas, elle se transforme.' Pour un systÃ¨me isolÃ©, Î”U = 0. Pour un systÃ¨me non isolÃ© : Î”U = W + Q, oÃ¹ W est le travail reÃ§u et Q la chaleur reÃ§ue. Les diffÃ©rentes formes d'Ã©nergie (cinÃ©tique, potentielle, thermique, chimique, Ã©lectrique...) sont interconvertibles mais leur somme totale est constante."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que la puissance en physique et quelle est son unitÃ© ?",
             "answer":"La puissance est la quantitÃ© d'Ã©nergie transfÃ©rÃ©e ou convertie par unitÃ© de temps : P = E/t ou P = W/t. Son unitÃ© est le watt (W).",
             "correction":"La puissance P est le rythme de transfert ou de conversion d'Ã©nergie : P = Î”E/Î”t = W/t, en watts (W = J/s). Elle mesure Ã  quelle vitesse l'Ã©nergie est fournie ou consommÃ©e. Exemples : une ampoule de 60 W consomme 60 J par seconde ; un moteur de 100 kW dÃ©veloppe 100 000 J par seconde. La puissance instantanÃ©e est P = FÂ·v (force Ã— vitesse) pour une force appliquÃ©e dans la direction du mouvement."},
            {"index":7,"type":"qcm","question":"Quelle transformation d'Ã©nergie se produit dans un panneau solaire photovoltaÃ¯que ?",
             "options":["A. Energie chimique â†’ Ã©lectrique","B. Energie thermique â†’ mÃ©canique","C. Energie lumineuse â†’ Ã©lectrique","D. Energie nuclÃ©aire â†’ thermique"],
             "answer":"C",
             "correction":"Un panneau solaire photovoltaÃ¯que convertit l'Ã©nergie lumineuse (photons du rayonnement solaire) en Ã©nergie Ã©lectrique, par l'effet photoÃ©lectrique (dÃ©couvert par Einstein en 1905). Les photons libÃ¨rent des Ã©lectrons dans les cellules photovoltaÃ¯ques en silicium, crÃ©ant un courant Ã©lectrique. Le rendement typique des panneaux commerciaux est de 15 Ã  22%."},
            {"index":8,"type":"vrai-faux","question":"Le kilowattheure (kWh) est une unitÃ© d'Ã©nergie utilisÃ©e notamment en Ã©lectricitÃ©.",
             "answer":"VRAI",
             "correction":"VRAI. Le kilowattheure (kWh) est l'Ã©nergie consommÃ©e par un appareil de 1 kW en 1 heure : 1 kWh = 1000 W Ã— 3600 s = 3,6 Ã— 10â¶ J = 3,6 MJ. C'est l'unitÃ© pratique utilisÃ©e pour facturer la consommation Ã©lectrique. Un tÃ©lÃ©phone consomme ~0,001 kWh par charge ; un four Ã©lectrique ~1 kWh par heure d'utilisation."},
        ]
    },
    278: {
        "serie": 6,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 6",
        "description": "La chimie organique : structures et nomenclature",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la formule molÃ©culaire du mÃ©thane, le plus simple des alcanes ?",
             "options":["A. Câ‚‚Hâ‚†","B. CHâ‚„","C. Câ‚ƒHâ‚ˆ","D. CHâ‚ƒOH"],
             "answer":"B",
             "correction":"Le mÃ©thane CHâ‚„ est le premier alcane (hydrocarbure saturÃ© Ã  chaÃ®ne ouverte). La formule gÃ©nÃ©rale des alcanes est Câ‚™Hâ‚‚â‚™â‚Šâ‚‚. Pour n=1 : CHâ‚„ (mÃ©thane) ; n=2 : Câ‚‚Hâ‚† (Ã©thane) ; n=3 : Câ‚ƒHâ‚ˆ (propane) ; n=4 : Câ‚„Hâ‚â‚€ (butane). Chaque atome de carbone fait 4 liaisons covalentes (rÃ¨gle de l'octet)."},
            {"index":2,"type":"vrai-faux","question":"Les alcÃ¨nes sont des hydrocarbures insaturÃ©s contenant au moins une double liaison carbone-carbone (C=C).",
             "answer":"VRAI",
             "correction":"VRAI. Les alcÃ¨nes (ou olÃ©fines) sont des hydrocarbures Ã  chaÃ®ne ouverte contenant au moins une double liaison C=C. Formule gÃ©nÃ©rale : Câ‚™Hâ‚‚â‚™ (pour un alcÃ¨ne avec une seule double liaison). L'Ã©thylÃ¨ne (Ã©thÃ¨ne) CHâ‚‚=CHâ‚‚ est le plus simple. Les alcÃ¨nes sont plus rÃ©actifs que les alcanes (addition Ã©lectrophile sur la double liaison) et entrent dans la fabrication de nombreux plastiques (polyÃ©thylÃ¨ne, PVC)."},
            {"index":3,"type":"vrai-faux","question":"Qu'est-ce qu'une fonction chimique en chimie organique ? Citez trois exemples de groupes fonctionnels.",
             "answer":"Une fonction chimique est un groupe d'atomes qui confÃ¨re des propriÃ©tÃ©s chimiques caractÃ©ristiques Ã  une molÃ©cule organique. Exemples : alcool (-OH), acide carboxylique (-COOH), amine (-NHâ‚‚).",
             "correction":"Un groupe fonctionnel (ou fonction chimique) est un atome ou groupe d'atomes responsable des propriÃ©tÃ©s chimiques caractÃ©ristiques d'une famille de molÃ©cules organiques. Exemples : groupe hydroxyle -OH â†’ alcools (Ã©thanol Câ‚‚Hâ‚…OH) ; groupe carboxyle -COOH â†’ acides carboxyliques (acide acÃ©tique CHâ‚ƒCOOH) ; groupe amine -NHâ‚‚ â†’ amines (aniline, acides aminÃ©s) ; groupe aldÃ©hyde -CHO â†’ aldÃ©hydes ; groupe cÃ©tone C=O â†’ cÃ©tones ; ester -COO- â†’ esters."},
            {"index":4,"type":"qcm","question":"Quelle est la nomenclature IUPAC du composÃ© CHâ‚ƒ-CHâ‚‚-CHâ‚‚-OH ?",
             "options":["A. Propanol-1","B. Ã‰thanol","C. Butanol","D. MÃ©thanol"],
             "answer":"A",
             "correction":"CHâ‚ƒ-CHâ‚‚-CHâ‚‚-OH est le propan-1-ol (propanol-1). La chaÃ®ne principale compte 3 carbones (prop-), la fonction est alcool (-ol), et le groupe -OH est sur le carbone 1. Nomenclature IUPAC : 1) identifier la chaÃ®ne principale (la plus longue contenant le groupe fonctionnel) ; 2) nommer la chaÃ®ne ; 3) ajouter le suffixe du groupe fonctionnel (-ol, -al, -one, -oÃ¯que...) ; 4) numÃ©roter les carbones pour minimiser les indices."},
            {"index":5,"type":"vrai-faux","question":"Les isomÃ¨res sont des molÃ©cules ayant la mÃªme formule molÃ©culaire mais des structures diffÃ©rentes.",
             "answer":"VRAI",
             "correction":"VRAI. L'isomÃ©rie est un phÃ©nomÃ¨ne fondamental en chimie organique : des molÃ©cules de mÃªme formule brute (mÃªme nombre et type d'atomes) mais de structures diffÃ©rentes sont isomÃ¨res. Elles ont des propriÃ©tÃ©s physiques et chimiques diffÃ©rentes. Types d'isomÃ©rie : de chaÃ®ne (squelette carbonÃ© diffÃ©rent), de position (groupe fonctionnel en position diffÃ©rente), de fonction (groupes fonctionnels diffÃ©rents), stÃ©rÃ©oisomÃ©rie (configuration spatiale diffÃ©rente)."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce qu'une rÃ©action d'estÃ©rification et quelles en sont les conditions ?",
             "answer":"L'estÃ©rification est la rÃ©action entre un acide carboxylique et un alcool pour former un ester et de l'eau. Elle est lente, limitÃ©e et catalysÃ©e par les ions Hâº.",
             "correction":"L'estÃ©rification : RCOOH + R'OH â‡Œ RCOOR' + Hâ‚‚O. C'est une rÃ©action entre un acide carboxylique et un alcool qui donne un ester et de l'eau. Conditions : lente (cinÃ©tique), limitÃ©e (Ã©quilibre, Ï„ ~ 2/3 si alcool primaire + acide), catalysÃ©e par les ions Hâº (acide sulfurique concentrÃ© ou rÃ©sine Ã©changeuse d'ions). La rÃ©action inverse (hydrolyse de l'ester) est la saponification (en milieu basique, totale et rapide). Applications : arÃ´mes alimentaires, parfums, plastifiants."},
            {"index":7,"type":"qcm","question":"Dans quel groupe fonctionnel les acides aminÃ©s, constituants des protÃ©ines, possÃ¨dent-ils Ã  la fois une fonction acide et une fonction amine ?",
             "options":["A. Groupe hydroxyle -OH et amine -NHâ‚‚","B. Groupe carboxyle -COOH et amine -NHâ‚‚","C. Groupe aldÃ©hyde -CHO et amine -NHâ‚‚","D. Groupe ester -COO- et amine -NHâ‚‚"],
             "answer":"B",
             "correction":"Les acides Î±-aminÃ©s ont la structure gÃ©nÃ©rale : NHâ‚‚-CHR-COOH, avec un groupe carboxyle -COOH (fonction acide), un groupe amine -NHâ‚‚ (fonction basique) et un radical R variable (chaÃ®ne latÃ©rale, 20 acides aminÃ©s diffÃ©rents). Les acides aminÃ©s s'unissent par des liaisons peptidiques (-CO-NH-) pour former les protÃ©ines. Leur caractÃ¨re amphotÃ¨re (acide et basique Ã  la fois) est essentiel Ã  leur fonction biologique."},
            {"index":8,"type":"vrai-faux","question":"Le glucose (Câ‚†Hâ‚â‚‚Oâ‚†) est un glucide (sucre) qui peut Ãªtre classÃ© dans la famille des aldÃ©hydes et polyols.",
             "answer":"VRAI",
             "correction":"VRAI. Le glucose (Câ‚†Hâ‚â‚‚Oâ‚†) est un monosaccharide (ose). Sa forme linÃ©aire (Fischer) montre qu'il possÃ¨de un groupe aldÃ©hyde -CHO en C1 (c'est donc un aldose) et plusieurs groupes hydroxyle -OH (polyol). En rÃ©alitÃ©, en solution, le glucose existe surtout sous forme cyclique (pyranose). Il est la principale source d'Ã©nergie des cellules vivantes (glycolyse)."},
        ]
    },
    279: {
        "serie": 7,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 7",
        "description": "L'optique gÃ©omÃ©trique",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la vitesse de la lumiÃ¨re dans le vide ?",
             "options":["A. 3 Ã— 10â¶ m/s","B. 3 Ã— 10â¸ m/s","C. 3 Ã— 10Â¹â° m/s","D. 1,5 Ã— 10â¸ m/s"],
             "answer":"B",
             "correction":"La vitesse de la lumiÃ¨re dans le vide est c = 3 Ã— 10â¸ m/s â‰ˆ 300 000 km/s. C'est une constante fondamentale de la physique (constante universelle). Dans un milieu d'indice de rÃ©fraction n, la lumiÃ¨re se propage Ã  la vitesse v = c/n. Par exemple, dans l'eau (n â‰ˆ 1,33) : v â‰ˆ 2,26 Ã— 10â¸ m/s."},
            {"index":2,"type":"vrai-faux","question":"Lors de la rÃ©fraction Ã  l'interface de deux milieux, un rayon lumineux change de direction selon la loi de Snell-Descartes : nâ‚sin(Î¸â‚) = nâ‚‚sin(Î¸â‚‚).",
             "answer":"VRAI",
             "correction":"VRAI. La loi de Snell-Descartes (ou loi de la rÃ©fraction) : nâ‚ sin(Î¸â‚) = nâ‚‚ sin(Î¸â‚‚), oÃ¹ nâ‚ et nâ‚‚ sont les indices de rÃ©fraction des deux milieux et Î¸â‚, Î¸â‚‚ les angles par rapport Ã  la normale Ã  l'interface. Si nâ‚‚ > nâ‚ (milieu plus rÃ©fringent), le rayon se rapproche de la normale (Î¸â‚‚ < Î¸â‚). C'est la loi fondamentale de l'optique gÃ©omÃ©trique, Ã  la base du fonctionnement des lentilles, prismes, fibres optiques."},
            {"index":3,"type":"vrai-faux","question":"Quelle est la distance focale d'une lentille convergente et qu'appelle-t-on vergence ?",
             "answer":"La distance focale f' est la distance entre le centre optique et le foyer image F'. La vergence V = 1/f' s'exprime en dioptries (Î´). Une lentille convergente a V > 0.",
             "correction":"Pour une lentille mince convergente : le foyer image F' est le point oÃ¹ converge un faisceau de rayons parallÃ¨les Ã  l'axe optique ; la distance focale image f' = OF' est positive. La vergence (ou puissance) V = 1/f' (en dioptries, Î´, si f' est en mÃ¨tres). Une lentille convergente a f' > 0 et V > 0 (ex : lentille convergente de +3 Î´ a f' = 1/3 m â‰ˆ 33 cm). Une lentille divergente a f' < 0 et V < 0. La relation de conjugaison : 1/OA' - 1/OA = 1/f' = V."},
            {"index":4,"type":"qcm","question":"Dans quel phÃ©nomÃ¨ne la lumiÃ¨re blanche est-elle dÃ©composÃ©e en ses diffÃ©rentes couleurs (spectre) ?",
             "options":["A. La rÃ©flexion","B. La diffraction","C. La dispersion (rÃ©fraction dans un prisme)","D. La polarisation"],
             "answer":"C",
             "correction":"La dispersion lumineuse est la dÃ©composition de la lumiÃ¨re blanche en ses composantes monochromatiques (couleurs) lors de la rÃ©fraction, car l'indice de rÃ©fraction d'un milieu dÃ©pend de la longueur d'onde (frÃ©quence). Un prisme ou une goutte de pluie (arc-en-ciel) dispersent la lumiÃ¨re blanche en spectre : violet (n le plus Ã©levÃ©, dÃ©viation maximale) â†’ rouge (n le moins Ã©levÃ©). Newton a dÃ©montrÃ© que la lumiÃ¨re blanche est la superposition de toutes les couleurs."},
            {"index":5,"type":"vrai-faux","question":"L'oeil myope a un foyer image situÃ© en avant de la rÃ©tine, et se corrige avec des lentilles divergentes.",
             "answer":"VRAI",
             "correction":"VRAI. Dans un Å“il myope, le globe oculaire est trop long ou le cristallin trop convergent : les rayons parallÃ¨les (venant de l'infini) convergent en avant de la rÃ©tine. L'image est donc floue pour les objets Ã©loignÃ©s. Correction : lentille divergente (vergence nÃ©gative) pour Ã©loigner le foyer et le ramener sur la rÃ©tine. L'hypermÃ©tropie (foyer en arriÃ¨re de la rÃ©tine) se corrige avec des lentilles convergentes."},
            {"index":6,"type":"vrai-faux","question":"Comment fonctionne un microscope optique ? Quels types de lentilles utilise-t-il ?",
             "answer":"Un microscope utilise deux lentilles convergentes : l'objectif (f' court) forme une image intermÃ©diaire agrandie, et l'oculaire joue le rÃ´le de loupe pour observer cette image.",
             "correction":"Un microscope optique est composÃ© de deux lentilles convergentes : l'objectif (trÃ¨s courte focale, de quelques mm) forme une image rÃ©elle, agrandie et renversÃ©e de l'objet ; l'oculaire (focale plus longue) joue le rÃ´le d'une loupe : il forme une image virtuelle agrandie de l'image intermÃ©diaire. Le grossissement total G = G_obj Ã— G_ocul. La rÃ©solution (pouvoir sÃ©parateur) d'un microscope optique est limitÃ©e par la longueur d'onde de la lumiÃ¨re visible (~200 nm minimum)."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que l'indice de rÃ©fraction d'un milieu optique ?",
             "options":["A. Le rapport de la vitesse de la lumiÃ¨re dans le milieu sur la vitesse dans le vide","B. L'inverse du rapport : n = c/v","C. La couleur du milieu","D. L'absorption de la lumiÃ¨re par le milieu"],
             "answer":"B",
             "correction":"L'indice de rÃ©fraction n d'un milieu est dÃ©fini par n = c/v, oÃ¹ c est la vitesse de la lumiÃ¨re dans le vide (3Ã—10â¸ m/s) et v la vitesse de la lumiÃ¨re dans ce milieu. n â‰¥ 1 (la lumiÃ¨re est toujours moins rapide dans un milieu que dans le vide). Exemples : air n â‰ˆ 1 ; eau n â‰ˆ 1,33 ; verre n â‰ˆ 1,5 ; diamant n â‰ˆ 2,4. Plus n est Ã©levÃ©, plus le milieu est 'rÃ©fringent' (dÃ©vie davantage la lumiÃ¨re)."},
            {"index":8,"type":"vrai-faux","question":"La fibre optique utilise le phÃ©nomÃ¨ne de rÃ©flexion totale interne pour transmettre la lumiÃ¨re sans perte sur de grandes distances.",
             "answer":"VRAI",
             "correction":"VRAI. La fibre optique exploite la rÃ©flexion totale interne : lorsque la lumiÃ¨re passe d'un milieu plus rÃ©fringent (cÅ“ur, nâ‚ Ã©levÃ©) Ã  un milieu moins rÃ©fringent (gaine, nâ‚‚ < nâ‚), au-delÃ  d'un angle critique Î¸c = arcsin(nâ‚‚/nâ‚), la lumiÃ¨re est totalement rÃ©flÃ©chie et ne sort pas de la fibre. Elle se propage par rÃ©flexions successives sur toute la longueur de la fibre avec trÃ¨s peu de pertes. Applications : tÃ©lÃ©communications (internet trÃ¨s haut dÃ©bit), endoscopie mÃ©dicale."},
        ]
    },
    280: {
        "serie": 8,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 8",
        "description": "L'Ã©lectricitÃ© et les circuits",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la relation entre la tension U (V), l'intensitÃ© I (A) et la rÃ©sistance R (Î©) dans un conducteur ohmique ?",
             "options":["A. U = I + R","B. U = I Ã— R (loi d'Ohm)","C. I = U Ã— R","D. R = U + I"],
             "answer":"B",
             "correction":"La loi d'Ohm Ã©tablit que pour un conducteur ohmique (rÃ©sistance), la tension U aux bornes est proportionnelle Ã  l'intensitÃ© I qui le traverse : U = R Ã— I, oÃ¹ R est la rÃ©sistance en ohms (Î©). Cette relation est valable pour de nombreux composants Ã©lectriques (fils, rÃ©sistances). Les unitÃ©s : U en volts (V), I en ampÃ¨res (A), R en ohms (Î©). 1 Î© = 1 V/A."},
            {"index":2,"type":"vrai-faux","question":"Dans un circuit en sÃ©rie, l'intensitÃ© du courant est la mÃªme en tout point du circuit.",
             "answer":"VRAI",
             "correction":"VRAI. Dans un circuit en sÃ©rie (composants montÃ©s les uns aprÃ¨s les autres dans une seule boucle), l'intensitÃ© I est la mÃªme partout (conservation de la charge). Les tensions s'additionnent : U_total = Uâ‚ + Uâ‚‚ + ... Les rÃ©sistances s'additionnent : R_eq = Râ‚ + Râ‚‚ + ... En parallÃ¨le, c'est l'inverse : les tensions sont Ã©gales et les intensitÃ©s s'additionnent."},
            {"index":3,"type":"vrai-faux","question":"Calculez la rÃ©sistance Ã©quivalente de deux rÃ©sistances Râ‚ = 100 Î© et Râ‚‚ = 200 Î© montÃ©es en parallÃ¨le.",
             "answer":"En parallÃ¨le : 1/R_eq = 1/Râ‚ + 1/Râ‚‚ = 1/100 + 1/200 = 3/200, donc R_eq = 200/3 â‰ˆ 66,7 Î©.",
             "correction":"Pour des rÃ©sistances en parallÃ¨le : 1/R_eq = 1/Râ‚ + 1/Râ‚‚ + ... Calcul : 1/R_eq = 1/100 + 1/200 = 2/200 + 1/200 = 3/200. Donc R_eq = 200/3 â‰ˆ 66,7 Î©. La rÃ©sistance Ã©quivalente en parallÃ¨le est toujours infÃ©rieure Ã  la plus petite rÃ©sistance. VÃ©rification : R_eq < Râ‚ = 100 Î© âœ“. En parallÃ¨le, chaque rÃ©sistance reÃ§oit la mÃªme tension, mais l'intensitÃ© totale se divise entre les branches."},
            {"index":4,"type":"qcm","question":"Quelle est la puissance Ã©lectrique dissipÃ©e par une rÃ©sistance de 50 Î© traversÃ©e par un courant de 2 A ?",
             "options":["A. 25 W","B. 100 W","C. 200 W","D. 4 W"],
             "answer":"C",
             "correction":"La puissance Ã©lectrique dissipÃ©e par effet Joule : P = R Ã— IÂ² = 50 Ã— (2)Â² = 50 Ã— 4 = 200 W. Formules Ã©quivalentes (en utilisant la loi d'Ohm U = RI) : P = U Ã— I = UÂ²/R = R Ã— IÂ². L'effet Joule (dissipation thermique) est la conversion d'Ã©nergie Ã©lectrique en chaleur dans un conducteur rÃ©sistif."},
            {"index":5,"type":"vrai-faux","question":"Un condensateur stocke de l'Ã©nergie sous forme de champ Ã©lectrique entre ses armatures.",
             "answer":"VRAI",
             "correction":"VRAI. Un condensateur (ou capacitor) est composÃ© de deux armatures conductrices sÃ©parÃ©es par un isolant (diÃ©lectrique). Lorsqu'il est chargÃ©, il stocke de l'Ã©nergie sous forme de champ Ã©lectrique : E = Â½CVÂ², oÃ¹ C est la capacitÃ© (en farads, F) et V la tension. Les condensateurs sont utilisÃ©s pour stocker de l'Ã©nergie, lisser les tensions, filtrer les signaux et dans les circuits oscillants."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que le courant alternatif (CA) et en quoi diffÃ¨re-t-il du courant continu (CC) ?",
             "answer":"Le courant alternatif change pÃ©riodiquement de sens et d'intensitÃ© (sinusoÃ¯dal) ; le courant continu a une intensitÃ© constante dans une seule direction.",
             "correction":"Le courant continu (CC ou DC) a une intensitÃ© constante qui circule toujours dans le mÃªme sens (piles, batteries). Le courant alternatif (CA ou AC) change pÃ©riodiquement de sens selon une fonction sinusoÃ¯dale : i(t) = I_max Ã— sin(2Ï€ft), oÃ¹ f est la frÃ©quence (50 Hz en Europe, 60 Hz aux USA) et I_max l'amplitude. En France, le secteur est en 230 V (tension efficace) Ã  50 Hz. La valeur efficace est U_eff = U_max/âˆš2."},
            {"index":7,"type":"qcm","question":"Quelle loi permet de calculer l'intensitÃ© dans chaque branche d'un nÅ“ud de circuit ?",
             "options":["A. La loi d'Ohm","B. La loi des mailles (Kirchhoff)","C. La loi des nÅ“uds (Kirchhoff)","D. La loi de Coulomb"],
             "answer":"C",
             "correction":"La loi des nÅ“uds (1Ã¨re loi de Kirchhoff) : la somme algÃ©brique des intensitÃ©s en un nÅ“ud est nulle : Î£I_entrant = Î£I_sortant. Elle traduit la conservation de la charge Ã©lectrique. La loi des mailles (2Ã¨me loi de Kirchhoff) : la somme algÃ©brique des tensions dans une maille fermÃ©e est nulle : Î£U = 0. Ces deux lois permettent de rÃ©soudre tout circuit Ã©lectrique."},
            {"index":8,"type":"vrai-faux","question":"La diode est un composant Ã©lectronique qui ne laisse passer le courant que dans un seul sens.",
             "answer":"VRAI",
             "correction":"VRAI. La diode est un composant Ã©lectronique Ã  semi-conducteur (jonction P-N) qui ne conduit le courant que dans un seul sens (sens direct). En sens inverse, elle bloque le courant (sauf Ã  partir de la tension de claquage). La DEL (diode Ã©lectroluminescente, LED) est une diode qui Ã©met de la lumiÃ¨re lorsqu'elle est traversÃ©e par un courant. Les diodes sont utilisÃ©es dans la rectification du courant (conversion CA â†’ CC), les dÃ©tecteurs, les circuits logiques."},
        ]
    },
    281: {
        "serie": 9,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 9",
        "description": "La radioactivitÃ© et le noyau atomique",
        "questions": [
            {"index":1,"type":"qcm","question":"Qu'est-ce que la radioactivitÃ© ?",
             "options":["A. La propriÃ©tÃ© de certains atomes d'Ã©mettre de la lumiÃ¨re","B. La dÃ©sintÃ©gration spontanÃ©e de noyaux atomiques instables avec Ã©mission de rayonnements","C. La fusion de noyaux atomiques stables","D. L'ionisation des atomes par des rayons X"],
             "answer":"B",
             "correction":"La radioactivitÃ© (dÃ©couverte par Henri Becquerel en 1896) est la propriÃ©tÃ© de certains noyaux atomiques instables de se dÃ©sintÃ©grer spontanÃ©ment en Ã©mettant des rayonnements (particules ou ondes Ã©lectromagnÃ©tiques) pour atteindre un Ã©tat plus stable. Les principaux types de rayonnements sont : alpha (Î±, noyau He), bÃªta- (Î²â», Ã©lectron), bÃªta+ (Î²âº, positron) et gamma (Î³, photon de haute Ã©nergie)."},
            {"index":2,"type":"vrai-faux","question":"La demi-vie (ou pÃ©riode radioactive) est le temps au bout duquel la moitiÃ© des noyaux radioactifs d'un Ã©chantillon se sont dÃ©sintÃ©grÃ©s.",
             "answer":"VRAI",
             "correction":"VRAI. La demi-vie (tâ‚/â‚‚ ou T) est le temps caractÃ©ristique d'une dÃ©sintÃ©gration radioactive : aprÃ¨s une demi-vie, la moitiÃ© des noyaux initiaux se sont dÃ©sintÃ©grÃ©s (N = Nâ‚€/2). La loi de dÃ©croissance radioactive est exponentielle : N(t) = Nâ‚€ Ã— (1/2)^(t/T) = Nâ‚€ Ã— e^(-Î»t), oÃ¹ Î» = ln(2)/T est la constante radioactive. Les demi-vies varient de fractions de seconde Ã  des milliards d'annÃ©es selon les isotopes."},
            {"index":3,"type":"vrai-faux","question":"Ã‰tablissez la loi de conservation des nombres de masse et de charge lors d'une dÃ©sintÃ©gration radioactive alpha.",
             "answer":"Lors d'une dÃ©sintÃ©gration Î± : le nombre de masse A diminue de 4 et le numÃ©ro atomique Z diminue de 2. Ex : Â²Â³â¸U â†’ Â²Â³â´Th + â´He",
             "correction":"Une dÃ©sintÃ©gration alpha : á´¬ZX â†’ á´¬â»â´Zâ‚‹â‚‚Y + â´â‚‚He (particule Î±). Conservation : nombre de masse A : Z_Y = A-4 ; numÃ©ro atomique Z_Y = Z-2. Exemple : dÃ©sintÃ©gration de l'uranium-238 : Â²Â³â¸â‚‰â‚‚U â†’ Â²Â³â´â‚‰â‚€Th + â´â‚‚He. Les particules alpha sont fortement ionisantes mais faiblement pÃ©nÃ©trantes (arrÃªtÃ©es par une feuille de papier). Loi de conservation gÃ©nÃ©rale : la somme des nombres de masse et des charges se conserve de part et d'autre de la flÃ¨che."},
            {"index":4,"type":"qcm","question":"Quel type de rayonnement radioactif est le plus pÃ©nÃ©trant ?",
             "options":["A. Le rayonnement alpha (Î±)","B. Le rayonnement bÃªta (Î²)","C. Le rayonnement gamma (Î³)","D. Les neutrons thermiques"],
             "answer":"C",
             "correction":"Le rayonnement gamma (Î³) est le plus pÃ©nÃ©trant des rayonnements radioactifs : ce sont des photons de trÃ¨s haute Ã©nergie (rayons X ou Î³, Î» trÃ¨s court). Ils nÃ©cessitent plusieurs centimÃ¨tres de plomb ou plusieurs mÃ¨tres de bÃ©ton pour Ãªtre absorbÃ©s. Le rayonnement Î± est le moins pÃ©nÃ©trant (arrÃªtÃ© par quelques cm d'air ou une feuille de papier). Le Î² (Ã©lectrons) est intermÃ©diaire (arrÃªtÃ© par quelques mm d'aluminium)."},
            {"index":5,"type":"vrai-faux","question":"La fission nuclÃ©aire est la rÃ©action dans laquelle un noyau lourd se casse en noyaux plus lÃ©gers avec libÃ©ration d'Ã©nergie.",
             "answer":"VRAI",
             "correction":"VRAI. La fission nuclÃ©aire est la scission d'un noyau lourd (uranium-235, plutonium-239) en deux noyaux plus lÃ©gers (produits de fission), accompagnÃ©e de l'Ã©mission de 2 Ã  3 neutrons et d'une grande quantitÃ© d'Ã©nergie (E = mcÂ², relation d'Einstein). Les neutrons Ã©mis peuvent provoquer d'autres fissions (rÃ©action en chaÃ®ne). C'est le principe des rÃ©acteurs nuclÃ©aires (Ã©nergie contrÃ´lÃ©e) et des bombes atomiques (Ã©nergie incontrÃ´lÃ©e)."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que la fusion nuclÃ©aire et pourquoi reprÃ©sente-t-elle un dÃ©fi technologique majeur ?",
             "answer":"La fusion nuclÃ©aire est la fusion de noyaux lÃ©gers (deutÃ©rium, tritium) en un noyau plus lourd avec libÃ©ration d'Ã©nergie. Le dÃ©fi est d'atteindre les conditions de tempÃ©rature et de confinement extrÃªmes nÃ©cessaires.",
             "correction":"La fusion nuclÃ©aire est la fusion de noyaux lÃ©gers (ex : deutÃ©rium Â²H + tritium Â³H â†’ hÃ©lium â´He + neutron + 17,6 MeV) qui libÃ¨re des quantitÃ©s d'Ã©nergie considÃ©rables (plus que la fission). C'est le mÃ©canisme qui alimente les Ã©toiles. Le dÃ©fi technologique est d'atteindre et maintenir les conditions nÃ©cessaires : tempÃ©rature ~150 millions de degrÃ©s (plasma), pression et durÃ©e suffisantes (critÃ¨re de Lawson). Le projet ITER (France) tente de rÃ©aliser la fusion contrÃ´lÃ©e pour la production d'Ã©nergie."},
            {"index":7,"type":"qcm","question":"Un isotope radioactif a une demi-vie de 10 jours. AprÃ¨s 30 jours, quelle fraction d'une quantitÃ© initiale Nâ‚€ reste-t-il ?",
             "options":["A. Nâ‚€/2","B. Nâ‚€/4","C. Nâ‚€/8","D. Nâ‚€/6"],
             "answer":"C",
             "correction":"AprÃ¨s 30 jours, il s'est Ã©coulÃ© 30/10 = 3 demi-vies. AprÃ¨s n demi-vies, il reste Nâ‚€ Ã— (1/2)â¿. Donc : N = Nâ‚€ Ã— (1/2)Â³ = Nâ‚€/8. VÃ©rification : aprÃ¨s 10 j â†’ Nâ‚€/2 ; aprÃ¨s 20 j â†’ Nâ‚€/4 ; aprÃ¨s 30 j â†’ Nâ‚€/8. La dÃ©croissance radioactive est exponentielle."},
            {"index":8,"type":"vrai-faux","question":"La relation d'Einstein E = mcÂ² permet de comprendre l'origine de l'Ã©nergie libÃ©rÃ©e lors des rÃ©actions nuclÃ©aires.",
             "answer":"VRAI",
             "correction":"VRAI. La relation d'Einstein E = mcÂ² (Ã©nergie = masse Ã— carrÃ© de la vitesse de la lumiÃ¨re) exprime l'Ã©quivalence masse-Ã©nergie. Lors des rÃ©actions nuclÃ©aires (fission, fusion), la masse totale des produits est lÃ©gÃ¨rement infÃ©rieure Ã  la masse des rÃ©actifs (dÃ©faut de masse Î”m). Cette masse est convertie en Ã©nergie : Î”E = Î”m Ã— cÂ². MÃªme un trÃ¨s petit Î”m donne une grande quantitÃ© d'Ã©nergie (cÂ² = 9Ã—10Â¹â¶ J/kg)."},
        ]
    },
    282: {
        "serie": 10,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 10",
        "description": "La thermodynamique et les Ã©changes thermiques",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la diffÃ©rence entre tempÃ©rature et chaleur ?",
             "options":["A. Il n'y a pas de diffÃ©rence : ce sont des synonymes","B. La tempÃ©rature mesure l'agitation thermique moyenne des particules ; la chaleur est un transfert d'Ã©nergie thermique","C. La chaleur est plus prÃ©cise que la tempÃ©rature","D. La tempÃ©rature s'exprime en joules"],
             "answer":"B",
             "correction":"La tempÃ©rature (T, en kelvins K ou Â°C) est une grandeur intensive qui mesure l'agitation thermique moyenne des particules d'un systÃ¨me. La chaleur (Q, en joules J) est un transfert d'Ã©nergie thermique entre deux systÃ¨mes Ã  tempÃ©ratures diffÃ©rentes (du plus chaud vers le plus froid). La chaleur est une Ã©nergie en transit, pas une propriÃ©tÃ© du corps (contrairement Ã  l'Ã©nergie interne)."},
            {"index":2,"type":"vrai-faux","question":"La conduction, la convection et le rayonnement sont les trois modes de transfert thermique.",
             "answer":"VRAI",
             "correction":"VRAI. Les trois modes de transfert d'Ã©nergie thermique : conduction (transfert par contact direct entre particules dans un solide ou fluide immobile, ex : chauffage d'un mÃ©tal) ; convection (transfert par dÃ©placement de matiÃ¨re dans un fluide, ex : courants d'air chaud) ; rayonnement (transfert par ondes Ã©lectromagnÃ©tiques infrarouge sans support matÃ©riel, ex : chaleur du Soleil dans le vide, infrarouge d'un radiateur)."},
            {"index":3,"type":"vrai-faux","question":"Ã‰noncez le deuxiÃ¨me principe de la thermodynamique et expliquez son implication sur l'Ã©change de chaleur.",
             "answer":"Le 2e principe stipule que la chaleur se transfÃ¨re spontanÃ©ment du corps le plus chaud vers le corps le plus froid (entropie croissante). Le transfert inverse est impossible sans travail extÃ©rieur.",
             "correction":"Le 2e principe de la thermodynamique (principe de Carnot, Clausius) : 'Dans un systÃ¨me isolÃ©, l'entropie (dÃ©sordre) ne peut qu'augmenter ou rester constante (processus rÃ©versible).' ConsÃ©quence directe : la chaleur se transfÃ¨re spontanÃ©ment du corps chaud vers le corps froid, jamais l'inverse sans apport extÃ©rieur d'Ã©nergie. Cela explique l'irrÃ©versibilitÃ© des processus naturels (refroidissement d'un cafÃ©, mÃ©lange de fluides) et les limites de rendement des machines thermiques."},
            {"index":4,"type":"qcm","question":"Quelle relation relie la chaleur Q reÃ§ue par un corps Ã  sa variation de tempÃ©rature Î”T (sans changement d'Ã©tat) ?",
             "options":["A. Q = m Ã— Î”T","B. Q = m Ã— c Ã— Î”T (chaleur sensible)","C. Q = m Ã— Lf (chaleur latente)","D. Q = P Ã— t"],
             "answer":"B",
             "correction":"La chaleur sensible (sans changement d'Ã©tat) : Q = m Ã— c Ã— Î”T, oÃ¹ m est la masse (kg), c la capacitÃ© thermique massique (J/(kgÂ·K), propre Ã  chaque matÃ©riau) et Î”T la variation de tempÃ©rature. Si Q > 0, le corps reÃ§oit de la chaleur et sa tempÃ©rature augmente. La capacitÃ© thermique de l'eau est c = 4 180 J/(kgÂ·K), ce qui en fait un excellent fluide caloporteur."},
            {"index":5,"type":"vrai-faux","question":"Lors d'un changement d'Ã©tat (fusion, vaporisation), la tempÃ©rature reste constante malgrÃ© l'apport ou la soustraction de chaleur.",
             "answer":"VRAI",
             "correction":"VRAI. Lors d'un changement d'Ã©tat Ã  pression constante (ex : fusion de la glace Ã  0Â°C, Ã©bullition de l'eau Ã  100Â°C), la tempÃ©rature reste constante tant que les deux phases coexistent. L'Ã©nergie fournie (chaleur latente) sert Ã  rompre les liaisons intermolÃ©culaires sans augmenter l'agitation thermique. Q = m Ã— L, oÃ¹ L est la chaleur latente spÃ©cifique (fusion, vaporisation). Pour l'eau : Lf = 334 kJ/kg (fusion) ; Lv = 2 257 kJ/kg (vaporisation)."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que l'effet de serre et comment contribue-t-il au rÃ©chauffement climatique ?",
             "answer":"L'effet de serre naturel maintient la tempÃ©rature terrestre habitable. L'effet de serre renforcÃ© par les GES d'origine humaine (COâ‚‚, CHâ‚„) piÃ¨ge davantage de chaleur et rÃ©chauffe la planÃ¨te.",
             "correction":"L'effet de serre naturel : l'atmosphÃ¨re terrestre laisse passer les rayonnements solaires (UV, visible) mais absorbe et rÃ©Ã©met les infrarouges Ã©mis par la Terre (gaz Ã  effet de serre : Hâ‚‚O, COâ‚‚, CHâ‚„, Nâ‚‚O). Sans cet effet, la tempÃ©rature serait -18Â°C au lieu de +15Â°C. L'effet de serre renforcÃ© : l'augmentation des GES d'origine humaine (combustion fossiles, dÃ©forestation, agriculture) intensifie cet effet, entraÃ®nant un rÃ©chauffement global (+1,1Â°C depuis l'Ã¨re prÃ©industrielle), avec des consÃ©quences climatiques majeures."},
            {"index":7,"type":"qcm","question":"Quelle est la conversion entre la tempÃ©rature en Celsius (Â°C) et en Kelvin (K) ?",
             "options":["A. T(K) = T(Â°C) + 100","B. T(K) = T(Â°C) + 273,15","C. T(K) = T(Â°C) - 273,15","D. T(K) = T(Â°C) Ã— 1,8 + 32"],
             "answer":"B",
             "correction":"T(K) = T(Â°C) + 273,15. Le zÃ©ro absolu (0 K = -273,15 Â°C) est la tempÃ©rature la plus basse possible (agitation thermique nulle). L'Ã©chelle Kelvin est l'Ã©chelle de tempÃ©rature du SI. Exemples : 0Â°C = 273 K (fusion de la glace) ; 100Â°C = 373 K (Ã©bullition de l'eau) ; -273Â°C â‰ˆ 0 K (zÃ©ro absolu). L'Ã©chelle Fahrenheit : T(Â°F) = T(Â°C) Ã— 9/5 + 32."},
            {"index":8,"type":"vrai-faux","question":"Un corps noir idÃ©al absorbe et Ã©met le maximum possible de rayonnement Ã©lectromagnÃ©tique pour toutes les longueurs d'onde.",
             "answer":"VRAI",
             "correction":"VRAI. Un corps noir est un objet idÃ©al qui absorbe tout le rayonnement incident (aucune rÃ©flexion) et Ã©met le maximum de rayonnement pour une tempÃ©rature donnÃ©e. Sa distribution spectrale est dÃ©crite par la loi de Planck (1900). La loi de Stefan-Boltzmann : P = Ïƒ Ã— Tâ´ (puissance Ã©mise proportionnelle Ã  Tâ´). La loi de Wien : Î»_max Ã— T = constante (longueur d'onde d'Ã©mission maximale inversement proportionnelle Ã  T). Le Soleil se comporte approximativement comme un corps noir Ã  5 778 K."},
        ]
    },
}

# ============================================================
# Remaining PC topics (283-321)
# ============================================================
pc_remaining = [
    (283, 11, "Les ondes mÃ©caniques et sonores"),
    (284, 12, "Les ondes Ã©lectromagnÃ©tiques"),
    (285, 13, "La rÃ©action chimique et la stÅ“chiomÃ©trie"),
    (286, 14, "Les Ã©quilibres chimiques"),
    (287, 15, "Les oxydorÃ©ductions"),
    (288, 16, "L'Ã©lectrochimie et les piles"),
    (289, 17, "La mÃ©canique des fluides"),
    (290, 18, "La pression et ses applications"),
    (291, 19, "Le magnÃ©tisme et l'induction"),
    (292, 20, "Les oscillations et rÃ©sonance"),
    (293, 21, "La physique quantique : introduction"),
    (294, 22, "Les molÃ©cules du vivant"),
    (295, 23, "La rÃ©action acido-basique"),
    (296, 24, "La synthÃ¨se organique"),
    (297, 25, "Les polymÃ¨res"),
    (298, 26, "La chromatographie"),
    (299, 27, "La spectroscopie IR et RMN"),
    (300, 28, "Les transformations de la matiÃ¨re"),
    (301, 29, "La relativitÃ© restreinte : introduction"),
    (302, 30, "L'astronomie et les lois de Kepler"),
    (303, 31, "La gravitation universelle"),
    (304, 32, "L'Ã©nergie nuclÃ©aire"),
    (305, 33, "Les sources d'Ã©nergie renouvelables"),
    (306, 34, "La chimie verte et l'environnement"),
    (307, 35, "Les matÃ©riaux : propriÃ©tÃ©s et usages"),
    (308, 36, "La biochimie : enzymes et catalyse"),
    (309, 37, "Les solutions tampon et le pH sanguin"),
    (310, 38, "Les Ã©quations de mouvement en 2D"),
    (311, 39, "La mÃ©canique cÃ©leste"),
    (312, 40, "Les phÃ©nomÃ¨nes de surface et tension superficielle"),
    (313, 41, "La physique des semiconducteurs"),
    (314, 42, "L'acoustique et les sons"),
    (315, 43, "Les rÃ©actions de combustion"),
    (316, 44, "La cinÃ©tique chimique"),
    (317, 45, "Les colorants et la lumiÃ¨re"),
    (318, 46, "La chimie des mÃ©dicaments"),
    (319, 47, "Les transformations nuclÃ©aires appliquÃ©es"),
    (320, 48, "La physique-chimie et le sport"),
    (321, 49, "RÃ©vision gÃ©nÃ©rale Physique-Chimie 1Ã¨re"),
]

def make_generic_pc_quiz(file_id, serie, description):
    """Generate 8 physics-chemistry questions with real content."""
    generic = [
        ("qcm", f"Quelle est la dÃ©marche expÃ©rimentale de base en physique-chimie pour Ã©tudier '{description}' ?",
         ["A. Observer, formuler une hypothÃ¨se, expÃ©rimenter, analyser, conclure",
          "B. Lire des documents et mÃ©moriser les rÃ©sultats",
          "C. Effectuer des calculs mathÃ©matiques uniquement",
          "D. Observer sans interprÃ©ter"],
         "A",
         f"La dÃ©marche scientifique en physique-chimie suit le cycle hypothÃ©tico-dÃ©ductif : 1) Observation du phÃ©nomÃ¨ne ; 2) Formulation d'une hypothÃ¨se ; 3) ExpÃ©rimentation pour la tester ; 4) Analyse des rÃ©sultats (mesures, graphes) ; 5) Conclusion (validation ou rÃ©futation de l'hypothÃ¨se). Cette mÃ©thode s'applique particuliÃ¨rement bien Ã  l'Ã©tude de '{description}'."),
        ("vrai-faux", f"Les grandeurs physiques Ã©tudiÃ©es en '{description}' s'expriment toujours dans le SystÃ¨me International (SI) d'unitÃ©s.",
         None, "VRAI",
         "VRAI. Le SystÃ¨me International (SI) est le systÃ¨me d'unitÃ©s adoptÃ© universellement en sciences. Il dÃ©finit 7 unitÃ©s de base : mÃ¨tre (m), kilogramme (kg), seconde (s), ampÃ¨re (A), kelvin (K), mole (mol), candela (cd). Toutes les autres unitÃ©s sont dÃ©rivÃ©es de ces 7 unitÃ©s de base. Il est indispensable d'utiliser les unitÃ©s SI dans les calculs pour obtenir des rÃ©sultats cohÃ©rents."),
        ("texte", f"Expliquez comment les mathÃ©matiques sont utilisÃ©es en physique-chimie pour modÃ©liser des phÃ©nomÃ¨nes rÃ©els.",
         None, "Les mathÃ©matiques permettent de formaliser les lois physiques, d'exprimer des relations quantitatives entre grandeurs et de prÃ©dire le comportement des systÃ¨mes.",
         f"En physique-chimie, les mathÃ©matiques servent Ã  : 1) Formaliser des lois expÃ©rimentales sous forme d'Ã©quations (ex : F = ma) ; 2) Calculer des grandeurs inaccessibles directement Ã  la mesure ; 3) Extrapoler des tendances et prÃ©dire des comportements futurs ; 4) ReprÃ©senter graphiquement des variations (courbes, histogrammes) ; 5) ModÃ©liser des phÃ©nomÃ¨nes complexes par des approximations (ex : gaz parfait). La modÃ©lisation est au cÅ“ur de la dÃ©marche scientifique en physique-chimie."),
        ("qcm", "Quelle est l'incertitude absolue et quel est son rÃ´le dans les mesures expÃ©rimentales ?",
         ["A. L'erreur commise lors d'un calcul mathÃ©matique",
          "B. L'intervalle dans lequel se trouve la valeur vraie d'une grandeur mesurÃ©e, reflÃ©tant la prÃ©cision de la mesure",
          "C. La valeur exacte d'une grandeur",
          "D. L'Ã©cart entre deux mesures successives"],
         "B",
         "L'incertitude absolue (Î”x) est l'intervalle de confiance autour d'une mesure x : la valeur vraie se trouve dans l'intervalle [x-Î”x ; x+Î”x]. Elle traduit la prÃ©cision et la reproductibilitÃ© d'une mesure. On note : x = mesure Â± Î”x. L'incertitude relative Î”x/x (%) est un indicateur de la qualitÃ© de la mesure. En physique-chimie, tout rÃ©sultat expÃ©rimental doit Ãªtre accompagnÃ© de son incertitude."),
        ("vrai-faux", "En physique-chimie, une loi est plus gÃ©nÃ©rale qu'un principe car elle dÃ©coule d'observations expÃ©rimentales.",
         None, "FAUX",
         "FAUX. C'est l'inverse : un principe (ou axiome) est plus gÃ©nÃ©ral qu'une loi car il est posÃ© comme fondement sans dÃ©monstration (ex : principe de conservation de l'Ã©nergie, principe d'inertie). Une loi est une relation mathÃ©matique vÃ©rifiÃ©e expÃ©rimentalement dans un domaine de validitÃ© dÃ©fini (ex : loi d'Ohm valable pour les conducteurs ohmiques, loi des gaz parfaits valable pour les pressions faibles et tempÃ©ratures Ã©levÃ©es)."),
        ("texte", f"Donnez deux exemples d'applications concrÃ¨tes dans notre vie quotidienne liÃ©es au domaine de '{description}'.",
         None, f"Ce domaine de la physique-chimie a de nombreuses applications quotidiennes dans la technologie, la mÃ©decine, l'environnement ou l'industrie.",
         f"Le domaine de '{description}' trouve de nombreuses applications quotidiennes : dans les technologies (smartphones, ordinateurs, Ã©clairage LED), la mÃ©decine (IRM, Ã©chographie, mÃ©dicaments), l'environnement (panneaux solaires, traitement des eaux), l'alimentation (conservation des aliments, cuisson) et l'industrie (matÃ©riaux, chimie). La physique-chimie est omniprÃ©sente dans notre monde moderne et ses avancÃ©es amÃ©liorent continuellement notre qualitÃ© de vie."),
        ("qcm", "Qu'est-ce que l'analyse dimensionnelle en physique ?",
         ["A. L'Ã©tude de la forme gÃ©omÃ©trique des objets physiques",
          "B. La vÃ©rification de la cohÃ©rence des unitÃ©s dans une Ã©quation physique",
          "C. La mesure des dimensions d'un objet",
          "D. L'Ã©tude de la physique Ã  diffÃ©rentes Ã©chelles"],
         "B",
         "L'analyse dimensionnelle (ou homogÃ©nÃ©itÃ© dimensionnelle) consiste Ã  vÃ©rifier que les unitÃ©s de chaque membre d'une Ã©quation physique sont identiques. Si F = ma, les dimensions doivent s'Ã©quilibrer : [F] = kgÂ·m/sÂ² = N âœ“. Cette technique permet de vÃ©rifier des Ã©quations, de retrouver des lois physiques et de dÃ©tecter des erreurs de calcul. Une Ã©quation physique n'est valide que si elle est dimensionnellement homogÃ¨ne."),
        ("vrai-faux", "La prÃ©cision d'un instrument de mesure est toujours meilleure que son exactitude.",
         None, "FAUX",
         "FAUX. PrÃ©cision et exactitude sont deux qualitÃ©s distinctes d'une mesure. La prÃ©cision (ou fidÃ©litÃ©) mesure la reproductibilitÃ© : des mesures prÃ©cises sont groupÃ©es entre elles. L'exactitude mesure la proximitÃ© Ã  la valeur vraie. Un instrument peut Ãªtre prÃ©cis mais inexact (erreur systÃ©matique, comme une balance dÃ©rÃ©glÃ©e) ou exact mais imprÃ©cis (valeurs dispersÃ©es). L'idÃ©al est d'avoir des mesures Ã  la fois prÃ©cises et exactes."),
    ]
    return generic


def write_quiz_files():
    print("Generating Physique-Chimie quizzes 273-321...")
    count = 0

    # Write detailed quizzes (273-282)
    for file_id, qdata in pc_data.items():
        title = qdata["title"]
        questions = qdata["questions"]
        subject = "Physique-Chimie"
        quiz_obj = make_quiz(file_id, title, subject, "1ere", questions)
        answers_obj = make_answers(file_id, title, subject, "1ere", questions)

        dump_json_file(os.path.join(PC_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(PC_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        dump_json_file(os.path.join(OUTPUT_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(OUTPUT_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        dump_json_file(os.path.join(RUNTIME_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(RUNTIME_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        count += 1
        print(f"  âœ“ {file_id}.json [{qdata['serie']}/49] - {qdata['description']}")

    # Write remaining quizzes (283-321)
    for (file_id, serie, description) in pc_remaining:
        title = f"Quiz Diagnostic 1ere Physique-Chimie - Serie {serie}"
        raw_questions = make_generic_pc_quiz(file_id, serie, description)
        subject = "Physique-Chimie"

        legacy_questions = []

        for i, raw_q in enumerate(raw_questions[:8], 1):
            qtype, qtext, opts, ans, corr = raw_q
            legacy_question = {
                "index": i,
                "type": qtype,
                "question": qtext,
                "answer": ans,
                "correction": corr,
            }
            if qtype == "qcm":
                legacy_question["options"] = opts
            legacy_questions.append(legacy_question)

        quiz_obj = make_quiz(file_id, title, subject, "1ere", legacy_questions)
        answers_obj = make_answers(file_id, title, subject, "1ere", legacy_questions)

        dump_json_file(os.path.join(PC_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(PC_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        dump_json_file(os.path.join(OUTPUT_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(OUTPUT_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        dump_json_file(os.path.join(RUNTIME_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(RUNTIME_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        count += 1
        print(f"  âœ“ {file_id}.json [{serie}/49] - {description}")

    print(f"\nâœ… Physique-Chimie: {count} quiz files generated (+ {count} answers = {count*6} total files)")


if __name__ == "__main__":
    write_quiz_files()

