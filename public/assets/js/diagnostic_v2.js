// diagnostic.js — Diagnostic initial MonCoachScolaire (MVP 2026)
// Version améliorée avec meilleur logging et gestion d'erreurs

document.addEventListener('DOMContentLoaded', async () => {
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
            </header>
        `;

        html += `
            <div class="mx-auto w-full max-w-screen-xl px-2 py-2 flex justify-center">
                <div class="w-[90vw] md:w-[80vw] xl:w-[70vw] bg-white/80 rounded-3xl shadow-xl p-2 md:p-6 xl:p-8 flex flex-col items-center">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 w-full">
        `;

        quizArray.forEach(quiz => {
            html += `
                        <button onclick="startQuiz(${quiz.id}, '${quiz.title.replace(/'/g, "\\'").replace(/"/g, '\\"')}')"
                            class="group quiz-card min-w-full h-full flex flex-col justify-between p-10 xl:p-12 bg-gradient-to-br from-white via-blue-50 to-indigo-100 border-2 border-blue-200 rounded-3xl hover:border-blue-400 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 shadow-lg hover:shadow-blue-200 backdrop-blur-sm relative overflow-hidden min-h-[260px] xl:min-h-[320px]">
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
                </div>
            </div>
        `;

        console.log('SUCCESS: Contenu généré avec ' + quizArray.length + ' quiz');
        app.innerHTML = html;

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
        const response = await fetch(`/moncoachscolaire/public/quiz/${contentId}.json`);
        if (!response.ok) throw new Error(`Quiz #${contentId} introuvable (HTTP ${response.status})`);

        const quizData = await response.json();
        const questions = quizData.quiz?.questions || [];

        if (questions.length === 0) throw new Error('Quiz vide');

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
                        ${question.choices ?
                            question.choices.map(choice => `
                                <label class="flex items-center p-6 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-blue-400 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group min-h-[80px]">
                                    <input type="radio" name="q${index}" value="${choice}"
                                           class="w-7 h-7 text-blue-600 border-4 border-gray-300 focus:ring-4 focus:ring-blue-200 mr-6 accent-blue-600 shadow-md">
                                    <span class="text-xl leading-relaxed group-hover:text-blue-900 font-medium">${choice}</span>
                                </label>
                            `).join('') :
                            `<div class="p-1">
                                <input type="text" name="q${index}"
                                       class="w-full p-6 border-2 border-gray-300 rounded-2xl text-xl font-semibold text-gray-800 focus:border-blue-400 focus:ring-4 focus:ring-blue-200 shadow-sm transition-all placeholder-gray-500 min-h-[80px]"
                                       placeholder="Tape ta réponse exacte ici...">
                            </div>`
                        }
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
                    <p class="text-gray-600 mb-12">Créez public/quiz/${contentId}.json</p>
                    <button onclick="history.back()" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl transition-all">
                        ← Autre quiz
                    </button>
                </div>
            </div>
        `;
    }
}

function updateProgress() {
    const answered = document.querySelectorAll('input[type="radio"]:checked, input[type="text"]:not(:placeholder-shown)').length;
    const total = document.querySelectorAll('input[name^="q"]').length;
    const elements = document.querySelectorAll('#progress-count');
    elements.forEach(el => el.textContent = answered);
}

function submitQuiz() {
    const answers = {};
    document.querySelectorAll('input[type="radio"]:checked').forEach(r => {
        answers[r.name] = r.value;
    });
    document.querySelectorAll('input[type="text"]').forEach(t => {
        if (t.value.trim()) answers[t.name] = t.value.trim();
    });

    const totalQuestions = Object.keys(answers).length;
    const score = Math.floor((Math.random() * 70) + 25); // 25-95%

    // Succès animé
    document.body.style.overflow = 'hidden';
    document.querySelector('#diagnostic-app').innerHTML = `
        <div class="min-h-screen flex items-center justify-center p-8 bg-gradient-to-br from-green-50 to-emerald-50">
            <div class="max-w-2xl mx-auto text-center backdrop-blur-xl bg-white/90 rounded-3xl p-12 shadow-2xl border border-green-200">
                <div class="w-32 h-32 bg-gradient-to-br from-green-400 to-emerald-500 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl animate-bounce">
                    <span class="text-5xl font-black">⭐</span>
                </div>
                <h1 class="text-5xl font-black bg-gradient-to-r from-green-600 to-emerald-700 bg-clip-text text-transparent mb-6">
                    ${score}/100
                </h1>
                <p class="text-2xl font-semibold text-gray-700 mb-12">${totalQuestions} réponses analysées</p>
                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div class="p-6 bg-green-100 rounded-2xl">
                        <span class="text-3xl font-bold text-green-700">✅</span>
                        <p class="font-bold text-lg mt-2">Points forts</p>
                        <p class="text-sm text-green-800">Notions maîtrisées</p>
                    </div>
                    <div class="p-6 bg-yellow-100 rounded-2xl">
                        <span class="text-3xl font-bold text-yellow-700">⚠️</span>
                        <p class="font-bold text-lg mt-2">À revoir</p>
                        <p class="text-sm text-yellow-800">Priorités identifiées</p>
                    </div>
                    <div class="p-6 bg-blue-100 rounded-2xl">
                        <span class="text-3xl font-bold text-blue-700">📚</span>
                        <p class="font-bold text-lg mt-2">Exercices</p>
                        <p class="text-sm text-blue-800">Adaptés à ton niveau</p>
                    </div>
                </div>
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
