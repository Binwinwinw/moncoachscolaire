<?php
/**
 * Composant PHP pour afficher la mascotte Colibri
 *
 * Usage:
 * require_once __DIR__ . '/colibri_mascot.php';
 * renderColibriMascot(['pose' => 'heureux', 'size' => 'medium']);
 */

/**
 * Affiche la mascotte Colibri
 *
 * @param array $options Options d'affichage
 *   - pose: string - Pose du colibri (neutre, heureux, encourageant, celebration, reflexion)
 *   - size: string - Taille (small, medium, large)
 *   - position: string - Position (inline, center, float)
 *   - message: string - Message à afficher dans la bulle
 *   - containerClass: string - Classe CSS supplémentaire pour le container
 *   - id: string - ID HTML pour le container
 *   - imageType: string - Type d'image (auto, cartoon, realiste)
 */
function renderColibriMascot($options = [])
{
    global $user_level;

    $defaultOptions = [
        'pose' => 'neutre',
        'size' => 'medium',
        'position' => 'inline',
        'message' => null,
        'containerClass' => '',
        'id' => 'colibri-mascot-' . uniqid(),
        'imageType' => 'auto',
    ];

    $options = array_merge($defaultOptions, $options);

    // Déterminer le type d'image si auto
    if ($options['imageType'] === 'auto' && !empty($user_level)) {
        $college_levels = ['6ème', '5ème', '4ème', '3ème'];
        $lycee_levels = ['Seconde', 'Première', 'Terminale'];

        if (in_array($user_level, $college_levels)) {
            $options['imageType'] = 'cartoon';
        } elseif (in_array($user_level, $lycee_levels) || $user_level === 'BAC') {
            $options['imageType'] = 'realiste';
        }
    }

    $containerClass = 'colibri-mascot-container ' . $options['containerClass'];
    $dataAttributes = '';
    $dataAttributes .= ' data-colibri="true"';
    $dataAttributes .= ' data-colibri-pose="' . htmlspecialchars($options['pose']) . '"';
    $dataAttributes .= ' data-colibri-size="' . htmlspecialchars($options['size']) . '"';
    $dataAttributes .= ' data-colibri-position="' . htmlspecialchars($options['position']) . '"';
    $dataAttributes .= ' data-colibri-image="' . htmlspecialchars($options['imageType']) . '"';

    if ($options['message']) {
        $dataAttributes .= ' data-colibri-message="' . htmlspecialchars($options['message']) . '"';
    }

    ?>
    <div id="<?php echo htmlspecialchars($options['id']); ?>" 
         class="<?php echo htmlspecialchars($containerClass); ?>"
         <?php echo $dataAttributes; ?>>
        <!-- La mascotte sera créée par JavaScript -->
    </div>
    <?php
}

/**
 * Affiche la mascotte globale qui vole sur toutes les pages
 *
 * @param array $options Options d'affichage
 */
function renderGlobalColibriMascot($options = [])
{
    global $user_level, $is_logged_in;

    // Ne pas afficher si l'utilisateur n'est pas connecté
    if (!$is_logged_in) {
        return;
    }

    $defaultOptions = [
        'size' => 'medium',
        'imageType' => 'auto',
    ];

    $options = array_merge($defaultOptions, $options);

    // Déterminer le type d'image si auto
    if ($options['imageType'] === 'auto' && !empty($user_level)) {
        $college_levels = ['6ème', '5ème', '4ème', '3ème'];
        $lycee_levels = ['Seconde', 'Première', 'Terminale'];

        if (in_array($user_level, $college_levels)) {
            $options['imageType'] = 'cartoon';
        } elseif (in_array($user_level, $lycee_levels) || $user_level === 'BAC') {
            $options['imageType'] = 'realiste';
        }
    }

    ?>
    <div class="colibri-mascot-global" 
         data-colibri-global="true"
         data-colibri-size="<?php echo htmlspecialchars($options['size']); ?>"
         data-colibri-image="<?php echo htmlspecialchars($options['imageType']); ?>">
        <!-- La mascotte sera créée par JavaScript -->
    </div>
    <?php
}

/**
 * Retourne le HTML pour la mascotte avec vidéo (alternative aux sprites)
 *
 * @param array $options Options
 *   - video: string - Type de vidéo (cartoon ou realiste)
 *   - autoplay: bool - Lecture automatique
 *   - loop: bool - Boucle
 *   - muted: bool - Son désactivé
 */
function renderColibriMascotVideo($options = [])
{
    $defaultOptions = [
        'video' => 'cartoon', // ou 'realiste'
        'autoplay' => true,
        'loop' => true,
        'muted' => true,
        'size' => 'medium',
        'containerClass' => '',
    ];

    $options = array_merge($defaultOptions, $options);

    $videoFile = $options['video'] === 'realiste'
        ? 'assets/img/coach/video-1765695500092.mp4'
        : 'assets/img/coach/video-1765695483376.mp4';

    $containerClass = 'colibri-mascot colibri-mascot-video size-' . $options['size'] . ' ' . $options['containerClass'];

    ?>
    <div class="<?php echo htmlspecialchars($containerClass); ?>">
        <video <?php echo $options['autoplay'] ? 'autoplay' : ''; ?> 
               <?php echo $options['loop'] ? 'loop' : ''; ?> 
               <?php echo $options['muted'] ? 'muted' : ''; ?> 
               playsinline
               class="colibri-video">
            <source src="<?php echo htmlspecialchars($videoFile); ?>" type="video/mp4">
            Votre navigateur ne supporte pas la vidéo HTML5.
        </video>
    </div>
    <?php
}

/**
 * Affiche la mascotte dans un feedback d'exercice
 *
 * @param string $type Type de feedback (success, error, thinking)
 * @param string $message Message optionnel
 */
function renderColibriInFeedback($type = 'success', $message = null)
{
    $poseMap = [
        'success' => 'celebration',
        'error' => 'encourageant',
        'thinking' => 'reflexion',
    ];

    $defaultMessages = [
        'success' => 'Bravo ! Continue comme ça ! 🎉',
        'error' => 'Pas grave, réessaie ! 💪',
        'thinking' => 'Réfléchis bien... 🤔',
    ];

    $pose = $poseMap[$type] ?? 'neutre';
    $msg = $message ?? ($defaultMessages[$type] ?? null);

    renderColibriMascot([
        'pose' => $pose,
        'size' => 'small',
        'position' => 'inline',
        'message' => $msg,
        'containerClass' => 'colibri-feedback-mascot',
    ]);
}
