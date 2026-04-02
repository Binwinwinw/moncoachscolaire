(function(){
  'use strict';

  // Animations du footer et gestion du background (migré depuis le HTML inline)
  document.addEventListener('DOMContentLoaded', function() {
    try {
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            try {
              entry.target.style.opacity = '1';
              entry.target.style.transform = 'translateY(0)';
            } catch(_) {}
          }
        });
      }, observerOptions);

      const featureCards = document.querySelectorAll('.feature-card');
      if (featureCards && featureCards.length) {
        featureCards.forEach(card => {
          try {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
          } catch(_) {}
        });
      }

      // Effet parallaxe pour l'image de fond - suit le mouvement du scroll avec un léger décalage
      let ticking = false;

      function updateBackgroundPosition() {
        try {
          const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
          const documentHeight = Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight,
            document.body.offsetHeight,
            document.documentElement.offsetHeight,
            document.body.clientHeight,
            document.documentElement.clientHeight
          );
          document.documentElement.style.setProperty('--bg-scroll', scrollTop);
          document.documentElement.style.setProperty('--doc-height', documentHeight + 'px');
        } catch(_) {}
        ticking = false;
      }

      function requestTick() {
        if (!ticking) {
          window.requestAnimationFrame(updateBackgroundPosition);
          ticking = true;
        }
      }

      // Initialiser la position au chargement
      updateBackgroundPosition();
      // Écouter le scroll pour mettre à jour la position de l'image
      window.addEventListener('scroll', requestTick, { passive: true });
      // Mettre à jour lors du redimensionnement et du chargement complet du DOM
      window.addEventListener('resize', updateBackgroundPosition, { passive: true });
      window.addEventListener('load', updateBackgroundPosition, { passive: true });

      // Observer les changements de taille du document (ajout de contenu dynamique)
      if (typeof ResizeObserver !== 'undefined') {
        try {
          const resizeObserver = new ResizeObserver(() => updateBackgroundPosition());
          resizeObserver.observe(document.body);
        } catch(_) {}
      }

    } catch (_) {
      // Fail silently — pas critique
    }
  });

  // Exposer la fonction pour le CTA
  window.scrollToFeatures = function() {
    try {
      const el = document.getElementById('features');
      if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
      }
    } catch(_) {}
  };
})();
