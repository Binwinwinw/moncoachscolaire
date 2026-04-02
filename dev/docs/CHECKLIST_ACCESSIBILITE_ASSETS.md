# Checklist Accessibilité Front-End

- [ ] Contraste texte/fond conforme WCAG AA (minimum 4.5:1)
- [ ] Navigation clavier (tabulation logique, focus visible)
- [ ] Présence d’attributs ARIA pertinents (aria-label, aria-live, etc.)
- [ ] Titres de page et balises <h1> cohérents
- [ ] Liens explicites (texte de lien compréhensible hors contexte)
- [ ] Images décoratives avec alt="" ; images informatives avec alt descriptif
- [ ] Formulaires accessibles (labels associés, erreurs lisibles, navigation clavier)
- [ ] Composants dynamiques (modals, menus, etc.) accessibles au clavier et ARIA
- [ ] Avertissements de session ou d’action (ex : timeout) annoncés via aria-live
- [ ] Pas de piège clavier (on peut toujours sortir d’un composant)
- [ ] Testé avec un lecteur d’écran (NVDA, VoiceOver, etc.)

# Checklist Optimisation des Assets

- [ ] Images en formats modernes (AVIF, WEBP, SVG pour illustrations)
- [ ] Compression d’images (TinyPNG, Squoosh, etc.)
- [ ] Lazy loading des images et vidéos (loading="lazy")
- [ ] Utilisation de <picture> pour le responsive
- [ ] Vidéos en MP4/WEBM optimisées (résolution adaptée, bitrate réduit)
- [ ] Pas d’images non utilisées dans le repo
- [ ] Fichiers CSS/JS minifiés en production
- [ ] Cache-control/headers adaptés pour les assets statiques
- [ ] Vérification du poids total de la page (<2 Mo recommandé)
- [ ] Audit Lighthouse > 90 sur Performance et Accessibilité

> À intégrer dans la documentation technique ou à utiliser lors de chaque sprint de QA.
