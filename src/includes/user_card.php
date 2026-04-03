<?php
// Composant carte utilisateur/profil résumé, version landingpage (copie fidèle)
if (!isset($is_logged_in)) {
    $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
}
if (!isset($is_parent_logged_in)) {
    $is_parent_logged_in = !empty($_SESSION['parent_id']);
}
if (!isset($user_name)) {
    $user_name = $_SESSION['user_name'] ?? 'Élève';
}
if (!isset($user_level_display)) {
    $user_level_display = $_SESSION['user_level'] ?? '';
}
if (!isset($is_admin)) {
    $is_admin = (function_exists('isAdmin') && isAdmin());
}
// Toggle: allow pages to suppress the built-in heading to avoid duplicates
$show_user_card_title = $show_user_card_title ?? true;
// Toggle: allow pages to suppress the lead paragraph (avoid duplicate role/level copy)
// If the title is suppressed, default to suppressing the lead as well to avoid repeated role/level lines
$show_user_card_lead = isset($show_user_card_lead) ? $show_user_card_lead : ($show_user_card_title ? true : false);
?>
<div class="user-card-landing-context">
    <?php if ($show_user_card_title): ?>
    <h1>
        <?php if ($is_logged_in): ?>
            Bienvenue <?php echo htmlspecialchars($user_name); ?>&nbsp;!
        <?php else: ?>
            Bienvenue sur MonCoachScolaire&nbsp;!
        <?php endif; ?>
    </h1>
    <?php endif; ?>

    <?php if ($show_user_card_lead): ?>
    <p class="lead">
        <?php if ($is_logged_in || $is_parent_logged_in): ?>
            <?php if ($is_admin): ?>
                Espace <strong>Administrateur</strong> - Accès complet à tous les niveaux et ressources
            <?php elseif ($is_parent_logged_in): ?>
                Espace <strong>Parents</strong> - Suivez la progression de vos enfants
            <?php else: ?>
                Ton espace personnalisé pour le niveau <strong><?php echo htmlspecialchars($user_level_display); ?></strong>
            <?php endif; ?>
        <?php else: ?>
            Ton coach pédagogique personnalisé pour réussir toute ta scolarité
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <?php if ($is_logged_in || $is_parent_logged_in): ?>
        <?php if ($is_admin): ?>
            <div class="coach-message">
                <strong>👋 Salut <?php echo htmlspecialchars($user_name); ?> !</strong><br>
                Tu es connecté en tant qu'<strong>administrateur</strong>.<br>
                Tu as accès à tous les niveaux, exercices et cours de l'application.
            </div>
        <?php elseif ($is_parent_logged_in): ?>
            <div class="coach-message">
                <strong>👋 Salut <?php echo htmlspecialchars($user_name); ?> !</strong><br>
                Tu es connecté en tant que <strong>parent</strong>.<br>
                Accède rapidement aux ressources de suivi de tes enfants ci-dessous.
            </div>
        <?php else: ?>
            <div class="coach-message">
                <strong>👋 Salut <?php echo htmlspecialchars($user_name); ?> !</strong><br>
                Tu es connecté en tant qu'élève de <strong><?php echo htmlspecialchars($user_level_display); ?></strong>.<br>
                Accède rapidement à tes ressources ci-dessous.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

