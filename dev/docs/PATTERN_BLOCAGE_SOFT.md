# Pattern « Blocage d’accès soft » (aperçu + encouragement à s’inscrire)

## Objectif
Permettre à un visiteur ou utilisateur non connecté d’accéder à la page, d’en voir un aperçu (structure, avantages, extraits anonymes), sans divulguer d’informations personnalisées, tout en l’encourageant à créer un compte ou se connecter.

## Fonctionnement
- **Aperçu visible** : l’utilisateur voit la structure, quelques exemples ou fonctionnalités, mais pas le contenu complet ni les données personnelles.
- **Message positif** : toujours bienveillant, jamais frustrant. Expliquer ce que l’utilisateur gagnera en s’inscrivant.
- **Appels à l’action** : boutons « Créer mon compte gratuit », « Me connecter » bien visibles.
- **Jamais de blocage dur** : la page reste accessible, seul le contenu premium ou personnalisé est masqué.
- **Respect de l’accessibilité** : le message doit être compréhensible, lisible, et accessible à tous.

## Exemple de code PHP/HTML
```php
<?php if (empty($is_logged_in)): ?>
  <div class="bg-gradient-to-r from-yellow-50 to-red-50 rounded-xl p-8 mb-8 border border-yellow-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-3">
      <span class="text-3xl">🔒</span>
      Débloque l’accès complet à la préparation orale
    </h2>
    <p class="text-gray-700 mb-6">
      Tu peux voir ci-dessous un aperçu de nos ressources pour la préparation aux épreuves orales, mais pour accéder à l’accompagnement personnalisé avec ton coach, tu dois créer un compte gratuit !
    </p>
    <ul class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <li>👨‍🏫 Coach Personnel : Accompagnement personnalisé pour ta préparation orale avec conseils adaptés</li>
      <li>🎤 Simulations Guidées : Entraîne-toi avec des simulations d’oraux et reçois des retours constructifs</li>
      <li>📊 Suivi de ta Progression : Dashboard pour suivre ta progression et tes points d’amélioration</li>
      <li>🎯 Ressources Exclusives : Accès à des annales, des stratégies éprouvées et des techniques de maîtres</li>
    </ul>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="<?php echo site_url('register'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition-colors shadow-lg">
        <span class="mr-2">✨</span>Créer mon compte gratuit
      </a>
      <a href="<?php echo site_url('login'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors shadow-lg">
        <span class="mr-2">🔑</span>Me connecter
      </a>
    </div>
  </div>
<?php endif; ?>
```

## Où trouver des exemples
- [src/pages/eleve/lycee/terminale/exercices-terminale.php](../src/pages/eleve/lycee/terminale/exercices-terminale.php)
- [src/pages/system/cours.php](../src/pages/system/cours.php)

## Bonnes pratiques
- Ne jamais afficher de message négatif ou frustrant.
- Toujours proposer une alternative (inscription, connexion).
- Ne jamais bloquer l’accès à la page elle-même.
- Adapter le wording à la cible (élève, parent, etc.).
- Respecter les hooks front et l’accessibilité.

---

*Documenté le 13/02/2026 — à généraliser sur toutes les pages publiques/freemium.*
