<!-- Coach WebM (mascotte) - Afficher uniquement si authentifié -->
<?php
if (!empty($_SESSION['user_id']) && !empty($_SESSION['logged_in'])):
    ?>
<style>
  /* Conteneur du coach avec positionnement fixe */
  .coach-overlay {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 250px;
    height: auto;
    z-index: 9999;
    pointer-events: none; /* Ne bloque pas les clics */
    opacity: 0;
    transition: opacity 0.5s ease-out;
  }

  .coach-overlay.active {
    opacity: 1;
    animation: slideInUp 0.6s ease-out;
  }

  .coach-overlay video {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    /* Important: la transparence de la vidéo WebM est préservée */
    background: transparent;
  }

  /* Animation d'apparition */
  @keyframes slideInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Responsive */
  @media (max-width: 480px) {
    .coach-overlay {
      width: 180px;
      bottom: 10px;
      right: 10px;
    }
  }
</style>

<!-- Inclure le script de gestion du coach -->
<script src="<?php echo site_url('/assets/js/coach-webm.js'); ?>"></script>

<?php
endif;
?>
