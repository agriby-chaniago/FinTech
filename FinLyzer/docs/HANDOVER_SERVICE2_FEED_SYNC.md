# Handover Integrasi Service 2 ke Service 1

Update: 2026-04-13
Audience: Tim Service 2
Tujuan: Sinkron transaksi incremental dari Service 1, analisis di Service 2, lalu push hasil ke Service 3.

## 1) Scope Wajib di Service 2

1. Pull data per user ke Service 1.
2. Endpoint pull:
   GET /api/service2/users/{userId}/transactions-feed
3. Header wajib:
   x-api-key: KEY_SERVICE2
4. Initial sync:
   Panggil tanpa parameter since.
5. Delta sync:
   Pakai since dari meta.next_since response terakhir.
6. Simpan cursor per user:
   next_since wajib dipersist per user agar incremental berjalan.
7. Pagination stream:
   Jika meta.has_more = true, lanjut pull lagi sampai false.
8. Upsert transaksi:
   Upsert by transaction id, hindari insert buta.
9. Setelah data siap:
   Jalankan analisis di Service 2, lalu push hasil ke Service 3.
10. Error handling minimum:
   - 401: stop sync dan alert (API key salah/expired)
   - 400: log bug request (payload/query tidak valid)
   - 404: user tidak ditemukan, skip user
   - 5xx: retry dengan exponential backoff

## 2) Data yang Harus Diberikan ke Tim Service 2

1. Base URL Service 1:
   - DEV: http://127.0.0.1:8000
   - STAGING: <isi dari tim infra>
   - PROD: <isi dari tim infra>
2. Endpoint final:
   /api/service2/users/{userId}/transactions-feed
3. API key khusus Service 2:
   Distribusikan via secret manager/secure channel. Jangan hardcode di repo publik.
4. Daftar user pull:
   Berikan list user_id aktif atau aturan mapping user yang berlaku.
5. Kontrak query params:
   - since: opsional, format ISO-8601
   - include_summary: opsional, nilai 0 atau 1
6. Kontrak response:
   success, message, data.transactions, data.summary (opsional), meta
7. Kontrak error code:
   400, 401, 404, 500
8. Aturan polling:
   rekomendasi 5-15 detik per user, atau scheduler internal/event-driven.
9. Batas data per call saat ini:
   max_items default 1000 (ikut meta dari Service 1).
10. Paket contoh siap pakai:
   Postman collection + langkah run.

## 3) Contoh Request

Initial sync:

curl --location --request GET "{{base_url}}/api/service2/users/2/transactions-feed" \
  --header "x-api-key: {{api_key}}"

Delta sync:

curl --location --request GET "{{base_url}}/api/service2/users/2/transactions-feed?since=2026-04-13T07:48:43+00:00" \
  --header "x-api-key: {{api_key}}"

Tanpa summary (lebih ringan):

curl --location --request GET "{{base_url}}/api/service2/users/2/transactions-feed?include_summary=0" \
  --header "x-api-key: {{api_key}}"

## 4) Kontrak Response

Contoh response sukses:

{
  "success": true,
  "message": "Transactions feed retrieved.",
  "data": {
    "transactions": [
      {
        "id": "trx_1001",
        "user_id": 2,
        "amount": 50000,
        "category": "food",
        "type": "expense",
        "occurred_at": "2026-04-13T07:45:00+00:00",
        "updated_at": "2026-04-13T07:48:43+00:00"
      }
    ],
    "summary": {
      "total_income": 0,
      "total_expense": 50000
    }
  },
  "meta": {
    "next_since": "2026-04-13T07:48:43+00:00",
    "has_more": false,
    "max_items": 1000,
    "count": 1
  }
}

Contoh response error:

401 Unauthorized
{
  "success": false,
  "message": "Unauthorized"
}

400 Bad Request
{
  "success": false,
  "message": "Invalid request parameter"
}

404 Not Found
{
  "success": false,
  "message": "User not found"
}

500 Internal Server Error
{
  "success": false,
  "message": "Internal server error"
}

## 5) Algoritma Sinkronisasi yang Direkomendasikan

Pseudo-flow per user:

load cursor_since from sync_state[user_id]
if cursor_since empty:
  request without since (initial snapshot)
else:
  request with since=cursor_since

repeat:
  call feed endpoint
  if 401: alert + stop user
  if 400: log bug + stop user
  if 404: log skip user + stop user
  if 5xx: retry with backoff, max N attempts

  upsert all transactions by transaction_id
  process analysis in Service 2
  push analysis result to Service 3

  save meta.next_since to sync_state[user_id]
  set cursor_since = meta.next_since
until meta.has_more == false

## 6) Postman Steps (Runner)

1. Import collection:
   docs/postman/Service1-Service2-FeedSync.postman_collection.json
2. Set variables di Collection Variables:
   - base_url
   - api_key
   - user_id
   - since (boleh kosong untuk initial sync)
3. Jalankan request Initial Sync.
4. Lihat Tests otomatis menyimpan:
   - next_since
   - has_more
5. Untuk delta, jalankan Delta Sync.
6. Untuk loop paging, jalankan request Delta Loop (has_more).

## 7) Catatan Keamanan

1. API key harus disimpan di secret manager.
2. Jangan commit key ke git.
3. Rotasi key berkala dan monitor 401 spikes.
