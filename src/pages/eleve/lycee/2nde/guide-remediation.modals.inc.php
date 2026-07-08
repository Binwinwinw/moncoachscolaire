<?php
?>
<!-- Modals pour chaque matiere -->
        <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('francais')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-francais-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Français</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Lecture : Analyser des textes litteraires varies (roman, theatre, poesie, argumentation)</li>
                        <li>Écriture : Maitriser l'écriture d'invention, le commentaire et la dissertation</li>
                        <li>Oral : Presenter un expose structure et argumente</li>
                        <li>Langue : Renforcer la grammaire, l'orthographe et le vocabulaire</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-700">💡 Conseils de remédiation :</span> Lecture reguliere (30 minutes par jour minimum) d'œuvres variees, tenir un carnet de lecture, pratiquer l'écriture creative hebdomadaire, reviser les regles de grammaire regulierement.</p>
            </div>
        </div>
        <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-violet-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-maths-title" class="text-2xl font-bold text-violet-700 mb-4 flex items-center gap-2">Mathématiques</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-violet-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Nombres : Maitriser les intervalles, la valeur absolue, les puissances</li>
                        <li>Fonctions : Comprendre les fonctions (image, antecedent, variations, courbes)</li>
                        <li>Geometrie : Utiliser les vecteurs, les equations de droites</li>
                        <li>Probabilites : Calculer des probabilites, utiliser les arbres ponderes</li>
                        <li>Statistiques : Calculer moyenne, mediane, etendue, ecart-type</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-violet-700">💡 Conseils de remédiation :</span> Refaire les exercices corriges jusqu'a maitriser la methode, apprendre par coeur les formules importantes, faire des exercices de calcul mental quotidiennement, utiliser la calculatrice avec methode.</p>
            </div>
        </div>
        <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-histoire-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Histoire-Géo</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Histoire : Comprendre les grandes periodes (Renaissance, Lumieres, Révolutions)</li>
                        <li>Geographie : Analyser les enjeux du developpement durable et les dynamiques territoriales</li>
                        <li>Methodes : Analyser des documents, construire une argumentation, realiser un croquis</li>
                        <li>EMC : Comprendre les valeurs republicaines et la citoyennete</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-700">💡 Conseils de remédiation :</span> Créer des frises chronologiques pour memoriser les dates cles, associer des images aux notions importantes, pratiquer l'analyse de documents regulierement, suivre l'actualite pour faire des liens avec le programme.</p>
            </div>
        </div>
        <div id="modal-svt" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-svt-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('svt')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-svt-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">SVT</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Biologie : Comprendre l'organisation du vivant (cellule, organismes)</li>
                        <li>Geologie : étudier la structure de la Terre et les risques geologiques</li>
                        <li>écologie : Comprendre les ecosystemes et le developpement durable</li>
                        <li>Methodes scientifiques : observation, experimentation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-700">💡 Conseils de remédiation :</span> Apprendre a realiser des schemas clairs et legendes, maitriser le vocabulaire scientifique (créer un glossaire), observer la nature pour faire des liens avec le cours, refaire les experiences vues en classe mentalement.</p>
            </div>
        </div>
        <div id="modal-physique" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-physique-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('physique')" class="absolute top-4 right-4 text-gray-400 hover:text-fuchsia-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-physique-title" class="text-2xl font-bold text-fuchsia-700 mb-4 flex items-center gap-2">Physique-Chimie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-fuchsia-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Mecanique : Mouvement, forces, energie</li>
                        <li>électricité : Courant, tension, resistance</li>
                        <li>Chimie : Atomes, molecules, transformations chimiques</li>
                        <li>Experimentation : mesures, protocoles</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-fuchsia-700">💡 Conseils de remédiation :</span> Apprendre par coeur les formules importantes, maitriser les unites et les conversions, refaire les exercices resolus etape par etape, verifier toujours l'homogeneite des formules.</p>
            </div>
        </div>
        <div id="modal-langues" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-langues-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('langues')" class="absolute top-4 right-4 text-gray-400 hover:text-violet-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-langues-title" class="text-2xl font-bold text-violet-700 mb-4 flex items-center gap-2">Langues Vivantes</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-violet-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Comprehension orale : Comprendre des documents audio authentiques</li>
                        <li>Expression orale : Prendre part a une conversation, presenter un expose</li>
                        <li>Comprehension écrite : Lire et comprendre des textes varies</li>
                        <li>Expression écrite : Rediger des textes coherents et varies</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-violet-700">💡 Conseils de remédiation :</span> Ecouter regulierement des podcasts, series, films en version originale, pratiquer la langue quotidiennement (meme 10 minutes), tenir un journal en langue etrangere, memoriser du vocabulaire par themes (fiches).</p>
            </div>
        </div>
        <div id="modal-methodo" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-methodo-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('methodo')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-methodo-title" class="text-2xl font-bold text-purple-800 mb-4 flex items-center gap-2">Méthodologie et Organisation</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-800 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Organisation du travail : Planning hebdomadaire, fiches de revision</li>
                        <li>Methodes de memorisation : Repetition espacee, schemas mentaux</li>
                        <li>Gestion du stress : Bien dormir, faire du sport, prendre des pauses</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-800">💡 Conseils de remédiation :</span> Repartir ton temps de travail par matiere, resumer chaque cours sur une fiche, reviser chaque jour plutot que de bachoter, commencer les revisions 1 semaine avant les controles.</p>
            </div>
        </div>
        <div id="modal-orientation" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-orientation-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('orientation')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-orientation-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Orientation</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-600 mb-1">✅ Choisir tes spécialités pour la Première :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Identifie tes centres d'interet : Quelles matières te plaisent le plus ?</li>
                        <li>Évalue tes competences : Dans quelles matières réussis-tu le mieux ?</li>
                        <li>Projette-toi : Vers quels métiers ou études veux-tu t'orienter ?</li>
                        <li>Informe-toi : Consulte les fiches métiers et les programmes des specialites</li>
                        <li>Teste : Participe aux portes ouvertes et aux mini-stages</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-700">💡 Calendrier d'orientation :</span> Octobre-Novembre (decouverte), Decembre-Janvier (forum des métiers), Fevrier-Mars (intention d'orientation), Avril-Mai (confirmation), Juin (voeux definitifs).</p>
            </div>
        </div>
