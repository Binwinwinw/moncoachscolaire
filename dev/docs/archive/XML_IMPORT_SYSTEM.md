# Système d'Import d'Exercices XML

## 📋 Vue d'ensemble

Le système d'import XML permet d'importer rapidement des exercices pédagogiques depuis des fichiers XML structurés vers la base de données MonCoachScolaire.

**Date de mise en place:** 01-01-2026
**Status:** ✅ Opérationnel et testé

---

## 🎯 Objectif

Établir une approche **scalable et maintenable** pour gérer les exercices via des fichiers de données structurés, plutôt que de les créer manuellement ou via des scripts ad-hoc.

**Avantages:**
- ✅ Données découpées du code
- ✅ Versioning facile des exercices
- ✅ Import batch pour plusieurs matières/niveaux
- ✅ Détection automatique des doublons
- ✅ Mode dry-run pour tester avant d'insérer
- ✅ Format standardisé et réutilisable

---

## 📁 Structure des fichiers

### Répertoire central
```
tools/data/
├── francais_6eme.xml         (21 exercices - TESTÉ ✅)
├── mathematiques_6eme.xml    (à venir)
├── sciences_6eme.xml         (à venir)
└── ...
```

Tous les fichiers XML d'exercices vont dans `tools/data/`

### Script d'import
```
tools/import_exercises_from_xml.php    (150 lignes - PROD-READY)
```

---

## 📄 Format XML d'exercice

Chaque exercice doit être enveloppé dans une balise `<exercise>` avec les champs suivants :

```xml
<exercise>
  <Id>1000</Id>                                 <!-- Auto-généré (optionnel) -->
  <Subject>Français</Subject>                   <!-- Matière -->
  <Level>6eme</Level>                           <!-- Niveau scolaire -->
  <Title>Accords des adjectifs...</Title>       <!-- Titre (REQUIS) -->
  <Content><![CDATA[...HTML...]]></Content>     <!-- Énoncé en HTML (optionnel) -->
  <Answer><![CDATA[...HTML...]]></Answer>       <!-- Correction en HTML (optionnel) -->
  <Tips><![CDATA[...HTML...]]></Tips>           <!-- Astuces pédagogiques (optionnel) -->
  <Domain>Grammaire</Domain>                    <!-- Domaine pédagogique (optionnel) -->
  <Competence>Accorder l'adjectif</Competence> <!-- Compétence visée (optionnel) -->
  <Difficulty>facile</Difficulty>               <!-- Niveau: facile/moyen/difficile (optionnel) -->
  <Identifier>FR-6EME-GRAM-001</Identifier>     <!-- Code unique (REQUIS) -->
  <isactive>1</isactive>                        <!-- (ignoré dans l'import) -->
  <XPPoints>12</XPPoints>                       <!-- (ignoré dans l'import) -->
</exercise>
```

### Champs REQUIS
- **Title** : Titre/Énoncé de l'exercice
- **Identifier** : Code unique (ex: `FR-6EME-GRAM-001`)
  - Format suggéré: `{MATIERE_CODE}-{NIVEAU}-{DOMAINE}-{NUM}`
  - Exemple: `MATH-5EME-ALGE-047`

### Champs FACULTATIFS (stockés en base)
- Subject, Level, Content, Answer, Tips, Domain, Competence, Difficulty

### Champs IGNORÉS
- `isactive`, `XPPoints` (non présents dans la table Exercises)

### Convention de contenu
Pour les champs `Content`, `Answer`, `Tips` contenant du HTML, utiliser `<![CDATA[...]]>` pour éviter les échappements XML :

```xml
<Content><![CDATA[
  <p>Accorde les adjectifs :</p>
  <ol>
    <li>Les fleurs (blanc) : <input type="text"></li>
  </ol>
]]></Content>
```

---

## 🚀 Utilisation du script d'import

### Installation
Le script est déjà en place : `tools/import_exercises_from_xml.php`

### Commandes

#### 1️⃣ Test en dry-run (RECOMMANDÉ d'abord)
```bash
php tools/import_exercises_from_xml.php tools/data/francais_6eme.xml --dry-run
```

**Résultat attendu:**
```
📋 Nombre d'exercices à importer: 21
🔍 MODE DRY-RUN activé (pas d'insertion en BDD)

✓ [0] FR-6EME-GRAM-001 (ID: 1000) - Serait importé [DRY-RUN]
✓ [1] FR-6EME-GRAM-002 (ID: 1001) - Serait importé [DRY-RUN]
...

==================================================
✓ Succès:    21
⚠️  Doublons: 0
❌ Erreurs:   0
```

#### 2️⃣ Import réel
```bash
php tools/import_exercises_from_xml.php tools/data/francais_6eme.xml
```

**Résultat attendu:**
```
✓ [0] FR-6EME-GRAM-001 (ID: 1000) - Importé avec succès
✓ [1] FR-6EME-GRAM-002 (ID: 1001) - Importé avec succès
...

==================================================
✓ Succès:    21
⚠️  Doublons: 0
❌ Erreurs:   0
```

### Flags disponibles
- `--dry-run` : Tester sans insérer en BDD (RECOMMANDÉ avant un import réel)

### Codes de sortie
- `0` : Succès (tous les exercices importés)
- `1` : Erreur(s) trouvée(s) ou fichier non valide

---

## ✅ Test réussi - Français 6ème

### Fichier testé
`tools/data/francais_6eme.xml` - 21 exercices

### Couvrage pédagogique
| Domaine | Nb exercices | IDs |
|---------|-------------|-----|
| Grammaire | 7 | GRAM-001 à 007 |
| Conjugaison | 4 | CONJ-001 à 004 |
| Orthographe | 4 | ORTHO-001 à 004 |
| Lecture/Compréhension | 1 | LECT-001 |
| Vocabulaire | 1 | VOCAB-001 |
| Production écrite | 2 | PROD-001 à 002 |
| Poésie | 1 | POET-001 |
| Synthèse générale | 1 | SYNTHESE-001 |
| **TOTAL** | **21** | |

### Résultats de l'import
```
Date: 2026-01-01
Fichier: francais_6eme.xml
Exercices importés: 21
Doublons détectés: 0
Erreurs: 0
Status: ✅ 100% succès
```

---

## 📊 Statistiques de base de données

Après l'import :
```sql
SELECT COUNT(*) FROM Exercises WHERE Subject = 'Français' AND Level = '6eme';
-- Résultat: 21 exercices
```

---

## 🔄 Workflow recommandé pour futurs imports

### Pour ajouter une nouvelle matière/niveau :

1. **Créer le fichier XML**
   ```bash
   # Exemple pour Mathématiques 5ème
   tools/data/mathematiques_5eme.xml
   ```

2. **Tester en dry-run**
   ```bash
   php tools/import_exercises_from_xml.php tools/data/mathematiques_5eme.xml --dry-run
   ```

3. **Vérifier les résultats**
   - Pas d'erreurs ? ✅
   - Nombre d'exercices correct ? ✅
   - Identifieurs uniques ? ✅

4. **Importer réellement**
   ```bash
   php tools/import_exercises_from_xml.php tools/data/mathematiques_5eme.xml
   ```

5. **Valider en base**
   ```sql
   SELECT COUNT(*) FROM Exercises WHERE Subject = 'Mathématiques' AND Level = '5eme';
   ```

---

## 🎓 Template pour nouveaux fichiers XML

Copier ce template et adapter :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<exercises>
  <exercise>
    <Id>2000</Id>
    <Subject>Mathématiques</Subject>
    <Level>5eme</Level>
    <Title>Équations simples du 1er degré</Title>
    <Content><![CDATA[
      <p>Résous l'équation suivante :</p>
      <p>2x + 3 = 11</p>
      <p>x = <input type="text" name="x" placeholder="..."></p>
    ]]></Content>
    <Answer><![CDATA[
      <p><strong>Solution :</strong></p>
      <p>2x + 3 = 11</p>
      <p>2x = 11 - 3</p>
      <p>2x = 8</p>
      <p>x = 4</p>
    ]]></Answer>
    <Tips><![CDATA[
      <ul>
        <li>Isole x en passant les termes de l'autre côté</li>
        <li>N'oublie pas de changer le signe!</li>
      </ul>
    ]]></Tips>
    <Domain>Algèbre</Domain>
    <Competence>Résoudre une équation du 1er degré</Competence>
    <Difficulty>moyen</Difficulty>
    <Identifier>MATH-5EME-ALGE-001</Identifier>
    <isactive>1</isactive>
    <XPPoints>15</XPPoints>
  </exercise>
  
  <!-- Ajouter d'autres exercices... -->
</exercises>
```

---

## 🛠️ Architecture technique

### Dépendances
- PHP 7.4+ (SimpleXML)
- PDO MySQL
- Table `Exercises` avec colonnes : Id, Subject, Level, Title, Content, Answer, Tips, Domain, Competence, Difficulty, Identifier

### Sécurité
- ✅ Prepared statements (protection contre SQL injection)
- ✅ Validation des champs obligatoires
- ✅ Détection des doublons via `Identifier`
- ✅ Gestion d'erreurs complète

### Performance
- Load XML via `simplexml_load_file()` (efficace)
- Boucle d'import simple et linéaire : O(n)
- Pas de dépendance lourde (SimpleXML natif de PHP)

---

## 📝 Notes d'implémentation

### Colonnes de la table Exercises
```sql
CREATE TABLE Exercises (
  Id INT AUTO_INCREMENT PRIMARY KEY,
  Subject VARCHAR(60),
  Level VARCHAR(10),
  Title VARCHAR(250),
  Content LONGTEXT,
  Answer LONGTEXT,
  Tips LONGTEXT,
  Domain VARCHAR(150),
  Competence VARCHAR(200),
  Difficulty VARCHAR(10),
  Identifier VARCHAR(50) UNIQUE,
  KEY idx_identifier (Identifier),
  KEY idx_level_subject (Level, Subject)
);
```

### Validation avant l'insertion
1. Fichier XML valide ? ✅
2. PDO connection active ? ✅
3. Title non vide ? ✅
4. Identifier non vide ? ✅
5. Identifier pas déjà en base ? ✅

---

## 🚨 Troubleshooting

| Problème | Solution |
|----------|----------|
| "Column not found" | Vérifier que la table a les colonnes (voir ALTER_EXERCISES_TABLE.sql) |
| "Identifier déjà existant" | Changer l'Identifier ou nettoyer la base |
| "XML invalide" | Vérifier la syntaxe XML, utiliser CDATA pour HTML |
| Erreur PDO | Vérifier db/connection.php et les credentials MySQL |
| Fichier non trouvé | Vérifier le chemin relatif et permissions du fichier |

---

## 📚 Prochaines étapes

1. ✅ XML import system mise en place
2. ✅ Test avec Français 6ème (21 exercices)
3. 📋 Créer XML pour :
   - Mathématiques (4 niveaux)
   - Sciences (4 niveaux)
   - Anglais (4 niveaux)
   - Histoire-Géographie (4 niveaux)
   - Autres matières...

4. 🎯 Intégrer dans pipeline de développement
5. 📊 Créer dashboard de gestion d'exercices XML

---

## 📞 Support

Pour toute question sur le système d'import :
- Vérifier ce document
- Consulter les logs du script (sortie colorée)
- Lancer en --dry-run pour debug
- Vérifier les fichiers XML via un validateur

**Dernière mise à jour:** 01-01-2026
