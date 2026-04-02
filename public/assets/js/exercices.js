// === Bloc "Ton exercice à faire" dynamique ===

// ... (suppression du doublon matieresParNiveau, gardé uniquement en haut du fichier)

document.addEventListener("DOMContentLoaded", function () {
    // Mapping matières par niveau — déclaré EN PREMIER pour éviter le Temporal Dead Zone
    const matieresParNiveau = {
        "6eme": [
            "Mathématiques",
            "Français",
            "Physique-Chimie",
            "SVT",
            "Histoire-Géo",
            "Anglais",
            "Espagnol",
        ],
        "5eme": [
            "Mathématiques",
            "Français",
            "Physique-Chimie",
            "SVT",
            "Histoire-Géo",
            "Anglais",
            "Espagnol",
        ],
        "4eme": [
            "Mathématiques",
            "Français",
            "Physique-Chimie",
            "SVT",
            "Histoire-Géo",
            "Anglais",
            "Espagnol",
        ],
        "3eme": [
            "Mathématiques",
            "Français",
            "Physique-Chimie",
            "SVT",
            "Histoire-Géo",
            "Anglais",
            "Espagnol",
        ],
        "2nde": [
            "Mathématiques",
            "Français",
            "Physique-Chimie",
            "SVT",
            "Histoire-Géo",
            "Anglais",
            "Espagnol",
        ],
        "1ere": [
            "Mathématiques",
            "Français",
            "Physique-Chimie",
            "SVT",
            "Histoire-Géo",
            "Anglais",
            "Espagnol",
            "Philosophie",
        ],
        terminale: [
            "Mathématiques",
            "Français",
            "Physique-Chimie",
            "SVT",
            "Histoire-Géo",
            "Anglais",
            "Espagnol",
            "Philosophie",
        ],
        bac: [
            "Mathématiques",
            "Français",
            "Physique-Chimie",
            "SVT",
            "Histoire-Géo",
            "Anglais",
            "Espagnol",
            "Philosophie",
        ],
    };

    // Affichage dynamique des sections pour visiteurs
    function setStep(step) {
        // 0 : choix niveau, 1 : choix matière, 2 : exercice
        const niveauSection = document.getElementById("niveau-section");
        const matiereSection = document.getElementById("matiere-section");
        if (niveauSection)
            niveauSection.style.display = step === 0 ? "" : "none";
        if (matiereSection)
            matiereSection.style.display = step === 1 ? "" : "none";
        if (randomSection)
            randomSection.style.display = step === 2 ? "" : "none";
    }
    // Mode d'emploi dynamique
    const modeEmploiMsg = document.getElementById("mode-emploi-message");
    let etape = 0; // 0: rien, 1: niveau choisi, 2: matière choisie
    const randomSection = document.getElementById("random-exercise-section");
    const cardContainer = document.getElementById("random-exercise-card");
    const errorDiv = document.getElementById("random-exercise-error");
    const niveauGrid = document.getElementById("niveau-grid");
    const matiereSection = document.getElementById("matiere-section");
    const matiereList = document.getElementById("matiere-list");
    const btnRandom = document.getElementById("btn-random-exercise");
    let userLevel = null;
    let currentSubject = null;

    // Initialisation automatique pour les élèves connectés (niveau connu)
    if (window.EXERCICE_USER_LEVEL && matiereList) {
        userLevel = window.EXERCICE_USER_LEVEL;
        // Générer la liste des matières pour ce niveau
        matiereList.innerHTML = "";
        (matieresParNiveau[userLevel] || []).forEach((matiere) => {
            const mBtn = document.createElement("button");
            mBtn.className =
                "matiere-btn px-4 py-2 rounded-full bg-slate-100 text-slate-700 font-semibold shadow hover:bg-blue-100 transition";
            mBtn.textContent = matiere;
            mBtn.setAttribute("data-subject", matiere);
            mBtn.addEventListener("click", function () {
                currentSubject = matiere;
                etape = 2;
                setStep(2);
                if (modeEmploiMsg)
                    modeEmploiMsg.textContent =
                        "Un exercice de " +
                        userLevel +
                        " en " +
                        matiere +
                        " va s'afficher !";
                // Charger et ouvrir la modale
                let baseUrl =
                    typeof window.baseUrl !== "undefined" && window.baseUrl
                        ? window.baseUrl
                        : "";
                if (baseUrl.endsWith("/")) baseUrl = baseUrl.slice(0, -1);
                let url = `${baseUrl}/index.php?page=api/exercices/get_exercises&action=exercises&level=${encodeURIComponent(userLevel)}&subject=${encodeURIComponent(matiere)}`;
                fetch(url)
                    .then((r) =>
                        r
                            .clone()
                            .text()
                            .then((raw) => {
                                try {
                                    return JSON.parse(raw);
                                } catch (e) {
                                    throw e;
                                }
                            }),
                    )
                    .then((data) => {
                        let exercises = [];
                        if (Array.isArray(data.exercises))
                            exercises = data.exercises;
                        else if (
                            data.data &&
                            Array.isArray(data.data.exercises)
                        )
                            exercises = data.data.exercises;
                        if (!exercises.length) {
                            openCourseModal(
                                "<div class=\"text-red-600\">Aucun exercice disponible pour cette matière/niveau pour le moment.<br><button class='mt-4 px-4 py-2 bg-blue-600 text-white rounded' onclick='closeCourseModal()'>Essayer une autre matière</button></div>",
                                "Exercice",
                            );
                            return;
                        }
                        const ex =
                            exercises[
                                Math.floor(Math.random() * exercises.length)
                            ];
                        let htmlUrl = `${baseUrl}/index.php?page=api/exercices/get_exercises&action=exercise_html&id=${ex.Id}`;
                        fetch(htmlUrl)
                            .then((r2) => r2.json())
                            .then((data2) => {
                                let html = data2.html;
                                if (!html && data2.data && data2.data.html)
                                    html = data2.data.html;
                                openCourseModal(
                                    html ||
                                        '<div class="text-red-600">Erreur de rendu de l\'exercice.</div>',
                                    "Exercice",
                                );
                            })
                            .catch(() => {
                                openCourseModal(
                                    '<div class="text-red-600">Erreur lors du chargement de l\'exercice.</div>',
                                    "Exercice",
                                );
                            });
                    })
                    .catch(() => {
                        openCourseModal(
                            '<div class="text-red-600">Erreur lors du chargement des exercices.</div>',
                            "Exercice",
                        );
                    });
            });
            matiereList.appendChild(mBtn);
        });
        // Afficher directement la section matière
        if (matiereSection) matiereSection.style.display = "";
        if (niveauSection) niveauSection.style.display = "none";
        setStep(1);
    } else {
        // Initialisation : seuls les niveaux sont visibles
        setStep(0);
    }

    // Mapping matières par niveau déplacé en haut du bloc pour éviter le TDZ

    // Message initial
    if (cardContainer) {
        cardContainer.innerHTML =
            '<div class="text-blue-700 text-lg font-semibold py-8">Cliquez sur un niveau scolaire pour découvrir un exercice interactif adapté&nbsp;!</div>';
    }
    if (matiereSection) matiereSection.classList.add("hidden");
    if (errorDiv) errorDiv.classList.add("hidden");

    // Fonction pour charger un exercice
    function loadRandomExercise(level, subject = null) {
        userLevel = level;
        cardContainer.innerHTML =
            '<div class="text-slate-400 py-8">Chargement...</div>';
        errorDiv.classList.add("hidden");
        let baseUrl =
            typeof window.baseUrl !== "undefined" && window.baseUrl
                ? window.baseUrl
                : "";
        if (baseUrl.endsWith("/")) baseUrl = baseUrl.slice(0, -1);
        let url = `${baseUrl}/index.php?page=api/exercices/get_exercises&action=exercises&level=${encodeURIComponent(userLevel)}`;
        if (subject) url += `&subject=${encodeURIComponent(subject)}`;
        fetch(url)
            .then((r) =>
                r
                    .clone()
                    .text()
                    .then((raw) => {
                        try {
                            return JSON.parse(raw);
                        } catch (e) {
                            throw e;
                        }
                    }),
            )
            .then((data) => {
                let exercises = [];
                if (Array.isArray(data.exercises)) exercises = data.exercises;
                else if (data.data && Array.isArray(data.data.exercises))
                    exercises = data.data.exercises;
                if (!exercises.length) {
                    cardContainer.innerHTML = "";
                    errorDiv.textContent =
                        "Aucun exercice trouvé pour ce niveau.";
                    errorDiv.classList.remove("hidden");
                    return;
                }
                const ex =
                    exercises[Math.floor(Math.random() * exercises.length)];
                let htmlUrl = `${baseUrl}/index.php?page=api/exercices/get_exercises&action=exercise_html&id=${ex.Id}`;
                fetch(htmlUrl)
                    .then((r2) => r2.json())
                    .then((data2) => {
                        let html = data2.html;
                        if (!html && data2.data && data2.data.html)
                            html = data2.data.html;
                        cardContainer.innerHTML =
                            html ||
                            '<div class="text-red-600">Erreur de rendu de l\'exercice.</div>';
                    })
                    .catch(() => {
                        cardContainer.innerHTML =
                            '<div class="text-red-600">Erreur lors du chargement de l\'exercice.</div>';
                    });
            })
            .catch(() => {
                cardContainer.innerHTML = "";
                errorDiv.textContent =
                    "Erreur lors du chargement des exercices.";
                errorDiv.classList.remove("hidden");
            });
    }

    // Fonction de rendu des boutons matière pour un niveau donné
    function renderMatiereButtons(level) {
        if (!matiereList) return;
        matiereList.innerHTML = "";
        (matieresParNiveau[level] || []).forEach((matiere) => {
            const mBtn = document.createElement("button");
            mBtn.className =
                "matiere-btn px-4 py-2 rounded-full bg-slate-100 text-slate-700 font-semibold shadow hover:bg-blue-100 transition";
            mBtn.textContent = matiere;
            mBtn.setAttribute("data-subject", matiere);
            mBtn.addEventListener("click", function () {
                currentSubject = matiere;
                etape = 2;
                setStep(2);
                if (modeEmploiMsg)
                    modeEmploiMsg.textContent =
                        "Un exercice de " +
                        (userLevel || "ton niveau") +
                        " en " +
                        matiere +
                        " va s'afficher !";
                loadRandomExercise(userLevel, matiere);
            });
            matiereList.appendChild(mBtn);
        });
        if (matiereSection) matiereSection.style.display = "";
        const niveauSection = document.getElementById("niveau-section");
        if (niveauSection) niveauSection.style.display = "none";
    }

    // Clic sur un niveau scolaire (affiche les matières au lieu de recréer les listeners)
    if (niveauGrid) {
        niveauGrid.querySelectorAll(".niveau-btn").forEach((btn) => {
            btn.style.cursor = "pointer";
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                const level = btn.getAttribute("data-level");
                userLevel = level;
                etape = 1;
                setStep(1);
                if (modeEmploiMsg)
                    modeEmploiMsg.textContent =
                        "Étape 2 : Choisis une matière pour voir un exercice de " +
                        btn.textContent.trim() +
                        " !";
                renderMatiereButtons(level);
            });
        });
    }
    if (typeof btnNew !== "undefined" && btnNew) {
        btnNew.addEventListener("click", function () {
            if (userLevel) loadRandomExercise(userLevel, currentSubject);
        });
    }
});
// Logic de la page Exercices: étape 1
// - Désactiver "📝 Voir la correction" par défaut
// - L'activer après succès (100%) ou après 5 échecs de vérification
// - Stocker l'état par exercice via localStorage

(function () {
    "use strict";

    const ATTEMPT_KEY_PREFIX = "exercise_attempts_";
    const MAX_FAILS_TO_UNLOCK = 5;

    function getAttempts(exId) {
        try {
            const raw = localStorage.getItem(ATTEMPT_KEY_PREFIX + exId);
            return raw ? JSON.parse(raw) : { failures: 0, succeeded: false };
        } catch (e) {
            return { failures: 0, succeeded: false };
        }
    }

    function setAttempts(exId, data) {
        try {
            localStorage.setItem(
                ATTEMPT_KEY_PREFIX + exId,
                JSON.stringify(data),
            );
        } catch (e) {
            /* ignore */
        }
    }

    function disableCorrectionButton(card) {
        const btn = card.querySelector(".btn-show-answer");
        if (!btn) return;
        btn.disabled = true;
        btn.classList.add("disabled");
        btn.dataset.lockReason = "success_or_5_fails";
        btn.title = "Débloqué après succès ou 5 essais";
    }

    function enableCorrectionButton(card) {
        const btn = card.querySelector(".btn-show-answer");
        if (!btn) return;
        btn.disabled = false;
        btn.classList.remove("disabled");
        btn.removeAttribute("title");
    }

    function hideCompleteButton(card) {
        const btn = card.querySelector(".btn-exercise-complete");
        if (!btn) return;
        btn.style.display = "none";
        btn.dataset.lockReason = "need_100_percent";
    }

    function showCompleteButton(card) {
        const btn = card.querySelector(".btn-exercise-complete");
        if (!btn) return;
        btn.style.display = "";
        delete btn.dataset.lockReason;
    }

    function initCardState(card) {
        const exId = card.getAttribute("data-exercise-id");
        if (!exId) return;
        const attempts = getAttempts(exId);

        // État correction
        if (attempts.succeeded || attempts.failures >= MAX_FAILS_TO_UNLOCK) {
            enableCorrectionButton(card);
        } else {
            disableCorrectionButton(card);
        }

        // État bouton "Marquer comme terminé"
        if (attempts.succeeded) {
            showCompleteButton(card);
        } else {
            hideCompleteButton(card);
        }
    }

    function computeScore(card) {
        // Détecter type et calculer % correct
        // 1) Coloriage
        const coloring = card.querySelector(".word-coloring-exercise");
        if (coloring) {
            let correctMap = {};
            try {
                correctMap = JSON.parse(coloring.dataset.correct || "{}");
            } catch (e) {}
            const words = coloring.querySelectorAll(".coloring-word");
            let total = 0,
                ok = 0;
            words.forEach((w) => {
                const base = (w.dataset.word || "").toLowerCase();
                const expected = correctMap[base] || "";
                if (expected && expected !== "determinant") {
                    total++;
                    const type = w.dataset.wordType || "none";
                    const isCorrect =
                        (type === "blue" && expected === "nom") ||
                        (type === "green" && expected === "verbe") ||
                        (type === "red" && expected === "adjectif");
                    if (isCorrect) ok++;
                }
            });
            const pct = total > 0 ? Math.round((ok / total) * 100) : 0;
            return { pct, total };
        }

        // 2) Conjugaison
        const conj = card.querySelector(".conjugation-exercise");
        if (conj) {
            const inputs = conj.querySelectorAll("input[data-correct]");
            let ok = 0;
            inputs.forEach((inp) => {
                const user = (inp.value || "").trim().toLowerCase();
                const expected = (inp.dataset.correct || "")
                    .trim()
                    .toLowerCase();
                if (user && expected && user === expected) ok++;
            });
            const total = inputs.length;
            const pct = total > 0 ? Math.round((ok / total) * 100) : 0;
            return { pct, total };
        }

        // 3) Maths
        const math = card.querySelector(".math-exercise");
        if (math) {
            const inputs = math.querySelectorAll("input[data-correct]");
            let ok = 0;
            inputs.forEach((inp) => {
                const user = parseFloat(inp.value);
                const expected = parseFloat(inp.dataset.correct);
                if (
                    !isNaN(user) &&
                    !isNaN(expected) &&
                    Math.abs(user - expected) < 0.01
                )
                    ok++;
            });
            const total = inputs.length;
            const pct = total > 0 ? Math.round((ok / total) * 100) : 0;
            return { pct, total };
        }

        // 4) QCM
        const qcm = card.querySelector(".qcm-exercise");
        if (qcm) {
            const questions = qcm.querySelectorAll(".qcm-question");
            let ok = 0;
            let total = 0;
            questions.forEach((q) => {
                const selected = q.querySelector('input[type="radio"]:checked');
                const correctRadio = q.querySelector(
                    'input[data-correct="true"]',
                );
                if (correctRadio) {
                    total++;
                    if (selected && selected === correctRadio) ok++;
                }
            });
            const pct = total > 0 ? Math.round((ok / total) * 100) : 0;
            return { pct, total };
        }

        // Fallback: pas interactif -> 0%
        return { pct: 0, total: 0 };
    }

    function onVerifyClicked(evt) {
        const btn = evt.currentTarget;
        const card = btn.closest(".exercise-card");
        if (!card) return;

        // Laisser interactive-exercises.js faire sa vérification, puis évaluer (petit délai)
        setTimeout(() => {
            const exId = card.getAttribute("data-exercise-id");
            if (!exId) return;
            const attempts = getAttempts(exId);
            const { pct } = computeScore(card);

            if (pct === 100) {
                attempts.succeeded = true;
                setAttempts(exId, attempts);
                enableCorrectionButton(card);
                showCompleteButton(card); // Étape 2: montrer "Marquer comme terminé"
            } else {
                attempts.failures = (attempts.failures || 0) + 1;
                setAttempts(exId, attempts);
                if (attempts.failures >= MAX_FAILS_TO_UNLOCK) {
                    enableCorrectionButton(card);
                }
                // Pas de 100%: on garde le bouton "Marquer comme terminé" caché
            }
        }, 50);
    }

    function attachVerifyListeners(card) {
        const selectors = [
            ".btn-check-coloring",
            ".btn-check-conjugation",
            ".btn-check-qcm",
            ".btn-check-math",
        ];
        selectors.forEach((sel) => {
            card.querySelectorAll(sel).forEach((btn) => {
                btn.addEventListener("click", onVerifyClicked);
            });
        });
    }

    // Fallback minimal pour toggleAnswer / markExerciseComplete si non définies
    if (typeof window.toggleAnswer !== "function") {
        window.toggleAnswer = function (exerciseId) {
            const card = document.querySelector(
                `.exercise-card[data-exercise-id="${exerciseId}"]`,
            );
            if (!card) return;
            const btn = card.querySelector(".btn-show-answer");
            if (btn && btn.disabled) return; // Verrouillée
            const answer = card.querySelector(".exercise-answer");
            if (!answer) return;
            const visible =
                answer.style.display !== "none" && answer.offsetParent !== null;
            answer.style.display = visible ? "none" : "block";
            if (btn)
                btn.textContent = visible
                    ? "📝 Voir la correction"
                    : "👁️ Masquer la correction";
        };
    }

    if (typeof window.markExerciseComplete !== "function") {
        window.markExerciseComplete = function (exerciseId, correct = true) {
            // Sauvegarder via API
            const params = new URLSearchParams({
                exercise_id: exerciseId,
                correct: correct ? "1" : "0",
            });
            if (window.csrfToken) {
                params.append("csrf_token", window.csrfToken);
            }
            fetch("/api/exercices/save-progress", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-CSRF-Token": window.csrfToken || "",
                },
                body: params,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        const card = document.querySelector(
                            `.exercise-card[data-exercise-id="${exerciseId}"]`,
                        );
                        if (card) {
                            card.classList.add("exercise-completed");
                            const btn = card.querySelector(
                                ".btn-exercise-complete",
                            );
                            if (btn) {
                                btn.textContent = "✅ Terminé";
                                btn.style.background = "#10b981";
                                btn.disabled = true;
                            }
                        }
                    }
                })
                .catch(() => {});
        };
    }

    function initializeCard(card) {
        initCardState(card);
        attachVerifyListeners(card);
    }

    function initAll() {
        document.querySelectorAll(".exercise-card").forEach(initializeCard);

        // Observer les ajouts dynamiques (chargés via API)
        const list = document.getElementById("exercices-list") || document.body;
        const obs = new MutationObserver((mutations) => {
            mutations.forEach((m) => {
                m.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) return;
                    if (
                        node.classList &&
                        node.classList.contains("exercise-card")
                    ) {
                        initializeCard(node);
                    } else {
                        node.querySelectorAll &&
                            node
                                .querySelectorAll(".exercise-card")
                                .forEach(initializeCard);
                    }
                });
            });
        });
        obs.observe(list, { childList: true, subtree: true });
    }

    document.addEventListener("DOMContentLoaded", initAll);
})();
