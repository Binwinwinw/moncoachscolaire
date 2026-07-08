// Hub cours — miroir de exercices.js (bloc aléatoire + visiteur)

document.addEventListener("DOMContentLoaded", function () {
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

    const niveauSection = document.getElementById("niveau-section");
    const matiereSection = document.getElementById("matiere-section");
    const randomSection = document.getElementById("random-cours-section");
    const cardContainer = document.getElementById("random-cours-card");
    const errorDiv = document.getElementById("random-cours-error");
    const niveauGrid = document.getElementById("niveau-grid");
    const matiereList = document.getElementById("matiere-list");
    const btnRandom = document.getElementById("btn-new-random-cours");
    const modeEmploiMsg = document.getElementById("mode-emploi-message");

    let userLevel = null;
    let currentSubject = null;
    let etape = 0;

    function setStep(step) {
        if (niveauSection) {
            niveauSection.style.display = step === 0 ? "" : "none";
        }
        if (matiereSection) {
            matiereSection.style.display = step !== 0 ? "" : "none";
        }
        if (randomSection) {
            randomSection.style.display = step === 2 ? "" : "none";
        }
    }

    function getBaseUrl() {
        let baseUrl =
            typeof window.baseUrl !== "undefined" && window.baseUrl
                ? window.baseUrl
                : "";
        if (baseUrl.endsWith("/")) {
            baseUrl = baseUrl.slice(0, -1);
        }
        return baseUrl;
    }

    function loadRandomCourse(level, subject = null) {
        if (!cardContainer) return;

        userLevel = level;
        cardContainer.innerHTML =
            '<div class="text-slate-400 py-8">Chargement...</div>';
        if (errorDiv) errorDiv.classList.add("hidden");

        const baseUrl = getBaseUrl();
        let url = `${baseUrl}/index.php?page=api/cours/get_cours&action=cours&level=${encodeURIComponent(level)}`;
        if (subject) {
            url += `&subject=${encodeURIComponent(subject)}`;
        }

        fetch(url)
            .then((r) =>
                r.clone().text().then((raw) => {
                    try {
                        return JSON.parse(raw);
                    } catch (e) {
                        throw e;
                    }
                }),
            )
            .then((data) => {
                let cours = [];
                if (Array.isArray(data.cours)) cours = data.cours;
                else if (data.data && Array.isArray(data.data.cours)) {
                    cours = data.data.cours;
                }

                if (!cours.length) {
                    cardContainer.innerHTML = "";
                    if (errorDiv) {
                        const filterText = subject ? ` en ${subject}` : "";
                        errorDiv.textContent = `Aucun cours trouvé pour ce niveau${filterText}.`;
                        errorDiv.classList.remove("hidden");
                    }
                    return;
                }

                const picked =
                    cours[Math.floor(Math.random() * cours.length)];
                const courseId = picked.Id || picked.id;
                const htmlUrl = `${baseUrl}/index.php?page=api/cours/get_cours&action=cours_html&id=${courseId}`;

                fetch(htmlUrl)
                    .then((r2) => r2.json())
                    .then((data2) => {
                        let html = data2.html;
                        if (!html && data2.data && data2.data.html) {
                            html = data2.data.html;
                        }
                        cardContainer.innerHTML =
                            html ||
                            '<div class="text-red-600">Erreur de rendu du cours.</div>';
                        if (
                            typeof window.InteractiveCours !== "undefined" &&
                            window.InteractiveCours.initAll
                        ) {
                            window.InteractiveCours.initAll();
                        }
                    })
                    .catch(() => {
                        cardContainer.innerHTML =
                            '<div class="text-red-600">Erreur lors du chargement du cours.</div>';
                    });
            })
            .catch(() => {
                cardContainer.innerHTML = "";
                if (errorDiv) {
                    errorDiv.textContent =
                        "Erreur lors du chargement des cours.";
                    errorDiv.classList.remove("hidden");
                }
            });
    }

    window.loadRandomCourse = loadRandomCourse;

    function renderMatiereButtons(level) {
        if (!matiereList) return;
        matiereList.innerHTML = "";
        (matieresParNiveau[level] || []).forEach((matiere) => {
            const mBtn = document.createElement("button");
            mBtn.className =
                "matiere-btn subject-filter-btn px-4 py-2 rounded-full bg-slate-100 text-slate-700 font-semibold shadow hover:bg-blue-100 transition";
            mBtn.textContent = matiere;
            mBtn.setAttribute("data-subject", matiere);
            mBtn.addEventListener("click", function () {
                currentSubject = matiere;
                etape = 2;
                setStep(2);
                if (modeEmploiMsg) {
                    modeEmploiMsg.textContent =
                        "Un cours de " +
                        (userLevel || "ton niveau") +
                        " en " +
                        matiere +
                        " va s'afficher !";
                }
                loadRandomCourse(userLevel, matiere);
            });
            matiereList.appendChild(mBtn);
        });
        if (matiereSection) matiereSection.style.display = "";
        if (niveauSection) niveauSection.style.display = "none";
    }

    if (window.COURS_USER_LEVEL && matiereList) {
        userLevel = window.COURS_USER_LEVEL;
        matiereList.innerHTML = "";
        (matieresParNiveau[userLevel] || []).forEach((matiere) => {
            const mBtn = document.createElement("button");
            mBtn.className =
                "matiere-btn subject-filter-btn px-4 py-2 rounded-full bg-slate-100 text-slate-700 font-semibold shadow hover:bg-blue-100 transition";
            mBtn.textContent = matiere;
            mBtn.setAttribute("data-subject", matiere);
            mBtn.addEventListener("click", function () {
                currentSubject = matiere;
                etape = 2;
                setStep(2);
                if (modeEmploiMsg) {
                    modeEmploiMsg.textContent =
                        "Un cours de " +
                        userLevel +
                        " en " +
                        matiere +
                        " s'affichera ici.";
                }
                loadRandomCourse(userLevel, matiere);
            });
            matiereList.appendChild(mBtn);
        });
        if (matiereSection) matiereSection.style.display = "";
        if (niveauSection) niveauSection.style.display = "none";
        setStep(1);
        loadRandomCourse(userLevel);
    } else {
        setStep(0);
    }

    if (cardContainer) {
        cardContainer.innerHTML =
            '<div class="text-blue-700 text-lg font-semibold py-8">Cliquez sur un niveau scolaire pour découvrir un cours adapté&nbsp;!</div>';
    }
    if (!window.COURS_USER_LEVEL || !matiereList) {
        if (matiereSection) matiereSection.classList.add("hidden");
    }
    if (errorDiv) errorDiv.classList.add("hidden");

    if (niveauGrid) {
        niveauGrid.querySelectorAll(".niveau-btn").forEach((btn) => {
            btn.style.cursor = "pointer";
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                const level = btn.getAttribute("data-level");
                userLevel = level;
                etape = 1;
                setStep(1);
                if (modeEmploiMsg) {
                    modeEmploiMsg.textContent =
                        "Étape 2 : Choisis une matière pour voir un cours de " +
                        btn.textContent.trim() +
                        " !";
                }
                renderMatiereButtons(level);
            });
        });
    }

    if (btnRandom) {
        btnRandom.addEventListener("click", function () {
            if (userLevel) loadRandomCourse(userLevel, currentSubject);
        });
    }
});
