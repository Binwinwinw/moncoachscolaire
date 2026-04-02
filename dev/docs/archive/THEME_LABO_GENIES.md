# Thème : Le Labo des Génies

## Changement de Thème

Le système de gamification a été renommé de **"L'Odyssée du Savoir"** à **"Le Labo des Génies"** pour mieux refléter l'approche scientifique et expérimentale de l'apprentissage.

## Vocabulaire Adapté

### Récompenses

| Ancien Terme | Nouveau Terme | Description |
|--------------|---------------|-------------|
| **Cristaux** 💎 | **Éléments** ⚗️ | Éléments scientifiques collectés lors des expériences |
| **XP** | **Points d'Expérience** | Points d'expérience scientifique accumulés |
| **Pouvoirs** | **Outils de Labo** | Outils et équipements de laboratoire débloqués |

### Niveaux Scientifiques

| Niveau | Ancien Nom | Nouveau Nom | Description |
|--------|------------|-------------|-------------|
| 1 | Aventurier | **Apprenti Scientifique** | Début de l'aventure scientifique |
| 2 | Explorateur | **Chercheur Junior** | Premiers pas dans la recherche |
| 3 | Ingénieur | **Expérimentateur** | Maîtrise des expériences de base |
| 4 | Expert | **Docteur en Sciences** | Expertise scientifique reconnue |
| 5 | Maître | **Professeur** | Enseignement et transmission |
| 6 | Grand Maître | **Maître de Laboratoire** | Direction d'un laboratoire |
| 7 | Légende | **Directeur de Recherche** | Recherche avancée et innovation |
| 8 | Mythe | **Génie Scientifique** | Maîtrise exceptionnelle |
| 9 | Épopée | **Légende du Labo** | Reconnaissance légendaire |
| 10 | Immortel | **Génie Immortel** | Maîtrise ultime et éternelle |

### Métaphores Scientifiques

- **Plateau de jeu** → **Laboratoire virtuel**
- **Cases** → **Stations d'expérimentation**
- **Avancement** → **Progression dans les expériences**
- **Quête** → **Expérience scientifique**
- **Pouvoirs** → **Outils de laboratoire**

## Design et Icônes

### Icônes Principales

- 🔬 **Le Labo des Génies** (titre principal)
- ⚗️ **Éléments scientifiques** (remplace 💎 cristaux)
- 🧪 **Expériences** (activités)
- 📊 **Données** (statistiques)
- 🎓 **Niveaux scientifiques** (progression)
- 🔬 **Outils de labo** (pouvoirs/équipements)

### Palette de Couleurs

- **Bleu scientifique** : #3b82f6 (expériences, découvertes)
- **Vert laboratoire** : #10b981 (succès, réactions réussies)
- **Violet innovation** : #8b5cf6 (innovations, découvertes)
- **Orange réaction** : #f59e0b (activité, énergie)

## Messages et Textes

### Messages de Progression

- Ancien : "Ta quête épique pour maîtriser les connaissances"
- Nouveau : "Transforme-toi en scientifique et découvre les secrets de la connaissance"

### Messages de Récompense

- Ancien : "Cristaux collectés"
- Nouveau : "Éléments collectés"

- Ancien : "Pouvoirs débloqués"
- Nouveau : "Outils de labo débloqués"

### Messages d'Encouragement

- "Continue tes expériences pour devenir un génie scientifique !"
- "Chaque exercice complété te fait progresser dans le laboratoire"
- "Débloque de nouveaux outils pour tes recherches !"

## Compatibilité

Pour maintenir la compatibilité avec le code existant, certaines variables conservent leur nom d'origine :
- `$cristaux` reste utilisé en interne (peut être renommé progressivement)
- Les fonctions existantes continuent de fonctionner
- L'affichage utilise le nouveau vocabulaire

## Évolutions Futures

### Outils de Laboratoire (Anciens Pouvoirs)

Les "pouvoirs" deviendront des "outils de laboratoire" :

| Ancien Pouvoir | Nouvel Outil | Description |
|----------------|--------------|-------------|
| Bouclier Mathématique 🛡️ | Calculatrice Avancée 🧮 | Aide aux calculs complexes |
| Bibliothèque Instantanée 📚 | Encyclopédie Scientifique 📖 | Accès aux définitions |
| Masque de Concentration 🎭 | Zone de Focus 🔬 | Environnement sans distraction |
| Éclair de Compréhension ⚡ | Accélérateur d'Apprentissage ⚡ | Apprentissage accéléré |
| Cristal de Prévision 🔮 | Prédicteur Scientifique 🔮 | Anticipation des difficultés |

### Stations d'Expérimentation

Les cases du plateau deviendront des "stations d'expérimentation" :
- Station 1 : Initiation aux bases
- Station 8 : Outil de calcul débloqué
- Station 12 : Encyclopédie disponible
- Station 16 : Zone de focus activée
- Station 20 : Accélérateur débloqué
- Station 24 : Prédicteur disponible

## Migration

### Fichiers Modifiés

- ✅ `progression.php` : Titre et messages mis à jour
- ✅ `dashboard.php` : Labels mis à jour
- ✅ `pages/demo.php` : Aperçu mis à jour
- ✅ `includes/progress_helpers.php` : Noms de niveaux mis à jour
- ✅ `assets/css/pages/progression.css` : Commentaires mis à jour
- ✅ `includes/gamification.php` : Commentaires mis à jour

### Fichiers à Migrer Progressivement

- `DOCUMENTATION.md` : Mise à jour des références
- Messages dans les exercices interactifs
- Textes d'aide et tooltips
- Emails et notifications

---

**Date de migration** : Décembre 2024
**Thème actif** : Le Labo des Génies 🔬

