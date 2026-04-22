# Keycloak Setup Guide - FinTech Microservices

## 📋 Overview

Panduan lengkap untuk setup Keycloak sebagai authentication provider untuk sistem FinTech yang mencakup 3 service:
- **FinTrack** (port 8001)
- **FinLyzer** (port 8002)
- **FinGoals** (port 8003)

---

## 🚀 Quick Start

### 1. Start Keycloak dengan Docker

```bash
# Dari root folder FinTech
cd d:\FOLDER\ SEMESTER\ 6\web\ service\FinTech
docker-compose up -d
```

**Output expected:**
```
Creating keycloak-db ... done
Creating keycloak   ... done
```

**Verify status:**
```bash
docker-compose ps
```

### 2. Access Keycloak Admin Console

- **URL**: http://localhost:8080/admin
- **Username**: `admin`
- **Password**: `admin_password_dev`

> ⚠️ **NOTE**: Change credentials di production!

---

## 🔧 Manual Setup (Jika tidak menggunakan Docker)

### 1. Create Realm: `fintech`

1. Login ke Keycloak Admin Console
2. Click **Create Realm** atau klik "fintech" di sidebar
3. Set realm name: `fintech`
4. Click **Create**

### 2. Create Clients

#### Client 1: fintrack-web

1. Di sidebar kiri, pilih **Clients**
2. Click **Create client**
3. Set **Client ID**: `fintrack-web`
4. Click **Next**
5. Kapasitas **Standard flow** pada
6. Click **Next**
7. Set redirect URIs:
   - `http://127.0.0.1:8001/auth/oidc/callback`
   - `http://localhost:8001/auth/oidc/callback`
8. Set post logout redirect URIs:
   - `http://127.0.0.1:8001`
   - `http://localhost:8001`
9. Click **Save**
10. Tab **Credentials** → Copy **Client Secret**

#### Client 2: finlyzer-web

Ulangi langkah di atas dengan:
- **Client ID**: `finlyzer-web`
- **Redirect URIs**:
  - `http://127.0.0.1:8002/auth/oidc/callback`
  - `http://localhost:8002/auth/oidc/callback`
- **Post logout URIs**:
  - `http://127.0.0.1:8002`
  - `http://localhost:8002`

#### Client 3: fingoals-web

Ulangi dengan:
- **Client ID**: `fingoals-web`
- **Redirect URIs**:
  - `http://127.0.0.1:8003/auth/oidc/callback`
  - `http://localhost:8003/auth/oidc/callback`
- **Post logout URIs**:
  - `http://127.0.0.1:8003`
  - `http://localhost:8003`

### 3. Enable Security Features

Di realm **fintech** settings:

1. **Login** tab:
   - Enable "Email as username"
   - Enable "Remember me"

2. **Security** tab:
   - Enable "Brute force protection"
   - Set "Max login failures": 5
   - Set "Failure reset time": 30 menit

3. **Tokens** tab:
   - Refresh token expiration: 30 menit
   - Access token lifespan: 5 menit

4. **Email** tab:
   - Configure SMTP server (gunakan MailHog untuk dev)

---

## 📝 Environment Configuration

Setiap service memiliki file `.env.keycloak`. Update file `.env` utama dengan:

### FinTrack (.env)

```bash
# Copy dari .env.keycloak
KEYCLOAK_BASE_URL=http://localhost:8080
KEYCLOAK_REALM=fintech
KEYCLOAK_ISSUER=http://localhost:8080/realms/fintech
KEYCLOAK_ENABLED=true
AUTH_MODE=hybrid

KEYCLOAK_CLIENT_ID=fintrack-web
KEYCLOAK_CLIENT_SECRET=<COPY_FROM_KEYCLOAK_ADMIN>
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8001/auth/oidc/callback
KEYCLOAK_POST_LOGOUT_REDIRECT_URI=http://127.0.0.1:8001
```

### FinLyzer (.env)

```bash
KEYCLOAK_CLIENT_ID=finlyzer-web
KEYCLOAK_CLIENT_SECRET=<COPY_FROM_KEYCLOAK_ADMIN>
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8002/auth/oidc/callback
KEYCLOAK_POST_LOGOUT_REDIRECT_URI=http://127.0.0.1:8002
```

### FinGoals (.env)

```bash
KEYCLOAK_CLIENT_ID=fingoals-web
KEYCLOAK_CLIENT_SECRET=<COPY_FROM_KEYCLOAK_ADMIN>
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8003/auth/oidc/callback
KEYCLOAK_POST_LOGOUT_REDIRECT_URI=http://127.0.0.1:8003
```

---

## 🧪 Testing

### 1. Verify Keycloak Endpoints

```bash
# Dari PowerShell/Terminal
curl http://localhost:8080/realms/fintech/.well-known/openid-configuration
```

Expected output: JSON dengan OIDC endpoints

### 2. Test OAuth2 Flow

```bash
# 1. Get authorization code
$authUrl = "http://localhost:8080/realms/fintech/protocol/openid-connect/auth?client_id=fintrack-web&response_type=code&redirect_uri=http://127.0.0.1:8001/auth/oidc/callback&scope=openid+profile+email"
# Buka di browser dan login

# 2. Exchange code for token (dari redirect URL)
curl -X POST http://localhost:8080/realms/fintech/protocol/openid-connect/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=authorization_code" \
  -d "client_id=fintrack-web" \
  -d "client_secret=<SECRET>" \
  -d "code=<AUTHORIZATION_CODE>" \
  -d "redirect_uri=http://127.0.0.1:8001/auth/oidc/callback"
```

### 3. Verify Laravel Integration

```bash
# Di masing-masing service folder
php artisan config:show keycloak

# Check routes
php artisan route:list | grep auth
```

---

## 📊 Create Test Users

Di Keycloak Admin Console:

1. **Users** menu → **Add user**
2. Username: `testuser`
3. Email: `testuser@fintech.local`
4. First Name: `Test`
5. Last Name: `User`
6. Click **Create**
7. **Credentials** tab → Set password
8. Uncheck "Temporary"
9. Click **Set Password**

---

## 🔗 Service Integration

Setiap service sudah memiliki `config/keycloak.php`. Pastikan sudah ada:

- `routes/api.php` dengan OIDC callback route
- Middleware authentication di `app/Http/Middleware`
- User model integration di `app/Models`

Contoh di `routes/web.php`:

```php
Route::middleware(['web'])->group(function () {
    // OIDC callback
    Route::get('/auth/oidc/callback', [AuthController::class, 'handleOidcCallback']);
    
    // Login redirect
    Route::get('/login', [AuthController::class, 'redirectToKeycloak']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

---

## 🚨 Troubleshooting

### Issue 1: Connection Refused ke Keycloak

```
Error: Connection refused to localhost:8080
```

**Solution:**
```bash
# Check Docker status
docker-compose ps

# Restart services
docker-compose down
docker-compose up -d
```

### Issue 2: Client Secret Mismatch

**Solution:**
- Copy exact client secret dari Keycloak Admin
- Pastikan di `.env` tidak ada whitespace ekstra
- Check: `php artisan config:show keycloak`

### Issue 3: Redirect URI Mismatch

Error: "Redirect URI mismatch"

**Solution:**
- Pastikan redirect URI di Keycloak **exactly match** dengan `KEYCLOAK_REDIRECT_URI`
- Check port numbers (8001, 8002, 8003)
- Check protocol (http vs https)

### Issue 4: CORS Issues

**Solution:**
Di realm **fintech** settings:

1. **Web Origins** tab
2. Add:
   - `http://127.0.0.1:8001`
   - `http://localhost:8001`
   - (dan untuk port 8002, 8003)

---

## 📈 Monitoring & Logs

### Docker Logs

```bash
# Keycloak logs
docker-compose logs -f keycloak

# Database logs
docker-compose logs -f postgres
```

### Check Token Validity

Gunakan jwt.io untuk decode dan verify JWT tokens yang diterima dari Keycloak.

---

## 🔐 Security Checklist (Development)

- [x] Realm created: `fintech`
- [x] 3 clients configured dengan redirect URIs
- [x] PKCE enabled untuk semua clients
- [x] Brute force protection enabled
- [x] Email verification configured
- [x] HTTPS required (di production)
- [x] Admin credentials changed (di production)

---

## 📚 Resources

- [Keycloak Documentation](https://www.keycloak.org/documentation)
- [OpenID Connect Protocol](https://openid.net/specs/openid-connect-core-1_0.html)
- [Laravel OIDC Integration](https://laravel.com/docs/authentication)

---

## 🎯 Next Steps

1. ✅ Start Keycloak dengan docker-compose
2. ✅ Setup 3 clients di Keycloak
3. ✅ Copy client secrets ke `.env` files
4. ✅ Test OAuth2 flow
5. ⏳ Implement AuthController di setiap service
6. ⏳ Setup user synchronization
7. ⏳ Configure hybrid authentication mode
8. ⏳ Begin Phase 8 - Hybrid Rollout

---

**Last Updated**: 2026-04-22  
**Status**: Ready for Development
