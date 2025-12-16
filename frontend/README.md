# MovieCMS - Frontend Vue.js

Application frontend en Vue.js pour la gestion de contenu CMS de films.

## Fonctionnalités

### Authentification
- Inscription des utilisateurs
- Connexion / Déconnexion
- Gestion des sessions avec JWT

### Partie Publique
- Liste des films avec filtres (recherche, réalisateur, tri)
- Détail d'un film
- Consultation des commentaires

### Partie Authentifiée
- Création de films (ROLE_ADMIN)
- Modification de films (ROLE_ADMIN)
- Suppression de films (ROLE_ADMIN)
- Ajout de commentaires sur les films
- Suppression de ses propres commentaires
- Upload d'images pour les posters

### Administration (ROLE_ADMIN)
- Gestion des utilisateurs
- Promotion/Rétrogradation des rôles
- Suppression d'utilisateurs
- Statistiques globales

## Technologies

- **Vue 3** avec Composition API
- **Vue Router** pour la navigation
- **Pinia** pour la gestion d'état
- **Axios** pour les appels API
- **Tailwind CSS** pour le style
- **Cypress** pour les tests E2E
- **Vite** comme build tool

## Prérequis

- Node.js >= 18
- npm ou yarn
- Backend Symfony en cours d'exécution sur `http://localhost:8000`

## Installation

```bash
# Installer les dépendances
npm install

# Démarrer le serveur de développement
npm run dev

# L'application sera accessible sur http://localhost:5173
```

## Configuration

### API Backend

L'URL de l'API backend est configurée dans `src/services/api.js` :

```javascript
baseURL: 'http://localhost:8000'
```

Modifiez cette URL si votre backend tourne sur un autre port.

## Tests

### Tests E2E avec Cypress

```bash
# Ouvrir l'interface Cypress
npm run test:e2e

# Exécuter les tests en mode headless (CI)
npm run test:e2e:ci
```

Les tests couvrent :
- Navigation sur la page d'accueil
- Inscription et connexion
- Consultation des films
- Création et gestion de commentaires (utilisateurs authentifiés)
- Filtres et recherche

**Comptes de test disponibles :**
- Administrateur : `admin@test.com` / `admin123`
- Utilisateur normal : `user@test.com` / `user123`

Ces comptes ont été créés et sont prêts à être utilisés pour les tests.

## Structure du Projet

```
frontend/
├── src/
│   ├── assets/          # Ressources statiques
│   ├── components/      # Composants réutilisables
│   ├── router/          # Configuration Vue Router
│   ├── services/        # Services API (Axios)
│   ├── stores/          # Stores Pinia
│   ├── views/           # Pages/Vues
│   │   ├── auth/        # Pages d'authentification
│   │   ├── movies/      # Pages de gestion des films
│   │   └── admin/       # Pages d'administration
│   ├── App.vue          # Composant racine
│   └── main.js          # Point d'entrée
├── cypress/
│   ├── e2e/             # Tests E2E
│   └── support/         # Commandes et utilitaires Cypress
├── public/              # Fichiers publics
└── index.html           # Template HTML

```

## Gestion des Rôles

### ROLE_USER (Par défaut)
- Consulter les films
- Poster des commentaires
- Supprimer ses propres commentaires

### ROLE_ADMIN
- Toutes les permissions ROLE_USER
- Créer/Modifier/Supprimer des films
- Accéder à la page d'administration
- Gérer les utilisateurs
- Supprimer tous les commentaires

## Build pour la Production

```bash
# Créer le build de production
npm run build

# Le build sera dans le dossier dist/

# Prévisualiser le build
npm run preview
```

## Déploiement

Le dossier `dist/` peut être déployé sur n'importe quel hébergeur statique :
- Netlify
- Vercel
- GitHub Pages
- Serveur Apache/Nginx

**Important:** Configurez votre serveur pour rediriger toutes les requêtes vers `index.html` pour que le routing Vue fonctionne correctement.

## Points Importants

1. **CORS**: Assurez-vous que le backend Symfony autorise les requêtes depuis l'origine du frontend
2. **JWT**: Les tokens JWT sont stockés dans le localStorage
3. **API Platform**: L'application utilise le format Hydra d'API Platform (`hydra:member`, etc.)
4. **Upload de fichiers**: Les images sont uploadées via l'endpoint `/api/media_objects`

## Problèmes Connus

- Les tests Cypress nécessitent des utilisateurs de test dans la base de données
- L'upload d'images nécessite que VichUploader soit correctement configuré dans le backend

## Développement

### Ajouter une nouvelle page

1. Créer la vue dans `src/views/`
2. Ajouter la route dans `src/router/index.js`
3. Ajouter le lien dans la navigation (`App.vue`)

### Ajouter un nouveau store

1. Créer le store dans `src/stores/`
2. Utiliser `defineStore` avec la Composition API
3. Importer et utiliser avec `useXxxStore()`

### Personnaliser le style

Tailwind CSS est configuré. Vous pouvez :
- Modifier les couleurs dans `tailwind.config.js`
- Ajouter des classes personnalisées dans `src/style.css`

## Auteur

Projet réalisé dans le cadre du cours WR506

## Licence

Projet académique
