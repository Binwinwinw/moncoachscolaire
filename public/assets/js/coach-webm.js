/**
 * Coach WebM (mascotte) - Authentifiés uniquement
 * Gère l'affichage de la vidéo WebM du coach avec transparence alpha
 * Intègre les scénarios d'utilisation : bonjour, encouragements, félicitations, célébrations
 * 
 * Utilisation: incluez ce script dans les pages protégées (dashboard, exercices, etc.)
 */

(function() {
  'use strict';

  /**
   * Catalogue des vidéos du coach
   * Utilise une SEULE vidéo pour tous les scénarios (simplicité + fiabilité)
   * Clés: actions/états ; Valeurs: nom du fichier dans assets/img/coach/humain/
   */
  const coachCatalog = {
    // Tous les scénarios utilisent la même vidéo
    bonjour: 'championdumonde.webm',
    posture: 'championdumonde.webm',
    encouragement: 'championdumonde.webm',
    encouragement_v2: 'championdumonde.webm',
    bravo: 'championdumonde.webm',
    tresbien: 'championdumonde.webm',
    champion: 'championdumonde.webm',
    fete: 'championdumonde.webm',
  };

  /**
   * Config globale du coach
   */
  const coachConfig = {
    containerId: 'coach-container',
    videoId: 'coach-humain',
    defaultDuration: 5000,
    autoStartOnLoad: true,
    defaultAction: 'bonjour',
    enableLogging: false // Debug
  };

  /**
   * État du coach (suivi des events)
   */
  const coachState = {
    firstVisitToday: !localStorage.getItem('coach_visit_' + new Date().toDateString()),
    correctAnswersCount: 0,
    lastAction: null,
    hideTimeoutId: null
  };

  /**
   * Logger debug
   */
  function log(msg) {
    if (coachConfig.enableLogging) {
      console.log('[Coach] ' + msg);
    }
  }

  /**
   * Initialiser le coach (une seule fois au chargement)
   */
  function initCoach() {
    if (document.getElementById(coachConfig.containerId)) {
      return; // Déjà initialisé
    }

    const container = document.createElement('div');
    container.id = coachConfig.containerId;
    container.className = 'coach-overlay';
    
    const video = document.createElement('video');
    video.id = coachConfig.videoId;
    video.muted = true;
    video.playsinline = true;
    video.loop = false;
    
    // Source WebM prioritaire + fallback MP4
    const sourceWebM = document.createElement('source');
    sourceWebM.type = 'video/webm; codecs=vp9';
    
    const sourceMP4 = document.createElement('source');
    sourceMP4.type = 'video/mp4';
    
    video.appendChild(sourceWebM);
    video.appendChild(sourceMP4);
    
    container.appendChild(video);
    document.body.appendChild(container);
    
    log('Coach initialisé');
  }

  /**
   * Afficher le coach avec une action spécifique
   * @param {string} action - Clé du coachCatalog (ex: 'bonjour', 'bravo', 'champion')
   * @param {number} duration - Durée avant masquage (ms)
   */
  window.showCoach = function(action, duration) {
    initCoach();
    
    if (!coachCatalog[action]) {
      console.warn('[Coach] Action non trouvée:', action, '. Actions disponibles:', Object.keys(coachCatalog));
      return;
    }
    
    const container = document.getElementById(coachConfig.containerId);
    const video = document.getElementById(coachConfig.videoId);
    
    if (!video) {
      console.error('[Coach] Élément vidéo non trouvé');
      return;
    }

    // Annuler l'ancien timeout si en cours
    if (coachState.hideTimeoutId) {
      clearTimeout(coachState.hideTimeoutId);
    }
    
    const filename = coachCatalog[action];
    const videoSrc = document.querySelector('body').getAttribute('data-base-url') || window.baseUrl || '';
    const webmPath = videoSrc + '/assets/img/coach/humain/' + filename;
    const mp4Path = webmPath.replace(/\.webm$/, '.mp4');
    
    // Mettre à jour sources
    video.querySelector('source[type*=webm]').src = webmPath;
    video.querySelector('source[type*=mp4]').src = mp4Path;
    
    // Réinitialiser et charger
    video.load();
    
    // Afficher le conteneur
    container.classList.add('active');
    
    // Lire la vidéo
    video.play().catch(err => log('Lecture bloquée: ' + err.message));
    
    // Masquer après durée
    const finalDuration = duration || coachConfig.defaultDuration;
    coachState.hideTimeoutId = setTimeout(() => {
      container.classList.remove('active');
      coachState.hideTimeoutId = null;
    }, finalDuration);

    coachState.lastAction = action;
    log('Affichage : ' + action + ' (' + finalDuration + 'ms)');
  };

  /**
   * Masquer le coach immédiatement
   */
  window.hideCoach = function() {
    const container = document.getElementById(coachConfig.containerId);
    const video = document.getElementById(coachConfig.videoId);
    if (container) container.classList.remove('active');
    if (video) {
      video.pause();
      if (coachState.hideTimeoutId) {
        clearTimeout(coachState.hideTimeoutId);
        coachState.hideTimeoutId = null;
      }
    }
    log('Coach masqué');
  };

  /**
   * Réagir à une réponse correcte
   * Après 3 réponses correctes, afficher "bravo"
   */
  window.onCorrectAnswer = function() {
    coachState.correctAnswersCount++;
    log('Réponse correcte #' + coachState.correctAnswersCount);
    
    if (coachState.correctAnswersCount >= 3) {
      window.showCoach('bravo', 4000);
      coachState.correctAnswersCount = 0; // Réinitialiser
    }
  };

  /**
   * Réagir à une erreur/mauvaise réponse
   */
  window.onIncorrectAnswer = function() {
    log('Réponse incorrecte');
    window.showCoach('encouragement', 4000);
  };

  /**
   * Afficher le score et réagir en conséquence
   * @param {number} score - Score en pourcentage (0-100)
   */
  window.onExerciseComplete = function(score) {
    log('Exercice complété - Score: ' + score + '%');

    if (score >= 90) {
      window.showCoach('champion', 5000);
    } else if (score >= 70) {
      window.showCoach('tresbien', 5000);
    } else if (score >= 50) {
      window.showCoach('bravo', 4000);
    } else {
      window.showCoach('encouragement', 4000);
    }
  };

  /**
   * Afficher la célébration pour un niveau/badge complété
   */
  window.onLevelOrBadgeComplete = function() {
    log('Niveau ou badge complété!');
    window.showCoach('fete', 6000);
  };

  /**
   * Afficher le coach au chargement de la page (si première visite du jour)
   */
  window.showDailyGreeting = function() {
    if (coachState.firstVisitToday) {
      log('Première visite du jour');
      window.showCoach('bonjour', 5000);
      localStorage.setItem('coach_visit_' + new Date().toDateString(), 'true');
    }
  };

  /**
   * Initialiser au chargement du DOM
   */
  document.addEventListener('DOMContentLoaded', () => {
    initCoach();
  });

  // Exposer les méthodes publiques
  window.coachState = coachState;
  window.coachConfig = coachConfig;

  log('Coach WebM chargé et prêt');
})();
