import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", ".."))
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
                "type": "open",
                "question": str(q.get("question", "")),
            })

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
                "type": "open",
                "correct_answer": q.get("answer", ""),
                "answer": q.get("answer", ""),
                "correction": explanation,
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

# ============================================================
# PHYSIQUE-CHIMIE 1ère — fichiers 273 à 321 (49 quizzes)
# ============================================================

pc_data = {
    273: {
        "serie": 1,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 1",
        "description": "La constitution de la matière",
        "questions": [
            {"index":1,"type":"qcm","question":"Un atome de carbone 12 (¹²C) possède :",
             "options":["A. 6 protons, 6 neutrons et 6 électrons","B. 12 protons, 12 neutrons et 12 électrons","C. 6 protons, 12 neutrons et 6 électrons","D. 12 protons et 6 électrons"],
             "answer":"A",
             "correction":"Le carbone-12 (¹²C) a un numéro atomique Z=6 (6 protons, 6 électrons) et un nombre de masse A=12 (6 protons + 6 neutrons). La notation A/Z X indique : A = nombre de masse (protons+neutrons), Z = numéro atomique (protons). Pour un atome neutre, le nombre d'électrons = nombre de protons."},
            {"index":2,"type":"vrai-faux","question":"Les isotopes d'un même élément chimique ont le même nombre de protons mais un nombre de neutrons différent.",
             "answer":"VRAI",
             "correction":"VRAI. Les isotopes sont des atomes du même élément (même numéro atomique Z, donc même nombre de protons) mais avec des nombres de neutrons différents (donc nombres de masse A différents). Exemple : ¹H (protium), ²H (deutérium), ³H (tritium) sont trois isotopes de l'hydrogène avec respectivement 0, 1 et 2 neutrons."},
            {"index":3,"type":"texte","question":"Définissez la notion de mole en chimie et donnez la valeur de la constante d'Avogadro.",
             "answer":"La mole est la quantité de matière contenant 6,022 × 10²³ entités (atomes, molécules, ions). La constante d'Avogadro est NA = 6,022 × 10²³ mol⁻¹.",
             "correction":"La mole (mol) est l'unité de quantité de matière du SI. Elle contient exactement 6,022 × 10²³ entités élémentaires (atomes, molécules, ions, électrons...). Cette constante fondamentale est la constante d'Avogadro : NA = 6,022 × 10²³ mol⁻¹. La masse molaire M (en g/mol) est la masse d'une mole d'entités. Relation : n = m/M où n est la quantité de matière (mol), m la masse (g) et M la masse molaire (g/mol)."},
            {"index":4,"type":"qcm","question":"Quelle est la configuration électronique de l'atome d'oxygène (Z=8) à l'état fondamental ?",
             "options":["A. 1s² 2s² 2p⁶","B. 1s² 2s² 2p⁴","C. 1s² 2s⁴ 2p²","D. 1s² 2s² 2p² 3s²"],
             "answer":"B",
             "correction":"L'oxygène (Z=8) a 8 électrons à répartir : couche 1 (n=1) : 2 électrons en 1s² ; couche 2 (n=2) : 6 électrons restants → 2 en 2s² et 4 en 2p⁴. Configuration : 1s² 2s² 2p⁴. La couche de valence (externe) contient 6 électrons (2s² 2p⁴). L'oxygène appartient à la famille VIA du tableau périodique."},
            {"index":5,"type":"vrai-faux","question":"La liaison covalente résulte du partage d'une ou plusieurs paires d'électrons entre deux atomes.",
             "answer":"VRAI",
             "correction":"VRAI. La liaison covalente est formée par le partage d'une ou plusieurs paires d'électrons entre deux atomes non métalliques. Exemples : H₂ (une liaison simple, 1 paire partagée), O₂ (une liaison double, 2 paires), N₂ (une liaison triple, 3 paires). La règle de l'octet (ou du duet pour H) prédit la formation de liaisons covalentes pour compléter la couche externe."},
            {"index":6,"type":"texte","question":"Qu'est-ce que l'électronégativité et comment influence-t-elle la polarité des liaisons ?",
             "answer":"L'électronégativité est la tendance d'un atome à attirer les électrons d'une liaison vers lui. Plus la différence d'électronégativité est grande, plus la liaison est polarisée.",
             "correction":"L'électronégativité (Pauling) mesure la tendance d'un atome à attirer le doublet d'électrons d'une liaison covalente. Elle croît de gauche à droite dans une période et de bas en haut dans un groupe. Si la différence d'électronégativité Δχ entre deux atomes liés est grande (>0,4), la liaison est covalente polaire (ex : H-Cl, O-H) avec des charges partielles δ+ et δ-. Si Δχ est très grande (>1,7), la liaison est ionique."},
            {"index":7,"type":"qcm","question":"Parmi ces espèces chimiques, laquelle est un ion polyatomique ?",
             "options":["A. Na⁺","B. Cl⁻","C. SO₄²⁻","D. Mg²⁺"],
             "answer":"C",
             "correction":"L'ion sulfate SO₄²⁻ est un ion polyatomique (ou moléculaire) : il est constitué de plusieurs atomes (1 soufre et 4 oxygènes) liés de façon covalente et portant une charge globale de 2-. En revanche, Na⁺, Cl⁻ et Mg²⁺ sont des ions monoatomiques (formés d'un seul atome ayant perdu ou gagné des électrons)."},
            {"index":8,"type":"vrai-faux","question":"La masse molaire de l'eau (H₂O) est d'environ 18 g/mol.",
             "answer":"VRAI",
             "correction":"VRAI. La masse molaire de H₂O se calcule : M(H₂O) = 2 × M(H) + M(O) = 2 × 1 + 16 = 18 g/mol. Cette valeur fondamentale intervient dans de nombreux calculs de chimie : 1 mole d'eau pèse 18 g, et 1 litre d'eau (densité ≈ 1) contient donc environ 1000/18 ≈ 55,6 moles d'eau."},
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
             "correction":"La concentration molaire c = n/V où n est la quantité de matière (mol) et V le volume de solution (en litres). c = 0,1 mol / 0,500 L = 0,2 mol/L. Attention : il faut convertir le volume en litres (500 mL = 0,500 L). La concentration en g/L serait : C = m/V = (0,1 × 58,5)/0,500 = 11,7 g/L."},
            {"index":2,"type":"vrai-faux","question":"Le pH d'une solution acide est inférieur à 7 à 25°C.",
             "answer":"VRAI",
             "correction":"VRAI. Le pH (potentiel hydrogène) est défini par pH = -log[H₃O⁺]. À 25°C, l'eau pure est neutre avec pH = 7 ([H₃O⁺] = [OH⁻] = 10⁻⁷ mol/L). Une solution acide a [H₃O⁺] > 10⁻⁷ mol/L, donc pH < 7. Une solution basique a [H₃O⁺] < 10⁻⁷ mol/L, donc pH > 7."},
            {"index":3,"type":"texte","question":"Qu'est-ce qu'une dilution et comment calcule-t-on la concentration de la solution diluée ?",
             "answer":"La dilution consiste à ajouter du solvant à une solution pour diminuer sa concentration. La loi de conservation : C₁ × V₁ = C₂ × V₂.",
             "correction":"Une dilution consiste à ajouter du solvant (eau) à une solution mère de concentration C₁ pour obtenir une solution fille de concentration C₂ < C₁. La quantité de soluté se conserve : n₁ = n₂, soit C₁ × V₁ = C₂ × V₂. Le facteur de dilution f = C₁/C₂ = V₂/V₁. Exemple : diluer 20 mL d'une solution à 2 mol/L à 200 mL donne C₂ = (2 × 20)/200 = 0,2 mol/L (dilution d'un facteur 10)."},
            {"index":4,"type":"qcm","question":"Un acide fort comme HCl (acide chlorhydrique) en solution aqueuse :",
             "options":["A. Se dissocie partiellement","B. Ne se dissocie pas","C. Se dissocie totalement en H⁺ et Cl⁻","D. Réagit avec l'eau pour former une base"],
             "answer":"C",
             "correction":"Un acide fort (HCl, HNO₃, H₂SO₄, HClO₄...) se dissocie totalement dans l'eau : HCl → H⁺ + Cl⁻ (ou HCl + H₂O → H₃O⁺ + Cl⁻). Pour un acide fort de concentration c, [H₃O⁺] = c et pH = -log(c). Un acide faible (CH₃COOH, HF...) ne se dissocie que partiellement, et le pH est calculé avec la constante d'acidité Ka."},
            {"index":5,"type":"vrai-faux","question":"La constante d'acidité Ka d'un acide est indépendante de la concentration de la solution.",
             "answer":"VRAI",
             "correction":"VRAI. La constante d'acidité Ka (ou pKa = -log Ka) est une constante thermodynamique qui dépend uniquement de la nature de l'acide et de la température, non de la concentration de la solution. Plus Ka est grand (pKa petit), plus l'acide est fort. Exemple : l'acide acétique CH₃COOH a pKa = 4,75 à 25°C."},
            {"index":6,"type":"texte","question":"Comment se réalise un dosage acido-basique par titrage colorimétrique ?",
             "answer":"Un titrage consiste à ajouter progressivement une solution titrante de concentration connue à la solution à titrer jusqu'à l'équivalence, repérée par le changement de couleur d'un indicateur.",
             "correction":"Un titrage acido-basique consiste à verser progressivement (burette) une solution titrante (base ou acide de concentration connue) dans une solution à titrer (contenue dans un bécher avec un indicateur coloré). L'équivalence est atteinte quand la réaction est totale (stœchiométrie exacte) : le changement de couleur de l'indicateur le signale. À l'équivalence : n(acide) = n(base) (pour des acides monoacides et des bases monobasiques). On peut alors calculer la concentration inconnue."},
            {"index":7,"type":"qcm","question":"Quelle est la relation entre pH et pOH dans une solution aqueuse à 25°C ?",
             "options":["A. pH = pOH","B. pH + pOH = 14","C. pH × pOH = 14","D. pH - pOH = 7"],
             "answer":"B",
             "correction":"À 25°C, le produit ionique de l'eau Ke = [H₃O⁺][OH⁻] = 10⁻¹⁴ mol²/L². En prenant le -log : pKe = pH + pOH = 14. Pour une solution neutre, pH = pOH = 7. Pour une solution acide (pH < 7), pOH > 7. Pour une solution basique (pH > 7), pOH < 7."},
            {"index":8,"type":"vrai-faux","question":"Une solution tampon résiste aux variations de pH lors de l'ajout de petites quantités d'acide ou de base.",
             "answer":"VRAI",
             "correction":"VRAI. Une solution tampon est un mélange d'un acide faible et de sa base conjuguée (ou d'une base faible et de son acide conjugué) dans des proportions similaires. Elle résiste aux variations de pH car l'acide conjugué neutralise les bases ajoutées et la base conjuguée neutralise les acides ajoutés. Exemple : solution tampon acétate (CH₃COOH/CH₃COO⁻). Le sang humain est maintenu à pH ≈ 7,4 grâce à un système tampon."},
        ]
    },
    275: {
        "serie": 3,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 3",
        "description": "La cinématique",
        "questions": [
            {"index":1,"type":"qcm","question":"Un objet se déplace de 120 m en 8 s. Quelle est sa vitesse moyenne ?",
             "options":["A. 8 m/s","B. 15 m/s","C. 960 m/s","D. 0,067 m/s"],
             "answer":"B",
             "correction":"La vitesse moyenne est définie par v = d/t, où d est la distance parcourue et t le temps. v = 120 m / 8 s = 15 m/s. Rappel des unités : la vitesse s'exprime en m/s (SI), km/h (1 m/s = 3,6 km/h). 15 m/s = 54 km/h."},
            {"index":2,"type":"vrai-faux","question":"Dans un mouvement uniforme, la vitesse est constante et l'accélération est nulle.",
             "answer":"VRAI",
             "correction":"VRAI. Un mouvement rectiligne uniforme (MRU) est caractérisé par une vitesse constante (en module et en direction). L'accélération (a = dv/dt) est donc nulle. Le graphe vitesse-temps est une droite horizontale, et le graphe position-temps est une droite de pente v (x = x₀ + v·t). C'est le régime prévu par le principe d'inertie de Newton en l'absence de force résultante."},
            {"index":3,"type":"texte","question":"Établissez les équations horaires d'un mouvement uniformément accéléré (MRUA) en partant d'un état initial x₀, v₀.",
             "answer":"Pour un MRUA : a = cste ; v(t) = v₀ + a·t ; x(t) = x₀ + v₀·t + ½·a·t²",
             "correction":"Pour un mouvement rectiligne uniformément accéléré (MRUA) avec accélération constante a : Équation de la vitesse : v(t) = v₀ + a·t (droite affine en t) ; Équation de la position : x(t) = x₀ + v₀·t + ½·a·t² (parabole en t). Relations indépendantes du temps : v² = v₀² + 2a(x - x₀). Exemple : chute libre : a = g = 9,81 m/s² (vers le bas), v₀ = 0 si lâché sans vitesse initiale."},
            {"index":4,"type":"qcm","question":"Un vecteur vitesse est instantanée est tangent à la trajectoire. Dans un mouvement circulaire uniforme, l'accélération est :",
             "options":["A. Nulle","B. Tangentielle à la trajectoire","C. Centripète (dirigée vers le centre)","D. Centrifuge (dirigée vers l'extérieur)"],
             "answer":"C",
             "correction":"Dans un mouvement circulaire uniforme (MCU), la vitesse est constante en module mais varie en direction. L'accélération centripète (ou normale) est perpendiculaire à la vitesse, dirigée vers le centre du cercle : a = v²/R. Elle ne change pas la vitesse (module) mais change la direction du mouvement. Son module est a = ω²·R = v²/R, où ω est la vitesse angulaire."},
            {"index":5,"type":"vrai-faux","question":"La chute libre (sans frottements) est un exemple de mouvement uniformément accéléré avec a = g ≈ 9,81 m/s².",
             "answer":"VRAI",
             "correction":"VRAI. La chute libre est le mouvement d'un objet soumis uniquement à la pesanteur (gravité), sans frottements de l'air. L'accélération est constante : a = g ≈ 9,81 m/s² (vers le bas). Les équations horaires sont : v(t) = g·t (vitesse nulle au départ) ; z(t) = h - ½·g·t² (hauteur en chute). En réalité, les frottements de l'air existent et limitent la vitesse à la 'vitesse limite'."},
            {"index":6,"type":"texte","question":"Définissez la période, la fréquence et la pulsation d'un mouvement circulaire uniforme et donnez leurs relations.",
             "answer":"Période T (s) : durée d'un tour complet. Fréquence f (Hz) : nombre de tours par seconde. Pulsation ω (rad/s). Relations : f = 1/T ; ω = 2π/T = 2πf.",
             "correction":"Pour un MCU : la période T (en secondes) est la durée d'un tour complet (rotation de 2π radians) ; la fréquence f (en hertz, Hz) est le nombre de tours par seconde (f = 1/T) ; la pulsation (ou vitesse angulaire) ω (en rad/s) est l'angle balayé par seconde (ω = 2π/T = 2πf). La vitesse linéaire est liée à ces grandeurs par : v = R·ω = 2πR/T, où R est le rayon de la trajectoire circulaire."},
            {"index":7,"type":"qcm","question":"Un mobile parcourt un virage circulaire de rayon 50 m à la vitesse de 20 m/s. Quelle est l'intensité de son accélération centripète ?",
             "options":["A. 0,4 m/s²","B. 8 m/s²","C. 40 m/s²","D. 1000 m/s²"],
             "answer":"B",
             "correction":"L'accélération centripète est donnée par a = v²/R = (20)²/50 = 400/50 = 8 m/s². Cette accélération est dirigée vers le centre du virage. La force centripète correspondante (2ème loi de Newton : F = m·a) est F = m × 8 = 8m (N) si m est la masse du mobile en kg."},
            {"index":8,"type":"vrai-faux","question":"La vitesse d'un objet et son accélération sont toujours de même sens.",
             "answer":"FAUX",
             "correction":"FAUX. La vitesse et l'accélération peuvent avoir des sens opposés. Si a et v sont dans le même sens, le mouvement est accéléré (la vitesse augmente). Si a et v sont de sens opposés, le mouvement est décéléré ou ralenti (la vitesse diminue). Exemple : lors d'un freinage, l'accélération (freinage) est opposée à la vitesse. Dans un mouvement circulaire uniforme, a (centripète) est perpendiculaire à v."},
        ]
    },
    276: {
        "serie": 4,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 4",
        "description": "Les lois de Newton",
        "questions": [
            {"index":1,"type":"qcm","question":"Énoncez la première loi de Newton (principe d'inertie).",
             "options":["A. F = m × a","B. Tout corps reste en état de repos ou de mouvement rectiligne uniforme si la résultante des forces qui s'exercent sur lui est nulle","C. Les forces d'action et de réaction sont égales et opposées","D. L'accélération d'un objet est inversement proportionnelle à sa masse"],
             "answer":"B",
             "correction":"Le 1er principe de Newton (principe d'inertie) : 'Tout corps persévère dans son état de repos ou de mouvement rectiligne uniforme, sauf si des forces extérieures l'obligent à changer.' En formule : si ΣF = 0, alors a = 0. L'inertie est la propriété d'un corps de résister aux changements de son état de mouvement."},
            {"index":2,"type":"vrai-faux","question":"Selon la 2ème loi de Newton, si on double la force appliquée sur un objet (masse constante), son accélération double.",
             "answer":"VRAI",
             "correction":"VRAI. La 2ème loi de Newton : ΣF = m × a, soit a = ΣF/m. Si la masse m est constante et qu'on double la résultante des forces ΣF, l'accélération a est également doublée (relation de proportionnalité directe). Réciproquement, si la masse double pour la même force, l'accélération est divisée par 2."},
            {"index":3,"type":"texte","question":"Expliquez la différence entre masse et poids, et donnez leur relation.",
             "answer":"La masse est une propriété intrinsèque d'un corps (en kg) ; le poids est la force gravitationnelle exercée sur ce corps (en N). Relation : P = m × g.",
             "correction":"La masse m (en kg) est une propriété intrinsèque d'un objet, indépendante du lieu (invariante). Elle mesure l'inertie d'un corps. Le poids P (en newtons, N) est la force gravitationnelle exercée par un astre sur un objet : P = m × g, où g est l'accélération de la pesanteur (g ≈ 9,81 m/s² sur Terre, 1,62 m/s² sur la Lune). Le poids varie selon le lieu, la masse non. Une balance mesure la masse ; un dynamomètre mesure le poids."},
            {"index":4,"type":"qcm","question":"Une voiture de 1000 kg accélère avec a = 3 m/s². Quelle est la résultante des forces horizontales qui s'exercent sur elle ?",
             "options":["A. 300 N","B. 3000 N","C. 333 N","D. 9810 N"],
             "answer":"B",
             "correction":"Application de la 2ème loi de Newton : ΣF = m × a = 1000 kg × 3 m/s² = 3000 N. Cette force résultante est la somme algébrique de toutes les forces horizontales (force motrice du moteur moins les frottements et la résistance de l'air). L'unité de force est le Newton (N = kg·m/s²)."},
            {"index":5,"type":"vrai-faux","question":"La 3ème loi de Newton stipule que si A exerce une force sur B, alors B exerce sur A une force égale en module, opposée en sens, sur la même droite d'action.",
             "answer":"VRAI",
             "correction":"VRAI. Le 3ème principe de Newton (action-réaction) : 'Toute action appelle une réaction égale et opposée.' Si A exerce F(A→B) sur B, alors B exerce F(B→A) = -F(A→B) sur A. Ces deux forces sont égales en module, opposées en sens, colinéaires mais exercées sur des objets différents. Exemple : la Terre attire la pomme (son poids), et la pomme attire la Terre avec la même force (mais la Terre est beaucoup plus massive, donc son accélération est négligeable)."},
            {"index":6,"type":"texte","question":"Qu'est-ce que la force de frottement et comment modélise-t-on la résistance à l'avancement ?",
             "answer":"La force de frottement s'oppose au mouvement d'un objet. On la modélise souvent comme f = μ × N, où μ est le coefficient de frottement et N la force normale.",
             "correction":"La force de frottement (cinétique) s'oppose au glissement d'une surface sur une autre. Sa modélisation simplifiée : f = μk × N, où μk est le coefficient de frottement cinétique (sans unité, entre 0 et 1) et N la réaction normale (force perpendiculaire à la surface). La résistance à l'avancement (dans les fluides) est modélisée par une force proportionnelle à la vitesse (frottement visqueux) ou au carré de la vitesse (résistance aérodynamique) selon le régime d'écoulement."},
            {"index":7,"type":"qcm","question":"Lors d'une collision entre deux boules de billard, que se conserve-t-il en l'absence de forces extérieures ?",
             "options":["A. La vitesse de chaque boule","B. L'énergie cinétique uniquement","C. La quantité de mouvement totale du système","D. La force entre les boules"],
             "answer":"C",
             "correction":"En l'absence de forces extérieures, la quantité de mouvement totale du système se conserve (principe de conservation de la quantité de mouvement, conséquence de la 3ème loi de Newton) : Σp(avant) = Σp(après), avec p = m·v. En cas de collision élastique, l'énergie cinétique se conserve aussi ; en cas de collision inélastique, l'énergie cinétique est partiellement dissipée en chaleur, son, déformation."},
            {"index":8,"type":"vrai-faux","question":"La force de réaction normale exercée par un sol sur un objet en équilibre est toujours égale et opposée au poids de cet objet.",
             "answer":"VRAI",
             "correction":"VRAI (en équilibre statique). Pour un objet en équilibre sur un plan horizontal (a = 0), la somme des forces est nulle : R (réaction normale, vers le haut) + P (poids, vers le bas) = 0, donc R = -P, |R| = |P| = mg. Attention : ce n'est PAS la 3ème loi de Newton (qui s'applique à la paire 'la Terre attire l'objet / l'objet attire la Terre'), mais la conséquence du 1er principe appliqué à l'équilibre."},
        ]
    },
    277: {
        "serie": 5,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 5",
        "description": "L'énergie : formes et conservation",
        "questions": [
            {"index":1,"type":"qcm","question":"L'énergie cinétique d'un objet de masse m se déplaçant à la vitesse v est :",
             "options":["A. Ec = m × v","B. Ec = ½ × m × v²","C. Ec = m × g × h","D. Ec = m × a"],
             "answer":"B",
             "correction":"L'énergie cinétique est Ec = ½mv² (en joules, J), où m est la masse en kg et v la vitesse en m/s. Elle est toujours positive ou nulle. Le théorème de l'énergie cinétique stipule que la variation d'énergie cinétique d'un objet est égale au travail total des forces qui s'exercent sur lui : ΔEc = W(toutes forces) = Ec_finale - Ec_initiale."},
            {"index":2,"type":"vrai-faux","question":"L'énergie mécanique se conserve uniquement lorsque les forces de frottement sont nulles (absence de forces non conservatives).",
             "answer":"VRAI",
             "correction":"VRAI. L'énergie mécanique Em = Ec + Ep (énergie cinétique + énergie potentielle) se conserve seulement si les forces non conservatives (frottements, résistance de l'air) n'effectuent aucun travail (W_nc = 0). En présence de frottements, Em diminue, l'énergie mécanique perdue étant convertie en énergie thermique (chaleur). On a : Em(finale) = Em(initiale) + W_nc."},
            {"index":3,"type":"texte","question":"Définissez l'énergie potentielle gravitationnelle et exprimez son expression mathématique.",
             "answer":"L'énergie potentielle gravitationnelle est l'énergie d'un objet due à sa position en hauteur : Ep = mgh, où m est la masse, g la pesanteur et h l'altitude.",
             "correction":"L'énergie potentielle de pesanteur Ep = mgh, où m est la masse (kg), g l'accélération de la pesanteur (≈9,81 m/s²) et h l'altitude (m) mesurée par rapport à une référence choisie arbitrairement (Ep = 0). Elle représente le travail que la pesanteur peut effectuer pour amener l'objet de l'altitude h à la référence. La variation d'Ep lors d'une chute de h₁ à h₂ : ΔEp = mg(h₂-h₁) ; si h₂ < h₁ (chute), ΔEp < 0 et Ec augmente (conservation de Em)."},
            {"index":4,"type":"qcm","question":"Un objet de 2 kg tombe d'une hauteur de 5 m (sans frottements). Quelle est sa vitesse au moment de toucher le sol ? (g = 10 m/s²)",
             "options":["A. 5 m/s","B. 10 m/s","C. 20 m/s","D. 50 m/s"],
             "answer":"B",
             "correction":"Conservation de l'énergie mécanique (sans frottements) : Em_initiale = Em_finale. L'objet part du repos (Ec_i = 0) à h = 5m : Em_i = mgh = 2×10×5 = 100 J. Au sol (h = 0, Ep = 0) : Em_f = ½mv² = 100 J. Donc ½ × 2 × v² = 100 → v² = 100 → v = 10 m/s. Vérification : v² = 2gh = 2×10×5 = 100, v = 10 m/s ✓"},
            {"index":5,"type":"vrai-faux","question":"Le premier principe de la thermodynamique affirme que l'énergie totale d'un système isolé se conserve.",
             "answer":"VRAI",
             "correction":"VRAI. Le 1er principe de la thermodynamique est le principe de conservation de l'énergie : 'L'énergie ne se crée pas, ne se détruit pas, elle se transforme.' Pour un système isolé, ΔU = 0. Pour un système non isolé : ΔU = W + Q, où W est le travail reçu et Q la chaleur reçue. Les différentes formes d'énergie (cinétique, potentielle, thermique, chimique, électrique...) sont interconvertibles mais leur somme totale est constante."},
            {"index":6,"type":"texte","question":"Qu'est-ce que la puissance en physique et quelle est son unité ?",
             "answer":"La puissance est la quantité d'énergie transférée ou convertie par unité de temps : P = E/t ou P = W/t. Son unité est le watt (W).",
             "correction":"La puissance P est le rythme de transfert ou de conversion d'énergie : P = ΔE/Δt = W/t, en watts (W = J/s). Elle mesure à quelle vitesse l'énergie est fournie ou consommée. Exemples : une ampoule de 60 W consomme 60 J par seconde ; un moteur de 100 kW développe 100 000 J par seconde. La puissance instantanée est P = F·v (force × vitesse) pour une force appliquée dans la direction du mouvement."},
            {"index":7,"type":"qcm","question":"Quelle transformation d'énergie se produit dans un panneau solaire photovoltaïque ?",
             "options":["A. Energie chimique → électrique","B. Energie thermique → mécanique","C. Energie lumineuse → électrique","D. Energie nucléaire → thermique"],
             "answer":"C",
             "correction":"Un panneau solaire photovoltaïque convertit l'énergie lumineuse (photons du rayonnement solaire) en énergie électrique, par l'effet photoélectrique (découvert par Einstein en 1905). Les photons libèrent des électrons dans les cellules photovoltaïques en silicium, créant un courant électrique. Le rendement typique des panneaux commerciaux est de 15 à 22%."},
            {"index":8,"type":"vrai-faux","question":"Le kilowattheure (kWh) est une unité d'énergie utilisée notamment en électricité.",
             "answer":"VRAI",
             "correction":"VRAI. Le kilowattheure (kWh) est l'énergie consommée par un appareil de 1 kW en 1 heure : 1 kWh = 1000 W × 3600 s = 3,6 × 10⁶ J = 3,6 MJ. C'est l'unité pratique utilisée pour facturer la consommation électrique. Un téléphone consomme ~0,001 kWh par charge ; un four électrique ~1 kWh par heure d'utilisation."},
        ]
    },
    278: {
        "serie": 6,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 6",
        "description": "La chimie organique : structures et nomenclature",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la formule moléculaire du méthane, le plus simple des alcanes ?",
             "options":["A. C₂H₆","B. CH₄","C. C₃H₈","D. CH₃OH"],
             "answer":"B",
             "correction":"Le méthane CH₄ est le premier alcane (hydrocarbure saturé à chaîne ouverte). La formule générale des alcanes est CₙH₂ₙ₊₂. Pour n=1 : CH₄ (méthane) ; n=2 : C₂H₆ (éthane) ; n=3 : C₃H₈ (propane) ; n=4 : C₄H₁₀ (butane). Chaque atome de carbone fait 4 liaisons covalentes (règle de l'octet)."},
            {"index":2,"type":"vrai-faux","question":"Les alcènes sont des hydrocarbures insaturés contenant au moins une double liaison carbone-carbone (C=C).",
             "answer":"VRAI",
             "correction":"VRAI. Les alcènes (ou oléfines) sont des hydrocarbures à chaîne ouverte contenant au moins une double liaison C=C. Formule générale : CₙH₂ₙ (pour un alcène avec une seule double liaison). L'éthylène (éthène) CH₂=CH₂ est le plus simple. Les alcènes sont plus réactifs que les alcanes (addition électrophile sur la double liaison) et entrent dans la fabrication de nombreux plastiques (polyéthylène, PVC)."},
            {"index":3,"type":"texte","question":"Qu'est-ce qu'une fonction chimique en chimie organique ? Citez trois exemples de groupes fonctionnels.",
             "answer":"Une fonction chimique est un groupe d'atomes qui confère des propriétés chimiques caractéristiques à une molécule organique. Exemples : alcool (-OH), acide carboxylique (-COOH), amine (-NH₂).",
             "correction":"Un groupe fonctionnel (ou fonction chimique) est un atome ou groupe d'atomes responsable des propriétés chimiques caractéristiques d'une famille de molécules organiques. Exemples : groupe hydroxyle -OH → alcools (éthanol C₂H₅OH) ; groupe carboxyle -COOH → acides carboxyliques (acide acétique CH₃COOH) ; groupe amine -NH₂ → amines (aniline, acides aminés) ; groupe aldéhyde -CHO → aldéhydes ; groupe cétone C=O → cétones ; ester -COO- → esters."},
            {"index":4,"type":"qcm","question":"Quelle est la nomenclature IUPAC du composé CH₃-CH₂-CH₂-OH ?",
             "options":["A. Propanol-1","B. Éthanol","C. Butanol","D. Méthanol"],
             "answer":"A",
             "correction":"CH₃-CH₂-CH₂-OH est le propan-1-ol (propanol-1). La chaîne principale compte 3 carbones (prop-), la fonction est alcool (-ol), et le groupe -OH est sur le carbone 1. Nomenclature IUPAC : 1) identifier la chaîne principale (la plus longue contenant le groupe fonctionnel) ; 2) nommer la chaîne ; 3) ajouter le suffixe du groupe fonctionnel (-ol, -al, -one, -oïque...) ; 4) numéroter les carbones pour minimiser les indices."},
            {"index":5,"type":"vrai-faux","question":"Les isomères sont des molécules ayant la même formule moléculaire mais des structures différentes.",
             "answer":"VRAI",
             "correction":"VRAI. L'isomérie est un phénomène fondamental en chimie organique : des molécules de même formule brute (même nombre et type d'atomes) mais de structures différentes sont isomères. Elles ont des propriétés physiques et chimiques différentes. Types d'isomérie : de chaîne (squelette carboné différent), de position (groupe fonctionnel en position différente), de fonction (groupes fonctionnels différents), stéréoisomérie (configuration spatiale différente)."},
            {"index":6,"type":"texte","question":"Qu'est-ce qu'une réaction d'estérification et quelles en sont les conditions ?",
             "answer":"L'estérification est la réaction entre un acide carboxylique et un alcool pour former un ester et de l'eau. Elle est lente, limitée et catalysée par les ions H⁺.",
             "correction":"L'estérification : RCOOH + R'OH ⇌ RCOOR' + H₂O. C'est une réaction entre un acide carboxylique et un alcool qui donne un ester et de l'eau. Conditions : lente (cinétique), limitée (équilibre, τ ~ 2/3 si alcool primaire + acide), catalysée par les ions H⁺ (acide sulfurique concentré ou résine échangeuse d'ions). La réaction inverse (hydrolyse de l'ester) est la saponification (en milieu basique, totale et rapide). Applications : arômes alimentaires, parfums, plastifiants."},
            {"index":7,"type":"qcm","question":"Dans quel groupe fonctionnel les acides aminés, constituants des protéines, possèdent-ils à la fois une fonction acide et une fonction amine ?",
             "options":["A. Groupe hydroxyle -OH et amine -NH₂","B. Groupe carboxyle -COOH et amine -NH₂","C. Groupe aldéhyde -CHO et amine -NH₂","D. Groupe ester -COO- et amine -NH₂"],
             "answer":"B",
             "correction":"Les acides α-aminés ont la structure générale : NH₂-CHR-COOH, avec un groupe carboxyle -COOH (fonction acide), un groupe amine -NH₂ (fonction basique) et un radical R variable (chaîne latérale, 20 acides aminés différents). Les acides aminés s'unissent par des liaisons peptidiques (-CO-NH-) pour former les protéines. Leur caractère amphotère (acide et basique à la fois) est essentiel à leur fonction biologique."},
            {"index":8,"type":"vrai-faux","question":"Le glucose (C₆H₁₂O₆) est un glucide (sucre) qui peut être classé dans la famille des aldéhydes et polyols.",
             "answer":"VRAI",
             "correction":"VRAI. Le glucose (C₆H₁₂O₆) est un monosaccharide (ose). Sa forme linéaire (Fischer) montre qu'il possède un groupe aldéhyde -CHO en C1 (c'est donc un aldose) et plusieurs groupes hydroxyle -OH (polyol). En réalité, en solution, le glucose existe surtout sous forme cyclique (pyranose). Il est la principale source d'énergie des cellules vivantes (glycolyse)."},
        ]
    },
    279: {
        "serie": 7,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 7",
        "description": "L'optique géométrique",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la vitesse de la lumière dans le vide ?",
             "options":["A. 3 × 10⁶ m/s","B. 3 × 10⁸ m/s","C. 3 × 10¹⁰ m/s","D. 1,5 × 10⁸ m/s"],
             "answer":"B",
             "correction":"La vitesse de la lumière dans le vide est c = 3 × 10⁸ m/s ≈ 300 000 km/s. C'est une constante fondamentale de la physique (constante universelle). Dans un milieu d'indice de réfraction n, la lumière se propage à la vitesse v = c/n. Par exemple, dans l'eau (n ≈ 1,33) : v ≈ 2,26 × 10⁸ m/s."},
            {"index":2,"type":"vrai-faux","question":"Lors de la réfraction à l'interface de deux milieux, un rayon lumineux change de direction selon la loi de Snell-Descartes : n₁sin(θ₁) = n₂sin(θ₂).",
             "answer":"VRAI",
             "correction":"VRAI. La loi de Snell-Descartes (ou loi de la réfraction) : n₁ sin(θ₁) = n₂ sin(θ₂), où n₁ et n₂ sont les indices de réfraction des deux milieux et θ₁, θ₂ les angles par rapport à la normale à l'interface. Si n₂ > n₁ (milieu plus réfringent), le rayon se rapproche de la normale (θ₂ < θ₁). C'est la loi fondamentale de l'optique géométrique, à la base du fonctionnement des lentilles, prismes, fibres optiques."},
            {"index":3,"type":"texte","question":"Quelle est la distance focale d'une lentille convergente et qu'appelle-t-on vergence ?",
             "answer":"La distance focale f' est la distance entre le centre optique et le foyer image F'. La vergence V = 1/f' s'exprime en dioptries (δ). Une lentille convergente a V > 0.",
             "correction":"Pour une lentille mince convergente : le foyer image F' est le point où converge un faisceau de rayons parallèles à l'axe optique ; la distance focale image f' = OF' est positive. La vergence (ou puissance) V = 1/f' (en dioptries, δ, si f' est en mètres). Une lentille convergente a f' > 0 et V > 0 (ex : lentille convergente de +3 δ a f' = 1/3 m ≈ 33 cm). Une lentille divergente a f' < 0 et V < 0. La relation de conjugaison : 1/OA' - 1/OA = 1/f' = V."},
            {"index":4,"type":"qcm","question":"Dans quel phénomène la lumière blanche est-elle décomposée en ses différentes couleurs (spectre) ?",
             "options":["A. La réflexion","B. La diffraction","C. La dispersion (réfraction dans un prisme)","D. La polarisation"],
             "answer":"C",
             "correction":"La dispersion lumineuse est la décomposition de la lumière blanche en ses composantes monochromatiques (couleurs) lors de la réfraction, car l'indice de réfraction d'un milieu dépend de la longueur d'onde (fréquence). Un prisme ou une goutte de pluie (arc-en-ciel) dispersent la lumière blanche en spectre : violet (n le plus élevé, déviation maximale) → rouge (n le moins élevé). Newton a démontré que la lumière blanche est la superposition de toutes les couleurs."},
            {"index":5,"type":"vrai-faux","question":"L'oeil myope a un foyer image situé en avant de la rétine, et se corrige avec des lentilles divergentes.",
             "answer":"VRAI",
             "correction":"VRAI. Dans un œil myope, le globe oculaire est trop long ou le cristallin trop convergent : les rayons parallèles (venant de l'infini) convergent en avant de la rétine. L'image est donc floue pour les objets éloignés. Correction : lentille divergente (vergence négative) pour éloigner le foyer et le ramener sur la rétine. L'hypermétropie (foyer en arrière de la rétine) se corrige avec des lentilles convergentes."},
            {"index":6,"type":"texte","question":"Comment fonctionne un microscope optique ? Quels types de lentilles utilise-t-il ?",
             "answer":"Un microscope utilise deux lentilles convergentes : l'objectif (f' court) forme une image intermédiaire agrandie, et l'oculaire joue le rôle de loupe pour observer cette image.",
             "correction":"Un microscope optique est composé de deux lentilles convergentes : l'objectif (très courte focale, de quelques mm) forme une image réelle, agrandie et renversée de l'objet ; l'oculaire (focale plus longue) joue le rôle d'une loupe : il forme une image virtuelle agrandie de l'image intermédiaire. Le grossissement total G = G_obj × G_ocul. La résolution (pouvoir séparateur) d'un microscope optique est limitée par la longueur d'onde de la lumière visible (~200 nm minimum)."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que l'indice de réfraction d'un milieu optique ?",
             "options":["A. Le rapport de la vitesse de la lumière dans le milieu sur la vitesse dans le vide","B. L'inverse du rapport : n = c/v","C. La couleur du milieu","D. L'absorption de la lumière par le milieu"],
             "answer":"B",
             "correction":"L'indice de réfraction n d'un milieu est défini par n = c/v, où c est la vitesse de la lumière dans le vide (3×10⁸ m/s) et v la vitesse de la lumière dans ce milieu. n ≥ 1 (la lumière est toujours moins rapide dans un milieu que dans le vide). Exemples : air n ≈ 1 ; eau n ≈ 1,33 ; verre n ≈ 1,5 ; diamant n ≈ 2,4. Plus n est élevé, plus le milieu est 'réfringent' (dévie davantage la lumière)."},
            {"index":8,"type":"vrai-faux","question":"La fibre optique utilise le phénomène de réflexion totale interne pour transmettre la lumière sans perte sur de grandes distances.",
             "answer":"VRAI",
             "correction":"VRAI. La fibre optique exploite la réflexion totale interne : lorsque la lumière passe d'un milieu plus réfringent (cœur, n₁ élevé) à un milieu moins réfringent (gaine, n₂ < n₁), au-delà d'un angle critique θc = arcsin(n₂/n₁), la lumière est totalement réfléchie et ne sort pas de la fibre. Elle se propage par réflexions successives sur toute la longueur de la fibre avec très peu de pertes. Applications : télécommunications (internet très haut débit), endoscopie médicale."},
        ]
    },
    280: {
        "serie": 8,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 8",
        "description": "L'électricité et les circuits",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la relation entre la tension U (V), l'intensité I (A) et la résistance R (Ω) dans un conducteur ohmique ?",
             "options":["A. U = I + R","B. U = I × R (loi d'Ohm)","C. I = U × R","D. R = U + I"],
             "answer":"B",
             "correction":"La loi d'Ohm établit que pour un conducteur ohmique (résistance), la tension U aux bornes est proportionnelle à l'intensité I qui le traverse : U = R × I, où R est la résistance en ohms (Ω). Cette relation est valable pour de nombreux composants électriques (fils, résistances). Les unités : U en volts (V), I en ampères (A), R en ohms (Ω). 1 Ω = 1 V/A."},
            {"index":2,"type":"vrai-faux","question":"Dans un circuit en série, l'intensité du courant est la même en tout point du circuit.",
             "answer":"VRAI",
             "correction":"VRAI. Dans un circuit en série (composants montés les uns après les autres dans une seule boucle), l'intensité I est la même partout (conservation de la charge). Les tensions s'additionnent : U_total = U₁ + U₂ + ... Les résistances s'additionnent : R_eq = R₁ + R₂ + ... En parallèle, c'est l'inverse : les tensions sont égales et les intensités s'additionnent."},
            {"index":3,"type":"texte","question":"Calculez la résistance équivalente de deux résistances R₁ = 100 Ω et R₂ = 200 Ω montées en parallèle.",
             "answer":"En parallèle : 1/R_eq = 1/R₁ + 1/R₂ = 1/100 + 1/200 = 3/200, donc R_eq = 200/3 ≈ 66,7 Ω.",
             "correction":"Pour des résistances en parallèle : 1/R_eq = 1/R₁ + 1/R₂ + ... Calcul : 1/R_eq = 1/100 + 1/200 = 2/200 + 1/200 = 3/200. Donc R_eq = 200/3 ≈ 66,7 Ω. La résistance équivalente en parallèle est toujours inférieure à la plus petite résistance. Vérification : R_eq < R₁ = 100 Ω ✓. En parallèle, chaque résistance reçoit la même tension, mais l'intensité totale se divise entre les branches."},
            {"index":4,"type":"qcm","question":"Quelle est la puissance électrique dissipée par une résistance de 50 Ω traversée par un courant de 2 A ?",
             "options":["A. 25 W","B. 100 W","C. 200 W","D. 4 W"],
             "answer":"C",
             "correction":"La puissance électrique dissipée par effet Joule : P = R × I² = 50 × (2)² = 50 × 4 = 200 W. Formules équivalentes (en utilisant la loi d'Ohm U = RI) : P = U × I = U²/R = R × I². L'effet Joule (dissipation thermique) est la conversion d'énergie électrique en chaleur dans un conducteur résistif."},
            {"index":5,"type":"vrai-faux","question":"Un condensateur stocke de l'énergie sous forme de champ électrique entre ses armatures.",
             "answer":"VRAI",
             "correction":"VRAI. Un condensateur (ou capacitor) est composé de deux armatures conductrices séparées par un isolant (diélectrique). Lorsqu'il est chargé, il stocke de l'énergie sous forme de champ électrique : E = ½CV², où C est la capacité (en farads, F) et V la tension. Les condensateurs sont utilisés pour stocker de l'énergie, lisser les tensions, filtrer les signaux et dans les circuits oscillants."},
            {"index":6,"type":"texte","question":"Qu'est-ce que le courant alternatif (CA) et en quoi diffère-t-il du courant continu (CC) ?",
             "answer":"Le courant alternatif change périodiquement de sens et d'intensité (sinusoïdal) ; le courant continu a une intensité constante dans une seule direction.",
             "correction":"Le courant continu (CC ou DC) a une intensité constante qui circule toujours dans le même sens (piles, batteries). Le courant alternatif (CA ou AC) change périodiquement de sens selon une fonction sinusoïdale : i(t) = I_max × sin(2πft), où f est la fréquence (50 Hz en Europe, 60 Hz aux USA) et I_max l'amplitude. En France, le secteur est en 230 V (tension efficace) à 50 Hz. La valeur efficace est U_eff = U_max/√2."},
            {"index":7,"type":"qcm","question":"Quelle loi permet de calculer l'intensité dans chaque branche d'un nœud de circuit ?",
             "options":["A. La loi d'Ohm","B. La loi des mailles (Kirchhoff)","C. La loi des nœuds (Kirchhoff)","D. La loi de Coulomb"],
             "answer":"C",
             "correction":"La loi des nœuds (1ère loi de Kirchhoff) : la somme algébrique des intensités en un nœud est nulle : ΣI_entrant = ΣI_sortant. Elle traduit la conservation de la charge électrique. La loi des mailles (2ème loi de Kirchhoff) : la somme algébrique des tensions dans une maille fermée est nulle : ΣU = 0. Ces deux lois permettent de résoudre tout circuit électrique."},
            {"index":8,"type":"vrai-faux","question":"La diode est un composant électronique qui ne laisse passer le courant que dans un seul sens.",
             "answer":"VRAI",
             "correction":"VRAI. La diode est un composant électronique à semi-conducteur (jonction P-N) qui ne conduit le courant que dans un seul sens (sens direct). En sens inverse, elle bloque le courant (sauf à partir de la tension de claquage). La DEL (diode électroluminescente, LED) est une diode qui émet de la lumière lorsqu'elle est traversée par un courant. Les diodes sont utilisées dans la rectification du courant (conversion CA → CC), les détecteurs, les circuits logiques."},
        ]
    },
    281: {
        "serie": 9,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 9",
        "description": "La radioactivité et le noyau atomique",
        "questions": [
            {"index":1,"type":"qcm","question":"Qu'est-ce que la radioactivité ?",
             "options":["A. La propriété de certains atomes d'émettre de la lumière","B. La désintégration spontanée de noyaux atomiques instables avec émission de rayonnements","C. La fusion de noyaux atomiques stables","D. L'ionisation des atomes par des rayons X"],
             "answer":"B",
             "correction":"La radioactivité (découverte par Henri Becquerel en 1896) est la propriété de certains noyaux atomiques instables de se désintégrer spontanément en émettant des rayonnements (particules ou ondes électromagnétiques) pour atteindre un état plus stable. Les principaux types de rayonnements sont : alpha (α, noyau He), bêta- (β⁻, électron), bêta+ (β⁺, positron) et gamma (γ, photon de haute énergie)."},
            {"index":2,"type":"vrai-faux","question":"La demi-vie (ou période radioactive) est le temps au bout duquel la moitié des noyaux radioactifs d'un échantillon se sont désintégrés.",
             "answer":"VRAI",
             "correction":"VRAI. La demi-vie (t₁/₂ ou T) est le temps caractéristique d'une désintégration radioactive : après une demi-vie, la moitié des noyaux initiaux se sont désintégrés (N = N₀/2). La loi de décroissance radioactive est exponentielle : N(t) = N₀ × (1/2)^(t/T) = N₀ × e^(-λt), où λ = ln(2)/T est la constante radioactive. Les demi-vies varient de fractions de seconde à des milliards d'années selon les isotopes."},
            {"index":3,"type":"texte","question":"Établissez la loi de conservation des nombres de masse et de charge lors d'une désintégration radioactive alpha.",
             "answer":"Lors d'une désintégration α : le nombre de masse A diminue de 4 et le numéro atomique Z diminue de 2. Ex : ²³⁸U → ²³⁴Th + ⁴He",
             "correction":"Une désintégration alpha : ᴬZX → ᴬ⁻⁴Z₋₂Y + ⁴₂He (particule α). Conservation : nombre de masse A : Z_Y = A-4 ; numéro atomique Z_Y = Z-2. Exemple : désintégration de l'uranium-238 : ²³⁸₉₂U → ²³⁴₉₀Th + ⁴₂He. Les particules alpha sont fortement ionisantes mais faiblement pénétrantes (arrêtées par une feuille de papier). Loi de conservation générale : la somme des nombres de masse et des charges se conserve de part et d'autre de la flèche."},
            {"index":4,"type":"qcm","question":"Quel type de rayonnement radioactif est le plus pénétrant ?",
             "options":["A. Le rayonnement alpha (α)","B. Le rayonnement bêta (β)","C. Le rayonnement gamma (γ)","D. Les neutrons thermiques"],
             "answer":"C",
             "correction":"Le rayonnement gamma (γ) est le plus pénétrant des rayonnements radioactifs : ce sont des photons de très haute énergie (rayons X ou γ, λ très court). Ils nécessitent plusieurs centimètres de plomb ou plusieurs mètres de béton pour être absorbés. Le rayonnement α est le moins pénétrant (arrêté par quelques cm d'air ou une feuille de papier). Le β (électrons) est intermédiaire (arrêté par quelques mm d'aluminium)."},
            {"index":5,"type":"vrai-faux","question":"La fission nucléaire est la réaction dans laquelle un noyau lourd se casse en noyaux plus légers avec libération d'énergie.",
             "answer":"VRAI",
             "correction":"VRAI. La fission nucléaire est la scission d'un noyau lourd (uranium-235, plutonium-239) en deux noyaux plus légers (produits de fission), accompagnée de l'émission de 2 à 3 neutrons et d'une grande quantité d'énergie (E = mc², relation d'Einstein). Les neutrons émis peuvent provoquer d'autres fissions (réaction en chaîne). C'est le principe des réacteurs nucléaires (énergie contrôlée) et des bombes atomiques (énergie incontrôlée)."},
            {"index":6,"type":"texte","question":"Qu'est-ce que la fusion nucléaire et pourquoi représente-t-elle un défi technologique majeur ?",
             "answer":"La fusion nucléaire est la fusion de noyaux légers (deutérium, tritium) en un noyau plus lourd avec libération d'énergie. Le défi est d'atteindre les conditions de température et de confinement extrêmes nécessaires.",
             "correction":"La fusion nucléaire est la fusion de noyaux légers (ex : deutérium ²H + tritium ³H → hélium ⁴He + neutron + 17,6 MeV) qui libère des quantités d'énergie considérables (plus que la fission). C'est le mécanisme qui alimente les étoiles. Le défi technologique est d'atteindre et maintenir les conditions nécessaires : température ~150 millions de degrés (plasma), pression et durée suffisantes (critère de Lawson). Le projet ITER (France) tente de réaliser la fusion contrôlée pour la production d'énergie."},
            {"index":7,"type":"qcm","question":"Un isotope radioactif a une demi-vie de 10 jours. Après 30 jours, quelle fraction d'une quantité initiale N₀ reste-t-il ?",
             "options":["A. N₀/2","B. N₀/4","C. N₀/8","D. N₀/6"],
             "answer":"C",
             "correction":"Après 30 jours, il s'est écoulé 30/10 = 3 demi-vies. Après n demi-vies, il reste N₀ × (1/2)ⁿ. Donc : N = N₀ × (1/2)³ = N₀/8. Vérification : après 10 j → N₀/2 ; après 20 j → N₀/4 ; après 30 j → N₀/8. La décroissance radioactive est exponentielle."},
            {"index":8,"type":"vrai-faux","question":"La relation d'Einstein E = mc² permet de comprendre l'origine de l'énergie libérée lors des réactions nucléaires.",
             "answer":"VRAI",
             "correction":"VRAI. La relation d'Einstein E = mc² (énergie = masse × carré de la vitesse de la lumière) exprime l'équivalence masse-énergie. Lors des réactions nucléaires (fission, fusion), la masse totale des produits est légèrement inférieure à la masse des réactifs (défaut de masse Δm). Cette masse est convertie en énergie : ΔE = Δm × c². Même un très petit Δm donne une grande quantité d'énergie (c² = 9×10¹⁶ J/kg)."},
        ]
    },
    282: {
        "serie": 10,
        "title": "Quiz Diagnostic 1ere Physique-Chimie - Serie 10",
        "description": "La thermodynamique et les échanges thermiques",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la différence entre température et chaleur ?",
             "options":["A. Il n'y a pas de différence : ce sont des synonymes","B. La température mesure l'agitation thermique moyenne des particules ; la chaleur est un transfert d'énergie thermique","C. La chaleur est plus précise que la température","D. La température s'exprime en joules"],
             "answer":"B",
             "correction":"La température (T, en kelvins K ou °C) est une grandeur intensive qui mesure l'agitation thermique moyenne des particules d'un système. La chaleur (Q, en joules J) est un transfert d'énergie thermique entre deux systèmes à températures différentes (du plus chaud vers le plus froid). La chaleur est une énergie en transit, pas une propriété du corps (contrairement à l'énergie interne)."},
            {"index":2,"type":"vrai-faux","question":"La conduction, la convection et le rayonnement sont les trois modes de transfert thermique.",
             "answer":"VRAI",
             "correction":"VRAI. Les trois modes de transfert d'énergie thermique : conduction (transfert par contact direct entre particules dans un solide ou fluide immobile, ex : chauffage d'un métal) ; convection (transfert par déplacement de matière dans un fluide, ex : courants d'air chaud) ; rayonnement (transfert par ondes électromagnétiques infrarouge sans support matériel, ex : chaleur du Soleil dans le vide, infrarouge d'un radiateur)."},
            {"index":3,"type":"texte","question":"Énoncez le deuxième principe de la thermodynamique et expliquez son implication sur l'échange de chaleur.",
             "answer":"Le 2e principe stipule que la chaleur se transfère spontanément du corps le plus chaud vers le corps le plus froid (entropie croissante). Le transfert inverse est impossible sans travail extérieur.",
             "correction":"Le 2e principe de la thermodynamique (principe de Carnot, Clausius) : 'Dans un système isolé, l'entropie (désordre) ne peut qu'augmenter ou rester constante (processus réversible).' Conséquence directe : la chaleur se transfère spontanément du corps chaud vers le corps froid, jamais l'inverse sans apport extérieur d'énergie. Cela explique l'irréversibilité des processus naturels (refroidissement d'un café, mélange de fluides) et les limites de rendement des machines thermiques."},
            {"index":4,"type":"qcm","question":"Quelle relation relie la chaleur Q reçue par un corps à sa variation de température ΔT (sans changement d'état) ?",
             "options":["A. Q = m × ΔT","B. Q = m × c × ΔT (chaleur sensible)","C. Q = m × Lf (chaleur latente)","D. Q = P × t"],
             "answer":"B",
             "correction":"La chaleur sensible (sans changement d'état) : Q = m × c × ΔT, où m est la masse (kg), c la capacité thermique massique (J/(kg·K), propre à chaque matériau) et ΔT la variation de température. Si Q > 0, le corps reçoit de la chaleur et sa température augmente. La capacité thermique de l'eau est c = 4 180 J/(kg·K), ce qui en fait un excellent fluide caloporteur."},
            {"index":5,"type":"vrai-faux","question":"Lors d'un changement d'état (fusion, vaporisation), la température reste constante malgré l'apport ou la soustraction de chaleur.",
             "answer":"VRAI",
             "correction":"VRAI. Lors d'un changement d'état à pression constante (ex : fusion de la glace à 0°C, ébullition de l'eau à 100°C), la température reste constante tant que les deux phases coexistent. L'énergie fournie (chaleur latente) sert à rompre les liaisons intermoléculaires sans augmenter l'agitation thermique. Q = m × L, où L est la chaleur latente spécifique (fusion, vaporisation). Pour l'eau : Lf = 334 kJ/kg (fusion) ; Lv = 2 257 kJ/kg (vaporisation)."},
            {"index":6,"type":"texte","question":"Qu'est-ce que l'effet de serre et comment contribue-t-il au réchauffement climatique ?",
             "answer":"L'effet de serre naturel maintient la température terrestre habitable. L'effet de serre renforcé par les GES d'origine humaine (CO₂, CH₄) piège davantage de chaleur et réchauffe la planète.",
             "correction":"L'effet de serre naturel : l'atmosphère terrestre laisse passer les rayonnements solaires (UV, visible) mais absorbe et réémet les infrarouges émis par la Terre (gaz à effet de serre : H₂O, CO₂, CH₄, N₂O). Sans cet effet, la température serait -18°C au lieu de +15°C. L'effet de serre renforcé : l'augmentation des GES d'origine humaine (combustion fossiles, déforestation, agriculture) intensifie cet effet, entraînant un réchauffement global (+1,1°C depuis l'ère préindustrielle), avec des conséquences climatiques majeures."},
            {"index":7,"type":"qcm","question":"Quelle est la conversion entre la température en Celsius (°C) et en Kelvin (K) ?",
             "options":["A. T(K) = T(°C) + 100","B. T(K) = T(°C) + 273,15","C. T(K) = T(°C) - 273,15","D. T(K) = T(°C) × 1,8 + 32"],
             "answer":"B",
             "correction":"T(K) = T(°C) + 273,15. Le zéro absolu (0 K = -273,15 °C) est la température la plus basse possible (agitation thermique nulle). L'échelle Kelvin est l'échelle de température du SI. Exemples : 0°C = 273 K (fusion de la glace) ; 100°C = 373 K (ébullition de l'eau) ; -273°C ≈ 0 K (zéro absolu). L'échelle Fahrenheit : T(°F) = T(°C) × 9/5 + 32."},
            {"index":8,"type":"vrai-faux","question":"Un corps noir idéal absorbe et émet le maximum possible de rayonnement électromagnétique pour toutes les longueurs d'onde.",
             "answer":"VRAI",
             "correction":"VRAI. Un corps noir est un objet idéal qui absorbe tout le rayonnement incident (aucune réflexion) et émet le maximum de rayonnement pour une température donnée. Sa distribution spectrale est décrite par la loi de Planck (1900). La loi de Stefan-Boltzmann : P = σ × T⁴ (puissance émise proportionnelle à T⁴). La loi de Wien : λ_max × T = constante (longueur d'onde d'émission maximale inversement proportionnelle à T). Le Soleil se comporte approximativement comme un corps noir à 5 778 K."},
        ]
    },
}

# ============================================================
# Remaining PC topics (283-321)
# ============================================================
pc_remaining = [
    (283, 11, "Les ondes mécaniques et sonores"),
    (284, 12, "Les ondes électromagnétiques"),
    (285, 13, "La réaction chimique et la stœchiométrie"),
    (286, 14, "Les équilibres chimiques"),
    (287, 15, "Les oxydoréductions"),
    (288, 16, "L'électrochimie et les piles"),
    (289, 17, "La mécanique des fluides"),
    (290, 18, "La pression et ses applications"),
    (291, 19, "Le magnétisme et l'induction"),
    (292, 20, "Les oscillations et résonance"),
    (293, 21, "La physique quantique : introduction"),
    (294, 22, "Les molécules du vivant"),
    (295, 23, "La réaction acido-basique"),
    (296, 24, "La synthèse organique"),
    (297, 25, "Les polymères"),
    (298, 26, "La chromatographie"),
    (299, 27, "La spectroscopie IR et RMN"),
    (300, 28, "Les transformations de la matière"),
    (301, 29, "La relativité restreinte : introduction"),
    (302, 30, "L'astronomie et les lois de Kepler"),
    (303, 31, "La gravitation universelle"),
    (304, 32, "L'énergie nucléaire"),
    (305, 33, "Les sources d'énergie renouvelables"),
    (306, 34, "La chimie verte et l'environnement"),
    (307, 35, "Les matériaux : propriétés et usages"),
    (308, 36, "La biochimie : enzymes et catalyse"),
    (309, 37, "Les solutions tampon et le pH sanguin"),
    (310, 38, "Les équations de mouvement en 2D"),
    (311, 39, "La mécanique céleste"),
    (312, 40, "Les phénomènes de surface et tension superficielle"),
    (313, 41, "La physique des semiconducteurs"),
    (314, 42, "L'acoustique et les sons"),
    (315, 43, "Les réactions de combustion"),
    (316, 44, "La cinétique chimique"),
    (317, 45, "Les colorants et la lumière"),
    (318, 46, "La chimie des médicaments"),
    (319, 47, "Les transformations nucléaires appliquées"),
    (320, 48, "La physique-chimie et le sport"),
    (321, 49, "Révision générale Physique-Chimie 1ère"),
]

def make_generic_pc_quiz(file_id, serie, description):
    """Generate 8 physics-chemistry questions with real content."""
    generic = [
        ("qcm", f"Quelle est la démarche expérimentale de base en physique-chimie pour étudier '{description}' ?",
         ["A. Observer, formuler une hypothèse, expérimenter, analyser, conclure",
          "B. Lire des documents et mémoriser les résultats",
          "C. Effectuer des calculs mathématiques uniquement",
          "D. Observer sans interpréter"],
         "A",
         f"La démarche scientifique en physique-chimie suit le cycle hypothético-déductif : 1) Observation du phénomène ; 2) Formulation d'une hypothèse ; 3) Expérimentation pour la tester ; 4) Analyse des résultats (mesures, graphes) ; 5) Conclusion (validation ou réfutation de l'hypothèse). Cette méthode s'applique particulièrement bien à l'étude de '{description}'."),
        ("vrai-faux", f"Les grandeurs physiques étudiées en '{description}' s'expriment toujours dans le Système International (SI) d'unités.",
         None, "VRAI",
         "VRAI. Le Système International (SI) est le système d'unités adopté universellement en sciences. Il définit 7 unités de base : mètre (m), kilogramme (kg), seconde (s), ampère (A), kelvin (K), mole (mol), candela (cd). Toutes les autres unités sont dérivées de ces 7 unités de base. Il est indispensable d'utiliser les unités SI dans les calculs pour obtenir des résultats cohérents."),
        ("texte", f"Expliquez comment les mathématiques sont utilisées en physique-chimie pour modéliser des phénomènes réels.",
         None, "Les mathématiques permettent de formaliser les lois physiques, d'exprimer des relations quantitatives entre grandeurs et de prédire le comportement des systèmes.",
         f"En physique-chimie, les mathématiques servent à : 1) Formaliser des lois expérimentales sous forme d'équations (ex : F = ma) ; 2) Calculer des grandeurs inaccessibles directement à la mesure ; 3) Extrapoler des tendances et prédire des comportements futurs ; 4) Représenter graphiquement des variations (courbes, histogrammes) ; 5) Modéliser des phénomènes complexes par des approximations (ex : gaz parfait). La modélisation est au cœur de la démarche scientifique en physique-chimie."),
        ("qcm", "Quelle est l'incertitude absolue et quel est son rôle dans les mesures expérimentales ?",
         ["A. L'erreur commise lors d'un calcul mathématique",
          "B. L'intervalle dans lequel se trouve la valeur vraie d'une grandeur mesurée, reflétant la précision de la mesure",
          "C. La valeur exacte d'une grandeur",
          "D. L'écart entre deux mesures successives"],
         "B",
         "L'incertitude absolue (Δx) est l'intervalle de confiance autour d'une mesure x : la valeur vraie se trouve dans l'intervalle [x-Δx ; x+Δx]. Elle traduit la précision et la reproductibilité d'une mesure. On note : x = mesure ± Δx. L'incertitude relative Δx/x (%) est un indicateur de la qualité de la mesure. En physique-chimie, tout résultat expérimental doit être accompagné de son incertitude."),
        ("vrai-faux", "En physique-chimie, une loi est plus générale qu'un principe car elle découle d'observations expérimentales.",
         None, "FAUX",
         "FAUX. C'est l'inverse : un principe (ou axiome) est plus général qu'une loi car il est posé comme fondement sans démonstration (ex : principe de conservation de l'énergie, principe d'inertie). Une loi est une relation mathématique vérifiée expérimentalement dans un domaine de validité défini (ex : loi d'Ohm valable pour les conducteurs ohmiques, loi des gaz parfaits valable pour les pressions faibles et températures élevées)."),
        ("texte", f"Donnez deux exemples d'applications concrètes dans notre vie quotidienne liées au domaine de '{description}'.",
         None, f"Ce domaine de la physique-chimie a de nombreuses applications quotidiennes dans la technologie, la médecine, l'environnement ou l'industrie.",
         f"Le domaine de '{description}' trouve de nombreuses applications quotidiennes : dans les technologies (smartphones, ordinateurs, éclairage LED), la médecine (IRM, échographie, médicaments), l'environnement (panneaux solaires, traitement des eaux), l'alimentation (conservation des aliments, cuisson) et l'industrie (matériaux, chimie). La physique-chimie est omniprésente dans notre monde moderne et ses avancées améliorent continuellement notre qualité de vie."),
        ("qcm", "Qu'est-ce que l'analyse dimensionnelle en physique ?",
         ["A. L'étude de la forme géométrique des objets physiques",
          "B. La vérification de la cohérence des unités dans une équation physique",
          "C. La mesure des dimensions d'un objet",
          "D. L'étude de la physique à différentes échelles"],
         "B",
         "L'analyse dimensionnelle (ou homogénéité dimensionnelle) consiste à vérifier que les unités de chaque membre d'une équation physique sont identiques. Si F = ma, les dimensions doivent s'équilibrer : [F] = kg·m/s² = N ✓. Cette technique permet de vérifier des équations, de retrouver des lois physiques et de détecter des erreurs de calcul. Une équation physique n'est valide que si elle est dimensionnellement homogène."),
        ("vrai-faux", "La précision d'un instrument de mesure est toujours meilleure que son exactitude.",
         None, "FAUX",
         "FAUX. Précision et exactitude sont deux qualités distinctes d'une mesure. La précision (ou fidélité) mesure la reproductibilité : des mesures précises sont groupées entre elles. L'exactitude mesure la proximité à la valeur vraie. Un instrument peut être précis mais inexact (erreur systématique, comme une balance déréglée) ou exact mais imprécis (valeurs dispersées). L'idéal est d'avoir des mesures à la fois précises et exactes."),
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
        print(f"  ✓ {file_id}.json [{qdata['serie']}/49] - {qdata['description']}")

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
        print(f"  ✓ {file_id}.json [{serie}/49] - {description}")

    print(f"\n✅ Physique-Chimie: {count} quiz files generated (+ {count} answers = {count*6} total files)")


if __name__ == "__main__":
    write_quiz_files()
