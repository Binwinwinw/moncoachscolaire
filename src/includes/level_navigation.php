<?php
/**
 * Génère la navigation entre niveaux pour les pages d'exercices, cours et remediation
 *
 * @param string $current_level Le niveau actuel (ex: 'Seconde', '6ème', 'Première', etc.)
 * @param string $page_type Type de page : 'exercices', 'cours', ou 'guide-remediation'
 * @return string HTML de la navigation
 */
if (!function_exists('render_level_navigation')) {
    function render_level_navigation($current_level, $page_type = 'exercices')
    {
        // Masquer la navigation pour tous sauf admin
        global $is_admin;
        if (empty($is_admin) || !$is_admin) {
            return '';
        }
        // Charger la normalisation si disponible (pour sécurité d'encodage)
        if (!function_exists('normalize_level_for_url') && is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
            require_once dirname(__DIR__, 2) . '/config/site_boot.php';
        }
        if (!function_exists('normalize_school_level') && is_file(dirname(__DIR__) . '/includes/level_normalization.php')) {
            require_once dirname(__DIR__) . '/includes/level_normalization.php';
        }
        // Définir l'ordre des niveaux
        $college_levels = ['6ème', '5ème', '4ème', '3ème'];
        $lycee_levels = ['Seconde', 'Première', 'Terminale'];
        $is_bac = ($current_level === 'BAC');

        // Déterminer le niveau précédent et suivant
        $prev_level = null;
        $next_level = null;
        $home_url = null;
        $home_label = null;

        if ($is_bac) {
            // Navigation pour BAC
            $prev_level = 'Terminale';
            $home_url = site_url('bac/bac-accueil');
            $home_label = '🏠 Accueil BAC';
        } elseif (in_array($current_level, $college_levels)) {
            // Navigation pour le collège
            $current_index = array_search($current_level, $college_levels);
            if ($current_index !== false) {
                if ($current_index > 0) {
                    $prev_level = $college_levels[$current_index - 1];
                }
                if ($current_index < count($college_levels) - 1) {
                    $next_level = $college_levels[$current_index + 1];
                } elseif ($current_level === '3ème') {
                    // Cas spécial : 3ème -> Seconde (transition collège -> lycée)
                    $next_level = 'Seconde';
                }
            }
            $home_url = site_url('college/college-accueil');
            $home_label = '🏠 Accueil Collège';
        } elseif (in_array($current_level, $lycee_levels)) {
            // Navigation pour le lycée
            $current_index = array_search($current_level, $lycee_levels);
            if ($current_index !== false) {
                if ($current_index > 0) {
                    $prev_level = $lycee_levels[$current_index - 1];
                } elseif ($current_index === 0) {
                    // Seconde : le niveau précédent est 3ème (collège)
                    $prev_level = '3ème';
                }
                // Ne pas définir $next_level ici pour Terminale car il sera géré dans le cas spécial Terminale -> BAC
                if ($current_index < count($lycee_levels) - 1 && $current_level !== 'Terminale') {
                    $next_level = $lycee_levels[$current_index + 1];
                }
            }
            $home_url = site_url('lycee/lycee-accueil');
            $home_label = '🏠 Accueil Lycée';
        }

        // Générer les URLs selon le type de page
        $prev_url = null;
        $next_url = null;

        // Charger la logique d'accès par niveau si disponible
        $canPrevAccess = true;
        $canNextAccess = true;
        if (is_file(dirname(__DIR__) . '/includes/level_access.php')) {
            require_once dirname(__DIR__) . '/includes/level_access.php';
            if ($prev_level) {
                $canPrevAccess = can_current_user_access_level($prev_level);
            }
            if ($next_level) {
                $canNextAccess = can_current_user_access_level($next_level);
            }
        }

        if ($prev_level) {
            $prev_normalized = normalize_level_for_url($prev_level);
            if (in_array($prev_level, $college_levels)) {
                if ($page_type === 'exercices') {
                    $prev_url = site_url('college/' . $prev_normalized . '/exercices-' . $prev_normalized);
                } elseif ($page_type === 'guide-remediation') {
                    $prev_url = site_url('college/' . $prev_normalized . '/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $prev_url = site_url('college/' . $prev_normalized . '/cours-' . $prev_normalized);
                }
            } elseif (in_array($prev_level, $lycee_levels)) {
                if ($page_type === 'exercices') {
                    $prev_url = site_url('lycee/' . $prev_normalized . '/exercices-' . $prev_normalized);
                } elseif ($page_type === 'guide-remediation') {
                    $prev_url = site_url('lycee/' . $prev_normalized . '/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $prev_url = site_url('lycee/' . $prev_normalized . '/cours-' . $prev_normalized);
                }
            } elseif ($prev_level === 'Terminale' && $is_bac) {
                // Cas spécial : BAC -> Terminale
                if ($page_type === 'exercices') {
                    $prev_url = site_url('lycee/terminale/exercices-terminale');
                } elseif ($page_type === 'guide-remediation') {
                    $prev_url = site_url('lycee/terminale/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $prev_url = site_url('lycee/terminale/cours-terminale');
                }
            } elseif ($prev_level === '3ème' && in_array($current_level, $lycee_levels)) {
                // Cas spécial : Seconde -> 3ème
                if ($page_type === 'exercices') {
                    $prev_url = site_url('college/3eme/exercices-3eme');
                } elseif ($page_type === 'guide-remediation') {
                    $prev_url = site_url('college/3eme/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $prev_url = site_url('college/3eme/cours-3eme');
                }
            }
        }

        // Cas spécial : Terminale -> BAC (doit être traité AVANT le bloc générique pour éviter les conflits)
        if ($current_level === 'Terminale' && !$is_bac) {
            // Terminale -> BAC (pour exercices, cours et guide-remediation)
            if ($page_type === 'exercices') {
                $next_url = site_url('bac/exercices-bac');
                $next_level = 'BAC';
            } elseif ($page_type === 'cours') {
                $next_url = site_url('cours', ['niveau' => 'bac']);
                $next_level = 'BAC';
            } elseif ($page_type === 'guide-remediation') {
                $next_url = site_url('bac/guide-remediation');
                $next_level = 'BAC';
            }
        } elseif ($next_level) {
            // Traitement générique pour les autres niveaux
            $next_normalized = normalize_level_for_url($next_level);
            // Déterminer correctement si le niveau suivant est au collège ou au lycée
            if (in_array($next_level, $college_levels)) {
                // Le niveau suivant est au collège
                if ($page_type === 'exercices') {
                    $next_url = site_url('college/' . $next_normalized . '/exercices-' . $next_normalized);
                } elseif ($page_type === 'guide-remediation') {
                    $next_url = site_url('college/' . $next_normalized . '/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $next_url = site_url('college/' . $next_normalized . '/cours-' . $next_normalized);
                }
            } elseif (in_array($next_level, $lycee_levels)) {
                // Le niveau suivant est au lycée
                if ($page_type === 'exercices') {
                    $next_url = site_url('lycee/' . $next_normalized . '/exercices-' . $next_normalized);
                } elseif ($page_type === 'guide-remediation') {
                    $next_url = site_url('lycee/' . $next_normalized . '/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $next_url = site_url('lycee/' . $next_normalized . '/cours-' . $next_normalized);
                }
            }
        }

        // Pour le BAC, pas de niveau suivant
        if ($is_bac) {
            $next_level = null;
            $next_url = null;
        }


        // Déterminer le label du niveau précédent
        $prev_label = null;
        if ($prev_level) {
            if ($prev_level === '3ème' && in_array($current_level, $lycee_levels)) {
                $prev_label = '◀️ 3ème';
            } else {
                $prev_label = '◀️ ' . $prev_level;
            }
        }

        // Déterminer le label du niveau suivant
        $next_label = null;
        if ($next_level) {
            if ($next_level === 'BAC') {
                $next_label = '▶️ BAC';
            } else {
                $next_label = $next_level . ' ▶️';
            }
        }

        // Déterminer le label de la section (Collège, Lycée ou BAC)
        $section_label = null;
        if (in_array($current_level, $college_levels)) {
            $section_label = 'Collège';
        } elseif (in_array($current_level, $lycee_levels)) {
            $section_label = 'Lycée';
        } elseif ($is_bac) {
            $section_label = 'BAC';
        }

        // Générer le HTML avec style amélioré inspiré de bac/cours-bac
        ob_start();
        ?>
        <div class="exercise-navigation" style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(255, 255, 255, 0.95); border-radius: 12px; border: 2px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
            <div class="nav-group" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <?php if ($prev_url && $prev_label): ?>
                    <?php if ($canPrevAccess): ?>
                        <a href="<?php echo htmlspecialchars($prev_url); ?>" class="nav-btn nav-prev" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);">
                            <?php echo htmlspecialchars($prev_label); ?>
                        </a>
                    <?php else: ?>
                        <span class="nav-btn nav-prev disabled" title="Niveau verrouillé" style="background: #f3f4f6; color: #9ca3af; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight:600; display:inline-flex; align-items:center; gap:0.5rem; border:2px solid #e5e7eb;">
                            🔒 <?php echo htmlspecialchars($prev_label); ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="nav-group">
                <?php if ($home_url && $home_label): ?>
                    <a href="<?php echo htmlspecialchars($home_url); ?>" class="nav-btn nav-home" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);">
                        <?php echo htmlspecialchars($home_label); ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="nav-group" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <?php if ($next_url && $next_label): ?>
                    <?php if ($canNextAccess): ?>
                        <a href="<?php echo htmlspecialchars($next_url); ?>" class="nav-btn nav-next" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);">
                            <?php echo htmlspecialchars($next_label); ?>
                        </a>
                    <?php else: ?>
                        <span class="nav-btn nav-next disabled" title="Niveau verrouillé" style="background: #f3f4f6; color: #9ca3af; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight:600; display:inline-flex; align-items:center; gap:0.5rem; border:2px solid #e5e7eb;">
                            <?php echo htmlspecialchars($next_label); ?> 🔒
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Génère la navigation entre les pages d'accueil (Collège+, Lycée+, BAC)
 *
 * @param string $current_accueil Type d'accueil actuel : 'college', 'lycee', ou 'bac'
 * @return string HTML de la navigation
 */
if (!function_exists('render_accueil_navigation')) {
    function render_accueil_navigation($current_accueil)
    {
        // Charger helpers de normalisation au besoin
        if (!function_exists('normalize_level_for_url') && is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
            require_once dirname(__DIR__, 2) . '/config/site_boot.php';
        }
        if (!function_exists('normalize_school_level') && is_file(dirname(__DIR__) . '/includes/level_normalization.php')) {
            require_once dirname(__DIR__) . '/includes/level_normalization.php';
        }

        // Déterminer les autres accueils disponibles
        $accueils = [
            'college' => [
                'url' => site_url('college/college-accueil'),
                'label' => '🎯 Collège+',
                'color' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                'shadow' => 'rgba(16, 185, 129, 0.3)',
            ],
            'lycee' => [
                'url' => site_url('lycee/lycee-accueil'),
                'label' => '🎓 Lycée+',
                'color' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
                'shadow' => 'rgba(59, 130, 246, 0.3)',
            ],
            'bac' => [
                'url' => site_url('bac/bac-accueil'),
                'label' => '🏆 Préparer le BAC',
                'color' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                'shadow' => 'rgba(245, 158, 11, 0.3)',
            ],
        ];

        // Règle d'affichage: pour un élève connecté, n'afficher QUE l'espace correspondant à son niveau
        // - Collège -> uniquement Collège+
        // - Lycée -> uniquement Lycée+
        // - BAC (si un jour utilisé comme niveau) -> uniquement BAC
        // Admins/Parents/Visiteurs: conservent la navigation complète

        $allowed_keys = array_keys($accueils);
        $is_admin = false;
        $is_parent = false;
        $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
        $user_level = $_SESSION['user_level'] ?? '';

        // Déterminer rôle admin/parent si possible sans casser l'inclusion
        if (function_exists('isAdmin')) {
            $is_admin = isAdmin();
        } else {
            $is_admin = (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
        }
        $is_parent = !empty($_SESSION['parent_id']);

        $is_student = $is_logged_in && !$is_admin && !$is_parent;

        if ($is_student && function_exists('is_college_level') && function_exists('is_lycee_level')) {
            if (is_college_level($user_level)) {
                // Élève collège : uniquement Collège+
                $allowed_keys = ['college'];
            } elseif (function_exists('levels_match') && levels_match($user_level, 'Terminale')) {
                // Élève Terminale : Lycée+ (actuel) + option BAC
                $allowed_keys = ['lycee', 'bac'];
            } elseif (is_lycee_level($user_level)) {
                // Élève Seconde ou Première : uniquement Lycée+
                $allowed_keys = ['lycee'];
            } elseif (function_exists('levels_match') && levels_match($user_level, 'BAC')) {
                // Élève BAC : uniquement BAC
                $allowed_keys = ['bac'];
            }
        }

        // Générer le HTML
        ob_start();
        ?>
        <div class="accueil-navigation-tool" style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(255, 255, 255, 0.9); border-radius: 12px; border: 2px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
            <div style="text-align: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; color: #1f2937; font-size: 1.1rem; font-weight: 600;">📚 Navigation entre les espaces</h3>
                <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.9rem;">Accédez rapidement aux autres niveaux scolaires</p>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; align-items: center;">
                <?php foreach ($accueils as $key => $accueil): ?>
                    <?php if (!in_array($key, $allowed_keys, true)) {
                        continue;
                    } ?>
                    <?php if ($key !== $current_accueil): ?>
                           <a href="<?php echo htmlspecialchars($accueil['url']); ?>"
                              class="nav-accueil-btn"
                              style="background: <?php echo htmlspecialchars($accueil['color']); ?>;
                                  color: white;
                                  padding: 0.75rem 1.5rem;
                                  border-radius: 8px;
                                  text-decoration: none;
                                  font-weight: 600;
                                  transition: all 0.3s ease;
                                  display: inline-flex;
                                  align-items: center;
                                  gap: 0.5rem;
                                  box-shadow: 0 4px 12px <?php echo htmlspecialchars($accueil['shadow']); ?>;">
                            <?php echo htmlspecialchars($accueil['label']); ?>
                        </a>
                    <?php else: ?>
                        <span style="background: #f3f4f6;
                                     color: #6b7280;
                                     padding: 0.75rem 1.5rem;
                                     border-radius: 8px;
                                     font-weight: 600;
                                     display: inline-flex;
                                     align-items: center;
                                     gap: 0.5rem;
                                     border: 2px solid #e5e7eb;
                                     cursor: default;">
                            <?php echo htmlspecialchars($accueil['label']); ?> (Actuel)
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
?>

