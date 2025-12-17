# Bomboclaat - MovieCMS

Application de gestion de films avec API Symfony et frontend Vue.js.

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ et npm
- PostgreSQL
- Symfony CLI (recommandé)

## Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd wr506d
```

### 2. Installation du backend (Symfony)

```bash
# Installer les dépendances PHP
composer install

# Configurer la base de données
# Créer un fichier .env.local avec vos paramètres de connexion
cp .env .env.local
# Éditer .env.local et configurer DATABASE_URL
```

Exemple de `DATABASE_URL` dans `.env.local`:
```
DATABASE_URL="postgresql://user:password@127.0.0.1:5432/bomboclaat?serverVersion=16&charset=utf8"
```

### 3. Créer la base de données

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Créer les tables
php bin/console doctrine:migrations:migrate

# Charger les données de test
php bin/console doctrine:fixtures:load
```

Les fixtures créent :
- 200 films avec acteurs, réalisateurs et catégories
- 2 utilisateurs de test :
  - **admin@test.com** / admin123 (admin)
  - **user@test.com** / user123 (utilisateur)

### 4. Installation du frontend (Vue.js)

```bash
cd frontend
npm install
```

## Lancer l'application

### Backend

```bash
# À la racine du projet
symfony serve
# ou
php -S localhost:8000 -t public
```

L'API sera accessible sur `http://localhost:8000`

### Frontend

```bash
cd frontend
npm run dev
```

Le frontend sera accessible sur `http://localhost:5173`

## Tests E2E avec Cypress

Les tests couvrent toute la partie publique et les fonctionnalités utilisateurs (pas l'administration).

### Prérequis pour les tests

1. Le backend doit être lancé sur `http://localhost:8000`
2. Le frontend doit être lancé sur `http://localhost:5173`
3. La base de données doit contenir les fixtures

### Lancer les tests

**Mode interactif** (interface Cypress) :
```bash
cd frontend
npm run test:e2e
```

**Mode headless** (CI/CD) :
```bash
cd frontend
npm run test:e2e:ci
```

### Tests disponibles

- **authentication.cy.js** : Inscription, connexion, déconnexion
- **home.cy.js** : Page d'accueil et navigation
- **movies.cy.js** : Liste, recherche, détails, création de films
- **comments.cy.js** : Publication et suppression de commentaires

## Structure du projet

```
wr506d/
├── src/                    # Code source Symfony
│   ├── Controller/         # Contrôleurs API
│   ├── Entity/            # Entités Doctrine
│   ├── Repository/        # Repositories
│   └── DataFixtures/      # Données de test
├── frontend/              # Application Vue.js
│   ├── src/
│   │   ├── views/        # Pages Vue
│   │   ├── components/   # Composants réutilisables
│   │   ├── stores/       # Stores Pinia
│   │   └── services/     # Services (API)
│   └── cypress/          # Tests E2E
│       └── e2e/          # Fichiers de tests
├── config/               # Configuration Symfony
└── migrations/           # Migrations de base de données
```

## Fonctionnalités

### Public
- Consultation des films et réalisateurs
- Recherche et filtrage
- Détails des films avec commentaires

### Utilisateur connecté
- Inscription et connexion
- Publication de commentaires
- Suppression de ses propres commentaires

### Administrateur
- Création/modification/suppression de films
- Gestion des utilisateurs
- Panneau d'administration

## Technologies utilisées

### Backend
- Symfony 7.2
- API Platform
- Doctrine ORM
- PostgreSQL
- JWT Authentication

### Frontend
- Vue.js 3
- Vue Router
- Pinia (state management)
- Axios
- Tailwind CSS

### Tests
- Cypress 15.7

## API Endpoints

- `POST /auth` - Authentification
- `POST /register` - Inscription
- `GET /api/me` - Profil utilisateur
- `GET /api/movies` - Liste des films
- `GET /api/movies/{id}` - Détails d'un film
- `POST /api/movies` - Créer un film (admin)
- `GET /api/directors` - Liste des réalisateurs
- `POST /api/comments` - Publier un commentaire
- `DELETE /api/comments/{id}` - Supprimer un commentaire

## Auteur

Lucas Lebecq - 2025
