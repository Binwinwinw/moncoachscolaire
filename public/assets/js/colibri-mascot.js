/**
 * JavaScript pour la Mascotte Colibri - MonCoachScolaire
 * Gère les animations, changements de poses et messages d'encouragement
 */

(function() {
    'use strict';

    /**
     * Classe pour gérer la mascotte Colibri
     */
    class ColibriMascot {
        constructor(container, options = {}) {
            this.container = typeof container === 'string' 
                ? document.querySelector(container) 
                : container;
            
            if (!this.container) {
                console.warn('ColibriMascot: Container not found');
                return;
            }

            this.options = {
                pose: 'neutre',
                size: 'medium',
                position: 'inline',
                showSpeechBubble: true,
                autoHideSpeechBubble: true,
                speechBubbleDuration: 6000, // 6 secondes
                imageType: 'auto', // 'auto', 'cartoon', 'realiste'
                ...options
            };

            this.currentPose = this.options.pose;
            this.speechBubble = null;
            this.animationTimeout = null;
            
            // Système d'automatisation de la vidéo
            this.videoLoopCount = 0;
            this.maxLoops = 2; // Arrêter après 2 boucles
            this.pauseDuration = 120000; // 2 minutes en millisecondes
            this.resumeTimeout = null;
            this.isPaused = false;
            this.videoElement = null;
            this.baseVideoSrc = null;
            this.successVideoSrc = null;
            this.boundHandleVideoEnded = this.handleVideoEnded.bind(this);

            // Système de sprites avec morph en boucle
            this.spriteElement = null;
            this.spriteIndex = 0;
            this.spritePoses = ['neutre', 'heureux', 'encourageant', 'celebration', 'reflexion'];
            this.spriteMorphInterval = null; // Intervalle pour la boucle de morph
            this.spriteMorphDuration = 60000; // Durée entre chaque morph (1 minute)
            this.spriteMorphTransitionDuration = 1500; // Durée de la transition CSS (1.5 secondes)
            this.isSpriteLoopPaused = false; // Indicateur si la boucle est en pause (pendant une vidéo)
            
            // Système de vidéos aléatoires toutes les 5 minutes
            this.randomVideoInterval = null;
            this.randomVideoDelay = 300000; // 5 minutes en millisecondes
            this.randomVideos = []; // Liste des vidéos aléatoires selon le niveau
            
            // Gestion de la visibilité de la page (Page Visibility API)
            this.isPageVisible = !document.hidden;
            this.visibilityChangeHandler = this.handleVisibilityChange.bind(this);
            
            // Écouter les changements de visibilité
            document.addEventListener('visibilitychange', this.visibilityChangeHandler);

            this.init();
        }

        init() {
            // Déterminer le type d'image selon le niveau
            const imageType = this.determineImageType();
            
            // Obtenir le baseUrl
            let baseUrl = '';
            if (typeof window.baseUrl !== 'undefined' && window.baseUrl !== null) {
                // Utiliser window.baseUrl défini par PHP (footer.php)
                // En prod (domaine racine) : baseUrl = '' (chaîne vide)
                // En local XAMPP : baseUrl = '/moncoachscolaire'
                baseUrl = String(window.baseUrl).trim();
            } else {
                // Fallback : détecter depuis location.pathname
                if (window.location.pathname) {
                    const pathParts = window.location.pathname.split('/').filter(p => p);
                    // Si on est dans un sous-dossier (ex: /moncoachscolaire/...)
                    if (pathParts.length > 0 && pathParts[0] !== 'index.php') {
                        // Vérifier si on est dans un sous-dossier (pas à la racine)
                        const scriptPath = window.location.pathname;
                        if (scriptPath.startsWith('/') && scriptPath.split('/').length > 2) {
                            baseUrl = '/' + pathParts[0];
                        }
                    }
                }
            }
            // Stocker pour une utilisation ultérieure (sprites, vidéos spéciales)
            // baseUrl peut être '' en production (domaine racine) ou '/moncoachscolaire' en local
            this.baseUrl = baseUrl;
            
            console.log('🔧 BaseUrl détecté pour mascotte:', {
                baseUrl: this.baseUrl,
                windowBaseUrl: typeof window.baseUrl !== 'undefined' ? window.baseUrl : 'NON DÉFINI',
                locationPathname: window.location.pathname,
                locationHref: window.location.href,
                locationOrigin: window.location.origin
            });
            
            // CRÉER L'ÉLÉMENT VIDÉO POUR L'ANIMATION CONTINUE
            this.videoElement = document.createElement('video');
            this.videoElement.autoplay = false; // Ne pas autoplay, on contrôle manuellement
            this.videoElement.loop = false;
            this.videoElement.muted = true;
            this.videoElement.playsInline = true;
            this.videoElement.preload = 'metadata'; // ✅ Lazy loading : charger uniquement les métadonnées
            this.videoElement.style.cssText = `
                width: 100%;
                height: 100%;
                object-fit: contain;
                border: none;
                border-radius: 0;
                background: transparent;
            `;
            
            // Choisir les vidéos selon le type de niveau
            // Les vidéos optimisées sont dans assets/img/coach/optimized/
            // Utiliser plusieurs sources pour optimiser le chargement (WebM prioritaire, MP4 fallback)
            
            // Normaliser baseUrl pour la construction des chemins
            // En prod (domaine racine) : baseUrl = '' → chemin = '/assets/...'
            // En local XAMPP : baseUrl = '/moncoachscolaire' → chemin = '/moncoachscolaire/assets/...'
            let normalizedBaseUrl = baseUrl ? baseUrl.replace(/\/$/, '') : '';
            // S'assurer qu'on a un slash au début si baseUrl n'est pas vide
            if (normalizedBaseUrl && !normalizedBaseUrl.startsWith('/')) {
                normalizedBaseUrl = '/' + normalizedBaseUrl;
            }
            
            console.log('🎥 Initialisation vidéo mascotte:', {
                imageType: imageType,
                baseUrl: baseUrl,
                normalizedBaseUrl: normalizedBaseUrl,
                windowBaseUrl: typeof window.baseUrl !== 'undefined' ? window.baseUrl : 'NON DÉFINI'
            });
            
            // Fonction helper pour construire les chemins avec URL complète
            // Utilise window.location.origin pour garantir le bon fonctionnement en production
            const buildPath = (filename) => {
                // Construire le chemin relatif
                const relativePath = normalizedBaseUrl 
                    ? `${normalizedBaseUrl}/assets/img/coach/optimized/${filename}`
                    : `/assets/img/coach/optimized/${filename}`;
                // Nettoyer les doubles slashes (sauf après le protocole http:// ou https://)
                const cleanPath = relativePath.replace(/([^:]\/)\/+/g, '$1');
                // Retourner l'URL complète pour garantir le bon fonctionnement
                return window.location.origin + cleanPath;
            };
            
            // Les vidéos ne sont plus utilisées en boucle continue
            // Elles seront jouées aléatoirement toutes les 5 minutes
            // On configure quand même les chemins pour les vidéos de succès
            // Les vidéos ne sont plus utilisées en boucle continue
            // Elles seront jouées aléatoirement toutes les 5 minutes
            // On configure quand même les chemins pour les vidéos de succès
            if (imageType === 'realiste') {
                // Lycée : vidéo réaliste (depuis optimized/)
                const basePath = buildPath('realiste_volant');
                console.log('📹 Chemin vidéo réaliste:', basePath);
                this.setupVideoSources(basePath);
                this.baseVideoSrc = basePath; // Stocker le chemin de base pour restauration
                this.successVideoSrc = basePath; // Même vidéo pour succès en mode réaliste
            } else {
                // Collège : vidéo de fond + animation spéciale de succès (depuis optimized/)
                const basePath = buildPath('colibricartoonvolantbackground');
                const successPath = buildPath('cartoon_volant');
                console.log('📹 Chemins vidéo cartoon:', { basePath, successPath });
                this.setupVideoSources(basePath);
                this.baseVideoSrc = basePath; // Stocker le chemin de base pour restauration
                this.successVideoSrc = successPath; // Chemin de base pour vidéo succès
            }
            
            // Gérer les erreurs de chargement vidéo
            this.videoElement.addEventListener('error', (e) => {
                const error = this.videoElement.error;
                const errorMessages = {
                    1: 'MEDIA_ERR_ABORTED - Le chargement a été interrompu',
                    2: 'MEDIA_ERR_NETWORK - Erreur réseau',
                    3: 'MEDIA_ERR_DECODE - Erreur de décodage (fichier corrompu?)',
                    4: 'MEDIA_ERR_SRC_NOT_SUPPORTED - Format non supporté'
                };
                
                console.error('❌ Erreur chargement vidéo mascotte:', {
                    error: e,
                    code: error?.code,
                    codeMessage: errorMessages[error?.code] || 'Erreur inconnue',
                    message: error?.message,
                    networkState: this.videoElement.networkState,
                    readyState: this.videoElement.readyState,
                    currentSrc: this.videoElement.currentSrc,
                    src: this.videoElement.src,
                    sources: Array.from(this.videoElement.querySelectorAll('source')).map(s => ({
                        src: s.src,
                        type: s.type
                    })),
                    suggestion: error?.code === 3 
                        ? '⚠️ Le fichier vidéo semble corrompu. Vérifiez l\'intégrité des fichiers compressés.'
                        : error?.code === 4
                        ? '⚠️ Le format vidéo n\'est pas supporté par le navigateur.'
                        : 'Vérifiez que les fichiers existent et sont accessibles.'
                });
                
                // Essayer de charger la source suivante si disponible
                if (error?.code === 3 || error?.code === 4) {
                    const sources = Array.from(this.videoElement.querySelectorAll('source'));
                    const currentIndex = sources.findIndex(s => s.src === this.videoElement.currentSrc);
                    if (currentIndex < sources.length - 1) {
                        console.log('🔄 Tentative de chargement de la source suivante...');
                        // Le navigateur devrait automatiquement essayer la source suivante
                    }
                }
            });
            
            // Log pour débogage
            this.videoElement.addEventListener('loadstart', () => {
                console.log('🔄 Début chargement vidéo mascotte:', this.videoElement.currentSrc || this.videoElement.src);
            });
            
            this.videoElement.addEventListener('loadeddata', () => {
                console.log('✅ Données vidéo chargées:', this.videoElement.currentSrc);
            });
            
            // Précharger les sources vidéo (mais ne pas les jouer automatiquement)
            // Les vidéos seront jouées aléatoirement toutes les 5 minutes
            this.videoElement.load();
            
            // Gérer les événements de la vidéo pour le système de vidéos aléatoires
            this.videoElement.addEventListener('ended', this.boundHandleVideoEnded);
            
            // Gérer le chargement de la vidéo (pour les vidéos aléatoires)
            this.videoElement.addEventListener('loadedmetadata', () => {
                console.log('📹 Métadonnées vidéo chargées:', {
                    duration: this.videoElement.duration,
                    videoWidth: this.videoElement.videoWidth,
                    videoHeight: this.videoElement.videoHeight,
                    currentSrc: this.videoElement.currentSrc
                });
            });
            
            // Gérer les problèmes de chargement
            this.videoElement.addEventListener('stalled', () => {
                console.warn('⚠️ Chargement vidéo interrompu (stalled)');
            });
            
            this.videoElement.addEventListener('suspend', () => {
                console.warn('⚠️ Chargement vidéo suspendu');
            });
            
            // Créer le container principal
            this.mascotElement = document.createElement('div');
            this.mascotElement.className = `colibri-mascot colibri-${this.currentPose} size-${this.options.size} position-${this.options.position}`;
            this.mascotElement.style.cssText = `
                position: relative;
                width: 100%;
                height: 100%;
                overflow: visible;
            `;
            
            // Ajouter la vidéo au container
            this.mascotElement.appendChild(this.videoElement);
            
            // Créer un overlay sprite pour l'effet de morph en boucle
            this.spriteElement = document.createElement('img');
            this.spriteElement.className = 'colibri-sprite-overlay';
            this.spriteElement.style.cssText = `
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                object-fit: contain;
                border: none;
                border-radius: 0;
                background: transparent;
                opacity: 1;
                transition: opacity 0.8s ease-in-out;
                pointer-events: none;
                z-index: 2;
            `;
            this.mascotElement.appendChild(this.spriteElement);
            
            // Masquer la vidéo de base (elle sera affichée uniquement pour les vidéos aléatoires)
            this.videoElement.style.display = 'none';
            this.videoElement.style.zIndex = '1';
            
            this.container.appendChild(this.mascotElement);
            
            // Stocker le type d'image pour référence future si nécessaire
            this.imageType = imageType;
            
            // Initialiser les vidéos aléatoires selon le niveau
            this.initRandomVideos(imageType);

            // Créer la bulle de parole si nécessaire
            if (this.options.showSpeechBubble) {
                this.createSpeechBubble();
            }

            // Démarrer la boucle de morph automatique des sprites
            this.startSpriteMorphLoop();
            
            // Démarrer le système de vidéos aléatoires toutes les 5 minutes
            this.startRandomVideoSystem();

            // Événement click : au clic on affiche soit un message (bulle),
            // soit une vidéo aléatoire si disponible
            this.mascotElement.addEventListener('click', () => {
                const hasVideos = this.randomVideos && this.randomVideos.length > 0;
                const useVideo = hasVideos && Math.random() < 0.5; // 50% vidéos, 50% bulle

                if (useVideo) {
                    this.playRandomVideo();
                } else {
                    // Bulle d'encouragement qui disparaît après un délai
                    this.showRandomEncouragement();
                }
            });
        }

        /**
         * Configure plusieurs sources vidéo pour optimiser le chargement
         * Le navigateur choisira automatiquement la meilleure source disponible
         * @param {string} basePath Chemin de base sans extension
         */
        setupVideoSources(basePath) {
            // Vérifier si WebM est supporté
            const video = document.createElement('video');
            const canPlayWebM = video.canPlayType('video/webm; codecs="vp9"') !== '';
            
            // Si basePath est déjà une URL complète (commence par http:// ou https://), extraire le chemin
            // Sinon, construire l'URL complète avec window.location.origin
            const isFullUrl = basePath.startsWith('http://') || basePath.startsWith('https://');
            let pathWithoutOrigin = basePath;
            
            if (isFullUrl) {
                // Extraire le chemin depuis l'URL complète
                try {
                    const url = new URL(basePath);
                    pathWithoutOrigin = url.pathname;
                } catch (e) {
                    // Si l'URL est mal formée, utiliser le chemin tel quel
                    pathWithoutOrigin = basePath.replace(/^https?:\/\/[^\/]+/, '');
                }
            }
            
            // Construire les URLs complètes avec window.location.origin
            const origin = window.location.origin;
            
            console.log('🎬 Configuration sources vidéo:', {
                basePath: basePath,
                isFullUrl: isFullUrl,
                pathWithoutOrigin: pathWithoutOrigin,
                origin: origin,
                canPlayWebM: canPlayWebM,
                baseUrl: this.baseUrl
            });
            
            // Créer les chemins pour les différentes versions
            // Les fichiers optimisés sont dans assets/img/coach/optimized/
            let optimizedWebM, originalWebM, optimizedMP4, originalMP4;
            
            if (pathWithoutOrigin.includes('/optimized/')) {
                // Si le chemin contient déjà optimized/, utiliser directement ces fichiers
                optimizedWebM = origin + pathWithoutOrigin + '.webm';
                optimizedMP4 = origin + pathWithoutOrigin + '.mp4';
                // Fallback vers versions originales (si elles existent dans coach/)
                const originalPath = pathWithoutOrigin.replace('/coach/optimized/', '/coach/');
                originalWebM = origin + originalPath + '.webm';
                originalMP4 = origin + originalPath + '.mp4';
            } else {
                // Chemin normal, créer les versions optimisées (priorité)
                const optimizedPath = pathWithoutOrigin.replace('/coach/', '/coach/optimized/');
                optimizedWebM = origin + optimizedPath + '.webm';
                optimizedMP4 = origin + optimizedPath + '.mp4';
                // Fallback vers versions originales
                originalWebM = origin + pathWithoutOrigin + '.webm';
                originalMP4 = origin + pathWithoutOrigin + '.mp4';
            }
            
            console.log('📁 Chemins vidéo générés:', {
                optimizedWebM: optimizedWebM,
                optimizedMP4: optimizedMP4,
                originalWebM: originalWebM,
                originalMP4: originalMP4
            });
            
            // Vider les sources existantes
            this.videoElement.innerHTML = '';
            
            // Ajouter les sources dans l'ordre de priorité (le navigateur choisira la première disponible)
            // 1. WebM optimisé (plus léger, meilleure compression) - PRIORITÉ MAXIMALE
            if (canPlayWebM) {
                const sourceWebMOptimized = document.createElement('source');
                sourceWebMOptimized.src = optimizedWebM;
                sourceWebMOptimized.type = 'video/webm; codecs="vp9"';
                sourceWebMOptimized.addEventListener('error', (e) => {
                    console.warn('⚠️ Erreur chargement WebM optimisé:', {
                        src: optimizedWebM,
                        error: e,
                        networkState: this.videoElement.networkState
                    });
                });
                this.videoElement.appendChild(sourceWebMOptimized);
                console.log('✅ Source WebM optimisé ajoutée:', optimizedWebM);
            }
            
            // 2. MP4 optimisé (meilleure compression que l'original) - PRIORITÉ HAUTE
            const sourceMP4Optimized = document.createElement('source');
            sourceMP4Optimized.src = optimizedMP4;
            sourceMP4Optimized.type = 'video/mp4';
            sourceMP4Optimized.addEventListener('error', (e) => {
                console.warn('⚠️ Erreur chargement MP4 optimisé:', {
                    src: optimizedMP4,
                    error: e,
                    networkState: this.videoElement.networkState
                });
            });
            this.videoElement.appendChild(sourceMP4Optimized);
            console.log('✅ Source MP4 optimisé ajoutée:', optimizedMP4);
            
            // 3. Fallback : versions originales (si les optimisées n'existent pas)
            if (canPlayWebM) {
                const sourceWebMOriginal = document.createElement('source');
                sourceWebMOriginal.src = originalWebM;
                sourceWebMOriginal.type = 'video/webm; codecs="vp9"';
                sourceWebMOriginal.addEventListener('error', (e) => {
                    console.warn('⚠️ Erreur chargement WebM original:', {
                        src: originalWebM,
                        error: e
                    });
                });
                this.videoElement.appendChild(sourceWebMOriginal);
            }
            
            const sourceMP4Original = document.createElement('source');
            sourceMP4Original.src = originalMP4;
            sourceMP4Original.type = 'video/mp4';
            sourceMP4Original.addEventListener('error', (e) => {
                console.warn('⚠️ Erreur chargement MP4 original:', {
                    src: originalMP4,
                    error: e
                });
            });
            this.videoElement.appendChild(sourceMP4Original);
            
            // Source par défaut (fallback ultime) - utiliser la version optimisée MP4 en priorité
            this.videoElement.src = optimizedMP4;
            console.log('🎬 Source par défaut définie:', optimizedMP4);
        }

        /**
         * Détermine la source vidéo optimale (WebM si disponible, sinon MP4)
         * Utilise la version optimisée si disponible, sinon l'original
         * @param {string} basePath Chemin de base sans extension
         * @returns {string} Chemin complet vers la vidéo optimale
         */
        getOptimalVideoSrc(basePath) {
            // Vérifier si WebM est supporté
            const video = document.createElement('video');
            const canPlayWebM = video.canPlayType('video/webm; codecs="vp9"') !== '';
            
            // Ordre de priorité :
            // 1. Version optimisée WebM (dans optimized/)
            // 2. Version optimisée MP4 (dans optimized/)
            // 3. Version originale (fallback)
            
            if (canPlayWebM) {
                // Essayer d'abord la version optimisée WebM
                const optimizedWebM = basePath.replace('/coach/', '/coach/optimized/') + '.webm';
                return optimizedWebM;
            }
            
            // Fallback : MP4 optimisé
            const optimizedMP4 = basePath.replace('/coach/', '/coach/optimized/') + '.mp4';
            return optimizedMP4;
        }

        /**
         * Détermine le type d'image à utiliser (cartoon pour collège, realiste pour lycée)
         */
        determineImageType() {
            if (this.options.imageType !== 'auto') {
                return this.options.imageType;
            }

            // Méthode 1 : Utiliser window.userLevel si disponible
            if (typeof window.userLevel !== 'undefined' && window.userLevel) {
                const level = window.userLevel.toLowerCase();
                const collegeLevels = ['6ème', '6eme', '5ème', '5eme', '4ème', '4eme', '3ème', '3eme'];
                const lyceeLevels = ['seconde', 'première', 'premiere', 'terminale', 'bac'];
                
                if (collegeLevels.includes(level)) {
                    return 'cartoon';
                }
                if (lyceeLevels.includes(level)) {
                    return 'realiste';
                }
            }

            // Méthode 2 : Vérifier les classes CSS ou attributs data
            const body = document.body;
            if (body.classList.contains('college') || body.dataset.levelType === 'college') {
                return 'cartoon';
            }
            if (body.classList.contains('lycee') || body.dataset.levelType === 'lycee') {
                return 'realiste';
            }

            // Méthode 3 : Vérifier l'URL
            const url = window.location.href;
            if (url.includes('/college/') || url.includes('/6eme') || url.includes('/5eme') || 
                url.includes('/4eme') || url.includes('/3eme')) {
                return 'cartoon';
            }
            if (url.includes('/lycee/') || url.includes('/seconde') || url.includes('/premiere') || 
                url.includes('/terminale') || url.includes('/bac')) {
                return 'realiste';
            }

            // Méthode 4 : Vérifier les éléments de navigation ou texte
            const navText = document.body.textContent || '';
            const collegeKeywords = ['6ème', '5ème', '4ème', '3ème', 'Collège'];
            const lyceeKeywords = ['Seconde', 'Première', 'Terminale', 'BAC', 'Lycée'];
            
            for (const keyword of lyceeKeywords) {
                if (navText.includes(keyword)) {
                    return 'realiste';
                }
            }
            for (const keyword of collegeKeywords) {
                if (navText.includes(keyword)) {
                    return 'cartoon';
                }
            }

            // Par défaut : cartoon (collège)
            return 'cartoon';
        }

        createSpeechBubble() {
            this.speechBubble = document.createElement('div');
            this.speechBubble.className = 'colibri-speech-bubble';
            this.mascotElement.appendChild(this.speechBubble);
        }

        /**
         * Change la pose du colibri
         * Note: Avec les vidéos, on garde toujours la vidéo visible
         * Les poses servent principalement pour les animations CSS et les messages
         * @param {string} pose - Pose à afficher (neutre, heureux, encourageant, celebration, reflexion)
         * @param {number} duration - Durée de l'animation en ms (0 = permanent)
         */
        setPose(pose, duration = 0) {
            const validPoses = ['neutre', 'heureux', 'encourageant', 'celebration', 'reflexion'];
            if (!validPoses.includes(pose)) {
                console.warn(`ColibriMascot: Invalid pose "${pose}"`);
                return;
            }

            // Retirer l'ancienne pose
            this.mascotElement.classList.remove(`colibri-${this.currentPose}`);
            
            // Ajouter la nouvelle pose
            this.currentPose = pose;
            this.mascotElement.classList.add(`colibri-${this.currentPose}`);

            // La vidéo reste toujours visible (pas de changement d'image statique)
            // Les animations CSS s'appliquent au container pour les effets visuels

            // Si duration > 0, revenir à neutre après la durée
            if (duration > 0) {
                if (this.animationTimeout) {
                    clearTimeout(this.animationTimeout);
                }
                this.animationTimeout = setTimeout(() => {
                    this.setPose('neutre');
                }, duration);
            }
        }

        /**
         * Affiche un message dans la bulle de parole
         * @param {string} message - Message à afficher
         * @param {number} duration - Durée d'affichage en ms
         */
        showMessage(message, duration = null) {
            if (!this.speechBubble) {
                this.createSpeechBubble();
            }

            this.speechBubble.textContent = message;
            
            // Forcer la visibilité avec plusieurs méthodes
            this.speechBubble.classList.add('visible');
            this.speechBubble.style.opacity = '1';
            this.speechBubble.style.visibility = 'visible';
            this.speechBubble.style.display = 'block';
            this.speechBubble.style.zIndex = '10001';
            
            console.log('💬 Bulle de parole affichée:', message, {
                hasVisible: this.speechBubble.classList.contains('visible'),
                opacity: this.speechBubble.style.opacity,
                zIndex: this.speechBubble.style.zIndex
            });

            const hideDuration = duration !== null ? duration : this.options.speechBubbleDuration;
            
            if (this.options.autoHideSpeechBubble && hideDuration > 0) {
                setTimeout(() => {
                    this.hideMessage();
                }, hideDuration);
            }
        }

        /**
         * Cache la bulle de parole
         */
        hideMessage() {
            if (this.speechBubble) {
                this.speechBubble.classList.remove('visible');
                // Réinitialiser les styles inline pour s'assurer que la bulle est bien cachée
                this.speechBubble.style.opacity = '0';
                this.speechBubble.style.visibility = 'hidden';
                console.log('💬 Bulle de parole masquée');
            }
        }

        /**
         * Réaction pour une bonne réponse (content)
         */
        celebrate() {
            // Interrompre la pause si la vidéo est en pause
            this.interruptPause();
            
            this.setPose('heureux', 2500);
            this.playSuccessVideo();
            const messages = [
                'Bravo ! 🎉',
                'Excellent travail ! ⭐',
                'Tu es sur la bonne voie ! 💪',
                'Superbe ! Continue comme ça ! 🚀',
                'Parfait ! Tu progresses ! ✨',
                'Formidable ! 👏',
                'Génial ! 🎊',
                'Tu es doué(e) ! 🌟'
            ];
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            this.showMessage(randomMessage, 3000);
        }

        /**
         * Réaction pour une mauvaise réponse (encourageant)
         * Toujours encourageant pour motiver l'utilisateur
         */
        encourage() {
            // Interrompre la pause si la vidéo est en pause
            this.interruptPause();
            
            this.setPose('encourageant', 2500);
            const messages = [
                'Pas grave, réessaie ! 💪',
                'Tu vas y arriver ! 🎯',
                'Courage, tu progresses ! 🌟',
                'Continue, tu es presque là ! 🔥',
                'N\'abandonne pas ! 💎',
                'Chaque erreur fait progresser ! 📈',
                'Tu peux le faire ! 💪',
                'Persévère, c\'est la clé ! 🔑',
                'C\'est en essayant qu\'on réussit ! 🎯',
                'Tu apprends de chaque erreur ! 📚',
                'Ne te décourage pas ! 💪',
                'Tu progresses à chaque essai ! ⬆️'
            ];
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            this.showMessage(randomMessage, 3000);
        }

        /**
         * Réaction neutre/attente
         */
        think() {
            this.setPose('reflexion', 0);
            const messages = [
                'Réfléchis bien... 🤔',
                'Tu peux le faire ! 💭',
                'Prends ton temps... ⏱️',
                'Concentre-toi... 🎯'
            ];
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            this.showMessage(randomMessage, 2500);
        }

        /**
         * Affiche un encouragement aléatoire
         */
        showRandomEncouragement() {
            const messages = [
                'Tu es capable ! 💪',
                'Continue comme ça ! 🌟',
                'Je crois en toi ! ⭐',
                'Chaque erreur te fait progresser ! 📈',
                'Tu es un champion ! 🏆'
            ];
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            this.setPose('encourageant', 1500);
            // Utiliser la durée par défaut (6000ms) au lieu d'une durée fixe
            this.showMessage(randomMessage);
        }

        /**
         * Réaction pour succès majeur (exercice complété, badge obtenu, etc.)
         */
        majorCelebration() {
            // Interrompre la pause si la vidéo est en pause
            this.interruptPause();
            
            this.setPose('celebration', 3000);
            this.playSuccessVideo();
            const messages = [
                '🎊 FÉLICITATIONS ! 🎊',
                '🌟 TU ES GÉNIAL(E) ! 🌟',
                '🏆 EXCELLENT ! 🏆',
                '💎 BRAVO CHAMPION(NE) ! 💎'
            ];
            const randomMessage = messages[Math.floor(Math.random() * messages.length)];
            this.showMessage(randomMessage, 4000);
        }

        /**
         * Gère la fin d'une vidéo (pour les vidéos aléatoires)
         */
        handleVideoEnded() {
            // Cette fonction est maintenant utilisée uniquement pour les vidéos aléatoires
            // Le gestionnaire 'ended' dans playRandomVideo() gère le retour aux sprites
            console.log('✅ Vidéo terminée');
        }

        /**
         * Joue la vidéo spéciale de succès puis revient à la vidéo de base
         */
        playSuccessVideo() {
            if (!this.videoElement || !this.successVideoSrc) {
                return;
            }

            try {
                // Arrêter la logique de boucle automatique le temps de l'animation spéciale
                this.videoElement.removeEventListener('ended', this.boundHandleVideoEnded);

                // Sauvegarder le chemin de base pour restauration
                const originalBasePath = this.baseVideoSrc;

                const onSuccessEnded = () => {
                    this.videoElement.removeEventListener('ended', onSuccessEnded);
                    // Revenir à la vidéo de base et relancer le cycle normal
                    this.videoElement.innerHTML = '';
                    this.setupVideoSources(originalBasePath);
                    this.videoElement.load(); // Recharger les sources
                    this.videoLoopCount = 0;
                    this.isPaused = false;
                    this.videoElement.addEventListener('ended', this.boundHandleVideoEnded);
                    this.startVideoLoop();
                };

                this.videoElement.addEventListener('ended', onSuccessEnded);
                this.videoElement.currentTime = 0;
                
                // Configurer les sources pour la vidéo de succès
                this.videoElement.innerHTML = ''; // Vider les sources actuelles
                this.setupVideoSources(this.successVideoSrc);
                this.videoElement.load(); // Recharger les nouvelles sources
                
                // Gérer la promesse play() correctement selon les recommandations Chrome
                // Ne pas appeler pause() immédiatement après play()
                const playPromise = this.videoElement.play();
                
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            // La lecture de la vidéo de succès a commencé avec succès
                            // Ne pas appeler pause() ici
                        })
                        .catch(err => {
                            console.warn('Erreur lors de la lecture de la vidéo de succès:', err);
                            // En cas d'erreur, revenir au comportement normal
                            this.videoElement.removeEventListener('ended', onSuccessEnded);
                            this.videoElement.addEventListener('ended', this.boundHandleVideoEnded);
                            this.videoElement.src = originalSrc || this.baseVideoSrc;
                            this.startVideoLoop();
                        });
                }
            } catch (error) {
                console.error('Erreur dans playSuccessVideo:', error);
                // En cas d'erreur, s'assurer que la vidéo revient à l'état normal
                if (this.videoElement) {
                    this.videoElement.src = this.baseVideoSrc;
                    this.videoElement.addEventListener('ended', this.boundHandleVideoEnded);
                    this.startVideoLoop();
                }
            }
        }
        
        /**
         * Démarre une boucle de vidéo
         * Gère correctement la promesse play() pour éviter les erreurs DOMException
         */
        startVideoLoop() {
            if (this.videoElement && !this.isPaused) {
                this.videoElement.currentTime = 0;
                
                // Gérer la promesse play() correctement selon les recommandations Chrome
                const playPromise = this.videoElement.play();
                
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            // La lecture a commencé avec succès
                            // Ne pas appeler pause() immédiatement après play()
                        })
                        .catch(err => {
                            // La lecture a échoué (auto-play bloqué, etc.)
                            console.warn('Erreur lors de la lecture de la vidéo:', err);
                            // Ne pas afficher l'erreur si c'est juste un blocage d'auto-play
                            if (err.name !== 'NotAllowedError') {
                                console.error('Erreur de lecture vidéo:', err);
                            }
                        });
                }
            }
        }
        
        /**
         * Met en pause la vidéo
         * S'assure de ne pas interrompre une promesse play() en cours
         */
        pauseVideo() {
            if (this.videoElement) {
                this.isPaused = true;
                
                // Vérifier si la vidéo est en cours de lecture avant de mettre en pause
                // Cela évite d'interrompre une promesse play() qui n'a pas encore été résolue
                if (!this.videoElement.paused) {
                    this.videoElement.pause();
                }
                // Optionnel : masquer la vidéo ou afficher une image statique
                // Pour l'instant, on laisse la dernière frame visible
            }
        }
        
        /**
         * Reprend la vidéo
         */
        resumeVideo() {
            if (this.videoElement) {
                this.isPaused = false;
                this.videoLoopCount = 0; // Réinitialiser le compteur
                this.startVideoLoop();
            }
        }
        
        /**
         * Programme la reprise de la vidéo après 2 minutes
         */
        scheduleResume() {
            // Annuler toute reprise précédente
            if (this.resumeTimeout) {
                clearTimeout(this.resumeTimeout);
            }
            
            this.resumeTimeout = setTimeout(() => {
                this.resumeVideo();
            }, this.pauseDuration);
        }
        
        /**
         * Interrompt la pause programmée et reprend immédiatement
         * Utile quand l'utilisateur interagit avec un exercice
         */
        interruptPause() {
            if (this.resumeTimeout) {
                clearTimeout(this.resumeTimeout);
                this.resumeTimeout = null;
            }
            
            if (this.isPaused) {
                this.resumeVideo();
            }
        }

        /**
         * Initialise la liste des vidéos aléatoires selon le niveau
         */
        initRandomVideos(imageType) {
            // Utiliser le même baseUrl que celui détecté dans init()
            const normalizedBaseUrl = this.baseUrl ? this.baseUrl.replace(/\/$/, '') : '';
            // S'assurer qu'on a un slash au début si baseUrl n'est pas vide
            let cleanBaseUrl = normalizedBaseUrl;
            if (cleanBaseUrl && !cleanBaseUrl.startsWith('/')) {
                cleanBaseUrl = '/' + cleanBaseUrl;
            }
            
            // Fonction helper pour construire les chemins vidéo avec URL complète
            // Utilise window.location.origin pour garantir le bon fonctionnement en production
            const buildPath = (filename) => {
                // Construire le chemin relatif
                const relativePath = cleanBaseUrl 
                    ? `${cleanBaseUrl}/assets/img/coach/optimized/${filename}`
                    : `/assets/img/coach/optimized/${filename}`;
                // Nettoyer les doubles slashes (sauf après le protocole http:// ou https://)
                const cleanPath = relativePath.replace(/([^:]\/)\/+/g, '$1');
                // Retourner l'URL complète pour garantir le bon fonctionnement
                return window.location.origin + cleanPath;
            };
            
            if (imageType === 'realiste') {
                // Vidéos pour lycée (réaliste)
                this.randomVideos = [
                    buildPath('realistecelebration'),
                    buildPath('realisteencourager'),
                    buildPath('realistefelicitations'),
                    buildPath('realiste_volant')
                ];
            } else {
                // Vidéos pour collège (cartoon)
                this.randomVideos = [
                    buildPath('cartoondebutderxercice'),
                    buildPath('cartoonreponsecorrecte'),
                    buildPath('cartoon_volant')
                ];
            }
            
            console.log('🎬 Vidéos aléatoires initialisées:', {
                imageType: imageType,
                baseUrl: cleanBaseUrl,
                videos: this.randomVideos
            });
        }
        
        /**
         * Démarre la boucle de morph automatique des sprites
         */
        startSpriteMorphLoop() {
            if (this.spriteMorphInterval) {
                clearInterval(this.spriteMorphInterval);
            }
            
            // Charger le premier sprite aléatoire immédiatement
            const randomIndex = Math.floor(Math.random() * this.spritePoses.length);
            this.loadSprite(randomIndex);
            
            // Démarrer la boucle automatique uniquement si la page est visible
            if (this.isPageVisible) {
                this.spriteMorphInterval = setInterval(() => {
                    // Vérifier que la page est toujours visible et que la boucle n'est pas en pause
                    if (this.isPageVisible && !this.isSpriteLoopPaused) {
                        this.cycleSpriteMorph();
                    }
                }, this.spriteMorphDuration);
            }
            
            console.log('🔄 Boucle de morph des sprites démarrée (toutes les ' + (this.spriteMorphDuration / 60000) + ' minute(s))');
        }
        
        /**
         * Charge un sprite spécifique
         */
        loadSprite(index) {
            if (!this.spriteElement) {
                return;
            }
            
            const folder = this.imageType === 'realiste' ? 'realiste' : 'cartoon';
            const pose = this.spritePoses[index % this.spritePoses.length];
            // Utiliser des chemins absolus basés sur window.location.origin pour plus de fiabilité
            // Cela garantit que les chemins fonctionnent en production comme en local
            const basePath = this.baseUrl ? `${this.baseUrl}/assets` : '/assets';
            const relativePath = `${basePath}/img/coach/colibri-sprites/${folder}/colibri-${pose}.png`;
            // Utiliser l'URL complète pour garantir le bon fonctionnement
            const src = window.location.origin + relativePath;
            
            console.log('🖼️ Chargement sprite:', {
                pose: pose,
                folder: folder,
                baseUrl: this.baseUrl,
                relativePath: relativePath,
                src: src,
                origin: window.location.origin
            });
            
            // Précharger l'image avant de l'afficher
            const img = new Image();
            img.onload = () => {
                console.log('✅ Sprite chargé avec succès:', src);
                // Changer la source seulement quand l'image est chargée
                this.spriteElement.src = src;
                // Forcer un reflow pour s'assurer que le changement de src est pris en compte
                void this.spriteElement.offsetWidth;
                // Faire apparaître le nouveau sprite avec un fondu entrant progressif
                // La transition CSS gérera automatiquement l'animation
                this.spriteElement.style.opacity = '1';
            };
            img.onerror = () => {
                console.warn('⚠️ Erreur chargement sprite:', src);
                // En cas d'erreur, réafficher le sprite précédent
                this.spriteElement.style.opacity = '1';
            };
            img.src = src;
        }
        
        /**
         * Morph visuel avec les sprites PNG en fonction du niveau (cartoon / réaliste)
         * Fonction appelée automatiquement en boucle avec sélection aléatoire
         */
        cycleSpriteMorph() {
            if (!this.spriteElement || !this.baseUrl) {
                return;
            }

            // Choisir un sprite aléatoire (différent du précédent si possible)
            let newIndex;
            do {
                newIndex = Math.floor(Math.random() * this.spritePoses.length);
            } while (newIndex === this.spriteIndex && this.spritePoses.length > 1);
            
            this.spriteIndex = newIndex;
            
            // Effet de fondu sortant progressif
            this.spriteElement.style.opacity = '0';
            
            // Attendre que le fondu sortant soit terminé avant de charger le nouveau sprite
            // Utiliser la moitié de la durée de transition pour un effet plus fluide
            setTimeout(() => {
                this.loadSprite(this.spriteIndex);
                // Le fondu entrant se fera automatiquement grâce à la transition CSS
            }, this.spriteMorphTransitionDuration / 2); // Délai pour le fondu sortant (750ms)
        }
        
        /**
         * Démarre le système de vidéos aléatoires toutes les 5 minutes
         */
        startRandomVideoSystem() {
            if (this.randomVideoInterval) {
                clearInterval(this.randomVideoInterval);
            }
            
            // Première vidéo après 5 minutes (uniquement si page visible)
            const scheduleNextVideo = () => {
                if (this.isPageVisible) {
                    setTimeout(() => {
                        if (this.isPageVisible) {
                            this.playRandomVideo();
                        }
                        // Programmer la vidéo suivante
                        scheduleNextVideo();
                    }, this.randomVideoDelay);
                }
            };
            
            scheduleNextVideo();
            
            console.log('⏰ Système de vidéos aléatoires démarré (toutes les ' + (this.randomVideoDelay / 60000) + ' minutes)');
        }
        
        /**
         * Met en pause la boucle de morph des sprites (pendant une vidéo)
         */
        pauseSpriteMorphLoop() {
            if (this.spriteMorphInterval) {
                clearInterval(this.spriteMorphInterval);
                this.spriteMorphInterval = null;
            }
            this.isSpriteLoopPaused = true;
            console.log('⏸️ Boucle de sprites mise en pause');
        }
        
        /**
         * Reprend la boucle de morph des sprites (après une vidéo)
         */
        resumeSpriteMorphLoop() {
            this.isSpriteLoopPaused = false;
            // Redémarrer la boucle uniquement si la page est visible
            if (this.isPageVisible) {
                this.startSpriteMorphLoop();
            }
            console.log('▶️ Boucle de sprites reprise');
        }
        
        /**
         * Gère les changements de visibilité de la page (Page Visibility API)
         */
        handleVisibilityChange() {
            const wasVisible = this.isPageVisible;
            this.isPageVisible = !document.hidden;
            
            if (wasVisible && !this.isPageVisible) {
                // Page devenue invisible : arrêter les intervalles
                console.log('⏸️ Page invisible - Arrêt des animations');
                if (this.spriteMorphInterval) {
                    clearInterval(this.spriteMorphInterval);
                    this.spriteMorphInterval = null;
                }
            } else if (!wasVisible && this.isPageVisible) {
                // Page redevenue visible : reprendre les animations
                console.log('▶️ Page visible - Reprise des animations');
                // Ne reprendre que si la boucle n'est pas en pause (pas de vidéo en cours)
                if (!this.isSpriteLoopPaused) {
                    this.startSpriteMorphLoop();
                }
            }
        }
        
        /**
         * Joue une vidéo aléatoire
         */
        playRandomVideo() {
            if (!this.randomVideos || this.randomVideos.length === 0) {
                console.warn('⚠️ Aucune vidéo aléatoire disponible');
                return;
            }
            
            // Choisir une vidéo aléatoire
            const randomIndex = Math.floor(Math.random() * this.randomVideos.length);
            const videoPath = this.randomVideos[randomIndex];
            
            console.log('🎬 Lecture vidéo aléatoire:', {
                videoPath: videoPath,
                fullUrl: window.location.origin + videoPath,
                baseUrl: this.baseUrl,
                randomIndex: randomIndex,
                totalVideos: this.randomVideos.length
            });
            
            // Arrêter temporairement la boucle de sprites
            this.pauseSpriteMorphLoop();
            
            // Sauvegarder l'état actuel
            const wasSpriteVisible = this.spriteElement && this.spriteElement.style.opacity === '1';
            
            // Masquer temporairement les sprites
            if (this.spriteElement) {
                this.spriteElement.style.opacity = '0';
            }
            
            // Afficher et jouer la vidéo
            this.videoElement.style.display = 'block';
            this.videoElement.style.zIndex = '3';
            
            // Configurer les sources pour la vidéo aléatoire
            this.videoElement.innerHTML = '';
            this.setupVideoSources(videoPath);
            // ✅ Lazy loading : charger uniquement quand nécessaire
            this.videoElement.preload = 'auto'; // Charger maintenant qu'on va l'utiliser
            this.videoElement.load();
            
            // Gérer la fin de la vidéo
            const onVideoEnded = () => {
                this.videoElement.removeEventListener('ended', onVideoEnded);
                // Masquer la vidéo
                this.videoElement.style.display = 'none';
                // Reprendre la boucle de sprites
                this.resumeSpriteMorphLoop();
                // Réafficher les sprites avec un léger délai pour l'effet de transition
                setTimeout(() => {
                    if (this.spriteElement && wasSpriteVisible) {
                        this.spriteElement.style.opacity = '1';
                    }
                }, 300);
                console.log('✅ Vidéo aléatoire terminée, retour aux sprites');
            };
            
            // Attendre que la vidéo soit prête avant de la jouer
            const onCanPlay = () => {
                this.videoElement.removeEventListener('canplay', onCanPlay);
                // Jouer la vidéo avec gestion de l'autoplay
                const playPromise = this.videoElement.play();
                if (playPromise !== undefined) {
                    playPromise
                        .then(() => {
                            console.log('▶️ Vidéo aléatoire en lecture:', videoPath);
                        })
                        .catch(err => {
                            const error = this.videoElement.error;
                            console.error('❌ Erreur lecture vidéo aléatoire:', {
                                videoPath: videoPath,
                                fullUrl: window.location.origin + videoPath,
                                baseUrl: this.baseUrl,
                                error: err,
                                errorCode: error ? error.code : 'unknown',
                                errorMessage: error ? error.message : 'Erreur inconnue',
                                networkState: this.videoElement.networkState,
                                readyState: this.videoElement.readyState,
                                sources: Array.from(this.videoElement.querySelectorAll('source')).map(s => ({
                                    src: s.src,
                                    type: s.type
                                }))
                            });
                            // En cas d'erreur (autoplay bloqué, etc.), reprendre les sprites
                            this.resumeSpriteMorphLoop();
                            onVideoEnded();
                        });
                }
            };
            
            this.videoElement.addEventListener('ended', onVideoEnded);
            this.videoElement.addEventListener('canplay', onCanPlay);
            
            // Si la vidéo est déjà prête, déclencher canplay manuellement
            if (this.videoElement.readyState >= 3) { // HAVE_FUTURE_DATA ou HAVE_ENOUGH_DATA
                setTimeout(() => onCanPlay(), 100);
            }
        }

        /**
         * Destruction de l'instance
         */
        destroy() {
            if (this.animationTimeout) {
                clearTimeout(this.animationTimeout);
            }
            
            // Arrêter la boucle de morph des sprites
            if (this.spriteMorphInterval) {
                clearInterval(this.spriteMorphInterval);
                this.spriteMorphInterval = null;
            }
            
            // Arrêter le système de vidéos aléatoires
            if (this.randomVideoInterval) {
                clearInterval(this.randomVideoInterval);
                this.randomVideoInterval = null;
            }
            
            // Nettoyer les événements vidéo
            if (this.videoElement) {
                this.videoElement.removeEventListener('ended', this.boundHandleVideoEnded);
            }
            if (this.resumeTimeout) {
                clearTimeout(this.resumeTimeout);
            }
            if (this.videoElement) {
                this.videoElement.pause();
                this.videoElement.src = '';
            }
            if (this.mascotElement && this.mascotElement.parentNode) {
                this.mascotElement.parentNode.removeChild(this.mascotElement);
            }
        }
    }

    // Exporter globalement
    window.ColibriMascot = ColibriMascot;

    /**
     * Fonction helper pour créer rapidement une mascotte
     */
    window.createColibriMascot = function(container, options) {
        return new ColibriMascot(container, options);
    };

    /**
     * Auto-initialisation des mascottes avec data-colibri
     */
    document.addEventListener('DOMContentLoaded', function() {
        const mascotContainers = document.querySelectorAll('[data-colibri]');
        mascotContainers.forEach(container => {
            const pose = container.getAttribute('data-colibri-pose') || 'neutre';
            const size = container.getAttribute('data-colibri-size') || 'medium';
            const position = container.getAttribute('data-colibri-position') || 'inline';
            const message = container.getAttribute('data-colibri-message');
            const imageType = container.getAttribute('data-colibri-image') || 'auto';
            
            const mascot = new ColibriMascot(container, {
                pose,
                size,
                position,
                showSpeechBubble: !!message,
                imageType: imageType
            });

            if (message) {
                const duration = parseInt(container.getAttribute('data-colibri-duration')) || 3000;
                mascot.showMessage(message, duration);
            }

            // Stocker l'instance pour manipulation ultérieure
            container.colibriMascot = mascot;
        });

        // Intégration avec les feedbacks d'exercices
        integrateWithExerciseFeedback();
        
        // La mascotte globale est initialisée dans initGlobalColibriMascot()
    });

    /**
     * Intègre la mascotte avec les feedbacks d'exercices
     */
    function integrateWithExerciseFeedback() {
        // Observer les changements dans les feedbacks
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Vérifier si c'est un feedback de correction
                        const feedback = node.classList?.contains('qcm-feedback') ||
                                        node.classList?.contains('math-feedback') ||
                                        node.classList?.contains('conjugation-feedback') ||
                                        node.querySelector?.('.qcm-feedback, .math-feedback, .conjugation-feedback');
                        
                        if (feedback) {
                            handleExerciseFeedback(node);
                        }
                    }
                });
            });
        });

        // Observer le body pour les nouveaux feedbacks
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Écouter les événements personnalisés de feedback
        document.addEventListener('exercise:correct', function(e) {
            showMascotReaction('celebrate');
        });

        document.addEventListener('exercise:incorrect', function(e) {
            showMascotReaction('encourage');
        });

        document.addEventListener('exercise:completed', function(e) {
            showMascotReaction('majorCelebration');
        });
    }

    /**
     * Gère les feedbacks d'exercices
     */
    function handleExerciseFeedback(feedbackElement) {
        const feedback = feedbackElement.querySelector('.qcm-feedback, .math-feedback, .conjugation-feedback');
        if (!feedback) return;

        // Vérifier si le feedback indique une bonne ou mauvaise réponse
        const isCorrect = feedback.textContent.includes('✅') || 
                         feedback.textContent.includes('Correct') ||
                         feedback.classList.contains('correct');
        
        const isIncorrect = feedback.textContent.includes('❌') || 
                           feedback.textContent.includes('Incorrect') ||
                           feedback.classList.contains('incorrect');

        // Trouver ou créer une mascotte pour ce feedback
        let mascotContainer = feedback.closest('.exercise-card')?.querySelector('.colibri-mascot-container');
        
        if (!mascotContainer) {
            mascotContainer = document.createElement('div');
            mascotContainer.className = 'colibri-mascot-container';
            feedback.parentNode.insertBefore(mascotContainer, feedback);
            
            const mascot = new ColibriMascot(mascotContainer, {
                size: 'small',
                position: 'inline'
            });
            mascotContainer.colibriMascot = mascot;
        }

        const mascot = mascotContainer.colibriMascot;
        if (mascot) {
            if (isCorrect) {
                mascot.celebrate();
            } else if (isIncorrect) {
                mascot.encourage();
            }
        }
    }

    /**
     * Affiche une réaction de la mascotte globale (si elle existe)
     */
    function showMascotReaction(type) {
        const globalMascot = document.querySelector('.colibri-mascot-global');
        if (globalMascot && globalMascot.colibriMascot) {
            const mascot = globalMascot.colibriMascot;
            switch(type) {
                case 'celebrate':
                    mascot.celebrate();
                    break;
                case 'encourage':
                    mascot.encourage();
                    break;
                case 'majorCelebration':
                    mascot.majorCelebration();
                    break;
                case 'think':
                    mascot.think();
                    break;
            }
        }
    }

    /**
     * Initialise la mascotte globale qui vole sur toutes les pages
     */
    function initGlobalColibriMascot() {
        console.log('🐦 Initialisation de la mascotte globale Colibri...');
        console.log('🐦 Configuration détectée:', {
            baseUrl: typeof window.baseUrl !== 'undefined' ? window.baseUrl : 'NON DÉFINI',
            userLevel: typeof window.userLevel !== 'undefined' ? window.userLevel : 'NON DÉFINI',
            ColibriMascot: typeof ColibriMascot !== 'undefined' ? 'DÉFINI' : 'NON DÉFINI'
        });
        
        // Vérifier si la mascotte globale existe déjà
        let globalContainer = document.querySelector('.colibri-mascot-global');
        
        if (!globalContainer) {
            // Créer le container global
            globalContainer = document.createElement('div');
            globalContainer.className = 'colibri-mascot-global';
            document.body.appendChild(globalContainer);
            console.log('✅ Container de la mascotte globale créé et ajouté au body');
        } else {
            console.log('ℹ️ Container de la mascotte globale existe déjà');
        }

        // Vérifier si la mascotte existe déjà dans le container
        if (!globalContainer.querySelector('.colibri-mascot')) {
            // Créer la mascotte globale
            try {
                if (typeof ColibriMascot === 'undefined') {
                    console.error('❌ ColibriMascot n\'est pas défini !');
                    return;
                }
                
                console.log('🐦 Création de l\'instance ColibriMascot...');
                const mascot = new ColibriMascot(globalContainer, {
                    pose: 'neutre',
                    size: 'medium', // 120px pour la mascotte globale
                    position: 'float',
                    showSpeechBubble: true,
                    imageType: 'auto' // Détecte automatiquement cartoon/realiste
                });
                
                // Stocker l'instance pour manipulation ultérieure
                globalContainer.colibriMascot = mascot;
                console.log('✅ Mascotte globale créée avec succès');
                console.log('🐦 Type d\'image détecté:', mascot.imageType || 'NON DÉFINI');
                console.log('🐦 Vidéos configurées:', {
                    base: mascot.baseVideoSrc || 'NON DÉFINI',
                    success: mascot.successVideoSrc || 'NON DÉFINI'
                });

                // Animation de vol initiale
                setTimeout(() => {
                    mascot.setPose('encourageant', 2000);
                    setTimeout(() => {
                        mascot.setPose('neutre');
                    }, 2000);
                }, 500);
            } catch (error) {
                console.error('❌ Erreur lors de la création de la mascotte:', error);
                console.error('❌ Stack trace:', error.stack);
            }
        } else {
            console.log('ℹ️ Mascotte globale existe déjà dans le container');
        }

        // Écouter les événements d'exercices pour réagir automatiquement
        // Ces événements interrompent la pause et font réagir la mascotte
        document.addEventListener('exercise:correct', function(e) {
            const mascot = globalContainer.colibriMascot;
            if (mascot) {
                mascot.celebrate(); // Content quand exercice réussi
            }
        });

        document.addEventListener('exercise:incorrect', function(e) {
            const mascot = globalContainer.colibriMascot;
            if (mascot) {
                // Toujours encourager, même en cas d'erreur
                mascot.encourage(); // Encourageant quand exercice non réussi
            }
        });

        document.addEventListener('exercise:completed', function(e) {
            const mascot = globalContainer.colibriMascot;
            if (mascot) {
                mascot.majorCelebration(); // Grande célébration pour exercice complété
            }
        });
    }

    // Initialiser la mascotte globale au chargement
    // Utiliser plusieurs méthodes pour s'assurer que l'initialisation se fait
    function tryInitGlobalMascot() {
        if (typeof ColibriMascot !== 'undefined') {
            initGlobalColibriMascot();
        } else {
            // Réessayer après un court délai
            setTimeout(tryInitGlobalMascot, 100);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(tryInitGlobalMascot, 200);
        });
    } else {
        // Si déjà chargé, initialiser après un court délai pour être sûr que tout est prêt
        setTimeout(tryInitGlobalMascot, 200);
    }

})();

