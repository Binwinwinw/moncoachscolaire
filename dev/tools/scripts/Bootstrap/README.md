# Bootstrap Système `.github/` Complet

## Vue d'ensemble

Ce système automatise la création et la synchronisation de la **structure complète `.github/`** à travers tous les repositories d'une organisation.

### Qu'est-ce qui est bootstrappé ?

Chaque repo reçoit une copie complète du répertoire `.github/` contenant :

#### 📁 **Dossiers**
- **`instructions/`** — Instructions spécialisées par langage
  - `js.instructions.md` — Conventions JavaScript (front/admin)
  - `php.instructions.md` — Conventions PHP principales
  - `php.instructions.db.md` — Conventions DB
  - `php.instructions.dev-tools.md` — Conventions dev tools
  - `php.instructions.public.md` — Conventions dossier public
  - `sql.instructions.md` — Conventions SQL/MySQL
  - `sql.instructions.dev-db.md` — Conventions DB dev

- **`prompts/`** — Collection de prompts Copilot prêts à l'emploi
  - `socle-qualite-stable.prompt.md` — Socle qualité universel ✨
  - `action-driven-workflow.prompt.md` — Focus sur les actions concrètes
  - `audit-strict.prompt.md` — Mode audit/debug strict
  - `debug-script.prompt.md` — Déboguer les scripts
  - `doc-reprise.prompt.md` — Documentation rapide
  - `documentation-deduplication-management.prompt.md` — Gestion docs
  - `mysql-safe.prompt.md` — Mode MySQL sécurisé
  - `patch-minimal.prompt.md` — Patches minimaux
  - `tailwind-apply.prompt.md` — Tailwind CSS optimisé
  - `transparent-sections-background.prompt.md` — CSS transparent

- **`workflows/`** — CI/CD et automatisations (structure vide = à enrichir)

#### 📄 **Fichiers de Context & Règles**
- `CONTEXT_PRODUIT.md` — Description produit + roadmap
- `PROJECT_CONTEXT.md` — Structure et architecture du projet
- `REGLES_IA.md` — Règles IA spécifiques au projet
- `copilot-plan.md` — Plan d'action Copilot
- `copilot-instructions.md` — Instructions générales Copilot
- `API_GUIDE_SECURITE.md` — Guide API sécurité

---

## 🚀 Utilisation

### Comportement Automatique

Le script détecte automatiquement et adapte son comportement :

#### **Cas 1 : Nouveau Repo** (pas de `.github/`)
```powershell
./bootstrap-github-structure.ps1 -RootPath ..
```
✅ Crée `.github/` complet  
✅ Ajoute tous les dossiers standards (instructions/, prompts/, workflows/)  
✅ Ajoute tous les fichiers (copilot-instructions.md, CONTEXT_PRODUIT.md, etc.)  
✅ Affiche résumé des ajouts

#### **Cas 2 : Repo Existant** (`.github/` existe)
```powershell
./bootstrap-github-structure.ps1 -RootPath ..
```
✅ **Détecte** quels dossiers et fichiers manquent  
✅ **Sauvegarde** le `.github/` existant (`.github.backup-TIMESTAMP`)  
✅ **Fusionne** intelligemment :
  - Fichiers STANDARDS remplacés (copilot-instructions.md, CONTEXT_PRODUIT.md, etc.)
  - Dossiers STANDARDS synchronisés (instructions/, prompts/, workflows/)
  - Fichiers PERSO préservés (JOURNAL_*.md, fichiers métier uniques)
✅ Affiche résumé (X ajoutés, Y remplacés, Z préservés)

#### **Cas 3 : Erreur d'Écriture**
```
  [ERROR] Échec d'écriture: [message détail]
  [ROLLBACK] Restauration depuis backup...
```
✅ Restaure automatiquement depuis le backup  
✅ Message clair + raison de l'erreur  
✅ Aucune donnée perdue

---

### 📖 Exemples d'Exécution

#### Multi-Repos (Tous les repos du parent)

```powershell
# DryRun d'abord (recommandé)
./bootstrap-github-structure.ps1 -RootPath .. -DryRun

# Exécution réelle
./bootstrap-github-structure.ps1 -RootPath ..
```

**Sortie DryRun** :
```
=== Multi-Repos ===
[INFO] 12 repos détectés

Processing: cv-expert
  [DÉTECTION] Dossier .github/ existe
    À ajouter: 2 élément(s)
      • fichier: API_GUIDE.md (nouveau)
      • dossier: workflows/
    À vérifier/remplacer: 8 élément(s)
      • fichier: copilot-instructions.md (sera remplacé)
      • dossier: instructions/ (existant)
      • dossier: prompts/ (existant)
    À PRÉSERVER: 1 fichier(s) personnel(s)
      • fichier perso: JOURNAL_REPRISE.md
  [DRY-RUN] Aucune modification
```

#### Repo Unique

```powershell
./bootstrap-github-structure.ps1 -RepoPath ../mon-projet -DryRun
./bootstrap-github-structure.ps1 -RepoPath ../mon-projet
```

---

### ⚙️ Paramètres

| Paramètre | Type | Description |
|-----------|------|-------------|
| `-RootPath` | string | Chemin parent contenant les repos (default: `.`) |
| `-RepoPath` | string | Chemin spécifique d'un repo (alternative à `-RootPath`) |
| `-SourcePath` | string | Chemin du `.github/` source (détecté auto par défaut) |
| `-Recurse` | switch | Cherche les repos en mode récursif |
| `-DryRun` | switch | Affiche ce qui serait fait **sans modifier** |

**Note** : Le paramètre `-Force` a été supprimé. La logique de fusion intelligente s'applique automatiquement.

---

## 🔄 Flux de Travail Complet

### Étape 1 : Préparer la source maître

Assurez-vous que `moncoachscolaire/.github/` contient la structure complète à jour :

```
.github/
├── instructions/     (7 fichiers .instructions.md)
├── prompts/         (10 fichiers .prompt.md)
├── workflows/       (dossier vide)
├── CONTEXT_PRODUIT.md
├── copilot-instructions.md
├── REGLES_IA.md
└── ...autres fichiers
```

### Étape 2 : Tester en DryRun

Avant d'exécuter réellement :

```powershell
./dev/tools/scripts/bootstrap-github-structure.ps1 -RootPath .. -DryRun
```

Vérifiez l'output pour voir :
- Nombre de repos détectés
- Dossiers/fichiers à ajouter/remplacer/préserver

### Étape 3 : Exécuter le Bootstrap

Une fois validé :

```powershell
./dev/tools/scripts/bootstrap-github-structure.ps1 -RootPath ..
```

Le script détecte automatiquement si `.github/` existe et fusionne intelligemment.

### Étape 4 : Vérifier les Résultats

```powershell
# Lister tous les repos bootstrappés
Get-ChildItem -Recurse -Filter ".github" -Directory | Select-Object -ExpandProperty FullName

# Vérifier un repo spécifique
Get-ChildItem D:\Hostinger\public_html\blogodo\.github
```

---

## 🛡️ Sécurité & Rollback

### Backups Automatiques

Lors d'une fusion sur un repo existant, l'ancien `.github/` est sauvegardé :

```
.github.backup-20260306-014930/
```

### Restore un Backup

Si quelque chose s'est mal passé :

```powershell
Remove-Item -Recurse -Force .\.github
Rename-Item -Path .\.github.backup-20260306-014930 -NewName .github
```

---

## 📊 Exemple de Résultat

### DryRun Output

```
[SOURCE] Détecté : ..\moncoachscolaire\.github

=== Bootstrap Multi-Repos ===
[INFO] 12 repos détectés

Processing: cv-expert
  [DÉTECTION] Dossier .github/ existe
    À ajouter: 2 élément(s)
      • fichier: API_GUIDE.md (nouveau)
      • dossier: workflows/
    À vérifier/remplacer: 8 élément(s)
      • fichier: copilot-instructions.md (sera remplacé)
      • dossier: instructions/ (existant)
      • dossier: prompts/ (existant)
    À PRÉSERVER: 1 fichier(s) personnel(s)
      • fichier perso: JOURNAL_REPRISE.md
  [DRY-RUN] Aucune modification

Processing: blogodo
  [DÉTECTION] Nouveau dossier .github/ (création complète)
  [DRY-RUN] Aucune modification

... (10 autres repos)

[SUMMARY] OK=12 | Total=12
```

### Exécution Réelle Output

```
Processing: cv-expert
  [DÉTECTION] Dossier .github/ existe
    À ajouter: 2 élément(s)
    ...
  [1/3] Sauvegarde...
  [OK] Backup: .github.backup-20260306-014930
  [2/3] Fusion intelligente...
    ✓ instructions/ synchronisé
    ✓ prompts/ synchronisé
    ✓ copilot-instructions.md (mis à jour)
    ✓ CONTEXT_PRODUIT.md (mis à jour)
  [3/3] Vérification...
  [OK] Fusion réussie

Processing: blogodo
  [DÉTECTION] Nouveau dossier .github/ (création complète)
  [1/2] Création .github/...
  [2/2] Copie complète...
  [OK] Création réussie

[SUMMARY] OK=12 | Total=12
```

---

## 🤖 Intégration avec Copilot

Une fois le bootstrap exécuté, VS Code Copilot chargera automatiquement :

1. **À l'ouverture du repo** : les fichiers `copilot-instructions.md` et `copilot-plan.md`
2. **Via la commande `/`** : les prompts `.prompt.md` sont visibles dans l'autocomplétion
3. **Pour chaque langage** : les instructions appropriées (`js.instructions.md`, `php.instructions.md`, etc.) sont détectées et appliquées

---

## 🔧 Maintenance

### Mettre à jour la source

Si vous modifiez `moncoachscolaire/.github/`, re-bootstrapper tous les repos :

```powershell
./dev/tools/scripts/bootstrap-github-structure.ps1 -RootPath .. -DryRun
# Vérifiez les changements détectés
./dev/tools/scripts/bootstrap-github-structure.ps1 -RootPath ..
```

Chaque repo aura son `.github.backup-TIMESTAMP` créé.

### Monitorer la couverture

Vérifiez que tous les repos ont la structure :

```powershell
Get-ChildItem -Recurse -Path .. -Filter ".github" -Directory | 
  Select-Object @{n="Repo";e={$_.Parent.Name}}, FullName
```

---

## 🐛 Troubleshooting

### Erreur : "Aucun source .github/ détecté"

**Cause** : Le script n'a pas trouvé `moncoachscolaire/.github`

**Solution** : 
```powershell
# Spécifiez le chemin source explicitement
./dev/tools/scripts/bootstrap-github-structure.ps1 -RootPath .. -SourcePath D:\Hostinger\public_html\moncoachscolaire\.github
```

### Erreur : "Pas de .git/ détecté"

**Cause** : Un dossier n'est pas un repo Git

**Solution** : Le script ignore automatiquement les dossiers sans `.git/`. C'est normal.

### Output très long

**Cause** : Le script liste tous les fichiers à copy (peut être 100+ fichiers)

**Solution** : Utilisez `-DryRun` d'abord pour valider, puis exécutez sans pipe

---

## 📝 Changelog

### v1.0 (6 mars 2026)
- ✅ Bootstrap complet `.github/` (instructions, prompts, context files)
- ✅ Détection auto des repos (single + multi-repos)
- ✅ Modes `-Force`, `-Merge`, `-DryRun`
- ✅ Backup automatique des `.github/` existants
- ✅ Support `-Recurse` pour nested repos
- ✅ 12 repos testés avec succès

---

## 🎯 Prochaines Étapes

- [ ] Auto-sync via Git hooks (pre-push validation)
- [ ] Schedule sync automatique (GitHub Actions, task Windows)
- [ ] Version `-Include/-Exclude` pour filtrer repos (exclure backups)
- [ ] Double-sync script (prompt + copilot-instructions.md simultanément)
- [ ] Checklists personnalisables par repo

---

**Questions ?** Consultez `dev/tools/scripts/bootstrap-github-structure.ps1` ou le prompt `socle-qualite-stable.prompt.md`
