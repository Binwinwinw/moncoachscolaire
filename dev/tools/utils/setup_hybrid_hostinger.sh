#!/bin/bash
# ============================================================================
# DÉPLOIEMENT HYBRIDE - SYNCHRONISATION DES ASSETS
# Copie les assets vers la bonne structure en production
# ============================================================================

echo "════════════════════════════════════════════════════════════════════"
echo "SETUP HYBRIDE POUR HOSTINGER"
echo "════════════════════════════════════════════════════════════════════"
echo ""

# Variables
PROD_ROOT="/home/u936396612/domains/moncoachscolaire.fr/public_html"
LOCAL_ASSETS="./public/assets"

echo "📍 Configuration:"
echo "─────────────────"
echo "Production Root: $PROD_ROOT"
echo "Local Assets: $LOCAL_ASSETS"
echo ""

# Options de déploiement
echo "🔧 Options de déploiement:"
echo "──────────────────────────"
echo ""
echo "Option 1: Copier les assets à la racine (RECOMMANDÉ)"
echo "  Commande: cp -r ./public/assets /home/u936396612/domains/moncoachscolaire.fr/public_html/"
echo ""
echo "Option 2: Garder /public/assets et utiliser .htaccess"
echo "  Commande: Ajouter une règle RewriteRule dans .htaccess"
echo ""
echo "Option 3: Symlink (si autorisé par Hostinger)"
echo "  Commande: ln -s /home/u936396612/domains/.../public/assets /home/.../assets"
echo ""

echo "════════════════════════════════════════════════════════════════════"
echo "VÉRIFICATION DES FICHIERS CRITIQUES"
echo "════════════════════════════════════════════════════════════════════"
echo ""

# Vérifier que les assets existent
if [ -d "$LOCAL_ASSETS/css" ]; then
    echo "✅ CSS files found:"
    ls -1 "$LOCAL_ASSETS/css"/*.css 2>/dev/null | head -5
    echo ""
fi

if [ -d "$LOCAL_ASSETS/js" ]; then
    echo "✅ JavaScript files found:"
    ls -1 "$LOCAL_ASSETS/js"/*.js 2>/dev/null | head -5
    echo ""
fi

echo "════════════════════════════════════════════════════════════════════"
echo "À FAIRE EN PRODUCTION"
echo "════════════════════════════════════════════════════════════════════"
echo ""
echo "1. Vérifier la structure actuelle:"
echo "   $ ls -la /home/u936396612/domains/moncoachscolaire.fr/public_html/"
echo ""
echo "2. Copier les assets:"
echo "   $ cd /home/u936396612/domains/moncoachscolaire.fr/public_html"
echo "   $ cp -r public/assets . "
echo ""
echo "3. Tester les URLs:"
echo "   $ curl https://moncoachscolaire.fr/assets/css/style.css | head -20"
echo "   $ curl https://moncoachscolaire.fr/assets/js/interactive-exercises.js | head -5"
echo ""
