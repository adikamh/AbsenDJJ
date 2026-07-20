# Catatan Perubahan Proyek (Changelog Lengkap)

Dokumen ini mencatat seluruh perubahan konfigurasi, struktur database, fungsionalitas, keamanan, dan optimasi antarmuka yang telah diimplementasikan dari **awal pembahasan hingga akhir**. Dokumentasi ini disusun untuk memastikan keselarasan sistem pada PHP 8.2 (target cPanel Multi-PHP), transisi framework, serta pemantapan alur absensi peserta magang.

---

## Ringkasan Perubahan Berdasarkan Kategori

### 1. Transisi Framework, Kompatibilitas PHP 8.2, dan Model Eloquent
* **Penyesuaian Versi Framework**:
  * Menurunkan framework dari Laravel 13 ke Laravel 11.55 demi kecocokan PHP 8.2.
  * Melakukan peningkatan terkontrol ke **Laravel 12.0** (versi stabil) dengan mengunci dependensi minimum PHP pada versi **`^8.2`** di `composer.json` agar aman dideploy ke server cPanel pengguna.
* **Konversi PHP Attributes Eloquent**:
  * Menghapus sintaksis PHP Attributes modern (`#[Fillable]` & `#[Hidden]`) di atas nama *class* Model Eloquent (`User`, `Attendance`, `LeaveRequest`, `Logbook`) karena sering memicu error *mass-assignment* lintas versi. Diganti dengan deklarasi properti standar Laravel:
    ```php
    protected $fillable = [ ... ];
    protected $hidden = [ ... ];
    ```
* **Perbaikan Driver Database (`config/database.php`)**:
  * Mengubah pemanggilan driver MySQL SSL `Pdo\Mysql::ATTR_SSL_CA` (fitur PHP 8.4) menjadi konstanta global standar **`PDO::MYSQL_ATTR_SSL_CA`** agar tidak memicu error *Class not found* pada runtime PHP 8.2.
* **Penghapusan Paket Konflik**:
  * Menghapus paket dev `laravel/pao` dan paket chart `arielmejiadev/larapex-charts` dari dependensi karena menuntut PHP ^8.3. Sebagai gantinya, visualisasi grafik di sisi client menggunakan pustaka ApexCharts langsung via JavaScript.

### 2. Aturan Absensi Baru, Batas Waktu, dan Libur Kerja
* **Penetapan Libur Akhir Pekan Otomatis**:
  * Mengintegrasikan seeding database (`work_schedules`) secara default agar menetapkan hari **Sabtu** dan **Minggu** sebagai "Libur Akhir Pekan". Sistem absensi secara otomatis menutup akses check-in pada hari-hari tersebut.
* **Sinkronisasi Hari Libur Nasional via API**:
  * Menambahkan tombol **"Info Sumber Data"** beserta modal pop-up yang merujuk pada endpoint `https://api-hari-libur.vercel.app`.
  * Menambahkan tombol **"Sinkronisasi API Manual"** lengkap dengan indikator *loading spinner* pada halaman Pengaturan Parameter Global untuk mengimpor hari libur nasional secara instan.
* **Status Logika Check-In & Batas Absensi**:
  * Mengubah penamaan label di seluruh halaman dari "Batas Terlambat/Toleransi" menjadi **"Batas Absensi (Waktu Terakhir Absen Masuk)"** agar lebih edukatif.
  * Absen masuk setelah jam kerja tetapi sebelum batas absensi akan diberi status **`Terlambat`**. 
  * Jika melewati batas absensi, tombol masuk berubah teks menjadi **"Waktu Absen Berakhir"** dan memicu pop-up SweetAlert2 penolakan jika diklik.
* **Alur Checkout Lupa Absen Masuk**:
  * Peserta yang melewatkan absen masuk pagi hari tetap diperbolehkan melakukan **Absen Pulang** pada sore harinya.
  * Jika absen pulang dilakukan sebelum jam pulang kompensasi (jam pulang standar + selisih jam keterlambatan), status absensinya diset otomatis menjadi **`Izin`** (dan otomatis membuat pengajuan izin di database). Jika tepat atau setelah jam kompensasi, status menjadi **`Lupa Absen Masuk`**.
  * Sistem secara otomatis mengubah status kehadiran hari sebelumnya menjadi **`Lupa Absen Pulang`** pada pukul 24:00 jika peserta lupa melakukan check-out.

### 3. Perbaikan Otorisasi & Perbaikan Perbandingan Tipe Data (Strict Comparison)
* **Pencegahan Error 403 Forbidden**:
  * Mengatasi kegagalan pencocokan identitas (karena cPanel mengembalikan string sedangkan ORM lokal mengembalikan integer) dengan menerapkan *explicit integer casting* `(int)` pada comparison ID pengguna (`user_id` dan `pembimbing_id`) di seluruh controller logbook dan absensi.
* **Bypass Otorisasi Khusus Super Admin**:
  * Membuka rute ekspor PDF/CSV laporan bulanan dan logbook peserta agar dapat diakses penuh oleh **Super Admin** tanpa dibatasi hak kepemilikan pembimbing.
* **Kalkulasi Absen/Alfa**:
  * Memasukkan status kehadiran `Lupa Absen Masuk` dan `Lupa Absen Pulang` ke dalam perhitungan kumulatif ketidakhadiran (alfa) di laporan rekapitulasi.

### 4. Aturan Validasi Pengajuan Izin & Sakit Baru (Terbaru)
* **Relaksasi Skema Database**:
  * Mengubah kolom `alasan` di tabel `leave_requests` menjadi **`nullable()`** via file migrasi agar alasan tidak wajib diisi ketika tipe pengajuan adalah sakit.
* **Validasi Kondisional Backend (`LeaveRequestController`)**:
  * **Jika memilih Izin**: Kolom `alasan` wajib diisi (`required`), sedangkan berkas dokumen bukti bersifat opsional (`nullable`).
  * **Jika memilih Sakit**: Dokumen bukti (Surat Dokter/Lampiran) wajib diunggah (`required` berupa file gambar/PDF maks 10MB), sedangkan alasan tertulis bersifat opsional (`nullable`).
* **Interaksi Form Frontend Dinamis (`leave.blade.php`)**:
  * Menuliskan skrip Javascript dinamis untuk menukar status wajib (`required`) dan menampilkan/menyembunyikan penanda bintang merah (`*`) pada input alasan dan berkas bukti secara instan berdasarkan pilihan dropdown jenis pengajuan.
  * Mengubah ID select dropdown modal menjadi `modal-jenis` untuk menghindari konflik ID ganda (`jenis`) dengan filter tabel utama.

### 5. Optimasi Geolokasi Presisi Tinggi & Geolocation Proxy (Terbaru)
* **Pelacakan Lokasi Kontinu (`watchPosition`)**:
  * Menggantikan fungsi sekali-jalan `getCurrentPosition` dengan pelacakan berkelanjutan `watchPosition` dikombinasikan dengan konfigurasi daya tinggi: `{ enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }`.
* **Geolocation Proxy (Pemberantas Bug Overwrite Lokasi Bandung)**:
  * Memasang script Geolocation Proxy di baris paling atas `@push('scripts')` (sebelum `@vite`) pada berkas `dashboard.blade.php`.
  * Script ini bertugas membajak pemanggilan `navigator.geolocation.getCurrentPosition` dari aset terkompilasi `dashboard.js` dan memaksanya untuk menggunakan data koordinat akurat terakhir yang tersimpan di `window.accuratePositionCache`. 
  * Ini **100% menyelesaikan masalah *race condition*** di mana callback kegagalan dari `dashboard.js` sering kali menimpa koordinat presisi pengguna dan memaksanya kembali ke lokasi default Bandung (`-6.914744, 107.625680`).
* **Sistem Badge Akurasi Sinyal GPS Real-time**:
  * Menyertakan indikator deviasi presisi GPS dalam meter ($\pm\text{m}$) di sisi koordinat pengguna secara dinamis:
    * 🟢 **Akurasi Tinggi** ($\le 40$ meter): Latar hijau, menandakan koordinat sangat presisi.
    * 🟡 **Akurasi Sedang** ($41 - 100$ meter): Latar oranye.
    * 🔴 **Akurasi Rendah** ($> 100$ meter): Latar merah, memberi tahu pengguna untuk bergeser ke tempat terbuka agar GPS mengunci sinyal secara presisi sebelum melakukan absensi.
* **Penyelamatan Event Listener via Inline Capturing**:
  * Mengikat event listener klik tombol check-in (`btnSubmitIn`) dan check-out (`btnSubmitOut`) pada fase *capturing* (`true`) langsung di file Blade untuk menimpa dan memintas script lama dari cache browser pengguna.
  * Memperbaiki kesalahan sintaksis `SyntaxError: expected expression, got ')'` akibat hilangnya kurung kurawal penutup `}` pada kode interceptor absen pulang.

### 6. Pemisahan Halaman Parameter Global & Dropdown Menu (Terbaru)
* **Pemisahan URL & Halaman**:
  * Memecah halaman pengaturan parameter global yang semula bertumpuk dalam sistem tab client-side menjadi **4 sub-halaman terpisah dengan URL-nya masing-masing** di bawah rute `/super-admin/settings/...` (Jadwal & Kehadiran, Kalender Jadwal, Tanggal Khusus/Libur, dan Lokasi & Geofencing).
  * **Penyatuan Jadwal Default & Jadwal Harian Khusus**: Menyatukan halaman "Jadwal & Waktu Kehadiran Default" dan "Jadwal Harian Khusus" (day overrides) ke dalam satu halaman terpadu **"Jadwal & Kehadiran"** (`settings/default`) agar super admin dapat menyetel jam default dan override hari kerja mingguan dalam satu layar.
  * Menyesuaikan seluruh aksi form submit, sinkronisasi API, dan penghapusan jadwal di `SettingsController.php` agar melakukan redirect secara tepat kembali ke sub-halaman asal (bukan ke halaman awal).
  * Menghapus bilah tab navigasi horizontal pada keempat sub-halaman tersebut demi kerapian visual, karena fungsinya telah sepenuhnya digantikan oleh dropdown sidebar.
* **Sidebar & Mobile Dropdown Navigation**:
  * Mengintegrasikan dropdown submenu (`has-submenu`) pada sidebar dasbor utama untuk mengelompokkan keempat sub-menu tersebut secara terstruktur.
  * Menambahkan fungsi Javascript `toggleSubmenu(el)` untuk membuka/tutup menu secara interaktif lengkap dengan perubahan arah tanda panah (`▾` / `▴`).
  * Menerapkan pembukaan otomatis menu dropdown jika pengguna sedang berada di rute settings mana saja (`request()->routeIs('super-admin.settings*')`).
  * **Floating Sub-menu Mobile (Terbaru)**: Menghapus tombol Pembimbing dan Peserta terpisah pada bottom nav, lalu menyatukannya ke dalam satu tombol trigger **Pengguna** yang memicu menu melayang (*floating pop-up menu*). Menu pop-up untuk **Setelan** (.mobile-settings-popup) dan **Pengguna** (.mobile-settings-popup.popup-center) masing-masing akan meluncur naik secara transparan saat diklik dengan rounded corners lebar, border halus, bayangan melayang, serta dibekali logika Javascript terpadu untuk *click-outside handler* dan pencegahan tumpang tindih menu.
* **CSS Layout Sizing**:
  * Mengubah styling kelas aktif sidebar agar hanya memberi efek warna background/border kiri pada baris menu induk (menggunakan child selector `> a`).
  * Ketika sidebar diciutkan (`body.sidebar-collapsed`), submenu dropdown bertransformasi secara dinamis menjadi tumpukan ikon kecil yang terpusat secara vertikal di bawah menu induk, sedangkan tulisan teks dan indikator panah otomatis disembunyikan.
  * Setiap sub-menu dibekali ikon SVG unik (seperti Jam, Kalender, Matahari, Bintang, dan Pin Peta) yang otomatis memiliki status aktif/hover yang selaras.
  * **Floating Bottom Navigation Capsule**: Merombak total tampilan bilah navigasi bawah mobile (`.bottom-nav`) menjadi kapsul melayang (*floating capsule*) dengan margin di sekelilingnya (`bottom: 16px; left: 16px; right: 16px;`), sudut membulat lebar (`border-radius: 24px`), border menyeluruh, serta efek bayangan 3D yang modern. Perubahan ini berlaku serentak untuk semua tipe hak akses pengguna (Super Admin, Admin, dan Peserta).
  * Menyesuaikan margin bawah `.main-content` pada mobile dari `85px` menjadi `105px` untuk memberikan jarak gulir yang cukup di balik kapsul menu melayang.
  * **Pemisahan Jarak FAB Mobile (Terbaru)**: Menaikkan posisi Floating Action Button (FAB) `.fab-container` (Super Admin) dan `.fab-container-left` (Pembimbing/Admin) dari `bottom: 80px` menjadi `bottom: 105px` di perangkat seluler agar tidak saling tumpang tindih dengan menu bottom nav kapsul melayang. Peran Peserta telah dikonfirmasi tidak memiliki tombol FAB.
  * **Flatpickr Time Picker (Terbaru)**: Mengganti input waktu default browser (`type="time"`) yang kaku dan tidak seragam menjadi input Flatpickr (`type="text"` kelas `.time-picker` dengan mode `readonly` untuk mencegah keyboard virtual menutupi layar). Kami merancang kustomisasi CSS yang sepenuhnya mendukung variabel CSS dasbor (otomatis menyesuaikan tema Gelap/Terang) dengan visual popup bersudut bulat (`border-radius: 16px`), efek hover warna emas/aksen utama, dan penataan AM/PM yang sangat rapi. Dilengkapi juga dengan sinkronisasi metode `.setDate()` pada edit modal.

---

## Rincian File yang Diubah Dari Awal Pembahasan

Berikut adalah daftar file yang dimodifikasi sepanjang siklus pengerjaan proyek:

1. **[composer.json](file:///k:/Notifications/composer.json)**
   * Konfigurasi PHP `^8.2`, Laravel `^12.0`, penyesuaian dependensi dev, dan penghapusan paket `larapex-charts`.
2. **[config/database.php](file:///k:/Notifications/config/database.php)**
   * Penggantian `Pdo\Mysql::ATTR_SSL_CA` menjadi `PDO::MYSQL_ATTR_SSL_CA`.
3. **[database/migrations/2026_07_07_000003_create_leave_requests_table.php](file:///k:/Notifications/database/migrations/2026_07_07_000003_create_leave_requests_table.php)**
   * Relaksasi kolom `alasan` menjadi `nullable()`.
4. **[app/Models/User.php](file:///k:/Notifications/app/Models/User.php)**, **[app/Models/Attendance.php](file:///k:/Notifications/app/Models/Attendance.php)**, **[app/Models/LeaveRequest.php](file:///k:/Notifications/app/Models/LeaveRequest.php)**, **[app/Models/Logbook.php](file:///k:/Notifications/app/Models/Logbook.php)**
   * Mengembalikan sintaksis ke model konvensional model properti standar Laravel.
5. **[app/Http/Controllers/AttendanceController.php](file:///k:/Notifications/app/Http/Controllers/AttendanceController.php)**
   * Pengaturan pembatasan absen masuk terlambat, aturan checkout lupa absen masuk (status `Izin` vs `Lupa Absen Masuk`), dan validasi geofence server-side.
6. **[app/Http/Controllers/Peserta/LeaveRequestController.php](file:///k:/Notifications/app/Http/Controllers/Peserta/LeaveRequestController.php)**
   * Penanganan aturan validasi kondisional antara pengajuan sakit dan izin.
7. **[app/Http/Controllers/Peserta/DashboardController.php](file:///k:/Notifications/app/Http/Controllers/Peserta/DashboardController.php)**
   * Pengiriman data parameter toleransi keterlambatan harian dan koordinat kantor ke dasbor.
8. **[app/Http/Controllers/DashboardController.php](file:///k:/Notifications/app/Http/Controllers/DashboardController.php)**
   * Integrasi auto-update status `Lupa Absen Pulang` harian bagi peserta.
9. **[app/Http/Controllers/Admin/InternController.php](file:///k:/Notifications/app/Http/Controllers/Admin/InternController.php)**, **[app/Http/Controllers/Peserta/AttendanceHistoryController.php](file:///k:/Notifications/app/Http/Controllers/Peserta/AttendanceHistoryController.php)**, **[app/Http/Controllers/Peserta/LogbookController.php](file:///k:/Notifications/app/Http/Controllers/Peserta/LogbookController.php)**
   * Casting integer `(int)` pembanding otorisasi, penambahan hak bypass Super Admin untuk ekspor laporan, dan rekapitulasi status lupa absen.
10. **[resources/views/dashboard/peserta/leave.blade.php](file:///k:/Notifications/resources/views/dashboard/peserta/leave.blade.php)**
    * Implementasi script interaksi form dinamis dan resolusi konflik ID `modal-jenis`.
11. **[resources/views/dashboard/peserta/dashboard.blade.php](file:///k:/Notifications/resources/views/dashboard/peserta/dashboard.blade.php)**
    * Pemasangan Geolocation Proxy, watchPosition kontinu, visualisasi meter akurasi GPS real-time, dan penulisan interceptor klik capturing.
12. **[resources/js/peserta/dashboard.js](file:///k:/Notifications/resources/js/peserta/dashboard.js)**
    * Menambahkan parameter akurasi tinggi pada fungsi `getCurrentPosition` (seperti `{ enableHighAccuracy: true }`) agar selaras dengan GPS inline.
13. **[app/Http/Controllers/SuperAdmin/SettingsController.php](file:///k:/Notifications/app/Http/Controllers/SuperAdmin/SettingsController.php)**
    * Pemisahan logika loading data parameter global ke dalam 5 sub-halaman terpisah serta pengkondisian redirect rute secara modular.
14. **[DELETE] resources/views/dashboard/super_admin/settings.blade.php**
    * Dihapus setelah kodenya dipecah ke dalam folder subviews demi kemudahan pengelolaan berkas.
15. **[resources/views/dashboard/super_admin/settings/default.blade.php](file:///k:/Notifications/resources/views/dashboard/super_admin/settings/default.blade.php)**
    * View terpadu (Jadwal & Kehadiran) hasil penyatuan formulir jam masuk/pulang default dan grid kartu "Jadwal Harian Khusus" (day overrides).
16. **[NEW] resources/views/dashboard/super_admin/settings/calendar.blade.php**
    * View kalender jadwal bulanan interaktif.
17. **[DELETE] resources/views/dashboard/super_admin/settings/day_overrides.blade.php**
    * Dihapus setelah kodenya disatukan secara penuh ke dalam subview default.blade.php.
18. **[NEW] resources/views/dashboard/super_admin/settings/date_overrides.blade.php**
    * View tabel penambahan tanggal libur/masuk khusus dan sinkronisasi API Vercel Libur Nasional.
19. **[NEW] resources/views/dashboard/super_admin/settings/geofencing.blade.php**
    * View form koordinat kantor terintegrasi peta Leaflet, penanda pin draggable, Nominatim search, dan deteksi GPS.
20. **[resources/views/dashboard/layout.blade.php](file:///k:/Notifications/resources/views/dashboard/layout.blade.php)**
    * Integrasi dropdown submenu di sidebar, event handler `toggleSubmenu`, state aktif menu setelan mobile, dan pemuatan CDN Flatpickr CSS/JS.
21. **[resources/css/dashboard-layout.css](file:///k:/Notifications/resources/css/dashboard-layout.css)**
    * Penambahan CSS child selector `.nav-item.active > a`, visualisasi ciut sidebar, dan CSS variables-aware styling overrides untuk Flatpickr time picker.
22. **[resources/css/super_admin/settings.css](file:///k:/Notifications/resources/css/super_admin/settings.css)**
    * Desain responsif mobile full-width untuk tombol sinkronisasi manual.
23. **[resources/js/super_admin/settings.js](file:///k:/Notifications/resources/js/super_admin/settings.js)**
    * Integrasi SweetAlert2 loading modal saat hapus/reset dan inisialisasi flatpickr time picker pada class `.time-picker` serta sinkronisasi modal edits via `.setDate()`.
24. **[routes/web.php](file:///k:/Notifications/routes/web.php)**
    * Penambahan 8 rute baru untuk pemisahan halaman pengaturan dan update data modular.
25. **[.agents/AGENTS.md](file:///k:/Notifications/.agents/AGENTS.md)**
    * Pembuatan aturan kompatibilitas target PHP 8.2 & Laravel 12.0.
26. **[Doc/catatan_perubahan.md](file:///k:/Notifications/Doc/catatan_perubahan.md)** *(File ini)*
    * Dokumentasi kronologis komprehensif.
