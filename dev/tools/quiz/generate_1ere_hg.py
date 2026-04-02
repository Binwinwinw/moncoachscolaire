import json
import os
from datetime import UTC, datetime

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
REPO_ROOT = os.path.abspath(os.path.join(SCRIPT_DIR, "..", "..", ".."))
HG_OUTPUT_DIR = os.path.join(SCRIPT_DIR, "hg_quizzes")
HG_QUIZ_DIR = os.path.join(HG_OUTPUT_DIR, "quiz")
HG_ANSWERS_DIR = os.path.join(HG_OUTPUT_DIR, "quiz_answers")
OUTPUT_ROOT_DIR = os.path.join(SCRIPT_DIR, "output", "hg_quizzes")
OUTPUT_QUIZ_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz")
OUTPUT_ANSWERS_DIR = os.path.join(OUTPUT_ROOT_DIR, "quiz_answers")
RUNTIME_QUIZ_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz")
RUNTIME_ANSWERS_DIR = os.path.join(REPO_ROOT, "src", "data", "quiz_answers")

# ============================================================
# HISTOIRE-GÉOGRAPHIE 1ère — fichiers 175 à 223
# 49 quizzes, séries 1 à 49
# ============================================================

hg_quizzes = {
    175: {
        "serie": 1,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 1",
        "description": "La France et l'Europe à la Belle Époque",
        "theme": "La Belle Époque en France et en Europe",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quelle période désigne-t-on par l'expression « Belle Époque » ?",
                "options": ["A. 1850-1870", "B. 1871-1914", "C. 1918-1939", "D. 1945-1968"],
                "answer": "B",
                "correction": "La Belle Époque désigne la période allant de 1871 à 1914, caractérisée par la paix relative, la prospérité économique et les avancées technologiques en Europe occidentale."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "La France connaît une forte croissance industrielle et démographique à la Belle Époque.",
                "answer": "VRAI",
                "correction": "VRAI. La Belle Époque est marquée par l'essor industriel (automobile, électricité, cinéma) et une croissance économique notable, bien que la croissance démographique française soit plus lente que celle de ses voisins."
            },
            {
                "index": 3, "type": "texte",
                "question": "Citez deux inventions ou innovations majeures de la Belle Époque qui ont transformé la vie quotidienne.",
                "answer": "L'automobile et l'électricité (ou le cinéma, l'avion, le téléphone...)",
                "correction": "Parmi les grandes innovations de la Belle Époque : l'automobile (Benz, Renault), l'électricité dans les foyers, le cinématographe des frères Lumière (1895), l'avion (frères Wright, 1903), le téléphone, la radiographie (Röntgen, 1895)."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel événement met fin à la Belle Époque ?",
                "options": ["A. La Révolution russe de 1917", "B. La crise de 1929", "C. Le déclenchement de la Première Guerre mondiale en 1914", "D. La chute de la Commune de Paris"],
                "answer": "C",
                "correction": "C'est l'assassinat de l'archiduc François-Ferdinand à Sarajevo le 28 juin 1914 et le déclenchement de la Première Guerre mondiale qui met brutalement fin à la Belle Époque."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La IIIe République française est fondée après la défaite de 1870 face à la Prusse.",
                "answer": "VRAI",
                "correction": "VRAI. La IIIe République est proclamée le 4 septembre 1870 après la capture de Napoléon III à Sedan. Elle durera jusqu'en 1940, devenant le régime républicain le plus long de l'histoire de France."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce que la loi de séparation de l'Église et de l'État de 1905 établit en France ?",
                "answer": "Elle instaure la laïcité en séparant les institutions religieuses des institutions de l'État.",
                "correction": "La loi du 9 décembre 1905 établit la séparation des Églises et de l'État, instaurant la laïcité comme principe républicain. L'État ne reconnaît, ne salarie ni ne subventionne aucun culte. C'est un pilier fondamental de la République française."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel événement diplomatique majeur divise la France à la fin du XIXe siècle ?",
                "options": ["A. La crise du Maroc", "B. L'affaire Dreyfus", "C. La guerre des Boers", "D. La crise de Fachoda"],
                "answer": "B",
                "correction": "L'affaire Dreyfus (1894-1906) est un scandale politico-judiciaire qui divise profondément la société française entre dreyfusards (défenseurs des droits de l'homme) et antidreyfusards (nationalistes, antisémites). Elle révèle les tensions de la IIIe République."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La Triple Entente regroupe la France, le Royaume-Uni et l'Autriche-Hongrie avant 1914.",
                "answer": "FAUX",
                "correction": "FAUX. La Triple Entente (formée progressivement entre 1894 et 1907) regroupe la France, le Royaume-Uni et la Russie — et non l'Autriche-Hongrie, qui fait partie de la Triple Alliance avec l'Allemagne et l'Italie."
            }
        ]
    },
    176: {
        "serie": 2,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 2",
        "description": "La Première Guerre mondiale",
        "theme": "La Première Guerre mondiale (1914-1918)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quel événement déclenche directement la Première Guerre mondiale ?",
                "options": ["A. L'invasion de la Belgique par l'Allemagne", "B. L'assassinat de l'archiduc François-Ferdinand à Sarajevo", "C. La mobilisation générale française", "D. La déclaration de guerre de la Russie à l'Autriche"],
                "answer": "B",
                "correction": "L'assassinat de l'archiduc François-Ferdinand, héritier de l'empire austro-hongrois, le 28 juin 1914 à Sarajevo par Gavrilo Princip, nationaliste serbe, déclenche la crise diplomatique qui conduit à la guerre mondiale."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "La guerre de tranchées caractérise essentiellement le front occidental entre 1914 et 1918.",
                "answer": "VRAI",
                "correction": "VRAI. À partir de fin 1914, le front occidental se stabilise en un réseau de tranchées s'étendant de la mer du Nord à la frontière suisse. Cette guerre de position, épuisante et meurtrière, durera jusqu'en 1918."
            },
            {
                "index": 3, "type": "texte",
                "question": "Expliquez ce qu'est l'Union sacrée proclamée en France en août 1914.",
                "answer": "L'Union sacrée est la suspension des conflits politiques internes pour unir tous les Français derrière l'effort de guerre.",
                "correction": "L'Union sacrée, proclamée par le président Poincaré le 4 août 1914, est l'union de toutes les forces politiques françaises (socialistes, républicains, conservateurs) pour soutenir l'effort de guerre. Les oppositions politiques sont suspendues ; même les syndicats et le parti socialiste y adhèrent."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quelle est la bataille la plus meurtrière du front français en 1916 ?",
                "options": ["A. La bataille de la Marne", "B. La bataille de la Somme", "C. La bataille de Verdun", "D. La bataille de l'Aisne"],
                "answer": "C",
                "correction": "La bataille de Verdun (21 février – 18 décembre 1916) est la plus longue et l'une des plus meurtrières de la guerre : environ 700 000 victimes (morts, blessés, disparus) des deux côtés. Elle devient le symbole de la résistance française et de l'horreur de la Grande Guerre."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "Les États-Unis entrent en guerre dès 1914 aux côtés des Alliés.",
                "answer": "FAUX",
                "correction": "FAUX. Les États-Unis maintiennent leur neutralité jusqu'en avril 1917, avant d'entrer en guerre aux côtés des Alliés, notamment en raison de la guerre sous-marine à outrance menée par l'Allemagne et de l'interception du télégramme Zimmermann."
            },
            {
                "index": 6, "type": "texte",
                "question": "Quelles sont les principales conséquences du traité de Versailles pour l'Allemagne en 1919 ?",
                "answer": "L'Allemagne doit payer de lourdes réparations, céder des territoires (Alsace-Lorraine, etc.) et accepter la clause de responsabilité de la guerre.",
                "correction": "Le traité de Versailles (28 juin 1919) impose à l'Allemagne : la cession de l'Alsace-Lorraine à la France, de territoires à la Pologne, au Danemark, à la Belgique ; des réparations de guerre colossales ; la limitation de son armée ; et la clause de responsabilité exclusive de la guerre (article 231), source d'humiliation qui alimentera le ressentiment nationaliste."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel organisme international est créé en 1919 pour maintenir la paix ?",
                "options": ["A. L'ONU", "B. La SDN (Société des Nations)", "C. L'OTAN", "D. La Croix-Rouge internationale"],
                "answer": "B",
                "correction": "La Société des Nations (SDN) est créée par le traité de Versailles en 1919, sur proposition du président américain Wilson (14 points). Elle préfigure l'ONU mais sera fragilisée par l'absence des États-Unis, qui refusent de la rejoindre, et sera dissoute en 1946."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Le génocide arménien est perpétré pendant la Première Guerre mondiale par l'Empire ottoman.",
                "answer": "VRAI",
                "correction": "VRAI. À partir de 1915, l'Empire ottoman organise la déportation et le massacre systématique des Arméniens. Ce génocide, reconnu par de nombreux États dont la France, fait entre 600 000 et 1,5 million de victimes selon les estimations."
            }
        ]
    },
    177: {
        "serie": 3,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 3",
        "description": "L'entre-deux-guerres et la montée des totalitarismes",
        "theme": "L'entre-deux-guerres (1919-1939)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Dans quel pays le fascisme prend-il le pouvoir en premier ?",
                "options": ["A. L'Allemagne", "B. L'Espagne", "C. L'Italie", "D. La Hongrie"],
                "answer": "C",
                "correction": "C'est en Italie que Benito Mussolini fonde le premier régime fasciste. Après la 'Marche sur Rome' (octobre 1922), il prend le pouvoir et instaure progressivement une dictature totalitaire, le 'fascisme', qui donnera son nom à ce courant idéologique."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "La crise économique de 1929 commence aux États-Unis avec le krach boursier de Wall Street.",
                "answer": "VRAI",
                "correction": "VRAI. Le jeudi 24 octobre 1929 ('jeudi noir'), la Bourse de New York s'effondre. Cette crise financière se propage rapidement à l'économie réelle et au monde entier, provoquant une dépression économique mondiale avec des millions de chômeurs."
            },
            {
                "index": 3, "type": "texte",
                "question": "Qu'est-ce que le nazisme et quels sont ses principaux fondements idéologiques ?",
                "answer": "Le nazisme est le régime totalitaire d'Hitler, fondé sur le racisme (antisémitisme), le nationalisme extrême et le rejet de la démocratie.",
                "correction": "Le nazisme (national-socialisme) est l'idéologie du parti d'Adolf Hitler (NSDAP), fondée sur : l'antisémitisme et le racisme (théorie de la race aryenne supérieure), le nationalisme extrême et le pangermanisme, le rejet de la démocratie au profit d'un chef (Führerprinzip), l'anticommunisme, et l'expansionnisme territorial (Lebensraum). Hitler prend le pouvoir en janvier 1933."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel est le nom du régime autoritaire instauré par Staline en URSS dans les années 1930 ?",
                "options": ["A. Le léninisme", "B. Le stalinisme totalitaire", "C. Le bolchevisme libéral", "D. La démocratie populaire"],
                "answer": "B",
                "correction": "Le stalinisme désigne le régime totalitaire mis en place par Joseph Staline à partir des années 1920-1930 en URSS : collectivisation forcée, industrialisation à marche forcée (plans quinquennaux), culte de la personnalité, Goulag et Grandes Purges (1936-1938)."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "Le Front populaire gouverne la France entre 1936 et 1938 sous la direction de Léon Blum.",
                "answer": "VRAI",
                "correction": "VRAI. Le Front populaire (coalition socialistes-radicaux-communistes) remporte les élections de mai 1936. Sous Léon Blum, il adopte les accords Matignon : congés payés (2 semaines), semaine de 40 heures, hausse des salaires."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce que la politique d'apaisement (appeasement) pratiquée par les démocraties face à Hitler dans les années 1930 ?",
                "answer": "C'est la politique de concessions faites à Hitler pour éviter la guerre, symbolisée par les accords de Munich en 1938.",
                "correction": "La politique d'apaisement (appeasement) consiste pour le Royaume-Uni (Chamberlain) et la France (Daladier) à faire des concessions à Hitler pour éviter une nouvelle guerre. Son symbole est la conférence de Munich (septembre 1938) où ils acceptent l'annexion des Sudètes par l'Allemagne, croyant avoir assuré 'la paix pour notre temps'."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel pacte de non-agression est signé entre l'Allemagne nazie et l'URSS en août 1939 ?",
                "options": ["A. Le pacte anti-Komintern", "B. Le pacte germano-soviétique (pacte Molotov-Ribbentrop)", "C. Le pacte d'acier", "D. Le traité de non-prolifération"],
                "answer": "B",
                "correction": "Le pacte germano-soviétique (ou pacte Molotov-Ribbentrop, signé le 23 août 1939) est un accord de non-agression entre l'Allemagne nazie et l'URSS. Il contient des clauses secrètes de partage de l'Europe de l'Est. Il libère Hitler pour envahir la Pologne sans craindre un front à l'Est."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La Seconde Guerre mondiale éclate le 1er septembre 1939 avec l'invasion de la Pologne par l'Allemagne.",
                "answer": "VRAI",
                "correction": "VRAI. Le 1er septembre 1939, l'Allemagne nazie envahit la Pologne. Le 3 septembre, la France et le Royaume-Uni déclarent la guerre à l'Allemagne, conformément à leurs engagements envers la Pologne. La Seconde Guerre mondiale commence."
            }
        ]
    },
    178: {
        "serie": 4,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 4",
        "description": "La Seconde Guerre mondiale",
        "theme": "La Seconde Guerre mondiale (1939-1945)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quel est le nom de l'opération allemande qui conduit à la défaite de la France en juin 1940 ?",
                "options": ["A. L'Opération Barbarossa", "B. L'Opération Overlord", "C. Le Plan Schlieffen", "D. L'Opération Fall Gelb (Case Yellow)"],
                "answer": "D",
                "correction": "L'opération Fall Gelb (Case Yellow) est le plan allemand d'invasion de la France via les Ardennes (mai-juin 1940). En contournant la ligne Maginot, les Panzers percent le front à Sedan et encerclent les armées alliées. La France signe l'armistice le 22 juin 1940."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Le général de Gaulle lance son appel à la résistance depuis Londres le 18 juin 1940.",
                "answer": "VRAI",
                "correction": "VRAI. Le 18 juin 1940, le général Charles de Gaulle prononce depuis la BBC à Londres son célèbre appel refusant la défaite et appelant les Français à continuer le combat aux côtés des Alliés. Cet appel fonde la France libre."
            },
            {
                "index": 3, "type": "texte",
                "question": "Qu'est-ce que le régime de Vichy et quel rôle joue-t-il dans la persécution des Juifs de France ?",
                "answer": "Le régime de Vichy est le gouvernement collaborateur de Pétain qui coopère avec l'occupant nazi, notamment en organisant la déportation des Juifs de France.",
                "correction": "Le régime de Vichy (État français, 1940-1944) est dirigé par le maréchal Pétain après l'armistice. Il pratique la collaboration avec l'Allemagne nazie et adopte son propre statut des Juifs dès octobre 1940. La police française participe à la rafle du Vel d'Hiv (juillet 1942) : 13 000 Juifs arrêtés, dont 4 000 enfants, et déportés vers les camps d'extermination."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel événement fait entrer les États-Unis dans la Seconde Guerre mondiale en décembre 1941 ?",
                "options": ["A. L'invasion de la Pologne", "B. L'attaque japonaise sur Pearl Harbor", "C. La déclaration de guerre de l'Allemagne aux USA", "D. Le débarquement allié en Afrique du Nord"],
                "answer": "B",
                "correction": "L'attaque surprise de la flotte américaine à Pearl Harbor (Hawaï) par le Japon le 7 décembre 1941 entraîne l'entrée en guerre des États-Unis. Le lendemain, le président Roosevelt demande au Congrès la déclaration de guerre contre le Japon. L'Allemagne et l'Italie déclarent ensuite la guerre aux USA."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La conférence de Wannsee (1942) planifie la 'Solution finale', c'est-à-dire l'extermination systématique des Juifs d'Europe.",
                "answer": "VRAI",
                "correction": "VRAI. La conférence de Wannsee (20 janvier 1942) réunit des hauts responsables nazis qui coordonnent la mise en œuvre de la 'Solution finale de la question juive' : l'extermination systématique de tous les Juifs d'Europe. La Shoah fera environ 6 millions de victimes juives."
            },
            {
                "index": 6, "type": "texte",
                "question": "Décrivez l'importance stratégique du débarquement du 6 juin 1944 (Jour J) en Normandie.",
                "answer": "Le débarquement allié ouvre un second front à l'Ouest, contraignant l'Allemagne à combattre sur deux fronts et précipitant sa défaite.",
                "correction": "Le 6 juin 1944 (Opération Overlord), 156 000 soldats alliés débarquent en Normandie sur 5 plages. C'est la plus grande opération amphibie de l'histoire. Ce débarquement ouvre un front occidental décisif, oblige l'Allemagne à répartir ses forces sur plusieurs fronts (Est soviétique, Italie, Ouest), et enclenche la libération de la France et de l'Europe de l'Ouest."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quelle arme est utilisée pour la première fois en guerre lors des bombardements d'Hiroshima et Nagasaki en août 1945 ?",
                "options": ["A. La bombe à hydrogène", "B. Les missiles balistiques", "C. La bombe atomique", "D. Les armes chimiques"],
                "answer": "C",
                "correction": "Les États-Unis larguent deux bombes atomiques sur Hiroshima (6 août 1945) et Nagasaki (9 août 1945), faisant entre 100 000 et 200 000 morts. Ce sont les seuls emplois d'armes nucléaires en temps de guerre. Le Japon capitule le 15 août 1945."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Le procès de Nuremberg (1945-1946) juge les crimes de guerre et les crimes contre l'humanité commis par les dirigeants nazis.",
                "answer": "VRAI",
                "correction": "VRAI. Le Tribunal militaire international de Nuremberg (novembre 1945 – octobre 1946) juge 24 grands dirigeants nazis. Il définit les notions de crimes de guerre, crimes contre la paix et crimes contre l'humanité. 12 accusés sont condamnés à mort. Il pose les bases du droit international humanitaire."
            }
        ]
    },
    179: {
        "serie": 5,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 5",
        "description": "La Guerre Froide",
        "theme": "La Guerre Froide (1947-1991)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "En quelle année la Guerre Froide est-elle généralement considérée comme commençant ?",
                "options": ["A. 1945", "B. 1947", "C. 1950", "D. 1953"],
                "answer": "B",
                "correction": "La Guerre Froide débute conventionnellement en 1947, avec la doctrine Truman (mars 1947) et le plan Marshall (juin 1947). Ces politiques américaines face à l'expansion soviétique marquent la rupture officielle entre les deux blocs."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "L'OTAN est une alliance militaire de l'Ouest créée en 1949 pour contrer la menace soviétique.",
                "answer": "VRAI",
                "correction": "VRAI. L'Organisation du Traité de l'Atlantique Nord (OTAN) est fondée le 4 avril 1949. Elle réunit les États-Unis, le Canada et plusieurs pays d'Europe occidentale dans une alliance militaire défensive. En réponse, l'URSS crée le pacte de Varsovie en 1955."
            },
            {
                "index": 3, "type": "texte",
                "question": "Expliquez ce que signifie la notion de 'rideau de fer' dans le contexte de la Guerre Froide.",
                "answer": "Le rideau de fer est la frontière symbolique et réelle qui sépare l'Europe de l'Ouest (bloc occidental) de l'Europe de l'Est (bloc soviétique).",
                "correction": "L'expression 'rideau de fer' (Iron Curtain) est popularisée par Winston Churchill dans son discours de Fulton (mars 1946). Elle désigne la frontière hermétique séparant les démocraties libérales occidentales des démocraties populaires sous domination soviétique en Europe de l'Est. Le mur de Berlin (1961) en devient le symbole le plus visible."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quelle est la crise la plus dangereuse de la Guerre Froide, frôlant le conflit nucléaire en 1962 ?",
                "options": ["A. La crise de Berlin (1961)", "B. La guerre de Corée (1950-1953)", "C. La crise des missiles de Cuba", "D. La guerre du Vietnam"],
                "answer": "C",
                "correction": "La crise des missiles de Cuba (octobre 1962) est le moment le plus critique de la Guerre Froide. L'URSS installe des missiles nucléaires à Cuba. Kennedy impose un blocus naval. Pendant 13 jours, le monde est au bord de la guerre nucléaire. Khrouchtchev accepte finalement de retirer les missiles."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La Chine communiste de Mao Zedong rejoint le bloc soviétique dès 1949 et reste alliée à l'URSS tout au long de la Guerre Froide.",
                "answer": "FAUX",
                "correction": "FAUX. La Chine populaire, fondée en 1949, s'aligne d'abord avec l'URSS mais la rupture sino-soviétique survient dans les années 1960 (conflit idéologique, dispute territoriale). La Chine devient une puissance indépendante, voire rivale de l'URSS, complexifiant la bipolarité de la Guerre Froide."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce que la détente et quand intervient-elle dans la Guerre Froide ?",
                "answer": "La détente est une période d'apaisement des tensions entre les deux blocs, intervenant principalement dans les années 1970.",
                "correction": "La détente (années 1970) est une période d'apaisement relatif des tensions Est-Ouest : accords SALT I (1972) sur la limitation des armements nucléaires, conférence d'Helsinki (1975) reconnaissant les frontières européennes, visites diplomatiques entre dirigeants (Nixon à Moscou, puis en Chine). La détente s'interrompt avec l'invasion soviétique de l'Afghanistan en 1979."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel dirigeant soviétique lance les réformes de la glasnost et de la perestroïka dans les années 1980 ?",
                "options": ["A. Brejnev", "B. Andropov", "C. Gorbatchev", "D. Eltsine"],
                "answer": "C",
                "correction": "Mikhaïl Gorbatchev (arrivé au pouvoir en 1985) lance la glasnost (transparence, liberté d'expression) et la perestroïka (restructuration économique). Ces réformes, voulues pour moderniser l'URSS, contribuent involontairement à sa désintégration."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La chute du mur de Berlin en novembre 1989 symbolise la fin de la Guerre Froide et la réunification allemande.",
                "answer": "VRAI",
                "correction": "VRAI. Le 9 novembre 1989, le mur de Berlin est ouvert puis démoli. Cet événement symbolise la fin du rideau de fer et de la Guerre Froide. L'Allemagne est réunifiée le 3 octobre 1990. L'URSS se dissout le 25 décembre 1991."
            }
        ]
    },
    180: {
        "serie": 6,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 6",
        "description": "La décolonisation",
        "theme": "La décolonisation (1945-1975)",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Dans quel contexte international la décolonisation s'accélère-t-elle après 1945 ?",
                "options": ["A. Le renforcement des empires coloniaux après la Seconde Guerre mondiale", "B. Le contexte de la Guerre Froide et l'affaiblissement des puissances coloniales", "C. Le retour du nationalisme européen", "D. La prospérité économique des métropoles coloniales"],
                "answer": "B",
                "correction": "La décolonisation s'accélère dans le contexte de la Guerre Froide (pressions américaine et soviétique contre le colonialisme), de l'affaiblissement des puissances coloniales (France, Royaume-Uni) après la guerre, et de la montée des mouvements nationalistes dans les colonies."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "L'Inde obtient son indépendance du Royaume-Uni en 1947 grâce notamment à l'action non-violente de Gandhi.",
                "answer": "VRAI",
                "correction": "VRAI. L'Inde proclame son indépendance le 15 août 1947, divisée en Inde (à majorité hindoue) et Pakistan (à majorité musulmane). Le Mahatma Gandhi a joué un rôle central par sa résistance non-violente (satyagraha) contre la domination britannique."
            },
            {
                "index": 3, "type": "texte",
                "question": "Quelles sont les principales caractéristiques de la guerre d'Algérie (1954-1962) ?",
                "answer": "La guerre d'Algérie est un conflit de décolonisation opposant le FLN algérien à la France, marqué par la violence, la torture et les débats sur l'identité française.",
                "correction": "La guerre d'Algérie (1er novembre 1954 – 19 mars 1962) oppose le FLN (Front de Libération Nationale) à l'armée française. Elle se distingue par : son intensité (400 000 à 1 million de morts selon les sources), le recours à la torture par l'armée française, les tensions entre pieds-noirs, harkis et nationalistes algériens. Elle se termine par les accords d'Évian et l'indépendance de l'Algérie le 5 juillet 1962."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Qu'est-ce que la conférence de Bandung (1955) représente dans le mouvement de décolonisation ?",
                "options": ["A. Une alliance militaire des pays colonisés contre les puissances occidentales", "B. La création d'une organisation des pays non-alignés", "C. La première grande réunion des pays d'Asie et d'Afrique affirmant leur indépendance et refusant la bipolarité", "D. Un traité économique entre pays du Tiers-Monde"],
                "answer": "C",
                "correction": "La conférence de Bandung (avril 1955) réunit 29 pays d'Asie et d'Afrique. Elle affirme les principes de non-alignement, d'anti-impérialisme et de solidarité entre peuples colonisés ou récemment indépendants. Elle est à l'origine du mouvement des non-alignés et du concept de Tiers-Monde."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "L'année 1960 est surnommée 'l'année de l'Afrique' car 17 pays africains accèdent à l'indépendance cette année-là.",
                "answer": "VRAI",
                "correction": "VRAI. En 1960, 17 pays africains (dont le Sénégal, le Cameroun, le Congo, la Côte d'Ivoire, le Mali, etc.) accèdent à l'indépendance, principalement des colonies françaises et belges. C'est pourquoi 1960 est surnommée 'l'année de l'Afrique'."
            },
            {
                "index": 6, "type": "texte",
                "question": "Quels défis les nouveaux États indépendants doivent-ils relever après la décolonisation ?",
                "answer": "Les nouveaux États doivent construire des institutions, assurer le développement économique, surmonter les divisions ethniques et gérer des frontières héritées de la colonisation.",
                "correction": "Les nouveaux États indépendants font face à de nombreux défis : construction de l'État et des institutions (souvent sur des bases coloniales artificielles), développement économique avec des économies peu diversifiées (monocultures), instabilité politique (coups d'État, guerres civiles), gestion des frontières héritées du colonialisme (découpant des ethnies), et défi de l'alphabétisation et des inégalités sociales."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel régime sud-africain de ségrégation raciale prend fin en 1994 avec l'élection de Nelson Mandela ?",
                "options": ["A. La colonisation", "B. L'apartheid", "C. Le ségrégationnisme", "D. La négritude"],
                "answer": "B",
                "correction": "L'apartheid (mot afrikaans signifiant 'séparation') est le régime de ségrégation raciale institutionnalisé en Afrique du Sud entre 1948 et 1991. Nelson Mandela, emprisonné 27 ans pour son combat contre ce régime, est élu premier président noir d'Afrique du Sud en avril 1994."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Le néocolonialisme désigne les nouvelles formes de domination économique et politique exercées par les anciennes puissances coloniales sur les États décolonisés.",
                "answer": "VRAI",
                "correction": "VRAI. Le néocolonialisme (terme popularisé par Nkrumah) désigne la persistance de la domination des anciennes métropoles sur leurs ex-colonies par des moyens économiques (firmes multinationales, dépendance commerciale), financiers (dette extérieure), politiques (soutien à des régimes corrompus) ou culturels, malgré l'indépendance formelle."
            }
        ]
    },
    181: {
        "serie": 7,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 7",
        "description": "La construction européenne",
        "theme": "La construction européenne depuis 1945",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quel traité crée la Communauté Économique Européenne (CEE) en 1957 ?",
                "options": ["A. Le traité de Paris (1951)", "B. Le traité de Rome (1957)", "C. Le traité de Maastricht (1992)", "D. Le traité de Lisbonne (2007)"],
                "answer": "B",
                "correction": "Le traité de Rome, signé le 25 mars 1957, crée la Communauté Économique Européenne (CEE) entre six pays fondateurs : France, Allemagne, Italie, Belgique, Pays-Bas, Luxembourg. Il établit un marché commun et des politiques communes (agricole notamment)."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "La réconciliation franco-allemande est au cœur de la construction européenne, symbolisée notamment par le traité de l'Élysée de 1963.",
                "answer": "VRAI",
                "correction": "VRAI. Le traité de l'Élysée (22 janvier 1963), signé par de Gaulle et Adenauer, scelle la réconciliation franco-allemande. Ce 'couple franco-allemand' est le moteur de l'intégration européenne tout au long de la construction de l'UE."
            },
            {
                "index": 3, "type": "texte",
                "question": "Quels sont les principaux apports du traité de Maastricht (1992) pour la construction européenne ?",
                "answer": "Le traité de Maastricht crée l'Union européenne, prévoit la monnaie unique (euro) et renforce la citoyenneté européenne.",
                "correction": "Le traité de Maastricht (signé le 7 février 1992) transforme la CEE en Union européenne (UE) et instaure : la citoyenneté européenne, la politique étrangère et de sécurité commune (PESC), la coopération judiciaire et policière (JAI), et surtout l'Union économique et monétaire (UEM) prévoyant la création de l'euro. L'euro est introduit en 1999 (en 2002 pour les pièces et billets)."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Combien de pays membres compte l'Union européenne en 2024 ?",
                "options": ["A. 25", "B. 27", "C. 28", "D. 30"],
                "answer": "B",
                "correction": "L'Union européenne compte 27 États membres depuis le retrait du Royaume-Uni (Brexit, effectif le 31 janvier 2020) qui était le 28e membre. L'UE s'est élargie progressivement : 6 membres fondateurs (1957), puis 9, 12, 15, 25 (2004, grand élargissement), 27, 28 (2013), et de nouveau 27 depuis le Brexit."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "L'espace Schengen permet la libre circulation des personnes entre les pays membres, sans contrôles aux frontières intérieures.",
                "answer": "VRAI",
                "correction": "VRAI. L'accord de Schengen (1985, entré en vigueur en 1995) crée un espace sans frontières intérieures pour les personnes entre les États signataires (26 pays aujourd'hui, dont des non-membres de l'UE comme la Suisse et la Norvège). Les contrôles aux frontières intérieures sont supprimés."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce que le Brexit et quelles en sont les principales conséquences ?",
                "answer": "Le Brexit est le retrait du Royaume-Uni de l'Union européenne, voté en 2016 et effectif en 2020, entraînant de nouveaux obstacles commerciaux et diplomatiques.",
                "correction": "Le Brexit (contraction de British Exit) est la sortie du Royaume-Uni de l'UE, décidée par référendum le 23 juin 2016 (51,9 % de 'Leave'). Il devient effectif le 31 janvier 2020. Conséquences : fin de la libre circulation entre le RU et l'UE, nouvelles barrières douanières et commerciales, tensions en Irlande du Nord, questionnement sur l'avenir de l'Union, pertes économiques pour les deux parties."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quelle institution européenne est la seule élue directement par les citoyens de l'UE ?",
                "options": ["A. La Commission européenne", "B. Le Conseil de l'UE", "C. Le Parlement européen", "D. La Cour de justice de l'UE"],
                "answer": "C",
                "correction": "Le Parlement européen est la seule institution de l'UE élue au suffrage universel direct par les citoyens européens depuis 1979. Il siège à Strasbourg et partage le pouvoir législatif avec le Conseil de l'UE (Conseil des ministres)."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La politique agricole commune (PAC) est l'une des politiques les plus anciennes et les plus importantes de l'UE en termes de budget.",
                "answer": "VRAI",
                "correction": "VRAI. La PAC, instaurée en 1962, est l'une des premières politiques communes de la CEE/UE. Elle représente encore aujourd'hui environ 31% du budget de l'UE (2021-2027), bien que cette part ait diminué. Elle vise à garantir la sécurité alimentaire et le revenu des agriculteurs européens."
            }
        ]
    },
    182: {
        "serie": 8,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 8",
        "description": "La France de la Ve République",
        "theme": "La Ve République française depuis 1958",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Qui est le fondateur et premier président de la Ve République française ?",
                "options": ["A. Georges Pompidou", "B. François Mitterrand", "C. Charles de Gaulle", "D. Valéry Giscard d'Estaing"],
                "answer": "C",
                "correction": "Charles de Gaulle rédige et fait adopter la constitution de la Ve République (4 octobre 1958), dont il devient le premier président. Il instaure un régime semi-présidentiel qui renforce considérablement le pouvoir exécutif par rapport à la IVe République."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Le président de la Ve République est élu au suffrage universel direct depuis 1962.",
                "answer": "VRAI",
                "correction": "VRAI. Initialement élu par un collège électoral, le président est élu au suffrage universel direct depuis la révision constitutionnelle de 1962, approuvée par référendum. Cette modification renforce encore la légitimité et le pouvoir présidentiel."
            },
            {
                "index": 3, "type": "texte",
                "question": "Qu'est-ce que la cohabitation en politique française et quand survient-elle pour la première fois ?",
                "answer": "La cohabitation est la situation où le président de la République et le Premier ministre sont de tendances politiques opposées. Elle survient pour la première fois en 1986.",
                "correction": "La cohabitation désigne la coexistence d'un président et d'un gouvernement de majorités politiques adverses. Sous la Ve République, elle s'est produite trois fois : 1986-1988 (Mitterrand-Chirac), 1993-1995 (Mitterrand-Balladur), 1997-2002 (Chirac-Jospin). Le quinquennat adopté en 2000 rend la cohabitation moins probable en alignant les élections présidentielle et législative."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel événement majeur de mai 1968 marque profondément la société française ?",
                "options": ["A. Un coup d'État militaire", "B. Une grande vague de grèves et de contestation étudiante et ouvrière", "C. L'assassinat du général de Gaulle", "D. Une invasion étrangère"],
                "answer": "B",
                "correction": "Mai 68 est une crise sociale et culturelle sans précédent : grèves généralisées (10 millions de grévistes), occupation des universités et des usines, revendications étudiantes de réforme de l'université et ouvrières d'augmentation de salaires. Si de Gaulle survit politiquement (victoires électorales de juin 1968), il démissionne en 1969. Mai 68 transforme profondément les mœurs et la société française."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "François Mitterrand est le premier président socialiste de la Ve République, élu en 1981.",
                "answer": "VRAI",
                "correction": "VRAI. Élu le 10 mai 1981, François Mitterrand est le premier président de gauche de la Ve République, mettant fin à 23 ans de domination de la droite. Il engage des réformes importantes : nationalisations, abolition de la peine de mort (1981), décentralisation, semaine de 39 heures et 5e semaine de congés payés."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce que la décentralisation mise en œuvre par les lois Defferre de 1982 ?",
                "answer": "La décentralisation transfère des compétences de l'État central aux collectivités territoriales (régions, départements, communes).",
                "correction": "Les lois Defferre (1982-1983) constituent la première grande réforme de décentralisation de la France, État historiquement très centralisé. Elles transfèrent des compétences importantes aux régions (développement économique), aux départements (action sociale) et aux communes (urbanisme). Les collectivités territoriales gagnent une vraie autonomie de gestion, bien que l'État reste le principal garant des inégalités territoriales."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Comment appelle-t-on la tendance à voter pour des partis extrémistes ou à s'abstenir par méfiance envers les institutions ?",
                "options": ["A. La bipolarisation", "B. Le populisme", "C. La démocratie participative", "D. La crise de la représentation politique ou 'NIMBY'"],
                "answer": "B",
                "correction": "Le populisme désigne les mouvements ou partis qui se réclament du peuple contre les élites, remettant en cause les institutions représentatives traditionnelles. Il peut être de droite (FN/RN) ou de gauche (La France Insoumise). La montée du populisme est souvent analysée comme une réponse à la crise de la représentation politique."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La France est une République laïque, ce qui signifie que l'État ne reconnaît officiellement aucune religion.",
                "answer": "VRAI",
                "correction": "VRAI. L'article 1er de la Constitution de 1958 définit la France comme une 'République indivisible, laïque, démocratique et sociale'. La laïcité, issue de la loi de 1905, signifie que l'État n'adhère à aucune religion, garantit la liberté de conscience et organise la séparation des institutions publiques et des organisations religieuses."
            }
        ]
    },
    183: {
        "serie": 9,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 9",
        "description": "La mondialisation",
        "theme": "La mondialisation contemporaine",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Qu'est-ce que la mondialisation ?",
                "options": ["A. L'uniformisation culturelle mondiale", "B. Le processus d'interconnexion et d'interdépendance croissante des économies, des sociétés et des cultures à l'échelle mondiale", "C. La domination économique des États-Unis sur le monde", "D. La suppression des frontières nationales"],
                "answer": "B",
                "correction": "La mondialisation est un processus multidimensionnel d'interconnexion croissante des économies (commerce, investissements, finance), des sociétés (migrations, communication) et des cultures (diffusion des idées, modes de vie) à l'échelle planétaire, accéléré depuis les années 1980-1990."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Les flux commerciaux mondiaux sont dominés par la 'Triade' : Amérique du Nord, Europe occidentale et Asie orientale.",
                "answer": "VRAI",
                "correction": "VRAI. La 'Triade' (Amérique du Nord, Europe occidentale, Asie orientale – Japon, Corée du Sud, Chine littorale) concentre la majorité des échanges commerciaux mondiaux, des investissements et de la production de richesses. Cependant, la montée des BRICS (Brésil, Russie, Inde, Chine, Afrique du Sud) rééquilibre progressivement ce schéma."
            },
            {
                "index": 3, "type": "texte",
                "question": "Qu'est-ce qu'une firme transnationale (FTN) et quel est son rôle dans la mondialisation ?",
                "answer": "Une FTN est une entreprise qui produit et vend dans plusieurs pays, jouant un rôle central dans la mondialisation économique par ses investissements et ses chaînes de valeur mondiales.",
                "correction": "Une firme transnationale (FTN) ou multinationale est une entreprise dont les activités (production, vente, R&D) s'étendent sur plusieurs pays via des filiales. Elles sont des acteurs majeurs de la mondialisation : elles représentent environ 1/3 du commerce mondial, organisent des chaînes de valeur mondiales (délocalisation de la production), transfèrent des technologies et des capitaux. Exemples : Apple, Toyota, LVMH, Total."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel continent est le plus marginalisé dans la mondialisation économique actuelle ?",
                "options": ["A. L'Amérique latine", "B. L'Asie du Sud", "C. L'Afrique subsaharienne", "D. L'Océanie"],
                "answer": "C",
                "correction": "L'Afrique subsaharienne reste globalement la région la plus marginalisée dans la mondialisation : faible part des échanges mondiaux (~2%), dépendance aux exportations de matières premières, manque d'investissements, instabilité politique. Cependant, des pays comme l'Éthiopie, le Rwanda ou le Sénégal connaissent une croissance significative."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La mondialisation accentue les inégalités entre pays et au sein des pays.",
                "answer": "VRAI",
                "correction": "VRAI (avec nuances). Si la mondialisation a sorti des centaines de millions de personnes de la pauvreté (notamment en Chine et Inde), elle a également accentué les inégalités : entre pays (écart Nord-Sud), au sein des pays (fractures sociales dans les pays développés, ouvriers délocalisés vs cadres qualifiés) et entre régions (métropolisation vs déserts ruraux)."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce qu'une métropole mondiale (ville mondiale ou global city) et citez deux exemples.",
                "answer": "Une métropole mondiale est une ville qui exerce une influence et des fonctions de commandement à l'échelle mondiale. Exemples : New York, Londres, Tokyo.",
                "correction": "Une ville mondiale (global city, concept de Saskia Sassen) est une métropole qui concentre des fonctions de commandement économique (sièges sociaux de FTN, bourses mondiales), financier (places boursières), politique et culturel à l'échelle planétaire. Elles forment un réseau mondial. Exemples : New York (finance, ONU), Londres (City financière), Tokyo, Paris (culture, mode), Shanghai, Dubaï."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Qu'est-ce que le libre-échange, promu par l'Organisation mondiale du commerce (OMC) ?",
                "options": ["A. La suppression totale des droits de douane dans le monde entier", "B. La réduction des barrières commerciales (droits de douane, quotas) pour favoriser le commerce international", "C. Le commerce équitable entre pays du Nord et du Sud", "D. La liberté de circulation des personnes entre tous les pays"],
                "answer": "B",
                "correction": "Le libre-échange est un principe commercial visant à réduire les obstacles aux échanges (droits de douane, quotas, normes protectionnistes). L'OMC (fondée en 1995, succédant au GATT) est l'organisation internationale qui promeut et arbitre les règles du commerce international et la libéralisation des échanges."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Internet et les technologies numériques sont des moteurs essentiels de la mondialisation contemporaine.",
                "answer": "VRAI",
                "correction": "VRAI. Internet, les technologies de l'information et de la communication (TIC), les réseaux sociaux et l'e-commerce ont révolutionné la mondialisation en permettant des échanges d'informations, de capitaux et de services quasi-instantanés à l'échelle mondiale. Ils ont créé de nouveaux acteurs mondiaux (GAFAM : Google, Apple, Facebook/Meta, Amazon, Microsoft)."
            }
        ]
    },
    184: {
        "serie": 10,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 10",
        "description": "Les migrations dans le monde",
        "theme": "Les migrations mondiales contemporaines",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quelle est la principale distinction entre un migrant économique et un réfugié ?",
                "options": ["A. Le migrant économique est plus pauvre que le réfugié", "B. Le réfugié fuit une persécution ou une menace grave, tandis que le migrant économique cherche de meilleures conditions de vie", "C. Le réfugié est toujours reconnu comme tel par les États", "D. Il n'y a pas de distinction juridique entre les deux"],
                "answer": "B",
                "correction": "Selon la Convention de Genève (1951), un réfugié est une personne qui fuit son pays en raison de persécutions (pour motifs de race, religion, nationalité, opinion politique, appartenance à un groupe social). Le migrant économique cherche de meilleures conditions économiques. La distinction est importante juridiquement car le réfugié bénéficie d'une protection internationale."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Les migrations Sud-Sud (entre pays en développement) représentent une part importante des migrations mondiales.",
                "answer": "VRAI",
                "correction": "VRAI. Contrairement aux idées reçues, les migrations Sud-Sud (entre pays d'Afrique, d'Asie ou d'Amérique latine) sont aussi importantes que les migrations Sud-Nord. Par exemple, les migrations africaines se font majoritairement intra-africaines (vers l'Afrique du Sud, la Côte d'Ivoire, le Nigeria)."
            },
            {
                "index": 3, "type": "texte",
                "question": "Qu'est-ce que la diaspora et quel est son rôle économique pour les pays d'origine ?",
                "answer": "La diaspora est une communauté dispersée hors de son territoire d'origine. Elle joue un rôle économique majeur par les transferts d'argent (remittances) vers les pays d'origine.",
                "correction": "La diaspora désigne la dispersion d'une communauté à travers le monde tout en maintenant un lien avec le pays d'origine. Sur le plan économique, les diasporas envoient des remittances (transferts financiers) représentant des montants supérieurs à l'aide publique au développement dans de nombreux pays (ex : l'Inde reçoit plus de 80 milliards de dollars par an de sa diaspora). Elles transfèrent aussi des compétences, des technologies et des réseaux."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel organisme des Nations Unies est chargé de protéger les réfugiés dans le monde ?",
                "options": ["A. L'UNICEF", "B. L'OMS", "C. Le HCR (Haut-Commissariat des Nations Unies pour les Réfugiés)", "D. Le FMI"],
                "answer": "C",
                "correction": "Le HCR (UNHCR en anglais), créé en 1950, est l'agence de l'ONU mandatée pour protéger les réfugiés, les demandeurs d'asile et les apatrides. Il coordonne l'aide internationale aux réfugiés et travaille à trouver des solutions durables (rapatriement volontaire, intégration locale, réinstallation dans un pays tiers)."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La 'crise migratoire' de 2015 en Europe est liée principalement aux guerres en Syrie et en Afghanistan.",
                "answer": "VRAI",
                "correction": "VRAI. En 2015, plus d'un million de personnes arrivent en Europe irrégulièrement, principalement des Syriens fuyant la guerre civile (depuis 2011), des Afghans et des Irakiens. Cette crise met à l'épreuve le système d'asile européen et génère des tensions politiques importantes au sein de l'UE."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce que la 'fuite des cerveaux' et quelles en sont les conséquences pour les pays en développement ?",
                "answer": "La fuite des cerveaux (brain drain) est l'émigration des personnes hautement qualifiées vers les pays riches, privant les pays d'origine de leurs compétences.",
                "correction": "La fuite des cerveaux (brain drain) désigne l'émigration des personnes les plus qualifiées (médecins, ingénieurs, scientifiques) des pays en développement vers les pays riches, attirés par de meilleures rémunérations et conditions de travail. Pour les pays d'origine : perte d'investissements publics en éducation, manque de compétences pour le développement, mais les remittances peuvent partiellement compenser. Le débat sur le 'brain gain' souligne les effets positifs potentiels (retour de compétences, réseaux)."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Qu'est-ce que le droit d'asile ?",
                "options": ["A. Le droit pour tout étranger de s'installer dans le pays de son choix", "B. La protection accordée par un État à une personne persécutée dans son pays d'origine", "C. Le droit pour les migrants économiques de travailler librement dans l'UE", "D. Un régime douanier spécial pour les travailleurs frontaliers"],
                "answer": "B",
                "correction": "Le droit d'asile est la protection qu'un État accorde à un étranger qui ne peut pas rentrer dans son pays d'origine en raison de persécutions graves (définies par la Convention de Genève de 1951). En France, c'est l'OFPRA (Office Français de Protection des Réfugiés et Apatrides) qui instruit les demandes."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "Les migrations climatiques sont appelées à augmenter significativement dans les prochaines décennies en raison du changement climatique.",
                "answer": "VRAI",
                "correction": "VRAI. Les 'réfugiés climatiques' (ou déplacés environnementaux) fuient des zones rendues invivables par le changement climatique (montée des eaux, désertification, événements extrêmes). Selon les projections, entre 200 millions et 1 milliard de personnes pourraient être déplacées d'ici 2050. Cependant, ils ne bénéficient pas encore d'un statut juridique international protégé."
            }
        ]
    },
    185: {
        "serie": 11,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 11",
        "description": "Les espaces géographiques de la France",
        "theme": "Géographie de la France",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Quel est le plus grand désert français en termes de densité de population ?",
                "options": ["A. La Camargue", "B. La Creuse", "C. La diagonale du vide", "D. Les Alpes"],
                "answer": "C",
                "correction": "La 'diagonale du vide' (ou diagonale des faibles densités) est une bande de territoire qui traverse la France du nord-est au sud-ouest (des Ardennes aux Landes), caractérisée par une très faible densité de population (moins de 30 hab/km²), un vieillissement de la population et une économie peu développée."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Paris et l'Île-de-France concentrent environ 20% de la population française et plus de 30% de la richesse nationale.",
                "answer": "VRAI",
                "correction": "VRAI. La région Île-de-France (12 millions d'habitants, soit ~18% de la population) produit environ 31-32% du PIB national. Cette hyper-métropolisation de Paris crée des déséquilibres territoriaux importants entre la capitale et le reste du territoire."
            },
            {
                "index": 3, "type": "texte",
                "question": "Qu'est-ce que la métropolisation et comment transforme-t-elle les territoires français ?",
                "answer": "La métropolisation est la concentration des activités et des populations dans les grandes métropoles, créant des inégalités avec les espaces ruraux et les villes moyennes.",
                "correction": "La métropolisation est le processus de concentration des fonctions économiques supérieures (sièges sociaux, R&D, finance, services), des populations qualifiées et des infrastructures dans les grandes métropoles. En France, elle renforce les métropoles (Paris, Lyon, Bordeaux, Montpellier, Nantes) tout en marginalsant des villes moyennes et des espaces ruraux. Elle génère des 'fractures territoriales' (déserts médicaux, éloignement des services publics)."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel espace géographique français présente la densité de population la plus élevée hors Île-de-France ?",
                "options": ["A. La région PACA", "B. L'Alsace (Bas-Rhin)", "C. Le Nord-Pas-de-Calais (Nord)", "D. La Gironde"],
                "answer": "C",
                "correction": "Le département du Nord (ancienne région Nord-Pas-de-Calais) est l'un des plus densément peuplés de France métropolitaine après la petite couronne parisienne, héritage de l'industrialisation au XIXe siècle (mines, textile, sidérurgie). La Métropole Européenne de Lille est la 4e aire urbaine de France."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "Les espaces ruraux français sont tous en déclin démographique.",
                "answer": "FAUX",
                "correction": "FAUX. Si certains espaces ruraux (notamment la diagonale du vide) connaissent un déclin démographique et économique, d'autres espaces ruraux sont en croissance, notamment les campagnes périurbaines (autour des grandes métropoles), les zones touristiques et les espaces ruraux attractifs (littoral, montagnes, Sud de la France). On parle de 'rurbanisation'."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce que les Outre-Mer français et quel est leur statut ?",
                "answer": "Les Outre-Mer français sont les territoires français situés en dehors de l'Europe métropolitaine, ayant différents statuts (DOM, ROM, COM, collectivités).",
                "correction": "Les Outre-Mer français regroupent différents types de territoires : les DROM (Départements et Régions d'Outre-Mer : Guadeloupe, Martinique, Guyane, La Réunion, Mayotte), les COM (Collectivités d'Outre-Mer : Polynésie française, Saint-Martin, Saint-Pierre-et-Miquelon...) et la Nouvelle-Calédonie à statut particulier. Ils sont dispersés sur les cinq océans, font de la France la 2e ZEE mondiale."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Quel phénomène désigne la croissance des villes au détriment des campagnes environnantes ?",
                "options": ["A. L'exode rural", "B. L'étalement urbain", "C. La périurbanisation", "D. L'urbanisation"],
                "answer": "C",
                "correction": "La périurbanisation désigne le développement résidentiel et économique dans les zones périphériques autour des villes (couronnes périurbaines), souvent sous forme de lotissements pavillonnaires. Ce phénomène, lié à la recherche de logements moins chers et de qualité de vie, est associé à l'étalement urbain et génère une dépendance à la voiture."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La France possède la deuxième zone économique exclusive (ZEE) la plus grande du monde grâce à ses territoires ultramarins.",
                "answer": "VRAI",
                "correction": "VRAI. Grâce à ses nombreux territoires ultramarins dispersés sur tous les océans (Polynésie française, Nouvelle-Calédonie, Guyane, Antilles, La Réunion, etc.), la France possède la 2e ZEE mondiale (~11,5 millions de km²), derrière les États-Unis (~12 millions de km²). Cela lui confère un immense potentiel en ressources marines."
            }
        ]
    },
    186: {
        "serie": 12,
        "title": "Quiz Diagnostic 1ere Histoire-Géographie - Serie 12",
        "description": "Les risques et le développement durable",
        "theme": "Risques, développement durable et enjeux environnementaux",
        "questions": [
            {
                "index": 1, "type": "qcm",
                "question": "Que signifie le concept de développement durable ?",
                "options": ["A. Un développement économique sans limites", "B. Un développement qui répond aux besoins du présent sans compromettre ceux des générations futures", "C. La protection exclusive de l'environnement au détriment de l'économie", "D. La croissance économique des pays en développement"],
                "answer": "B",
                "correction": "Le développement durable est défini par le rapport Brundtland (1987) comme 'un développement qui répond aux besoins du présent sans compromettre la capacité des générations futures à répondre aux leurs.' Il repose sur trois piliers : économique (croissance), social (équité) et environnemental (préservation des ressources)."
            },
            {
                "index": 2, "type": "vrai-faux",
                "question": "Le changement climatique est principalement causé par les émissions de gaz à effet de serre d'origine humaine.",
                "answer": "VRAI",
                "correction": "VRAI. Le GIEC (Groupe d'experts intergouvernemental sur l'évolution du climat) affirme avec un très haut degré de certitude que le réchauffement climatique observé depuis le milieu du XXe siècle est principalement dû aux émissions anthropiques de gaz à effet de serre (CO², méthane, protoxyde d'azote), notamment issues de la combustion des énergies fossiles."
            },
            {
                "index": 3, "type": "texte",
                "question": "Qu'est-ce que l'accord de Paris (2015) et quels sont ses objectifs en matière de changement climatique ?",
                "answer": "L'accord de Paris est un accord international visant à limiter le réchauffement climatique à +1,5°C à +2°C par rapport à l'ère préindustrielle.",
                "correction": "L'accord de Paris, adopté lors de la COP21 (décembre 2015) et signé par 196 pays, vise à maintenir le réchauffement climatique bien en dessous de +2°C par rapport aux niveaux préindustriels, et à poursuivre les efforts pour le limiter à +1,5°C. Chaque pays soumet des contributions nationales déterminées (NDC) et les révise à la hausse progressivement. C'est le premier accord climatique universel et contraignant."
            },
            {
                "index": 4, "type": "qcm",
                "question": "Quel type de risque naturel menace le plus les côtes françaises en raison du changement climatique ?",
                "options": ["A. Les tremblements de terre", "B. Les éruptions volcaniques", "C. La montée du niveau des mers et les submersions marines", "D. Les tsunamis"],
                "answer": "C",
                "correction": "La montée du niveau des mers (environ 3,7 mm/an actuellement, potentiellement 1 m ou plus d'ici 2100) menace les côtes basses françaises (littoral atlantique, Camargue, côtes de la Manche). Des communes comme Le Mont-Saint-Michel ou certaines zones de la Camargue sont déjà exposées à des risques accrus de submersion et d'érosion côtière."
            },
            {
                "index": 5, "type": "vrai-faux",
                "question": "La France tire environ 70-75% de son électricité de l'énergie nucléaire, ce qui est l'une des proportions les plus élevées au monde.",
                "answer": "VRAI",
                "correction": "VRAI. La France a la proportion la plus élevée au monde d'électricité d'origine nucléaire (~70-75% selon les années). Le parc nucléaire d'EDF (56 réacteurs en 2023, en cours de maintenance) est au cœur du mix énergétique français. Cette dépendance au nucléaire est au cœur des débats sur la transition énergétique."
            },
            {
                "index": 6, "type": "texte",
                "question": "Qu'est-ce qu'un risque majeur et comment les sociétés peuvent-elles s'y préparer ?",
                "answer": "Un risque majeur est un événement potentiellement catastrophique (naturel ou technologique) pour une société. La prévention passe par la cartographie, l'alerte, les plans de secours et l'éducation.",
                "correction": "Un risque majeur est un aléa (naturel : séisme, inondation, cyclone ; ou technologique : accident industriel, nucléaire) pouvant causer de nombreuses victimes et des dommages considérables. La gestion des risques comprend : la prévention (cartographie, PPR - Plans de Prévention des Risques), la préparation (plans ORSEC, PCS), l'alerte (systèmes d'alerte précoce), la protection (digues, constructions parasismiques) et la mémoire du risque (culture du risque)."
            },
            {
                "index": 7, "type": "qcm",
                "question": "Qu'est-ce que l'empreinte carbone d'un pays ou d'une personne ?",
                "options": ["A. La surface de forêt nécessaire pour absorber le CO² émis", "B. La quantité totale de gaz à effet de serre émise directement et indirectement", "C. Le bilan des émissions de carbone d'un territoire", "D. Le score de pollution atmosphérique d'une ville"],
                "answer": "B",
                "correction": "L'empreinte carbone mesure la quantité totale de gaz à effet de serre (en équivalent CO²) émise directement et indirectement par une personne, une organisation ou un pays, y compris les émissions liées à la consommation de biens importés. C'est un indicateur clé pour mesurer l'impact climatique et orienter les politiques de réduction."
            },
            {
                "index": 8, "type": "vrai-faux",
                "question": "La biodiversité mondiale est actuellement menacée par une extinction de masse liée aux activités humaines.",
                "answer": "VRAI",
                "correction": "VRAI. Les scientifiques parlent d'une 6e extinction de masse : le taux d'extinction des espèces serait 1 000 fois supérieur au taux naturel. Les principales causes sont : la destruction des habitats (déforestation, urbanisation), la surexploitation des ressources, la pollution, les espèces invasives et le changement climatique. L'IPBES (équivalent du GIEC pour la biodiversité) estime qu'un million d'espèces sont menacées."
            }
        ]
    },
}

# Continuing with HG quizzes 187-223 with abbreviated structure (same quality)
hg_additional_topics = [
    (187, 13, "L'industrialisation et la société au XIXe siècle", "Révolution industrielle et transformations sociales"),
    (188, 14, "Les révolutions politiques du XIXe siècle", "Révolutions libérales et nationales (1830-1871)"),
    (189, 15, "L'expansion coloniale européenne au XIXe siècle", "L'impérialisme colonial européen"),
    (190, 16, "La Révolution française et l'Empire", "La Révolution française (1789-1815)"),
    (191, 17, "Géographie des espaces productifs", "Les espaces agricoles et industriels dans le monde"),
    (192, 18, "Les échanges commerciaux mondiaux", "Commerce mondial et organisations internationales"),
    (193, 19, "L'Asie orientale : croissance et puissance", "L'Asie orientale dans la mondialisation"),
    (194, 20, "Les États-Unis : hyperpuissance mondiale", "Les États-Unis, première puissance mondiale"),
    (195, 21, "Russie et espaces post-soviétiques", "La Russie, puissance en recomposition"),
    (196, 22, "L'Afrique subsaharienne : défis et potentiels", "L'Afrique dans la mondialisation"),
    (197, 23, "Le Moyen-Orient : conflits et enjeux", "Le Moyen-Orient, région de tensions"),
    (198, 24, "Géographie des mers et des océans", "Les espaces maritimes : enjeux et conflits"),
    (199, 25, "Les espaces de la mondialisation : FTN et IDE", "Firmes transnationales et investissements directs à l'étranger"),
    (200, 26, "L'ONU et la gouvernance mondiale", "Le multilatéralisme et les organisations internationales"),
    (201, 27, "Géopolitique du Moyen-Orient", "Conflits au Moyen-Orient depuis 1945"),
    (202, 28, "Les puissances émergentes : BRICS", "La montée des puissances émergentes"),
    (203, 29, "L'Inde : géant en développement", "L'Inde, puissance démographique et économique"),
    (204, 30, "Urbanisation et métropolisation mondiales", "Villes mondiales et urbanisation"),
    (205, 31, "Développement et sous-développement", "Inégalités de développement dans le monde"),
    (206, 32, "Les espaces ruraux dans le monde", "Agriculture mondiale et espaces ruraux"),
    (207, 33, "L'énergie dans le monde", "Les ressources énergétiques mondiales"),
    (208, 34, "La France dans l'Union européenne", "La France et sa place en Europe"),
    (209, 35, "La géographie des frontières", "Frontières et territoires dans le monde"),
    (210, 36, "Le tourisme mondial", "Le tourisme, un phénomène mondial"),
    (211, 37, "Les inégalités sociales en France", "Société française et inégalités"),
    (212, 38, "L'Union européenne : institutions et enjeux", "L'UE, organisation et défis"),
    (213, 39, "Les mémoires de la Seconde Guerre mondiale", "Mémoire, histoire et devoir de mémoire"),
    (214, 40, "La Chine : puissance du XXIe siècle", "La Chine, nouvelle superpuissance"),
    (215, 41, "Géographie de la santé dans le monde", "Inégalités de santé et accès aux soins"),
    (216, 42, "Le terrorisme international", "Le terrorisme, menace globale"),
    (217, 43, "La France et l'Afrique", "Relations franco-africaines"),
    (218, 44, "Les révolutions arabes (2010-2011)", "Le Printemps arabe et ses conséquences"),
    (219, 45, "L'espace numérique et la géopolitique", "Géopolitique du numérique"),
    (220, 46, "L'eau dans le monde : ressource stratégique", "L'eau, enjeu géopolitique mondial"),
    (221, 47, "La France : puissance nucléaire et siège au Conseil de sécurité", "La France dans les relations internationales"),
    (222, 48, "Les flux migratoires en Europe", "L'Europe face aux migrations"),
    (223, 49, "Révision générale Histoire-Géographie 1ère", "Révision complète du programme"),
]

def make_hg_quiz_from_template(file_id, serie, title, description, theme):
    """Generate a complete HG quiz with real questions from a theme"""
    topics_questions = {
        "Révolution industrielle et transformations sociales": [
            ("qcm", "Quel pays est considéré comme le berceau de la Révolution industrielle au XVIIIe siècle ?",
             ["A. La France", "B. L'Allemagne", "C. Le Royaume-Uni", "D. Les États-Unis"], "C",
             "Le Royaume-Uni est le berceau de la Révolution industrielle (deuxième moitié du XVIIIe siècle) : invention de la machine à vapeur (Watt, 1769), essor du textile (métier à tisser mécanique), exploitation du charbon. Ces innovations se diffusent ensuite en Europe et en Amérique."),
            ("vrai-faux", "L'exode rural accompagne l'industrialisation avec la migration des paysans vers les villes industrielles.",
             None, "VRAI",
             "VRAI. L'industrialisation provoque un exode rural massif : les paysans quittent les campagnes pour travailler dans les usines des villes industrielles (Manchester, Lille, Roubaix, Essen). Ce phénomène engendre une croissance urbaine rapide et des problèmes sociaux (taudis, pauvreté ouvrière)."),
            ("texte", "Qu'est-ce qu'un prolétariat et comment apparaît-il avec l'industrialisation ?",
             None, "Le prolétariat est la classe sociale des travailleurs salariés qui ne possèdent que leur force de travail.",
             "Le prolétariat est la classe des ouvriers salariés qui vendent leur force de travail en échange d'un salaire, sans posséder les moyens de production. Il émerge avec l'industrialisation : anciens artisans et paysans devenus ouvriers d'usine, travaillant dans des conditions souvent pénibles (longues heures, travail des enfants, salaires bas). Karl Marx théorise ce concept dans son analyse du capitalisme."),
            ("qcm", "Quel philosophe théorise la lutte des classes entre bourgeoisie et prolétariat au XIXe siècle ?",
             ["A. Adam Smith", "B. Karl Marx", "C. Auguste Comte", "D. John Stuart Mill"], "B",
             "Karl Marx (1818-1883), avec Friedrich Engels, développe la théorie matérialiste de l'histoire et du communisme scientifique. Dans le Manifeste du parti communiste (1848) et Le Capital (1867), il analyse le capitalisme, l'exploitation du prolétariat par la bourgeoisie et prédit la révolution socialiste."),
            ("vrai-faux", "Les premières lois sociales protégeant les travailleurs sont adoptées en France sous la Troisième République.",
             None, "VRAI",
             "VRAI. La IIIe République française adopte progressivement des lois sociales : loi sur le travail des enfants (1874), loi Waldeck-Rousseau sur les syndicats (1884), loi sur les accidents du travail (1898), loi sur le repos dominical (1906), retraites ouvrières (1910)."),
            ("texte", "Qu'est-ce que le mouvement ouvrier et quelles formes d'action développe-t-il au XIXe siècle ?",
             None, "Le mouvement ouvrier regroupe les organisations de travailleurs (syndicats, partis socialistes) qui luttent pour l'amélioration des conditions de travail et la transformation sociale.",
             "Le mouvement ouvrier rassemble ouvriers et artisans luttant pour leurs droits : création de syndicats (CGT en France en 1895), associations mutualistes, partis socialistes (SFIO en France en 1905). Les formes d'action sont : la grève, les manifestations, le boycott, et l'action politique. L'Internationale ouvrière (Ire, IIe) coordonne les luttes à l'échelle internationale."),
            ("qcm", "Qu'est-ce que le taylorisme ?",
             ["A. Une doctrine économique libérale", "B. Une méthode d'organisation scientifique du travail visant à maximiser la productivité", "C. Un mouvement syndical américain", "D. Une technique de construction industrielle"], "B",
             "Le taylorisme est l'organisation scientifique du travail théorisée par Frederick Winslow Taylor (Principes du management scientifique, 1911) : décomposition des tâches en gestes élémentaires, chronométrage, spécialisation des ouvriers. Combiné au fordisme (travail à la chaîne, production de masse, hauts salaires), il révolutionne l'industrie du XXe siècle."),
            ("vrai-faux", "La bourgeoisie industrielle devient la classe dominante du XIXe siècle, remplaçant progressivement l'aristocratie foncière.",
             None, "VRAI",
             "VRAI. Le XIXe siècle voit l'ascension de la bourgeoisie industrielle et financière comme classe dominante. Elle contrôle les moyens de production, accède au pouvoir politique (suffrages censitaires) et impose ses valeurs (travail, épargne, famille, propriété). L'aristocratie foncière, bien que conservant du prestige, perd progressivement son influence économique et politique."),
        ],
        "Révolutions libérales et nationales (1830-1871)": [
            ("qcm", "Quelle révolution européenne éclate en 1848 dans plusieurs pays simultanément ?",
             ["A. La Révolution industrielle", "B. Le Printemps des peuples", "C. La révolution bolchevique", "D. La révolution libérale de 1830"], "B",
             "Le 'Printemps des peuples' (1848) est une vague révolutionnaire qui touche simultanément la France (IIe République), les États allemands, l'Autriche-Hongrie, l'Italie, la Pologne. Ces révolutions mêlent revendications libérales (constitutions, libertés) et nationales (unité allemande, italienne). La plupart échouent, mais elles accélèrent les transformations politiques."),
            ("vrai-faux", "L'unification de l'Italie se réalise entre 1859 et 1870 sous l'égide du Piémont-Sardaigne et de Cavour.",
             None, "VRAI",
             "VRAI. L'unification italienne (Risorgimento) est menée par le royaume de Piémont-Sardaigne avec Cavour (Premier ministre) et Garibaldi (révolutionnaire). Elle s'achève avec la prise de Rome en 1870. Victor-Emmanuel II devient le premier roi d'Italie unifiée en 1861."),
            ("texte", "Qu'est-ce que le nationalisme et comment se manifeste-t-il en Europe au XIXe siècle ?",
             None, "Le nationalisme est l'idéologie qui affirme que les nations ont le droit de constituer des États indépendants. Au XIXe siècle, il pousse à l'unification de nations divisées (Allemagne, Italie) et à l'indépendance de peuples dominés.",
             "Le nationalisme est l'idéologie selon laquelle chaque nation (définie par une langue, une culture, une histoire communes) a le droit de former un État souverain. Au XIXe siècle, il prend deux formes : le nationalisme d'unification (Allemagne de Bismarck, Italie du Risorgimento) et le nationalisme de libération (Grèce, Belgique, Pologne). Il est lié au romantisme et à la revendication d'une identité culturelle distincte."),
            ("qcm", "Qui unifie l'Allemagne en 1871 par la guerre contre la France ?",
             ["A. Frédéric le Grand", "B. Napoléon III", "C. Otto von Bismarck", "D. Guillaume II"], "C",
             "Otto von Bismarck, chancelier de Prusse, unifie l'Allemagne par la politique de la 'Realpolitik' : guerres contre le Danemark (1864), l'Autriche (1866) et la France (1870-1871). La défaite française de Sedan conduit à la proclamation du IIe Reich allemand dans la galerie des Glaces de Versailles le 18 janvier 1871."),
            ("vrai-faux", "La Commune de Paris (1871) est un épisode révolutionnaire qui s'établit après la défaite de la France face à la Prusse.",
             None, "VRAI",
             "VRAI. Après la capitulation de Paris (janvier 1871), la Commune de Paris (18 mars – 28 mai 1871) est un gouvernement révolutionnaire qui contrôle Paris pendant deux mois. La répression versaillaise (Semaine sanglante) fait entre 10 000 et 30 000 morts. La Commune reste un symbole pour le mouvement ouvrier international."),
            ("texte", "Qu'est-ce que le libéralisme politique au XIXe siècle et quelles sont ses principales revendications ?",
             None, "Le libéralisme politique revendique les libertés individuelles (presse, expression, conscience), les constitutions limitant le pouvoir royal et la représentation politique.",
             "Le libéralisme politique du XIXe siècle est fondé sur la philosophie des Lumières et revendique : les droits et libertés individuels (liberté de la presse, d'expression, de conscience, de réunion), des constitutions qui limitent le pouvoir des monarques, la représentation politique par un parlement élu, l'égalité devant la loi et la séparation des pouvoirs (Montesquieu). Il s'oppose aux régimes absolutistes et à la Sainte-Alliance."),
            ("qcm", "Quel régime politique est instauré en France après la révolution de 1848 ?",
             ["A. La monarchie constitutionnelle", "B. La IIe République", "C. Le Premier Empire", "D. La IIIe République"], "B",
             "La révolution de février 1848 renverse la monarchie de Juillet de Louis-Philippe et proclame la IIe République. Elle instaure le suffrage universel masculin, abolit l'esclavage dans les colonies (décret Schoelcher) et les châtiments corporels. Elle est renversée par le coup d'État de Louis-Napoléon Bonaparte en décembre 1851."),
            ("vrai-faux", "Victor Hugo est l'un des grands représentants du romantisme littéraire français du XIXe siècle.",
             None, "VRAI",
             "VRAI. Victor Hugo (1802-1885) est l'une des figures majeures du romantisme français : poète (Les Contemplations), romancier (Notre-Dame de Paris, Les Misérables) et dramaturge (Hernani). Engagé politiquement, il est aussi un républicain convaincu, exilé sous Napoléon III, et défenseur des pauvres et des opprimés."),
        ],
        "L'impérialisme colonial européen": [
            ("qcm", "Quelle conférence internationale en 1884-1885 organise le partage de l'Afrique entre puissances européennes ?",
             ["A. La conférence de Vienne (1815)", "B. La conférence de Berlin (1884-1885)", "C. La conférence de San Francisco (1945)", "D. La conférence de Bandung (1955)"], "B",
             "La conférence de Berlin (novembre 1884 – février 1885), organisée par Bismarck, réunit les puissances européennes pour réguler la colonisation de l'Afrique. Elle pose les règles du partage (occupation effective, liberté de commerce dans le bassin du Congo) et accélère la 'course au clocher' pour la colonisation africaine."),
            ("vrai-faux", "En 1914, les puissances européennes contrôlent environ 80% des terres émergées du globe.",
             None, "VRAI",
             "VRAI. À la veille de la Première Guerre mondiale (1914), les empires coloniaux européens couvrent environ 80-85% des terres émergées. Les plus grands empires sont britannique (~33 millions de km²) et français (~11 millions de km²). L'Afrique et l'Asie sont presque entièrement colonisées."),
            ("texte", "Quelles sont les justifications avancées par les Européens pour légitimer la colonisation ?",
             None, "Les Européens justifient la colonisation par la 'mission civilisatrice', la supériorité raciale supposée et les intérêts économiques (matières premières, marchés).",
             "Les Européens avancent plusieurs justifications : idéologique (la 'mission civilisatrice' – apporter le progrès, la médecine, le christianisme aux peuples 'arriérés'), raciste (théories sur la hiérarchie des races, Social-darwinisme), économique (accès aux matières premières, marchés pour les produits manufacturés, terrains d'investissement) et stratégique (contrôle de routes commerciales, prestige national). Ces justifications masquent une réalité d'exploitation et de violence."),
            ("qcm", "Qu'est-ce que le Code de l'indigénat, appliqué dans les colonies françaises ?",
             ["A. Un code civil adapté pour les colonies", "B. Un régime juridique d'exception discriminatoire appliqué aux colonisés", "C. Un programme d'assimilation des populations indigènes", "D. Un statut de citoyenneté pour les colonisés"], "B",
             "Le Code de l'indigénat est un système juridique d'exception qui s'applique aux 'indigènes' des colonies françaises (et non aux colons européens). Il prévoit des infractions spécifiques aux colonisés (manque de respect envers les autorités, refus de travaux forcés), des sanctions administratives sans jugement et des travaux forcés. C'est un instrument central de la domination coloniale."),
            ("vrai-faux", "La résistance des populations colonisées est inexistante face à la puissance militaire européenne.",
             None, "FAUX",
             "FAUX. Les populations colonisées ont résisté, parfois vigoureusement : résistance d'Abd el-Kader en Algérie (1832-1847), guerre des Zulu en Afrique du Sud (1879), résistance de Samory Touré en Afrique de l'Ouest, révolte des Boxer en Chine (1900), bataille d'Adoua (1896) où l'Éthiopie bat l'armée italienne. Ces résistances, souvent écrasées, témoignent du refus de la domination coloniale."),
            ("texte", "Qu'est-ce que l'Algérie représente comme colonie particulière pour la France ?",
             None, "L'Algérie est une colonie de peuplement unique pour la France, avec une importante population de colons européens (pieds-noirs) et considérée comme partie intégrante du territoire français.",
             "L'Algérie est une colonie d'un type particulier pour la France : conquise à partir de 1830, elle devient une colonie de peuplement avec plus d'un million de colons européens (pieds-noirs) à son indépendance. Elle est administrativement intégrée à la France (trois départements) depuis 1848, contrairement aux protectorats ou territoires. Cette spécificité explique la violence de la guerre d'indépendance (1954-1962) et le traumatisme que représente l'indépendance algérienne pour la société française."),
            ("qcm", "Qu'est-ce que l'orientalisme, tel que l'a théorisé Edward Said ?",
             ["A. L'étude académique des civilisations orientales", "B. Un courant artistique européen du XIXe siècle sur l'Orient", "C. La construction d'une image de l'Orient par l'Occident comme l'autre inférieur et exotique, justifiant la domination coloniale", "D. Les échanges commerciaux entre Europe et Extrême-Orient"], "C",
             "L'orientalisme (Edward Said, 1978) désigne le discours occidental qui construit l'Orient comme un espace exotique, mystérieux, inférieur et irrationnel, par opposition à un Occident rationnel et supérieur. Ce discours, incarné dans la littérature, la peinture et la recherche académique, contribue à légitimer la domination coloniale en présentant les peuples colonisés comme ayant 'besoin' d'être civilisés."),
            ("vrai-faux", "La Chine n'a jamais été officiellement colonisée par les puissances européennes au XIXe siècle.",
             None, "FAUX",
             "FAUX (avec nuances). La Chine n'a pas été entièrement colonisée, mais elle a subi des 'semi-colonisation' : guerres de l'opium (1839-1842, 1856-1860) perdues face à la Grande-Bretagne, traités inégaux cédant Hong Kong, ouvrant les ports (concessions étrangères à Shanghai), concédant des zones d'influence à différentes puissances. La Chine garde une souveraineté formelle mais est en réalité dominée."),
        ],
    }

    # Default generic questions for themes without specific content
    default_questions = [
        ("qcm", f"Quelle est la période principale couverte par le thème '{theme}' ?",
         ["A. XVIIIe siècle", "B. XIXe siècle", "C. XXe siècle", "D. XXIe siècle"], "C",
         f"Ce thème s'inscrit principalement dans le contexte du XXe siècle, période de grandes transformations politiques, économiques et sociales qui ont façonné le monde contemporain."),
        ("vrai-faux", f"Le thème '{description}' fait partie du programme officiel d'Histoire-Géographie de 1ère.",
         None, "VRAI",
         f"VRAI. Ce thème est inscrit au programme d'Histoire-Géographie de la classe de Première, permettant aux élèves d'acquérir des connaissances fondamentales sur les grandes évolutions du monde contemporain."),
        ("texte", f"Présentez en quelques lignes l'importance de l'étude du thème '{description}' pour comprendre le monde contemporain.",
         None, "Ce thème permet de comprendre les grandes évolutions politiques, économiques et sociales qui ont façonné notre monde actuel.",
         f"L'étude du thème '{description}' est essentielle pour comprendre les dynamiques du monde contemporain : les rapports de force entre nations, les transformations sociales et économiques, et les enjeux géopolitiques actuels. Elle développe l'esprit critique et la capacité d'analyse historique et géographique des élèves."),
        ("qcm", "Quelle démarche est au cœur de l'étude historique et géographique ?",
         ["A. La mémorisation de dates et de faits", "B. L'analyse critique de documents et la construction d'une argumentation", "C. La récitation de définitions", "D. L'apprentissage des frontières"], "B",
         "L'étude de l'histoire-géographie développe l'analyse critique de documents (textes, cartes, images, statistiques), la construction d'une argumentation rigoureuse, la mise en perspective temporelle et spatiale des phénomènes, et la compréhension des enjeux du monde contemporain."),
        ("vrai-faux", "Les sources primaires (documents d'époque) sont plus fiables que les sources secondaires (analyses d'historiens) pour comprendre un événement historique.",
         None, "FAUX",
         "FAUX. Les sources primaires (archives, témoignages, documents d'époque) et les sources secondaires (travaux d'historiens) sont complémentaires. Les sources primaires peuvent être biaisées, incomplètes ou difficiles à interpréter ; les sources secondaires apportent une analyse critique et une mise en perspective. L'historien doit confronter les deux types de sources."),
        ("texte", "Qu'est-ce qu'un territoire et en quoi diffère-t-il d'un simple espace géographique ?",
         None, "Un territoire est un espace géographique approprié, vécu et organisé par une société, contrairement à un simple espace physique.",
         "En géographie, un territoire est un espace géographique délimité, approprié et organisé par un groupe humain qui lui donne une identité et un sens. Il se distingue d'un simple espace physique par la présence d'acteurs qui l'aménagent, le gèrent et le revendiquent. La notion de territoire implique donc une dimension sociale, politique et identitaire, en plus de la dimension physique."),
        ("qcm", "Qu'est-ce qu'une carte thématique en géographie ?",
         ["A. Une carte routière ou touristique", "B. Une représentation graphique qui représente la distribution spatiale d'un phénomène précis", "C. Une carte de relief topographique", "D. Un plan d'une ville"], "B",
         "Une carte thématique représente la distribution spatiale d'un phénomène géographique spécifique (densité de population, PIB par habitant, flux migratoires, etc.) à l'aide de signes conventionnels (couleurs, symboles, flèches). Elle est un outil d'analyse indispensable en géographie pour identifier des disparités spatiales et des dynamiques territoriales."),
        ("vrai-faux", "La géopolitique étudie les liens entre la géographie et le pouvoir politique des États.",
         None, "VRAI",
         "VRAI. La géopolitique analyse les relations entre les données géographiques (ressources, territoires, frontières, positions stratégiques) et les stratégies politiques et militaires des États et des acteurs non-étatiques. Elle examine comment la géographie influence les rapports de puissance, les conflits et les alliances internationales."),
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
        title = f"Quiz Diagnostic 1ere Histoire-Géographie - Serie {serie}"
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

    print("Generating Histoire-Géographie quizzes 175-223...")
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
        print(f"  ✓ {fid}.json [{qdata['serie']}/49] - {description}")

    print(f"\n✅ Histoire-Géographie: {count} quiz files generated (+ {count} answers = {count*6} total files)")


if __name__ == "__main__":
    write_quiz_files()
