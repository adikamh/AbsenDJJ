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

## Penyelarasan Tema & Penyempurnaan Tampilan Halaman Pengaturan (Jadwal Kehadiran)

- Menghapus semua inline style override yang tidak kompatibel pada input form dan label di `super_admin_settings.blade.php`, seperti `background: var(--input-bg)` dan `color: var(--text-main)`. Hal ini membuat input form mewarisi desain global secara konsisten.
- Mengubah warna teks deskripsi dan kontainer kosong (empty state) dari variabel non-standar `var(--text-muted)` ke variabel standar `var(--text-secondary)`.
- Mengimplementasikan CSS grid responsive `.time-inputs-grid` pada form tab jadwal default untuk menggantikan grid kolom kaku, sehingga tampilan di layar mobile tidak terhimpit.
- Memperbarui berkas stylesheet `super_admin_settings.css` untuk:
  - Menyinkronkan warna latar belakang kartu hari (`.day-card`) dengan variabel standar `--glass-bg` agar terlihat kontras dan terbaca baik pada light mode maupun dark mode.
  - Memperbaiki warna tombol edit (`.btn-day-edit` dan `.btn-date-edit`) menggunakan latar `--accent-primary` dan teks `--button-primary-text` agar teksnya tetap terbaca jelas (tidak berwarna putih di atas warna kuning terang).
  - Menyinkronkan modal overlay (`.modal-container`) menggunakan latar belakang `--modal-bg` agar warna latar belakang modal berubah solid putih pada light mode dan solid gelap pada dark mode, menjaga agar teks isian di dalamnya tidak kabur atau tidak terbaca.
  - Memperbaiki tab navigation hover dan background aktif menggunakan `--accent-primary-rgba` dan `--accent-primary-rgba-light` alih-alih variabel RGB mentah yang tidak terdefinisi.
- Menghubungkan tombol aksi Edit dan Hapus pada tabel tanggal khusus dengan class `.btn-date-edit` dan `.btn-date-delete` kustom yang diatur langsung dalam berkas CSS, menghilangkan deklarasi inline style yang tidak perlu.
- Mengubah checkbox "Tandai sebagai Hari Libur" biasa menjadi komponen **toggle switch switch-slider kustom** yang modern dan interaktif dengan aksen merah cerah (`#ef4444`) saat diaktifkan, serta merapikan struktur form di dalam modal `Edit Jadwal Hari` dan `Tambah/Edit Tanggal Khusus` agar seragam dan sedap dipandang.

## Integrasi Import Hari Libur Nasional Otomatis (API Vercel Hari Libur)

- Menyediakan fitur import hari libur nasional Indonesia secara otomatis menggunakan REST API publik `https://api-hari-libur.vercel.app/api`.
- Menambahkan route POST `/super-admin/schedules/sync-holidays` di [web.php](file:///c:/laragon/www/AbsenDJJ/routes/web.php).
- Mengimplementasikan method `syncHolidays()` di [DashboardController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/DashboardController.php) yang melakukan HTTP GET ke API, mem-parsing daftar hari libur, mengecek duplikasi tanggal di database, dan menyimpan data hari libur baru ke tabel `work_schedules` dengan status `is_holiday: true`.
- Menambahkan tombol **Import Hari Libur** dengan ikon download bergaya modern di tab Tanggal Khusus/Libur pada halaman [super_admin_settings.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/super_admin_settings.blade.php).
- Menghubungkan tombol tersebut dengan modal input SweetAlert2 agar Super Admin dapat memilih tahun target import secara interaktif, lengkap dengan efek visual loading saat proses sinkronisasi berjalan.
- Menulis unit/feature test `test_super_admin_can_sync_holidays_from_api` di [SuperAdminSettingsTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/SuperAdminSettingsTest.php) dengan memanfaatkan `Http::fake()` untuk menjamin keandalan pengujian tanpa bergantung pada koneksi internet eksternal (11/11 tests passed).

## Tampilan Keterangan Acara / Hari Libur di Sel Kalender

- Menambahkan sub-elemen `.cell-desc` untuk menampilkan keterangan hari libur atau alasan penanggalan khusus langsung di bawah jam kerja pada setiap sel tanggal di kalender.
- Mengatur style CSS `.cell-desc` dengan visual tag berlatar belakang transparan tipis berwarna tematik (merah untuk libur, biru untuk tanggal khusus, hijau untuk override hari) agar terlihat rapi dan tidak merusak estetika tata letak.
- Memisahkan status utama jam kerja (`Libur` / `Jam Masuk - Pulang`) dari kolom `keterangan` di JavaScript agar tampilan sel tanggal bersih, konsisten, dan mudah dipahami.

## Fitur Pagination pada Tabel Tanggal Khusus / Libur

- Menambahkan fitur **pagination sisi klien** (client-side pagination) pada tabel data Tanggal Khusus / Libur dengan batas maksimum **5 baris per halaman**.
- Menyematkan elemen kontrol pagination (`table-pagination-controls`) yang menampilkan keterangan halaman yang aktif (misal: *Menampilkan 1 - 5 dari 12 data*) beserta tombol navigasi **Sebelumnya** dan **Berikutnya**.
- Logika JavaScript pagination otomatis menyembunyikan kontainer kontrol navigasi jika jumlah total data kurang dari atau sama dengan 5 baris, serta melakukan disable tombol navigasi di halaman pertama dan terakhir secara dinamis dengan visual opacity `0.5` dan cursor `not-allowed`.

## Tombol Generator Password Otomatis Acak pada Tambah Peserta

- Mengganti sistem pemilihan radio button yang kompleks dengan satu input field password tunggal yang fleksibel.
- Menambahkan **tombol generator bertuliskan 'Auto'** (text button) di samping input password.
- Menulis algoritma acak (`generateRandomPassword()`) pada [super_admin_peserta.js](file:///c:/laragon/www/AbsenDJJ/resources/js/super_admin_peserta.js) yang memadukan karakter dari NIP & Nama secara acak/scrambled:
  - **Panjang acak**: berkisar antara **8 hingga 10 karakter** untuk password otomatis.
  - **Panjang manual**: minimal 8 karakter dan maksimal bebas.
  - **Format aman**: menjamin kombinasi huruf besar, huruf kecil, angka, dan karakter underscore (`_`), serta menyaring/melarang karakter spesial lainnya.
- Menampilkan SweetAlert2 dialog pop-up yang informatif ketika tombol generator 'Auto' diklik untuk memberitahukan password baru yang terbentuk dan langsung mengisi isian password secara otomatis.
- Memperkecil lebar tampilan input field password di modal Tambah Peserta (dari semula satu baris penuh `span 2` menjadi setengah baris `1 column`) agar sejajar dan seragam dengan isian data peserta lainnya.

## Perluasan Fitur Auto-Generate Password pada Reset Peserta & Kelola Pembimbing

- **Reset Password Peserta**: Menambahkan tombol **Auto** di modal Reset Password Peserta yang otomatis menghasilkan password acak (8-10 karakter) berdasarkan dataset NIP & Nama peserta aktif dan mengisi kolom Password Baru serta Konfirmasi Password secara bersamaan.
- **Tambah Pembimbing**: Menambahkan tombol **Auto** dengan posisi lebar penuh (sepanjang input no telepon) dan tombol pendukung di bawah input password pada modal Tambah Pembimbing ([super_admin_pembimbing.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/super_admin_pembimbing.blade.php)).
- **Reset Password Pembimbing**: Menambahkan tombol **Auto** di modal Reset Password Pembimbing yang mengisi kolom Password Baru & Konfirmasi secara otomatis dengan password acak aman (8-10 karakter).
- Melengkapi berkas JavaScript [super_admin_pembimbing.js](file:///c:/laragon/www/AbsenDJJ/resources/js/super_admin_pembimbing.js) dengan algoritma generator password acak berbasis NIP & Nama untuk mendukung pembimbing baru maupun proses reset.

## Pemisahan Aset CSS & JS Halaman Pengaturan (System Settings)

- Memisahkan seluruh logika JavaScript dari berkas Blade [super_admin_settings.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/super_admin_settings.blade.php) ke berkas JavaScript eksternal [super_admin_settings.js](file:///c:/laragon/www/AbsenDJJ/resources/js/super_admin_settings.js).
- Menyediakan objek konfigurasi global `window.settingsConfig` pada berkas Blade untuk mengalirkan data parameter default (`jam_masuk`, `jam_pulang`, `batas_keterlambatan`), data overrides (`dayOverrides`, `dateOverrides`), rute penyimpanan/sync, dan token CSRF ke berkas JS eksternal.
- Berkas stylesheet pendukung [super_admin_settings.css](file:///c:/laragon/www/AbsenDJJ/resources/css/super_admin_settings.css) sudah terpisah sebelumnya, sehingga aset CSS dan JS halaman pengaturan kini sepenuhnya terisolasi dari berkas Blade.
- Mendaftarkan berkas JavaScript baru tersebut ke [vite.config.js](file:///c:/laragon/www/AbsenDJJ/vite.config.js) untuk mendukung proses kompilasi otomatis menggunakan Vite.
- Memverifikasi keberhasilan integrasi aset dengan menjalankan unit test pada `SuperAdminSettingsTest` dan seluruh pengujian (11/11 tests) dinyatakan berhasil (*passed*).
- Mengimplementasikan alur impor hari libur nasional secara otomatis (pada pemanggilan halaman pengaturan) untuk tahun berjalan saat ini beserta tahun berikutnya (misalnya: 2026 dan 2027) tanpa memerlukan aksi manual atau penekanan tombol oleh pengguna.
- Menambahkan method pembantu `performSyncHolidaysForYear()` di [DashboardController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/DashboardController.php) untuk secara senyap mengunduh data hari libur dari API publik dengan limitasi timeout, mendeteksi duplikasi, dan mengisinya ke tabel `work_schedules`.
- Menghapus tombol **Import Hari Libur** dari tampilan visual [super_admin_settings.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/super_admin_settings.blade.php) serta menghapus *event listener* terkait dari [super_admin_settings.js](file:///c:/laragon/www/AbsenDJJ/resources/js/super_admin_settings.js) karena sinkronisasi kini sepenuhnya berjalan di latar belakang secara transparan.
- Memperbarui berkas pengujian [SuperAdminSettingsTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/SuperAdminSettingsTest.php) dengan menyematkan `Http::fake()` pada method `setUp()` guna memastikan seluruh skenario pengujian unit settings berjalan secara luring dan cepat tanpa melakukan koneksi jaringan nyata.

## Validasi Status Aktif pada Autentikasi Login

- Memperbarui method `login()` pada [AuthController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/AuthController.php) agar memeriksa nilai kolom `status_aktif` setelah proses autentikasi kredensial pengguna berhasil. Jika akun dalam status nonaktif (`status_aktif = false`), sistem secara otomatis mengeluarkan user, mengembalikan token sesi baru, meningkatkan hit rate limit login, dan melempar eksepsi validasi berisi pesan kesalahan *"Akun Anda dinonaktifkan. Silakan hubungi administrator."*.
- Membuat berkas test feature baru di [AuthLoginStatusTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/AuthLoginStatusTest.php) untuk secara komprehensif menguji keberhasilan login bagi akun pembimbing dan peserta yang berstatus aktif, serta penolakan login bagi akun yang dinonaktifkan.
- Memperbarui berkas [login.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/auth/login.blade.php) untuk mengimpor pustaka SweetAlert2 dan menampilkan pop-up alert kesalahan interaktif saat akun yang dinonaktifkan mencoba untuk melakukan login, serta menyembunyikan banner kesalahan default untuk kasus deaktif ini demi menjaga estetika antarmuka.
- Memverifikasi fungsionalitas dengan menjalankan seluruh test suite (`php artisan test`) dan 26/26 pengujian dinyatakan lulus (*passed*).

## Batasan Login Satu Perangkat (Single Session per Account)

- Memperbarui method `login()` pada [AuthController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/AuthController.php) agar menghapus seluruh sesi aktif lainnya di database (tabel `sessions`) bagi akun yang berhasil login (kecuali akun `super_admin`), menyisakan hanya sesi baru yang sedang aktif saat ini.
- Membuat berkas test feature baru di [AuthSingleSessionTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/AuthSingleSessionTest.php) untuk memverifikasi bahwa login akun pembimbing (admin) secara otomatis menghapus sesi lainnya, sementara akun superadmin tetap dapat mempertahankan beberapa sesi aktif secara bersamaan.
- Memverifikasi fungsionalitas dengan menjalankan seluruh test suite (`php artisan test`) dan seluruh pengujian (28/28 tests) dinyatakan lulus (*passed*).

## Reorganisasi Struktur Berkas View, JS, dan CSS Berdasarkan Peran Pengguna (Role-Based Reorganization)

- Mengelompokkan seluruh berkas visual (Views), logika client-side (JS), dan stylesheet (CSS) ke dalam subfolder yang terpisah berdasarkan peran pengguna (*super_admin*, *admin*, dan *peserta*):
  - **Super Admin**:
    - Views: `dashboard/super_admin/dashboard.blade.php`, `instansi.blade.php`, `pembimbing.blade.php`, `peserta.blade.php`, `settings.blade.php`
    - JS: `resources/js/super_admin/dashboard.js`, `instansi.js`, `pembimbing.js`, `peserta.js`, `settings.js`
    - CSS: `resources/css/super_admin/dashboard.css`, `instansi.css`, `pembimbing.css`, `peserta.css`, `settings.css`
  - **Admin (Pembimbing)**:
    - Views: `dashboard/admin/dashboard.blade.php`
    - JS: `resources/js/admin/dashboard.js`
    - CSS: `resources/css/admin/dashboard.css`
  - **Peserta**:
    - Views: `dashboard/peserta/dashboard.blade.php`
    - JS: `resources/js/peserta/dashboard.js`
    - CSS: `resources/css/peserta/dashboard.css`
- Memperbarui [DashboardController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/DashboardController.php) untuk memuat berkas Blade menggunakan referensi path subfolder baru (misal: `dashboard.super_admin.dashboard`).
- Menyesuaikan import `@vite()` pada masing-masing berkas Blade dan mendaftarkan pembaruan entri path aset ke dalam [vite.config.js](file:///c:/laragon/www/AbsenDJJ/vite.config.js).
- Memvalidasi keberhasilan reorganisasi dengan melakukan build kompilasi produksi (`npm run build`) dan menjalankan seluruh test suite (`php artisan test`) yang mencatatkan 28/28 pengujian lulus (*passed*).

## Modularisasi Controller (Decoupling Monolithic DashboardController)

- Memecah berkas controller monolitik `DashboardController.php` menjadi beberapa controller yang fokus dan terisolasi berdasarkan fitur dan peran pengguna:
  - **DashboardController**: Berfungsi sebagai router/delegator umum yang meneruskan request `/dashboard` ke controller spesifik sesuai peran user yang login.
  - **SuperAdmin\DashboardController**: Menangani logic dan visualisasi data statistik dashboard Super Admin.
  - **SuperAdmin\PembimbingController**: Menangani CRUD, reset password, dan manajemen akun pembimbing (admin).
  - **SuperAdmin\PesertaController**: Menangani CRUD, reset password, dan manajemen akun peserta magang.
  - **SuperAdmin\InstansiController**: Menangani manajemen instansi sekolah/perguruan tinggi.
  - **SuperAdmin\SettingsController**: Menangani pengaturan parameter global, override jadwal, serta sinkronisasi otomatis/manual hari libur nasional.
  - **Admin\DashboardController**: Menangani dashboard pembimbing dan data anak bimbingan.
  - **Peserta\DashboardController**: Menangani dashboard peserta magang beserta status absensi hari ini.
- Memperbarui berkas rute [web.php](file:///c:/laragon/www/AbsenDJJ/routes/web.php) agar menunjuk ke masing-masing controller baru yang sesuai untuk setiap endpoint rute.
- Memvalidasi keberhasilan modularisasi controller dengan menjalankan seluruh test suite (`php artisan test`) dan seluruh pengujian (28/28 tests) lulus tanpa kendala (*passed*).

