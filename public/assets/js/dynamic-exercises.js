/**
 * Système dynamique d'affichage des exercices
 * - Affiche les matières sous forme de boutons
 * - Charge un exercice aléatoire à la fois
 * - Réussite = exercice suivant aléatoire
 * - Échec = même exercice à refaire
 */

(function () {
    "use strict";

    class DynamicExerciseSystem {
        constructor(options = {}) {
            this.options = Object.assign(
                {
                    containerSelector: ".dynamic-exercises-container",
                    level: null, // Niveau de l'élève (6ème, 5ème, etc.)
                    isAdmin: false, // Nouvel indicateur admin
                    apiEndpoint: DynamicExerciseSystem.getDefaultApiEndpoint(),
                },
                options,
            );
            // Initialisations déplacées ici
            this.currentSubject = null;
            this.currentExercise = null;
            this.availableExercises = [];
            this.completedExercises = new Set(); // IDs des exercices réussis
            this.failedExerciseId = null; // ID de l'exercice échoué à refaire

            // Gérer containerSelector qui peut être soit un string (sélecteur), soit un élément DOM
            if (typeof this.options.containerSelector === "string") {
                this.container = document.querySelector(
                    this.options.containerSelector,
                );
            } else if (this.options.containerSelector instanceof HTMLElement) {
                // Si c'est déjà un élément DOM, l'utiliser directement
                this.container = this.options.containerSelector;
            } else {
                // Essayer avec le sélecteur par défaut
                this.container = document.querySelector(
                    ".dynamic-exercises-container",
                );
            }

            if (!this.container) {
                console.error("DynamicExerciseSystem: Container not found");
                return;
            }

            // Détecter le niveau depuis l'URL ou le body SEULEMENT si pas passé en option
            // Si admin, ignorer la détection du niveau
            if (!this.options.isAdmin) {
                if (!this.options.level) {
                    this.options.level = this.detectLevel();
                } else {
                    // Valider et normaliser le niveau s'il est passé en option
                    console.log(
                        "✅ Niveau fourni en option:",
                        this.options.level,
                    );
                }
            } else {
                console.log(
                    "👑 Mode admin activé : accès à tous les exercices",
                );
            }

            this.init();
        }

        /**
         * Détermine dynamiquement le chemin de l'API selon l'environnement
         */
        static getDefaultApiEndpoint() {
            const baseUrl =
                typeof window.baseUrl === "string"
                    ? window.baseUrl.replace(/\/+$/, "")
                    : "";
            const base = baseUrl.replace(/\/public$/, "");
            return base + "/index.php?page=api/get_exercises";
        }

        /**
         * Construit une URL API valide même si apiEndpoint contient deja des query params.
         */
        buildApiUrl(params) {
            const endpoint = this.options.apiEndpoint || "";
            const separator = endpoint.includes("?") ? "&" : "?";
            return `${endpoint}${separator}${params}`;
        }

        /**
         * Détecte le niveau depuis l'URL ou le DOM
         */
        detectLevel() {
            // Vérifier window.userLevel si disponible
            if (typeof window.userLevel !== "undefined" && window.userLevel) {
                return window.userLevel;
            }

            // Vérifier l'URL
            const url = window.location.href;
            const levelPatterns = {
                "6ème": /6[èe]me|6eme/i,
                "5ème": /5[èe]me|5eme/i,
                "4ème": /4[èe]me|4eme/i,
                "3ème": /3[èe]me|3eme/i,
                Seconde: /seconde|2nde/i,
                Première: /premi[èe]re|1[èe]re/i,
                Terminale: /terminale/i,
                BAC: /bac/i,
            };

            for (const [level, pattern] of Object.entries(levelPatterns)) {
                if (pattern.test(url)) {
                    return level;
                }
            }

            // Vérifier les classes CSS
            const body = document.body;
            if (
                body.classList.contains("level-6eme") ||
                body.dataset.level === "6eme"
            )
                return "6ème";
            if (
                body.classList.contains("level-5eme") ||
                body.dataset.level === "5eme"
            )
                return "5ème";
            if (
                body.classList.contains("level-4eme") ||
                body.dataset.level === "4eme"
            )
                return "4ème";
            if (
                body.classList.contains("level-3eme") ||
                body.dataset.level === "3eme"
            )
                return "3ème";

            return "6ème"; // Par défaut
        }

        /**
         * Initialise le système
         */
        async init() {
            console.log("🎯 Initialisation du système d'exercices dynamique");
            console.log("📚 Niveau détecté:", this.options.level);
            console.log("🌐 API Endpoint:", this.options.apiEndpoint);

            if (!this.container) {
                console.error("❌ Container non trouvé");
                return;
            }

            // Charger les matières disponibles pour ce niveau
            await this.loadSubjects();
        }

        /**
         * Charge les matières disponibles et affiche les boutons
         */
        /**
         * Normalise le niveau pour matcher la base JSON (ex : '5ème' -> '5eme')
         */
        normalizeLevel(level) {
            if (!level) return "";
            // Retirer accents et adapter BAC
            const map = {
                "6ème": "6eme",
                "5ème": "5eme",
                "4ème": "4eme",
                "3ème": "3eme",
                Seconde: "Seconde",
                Première: "Premiere",
                Terminale: "Terminale",
                BAC: "Terminale",
            };
            // Si déjà sans accent, renvoyer tel quel
            if (Object.values(map).includes(level)) return level;
            // Sinon, chercher dans le mapping
            return (
                map[level] ||
                level
                    .replace(/[èéêë]/g, "e")
                    .replace(/[àâä]/g, "a")
                    .replace(/[îï]/g, "i")
                    .replace(/[ôö]/g, "o")
                    .replace(/[ûü]/g, "u")
            );
        }

        async loadSubjects() {
            try {
                let url;
                if (this.options.isAdmin) {
                    url = this.buildApiUrl("action=subjects&admin=1");
                    console.log("📡 Requête API (admin):", url);
                } else {
                    if (!this.options.level) {
                        throw new Error(
                            "Niveau non défini. Impossible de charger les matières.",
                        );
                    }
                    const normalizedLevel = this.normalizeLevel(
                        this.options.level,
                    );
                    url = this.buildApiUrl(
                        `action=subjects&level=${encodeURIComponent(normalizedLevel)}`,
                    );
                    console.log("📡 Requête API:", url);
                    console.log("📚 Niveau utilisé:", normalizedLevel);
                }

                const response = await fetch(url);

                // Lire le texte de la réponse UNE SEULE FOIS
                const text = await response.text();

                if (!response.ok) {
                    // Essayer de parser le message d'erreur
                    let errorMessage = `HTTP error! status: ${response.status}`;
                    console.error("📄 Réponse texte:", text);

                    // Essayer de parser en JSON pour extraire le message d'erreur
                    try {
                        const errorData = JSON.parse(text);
                        if (errorData.error) {
                            errorMessage += " - " + errorData.error;
                        }
                        if (errorData.debug) {
                            console.error("🐛 Debug info:", errorData.debug);
                        }
                    } catch (parseError) {
                        // Si ce n'est pas du JSON, utiliser le texte brut
                    }

                    throw new Error(errorMessage);
                }

                // Parser le texte en JSON
                const data = JSON.parse(text);
                console.log("📦 Réponse API:", data);

                if (data.success && data.subjects && data.subjects.length > 0) {
                    // Afficher tous les boutons, même si certains ont count=0
                    this.displaySubjectButtons(data.subjects);
                } else {
                    // Si la liste est vide (cas très rare), afficher l'erreur
                    this.displayError(
                        "Aucune matière disponible pour ce niveau.",
                    );
                }
            } catch (error) {
                console.error(
                    "❌ Erreur lors du chargement des matières:",
                    error,
                );
                console.error("📊 Détails:", {
                    level: this.options.level,
                    apiEndpoint: this.options.apiEndpoint,
                    error: error.message,
                    stack: error.stack,
                });
                // Extraire le code d'erreur HTTP du message
                let userMessage = "Erreur lors du chargement des matières";
                if (error.message.includes("HTTP error! status: 500")) {
                    userMessage =
                        "Erreur serveur - Veuillez réessayer plus tard";
                } else if (error.message.includes("HTTP error! status:")) {
                    userMessage = "Erreur de communication avec le serveur";
                }
                // Affichage positif même en cas d'absence de matières
                this.displayError(
                    "Aucune matière n’a été trouvée pour ce niveau pour le moment. N’hésitez pas à explorer d’autres matières ou à revenir plus tard !",
                );
            }
        }

        /**
         * Affiche les boutons de matières
         */
        displaySubjectButtons(subjects) {
            this.container.innerHTML = `
                <div class="dynamic-exercises-header">
                    <h2>📚 Choisissez une matière</h2>
                    <p>Sélectionnez la matière que vous souhaitez travailler :</p>
                </div>
                <div class="subjects-grid">
                    ${subjects
                        .map(
                            (subject) => `
                        <button class="subject-btn" data-subject="${this.escapeHtml(subject.name)}">
                            <span class="subject-icon">${subject.icon || "📚"}</span>
                            <span class="subject-name">${this.escapeHtml(subject.name)}</span>
                            <span class="subject-count">${subject.count} exercice${subject.count > 1 ? "s" : ""}</span>
                        </button>
                    `,
                        )
                        .join("")}
                </div>
                <div class="exercise-display-area" style="display: none;">
                    <!-- Zone d'affichage de l'exercice -->
                </div>
            `;

            // Ajouter les event listeners
            // Ajouter les event listeners uniquement sur les boutons actifs
            this.container.querySelectorAll(".subject-btn").forEach((btn) => {
                if (!btn.classList.contains("disabled")) {
                    btn.addEventListener("click", (e) => {
                        const subject = e.currentTarget.dataset.subject;
                        this.selectSubject(subject);
                    });
                }
            });
        }

        /**
         * Sélectionne une matière et charge les exercices
         */
        async selectSubject(subject) {
            this.currentSubject = subject;
            this.availableExercises = [];
            this.completedExercises.clear();
            this.failedExerciseId = null;

            // Mettre à jour l'UI
            this.container.querySelectorAll(".subject-btn").forEach((btn) => {
                btn.classList.remove("active");
                if (btn.dataset.subject === subject) {
                    btn.classList.add("active");
                }
            });

            // Afficher la zone d'exercice
            const exerciseArea = this.container.querySelector(
                ".exercise-display-area",
            );
            exerciseArea.style.display = "block";
            exerciseArea.innerHTML =
                '<div class="loading-exercises">📚 Chargement des exercices...</div>';

            // Charger les exercices de cette matière
            await this.loadExercisesForSubject(subject);

            // Afficher la sélection si exercices disponibles
            if (this.availableExercises.length > 0) {
                // Afficher le bouton Exercice aléatoire et la liste déroulante
                let selectOptions = this.availableExercises
                    .map(
                        (ex) =>
                            `<option value="${ex.Id}">${this.escapeHtml(ex.Title || "Exercice " + ex.Id)}</option>`,
                    )
                    .join("");
                exerciseArea.innerHTML = `
                    <div class="exercise-choice-bar">
                        <button class="btn-random-exercise">🎲 Exercice aléatoire</button>
                        <span style="margin-left:1em;">ou choisir un exercice précis :</span>
                        <select class="exercise-select">
                            <option value="">-- Sélectionner un exercice --</option>
                            ${selectOptions}
                        </select>
                        <button class="btn-show-exercise" disabled>Afficher</button>
                    </div>
                    <div class="exercise-display-selected"></div>
                `;

                // Gestion bouton aléatoire
                exerciseArea
                    .querySelector(".btn-random-exercise")
                    .addEventListener("click", () => {
                        this.displayRandomExercise();
                    });

                // Gestion sélection précise
                const select = exerciseArea.querySelector(".exercise-select");
                const btnShow =
                    exerciseArea.querySelector(".btn-show-exercise");
                select.addEventListener("change", () => {
                    btnShow.disabled = !select.value;
                });
                btnShow.addEventListener("click", () => {
                    const selectedId = select.value;
                    if (selectedId) {
                        const ex = this.availableExercises.find(
                            (e) => String(e.Id) === String(selectedId),
                        );
                        if (ex) {
                            this.renderExerciseInArea(
                                ex,
                                exerciseArea.querySelector(
                                    ".exercise-display-selected",
                                ),
                            );
                        }
                    }
                });
            } else {
                exerciseArea.innerHTML =
                    '<div class="no-exercises">Aucun exercice disponible pour cette matière.</div>';
            }
        }

        /**
         * Charge les exercices pour une matière donnée
         */
        async loadExercisesForSubject(subject) {
            try {
                let url;
                if (this.options.isAdmin) {
                    url = this.buildApiUrl(
                        `action=exercises&admin=1&subject=${encodeURIComponent(subject)}`,
                    );
                } else {
                    const normalizedLevel = this.normalizeLevel(
                        this.options.level,
                    );
                    url = this.buildApiUrl(
                        `action=exercises&level=${encodeURIComponent(normalizedLevel)}&subject=${encodeURIComponent(subject)}`,
                    );
                }

                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (
                    data.success &&
                    data.exercises &&
                    data.exercises.length > 0
                ) {
                    this.availableExercises = data.exercises;
                    console.log(
                        `✅ ${data.exercises.length} exercice(s) chargé(s) pour ${subject}`,
                    );
                } else {
                    this.availableExercises = [];
                }
            } catch (error) {
                console.error(
                    "Erreur lors du chargement des exercices:",
                    error,
                );
                this.availableExercises = [];
            }
        }

        /**
         * Affiche un exercice aléatoire (ou le même si échec précédent)
         */
        displayRandomExercise() {
            let exerciseToDisplay = null;

            // Si un exercice a échoué, afficher le même
            if (this.failedExerciseId !== null) {
                exerciseToDisplay = this.availableExercises.find(
                    (ex) => ex.Id == this.failedExerciseId,
                );
                if (!exerciseToDisplay) {
                    this.failedExerciseId = null;
                }
            }

            // Sinon, choisir un exercice aléatoire non complété
            if (!exerciseToDisplay) {
                const availableExercises = this.availableExercises.filter(
                    (ex) => !this.completedExercises.has(String(ex.Id)),
                );

                if (availableExercises.length === 0) {
                    this.displayAllCompleted();
                    return;
                }

                const randomIndex = Math.floor(
                    Math.random() * availableExercises.length,
                );
                exerciseToDisplay = availableExercises[randomIndex];
            }

            this.currentExercise = exerciseToDisplay;
            // Afficher dans la zone dédiée si sélecteur affiché
            const exerciseArea = this.container.querySelector(
                ".exercise-display-area",
            );
            const displaySelected = exerciseArea
                ? exerciseArea.querySelector(".exercise-display-selected")
                : null;
            if (displaySelected) {
                this.renderExerciseInArea(exerciseToDisplay, displaySelected);
            } else {
                this.renderExercise(exerciseToDisplay);
            }
        }

        // Nouvelle méthode pour afficher un exercice dans une zone spécifique
        renderExerciseInArea(exercise, area) {
            if (!area) return;
            area.innerHTML =
                '<div class="loading-exercise">Chargement de l\'exercice...</div>';
            this.loadExerciseHTML(exercise.Id)
                .then((html) => {
                    area.innerHTML = html;
                    setTimeout(() => {
                        this.initializeInteractiveExercises();
                        this.attachExerciseListeners();
                    }, 100);
                })
                .catch((error) => {
                    area.innerHTML =
                        '<div class="exercise-error">Erreur lors du chargement de l\'exercice.</div>';
                });
        }

        /**
         * Affiche un exercice
         */
        renderExercise(exercise) {
            const exerciseArea = this.container.querySelector(
                ".exercise-display-area",
            );

            // Charger l'exercice via l'API pour obtenir le HTML complet
            this.loadExerciseHTML(exercise.Id)
                .then((html) => {
                    console.log(
                        "📄 HTML de l'exercice chargé, insertion dans le DOM...",
                    );
                    exerciseArea.innerHTML = html;

                    // Attendre un peu pour que le DOM soit mis à jour
                    setTimeout(() => {
                        // Vérifier que le HTML contient les éléments interactifs
                        const hasQCM =
                            exerciseArea.querySelector(".qcm-exercise");
                        const hasMath =
                            exerciseArea.querySelector(".math-exercise");
                        const hasConjugation = exerciseArea.querySelector(
                            ".conjugation-exercise",
                        );
                        console.log(
                            "📊 Exercices détectés dans le HTML - QCM:",
                            !!hasQCM,
                            "Math:",
                            !!hasMath,
                            "Conjugation:",
                            !!hasConjugation,
                        );

                        if (hasQCM || hasMath || hasConjugation) {
                            // Initialiser les exercices interactifs
                            this.initializeInteractiveExercises();
                        } else {
                            console.warn(
                                "⚠️ Aucun exercice interactif détecté dans le HTML",
                            );
                        }

                        // Ajouter les event listeners pour les feedbacks
                        this.attachExerciseListeners();
                    }, 100);
                })
                .catch((error) => {
                    console.error(
                        "❌ Erreur lors du chargement de l'exercice:",
                        error,
                    );
                    // Fallback: utiliser le HTML de base
                    exerciseArea.innerHTML =
                        this.generateExerciseHTML(exercise);
                    this.initializeInteractiveExercises();
                    this.attachExerciseListeners();
                });
        }

        /**
         * Initialise les exercices interactifs
         */
        initializeInteractiveExercises() {
            console.log("🎮 Initialisation des exercices interactifs...");

            const exerciseArea = this.container.querySelector(
                ".exercise-display-area",
            );
            if (!exerciseArea) {
                console.warn("⚠️ Zone d'exercice non trouvée");
                return;
            }

            // Fonction helper pour réessayer l'initialisation
            const tryInit = (attempt = 0) => {
                const maxAttempts = 10; // Plus de tentatives

                if (attempt >= maxAttempts) {
                    console.warn(
                        "⚠️ Impossible d'initialiser les exercices interactifs après " +
                            maxAttempts +
                            " tentatives",
                    );

                    // Vérifier manuellement si les containers existent
                    const qcmExercises =
                        exerciseArea.querySelectorAll(".qcm-exercise");
                    const mathExercises =
                        exerciseArea.querySelectorAll(".math-exercise");
                    const conjugationExercises = exerciseArea.querySelectorAll(
                        ".conjugation-exercise",
                    );
                    console.log(
                        "📊 Containers trouvés - QCM:",
                        qcmExercises.length,
                        "Math:",
                        mathExercises.length,
                        "Conjugation:",
                        conjugationExercises.length,
                    );

                    return;
                }

                // Vérifier si les exercices sont présents dans le DOM
                const hasExercises =
                    exerciseArea.querySelectorAll(
                        ".qcm-exercise, .math-exercise, .conjugation-exercise",
                    ).length > 0;

                if (!hasExercises) {
                    console.log(
                        "⏳ Attente des exercices dans le DOM (tentative " +
                            (attempt + 1) +
                            ")...",
                    );
                    setTimeout(() => tryInit(attempt + 1), 200);
                    return;
                }

                // Méthode 1 : Utiliser InteractiveExercises.initAll si disponible
                if (typeof window.InteractiveExercises !== "undefined") {
                    if (
                        typeof window.InteractiveExercises.initAll ===
                        "function"
                    ) {
                        try {
                            console.log(
                                "🔄 Appel de InteractiveExercises.initAll()...",
                            );

                            // Appeler initAll pour initialiser tous les exercices
                            window.InteractiveExercises.initAll();

                            // Vérifier que les containers ont été remplis après un court délai
                            setTimeout(() => {
                                const qcmContainer =
                                    exerciseArea.querySelector(
                                        ".qcm-container",
                                    );
                                const mathContainer =
                                    exerciseArea.querySelector(
                                        ".math-container",
                                    );
                                const conjugationContainer =
                                    exerciseArea.querySelector(
                                        ".conjugation-container",
                                    );

                                const qcmFilled =
                                    qcmContainer &&
                                    qcmContainer.children.length > 0;
                                const mathFilled =
                                    mathContainer &&
                                    mathContainer.children.length > 0;
                                const conjFilled =
                                    conjugationContainer &&
                                    conjugationContainer.children.length > 0;

                                console.log(
                                    "📊 État des containers après initAll:",
                                );
                                console.log(
                                    "  - QCM rempli:",
                                    qcmFilled,
                                    "(enfants:",
                                    qcmContainer
                                        ? qcmContainer.children.length
                                        : 0,
                                    ")",
                                );
                                console.log(
                                    "  - Math rempli:",
                                    mathFilled,
                                    "(enfants:",
                                    mathContainer
                                        ? mathContainer.children.length
                                        : 0,
                                    ")",
                                );
                                console.log(
                                    "  - Conjugation rempli:",
                                    conjFilled,
                                    "(enfants:",
                                    conjugationContainer
                                        ? conjugationContainer.children.length
                                        : 0,
                                    ")",
                                );

                                if (qcmFilled || mathFilled || conjFilled) {
                                    console.log(
                                        "✅ Exercices interactifs initialisés - Containers remplis avec succès",
                                    );
                                } else {
                                    // Vérifier si les exercices existent mais n'ont pas de questions
                                    const qcmExercise =
                                        exerciseArea.querySelector(
                                            ".qcm-exercise",
                                        );
                                    const mathExercise =
                                        exerciseArea.querySelector(
                                            ".math-exercise",
                                        );
                                    const conjExercise =
                                        exerciseArea.querySelector(
                                            ".conjugation-exercise",
                                        );

                                    if (
                                        qcmExercise ||
                                        mathExercise ||
                                        conjExercise
                                    ) {
                                        console.warn(
                                            "⚠️ Exercices trouvés mais containers non remplis - possible problème de parsing des questions",
                                        );
                                        if (qcmExercise) {
                                            console.log(
                                                "  QCM data-questions:",
                                                qcmExercise.dataset.questions
                                                    ? "présent"
                                                    : "absent",
                                            );
                                        }
                                        if (mathExercise) {
                                            console.log(
                                                "  Math data-questions:",
                                                mathExercise.dataset.questions
                                                    ? "présent"
                                                    : "absent",
                                            );
                                        }
                                        if (conjExercise) {
                                            console.log(
                                                "  Conjugation data-questions:",
                                                conjExercise.dataset.questions
                                                    ? "présent"
                                                    : "absent",
                                            );
                                        }
                                    }
                                    setTimeout(() => tryInit(attempt + 1), 300);
                                }
                            }, 500);
                            return;
                        } catch (error) {
                            console.error(
                                "❌ Erreur lors de l'initialisation InteractiveExercises:",
                                error,
                            );
                            setTimeout(() => tryInit(attempt + 1), 200);
                        }
                    } else if (
                        typeof window.InteractiveExercises.init === "function"
                    ) {
                        try {
                            window.InteractiveExercises.init();
                            console.log(
                                "✅ Exercices interactifs initialisés via InteractiveExercises.init",
                            );
                            return;
                        } catch (error) {
                            console.warn(
                                "⚠️ Erreur lors de l'initialisation InteractiveExercises:",
                                error,
                            );
                            setTimeout(() => tryInit(attempt + 1), 200);
                        }
                    } else {
                        console.warn(
                            "⚠️ InteractiveExercises.initAll et init non disponibles",
                        );
                        setTimeout(() => tryInit(attempt + 1), 200);
                    }
                } else {
                    console.log(
                        "⏳ InteractiveExercises non encore disponible (tentative " +
                            (attempt + 1) +
                            ")...",
                    );
                    setTimeout(() => tryInit(attempt + 1), 200);
                }
            };

            // Démarrer l'initialisation après un court délai pour laisser le HTML se charger
            setTimeout(() => tryInit(), 300);
        }

        /**
         * Charge le HTML complet d'un exercice via l'API
         */
        async loadExerciseHTML(exerciseId) {
            const response = await fetch(
                this.buildApiUrl(`action=exercise_html&id=${exerciseId}`),
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.html) {
                return data.html;
            }

            throw new Error("Impossible de charger le HTML de l'exercice");
        }

        /**
         * Génère le HTML pour un exercice
         */
        generateExerciseHTML(exercise) {
            // Générer le HTML pour les cours liés
            let linkedCoursesHTML = "";
            if (exercise.LinkedCourses && exercise.LinkedCourses.length > 0) {
                linkedCoursesHTML = `
                    <div class="linked-courses-section">
                        <h4>📚 Cours associés</h4>
                        <div class="linked-courses-list">
                            ${exercise.LinkedCourses.map(
                                (course) => `
                                <a href="index.php?page=view_course&id=${course.Id}" class="linked-course-item" target="_blank">
                                    <span class="course-icon">📖</span>
                                    <span class="course-title">${this.escapeHtml(course.Title)}</span>
                                    <span class="course-badge">Cours n°${course.CourseNumber}</span>
                                </a>
                            `,
                            ).join("")}
                        </div>
                        <p class="linked-courses-hint">💡 Consulte ces cours pour mieux comprendre l'exercice !</p>
                    </div>
                `;
            }

            return `
                <div class="single-exercise-container">
                    <div class="exercise-card" data-exercise-id="${exercise.Id}">
                        <div class="exercise-header">
                            <h3 class="exercise-title">${this.escapeHtml(exercise.Title || "")}</h3>
                            <div class="exercise-meta">
                                <span class="exercise-subject">${this.escapeHtml(exercise.Subject || "")}</span>
                                <span class="exercise-level">${this.escapeHtml(exercise.Level || "")}</span>
                            </div>
                        </div>
                        ${linkedCoursesHTML}
                        <div class="exercise-content">
                            ${exercise.Content || ""}
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Attache les listeners pour détecter les réponses aux exercices
         */
        attachExerciseListeners() {
            // Observer les changements de feedback
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) {
                            this.checkForFeedback(node);
                        }
                    });
                });
            });

            const exerciseArea = this.container.querySelector(
                ".exercise-display-area",
            );
            if (exerciseArea) {
                observer.observe(exerciseArea, {
                    childList: true,
                    subtree: true,
                });
            }

            // Écouter les événements personnalisés
            document.addEventListener("exercise:correct", (e) => {
                this.handleExerciseSuccess();
            });

            document.addEventListener("exercise:incorrect", (e) => {
                this.handleExerciseFailure();
            });

            document.addEventListener("exercise:completed", (e) => {
                this.handleExerciseSuccess();
            });
        }

        /**
         * Vérifie les feedbacks dans le DOM
         */
        checkForFeedback(element) {
            const feedback = element.querySelector?.(
                ".qcm-feedback, .math-feedback, .conjugation-feedback",
            );
            if (!feedback) return;

            // Vérifier si c'est un feedback de succès ou d'échec
            const isCorrect =
                feedback.classList.contains("feedback-success") ||
                feedback.textContent.includes("✅") ||
                feedback.textContent.includes("Correct") ||
                feedback.textContent.includes("Bravo");

            const isIncorrect =
                feedback.classList.contains("feedback-needs-work") ||
                feedback.textContent.includes("❌") ||
                feedback.textContent.includes("Incorrect") ||
                feedback.textContent.includes("Réessaie");

            if (isCorrect) {
                this.handleExerciseSuccess();
            } else if (isIncorrect) {
                this.handleExerciseFailure();
            }
        }

        /**
         * Gère le succès d'un exercice
         */
        handleExerciseSuccess() {
            if (!this.currentExercise) return;

            console.log("✅ Exercice réussi:", this.currentExercise.Id);

            // Marquer comme complété
            this.completedExercises.add(String(this.currentExercise.Id));
            this.failedExerciseId = null; // Réinitialiser l'échec

            // Afficher un message de succès
            this.showSuccessMessage();

            // Attendre un peu puis afficher l'exercice suivant
            setTimeout(() => {
                this.displayRandomExercise();
            }, 2000);

            // Déclencher l'événement pour la mascotte
            document.dispatchEvent(
                new CustomEvent("exercise:correct", {
                    detail: { exerciseId: this.currentExercise.Id },
                }),
            );
        }

        /**
         * Gère l'échec d'un exercice
         */
        handleExerciseFailure() {
            if (!this.currentExercise) return;

            console.log("❌ Exercice échoué:", this.currentExercise.Id);

            // Sauvegarder l'ID de l'exercice échoué pour le refaire
            this.failedExerciseId = this.currentExercise.Id;

            // Afficher un message d'encouragement
            this.showEncouragementMessage();

            // L'exercice sera automatiquement réaffiché lors du prochain appel à displayRandomExercise
            // Pour l'instant, on laisse l'utilisateur réessayer

            // Déclencher l'événement pour la mascotte
            document.dispatchEvent(
                new CustomEvent("exercise:incorrect", {
                    detail: { exerciseId: this.currentExercise.Id },
                }),
            );
        }

        /**
         * Affiche un message de succès
         */
        showSuccessMessage() {
            const exerciseArea = this.container.querySelector(
                ".exercise-display-area",
            );
            const message = document.createElement("div");
            message.className = "exercise-success-message";
            message.innerHTML =
                "🎉 Excellent ! Passage à l'exercice suivant...";
            message.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                color: white;
                padding: 2rem 3rem;
                border-radius: 12px;
                font-size: 1.2rem;
                font-weight: 600;
                z-index: 10000;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                animation: fadeInOut 2s ease-in-out;
            `;

            document.body.appendChild(message);

            setTimeout(() => {
                if (document.body.contains(message)) {
                    message.remove();
                }
            }, 2000);
        }

        /**
         * Affiche un message d'encouragement
         */
        showEncouragementMessage() {
            const exerciseArea = this.container.querySelector(
                ".exercise-display-area",
            );
            const message = document.createElement("div");
            message.className = "exercise-encouragement-message";
            message.innerHTML =
                "💪 Pas grave ! Réessaie cet exercice, tu vas y arriver !";
            message.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                color: white;
                padding: 2rem 3rem;
                border-radius: 12px;
                font-size: 1.2rem;
                font-weight: 600;
                z-index: 10000;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                animation: fadeInOut 3s ease-in-out;
            `;

            document.body.appendChild(message);

            setTimeout(() => {
                if (document.body.contains(message)) {
                    message.remove();
                }
            }, 3000);
        }

        /**
         * Affiche le message "tous les exercices complétés"
         */
        displayAllCompleted() {
            const exerciseArea = this.container.querySelector(
                ".exercise-display-area",
            );
            exerciseArea.innerHTML = `
                <div class="all-completed-message">
                    <h3>🎉 Félicitations !</h3>
                    <p>Vous avez complété tous les exercices de ${this.currentSubject} !</p>
                    <button class="btn-select-another-subject" onclick="window.dynamicExerciseSystem?.resetToSubjects()">
                        Choisir une autre matière
                    </button>
                </div>
            `;
        }

        /**
         * Réinitialise et retourne à la sélection de matière
         */
        resetToSubjects() {
            this.currentSubject = null;
            this.currentExercise = null;
            this.availableExercises = [];
            this.completedExercises.clear();
            this.failedExerciseId = null;

            this.loadSubjects();
        }

        /**
         * Affiche un message d'erreur
         */
        displayError(message) {
            this.container.innerHTML = `
                <div class="exercise-error">
                    <p>❌ ${this.escapeHtml(message)}</p>
                </div>
            `;
        }

        /**
         * Échappe le HTML pour éviter les XSS
         */
        escapeHtml(text) {
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Exporter globalement
    window.DynamicExerciseSystem = DynamicExerciseSystem;

    // Auto-initialisation si data-dynamic-exercises est présent
    function autoInitDynamicExercises() {
        // Vérifier si déjà initialisé manuellement
        if (window.dynamicExerciseSystem) {
            console.log("ℹ️ Système déjà initialisé manuellement");
            return;
        }

        const container = document.querySelector("[data-dynamic-exercises]");
        if (container) {
            console.log(
                "🎯 Tentative d'initialisation automatique du système d'exercices dynamique...",
            );
            const level = container.dataset.level || null;
            // Détection dynamique du chemin API selon l'environnement
            let apiEndpoint = DynamicExerciseSystem.getDefaultApiEndpoint();
            try {
                window.dynamicExerciseSystem = new DynamicExerciseSystem({
                    containerSelector: "[data-dynamic-exercises]",
                    level: level,
                    apiEndpoint: apiEndpoint,
                });
                console.log(
                    "✅ Système d'exercices dynamique initialisé avec succès",
                );
            } catch (error) {
                console.error("❌ Erreur lors de l'initialisation:", error);
                container.innerHTML =
                    '<div class="exercise-error">Erreur lors de l\'initialisation du système. Veuillez recharger la page.</div>';
            }
        } else {
            console.warn("⚠️ Container [data-dynamic-exercises] non trouvé");
        }
    }

    // Initialiser dès que le DOM est prêt
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", autoInitDynamicExercises);
    } else {
        // Si déjà chargé, initialiser après un court délai
        setTimeout(autoInitDynamicExercises, 100);
    }

    // Exposer la fonction pour initialisation manuelle
    window.autoInitDynamicExercises = autoInitDynamicExercises;
})();
