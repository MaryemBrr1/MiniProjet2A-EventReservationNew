# MiniProjet2A-EventReservation

Application web complète de gestion de réservations d'événements développée avec Symfony 7, JWT et Passkeys.

---

## Description

Cette application permet :
- Aux **utilisateurs** de consulter des événements, de créer un compte, de se connecter (mot de passe ou Passkey biométrique) et de réserver des places
- Aux **administrateurs** de gérer les événements (CRUD complet) et de consulter les réservations

---

## Technologies utilisées

| Technologie | Version | Rôle |
|---|---|---|
| PHP | 8.2 | Langage backend |
| Symfony | 7.x | Framework PHP |
| Doctrine ORM | 3.x | Gestion base de données |
| MySQL | 8.0 | Base de données |
| LexikJWT | 3.x | Authentification JWT |
| Gesdinet Refresh Token | 2.x | Refresh tokens |
| WebAuthn / Passkeys | — | Authentification biométrique |
| Nelmio CORS | — | Configuration CORS API |
| Tailwind CSS (CDN) | 3.x | Style et design |
| Docker | — | Conteneurisation |
| GitHub | — | Versioning et collaboration |

---

## Membres de l'équipe

| Membre | Rôle |
|---|---|
| [Nom Personne 1] | Entités, CRUD Admin, Docker |
| [Ton Nom] | Auth JWT/Passkeys, Pages utilisateur, Design |

---

## Prérequis

- PHP 8.2+
- Composer 2.x
- Symfony CLI
- MySQL 8.0+
- Git
- Docker Desktop (optionnel)

---

## Installation et démarrage

### 1. Cloner le projet
```bash
git clone https://github.com/TON_USERNAME/MiniProjet2A-EventReservation-NomEquipe.git
cd MiniProjet2A-EventReservation-NomEquipe
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configurer l'environnement
```bash
cp .env .env.local
```

Modifier `.env.local` :
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/event_reservation"
JWT_PASSPHRASE=votre_passphrase
APP_DOMAIN=localhost
WEBAUTHN_RP_NAME="EventApp ISSAT"
```

### 4. Générer les clés JWT
```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

### 5. Créer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 6. Lancer l'application
```bash
symfony serve
```

Accéder à : **http://localhost:8000**

---

## Comptes de test

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | admin@example.com | admin123 |
| Utilisateur | eya@example.com | eya123 |
| Utilisateur | sara@example.com | sara123 |

---

## Structure des branches
```
main          ← code stable et fonctionnel
dev           ← intégration et tests
├── feature/entities-bdd         (Personne 1)
├── feature/crud-admin           (Personne 1)
├── feature/auth-jwt-passkeys    (Personne 2)
└── feature/reservation-user     (Personne 2)
```

---

## Fonctionnalités

### Côté utilisateur
- ✅ Inscription et connexion (email/mot de passe)
- ✅ Connexion biométrique via Passkeys (WebAuthn)
- ✅ Liste des événements disponibles
- ✅ Détail d'un événement (description, date, lieu, places)
- ✅ Formulaire de réservation (nom, email, téléphone)
- ✅ Vérification unicité email par événement
- ✅ Page de confirmation de réservation

### Côté administrateur
- ✅ Authentification sécurisée
- ✅ Tableau de bord avec statistiques
- ✅ CRUD complet sur les événements
- ✅ Consultation des réservations par événement
- ✅ Déconnexion sécurisée

### Sécurité
- ✅ JWT (JSON Web Tokens) avec LexikJWTBundle
- ✅ Refresh tokens avec GesdinetBundle
- ✅ Passkeys / WebAuthn (FIDO2)
- ✅ Protection CSRF sur tous les formulaires
- ✅ Contrôle d'accès par rôles (ROLE_USER, ROLE_ADMIN)

---

## Docker
```bash
# Construire et lancer
docker-compose up -d

# Arrêter
docker-compose down
```

---

## Liens utiles

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [LexikJWT Bundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
- [WebAuthn Guide](https://webauthn.guide/)
- [FIDO Alliance Passkeys](https://fidoalliance.org/passkeys/)
## Notes de développement
- Authentification sécurisée via JWT + Passkeys (WebAuthn/FIDO2)
- Protection CSRF sur tous les formulaires
- Validation unicité email par événement