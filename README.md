# Simple Note Taking

Aplikasi web untuk mengumpulkan catatan berdasarkan kategori, lengkap dengan
attachment dan link share publik berformat slug. Dibangun di atas
**Laravel 13** dan **MariaDB 11** dengan postur keamanan yang ditargetkan untuk
A+ pada [securityheaders.com](https://securityheaders.com) /
[Qualys SSL Labs](https://www.ssllabs.com/) serta selaras dengan praktik
[OWASP ASVS](https://owasp.org/www-project-application-security-verification-standard/)
dan [OWASP Secure Headers Project](https://owasp.org/www-project-secure-headers/).

## Fitur

- **Catatan** — judul, tanggal otomatis, isi rich-text yang disanitasi,
  thumbnail otomatis di-resize proporsional ke ≤1200×1200 + dikonversi ke WebP,
  URL referensi opsional, lampiran multi-file (klik tombol **+ Tambah Lampiran**)
  hanya untuk ekstensi `.txt`, `.pdf`, `.jpg`, `.png`, `.jpeg`, `.xlsx`, `.docx`.
- **Kategori** — CRUD penuh (per user, slug dijamin unik).
- **User** — CRUD admin, role `admin`/`user`.
- **Profil** — user dapat ganti foto profil (di-resize ke 256×256 WebP) dan
  password (rule: min 12 char, mixed case + angka + simbol + uncompromised).
- **Captcha login** — 8 karakter alfanumerik kombinasi huruf besar/kecil + angka,
  digenerate server-side, hash disimpan di session (single-use, TTL 5 menit).
- **Share publik** — tiap catatan punya link `/{id}-{slug}.html` yang dapat
  diproteksi dengan password (di-hash bcrypt) dan/atau tanggal kedaluwarsa.

## Hardening keamanan

| Area | Implementasi |
|---|---|
| Headers | `Strict-Transport-Security` (2 tahun, `includeSubDomains; preload`), CSP yang ketat (`default-src 'self'`, `frame-ancestors 'none'`, `object-src 'none'`, `upgrade-insecure-requests`, …), `X-Content-Type-Options`, `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP/COEP, `X-Permitted-Cross-Domain-Policies: none`, `X-XSS-Protection: 0`. Header `Server` & `X-Powered-By` dihapus. Lihat <code>app/Http/Middleware/SecurityHeaders.php</code>. |
| CSRF | Bawaan Laravel, dipakai di semua form. Cookie session: `Secure`, `HttpOnly`, `SameSite=Strict`, terenkripsi. |
| XSS | Output Blade ter-escape default; isi catatan dibersihkan via [`mews/purifier`](https://github.com/mewebstudio/Purifier) (HTMLPurifier) sebelum disimpan. |
| Injection | Eloquent + parameter binding di mana-mana; tidak ada raw SQL terhadap input user. Pencarian `LIKE` meng-escape `%`/`_`. |
| Auth | Bcrypt 12 rounds, login throttling per email+IP (5 attempts → 60 s lock-out via `RateLimiter`), regenerasi session ID setelah login, captcha single-use. |
| File upload | Allow-list per ekstensi **dan** tipe MIME aktual (`getimagesize` untuk gambar) di <code>app/Support/FileSecurity.php</code>. Lampiran disimpan di disk `local` (di luar `public/`) — diakses lewat controller dengan `Content-Disposition: attachment` dan `X-Content-Type-Options: nosniff`. |
| Image processing | [Intervention Image](https://image.intervention.io/v3) v4 mendekode ulang lalu menulis WebP — strip metadata, prevent polyglot. |
| Password rule | `min:12`, mixed case, angka, simbol, dan `uncompromised()` (HIBP k-anonymity). |
| Slug share link | Pattern regex ketat (`[0-9]+`, `[A-Za-z0-9\-]+`); brute force unlock di-throttle (8/menit). |
| Session | `SESSION_ENCRYPT=true`, lifetime 60 menit. |
| Logging | `LOG_LEVEL=warning` di production agar PII tidak bocor ke log. |

## Persyaratan

- PHP 8.3+ dengan ekstensi: `pdo_mysql`, `gd`, `mbstring`, `xml`, `intl`, `zip`, `bcmath`.
- MariaDB 11.
- Composer 2, Node 20+/npm 10+.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate

# Buat database & user di MariaDB:
#   CREATE DATABASE simple_not_taking CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
#   CREATE USER 'simple_not_taking'@'localhost' IDENTIFIED BY 'change-me';
#   GRANT ALL ON simple_not_taking.* TO 'simple_not_taking'@'localhost';

php artisan migrate --seed
php artisan storage:link
npm ci && npm run build
```

User admin default (ganti segera setelah login pertama):

- email: `admin@example.com`
- password: `ChangeMeNow!2026`

## Menjalankan

```bash
# Development
php artisan serve

# Production (di belakang TLS-terminating reverse proxy seperti nginx + certbot)
APP_ENV=production APP_DEBUG=false php artisan optimize
```

Untuk grade A+ securityheaders.com: pastikan deploy di belakang HTTPS dengan
sertifikat valid, set `APP_URL=https://...`, dan `FORCE_HTTPS=true`. Header
`Strict-Transport-Security` baru dihormati browser bila response benar-benar
disajikan via HTTPS.

## Testing

```bash
./vendor/bin/pint        # style
php artisan test         # unit + feature
```

## Catatan tentang versi Laravel

Spesifikasi awal meminta Laravel 13. Saat repository ini dibuat, Laravel 13
memang sudah dirilis (lihat `composer.json` → `"laravel/framework": "^13.0"`)
dan menjadi requirement minimum proyek ini.

## Favicon

`public/favicon.svg` adalah artwork notebook lucu yang dirilis oleh penulis
proyek di bawah lisensi
[CC0 1.0](https://creativecommons.org/publicdomain/zero/1.0/) (public domain) —
**bebas untuk tujuan komersil**.

## Lisensi

MIT (mengikuti skeleton Laravel) — kecuali `public/favicon.svg` yang CC0.
