# Validation de cohérence des exercices

Outil: `tools/validate_exercises_coherence.php`
- Vérifie :
  - Réponse non vide
  - Similarité Jaccard question/réponse (mots-clés) >= 0.08
  - Si question contient des nombres ou une demande de quantité, la réponse doit contenir au moins un nombre (hors Vrai/Faux)
- Affiche la liste des exercices suspects.

Commandes utiles:
```powershell
php d:\Hostinger\public_html\moncoachscolaire\tools\validate_exercises_coherence.php
php d:\Hostinger\public_html\moncoachscolaire\tools\check_empty_answers.php
```

## Suspects actuels à corriger
(voir sortie du validateur)
- 5ème : Anglais #368, Français #354/#358/#359, Maths #344/#348/#349/#350/#351
- 6ème : Français #296/#299/#308
- 4ème : Histoire-Géo #98/#100
- Terminale : Anglais #152, Histoire-Géo #146/#147, Maths #132, Physique-Chimie #150, SVT #136

## Plan de correction
- Pour chaque exercice suspect: enrichir ou ajuster la réponse pour qu’elle réponde explicitement aux questions (quantités/nombres si demandés, vocabulaire partagé).
- Relancer le validateur puis, si besoin, réimporter via les scripts d’import Markdown ou spécifiques.
