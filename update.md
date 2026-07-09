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
