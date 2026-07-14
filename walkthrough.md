# Walkthrough: Penyelesaian Catatan Dosen & Rangkaian Pengujian SIAREA

Dokumen ini mendokumentasikan pemenuhan catatan revisi dari dosen terkait integrasi fitur gaji, penguatan alur anti titip absen (geofencing, jam aktif, & pencegahan presensi ganda), serta pembuatan pengujian sistem otomatis.

---

## Pemenuhan Catatan Dosen

### 1. Konsistensi Fitur Perhitungan & Informasi Gaji
* **Hasil Review:** 
  * Fitur ini sudah **sangat konsisten dan terintegrasi secara utuh**.
  * **Database:** Tabel `salary_calculations` menampung histori perhitungan gaji karyawan.
  * **Logika Bisnis:** Diatur di [SalaryService.php](file:///d:/TA/SIAREA/app/Services/SalaryService.php) dan model [SalaryCalculation.php](file:///d:/TA/SIAREA/app/Models/SalaryCalculation.php) untuk menghitung otomatis gaji bersih dikurangi akumulasi potongan keterlambatan.
  * **Hak Akses & Menu:** Admin memiliki menu perhitungan, persetujuan (approval), pembayaran (payment), ekspor laporan CSV, dan riwayat gaji staf. Karyawan hanya memiliki hak akses baca (read-only) untuk melihat slip gaji mereka sendiri via menu *Slip Gaji* di dasbor.
  * *Tidak diperlukan perubahan kode di poin ini karena seluruh alur sudah konsisten.*

### 2. Mekanisme Anti Titip Absen yang Utuh
Sistem absensi kini menggabungkan 4 lapisan pengamanan secara berurutan dalam satu transaksi database:
1. **Validasi QR Code:** Memeriksa keaslian token QR Code di database.
2. **Jam Aktif Absensi:** Memeriksa apakah pemindaian dilakukan pada jam aktif shift terkait (ditambah buffer check-in maksimal 60 menit sebelum shift mulai) menggunakan fungsi `$qr->isCurrentlyActive()`.
3. **Geofencing (Radius Area):** Memeriksa koordinat pengguna menggunakan rumus Haversine untuk menjamin jarak berada di dalam batas radius toleransi QR Code (misal: 50 meter).
4. **Pencegahan Presensi Ganda:** Menolak check-in ganda di hari/shift yang sama, serta menolak checkout ganda jika staf sudah berstatus pulang.

### 3. Pengujian Sistem Otomatis
Kami mengonfigurasi database SQLite in-memory pada [phpunit.xml](file:///d:/TA/SIAREA/phpunit.xml) untuk menjalankan uji coba secara terisolasi tanpa merusak database utama.

Kami membuat berkas pengujian baru di [AttendanceTest.php](file:///d:/TA/SIAREA/tests/Feature/AttendanceTest.php) untuk mensimulasikan skenario berikut:
* **Presensi di luar radius:** `test_presence_outside_radius` memastikan check-in ditolak jika jarak GPS terlalu jauh.
* **Presensi di luar jam aktif:** `test_presence_outside_active_hours` memastikan check-in ditolak di luar waktu shift (dan buffer).
* **Presensi menggunakan QR tidak valid:** `test_presence_using_invalid_qr` memastikan penolakan kode QR tiruan/tidak terdaftar.
* **Percobaan presensi ganda:** `test_double_presence_attempt` menjamin karyawan tidak bisa melakukan check-in berkali-kali pada hari/shift yang sama.
* **Akses bersamaan (checkout ganda):** `test_concurrent_checkout_prevention` menjamin checkout kedua akan ditolak jika staf sudah melakukan checkout sebelumnya.

---

## Hasil Eksekusi Pengujian (`php artisan test`)
Pengujian berjalan sukses dengan status **PASS** untuk seluruh asersi:

```bash
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\AttendanceTest
  ✓ presence outside radius
  ✓ presence outside active hours
  ✓ presence using invalid qr
  ✓ double presence attempt
  ✓ concurrent checkout prevention

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

  Tests:    7 passed (18 assertions)
  Duration: 0.78s
```
Sistem SIAREA sekarang memenuhi kriteria keamanan sistem absensi dan siap diserahkan ke dosen!
