# Update 2026-07-09

## Halaman `super_admin_pembimbing.blade.php`

- Mempelajari halaman `resources/views/dashboard/super_admin_pembimbing.blade.php`.
- Halaman ini menggunakan layout utama `dashboard.layout`.
- Judul halaman dan header diatur menjadi `Kelola Pembimbing`.
- Konten utama berupa kartu tabel `Daftar Pembimbing`.
- Data yang ditampilkan berasal dari variabel `$pembimbing`.
- Setiap baris tabel menampilkan:
  - Nama lengkap pembimbing.
  - Email pembimbing.
  - Nama instansi dari relasi `instansi`.
  - Status aktif/nonaktif menggunakan badge.
- Jika tidak ada data pembimbing, halaman menampilkan pesan `Belum ada pembimbing terdaftar.`

## Alur Data

- Route halaman berada di `routes/web.php`.
- URL yang digunakan adalah `/super-admin/pembimbing`.
- Nama route adalah `super-admin.pembimbing`.
- Route hanya dapat diakses user yang sudah login dan memiliki role `super_admin`.
- Controller yang digunakan adalah `DashboardController::managePembimbing()`.
- Method controller mengambil user dengan role `admin`, memuat relasi `role` dan `instansi`, mengurutkan berdasarkan `nama_lengkap`, lalu mengirim data ke view.

## Catatan Perubahan

- Tidak ada perubahan pada file Blade halaman pembimbing.
- Menambahkan dokumentasi pembelajaran halaman ke file `update.md`.

## Catatan Lanjutan

- Halaman ini saat ini hanya bersifat daftar data.
- Belum ada fitur tambah, edit, hapus, pencarian, filter status, atau pagination.
- Struktur tampilannya konsisten dengan halaman `super_admin_peserta.blade.php`.

## Update Fitur Tambah Pembimbing

- Menambahkan tombol `Tambahkan Pembimbing` pada halaman `resources/views/dashboard/super_admin_pembimbing.blade.php`.
- Tombol membuka modal popup berisi form tambah pembimbing.
- Field form yang tersedia:
  - NIP.
  - Nama.
  - Email.
  - No Telepon.
  - Instansi.
  - Status aktif/tidak aktif.
- Menambahkan validasi penyimpanan pembimbing di `DashboardController::storePembimbing()`.
- Menambahkan route POST `super-admin.pembimbing.store` untuk menyimpan data pembimbing.
- Pembimbing baru otomatis dibuat dengan role `admin`.
- Password awal pembimbing baru diset ke `password`.
- Menambahkan kolom `nip` dan `no_telepon` ke tabel `users` melalui migration baru.
- Menampilkan kolom NIP dan No Telepon pada tabel daftar pembimbing.
- Menambahkan style tombol, alert, modal, form, dan layout mobile pada `resources/css/dashboard-layout.css`.

## Update Penyesuaian Modal Pembimbing

- Mengubah field `Instansi` di modal tambah pembimbing dari dropdown menjadi input text.
- Penyimpanan pembimbing sekarang menerima nama instansi dari input text.
- Jika nama instansi belum ada, sistem membuat data instansi baru dengan jenis default `Lainnya`.
- Mengubah warna teks tombol utama agar mengikuti tema:
  - Dark mode menggunakan teks gelap di atas tombol kuning.
  - Light mode menggunakan teks putih di atas tombol biru.

## Update Password Tambah Pembimbing

- Menambahkan field `Password` pada modal tambah pembimbing.
- Menambahkan field `Alamat` pada modal tambah pembimbing.
- Password wajib diisi minimal 8 karakter.
- Password pembimbing baru sekarang disimpan dari input form, bukan lagi memakai password default `password`.
- Pesan sukses tambah pembimbing tidak lagi menampilkan password default.
- Menambahkan tombol `Tampilkan/Sembunyikan` pada field password agar password bisa dilihat saat pengisian.

## Update Detail dan Reset Password Pembimbing

- Menampilkan data `Alamat` pada modal detail pembimbing.
- Menambahkan field `Alamat` pada modal edit pembimbing.
- Menambahkan tombol `Reset Password` pada kolom action daftar pembimbing.
- Tombol reset password membuka modal untuk mengisi password baru dan konfirmasi password.
- Password reset wajib minimal 8 karakter dan konfirmasi harus sama.
- Menambahkan route PUT `super-admin.pembimbing.reset-password`.
- Menambahkan method `DashboardController::resetPembimbingPassword()` untuk menyimpan password baru.
- Mengubah tombol action pembimbing menjadi ikon saja untuk Detail, Edit, Reset Password, dan Delete.

## Update Validasi Form Modal

- Semua form modal sekarang menampilkan alert SweetAlert2 jika ada field wajib yang belum diisi.
- Field `No Telepon` pada form pembimbing dan peserta hanya menerima angka.
- Input `No Telepon` otomatis menghapus karakter selain angka saat diketik.
- Validasi backend `no_telepon` juga diperketat agar hanya menerima angka.
- Alert tambahan ditampilkan untuk format email tidak valid dan password yang kurang dari minimal karakter.

## Update SweetAlert2

- Menggunakan SweetAlert2 dari package aplikasi melalui import di `resources/js/dashboard-layout.js`.
- Mengubah pemberitahuan `success`, `error`, dan validasi error menjadi popup SweetAlert2.
- Menghapus alert inline pada halaman `super_admin_pembimbing.blade.php` agar notifikasi tidak tampil ganda.
- Warna popup SweetAlert2 mengikuti tema dark mode dan light mode.
- Teks popup menggunakan Bahasa Indonesia:
  - `Berhasil` untuk notifikasi sukses.
  - `Terjadi Kesalahan` untuk notifikasi error.
  - `Data Belum Lengkap` untuk validasi form.

## Update Action Pembimbing

- Menambahkan kolom `Action` pada tabel daftar pembimbing.
- Menambahkan tombol `Detail`, `Edit`, dan `Delete` pada setiap baris pembimbing.
- Tombol `Detail` membuka modal detail berisi NIP, nama, email, no telepon, instansi, dan status.
- Tombol `Edit` membuka modal edit dan menyimpan perubahan lewat route PUT `super-admin.pembimbing.update`.
- Tombol `Delete` menghapus data lewat route DELETE `super-admin.pembimbing.destroy`.
- Delete memakai konfirmasi SweetAlert2 berbahasa Indonesia sebelum data dihapus.
- Menambahkan style tombol action dan tampilan detail modal pada `resources/css/dashboard-layout.css`.

## Update Tampilan Tabel Pembimbing

- Menghapus kolom `No Telepon` dan `Instansi` dari tabel daftar pembimbing.
- Data `No Telepon` dan `Instansi` tetap tersedia pada modal `Detail` dan form `Edit`.
- Tabel utama sekarang hanya menampilkan NIP, nama, email, status, dan action.

## Update Tampilan Modal

- Mengubah modal popup agar tidak memakai efek shadow.
- Modal popup sekarang memakai warna solid pada dark mode.
- Perubahan diterapkan melalui variabel CSS khusus modal di `resources/css/dashboard-layout.css`.

## Halaman `super_admin_peserta.blade.php`

- Mempelajari halaman `resources/views/dashboard/super_admin_peserta.blade.php`.
- Halaman ini menggunakan layout utama `dashboard.layout`.
- Judul halaman dan header diatur menjadi `Kelola Peserta`.
- Konten utama berupa kartu tabel `Daftar Peserta`.
- Data yang ditampilkan berasal dari variabel `$peserta`.
- Setiap baris tabel menampilkan:
  - Nama lengkap peserta.
  - Email peserta.
  - Nama pembimbing dari relasi `pembimbing`.
  - Nama instansi dari relasi `instansi`.
  - Status aktif/nonaktif menggunakan badge.
- Jika tidak ada data peserta, halaman menampilkan pesan `Belum ada peserta terdaftar.`

## Alur Data Peserta

- Route halaman berada di `routes/web.php`.
- URL yang digunakan adalah `/super-admin/peserta`.
- Nama route adalah `super-admin.peserta`.
- Route hanya dapat diakses user yang sudah login dan memiliki role `super_admin`.
- Controller yang digunakan adalah `DashboardController::managePeserta()`.
- Method controller mengambil user dengan role `peserta`, memuat relasi `role`, `instansi`, dan `pembimbing`, mengurutkan berdasarkan `nama_lengkap`, lalu mengirim data ke view.

## Catatan Lanjutan Peserta

- Halaman peserta saat ini masih bersifat daftar data.
- Belum ada tombol tambah peserta, modal form, detail, edit, delete, pencarian, filter, atau pagination.
- Struktur dasar halaman mirip dengan versi awal halaman `super_admin_pembimbing.blade.php`.

## Update Fitur Tambah Peserta

- Menambahkan tombol `Tambahkan Peserta` pada halaman `resources/views/dashboard/super_admin_peserta.blade.php`.
- Tombol membuka modal popup berisi form tambah peserta.
- Field form yang tersedia:
  - NIP.
  - Nama.
  - Email.
  - No Telepon.
  - Alamat.
  - Password.
  - No Darurat 1.
  - Hubungan Darurat 1.
  - No Darurat 2.
  - Hubungan Darurat 2.
  - Instansi.
  - Pembimbing.
  - Status aktif/tidak aktif.
- Pembimbing dipilih dari data user role `admin` yang sudah ada di database.
- Menambahkan validasi penyimpanan peserta di `DashboardController::storePeserta()`.
- Menambahkan route POST `super-admin.peserta.store` untuk menyimpan data peserta.
- Peserta baru otomatis dibuat dengan role `peserta`.
- Password peserta baru sekarang disimpan dari input form.
- Jika nama instansi belum ada, sistem membuat data instansi baru dengan jenis default `Lainnya`.
- Menampilkan kolom NIP dan No Telepon pada tabel daftar peserta.
- Menambahkan kolom `alamat` ke tabel `users` melalui migration baru.
- Menampilkan kolom Alamat pada tabel daftar peserta.
- Menambahkan kolom kontak darurat peserta ke tabel `users` melalui migration baru.
- Field No Darurat 1 dan No Darurat 2 hanya menerima angka.

## Update Action Peserta

- Menambahkan tombol aksi `Detail`, `Edit`, `Reset Password`, dan `Delete` pada kolom Action daftar peserta di halaman `resources/views/dashboard/super_admin_peserta.blade.php`.
- Menambahkan modal `#detail-peserta-modal` untuk menampilkan detail lengkap peserta (NIP, Nama, Email, No Telepon, Alamat, No Darurat 1 & 2 beserta Hubungan, Instansi, Pembimbing, dan Status) kecuali password.
- Menambahkan modal `#edit-peserta-modal` untuk mengubah data peserta melalui route PUT `super-admin.peserta.update` yang divalidasi oleh `DashboardController::updatePeserta()`.
- Menambahkan modal `#reset-password-peserta-modal` untuk mereset password peserta melalui route PUT `super-admin.peserta.reset-password` yang divalidasi oleh `DashboardController::resetPesertaPassword()`.
- Menambahkan konfirmasi penghapusan (Delete) menggunakan SweetAlert2 (`window.confirmDangerAction`) sebelum data peserta dihapus.

## Update Database Seeder

- Memperbarui `database/seeders/UserSeeder.php` untuk men-seed NIP, No Telepon, Alamat, serta Kontak Darurat 1 & 2 (beserta Hubungannya) secara otomatis untuk data testing admin/pembimbing dan peserta (interns).
- Menjalankan fresh migration dan seeder (`php artisan migrate:fresh --seed`) untuk mempopulasi ulang database dengan data terstruktur baru ini.

## Update Dropdown Pembimbing (Searchable)

- Menambahkan komponen *searchable select/dropdown* kustom untuk pilihan Pembimbing di modal Tambah dan Edit peserta.
- Menyembunyikan input select bawaan browser dan menggantikannya dengan input text pencarian beserta daftar opsi dropdown kustom yang dapat dicari/difilter saat pengguna mengetik.
- Menambahkan style visual dropdown pencarian di `resources/css/dashboard-layout.css`.
- Menghubungkan fungsi pencarian di frontend agar tetap mensinkronisasikan pilihan ke input `<select>` asli (sehingga form validation dan request data backend tetap bekerja dengan normal).
- Menambahkan komponen *autocomplete input dropdown* kustom pada bagian pengisian nama Instansi. Jika nama instansi diketik, akan memunculkan rekomendasi instansi yang terdaftar di database.

## Update Pencarian & Filter Tabel Daftar Peserta

- Menambahkan bar pencarian (*search bar*) `#table-search` pada tabel daftar peserta untuk menyaring data secara real-time berdasarkan NIP, nama lengkap, email, atau nama instansi.
- Menyediakan tombol filter kustom dengan ikon SVG di sebelah search bar yang memicu dibukanya modal filter (`#filter-peserta-modal`).
- Memindahkan input filter (Status, Instansi, Pembimbing) ke dalam satu modal terpadu agar layout tabel tetap bersih dan rapi.
- Menyediakan tombol **Terapkan Filter** untuk mengeksekusi filter dan **Reset Filter** untuk mengosongkan semua filter.
- Menambahkan style `.btn-active-filter` agar tombol filter utama disorot (highlighted) dengan warna aksen bila terdapat minimal satu kriteria filter yang sedang aktif diterapkan.
- Menyediakan baris tabel info `#table-no-results` yang otomatis tampil jika tidak ada baris peserta yang cocok dengan kriteria pencarian/filter.
- Mengimplementasikan seluruh logika ini secara client-side dengan vanilla JS demi kinerja penyaringan data yang responsif tanpa reload halaman.

## Penyelarasan Warna Dropdown Tema (Light & Dark Mode)

- Menambahkan aturan CSS global untuk elemen `select option` agar otomatis menyesuaikan dengan warna latar belakang dan teks tema saat ini.
- Pada **Dark Mode**: Elemen `<option>` menggunakan latar belakang gelap (`#1e293b`) dan teks terang (`#f8fafc`).
- Pada **Light Mode**: Elemen `<option>` menggunakan latar belakang putih (`#ffffff`) dan teks gelap (`#0f172a`).
- Menjamin agar seluruh dropdown kustom (pembimbing, instansi autocomplete) dan dropdown native (filter status, instansi, pembimbing) memiliki rasio kontras warna yang baik di semua perangkat dan browser.

## Update Fitur Pagination (Maksimal 5 Data)

- Menambahkan komponen pagination kustom `#table-pagination` di bawah tabel peserta (Kelola Peserta) dan tabel pembimbing (Kelola Pembimbing) untuk membatasi tampilan data maksimal 5 data per halaman.
- Menampilkan teks informasi penomoran halaman secara dinamis: "Menampilkan [awal]-[akhir] dari [total] data".
- Menambahkan tombol kontrol halaman (Sebelumnya, Angka Halaman, Berikutnya) yang otomatis ter-generate berdasarkan jumlah data aktual.
- Mengintegrasikan logika pagination secara langsung dengan search bar dan filter kustom: jika pengguna melakukan pencarian atau pemfilteran data, jumlah halaman dan penomoran halaman akan dihitung ulang secara real-time dan dikembalikan ke halaman pertama hasil pencarian.
- Menambahkan visual pendukung di `resources/css/dashboard-layout.css` untuk kelas `.table-pagination`, `.pagination-btn`, dan status tombol aktif (`.is-active`) / dinonaktifkan (`disabled`).

## Update Halaman Kelola Pembimbing (Search, Filter Modal, & Autocomplete)

- Menambahkan bar pencarian (`#table-search`) pada tabel Kelola Pembimbing untuk memfilter baris berdasarkan NIP, nama lengkap, email, atau nama instansi.
- Menyediakan tombol filter ber-ikon SVG yang memicu modal filter pembimbing (`#filter-pembimbing-modal`) untuk menyaring data berdasarkan Status (Aktif/Nonaktif) dan Instansi.
- Menambahkan fungsionalitas input instansi autocomplete kustom pada formulir tambah & edit pembimbing (mengambil referensi nama instansi secara dinamis dari database).
- Menerapkan pagination kustom client-side maksimal 5 data per halaman untuk tabel daftar pembimbing.
- Memperbarui `DashboardController::managePembimbing()` agar turut mem-pass data variabel `$instansi` ke file view [super_admin_pembimbing.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/super_admin_pembimbing.blade.php).

## Penambahan Fitur Kelola Master Data Instansi

- Membuat halaman **Kelola Instansi** (`/super-admin/instansi`) khusus bagi Super Admin.
- Menyediakan tabel daftar instansi yang dilengkapi dengan kolom Nama Instansi, Jenis Instansi, dan Jumlah Anggota (Pembimbing & Peserta).
- Menambahkan menu **Kelola Instansi** pada sidebar navigasi [layout.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/layout.blade.php).
- Mengimplementasikan alur CRUD instansi secara menyeluruh pada `DashboardController`:
  - `manageInstansi()`: Menampilkan semua instansi beserta jumlah anggotanya.
  - `storeInstansi()` & `updateInstansi()`: Validasi keunikan nama instansi dan jenis instansi.
  - `destroyInstansi()`: Melindungi integritas data dengan membatasi penghapusan instansi yang masih memiliki pembimbing atau peserta aktif.
- Menambahkan fungsionalitas *client-side* pencarian real-time, penyaringan jenis instansi melalui modal, dan pagination interaktif maksimal 5 data per halaman.
- Menambahkan integrasi popup SweetAlert2 (`window.confirmDangerAction`) saat tombol hapus diklik.
- Menulis berkas test unit/fitur [SuperAdminInstansiTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/SuperAdminInstansiTest.php) untuk menguji keabsahan hak akses serta alur CRUD instansi (dan semua test passed).

## Pemisahan Aset CSS & JS ke File Terpisah

- Memindahkan semua baris logika inline JavaScript dan CSS dari berkas Blade Super Admin ke dalam direktori terpisah:
  - **Kelola Peserta**: Dipindah ke `resources/js/super_admin_peserta.js` dan `resources/css/super_admin_peserta.css`.
  - **Kelola Pembimbing**: Dipindah ke `resources/js/super_admin_pembimbing.js` dan `resources/css/super_admin_pembimbing.css`.
  - **Kelola Instansi**: Dipindah ke `resources/js/super_admin_instansi.js` dan `resources/css/super_admin_instansi.css`.
- Menghubungkan seluruh berkas aset eksternal tersebut ke halaman Blade-nya masing-masing menggunakan direktif `@vite('resources/...')`.
- Mendaftarkan keenam berkas aset baru tersebut pada berkas konfigurasi `vite.config.js` untuk kompilasi otomatis.

## Penambahan Grafik Analisis Kehadiran Harian / Mingguan

- Memproses data riwayat kehadiran harian peserta selama 7 hari kerja terakhir pada method `superAdminDashboard` di `DashboardController`.
- Memuat visualisasi **ApexCharts** stacked column di `super_admin.blade.php` melalui Canvas/Div Container `#attendanceChart`.
- Menambahkan skrip inisialisasi dan rendering di `resources/js/dashboard-super-admin.js` lengkap dengan **MutationObserver** untuk mendeteksi perubahan tema (light/dark mode) secara instan, serta menyelaraskan warna teks, legenda, gridline, dan tooltip ApexCharts.
- Mengatur style glassmorphism pada boks kontainer grafik di `resources/css/dashboard-super-admin.css`.

## Penambahan Fitur Pengaturan Parameter Global Aplikasi (System Settings)

- Mengintegrasikan package `spatie/laravel-settings` untuk manajemen parameter global aplikasi secara dinamis.
- Membuat settings class `app/Settings/GeneralSettings.php` yang mendefinisikan parameter waktu absensi (`jam_masuk`, `jam_pulang`, `batas_keterlambatan`) dan parameter area geofencing (`latitude_kantor`, `longitude_kantor`, `radius_meter`).
- Melakukan check-in status validation (keterlambatan) secara dinamis di `AttendanceController.php` dengan merujuk pada nilai `batas_keterlambatan` dari GeneralSettings.
- Membuat halaman formulir pengaturan `/super-admin/settings` khusus bagi Super Admin untuk mengupdate parameter tersebut.
- Menyusun layout settings yang responsif dan berpenampilan premium pada view `super_admin_settings.blade.php` dengan stylesheet pendukung di `resources/css/super_admin_settings.css` (terdaftar di `vite.config.js`).
- Menambahkan test feature `SuperAdminSettingsTest.php` untuk menguji hak otorisasi akses halaman, pembatasan akses non-super-admin, validasi input waktu/radius, dan keberhasilan proses penyimpanan data settings (dan seluruh test passed).
- Mengintegrasikan peta interaktif **Leaflet** di `super_admin_settings.blade.php` dengan penanda (*draggable marker*) sehingga koordinat kantor dapat diatur secara visual tanpa input manual.
- Mengatur bidang input koordinat latitude dan longitude kantor menjadi `readonly` agar diperbarui secara otomatis dari lokasi penanda di peta.
- Menambahkan visualisasi lingkaran radius geofencing (*geofence radius circle*) dinamis pada peta Leaflet yang meluas/menyempit secara real-time saat pengguna mengubah nilai radius pada kolom input form.
- Menyediakan **ikon edit (pensil)** bergaya modern di sebelah kanan input Jam Masuk, Jam Pulang, Toleransi Keterlambatan, dan Radius Geofencing sebagai panduan visual agar pengguna langsung mengenali bahwa kolom-kolom parameter tersebut dapat diedit.
- Menambahkan **kolom pencarian lokasi (search input)** di atas peta Leaflet yang terhubung ke Nominatim API untuk mempermudah pencarian alamat kantor secara visual.
- Menambahkan **tombol navigasi lokasi (GPS)** menggunakan browser Geolocation API untuk mendeteksi posisi koordinat pengguna saat ini, lalu menaruh marker dan mengarahkan peta ke lokasi tersebut secara langsung.
- Mengganti seluruh pesan **browser default alert** (seperti lokasi tidak ditemukan atau kegagalan GPS) dengan pop-up dialog **SweetAlert2** interaktif berpenampilan modern dengan skema warna background, teks, dan tombol yang menyesuaikan tema aktif (light/dark mode).
