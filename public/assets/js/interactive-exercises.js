/**
 * Détecte dynamiquement le type d'exercice à partir du content/instruction
 * Retourne un type standardisé (graph, dragdrop, table, code, simulation, advanced-chronology, qcm, texte, etc.)
 */
function detectExerciseType(content, instruction) {
    const txt = (content + " " + instruction).toLowerCase();
    // Patterns extensibles
    if (
        /trace(r)?|placer|droite|triangle|figure|segment|cercle|géométrie/.test(
            txt,
        )
    )
        return "graph";
    if (/glisse|associe|relie|déplace|drag|drop|classe|range|ordre/.test(txt))
        return "dragdrop";
    if (/tableau|remplis|complète le tableau|case|cellule/.test(txt))
        return "table";
    if (/code|programme|algorithme|python|scratch|complète le code/.test(txt))
        return "code";
    if (/simul|manipul|expérience|variable|observe|expérimente/.test(txt))
        return "simulation";
    if (
        /chronolog|ligne du temps|timeline|événement|classement avancé/.test(
            txt,
        )
    )
        return "advanced-chronology";
    if (/qcm|choix multiple|coche|sélectionne/.test(txt)) return "qcm";
    if (/texte à trou|complète le texte|saisie libre|rédige|écris/.test(txt))
        return "texte";
    // Fallback
    return "texte";
}

/**
 * Système d'exercices interactifs pour MonCoachScolaire
 * Permet aux élèves de vraiment faire les exercices avant de voir la correction
 */

(function () {
    "use strict";

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function getClosestExerciseCard(exerciseElement) {
        return exerciseElement.closest(".exercise-card");
    }

    function getExerciseContext(exerciseElement) {
        const exerciseCard = getClosestExerciseCard(exerciseElement);
        const levelBadge = exerciseCard
            ? exerciseCard.querySelector(".exercise-level")
            : null;
        const competenceBadge = exerciseCard
            ? exerciseCard.querySelector(".exercise-competence")
            : null;
        const answerContent = exerciseCard
            ? exerciseCard.querySelector(".answer-content")
            : null;

        return {
            exerciseId: exerciseCard
                ? exerciseCard.dataset.exerciseId || ""
                : exerciseElement.dataset.exerciseId || "",
            level:
                exerciseElement.dataset.level ||
                (levelBadge ? levelBadge.textContent.trim() : ""),
            subject:
                exerciseElement.dataset.subject ||
                (exerciseCard ? exerciseCard.dataset.subject || "" : ""),
            competence: competenceBadge
                ? competenceBadge.textContent.trim()
                : "",
            officialCorrection: answerContent
                ? answerContent.textContent.trim()
                : "",
            source:
                exerciseElement.dataset.source ||
                (exerciseCard ? "library-exercise" : "dynamic-exercise"),
        };
    }

    function buildExplanationPayload(exerciseElement, incorrectItems) {
        const exerciseContext = getExerciseContext(exerciseElement);

        return {
            exercise_id: exerciseContext.exerciseId,
            level: exerciseContext.level,
            subject: exerciseContext.subject,
            competence: exerciseContext.competence,
            official_correction: exerciseContext.officialCorrection,
            source: exerciseContext.source,
            incorrect_items: incorrectItems,
        };
    }

    function buildPreciseCourseModalHtml(courseData) {
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
            .map(function (item) {
                return "<li>" + escapeHtml(item) + "</li>";
            })
            .join("");

        const methodStepsHtml = methodSteps
            .map(function (item) {
                return "<li>" + escapeHtml(item) + "</li>";
            })
            .join("");

        const commonPitfallsHtml = commonPitfalls
            .map(function (item) {
                return "<li>" + escapeHtml(item) + "</li>";
            })
            .join("");

        const referencesHtml = references
            .map(function (reference) {
                return (
                    "<li>" +
                    '<a href="' +
                    escapeHtml(reference.url || "") +
                    '" target="_blank" rel="noopener">' +
                    escapeHtml(reference.title || "Ressource") +
                    "</a>" +
                    (reference.source
                        ? ' <span class="text-slate-600">(' +
                          escapeHtml(reference.source) +
                          ")</span>"
                        : "") +
                    "</li>"
                );
            })
            .join("");

        let html = "";

        if (courseData.summary) {
            html +=
                '<div class="key-points"><h3>🎯 Résumé utile</h3><p>' +
                escapeHtml(courseData.summary) +
                "</p></div>";
        }

        if (courseData.concept_focus) {
            html +=
                '<div class="course-section"><h3>📘 Notion à retenir</h3><div class="course-section-content">' +
                escapeHtml(courseData.concept_focus) +
                "</div></div>";
        }

        if (keyPointsHtml) {
            html +=
                '<div class="course-section"><h3>🧩 Points clés</h3><div class="course-section-content"><ul>' +
                keyPointsHtml +
                "</ul></div></div>";
        }

        if (methodStepsHtml) {
            html +=
                '<div class="course-section"><h3>🪜 Méthode</h3><div class="course-section-content"><ol>' +
                methodStepsHtml +
                "</ol></div></div>";
        }

        if (courseData.worked_example) {
            html +=
                '<div class="example-box"><h3>✏️ Exemple guidé</h3><p>' +
                escapeHtml(courseData.worked_example) +
                "</p></div>";
        }

        if (commonPitfallsHtml) {
            html +=
                '<div class="course-section"><h3>🚫 Pièges à éviter</h3><div class="course-section-content"><ul>' +
                commonPitfallsHtml +
                "</ul></div></div>";
        }

        if (courseData.practice_tip) {
            html +=
                '<div class="formula-box">💡 ' +
                escapeHtml(courseData.practice_tip) +
                "</div>";
        }

        if (courseData.verification_question) {
            html +=
                '<div class="course-section"><h3>✅ Vérifie ta compréhension</h3><div class="course-section-content">' +
                escapeHtml(courseData.verification_question) +
                "</div></div>";
        }

        if (referencesHtml) {
            html +=
                '<div class="course-section"><h3>🔎 Ressources utiles</h3><div class="course-section-content"><ul>' +
                referencesHtml +
                "</ul></div></div>";
        }

        return (
            html ||
            '<div class="course-section-content">Aucun mini-cours disponible.</div>'
        );
    }

    function buildExplanationModalHtml(explanationData) {
        const steps = Array.isArray(explanationData.steps)
            ? explanationData.steps
            : [];
        const perQuestion = Array.isArray(explanationData.per_question)
            ? explanationData.per_question
            : [];
        const stepsHtml = steps
            .map(function (step) {
                return "<li>" + escapeHtml(step) + "</li>";
            })
            .join("");

        const perQuestionHtml = perQuestion
            .map(function (item, index) {
                return (
                    "" +
                    '<div class="course-section">' +
                    "  <h3>❓ Question " +
                    (index + 1) +
                    "</h3>" +
                    '  <div class="course-section-content">' +
                    "    <p><strong>Enoncé :</strong> " +
                    escapeHtml(item.question || "") +
                    "</p>" +
                    "    <p><strong>Ta réponse :</strong> " +
                    escapeHtml(item.your_answer || "Aucune réponse") +
                    "</p>" +
                    "    <p><strong>Bonne réponse :</strong> " +
                    escapeHtml(item.correct_answer || "") +
                    "</p>" +
                    "    <p><strong>Explication :</strong> " +
                    escapeHtml(item.explanation || "") +
                    "</p>" +
                    "  </div>" +
                    "</div>"
                );
            })
            .join("");

        let html = "";

        if (explanationData.summary) {
            html +=
                '<div class="key-points"><h3>🎯 Résumé utile</h3><p>' +
                escapeHtml(explanationData.summary) +
                "</p></div>";
        }

        if (explanationData.learning_objective) {
            html +=
                '<div class="course-section"><h3>📘 Objectif</h3><div class="course-section-content">' +
                escapeHtml(explanationData.learning_objective) +
                "</div></div>";
        }

        if (explanationData.mistake_pattern) {
            html +=
                '<div class="example-box"><h3>🧠 Erreur probable</h3><p>' +
                escapeHtml(explanationData.mistake_pattern) +
                "</p></div>";
        }

        if (stepsHtml) {
            html +=
                '<div class="course-section"><h3>🪜 Comment refaire juste</h3><div class="course-section-content"><ol>' +
                stepsHtml +
                "</ol></div></div>";
        }

        html += perQuestionHtml;

        if (explanationData.retry_tip) {
            html +=
                '<div class="formula-box">💡 ' +
                escapeHtml(explanationData.retry_tip) +
                "</div>";
        }

        if (explanationData.verification_question) {
            html +=
                '<div class="course-section"><h3>✅ Vérifie ta compréhension</h3><div class="course-section-content">' +
                escapeHtml(explanationData.verification_question) +
                "</div></div>";
        }

        return (
            html ||
            '<div class="course-section-content">Aucune explication disponible.</div>'
        );
    }

    function openExplanationContent(html, title, feedbackDiv) {
        if (typeof window.openCourseModal === "function") {
            window.openCourseModal(html, title);
            return;
        }

        if (feedbackDiv) {
            feedbackDiv.insertAdjacentHTML(
                "beforeend",
                '<div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200">' +
                    html +
                    "</div>",
            );
        }
    }

    async function requestExerciseExplanation(payload, feedbackDiv) {
        const modalTitle = "Comprendre mon erreur";
        openExplanationContent(
            '<div class="course-section-content">⏳ Génération de l explication en cours...</div>',
            modalTitle,
            feedbackDiv,
        );

        const baseUrl =
            typeof window.baseUrl !== "undefined" ? window.baseUrl : "";
        const apiUrl =
            (baseUrl || "") +
            "/index.php?page=api/ia/generate_exercise_explanation";
        const csrfToken = window.csrfToken || "";

        const response = await fetch(apiUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken,
            },
            body: JSON.stringify(
                Object.assign({}, payload, { csrf_token: csrfToken }),
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
                    : "Explication indisponible pour le moment",
            );
        }

        openExplanationContent(
            buildExplanationModalHtml(data.data),
            modalTitle,
            feedbackDiv,
        );
    }

    async function requestPreciseCourse(payload, feedbackDiv) {
        const modalTitle = "Mini-cours ciblé";
        openExplanationContent(
            '<div class="course-section-content">⏳ Génération du mini-cours en cours...</div>',
            modalTitle,
            feedbackDiv,
        );

        const baseUrl =
            typeof window.baseUrl !== "undefined" ? window.baseUrl : "";
        const apiUrl =
            (baseUrl || "") + "/index.php?page=api/ia/generate_precise_course";
        const csrfToken = window.csrfToken || "";

        const response = await fetch(apiUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken,
            },
            body: JSON.stringify(
                Object.assign({}, payload, { csrf_token: csrfToken }),
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
                    : "Mini-cours indisponible pour le moment",
            );
        }

        openExplanationContent(
            buildPreciseCourseModalHtml(data.data),
            data.data.title || modalTitle,
            feedbackDiv,
        );
    }

    function attachExplanationButton(feedbackDiv, payload) {
        if (
            !feedbackDiv ||
            !payload ||
            !Array.isArray(payload.incorrect_items) ||
            payload.incorrect_items.length === 0
        ) {
            return;
        }

        const existing = feedbackDiv.querySelector(
            ".btn-understand-error-wrap",
        );
        if (existing) {
            existing.remove();
        }

        const wrapper = document.createElement("div");
        wrapper.className = "btn-understand-error-wrap mt-4";

        const hint = document.createElement("p");
        hint.className = "text-sm text-slate-700 mb-2";
        hint.textContent =
            "Tu peux soit comprendre l erreur, soit ouvrir un mini-cours ciblé pour retravailler précisément la notion.";

        const buttonsRow = document.createElement("div");
        buttonsRow.className = "flex flex-wrap gap-2";

        const explainButton = document.createElement("button");
        explainButton.type = "button";
        explainButton.className =
            "btn-understand-error inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition";
        explainButton.textContent = "💡 Comprendre mon erreur";
        explainButton.setAttribute(
            "aria-label",
            "Afficher une explication pédagogique détaillée",
        );

        const courseButton = document.createElement("button");
        courseButton.type = "button";
        courseButton.className =
            "inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 transition";
        courseButton.textContent = "📘 Voir le mini-cours ciblé";
        courseButton.setAttribute(
            "aria-label",
            "Afficher un mini-cours ciblé sur la notion à retravailler",
        );

        explainButton.addEventListener("click", async function () {
            explainButton.disabled = true;
            explainButton.textContent = "⏳ Explication en cours...";

            try {
                await requestExerciseExplanation(payload, feedbackDiv);
            } catch (error) {
                const message =
                    error && error.message
                        ? error.message
                        : "Impossible de charger l explication pour le moment.";
                feedbackDiv.insertAdjacentHTML(
                    "beforeend",
                    '<p class="mt-3 text-sm text-red-700">' +
                        escapeHtml(message) +
                        "</p>",
                );
            } finally {
                explainButton.disabled = false;
                explainButton.textContent = "💡 Comprendre mon erreur";
            }
        });

        courseButton.addEventListener("click", async function () {
            courseButton.disabled = true;
            courseButton.textContent = "⏳ Mini-cours en cours...";

            try {
                await requestPreciseCourse(payload, feedbackDiv);
            } catch (error) {
                const message =
                    error && error.message
                        ? error.message
                        : "Impossible de charger le mini-cours pour le moment.";
                feedbackDiv.insertAdjacentHTML(
                    "beforeend",
                    '<p class="mt-3 text-sm text-red-700">' +
                        escapeHtml(message) +
                        "</p>",
                );
            } finally {
                courseButton.disabled = false;
                courseButton.textContent = "📘 Voir le mini-cours ciblé";
            }
        });

        wrapper.appendChild(hint);
        buttonsRow.appendChild(explainButton);
        buttonsRow.appendChild(courseButton);
        wrapper.appendChild(buttonsRow);
        feedbackDiv.appendChild(wrapper);
    }

    /**
     * Exercice interactif : Classes de mots (coloriage)
     */
    function initWordColoringExercise() {
        const coloringExercises = document.querySelectorAll(
            ".word-coloring-exercise",
        );

        coloringExercises.forEach((exercise) => {
            const sentence = exercise.dataset.sentence || "";
            const words = sentence.split(" ");
            let correctAnswers = {};
            try {
                correctAnswers = JSON.parse(exercise.dataset.correct || "{}");
            } catch (e) {
                console.error("Erreur parsing correct answers:", e);
            }

            const container = exercise.querySelector(
                ".word-coloring-container",
            );
            const checkBtn = exercise.querySelector(".btn-check-coloring");
            const feedbackDiv = exercise.querySelector(".coloring-feedback");

            if (!container) return;

            // Vider le conteneur au cas où
            container.innerHTML = "";

            // Créer les mots cliquables
            words.forEach((word, index) => {
                const wordSpan = document.createElement("span");
                wordSpan.className = "coloring-word";
                const cleanWord = word.replace(/[.,;:!?]/g, "");
                wordSpan.textContent = word;
                wordSpan.dataset.word = cleanWord;
                wordSpan.dataset.wordType = "none";

                // Permettre de changer la couleur en cliquant
                wordSpan.addEventListener("click", function () {
                    const colors = ["none", "blue", "green", "red"];
                    const colorNames = {
                        blue: "nom",
                        green: "verbe",
                        red: "adjectif",
                        none: "aucune",
                    };

                    const currentColor = this.dataset.wordType || "none";
                    const currentIndex = colors.indexOf(currentColor);
                    const nextIndex = (currentIndex + 1) % colors.length;
                    const nextColor = colors[nextIndex];

                    // Appliquer le style
                    this.classList.remove(
                        "colored-blue",
                        "colored-green",
                        "colored-red",
                    );
                    if (nextColor !== "none") {
                        this.classList.add("colored-" + nextColor);
                    }

                    this.dataset.wordType = nextColor;
                    this.title =
                        "Clique pour changer : " +
                        (colorNames[nextColor] || "aucune couleur");
                });

                container.appendChild(wordSpan);
                if (index < words.length - 1) {
                    container.appendChild(document.createTextNode(" "));
                }
            });

            // Bouton de vérification
            if (checkBtn && feedbackDiv) {
                if (checkBtn.dataset.coloringBound === "1") {
                    return;
                }

                checkBtn.dataset.coloringBound = "1";
                feedbackDiv.setAttribute("aria-live", "polite");
                checkBtn.addEventListener("click", function () {
                    let correct = 0;
                    let total = 0;
                    const incorrectItems = [];

                    container
                        .querySelectorAll(".coloring-word")
                        .forEach((wordSpan) => {
                            const word = wordSpan.dataset.word;
                            const userType =
                                wordSpan.dataset.wordType || "none";
                            const correctType = correctAnswers[word] || "";

                            // Ignorer les déterminants dans la vérification
                            if (correctType && correctType !== "determinant") {
                                total++;

                                // Vérifier si la réponse est correcte
                                const isCorrect =
                                    (userType === "blue" &&
                                        correctType === "nom") ||
                                    (userType === "green" &&
                                        correctType === "verbe") ||
                                    (userType === "red" &&
                                        correctType === "adjectif");

                                wordSpan.classList.remove(
                                    "correct-answer",
                                    "wrong-answer",
                                );

                                if (isCorrect) {
                                    correct++;
                                    wordSpan.classList.add("correct-answer");
                                } else if (userType !== "none") {
                                    wordSpan.classList.add("wrong-answer");
                                    incorrectItems.push({
                                        question:
                                            'Identifier la nature du mot "' +
                                            word +
                                            '"',
                                        user_answer: userType,
                                        correct_answer: correctType,
                                        official_correction:
                                            'Le mot "' +
                                            word +
                                            '" est un ' +
                                            correctType +
                                            ".",
                                        question_type: "word-coloring",
                                        choices: ["nom", "verbe", "adjectif"],
                                    });
                                }
                            }
                        });

                    // Afficher le feedback
                    const percentage =
                        total > 0 ? Math.round((correct / total) * 100) : 0;
                    let feedbackHTML = "";

                    if (percentage === 100) {
                        feedbackHTML = `
                            <div class="feedback-success">
                                <strong>🎉 Parfait !</strong> Tu as identifié tous les mots correctement !
                                +10 points d'expérience !
                            </div>
                        `;
                    } else if (percentage >= 70) {
                        feedbackHTML = `
                            <div class="feedback-good">
                                <strong>👏 Bien joué !</strong> Tu as ${correct}/${total} bonnes réponses.
                                Continue comme ça !
                            </div>
                        `;
                    } else {
                        feedbackHTML = `
                            <div class="feedback-needs-work">
                                <strong>💪 Encore un effort !</strong> Tu as ${correct}/${total} bonnes réponses.
                                Regarde bien les astuces du coach et réessaye !
                            </div>
                        `;
                    }

                    feedbackHTML += `
                        <button class="btn-show-correction" onclick="showColoringCorrection(this)">
                            📝 Voir la correction complète
                        </button>
                        <div class="full-correction" style="display:none; margin-top:1rem;">
                            ${generateCorrectionHTML(correctAnswers)}
                        </div>
                    `;

                    feedbackDiv.innerHTML = feedbackHTML;
                    feedbackDiv.style.display = "block";

                    if (incorrectItems.length > 0) {
                        attachExplanationButton(
                            feedbackDiv,
                            buildExplanationPayload(exercise, incorrectItems),
                        );
                    }
                });
            }
        });
    }

    /**
     * Génère le HTML de correction
     */
    function generateCorrectionHTML(correctAnswers) {
        let html = "<strong>✅ Correction complète :</strong><br>";
        const byType = { nom: [], verbe: [], adjectif: [] };

        Object.keys(correctAnswers).forEach((word) => {
            const type = correctAnswers[word];
            if (byType[type]) {
                byType[type].push(word);
            }
        });

        if (byType.nom.length > 0) {
            html += `<span class="text-blue">Noms (${byType.nom.length}) :</span> ${byType.nom.join(", ")}<br>`;
        }
        if (byType.verbe.length > 0) {
            html += `<span class="text-green">Verbes (${byType.verbe.length}) :</span> ${byType.verbe.join(", ")}<br>`;
        }
        if (byType.adjectif.length > 0) {
            html += `<span class="text-red">Adjectifs (${byType.adjectif.length}) :</span> ${byType.adjectif.join(", ")}<br>`;
        }

        return html;
    }

    /**
     * Affiche/masque la correction complète
     */
    window.showColoringCorrection = function (btn) {
        const correctionDiv = btn.nextElementSibling;
        if (correctionDiv) {
            const isVisible = correctionDiv.style.display !== "none";
            correctionDiv.style.display = isVisible ? "none" : "block";
            btn.textContent = isVisible
                ? "📝 Voir la correction complète"
                : "👁️ Masquer la correction";
        }
    };

    /**
     * Exercice interactif : Conjugaison (champs de saisie)
     */
    function initConjugationExercise() {
        const conjugationExercises = document.querySelectorAll(
            ".conjugation-exercise",
        );

        console.log(
            "🔄 Initialisation des exercices de conjugaison:",
            conjugationExercises.length,
        );

        conjugationExercises.forEach((exercise, exerciseIndex) => {
            let questions = [];
            try {
                const questionsJson = exercise.dataset.questions || "[]";
                questions = JSON.parse(questionsJson);
                console.log(
                    `  ✅ Exercice ${exerciseIndex + 1}: ${questions.length} question(s) trouvée(s)`,
                    questions,
                );
            } catch (e) {
                console.error(
                    "❌ Erreur parsing questions:",
                    e,
                    exercise.dataset.questions,
                );
            }

            const container = exercise.querySelector(".conjugation-container");
            const checkBtn = exercise.querySelector(".btn-check-conjugation");
            const feedbackDiv = exercise.querySelector(".conjugation-feedback");

            if (!container) {
                console.warn(
                    "⚠️ Container .conjugation-container non trouvé pour l'exercice",
                    exerciseIndex + 1,
                );
                return;
            }

            console.log(
                `  📝 Création des champs pour l'exercice ${exerciseIndex + 1}...`,
            );

            // Vider le conteneur
            container.innerHTML = "";

            if (questions.length === 0) {
                console.warn(
                    `  ⚠️ Aucune question trouvée pour l'exercice ${exerciseIndex + 1}`,
                );
                container.innerHTML =
                    '<p style="color: #ef4444; padding: 1rem;">Aucune question disponible pour cet exercice.</p>';
                return;
            }

            // Créer les champs de saisie
            questions.forEach((q, index) => {
                const questionDiv = document.createElement("div");
                questionDiv.className = "conjugation-question";

                // Extraire la partie à compléter
                let sentence = q.sentence || q.question || "";
                // Gérer à la fois answer (string) et answers (array)
                const answer =
                    q.answer ||
                    (q.answers && q.answers.length > 0 ? q.answers[0] : "");
                const answers = q.answers || (q.answer ? [q.answer] : []); // Tableau de réponses pour plusieurs champs
                const verb = q.verb || "";
                const adjectives = q.adjectives || [];

                let displayHTML = "";

                // Cas 1 : Plusieurs adjectifs (format avec _0_, _1_, etc.)
                if (
                    sentence.includes("(_0_)") ||
                    (answers.length > 0 && adjectives.length > 0)
                ) {
                    // Remplacer chaque placeholder par un input
                    let currentSentence = sentence;
                    const inputStyle =
                        "display: inline-block; min-width: 120px; margin: 0 0.3rem; padding: 0.5rem; border: 2px solid #3b82f6; border-radius: 4px;";

                    // Remplacer tous les placeholders _X_ par des inputs
                    for (
                        let i = 0;
                        i < Math.max(answers.length, adjectives.length);
                        i++
                    ) {
                        const placeholder = `(_${i}_)`;
                        const correctAnswer = answers[i] || answer || "";
                        const placeholderRegex = new RegExp(
                            placeholder.replace(/[()]/g, "\\$&"),
                            "g",
                        );

                        if (currentSentence.includes(placeholder)) {
                            const inputId = `input-${index}-${i}`;
                            const inputHTML = `<input type="text"
                                id="${inputId}"
                                class="conjugation-input"
                                data-correct="${correctAnswer}"
                                data-question="${index}"
                                data-input-index="${i}"
                                placeholder="${adjectives[i] || "réponse"}"
                                autocomplete="off"
                                style="${inputStyle}">`;
                            currentSentence = currentSentence.replace(
                                placeholderRegex,
                                inputHTML,
                            );
                        }
                    }

                    displayHTML = `<p><strong>${q.letter || index + 1}.</strong> ${currentSentence}</p>`;
                }
                // Cas 2 : Format simple avec (_)
                else if (sentence.includes("(_)")) {
                    // Séparer la phrase en parties
                    const parts = sentence.split("(_)");
                    const before = parts[0] || "";
                    const after = parts[1] || "";

                    displayHTML = `
                        <p><strong>${index + 1}.</strong> ${before.trim()}
                        <input type="text"
                               class="conjugation-input conjugation-input--inline"
                               data-correct="${answer}"
                               data-question="${index}"
                               placeholder="verbe conjugué"
                               autocomplete="off">
                        ${after.trim()}</p>
                    `;
                }
                // Cas 3 : Format classique : phrase complète + input séparé
                else {
                    displayHTML = `
                        <p><strong>${index + 1}.</strong> ${sentence}</p>
                        <input type="text"
                               class="conjugation-input conjugation-input--block"
                               data-correct="${answer}"
                               data-question="${index}"
                               placeholder="Écris ta réponse ici..."
                               autocomplete="off">
                    `;
                }

                questionDiv.innerHTML =
                    displayHTML + '<span class="input-feedback"></span>';
                container.appendChild(questionDiv);
            });

            // Bouton de vérification
            if (checkBtn && feedbackDiv) {
                if (checkBtn.dataset.conjugationBound === "1") {
                    return;
                }

                checkBtn.dataset.conjugationBound = "1";
                feedbackDiv.setAttribute("aria-live", "polite");
                checkBtn.addEventListener("click", function () {
                    let correct = 0;
                    const inputs =
                        container.querySelectorAll(".conjugation-input");
                    const incorrectItems = [];

                    inputs.forEach((input) => {
                        const userAnswer = input.value.trim().toLowerCase();
                        const correctAnswer = (input.dataset.correct || "")
                            .trim()
                            .toLowerCase();

                        // Trouver le feedback span (peut être nextElementSibling ou dans le parent)
                        let feedbackSpan = input.nextElementSibling;
                        if (
                            !feedbackSpan ||
                            !feedbackSpan.classList.contains("input-feedback")
                        ) {
                            // Chercher dans le parent
                            const questionDiv = input.closest(
                                ".conjugation-question",
                            );
                            if (questionDiv) {
                                feedbackSpan =
                                    questionDiv.querySelector(
                                        ".input-feedback",
                                    );
                            }
                        }

                        input.classList.remove("correct-input", "wrong-input");
                        if (feedbackSpan) {
                            feedbackSpan.style.display = "none";
                        }

                        if (!userAnswer) {
                            if (feedbackSpan) {
                                feedbackSpan.textContent =
                                    "⚠️ Tu n'as pas encore répondu";
                                feedbackSpan.className =
                                    "input-feedback warning";
                                feedbackSpan.style.display = "block";
                            }
                            return;
                        }

                        if (userAnswer === correctAnswer) {
                            correct++;
                            input.classList.add("correct-input");
                            if (feedbackSpan) {
                                feedbackSpan.textContent = "✅ Correct !";
                                feedbackSpan.className =
                                    "input-feedback success";
                                feedbackSpan.style.display = "block";
                            }
                        } else {
                            input.classList.add("wrong-input");
                            if (feedbackSpan) {
                                feedbackSpan.textContent = `❌ Réponse attendue : "${input.dataset.correct}"`;
                                feedbackSpan.className = "input-feedback error";
                                feedbackSpan.style.display = "block";
                            }

                            const questionDiv = input.closest(
                                ".conjugation-question",
                            );
                            const questionText = questionDiv
                                ? questionDiv.querySelector("p")?.textContent ||
                                  "Question de conjugaison"
                                : "Question de conjugaison";

                            incorrectItems.push({
                                question: questionText.trim(),
                                user_answer: input.value.trim(),
                                correct_answer: input.dataset.correct || "",
                                official_correction:
                                    'La forme attendue est "' +
                                    (input.dataset.correct || "") +
                                    '".',
                                question_type: "conjugation",
                            });
                        }
                    });

                    // Afficher le feedback global
                    const percentage =
                        inputs.length > 0
                            ? Math.round((correct / inputs.length) * 100)
                            : 0;
                    let feedbackHTML = "";

                    if (percentage === 100) {
                        feedbackHTML = `
                            <div class="feedback-success">
                                <strong>🎉 Excellent !</strong> Toutes tes réponses sont correctes !
                                +15 points d'expérience !
                            </div>
                        `;
                    } else if (percentage >= 70) {
                        feedbackHTML = `
                            <div class="feedback-good">
                                <strong>👏 Bien joué !</strong> Tu as ${correct}/${inputs.length} bonnes réponses.
                                Continue comme ça !
                            </div>
                        `;
                    } else {
                        feedbackHTML = `
                            <div class="feedback-needs-work">
                                <strong>💪 Encore un effort !</strong> Tu as ${correct}/${inputs.length} bonnes réponses.
                                Relis les astuces et réessaye !
                            </div>
                        `;
                    }

                    feedbackDiv.innerHTML = feedbackHTML;
                    feedbackDiv.style.display = "block";

                    if (incorrectItems.length > 0) {
                        attachExplanationButton(
                            feedbackDiv,
                            buildExplanationPayload(exercise, incorrectItems),
                        );
                    }
                });
            }
        });
    }

    /**
     * Exercice interactif : Mathématiques (champs de saisie numériques)
     */
    function initMathExercise() {
        const mathExercises = document.querySelectorAll(".math-exercise");

        mathExercises.forEach((exercise) => {
            let questions = [];
            try {
                questions = JSON.parse(exercise.dataset.questions || "[]");
            } catch (e) {
                console.error("Erreur parsing questions:", e);
            }

            const container = exercise.querySelector(".math-container");
            const checkBtn = exercise.querySelector(".btn-check-math");
            const feedbackDiv = exercise.querySelector(".math-feedback");

            if (!container) return;

            // Vider le conteneur
            container.innerHTML = "";

            // Créer les champs de saisie numériques
            questions.forEach((q, index) => {
                const questionDiv = document.createElement("div");
                questionDiv.className = "math-question";
                const question = q.question || "";
                const answer = q.answer || "";

                questionDiv.innerHTML = `
                    <p><strong>${index + 1}.</strong> ${question}</p>
                    <input type="number"
                           class="math-input"
                           data-correct="${answer}"
                           data-question="${index}"
                           placeholder="Ta réponse..."
                           step="any"
                           autocomplete="off">
                    <span class="input-feedback"></span>
                `;
                container.appendChild(questionDiv);
            });

            // Bouton de vérification
            if (checkBtn && feedbackDiv) {
                if (checkBtn.dataset.mathBound === "1") {
                    return;
                }

                checkBtn.dataset.mathBound = "1";
                feedbackDiv.setAttribute("aria-live", "polite");
                checkBtn.addEventListener("click", function () {
                    let correct = 0;
                    const inputs = container.querySelectorAll(".math-input");
                    const incorrectItems = [];

                    inputs.forEach((input) => {
                        const userAnswer = parseFloat(input.value);
                        const correctAnswer = parseFloat(input.dataset.correct);
                        const feedbackSpan = input.nextElementSibling;

                        input.classList.remove("correct-input", "wrong-input");
                        feedbackSpan.style.display = "none";

                        if (isNaN(userAnswer)) {
                            feedbackSpan.textContent = "⚠️ Écris un nombre";
                            feedbackSpan.className = "input-feedback warning";
                            feedbackSpan.style.display = "inline-block";
                            return;
                        }

                        // Accepter une petite marge d'erreur
                        if (Math.abs(userAnswer - correctAnswer) < 0.01) {
                            correct++;
                            input.classList.add("correct-input");
                            feedbackSpan.textContent = "✅ Correct !";
                            feedbackSpan.className = "input-feedback success";
                            feedbackSpan.style.display = "inline-block";
                        } else {
                            input.classList.add("wrong-input");
                            feedbackSpan.textContent = `❌ Réponse attendue : ${correctAnswer}`;
                            feedbackSpan.className = "input-feedback error";
                            feedbackSpan.style.display = "inline-block";

                            const questionDiv = input.closest(".math-question");
                            const questionText = questionDiv
                                ? questionDiv.querySelector("p")?.textContent ||
                                  "Question de mathématiques"
                                : "Question de mathématiques";

                            incorrectItems.push({
                                question: questionText.trim(),
                                user_answer: input.value.trim(),
                                correct_answer: String(correctAnswer),
                                official_correction:
                                    "La bonne réponse est " +
                                    String(correctAnswer) +
                                    ".",
                                question_type: "math",
                            });
                        }
                    });

                    // Afficher le feedback global
                    const percentage =
                        inputs.length > 0
                            ? Math.round((correct / inputs.length) * 100)
                            : 0;
                    let feedbackHTML = "";
                    let crystalsAwarded = 0;

                    if (percentage === 100) {
                        crystalsAwarded = 3;
                        feedbackHTML = `
                            <div class="feedback-success">
                                <strong>🌟 Parfait !</strong> Toutes tes réponses sont correctes !
                                +${crystalsAwarded} cristaux mathématiques ! 💎
                            </div>
                        `;
                    } else if (percentage >= 70) {
                        crystalsAwarded = 1;
                        feedbackHTML = `
                            <div class="feedback-good">
                                <strong>👏 Bien joué !</strong> Tu as ${correct}/${inputs.length} bonnes réponses.
                                +${crystalsAwarded} cristal ! 💎
                            </div>
                        `;
                    } else {
                        feedbackHTML = `
                            <div class="feedback-needs-work">
                                <strong>💪 Encore un effort !</strong> Tu as ${correct}/${inputs.length} bonnes réponses.
                                Regarde les astuces du coach et réessaye !
                            </div>
                        `;
                    }

                    feedbackDiv.innerHTML = feedbackHTML;
                    feedbackDiv.style.display = "block";

                    if (incorrectItems.length > 0) {
                        attachExplanationButton(
                            feedbackDiv,
                            buildExplanationPayload(exercise, incorrectItems),
                        );
                    }

                    // Déclencher le coach WebM selon le résultat
                    showCoachInFeedback(feedbackDiv, score, total);

                    // Déclencher l'animation de cristaux si disponible
                    if (
                        crystalsAwarded > 0 &&
                        typeof awardCrystals === "function"
                    ) {
                        awardCrystals(crystalsAwarded);
                    }

                    // Sauvegarder la progression
                    const exerciseCard = exercise.closest(".exercise-card");
                    const exerciseId = exerciseCard
                        ? exerciseCard.dataset.exerciseId || null
                        : null;
                    const difficulty = exerciseCard
                        ? exerciseCard.dataset.difficulty || "moyen"
                        : "moyen";

                    if (
                        exerciseId &&
                        typeof saveProgressToServer === "function"
                    ) {
                        const rewards = calculateRewards(
                            percentage,
                            difficulty,
                        );
                        saveProgressToServer({
                            exerciseId: parseInt(exerciseId),
                            score: percentage,
                            correct: rewards.correct,
                            xp: rewards.xp,
                            cristaux: crystalsAwarded || rewards.cristaux,
                            subject: "Mathématiques",
                        });
                    }
                });
            }
        });
    }

    /**
     * Exercice interactif : QCM (Questionnaire à Choix Multiples)
     */
    function initQCMExercise() {
        const qcmExercises = document.querySelectorAll(".qcm-exercise");

        qcmExercises.forEach((exercise) => {
            let questions = [];
            try {
                questions = JSON.parse(exercise.dataset.questions || "[]");
            } catch (e) {
                console.error("Erreur parsing QCM questions:", e);
            }

            const container = exercise.querySelector(".qcm-container");
            const checkBtn = exercise.querySelector(".btn-check-qcm");
            const feedbackDiv = exercise.querySelector(".qcm-feedback");

            if (feedbackDiv) {
                feedbackDiv.setAttribute("aria-live", "polite");
            }

            if (!container) return;

            // Vider le conteneur
            container.innerHTML = "";

            if (questions.length === 0) {
                console.warn(
                    "⚠️ Aucune question dans le tableau pour cet exercice QCM",
                );
                container.innerHTML =
                    '<p style="color: #ef4444;">Aucune question disponible pour cet exercice.</p>';
                return;
            }

            console.log(
                "  ✅ Création de " + questions.length + " question(s) QCM",
            );

            // Créer les questions QCM
            questions.forEach((q, index) => {
                const questionDiv = document.createElement("div");
                questionDiv.className = "qcm-question";

                const question = q.question || "";
                const choices = q.choices || [];
                const correctAnswer = q.correct || "";

                let choicesHTML = "";
                choices.forEach((choice, choiceIndex) => {
                    const choiceId = `qcm-${index}-${choiceIndex}`;
                    choicesHTML += `
                        <label class="qcm-choice">
                            <input type="radio"
                                   name="qcm-${index}"
                                   value="${choice.value || choice}"
                                   data-correct="${choice.value === correctAnswer || choice === correctAnswer}"
                                   class="qcm-radio">
                            <span class="qcm-choice-text">${choice.label || choice}</span>
                        </label>
                    `;
                });

                questionDiv.innerHTML = `
                    <p><strong>${index + 1}.</strong> ${question}</p>
                    <div class="qcm-choices">
                        ${choicesHTML}
                    </div>
                    <span class="input-feedback"></span>
                `;
                container.appendChild(questionDiv);
            });

            // Bouton de vérification
            if (checkBtn && feedbackDiv) {
                if (checkBtn.dataset.qcmBound === "1") {
                    return;
                }

                checkBtn.dataset.qcmBound = "1";
                checkBtn.addEventListener("click", function () {
                    let correct = 0;
                    const questionDivs =
                        container.querySelectorAll(".qcm-question");
                    const incorrectItems = [];
                    const exerciseContext = getExerciseContext(exercise);

                    questionDivs.forEach((questionDiv, index) => {
                        const selectedRadio = questionDiv.querySelector(
                            'input[type="radio"]:checked',
                        );
                        const feedbackSpan =
                            questionDiv.querySelector(".input-feedback");
                        const correctRadio = questionDiv.querySelector(
                            'input[data-correct="true"]',
                        );

                        questionDiv
                            .querySelectorAll(".qcm-choice")
                            .forEach((choice) => {
                                choice.classList.remove(
                                    "correct-choice",
                                    "wrong-choice",
                                );
                            });
                        feedbackSpan.style.display = "none";

                        if (!selectedRadio) {
                            feedbackSpan.textContent =
                                "⚠️ Tu n'as pas encore répondu";
                            feedbackSpan.className = "input-feedback warning";
                            feedbackSpan.style.display = "inline-block";
                            return;
                        }

                        const isCorrect = selectedRadio === correctRadio;

                        if (isCorrect) {
                            correct++;
                            selectedRadio
                                .closest(".qcm-choice")
                                .classList.add("correct-choice");
                            feedbackSpan.textContent = "✅ Correct !";
                            feedbackSpan.className = "input-feedback success";
                            feedbackSpan.style.display = "inline-block";
                        } else {
                            selectedRadio
                                .closest(".qcm-choice")
                                .classList.add("wrong-choice");
                            if (correctRadio) {
                                correctRadio
                                    .closest(".qcm-choice")
                                    .classList.add("correct-choice");
                            }
                            const correctLabel = correctRadio
                                ? correctRadio
                                      .closest(".qcm-choice")
                                      .querySelector(".qcm-choice-text")
                                      .textContent
                                : "";
                            const selectedLabel = selectedRadio.closest(
                                ".qcm-choice",
                            )
                                ? selectedRadio
                                      .closest(".qcm-choice")
                                      .querySelector(".qcm-choice-text")
                                      .textContent
                                : "";
                            feedbackSpan.textContent = `❌ Réponse attendue : ${correctLabel || "Vérifie ta réponse"}`;
                            feedbackSpan.className = "input-feedback error";
                            feedbackSpan.style.display = "inline-block";

                            incorrectItems.push({
                                question:
                                    questions[index] &&
                                    questions[index].question
                                        ? questions[index].question
                                        : "Question " + (index + 1),
                                user_answer:
                                    selectedLabel || selectedRadio.value || "",
                                correct_answer: correctLabel || "",
                                official_correction:
                                    questions[index] &&
                                    questions[index].explanation
                                        ? questions[index].explanation
                                        : exerciseContext.officialCorrection,
                                question_type: "qcm",
                                choices: Array.isArray(
                                    questions[index] &&
                                        questions[index].choices,
                                )
                                    ? questions[index].choices.map(
                                          function (choice) {
                                              return choice && choice.label
                                                  ? choice.label
                                                  : choice;
                                          },
                                      )
                                    : [],
                            });
                        }
                    });

                    // Afficher le feedback global
                    const percentage =
                        questionDivs.length > 0
                            ? Math.round((correct / questionDivs.length) * 100)
                            : 0;
                    let feedbackHTML = "";

                    if (percentage === 100) {
                        feedbackHTML = `
                            <div class="feedback-success">
                                <strong>🎉 Parfait !</strong> Toutes tes réponses sont correctes !
                                +10 points d'expérience !
                            </div>
                        `;
                    } else if (percentage >= 70) {
                        feedbackHTML = `
                            <div class="feedback-good">
                                <strong>👏 Bien joué !</strong> Tu as ${correct}/${questionDivs.length} bonnes réponses.
                                Continue comme ça !
                            </div>
                        `;
                    } else {
                        feedbackHTML = `
                            <div class="feedback-needs-work">
                                <strong>💪 Encore un effort !</strong> Tu as ${correct}/${questionDivs.length} bonnes réponses.
                                Relis les astuces et réessaye !
                            </div>
                        `;
                    }

                    feedbackDiv.innerHTML = feedbackHTML;
                    feedbackDiv.style.display = "block";

                    // Déclencher le coach WebM selon le résultat
                    showCoachInFeedback(
                        feedbackDiv,
                        correct,
                        questionDivs.length,
                    );

                    if (incorrectItems.length > 0) {
                        attachExplanationButton(
                            feedbackDiv,
                            buildExplanationPayload(exercise, incorrectItems),
                        );
                    }

                    // Sauvegarder la progression pour conjugaison/réécriture
                    const exerciseCard = exercise.closest(".exercise-card");
                    const exerciseId = exerciseCard
                        ? exerciseCard.dataset.exerciseId || null
                        : null;
                    const difficulty = exerciseCard
                        ? exerciseCard.dataset.difficulty || "moyen"
                        : "moyen";
                    const subject = exerciseCard
                        ? exerciseCard.dataset.subject || "Français"
                        : "Français";

                    if (exerciseId) {
                        setTimeout(() => {
                            const saveFn =
                                window.InteractiveExercises?.saveProgress ||
                                (typeof saveProgressToServer === "function"
                                    ? saveProgressToServer
                                    : null);
                            const calcFn =
                                window.InteractiveExercises?.calculateRewards ||
                                (typeof calculateRewards === "function"
                                    ? calculateRewards
                                    : null);

                            if (saveFn && calcFn) {
                                const rewards = calcFn(percentage, difficulty);
                                saveFn({
                                    exerciseId: parseInt(exerciseId),
                                    score: percentage,
                                    correct: rewards.correct,
                                    xp: rewards.xp,
                                    cristaux: rewards.cristaux,
                                    subject: subject,
                                });
                            }
                        }, 100);
                    }
                });
            }
        });
    }

    /**
     * Exercice interactif : Classement chronologique
     */
    function initChronologyExercise() {
        const chronologyExercises = document.querySelectorAll(
            ".chronology-exercise",
        );

        chronologyExercises.forEach((exercise) => {
            let events = [];
            try {
                events = JSON.parse(exercise.dataset.events || "[]");
            } catch (e) {
                console.error("Erreur parsing chronology events:", e);
            }

            const container = exercise.querySelector(".chronology-container");
            const checkBtn = exercise.querySelector(".btn-check-chronology");
            const feedbackDiv = exercise.querySelector(".chronology-feedback");

            if (!container) return;

            // Vider le conteneur
            container.innerHTML = "";

            // Version simplifiée : sélection avec select
            container.innerHTML = `
                <p><strong>Classe les événements par ordre chronologique (du plus ancien au plus récent) :</strong></p>
                <div class="chronology-selects"></div>
            `;

            const selectsContainer = container.querySelector(
                ".chronology-selects",
            );
            const correctOrder = events
                .map((e, i) => i)
                .sort((a, b) => {
                    return (events[a].order || a) - (events[b].order || b);
                });

            events.forEach((event, index) => {
                const selectDiv = document.createElement("div");
                selectDiv.className = "chronology-select-item";
                selectDiv.innerHTML = `
                    <label>Position ${index + 1} :</label>
                    <select class="chronology-select" data-position="${index}">
                        <option value="">-- Choisir un événement --</option>
                        ${events
                            .map(
                                (e, i) =>
                                    `<option value="${i}">${e.text || e}</option>`,
                            )
                            .join("")}
                    </select>
                `;
                selectsContainer.appendChild(selectDiv);
            });

            // Bouton de vérification
            if (checkBtn && feedbackDiv) {
                checkBtn.addEventListener("click", function () {
                    let correct = 0;
                    const selects =
                        container.querySelectorAll(".chronology-select");
                    const selectedValues = Array.from(selects).map((s) =>
                        parseInt(s.value),
                    );

                    // Vérifier si tous les événements sont sélectionnés
                    if (selectedValues.some((v) => isNaN(v))) {
                        feedbackDiv.innerHTML = `
                            <div class="feedback-needs-work">
                                <strong>⚠️ Attention !</strong> Tu dois classer tous les événements.
                            </div>
                        `;
                        feedbackDiv.style.display = "block";
                        return;
                    }

                    // Vérifier l'ordre
                    selectedValues.forEach((selectedIndex, position) => {
                        if (selectedIndex === correctOrder[position]) {
                            correct++;
                            selects[position].classList.add("correct-select");
                            selects[position].classList.remove("wrong-select");
                        } else {
                            selects[position].classList.add("wrong-select");
                            selects[position].classList.remove(
                                "correct-select",
                            );
                        }
                    });

                    // Afficher le feedback
                    const percentage = Math.round(
                        (correct / events.length) * 100,
                    );
                    let feedbackHTML = "";

                    if (percentage === 100) {
                        feedbackHTML = `
                            <div class="feedback-success">
                                <strong>🎉 Parfait !</strong> Tu as classé tous les événements dans le bon ordre !
                                +10 points d'expérience !
                            </div>
                        `;
                    } else if (percentage >= 70) {
                        feedbackHTML = `
                            <div class="feedback-good">
                                <strong>👏 Bien joué !</strong> Tu as ${correct}/${events.length} événements dans le bon ordre.
                                Continue comme ça !
                            </div>
                        `;
                    } else {
                        feedbackHTML = `
                            <div class="feedback-needs-work">
                                <strong>💪 Encore un effort !</strong> Tu as ${correct}/${events.length} événements dans le bon ordre.
                                Relis les astuces et réessaye !
                            </div>
                        `;
                    }

                    feedbackHTML += `
                        <button class="btn-show-correction" onclick="showChronologyCorrection(this)">
                            📝 Voir l'ordre correct
                        </button>
                        <div class="full-correction" style="display:none; margin-top:1rem;">
                            <strong>✅ Ordre chronologique correct :</strong><br>
                            ${correctOrder
                                .map(
                                    (idx, pos) =>
                                        `${pos + 1}. ${events[idx].text || events[idx]}${events[idx].date ? " (" + events[idx].date + ")" : ""}`,
                                )
                                .join("<br>")}
                        </div>
                    `;

                    feedbackDiv.innerHTML = feedbackHTML;
                    feedbackDiv.style.display = "block";
                });
            }
        });
    }

    /**
     * Affiche/masque la correction de chronologie
     */
    window.showChronologyCorrection = function (btn) {
        const correctionDiv = btn.nextElementSibling;
        if (correctionDiv) {
            const isVisible = correctionDiv.style.display !== "none";
            correctionDiv.style.display = isVisible ? "none" : "block";
            btn.textContent = isVisible
                ? "📝 Voir l'ordre correct"
                : "👁️ Masquer l'ordre correct";
        }
    };

    // Initialisation au chargement de la page
    // --- Nouveaux types d'exercices interactifs ---
    function initGraphExercise() {
        const graphExercises = document.querySelectorAll(".graph-exercise");
        graphExercises.forEach((exercise) => {
            try {
                // --- Création du canvas ---
                let canvas = exercise.querySelector("canvas.graph-canvas");
                if (!canvas) {
                    canvas = document.createElement("canvas");
                    canvas.className = "graph-canvas";
                    canvas.width = 400;
                    canvas.height = 300;
                    canvas.style.border = "2px solid #3b82f6";
                    exercise.appendChild(canvas);
                }
                const ctx = canvas.getContext("2d");
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = "#f3f4f6";
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // --- Axes ---
                ctx.strokeStyle = "#3b82f6";
                ctx.beginPath();
                ctx.moveTo(40, 260);
                ctx.lineTo(360, 260); // axe x
                ctx.moveTo(40, 260);
                ctx.lineTo(40, 40); // axe y
                ctx.stroke();

                // --- Consigne ---
                let instruction =
                    exercise.dataset.instruction ||
                    "Cliquez pour placer les points du triangle rectangle en B.";
                let instructionDiv =
                    exercise.querySelector(".graph-instruction");
                if (!instructionDiv) {
                    instructionDiv = document.createElement("div");
                    instructionDiv.className = "graph-instruction";
                    instructionDiv.style.marginBottom = "0.5rem";
                    instructionDiv.style.fontWeight = "bold";
                    instructionDiv.textContent = instruction;
                    exercise.insertBefore(instructionDiv, canvas);
                }

                // --- Points et tracé ---
                let points = [];
                let shape = exercise.dataset.shape || "triangle"; // 'line' ou 'triangle'
                canvas.addEventListener("click", function (e) {
                    if (points.length >= (shape === "triangle" ? 3 : 2)) return;
                    const rect = canvas.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    points.push({ x, y });
                    ctx.beginPath();
                    ctx.arc(x, y, 5, 0, 2 * Math.PI);
                    ctx.fillStyle = "#ef4444";
                    ctx.fill();
                    // Tracer la figure si tous les points sont placés
                    if (
                        (shape === "triangle" && points.length === 3) ||
                        (shape === "line" && points.length === 2)
                    ) {
                        ctx.beginPath();
                        ctx.strokeStyle = "#10b981";
                        if (shape === "triangle") {
                            ctx.moveTo(points[0].x, points[0].y);
                            ctx.lineTo(points[1].x, points[1].y);
                            ctx.lineTo(points[2].x, points[2].y);
                            ctx.lineTo(points[0].x, points[0].y);
                        } else {
                            ctx.moveTo(points[0].x, points[0].y);
                            ctx.lineTo(points[1].x, points[1].y);
                        }
                        ctx.stroke();
                    }
                });

                // --- Calcul et correction ---
                let checkBtn = exercise.querySelector(".btn-check-graph");
                if (!checkBtn) {
                    checkBtn = document.createElement("button");
                    checkBtn.className = "btn-check-graph";
                    checkBtn.textContent = "Vérifier le tracé";
                    checkBtn.style.marginTop = "1rem";
                    exercise.appendChild(checkBtn);
                }
                checkBtn.onclick = function () {
                    if (
                        (shape === "triangle" && points.length !== 3) ||
                        (shape === "line" && points.length !== 2)
                    ) {
                        showGraphFeedback(
                            exercise,
                            "❌ Placez tous les points avant de vérifier.",
                            "error",
                        );
                        return;
                    }
                    // Exemple de correction : vérifier si le triangle est rectangle en B
                    if (shape === "triangle") {
                        // Calcul des distances
                        function dist(a, b) {
                            return Math.sqrt(
                                Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2),
                            );
                        }
                        const AB = dist(points[0], points[1]);
                        const BC = dist(points[1], points[2]);
                        const CA = dist(points[2], points[0]);
                        // Vérifier le théorème de Pythagore pour l'angle B
                        const isRight =
                            Math.abs(
                                Math.pow(AB, 2) +
                                    Math.pow(BC, 2) -
                                    Math.pow(CA, 2),
                            ) < 10;
                        if (isRight) {
                            showGraphFeedback(
                                exercise,
                                "✅ Bravo ! Triangle rectangle en B.",
                                "success",
                            );
                        } else {
                            showGraphFeedback(
                                exercise,
                                "❌ Le triangle n’est pas rectangle en B.",
                                "error",
                            );
                        }
                    } else if (shape === "line") {
                        showGraphFeedback(
                            exercise,
                            "✅ Droite tracée entre les deux points.",
                            "success",
                        );
                    }
                };
            } catch (err) {
                // Affichage utilisateur + log console
                let feedback = exercise.querySelector(".graph-feedback");
                if (!feedback) {
                    feedback = document.createElement("div");
                    feedback.className = "graph-feedback";
                    feedback.style.color = "#ef4444";
                    feedback.style.marginTop = "1rem";
                    exercise.appendChild(feedback);
                }
                feedback.textContent =
                    "Erreur lors de l'initialisation de l'exercice graphique.";
                console.error("Erreur initGraphExercise:", err);
            }
        });

        // Helper pour feedback utilisateur
        function showGraphFeedback(exercise, message, type) {
            let feedback = exercise.querySelector(".graph-feedback");
            if (!feedback) {
                feedback = document.createElement("div");
                feedback.className = "graph-feedback";
                feedback.style.marginTop = "1rem";
                exercise.appendChild(feedback);
            }
            feedback.textContent = message;
            feedback.style.color = type === "success" ? "#10b981" : "#ef4444";
        }
    }

    function initDragDropExercise() {
        const dragDropExercises =
            document.querySelectorAll(".dragdrop-exercise");
        dragDropExercises.forEach((exercise) => {
            // TODO: Initialisation drag & drop (association, classement, etc.)
            console.log("🟧 Initialisation exercice drag & drop:", exercise);
        });
    }

    function initTableExercise() {
        const tableExercises = document.querySelectorAll(".table-exercise");
        tableExercises.forEach((exercise) => {
            // TODO: Initialisation tableau à compléter
            console.log("🟨 Initialisation exercice tableau:", exercise);
        });
    }

    function initCodeExercise() {
        const codeExercises = document.querySelectorAll(".code-exercise");
        codeExercises.forEach((exercise) => {
            // TODO: Initialisation éditeur de code interactif (exécution, correction)
            console.log("🟪 Initialisation exercice code:", exercise);
        });
    }

    function initSimulationExercise() {
        const simulationExercises = document.querySelectorAll(
            ".simulation-exercise",
        );
        simulationExercises.forEach((exercise) => {
            // TODO: Initialisation simulation/manipulation virtuelle
            console.log("🟫 Initialisation exercice simulation:", exercise);
        });
    }

    function initAdvancedChronologyExercise() {
        const advChronologyExercises = document.querySelectorAll(
            ".advanced-chronology-exercise",
        );
        advChronologyExercises.forEach((exercise) => {
            // TODO: Initialisation classement chronologique avancé (drag & drop, timeline)
            console.log(
                "⬛ Initialisation exercice chronologie avancée:",
                exercise,
            );
        });
    }

    function initAll() {
        console.log("🔄 InteractiveExercises.initAll() appelé");
        console.log("📊 Exercices trouvés dans le document:");
        console.log(
            "  - QCM:",
            document.querySelectorAll(".qcm-exercise").length,
        );
        console.log(
            "  - Math:",
            document.querySelectorAll(".math-exercise").length,
        );
        console.log(
            "  - Conjugation:",
            document.querySelectorAll(".conjugation-exercise").length,
        );
        console.log(
            "  - Word Coloring:",
            document.querySelectorAll(".word-coloring-exercise").length,
        );
        console.log(
            "  - Chronology:",
            document.querySelectorAll(".chronology-exercise").length,
        );
        console.log(
            "  - Graphique:",
            document.querySelectorAll(".graph-exercise").length,
        );
        console.log(
            "  - Drag & Drop:",
            document.querySelectorAll(".dragdrop-exercise").length,
        );
        console.log(
            "  - Tableau:",
            document.querySelectorAll(".table-exercise").length,
        );
        console.log(
            "  - Code:",
            document.querySelectorAll(".code-exercise").length,
        );
        console.log(
            "  - Simulation:",
            document.querySelectorAll(".simulation-exercise").length,
        );
        console.log(
            "  - Chronologie avancée:",
            document.querySelectorAll(".advanced-chronology-exercise").length,
        );

        // --- NOUVEAU SYSTÈME : initialisation dynamique pour les nouveaux exercices ---
        // Tous les nouveaux exercices doivent utiliser .exercise-auto et data-content/data-instruction
        document.querySelectorAll(".exercise-auto").forEach((exercise) => {
            const content = exercise.dataset.content || "";
            const instruction = exercise.dataset.instruction || "";
            const detectedType = detectExerciseType(content, instruction);
            exercise.dataset.detectedType = detectedType;
            switch (detectedType) {
                case "graph":
                    initGraphExercise && initGraphExercise();
                    break;
                case "dragdrop":
                    initDragDropExercise && initDragDropExercise();
                    break;
                case "table":
                    initTableExercise && initTableExercise();
                    break;
                case "code":
                    initCodeExercise && initCodeExercise();
                    break;
                case "simulation":
                    initSimulationExercise && initSimulationExercise();
                    break;
                case "advanced-chronology":
                    initAdvancedChronologyExercise &&
                        initAdvancedChronologyExercise();
                    break;
                case "qcm":
                    initQCMExercise && initQCMExercise();
                    break;
                case "texte":
                default:
                    /* fallback */ break;
            }
        });

        // --- ANCIEN SYSTÈME : initialisation pour compatibilité (migration progressive) ---
        // À supprimer quand tous les exercices auront migré vers .exercise-auto
        // (Gardé pour compatibilité avec les anciens exercices)
        initWordColoringExercise();
        initConjugationExercise();
        initMathExercise();
        initQCMExercise();
        initChronologyExercise();
        initGraphExercise();
        initDragDropExercise();
        initTableExercise();
        initCodeExercise();
        initSimulationExercise();
        initAdvancedChronologyExercise();

        // --- FIN DE SÉPARATION ---
        console.log("✅ Toutes les initialisations terminées");
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initAll);
    } else {
        initAll();
    }

    // Exposer aussi une fonction pour réinitialiser après ajout de contenu dynamique
    window.InteractiveExercises = window.InteractiveExercises || {};
    window.InteractiveExercises.reinit = function () {
        console.log("🔄 Réinitialisation des exercices interactifs...");
        initAll();
    };

    /**
     * Fonction pour sauvegarder la progression sur le serveur avec fallback localStorage
     */
    async function saveProgressToServer(exerciseData) {
        // En prod et en local (virtual host), l'API est à la racine du projet.
        // En local XAMPP (http://localhost/moncoachscolaire), window.baseUrl = '/moncoachscolaire'
        const baseUrl =
            typeof window.baseUrl !== "undefined" ? window.baseUrl : "";
        const apiUrl = (baseUrl || "") + "/api/save-progress.php";

        try {
            const headers = { "Content-Type": "application/json" };
            if (window.csrfToken) {
                headers["X-CSRF-Token"] = window.csrfToken;
                exerciseData.csrf_token = window.csrfToken;
            }

            const response = await fetch(apiUrl, {
                method: "POST",
                headers,
                body: JSON.stringify(exerciseData),
            });

            const result = await response.json();

            if (result.fallback || !result.success) {
                // BDD indisponible ou erreur → sauvegarder dans localStorage
                saveProgressToLocalStorage(exerciseData);
                console.log(
                    "Progression sauvegardée localement (BDD indisponible ou erreur)",
                );
                return { saved: "local", data: exerciseData };
            } else if (result.success) {
                // Sauvegarde réussie
                console.log("Progression sauvegardée sur le serveur:", result);

                // Déclencher un événement pour mettre à jour l'interface
                document.dispatchEvent(
                    new CustomEvent("exerciseCompleted", {
                        detail: {
                            xp: result.xp || 0,
                            cristaux: result.cristaux || 0,
                            badge: result.badge_unlocked
                                ? result.badge_name
                                : null,
                            progress: result.progress,
                        },
                    }),
                );

                return { saved: "server", data: result };
            }
        } catch (error) {
            // Erreur réseau → localStorage
            console.error(
                "Erreur sauvegarde, utilisation localStorage:",
                error,
            );
            saveProgressToLocalStorage(exerciseData);
            return { saved: "local", data: exerciseData, error: error.message };
        }
    }

    /**
     * Sauvegarde la progression dans localStorage
     */
    function saveProgressToLocalStorage(data) {
        if (typeof Storage === "undefined") {
            console.warn("localStorage non disponible");
            return;
        }

        const key = `progress_${data.exerciseId || "unknown"}_${Date.now()}`;
        const saveData = {
            ...data,
            timestamp: new Date().toISOString(),
            synced: false,
        };

        localStorage.setItem(key, JSON.stringify(saveData));

        // Marquer qu'il y a des données en attente
        const pending = JSON.parse(
            localStorage.getItem("pending_progress_saves") || "[]",
        );
        pending.push(key);
        localStorage.setItem("pending_progress_saves", JSON.stringify(pending));

        console.log("Progression sauvegardée dans localStorage:", key);
    }

    /**
     * Synchronise localStorage → serveur quand la BDD redevient disponible
     */
    async function syncLocalStorageToServer() {
        if (typeof Storage === "undefined") {
            return;
        }

        const pendingKeys = JSON.parse(
            localStorage.getItem("pending_progress_saves") || "[]",
        );

        if (pendingKeys.length === 0) {
            return;
        }

        console.log(
            `Tentative de synchronisation de ${pendingKeys.length} sauvegardes...`,
        );

        const syncedKeys = [];
        const failedKeys = [];

        for (const key of pendingKeys) {
            try {
                const dataStr = localStorage.getItem(key);
                if (!dataStr) {
                    syncedKeys.push(key);
                    continue;
                }

                const data = JSON.parse(dataStr);
                const result = await saveProgressToServer(data);

                if (result.saved === "server") {
                    syncedKeys.push(key);
                    localStorage.removeItem(key);
                } else {
                    failedKeys.push(key);
                }
            } catch (error) {
                console.error(`Erreur sync pour ${key}:`, error);
                failedKeys.push(key);
            }
        }

        // Mettre à jour la liste des clés en attente
        localStorage.setItem(
            "pending_progress_saves",
            JSON.stringify(failedKeys),
        );

        if (syncedKeys.length > 0) {
            console.log(
                `${syncedKeys.length} sauvegardes synchronisées avec succès`,
            );
        }

        return { synced: syncedKeys.length, failed: failedKeys.length };
    }

    /**
     * Helper pour calculer XP et cristaux selon le score
     */
    function calculateRewards(score, difficulty = "moyen") {
        const multipliers = {
            facile: { xp: 10, cristaux: 1 },
            moyen: { xp: 15, cristaux: 2 },
            difficile: { xp: 25, cristaux: 3 },
        };

        const base = multipliers[difficulty] || multipliers["moyen"];
        const correct = score >= 50;

        return {
            xp: correct ? base.xp : 0,
            cristaux: correct ? base.cristaux : 0,
            correct: correct,
        };
    }

    // Tenter de synchroniser au chargement de la page
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(syncLocalStorageToServer, 2000); // Attendre 2 secondes
        });
    } else {
        setTimeout(syncLocalStorageToServer, 2000);
    }

    // Tenter de synchroniser toutes les 5 minutes
    setInterval(syncLocalStorageToServer, 5 * 60 * 1000);

    /**
     * Déclenche le coach WebM selon le score
     * @param {HTMLElement} feedbackDiv - Élément du feedback
     * @param {number} score - Score obtenu
     * @param {number} total - Score total
     */
    function showCoachInFeedback(feedbackDiv, score, total) {
        // Vérifier si le coach WebM est disponible
        if (typeof window.onExerciseComplete === "undefined") {
            return; // Le script coach-webm.js n'est pas chargé
        }

        // Déclencher le coach selon le score
        const percentage = total > 0 ? (score / total) * 100 : 0;
        window.onExerciseComplete(percentage);
    }

    // Fonction pour réinitialiser après ajout de contenu dynamique
    function reinit() {
        console.log("🔄 Réinitialisation des exercices interactifs...");
        initAll();
    }

    // Exposer les fonctions pour utilisation externe
    window.InteractiveExercises = {
        initWordColoring: initWordColoringExercise,
        initConjugation: initConjugationExercise,
        initMath: initMathExercise,
        initQCM: initQCMExercise,
        initChronology: initChronologyExercise,
        initAll: initAll,
        reinit: reinit,
        saveProgress: saveProgressToServer,
        syncLocalStorage: syncLocalStorageToServer,
        calculateRewards: calculateRewards,
    };
})();
