import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
PHILO_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "philo_quizzes")
PHILO_QUIZ_DIR = os.path.join(PHILO_OUTPUT_DIR, "quiz")
PHILO_ANSWERS_DIR = os.path.join(PHILO_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "philo_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

os.makedirs(PHILO_QUIZ_DIR, exist_ok=True)
os.makedirs(PHILO_ANSWERS_DIR, exist_ok=True)
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
# PHILOSOPHIE 1Ã¨re â€” fichiers 224 Ã  272 (49 quizzes)
# ============================================================

philo_data = {
    224: {
        "serie": 1,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 1",
        "description": "La conscience et le sujet",
        "questions": [
            {"index":1,"type":"qcm","question":"Pour Descartes, quelle est la premiÃ¨re certitude absolue Ã  laquelle parvient le philosophe aprÃ¨s le doute mÃ©thodique ?",
             "options":["A. L'existence de Dieu","B. 'Je pense, donc je suis' (cogito ergo sum)","C. L'existence du monde extÃ©rieur","D. La certitude des mathÃ©matiques"],
             "answer":"B",
             "correction":"Le cogito cartÃ©sien ('Je pense, donc je suis') est la premiÃ¨re vÃ©ritÃ© indubitable trouvÃ©e par Descartes aprÃ¨s avoir appliquÃ© le doute hyperbolique Ã  toutes ses croyances (MÃ©ditations mÃ©taphysiques, 1641). MÃªme si je doute, je suis une chose qui pense : c'est le fondement de toute connaissance."},
            {"index":2,"type":"vrai-faux","question":"La conscience de soi est une capacitÃ© prÃ©sente chez tous les Ãªtres vivants, y compris les animaux.",
             "answer":"FAUX",
             "correction":"FAUX. Pour la plupart des philosophes (Descartes, Hegel), la conscience de soi (capacitÃ© Ã  se prendre soi-mÃªme comme objet de rÃ©flexion) est une caractÃ©ristique spÃ©cifiquement humaine. Les animaux peuvent avoir une conscience spontanÃ©e (perception immÃ©diate), mais la conscience rÃ©flexive â€“ se retourner sur soi-mÃªme et se connaÃ®tre â€“ semble propre Ã  l'Ãªtre humain."},
            {"index":3,"type":"vrai-faux","question":"Expliquez la distinction entre conscience immÃ©diate (ou spontanÃ©e) et conscience rÃ©flexive.",
             "answer":"La conscience immÃ©diate est la perception directe du monde ; la conscience rÃ©flexive est la capacitÃ© Ã  se prendre soi-mÃªme comme objet de pensÃ©e.",
             "correction":"La conscience immÃ©diate (ou spontanÃ©e) est la conscience tournÃ©e vers le monde : je perÃ§ois, j'agis, je ressens, sans me prendre moi-mÃªme pour objet. La conscience rÃ©flexive (ou rÃ©flexion) est le retour de la conscience sur elle-mÃªme : je pense ma propre pensÃ©e, je m'observe, je me connais. Hegel dans la PhÃ©nomÃ©nologie de l'Esprit montre que la conscience de soi passe nÃ©cessairement par l'autre (dialectique maÃ®tre-esclave)."},
            {"index":4,"type":"qcm","question":"Selon Sartre, la conscience humaine est fondamentalement :",
             "options":["A. DÃ©terminÃ©e par l'inconscient","B. LibertÃ© absolue, sans nature fixÃ©e Ã  l'avance ('l'existence prÃ©cÃ¨de l'essence')","C. Identique Ã  la conscience animale","D. DÃ©terminÃ©e par les conditions matÃ©rielles"],
             "answer":"B",
             "correction":"Pour Sartre (L'existentialisme est un humanisme, 1945), 'l'existence prÃ©cÃ¨de l'essence' : l'homme n'a pas de nature prÃ©dÃ©finie, il se crÃ©e par ses choix et ses actes. La conscience est 'nÃ©ant', pur projet, libertÃ© radicale. L'homme est 'condamnÃ© Ã  Ãªtre libre' : mÃªme ne pas choisir est un choix."},
            {"index":5,"type":"vrai-faux","question":"Le sujet philosophique dÃ©signe simplement la personne grammaticale qui parle ou agit.",
             "answer":"FAUX",
             "correction":"FAUX. En philosophie, le 'sujet' est l'Ãªtre conscient, pensant, capable de se rapporter Ã  lui-mÃªme et au monde. Il est le centre de l'expÃ©rience subjective et de la connaissance. Cette notion implique l'identitÃ© personnelle, la libertÃ© et la responsabilitÃ© morale â€“ bien au-delÃ  du simple usage grammatical."},
            {"index":6,"type":"vrai-faux","question":"En quoi consiste le problÃ¨me de l'identitÃ© personnelle ? Illustrez avec un auteur.",
             "answer":"Le problÃ¨me de l'identitÃ© personnelle est de savoir ce qui fait qu'une personne reste la mÃªme au fil du temps malgrÃ© les changements. Locke l'ancre dans la continuitÃ© de la conscience et de la mÃ©moire.",
             "correction":"Le problÃ¨me de l'identitÃ© personnelle pose la question : qu'est-ce qui fait que je suis bien la mÃªme personne qu'hier, enfant, ou il y a 10 ans, malgrÃ© les changements physiques et psychologiques ? Locke (Essai sur l'entendement humain) ancre l'identitÃ© dans la continuitÃ© de la conscience et de la mÃ©moire. Hume nie toute identitÃ© substantielle du moi (faisceau de perceptions). Parfit (Reasons and Persons) remet en question la notion mÃªme de personne identique dans le temps."},
            {"index":7,"type":"qcm","question":"La maxime 'Connais-toi toi-mÃªme' (GnÃ´thi seauton) est associÃ©e Ã  :",
             "options":["A. Aristote","B. Platon / Socrate","C. Ã‰picure","D. Nietzsche"],
             "answer":"B",
             "correction":"'Connais-toi toi-mÃªme' est l'inscription gravÃ©e au frontispice du temple de Delphes et le principe fondateur de la philosophie socratique. Pour Socrate (selon Platon), la sagesse commence par la connaissance de soi, de ses ignorances et de ses limites. C'est le point de dÃ©part de toute dÃ©marche philosophique authentique."},
            {"index":8,"type":"vrai-faux","question":"Pour Freud, la conscience est la seule instance psychique qui dÃ©termine nos comportements.",
             "answer":"FAUX",
             "correction":"FAUX. Freud (L'interprÃ©tation des rÃªves, 1900 ; Introduction Ã  la psychanalyse) montre que l'inconscient â€“ ensemble de reprÃ©sentations refoulÃ©es inaccessibles Ã  la conscience â€“ joue un rÃ´le dÃ©terminant dans nos comportements, dÃ©sirs, symptÃ´mes et actes manquÃ©s. La conscience n'est que 'la partie Ã©mergÃ©e de l'iceberg' ; l'inconscient en constitue la plus grande partie."},
        ]
    },
    225: {
        "serie": 2,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 2",
        "description": "L'inconscient",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle mÃ©thode Freud dÃ©veloppe-t-il pour accÃ©der Ã  l'inconscient ?",
             "options":["A. La mÃ©ditation","B. La psychanalyse (association libre, interprÃ©tation des rÃªves)","C. La dialectique socratique","D. L'introspection rationnelle"],
             "answer":"B",
             "correction":"Freud dÃ©veloppe la psychanalyse : mÃ©thode thÃ©rapeutique et thÃ©orie de l'appareil psychique. Ses techniques d'accÃ¨s Ã  l'inconscient sont l'association libre (dire tout ce qui vient Ã  l'esprit sans censure), l'interprÃ©tation des rÃªves ('voie royale vers l'inconscient'), l'analyse des actes manquÃ©s et des lapsus (Psychopathologie de la vie quotidienne)."},
            {"index":2,"type":"vrai-faux","question":"L'inconscient freudien est simplement ce dont on n'est pas conscient Ã  un moment donnÃ©, comme les souvenirs oubliÃ©s.",
             "answer":"FAUX",
             "correction":"FAUX. Pour Freud, l'inconscient n'est pas seulement le 'prÃ©conscient' (ce qui est temporairement hors de la conscience). L'inconscient freudien est un systÃ¨me actif qui contient des reprÃ©sentations refoulÃ©es â€“ trop douloureuses ou inacceptables pour Ãªtre conscientes â€“ qui exercent une force permanente sur nos comportements, dÃ©sirs et symptÃ´mes, malgrÃ© la rÃ©sistance de la censure."},
            {"index":3,"type":"vrai-faux","question":"Qu'est-ce que le refoulement chez Freud et quel est son rÃ´le dans la formation de l'inconscient ?",
             "answer":"Le refoulement est le mÃ©canisme de dÃ©fense par lequel des reprÃ©sentations jugÃ©es inacceptables sont repoussÃ©es dans l'inconscient et maintenues hors de la conscience.",
             "correction":"Le refoulement (VerdrÃ¤ngung) est le mÃ©canisme central de la psychanalyse freudienne : la censure psychique refoule dans l'inconscient les reprÃ©sentations liÃ©es Ã  des dÃ©sirs inacceptables (pulsions sexuelles, agressives), les maintenant hors de la conscience. Ces reprÃ©sentations refoulÃ©es ne disparaissent pas mais exercent une pression constante et reviennent sous des formes dÃ©guisÃ©es : rÃªves, lapsus, actes manquÃ©s, symptÃ´mes nÃ©vrotiques."},
            {"index":4,"type":"qcm","question":"Quelle objection philosophique majeure peut-on adresser Ã  la notion d'inconscient freudien ?",
             "options":["A. L'inconscient n'existe que chez les malades mentaux","B. Si l'inconscient est par dÃ©finition inaccessible Ã  la conscience, on ne peut ni le prouver ni le rÃ©futer (problÃ¨me de rÃ©futabilitÃ©)","C. La psychanalyse est trop rationnelle","D. Freud ne parle que des rÃªves"],
             "answer":"B",
             "correction":"La critique philosophique majeure de l'inconscient freudien (notamment Karl Popper) porte sur sa non-rÃ©futabilitÃ© : une thÃ©orie scientifique doit pouvoir Ãªtre potentiellement infirmÃ©e par l'expÃ©rience. La psychanalyse interprÃ¨te tout (confirmation comme infirmation) comme preuve de sa thÃ©orie. De plus, Sartre (L'ÃŠtre et le NÃ©ant) critique la 'mauvaise foi' : l'inconscient est une faÃ§on de nier sa libertÃ© et sa responsabilitÃ©."},
            {"index":5,"type":"vrai-faux","question":"Selon Freud, le Ã§a, le moi et le surmoi sont les trois instances de la deuxiÃ¨me topique de l'appareil psychique.",
             "answer":"VRAI",
             "correction":"VRAI. Dans la deuxiÃ¨me topique freudienne (Au-delÃ  du principe de plaisir, 1920), Freud distingue trois instances : le Ã§a (rÃ©servoir des pulsions, principe de plaisir, inconscient), le moi (instance de mÃ©diation entre le Ã§a, le surmoi et la rÃ©alitÃ©, principe de rÃ©alitÃ©) et le surmoi (instance morale intÃ©riorisÃ©e, censure, idÃ©al du moi)."},
            {"index":6,"type":"vrai-faux","question":"En quoi la dÃ©couverte de l'inconscient constitue-t-elle, selon Freud, une 'blessure narcissique' pour l'humanitÃ© ?",
             "answer":"L'inconscient montre que le moi n'est pas maÃ®tre chez lui : nos comportements sont en partie dÃ©terminÃ©s par des forces que nous ne contrÃ´lons pas, blessant ainsi notre image de maÃ®trise de nous-mÃªmes.",
             "correction":"Freud (Introduction Ã  la psychanalyse) identifie trois 'blessures narcissiques' : Copernic (la Terre n'est pas le centre de l'univers), Darwin (l'homme descend de l'animal) et lui-mÃªme (le moi n'est pas maÃ®tre chez lui). La psychanalyse montre que nos pensÃ©es, dÃ©sirs et comportements sont largement dÃ©terminÃ©s par un inconscient que nous ne contrÃ´lons pas, remettant en cause l'idÃ©al de maÃ®trise de soi de la philosophie classique."},
            {"index":7,"type":"qcm","question":"Quel philosophe affirme que la thÃ¨se de l'inconscient est incompatible avec la libertÃ© humaine et constitue une 'mauvaise foi' ?",
             "options":["A. Freud lui-mÃªme","B. Hegel","C. Sartre","D. Descartes"],
             "answer":"C",
             "correction":"Jean-Paul Sartre (L'ÃŠtre et le NÃ©ant, 1943) critique l'inconscient freudien : pour lui, la conscience est transparente Ã  elle-mÃªme et fondamentalement libre. Se rÃ©fugier derriÃ¨re l'inconscient pour expliquer ses actes ('c'est mon inconscient') est une forme de 'mauvaise foi' â€“ une maniÃ¨re de fuir sa libertÃ© et sa responsabilitÃ© en se traitant comme une chose dÃ©terminÃ©e."},
            {"index":8,"type":"vrai-faux","question":"L'interprÃ©tation des rÃªves selon Freud montre que les rÃªves ont un sens cachÃ© qui exprime des dÃ©sirs inconscients refoulÃ©s.",
             "answer":"VRAI",
             "correction":"VRAI. Pour Freud (L'interprÃ©tation des rÃªves, 1900), le rÃªve est 'la voie royale vers l'inconscient'. Il distingue le contenu manifeste (ce dont on se souvient) et le contenu latent (le sens cachÃ©). Le travail du rÃªve (condensation, dÃ©placement, mise en scÃ¨ne, Ã©laboration secondaire) transforme les dÃ©sirs inconscients refoulÃ©s en contenu manifeste acceptable pour la censure."},
        ]
    },
    226: {
        "serie": 3,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 3",
        "description": "La libertÃ©",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle position philosophique affirme que toutes nos actions sont dÃ©terminÃ©es par des causes antÃ©rieures, rendant la libertÃ© illusoire ?",
             "options":["A. Le libertarisme","B. Le dÃ©terminisme","C. Le compatibilisme","D. L'existentialisme"],
             "answer":"B",
             "correction":"Le dÃ©terminisme affirme que tous les Ã©vÃ©nements, y compris nos actions et pensÃ©es, sont intÃ©gralement dÃ©terminÃ©s par des causes antÃ©rieures (biologiques, psychologiques, sociales, physiques). Dans cette perspective, la libertÃ© au sens de 'pouvoir faire autrement' est une illusion. Spinoza et plus tard le positivisme scientifique dÃ©fendent cette position."},
            {"index":2,"type":"vrai-faux","question":"Pour Kant, la libertÃ© est compatible avec le dÃ©terminisme naturel, car elle appartient Ã  un ordre diffÃ©rent (le monde intelligible).",
             "answer":"VRAI",
             "correction":"VRAI. Kant (Critique de la raison pratique) rÃ©sout l'antinomie libertÃ©/dÃ©terminisme en distinguant deux mondes : le monde phÃ©nomÃ©nal (nature, dÃ©terminisme causal) et le monde noumÃ¨nal (choses en soi, libertÃ©). En tant qu'Ãªtre sensible, l'homme est soumis au dÃ©terminisme ; en tant qu'Ãªtre raisonnable (noumÃ¨ne), il est libre et soumis Ã  la loi morale qu'il se donne lui-mÃªme (autonomie)."},
            {"index":3,"type":"vrai-faux","question":"Qu'est-ce que le libre arbitre et pourquoi est-il au cÅ“ur du dÃ©bat sur la libertÃ© ?",
             "answer":"Le libre arbitre est la capacitÃ© de choisir librement entre plusieurs options, indÃ©pendamment de toute dÃ©termination extÃ©rieure ou intÃ©rieure. Il est au cÅ“ur du dÃ©bat entre dÃ©terminisme et libertarisme.",
             "correction":"Le libre arbitre (liberum arbitrium) est la facultÃ© de choisir librement, d'Ãªtre la cause de ses propres actes sans Ãªtre entiÃ¨rement dÃ©terminÃ© par des causes externes ou internes (nature, Ã©ducation, inconscient). Il est central en philosophie morale et juridique : si l'homme n'a pas de libre arbitre, peut-on le tenir responsable de ses actes ? Le compatibilisme (Hume, Hobbes) tente de concilier libertÃ© et dÃ©terminisme en redÃ©finissant la libertÃ© comme l'absence de contrainte extÃ©rieure."},
            {"index":4,"type":"qcm","question":"Pour Spinoza, l'homme qui croit agir librement est en rÃ©alitÃ© :",
             "options":["A. VÃ©ritablement libre car il suit sa nature","B. Ignorant des causes qui le dÃ©terminent, car tout est nÃ©cessitÃ©","C. Libre grÃ¢ce Ã  la raison","D. Libre car il obÃ©it Ã  Dieu"],
             "answer":"B",
             "correction":"Pour Spinoza (Ã‰thique, Appendice de la Partie I), l'homme croit Ãªtre libre parce qu'il a conscience de ses dÃ©sirs mais ignore les causes qui les dÃ©terminent. 'Les hommes se croient libres parce qu'ils sont conscients de leurs dÃ©sirs et ignorants des causes qui les dÃ©terminent.' La vraie libertÃ© spinoziste n'est pas le libre arbitre mais la comprÃ©hension des nÃ©cessitÃ©s qui nous gouvernent (la libertÃ© comme nÃ©cessitÃ© comprise)."},
            {"index":5,"type":"vrai-faux","question":"La libertÃ© politique (civile) dÃ©signe la capacitÃ© d'agir sans aucune contrainte ni loi.",
             "answer":"FAUX",
             "correction":"FAUX. La libertÃ© politique (civile ou sociale) n'est pas l'absence de toute loi, mais la capacitÃ© d'agir dans le cadre de lois que l'on s'est donnÃ©es collectivement (Rousseau : 'la libertÃ©, c'est l'obÃ©issance Ã  la loi qu'on s'est prescrite'). Montesquieu (De l'esprit des lois) la dÃ©finit comme le droit de faire tout ce que les lois permettent. Elle implique un Ã©quilibre entre libertÃ© individuelle et contraintes lÃ©gitimes de la vie sociale."},
            {"index":6,"type":"vrai-faux","question":"Quelle est la thÃ¨se de Rousseau sur la libertÃ© dans le Contrat social ?",
             "answer":"Rousseau soutient que la vraie libertÃ© est l'obÃ©issance Ã  la loi que l'on s'est soi-mÃªme prescrite dans le cadre du contrat social ; la libertÃ© civile est supÃ©rieure Ã  la libertÃ© naturelle.",
             "correction":"Dans Du Contrat social (1762), Rousseau distingue libertÃ© naturelle (faire tout ce que l'on veut, limitÃ©e par la force) et libertÃ© civile (obÃ©ir aux lois issues de la volontÃ© gÃ©nÃ©rale). En entrant dans la sociÃ©tÃ© politique, l'homme renonce Ã  sa libertÃ© naturelle mais gagne une libertÃ© civile plus noble : 'l'obÃ©issance Ã  la loi qu'on s'est prescrite est libertÃ©.' Il ajoute la libertÃ© morale : la maÃ®trise de soi par la raison contre les passions."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que l'autonomie au sens kantien ?",
             "options":["A. L'indÃ©pendance Ã©conomique","B. La capacitÃ© Ã  vivre seul sans aide","C. La capacitÃ© Ã  se donner Ã  soi-mÃªme sa propre loi morale par la raison","D. La libertÃ© de faire ce que l'on veut"],
             "answer":"C",
             "correction":"L'autonomie (auto = soi-mÃªme, nomos = loi) chez Kant est la propriÃ©tÃ© qu'a la volontÃ© de se donner Ã  elle-mÃªme sa propre loi morale par la raison pure pratique, indÃ©pendamment de toute inclination sensible ou autoritÃ© extÃ©rieure. Elle s'oppose Ã  l'hÃ©tÃ©ronomie (recevoir sa loi d'une source extÃ©rieure : dÃ©sirs, traditions, autoritÃ©). L'autonomie est le fondement de la dignitÃ© humaine et de la morale kantienne."},
            {"index":8,"type":"vrai-faux","question":"L'existentialisme sartrien affirme que 'l'existence prÃ©cÃ¨de l'essence', ce qui signifie que l'homme se dÃ©finit par ses choix et non par une nature prÃ©alable.",
             "answer":"VRAI",
             "correction":"VRAI. Pour Sartre (L'existentialisme est un humanisme, 1945), contrairement aux objets (dont l'essence â€“ ce qu'ils sont â€“ prÃ©cÃ¨de l'existence), l'homme existe d'abord puis se dÃ©finit par ses choix et ses actes. Il n'a pas de nature fixe Ã  l'avance. Cette absence de nature prÃ©alable est source de libertÃ© absolue mais aussi d'angoisse et de responsabilitÃ© totale."},
        ]
    },
    227: {
        "serie": 4,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 4",
        "description": "La vÃ©ritÃ©",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la conception classique (correspondantiste) de la vÃ©ritÃ© ?",
             "options":["A. Est vrai ce qui est utile","B. Est vrai ce qui est cohÃ©rent avec un systÃ¨me de croyances","C. Est vrai ce qui correspond Ã  la rÃ©alitÃ© (adÃ©quation entre l'intellect et la chose)","D. Est vrai ce qui fait consensus dans une communautÃ©"],
             "answer":"C",
             "correction":"La conception correspondantiste (ou adÃ©quationniste) de la vÃ©ritÃ©, hÃ©ritÃ©e d'Aristote ('est vraie la proposition qui affirme ce qui est'), dÃ©finit la vÃ©ritÃ© comme l'adÃ©quation (adequatio) entre un jugement ou une proposition et la rÃ©alitÃ© qu'il reprÃ©sente. C'est la conception la plus intuitive et la plus rÃ©pandue en philosophie classique."},
            {"index":2,"type":"vrai-faux","question":"Le scepticisme philosophique affirme qu'il est impossible d'atteindre une vÃ©ritÃ© certaine et dÃ©finitive.",
             "answer":"VRAI",
             "correction":"VRAI. Le scepticisme (de Pyrrhon Ã  Sextus Empiricus dans l'AntiquitÃ©) affirme qu'on ne peut avoir de connaissance certaine car nos facultÃ©s sont faillibles, les opinions contradictoires se valent et toute preuve exige une autre preuve (rÃ©gression Ã  l'infini). La rÃ©ponse sceptique est l'Ã©pochÃ¨ (suspension du jugement) et l'ataraxie (tranquillitÃ© de l'Ã¢me). Le scepticisme modÃ©rÃ© (Montaigne) est une posture intellectuelle d'humilitÃ©."},
            {"index":3,"type":"vrai-faux","question":"Quelle distinction Platon Ã©tablit-il entre opinion (doxa) et savoir vrai (Ã©pistÃ©mÃ¨) ?",
             "answer":"Pour Platon, l'opinion porte sur le monde sensible changeant, tandis que le savoir vrai porte sur les IdÃ©es Ã©ternelles et immuables, accessibles par la raison.",
             "correction":"Dans la RÃ©publique (allÃ©gorie de la ligne), Platon distingue : l'opinion (doxa) â€“ connaissance du monde sensible, du visible, instable et incertaine â€“ et la science (Ã©pistÃ©mÃ¨) â€“ connaissance du monde intelligible, des IdÃ©es (Formes) Ã©ternelles et immuables. L'allÃ©gorie de la caverne illustre ce passage de l'opinion (ombres sur la paroi) Ã  la vÃ©ritÃ© (lumiÃ¨re du soleil = l'IdÃ©e du Bien). La philosophie est le chemin qui mÃ¨ne de la doxa Ã  l'Ã©pistÃ©mÃ¨."},
            {"index":4,"type":"qcm","question":"Pour Nietzsche, 'il n'y a pas de faits, seulement des interprÃ©tations' signifie que :",
             "options":["A. La vÃ©ritÃ© scientifique est impossible","B. Toute vÃ©ritÃ© est une perspective, une interprÃ©tation liÃ©e Ã  une volontÃ© de puissance","C. Les faits historiques n'existent pas","D. Seules les opinions comptent"],
             "answer":"B",
             "correction":"Pour Nietzsche (Le Gai Savoir, Fragments posthumes), la vÃ©ritÃ© n'est pas une adÃ©quation au rÃ©el mais le rÃ©sultat d'une interprÃ©tation perpÃ©tuelle du monde. Il n'y a pas de 'fait brut' : toute perception, tout concept est dÃ©jÃ  une interprÃ©tation liÃ©e Ã  une perspective, une volontÃ© de puissance. La 'vÃ©ritÃ©' est une 'armÃ©e mobile de mÃ©taphores' â€“ une fiction utile que nous confondons avec le rÃ©el."},
            {"index":5,"type":"vrai-faux","question":"En sciences, une thÃ©orie est d'autant plus vraie qu'elle est ancienne et bien Ã©tablie.",
             "answer":"FAUX",
             "correction":"FAUX. En philosophie des sciences, Karl Popper (La Logique de la dÃ©couverte scientifique) affirme que la science progresse par rÃ©futations et rÃ©volutions. Une thÃ©orie scientifique n'est jamais dÃ©finitivement vraie : elle est acceptÃ©e provisoirement jusqu'Ã  ce qu'une expÃ©rience la rÃ©fute. Thomas Kuhn parle de 'rÃ©volutions scientifiques' (changements de paradigme). L'anciennetÃ© d'une thÃ©orie ne garantit pas sa vÃ©ritÃ©."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que la mÃ©thode du doute chez Descartes et Ã  quoi mÃ¨ne-t-elle ?",
             "answer":"Le doute mÃ©thodique cartÃ©sien consiste Ã  rejeter tout ce qui peut Ãªtre mis en doute pour trouver une premiÃ¨re vÃ©ritÃ© indubitable : le cogito.",
             "correction":"Descartes (MÃ©ditations mÃ©taphysiques, 1Ã¨re mÃ©ditation) applique un doute mÃ©thodique (provisoire et volontaire, non sceptique dÃ©finitif) : il rejette les certitudes des sens (qui trompent parfois), l'existence du monde extÃ©rieur (peut-Ãªtre un rÃªve), mÃªme les vÃ©ritÃ©s mathÃ©matiques (hypothÃ¨se du malin gÃ©nie). Ce doute hyperbolique le conduit Ã  la seule certitude indubitable : 'Je pense, donc je suis.' Le cogito est le fondement de tout l'Ã©difice de la connaissance cartÃ©sienne."},
            {"index":7,"type":"qcm","question":"Le pragmatisme amÃ©ricain (William James, John Dewey) dÃ©finit la vÃ©ritÃ© comme :",
             "options":["A. L'adÃ©quation entre l'idÃ©e et la rÃ©alitÃ©","B. Ce qui est utile, ce qui 'fonctionne' dans la pratique","C. Ce qui rÃ©siste au doute radical","D. Ce qui est universellement acceptÃ©"],
             "answer":"B",
             "correction":"Le pragmatisme (William James, Pragmatism, 1907 ; John Dewey) dÃ©finit la vÃ©ritÃ© de maniÃ¨re instrumentale : est vrai ce qui est utile, ce qui 'fonctionne' (works), ce qui produit de bons rÃ©sultats dans l'action. Une idÃ©e vraie est une idÃ©e qui 'paie' (pays). Cette conception rompt avec le rÃ©alisme classique en faisant de la vÃ©ritÃ© une valeur pratique plutÃ´t qu'une correspondance thÃ©orique."},
            {"index":8,"type":"vrai-faux","question":"La vÃ©ritÃ© scientifique est dÃ©finitive et ne peut Ãªtre remise en question.",
             "answer":"FAUX",
             "correction":"FAUX. La vÃ©ritÃ© scientifique est provisoire, rÃ©visable et falsifiable (Popper). L'histoire des sciences montre de nombreuses rÃ©visions : la physique newtonienne a Ã©tÃ© relativisÃ©e par Einstein, la cosmologie gÃ©ocentrique remplacÃ©e par l'hÃ©liocentrisme. Bachelard (La Formation de l'esprit scientifique) montre que la science progresse contre les 'obstacles Ã©pistÃ©mologiques' et par rectifications successives."},
        ]
    },
    228: {
        "serie": 5,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 5",
        "description": "La raison et la dÃ©monstration",
        "questions": [
            {"index":1,"type":"qcm","question":"Qu'est-ce qu'un syllogisme aristotÃ©licien ?",
             "options":["A. Une forme de mÃ©ditation","B. Un raisonnement dÃ©ductif composÃ© de deux prÃ©misses et d'une conclusion","C. Un raisonnement par analogie","D. Une preuve empirique"],
             "answer":"B",
             "correction":"Le syllogisme (Aristote, Premiers Analytiques) est une forme de raisonnement dÃ©ductif valide composÃ©e de deux prÃ©misses (une majeure et une mineure) et d'une conclusion. Exemple classique : 'Tout homme est mortel (majeure) ; Socrate est un homme (mineure) ; donc Socrate est mortel (conclusion).' La logique formelle aristotÃ©licienne fonde la tradition de la dÃ©monstration rationnelle occidentale."},
            {"index":2,"type":"vrai-faux","question":"Le rationalisme philosophique affirme que la raison est la seule source fiable de connaissance.",
             "answer":"VRAI",
             "correction":"VRAI. Le rationalisme (Descartes, Spinoza, Leibniz) affirme que la raison â€“ et non les sens ou l'expÃ©rience â€“ est la source principale et la plus fiable de la connaissance. Les idÃ©es innÃ©es ou les vÃ©ritÃ©s de raison (mathÃ©matiques, logique) sont indÃ©pendantes de l'expÃ©rience sensible. Il s'oppose Ã  l'empirisme (Locke, Hume, Berkeley) qui fait de l'expÃ©rience le fondement de la connaissance."},
            {"index":3,"type":"vrai-faux","question":"Quelle est la diffÃ©rence entre raisonnement dÃ©ductif et raisonnement inductif ?",
             "answer":"La dÃ©duction part de principes gÃ©nÃ©raux pour aboutir Ã  une conclusion particuliÃ¨re nÃ©cessaire ; l'induction part de cas particuliers pour Ã©tablir une loi gÃ©nÃ©rale probable.",
             "correction":"La dÃ©duction (logique, mathÃ©matiques) part de prÃ©misses gÃ©nÃ©rales pour tirer une conclusion particuliÃ¨re nÃ©cessairement vraie si les prÃ©misses sont vraies. La conclusion est contenue dans les prÃ©misses (ex : syllogisme). L'induction (sciences empiriques) part d'observations particuliÃ¨res rÃ©pÃ©tÃ©es pour Ã©tablir une loi gÃ©nÃ©rale probable mais jamais certaine (problÃ¨me de l'induction de Hume : aucun nombre d'observations ne peut garantir la vÃ©ritÃ© d'une loi universelle)."},
            {"index":4,"type":"qcm","question":"Qu'est-ce qu'une dÃ©monstration mathÃ©matique ?",
             "options":["A. Une vÃ©rification expÃ©rimentale","B. Une suite de dÃ©ductions Ã  partir d'axiomes et de dÃ©finitions, aboutissant Ã  un thÃ©orÃ¨me","C. Une argumentation rhÃ©torique convaincante","D. Un raisonnement par analogie"],
             "answer":"B",
             "correction":"Une dÃ©monstration mathÃ©matique est une suite de dÃ©ductions logiques rigoureuses, partant d'axiomes (vÃ©ritÃ©s premiÃ¨res acceptÃ©es sans preuve) et de dÃ©finitions, pour Ã©tablir la vÃ©ritÃ© nÃ©cessaire d'un thÃ©orÃ¨me. Elle se distingue de la preuve empirique (qui dÃ©pend de l'expÃ©rience) par son caractÃ¨re formel, universel et nÃ©cessaire. Les mathÃ©matiques sont pour les rationalistes le modÃ¨le de toute connaissance certaine."},
            {"index":5,"type":"vrai-faux","question":"L'empirisme de Hume affirme que toute connaissance vient de l'expÃ©rience sensible et que les idÃ©es sont des copies affaiblies des impressions sensorielles.",
             "answer":"VRAI",
             "correction":"VRAI. Pour Hume (EnquÃªte sur l'entendement humain), toutes nos idÃ©es proviennent d'impressions sensorielles : les idÃ©es sont des 'copies' plus faibles des impressions. Il n'existe pas d'idÃ©es innÃ©es. MÃªme les idÃ©es complexes (comme la causalitÃ©) sont des habitudes mentales issues de l'expÃ©rience rÃ©pÃ©tÃ©e, non des vÃ©ritÃ©s rationnelles nÃ©cessaires. Cet empirisme radical remet en cause la mÃ©taphysique et la raison pure."},
            {"index":6,"type":"vrai-faux","question":"Comment Kant tente-t-il de dÃ©passer l'opposition entre rationalisme et empirisme ?",
             "answer":"Kant synthÃ©tise rationalisme et empirisme en montrant que la connaissance rÃ©sulte de l'application des formes a priori de la sensibilitÃ© et de l'entendement aux donnÃ©es de l'expÃ©rience.",
             "correction":"Kant (Critique de la raison pure, 1781) entreprend une 'rÃ©volution copernicienne' : ce n'est pas notre connaissance qui se rÃ¨gle sur les objets, mais les objets qui se rÃ¨glent sur les structures de notre esprit. Il distingue les formes a priori (espace, temps, catÃ©gories comme la causalitÃ©) qui organisent l'expÃ©rience, et la matiÃ¨re a posteriori (donnÃ©es sensorielles). La connaissance naÃ®t de la synthÃ¨se de l'a priori et de l'expÃ©rience, dÃ©passant l'opposition rationalisme/empirisme."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que le principe de non-contradiction en logique ?",
             "options":["A. Une proposition ne peut pas Ãªtre Ã  la fois vraie et fausse dans le mÃªme sens et au mÃªme moment","B. Toute dÃ©monstration doit Ã©viter les rÃ©pÃ©titions","C. Il ne faut pas contredire les autoritÃ©s","D. Deux thÃ©ories ne peuvent jamais coexister"],
             "answer":"A",
             "correction":"Le principe de non-contradiction (Aristote, MÃ©taphysique) est l'un des principes fondamentaux de la logique : 'Il est impossible qu'une mÃªme chose soit et ne soit pas en mÃªme temps, sous le mÃªme rapport.' Toute contradiction logique est source d'erreur. Ce principe est le fondement de toute dÃ©monstration et de toute pensÃ©e rationnelle cohÃ©rente."},
            {"index":8,"type":"vrai-faux","question":"La raison instrumentale dÃ©signe l'usage de la raison pour dÃ©finir les fins morales et politiques d'une sociÃ©tÃ©.",
             "answer":"FAUX",
             "correction":"FAUX. La raison instrumentale (Horkheimer, Adorno â€“ Dialectique de la Raison) est la raison rÃ©duite Ã  l'efficacitÃ© technique : calculer les meilleurs moyens pour atteindre des fins donnÃ©es, sans questionner ces fins elles-mÃªmes. Elle s'oppose Ã  la raison substantielle (qui juge des fins). La raison instrumentale, dominante dans la modernitÃ©, est critiquÃ©e car elle peut servir n'importe quelle fin, y compris les pires (ex : efficacitÃ© de l'industrie d'extermination nazie)."},
        ]
    },
    229: {
        "serie": 6,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 6",
        "description": "Le langage",
        "questions": [
            {"index":1,"type":"qcm","question":"Pour quoi le langage est-il considÃ©rÃ© comme spÃ©cifique Ã  l'Ãªtre humain ?",
             "options":["A. Les humains sont les seuls Ã  Ã©mettre des sons","B. Le langage humain est articulÃ©, symbolique et permet d'exprimer des pensÃ©es abstraites et de crÃ©er du sens","C. Les animaux ne communiquent pas","D. Le langage humain est innÃ© et gÃ©nÃ©tiquement programmÃ©"],
             "answer":"B",
             "correction":"Ce qui distingue le langage humain des systÃ¨mes de communication animaux (signaux, codes gÃ©nÃ©tiques), c'est son double articulation (Martinet), sa crÃ©ativitÃ© infinie (Chomsky), sa dimension symbolique (le mot n'est pas la chose) et sa capacitÃ© Ã  exprimer des contenus abstraits, des contre-factuels et des rÃ©cits. Aristote dÃ©finit l'homme comme 'animal politique' prÃ©cisÃ©ment parce qu'il a le logos (raison + langage)."},
            {"index":2,"type":"vrai-faux","question":"Selon Saussure, le signe linguistique est composÃ© d'un signifiant (image acoustique) et d'un signifiÃ© (concept).",
             "answer":"VRAI",
             "correction":"VRAI. Ferdinand de Saussure (Cours de linguistique gÃ©nÃ©rale, 1916) dÃ©finit le signe linguistique comme l'union d'un signifiant (image acoustique, la forme sonore ou graphique du mot) et d'un signifiÃ© (concept, la reprÃ©sentation mentale). Ce lien est arbitraire (pas de ressemblance naturelle entre le son 'chien' et l'animal) et diffÃ©rentiel (chaque signe se dÃ©finit par opposition aux autres)."},
            {"index":3,"type":"vrai-faux","question":"En quoi consiste la thÃ¨se de Wittgenstein 'les limites de mon langage signifient les limites de mon monde' ?",
             "answer":"Cette thÃ¨se signifie que notre capacitÃ© Ã  penser et Ã  concevoir le monde est limitÃ©e par les possibilitÃ©s expressives de notre langage.",
             "correction":"Wittgenstein (Tractatus logico-philosophicus, 1921) affirme que le monde ne peut Ãªtre pensÃ© qu'Ã  travers le langage : 'Les limites de mon langage signifient les limites de mon monde.' Ce qui ne peut Ãªtre dit (l'indicible : l'Ã©thique, le mystique) ne peut Ãªtre pensÃ©. Cette thÃ¨se a des implications importantes : notre vision du monde est structurÃ©e par notre langue. Le relativisme linguistique (hypothÃ¨se Sapir-Whorf) prolonge cette idÃ©e : la langue maternelle influence la faÃ§on de percevoir le rÃ©el."},
            {"index":4,"type":"qcm","question":"Qu'est-ce que la performativitÃ© du langage selon Austin ?",
             "options":["A. La capacitÃ© du langage Ã  dÃ©crire le monde","B. La propriÃ©tÃ© de certains Ã©noncÃ©s Ã  accomplir une action en les Ã©nonÃ§ant ('Je vous dÃ©clare mariÃ©s')","C. Le pouvoir rhÃ©torique de convaincre","D. La musicalitÃ© du discours"],
             "answer":"B",
             "correction":"J.L. Austin (Quand dire, c'est faire, 1962) distingue les Ã©noncÃ©s constatifs (qui dÃ©crivent) et les Ã©noncÃ©s performatifs (qui accomplissent une action en Ã©tant prononcÃ©s). Ex : 'Je promets', 'Je vous dÃ©clare mariÃ©s', 'La sÃ©ance est ouverte'. Ces actes de langage ne sont pas vrais ou faux mais 'heureux' ou 'malheureux' selon les conditions de leur Ã©nonciation (contexte, autoritÃ© du locuteur)."},
            {"index":5,"type":"vrai-faux","question":"Le langage est uniquement un instrument de communication neutre qui transmet des informations objectives.",
             "answer":"FAUX",
             "correction":"FAUX. Le langage est bien plus qu'un simple outil de transmission d'informations : il construit le sens, exprime des valeurs et des Ã©motions, structure notre rapport au monde (Wittgenstein), peut manipuler et exercer du pouvoir (rhÃ©torique, propagande), crÃ©e du lien social et identitaire, et peut aussi cacher ou dÃ©former la rÃ©alitÃ© (Orwell, 1984 : la 'novlangue'). Heidegger dit mÃªme que 'le langage est la maison de l'Ãªtre'."},
            {"index":6,"type":"vrai-faux","question":"Quelle est la diffÃ©rence entre langue et parole selon Saussure ?",
             "answer":"La langue est le systÃ¨me collectif de signes partagÃ© par une communautÃ© ; la parole est l'usage individuel et concret de ce systÃ¨me.",
             "correction":"Saussure (Cours de linguistique gÃ©nÃ©rale) distingue : la langue (systÃ¨me abstrait, collectif, social, de signes partagÃ© par tous les membres d'une communautÃ© linguistique, code commun) et la parole (acte individuel, concret, variable, d'utilisation de ce systÃ¨me). La langue est l'objet de la linguistique ; la parole est son actualisation. Chomsky reformulera cette distinction en compÃ©tence/performance linguistique."},
            {"index":7,"type":"qcm","question":"Pour Bergson, le langage est insuffisant pour exprimer la rÃ©alitÃ© profonde de la conscience car :",
             "options":["A. Le langage est trop poÃ©tique","B. Le langage, fait pour l'action et la communication sociale, dÃ©coupe et fige ce qui est en rÃ©alitÃ© fluide et continu (la durÃ©e)","C. La conscience est trop complexe pour aucun systÃ¨me symbolique","D. Bergson prÃ©fÃ¨re le silence"],
             "answer":"B",
             "correction":"Bergson (Essai sur les donnÃ©es immÃ©diates de la conscience) critique le langage qui, adaptÃ© Ã  la vie pratique et Ã  la communication sociale, impose ses dÃ©coupages spatiaux et statiques sur une rÃ©alitÃ© qui est en rÃ©alitÃ© durÃ©e pure, mouvement continu, qualitÃ© irrÃ©ductible Ã  la quantitÃ©. En mettant des mots sur notre vie intÃ©rieure, nous la trahissons en la figeant. La vraie conscience est ineffable (ne peut se dire entiÃ¨rement)."},
            {"index":8,"type":"vrai-faux","question":"L'apprentissage du langage est exclusivement culturel : un enfant sans contact humain dÃ©velopperait spontanÃ©ment son propre langage.",
             "answer":"FAUX",
             "correction":"FAUX. Si la capacitÃ© au langage est biologiquement inscrite (Chomsky : 'grammaire universelle' innÃ©e, dispositif d'acquisition du langage), son acquisition nÃ©cessite impÃ©rativement l'exposition Ã  une langue humaine dans une pÃ©riode critique (avant 12 ans environ). Les cas d'enfants 'sauvages' (Genie, Victor de l'Aveyron) montrent que sans stimulation linguistique, le langage ne se dÃ©veloppe pas spontanÃ©ment ou trÃ¨s incomplÃ¨tement."},
        ]
    },
    230: {
        "serie": 7,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 7",
        "description": "Le travail et la technique",
        "questions": [
            {"index":1,"type":"qcm","question":"Selon Marx, qu'est-ce que l'aliÃ©nation du travail dans le systÃ¨me capitaliste ?",
             "options":["A. Le fait de travailler trop longtemps","B. Le fait que l'ouvrier est sÃ©parÃ© du produit de son travail, de son activitÃ©, de ses semblables et de lui-mÃªme","C. La mÃ©canisation des tÃ¢ches","D. Le salaire insuffisant des ouvriers"],
             "answer":"B",
             "correction":"Marx (Manuscrits de 1844) thÃ©orise l'aliÃ©nation : dans le capitalisme, le travailleur est aliÃ©nÃ© (Ã©tranger Ã  lui-mÃªme) de quatre faÃ§ons : sÃ©parÃ© du produit de son travail (qui lui Ã©chappe), de son activitÃ© laborieuse (contrainte, non Ã©panouissante), de ses semblables (concurrence), et de son Ãªtre gÃ©nÃ©rique (son humanitÃ©). Le travail, qui devrait Ãªtre la rÃ©alisation de l'homme, devient une souffrance."},
            {"index":2,"type":"vrai-faux","question":"Hegel voit dans le travail un moment positif par lequel l'homme se rÃ©alise et transforme la nature en y imprimant sa marque.",
             "answer":"VRAI",
             "correction":"VRAI. Dans la dialectique du maÃ®tre et de l'esclave (PhÃ©nomÃ©nologie de l'Esprit), Hegel montre que c'est l'esclave, qui travaille, qui finit par se libÃ©rer : en transformant la nature par son travail, il y imprime sa subjectivitÃ©, se reconnaÃ®t dans son Å“uvre et dÃ©veloppe sa conscience de soi. Le travail est mÃ©diation entre l'homme et le monde, source de formation (Bildung) et d'Ã©mancipation."},
            {"index":3,"type":"vrai-faux","question":"En quoi la technique est-elle constitutive de l'humanitÃ© ? Illustrez avec un exemple.",
             "answer":"La technique est constitutive de l'humanitÃ© car l'Ãªtre humain est par nature un Ãªtre technicien qui transforme son milieu par des outils ; sans technique, il ne pourrait survivre ni se dÃ©velopper.",
             "correction":"Pour les philosophes comme Bergson (L'Ã‰volution crÃ©atrice) ou Leroi-Gourhan (Le Geste et la Parole), l'homo sapiens est aussi homo faber : l'usage d'outils est coextensif Ã  l'humanitÃ© (silex taillÃ©s il y a 2,5 millions d'annÃ©es). La technique n'est pas un ajout extÃ©rieur mais constitutive de la condition humaine : elle permet de s'adapter Ã  l'environnement, de libÃ©rer du temps (meule, four), de crÃ©er de la culture. Exemple : l'Ã©criture est une technique cognitive qui transforme la mÃ©moire et la pensÃ©e."},
            {"index":4,"type":"qcm","question":"Heidegger critique la technique moderne car elle :",
             "options":["A. Est trop lente et inefficace","B. RÃ©duit tout l'Ã©tant (le monde, les hommes, la nature) Ã  un simple stock de ressources disponibles (le 'Gestell' ou arraisonnement)","C. DÃ©truit les emplois","D. N'est pas assez universelle"],
             "answer":"B",
             "correction":"Heidegger (La Question de la technique, 1953) critique la technique moderne : elle n'est pas neutre mais impose une faÃ§on d'Ãªtre au monde, le 'Gestell' (arraisonnement, dispositif). Elle rÃ©duit tout l'Ã©tant Ã  des 'stocks disponibles' (Bestand) : la nature est un rÃ©servoir d'Ã©nergie, les hommes des ressources humaines. Elle occulte les autres modes de dÃ©voilement de l'Ãªtre (art, poÃ©sie) et constitue le 'danger' suprÃªme."},
            {"index":5,"type":"vrai-faux","question":"Le progrÃ¨s technique garantit automatiquement le progrÃ¨s moral et le bonheur humain.",
             "answer":"FAUX",
             "correction":"FAUX. Le progrÃ¨s technique n'implique pas automatiquement le progrÃ¨s moral ou le bonheur. L'histoire montre que les mÃªmes avancÃ©es techniques peuvent servir des fins opposÃ©es : la chimie produit des mÃ©dicaments et des armes chimiques, l'Ã©nergie nuclÃ©aire chauffe les maisons et dÃ©truit des villes. Jonas (Le Principe responsabilitÃ©) souligne que la technique moderne crÃ©e des risques sans prÃ©cÃ©dent et exige une nouvelle Ã©thique de la responsabilitÃ© envers les gÃ©nÃ©rations futures."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que la division du travail selon Adam Smith et quelles en sont les consÃ©quences ?",
             "answer":"La division du travail est la spÃ©cialisation des travailleurs dans des tÃ¢ches prÃ©cises, accroissant la productivitÃ© mais pouvant aboutir Ã  l'abrutissement du travailleur.",
             "correction":"Adam Smith (La Richesse des nations, 1776) montre que la division du travail (spÃ©cialisation de chaque travailleur dans une tÃ¢che Ã©lÃ©mentaire) est la principale source de la richesse des nations : gain de dextÃ©ritÃ©, Ã©conomie de temps, invention de machines. Mais il reconnaÃ®t aussi ses limites : le travailleur rÃ©pÃ©titif devient 'aussi stupide et ignorant qu'une crÃ©ature humaine peut le devenir.' Marx et Tocqueville amplifieront cette critique de l'abrutissement par la spÃ©cialisation."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que l'homo faber ?",
             "options":["A. L'homme qui parle","B. L'homme qui pense abstraitement","C. L'homme fabricant, dÃ©finit par son usage des outils et des techniques","D. L'homme politique"],
             "answer":"C",
             "correction":"L'homo faber (Bergson, L'Ã‰volution crÃ©atrice ; Henri Bergson) dÃ©signe l'homme en tant qu'Ãªtre fabricant (faber = artisan en latin), qui se distingue des autres animaux par sa capacitÃ© Ã  fabriquer et utiliser des outils. Cette dÃ©finition de l'humain par la technique s'oppose ou complÃ¨te la dÃ©finition traditionnelle de l'homo sapiens (homme qui sait, qui pense) et de l'homo loquens (homme qui parle)."},
            {"index":8,"type":"vrai-faux","question":"L'automatisation et l'intelligence artificielle posent de nouveaux dÃ©fis philosophiques quant Ã  la place du travail humain dans la sociÃ©tÃ©.",
             "answer":"VRAI",
             "correction":"VRAI. L'automatisation croissante (robots industriels) et l'intelligence artificielle menacent de nombreux emplois, y compris qualifiÃ©s, posant des questions philosophiques majeures : le travail est-il constitutif de l'identitÃ© humaine et de la dignitÃ© ? Une sociÃ©tÃ© sans travail est-elle souhaitable ou possible ? Comment redistribuer les richesses crÃ©Ã©es par les machines ? Ces enjeux renouvellent les dÃ©bats philosophiques sur le travail, le loisir et l'humanitÃ©."},
        ]
    },
    231: {
        "serie": 8,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 8",
        "description": "La morale et l'Ã©thique",
        "questions": [
            {"index":1,"type":"qcm","question":"Qu'est-ce que l'impÃ©ratif catÃ©gorique chez Kant ?",
             "options":["A. Une rÃ¨gle morale qui dÃ©pend des circonstances","B. Un commandement inconditionnel de la raison : 'Agis seulement selon la maxime que tu peux vouloir Ã©riger en loi universelle'","C. L'obligation lÃ©gale de respecter les lois de l'Ã‰tat","D. L'intÃ©rÃªt bien compris de chaque individu"],
             "answer":"B",
             "correction":"L'impÃ©ratif catÃ©gorique (Kant, Fondements de la mÃ©taphysique des mÅ“urs, 1785) est la loi morale suprÃªme, inconditionnelle (non hypothÃ©tique) : 'Agis seulement d'aprÃ¨s la maxime grÃ¢ce Ã  laquelle tu peux vouloir en mÃªme temps qu'elle devienne une loi universelle.' Il a plusieurs formulations, dont la formule de l'humanitÃ© : 'Agis de faÃ§on Ã  traiter l'humanitÃ© toujours comme une fin et jamais seulement comme un moyen.'"},
            {"index":2,"type":"vrai-faux","question":"L'utilitarisme (Bentham, Mill) juge la valeur morale d'une action par ses consÃ©quences et l'utilitÃ© qu'elle produit.",
             "answer":"VRAI",
             "correction":"VRAI. L'utilitarisme (Jeremy Bentham, John Stuart Mill) est une Ã©thique consÃ©quentialiste : la valeur morale d'un acte est jugÃ©e par ses consÃ©quences. Le principe d'utilitÃ© (ou 'principe du plus grand bonheur') affirme qu'il faut maximiser le bonheur du plus grand nombre. L'action moralement bonne est celle qui produit le plus de bonheur (ou plaisir) et le moins de souffrance pour le plus grand nombre de personnes."},
            {"index":3,"type":"vrai-faux","question":"Quelle est la diffÃ©rence entre morale et Ã©thique ?",
             "answer":"La morale dÃ©signe l'ensemble des rÃ¨gles et devoirs qui s'imposent Ã  tous ; l'Ã©thique est une rÃ©flexion philosophique sur ce qui constitue une 'bonne vie' et les fondements de l'agir moral.",
             "correction":"La distinction est souvent nuancÃ©e : la morale (du latin mores : mÅ“urs) dÃ©signe traditionnellement les normes, rÃ¨gles et devoirs qui s'imposent Ã  tous (dimension impÃ©rative, devoir). L'Ã©thique (du grec ethos : caractÃ¨re, maniÃ¨re de vivre) est la rÃ©flexion philosophique sur ce qui constitue une 'bonne vie', les vertus, et les fondements de l'agir juste. Pour certains (Ricoeur), l'Ã©thique (visÃ©e du bien) prÃ©cÃ¨de la morale (obligation) ; l'Ã©thique est premiÃ¨re."},
            {"index":4,"type":"qcm","question":"Quelle est la position morale d'Aristote concernant la vertu ?",
             "options":["A. La vertu est une rÃ¨gle divine imposÃ©e de l'extÃ©rieur","B. La vertu est une disposition acquise par l'habitude, un juste milieu entre excÃ¨s et dÃ©faut","C. La vertu n'existe pas, seul l'intÃ©rÃªt personnel compte","D. La vertu est l'obÃ©issance aux lois de la citÃ©"],
             "answer":"B",
             "correction":"Pour Aristote (Ã‰thique Ã  Nicomaque), la vertu (arÃ¨tÃ¨) est une disposition stable du caractÃ¨re acquise par l'habitude et l'Ã©ducation, qui consiste Ã  choisir le juste milieu (mÃ©diÃ©tÃ©) entre deux extrÃªmes dÃ©fectueux. Ex : le courage est le juste milieu entre la lÃ¢chetÃ© et la tÃ©mÃ©ritÃ©. La vie vertueuse mÃ¨ne Ã  l'eudaimonia (bonheur, Ã©panouissement) â€“ fin ultime de l'existence humaine."},
            {"index":5,"type":"vrai-faux","question":"Le relativisme moral affirme qu'il n'existe pas de valeurs morales universelles valables pour tous les Ãªtres humains.",
             "answer":"VRAI",
             "correction":"VRAI (avec nuances). Le relativisme moral affirme que les valeurs morales sont relatives Ã  une culture, une Ã©poque ou un individu, sans qu'aucune ne soit objectivement supÃ©rieure aux autres. Il s'oppose au universalisme moral (Kant, droits de l'homme). Si le relativisme a l'avantage de respecter la diversitÃ© culturelle, il est critiquÃ© car il peut justifier des pratiques comme la torture ou l'esclavage au nom des cultures."},
            {"index":6,"type":"vrai-faux","question":"Qu'est-ce que le dilemme du tramway (trolley problem) et qu'illustre-t-il en Ã©thique ?",
             "answer":"Le dilemme du tramway est une expÃ©rience de pensÃ©e qui oppose l'intuition d'Ã©pargner le plus de vies (utilitarisme) Ã  l'interdiction de se servir d'une personne comme moyen (dÃ©ontologie kantienne).",
             "correction":"Le trolley problem (Foot, Thomson) : un tramway fou fonce vers 5 personnes ; vous pouvez actionner un levier pour le dÃ©vier vers une voie oÃ¹ il tuera 1 personne. Devez-vous agir ? L'utilitarisme dit oui (5 > 1) ; la dÃ©ontologie kantienne dit non (on ne peut pas utiliser quelqu'un comme moyen). Ce dilemme illustre la tension entre Ã©thiques consÃ©quentialistes (rÃ©sultats) et dÃ©ontologiques (rÃ¨gles absolues), et rÃ©vÃ¨le nos intuitions morales souvent contradictoires."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que la 'golden rule' (rÃ¨gle d'or) prÃ©sente dans de nombreuses traditions morales et religieuses ?",
             "options":["A. Le principe de maximiser son propre bonheur","B. 'Ne fais pas Ã  autrui ce que tu ne voudrais pas qu'il te fasse'","C. L'obligation d'obÃ©ir aux lois de l'Ã‰tat","D. Le devoir de chercher la vÃ©ritÃ©"],
             "answer":"B",
             "correction":"La rÃ¨gle d'or ('Ne fais pas Ã  autrui ce que tu ne voudrais pas qu'on te fasse' / 'Fais aux autres ce que tu voudrais qu'on te fasse') est un principe moral prÃ©sent dans pratiquement toutes les traditions Ã©thiques et religieuses (Confucius, Bible, Coran, Kant, etc.). Elle constitue un des fondements les plus universels de l'Ã©thique et anticipe le principe kantien d'universalisabilitÃ©."},
            {"index":8,"type":"vrai-faux","question":"L'Ã©thique du care (Gilligan, Noddings) met l'accent sur les relations, la sollicitude et la responsabilitÃ© envers les personnes vulnÃ©rables.",
             "answer":"VRAI",
             "correction":"VRAI. L'Ã©thique du care (Carol Gilligan, Nel Noddings) est nÃ©e en rÃ©action Ã  la morale kantienne universaliste jugÃ©e trop abstraite et masculine. Elle valorise la sollicitude (care), les relations interpersonnelles, la sensibilitÃ© aux contextes particuliers et la responsabilitÃ© envers les personnes vulnÃ©rables et dÃ©pendantes. Elle met en avant des vertus comme l'empathie, la bienveillance et l'attention aux besoins des autres."},
        ]
    },
    232: {
        "serie": 9,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 9",
        "description": "La politique et l'Ã‰tat",
        "questions": [
            {"index":1,"type":"qcm","question":"Selon Hobbes, pourquoi les hommes acceptent-ils de vivre sous l'autoritÃ© d'un Ã‰tat (LÃ©viathan) ?",
             "options":["A. Par amour du bien commun","B. Pour sortir de l'Ã©tat de nature (guerre de 'tous contre tous') et garantir leur sÃ©curitÃ©","C. Par obligation divine","D. Par obÃ©issance Ã  la tradition"],
             "answer":"B",
             "correction":"Hobbes (LÃ©viathan, 1651) dÃ©crit l'Ã©tat de nature comme une guerre 'de tous contre tous', vie 'solitaire, misÃ©rable, dangereuse, animale et brÃ¨ve'. Pour sortir de cet Ã©tat, les hommes concluent un contrat social : ils cÃ¨dent tous leurs droits naturels Ã  un souverain absolu (LÃ©viathan) en Ã©change de la paix et de la sÃ©curitÃ©. L'Ã‰tat est justifiÃ© par la peur de la mort violente."},
            {"index":2,"type":"vrai-faux","question":"Pour Locke, la lÃ©gitimitÃ© de l'Ã‰tat repose sur le consentement des gouvernÃ©s et la protection des droits naturels (vie, libertÃ©, propriÃ©tÃ©).",
             "answer":"VRAI",
             "correction":"VRAI. John Locke (TraitÃ© du gouvernement civil, 1690) fonde l'Ã‰tat libÃ©ral : les hommes ont des droits naturels inalienables (vie, libertÃ©, propriÃ©tÃ©). Ils consentent Ã  crÃ©er un Ã‰tat pour mieux les protÃ©ger. Si le gouvernement viole ces droits, les citoyens ont le droit de rÃ©sistance et de rÃ©volution. Locke influence profondÃ©ment les rÃ©volutions amÃ©ricaine et franÃ§aise."},
            {"index":3,"type":"vrai-faux","question":"Qu'est-ce que la dÃ©mocratie selon Lincoln et quelles en sont les conditions ?",
             "answer":"La dÃ©mocratie est le gouvernement 'du peuple, par le peuple, pour le peuple'. Elle exige la libertÃ© d'expression, des Ã©lections libres et la protection des droits des citoyens.",
             "correction":"La formule d'Abraham Lincoln (discours de Gettysburg, 1863) : 'le gouvernement du peuple, par le peuple, pour le peuple' synthÃ©tise l'idÃ©al dÃ©mocratique. La dÃ©mocratie implique : la souverainetÃ© populaire (le pouvoir vient du peuple), des Ã©lections libres et pluralistes, la sÃ©paration des pouvoirs (Montesquieu), la garantie des libertÃ©s fondamentales, l'Ã‰tat de droit, et l'Ã©galitÃ© des citoyens devant la loi. Tocqueville (De la dÃ©mocratie en AmÃ©rique) en analyse les promesses et les risques (tyrannie de la majoritÃ©)."},
            {"index":4,"type":"qcm","question":"Qu'est-ce que la sÃ©paration des pouvoirs selon Montesquieu ?",
             "options":["A. La division de l'Ã‰tat en rÃ©gions autonomes","B. La sÃ©paration du pouvoir exÃ©cutif, lÃ©gislatif et judiciaire pour Ã©viter les abus de pouvoir","C. La sÃ©paration de l'Ã‰glise et de l'Ã‰tat","D. La division entre pouvoir central et collectivitÃ©s locales"],
             "answer":"B",
             "correction":"Montesquieu (De l'esprit des lois, 1748) thÃ©orise la sÃ©paration des pouvoirs : pour Ã©viter le despotisme, il faut que le pouvoir lÃ©gislatif (faire les lois), le pouvoir exÃ©cutif (appliquer les lois) et le pouvoir judiciaire (juger selon les lois) soient sÃ©parÃ©s et Ã©quilibrÃ©s. Ce principe est fondateur des dÃ©mocraties libÃ©rales modernes et est inscrit dans les constitutions amÃ©ricaine (1787) et franÃ§aise."},
            {"index":5,"type":"vrai-faux","question":"L'anarchisme philosophique affirme que l'Ã‰tat est une institution nÃ©cessaire pour organiser la sociÃ©tÃ©.",
             "answer":"FAUX",
             "correction":"FAUX. L'anarchisme (Proudhon, Bakounine, Kropotkine) affirme au contraire que l'Ã‰tat est une institution oppressive et illÃ©gitime qui doit Ãªtre supprimÃ©e. Les anarchistes croient que les individus et les communautÃ©s peuvent s'organiser librement et volontairement, sans coercition Ã©tatique. Proudhon est cÃ©lÃ¨bre pour sa formule : 'La propriÃ©tÃ©, c'est le vol !'"},
            {"index":6,"type":"vrai-faux","question":"Quelle distinction Arendt Ã©tablit-elle entre le travail, l'Å“uvre et l'action ?",
             "answer":"Arendt distingue le travail (activitÃ© biologique de survie), l'Å“uvre (fabrication d'objets durables) et l'action (dimension politique de la libertÃ© et de l'espace public).",
             "correction":"Hannah Arendt (Condition de l'homme moderne, 1958) distingue trois activitÃ©s fondamentales : le travail (labor) â€“ activitÃ© biologique de reproduction de la vie, cyclique, laisse aucune trace durable ; l'Å“uvre (work) â€“ fabrication d'objets durables qui constituent un monde humain commun ; l'action (action) â€“ activitÃ© proprement politique, dans l'espace public, par laquelle les hommes se rÃ©vÃ¨lent comme Ãªtres singuliers et exercent leur libertÃ©. Pour Arendt, la vie active est dominÃ©e par le travail dans la modernitÃ©, au dÃ©triment de l'action politique."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que le totalitarisme selon Hannah Arendt ?",
             "options":["A. Un rÃ©gime autoritaire qui contrÃ´le l'armÃ©e et la police","B. Un rÃ©gime politique inÃ©dit qui vise Ã  transformer radicalement la sociÃ©tÃ© et l'Ãªtre humain par la terreur et l'idÃ©ologie","C. Un rÃ©gime de parti unique sans idÃ©ologie","D. La dictature d'un seul homme"],
             "answer":"B",
             "correction":"Hannah Arendt (Les Origines du totalitarisme, 1951) analyse le totalitarisme (nazisme, stalinisme) comme un rÃ©gime politique radicalement nouveau : il ne cherche pas seulement Ã  contrÃ´ler les comportements mais Ã  transformer la nature humaine elle-mÃªme par l'idÃ©ologie (race, classe), la terreur gÃ©nÃ©ralisÃ©e, les camps de concentration et la dÃ©solation (destruction des liens sociaux et de la dignitÃ© humaine)."},
            {"index":8,"type":"vrai-faux","question":"La dÃ©sobÃ©issance civile est une forme de rÃ©sistance non-violente Ã  des lois jugÃ©es injustes, thÃ©orisÃ©e notamment par Thoreau et Gandhi.",
             "answer":"VRAI",
             "correction":"VRAI. La dÃ©sobÃ©issance civile (Thoreau, La RÃ©sistance au gouvernement civil, 1849) est le refus dÃ©libÃ©rÃ©, public et non-violent d'obÃ©ir Ã  une loi jugÃ©e injuste, acceptant d'en subir les consÃ©quences lÃ©gales. Gandhi l'a thÃ©orisÃ©e et pratiquÃ©e (satyagraha) pour l'indÃ©pendance de l'Inde. Martin Luther King l'a utilisÃ©e dans la lutte pour les droits civiques. Elle pose la question philosophique : jusqu'oÃ¹ est-il lÃ©gitime de dÃ©sobÃ©ir aux lois ?"},
        ]
    },
    233: {
        "serie": 10,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 10",
        "description": "Le bonheur",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle Ã©cole philosophique de l'AntiquitÃ© considÃ¨re que le plaisir est le souverain bien et le but de la vie ?",
             "options":["A. Le stoÃ¯cisme","B. L'Ã©picurisme","C. Le cynisme","D. Le platonisme"],
             "answer":"B",
             "correction":"L'Ã©picurisme (Ã‰picure, IVe-IIIe s. av. J.-C.) considÃ¨re le plaisir (hÃ¨donÃ¨) comme le souverain bien et le but de la vie. Mais Ã‰picure distingue les plaisirs : les plaisirs kinÃ©tatiques (en mouvement, du corps) et les plaisirs catastÃ©matiques (stables, de l'Ã¢me) â€“ l'ataraxie (absence de trouble de l'Ã¢me) et l'aponie (absence de douleur du corps). La sagesse Ã©picurienne consiste Ã  rechercher les plaisirs stables et simples, Ã  amitiÃ©, Ã  philosophie."},
            {"index":2,"type":"vrai-faux","question":"Pour Aristote, le bonheur (eudaimonia) n'est pas un Ã©tat passif mais une activitÃ© de l'Ã¢me conforme Ã  la vertu.",
             "answer":"VRAI",
             "correction":"VRAI. Pour Aristote (Ã‰thique Ã  Nicomaque), l'eudaimonia (bonheur, Ã©panouissement, 'bonne vie') est la fin ultime de toute action humaine. Ce n'est pas un Ã©tat passif (comme le plaisir) mais une activitÃ© : 'l'exercice actif de l'Ã¢me en accord avec la vertu.' Le bonheur aristotiÃ©licien implique l'actualisation de toutes les potentialitÃ©s humaines (vie contemplative, amitiÃ©, vie politique, vertus)."},
            {"index":3,"type":"vrai-faux","question":"Quelle est la critique stoÃ¯cienne de la poursuite du bonheur par les biens extÃ©rieurs ?",
             "answer":"Les stoÃ¯ciens critiquent la dÃ©pendance aux biens extÃ©rieurs (richesse, santÃ©, rÃ©putation) comme source de bonheur, car ces biens ne dÃ©pendent pas de nous ; le vrai bonheur rÃ©side dans la vertu et la maÃ®trise de soi.",
             "correction":"Les stoÃ¯ciens (Ã‰pictÃ¨te, Marc AurÃ¨le, SÃ©nÃ¨que) distinguent ce qui dÃ©pend de nous (eph' hÃ¨min : nos jugements, dÃ©sirs, impulsions) et ce qui n'en dÃ©pend pas (santÃ©, richesse, rÃ©putation, mort des proches). Le bonheur rÃ©side uniquement dans ce qui dÃ©pend de nous : la vertu, la sagesse, la maÃ®trise de nos reprÃ©sentations. Chercher le bonheur dans les biens extÃ©rieurs est une erreur qui gÃ©nÃ¨re des passions (peur, dÃ©sir, colÃ¨re) et la servitude."},
            {"index":4,"type":"qcm","question":"Pascal affirme que 'tous les hommes recherchent d'Ãªtre heureux' mais que le bonheur vÃ©ritable est :",
             "options":["A. Dans la philosophie","B. Dans les plaisirs du corps","C. Inaccessible sans Dieu car l'homme porte en lui un 'gouffre infini' que seul Dieu peut combler","D. Dans la richesse matÃ©rielle"],
             "answer":"C",
             "correction":"Pascal (PensÃ©es) affirme que 'tous les hommes recherchent d'Ãªtre heureux sans exception.' Mais le bonheur terrestre est impossible : l'homme est 'un roseau pensant', un Ãªtre de misÃ¨re et de grandeur, portant en lui un 'abÃ®me infini' que rien de fini ne peut combler. Le divertissement (travail, jeux, guerres) est une fuite de cette misÃ¨re. Seul Dieu peut combler cet abÃ®me. Pascal est donc un 'apologiste' du christianisme par le pari."},
            {"index":5,"type":"vrai-faux","question":"Le bonheur est un Ã©tat qui peut Ãªtre dÃ©fini de faÃ§on universelle et objectivement valable pour tous les Ãªtres humains.",
             "answer":"FAUX",
             "correction":"FAUX. Le bonheur est une notion subjective, relative aux individus et aux cultures. Ce qui rend heureux varie considÃ©rablement d'une personne Ã  l'autre et d'une Ã©poque Ã  l'autre. La philosophie elle-mÃªme propose des conceptions radicalement diffÃ©rentes du bonheur (plaisir chez Ã‰picure, vertu chez Aristote, sagesse chez les stoÃ¯ciens, absence de souffrance chez Schopenhauer). Kant distingue bonheur (incertain, subjectif) et dignitÃ© morale (certaine, universelle)."},
            {"index":6,"type":"vrai-faux","question":"Schopenhauer pense-t-il que le bonheur est accessible Ã  l'homme ? Pourquoi ?",
             "answer":"Pour Schopenhauer, le bonheur durable est inaccessible car la vie est dominÃ©e par une VolontÃ© aveugle et insatiable qui gÃ©nÃ¨re souffrance perpÃ©tuelle. La sagesse consiste Ã  nier la VolontÃ©.",
             "correction":"Arthur Schopenhauer (Le Monde comme volontÃ© et comme reprÃ©sentation) est profondÃ©ment pessimiste : l'essence de l'existence est une VolontÃ© (Wille) aveugle, insatiable, qui nous domine. Le dÃ©sir satisfait ne procure qu'un bref soulagement avant qu'un nouveau dÃ©sir surgisse. La vie oscille entre la douleur du manque et l'ennui de la satisfaction. Le bonheur durable est une illusion. La sagesse consiste Ã  nier la VolontÃ© : par l'art (contemplation esthÃ©tique), la compassion (pitiÃ©) et l'ascÃ¨se."},
            {"index":7,"type":"qcm","question":"Selon Kant, peut-on faire du bonheur le fondement de la morale ?",
             "options":["A. Oui, car le bonheur est le but de tout Ãªtre raisonnable","B. Non, car le bonheur est subjectif et empirique, tandis que la morale exige un principe universel et rationnel","C. Oui, selon l'utilitarisme qu'il approuve","D. Non, car Kant pense que le bonheur n'existe pas"],
             "answer":"B",
             "correction":"Kant refuse de fonder la morale sur le bonheur : le bonheur est empirique (variable selon les individus et les cultures), subjectif et conditionnel. Il ne peut fournir un principe moral universel et nÃ©cessaire. La morale kantienne est dÃ©ontologique : elle repose sur l'impÃ©ratif catÃ©gorique, loi universelle de la raison pure pratique. Kant admet cependant que le 'souverain bien' combine vertu et bonheur, mais la vertu (dignitÃ© d'Ãªtre heureux) est premiÃ¨re."},
            {"index":8,"type":"vrai-faux","question":"Le bouddhisme propose, comme voie vers la cessation de la souffrance, le dÃ©tachement des dÃ©sirs et l'atteinte du Nirvana.",
             "answer":"VRAI",
             "correction":"VRAI. Le bouddhisme (Bouddha, Ve s. av. J.-C.) enseigne les Quatre Nobles VÃ©ritÃ©s : 1) l'existence est souffrance (dukkha) ; 2) la souffrance a une cause (le dÃ©sir, l'attachement - tanha) ; 3) la cessation de la souffrance est possible ; 4) il existe un chemin (Noble Octuple Sentier) vers cette cessation. Le Nirvana est l'extinction des dÃ©sirs et l'affranchissement du cycle des renaissances (samsara). Cette vision rejoint Schopenhauer dans le diagnostic mais propose une voie de libÃ©ration."},
        ]
    },
}

# ============================================================
# Themes for remaining Philosophy quizzes (234-272)
# ============================================================
philo_remaining = [
    (234, 11, "La justice et le droit"),
    (235, 12, "La nature humaine"),
    (236, 13, "L'art et le beau"),
    (237, 14, "La religion et la foi"),
    (238, 15, "Le temps et l'histoire"),
    (239, 16, "Autrui et la reconnaissance"),
    (240, 17, "La perception et les sens"),
    (241, 18, "La science et l'Ã©pistÃ©mologie"),
    (242, 19, "La culture et la nature"),
    (243, 20, "L'Ã©motion et la passion"),
    (244, 21, "La mort et l'existence"),
    (245, 22, "Le dÃ©sir"),
    (246, 23, "L'amour et l'amitiÃ©"),
    (247, 24, "La mÃ©moire et l'identitÃ©"),
    (248, 25, "La sociÃ©tÃ© et le contrat social"),
    (249, 26, "L'Ã©ducation et la formation"),
    (250, 27, "La phÃ©nomÃ©nologie"),
    (251, 28, "Le matÃ©rialisme et l'idÃ©alisme"),
    (252, 29, "La mÃ©taphysique : Ãªtre et existence"),
    (253, 30, "L'Ã©thique environnementale"),
    (254, 31, "La philosophie politique contemporaine"),
    (255, 32, "Le stoÃ¯cisme et le cynisme"),
    (256, 33, "La philosophie de Platon"),
    (257, 34, "La philosophie d'Aristote"),
    (258, 35, "La philosophie mÃ©diÃ©vale"),
    (259, 36, "La philosophie des LumiÃ¨res"),
    (260, 37, "La philosophie de Hegel"),
    (261, 38, "La philosophie de Marx"),
    (262, 39, "La philosophie existentialiste"),
    (263, 40, "La philosophie analytique"),
    (264, 41, "La philosophie du langage"),
    (265, 42, "La philosophie de l'esprit"),
    (266, 43, "La philosophie morale appliquÃ©e"),
    (267, 44, "La bioÃ©thique"),
    (268, 45, "La philosophie des droits de l'homme"),
    (269, 46, "La philosophie de la connaissance (Ã©pistÃ©mologie)"),
    (270, 47, "La philosophie orientale"),
    (271, 48, "La philosophie et la psychologie"),
    (272, 49, "RÃ©vision gÃ©nÃ©rale Philosophie 1Ã¨re"),
]

def make_generic_philo_quiz(file_id, serie, description):
    """Generate 8 philosophy questions with real content for a given theme."""
    theme_content = {
        "La justice et le droit": [
            ("qcm","Selon Rawls, les principes de justice doivent Ãªtre choisis derriÃ¨re un 'voile d'ignorance'. Que signifie cette expression ?",
             ["A. Choisir les lois sans regarder les autres","B. Choisir les principes de justice sans savoir quelle place on occupera dans la sociÃ©tÃ©","C. Ignorer les lois injustes","D. Voter Ã  bulletin secret"],
             "B","Le 'voile d'ignorance' (John Rawls, ThÃ©orie de la justice, 1971) est un dispositif de pensÃ©e : pour choisir des principes justes, imaginons que nous ne sachions pas quelle position nous occuperons dans la sociÃ©tÃ© (riche/pauvre, homme/femme, etc.). DerriÃ¨re ce voile, des personnes rationnelles choisiraient des principes garantissant les libertÃ©s fondamentales et profitant aux plus dÃ©favorisÃ©s (principe de diffÃ©rence)."),
            ("vrai-faux","La justice distributive concerne la rÃ©partition Ã©quitable des biens et des charges dans une sociÃ©tÃ©.",
             None,"VRAI","VRAI. La justice distributive (Aristote, Politique) traite de la distribution Ã©quitable des biens, des ressources, des droits et des charges dans une sociÃ©tÃ©. Elle s'oppose Ã  la justice commutative (Ã©galitÃ© dans les Ã©changes) et Ã  la justice corrective (rÃ©paration des torts). Les thÃ©ories modernes de la justice (Rawls, Nozick, Walzer) dÃ©battent des principes de cette distribution."),
            ("texte","Quelle diffÃ©rence y a-t-il entre lÃ©galitÃ© et lÃ©gitimitÃ© ?",
             None,"La lÃ©galitÃ© renvoie Ã  la conformitÃ© Ã  la loi positive ; la lÃ©gitimitÃ© renvoie Ã  la justification morale et Ã  la reconnaissance du pouvoir par ceux qui y sont soumis.",
             "La lÃ©galitÃ© est la conformitÃ© aux rÃ¨gles juridiques positives en vigueur (ce qui est conforme Ã  la loi). La lÃ©gitimitÃ© est la justification morale, politique ou philosophique d'un pouvoir ou d'une rÃ¨gle (ce qui est juste, ce qui mÃ©rite d'Ãªtre obÃ©i). Un rÃ©gime peut Ãªtre lÃ©gal (respecter ses propres lois) sans Ãªtre lÃ©gitime (rÃ©gimes totalitaires). La rÃ©sistance civile et la dÃ©sobÃ©issance civile s'appuient sur la distinction lÃ©galitÃ©/lÃ©gitimitÃ©."),
            ("qcm","Qu'est-ce que le droit naturel ?",
             ["A. Le droit des animaux dans la nature","B. Un ensemble de droits fondamentaux inhÃ©rents Ã  la nature humaine, antÃ©rieurs et supÃ©rieurs aux lois positives","C. Les lois tirÃ©es de l'observation de la nature","D. Le droit international de l'environnement"],
             "B","Le droit naturel (CicÃ©ron, Grotius, Locke) est un ensemble de droits ou de principes moraux universels, fondÃ©s sur la nature humaine ou la raison, antÃ©rieurs et supÃ©rieurs aux lois positives (Ã©tablies par les sociÃ©tÃ©s). Il fonde les droits de l'homme (DÃ©claration de 1789 : droits 'naturels, inaliÃ©nables et sacrÃ©s'). Il s'oppose au positivisme juridique (Kelsen) qui ne reconnaÃ®t que les lois positives effectivement Ã©dictÃ©es."),
            ("vrai-faux","Pour Thrasymachus (dans la RÃ©publique de Platon), la justice est l'avantage du plus fort.",
             None,"VRAI","VRAI. Thrasymachus (interlocuteur de Socrate dans la RÃ©publique) soutient que 'la justice n'est rien d'autre que l'avantage du plus fort' : ceux qui dÃ©tiennent le pouvoir Ã©dictent des lois qui servent leurs intÃ©rÃªts et appellent cela 'justice'. C'est une position cynique que Socrate/Platon rÃ©futent longuement. Nietzsche reformulera une idÃ©e similaire avec la critique de la 'morale des esclaves'."),
            ("texte","Qu'est-ce que la peine dans le droit pÃ©nal et quels en sont les fondements philosophiques ?",
             None,"La peine a plusieurs fondements philosophiques : rÃ©tribution (punir le mal en soi), dissuasion (Ã©viter les crimes futurs), rÃ©habilitation (rÃ©insertion du condamnÃ©) et protection sociale.",
             "La philosophie de la peine distingue plusieurs fondements : la rÃ©tribution (Kant : punir le criminel est un impÃ©ratif de justice, il le mÃ©rite) ; la dissuasion/prÃ©vention gÃ©nÃ©rale (Bentham : la peine doit dissuader les crimes futurs, calculer la douleur) ; la rÃ©habilitation (rÃ©Ã©ducation, rÃ©insertion du condamnÃ©) ; et la protection sociale (isoler les individus dangereux). Ces fondements conduisent Ã  des politiques pÃ©nales trÃ¨s diffÃ©rentes et alimentent les dÃ©bats sur la prison, la peine de mort et la rÃ©cidive."),
            ("qcm","Quelle position philosophique Nozick dÃ©fend-il contre Rawls concernant la justice ?",
             ["A. La justice exige une redistribution Ã©galitaire des richesses","B. Toute distribution issue d'Ã©changes libres et volontaires est juste, quelle que soit son inÃ©galitÃ©","C. La justice est la propriÃ©tÃ© du plus fort","D. Il n'existe pas de principes objectifs de justice"],
             "B","Robert Nozick (Anarchie, Ã‰tat et Utopie, 1974) dÃ©fend le libertarianisme : toute distribution issue d'acquisitions et d'Ã©changes librement consentis est juste, mÃªme si elle produit de grandes inÃ©galitÃ©s. Il s'oppose Ã  Rawls : l'Ã‰tat minimal est le seul Ã‰tat juste ; tout Ã‰tat plus grand viole les droits individuels en redistribuant les richesses ('la taxation, c'est le travail forcÃ©')."),
            ("vrai-faux","La DÃ©claration universelle des droits de l'homme (1948) reconnaÃ®t des droits universels valables pour tous les Ãªtres humains.",
             None,"VRAI","VRAI. La DÃ©claration universelle des droits de l'homme, adoptÃ©e par l'ONU le 10 dÃ©cembre 1948, proclame 30 droits universels, inaliÃ©nables et indivisibles pour tous les Ãªtres humains 'sans distinction aucune' (race, sexe, religion, etc.). Elle constitue le fondement philosophique du droit international des droits de l'homme, bien que sa valeur contraignante soit limitÃ©e."),
        ],
    }

    # Default questions for themes without specific content
    generic = [
        ("qcm", f"Quelle approche philosophique est au cÅ“ur de l'Ã©tude de '{description}' ?",
         ["A. Une approche empirique basÃ©e sur l'expÃ©rience", "B. Une approche rationnelle et critique visant Ã  clarifier les concepts fondamentaux", "C. Une approche purement historique", "D. Une approche scientifique expÃ©rimentale"],
         "B", f"La philosophie aborde le thÃ¨me de '{description}' par une dÃ©marche rationnelle et critique : elle interroge les concepts fondamentaux, examine les arguments, identifie les prÃ©supposÃ©s et cherche des principes universels, distinguant ainsi l'approche philosophique des approches scientifiques ou historiques."),
        ("vrai-faux", f"Le questionnement philosophique sur '{description}' remonte Ã  l'AntiquitÃ© grecque.",
         None, "VRAI", f"VRAI. Les grandes questions philosophiques liÃ©es Ã  '{description}' ont Ã©tÃ© posÃ©es dÃ¨s l'AntiquitÃ© grecque (Socrate, Platon, Aristote) et continuent d'Ãªtre dÃ©battues jusqu'Ã  nos jours, montrant la pÃ©rennitÃ© et la richesse de la rÃ©flexion philosophique Ã  travers les Ã¢ges."),
        ("texte", f"En quoi consiste la dÃ©marche philosophique appliquÃ©e Ã  '{description}' ?",
         None, f"La dÃ©marche philosophique consiste Ã  dÃ©finir les concepts, Ã  examiner les arguments, Ã  identifier les contradictions et Ã  construire une rÃ©flexion rigoureuse et autonome.",
         f"Philosopher sur '{description}' consiste Ã  : 1) dÃ©finir prÃ©cisÃ©ment les concepts impliquÃ©s (qu'est-ce que cela signifie vraiment ?) ; 2) identifier et examiner les diffÃ©rentes positions possibles (thÃ¨ses et antithÃ¨ses) ; 3) Ã©valuer les arguments par la logique et la raison ; 4) dÃ©passer les opinions communes (doxa) pour atteindre une rÃ©flexion fondÃ©e. Cette dÃ©marche dialectique (Hegel) permet de construire une pensÃ©e autonome et rigoureuse."),
        ("qcm", "Quel philosophe de l'AntiquitÃ© a dit 'Je sais que je ne sais rien' pour exprimer la conscience de ses propres limites ?",
         ["A. Platon", "B. Aristote", "C. Socrate", "D. Ã‰picure"],
         "C", "Cette formule est attribuÃ©e Ã  Socrate (bien qu'elle soit une reformulation tardive). Elle exprime l'humilitÃ© intellectuelle fondamentale (la 'docte ignorance'). Pour Socrate, cette conscience de son ignorance le distinguait de ceux qui croyaient savoir sans vraiment savoir : la sagesse commence par la reconnaissance de ses limites. C'est le point de dÃ©part de toute dÃ©marche philosophique authentique."),
        ("vrai-faux", "La philosophie peut apporter des certitudes absolues sur toutes les questions qu'elle pose.",
         None, "FAUX", "FAUX. La philosophie n'a pas vocation Ã  apporter des rÃ©ponses dÃ©finitives et absolues sur l'ensemble des questions qu'elle pose. Sa valeur rÃ©side davantage dans la qualitÃ© du questionnement, la rigueur de l'argumentation et la clarification des concepts que dans la production de dogmes. Bertrand Russell disait que la valeur de la philosophie est dans l'incertitude qu'elle maintient."),
        ("texte", f"Comment la notion de '{description.split()[0].lower() if description.split() else 'concept'}' peut-elle Ãªtre rapprochÃ©e de la condition humaine ?",
         None, "Cette notion touche Ã  des aspects essentiels de la condition humaine : la finitude, la libertÃ©, la recherche de sens et le rapport Ã  autrui.",
         f"Les grands thÃ¨mes philosophiques, dont '{description}', Ã©clairent la condition humaine dans ses dimensions fondamentales : la finitude et la mortalitÃ©, la libertÃ© et la responsabilitÃ©, le rapport Ã  l'autre et Ã  la sociÃ©tÃ©, la recherche de sens et de bonheur. La philosophie nous aide Ã  nous orienter dans l'existence en nous dotant d'outils conceptuels pour penser notre situation et nos choix."),
        ("qcm", "Qu'est-ce que l'hermÃ©neutique en philosophie ?",
         ["A. L'art de la rhÃ©torique et de la persuasion", "B. La thÃ©orie et la mÃ©thode d'interprÃ©tation des textes et des discours", "C. L'Ã©tude des langues mortes", "D. La logique formelle"],
         "B", "L'hermÃ©neutique (de HermÃ¨s, messager des dieux) est l'art et la thÃ©orie de l'interprÃ©tation, notamment des textes (bibliques initialement, puis littÃ©raires, historiques, philosophiques). Les principaux thÃ©oriciens sont Schleiermacher, Dilthey, Heidegger et Gadamer (VÃ©ritÃ© et mÃ©thode). L'hermÃ©neutique soulÃ¨ve la question du cercle hermÃ©neutique : comprendre le tout par les parties et les parties par le tout."),
        ("vrai-faux", "La philosophie a pour seul but la contemplation dÃ©sintÃ©ressÃ©e de la vÃ©ritÃ©, sans application pratique.",
         None, "FAUX", "FAUX. Si la tradition contemplative (Aristote : la vie thÃ©orÃ©tique comme vie la plus haute) valorise la connaissance pour elle-mÃªme, de nombreux courants philosophiques insistent sur la dimension pratique de la philosophie : Marx ('les philosophes n'ont fait qu'interprÃ©ter le monde ; il s'agit maintenant de le transformer'), le pragmatisme amÃ©ricain, l'existentialisme sartrien. La philosophie Ã©claire l'action morale, politique et personnelle."),
    ]

    content = theme_content.get(description, generic)
    return content


def write_quiz_files():
    print("Generating Philosophie quizzes 224-272...")
    count = 0

    # Write the fully defined quizzes (224-233)
    for file_id, qdata in philo_data.items():
        title = qdata["title"]
        questions = qdata["questions"]
        subject = "Philosophie"
        quiz_obj = make_quiz(file_id, title, subject, "1ere", questions)
        answers_obj = make_answers(file_id, title, subject, "1ere", questions)

        dump_json_file(os.path.join(PHILO_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(PHILO_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        dump_json_file(os.path.join(OUTPUT_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(OUTPUT_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        dump_json_file(os.path.join(RUNTIME_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(RUNTIME_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        count += 1
        print(f"  âœ“ {file_id}.json [{qdata['serie']}/49] - {qdata['description']}")

    # Write remaining quizzes (234-272)
    for (file_id, serie, description) in philo_remaining:
        title = f"Quiz Diagnostic 1ere Philosophie - Serie {serie}"
        raw_questions = make_generic_philo_quiz(file_id, serie, description)
        subject = "Philosophie"

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

        dump_json_file(os.path.join(PHILO_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(PHILO_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        dump_json_file(os.path.join(OUTPUT_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(OUTPUT_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        dump_json_file(os.path.join(RUNTIME_QUIZ_DIR, f"{file_id}.json"), quiz_obj)
        dump_json_file(os.path.join(RUNTIME_ANSWERS_DIR, f"{file_id}.json"), answers_obj)
        count += 1
        print(f"  âœ“ {file_id}.json [{serie}/49] - {description}")

    print(f"\nâœ… Philosophie: {count} quiz files generated (+ {count} answers = {count*6} total files)")


if __name__ == "__main__":
    write_quiz_files()

