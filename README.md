# FinTech Monorepo - Cara Menjalankan Semua Service

Panduan ini untuk menjalankan seluruh stack lokal:

- FinTrack (Service A)
- FinLyzer (Service B)
- FinGoals (Service C)
- Keycloak (OIDC)

## Quick Start 5 Menit

Bagian ini untuk kamu yang mau langsung jalan dulu, detail lengkap tetap ada di section bawah.

### Jalankan Harian (Setelah Setup Awal Selesai)

Jalankan di 4 terminal terpisah:

Terminal 1 - Keycloak

```bash
docker start keycloak-fintech
```

Terminal 2 - FinTrack (A)

```bash
cd FinTrack
php artisan serve --host=127.0.0.1 --port=8001
```

Terminal 3 - FinLyzer (B)

```bash
cd FinLyzer
php artisan serve --host=127.0.0.1 --port=8002
```

Terminal 4 - FinGoals (C)

```bash
cd FinGoals
php artisan serve --host=127.0.0.1 --port=8003
```

Verifikasi cepat:

```bash
curl -sf http://127.0.0.1:8080/realms/fintech/.well-known/openid-configuration | head -c 200
```

### Setup Awal Sekali Saja

Lakukan sekali di root repository:

```bash
for svc in FinTrack FinLyzer FinGoals; do
  cd "$svc"
  cp -n .env.example .env
  composer install
  npm install
  php artisan key:generate
  php artisan migrate
  cd ..
done
```

Lalu sesuaikan env berikut sebelum run harian:

- Keycloak OIDC vars: `AUTH_MODE=oidc`, `KEYCLOAK_ENABLED=true`, `KEYCLOAK_*`
- Koneksi DB tiap service
- Pair API key antar service (lihat section Sinkronkan API Key Antar Service)

## Ringkasan Port

- Keycloak: http://127.0.0.1:8080
- FinTrack (A): http://127.0.0.1:8001
- FinLyzer (B): http://127.0.0.1:8002
- FinGoals (C): http://127.0.0.1:8003

## Prasyarat

Pastikan tools ini tersedia di mesin lokal:

- PHP 8.2+
- Composer 2+
- Node.js 18+ dan npm
- MySQL 8+
- Redis (opsional, direkomendasikan)
- Docker (untuk menjalankan Keycloak)

## 1) Jalankan Keycloak

Jika container sudah pernah dibuat:

```bash
docker start keycloak-fintech
```

Jika belum ada, buat container baru:

```bash
docker run --name keycloak-fintech \
  -p 127.0.0.1:8080:8080 \
  -e KEYCLOAK_ADMIN=admin \
  -e KEYCLOAK_ADMIN_PASSWORD=admin \
  quay.io/keycloak/keycloak:26.6.0 start-dev
```

Verifikasi OIDC discovery endpoint:

```bash
curl -sf http://127.0.0.1:8080/realms/fintech/.well-known/openid-configuration | head -c 200
```

Buka admin console: http://127.0.0.1:8080/admin

## 2) Setup Realm dan Client di Keycloak

Buat realm: `fintech`

Buat client berikut:

1. `fintrack-web`

- Redirect URI: `http://127.0.0.1:8001/auth/oidc/callback`
- Post logout URI: `http://127.0.0.1:8001`

2. `finlyzer-web`

- Redirect URI: `http://127.0.0.1:8002/auth/oidc/callback`
- Post logout URI: `http://127.0.0.1:8002`

3. `fingoals-web`

- Redirect URI: `http://127.0.0.1:8003/auth/oidc/callback`
- Post logout URI: `http://127.0.0.1:8003`

Rekomendasi setting:

- Standard Flow: enabled
- PKCE: required
- Brute force protection: enabled
- Email verification: enabled

## 3) Siapkan Database

Buat database MySQL untuk masing-masing service (nama boleh disesuaikan):

```sql
CREATE DATABASE fintrack;
CREATE DATABASE finlyzer;
CREATE DATABASE fingoals;
```

## 4) Setup FinTrack (Service A)

```bash
cd FinTrack
cp .env.example .env
composer install
npm install
php artisan key:generate
```

Edit `.env` minimal:

```env
APP_URL=http://127.0.0.1:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fintrack
DB_USERNAME=root
DB_PASSWORD=your_password

AUTH_MODE=oidc
KEYCLOAK_ENABLED=true
KEYCLOAK_BASE_URL=http://127.0.0.1:8080
KEYCLOAK_REALM=fintech
KEYCLOAK_ISSUER=http://127.0.0.1:8080/realms/fintech
KEYCLOAK_CLIENT_ID=fintrack-web
KEYCLOAK_CLIENT_SECRET=your_fintrack_client_secret
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8001/auth/oidc/callback
KEYCLOAK_POST_LOGOUT_REDIRECT_URI=http://127.0.0.1:8001

ANALYZER_SERVICE_URL=http://127.0.0.1:8002/api/internal/analyze
PLANNER_SERVICE_URL=http://127.0.0.1:8003/api/internal/plan
```

Lanjutkan:

```bash
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8001
```

## 5) Setup FinLyzer (Service B)

```bash
cd FinLyzer
cp .env.example .env
composer install
npm install
php artisan key:generate
```

Edit `.env` minimal:

```env
APP_URL=http://127.0.0.1:8002

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finlyzer
DB_USERNAME=root
DB_PASSWORD=your_password

AUTH_MODE=oidc
KEYCLOAK_ENABLED=true
KEYCLOAK_BASE_URL=http://127.0.0.1:8080
KEYCLOAK_REALM=fintech
KEYCLOAK_ISSUER=http://127.0.0.1:8080/realms/fintech
KEYCLOAK_CLIENT_ID=finlyzer-web
KEYCLOAK_CLIENT_SECRET=your_finlyzer_client_secret
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8002/auth/oidc/callback
KEYCLOAK_POST_LOGOUT_REDIRECT_URI=http://127.0.0.1:8002

FINTRACK_FEED_BASE_URL=http://127.0.0.1:8001
```

Lanjutkan:

```bash
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8002
```

## 6) Setup FinGoals (Service C)

```bash
cd FinGoals
cp .env.example .env
composer install
npm install
php artisan key:generate
```

Edit `.env` minimal:

```env
APP_URL=http://127.0.0.1:8003

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fingoals
DB_USERNAME=root
DB_PASSWORD=your_password

AUTH_MODE=oidc
KEYCLOAK_ENABLED=true
KEYCLOAK_BASE_URL=http://127.0.0.1:8080
KEYCLOAK_REALM=fintech
KEYCLOAK_ISSUER=http://127.0.0.1:8080/realms/fintech
KEYCLOAK_CLIENT_ID=fingoals-web
KEYCLOAK_CLIENT_SECRET=your_fingoals_client_secret
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8003/auth/oidc/callback
KEYCLOAK_POST_LOGOUT_REDIRECT_URI=http://127.0.0.1:8003
```

Lanjutkan:

```bash
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8003
```

## 7) Sinkronkan API Key Antar Service

Supaya internal endpoint tidak 401, samakan nilai kunci berikut:

1. A memanggil B (internal analyze)

- FinTrack: `ANALYZER_SERVICE_API_KEY`
- FinLyzer: `ANALYZER_API_KEY`

2. B pull feed dari A

- FinTrack: `SERVICE2_PULL_API_KEY`
- FinLyzer: `FINTRACK_FEED_API_KEY`

3. A memanggil C (internal plan)

- FinTrack: `PLANNER_SERVICE_API_KEY`
- FinGoals: `INVESTMENT_PLANNER_API_KEY`

4. C callback ke A (jika flow callback aktif)

- FinTrack: `SERVICE3_CALLBACK_API_KEY`
- Request callback C ke A kirim header `x-api-key` dengan nilai yang sama

Tips dev:

- Boleh gunakan satu nilai global via `INTER_SERVICE_API_KEY` di FinTrack, lalu override bila perlu.

## 8) Jalankan Frontend Asset (Opsional)

Jika butuh hot reload Vite di masing-masing service:

```bash
npm run dev
```

Atau untuk build statis:

```bash
npm run build
```

## 9) Smoke Test Cepat

1. Cek aplikasi terbuka:

- http://127.0.0.1:8001
- http://127.0.0.1:8002
- http://127.0.0.1:8003

2. Cek discovery Keycloak:

```bash
curl -sf http://127.0.0.1:8080/realms/fintech/.well-known/openid-configuration | jq '.issuer'
```

3. Cek OIDC redirect endpoint:

- http://127.0.0.1:8001/auth/oidc/redirect
- http://127.0.0.1:8002/auth/oidc/redirect
- http://127.0.0.1:8003/auth/oidc/redirect

4. Jalankan test OIDC callback (opsional):

```bash
cd FinTrack && php artisan test --filter OidcCallbackValidationTest
cd FinLyzer && php artisan test --filter OidcCallbackValidationTest
cd FinGoals && php artisan test --filter OidcCallbackValidationTest
```

## 10) Troubleshooting Umum

- Error `State OIDC tidak valid`
  - Pastikan callback dipanggil dari flow redirect resmi, bukan URL callback langsung.

- Error `issuer invalid`
  - Pastikan `KEYCLOAK_ISSUER` di `.env` sama dengan issuer realm (`http://127.0.0.1:8080/realms/fintech`).

- Error `Invalid API key`
  - Cek pasangan variabel antar service pada bagian Sinkronkan API Key Antar Service.

- 502 atau timeout antar service
  - Cek service tujuan sudah hidup dan URL internal di `.env` benar.

## Referensi Dokumen

- `docs/PHASE1_CROSS_SERVICE_CONTRACT.md`
- `docs/PHASE2_8_9_KEYCLOAK_ROLLOUT_RUNBOOK.md`
- `docs/VERIFICATION_GATES_CHECKLIST.md`
