/**
 * Système de déconnexion automatique basé sur l'inactivité
 * - Surveille l'inactivité de l'utilisateur
 * - Affiche un avertissement 5 minutes avant la déconnexion
 * - Déconnecte automatiquement après 30 minutes d'inactivité
 * - Permet de prolonger la session
 */

(function() {
    'use strict';

    // Configuration
    // Détecter si c'est un compte démo (via window ou fallback)
    const IS_DEMO = window.isDemo || (window.pageConfig && window.pageConfig.isDemo) || false;
    const INACTIVITY_TIMEOUT = IS_DEMO ? (10 * 60 * 1000) : (30 * 60 * 1000); // 10 min démo, 30 min sinon
    const WARNING_TIME = 2 * 60 * 1000; // 2 minutes avant la déconnexion
    const WARNING_THRESHOLD = INACTIVITY_TIMEOUT - WARNING_TIME;

    let inactivityTimer = null;
    let warningTimer = null;
    let lastActivityTime = Date.now();
    let warningShown = false;

    /**
     * Réinitialise le timer d'inactivité
     */
    function resetInactivityTimer() {
        // Annuler les timers existants
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }
        if (warningTimer) {
            clearTimeout(warningTimer);
        }

        // Masquer l'avertissement si visible
        if (warningShown) {
            hideWarning();
        }

        // Mettre à jour le temps de dernière activité
        lastActivityTime = Date.now();

        // Programmer l'avertissement (après 25 minutes)
        warningTimer = setTimeout(() => {
            showWarning();
        }, WARNING_THRESHOLD);

        // Programmer la déconnexion (après 30 minutes)
        inactivityTimer = setTimeout(() => {
            logout();
        }, INACTIVITY_TIMEOUT);

        console.log('🔄 Timer d\'inactivité réinitialisé');
    }

    /**
     * Affiche l'avertissement de déconnexion imminente
     */
    function showWarning() {
        if (warningShown) return;

        warningShown = true;
        const modal = createWarningModal();
        document.body.appendChild(modal);

        // Animation d'apparition
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);

        // Mettre à jour le compte à rebours
        updateCountdown(modal);
    }

    /**
     * Crée le modal d'avertissement
     */
    function createWarningModal() {
        const modal = document.createElement('div');
        modal.id = 'session-timeout-warning';
        modal.className = 'session-timeout-modal';
        modal.innerHTML = `
            <div class="session-timeout-overlay"></div>
            <div class="session-timeout-content">
                <div class="session-timeout-icon">⏰</div>
                <h2>Session sur le point d'expirer</h2>
                <p>Vous n'avez pas été actif depuis un moment.</p>
                <p class="session-timeout-countdown">
                    Vous serez déconnecté dans <span id="countdown-time">5:00</span>
                </p>
                <div class="session-timeout-actions">
                    <button id="extend-session-btn" class="btn-extend-session">
                        Rester connecté
                    </button>
                    <button id="logout-now-btn" class="btn-logout-now">
                        Se déconnecter maintenant
                    </button>
                </div>
            </div>
        `;

        // Gérer le bouton "Rester connecté"
        const extendBtn = modal.querySelector('#extend-session-btn');
        extendBtn.addEventListener('click', () => {
            resetInactivityTimer();
            hideWarning();
        });

        // Gérer le bouton "Se déconnecter maintenant"
        const logoutBtn = modal.querySelector('#logout-now-btn');
        logoutBtn.addEventListener('click', () => {
            logout();
        });

        return modal;
    }

    /**
     * Met à jour le compte à rebours
     */
    function updateCountdown(modal) {
        const countdownElement = modal.querySelector('#countdown-time');
        if (!countdownElement) return;

        const updateCountdownDisplay = () => {
            if (!warningShown) return;

            const timeRemaining = INACTIVITY_TIMEOUT - (Date.now() - lastActivityTime);

            if (timeRemaining <= 0) {
                // Le timer principal devrait déjà avoir déclenché la déconnexion
                return;
            }

            const minutes = Math.floor(timeRemaining / 60000);
            const seconds = Math.floor((timeRemaining % 60000) / 1000);
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            // Continuer à mettre à jour toutes les secondes
            setTimeout(updateCountdownDisplay, 1000);
        };

        updateCountdownDisplay();
    }

    /**
     * Masque l'avertissement
     */
    function hideWarning() {
        const modal = document.getElementById('session-timeout-warning');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
        warningShown = false;
    }

    /**
     * Déconnecte l'utilisateur
     */
    function logout() {
        const msg = IS_DEMO ? '🔒 Déconnexion démo automatique due à l\'inactivité' : '🔒 Déconnexion automatique due à l\'inactivité';
        console.log(msg);

        // Masquer l'avertissement si visible
        hideWarning();

        // Pour démo, rediriger direct à landing. Sinon logout normal.
        const logoutUrl = IS_DEMO ? getLandingUrl() : getLogoutUrl();
        if (!logoutUrl) {
            return;
        }
        window.location.href = logoutUrl;
    }

    /**
     * Obtient l'URL de déconnexion
     */
    function getLogoutUrl() {
        // Essayer de détecter l'URL de base
        const baseUrl = getBaseUrl();
        if (!baseUrl) {
            return null;
        }
        const currentPath = window.location.pathname;

        // Déterminer si on utilise le routeur (index.php) ou les fichiers directs
        const usesRouter = currentPath.includes('index.php');

        if (usesRouter) {
            // Utiliser le routeur
            return buildRouterUrl(baseUrl, 'logout', { reason: 'timeout' });
        } else {
            // Utiliser le fichier direct
            return buildDirectUrl(baseUrl, '/logout.php', { reason: 'timeout' });
        }
    }

    /**
     * Obtient l'URL de landing page (timeout)
     */
    function getLandingUrl() {
        const baseUrl = getBaseUrl();
        if (!baseUrl) {
            return null;
        }
        const currentPath = window.location.pathname;
        const usesRouter = currentPath.includes('index.php');

        if (usesRouter) {
            return buildRouterUrl(baseUrl, 'landingpage', { reason: 'timeout' });
        }
        return buildDirectUrl(baseUrl, '/landingpage.php', { reason: 'timeout' });
    }

    /**
     * Normalise la baseUrl
     */
    function getBaseUrl() {
        const base = (typeof window.baseUrl === 'string' ? window.baseUrl : '').trim();
        if (!base) {
            console.warn('[session-timeout] window.baseUrl manquant, redirection annulée.');
            return null;
        }
        return base.replace(/\/+$/, '');
    }

    /**
     * Construit une URL routeur
     */
    function buildRouterUrl(baseUrl, page, params) {
        const query = new URLSearchParams({ page, ...params });
        return `${baseUrl}/index.php?${query.toString()}`;
    }

    /**
     * Construit une URL directe
     */
    function buildDirectUrl(baseUrl, path, params) {
        const query = new URLSearchParams(params);
        const suffix = query.toString();
        return `${baseUrl}${path}${suffix ? `?${suffix}` : ''}`;
    }

    /**
     * Détecte l'activité de l'utilisateur
     */
    function detectActivity() {
        resetInactivityTimer();
    }

    /**
     * Initialise le système de surveillance d'inactivité
     */
    function init() {
        // Vérifier si l'utilisateur est connecté
        // On peut vérifier la présence d'un élément ou d'une variable globale
        const isLoggedIn = (document.body && document.body.dataset && document.body.dataset.userId) ||
                  document.querySelector('.topbar') ||
                  window.location.pathname.includes('dashboard') ||
                  window.location.pathname.includes('progression');

        if (!isLoggedIn) {
            console.log('ℹ️ Utilisateur non connecté - surveillance d\'inactivité désactivée');
            return;
        }

        console.log('✅ Surveillance d\'inactivité activée');

        // Événements à surveiller
        const events = [
            'mousedown',
            'mousemove',
            'keypress',
            'scroll',
            'touchstart',
            'click'
        ];

        // Ajouter les écouteurs d'événements
        events.forEach(event => {
            document.addEventListener(event, detectActivity, true);
        });

        // Surveiller aussi les changements de visibilité de la page
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Si la page redevient visible, réinitialiser le timer
                detectActivity();
            }
        });

        // Démarrer le timer initial
        resetInactivityTimer();

        // Intercepter les requêtes fetch pour réinitialiser le timer
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            // Réinitialiser le timer à chaque requête AJAX
            resetInactivityTimer();
            return originalFetch.apply(this, args);
        };

        // Intercepter les requêtes XMLHttpRequest pour réinitialiser le timer
        const originalXHROpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function(...args) {
            this.addEventListener('loadstart', () => {
                resetInactivityTimer();
            });
            return originalXHROpen.apply(this, args);
        };

        // Exposer une fonction globale pour réinitialiser manuellement (utile pour les requêtes AJAX)
        window.resetSessionTimeout = resetInactivityTimer;
    }

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
