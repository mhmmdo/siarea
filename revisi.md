# DOKUMENTASI REVISI SISTEM - SIAREA

Dokumen ini berisi rangkuman pembaruan sistem absensi, proteksi keamanan, manajemen akun, serta pengujian sistem otomatis pada aplikasi **SIAREA** (Sistem Absensi Kafe).

---

## DAFTAR REVISI & PEMBARUAN SISTEM

### 1. Sistem Absensi Aman (Anti Titip Absen & Geofencing)
Sistem absensi kini menggabungkan perlindungan bertingkat di sisi server dalam satu transaksi database:
* **Pembatasan Waktu Masuk (Check-In):** Karyawan hanya bisa melakukan check-in jika waktu saat ini berada pada jam kerja shift aktif (dengan batas toleransi buffer check-in dimulai **60 menit sebelum shift berjalan** hingga shift berakhir).
* **Pembatasan Waktu Pulang (Check-Out) & Waktu Shift Malam:** Tombol check-out dikunci dengan hitung mundur hingga waktu pulang diperbolehkan (**paling cepat 15 menit sebelum shift berakhir**). Perhitungan shift malam (overnight) disesuaikan agar tidak terjadi kesalahan deteksi hari.
* **Geofencing (Area Batas Radius):** Lokasi pengguna divalidasi menggunakan rumus Haversine untuk memastikan jarak fisik dengan koordinat QR Code tidak melebihi radius area yang diperbolehkan (misal: 50 meter).
* **Pencegahan Presensi Ganda:** Karyawan diblokir secara otomatis di sisi backend dari melakukan check-in ganda pada shift/hari yang sama, atau melakukan check-out berulang jika statusnya sudah pulang.

### 2. Deteksi Proteksi GPS Palsu (Fake GPS Protection)
* **Validasi Akurasi Geolocation:** Ditambahkan kode pemeriksaan di sisi frontend JavaScript (`position.coords.accuracy === 0`).
* **Cara Kerja:** Aplikasi tiruan lokasi (Fake GPS / Mock Location) biasanya mengirimkan tingkat akurasi 0 meter ke browser karena tidak menyimulasikan fluktuasi sinyal satelit. Jika terdeteksi akurasi bernilai tepat 0, absensi dihentikan dan sistem memunculkan peringatan bagi pengguna untuk mematikan aplikasi Fake GPS.

### 3. Pengalihan Sistem Login Menggunakan Nomor HP (Bukan Email)
* **Login Karyawan:** Login dialihkan menggunakan **Nomor HP** yang terdaftar di sistem untuk menggantikan email Gmail.
* **Sistem Login Hibrida Pintar:** Backend secara dinamis mendeteksi tipe input. Jika input berupa angka (Nomor HP), pencarian dilakukan ke tabel `employees`. Jika berupa karakter biasa, pencarian dilakukan ke tabel `users` (agar pengguna Admin tetap dapat masuk menggunakan username bawaan `admin`).
* **Halaman Login Baru:** Layout input pada halaman login disesuaikan menjadi *Nomor HP / Username* dengan ikon yang bersih dan modern.

### 4. Fitur Manajemen & Ubah Password Akun
* **Ubah Password Mandiri Karyawan:** Ditambahkan form ubah kata sandi baru di halaman **Profil Saya** bagian bawah. Pengguna wajib memasukkan password saat ini demi alasan keamanan.
* **Ubah Password Mandiri Admin:** Ditambahkan menu baru **Pengaturan Akun** pada sidebar dan tautan avatar di topbar admin untuk mengganti kata sandi admin aktif.
* **Reset Karyawan oleh Admin:** Pada halaman pengelolaan karyawan, admin kini dapat mereset atau menetapkan password baru secara manual bagi staf tertentu melalui form edit karyawan.

### 5. Rangkaian Pengujian Sistem Otomatis (Automated Testing)
Untuk memenuhi kelayakan pengujian sistem dari dosen, rangkaian pengujian otomatis disiapkan menggunakan database terisolasi SQLite *in-memory* tanpa mengotori database produksi.
Skenario pengujian yang tercakup dalam `tests/Feature/AttendanceTest.php`:
1. **Presensi di luar radius:** Memastikan check-in gagal jika koordinat GPS pengguna berada di luar batas toleransi radius QR.
2. **Presensi di luar jam aktif:** Memastikan check-in ditolak di luar jam aktif shift dan buffer.
3. **Presensi memakai QR tidak valid:** Memastikan penolakan jika kode token QR palsu atau dinonaktifkan.
4. **Percobaan check-in ganda:** Memastikan karyawan tidak bisa check-in berulang kali pada hari yang sama.
5. **Checkout ganda:** Menolak proses checkout berulang jika status absensi hari tersebut sudah terekam pulang.
6. **Akses bersamaan (Concurrent Access):** Memastikan minimal 2 akun/staf berbeda dapat melakukan absensi secara bersamaan dengan sukses pada shift yang sama (`test_multiple_users_can_attend_simultaneously`).

---

## DETAIL FILE YANG DIUBAH / DITAMBAHKAN

| No | Nama File | Status | Fungsi / Kegunaan |
| :--- | :--- | :--- | :--- |
| 1 | `routes/web.php` | MODIFY | Menambahkan rute ubah password karyawan, profil admin, dan ganti password admin. |
| 2 | `app/Services/AttendanceService.php` | MODIFY | Menambahkan validasi jam aktif QR Code (`isCurrentlyActive`) dan pengetatan logika check-in/check-out. |
| 3 | `app/Models/QrCode.php` | MODIFY | Menambahkan buffer check-in 60 menit sebelum shift mulai pada kalkulasi `isCurrentlyActive`. |
| 4 | `app/Http/Controllers/Web/AuthController.php` | MODIFY | Menyesuaikan logika autentikasi hibrida (Nomor HP untuk staf, Username/Email untuk admin). |
| 5 | `resources/views/auth/login.blade.php` | MODIFY | Mengubah input email menjadi Nomor HP / Username. |
| 6 | `app/Http/Controllers/Web/UserController.php` | MODIFY | Menambahkan method `updatePassword` dan mengatur logika checkout sisa waktu tunggu. |
| 7 | `resources/views/user/profile.blade.php` | MODIFY | Menambahkan form ubah password karyawan mandiri. |
| 8 | `app/Http/Controllers/Web/AdminController.php` | MODIFY | Menambahkan method `profile` dan `updatePassword` untuk akun admin. |
| 9 | `resources/views/admin/profile.blade.php` | **NEW** | Membuat halaman ubah password khusus untuk admin. |
| 10 | `resources/views/layouts/app.blade.php` | MODIFY | Menambahkan menu "Pengaturan Akun" di sidebar admin dan tautan topbar. |
| 11 | `app/Http/Controllers/Web/EmployeeController.php` | MODIFY | Menambahkan penanganan reset password staf oleh admin. |
| 12 | `resources/views/admin/employees/edit.blade.php` | MODIFY | Menambahkan kolom input password baru dan konfirmasi password staf. |
| 13 | `resources/views/user/checkout.blade.php` | MODIFY | Memasang tombol checkout dinonaktifkan jika belum jam pulang dan deteksi GPS palsu. |
| 14 | `resources/views/user/scan.blade.php` | MODIFY | Memasang deteksi awal GPS palsu (Mock Location accuracy 0m). |
| 15 | `phpunit.xml` | MODIFY | Mengaktifkan driver database `sqlite` `:memory:` untuk pengetesan. |
| 16 | `tests/Feature/AttendanceTest.php` | **NEW** | Membuat berkas skenario pengujian otomatis. |

---

## CARA MENJALANKAN PENGUJIAN SISTEM OTOMATIS
Buka terminal pada direktori proyek Anda, lalu jalankan perintah berikut:
```bash
php artisan test
```
Seluruh skenario pengujian akan berjalan secara otomatis dengan hasil status **PASS**.
