# SQL Files Guide

Folder ini berisi dua jenis file SQL:

1. `financial_analyzer_service_schema.sql`
   - Struktur tabel saja (tanpa data).
   - Cocok untuk setup awal yang bersih.

2. `financial_analyzer_service.sql`
   - Dump dari phpMyAdmin (struktur + data pada saat export).
   - Cocok jika ingin restore kondisi data seperti saat backup.

## Rekomendasi Pakai

1. Dev/test baru: pakai `financial_analyzer_service_schema.sql` atau `php artisan migrate`.
2. Restore snapshot lengkap: pakai `financial_analyzer_service.sql`.

## Contoh Import MySQL

Import schema-only:

```bash
mysql -u root -p financial_analyzer_service < database/sql/financial_analyzer_service_schema.sql
```

Import full dump (with data):

```bash
mysql -u root -p financial_analyzer_service < database/sql/financial_analyzer_service.sql
```
