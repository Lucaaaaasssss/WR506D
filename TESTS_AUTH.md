# Guide de test de l'authentification JWT

## 🎯 Résumé

Votre authentification JWT fonctionne correctement ! Vous pouvez tester avec :
- **Script bash** : `./test_auth.sh`
- **curl** (exemples ci-dessous)
- **Postman/Insomnia** (voir configuration ci-dessous)

## 👤 Utilisateur de test

- **Email** : `test@example.com`
- **Mot de passe** : `MYPASSWORD`

## 📝 Tests avec curl

### 1. Obtenir un token JWT

```bash
curl -X POST http://localhost:8000/auth \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"MYPASSWORD"}'
```

**Réponse attendue** :
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### 2. Lister les films avec le token

```bash
# Remplacez VOTRE_TOKEN par le token reçu
curl -X GET http://localhost:8000/api/movies \
  -H "Accept: application/ld+json" \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

### 3. Créer un film avec le token

```bash
curl -X POST http://localhost:8000/api/movies \
  -H "Content-Type: application/ld+json" \
  -H "Accept: application/ld+json" \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -d '{
    "name": "Mon nouveau film",
    "description": "Description du film",
    "duration": 120,
    "budget": 1000000,
    "draft": false,
    "online": true,
    "director": "/api/directors/1"
  }'
```

### 4. Obtenir un film spécifique

```bash
curl -X GET http://localhost:8000/api/movies/1758 \
  -H "Accept: application/ld+json" \
  -H "Authorization: Bearer VOTRE_TOKEN"
```

## 🚀 Test avec Postman

### Configuration de l'authentification

1. **Créer une requête "Login"**
   - Méthode : `POST`
   - URL : `http://localhost:8000/auth`
   - Headers :
     - `Content-Type: application/json`
   - Body (raw JSON) :
     ```json
     {
       "email": "test@example.com",
       "password": "MYPASSWORD"
     }
     ```
   - Dans l'onglet "Tests", ajoutez :
     ```javascript
     var jsonData = pm.response.json();
     pm.environment.set("jwt_token", jsonData.token);
     ```

2. **Utiliser le token dans les autres requêtes**
   - Dans l'onglet "Authorization"
   - Type : `Bearer Token`
   - Token : `{{jwt_token}}`

### Exemples de requêtes Postman

#### GET - Liste des films
- Méthode : `GET`
- URL : `http://localhost:8000/api/movies`
- Headers :
  - `Accept: application/ld+json`
  - `Authorization: Bearer {{jwt_token}}`

#### POST - Créer un film
- Méthode : `POST`
- URL : `http://localhost:8000/api/movies`
- Headers :
  - `Content-Type: application/ld+json`
  - `Accept: application/ld+json`
  - `Authorization: Bearer {{jwt_token}}`
- Body (raw JSON) :
  ```json
  {
    "name": "Film test Postman",
    "description": "Description",
    "duration": 120,
    "budget": 1000000,
    "draft": false,
    "online": true,
    "director": "/api/directors/1"
  }
  ```

## 🔒 Sécuriser l'API (optionnel)

Actuellement, l'API est accessible sans token. Pour la sécuriser :

1. Modifiez `config/packages/security.yaml` :

```yaml
access_control:
    - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }
```

2. Redémarrez le serveur

3. Testez l'accès sans token (devrait retourner 401 Unauthorized)

## 🔑 Créer un nouvel utilisateur

```bash
# 1. Générer le hash du mot de passe
php bin/console security:hash-password VotreMotDePasse

# 2. Insérer l'utilisateur en base
php bin/console dbal:run-sql "INSERT INTO \"user\" (email, roles, password) VALUES ('nouvel.user@example.com', '[]', 'HASH_GENERE_ICI')"
```

## 🧪 Script de test automatique

Exécutez le script de test complet :

```bash
./test_auth.sh
```

Ce script teste :
- ✅ Obtention du token
- ✅ Accès à l'API avec token
- ✅ Création d'une ressource
- ✅ Accès sans token (pour vérifier la sécurité)

## 📊 Décodage du token JWT

Vous pouvez décoder votre token sur [jwt.io](https://jwt.io) pour voir son contenu :

```json
{
  "iat": 1764665015,
  "exp": 1764668615,
  "roles": ["ROLE_USER"],
  "username": "test@example.com"
}
```

- `iat` : Date de création (timestamp)
- `exp` : Date d'expiration (timestamp) - 1 heure après création
- `roles` : Rôles de l'utilisateur
- `username` : Email de l'utilisateur

## ❓ Résolution de problèmes

### Erreur 401 Unauthorized
- Vérifiez que le token est valide
- Vérifiez que le token n'a pas expiré (1 heure)
- Vérifiez le format : `Authorization: Bearer VOTRE_TOKEN`

### Erreur 422 Unprocessable Entity
- Vérifiez les validations des champs
- Duration doit être entre 30 et 400 minutes
- Director est obligatoire

### Le serveur ne répond pas
```bash
# Vérifier que le serveur est démarré
ps aux | grep "php -S"

# Redémarrer le serveur si nécessaire
pkill -f "php -S" && php -S localhost:8000 -t public &
```
