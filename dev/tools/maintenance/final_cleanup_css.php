<?php
/**
 * Nettoyage final : suppression des règles CSS orphelines et incomplètes
 */

$cssFile = 'assets/css/style.css';
$content = file_get_contents($cssFile);

// Supprimer les règles orphelines avec sélecteurs vides
// Pattern: .className suivi de rien (juste un retour à la ligne ou espace)
$content = preg_replace('/\.(modern-landing|hero-modern|card-feature|card-features|features-modern|container-modern|parcours-modern)\s*\{[^}]*\}/m', '', $content);

// Supprimer les sélecteurs avec attributs data-color qui sont incomplets
$content = preg_replace('/\.card-feature\[data-color="[^"]+"\]\s*\{[^}]*\}\s*\.card-feature\[data-color="[^"]+"\]\s*\{[^}]*\}/m', '', $content);

// Supprimer les lignes vides multiples
$content = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $content);

// Supprimer les règles dans les media queries qui sont vides
$content = preg_replace('/@media[^{]*\{\s*\n\s*\.(modern-landing|hero-modern|features-modern|parcours-modern|container-modern)[^}]*\}/m', '', $content);

// Nettoyer les media queries vides
$content = preg_replace('/@media[^{]*\{\s*\}/m', '', $content);

// Nettoyer les lignes vides multiples après nettoyage
$content = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $content);

file_put_contents($cssFile, $content);
echo "✅ Nettoyage final terminé pour $cssFile\n";

