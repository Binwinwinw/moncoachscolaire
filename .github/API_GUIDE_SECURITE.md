# Guide APIs & Sécurité — MonCoachScolaire

## 1. Introduction
Ce guide présente les APIs pertinentes à implémenter pour MonCoachScolaire, leur utilité métier, et les mesures de sécurité adaptées à chaque usage (élève, parent, admin).

## 2. Catalogue des APIs utiles

### 2.1 Exercices & Quiz
- **GET /api/exercices** : Liste d’exercices filtrables (niveau, matière, difficulté)
- **GET /api/quiz** : Quiz du jour, quiz par niveau
- **POST /api/exercices/validation** : Soumission de réponses, correction
- **Utilité** : Intégration mobile, IA, partenaires, automatisation du suivi
- **Sécurité** : Authentification requise pour soumission, validation stricte des entrées, logs d’activité

### 2.2 Progression & Statistiques
- **GET /api/progression** : Progression élève, badges, historique
- **GET /api/stats** : Statistiques agrégées (admin, parent)
- **Utilité** : Dashboard, suivi parent, reporting
- **Sécurité** : ACL par rôle, anonymisation des données, rate limiting

### 2.3 Gestion de contenu pédagogique
- **POST/PUT/DELETE /api/content** : Création, édition, suppression de cours/exercices (admin)
- **Utilité** : Automatisation, synchronisation, gestion multi-admin
- **Sécurité** : Authentification forte (admin), validation des payloads, journalisation

### 2.4 Notifications & Feedback
- **POST /api/notifications** : Envoi de notifications personnalisées
- **GET /api/feedback** : Récupération des feedbacks
- **Utilité** : Engagement, suivi, automatisation
- **Sécurité** : Authentification, anti-spam, logs

### 2.5 Gestion des utilisateurs & rôles
- **POST/PUT/DELETE /api/users** : Gestion des comptes, rôles, permissions
- **Utilité** : SSO, intégration outils scolaires, gestion parent/élève/admin
- **Sécurité** : Authentification, contrôle d’accès, validation des droits

### 2.6 Paiement/Abonnement (optionnel)
- **POST /api/payment** : Gestion des abonnements, factures
- **Utilité** : Services premium
- **Sécurité** : HTTPS obligatoire, validation des transactions, logs

### 2.7 Génération de données de test
- **POST /api/populate** : Génération de données fictives pour tests/démos
- **Utilité** : QA, démo, sandbox
- **Sécurité** : Accès restreint (admin/dev), purge automatique

## 3. Risques & Menaces
- Endpoints non sécurisés
- Injection SQL/commande
- Fuite d’informations
- Brute force/abuse
- Escalade de privilèges
- XSS/CSRF
- API non chiffrée

## 4. Mesures de sécurité recommandées
- Authentification forte (JWT, sessions, OAuth2)
- Contrôle d’accès par rôle (ACL)
- Validation et filtrage des entrées/sorties
- Rate limiting et monitoring
- Logs sécurisés, erreurs génériques côté client
- Séparation stricte des rôles et permissions
- Documentation OpenAPI/Swagger
- Tests de sécurité réguliers (OWASP API Top 10)
- HTTPS obligatoire

## 5. Checklist sécurité API
- [ ] Authentification sur tous les endpoints sensibles
- [ ] Validation stricte des entrées (type, format, taille)
- [ ] Rate limiting sur endpoints critiques
- [ ] Contrôle d’accès par rôle
- [ ] Journalisation des actions sensibles
- [ ] Chiffrement des données sensibles
- [ ] Documentation et tests automatisés
- [ ] Monitoring et alertes sur abus

---
Pour toute évolution, voir aussi `.github/CONTEXT_PRODUIT.md` et `DOCUMENTATION.md`.
