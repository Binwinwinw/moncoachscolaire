<?php
?>
<!-- Modals pour chaque matière -->
        <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('francais')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-francais-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">📖</span> Français</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Lecture et compréhension de textes complexes</li>
                        <li>Expression écrite : récit, description, argumentation, dissertation littéraire</li>
                        <li>Expression orale : argumentation orale, prise de parole en public</li>
                        <li>Étude de la langue : grammaire, orthographe, analyse syntaxique</li>
                        <li>Littérature : œuvres du XIXe siècle, théâtre moderne, poésie engagée</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Lecture régulière d'œuvres variées, pratique de l'écriture argumentative, analyse de textes, révision des règles grammaticales.</p>
            </div>
        </div>
        <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-maths-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧮</span> Mathématiques</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Nombres et calculs : nombres réels, calcul algébrique</li>
                        <li>Géométrie : triangles, propriétés, théorèmes</li>
                        <li>Fonctions et analyse : tableaux de valeurs, représentations graphiques, variations</li>
                        <li>Statistiques et probabilités</li>
                        <li>Algorithmique et programmation : Python, algorithmes complexes</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Exercices quotidiens, utilisation de tableaux de valeurs, représentation graphique, résolution de problèmes complexes.</p>
            </div>
        </div>
        <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-histoire-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🌍</span> Histoire-Géo</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Histoire : périodes chronologiques, événements majeurs</li>
                        <li>Géographie : espaces, sociétés, environnement</li>
                        <li>Méthodes : analyse de documents, cartes, frises</li>
                        <li>Citoyenneté : valeurs républicaines, droits et devoirs</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Création de frises chronologiques, observation de cartes, débats sur l'actualité, analyse critique de documents.</p>
            </div>
        </div>
        <div id="modal-svt" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-svt-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('svt')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-svt-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧪</span> SVT</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>La matière et l'énergie</li>
                        <li>La Terre et l'Univers</li>
                        <li>Le vivant et son évolution</li>
                        <li>L'environnement et développement durable</li>
                        <li>Méthodes scientifiques : observation, expérimentation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Expériences simples, observation de la nature, schémas scientifiques, compréhension des enjeux environnementaux.</p>
            </div>
        </div>
        <div id="modal-physique" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-physique-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('physique')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-physique-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">⚗️</span> Physique-Chimie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Mouvement et forces : équilibre, conditions d'équilibre</li>
                        <li>Énergie : formes, transformations</li>
                        <li>Matière : propriétés, mélanges, solutions</li>
                        <li>Expérimentation : mesures, protocoles</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Manipulations concrètes, mesures quotidiennes, observation des phénomènes physiques, compréhension des équilibres.</p>
            </div>
        </div>
        <div id="modal-technologie" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-technologie-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('technologie')" class="absolute top-4 right-4 text-gray-400 hover:text-green-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-technologie-title" class="text-2xl font-bold text-green-800 mb-4 flex items-center gap-2"><span class="text-3xl">🔧</span> Technologie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-800 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Conception et réalisation de systèmes techniques complexes</li>
                        <li>Programmation avancée</li>
                        <li>Utilisation d'outils numériques adaptés</li>
                        <li>Analyse de systèmes techniques</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-800">💡 Conseils de remédiation :</span> Conception de systèmes complexes, programmation avancée, projets collaboratifs.</p>
            </div>
        </div>
        <div id="modal-anglais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-anglais-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('anglais')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-anglais-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🌐</span> Anglais</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Compréhension orale et écrite</li>
                        <li>Expression orale : présentation, interaction, argumentation</li>
                        <li>Expression écrite : description, récit, argumentation</li>
                        <li>Civilisation : culture anglophone</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Écoute de podcasts, visionnage de vidéos, conversation en anglais, jeux linguistiques, lecture de textes variés.</p>
            </div>
        </div>
        <div id="modal-arts" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-arts-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('arts')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-arts-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🎨</span> Arts</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Arts plastiques : techniques, composition, expression</li>
                        <li>Éducation musicale : écoute, pratique, culture</li>
                        <li>Arts du spectacle : théâtre, danse, cinéma</li>
                        <li>Analyse d'œuvres : description, interprétation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Pratique artistique régulière, visites culturelles, création personnelle, analyse d'œuvres.</p>
            </div>
        </div>
