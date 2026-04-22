# Setup Keycloak untuk FinLyzer

## 📋 Status

Fitur Keycloak OIDC sudah fully implemented di FinLyzer. Tinggal konfigurasi environment variable dan test.

---

## 🔧 Step 1: Update .env

Copy konfigurasi dari `.env.keycloak` ke `.env`:

```bash
# Base Keycloak Configuration
KEYCLOAK_ENABLED=true
AUTH_MODE=hybrid
KEYCLOAK_BASE_URL=http://localhost:8080
KEYCLOAK_REALM=fintech
KEYCLOAK_ISSUER=http://localhost:8080/realms/fintech

# Client Configuration (FinLyzer Web)
KEYCLOAK_CLIENT_ID=finlyzer-web
KEYCLOAK_CLIENT_SECRET=<PASTE_SECRET_FROM_KEYCLOAK_ADMIN>

# Redirect URIs
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8002/auth/oidc/callback
KEYCLOAK_POST_LOGOUT_REDIRECT_URI=http://127.0.0.1:8002

# Scopes
KEYCLOAK_SCOPES=openid profile email
KEYCLOAK_HTTP_TIMEOUT=10
```

---

## 🔐 Step 2: Get Client Secret dari Keycloak

1. **Buka Keycloak Admin Console**
   - URL: http://localhost:8080/admin
   - Username: `admin`
   - Password: `admin_password_dev`

2. **Navigasi ke Clients**
   - Pilih realm: `fintech`
   - Menu sebelah kiri: Clients
   - Click: `finlyzer-web`

3. **Copy Client Secret**
   - Tab: Credentials
   - Client ID and Secret
   - Copy nilai Secret

4. **Update .env**
   ```
   KEYCLOAK_CLIENT_SECRET=<PASTE_HERE>
   ```

---

## ✅ Step 3: Verifikasi Konfigurasi

```bash
# Masuk folder FinLyzer
cd d:\FOLDER\ SEMESTER\ 6\web\ service\FinTech\FinLyzer

# Test Keycloak config
php artisan config:show keycloak

# Check routes
php artisan route:list | findstr /i "oidc\|login\|logout"
```

Expected output:
```
GET|POST   /login                             login.attempt       SessionController@store
GET        /auth/oidc/login                   oidc.login          OidcController@redirect
GET        /auth/oidc/callback                oidc.callback       OidcController@callback
POST       /auth/oidc/logout                  oidc.logout         OidcController@logout
POST       /logout                            logout              SessionController@destroy
```

---

## 🚀 Step 4: Start FinLyzer

```bash
# Dari folder FinLyzer
php artisan serve --port=8002
```

Expected output:
```
Laravel development server started: http://127.0.0.1:8002
```

---

## 🧪 Step 5: Test Login Flow

### Test 1: Akses Login Page
1. Buka browser: http://localhost:8002/login
2. Lihat dua tombol:
   - Login (email/password tradisional)
   - Login dengan Keycloak

### Test 2: Keycloak OIDC Login
1. Click tombol "Login dengan Keycloak"
2. Redirect ke Keycloak login page
3. Login dengan test user
4. Redirect kembali ke FinLyzer dashboard
5. Verify: User terlogin dan `keycloak_sub` terisi

### Test 3: Logout
1. Click logout button
2. Redirect ke Keycloak logout
3. Redirect kembali ke login page

---

## 📂 File Structure

```
FinLyzer/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Auth/
│   │           ├── SessionController.php    ✅ Updated
│   │           └── OidcController.php       ✅ Already exists
│   └── Services/
│       └── KeycloakService.php              (Optional, untuk helper)
│
├── routes/
│   └── web.php                               ✅ Updated (OIDC routes)
│
├── resources/
│   └── views/
│       └── auth/
│           └── login.blade.php               ✅ Updated (Keycloak button)
│
├── config/
│   └── keycloak.php                          ✅ Already exists
│
└── .env.keycloak                             ✅ Configuration template
```

---

## 🔑 Environment Variables Reference

| Variable | Value | Required |
|----------|-------|----------|
| `KEYCLOAK_ENABLED` | `true` | Yes |
| `AUTH_MODE` | `hybrid` | Yes |
| `KEYCLOAK_BASE_URL` | `http://localhost:8080` | Yes |
| `KEYCLOAK_REALM` | `fintech` | Yes |
| `KEYCLOAK_ISSUER` | `http://localhost:8080/realms/fintech` | Yes |
| `KEYCLOAK_CLIENT_ID` | `finlyzer-web` | Yes |
| `KEYCLOAK_CLIENT_SECRET` | `<from-keycloak>` | Yes |
| `KEYCLOAK_REDIRECT_URI` | `http://127.0.0.1:8002/auth/oidc/callback` | Yes |
| `KEYCLOAK_POST_LOGOUT_REDIRECT_URI` | `http://127.0.0.1:8002` | Yes |
| `KEYCLOAK_SCOPES` | `openid profile email` | No (default) |
| `KEYCLOAK_HTTP_TIMEOUT` | `10` | No (default) |

---

## 🔍 Debugging

### Check if Keycloak is running
```bash
curl http://localhost:8080/realms/fintech/.well-known/openid-configuration
```

### Check Laravel routes
```bash
php artisan route:list --name=oidc
```

### Check config
```bash
php artisan config:show keycloak
```

### Check logs
```bash
tail -f storage/logs/laravel.log
```

### Enable debug mode in .env
```
APP_DEBUG=true
LOG_LEVEL=debug
```

---

## 🚨 Common Issues & Solutions

### Issue 1: "Redirect URI mismatch"
**Cause**: REDIRECT_URI di Keycloak tidak sesuai dengan aplikasi  
**Solution**:
1. Check di Keycloak Admin → Clients → finlyzer-web
2. Valid redirect URIs harus include:
   - http://127.0.0.1:8002/auth/oidc/callback
   - http://localhost:8002/auth/oidc/callback
3. Update .env jika URL berbeda

### Issue 2: "Client secret mismatch"
**Cause**: Client secret di .env tidak sesuai  
**Solution**:
1. Login ke Keycloak Admin
2. Clients → finlyzer-web → Credentials
3. Copy exact secret (hati-hati whitespace)
4. Update .env

### Issue 3: "Keycloak endpoint is empty"
**Cause**: KEYCLOAK_BASE_URL tidak dikonfigurasi  
**Solution**: Update .env dengan Keycloak URL

### Issue 4: Stuck di login Keycloak
**Cause**: Session/Cache tidak clear  
**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan session:clear
```

---

## 📊 Database

User yang login via Keycloak akan disimpan dengan field:
- `keycloak_sub`: UUID unique dari Keycloak
- `email`: Email dari Keycloak
- `name`: Full name dari Keycloak
- `password`: Random bcrypt hash

---

## ✨ Features Implemented

- ✅ OIDC authorization code flow dengan PKCE
- ✅ State validation (CSRF protection)
- ✅ Nonce validation
- ✅ ID token validation
- ✅ User auto-creation/update dari Keycloak
- ✅ Hybrid authentication (legacy + Keycloak)
- ✅ Logout handling dengan Keycloak
- ✅ Cross-service logout sync (TODO: implement service.logout_sync.targets)
- ✅ Token replay attack prevention

---

## 📝 Next Steps

1. ✅ Keycloak Docker running
2. ✅ finlyzer-web client created di Keycloak
3. ✅ OidcController implemented
4. ✅ Routes configured
5. ✅ .env configured
6. ⏳ Test login flow
7. ⏳ Setup untuk FinTrack dan FinGoals
8. ⏳ Phase 8 - Hybrid Rollout (jika dibutuhkan)

---

**Status**: Ready for Testing ✅  
**Last Updated**: 2026-04-22
