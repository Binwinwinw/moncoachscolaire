// diagnostic.js — Diagnostic initial MonCoachScolaire (MVP 2026)
// Version améliorée avec meilleur logging et gestion d'erreurs
console.log('✅ diagnostic.js chargé');

let currentQuizState = null;

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

document.addEventListener('DOMContentLoaded', async () => {
    console.log('✅ DOMContentLoaded déclenché');
    const app = document.getElementById('diagnostic-app');
    if (!app) return console.error('diagnostic-app non trouvé');

    // Niveau synchronisé depuis PHP
    const level = window.userLevel || '6eme';
    const urlParams = new URLSearchParams(window.location.search);
    const subject = urlParams.get('subject') || null;

    // Vérifier que apiBasePath est défini
    if (!window.apiBasePath) {
        console.error('ERROR: window.apiBasePath non défini');
        console.log('Configuration disponible:', { userLevel: window.userLevel, basePath: window.basePath });
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

    const apiUrl = `${window.apiBasePath}/diagnostic.php?level=${encodeURIComponent(level)}` +
                   (subject ? `&subject=${encodeURIComponent(subject)}` : '');

    console.log('INFO: Diagnostic page loading');
    console.log('- User Level:', level);
    console.log('- API Base Path:', window.apiBasePath);
    console.log('- API URL:', apiUrl);
    console.log('- Subject:', subject || 'none');

    try {
        console.log('FETCH: Appel API...');
        const response = await fetch(apiUrl);

        // Vérifier le statut HTTP
        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
        }

        // Lire la réponse comme texte d'abord pour debug
        const responseText = await response.text();
        console.log('RESPONSE: Réponse brute reçue (' + responseText.length + ' bytes)');
        console.log('RESPONSE: Premiers 300 caractères:', responseText.substring(0, 300));

        // Essayer de parser en JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('ERROR: Impossible de parser JSON:', parseError.message);
            console.error('ERROR: Contenu:', responseText);
            throw new Error(`Réponse API invalide (JSON parse error): ${parseError.message}`);
        }

        console.log('SUCCESS: JSON parsé', data);

        // Récupérer les quiz (data.quiz ou data.data selon la structure)
        const quizArray = data.quiz || data.data || [];
        const recommendedQuizId = Number(data?.recommendation?.id || 0);

        console.log('INFO: Quiz array reçu:', {
            isArray: Array.isArray(quizArray),
            length: quizArray ? quizArray.length : 'N/A',
            type: typeof quizArray,
            content: quizArray
        });

        if (!Array.isArray(quizArray)) {
            console.error('ERROR: data.quiz n\'est pas un array:', quizArray);
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
            console.log('INFO: Aucun quiz trouvé pour le niveau: ' + level);
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
        let levelType = 'Collège'; // Par défaut
        let levelBadgeClass = 'bg-blue-100 text-blue-800';

        const levelLower = level.toLowerCase();
        if (['2nde', 'seconde', '1ere', 'premiere', 'terminale'].includes(levelLower)) {
            levelType = 'Lycée';
            levelBadgeClass = 'bg-purple-100 text-purple-800';
        } else if (levelLower === 'bac') {
            levelType = 'BAC';
            levelBadgeClass = 'bg-amber-100 text-amber-800';
        }

        const pageSize = 12;
        const totalPages = Math.max(1, Math.ceil(quizArray.length / pageSize));
        let currentPage = 1;

        function renderPagination() {
            if (totalPages <= 1) {
                return '';
            }

            const buttons = [];
            for (let page = 1; page <= totalPages; page++) {
                const isActive = page === currentPage;
                buttons.push(`
                    <button data-quiz-page="${page}" class="px-4 py-2 rounded-xl border-2 text-sm font-bold transition-all ${isActive ? 'bg-blue-600 text-white border-blue-600 shadow-lg' : 'bg-white text-blue-700 border-blue-200 hover:border-blue-400 hover:bg-blue-50'}">
                        ${page}
                    </button>
                `);
            }

            return `
                <div class="w-full flex flex-wrap justify-center items-center gap-2 mt-8">
                    <span class="text-sm text-gray-600 font-semibold mr-2">Page ${currentPage}/${totalPages}</span>
                    ${buttons.join('')}
                </div>
            `;
        }

        function renderQuizPage() {
            const start = (currentPage - 1) * pageSize;
            const pagedQuiz = quizArray.slice(start, start + pageSize);

            let html = `
                <header class="mb-12 text-center">
                    <h1 class="text-5xl md:text-6xl font-extrabold mb-6 drop-shadow-lg"
                        style="background: linear-gradient(to right, #2563eb, #7c3aed, #4f46e5); -webkit-background-clip: text; color: #1e293b;">
                        🩺 Diagnostics ${level.toUpperCase()}
                    </h1>
                    <p class="text-2xl text-gray-800 font-semibold mb-2">${quizArray.length} quiz disponibles</p>
                    <div class="flex flex-wrap justify-center gap-4 mb-4">
                        <span class="px-4 py-2 rounded-full ${levelBadgeClass} font-bold shadow">${levelType}</span>
                        <span class="px-4 py-2 rounded-full bg-indigo-100 text-indigo-800 font-bold shadow">Test de niveau</span>
                    </div>
                    <div class="max-w-xl mx-auto bg-white/80 rounded-2xl p-4 border border-blue-100 shadow-sm">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Tu connais l'ID d'un quiz ? Lance-le directement :</p>
                        <div class="flex gap-2">
                            <input id="quiz-id-input" type="number" min="1" placeholder="Ex: 145"
                                   class="flex-1 px-4 py-3 rounded-xl border-2 border-blue-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none" />
                            <button id="quiz-id-start-btn" class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all">
                                Ouvrir
                            </button>
                        </div>
                    </div>
                </header>
            `;

            html += `
                <div class="mx-auto w-full max-w-screen-xl px-2 py-2 flex justify-center">
                    <div class="w-[90vw] md:w-[80vw] xl:w-[70vw] bg-white/80 rounded-3xl shadow-xl p-2 md:p-6 xl:p-8 flex flex-col items-center">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 w-full">
            `;

            pagedQuiz.forEach(quiz => {
            const isRecommended = recommendedQuizId > 0 && Number(quiz.id) === recommendedQuizId;
            const recommendedBadge = isRecommended
                ? '<span class="absolute top-4 left-4 px-4 py-2 rounded-full bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-xs shadow-lg">⭐ Recommandé</span>'
                : '';

            const recommendedCardClass = isRecommended
                ? 'border-emerald-400 ring-2 ring-emerald-200 shadow-emerald-200'
                : 'border-blue-200';

                html += `
                        <button onclick="startQuiz(${quiz.id}, '${quiz.title.replace(/'/g, "\\'").replace(/"/g, '\\"')}')"
                            class="group quiz-card min-w-full h-full flex flex-col justify-between p-10 xl:p-12 bg-gradient-to-br from-white via-blue-50 to-indigo-100 border-2 ${recommendedCardClass} rounded-3xl hover:border-blue-400 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 shadow-lg hover:shadow-blue-200 backdrop-blur-sm relative overflow-hidden min-h-[260px] xl:min-h-[320px]">
                            ${recommendedBadge}
                            <span class="absolute top-4 right-4 px-4 py-2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 text-white font-bold text-xs shadow-lg">ID ${quiz.id}</span>
                            <div class="flex items-start space-x-4 mb-6">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-700 rounded-2xl flex items-center justify-center shadow-xl flex-shrink-0 border-4 border-white">
                                    <span class="text-white font-extrabold text-2xl">${quiz.id}</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-extrabold text-2xl text-gray-800 leading-tight group-hover:text-blue-700 drop-shadow">${quiz.title}</h3>
                                    <p class="text-sm font-bold text-indigo-900 bg-indigo-100 px-4 py-2 rounded-full mt-2 inline-block shadow">${quiz.subject}</p>
                                </div>
                            </div>
                            <p class="text-gray-800 text-base leading-relaxed font-medium mb-2">${quiz.description || 'Diagnostic niveau ' + level}</p>
                            <div class="flex flex-wrap gap-2 mt-4">
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs font-semibold shadow">${quiz.level}</span>
                                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-semibold shadow">Quiz</span>
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

            console.log('SUCCESS: Contenu généré avec ' + pagedQuiz.length + ' quiz sur la page ' + currentPage + '/' + totalPages);
            app.innerHTML = html;

            const pagerButtons = app.querySelectorAll('button[data-quiz-page]');
            pagerButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const nextPage = Number(button.getAttribute('data-quiz-page') || '1');
                    if (Number.isNaN(nextPage) || nextPage < 1 || nextPage > totalPages || nextPage === currentPage) {
                        return;
                    }
                    currentPage = nextPage;
                    renderQuizPage();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });

            const input = app.querySelector('#quiz-id-input');
            const launchBtn = app.querySelector('#quiz-id-start-btn');
            const launchById = () => {
                const requestedId = Number(input?.value || 0);
                if (!requestedId) {
                    alert('Entre un ID de quiz valide.');
                    return;
                }

                const selected = quizArray.find(item => Number(item.id) === requestedId);
                if (!selected) {
                    alert(`Quiz ID ${requestedId} introuvable pour ce niveau.`);
                    return;
                }

                startQuiz(selected.id, selected.title || `Quiz ${selected.id}`);
            };

            if (launchBtn) {
                launchBtn.addEventListener('click', launchById);
            }
            if (input) {
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        launchById();
                    }
                });
            }
        }

        renderQuizPage();

    } catch (error) {
        console.error('CATCH ERROR:', error);
        console.error('Error stack:', error.stack);

        app.innerHTML = `
            <div class="text-center py-20 px-4">
                <div class="w-28 h-28 bg-red-100 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl">
                    <span class="text-5xl">⚠️</span>
                </div>
                <h2 class="text-3xl font-bold text-red-600 mb-4">Chargement échoué</h2>
                <p class="text-lg text-gray-600 mb-8">${error.message}</p>
                <p class="text-sm text-gray-500 mb-8 max-w-2xl mx-auto bg-gray-100 p-4 rounded-lg font-mono">
                    ${error.stack ? error.stack.substring(0, 300) : 'No stack trace'}
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
    const app = document.getElementById('diagnostic-app');

    app.innerHTML = `
        <div class="min-h-[60vh] flex items-center justify-center p-8">
            <div class="text-center">
                <div class="animate-spin rounded-full h-20 w-20 border-4 border-gray-200 border-t-blue-600 mx-auto mb-8"></div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">${title}</h2>
                <p class="text-xl text-gray-600">Chargement des questions...</p>
            </div>
        </div>
    `;

    try {
        const response = await fetch(`${window.apiBasePath}/quiz.php?id=${contentId}`);
        if (!response.ok) {
            if (response.status === 404) {
                throw new Error('Ce quiz est en cours de preparation. Reviens dans un instant, il arrive bientot.');
            }
            throw new Error('Le quiz est temporairement indisponible. Merci de reessayer dans quelques instants.');
        }

        const quizData = await response.json();
        const questions = quizData.quiz?.questions || [];

        if (questions.length === 0) throw new Error('Quiz vide');

        currentQuizState = {
            contentId,
            title,
            totalQuestions: questions.length,
            startedAt: Date.now()
        };

        // Questions UI
        let html = `
            <header class="sticky top-0 bg-white/80 backdrop-blur-md border-b-2 border-blue-100 z-20 p-6 mb-8 shadow-sm">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            ${title}
                        </h1>
                        <div class="flex items-center space-x-4 text-sm font-semibold">
                            <span>Progression: <span id="progress-count">0</span>/${questions.length}</span>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs">Prêt</span>
                        </div>
                    </div>
                </div>
            </header>
        `;

        questions.forEach((question, index) => {
            const qIndex = index + 1;
            html += `
                <section class="question mb-10 p-8 bg-white/70 backdrop-blur-sm border border-blue-100 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300">
                    <div class="flex items-start mb-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-3xl flex items-center justify-center font-bold text-2xl mr-6 flex-shrink-0 shadow-2xl">
                            Q${qIndex}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-2xl font-semibold text-gray-800 leading-tight mb-6">${question.question}</h3>
                        </div>
                    </div>

                    <div class="space-y-4">
                        ${renderQuestionInputs(question, index)}
                    </div>
                </section>
            `;
        });

        html += `
            <div class="sticky bottom-6 z-30 bg-white/90 backdrop-blur-md border-t-4 border-green-200 p-8 rounded-3xl shadow-2xl mx-4 lg:mx-0">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6 text-lg font-semibold">
                        <span>✅ <span id="progress-count">0</span>/${questions.length} répondues</span>
                        <span class="text-green-700 text-xl font-bold">Prêt à corriger !</span>
                    </div>
                    <button onclick="submitQuiz()"
                            class="w-full bg-gradient-to-r from-green-500 via-emerald-600 to-teal-600 hover:from-green-600 hover:via-emerald-700 hover:to-teal-700 text-white py-6 px-12 rounded-3xl text-2xl font-black shadow-2xl hover:shadow-3xl transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center group">
                        <span class="mr-4">📊 Corriger mon diagnostic</span>
                        <span class="px-4 py-2 bg-white/30 backdrop-blur-sm rounded-2xl text-lg font-bold group-hover:bg-white/50 transition-all">GO !</span>
                    </button>
                </div>
            </div>
        `;

        app.innerHTML = html;

        // Live progress
        const inputs = document.querySelectorAll('input[name^="q"]');
        inputs.forEach(input => {
            input.addEventListener('change', updateProgress);
            input.addEventListener('input', updateProgress);
        });
        updateProgress();

    } catch (error) {
        console.error('Quiz load error:', error);
        app.innerHTML = `
            <div class="min-h-screen flex items-center justify-center p-8">
                <div class="max-w-md text-center">
                    <div class="w-32 h-32 bg-gradient-to-br from-red-400 to-red-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                        <span class="text-5xl font-bold">!</span>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">${title}</h2>
                    <p class="text-xl text-red-600 mb-8 font-semibold">${error.message}</p>
                    <p class="text-gray-600 mb-12">Vérifiez que src/data/quiz/${contentId}.json existe</p>
                    <button onclick="history.back()" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl transition-all">
                        ← Autre quiz
                    </button>
                </div>
            </div>
        `;
    }
}

function updateProgress() {
    const answered = Object.keys(collectAnswers()).length;
    const total = currentQuizState?.totalQuestions || document.querySelectorAll('.question').length;
    const elements = document.querySelectorAll('#progress-count');
    elements.forEach(el => {
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
        alert('Commence par repondre a quelques questions pour recevoir ton diagnostic.');
        return;
    }

    const durationSeconds = Math.max(
        1,
        Math.round((Date.now() - (currentQuizState.startedAt || Date.now())) / 1000)
    );

    // Préparer payload avec métadonnées de révision si applicable
    const payload = {
        quiz_id: currentQuizState.contentId,
        answers,
        duration_seconds: durationSeconds
    };

    if (currentQuizState.isReview && currentQuizState.originalAnswers) {
        payload.is_review = true;
        payload.original_answers = currentQuizState.originalAnswers;
        payload.reviewed_question_indexes = currentQuizState.reviewQuestionIndexes || [];
    }

    let responseData;
    try {
        const response = await fetch(`${window.apiBasePath}/diagnostic/submit.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const bodyText = await response.text();
        let parsed;
        try {
            parsed = JSON.parse(bodyText);
        } catch (parseError) {
            throw new Error('Reponse JSON invalide du serveur');
        }

        if (!response.ok || !parsed.success) {
            const apiMessage = parsed?.error || parsed?.error?.message || `HTTP ${response.status}`;
            throw new Error(apiMessage);
        }

        responseData = parsed.data || {};
    } catch (error) {
        console.error('Erreur soumission diagnostic:', error);
        alert(`Impossible de corriger le diagnostic: ${error.message}`);
        return;
    }

    const score = Number(responseData.score || 0);
    const oldScore = responseData.old_score !== undefined && responseData.old_score !== null
        ? Number(responseData.old_score)
        : null;
    const isReviewResult = responseData.is_review === true;
    const feedback = responseData.feedback || {};
    const xpGained = Number(responseData.xp_gained || 0);
    const xpTotal = Number(responseData.xp_total || 0);
    const strengths = Array.isArray(feedback.strengths) ? feedback.strengths : [];
    const toReview = Array.isArray(feedback.to_review) ? feedback.to_review : [];

    // Stocker les réponses initiales pour révision future
    if (!isReviewResult) {
        currentQuizState.originalAnswers = answers;
        currentQuizState.originalScore = score;
    }

    document.body.style.overflow = 'hidden';

    // Message conditionnel selon révision ou première tentative
    let scoreDisplay = `<h1 class="text-5xl font-black bg-gradient-to-r from-green-600 to-emerald-700 bg-clip-text text-transparent mb-6">
        ${score}/100
    </h1>`;

    if (isReviewResult && oldScore !== null) {
        const improvement = score - oldScore;
        const improvementText = improvement > 0
            ? `<span class="text-green-600">+${improvement.toFixed(1)} points</span>`
            : `<span class="text-orange-600">${improvement.toFixed(1)} points</span>`;

        scoreDisplay = `
            <div class="mb-6">
                <div class="text-sm font-semibold text-gray-600 mb-2">Score initial : ${oldScore}/100</div>
                <h1 class="text-5xl font-black bg-gradient-to-r from-green-600 to-emerald-700 bg-clip-text text-transparent mb-2">
                    ${score}/100
                </h1>
                <div class="text-2xl font-bold">${improvementText}</div>
            </div>
        `;
    }

    document.querySelector('#diagnostic-app').innerHTML = `
        <div class="min-h-screen flex items-center justify-center p-8 bg-gradient-to-br from-green-50 to-emerald-50">
            <div class="max-w-2xl mx-auto text-center backdrop-blur-xl bg-white/90 rounded-3xl p-12 shadow-2xl border border-green-200">
                <div class="w-32 h-32 bg-gradient-to-br from-green-400 to-emerald-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl ${isReviewResult ? '' : 'animate-bounce'}">
                    <span class="text-5xl font-black">${isReviewResult ? '🎯' : '⭐'}</span>
                </div>
                ${scoreDisplay}
                <p class="text-2xl font-semibold text-gray-700 mb-4">${answeredCount}/${totalQuestions} reponses analysees</p>
                <p class="text-lg text-gray-700 mb-8">${escapeHtml(feedback.message || 'Bravo pour ton effort, continue comme ca !')}</p>
                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div class="p-6 bg-green-100 rounded-2xl">
                        <span class="text-3xl font-bold text-green-700">✅</span>
                        <p class="font-bold text-lg mt-2">Points forts</p>
                        <p class="text-sm text-green-800">${strengths.length ? escapeHtml(strengths.join(', ')) : 'Progression en cours'}</p>
                    </div>
                    <div class="p-6 bg-yellow-100 rounded-2xl">
                        <span class="text-3xl font-bold text-yellow-700">⚠️</span>
                        <p class="font-bold text-lg mt-2">À revoir</p>
                        <p class="text-sm text-yellow-800">${toReview.length ? escapeHtml(toReview.join(', ')) : 'Tres bon niveau global'}</p>
                    </div>
                    <div class="p-6 bg-blue-100 rounded-2xl">
                        <span class="text-3xl font-bold text-blue-700">📚</span>
                        <p class="font-bold text-lg mt-2">Progression</p>
                        <p class="text-sm text-blue-800">+${xpGained} XP (total ${xpTotal})</p>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <button onclick="retryQuiz()"
                            class="flex-1 inline-flex items-center justify-center px-8 py-5 bg-gradient-to-r from-yellow-500 to-amber-600 hover:from-yellow-600 hover:to-amber-700 text-white font-black text-lg rounded-3xl shadow-2xl hover:shadow-3xl transform hover:scale-[1.05] transition-all duration-300">
                        🔄 Refaire tout le quiz
                    </button>
                    ${toReview.length > 0 ? `
                    <button onclick="reviewFailedQuestions(${JSON.stringify(toReview).replace(/"/g, '&quot;')})"
                            class="flex-1 inline-flex items-center justify-center px-8 py-5 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-black text-lg rounded-3xl shadow-2xl hover:shadow-3xl transform hover:scale-[1.05] transition-all duration-300">
                        ⚠️ Revoir les questions ratées
                    </button>
                    ` : ''}
                </div>
                ${score < 50 ? `
                <button onclick="showCorrectionsHelp()"
                        class="mb-6 inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white font-bold rounded-2xl shadow-xl transition-all">
                    💡 Voir les corrections pour progresser
                </button>
                ` : ''}
                <a href="?page=exercices"
                   class="inline-flex items-center px-12 py-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-xl rounded-3xl shadow-2xl hover:shadow-3xl transform hover:scale-[1.05] transition-all duration-300">
                    🚀 Commencer mes exercices adaptés
                    <span class="ml-4">→</span>
                </a>
            </div>
        </div>
    `;

    setTimeout(() => document.body.style.overflow = 'auto', 100);
}

function retryQuiz() {
    if (!currentQuizState) {
        alert('Impossible de relancer le quiz (état perdu). Retournez à la liste des quiz.');
        return;
    }
    startQuiz(currentQuizState.contentId, currentQuizState.title);
}

async function showCorrectionsHelp() {
    if (!currentQuizState) {
        alert('Etat du quiz indisponible.');
        return;
    }

    try {
        const response = await fetch(`${window.apiBasePath}/quiz.php?id=${currentQuizState.contentId}&include_answers=1`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const quizData = await response.json();
        const answersData = quizData.answers || quizData.quiz_answers || quizData.quiz?.answers || [];

        if (!Array.isArray(answersData) || answersData.length === 0) {
            alert('Corrections indisponibles pour ce quiz.');
            return;
        }

        const rows = answersData
            .slice(0, 8)
            .map((row, idx) => {
                const correction = escapeHtml((row && row.correction) ? String(row.correction) : 'Correction non disponible.');
                return `<div class="p-4 border rounded-xl bg-gray-50 mb-3"><p class="font-bold mb-1">Q${idx + 1}</p><p class="text-sm text-gray-700">${correction}</p></div>`;
            })
            .join('');

        const app = document.getElementById('diagnostic-app');
        app.innerHTML = `
            <div class="max-w-4xl mx-auto p-6">
                <div class="bg-white rounded-3xl border shadow-xl p-6">
                    <h2 class="text-2xl font-black mb-2">Corrections guidees</h2>
                    <p class="text-gray-600 mb-6">Lis les explications, puis refais le quiz pour gagner des points.</p>
                    ${rows}
                    <div class="mt-6 flex gap-3">
                        <button onclick="retryQuiz()" class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold">🔄 Refaire le quiz</button>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        console.error('Erreur affichage corrections:', error);
        alert('Impossible de charger les corrections pour le moment.');
    }
}

function reviewFailedQuestions(toReviewLabels) {
    if (!currentQuizState) {
        alert('Impossible de revoir les questions (état perdu). Retournez à la liste des quiz.');
        return;
    }

    if (!currentQuizState.originalAnswers) {
        alert('Aucune réponse initiale trouvée. Refais le quiz complet.');
        retryQuiz();
        return;
    }

    const app = document.getElementById('diagnostic-app');

    app.innerHTML = `
        <div class="min-h-[60vh] flex items-center justify-center p-8">
            <div class="text-center">
                <div class="animate-spin rounded-full h-20 w-20 border-4 border-gray-200 border-t-orange-600 mx-auto mb-8"></div>
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Révision ciblée</h2>
                <p class="text-xl text-gray-600">Chargement des questions à revoir...</p>
            </div>
        </div>
    `;

    fetch(`${window.apiBasePath}/quiz.php?id=${currentQuizState.contentId}`)
        .then(response => response.json())
        .then(quizData => {
            const allQuestions = quizData.quiz?.questions || [];

            // Extraire les numéros de questions depuis les labels (ex: "Q1", "Q2")
            const questionNumbers = toReviewLabels
                .map(label => {
                    const match = label.match(/Q(\d+)/i);
                    return match ? parseInt(match[1], 10) - 1 : null;
                })
                .filter(n => n !== null && n >= 0 && n < allQuestions.length);

            if (questionNumbers.length === 0) {
                throw new Error('Aucune question à revoir trouvée');
            }

            const questionsToReview = questionNumbers.map(idx => allQuestions[idx]);

            // Mettre à jour l'état pour la révision
            currentQuizState.totalQuestions = questionsToReview.length;
            currentQuizState.startedAt = Date.now();
            currentQuizState.isReview = true;
            currentQuizState.reviewQuestionIndexes = questionNumbers;

            renderReviewQuiz(questionsToReview, toReviewLabels);
        })
        .catch(error => {
            console.error('Erreur chargement révision:', error);
            app.innerHTML = `
                <div class="min-h-screen flex items-center justify-center p-8">
                    <div class="max-w-md text-center">
                        <div class="w-32 h-32 bg-gradient-to-br from-red-400 to-red-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                            <span class="text-5xl font-bold">!</span>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Révision impossible</h2>
                        <p class="text-xl text-red-600 mb-8 font-semibold">${error.message}</p>
                        <button onclick="retryQuiz()" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl transition-all">
                            ← Refaire tout le quiz
                        </button>
                    </div>
                </div>
            `;
        });
}

function renderReviewQuiz(questions, labels) {
    const app = document.getElementById('diagnostic-app');

    let html = `
        <header class="sticky top-0 bg-white/80 backdrop-blur-md border-b-2 border-orange-100 z-20 p-6 mb-8 shadow-sm">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                            ${currentQuizState.title} - Révision ciblée
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">Questions à revoir : ${labels.join(', ')}</p>
                    </div>
                    <div class="flex items-center space-x-4 text-sm font-semibold">
                        <span>Progression: <span id="progress-count">0</span>/${questions.length}</span>
                        <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs">Révision</span>
                    </div>
                </div>
            </div>
        </header>
    `;

    questions.forEach((question, index) => {
        const qIndex = index + 1;
        html += `
            <section class="question mb-10 p-8 bg-white/70 backdrop-blur-sm border border-orange-100 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="flex items-start mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-700 text-white rounded-3xl flex items-center justify-center font-bold text-2xl mr-6 flex-shrink-0 shadow-2xl">
                        ${labels[index] || `Q${qIndex}`}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-2xl font-semibold text-gray-800 leading-tight mb-6">${question.question}</h3>
                    </div>
                </div>

                <div class="space-y-4">
                    ${renderQuestionInputs(question, index)}
                </div>
            </section>
        `;
    });

    html += `
        <div class="sticky bottom-6 z-30 bg-white/90 backdrop-blur-md border-t-4 border-orange-200 p-8 rounded-3xl shadow-2xl mx-4 lg:mx-0">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-6 text-lg font-semibold">
                    <span>✅ <span id="progress-count">0</span>/${questions.length} répondues</span>
                    <span class="text-orange-700 text-xl font-bold">Prêt à corriger !</span>
                </div>
                <button onclick="submitQuiz()"
                        class="w-full bg-gradient-to-r from-orange-500 via-red-600 to-pink-600 hover:from-orange-600 hover:via-red-700 hover:to-pink-700 text-white py-6 px-12 rounded-3xl text-2xl font-black shadow-2xl hover:shadow-3xl transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center group">
                    <span class="mr-4">📊 Corriger ma révision</span>
                    <span class="px-4 py-2 bg-white/30 backdrop-blur-sm rounded-2xl text-lg font-bold group-hover:bg-white/50 transition-all">GO !</span>
                </button>
            </div>
        </div>
    `;

    app.innerHTML = html;

    // Live progress
    const inputs = document.querySelectorAll('input[name^="q"]');
    inputs.forEach(input => {
        input.addEventListener('change', updateProgress);
        input.addEventListener('input', updateProgress);
    });
    updateProgress();
}

function renderQuestionInputs(question, index) {
    const type = String(question.type || '').toLowerCase();
    const choiceList = Array.isArray(question.choices) ? question.choices : [];

    if (choiceList.length > 0) {
        return choiceList.map(choice => {
            const safeChoice = escapeHtml(choice);
            return `
                <label class="flex items-center p-6 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-blue-400 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group min-h-[80px]">
                    <input type="radio" name="q${index}" value="${safeChoice}"
                           class="w-7 h-7 text-blue-600 border-4 border-gray-300 focus:ring-4 focus:ring-blue-200 mr-6 accent-blue-600 shadow-md">
                    <span class="text-xl leading-relaxed group-hover:text-blue-900 font-medium">${safeChoice}</span>
                </label>
            `;
        }).join('');
    }

    if (type === 'vrai-faux' || type === 'vrai_faux') {
        return ['Vrai', 'Faux'].map(choice => `
            <label class="flex items-center p-6 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-blue-400 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group min-h-[80px]">
                <input type="radio" name="q${index}" value="${choice.toLowerCase()}"
                       class="w-7 h-7 text-blue-600 border-4 border-gray-300 focus:ring-4 focus:ring-blue-200 mr-6 accent-blue-600 shadow-md">
                <span class="text-xl leading-relaxed group-hover:text-blue-900 font-medium">${choice}</span>
            </label>
        `).join('');
    }

    return `<div class="p-1">
        <input type="text" name="q${index}"
               class="w-full p-6 border-2 border-gray-300 rounded-2xl text-xl font-semibold text-gray-800 focus:border-blue-400 focus:ring-4 focus:ring-blue-200 shadow-sm transition-all placeholder-gray-500 min-h-[80px]"
               placeholder="Tape ta reponse exacte ici...">
    </div>`;
}

function collectAnswers() {
    const answers = {};

    document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
        answers[input.name] = input.value;
    });

    document.querySelectorAll('input[type="text"]').forEach(input => {
        const value = input.value.trim();
        if (value !== '') {
            answers[input.name] = value;
        }
    });

    return answers;
}
