# MiniProjet2A — Application Web de Gestion de Réservations d'Événements

<div align="center">

![Symfony](https://img.shields.io/badge/Symfony-7.4-black?style=for-the-badge&logo=symfony)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)
![JWT](https://img.shields.io/badge/JWT-Auth-000000?style=for-the-badge&logo=jsonwebtokens)
![WebAuthn](https://img.shields.io/badge/Passkeys-WebAuthn-blue?style=for-the-badge)
![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=for-the-badge&logo=docker)

**Application web complète de gestion et réservation d'événements**  
Développée avec Symfony 7, JWT, Passkeys (WebAuthn) et Docker

</div>

---

## Informations du projet

| Champ | Valeur |
|-------|--------|
| **Étudiant** | Eya Bouallegue |
| **Encadrant** | M. Sofiene Ben Ahmed |
| **Filière** | FIA2-GL |
| **Institution** | ISSAT Sousse |
| **Année universitaire** | 2025-2026 |
| **Dépôt GitHub** | [MiniProjet2A-EventReservation-eyablg](https://github.com/Eyablg-ops/MiniProjet2A-EventReservation-eyablg) |

---

## Description

Cette application web permet :

- **Côté utilisateur** : consulter les événements disponibles, voir leur détail (description, date, lieu, image, places) et effectuer une réservation en ligne avec confirmation.
- **Côté administrateur** : gérer les événements via un tableau de bord sécurisé (CRUD complet) et consulter les réservations par événement.
- **Sécurité renforcée** : authentification par JWT (tokens d'accès) et Passkeys WebAuthn (biométrie Touch ID / Face ID).

---

## Technologies utilisées

| Technologie | Version | Rôle |
|-------------|---------|------|
| PHP | 8.2 | Langage principal |
| Symfony | 7.4 | Framework MVC |
| Doctrine ORM | 3.x | Gestion base de données |
| MySQL | 8.0 | Base de données relationnelle |
| LexikJWTBundle | 3.x | Authentification par tokens JWT |
| GesdinetJWTRefreshToken | 1.5 | Refresh tokens |
| web-auth/webauthn-lib | 4.9 | Implémentation WebAuthn (Passkeys) |
| Twig | 3.x | Moteur de templates HTML |
| Tailwind CSS | CDN | Framework CSS utilitaire |
| Docker | Latest | Conteneurisation |
| Nginx | Alpine | Serveur web |
| GitHub | — | Versionnage et collaboration |

---

## Fonctionnalités réalisées

### Côté utilisateur
- [x] Inscription avec email + mot de passe
- [x] Connexion par formulaire (login/password)
- [x] Connexion par Passkey (WebAuthn / biométrie)
- [x] Enregistrement d'une Passkey (Touch ID / Face ID)
- [x] Affichage de la liste des événements
- [x] Consultation du détail d'un événement (description, date, lieu, image, places)
- [x] Formulaire de réservation (nom, email, téléphone)
- [x] Enregistrement des réservations en base de données
- [x] Message de confirmation après réservation
- [x] Déconnexion sécurisée

### Côté administrateur
- [x] Connexion sécurisée (ROLE_ADMIN)
- [x] Tableau de bord listant tous les événements
- [x] Créer un événement (titre, description, date, lieu, places, image)
- [x] Modifier un événement existant
- [x] Supprimer un événement (avec protection CSRF)
- [x] Consulter les réservations par événement
- [x] Déconnexion sécurisée

### Sécurité & Infrastructure
- [x] JWT (JSON Web Tokens) pour l'API REST
- [x] Refresh tokens (renouvellement automatique)
- [x] Passkeys WebAuthn (cryptographie asymétrique)
- [x] Hachage des mots de passe (bcrypt auto)
- [x] Protection CSRF sur les formulaires
- [x] Configuration Docker (PHP-FPM + Nginx + MySQL)
- [x] Versionnage Git avec branches main/dev

---

## Architecture du projet

```
MiniProjet2A-EventReservation-eyablg/
├── config/
│   ├── jwt/                    # Clés RSA (private.pem, public.pem)
│   └── packages/               # Configuration des bundles
│       ├── security.yaml       # Firewalls, access_control
│       ├── lexik_jwt_authentication.yaml
│       ├── gesdinet_jwt_refresh_token.yaml
│       └── nelmio_cors.yaml
├── src/
│   ├── Controller/
│   │   ├── AuthApiController.php      # API JWT + Passkeys
│   │   ├── EventController.php        # Liste et détail événements
│   │   ├── ReservationController.php  # Réservations
│   │   ├── AdminController.php        # Dashboard admin CRUD
│   │   ├── SecurityController.php     # Login/logout
│   │   ├── RegistrationController.php # Inscription
│   │   └── HomeController.php
│   ├── Entity/
│   │   ├── User.php
│   │   ├── Event.php
│   │   ├── Reservation.php
│   │   └── WebauthnCredential.php
│   ├── Form/
│   │   ├── EventType.php
│   │   └── ReservationType.php
│   ├── Repository/
│   ├── Service/
│   │   └── PasskeyAuthService.php
│   ├── DataFixtures/
│   │   └── AppFixtures.php
│   └── Command/
│       └── CreateAdminCommand.php
├── templates/
│   ├── base.html.twig
│   ├── events/
│   ├── reservations/
│   ├── admin/
│   ├── security/
│   └── registration/
├── docker-compose.yml
├── Dockerfile
└── .env
```

---

## Installation et lancement

### Prérequis
- PHP 8.2+
- Composer 2.x
- MySQL 8.0 (ou MAMP sur Mac)
- Symfony CLI
- Node.js 18+ (optionnel, pour assets)
- Docker (optionnel)

### Installation locale (sans Docker)

```bash
# 1. Cloner le dépôt
git clone https://github.com/Eyablg-ops/MiniProjet2A-EventReservation-eyablg.git
cd MiniProjet2A-EventReservation-eyablg

# 2. Installer les dépendances PHP
composer install

# 3. Créer le fichier de configuration locale
cp .env .env.local
# Modifier .env.local avec vos identifiants MySQL :
# DATABASE_URL="mysql://root:root@127.0.0.1:8889/Evt_reservation?serverVersion=8.0&charset=utf8mb4"
# JWT_PASSPHRASE=votre_passphrase

# 4. Générer les clés JWT
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

# 5. Créer la base de données et appliquer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 6. Charger les données de test (fixtures)
php bin/console doctrine:fixtures:load

# 7. Créer le compte administrateur
php bin/console app:create-admin

# 8. Lancer le serveur de développement
symfony serve -d

# 9. Ouvrir dans le navigateur
# http://127.0.0.1:8000/events
```

### Installation avec Docker

```bash
# Construire et démarrer les conteneurs
docker-compose up -d --build

# Appliquer les migrations
docker-compose exec php php bin/console doctrine:migrations:migrate

# Charger les fixtures
docker-compose exec php php bin/console doctrine:fixtures:load

# Créer l'admin
docker-compose exec php php bin/console app:create-admin

# Accéder à l'application : http://localhost:8080
```

---

## Comptes de test

| Rôle | Email | Mot de passe |
|------|-------|-------------|
| Administrateur | admin@example.com | admin123 |
| Utilisateur | user@example.com | user123 |

---

## Routes principales

| Route | Méthode | Accès | Description |
|-------|---------|-------|-------------|
| `/` | GET | Public | Redirection vers /events |
| `/events` | GET | Public | Liste des événements |
| `/events/{id}` | GET | Public | Détail d'un événement |
| `/reservations/new/{id}` | GET/POST | ROLE_USER | Formulaire de réservation |
| `/login` | GET/POST | Public | Page de connexion |
| `/logout` | GET | Connecté | Déconnexion |
| `/register` | GET/POST | Public | Inscription |
| `/admin` | GET | ROLE_ADMIN | Dashboard admin |
| `/admin/event/new` | GET/POST | ROLE_ADMIN | Créer un événement |
| `/admin/event/{id}/edit` | GET/POST | ROLE_ADMIN | Modifier un événement |
| `/admin/event/{id}/delete` | POST | ROLE_ADMIN | Supprimer un événement |
| `/api/auth/passkey/register/options` | POST | Public | Options WebAuthn (enregistrement) |
| `/api/auth/passkey/register/verify` | POST | Public | Vérification Passkey |
| `/api/auth/passkey/login/options` | POST | Public | Options WebAuthn (connexion) |
| `/api/auth/passkey/login/verify` | POST | Public | Authentification Passkey → JWT |
| `/api/auth/me` | GET | JWT | Profil utilisateur connecté |
| `/api/token/refresh` | POST | Public | Renouveler le JWT |

---

## Branches Git

| Branche | Description |
|---------|-------------|
| `main` | Code stable et fonctionnel |
| `dev` | Intégration et tests |

---

## Structure de la base de données

| Table | Colonnes principales |
|-------|---------------------|
| `user` | id (UUID), email, roles, password, username |
| `event` | id, title, description, date, location, seats, image |
| `reservation` | id, event_id, user_id, name, email, phone, created_at |
| `webauthn_credential` | id, user_id, credentialData, name, createdAt, lastUsedAt |

---

## Auteur

**Eya Bouallegue**  
Étudiante FIA2-GL — ISSAT Sousse  
GitHub : [@Eyablg-ops](https://github.com/Eyablg-ops)

---

*Projet réalisé dans le cadre du module de développement web avancé — ISSAT Sousse 2025-2026*
