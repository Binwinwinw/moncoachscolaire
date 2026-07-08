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
                        <li>Lecture et analyse de textes complexes</li>
                        <li>Expression écrite : commentaire, dissertation, écriture d'invention</li>
                        <li>Expression orale : exposé oral, entretien, lecture expressive</li>
                        <li>Étude de la langue : analyse stylistique, figures de style</li>
                        <li>Littérature : œuvres du XXe siècle, théâtre contemporain, poésie moderne</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Lecture régulière d'œuvres variées, pratique de l'écriture argumentative, préparation à l'oral du Brevet, analyse de textes complexes.</p>
            </div>
        </div>
        <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-maths-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧮</span> Mathématiques</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Nombres et calculs : nombres complexes, calcul avancé</li>
                        <li>Géométrie : trigonométrie, propriétés avancées</li>
                        <li>Fonctions et analyse : représentations multiples, dérivées simples</li>
                        <li>Statistiques et probabilités : probabilités conditionnelles</li>
                        <li>Algorithmique et programmation : Python, algorithmes complexes</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Entraînement aux exercices types du Brevet, gestion du temps, méthodes de résolution, correction des erreurs fréquentes.</p>
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
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Création de frises chronologiques, observation de cartes, débats sur l'actualité, préparation aux épreuves du Brevet.</p>
            </div>
        </div>
        <div id="modal-svt" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-svt-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('svt')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-svt-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧪</span> SVT</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>La génétique et l'évolution</li>
                        <li>L'écologie et les écosystèmes</li>
                        <li>Le corps humain et la santé</li>
                        <li>La Terre et l'Univers</li>
                        <li>Méthodes scientifiques : observation, expérimentation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Expériences simples, observation de la nature, schémas scientifiques, préparation aux épreuves du Brevet.</p>
            </div>
        </div>
        <div id="modal-physique" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-physique-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('physique')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-physique-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">⚗️</span> Physique-Chimie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Chimie organique : formules, reconnaissances</li>
                        <li>Énergie : formes, transformations</li>
                        <li>Matière : propriétés, mélanges, solutions</li>
                        <li>Expérimentation : mesures, protocoles</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Manipulations concrètes, mesures quotidiennes, observation des phénomènes physiques, préparation aux épreuves du Brevet.</p>
            </div>
        </div>
        <div id="modal-technologie" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-technologie-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('technologie')" class="absolute top-4 right-4 text-gray-400 hover:text-green-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-technologie-title" class="text-2xl font-bold text-green-800 mb-4 flex items-center gap-2"><span class="text-3xl">🔧</span> Technologie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-800 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Conception et réalisation de systèmes complexes</li>
                        <li>Programmation avancée</li>
                        <li>Innovation technologique</li>
                        <li>Analyse de systèmes techniques</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-800">💡 Conseils de remédiation :</span> Conception de systèmes complexes, programmation avancée, projets collaboratifs, innovation technologique.</p>
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
        <div id="modal-brevet" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-brevet-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('brevet')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-brevet-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">📝</span> Préparation au Brevet</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Structure de l'examen :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Français (4h) : dictée, rédactions, questions</li>
                        <li>Mathématiques (2h) : exercices et problèmes</li>
                        <li>Histoire-Géographie (2h) : questions et analyse</li>
                        <li>Sciences (1h) : questions de cours et exercices</li>
                        <li>Oral : exposé et entretien</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Stratégies de réussite :</span> Réviser régulièrement les notions essentielles, s'entraîner aux exercices types, gérer son temps lors des épreuves, relire et corriger ses copies.</p>
            </div>
        </div>
