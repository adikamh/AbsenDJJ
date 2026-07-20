# Dokumentasi Update & Analisis Database - AbsenDJJ

## 1. Analisis Hubungan ID Antar Tabel (Entity Relationship Analysis)

Berikut adalah struktur hubungan ID (Primary Key & Foreign Key) antar tabel pada basis data aplikasi **AbsenDJJ**:

### Diagram Skema Hubungan ID
```
  +---------------+        +-----------------+
  |     roles     |        |    instansi     |
  +---------------+        +-----------------+
  | PK: id        |        | PK: id          |
  +-------+-------+        +--------+--------+
          | 1                       | 1
          |                         |
          | N                       | N
  +-------v-------------------------v--------+
  |                 users                    |
  +------------------------------------------+
  | PK: id                                   |
  | Unique Code: user_code (Route Binding)   |
  | FK: role_id --------> roles.id           |
  | FK: instansi_id ----> instansi.id        |
  | FK: pembimbing_id -> users.id (Self-Ref) |
  +----+------------+------------+-----------+
       | 1          | 1          | 1
       |            |            |
       | N          | N          | N
+------v-------+ +--v---------+ +-v---------------+
| attendances  | |  logbooks  | | leave_requests  |
+--------------+ +------------+ +-----------------+
| PK: id       | | PK: id     | | PK: id          |
| FK: user_id  | | Code:      | | FK: user_id     |
|   ->users.id | | logbook_code|   -> users.id    |
+--------------+ | FK: user_id| +-----------------+
                 |   ->users.id|
                 +------------+
```

---

### Detail Hubungan & Constraint Constraint

#### A. Tabel `users` (Pusat Entitas Utama)
- **Primary Key**: `id` (BigInteger Auto-Increment)
- **Unique Public Code**: `user_code` (misal: `USR-00001` - digunakan untuk Route Model Binding agar memproteksi exposure ID internal database).
- **Foreign Keys**:
  1. `role_id` $\rightarrow$ Ref: `roles.id` (`ON DELETE RESTRICT`)
     - Memastikan role tidak bisa dihapus jika masih digunakan oleh user.
  2. `instansi_id` $\rightarrow$ Ref: `instansi.id` (`ON DELETE SET NULL`)
     - Jika instansi dihapus, data user tetap ada dan nilai `instansi_id` menjadi `NULL`.
  3. `pembimbing_id` $\rightarrow$ Ref: `users.id` (Self-referential, `ON DELETE SET NULL`)
     - Menghubungkan peserta magang dengan pembimbing lapangannya. Jika user pembimbing dihapus, status pembimbing peserta menjadi `NULL`.

#### B. Tabel Transaksi / Aktivitas (Milik User)
1. **`attendances`**
   - **Primary Key**: `id`
   - **Foreign Key**: `user_id` $\rightarrow$ Ref: `users.id` (`ON DELETE CASCADE`)
   - **Index**: Compound Index `(user_id, tanggal)` untuk mempercepat query pencarian presensi harian user.

2. **`logbooks`**
   - **Primary Key**: `id`
   - **Unique Public Code**: `logbook_code` (misal: `LB-00001`)
   - **Foreign Key**: `user_id` $\rightarrow$ Ref: `users.id` (`ON DELETE CASCADE`)
   - **Index**: Compound Index `(user_id, tanggal)`.

3. **`leave_requests`** (Pengajuan Izin/Sakit)
   - **Primary Key**: `id`
   - **Foreign Key**: `user_id` $\rightarrow$ Ref: `users.id` (`ON DELETE CASCADE`)
   - **Index**: Index `(user_id)`.

#### C. Tabel Pendukung & Konfigurasi
1. **`code_sequences`**
   - **Primary Key**: `id`
   - **Unique Key**: `key` (`user_code_seq`, `logbook_code_seq`)
   - Digunakan untuk pencacah otomatis kode unik publik (`user_code` dan `logbook_code`).

2. **`notifications`**
   - **Primary Key**: `id` (UUID)
   - **Polymorphic Relation**: `notifiable_id` & `notifiable_type` $\rightarrow$ terhubung ke model `User` (`users.id`).

3. **`work_schedules`** & **`office_locations`**
   - Tabel independen untuk menyimpan jam kerja dan koordinat lokasi kantor/geofencing.

---

## 2. Catatan Perubahan & Pembaruan Kode Program (Update Log)

| Tanggal | Komponen | Deskripsi Perubahan |
|---|---|---|
| 2026-07-20 | Server & Runtime | Pengujian & pemastian server lokal berjalan via `php artisan serve` (port 8000) dan Vite dev server (port 5173). |
| 2026-07-20 | Dokumentasi | Pembuatan dokumen `update.md` yang memuat hasil analisis relasi Foreign Key / ID antar tabel database serta acuan struktur data. |
| 2026-07-20 | Database MySQL | Mengubah konfigurasi koneksi di `.env` dan `.env.example` ke MySQL dengan nama database `absenmagang`. Menjalankan migrasi (`php artisan migrate`) dan seeder (`php artisan db:seed`). |
| 2026-07-20 | Enkripsi / Auth | Generasi enkripsi `APP_KEY` menggunakan `php artisan key:generate` dan pembersihan cache konfigurasi untuk mengatasi `MissingAppKeyException`. |
| 2026-07-20 | Routing & Model | Mengatasi `UrlGenerationException` pada route `super-admin.peserta.destroy` dengan melakukan backfill `user_code` pada tabel `users`, serta menambahkan fallback `getRouteKey()` & `resolveRouteBinding()` pada model `User`. |
| 2026-07-20 | Keamanan (Security) | Mengimplementasikan `SecurityHeadersMiddleware` secara global di `bootstrap/app.php` untuk mengirimkan header `Content-Security-Policy` (CSP) beserta HTTP security headers lainnya (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`). |
| 2026-07-20 | Keamanan (CORS) | Mengonfigurasi `config/cors.php` secara restriktif dengan membatasi `allowed_origins` ke domain resmi aplikasi (`APP_URL`, `http://127.0.0.1:8000`, `http://localhost:5173`) dan melarang wildcard (`*`) untuk mencegah ancaman cross-domain read request unauthenticated. |
| 2026-07-20 | Keamanan (Clickjacking) | Memastikan proteksi Anti-Clickjacking di seluruh aplikasi dengan menempatkan `SecurityHeadersMiddleware` di urutan pertama `$middleware->prepend(...)` pada `bootstrap/app.php`, mengirimkan header `X-Frame-Options: DENY` dan CSP directive `frame-ancestors 'none'`. |
| 2026-07-20 | Keamanan (SRI) | Menambahkan atribut Subresource Integrity (`integrity="sha384-..."`) dan `crossorigin="anonymous"` pada seluruh pustaka eksternal CDN (`SweetAlert2`, `ApexCharts`, `Chart.js`, `Flatpickr`) di seluruh template Blade. |
| 2026-07-20 | Keamanan (Redirect) | Menambahkan sanitasi otomatis pada `SecurityHeadersMiddleware` untuk memastikan seluruh respons HTTP 3xx (Redirect) hanya berisi body HTML minimal tanpa memuat isi/konten data sensitif (mencegah potensi *Big Redirect Sensitive Information Leak*). |
| 2026-07-20 | Keamanan (Cookie) | Mengonfigurasi `SESSION_HTTP_ONLY=true` dan `SESSION_SAME_SITE=lax` secara eksplisit pada `.env`, `.env.example`, dan `config/session.php` untuk memastikan seluruh cookie sesi terlindungi dari akses JavaScript (mencegah ancaman *Session Hijacking* / XSS cookie theft). |
| 2026-07-20 | Keamanan (Information Leak) | Menghapus header `X-Powered-By` dan `Server` secara otomatis pada `SecurityHeadersMiddleware` untuk mencegah kebocoran informasi versi PHP/server (*Server Leaks Information via X-Powered-By*). |
| 2026-07-20 | Keamanan (HSTS) | Mengaktifkan header `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` pada `SecurityHeadersMiddleware` untuk mewajibkan browser berkomunikasi via koneksi aman HTTPS (*HTTP Strict Transport Security*). |
| 2026-07-20 | Keamanan (Audit) | Memverifikasi temuan *Timestamp Disclosure - Unix* pada scanner security; mengonfirmasi bahwa nilai timestamp 10-13 digit merupakan data non-sensitif (waktu pembuatan file/halaman & cache buster) serta memastikan route penguji `/dev-reload-check` terisolasi hanya untuk lingkungan pengembangan lokal (`APP_ENV=local`). |
| 2026-07-20 | Keamanan (Anti-Sniffing) | Memastikan header `X-Content-Type-Options: nosniff` terpasang di seluruh respons aplikasi (termasuk halaman error 401, 403, 404, 500 via `$exceptions->respond(...)` pada `bootstrap/app.php`) untuk mencegah ancaman *MIME-sniffing*. |
| 2026-07-20 | Keamanan (Auth Audit) | Memverifikasi notifikasi informasional *Authentication Request Identified* pada scanner security; mengonfirmasi bahwa titik masuk autentikasi `POST /login` telah dilengkapi proteksi *Brute-Force Rate Limiting* (`RateLimiter`), regenerasi ID sesi otomatis (`session()->regenerate()`), proteksi *Session Fixation*, dan pencegahan *CSRF* (`@csrf`). |

---
