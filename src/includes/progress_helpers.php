<?php

/**
 * Helpers pour le calcul de niveau, position, XP, etc.
 * Compatible avec le système de gamification existant
 */

if (!function_exists('getUserLevel')) {
    require_once __DIR__ . '/gamification.php';
}

/**
 * Calcule le niveau utilisateur basé sur l'XP
 * @param int $xp Points d'expérience
 * @return int Niveau (1-10)
 */
function calculateUserLevel($xp)
{
    $levels = [
        1 => 0,
        2 => 100,
        3 => 300,
        4 => 600,
        5 => 1000,
        6 => 1500,
        7 => 2100,
        8 => 2800,
        9 => 3600,
        10 => 4500,
    ];

    $level = 1;
    foreach ($levels as $lvl => $minXP) {
        if ($xp >= $minXP) {
            $level = $lvl;
        } else {
            break;
        }
    }

    return $level;
}

/**
 * Retourne le nom du niveau
 * @param int $level Niveau (1-10)
 * @return string Nom du niveau
 */
function getLevelName($level)
{
    $names = [
        1 => 'Apprenti Scientifique',
        2 => 'Chercheur Junior',
        3 => 'Expérimentateur',
        4 => 'Docteur en Sciences',
        5 => 'Professeur',
        6 => 'Maître de Laboratoire',
        7 => 'Directeur de Recherche',
        8 => 'Génie Scientifique',
        9 => 'Légende du Labo',
        10 => 'Génie Immortel',
    ];

    return $names[$level] ?? 'Apprenti Scientifique';
}

/**
 * Calcule la position sur le plateau de jeu (1-64 cases)
 * @param int $xp Points d'expérience
 * @return int Position (1-64)
 */
function calculateGamePosition($xp)
{
    // 64 cases sur le plateau
    // Position = min(64, XP / 100)
    $position = min(64, max(1, floor($xp / 10) + 1));
    return $position;
}

/**
 * Calcule l'XP nécessaire pour le prochain niveau
 * @param int $currentLevel Niveau actuel
 * @return int XP nécessaire
 */
function getXPForNextLevel($currentLevel)
{
    $levels = [
        1 => 100,
        2 => 300,
        3 => 600,
        4 => 1000,
        5 => 1500,
        6 => 2100,
        7 => 2800,
        8 => 3600,
        9 => 4500,
        10 => PHP_INT_MAX,
    ];

    return $levels[$currentLevel] ?? PHP_INT_MAX;
}

/**
 * Calcule le pourcentage de progression vers le prochain niveau
 * @param int $currentXP XP actuel
 * @param int $currentLevel Niveau actuel
 * @return float Pourcentage (0-100)
 */
function getProgressPercentage($currentXP, $currentLevel)
{
    $xpForNext = getXPForNextLevel($currentLevel);
    $xpForCurrent = getXPForNextLevel($currentLevel - 1);
    $xpInLevel = $currentXP - $xpForCurrent;
    $xpNeeded = $xpForNext - $xpForCurrent;

    if ($xpNeeded <= 0) {
        return 100;
    }

    return min(100, max(0, ($xpInLevel / $xpNeeded) * 100));
}

/**
 * Récupère le nombre de pouvoirs débloqués
 * @param int $userId ID utilisateur
 * @return int Nombre de pouvoirs
 */
function getUnlockedPowersCount($userId)
{
    global $pdo;

    if (!$pdo) {
        return 0;
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM UserPowers WHERE UserId = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    } catch (Exception $e) {
        error_log("Erreur comptage pouvoirs: " . $e->getMessage());
        return 0;
    }
}

/**
 * Récupère le total de pouvoirs disponibles
 * @return int Total de pouvoirs
 */
function getTotalPowersCount()
{
    global $pdo;

    if (!$pdo) {
        return 8;
    } // Par défaut

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Powers");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 8);
    } catch (Exception $e) {
        return 8;
    }
}
