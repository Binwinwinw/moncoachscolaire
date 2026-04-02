<!-- Coach WebM - Déclencheurs pour page Exercices/Quiz -->
<!-- 
  Incluez ce bloc dans vos pages d'exercices et quiz pour intégrer les déclencheurs du coach.
  Les événements suivants sont disponibles :
  - onCorrectAnswer()      : Appelé quand la réponse est correcte (3x = affiche "bravo")
  - onIncorrectAnswer()    : Appelé après une mauvaise réponse (affiche encouragement)
  - onExerciseComplete(score) : Appelé à la fin d'un exercice avec le score (affiche selon score)
  - onLevelOrBadgeComplete() : Appelé quand un niveau/badge est complété
-->

<script>
  /**
   * Exemple d'intégration sur page exercices
   * À adapter selon votre structure HTML et événements
   */

  // 1. RÉPONSE CORRECTE
  // À appeler chaque fois qu'une réponse est validée comme correcte
  function validateAnswer(isCorrect) {
    if (isCorrect) {
      window.onCorrectAnswer();
      // ... votre logique de validation
    } else {
      window.onIncorrectAnswer();
      // ... votre logique de non-validation
    }
  }

  // 2. EXERCICE COMPLÉTÉ
  // À appeler à la fin de l'exercice avec le score (0-100)
  function submitExercise(score) {
    // Envoyer le score au serveur, afficher résultat, etc.
    // ...
    
    // Déclencher le coach selon le score
    window.onExerciseComplete(score);
    
    // Afficher le résultat final à l'utilisateur
    // ...
  }

  // 3. NIVEAU OU BADGE COMPLÉTÉ
  // À appeler quand l'utilisateur déverrouille quelque chose
  function onAchievementUnlocked(type) { // type: 'level' ou 'badge'
    window.onLevelOrBadgeComplete();
    // Afficher la notification d'achievement, etc.
  }

  // 4. ACTIONS RAPIDES
  // Vous pouvez aussi déclencher manuellement n'importe quand :
  // window.showCoach('bonjour', 5000);  // Affiche bonjour 5 sec
  // window.showCoach('champion', 6000); // Affiche champion 6 sec
  // window.hideCoach();                  // Masque immédiatement
</script>

<!-- 
  SCÉNARIOS D'UTILISATION RÉELS (basés sur les vidéos disponibles)
  
  Situation                    Animation                  Quand appeler
  ========================================================================================
  Première connexion du jour   👋 bonjour               window.showDailyGreeting() (au chargement du dashboard)
  Après une mauvaise réponse   💬 encouragement         window.onIncorrectAnswer()
  3 bonnes réponses consécutives 👍 bravo               window.onCorrectAnswer() x3
  Score d'exercice 50-70%      💪 bravo/tresbien        window.onExerciseComplete(score)
  Score d'exercice 70-90%      ⭐ tresbien              window.onExerciseComplete(score)
  Score d'exercice >90%        🎊 champion (fête)       window.onExerciseComplete(score)
  Niveau/Badge complété        🎉 fête (champion)       window.onLevelOrBadgeComplete()
  
  VIDÉOS DISPONIBLES (dans assets/img/coach/humain/)
  ========================================================================================
  Fichier                              Usage
  ========================================================================================
  bonjour_bienvenue.webm              Salutation première visite du jour
  toituvasyarriver.webm               Encouragements génériques (erreur, encouragement v1 & v2)
  bravo.webm                           3 bonnes réponses, résultat décent
  tresbien.webm                        Score 70-90% (très bon)
  championdumonde.webm                Score >90%, niveau complété, fête, badges
  posture_positive_accueillante.webm  Neutre/accueil par défaut
-->
