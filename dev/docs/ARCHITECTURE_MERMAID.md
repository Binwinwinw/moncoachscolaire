ok procède

# Schéma d’architecture technique MonCoachScolaire

```mermaid
flowchart TD
    subgraph Utilisateurs
        U1[Élève]
        U2[Parent]
        U3[Admin]
    end
    U1-->|Navigateur|FE[Front-end PHP/JS (public/)]
    U2-->|Navigateur|FE
    U3-->|Navigateur|FE
    FE-->|Requêtes HTTP|BE[Backend PHP (src/)]
    BE-->|Lecture/Écriture|DB[(Base de données MySQL)]
    BE-->|Lecture/Écriture|JSON[Exercices JSON/XML (db/json, docs/data)]
    BE-->|Inclut|INC[Includes & Config (src/includes, src/config)]
    BE-->|APIs internes|API[API PHP (src/api)]
    BE-->|Pages dynamiques|PAGES[Pages (src/pages)]
    FE-->|Assets statiques|ASSETS[Assets (public/assets)]
    subgraph Outils & Tests
        TOOLS[Outils/scripts (dev/tools)]
        TESTS[Tests (dev/tests)]
    end
    TOOLS-->|Import/Export|DB
    TOOLS-->|Import/Export|JSON
    TESTS-->|Vérification|BE
```

> Ce schéma illustre les principaux flux entre utilisateurs, front-end, back-end, base de données, fichiers d’exercices, outils et tests.

## Schéma IA/Ollama (quiz)

```mermaid
graph TD
A[Front (JS)] -- POST /api/ia/generate_quiz.php --> B[Backend PHP]
B -- Prompt structuré --> C[Ollama (container IA)]
C -- JSON questions --> B
B -- HTML+JSON --> A
A -- POST /api/ia/save_generated_quiz.php --> B
B -- DB insert --> D[(Base de données)]
```

> Ce schéma illustre le flux complet de génération de quiz via IA (Ollama/container), du front jusqu'à la base de données, avec fallback automatique si besoin.
