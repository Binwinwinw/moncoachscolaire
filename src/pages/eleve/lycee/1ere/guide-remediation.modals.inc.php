<?php
?>
<!-- Modals pour chaque matiere -->
            <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('francais')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-francais-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Francais</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>?criture : Expression argumentee, commentaire, dissertation courte, synthese</li>
                            <li>Analyse de texte : Identification du registre, procedes stylistiques, tension argumentative</li>
                            <li>Preparation au grand oral : Presentation structuree, argumentation</li>
                            <li>Litterature : 'uvres du programme de Première</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Travail sur l'écriture argumentee, analyse de texte reguliere, preparation au grand oral, organisation d'un carnet de revision par matiere.</p>
                </div>
            </div>
            <div id="modal-sciences" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-sciences-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('sciences')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-sciences-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Sciences (SVT / Physique-Chimie)</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Methodes experimentales : Analyse de resultats, mise en relation des connaissances</li>
                            <li>Physique-Chimie : ?quilibres, reactions, notions d'energie</li>
                            <li>SVT : Approfondissement des connaissances biologiques et geologiques</li>
                            <li>Analyse critique : Interpretation de resultats experimentaux</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Renforcement des methodes experimentales, exercices sur equilibres et reactions, preparation des dossiers de specialite par projets concrets.</p>
                </div>
            </div>
            <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-histoire-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Histoire-Geo / EMC</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Construction d'une synthese et d'un raisonnement structure</li>
                            <li>Utilisation de documents historiques et geographiques</li>
                            <li>Analyse critique de sources</li>
                            <li>Argumentation et expression écrite</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Travail sur la capacite a construire une synthese, utilisation de documents pour etayer un propos, pratique reguliere de l'argumentation.</p>
                </div>
            </div>
            <div id="modal-methodo" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-methodo-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('methodo')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-methodo-title" class="text-2xl font-bold text-purple-800 mb-4 flex items-center gap-2">Methodologie et Gestion du Travail</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-800 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Organisation : Carnet de revision par matiere</li>
                            <li>Travail regulier : Seances courtes + sorties d'entrainement</li>
                            <li>Preparation des dossiers : Projets concrets pour les specialites</li>
                            <li>Gestion du temps : Planning efficace</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-800">Conseils de remédiation :</span> Organiser un carnet de revision par matiere, travail regulier avec séances courtes, preparer les dossiers de specialite par projets concrets.</p>
                </div>
            </div>
            <div id="modal-orientation" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-orientation-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('orientation')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-orientation-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Orientation - Choix des Specialites</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Comment choisir :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>évaluer ses gouts : Quelles matières te plaisent le plus ?</li>
                            <li>évaluer ses competences : Dans quelles matières réussis-tu le mieux ?</li>
                            <li>évaluer les debouches : Vers quels métiers ou études veux-tu t'orienter ?</li>
                            <li>Plan de 6 semaines : Tester des specialites et valider un choix</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Explication de comment evaluer ses gouts, ses competences et le debouche des specialites. Proposer un plan de 6 semaines pour tester des specialites et valider un choix.</p>
                </div>
            </div>
            <div id="modal-oral" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-oral-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('oral')" class="absolute top-4 right-4 text-gray-400 hover:text-fuchsia-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-oral-title" class="text-2xl font-bold text-fuchsia-700 mb-4 flex items-center gap-2">Preparation au Grand Oral</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-fuchsia-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Presentation structuree : Organisation de la prise de parole</li>
                            <li>Argumentation : Construction d'un raisonnement clair</li>
                            <li>Gestion du stress : Techniques de relaxation et de preparation</li>
                            <li>Expression orale : Clarte, fluidite, conviction</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-fuchsia-700">Conseils de remédiation :</span> Conseils pour preparer le grand oral, simulations regulieres, fiches synthetiques pour chaque projet, entrainement a la prise de parole.</p>
                </div>
            </div>
