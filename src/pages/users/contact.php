<?php
/**
 * Page de Contact - MonCoachScolaire
 * Styles externes : assets/css/pages/contact.css
 */

$hide_site_header = true;
$hide_skip_link = true;
$page_title = 'Contact - MonCoachScolaire';
$page_css = 'contact.css';

// Charger site_boot si ce fichier est accédé directement
if (!function_exists('site_url')) {
    if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
        require_once dirname(__DIR__, 2) . '/config/site_boot.php';
    } elseif (is_file(__DIR__ . '/../config/site_boot.php')) {
        require_once __DIR__ . '/../config/site_boot.php';
    }
}
?>

<main class="main-content contact-page-main">
  <section class="contact-section">
    <h2>Contactez-nous</h2>
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="contact-success">Votre message a bien été envoyé !</div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="contact-error">Une erreur est survenue. Merci de réessayer.</div>
    <?php endif; ?>
    <form class="contact-form" method="post" action="<?php echo site_url('users/send_contact'); ?>">
      <div class="form-group">
        <label for="nom">👤 Nom</label>
        <input type="text" id="nom" name="nom" required placeholder="Votre nom">
      </div>
      <div class="form-group">
        <label for="email">📧 Email</label>
        <input type="email" id="email" name="email" required placeholder="votre.email@exemple.com">
      </div>
      <div class="form-group">
        <label for="message">💬 Message</label>
        <textarea id="message" name="message" rows="5" required placeholder="Votre message..."></textarea>
      </div>
      <button type="submit" class="contact-submit-btn">
        <span class="btn-text">📤 Envoyer</span>
      </button>
    </form>
  </section>
</main>
