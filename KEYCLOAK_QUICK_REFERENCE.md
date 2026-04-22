# Keycloak Quick Reference - Commands & Tips

## 🚀 Quick Start Commands

### Windows (PowerShell/CMD)
```bash
# Start Keycloak
cd "d:\FOLDER SEMESTER 6\web service\FinTech"
.\setup-keycloak.bat

# Or manually
docker-compose up -d

# View logs
docker-compose logs -f keycloak

# Stop
docker-compose down
```

### Linux/Mac
```bash
# Start Keycloak
cd "d:\FOLDER SEMESTER 6\web service\FinTech"
chmod +x setup-keycloak.sh
./setup-keycloak.sh

# View logs
docker-compose logs -f keycloak

# Stop
docker-compose down
```

---

## 🔑 Key Endpoints

| Endpoint | URL |
|----------|-----|
| Admin Console | http://localhost:8080/admin |
| OIDC Discovery | http://localhost:8080/realms/fintech/.well-known/openid-configuration |
| Authorization | http://localhost:8080/realms/fintech/protocol/openid-connect/auth |
| Token | http://localhost:8080/realms/fintech/protocol/openid-connect/token |
| Userinfo | http://localhost:8080/realms/fintech/protocol/openid-connect/userinfo |
| Logout | http://localhost:8080/realms/fintech/protocol/openid-connect/logout |
| JWKS | http://localhost:8080/realms/fintech/protocol/openid-connect/certs |

---

## 🐳 Docker Commands

```bash
# List containers
docker-compose ps

# View logs
docker-compose logs keycloak
docker-compose logs postgres

# Restart service
docker-compose restart keycloak

# Rebuild images
docker-compose down
docker-compose up -d --build

# Remove everything
docker-compose down -v  # -v removes volumes

# Check container health
docker inspect fintech-keycloak --format='{{.State.Health.Status}}'
```

---

## 📝 Database Commands

```bash
# Access PostgreSQL
docker exec -it keycloak-db psql -U keycloak -d keycloak

# List tables
\dt

# Query users
SELECT id, username, email FROM user_entity;

# Exit
\q
```

---

## 🔍 Testing & Verification

### Test OIDC Discovery
```bash
curl http://localhost:8080/realms/fintech/.well-known/openid-configuration
```

### Get Access Token
```bash
curl -X POST \
  http://localhost:8080/realms/fintech/protocol/openid-connect/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials" \
  -d "client_id=fintrack-web" \
  -d "client_secret=YOUR_CLIENT_SECRET"
```

### Verify JWT Token
1. Go to https://jwt.io
2. Paste token di "Encoded" section
3. Paste public key dari JWKS endpoint untuk verify

---

## 🐛 Troubleshooting

### Keycloak not starting
```bash
# Check logs
docker-compose logs keycloak

# Restart
docker-compose down
docker-compose up -d

# Wait for health check
docker ps
```

### PostgreSQL connection issues
```bash
# Check database status
docker-compose logs postgres

# Restart database
docker-compose restart postgres
docker-compose restart keycloak
```

### Port already in use (8080 atau 5432)
```bash
# Find process using port 8080
netstat -ano | findstr :8080  # Windows
lsof -i :8080                  # Mac/Linux

# Kill process (Windows)
taskkill /PID <PID> /F

# Or change port in docker-compose.yml
ports:
  - "8888:8080"  # Use 8888 instead
```

### Client secret not matching
```bash
# In Keycloak Admin Console
1. Go to Clients → fintrack-web
2. Credentials tab
3. Check "Client ID and Secret" is selected
4. View or Regenerate Secret
5. Copy exact value (no spaces)
6. Update .env file
```

---

## 📊 Admin Console Navigation

```
Admin Console (http://localhost:8080/admin)
├── Realm: fintech
│   ├── Clients
│   │   ├── fintrack-web
│   │   ├── finlyzer-web
│   │   └── fingoals-web
│   ├── Users
│   │   └── Create test users
│   ├── Roles
│   │   └── Create roles (optional)
│   ├── Realm Settings
│   │   ├── Login (email verification)
│   │   ├── Security (brute force)
│   │   ├── Tokens (expiration)
│   │   └── Email (SMTP)
│   └── Events
│       └── Configure audit logging
```

---

## 🔐 Important Configuration

### For Development Only:
- ✅ HTTP allowed (KEYCLOAK_PROXY: edge)
- ✅ Weak passwords allowed
- ✅ CORS open to localhost

### For Production:
- 🔒 HTTPS required
- 🔒 Strong admin password
- 🔒 Change default secrets
- 🔒 Configure SMTP
- 🔒 Configure database backup
- 🔒 Enable brute force protection
- 🔒 Restrict CORS
- 🔒 Enable audit logs

---

## 📱 Service-Specific Configs

### FinTrack
```env
KEYCLOAK_CLIENT_ID=fintrack-web
KEYCLOAK_CLIENT_SECRET=<SECRET>
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8001/auth/oidc/callback
```

### FinLyzer
```env
KEYCLOAK_CLIENT_ID=finlyzer-web
KEYCLOAK_CLIENT_SECRET=<SECRET>
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8002/auth/oidc/callback
```

### FinGoals
```env
KEYCLOAK_CLIENT_ID=fingoals-web
KEYCLOAK_CLIENT_SECRET=<SECRET>
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8003/auth/oidc/callback
```

---

## ✅ Setup Checklist

- [ ] Docker installed and running
- [ ] Run `setup-keycloak.bat` (Windows) or `setup-keycloak.sh` (Linux/Mac)
- [ ] Keycloak accessible at http://localhost:8080
- [ ] Login to admin console (admin/admin_password_dev)
- [ ] Create/verify 3 clients (fintrack-web, finlyzer-web, fingoals-web)
- [ ] Copy client secrets
- [ ] Update .env files in FinTrack, FinLyzer, FinGoals
- [ ] Test OAuth2 flow
- [ ] Verify JWT token validation
- [ ] Configure email (optional for dev)
- [ ] Create test users
- [ ] Test login in each service

---

**Last Updated**: 2026-04-22
