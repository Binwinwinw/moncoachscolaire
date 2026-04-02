# Spécification du format XML pour exercices

## 📋 DTD (Document Type Definition)

```xml
<!ELEMENT exercises (exercise+)>
<!ELEMENT exercise (
  Id,
  Subject,
  Level,
  Title,
  Content?,
  Answer?,
  Tips?,
  Domain?,
  Competence?,
  Difficulty?,
  Identifier,
  isactive?,
  XPPoints?
)>
<!ELEMENT Id (#PCDATA)>
<!ELEMENT Subject (#PCDATA)>
<!ELEMENT Level (#PCDATA)>
<!ELEMENT Title (#PCDATA)>
<!ELEMENT Content (#PCDATA)>
<!ELEMENT Answer (#PCDATA)>
<!ELEMENT Tips (#PCDATA)>
<!ELEMENT Domain (#PCDATA)>
<!ELEMENT Competence (#PCDATA)>
<!ELEMENT Difficulty (#PCDATA)>
<!ELEMENT Identifier (#PCDATA)>
<!ELEMENT isactive (#PCDATA)>
<!ELEMENT XPPoints (#PCDATA)>
```

---

## 📝 Schéma XML complet

### Élément racine
```xml
<?xml version="1.0" encoding="UTF-8"?>
<exercises>
  <!-- Contient 1 ou plusieurs éléments <exercise> -->
</exercises>
```

### Structure d'un exercice
```xml
<exercise>
  <Id>NNNN</Id>
  <Subject>Matière</Subject>
  <Level>Niveau</Level>
  <Title>Titre complet</Title>
  <Content>Énoncé/Instruction</Content>
  <Answer>Réponse/Correction</Answer>
  <Tips>Conseils/Astuces</Tips>
  <Domain>Domaine pédagogique</Domain>
  <Competence>Compétence visée</Competence>
  <Difficulty>Niveau de difficulté</Difficulty>
  <Identifier>CODE-UNIQUE</Identifier>
  <isactive>0|1</isactive>
  <XPPoints>Points</XPPoints>
</exercise>
```

---

## 🎯 Détails de chaque champ

### 1. `Id` (Optionnel)
- **Type:** Integer
- **Usage:** Auto-généré par la base, on peut le laisser ou le mettre
- **Exemple:** `1000`, `2001`
- **Note:** N'est PAS importé (auto-increment en base)

### 2. `Subject` (Obligatoire pour le contexte)
- **Type:** String (60 caractères max)
- **Usage:** Matière/Discipline
- **Valeurs possibles:**
  - `Français`
  - `Mathématiques`
  - `Anglais`
  - `Sciences`
  - `Histoire-Géographie`
  - `Philosophie`
  - `Informatique`
  - Etc.
- **Exemple:** `<Subject>Français</Subject>`

### 3. `Level` (Obligatoire pour le contexte)
- **Type:** String (10 caractères max)
- **Usage:** Niveau scolaire
- **Valeurs recommandées:**
  - `6eme` - 6ème (11-12 ans)
  - `5eme` - 5ème (12-13 ans)
  - `4eme` - 4ème (13-14 ans)
  - `3eme` - 3ème (14-15 ans)
  - `2nde` - 2nde (15-16 ans)
  - `1ere` - 1ère (16-17 ans)
  - `Terminale` - Terminale (17-18 ans)
- **Exemple:** `<Level>6eme</Level>`
- **Note:** Pas de majuscules, utiliser Unicode normal

### 4. `Title` ⭐ REQUIS
- **Type:** String (250 caractères max)
- **Usage:** Titre/Énoncé court de l'exercice
- **Format:** Descriptif et clair
- **Exemple:** `<Title>Accords des adjectifs qualificatifs</Title>`
- **⚠️ Validation:** Non vide obligatoirement

### 5. `Content` (Facultatif)
- **Type:** String - HTML/Texte avec CDATA
- **Usage:** Énoncé complet de l'exercice
- **Format:**
  ```xml
  <Content><![CDATA[
    <h3>Question:</h3>
    <p>Accorde l'adjectif...</p>
    <input type="text" placeholder="...">
  ]]></Content>
  ```
- **Recommandation:** Utiliser CDATA pour contenu HTML
- **Exemple:** Voir fichiers `.xml` dans `tools/data/`

### 6. `Answer` (Facultatif)
- **Type:** String - HTML/Texte avec CDATA
- **Usage:** Solution/Correction de l'exercice
- **Format:** Similaire à Content
- **Exemple:**
  ```xml
  <Answer><![CDATA[
    <h4>Réponse:</h4>
    <p>L'adjectif s'accorde en genre et nombre...</p>
  ]]></Answer>
  ```

### 7. `Tips` (Facultatif)
- **Type:** String - HTML/Texte avec CDATA
- **Usage:** Conseils, astuces, méthodologie
- **Format:** Liste ou paragraphes HTML
- **Exemple:**
  ```xml
  <Tips><![CDATA[
    <ul>
      <li>Cherche le nom auquel se rapporte l'adjectif</li>
      <li>Note son genre et nombre</li>
      <li>Accorde l'adjectif en conséquence</li>
    </ul>
  ]]></Tips>
  ```
- **Gamification:** Peut contenir badges, points, etc.

### 8. `Domain` (Facultatif)
- **Type:** String (150 caractères max)
- **Usage:** Domaine/Chapitre pédagogique
- **Exemples:**
  - `Grammaire`
  - `Conjugaison`
  - `Orthographe`
  - `Vocabulaire`
  - `Littérature`
  - `Algèbre`
  - `Géométrie`
  - `Physique`
  - Etc.
- **Utilité:** Catégorisation et recherche

### 9. `Competence` (Facultatif)
- **Type:** String (200 caractères max)
- **Usage:** Compétence visée/Objectif pédagogique
- **Exemples:**
  - `Accorder l'adjectif qualificatif`
  - `Résoudre une équation du 1er degré`
  - `Identifier les figures de style`
  - `Conjuguer au passé composé`
- **Aligne avec:** Socle commun de compétences

### 10. `Difficulty` (Facultatif)
- **Type:** String (10 caractères max)
- **Usage:** Niveau de difficulté relatif
- **Valeurs recommandées:**
  - `facile` - ⭐ - Basique, niveau d'entrée
  - `moyen` - ⭐⭐ - Intermédiaire, application
  - `difficile` - ⭐⭐⭐ - Avancé, complexe
  - Alternative: `easy`, `medium`, `hard`
  - Numérique: `1`, `2`, `3`
- **Exemple:** `<Difficulty>moyen</Difficulty>`

### 11. `Identifier` ⭐ REQUIS
- **Type:** String (50 caractères max) - **UNIQUE**
- **Usage:** Code unique et stable pour l'exercice
- **Format recommandé:** `MATIERE-NIVEAU-DOMAINE-NUMERO`
- **Exemples:**
  - `FR-6EME-GRAM-001` (Français, 6ème, Grammaire, #001)
  - `MATH-5EME-ALGE-047` (Maths, 5ème, Algèbre, #047)
  - `ENG-4EME-VERB-012` (Anglais, 4ème, Verbes, #012)
  - `SCI-3EME-PHYS-033` (Sciences, 3ème, Physique, #033)

- **Conventions:**
  - Pas d'espaces
  - Majuscules recommandées
  - Caractères alphanumériques + tirets
  - Stable dans le temps (pour historique/API)

- **⚠️ Validation:** 
  - Non vide obligatoirement
  - Doit être unique dans la table (doublon = erreur)

### 12. `isactive` (Ignoré)
- **Type:** Integer (0 ou 1)
- **Usage:** Statut actif/inactif (informationnel)
- **Note:** **PAS IMPORTÉ** (pas de colonne en base)
- **Exemple:** `<isactive>1</isactive>`

### 13. `XPPoints` (Ignoré)
- **Type:** Integer
- **Usage:** Points d'expérience pour gamification
- **Note:** **PAS IMPORTÉ** (pas de colonne en base)
- **Plage:** 0-100 (recommandé)
- **Exemple:** `<XPPoints>15</XPPoints>`

---

## 🔍 Règles de validation

### Validation au chargement XML
1. ✅ Fichier valide XML (bien-formé)
2. ✅ Élément racine = `<exercises>`
3. ✅ Contient au moins 1 `<exercise>`

### Validation à l'import
1. ✅ Title **non vide** → rejeté si vide
2. ✅ Identifier **non vide** → rejeté si vide
3. ✅ Identifier **unique** → rejeté si existe déjà en base
4. ✅ Champs optionnels = NULL si vides

### Validation de contenu
- HTML dans Content/Answer/Tips doit être **valide** (optionnel mais recommandé)
- Pas de caractères de contrôle non-UTF8
- Longueur des strings doit respecter le schéma BDD

---

## 💾 Encodage et caractères spéciaux

### Déclaration obligatoire
```xml
<?xml version="1.0" encoding="UTF-8"?>
```

### Caractères spéciaux
- Accentués: ✅ Supportés `é`, `à`, `ç`, `ù`
- Émojis: ⚠️ À éviter (risque de corruption)
- HTML entities: ✅ `&lt;`, `&amp;`, `&quot;`
- CDATA: ✅ Pour contenu HTML complexe

### Exemple avec caractères spéciaux
```xml
<?xml version="1.0" encoding="UTF-8"?>
<exercises>
  <exercise>
    <Title>Règles d'orthographe - L'accent aigu</Title>
    <Content><![CDATA[
      <p>Complète les mots avec é ou è :</p>
      <p><input type="text" name="q1"> = élève</p>
    ]]></Content>
  </exercise>
</exercises>
```

---

## 📐 Schéma XSD (optionnel pour validation)

```xsd
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema">

  <xs:element name="exercises">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="exercise" maxOccurs="unbounded">
          <xs:complexType>
            <xs:sequence>
              <xs:element name="Id" type="xs:integer" minOccurs="0"/>
              <xs:element name="Subject" type="xs:string"/>
              <xs:element name="Level" type="xs:string"/>
              <xs:element name="Title" type="xs:string"/>
              <xs:element name="Content" type="xs:string" minOccurs="0"/>
              <xs:element name="Answer" type="xs:string" minOccurs="0"/>
              <xs:element name="Tips" type="xs:string" minOccurs="0"/>
              <xs:element name="Domain" type="xs:string" minOccurs="0"/>
              <xs:element name="Competence" type="xs:string" minOccurs="0"/>
              <xs:element name="Difficulty" type="xs:string" minOccurs="0"/>
              <xs:element name="Identifier" type="xs:string"/>
              <xs:element name="isactive" type="xs:integer" minOccurs="0"/>
              <xs:element name="XPPoints" type="xs:integer" minOccurs="0"/>
            </xs:sequence>
          </xs:complexType>
        </xs:element>
      </xs:sequence>
    </xs:complexType>
  </xs:element>

</xs:schema>
```

---

## 📊 Exemples complets

### Exemple minimal (champs requis seulement)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<exercises>
  <exercise>
    <Subject>Mathématiques</Subject>
    <Level>6eme</Level>
    <Title>Calcul simple: 2+2</Title>
    <Identifier>MATH-6EME-ARITH-001</Identifier>
  </exercise>
</exercises>
```

### Exemple complet (tous les champs)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<exercises>
  <exercise>
    <Id>3001</Id>
    <Subject>Français</Subject>
    <Level>6eme</Level>
    <Title>Accords des adjectifs qualificatifs</Title>
    <Content><![CDATA[
      <p>Accorde les adjectifs entre parenthèses :</p>
      <ol>
        <li>Les fleurs (blanc et parfumé) : __________</li>
        <li>Une histoire (passionnant) : __________</li>
        <li>Des enfants (joyeux) : __________</li>
      </ol>
    ]]></Content>
    <Answer><![CDATA[
      <ol>
        <li><strong>blanches et parfumée</strong> (accord féminin pluriel)</li>
        <li><strong>passionnante</strong> (accord féminin singulier)</li>
        <li><strong>joyeux</strong> (accord masculin pluriel)</li>
      </ol>
    ]]></Answer>
    <Tips><![CDATA[
      <ul>
        <li>L'adjectif s'accorde en genre ET nombre avec le nom</li>
        <li>Cherche toujours le nom auquel se rapporte l'adjectif</li>
        <li>Attention à l'ordre : nom singulier → adjectif singulier</li>
      </ul>
    ]]></Tips>
    <Domain>Étude de la langue - Grammaire</Domain>
    <Competence>Accorder l'adjectif qualificatif avec le nom</Competence>
    <Difficulty>facile</Difficulty>
    <Identifier>FR-6EME-GRAM-099</Identifier>
    <isactive>1</isactive>
    <XPPoints>12</XPPoints>
  </exercise>
</exercises>
```

---

## 🛠️ Outils de validation

### Validateurs XML en ligne
- **xmlvalidation.com** : Validation XML basique
- **w3schools.com/xml** : Tester et valider
- **online-xml-tools.com** : Validation avancée

### Validateurs locaux (CLI)
```bash
# Avec Python
python -m xml.dom.minidom tools/data/francais_6eme.xml

# Avec xmllint (libxml2)
xmllint --format tools/data/francais_6eme.xml > /dev/null

# Avec PHP
php -r "simplexml_load_file('tools/data/francais_6eme.xml') or die('Invalid XML');"
```

---

## 📋 Checklist pour créer un fichier XML

- [ ] Déclaration XML correcte : `<?xml version="1.0" encoding="UTF-8"?>`
- [ ] Élément racine `<exercises>` présent
- [ ] Chaque exercice dans `<exercise>...</exercise>`
- [ ] **Title** rempli (requis)
- [ ] **Identifier** rempli et unique (requis)
- [ ] Subject et Level cohérents
- [ ] Contenu HTML valide (si présent)
- [ ] CDATA utilisé pour le HTML
- [ ] Fichier bien-formé (tester avec validateur)
- [ ] Encodage UTF-8 respecté
- [ ] Placé dans `tools/data/`

---

## 🔗 Références

- **Spécification XML:** https://www.w3.org/TR/xml/
- **SimpleXML PHP:** https://www.php.net/manual/en/book.simplexml.php
- **HTML5 valide:** https://html.spec.whatwg.org/

**Document créé:** 01-01-2026
**Version:** 1.0
