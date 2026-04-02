# DOCUMENTATION DU PROJET - MonCoachScolaire

Ce document retrace l'historique et les décisions techniques prises tout au long du développement du projet MonCoachScolaire.

---

## Date : 11 Novembre 2025

### Phase 1 : Initialisation et Structuration du Projet

1. **Changement de Nom :**
    * Le projet, initialement nommé "Collège+", a été renommé **"MonCoachScolaire"** pour mieux refléter son ambition d'accompagnement global.
    * Le fichier `index.php` et les titres principaux ont été mis à jour en conséquence.

2. **Architecture du Site :**
    * Le site a été structuré en trois sections distinctes pour couvrir l'ensemble du parcours secondaire :
        * **Collège+** : Du CM2 à la 3ème.
        * **Lycée+** : De la Seconde à la Terminale.
        * **Sur le chemin du BAC** : Préparation intensive à l'examen.

3. **Développement de la Section "Collège+" :**
    * Création d'une page d'accueil dédiée à la section : `college/index.php`.
    * Cette page a été organisée en "cartes" pour chaque cycle du collège :
        * CM2 - 6ème (Transition)
        * 5ème - 4ème (Consolidation)
        * 3ème (Préparation au Brevet)

---

### Phase 2 : Création des Guides de Remédiation Pédagogique

1. **Objectif :**
    * Développer des guides de remédiation complets pour chaque niveau du collège, basés sur les **programmes officiels de 2025**.
    * Ces guides sont destinés à accompagner les élèves en difficulté en leur proposant des contenus structurés, des exercices corrigés et des conseils méthodologiques.

2. **Création des Fichiers Guides :**
    * Pour chaque niveau, un guide détaillé a été créé sous forme de fichier HTML statique pour une consultation facile et une mise en page soignée (style document officiel).
        * `college/6eme/guide-remediation.php`
        * `college/5eme/guide-remediation.php`
        * `college/4eme/guide-remediation.php`
        * `college/3eme/guide-remediation.php`

3. **Intégration et Mise à Jour des Pages PHP :**
    * Les anciens fichiers `guide-remediation.php` de chaque niveau ont été supprimés et remplacés par de nouvelles versions qui intègrent le contenu riche des fichiers HTML.

4. **Contenu des Guides :**
    * **Structure :** Chaque guide est organisé par matière (Français, Mathématiques, Sciences et Technologie).
    * **Pédagogie :** Intégration de sections spécifiques :
        * Vue d'ensemble du programme 2025.
        * Exercices pratiques avec niveaux de difficulté et corrections détaillées.
        * Conseils de remédiation et stratégies d'apprentissage.
        * Outils pour la différenciation pédagogique.
        * Grilles d'évaluation pour suivre les progrès.
    * **Technologie :** Utilisation du micro-framework CSS **Pico.css** pour un design sobre, lisible et responsive, adapté à un format "document".

5. **Navigation :**
    * La page `college/index.php` a été mise à jour pour lier correctement chaque niveau à son guide de remédiation respectif.

---

### Phase 3 : Concepts de Gamification Éducative

1. **Objectif :**
    * Intégrer des éléments de gamification pour rendre l'apprentissage plus engageant et motivant.
    * Développer des mécaniques de jeu adaptées à chaque matière et niveau scolaire.

2. **Thèmes et Fils Rouges par Niveau :**
    * Définition des thèmes narratifs pour structurer l'expérience d'apprentissage :
    
    | Niveau | Thème | Fil Rouge | Objectif Principal |
    |--------|-------|-----------|-------------------|
    | **6ème** | **L'AVENTURIER** | "Quête du Sceptre Unificateur" | Adaptation au collège, consolidation bases |
    | **5ème** | **L'EXPLORATEUR** | "Mystère des Cartes Perdues" | Approfondissement, ouverture nouvelles matières |
    | **4ème** | **L'INGÉNIEUR** | "Réparation Machine Temporelle" | Complexité, analyse, esprit critique |
    | **3ème** | **L'EXPERT** | "Tour de Préparation Brevet" | Maîtrise, synthèse, préparation examen |

3. **Types de Jeux par Matière :**
    * Proposition de mécaniques de jeu adaptées à chaque discipline :
    
    | Matière | Type de Jeu | Objectif Pédagogique | Exemple 6ème | Exemple 4ème | Exemple 3ème |
    ---

    ## Sauvegardes temporaires / capture avant modification (nouvelle pratique)

    Important — pour éviter de perdre du travail lors de modifications importantes, nous avons adopté une *pratique obligatoire* :

    - Avant toute modification risquée (structure, CSS principal, pages critiques), exécuter un snapshot des fichiers concernés.
    - Un outil pratique est fourni dans `scripts/snapshot.php` (et `scripts/snapshot.ps1` pour PowerShell) :
        - Exemple :
            - `php scripts/snapshot.php landingpage.php style.css`
                - `php scripts/snapshot.php --all` (capture de tout le projet, exclut backups/vendor/node_modules)
                - Nouveaux flags utiles pour limiter la place disque :
                    - `--compress` : compresse automatiquement le snapshot en ZIP (supprime le dossier non compressé)
                    - `--keep=N` : conserve seulement les N snapshots les plus récents (ex. `--keep=7`)
                    - `--prune-days=X` : supprime les snapshots plus vieux que X jours
                    - `--exclude=assets/img,tests` : ajouter des chemins à exclure du snapshot (séparés par des virgules)
                - Exemple : `php scripts/snapshot.php --all --compress --keep=7 --exclude=assets/img`
        - PowerShell : `.\
emove ps1\snapshot.ps1 -Paths landingpage.php,style.css` ou `.\
emove ps1\snapshot.ps1 -All` (voir script for usage)

    - Les snapshots sont stockés dans `backups/YYYYMMDD_HHMMSS/` (ou en `.zip` si compressé). Pour économiser l'espace disque, utilisez `--compress` et `--keep=N`/`--prune-days=X`.

    ## Router centralisé (index.php)

    Le site utilise maintenant `index.php` comme routeur central. Points importants :

    - Toutes les pages internes doivent être référencées via `index.php?page=...` pour garantir que le site fonctionne correctement à la fois en local et en production (sous un sous-dossier).
    - Exemples :
        - `index.php?page=college/6eme/exercices-6eme`
        - `index.php?page=register`
        - `index.php?page=parents&id=123`
    - Le routeur applique une validation simple et protège contre les traversées de répertoires; il cherche automatiquement les fichiers `*.php` (ou les index dans des dossiers) pour inclure la page correspondant à `page`.
    - Quand vous éditez des liens ou du JS qui effectue des redirections, préférez `index.php?page=...` pour rester cohérent.

    Si vous préférez migrer progressivement, le repo contient `scripts/convert_links_to_router.php` pour analyser et convertir automatiquement les href internes vers `index.php?page=...`. Ce script exclut les répertoires `backups/` et `vendor/` par défaut et propose un mode `--apply` (et un dry-run) pour appliquer les modifications.

    ### Git hooks (optionnel)

    Des exemples de hooks sont fournis dans `scripts/git-hooks/` :
    - `pre-commit.sample` — capture des fichiers mis en staging avant commit.
    - `pre-push.sample` — capture complète du projet avant push.

    Pour activer un hook, copiez le fichier correspondant dans `.git/hooks/` (retirez l'extension `.sample`) et rendez-le exécutable.

    ### Pourquoi cette règle ?

    Tu m'as demandé d'« ancrer » cette exigence — la manière la plus fiable est de l'intégrer au dépôt sous forme de scripts et d'une documentation claire. Comme assistant je peux suivre et recommander cette procédure à chaque modification que je propose, et je peux automatiser la snapshot avant d'appliquer des patches si tu le souhaites.

    |---------|-------------|---------------------|--------------|--------------|--------------|
    | **Mathématiques** | Obby (parcours) | Maîtriser une notion en résolvant des problèmes | Sauter sur plateformes "Nombre Décimal" | Résoudre équation pour faire apparaître un pont | Appliquer Pythagore pour calculer un saut |
    | **Français** | Jeu de Rôle/Donjon | Utiliser les règles comme "armes" | Conjuguer au présent pour vaincre un monstre | Trouver nature/fonction des mots | Répondre sur figures de style pour persuader |
    | **Histoire-Géo** | Aventure/Point & Click | Explorer environnements historiques | Retrouver objets anachroniques à Versailles | - | Analyser carte WWI pour positionner tranchées |
    | **SVT** | Simulation (Tycoon) | Gérer écosystème/expérience | "Cellule Builder" : assembler organelles | - | "EcoManager" : équilibrer écosystème |
    | **Physique-Chimie** | Puzzle Physique | Appliquer lois physiques | Brancher circuit pour allumer lampe | - | Calculer force pour construire catapulte |
    | **Langues** | Jeu de Société | Communiquer pour tâche | Acheter item avec phrases basiques | - | "Among Us" pédagogique avec alibi en LV |
    | **Arts** | Création/Sandbox | Créer et collectionner | "Pixel Art" : recréer tableau célèbre | Collectionner cartes artistes | - |

4. **Environnements de Jeu par Matière et Niveau :**
    * Définition des univers immersifs pour chaque combinaison matière-niveau :
    
    | Matière | 6ème (Aventurier) | 5ème (Explorateur) | 4ème (Ingénieur) | 3ème (Expert) |
    |---------|-------------------|-------------------|------------------|---------------|
    | **Français** | Donjon Grammaire - Forêt des Contes | Château des Temps - Procès des Mots | Bibliothèque Lumières - Enquête Grammaticale | Épreuve Ultime - Analyse Textes |
    | **Maths** | Îles des Nombres - Temple Géométrie | Salles Calcul Littéral - Labyrinthe Angles | Théorème Pythagore - Forêt des Fonctions | Arène du Brevet - Révision intensive |
    | **Histoire** | Oracle Antiquité | Archives Moyen-Âge | Siècle Lumières - Révolution | Révision 1914→Today - Frise interactive |
    | **Sciences** | Bioparc Mystérieux (SVT) | Corps Humain (SVT) - Labo Éléments (PC) | Code de la Vie (SVT) - Centrale Énergétique (PC) | Enjeux Planétaires - Synthèse problèmes |
    | **Langues** | Maison Explorateur | Ville Anglaise - Plaza Mayor (LV2) | Agent Secret - Missions complexes | Citoyen Monde - Débats société |

5. **Exemple de Mécanique de Jeu :**
    * Illustration concrète d'une mécanique appliquée au théorème de Pythagore :
    
    | Élément | Contenu Type |
    |---------|--------------|
    | **Notion** | Théorème de Pythagore |
    | **Objectif** | Calculer longueur hypoténuse |
    | **Scénario** | Traverser rivière avec échelle |
    | **Mécanique** | Saisir longueur hypoténuse - Test physique de la longueur |
    | **Feedback 👍** | "Génial ! √169 = 13. +20 points" |
    | **Feedback 👎** | "Presque ! a² + b² = c². 5² + 12² = ?" |
    | **Récompense** | 20 Éduc'Pièces - Plan maison géométrique |

6. **Phases de Développement Gamifié :**
    * Planification temporelle pour l'implémentation progressive :
    
    | Phase | Période | Priorités | Actions Clés |
    |-------|----------|-----------|-------------|
    | **Phase 1** | Lancement | Maths 4ème/3ème - Français 3ème - Histoire-Géo 3ème | Contenu Brevet - Exercices types - QCM interactifs |
    | **Phase 2** | Consolidation | Sciences 5ème/4ème - Langues tous niveaux - Compléter Maths/Français | Labos virtuels - Conversations IA - Simulations |
    | **Phase 3** | Expertise | Défis collaboratifs - Arts et culture - Événements saisonniers | Mode équipe - Musée virtuel - Quêtes thématiques |

---

### Phase 4 : Implémentation des Activités Gamifiées

1. **Objectif :**
    * Intégrer les concepts de gamification directement dans les guides de remédiation pédagogiques.
    * Créer des sections dédiées aux activités ludiques dans chaque niveau du collège.

2. **Mise à Jour des Guides :**
    * Ajout d'une section "Activités Gamifiées" dans chaque guide de remédiation :
        * `college/6eme/guide-remediation.php` - Thème "L'Aventurier"
        * `college/5eme/guide-remediation.php` - Thème "L'Explorateur"
        * `college/4eme/guide-remediation.php` - Thème "L'Ingénieur"
        * `college/3eme/guide-remediation.php` - Thème "L'Expert"

3. **Contenu des Sections Gamifiées :**
    * **Thème Narratif :** Histoire immersive adaptée au niveau scolaire
    * **Activités par Matière :** Description des mécaniques de jeu spécifiques
    * **Objectifs Pédagogiques :** Liens avec les compétences à développer
    * **Environnements :** Univers immersifs pour chaque matière et niveau

4. **Technologies Utilisées :**
    * Intégration dans les fichiers HTML existants avec le framework Pico.css
    * Utilisation d'emojis et de mise en page structurée pour l'engagement visuel
    * Liens avec les programmes officiels 2025

---

### Phase 5 : Création des Pages d'Exercices Interactives

1. **Objectif :**
    * Développer des pages d'exercices interactives pour chaque niveau du collège.
    * Respecter le référentiel pédagogique tout en adoptant une approche "coach scolaire" personnalisée.
    * Intégrer les concepts de gamification dans les exercices.

2. **Approche Coach Scolaire :**
    * **Accompagnement personnalisé :** Messages motivants et conseils méthodologiques adaptés.
    * **Suivi des progrès :** Barres de progression et feedback immédiat.
    * **Motivation :** Thèmes narratifs inspirés des concepts gamifiés (Aventurier, Explorateur, Ingénieur, Expert).
    * **Différenciation :** Exercices gradués par difficulté (Facile, Moyen, Difficile).
    * **Référentiel respecté :** Contenus conformes aux programmes 2025.

3. **Création des Pages :**
    * `college/6eme/exercices-6eme.php` - Thème "L'Aventurier" (adaptation collège, bases)
    * `college/5eme/exercices-5eme.php` - Thème "L'Explorateur" (approfondissement, nouvelles matières)
    * `college/4eme/exercices-4eme.php` - Thème "L'Ingénieur" (complexité, analyse, esprit critique)
    * `college/3eme/exercices-3eme.php` - Thème "L'Expert" (maîtrise, synthèse, préparation Brevet)

4. **Contenu Pédagogique :**
    * **Français :** Grammaire, conjugaison, analyse textuelle, réécriture.
    * **Mathématiques :** Calculs, géométrie, problèmes complexes, statistiques.
    * **Sciences :** SVT, Physique-Chimie, expérimentations, enjeux environnementaux.
    * **Histoire-Géo :** Analyse historique, géographie contemporaine, cartes et documents.
    * **Exercices types Brevet** pour la 3ème.

5. **Technologies Utilisées :**
    * Framework Pico.css pour le design responsive.
    * JavaScript pour l'interactivité (corrections, progression).
    * Structure PHP pour l'intégration future avec base de données.
    * Mise en page adaptée à la consultation sur différents supports.

6. **Mise à Jour de la Navigation :**
    * Intégration des liens vers les pages d'exercices dans `college/index.php`.
    * Boutons différenciés par niveau avec icônes appropriées.
    * Accessibilité améliorée pour tous les utilisateurs.

---

### Phase 6 : Intégration des Concepts Pédagogiques Avancés

1. **Thèmes Narratifs et Fils Rouges par Niveau :**
    * Définition des univers immersifs pour structurer l'expérience d'apprentissage :
    
    | Niveau | Thème | Fil Rouge | Objectif Principal |
    |--------|-------|-----------|-------------------|
    | **6ème** | **L'AVENTURIER** | "Quête du Sceptre Unificateur" | Adaptation au collège, consolidation bases |
    | **5ème** | **L'EXPLORATEUR** | "Mystère des Cartes Perdues" | Approfondissement, ouverture nouvelles matières |
    | **4ème** | **L'INGÉNIEUR** | "Réparation Machine Temporelle" | Complexité, analyse, esprit critique |
    | **3ème** | **L'EXPERT** | "Tour de Préparation Brevet" | Maîtrise, synthèse, préparation examen |

## Base de Données Locale (SQLServer)

Pour démarrer localement avec SQLServer et SQLTools (extension), voici un script d'initialisation que tu peux exécuter dans SQLTools :

Chemin : `db/create_schema.sql`

Commandes rapides (PowerShell) :
```powershell
cd D:\Hostinger\public_html\moncoachscolaire\db
sqlcmd -S . -i create_schema.sql
```

Ce script crée les tables essentielles (`Users`, `UserProgress`, `Powers`, `UserPowers`, `Achievements`, `UserAchievements`, `Exercises`, `ExerciseResponses`) et insère quelques données de test.

Conseil : Utilise l'extension `SQLTools` pour exécuter le fichier `create_schema.sql` et vérifier la création de la base.
2. **Environnements de Jeu par Matière et Niveau :**
    * Univers immersifs adaptés à chaque combinaison matière-niveau :
    
    | Matière | 6ème (Aventurier) | 5ème (Explorateur) | 4ème (Ingénieur) | 3ème (Expert) |
    |---------|-------------------|-------------------|------------------|---------------|
    | **Français** | Donjon Grammaire<br>Forêt des Contes | Château des Temps<br>Procès des Mots | Bibliothèque Lumières<br>Enquête Grammaticale | Épreuve Ultime<br>Analyse Textes |
    | **Maths** | Îles des Nombres<br>Temple Géométrie | Salles Calcul Littéral<br>Labyrinthe Angles | Théorème Pythagore<br>Forêt des Fonctions | Arène du Brevet<br>Révision intensive |
    | **Histoire** | Oracle Antiquité | Archives Moyen-Âge | Siècle Lumières<br>Révolution | Révision 1914→Today<br>Frise interactive |
    | **Sciences** | Bioparc Mystérieux (SVT) | Corps Humain (SVT)<br>Labo Éléments (PC) | Code de la Vie (SVT)<br>Centrale Énergétique (PC) | Enjeux Planétaires<br>Synthèse problèmes |
    | **Langues** | Maison Explorateur | Ville Anglaise<br>Plaza Mayor (LV2) | Agent Secret<br>Missions complexes | Citoyen Monde<br>Débats société |

3. **Plan de Développement Gamifié :**
    * Stratégie temporelle pour l'implémentation progressive :
    
    | Phase | Période | Priorités | Actions Clés |
    |-------|----------|-----------|-------------|
    | **Phase 1** | Lancement | Maths 4ème/3ème<br>Français 3ème<br>Histoire-Géo 3ème | Contenu Brevet<br>Exercices types<br>QCM interactifs |
    | **Phase 2** | Consolidation | Sciences 5ème/4ème<br>Langues tous niveaux<br>Compléter Maths/Français | Labos virtuels<br>Conversations IA<br>Simulations |
    | **Phase 3** | Expertise | Défis collaboratifs<br>Arts et culture<br>Événements saisonniers | Mode équipe<br>Musée virtuel<br>Quêtes thématiques |

4. **Exemple de Mécanique de Jeu Pédagogique :**
    * Illustration concrète appliquée au théorème de Pythagore :
    
    | Élément | Contenu Type |
    |---------|--------------|
    | **Notion** | Théorème de Pythagore |
    | **Objectif** | Calculer longueur hypoténuse |
    | **Scénario** | Traverser rivière avec échelle |
    | **Mécanique** | Saisir longueur hypoténuse<br>Test physique de la longueur |
    | **Feedback 👍** | "Génial ! √169 = 13. +20 points" |
    | **Feedback 👎** | "Presque ! a² + b² = c². 5² + 12² = ?" |
    | **Récompense** | 20 Éduc'Pièces<br>Plan maison géométrique |

5. **Intégration dans les Pages d'Exercices :**
    * Application des thèmes narratifs dans les messages du coach
    * Utilisation des environnements immersifs pour contextualiser les exercices
    * Adaptation des mécaniques de jeu selon le niveau et la matière

---

## Phase 7 : Implémentation du Labo des Génies - Gamification Pédagogique Avancée

### Décembre 2024 - Le Labo des Génies

### 1. **Vision et Concept Fondateur**

**Le Labo des Génies** représente l'aboutissement des principes de gamification pédagogique avancés intégrés dans MonCoachScolaire. Inspiré de l'univers scientifique et expérimental, cette interface transforme l'apprentissage en une **aventure scientifique personnalisée** où chaque élève devient un chercheur dans son propre laboratoire virtuel.

**Principe central :** Au lieu de "faire des exercices", l'élève "mène des expériences". Chaque succès débloque des **outils de laboratoire concrets** qui l'aident réellement dans ses apprentissages.

### 2. **Architecture Technique de la Gamification**

#### **Laboratoire Virtuel Dynamique**

* **64 stations d'expérimentation** disposées en grille, représentant des expériences progressives
* **Token scientifique animé** qui avance automatiquement selon les réussites réelles d'exercices
* **Système de stations spéciales** : stations "outils de labo" à intervalles stratégiques (stations 8, 12, 16, 20, 24)

#### **Mécaniques de Progression Intelligente**

```javascript
// Système de progression basé sur les réussites
advanceToSquare(squareNumber) {
    this.currentPosition = squareNumber;
    this.xp += 100; // XP gagné par case avancée
    this.checkPowerUnlock(squareNumber); // Vérification déblocage pouvoir
}
```

---

## Phase 7 : Déploiement de la section Lycée

1. **Objectif :**
    * Fournir des guides de remédiation complets et des séries d'exercices pour les niveaux Lycée (Seconde, Première, Terminale).

2. **Fichiers ajoutés :**
    * `lycee/index.php` — page d'accueil de la section Lycée+
    * `lycee/seconde/guide-remediation.php`, `lycee/seconde/exercices-seconde.php`
    * `lycee/premiere/guide-remediation.php`, `lycee/premiere/exercices-premiere.php`
    * `lycee/terminale/guide-remediation.php`, `lycee/terminale/exercices-terminale.php`
    * `lycee/README.md` — description du contenu et bonnes pratiques pour contributions

3. **Tests automatisés ajoutés :**
    * `tests/test_lycee_pages.php` — vérifie que toutes les pages Lycée répondent correctement (HTTP 200/3xx)
    * Les tests CLI peuvent être exécutés localement depuis la racine du projet : `php tests/test_lycee_pages.php`

4. **Usage & maintenance :**
    * Pour ajouter ou modifier une page de remédiation, suivre le modèle existant : inclure `header.php` / `footer.php` et garantir que `tests/test_lycee_pages.php` couvre la nouvelle page.
    * Mettre à jour `lycee/README.md` et `DOCUMENTATION.md` si de nouvelles pages ou ressources sont ajoutées.

---

## Phase 8 : Interactivité, Tests et Suivi (Lycée)

1. **Interactivité QCM**
    * Les pages d'exercices Lycée incluent désormais des QCM interactifs (auto-correction client-side) via `assets/js/qcm.js`.
    * Les boutons `Vérifier` montrent un feedback instantané (bonne/mauvaise réponse) et le score.

2. **Sauvegarde progressive / API**
    * Un endpoint `api/save_progress.php` a été ajouté. Il accepte les POST JSON { exerciseId, score, correct }.
    * Si la base de données est disponible et que l'utilisateur est connecté (session), la réponse est persistée dans `ExerciseResponses`.
    * Dans les environnements sans DB ou sans session, l'API stocke les enregistrements dans `data/unsaved_progress.json` en attente de traitement.

3. **Tests E2E & CLI**
    * Playwright E2E tests ont été étendus :
      - `tests/e2e/sidebar.spec.js` (existant) vérifie le toggle de la sidebar depuis l'en-tête.
      - `tests/e2e/lycee.spec.js` vérifie la navigation et le chargement des pages Lycée.
      - `tests/e2e/qcm.spec.js` simule la correction d'un QCM et l'appel à l'API de sauvegarde.
    * Tests CLI ajoutés : `tests/test_lycee_pages.php`, `tests/test_api_save_progress.php`.

4. **Impression / PDF**
    * Les pages de guides incluent un bouton "Imprimer / Enregistrer en PDF" qui déclenche la boîte d'impression du navigateur (window.print) et un style d'impression (`@media print`) optimise la mise en page (header/sidebar masqués, contenu principal maximisé).

5. **Comment tester rapidement**
    * Playwright (Node.js) — installer, puis :

```bash
npx playwright install
npm run test:e2e
```

    * Tests CLI :

```bash
php tests/test_pages.php
php tests/test_lycee_pages.php
php tests/test_api_save_progress.php
```

---



* **Progression conditionnelle** : L'avancement dépend des succès aux exercices/quizz
* **XP cumulative** avec système de niveaux scientifiques (Apprenti Scientifique → Chercheur Junior → Expérimentateur → Docteur en Sciences)
* **Sauvegarde persistante** via localStorage pour maintenir la motivation

### 3. **Les 8 Outils de Laboratoire - Gamification Fonctionnelle**

Chaque outil débloqué représente une **capacité pédagogique réelle** qui aide l'élève dans ses apprentissages :

| Outil de Labo | Icône | Utilité Pédagogique | Station de Déblocage | Mécanique Technique |
|---------------|-------|-------------------|---------------------|-------------------|
| **Calculatrice Avancée** | 🧮 | Réduit les erreurs de calcul de 50% | Station 8 | Validation automatique avec tolérance d'erreur |
| **Encyclopédie Scientifique** | 📖 | Accès rapide aux définitions complexes | Station 12 | Pop-up contextuel avec glossaire intégré |
| **Zone de Focus** | 🔬 | Bloque les distractions pendant 30 min | Station 16 | Interface minimaliste, notifications désactivées |
| **Accélérateur d'Apprentissage** | ⚡ | Double la vitesse d'apprentissage | Station 20 | Contenu accéléré, résumé automatique |
| **Prédicteur Scientifique** | 🔮 | Prévoit les questions difficiles | Station 24 | Analyse prédictive basée sur historique |
| **Booster de Motivation** | 🌟 | Booste la motivation de +100% | Récompense Spéciale | Messages personnalisés, encouragements ciblés |
| **Correcteur Orthographique** | ✍️ | Maîtrise parfaite de l'orthographe | Récompense Spéciale | Correction orthographique en temps réel |
| **Laboratoire Expert** | 🏆 | Maîtrise ultime, accès aux secrets | Station Finale | Contenu expert, easter eggs pédagogiques |

### 4. **Principe de "Outil de Labo = Récompense Pédagogique"**

**Innovation majeure :** Contrairement aux jeux traditionnels où les récompenses sont purement cosmétiques, ici chaque "outil de laboratoire" apporte une **amélioration pédagogique réelle** :

* **Calculatrice Avancée** : Interface qui accepte les réponses approximatives (ex: 3.1416 ≈ π)
* **Encyclopédie Scientifique** : Bouton "💡" qui affiche des définitions contextuelles
* **Zone de Focus** : Mode "focus" qui cache les éléments distracteurs
* **Accélérateur d'Apprentissage** : Génération automatique de résumés et schémas mentaux

### 5. **Mécaniques Psychologiques Avancées**

#### **Principe de l'Effort Récompensé**

* Chaque station complétée = reconnaissance visible du travail accompli
* Les outils de laboratoire débloqués créent un **sentiment de progression tangible**
* **Boucle de rétroaction positive** : Effort → Réussite → Outil → Motivation accrue

#### **Gamification Non-Lineaire**

* Possibilité de **revisiter les stations passées** pour consolider les acquis
* **Multiples chemins de progression** selon les matières maîtrisées
* **Système de "récompenses surprises"** pour maintenir l'engagement

#### **Personnalisation Adaptative**

* Les pouvoirs se débloquent selon le **profil d'apprentissage** de l'élève
* **Difficulté adaptative** : plus l'élève progresse, plus les défis s'adaptent
* **Recommandations personnalisées** basées sur les points faibles identifiés

### 6. **Intégration Technique et API**

#### **Architecture Modulaire**

```html
<!-- Structure du laboratoire virtuel -->
<div class="game-board">
    <div class="board-squares" id="board-squares"></div>
    <div class="player-token" id="player-token"></div>
</div>
```

#### **Système d'Événements**

```javascript
// Écouteur d'événements pour les succès d'exercices
document.addEventListener('exerciseCompleted', (event) => {
    const { subject, difficulty } = event.detail;
    this.onExerciseCompleted(subject, difficulty);
});
```

#### **Persistance des Données**

* **localStorage** pour la progression hors-ligne
* **Préparation API** pour synchronisation serveur
* **Sauvegarde automatique** à chaque avancement

### 7. **Métriques de Gamification et KPIs Pédagogiques**

#### **Indicateurs de Motivation**

* **Temps passé** sur la plateforme (+40% attendu)
* **Fréquence de connexion** (quotidienne vs hebdomadaire)
* **Taux d'achèvement** des parcours pédagogiques

#### **Métriques Pédagogiques**

* **Progression scientifique** mesurée par les résultats aux expériences
* **Réduction du découragement** (moins d'abandons)
* **Amélioration de l'autonomie** dans l'apprentissage

#### **KPIs Techniques**

* **Temps de chargement** de l'interface (< 2 secondes)
* **Taux de conversion** inscription → première quête commencée
* **Engagement moyen** par session (> 15 minutes)

### 8. **Impact Pédagogique et Recherche-Backed**

Cette implémentation s'appuie sur les **principes de gamification validés par la recherche** :

#### **Théorie de l'Autodétermination (Deci & Ryan)**

* **Autonomie** : L'élève choisit son rythme et ses expériences
* **Compétence** : Les outils de laboratoire débloqués prouvent la progression
* **Relation sociale** : Partage des réussites avec les parents

#### **Flow Theory (Csikszentmihalyi)**

* **Défis équilibrés** : Difficulté qui correspond au niveau de l'élève
* **Feedback immédiat** : Récompenses visuelles instantanées
* **Objectifs clairs** : Prochaine station = prochain objectif

#### **Achievement Theory (Atkinson)**

* **Récompenses symboliques** : Outils de laboratoire comme marqueurs de succès
* **Progression visible** : Avancement physique dans le laboratoire
* **Reconnaissance sociale** : Badges partageables

### 9. **Évolutions Futures et Scalabilité**

#### **Extensions Prévues**

* **Mode multijoueur** : Quêtes collaboratives entre élèves
* **Tournois pédagogiques** : Compétitions amicales par niveau
* **Personnalisation avancée** : Avatars et thèmes personnalisables

#### **Intégration IA**

* **Recommandations intelligentes** : Prochain pouvoir basé sur les besoins
* **Analyse prédictive** : Anticipation des difficultés
* **Coaching adaptatif** : Messages personnalisés du coach virtuel

### 10. **Conclusion : Une Gamification Authentiquement Pédagogique**

Le Labo des Génies ne se contente pas d'ajouter des éléments ludiques à l'apprentissage - il **redéfinit l'expérience éducative** en créant une **narrative scientifique cohérente** où chaque élève est un chercheur dans son propre laboratoire virtuel.

**Le vrai génie de cette approche :** Les "récompenses" ne sont pas des skins cosmétiques, mais des **outils pédagogiques concrets** qui améliorent réellement les capacités d'apprentissage de l'élève. C'est la gamification au service de l'éducation, pas l'inverse.

**Résultat attendu :** Une plateforme où les élèves ne "font plus leurs devoirs" - ils **mènent des expériences scientifiques** pour devenir des génies du savoir ! 🔬⚗️📊

---

## Intégration UI : Sidebar dynamique et animations

### But
Rendre la barre de navigation latérale (sidebar) disponible sur toutes les pages, personnalisable par page et visuellement engageante grâce à un dégradé animé et des micro-animations sur les liens.

### Détails techniques
- La sidebar est définie dans `header.php` et incluse partout via `include 'header.php'` (ou chemin relatif depuis les sous-dossiers).
- Chaque page peut définir `$sidebar_items` comme tableau avant `include 'header.php'` pour personnaliser son contenu.

- Si une page ne doit pas afficher la sidebar (ex. page login publique, landing sans navigation), vous pouvez définir
    `$disable_sidebar = true;` avant `include 'header.php'`. Cela enlèvera la sidebar et empêchera la page d'être décalée via `margin-left`.

### Activer temporairement le mode debug/bannières pour QA

Parfois vous devez tester les flux côté utilisateur (ex. navigation, erreurs quand la DB est indisponible). Attention : la bannière "La base de données est indisponible" est désormais affichée automatiquement seulement lorsque `APP_DEBUG=true`. Sur les environnements de production elle ne s'affichera pas automatiquement — utilisez le mécanisme ci‑dessous pour forcer son affichage lors d'un test QA.

- Définissez une clé secrète dans `.env` : `DEBUG_SECRET=VotreSecretLongEtSûr`
- Ouvrez l'URL suivante (ex. sur la machine de test) : `https://votre-site/moncoachscolaire/?force_debug=<VotreSecretLongEtSûr>`
- Ce paramètre place un cookie/session pour activer temporairement la bannière pour votre navigateur. Pour la retirer : `?force_debug=clear`.

Si vous êtes en `APP_ENV=local` ou que `APP_DEBUG=true`, utiliser `?force_debug=1` est suffisant.
- L'item actif est automatiquement calculé en comparant l'URL courante à chaque `href` du tableau.
- Les animations incluent un dégradé animé et des effets de survol (translation, ombre, effet de glow sur l'icône).

### Exemple
Dans `college/index.php` :
```php
$sidebar_items = [
    ['href' => '../index.php', 'icon'=>'🏠', 'label'=>'Accueil'],
    ['href' => 'index.php', 'icon'=>'📚', 'label'=>'Collège+'],
    ...
];
$page_title = 'Collège+';
include '../header.php';
```

### Effets attendus
- Interface cohérente sur toutes les pages
- Possibilité d'adapter la sidebar pour des pages thématiques (par exemple, une page `Cours` peut montrer des chapitres comme liens)
- Amélioration de la lisibilité et du confort utilisateur grâce aux animations

---

## Tests E2E (Playwright) — Guide rapide

J'ai ajouté des tests Playwright pour vérifier le comportement du toggle de la sidebar via le header (fichier : `tests/e2e/sidebar.spec.js`).

Notes importantes :
- Les tests supposent que le site est servi à l'URL `http://localhost/moncoachscolaire` (modifiez `playwright.config.js` si nécessaire).
- Installation recommandée :

```bash
npm install
npx playwright install
```

Exécution :

```bash
npm run test:e2e
```

Le test simule : ouverture de la page → vérifie l'état ARIA initial → clique hamburger → vérifie `aria-hidden` et la classe `sidebar-hidden` sur `body` → reclique pour rétablir l'état.

---

## Phase 9 : Implémentation Complète du Workflow de Progression

### Date : Décembre 2024 - Système de Progression Gamifiée Fonctionnel

#### 1. **Objectif et Vision**

Création d'un système complet de sauvegarde et visualisation de la progression utilisateur, avec une architecture robuste supportant :
- Sauvegarde universelle (local/production)
- Fallback automatique vers localStorage en cas d'indisponibilité BDD
- Synchronisation automatique localStorage → BDD
- Visualisation gamifiée de la progression dans le laboratoire virtuel "Le Labo des Génies"

#### 2. **Architecture Technique**

##### **2.1 API de Sauvegarde Universelle (`api/save-progress.php`)**

Fichier central pour la persistance de la progression avec gestion intelligente des erreurs :

**Fonctionnalités :**
- **Authentification** : Vérification de session utilisateur
- **Validation** : Contrôle des données POST JSON (exerciseId, score, correct, xp, cristaux, subject)
- **Détection BDD** : Fallback automatique si `$pdo` indisponible
- **Double stratégie** : Utilise `completeExercise()` si disponible, sinon sauvegarde manuelle
- **Gestion doublons** : Vérifie et met à jour les exercices déjà complétés

**Format de requête :**
```json
{
  "exerciseId": 123,
  "score": 85,
  "correct": true,
  "xp": 15,
  "cristaux": 2,
  "subject": "Mathématiques"
}
```

**Réponses possibles :**
- `200 OK` : Progression sauvegardée avec succès
- `400 Bad Request` : ID exercice invalide ou données manquantes
- `401 Unauthorized` : Utilisateur non authentifié
- `503 Service Unavailable` : BDD indisponible (fallback localStorage)

##### **2.2 Helpers de Calcul (`includes/progress_helpers.php`)**

Fonctions utilitaires pour les calculs de progression :

**Fonctions principales :**
- `calculateUserLevel($xp)` : Calcule le niveau (1-10) basé sur l'XP
- `getLevelName($level)` : Retourne le nom du niveau (Aventurier, Explorateur, etc.)
- `calculateGamePosition($xp)` : Calcule la position sur le plateau (1-64 cases)
- `getXPForNextLevel($currentLevel)` : XP nécessaire pour le niveau suivant
- `getProgressPercentage($currentXP, $currentLevel)` : Pourcentage vers prochain niveau
- `getUnlockedPowersCount($userId)` : Nombre de pouvoirs débloqués
- `getTotalPowersCount()` : Total de pouvoirs disponibles

**Tableaux de progression :**
- Niveaux XP : [0, 100, 300, 600, 1000, 1500, 2100, 2800, 3600, 4500]
- Noms : Aventurier → Explorateur → Ingénieur → Expert → Maître → Grand Maître → Légende → Mythe → Épopée → Immortel

##### **2.3 JavaScript Interactif avec Fallback (`assets/js/interactive-exercises.js`)**

Intégration complète de la sauvegarde dans tous les types d'exercices :

**Fonctions ajoutées :**
- `saveProgressToServer(exerciseData)` : Sauvegarde avec fallback automatique
- `saveProgressToLocalStorage(data)` : Stockage local en cas d'erreur
- `syncLocalStorageToServer()` : Synchronisation automatique
- `calculateRewards(score, difficulty)` : Calcul XP/cristaux selon difficulté

**Mécanisme de fallback :**
1. Tentative de sauvegarde via API
2. Si erreur réseau ou BDD indisponible → localStorage
3. Marque les sauvegardes comme "pending"
4. Synchronisation automatique toutes les 5 minutes
5. Synchronisation au chargement de page (2 secondes de délai)

**Types d'exercices supportés :**
- QCM (Questionnaire à Choix Multiples)
- Exercices mathématiques
- Conjugaison/Réécriture
- Coloriage de mots (classes grammaticales)
- Classement chronologique

##### **2.4 Page de Progression Complétée (`progression.php`)**

Visualisation gamifiée complète de la progression utilisateur :

**Données chargées depuis BDD :**
- XP total et niveau calculé
- Position sur le plateau (1-64)
- Cristaux collectés (total et par matière)
- Exercices complétés (total et par matière)
- Badges débloqués
- Pouvoirs débloqués (X/8)

**Composants visuels :**
- **Header** : Titre "Le Labo des Génies" avec sous-titre
- **Statut joueur** : 4 cartes affichant niveau, position, XP, pouvoirs
- **Barre de progression** : Pourcentage vers le prochain niveau
- **Statistiques détaillées** : Via `renderDetailedProgress()` de `progress_display.php`
- **Plateau de jeu** : Grille 8x8 (64 cases) avec token animé

**JavaScript intégré :**
- Génération automatique des 64 cases
- Positionnement du token selon la progression
- Cases visitées/actuelles mises en évidence
- Animation d'apparition du token
- Écouteur d'événements `exerciseCompleted` pour mise à jour en temps réel

##### **2.5 Styles CSS (`assets/css/pages/progression.css`)**

Design gamifié avec dégradés et animations :

**Caractéristiques :**
- Dégradé violet/bleu pour le conteneur principal
- Cartes de statut avec effet glassmorphism (backdrop-filter)
- Barre de progression XP avec dégradé doré
- Plateau de jeu circulaire avec grille 8x8
- Token du joueur animé avec pulsation
- Cases visitées en vert, case actuelle en or
- Responsive mobile avec adaptation des tailles

##### **2.6 Améliorations Composants (`includes/exercice_card.php`)**

Ajout d'attributs data nécessaires pour la sauvegarde :

**Attributs ajoutés :**
- `data-exercise-id` : ID unique de l'exercice
- `data-difficulty` : Niveau de difficulté (facile/moyen/difficile)
- `data-subject` : Matière (Mathématiques, Français, etc.)

**Détection automatique :**
- Analyse du titre pour déterminer la difficulté
- Utilise les métadonnées de l'exercice (Subject, Level)

#### 3. **Flux de Données Complet**

```
[Élève complète exercice]
    ↓
[JavaScript calcule score/XP/cristaux]
    ↓
[Appel API save-progress.php]
    ↓
    ├─→ [BDD disponible] → [Sauvegarde en base] → [Retour succès]
    │
    └─→ [BDD indisponible] → [localStorage] → [Marqué "pending"]
        ↓
    [Synchronisation auto toutes les 5 min]
        ↓
    [BDD redevenue disponible] → [Synchronisation réussie]
```

#### 4. **Système de Récompenses**

**Calcul selon difficulté :**
- **Facile** : 10 XP, 1 cristal
- **Moyen** : 15 XP, 2 cristaux
- **Difficile** : 25 XP, 3 cristaux

**Conditions :**
- Récompenses uniquement si score ≥ 50%
- XP et cristaux ajoutés à la progression totale
- Badges automatiques selon le nombre d'exercices complétés

#### 5. **Gestion des Erreurs et Robustesse**

**Stratégies mises en place :**
1. **Détection BDD** : Vérification de `$pdo` avant toute opération
2. **Fallback transparent** : localStorage sans interruption pour l'utilisateur
3. **Synchronisation intelligente** : Tentative automatique lors de la reconnexion
4. **Gestion doublons** : Vérification avant insertion dans ExerciseResponses
5. **Validation données** : Contrôle strict des types et valeurs

#### 6. **Intégration avec Système Existant**

**Fichiers modifiés/enrichis :**
- `api/save-progress.php` : Nouvelle API universelle
- `includes/progress_helpers.php` : Nouveaux helpers
- `includes/gamification.php` : Utilisé pour `completeExercise()` si disponible
- `includes/progress_display.php` : Utilisé pour `renderDetailedProgress()`
- `assets/js/interactive-exercises.js` : Intégration sauvegarde automatique
- `progression.php` : Chargement BDD + visualisation complète
- `includes/exercice_card.php` : Ajout attributs data nécessaires

**Compatibilité :**
- Fonctionne avec ou sans BDD (fallback localStorage)
- Compatible avec le système de gamification existant
- Utilise les fonctions de `gamification.php` si disponibles
- Sauvegarde manuelle si `completeExercise()` non disponible

#### 7. **Tests et Validation**

**Points de vérification :**
- Syntaxe PHP validée (`php -l`)
- Pas d'erreurs de linting détectées
- Structure JSON valide pour localStorage
- Gestion des cas d'erreur réseau
- Gestion des cas BDD indisponible
- Synchronisation localStorage → BDD

#### 8. **Évolutions Futures Possibles**

**Améliorations envisageables :**
- Notifications push lors de la synchronisation réussie
- Graphiques de progression temporelle
- Comparaison avec la moyenne de la classe
- Défis hebdomadaires avec récompenses bonus
- Mode hors-ligne complet avec Service Worker
- Export PDF de la progression

#### 9. **Conclusion**

Cette implémentation complète transforme la progression de simple compteur en **expérience gamifiée engageante** où chaque exercice complété contribue visuellement à l'avancement dans le laboratoire virtuel "Le Labo des Génies". Le système robuste avec fallback localStorage garantit que **aucune progression n'est perdue**, même en cas de problème réseau ou serveur.

**Impact attendu :**
- Motivation accrue des élèves grâce à la visualisation de la progression
- Persistance fiable des données même hors-ligne
- Expérience utilisateur fluide sans interruption

---

## Phase 10 : Page de Démonstration et Accès Sécurisé au Compte Démo

### Date : Décembre 2024 - Système de Démonstration pour Utilisateurs Sceptiques

#### 1. **Objectif et Vision**

Création d'une page de démonstration complète permettant aux visiteurs de tester les fonctionnalités principales de MonCoachScolaire **avant de créer un compte**, dans le but de :
- Rassurer les utilisateurs sceptiques ou curieux
- Montrer la valeur ajoutée de la plateforme
- Augmenter le taux de conversion inscription
- Offrir une expérience immersive sans engagement

#### 2. **Architecture Technique**

##### **2.1 Page de Démonstration (`pages/demo.php`)**

Page complète et interactive présentant toutes les fonctionnalités principales :

**Sections implémentées :**
- **Exercices Interactifs** : 2-3 exercices réels depuis la base de données (QCM, Math, Conjugaison)
- **Cours Complet** : Aperçu d'un cours avec navigation fonctionnelle
- **Quiz Dynamique** : Quiz généré dynamiquement basé sur les exercices existants
- **Aperçu Le Labo des Génies** : Visualisation de la progression gamifiée avec données pré-remplies

**Fonctionnalités :**
- Chargement automatique d'exercices depuis la BDD pour le niveau "6ème" (par défaut)
- Génération dynamique de quiz via `includes/quiz_generator.php`
- Affichage de données de progression pré-remplies pour Le Labo des Génies
- Système de limitation intelligent (5 exercices maximum)
- Tracking des actions utilisateur pour déclencher les CTA

##### **2.2 Système d'Accès Sécurisé au Compte Démo**

**Mécanisme de connexion automatique :**

1. **Point d'entrée principal** : `demo_login.php`
   - Recherche l'utilisateur "demo@example.com" dans la base de données
   - Crée automatiquement le compte demo s'il n'existe pas (sauf en mode read-only)
   - Définit les variables de session : `$_SESSION['is_demo'] = true`
   - Redirige vers la page demandée

2. **Accès via URL** : `index.php?page=demo&demo=1`
   - Vérifie le paramètre `demo=1` dans l'URL
   - Force le mode démo même sans connexion préalable
   - Crée une session visiteur si nécessaire (`user_id = 0`)

3. **Protection et sécurité :**
   - Le compte demo utilise un hash spécial (`demo-hash`) non exploitable
   - Le mot de passe "demo" est accepté uniquement pour ce compte spécifique
   - La session est marquée avec `$_SESSION['is_demo'] = true` pour différenciation
   - Pas de privilèges administrateur ou accès aux données sensibles

**Fichiers impliqués :**
- `demo_login.php` : Script de connexion automatique au compte demo
- `login.php` : Support du mode démo dans le système d'authentification standard
- `pages/demo.php` : Page de démonstration avec vérification du mode démo

##### **2.3 Points d'Entrée Multiples**

**Stratégie de découverte :**

1. **Landing Page** (`landingpage.php`)
   - Section dédiée "🎮 Essayez MonCoachScolaire gratuitement"
   - Bouton CTA visible "✨ Essayer la démo"
   - Positionné stratégiquement après le header, avant la section "Pourquoi choisir"

2. **Topbar** (`topbar.php`)
   - Lien "🎮 Mode Démo" visible uniquement pour les visiteurs non connectés
   - Style distinctif avec dégradé violet/rose (`btn-demo`)
   - Positionné avant les boutons "Se connecter" et "Créer un compte"

3. **Accès direct** : URL `index.php?page=demo&demo=1`
   - Permet le partage direct de la démo
   - Redirection automatique vers `demo_login.php` si non authentifié

##### **2.4 Système de Limitation et CTA**

**Mécanisme de limitation :**

- **Maximum 5 exercices** : Après 5 exercices complétés, affichage d'un message de limitation
- **Popup après 3 actions** : Incitation à l'inscription après 3 exercices complétés
- **Tracking des actions** : Via `api/track-demo-action.php` et localStorage
- **Messages contextuels** : CTA adaptés selon le nombre d'actions

**Composants CTA :**

1. **Bannière fixe en haut** : Visible sur toute la page de démo
   - Message : "Mode Démonstration - Créez un compte gratuit pour sauvegarder votre progression"
   - Bouton "✨ Créer mon compte" avec lien vers `register`
   - Bouton de fermeture (×) pour masquer temporairement

2. **Popup modal** : Apparaît après 3 exercices complétés
   - Titre : "🎉 Excellent travail !"
   - Liste des avantages de créer un compte
   - Boutons : "✨ Créer mon compte" (primary) et "Continuer la démo" (secondary)
   - Fermeture automatique après 10 secondes si aucune action

3. **Messages de limitation** : Après 5 exercices
   - Message d'avertissement avec fond jaune
   - Explication : "Vous avez atteint la limite de la démo"
   - CTA : "✨ Créer mon compte gratuit"

4. **CTAs contextuels** : Dans chaque section
   - Section Cours : "💡 Créez un compte pour accéder à tous les cours"
   - Section Quiz : "🔒 Créez un compte pour répondre au quiz complet"
   - Section Labo : "🚀 Créez un compte pour commencer votre propre aventure dans Le Labo des Génies"

##### **2.5 API de Tracking (`api/track-demo-action.php`)**

Endpoint REST pour suivre les actions en mode démo :

**Fonctionnalités :**
- Accepte les requêtes POST avec JSON
- Sauvegarde le nombre d'actions dans `$_SESSION['demo_action_count']`
- Format de requête : `{ "action": "exercise_completed", "count": 3 }`
- Réponse : `{ "success": true, "action_count": 3 }`

**Intégration JavaScript :**
- Écouteur d'événements `exerciseCompleted` sur le document
- Appel automatique à l'API après chaque exercice complété
- Fallback localStorage si l'API est indisponible
- Synchronisation automatique au chargement de page

##### **2.6 Styles et Design (`assets/css/pages/demo.css`)**

Design moderne et engageant avec :

**Caractéristiques visuelles :**
- Dégradés colorés (violet/rose pour les CTA, bleu clair pour les sections)
- Cartes avec ombres et bordures arrondies
- Animations fluides (fadeIn, slideUp pour les popups)
- Responsive design adapté mobile/tablette/desktop

**Composants stylisés :**
- Bannière CTA avec dégradé violet/rose
- Cartes de statistiques avec icônes emoji
- Sections avec fond blanc et ombres subtiles
- Popup modal avec backdrop blur
- Boutons avec effets hover et transitions

#### 3. **Workflow Complet Utilisateur**

```
[Visiteur arrive sur landing page]
    ↓
[Clique sur "Essayer la démo" OU "Mode Démo" dans topbar]
    ↓
[Redirection vers demo_login.php]
    ↓
[Connexion automatique au compte demo]
    ↓
[Redirection vers pages/demo.php]
    ↓
[Affichage de la page de démonstration]
    ├─→ Section Exercices (2-3 exercices interactifs)
    ├─→ Section Cours (aperçu avec navigation)
    ├─→ Section Quiz (5 questions dynamiques)
    └─→ Aperçu Le Labo des Génies (données pré-remplies)
    ↓
[Utilisateur complète un exercice]
    ↓
[Événement "exerciseCompleted" déclenché]
    ↓
[Tracking : action_count++]
    ↓
    ├─→ [Si action_count === 3] → [Popup CTA affiché]
    └─→ [Si action_count >= 5] → [Message de limitation affiché]
    ↓
[Utilisateur clique sur "Créer mon compte"]
    ↓
[Redirection vers register.php]
    ↓
[Création du compte réel]
    ↓
[Redirection vers dashboard avec progression sauvegardée]
```

#### 4. **Sécurité et Bonnes Pratiques**

**Mesures de sécurité implémentées :**

1. **Isolation du compte demo** :
   - Hash spécial `demo-hash` non exploitable pour authentification réelle
   - Session marquée `is_demo = true` pour différenciation
   - Pas d'accès aux données sensibles ou fonctionnalités admin

2. **Protection contre l'abus** :
   - Limitation à 5 exercices pour éviter l'utilisation intensive
   - Tracking des actions pour détecter les patterns suspects
   - Messages CTA réguliers pour inciter à l'inscription

3. **Gestion des données** :
   - Les données du compte demo peuvent être réinitialisées périodiquement
   - Pas de sauvegarde persistante des résultats en mode démo (optionnel)
   - Fallback localStorage pour éviter la perte de progression temporaire

4. **Validation des entrées** :
   - Vérification du paramètre `demo=1` dans l'URL
   - Validation des données POST dans l'API de tracking
   - Protection CSRF pour les actions sensibles

#### 5. **Intégration avec le Système Existant**

**Fichiers créés/modifiés :**

**Nouveaux fichiers :**
- `pages/demo.php` : Page de démonstration complète
- `assets/css/pages/demo.css` : Styles dédiés à la démo
- `api/track-demo-action.php` : API de tracking des actions

**Fichiers modifiés :**
- `landingpage.php` : Ajout de la section CTA "Essayer la démo"
- `topbar.php` : Ajout du lien "Mode Démo" pour non-connectés
- `assets/css/style.css` : Style pour le bouton `.btn-demo`
- `demo_login.php` : Amélioration de la gestion du compte demo (déjà existant)
- `login.php` : Support du mode démo dans l'authentification standard

**Compatibilité :**
- Fonctionne avec ou sans base de données (fallback session visiteur)
- Compatible avec le système de gamification existant
- Utilise les composants d'exercices interactifs existants
- Intègre le système de quiz dynamique

#### 6. **Métriques et KPIs**

**Indicateurs de succès attendus :**

1. **Taux de conversion** :
   - Visiteurs landing → Clic sur "Essayer la démo" : Objectif > 20%
   - Utilisateurs démo → Inscription : Objectif > 15%
   - Temps moyen sur page démo : Objectif > 5 minutes

2. **Engagement** :
   - Nombre moyen d'exercices complétés en démo : Objectif > 2
   - Taux de complétion du quiz : Objectif > 60%
   - Taux d'interaction avec l'aperçu Le Labo des Génies : Objectif > 40%

3. **Qualité de l'expérience** :
   - Temps de chargement page démo : Objectif < 2 secondes
   - Taux d'erreurs JavaScript : Objectif < 1%
   - Satisfaction utilisateur (via feedback) : Objectif > 4/5

#### 7. **Évolutions Futures Possibles**

**Améliorations envisageables :**

1. **Personnalisation** :
   - Choix du niveau pour la démo (6ème, 5ème, 4ème, 3ème)
   - Sélection de la matière préférée pour les exercices
   - Aperçu adapté selon le niveau choisi

2. **Gamification renforcée** :
   - Badge "Explorateur" pour les utilisateurs ayant testé la démo
   - Récompense bonus lors de l'inscription après démo
   - Comparaison avec les autres utilisateurs démo

3. **Analytics avancés** :
   - Tracking détaillé des interactions (heatmaps)
   - A/B testing des messages CTA
   - Analyse du parcours utilisateur dans la démo

4. **Fonctionnalités supplémentaires** :
   - Mode démo étendu avec plus d'exercices (version premium)
   - Partage social de la progression démo
   - Email de rappel pour les utilisateurs ayant commencé la démo

#### 8. **Conclusion**

La page de démonstration représente un **outil marketing et pédagogique puissant** qui permet aux visiteurs de découvrir MonCoachScolaire de manière immersive avant de s'engager. Le système d'accès sécurisé au compte demo garantit une expérience fluide tout en protégeant l'intégrité de la plateforme.

**Impact attendu :**
- Réduction de la friction à l'inscription grâce à la démonstration concrète
- Augmentation du taux de conversion visiteurs → utilisateurs inscrits
- Confiance accrue des utilisateurs grâce à la transparence (essai avant achat)
- Réduction du taux d'abandon grâce à la compréhension préalable de la valeur

**Innovation clé :** Contrairement aux démos traditionnelles limitées et statiques, cette implémentation offre une **expérience complète et interactive** où l'utilisateur peut réellement tester les fonctionnalités principales, créant ainsi un engagement authentique et une motivation à s'inscrire.

---

> **Note :** Cette documentation sera mise à jour au fur et à mesure de l'avancement du projet.
