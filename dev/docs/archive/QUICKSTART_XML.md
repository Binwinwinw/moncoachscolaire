# Guide de démarrage rapide - Import XML

**⏱️ Durée:** 5 minutes  
**Niveau:** Débutant

---

## 🎯 Objectif

Importer rapidement des exercices XML dans MonCoachScolaire.

---

## ⚡ Quickstart (3 étapes)

### Étape 1: Préparer le fichier XML

Créer un fichier `tools/data/ma_matiere.xml` :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<exercises>
  <exercise>
    <Subject>Mathématiques</Subject>
    <Level>6eme</Level>
    <Title>Additionner deux nombres</Title>
    <Content><![CDATA[
      <p>Calcule : 5 + 3 = ?</p>
      <input type="text" placeholder="Réponse">
    ]]></Content>
    <Answer><![CDATA[
      <p>Réponse: 5 + 3 = <strong>8</strong></p>
    ]]></Answer>
    <Identifier>MATH-6EME-ARITH-001</Identifier>
  </exercise>
</exercises>
```

**Points clés:**
- ✅ Balise racine `<exercises>`
- ✅ Chaque exercice dans `<exercise>...</exercise>`
- ✅ **Title** obligatoire
- ✅ **Identifier** obligatoire (unique!)
- ✅ HTML dans CDATA

### Étape 2: Tester en dry-run

```bash
cd "d:\Hostinger\public_html\moncoachscolaire"
php tools/import_exercises_from_xml.php tools/data/ma_matiere.xml --dry-run
```

**Résultat attendu:**
```
✓ XML chargé avec succès
✓ Connexion à la base de données établie

📋 Nombre d'exercices à importer: 1
🔍 MODE DRY-RUN activé (pas d'insertion en BDD)

✓ [exercise] MATH-6EME-ARITH-001 - Serait importé [DRY-RUN]

==================================================
✓ Succès:    1
❌ Erreurs:   0
==================================================
```

**Pas d'erreur = Bon signe ✅**

### Étape 3: Importer réellement

```bash
php tools/import_exercises_from_xml.php tools/data/ma_matiere.xml
```

**Résultat attendu:**
```
✓ [exercise] MATH-6EME-ARITH-001 - Importé avec succès

==================================================
✓ Succès:    1
❌ Erreurs:   0
==================================================
```

**Voilà! C'est importé ✅**

---

## 📝 Template minimal

Copier/coller pour démarrer vite:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<exercises>
  <exercise>
    <Subject>Matière</Subject>
    <Level>6eme</Level>
    <Title>Titre du 1er exercice</Title>
    <Content><![CDATA[
      <p>Énoncé de l'exercice...</p>
    ]]></Content>
    <Answer><![CDATA[
      <p>Correction...</p>
    ]]></Answer>
    <Domain>Domaine pédagogique</Domain>
    <Competence>Compétence visée</Competence>
    <Difficulty>facile</Difficulty>
    <Identifier>MAT-6EME-DOM-001</Identifier>
  </exercise>
  
  <exercise>
    <Subject>Matière</Subject>
    <Level>6eme</Level>
    <Title>Titre du 2e exercice</Title>
    <Content><![CDATA[
      <p>...</p>
    ]]></Content>
    <Answer><![CDATA[
      <p>...</p>
    ]]></Answer>
    <Identifier>MAT-6EME-DOM-002</Identifier>
  </exercise>
  
  <!-- Ajouter d'autres exercices -->
</exercises>
```

---

## 🎓 Exemple complet - Français 6ème

Voir l'exemple en production:

📄 **Fichier:** `tools/data/francais_6eme.xml`

✅ **Status:** Importé avec succès (21 exercices)

---

## ⚠️ Erreurs courantes

| Erreur | Cause | Solution |
|--------|-------|----------|
| "Column not found: isactive" | Champ n'existe pas en base | **Ignoré** dans le script, normal |
| "Identifier déjà existant" | Doublon | Changer l'Identifier |
| "Invalid XML" | Syntaxe XML cassée | Valider le XML |
| Fichier non trouvé | Mauvais chemin | Vérifier le chemin complet |

---

## 🔍 Validation rapide

### Vérifier que le XML est valide

```bash
php -r "
\$xml = simplexml_load_file('tools/data/ma_matiere.xml');
echo 'Nombre d\'exercices: ' . count(\$xml->exercise) . PHP_EOL;
echo 'XML OK ✅';
"
```

### Vérifier les exercices en base

Après l'import:

```bash
# (Via MySQL ou phpMyAdmin)
SELECT COUNT(*) FROM Exercises WHERE Subject = 'Mathématiques' AND Level = '6eme';
```

---

## 📚 Ressources

| Doc | Contenu |
|-----|---------|
| `docs/XML_IMPORT_SYSTEM.md` | Guide complet |
| `docs/XML_FORMAT_SPECIFICATION.md` | Format détaillé |
| `docs/XML_TEST_RESULTS.md` | Résultats test |
| `tools/data/francais_6eme.xml` | Exemple réel |

---

## ✅ Checklist rapide

Avant chaque import:

- [ ] Fichier XML valide
- [ ] **Title** rempli pour tous les exercices
- [ ] **Identifier** rempli et unique
- [ ] Pas d'accents bizarres (UTF-8)
- [ ] Test dry-run réussi
- [ ] Pas de "Identifier déjà existant"
- [ ] Import réel réussi

---

## 🚀 Prochains pas

1. Créer votre fichier XML
2. Tester en `--dry-run`
3. Importer quand prêt
4. Vérifier les données en base
5. Documenter le fichier

---

**Besoin d'aide?** Consulter les documents détaillés dans `docs/`

**Dernière mise à jour:** 01-01-2026
