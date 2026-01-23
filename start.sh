#!/bin/bash

echo "🚀 Démarrage de MovieCMS..."

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Vérifier Docker
if ! docker ps &> /dev/null; then
    echo -e "${RED}❌ Docker n'est pas démarré. Démarrez Docker Desktop et relancez ce script.${NC}"
    exit 1
fi

# Démarrer PostgreSQL
echo -e "${BLUE}📦 Démarrage de PostgreSQL...${NC}"
docker-compose up -d

sleep 2

# Démarrer le backend
echo -e "${BLUE}🔧 Démarrage du backend Symfony...${NC}"
php -S localhost:8000 -t public &
BACKEND_PID=$!

sleep 2

# Vérifier que le backend répond
if curl -s http://localhost:8000/api > /dev/null; then
    echo -e "${GREEN}✅ Backend démarré sur http://localhost:8000${NC}"
else
    echo -e "${RED}❌ Erreur lors du démarrage du backend${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Backend prêt !${NC}"
echo ""
echo "Pour démarrer le frontend :"
echo "  cd frontend"
echo "  npm run dev"
echo ""
echo "Pour arrêter le backend :"
echo "  kill $BACKEND_PID"
echo ""
echo "URLs:"
echo "  Backend API: http://localhost:8000/api"
echo "  Frontend:    http://localhost:5173 (après npm run dev)"
echo ""
