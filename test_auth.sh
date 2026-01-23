#!/bin/bash

# Script de test pour l'authentification JWT
# Usage: ./test_auth.sh

BASE_URL="http://localhost:8000"
EMAIL="test@example.com"
PASSWORD="MYPASSWORD"

echo "=========================================="
echo "Test d'authentification JWT"
echo "=========================================="
echo ""

# 1. Obtenir un token
echo "1. Obtention du token..."
RESPONSE=$(curl -s -X POST "$BASE_URL/auth" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $RESPONSE | python3 -c "import sys, json; print(json.load(sys.stdin)['token'])" 2>/dev/null)

if [ -z "$TOKEN" ]; then
  echo "❌ Échec de l'authentification"
  echo "Réponse: $RESPONSE"
  exit 1
fi

echo "✅ Token obtenu avec succès!"
echo "Token: ${TOKEN:0:50}..."
echo ""

# 2. Tester l'accès à l'API
echo "2. Test d'accès à l'API avec le token..."
API_RESPONSE=$(curl -s -X GET "$BASE_URL/api/movies" \
  -H "Accept: application/ld+json" \
  -H "Authorization: Bearer $TOKEN")

TOTAL_ITEMS=$(echo $API_RESPONSE | python3 -c "import sys, json; print(json.load(sys.stdin)['totalItems'])" 2>/dev/null)

if [ ! -z "$TOTAL_ITEMS" ]; then
  echo "✅ Accès à l'API réussi!"
  echo "Nombre total de films: $TOTAL_ITEMS"
else
  echo "❌ Échec d'accès à l'API"
  echo "Réponse: ${API_RESPONSE:0:200}..."
  exit 1
fi
echo ""

# 3. Tester une requête POST
echo "3. Test de création d'un film..."
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/api/movies" \
  -H "Content-Type: application/ld+json" \
  -H "Accept: application/ld+json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"name":"Test Auth Movie","description":"Film créé via test auth","duration":120,"budget":1000000,"draft":false,"online":true,"director":"/api/directors/1"}')

MOVIE_ID=$(echo $CREATE_RESPONSE | python3 -c "import sys, json; print(json.load(sys.stdin).get('id', ''))" 2>/dev/null)

if [ ! -z "$MOVIE_ID" ]; then
  echo "✅ Film créé avec succès!"
  echo "ID du film: $MOVIE_ID"
else
  echo "❌ Échec de création du film"
  echo "Réponse: ${CREATE_RESPONSE:0:200}..."
fi
echo ""

# 4. Tester sans token
echo "4. Test d'accès sans token (devrait fonctionner car pas de restriction)..."
NO_TOKEN_RESPONSE=$(curl -s -X GET "$BASE_URL/api/movies?page=1" \
  -H "Accept: application/ld+json")

NO_TOKEN_ITEMS=$(echo $NO_TOKEN_RESPONSE | python3 -c "import sys, json; print(json.load(sys.stdin).get('totalItems', 'error'))" 2>/dev/null)

if [ "$NO_TOKEN_ITEMS" != "error" ]; then
  echo "⚠️  L'API est accessible sans token (pas de restriction actuellement)"
  echo "💡 Pour sécuriser, ajoutez 'security: IS_AUTHENTICATED_FULLY' dans config/packages/security.yaml"
else
  echo "✅ L'API est protégée (accès refusé sans token)"
fi
echo ""

echo "=========================================="
echo "Tests terminés!"
echo "=========================================="
