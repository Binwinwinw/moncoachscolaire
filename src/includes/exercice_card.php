<?php
/**
 * Composant d'affichage d'une carte d'exercice
 *
 * Usage:
 * require_once __DIR__ . '/exercice_card.php';
 * renderExerciseCard($exercise, ['showAnswer' => false]);
 */

/**
 * Affiche une carte d'exercice
 *
 * @param array $exercise Données de l'exercice (depuis la DB)
 * @param array $options Options d'affichage
 *   - showAnswer: bool - Afficher la correction (default: false)
 *   - showDetails: bool - Afficher les détails complets (default: true)
 *   - cardClass: string - Classes CSS supplémentaires
 */
function renderExerciseCard($exercise, $options = [])
{
    $defaultOptions = [
        'showAnswer' => false,
        'showDetails' => true,
        'cardClass' => '',
    ];
    $options = array_merge($defaultOptions, $options);

    if (!$exercise || empty($exercise['Title'])) {
        return;
    }

    // Gestion robuste de la casse de l'ID (Id/id)
    $id = $exercise['Id'] ?? $exercise['id'] ?? 0;
    $title = htmlspecialchars($exercise['Title'] ?? '');
    $content = $exercise['Content'] ?? '';
    $answer = $exercise['Answer'] ?? '';
    $subject = htmlspecialchars($exercise['Subject'] ?? '');
    $level = htmlspecialchars($exercise['Level'] ?? '');

    // Densité UI selon l'âge (primaire/college/lycee)
    $levelRaw = mb_strtolower(trim($exercise['Level'] ?? ''), 'UTF-8');
    $ageClass = 'ui-age-college';
    if (preg_match('/(seconde|2nde|premi|1ere|terminale|bac)/i', $levelRaw)) {
        $ageClass = 'ui-age-lycee';
    } elseif (preg_match('/(6|5|4|3)/', $levelRaw)) {
        $ageClass = 'ui-age-college';
    }

    // Vérifier si l'exercice est accessible pour l'utilisateur
    $is_locked = false;
    if (is_file(dirname(__DIR__) . '/src/includes/level_access.php')) {
        // NOTE: path depends on include location when called from different scripts; try common paths
        if (is_file(__DIR__ . '/level_access.php')) {
            require_once __DIR__ . '/level_access.php';
        } elseif (is_file(dirname(__DIR__) . '/includes/level_access.php')) {
            require_once dirname(__DIR__) . '/includes/level_access.php';
        }
    }
    if (function_exists('can_current_user_access_level')) {
        $is_locked = !can_current_user_access_level($exercise['Level'] ?? $exercise['level'] ?? '');
    }

    // Classes CSS
    $cardClass = 'exercise-card exercise-card-ui ' . $ageClass . ' ' . $options['cardClass'];
    if ($options['showAnswer']) {
        $cardClass .= ' answer-visible';
    }
    if ($is_locked) {
        $cardClass .= ' locked';
    }

    // Icônes SVG et couleurs selon la matière
    $subject_key = mb_strtolower(trim($subject), 'UTF-8');
    $subject_icons = [
        'mathématiques' => ['svg' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 4.5C3 3.67 3.67 3 4.5 3h15c.83 0 1.5.67 1.5 1.5v15c0 .83-.67 1.5-1.5 1.5H4.5A1.5 1.5 0 0 1 3 19.5v-15z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 8h10M7 12h10M7 16h10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'color' => '#6366F1'],
        'français' => ['svg' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'color' => '#EF4444'],
        'svt' => ['svg' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v20M2 12h20" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'color' => '#059669'],
        'physique-chimie' => ['svg' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2c3 0 5 2 5 5 0 3-2 5-5 5s-5-2-5-5c0-3 2-5 5-5z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 21s4-4 9-4 9 4 9 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'color' => '#0EA5A4'],
        'anglais' => ['svg' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 12h20M12 2v20" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'color' => '#F59E0B'],
    ];
    $default_icon = ['svg' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.2"/><path d="M7 8h10M7 12h6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>', 'color' => '#64748B'];
    $si = $subject_icons[$subject_key] ?? $default_icon;
    $subjectIcon = $si['svg'];
    $subjectColor = $si['color'];

    // Déterminer la difficulté selon le niveau
    $difficulty = 'moyen'; // Par défaut
    if (stripos($title, 'facile') !== false || stripos($title, 'débutant') !== false) {
        $difficulty = 'facile';
    } elseif (stripos($title, 'difficile') !== false || stripos($title, 'expert') !== false) {
        $difficulty = 'difficile';
    }

    ?>
    <article id="exercise-<?php echo $id; ?>" class="<?php echo $cardClass; ?>" role="article" aria-labelledby="exercise-title-<?php echo $id; ?>" tabindex="0"
         data-exercise-id="<?php echo $id; ?>"
         data-difficulty="<?php echo htmlspecialchars($difficulty); ?>"
         data-subject="<?php echo htmlspecialchars($subject); ?>">
        <header class="exercise-header" role="heading" aria-level="3">
            <div class="exercise-title-section">
                <span class="exercise-icon" style="color: <?php echo $subjectColor ?? '#64748B'; ?>; display: inline-block; width: 48px; height: 48px; max-width: 100%;" aria-hidden="true">
                    <?php echo preg_replace('/<svg /', '<svg style="width:48px;height:48px;max-width:100%;" ', $subjectIcon); ?>
                </span>
                <h3 id="exercise-title-<?php echo $id; ?>" class="exercise-title">
                        <span class="exercise-number">#<?php echo htmlspecialchars($id); ?></span>
                        <?php echo $title; ?>
                </h3>
            </div>
            <div class="exercise-meta" aria-hidden="false">
                <span class="exercice-badge exercise-level"><?= htmlspecialchars($level) ?></span>
                <?php if ($is_locked): ?>
                    <span class="exercice-badge exercise-locked" title="Niveau verrouillé">🔒 Niveau verrouillé</span>
                <?php endif; ?>
                <span class="exercice-badge exercise-subject"><?php echo $subject; ?></span>
                <span class="exercice-badge exercise-domain"><?php echo htmlspecialchars($exercise['Domain'] ?? ''); ?></span>
                <span class="exercice-badge exercise-competence"><?php echo htmlspecialchars($exercise['Competence'] ?? ''); ?></span>
                <span class="exercice-badge exercise-xp">XP : <?php echo htmlspecialchars($exercise['XP_Points'] ?? ''); ?></span>
            </div>
            <button class="exercise-toggle" aria-expanded="false" aria-controls="exercise-content-<?php echo $id; ?>" aria-label="Afficher les détails de l'exercice">
                <svg class="chevron" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="sr-only">Afficher les détails</span>
            </button>
        </header>

        <div id="exercise-content-<?php echo $id; ?>" class="exercise-content collapsible" aria-hidden="true">
            <?php
            // CONSIGNE
            if (!empty($exercise['Instruction'])):
                ?>
            <div class="exercise-section exercise-section--instruction">
                <div class="exercise-instruction">
                    <strong>📋 Consigne :</strong>
                    <p><?php echo htmlspecialchars($exercise['Instruction']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php
                // ✅ Gestion multi-parties
                $isMultiPartsRendered = false;

    if (isset($exercise['structure_type']) && $exercise['structure_type'] === 'multi-parties'
        && !empty($exercise['sub_questions'])) {

        $subQuestions = json_decode($exercise['sub_questions'], true);

        if ($subQuestions && !empty($subQuestions['questions'])) {
            $isMultiPartsRendered = true;
            ?>
                    <div class="exercise-section exercise-section--content">
                    <div class="exercise-multi-parties">
                        <?php if (!empty($subQuestions['introduction'])): ?>
                        <div class="exercise-introduction">
                            <p><?php echo nl2br(htmlspecialchars($subQuestions['introduction'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="sub-questions-list">
                            <?php foreach ($subQuestions['questions'] as $sq): ?>
                            <div class="sub-question-item" data-question-id="<?php echo htmlspecialchars($sq['id']); ?>">
                                <label class="sub-question-label">
                                    <strong><?php echo htmlspecialchars($sq['id']); ?>)</strong>
                                    <?php echo htmlspecialchars($sq['enonce']); ?>
                                </label>

                                <?php if (empty($options['readOnly'])): ?>
                                <input type="text"
                                       name="answer_<?php echo htmlspecialchars($sq['id']); ?>"
                                       class="sub-question-input"
                                       placeholder="Ta réponse..." />
                                <?php else: ?>
                                <div class="sub-question-readonly">
                                    <em>Créez un compte pour répondre</em>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (empty($options['readOnly'])): ?>
                        <div class="exercise-actions-row">
                            <button type="button" class="btn-check-multi-parts" data-exercise-id="<?php echo $id; ?>">
                                ✅ Vérifier mes réponses
                            </button>
                        </div>
                        <div class="multi-parts-feedback"></div>
                        <?php endif; ?>
                    </div>
                    </div>
                    <?php
        }
    }
    ?>

            <?php if (!$isMultiPartsRendered): ?>
            <!-- Affichage classique -->
            <div class="exercise-section exercise-section--content">
                <div class="exercise-enonce">
                    <?php echo cleanExerciseContent($content); ?>
                </div>

            <!-- Formulaire dynamique selon AnswerType et Choices -->
            <form class="exercise-form">
                <?php
        $readOnly = !empty($options['readOnly']) || !empty($is_locked);
                $answerType = strtolower(trim($exercise['AnswerType'] ?? ''));
                $choices = $exercise['Choices'] ?? null;
                if ($readOnly) {
                    echo '<div class="exercise-preview-note">Aperçu : créez un compte pour interagir avec cet exercice.</div>';
                } else {
                    if ($answerType === 'qcm' && !empty($choices) && is_array($choices)) {
                        foreach ($choices as $idx => $choice) {
                            $val = chr(97 + $idx); // a, b, c, d ...
                            echo '<div class="exercise-choice"><label><input type="radio" name="answer" value="' . $val . '"> ' . htmlspecialchars($choice) . '</label></div>';
                        }
                    } elseif ($answerType === 'texte' || $answerType === 'text' || $answerType === 'saisie' || empty($answerType)) {
                        echo '<textarea class="exercise-freeform-input" placeholder="Écris ta réponse ici..."></textarea>';
                    } elseif ($answerType === 'choix' && !empty($choices) && is_array($choices)) {
                        echo '<select name="answer" class="exercise-select">';
                        foreach ($choices as $choice) {
                            echo '<option value="' . htmlspecialchars($choice) . '">' . htmlspecialchars($choice) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo '<input type="text" name="answer" class="exercise-input" placeholder="Réponse...">';
                    }
                    echo '<button type="button" class="btn-check-answer">Vérifier ma réponse</button>';
                }
    ?>
            </form>
            <div class="exercise-feedback-area" aria-live="polite"></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($exercise['Tips'])): ?>
            <div class="exercise-section exercise-section--tips">
                <div class="exercise-tips">
                    💡 <?php echo htmlspecialchars($exercise['Tips']); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($answer)): ?>
            <div class="exercise-answer" <?php echo $options['showAnswer'] ? '' : 'hidden'; ?>>
                <h4 class="answer-title">📝 Correction</h4>
                <div class="answer-content">
                    <?php echo $answer; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <footer class="exercise-actions">
        <!-- DEBUG : LinkedCourses -->
        <!-- <?php var_dump($exercise['LinkedCourses'] ?? 'KEY_NOT_FOUND'); ?> -->
        <?php
        $readOnly = !empty($options['readOnly']) || !empty($is_locked);
    if (empty($readOnly)):
        ?>

            <div class="exercise-actions-group">

            <!-- Bouton "Voir le cours" si des cours sont liés -->
            <?php if (isset($exercise['LinkedCourses']) && !empty($exercise['LinkedCourses'])): ?>
            <?php
            // LinkedCourses peut être JSON ou array
            $linkedCourses = is_string($exercise['LinkedCourses'])
                ? json_decode($exercise['LinkedCourses'], true)
                : $exercise['LinkedCourses'];

                // Vérifier que le décodage JSON a fonctionné et qu'on a bien un array non vide
                if (!empty($linkedCourses) && is_array($linkedCourses)):
                    $firstCourseId = is_array($linkedCourses[0])
                        ? ($linkedCourses[0]['id'] ?? $linkedCourses[0])
                        : $linkedCourses[0];
                    ?>
            <a href="<?php echo site_url('view_course') . '?id=' . htmlspecialchars($firstCourseId); ?>" class="btn-view-course" title="Consulter le cours associé">📖 Voir le cours</a>

            <?php endif; ?>
            <?php endif; ?>

            <button
                class="btn-exercise-complete"
                onclick="markExerciseComplete(<?php echo $id; ?>)"
                data-exercise-id="<?php echo $id; ?>"
            >
                ✅ Marquer comme terminé
            </button>

            <button class="btn-duplicate" data-exercise-id="<?php echo $id; ?>" title="Dupliquer cet exercice">📄 Dupliquer</button>

            <button class="btn-toggle-active" data-exercise-id="<?php echo $id; ?>" data-active="<?php echo isset($exercise['is_active']) && $exercise['is_active'] ? '1' : '0'; ?>" title="Activer / Désactiver">
                <?php if (isset($exercise['is_active']) && $exercise['is_active']): ?>🔓 Actif<?php else: ?>🔒 Inactif<?php endif; ?>
            </button>
            </div>
        <?php else: ?>
        <!-- Aperçu sans incitation -->
        <?php endif; ?>
        </footer>
    </article>
    <?php
}

/**
 * Génère le HTML interactif pour un exercice
 * Détecte automatiquement le type d'exercice et génère les classes/attributs nécessaires
 */
function generateInteractiveExercise($exercise, $subject)
{
    // Charger le générateur amélioré s'il existe
    if (file_exists(dirname(__DIR__) . '/includes/exercise_interactive_generator.php')) {
        require_once dirname(__DIR__) . '/includes/exercise_interactive_generator.php';
        if (function_exists('generateQualityInteractiveExercise')) {
            $result = generateQualityInteractiveExercise($exercise, $subject);
            if ($result !== null) {
                return $result;
            }
        }
    }

    // Fallback sur l'ancienne méthode
    $content = $exercise['Content'] ?? '';
    $title = $exercise['Title'] ?? '';
    $id = $exercise['Id'] ?? 0;

    // Nettoyer le contenu HTML
    $cleanContent = strip_tags($content);

    // Priorité au champ AnswerType si présent
    $answerType = isset($exercise['AnswerType']) ? strtolower(trim($exercise['AnswerType'])) : null;
    if ($answerType === 'qcm' || $answerType === 'choix') {
        $exerciseType = 'qcm';
    } elseif ($answerType === 'texte' || $answerType === 'text' || $answerType === 'saisie') {
        $exerciseType = 'texte';
    } elseif ($answerType) {
        $exerciseType = $answerType;
    } else {
        // Détection automatique si non précisé
        $exerciseType = detectExerciseType($subject, $cleanContent, $title);
        // Correction généralisée : si la détection automatique retourne 'qcm' mais qu'il n'y a pas de choix, fallback sur texte (toutes matières)
        if ($exerciseType === 'qcm') {
            $choices = $exercise['Choices'] ?? null;
            if (empty($choices) || $choices === null) {
                $exerciseType = 'texte';
            }
        }
    }

    // Générer les questions selon le type
    $questions = extractQuestionsFromContent($exercise, $exerciseType);

    if (empty($questions)) {
        // Si on ne peut pas extraire de questions, afficher le contenu normalement
        $cleanedContent = cleanExerciseContent($content);
        // Si l'énoncé contient des sous-questions a), b), c)... ajouter une zone de réponse libre
        $hasEnumeratedQuestions = preg_match('/\b[a-e]\)\s/i', strip_tags($content));
        if ($hasEnumeratedQuestions) {
            $hint = '<div class="exercise-freeform-hint" style="margin-top:1rem;font-weight:600;color:#0f172a;">Saisis tes réponses pour a), b), c) ici :</div>';
            $textarea = '<textarea class="exercise-freeform-input" style="width:100%;min-height:140px;margin-top:0.5rem;padding:0.75rem;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Réponds pour chaque sous-question (a, b, c, ...)"></textarea>';
            return '<div class="exercise-enonce">' . $cleanedContent . '</div>' . $hint . $textarea;
        }
        return '<div class="exercise-enonce">' . $cleanedContent . '</div>';
    }

    // Générer le HTML interactif selon le type
    switch ($exerciseType) {
        case 'qcm':
            return generateQCMExercise($questions, $id);
        case 'math':
            return generateMathExercise($questions, $id);
        case 'conjugation':
            return generateConjugationExercise($questions, $id);
        case 'word-coloring':
            return generateWordColoringExercise($exercise, $id);
        case 'texte':
        case 'text':
        case 'saisie':
            // Affichage champ texte libre
            $cleanedContent = cleanExerciseContent($content);
            $textarea = '<textarea class="exercise-freeform-input" style="width:100%;min-height:100px;margin-top:0.5rem;padding:0.75rem;border:1px solid #cbd5e1;border-radius:8px;" placeholder="Écris ta réponse ici..."></textarea>';
            return '<div class="exercise-enonce">' . $cleanedContent . '</div>' . $textarea;
        default:
            // Nettoyer le contenu pour éviter les fuites de code HTML
            $cleanedContent = cleanExerciseContent($content);
            return '<div class="exercise-enonce">' . $cleanedContent . '</div>';
    }
}

/**
 * Nettoie le contenu d'un exercice pour éviter les fuites de code HTML
 *
 * @param string $content Contenu brut de l'exercice
 * @return string Contenu nettoyé et sécurisé
 */
function cleanExerciseContent($content)
{
    if (empty($content)) {
        return '';
    }

    // Décoder les entités HTML si présentes
    $cleaned = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Autoriser seulement les balises HTML sûres et bien formées
    $allowedTags = '<p><strong><em><b><i><ul><ol><li><br><br/><h1><h2><h3><h4><h5><h6><span><div>';
    $cleaned = strip_tags($cleaned, $allowedTags);

    // Nettoyer les balises mal formées avec DOMDocument si disponible
    if (class_exists('DOMDocument') && !empty(trim($cleaned))) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $html = '<?xml encoding="UTF-8"><div>' . $cleaned . '</div>';
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $container = $dom->getElementsByTagName('div')->item(0);
        if ($container) {
            $cleaned = '';
            foreach ($container->childNodes as $node) {
                $cleaned .= $dom->saveHTML($node);
            }
        }
    }

    // Si après nettoyage le contenu est vide, utiliser le texte brut échappé
    $textOnly = strip_tags($cleaned);
    if (empty(trim($textOnly))) {
        $cleaned = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleaned = nl2br($cleaned);
    }

    return $cleaned;
}

/**
 * Détecte le type d'exercice selon la matière et le contenu
 */
function detectExerciseType($subject, $content, $title)
{
    $contentLower = mb_strtolower($content);
    $titleLower = mb_strtolower($title);

    // Mathématiques
    if ($subject === 'Mathématiques' || $subject === 'Maths') {
        // Vérifier si c'est un exercice de calcul
        if (preg_match('/\d+\s*[+\-×*÷\/]\s*\d+/', $content)
            || preg_match('/fraction|calcul|équation|problème/i', $content)) {
            return 'math';
        }
        // Sinon QCM par défaut pour les maths
        return 'qcm';
    }

    // Français
    if ($subject === 'Français') {
        // Classes de mots / analyse de phrase -> exercice de coloriage
        if (preg_match('/classe\s+de\s+mots|classes\s+de\s+mots|analyse\s+la\s+phrase|grammaire/i', $content)) {
            return 'word-coloring';
        }
        // Conjugaison (plus strict pour éviter les faux positifs)
        $hasPlaceholders = preg_match('/\(_\)|____|_\s*_/', $content) === 1;
        $explicitConjug = (preg_match('/\bconjug/i', $title) || preg_match('/\bconjug/i', $content));
        if ($hasPlaceholders || $explicitConjug) {
            return 'conjugation';
        }
        // Réécriture (phrases à compléter)
        if (preg_match('/compléte|écris|réécris|transforme/i', $content)) {
            return 'conjugation'; // Utilise le même format que la conjugaison
        }
        // QCM par défaut pour le français
        return 'qcm';
    }

    // Par défaut : QCM
    return 'qcm';
}

/**
 * Génère le HTML pour un exercice de "classes de mots" (coloriage)
 * Attend une phrase dans le contenu/correction et une correspondance mot -> type
 */
function generateWordColoringExercise($exercise, $exerciseId)
{
    $content = html_entity_decode($exercise['Content'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $answer = html_entity_decode($exercise['Answer'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

    list($sentence, $mapping) = parseWordColoringData($content, $answer);
    if (empty($sentence)) {
        // Fallback: afficher le contenu brut si on ne parvient pas à générer l'exercice
        $cleanedContent = cleanExerciseContent($content);
        return '<div class="exercise-enonce">' . $cleanedContent . '</div>';
    }

    $mappingJson = json_encode($mapping, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);
    $sentenceAttr = htmlspecialchars($sentence, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return <<<HTML
    <div class="word-coloring-exercise" data-sentence="{$sentenceAttr}" data-correct='{$mappingJson}' data-exercise-id="{$exerciseId}">
        <div class="word-coloring-container"></div>
        <button class="btn-check-coloring" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            ✅ Vérifier mes réponses
        </button>
        <div class="coloring-feedback" style="margin-top: 1rem; display: none;"></div>
    </div>
HTML;
}

/**
 * Extrait la phrase et la correspondance mot -> type depuis le contenu/correction
 */
function parseWordColoringData($content, $answer)
{
    // Décoder et nettoyer
    $text = strip_tags($content . "\n" . $answer);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // 1) Extraire la phrase entre guillemets
    $sentence = '';
    if (preg_match('/\"([^\"]+?)\"/u', $text, $m)) {
        $sentence = trim($m[1]);
    } elseif (preg_match('/"([^"]+)"/u', $text, $m2)) {
        $sentence = trim($m2[1]);
    }

    // Fallback: chercher la phrase après "Analyse la phrase suivante" ou la première phrase complète
    if ($sentence === '') {
        $linesRaw = preg_split('/\r?\n/', $text);
        $lines = array_map(function ($l) {
            return trim(preg_replace('/\*+/', '', $l));
        }, $linesRaw);
        $pickNext = false;
        foreach ($lines as $l) {
            if ($pickNext && mb_strlen($l) > 10) {
                // Prendre la première phrase plausible
                if (preg_match('/[\.!?]$/u', $l) || mb_substr_count($l, ' ') >= 5) {
                    $sentence = $l;
                    break;
                }
            }
            if (preg_match('/analyse\s+la\s+phrase\s+suivante/i', $l)) {
                $pickNext = true;
                continue;
            }
            // Sinon, prendre la première ligne assez riche
            if ($sentence === '' && mb_substr_count($l, ' ') >= 6 && preg_match('/[\.!?]/u', $l)) {
                $sentence = $l;
            }
        }
    }

    // 2) Extraire le mapping détaillé (analyse mot par mot)
    $mapping = [];
    $lines = preg_split('/\r?\n/', $text);
    foreach ($lines as $line) {
        // Ex: 1. Le → Déterminant (article défini)
        if (preg_match('/^\s*\d+\.\s*\**([^\*\s]+)\**\s*[→:\-]\s*\**([^\*\(\n]+)\**/u', trim($line), $mm)) {
            $word = normalizeWordForMapping($mm[1]);
            $type = normalizeGrammarType($mm[2]);
            if ($word && $type) {
                $mapping[$word] = $type;
            }
        }
    }

    // 3) Si pas assez d'entrées, tenter le récapitulatif par classe ("- Noms : chien, jardin")
    if (count($mapping) < 2) {
        foreach ($lines as $line) {
            if (preg_match('/^\s*\-\s*\**(Noms?|Verbes?|Adjectifs?|Déterminants?|Adverbes?|Prépositions?)\**\s*:\s*(.+)$/iu', trim($line), $mr)) {
                $type = normalizeGrammarType($mr[1]);
                $wordsStr = $mr[2];
                // Retirer parenthèses et x2, etc.
                $wordsStr = preg_replace('/\(.*?\)/', '', $wordsStr);
                $words = preg_split('/\s*,\s*/', $wordsStr);
                foreach ($words as $w) {
                    $w = trim($w);
                    if ($w === '') {
                        continue;
                    }
                    $mapping[normalizeWordForMapping($w)] = $type;
                }
            }
        }
    }

    return [$sentence, $mapping];
}

function normalizeWordForMapping($word)
{
    $w = mb_strtolower(trim($word));
    // retirer ponctuation
    $w = preg_replace('/[.,;:!?\"\'\'\(\)\[\]]/u', '', $w);
    return $w;
}

function normalizeGrammarType($type)
{
    $t = mb_strtolower(trim($type));
    $t = str_replace(['é', 'è', 'ê', 'à', 'ù', 'ï', 'î', 'ô', 'ç'], ['e','e','e','a','u','i','i','o','c'], $t);
    // Normaliser
    if (strpos($t, 'nom') !== false) {
        return 'nom';
    }
    if (strpos($t, 'verbe') !== false) {
        return 'verbe';
    }
    if (strpos($t, 'adjectif') !== false) {
        return 'adjectif';
    }
    if (strpos($t, 'determinant') !== false || strpos($t, 'article') !== false) {
        return 'determinant';
    }
    if (strpos($t, 'adverbe') !== false) {
        return 'adverbe';
    }
    if (strpos($t, 'preposition') !== false) {
        return 'preposition';
    }
    return '';
}

/**
 * Extrait les questions depuis le contenu de l'exercice
 */
function extractQuestionsFromContent($exercise, $type)
{
    $content = strip_tags($exercise['Content'] ?? '');
    $answer = strip_tags($exercise['Answer'] ?? '');
    $title = $exercise['Title'] ?? '';

    $questions = [];

    if ($type === 'qcm') {
        // Extraire les questions QCM (format: question ? choix1, choix2, choix3)
        if (preg_match_all('/(.+?)\?/i', $content, $matches)) {
            foreach ($matches[1] as $index => $questionText) {
                // Générer des choix (simplifié - à améliorer)
                $choicesData = generateChoicesFromAnswer($answer, $index);

                // Formater les choix au format attendu par le JavaScript
                $formattedChoices = [];
                $correctValue = 'a';
                foreach ($choicesData['choices'] as $idx => $choice) {
                    $value = chr(97 + $idx); // a, b, c, d
                    if ($choice === $choicesData['correct']) {
                        $correctValue = $value;
                    }
                    $formattedChoices[] = [
                        'value' => $value,
                        'label' => $value . ') ' . $choice,
                    ];
                }

                $questions[] = [
                    'question' => trim($questionText) . ' ?',
                    'choices' => $formattedChoices,
                    'correct' => $correctValue,
                ];
            }
        } else {
            // Si pas de format QCM détecté, créer une question simple depuis le titre ou le début du contenu
            $questionText = !empty($title) ? $title : mb_substr($content, 0, 100);
            $questions[] = [
                'question' => $questionText,
                'choices' => [
                    ['value' => 'a', 'label' => 'a) Vrai'],
                    ['value' => 'b', 'label' => 'b) Faux'],
                    ['value' => 'c', 'label' => 'c) Je ne sais pas'],
                ],
                'correct' => 'a',
            ];
        }
    } elseif ($type === 'math') {
        // Extraire les calculs mathématiques
        if (preg_match_all('/(\d+)\s*([+\-×*÷\/])\s*(\d+)\s*=\s*\?/', $content, $mathMatches)) {
            foreach ($mathMatches[0] as $index => $match) {
                $num1 = (int) $mathMatches[1][$index];
                $op = $mathMatches[2][$index];
                $num2 = (int) $mathMatches[3][$index];

                $result = calculateResult($num1, $op, $num2);
                $wrong1 = $result + rand(1, 10);
                $wrong2 = $result - rand(1, 10);

                $questions[] = [
                    'question' => "$num1 $op $num2 = ?",
                    'answer' => (string) $result,
                    'choices' => [(string) $result, (string) $wrong1, (string) $wrong2],
                ];
            }
        }
    } elseif ($type === 'conjugation') {
        // Extraire les phrases à compléter (placeholders)
        if (preg_match_all('/____|\.\.\.|_+/', $content, $gapMatches)) {
            $sentences = preg_split('/\r?\n|\.(?!\d)/', $content);
            foreach ($sentences as $sentence) {
                $s = trim($sentence);
                if ($s === '') {
                    continue;
                }
                if (strpos($s, '____') !== false || strpos($s, '...') !== false || preg_match('/_\s*_/', $s)) {
                    $questions[] = [
                        'sentence' => $s,
                        'answer' => '',
                    ];
                }
            }
        }
        // Fallback: énoncés numérotés "1. ..." avec réponses numérotées dans la correction
        if (empty($questions)) {
            $lines = preg_split('/\r?\n/', $content);
            $numSentences = [];
            foreach ($lines as $line) {
                if (preg_match('/^\s*(\d+)[\)\.]\s*(.+)$/u', trim($line), $m)) {
                    $numSentences[(int) $m[1]] = trim($m[2]);
                }
            }
            if (!empty($numSentences)) {
                $ansLines = preg_split('/\r?\n/', $answer);
                $numAnswers = [];
                foreach ($ansLines as $al) {
                    if (preg_match('/^\s*(\d+)[\)\.:\-]\s*(.+)$/u', trim($al), $ma)) {
                        $numAnswers[(int) $ma[1]] = trim($ma[2]);
                    }
                }
                foreach ($numSentences as $n => $sent) {
                    $questions[] = [
                        'sentence' => $sent,
                        'answer' => $numAnswers[$n] ?? '',
                    ];
                }
            }
        }
    }

    return $questions;
}

/**
 * Génère des choix pour un QCM depuis la réponse
 */
function generateChoicesFromAnswer($answer, $index = 0)
{
    $correct = 'Réponse correcte';
    if (!empty($answer)) {
        // Essayer d'extraire la bonne réponse depuis la correction
        $lines = explode("\n", $answer);
        if (isset($lines[$index])) {
            $correct = trim($lines[$index]);
        } else {
            $correct = trim($lines[0]);
        }
        // Nettoyer les balises HTML et limiter à 80 caractères
        $correct = strip_tags($correct);
        $correct = mb_substr($correct, 0, 80);
    }

    // Générer des distracteurs plausibles
    $distractor1 = 'Choix alternatif 1';
    $distractor2 = 'Choix alternatif 2';
    $distractor3 = 'Choix alternatif 3';

    // Mélanger l'ordre
    $allChoices = [$correct, $distractor1, $distractor2, $distractor3];
    shuffle($allChoices);

    return [
        'choices' => $allChoices,
        'correct' => $correct,
    ];
}

/**
 * Calcule le résultat d'une opération mathématique
 */
function calculateResult($num1, $op, $num2)
{
    switch ($op) {
        case '+': return $num1 + $num2;
        case '-': return $num1 - $num2;
        case '*':
        case '×': return $num1 * $num2;
        case '/':
        case '÷': return $num2 != 0 ? round($num1 / $num2, 2) : 0;
        default: return 0;
    }
}

/**
 * Génère le HTML pour un exercice QCM
 */
function generateQCMExercise($questions, $exerciseId)
{
    $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

    return <<<HTML
    <div class="qcm-exercise" data-questions='{$questionsJson}' data-exercise-id="{$exerciseId}">
        <div class="qcm-container"></div>
        <button class="btn-check-qcm" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            ✅ Vérifier mes réponses
        </button>
        <div class="qcm-feedback" style="margin-top: 1rem; display: none;"></div>
    </div>
HTML;
}

/**
 * Génère le HTML pour un exercice mathématique
 */
function generateMathExercise($questions, $exerciseId)
{
    $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

    return <<<HTML
    <div class="math-exercise" data-questions='{$questionsJson}' data-exercise-id="{$exerciseId}">
        <div class="math-container"></div>
        <button class="btn-check-math" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            ✅ Vérifier mes réponses
        </button>
        <div class="math-feedback" style="margin-top: 1rem; display: none;"></div>
    </div>
HTML;
}

/**
 * Génère le HTML pour un exercice de conjugaison
 */
function generateConjugationExercise($questions, $exerciseId)
{
    $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

    return <<<HTML
    <div class="conjugation-exercise" data-questions='{$questionsJson}' data-exercise-id="{$exerciseId}">
        <div class="conjugation-container"></div>
        <button class="btn-check-conjugation" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
            ✅ Vérifier mes réponses
        </button>
        <div class="conjugation-feedback" style="margin-top: 1rem; display: none;"></div>
    </div>
HTML;
}

/**
 * Affiche une liste d'exercices
 *
 * @param array $exercises Liste d'exercices
 * @param array $options Options d'affichage
 */
function renderExerciseList($exercises, $options = [])
{
    if (empty($exercises)) {
        echo '<div class="no-exercises"><p>Aucun exercice disponible pour le moment.</p></div>';
        return;
    }

    $defaultOptions = [
        'showAnswer' => false,
        'groupBySubject' => true,
        'emptyMessage' => 'Aucun exercice disponible',
    ];
    $options = array_merge($defaultOptions, $options);

    if ($options['groupBySubject']) {
        // Grouper par matière
        $grouped = [];
        foreach ($exercises as $exercise) {
            $subject = $exercise['Subject'] ?? 'Autre';
            if (!isset($grouped[$subject])) {
                $grouped[$subject] = [];
            }
            $grouped[$subject][] = $exercise;
        }

        // Détecter si on doit limiter à 1 exercice par matière (visiteur)
        $limitPerSubject = 0;
        if (isset($options['limitPerSubject'])) {
            $limitPerSubject = (int) $options['limitPerSubject'];
        }

        // Si on veut une seule matière aléatoire (pour visiteurs)
        if (isset($options['limitSubjects']) && $options['limitSubjects'] > 0) {
            $subjects = array_keys($grouped);
            shuffle($subjects);
            $subjects = array_slice($subjects, 0, $options['limitSubjects']);
        } else {
            $subjects = array_keys($grouped);
        }

        foreach ($subjects as $subject) {
            $subjectExercises = $grouped[$subject];
            $icon = ($subject === 'Mathématiques') ? '🧮' : '📚';
            echo '<section class="exercise-subject-group">';
            echo '<h2 class="subject-title">' . $icon . ' ' . htmlspecialchars($subject) . '</h2>';
            echo '<div class="exercise-grid">';

            if ($limitPerSubject > 0) {
                shuffle($subjectExercises);
                $subjectExercises = array_slice($subjectExercises, 0, $limitPerSubject);
            }
            foreach ($subjectExercises as $exercise) {
                renderExerciseCard($exercise, $options);
            }

            echo '</div>';
            echo '</section>';
        }
    } else {
        echo '<div class="exercise-grid">';
        foreach ($exercises as $exercise) {
            renderExerciseCard($exercise, $options);
        }
        echo '</div>';
    }
}

/**
 * Affiche un exercice complet avec toutes les sections
 *
 * @param array $exercise Données de l'exercice
 * @param array $options Options d'affichage
 */
function renderExerciseFull($exercise, $options = [])
{
    if (!$exercise) {
        echo '<p>Exercice non trouvé.</p>';
        return;
    }

    $options['showAnswer'] = true;
    $options['showDetails'] = true;

    renderExerciseCard($exercise, $options);
}

/**
 * Affiche un aperçu limité d'exercices pour les utilisateurs non connectés
 * @param string $level Niveau (ex: '6ème', 'Seconde', 'Terminale')
 * @param int $limit Nombre d'exercices à afficher
 */
function renderExercisePreview($level, $limit = 3)
{
    // récupérer avec le loader si disponible
    if (is_file(dirname(__DIR__) . '/includes/exercice_loader.php')) {
        require_once dirname(__DIR__) . '/includes/exercice_loader.php';
    }
    $exercises = [];
    if (function_exists('getExercisesByLevel')) {
        // Récupérer TOUS les exercices actifs du niveau (pas de limit global)
        $exercises = getExercisesByLevel($level, null, null);
    }
    if (empty($exercises)) {
        echo '<div class="no-exercises"><p>Aucun aperçu disponible.</p></div>';
        return;
    }
    // Grouper par matière
    $grouped = [];
    foreach ($exercises as $ex) {
        $s = $ex['Subject'] ?? 'Inconnu';
        if (!isset($grouped[$s])) {
            $grouped[$s] = [];
        }
        $grouped[$s][] = $ex;
    }
    // Tirer une matière au hasard
    $subjects = array_keys($grouped);
    shuffle($subjects);
    $subject = $subjects[0];
    // Tirer un exercice au hasard dans cette matière
    $subjectExercises = $grouped[$subject];
    shuffle($subjectExercises);
    $exercise = $subjectExercises[0];
    // ...existing code...
    // Afficher la carte unique
    renderExerciseCard($exercise, [
        'showAnswer' => false,
        'readOnly' => true,
    ]);
}

// Script JavaScript - sera inclus uniquement si nécessaire
// Note: Ce script est chargé dans les pages qui utilisent renderExerciseCard
// Pour éviter les "headers already sent", il est encapsulé dans une fonction
if (!function_exists('renderExerciseCardScript')) {
    function renderExerciseCardScript()
    {
        static $scriptRendered = false;
        if ($scriptRendered) {
            return;
        } // Éviter les doublons
        $scriptRendered = true;
        ?>
<script>
/**
 * Fonction JavaScript pour afficher/masquer la correction
 */
function toggleAnswer(exerciseId) {
    // Trouver la carte de l'exercice
    const card = document.querySelector(`.exercise-card[data-exercise-id="${exerciseId}"]`);
    if (!card) {
        console.error('Carte d\'exercice non trouvée pour ID:', exerciseId);
        return;
    }

    // Trouver la div de correction et le bouton
    const answerDiv = card.querySelector('.exercise-answer');
    const button = card.querySelector('.btn-show-answer');

    if (!answerDiv) {
        console.error('Div de correction non trouvée pour l\'exercice ID:', exerciseId);
        // Essayer de charger la correction via AJAX
        loadExerciseAnswer(exerciseId);
        return;
    }

    // Afficher/masquer la correction
    const isVisible = answerDiv.style.display !== 'none' && answerDiv.offsetParent !== null;

    if (isVisible) {
        // Masquer la correction
        answerDiv.style.display = 'none';
        if (button) {
            button.textContent = '📝 Voir la correction';
        }
    } else {
        // Afficher la correction
        answerDiv.style.display = 'block';
        if (button) {
            button.textContent = '👁️ Masquer la correction';
        }
    }
}

/**
 * Charge la correction d'un exercice via AJAX
 */
function loadExerciseAnswer(exerciseId) {
    // TODO: Implémenter le chargement AJAX si nécessaire
    console.log('Chargement de la correction pour l\'exercice', exerciseId);
}

/**
 * Marque un exercice comme terminé et sauvegarde la progression
 */
function markExerciseComplete(exerciseId, correct = true) {
    const card = document.querySelector(`[data-exercise-id="${exerciseId}"]`);
    const button = card ? card.querySelector('.btn-exercise-complete') : null;

    if (button) {
        button.disabled = true;
        button.textContent = '⏳ Enregistrement...';
    }

    // Appeler l'API pour sauvegarder
    fetch('api/save_exercise_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            exercise_id: exerciseId,
            correct: correct ? '1' : '0'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Afficher les récompenses
            if (data.cristaux > 0) {
                showRewardAnimation('💎', data.cristaux, 'cristaux');
            }
            if (data.xp > 0) {
                showRewardAnimation('⭐', data.xp, 'XP');
            }
            if (data.badge_unlocked) {
                showBadgeUnlocked(data.badge_name);
            }

            // Mettre à jour l'affichage
            if (card) {
                card.classList.add('exercise-completed');
                if (button) {
                    button.textContent = '✅ Terminé';
                    button.style.background = '#10b981';
                }

                // Afficher un message de succès
                const successMsg = document.createElement('div');
                successMsg.className = 'exercise-success-message';
                successMsg.innerHTML = `
                    <strong>🎉 Exercice complété !</strong><br>
                    ${data.cristaux > 0 ? `💎 +${data.cristaux} cristaux<br>` : ''}
                    ${data.xp > 0 ? `⭐ +${data.xp} XP<br>` : ''}
                    ${data.badge_unlocked ? `🏆 Badge débloqué : ${data.badge_name}` : ''}
                `;
                card.appendChild(successMsg);

                setTimeout(() => {
                    successMsg.style.opacity = '0';
                    setTimeout(() => successMsg.remove(), 500);
                }, 5000);
            }

            // Mettre à jour la progression globale si disponible
            if (data.progress && typeof updateGlobalProgress === 'function') {
                updateGlobalProgress(data.progress);
            }
        } else {
            // Gérer l'erreur
            if (button) {
                button.disabled = false;
                button.textContent = '❌ Erreur - Réessayer';
            }

            alert(data.message || 'Erreur lors de l\'enregistrement');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        if (button) {
            button.disabled = false;
            button.textContent = '❌ Erreur - Réessayer';
        }
    });
}

/**
 * Affiche une animation de récompense
 */
function showRewardAnimation(icon, amount, type) {
    const animationDiv = document.createElement('div');
    animationDiv.className = 'reward-animation';
    animationDiv.innerHTML = `${icon} +${amount} ${type}`;
    animationDiv.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 2em;
        color: #fbbf24;
        z-index: 10000;
        pointer-events: none;
        animation: rewardFloat 2s ease-out forwards;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    `;

    document.body.appendChild(animationDiv);

    setTimeout(() => {
        if (document.body.contains(animationDiv)) {
            document.body.removeChild(animationDiv);
        }
    }, 2000);
}

/**
 * Affiche une notification de badge débloqué
 */
function showBadgeUnlocked(badgeName) {
    const badgeDiv = document.createElement('div');
    badgeDiv.className = 'badge-unlocked-notification';
    badgeDiv.innerHTML = `
        <div class="badge-notification-content">
            <h3>🏆 Badge Débloqué !</h3>
            <p><strong>${badgeName}</strong></p>
        </div>
    `;
    badgeDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: badgeSlideIn 0.5s ease-out;
    `;

    document.body.appendChild(badgeDiv);

    setTimeout(() => {
        badgeDiv.style.animation = 'badgeSlideOut 0.5s ease-out forwards';
        setTimeout(() => {
            if (document.body.contains(badgeDiv)) {
                document.body.removeChild(badgeDiv);
            }
        }, 500);
    }, 4000);
}
</script>
<?php
    }
}
