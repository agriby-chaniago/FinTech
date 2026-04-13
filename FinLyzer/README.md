# Financial Analyzer Service

Microservice Laravel untuk analisis transaksi keuangan dengan AI insight (Groq).

## Ringkas Fitur

- Endpoint analisis: `POST /api/analyze`
- Endpoint analisis otomatis dari FinTrack feed: `POST /api/analyze/auto`
- Endpoint auto run tanpa body: `POST /api/analyze/auto/run`
- Endpoint ambil hasil terbaru untuk Service C: `GET /api/analyze/auto/latest`
- Hitung metrik utama: `total_income`, `total_expense`, `transaction_count`, `top_category`
- Breakdown persentase kategori
- Simpan hasil ke tabel:
  - `analysis_reports`
  - `category_breakdowns`
  - `ai_insights`
- Proteksi API key via middleware

## Quick Start

1. Install dependency

```bash
composer install
```

2. Siapkan environment

```bash
copy .env.example .env
```

3. Pastikan nilai penting di `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=financial_analyzer_service
DB_USERNAME=root
DB_PASSWORD=

ANALYZER_API_KEY=fintrack1
ANALYZER_API_KEY_HEADERS=x-api-key,service2_api_key

FINTRACK_FEED_BASE_URL=http://127.0.0.1:8001
FINTRACK_FEED_PATH=/api/service2/users/{user_id}/transactions-feed
FINTRACK_FEED_API_KEY=fintrack1
FINTRACK_FEED_API_KEY_HEADER=x-api-key
FINTRACK_FEED_TIMEOUT=20
FINTRACK_FEED_RETRY_TIMES=2
FINTRACK_FEED_RETRY_SLEEP_MS=300
FINTRACK_FEED_DEFAULT_USER_ID=2
FINTRACK_FEED_USE_SAVED_SINCE=true
FINTRACK_FEED_SINCE_CACHE_PREFIX=fintrack_feed_since_user_
FINTRACK_FEED_AUTO_SCHEDULE_ENABLED=false
FINTRACK_FEED_AUTO_SCHEDULE_CRON=*/5 * * * *

GROQ_API_KEY=your_groq_api_key
GROQ_MODEL=llama-3.1-8b-instant
GROQ_BASE_URL=https://api.groq.com/openai/v1
GROQ_TIMEOUT=15
```

4. Jalankan migrasi dan server

```bash
php artisan migrate
php artisan serve
```

## Cara Pakai Endpoint

URL:

```text
POST /api/analyze
```

Header (pilih salah satu):

```http
x-api-key: fintrack1
```

atau

```http
service2_api_key: fintrack1
```

Request body:

```json
{
  "user_id": 1,
  "transactions": [
    {
      "amount": 50000,
      "category": "food",
      "type": "expense"
    },
    {
      "amount": 3000000,
      "category": "salary",
      "type": "income"
    }
  ]
}
```

Response body:

```json
{
  "total_income": 3000000,
  "total_expense": 50000,
  "transaction_count": 2,
  "top_category": "food",
  "category_breakdown": {
    "food": 100
  },
  "insight": "pengeluaran makanan terlalu tinggi"
}
```

## Cara Pakai Analisis Otomatis (Dari FinTrack1)

URL:

```text
POST /api/analyze/auto
```

Header:

```http
x-api-key: fintrack1
Content-Type: application/json
```

Request body:

```json
{
  "user_id": 2,
  "since": "2026-04-13T10:00:00Z"
}
```

`since` opsional. Jika diisi, service akan mengambil delta feed terbaru dari Project A.

Jika `since` tidak diisi dan `FINTRACK_FEED_USE_SAVED_SINCE=true`, service otomatis memakai since terakhir yang tersimpan.

Contoh response sukses:

```json
{
  "message": "Analisis otomatis berhasil.",
  "source": {
    "user_id": 2,
    "fetched_transactions": 3,
    "since_used": "2026-04-13T10:00:00Z",
    "next_since": "2026-04-13T11:00:00Z"
  },
  "analysis": {
    "total_income": 3000000,
    "total_expense": 50000,
    "transaction_count": 3,
    "top_category": "food",
    "category_breakdown": {
      "food": 100
    },
    "insight": "..."
  }
}
```

## Auto Run Paling Praktis (API Key Only)

Jika ingin jalan otomatis tanpa kirim body, gunakan endpoint ini:

```text
POST /api/analyze/auto/run
```

Header:

```http
x-api-key: fintrack1
```

Mode ini otomatis:

1. Pakai `FINTRACK_FEED_DEFAULT_USER_ID` (default `2`)
2. Pakai `since` terakhir yang tersimpan (jika ada)
3. Mengambil transaksi terbaru dari feed Project A
4. Menyimpan `next_since` untuk run berikutnya

## Penggunaan Dashboard Multi User (UI)

Dashboard sekarang fokus untuk penggunaan otomatis dan multi user.

Langkah pakai:

1. Isi `API Key`.
2. Isi daftar user ID (misal: `2,3,5`).
3. Klik `Run Batch Multi User`.
4. Pilih user pada panel hasil untuk melihat:
  - source sync
  - metrik utama
  - insight AI
  - payload siap kirim ke Service C

Metrik tambahan yang ditampilkan:

- `net_balance` (arus kas bersih)
- `savings_rate` (rasio tabungan)
- `financial_health` (status keuangan)
- `summary` (ringkasan analisis bahasa sederhana)

## Endpoint Pull Hasil Terbaru (Untuk Service C)

Service C dapat mengambil hasil analisis terbaru (per user) dari endpoint ini:

```text
GET /api/analyze/auto/latest?user_id=2
```

Header:

```http
x-api-key: fintrack1
```

Contoh response:

```json
{
  "message": "Payload terbaru untuk Service C berhasil diambil.",
  "data": {
    "run_id": 12,
    "executed_at": "2026-04-13T11:15:42+00:00",
    "source_sync": {
      "user_id": 2,
      "fetched_transactions": 8,
      "next_since": "SYNC_TOKEN_2"
    },
    "metrics": {
      "total_income": 5000000,
      "total_expense": 1200000,
      "transaction_count": 8,
      "top_category": "rent"
    },
    "category_breakdown": [
      {
        "category": "rent",
        "amount": 900000,
        "percentage": 75
      }
    ],
    "ai_insight": {
      "provider": "groq",
      "model": "llama-3.1-8b-instant",
      "text": "..."
    }
  }
}
```

## Otomasi via Command (Tanpa Body Request)

Untuk menjalankan auto-pull + analisis langsung dari server:

```bash
php artisan fintrack:auto-analyze
```

Opsi penting:

```bash
php artisan fintrack:auto-analyze --user_id=2
php artisan fintrack:auto-analyze --since="2026-04-13T10:00:00Z"
php artisan fintrack:auto-analyze --include_summary
php artisan fintrack:auto-analyze --no-saved-since
```

## Scheduler (Otomatis Berkala)

Aktifkan di `.env`:

```env
FINTRACK_FEED_AUTO_SCHEDULE_ENABLED=true
FINTRACK_FEED_AUTO_SCHEDULE_CRON=*/5 * * * *
```

Lalu jalankan scheduler worker:

```bash
php artisan schedule:work
```

Untuk server production, jalankan cron sistem setiap menit ke `php artisan schedule:run`.

## Integrasi Cepat Project A

Environment Project A:

```env
FINANCIAL_ANALYZER_URL=http://127.0.0.1:8000
service2_api_key=fintrack1
```

Contoh Laravel:

```php
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'x-api-key' => env('service2_api_key'),
])->post(env('FINANCIAL_ANALYZER_URL').'/api/analyze', [
    'user_id' => $userId,
    'transactions' => $transactions,
]);

$analysis = $response->throw()->json();
```

## File Penting untuk Maintenance

- `app/Http/Requests/AnalyzeRequest.php`
- `app/Services/FinancialAnalysisService.php`
- `app/Services/GroqInsightService.php`
- `app/Http/Middleware/ValidateApiKey.php`
- `tests/Feature/AnalyzeApiTest.php`

## Test

```bash
php artisan test
```

## Dokumen Handover Service 2

- Handover 1 halaman: `docs/HANDOVER_SERVICE2_FEED_SYNC.md`
- Postman collection: `docs/postman/Service1-Service2-FeedSync.postman_collection.json`

## SQL Files

- Panduan SQL: `database/sql/README.md`
- Schema only: `database/sql/financial_analyzer_service_schema.sql`
- Full dump (phpMyAdmin, with data): `database/sql/financial_analyzer_service.sql`
