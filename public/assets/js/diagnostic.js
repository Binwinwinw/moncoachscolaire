// diagnostic.js — Diagnostic initial MonCoachScolaire (MVP 2026)
// Version améliorée avec meilleur logging et gestion d'erreurs
console.log("✅ diagnostic.js chargé");

let currentQuizState = null;

function normalizeSchoolLevelValue(levelValue) {
    const normalized = String(levelValue || "")
        .trim()
        .toLowerCase()
        .replace(/é|è|ê/g, "e")
        .replace(/\s+/g, "");

    const aliases = {
        "6eme": "6eme",
        "5eme": "5eme",
        "4eme": "4eme",
        "3eme": "3eme",
        seconde: "2nde",
        "2nde": "2nde",
        premiere: "1ere",
        "1ere": "1ere",
        terminale: "terminale",
        bac: "bac",
    };

    return aliases[normalized] || normalized;
}

function initDiagnosticQuizAiModal() {
    const openButton = document.getElementById("btn-diagnostic-quiz-ia");
    const modal = document.getElementById("modal-diagnostic-quiz-ia");
    const closeButton = document.getElementById(
        "close-modal-diagnostic-quiz-ia",
    );
    const form = document.getElementById("form-diagnostic-quiz-ia");
    const result = document.getElementById("diagnostic-quiz-ia-result");

    if (!openButton || !modal || !closeButton || !form || !result) {
        return;
    }

    const levelSelect = document.getElementById("diagnostic-quiz-niveau");
    const levelContainer = document.getElementById(
        "diagnostic-quiz-niveau-container",
    );
    const subjectSelect = document.getElementById("diagnostic-quiz-matiere");
    const typeInput = document.getElementById("diagnostic-quiz-type");
    const diagnosticContext = window.diagnosticContext || {};

    const lockedLevel = normalizeSchoolLevelValue(
        window.requestLevel || window.userLevel || "",
    );

    const closeModal = function () {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        modal.setAttribute("aria-hidden", "true");
    };

    const openModal = function () {
        result.innerHTML = "";
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        modal.setAttribute("aria-hidden", "false");

        if (levelSelect) {
            const matchingLevelOption = levelSelect.querySelector(
                `option[value="${lockedLevel}"]`,
            );
            if (matchingLevelOption) {
                matchingLevelOption.selected = true;
            }

            if (lockedLevel && levelContainer) {
                levelContainer.style.display = "none";
            }

            if (lockedLevel) {
                levelSelect.disabled = true;
                levelSelect.setAttribute("aria-disabled", "true");
            }
        }

        if (subjectSelect) {
            const preferredSubject = String(
                window.requestSubject || window.userSubject || "",
            ).trim();
            if (preferredSubject) {
                const options = Array.from(subjectSelect.options);
                const matchingSubjectOption = options.find(
                    (option) =>
                        option.value.toLowerCase() ===
                        preferredSubject.toLowerCase(),
                );
                if (matchingSubjectOption) {
                    matchingSubjectOption.selected = true;
                }
            }
        }
    };

    openButton.addEventListener("click", openModal);
    closeButton.addEventListener("click", closeModal);

    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !modal.classList.contains("hidden")) {
            closeModal();
        }
    });

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const randomTypes = ["QCM", "vrai/faux", "QCU", "texte"];

        let niveau = lockedLevel;
        const matiere = subjectSelect ? subjectSelect.value.trim() : "";
        let type = typeInput ? typeInput.value.trim() : "";

        if (!niveau && levelSelect) {
            niveau = levelSelect.value.trim();
        }

        if (!type) {
            type = randomTypes[Math.floor(Math.random() * randomTypes.length)];
            if (typeInput) {
                typeInput.value = type;
            }
        }

        if (!niveau || !matiere) {
            result.innerHTML =
                '<div class="text-red-600">Merci de choisir un niveau et une matière.</div>';
            return;
        }

        result.innerHTML =
            '<div class="py-4 text-center text-slate-500">Génération du quiz en cours...</div>';

        try {
            const csrfToken = window.csrfToken || "";
            const generateResponse = await fetch(
                `${window.basePath || ""}/index.php?page=api/ia/generate_quiz`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-Token": csrfToken,
                    },
                    body: JSON.stringify({
                        niveau,
                        matiere,
                        type,
                        output_mode: "quiz",
                        context_page: "diagnostic",
                        child_user_id:
                            diagnosticContext.role === "parent" &&
                            diagnosticContext.selectedChildId
                                ? Number(diagnosticContext.selectedChildId)
                                : null,
                        csrf_token: csrfToken,
                    }),
                },
            );

            const responseText = await generateResponse.text();
            let generatedData;
            try {
                generatedData = JSON.parse(responseText);
            } catch (parseError) {
                throw new Error("Réponse JSON invalide du serveur");
            }

            if (
                !generateResponse.ok ||
                !generatedData ||
                !generatedData.success
            ) {
                throw new Error(
                    generatedData && generatedData.error
                        ? generatedData.error
                        : `Erreur serveur (${generateResponse.status})`,
                );
            }

            if (!generatedData.quiz_html) {
                throw new Error("Aucun quiz généré.");
            }

            result.innerHTML = generatedData.quiz_html;

            if (typeof window.InteractiveExercises !== "undefined") {
                window.InteractiveExercises.initAll();
            }

            if (
                Array.isArray(generatedData.questions) &&
                generatedData.questions.length > 0
            ) {
                const saveButton = document.createElement("button");
                saveButton.type = "button";
                saveButton.className =
                    "mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2 font-bold text-white transition hover:bg-blue-800";
                saveButton.innerHTML = "📂 Sauvegarder dans la bibliothèque";

                saveButton.addEventListener("click", async () => {
                    saveButton.disabled = true;
                    saveButton.innerHTML = "⌛ Sauvegarde en cours...";

                    try {
                        const csrfToken = window.csrfToken || "";
                        const saveResponse = await fetch(
                            `${window.basePath || ""}/index.php?page=api/ia/save_generated_quiz`,
                            {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-Token": csrfToken,
                                },
                                body: JSON.stringify({
                                    questions: generatedData.questions,
                                    level: generatedData.level,
                                    subject: generatedData.subject,
                                    csrf_token: csrfToken,
                                }),
                            },
                        );

                        const saveText = await saveResponse.text();
                        let saveData;
                        try {
                            saveData = JSON.parse(saveText);
                        } catch (parseError) {
                            throw new Error(
                                "Réponse JSON invalide lors de la sauvegarde",
                            );
                        }

                        if (
                            !saveResponse.ok ||
                            !saveData ||
                            !saveData.success
                        ) {
                            throw new Error(
                                saveData && saveData.error
                                    ? saveData.error
                                    : `Erreur serveur (${saveResponse.status})`,
                            );
                        }

                        saveButton.innerHTML = "✅ Quiz sauvegardé !";
                        saveButton.classList.remove(
                            "bg-blue-700",
                            "hover:bg-blue-800",
                        );
                        saveButton.classList.add(
                            "bg-green-600",
                            "hover:bg-green-700",
                        );
                    } catch (error) {
                        alert(
                            "Erreur lors de la sauvegarde : " + error.message,
                        );
                        saveButton.disabled = false;
                        saveButton.innerHTML =
                            "📂 Sauvegarder dans la bibliothèque";
                    }
                });

                result.appendChild(saveButton);
            }
        } catch (error) {
            result.innerHTML = `<div class="text-red-600">Erreur : ${escapeHtml(error.message || "Erreur inconnue")}</div>`;
        }
    });
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

function getDiagnosticIncorrectItems(results) {
    if (!Array.isArray(results)) {
        return [];
    }

    return results
        .filter((result) => result && result.is_correct === false)
        .slice(0, 4)
        .map((result) => ({
            question: result.question || "Question de diagnostic",
            user_answer: result.user_answer || "",
            correct_answer: result.expected_answer || "",
            official_correction: result.correction || "",
            question_type: result.type || "texte",
        }))
        .filter((item) => item.question && item.correct_answer);
}

function buildDiagnosticAiPayload() {
    if (!currentQuizState) {
        return null;
    }

    const incorrectItems = getDiagnosticIncorrectItems(
        currentQuizState.lastResults,
    );

    if (incorrectItems.length === 0) {
        return null;
    }

    return {
        exercise_id: 0,
        level: currentQuizState.level || window.userLevel || "",
        subject: currentQuizState.subject || window.userSubject || "",
        competence: currentQuizState.quizTitle || currentQuizState.title || "",
        official_correction: incorrectItems
            .map((item) => item.official_correction)
            .filter(Boolean)
            .join(" "),
        source: "diagnostic-quiz",
        incorrect_items: incorrectItems,
    };
}

function buildDiagnosticExplanationModalHtml(explanationData) {
    const steps = Array.isArray(explanationData.steps)
        ? explanationData.steps
        : [];
    const perQuestion = Array.isArray(explanationData.per_question)
        ? explanationData.per_question
        : [];

    const stepsHtml = steps
        .map((step) => `<li>${escapeHtml(step)}</li>`)
        .join("");
    const perQuestionHtml = perQuestion
        .map(
            (item, index) => `
                <div class="course-section">
                    <h3>❓ Question ${index + 1}</h3>
                    <div class="course-section-content">
                        <p><strong>Enoncé :</strong> ${escapeHtml(item.question || "")}</p>
                        <p><strong>Ta réponse :</strong> ${escapeHtml(item.your_answer || "Aucune réponse")}</p>
                        <p><strong>Bonne réponse :</strong> ${escapeHtml(item.correct_answer || "")}</p>
                        <p><strong>Explication :</strong> ${escapeHtml(item.explanation || "")}</p>
                    </div>
                </div>
            `,
        )
        .join("");

    let html = "";

    if (explanationData.summary) {
        html += `<div class="key-points"><h3>🎯 Résumé utile</h3><p>${escapeHtml(explanationData.summary)}</p></div>`;
    }
    if (explanationData.learning_objective) {
        html += `<div class="course-section"><h3>📘 Objectif</h3><div class="course-section-content">${escapeHtml(explanationData.learning_objective)}</div></div>`;
    }
    if (explanationData.mistake_pattern) {
        html += `<div class="example-box"><h3>🧠 Erreur probable</h3><p>${escapeHtml(explanationData.mistake_pattern)}</p></div>`;
    }
    if (stepsHtml) {
        html += `<div class="course-section"><h3>🪜 Comment refaire juste</h3><div class="course-section-content"><ol>${stepsHtml}</ol></div></div>`;
    }
    html += perQuestionHtml;
    if (explanationData.retry_tip) {
        html += `<div class="formula-box">💡 ${escapeHtml(explanationData.retry_tip)}</div>`;
    }
    if (explanationData.verification_question) {
        html += `<div class="course-section"><h3>✅ Vérifie ta compréhension</h3><div class="course-section-content">${escapeHtml(explanationData.verification_question)}</div></div>`;
    }

    return (
        html ||
        '<div class="course-section-content">Aucune explication disponible.</div>'
    );
}

function buildDiagnosticPreciseCourseModalHtml(courseData) {
    const keyPoints = Array.isArray(courseData.key_points)
        ? courseData.key_points
        : [];
    const methodSteps = Array.isArray(courseData.method_steps)
        ? courseData.method_steps
        : [];
    const commonPitfalls = Array.isArray(courseData.common_pitfalls)
        ? courseData.common_pitfalls
        : [];
    const references = Array.isArray(courseData.references)
        ? courseData.references
        : [];

    const keyPointsHtml = keyPoints
        .map((item) => `<li>${escapeHtml(item)}</li>`)
        .join("");
    const methodStepsHtml = methodSteps
        .map((item) => `<li>${escapeHtml(item)}</li>`)
        .join("");
    const commonPitfallsHtml = commonPitfalls
        .map((item) => `<li>${escapeHtml(item)}</li>`)
        .join("");
    const referencesHtml = references
        .map(
            (reference) =>
                `<li><a href="${escapeHtml(reference.url || "")}" target="_blank" rel="noopener">${escapeHtml(reference.title || "Ressource")}</a>${reference.source ? ` <span class="text-slate-600">(${escapeHtml(reference.source)})</span>` : ""}</li>`,
        )
        .join("");

    let html = "";

    if (courseData.summary) {
        html += `<div class="key-points"><h3>🎯 Résumé utile</h3><p>${escapeHtml(courseData.summary)}</p></div>`;
    }
    if (courseData.concept_focus) {
        html += `<div class="course-section"><h3>📘 Notion à retenir</h3><div class="course-section-content">${escapeHtml(courseData.concept_focus)}</div></div>`;
    }
    if (keyPointsHtml) {
        html += `<div class="course-section"><h3>🧩 Points clés</h3><div class="course-section-content"><ul>${keyPointsHtml}</ul></div></div>`;
    }
    if (methodStepsHtml) {
        html += `<div class="course-section"><h3>🪜 Méthode</h3><div class="course-section-content"><ol>${methodStepsHtml}</ol></div></div>`;
    }
    if (courseData.worked_example) {
        html += `<div class="example-box"><h3>✏️ Exemple guidé</h3><p>${escapeHtml(courseData.worked_example)}</p></div>`;
    }
    if (commonPitfallsHtml) {
        html += `<div class="course-section"><h3>🚫 Pièges à éviter</h3><div class="course-section-content"><ul>${commonPitfallsHtml}</ul></div></div>`;
    }
    if (courseData.practice_tip) {
        html += `<div class="formula-box">💡 ${escapeHtml(courseData.practice_tip)}</div>`;
    }
    if (courseData.verification_question) {
        html += `<div class="course-section"><h3>✅ Vérifie ta compréhension</h3><div class="course-section-content">${escapeHtml(courseData.verification_question)}</div></div>`;
    }
    if (referencesHtml) {
        html += `<div class="course-section"><h3>🔎 Ressources utiles</h3><div class="course-section-content"><ul>${referencesHtml}</ul></div></div>`;
    }

    return (
        html ||
        '<div class="course-section-content">Aucun mini-cours disponible.</div>'
    );
}

function ensureDiagnosticCourseModal() {
    let modal = document.getElementById("course-modal");
    if (modal) {
        return modal;
    }

    const wrapper = document.createElement("div");
    wrapper.innerHTML = `
        <div id="course-modal" class="modal">
            <div class="modal-overlay"></div>
            <div class="modal-content course-modal-content">
                <div class="modal-header">
                    <h2 id="course-title">📚 Cours</h2>
                    <button type="button" class="modal-close">✕</button>
                </div>
                <div class="modal-body" id="course-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary">J'ai compris ! 💪</button>
                </div>
            </div>
        </div>
    `;

    modal = wrapper.firstElementChild;
    if (!modal) {
        return null;
    }

    document.body.appendChild(modal);

    const closeModal = function () {
        modal.classList.remove("active");
        document.body.style.overflow = "";
    };

    const overlay = modal.querySelector(".modal-overlay");
    const closeButton = modal.querySelector(".modal-close");
    const footerButton = modal.querySelector(".modal-footer .btn");

    [overlay, closeButton, footerButton].forEach(function (element) {
        if (element) {
            element.addEventListener("click", closeModal);
        }
    });

    window.closeCourseModal = window.closeCourseModal || closeModal;

    return modal;
}

function openDiagnosticAiModal(html, title) {
    const modal = ensureDiagnosticCourseModal();

    if (
        typeof window.openCourseModal === "function" &&
        document.getElementById("course-modal")
    ) {
        window.openCourseModal(html, title);
        return;
    }

    if (!modal) {
        alert(title);
        return;
    }

    const titleNode = document.getElementById("course-title");
    const bodyNode = document.getElementById("course-body");

    if (titleNode) {
        titleNode.textContent = title || "Cours";
    }
    if (bodyNode) {
        bodyNode.innerHTML =
            html ||
            '<div class="course-section-content">Contenu indisponible.</div>';
    }

    modal.classList.add("active");
    document.body.style.overflow = "hidden";
}

async function requestDiagnosticAi(endpoint, payload) {
    const response = await fetch(endpoint, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": window.csrfToken || "",
        },
        body: JSON.stringify(
            Object.assign({}, payload, {
                csrf_token: window.csrfToken || "",
            }),
        ),
    });

    let data;
    try {
        data = await response.json();
    } catch (error) {
        throw new Error("Réponse JSON invalide du serveur");
    }

    if (!response.ok || !data || !data.success || !data.data) {
        throw new Error(
            data && data.error
                ? data.error
                : "Contenu pédagogique indisponible pour le moment",
        );
    }

    return data.data;
}

async function openDiagnosticExplanation() {
    const payload = buildDiagnosticAiPayload();
    if (!payload) {
        alert(
            "Aucune erreur exploitable trouvée pour générer une explication.",
        );
        return;
    }

    openDiagnosticAiModal(
        '<div class="course-section-content">⏳ Génération de l explication en cours...</div>',
        "Comprendre mes erreurs",
    );

    const data = await requestDiagnosticAi(
        `${window.basePath || ""}/index.php?page=api/ia/generate_exercise_explanation`,
        payload,
    );

    openDiagnosticAiModal(
        buildDiagnosticExplanationModalHtml(data),
        "Comprendre mes erreurs",
    );
}

async function openDiagnosticPreciseCourse() {
    const payload = buildDiagnosticAiPayload();
    if (!payload) {
        alert("Aucune erreur exploitable trouvée pour générer un mini-cours.");
        return;
    }

    openDiagnosticAiModal(
        '<div class="course-section-content">⏳ Génération du mini-cours en cours...</div>',
        "Mini-cours ciblé",
    );

    const data = await requestDiagnosticAi(
        `${window.basePath || ""}/index.php?page=api/ia/generate_precise_course`,
        payload,
    );

    openDiagnosticAiModal(
        buildDiagnosticPreciseCourseModalHtml(data),
        data.title || "Mini-cours ciblé",
    );
}

function getPoolHintText(totalPool) {
    if (Number.isNaN(totalPool) || totalPool <= 0) {
        return "";
    }

    if (totalPool <= 2) {
        return `⚠️ Pool très petit (${totalPool} quiz). Tu risques de retrouver les mêmes quiz très vite. On travaille à enrichir le contenu.`;
    }

    if (totalPool <= 5) {
        return `⚠️ Pool réduit (${totalPool} quiz). Les répétitions sont possibles rapidement, surtout pour ta matière.`;
    }

    if (totalPool <= 10) {
        return `ℹ️ Pool modéré (${totalPool} quiz). Tu auras de la nouveauté, mais surveille les répétitions sur plusieurs sessions.`;
    }

    return "";
}

function groupQuizIdsBySubject(quizRows) {
    const groups = {};
    if (!Array.isArray(quizRows)) {
        return groups;
    }

    quizRows.forEach((row) => {
        if (!row || typeof row !== "object") {
            return;
        }
        const subject = row.subject ? String(row.subject).trim() : "Autres";
        const id = Number(row.id);
        if (!id || id <= 0) {
            return;
        }
        if (!groups[subject]) {
            groups[subject] = [];
        }
        groups[subject].push(id);
    });

    Object.keys(groups).forEach((subject) => {
        groups[subject].sort((a, b) => a - b);
    });

    return groups;
}

function renderAvailableQuizIds(quizRows, subject) {
    if (!Array.isArray(quizRows) || quizRows.length === 0) {
        return "";
    }

    const grouped = groupQuizIdsBySubject(quizRows);
    const allSubjects = Object.keys(grouped);
    if (allSubjects.length === 0) {
        return "";
    }

    const subjectKey = subject ? String(subject).trim() : "";
    const selectedSubject =
        subjectKey && grouped[subjectKey] ? subjectKey : null;

    let html = `
        <div id="diagnostic-ids-suggestions" class="mt-4 text-sm text-slate-700">
            <p class="font-semibold mb-3">IDs disponibles pour ce niveau :</p>
    `;

    const renderIds = (subjectName, ids) => {
        const displayIds = ids.slice(0, 30).join(", ");
        const more = ids.length > 30 ? ` +${ids.length - 30} autres` : "";
        return `
            <div class="mb-2">
                <span class="font-semibold">${escapeHtml(subjectName)} :</span>
                <span class="block mt-1 text-slate-600">${escapeHtml(displayIds)}${escapeHtml(more)}</span>
            </div>
        `;
    };

    if (selectedSubject) {
        html += renderIds(selectedSubject, grouped[selectedSubject]);
    } else {
        allSubjects.sort();
        allSubjects.slice(0, 5).forEach((subjectName) => {
            html += renderIds(subjectName, grouped[subjectName]);
        });
        if (allSubjects.length > 5) {
            html += `<div class="text-xs text-slate-500">…et ${allSubjects.length - 5} autres matières.</div>`;
        }
    }

    html += "</div>";
    return html;
}

document.addEventListener("DOMContentLoaded", async () => {
    console.log("✅ DOMContentLoaded déclenché");
    initDiagnosticQuizAiModal();

    const app = document.getElementById("diagnostic-app");
    if (!app) return console.error("diagnostic-app non trouvé");

    const diagnosticContext = window.diagnosticContext || {};
    if (diagnosticContext.role === "parent") {
        if (diagnosticContext.hasLinkedChildren === false) {
            return;
        }
        if (diagnosticContext.needsSelection) {
            return;
        }
    }

    if (diagnosticContext.catalogAvailable === false) {
        return;
    }

    // Niveau synchronisé depuis PHP et la requête actuelle
    const level = window.requestLevel || window.userLevel || "6eme";
    const requestSubject = window.requestSubject || null;
    const urlParams = new URLSearchParams(window.location.search);
    const subject = urlParams.get("subject") || requestSubject || null;
    const childId = Number(diagnosticContext.selectedChildId || 0);
    const childIdQuery =
        diagnosticContext.role === "parent" && childId > 0
            ? `&child_id=${encodeURIComponent(String(childId))}`
            : "";

    // Vérifier que apiBasePath est défini
    if (!window.apiBasePath) {
        console.error("ERROR: window.apiBasePath non défini");
        console.log("Configuration disponible:", {
            userLevel: window.userLevel,
            basePath: window.basePath,
        });
        app.innerHTML = `
            <div class="text-center py-20 px-4">
                <div class="w-24 h-24 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <span class="text-4xl">⚠️</span>
                </div>
                <h2 class="text-2xl font-bold text-red-700 mb-4">Configuration manquante</h2>
                <p class="text-lg text-gray-600">window.apiBasePath non défini</p>
            </div>
        `;
        return;
    }

    const apiUrl =
        `${window.apiBasePath}/diagnostic.php?level=${encodeURIComponent(level)}` +
        (subject ? `&subject=${encodeURIComponent(subject)}` : "") +
        childIdQuery;
    const idsUrl =
        `${window.apiBasePath}/diagnostic.php?ids_only=1&level=${encodeURIComponent(
            level,
        )}` +
        (subject ? `&subject=${encodeURIComponent(subject)}` : "") +
        childIdQuery;

    console.log("INFO: Diagnostic page loading");
    console.log("INFO: Diagnostic IDs URL:", idsUrl);
    console.log("- User Level:", level);
    console.log("- API Base Path:", window.apiBasePath);
    console.log("- API URL:", apiUrl);
    console.log("- Subject:", subject || "none");
    console.log("- Child ID:", childId || "none");

    try {
        console.log("FETCH: Appel API...");
        const [response, idsResponse] = await Promise.all([
            fetch(apiUrl),
            fetch(idsUrl).catch((error) => {
                console.warn(
                    "WARNING: Impossible de charger la liste des IDs:",
                    error,
                );
                return null;
            }),
        ]);

        // Vérifier le statut HTTP
        if (!response.ok) {
            throw new Error(
                `Erreur HTTP ${response.status}: ${response.statusText}`,
            );
        }

        // Lire la réponse comme texte d'abord pour debug
        const responseText = await response.text();
        console.log(
            "RESPONSE: Réponse brute reçue (" + responseText.length + " bytes)",
        );
        console.log(
            "RESPONSE: Premiers 300 caractères:",
            responseText.substring(0, 300),
        );

        // Essayer de parser en JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error(
                "ERROR: Impossible de parser JSON:",
                parseError.message,
            );
            console.error("ERROR: Contenu:", responseText);
            throw new Error(
                `Réponse API invalide (JSON parse error): ${parseError.message}`,
            );
        }

        let idsData = null;
        if (idsResponse && idsResponse.ok) {
            try {
                const idsText = await idsResponse.text();
                idsData = JSON.parse(idsText);
            } catch (e) {
                console.warn("WARNING: Impossible de parser les IDs JSON:", e);
            }
        }

        console.log("SUCCESS: JSON parsé", data);

        // Récupérer les quiz (data.quiz ou data.data selon la structure)
        const quizArray = data.quiz || data.data || [];
        const availableQuizIds = idsData?.quiz || [];
        const recommendedQuizId = Number(data?.recommendation?.id || 0);
        const totalPool = Number(data?.total_pool || quizArray.length);

        const poolHintText = getPoolHintText(totalPool);

        console.log("INFO: Quiz array reçu:", {
            isArray: Array.isArray(quizArray),
            length: quizArray ? quizArray.length : "N/A",
            totalPool,
            poolHintText,
            type: typeof quizArray,
            content: quizArray,
        });

        if (!Array.isArray(quizArray)) {
            console.error("ERROR: data.quiz n'est pas un array:", quizArray);
            app.innerHTML = `
                <div class="text-center py-20 px-4">
                    <div class="w-24 h-24 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-4xl">⚠️</span>
                    </div>
                    <h2 class="text-2xl font-bold text-red-700 mb-4">Erreur format API</h2>
                    <p class="text-lg text-gray-600">Quiz reçue n'est pas une liste valide</p>
                    <p class="text-sm text-gray-500 mt-4">Type reçu: ${typeof quizArray}</p>
                </div>
            `;
            return;
        }

        if (quizArray.length === 0) {
            console.log("INFO: Aucun quiz trouvé pour le niveau: " + level);
            app.innerHTML = `
                <div class="text-center py-20 px-4">
                    <div class="w-24 h-24 bg-yellow-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-4xl">ℹ️</span>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Quiz bientôt disponible</h2>
                    <p class="text-xl text-gray-600 mb-8">Aucun quiz n'est encore publié pour le niveau <span class="font-bold text-blue-700">${level}</span>.</p>
                    <p class="text-lg text-gray-500 mb-8">Reviens bientôt pour découvrir tes diagnostics adaptés !</p>
                </div>
            `;
            return;
        }

        // Grille quiz
        // Déterminer le type de niveau pour le badge
        let levelType = "Collège"; // Par défaut
        let levelBadgeClass = "bg-blue-100 text-blue-800";

        const levelLower = level.toLowerCase();
        if (
            ["2nde", "seconde", "1ere", "premiere", "terminale"].includes(
                levelLower,
            )
        ) {
            levelType = "Lycée";
            levelBadgeClass = "bg-purple-100 text-purple-800";
        } else if (levelLower === "bac") {
            levelType = "BAC";
            levelBadgeClass = "bg-amber-100 text-amber-800";
        }

        const pageSize = 12;
        const totalPages = Math.max(1, Math.ceil(quizArray.length / pageSize));
        let currentPage = 1;

        function renderPagination() {
            if (totalPages <= 1) {
                return "";
            }

            const buttons = [];
            for (let page = 1; page <= totalPages; page++) {
                const isActive = page === currentPage;
                buttons.push(`
                    <button data-quiz-page="${page}" class="rounded-xl border px-4 py-2 text-sm font-semibold transition ${isActive ? "border-emerald-700 bg-emerald-700 text-white shadow" : "border-slate-300 bg-white text-slate-700 hover:border-emerald-400 hover:bg-emerald-50"}">
                        ${page}
                    </button>
                `);
            }

            return `
                <div class="mt-8 flex w-full flex-wrap items-center justify-center gap-2">
                    <span class="mr-2 text-sm font-semibold text-slate-600">Page ${currentPage}/${totalPages}</span>
                    ${buttons.join("")}
                </div>
            `;
        }

        function renderQuizPage() {
            const start = (currentPage - 1) * pageSize;
            const pagedQuiz = quizArray.slice(start, start + pageSize);

            let html = `
                <header class="mb-10 rounded-3xl border border-slate-200 bg-white/90 p-6 text-center shadow-sm md:p-8">
                    <h1 class="mb-4 text-4xl font-extrabold text-slate-900 md:text-5xl">
                        🩺 Diagnostics ${level.toUpperCase()}
                    </h1>
                    <p class="mb-3 text-lg font-semibold text-slate-700 md:text-2xl">${quizArray.length} quiz disponibles</p>
                    ${poolHintText ? `<p class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-base font-semibold text-amber-800">${escapeHtml(poolHintText)}</p>` : ""}
                    <div class="mb-4 flex flex-wrap justify-center gap-3">
                        <span class="rounded-full px-4 py-2 font-semibold shadow-sm ${levelBadgeClass}">${levelType}</span>
                        <span class="rounded-full bg-emerald-100 px-4 py-2 font-semibold text-emerald-800 shadow-sm">Test de niveau</span>
                    </div>
                    <div class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <p class="mb-2 text-sm font-semibold text-slate-700">Tu connais l'ID d'un quiz ? Lance-le directement :</p>
                        <div class="flex gap-2">
                            <input id="quiz-id-input" type="number" min="1" placeholder="Ex: 145"
                                   class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" />
                            <button id="quiz-id-start-btn" class="rounded-xl bg-emerald-700 px-5 py-3 font-semibold text-white transition hover:bg-emerald-800">
                                Ouvrir
                            </button>
                        </div>
                        <div id="diagnostic-ids-placeholder" class="mt-4"></div>
                    </div>
                </header>
            `;

            html += `
                <div class="mx-auto w-full max-w-screen-xl px-2 py-2">
                    <div class="flex w-full flex-col items-center rounded-3xl border border-slate-200 bg-white/90 p-3 shadow-sm md:p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 w-full">
            `;

            pagedQuiz.forEach((quiz) => {
                const isRecommended =
                    recommendedQuizId > 0 &&
                    Number(quiz.id) === recommendedQuizId;
                const recommendedBadge = isRecommended
                    ? '<span class="absolute left-4 top-4 rounded-full bg-emerald-700 px-3 py-1 text-xs font-semibold text-white shadow">⭐ Recommandé</span>'
                    : "";

                const recommendedCardClass = isRecommended
                    ? "border-emerald-300 ring-2 ring-emerald-100"
                    : "border-slate-200";

                html += `
                        <button onclick="startQuiz(${quiz.id}, '${quiz.title.replace(/'/g, "\\'").replace(/"/g, '\\"')}')"
                            class="group quiz-card relative flex h-full min-w-full flex-col justify-between overflow-hidden rounded-3xl border ${recommendedCardClass} bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                            ${recommendedBadge}
                            <span class="absolute right-4 top-4 rounded-full bg-slate-900 px-3 py-1 text-[0.65rem] font-semibold text-white shadow">ID ${quiz.id}</span>
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-700 text-white shadow">
                                    <span class="text-white font-bold text-lg">${quiz.id}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-xl font-bold leading-tight text-slate-900 group-hover:text-emerald-800">${quiz.title}</h3>
                                    <p class="mt-2 inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">${quiz.subject}</p>
                                </div>
                            </div>
                            <p class="mb-3 text-sm leading-snug text-slate-600">${quiz.description || "Diagnostic niveau " + level}</p>
                            <div class="mt-auto flex flex-wrap gap-2">
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[0.66rem] font-semibold text-emerald-800">${quiz.level}</span>
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[0.66rem] font-semibold text-amber-800">Quiz</span>
                            </div>
                        </button>
            `;
            });

            html += `
                        </div>
                        ${renderPagination()}
                    </div>
                </div>
            `;

            console.log(
                "SUCCESS: Contenu généré avec " +
                    pagedQuiz.length +
                    " quiz sur la page " +
                    currentPage +
                    "/" +
                    totalPages,
            );
            app.innerHTML = html;

            const pagerButtons = app.querySelectorAll("button[data-quiz-page]");
            pagerButtons.forEach((button) => {
                button.addEventListener("click", () => {
                    const nextPage = Number(
                        button.getAttribute("data-quiz-page") || "1",
                    );
                    if (
                        Number.isNaN(nextPage) ||
                        nextPage < 1 ||
                        nextPage > totalPages ||
                        nextPage === currentPage
                    ) {
                        return;
                    }
                    currentPage = nextPage;
                    renderQuizPage();
                    window.scrollTo({ top: 0, behavior: "smooth" });
                });
            });

            const input = app.querySelector("#quiz-id-input");
            const launchBtn = app.querySelector("#quiz-id-start-btn");
            async function fetchQuizFileById(requestedId) {
                const verifyUrl = `${window.apiBasePath}/quiz.php?id=${requestedId}&include_answers=1`;
                const response = await fetch(verifyUrl);
                if (!response.ok) {
                    return null;
                }

                const data = await response.json();
                if (!data || typeof data !== "object") {
                    return null;
                }
                if (data.success === false) {
                    return null;
                }
                if (!data.quiz || typeof data.quiz !== "object") {
                    return null;
                }

                return data;
            }

            const launchById = async () => {
                const requestedId = Number(input?.value || 0);
                if (!requestedId) {
                    alert("Entre un ID de quiz valide.");
                    return;
                }

                const selected = quizArray.find(
                    (item) => Number(item.id) === requestedId,
                );
                if (selected) {
                    startQuiz(
                        selected.id,
                        selected.title || `Quiz ${selected.id}`,
                    );
                    return;
                }

                const quizData = await fetchQuizFileById(requestedId);
                if (!quizData) {
                    alert(
                        `Quiz ID ${requestedId} introuvable dans src/data/quiz ou src/data/quiz_answers.`,
                    );
                    return;
                }

                if (
                    !Array.isArray(quizData.answers) ||
                    quizData.answers.length === 0
                ) {
                    alert(
                        `Quiz ID ${requestedId} existe mais le fichier de réponses est introuvable ou incomplet.`,
                    );
                    return;
                }

                startQuiz(
                    requestedId,
                    quizData.quiz?.title || `Quiz ${requestedId}`,
                );
            };

            const idsPlaceholder = app.querySelector(
                "#diagnostic-ids-placeholder",
            );
            if (idsPlaceholder) {
                idsPlaceholder.innerHTML = renderAvailableQuizIds(
                    availableQuizIds,
                    subject,
                );
            }

            if (launchBtn) {
                launchBtn.addEventListener("click", launchById);
            }
            if (input) {
                input.addEventListener("keydown", (event) => {
                    if (event.key === "Enter") {
                        launchById();
                    }
                });
            }
        }

        renderQuizPage();
    } catch (error) {
        console.error("CATCH ERROR:", error);
        console.error("Error stack:", error.stack);

        app.innerHTML = `
            <div class="text-center py-20 px-4">
                <div class="w-28 h-28 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl">
                    <span class="text-5xl">⚠️</span>
                </div>
                <h2 class="text-3xl font-bold text-red-600 mb-4">Chargement échoué</h2>
                <p class="text-lg text-gray-600 mb-8">${error.message}</p>
                <p class="text-sm text-gray-500 mb-8 max-w-2xl mx-auto bg-gray-100 p-4 rounded-lg font-mono">
                    ${error.stack ? error.stack.substring(0, 300) : "No stack trace"}
                </p>
                <button onclick="location.reload()" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg transition-all">
                    🔄 Réessayer
                </button>
            </div>
        `;
    }
});

// Quiz engine
async function startQuiz(contentId, title) {
    const app = document.getElementById("diagnostic-app");

    app.innerHTML = `
        <div class="min-h-[60vh] flex items-center justify-center p-8">
            <div class="text-center">
                <div class="mx-auto mb-8 h-20 w-20 animate-spin rounded-full border-4 border-slate-200 border-t-emerald-700"></div>
                <h2 class="mb-4 text-3xl font-bold text-slate-900">${title}</h2>
                <p class="text-xl text-slate-600">Chargement des questions...</p>
            </div>
        </div>
    `;

    try {
        const response = await fetch(
            `${window.apiBasePath}/quiz.php?id=${contentId}`,
        );
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error(
                    "Ce quiz est en cours de preparation. Reviens dans un instant, il arrive bientot.",
                );
            }
            throw new Error(
                "Le quiz est temporairement indisponible. Merci de reessayer dans quelques instants.",
            );
        }

        const quizData = await response.json();
        const questions = quizData.quiz?.questions || [];

        if (questions.length === 0) throw new Error("Quiz vide");

        currentQuizState = {
            contentId,
            title,
            totalQuestions: questions.length,
            startedAt: Date.now(),
        };

        // Questions UI
        let html = `
            <header class="sticky top-0 z-20 mb-8 border-b border-slate-200 bg-white/90 p-6 shadow-sm">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-slate-900">
                            ${title}
                        </h1>
                        <div class="flex items-center space-x-4 text-sm font-semibold">
                            <span>Progression: <span id="progress-count">0</span>/${questions.length}</span>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs text-emerald-800">Prêt</span>
                        </div>
                    </div>
                </div>
            </header>
        `;

        questions.forEach((question, index) => {
            const qIndex = index + 1;
            html += `
                <section class="question mb-10 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition hover:shadow-md">
                    <div class="flex items-start mb-8">
                        <div class="mr-6 flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-3xl bg-emerald-700 text-2xl font-bold text-white shadow">
                            Q${qIndex}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="mb-6 text-2xl font-semibold leading-tight text-slate-900">${question.question}</h3>
                        </div>
                    </div>

                    <div class="space-y-4">
                        ${renderQuestionInputs(question, index)}
                    </div>
                </section>
            `;
        });

        html += `
            <div class="sticky bottom-6 z-30 mx-4 rounded-3xl border border-emerald-200 bg-white/95 p-8 shadow-lg lg:mx-0">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6 text-lg font-semibold">
                        <span>✅ <span id="progress-count">0</span>/${questions.length} répondues</span>
                        <span class="text-xl font-bold text-emerald-700">Prêt à corriger !</span>
                    </div>
                    <button onclick="submitQuiz()"
                            class="group flex w-full items-center justify-center rounded-3xl bg-emerald-700 px-12 py-6 text-2xl font-black text-white shadow transition hover:bg-emerald-800">
                        <span class="mr-4">📊 Corriger mon diagnostic</span>
                        <span class="rounded-2xl bg-white/20 px-4 py-2 text-lg font-bold transition group-hover:bg-white/30">GO !</span>
                    </button>
                </div>
            </div>
        `;

        app.innerHTML = html;

        // Live progress
        const inputs = document.querySelectorAll('input[name^="q"]');
        inputs.forEach((input) => {
            input.addEventListener("change", updateProgress);
            input.addEventListener("input", updateProgress);
        });
        updateProgress();
    } catch (error) {
        console.error("Quiz load error:", error);
        app.innerHTML = `
            <div class="min-h-screen flex items-center justify-center p-8">
                <div class="max-w-md text-center">
                    <div class="mx-auto mb-8 flex h-32 w-32 items-center justify-center rounded-3xl bg-rose-100 shadow">
                        <span class="text-5xl font-bold">!</span>
                    </div>
                    <h2 class="mb-6 text-3xl font-bold text-slate-900">${title}</h2>
                    <p class="mb-8 text-xl font-semibold text-rose-700">${error.message}</p>
                    <p class="mb-12 text-slate-600">Vérifiez que src/data/quiz/${contentId}.json existe</p>
                    <button onclick="history.back()" class="rounded-2xl bg-emerald-700 px-8 py-4 font-semibold text-white shadow transition hover:bg-emerald-800">
                        ← Autre quiz
                    </button>
                </div>
            </div>
        `;
    }
}

function updateProgress() {
    const answered = Object.keys(collectAnswers()).length;
    const total =
        currentQuizState?.totalQuestions ||
        document.querySelectorAll(".question").length;
    const elements = document.querySelectorAll("#progress-count");
    elements.forEach((el) => {
        el.textContent = `${answered}`;
    });
}

async function submitQuiz() {
    if (!currentQuizState) {
        return;
    }

    const answers = collectAnswers();
    const answeredCount = Object.keys(answers).length;
    const totalQuestions = currentQuizState.totalQuestions || 1;

    if (answeredCount === 0) {
        alert(
            "Commence par repondre a quelques questions pour recevoir ton diagnostic.",
        );
        return;
    }

    const durationSeconds = Math.max(
        1,
        Math.round(
            (Date.now() - (currentQuizState.startedAt || Date.now())) / 1000,
        ),
    );

    // Préparer payload avec métadonnées de révision si applicable
    const payload = {
        quiz_id: currentQuizState.contentId,
        answers,
        duration_seconds: durationSeconds,
        csrf_token: window.csrfToken || "",
    };

    if (
        window.diagnosticContext &&
        window.diagnosticContext.role === "parent" &&
        window.diagnosticContext.selectedChildId
    ) {
        payload.child_user_id = Number(
            window.diagnosticContext.selectedChildId,
        );
    }

    if (currentQuizState.isReview && currentQuizState.originalAnswers) {
        payload.is_review = true;
        payload.original_answers = currentQuizState.originalAnswers;
        payload.reviewed_question_indexes =
            currentQuizState.reviewQuestionIndexes || [];
    }

    let responseData;
    try {
        const response = await fetch(
            `${window.apiBasePath}/diagnostic/submit.php`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-Token": window.csrfToken || "",
                },
                body: JSON.stringify(payload),
            },
        );

        const bodyText = await response.text();
        let parsed;
        try {
            parsed = JSON.parse(bodyText);
        } catch (parseError) {
            throw new Error("Reponse JSON invalide du serveur");
        }

        if (!response.ok || !parsed.success) {
            const apiMessage =
                parsed?.error ||
                parsed?.error?.message ||
                `HTTP ${response.status}`;
            throw new Error(apiMessage);
        }

        responseData = parsed.data || {};
    } catch (error) {
        console.error("Erreur soumission diagnostic:", error);
        alert(`Impossible de corriger le diagnostic: ${error.message}`);
        return;
    }

    const score = Number(responseData.score || 0);
    const oldScore =
        responseData.old_score !== undefined && responseData.old_score !== null
            ? Number(responseData.old_score)
            : null;
    const isReviewResult = responseData.is_review === true;
    const feedback = responseData.feedback || {};
    const xpGained = Number(responseData.xp_gained || 0);
    const xpTotal = Number(responseData.xp_total || 0);
    const detailedResults = Array.isArray(responseData.results)
        ? responseData.results
        : [];
    const strengths = Array.isArray(feedback.strengths)
        ? feedback.strengths
        : [];
    const toReview = Array.isArray(feedback.to_review)
        ? feedback.to_review
        : [];
    const diagnosticContext = window.diagnosticContext || {};
    const parentSwitchChildHtml =
        diagnosticContext.role === "parent" && diagnosticContext.canSwitchChild
            ? `
                <a href="${escapeHtml(diagnosticContext.diagnosticPageUrl || "?page=diagnostic")}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-6 py-4 font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                    Changer d’enfant
                </a>
            `
            : "";

    currentQuizState.lastResults = detailedResults;
    currentQuizState.level = responseData.level || currentQuizState.level || "";
    currentQuizState.subject =
        responseData.subject ||
        currentQuizState.subject ||
        window.userSubject ||
        "";
    currentQuizState.quizTitle =
        responseData.quiz_title ||
        currentQuizState.quizTitle ||
        currentQuizState.title ||
        "";

    // Stocker les réponses initiales pour révision future
    if (!isReviewResult) {
        currentQuizState.originalAnswers = answers;
        currentQuizState.originalScore = score;
    }

    document.body.style.overflow = "hidden";

    // Message conditionnel selon révision ou première tentative
    let scoreDisplay = `<h1 class="mb-6 text-5xl font-black text-emerald-700">
        ${score}/100
    </h1>`;

    if (isReviewResult && oldScore !== null) {
        const improvement = score - oldScore;
        const improvementText =
            improvement > 0
                ? `<span class="text-green-600">+${improvement.toFixed(1)} points</span>`
                : `<span class="text-orange-600">${improvement.toFixed(1)} points</span>`;

        scoreDisplay = `
            <div class="mb-6">
                <div class="text-sm font-semibold text-gray-600 mb-2">Score initial : ${oldScore}/100</div>
                <h1 class="mb-2 text-5xl font-black text-emerald-700">
                    ${score}/100
                </h1>
                <div class="text-2xl font-bold">${improvementText}</div>
            </div>
        `;
    }

    document.querySelector("#diagnostic-app").innerHTML = `
        <div class="min-h-screen flex items-center justify-center p-8">
            <div class="mx-auto max-w-2xl rounded-3xl border border-emerald-200 bg-white/95 p-12 text-center shadow-lg">
                <div class="mx-auto mb-8 flex h-32 w-32 items-center justify-center rounded-3xl bg-emerald-100 shadow ${isReviewResult ? "" : "animate-bounce"}">
                    <span class="text-5xl font-black">${isReviewResult ? "🎯" : "⭐"}</span>
                </div>
                ${scoreDisplay}
                <p class="mb-4 text-2xl font-semibold text-slate-700">${answeredCount}/${totalQuestions} reponses analysees</p>
                <p class="mb-8 text-lg text-slate-700">${escapeHtml(feedback.message || "Bravo pour ton effort, continue comme ca !")}</p>
                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div class="rounded-2xl bg-emerald-100 p-6">
                        <span class="text-3xl font-bold text-emerald-700">✅</span>
                        <p class="font-bold text-lg mt-2">Points forts</p>
                        <p class="text-sm text-emerald-800">${strengths.length ? escapeHtml(strengths.join(", ")) : "Progression en cours"}</p>
                    </div>
                    <div class="rounded-2xl bg-amber-100 p-6">
                        <span class="text-3xl font-bold text-amber-700">⚠️</span>
                        <p class="font-bold text-lg mt-2">À revoir</p>
                        <p class="text-sm text-amber-800">${toReview.length ? escapeHtml(toReview.join(", ")) : "Tres bon niveau global"}</p>
                    </div>
                    <div class="rounded-2xl bg-sky-100 p-6">
                        <span class="text-3xl font-bold text-sky-700">📚</span>
                        <p class="font-bold text-lg mt-2">Progression</p>
                        <p class="text-sm text-sky-800">+${xpGained} XP (total ${xpTotal})</p>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <button onclick="retryQuiz()"
                            class="inline-flex flex-1 items-center justify-center rounded-3xl bg-amber-600 px-8 py-5 text-lg font-black text-white shadow transition hover:bg-amber-700">
                        🔄 Refaire tout le quiz
                    </button>
                    ${
                        toReview.length > 0
                            ? `
                    <button onclick="reviewFailedQuestions(${JSON.stringify(toReview).replace(/"/g, "&quot;")})"
                            class="inline-flex flex-1 items-center justify-center rounded-3xl bg-rose-700 px-8 py-5 text-lg font-black text-white shadow transition hover:bg-rose-800">
                        ⚠️ Revoir les questions ratées
                    </button>
                    `
                            : ""
                    }
                </div>
                ${
                    detailedResults.some(
                        (result) => result && result.is_correct === false,
                    )
                        ? `
                <div class="grid md:grid-cols-2 gap-4 mb-6">
                    <button data-ai-action="diagnostic-explanation" onclick="openDiagnosticExplanation().catch((error) => { console.error('Diagnostic explanation error:', error); alert(error.message || 'Explication indisponible'); })"
                            class="inline-flex items-center justify-center rounded-2xl bg-indigo-700 px-8 py-4 font-bold text-white shadow transition hover:bg-indigo-800">
                        💡 Comprendre mes erreurs
                    </button>
                    <button data-ai-action="diagnostic-precise-course" onclick="openDiagnosticPreciseCourse().catch((error) => { console.error('Diagnostic precise course error:', error); alert(error.message || 'Mini-cours indisponible'); })"
                            class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-8 py-4 font-bold text-white shadow transition hover:bg-black">
                        📘 Voir mon mini-cours ciblé
                    </button>
                </div>
                `
                        : ""
                }
                ${
                    score < 50
                        ? `
                <button onclick="showCorrectionsHelp()"
                        class="mb-6 inline-flex items-center justify-center rounded-2xl bg-violet-700 px-8 py-4 font-bold text-white shadow transition hover:bg-violet-800">
                    💡 Voir les corrections pour progresser
                </button>
                `
                        : ""
                }
                <a href="?page=exercices"
                   class="inline-flex items-center rounded-3xl bg-emerald-700 px-12 py-6 text-xl font-black text-white shadow transition hover:bg-emerald-800">
                    🚀 Commencer mes exercices adaptés
                    <span class="ml-4">→</span>
                </a>
                ${parentSwitchChildHtml ? `<div class="mt-4">${parentSwitchChildHtml}</div>` : ""}
            </div>
        </div>
    `;

    setTimeout(() => (document.body.style.overflow = "auto"), 100);
}

function retryQuiz() {
    if (!currentQuizState) {
        alert(
            "Impossible de relancer le quiz (état perdu). Retournez à la liste des quiz.",
        );
        return;
    }
    startQuiz(currentQuizState.contentId, currentQuizState.title);
}

async function showCorrectionsHelp() {
    if (!currentQuizState) {
        alert("Etat du quiz indisponible.");
        return;
    }

    if (
        Array.isArray(currentQuizState.lastResults) &&
        currentQuizState.lastResults.length > 0
    ) {
        const rows = currentQuizState.lastResults
            .filter((row) => row && row.is_correct === false)
            .slice(0, 8)
            .map((row, idx) => {
                const correction = escapeHtml(
                    row && row.correction
                        ? String(row.correction)
                        : "Correction non disponible.",
                );
                const yourAnswer = escapeHtml(
                    row && row.user_answer
                        ? String(row.user_answer)
                        : "Aucune réponse",
                );
                const expectedAnswer = escapeHtml(
                    row && row.expected_answer
                        ? String(row.expected_answer)
                        : "Réponse attendue non disponible",
                );
                return `<div class="p-4 border rounded-xl bg-gray-50 mb-3"><p class="font-bold mb-1">Q${idx + 1}</p><p class="text-sm text-gray-700 mb-2"><strong>Ta réponse :</strong> ${yourAnswer}</p><p class="text-sm text-gray-700 mb-2"><strong>Réponse attendue :</strong> ${expectedAnswer}</p><p class="text-sm text-gray-700">${correction}</p></div>`;
            })
            .join("");

        const app = document.getElementById("diagnostic-app");
        app.innerHTML = `
            <div class="max-w-4xl mx-auto p-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-2xl font-black mb-2">Corrections guidees</h2>
                    <p class="mb-6 text-slate-600">Lis les explications, puis refais le quiz pour gagner des points.</p>
                    ${rows || '<p class="text-gray-600">Aucune correction détaillée disponible.</p>'}
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button onclick="retryQuiz()" class="rounded-xl bg-emerald-700 px-5 py-3 font-semibold text-white transition hover:bg-emerald-800">🔄 Refaire le quiz</button>
                        <button data-ai-action="diagnostic-explanation" onclick="openDiagnosticExplanation().catch((error) => { console.error('Diagnostic explanation error:', error); alert(error.message || 'Explication indisponible'); })" class="rounded-xl bg-indigo-700 px-5 py-3 font-semibold text-white transition hover:bg-indigo-800">💡 Comprendre mes erreurs</button>
                        <button data-ai-action="diagnostic-precise-course" onclick="openDiagnosticPreciseCourse().catch((error) => { console.error('Diagnostic precise course error:', error); alert(error.message || 'Mini-cours indisponible'); })" class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white transition hover:bg-black">📘 Mini-cours ciblé</button>
                    </div>
                </div>
            </div>
        `;
        return;
    }

    try {
        const response = await fetch(
            `${window.apiBasePath}/quiz.php?id=${currentQuizState.contentId}&include_answers=1`,
        );
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const quizData = await response.json();
        const answersData =
            quizData.answers ||
            quizData.quiz_answers ||
            quizData.quiz?.answers ||
            [];

        if (!Array.isArray(answersData) || answersData.length === 0) {
            alert("Corrections indisponibles pour ce quiz.");
            return;
        }

        const rows = answersData
            .slice(0, 8)
            .map((row, idx) => {
                const correction = escapeHtml(
                    row && row.correction
                        ? String(row.correction)
                        : "Correction non disponible.",
                );
                return `<div class="p-4 border rounded-xl bg-gray-50 mb-3"><p class="font-bold mb-1">Q${idx + 1}</p><p class="text-sm text-gray-700">${correction}</p></div>`;
            })
            .join("");

        const app = document.getElementById("diagnostic-app");
        app.innerHTML = `
            <div class="max-w-4xl mx-auto p-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-2xl font-black mb-2">Corrections guidees</h2>
                    <p class="mb-6 text-slate-600">Lis les explications, puis refais le quiz pour gagner des points.</p>
                    ${rows}
                    <div class="mt-6 flex gap-3">
                        <button onclick="retryQuiz()" class="rounded-xl bg-emerald-700 px-5 py-3 font-semibold text-white transition hover:bg-emerald-800">🔄 Refaire le quiz</button>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        console.error("Erreur affichage corrections:", error);
        alert("Impossible de charger les corrections pour le moment.");
    }
}

function reviewFailedQuestions(toReviewLabels) {
    if (!currentQuizState) {
        alert(
            "Impossible de revoir les questions (état perdu). Retournez à la liste des quiz.",
        );
        return;
    }

    if (!currentQuizState.originalAnswers) {
        alert("Aucune réponse initiale trouvée. Refais le quiz complet.");
        retryQuiz();
        return;
    }

    const app = document.getElementById("diagnostic-app");

    app.innerHTML = `
        <div class="min-h-[60vh] flex items-center justify-center p-8">
            <div class="text-center">
                <div class="mx-auto mb-8 h-20 w-20 animate-spin rounded-full border-4 border-slate-200 border-t-rose-700"></div>
                <h2 class="mb-4 text-3xl font-bold text-slate-900">Révision ciblée</h2>
                <p class="text-xl text-slate-600">Chargement des questions à revoir...</p>
            </div>
        </div>
    `;

    fetch(`${window.apiBasePath}/quiz.php?id=${currentQuizState.contentId}`)
        .then((response) => response.json())
        .then((quizData) => {
            const allQuestions = quizData.quiz?.questions || [];

            // Extraire les numéros de questions depuis les labels (ex: "Q1", "Q2")
            const questionNumbers = toReviewLabels
                .map((label) => {
                    const match = label.match(/Q(\d+)/i);
                    return match ? parseInt(match[1], 10) - 1 : null;
                })
                .filter((n) => n !== null && n >= 0 && n < allQuestions.length);

            if (questionNumbers.length === 0) {
                throw new Error("Aucune question à revoir trouvée");
            }

            const questionsToReview = questionNumbers.map(
                (idx) => allQuestions[idx],
            );

            // Mettre à jour l'état pour la révision
            currentQuizState.totalQuestions = questionsToReview.length;
            currentQuizState.startedAt = Date.now();
            currentQuizState.isReview = true;
            currentQuizState.reviewQuestionIndexes = questionNumbers;

            renderReviewQuiz(questionsToReview, toReviewLabels);
        })
        .catch((error) => {
            console.error("Erreur chargement révision:", error);
            app.innerHTML = `
                <div class="min-h-screen flex items-center justify-center p-8">
                    <div class="max-w-md text-center">
                        <div class="w-32 h-32 bg-gradient-to-br from-red-400 to-red-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                            <span class="text-5xl font-bold">!</span>
                        </div>
                        <h2 class="mb-6 text-3xl font-bold text-slate-900">Révision impossible</h2>
                        <p class="mb-8 text-xl font-semibold text-rose-700">${error.message}</p>
                        <button onclick="retryQuiz()" class="rounded-2xl bg-emerald-700 px-8 py-4 font-semibold text-white shadow transition hover:bg-emerald-800">
                            ← Refaire tout le quiz
                        </button>
                    </div>
                </div>
            `;
        });
}

function renderReviewQuiz(questions, labels) {
    const app = document.getElementById("diagnostic-app");

    let html = `
        <header class="sticky top-0 z-20 mb-8 border-b border-rose-100 bg-white/90 p-6 shadow-sm">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">
                            ${currentQuizState.title} - Révision ciblée
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">Questions à revoir : ${labels.join(", ")}</p>
                    </div>
                    <div class="flex items-center space-x-4 text-sm font-semibold">
                        <span>Progression: <span id="progress-count">0</span>/${questions.length}</span>
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs text-rose-800">Révision</span>
                    </div>
                </div>
            </div>
        </header>
    `;

    questions.forEach((question, index) => {
        const qIndex = index + 1;
        html += `
            <section class="question mb-10 rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition hover:shadow-md">
                <div class="flex items-start mb-8">
                    <div class="mr-6 flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-3xl bg-rose-700 text-2xl font-bold text-white shadow">
                        ${labels[index] || `Q${qIndex}`}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="mb-6 text-2xl font-semibold leading-tight text-slate-900">${question.question}</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    ${renderQuestionInputs(question, index)}
                </div>
            </section>
        `;
    });

    html += `
        <div class="sticky bottom-6 z-30 mx-4 rounded-3xl border border-rose-200 bg-white/95 p-8 shadow-lg lg:mx-0">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-6 text-lg font-semibold">
                    <span>✅ <span id="progress-count">0</span>/${questions.length} répondues</span>
                    <span class="text-xl font-bold text-rose-700">Prêt à corriger !</span>
                </div>
                <button onclick="submitQuiz()"
                        class="group flex w-full items-center justify-center rounded-3xl bg-rose-700 px-12 py-6 text-2xl font-black text-white shadow transition hover:bg-rose-800">
                    <span class="mr-4">📊 Corriger ma révision</span>
                    <span class="rounded-2xl bg-white/20 px-4 py-2 text-lg font-bold transition group-hover:bg-white/30">GO !</span>
                </button>
            </div>
        </div>
    `;

    app.innerHTML = html;

    // Live progress
    const inputs = document.querySelectorAll('input[name^="q"]');
    inputs.forEach((input) => {
        input.addEventListener("change", updateProgress);
        input.addEventListener("input", updateProgress);
    });
    updateProgress();
}

function renderQuestionInputs(question, index) {
    const type = String(question.type || "").toLowerCase();
    const choiceList = Array.isArray(question.choices) ? question.choices : [];

    if (choiceList.length > 0) {
        return choiceList
            .map((choice) => {
                const safeChoice = escapeHtml(choice);
                return `
                <label class="group flex min-h-[80px] cursor-pointer items-center rounded-2xl border border-slate-300 p-6 transition hover:border-emerald-400 hover:bg-emerald-50">
                    <input type="radio" name="q${index}" value="${safeChoice}"
                           class="mr-6 h-7 w-7 border-2 border-slate-300 accent-emerald-600 shadow-sm focus:ring-2 focus:ring-emerald-200">
                    <span class="text-xl font-medium leading-relaxed text-slate-800 group-hover:text-emerald-900">${safeChoice}</span>
                </label>
            `;
            })
            .join("");
    }

    if (type === "vrai-faux" || type === "vrai_faux") {
        return ["Vrai", "Faux"]
            .map(
                (choice) => `
            <label class="group flex min-h-[80px] cursor-pointer items-center rounded-2xl border border-slate-300 p-6 transition hover:border-emerald-400 hover:bg-emerald-50">
                <input type="radio" name="q${index}" value="${choice.toLowerCase()}"
                       class="mr-6 h-7 w-7 border-2 border-slate-300 accent-emerald-600 shadow-sm focus:ring-2 focus:ring-emerald-200">
                <span class="text-xl font-medium leading-relaxed text-slate-800 group-hover:text-emerald-900">${choice}</span>
            </label>
        `,
            )
            .join("");
    }

    return `<div class="p-1">
        <input type="text" name="q${index}"
               class="min-h-[80px] w-full rounded-2xl border border-slate-300 p-6 text-xl font-semibold text-slate-800 shadow-sm transition placeholder-slate-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
               placeholder="Tape ta reponse exacte ici...">
    </div>`;
}

function collectAnswers() {
    const answers = {};

    document
        .querySelectorAll('input[type="radio"]:checked')
        .forEach((input) => {
            answers[input.name] = input.value;
        });

    document.querySelectorAll('input[type="text"]').forEach((input) => {
        const value = input.value.trim();
        if (value !== "") {
            answers[input.name] = value;
        }
    });

    return answers;
}
