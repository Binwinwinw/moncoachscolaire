<?php
?>
<!-- Modals Terminale -->
            <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-maths-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Mathematiques</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Suites : arithmetiques, geometriques, limites</li>
                            <li>Fonctions : derivees avancees, variations, limites</li>
                            <li>Probabilites : lois de probabilite, variables aleatoires</li>
                            <li>Algorithmique : algorithmes complexes, programmation</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Utiliser des annales recentes, s'entrainer en temps limite, corriger avec bareme, noter les axes d'amelioration. Exemple : 1 sujet complet par semaine.</p>
                </div>
            </div>
            <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('francais')" class="absolute top-4 right-4 text-gray-400 hover:text-fuchsia-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-francais-title" class="text-2xl font-bold text-fuchsia-700 mb-4 flex items-center gap-2">Francais</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-fuchsia-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Dissertation : Construction d'une argumentation structuree</li>
                            <li>Commentaire : Analyse approfondie de texte</li>
                            <li>Dictee ciblee : Orthographe et grammaire</li>
                            <li>Expression écrite : Clarte, precision, style</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-fuchsia-700">Conseils de remédiation :</span> Pratique reguliere de la dissertation et du commentaire, revision des regles grammaticales, entrainement a la dictee.</p>
                </div>
            </div>
            <div id="modal-sciences" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-sciences-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('sciences')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-sciences-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Sciences (Physique-Chimie / SVT)</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Physique-Chimie : Problemes complexes, equilibres, reactions</li>
                            <li>SVT : Experimentation, analyse critique de resultats</li>
                            <li>Methodes experimentales : Protocoles, interpretation</li>
                            <li>Analyse critique : Interpretation de resultats</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Resolution de problemes de physique-chimie, experimentation en SVT, analyse critique de resultats, maitrise des protocoles.</p>
                </div>
            </div>
            <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-histoire-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Histoire-Geo</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Methodologie de la dissertation : Structure, argumentation</li>
                            <li>Composition : Synthese structuree</li>
                            <li>Etude de document : Analyse critique</li>
                            <li>Expression écrite : Clarte, precision</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Maitrise de la méthodologie de la dissertation, pratique de la composition, analyse de documents, entrainement regulier.</p>
                </div>
            </div>
            <div id="modal-organisation" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-organisation-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('organisation')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-organisation-title" class="text-2xl font-bold text-purple-800 mb-4 flex items-center gap-2">Organisation des Revisions</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-800 mb-1">o. Calendrier a 12 semaines :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Alternance matières : Repartir le temps par matiere</li>
                            <li>Annales hebdomadaires : 1 sujet complet par semaine</li>
                            <li>Temps de correction : Analyser les erreurs</li>
                            <li>Seances de simulation : ?crits et oraux</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-800">Conseils de remédiation :</span> Calendrier a 12 semaines avec alternance des matières, annales hebdomadaires, temps de correction et séances de simulation (écrits et oraux).</p>
                </div>
            </div>
            <div id="modal-epreuve" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-epreuve-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('epreuve')" class="absolute top-4 right-4 text-gray-400 hover:text-violet-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-epreuve-title" class="text-2xl font-bold text-violet-700 mb-4 flex items-center gap-2">Methode pour l'Epreuve Ecrite</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-violet-700 mb-1">o. étapes cles :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Lire attentivement le sujet et reperer les mots-cles</li>
                            <li>Temps de planification (5-10 min) : Schemas, brouillon</li>
                            <li>Redaction claire et structuree</li>
                            <li>Relecture finale : Verification et correction</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-violet-700">Conseils de remédiation :</span> Lire attentivement le sujet, temps de planification (5-10 min) avec schemas et brouillon, redaction claire et structuree, relecture finale.</p>
                </div>
            </div>
            <div id="modal-oral" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-oral-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('oral')" class="absolute top-4 right-4 text-gray-400 hover:text-fuchsia-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-oral-title" class="text-2xl font-bold text-fuchsia-700 mb-4 flex items-center gap-2">Preparation a l'Oral</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-fuchsia-700 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Simulations regulieres : 2 a 4 semaines avant l'oral</li>
                            <li>Fiches synthetiques : Pour chaque projet</li>
                            <li>Entrainement a la prise de parole</li>
                            <li>Gestion du stress : Techniques de relaxation</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-fuchsia-700">Conseils de remédiation :</span> Simulations regulieres (2 a 4 semaines avant l'oral), fiches synthetiques pour chaque projet, entrainement a la prise de parole et gestion du stress.</p>
                </div>
            </div>
            <div id="modal-annales" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-annales-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('annales')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-annales-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Annales et Sujets Types</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Strategie d'entrainement :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Utiliser des annales recentes</li>
                            <li>S'entrainer en temps limite</li>
                            <li>Corriger avec bareme</li>
                            <li>Noter les axes d'amelioration</li>
                            <li>Progression : 1 sujet complet par semaine</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Utiliser des annales recentes, s'entrainer en temps limite, corriger avec bareme, et noter les axes d'amelioration. Exemple de progression : 1 sujet complet par semaine.</p>
                </div>
            </div>
