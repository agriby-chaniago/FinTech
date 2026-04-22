#!/bin/bash

# ============================================
# Keycloak Setup Script for FinTech (Linux/Mac)
# ============================================

set -e

echo ""
echo "========================================"
echo "   Keycloak Setup - FinTech System"
echo "========================================"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "[ERROR] Docker is not installed"
    echo "Please install Docker from: https://www.docker.com/"
    exit 1
fi

echo "[OK] Docker is installed"
echo ""

# Check if Docker daemon is running
if ! docker ps &> /dev/null; then
    echo "[ERROR] Docker daemon is not running"
    echo "Please start Docker"
    exit 1
fi

echo "[OK] Docker daemon is running"
echo ""

# Start Keycloak
echo "[*] Starting Keycloak containers..."
docker-compose up -d

echo "[OK] Docker containers started"
echo ""

# Wait for Keycloak to be ready
echo "[*] Waiting for Keycloak to be ready (this may take up to 60 seconds)..."
sleep 5

attempts=0
while [ $attempts -lt 12 ]; do
    if curl -s http://localhost:8080/realms/master/.well-known/openid-configuration > /dev/null 2>&1; then
        break
    fi
    attempts=$((attempts+1))
    echo "[*] Waiting... ($attempts/12)"
    sleep 5
done

echo "[OK] Keycloak is ready"
echo ""

# Display access information
echo "========================================"
echo "   Keycloak Access Information"
echo "========================================"
echo ""
echo "Admin Console: http://localhost:8080/admin"
echo "Username: admin"
echo "Password: admin_password_dev"
echo ""
echo "Realm: fintech"
echo "OIDC Config: http://localhost:8080/realms/fintech/.well-known/openid-configuration"
echo ""

# Display next steps
echo "========================================"
echo "   Next Steps"
echo "========================================"
echo ""
echo "1. Open Admin Console:"
echo "   http://localhost:8080/admin"
echo ""
echo "2. Login with:"
echo "   Username: admin"
echo "   Password: admin_password_dev"
echo ""
echo "3. Create clients (fintrack-web, finlyzer-web, fingoals-web)"
echo "   OR import from: keycloak-setup/realm-export.json"
echo ""
echo "4. Update .env files in each service with client secrets:"
echo "   - FinTrack/.env"
echo "   - FinLyzer/.env"
echo "   - FinGoals/.env"
echo ""
echo "5. Start services:"
echo "   cd FinTrack && php artisan serve --port=8001"
echo "   cd FinLyzer && php artisan serve --port=8002"
echo "   cd FinGoals && php artisan serve --port=8003"
echo ""
echo "========================================"
echo ""

# Display container status
echo "Container Status:"
echo ""
docker-compose ps

echo ""
echo "[OK] Setup complete. Visit http://localhost:8080/admin to continue."
echo ""
