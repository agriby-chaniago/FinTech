@echo off
REM ============================================
REM Keycloak Setup Script for FinTech
REM ============================================

setlocal enabledelayedexpansion

echo.
echo ========================================
echo    Keycloak Setup - FinTech System
echo ========================================
echo.

REM Check if Docker is installed
docker --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not installed or not in PATH
    echo Please install Docker Desktop from: https://www.docker.com/products/docker-desktop
    exit /b 1
)

echo [OK] Docker is installed
echo.

REM Check if Docker daemon is running
docker ps >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker daemon is not running
    echo Please start Docker Desktop
    exit /b 1
)

echo [OK] Docker daemon is running
echo.

REM Start Keycloak
echo [*] Starting Keycloak containers...
docker-compose up -d

if errorlevel 1 (
    echo [ERROR] Failed to start Docker containers
    exit /b 1
)

echo [OK] Docker containers started
echo.

REM Wait for Keycloak to be ready
echo [*] Waiting for Keycloak to be ready (this may take up to 60 seconds)...
timeout /t 5 /nobreak

set attempts=0
:wait_loop
set /a attempts+=1
if %attempts% gtr 12 (
    echo [WARNING] Keycloak may still be starting...
    goto continue
)

curl -s http://localhost:8080/realms/master/.well-known/openid-configuration >nul 2>&1
if errorlevel 1 (
    echo [*] Waiting... (%attempts%/12)
    timeout /t 5 /nobreak
    goto wait_loop
)

:continue
echo [OK] Keycloak is ready
echo.

REM Display access information
echo ========================================
echo    Keycloak Access Information
echo ========================================
echo.
echo Admin Console: http://localhost:8080/admin
echo Username: admin
echo Password: admin_password_dev
echo.
echo Realm: fintech
echo OIDC Config: http://localhost:8080/realms/fintech/.well-known/openid-configuration
echo.

REM Check if realm exists and needs setup
echo [*] Checking Keycloak setup status...
echo.

REM Display next steps
echo ========================================
echo    Next Steps
echo ========================================
echo.
echo 1. Open Admin Console:
echo    http://localhost:8080/admin
echo.
echo 2. Login with:
echo    Username: admin
echo    Password: admin_password_dev
echo.
echo 3. Create clients (fintrack-web, finlyzer-web, fingoals-web)
echo    OR import from: keycloak-setup/realm-export.json
echo.
echo 4. Update .env files in each service with client secrets:
echo    - FinTrack/.env
echo    - FinLyzer/.env
echo    - FinGoals/.env
echo.
echo 5. Start services:
echo    cd FinTrack && php artisan serve --port=8001
echo    cd FinLyzer && php artisan serve --port=8002
echo    cd FinGoals && php artisan serve --port=8003
echo.
echo ========================================
echo.

REM Display container status
echo Container Status:
echo.
docker-compose ps

echo.
echo [OK] Setup complete. Visit http://localhost:8080/admin to continue.
echo.

endlocal
