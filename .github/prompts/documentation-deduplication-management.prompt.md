# Prompt : Gestion et déduplication de la documentation projet

## 🎯 Objectif

Auditer, organiser et déduplicater la documentation du projet **MonCoachScolaire** sans perdre d'informations essentielles, tout en évitant les doublons entre fichiers.

**Contexte :** Le projet peut être repris par d'autres outils ou assistants IA. La documentation doit être **claire, non-redondante, complète et maintenable** pour permettre une continuité sans confusion.

---

## 📋 Principes de gestion documentaire

### ✅ **Ce qui est acceptable**
- **Plusieurs fichiers avec des rôles distincts** (ex : README.md pour l'aperçu, CONTRIBUTING.md pour les contributions)
- **Références croisées** entre documents (ex : "Voir ARCHITECTURE.md pour les détails")
- **Informations complémentaires** dans différents fichiers si elles servent des contextes différents

### ❌ **Ce qui doit être évité**
- **Doublons littéraux** : même information copiée-collée dans plusieurs fichiers
- **Contradictions** : informations qui se contredisent entre fichiers
- **Fichiers obsolètes** : documentation qui n'est plus à jour ou ne correspond plus au code
- **Informations orphelines** : infos importantes noyées dans des fichiers sans rôle clair

---

## 🔍 Méthodologie d'audit documentaire

### **Étape 1 : Inventaire**
Liste tous les fichiers de documentation existants avec leur rôle supposé.

**Format attendu :**
```markdown
| Fichier                     | Rôle actuel                          | Taille  | Dernière MAJ |
|-----------------------------|--------------------------------------|---------|--------------|
| README.md                   | Aperçu général du projet             | 5 KB    | 2026-02-14   |
| .github/REGLES_IA.md        | Règles pour assistants IA            | 12 KB   | 2026-02-10   |
| docs/ARCHITECTURE.md        | Structure technique du projet        | 8 KB    | 2026-02-05   |
| CONTRIBUTING.md             | Guide de contribution                | 3 KB    | 2026-01-20   |
| action-driven-workflow.md   | Méthodologie de travail              | 6 KB    | 2026-02-14   |
```

---

### **Étape 2 : Analyse des doublons**

Pour chaque paire de fichiers, identifier :
1. **Informations identiques** (copier-coller pur)
2. **Informations similaires** (même sujet, formulation différente)
3. **Informations complémentaires** (angle différent sur le même sujet)

**Format attendu :**
```markdown
### Doublon détecté : README.md vs ARCHITECTURE.md

**Section concernée :** "Structure des dossiers"

**README.md (lignes 45-60) :**
```
src/
├── pages/       # Pages PHP
├── includes/    # Composants réutilisables
├── components/  # Composants UI
└── config/      # Configuration
```

**ARCHITECTURE.md (lignes 12-35) :**
```
Arborescence complète :
src/
├── pages/           # Pages PHP (landingpage, admin, eleve, parents)
├── includes/        # Topbar, footer, admin_auth
├── components/      # Composants UI (card.php)
├── config/          # config.php, site_boot.php
└── database/        # connection.php, migrations
```

**Décision :** 
- Conserver la version détaillée dans ARCHITECTURE.md
- Dans README.md, remplacer par : "Voir ARCHITECTURE.md pour la structure complète"
```

---

### **Étape 3 : Définir le rôle unique de chaque fichier**

Chaque fichier doit avoir un **rôle distinct et clairement défini**.

**Tableau de rôles (à adapter selon le projet) :**

| Fichier                          | Rôle unique                                                                 | Audience cible        |
|----------------------------------|-----------------------------------------------------------------------------|-----------------------|
| `README.md`                      | Aperçu général, installation rapide, liens vers docs détaillées            | Tous (découverte)     |
| `.github/REGLES_IA.md`           | Règles et conventions pour assistants IA (Copilot, ChatGPT, etc.)          | IA + développeurs     |
| `docs/ARCHITECTURE.md`           | Structure technique, choix d'architecture, organisation du code             | Développeurs avancés  |
| `docs/API.md`                    | Documentation des endpoints et fonctions publiques                          | Développeurs          |
| `CONTRIBUTING.md`                | Guide pour contribuer (workflow Git, conventions, tests)                    | Contributeurs         |
| `CHANGELOG.md`                   | Historique des versions et modifications                                    | Tous (suivi)          |
| `docs/DEPLOYMENT.md`             | Instructions de déploiement (serveur, environnement, CI/CD)                 | DevOps                |
| `action-driven-workflow.md`      | Méthodologie de travail (analyse → explication → action)                    | IA + développeurs     |
| `transparent-sections-bg.md`     | Instructions spécifiques pour une tâche (exemple : modification CSS)       | IA (tâche ponctuelle) |

---

### **Étape 4 : Plan de déduplication**

Pour chaque doublon identifié, décider de l'action à prendre.

**Actions possibles :**
1. **Conserver dans un seul fichier** (le plus détaillé ou le plus pertinent)
2. **Remplacer par une référence** dans les autres fichiers (ex : "Voir X.md section Y")
3. **Fusionner** si les deux versions apportent des infos complémentaires
4. **Archiver** si l'information est obsolète

**Format de plan :**
```markdown
### Plan de déduplication

#### 1. Structure des dossiers
- **Source principale :** `ARCHITECTURE.md` (lignes 12-35)
- **Action sur README.md :** Remplacer par "📁 Voir [ARCHITECTURE.md](docs/ARCHITECTURE.md) pour la structure complète"
- **Justification :** README doit rester concis, ARCHITECTURE contient les détails

#### 2. Règles de nommage des variables
- **Source principale :** `.github/REGLES_IA.md` (section "Conventions")
- **Action sur CONTRIBUTING.md :** Ajouter "Respecter les conventions définies dans [REGLES_IA.md](.github/REGLES_IA.md)"
- **Justification :** REGLES_IA est la référence pour les conventions, CONTRIBUTING guide le workflow

#### 3. Instructions d'installation
- **Source principale :** `README.md` (section "Installation")
- **Action sur DEPLOYMENT.md :** Commencer par "Pour une installation locale, voir [README.md](../README.md). Cette page couvre le déploiement en production."
- **Justification :** README = installation locale, DEPLOYMENT = production
```

---

## 🛠️ Tâches concrètes pour Copilot

### **Tâche 1 : Inventaire de la documentation**
```
Analyse tous les fichiers .md du projet et génère un tableau Markdown listant :
- Nom du fichier
- Rôle actuel (déduit du contenu)
- Taille approximative (nombre de lignes ou de sections)
- Principales sections couvertes

Format de sortie : tableau Markdown enregistré dans `docs/INVENTORY.md`
```

---

### **Tâche 2 : Détection des doublons**
```
Compare les fichiers suivants deux à deux et identifie les sections identiques ou très similaires :
- README.md
- .github/REGLES_IA.md
- docs/ARCHITECTURE.md
- CONTRIBUTING.md
- [autres fichiers .md pertinents]

Pour chaque doublon trouvé, génère un rapport avec :
- Fichiers concernés
- Sections concernées (numéros de lignes)
- Niveau de similarité (identique / similaire / complémentaire)
- Suggestion d'action (conserver où ? référencer comment ?)

Format de sortie : rapport Markdown enregistré dans `docs/DUPLICATES_REPORT.md`
```

---

### **Tâche 3 : Proposition de réorganisation**
```
En te basant sur l'inventaire et le rapport de doublons, propose une réorganisation de la documentation qui :
1. Définit un rôle unique et clair pour chaque fichier
2. Élimine les doublons (par suppression ou référence croisée)
3. Préserve toutes les informations essentielles
4. Maintient la cohérence globale

Format de sortie :
- Tableau des rôles (fichier → rôle unique → audience)
- Plan de migration (quoi déplacer où, quoi référencer)
- Liste des actions à effectuer (fichier par fichier)

Enregistrer dans `docs/REORGANIZATION_PLAN.md`
```

---

### **Tâche 4 : Application des modifications**
```
Applique les modifications planifiées dans REORGANIZATION_PLAN.md en respectant ces règles :

✅ À faire :
- Supprimer les doublons littéraux
- Remplacer par des références croisées claires (format : "Voir [FICHIER.md](chemin/vers/fichier.md#section)")
- Déplacer les sections mal placées vers le bon fichier
- Ajouter des en-têtes clairs si nécessaire

❌ À ne PAS faire :
- Supprimer des informations uniques (même si elles semblent mineures)
- Créer de nouveaux fichiers sans valider leur rôle
- Casser les liens existants (vérifier après modification)

Après chaque modification, ajouter un commentaire HTML invisible dans le fichier source :
<!-- Déduplication appliquée le 2026-02-14 : section X déplacée vers Y.md -->
```

---

## 📊 Critères de validation

Après réorganisation, la documentation doit respecter ces critères :

### ✅ **Checklist de qualité**
- [ ] Chaque fichier a un rôle unique et clairement défini
- [ ] Aucun doublon littéral entre fichiers
- [ ] Les références croisées sont claires et fonctionnelles
- [ ] Toutes les informations essentielles sont préservées
- [ ] La navigation entre documents est logique
- [ ] Les audiences cibles sont identifiées (développeurs, IA, utilisateurs, etc.)
- [ ] Les fichiers obsolètes sont archivés (déplacés dans `docs/archive/`)
- [ ] Un historique des modifications est disponible (commentaires HTML ou CHANGELOG)

---

## 🔄 Maintenance continue

### **Règles pour éviter les doublons futurs**

1. **Avant d'ajouter une nouvelle information dans un fichier :**
   - Vérifier si elle existe déjà ailleurs
   - Si oui, ajouter une référence au lieu de dupliquer
   
2. **Avant de créer un nouveau fichier de documentation :**
   - Vérifier qu'aucun fichier existant ne couvre déjà ce rôle
   - Définir clairement son rôle unique
   - L'ajouter au tableau des rôles (`docs/INVENTORY.md`)

3. **Lors d'une mise à jour d'information :**
   - Mettre à jour dans le fichier source principal
   - Vérifier que les références croisées pointent toujours vers la bonne section

4. **Audit trimestriel :**
   - Relancer les tâches 1 (inventaire) et 2 (doublons) tous les 3 mois
   - Ajuster la documentation si de nouveaux doublons apparaissent

---

## 📝 Template de référence croisée

Utiliser ce format standardisé pour les références entre documents :

```markdown
### [Titre de la section]

> 📖 **Pour en savoir plus :** Voir [FICHIER.md](chemin/vers/fichier.md#ancre-section) – Section "Titre exact"

_Résumé en 1-2 phrases si nécessaire pour le contexte._
```

**Exemple concret :**
```markdown
### Structure du projet

> 📖 **Pour en savoir plus :** Voir [ARCHITECTURE.md](docs/ARCHITECTURE.md#arborescence) – Section "Arborescence complète"

Le projet suit une architecture MVC adaptée avec séparation claire entre pages, composants et configuration.
```

---

## 🎯 Résumé en une phrase

**"Chaque fichier de documentation doit avoir un rôle unique, clair et non-redondant. Les doublons doivent être remplacés par des références croisées vers le document source le plus détaillé."**

---

## 📂 Fichiers à créer/mettre à jour

Suite à l'application de ce prompt, les fichiers suivants seront générés ou mis à jour :

| Fichier                           | Type      | Contenu                                      |
|-----------------------------------|-----------|----------------------------------------------|
| `docs/INVENTORY.md`               | Nouveau   | Inventaire complet de la documentation       |
| `docs/DUPLICATES_REPORT.md`       | Nouveau   | Rapport des doublons détectés                |
| `docs/REORGANIZATION_PLAN.md`     | Nouveau   | Plan de réorganisation détaillé              |
| `README.md`                       | Mise à jour | Doublons supprimés, références ajoutées     |
| `.github/REGLES_IA.md`            | Mise à jour | Doublons supprimés, rôle clarifié           |
| `docs/ARCHITECTURE.md`            | Mise à jour | Sections consolidées                        |
| `CONTRIBUTING.md`                 | Mise à jour | Références vers autres docs                 |
| [Autres fichiers .md concernés]   | Mise à jour | Selon le plan de réorganisation             |

---

## 🚀 Comment utiliser ce prompt avec Copilot

### **Option 1 : Exécution complète**
```
@workspace Applique la méthodologie complète de gestion documentaire décrite dans [ce prompt] au projet MonCoachScolaire. Génère l'inventaire, le rapport de doublons, le plan de réorganisation, puis applique les modifications.
```

### **Option 2 : Étape par étape**
```
1. @workspace Génère l'inventaire de la documentation (Tâche 1)
2. [Vérifier le résultat]
3. @workspace Détecte les doublons (Tâche 2)
4. [Vérifier le résultat]
5. @workspace Propose un plan de réorganisation (Tâche 3)
6. [Valider le plan]
7. @workspace Applique les modifications planifiées (Tâche 4)
```

### **Option 3 : Audit ponctuel**
```
@workspace Analyse la documentation existante et identifie les doublons potentiels entre README.md et .github/REGLES_IA.md. Propose des actions correctives sans appliquer les modifications.
```

---

**Nom du fichier :** `documentation-deduplication-management.prompt.md`
