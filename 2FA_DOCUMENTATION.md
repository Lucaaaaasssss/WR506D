# Documentation de l'authentification à deux facteurs (2FA)

## Vue d'ensemble

Le système 2FA (Two-Factor Authentication) a été entièrement implémenté dans le projet WR506D. Il utilise le protocole TOTP (Time-based One-Time Password) compatible avec Google Authenticator, Authy, et toutes les applications 2FA standard.

## Fonctionnalités implémentées

### Backend (Symfony)

#### 1. Entité User
Trois nouveaux champs ajoutés :
- `twoFactorSecret` (string, nullable) : Secret TOTP
- `twoFactorEnabled` (boolean) : Statut d'activation
- `twoFactorBackupCodes` (json, nullable) : Codes de secours hashés (SHA-256)

#### 2. Service TwoFactorService
**Localisation** : `src/Service/TwoFactorService.php`

Méthodes disponibles :
- `generateSecret()` : Génère un secret TOTP aléatoire
- `getProvisioningUri(User $user)` : Crée l'URI otpauth:// pour Google Authenticator
- `getQrCode(User $user)` : Génère le QR code en data URI PNG
- `verifyCode(User $user, string $code)` : Vérifie un code TOTP à 6 chiffres
- `generateBackupCodes()` : Génère 8 codes de secours aléatoires (8 caractères)
- `verifyBackupCode(User $user, string $code)` : Vérifie un code de secours
- `removeBackupCode(User $user, string $code)` : Supprime un code de secours après utilisation

#### 3. Contrôleur TwoFactorController
**Localisation** : `src/Controller/TwoFactorController.php`

Endpoints disponibles :

**POST /api/2fa/setup**
- Authentification : JWT Token requis
- Rôle : ROLE_USER
- Génère un secret TOTP et un QR code
- Retourne :
  ```json
  {
    "secret": "...",
    "qr_code": "data:image/png;base64,...",
    "provisioning_uri": "otpauth://totp/...",
    "message": "..."
  }
  ```

**POST /api/2fa/enable**
- Authentification : JWT Token requis
- Rôle : ROLE_USER
- Body : `{"code": "123456"}`
- Vérifie le code TOTP et active le 2FA
- Génère 8 codes de secours
- Retourne :
  ```json
  {
    "message": "2FA activé avec succès !",
    "backup_codes": ["...", "..."],
    "warning": "..."
  }
  ```

**POST /api/2fa/disable**
- Authentification : JWT Token requis
- Rôle : ROLE_USER
- Body : `{"code": "123456"}` (code TOTP ou code de secours)
- Désactive le 2FA et supprime toutes les données
- Retourne :
  ```json
  {
    "message": "2FA désactivé avec succès"
  }
  ```

**POST /api/2fa/verify**
- Authentification : Non requis (utilisé lors du login)
- Body : `{"email": "user@test.com", "code": "123456"}`
- Vérifie un code TOTP ou code de secours
- Si c'est un code de secours valide, il est automatiquement supprimé
- Retourne :
  ```json
  {
    "message": "Code valide",
    "is_backup_code": true,
    "remaining_backup_codes": 7
  }
  ```

**GET /api/2fa/status**
- Authentification : JWT Token requis
- Rôle : ROLE_USER
- Retourne le statut 2FA de l'utilisateur
- Retourne :
  ```json
  {
    "enabled": true,
    "backup_codes_count": 8
  }
  ```

#### 4. Endpoint /api/me mis à jour
Le endpoint existant `/api/me` a été enrichi pour inclure :
```json
{
  "id": 1,
  "email": "user@test.com",
  "firstname": "...",
  "lastname": "...",
  "roles": [...],
  "twoFactorEnabled": true
}
```

### Frontend (Vue.js)

#### 1. Page de profil avec gestion 2FA
**Localisation** : `frontend/src/views/ProfileView.vue`

Fonctionnalités :
- **Activation du 2FA** :
  - Bouton "Activer le 2FA"
  - Affichage du QR code à scanner
  - Affichage de la clé secrète (pour saisie manuelle)
  - Champ pour entrer le code de vérification
  - Affichage des 8 codes de secours après activation

- **Désactivation du 2FA** :
  - Bouton "Désactiver le 2FA"
  - Demande de confirmation avec un code 2FA
  - Suppression complète des données 2FA

#### 2. Login avec support 2FA
**Localisation** : `frontend/src/views/auth/LoginView.vue`

Flux de connexion :
1. L'utilisateur entre email + mot de passe
2. Si le 2FA est activé, un écran s'affiche demandant le code
3. L'utilisateur entre le code TOTP (6 chiffres) ou un code de secours (8 caractères)
4. Le code est vérifié via `/api/2fa/verify`
5. Si valide, l'utilisateur est connecté

#### 3. Store Auth enrichi
**Localisation** : `frontend/src/stores/auth.js`

Nouvelle méthode :
- `refreshUser()` : Rafraîchit les informations utilisateur depuis `/api/me`

## Flux utilisateur complet

### Activation du 2FA

1. **L'utilisateur se connecte** avec email + mot de passe
2. **Il va sur sa page de profil** (`/profile`)
3. **Il clique sur "Activer le 2FA"**
4. **Un QR code s'affiche** avec la clé secrète
5. **Il scanne le QR code** avec Google Authenticator
6. **Il entre le code à 6 chiffres** généré par l'application
7. **Le système vérifie le code** et active le 2FA
8. **8 codes de secours sont générés** et affichés une seule fois
9. **L'utilisateur doit les sauvegarder** dans un endroit sûr

### Connexion avec 2FA

1. **L'utilisateur entre email + mot de passe**
2. **Un écran 2FA s'affiche** demandant le code
3. **Il ouvre Google Authenticator** et lit le code
4. **Il entre le code à 6 chiffres**
5. **Le système vérifie** et le connecte
6. **Alternative** : Il peut utiliser un code de secours à la place

### Utilisation d'un code de secours

- Les codes de secours peuvent être utilisés **à la place du code TOTP**
- Chaque code de secours est **à usage unique**
- Après utilisation, le code est **automatiquement supprimé**
- Le système indique le **nombre de codes restants**

### Désactivation du 2FA

1. **L'utilisateur va sur sa page de profil**
2. **Il clique sur "Désactiver le 2FA"**
3. **Un champ demande un code de confirmation**
4. **Il entre un code TOTP ou un code de secours**
5. **Le système désactive le 2FA** et supprime toutes les données

## Sécurité

### Hachage des codes de secours
- Les codes de secours sont hachés avec **SHA-256** avant stockage
- Seuls les hash sont stockés en base de données
- Même en cas de compromission de la base, les codes ne peuvent être récupérés

### Protection des endpoints
- Tous les endpoints 2FA nécessitent une authentification JWT
- Seul `/api/2fa/verify` est public (pour le processus de login)

### Codes de secours
- **8 codes** générés lors de l'activation
- **Usage unique** : supprimés après utilisation
- **Hachés** en base de données
- Affichés **une seule fois** lors de l'activation

## Tests

### Backend
Tous les endpoints ont été testés et validés :
- ✅ Setup 2FA (génération secret + QR code)
- ✅ Enable 2FA (vérification code + génération backup codes)
- ✅ Verify 2FA (code TOTP + codes de secours)
- ✅ Disable 2FA (désactivation sécurisée)
- ✅ Status 2FA (récupération du statut)
- ✅ Suppression automatique des codes de secours après usage

### Frontend
- ✅ Interface d'activation avec QR code
- ✅ Interface de désactivation
- ✅ Login avec 2FA
- ✅ Gestion des codes de secours

## Bibliothèques utilisées

### Backend (Composer)
- `spomky-labs/otphp` (v11.4.1) : Génération et vérification TOTP
- `endroid/qr-code` (v6.1.0) : Génération des QR codes

### Frontend (npm)
- `axios` : Requêtes HTTP vers l'API
- `vue-router` : Navigation
- `pinia` : Gestion d'état

## Migration de base de données

Migration créée : `Version20260112113752.php`

SQL exécuté :
```sql
ALTER TABLE "user" ADD two_factor_secret VARCHAR(255) DEFAULT NULL;
ALTER TABLE "user" ADD two_factor_enabled BOOLEAN DEFAULT false NOT NULL;
ALTER TABLE "user" ADD two_factor_backup_codes JSON DEFAULT NULL;
```

## Compatibilité

Le système est compatible avec toutes les applications d'authentification supportant le protocole TOTP :
- ✅ Google Authenticator
- ✅ Authy
- ✅ Microsoft Authenticator
- ✅ 1Password
- ✅ LastPass Authenticator
- ✅ Et toutes les autres applications TOTP standard

## Notes importantes

1. **Les codes de secours** ne sont affichés qu'une seule fois lors de l'activation
2. **Les secrets TOTP** sont stockés en base de données (sécurisation supplémentaire recommandée en production)
3. **Les codes de secours** sont hachés (SHA-256) avant stockage
4. **Le 2FA est optionnel** : chaque utilisateur décide de l'activer ou non
5. **Pas de blocage** : si l'utilisateur perd ses codes, un admin peut désactiver le 2FA en base

## Améliorations futures possibles

- Ajout d'une fonctionnalité de régénération des codes de secours
- Notification par email lors de l'activation/désactivation du 2FA
- Historique des connexions avec 2FA
- Support de WebAuthn (FIDO2) en complément
- Rate limiting sur les tentatives de vérification 2FA
- Chiffrement du secret TOTP en base de données

---

**Date de création** : 12 janvier 2026
**Version** : 1.0
**Développeur** : Claude Sonnet 4.5 + Lucas
