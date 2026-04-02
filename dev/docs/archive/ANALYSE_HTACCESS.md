# ANALYSE COMPLÈTE DU FICHIER .htaccess
# Recherche de problèmes potentiels pour la production
# Date: 2025-12-31

## 1. RÈGLES AVEC CHEMINS ABSOLUS (PROBLÉMATIQUES)

### ❌ PROBLÈME TROUVÉ - Ligne 150-157
```apache
# Router les appels API vers src/api - AVANT les alias d'assets
RewriteCond %{ENV:LOCAL_ENV} == 1
RewriteRule ^api/(.*)$ /moncoachscolaire/src/api/$1 [PT,L,QSA]

# Production: router sans le préfixe /moncoachscolaire/
RewriteCond %{ENV:LOCAL_ENV} != 1
RewriteRule ^api/(.*)$ /src/api/$1 [PT,L,QSA]
```
**STATUT**: ✅ CORRIGÉ (règle hybride en place)
**SOLUTION**: Règle conditionnelle selon LOCAL_ENV

---

## 2. DÉTECTION D'ENVIRONNEMENT

### ✅ BON - Lignes 13-19
```apache
<IfModule mod_setenvif.c>
  SetEnvIf Host "^localhost" LOCAL_ENV=1
  SetEnvIf Host "^127\.0\.0\.1" LOCAL_ENV=1
  SetEnvIf Host "\.local$" LOCAL_ENV=1
  SetEnvIf Host "^dev\." LOCAL_ENV=1
  SetEnvIf Host "\.test$" LOCAL_ENV=1
</IfModule>
```
**STATUT**: ✅ Correct
**EXPLICATION**: Détecte automatiquement l'environnement local

### ⚠️ ATTENTION - Lignes 26-42
```apache
RewriteCond %{DOCUMENT_ROOT}/src/config/.env.production -f [OR]
RewriteCond %{DOCUMENT_ROOT}/.env.production -f
RewriteRule .* - [E=APP_ENV:production,E=LOCAL_ENV:0]

RewriteCond %{DOCUMENT_ROOT}/src/config/.env -f [OR]
RewriteCond %{DOCUMENT_ROOT}/.env -f
RewriteRule .* - [E=LOCAL_ENV:1,E=APP_ENV:local]
```
**STATUT**: ⚠️ Peut causer confusion si les deux fichiers existent
**RECOMMANDATION**: La logique est correcte (production priorisé), mais vérifier que .env.production existe bien en production

---

## 3. RÈGLES DE ROUTAGE

### ✅ BON - Ligne 159-164 (Assets directs)
```apache
RewriteRule ^public/assets/(.*)$ public/assets/$1 [L,END]
RewriteRule ^public/img/(.*)$ public/img/$1 [L,END]
```
**STATUT**: ✅ Correct - Chemins relatifs, pas de hardcoding

### ✅ BON - Ligne 166-170 (Alias assets)
```apache
RewriteRule ^assets/(.*)$ public/assets/$1 [L]
RewriteRule ^img/(.*)$ public/img/$1 [L]
```
**STATUT**: ✅ Correct - Permettent /assets/ ET /public/assets/

---

## 4. SÉCURITÉ

### ✅ BON - Ligne 78-85 (Blocage fichiers sensibles)
```apache
<FilesMatch "\.(env|ini|log|sh|bak|sql|htaccessbak)$">
  <If "%{ENV:LOCAL_ENV} != '1'">
    Require all denied
  </If>
</FilesMatch>
```
**STATUT**: ✅ Correct - Bloque en production uniquement

### ✅ BON - Ligne 172-174 (Blocage dossiers sensibles)
```apache
RewriteCond %{ENV:LOCAL_ENV} !=1
RewriteCond %{REQUEST_URI} ^/(\.git|\.vscode|\.assistant|backups|dev|docs|tools|tests|includes|db)/ [NC]
RewriteRule .* - [F,L]
```
**STATUT**: ✅ Correct - Protection production uniquement

---

## 5. PROBLÈMES POTENTIELS À SURVEILLER

### ⚠️ ATTENTION - Routage final (Lignes 189-198)
```apache
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/public/
RewriteCond %{REQUEST_URI} !^/src/
RewriteCond %{REQUEST_URI} !^/assets/
```
**PROBLÈME POTENTIEL**: Ces conditions pourraient bloquer certains chemins en production
**TEST REQUIS**: Vérifier que les API passent bien par ces règles

### ❌ PROBLÈME POSSIBLE - Flag [END] vs [L]
```apache
RewriteRule ^public/assets/(.*)$ public/assets/$1 [L,END]
```
**ATTENTION**: Le flag `[END]` n'existe pas sur tous les serveurs Apache
**SOLUTION**: Si erreur 500, remplacer `[L,END]` par `[L]` uniquement

---

## 6. CHEMINS À VÉRIFIER EN PRODUCTION

### Checklist des URLs critiques:
- [ ] `/api/admin/stats.php` → doit router vers `/src/api/admin/stats.php`
- [ ] `/assets/css/style.css` → doit servir `/public/assets/css/style.css`
- [ ] `/public/assets/css/style.css` → doit servir directement
- [ ] `/.env.production` → doit être BLOQUÉ (403)
- [ ] `/tools/` → doit être BLOQUÉ (403)
- [ ] `/index.php?page=dashboard` → doit fonctionner

---

## 7. RECOMMANDATIONS POUR ÉVITER FUTURS PROBLÈMES

### 1. Tests automatiques à créer
```bash
# Script de test pour vérifier les règles .htaccess
curl -I https://moncoachscolaire.fr/api/admin/stats.php
curl -I https://moncoachscolaire.fr/assets/css/style.css
curl -I https://moncoachscolaire.fr/.env.production
```

### 2. Validation avant déploiement
- ✅ Tester localement d'abord
- ✅ Vérifier les logs Apache après déploiement
- ✅ Tester les URLs critiques manuellement

### 3. Documentation
- Maintenir une liste des endpoints API
- Documenter la structure des assets
- Noter les différences local/production

---

## 8. POINTS SENSIBLES IDENTIFIÉS

### 🔴 CRITIQUE
1. **Routage API**: Dépend de LOCAL_ENV correctement détecté
2. **Fichiers .env**: Doivent être dans DOCUMENT_ROOT, pas dans parent

### 🟡 IMPORTANT
1. **Flag [END]**: Peut ne pas fonctionner sur Apache < 2.3.9
2. **Ordre des règles**: API AVANT assets (déjà correct)

### 🟢 BON
1. Détection automatique environnement
2. Sécurité conditionnelle (local vs prod)
3. Support chemins multiples pour assets

---

## 9. TESTS DE VALIDATION

### Test 1: Environnement détecté correctement
```
LOCAL: SetEnvIf Host "localhost" → LOCAL_ENV=1
PROD: SetEnvIf Host "moncoachscolaire.fr" → LOCAL_ENV=0
```

### Test 2: Routage API
```
LOCAL: /api/admin/stats.php → /moncoachscolaire/src/api/admin/stats.php
PROD: /api/admin/stats.php → /src/api/admin/stats.php
```

### Test 3: Assets
```
/assets/css/style.css → /public/assets/css/style.css (les deux envs)
/public/assets/css/style.css → sert directement (les deux envs)
```

---

## 10. CONCLUSION

### ✅ Correctif appliqué
- Routage API hybride implémenté

### ⚠️ À surveiller
- Vérifier flag [END] si erreur 500
- S'assurer .env.production existe en production

### 📋 Actions recommandées
1. Tester toutes les URLs critiques après déploiement
2. Vérifier logs Apache pour erreurs
3. Créer script de validation automatique

### 🎯 Score de compatibilité
- Local: 10/10 ✅
- Production: 9/10 ⚠️ (vérifier flag [END])

---

**Date d'analyse**: 2025-12-31
**Fichier**: .htaccess (317 lignes)
**Environnements**: Local (XAMPP) + Production (Hostinger/LiteSpeed)
