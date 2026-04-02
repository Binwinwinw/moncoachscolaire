💻 GUIDE D'INTÉGRATION AVEC FOND TRANSPARENT
Structure HTML
Copy<div class="coach-overlay">
  <video id="coach-humain" autoplay muted playsinline>
    <source src="" type="video/mp4">
  </video>
</div>
CSS pour fond transparent
Copy.coach-overlay {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 250px;
  height: auto;
  z-index: 9999;
  pointer-events: none; /* Ne bloque pas les clics */
}

#coach-humain {
  width: 100%;
  height: auto;
  /* Le fond transparent de la vidéo sera respecté */
}

/* Animation d'apparition fluide */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.coach-overlay.active {
  animation: fadeInUp 0.6s ease-out;
}
JavaScript - Système de déclenchement
Copy// Catalogue des vidéos du coach
const coachVideos = {
  heureux: 'coach-heureux.mp4',
  encourager1: 'coach-encourager-v1.mp4',
  bonjour: 'coach-bonjour.mp4',
  encourager2: 'coach-encourager-v2.mp4',
  fete: 'coach-fete.mp4',
  felicite: 'coach-felicite.mp4'
};

// Fonction pour afficher le coach
function showCoach(action, duration = 5000) {
  const overlay = document.querySelector('.coach-overlay');
  const video = document.getElementById('coach-humain');
  const source = video.querySelector('source');
  
  // Charger la vidéo
  source.src = coachVideos[action];
  video.load();
  
  // Afficher avec animation
  overlay.classList.add('active');
  video.play();
  
  // Masquer après la durée
  setTimeout(() => {
    overlay.classList.remove('active');
  }, duration);
}

// ===== EXEMPLES D'UTILISATION =====

// Au chargement de la page
window.addEventListener('DOMContentLoaded', () => {
  showCoach('bonjour', 5000);
});

// Quand l'élève commence un exercice difficile
function onExerciceDifficile() {
  showCoach('encourager1');
}

// Après une erreur (pour remotiver)
function onErreur() {
  showCoach('encourager1');
}

// Après 3 bonnes réponses
let bonnesReponses = 0;
function onReponseCorrecte() {
  bonnesReponses++;
  if (bonnesReponses === 3) {
    showCoach('encourager2');
    bonnesReponses = 0;
  }
}

// Exercice complété avec succès
function onExerciceTermine(score) {
  if (score >= 80) {
    showCoach('felicite');
  } else if (score >= 50) {
    showCoach('encourager2');
  }
}

// Niveau complété à 100%
function onNiveauComplete() {
  showCoach('fete', 6000); // Plus long pour la célébration
}

// Badge débloqué
function onBadgeObtenu() {
  showCoach('fete');
}
Intégration PHP - Détection du contexte
Copy<?php
session_start();

// Déterminer quelle animation afficher
$coachAction = 'heureux'; // Par défaut

// Première visite du jour
if (!isset($_SESSION['derniere_visite']) || 
    date('Y-m-d', $_SESSION['derniere_visite']) !== date('Y-m-d')) {
    $coachAction = 'bonjour';
    $_SESSION['derniere_visite'] = time();
}

// Nouvel utilisateur
if (isset($_SESSION['premiere_connexion']) && $_SESSION['premiere_connexion']) {
    $coachAction = 'bonjour';
    $_SESSION['premiere_connexion'] = false;
}
?>

<script>
// Passer l'action PHP à JavaScript
document.addEventListener('DOMContentLoaded', function() {
  showCoach('<?php echo $coachAction; ?>');
});
</script>
🎯 SCÉNARIOS D'UTILISATION DÉTAILLÉS
Situation	Animation	Moment de déclenchement
Connexion matin	👋 Bonjour	Première connexion du jour
Page d'accueil	😊 Heureux	État par défaut
Exercice difficile	💪 Encourage v1	Au clic sur "Commencer"
Après une erreur	💪 Encourage v1	Après réponse incorrecte
3 bonnes réponses	💪 Encourage v2	Compteur atteint
Exercice à 50%	💪 Encourage v2	Barre de progression
Score 70-90%	🎊 Félicite	Fin d'exercice
Score >90%	🎉 Fête	Fin d'exercice
Niveau complété	🎉 Fête	100% de réussite
Badge débloqué	🎉 Fête	Achievement unlocked