# Correction de l''encodage UTF-8

## Problème résolu
Les fichiers PHP contenaient des caractères mal encodés (é affichés comme Ã, etc.) car ils étaient en Windows-1252/ISO-8859-1 au lieu d''UTF-8.

## Actions effectuées

### 1. Conversion de tous les fichiers source en UTF-8
- **97 fichiers PHP** dans `src/` convertis en UTF-8 sans BOM
- Correction des caractères mal encodés (Ã  é, Ã¨  è, etc.)
- Script disponible : `fix_all_encoding.ps1`

### 2. Configuration PHP pour UTF-8
Ajout dans `src/config/site_boot.php` :
```php
mb_internal_encoding(''UTF-8'');
mb_http_output(''UTF-8'');
header(''Content-Type: text/html; charset=UTF-8'');
```

### 3. EditorConfig créé
Fichier `.editorconfig` créé à la racine pour forcer UTF-8 dans tous les éditeurs compatibles.

## Prévention future

### Dans VS Code
Vérifier dans les paramètres :
- `Files: Encoding` = `utf8`
- `Files: Auto Guess Encoding` = activé

### Scripts disponibles
- **fix_all_encoding.ps1** : Nettoie tous les fichiers PHP de src/
- **fix_encoding.ps1** : Version simple (deprecated)

## Utilisation

### Pour nettoyer à nouveau si nécessaire :
```powershell
& ''D:\Hostinger\public_html\moncoachscolaire\fix_all_encoding.ps1''
```

### Vérifier un fichier spécifique :
```powershell
Get-Content fichier.php -Encoding UTF8 | Select-String "Ã"
```

## Date de correction
2 janvier 2026
