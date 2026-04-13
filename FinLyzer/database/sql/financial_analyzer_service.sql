-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 13 Apr 2026 pada 14.07
-- Versi server: 8.0.30
-- Versi PHP: 8.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `financial_analyzer_service`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_insights`
--

CREATE TABLE `ai_insights` (
  `id` bigint UNSIGNED NOT NULL,
  `analysis_report_id` bigint UNSIGNED NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'groq',
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt` text COLLATE utf8mb4_unicode_ci,
  `insight` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `ai_insights`
--

INSERT INTO `ai_insights` (`id`, `analysis_report_id`, `provider`, `model`, `prompt`, `insight`, `created_at`, `updated_at`) VALUES
(1, 1, 'groq', NULL, 'Berdasarkan data keuangan berikut, berikan insight singkat dan saran: {\"user_id\":1,\"total_income\":3000000,\"total_expense\":50000,\"transaction_count\":2,\"top_category\":\"food\",\"category_breakdown\":{\"food\":100}}', 'GROQ_API_KEY belum dikonfigurasi. Tambahkan API key untuk mendapatkan insight AI dari Groq.', '2026-04-13 01:02:16', '2026-04-13 01:02:16'),
(2, 2, 'groq', 'llama-3.1-8b-instant', 'Berdasarkan data keuangan berikut, berikan insight singkat dan saran: {\"user_id\":1,\"total_income\":3000000,\"total_expense\":50000,\"transaction_count\":2,\"top_category\":\"food\",\"category_breakdown\":{\"food\":100}}', '**Insight Singkat:**\n\nBerikut adalah beberapa insight yang dapat ditarik dari data keuangan:\n\n* Total pendapatan pengguna adalah Rp 3.000.000.\n* Total pengeluaran pengguna adalah Rp 50.000.\n* Jumlah transaksi yang dilakukan oleh pengguna adalah 2.\n* Kategori pengeluaran terbesar adalah \"makanan\" (100% dari total pengeluaran).\n\n**Saran:**\n\nBerikut adalah beberapa saran berdasarkan data keuangan:\n\n1. **Perluasan pengeluaran**: Dengan total pendapatan Rp 3.000.000 dan total pengeluaran Rp 50.000, pengguna memiliki ruang untuk meningkatkan pengeluaran pada kategori lainnya.\n2. **Optimasi pengeluaran**: Pengguna dapat mempertimbangkan untuk mengalokasikan pengeluaran pada kategori lainnya, seperti \"transportasi\", \"hiburan\", atau \"keuangan\".\n3.', '2026-04-13 01:27:51', '2026-04-13 01:27:51'),
(3, 3, 'groq', 'llama-3.1-8b-instant', 'Berdasarkan data keuangan berikut, berikan insight singkat dan saran: {\"user_id\":1,\"total_income\":3000000,\"total_expense\":50000,\"transaction_count\":2,\"top_category\":\"food\",\"category_breakdown\":{\"food\":100}}', '**Insight Singkat:**\n\nDari data keuangan yang diberikan, kita dapat menyimpulkan bahwa:\n\n* Pengguna memiliki pendapatan total sebesar Rp 3.000.000.\n* Pengguna memiliki pengeluaran total sebesar Rp 50.000.\n* Pengguna melakukan 2 transaksi.\n* Kategori pengeluaran terbesar adalah \"Makanan\" (100% dari total pengeluaran).\n\n**Saran:**\n\nDengan melihat data keuangan yang ada, saya merekomendasikan beberapa hal berikut:\n\n1. **Perluasan pengeluaran**: Meskipun pengguna memiliki pendapatan yang cukup besar, pengeluaran yang sangat rendah mungkin tidak mencerminkan gaya hidup yang seimbang. Mungkin pengguna perlu mempertimbangkan untuk meningkatkan pengeluaran untuk kebutuhan lainnya, seperti pengeluaran untuk pendidikan, kesehatan, atau investasi.\n2. **P', '2026-04-13 01:27:55', '2026-04-13 01:27:55'),
(4, 4, 'groq', 'llama-3.1-8b-instant', 'Berdasarkan data keuangan berikut, berikan insight singkat dan saran: {\"user_id\":1,\"total_income\":3000000,\"total_expense\":50000,\"transaction_count\":2,\"top_category\":\"food\",\"category_breakdown\":{\"food\":100}}', 'Berikut adalah insight singkat dan saran berdasarkan data keuangan yang diberikan:\n\n**Insight Singkat:**\n\n* Pengguna memiliki total pendapatan sebesar Rp 3.000.000.\n* Pengguna memiliki total pengeluaran sebesar Rp 50.000.\n* Pengguna melakukan 2 transaksi.\n* Kategori pengeluaran terbesar adalah \"makanan\" (food) sebesar Rp 100.\n\n**Saran:**\n\n* Perluasan pengeluaran: Meskipun pengguna memiliki total pendapatan yang besar, pengeluaran yang sangat rendah dapat menunjukkan bahwa pengguna belum melakukan pengeluaran yang signifikan. Oleh karena itu, perluasan pengeluaran dapat menjadi prioritas untuk meningkatkan keseimbangan keuangan.\n* Pengeluaran pada kategori \"makanan\": Karena kategori \"makanan\" merupakan kategori pengeluaran terbesar, pengguna dapat mempertimb', '2026-04-13 05:46:56', '2026-04-13 05:46:56'),
(5, 5, 'groq', 'llama-3.1-8b-instant', 'Berdasarkan data keuangan berikut, berikan insight singkat dan saran: {\"user_id\":1,\"total_income\":3000000,\"total_expense\":50000,\"transaction_count\":2,\"top_category\":\"food\",\"category_breakdown\":{\"food\":100}}', 'Berikut adalah insight singkat dan saran berdasarkan data keuangan yang diberikan:\n\n**Insight Singkat:**\n\n* Pengguna memiliki total pendapatan sebesar Rp 3.000.000.\n* Pengguna memiliki total pengeluaran sebesar Rp 50.000.\n* Pengguna melakukan 2 transaksi.\n* Kategori pengeluaran terbesar adalah \"makanan\" (100% dari total pengeluaran).\n\n**Saran:**\n\n* Perluasan pengeluaran yang signifikan dibandingkan dengan pendapatan. Saran untuk menghemat pengeluaran dan meningkatkan pendapatan.\n* Kategori \"makanan\" terlalu besar. Saran untuk membagi pengeluaran ke kategori lain seperti \"transportasi\", \"hiburan\", dll.\n* Perluasan transaksi yang rendah. Saran untuk meningkatkan frekuensi transaksi dan meningkatkan pendapatan.\n\n**Actionable:**\n\n* Buat renc', '2026-04-13 06:04:20', '2026-04-13 06:04:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `analysis_reports`
--

CREATE TABLE `analysis_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `total_income` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_expense` decimal(15,2) NOT NULL DEFAULT '0.00',
  `transaction_count` int UNSIGNED NOT NULL DEFAULT '0',
  `top_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_transactions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `analysis_reports`
--

INSERT INTO `analysis_reports` (`id`, `user_id`, `total_income`, `total_expense`, `transaction_count`, `top_category`, `raw_transactions`, `created_at`, `updated_at`) VALUES
(1, 1, 3000000.00, 50000.00, 2, 'food', '[{\"type\": \"expense\", \"amount\": 50000, \"category\": \"food\"}, {\"type\": \"income\", \"amount\": 3000000, \"category\": \"salary\"}]', '2026-04-13 01:02:16', '2026-04-13 01:02:16'),
(2, 1, 3000000.00, 50000.00, 2, 'food', '[{\"type\": \"income\", \"amount\": 3000000, \"category\": \"salary\"}, {\"type\": \"expense\", \"amount\": 50000, \"category\": \"food\"}]', '2026-04-13 01:27:48', '2026-04-13 01:27:48'),
(3, 1, 3000000.00, 50000.00, 2, 'food', '[{\"type\": \"income\", \"amount\": 3000000, \"category\": \"salary\"}, {\"type\": \"expense\", \"amount\": 50000, \"category\": \"food\"}]', '2026-04-13 01:27:53', '2026-04-13 01:27:53'),
(4, 1, 3000000.00, 50000.00, 2, 'food', '[{\"type\": \"income\", \"amount\": 3000000, \"category\": \"salary\"}, {\"type\": \"expense\", \"amount\": 50000, \"category\": \"food\"}]', '2026-04-13 05:46:55', '2026-04-13 05:46:55'),
(5, 1, 3000000.00, 50000.00, 2, 'food', '[{\"type\": \"income\", \"amount\": 3000000, \"category\": \"salary\"}, {\"type\": \"expense\", \"amount\": 50000, \"category\": \"food\"}]', '2026-04-13 06:04:18', '2026-04-13 06:04:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `category_breakdowns`
--

CREATE TABLE `category_breakdowns` (
  `id` bigint UNSIGNED NOT NULL,
  `analysis_report_id` bigint UNSIGNED NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `category_breakdowns`
--

INSERT INTO `category_breakdowns` (`id`, `analysis_report_id`, `category`, `amount`, `percentage`, `created_at`, `updated_at`) VALUES
(1, 1, 'food', 50000.00, 100.00, '2026-04-13 01:02:16', '2026-04-13 01:02:16'),
(2, 2, 'food', 50000.00, 100.00, '2026-04-13 01:27:48', '2026-04-13 01:27:48'),
(3, 3, 'food', 50000.00, 100.00, '2026-04-13 01:27:53', '2026-04-13 01:27:53'),
(4, 4, 'food', 50000.00, 100.00, '2026-04-13 05:46:55', '2026-04-13 05:46:55'),
(5, 5, 'food', 50000.00, 100.00, '2026-04-13 06:04:18', '2026-04-13 06:04:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_13_000100_create_analysis_reports_table', 1),
(5, '2026_04_13_000101_create_category_breakdowns_table', 1),
(6, '2026_04_13_000102_create_ai_insights_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3WioplCA3XvHNPcAPOM4RAStoNVmrlNATEoJJaKH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ2a3pMQUhtWVBKRjlKWUpRcDlYWFlHWVZCQndpdGY4Vkh6Y3ByWWxwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776070055),
('DeLMRgUjNjInEX7rgIupR7FZv8p7TvEmEPYhY5dR', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIxc2tUTmZCNnl6TG1CWXZQd0NJSFp5bVQ4OEhkTDJmV1pDRzVFeFB4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776087602),
('EhVJS7cmu5uCUpuAt89BttG1JpIy6rw2rhhwdT0q', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJCZmpwNVpBdXluTmxPRE43c2JLNENDWGZpeDZZN2I1cXRBQmY2b3FwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776087131),
('NQmYCr6QwYJamBvH0hdmzQ586rqXEDF43kB6ecv0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJVMVZzamFzNVZYTTRlNnF3eGZxNlo0Zk81UEtHUENVVmk5eXpIOVZGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776066813),
('wYCc6rfejuXsMGWxCcwmdYR4rid0wQ17RZCTogxl', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; id-ID) WindowsPowerShell/5.1.19041.5912', 'eyJfdG9rZW4iOiJhSkdtRlh4czZSaXZUTW1nd3Z6bE5TdVVqT1M0NFhtRmFqQUk0S0ZDIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776066552);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `ai_insights`
--
ALTER TABLE `ai_insights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_insights_analysis_report_id_foreign` (`analysis_report_id`);

--
-- Indeks untuk tabel `analysis_reports`
--
ALTER TABLE `analysis_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `analysis_reports_user_id_index` (`user_id`);

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indeks untuk tabel `category_breakdowns`
--
ALTER TABLE `category_breakdowns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_breakdowns_analysis_report_id_foreign` (`analysis_report_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `ai_insights`
--
ALTER TABLE `ai_insights`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `analysis_reports`
--
ALTER TABLE `analysis_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `category_breakdowns`
--
ALTER TABLE `category_breakdowns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `ai_insights`
--
ALTER TABLE `ai_insights`
  ADD CONSTRAINT `ai_insights_analysis_report_id_foreign` FOREIGN KEY (`analysis_report_id`) REFERENCES `analysis_reports` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `category_breakdowns`
--
ALTER TABLE `category_breakdowns`
  ADD CONSTRAINT `category_breakdowns_analysis_report_id_foreign` FOREIGN KEY (`analysis_report_id`) REFERENCES `analysis_reports` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
