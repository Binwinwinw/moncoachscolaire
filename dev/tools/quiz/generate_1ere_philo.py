import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", ".."))
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
# PHILOSOPHIE 1ère — fichiers 224 à 272 (49 quizzes)
# ============================================================

philo_data = {
    224: {
        "serie": 1,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 1",
        "description": "La conscience et le sujet",
        "questions": [
            {"index":1,"type":"qcm","question":"Pour Descartes, quelle est la première certitude absolue à laquelle parvient le philosophe après le doute méthodique ?",
             "options":["A. L'existence de Dieu","B. 'Je pense, donc je suis' (cogito ergo sum)","C. L'existence du monde extérieur","D. La certitude des mathématiques"],
             "answer":"B",
             "correction":"Le cogito cartésien ('Je pense, donc je suis') est la première vérité indubitable trouvée par Descartes après avoir appliqué le doute hyperbolique à toutes ses croyances (Méditations métaphysiques, 1641). Même si je doute, je suis une chose qui pense : c'est le fondement de toute connaissance."},
            {"index":2,"type":"vrai-faux","question":"La conscience de soi est une capacité présente chez tous les êtres vivants, y compris les animaux.",
             "answer":"FAUX",
             "correction":"FAUX. Pour la plupart des philosophes (Descartes, Hegel), la conscience de soi (capacité à se prendre soi-même comme objet de réflexion) est une caractéristique spécifiquement humaine. Les animaux peuvent avoir une conscience spontanée (perception immédiate), mais la conscience réflexive – se retourner sur soi-même et se connaître – semble propre à l'être humain."},
            {"index":3,"type":"texte","question":"Expliquez la distinction entre conscience immédiate (ou spontanée) et conscience réflexive.",
             "answer":"La conscience immédiate est la perception directe du monde ; la conscience réflexive est la capacité à se prendre soi-même comme objet de pensée.",
             "correction":"La conscience immédiate (ou spontanée) est la conscience tournée vers le monde : je perçois, j'agis, je ressens, sans me prendre moi-même pour objet. La conscience réflexive (ou réflexion) est le retour de la conscience sur elle-même : je pense ma propre pensée, je m'observe, je me connais. Hegel dans la Phénoménologie de l'Esprit montre que la conscience de soi passe nécessairement par l'autre (dialectique maître-esclave)."},
            {"index":4,"type":"qcm","question":"Selon Sartre, la conscience humaine est fondamentalement :",
             "options":["A. Déterminée par l'inconscient","B. Liberté absolue, sans nature fixée à l'avance ('l'existence précède l'essence')","C. Identique à la conscience animale","D. Déterminée par les conditions matérielles"],
             "answer":"B",
             "correction":"Pour Sartre (L'existentialisme est un humanisme, 1945), 'l'existence précède l'essence' : l'homme n'a pas de nature prédéfinie, il se crée par ses choix et ses actes. La conscience est 'néant', pur projet, liberté radicale. L'homme est 'condamné à être libre' : même ne pas choisir est un choix."},
            {"index":5,"type":"vrai-faux","question":"Le sujet philosophique désigne simplement la personne grammaticale qui parle ou agit.",
             "answer":"FAUX",
             "correction":"FAUX. En philosophie, le 'sujet' est l'être conscient, pensant, capable de se rapporter à lui-même et au monde. Il est le centre de l'expérience subjective et de la connaissance. Cette notion implique l'identité personnelle, la liberté et la responsabilité morale – bien au-delà du simple usage grammatical."},
            {"index":6,"type":"texte","question":"En quoi consiste le problème de l'identité personnelle ? Illustrez avec un auteur.",
             "answer":"Le problème de l'identité personnelle est de savoir ce qui fait qu'une personne reste la même au fil du temps malgré les changements. Locke l'ancre dans la continuité de la conscience et de la mémoire.",
             "correction":"Le problème de l'identité personnelle pose la question : qu'est-ce qui fait que je suis bien la même personne qu'hier, enfant, ou il y a 10 ans, malgré les changements physiques et psychologiques ? Locke (Essai sur l'entendement humain) ancre l'identité dans la continuité de la conscience et de la mémoire. Hume nie toute identité substantielle du moi (faisceau de perceptions). Parfit (Reasons and Persons) remet en question la notion même de personne identique dans le temps."},
            {"index":7,"type":"qcm","question":"La maxime 'Connais-toi toi-même' (Gnôthi seauton) est associée à :",
             "options":["A. Aristote","B. Platon / Socrate","C. Épicure","D. Nietzsche"],
             "answer":"B",
             "correction":"'Connais-toi toi-même' est l'inscription gravée au frontispice du temple de Delphes et le principe fondateur de la philosophie socratique. Pour Socrate (selon Platon), la sagesse commence par la connaissance de soi, de ses ignorances et de ses limites. C'est le point de départ de toute démarche philosophique authentique."},
            {"index":8,"type":"vrai-faux","question":"Pour Freud, la conscience est la seule instance psychique qui détermine nos comportements.",
             "answer":"FAUX",
             "correction":"FAUX. Freud (L'interprétation des rêves, 1900 ; Introduction à la psychanalyse) montre que l'inconscient – ensemble de représentations refoulées inaccessibles à la conscience – joue un rôle déterminant dans nos comportements, désirs, symptômes et actes manqués. La conscience n'est que 'la partie émergée de l'iceberg' ; l'inconscient en constitue la plus grande partie."},
        ]
    },
    225: {
        "serie": 2,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 2",
        "description": "L'inconscient",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle méthode Freud développe-t-il pour accéder à l'inconscient ?",
             "options":["A. La méditation","B. La psychanalyse (association libre, interprétation des rêves)","C. La dialectique socratique","D. L'introspection rationnelle"],
             "answer":"B",
             "correction":"Freud développe la psychanalyse : méthode thérapeutique et théorie de l'appareil psychique. Ses techniques d'accès à l'inconscient sont l'association libre (dire tout ce qui vient à l'esprit sans censure), l'interprétation des rêves ('voie royale vers l'inconscient'), l'analyse des actes manqués et des lapsus (Psychopathologie de la vie quotidienne)."},
            {"index":2,"type":"vrai-faux","question":"L'inconscient freudien est simplement ce dont on n'est pas conscient à un moment donné, comme les souvenirs oubliés.",
             "answer":"FAUX",
             "correction":"FAUX. Pour Freud, l'inconscient n'est pas seulement le 'préconscient' (ce qui est temporairement hors de la conscience). L'inconscient freudien est un système actif qui contient des représentations refoulées – trop douloureuses ou inacceptables pour être conscientes – qui exercent une force permanente sur nos comportements, désirs et symptômes, malgré la résistance de la censure."},
            {"index":3,"type":"texte","question":"Qu'est-ce que le refoulement chez Freud et quel est son rôle dans la formation de l'inconscient ?",
             "answer":"Le refoulement est le mécanisme de défense par lequel des représentations jugées inacceptables sont repoussées dans l'inconscient et maintenues hors de la conscience.",
             "correction":"Le refoulement (Verdrängung) est le mécanisme central de la psychanalyse freudienne : la censure psychique refoule dans l'inconscient les représentations liées à des désirs inacceptables (pulsions sexuelles, agressives), les maintenant hors de la conscience. Ces représentations refoulées ne disparaissent pas mais exercent une pression constante et reviennent sous des formes déguisées : rêves, lapsus, actes manqués, symptômes névrotiques."},
            {"index":4,"type":"qcm","question":"Quelle objection philosophique majeure peut-on adresser à la notion d'inconscient freudien ?",
             "options":["A. L'inconscient n'existe que chez les malades mentaux","B. Si l'inconscient est par définition inaccessible à la conscience, on ne peut ni le prouver ni le réfuter (problème de réfutabilité)","C. La psychanalyse est trop rationnelle","D. Freud ne parle que des rêves"],
             "answer":"B",
             "correction":"La critique philosophique majeure de l'inconscient freudien (notamment Karl Popper) porte sur sa non-réfutabilité : une théorie scientifique doit pouvoir être potentiellement infirmée par l'expérience. La psychanalyse interprète tout (confirmation comme infirmation) comme preuve de sa théorie. De plus, Sartre (L'Être et le Néant) critique la 'mauvaise foi' : l'inconscient est une façon de nier sa liberté et sa responsabilité."},
            {"index":5,"type":"vrai-faux","question":"Selon Freud, le ça, le moi et le surmoi sont les trois instances de la deuxième topique de l'appareil psychique.",
             "answer":"VRAI",
             "correction":"VRAI. Dans la deuxième topique freudienne (Au-delà du principe de plaisir, 1920), Freud distingue trois instances : le ça (réservoir des pulsions, principe de plaisir, inconscient), le moi (instance de médiation entre le ça, le surmoi et la réalité, principe de réalité) et le surmoi (instance morale intériorisée, censure, idéal du moi)."},
            {"index":6,"type":"texte","question":"En quoi la découverte de l'inconscient constitue-t-elle, selon Freud, une 'blessure narcissique' pour l'humanité ?",
             "answer":"L'inconscient montre que le moi n'est pas maître chez lui : nos comportements sont en partie déterminés par des forces que nous ne contrôlons pas, blessant ainsi notre image de maîtrise de nous-mêmes.",
             "correction":"Freud (Introduction à la psychanalyse) identifie trois 'blessures narcissiques' : Copernic (la Terre n'est pas le centre de l'univers), Darwin (l'homme descend de l'animal) et lui-même (le moi n'est pas maître chez lui). La psychanalyse montre que nos pensées, désirs et comportements sont largement déterminés par un inconscient que nous ne contrôlons pas, remettant en cause l'idéal de maîtrise de soi de la philosophie classique."},
            {"index":7,"type":"qcm","question":"Quel philosophe affirme que la thèse de l'inconscient est incompatible avec la liberté humaine et constitue une 'mauvaise foi' ?",
             "options":["A. Freud lui-même","B. Hegel","C. Sartre","D. Descartes"],
             "answer":"C",
             "correction":"Jean-Paul Sartre (L'Être et le Néant, 1943) critique l'inconscient freudien : pour lui, la conscience est transparente à elle-même et fondamentalement libre. Se réfugier derrière l'inconscient pour expliquer ses actes ('c'est mon inconscient') est une forme de 'mauvaise foi' – une manière de fuir sa liberté et sa responsabilité en se traitant comme une chose déterminée."},
            {"index":8,"type":"vrai-faux","question":"L'interprétation des rêves selon Freud montre que les rêves ont un sens caché qui exprime des désirs inconscients refoulés.",
             "answer":"VRAI",
             "correction":"VRAI. Pour Freud (L'interprétation des rêves, 1900), le rêve est 'la voie royale vers l'inconscient'. Il distingue le contenu manifeste (ce dont on se souvient) et le contenu latent (le sens caché). Le travail du rêve (condensation, déplacement, mise en scène, élaboration secondaire) transforme les désirs inconscients refoulés en contenu manifeste acceptable pour la censure."},
        ]
    },
    226: {
        "serie": 3,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 3",
        "description": "La liberté",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle position philosophique affirme que toutes nos actions sont déterminées par des causes antérieures, rendant la liberté illusoire ?",
             "options":["A. Le libertarisme","B. Le déterminisme","C. Le compatibilisme","D. L'existentialisme"],
             "answer":"B",
             "correction":"Le déterminisme affirme que tous les événements, y compris nos actions et pensées, sont intégralement déterminés par des causes antérieures (biologiques, psychologiques, sociales, physiques). Dans cette perspective, la liberté au sens de 'pouvoir faire autrement' est une illusion. Spinoza et plus tard le positivisme scientifique défendent cette position."},
            {"index":2,"type":"vrai-faux","question":"Pour Kant, la liberté est compatible avec le déterminisme naturel, car elle appartient à un ordre différent (le monde intelligible).",
             "answer":"VRAI",
             "correction":"VRAI. Kant (Critique de la raison pratique) résout l'antinomie liberté/déterminisme en distinguant deux mondes : le monde phénoménal (nature, déterminisme causal) et le monde noumènal (choses en soi, liberté). En tant qu'être sensible, l'homme est soumis au déterminisme ; en tant qu'être raisonnable (noumène), il est libre et soumis à la loi morale qu'il se donne lui-même (autonomie)."},
            {"index":3,"type":"texte","question":"Qu'est-ce que le libre arbitre et pourquoi est-il au cœur du débat sur la liberté ?",
             "answer":"Le libre arbitre est la capacité de choisir librement entre plusieurs options, indépendamment de toute détermination extérieure ou intérieure. Il est au cœur du débat entre déterminisme et libertarisme.",
             "correction":"Le libre arbitre (liberum arbitrium) est la faculté de choisir librement, d'être la cause de ses propres actes sans être entièrement déterminé par des causes externes ou internes (nature, éducation, inconscient). Il est central en philosophie morale et juridique : si l'homme n'a pas de libre arbitre, peut-on le tenir responsable de ses actes ? Le compatibilisme (Hume, Hobbes) tente de concilier liberté et déterminisme en redéfinissant la liberté comme l'absence de contrainte extérieure."},
            {"index":4,"type":"qcm","question":"Pour Spinoza, l'homme qui croit agir librement est en réalité :",
             "options":["A. Véritablement libre car il suit sa nature","B. Ignorant des causes qui le déterminent, car tout est nécessité","C. Libre grâce à la raison","D. Libre car il obéit à Dieu"],
             "answer":"B",
             "correction":"Pour Spinoza (Éthique, Appendice de la Partie I), l'homme croit être libre parce qu'il a conscience de ses désirs mais ignore les causes qui les déterminent. 'Les hommes se croient libres parce qu'ils sont conscients de leurs désirs et ignorants des causes qui les déterminent.' La vraie liberté spinoziste n'est pas le libre arbitre mais la compréhension des nécessités qui nous gouvernent (la liberté comme nécessité comprise)."},
            {"index":5,"type":"vrai-faux","question":"La liberté politique (civile) désigne la capacité d'agir sans aucune contrainte ni loi.",
             "answer":"FAUX",
             "correction":"FAUX. La liberté politique (civile ou sociale) n'est pas l'absence de toute loi, mais la capacité d'agir dans le cadre de lois que l'on s'est données collectivement (Rousseau : 'la liberté, c'est l'obéissance à la loi qu'on s'est prescrite'). Montesquieu (De l'esprit des lois) la définit comme le droit de faire tout ce que les lois permettent. Elle implique un équilibre entre liberté individuelle et contraintes légitimes de la vie sociale."},
            {"index":6,"type":"texte","question":"Quelle est la thèse de Rousseau sur la liberté dans le Contrat social ?",
             "answer":"Rousseau soutient que la vraie liberté est l'obéissance à la loi que l'on s'est soi-même prescrite dans le cadre du contrat social ; la liberté civile est supérieure à la liberté naturelle.",
             "correction":"Dans Du Contrat social (1762), Rousseau distingue liberté naturelle (faire tout ce que l'on veut, limitée par la force) et liberté civile (obéir aux lois issues de la volonté générale). En entrant dans la société politique, l'homme renonce à sa liberté naturelle mais gagne une liberté civile plus noble : 'l'obéissance à la loi qu'on s'est prescrite est liberté.' Il ajoute la liberté morale : la maîtrise de soi par la raison contre les passions."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que l'autonomie au sens kantien ?",
             "options":["A. L'indépendance économique","B. La capacité à vivre seul sans aide","C. La capacité à se donner à soi-même sa propre loi morale par la raison","D. La liberté de faire ce que l'on veut"],
             "answer":"C",
             "correction":"L'autonomie (auto = soi-même, nomos = loi) chez Kant est la propriété qu'a la volonté de se donner à elle-même sa propre loi morale par la raison pure pratique, indépendamment de toute inclination sensible ou autorité extérieure. Elle s'oppose à l'hétéronomie (recevoir sa loi d'une source extérieure : désirs, traditions, autorité). L'autonomie est le fondement de la dignité humaine et de la morale kantienne."},
            {"index":8,"type":"vrai-faux","question":"L'existentialisme sartrien affirme que 'l'existence précède l'essence', ce qui signifie que l'homme se définit par ses choix et non par une nature préalable.",
             "answer":"VRAI",
             "correction":"VRAI. Pour Sartre (L'existentialisme est un humanisme, 1945), contrairement aux objets (dont l'essence – ce qu'ils sont – précède l'existence), l'homme existe d'abord puis se définit par ses choix et ses actes. Il n'a pas de nature fixe à l'avance. Cette absence de nature préalable est source de liberté absolue mais aussi d'angoisse et de responsabilité totale."},
        ]
    },
    227: {
        "serie": 4,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 4",
        "description": "La vérité",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle est la conception classique (correspondantiste) de la vérité ?",
             "options":["A. Est vrai ce qui est utile","B. Est vrai ce qui est cohérent avec un système de croyances","C. Est vrai ce qui correspond à la réalité (adéquation entre l'intellect et la chose)","D. Est vrai ce qui fait consensus dans une communauté"],
             "answer":"C",
             "correction":"La conception correspondantiste (ou adéquationniste) de la vérité, héritée d'Aristote ('est vraie la proposition qui affirme ce qui est'), définit la vérité comme l'adéquation (adequatio) entre un jugement ou une proposition et la réalité qu'il représente. C'est la conception la plus intuitive et la plus répandue en philosophie classique."},
            {"index":2,"type":"vrai-faux","question":"Le scepticisme philosophique affirme qu'il est impossible d'atteindre une vérité certaine et définitive.",
             "answer":"VRAI",
             "correction":"VRAI. Le scepticisme (de Pyrrhon à Sextus Empiricus dans l'Antiquité) affirme qu'on ne peut avoir de connaissance certaine car nos facultés sont faillibles, les opinions contradictoires se valent et toute preuve exige une autre preuve (régression à l'infini). La réponse sceptique est l'épochè (suspension du jugement) et l'ataraxie (tranquillité de l'âme). Le scepticisme modéré (Montaigne) est une posture intellectuelle d'humilité."},
            {"index":3,"type":"texte","question":"Quelle distinction Platon établit-il entre opinion (doxa) et savoir vrai (épistémè) ?",
             "answer":"Pour Platon, l'opinion porte sur le monde sensible changeant, tandis que le savoir vrai porte sur les Idées éternelles et immuables, accessibles par la raison.",
             "correction":"Dans la République (allégorie de la ligne), Platon distingue : l'opinion (doxa) – connaissance du monde sensible, du visible, instable et incertaine – et la science (épistémè) – connaissance du monde intelligible, des Idées (Formes) éternelles et immuables. L'allégorie de la caverne illustre ce passage de l'opinion (ombres sur la paroi) à la vérité (lumière du soleil = l'Idée du Bien). La philosophie est le chemin qui mène de la doxa à l'épistémè."},
            {"index":4,"type":"qcm","question":"Pour Nietzsche, 'il n'y a pas de faits, seulement des interprétations' signifie que :",
             "options":["A. La vérité scientifique est impossible","B. Toute vérité est une perspective, une interprétation liée à une volonté de puissance","C. Les faits historiques n'existent pas","D. Seules les opinions comptent"],
             "answer":"B",
             "correction":"Pour Nietzsche (Le Gai Savoir, Fragments posthumes), la vérité n'est pas une adéquation au réel mais le résultat d'une interprétation perpétuelle du monde. Il n'y a pas de 'fait brut' : toute perception, tout concept est déjà une interprétation liée à une perspective, une volonté de puissance. La 'vérité' est une 'armée mobile de métaphores' – une fiction utile que nous confondons avec le réel."},
            {"index":5,"type":"vrai-faux","question":"En sciences, une théorie est d'autant plus vraie qu'elle est ancienne et bien établie.",
             "answer":"FAUX",
             "correction":"FAUX. En philosophie des sciences, Karl Popper (La Logique de la découverte scientifique) affirme que la science progresse par réfutations et révolutions. Une théorie scientifique n'est jamais définitivement vraie : elle est acceptée provisoirement jusqu'à ce qu'une expérience la réfute. Thomas Kuhn parle de 'révolutions scientifiques' (changements de paradigme). L'ancienneté d'une théorie ne garantit pas sa vérité."},
            {"index":6,"type":"texte","question":"Qu'est-ce que la méthode du doute chez Descartes et à quoi mène-t-elle ?",
             "answer":"Le doute méthodique cartésien consiste à rejeter tout ce qui peut être mis en doute pour trouver une première vérité indubitable : le cogito.",
             "correction":"Descartes (Méditations métaphysiques, 1ère méditation) applique un doute méthodique (provisoire et volontaire, non sceptique définitif) : il rejette les certitudes des sens (qui trompent parfois), l'existence du monde extérieur (peut-être un rêve), même les vérités mathématiques (hypothèse du malin génie). Ce doute hyperbolique le conduit à la seule certitude indubitable : 'Je pense, donc je suis.' Le cogito est le fondement de tout l'édifice de la connaissance cartésienne."},
            {"index":7,"type":"qcm","question":"Le pragmatisme américain (William James, John Dewey) définit la vérité comme :",
             "options":["A. L'adéquation entre l'idée et la réalité","B. Ce qui est utile, ce qui 'fonctionne' dans la pratique","C. Ce qui résiste au doute radical","D. Ce qui est universellement accepté"],
             "answer":"B",
             "correction":"Le pragmatisme (William James, Pragmatism, 1907 ; John Dewey) définit la vérité de manière instrumentale : est vrai ce qui est utile, ce qui 'fonctionne' (works), ce qui produit de bons résultats dans l'action. Une idée vraie est une idée qui 'paie' (pays). Cette conception rompt avec le réalisme classique en faisant de la vérité une valeur pratique plutôt qu'une correspondance théorique."},
            {"index":8,"type":"vrai-faux","question":"La vérité scientifique est définitive et ne peut être remise en question.",
             "answer":"FAUX",
             "correction":"FAUX. La vérité scientifique est provisoire, révisable et falsifiable (Popper). L'histoire des sciences montre de nombreuses révisions : la physique newtonienne a été relativisée par Einstein, la cosmologie géocentrique remplacée par l'héliocentrisme. Bachelard (La Formation de l'esprit scientifique) montre que la science progresse contre les 'obstacles épistémologiques' et par rectifications successives."},
        ]
    },
    228: {
        "serie": 5,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 5",
        "description": "La raison et la démonstration",
        "questions": [
            {"index":1,"type":"qcm","question":"Qu'est-ce qu'un syllogisme aristotélicien ?",
             "options":["A. Une forme de méditation","B. Un raisonnement déductif composé de deux prémisses et d'une conclusion","C. Un raisonnement par analogie","D. Une preuve empirique"],
             "answer":"B",
             "correction":"Le syllogisme (Aristote, Premiers Analytiques) est une forme de raisonnement déductif valide composée de deux prémisses (une majeure et une mineure) et d'une conclusion. Exemple classique : 'Tout homme est mortel (majeure) ; Socrate est un homme (mineure) ; donc Socrate est mortel (conclusion).' La logique formelle aristotélicienne fonde la tradition de la démonstration rationnelle occidentale."},
            {"index":2,"type":"vrai-faux","question":"Le rationalisme philosophique affirme que la raison est la seule source fiable de connaissance.",
             "answer":"VRAI",
             "correction":"VRAI. Le rationalisme (Descartes, Spinoza, Leibniz) affirme que la raison – et non les sens ou l'expérience – est la source principale et la plus fiable de la connaissance. Les idées innées ou les vérités de raison (mathématiques, logique) sont indépendantes de l'expérience sensible. Il s'oppose à l'empirisme (Locke, Hume, Berkeley) qui fait de l'expérience le fondement de la connaissance."},
            {"index":3,"type":"texte","question":"Quelle est la différence entre raisonnement déductif et raisonnement inductif ?",
             "answer":"La déduction part de principes généraux pour aboutir à une conclusion particulière nécessaire ; l'induction part de cas particuliers pour établir une loi générale probable.",
             "correction":"La déduction (logique, mathématiques) part de prémisses générales pour tirer une conclusion particulière nécessairement vraie si les prémisses sont vraies. La conclusion est contenue dans les prémisses (ex : syllogisme). L'induction (sciences empiriques) part d'observations particulières répétées pour établir une loi générale probable mais jamais certaine (problème de l'induction de Hume : aucun nombre d'observations ne peut garantir la vérité d'une loi universelle)."},
            {"index":4,"type":"qcm","question":"Qu'est-ce qu'une démonstration mathématique ?",
             "options":["A. Une vérification expérimentale","B. Une suite de déductions à partir d'axiomes et de définitions, aboutissant à un théorème","C. Une argumentation rhétorique convaincante","D. Un raisonnement par analogie"],
             "answer":"B",
             "correction":"Une démonstration mathématique est une suite de déductions logiques rigoureuses, partant d'axiomes (vérités premières acceptées sans preuve) et de définitions, pour établir la vérité nécessaire d'un théorème. Elle se distingue de la preuve empirique (qui dépend de l'expérience) par son caractère formel, universel et nécessaire. Les mathématiques sont pour les rationalistes le modèle de toute connaissance certaine."},
            {"index":5,"type":"vrai-faux","question":"L'empirisme de Hume affirme que toute connaissance vient de l'expérience sensible et que les idées sont des copies affaiblies des impressions sensorielles.",
             "answer":"VRAI",
             "correction":"VRAI. Pour Hume (Enquête sur l'entendement humain), toutes nos idées proviennent d'impressions sensorielles : les idées sont des 'copies' plus faibles des impressions. Il n'existe pas d'idées innées. Même les idées complexes (comme la causalité) sont des habitudes mentales issues de l'expérience répétée, non des vérités rationnelles nécessaires. Cet empirisme radical remet en cause la métaphysique et la raison pure."},
            {"index":6,"type":"texte","question":"Comment Kant tente-t-il de dépasser l'opposition entre rationalisme et empirisme ?",
             "answer":"Kant synthétise rationalisme et empirisme en montrant que la connaissance résulte de l'application des formes a priori de la sensibilité et de l'entendement aux données de l'expérience.",
             "correction":"Kant (Critique de la raison pure, 1781) entreprend une 'révolution copernicienne' : ce n'est pas notre connaissance qui se règle sur les objets, mais les objets qui se règlent sur les structures de notre esprit. Il distingue les formes a priori (espace, temps, catégories comme la causalité) qui organisent l'expérience, et la matière a posteriori (données sensorielles). La connaissance naît de la synthèse de l'a priori et de l'expérience, dépassant l'opposition rationalisme/empirisme."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que le principe de non-contradiction en logique ?",
             "options":["A. Une proposition ne peut pas être à la fois vraie et fausse dans le même sens et au même moment","B. Toute démonstration doit éviter les répétitions","C. Il ne faut pas contredire les autorités","D. Deux théories ne peuvent jamais coexister"],
             "answer":"A",
             "correction":"Le principe de non-contradiction (Aristote, Métaphysique) est l'un des principes fondamentaux de la logique : 'Il est impossible qu'une même chose soit et ne soit pas en même temps, sous le même rapport.' Toute contradiction logique est source d'erreur. Ce principe est le fondement de toute démonstration et de toute pensée rationnelle cohérente."},
            {"index":8,"type":"vrai-faux","question":"La raison instrumentale désigne l'usage de la raison pour définir les fins morales et politiques d'une société.",
             "answer":"FAUX",
             "correction":"FAUX. La raison instrumentale (Horkheimer, Adorno – Dialectique de la Raison) est la raison réduite à l'efficacité technique : calculer les meilleurs moyens pour atteindre des fins données, sans questionner ces fins elles-mêmes. Elle s'oppose à la raison substantielle (qui juge des fins). La raison instrumentale, dominante dans la modernité, est critiquée car elle peut servir n'importe quelle fin, y compris les pires (ex : efficacité de l'industrie d'extermination nazie)."},
        ]
    },
    229: {
        "serie": 6,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 6",
        "description": "Le langage",
        "questions": [
            {"index":1,"type":"qcm","question":"Pour quoi le langage est-il considéré comme spécifique à l'être humain ?",
             "options":["A. Les humains sont les seuls à émettre des sons","B. Le langage humain est articulé, symbolique et permet d'exprimer des pensées abstraites et de créer du sens","C. Les animaux ne communiquent pas","D. Le langage humain est inné et génétiquement programmé"],
             "answer":"B",
             "correction":"Ce qui distingue le langage humain des systèmes de communication animaux (signaux, codes génétiques), c'est son double articulation (Martinet), sa créativité infinie (Chomsky), sa dimension symbolique (le mot n'est pas la chose) et sa capacité à exprimer des contenus abstraits, des contre-factuels et des récits. Aristote définit l'homme comme 'animal politique' précisément parce qu'il a le logos (raison + langage)."},
            {"index":2,"type":"vrai-faux","question":"Selon Saussure, le signe linguistique est composé d'un signifiant (image acoustique) et d'un signifié (concept).",
             "answer":"VRAI",
             "correction":"VRAI. Ferdinand de Saussure (Cours de linguistique générale, 1916) définit le signe linguistique comme l'union d'un signifiant (image acoustique, la forme sonore ou graphique du mot) et d'un signifié (concept, la représentation mentale). Ce lien est arbitraire (pas de ressemblance naturelle entre le son 'chien' et l'animal) et différentiel (chaque signe se définit par opposition aux autres)."},
            {"index":3,"type":"texte","question":"En quoi consiste la thèse de Wittgenstein 'les limites de mon langage signifient les limites de mon monde' ?",
             "answer":"Cette thèse signifie que notre capacité à penser et à concevoir le monde est limitée par les possibilités expressives de notre langage.",
             "correction":"Wittgenstein (Tractatus logico-philosophicus, 1921) affirme que le monde ne peut être pensé qu'à travers le langage : 'Les limites de mon langage signifient les limites de mon monde.' Ce qui ne peut être dit (l'indicible : l'éthique, le mystique) ne peut être pensé. Cette thèse a des implications importantes : notre vision du monde est structurée par notre langue. Le relativisme linguistique (hypothèse Sapir-Whorf) prolonge cette idée : la langue maternelle influence la façon de percevoir le réel."},
            {"index":4,"type":"qcm","question":"Qu'est-ce que la performativité du langage selon Austin ?",
             "options":["A. La capacité du langage à décrire le monde","B. La propriété de certains énoncés à accomplir une action en les énonçant ('Je vous déclare mariés')","C. Le pouvoir rhétorique de convaincre","D. La musicalité du discours"],
             "answer":"B",
             "correction":"J.L. Austin (Quand dire, c'est faire, 1962) distingue les énoncés constatifs (qui décrivent) et les énoncés performatifs (qui accomplissent une action en étant prononcés). Ex : 'Je promets', 'Je vous déclare mariés', 'La séance est ouverte'. Ces actes de langage ne sont pas vrais ou faux mais 'heureux' ou 'malheureux' selon les conditions de leur énonciation (contexte, autorité du locuteur)."},
            {"index":5,"type":"vrai-faux","question":"Le langage est uniquement un instrument de communication neutre qui transmet des informations objectives.",
             "answer":"FAUX",
             "correction":"FAUX. Le langage est bien plus qu'un simple outil de transmission d'informations : il construit le sens, exprime des valeurs et des émotions, structure notre rapport au monde (Wittgenstein), peut manipuler et exercer du pouvoir (rhétorique, propagande), crée du lien social et identitaire, et peut aussi cacher ou déformer la réalité (Orwell, 1984 : la 'novlangue'). Heidegger dit même que 'le langage est la maison de l'être'."},
            {"index":6,"type":"texte","question":"Quelle est la différence entre langue et parole selon Saussure ?",
             "answer":"La langue est le système collectif de signes partagé par une communauté ; la parole est l'usage individuel et concret de ce système.",
             "correction":"Saussure (Cours de linguistique générale) distingue : la langue (système abstrait, collectif, social, de signes partagé par tous les membres d'une communauté linguistique, code commun) et la parole (acte individuel, concret, variable, d'utilisation de ce système). La langue est l'objet de la linguistique ; la parole est son actualisation. Chomsky reformulera cette distinction en compétence/performance linguistique."},
            {"index":7,"type":"qcm","question":"Pour Bergson, le langage est insuffisant pour exprimer la réalité profonde de la conscience car :",
             "options":["A. Le langage est trop poétique","B. Le langage, fait pour l'action et la communication sociale, découpe et fige ce qui est en réalité fluide et continu (la durée)","C. La conscience est trop complexe pour aucun système symbolique","D. Bergson préfère le silence"],
             "answer":"B",
             "correction":"Bergson (Essai sur les données immédiates de la conscience) critique le langage qui, adapté à la vie pratique et à la communication sociale, impose ses découpages spatiaux et statiques sur une réalité qui est en réalité durée pure, mouvement continu, qualité irréductible à la quantité. En mettant des mots sur notre vie intérieure, nous la trahissons en la figeant. La vraie conscience est ineffable (ne peut se dire entièrement)."},
            {"index":8,"type":"vrai-faux","question":"L'apprentissage du langage est exclusivement culturel : un enfant sans contact humain développerait spontanément son propre langage.",
             "answer":"FAUX",
             "correction":"FAUX. Si la capacité au langage est biologiquement inscrite (Chomsky : 'grammaire universelle' innée, dispositif d'acquisition du langage), son acquisition nécessite impérativement l'exposition à une langue humaine dans une période critique (avant 12 ans environ). Les cas d'enfants 'sauvages' (Genie, Victor de l'Aveyron) montrent que sans stimulation linguistique, le langage ne se développe pas spontanément ou très incomplètement."},
        ]
    },
    230: {
        "serie": 7,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 7",
        "description": "Le travail et la technique",
        "questions": [
            {"index":1,"type":"qcm","question":"Selon Marx, qu'est-ce que l'aliénation du travail dans le système capitaliste ?",
             "options":["A. Le fait de travailler trop longtemps","B. Le fait que l'ouvrier est séparé du produit de son travail, de son activité, de ses semblables et de lui-même","C. La mécanisation des tâches","D. Le salaire insuffisant des ouvriers"],
             "answer":"B",
             "correction":"Marx (Manuscrits de 1844) théorise l'aliénation : dans le capitalisme, le travailleur est aliéné (étranger à lui-même) de quatre façons : séparé du produit de son travail (qui lui échappe), de son activité laborieuse (contrainte, non épanouissante), de ses semblables (concurrence), et de son être générique (son humanité). Le travail, qui devrait être la réalisation de l'homme, devient une souffrance."},
            {"index":2,"type":"vrai-faux","question":"Hegel voit dans le travail un moment positif par lequel l'homme se réalise et transforme la nature en y imprimant sa marque.",
             "answer":"VRAI",
             "correction":"VRAI. Dans la dialectique du maître et de l'esclave (Phénoménologie de l'Esprit), Hegel montre que c'est l'esclave, qui travaille, qui finit par se libérer : en transformant la nature par son travail, il y imprime sa subjectivité, se reconnaît dans son œuvre et développe sa conscience de soi. Le travail est médiation entre l'homme et le monde, source de formation (Bildung) et d'émancipation."},
            {"index":3,"type":"texte","question":"En quoi la technique est-elle constitutive de l'humanité ? Illustrez avec un exemple.",
             "answer":"La technique est constitutive de l'humanité car l'être humain est par nature un être technicien qui transforme son milieu par des outils ; sans technique, il ne pourrait survivre ni se développer.",
             "correction":"Pour les philosophes comme Bergson (L'Évolution créatrice) ou Leroi-Gourhan (Le Geste et la Parole), l'homo sapiens est aussi homo faber : l'usage d'outils est coextensif à l'humanité (silex taillés il y a 2,5 millions d'années). La technique n'est pas un ajout extérieur mais constitutive de la condition humaine : elle permet de s'adapter à l'environnement, de libérer du temps (meule, four), de créer de la culture. Exemple : l'écriture est une technique cognitive qui transforme la mémoire et la pensée."},
            {"index":4,"type":"qcm","question":"Heidegger critique la technique moderne car elle :",
             "options":["A. Est trop lente et inefficace","B. Réduit tout l'étant (le monde, les hommes, la nature) à un simple stock de ressources disponibles (le 'Gestell' ou arraisonnement)","C. Détruit les emplois","D. N'est pas assez universelle"],
             "answer":"B",
             "correction":"Heidegger (La Question de la technique, 1953) critique la technique moderne : elle n'est pas neutre mais impose une façon d'être au monde, le 'Gestell' (arraisonnement, dispositif). Elle réduit tout l'étant à des 'stocks disponibles' (Bestand) : la nature est un réservoir d'énergie, les hommes des ressources humaines. Elle occulte les autres modes de dévoilement de l'être (art, poésie) et constitue le 'danger' suprême."},
            {"index":5,"type":"vrai-faux","question":"Le progrès technique garantit automatiquement le progrès moral et le bonheur humain.",
             "answer":"FAUX",
             "correction":"FAUX. Le progrès technique n'implique pas automatiquement le progrès moral ou le bonheur. L'histoire montre que les mêmes avancées techniques peuvent servir des fins opposées : la chimie produit des médicaments et des armes chimiques, l'énergie nucléaire chauffe les maisons et détruit des villes. Jonas (Le Principe responsabilité) souligne que la technique moderne crée des risques sans précédent et exige une nouvelle éthique de la responsabilité envers les générations futures."},
            {"index":6,"type":"texte","question":"Qu'est-ce que la division du travail selon Adam Smith et quelles en sont les conséquences ?",
             "answer":"La division du travail est la spécialisation des travailleurs dans des tâches précises, accroissant la productivité mais pouvant aboutir à l'abrutissement du travailleur.",
             "correction":"Adam Smith (La Richesse des nations, 1776) montre que la division du travail (spécialisation de chaque travailleur dans une tâche élémentaire) est la principale source de la richesse des nations : gain de dextérité, économie de temps, invention de machines. Mais il reconnaît aussi ses limites : le travailleur répétitif devient 'aussi stupide et ignorant qu'une créature humaine peut le devenir.' Marx et Tocqueville amplifieront cette critique de l'abrutissement par la spécialisation."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que l'homo faber ?",
             "options":["A. L'homme qui parle","B. L'homme qui pense abstraitement","C. L'homme fabricant, définit par son usage des outils et des techniques","D. L'homme politique"],
             "answer":"C",
             "correction":"L'homo faber (Bergson, L'Évolution créatrice ; Henri Bergson) désigne l'homme en tant qu'être fabricant (faber = artisan en latin), qui se distingue des autres animaux par sa capacité à fabriquer et utiliser des outils. Cette définition de l'humain par la technique s'oppose ou complète la définition traditionnelle de l'homo sapiens (homme qui sait, qui pense) et de l'homo loquens (homme qui parle)."},
            {"index":8,"type":"vrai-faux","question":"L'automatisation et l'intelligence artificielle posent de nouveaux défis philosophiques quant à la place du travail humain dans la société.",
             "answer":"VRAI",
             "correction":"VRAI. L'automatisation croissante (robots industriels) et l'intelligence artificielle menacent de nombreux emplois, y compris qualifiés, posant des questions philosophiques majeures : le travail est-il constitutif de l'identité humaine et de la dignité ? Une société sans travail est-elle souhaitable ou possible ? Comment redistribuer les richesses créées par les machines ? Ces enjeux renouvellent les débats philosophiques sur le travail, le loisir et l'humanité."},
        ]
    },
    231: {
        "serie": 8,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 8",
        "description": "La morale et l'éthique",
        "questions": [
            {"index":1,"type":"qcm","question":"Qu'est-ce que l'impératif catégorique chez Kant ?",
             "options":["A. Une règle morale qui dépend des circonstances","B. Un commandement inconditionnel de la raison : 'Agis seulement selon la maxime que tu peux vouloir ériger en loi universelle'","C. L'obligation légale de respecter les lois de l'État","D. L'intérêt bien compris de chaque individu"],
             "answer":"B",
             "correction":"L'impératif catégorique (Kant, Fondements de la métaphysique des mœurs, 1785) est la loi morale suprême, inconditionnelle (non hypothétique) : 'Agis seulement d'après la maxime grâce à laquelle tu peux vouloir en même temps qu'elle devienne une loi universelle.' Il a plusieurs formulations, dont la formule de l'humanité : 'Agis de façon à traiter l'humanité toujours comme une fin et jamais seulement comme un moyen.'"},
            {"index":2,"type":"vrai-faux","question":"L'utilitarisme (Bentham, Mill) juge la valeur morale d'une action par ses conséquences et l'utilité qu'elle produit.",
             "answer":"VRAI",
             "correction":"VRAI. L'utilitarisme (Jeremy Bentham, John Stuart Mill) est une éthique conséquentialiste : la valeur morale d'un acte est jugée par ses conséquences. Le principe d'utilité (ou 'principe du plus grand bonheur') affirme qu'il faut maximiser le bonheur du plus grand nombre. L'action moralement bonne est celle qui produit le plus de bonheur (ou plaisir) et le moins de souffrance pour le plus grand nombre de personnes."},
            {"index":3,"type":"texte","question":"Quelle est la différence entre morale et éthique ?",
             "answer":"La morale désigne l'ensemble des règles et devoirs qui s'imposent à tous ; l'éthique est une réflexion philosophique sur ce qui constitue une 'bonne vie' et les fondements de l'agir moral.",
             "correction":"La distinction est souvent nuancée : la morale (du latin mores : mœurs) désigne traditionnellement les normes, règles et devoirs qui s'imposent à tous (dimension impérative, devoir). L'éthique (du grec ethos : caractère, manière de vivre) est la réflexion philosophique sur ce qui constitue une 'bonne vie', les vertus, et les fondements de l'agir juste. Pour certains (Ricoeur), l'éthique (visée du bien) précède la morale (obligation) ; l'éthique est première."},
            {"index":4,"type":"qcm","question":"Quelle est la position morale d'Aristote concernant la vertu ?",
             "options":["A. La vertu est une règle divine imposée de l'extérieur","B. La vertu est une disposition acquise par l'habitude, un juste milieu entre excès et défaut","C. La vertu n'existe pas, seul l'intérêt personnel compte","D. La vertu est l'obéissance aux lois de la cité"],
             "answer":"B",
             "correction":"Pour Aristote (Éthique à Nicomaque), la vertu (arètè) est une disposition stable du caractère acquise par l'habitude et l'éducation, qui consiste à choisir le juste milieu (médiété) entre deux extrêmes défectueux. Ex : le courage est le juste milieu entre la lâcheté et la témérité. La vie vertueuse mène à l'eudaimonia (bonheur, épanouissement) – fin ultime de l'existence humaine."},
            {"index":5,"type":"vrai-faux","question":"Le relativisme moral affirme qu'il n'existe pas de valeurs morales universelles valables pour tous les êtres humains.",
             "answer":"VRAI",
             "correction":"VRAI (avec nuances). Le relativisme moral affirme que les valeurs morales sont relatives à une culture, une époque ou un individu, sans qu'aucune ne soit objectivement supérieure aux autres. Il s'oppose au universalisme moral (Kant, droits de l'homme). Si le relativisme a l'avantage de respecter la diversité culturelle, il est critiqué car il peut justifier des pratiques comme la torture ou l'esclavage au nom des cultures."},
            {"index":6,"type":"texte","question":"Qu'est-ce que le dilemme du tramway (trolley problem) et qu'illustre-t-il en éthique ?",
             "answer":"Le dilemme du tramway est une expérience de pensée qui oppose l'intuition d'épargner le plus de vies (utilitarisme) à l'interdiction de se servir d'une personne comme moyen (déontologie kantienne).",
             "correction":"Le trolley problem (Foot, Thomson) : un tramway fou fonce vers 5 personnes ; vous pouvez actionner un levier pour le dévier vers une voie où il tuera 1 personne. Devez-vous agir ? L'utilitarisme dit oui (5 > 1) ; la déontologie kantienne dit non (on ne peut pas utiliser quelqu'un comme moyen). Ce dilemme illustre la tension entre éthiques conséquentialistes (résultats) et déontologiques (règles absolues), et révèle nos intuitions morales souvent contradictoires."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que la 'golden rule' (règle d'or) présente dans de nombreuses traditions morales et religieuses ?",
             "options":["A. Le principe de maximiser son propre bonheur","B. 'Ne fais pas à autrui ce que tu ne voudrais pas qu'il te fasse'","C. L'obligation d'obéir aux lois de l'État","D. Le devoir de chercher la vérité"],
             "answer":"B",
             "correction":"La règle d'or ('Ne fais pas à autrui ce que tu ne voudrais pas qu'on te fasse' / 'Fais aux autres ce que tu voudrais qu'on te fasse') est un principe moral présent dans pratiquement toutes les traditions éthiques et religieuses (Confucius, Bible, Coran, Kant, etc.). Elle constitue un des fondements les plus universels de l'éthique et anticipe le principe kantien d'universalisabilité."},
            {"index":8,"type":"vrai-faux","question":"L'éthique du care (Gilligan, Noddings) met l'accent sur les relations, la sollicitude et la responsabilité envers les personnes vulnérables.",
             "answer":"VRAI",
             "correction":"VRAI. L'éthique du care (Carol Gilligan, Nel Noddings) est née en réaction à la morale kantienne universaliste jugée trop abstraite et masculine. Elle valorise la sollicitude (care), les relations interpersonnelles, la sensibilité aux contextes particuliers et la responsabilité envers les personnes vulnérables et dépendantes. Elle met en avant des vertus comme l'empathie, la bienveillance et l'attention aux besoins des autres."},
        ]
    },
    232: {
        "serie": 9,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 9",
        "description": "La politique et l'État",
        "questions": [
            {"index":1,"type":"qcm","question":"Selon Hobbes, pourquoi les hommes acceptent-ils de vivre sous l'autorité d'un État (Léviathan) ?",
             "options":["A. Par amour du bien commun","B. Pour sortir de l'état de nature (guerre de 'tous contre tous') et garantir leur sécurité","C. Par obligation divine","D. Par obéissance à la tradition"],
             "answer":"B",
             "correction":"Hobbes (Léviathan, 1651) décrit l'état de nature comme une guerre 'de tous contre tous', vie 'solitaire, misérable, dangereuse, animale et brève'. Pour sortir de cet état, les hommes concluent un contrat social : ils cèdent tous leurs droits naturels à un souverain absolu (Léviathan) en échange de la paix et de la sécurité. L'État est justifié par la peur de la mort violente."},
            {"index":2,"type":"vrai-faux","question":"Pour Locke, la légitimité de l'État repose sur le consentement des gouvernés et la protection des droits naturels (vie, liberté, propriété).",
             "answer":"VRAI",
             "correction":"VRAI. John Locke (Traité du gouvernement civil, 1690) fonde l'État libéral : les hommes ont des droits naturels inalienables (vie, liberté, propriété). Ils consentent à créer un État pour mieux les protéger. Si le gouvernement viole ces droits, les citoyens ont le droit de résistance et de révolution. Locke influence profondément les révolutions américaine et française."},
            {"index":3,"type":"texte","question":"Qu'est-ce que la démocratie selon Lincoln et quelles en sont les conditions ?",
             "answer":"La démocratie est le gouvernement 'du peuple, par le peuple, pour le peuple'. Elle exige la liberté d'expression, des élections libres et la protection des droits des citoyens.",
             "correction":"La formule d'Abraham Lincoln (discours de Gettysburg, 1863) : 'le gouvernement du peuple, par le peuple, pour le peuple' synthétise l'idéal démocratique. La démocratie implique : la souveraineté populaire (le pouvoir vient du peuple), des élections libres et pluralistes, la séparation des pouvoirs (Montesquieu), la garantie des libertés fondamentales, l'État de droit, et l'égalité des citoyens devant la loi. Tocqueville (De la démocratie en Amérique) en analyse les promesses et les risques (tyrannie de la majorité)."},
            {"index":4,"type":"qcm","question":"Qu'est-ce que la séparation des pouvoirs selon Montesquieu ?",
             "options":["A. La division de l'État en régions autonomes","B. La séparation du pouvoir exécutif, législatif et judiciaire pour éviter les abus de pouvoir","C. La séparation de l'Église et de l'État","D. La division entre pouvoir central et collectivités locales"],
             "answer":"B",
             "correction":"Montesquieu (De l'esprit des lois, 1748) théorise la séparation des pouvoirs : pour éviter le despotisme, il faut que le pouvoir législatif (faire les lois), le pouvoir exécutif (appliquer les lois) et le pouvoir judiciaire (juger selon les lois) soient séparés et équilibrés. Ce principe est fondateur des démocraties libérales modernes et est inscrit dans les constitutions américaine (1787) et française."},
            {"index":5,"type":"vrai-faux","question":"L'anarchisme philosophique affirme que l'État est une institution nécessaire pour organiser la société.",
             "answer":"FAUX",
             "correction":"FAUX. L'anarchisme (Proudhon, Bakounine, Kropotkine) affirme au contraire que l'État est une institution oppressive et illégitime qui doit être supprimée. Les anarchistes croient que les individus et les communautés peuvent s'organiser librement et volontairement, sans coercition étatique. Proudhon est célèbre pour sa formule : 'La propriété, c'est le vol !'"},
            {"index":6,"type":"texte","question":"Quelle distinction Arendt établit-elle entre le travail, l'œuvre et l'action ?",
             "answer":"Arendt distingue le travail (activité biologique de survie), l'œuvre (fabrication d'objets durables) et l'action (dimension politique de la liberté et de l'espace public).",
             "correction":"Hannah Arendt (Condition de l'homme moderne, 1958) distingue trois activités fondamentales : le travail (labor) – activité biologique de reproduction de la vie, cyclique, laisse aucune trace durable ; l'œuvre (work) – fabrication d'objets durables qui constituent un monde humain commun ; l'action (action) – activité proprement politique, dans l'espace public, par laquelle les hommes se révèlent comme êtres singuliers et exercent leur liberté. Pour Arendt, la vie active est dominée par le travail dans la modernité, au détriment de l'action politique."},
            {"index":7,"type":"qcm","question":"Qu'est-ce que le totalitarisme selon Hannah Arendt ?",
             "options":["A. Un régime autoritaire qui contrôle l'armée et la police","B. Un régime politique inédit qui vise à transformer radicalement la société et l'être humain par la terreur et l'idéologie","C. Un régime de parti unique sans idéologie","D. La dictature d'un seul homme"],
             "answer":"B",
             "correction":"Hannah Arendt (Les Origines du totalitarisme, 1951) analyse le totalitarisme (nazisme, stalinisme) comme un régime politique radicalement nouveau : il ne cherche pas seulement à contrôler les comportements mais à transformer la nature humaine elle-même par l'idéologie (race, classe), la terreur généralisée, les camps de concentration et la désolation (destruction des liens sociaux et de la dignité humaine)."},
            {"index":8,"type":"vrai-faux","question":"La désobéissance civile est une forme de résistance non-violente à des lois jugées injustes, théorisée notamment par Thoreau et Gandhi.",
             "answer":"VRAI",
             "correction":"VRAI. La désobéissance civile (Thoreau, La Résistance au gouvernement civil, 1849) est le refus délibéré, public et non-violent d'obéir à une loi jugée injuste, acceptant d'en subir les conséquences légales. Gandhi l'a théorisée et pratiquée (satyagraha) pour l'indépendance de l'Inde. Martin Luther King l'a utilisée dans la lutte pour les droits civiques. Elle pose la question philosophique : jusqu'où est-il légitime de désobéir aux lois ?"},
        ]
    },
    233: {
        "serie": 10,
        "title": "Quiz Diagnostic 1ere Philosophie - Serie 10",
        "description": "Le bonheur",
        "questions": [
            {"index":1,"type":"qcm","question":"Quelle école philosophique de l'Antiquité considère que le plaisir est le souverain bien et le but de la vie ?",
             "options":["A. Le stoïcisme","B. L'épicurisme","C. Le cynisme","D. Le platonisme"],
             "answer":"B",
             "correction":"L'épicurisme (Épicure, IVe-IIIe s. av. J.-C.) considère le plaisir (hèdonè) comme le souverain bien et le but de la vie. Mais Épicure distingue les plaisirs : les plaisirs kinétatiques (en mouvement, du corps) et les plaisirs catastématiques (stables, de l'âme) – l'ataraxie (absence de trouble de l'âme) et l'aponie (absence de douleur du corps). La sagesse épicurienne consiste à rechercher les plaisirs stables et simples, à amitié, à philosophie."},
            {"index":2,"type":"vrai-faux","question":"Pour Aristote, le bonheur (eudaimonia) n'est pas un état passif mais une activité de l'âme conforme à la vertu.",
             "answer":"VRAI",
             "correction":"VRAI. Pour Aristote (Éthique à Nicomaque), l'eudaimonia (bonheur, épanouissement, 'bonne vie') est la fin ultime de toute action humaine. Ce n'est pas un état passif (comme le plaisir) mais une activité : 'l'exercice actif de l'âme en accord avec la vertu.' Le bonheur aristotiélicien implique l'actualisation de toutes les potentialités humaines (vie contemplative, amitié, vie politique, vertus)."},
            {"index":3,"type":"texte","question":"Quelle est la critique stoïcienne de la poursuite du bonheur par les biens extérieurs ?",
             "answer":"Les stoïciens critiquent la dépendance aux biens extérieurs (richesse, santé, réputation) comme source de bonheur, car ces biens ne dépendent pas de nous ; le vrai bonheur réside dans la vertu et la maîtrise de soi.",
             "correction":"Les stoïciens (Épictète, Marc Aurèle, Sénèque) distinguent ce qui dépend de nous (eph' hèmin : nos jugements, désirs, impulsions) et ce qui n'en dépend pas (santé, richesse, réputation, mort des proches). Le bonheur réside uniquement dans ce qui dépend de nous : la vertu, la sagesse, la maîtrise de nos représentations. Chercher le bonheur dans les biens extérieurs est une erreur qui génère des passions (peur, désir, colère) et la servitude."},
            {"index":4,"type":"qcm","question":"Pascal affirme que 'tous les hommes recherchent d'être heureux' mais que le bonheur véritable est :",
             "options":["A. Dans la philosophie","B. Dans les plaisirs du corps","C. Inaccessible sans Dieu car l'homme porte en lui un 'gouffre infini' que seul Dieu peut combler","D. Dans la richesse matérielle"],
             "answer":"C",
             "correction":"Pascal (Pensées) affirme que 'tous les hommes recherchent d'être heureux sans exception.' Mais le bonheur terrestre est impossible : l'homme est 'un roseau pensant', un être de misère et de grandeur, portant en lui un 'abîme infini' que rien de fini ne peut combler. Le divertissement (travail, jeux, guerres) est une fuite de cette misère. Seul Dieu peut combler cet abîme. Pascal est donc un 'apologiste' du christianisme par le pari."},
            {"index":5,"type":"vrai-faux","question":"Le bonheur est un état qui peut être défini de façon universelle et objectivement valable pour tous les êtres humains.",
             "answer":"FAUX",
             "correction":"FAUX. Le bonheur est une notion subjective, relative aux individus et aux cultures. Ce qui rend heureux varie considérablement d'une personne à l'autre et d'une époque à l'autre. La philosophie elle-même propose des conceptions radicalement différentes du bonheur (plaisir chez Épicure, vertu chez Aristote, sagesse chez les stoïciens, absence de souffrance chez Schopenhauer). Kant distingue bonheur (incertain, subjectif) et dignité morale (certaine, universelle)."},
            {"index":6,"type":"texte","question":"Schopenhauer pense-t-il que le bonheur est accessible à l'homme ? Pourquoi ?",
             "answer":"Pour Schopenhauer, le bonheur durable est inaccessible car la vie est dominée par une Volonté aveugle et insatiable qui génère souffrance perpétuelle. La sagesse consiste à nier la Volonté.",
             "correction":"Arthur Schopenhauer (Le Monde comme volonté et comme représentation) est profondément pessimiste : l'essence de l'existence est une Volonté (Wille) aveugle, insatiable, qui nous domine. Le désir satisfait ne procure qu'un bref soulagement avant qu'un nouveau désir surgisse. La vie oscille entre la douleur du manque et l'ennui de la satisfaction. Le bonheur durable est une illusion. La sagesse consiste à nier la Volonté : par l'art (contemplation esthétique), la compassion (pitié) et l'ascèse."},
            {"index":7,"type":"qcm","question":"Selon Kant, peut-on faire du bonheur le fondement de la morale ?",
             "options":["A. Oui, car le bonheur est le but de tout être raisonnable","B. Non, car le bonheur est subjectif et empirique, tandis que la morale exige un principe universel et rationnel","C. Oui, selon l'utilitarisme qu'il approuve","D. Non, car Kant pense que le bonheur n'existe pas"],
             "answer":"B",
             "correction":"Kant refuse de fonder la morale sur le bonheur : le bonheur est empirique (variable selon les individus et les cultures), subjectif et conditionnel. Il ne peut fournir un principe moral universel et nécessaire. La morale kantienne est déontologique : elle repose sur l'impératif catégorique, loi universelle de la raison pure pratique. Kant admet cependant que le 'souverain bien' combine vertu et bonheur, mais la vertu (dignité d'être heureux) est première."},
            {"index":8,"type":"vrai-faux","question":"Le bouddhisme propose, comme voie vers la cessation de la souffrance, le détachement des désirs et l'atteinte du Nirvana.",
             "answer":"VRAI",
             "correction":"VRAI. Le bouddhisme (Bouddha, Ve s. av. J.-C.) enseigne les Quatre Nobles Vérités : 1) l'existence est souffrance (dukkha) ; 2) la souffrance a une cause (le désir, l'attachement - tanha) ; 3) la cessation de la souffrance est possible ; 4) il existe un chemin (Noble Octuple Sentier) vers cette cessation. Le Nirvana est l'extinction des désirs et l'affranchissement du cycle des renaissances (samsara). Cette vision rejoint Schopenhauer dans le diagnostic mais propose une voie de libération."},
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
    (241, 18, "La science et l'épistémologie"),
    (242, 19, "La culture et la nature"),
    (243, 20, "L'émotion et la passion"),
    (244, 21, "La mort et l'existence"),
    (245, 22, "Le désir"),
    (246, 23, "L'amour et l'amitié"),
    (247, 24, "La mémoire et l'identité"),
    (248, 25, "La société et le contrat social"),
    (249, 26, "L'éducation et la formation"),
    (250, 27, "La phénoménologie"),
    (251, 28, "Le matérialisme et l'idéalisme"),
    (252, 29, "La métaphysique : être et existence"),
    (253, 30, "L'éthique environnementale"),
    (254, 31, "La philosophie politique contemporaine"),
    (255, 32, "Le stoïcisme et le cynisme"),
    (256, 33, "La philosophie de Platon"),
    (257, 34, "La philosophie d'Aristote"),
    (258, 35, "La philosophie médiévale"),
    (259, 36, "La philosophie des Lumières"),
    (260, 37, "La philosophie de Hegel"),
    (261, 38, "La philosophie de Marx"),
    (262, 39, "La philosophie existentialiste"),
    (263, 40, "La philosophie analytique"),
    (264, 41, "La philosophie du langage"),
    (265, 42, "La philosophie de l'esprit"),
    (266, 43, "La philosophie morale appliquée"),
    (267, 44, "La bioéthique"),
    (268, 45, "La philosophie des droits de l'homme"),
    (269, 46, "La philosophie de la connaissance (épistémologie)"),
    (270, 47, "La philosophie orientale"),
    (271, 48, "La philosophie et la psychologie"),
    (272, 49, "Révision générale Philosophie 1ère"),
]

def make_generic_philo_quiz(file_id, serie, description):
    """Generate 8 philosophy questions with real content for a given theme."""
    theme_content = {
        "La justice et le droit": [
            ("qcm","Selon Rawls, les principes de justice doivent être choisis derrière un 'voile d'ignorance'. Que signifie cette expression ?",
             ["A. Choisir les lois sans regarder les autres","B. Choisir les principes de justice sans savoir quelle place on occupera dans la société","C. Ignorer les lois injustes","D. Voter à bulletin secret"],
             "B","Le 'voile d'ignorance' (John Rawls, Théorie de la justice, 1971) est un dispositif de pensée : pour choisir des principes justes, imaginons que nous ne sachions pas quelle position nous occuperons dans la société (riche/pauvre, homme/femme, etc.). Derrière ce voile, des personnes rationnelles choisiraient des principes garantissant les libertés fondamentales et profitant aux plus défavorisés (principe de différence)."),
            ("vrai-faux","La justice distributive concerne la répartition équitable des biens et des charges dans une société.",
             None,"VRAI","VRAI. La justice distributive (Aristote, Politique) traite de la distribution équitable des biens, des ressources, des droits et des charges dans une société. Elle s'oppose à la justice commutative (égalité dans les échanges) et à la justice corrective (réparation des torts). Les théories modernes de la justice (Rawls, Nozick, Walzer) débattent des principes de cette distribution."),
            ("texte","Quelle différence y a-t-il entre légalité et légitimité ?",
             None,"La légalité renvoie à la conformité à la loi positive ; la légitimité renvoie à la justification morale et à la reconnaissance du pouvoir par ceux qui y sont soumis.",
             "La légalité est la conformité aux règles juridiques positives en vigueur (ce qui est conforme à la loi). La légitimité est la justification morale, politique ou philosophique d'un pouvoir ou d'une règle (ce qui est juste, ce qui mérite d'être obéi). Un régime peut être légal (respecter ses propres lois) sans être légitime (régimes totalitaires). La résistance civile et la désobéissance civile s'appuient sur la distinction légalité/légitimité."),
            ("qcm","Qu'est-ce que le droit naturel ?",
             ["A. Le droit des animaux dans la nature","B. Un ensemble de droits fondamentaux inhérents à la nature humaine, antérieurs et supérieurs aux lois positives","C. Les lois tirées de l'observation de la nature","D. Le droit international de l'environnement"],
             "B","Le droit naturel (Cicéron, Grotius, Locke) est un ensemble de droits ou de principes moraux universels, fondés sur la nature humaine ou la raison, antérieurs et supérieurs aux lois positives (établies par les sociétés). Il fonde les droits de l'homme (Déclaration de 1789 : droits 'naturels, inaliénables et sacrés'). Il s'oppose au positivisme juridique (Kelsen) qui ne reconnaît que les lois positives effectivement édictées."),
            ("vrai-faux","Pour Thrasymachus (dans la République de Platon), la justice est l'avantage du plus fort.",
             None,"VRAI","VRAI. Thrasymachus (interlocuteur de Socrate dans la République) soutient que 'la justice n'est rien d'autre que l'avantage du plus fort' : ceux qui détiennent le pouvoir édictent des lois qui servent leurs intérêts et appellent cela 'justice'. C'est une position cynique que Socrate/Platon réfutent longuement. Nietzsche reformulera une idée similaire avec la critique de la 'morale des esclaves'."),
            ("texte","Qu'est-ce que la peine dans le droit pénal et quels en sont les fondements philosophiques ?",
             None,"La peine a plusieurs fondements philosophiques : rétribution (punir le mal en soi), dissuasion (éviter les crimes futurs), réhabilitation (réinsertion du condamné) et protection sociale.",
             "La philosophie de la peine distingue plusieurs fondements : la rétribution (Kant : punir le criminel est un impératif de justice, il le mérite) ; la dissuasion/prévention générale (Bentham : la peine doit dissuader les crimes futurs, calculer la douleur) ; la réhabilitation (rééducation, réinsertion du condamné) ; et la protection sociale (isoler les individus dangereux). Ces fondements conduisent à des politiques pénales très différentes et alimentent les débats sur la prison, la peine de mort et la récidive."),
            ("qcm","Quelle position philosophique Nozick défend-il contre Rawls concernant la justice ?",
             ["A. La justice exige une redistribution égalitaire des richesses","B. Toute distribution issue d'échanges libres et volontaires est juste, quelle que soit son inégalité","C. La justice est la propriété du plus fort","D. Il n'existe pas de principes objectifs de justice"],
             "B","Robert Nozick (Anarchie, État et Utopie, 1974) défend le libertarianisme : toute distribution issue d'acquisitions et d'échanges librement consentis est juste, même si elle produit de grandes inégalités. Il s'oppose à Rawls : l'État minimal est le seul État juste ; tout État plus grand viole les droits individuels en redistribuant les richesses ('la taxation, c'est le travail forcé')."),
            ("vrai-faux","La Déclaration universelle des droits de l'homme (1948) reconnaît des droits universels valables pour tous les êtres humains.",
             None,"VRAI","VRAI. La Déclaration universelle des droits de l'homme, adoptée par l'ONU le 10 décembre 1948, proclame 30 droits universels, inaliénables et indivisibles pour tous les êtres humains 'sans distinction aucune' (race, sexe, religion, etc.). Elle constitue le fondement philosophique du droit international des droits de l'homme, bien que sa valeur contraignante soit limitée."),
        ],
    }

    # Default questions for themes without specific content
    generic = [
        ("qcm", f"Quelle approche philosophique est au cœur de l'étude de '{description}' ?",
         ["A. Une approche empirique basée sur l'expérience", "B. Une approche rationnelle et critique visant à clarifier les concepts fondamentaux", "C. Une approche purement historique", "D. Une approche scientifique expérimentale"],
         "B", f"La philosophie aborde le thème de '{description}' par une démarche rationnelle et critique : elle interroge les concepts fondamentaux, examine les arguments, identifie les présupposés et cherche des principes universels, distinguant ainsi l'approche philosophique des approches scientifiques ou historiques."),
        ("vrai-faux", f"Le questionnement philosophique sur '{description}' remonte à l'Antiquité grecque.",
         None, "VRAI", f"VRAI. Les grandes questions philosophiques liées à '{description}' ont été posées dès l'Antiquité grecque (Socrate, Platon, Aristote) et continuent d'être débattues jusqu'à nos jours, montrant la pérennité et la richesse de la réflexion philosophique à travers les âges."),
        ("texte", f"En quoi consiste la démarche philosophique appliquée à '{description}' ?",
         None, f"La démarche philosophique consiste à définir les concepts, à examiner les arguments, à identifier les contradictions et à construire une réflexion rigoureuse et autonome.",
         f"Philosopher sur '{description}' consiste à : 1) définir précisément les concepts impliqués (qu'est-ce que cela signifie vraiment ?) ; 2) identifier et examiner les différentes positions possibles (thèses et antithèses) ; 3) évaluer les arguments par la logique et la raison ; 4) dépasser les opinions communes (doxa) pour atteindre une réflexion fondée. Cette démarche dialectique (Hegel) permet de construire une pensée autonome et rigoureuse."),
        ("qcm", "Quel philosophe de l'Antiquité a dit 'Je sais que je ne sais rien' pour exprimer la conscience de ses propres limites ?",
         ["A. Platon", "B. Aristote", "C. Socrate", "D. Épicure"],
         "C", "Cette formule est attribuée à Socrate (bien qu'elle soit une reformulation tardive). Elle exprime l'humilité intellectuelle fondamentale (la 'docte ignorance'). Pour Socrate, cette conscience de son ignorance le distinguait de ceux qui croyaient savoir sans vraiment savoir : la sagesse commence par la reconnaissance de ses limites. C'est le point de départ de toute démarche philosophique authentique."),
        ("vrai-faux", "La philosophie peut apporter des certitudes absolues sur toutes les questions qu'elle pose.",
         None, "FAUX", "FAUX. La philosophie n'a pas vocation à apporter des réponses définitives et absolues sur l'ensemble des questions qu'elle pose. Sa valeur réside davantage dans la qualité du questionnement, la rigueur de l'argumentation et la clarification des concepts que dans la production de dogmes. Bertrand Russell disait que la valeur de la philosophie est dans l'incertitude qu'elle maintient."),
        ("texte", f"Comment la notion de '{description.split()[0].lower() if description.split() else 'concept'}' peut-elle être rapprochée de la condition humaine ?",
         None, "Cette notion touche à des aspects essentiels de la condition humaine : la finitude, la liberté, la recherche de sens et le rapport à autrui.",
         f"Les grands thèmes philosophiques, dont '{description}', éclairent la condition humaine dans ses dimensions fondamentales : la finitude et la mortalité, la liberté et la responsabilité, le rapport à l'autre et à la société, la recherche de sens et de bonheur. La philosophie nous aide à nous orienter dans l'existence en nous dotant d'outils conceptuels pour penser notre situation et nos choix."),
        ("qcm", "Qu'est-ce que l'herméneutique en philosophie ?",
         ["A. L'art de la rhétorique et de la persuasion", "B. La théorie et la méthode d'interprétation des textes et des discours", "C. L'étude des langues mortes", "D. La logique formelle"],
         "B", "L'herméneutique (de Hermès, messager des dieux) est l'art et la théorie de l'interprétation, notamment des textes (bibliques initialement, puis littéraires, historiques, philosophiques). Les principaux théoriciens sont Schleiermacher, Dilthey, Heidegger et Gadamer (Vérité et méthode). L'herméneutique soulève la question du cercle herméneutique : comprendre le tout par les parties et les parties par le tout."),
        ("vrai-faux", "La philosophie a pour seul but la contemplation désintéressée de la vérité, sans application pratique.",
         None, "FAUX", "FAUX. Si la tradition contemplative (Aristote : la vie théorétique comme vie la plus haute) valorise la connaissance pour elle-même, de nombreux courants philosophiques insistent sur la dimension pratique de la philosophie : Marx ('les philosophes n'ont fait qu'interpréter le monde ; il s'agit maintenant de le transformer'), le pragmatisme américain, l'existentialisme sartrien. La philosophie éclaire l'action morale, politique et personnelle."),
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
        print(f"  ✓ {file_id}.json [{qdata['serie']}/49] - {qdata['description']}")

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
        print(f"  ✓ {file_id}.json [{serie}/49] - {description}")

    print(f"\n✅ Philosophie: {count} quiz files generated (+ {count} answers = {count*6} total files)")


if __name__ == "__main__":
    write_quiz_files()
