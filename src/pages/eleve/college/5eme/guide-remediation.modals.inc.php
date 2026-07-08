<?php
?>
<!-- Modals pour chaque matière -->
        <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('francais')" class="absolute top-4 right-4 text-gray-400 <?php echo $modal_close_hover; ?> text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-francais-title" class="text-2xl font-bold <?php echo $modal_title; ?> mb-4 flex items-center gap-2"><span class="text-3xl">📖</span> Français</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold <?php echo $modal_heading; ?> mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Lecture et compréhension de textes variés</li>
                        <li>Expression écrite : récit, description, dialogue, argumentation</li>
                        <li>Expression orale : présentation et argumentation</li>
                        <li>Étude de la langue : grammaire et orthographe</li>
                        <li>Littérature : œuvres du patrimoine et contemporaines</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Lecture quotidienne de 25 minutes, exercices de grammaire réguliers, écriture créative hebdomadaire, travail sur l'argumentation.</p>
            </div>
        </div>
        <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-maths-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧮</span> Mathématiques</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Nombres et calculs : nombres rationnels, fractions, décimaux</li>
                        <li>Géométrie : figures planes, solides, transformations, théorème de Pythagore</li>
                        <li>Fonctions : introduction aux fonctions</li>
                        <li>Statistiques et probabilités</li>
                        <li>Algorithmique et programmation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Exercices quotidiens de calcul mental, manipulation d'objets géométriques, jeux mathématiques, initiation à la programmation.</p>
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
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Création de frises chronologiques, observation de cartes, débats sur l'actualité, analyse de documents.</p>
            </div>
        </div>
        <div id="modal-svt" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-svt-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('svt')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-svt-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧪</span> SVT</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Le vivant : cellules, organismes, biodiversité</li>
                        <li>La matière : états, transformations, réactions</li>
                        <li>La Terre et l'Univers : planète, système solaire</li>
                        <li>Méthodes scientifiques : observation, expérimentation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Expériences simples à la maison, observation de la nature, schémas et dessins scientifiques.</p>
            </div>
        </div>
        <div id="modal-physique" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-physique-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('physique')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-physique-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">⚗️</span> Physique-Chimie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Énergie : formes, transformations, économie</li>
                        <li>Mouvement : vitesse, trajectoires</li>
                        <li>Matière : propriétés, mélanges, solutions</li>
                        <li>Électricité : circuits électriques simples</li>
                        <li>Expérimentation : mesures, protocoles</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Manipulations concrètes, mesures quotidiennes, observation des phénomènes physiques, construction de circuits simples.</p>
            </div>
        </div>
        <div id="modal-technologie" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-technologie-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('technologie')" class="absolute top-4 right-4 text-gray-400 hover:text-green-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-technologie-title" class="text-2xl font-bold text-green-800 mb-4 flex items-center gap-2"><span class="text-3xl">🔧</span> Technologie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-800 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Conception et réalisation de projets techniques</li>
                        <li>Programmation et algorithmique</li>
                        <li>Utilisation d'outils numériques</li>
                        <li>Analyse de systèmes techniques</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-800">💡 Conseils de remédiation :</span> Réalisation de maquettes, projets concrets, programmation simple, manipulation d'outils numériques.</p>
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
                        <li>Expression orale : présentation, interaction</li>
                        <li>Expression écrite : description, récit</li>
                        <li>Civilisation : culture anglophone</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Écoute de podcasts, visionnage de vidéos, conversation en anglais, jeux linguistiques.</p>
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
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Pratique artistique régulière, visites culturelles, création personnelle.</p>
            </div>
        </div>
