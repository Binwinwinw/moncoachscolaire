# 🔄 Configuration Synchronisation: moncoachscolaire (local) → u936396612_mcoachscolaire (prod)

## 🎯 Situation Actuelle

```
BD Locale:      moncoachscolaire (sur localhost)
BD Production:  u936396612_mcoachscolaire (sur serveur Hostinger)
```

**Problème:** La BD production n'est PAS accessible depuis votre machine locale.

---

## ✅ 3 Solutions Possibles

### Option 1: Export/Import Manuel (RECOMMANDÉ - Le Plus Sûr)

Cette méthode utilise des fichiers SQL pour transférer les données.

#### Étape 1: Exporter les données locales

```bash
# Exporter UNIQUEMENT les nouvelles données (pas toute la BD)
php tools/export_new_data.php
```

Créons ce script:

```php
<?php
// tools/export_new_data.php
$pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire;charset=utf8mb4', 'root', '');

// Exporter les exercices
$stmt = $pdo->query("SELECT * FROM Exercises ORDER BY Id");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents('export_exercises.sql', 
    "-- Exercices à importer\n" .
    "-- IMPORTANT: Vérifier qu'il n'y a pas de doublons avant d'importer!\n\n"
);

foreach ($exercises as $ex) {
    $title = addslashes($ex['Title']);
    $subject = addslashes($ex['Subject']);
    $level = addslashes($ex['Level']);
    $content = addslashes($ex['Content']);
    $answer = addslashes($ex['Answer']);
    $is_active = $ex['is_active'] ?? 1;
    
    $sql = "INSERT IGNORE INTO Exercises (Subject, Level, Title, Content, Answer, is_active) 
            VALUES ('$subject', '$level', '$title', '$content', '$answer', $is_active);\n";
    
    file_put_contents('export_exercises.sql', $sql, FILE_APPEND);
}

echo "✅ Export créé: export_exercises.sql\n";
echo "   Exercices: " . count($exercises) . "\n";
```

#### Étape 2: Uploader sur Hostinger

Via **phpMyAdmin** sur Hostinger:

1. Se connecter à phpMyAdmin
2. Sélectionner la BD `u936396612_mcoachscolaire`
3. Onglet "Importer"
4. Choisir `export_exercises.sql`
5. Cliquer "Exécuter"

---

### Option 2: Connexion SSH + MySQL Distant (Si SSH Activé)

Si vous avez accès SSH à Hostinger:

```bash
# Se connecter en SSH
ssh u936396612@votredomaine.com

# Sur le serveur, importer depuis local
mysql -u u936396612_user -p u936396612_mcoachscolaire < export_exercises.sql
```

---

### Option 3: API de Synchronisation (AVANCÉ)

Créer une API sur votre serveur Hostinger qui accepte les données et les insère.

**Fichier sur Hostinger:** `public_html/moncoachscolaire/api/sync_import.php`

```php
<?php
// Sécurité: vérifier un token secret
if (!isset($_POST['sync_token']) || $_POST['sync_token'] !== 'votre_token_secret_123') {
    die(json_encode(['error' => 'Unauthorized']));
}

require_once '../db/connection.php';

$exercises = json_decode($_POST['exercises'], true);
$imported = 0;

foreach ($exercises as $ex) {
    $stmt = $pdo->prepare("
        INSERT INTO Exercises (Subject, Level, Title, Content, Answer, is_active)
        SELECT :subject, :level, :title, :content, :answer, :is_active
        WHERE NOT EXISTS (
            SELECT 1 FROM Exercises 
            WHERE Title = :title AND Subject = :subject AND Level = :level
        )
    ");
    
    $stmt->execute([
        'subject' => $ex['Subject'],
        'level' => $ex['Level'],
        'title' => $ex['Title'],
        'content' => $ex['Content'],
        'answer' => $ex['Answer'],
        'is_active' => $ex['is_active'] ?? 1
    ]);
    
    if ($stmt->rowCount() > 0) $imported++;
}

echo json_encode(['success' => true, 'imported' => $imported]);
```

**Script local pour envoyer:**

```php
<?php
// tools/sync_via_api.php
$pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire;charset=utf8mb4', 'root', '');

$exercises = $pdo->query("SELECT * FROM Exercises")->fetchAll(PDO::FETCH_ASSOC);

$ch = curl_init('https://votredomaine.com/moncoachscolaire/api/sync_import.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'sync_token' => 'votre_token_secret_123',
    'exercises' => json_encode($exercises)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
echo $result;
```

---

## 🚀 MÉTHODE RECOMMANDÉE: Export Manuel

Voici le workflow complet:

### Étape 1: Créer le script d'export

```php
<?php
// tools/export_for_production.php

$pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function escapeValue($value) {
    return addslashes($value);
}

// Créer le fichier SQL
$output = "-- ═══════════════════════════════════════════════════════════════\n";
$output .= "-- 🔄 SYNCHRONISATION: moncoachscolaire → u936396612_mcoachscolaire\n";
$output .= "-- Généré le: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- ═══════════════════════════════════════════════════════════════\n\n";

$output .= "USE `u936396612_mcoachscolaire`;\n\n";

// Exporter les exercices
$stmt = $pdo->query("SELECT * FROM Exercises ORDER BY Id");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$output .= "-- ═══════════════════════════════════════════════════════════════\n";
$output .= "-- EXERCICES (" . count($exercises) . " total)\n";
$output .= "-- ═══════════════════════════════════════════════════════════════\n\n";

foreach ($exercises as $ex) {
    $subject = escapeValue($ex['Subject']);
    $level = escapeValue($ex['Level']);
    $title = escapeValue($ex['Title']);
    $content = escapeValue($ex['Content']);
    $answer = escapeValue($ex['Answer']);
    $is_active = $ex['is_active'] ?? 1;
    
    $output .= "INSERT IGNORE INTO `Exercises` (`Subject`, `Level`, `Title`, `Content`, `Answer`, `is_active`) VALUES\n";
    $output .= "  ('$subject', '$level', '$title', '$content', '$answer', $is_active);\n";
}

// Sauvegarder
file_put_contents('export_for_hostinger.sql', $output);

echo "✅ Export créé: export_for_hostinger.sql\n";
echo "   Taille: " . round(filesize('export_for_hostinger.sql') / 1024, 2) . " KB\n";
echo "   Exercices: " . count($exercises) . "\n\n";
echo "📋 PROCHAINE ÉTAPE:\n";
echo "   1. Uploader export_for_hostinger.sql via FTP/SFTP\n";
echo "   2. Importer dans phpMyAdmin sur Hostinger\n";
echo "   3. Vérifier les données dans la BD prod\n";
?>
```

### Étape 2: Exécuter l'export

```bash
php tools/export_for_production.php
```

### Étape 3: Uploader et importer

1. **Via phpMyAdmin sur Hostinger:**
   - Connexion: https://hpanel.hostinger.com
   - Bases de données → phpMyAdmin
   - Sélectionner `u936396612_mcoachscolaire`
   - Importer → Choisir `export_for_hostinger.sql`
   - Exécuter

2. **Vérifier:**
   ```sql
   SELECT COUNT(*) FROM Exercises;
   SELECT COUNT(*) FROM Users;
   ```

---

## 📊 Résumé

```
Situation:
  Local:  moncoachscolaire (localhost, accessible)
  Prod:   u936396612_mcoachscolaire (Hostinger, non accessible directement)

Solution Simple:
  1. Exporter données locales → fichier SQL
  2. Uploader fichier sur Hostinger
  3. Importer via phpMyAdmin

Avantages:
  ✅ Ne nécessite pas de connexion SSH
  ✅ Facile à vérifier avant d'importer
  ✅ Peut annuler facilement (pas d'import = pas de changement)
  ✅ Logs visibles dans phpMyAdmin
```

---

**Voulez-vous que je crée le script `export_for_production.php` complet?**
