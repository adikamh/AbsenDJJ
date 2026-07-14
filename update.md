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

## Upgrade Dashboard dan Fitur Peserta Magang (Intern)

- **Modul Pencatatan Logbook Kegiatan**:
  - Mengintegrasikan Modal Popup Tambah Logbook pada dashboard utama peserta magang untuk memasukkan Tanggal, Judul Kegiatan, dan Deskripsi Kegiatan secara langsung.
  - Membuat halaman khusus baru di `/peserta/logbook` untuk menampilkan seluruh daftar logbook dengan paginasi (10 entri per halaman) serta aksi Edit dan Hapus bagi logbook yang masih berstatus `Pending`.
  - Membuat layout ekspor logbook cetak di `/peserta/logbook/export-pdf` dengan format KOP Surat resmi Kementerian Pekerjaan Umum dan Perumahan Rakyat (PUPR) yang terintegrasi tombol Cetak (`window.print()`).
  - Membuat `App\Http\Controllers\Peserta\LogbookController` untuk mengendalikan proses penayangan, penyimpanan, pengubahan, penghapusan, dan ekspor data logbook.
- **Modul Pengajuan Izin / Sakit**:
  - Mengintegrasikan Modal Popup Pengajuan Izin/Sakit pada dashboard utama peserta magang untuk memasukkan Tanggal Mulai, Tanggal Selesai, Jenis Pengajuan (Izin / Sakit), Alasan, dan Upload Dokumen Bukti (Surat Dokter / Lampiran).
  - Membuat `App\Http\Controllers\Peserta\LeaveRequestController` untuk menangani penyimpanan pengajuan izin/sakit beserta upload berkas bukti secara aman ke direktori `public/uploads/leave_proofs/`.
- **Halaman Visual Riwayat Kehadiran (Attendance Calendar)**:
  - Mengubah rute halaman `/peserta/my-attendance` yang sebelumnya statis menjadi halaman visualisasi kalender bulanan interaktif.
  - Setiap sel tanggal pada kalender diberi kode warna sesuai status absensi: Hijau (Hadir), Kuning/Oranye (Terlambat), Biru (Izin/Sakit), Merah (Alfa/Tanpa Keterangan), dan Abu-abu (Hari Libur/Akhir Pekan).
  - Menghitung statistik akumulatif bulanan (Total Hadir, Terlambat, Izin, Alfa) untuk ditampilkan dalam bentuk widget kartu analitik.
  - Membuat `App\Http\Controllers\Peserta\AttendanceHistoryController` untuk mengurus rendering data kalender dan statistik kehadiran.
- **Upgrade Antarmuka Dashboard**:
  - Menambahkan 3 kartu metrik analitik baru di atas dashboard peserta magang: *Persentase Kehadiran*, *Total Logbook Disetujui*, dan *Izin/Sakit Terpakai*.
  - Menambahkan menu navigasi sidebar "Logbook Kegiatan" di [layout.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/layout.blade.php) dan menghubungkan "Riwayat Absen" ke halaman kalender baru.
- **Mendaftarkan Aset ke Vite**:
  - Mendaftarkan berkas JavaScript baru `resources/js/peserta/logbook.js` ke dalam [vite.config.js](file:///c:/laragon/www/AbsenDJJ/vite.config.js) untuk kompilasi otomatis.
- **Pengujian Otomatis (Testing)**:
  - Membuat berkas test feature baru di [PesertaLogbookTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/PesertaLogbookTest.php) untuk memverifikasi fungsionalitas CRUD logbook dan batasan edit/hapus logbook disetujui.
  - Membuat berkas test feature baru di [PesertaLeaveRequestTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/PesertaLeaveRequestTest.php) untuk memvalidasi alur input form izin, upload berkas bukti, dan validasi rentang tanggal.
  - Menjalankan seluruh test suite (`php artisan test`) dan seluruh **36/36 pengujian dinyatakan lulus** (*passed*).

## Watermark Kamera Absensi, Geocoding Alamat, dan Peta Lokasi

- **Watermark Logo Kementerian PUPR**:
  - Menambahkan logo absensi di pojok kiri atas foto selfie (`public/images/Logo/logo_absen.png`) dengan ukuran proporsional (lebar ~45% kanvas) menjaga aspect ratio asli.
- **Peta Lokasi Mini (Mini Map)**:
  - Menyematkan peta mini ukuran `140px x 140px` dengan border putih tipis di pojok kiri bawah foto menggunakan Yandex Static Maps API (`https://static-maps.yandex.ru/1.x/`) dengan pin penanda lokasi (`pm2rdm`) berdasarkan GPS peserta secara real-time.
  - Konfigurasi Yandex API diset `crossOrigin = "anonymous"` agar aman diunduh dan dipasang pada kanvas tanpa memicu SecurityError saat konversi Base64.
- **Informasi Geocoding Alamat & Waktu**:
  - Mengintegrasikan Nominatim OpenStreetMap API untuk melakukan reverse geocoding koordinat GPS secara real-time menjadi alamat lengkap (Jalan, Kelurahan/Kecamatan, Kota, Provinsi).
  - Menggambar kotak informasi semi-transparan (`rgba(15, 23, 42, 0.7)`) dengan sudut membulat (*rounded corners*) di pojok kanan bawah foto untuk menampung teks putih (Tanggal/Waktu, Koordinat Latitude & Longitude, serta Alamat Lengkap) sehingga tulisan tetap kontras dan mudah dibaca di atas latar belakang foto apa pun.
- **Penanganan Loading & Fallback**:
  - Mematikan tombol dan mengubah teks menjadi `"Memproses Foto..."` saat asinkronus rendering sedang berlangsung.
  - Menangani error secara mandiri (*graceful fallback*) sehingga jika koneksi internet terputus atau API maps/geocoding lambat merespon, absensi tetap dapat dilanjutkan dengan data koordinat & timestamp default agar tidak mengganggu operasional pengguna.

## Relokasi dan Pembersihan Kartu Widget Analitik

- **Relokasi Persentase Kehadiran**:
  - Memindahkan kartu widget analitik "Persentase Kehadiran" dari dashboard utama peserta ke halaman **Riwayat Absen** (`attendance_history.blade.php`).
  - Memperbarui `AttendanceHistoryController.php` untuk memproses persentase kehadiran kumulatif secara dinamis dan mengalirkannya ke dalam view riwayat absensi.
- **Relokasi Logbook Disetujui**:
  - Memindahkan kartu widget analitik "Logbook Disetujui" dari dashboard utama peserta ke halaman **Logbook Kegiatan** (`logbook.blade.php`) di bagian atas daftar tabel.
  - Memperbarui `LogbookController.php` untuk menghitung berkas logbook berstatus `Approved` milik peserta aktif secara real-time dan mengalirkannya ke view logbook.
- **Pembersihan Kartu Izin & Sakit (Disetujui)**:
  - Menghapus kartu widget analitik "Izin & Sakit (Disetujui)" dari dashboard utama peserta magang sesuai instruksi.
  - Menyederhanakan `DashboardController.php` (Peserta) agar tidak lagi memproses variabel widget yang tidak terpakai sehingga beban query database berkurang dan rendering dashboard menjadi lebih ringan.

## Perbaikan Status Kehadiran Hari Ini pada Dashboard

- **Deteksi Keterlambatan Absen**:
  - Menyesuaikan visualisasi lencana status pada kartu "Kehadiran Hari Ini" di dashboard utama peserta magang. Jika peserta absen masuk melebihi batas jam kerja hari berjalan yang ditetapkan oleh superadmin, lencana otomatis berubah warna menjadi kuning/oranye dengan keterangan `"Terlambat"`.
- **Integrasi Status Izin & Sakit**:
  - Memperbarui `DashboardController.php` (Peserta) untuk memeriksa apakah terdapat pengajuan `LeaveRequest` berstatus `Approved` milik peserta aktif yang mencakup tanggal hari berjalan.
  - Jika peserta tidak melakukan absen masuk tetapi memiliki surat izin/sakit yang disetujui untuk hari tersebut, kartu "Kehadiran Hari Ini" otomatis menampilkan status `"Izin"` atau `"Sakit"` berwarna biru (`badge-info`) sebagai pengganti teks default `"Belum Absen"`.
- **Pengujian**:
  - Menambahkan pengujian unit baru `test_dashboard_displays_today_approved_leave` pada [PesertaLeaveRequestTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/PesertaLeaveRequestTest.php) untuk mengunci keabsahan pemuatan status izin/sakit hari berjalan pada tampilan dashboard utama.

## Jam Masuk & Jam Pulang Berdasarkan Jadwal Kerja

- **Pengambilan Waktu Sesuai Jadwal (Superadmin)**:
  - Mengubah kartu "Jam Masuk" dan "Jam Pulang" di dashboard peserta agar menampilkan batas jam kerja resmi yang ditetapkan oleh Superadmin untuk hari berjalan.
  - Alur pembacaan waktu target didasarkan pada prioritasi model `WorkSchedule` (override tanggal khusus atau override hari kerja reguler) dan jika tidak ada override, otomatis menggunakan konfigurasi `GeneralSettings`.
- **Penanganan Hari Libur / Akhir Pekan**:
  - Apabila hari berjalan terdeteksi sebagai hari libur (baik berdasarkan kalender libur nasional/khusus yang terdaftar di `WorkSchedule` maupun akhir pekan tanpa jadwal pengganti), kedua kartu jam tersebut otomatis dikosongkan dan menampilkan tanda default (`--:--`) sebagai penanda tidak ada jam wajib hadir pada hari tersebut.

## Tampilan Jam Absen Riil di Samping Jam Jadwal Kerja

- **Informasi Gabungan (Jadwal & Riil)**:
  - Menyandingkan target jam kerja resmi dengan waktu absen riil peserta di dalam kartu "Jam Masuk" dan "Jam Pulang".
  - Sebagai contoh, apabila target masuk adalah `08:00` dan peserta absen masuk pukul `07:45`, kartu menampilkan `"08:00 (Absen: 07:45)"`.
  - Jika peserta belum melakukan absen masuk/pulang, kartu otomatis menampilkan keterangan `(Absen: --:--)` di samping target jam kerja.

## Fitur Pemilih Kamera dan Optimalisasi Ukuran Preview Foto

- **Dropdown Pemilih Kamera (Multiple Cameras)**:
  - Mengintegrasikan elemen `<select>` dropdown pilihan kamera pada kontrol absen masuk dan absen pulang agar mendukung perangkat handphone yang memiliki lebih dari satu kamera (kamera depan, kamera belakang, wide, dll.).
  - Menggunakan API `navigator.mediaDevices.enumerateDevices` untuk mendeteksi seluruh perangkat input video yang tersedia secara dinamis dan memicu pembaruan nama kamera secara otomatis setelah izin akses kamera disetujui.
  - Mengubah stream video secara langsung saat pengguna mengganti pilihan kamera pada dropdown.
- **Ukuran Preview Foto Selfie yang Ringkas**:
  - Mengubah tampilan foto selfie hasil potret absensi agar tidak memenuhi layar dengan lebar 100%. Gambar pratinjau selfie (preview) diset berukuran ringkas (`max-width: 160px`) dengan border membulat (*rounded corners*) yang terletak rapi di tengah kontainer.

## Pratinjau Thumbnail Selfie Tersimpan & Modal Popup Pembesaran

- **Thumbnail Foto Tersimpan (80x80px)**:
  - Menyusun letak foto masuk dan foto pulang yang telah sukses disimpan ke dalam baris berdampingan (`saved-selfies-row`) dengan resolusi mini `80px x 80px`. Hal ini menjaga agar kartu Kontrol Kehadiran Harian tetap padat, responsif, dan tidak melebar secara vertikal.
  - Menambahkan efek hover `.clickable-selfie:hover` berupa perbesaran skala halus (`scale(1.08)`) dan peningkatan kecerahan agar interaktif.
- **Popup Modal Pembesaran Foto (Enlarge Selfie Modal)**:
  - Membuat elemen overlay modal `#selfie-modal` yang memanfaatkan kerangka `.form-modal-backdrop` bawaan sistem agar terlihat menyatu dengan sisa UI lainnya.
  - Menghubungkan klik thumbnail ke fungsi global `window.showImageModal` untuk menampilkan foto ukuran penuh beserta label keterangan yang sesuai dalam popup modal secara asinkron.

## Integrasi Nama Lokasi Alamat Lengkap pada Panel Kehadiran Utama

- **Penerjemahan Koordinat Menjadi Alamat Jalan**:
  - Mengintegrasikan Nominatim OpenStreetMap API pada panel utama GPS untuk menerjemahkan koordinat latitude/longitude secara asinkronus menjadi nama jalan dan wilayah administrasi lengkap (Negara/Provinsi/Kota/Kecamatan/Jalan).
  - Saat memuat halaman atau saat GPS berhasil mengunci koordinat, dashboard akan menampilkan status di bawah koordinat tersebut menggunakan icon SVG penunjuk lokasi (menggantikan penggunaan emoji).

## Perhitungan Jarak Real-Time & Deteksi Zona Radius Kehadiran

- **Parameter Kantor Dinamis**:
  - Mengalirkan koordinat kantor (`latitude_kantor` dan `longitude_kantor`) serta batas `radius_meter` dari `GeneralSettings` ke dashboard menggunakan atribut data pada elemen HTML.
- **Kalkulasi Jarak Haversine & Visualisasi Warna**:
  - Menggunakan rumus Haversine di client-side untuk menghitung jarak presisi (dalam meter) antara koordinat GPS peserta magang dengan koordinat kantor secara dinamis.
  - Jika posisi peserta berada di dalam batas radius yang diperbolehkan (`distance <= radius`), informasi jarak ditampilkan berwarna hijau (`#34d399`) dengan indikator `"Di dalam zona"` dan ikon centang SVG.
  - Jika posisi peserta melebihi radius, informasi jarak ditampilkan berwarna merah (`#f87171`) dengan indikator `"Di luar zona"` dan ikon peringatan SVG.
  - Seluruh emoji dan emotikon pada panel ini telah dihapus sepenuhnya dan digantikan dengan ikon SVG modern.

## Perbaikan Tata Letak Kamera & Foto Tersimpan pada Kartu Kontrol Kehadiran Harian

- **Alur Kamera Terpadu (Buka Kamera → Pilih Kamera → Mulai Kamera)**:
  - Menggabungkan tombol "Buka Kamera" dan dropdown "Pilih Kamera" menjadi satu alur terpadu. Saat peserta menekan "Buka Kamera", panel pemilih kamera (`#camera-select-wrap` / `#camera-select-out-wrap`) muncul terlebih dahulu beserta daftar perangkat kamera yang terdeteksi. Setelah pengguna memilih kamera dan menekan tombol "Mulai Kamera", baru stream video kamera aktif.
  - Dropdown pemilih kamera (`select`) disembunyikan secara default (`display: none`) dan hanya ditampilkan saat tombol "Buka Kamera" ditekan, menjaga agar tampilan kartu tetap bersih.
  - Saat pengguna menekan "Foto Ulang", alur kembali ke panel pemilih kamera agar pengguna dapat mengganti perangkat kamera jika diinginkan.
- **Ukuran Preview Kamera yang Proporsional**:
  - Mengatur lebar maksimum video stream preview kamera menjadi `240px` (sebelumnya `280px`) melalui class CSS `.camera-video-preview` agar tidak terlalu besar maupun terlalu kecil, terutama di layar handphone.
  - Mengatur lebar hasil foto selfie yang sudah dipotret menjadi `140px` melalui class CSS `.camera-selfie-result`.
- **Foto Masuk & Pulang Bersebelahan**:
  - Foto check-in dan check-out yang telah tersimpan tetap ditampilkan secara berdampingan (*side-by-side*) menggunakan kontainer `saved-selfies-row` dengan `display: flex` sehingga tidak memanjang ke bawah secara vertikal.
- **Ikon SVG pada Tombol Kamera**:
  - Menambahkan ikon kamera SVG di dalam tombol "Buka Kamera" dan "Mulai Kamera" baik untuk absen masuk maupun absen pulang agar tampilan lebih informatif dan konsisten.

## Penyempurnaan Kartu Informasi Pembimbing Lapangan

- **Informasi Pembimbing Lengkap**:
  - Memperluas kartu "Pembimbing Lapangan" pada dashboard peserta dari hanya menampilkan nama, email, dan instansi menjadi menampilkan informasi lengkap secara terstruktur: NIP, Email, No. Telepon, Instansi, dan Status (Aktif/Nonaktif).
  - Data Alamat dan Password sengaja tidak ditampilkan sesuai permintaan.
  - Menggunakan tata letak baris detail (`.supervisor-detail-row`) dengan label di kiri dan nilai di kanan untuk keterbacaan yang rapi dan responsif.
  - Status pembimbing ditampilkan menggunakan badge berwarna hijau (Aktif) atau merah (Nonaktif).
  - Jika pembimbing belum ditugaskan, kartu menampilkan pesan fallback "Pembimbing belum ditugaskan."

## Update 2026-07-13

### Sinkronisasi Zona Waktu (Server-side & Client-side)

- Mengubah default timezone Laravel di [config/app.php](file:///c:/laragon/www/AbsenDJJ/config/app.php) dari `UTC` ke `'Asia/Jakarta'` (WIB) menggunakan `env('APP_TIMEZONE', 'Asia/Jakarta')`.
- Menambahkan baris `APP_TIMEZONE=Asia/Jakarta` pada berkas [.env](file:///c:/laragon/www/AbsenDJJ/.env) dan [.env.example](file:///c:/laragon/www/AbsenDJJ/.env.example).
- Melakukan sinkronisasi pencarian record presensi harian di [AttendanceController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/AttendanceController.php) dan dashboard peserta dengan mengubah query dari `where('tanggal', ...)` menjadi `whereDate('tanggal', ...)` guna melindungi database SQLite dari shifting timezone saat test suite berjalan.

### Tampilan Jam Digital Real-time (Live Clock) di Header

- Menyisipkan komponen digital clock (`#digital-clock`) dan tanggal (`#digital-date`) pada `.card-header` kartu Kontrol Kehadiran Harian di halaman [dashboard.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/dashboard.blade.php).
- Menghubungkan jam digital ke inisialisasi server timestamp (`data-server-timestamp`) untuk mendeteksi deviasi/offset waktu antara browser klien dengan server, sehingga jam digital berdetak secara real-time sinkron dengan server.
- Merancang CSS styling `.digital-clock-container` khusus header di [dashboard.css](file:///c:/laragon/www/AbsenDJJ/resources/css/peserta/dashboard.css) agar berpenampilan ringkas, berukuran kecil, dan terposisi rapi di sisi kanan atas header kartu.

### Validasi Wajib Logbook Sebelum Absen Pulang

- Memperbarui method `checkOut` di [AttendanceController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/AttendanceController.php) agar memvalidasi apakah peserta sudah mengisi minimal 1 logbook pada hari berjalan. Jika belum, server akan menolak request check-out dan mengembalikan pesan error.
- Memperbarui dashboard controller peserta [DashboardController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/DashboardController.php) untuk memproses jumlah logbook hari ini (`todayLogbooksCount`) dan mengalirkannya ke view [dashboard.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/dashboard.blade.php) pada data atribut tombol `btn-submit-out`.
- Memodifikasi [dashboard.js](file:///c:/laragon/www/AbsenDJJ/resources/js/peserta/dashboard.js) untuk memeriksa jumlah logbook hari ini di sisi klien saat tombol "Buka Kamera" dan "Absen Pulang" ditekan. Pengguna akan dicegah dari mengambil foto dan melakukan submit dengan menampilkan alert jika logbook masih kosong.
- Menulis berkas test feature baru [AttendanceCheckOutTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/AttendanceCheckOutTest.php) untuk secara komprehensif memvalidasi aturan check-out (belum check-in, belum mengisi logbook, dan sukses check-out setelah mengisi logbook). Seluruh suite test passed.

### Integrasi Dialog SweetAlert2 di Dashboard Peserta

- Menggantikan semua penggunaan fungsi `alert()` dan `confirm()` bawaan browser pada JavaScript peserta [dashboard.js](file:///c:/laragon/www/AbsenDJJ/resources/js/peserta/dashboard.js) dengan dialog kustom **SweetAlert2** (`Swal.fire`).
- Merancang helper di Javascript klien (`showSwalSuccess`, `showSwalError`, dan `showSwalConfirm`) yang secara dinamis menyesuaikan skema warna popup (background, warna teks, tombol konfirmasi) berdasarkan tema aktif (light/dark mode) di dashboard.
- Memperbarui penanganan error kamera, kesalahan reverse geocoding/geofencing, kegagalan request absensi, notifikasi validasi data kosong, serta dialog konfirmasi absen pulang agar seragam menggunakan antarmuka SweetAlert2 premium.

### Fitur Bundel Laporan Bulanan Peserta Magang

- Menambahkan route baru `/peserta/my-attendance/monthly-report` di [routes/web.php](file:///c:/laragon/www/AbsenDJJ/routes/web.php) untuk mengarahkan ke pembuatan laporan bulanan peserta.
- Mengimplementasikan method `exportMonthlyReport` di [AttendanceHistoryController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/AttendanceHistoryController.php) yang memproses filter bulan/tahun, menghitung ringkasan statistik kehadiran bulanan (total masuk, terlambat, izin, sakit, alfa, persentase kehadiran), serta memuat semua entri logbook terkait.
- Merancang halaman cetak PDF premium [monthly_report_pdf.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/monthly_report_pdf.blade.php) yang dilengkapi kop resmi Kementrian PUPR Direktorat Bina Teknik Jalan dan Jembatan, rekap kehadiran bulanan, rincian absensi harian, laporan kegiatan harian (logbook) bulanan, serta bagian tanda tangan pembimbing lapangan & peserta.
- Menyediakan tombol "Cetak Laporan" di halaman kalender riwayat kehadiran [attendance_history.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/attendance_history.blade.php) yang secara dinamis mencetak laporan sesuai bulan dan tahun yang sedang dipilih oleh peserta.

### Fitur Ekspor CSV Mandiri (Logbook & Kehadiran)

- Menambahkan route baru `/peserta/my-attendance/csv` dan `/peserta/logbook/export-csv` di [routes/web.php](file:///c:/laragon/www/AbsenDJJ/routes/web.php).
- Mengimplementasikan penanganan aliran data (stream response) CSV di [LogbookController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/LogbookController.php) (`exportCsv`) dan [AttendanceHistoryController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/AttendanceHistoryController.php) (`exportCsv`).
- Mendukung filter bulanan dinamis pada ekspor CSV absensi maupun logbook, lengkap dengan penambahan penanda UTF-8 BOM dan direktif pembatas `sep=;` di baris pertama berkas agar dokumen langsung terbaca terbagi menjadi kolom-kolom tabel yang rapi saat dibuka di Microsoft Excel/Google Sheets tanpa penyesuaian manual.
- Menambahkan tombol **Ekspor CSV Absen** di halaman riwayat absensi [attendance_history.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/attendance_history.blade.php) dan tombol **Ekspor CSV** di halaman daftar logbook [logbook.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/logbook.blade.php).

### Fitur Halaman Izin & Sakit Khusus Peserta

- Menambahkan menu **Izin / Sakit** pada sidebar navigasi [layout.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/layout.blade.php) khusus untuk role peserta.
- Menambahkan route GET `/peserta/leave-request` di [routes/web.php](file:///c:/laragon/www/AbsenDJJ/routes/web.php).
- Mengimplementasikan method `index` di [LeaveRequestController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/LeaveRequestController.php) yang memproses daftar permohonan izin/sakit dengan pagination (maksimal 5 entri per halaman) serta menyediakan form pencarian & filter berdasarkan jenis pengajuan dan status persetujuan.
- Merancang halaman antarmuka [leave.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/leave.blade.php) yang didalamnya menyatukan tabel riwayat pengajuan dan panel filter pencarian ke dalam satu kartu terintegrasi (*single-card layout*), serta menghilangkan tombol cari manual agar list data melakukan pembaruan otomatis (*auto-refresh*) seketika opsi filter/pencarian diubah.
- Menambahkan kartu ringkasan statistik **Izin Disetujui** dan **Sakit Disetujui** di bagian atas halaman izin/sakit untuk memantau akumulasi total persetujuan pengajuan secara real-time.
- Memperbarui pengalihan (redirect) pada proses penyimpanan pengajuan izin/sakit agar mengarah kembali ke halaman riwayat izin/sakit (`peserta.leave`), serta memperbarui assert redirect pada berkas pengujian [PesertaLeaveRequestTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/PesertaLeaveRequestTest.php).
- Mengubah perilaku kartu "Pengajuan Izin Sakit" di dashboard utama peserta [dashboard.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/dashboard.blade.php) agar hanya memfungsikan kartu tersebut sebagai ringkasan (highlight) dari 5 data pengajuan terbaru, serta mengubah tombol "Ajukan" di header kartu menjadi link "Selengkapnya" ke halaman izin/sakit baru.

### Fitur Pencarian & Pagination Logbook Kegiatan Peserta

- Memperbarui method `index` di [LogbookController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/LogbookController.php) agar memproses input pencarian kegiatan/deskripsi dan filter status approval, serta membatasi pagination maksimal **5 entri per halaman**.
- Merancang ulang antarmuka [logbook.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/logbook.blade.php) menggunakan *single-card layout* terintegrasi (menyatukan form filter pencarian dan tabel daftar logbook) dan mendukung pembaruan otomatis (*auto-refresh*) pada input pencarian dan select dropdown status.
- Mengubah perilaku kartu "Logbook Kegiatan Terbaru" di dashboard utama peserta [dashboard.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/dashboard.blade.php) agar hanya berfungsi sebagai highlight dari 5 data logbook terbaru saja, serta mengubah tombol "Tulis Baru" di header kartu menjadi link "Selengkapnya" ke halaman logbook utama.

### Fitur Draft Logbook (Simpan Sementara) & Tagging Kegiatan

- Membuat database migration `2026_07_13_113329_add_kategori_and_tags_to_logbooks_table.php` untuk menambahkan kolom `tags` pada tabel `logbooks`, lalu menjalankannya.
- Memperbarui model [Logbook.php](file:///c:/laragon/www/AbsenDJJ/app/Models/Logbook.php) agar field `tags` masuk dalam list fillable attributes.
- Memperbarui method `store`, `update`, dan `destroy` pada [LogbookController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/LogbookController.php) agar mendukung penyimpanan status `'Draft'` (Simpan Sementara) jika tombol draft diklik, serta membolehkan pengubahan dan penghapusan data logbook yang berstatus `'Draft'`.
- Menambahkan field `Tags` pada ekspor berkas CSV dalam method `exportCsv` di [LogbookController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/LogbookController.php).
- Memperbarui halaman cetak PDF [logbook_pdf.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/logbook_pdf.blade.php) untuk menampilkan metadata tags di bawah judul tugas/kegiatan secara rapi.
- Memperbarui file view [logbook.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/logbook.blade.php) dengan menyajikan tag-badge di bawah judul kegiatan, menambahkan text input Tag pada modal tambah & edit, serta tombol submit ganda "Simpan Draft" dan "Kirim ke Pembimbing".
- Menambahkan 3 unit testing baru di [PesertaLogbookTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/PesertaLogbookTest.php) untuk memverifikasi fungsionalitas pembuatan, pembaruan publikasi, dan penghapusan draft logbook.

### Fitur Notifikasi & Pengingat (Reminders)

- Menjalankan migrasi tabel notifikasi bawaan Laravel untuk menyimpan notifikasi persetujuan logbook dan perizinan.
- Membuat kelas `App\Notifications\AbsenNotification` berbasis database channel untuk mengirim notifikasi secara terstruktur.
- Memperbarui `App\Http\Controllers\Admin\DashboardController.php` dengan menambahkan metode persetujuan (`approveLogbook`, `rejectLogbook`, `approveLeave`, `rejectLeave`) yang secara otomatis memicu pengiriman notifikasi ke intern yang bersangkutan.
- Mengubah tampilan tombol Tinjauan Logbook dan Setuju/Tolak Izin di dashboard pembimbing menjadi form submit aksi yang fully-functional.
- Menambahkan ikon notifikasi lonceng dan dropdown panel notifikasi (*glassmorphic style*) pada top-header [layout.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/layout.blade.php).
- Mengintegrasikan script penanganan notifikasi real-time via REST API polling (setiap 30 detik) dan fitur **Push Notifications** bawaan browser untuk pengingat absen masuk (jika belum absen setelah jam 07:30) serta pengingat mengisi logbook & absen pulang (jika belum absen pulang setelah jam 15:30) pada [dashboard-layout.js](file:///c:/laragon/www/AbsenDJJ/resources/js/dashboard-layout.js).
- Membangun kembali asset klien (`npm run build`).
- Menambahkan 3 unit pengujian baru di [NotificationTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/NotificationTest.php) untuk memverifikasi fungsionalitas pengambilan notifikasi, penandaan dibaca, dan pengiriman notifikasi dari pembimbing (Semua 46 test suite dinyatakan **Lulus**).

### Peningkatan Fitur & Halaman Kelola Anak Bimbingan (Pembimbing Lapangan)

- **Halaman Daftar Anak Bimbingan Terpadu ([index.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/admin/interns/index.blade.php)):**
  - Menyatukan baris filter/pencarian langsung ke dalam header tabel **Daftar Aktivitas Intern** untuk efisiensi ruang layar.
  - Membatasi daftar bimbingan maksimal **5 data per halaman** menggunakan paginasi server, sementara stat card total bimbingan tetap menghitung keseluruhan data.
- **Halaman Logbook Anak Didik Khusus ([index.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/admin/logbooks/index.blade.php)):**
  - Membuat route baru GET `/admin/logbooks` yang dihubungkan ke `InternController@logbooks`.
  - Menyatukan kolom pencarian/status filter langsung ke dalam header tabel **Daftar Logbook Intern**.
  - Menyederhanakan kolom tabel utama dengan menyembunyikan kolom *Deskripsi*, *Tag*, dan *Catatan Pembimbing* dari tabel utama.
  - Menambahkan 3 kartu statistik premium di bagian atas halaman (Logbook Pending, Logbook Disetujui, dan Logbook Ditolak) dengan total riil.
  - Menambahkan tombol **Detail** yang membuka modal pop-up detail kegiatan logbook lengkap dengan rincian deskripsi, tag label, status, dan catatan pembimbing.
- **Halaman Izin & Sakit Anak Didik Khusus ([index.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/admin/leaves/index.blade.php)):**
  - Membuat route baru GET `/admin/leaves` yang dihubungkan ke `InternController@leaves`.
  - Menyatukan panel filter pencarian, status perizinan, dan jenis permohonan ke header tabel **Daftar Pengajuan Izin / Sakit**.
  - Menyederhanakan kolom tabel utama dengan menyembunyikan kolom *Alasan*, *Bukti*, dan *Catatan Pembimbing* dari tabel utama.
  - Menambahkan 3 kartu statistik premium di bagian atas halaman (Izin/Sakit Pending, Izin/Sakit Disetujui, dan Izin/Sakit Ditolak) dengan total riil.
  - Menambahkan tombol **Detail** yang membuka modal pop-up detail permohonan lengkap dengan rincian tanggal, alasan, tautan unduh bukti, status, dan catatan pembimbing.
- **Redesain Dashboard Pembimbing ([dashboard.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/admin/dashboard.blade.php)):**
  - Menyediakan 4 kartu statistik premium dengan indikator total riil dan ikon SVG.
  - Membatasi seluruh tabel dashboard (Logbook, Intern, Izin/Sakit) hanya menampilkan **maksimal 3 highlight data**, lengkap dengan tautan dinamis "Lihat Semua Pending" di footer kartu jika total data melebihi 3.
- **Modal Pop-Up Konfirmasi & Catatan Pembimbing:**
  - Memindahkan input teks *Catatan Pembimbing* yang sebelumnya tersebar di baris tabel menjadi satu **Modal Pop-up Konfirmasi** global yang elegan.
  - Berlaku pada persetujuan/penolakan logbook dan izin di halaman Dashboard, Logbook review, dan Leaves review.
  - Kolom catatan bersifat opsional saat tombol "Setujui" diklik, dan bersifat wajib (wajib diisi alasan) saat tombol "Tolak" diklik.
- **Sinkronisasi Mode Gelap & Terang:**
  - Mengubah warna teks statis (`#fff`) pada judul modal, data input filter, placeholder, textarea catatan pembimbing, dan nilai statistik pembimbing menggunakan CSS variables dinamis `var(--text-primary)` agar warna teks bertransisi dengan sempurna saat berganti tema dan menjaga keterbacaan penuh.
- **Pemisahan Script & Style Bersih:**
  - Memisahkan seluruh kode JavaScript inline di halaman `dashboard.blade.php`, `logbooks/index.blade.php`, dan `leaves/index.blade.php` ke dalam berkas JS tersendiri: `resources/js/admin/dashboard.js`, `resources/js/admin/logbooks.js`, dan `resources/js/admin/leaves.js`.
  - Memisahkan inline styles `<style>` dari `dashboard.blade.php` ke berkas `resources/css/admin/dashboard.css`.
  - Mendaftarkan seluruh berkas aset tersebut ke `vite.config.js` sehingga terkompilasi optimal oleh Vite.
- **Pengujian Fungsional Tambahan:**
  - Menyusun 9 kasus pengujian baru di berkas [AdminInternsTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/AdminInternsTest.php) untuk memvalidasi paginasi max 5, pencarian, RBAC 403, highlight dashboard max 3, filter approval logbook, filter leaves, dan tindakan persetujuan modal. Seluruh 55 pengujian passed.

## Update Tambahan (PWA, Cookie Consent, & Keamanan Akses)

### Dukungan Progressive Web Application (PWA / WPA)
- **Manifestasi Aplikasi**: Membuat berkas [manifest.json](file:///c:/laragon/www/AbsenDJJ/public/manifest.json) untuk mendefinisikan metadata PWA (nama aplikasi "Absen Magang", URL awal, warna tema, display standalone, dan ikon aplikasi).
- **Service Worker**: Menyediakan berkas [sw.js](file:///c:/laragon/www/AbsenDJJ/public/sw.js) untuk menangani caching aset utama (`/`, `/css/app.css`, `/js/app.js`) dengan strategi cache-first.
- **Registrasi Service Worker**: Menyisipkan skrip registrasi Service Worker pada bagian bawah berkas layout utama [layout.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/layout.blade.php).
- **Meta Tags & Aset PWA**: Menghubungkan manifest serta menambahkan meta tag `theme-color` dan tautan `apple-touch-icon` pada layout dashboard dan halaman depan [welcome.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/welcome.blade.php).

### Sistem Kebijakan & Persetujuan Cookie (Cookie Consent)
- **Backend Controller & Sesi**: Membuat [CookieConsentController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/CookieConsentController.php) untuk menyimpan status persetujuan cookie (accepted/declined) ke dalam data sesi dan cookie HTTP terenkripsi dengan masa kedaluwarsa 1 tahun.
- **Rute API Cookie**: Menambahkan endpoint POST `/cookie-consent` pada berkas rute [web.php](file:///c:/laragon/www/AbsenDJJ/routes/web.php).
- **Modal Dialog Banner**: Menyematkan antarmuka modal dialog Kebijakan Cookie kustom pada [layout.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/layout.blade.php) yang otomatis muncul jika pengguna belum memberikan persetujuan.
- **Logika AJAX Frontend**: Mengintegrasikan event listener pada berkas [dashboard-layout.js](file:///c:/laragon/www/AbsenDJJ/resources/js/dashboard-layout.js) untuk mengirimkan pilihan persetujuan pengguna secara asinkron (AJAX) ke backend dan menutup modal banner secara dinamis.
- **Pengujian Unit**: Menyusun berkas pengujian [CookieConsentTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/CookieConsentTest.php) guna memverifikasi penyimpanan data persetujuan cookie di session dan cookie HTTP.

### Proteksi Bruteforce Login & Middleware Otorisasi Peran
- **Pembatasan Rate Limit Login**: Mengimplementasikan pembatasan percobaan login (throttling) pada method `login` di [AuthController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/AuthController.php) berdasarkan email dan IP Address guna melindungi aplikasi dari serangan bruteforce.
- **Pengujian Rate Limit**: Menyusun berkas pengujian [AuthLoginRateLimitTest.php](file:///c:/laragon/www/AbsenDJJ/tests/Feature/AuthLoginRateLimitTest.php) untuk menguji keandalan sistem pembatasan login saat terdapat rentetan kegagalan login.
- **Middleware Otorisasi Role**: Membuat berkas [RoleMiddleware.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Middleware/RoleMiddleware.php) untuk memeriksa hak akses rute berdasarkan nama peran user (`super_admin`, `admin`, `peserta`) dan membatasi akses ilegal dengan respon status 403 Forbidden.
- **Registrasi Middleware**: Mendaftarkan alias middleware `'role'` pada konfigurasi middleware di berkas inisialisasi aplikasi [bootstrap/app.php](file:///c:/laragon/www/AbsenDJJ/bootstrap/app.php).

## Update Tampilan Responsif Mobile (HP Support)

### Off-Canvas Sidebar Navigation
- **HTML Modification ([layout.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/layout.blade.php))**:
  - Menambahkan overlay `<div class="sidebar-backdrop">` agar pengguna mobile dapat menutup sidebar dengan mengetuk area di luar menu.
  - Menambahkan tombol menu hamburger responsif `<button class="mobile-toggle-btn">` pada header halaman.
- **Javascript Logic ([dashboard-layout.js](file:///c:/laragon/www/AbsenDJJ/resources/js/dashboard-layout.js))**:
  - Mengimplementasikan event listener untuk menambah/menghapus kelas `sidebar-mobile-open` pada elemen `body`.
  - Menutup sidebar secara otomatis ketika tautan menu navigasi diklik atau backdrop disentuh.
- **CSS Layout Styling ([dashboard-layout.css](file:///c:/laragon/www/AbsenDJJ/resources/css/dashboard-layout.css))**:
  - Menyembunyikan sidebar off-screen (`transform: translateX(-100%)`) pada lebar layar <= 768px.
  - Menambahkan efek bayangan drawer dan blur kaca glassmorphism yang premium pada mobile drawer.
  - Mengurangi padding kontainer konten utama (`.main-content`) menjadi `20px 16px` di mobile agar ruang layar lebih maksimal.

### Form, Table & Widget Grid Optimizations
- **Responsif Form & Table ([dashboard-layout.css](file:///c:/laragon/www/AbsenDJJ/resources/css/dashboard-layout.css))**:
  - Grid form input pada modal box (`.form-grid`) otomatis berubah menjadi 1 kolom di mobile.
  - Ukuran padding baris tabel (`.custom-table`) disesuaikan agar lebih rapat dan kompak di layar sempit.
  - Penyesuaian responsif scrollable horizontal pada kontainer tabel `.table-responsive`.

### Responsif Kalender Kehadiran & Kamera
- **Kalender Peserta ([dashboard.css](file:///c:/laragon/www/AbsenDJJ/resources/css/peserta/dashboard.css))**:
  - Sel kalender riwayat kehadiran menyusut (tinggi minimal `60px`) dengan ukuran font nomor hari dan badge status yang disesuaikan pada mobile.
  - Menyembunyikan teks keterangan acara/libur (`.cell-desc`) pada kalender mobile untuk mencegah penumpukan teks.
  - Preview area video webcam dan hasil foto selfie absensi disesuaikan ukurannya agar pas di layar HP.
- **Kalender Admin Settings ([settings.css](file:///c:/laragon/www/AbsenDJJ/resources/css/super_admin/settings.css))**:
  - Sel kalender parameter setelan global menyusut menjadi `min-height: 55px` dengan penyesuaian font dan margin.
  - Leaflet map koordinat geofencing disesuaikan tingginya menjadi `240px` dengan grid input koordinat flex vertical.

## Update Relokasi Cetak Logbook & Detail Absensi

### Relokasi Tombol Cetak/Rekap Logbook
- **Pembersihan Halaman Detail & Tata Letak Sekuensial ([show.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/admin/interns/show.blade.php))**:
  - Menghapus tombol `Cetak Logbook (PDF)` dan `Rekap Logbook (CSV)` dari kartu "Kontak Darurat & Unduhan" untuk merampingkan antarmuka detail aktivitas.
  - Menghapus kartu statistik "Logbook Disetujui" dari barisan atas kartu ringkasan aktivitas (hadir, terlambat, izin, sakit) agar dashboard pembimbing lebih bersih dan fokus pada visualisasi kehadiran.
  - Menghilangkan navigasi tab (Tabs Container) pada halaman Detail Aktivitas Intern, dan menggantinya dengan susunan kartu sekuensial yang tampil langsung secara berurutan ke bawah: **Visual Kalender Kehadiran** (teratas), diikuti oleh **Tabel Riwayat Absensi**, dan kemudian **Logbook Harian Kegiatan**. Hal ini mempermudah pembimbing untuk melihat seluruh data penting dalam satu halaman gulir tanpa perlu berpindah tab.
- **Penyediaan Dropdown Dropdown Pilihan Anak Didik ([InternController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Admin/InternController.php))**:
  - Memperbarui method `logbooks()` untuk mengambil daftar semua anak bimbingan aktif (`$guidedInterns`) dan mengirimkannya ke tampilan index logbook pembimbing.
- **Penyaringan Laporan Ekspor ([LogbookController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Peserta/LogbookController.php))**:
  - Memperbarui method `exportPdf()` agar mendukung penyaringan bulan dan tahun opsional, serupa dengan fitur `exportCsv()` sehingga laporan logbook yang dicetak dapat disesuaikan per bulan.
- **Form Cetak & Rekap Logbook ([index.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/admin/logbooks/index.blade.php))**:
  - Menyediakan widget kartu baru "Cetak & Rekap Logbook Anak Didik" di bagian atas halaman Logbook Kegiatan Anak Didik.
  - Form ini dilengkapi dropdown pemilihan anak didik, bulan, dan tahun, serta menggunakan atribut HTML5 `formaction` dan `formtarget` guna mengarahkan ekspor PDF/CSV secara asinkron dan presisi.

## Update 2026-07-14

### Perbaikan Bug Kolom Catatan Pembimbing Tidak Muncul di Logbook Peserta

- **Akar Masalah**: Saat pembimbing menyetujui atau menolak logbook tanpa mengisi catatan, nilai `catatan_pembimbing` disimpan ke database sebagai string kosong `""` (bukan `null`). Operator `??` (null coalescing) pada Blade hanya menangani nilai `null`, sehingga string kosong lolos dan ditampilkan sebagai sel tabel kosong tanpa teks apapun.
- **Perbaikan View** ([logbook.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/logbook.blade.php)):
  - Mengubah tampilan kolom Catatan Pembimbing dari `{{ $logbook->catatan_pembimbing ?? '-' }}` menjadi `{{ !empty($logbook->catatan_pembimbing) ? $logbook->catatan_pembimbing : '-' }}` agar menangani baik `null` maupun string kosong `""`.
- **Perbaikan View** ([dashboard.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/dashboard.blade.php)):
  - Menerapkan perbaikan yang sama pada tabel ringkasan logbook terbaru di dashboard utama peserta.
- **Perbaikan Root Cause** ([DashboardController.php](file:///c:/laragon/www/AbsenDJJ/app/Http/Controllers/Admin/DashboardController.php)):
  - Menormalisasi input `catatan_pembimbing` pada method `approveLogbook()`, `rejectLogbook()`, `approveLeave()`, dan `rejectLeave()` menggunakan `?: null` agar string kosong otomatis dikonversi menjadi `null` sebelum disimpan ke database.
- Seluruh **55/55 pengujian** dinyatakan lulus (*passed*).

### Konfirmasi Hapus Logbook dengan SweetAlert2

- Menambahkan dialog konfirmasi **SweetAlert2** pada tombol **Hapus** di tabel Daftar Logbook Kegiatan peserta ([logbook.blade.php](file:///c:/laragon/www/AbsenDJJ/resources/views/dashboard/peserta/logbook.blade.php)).
- Saat peserta menekan tombol Hapus, popup SweetAlert2 bertema modern muncul dengan judul *"Hapus Logbook?"*, pesan konfirmasi, serta tombol *"Ya, Hapus"* (merah) dan *"Batal"* (abu-abu).
- Warna background dan teks popup otomatis menyesuaikan tema aktif (dark mode: latar gelap `#1e293b`, light mode: latar putih `#ffffff`).
- Menyediakan fallback `confirm()` bawaan browser jika pustaka SweetAlert2 belum termuat.
