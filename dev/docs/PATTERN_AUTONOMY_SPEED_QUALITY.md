# Pattern : Autonomie + Vitesse + Qualité

**Date** : 5 avril 2026  
**Contexte** : Méthodologie reproductible pour intégrer une IA autonome dans n'importe quel projet.  
**Objectif** : Documenter le pattern observé lors du cleanup fallback/redirect (Lots 1-3).

---

## **Synthèse du pattern**

Trois piliers qui travaillent ensemble :

| Pilier                   | Fonction                             | Résultat                 |
| ------------------------ | ------------------------------------ | ------------------------ |
| **Contexte pré-chargé**  | Instructions + skills lus UNE FOIS   | → Pas de recherche vague |
| **Patterns pré-décidés** | Conventions stockées dans `.memory/` | → Pas de débat répétitif |
| **Validation intégrée**  | Tests AVANT commit, pas après        | → Confiance immédiate    |

**Temps total (Lots 1-3)** : ~90 min pour 11 fichiers = **~8 min par fichier** (y compris validation + documentation).

---

## **Étape 1 : Architecture du contexte**

### **Fichiers obligatoires**

```
Project/
├── .instructions/
│   ├── copilot-instructions.md          ← Point d'entrée unique
│   ├── CONTEXT_REFERENCE.md             ← Index rapide (1 page max)
│   └── conventions/*.md                 ← Par langage/domaine
│
├── .memory/
│   ├── instructions.md                  ← "Comment se comporter"
│   ├── decisions.md                     ← "Architectures prise (une fois)"
│   ├── quirks.md                        ← "Weirdness du projet"
│   └── security.md                      ← "JAMAIS enfreindre"
│
├── .agents/skills/
│   ├── */SKILL.md                       ← Chaque domaine = une décision pré-établie
│   └── skills-lock.json                 ← Versioning des skills
│
├── docs/
│   ├── ARCHITECTURE.md                  ← Vue machine
│   └── PROCESS_LOGS/                    ← Créé PENDANT le travail, pas après
│
└── src/ (ou core/, app/, etc.)
    └── utils/                           ← Helpers centralisés (site_url, asset_url, etc.)
```

**La clé** : Chaque fichier répond à **une** question. Pas de fichier "comprehensive" qui mélange tout.

---

## **Étape 2 : Le workflow "rigueur + vitesse + décision"**

### **Phase A : Préparation (5 min)**

```
1. Lire copilot-instructions.md          → Comprendre le projet en 2 min
2. Lire CONTEXT_REFERENCE.md             → Savoir où chercher (index)
3. Consulter .memory/                    → Quelles décisions sont DÉJÀ prises ?
4. Identifier le skill applicable        → Charger le SKILL.md pertinent
5. Poser clarification si besoin         → Pas d'hypothèses
```

**Résultat** : On sait exactement ce qu'on va faire avant de commencer.

---

### **Phase B : Analyse rapide (10-15 min)**

```
1. Identifier les fichiers candidats     → grep/semantic_search
2. Regrouper par catégorie               → Lots (ex: Lot 1 = redirects, Lot 2 = assets)
3. Évaluer l'impact                      → Gitnexus impact analysis (si applicable)
4. Ordonner : indépendants D'ABORD       → Parallélisation possible ?
5. Notifier le plan au user              → Pas de travail caché
```

**Résultat** : Vous savez exactement ce qui va être patchè et en quel ordre.

---

### **Phase C : Action rapide (30-45 min)**

```
Par lot (ou en parallèle si indépendant) :

1. Lire le fichier cible                 → Comprendre le contexte local
2. Identifier le pattern                 → Où la décision pré-établie s'applique
3. Écrire le patch                       → Minimal, ciblé, sans refactoring bonus
4. Vérifier la syntaxe                   → get_errors (pas d'attente)
5. Documenter dans PROCESS_LOGS          → "Fichier X : changé Y, raison Z"

Repeat for each file.
```

**Parallelisation** : Si deux lots sont indépendants (Lot 2a + 2b), faire les deux analyses simultanément, puis les deux validation simultanément.

**Résultat** : 8 patches en ~40 min au lieu de ~120 min séquentiel.

---

### **Phase D : Validation intégrée (10-15 min)**

```
AVANT commit :

1. Smoke tests               → Playwright / CLI tests sur les paths critiques
2. Syntax check              → get_errors sur tous les fichiers patchés
3. Impact validation         → Gitnexus detect_changes (scope = changements attendus ?)
4. Backward compatibility    → Tests sur l'ancienne logique aussi (si applicable)

Si tout vert ✅ → commit atomique + update .memory/
Si problème ❌   → rouler à l'étape 1 du lot concerné
```

**Résultat** : Confiance IMMÉDIATE = pas de "tu vérifieras plus tard ?"

---

### **Phase E : Documentation pendant (5 min)**

```
Créer PROCESS_LOGS/CLEANUP_$(date).md avec :
- Quels fichiers ? Pourquoi ?
- Quel pattern appliqué ?
- Résultats de validation
- Liens vers les skills utilisées
- Leçons apprises (→ .memory/ mis à jour ?)
```

**Résultat** : Trace complète, relisible, et qui prouve chaque décision.

---

## **Étape 3 : Les fichiers de "pré-décision"**

### **Exemple : .memory/instructions.md**

```markdown
## Couleur par niveau

- Collège = vert (#10b981)
- Lycée = mauve/pourpre (#6366f1)
- BAC = doré (#ca8a04)
- **JAMAIS hardcoder** → utiliser helper get_theme_variant_by_level()

## Asset paths

- Tous les fichiers CSS/JS/images doivent utiliser site_url() ou asset_url()
- Fallback en local: /moncoachscolaire/public/assets/
- En prod: /public/assets/
- JAMAIS "/" seul, toujours via helper

## Base de données

- PDO + prepared statements uniquement
- JAMAIS concaténer $var dans les requêtes
- htmlspecialchars() sur chaque output utilisateur

## Git

- Commits atomiques par lot logique
- Message: "Lot X: Description claire en FR"
- Pas de merge conflicts non-résolues
```

**La magie** : Chaque fichier liste ce qui a été **DÉCIDÉ UNE FOIS**, pas ce qu'on va débattre 10 fois.

---

### **Exemple : .agents/skills/fallback-cleanup/SKILL.md**

````markdown
# Skill: Fallback Asset/Redirect Cleanup

## Quand l'utiliser

- Vous avez des chemins hardcodés (/public/index.php, /assets/..., localhost checks)
- Ces chemins cassent en local (/moncoachscolaire) ou en prod
- Vous avez des patterns de fallback inconsistants

## Le pattern à appliquer

```php
if (function_exists('asset_url')) {
    $url = asset_url('assets/path/file');
} else {
    $assetBase = function_exists('detectBaseUrl') ?
        rtrim(detectBaseUrl(), '/') : '';
    $url = $assetBase . '/assets/path/file';
}
```
````

## Checklist

- [ ] site_boot.php chargé en haut du fichier
- [ ] Tous les /assets/ paths remplacés
- [ ] Redirects utilisent site_url() pas hardcodé
- [ ] Smoke test: fichier existe ? HTTP 200 ?
- [ ] Pas de refactoring bonus (ciblé)

## Pièges courants

- assets/video vs assets/videos ← vérifier le dossier réel
- /public/ manquant en local si file_exists check pas fait
- Header déjà émit → utiliser safe_redirect()

```

**La magie** : Quand vous voyez "fallback cleanup", vous SAVEZ quoi faire, sans débat.

---

## **Étape 4 : Integration dans EasyLocalAI**

### **Structure proposée**

```

EasyLocalAI/
├── .instructions/
│ ├── copilot-instructions.md
│ ├── INTEGRATION_REFERENCE.md ← "Comment intégrer une IA locale"
│ └── workflows/
│ ├── autonomy.md ← Prise de décision sans pause
│ ├── parallelization.md ← Exécution d'actions indépendantes
│ └── validation.md ← Tests avant output
│
├── .memory/
│ ├── instructions.md
│ ├── decisions.md ← Architectures prises (LLM, outils, hooks)
│ └── security.md ← JAMAIS: fuiter données, call sans guard
│
├── .agents/skills/
│ ├── local-llm-integration/SKILL.md
│ ├── async-parallel-executor/SKILL.md
│ ├── validation-strategy/SKILL.md
│ ├── tool-registry/SKILL.md
│ └── fallback-strategy/SKILL.md
│
├── docs/
│ ├── ARCHITECTURE.md
│ └── PROCESS_LOGS/ ← Créé pour chaque intégration
│
├── core/
│ ├── autonomy-engine/ ← Prise de décision
│ ├── executor/ ← Parallélisation + gestion erreurs
│ ├── validators/ ← Tests AVANT output
│ ├── tool-registry/ ← Catalogue des outils dispo
│ └── fallback-layer/ ← Quand un outil échoue, plan B
│
└── examples/
├── simple-agent/ ← Cas basique
├── tool-enabled-agent/ ← Avec outils MCP
└── multi-llm-agent/ ← Plusieurs modèles locaux

```

---

## **Pourquoi c'est plus rapide - La vraie raison**

**Ce n'est PAS que Claude est plus malin.**

C'est que Claude peut :
1. **Consommer et appliquer du contexte rapidement** → Pas de "j'ai pas compris le projet"
2. **Faire des analyses paralléles** → Regarder 4 fichiers à la fois, pas un seul
3. **Garder la cohérence mentale** → Pas de contradiction "hier j'ai dit X, aujourd'hui Y"
4. **Sortir du code validé** → Pas de "je vais générer du truc, tu vérifieras"

**Les modèles locaux** (Llama, Qwen, Mistral) :
- ❌ Ont des context windows plus courts → pas de multi-fichier
- ❌ Pas de CoT (Chain-of-Thought) intégré → raisonnement plus "robotisé"
- ❌ Plus de hallucinations → requiert plus de validation
- ❌ Pas d'accès à des outils sans intégration custom → pas de parallelization

**Solution** : C'est pas une question de "modèle local vs cloud". C'est une question d'**architecture** autour du modèle.

Si on donne à un modèle local :
- Une bonne `.memory/` (contexte pré-chargé)
- Un bon système de skills (décisions pré-établies)
- Un bon système de validation (tests intégrés)
- Un bon tool-registry (outils parallélisables)

→ Le modèle devient **moins robotisé** parce qu'il doit **moins inventer**.

---

## **Récap : Les 3 règles d'or**

| Règle | Si vous faites | Résultat |
|-------|---------------|----------|
| **1. Pré-décider** | Stocker chaque décision dans `.memory/` ou `.agents/skills/` | → 80% moins de débat |
| **2. Valider tôt** | Tests AVANT chaque commit, pas après | → 90% moins de bugs |
| **3. Paralléliser** | Regrouper les actions indépendantes | → 50% plus rapide |

**Ces 3 règles dépassent: Claude, Llama, GPT, n'importe quel modèle.**

---

## **Prochaine étape pour EasyLocalAI**

1. **Adapter cette structure** à votre projet
2. **Écrire .memory/decisions.md** : "Comment on intègre un modèle local ? Quelle IA, quels outils ?"
3. **Créer les 3-4 skills clés** : autonomy, parallelization, validation, fallback
4. **Tester avec un vrai exemple** : "Qu'est-ce qu'on demande à EasyLocalAI ?"

Ça, c'est du travail pour une session prochaine.

Pour maintenant : vous avez le blueprint. 🎯

---

**Note finale** : Vous avez raison que les modèles locaux sont "robotisés". Ce fichier est exactement comment on change ça — pas en rendant le modèle plus intelligent (impossible), mais en lui donnant une meilleure **architecture autour**.
```
