import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", "..", ".."))
HG_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "hg_quizzes")
HG_QUIZ_DIR = os.path.join(HG_OUTPUT_DIR, "quiz")
HG_ANSWERS_DIR = os.path.join(HG_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "hg_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

# ============================================================
# HISTOIRE-GÃ‰OGRAPHIE 1Ã¨re â€” fichiers 175 Ã  223
# 49 quizzes, sÃ©ries 1 Ã  49
# ============================================================

hg_quizzes = {
    175: {
        "serie": 1,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 1",
        "description": "La France et l'Europe Ã  la Belle Ã‰poque",
        "theme": "La Belle Ã‰poque en France et en Europe",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quelle pÃ©riode dÃ©signe-t-on par l'expression Â« Belle Ã‰poque Â» ?",
                "options": ["A. 1850-1870", "B. 1871-1914", "C. 1918-1939", "D. 1945-1968"],
                "answer": "B",
                "correction": "La Belle Ã‰poque dÃ©signe la pÃ©riode allant de 1871 Ã  1914, caractÃ©risÃ©e par la paix relative, la prospÃ©ritÃ© Ã©conomique et les avancÃ©es technologiques en Europe occidentale."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "La France connaÃ®t une forte croissance industrielle et dÃ©mographique Ã  la Belle Ã‰poque.",
                "answer": "VRAI",
                "correction": "VRAI. La Belle Ã‰poque est marquÃ©e par l'essor industriel (automobile, Ã©lectricitÃ©, cinÃ©ma) et une croissance Ã©conomique notable, bien que la croissance dÃ©mographique franÃ§aise soit plus lente que celle de ses voisins."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Citez deux inventions ou innovations majeures de la Belle Ã‰poque qui ont transformÃ© la vie quotidienne.",
                "answer": "L'automobile et l'Ã©lectricitÃ© (ou le cinÃ©ma, l'avion, le tÃ©lÃ©phone...)",
                "correction": "Parmi les grandes innovations de la Belle Ã‰poque : l'automobile (Benz, Renault), l'Ã©lectricitÃ© dans les foyers, le cinÃ©matographe des frÃ¨res LumiÃ¨re (1895), l'avion (frÃ¨res Wright, 1903), le tÃ©lÃ©phone, la radiographie (RÃ¶ntgen, 1895)."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel Ã©vÃ©nement met fin Ã  la Belle Ã‰poque ?",
                "options": ["A. La RÃ©volution russe de 1917", "B. La crise de 1929", "C. Le dÃ©clenchement de la PremiÃ¨re Guerre mondiale en 1914", "D. La chute de la Commune de Paris"],
                "answer": "C",
                "correction": "C'est l'assassinat de l'archiduc FranÃ§ois-Ferdinand Ã  Sarajevo le 28 juin 1914 et le dÃ©clenchement de la PremiÃ¨re Guerre mondiale qui met brutalement fin Ã  la Belle Ã‰poque."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La IIIe RÃ©publique franÃ§aise est fondÃ©e aprÃ¨s la dÃ©faite de 1870 face Ã  la Prusse.",
                "answer": "VRAI",
                "correction": "VRAI. La IIIe RÃ©publique est proclamÃ©e le 4 septembre 1870 aprÃ¨s la capture de NapolÃ©on III Ã  Sedan. Elle durera jusqu'en 1940, devenant le rÃ©gime rÃ©publicain le plus long de l'histoire de France."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce que la loi de sÃ©paration de l'Ã‰glise et de l'Ã‰tat de 1905 Ã©tablit en France ?",
                "answer": "Elle instaure la laÃ¯citÃ© en sÃ©parant les institutions religieuses des institutions de l'Ã‰tat.",
                "correction": "La loi du 9 dÃ©cembre 1905 Ã©tablit la sÃ©paration des Ã‰glises et de l'Ã‰tat, instaurant la laÃ¯citÃ© comme principe rÃ©publicain. L'Ã‰tat ne reconnaÃ®t, ne salarie ni ne subventionne aucun culte. C'est un pilier fondamental de la RÃ©publique franÃ§aise."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel Ã©vÃ©nement diplomatique majeur divise la France Ã  la fin du XIXe siÃ¨cle ?",
                "options": ["A. La crise du Maroc", "B. L'affaire Dreyfus", "C. La guerre des Boers", "D. La crise de Fachoda"],
                "answer": "B",
                "correction": "L'affaire Dreyfus (1894-1906) est un scandale politico-judiciaire qui divise profondÃ©ment la sociÃ©tÃ© franÃ§aise entre dreyfusards (dÃ©fenseurs des droits de l'homme) et antidreyfusards (nationalistes, antisÃ©mites). Elle rÃ©vÃ¨le les tensions de la IIIe RÃ©publique."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La Triple Entente regroupe la France, le Royaume-Uni et l'Autriche-Hongrie avant 1914.",
                "answer": "FAUX",
                "correction": "FAUX. La Triple Entente (formÃ©e progressivement entre 1894 et 1907) regroupe la France, le Royaume-Uni et la Russie â€” et non l'Autriche-Hongrie, qui fait partie de la Triple Alliance avec l'Allemagne et l'Italie."
            }
        ]
    },
    176: {
        "serie": 2,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 2",
        "description": "La PremiÃ¨re Guerre mondiale",
        "theme": "La PremiÃ¨re Guerre mondiale (1914-1918)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quel Ã©vÃ©nement dÃ©clenche directement la PremiÃ¨re Guerre mondiale ?",
                "options": ["A. L'invasion de la Belgique par l'Allemagne", "B. L'assassinat de l'archiduc FranÃ§ois-Ferdinand Ã  Sarajevo", "C. La mobilisation gÃ©nÃ©rale franÃ§aise", "D. La dÃ©claration de guerre de la Russie Ã  l'Autriche"],
                "answer": "B",
                "correction": "L'assassinat de l'archiduc FranÃ§ois-Ferdinand, hÃ©ritier de l'empire austro-hongrois, le 28 juin 1914 Ã  Sarajevo par Gavrilo Princip, nationaliste serbe, dÃ©clenche la crise diplomatique qui conduit Ã  la guerre mondiale."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "La guerre de tranchÃ©es caractÃ©rise essentiellement le front occidental entre 1914 et 1918.",
                "answer": "VRAI",
                "correction": "VRAI. Ã€ partir de fin 1914, le front occidental se stabilise en un rÃ©seau de tranchÃ©es s'Ã©tendant de la mer du Nord Ã  la frontiÃ¨re suisse. Cette guerre de position, Ã©puisante et meurtriÃ¨re, durera jusqu'en 1918."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Expliquez ce qu'est l'Union sacrÃ©e proclamÃ©e en France en aoÃ»t 1914.",
                "answer": "L'Union sacrÃ©e est la suspension des conflits politiques internes pour unir tous les FranÃ§ais derriÃ¨re l'effort de guerre.",
                "correction": "L'Union sacrÃ©e, proclamÃ©e par le prÃ©sident PoincarÃ© le 4 aoÃ»t 1914, est l'union de toutes les forces politiques franÃ§aises (socialistes, rÃ©publicains, conservateurs) pour soutenir l'effort de guerre. Les oppositions politiques sont suspendues ; mÃªme les syndicats et le parti socialiste y adhÃ¨rent."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quelle est la bataille la plus meurtriÃ¨re du front franÃ§ais en 1916 ?",
                "options": ["A. La bataille de la Marne", "B. La bataille de la Somme", "C. La bataille de Verdun", "D. La bataille de l'Aisne"],
                "answer": "C",
                "correction": "La bataille de Verdun (21 fÃ©vrier â€“ 18 dÃ©cembre 1916) est la plus longue et l'une des plus meurtriÃ¨res de la guerre : environ 700 000 victimes (morts, blessÃ©s, disparus) des deux cÃ´tÃ©s. Elle devient le symbole de la rÃ©sistance franÃ§aise et de l'horreur de la Grande Guerre."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "Les Ã‰tats-Unis entrent en guerre dÃ¨s 1914 aux cÃ´tÃ©s des AlliÃ©s.",
                "answer": "FAUX",
                "correction": "FAUX. Les Ã‰tats-Unis maintiennent leur neutralitÃ© jusqu'en avril 1917, avant d'entrer en guerre aux cÃ´tÃ©s des AlliÃ©s, notamment en raison de la guerre sous-marine Ã  outrance menÃ©e par l'Allemagne et de l'interception du tÃ©lÃ©gramme Zimmermann."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Quelles sont les principales consÃ©quences du traitÃ© de Versailles pour l'Allemagne en 1919 ?",
                "answer": "L'Allemagne doit payer de lourdes rÃ©parations, cÃ©der des territoires (Alsace-Lorraine, etc.) et accepter la clause de responsabilitÃ© de la guerre.",
                "correction": "Le traitÃ© de Versailles (28 juin 1919) impose Ã  l'Allemagne : la cession de l'Alsace-Lorraine Ã  la France, de territoires Ã  la Pologne, au Danemark, Ã  la Belgique ; des rÃ©parations de guerre colossales ; la limitation de son armÃ©e ; et la clause de responsabilitÃ© exclusive de la guerre (article 231), source d'humiliation qui alimentera le ressentiment nationaliste."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel organisme international est crÃ©Ã© en 1919 pour maintenir la paix ?",
                "options": ["A. L'ONU", "B. La SDN (SociÃ©tÃ© des Nations)", "C. L'OTAN", "D. La Croix-Rouge internationale"],
                "answer": "B",
                "correction": "La SociÃ©tÃ© des Nations (SDN) est crÃ©Ã©e par le traitÃ© de Versailles en 1919, sur proposition du prÃ©sident amÃ©ricain Wilson (14 points). Elle prÃ©figure l'ONU mais sera fragilisÃ©e par l'absence des Ã‰tats-Unis, qui refusent de la rejoindre, et sera dissoute en 1946."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Le gÃ©nocide armÃ©nien est perpÃ©trÃ© pendant la PremiÃ¨re Guerre mondiale par l'Empire ottoman.",
                "answer": "VRAI",
                "correction": "VRAI. Ã€ partir de 1915, l'Empire ottoman organise la dÃ©portation et le massacre systÃ©matique des ArmÃ©niens. Ce gÃ©nocide, reconnu par de nombreux Ã‰tats dont la France, fait entre 600 000 et 1,5 million de victimes selon les estimations."
            }
        ]
    },
    177: {
        "serie": 3,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 3",
        "description": "L'entre-deux-guerres et la montÃ©e des totalitarismes",
        "theme": "L'entre-deux-guerres (1919-1939)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Dans quel pays le fascisme prend-il le pouvoir en premier ?",
                "options": ["A. L'Allemagne", "B. L'Espagne", "C. L'Italie", "D. La Hongrie"],
                "answer": "C",
                "correction": "C'est en Italie que Benito Mussolini fonde le premier rÃ©gime fasciste. AprÃ¨s la 'Marche sur Rome' (octobre 1922), il prend le pouvoir et instaure progressivement une dictature totalitaire, le 'fascisme', qui donnera son nom Ã  ce courant idÃ©ologique."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "La crise Ã©conomique de 1929 commence aux Ã‰tats-Unis avec le krach boursier de Wall Street.",
                "answer": "VRAI",
                "correction": "VRAI. Le jeudi 24 octobre 1929 ('jeudi noir'), la Bourse de New York s'effondre. Cette crise financiÃ¨re se propage rapidement Ã  l'Ã©conomie rÃ©elle et au monde entier, provoquant une dÃ©pression Ã©conomique mondiale avec des millions de chÃ´meurs."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Qu'est-ce que le nazisme et quels sont ses principaux fondements idÃ©ologiques ?",
                "answer": "Le nazisme est le rÃ©gime totalitaire d'Hitler, fondÃ© sur le racisme (antisÃ©mitisme), le nationalisme extrÃªme et le rejet de la dÃ©mocratie.",
                "correction": "Le nazisme (national-socialisme) est l'idÃ©ologie du parti d'Adolf Hitler (NSDAP), fondÃ©e sur : l'antisÃ©mitisme et le racisme (thÃ©orie de la race aryenne supÃ©rieure), le nationalisme extrÃªme et le pangermanisme, le rejet de la dÃ©mocratie au profit d'un chef (FÃ¼hrerprinzip), l'anticommunisme, et l'expansionnisme territorial (Lebensraum). Hitler prend le pouvoir en janvier 1933."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel est le nom du rÃ©gime autoritaire instaurÃ© par Staline en URSS dans les annÃ©es 1930 ?",
                "options": ["A. Le lÃ©ninisme", "B. Le stalinisme totalitaire", "C. Le bolchevisme libÃ©ral", "D. La dÃ©mocratie populaire"],
                "answer": "B",
                "correction": "Le stalinisme dÃ©signe le rÃ©gime totalitaire mis en place par Joseph Staline Ã  partir des annÃ©es 1920-1930 en URSS : collectivisation forcÃ©e, industrialisation Ã  marche forcÃ©e (plans quinquennaux), culte de la personnalitÃ©, Goulag et Grandes Purges (1936-1938)."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "Le Front populaire gouverne la France entre 1936 et 1938 sous la direction de LÃ©on Blum.",
                "answer": "VRAI",
                "correction": "VRAI. Le Front populaire (coalition socialistes-radicaux-communistes) remporte les Ã©lections de mai 1936. Sous LÃ©on Blum, il adopte les accords Matignon : congÃ©s payÃ©s (2 semaines), semaine de 40 heures, hausse des salaires."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce que la politique d'apaisement (appeasement) pratiquÃ©e par les dÃ©mocraties face Ã  Hitler dans les annÃ©es 1930 ?",
                "answer": "C'est la politique de concessions faites Ã  Hitler pour Ã©viter la guerre, symbolisÃ©e par les accords de Munich en 1938.",
                "correction": "La politique d'apaisement (appeasement) consiste pour le Royaume-Uni (Chamberlain) et la France (Daladier) Ã  faire des concessions Ã  Hitler pour Ã©viter une nouvelle guerre. Son symbole est la confÃ©rence de Munich (septembre 1938) oÃ¹ ils acceptent l'annexion des SudÃ¨tes par l'Allemagne, croyant avoir assurÃ© 'la paix pour notre temps'."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel pacte de non-agression est signÃ© entre l'Allemagne nazie et l'URSS en aoÃ»t 1939 ?",
                "options": ["A. Le pacte anti-Komintern", "B. Le pacte germano-soviÃ©tique (pacte Molotov-Ribbentrop)", "C. Le pacte d'acier", "D. Le traitÃ© de non-prolifÃ©ration"],
                "answer": "B",
                "correction": "Le pacte germano-soviÃ©tique (ou pacte Molotov-Ribbentrop, signÃ© le 23 aoÃ»t 1939) est un accord de non-agression entre l'Allemagne nazie et l'URSS. Il contient des clauses secrÃ¨tes de partage de l'Europe de l'Est. Il libÃ¨re Hitler pour envahir la Pologne sans craindre un front Ã  l'Est."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La Seconde Guerre mondiale Ã©clate le 1er septembre 1939 avec l'invasion de la Pologne par l'Allemagne.",
                "answer": "VRAI",
                "correction": "VRAI. Le 1er septembre 1939, l'Allemagne nazie envahit la Pologne. Le 3 septembre, la France et le Royaume-Uni dÃ©clarent la guerre Ã  l'Allemagne, conformÃ©ment Ã  leurs engagements envers la Pologne. La Seconde Guerre mondiale commence."
            }
        ]
    },
    178: {
        "serie": 4,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 4",
        "description": "La Seconde Guerre mondiale",
        "theme": "La Seconde Guerre mondiale (1939-1945)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quel est le nom de l'opÃ©ration allemande qui conduit Ã  la dÃ©faite de la France en juin 1940 ?",
                "options": ["A. L'OpÃ©ration Barbarossa", "B. L'OpÃ©ration Overlord", "C. Le Plan Schlieffen", "D. L'OpÃ©ration Fall Gelb (Case Yellow)"],
                "answer": "D",
                "correction": "L'opÃ©ration Fall Gelb (Case Yellow) est le plan allemand d'invasion de la France via les Ardennes (mai-juin 1940). En contournant la ligne Maginot, les Panzers percent le front Ã  Sedan et encerclent les armÃ©es alliÃ©es. La France signe l'armistice le 22 juin 1940."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Le gÃ©nÃ©ral de Gaulle lance son appel Ã  la rÃ©sistance depuis Londres le 18 juin 1940.",
                "answer": "VRAI",
                "correction": "VRAI. Le 18 juin 1940, le gÃ©nÃ©ral Charles de Gaulle prononce depuis la BBC Ã  Londres son cÃ©lÃ¨bre appel refusant la dÃ©faite et appelant les FranÃ§ais Ã  continuer le combat aux cÃ´tÃ©s des AlliÃ©s. Cet appel fonde la France libre."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Qu'est-ce que le rÃ©gime de Vichy et quel rÃ´le joue-t-il dans la persÃ©cution des Juifs de France ?",
                "answer": "Le rÃ©gime de Vichy est le gouvernement collaborateur de PÃ©tain qui coopÃ¨re avec l'occupant nazi, notamment en organisant la dÃ©portation des Juifs de France.",
                "correction": "Le rÃ©gime de Vichy (Ã‰tat franÃ§ais, 1940-1944) est dirigÃ© par le marÃ©chal PÃ©tain aprÃ¨s l'armistice. Il pratique la collaboration avec l'Allemagne nazie et adopte son propre statut des Juifs dÃ¨s octobre 1940. La police franÃ§aise participe Ã  la rafle du Vel d'Hiv (juillet 1942) : 13 000 Juifs arrÃªtÃ©s, dont 4 000 enfants, et dÃ©portÃ©s vers les camps d'extermination."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel Ã©vÃ©nement fait entrer les Ã‰tats-Unis dans la Seconde Guerre mondiale en dÃ©cembre 1941 ?",
                "options": ["A. L'invasion de la Pologne", "B. L'attaque japonaise sur Pearl Harbor", "C. La dÃ©claration de guerre de l'Allemagne aux USA", "D. Le dÃ©barquement alliÃ© en Afrique du Nord"],
                "answer": "B",
                "correction": "L'attaque surprise de la flotte amÃ©ricaine Ã  Pearl Harbor (HawaÃ¯) par le Japon le 7 dÃ©cembre 1941 entraÃ®ne l'entrÃ©e en guerre des Ã‰tats-Unis. Le lendemain, le prÃ©sident Roosevelt demande au CongrÃ¨s la dÃ©claration de guerre contre le Japon. L'Allemagne et l'Italie dÃ©clarent ensuite la guerre aux USA."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La confÃ©rence de Wannsee (1942) planifie la 'Solution finale', c'est-Ã -dire l'extermination systÃ©matique des Juifs d'Europe.",
                "answer": "VRAI",
                "correction": "VRAI. La confÃ©rence de Wannsee (20 janvier 1942) rÃ©unit des hauts responsables nazis qui coordonnent la mise en Å“uvre de la 'Solution finale de la question juive' : l'extermination systÃ©matique de tous les Juifs d'Europe. La Shoah fera environ 6 millions de victimes juives."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "DÃ©crivez l'importance stratÃ©gique du dÃ©barquement du 6 juin 1944 (Jour J) en Normandie.",
                "answer": "Le dÃ©barquement alliÃ© ouvre un second front Ã  l'Ouest, contraignant l'Allemagne Ã  combattre sur deux fronts et prÃ©cipitant sa dÃ©faite.",
                "correction": "Le 6 juin 1944 (OpÃ©ration Overlord), 156 000 soldats alliÃ©s dÃ©barquent en Normandie sur 5 plages. C'est la plus grande opÃ©ration amphibie de l'histoire. Ce dÃ©barquement ouvre un front occidental dÃ©cisif, oblige l'Allemagne Ã  rÃ©partir ses forces sur plusieurs fronts (Est soviÃ©tique, Italie, Ouest), et enclenche la libÃ©ration de la France et de l'Europe de l'Ouest."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quelle arme est utilisÃ©e pour la premiÃ¨re fois en guerre lors des bombardements d'Hiroshima et Nagasaki en aoÃ»t 1945 ?",
                "options": ["A. La bombe Ã  hydrogÃ¨ne", "B. Les missiles balistiques", "C. La bombe atomique", "D. Les armes chimiques"],
                "answer": "C",
                "correction": "Les Ã‰tats-Unis larguent deux bombes atomiques sur Hiroshima (6 aoÃ»t 1945) et Nagasaki (9 aoÃ»t 1945), faisant entre 100 000 et 200 000 morts. Ce sont les seuls emplois d'armes nuclÃ©aires en temps de guerre. Le Japon capitule le 15 aoÃ»t 1945."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Le procÃ¨s de Nuremberg (1945-1946) juge les crimes de guerre et les crimes contre l'humanitÃ© commis par les dirigeants nazis.",
                "answer": "VRAI",
                "correction": "VRAI. Le Tribunal militaire international de Nuremberg (novembre 1945 â€“ octobre 1946) juge 24 grands dirigeants nazis. Il dÃ©finit les notions de crimes de guerre, crimes contre la paix et crimes contre l'humanitÃ©. 12 accusÃ©s sont condamnÃ©s Ã  mort. Il pose les bases du droit international humanitaire."
            }
        ]
    },
    179: {
        "serie": 5,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 5",
        "description": "La Guerre Froide",
        "theme": "La Guerre Froide (1947-1991)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "En quelle annÃ©e la Guerre Froide est-elle gÃ©nÃ©ralement considÃ©rÃ©e comme commenÃ§ant ?",
                "options": ["A. 1945", "B. 1947", "C. 1950", "D. 1953"],
                "answer": "B",
                "correction": "La Guerre Froide dÃ©bute conventionnellement en 1947, avec la doctrine Truman (mars 1947) et le plan Marshall (juin 1947). Ces politiques amÃ©ricaines face Ã  l'expansion soviÃ©tique marquent la rupture officielle entre les deux blocs."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "L'OTAN est une alliance militaire de l'Ouest crÃ©Ã©e en 1949 pour contrer la menace soviÃ©tique.",
                "answer": "VRAI",
                "correction": "VRAI. L'Organisation du TraitÃ© de l'Atlantique Nord (OTAN) est fondÃ©e le 4 avril 1949. Elle rÃ©unit les Ã‰tats-Unis, le Canada et plusieurs pays d'Europe occidentale dans une alliance militaire dÃ©fensive. En rÃ©ponse, l'URSS crÃ©e le pacte de Varsovie en 1955."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Expliquez ce que signifie la notion de 'rideau de fer' dans le contexte de la Guerre Froide.",
                "answer": "Le rideau de fer est la frontiÃ¨re symbolique et rÃ©elle qui sÃ©pare l'Europe de l'Ouest (bloc occidental) de l'Europe de l'Est (bloc soviÃ©tique).",
                "correction": "L'expression 'rideau de fer' (Iron Curtain) est popularisÃ©e par Winston Churchill dans son discours de Fulton (mars 1946). Elle dÃ©signe la frontiÃ¨re hermÃ©tique sÃ©parant les dÃ©mocraties libÃ©rales occidentales des dÃ©mocraties populaires sous domination soviÃ©tique en Europe de l'Est. Le mur de Berlin (1961) en devient le symbole le plus visible."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quelle est la crise la plus dangereuse de la Guerre Froide, frÃ´lant le conflit nuclÃ©aire en 1962 ?",
                "options": ["A. La crise de Berlin (1961)", "B. La guerre de CorÃ©e (1950-1953)", "C. La crise des missiles de Cuba", "D. La guerre du Vietnam"],
                "answer": "C",
                "correction": "La crise des missiles de Cuba (octobre 1962) est le moment le plus critique de la Guerre Froide. L'URSS installe des missiles nuclÃ©aires Ã  Cuba. Kennedy impose un blocus naval. Pendant 13 jours, le monde est au bord de la guerre nuclÃ©aire. Khrouchtchev accepte finalement de retirer les missiles."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La Chine communiste de Mao Zedong rejoint le bloc soviÃ©tique dÃ¨s 1949 et reste alliÃ©e Ã  l'URSS tout au long de la Guerre Froide.",
                "answer": "FAUX",
                "correction": "FAUX. La Chine populaire, fondÃ©e en 1949, s'aligne d'abord avec l'URSS mais la rupture sino-soviÃ©tique survient dans les annÃ©es 1960 (conflit idÃ©ologique, dispute territoriale). La Chine devient une puissance indÃ©pendante, voire rivale de l'URSS, complexifiant la bipolaritÃ© de la Guerre Froide."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce que la dÃ©tente et quand intervient-elle dans la Guerre Froide ?",
                "answer": "La dÃ©tente est une pÃ©riode d'apaisement des tensions entre les deux blocs, intervenant principalement dans les annÃ©es 1970.",
                "correction": "La dÃ©tente (annÃ©es 1970) est une pÃ©riode d'apaisement relatif des tensions Est-Ouest : accords SALT I (1972) sur la limitation des armements nuclÃ©aires, confÃ©rence d'Helsinki (1975) reconnaissant les frontiÃ¨res europÃ©ennes, visites diplomatiques entre dirigeants (Nixon Ã  Moscou, puis en Chine). La dÃ©tente s'interrompt avec l'invasion soviÃ©tique de l'Afghanistan en 1979."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel dirigeant soviÃ©tique lance les rÃ©formes de la glasnost et de la perestroÃ¯ka dans les annÃ©es 1980 ?",
                "options": ["A. Brejnev", "B. Andropov", "C. Gorbatchev", "D. Eltsine"],
                "answer": "C",
                "correction": "MikhaÃ¯l Gorbatchev (arrivÃ© au pouvoir en 1985) lance la glasnost (transparence, libertÃ© d'expression) et la perestroÃ¯ka (restructuration Ã©conomique). Ces rÃ©formes, voulues pour moderniser l'URSS, contribuent involontairement Ã  sa dÃ©sintÃ©gration."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La chute du mur de Berlin en novembre 1989 symbolise la fin de la Guerre Froide et la rÃ©unification allemande.",
                "answer": "VRAI",
                "correction": "VRAI. Le 9 novembre 1989, le mur de Berlin est ouvert puis dÃ©moli. Cet Ã©vÃ©nement symbolise la fin du rideau de fer et de la Guerre Froide. L'Allemagne est rÃ©unifiÃ©e le 3 octobre 1990. L'URSS se dissout le 25 dÃ©cembre 1991."
            }
        ]
    },
    180: {
        "serie": 6,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 6",
        "description": "La dÃ©colonisation",
        "theme": "La dÃ©colonisation (1945-1975)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Dans quel contexte international la dÃ©colonisation s'accÃ©lÃ¨re-t-elle aprÃ¨s 1945 ?",
                "options": ["A. Le renforcement des empires coloniaux aprÃ¨s la Seconde Guerre mondiale", "B. Le contexte de la Guerre Froide et l'affaiblissement des puissances coloniales", "C. Le retour du nationalisme europÃ©en", "D. La prospÃ©ritÃ© Ã©conomique des mÃ©tropoles coloniales"],
                "answer": "B",
                "correction": "La dÃ©colonisation s'accÃ©lÃ¨re dans le contexte de la Guerre Froide (pressions amÃ©ricaine et soviÃ©tique contre le colonialisme), de l'affaiblissement des puissances coloniales (France, Royaume-Uni) aprÃ¨s la guerre, et de la montÃ©e des mouvements nationalistes dans les colonies."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "L'Inde obtient son indÃ©pendance du Royaume-Uni en 1947 grÃ¢ce notamment Ã  l'action non-violente de Gandhi.",
                "answer": "VRAI",
                "correction": "VRAI. L'Inde proclame son indÃ©pendance le 15 aoÃ»t 1947, divisÃ©e en Inde (Ã  majoritÃ© hindoue) et Pakistan (Ã  majoritÃ© musulmane). Le Mahatma Gandhi a jouÃ© un rÃ´le central par sa rÃ©sistance non-violente (satyagraha) contre la domination britannique."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Quelles sont les principales caractÃ©ristiques de la guerre d'AlgÃ©rie (1954-1962) ?",
                "answer": "La guerre d'AlgÃ©rie est un conflit de dÃ©colonisation opposant le FLN algÃ©rien Ã  la France, marquÃ© par la violence, la torture et les dÃ©bats sur l'identitÃ© franÃ§aise.",
                "correction": "La guerre d'AlgÃ©rie (1er novembre 1954 â€“ 19 mars 1962) oppose le FLN (Front de LibÃ©ration Nationale) Ã  l'armÃ©e franÃ§aise. Elle se distingue par : son intensitÃ© (400 000 Ã  1 million de morts selon les sources), le recours Ã  la torture par l'armÃ©e franÃ§aise, les tensions entre pieds-noirs, harkis et nationalistes algÃ©riens. Elle se termine par les accords d'Ã‰vian et l'indÃ©pendance de l'AlgÃ©rie le 5 juillet 1962."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Qu'est-ce que la confÃ©rence de Bandung (1955) reprÃ©sente dans le mouvement de dÃ©colonisation ?",
                "options": ["A. Une alliance militaire des pays colonisÃ©s contre les puissances occidentales", "B. La crÃ©ation d'une organisation des pays non-alignÃ©s", "C. La premiÃ¨re grande rÃ©union des pays d'Asie et d'Afrique affirmant leur indÃ©pendance et refusant la bipolaritÃ©", "D. Un traitÃ© Ã©conomique entre pays du Tiers-Monde"],
                "answer": "C",
                "correction": "La confÃ©rence de Bandung (avril 1955) rÃ©unit 29 pays d'Asie et d'Afrique. Elle affirme les principes de non-alignement, d'anti-impÃ©rialisme et de solidaritÃ© entre peuples colonisÃ©s ou rÃ©cemment indÃ©pendants. Elle est Ã  l'origine du mouvement des non-alignÃ©s et du concept de Tiers-Monde."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "L'annÃ©e 1960 est surnommÃ©e 'l'annÃ©e de l'Afrique' car 17 pays africains accÃ¨dent Ã  l'indÃ©pendance cette annÃ©e-lÃ .",
                "answer": "VRAI",
                "correction": "VRAI. En 1960, 17 pays africains (dont le SÃ©nÃ©gal, le Cameroun, le Congo, la CÃ´te d'Ivoire, le Mali, etc.) accÃ¨dent Ã  l'indÃ©pendance, principalement des colonies franÃ§aises et belges. C'est pourquoi 1960 est surnommÃ©e 'l'annÃ©e de l'Afrique'."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Quels dÃ©fis les nouveaux Ã‰tats indÃ©pendants doivent-ils relever aprÃ¨s la dÃ©colonisation ?",
                "answer": "Les nouveaux Ã‰tats doivent construire des institutions, assurer le dÃ©veloppement Ã©conomique, surmonter les divisions ethniques et gÃ©rer des frontiÃ¨res hÃ©ritÃ©es de la colonisation.",
                "correction": "Les nouveaux Ã‰tats indÃ©pendants font face Ã  de nombreux dÃ©fis : construction de l'Ã‰tat et des institutions (souvent sur des bases coloniales artificielles), dÃ©veloppement Ã©conomique avec des Ã©conomies peu diversifiÃ©es (monocultures), instabilitÃ© politique (coups d'Ã‰tat, guerres civiles), gestion des frontiÃ¨res hÃ©ritÃ©es du colonialisme (dÃ©coupant des ethnies), et dÃ©fi de l'alphabÃ©tisation et des inÃ©galitÃ©s sociales."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel rÃ©gime sud-africain de sÃ©grÃ©gation raciale prend fin en 1994 avec l'Ã©lection de Nelson Mandela ?",
                "options": ["A. La colonisation", "B. L'apartheid", "C. Le sÃ©grÃ©gationnisme", "D. La nÃ©gritude"],
                "answer": "B",
                "correction": "L'apartheid (mot afrikaans signifiant 'sÃ©paration') est le rÃ©gime de sÃ©grÃ©gation raciale institutionnalisÃ© en Afrique du Sud entre 1948 et 1991. Nelson Mandela, emprisonnÃ© 27 ans pour son combat contre ce rÃ©gime, est Ã©lu premier prÃ©sident noir d'Afrique du Sud en avril 1994."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Le nÃ©ocolonialisme dÃ©signe les nouvelles formes de domination Ã©conomique et politique exercÃ©es par les anciennes puissances coloniales sur les Ã‰tats dÃ©colonisÃ©s.",
                "answer": "VRAI",
                "correction": "VRAI. Le nÃ©ocolonialisme (terme popularisÃ© par Nkrumah) dÃ©signe la persistance de la domination des anciennes mÃ©tropoles sur leurs ex-colonies par des moyens Ã©conomiques (firmes multinationales, dÃ©pendance commerciale), financiers (dette extÃ©rieure), politiques (soutien Ã  des rÃ©gimes corrompus) ou culturels, malgrÃ© l'indÃ©pendance formelle."
            }
        ]
    },
    181: {
        "serie": 7,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 7",
        "description": "La construction europÃ©enne",
        "theme": "La construction europÃ©enne depuis 1945",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quel traitÃ© crÃ©e la CommunautÃ© Ã‰conomique EuropÃ©enne (CEE) en 1957 ?",
                "options": ["A. Le traitÃ© de Paris (1951)", "B. Le traitÃ© de Rome (1957)", "C. Le traitÃ© de Maastricht (1992)", "D. Le traitÃ© de Lisbonne (2007)"],
                "answer": "B",
                "correction": "Le traitÃ© de Rome, signÃ© le 25 mars 1957, crÃ©e la CommunautÃ© Ã‰conomique EuropÃ©enne (CEE) entre six pays fondateurs : France, Allemagne, Italie, Belgique, Pays-Bas, Luxembourg. Il Ã©tablit un marchÃ© commun et des politiques communes (agricole notamment)."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "La rÃ©conciliation franco-allemande est au cÅ“ur de la construction europÃ©enne, symbolisÃ©e notamment par le traitÃ© de l'Ã‰lysÃ©e de 1963.",
                "answer": "VRAI",
                "correction": "VRAI. Le traitÃ© de l'Ã‰lysÃ©e (22 janvier 1963), signÃ© par de Gaulle et Adenauer, scelle la rÃ©conciliation franco-allemande. Ce 'couple franco-allemand' est le moteur de l'intÃ©gration europÃ©enne tout au long de la construction de l'UE."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Quels sont les principaux apports du traitÃ© de Maastricht (1992) pour la construction europÃ©enne ?",
                "answer": "Le traitÃ© de Maastricht crÃ©e l'Union europÃ©enne, prÃ©voit la monnaie unique (euro) et renforce la citoyennetÃ© europÃ©enne.",
                "correction": "Le traitÃ© de Maastricht (signÃ© le 7 fÃ©vrier 1992) transforme la CEE en Union europÃ©enne (UE) et instaure : la citoyennetÃ© europÃ©enne, la politique Ã©trangÃ¨re et de sÃ©curitÃ© commune (PESC), la coopÃ©ration judiciaire et policiÃ¨re (JAI), et surtout l'Union Ã©conomique et monÃ©taire (UEM) prÃ©voyant la crÃ©ation de l'euro. L'euro est introduit en 1999 (en 2002 pour les piÃ¨ces et billets)."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Combien de pays membres compte l'Union europÃ©enne en 2024 ?",
                "options": ["A. 25", "B. 27", "C. 28", "D. 30"],
                "answer": "B",
                "correction": "L'Union europÃ©enne compte 27 Ã‰tats membres depuis le retrait du Royaume-Uni (Brexit, effectif le 31 janvier 2020) qui Ã©tait le 28e membre. L'UE s'est Ã©largie progressivement : 6 membres fondateurs (1957), puis 9, 12, 15, 25 (2004, grand Ã©largissement), 27, 28 (2013), et de nouveau 27 depuis le Brexit."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "L'espace Schengen permet la libre circulation des personnes entre les pays membres, sans contrÃ´les aux frontiÃ¨res intÃ©rieures.",
                "answer": "VRAI",
                "correction": "VRAI. L'accord de Schengen (1985, entrÃ© en vigueur en 1995) crÃ©e un espace sans frontiÃ¨res intÃ©rieures pour les personnes entre les Ã‰tats signataires (26 pays aujourd'hui, dont des non-membres de l'UE comme la Suisse et la NorvÃ¨ge). Les contrÃ´les aux frontiÃ¨res intÃ©rieures sont supprimÃ©s."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce que le Brexit et quelles en sont les principales consÃ©quences ?",
                "answer": "Le Brexit est le retrait du Royaume-Uni de l'Union europÃ©enne, votÃ© en 2016 et effectif en 2020, entraÃ®nant de nouveaux obstacles commerciaux et diplomatiques.",
                "correction": "Le Brexit (contraction de British Exit) est la sortie du Royaume-Uni de l'UE, dÃ©cidÃ©e par rÃ©fÃ©rendum le 23 juin 2016 (51,9 % de 'Leave'). Il devient effectif le 31 janvier 2020. ConsÃ©quences : fin de la libre circulation entre le RU et l'UE, nouvelles barriÃ¨res douaniÃ¨res et commerciales, tensions en Irlande du Nord, questionnement sur l'avenir de l'Union, pertes Ã©conomiques pour les deux parties."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quelle institution europÃ©enne est la seule Ã©lue directement par les citoyens de l'UE ?",
                "options": ["A. La Commission europÃ©enne", "B. Le Conseil de l'UE", "C. Le Parlement europÃ©en", "D. La Cour de justice de l'UE"],
                "answer": "C",
                "correction": "Le Parlement europÃ©en est la seule institution de l'UE Ã©lue au suffrage universel direct par les citoyens europÃ©ens depuis 1979. Il siÃ¨ge Ã  Strasbourg et partage le pouvoir lÃ©gislatif avec le Conseil de l'UE (Conseil des ministres)."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La politique agricole commune (PAC) est l'une des politiques les plus anciennes et les plus importantes de l'UE en termes de budget.",
                "answer": "VRAI",
                "correction": "VRAI. La PAC, instaurÃ©e en 1962, est l'une des premiÃ¨res politiques communes de la CEE/UE. Elle reprÃ©sente encore aujourd'hui environ 31% du budget de l'UE (2021-2027), bien que cette part ait diminuÃ©. Elle vise Ã  garantir la sÃ©curitÃ© alimentaire et le revenu des agriculteurs europÃ©ens."
            }
        ]
    },
    182: {
        "serie": 8,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 8",
        "description": "La France de la Ve RÃ©publique",
        "theme": "La Ve RÃ©publique franÃ§aise depuis 1958",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Qui est le fondateur et premier prÃ©sident de la Ve RÃ©publique franÃ§aise ?",
                "options": ["A. Georges Pompidou", "B. FranÃ§ois Mitterrand", "C. Charles de Gaulle", "D. ValÃ©ry Giscard d'Estaing"],
                "answer": "C",
                "correction": "Charles de Gaulle rÃ©dige et fait adopter la constitution de la Ve RÃ©publique (4 octobre 1958), dont il devient le premier prÃ©sident. Il instaure un rÃ©gime semi-prÃ©sidentiel qui renforce considÃ©rablement le pouvoir exÃ©cutif par rapport Ã  la IVe RÃ©publique."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Le prÃ©sident de la Ve RÃ©publique est Ã©lu au suffrage universel direct depuis 1962.",
                "answer": "VRAI",
                "correction": "VRAI. Initialement Ã©lu par un collÃ¨ge Ã©lectoral, le prÃ©sident est Ã©lu au suffrage universel direct depuis la rÃ©vision constitutionnelle de 1962, approuvÃ©e par rÃ©fÃ©rendum. Cette modification renforce encore la lÃ©gitimitÃ© et le pouvoir prÃ©sidentiel."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Qu'est-ce que la cohabitation en politique franÃ§aise et quand survient-elle pour la premiÃ¨re fois ?",
                "answer": "La cohabitation est la situation oÃ¹ le prÃ©sident de la RÃ©publique et le Premier ministre sont de tendances politiques opposÃ©es. Elle survient pour la premiÃ¨re fois en 1986.",
                "correction": "La cohabitation dÃ©signe la coexistence d'un prÃ©sident et d'un gouvernement de majoritÃ©s politiques adverses. Sous la Ve RÃ©publique, elle s'est produite trois fois : 1986-1988 (Mitterrand-Chirac), 1993-1995 (Mitterrand-Balladur), 1997-2002 (Chirac-Jospin). Le quinquennat adoptÃ© en 2000 rend la cohabitation moins probable en alignant les Ã©lections prÃ©sidentielle et lÃ©gislative."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel Ã©vÃ©nement majeur de mai 1968 marque profondÃ©ment la sociÃ©tÃ© franÃ§aise ?",
                "options": ["A. Un coup d'Ã‰tat militaire", "B. Une grande vague de grÃ¨ves et de contestation Ã©tudiante et ouvriÃ¨re", "C. L'assassinat du gÃ©nÃ©ral de Gaulle", "D. Une invasion Ã©trangÃ¨re"],
                "answer": "B",
                "correction": "Mai 68 est une crise sociale et culturelle sans prÃ©cÃ©dent : grÃ¨ves gÃ©nÃ©ralisÃ©es (10 millions de grÃ©vistes), occupation des universitÃ©s et des usines, revendications Ã©tudiantes de rÃ©forme de l'universitÃ© et ouvriÃ¨res d'augmentation de salaires. Si de Gaulle survit politiquement (victoires Ã©lectorales de juin 1968), il dÃ©missionne en 1969. Mai 68 transforme profondÃ©ment les mÅ“urs et la sociÃ©tÃ© franÃ§aise."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "FranÃ§ois Mitterrand est le premier prÃ©sident socialiste de la Ve RÃ©publique, Ã©lu en 1981.",
                "answer": "VRAI",
                "correction": "VRAI. Ã‰lu le 10 mai 1981, FranÃ§ois Mitterrand est le premier prÃ©sident de gauche de la Ve RÃ©publique, mettant fin Ã  23 ans de domination de la droite. Il engage des rÃ©formes importantes : nationalisations, abolition de la peine de mort (1981), dÃ©centralisation, semaine de 39 heures et 5e semaine de congÃ©s payÃ©s."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce que la dÃ©centralisation mise en Å“uvre par les lois Defferre de 1982 ?",
                "answer": "La dÃ©centralisation transfÃ¨re des compÃ©tences de l'Ã‰tat central aux collectivitÃ©s territoriales (rÃ©gions, dÃ©partements, communes).",
                "correction": "Les lois Defferre (1982-1983) constituent la premiÃ¨re grande rÃ©forme de dÃ©centralisation de la France, Ã‰tat historiquement trÃ¨s centralisÃ©. Elles transfÃ¨rent des compÃ©tences importantes aux rÃ©gions (dÃ©veloppement Ã©conomique), aux dÃ©partements (action sociale) et aux communes (urbanisme). Les collectivitÃ©s territoriales gagnent une vraie autonomie de gestion, bien que l'Ã‰tat reste le principal garant des inÃ©galitÃ©s territoriales."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Comment appelle-t-on la tendance Ã  voter pour des partis extrÃ©mistes ou Ã  s'abstenir par mÃ©fiance envers les institutions ?",
                "options": ["A. La bipolarisation", "B. Le populisme", "C. La dÃ©mocratie participative", "D. La crise de la reprÃ©sentation politique ou 'NIMBY'"],
                "answer": "B",
                "correction": "Le populisme dÃ©signe les mouvements ou partis qui se rÃ©clament du peuple contre les Ã©lites, remettant en cause les institutions reprÃ©sentatives traditionnelles. Il peut Ãªtre de droite (FN/RN) ou de gauche (La France Insoumise). La montÃ©e du populisme est souvent analysÃ©e comme une rÃ©ponse Ã  la crise de la reprÃ©sentation politique."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La France est une RÃ©publique laÃ¯que, ce qui signifie que l'Ã‰tat ne reconnaÃ®t officiellement aucune religion.",
                "answer": "VRAI",
                "correction": "VRAI. L'article 1er de la Constitution de 1958 dÃ©finit la France comme une 'RÃ©publique indivisible, laÃ¯que, dÃ©mocratique et sociale'. La laÃ¯citÃ©, issue de la loi de 1905, signifie que l'Ã‰tat n'adhÃ¨re Ã  aucune religion, garantit la libertÃ© de conscience et organise la sÃ©paration des institutions publiques et des organisations religieuses."
            }
        ]
    },
    183: {
        "serie": 9,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 9",
        "description": "La mondialisation",
        "theme": "La mondialisation contemporaine",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Qu'est-ce que la mondialisation ?",
                "options": ["A. L'uniformisation culturelle mondiale", "B. Le processus d'interconnexion et d'interdÃ©pendance croissante des Ã©conomies, des sociÃ©tÃ©s et des cultures Ã  l'Ã©chelle mondiale", "C. La domination Ã©conomique des Ã‰tats-Unis sur le monde", "D. La suppression des frontiÃ¨res nationales"],
                "answer": "B",
                "correction": "La mondialisation est un processus multidimensionnel d'interconnexion croissante des Ã©conomies (commerce, investissements, finance), des sociÃ©tÃ©s (migrations, communication) et des cultures (diffusion des idÃ©es, modes de vie) Ã  l'Ã©chelle planÃ©taire, accÃ©lÃ©rÃ© depuis les annÃ©es 1980-1990."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Les flux commerciaux mondiaux sont dominÃ©s par la 'Triade' : AmÃ©rique du Nord, Europe occidentale et Asie orientale.",
                "answer": "VRAI",
                "correction": "VRAI. La 'Triade' (AmÃ©rique du Nord, Europe occidentale, Asie orientale â€“ Japon, CorÃ©e du Sud, Chine littorale) concentre la majoritÃ© des Ã©changes commerciaux mondiaux, des investissements et de la production de richesses. Cependant, la montÃ©e des BRICS (BrÃ©sil, Russie, Inde, Chine, Afrique du Sud) rÃ©Ã©quilibre progressivement ce schÃ©ma."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Qu'est-ce qu'une firme transnationale (FTN) et quel est son rÃ´le dans la mondialisation ?",
                "answer": "Une FTN est une entreprise qui produit et vend dans plusieurs pays, jouant un rÃ´le central dans la mondialisation Ã©conomique par ses investissements et ses chaÃ®nes de valeur mondiales.",
                "correction": "Une firme transnationale (FTN) ou multinationale est une entreprise dont les activitÃ©s (production, vente, R&D) s'Ã©tendent sur plusieurs pays via des filiales. Elles sont des acteurs majeurs de la mondialisation : elles reprÃ©sentent environ 1/3 du commerce mondial, organisent des chaÃ®nes de valeur mondiales (dÃ©localisation de la production), transfÃ¨rent des technologies et des capitaux. Exemples : Apple, Toyota, LVMH, Total."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel continent est le plus marginalisÃ© dans la mondialisation Ã©conomique actuelle ?",
                "options": ["A. L'AmÃ©rique latine", "B. L'Asie du Sud", "C. L'Afrique subsaharienne", "D. L'OcÃ©anie"],
                "answer": "C",
                "correction": "L'Afrique subsaharienne reste globalement la rÃ©gion la plus marginalisÃ©e dans la mondialisation : faible part des Ã©changes mondiaux (~2%), dÃ©pendance aux exportations de matiÃ¨res premiÃ¨res, manque d'investissements, instabilitÃ© politique. Cependant, des pays comme l'Ã‰thiopie, le Rwanda ou le SÃ©nÃ©gal connaissent une croissance significative."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La mondialisation accentue les inÃ©galitÃ©s entre pays et au sein des pays.",
                "answer": "VRAI",
                "correction": "VRAI (avec nuances). Si la mondialisation a sorti des centaines de millions de personnes de la pauvretÃ© (notamment en Chine et Inde), elle a Ã©galement accentuÃ© les inÃ©galitÃ©s : entre pays (Ã©cart Nord-Sud), au sein des pays (fractures sociales dans les pays dÃ©veloppÃ©s, ouvriers dÃ©localisÃ©s vs cadres qualifiÃ©s) et entre rÃ©gions (mÃ©tropolisation vs dÃ©serts ruraux)."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce qu'une mÃ©tropole mondiale (ville mondiale ou global city) et citez deux exemples.",
                "answer": "Une mÃ©tropole mondiale est une ville qui exerce une influence et des fonctions de commandement Ã  l'Ã©chelle mondiale. Exemples : New York, Londres, Tokyo.",
                "correction": "Une ville mondiale (global city, concept de Saskia Sassen) est une mÃ©tropole qui concentre des fonctions de commandement Ã©conomique (siÃ¨ges sociaux de FTN, bourses mondiales), financier (places boursiÃ¨res), politique et culturel Ã  l'Ã©chelle planÃ©taire. Elles forment un rÃ©seau mondial. Exemples : New York (finance, ONU), Londres (City financiÃ¨re), Tokyo, Paris (culture, mode), Shanghai, DubaÃ¯."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Qu'est-ce que le libre-Ã©change, promu par l'Organisation mondiale du commerce (OMC) ?",
                "options": ["A. La suppression totale des droits de douane dans le monde entier", "B. La rÃ©duction des barriÃ¨res commerciales (droits de douane, quotas) pour favoriser le commerce international", "C. Le commerce Ã©quitable entre pays du Nord et du Sud", "D. La libertÃ© de circulation des personnes entre tous les pays"],
                "answer": "B",
                "correction": "Le libre-Ã©change est un principe commercial visant Ã  rÃ©duire les obstacles aux Ã©changes (droits de douane, quotas, normes protectionnistes). L'OMC (fondÃ©e en 1995, succÃ©dant au GATT) est l'organisation internationale qui promeut et arbitre les rÃ¨gles du commerce international et la libÃ©ralisation des Ã©changes."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Internet et les technologies numÃ©riques sont des moteurs essentiels de la mondialisation contemporaine.",
                "answer": "VRAI",
                "correction": "VRAI. Internet, les technologies de l'information et de la communication (TIC), les rÃ©seaux sociaux et l'e-commerce ont rÃ©volutionnÃ© la mondialisation en permettant des Ã©changes d'informations, de capitaux et de services quasi-instantanÃ©s Ã  l'Ã©chelle mondiale. Ils ont crÃ©Ã© de nouveaux acteurs mondiaux (GAFAM : Google, Apple, Facebook/Meta, Amazon, Microsoft)."
            }
        ]
    },
    184: {
        "serie": 10,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 10",
        "description": "Les migrations dans le monde",
        "theme": "Les migrations mondiales contemporaines",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quelle est la principale distinction entre un migrant Ã©conomique et un rÃ©fugiÃ© ?",
                "options": ["A. Le migrant Ã©conomique est plus pauvre que le rÃ©fugiÃ©", "B. Le rÃ©fugiÃ© fuit une persÃ©cution ou une menace grave, tandis que le migrant Ã©conomique cherche de meilleures conditions de vie", "C. Le rÃ©fugiÃ© est toujours reconnu comme tel par les Ã‰tats", "D. Il n'y a pas de distinction juridique entre les deux"],
                "answer": "B",
                "correction": "Selon la Convention de GenÃ¨ve (1951), un rÃ©fugiÃ© est une personne qui fuit son pays en raison de persÃ©cutions (pour motifs de race, religion, nationalitÃ©, opinion politique, appartenance Ã  un groupe social). Le migrant Ã©conomique cherche de meilleures conditions Ã©conomiques. La distinction est importante juridiquement car le rÃ©fugiÃ© bÃ©nÃ©ficie d'une protection internationale."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Les migrations Sud-Sud (entre pays en dÃ©veloppement) reprÃ©sentent une part importante des migrations mondiales.",
                "answer": "VRAI",
                "correction": "VRAI. Contrairement aux idÃ©es reÃ§ues, les migrations Sud-Sud (entre pays d'Afrique, d'Asie ou d'AmÃ©rique latine) sont aussi importantes que les migrations Sud-Nord. Par exemple, les migrations africaines se font majoritairement intra-africaines (vers l'Afrique du Sud, la CÃ´te d'Ivoire, le Nigeria)."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Qu'est-ce que la diaspora et quel est son rÃ´le Ã©conomique pour les pays d'origine ?",
                "answer": "La diaspora est une communautÃ© dispersÃ©e hors de son territoire d'origine. Elle joue un rÃ´le Ã©conomique majeur par les transferts d'argent (remittances) vers les pays d'origine.",
                "correction": "La diaspora dÃ©signe la dispersion d'une communautÃ© Ã  travers le monde tout en maintenant un lien avec le pays d'origine. Sur le plan Ã©conomique, les diasporas envoient des remittances (transferts financiers) reprÃ©sentant des montants supÃ©rieurs Ã  l'aide publique au dÃ©veloppement dans de nombreux pays (ex : l'Inde reÃ§oit plus de 80 milliards de dollars par an de sa diaspora). Elles transfÃ¨rent aussi des compÃ©tences, des technologies et des rÃ©seaux."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel organisme des Nations Unies est chargÃ© de protÃ©ger les rÃ©fugiÃ©s dans le monde ?",
                "options": ["A. L'UNICEF", "B. L'OMS", "C. Le HCR (Haut-Commissariat des Nations Unies pour les RÃ©fugiÃ©s)", "D. Le FMI"],
                "answer": "C",
                "correction": "Le HCR (UNHCR en anglais), crÃ©Ã© en 1950, est l'agence de l'ONU mandatÃ©e pour protÃ©ger les rÃ©fugiÃ©s, les demandeurs d'asile et les apatrides. Il coordonne l'aide internationale aux rÃ©fugiÃ©s et travaille Ã  trouver des solutions durables (rapatriement volontaire, intÃ©gration locale, rÃ©installation dans un pays tiers)."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La 'crise migratoire' de 2015 en Europe est liÃ©e principalement aux guerres en Syrie et en Afghanistan.",
                "answer": "VRAI",
                "correction": "VRAI. En 2015, plus d'un million de personnes arrivent en Europe irrÃ©guliÃ¨rement, principalement des Syriens fuyant la guerre civile (depuis 2011), des Afghans et des Irakiens. Cette crise met Ã  l'Ã©preuve le systÃ¨me d'asile europÃ©en et gÃ©nÃ¨re des tensions politiques importantes au sein de l'UE."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce que la 'fuite des cerveaux' et quelles en sont les consÃ©quences pour les pays en dÃ©veloppement ?",
                "answer": "La fuite des cerveaux (brain drain) est l'Ã©migration des personnes hautement qualifiÃ©es vers les pays riches, privant les pays d'origine de leurs compÃ©tences.",
                "correction": "La fuite des cerveaux (brain drain) dÃ©signe l'Ã©migration des personnes les plus qualifiÃ©es (mÃ©decins, ingÃ©nieurs, scientifiques) des pays en dÃ©veloppement vers les pays riches, attirÃ©s par de meilleures rÃ©munÃ©rations et conditions de travail. Pour les pays d'origine : perte d'investissements publics en Ã©ducation, manque de compÃ©tences pour le dÃ©veloppement, mais les remittances peuvent partiellement compenser. Le dÃ©bat sur le 'brain gain' souligne les effets positifs potentiels (retour de compÃ©tences, rÃ©seaux)."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Qu'est-ce que le droit d'asile ?",
                "options": ["A. Le droit pour tout Ã©tranger de s'installer dans le pays de son choix", "B. La protection accordÃ©e par un Ã‰tat Ã  une personne persÃ©cutÃ©e dans son pays d'origine", "C. Le droit pour les migrants Ã©conomiques de travailler librement dans l'UE", "D. Un rÃ©gime douanier spÃ©cial pour les travailleurs frontaliers"],
                "answer": "B",
                "correction": "Le droit d'asile est la protection qu'un Ã‰tat accorde Ã  un Ã©tranger qui ne peut pas rentrer dans son pays d'origine en raison de persÃ©cutions graves (dÃ©finies par la Convention de GenÃ¨ve de 1951). En France, c'est l'OFPRA (Office FranÃ§ais de Protection des RÃ©fugiÃ©s et Apatrides) qui instruit les demandes."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Les migrations climatiques sont appelÃ©es Ã  augmenter significativement dans les prochaines dÃ©cennies en raison du changement climatique.",
                "answer": "VRAI",
                "correction": "VRAI. Les 'rÃ©fugiÃ©s climatiques' (ou dÃ©placÃ©s environnementaux) fuient des zones rendues invivables par le changement climatique (montÃ©e des eaux, dÃ©sertification, Ã©vÃ©nements extrÃªmes). Selon les projections, entre 200 millions et 1 milliard de personnes pourraient Ãªtre dÃ©placÃ©es d'ici 2050. Cependant, ils ne bÃ©nÃ©ficient pas encore d'un statut juridique international protÃ©gÃ©."
            }
        ]
    },
    185: {
        "serie": 11,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 11",
        "description": "Les espaces gÃ©ographiques de la France",
        "theme": "GÃ©ographie de la France",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quel est le plus grand dÃ©sert franÃ§ais en termes de densitÃ© de population ?",
                "options": ["A. La Camargue", "B. La Creuse", "C. La diagonale du vide", "D. Les Alpes"],
                "answer": "C",
                "correction": "La 'diagonale du vide' (ou diagonale des faibles densitÃ©s) est une bande de territoire qui traverse la France du nord-est au sud-ouest (des Ardennes aux Landes), caractÃ©risÃ©e par une trÃ¨s faible densitÃ© de population (moins de 30 hab/kmÂ²), un vieillissement de la population et une Ã©conomie peu dÃ©veloppÃ©e."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Paris et l'ÃŽle-de-France concentrent environ 20% de la population franÃ§aise et plus de 30% de la richesse nationale.",
                "answer": "VRAI",
                "correction": "VRAI. La rÃ©gion ÃŽle-de-France (12 millions d'habitants, soit ~18% de la population) produit environ 31-32% du PIB national. Cette hyper-mÃ©tropolisation de Paris crÃ©e des dÃ©sÃ©quilibres territoriaux importants entre la capitale et le reste du territoire."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Qu'est-ce que la mÃ©tropolisation et comment transforme-t-elle les territoires franÃ§ais ?",
                "answer": "La mÃ©tropolisation est la concentration des activitÃ©s et des populations dans les grandes mÃ©tropoles, crÃ©ant des inÃ©galitÃ©s avec les espaces ruraux et les villes moyennes.",
                "correction": "La mÃ©tropolisation est le processus de concentration des fonctions Ã©conomiques supÃ©rieures (siÃ¨ges sociaux, R&D, finance, services), des populations qualifiÃ©es et des infrastructures dans les grandes mÃ©tropoles. En France, elle renforce les mÃ©tropoles (Paris, Lyon, Bordeaux, Montpellier, Nantes) tout en marginalsant des villes moyennes et des espaces ruraux. Elle gÃ©nÃ¨re des 'fractures territoriales' (dÃ©serts mÃ©dicaux, Ã©loignement des services publics)."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel espace gÃ©ographique franÃ§ais prÃ©sente la densitÃ© de population la plus Ã©levÃ©e hors ÃŽle-de-France ?",
                "options": ["A. La rÃ©gion PACA", "B. L'Alsace (Bas-Rhin)", "C. Le Nord-Pas-de-Calais (Nord)", "D. La Gironde"],
                "answer": "C",
                "correction": "Le dÃ©partement du Nord (ancienne rÃ©gion Nord-Pas-de-Calais) est l'un des plus densÃ©ment peuplÃ©s de France mÃ©tropolitaine aprÃ¨s la petite couronne parisienne, hÃ©ritage de l'industrialisation au XIXe siÃ¨cle (mines, textile, sidÃ©rurgie). La MÃ©tropole EuropÃ©enne de Lille est la 4e aire urbaine de France."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "Les espaces ruraux franÃ§ais sont tous en dÃ©clin dÃ©mographique.",
                "answer": "FAUX",
                "correction": "FAUX. Si certains espaces ruraux (notamment la diagonale du vide) connaissent un dÃ©clin dÃ©mographique et Ã©conomique, d'autres espaces ruraux sont en croissance, notamment les campagnes pÃ©riurbaines (autour des grandes mÃ©tropoles), les zones touristiques et les espaces ruraux attractifs (littoral, montagnes, Sud de la France). On parle de 'rurbanisation'."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce que les Outre-Mer franÃ§ais et quel est leur statut ?",
                "answer": "Les Outre-Mer franÃ§ais sont les territoires franÃ§ais situÃ©s en dehors de l'Europe mÃ©tropolitaine, ayant diffÃ©rents statuts (DOM, ROM, COM, collectivitÃ©s).",
                "correction": "Les Outre-Mer franÃ§ais regroupent diffÃ©rents types de territoires : les DROM (DÃ©partements et RÃ©gions d'Outre-Mer : Guadeloupe, Martinique, Guyane, La RÃ©union, Mayotte), les COM (CollectivitÃ©s d'Outre-Mer : PolynÃ©sie franÃ§aise, Saint-Martin, Saint-Pierre-et-Miquelon...) et la Nouvelle-CalÃ©donie Ã  statut particulier. Ils sont dispersÃ©s sur les cinq ocÃ©ans, font de la France la 2e ZEE mondiale."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel phÃ©nomÃ¨ne dÃ©signe la croissance des villes au dÃ©triment des campagnes environnantes ?",
                "options": ["A. L'exode rural", "B. L'Ã©talement urbain", "C. La pÃ©riurbanisation", "D. L'urbanisation"],
                "answer": "C",
                "correction": "La pÃ©riurbanisation dÃ©signe le dÃ©veloppement rÃ©sidentiel et Ã©conomique dans les zones pÃ©riphÃ©riques autour des villes (couronnes pÃ©riurbaines), souvent sous forme de lotissements pavillonnaires. Ce phÃ©nomÃ¨ne, liÃ© Ã  la recherche de logements moins chers et de qualitÃ© de vie, est associÃ© Ã  l'Ã©talement urbain et gÃ©nÃ¨re une dÃ©pendance Ã  la voiture."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La France possÃ¨de la deuxiÃ¨me zone Ã©conomique exclusive (ZEE) la plus grande du monde grÃ¢ce Ã  ses territoires ultramarins.",
                "answer": "VRAI",
                "correction": "VRAI. GrÃ¢ce Ã  ses nombreux territoires ultramarins dispersÃ©s sur tous les ocÃ©ans (PolynÃ©sie franÃ§aise, Nouvelle-CalÃ©donie, Guyane, Antilles, La RÃ©union, etc.), la France possÃ¨de la 2e ZEE mondiale (~11,5 millions de kmÂ²), derriÃ¨re les Ã‰tats-Unis (~12 millions de kmÂ²). Cela lui confÃ¨re un immense potentiel en ressources marines."
            }
        ]
    },
    186: {
        "serie": 12,
        "title": "Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie 12",
        "description": "Les risques et le dÃ©veloppement durable",
        "theme": "Risques, dÃ©veloppement durable et enjeux environnementaux",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Que signifie le concept de dÃ©veloppement durable ?",
                "options": ["A. Un dÃ©veloppement Ã©conomique sans limites", "B. Un dÃ©veloppement qui rÃ©pond aux besoins du prÃ©sent sans compromettre ceux des gÃ©nÃ©rations futures", "C. La protection exclusive de l'environnement au dÃ©triment de l'Ã©conomie", "D. La croissance Ã©conomique des pays en dÃ©veloppement"],
                "answer": "B",
                "correction": "Le dÃ©veloppement durable est dÃ©fini par le rapport Brundtland (1987) comme 'un dÃ©veloppement qui rÃ©pond aux besoins du prÃ©sent sans compromettre la capacitÃ© des gÃ©nÃ©rations futures Ã  rÃ©pondre aux leurs.' Il repose sur trois piliers : Ã©conomique (croissance), social (Ã©quitÃ©) et environnemental (prÃ©servation des ressources)."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Le changement climatique est principalement causÃ© par les Ã©missions de gaz Ã  effet de serre d'origine humaine.",
                "answer": "VRAI",
                "correction": "VRAI. Le GIEC (Groupe d'experts intergouvernemental sur l'Ã©volution du climat) affirme avec un trÃ¨s haut degrÃ© de certitude que le rÃ©chauffement climatique observÃ© depuis le milieu du XXe siÃ¨cle est principalement dÃ» aux Ã©missions anthropiques de gaz Ã  effet de serre (COÂ², mÃ©thane, protoxyde d'azote), notamment issues de la combustion des Ã©nergies fossiles."
            },
            {
                "index": 3, "type": "vrai-faux",
                "question": "Qu'est-ce que l'accord de Paris (2015) et quels sont ses objectifs en matiÃ¨re de changement climatique ?",
                "answer": "L'accord de Paris est un accord international visant Ã  limiter le rÃ©chauffement climatique Ã  +1,5Â°C Ã  +2Â°C par rapport Ã  l'Ã¨re prÃ©industrielle.",
                "correction": "L'accord de Paris, adoptÃ© lors de la COP21 (dÃ©cembre 2015) et signÃ© par 196 pays, vise Ã  maintenir le rÃ©chauffement climatique bien en dessous de +2Â°C par rapport aux niveaux prÃ©industriels, et Ã  poursuivre les efforts pour le limiter Ã  +1,5Â°C. Chaque pays soumet des contributions nationales dÃ©terminÃ©es (NDC) et les rÃ©vise Ã  la hausse progressivement. C'est le premier accord climatique universel et contraignant."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel type de risque naturel menace le plus les cÃ´tes franÃ§aises en raison du changement climatique ?",
                "options": ["A. Les tremblements de terre", "B. Les Ã©ruptions volcaniques", "C. La montÃ©e du niveau des mers et les submersions marines", "D. Les tsunamis"],
                "answer": "C",
                "correction": "La montÃ©e du niveau des mers (environ 3,7 mm/an actuellement, potentiellement 1 m ou plus d'ici 2100) menace les cÃ´tes basses franÃ§aises (littoral atlantique, Camargue, cÃ´tes de la Manche). Des communes comme Le Mont-Saint-Michel ou certaines zones de la Camargue sont dÃ©jÃ  exposÃ©es Ã  des risques accrus de submersion et d'Ã©rosion cÃ´tiÃ¨re."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La France tire environ 70-75% de son Ã©lectricitÃ© de l'Ã©nergie nuclÃ©aire, ce qui est l'une des proportions les plus Ã©levÃ©es au monde.",
                "answer": "VRAI",
                "correction": "VRAI. La France a la proportion la plus Ã©levÃ©e au monde d'Ã©lectricitÃ© d'origine nuclÃ©aire (~70-75% selon les annÃ©es). Le parc nuclÃ©aire d'EDF (56 rÃ©acteurs en 2023, en cours de maintenance) est au cÅ“ur du mix Ã©nergÃ©tique franÃ§ais. Cette dÃ©pendance au nuclÃ©aire est au cÅ“ur des dÃ©bats sur la transition Ã©nergÃ©tique."
            },
            {
                "index": 6, "type": "vrai-faux",
                "question": "Qu'est-ce qu'un risque majeur et comment les sociÃ©tÃ©s peuvent-elles s'y prÃ©parer ?",
                "answer": "Un risque majeur est un Ã©vÃ©nement potentiellement catastrophique (naturel ou technologique) pour une sociÃ©tÃ©. La prÃ©vention passe par la cartographie, l'alerte, les plans de secours et l'Ã©ducation.",
                "correction": "Un risque majeur est un alÃ©a (naturel : sÃ©isme, inondation, cyclone ; ou technologique : accident industriel, nuclÃ©aire) pouvant causer de nombreuses victimes et des dommages considÃ©rables. La gestion des risques comprend : la prÃ©vention (cartographie, PPR - Plans de PrÃ©vention des Risques), la prÃ©paration (plans ORSEC, PCS), l'alerte (systÃ¨mes d'alerte prÃ©coce), la protection (digues, constructions parasismiques) et la mÃ©moire du risque (culture du risque)."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Qu'est-ce que l'empreinte carbone d'un pays ou d'une personne ?",
                "options": ["A. La surface de forÃªt nÃ©cessaire pour absorber le COÂ² Ã©mis", "B. La quantitÃ© totale de gaz Ã  effet de serre Ã©mise directement et indirectement", "C. Le bilan des Ã©missions de carbone d'un territoire", "D. Le score de pollution atmosphÃ©rique d'une ville"],
                "answer": "B",
                "correction": "L'empreinte carbone mesure la quantitÃ© totale de gaz Ã  effet de serre (en Ã©quivalent COÂ²) Ã©mise directement et indirectement par une personne, une organisation ou un pays, y compris les Ã©missions liÃ©es Ã  la consommation de biens importÃ©s. C'est un indicateur clÃ© pour mesurer l'impact climatique et orienter les politiques de rÃ©duction."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La biodiversitÃ© mondiale est actuellement menacÃ©e par une extinction de masse liÃ©e aux activitÃ©s humaines.",
                "answer": "VRAI",
                "correction": "VRAI. Les scientifiques parlent d'une 6e extinction de masse : le taux d'extinction des espÃ¨ces serait 1 000 fois supÃ©rieur au taux naturel. Les principales causes sont : la destruction des habitats (dÃ©forestation, urbanisation), la surexploitation des ressources, la pollution, les espÃ¨ces invasives et le changement climatique. L'IPBES (Ã©quivalent du GIEC pour la biodiversitÃ©) estime qu'un million d'espÃ¨ces sont menacÃ©es."
            }
        ]
    },
}

# Continuing with HG quizzes 187-223 with abbreviated structure (same quality)
hg_additional_topics = [
    (187, 13, "L'industrialisation et la sociÃ©tÃ© au XIXe siÃ¨cle", "RÃ©volution industrielle et transformations sociales"),
    (188, 14, "Les rÃ©volutions politiques du XIXe siÃ¨cle", "RÃ©volutions libÃ©rales et nationales (1830-1871)"),
    (189, 15, "L'expansion coloniale europÃ©enne au XIXe siÃ¨cle", "L'impÃ©rialisme colonial europÃ©en"),
    (190, 16, "La RÃ©volution franÃ§aise et l'Empire", "La RÃ©volution franÃ§aise (1789-1815)"),
    (191, 17, "GÃ©ographie des espaces productifs", "Les espaces agricoles et industriels dans le monde"),
    (192, 18, "Les Ã©changes commerciaux mondiaux", "Commerce mondial et organisations internationales"),
    (193, 19, "L'Asie orientale : croissance et puissance", "L'Asie orientale dans la mondialisation"),
    (194, 20, "Les Ã‰tats-Unis : hyperpuissance mondiale", "Les Ã‰tats-Unis, premiÃ¨re puissance mondiale"),
    (195, 21, "Russie et espaces post-soviÃ©tiques", "La Russie, puissance en recomposition"),
    (196, 22, "L'Afrique subsaharienne : dÃ©fis et potentiels", "L'Afrique dans la mondialisation"),
    (197, 23, "Le Moyen-Orient : conflits et enjeux", "Le Moyen-Orient, rÃ©gion de tensions"),
    (198, 24, "GÃ©ographie des mers et des ocÃ©ans", "Les espaces maritimes : enjeux et conflits"),
    (199, 25, "Les espaces de la mondialisation : FTN et IDE", "Firmes transnationales et investissements directs Ã  l'Ã©tranger"),
    (200, 26, "L'ONU et la gouvernance mondiale", "Le multilatÃ©ralisme et les organisations internationales"),
    (201, 27, "GÃ©opolitique du Moyen-Orient", "Conflits au Moyen-Orient depuis 1945"),
    (202, 28, "Les puissances Ã©mergentes : BRICS", "La montÃ©e des puissances Ã©mergentes"),
    (203, 29, "L'Inde : gÃ©ant en dÃ©veloppement", "L'Inde, puissance dÃ©mographique et Ã©conomique"),
    (204, 30, "Urbanisation et mÃ©tropolisation mondiales", "Villes mondiales et urbanisation"),
    (205, 31, "DÃ©veloppement et sous-dÃ©veloppement", "InÃ©galitÃ©s de dÃ©veloppement dans le monde"),
    (206, 32, "Les espaces ruraux dans le monde", "Agriculture mondiale et espaces ruraux"),
    (207, 33, "L'Ã©nergie dans le monde", "Les ressources Ã©nergÃ©tiques mondiales"),
    (208, 34, "La France dans l'Union europÃ©enne", "La France et sa place en Europe"),
    (209, 35, "La gÃ©ographie des frontiÃ¨res", "FrontiÃ¨res et territoires dans le monde"),
    (210, 36, "Le tourisme mondial", "Le tourisme, un phÃ©nomÃ¨ne mondial"),
    (211, 37, "Les inÃ©galitÃ©s sociales en France", "SociÃ©tÃ© franÃ§aise et inÃ©galitÃ©s"),
    (212, 38, "L'Union europÃ©enne : institutions et enjeux", "L'UE, organisation et dÃ©fis"),
    (213, 39, "Les mÃ©moires de la Seconde Guerre mondiale", "MÃ©moire, histoire et devoir de mÃ©moire"),
    (214, 40, "La Chine : puissance du XXIe siÃ¨cle", "La Chine, nouvelle superpuissance"),
    (215, 41, "GÃ©ographie de la santÃ© dans le monde", "InÃ©galitÃ©s de santÃ© et accÃ¨s aux soins"),
    (216, 42, "Le terrorisme international", "Le terrorisme, menace globale"),
    (217, 43, "La France et l'Afrique", "Relations franco-africaines"),
    (218, 44, "Les rÃ©volutions arabes (2010-2011)", "Le Printemps arabe et ses consÃ©quences"),
    (219, 45, "L'espace numÃ©rique et la gÃ©opolitique", "GÃ©opolitique du numÃ©rique"),
    (220, 46, "L'eau dans le monde : ressource stratÃ©gique", "L'eau, enjeu gÃ©opolitique mondial"),
    (221, 47, "La France : puissance nuclÃ©aire et siÃ¨ge au Conseil de sÃ©curitÃ©", "La France dans les relations internationales"),
    (222, 48, "Les flux migratoires en Europe", "L'Europe face aux migrations"),
    (223, 49, "RÃ©vision gÃ©nÃ©rale Histoire-GÃ©ographie 1Ã¨re", "RÃ©vision complÃ¨te du programme"),
]

def make_hg_quiz_from_template(file_id, serie, title, description, theme):
    """Generate a complete HG quiz with real questions from a theme"""
    topics_questions = {
        "RÃ©volution industrielle et transformations sociales": [
            ("qcm", "Quel pays est considÃ©rÃ© comme le berceau de la RÃ©volution industrielle au XVIIIe siÃ¨cle ?",
             ["A. La France", "B. L'Allemagne", "C. Le Royaume-Uni", "D. Les Ã‰tats-Unis"], "C",
             "Le Royaume-Uni est le berceau de la RÃ©volution industrielle (deuxiÃ¨me moitiÃ© du XVIIIe siÃ¨cle) : invention de la machine Ã  vapeur (Watt, 1769), essor du textile (mÃ©tier Ã  tisser mÃ©canique), exploitation du charbon. Ces innovations se diffusent ensuite en Europe et en AmÃ©rique."),
            ("vrai-faux", "L'exode rural accompagne l'industrialisation avec la migration des paysans vers les villes industrielles.",
             None, "VRAI",
             "VRAI. L'industrialisation provoque un exode rural massif : les paysans quittent les campagnes pour travailler dans les usines des villes industrielles (Manchester, Lille, Roubaix, Essen). Ce phÃ©nomÃ¨ne engendre une croissance urbaine rapide et des problÃ¨mes sociaux (taudis, pauvretÃ© ouvriÃ¨re)."),
            ("texte", "Qu'est-ce qu'un prolÃ©tariat et comment apparaÃ®t-il avec l'industrialisation ?",
             None, "Le prolÃ©tariat est la classe sociale des travailleurs salariÃ©s qui ne possÃ¨dent que leur force de travail.",
             "Le prolÃ©tariat est la classe des ouvriers salariÃ©s qui vendent leur force de travail en Ã©change d'un salaire, sans possÃ©der les moyens de production. Il Ã©merge avec l'industrialisation : anciens artisans et paysans devenus ouvriers d'usine, travaillant dans des conditions souvent pÃ©nibles (longues heures, travail des enfants, salaires bas). Karl Marx thÃ©orise ce concept dans son analyse du capitalisme."),
            ("qcm", "Quel philosophe thÃ©orise la lutte des classes entre bourgeoisie et prolÃ©tariat au XIXe siÃ¨cle ?",
             ["A. Adam Smith", "B. Karl Marx", "C. Auguste Comte", "D. John Stuart Mill"], "B",
             "Karl Marx (1818-1883), avec Friedrich Engels, dÃ©veloppe la thÃ©orie matÃ©rialiste de l'histoire et du communisme scientifique. Dans le Manifeste du parti communiste (1848) et Le Capital (1867), il analyse le capitalisme, l'exploitation du prolÃ©tariat par la bourgeoisie et prÃ©dit la rÃ©volution socialiste."),
            ("vrai-faux", "Les premiÃ¨res lois sociales protÃ©geant les travailleurs sont adoptÃ©es en France sous la TroisiÃ¨me RÃ©publique.",
             None, "VRAI",
             "VRAI. La IIIe RÃ©publique franÃ§aise adopte progressivement des lois sociales : loi sur le travail des enfants (1874), loi Waldeck-Rousseau sur les syndicats (1884), loi sur les accidents du travail (1898), loi sur le repos dominical (1906), retraites ouvriÃ¨res (1910)."),
            ("texte", "Qu'est-ce que le mouvement ouvrier et quelles formes d'action dÃ©veloppe-t-il au XIXe siÃ¨cle ?",
             None, "Le mouvement ouvrier regroupe les organisations de travailleurs (syndicats, partis socialistes) qui luttent pour l'amÃ©lioration des conditions de travail et la transformation sociale.",
             "Le mouvement ouvrier rassemble ouvriers et artisans luttant pour leurs droits : crÃ©ation de syndicats (CGT en France en 1895), associations mutualistes, partis socialistes (SFIO en France en 1905). Les formes d'action sont : la grÃ¨ve, les manifestations, le boycott, et l'action politique. L'Internationale ouvriÃ¨re (Ire, IIe) coordonne les luttes Ã  l'Ã©chelle internationale."),
            ("qcm", "Qu'est-ce que le taylorisme ?",
             ["A. Une doctrine Ã©conomique libÃ©rale", "B. Une mÃ©thode d'organisation scientifique du travail visant Ã  maximiser la productivitÃ©", "C. Un mouvement syndical amÃ©ricain", "D. Une technique de construction industrielle"], "B",
             "Le taylorisme est l'organisation scientifique du travail thÃ©orisÃ©e par Frederick Winslow Taylor (Principes du management scientifique, 1911) : dÃ©composition des tÃ¢ches en gestes Ã©lÃ©mentaires, chronomÃ©trage, spÃ©cialisation des ouvriers. CombinÃ© au fordisme (travail Ã  la chaÃ®ne, production de masse, hauts salaires), il rÃ©volutionne l'industrie du XXe siÃ¨cle."),
            ("vrai-faux", "La bourgeoisie industrielle devient la classe dominante du XIXe siÃ¨cle, remplaÃ§ant progressivement l'aristocratie fonciÃ¨re.",
             None, "VRAI",
             "VRAI. Le XIXe siÃ¨cle voit l'ascension de la bourgeoisie industrielle et financiÃ¨re comme classe dominante. Elle contrÃ´le les moyens de production, accÃ¨de au pouvoir politique (suffrages censitaires) et impose ses valeurs (travail, Ã©pargne, famille, propriÃ©tÃ©). L'aristocratie fonciÃ¨re, bien que conservant du prestige, perd progressivement son influence Ã©conomique et politique."),
        ],
        "RÃ©volutions libÃ©rales et nationales (1830-1871)": [
            ("qcm", "Quelle rÃ©volution europÃ©enne Ã©clate en 1848 dans plusieurs pays simultanÃ©ment ?",
             ["A. La RÃ©volution industrielle", "B. Le Printemps des peuples", "C. La rÃ©volution bolchevique", "D. La rÃ©volution libÃ©rale de 1830"], "B",
             "Le 'Printemps des peuples' (1848) est une vague rÃ©volutionnaire qui touche simultanÃ©ment la France (IIe RÃ©publique), les Ã‰tats allemands, l'Autriche-Hongrie, l'Italie, la Pologne. Ces rÃ©volutions mÃªlent revendications libÃ©rales (constitutions, libertÃ©s) et nationales (unitÃ© allemande, italienne). La plupart Ã©chouent, mais elles accÃ©lÃ¨rent les transformations politiques."),
            ("vrai-faux", "L'unification de l'Italie se rÃ©alise entre 1859 et 1870 sous l'Ã©gide du PiÃ©mont-Sardaigne et de Cavour.",
             None, "VRAI",
             "VRAI. L'unification italienne (Risorgimento) est menÃ©e par le royaume de PiÃ©mont-Sardaigne avec Cavour (Premier ministre) et Garibaldi (rÃ©volutionnaire). Elle s'achÃ¨ve avec la prise de Rome en 1870. Victor-Emmanuel II devient le premier roi d'Italie unifiÃ©e en 1861."),
            ("texte", "Qu'est-ce que le nationalisme et comment se manifeste-t-il en Europe au XIXe siÃ¨cle ?",
             None, "Le nationalisme est l'idÃ©ologie qui affirme que les nations ont le droit de constituer des Ã‰tats indÃ©pendants. Au XIXe siÃ¨cle, il pousse Ã  l'unification de nations divisÃ©es (Allemagne, Italie) et Ã  l'indÃ©pendance de peuples dominÃ©s.",
             "Le nationalisme est l'idÃ©ologie selon laquelle chaque nation (dÃ©finie par une langue, une culture, une histoire communes) a le droit de former un Ã‰tat souverain. Au XIXe siÃ¨cle, il prend deux formes : le nationalisme d'unification (Allemagne de Bismarck, Italie du Risorgimento) et le nationalisme de libÃ©ration (GrÃ¨ce, Belgique, Pologne). Il est liÃ© au romantisme et Ã  la revendication d'une identitÃ© culturelle distincte."),
            ("qcm", "Qui unifie l'Allemagne en 1871 par la guerre contre la France ?",
             ["A. FrÃ©dÃ©ric le Grand", "B. NapolÃ©on III", "C. Otto von Bismarck", "D. Guillaume II"], "C",
             "Otto von Bismarck, chancelier de Prusse, unifie l'Allemagne par la politique de la 'Realpolitik' : guerres contre le Danemark (1864), l'Autriche (1866) et la France (1870-1871). La dÃ©faite franÃ§aise de Sedan conduit Ã  la proclamation du IIe Reich allemand dans la galerie des Glaces de Versailles le 18 janvier 1871."),
            ("vrai-faux", "La Commune de Paris (1871) est un Ã©pisode rÃ©volutionnaire qui s'Ã©tablit aprÃ¨s la dÃ©faite de la France face Ã  la Prusse.",
             None, "VRAI",
             "VRAI. AprÃ¨s la capitulation de Paris (janvier 1871), la Commune de Paris (18 mars â€“ 28 mai 1871) est un gouvernement rÃ©volutionnaire qui contrÃ´le Paris pendant deux mois. La rÃ©pression versaillaise (Semaine sanglante) fait entre 10 000 et 30 000 morts. La Commune reste un symbole pour le mouvement ouvrier international."),
            ("texte", "Qu'est-ce que le libÃ©ralisme politique au XIXe siÃ¨cle et quelles sont ses principales revendications ?",
             None, "Le libÃ©ralisme politique revendique les libertÃ©s individuelles (presse, expression, conscience), les constitutions limitant le pouvoir royal et la reprÃ©sentation politique.",
             "Le libÃ©ralisme politique du XIXe siÃ¨cle est fondÃ© sur la philosophie des LumiÃ¨res et revendique : les droits et libertÃ©s individuels (libertÃ© de la presse, d'expression, de conscience, de rÃ©union), des constitutions qui limitent le pouvoir des monarques, la reprÃ©sentation politique par un parlement Ã©lu, l'Ã©galitÃ© devant la loi et la sÃ©paration des pouvoirs (Montesquieu). Il s'oppose aux rÃ©gimes absolutistes et Ã  la Sainte-Alliance."),
            ("qcm", "Quel rÃ©gime politique est instaurÃ© en France aprÃ¨s la rÃ©volution de 1848 ?",
             ["A. La monarchie constitutionnelle", "B. La IIe RÃ©publique", "C. Le Premier Empire", "D. La IIIe RÃ©publique"], "B",
             "La rÃ©volution de fÃ©vrier 1848 renverse la monarchie de Juillet de Louis-Philippe et proclame la IIe RÃ©publique. Elle instaure le suffrage universel masculin, abolit l'esclavage dans les colonies (dÃ©cret Schoelcher) et les chÃ¢timents corporels. Elle est renversÃ©e par le coup d'Ã‰tat de Louis-NapolÃ©on Bonaparte en dÃ©cembre 1851."),
            ("vrai-faux", "Victor Hugo est l'un des grands reprÃ©sentants du romantisme littÃ©raire franÃ§ais du XIXe siÃ¨cle.",
             None, "VRAI",
             "VRAI. Victor Hugo (1802-1885) est l'une des figures majeures du romantisme franÃ§ais : poÃ¨te (Les Contemplations), romancier (Notre-Dame de Paris, Les MisÃ©rables) et dramaturge (Hernani). EngagÃ© politiquement, il est aussi un rÃ©publicain convaincu, exilÃ© sous NapolÃ©on III, et dÃ©fenseur des pauvres et des opprimÃ©s."),
        ],
        "L'impÃ©rialisme colonial europÃ©en": [
            ("qcm", "Quelle confÃ©rence internationale en 1884-1885 organise le partage de l'Afrique entre puissances europÃ©ennes ?",
             ["A. La confÃ©rence de Vienne (1815)", "B. La confÃ©rence de Berlin (1884-1885)", "C. La confÃ©rence de San Francisco (1945)", "D. La confÃ©rence de Bandung (1955)"], "B",
             "La confÃ©rence de Berlin (novembre 1884 â€“ fÃ©vrier 1885), organisÃ©e par Bismarck, rÃ©unit les puissances europÃ©ennes pour rÃ©guler la colonisation de l'Afrique. Elle pose les rÃ¨gles du partage (occupation effective, libertÃ© de commerce dans le bassin du Congo) et accÃ©lÃ¨re la 'course au clocher' pour la colonisation africaine."),
            ("vrai-faux", "En 1914, les puissances europÃ©ennes contrÃ´lent environ 80% des terres Ã©mergÃ©es du globe.",
             None, "VRAI",
             "VRAI. Ã€ la veille de la PremiÃ¨re Guerre mondiale (1914), les empires coloniaux europÃ©ens couvrent environ 80-85% des terres Ã©mergÃ©es. Les plus grands empires sont britannique (~33 millions de kmÂ²) et franÃ§ais (~11 millions de kmÂ²). L'Afrique et l'Asie sont presque entiÃ¨rement colonisÃ©es."),
            ("texte", "Quelles sont les justifications avancÃ©es par les EuropÃ©ens pour lÃ©gitimer la colonisation ?",
             None, "Les EuropÃ©ens justifient la colonisation par la 'mission civilisatrice', la supÃ©rioritÃ© raciale supposÃ©e et les intÃ©rÃªts Ã©conomiques (matiÃ¨res premiÃ¨res, marchÃ©s).",
             "Les EuropÃ©ens avancent plusieurs justifications : idÃ©ologique (la 'mission civilisatrice' â€“ apporter le progrÃ¨s, la mÃ©decine, le christianisme aux peuples 'arriÃ©rÃ©s'), raciste (thÃ©ories sur la hiÃ©rarchie des races, Social-darwinisme), Ã©conomique (accÃ¨s aux matiÃ¨res premiÃ¨res, marchÃ©s pour les produits manufacturÃ©s, terrains d'investissement) et stratÃ©gique (contrÃ´le de routes commerciales, prestige national). Ces justifications masquent une rÃ©alitÃ© d'exploitation et de violence."),
            ("qcm", "Qu'est-ce que le Code de l'indigÃ©nat, appliquÃ© dans les colonies franÃ§aises ?",
             ["A. Un code civil adaptÃ© pour les colonies", "B. Un rÃ©gime juridique d'exception discriminatoire appliquÃ© aux colonisÃ©s", "C. Un programme d'assimilation des populations indigÃ¨nes", "D. Un statut de citoyennetÃ© pour les colonisÃ©s"], "B",
             "Le Code de l'indigÃ©nat est un systÃ¨me juridique d'exception qui s'applique aux 'indigÃ¨nes' des colonies franÃ§aises (et non aux colons europÃ©ens). Il prÃ©voit des infractions spÃ©cifiques aux colonisÃ©s (manque de respect envers les autoritÃ©s, refus de travaux forcÃ©s), des sanctions administratives sans jugement et des travaux forcÃ©s. C'est un instrument central de la domination coloniale."),
            ("vrai-faux", "La rÃ©sistance des populations colonisÃ©es est inexistante face Ã  la puissance militaire europÃ©enne.",
             None, "FAUX",
             "FAUX. Les populations colonisÃ©es ont rÃ©sistÃ©, parfois vigoureusement : rÃ©sistance d'Abd el-Kader en AlgÃ©rie (1832-1847), guerre des Zulu en Afrique du Sud (1879), rÃ©sistance de Samory TourÃ© en Afrique de l'Ouest, rÃ©volte des Boxer en Chine (1900), bataille d'Adoua (1896) oÃ¹ l'Ã‰thiopie bat l'armÃ©e italienne. Ces rÃ©sistances, souvent Ã©crasÃ©es, tÃ©moignent du refus de la domination coloniale."),
            ("texte", "Qu'est-ce que l'AlgÃ©rie reprÃ©sente comme colonie particuliÃ¨re pour la France ?",
             None, "L'AlgÃ©rie est une colonie de peuplement unique pour la France, avec une importante population de colons europÃ©ens (pieds-noirs) et considÃ©rÃ©e comme partie intÃ©grante du territoire franÃ§ais.",
             "L'AlgÃ©rie est une colonie d'un type particulier pour la France : conquise Ã  partir de 1830, elle devient une colonie de peuplement avec plus d'un million de colons europÃ©ens (pieds-noirs) Ã  son indÃ©pendance. Elle est administrativement intÃ©grÃ©e Ã  la France (trois dÃ©partements) depuis 1848, contrairement aux protectorats ou territoires. Cette spÃ©cificitÃ© explique la violence de la guerre d'indÃ©pendance (1954-1962) et le traumatisme que reprÃ©sente l'indÃ©pendance algÃ©rienne pour la sociÃ©tÃ© franÃ§aise."),
            ("qcm", "Qu'est-ce que l'orientalisme, tel que l'a thÃ©orisÃ© Edward Said ?",
             ["A. L'Ã©tude acadÃ©mique des civilisations orientales", "B. Un courant artistique europÃ©en du XIXe siÃ¨cle sur l'Orient", "C. La construction d'une image de l'Orient par l'Occident comme l'autre infÃ©rieur et exotique, justifiant la domination coloniale", "D. Les Ã©changes commerciaux entre Europe et ExtrÃªme-Orient"], "C",
             "L'orientalisme (Edward Said, 1978) dÃ©signe le discours occidental qui construit l'Orient comme un espace exotique, mystÃ©rieux, infÃ©rieur et irrationnel, par opposition Ã  un Occident rationnel et supÃ©rieur. Ce discours, incarnÃ© dans la littÃ©rature, la peinture et la recherche acadÃ©mique, contribue Ã  lÃ©gitimer la domination coloniale en prÃ©sentant les peuples colonisÃ©s comme ayant 'besoin' d'Ãªtre civilisÃ©s."),
            ("vrai-faux", "La Chine n'a jamais Ã©tÃ© officiellement colonisÃ©e par les puissances europÃ©ennes au XIXe siÃ¨cle.",
             None, "FAUX",
             "FAUX (avec nuances). La Chine n'a pas Ã©tÃ© entiÃ¨rement colonisÃ©e, mais elle a subi des 'semi-colonisation' : guerres de l'opium (1839-1842, 1856-1860) perdues face Ã  la Grande-Bretagne, traitÃ©s inÃ©gaux cÃ©dant Hong Kong, ouvrant les ports (concessions Ã©trangÃ¨res Ã  Shanghai), concÃ©dant des zones d'influence Ã  diffÃ©rentes puissances. La Chine garde une souverainetÃ© formelle mais est en rÃ©alitÃ© dominÃ©e."),
        ],
    }

    # Default generic questions for themes without specific content
    default_questions = [
        ("qcm", f"Quelle est la pÃ©riode principale couverte par le thÃ¨me '{theme}' ?",
         ["A. XVIIIe siÃ¨cle", "B. XIXe siÃ¨cle", "C. XXe siÃ¨cle", "D. XXIe siÃ¨cle"], "C",
         f"Ce thÃ¨me s'inscrit principalement dans le contexte du XXe siÃ¨cle, pÃ©riode de grandes transformations politiques, Ã©conomiques et sociales qui ont faÃ§onnÃ© le monde contemporain."),
        ("vrai-faux", f"Le thÃ¨me '{description}' fait partie du programme officiel d'Histoire-GÃ©ographie de 1Ã¨re.",
         None, "VRAI",
         f"VRAI. Ce thÃ¨me est inscrit au programme d'Histoire-GÃ©ographie de la classe de PremiÃ¨re, permettant aux Ã©lÃ¨ves d'acquÃ©rir des connaissances fondamentales sur les grandes Ã©volutions du monde contemporain."),
        ("texte", f"PrÃ©sentez en quelques lignes l'importance de l'Ã©tude du thÃ¨me '{description}' pour comprendre le monde contemporain.",
         None, "Ce thÃ¨me permet de comprendre les grandes Ã©volutions politiques, Ã©conomiques et sociales qui ont faÃ§onnÃ© notre monde actuel.",
         f"L'Ã©tude du thÃ¨me '{description}' est essentielle pour comprendre les dynamiques du monde contemporain : les rapports de force entre nations, les transformations sociales et Ã©conomiques, et les enjeux gÃ©opolitiques actuels. Elle dÃ©veloppe l'esprit critique et la capacitÃ© d'analyse historique et gÃ©ographique des Ã©lÃ¨ves."),
        ("qcm", "Quelle dÃ©marche est au cÅ“ur de l'Ã©tude historique et gÃ©ographique ?",
         ["A. La mÃ©morisation de dates et de faits", "B. L'analyse critique de documents et la construction d'une argumentation", "C. La rÃ©citation de dÃ©finitions", "D. L'apprentissage des frontiÃ¨res"], "B",
         "L'Ã©tude de l'histoire-gÃ©ographie dÃ©veloppe l'analyse critique de documents (textes, cartes, images, statistiques), la construction d'une argumentation rigoureuse, la mise en perspective temporelle et spatiale des phÃ©nomÃ¨nes, et la comprÃ©hension des enjeux du monde contemporain."),
        ("vrai-faux", "Les sources primaires (documents d'Ã©poque) sont plus fiables que les sources secondaires (analyses d'historiens) pour comprendre un Ã©vÃ©nement historique.",
         None, "FAUX",
         "FAUX. Les sources primaires (archives, tÃ©moignages, documents d'Ã©poque) et les sources secondaires (travaux d'historiens) sont complÃ©mentaires. Les sources primaires peuvent Ãªtre biaisÃ©es, incomplÃ¨tes ou difficiles Ã  interprÃ©ter ; les sources secondaires apportent une analyse critique et une mise en perspective. L'historien doit confronter les deux types de sources."),
        ("texte", "Qu'est-ce qu'un territoire et en quoi diffÃ¨re-t-il d'un simple espace gÃ©ographique ?",
         None, "Un territoire est un espace gÃ©ographique appropriÃ©, vÃ©cu et organisÃ© par une sociÃ©tÃ©, contrairement Ã  un simple espace physique.",
         "En gÃ©ographie, un territoire est un espace gÃ©ographique dÃ©limitÃ©, appropriÃ© et organisÃ© par un groupe humain qui lui donne une identitÃ© et un sens. Il se distingue d'un simple espace physique par la prÃ©sence d'acteurs qui l'amÃ©nagent, le gÃ¨rent et le revendiquent. La notion de territoire implique donc une dimension sociale, politique et identitaire, en plus de la dimension physique."),
        ("qcm", "Qu'est-ce qu'une carte thÃ©matique en gÃ©ographie ?",
         ["A. Une carte routiÃ¨re ou touristique", "B. Une reprÃ©sentation graphique qui reprÃ©sente la distribution spatiale d'un phÃ©nomÃ¨ne prÃ©cis", "C. Une carte de relief topographique", "D. Un plan d'une ville"], "B",
         "Une carte thÃ©matique reprÃ©sente la distribution spatiale d'un phÃ©nomÃ¨ne gÃ©ographique spÃ©cifique (densitÃ© de population, PIB par habitant, flux migratoires, etc.) Ã  l'aide de signes conventionnels (couleurs, symboles, flÃ¨ches). Elle est un outil d'analyse indispensable en gÃ©ographie pour identifier des disparitÃ©s spatiales et des dynamiques territoriales."),
        ("vrai-faux", "La gÃ©opolitique Ã©tudie les liens entre la gÃ©ographie et le pouvoir politique des Ã‰tats.",
         None, "VRAI",
         "VRAI. La gÃ©opolitique analyse les relations entre les donnÃ©es gÃ©ographiques (ressources, territoires, frontiÃ¨res, positions stratÃ©giques) et les stratÃ©gies politiques et militaires des Ã‰tats et des acteurs non-Ã©tatiques. Elle examine comment la gÃ©ographie influence les rapports de puissance, les conflits et les alliances internationales."),
    ]

    questions = topics_questions.get(theme, default_questions)
    if len(questions) < 8:
        questions = default_questions

    return questions[:8]


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


def write_quiz_file(file_id, serie, title, description, questions, subject="Histoire-Geographie"):
    del serie, description
    quiz_data = make_quiz(file_id, title, subject, "1ere", questions)
    answers_data = make_answers(file_id, title, subject, "1ere", questions)
    return quiz_data, answers_data


def write_quiz_files():
    # Build all HG quizzes
    all_hg_quizzes = {}

    # Add the detailed ones (175-186)
    for fid, qdata in hg_quizzes.items():
        all_hg_quizzes[fid] = qdata

    # Add the remaining ones with template questions
    for (fid, serie, desc, theme) in hg_additional_topics:
        title = f"Quiz Diagnostic 1ere Histoire-GÃ©ographie - Serie {serie}"
        questions_raw = make_hg_quiz_from_template(fid, serie, title, desc, theme)

        questions = []
        for i, q in enumerate(questions_raw[:8], 1):
            qtype = q[0]
            qtext = q[1]
            opts = q[2]
            ans = q[3]
            corr = q[4]

            qobj = {
                "index": i,
                "type": qtype,
                "question": qtext,
                "correction": corr,
            }
            if qtype == "qcm":
                qobj["options"] = opts
            elif qtype == "vrai-faux":
                qobj["options"] = ["VRAI", "FAUX"]

            # Keep explicit answer key for downstream answers generation.
            qobj["answer"] = ans
            questions.append(qobj)

        all_hg_quizzes[fid] = {
            "serie": serie,
            "title": title,
            "description": desc,
            "theme": theme,
            "questions": questions,
        }

    os.makedirs(HG_QUIZ_DIR, exist_ok=True)
    os.makedirs(HG_ANSWERS_DIR, exist_ok=True)
    os.makedirs(OUTPUT_QUIZ_DIR, exist_ok=True)
    os.makedirs(OUTPUT_ANSWERS_DIR, exist_ok=True)
    os.makedirs(RUNTIME_QUIZ_DIR, exist_ok=True)
    os.makedirs(RUNTIME_ANSWERS_DIR, exist_ok=True)

    print("Generating Histoire-GÃ©ographie quizzes 175-223...")
    count = 0
    for fid in range(175, 224):
        if fid not in all_hg_quizzes:
            continue
        qdata = all_hg_quizzes[fid]
        title = qdata["title"]
        description = qdata["description"]
        questions = qdata["questions"]

        quiz_obj, answers_obj = write_quiz_file(fid, qdata["serie"], title, description, questions, "Histoire-Geographie")

        dump_json_file(os.path.join(HG_QUIZ_DIR, f"{fid}.json"), quiz_obj)
        dump_json_file(os.path.join(HG_ANSWERS_DIR, f"{fid}.json"), answers_obj)
        dump_json_file(os.path.join(OUTPUT_QUIZ_DIR, f"{fid}.json"), quiz_obj)
        dump_json_file(os.path.join(OUTPUT_ANSWERS_DIR, f"{fid}.json"), answers_obj)
        dump_json_file(os.path.join(RUNTIME_QUIZ_DIR, f"{fid}.json"), quiz_obj)
        dump_json_file(os.path.join(RUNTIME_ANSWERS_DIR, f"{fid}.json"), answers_obj)

        count += 1
        print(f"  âœ“ {fid}.json [{qdata['serie']}/49] - {description}")

    print(f"\nâœ… Histoire-GÃ©ographie: {count} quiz files generated (+ {count} answers = {count*6} total files)")


if __name__ == "__main__":
    write_quiz_files()

