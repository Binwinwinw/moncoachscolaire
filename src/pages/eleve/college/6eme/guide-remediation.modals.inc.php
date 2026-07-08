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
                            <li>Expression écrite : récit, description, dialogue</li>
                            <li>Expression orale : présentation et argumentation</li>
                            <li>Étude de la langue : grammaire et orthographe</li>
                            <li>Littérature : œuvres du patrimoine et contemporaines</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Lecture quotidienne de 20 minutes, exercices de grammaire réguliers, écriture créative hebdomadaire.</p>
                </div>
            </div>
            <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-maths-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧮</span> Mathématiques</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Nombres et calculs : opérations, fractions, décimaux</li>
                            <li>Géométrie : figures planes, solides, transformations</li>
                            <li>Grandeurs et mesures : périmètre, aire, volume</li>
                            <li>Organisation et gestion de données</li>
                            <li>Raisonnement logique et résolution de problèmes</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Exercices quotidiens de calcul mental, manipulation d'objets géométriques, jeux mathématiques.</p>
                </div>
            </div>
            <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-histoire-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🌍</span> Histoire-Géo</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Repères chronologiques et spatiaux</li>
                            <li>Lecture et analyse de documents historiques et géographiques</li>
                            <li>Compréhension des sociétés et des territoires</li>
                            <li>Expression écrite et orale sur des sujets historiques/géographiques</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Fiches de révision, cartes mentales, exposés oraux, visites virtuelles de musées.</p>
                </div>
            </div>
            <div id="modal-sciences" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-sciences-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('sciences')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-sciences-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🔬</span> Sciences</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Observation, expérimentation, démarche scientifique</li>
                            <li>Connaissances en SVT, physique, chimie</li>
                            <li>Lecture de graphiques et tableaux</li>
                            <li>Rédaction de comptes-rendus d’expériences</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Expériences à la maison, vidéos scientifiques, quiz interactifs.</p>
                </div>
            </div>
            <div id="modal-anglais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-anglais-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('anglais')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-anglais-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🇬🇧</span> Anglais</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Compréhension orale et écrite</li>
                            <li>Expression orale et écrite</li>
                            <li>Vocabulaire de base et grammaire</li>
                            <li>Interaction en situation réelle</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Séries/films en VO, applications d’apprentissage, échanges linguistiques.</p>
                </div>
            </div>
            <div id="modal-espagnol" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-espagnol-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('espagnol')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-espagnol-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🇪🇸</span> Espagnol</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Compréhension orale et écrite</li>
                            <li>Expression orale et écrite</li>
                            <li>Vocabulaire de base et grammaire</li>
                            <li>Découverte de la culture hispanique</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Chansons, vidéos, jeux de rôle, échanges avec des natifs.</p>
                </div>
            </div>
            <div id="modal-technologie" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-technologie-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('technologie')" class="absolute top-4 right-4 text-gray-400 hover:text-green-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-technologie-title" class="text-2xl font-bold text-green-800 mb-4 flex items-center gap-2"><span class="text-3xl">🧑‍💻</span> Technologie</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-800 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Compréhension des objets techniques</li>
                            <li>Initiation à la programmation</li>
                            <li>Travail en équipe sur des projets</li>
                            <li>Utilisation raisonnée du numérique</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-800">💡 Conseils de remédiation :</span> Réalisation de maquettes, projets concrets, programmation simple, manipulation d'outils numériques.</p>
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
